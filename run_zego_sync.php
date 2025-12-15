<?php

/**
 * Zego API Sync Script
 * ทดสอบ sync Zego API 1 record
 * 
 * Usage: php run_zego_sync.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\Backend\ApiManagementController;
use App\Models\Backend\ApiProviderModel;

echo "=== Zego API Sync Test ===\n";
echo "Starting sync...\n\n";

try {
    // หา Zego API Provider
    $provider = ApiProviderModel::where('code', 'zego')->first();
    
    if (!$provider) {
        echo "❌ Error: Zego API provider not found!\n";
        exit(1);
    }
    
    echo "✓ Found provider: {$provider->name} (ID: {$provider->id})\n";
    echo "  URL: {$provider->url}\n";
    echo "  Status: {$provider->status}\n\n";
    
    // เรียก sync
    $controller = new ApiManagementController();
    $result = $controller->performSync($provider, 'manual', 1);
    
    echo "\n=== Sync Results ===\n";
    echo "Status: ✓ SUCCESS\n";
    
    if (isset($result['summary'])) {
        $summary = $result['summary'];
        echo "\nStatistics:\n";
        echo "  Total Records: {$summary['total_records']}\n";
        echo "  Tours Created: {$summary['created_tours']}\n";
        echo "  Tours Duplicated: {$summary['duplicated_tours']}\n";
        echo "  Errors: {$summary['error_count']}\n";
    }
    
    if (isset($result['log_id'])) {
        echo "\nSync Log ID: {$result['log_id']}\n";
    }
    
    echo "\n✓ Sync completed!\n";
    
} catch (\Exception $e) {
    echo "\n❌ Exception occurred:\n";
    echo "Message: {$e->getMessage()}\n";
    echo "File: {$e->getFile()}:{$e->getLine()}\n";
    echo "\nStack trace:\n{$e->getTraceAsString()}\n";
    exit(1);
}
