<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Backend\ApiManagementController;

// Clear log file
file_put_contents(storage_path('logs/laravel.log'), '');

// Get provider
$provider = DB::table('tb_api_providers')->where('id', 56)->first();
if (!$provider) {
    die("ProBookingCenter provider not found\n");
}

// Create controller
$controller = new ApiManagementController();

// Call sync provider (will sync all tours)
echo "Syncing ProBookingCenter provider...\n";
try {
    // Mock request
    $request = new \Illuminate\Http\Request();
    $request->merge(['id' => 56]);
    
    $result = $controller->syncProvider($request);
    echo "Sync completed\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// Show log entries for PJPCTS15-SL
echo "\n\n=== LOG ENTRIES FOR PJPCTS15-SL ===\n";
$log = file_get_contents(storage_path('logs/laravel.log'));
$lines = explode("\n", $log);
foreach ($lines as $line) {
    if (stripos($line, 'PJPCTS15-SL') !== false || stripos($line, 'period') !== false) {
        echo $line . "\n";
    }
}
