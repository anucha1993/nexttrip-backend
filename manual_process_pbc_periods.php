<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Models\Backend\TourModel;

echo "🔄 Manual Period Processing for ProBookingCenter\n\n";

// Get provider
$provider = DB::table('tb_api_providers')->where('id', 56)->first();
$headers = [
    'Content-Type' => 'application/json',
    'API-Key' => 'jDOpBMVdyFxbZpvPrzB7ySuxFEuONNFTQEBBoQ7Y'
];

// Get all ProBookingCenter tours
$allPBCTours = TourModel::where('api_type', 'probookingcenter')
    ->whereNull('deleted_at')
    ->get();

echo "Found " . $allPBCTours->count() . " ProBookingCenter tours\n\n";

$createdPeriods = 0;
$updatedPeriods = 0;

// Process periods for each tour using seriesCode parameter
foreach ($allPBCTours as $tour) {
    $seriesCode = $tour->code1;
    
    if (empty($seriesCode)) {
        echo "⚠️  Tour ID {$tour->id}: No code1, skipping\n";
        continue;
    }
    
    // Fetch periods for this specific tour using seriesCode parameter
    $periodResponse = Http::withHeaders($headers)
        ->timeout(30)
        ->get($provider->period_endpoint, ['seriesCode' => $seriesCode]);
    
    if ($periodResponse->successful()) {
        $periodData = $periodResponse->json();
        $periods = $periodData['data'] ?? [];
        
        if (count($periods) > 0) {
            echo "✅ Tour ID {$tour->id} ({$seriesCode}): Found " . count($periods) . " periods\n";
            
            // Process periods (simplified version)
            $syncedPeriodCodes = [];
            
            foreach ($periods as $periodData) {
                $periodCode = $periodData['periodCode'] ?? null;
                if (!$periodCode) continue;
                
                // Find existing period
                $period = \App\Models\Backend\TourPeriodModel::where('tour_id', $tour->id)
                    ->where('period_code', $periodCode)
                    ->whereNull('deleted_at')
                    ->first();
                
                $isNew = false;
                if (!$period) {
                    $period = new \App\Models\Backend\TourPeriodModel();
                    $period->tour_id = $tour->id;
                    $period->period_code = $periodCode;
                    $period->api_type = 'probookingcenter';
                    $isNew = true;
                }
                
                // Set dates
                $startDate = $periodData['periodStart'] ?? null;
                $endDate = $periodData['periodEnd'] ?? null;
                
                if ($startDate) {
                    $period->start_date = \Carbon\Carbon::createFromFormat('d/m/Y', $startDate)->format('Y-m-d');
                }
                if ($endDate) {
                    $period->end_date = \Carbon\Carbon::createFromFormat('d/m/Y', $endDate)->format('Y-m-d');
                }
                
                // Set day/night from parent tour
                $period->day = $tour->day ?? null;
                $period->night = $tour->night ?? null;
                
                // Set group_date
                if ($startDate) {
                    $dateObj = \Carbon\Carbon::createFromFormat('d/m/Y', $startDate);
                    $period->group_date = $dateObj->format('mY');
                }
                
                // Set group and count
                $period->group = $periodData['busName'] ?? null;
                $period->count = $periodData['available'] ?? 0;
                
                // Calculate prices
                $regularPriceAdult = $periodData['regularPrice']['adult'] ?? 0;
                $salesPriceAdult = $periodData['salesPrice']['adult'] ?? 0;
                
                $period->price1 = $regularPriceAdult;
                $period->special_price1 = ($regularPriceAdult > $salesPriceAdult) ? ($regularPriceAdult - $salesPriceAdult) : 0;
                
                // Price 2 (single supplement)
                $period->price2 = $periodData['regularPrice']['single'] ?? 0;
                $period->special_price2 = 0;
                
                // Status
                $period->status_display = 'on';
                $period->status_period = 1;
                
                $period->save();
                
                $syncedPeriodCodes[] = $periodCode;
                
                if ($isNew) {
                    $createdPeriods++;
                } else {
                    $updatedPeriods++;
                }
            }
            
            // Soft delete periods not in sync
            if (count($syncedPeriodCodes) > 0) {
                \App\Models\Backend\TourPeriodModel::where('tour_id', $tour->id)
                    ->where('api_type', 'probookingcenter')
                    ->whereNotIn('period_code', $syncedPeriodCodes)
                    ->whereNull('deleted_at')
                    ->update(['deleted_at' => now()]);
            }
            
            // Update tour price from periods
            $cheapestPeriod = \App\Models\Backend\TourPeriodModel::where('tour_id', $tour->id)
                ->whereNull('deleted_at')
                ->orderBy(DB::raw('price1 - COALESCE(special_price1, 0)'))
                ->first();
            
            if ($cheapestPeriod && $cheapestPeriod->price1 > 0) {
                $tour->price = $cheapestPeriod->price1;
                $tour->special_price = $cheapestPeriod->special_price1 ?? 0;
                
                $net_price = $tour->price - $tour->special_price;
                if ($net_price <= 10000) $tour->price_group = 1;
                elseif ($net_price <= 20000) $tour->price_group = 2;
                elseif ($net_price <= 30000) $tour->price_group = 3;
                elseif ($net_price <= 50000) $tour->price_group = 4;
                elseif ($net_price <= 80000) $tour->price_group = 5;
                else $tour->price_group = 6;
                
                $tour->save();
            }
        } else {
            echo "⚠️  Tour ID {$tour->id} ({$seriesCode}): No periods found\n";
        }
    } else {
        echo "❌ Tour ID {$tour->id} ({$seriesCode}): API error " . $periodResponse->status() . "\n";
    }
    
    usleep(100000); // 0.1 second delay
}

echo "\n📊 Summary:\n";
echo "  Created Periods: $createdPeriods\n";
echo "  Updated Periods: $updatedPeriods\n";

// Check Tour 125035
$tour125035 = DB::table('tb_tour')->where('id', 125035)->first(['id', 'code1', 'price', 'special_price']);
$periods125035 = DB::table('tb_tour_period')->where('tour_id', 125035)->whereNull('deleted_at')->count();

echo "\n=== Tour ID 125035 ===\n";
echo "  Code1: {$tour125035->code1}\n";
echo "  Price: {$tour125035->price}\n";
echo "  Special Price: {$tour125035->special_price}\n";
echo "  Periods: $periods125035\n";
