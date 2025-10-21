<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;

// Initialize Laravel  
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Complete TTN Japan Analysis ===\n\n";

try {
    // Find all providers and their mappings
    echo "📋 All API Providers and their mappings:\n";
    $providers = DB::table('api_providers')->get();
    
    foreach ($providers as $provider) {
        echo "\n🔧 Provider: {$provider->id} | {$provider->code} | {$provider->name}\n";
        
        // Check field mappings
        $mappings = DB::table('tb_api_field_mappings')
                     ->where('api_provider_id', $provider->id)
                     ->get();
                     
        if ($mappings->count() > 0) {
            echo "   Field mappings ({$mappings->count()}):\n";
            foreach ($mappings as $mapping) {
                echo "     {$mapping->field_type}: {$mapping->local_field} ← {$mapping->api_field}\n";
            }
            
            // Check for day/night specifically
            $dayNightMappings = $mappings->whereIn('local_field', ['day', 'night']);
            if ($dayNightMappings->count() > 0) {
                echo "   ⚠️ Has day/night mappings!\n";
            }
        } else {
            echo "   No field mappings\n";
        }
    }
    
    // Also check if there are any mappings without proper provider linkage
    echo "\n🔍 Checking for orphaned mappings:\n";
    $allMappings = DB::table('tb_api_field_mappings')->get();
    
    $groupedMappings = $allMappings->groupBy('api_provider_id');
    foreach ($groupedMappings as $providerId => $mappings) {
        $provider = DB::table('api_providers')->where('id', $providerId)->first();
        $providerName = $provider ? $provider->name : 'Unknown Provider';
        
        echo "   Provider ID {$providerId} ({$providerName}): {$mappings->count()} mappings\n";
        
        $problemMappings = $mappings->whereIn('local_field', ['day', 'night']);
        if ($problemMappings->count() > 0) {
            echo "     ❌ Has {$problemMappings->count()} day/night mappings that could cause issues:\n";
            foreach ($problemMappings as $mapping) {
                echo "       ID {$mapping->id}: {$mapping->field_type} - {$mapping->local_field} ← {$mapping->api_field}\n";
            }
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}