<?php

namespace Tests\Feature;

use App\Models\Festival;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression coverage for the "promoter manager can't manage their
 * sub-promoters' commission" bug.
 *
 * Scenarios covered:
 *
 *   - admin creates a promoter + promotes them to manager → manager
 *     can list / show / update commission on sub-promoters they own,
 *   - a plain (non-manager) promoter is blocked everywhere,
 *   - a sub-promoter is blocked from every management endpoint,
 *   - a manager cannot manage a sub-promoter that belongs to another
 *     manager (parent_id check),
 *   - the manager check correctly handles being scoped to the festival
 *     (a manager on festival A cannot manage sub-promoters on festival B).
 */
class SubPromoterCommissionFlowTest extends TestCase
{
    use RefreshDatabase;

    private Festival $festivalA;
    private Festival $festivalB;
    private TicketType $ttA;

    protected function setUp(): void
    {
        parent::setUp();

        $this->festivalA = Festival::create([
            'name' => 'A', 'year' => 2026, 'slug' => 'a-2026',
            'status' => 'active',
            'primary_color' => '#000', 'secondary_color' => '#fff',
        ]);
        $this->festivalB = Festival::create([
            'name' => 'B', 'year' => 2026, 'slug' => 'b-2026',
            'status' => 'active',
            'primary_color' => '#000', 'secondary_color' => '#fff',
        ]);

        $this->ttA = TicketType::create([
            'festival_id' => $this->festivalA->id,
            'name' => 'GA',
            'price' => 1000,
            'qr_coordinates' => ['x' => 0, 'y' => 0, 'size' => 100],
        ]);
        \App\Models\TicketCommission::create([
            'ticket_type_id' => $this->ttA->id,
            'min_sold' => 1, 'max_sold' => 0, 'commission_amount' => 50.00,
            'valid_from' => '2020-01-01', 'valid_to' => null,
        ]);
    }

    private function makeManager(string $email, Festival $festival): User
    {
        $m = User::create([
            'name' => 'Manager', 'email' => $email,
            'password' => bcrypt('x'), 'role' => 'promoter',
        ]);
        $m->festivals()->attach($festival->id, [
            'role_in_festival' => 'promoter_manager',
            'assigned_by' => null, 'assigned_at' => now(),
        ]);
        return $m;
    }

    private function makeSub(User $parent, string $email, Festival $festival): User
    {
        $s = User::create([
            'name' => 'Sub', 'email' => $email,
            'password' => bcrypt('x'), 'role' => 'sub_promoter',
            'parent_id' => $parent->id,
        ]);
        $s->festivals()->attach($festival->id, [
            'role_in_festival' => 'sub_promoter',
            'assigned_by' => $parent->id, 'assigned_at' => now(),
        ]);
        return $s;
    }

    public function test_manager_can_full_flow(): void
    {
        $m = $this->makeManager('manager-flow@t.local', $this->festivalA);
        $s = $this->makeSub($m, 'sub-flow@t.local', $this->festivalA);
        $this->actingAs($m);

        $this->get("/promoter/festivals/{$this->festivalA->slug}/sub-promoters")->assertOk();
        $this->get("/promoter/festivals/{$this->festivalA->slug}/sub-promoters/{$s->id}")->assertOk();
        $this->put("/promoter/festivals/{$this->festivalA->slug}/sub-promoters/{$s->id}", [
            'commissions' => [$this->ttA->id => 5.00],
        ])->assertRedirect();
    }

    public function test_plain_promoter_without_manager_role_is_blocked(): void
    {
        $p = User::create([
            'name' => 'Plain Promoter', 'email' => 'plain@t.local',
            'password' => bcrypt('x'), 'role' => 'promoter',
        ]);
        $p->festivals()->attach($this->festivalA->id, [
            'role_in_festival' => 'promoter',  // not promoter_manager
            'assigned_by' => null, 'assigned_at' => now(),
        ]);
        $s = $this->makeSub($p, 'sub-plain@t.local', $this->festivalA);
        $this->actingAs($p);

        $this->get("/promoter/festivals/{$this->festivalA->slug}/sub-promoters")->assertForbidden();
        $this->get("/promoter/festivals/{$this->festivalA->slug}/sub-promoters/{$s->id}")->assertForbidden();
        $this->put("/promoter/festivals/{$this->festivalA->slug}/sub-promoters/{$s->id}", [
            'commissions' => [$this->ttA->id => 5.00],
        ])->assertForbidden();
    }

