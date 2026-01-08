<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Backend\TourModel;

// Get all QeBooking tours with periods but no price
$tours = DB::table('tb_tour as t')
    ->join('tb_tour_period as p', 't.id', '=', 'p.tour_id')
    ->where('t.wholesale_id', 67) // QeBooking
    ->whereNull('t.deleted_at')
    ->whereNull('p.deleted_at')
    ->where('t.price', 0)
    ->where('p.price1', '>', 0)
    ->select('t.id', 't.code1', 't.name')
    ->distinct()
    ->get();

echo "Found " . $tours->count() . " QeBooking tours with periods but no tour price\n\n";

$updated = 0;
foreach ($tours as $tourData) {
    // Get full tour model
    $tour = TourModel::find($tourData->id);
    
    if (!$tour) continue;
    
    // Get cheapest period
    $cheapestPeriod = \App\Models\Backend\TourPeriodModel::where('tour_id', $tour->id)
        ->whereNull('deleted_at')
        ->orderBy(DB::raw('price1 - COALESCE(special_price1, 0)'))
        ->first();
    
    if ($cheapestPeriod && $cheapestPeriod->price1 > 0) {
        $price = $cheapestPeriod->price1;
        $special_price = $cheapestPeriod->special_price1 ?? 0;
        $net_price = $price - $special_price;
        
        // Calculate num_day
        $num_day = '';
        if ($cheapestPeriod->day && $cheapestPeriod->night) {
            $num_day = $cheapestPeriod->day . ' วัน ' . $cheapestPeriod->night . ' คืน';
        }
        
        // Calculate price_group
        $price_group = 0;
        if ($net_price > 0) {
            if ($net_price <= 10000) {
                $price_group = 1;
            } elseif ($net_price <= 20000) {
                $price_group = 2;
            } elseif ($net_price <= 30000) {
                $price_group = 3;
            } elseif ($net_price <= 50000) {
                $price_group = 4;
            } elseif ($net_price <= 80000) {
                $price_group = 5;
            } else {
                $price_group = 6;
            }
        }
        
        // Update tour
        $tour->num_day = $num_day;
        $tour->price = $price;
        $tour->special_price = $special_price;
        $tour->price_group = $price_group;
        
        // Calculate promotion flags
        if ($special_price > 0 && $price > 0) {
            $discountPercent = ($special_price / $price) * 100;
            if ($discountPercent >= 30) {
                $tour->promotion1 = 'Y';
                $tour->promotion2 = 'N';
            } elseif ($discountPercent > 0) {
                $tour->promotion1 = 'N';
                $tour->promotion2 = 'Y';
            }
        }
        
        $tour->save();
        $updated++;
        
        if ($updated <= 5) {
            echo "✅ Tour ID {$tour->id} ({$tour->code1}): price={$price}, special={$special_price}, group={$price_group}\n";
        }
    }
}

echo "\n📊 Updated $updated tours with prices from periods\n";

// Check sample after update
$sample = DB::table('tb_tour')->where('id', 124977)->first(['id', 'code1', 'price', 'special_price', 'price_group']);
if ($sample) {
    echo "\n=== Sample Tour ID 124977 After Update ===\n";
    echo "  Code1: {$sample->code1}\n";
    echo "  Price: {$sample->price}\n";
    echo "  Special Price: {$sample->special_price}\n";
    echo "  Price Group: {$sample->price_group}\n";
}

// Summary
$toursWithPrice = DB::table('tb_tour')->where('wholesale_id', 67)->whereNull('deleted_at')->where('price', '>', 0)->count();
$totalTours = DB::table('tb_tour')->where('wholesale_id', 67)->whereNull('deleted_at')->count();
echo "\n📊 QeBooking tours with price > 0: $toursWithPrice out of $totalTours\n";
