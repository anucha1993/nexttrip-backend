<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Inspiring;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('check:mappings', function () {
    $this->info('=== ตรวจสอบ Field Mappings ทุก API Provider ===');
    
    $results = DB::select("
        SELECT 
            p.name, 
            p.code, 
            COUNT(m.id) as total_mappings,
            SUM(CASE WHEN m.field_type = 'tour' THEN 1 ELSE 0 END) as tour_mappings,
            SUM(CASE WHEN m.field_type = 'period' THEN 1 ELSE 0 END) as period_mappings
        FROM tb_api_providers p 
        LEFT JOIN tb_api_field_mappings m ON p.id = m.api_provider_id 
        GROUP BY p.id 
        ORDER BY p.name
    ");
    
    foreach ($results as $row) {
        $this->line("=== {$row->name} ({$row->code}) ===");
        $this->line("  Total mappings: {$row->total_mappings}");
        $this->line("  Tour mappings: {$row->tour_mappings}");
        $this->line("  Period mappings: {$row->period_mappings}");
        $this->line("");
    }
    
    // ตรวจสอบ iTravel โดยเฉพาะ
    $this->info("=== iTravel Details ===");
    $itravelMappings = DB::select("
        SELECT 
            m.field_type,
            m.local_field,
            m.api_field,
            m.default_value,
            m.data_type,
            m.is_required
        FROM tb_api_field_mappings m
        JOIN tb_api_providers p ON p.id = m.api_provider_id
        WHERE p.code = 'itravel'
        ORDER BY m.field_type, m.local_field
    ");
    
    if (empty($itravelMappings)) {
        $this->error("❌ iTravel ไม่มี field mappings เลย!");
    } else {
        foreach ($itravelMappings as $mapping) {
            $required = $mapping->is_required ? '(required)' : '';
            $this->line("  {$mapping->field_type}: {$mapping->local_field} -> '{$mapping->api_field}' [{$mapping->data_type}] {$required}");
            if ($mapping->default_value) {
                $this->line("    Default: '{$mapping->default_value}'");
            }
        }
    }
    
})->purpose('ตรวจสอบ API Field Mappings');