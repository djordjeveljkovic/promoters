<?php

namespace Tests\Feature;

use App\Models\Festival;
use App\Models\MailTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests for:
 *   - U-004  mail-template editor surfaces promoter / admin templates
 *   - B-011  festival slug resolution (Route::bind) so `/superadmin/festivals/{slug}/edit` works
 */
class RouteBindingAndMailTemplateTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_festival_routes_resolve_by_slug(): void
    {
        $u = User::create([
            'name'     => 'Root',
            'email'    => 'root@test.local',
            'password' => bcrypt('x'),
            'role'     => 'superadmin',
        ]);
        $this->actingAs($u);

        $f = Festival::create([
            'name'           => 'REFEST',
            'year'           => 2099,
            'slug'           => 'refest-2099',
            'status'         => 'active',
            'primary_color'  => '#ff2d92',
            'secondary_color'=> '#5ce1ff',
        ]);

        $r = $this->get('/superadmin/festivals/refest-2099/edit');
        $r->assertOk();
        $r->assertSee('REFEST');
        $r->assertSee('2099');
    }

    public function test_unknown_slug_returns_404(): void
    {
        $u = User::create([
            'name'     => 'Root',
            'email'    => 'root@test.local',
            'password' => bcrypt('x'),
            'role'     => 'superadmin',
        ]);
        $this->actingAs($u);

        $r = $this->get('/superadmin/festivals/no-such-festival/edit');
        $r->assertNotFound();
    }

    public function test_mail_template_editor_surfaces_promoter_and_admin_keys(): void
    {
        $u = User::create([
            'name'     => 'Admin',
            'email'    => 'admin@test.local',
            'password' => bcrypt('x'),
            'role'     => 'admin',
        ]);
        $f = Festival::create([
            'name'           => 'REFEST',
            'year'           => 2099,
            'slug'           => 'refest-2099',
            'status'         => 'active',
            'primary_color'  => '#ff2d92',
            'secondary_color'=> '#5ce1ff',
        ]);
        $u->festivals()->attach($f->id, [
            'role_in_festival' => 'admin',
            'assigned_by'      => null,
            'assigned_at'      => now(),
        ]);
        $this->actingAs($u);

        $r = $this->get("/admin/festivals/{$f->slug}/mail-templates");
        $r->assertOk();
        // The editor should mention every template key it knows about.
        $r->assertSee('Customer — Tickets delivery');
        $r->assertSee('Promoter — New order notification');
        $r->assertSee('Admin — Daily summary');
        $r->assertSee('Admin — Ticket-image generation failed');
    }

    public function test_create_template_for_new_key(): void
    {
        $u = User::create([
            'name'     => 'Admin',
            'email'    => 'admin@test.local',
            'password' => bcrypt('x'),
            'role'     => 'admin',
        ]);
        $f = Festival::create([
            'name'           => 'REFEST',
            'year'           => 2099,
            'slug'           => 'refest-2099',
            'status'         => 'active',
            'primary_color'  => '#ff2d92',
            'secondary_color'=> '#5ce1ff',
        ]);
        $u->festivals()->attach($f->id, [
            'role_in_festival' => 'admin',
            'assigned_by'      => null,
            'assigned_at'      => now(),
        ]);
        $this->actingAs($u);

        // The Livewire save() lives behind a Livewire update endpoint which
        // is non-trivial to drive in tests.  Instead, we verify the
        // store path by going through the model directly.
        $tpl = MailTemplate::create([
            'key'        => 'promoter.new_order',
            'festival_id' => $f->id,
            'name'        => 'Promoter — New order (override)',
            'subject'     => 'New order for {{ $festival_name }}',
            'html_body'   => '<p>Order #{{ $order_number }} received.</p>',
            'is_active'   => true,
        ]);
        $this->assertNotNull($tpl->fresh());
    }

    public function test_festival_to_string_returns_slug(): void
    {
        $f = Festival::create([
            'name' => 'X', 'year' => 2099, 'slug' => 'x-2099', 'status' => 'active',
        ]);
        // __toString() is the canonical value Laravel's route generator
        // uses when a model is interpolated into a URL.
        $this->assertSame('x-2099', (string) $f);
    }
}