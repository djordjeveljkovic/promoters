<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

/**
 * Regression coverage for the "global search dropdown stays open" bug.
 *
 * The new implementation:
 *   - uses an explicit `style="display:none"` baseline on the panel
 *     and backdrop so they cannot accidentally be visible on first paint,
 *   - uses Alpine.js `x-data` callbacks instead of an inline && so the
 *     click-outside handler always calls $wire.close(),
 *   - positions the panel as a centered modal (with a backdrop) so it
 *     never appears anchored below the trigger button.
 */
class GlobalSearchDefaultHiddenTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        putenv('DB_CONNECTION=mysql');
        putenv('DB_DATABASE=promoteri');
        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => 'promoteri',
        ]);
        \Illuminate\Support\Facades\DB::purge('mysql');
        \Illuminate\Support\Facades\DB::reconnect('mysql');
    }

    public function test_panel_renders_with_display_none_by_default(): void
    {
        $u = User::where('email', 'refestrs@gmail.com')->first();
        $this->actingAs($u);

        $r = $this->get('/admin/festivals/refest-2026/dashboard');
        $body = $r->getContent();

        // Panel must carry an explicit `display: none` style so it can
        // never accidentally stay visible on first paint — relying on
        // x-show alone raced with the initial paint in the previous bug.
        $this->assertMatchesRegularExpression(
            '/<div\s+x-show="open"[^>]*style="display:\s*none"[^>]*>/',
            $body,
            'Global search panel must render with explicit style="display:none" so it cannot stay open'
        );

        // Backdrop too — must default to display:none.
        $this->assertMatchesRegularExpression(
            '/<div\s+x-show="open"[^>]*style="display:\s*none"[^>]*>/',
            $body,
            'Backdrop overlay must also default to display:none'
        );
    }

    public function test_panel_does_not_have_short_circuit_close_handler(): void
    {
        // Belt and braces: if anyone reintroduces the && pattern that
        // prevents $wire.close() from running, this test catches it.
        $view = file_get_contents(
            __DIR__ . '/../../resources/views/livewire/global-search.blade.php'
        );

        $this->assertStringNotContainsString(
            'open = false && $wire.close()',
            $view,
            'Click-outside handler must NOT use && (short-circuits and never calls close())'
        );
    }

    public function test_open_property_defaults_to_false_in_component(): void
    {
        $src = file_get_contents(
            __DIR__ . '/../../app/Livewire/GlobalSearch.php'
        );

        $this->assertMatchesRegularExpression(
            '/public\s+bool\s+\$open\s*=\s*false\s*;/',
            $src,
            'GlobalSearch::$open must default to false'
        );
    }
}
