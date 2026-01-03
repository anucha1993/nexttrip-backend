<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Backend\TourModel;
use Illuminate\Support\Facades\DB;

$code1Value = 'BT-MMR16_8M';

echo "Testing different query methods:\n\n";

// Method 1: Direct where
$tour1 = TourModel::where('code1', $code1Value)->whereNull('deleted_at')->first();
echo "1. Direct where: " . ($tour1 ? "FOUND ID={$tour1->id}" : "NOT FOUND") . "\n";

// Method 2: With transaction (without lock)
$tour2 = DB::transaction(function() use ($code1Value) {
    return TourModel::where('code1', $code1Value)->whereNull('deleted_at')->first();
});
echo "2. Transaction without lock: " . ($tour2 ? "FOUND ID={$tour2->id}" : "NOT FOUND") . "\n";

// Method 3: With transaction and lock
$tour3 = DB::transaction(function() use ($code1Value) {
    return TourModel::where('code1', $code1Value)->whereNull('deleted_at')->lockForUpdate()->first();
});
echo "3. Transaction with lock: " . ($tour3 ? "FOUND ID={$tour3->id}" : "NOT FOUND") . "\n";

// Method 4: Using DB query builder
$tour4 = DB::table('tb_tour')->where('code1', $code1Value)->whereNull('deleted_at')->first();
echo "4. DB query builder: " . ($tour4 ? "FOUND ID={$tour4->id}" : "NOT FOUND") . "\n";

// Method 5: Raw query
$tour5 = DB::selectOne("SELECT * FROM tb_tour WHERE code1 = ? AND deleted_at IS NULL LIMIT 1", [$code1Value]);
echo "5. Raw query: " . ($tour5 ? "FOUND ID={$tour5->id}" : "NOT FOUND") . "\n";

// Show actual code1 values in database
echo "\n=== Actual code1 values in database ===\n";
$tours = DB::table('tb_tour')
    ->where('code1', 'LIKE', '%MMR16%')
    ->whereNull('deleted_at')
    ->get(['id', 'code1']);
    
foreach ($tours as $t) {
    echo "ID={$t->id}, code1='{$t->code1}', length=" . strlen($t->code1) . ", hex=" . bin2hex($t->code1) . "\n";
}

echo "\nSearching code1 length: " . strlen($code1Value) . ", hex: " . bin2hex($code1Value) . "\n";
