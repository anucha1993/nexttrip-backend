<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Backend\ApiProviderModel;
use App\Models\Backend\ApiFieldMappingModel;

class FixGo365MappingsCommand extends Command
{
    protected $signature = 'go365:fix-mappings';
    protected $description = 'แก้ไข GO365 field mappings ให้ตรงกับ hardcode';

    public function handle()
    {
        $this->info('=== แก้ไข GO365 Field Mappings ===');
        
        $go365 = ApiProviderModel::where('code', 'go365')->first();
        
        if (!$go365) {
            $this->error('ไม่พบ GO365 Provider');
            return 1;
        }
        
        // ลบ mappings เดิม
        $this->info('ลบ field mappings เดิม...');
        $go365->fieldMappings()->delete();
        
        // สร้าง mappings ใหม่ตาม hardcode
        $this->createCorrectMappings($go365);
        
        // ตรวจสอบผลลัพธ์
        $this->verifyMappings($go365);
        
        return 0;
    }
    
    private function createCorrectMappings($provider)
    {
        $this->info('สร้าง field mappings ใหม่...');
        
        // Tour Fields ตาม hardcode
        $tourMappings = [
            ['local_field' => 'api_id', 'api_field' => 'tour_id', 'data_type' => 'integer'],
            ['local_field' => 'code1', 'api_field' => 'tour_code', 'data_type' => 'string'],
            ['local_field' => 'name', 'api_field' => 'tour_name', 'data_type' => 'string'],
            ['local_field' => 'description', 'api_field' => 'tour_description', 'data_type' => 'string'],
            ['local_field' => 'image', 'api_field' => 'tour_cover_image', 'data_type' => 'url'],
            ['local_field' => 'country_id', 'api_field' => 'tour_country', 'data_type' => 'array', 'transformation_rule' => 'country_code_lookup'],
            ['local_field' => 'airline_id', 'api_field' => 'tour_airline.airline_iata', 'data_type' => 'string', 'transformation_rule' => 'airline_lookup'],
            ['local_field' => 'pdf_file', 'api_field' => 'tour_file.file_pdf', 'data_type' => 'url'],
            ['local_field' => 'wholesale_id', 'api_field' => 'static:41', 'data_type' => 'integer'],
            ['local_field' => 'group_id', 'api_field' => 'static:3', 'data_type' => 'integer'],
            ['local_field' => 'data_type', 'api_field' => 'static:2', 'data_type' => 'integer'],
            ['local_field' => 'api_type', 'api_field' => 'static:go365', 'data_type' => 'string'],
            ['local_field' => 'image_check_change', 'api_field' => 'static:2', 'data_type' => 'integer'],
        ];
        
        // Period Fields ตาม hardcode  
        $periodMappings = [
            ['local_field' => 'period_api_id', 'api_field' => 'period_id', 'data_type' => 'integer'],
            ['local_field' => 'start_date', 'api_field' => 'period_date', 'data_type' => 'date'],
            ['local_field' => 'end_date', 'api_field' => 'period_back', 'data_type' => 'date'],
            ['local_field' => 'day', 'api_field' => 'tour_num_day', 'data_type' => 'integer'],
            ['local_field' => 'night', 'api_field' => 'tour_num_night', 'data_type' => 'integer'],
            ['local_field' => 'price1', 'api_field' => 'period_rate_adult_twn', 'data_type' => 'decimal'],
            ['local_field' => 'price2', 'api_field' => 'period_rate_adult_sgl', 'data_type' => 'decimal', 'transformation_rule' => 'sgl_minus_twn'],
            ['local_field' => 'price3', 'api_field' => 'period_rate_adult_twn', 'data_type' => 'decimal'],
            ['local_field' => 'price4', 'api_field' => 'period_rate_adult_twn', 'data_type' => 'decimal'],
            ['local_field' => 'group', 'api_field' => 'period_quota', 'data_type' => 'integer'],
            ['local_field' => 'count', 'api_field' => 'period_available', 'data_type' => 'integer'],
            ['local_field' => 'status_period', 'api_field' => 'period_visible', 'data_type' => 'integer', 'transformation_rule' => 'visible_to_status'],
            ['local_field' => 'status_display', 'api_field' => 'static:on', 'data_type' => 'string'],
            ['local_field' => 'api_type', 'api_field' => 'static:go365', 'data_type' => 'string'],
            ['local_field' => 'group_date', 'api_field' => 'period_date', 'data_type' => 'date', 'transformation_rule' => 'date_to_group_format'],
            ['local_field' => 'tour_id', 'api_field' => 'parent:tour_id', 'data_type' => 'integer'],
        ];
        
        $created = 0;
        
        // สร้าง tour mappings
        foreach ($tourMappings as $mapping) {
            ApiFieldMappingModel::create(array_merge($mapping, [
                'api_provider_id' => $provider->id,
                'field_type' => 'tour'
            ]));
            $created++;
        }
        
        // สร้าง period mappings
        foreach ($periodMappings as $mapping) {
            ApiFieldMappingModel::create(array_merge($mapping, [
                'api_provider_id' => $provider->id,
                'field_type' => 'period'
            ]));
            $created++;
        }
        
        $this->info("✅ สร้าง field mappings สำเร็จ: {$created} mappings");
    }
    
    private function verifyMappings($provider)
    {
        $this->info("\n--- ตรวจสอบ Field Mappings ที่สร้างใหม่ ---");
        
        $tourMappings = $provider->fieldMappings()->where('field_type', 'tour')->get();
        $periodMappings = $provider->fieldMappings()->where('field_type', 'period')->get();
        
        $this->info("Tour Fields ({$tourMappings->count()}):");
        foreach ($tourMappings as $mapping) {
            $transformation = $mapping->transformation_rule ? " [{$mapping->transformation_rule}]" : "";
            $this->info("  {$mapping->local_field} => {$mapping->api_field} ({$mapping->data_type}){$transformation}");
        }
        
        $this->info("\nPeriod Fields ({$periodMappings->count()}):");
        foreach ($periodMappings as $mapping) {
            $transformation = $mapping->transformation_rule ? " [{$mapping->transformation_rule}]" : "";
            $this->info("  {$mapping->local_field} => {$mapping->api_field} ({$mapping->data_type}){$transformation}");
        }
        
        // อัปเดต provider configuration
        $provider->update([
            'base_url' => 'https://api.kaikongservice.com',
            'api_endpoint' => '/api/v1/tours/search',
            'headers' => json_encode([
                'Content-Type' => 'application/json',
                'x-api-key' => '${GO365_API_KEY}'
            ]),
            'additional_config' => json_encode([
                'detail_endpoint' => '/api/v1/tours/detail/{id}',
                'wholesale_id' => 41,
                'requires_multi_step' => true,
                'period_endpoint' => '/api/v1/tours/detail/{tour_id}',
                'period_data_path' => 'data.0.tour_period'
            ])
        ]);
        
        $this->info("\n✅ อัปเดต provider configuration สำเร็จ");
    }
}