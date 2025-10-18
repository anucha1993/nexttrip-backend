<?php
require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Creating Conditions and Promotion Rules for All API Providers ===\n\n";

try {
    // Define conditions and rules for each API type based on headcode analysis
    $apiConfigurations = [
        'zego' => [
            'conditions' => [
                [
                    'condition_type' => 'price_calculation',
                    'field_name' => 'discount_percentage',
                    'action_type' => 'calculate_percentage',
                    'condition_rules' => json_encode([
                        'formula' => 'max(cal1, cal2, cal3, cal4) where cal = (price_start - price_end) / price_start * 100',
                        'description' => 'คำนวณเปอร์เซ็นต์ส่วนลดสูงสุดจากทุกประเภทราคา'
                    ]),
                    'priority' => 1
                ],
                [
                    'condition_type' => 'period_status_mapping',
                    'field_name' => 'status_period',
                    'action_type' => 'map_status_value',
                    'condition_rules' => json_encode([
                        'mappings' => [
                            'Book' => 1,
                            'Waitlist' => 2,
                            'Close Group' => 3,
                            'Soldout' => 3
                        ],
                        'source_field' => 'PeriodStatus'
                    ]),
                    'priority' => 2
                ],
                [
                    'condition_type' => 'price_group_assignment',
                    'field_name' => 'price_group',
                    'action_type' => 'set_value',
                    'condition_rules' => json_encode([
                        'ranges' => [
                            ['min' => 0, 'max' => 10000, 'group' => 1],
                            ['min' => 10001, 'max' => 20000, 'group' => 2],
                            ['min' => 20001, 'max' => 30000, 'group' => 3],
                            ['min' => 30001, 'max' => 50000, 'group' => 4],
                            ['min' => 50001, 'max' => 80000, 'group' => 5],
                            ['min' => 80001, 'max' => 999999999, 'group' => 6]
                        ]
                    ]),
                    'priority' => 3
                ],
                [
                    'condition_type' => 'image_processing',
                    'field_name' => 'image',
                    'action_type' => 'download_image',
                    'condition_rules' => json_encode([
                        'download_path' => 'upload/tour/zegoapi/',
                        'resize_dimensions' => '600x600',
                        'allowed_extensions' => ['png', 'jpeg', 'jpg', 'webp']
                    ]),
                    'priority' => 4
                ],
                [
                    'condition_type' => 'pdf_processing',
                    'field_name' => 'pdf_file',
                    'action_type' => 'download_file',
                    'condition_rules' => json_encode([
                        'download_path' => 'upload/tour/pdf_file/zegoapi/',
                        'version_check' => true,
                        'header_watermark' => true
                    ]),
                    'priority' => 5
                ]
            ],
            'promotion_rules' => [
                ['rule_name' => 'Zego Fire Sale Rule', 'condition_operator' => '>=', 'condition_value' => 30.00, 'promotion_type' => 'fire_sale', 'promotion1_value' => 'Y', 'promotion2_value' => 'N'],
                ['rule_name' => 'Zego Normal Promotion Rule', 'condition_operator' => '<', 'condition_value' => 30.00, 'promotion_type' => 'normal', 'promotion1_value' => 'N', 'promotion2_value' => 'Y'],
                ['rule_name' => 'Zego No Promotion Rule', 'condition_operator' => '<=', 'condition_value' => 0.00, 'promotion_type' => 'none', 'promotion1_value' => 'N', 'promotion2_value' => 'N']
            ]
        ],
        
        'bestconsortium' => [
            'conditions' => [
                [
                    'condition_type' => 'rate_limiting_handler',
                    'field_name' => 'api_rate_limit',
                    'action_type' => 'handle_rate_limit',
                    'condition_rules' => json_encode([
                        'check_headers' => ['X-RateLimit-Remaining', 'X-RateLimit-Reset'],
                        'wait_on_limit' => true,
                        'retry_on_429' => true
                    ]),
                    'priority' => 1
                ],
                [
                    'condition_type' => 'nested_api_calls',
                    'field_name' => 'multi_step_processing',
                    'action_type' => 'set_value',
                    'condition_rules' => json_encode([
                        'step1' => 'Get countries from /v1/series/country',
                        'step2' => 'Get tours from /api/tour-programs/v2/{country_id}',
                        'description' => 'Handle nested country->tours API calls'
                    ]),
                    'priority' => 2
                ],
                [
                    'condition_type' => 'content_length_validation',
                    'field_name' => 'image_validation',
                    'action_type' => 'validate_content',
                    'condition_rules' => json_encode([
                        'check_content_length' => true,
                        'min_size' => 1,
                        'source_field' => 'bannerSq'
                    ]),
                    'priority' => 3
                ],
                [
                    'condition_type' => 'price_calculation',
                    'field_name' => 'discount_percentage',
                    'action_type' => 'calculate_percentage',
                    'condition_rules' => json_encode([
                        'formula' => '(adultPrice_old - adultPrice) / adultPrice_old * 100',
                        'description' => 'คำนวณส่วนลดจากราคาเดิมและราคาปัจจุบัน'
                    ]),
                    'priority' => 4
                ]
            ],
            'promotion_rules' => [
                ['rule_name' => 'Best Fire Sale Rule', 'condition_operator' => '>=', 'condition_value' => 30.00, 'promotion_type' => 'fire_sale', 'promotion1_value' => 'Y', 'promotion2_value' => 'N'],
                ['rule_name' => 'Best Normal Promotion Rule', 'condition_operator' => '<', 'condition_value' => 30.00, 'promotion_type' => 'normal', 'promotion1_value' => 'N', 'promotion2_value' => 'Y'],
                ['rule_name' => 'Best No Promotion Rule', 'condition_operator' => '<=', 'condition_value' => 0.00, 'promotion_type' => 'none', 'promotion1_value' => 'N', 'promotion2_value' => 'N']
            ]
        ],
        
        'ttn_japan' => [
            'conditions' => [
                [
                    'condition_type' => 'fixed_value_assignment',
                    'field_name' => 'country_id',
                    'action_type' => 'assign_fixed_value',
                    'condition_rules' => json_encode([
                        'fixed_country' => 'JAPAN',
                        'description' => 'กำหนดประเทศเป็นญี่ปุ่นเสมอ'
                    ]),
                    'priority' => 1
                ],
                [
                    'condition_type' => 'pdf_link_storage',
                    'field_name' => 'pdf_file',
                    'action_type' => 'store_link',
                    'condition_rules' => json_encode([
                        'store_as_url' => true,
                        'source_field' => 'PDF',
                        'description' => 'เก็บ PDF เป็น Google Drive link ไม่ต้องดาวน์โหลด'
                    ]),
                    'priority' => 2
                ],
                [
                    'condition_type' => 'price_calculation',
                    'field_name' => 'discount_percentage',
                    'action_type' => 'calculate_percentage',
                    'condition_rules' => json_encode([
                        'description' => 'คำนวณเปอร์เซ็นต์ส่วนลดแบบเดียวกับ TTN_ALL'
                    ]),
                    'priority' => 3
                ]
            ],
            'promotion_rules' => [
                ['rule_name' => 'TTN Japan Fire Sale Rule', 'condition_operator' => '>=', 'condition_value' => 30.00, 'promotion_type' => 'fire_sale', 'promotion1_value' => 'Y', 'promotion2_value' => 'N'],
                ['rule_name' => 'TTN Japan Normal Promotion Rule', 'condition_operator' => '<', 'condition_value' => 30.00, 'promotion_type' => 'normal', 'promotion1_value' => 'N', 'promotion2_value' => 'Y'],
                ['rule_name' => 'TTN Japan No Promotion Rule', 'condition_operator' => '<=', 'condition_value' => 0.00, 'promotion_type' => 'none', 'promotion1_value' => 'N', 'promotion2_value' => 'N']
            ]
        ],
        
        'go365' => [
            'conditions' => [
                [
                    'condition_type' => 'nested_api_calls',
                    'field_name' => 'multi_step_processing',
                    'action_type' => 'set_value',
                    'condition_rules' => json_encode([
                        'step1' => 'Get tour list from /tour-list',
                        'step2' => 'Get tour details from /tour-detail/{id}',
                        'description' => 'Handle multi-step tour processing'
                    ]),
                    'priority' => 1
                ],
                [
                    'condition_type' => 'price_calculation',
                    'field_name' => 'discount_percentage',
                    'action_type' => 'calculate_percentage',
                    'condition_rules' => json_encode([
                        'description' => 'คำนวณเปอร์เซ็นต์ส่วนลดจากข้อมูล period'
                    ]),
                    'priority' => 2
                ]
            ],
            'promotion_rules' => [
                ['rule_name' => 'GO365 Fire Sale Rule', 'condition_operator' => '>=', 'condition_value' => 30.00, 'promotion_type' => 'fire_sale', 'promotion1_value' => 'Y', 'promotion2_value' => 'N'],
                ['rule_name' => 'GO365 Normal Promotion Rule', 'condition_operator' => '<', 'condition_value' => 30.00, 'promotion_type' => 'normal', 'promotion1_value' => 'N', 'promotion2_value' => 'Y'],
                ['rule_name' => 'GO365 No Promotion Rule', 'condition_operator' => '<=', 'condition_value' => 0.00, 'promotion_type' => 'none', 'promotion1_value' => 'N', 'promotion2_value' => 'N']
            ]
        ]
    ];
    
    // Process each API provider
    foreach ($apiConfigurations as $providerCode => $config) {
        $provider = DB::table('tb_api_providers')->where('code', $providerCode)->first();
        
        if (!$provider) {
            echo "⚠️  Provider {$providerCode} not found, skipping...\n";
            continue;
        }
        
        echo "📋 Processing {$provider->name} (ID: {$provider->id})\n";
        
        // Clear existing conditions and promotion rules
        DB::table('tb_api_conditions')->where('api_provider_id', $provider->id)->delete();
        DB::table('tb_api_promotion_rules')->where('api_provider_id', $provider->id)->delete();
        
        // Add conditions
        if (isset($config['conditions'])) {
            foreach ($config['conditions'] as $condition) {
                $condition['api_provider_id'] = $provider->id;
                $condition['is_active'] = true;
                $condition['created_at'] = now();
                $condition['updated_at'] = now();
                
                DB::table('tb_api_conditions')->insert($condition);
            }
            echo "  ✅ Added " . count($config['conditions']) . " conditions\n";
        }
        
        // Add promotion rules
        if (isset($config['promotion_rules'])) {
            foreach ($config['promotion_rules'] as $rule) {
                $rule['api_provider_id'] = $provider->id;
                $rule['condition_field'] = 'discount_percentage';
                $rule['priority'] = array_search($rule, $config['promotion_rules']) + 1;
                $rule['is_active'] = true;
                $rule['description'] = "ส่วนลด {$rule['condition_operator']} {$rule['condition_value']}% = {$rule['promotion_type']}";
                $rule['created_at'] = now();
                $rule['updated_at'] = now();
                
                DB::table('tb_api_promotion_rules')->insert($rule);
            }
            echo "  ✅ Added " . count($config['promotion_rules']) . " promotion rules\n";
        }
        
        echo "\n";
    }
    
    echo "🎉 All API providers have been configured with conditions and promotion rules!\n";
    echo "\n📊 Summary:\n";
    
    $totalProviders = count($apiConfigurations);
    $totalConditions = DB::table('tb_api_conditions')->count();
    $totalPromotionRules = DB::table('tb_api_promotion_rules')->count();
    
    echo "- Configured Providers: {$totalProviders}\n";
    echo "- Total Conditions: {$totalConditions}\n";
    echo "- Total Promotion Rules: {$totalPromotionRules}\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>