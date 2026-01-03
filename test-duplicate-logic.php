<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Backend\ApiProviderModel;
use App\Models\Backend\TourModel;
use Illuminate\Support\Facades\DB;

echo "=== Testing Duplicate Check Logic ===\n\n";

// Get Best provider
$provider = ApiProviderModel::where('code', 'bestconsortium')->first();
if (!$provider) {
    die("❌ Provider not found\n");
}

echo "✅ Provider: {$provider->name}\n";
echo "   Code: {$provider->code}\n\n";

// Simulate tour data from API (the one that causes duplicate error)
$tourData = [
    'id' => 1475,  // api_id
    'code' => 'BT-MMR16_8M',  // This should map to code1
    'name' => 'มหัศจรรย์..MYANMAR ย่างกุ้ง หงสา สักการะสิ่งศักดิ์สิทธิ์ ที่ต้องห้ามพลาด',
];

echo "📦 Simulating API data:\n";
echo "   API id: {$tourData['id']}\n";
echo "   API code: {$tourData['code']}\n\n";

// Get code1 field mapping
$code1Mapping = $provider->fieldMappings()
    ->where('field_type', 'tour')
    ->where('local_field', 'code1')
    ->first();

if (!$code1Mapping) {
    die("❌ No code1 mapping found!\n");
}

echo "✅ code1 mapping found:\n";
echo "   API field: {$code1Mapping->api_field}\n";
echo "   Maps to: {$code1Mapping->local_field}\n\n";

// Get code1 value from API data
$code1Value = $tourData[$code1Mapping->api_field] ?? null;

if (!$code1Value) {
    die("❌ code1 value not found in API data!\n");
}

echo "✅ code1 value from API: {$code1Value}\n\n";

// Check for duplicate in database
echo "🔍 Checking for duplicate in database...\n";

$duplicateTour = DB::transaction(function() use ($code1Value) {
    return TourModel::where('code1', $code1Value)
        ->whereNull('deleted_at')
        ->lockForUpdate()
        ->first();
});

if ($duplicateTour) {
    echo "\n✅✅✅ DUPLICATE FOUND! ✅✅✅\n";
    echo "   Tour ID: {$duplicateTour->id}\n";
    echo "   Tour Code: {$duplicateTour->code}\n";
    echo "   Tour Name: {$duplicateTour->name}\n";
    echo "   API Type: {$duplicateTour->api_type}\n";
    echo "   API ID: {$duplicateTour->api_id}\n";
    echo "   Created: {$duplicateTour->created_at}\n";
    echo "\n✅ Should SKIP this tour (no insert)\n";
    echo "✅ Duplicate check is WORKING!\n";
} else {
    echo "\n❌ NO DUPLICATE FOUND\n";
    echo "❌ Would try to INSERT and get duplicate error!\n";
    echo "❌ Duplicate check is FAILING!\n";
}

echo "\n=== Testing the actual code path ===\n";

// Now let's check if the code in ApiManagementController has this logic
$controllerPath = __DIR__ . '/app/Http/Controllers/Backend/ApiManagementController.php';
$controllerCode = file_get_contents($controllerPath);

$checks = [
    'CHECK DUPLICATE CODE1 BEFORE MAPPING' => strpos($controllerCode, 'CHECK DUPLICATE CODE1 BEFORE MAPPING') !== false,
    'code1Mapping = $provider->fieldMappings()' => strpos($controllerCode, 'code1Mapping = $provider->fieldMappings()') !== false,
    'tourData[$code1Mapping->api_field]' => strpos($controllerCode, 'tourData[$code1Mapping->api_field]') !== false,
    'lockForUpdate()' => strpos($controllerCode, 'lockForUpdate()') !== false,
    'duplicate_skipped' => strpos($controllerCode, 'duplicate_skipped') !== false,
];

foreach ($checks as $check => $found) {
    $status = $found ? '✅' : '❌';
    echo "$status $check\n";
}

if (in_array(false, $checks)) {
    echo "\n❌ Some required code is missing!\n";
    echo "❌ Need to verify file is uploaded correctly\n";
} else {
    echo "\n✅ All required code is present\n";
    echo "✅ Logic should work!\n";
}
