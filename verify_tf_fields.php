<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Tour Factory Field Check ===\n\n";

$tours = DB::table('tb_tour')
    ->where('api_type', 'tourfactory')
    ->select('name', 'code1', 'image', 'pdf_file')
    ->get();

foreach ($tours as $tour) {
    echo "Tour: {$tour->name}\n";
    echo "  code1: " . ($tour->code1 ?: '❌ EMPTY') . "\n";
    echo "  image: " . ($tour->image ?: '❌ EMPTY') . "\n";
    echo "  pdf: " . ($tour->pdf_file ?: '❌ EMPTY') . "\n";
    echo "\n";
}

echo "Total tours: " . count($tours) . "\n";
