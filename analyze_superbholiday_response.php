<?php
// Test what Super Holiday API actually returns
$url = 'https://superbholidayz.com/superb/apiweb.php?mainid=21';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "=== Super Holiday API Real Response ===\n";
echo "URL: $url\n";
echo "HTTP Code: $httpCode\n";

if ($response) {
    $data = json_decode($response, true);
    if ($data && isset($data[0])) {
        echo "\nFirst tour full data structure:\n";
        echo json_encode($data[0], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        echo "\n\n=== Key Fields Analysis ===\n";
        $tour = $data[0];
        echo "Country field: " . ($tour['Country'] ?? 'NULL') . "\n";
        echo "Title field: " . ($tour['title'] ?? 'NULL') . "\n";
        echo "Maincode field: " . ($tour['maincode'] ?? 'NULL') . "\n";
        
        // Check if Country field has any value
        if (isset($tour['Country']) && !empty($tour['Country'])) {
            echo "\n=== Country Field Has Value! ===\n";
            echo "Country value: '" . $tour['Country'] . "'\n";
        } else {
            echo "\n=== Country Field is Empty/NULL ===\n";
            echo "Need to use name-based detection\n";
        }
    }
} else {
    echo "Failed to fetch API response\n";
}
?>