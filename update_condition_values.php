<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Http\Kernel')->handle(
    Illuminate\Http\Request::capture()
);

use App\Models\Backend\ApiConditionModel;
use App\Models\Backend\ApiProviderModel;

// อัพเดท conditions ที่ยังไม่มี value ให้มี default values
$updates = [
    // Zego - Country Mapping
    ['provider_code' => 'zego', 'condition_type' => 'country_mapping', 'field_name' => 'CountryName', 'value' => 'Thailand,Japan,Korea'],
    ['provider_code' => 'zego', 'condition_type' => 'country_mapping', 'field_name' => 'DepartCountryName', 'value' => 'Thailand,Japan,Korea'],
    
    // Airline Mapping
    ['provider_code' => 'zego', 'condition_type' => 'airline_mapping', 'field_name' => 'AirlineCode', 'value' => 'TG,WE,AirAsia'],
    ['provider_code' => 'best_consortium', 'condition_type' => 'airline_mapping', 'field_name' => 'airline', 'value' => 'TG,WE,AirAsia'],
    
    // Image Processing
    ['provider_code' => 'zego', 'condition_type' => 'image_processing', 'field_name' => 'URLImage', 'value' => 'http://,https://,.jpg,.png'],
    ['provider_code' => 'best_consortium', 'condition_type' => 'image_processing', 'field_name' => 'image_url', 'value' => 'http://,https://,.jpg,.png'],
    ['provider_code' => 'ttn_japan', 'condition_type' => 'image_processing', 'field_name' => 'P_Image', 'value' => 'http://,https://,.jpg,.png'],
    ['provider_code' => 'go365', 'condition_type' => 'image_processing', 'field_name' => 'Picture', 'value' => 'http://,https://,.jpg,.png'],
    
    // Field Transformation
    ['provider_code' => 'zego', 'condition_type' => 'field_transformation', 'field_name' => 'Promotion1', 'value' => 'Y,N'],
    ['provider_code' => 'zego', 'condition_type' => 'field_transformation', 'field_name' => 'Promotion2', 'value' => 'Y,N'],
    
    // Text Processing
    ['provider_code' => 'tour_factory', 'condition_type' => 'text_processing', 'field_name' => 'description', 'value' => 'null,empty,undefined'],
    
    // Data Update Check
    ['provider_code' => 'go365', 'condition_type' => 'data_update_check', 'field_name' => 'LastUpdate', 'value' => 'daily,weekly,changed'],
];

echo "=== UPDATING CONDITIONS WITH DEFAULT VALUES ===\n";

foreach ($updates as $update) {
    $provider = ApiProviderModel::where('code', $update['provider_code'])->first();
    if (!$provider) {
        echo "Provider {$update['provider_code']} not found\n";
        continue;
    }
    
    $condition = ApiConditionModel::where([
        'api_provider_id' => $provider->id,
        'condition_type' => $update['condition_type'],
        'field_name' => $update['field_name']
    ])->first();
    
    if ($condition) {
        if (empty($condition->value)) {
            $condition->value = $update['value'];
            $condition->save();
            echo "✅ Updated: {$provider->name} - {$update['condition_type']} - {$update['field_name']} = {$update['value']}\n";
        } else {
            echo "⚠️  Already has value: {$provider->name} - {$update['condition_type']} - {$update['field_name']} = {$condition->value}\n";
        }
    } else {
        echo "❌ Condition not found: {$provider->name} - {$update['condition_type']} - {$update['field_name']}\n";
    }
}

echo "\n=== SUMMARY OF ALL CONDITIONS WITH VALUES ===\n";
$conditions = ApiConditionModel::with('apiProvider')->whereNotNull('value')->where('value', '!=', '')->get();
foreach ($conditions as $condition) {
    $provider = $condition->apiProvider->name ?? 'Unknown';
    echo "{$provider} | {$condition->condition_type} | {$condition->field_name} | {$condition->operator} | {$condition->value} | {$condition->action_type}\n";
}