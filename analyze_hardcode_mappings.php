<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Http\Kernel')->handle(
    Illuminate\Http\Request::capture()
);

use App\Models\Backend\ApiProviderModel;
use App\Models\Backend\ApiFieldMappingModel;

// วิเคราะห์ hardcode mappings จาก ApiController.php
$hardcodeMappings = [
    // === ZEGO API ===
    'zego' => [
        'tour' => [
            ['local_field' => 'api_id', 'api_field' => 'ProductCode', 'data_type' => 'string', 'is_required' => true],
            ['local_field' => 'api_type', 'api_field' => '', 'data_type' => 'string', 'static_value' => 'zego'],
            ['local_field' => 'data_type', 'api_field' => '', 'data_type' => 'string', 'static_value' => 'package'],
            ['local_field' => 'name', 'api_field' => 'ProductName', 'data_type' => 'string'],
            ['local_field' => 'description', 'api_field' => 'Highlight', 'data_type' => 'string'],
            ['local_field' => 'country_id', 'api_field' => 'CountryName', 'data_type' => 'json'],
            ['local_field' => 'airline_id', 'api_field' => 'AirlineCode', 'data_type' => 'integer'],
            ['local_field' => 'image', 'api_field' => 'URLImage', 'data_type' => 'string'],
            ['local_field' => 'num_day', 'api_field' => 'NumDay', 'data_type' => 'integer'],
            ['local_field' => 'num_night', 'api_field' => 'NumNight', 'data_type' => 'integer'],
            ['local_field' => 'wholesale_id', 'api_field' => '', 'data_type' => 'integer', 'static_value' => '1'],
            ['local_field' => 'group_id', 'api_field' => '', 'data_type' => 'integer', 'static_value' => '1'],
        ],
        'period' => [
            ['local_field' => 'periods', 'api_field' => 'PeriodTour', 'data_type' => 'json'],
            ['local_field' => 'period_api_id', 'api_field' => 'PeriodCode', 'data_type' => 'string'],
            ['local_field' => 'start_date', 'api_field' => 'PeriodStart', 'data_type' => 'date'],
            ['local_field' => 'end_date', 'api_field' => 'PeriodEnd', 'data_type' => 'date'],
            ['local_field' => 'price1', 'api_field' => 'Price1', 'data_type' => 'decimal'],
            ['local_field' => 'price2', 'api_field' => 'Price2', 'data_type' => 'decimal'],
            ['local_field' => 'price3', 'api_field' => 'Price3', 'data_type' => 'decimal'],
            ['local_field' => 'price4', 'api_field' => 'Price4', 'data_type' => 'decimal'],
            ['local_field' => 'count', 'api_field' => 'SeatAvailable', 'data_type' => 'integer'],
            ['local_field' => 'status_period_text', 'api_field' => 'StatusPeriod', 'data_type' => 'string'],
        ]
    ],
    
    // === BEST CONSORTIUM API ===
    'bestconsortium' => [
        'tour' => [
            ['local_field' => 'api_id', 'api_field' => 'id', 'data_type' => 'string', 'is_required' => true],
            ['local_field' => 'api_type', 'api_field' => '', 'data_type' => 'string', 'static_value' => 'best'],
            ['local_field' => 'data_type', 'api_field' => '', 'data_type' => 'string', 'static_value' => 'package'],
            ['local_field' => 'name', 'api_field' => 'name', 'data_type' => 'string'],
            ['local_field' => 'country_id', 'api_field' => 'nameEng', 'data_type' => 'json'], // จาก country API
            ['local_field' => 'airline_id', 'api_field' => 'airline_name', 'data_type' => 'integer'], // ค้นหาจากชื่อ
            ['local_field' => 'image', 'api_field' => 'bannerSq', 'data_type' => 'string'],
            ['local_field' => 'wholesale_id', 'api_field' => '', 'data_type' => 'integer', 'static_value' => '2'],
            ['local_field' => 'group_id', 'api_field' => '', 'data_type' => 'integer', 'static_value' => '2'],
        ],
        'period' => [
            ['local_field' => 'periods', 'api_field' => 'departures', 'data_type' => 'json'],
            ['local_field' => 'period_api_id', 'api_field' => 'departureId', 'data_type' => 'string'],
            ['local_field' => 'start_date', 'api_field' => 'departureDate', 'data_type' => 'date'],
            ['local_field' => 'end_date', 'api_field' => 'returnDate', 'data_type' => 'date'],
            ['local_field' => 'price1', 'api_field' => 'adultPrice', 'data_type' => 'decimal'],
            ['local_field' => 'price2', 'api_field' => 'childPrice', 'data_type' => 'decimal'],
            ['local_field' => 'price3', 'api_field' => 'infantPrice', 'data_type' => 'decimal'],
            ['local_field' => 'count', 'api_field' => 'availableSeats', 'data_type' => 'integer'],
            ['local_field' => 'status_period_text', 'api_field' => 'status', 'data_type' => 'string'],
        ]
    ],
    
    // === TTN JAPAN API ===
    'ttn_japan' => [
        'tour' => [
            ['local_field' => 'api_id', 'api_field' => 'P_ID', 'data_type' => 'string', 'is_required' => true],
            ['local_field' => 'api_type', 'api_field' => '', 'data_type' => 'string', 'static_value' => 'ttn'],
            ['local_field' => 'data_type', 'api_field' => '', 'data_type' => 'string', 'static_value' => 'package'],
            ['local_field' => 'name', 'api_field' => 'P_NAME', 'data_type' => 'string'],
            ['local_field' => 'description', 'api_field' => 'P_DESCRIPTION', 'data_type' => 'string'],
            ['local_field' => 'country_id', 'api_field' => '', 'data_type' => 'json', 'static_value' => 'JAPAN'], // hardcode Japan
            ['local_field' => 'city_id', 'api_field' => 'P_LOCATION', 'data_type' => 'json'],
            ['local_field' => 'airline_id', 'api_field' => 'P_AIRLINE', 'data_type' => 'integer'],
            ['local_field' => 'image', 'api_field' => 'P_Image', 'data_type' => 'string'], // หรือ BANNER
            ['local_field' => 'pdf_file', 'api_field' => 'P_PDF', 'data_type' => 'string'],
            ['local_field' => 'num_day', 'api_field' => 'P_DAY', 'data_type' => 'integer'],
            ['local_field' => 'num_night', 'api_field' => 'P_NIGHT', 'data_type' => 'integer'],
            ['local_field' => 'wholesale_id', 'api_field' => '', 'data_type' => 'integer', 'static_value' => '3'],
            ['local_field' => 'group_id', 'api_field' => '', 'data_type' => 'integer', 'static_value' => '3'],
        ],
        'period' => [
            ['local_field' => 'periods', 'api_field' => 'Price', 'data_type' => 'json'], // จาก periods API
            ['local_field' => 'period_api_id', 'api_field' => 'Period_ID', 'data_type' => 'string'],
            ['local_field' => 'start_date', 'api_field' => 'S_Date', 'data_type' => 'date'],
            ['local_field' => 'end_date', 'api_field' => 'E_Date', 'data_type' => 'date'],
            ['local_field' => 'price1', 'api_field' => 'Adult_Price', 'data_type' => 'decimal'],
            ['local_field' => 'price2', 'api_field' => 'Child_Price', 'data_type' => 'decimal'],
            ['local_field' => 'price3', 'api_field' => 'Infant_Price', 'data_type' => 'decimal'],
            ['local_field' => 'count', 'api_field' => 'Seat_Available', 'data_type' => 'integer'],
            ['local_field' => 'status_period_text', 'api_field' => 'Status', 'data_type' => 'string'],
        ]
    ],
    
    // === GO365 API ===
    'go365' => [
        'tour' => [
            ['local_field' => 'api_id', 'api_field' => 'tour_id', 'data_type' => 'string', 'is_required' => true],
            ['local_field' => 'api_type', 'api_field' => '', 'data_type' => 'string', 'static_value' => 'go365'],
            ['local_field' => 'data_type', 'api_field' => '', 'data_type' => 'string', 'static_value' => 'package'],
            ['local_field' => 'name', 'api_field' => 'tour_name', 'data_type' => 'string'],
            ['local_field' => 'description', 'api_field' => 'tour_description', 'data_type' => 'string'],
            ['local_field' => 'country_id', 'api_field' => 'tour_country', 'data_type' => 'json'], // array ของ countries
            ['local_field' => 'airline_id', 'api_field' => 'tour_airline', 'data_type' => 'integer'], // object ของ airline
            ['local_field' => 'image', 'api_field' => 'tour_cover_image', 'data_type' => 'string'],
            ['local_field' => 'num_day', 'api_field' => 'tour_duration_day', 'data_type' => 'integer'],
            ['local_field' => 'num_night', 'api_field' => 'tour_duration_night', 'data_type' => 'integer'],
            ['local_field' => 'wholesale_id', 'api_field' => '', 'data_type' => 'integer', 'static_value' => '4'],
            ['local_field' => 'group_id', 'api_field' => '', 'data_type' => 'integer', 'static_value' => '4'],
        ],
        'period' => [
            ['local_field' => 'start_date', 'api_field' => 'tour_date_min', 'data_type' => 'date'],
            ['local_field' => 'end_date', 'api_field' => 'tour_date_max', 'data_type' => 'date'],
            ['local_field' => 'price1', 'api_field' => 'tour_price_start', 'data_type' => 'decimal'],
            ['local_field' => 'count', 'api_field' => 'tour_period_count', 'data_type' => 'integer'],
        ]
    ],
];

