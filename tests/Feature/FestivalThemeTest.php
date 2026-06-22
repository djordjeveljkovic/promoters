<?php

namespace Tests\Feature;

use App\Models\Festival;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FestivalThemeTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_primary_color_is_returned_when_unset(): void
    {
        $festival = Festival::create([
            'name' => 'Test', 'year' => 2026, 'slug' => 'test-2026',
            'status' => 'active', 'primary_color' => '', 'secondary_color' => '',
        ]);

        $this->assertSame('#4f46e5', $festival->primaryColor());
        $this->assertSame('#818cf8', $festival->secondaryColor());
        $this->assertFalse($festival->hasCustomTheme());
    }

    public function test_custom_colors_are_returned_when_set(): void
    {
        $festival = Festival::create([
            'name' => 'REFEST', 'year' => 2026, 'slug' => 'refest-2026',
            'status' => 'active', 'primary_color' => '#dc2626', 'secondary_color' => '#fbbf24',
        ]);

        $this->assertSame('#dc2626', $festival->primaryColor());
        $this->assertSame('#fbbf24', $festival->secondaryColor());
        $this->assertTrue($festival->hasCustomTheme());
    }

    public function test_invalid_color_falls_back_to_default(): void
    {
        $festival = Festival::create([
            'name' => 'Bad', 'year' => 2026, 'slug' => 'bad-2026',
            'status' => 'active', 'primary_color' => 'not-a-color',
        ]);

        $this->assertSame('#4f46e5', $festival->primaryColor());
    }

    public function test_contrast_color_picks_white_on_dark_and_black_on_light(): void
    {
        $festival = new Festival();

        $this->assertSame('#ffffff', $festival->contrastColorOn('#dc2626')); // red → white
        $this->assertSame('#ffffff', $festival->contrastColorOn('#000000')); // black → white
        $this->assertSame('#0f172a', $festival->contrastColorOn('#ffffff')); // white → black
        $this->assertSame('#0f172a', $festival->contrastColorOn('#fde68a')); // light yellow → black
    }

    public function test_superadmin_can_save_a_custom_theme(): void
    {
        $admin = User::create([
            'name' => 'Admin', 'email' => 'a@t.test', 'password' => bcrypt('x'), 'role' => 'superadmin',
        ]);
        $this->actingAs($admin);

        $festival = Festival::create([
            'name' => 'Lovefest', 'year' => 2026, 'slug' => 'lovefest-2026',
            'status' => 'draft', 'primary_color' => '#000000', 'secondary_color' => '#000000',
        ]);

        // Submit the form with a hex value typed without the leading
        // "#" — the controller should normalise it before persisting.
        $response = $this->put("/superadmin/festivals/{$festival->id}", [
            'name'            => 'Lovefest',
            'year'            => 2026,
            'status'          => 'active',
            'is_public'       => 1,
            'primary_color'   => '#10b981',
            'secondary_color' => '#6ee7b7',
        ]);

        $response->assertRedirect(route('superadmin.festivals.index'));
        $festival->refresh();
        $this->assertSame('#10b981', $festival->primary_color);
        $this->assertSame('#6ee7b7', $festival->secondary_color);
    }

    public function test_hex_without_leading_hash_is_normalised(): void
    {
        $this->assertSame('#dc2626', Festival::normaliseColor('dc2626'));
        $this->assertSame('#dc2626', Festival::normaliseColor('#dc2626'));
        $this->assertSame('#dc2626', Festival::normaliseColor('  #DC2626 '));
        $this->assertNull(Festival::normaliseColor(''));
        $this->assertNull(Festival::normaliseColor(null));
        $this->assertNull(Festival::normaliseColor('   '));
    }

    public function test_invalid_hex_color_is_rejected(): void
    {
        $admin = User::create([
            'name' => 'Admin', 'email' => 'a@t.test', 'password' => bcrypt('x'), 'role' => 'superadmin',
        ]);
        $this->actingAs($admin);

        $festival = Festival::create([
            'name' => 'X', 'year' => 2026, 'slug' => 'x-2026',
            'status' => 'draft', 'primary_color' => '#000000', 'secondary_color' => '#000000',
        ]);

        $response = $this->put("/superadmin/festivals/{$festival->id}", [
            'name'            => 'X',
            'year'            => 2026,
            'status'          => 'active',
            'is_public'       => 1,
            'primary_color'   => 'not-a-color',
            'secondary_color' => '#6ee7b7',
        ]);

        $response->assertSessionHasErrors('primary_color');
    }
}
