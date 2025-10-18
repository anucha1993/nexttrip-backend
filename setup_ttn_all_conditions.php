<?php
require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Adding TTN_ALL Processing Conditions & Promotion Rules ===\n\n";

try {
    // Get TTN_ALL provider
    $provider = DB::table('tb_api_providers')->where('code', 'ttn_all')->first();
    
    if (!$provider) {
        echo "❌ TTN_ALL provider not found!\n";
        exit(1);
    }
    
    echo "✅ Found TTN_ALL provider (ID: {$provider->id})\n\n";
    
    // 1. Add Processing Conditions
    echo "📋 Adding Processing Conditions...\n";
    
    // Clear existing conditions
    DB::table('tb_api_conditions')->where('api_provider_id', $provider->id)->delete();
    
    $conditions = [
        [
            'api_provider_id' => $provider->id,
            'condition_type' => 'price_calculation',
            'field_name' => 'discount_percentage',
            'condition_rules' => json_encode([
                'formula' => '(price1 - special_price1) / price1 * 100',
                'description' => 'คำนวณเปอร์เซ็นต์ส่วนลดจากราคาผู้ใหญ่พักคู่'
            ]),
            'priority' => 1,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ],
        [
            'api_provider_id' => $provider->id,
            'condition_type' => 'price_group_assignment',
            'field_name' => 'price_group',
            'condition_rules' => json_encode([
                'ranges' => [
                    ['min' => 0, 'max' => 10000, 'group' => 1],
                    ['min' => 10001, 'max' => 20000, 'group' => 2],
                    ['min' => 20001, 'max' => 30000, 'group' => 3],
                    ['min' => 30001, 'max' => 50000, 'group' => 4],
                    ['min' => 50001, 'max' => 80000, 'group' => 5],
                    ['min' => 80001, 'max' => 999999999, 'group' => 6]
                ],
                'base_price' => 'net_price',
                'description' => 'กำหนดกลุ่มราคาตามยอดราคาสุทธิ'
            ]),
            'priority' => 2,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ],
        [
            'api_provider_id' => $provider->id,
            'condition_type' => 'period_status_assignment',
            'field_name' => 'status_period',
            'condition_rules' => json_encode([
                'conditions' => [
                    ['if' => 'P_AVAILABLE > 0', 'then' => 1, 'description' => 'มีที่นั่งว่าง'],
                    ['if' => 'P_AVAILABLE <= 0', 'then' => 3, 'description' => 'หมดแล้ว']
                ]
            ]),
            'priority' => 3,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ],
        [
            'api_provider_id' => $provider->id,
            'condition_type' => 'image_processing',
            'field_name' => 'image',
            'condition_rules' => json_encode([
                'download_path' => 'upload/tour/ttn_allapi/',
                'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif'],
                'max_file_size' => '5MB',
                'description' => 'ดาวน์โหลดและจัดเก็บรูปภาพทัวร์'
            ]),
            'priority' => 4,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ],
        [
            'api_provider_id' => $provider->id,
            'condition_type' => 'pdf_processing',
            'field_name' => 'pdf_file',
            'condition_rules' => json_encode([
                'download_path' => 'upload/tour/pdf_file/ttn_allapi/',
                'allowed_extensions' => ['pdf'],
                'max_file_size' => '10MB',
                'description' => 'ดาวน์โหลดและจัดเก็บไฟล์ PDF รายละเอียดทัวร์'
            ]),
            'priority' => 5,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]
    ];
    
    DB::table('tb_api_conditions')->insert($conditions);
    echo "✅ Added " . count($conditions) . " processing conditions\n\n";
    
    // 2. Add Promotion Rules
    echo "🎁 Adding Promotion Rules...\n";
    
    // Clear existing promotion rules
    DB::table('tb_api_promotion_rules')->where('api_provider_id', $provider->id)->delete();
    
    $promotionRules = [
        [
            'api_provider_id' => $provider->id,
            'rule_name' => 'Fire Sale Rule',
            'condition_field' => 'discount_percentage',
            'condition_operator' => '>=',
            'condition_value' => 30.00,
            'promotion_type' => 'fire_sale',
            'promotion1_value' => 'Y',
            'promotion2_value' => 'N',
            'priority' => 1,
            'is_active' => true,
            'description' => 'ส่วนลด >= 30% = โปรไฟไหม้',
            'created_at' => now(),
            'updated_at' => now()
        ],
        [
            'api_provider_id' => $provider->id,
            'rule_name' => 'Normal Promotion Rule',
            'condition_field' => 'discount_percentage',
            'condition_operator' => '<',
            'condition_value' => 30.00,
            'promotion_type' => 'normal',
            'promotion1_value' => 'N',
            'promotion2_value' => 'Y',
            'priority' => 2,
            'is_active' => true,
            'description' => 'ส่วนลด < 30% และ > 0% = โปรธรรมดา',
            'created_at' => now(),
            'updated_at' => now()
        ],
        [
            'api_provider_id' => $provider->id,
            'rule_name' => 'No Promotion Rule',
            'condition_field' => 'discount_percentage',
            'condition_operator' => '<=',
            'condition_value' => 0.00,
            'promotion_type' => 'none',
            'promotion1_value' => 'N',
            'promotion2_value' => 'N',
            'priority' => 3,
            'is_active' => true,
            'description' => 'ไม่มีส่วนลด = ไม่เป็นโปรโมชั่น',
            'created_at' => now(),
            'updated_at' => now()
        ]
    ];
    
    DB::table('tb_api_promotion_rules')->insert($promotionRules);
    echo "✅ Added " . count($promotionRules) . " promotion rules\n\n";
    
    echo "🎉 TTN_ALL Conditions and Promotion Rules setup completed!\n";
    echo "📋 Summary:\n";
    echo "   - Processing Conditions: " . count($conditions) . " items\n";
    echo "   - Promotion Rules: " . count($promotionRules) . " items\n\n";
    
    echo "📝 Configured Rules:\n";
    echo "   🔥 Fire Sale: ส่วนลด >= 30%\n";
    echo "   🎁 Normal Promo: ส่วนลด < 30% และ > 0%\n";
    echo "   ❌ No Promo: ไม่มีส่วนลด\n";
    echo "   💰 Price Groups: 1-6 ตามช่วงราคา\n";
    echo "   📊 Period Status: ตาม available count\n\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>