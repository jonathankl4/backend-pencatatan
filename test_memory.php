<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$req = Illuminate\Http\Request::create('/api/sales', 'GET', ['start_date' => '2026-06-01', 'end_date' => '2026-06-30']);
$req->setUserResolver(function() { return App\Models\User::find(1); });
$ctrl = app(App\Http\Controllers\Api\SaleController::class);

try {
    $res = $ctrl->index($req);
    $data = $res->toArray($req);
    echo "Success! Count: " . count($data) . "\n";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
