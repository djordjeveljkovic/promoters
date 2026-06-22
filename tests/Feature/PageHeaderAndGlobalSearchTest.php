<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

/**
 * Regression tests for two recently-fixed UI bugs.
 *
 *   1. <x-ds.page-header> was silently dropping the contents of its
 *      <x-slot:actions> because the component was checking the wrong
 *      variable ($slot = the default slot, not the named one).  All
 *      "Add promoter", "Invite promoter", "New order", "Create Order"
 *      buttons in the admin/promoter indexes vanished as a result.
 *
 *   2. The global-search dropdown used `open = false && $wire.close()`
 *      on @click.outside / @keydown.escape.  JavaScript's `&&`
 *      short-circuits, so $wire.close() never ran and the panel
 *      snapped right back open on the next Livewire re-render.
 */
class PageHeaderAndGlobalSearchTest extends TestCase
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

        if (!User::where('email', 'superadmin@refest.rs')->exists()) {
            $this->artisan('db:seed', ['--force' => true]);
        }
    }

    public function test_admin_promoters_index_shows_add_promoter_action_button(): void
    {
        $u = User::where('email', 'refestrs@gmail.com')->first();
        $this->actingAs($u);
        app()->setLocale('en');

        $r = $this->get('/admin/festivals/refest-2026/promoters');
        $r->assertOk();

        $body = $r->getContent();
        if (preg_match('/<main[^>]*>(.*?)<\/main>/s', $body, $m)) {
            $body = $m[1];
        }
        $visible = strip_tags($body);

        $this->assertStringContainsString('Add Promoter', $visible,
            'Add Promoter button should be visible in the page header actions');

        // The action slot must NOT also be empty (would mean the slot
        // variable went back to being checked on the wrong name).
        $this->assertStringContainsString('Manager rates', $visible,
            'Manager rates button (also in the same slot) should be visible');
    }

    public function test_admin_orders_index_shows_create_order_action(): void
    {
        $u = User::where('email', 'refestrs@gmail.com')->first();
        $this->actingAs($u);
        app()->setLocale('en');

        $r = $this->get('/admin/festivals/refest-2026/orders');
        $r->assertOk();

        $body = $r->getContent();
        if (preg_match('/<main[^>]*>(.*?)<\/main>/s', $body, $m)) {
            $body = $m[1];
        }
        $visible = strip_tags($body);

        $this->assertStringContainsString('Create Order', $visible,
            'Create Order button should be visible in the page header actions');
    }

    public function test_admin_ticket_types_index_shows_new_ticket_type_action(): void
    {
        $u = User::where('email', 'refestrs@gmail.com')->first();
        $this->actingAs($u);
        app()->setLocale('en');

        $r = $this->get('/admin/festivals/refest-2026/ticket-types');
        $r->assertOk();

        $body = $r->getContent();
        if (preg_match('/<main[^>]*>(.*?)<\/main>/s', $body, $m)) {
            $body = $m[1];
        }
        $visible = strip_tags($body);

        $this->assertStringContainsString('Create Ticket Type', $visible);
    }

    public function test_global_search_close_uses_semicolon_not_and(): void
    {
        // The previous bug used `open = false && $wire.close()`.
        // The `&&` short-circuited and $wire.close() never ran, so the
        // dropdown snapped back open on the next Livewire re-render.
        // Verify the markup uses the sequence (semicolon) form.
        $view = file_get_contents(
            __DIR__ . '/../../resources/views/livewire/global-search.blade.php'
        );

        // Should NOT contain the broken pattern.
        $this->assertStringNotContainsString(
            'open = false && $wire.close()',
            $view,
            'Global search still uses the && short-circuit pattern that prevents close() from running'
        );

        // Should contain the fixed pattern.
        $this->assertStringContainsString(
            'open = false; $wire.close()',
            $view,
            'Global search should use a semicolon so both statements always run'
        );
    }
}