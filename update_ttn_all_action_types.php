<?php
require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Updating TTN_ALL Conditions with Action Types ===\n\n";

try {
    // Get TTN_ALL provider
    $provider = DB::table('tb_api_providers')->where('code', 'ttn_all')->first();
    
    if (!$provider) {
        echo "❌ TTN_ALL provider not found!\n";
        exit(1);
    }
    
    echo "✅ Found TTN_ALL provider (ID: {$provider->id})\n\n";
    
    // Update conditions with action_type
    echo "📋 Updating Conditions with Action Types...\n";
    
    // Check if action_type column exists
    $tableExists = DB::select("SHOW COLUMNS FROM tb_api_conditions LIKE 'action_type'");
    
    if (empty($tableExists)) {
        echo "⚠️  Adding action_type column to tb_api_conditions table...\n";
        DB::statement("ALTER TABLE tb_api_conditions ADD COLUMN action_type VARCHAR(50) NULL AFTER condition_type");
    }
    
    // Update specific conditions with appropriate action types
    $updates = [
        [
            'condition_type' => 'price_calculation',
            'action_type' => 'transform_value',
            'description' => 'คำนวณเปอร์เซ็นต์ส่วนลดสำหรับกำหนดโปรโมชั่น'
        ],
        [
            'condition_type' => 'price_group_assignment',
            'action_type' => 'set_value',
            'description' => 'กำหนดกลุ่มราคาตามช่วงราคาสุทธิ'
        ],
        [
            'condition_type' => 'period_status_assignment',
            'action_type' => 'set_value',
            'description' => 'กำหนดสถานะตามจำนวนที่นั่งว่าง'
        ],
        [
            'condition_type' => 'image_processing',
            'action_type' => 'download_image',
            'description' => 'ดาวน์โหลดและจัดเก็บรูปภาพทัวร์'
        ],
        [
            'condition_type' => 'pdf_processing',
            'action_type' => 'download_file',
            'description' => 'ดาวน์โหลดและจัดเก็บไฟล์ PDF'
        ]
    ];
    
    foreach ($updates as $update) {
        $affected = DB::table('tb_api_conditions')
            ->where('api_provider_id', $provider->id)
            ->where('condition_type', $update['condition_type'])
            ->update([
                'action_type' => $update['action_type'],
                'updated_at' => now()
            ]);
            
        echo "✅ Updated {$update['condition_type']} → {$update['action_type']} ({$affected} rows)\n";
    }
    
    // Check if download_file action exists in UI options
    echo "\n🔍 Checking Action Type options...\n";
    
    $actionTypes = [
        'lookup_database' => 'ค้นหาจากฐานข้อมูล',
        'download_image' => 'ดาวน์โหลดรูปภาพ',
        'download_file' => 'ดาวน์โหลดไฟล์',
        'set_value' => 'กำหนดค่า',
        'transform_value' => 'แปลงค่า',
        'skip_record' => 'ข้ามการบันทึก'
    ];
    
    echo "📋 Available Action Types:\n";
    foreach ($actionTypes as $value => $label) {
        echo "   - {$value}: {$label}\n";
    }
    
    echo "\n🎉 TTN_ALL Conditions updated with Action Types!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>