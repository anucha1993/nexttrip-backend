<?php
// แสดง Static Values และวิธีการกำหนดค่า
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "STATIC VALUES คืออะไร และทำไมไม่ต้องระบุ API Field\n";
echo "=================================================\n\n";

echo "1. STATIC VALUES คือค่าคงที่ที่เราต้องกำหนดเองในระบบ ไม่ได้มาจาก API\n\n";

echo "2. ตัวอย่างการทำงานของ Static Values:\n";
echo "   - เมื่อเราดึงข้อมูลจาก Zego API\n";
echo "   - API จะส่งข้อมูลเช่น ProductCode, ProductName, Price\n";
echo "   - แต่เราต้องเพิ่มข้อมูลพิเศษที่ API ไม่ได้ส่งมาให้:\n";
echo "     * api_type = 'zego' (เพื่อระบุว่าข้อมูลนี้มาจาก API ไหน)\n";
echo "     * data_type = 'package' (เพื่อระบุประเภทข้อมูล)\n";
echo "     * wholesale_id = 1 (รหัสผู้ให้บริการ)\n\n";

$providers = DB::table('tb_api_providers')->get();

echo "3. Static Values ปัจจุบันในระบบ:\n";
echo "================================\n\n";

foreach($providers as $provider) {
    echo "Provider: {$provider->name}\n";
    
    // ตรวจสอบ static values ที่มีอยู่
    $static_mappings = DB::table('tb_api_field_mappings')
        ->where('api_provider_id', $provider->id)
        ->where(function($query) {
            $query->whereNull('api_field')->orWhere('api_field', '');
        })
        ->get();
        
    if(count($static_mappings) > 0) {
        foreach($static_mappings as $mapping) {
            $static_value = "";
            
            // กำหนดค่า static ตามประเภทฟิลด์
            switch($mapping->local_field) {
                case 'api_type':
                    $static_value = $provider->code;
                    break;
                case 'data_type':
                    $static_value = 'package';
                    break;
                case 'wholesale_id':
                    $static_value = '1';
                    break;
                case 'group_id':
                    $static_value = '1';
                    break;
                case 'country_id':
                    $static_value = 'จะถูกกำหนดโดย condition mapping';
                    break;
            }
            
            echo "  - {$mapping->local_field}: {$static_value}\n";
        }
    } else {
        echo "  - ไม่มี static values\n";
    }
    echo "\n";
}

echo "4. ทำไม Static Values ไม่ต้องระบุ API Field:\n";
echo "==========================================\n";
echo "- เพราะข้อมูลเหล่านี้ไม่ได้มาจาก API Response\n";
echo "- เราต้องกำหนดค่าเองในระบบตอนที่ประมวลผลข้อมูล\n";
echo "- ระบบจะใช้ข้อมูลเหล่านี้เพื่อจัดเก็บและจำแนกข้อมูลในฐานข้อมูล\n\n";

echo "5. วิธีการทำงานใน Code:\n";
echo "=====================\n";
echo "ใน ApiManagementController->mapTourFieldsFromConfig():\n";
echo "\n";
echo "if (empty(\$mapping->api_field)) {\n";
echo "    // นี่คือ Static Value - ไม่ได้มาจาก API\n";
echo "    switch(\$mapping->local_field) {\n";
echo "        case 'api_type':\n";
echo "            \$tourModel->api_type = \$provider->code;\n";
echo "            break;\n";
echo "        case 'data_type':\n";
echo "            \$tourModel->data_type = 'package';\n";
echo "            break;\n";
echo "        case 'wholesale_id':\n";
echo "            \$tourModel->wholesale_id = 1;\n";
echo "            break;\n";
echo "    }\n";
echo "} else {\n";
echo "    // นี่คือ API Field - มาจาก API Response\n";
echo "    \$apiValue = \$tourData[\$mapping->api_field];\n";
echo "    \$tourModel->{\$mapping->local_field} = \$apiValue;\n";
echo "}\n\n";

echo "6. ประโยชน์ของ Static Values:\n";
echo "============================\n";
echo "- ช่วยให้ระบบรู้ว่าข้อมูลแต่ละรายการมาจาก API ไหน\n";
echo "- ช่วยในการจัดกลุ่มและการค้นหาข้อมูล\n";
echo "- รักษาความสอดคล้องของข้อมูลในระบบ\n";
echo "- สะดวกในการ debug และ maintenance\n";