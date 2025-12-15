<?php

/**
 * TTN Japan API Sync Script
 * ทดสอบ sync TTN Japan API 1 record
 * 
 * Usage: php run_ttn_sync.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\Backend\ApiManagementController;
use App\Models\Backend\ApiProviderModel;

echo "=== TTN Japan API Sync Test ===\n";
echo "Starting sync...\n\n";

try {
    // หา TTN Japan API Provider
    $provider = ApiProviderModel::where('code', 'ttn_japan')->first();
    
    if (!$provider) {
        echo "❌ Error: TTN Japan API provider not found!\n";
        exit(1);
    }
    
    echo "✓ Found provider: {$provider->name} (ID: {$provider->id})\n";
    echo "  URL: {$provider->url}\n";
    echo "  Status: {$provider->status}\n";
    echo "  Multi-step: " . ($provider->requires_multi_step ? 'Yes' : 'No') . "\n\n";
    
    // เรียก sync
    $controller = new ApiManagementController();
    $result = $controller->performSync($provider, 'manual', 1);
    
    echo "\n=== Sync Results ===\n";
    echo "Status: " . ($result['success'] ? '✓ SUCCESS' : '❌ FAILED') . "\n";
    echo "Message: {$result['message']}\n";
    
    if (isset($result['stats'])) {
        $stats = $result['stats'];
        echo "\nStatistics:\n";
        echo "  Tours Created: {$stats['tours_created']}\n";
        echo "  Tours Updated: {$stats['tours_updated']}\n";
        echo "  Tours Skipped: {$stats['tours_skipped']}\n";
        echo "  Errors: {$stats['errors']}\n";
        
        if (isset($stats['periods_created'])) {
            echo "  Periods Created: {$stats['periods_created']}\n";
        }
        if (isset($stats['periods_updated'])) {
            echo "  Periods Updated: {$stats['periods_updated']}\n";
        }
    }
    
    if (isset($result['error_details']) && !empty($result['error_details'])) {
        echo "\nError Details:\n";
        foreach ($result['error_details'] as $error) {
            echo "  - {$error}\n";
        }
    }
    
    echo "\n✓ Sync completed!\n";
    
} catch (\Exception $e) {
    echo "\n❌ Exception occurred:\n";
    echo "Message: {$e->getMessage()}\n";
    echo "File: {$e->getFile()}:{$e->getLine()}\n";
    echo "\nStack trace:\n{$e->getTraceAsString()}\n";
    exit(1);
}
