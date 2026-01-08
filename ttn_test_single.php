<?php

/**
 * TTN Japan API Test Script - Single Program
 * ทดสอบ sync TTN Japan API เฉพาะ program 381
 * 
 * Usage: php ttn_test_single.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\Backend\ApiManagementController;
use App\Models\Backend\ApiProviderModel;
use Illuminate\Support\Facades\Http;

echo "=== TTN Japan API Test - Program 381 ===\n";
echo "Testing single program with periods...\n\n";

try {
    // ทดสอบ period endpoint สำหรับ program 381
    echo "1. Testing period endpoint for program 381...\n";
    $periodUrl = 'https://online.ttnconnect.com/api/agency/program/period/381';
    
    $response = Http::withHeaders([
        "Content-Type" => "application/json; charset=UTF-8",
    ])->get($periodUrl);
    
    echo "   URL: $periodUrl\n";
    echo "   Status: " . $response->status() . "\n";
    echo "   Successful: " . ($response->successful() ? 'Yes' : 'No') . "\n";
    
    if ($response->successful()) {
        $periodData = $response->json();
        echo "   Response type: " . gettype($periodData) . "\n";
        echo "   Period count: " . (is_array($periodData) ? count($periodData) : 0) . "\n";
        
        if (is_array($periodData) && count($periodData) > 0) {
            echo "   First period structure:\n";
            $first = $periodData[0];
            echo "     Keys: " . implode(', ', array_keys($first)) . "\n";
            
            if (isset($first['Price']) && is_array($first['Price'])) {
                echo "     Price array count: " . count($first['Price']) . "\n";
                if (count($first['Price']) > 0) {
                    echo "     First price keys: " . implode(', ', array_keys($first['Price'][0])) . "\n";
                }
            }
        }
    } else {
        echo "   Error: " . $response->body() . "\n";
    }
    
    echo "\n2. Testing tour detail endpoint for program 381...\n";
    $tourUrl = 'https://online.ttnconnect.com/api/agency/program/381';
    
    $response2 = Http::withHeaders([
        "Content-Type" => "application/json; charset=UTF-8",
    ])->get($tourUrl);
    
    echo "   URL: $tourUrl\n";
    echo "   Status: " . $response2->status() . "\n";
    echo "   Successful: " . ($response2->successful() ? 'Yes' : 'No') . "\n";
    
    if ($response2->successful()) {
        $tourData = $response2->json();
        echo "   Response type: " . gettype($tourData) . "\n";
        echo "   Tour count: " . (is_array($tourData) ? count($tourData) : 0) . "\n";
        
        if (is_array($tourData) && count($tourData) > 0) {
            $tour = $tourData[0];
            echo "   Tour data:\n";
            echo "     P_ID: " . ($tour['P_ID'] ?? 'N/A') . "\n";
            echo "     P_CODE: " . ($tour['P_CODE'] ?? 'N/A') . "\n";
            echo "     P_NAME: " . substr($tour['P_NAME'] ?? 'N/A', 0, 50) . "...\n";
            echo "     BANNER: " . (isset($tour['BANNER']) ? 'Yes' : 'No') . "\n";
            echo "     PDF: " . (isset($tour['PDF']) ? 'Yes' : 'No') . "\n";
        }
    } else {
        echo "   Error: " . $response2->body() . "\n";
    }
    
    echo "\n✓ Test completed!\n";
    
} catch (\Exception $e) {
    echo "\n❌ Exception occurred:\n";
    echo "Message: {$e->getMessage()}\n";
    echo "File: {$e->getFile()}:{$e->getLine()}\n";
    exit(1);
}