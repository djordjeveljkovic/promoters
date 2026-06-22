<?php

namespace Tests\Feature;

use App\Models\Festival;
use App\Models\TicketOrder;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * P-069: Global search coverage.
 */
class GlobalSearchTest extends TestCase
{
    use RefreshDatabase;

    private Festival $festival;
    private User $admin;
    private User $promoter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->festival = Festival::create([
            'name' => 'REFEST', 'year' => 2026, 'slug' => 'refest-2026',
            'status' => 'active',
        ]);

        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'a@test.rs',
            'password' => bcrypt('x'), 'role' => 'admin',
        ]);
        $this->admin->festivals()->attach($this->festival->id, [
            'role_in_festival' => 'admin', 'assigned_at' => now(),
        ]);

        $this->promoter = User::create([
            'name' => 'Mario Promoter', 'email' => 'mario@test.rs',
            'password' => bcrypt('x'), 'role' => 'promoter',
        ]);
        $this->promoter->festivals()->attach($this->festival->id, [
            'role_in_festival' => 'promoter', 'assigned_at' => now(),
        ]);

        TicketType::create([
            'festival_id' => $this->festival->id,
            'name' => 'General Admission',
            'price' => 5000,
            'qr_coordinates' => ['x' => 0, 'y' => 0, 'size' => 100],
        ]);
    }

    public function test_search_too_short_returns_empty(): void
    {
        $this->actingAs($this->admin);
        $component = \Livewire\Livewire::test('global-search');
        $component->set('q', 'a');
        $this->assertCount(0, $component->viewData('groups'));
    }

    public function test_search_finds_festival_by_name(): void
    {
        $this->actingAs($this->admin);
        $component = \Livewire\Livewire::test('global-search');
        $component->set('q', 'REFEST');
        $groups = $component->viewData('groups');
        $this->assertTrue($groups->has('festivals'));
        $this->assertSame($this->festival->id, $groups['festivals']->first()->id);
    }

    public function test_search_finds_promoter_by_email(): void
    {
        $this->actingAs($this->admin);
        $component = \Livewire\Livewire::test('global-search');
        $component->set('q', 'mario@');
        $groups = $component->viewData('groups');
        $this->assertTrue($groups->has('promoters'));
        $this->assertSame($this->promoter->id, $groups['promoters']->first()->id);
    }

    public function test_search_finds_ticket_type_by_name(): void
    {
        $this->actingAs($this->admin);
        $component = \Livewire\Livewire::test('global-search');
        $component->set('q', 'General');
        $groups = $component->viewData('groups');
        $this->assertTrue($groups->has('ticket_types'));
    }

    public function test_search_finds_completed_order_by_number(): void
    {
        $buyer = User::create(['name' => 'B', 'email' => 'b@test.rs', 'password' => bcrypt('x'), 'role' => 'buyer']);
        TicketOrder::create([
            'festival_id' => $this->festival->id,
            'order_number' => 'XYZZY-42',
            'email' => 'cust@test.rs',
            'ordered_by' => $buyer->id,
            'requested_by' => $this->promoter->id,
            'job_status' => 'completed',
            'paid' => 5000, 'total' => 5000,
        ]);

        $this->actingAs($this->admin);
        $component = \Livewire\Livewire::test('global-search');
        $component->set('q', 'XYZZY');
        $groups = $component->viewData('groups');
        $this->assertTrue($groups->has('orders'));
        $this->assertSame('XYZZY-42', $groups['orders']->first()->order_number);
    }

    public function test_search_does_not_leak_other_festivals(): void
    {
        // Create a second festival + a promoter that's NOT in our scope.
        $other = Festival::create([
            'name' => 'SECRETFEST', 'year' => 2027, 'slug' => 'secret-2027',
            'status' => 'active',
        ]);
        $alien = User::create([
            'name' => 'Alien Promoter', 'email' => 'alien@other.test',
            'password' => bcrypt('x'), 'role' => 'promoter',
        ]);
        $alien->festivals()->attach($other->id, [
            'role_in_festival' => 'promoter', 'assigned_at' => now(),
        ]);

        $this->actingAs($this->admin);

        // First sanity check — the alien promoter is NOT reachable for our admin.
        $accessible = $this->admin->accessibleFestivals()->pluck('id')->all();
        $this->assertNotContains($other->id, $accessible);

        $component = \Livewire\Livewire::test('global-search');
        $component->set('q', 'Alien');
        $groups = $component->viewData('groups');

        // The promoters group either doesn't exist (no matches) or
        // contains zero rows. Either way, our actor should never see
        // the alien user.
        $promoters = $groups->get('promoters', collect());
        $this->assertCount(0, $promoters);

        $festivals = $groups->get('festivals', collect());
        $this->assertCount(0, $festivals);
    }

    public function test_urlFor_returns_admin_routes_for_admin(): void
    {
        $this->actingAs($this->admin);
        $component = \Livewire\Livewire::test('global-search');
        // Reach into the underlying Livewire component to grab the return
        // value (Livewire::call returns the test harness, not the value).
        $url = $component->instance()->urlFor('festivals', $this->festival);
        $this->assertIsString($url);
        $this->assertStringContainsString('/admin/festivals/refest-2026', $url);
    }

    public function test_urlFor_returns_promoter_dashboard_for_promoter(): void
    {
        $this->actingAs($this->promoter);
        $component = \Livewire\Livewire::test('global-search');
        $url = $component->instance()->urlFor('festivals', $this->festival);
        $this->assertIsString($url);
        $this->assertStringContainsString('/promoter/festivals/refest-2026', $url);
    }
}
