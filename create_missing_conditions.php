<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Http\Kernel')->handle(
    Illuminate\Http\Request::capture()
);

use App\Models\Backend\ApiConditionModel;
use App\Models\Backend\ApiProviderModel;

// สร้าง conditions ที่ขาดหายไป
$missingConditions = [
    'zego' => [
        ['condition_type' => 'country_mapping', 'field_name' => 'DepartCountryName', 'priority' => 2],
        ['condition_type' => 'field_transformation', 'field_name' => 'Promotion1', 'priority' => 3],
        ['condition_type' => 'field_transformation', 'field_name' => 'Promotion2', 'priority' => 4],
    ],
    'ttn_japan' => [
        ['condition_type' => 'country_mapping', 'field_name' => 'P_COUNTRY', 'priority' => 1],
        ['condition_type' => 'field_transformation', 'field_name' => 'P_LOCATION', 'priority' => 2],
        ['condition_type' => 'airline_mapping', 'field_name' => 'P_AIRLINE', 'priority' => 3],
        ['condition_type' => 'image_processing', 'field_name' => 'BANNER', 'priority' => 4],
    ],
    'go365' => [
        ['condition_type' => 'country_mapping', 'field_name' => 'tour_country', 'priority' => 1],
        ['condition_type' => 'airline_mapping', 'field_name' => 'tour_airline', 'priority' => 2],
        ['condition_type' => 'image_processing', 'field_name' => 'tour_cover_image', 'priority' => 3],
        ['condition_type' => 'data_update_check', 'field_name' => 'LastUpdate', 'priority' => 4],
    ],
];

echo "=== CREATING MISSING CONDITIONS ===\n";

$totalCreated = 0;
$totalErrors = 0;

foreach ($missingConditions as $providerCode => $conditions) {
    $provider = ApiProviderModel::where('code', $providerCode)->first();
    
    if (!$provider) {
        echo "❌ Provider '{$providerCode}' not found\n";
        $totalErrors++;
        continue;
    }
    
    echo "\n🔄 Processing provider: {$provider->name} ({$providerCode})\n";
    
    foreach ($conditions as $conditionData) {
        // ตรวจสอบว่ามี condition นี้แล้วหรือไม่
        $existingCondition = ApiConditionModel::where([
            'api_provider_id' => $provider->id,
            'condition_type' => $conditionData['condition_type'],
            'field_name' => $conditionData['field_name']
        ])->first();
        
        if (!$existingCondition) {
            // สร้างใหม่
            $newCondition = ApiConditionModel::create([
                'api_provider_id' => $provider->id,
                'condition_type' => $conditionData['condition_type'],
                'field_name' => $conditionData['field_name'],
                'operator' => 'EXISTS', // default
                'value' => '', // จะอัพเดทภายหลัง
                'action_type' => 'set_value', // default
                'priority' => $conditionData['priority'],
                'is_active' => true,
                'condition_rules' => [],
                'action_rules' => []
            ]);
            
            echo "  ✅ Created: {$conditionData['condition_type']} - {$conditionData['field_name']}\n";
            $totalCreated++;
        } else {
            echo "  ⚠️  Already exists: {$conditionData['condition_type']} - {$conditionData['field_name']}\n";
        }
    }
}

echo "\n=== SUMMARY ===\n";
echo "Total created: {$totalCreated}\n";
echo "Total errors: {$totalErrors}\n";