echo "=== ANALYZING HARDCODE MAPPINGS FROM ApiController.php ===\n\n";

// ตรวจสอบ mappings ปัจจุบันในฐานข้อมูล
foreach ($hardcodeMappings as $providerCode => $fieldTypes) {
    $provider = ApiProviderModel::where('code', $providerCode)->first();
    
    if (!$provider) {
        echo "❌ Provider '{$providerCode}' not found\n";
        continue;
    }
    
    echo "🔍 Analyzing provider: {$provider->name} ({$providerCode})\n";
    echo str_repeat("-", 60) . "\n";
    
    foreach ($fieldTypes as $fieldType => $expectedMappings) {
        echo "\n  📋 {$fieldType} mappings:\n";
        
        // ดึง mappings ปัจจุบัน
        $currentMappings = ApiFieldMappingModel::where([
            'api_provider_id' => $provider->id,
            'field_type' => $fieldType
        ])->get();
        
        echo "    Current: " . $currentMappings->count() . " mappings\n";
        echo "    Expected: " . count($expectedMappings) . " mappings\n\n";
        
        // เปรียบเทียบ mapping แต่ละตัว
        foreach ($expectedMappings as $expected) {
            $current = $currentMappings->where('local_field', $expected['local_field'])->first();
            
            if (!$current) {
                echo "    ❌ Missing: {$expected['local_field']} -> {$expected['api_field']}\n";
            } else {
                $matches = true;
                $issues = [];
                
                if ($current->api_field !== $expected['api_field']) {
                    $matches = false;
                    $issues[] = "api_field: '{$current->api_field}' != '{$expected['api_field']}'";
                }
                
                if ($current->data_type !== $expected['data_type']) {
                    $matches = false;
                    $issues[] = "data_type: '{$current->data_type}' != '{$expected['data_type']}'";
                }
                
                if (isset($expected['is_required']) && $current->is_required != $expected['is_required']) {
                    $matches = false;
                    $issues[] = "is_required: " . ($current->is_required ? 'true' : 'false') . " != " . ($expected['is_required'] ? 'true' : 'false');
                }
                
                if ($matches) {
                    echo "    ✅ OK: {$expected['local_field']} -> {$expected['api_field']}\n";
                } else {
                    echo "    ⚠️  Issues: {$expected['local_field']} -> " . implode(', ', $issues) . "\n";
                }
            }
        }
        
        // ตรวจสอบ mappings ที่เกิน
        foreach ($currentMappings as $current) {
            $found = false;
            foreach ($expectedMappings as $expected) {
                if ($expected['local_field'] === $current->local_field) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                echo "    🔄 Extra: {$current->local_field} -> {$current->api_field} (not in hardcode)\n";
            }
        }
    }
    
    echo "\n";
}

// บันทึกข้อมูลลงไฟล์เพื่อใช้ในการแก้ไข
file_put_contents('hardcode_mappings.json', json_encode($hardcodeMappings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "💾 Hardcode mappings saved to hardcode_mappings.json\n";