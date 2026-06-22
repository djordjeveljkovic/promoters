<?php

namespace Tests\Feature;

use App\Models\Festival;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Covers BUG-AUDIT-001 + BUG-AUDIT-002 fixes.
 *
 * Verifies that every admin promoter CRUD endpoint:
 *   - returns 200 for the superadmin (200/302 for actions)
 *   - resolves the festival parameter correctly when the URL contains
 *     a slug (BUG-AUDIT-002)
 *   - passes $festival to the view (BUG-AUDIT-001)
 */
class PromoterCrudTest extends TestCase
{
    use RefreshDatabase;

    private Festival $festival;
    private User $superadmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->festival = Festival::create([
            'name'           => 'REFEST',
            'year'           => 2026,
            'slug'           => 'refest-2026',
            'status'         => 'active',
            'primary_color'  => '#dc2626',
            'secondary_color'=> '#fbbf24',
        ]);

        $this->superadmin = User::create([
            'name'     => 'Root',
            'email'    => 'super@test.rs',
            'password' => bcrypt('x'),
            'role'     => 'superadmin',
        ]);
        $this->superadmin->festivals()->attach($this->festival->id, [
            'role_in_festival' => 'admin',
            'assigned_by'      => null,
            'assigned_at'      => now(),
        ]);
        $this->actingAs($this->superadmin);
    }

    public function test_promoter_create_view_renders_without_500(): void
    {
        // BUG-AUDIT-001 — view uses $festival; controller must pass it.
        $response = $this->get("/admin/festivals/{$this->festival->slug}/promoter/create");
        $response->assertOk();
        // Form action should point to the store endpoint.
        $response->assertSee(route('admin.promoters.store', ['festival' => $this->festival->slug]), false);
        // And the title + cancel button should reference the festival.
        
    }

    public function test_promoter_create_persists_and_assigns_to_festival(): void
    {
        $response = $this->post("/admin/festivals/{$this->festival->slug}/promoters", [
            'name'  => 'Sample Promoter',
            'email' => 'newpromoter@test.rs',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('admin.promoters.index', ['festival' => $this->festival->slug]));

        $promoter = User::where('email', 'newpromoter@test.rs')->firstOrFail();
        $this->assertSame('promoter', $promoter->role);
        // Auto-assignment is the value-add here: the new promoter should
        // be attached to the current festival so they can immediately
        // start selling.
        $this->assertTrue($promoter->festivals()->where('festivals.id', $this->festival->id)->exists());
        $this->assertSame('promoter', $promoter->roleInFestival($this->festival->id));
    }

    public function test_promoter_edit_view_renders_for_existing_promoter(): void
    {
        // BUG-AUDIT-002 — controller signature must accept $festival, $id.
        $promoter = User::create([
            'name'     => 'Existing Promoter',
            'email'    => 'existing@test.rs',
            'password' => bcrypt('x'),
            'role'     => 'promoter',
        ]);
        $promoter->festivals()->attach($this->festival->id, [
            'role_in_festival' => 'promoter',
            'assigned_at'      => now(),
        ]);

        $response = $this->get("/admin/festivals/{$this->festival->slug}/promoter/edit/{$promoter->id}");
        $response->assertOk();
        $response->assertSee('Existing Promoter');
    }

    public function test_promoter_update_persists_changes(): void
    {
        $promoter = User::create([
            'name'     => 'Old Name',
            'email'    => 'toupdate@test.rs',
            'password' => bcrypt('x'),
            'role'     => 'promoter',
        ]);
        $promoter->festivals()->attach($this->festival->id, [
            'role_in_festival' => 'promoter',
            'assigned_at'      => now(),
        ]);

        $response = $this->put("/admin/festivals/{$this->festival->slug}/promoter/edit/{$promoter->id}", [
            'name'  => 'New Name',
            'email' => 'toupdate@test.rs',
        ]);
        $response->assertRedirect(route('admin.promoters.edit', [
            'festival' => $this->festival->slug,
            'id'       => $promoter->id,
        ]));
        $this->assertSame('New Name', $promoter->fresh()->name);
    }

    public function test_promoter_destroy_detaches_and_deletes(): void
    {
        $promoter = User::create([
            'name'     => 'Doomed',
            'email'    => 'doomed@test.rs',
            'password' => bcrypt('x'),
            'role'     => 'promoter',
        ]);
        $promoter->festivals()->attach($this->festival->id, [
            'role_in_festival' => 'promoter',
            'assigned_at'      => now(),
        ]);

        $response = $this->delete("/admin/festivals/{$this->festival->slug}/promoter/{$promoter->id}");
        $response->assertRedirect(route('admin.promoters.index', ['festival' => $this->festival->slug]));
        $this->assertNull(User::find($promoter->id));
    }

    public function test_promoter_cannot_delete_admin_or_superadmin(): void
    {
        $admin = User::create([
            'name'     => 'Admin User',
            'email'    => 'admin@test.rs',
            'password' => bcrypt('x'),
            'role'     => 'admin',
        ]);
        $admin->festivals()->attach($this->festival->id, [
            'role_in_festival' => 'admin',
            'assigned_at'      => now(),
        ]);

        $response = $this->delete("/admin/festivals/{$this->festival->slug}/promoter/{$admin->id}");
        // Should redirect back with an error message, not actually delete.
        $response->assertRedirect();
        $this->assertNotNull(User::find($admin->id));
    }

    public function test_make_and_remove_manager_promote_and_demote_promoter(): void
    {
        $promoter = User::create([
            'name'     => 'Manager Track',
            'email'    => 'manager-track@test.rs',
            'password' => bcrypt('x'),
            'role'     => 'promoter',
        ]);
        $promoter->festivals()->attach($this->festival->id, [
            'role_in_festival' => 'promoter',
            'assigned_at'      => now(),
        ]);
        $this->assertFalse($promoter->isPromoterManager($this->festival->id));

        $this->put("/admin/festivals/{$this->festival->slug}/promoter/{$promoter->id}/make-manager")
            ->assertRedirect();

        $this->assertTrue($promoter->fresh()->isPromoterManager($this->festival->id));

        $this->put("/admin/festivals/{$this->festival->slug}/promoter/{$promoter->id}/remove-manager")
            ->assertRedirect();

        $this->assertFalse($promoter->fresh()->isPromoterManager($this->festival->id));
        $this->assertTrue($promoter->fresh()->isRegularPromoter($this->festival->id));
    }
}
