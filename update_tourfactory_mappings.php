<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Backend\ApiFieldMappingModel;

echo "=== Updating Tour Factory Mappings ===\n\n";

// Update period mappings to match actual API response
$updates = [
    ['local' => 'period_api_id', 'old_api' => 'period_id', 'new_api' => 'id'],
    ['local' => 'start_date', 'old_api' => 'start_date', 'new_api' => 'start'],
    ['local' => 'end_date', 'old_api' => 'end_date', 'new_api' => 'end'],
    ['local' => 'price1', 'old_api' => 'price_adult', 'new_api' => 'priceAdultDouble'],
    ['local' => 'price2', 'old_api' => 'price_single', 'new_api' => 'priceSingleRoomAdd'],
    ['local' => 'price3', 'old_api' => 'price_child', 'new_api' => 'priceChild'],
    ['local' => 'count', 'old_api' => 'seat_available', 'new_api' => 'available'],
];

foreach ($updates as $update) {
    $mapping = ApiFieldMappingModel::where('api_provider_id', 47)
        ->where('field_type', 'period')
        ->where('local_field', $update['local'])
        ->first();
        
    if ($mapping) {
        $oldApi = $mapping->api_field;
        $mapping->api_field = $update['new_api'];
        $mapping->save();
        echo "✓ Updated {$update['local']}: {$oldApi} → {$update['new_api']}\n";
    }
}

// Remove night mapping (day/night should come from tour, not period)
$nightMapping = ApiFieldMappingModel::where('api_provider_id', 47)
    ->where('field_type', 'period')
    ->where('local_field', 'night')
    ->first();
    
if ($nightMapping) {
    $nightMapping->delete();
    echo "\n✓ Removed night mapping (will use from tour instead)\n";
}

// Add status mapping
$statusMapping = ApiFieldMappingModel::where('api_provider_id', 47)
    ->where('field_type', 'period')
    ->where('local_field', 'status_period')
    ->first();
    
if (!$statusMapping) {
    $statusMapping = new ApiFieldMappingModel();
    $statusMapping->api_provider_id = 47;
    $statusMapping->field_type = 'period';
    $statusMapping->api_field = 'status';
    $statusMapping->local_field = 'status_period';
    $statusMapping->transformation_rules = json_encode([
        'type' => 'status_mapping',
        'rules' => [
            'Open' => 1,
            'Wait' => 2,
            'Close' => 3,
            'Cancel' => 3
        ]
    ]);
    $statusMapping->save();
    echo "\n✓ Added status_period mapping\n";
}

echo "\n=== Done! ===\n";
