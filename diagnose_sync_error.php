<?php
require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Testing Super Holiday Manual Sync with Error Handling ===\n";

try {
    // Set longer time limit
    set_time_limit(300); // 5 minutes
    ini_set('memory_limit', '512M');
    
    // Get provider info
    $pdo = new PDO('mysql:host=27.254.134.77;dbname=nexttrip_web', 'nexttrip_web', '49$$INotHbhk6jqv');
    $stmt = $pdo->prepare('SELECT * FROM tb_api_providers WHERE code = "superbholiday" LIMIT 1');
    $stmt->execute();
    $provider = $stmt->fetch(PDO::FETCH_OBJ);
    
    if (!$provider) {
        echo "❌ Super Holiday provider not found\n";
        exit(1);
    }
    
    echo "✅ Found provider: " . $provider->name . " (ID: " . $provider->id . ")\n";
    
    // Test with very small limit to isolate the problem
    echo "Testing with limit 1 to isolate issue...\n";
    
    // Direct API test
    echo "\n=== Step 1: Testing Direct API Access ===\n";
    $testUrl = 'https://superbholidayz.com/superb/apiweb.php?id=21';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $testUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200 && $response) {
        $data = json_decode($response, true);
        echo "✅ API accessible, returned " . count($data) . " records\n";
        
        // Test field mapping logic
        if (isset($data[0])) {
            echo "\n=== Step 2: Testing Field Mapping Logic ===\n";
            $tourData = $data[0];
            echo "Sample tour maincode: " . ($tourData['maincode'] ?? 'NOT_FOUND') . "\n";
            echo "Sample tour title: " . substr($tourData['title'] ?? 'NOT_FOUND', 0, 50) . "\n";
            echo "Sample tour Country: " . ($tourData['Country'] ?? 'NOT_FOUND') . "\n";
            
            // Test problematic field mappings
            echo "\n=== Step 3: Testing Country Mapping ===\n";
            if (isset($tourData['Country']) && !empty($tourData['Country'])) {
                $stmt = $pdo->prepare('SELECT id, country_name_th FROM tb_country WHERE country_name_th LIKE ? AND status = "on" AND deleted_at IS NULL LIMIT 1');
                $stmt->execute(['%' . $tourData['Country'] . '%']);
                $country = $stmt->fetch();
                if ($country) {
                    echo "✅ Country found: " . $tourData['Country'] . " => ID " . $country['id'] . "\n";
                } else {
                    echo "⚠️ Country not found: " . $tourData['Country'] . "\n";
                }
            } else {
                echo "⚠️ Country field empty or missing\n";
            }
            
            // Test airline mapping
            echo "\n=== Step 4: Testing Airline Mapping ===\n";
            if (isset($tourData['aey']) && !empty($tourData['aey'])) {
                $parts = explode('(', $tourData['aey']);
                $code_airline = "";
                if (isset($parts[1])) {
                    $code_airline = trim($parts[1], ') ');
                }
                echo "Airline string: " . $tourData['aey'] . "\n";
                echo "Extracted code: " . $code_airline . "\n";
                
                if (!empty($code_airline)) {
                    $stmt = $pdo->prepare('SELECT id, travel_name FROM tb_travel_type WHERE code = ? AND status = "on" AND deleted_at IS NULL LIMIT 1');
                    $stmt->execute([$code_airline]);
                    $airline = $stmt->fetch();
                    if ($airline) {
                        echo "✅ Airline found: " . $code_airline . " => " . $airline['travel_name'] . "\n";
                    } else {
                        echo "⚠️ Airline not found: " . $code_airline . "\n";
                    }
                }
            }
        }
        
    } else {
        echo "❌ API not accessible: HTTP " . $httpCode . "\n";
    }
    
    echo "\n=== Step 5: Database Connection Test ===\n";
    $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM tb_tour WHERE api_type = "superbholiday"');
    $stmt->execute();
    $result = $stmt->fetch();
    echo "✅ Database connection OK. Current Super Holiday tours: " . $result['count'] . "\n";
    
} catch (Exception $e) {
    echo "❌ Error occurred:\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " (Line: " . $e->getLine() . ")\n";
    
    // Check for common error causes
    if (strpos($e->getMessage(), 'memory') !== false) {
        echo "\n🔧 Solution: Increase memory_limit in php.ini or script\n";
    } elseif (strpos($e->getMessage(), 'timeout') !== false) {
        echo "\n🔧 Solution: Increase max_execution_time or use smaller batches\n";
    } elseif (strpos($e->getMessage(), 'connection') !== false) {
        echo "\n🔧 Solution: Check database connection or API endpoint\n";
    } elseif (strpos($e->getMessage(), 'constraint') !== false) {
        echo "\n🔧 Solution: Fix duplicate key/constraint violation\n";
    }
}
?>