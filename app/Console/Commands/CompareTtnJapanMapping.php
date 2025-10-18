<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Backend\ApiProviderModel;

class CompareTtnJapanMapping extends Command
{
    protected $signature = 'ttn:compare-mapping';
    protected $description = 'Compare TTN Japan API hardcode vs database mappings';

    public function handle()
    {
        $this->info('🔍 เปรียบเทียบ TTN Japan API Mapping ระหว่าง Hardcode vs Database');
        $this->info('=================================================================');

        // Hardcode mapping จาก ttn_api_japan() function
        $hardcodeMappings = [
            'tour' => [
                'api_id' => 'P_ID',
                'code1' => 'P_CODE', 
                'name' => 'P_NAME',
                'description' => 'P_HIGHLIGHT',
                'rating' => 'P_HOTEL_STAR',
                'image' => 'BANNER',
                'pdf_file' => 'PDF',
                'country_id' => 'JAPAN', // hardcoded to Japan search
                'airline_id' => 'P_AIRLINE',
                'day' => 'P_DAY',
                'night' => 'P_NIGHT',
            ],
            'period' => [
                // TTN Japan has complex period structure with separate API call
                'period_api_id' => 'P_ID', // from call3 (period data)
                'start_date' => 'P_DUE_START', // from call3 
                'end_date' => 'P_DUE_END', // from call3
                'group_date' => 'P_DUE_START', // transformed to mY format
                'price1' => 'P_ADULT_PRICE', // from pe (price array)
                'price2' => 'P_SINGLE_PRICE', // from pe (price array)
                'day' => 'P_DAY', // from call2 (main tour)
                'night' => 'P_NIGHT', // from call2 (main tour) 
                'group' => 'P_VOLUME', // from pe (price array)
                'count' => 'P_AVAILABLE', // from pe (price array)
                'status_period' => 'P_AVAILABLE', // Open=1, ChangePrice=3
            ]
        ];

        $ttnJapan = ApiProviderModel::where('code', 'ttn_japan')->first();
        
        if (!$ttnJapan) {
            $this->error("❌ ไม่พบ TTN Japan provider ในฐานข้อมูล");
            return;
        }
        
        $this->info("✅ พบ TTN Japan Provider: {$ttnJapan->name} (ID: {$ttnJapan->id})");
        
        $dbMappings = $ttnJapan->fieldMappings()->get();
        
        $this->info("📊 จำนวน Field Mappings ในฐานข้อมูล: " . $dbMappings->count());
        $this->info("📊 จำนวน Hardcode Tour Fields: " . count($hardcodeMappings['tour']));
        $this->info("📊 จำนวน Hardcode Period Fields: " . count($hardcodeMappings['period']));
        
        // แยกตาม field_type
        $dbTourFields = $dbMappings->where('field_type', 'tour')->keyBy('local_field');
        $dbPeriodFields = $dbMappings->where('field_type', 'period')->keyBy('local_field');
        
        $this->info("\n🔍 TOUR FIELDS COMPARISON:");
        
        $headers = ['Local Field', 'Hardcode API', 'Database API', 'Status'];
        $rows = [];
        
        foreach ($hardcodeMappings['tour'] as $localField => $apiField) {
            $dbField = $dbTourFields->get($localField);
            $dbApiField = $dbField ? $dbField->api_field : 'NOT FOUND';
            
            $status = '❌ MISSING';
            if ($dbField) {
                $status = ($dbField->api_field === $apiField) ? '✅ MATCH' : '⚠️ DIFF';
            }
            
            $rows[] = [$localField, $apiField, $dbApiField, $status];
        }
        
        $this->table($headers, $rows);
        
        $this->info("\n🔍 PERIOD FIELDS COMPARISON:");
        
        $rows = [];
        foreach ($hardcodeMappings['period'] as $localField => $apiField) {
            $dbField = $dbPeriodFields->get($localField);
            $dbApiField = $dbField ? $dbField->api_field : 'NOT FOUND';
            
            $status = '❌ MISSING';
            if ($dbField) {
                $status = ($dbField->api_field === $apiField) ? '✅ MATCH' : '⚠️ DIFF';
            }
            
            $rows[] = [$localField, $apiField, $dbApiField, $status];
        }
        
        $this->table($headers, $rows);
        
        // หาฟิลด์ที่หายไป
        $missingTour = collect($hardcodeMappings['tour'])->reject(function ($apiField, $localField) use ($dbTourFields) {
            return $dbTourFields->has($localField);
        });
        
        $missingPeriod = collect($hardcodeMappings['period'])->reject(function ($apiField, $localField) use ($dbPeriodFields) {
            return $dbPeriodFields->has($localField);
        });
        
        if ($missingTour->count() > 0 || $missingPeriod->count() > 0) {
            $this->error("\n❌ MISSING FIELDS ที่ต้องเพิ่มใน Database:");
            
            if ($missingTour->count() > 0) {
                $this->error("Tour Fields:");
                foreach ($missingTour as $localField => $apiField) {
                    $this->error("  - {$localField} => {$apiField}");
                }
            }
            
            if ($missingPeriod->count() > 0) {
                $this->error("Period Fields:");
                foreach ($missingPeriod as $localField => $apiField) {
                    $this->error("  - {$localField} => {$apiField}");
                }
            }
        }
        
        $this->info("\n✅ การเปรียบเทียบเสร็จสิ้น!");
        $this->warn("\n⚠️ Note: Period fields อาจต้องตรวจสอบ hardcode structure ใหม่");
    }
}