<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Backend\TourModel;

echo "=== Super Holiday Quick Check ===\n";

try {
    // Check for existing tours with the problematic codes
    $problematicCodes = [
        'SPHZ-CNF1-PA',
        'SPHZ-CNF1-PB', 
        'SPHZ-CXJ08',
        'SPHZ-CNB6',
        'SPHZ-TSN01'
    ];
    
    echo "Checking existing tours with problematic codes:\n";
    foreach ($problematicCodes as $code) {
        $existingTour = TourModel::where('code1', $code)->first();
        if ($existingTour) {
            echo "  $code: Found (ID: {$existingTour->id}, API Type: '{$existingTour->api_type}', Deleted: " . 
                 ($existingTour->deleted_at ? 'YES' : 'NO') . ")\n";
                 
            // Try to update this tour to set api_type
            if (empty($existingTour->api_type)) {
                echo "    -> Updating api_type to 'superbholiday'\n";
                $existingTour->api_type = 'superbholiday';
                $existingTour->save();
            }
        } else {
            echo "  $code: Not found\n";
        }
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== Quick Check Completed ===\n";