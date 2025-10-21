<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use App\Models\Backend\ApiProviderModel;

// Initialize Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Finding Correct TTN Japan API URLs ===\n\n";

try {
    $tourId = 296; // First tour ID we got
    $baseUrl = 'https://online.ttnconnect.com/api/agency';
    
    // Test different URL patterns
    $testUrls = [
        'Detail Options' => [
            "{$baseUrl}/program/{$tourId}",
            "{$baseUrl}/program/{$tourId}/detail",
            "{$baseUrl}/program/{$tourId}/info",
            "{$baseUrl}/programs/{$tourId}",
            "{$baseUrl}/get-program/{$tourId}",
            "{$baseUrl}/program-detail/{$tourId}",
        ],
        'Period Options' => [
            "{$baseUrl}/program/{$tourId}/periods",
            "{$baseUrl}/program/{$tourId}/schedule",
            "{$baseUrl}/program/{$tourId}/dates",
            "{$baseUrl}/periods/{$tourId}",
            "{$baseUrl}/schedule/{$tourId}",
            "{$baseUrl}/program-periods/{$tourId}",
            "{$baseUrl}/get-periods/{$tourId}",
            "{$baseUrl}/program/{$tourId}/period",
        ]
    ];
    
    $context = stream_context_create([
        'http' => [
            'timeout' => 10,
            'user_agent' => 'NextTrip-Backend/1.0',
            'ignore_errors' => true
        ]
    ]);
    
    foreach ($testUrls as $category => $urls) {
        echo "🧪 Testing {$category}:\n";
        
        foreach ($urls as $url) {
            echo "   Testing: {$url} ... ";
            
            $response = @file_get_contents($url, false, $context);
            
            if ($response !== false) {
                $length = strlen($response);
                $data = json_decode($response, true);
                
                if ($data !== null) {
                    if (is_array($data)) {
                        $count = count($data);
                        $keys = !empty($data) ? array_keys($data[0] ?? $data) : [];
                        echo "✅ SUCCESS - {$length} bytes, {$count} items, keys: " . implode(', ', array_slice($keys, 0, 5)) . "\n";
                        
                        // Show sample data for successful calls
                        if ($category === 'Period Options' && $count > 0) {
                            echo "      📅 Period fields found:\n";
                            $firstItem = is_array($data) && !empty($data) ? $data[0] : $data;
                            foreach ($firstItem as $key => $value) {
                                if (stripos($key, 'date') !== false || 
                                    stripos($key, 'price') !== false || 
                                    stripos($key, 'day') !== false ||
                                    stripos($key, 'available') !== false) {
                                    echo "         🎯 {$key}: {$value}\n";
                                }
                            }
                        }
                    } else {
                        echo "✅ SUCCESS - {$length} bytes, single object\n";
                    }
                } else {
                    echo "⚠️  {$length} bytes, not JSON: " . substr($response, 0, 50) . "...\n";
                }
            } else {
                // Get HTTP response code
                $headers = $http_response_header ?? [];
                $statusLine = $headers[0] ?? 'Unknown error';
                echo "❌ FAILED - {$statusLine}\n";
            }
        }
        echo "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}