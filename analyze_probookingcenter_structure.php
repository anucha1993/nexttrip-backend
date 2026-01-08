<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Http;

$provider = DB::table('tb_api_providers')->where('code', 'probookingcenter')->first();

if (!$provider) {
    die("ProBookingCenter provider not found!\n");
}

$headers = json_decode($provider->headers, true);

echo "=== Analyzing ProBookingCenter Structure ===\n\n";

// 1. Get tours
echo "1. Getting tours...\n";
$listResponse = Http::withHeaders($headers)->timeout(30)->get($provider->url);
$listData = $listResponse->json();
$tours = $listData['data'] ?? [];

echo "Total tours: " . count($tours) . "\n\n";

// Show first 5 tours
echo "First 5 tours:\n";
for ($i = 0; $i < min(5, count($tours)); $i++) {
    $tour = $tours[$i];
    echo "  - seriesCode: " . ($tour['seriesCode'] ?? 'N/A') . "\n";
    echo "    seriesName: " . ($tour['seriesName'] ?? 'N/A') . "\n\n";
}

// 2. Get all periods
echo "2. Getting all periods...\n";
$periodResponse = Http::withHeaders($headers)->timeout(30)->get($provider->period_endpoint);
$periodData = $periodResponse->json();
$allPeriods = $periodData['data'] ?? [];

echo "Total periods: " . count($allPeriods) . "\n\n";

// 3. Group periods by seriesCode
echo "3. Analyzing periods per tour:\n";
$periodsBySeries = [];
foreach ($allPeriods as $period) {
    $seriesCode = $period['seriesCode'] ?? 'UNKNOWN';
    if (!isset($periodsBySeries[$seriesCode])) {
        $periodsBySeries[$seriesCode] = [];
    }
    $periodsBySeries[$seriesCode][] = $period;
}

echo "Unique seriesCode count: " . count($periodsBySeries) . "\n\n";

// Show series with most periods
arsort($periodsBySeries);
$topSeries = array_slice($periodsBySeries, 0, 10, true);

echo "Top 10 series with most periods:\n";
foreach ($topSeries as $seriesCode => $periods) {
    echo "  - {$seriesCode}: " . count($periods) . " periods\n";
    if (count($periods) > 1) {
        echo "    Period dates:\n";
        foreach ($periods as $idx => $p) {
            echo "      " . ($idx + 1) . ". " . ($p['periodStart'] ?? 'N/A') . " - " . ($p['periodEnd'] ?? 'N/A') . "\n";
            if ($idx >= 2) {
                echo "      ... (and " . (count($periods) - 3) . " more)\n";
                break;
            }
        }
    }
}

// 4. Check match between tours and periods
echo "\n4. Matching tours with periods:\n";
$toursWithPeriods = 0;
$toursWithoutPeriods = 0;
$totalMatchedPeriods = 0;

foreach ($tours as $tour) {
    $seriesCode = $tour['seriesCode'] ?? null;
    if ($seriesCode && isset($periodsBySeries[$seriesCode])) {
        $toursWithPeriods++;
        $totalMatchedPeriods += count($periodsBySeries[$seriesCode]);
    } else {
        $toursWithoutPeriods++;
    }
}

echo "Tours with periods: {$toursWithPeriods}\n";
echo "Tours without periods: {$toursWithoutPeriods}\n";
echo "Total matched periods: {$totalMatchedPeriods}\n";

echo "\n5. First 5 tours period counts:\n";
for ($i = 0; $i < min(5, count($tours)); $i++) {
    $tour = $tours[$i];
    $seriesCode = $tour['seriesCode'] ?? 'N/A';
    $periodCount = isset($periodsBySeries[$seriesCode]) ? count($periodsBySeries[$seriesCode]) : 0;
    echo "  Tour #{$i}: {$seriesCode} - {$periodCount} periods\n";
}
