<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

// Delete periods with NULL start_date or end_date for ProBookingCenter tours
$deleted = DB::table('tb_tour_period')
    ->whereNull('start_date')
    ->orWhereNull('end_date')
    ->where('api_type', 'probookingcenter')
    ->whereNull('deleted_at')
    ->update(['deleted_at' => now()]);

echo "Soft deleted $deleted periods with NULL dates for ProBookingCenter tours\n";

// Show remaining periods for tour 125035
$periods = DB::table('tb_tour_period')
    ->where('tour_id', 125035)
    ->whereNull('deleted_at')
    ->get();

echo "\nRemaining periods for Tour ID 125035: " . $periods->count() . "\n";
foreach ($periods as $period) {
    echo "  Period ID: {$period->id}, Start: {$period->start_date}, End: {$period->end_date}\n";
}
