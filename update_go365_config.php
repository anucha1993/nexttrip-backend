<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Updating GO365 API Provider configuration...\n";

// Get GO365 provider
$go365 = DB::table('tb_api_providers')->where('code', 'go365')->first();

if (!$go365) {
    echo "GO365 provider not found!\n";
    exit(1);
}

// Update configuration based on original headcode
$updateData = [
    'tour_detail_endpoint' => 'https://api.kaikongservice.com/api/v1/tours/detail/{tour_id}',
    'requires_multi_step' => true,
    'url_parameters' => json_encode([
        'tour_detail_id_field' => 'tour_id',
        'period_id_field' => null
    ])
];

$result = DB::table('tb_api_providers')
    ->where('code', 'go365')
    ->update($updateData);

if ($result) {
    echo "GO365 provider updated successfully!\n";
    
    // Verify the update
    $updated = DB::table('tb_api_providers')->where('code', 'go365')->first();
    echo "\nUpdated Configuration:\n";
    echo "Tour Detail Endpoint: " . $updated->tour_detail_endpoint . "\n";
    echo "Requires Multi-Step: " . ($updated->requires_multi_step ? 'YES' : 'NO') . "\n";
    echo "URL Parameters: " . $updated->url_parameters . "\n";
} else {
    echo "Failed to update GO365 provider!\n";
    exit(1);
}

echo "\nDone!\n";