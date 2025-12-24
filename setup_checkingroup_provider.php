<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Setting up Checkin Group API Provider...\n\n";

// 1. Create/Update API Provider
$providerId = DB::table('tb_api_providers')->insertGetId([
    'code' => 'checkingroup',
    'name' => 'Checkin Group',
    'url' => 'https://api.checkingroup.co.th/v1/programtours',
    'headers' => json_encode([
        'Content-Type' => 'application/json',
        'Accept' => 'application/json'
    ]),
    'status' => 'active',
    'requires_multi_step' => false,
    'config' => json_encode([
        'wholesale_id' => null, // Set if needed
        'group_id' => 3
    ]),
    'created_at' => now(),
    'updated_at' => now()
]);

echo "✅ Provider created (ID: {$providerId})\n\n";

// 2. Tour Field Mappings
$tourMappings = [
    // Core fields
    ['api_field' => 'id', 'local_field' => 'api_id', 'data_type' => 'integer', 'is_required' => true],
    ['api_field' => 'code', 'local_field' => 'code1', 'data_type' => 'string', 'is_required' => true],
    ['api_field' => 'name', 'local_field' => 'name', 'data_type' => 'string', 'is_required' => true],
    ['api_field' => 'highlight', 'local_field' => 'detail', 'data_type' => 'string', 'is_required' => false],
    
    // Images & Files
    ['api_field' => 'banner', 'local_field' => 'image', 'data_type' => 'string', 'is_required' => false],
    ['api_field' => 'pdf', 'local_field' => 'pdf_file', 'data_type' => 'string', 'is_required' => false],
    
    // Days/Nights
    ['api_field' => 'day', 'local_field' => 'day', 'data_type' => 'integer', 'is_required' => false],
    ['api_field' => 'night', 'local_field' => 'night', 'data_type' => 'integer', 'is_required' => false],
    
    // Vehicle/Airline
    ['api_field' => 'vehicle', 'local_field' => 'airline_name', 'data_type' => 'string', 'is_required' => false],
    
    // Static values
    ['api_field' => 'static:checkingroup', 'local_field' => 'api_type', 'data_type' => 'string', 'is_required' => true],
    ['api_field' => 'static:2', 'local_field' => 'data_type', 'data_type' => 'integer', 'is_required' => true],
];

foreach ($tourMappings as $mapping) {
    DB::table('tb_api_field_mappings')->insert([
        'api_provider_id' => $providerId,
        'field_type' => 'tour',
        'api_field' => $mapping['api_field'],
        'local_field' => $mapping['local_field'],
        'data_type' => $mapping['data_type'],
        'is_required' => $mapping['is_required'],
        'created_at' => now(),
        'updated_at' => now()
    ]);
}

echo "✅ Tour field mappings created (" . count($tourMappings) . " fields)\n\n";

// 3. Period Field Mappings
$periodMappings = [
    // Period identification
    ['api_field' => 'id', 'local_field' => 'period_api_id', 'data_type' => 'integer'],
    
    // Dates
    ['api_field' => 'start', 'local_field' => 'start_date', 'data_type' => 'date'],
    ['api_field' => 'end', 'local_field' => 'end_date', 'data_type' => 'date'],
    
    // Prices
    ['api_field' => 'priceAdultDouble', 'local_field' => 'price1', 'data_type' => 'decimal'], // ผู้ใหญ่พักคู่
    ['api_field' => 'priceForOne', 'local_field' => 'price2', 'data_type' => 'decimal'], // พักเดี่ยว
    ['api_field' => 'priceChild', 'local_field' => 'price3', 'data_type' => 'decimal'], // เด็กมีเตียง
    ['api_field' => 'priceChildNoBed', 'local_field' => 'price4', 'data_type' => 'decimal'], // เด็กไม่มีเตียง
    
    // Special prices (same as normal prices if no discount)
    ['api_field' => 'priceAdultDouble', 'local_field' => 'special_price1', 'data_type' => 'decimal'],
    ['api_field' => 'priceForOne', 'local_field' => 'special_price2', 'data_type' => 'decimal'],
    ['api_field' => 'priceChild', 'local_field' => 'special_price3', 'data_type' => 'decimal'],
    ['api_field' => 'priceChildNoBed', 'local_field' => 'special_price4', 'data_type' => 'decimal'],
    
    // Seat availability
    ['api_field' => 'seat', 'local_field' => 'count', 'data_type' => 'integer'],
    ['api_field' => 'group', 'local_field' => 'group', 'data_type' => 'integer'],
    
    // Flight info
    ['api_field' => 'flight', 'local_field' => 'remark', 'data_type' => 'string'],
];

foreach ($periodMappings as $mapping) {
    DB::table('tb_api_field_mappings')->insert([
        'api_provider_id' => $providerId,
        'field_type' => 'period',
        'api_field' => $mapping['api_field'],
        'local_field' => $mapping['local_field'],
        'data_type' => $mapping['data_type'],
        'is_required' => false,
        'created_at' => now(),
        'updated_at' => now()
    ]);
}

echo "✅ Period field mappings created (" . count($periodMappings) . " fields)\n\n";

echo "=" . str_repeat("=", 60) . "\n";
echo "Checkin Group API Provider Setup Complete!\n";
echo "=" . str_repeat("=", 60) . "\n\n";

echo "Summary:\n";
echo "  - Provider ID: {$providerId}\n";
echo "  - Code: checkingroup\n";
echo "  - URL: https://api.checkingroup.co.th/v1/programtours\n";
echo "  - Tour Fields: " . count($tourMappings) . "\n";
echo "  - Period Fields: " . count($periodMappings) . "\n\n";

echo "Next steps:\n";
echo "  1. Go to API Management page\n";
echo "  2. Test Connection for Checkin Group\n";
echo "  3. Run manual sync\n";
echo "  4. Set up schedule if needed\n\n";
