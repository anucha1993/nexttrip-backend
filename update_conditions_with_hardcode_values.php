<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Http\Kernel')->handle(
    Illuminate\Http\Request::capture()
);

use App\Models\Backend\ApiConditionModel;
use App\Models\Backend\ApiProviderModel;

// อัพเดท conditions ด้วย hardcode values จาก ApiController.php เดิม
$hardcodeValues = [
    // === ZEGO API ===
    'zego' => [
        // Country Mapping - จาก CountryName
        ['condition_type' => 'country_mapping', 'field_name' => 'CountryName', 'operator' => 'EXISTS', 'value' => 'Thailand,Japan,Korea,Singapore,Malaysia', 'action_type' => 'lookup_database'],
        ['condition_type' => 'country_mapping', 'field_name' => 'DepartCountryName', 'operator' => 'EXISTS', 'value' => 'Thailand', 'action_type' => 'lookup_database'],
        
        // Airline Mapping - จาก AirlineCode
        ['condition_type' => 'airline_mapping', 'field_name' => 'AirlineCode', 'operator' => 'EXISTS', 'value' => 'TG,WE,FD,SL,DD', 'action_type' => 'lookup_database'],
        
        // Image Processing - จาก URLImage
        ['condition_type' => 'image_processing', 'field_name' => 'URLImage', 'operator' => 'EXISTS', 'value' => 'https://,http://,.jpg,.png,.jpeg,.webp', 'action_type' => 'download_image'],
        
        // Field Transformation - จาก Promotion1, Promotion2
        ['condition_type' => 'field_transformation', 'field_name' => 'Promotion1', 'operator' => 'EXISTS', 'value' => 'Y,N', 'action_type' => 'set_value'],
        ['condition_type' => 'field_transformation', 'field_name' => 'Promotion2', 'operator' => 'EXISTS', 'value' => 'Y,N', 'action_type' => 'set_value'],
        
        // Text Processing - จาก ProductName, Highlight
        ['condition_type' => 'text_processing', 'field_name' => 'ProductName', 'operator' => 'EXISTS', 'value' => 'null,empty,undefined', 'action_type' => 'transform_value'],
        ['condition_type' => 'text_processing', 'field_name' => 'Highlight', 'operator' => 'EXISTS', 'value' => '\n', 'action_type' => 'transform_value'],
    ],
    
    // === BEST CONSORTIUM API ===
    'bestconsortium' => [
        // Country Mapping - จาก nameEng
        ['condition_type' => 'country_mapping', 'field_name' => 'nameEng', 'operator' => 'EXISTS', 'value' => 'Thailand,Japan,Korea,Singapore,Europe', 'action_type' => 'lookup_database'],
        
        // Airline Mapping - จาก airline_name
        ['condition_type' => 'airline_mapping', 'field_name' => 'airline_name', 'operator' => 'EXISTS', 'value' => 'Thai Airways,Bangkok Airways,Air Asia,Thai Smile', 'action_type' => 'lookup_database'],
        
        // Image Processing - จาก bannerSq
        ['condition_type' => 'image_processing', 'field_name' => 'bannerSq', 'operator' => 'EXISTS', 'value' => 'https://,http://,.jpg,.png,.jpeg,.webp', 'action_type' => 'download_image'],
    ],
    
    // === TTN JAPAN API ===
    'ttn_japan' => [
        // Country Mapping - hardcode Japan
        ['condition_type' => 'country_mapping', 'field_name' => 'P_COUNTRY', 'operator' => 'EXISTS', 'value' => 'JAPAN', 'action_type' => 'lookup_database'],
        
        // City Mapping - จาก P_LOCATION
        ['condition_type' => 'field_transformation', 'field_name' => 'P_LOCATION', 'operator' => 'EXISTS', 'value' => 'Tokyo,Osaka,Kyoto,Nagoya,Fukuoka', 'action_type' => 'lookup_database'],
        
        // Airline Mapping - จาก P_AIRLINE
        ['condition_type' => 'airline_mapping', 'field_name' => 'P_AIRLINE', 'operator' => 'EXISTS', 'value' => 'TG,NH,JL', 'action_type' => 'lookup_database'],
        
        // Image Processing - จาก BANNER (Google Drive URL)
        ['condition_type' => 'image_processing', 'field_name' => 'BANNER', 'operator' => 'EXISTS', 'value' => 'drive.google.com,https://,http://', 'action_type' => 'download_image'],
    ],
    
    // === GO365 API ===
    'go365' => [
        // Country Mapping - จาก tour_country array
        ['condition_type' => 'country_mapping', 'field_name' => 'tour_country', 'operator' => 'EXISTS', 'value' => 'country_code_2,country_id', 'action_type' => 'lookup_database'],
        
        // Airline Mapping - จาก tour_airline
        ['condition_type' => 'airline_mapping', 'field_name' => 'tour_airline', 'operator' => 'EXISTS', 'value' => 'airline_iata,airline_id', 'action_type' => 'lookup_database'],
        
        // Image Processing - จาก tour_cover_image
        ['condition_type' => 'image_processing', 'field_name' => 'tour_cover_image', 'operator' => 'EXISTS', 'value' => 'https://,http://,.jpg,.png,.jpeg,.webp', 'action_type' => 'download_image'],
        
        // Data Update Check - จาก LastUpdate
        ['condition_type' => 'data_update_check', 'field_name' => 'LastUpdate', 'operator' => 'EXISTS', 'value' => 'daily,weekly,changed', 'action_type' => 'set_value'],
    ],
    
    // === TOUR FACTORY API ===
    'tourfactory' => [
        // Text Processing - generic text processing
        ['condition_type' => 'text_processing', 'field_name' => 'description', 'operator' => 'EXISTS', 'value' => 'null,empty,undefined', 'action_type' => 'transform_value'],
    ],
    
    // === SUPER HOLIDAY API ===
    'superbholiday' => [
        // Text Processing - generic text processing
        ['condition_type' => 'text_processing', 'field_name' => 'description', 'operator' => 'EXISTS', 'value' => 'null,empty,undefined', 'action_type' => 'transform_value'],
    ],
];

