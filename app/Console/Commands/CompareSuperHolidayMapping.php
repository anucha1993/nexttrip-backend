<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Backend\ApiProviderModel;

class CompareSuperHolidayMapping extends Command
{
    protected $signature = 'superholiday:compare-mapping';
    protected $description = 'Compare Super Holiday API hardcode field mappings with database UI mappings';

    public function handle()
    {
        $this->info('🔍 Analyzing Super Holiday API field mappings...');
        
        $superholiday = ApiProviderModel::where('code', 'superbholiday')->first();
        
        if (!$superholiday) {
            $this->error('❌ Super Holiday provider not found!');
            return 1;
        }
        
        $this->info("✅ Found Super Holiday provider: {$superholiday->name} (ID: {$superholiday->id})");
        
        // Hardcode mappings จาก ApiController.php
        $hardcodeTourMappings = [
            'api_id' => 'mainid',
            'code1' => 'maincode', 
            'name' => 'title',
            'country_id' => 'Country', // -> CountryModel lookup by country_name_th
            'airline_id' => 'aey', // -> extract code from "(code)" format -> TravelTypeModel lookup
            'image' => 'banner',
            'pdf_file' => 'pdf',
            'data_type' => '2', // static value
            'api_type' => 'superbholiday', // static value
            'group_id' => '3', // static value 
            'wholesale_id' => '22' // static value
        ];
        
        $hardcodePeriodMappings = [
            'period_code' => 'pid', // Note: uses period_code instead of period_api_id
            'start_date' => 'Date',
            'end_date' => 'ENDDate',
            'group_date' => 'Date', // -> transformed to date('mY',strtotime($call1['Date']))
            'day' => 'day',
            'night' => 'night',
            'group' => 'Size',
            'count' => 'AVBL',
            'price1' => 'Adult',
            'price2' => 'Single', 
            'price3' => 'Chd+B',
            'price4' => 'ChdNB',
            'status_period' => 'AVBL', // -> conditional: if AVBL > 0 then 1 else 3
            'status_display' => 'on', // static value
            'api_type' => 'superbholiday' // static value
        ];
        
        // Get current database mappings
        $dbMappings = $superholiday->fieldMappings()->get();
        $dbTourMappings = $dbMappings->where('field_type', 'tour')->pluck('api_field', 'local_field')->toArray();
        $dbPeriodMappings = $dbMappings->where('field_type', 'period')->pluck('api_field', 'local_field')->toArray();
        
        $this->info("\n📊 COMPARISON RESULTS:");
        $this->info("====================");
        
        // Compare tour fields
        $this->info("\n🏛️  TOUR FIELDS COMPARISON:");
        $tourMatches = 0;
        $tourTotal = count($hardcodeTourMappings);
        
        foreach ($hardcodeTourMappings as $localField => $apiField) {
            $dbField = $dbTourMappings[$localField] ?? 'NOT FOUND';
            $status = ($dbField === $apiField) ? '✅ MATCH' : '❌ MISMATCH';
            
            if ($dbField === $apiField) {
                $tourMatches++;
            }
            
            $this->line("  {$localField}: hardcode='{$apiField}' | database='{$dbField}' | {$status}");
        }
        
        // Compare period fields  
        $this->info("\n📅 PERIOD FIELDS COMPARISON:");
        $periodMatches = 0;
        $periodTotal = count($hardcodePeriodMappings);
        
        foreach ($hardcodePeriodMappings as $localField => $apiField) {
            $dbField = $dbPeriodMappings[$localField] ?? 'NOT FOUND';
            $status = ($dbField === $apiField) ? '✅ MATCH' : '❌ MISMATCH';
            
            if ($dbField === $apiField) {
                $periodMatches++;
            }
            
            $this->line("  {$localField}: hardcode='{$apiField}' | database='{$dbField}' | {$status}");
        }
        
        // Summary
        $totalMatches = $tourMatches + $periodMatches;
        $totalFields = $tourTotal + $periodTotal;
        $percentage = $totalFields > 0 ? round(($totalMatches / $totalFields) * 100, 1) : 0;
        
        $this->info("\n📈 SUMMARY:");
        $this->info("==========");
        $this->info("Tour Fields: {$tourMatches}/{$tourTotal} match");
        $this->info("Period Fields: {$periodMatches}/{$periodTotal} match");
        $this->info("Overall: {$totalMatches}/{$totalFields} ({$percentage}%) fields match");
        
        if ($percentage < 100) {
            $this->warn("\n⚠️  Database mappings need updates to match hardcode implementation!");
        } else {
            $this->info("\n🎉 Perfect match! Database mappings are synchronized with hardcode.");
        }
        
        // Special notes
        $this->info("\n📝 SPECIAL NOTES:");
        $this->info("- period_code field is used instead of period_api_id");
        $this->info("- country_id uses Country field with CountryModel lookup by country_name_th");
        $this->info("- airline_id extracts code from 'aey' field format like 'Name (CODE)'");
        $this->info("- status_period is conditional: if AVBL > 0 then 1 else 3");
        $this->info("- group_date transforms Date field to mY format");
        
        return 0;
    }
}