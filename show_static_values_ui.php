<?php
// แสดงการทำงานของ Static Values ที่ชัดเจน
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Static Values ที่แสดงในระบบ UI\n";
echo "===============================\n\n";

echo "🔍 ตอนนี้ UI จะแสดง:\n";
echo "- แทนที่จะเป็น 'STATIC VALUE' ที่งง\n";
echo "- จะแสดงค่าจริงที่ใช้งาน เช่น 'zego', 'package'\n\n";

$providers = DB::table('tb_api_providers')->get();

foreach($providers as $provider) {
    echo "📋 {$provider->name}:\n";
    echo str_repeat("-", strlen($provider->name) + 4) . "\n";
    
    $static_mappings = DB::table('tb_api_field_mappings')
        ->where('api_provider_id', $provider->id)
        ->where(function($query) {
            $query->whereNull('api_field')->orWhere('api_field', '');
        })
        ->get();
        
    if(count($static_mappings) > 0) {
        foreach($static_mappings as $mapping) {
            $rules = json_decode($mapping->transformation_rules, true);
            $staticValue = $rules['static_value'] ?? 'ไม่ได้กำหนด';
            
            echo "  🏷️  {$mapping->local_field}: '{$staticValue}'\n";
        }
    } else {
        echo "  ไม่มี static values\n";
    }
    echo "\n";
}

echo "✨ การปรับปรุง UI:\n";
echo "=================\n";
echo "1. ✅ แสดงค่าจริงแทน 'STATIC VALUE'\n";
echo "2. ✅ เพิ่มช่องใส่ Static Value ใหม่\n";
echo "3. ✅ รองรับการแก้ไขและเพิ่ม Static Values\n";
echo "4. ✅ แสดงคำแนะนำให้เลือกระหว่าง API Field หรือ Static Value\n\n";

echo "🚀 วิธีใช้งาน:\n";
echo "==============\n";
echo "เมื่อเพิ่ม Field Mapping ใหม่:\n";
echo "- ช่องแรก: ใส่ชื่อ API field (เช่น ProductCode, ProductName)\n";
echo "- ช่องที่สอง: ใส่ค่า Static (เช่น zego, package)\n";
echo "- เลือกเฉพาะช่องใดช่องหนึ่ง ไม่ต้องใส่ทั้งสองช่อง\n\n";

echo "💡 ตัวอย่าง Static Values ที่ใช้บ่อย:\n";
echo "====================================\n";
echo "api_type:\n";
echo "- zego (สำหรับ Zego API)\n";
echo "- go365 (สำหรับ GO365 API)\n";
echo "- bestconsortium (สำหรับ Best Consortium API)\n";
echo "- ttn_japan (สำหรับ TTN Japan API)\n\n";
echo "data_type:\n";
echo "- package (สำหรับทัวร์ทุกประเภท)\n\n";
echo "country_id:\n";
echo "- [\"JP\"] (สำหรับ TTN Japan - ประเทศญี่ปุ่นเท่านั้น)\n";