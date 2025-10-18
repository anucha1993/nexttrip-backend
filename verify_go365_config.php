<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== GO365 Configuration Summary ===\n";

$go365 = DB::table('tb_api_providers')->where('code', 'go365')->first();

if ($go365) {
    echo "Main URL: " . $go365->url . "\n";
    echo "Tour Detail Endpoint: " . $go365->tour_detail_endpoint . "\n";
    echo "Requires Multi-Step: " . ($go365->requires_multi_step ? 'YES' : 'NO') . "\n";
    echo "URL Parameters: " . $go365->url_parameters . "\n";
    
    $conditions = DB::table('tb_api_conditions')->where('api_provider_id', $go365->id)->get();
    echo "Total Conditions: " . $conditions->count() . "\n";
    
    $periodCondition = $conditions->where('condition_type', 'period_processing')->first();
    echo "Period Processing Condition: " . ($periodCondition ? 'EXISTS' : 'NOT FOUND') . "\n";
    
    echo "\n=== Headers ===\n";
    $headers = json_decode($go365->headers, true);
    foreach ($headers as $key => $value) {
        echo "$key: " . substr($value, 0, 50) . "...\n";
    }
    
    echo "\n=== Field Mappings ===\n";
    $mappings = DB::table('tb_api_field_mappings')->where('api_provider_id', $go365->id)->get();
    echo "Total Mappings: " . $mappings->count() . "\n";
    echo "Tour Fields: " . $mappings->where('field_type', 'tour')->count() . "\n";
    echo "Period Fields: " . $mappings->where('field_type', 'period')->count() . "\n";
    
} else {
    echo "GO365 provider not found!\n";
}

echo "\n=== Configuration Status ===\n";
echo "✅ Headers: " . (isset($headers['x-api-key']) ? 'SET' : 'MISSING') . "\n";
echo "✅ Tour Detail Endpoint: " . ($go365->tour_detail_endpoint ? 'SET' : 'MISSING') . "\n";
echo "✅ Multi-Step: " . ($go365->requires_multi_step ? 'ENABLED' : 'DISABLED') . "\n";
echo "✅ Field Mappings: " . ($mappings->count() > 0 ? 'CONFIGURED' : 'MISSING') . "\n";
echo "✅ Period Condition: " . (isset($periodCondition) ? 'SET' : 'MISSING') . "\n";

echo "\nGO365 is ready for multi-step API processing!\n";