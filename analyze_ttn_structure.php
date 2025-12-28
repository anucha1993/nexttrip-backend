<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

echo "=== TTN Japan API Structure Analysis ===\n";

try {
    // Test API connection
    echo "Getting TTN Japan programs...\n";
    
    $response = Http::withHeaders([
        "Content-Type" => "application/json; charset=UTF-8",
    ])->get('https://online.ttnconnect.com/api/agency/get-programId');
    
    if (!$response->successful()) {
        echo "ERROR: API connection failed: " . $response->status() . "\n";
        exit(1);
    }
    
    $programs = $response->json();
    echo "Found " . count($programs) . " programs\n\n";
    
    if (count($programs) > 0) {
        echo "First program structure:\n";
        print_r($programs[0]);
        echo "\n";
        
        $programId = $programs[0]['P_ID'];
        
        // Get program details
        echo "Getting program details for ID: $programId\n";
        $detailResponse = Http::withHeaders([
            "Content-Type" => "application/json; charset=UTF-8",
        ])->get("https://online.ttnconnect.com/api/agency/get-program/$programId");
        
        if ($detailResponse->successful()) {
            $detail = $detailResponse->json();
            echo "Program detail structure:\n";
            print_r($detail);
            echo "\n";
            
            // Get periods
            echo "Getting periods for program ID: $programId\n";
            $periodResponse = Http::withHeaders([
                "Content-Type" => "application/json; charset=UTF-8",
            ])->get("https://online.ttnconnect.com/api/agency/get-period/$programId");
            
            if ($periodResponse->successful()) {
                $periods = $periodResponse->json();
                echo "Found " . count($periods) . " periods\n";
                if (count($periods) > 0) {
                    echo "First period structure:\n";
                    print_r($periods[0]);
                }
            } else {
                echo "Failed to get periods: " . $periodResponse->status() . "\n";
                echo "Response: " . $periodResponse->body() . "\n";
            }
        } else {
            echo "Failed to get program details: " . $detailResponse->status() . "\n";
            echo "Response: " . $detailResponse->body() . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== Analysis Completed ===\n";