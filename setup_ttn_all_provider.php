<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Creating TTN_ALL API Provider based on headcode ===\n";

// Check if TTN_ALL provider already exists
$existing = DB::table('tb_api_providers')->where('code', 'ttn_all')->first();
if ($existing) {
    echo "TTN_ALL provider already exists with ID: " . $existing->id . "\n";
    echo "Updating existing provider...\n";
    $providerId = $existing->id;
    
    // Update existing provider
    DB::table('tb_api_providers')->where('id', $providerId)->update([
        'name' => 'TTN_ALL API',
        'url' => 'https://www.ttnplus.co.th/api/program',
        'status' => 'active',
        'headers' => json_encode([
            'Content-Type' => 'application/json'
        ]),
        'config' => json_encode([
            'wholesale_id' => '10',
            'group_id' => '3',
            'image_resize' => [
                'width' => '600',
                'height' => '600'
            ],
            'allowed_image_ext_string' => 'png, jpeg, jpg, webp',
            'image_check_change' => '2',
            'country_filter' => null
        ]),
        'updated_at' => now()
    ]);
} else {
    // Create new provider
    $providerId = DB::table('tb_api_providers')->insertGetId([
        'name' => 'TTN_ALL API',
        'code' => 'ttn_all',
        'url' => 'https://www.ttnplus.co.th/api/program',
        'period_endpoint' => null,
        'tour_detail_endpoint' => null,
        'requires_multi_step' => false,
        'url_parameters' => json_encode([]),
        'headers' => json_encode([
            'Content-Type' => 'application/json'
        ]),
        'config' => json_encode([
            'wholesale_id' => '10',
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
        'description' => 'TTN Plus API Provider for all tours',
        'created_at' => now(),
        'updated_at' => now()
    ]);
    echo "Created TTN_ALL provider with ID: $providerId\n";
}

// Delete existing mappings to recreate
DB::table('tb_api_field_mappings')->where('api_provider_id', $providerId)->delete();
echo "Deleted existing field mappings\n";

// Tour Field Mappings based on headcode
$tourMappings = [
    [
        'api_provider_id' => $providerId,
        'field_type' => 'tour',
        'local_field' => 'api_id',
        'api_field' => 'P_ID',
        'data_type' => 'integer',
        'transformation_rules' => json_encode([]),
        'is_required' => false,
        'created_at' => now(),
        'updated_at' => now()
    ],
    [
        'api_provider_id' => $providerId,
        'field_type' => 'tour',
        'local_field' => 'code1',
        'api_field' => 'P_CODE',
        'data_type' => 'string',
        'transformation_rules' => json_encode([]),
        'is_required' => false,
        'created_at' => now(),
        'updated_at' => now()
    ],
    [
        'api_provider_id' => $providerId,
        'field_type' => 'tour',
        'local_field' => 'name',
        'api_field' => 'P_NAME',
        'data_type' => 'string',
        'transformation_rules' => json_encode([]),
        'is_required' => false,
        'created_at' => now(),
        'updated_at' => now()
    ],
    [
        'api_provider_id' => $providerId,
        'field_type' => 'tour',
        'local_field' => 'description',
        'api_field' => 'P_DESC',
        'data_type' => 'string',
        'transformation_rules' => json_encode([]),
        'is_required' => false,
        'created_at' => now(),
        'updated_at' => now()
    ],
    [
        'api_provider_id' => $providerId,
        'field_type' => 'tour',
        'local_field' => 'image',
        'api_field' => 'img_url',
        'data_type' => 'string',
        'transformation_rules' => json_encode(['type' => 'image_download']),
        'is_required' => false,
        'created_at' => now(),
        'updated_at' => now()
    ],
    [
        'api_provider_id' => $providerId,
        'field_type' => 'tour',
        'local_field' => 'country_id',
        'api_field' => 'CountryName',
        'data_type' => 'string',
        'transformation_rules' => json_encode(['type' => 'country_mapping']),
        'is_required' => false,
        'created_at' => now(),
        'updated_at' => now()
    ],
    [
        'api_provider_id' => $providerId,
        'field_type' => 'tour',
        'local_field' => 'pdf_file',
        'api_field' => 'pdf_url',
        'data_type' => 'string',
        'transformation_rules' => json_encode(['type' => 'pdf_download']),
        'is_required' => false,
        'created_at' => now(),
        'updated_at' => now()
    ],
    [
        'api_provider_id' => $providerId,
        'field_type' => 'tour',
        'local_field' => 'data_type',
        'api_field' => 'static:2',
        'data_type' => 'integer',
        'transformation_rules' => json_encode(['type' => 'static_value', 'static_value' => 2]),
        'is_required' => false,
        'created_at' => now(),
        'updated_at' => now()
    ],
    [
        'api_provider_id' => $providerId,
        'field_type' => 'tour',
        'local_field' => 'api_type',
        'api_field' => 'static:ttn_all',
        'data_type' => 'string',
        'transformation_rules' => json_encode(['type' => 'static_value', 'static_value' => 'ttn_all']),
        'is_required' => false,
        'created_at' => now(),
        'updated_at' => now()
    ],
    [
        'api_provider_id' => $providerId,
        'field_type' => 'tour',
        'local_field' => 'group_id',
        'api_field' => 'static:3',
        'data_type' => 'integer',
        'transformation_rules' => json_encode(['type' => 'static_value', 'static_value' => 3]),
        'is_required' => false,
        'created_at' => now(),
        'updated_at' => now()
    ],
    [
        'api_provider_id' => $providerId,
        'field_type' => 'tour',
        'local_field' => 'wholesale_id',
        'api_field' => 'static:10',
        'data_type' => 'integer',
        'transformation_rules' => json_encode(['type' => 'static_value', 'static_value' => 10]),
        'is_required' => false,
        'created_at' => now(),
        'updated_at' => now()
    ]
];

// Period Field Mappings based on headcode
$periodMappings = [
    [
        'api_provider_id' => $providerId,
        'field_type' => 'period',
        'local_field' => 'period_api_id',
        'api_field' => 'PR_ID',
        'data_type' => 'integer',
        'transformation_rules' => json_encode([]),
        'is_required' => false,
        'created_at' => now(),
        'updated_at' => now()
    ],
    [
        'api_provider_id' => $providerId,
        'field_type' => 'period',
        'local_field' => 'period_code',
        'api_field' => 'PR_CODE',
        'data_type' => 'string',
        'transformation_rules' => json_encode([]),
        'is_required' => false,
        'created_at' => now(),
        'updated_at' => now()
    ],
    [
        'api_provider_id' => $providerId,
        'field_type' => 'period',
        'local_field' => 'start_date',
        'api_field' => 'PR_START_DATE',
        'data_type' => 'date',
        'transformation_rules' => json_encode([]),
        'is_required' => false,
        'created_at' => now(),
        'updated_at' => now()
    ],
    [
        'api_provider_id' => $providerId,
        'field_type' => 'period',
        'local_field' => 'end_date',
        'api_field' => 'PR_END_DATE',
        'data_type' => 'date',
        'transformation_rules' => json_encode([]),
        'is_required' => false,
        'created_at' => now(),
        'updated_at' => now()
    ],
    [
        'api_provider_id' => $providerId,
        'field_type' => 'period',
        'local_field' => 'day',
        'api_field' => 'PR_DAY',
        'data_type' => 'integer',
        'transformation_rules' => json_encode([]),
        'is_required' => false,
        'created_at' => now(),
        'updated_at' => now()
    ],
    [
        'api_provider_id' => $providerId,
        'field_type' => 'period',
        'local_field' => 'night',
        'api_field' => 'PR_NIGHT',
        'data_type' => 'integer',
        'transformation_rules' => json_encode([]),
        'is_required' => false,
        'created_at' => now(),
        'updated_at' => now()
    ],
    [
        'api_provider_id' => $providerId,
        'field_type' => 'period',
        'local_field' => 'price1',
        'api_field' => 'PR_PRICE1',
        'data_type' => 'decimal',
        'transformation_rules' => json_encode(['comment' => 'Adult twin share rate']),
        'is_required' => false,
        'created_at' => now(),
        'updated_at' => now()
    ],
    [
        'api_provider_id' => $providerId,
        'field_type' => 'period',
        'local_field' => 'price2',
        'api_field' => 'PR_PRICE2',
        'data_type' => 'decimal',
        'transformation_rules' => json_encode(['comment' => 'Adult single rate']),
        'is_required' => false,
        'created_at' => now(),
        'updated_at' => now()
    ],
    [
        'api_provider_id' => $providerId,
        'field_type' => 'period',
        'local_field' => 'price3',
        'api_field' => 'PR_PRICE3',
        'data_type' => 'decimal',
        'transformation_rules' => json_encode(['comment' => 'Child with bed rate']),
        'is_required' => false,
        'created_at' => now(),
        'updated_at' => now()
    ],
    [
        'api_provider_id' => $providerId,
        'field_type' => 'period',
        'local_field' => 'price4',
        'api_field' => 'PR_PRICE4',
        'data_type' => 'decimal',
        'transformation_rules' => json_encode(['comment' => 'Child no bed rate']),
        'is_required' => false,
        'created_at' => now(),
        'updated_at' => now()
    ],
    [
        'api_provider_id' => $providerId,
        'field_type' => 'period',
        'local_field' => 'special_price1',
        'api_field' => 'PR_SPECIAL_PRICE1',
        'data_type' => 'decimal',
        'transformation_rules' => json_encode(['comment' => 'Adult twin special price']),
        'is_required' => false,
        'created_at' => now(),
        'updated_at' => now()
    ],
    [
        'api_provider_id' => $providerId,
        'field_type' => 'period',
        'local_field' => 'special_price2',
        'api_field' => 'PR_SPECIAL_PRICE2',
        'data_type' => 'decimal',
        'transformation_rules' => json_encode(['comment' => 'Adult single special price']),
        'is_required' => false,
        'created_at' => now(),
        'updated_at' => now()
    ],
    [
        'api_provider_id' => $providerId,
        'field_type' => 'period',
        'local_field' => 'special_price3',
        'api_field' => 'PR_SPECIAL_PRICE3',
        'data_type' => 'decimal',
        'transformation_rules' => json_encode(['comment' => 'Child with bed special price']),
        'is_required' => false,
        'created_at' => now(),
        'updated_at' => now()
    ],
    [
        'api_provider_id' => $providerId,
        'field_type' => 'period',
        'local_field' => 'special_price4',
        'api_field' => 'PR_SPECIAL_PRICE4',
        'data_type' => 'decimal',
        'transformation_rules' => json_encode(['comment' => 'Child no bed special price']),
        'is_required' => false,
        'created_at' => now(),
        'updated_at' => now()
    ],
    [
        'api_provider_id' => $providerId,
        'field_type' => 'period',
        'local_field' => 'group',
        'api_field' => 'PR_GROUP',
        'data_type' => 'integer',
        'transformation_rules' => json_encode([]),
        'is_required' => false,
        'created_at' => now(),
        'updated_at' => now()
    ],
    [
        'api_provider_id' => $providerId,
        'field_type' => 'period',
        'local_field' => 'count',
        'api_field' => 'PR_COUNT',
        'data_type' => 'integer',
        'transformation_rules' => json_encode([]),
        'is_required' => false,
        'created_at' => now(),
        'updated_at' => now()
    ],
    [
        'api_provider_id' => $providerId,
        'field_type' => 'period',
        'local_field' => 'status_display',
        'api_field' => 'static:on',
        'data_type' => 'string',
        'transformation_rules' => json_encode(['type' => 'static_value', 'static_value' => 'on']),
        'is_required' => false,
        'created_at' => now(),
        'updated_at' => now()
    ],
    [
        'api_provider_id' => $providerId,
        'field_type' => 'period',
        'local_field' => 'status_period',
        'api_field' => 'PR_STATUS',
        'data_type' => 'integer',
        'transformation_rules' => json_encode([
            'type' => 'conditional',
            'rules' => [
                'if PR_STATUS == "Book" then status_period = 1',
                'elseif PR_STATUS == "Waitlist" then status_period = 2',
                'elseif PR_STATUS == "Close Group" OR PR_STATUS == "Soldout" then status_period = 3',
                'else status_period = 1'
            ]
        ]),
        'is_required' => false,
        'created_at' => now(),
        'updated_at' => now()
    ],
    [
        'api_provider_id' => $providerId,
        'field_type' => 'period',
        'local_field' => 'api_type',
        'api_field' => 'static:ttn_all',
        'data_type' => 'string',
        'transformation_rules' => json_encode(['type' => 'static_value', 'static_value' => 'ttn_all']),
        'is_required' => false,
        'created_at' => now(),
        'updated_at' => now()
    ],
    [
        'api_provider_id' => $providerId,
        'field_type' => 'period',
        'local_field' => 'group_date',
        'api_field' => 'PR_START_DATE',
        'data_type' => 'string',
        'transformation_rules' => json_encode([
            'type' => 'date_format',
            'format' => 'mY',
            'source_format' => 'Y-m-d'
        ]),
        'is_required' => false,
        'created_at' => now(),
        'updated_at' => now()
    ],
    [
        'api_provider_id' => $providerId,
        'field_type' => 'period',
        'local_field' => 'tour_id',
        'api_field' => 'parent:tour_id',
        'data_type' => 'integer',
        'transformation_rules' => json_encode(['source' => 'parent_tour_id']),
        'is_required' => true,
        'created_at' => now(),
        'updated_at' => now()
    ]
];

// Insert field mappings
$allMappings = array_merge($tourMappings, $periodMappings);
foreach ($allMappings as $mapping) {
    DB::table('tb_api_field_mappings')->insert($mapping);
}

echo "Added " . count($tourMappings) . " tour field mappings\n";
echo "Added " . count($periodMappings) . " period field mappings\n";

echo "TTN_ALL API Provider setup completed successfully!\n";