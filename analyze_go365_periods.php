<?php

require_once __DIR__ . '/vendor/autoload.php';

// Initialize Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Backend\ApiProviderModel;

echo "=== GO365 Period Data Analysis ===\n\n";

try {
    $provider = ApiProviderModel::where('code', 'go365')->first();
    $headers = $provider->headers;
    
    // Get tour detail
    $detailUrl = "https://api.kaikongservice.com/api/v1/tours/detail/9002";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $detailUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'x-api-key: ' . $headers['x-api-key'],
        'Content-Type: application/json'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == 200) {
        $data = json_decode($response, true);
        
        if (isset($data['data'][0])) {
            $tourDetail = $data['data'][0];
            
            echo "🔍 Tour Information:\n";
            echo "   Tour ID: " . $tourDetail['tour_id'] . "\n";
            echo "   Tour Code: " . $tourDetail['tour_code'] . "\n";
            echo "   Days: " . $tourDetail['tour_num_day'] . "\n";
            echo "   Nights: " . $tourDetail['tour_num_night'] . "\n\n";
            
            if (isset($tourDetail['tour_period']) && is_array($tourDetail['tour_period'])) {
                $periods = $tourDetail['tour_period'];
                echo "🗓️ Period Analysis:\n";
                echo "   Total periods: " . count($periods) . "\n\n";
                
                if (!empty($periods)) {
                    $samplePeriod = $periods[0];
                    echo "   Sample Period Structure:\n";
                    
                    foreach ($samplePeriod as $key => $value) {
                        $displayValue = is_array($value) ? "[array with " . count($value) . " items]" : $value;
                        echo "     {$key}: {$displayValue}\n";
                    }
                    
                    echo "\n🧪 Period Field Analysis:\n";
                    
                    // Check required fields for processing
                    $requiredFields = [
                        'period_id' => 'Period ID',
                        'period_date' => 'Start Date',
                        'period_back' => 'End Date',
                        'period_rate_adult_twn' => 'Adult Twin Rate',
                        'period_rate_adult_sgl' => 'Adult Single Rate',
                        'period_quota' => 'Quota',
                        'period_available' => 'Available',
                        'period_visible' => 'Visible Status'
                    ];
                    
                    foreach ($requiredFields as $field => $desc) {
                        if (isset($samplePeriod[$field])) {
                            echo "   ✅ {$desc} ({$field}): " . $samplePeriod[$field] . "\n";
                        } else {
                            echo "   ❌ {$desc} ({$field}): MISSING\n";
                        }
                    }
                    
                    // Check a few more periods for consistency
                    echo "\n📊 Period Sample Data (first 3 periods):\n";
                    for ($i = 0; $i < min(3, count($periods)); $i++) {
                        $period = $periods[$i];
                        echo "   Period " . ($i + 1) . ":\n";
                        echo "     ID: " . ($period['period_id'] ?? 'NULL') . "\n";
                        echo "     Date: " . ($period['period_date'] ?? 'NULL') . "\n";
                        echo "     Back: " . ($period['period_back'] ?? 'NULL') . "\n";
                        echo "     Twin Rate: " . ($period['period_rate_adult_twn'] ?? 'NULL') . "\n";
                        echo "     Single Rate: " . ($period['period_rate_adult_sgl'] ?? 'NULL') . "\n";
                        echo "     Available: " . ($period['period_available'] ?? 'NULL') . "\n";
                        echo "     Visible: " . ($period['period_visible'] ?? 'NULL') . "\n";
                        echo "\n";
                    }
                    
                } else {
                    echo "   ❌ No periods in tour_period array\n";
                }
            } else {
                echo "   ❌ No tour_period key found\n";
                echo "   Available keys: " . implode(', ', array_keys($tourDetail)) . "\n";
            }
        } else {
            echo "❌ No tour data found\n";
        }
    } else {
        echo "❌ API call failed: HTTP {$httpCode}\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}