<?php

require_once __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Delete periods array mapping ===\n\n";

$mapping = DB::table('tb_api_field_mappings')
    ->where('api_provider_id', 52)
    ->where('local_field', 'periods')
    ->first();

if($mapping) {
    DB::table('tb_api_field_mappings')
        ->where('id', $mapping->id)
        ->delete();
    
    echo "✓ Deleted periods mapping ID: {$mapping->id}\n";
    echo "  (periods ไม่ควรเป็น field ใน tour table)\n";
} else {
    echo "No periods mapping found\n";
}
