<?php
require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Analyzing All API Providers for Missing Condition Types ===\n\n";

try {
    // Get all providers from database
    $providers = DB::table('tb_api_providers')->where('status', 'active')->get();
    
    echo "Found " . $providers->count() . " active providers:\n";
    foreach ($providers as $provider) {
        echo "- {$provider->name} (Code: {$provider->code})\n";
    }
    echo "\n";
    
    // Analyze headcode patterns for each API
    $apiPatterns = [
        'zego' => [
            'conditions' => [
                'price_calculation' => 'Calculate discount percentage for promotions',
                'price_group_assignment' => 'Assign price group based on net price ranges',
                'period_status_mapping' => 'Map PeriodStatus (Book/Waitlist/Close Group/Soldout) to status_period',
                'image_processing' => 'Download and process tour images',
                'pdf_processing' => 'Download and process PDF files with version check',
                'country_mapping' => 'Map CountryName to country_id',
                'airline_mapping' => 'Map AirlineCode to airline_id'
            ],
            'promotion_rules' => [
                'Fire Sale: >= 30% discount',
                'Normal Promotion: < 30% and > 0% discount', 
                'No Promotion: <= 0% discount'
            ],
            'status_mapping' => [
                'Book => status_period = 1',
                'Waitlist => status_period = 2',
                'Close Group/Soldout => status_period = 3'
            ]
        ],
        'best' => [
            'conditions' => [
                'price_calculation' => 'Calculate discount percentage from adultPrice_old vs adultPrice',
                'price_group_assignment' => 'Assign price group based on net price',
                'period_status_assignment' => 'Customer defines status_period manually',
                'image_processing' => 'Download bannerSq images with content-length check',
                'pdf_processing' => 'Download filePdf with proper filename handling',
                'country_mapping' => 'Map nameEng to country_id',
                'airline_mapping' => 'Map airline_name to airline_id (by name match)'
            ],
            'promotion_rules' => 'Same as ZEGO (30% threshold)',
            'special_features' => [
                'Rate limiting handling',
                'Content-Length validation for images',
                'Nested country/tour API calls'
            ]
        ],
        'ttn' => [
            'conditions' => [
                'price_calculation' => 'Calculate discount percentage',
                'price_group_assignment' => 'Assign price group based on net price',
                'period_status_assignment' => 'Map period status',
                'image_processing' => 'Download BANNER images',
                'pdf_link_storage' => 'Store PDF as Google Drive link (no download)',
                'country_mapping' => 'Fixed to JAPAN country',
                'airline_mapping' => 'Map P_AIRLINE to airline_id'
            ],
            'promotion_rules' => 'Same as ZEGO (30% threshold)',
            'special_features' => [
                'Fixed country (JAPAN)',
                'PDF stored as link, not downloaded',
                'P_LOCATION mapping to location'
            ]
        ],
        'ttn_all' => [
            'conditions' => [
                'price_calculation' => 'Calculate discount percentage from PR_PRICE vs PR_SPECIAL_PRICE',
                'price_group_assignment' => 'Assign price group based on net price ranges',
                'period_status_assignment' => 'Map P_AVAILABLE count to status_period',
                'image_processing' => 'Download img_url images',
                'pdf_processing' => 'Download pdf_url files',
                'country_mapping' => 'Map CountryName to country_id'
            ],
            'promotion_rules' => 'Same as ZEGO (30% threshold)',
            'status_mapping' => [
                'P_AVAILABLE > 0 => status_period = 1',
                'P_AVAILABLE <= 0 => status_period = 3'
            ]
        ],
        'itravel' => [
            'conditions' => [
                'price_calculation' => 'Calculate discount percentage',
                'price_group_assignment' => 'Assign price group based on net price',
                'image_processing' => 'Download tour images',
                'pdf_processing' => 'Download PDF files',
                'country_mapping' => 'Map country data',
                'airline_mapping' => 'Map airline data'
            ],
            'promotion_rules' => 'Same as ZEGO (30% threshold)'
        ]
    ];
    
    // Check current condition types in UI
    echo "=== Current Condition Types in UI ===\n";
    $currentConditionTypes = [
        'country_mapping',
        'airline_mapping', 
        'image_processing',
        'pdf_processing',
        'price_calculation',
        'price_group_assignment',
        'period_status_assignment',
        'data_update_check',
        'field_transformation',
        'data_validation',
        'text_processing'
    ];
    
    foreach ($currentConditionTypes as $type) {
        echo "✅ $type\n";
    }
    
    // Identify missing condition types needed
    echo "\n=== Missing Condition Types Needed ===\n";
    $missingTypes = [
        'period_status_mapping' => 'Map API status values to status_period numbers',
        'pdf_link_storage' => 'Store PDF as link instead of downloading',
        'content_length_validation' => 'Validate image content-length before processing',
        'rate_limiting_handler' => 'Handle API rate limiting and retries',
        'nested_api_calls' => 'Handle multi-step API calls (country->tours)',
        'fixed_value_assignment' => 'Assign fixed values (like Japan for TTN)',
        'availability_status_mapping' => 'Map availability counts to status'
    ];
    
    foreach ($missingTypes as $type => $description) {
        echo "❌ $type: $description\n";
    }
    
    // Check current action types
    echo "\n=== Current Action Types in UI ===\n";
    $currentActionTypes = [
        'lookup_database',
        'download_image',
        'download_file',
        'set_value',
        'transform_value',
        'skip_record'
    ];
    
    foreach ($currentActionTypes as $type) {
        echo "✅ $type\n";
    }
    
    // Identify missing action types
    echo "\n=== Missing Action Types Needed ===\n";
    $missingActionTypes = [
        'store_link' => 'Store as link/URL instead of downloading',
        'validate_content' => 'Validate content before processing',
        'handle_rate_limit' => 'Handle API rate limiting',
        'map_status_value' => 'Map status string to numeric value',
        'calculate_percentage' => 'Calculate percentage values',
        'assign_fixed_value' => 'Assign predefined fixed value'
    ];
    
    foreach ($missingActionTypes as $type => $description) {
        echo "❌ $type: $description\n";
    }
    
    echo "\n=== Recommendations ===\n";
    echo "1. Add missing condition types to handle all API patterns\n";
    echo "2. Add missing action types for specialized processing\n";
    echo "3. Create provider-specific condition templates\n";
    echo "4. Update UI dropdowns with all required options\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>