<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Backend\ApiProviderModel;
use App\Models\Backend\ApiFieldMappingModel;

return new class extends Migration
{
    public function up()
    {
        // แก้ไข TTN Japan API Field Mappings ให้ตรงกับ hardcode
        $ttnJapan = ApiProviderModel::where('code', 'ttn_japan')->first();
        
        if ($ttnJapan) {
            // ลบ mapping เดิมทั้งหมด
            ApiFieldMappingModel::where('api_provider_id', $ttnJapan->id)->delete();
            
            // เพิ่ม tour field mappings ใหม่ตาม hardcode
            $tourMappings = [
                ['field_type' => 'tour', 'local_field' => 'api_id', 'api_field' => 'P_ID', 'data_type' => 'string', 'is_required' => true],
                ['field_type' => 'tour', 'local_field' => 'code1', 'api_field' => 'P_CODE', 'data_type' => 'string'],
                ['field_type' => 'tour', 'local_field' => 'name', 'api_field' => 'P_NAME', 'data_type' => 'string', 'is_required' => true],
                ['field_type' => 'tour', 'local_field' => 'description', 'api_field' => 'P_HIGHLIGHT', 'data_type' => 'string'],
                ['field_type' => 'tour', 'local_field' => 'rating', 'api_field' => 'P_HOTEL_STAR', 'data_type' => 'string'],
                ['field_type' => 'tour', 'local_field' => 'image', 'api_field' => 'BANNER', 'data_type' => 'string'],
                ['field_type' => 'tour', 'local_field' => 'pdf_file', 'api_field' => 'PDF', 'data_type' => 'string'],
                ['field_type' => 'tour', 'local_field' => 'country_id', 'api_field' => 'JAPAN', 'data_type' => 'json', 'transformation_rules' => [['type' => 'hardcoded_country', 'value' => 'JAPAN']]],
                ['field_type' => 'tour', 'local_field' => 'airline_id', 'api_field' => 'P_AIRLINE', 'data_type' => 'string', 'transformation_rules' => [['type' => 'airline_lookup', 'search_field' => 'code', 'target_field' => 'id']]],
                ['field_type' => 'tour', 'local_field' => 'day', 'api_field' => 'P_DAY', 'data_type' => 'string'],
                ['field_type' => 'tour', 'local_field' => 'night', 'api_field' => 'P_NIGHT', 'data_type' => 'string'],
            ];
            
            // เพิ่ม period field mappings ใหม่ตาม hardcode
            $periodMappings = [
                ['field_type' => 'period', 'local_field' => 'period_api_id', 'api_field' => 'P_ID', 'data_type' => 'string', 'is_required' => true, 'source' => 'period_api_call'],
                ['field_type' => 'period', 'local_field' => 'start_date', 'api_field' => 'P_DUE_START', 'data_type' => 'date', 'source' => 'period_api_call'],
                ['field_type' => 'period', 'local_field' => 'end_date', 'api_field' => 'P_DUE_END', 'data_type' => 'date', 'source' => 'period_api_call'],
                ['field_type' => 'period', 'local_field' => 'group_date', 'api_field' => 'P_DUE_START', 'data_type' => 'string', 'transformation_rules' => [['type' => 'date_format', 'format' => 'mY']], 'source' => 'period_api_call'],
                ['field_type' => 'period', 'local_field' => 'price1', 'api_field' => 'P_ADULT_PRICE', 'data_type' => 'decimal', 'source' => 'price_array'],
                ['field_type' => 'period', 'local_field' => 'price2', 'api_field' => 'P_SINGLE_PRICE', 'data_type' => 'decimal', 'source' => 'price_array'],
                ['field_type' => 'period', 'local_field' => 'day', 'api_field' => 'P_DAY', 'data_type' => 'string', 'source' => 'parent_tour'],
                ['field_type' => 'period', 'local_field' => 'night', 'api_field' => 'P_NIGHT', 'data_type' => 'string', 'source' => 'parent_tour'],
                ['field_type' => 'period', 'local_field' => 'group', 'api_field' => 'P_VOLUME', 'data_type' => 'string', 'source' => 'price_array'],
                ['field_type' => 'period', 'local_field' => 'count', 'api_field' => 'P_AVAILABLE', 'data_type' => 'string', 'source' => 'price_array'],
                ['field_type' => 'period', 'local_field' => 'status_period', 'api_field' => 'P_AVAILABLE', 'data_type' => 'integer', 'transformation_rules' => [['type' => 'status_mapping', 'values' => ['Open' => 1, 'ChangePrice' => 3]]], 'source' => 'price_array'],
            ];
            
            // รวม mappings ทั้งหมด
            $allMappings = array_merge($tourMappings, $periodMappings);
            
            // เพิ่ม api_provider_id และบันทึกลงฐานข้อมูล
            foreach ($allMappings as $mapping) {
                ApiFieldMappingModel::create(array_merge($mapping, ['api_provider_id' => $ttnJapan->id]));
            }
            
            echo "✅ อัพเดท TTN Japan API Field Mappings สำเร็จ - " . count($allMappings) . " fields\n";
        } else {
            echo "❌ ไม่พบ TTN Japan API Provider\n";
        }
    }

    public function down()
    {
        // Rollback - ลบ mappings ที่เพิ่มใหม่
        $ttnJapan = ApiProviderModel::where('code', 'ttn_japan')->first();
        
        if ($ttnJapan) {
            ApiFieldMappingModel::where('api_provider_id', $ttnJapan->id)->delete();
            echo "🔄 Rollback TTN Japan API Field Mappings สำเร็จ\n";
        }
    }
};