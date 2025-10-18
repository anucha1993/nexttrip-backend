<?php
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Setting up Default API Conditions based on hardcode patterns ===" . PHP_EOL;

// Get API providers
$providers = App\Models\Backend\ApiProviderModel::whereIn('code', ['zego', 'bestconsortium', 'ttn_japan', 'tourfactory', 'go365'])->get();

foreach ($providers as $provider) {
    echo PHP_EOL . "Setting up conditions for: " . $provider->name . " (" . $provider->code . ")" . PHP_EOL;
    
    // ลบ conditions เดิม
    $provider->conditions()->delete();
    
    // ตั้งค่า conditions ตามแต่ละ provider
    switch ($provider->code) {
        case 'zego':
            setupZegoConditions($provider);
            break;
            
        case 'bestconsortium':
            setupBestConsortiumConditions($provider);
            break;
            
        case 'ttn_japan':
            setupTTNJapanConditions($provider);
            break;
            
        case 'tourfactory':
            setupTourFactoryConditions($provider);
            break;
            
        case 'go365':
            setupGO365Conditions($provider);
            break;
    }
    
    $conditionsCount = $provider->conditions()->count();
    echo "Created {$conditionsCount} conditions for {$provider->name}" . PHP_EOL;
}

// Functions to setup conditions for each provider

function setupZegoConditions($provider) {
    // 1. Country Mapping - if CountryName exists
    App\Models\Backend\ApiConditionModel::create([
        'api_provider_id' => $provider->id,
        'condition_type' => 'country_mapping',
        'field_name' => 'CountryName',
        'operator' => 'EXISTS',
        'value' => null,
        'action_type' => 'lookup_database',
        'priority' => 1,
        'is_active' => true,
        'condition_rules' => [
            'table' => 'tb_country',
            'search_field' => 'country_name_en',
            'search_type' => 'LIKE',
            'where_conditions' => [
                ['status', '=', 'on'],
                ['deleted_at', 'IS', 'NULL']
            ],
            'result_field' => 'id',
            'target_field' => 'country_id',
            'result_format' => 'json_array'
        ],
        'action_rules' => [
            'success_action' => 'set_field_value',
            'failure_action' => 'set_empty_array'
        ]
    ]);

    // 2. Airline Mapping - if AirlineCode exists  
    App\Models\Backend\ApiConditionModel::create([
        'api_provider_id' => $provider->id,
        'condition_type' => 'airline_mapping',
        'field_name' => 'AirlineCode',
        'operator' => 'EXISTS',
        'value' => null,
        'action_type' => 'lookup_database',
        'priority' => 2,
        'is_active' => true,
        'condition_rules' => [
            'table' => 'tb_travel_type',
            'search_field' => 'code',
            'search_type' => 'EXACT',
            'where_conditions' => [
                ['status', '=', 'on'],
                ['deleted_at', 'IS', 'NULL']
            ],
            'result_field' => 'id',
            'target_field' => 'airline_id'
        ],
        'action_rules' => [
            'success_action' => 'set_field_value',
            'failure_action' => 'skip'
        ]
    ]);

    // 3. Image Processing - if URLImage exists
    App\Models\Backend\ApiConditionModel::create([
        'api_provider_id' => $provider->id,
        'condition_type' => 'image_processing',
        'field_name' => 'URLImage',
        'operator' => 'EXISTS',
        'value' => null,
        'action_type' => 'download_image',
        'priority' => 3,
        'is_active' => true,
        'condition_rules' => [
            'resize_width' => 600,
            'resize_height' => 600,
            'allowed_extensions' => ['png', 'jpeg', 'jpg', 'webp'],
            'save_path' => 'upload/tour/zego/',
            'check_content_length' => true
        ],
        'action_rules' => [
            'success_action' => 'set_field_value',
            'failure_action' => 'log_error'
        ]
    ]);

    // 4. Data Update Check - country_check_change
    App\Models\Backend\ApiConditionModel::create([
        'api_provider_id' => $provider->id,
        'condition_type' => 'data_update_check',
        'field_name' => 'country_check_change',
        'operator' => 'NOT EXISTS',
        'value' => null,
        'action_type' => 'set_value',
        'priority' => 4,
        'is_active' => true,
        'condition_rules' => [
            'check_field' => 'country_check_change',
            'trigger_condition' => 'IS NULL',
            'execute_action' => 'country_mapping'
        ],
        'action_rules' => [
            'action' => 'conditional_country_mapping'
        ]
    ]);

    // 5. Data Update Check - airline_check_change  
    App\Models\Backend\ApiConditionModel::create([
        'api_provider_id' => $provider->id,
        'condition_type' => 'data_update_check',
        'field_name' => 'airline_check_change',
        'operator' => 'NOT EXISTS',
        'value' => null,
        'action_type' => 'set_value',
        'priority' => 5,
        'is_active' => true,
        'condition_rules' => [
            'check_field' => 'airline_check_change',
            'trigger_condition' => 'IS NULL',
            'execute_action' => 'airline_mapping'
        ],
        'action_rules' => [
            'action' => 'conditional_airline_mapping'
        ]
    ]);

    // 6. Text Processing - ProductName
    App\Models\Backend\ApiConditionModel::create([
        'api_provider_id' => $provider->id,
        'condition_type' => 'text_processing',
        'field_name' => 'ProductName',
        'operator' => 'EXISTS',
        'value' => null,
        'action_type' => 'set_value',
        'priority' => 6,
        'is_active' => true,
        'condition_rules' => [
            'source_field' => 'ProductName',
            'target_field' => 'name',
            'transformations' => []
        ],
        'action_rules' => [
            'action' => 'direct_assignment'
        ]
    ]);

    // 7. Text Processing - Highlight (description)
    App\Models\Backend\ApiConditionModel::create([
        'api_provider_id' => $provider->id,
        'condition_type' => 'text_processing',
        'field_name' => 'Highlight',
        'operator' => 'EXISTS',
        'value' => null,
        'action_type' => 'transform_value',
        'priority' => 7,
        'is_active' => true,
        'condition_rules' => [
            'source_field' => 'Highlight',
            'target_field' => 'description',
            'transformations' => [
                ['type' => 'string_replace', 'search' => '\n', 'replace' => '']
            ]
        ],
        'action_rules' => [
            'action' => 'transform_and_assign'
        ]
    ]);

    // 8. Field Transformation - MaxHotelStars to rating
    App\Models\Backend\ApiConditionModel::create([
        'api_provider_id' => $provider->id,
        'condition_type' => 'field_transformation',
        'field_name' => 'MaxHotelStars',
        'operator' => 'EXISTS',
        'value' => null,
        'action_type' => 'set_value',
        'priority' => 8,
        'is_active' => true,
        'condition_rules' => [
            'source_field' => 'MaxHotelStars',
            'target_field' => 'rating',
            'data_type' => 'integer'
        ],
        'action_rules' => [
            'action' => 'direct_assignment'
        ]
    ]);
}

