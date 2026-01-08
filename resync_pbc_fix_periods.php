<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Backend\ApiManagementController;

echo "🔄 Re-syncing ProBookingCenter to fix periods...\n\n";

// Delete existing period for Tour 125035 to start fresh
DB::table('tb_tour_period')
    ->where('tour_id', 125035)
    ->whereNull('deleted_at')
    ->update(['deleted_at' => now()]);

echo "Deleted existing periods for Tour 125035\n\n";

// Mock request
$request = new \Illuminate\Http\Request();
$request->merge(['id' => 56]); // ProBookingCenter provider ID

// Create controller and sync
$controller = new ApiManagementController();

try {
    $response = $controller->syncProvider($request);
    $data = $response->getData(true);
    
    echo "✅ Sync completed!\n";
    echo "  Created Tours: " . ($data['tours']['created'] ?? 0) . "\n";
    echo "  Duplicated Tours: " . ($data['tours']['duplicated'] ?? 0) . "\n";
    echo "  Created Periods: " . ($data['periods']['created'] ?? 0) . "\n";
    echo "  Updated Periods: " . ($data['periods']['updated'] ?? 0) . "\n\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n\n";
}

// Check periods for Tour 125035
$periods = DB::table('tb_tour_period')
    ->where('tour_id', 125035)
    ->whereNull('deleted_at')
    ->orderBy('start_date')
    ->get(['id', 'period_code', 'start_date', 'end_date', 'price1', 'special_price1']);

echo "=== Tour ID 125035 Periods After Sync ===\n";
echo "Total: " . $periods->count() . " periods\n\n";

foreach ($periods as $period) {
    $periodCode = substr($period->period_code, 0, 30) . '...';
    echo "Period ID {$period->id}:\n";
    echo "  Code: {$periodCode}\n";
    echo "  Dates: {$period->start_date} to {$period->end_date}\n";
    echo "  Price1: {$period->price1}, Special: {$period->special_price1}\n";
    echo "  ---\n";
}

// Check tour price
$tour = DB::table('tb_tour')->where('id', 125035)->first(['id', 'code1', 'price', 'special_price']);
echo "\n=== Tour Price ===\n";
echo "  Code1: {$tour->code1}\n";
echo "  Price: {$tour->price}\n";
echo "  Special Price: {$tour->special_price}\n";
