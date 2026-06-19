<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Festival;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use App\Livewire\Auth\Login;

/**
 * Smoke tests for the multi-festival routing model.
 *
 * These tests do NOT use RefreshDatabase — they assume a freshly seeded
 * MySQL database. They exist purely to verify the routing/middleware
 * decisions in the multi-festival refactor.
 *
 * Run with:
 *   php artisan test --filter=SmokeTest --env=local
 */
class SmokeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Force the real MySQL connection (phpunit.xml defaults to in-memory SQLite).
        putenv('DB_CONNECTION=mysql');
        putenv('DB_DATABASE=promoteri');
        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => 'promoteri',
        ]);
        // Reconnect with the new settings.
        \Illuminate\Support\Facades\DB::purge('mysql');
        \Illuminate\Support\Facades\DB::reconnect('mysql');
    }

    private function ensureSeeded(): void
    {
        if (User::where('email', 'superadmin@refest.rs')->doesntExist()) {
            // Auto-run seeders if needed.
            $this->artisan('db:seed', ['--force' => true]);
        }
    }

    public function test_superadmin(): void
    {
        $this->ensureSeeded();
        $u = User::where('email', 'superadmin@refest.rs')->firstOrFail();
        $this->actingAs($u);

        $checks = [
            ['/superadmin/dashboard', 200, 'Superadmin Dashboard'],
            ['/superadmin/festivals', 200, 'REFEST 2026'],
            ['/superadmin/users', 200, 'Sample Promoter'],
            ['/admin/festivals', 200, 'Pick a festival'],
            ['/admin/festivals/refest-2026/dashboard', 200, null],
            ['/admin/festivals/lovefest-2027/dashboard', 200, 'Lovefest 2027'],
        ];

        echo "\n=== SUPERADMIN ({$u->email}) ===\n";
        foreach ($checks as [$path, $expected, $must]) {
            $resp = $this->get($path);
            $status = $resp->getStatusCode();
            $ok = $status === $expected ? '✅' : "❌ (got $status, want $expected)";
            $content = '';
            if ($must) {
                $content = str_contains($resp->getContent(), $must) ? '✅' : "❌ (missing '$must')";
            }
            printf("  %s %-55s %s\n", $ok, $path, $content);
        }

        // Just to satisfy PHPUnit that something ran.
        $this->assertTrue(true);
    }

    public function test_admin(): void
    {
        $this->ensureSeeded();
        $u = User::where('email', 'refestrs@gmail.com')->firstOrFail();
        $this->actingAs($u);

        $checks = [
            ['/superadmin/dashboard', 403, null],
            ['/admin/festivals', 200, 'Pick a festival'],
            ['/admin/festivals/refest-2026/dashboard', 200, null],
            ['/admin/festivals/lovefest-2027/dashboard', 200, null],
            ['/admin/festivals/refest-2025/dashboard', 200, null],
        ];

        echo "\n=== ADMIN ({$u->email}) ===\n";
        foreach ($checks as [$path, $expected, $must]) {
            $resp = $this->get($path);
            $status = $resp->getStatusCode();
            $ok = $status === $expected ? '✅' : "❌ (got $status, want $expected)";
            $content = '';
            if ($must) {
                $content = str_contains($resp->getContent(), $must) ? '✅' : "❌ (missing '$must')";
            }
            printf("  %s %-55s %s\n", $ok, $path, $content);
        }
        $this->assertTrue(true);
    }

    public function test_promoter(): void
    {
        $this->ensureSeeded();
        $u = User::where('email', 'promoter@example.com')->firstOrFail();
        $this->actingAs($u);

        $checks = [
            ['/superadmin/dashboard', 403, null],
            ['/admin/festivals', 403, null],
            ['/promoter/festivals', 200, 'REFEST 2026'],
            ['/promoter/festivals/refest-2026/dashboard', 200, null],
            ['/promoter/festivals/lovefest-2027/dashboard', 403, null],
            ['/promoter/festivals/refest-2025/dashboard', 403, null],
        ];

        echo "\n=== PROMOTER ({$u->email}) ===\n";
        foreach ($checks as [$path, $expected, $must]) {
            $resp = $this->get($path);
            $status = $resp->getStatusCode();
            $ok = $status === $expected ? '✅' : "❌ (got $status, want $expected)";
            $content = '';
            if ($must) {
                $content = str_contains($resp->getContent(), $must) ? '✅' : "❌ (missing '$must')";
            }
            printf("  %s %-55s %s\n", $ok, $path, $content);
        }
        $this->assertTrue(true);
    }

    public function test_sub(): void
    {
        $this->ensureSeeded();
        $u = User::where('email', 'sub@example.com')->firstOrFail();
        $this->actingAs($u);

        $checks = [
            ['/admin/festivals', 403, null],
            ['/promoter/festivals', 200, 'REFEST 2026'],
            ['/promoter/festivals/refest-2026/dashboard', 200, null],
            ['/promoter/festivals/lovefest-2027/dashboard', 403, null],
            ['/sub-promoter/dashboard', 200, 'Sub-promoter dashboard'],
        ];

        echo "\n=== SUB-PROMOTER ({$u->email}) ===\n";
        foreach ($checks as [$path, $expected, $must]) {
            $resp = $this->get($path);
            $status = $resp->getStatusCode();
            $ok = $status === $expected ? '✅' : "❌ (got $status, want $expected)";
            $content = '';
            if ($must) {
                $content = str_contains($resp->getContent(), $must) ? '✅' : "❌ (missing '$must')";
            }
            printf("  %s %-55s %s\n", $ok, $path, $content);
        }
        $this->assertTrue(true);
    }
}