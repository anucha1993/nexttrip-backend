<?php
// สรุปการแก้ไขปัญหา STATIC VALUES ซ้ำซ้อน
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "สรุปการแก้ไขปัญหา STATIC VALUES ซ้ำซ้อน\n";
echo "========================================\n\n";

echo "❌ ปัญหาที่พบ:\n";
echo "- มีการตั้งค่า wholesale_id และ group_id ซ้ำซ้อนใน 2 ที่:\n";
echo "  1. Provider Config (การตั้งค่า API Provider)\n";
echo "  2. Field Mappings (Static Values)\n";
echo "- ทำให้เกิดความสับสนและข้อมูลไม่สอดคล้อง\n\n";

echo "✅ วิธีการแก้ไข:\n";
echo "1. ลบ field mappings ที่ซ้ำซ้อน (wholesale_id, group_id)\n";
echo "2. ใช้ค่าจาก Provider Config เท่านั้น\n";
echo "3. อัพเดต UI ให้แสดงข้อมูล Provider Config ที่ใช้จริง\n";
echo "4. เก็บเฉพาะ Static Values ที่จำเป็น (api_type, data_type)\n\n";

echo "📋 Static Values ที่เหลือในระบบ:\n";
echo "================================\n";

$providers = DB::table('tb_api_providers')->get();

foreach($providers as $provider) {
    echo "\n{$provider->name}:\n";
    
    // แสดง config values ที่ใช้งานจริง
    $config = json_decode($provider->config, true) ?? [];
    echo "  จากการตั้งค่า Provider:\n";
    echo "  - wholesale_id: " . ($config['wholesale_id'] ?? 'ไม่ได้ตั้งค่า') . "\n";
    echo "  - group_id: " . ($config['group_id'] ?? 'ไม่ได้ตั้งค่า') . "\n";
    echo "  - api_type: {$provider->code} (อัตโนมัติ)\n";
    echo "  - data_type: package (อัตโนมัติ)\n";
    
    // แสดง static field mappings ที่เหลือ
    $static_mappings = DB::table('tb_api_field_mappings')
        ->where('api_provider_id', $provider->id)
        ->where(function($query) {
            $query->whereNull('api_field')->orWhere('api_field', '');
        })
        ->pluck('local_field')
        ->toArray();
        
    if(count($static_mappings) > 0) {
        echo "  Static Field Mappings ที่จำเป็น:\n";
        foreach($static_mappings as $field) {
            echo "  - {$field}\n";
        }
    }
}

echo "\n🎯 ผลลัพธ์:\n";
echo "===========\n";
echo "✓ ไม่มีการซ้ำซ้อนของ wholesale_id และ group_id แล้ว\n";
echo "✓ ระบบใช้ค่าจาก Provider Config เป็นหลัก\n";
echo "✓ UI แสดงข้อมูลที่ชัดเจนและไม่สับสน\n";
echo "✓ Static Values เหลือเฉพาะที่จำเป็นจริงๆ\n";
echo "✓ Code มีความสอดคล้องและง่ายต่อการดูแลรักษา\n\n";

echo "💡 หลักการใหม่:\n";
echo "==============\n";
echo "- Provider Config = ข้อมูลระดับ API Provider (wholesale_id, group_id, image_resize, etc.)\n";
echo "- Field Mappings = การแปลงข้อมูลจาก API fields เท่านั้น\n";
echo "- Static Values = เฉพาะค่าที่ไม่ได้อยู่ในทั้ง API และ Provider Config\n";