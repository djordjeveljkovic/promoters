<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Testing\TestResponse;
use Illuminate\Foundation\Application;

$req = \Illuminate\Http\Request::create(
    '/admin/festivals/f-2026/promoter/2/make-manager',
    'PUT'
);

// Check what route it matches
$route = app('router')->getRoutes()->match($req);
echo "Matched route: " . ($route ? $route->getName() : 'none') . "\n";
echo "URI: " . ($route ? $route->uri() : 'none') . "\n";
echo "Methods: " . ($route ? implode(',', $route->methods()) : 'none') . "\n";
