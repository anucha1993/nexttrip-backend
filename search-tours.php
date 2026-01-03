<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Backend\TourModel;
use Illuminate\Support\Facades\DB;

echo "=== Searching for tours with similar code1 ===\n\n";

// Search by LIKE
$tours = TourModel::where('code1', 'LIKE', '%MMR16%')
    ->orWhere('code1', 'LIKE', '%MMR15%')
    ->whereNull('deleted_at')
    ->get(['id', 'code', 'code1', 'api_id', 'api_type', 'name']);

echo "Found " . $tours->count() . " tours:\n\n";

foreach ($tours as $tour) {
    echo "ID: {$tour->id}\n";
    echo "code: {$tour->code}\n";
    echo "code1: '{$tour->code1}'\n";
    echo "code1 (hex): " . bin2hex($tour->code1) . "\n";
    echo "api_id: {$tour->api_id}\n";
    echo "api_type: {$tour->api_type}\n";
    echo "name: " . substr($tour->name, 0, 50) . "...\n";
    echo "---\n\n";
}

// Check exact match with different cases
$exact = TourModel::whereRaw("BINARY code1 = 'BT-MMR16_8M'")->whereNull('deleted_at')->first();
echo "Exact match (case-sensitive): " . ($exact ? "FOUND ID={$exact->id}" : "NOT FOUND") . "\n";

// Show database connection
echo "\n=== Database Connection ===\n";
$dbConfig = config('database.connections.mysql');
echo "Host: {$dbConfig['host']}\n";
echo "Database: {$dbConfig['database']}\n";
echo "Username: {$dbConfig['username']}\n";

// Count total tours
$totalTours = TourModel::whereNull('deleted_at')->count();
echo "\nTotal active tours: {$totalTours}\n";
