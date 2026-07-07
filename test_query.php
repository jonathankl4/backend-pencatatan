<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$s = \Carbon\Carbon::parse('2026-06-01')->startOfDay();
$e = \Carbon\Carbon::parse('2026-06-30')->endOfDay();
$q = App\Models\Sale::where('user_id', 1)->whereBetween('sale_date', [$s, $e]);

echo "Count: " . $q->count() . "\n";
echo "SQL: " . $q->toSql() . "\n";
print_r($q->getBindings());
