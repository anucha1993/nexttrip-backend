<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

// Use full class paths
$ApiProviderModel = 'App\Models\Backend\ApiProviderModel';
$TourModel = 'App\Models\Backend\TourModel';

echo "=== Testing Best Consortium Duplicate Check ===\n\n";

// Get Best Consortium provider
$provider = $ApiProviderModel::where('code', 'bestconsortium')->first();

if (!$provider) {
    echo "❌ Provider 'bestconsortium' not found!\n";
    exit(1);
}

echo "✓ Provider found: ID {$provider->id}, Code: {$provider->code}\n\n";

// Get code1 field mapping
$code1Mapping = $provider->fieldMappings()
    ->where('field_type', 'tour')
    ->where('local_field', 'code1')
    ->first();

if (!$code1Mapping) {
    echo "❌ code1 field mapping not found!\n";
    exit(1);
}

echo "✓ code1 Mapping found:\n";
echo "  - API Field: {$code1Mapping->api_field}\n";
echo "  - Local Field: {$code1Mapping->local_field}\n\n";

// Simulate API data
$testTourData = [
    'code' => 'BT-MMR16_8M',
    'name' => 'Test Tour Myanmar',
    'api_id' => 1475
];

echo "=== Simulating API Data ===\n";
echo "API Field to check: {$code1Mapping->api_field}\n";
echo "Value from API data: " . ($testTourData[$code1Mapping->api_field] ?? 'NOT FOUND') . "\n\n";

if (!isset($testTourData[$code1Mapping->api_field])) {
    echo "❌ PROBLEM: API field '{$code1Mapping->api_field}' not found in tour data!\n";
    echo "Available fields: " . implode(', ', array_keys($testTourData)) . "\n";
    exit(1);
}

$code1Value = $testTourData[$code1Mapping->api_field];
echo "✓ Code1 value extracted: '$code1Value'\n\n";

// Now check for duplicates
echo "=== Checking for Duplicates ===\n";

$duplicateTour = DB::transaction(function() use ($code1Value, $TourModel) {
    // First check active tours
    $activeTour = $TourModel::where('code1', $code1Value)
        ->whereNull('deleted_at')
        ->lockForUpdate()
        ->first();
    
    if ($activeTour) {
        echo "Found active tour: ID {$activeTour->id}\n";
        return $activeTour;
    }
    
    // Also check soft deleted tours
    $deletedTour = $TourModel::where('code1', $code1Value)
        ->whereNotNull('deleted_at')
        ->lockForUpdate()
        ->first();
    
    if ($deletedTour) {
        echo "Found SOFT DELETED tour: ID {$deletedTour->id}, deleted_at: {$deletedTour->deleted_at}\n";
    }
    
    return $deletedTour;
});

if ($duplicateTour) {
    $isDeleted = $duplicateTour->deleted_at !== null;
    
    echo "\n✓ DUPLICATE FOUND!\n";
    echo "  - Tour ID: {$duplicateTour->id}\n";
    echo "  - code1: {$duplicateTour->code1}\n";
    echo "  - Is Deleted: " . ($isDeleted ? 'YES' : 'NO') . "\n";
    echo "  - deleted_at: " . ($duplicateTour->deleted_at ?? 'NULL') . "\n";
    echo "\n";
    echo "✓ Action: SKIP CREATION (return early from function)\n";
} else {
    echo "\n❌ NO DUPLICATE FOUND - Would attempt INSERT (and fail on constraint)\n";
}

echo "\n=== Test Complete ===\n";
