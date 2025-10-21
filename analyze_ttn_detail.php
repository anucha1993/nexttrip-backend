<?php

require_once __DIR__ . '/vendor/autoload.php';

// Initialize Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Analyzing TTN Japan Detail API Structure ===\n\n";

try {
    $tourId = 296;
    $detailUrl = "https://online.ttnconnect.com/api/agency/program/{$tourId}";
    
    echo "🔍 Fetching: {$detailUrl}\n\n";
    
    $context = stream_context_create([
        'http' => [
            'timeout' => 30,
            'user_agent' => 'NextTrip-Backend/1.0'
        ]
    ]);
    
    $response = file_get_contents($detailUrl, false, $context);
    if (!$response) {
        echo "❌ Failed to fetch data\n";
        exit(1);
    }
    
    $data = json_decode($response, true);
    if (!$data) {
        echo "❌ Invalid JSON\n";
        echo "Raw response: " . substr($response, 0, 1000) . "...\n";
        exit(1);
    }
    
    echo "✅ Success! Data structure:\n\n";
    
    // Show all fields
    echo "📋 All Fields:\n";
    foreach ($data as $key => $value) {
        $type = gettype($value);
        if (is_array($value)) {
            $type .= " (" . count($value) . " items)";
            if (count($value) > 0 && is_array($value[0])) {
                $firstKeys = array_keys($value[0]);
                $type .= " - First item keys: " . implode(', ', array_slice($firstKeys, 0, 5));
            }
        } elseif (is_string($value)) {
            $type .= " (" . strlen($value) . " chars)";
        }
        
        $displayValue = is_array($value) ? '[Array]' : 
                       (is_string($value) && strlen($value) > 100 ? substr($value, 0, 100) . '...' : $value);
        
        echo "   {$key}: {$displayValue} ({$type})\n";
    }
    
    // Look for period-related arrays
    echo "\n🗓️ Period-related Fields Analysis:\n";
    $periodArrays = [];
    
    foreach ($data as $key => $value) {
        if (is_array($value) && !empty($value)) {
            // Check if this could be periods
            $firstItem = $value[0];
            if (is_array($firstItem)) {
                $keys = array_keys($firstItem);
                $hasPeriodFields = false;
                
                foreach (['date', 'start', 'end', 'price', 'day', 'night', 'available', 'period'] as $periodKeyword) {
                    foreach ($keys as $key2) {
                        if (stripos($key2, $periodKeyword) !== false) {
                            $hasPeriodFields = true;
                            break 2;
                        }
                    }
                }
                
                if ($hasPeriodFields) {
                    $periodArrays[$key] = $value;
                    echo "   🎯 {$key}: " . count($value) . " items - LOOKS LIKE PERIODS!\n";
                    echo "      Keys: " . implode(', ', $keys) . "\n";
                    
                    // Show first few periods
                    for ($i = 0; $i < min(3, count($value)); $i++) {
                        echo "      Period " . ($i + 1) . ":\n";
                        foreach ($value[$i] as $k => $v) {
                            echo "         {$k}: {$v}\n";
                        }
                        echo "\n";
                    }
                }
            }
        }
    }
    
    if (empty($periodArrays)) {
        echo "   ❌ No period arrays found!\n";
        echo "   💡 Periods might be in a different format or endpoint\n";
    }
    
    // Check the current field mappings
    echo "\n🗂️ Current Period Field Mappings:\n";
    $currentMappings = [
        'period_api_id' => 'P_ID',
        'start_date' => 'P_DUE_START', 
        'end_date' => 'P_DUE_END',
        'price1' => 'P_ADULT_PRICE',
        'price2' => 'P_SINGLE_PRICE',
        'day' => 'P_DAY',
        'night' => 'P_NIGHT',
        'group' => 'P_VOLUME',
        'count' => 'P_AVAILABLE'
    ];
    
    foreach ($currentMappings as $local => $api) {
        $found = isset($data[$api]) ? '✅' : '❌';
        echo "   {$found} {$local} ← {$api}\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}