<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Backend\TourModel;
use Illuminate\Support\Facades\DB;

echo "=== Checking Tour ID 9773 ===\n\n";

$tour = TourModel::find(9773);

if (!$tour) {
    die("❌ Tour ID 9773 not found!\n");
}

echo "✅ Found Tour ID 9773\n";
echo "code: {$tour->code}\n";
echo "code1: '{$tour->code1}'\n";
echo "code1 length: " . strlen($tour->code1) . "\n";
echo "code1 (hex): " . bin2hex($tour->code1) . "\n";
echo "api_id: {$tour->api_id}\n";
echo "api_type: {$tour->api_type}\n";
echo "deleted_at: " . ($tour->deleted_at ? $tour->deleted_at : 'NULL') . "\n";
echo "name: {$tour->name}\n\n";

// Test matching this code1
$testCode = 'BT-MMR15_8M';
echo "Testing match with: '{$testCode}'\n";
echo "Test length: " . strlen($testCode) . "\n";
echo "Test (hex): " . bin2hex($testCode) . "\n\n";

echo "String comparison:\n";
echo "  tour->code1 == testCode: " . ($tour->code1 == $testCode ? 'TRUE' : 'FALSE') . "\n";
echo "  tour->code1 === testCode: " . ($tour->code1 === $testCode ? 'TRUE' : 'FALSE') . "\n";
echo "  strcmp: " . strcmp($tour->code1, $testCode) . "\n\n";

// Test query
echo "Query test:\n";
$found = TourModel::where('code1', $testCode)->whereNull('deleted_at')->first();
echo "  where('code1', '{$testCode}'): " . ($found ? "FOUND ID={$found->id}" : "NOT FOUND") . "\n";

$found2 = TourModel::where('code1', $tour->code1)->whereNull('deleted_at')->first();
echo "  where('code1', tour->code1): " . ($found2 ? "FOUND ID={$found2->id}" : "NOT FOUND") . "\n";

// Check for hidden characters
echo "\nCharacter analysis:\n";
for ($i = 0; $i < strlen($tour->code1); $i++) {
    $char = $tour->code1[$i];
    $ascii = ord($char);
    echo "  pos $i: '$char' (ASCII: $ascii, hex: " . dechex($ascii) . ")\n";
}
