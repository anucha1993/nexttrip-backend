<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Cleaning iTravel Tours ===\n\n";

// Count tours
$count = DB::table('tb_tour')
    ->where('api_type', 'itravel')
    ->whereNull('deleted_at')
    ->count();

echo "Found {$count} iTravel tours\n";

if ($count > 0) {
    // Check unique codes
    $tours = DB::table('tb_tour')
        ->where('api_type', 'itravel')
        ->whereNull('deleted_at')
        ->select('id', 'code1', 'name', 'api_id')
        ->get();
    
    $uniqueCodes = $tours->pluck('code1')->unique()->count();
    echo "Unique code1 values: {$uniqueCodes}\n";
    echo "Unique tour IDs: {$tours->count()}\n\n";
    
    if ($uniqueCodes < $tours->count()) {
        echo "⚠️ PROBLEM: Only {$uniqueCodes} unique codes but {$tours->count()} tours!\n";
        echo "This means duplicate detection is broken.\n\n";
        
        // Show sample
        echo "Sample tours:\n";
        foreach ($tours->take(5) as $tour) {
            echo "  ID: {$tour->id} | code1: {$tour->code1} | api_id: {$tour->api_id}\n";
        }
    }
    
    echo "\nDeleting all iTravel tours...\n";
    DB::table('tb_tour')->where('api_type', 'itravel')->delete();
    DB::table('tb_tour_period')->whereIn('tour_id', $tours->pluck('id'))->delete();
    echo "✓ Deleted!\n";
} else {
    echo "No tours to delete.\n";
}

echo "\nDone!\n";
