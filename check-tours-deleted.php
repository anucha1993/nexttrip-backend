<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Backend\TourModel;

echo "=== Checking BT-MMR16_8M and BT-MMR15_8M ===\n\n";

$testCodes = ['BT-MMR16_8M', 'BT-MMR15_8M'];

foreach ($testCodes as $code) {
    echo "Code: $code\n";
    
    // Check with deleted
    $withDeleted = TourModel::withTrashed()->where('code1', $code)->first();
    
    if ($withDeleted) {
        echo "  ✅ FOUND (including deleted)\n";
        echo "     ID: {$withDeleted->id}\n";
        echo "     Name: {$withDeleted->name}\n";
        echo "     api_type: {$withDeleted->api_type}\n";
        echo "     deleted_at: " . ($withDeleted->deleted_at ? $withDeleted->deleted_at : 'NULL') . "\n";
        echo "     created_at: {$withDeleted->created_at}\n";
    } else {
        echo "  ❌ NOT FOUND (even deleted ones)\n";
    }
    
    // Check how many times this code appears in API response
    echo "\n";
}

// Check Best Consortium API response
echo "\n=== Fetching Best Consortium API ===\n";
$provider = \App\Models\Backend\ApiProviderModel::where('code', 'bestconsortium')->first();

if ($provider) {
    try {
        $response = \Http::get($provider->url);
        $tours = $response->json();
        
        if (is_array($tours)) {
            $codes = array_column($tours, 'code');
            $codeCount = array_count_values($codes);
            
            foreach ($testCodes as $testCode) {
                $count = $codeCount[$testCode] ?? 0;
                echo "$testCode appears $count time(s) in API response\n";
            }
        }
    } catch (\Exception $e) {
        echo "Error fetching API: " . $e->getMessage() . "\n";
    }
}
