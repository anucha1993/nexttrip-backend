<?php
require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Adding Missing Conditions and Promotion Rules ===\n\n";

try {
    // Configuration for remaining API providers
    $additionalConfigurations = [
        'superbholiday' => [
            'conditions' => [
                [
                    'condition_type' => 'price_calculation',
                    'field_name' => 'discount_percentage',
                    'action_type' => 'calculate_percentage',
                    'condition_rules' => json_encode([
                        'formula' => 'Calculate discount percentage from API data',
                        'description' => 'คำนวณเปอร์เซ็นต์ส่วนลดจากข้อมูล API'
                    ]),
                    'priority' => 1
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
                    'priority' => 2
                ],
                [
                    'condition_type' => 'image_processing',
                    'field_name' => 'image',
                    'action_type' => 'download_image',
                    'condition_rules' => json_encode([
                        'download_path' => 'upload/tour/superholidayapi/',
                        'resize_dimensions' => '600x600',
                        'allowed_extensions' => ['png', 'jpeg', 'jpg', 'webp']
                    ]),
                    'priority' => 3
                ],
                [
                    'condition_type' => 'pdf_processing',
                    'field_name' => 'pdf_file',
                    'action_type' => 'download_file',
                    'condition_rules' => json_encode([
                        'download_path' => 'upload/tour/pdf_file/superholidayapi/',
                        'allowed_extensions' => ['pdf']
                    ]),
                    'priority' => 4
                ]
            ],
            'promotion_rules' => [
                ['rule_name' => 'Super Holiday Fire Sale Rule', 'condition_operator' => '>=', 'condition_value' => 30.00, 'promotion_type' => 'fire_sale', 'promotion1_value' => 'Y', 'promotion2_value' => 'N'],
                ['rule_name' => 'Super Holiday Normal Promotion Rule', 'condition_operator' => '<', 'condition_value' => 30.00, 'promotion_type' => 'normal', 'promotion1_value' => 'N', 'promotion2_value' => 'Y'],
                ['rule_name' => 'Super Holiday No Promotion Rule', 'condition_operator' => '<=', 'condition_value' => 0.00, 'promotion_type' => 'none', 'promotion1_value' => 'N', 'promotion2_value' => 'N']
            ]
        ],
        
        'tourfactory' => [
            'promotion_rules' => [
                ['rule_name' => 'Tour Factory Fire Sale Rule', 'condition_operator' => '>=', 'condition_value' => 30.00, 'promotion_type' => 'fire_sale', 'promotion1_value' => 'Y', 'promotion2_value' => 'N'],
                ['rule_name' => 'Tour Factory Normal Promotion Rule', 'condition_operator' => '<', 'condition_value' => 30.00, 'promotion_type' => 'normal', 'promotion1_value' => 'N', 'promotion2_value' => 'Y'],
                ['rule_name' => 'Tour Factory No Promotion Rule', 'condition_operator' => '<=', 'condition_value' => 0.00, 'promotion_type' => 'none', 'promotion1_value' => 'N', 'promotion2_value' => 'N']
            ],
            'additional_conditions' => [
                [
                    'condition_type' => 'price_calculation',
                    'field_name' => 'discount_percentage',
                    'action_type' => 'calculate_percentage',
                    'condition_rules' => json_encode([
                        'description' => 'คำนวณเปอร์เซ็นต์ส่วนลดสำหรับ Tour Factory'
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
                ]
            ]
        ]
    ];
    
    // Process missing configurations
    foreach ($additionalConfigurations as $providerCode => $config) {
        $provider = DB::table('tb_api_providers')->where('code', $providerCode)->first();
        
        if (!$provider) {
            echo "⚠️  Provider {$providerCode} not found, skipping...\n";
            continue;
        }
        
        echo "📋 Processing {$provider->name} (ID: {$provider->id})\n";
        
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
        
        // Add additional conditions  
        if (isset($config['additional_conditions'])) {
            foreach ($config['additional_conditions'] as $condition) {
                $condition['api_provider_id'] = $provider->id;
                $condition['is_active'] = true;
                $condition['created_at'] = now();
                $condition['updated_at'] = now();
                
                DB::table('tb_api_conditions')->insert($condition);
            }
            echo "  ✅ Added " . count($config['additional_conditions']) . " additional conditions\n";
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
    
    echo "🎉 Missing configurations have been added!\n";
    
    // Final summary
    echo "\n📊 Final Summary:\n";
    $allProviders = DB::table('tb_api_providers')->where('status', 'active')->count();
    $totalConditions = DB::table('tb_api_conditions')->count();
    $totalPromotionRules = DB::table('tb_api_promotion_rules')->count();
    
    echo "- Active Providers: {$allProviders}\n";
    echo "- Total Conditions: {$totalConditions}\n";
    echo "- Total Promotion Rules: {$totalPromotionRules}\n";
    
    // Check if any providers still missing configurations
    $stillMissingConditions = DB::select("
        SELECT COUNT(*) as count 
        FROM tb_api_providers p 
        LEFT JOIN tb_api_conditions c ON p.id = c.api_provider_id 
        WHERE p.status = 'active' AND c.id IS NULL
    ")[0]->count;
    
    $stillMissingPromotions = DB::select("
        SELECT COUNT(*) as count 
        FROM tb_api_providers p 
        LEFT JOIN tb_api_promotion_rules pr ON p.id = pr.api_provider_id 
        WHERE p.status = 'active' AND pr.id IS NULL
    ")[0]->count;
    
    if ($stillMissingConditions == 0 && $stillMissingPromotions == 0) {
        echo "✅ All providers now have complete configurations!\n";
    } else {
        echo "⚠️  Still missing: {$stillMissingConditions} providers without conditions, {$stillMissingPromotions} without promotion rules\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>