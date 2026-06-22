<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

\Illuminate\Support\Facades\DB::reconnect();
\Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);

$f = \App\Models\Festival::create(['name' => 'F', 'year' => 2026, 'slug' => 'f-2026', 'status' => 'active', 'primary_color' => '#000', 'secondary_color' => '#fff']);
$admin = \App\Models\User::create(['name' => 'A', 'email' => 'a@t', 'password' => bcrypt('x'), 'role' => 'admin']);
$admin->festivals()->attach($f->id, ['role_in_festival' => 'admin', 'assigned_by' => null, 'assigned_at' => now()]);
$promoter = \App\Models\User::create(['name' => 'P', 'email' => 'p@t', 'password' => bcrypt('x'), 'role' => 'promoter']);
$promoter->festivals()->attach($f->id, ['role_in_festival' => 'promoter', 'assigned_by' => null, 'assigned_at' => now()]);

auth()->login($admin);
$req = \Illuminate\Http\Request::create(
    route('admin.promoters.make-manager', ['festival' => 'f-2026', 'id' => $promoter->id]),
    'PUT'
);
$req->setUserResolver(fn() => $admin);
$resp = $app->make(\Illuminate\Contracts\Http\Kernel::class)->handle($req);
echo 'Status: ', $resp->getStatusCode(), PHP_EOL;
echo 'URL: ', route('admin.promoters.make-manager', ['festival' => 'f-2026', 'id' => $promoter->id]), PHP_EOL;
echo 'Body: ', substr($resp->getContent(), 0, 500), PHP_EOL;
