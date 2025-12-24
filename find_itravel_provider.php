<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Finding iTravel Provider ===\n\n";

// Search for provider
$providers = DB::select("SELECT id, name, code FROM tb_api_providers WHERE name LIKE '%travel%' OR code LIKE '%travel%'");

if (empty($providers)) {
    echo "❌ No iTravel provider found!\n";
    exit(1);
}

echo "Found providers:\n";
foreach ($providers as $p) {
    echo "  ID {$p->id}: {$p->name} (code: {$p->code})\n";
}

echo "\nUsing: {$providers[0]->name} (ID: {$providers[0]->id}, code: {$providers[0]->code})\n\n";

$providerId = $providers[0]->id;

// Check all tour mappings
echo "=== Tour Mappings (field_type='tour') ===\n";
$tourMappings = DB::select("SELECT id, field_type, local_field, api_field, data_type FROM tb_api_field_mappings WHERE api_provider_id = ? AND field_type = 'tour' ORDER BY local_field", [$providerId]);

if (empty($tourMappings)) {
    echo "  (none found)\n";
} else {
    foreach ($tourMappings as $m) {
        echo "  ID {$m->id}: {$m->local_field} ← {$m->api_field} ({$m->data_type})\n";
    }
}

// Check if api_id exists
$apiIdMapping = DB::select("SELECT * FROM tb_api_field_mappings WHERE api_provider_id = ? AND field_type = 'tour' AND local_field = 'api_id' LIMIT 1", [$providerId])[0] ?? null;

echo "\n=== api_id Mapping ===\n";
if ($apiIdMapping) {
    echo "✓ Found: {$apiIdMapping->local_field} ← {$apiIdMapping->api_field}\n";
} else {
    echo "❌ NOT FOUND - Adding now...\n";
    
    DB::insert("INSERT INTO tb_api_field_mappings (api_provider_id, field_type, local_field, api_field, data_type, is_required, created_at, updated_at) VALUES (?, 'tour', 'api_id', 'code', 'string', 1, NOW(), NOW())", [$providerId]);
    
    echo "✓ Added: api_id ← code\n";
}

echo "\nDone!\n";
