<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Creating ProBookingCenter API Provider...\n";

// 1. Create ProBookingCenter Provider
$providerId = DB::table('tb_api_providers')->insertGetId([
    'name' => 'ProBookingCenter API',
    'code' => 'probookingcenter',
    'url' => 'https://api.probookingcenter.com/api/tours/series',
    'period_endpoint' => 'https://api.probookingcenter.com/api/tours/period',
    'tour_detail_endpoint' => '',
    'requires_multi_step' => 1,
    'url_parameters' => json_encode([
        'tour_detail_id_field' => 'seriesCode',
        'period_id_field' => 'periodCode'
    ]),
    'headers' => json_encode([
        'Content-Type' => 'Nextrip Holiday',
        'API-Key' => 'jDOpBMVdyFxbZpvPrzB7ySuxFEuONNFTQEBBoQ7Y'
    ]),
    'pdf_header' => '',
    'pdf_footer' => '',
    'pdf_header_footer_enabled' => 'on',
    'config' => json_encode([
        'wholesale_id' => '39',
        'group_id' => '3',
        'period_url_pattern' => 'https://api.probookingcenter.com/api/tours/period',
        'image_resize' => [
            'width' => '600',
            'height' => '600'
        ],
        'allowed_image_ext_string' => 'png, jpeg, jpg, webp',
        'image_check_change' => '2',
        'country_filter' => null
    ]),
    'status' => 'active',
    'description' => 'ProBookingCenter Tour API Provider',
    'created_at' => now(),
    'updated_at' => now()
]);

echo "✓ Created ProBookingCenter provider with ID: {$providerId}\n";

// 2. Create field mappings for Tour
$tourMappings = [
    ['local_field' => 'api_id', 'api_field' => 'seriesCode', 'data_type' => 'string', 'is_required' => 1],
    ['local_field' => 'code1', 'api_field' => 'seriesCode', 'data_type' => 'string', 'is_required' => 1],
    ['local_field' => 'name', 'api_field' => 'seriesName', 'data_type' => 'string', 'is_required' => 1],
    ['local_field' => 'image', 'api_field' => 'seriesImageUrl', 'data_type' => 'string', 'is_required' => 0],
    ['local_field' => 'country_id', 'api_field' => 'countryName', 'data_type' => 'string', 'is_required' => 0],
    ['local_field' => 'airline_id', 'api_field' => 'airline', 'data_type' => 'string', 'is_required' => 0],
    ['local_field' => 'pdf_file', 'api_field' => 'docs.pdfUrl', 'data_type' => 'string', 'is_required' => 0],
    ['local_field' => 'api_type', 'api_field' => '', 'data_type' => 'string', 'is_required' => 1],
    ['local_field' => 'data_type', 'api_field' => '', 'data_type' => 'integer', 'is_required' => 1],
];

foreach ($tourMappings as $mapping) {
    DB::table('tb_api_field_mappings')->insert([
        'api_provider_id' => $providerId,
        'field_type' => 'tour',
        'local_field' => $mapping['local_field'],
        'api_field' => $mapping['api_field'],
        'data_type' => $mapping['data_type'],
        'transformation_rules' => '[]',
        'is_required' => $mapping['is_required'],
        'created_at' => now(),
        'updated_at' => now()
    ]);
}

echo "✓ Created " . count($tourMappings) . " tour field mappings\n";

// 3. Create field mappings for Period
$periodMappings = [
    ['local_field' => 'period_api_id', 'api_field' => 'periodCode', 'data_type' => 'string'],
    ['local_field' => 'start_date', 'api_field' => 'periodStart', 'data_type' => 'date'],
    ['local_field' => 'end_date', 'api_field' => 'periodEnd', 'data_type' => 'date'],
    ['local_field' => 'group', 'api_field' => 'seat', 'data_type' => 'integer'],
    ['local_field' => 'count', 'api_field' => 'available', 'data_type' => 'integer'],
];

foreach ($periodMappings as $mapping) {
    DB::table('tb_api_field_mappings')->insert([
        'api_provider_id' => $providerId,
        'field_type' => 'period',
        'local_field' => $mapping['local_field'],
        'api_field' => $mapping['api_field'],
        'data_type' => $mapping['data_type'],
        'transformation_rules' => '[]',
        'is_required' => 0,
        'created_at' => now(),
        'updated_at' => now()
    ]);
}

echo "✓ Created " . count($periodMappings) . " period field mappings\n";

// 4. Summary
echo "\n=== ProBookingCenter API Setup Complete ===\n";
echo "Provider ID: {$providerId}\n";
echo "Code: probookingcenter\n";
echo "URL: https://api.probookingcenter.com/api/tours/series\n";
echo "Period URL: https://api.probookingcenter.com/api/tours/period\n";
echo "Wholesale ID: 39\n";
echo "Field Mappings: " . (count($tourMappings) + count($periodMappings)) . "\n";
echo "Status: active\n";
echo "\nYou can now test/sync ProBookingCenter API with limit 5.\n";
