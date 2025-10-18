<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('check:field-mappings', function () {
    $this->info('=== ตรวจสอบ Field Mappings ทุก API Provider ===');
    
    $providers = \App\Models\Backend\ApiProviderModel::with('fieldMappings')->get();
    
    foreach ($providers as $provider) {
        $this->line("=== {$provider->name} ({$provider->code}) ===");
        
        $tourMappings = $provider->fieldMappings->where('field_type', 'tour');
        $periodMappings = $provider->fieldMappings->where('field_type', 'period');
        
        $this->line("  📊 Tour mappings: {$tourMappings->count()}");
        $this->line("  📅 Period mappings: {$periodMappings->count()}");
        
        if ($tourMappings->count() > 0) {
            $this->line("  🔗 Tour Fields:");
            foreach ($tourMappings as $mapping) {
                $default = $mapping->default_value ? " (default: {$mapping->default_value})" : "";
                $this->line("    - {$mapping->local_field} ← '{$mapping->api_field}' [{$mapping->data_type}]{$default}");
            }
        }
        
        if ($periodMappings->count() > 0) {
            $this->line("  📅 Period Fields:");
            foreach ($periodMappings as $mapping) {
                $default = $mapping->default_value ? " (default: {$mapping->default_value})" : "";
                $this->line("    - {$mapping->local_field} ← '{$mapping->api_field}' [{$mapping->data_type}]{$default}");
            }
        }
        
        if ($tourMappings->count() == 0 && $periodMappings->count() == 0) {
            $this->error("  ❌ ไม่มี field mappings เลย!");
        }
        
        $this->line("");
    }
    
    // ตรวจสอบ iTravel เฉพาะ
    $itravel = $providers->where('code', 'itravel')->first();
    if ($itravel) {
        $this->info("=== iTravel Detailed Check ===");
        $this->line("Total mappings: {$itravel->fieldMappings->count()}");
        
        foreach ($itravel->fieldMappings as $mapping) {
            $static = "";
            if ($mapping->transformation_rules && isset($mapping->transformation_rules['static_value'])) {
                $static = " (STATIC: {$mapping->transformation_rules['static_value']})";
            }
            $this->line("- [{$mapping->field_type}] {$mapping->local_field} ← '{$mapping->api_field}'{$static}");
        }
    }
    
})->purpose('ตรวจสอบ Field Mappings ทุก API Provider');