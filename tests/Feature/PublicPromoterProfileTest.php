<?php

namespace Tests\Feature;

use App\Models\Festival;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * P-070: public promoter profile coverage.
 *
 *  - a promoter with `is_public = true` is visible at /p/{id};
 *  - a promoter with `is_public = false` returns 404;
 *  - non-promoter users are never public;
 *  - the promoter edit form persists the public toggle and bio.
 */
class PublicPromoterProfileTest extends TestCase
{
    use RefreshDatabase;

    private Festival $festival;
    private User $admin;
    private User $promoter;
    private User $privatePromoter;
    private User $buyer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->festival = Festival::create([
            'name' => 'REFEST', 'year' => 2026, 'slug' => 'refest-2026',
            'status' => 'active', 'is_public' => true,
        ]);

        $this->admin = User::create([
            'name' => 'A', 'email' => 'a@test.rs',
            'password' => bcrypt('x'), 'role' => 'admin',
        ]);
        $this->admin->festivals()->attach($this->festival->id, [
            'role_in_festival' => 'admin', 'assigned_at' => now(),
        ]);

        $this->promoter = User::create([
            'name'     => 'Mario Promoter',
            'email'    => 'mario@test.rs',
            'password' => bcrypt('x'),
            'role'     => 'promoter',
            'is_public' => true,
            'bio'      => 'I sell tickets for music festivals across the Balkans.',
        ]);
        $this->promoter->festivals()->attach($this->festival->id, [
            'role_in_festival' => 'promoter', 'assigned_at' => now(),
        ]);

        $this->privatePromoter = User::create([
            'name'     => 'Hidden Promoter',
            'email'    => 'hidden@test.rs',
            'password' => bcrypt('x'),
            'role'     => 'promoter',
            'is_public' => false,
        ]);
        $this->privatePromoter->festivals()->attach($this->festival->id, [
            'role_in_festival' => 'promoter', 'assigned_at' => now(),
        ]);

        $this->buyer = User::create([
            'name' => 'B', 'email' => 'b@test.rs',
            'password' => bcrypt('x'), 'role' => 'buyer',
        ]);
    }

    public function test_public_profile_renders_for_public_promoter(): void
    {
        $response = $this->get('/p/' . $this->promoter->id);
        $response->assertOk();
        $response->assertSee('Mario Promoter');
        $response->assertSee('I sell tickets for music festivals');
        $response->assertSee('REFEST'); // festival listed
    }

    public function test_public_profile_returns_404_for_private_promoter(): void
    {
        $response = $this->get('/p/' . $this->privatePromoter->id);
        $response->assertStatus(404);
    }

    public function test_public_profile_returns_404_for_buyer(): void
    {
        $response = $this->get('/p/' . $this->buyer->id);
        $response->assertStatus(404);
    }

    public function test_public_profile_returns_404_for_admin(): void
    {
        $response = $this->get('/p/' . $this->admin->id);
        $response->assertStatus(404);
    }

    public function test_admin_can_toggle_promoter_public_status(): void
    {
        $this->actingAs($this->admin);

        // Promote the private promoter to public.
        $response = $this->put(
            "/admin/festivals/{$this->festival->slug}/promoter/edit/{$this->privatePromoter->id}",
            [
                'name'      => $this->privatePromoter->name,
                'email'     => $this->privatePromoter->email,
                'is_public' => 1,
                'bio'       => 'Now I am visible too.',
            ]
        );
        $response->assertRedirect();
        $this->assertTrue($this->privatePromoter->fresh()->is_public);
        $this->assertSame('Now I am visible too.', $this->privatePromoter->fresh()->bio);

        // Profile now reachable.
        $this->get('/p/' . $this->privatePromoter->id)->assertOk();
    }

    public function test_admin_can_set_promoter_private(): void
    {
        $this->actingAs($this->admin);

        // Make the public promoter private.
        $this->put(
            "/admin/festivals/{$this->festival->slug}/promoter/edit/{$this->promoter->id}",
            [
                'name'      => $this->promoter->name,
                'email'     => $this->promoter->email,
                'is_public' => 0, // unchecked
                'bio'       => 'Going private again.',
            ]
        )->assertRedirect();
        $this->assertFalse($this->promoter->fresh()->is_public);

        // Profile should now 404.
        $this->get('/p/' . $this->promoter->id)->assertStatus(404);
    }
}
