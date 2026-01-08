<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Backend\ApiManagementController;

echo "🔄 Starting ProBookingCenter Re-sync...\n";
echo "This will update tour prices from periods\n\n";

// Mock request
$request = new \Illuminate\Http\Request();
$request->merge(['id' => 56]); // ProBookingCenter provider ID

// Create controller and sync
$controller = new ApiManagementController();

try {
    $response = $controller->syncProvider($request);
    $data = $response->getData(true);
    
    echo "✅ Sync completed!\n";
    echo "  Created: " . ($data['tours']['created'] ?? 0) . " tours\n";
    echo "  Duplicated: " . ($data['tours']['duplicated'] ?? 0) . " tours\n";
    echo "  Periods Created: " . ($data['periods']['created'] ?? 0) . "\n";
    echo "  Periods Updated: " . ($data['periods']['updated'] ?? 0) . "\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

// Check Tour ID 125035 after sync
echo "\n=== Tour ID 125035 After Sync ===\n";
$tour = DB::table('tb_tour')->where('id', 125035)->first(['id', 'name', 'code1', 'price', 'special_price', 'price_group']);
echo "  Code1: {$tour->code1}\n";
echo "  Price: {$tour->price}\n";
echo "  Special Price: {$tour->special_price}\n";
echo "  Price Group: {$tour->price_group}\n";

// Check all ProBookingCenter tours with correct prices
$toursWithPrice = DB::table('tb_tour')
    ->where('wholesale_id', 39)
    ->whereNull('deleted_at')
    ->where('price', '>', 0)
    ->count();

$totalTours = DB::table('tb_tour')
    ->where('wholesale_id', 39)
    ->whereNull('deleted_at')
    ->count();

echo "\n📊 ProBookingCenter tours with price > 0: $toursWithPrice out of $totalTours\n";
