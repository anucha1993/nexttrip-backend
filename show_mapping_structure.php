<?php

require_once __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== tb_api_field_mappings structure ===\n\n";

$columns = DB::select('DESCRIBE tb_api_field_mappings');

foreach($columns as $col) {
    echo sprintf("  %-25s %-20s %s\n", $col->Field, $col->Type, $col->Null);
}

echo "\n=== Sample image mapping ===\n\n";

$sample = DB::table('tb_api_field_mappings')
    ->where('api_provider_id', 52)
    ->limit(5)
    ->get();

foreach($sample as $row) {
    echo "ID {$row->id}:\n";
    foreach((array)$row as $key => $val) {
        if(is_string($val) || is_numeric($val)) {
            echo "  $key: " . substr($val, 0, 100) . "\n";
        }
    }
    echo "\n";
}
