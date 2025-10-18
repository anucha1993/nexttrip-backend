<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Backend\ApiProviderModel;

class CompareZegoMapping extends Command
{
    protected $signature = 'zego:compare-mapping';
    protected $description = 'Compare Zego API hardcode vs database mappings';

    public function handle()
    {
        $this->info('🔍 เปรียบเทียบ Zego API Mapping ระหว่าง Hardcode vs Database');
        $this->info('===========================================================');

        // Hardcode mapping จาก zego_api() function
        $hardcodeMappings = [
            'tour' => [
                'api_id' => 'ProductID',
                'code1' => 'ProductCode', 
                'name' => 'ProductName',
                'description' => 'Highlight',
                'rating' => 'MaxHotelStars',
                'image' => 'URLImage',
                'pdf_file' => 'FilePDF',
                'country_id' => 'CountryName',
                'airline_id' => 'AirlineCode',
            ],
            'period' => [
                'period_api_id' => 'PeriodID',
                'start_date' => 'PeriodStartDate',
                'end_date' => 'PeriodEndDate',
                'price1' => 'Price',
                'price2' => 'Price_Single_Bed',
                'price3' => 'Price_Child',
                'price4' => 'Price_ChildNB',
                'day' => 'Days',
                'night' => 'Nights',
                'group' => 'GroupSize',
                'count' => 'Seat',
                'status_period_text' => 'PeriodStatus',
            ]
        ];

        $zego = ApiProviderModel::where('code', 'zego')->first();
        
        if (!$zego) {
            $this->error("❌ ไม่พบ Zego provider ในฐานข้อมูล");
            return;
        }
        
        $this->info("✅ พบ Zego Provider: {$zego->name} (ID: {$zego->id})");
        
        $dbMappings = $zego->fieldMappings()->get();
        
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
    }
}