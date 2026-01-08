<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Creating QeBooking API Provider...\n";

// 1. Create QeBooking Provider (copy from GO365)
$qebookingId = DB::table('tb_api_providers')->insertGetId([
    'name' => 'QeBooking API',
    'code' => 'qebooking',
    'url' => 'https://api.2ucenter.com/api/v1/tours/search',
    'period_endpoint' => '',
    'tour_detail_endpoint' => 'https://api.2ucenter.com/api/v1/tours/detail/{tour_id}',
    'requires_multi_step' => 1,
    'url_parameters' => json_encode([
        'tour_detail_id_field' => 'tour_id',
        'period_id_field' => null
    ]),
    'headers' => json_encode([
        'x-api-key' => 'eyJhbGciOiJIUzUxMiJ9.eyJhcGlfaWQiOjgsImFnZW50X2lkIjoxMDA3LCJ1c2VyX2lkIjoxNzQ5Nn0.NBXKegA03NYe8AI-9_EBgY4Zsu9PD3v4ppif-V6R7Y97njaYH2SKmrDZiV1gXaagZGOzrBNo8AottbJZIm4KpQ',
        'Content-Type' => 'application/json'
    ]),
    'pdf_header' => '',
    'pdf_footer' => '',
    'pdf_header_footer_enabled' => 'on',
    'config' => json_encode([
        'wholesale_id' => '67',
        'group_id' => '3',
        'image_resize' => [
            'width' => '600',
            'height' => '600'
        ],
        'allowed_image_ext_string' => 'png, jpeg, jpg, webp',
        'image_check_change' => '2',
        'country_filter' => null
    ]),
    'status' => 'active',
    'description' => 'QeBooking Tour API Provider (same as GO365 API)',
    'created_at' => now(),
    'updated_at' => now()
]);

echo "✓ Created QeBooking provider with ID: {$qebookingId}\n";

// 2. Copy all field mappings from GO365 (ID 48)
$go365Mappings = DB::table('tb_api_field_mappings')
    ->where('api_provider_id', 48)
    ->get();

echo "Copying " . count($go365Mappings) . " field mappings from GO365...\n";

foreach ($go365Mappings as $mapping) {
    DB::table('tb_api_field_mappings')->insert([
        'api_provider_id' => $qebookingId,
        'field_type' => $mapping->field_type,
        'local_field' => $mapping->local_field,
        'api_field' => $mapping->api_field,
        'data_type' => $mapping->data_type,
        'transformation_rules' => $mapping->transformation_rules,
        'is_required' => $mapping->is_required,
        'created_at' => now(),
        'updated_at' => now()
    ]);
}

echo "✓ Copied all field mappings\n";

// 3. Summary
echo "\n=== QeBooking API Setup Complete ===\n";
echo "Provider ID: {$qebookingId}\n";
echo "Code: qebooking\n";
echo "URL: https://api.2ucenter.com\n";
echo "Wholesale ID: 67\n";
echo "Field Mappings: " . count($go365Mappings) . "\n";
echo "Status: active\n";
echo "\nYou can now sync QeBooking API from the management interface.\n";
