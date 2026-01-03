<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Backend\ApiProviderModel;
use App\Models\Backend\TourModel;

echo "=== Testing Duplicate Code1 Check ===\n\n";

// Get Best provider
$provider = ApiProviderModel::where('code', 'bestconsortium')->first();
if (!$provider) {
    die("❌ Best Consortium provider not found!\n");
}
echo "✅ Found provider: {$provider->name}\n\n";

// Get field mapping for code1
$code1Mapping = $provider->fieldMappings()
    ->where('local_field', 'code1')
    ->where('field_type', 'tour')
    ->first();

if ($code1Mapping) {
    echo "✅ code1 field mapping found:\n";
    echo "   API field: {$code1Mapping->api_field}\n";
    echo "   Local field: {$code1Mapping->local_field}\n\n";
} else {
    echo "❌ NO code1 field mapping found!\n\n";
}

// Check for duplicate code1 values
$testCode1Values = ['BT-MMR16_8M', 'BT-MMR15_8M'];

foreach ($testCode1Values as $code1) {
    echo "Checking code1: $code1\n";
    
    $existing = TourModel::where('code1', $code1)
        ->whereNull('deleted_at')
        ->first();
    
    if ($existing) {
        echo "  ✅ FOUND in database - Tour ID: {$existing->id}\n";
        echo "     Name: {$existing->name}\n";
        echo "     API Type: {$existing->api_type}\n";
        echo "     Created: {$existing->created_at}\n";
    } else {
        echo "  ❌ NOT FOUND in database\n";
    }
    echo "\n";
}

echo "\n=== Conclusion ===\n";
if ($code1Mapping) {
    echo "✅ Duplicate check should work - field mapping exists\n";
} else {
    echo "❌ Duplicate check will FAIL - no field mapping for code1\n";
    echo "   Need to create field mapping in tb_api_field_mappings\n";
}