    public function test_sub_promoter_is_blocked_from_management_endpoints(): void
    {
        $m = $this->makeManager('m@t.local', $this->festivalA);
        $s = $this->makeSub($m, 's@t.local', $this->festivalA);
        $this->actingAs($s);

        $this->get("/promoter/festivals/{$this->festivalA->slug}/sub-promoters")->assertForbidden();
        $this->get("/promoter/festivals/{$this->festivalA->slug}/sub-promoters/{$s->id}")->assertForbidden();
    }

    public function test_manager_cannot_manage_another_managers_sub_promoter(): void
    {
        $m1 = $this->makeManager('m1@t.local', $this->festivalA);
        $m2 = $this->makeManager('m2@t.local', $this->festivalA);
        $s2 = $this->makeSub($m2, 's2@t.local', $this->festivalA);

        $this->actingAs($m1);

        // m1 is a manager on the festival — passes the role check.
        // But s2 belongs to m2, so the parent_id check should 403.
        $this->get("/promoter/festivals/{$this->festivalA->slug}/sub-promoters/{$s2->id}")->assertForbidden();
        $this->put("/promoter/festivals/{$this->festivalA->slug}/sub-promoters/{$s2->id}", [
            'commissions' => [$this->ttA->id => 5.00],
        ])->assertForbidden();
    }

    public function test_manager_on_festival_a_cannot_manage_subpromoters_on_festival_b(): void
    {
        $m = $this->makeManager('cross@t.local', $this->festivalA);
        $sb = $this->makeSub($m, 'sub-b@t.local', $this->festivalB);
        $this->actingAs($m);

        // Sub-promoter belongs to festival B; manager only has access to A.
        $this->get("/promoter/festivals/{$this->festivalB->slug}/sub-promoters")->assertForbidden();
        $this->get("/promoter/festivals/{$this->festivalB->slug}/sub-promoters/{$sb->id}")->assertForbidden();
    }

    public function test_festival_admin_can_manage_any_subpromoter(): void
    {
        // Admin should be able to rescue a sub-promoter whose parent_id
        // drifted (e.g. because the original manager was removed from
        // the festival but the sub-promoter still exists).
        $manager = $this->makeManager('mgr-rescue@t.local', $this->festivalA);
        $sub = $this->makeSub($manager, 'sub-orphan@t.local', $this->festivalA);
        // Simulate the orphan state: clear the parent_id.
        $sub->update(['parent_id' => null]);

        $admin = User::create([
            'name' => 'Admin', 'email' => 'admin-rescue@t.local',
            'password' => bcrypt('x'), 'role' => 'admin',
        ]);
        $admin->festivals()->attach($this->festivalA->id, [
            'role_in_festival' => 'admin',
            'assigned_by' => null, 'assigned_at' => now(),
        ]);
        $this->actingAs($admin);

        $this->get("/promoter/festivals/{$this->festivalA->slug}/sub-promoters/{$sub->id}")->assertOk();
        $this->put("/promoter/festivals/{$this->festivalA->slug}/sub-promoters/{$sub->id}", [
            'commissions' => [],
        ])->assertRedirect();
    }

    public function test_superadmin_can_manage_any_subpromoter(): void
    {
        $manager = $this->makeManager('mgr-su@t.local', $this->festivalA);
        $sub = $this->makeSub($manager, 'sub-su@t.local', $this->festivalA);
        $sub->update(['parent_id' => null]);

        $su = User::create([
            'name' => 'Root', 'email' => 'root@t.local',
            'password' => bcrypt('x'), 'role' => 'superadmin',
        ]);
        $this->actingAs($su);

        $this->get("/promoter/festivals/{$this->festivalA->slug}/sub-promoters/{$sub->id}")->assertOk();
    }
}