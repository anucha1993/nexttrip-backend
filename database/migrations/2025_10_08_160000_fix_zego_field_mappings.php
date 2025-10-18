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
        // แก้ไข Zego API Field Mappings ให้ตรงกับ hardcode
        $zego = ApiProviderModel::where('code', 'zego')->first();
        
        if ($zego) {
            // ลบ mapping เดิมทั้งหมด
            ApiFieldMappingModel::where('api_provider_id', $zego->id)->delete();
            
            // เพิ่ม tour field mappings ใหม่ตาม hardcode
            $tourMappings = [
                ['field_type' => 'tour', 'local_field' => 'api_id', 'api_field' => 'ProductID', 'data_type' => 'string', 'is_required' => true],
                ['field_type' => 'tour', 'local_field' => 'code1', 'api_field' => 'ProductCode', 'data_type' => 'string'],
                ['field_type' => 'tour', 'local_field' => 'name', 'api_field' => 'ProductName', 'data_type' => 'string', 'is_required' => true],
                ['field_type' => 'tour', 'local_field' => 'description', 'api_field' => 'Highlight', 'data_type' => 'string', 'transformation_rules' => [['type' => 'string_replace', 'search' => "\n", 'replace' => '']]],
                ['field_type' => 'tour', 'local_field' => 'rating', 'api_field' => 'MaxHotelStars', 'data_type' => 'string'],
                ['field_type' => 'tour', 'local_field' => 'image', 'api_field' => 'URLImage', 'data_type' => 'string'],
                ['field_type' => 'tour', 'local_field' => 'pdf_file', 'api_field' => 'FilePDF', 'data_type' => 'string'],
                ['field_type' => 'tour', 'local_field' => 'country_id', 'api_field' => 'CountryName', 'data_type' => 'json', 'transformation_rules' => [['type' => 'country_lookup', 'search_field' => 'country_name_en', 'target_field' => 'id']]],
                ['field_type' => 'tour', 'local_field' => 'airline_id', 'api_field' => 'AirlineCode', 'data_type' => 'string', 'transformation_rules' => [['type' => 'airline_lookup', 'search_field' => 'code', 'target_field' => 'id']]],
            ];
            
            // เพิ่ม period field mappings ใหม่ตาม hardcode
            $periodMappings = [
                ['field_type' => 'period', 'local_field' => 'period_api_id', 'api_field' => 'PeriodID', 'data_type' => 'string', 'is_required' => true],
                ['field_type' => 'period', 'local_field' => 'start_date', 'api_field' => 'PeriodStartDate', 'data_type' => 'date'],
                ['field_type' => 'period', 'local_field' => 'end_date', 'api_field' => 'PeriodEndDate', 'data_type' => 'date'],
                ['field_type' => 'period', 'local_field' => 'price1', 'api_field' => 'Price', 'data_type' => 'decimal', 'transformation_rules' => [['type' => 'special_price_calculation', 'start_field' => 'Price', 'end_field' => 'Price_End']]],
                ['field_type' => 'period', 'local_field' => 'price2', 'api_field' => 'Price_Single_Bed', 'data_type' => 'decimal', 'transformation_rules' => [['type' => 'special_price_calculation', 'start_field' => 'Price_Single_Bed', 'end_field' => 'Price_Single_Bed_End']]],
                ['field_type' => 'period', 'local_field' => 'price3', 'api_field' => 'Price_Child', 'data_type' => 'decimal', 'transformation_rules' => [['type' => 'special_price_calculation', 'start_field' => 'Price_Child', 'end_field' => 'Price_Child_End']]],
                ['field_type' => 'period', 'local_field' => 'price4', 'api_field' => 'Price_ChildNB', 'data_type' => 'decimal', 'transformation_rules' => [['type' => 'special_price_calculation', 'start_field' => 'Price_ChildNB', 'end_field' => 'Price_ChildNB_End']]],
                ['field_type' => 'period', 'local_field' => 'special_price1', 'api_field' => 'Price_End', 'data_type' => 'decimal', 'transformation_rules' => [['type' => 'discount_calculation', 'base_field' => 'Price']]],
                ['field_type' => 'period', 'local_field' => 'special_price2', 'api_field' => 'Price_Single_Bed_End', 'data_type' => 'decimal', 'transformation_rules' => [['type' => 'discount_calculation', 'base_field' => 'Price_Single_Bed']]],
                ['field_type' => 'period', 'local_field' => 'special_price3', 'api_field' => 'Price_Child_End', 'data_type' => 'decimal', 'transformation_rules' => [['type' => 'discount_calculation', 'base_field' => 'Price_Child']]],
                ['field_type' => 'period', 'local_field' => 'special_price4', 'api_field' => 'Price_ChildNB_End', 'data_type' => 'decimal', 'transformation_rules' => [['type' => 'discount_calculation', 'base_field' => 'Price_ChildNB']]],
                ['field_type' => 'period', 'local_field' => 'day', 'api_field' => 'Days', 'data_type' => 'string', 'source' => 'parent_tour'],
                ['field_type' => 'period', 'local_field' => 'night', 'api_field' => 'Nights', 'data_type' => 'string', 'source' => 'parent_tour'],
                ['field_type' => 'period', 'local_field' => 'group', 'api_field' => 'GroupSize', 'data_type' => 'string'],
                ['field_type' => 'period', 'local_field' => 'count', 'api_field' => 'Seat', 'data_type' => 'string'],
                ['field_type' => 'period', 'local_field' => 'group_date', 'api_field' => 'PeriodStartDate', 'data_type' => 'string', 'transformation_rules' => [['type' => 'date_format', 'format' => 'mY']]],
                ['field_type' => 'period', 'local_field' => 'status_period', 'api_field' => 'PeriodStatus', 'data_type' => 'integer', 'transformation_rules' => [['type' => 'status_mapping', 'values' => ['Book' => 1, 'Waitlist' => 2, 'Close Group' => 3, 'Soldout' => 3]]]],
            ];
            
            // รวม mappings ทั้งหมด
            $allMappings = array_merge($tourMappings, $periodMappings);
            
            // เพิ่ม api_provider_id และบันทึกลงฐานข้อมูล
            foreach ($allMappings as $mapping) {
                ApiFieldMappingModel::create(array_merge($mapping, ['api_provider_id' => $zego->id]));
            }
            
            echo "✅ อัพเดท Zego API Field Mappings สำเร็จ - " . count($allMappings) . " fields\n";
        } else {
            echo "❌ ไม่พบ Zego API Provider\n";
        }
    }

    public function down()
    {
        // Rollback - ลบ mappings ที่เพิ่มใหม่
        $zego = ApiProviderModel::where('code', 'zego')->first();
        
        if ($zego) {
            ApiFieldMappingModel::where('api_provider_id', $zego->id)->delete();
            echo "🔄 Rollback Zego API Field Mappings สำเร็จ\n";
        }
    }
};