echo "=== UPDATING CONDITIONS WITH HARDCODE VALUES FROM ApiController.php ===\n";

$totalUpdated = 0;
$totalErrors = 0;

foreach ($hardcodeValues as $providerCode => $conditions) {
    $provider = ApiProviderModel::where('code', $providerCode)->first();
    
    if (!$provider) {
        echo "❌ Provider '{$providerCode}' not found\n";
        $totalErrors++;
        continue;
    }
    
    echo "\n🔄 Processing provider: {$provider->name} ({$providerCode})\n";
    
    foreach ($conditions as $conditionData) {
        // ค้นหา condition ที่มีอยู่แล้ว
        $existingCondition = ApiConditionModel::where([
            'api_provider_id' => $provider->id,
            'condition_type' => $conditionData['condition_type'],
            'field_name' => $conditionData['field_name']
        ])->first();
        
        if ($existingCondition) {
            // อัพเดท value ถ้ายังไม่มี
            if (empty($existingCondition->value)) {
                $existingCondition->value = $conditionData['value'];
                $existingCondition->operator = $conditionData['operator'];
                $existingCondition->action_type = $conditionData['action_type'];
                $existingCondition->save();
                
                echo "  ✅ Updated: {$conditionData['condition_type']} - {$conditionData['field_name']} = {$conditionData['value']}\n";
                $totalUpdated++;
            } else {
                echo "  ⚠️  Already has value: {$conditionData['condition_type']} - {$conditionData['field_name']} = {$existingCondition->value}\n";
            }
        } else {
            echo "  ❌ Condition not found: {$conditionData['condition_type']} - {$conditionData['field_name']}\n";
            $totalErrors++;
        }
    }
}

echo "\n=== SUMMARY ===\n";
echo "Total updated: {$totalUpdated}\n";
echo "Total errors: {$totalErrors}\n";

// แสดงรายการ conditions ที่มี values แล้ว
echo "\n=== ALL CONDITIONS WITH VALUES ===\n";
$conditionsWithValues = ApiConditionModel::with('apiProvider')
    ->whereNotNull('value')
    ->where('value', '!=', '')
    ->orderBy('api_provider_id')
    ->orderBy('condition_type')
    ->get();

foreach ($conditionsWithValues as $condition) {
    $provider = $condition->apiProvider->name ?? 'Unknown';
    echo "{$provider} | {$condition->condition_type} | {$condition->field_name} | {$condition->operator} | {$condition->value} | {$condition->action_type}\n";
}