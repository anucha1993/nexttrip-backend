<?php
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Setting up Promotion Rules ===" . PHP_EOL;

// Get API providers
$zego = App\Models\Backend\ApiProviderModel::where('code', 'zego')->first();
$bestConsortium = App\Models\Backend\ApiProviderModel::where('code', 'bestconsortium')->first();

if ($zego) {
    echo "Setting up Zego promotion rules..." . PHP_EOL;
    
    // Delete existing rules
    $zego->promotionRules()->delete();
    
    // Fire Sale Rule (discount >= 30%)
    App\Models\Backend\ApiPromotionRuleModel::create([
        'api_provider_id' => $zego->id,
        'rule_name' => 'Fire Sale Rule',
        'condition_field' => 'discount_percentage',
        'condition_operator' => '>=',
        'condition_value' => 30.00,
        'promotion_type' => 'fire_sale',
        'promotion1_value' => 'Y',
        'promotion2_value' => 'N',
        'priority' => 1,
        'is_active' => true,
        'description' => 'โปรไฟไหม้ สำหรับส่วนลด >= 30%'
    ]);
    
    // Normal Promotion Rule (discount > 0% และ < 30%)
    App\Models\Backend\ApiPromotionRuleModel::create([
        'api_provider_id' => $zego->id,
        'rule_name' => 'Normal Promotion Rule',
        'condition_field' => 'discount_percentage',
        'condition_operator' => '>',
        'condition_value' => 0.00,
        'promotion_type' => 'normal',
        'promotion1_value' => 'N',
        'promotion2_value' => 'Y',
        'priority' => 2,
        'is_active' => true,
        'description' => 'โปรธรรมดา สำหรับส่วนลด > 0% แต่ < 30%'
    ]);
    
    // No Promotion Rule (no discount)
    App\Models\Backend\ApiPromotionRuleModel::create([
        'api_provider_id' => $zego->id,
        'rule_name' => 'No Promotion Rule',
        'condition_field' => 'discount_percentage',
        'condition_operator' => '<=',
        'condition_value' => 0.00,
        'promotion_type' => 'none',
        'promotion1_value' => 'N',
        'promotion2_value' => 'N',
        'priority' => 3,
        'is_active' => true,
        'description' => 'ไม่เป็นโปรโมชั่น สำหรับไม่มีส่วนลด'
    ]);
    
    echo "Zego rules created: " . $zego->promotionRules()->count() . PHP_EOL;
}

if ($bestConsortium) {
    echo "Setting up Best Consortium promotion rules..." . PHP_EOL;
    
    // Delete existing rules
    $bestConsortium->promotionRules()->delete();
    
    // Fire Sale Rule (discount >= 30%)
    App\Models\Backend\ApiPromotionRuleModel::create([
        'api_provider_id' => $bestConsortium->id,
        'rule_name' => 'Fire Sale Rule',
        'condition_field' => 'discount_percentage',
        'condition_operator' => '>=',
        'condition_value' => 30.00,
        'promotion_type' => 'fire_sale',
        'promotion1_value' => 'Y',
        'promotion2_value' => 'N',
        'priority' => 1,
        'is_active' => true,
        'description' => 'โปรไฟไหม้ สำหรับส่วนลด >= 30%'
    ]);
    
    // Normal Promotion Rule (discount > 0% และ < 30%)
    App\Models\Backend\ApiPromotionRuleModel::create([
        'api_provider_id' => $bestConsortium->id,
        'rule_name' => 'Normal Promotion Rule',
        'condition_field' => 'discount_percentage',
        'condition_operator' => '>',
        'condition_value' => 0.00,
        'promotion_type' => 'normal',
        'promotion1_value' => 'N',
        'promotion2_value' => 'Y',
        'priority' => 2,
        'is_active' => true,
        'description' => 'โปรธรรมดา สำหรับส่วนลด > 0% แต่ < 30%'
    ]);
    
    // No Promotion Rule (no discount)
    App\Models\Backend\ApiPromotionRuleModel::create([
        'api_provider_id' => $bestConsortium->id,
        'rule_name' => 'No Promotion Rule',
        'condition_field' => 'discount_percentage',
        'condition_operator' => '<=',
        'condition_value' => 0.00,
        'promotion_type' => 'none',
        'promotion1_value' => 'N',
        'promotion2_value' => 'N',
        'priority' => 3,
        'is_active' => true,
        'description' => 'ไม่เป็นโปรโมชั่น สำหรับไม่มีส่วนลด'
    ]);
    
    echo "Best Consortium rules created: " . $bestConsortium->promotionRules()->count() . PHP_EOL;
}

echo PHP_EOL . "=== Summary ===" . PHP_EOL;
echo "Total promotion rules created: " . App\Models\Backend\ApiPromotionRuleModel::count() . PHP_EOL;

// Display all rules
$allRules = App\Models\Backend\ApiPromotionRuleModel::with('apiProvider')->get();
foreach ($allRules as $rule) {
    echo sprintf("%s: %s (%s %s %s) -> %s/%s" . PHP_EOL,
        $rule->apiProvider->name,
        $rule->rule_name,
        $rule->condition_field,
        $rule->condition_operator,
        $rule->condition_value,
        $rule->promotion1_value,
        $rule->promotion2_value
    );
}