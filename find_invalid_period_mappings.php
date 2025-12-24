<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Finding Checkin Group Mappings ===\n\n";

$provider = DB::table('tb_api_providers')->where('code', 'checkingroup')->first();
if (!$provider) {
    echo "Provider not found!\n";
    exit;
}

echo "Provider ID: {$provider->id}\n\n";

// Get all period mappings
$mappings = DB::table('tb_api_field_mappings')
    ->where('api_provider_id', $provider->id)
    ->where('field_type', 'period')
    ->get();

echo "=== Period Mappings ({mappings->count()}) ===\n\n";

// Check which columns exist
$periodCols = DB::select("SHOW COLUMNS FROM tb_tour_period");
$validCols = array_map(fn($c) => $c->Field, $periodCols);

$invalidIds = [];

foreach ($mappings as $m) {
    $valid = in_array($m->local_field, $validCols);
    $mark = $valid ? '✓' : '✗';
    echo "{$mark} ID:{$m->id} | {$m->api_field} → {$m->local_field}\n";
    
    if (!$valid) {
        $invalidIds[] = $m->id;
    }
}

if (!empty($invalidIds)) {
    echo "\n=== FIX: Delete Invalid Mappings ===\n\n";
    echo "DELETE FROM tb_api_field_mappings WHERE id IN (" . implode(',', $invalidIds) . ");\n";
} else {
    echo "\n✓ All mappings valid!\n";
}
