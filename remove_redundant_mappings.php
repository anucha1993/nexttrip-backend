<?php
// สคริปต์ลบ field mappings ที่ซ้ำซ้อนกับ provider config
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "กำลังลบ Field Mappings ที่ซ้ำซ้อนกับ Provider Config\n";
echo "=================================================\n\n";

// รายการ fields ที่ซ้ำซ้อนกับ provider config
$redundant_fields = [
    'wholesale_id', // มีใน config แล้ว
    'group_id',     // มีใน config แล้ว
    // เก็บ api_type และ data_type ไว้ เพราะเป็นค่าที่จำเป็นและไม่ได้อยู่ใน config
];

foreach($redundant_fields as $field) {
    echo "ลบ field mapping: {$field}\n";
    
    $deleted = DB::table('tb_api_field_mappings')
        ->where('local_field', $field)
        ->where(function($query) {
            $query->whereNull('api_field')->orWhere('api_field', '');
        })
        ->delete();
        
    echo "  - ลบไปแล้ว: {$deleted} รายการ\n";
}

echo "\nตรวจสอบ field mappings ที่เหลือ:\n";
echo "==============================\n";

$remaining = DB::table('tb_api_field_mappings')
    ->leftJoin('tb_api_providers', 'tb_api_field_mappings.api_provider_id', '=', 'tb_api_providers.id')
    ->select('tb_api_providers.name', 'tb_api_field_mappings.local_field', 'tb_api_field_mappings.api_field')
    ->where(function($query) {
        $query->whereNull('tb_api_field_mappings.api_field')->orWhere('tb_api_field_mappings.api_field', '');
    })
    ->get();

if(count($remaining) > 0) {
    foreach($remaining as $mapping) {
        echo "- {$mapping->name}: {$mapping->local_field}\n";
    }
} else {
    echo "ไม่มี static mappings ที่ซ้ำซ้อนแล้ว\n";
}

echo "\nข้อมูล Provider Config ที่จะใช้แทน:\n";
echo "==================================\n";

$providers = DB::table('tb_api_providers')->get();
foreach($providers as $provider) {
    $config = json_decode($provider->config, true);
    echo "{$provider->name}:\n";
    echo "  - wholesale_id: " . ($config['wholesale_id'] ?? 'ไม่ได้ตั้งค่า') . "\n";
    echo "  - group_id: " . ($config['group_id'] ?? 'ไม่ได้ตั้งค่า') . "\n";
    echo "\n";
}