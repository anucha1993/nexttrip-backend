<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Search for ProBookingCenter Tours ===\n\n";

// Search for tours with this name pattern
$tours = DB::table('tb_tour')
    ->where('name', 'LIKE', '%HOKKAIDO%ASAHIKAWA%')
    ->orWhere('name', 'LIKE', '%PJPCTS15%')
    ->get();

echo "Found " . $tours->count() . " tours\n\n";

foreach ($tours as $tour) {
    echo "Tour ID: {$tour->id}\n";
    echo "  Name: {$tour->name}\n";
    echo "  API Type: {$tour->api_type}\n";
    echo "  API ID: {$tour->api_id}\n";
    echo "  Code1: {$tour->code1}\n";
    echo "  Image: {$tour->image}\n";
    
    $periodCount = DB::table('tb_tour_period')
        ->where('tour_id', $tour->id)
        ->whereNull('deleted_at')
        ->count();
    echo "  Periods: {$periodCount}\n\n";
}
