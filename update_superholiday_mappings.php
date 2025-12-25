<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Updating Super Holiday Field Mappings ===\n\n";

// Get Super Holiday provider
$provider = \App\Models\Backend\ApiProviderModel::where('code', 'superbholiday')
    ->orWhere('code', 'superb_holiday')
    ->first();

if (!$provider) {
    echo "❌ Super Holiday provider not found!\n";
    exit(1);
}

echo "Provider ID: {$provider->id}\n";
echo "Provider Name: {$provider->name}\n\n";

// Update api_type mapping
echo "Updating api_type field mapping...\n";
$apiTypeMapping = \App\Models\Backend\ApiFieldMappingModel::where('api_provider_id', $provider->id)
    ->where('field_type', 'tour')
    ->where('local_field', 'api_type')
    ->first();

if ($apiTypeMapping) {
    $apiTypeMapping->transformation_rules = [
        [
            'type' => 'static_value',
            'value' => 'superbholiday',
            'description' => 'Static value: superbholiday'
        ]
    ];
    $apiTypeMapping->save();
    echo "✓ api_type mapping updated\n";
} else {
    echo "❌ api_type mapping not found\n";
}

// Update data_type mapping  
echo "Updating data_type field mapping...\n";
$dataTypeMapping = \App\Models\Backend\ApiFieldMappingModel::where('api_provider_id', $provider->id)
    ->where('field_type', 'tour')
    ->where('local_field', 'data_type')
    ->first();

if ($dataTypeMapping) {
    $dataTypeMapping->transformation_rules = [
        [
            'type' => 'static_value',
            'value' => 2,
            'description' => 'Static value: 2 (API data type)'
        ]
    ];
    $dataTypeMapping->save();
    echo "✓ data_type mapping updated\n";
} else {
    echo "❌ data_type mapping not found\n";
}

// Update status_display for periods
echo "\nUpdating period status_display field mapping...\n";
$statusDisplayMapping = \App\Models\Backend\ApiFieldMappingModel::where('api_provider_id', $provider->id)
    ->where('field_type', 'period')
    ->where('local_field', 'status_display')
    ->first();

if ($statusDisplayMapping) {
    $statusDisplayMapping->transformation_rules = [
        [
            'type' => 'static_value',
            'value' => 'on',
            'description' => 'Static value: on'
        ]
    ];
    $statusDisplayMapping->save();
    echo "✓ status_display mapping updated\n";
} else {
    echo "❌ status_display mapping not found\n";
}

// Update period api_type
echo "Updating period api_type field mapping...\n";
$periodApiTypeMapping = \App\Models\Backend\ApiFieldMappingModel::where('api_provider_id', $provider->id)
    ->where('field_type', 'period')
    ->where('local_field', 'api_type')
    ->first();

if ($periodApiTypeMapping) {
    $periodApiTypeMapping->transformation_rules = [
        [
            'type' => 'static_value',
            'value' => 'superbholiday',
            'description' => 'Static value: superbholiday'
        ]
    ];
    $periodApiTypeMapping->save();
    echo "✓ period api_type mapping updated\n";
} else {
    echo "❌ period api_type mapping not found\n";
}

echo "\n✓ Done!\n";
