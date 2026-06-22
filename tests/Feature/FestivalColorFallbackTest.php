<?php

namespace Tests\Feature;

use App\Models\Festival;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression coverage for B-003:
 *   Creating a festival without `primary_color` / `secondary_color`
 *   should not violate the NOT NULL constraint on the columns.
 *
 *   Before the fix, the controller assigned `null` to the colour fields
 *   (via Festival::normaliseColor(null) → null), which caused MySQL to
 *   reject the INSERT.  The fix only sets the colour when the normalised
 *   value is a real hex string; otherwise the column default kicks in.
 */
class FestivalColorFallbackTest extends TestCase
{
    use RefreshDatabase;

    private User $superadmin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->superadmin = User::create([
            'name'     => 'Root',
            'email'    => 'root@test.local',
            'password' => bcrypt('x'),
            'role'     => 'superadmin',
        ]);
        $this->actingAs($this->superadmin);
    }

    public function test_create_festival_with_no_colors_falls_back_to_defaults(): void
    {
        $r = $this->post('/superadmin/festivals', [
            'name'    => 'NoColours',
            'year'    => 2099,
            'tagline' => 'no colours here',
            'status'  => 'draft',
            // No primary_color, no secondary_color
        ]);

        $r->assertRedirect(route('superadmin.festivals.index'));

        $f = Festival::where('name', 'NoColours')->firstOrFail();
        $this->assertNotNull($f->primary_color, 'primary_color should fall back to column default');
        $this->assertNotNull($f->secondary_color, 'secondary_color should fall back to column default');
    }

    public function test_create_festival_with_explicit_null_colors_uses_defaults(): void
    {
        $r = $this->post('/superadmin/festivals', [
            'name'           => 'NullColours',
            'year'           => 2099,
            'status'         => 'draft',
            'primary_color'  => null,
            'secondary_color'=> null,
        ]);

        $r->assertRedirect(route('superadmin.festivals.index'));

        $f = Festival::where('name', 'NullColours')->firstOrFail();
        $this->assertNotNull($f->primary_color);
        $this->assertNotNull($f->secondary_color);
    }

    public function test_create_festival_with_explicit_colors_uses_them(): void
    {
        $r = $this->post('/superadmin/festivals', [
            'name'           => 'HasColours',
            'year'           => 2099,
            'status'         => 'draft',
            'primary_color'  => '#ff0000',
            'secondary_color'=> '#00ff00',
        ]);

        $r->assertRedirect(route('superadmin.festivals.index'));

        $f = Festival::where('name', 'HasColours')->firstOrFail();
        $this->assertSame('#ff0000', $f->primary_color);
        $this->assertSame('#00ff00', $f->secondary_color);
    }

    public function test_update_festival_with_null_colors_preserves_defaults(): void
    {
        $f = Festival::create([
            'name'           => 'Existing',
            'year'           => 2099,
            'slug'           => 'existing-2099',
            'status'         => 'draft',
            'primary_color'  => '#123456',
            'secondary_color'=> '#654321',
        ]);

        // Now update without sending the colour fields.
        $r = $this->put("/superadmin/festivals/{$f->slug}", [
            'name'   => 'Existing Renamed',
            'year'   => 2099,
            'status' => 'draft',
        ]);

        $r->assertRedirect(route('superadmin.festivals.index'));

        $f->refresh();
        $this->assertSame('Existing Renamed', $f->name);
        // Colours should still be set (defaults if absent).
        $this->assertNotNull($f->primary_color);
        $this->assertNotNull($f->secondary_color);
    }
}