function setupBestConsortiumConditions($provider) {
    // เหมือน Zego แต่เปลี่ยน path image
    App\Models\Backend\ApiConditionModel::create([
        'api_provider_id' => $provider->id,
        'condition_type' => 'country_mapping',
        'field_name' => 'CountryName',
        'operator' => 'EXISTS',
        'value' => null,
        'action_type' => 'lookup_database',
        'priority' => 1,
        'is_active' => true,
        'condition_rules' => [
            'table' => 'tb_country',
            'search_field' => 'country_name_en',
            'search_type' => 'LIKE'
        ],
        'action_rules' => []
    ]);

    App\Models\Backend\ApiConditionModel::create([
        'api_provider_id' => $provider->id,
        'condition_type' => 'image_processing',
        'field_name' => 'URLImage',
        'operator' => 'EXISTS',
        'value' => null,
        'action_type' => 'download_image',
        'priority' => 2,
        'is_active' => true,
        'condition_rules' => [
            'save_path' => 'upload/tour/bestconsortium/',
            'resize_width' => 600,
            'resize_height' => 600
        ],
        'action_rules' => []
    ]);
}

function setupTTNJapanConditions($provider) {
    // TTN Japan มี pattern พิเศษ
    App\Models\Backend\ApiConditionModel::create([
        'api_provider_id' => $provider->id,
        'condition_type' => 'field_transformation',
        'field_name' => 'Program_Name',
        'operator' => 'EXISTS',
        'value' => null,
        'action_type' => 'set_value',
        'priority' => 1,
        'is_active' => true,
        'condition_rules' => [
            'source_field' => 'Program_Name',
            'target_field' => 'name'
        ],
        'action_rules' => []
    ]);

    App\Models\Backend\ApiConditionModel::create([
        'api_provider_id' => $provider->id,
        'condition_type' => 'image_processing',
        'field_name' => 'Image_Name',
        'operator' => 'EXISTS',
        'value' => null,
        'action_type' => 'download_image',
        'priority' => 2,
        'is_active' => true,
        'condition_rules' => [
            'save_path' => 'upload/tour/ttn_japan/',
            'base_url' => 'https://www.ttngroup.co.th/'
        ],
        'action_rules' => []
    ]);
}

function setupTourFactoryConditions($provider) {
    // Tour Factory conditions
    App\Models\Backend\ApiConditionModel::create([
        'api_provider_id' => $provider->id,
        'condition_type' => 'field_transformation',
        'field_name' => 'product_name',
        'operator' => 'EXISTS',
        'value' => null,
        'action_type' => 'set_value',
        'priority' => 1,
        'is_active' => true,
        'condition_rules' => [
            'source_field' => 'product_name',
            'target_field' => 'name'
        ],
        'action_rules' => []
    ]);
}

function setupGO365Conditions($provider) {
    // GO365 conditions
    App\Models\Backend\ApiConditionModel::create([
        'api_provider_id' => $provider->id,
        'condition_type' => 'field_transformation',
        'field_name' => 'tour_name',
        'operator' => 'EXISTS',
        'value' => null,
        'action_type' => 'set_value',
        'priority' => 1,
        'is_active' => true,
        'condition_rules' => [
            'source_field' => 'tour_name',
            'target_field' => 'name'
        ],
        'action_rules' => []
    ]);

    App\Models\Backend\ApiConditionModel::create([
        'api_provider_id' => $provider->id,
        'condition_type' => 'image_processing',
        'field_name' => 'tour_image',
        'operator' => 'EXISTS',
        'value' => null,
        'action_type' => 'download_image',
        'priority' => 2,
        'is_active' => true,
        'condition_rules' => [
            'save_path' => 'upload/tour/go365/'
        ],
        'action_rules' => []
    ]);
}

echo PHP_EOL . "=== Summary ===" . PHP_EOL;
echo "Total conditions created: " . App\Models\Backend\ApiConditionModel::count() . PHP_EOL;

// Display all conditions
$allConditions = App\Models\Backend\ApiConditionModel::with('apiProvider')->get();
foreach ($allConditions as $condition) {
    echo sprintf("%s: %s (%s -> %s)" . PHP_EOL,
        $condition->apiProvider->name,
        $condition->condition_type,
        $condition->field_name,
        $condition->action_type
    );
}