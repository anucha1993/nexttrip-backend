<?php
// สคริปต์แสดงข้อมูล Static Values สำหรับแต่ละ Provider
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$providers = DB::table('tb_api_providers')->get();

echo "Static Values Reference Guide\n";
echo "============================\n\n";

foreach($providers as $provider) {
    echo "Provider: {$provider->name} (Code: {$provider->code})\n";
    echo str_repeat("-", 50) . "\n";
    
    // Static values สำหรับ tour
    echo "Tour Static Values:\n";
    echo "- api_type: '{$provider->code}'\n";
    echo "- data_type: 'package'\n";
    echo "- wholesale_id: 1 (default)\n";
    echo "- group_id: 1 (default)\n";
    
    // แสดง field mappings ที่มี static values
    $static_mappings = DB::table('tb_api_field_mappings')
        ->where('api_provider_id', $provider->id)
        ->where(function($query) {
            $query->whereNull('api_field')->orWhere('api_field', '');
        })
        ->get();
        
    if(count($static_mappings) > 0) {
        echo "\nCurrent Static Field Mappings:\n";
        foreach($static_mappings as $mapping) {
            echo "- {$mapping->local_field} ({$mapping->field_type}): [STATIC VALUE - {$mapping->data_type}]\n";
        }
    }
    
    // แสดง field mappings ที่มาจาก API
    $api_mappings = DB::table('tb_api_field_mappings')
        ->where('api_provider_id', $provider->id)
        ->where('api_field', '!=', '')
        ->whereNotNull('api_field')
        ->get();
        
    if(count($api_mappings) > 0) {
        echo "\nAPI Field Mappings:\n";
        foreach($api_mappings as $mapping) {
            echo "- {$mapping->local_field} ({$mapping->field_type}): {$mapping->api_field}\n";
        }
    }
    
    echo "\n" . str_repeat("=", 60) . "\n\n";
}

echo "Static Value Explanation:\n";
echo "========================\n";
echo "Static values คือค่าที่ไม่ได้มาจาก API แต่เป็นค่าคงที่ที่กำหนดไว้:\n";
echo "- api_type: รหัสของ API Provider (เช่น 'zego', 'itravel')\n";
echo "- data_type: ประเภทข้อมูล มักเป็น 'package' สำหรับทัวร์\n";
echo "- wholesale_id: ID ของผู้ให้บริการ wholesale (default = 1)\n";
echo "- group_id: ID ของกลุ่มทัวร์ (default = 1)\n";
echo "- country_id: ID ประเทศ (อาจเป็น static หรือมาจาก API mapping)\n";
echo "- airline_id: ID สายการบิน (อาจเป็น static หรือมาจาก API mapping)\n\n";

echo "UI จะแสดง:\n";
echo "- 'STATIC VALUE' สำหรับฟิลด์ที่เป็น static\n";
echo "- ชื่อฟิลด์จริงจาก API สำหรับฟิลด์ที่มาจาก API\n";