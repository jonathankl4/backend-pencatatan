<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$req = Illuminate\Http\Request::create('/api/expenses', 'GET', ['start_date' => '2026-06-15', 'end_date' => '2026-06-15']);
$req->setUserResolver(function() { return App\Models\User::find(1); });
$ctrl = app(App\Http\Controllers\Api\ExpenseController::class);

echo "Executing...\n";
$time = microtime(true);
$res = $ctrl->index($req);
$data = $res->toArray($req);
echo "Count: " . count($data) . "\n";
echo "Time: " . (microtime(true) - $time) . "\n";
