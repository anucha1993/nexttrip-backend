<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Checking iTravel Tours ===\n\n";

$tours = DB::table('tb_tour')
    ->where('api_type', 'itravel')
    ->select('id', 'code', 'code1', 'name', 'api_id')
    ->get();

echo "Total iTravel tours: " . $tours->count() . "\n\n";

if ($tours->count() > 0) {
    echo "Sample tours:\n";
    foreach ($tours->take(10) as $tour) {
        echo "  ID: {$tour->id} | code1: {$tour->code1} | api_id: {$tour->api_id}\n";
    }
    
    // Check duplicates
    $duplicates = DB::table('tb_tour')
        ->where('api_type', 'itravel')
        ->whereNull('deleted_at')
        ->select('code1', DB::raw('COUNT(*) as count'))
        ->groupBy('code1')
        ->having('count', '>', 1)
        ->get();
    
    if ($duplicates->count() > 0) {
        echo "\n⚠️  Duplicate code1 found:\n";
        foreach ($duplicates as $dup) {
            echo "  {$dup->code1}: {$dup->count} times\n";
        }
    }
}

echo "\nDeleting all iTravel tours...\n";
DB::table('tb_tour')->where('api_type', 'itravel')->delete();
DB::table('tb_tour_period')->whereIn('tour_id', function($query) {
    $query->select('id')->from('tb_tour')->where('api_type', 'itravel');
})->delete();

echo "✓ Deleted!\n";
