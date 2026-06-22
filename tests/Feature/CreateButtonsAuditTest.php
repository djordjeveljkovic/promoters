<?php

namespace Tests\Feature;

use App\Models\Festival;
use App\Models\User;
use Tests\TestCase;

/**
 * Audit: probe every main page and print which "create" / "new"
 * buttons are visible on it.  Helps us spot pages that render but
 * are missing the affordance the user expects.
 *
 * Does NOT use RefreshDatabase — uses the seeded MySQL DB so the
 * routes that require a real festival/seed data work.
 */
class CreateButtonsAudit extends TestCase
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

    /**
     * @dataProvider pages
     */
    public function test_create_buttons(string $path, string $userEmail): void
    {
        $u = User::where('email', $userEmail)->first();
        if (!$u) { $this->markTestSkipped(); }
        $this->actingAs($u);

        $resp = $this->get($path);
        $status = $resp->getStatusCode();

        if ($status >= 400) {
            echo sprintf("\n  %-65s %s %3d", $path, 'ERR', $status);
            $this->assertTrue(true);
            return;
        }

        $body = $resp->getContent();

        // Extract the <main>...</main> content so we're only searching
        // the visible page body.  The full document includes Livewire
        // snapshot JSON with HTML-like fragments that confuse libxml,
        // causing strip_tags() to abort early and miss content further
        // down the page.
        if (preg_match('/<main[^>]*>(.*?)<\/main>/s', $body, $m)) {
            $body = $m[1];
        }
        $visible = strip_tags($body);

        $patterns = [
            'new festival'   => ['New festival', 'Create festival'],
            'new user'       => ['New user'],
            'new order'      => ['New order', 'Create Order'],
            'add promoter'   => ['Add promoter', 'Add Promoter'],
            'invite promoter'=> ['Invite promoter', 'Invite Promoter'],
            'new ticket type'=> ['New ticket type', 'Create ticket type', 'Create Ticket Type'],
            'add ticket'     => ['Add ticket'],
            'assign user'    => ['Assign user', 'Add a user'],
            'add sub-promo'  => ['Add sub-promoter'],
        ];
        $found = [];
        foreach ($patterns as $key => $needles) {
            foreach ($needles as $n) {
                if (stripos($visible, $n) !== false) {
                    $found[] = $key;
                    break;
                }
            }
        }

        echo sprintf("\n  %-65s OK     buttons=%s", $path, $found ? implode(',', $found) : 'NONE');
        $this->assertTrue(true);
    }

    public static function pages(): array
    {
        return [
            // Superadmin pages
            ['/superadmin/dashboard',                    'superadmin@refest.rs'],
            ['/superadmin/festivals',                     'superadmin@refest.rs'],
            ['/superadmin/users',                         'superadmin@refest.rs'],
            ['/superadmin/mail-templates',                'superadmin@refest.rs'],
            ['/superadmin/festivals/refest-2026/assignments', 'superadmin@refest.rs'],

            // Admin pages (festival-scoped)
            ['/admin/festivals',                                'refestrs@gmail.com'],
            ['/admin/festivals/refest-2026/dashboard',          'refestrs@gmail.com'],
            ['/admin/festivals/refest-2026/orders',             'refestrs@gmail.com'],
            ['/admin/festivals/refest-2026/promoters',           'refestrs@gmail.com'],
            ['/admin/festivals/refest-2026/ticket-types',        'refestrs@gmail.com'],
            ['/admin/festivals/refest-2026/promoter-managers',   'refestrs@gmail.com'],
            ['/admin/festivals/refest-2026/scan',                'refestrs@gmail.com'],

            // Promoter pages (festival-scoped)
            ['/promoter/festivals',                              'promoter@example.com'],
            ['/promoter/festivals/refest-2026/dashboard',        'promoter@example.com'],
            ['/promoter/festivals/refest-2026/orders',           'promoter@example.com'],
            ['/promoter/festivals/refest-2026/sub-promoters',    'promoter@example.com'],
        ];
    }
}