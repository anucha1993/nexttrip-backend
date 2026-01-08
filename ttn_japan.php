<?php

/**
 * TTN Japan API Sync Script
 * ทดสอบ sync TTN Japan API 5 records
 * 
 * Usage: php ttn_japan.php
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
    $result = $controller->performSync($provider, 'manual', 5);
    
    echo "\n=== Sync Results ===\n";
    
    if (is_array($result) && isset($result['summary'])) {
        echo "Status: ✓ SUCCESS\n";
        echo "Log ID: {$result['log_id']}\n";
        
        $summary = $result['summary'];
        echo "\nStatistics:\n";
        echo "  Created Tours: " . ($summary['created_tours'] ?? 0) . "\n";
        echo "  Updated Tours: " . ($summary['updated_tours'] ?? 0) . "\n";
        echo "  Duplicated Tours: " . ($summary['duplicated_tours'] ?? 0) . "\n";
        echo "  Error Count: " . ($summary['error_count'] ?? 0) . "\n";
        
    } else {
        echo "Status: ❌ FAILED\n";
        echo "Result: " . json_encode($result, JSON_PRETTY_PRINT) . "\n";
    }
    
    echo "\n✓ Sync completed!\n";
    
} catch (\Exception $e) {
    echo "\n❌ Exception occurred:\n";
    echo "Message: {$e->getMessage()}\n";
    echo "File: {$e->getFile()}:{$e->getLine()}\n";
    echo "\nStack trace:\n{$e->getTraceAsString()}\n";
    exit(1);
}