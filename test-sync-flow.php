<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Backend\ApiProviderModel;
use App\Models\Backend\TourModel;
use App\Http\Controllers\Backend\ApiManagementController;

echo "=== Testing Best Consortium Sync Flow ===\n\n";

// Get Best provider
$provider = ApiProviderModel::where('code', 'bestconsortium')->first();
if (!$provider) {
    die("❌ Best Consortium provider not found!\n");
}

echo "✅ Provider: {$provider->name} (code: {$provider->code})\n\n";

// Simulate tour data from API
$tourData = [
    'id' => 1475, // api_id
    'code' => 'BT-MMR16_8M', // This should map to code1
    'name' => 'Test Tour Myanmar',
];

echo "📦 Sample API Data:\n";
echo "   id: {$tourData['id']}\n";
echo "   code: {$tourData['code']}\n\n";

// Get field mappings
$fieldMappings = $provider->fieldMappings()->where('field_type', 'tour')->get();

echo "📋 Field Mappings for 'tour':\n";
foreach ($fieldMappings as $mapping) {
    echo "   {$mapping->api_field} → {$mapping->local_field}";
    if ($mapping->static_value) {
        echo " (static: {$mapping->static_value})";
    }
    echo "\n";
}

// Find code1 mapping
$code1Mapping = $fieldMappings->firstWhere('local_field', 'code1');
if ($code1Mapping) {
    echo "\n✅ code1 mapping: {$code1Mapping->api_field} → code1\n";
    
    $code1Value = $tourData[$code1Mapping->api_field] ?? null;
    echo "   Value from API: " . ($code1Value ?? 'NULL') . "\n";
    
    if ($code1Value) {
        // Check if exists in DB
        $existing = TourModel::where('code1', $code1Value)->whereNull('deleted_at')->first();
        if ($existing) {
            echo "\n❌ DUPLICATE EXISTS in database!\n";
            echo "   Tour ID: {$existing->id}\n";
            echo "   Name: {$existing->name}\n";
            echo "   api_type: {$existing->api_type}\n";
            echo "   Created: {$existing->created_at}\n";
            echo "\n✅ Should SKIP this tour during sync\n";
        } else {
            echo "\n✅ No duplicate - OK to insert\n";
        }
    }
} else {
    echo "\n❌ NO code1 mapping found!\n";
}

echo "\n=== Checking if duplicate check code exists ===\n";
$controllerContent = file_get_contents(__DIR__ . '/app/Http/Controllers/Backend/ApiManagementController.php');
if (strpos($controllerContent, 'DEBUG: Before duplicate check') !== false) {
    echo "✅ Debug logging code is present\n";
} else {
    echo "❌ Debug logging code NOT found\n";
}

if (strpos($controllerContent, 'SIMPLE: Check if code1 already exists') !== false) {
    echo "✅ Duplicate check code is present\n";
} else {
    echo "❌ Duplicate check code NOT found\n";
}
