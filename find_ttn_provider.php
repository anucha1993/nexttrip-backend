<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;

// Initialize Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Finding TTN Japan Provider ===\n\n";

try {
    // Check all providers
    $providers = DB::table('api_providers')->get();
    
    echo "📋 All API Providers:\n";
    foreach ($providers as $provider) {
        echo "   ID: {$provider->id} | Code: {$provider->code} | Name: {$provider->name}\n";
    }
    
    // Try different codes
    echo "\n🔍 Looking for TTN related providers:\n";
    $ttnProviders = DB::table('api_providers')
                     ->where('code', 'like', '%ttn%')
                     ->orWhere('name', 'like', '%TTN%')
                     ->orWhere('name', 'like', '%Japan%')
                     ->get();
                     
    foreach ($ttnProviders as $provider) {
        echo "   Found: {$provider->id} | {$provider->code} | {$provider->name}\n";
        
        // Check field mappings for this provider
        $mappings = DB::table('api_field_mapping')
                     ->where('api_provider_id', $provider->id)
                     ->whereIn('local_field', ['day', 'night'])
                     ->get();
                     
        if ($mappings->count() > 0) {
            echo "     Problem mappings:\n";
            foreach ($mappings as $mapping) {
                echo "       {$mapping->field_type}: {$mapping->local_field} ← {$mapping->api_field}\n";
            }
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}