<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiConditionModel extends Model
{
    use HasFactory;
    
    protected $table = 'tb_api_conditions';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'api_provider_id', 'condition_type', 'field_name', 
        'operator', 'value', 'action_type', 'action_rules',
        'condition_rules', 'priority', 'is_active'
    ];
    
    protected $casts = [
        'condition_rules' => 'array',
        'action_rules' => 'array',
        'is_active' => 'boolean',
    ];
    
    /**
     * Get available condition types from existing ApiController patterns
     */
    public static function getConditionTypes()
    {
        return [
            'country_mapping' => 'Country Mapping (CountryName -> country_id)',
            'airline_mapping' => 'Airline Mapping (AirlineCode -> airline_id)', 
            'image_processing' => 'Image Processing (URLImage -> download & resize)',
            'data_update_check' => 'Data Update Check (*_check_change fields)',
            'field_transformation' => 'Field Transformation (format, replace, etc.)',
            'price_calculation' => 'Price Calculation',
            'data_validation' => 'Data Validation (skip if conditions met)',
            'text_processing' => 'Text Processing (remove newlines, etc.)'
        ];
    }
    
    /**
     * Get available operators
     */
    public static function getOperators()
    {
        return [
            '=' => 'เท่ากับ',
            '!=' => 'ไม่เท่ากับ',
            '>' => 'มากกว่า',
            '>=' => 'มากกว่าเท่ากับ',
            '<' => 'น้อยกว่า', 
            '<=' => 'น้อยกว่าเท่ากับ',
            'LIKE' => 'มีข้อความ',
            'NOT LIKE' => 'ไม่มีข้อความ',
            'IN' => 'อยู่ในรายการ',
            'NOT IN' => 'ไม่อยู่ในรายการ',
            'EXISTS' => 'มีค่า',
            'NOT EXISTS' => 'ไม่มีค่า',
            'EMPTY' => 'ว่าง',
            'NOT EMPTY' => 'ไม่ว่าง'
        ];
    }
    
    /**
     * Get available action types
     */
    public static function getActionTypes()
    {
        return [
            'skip_record' => 'ข้ามการบันทึก',
            'set_value' => 'กำหนดค่า',
            'transform_value' => 'แปลงค่า',
            'lookup_database' => 'ค้นหาจากฐานข้อมูล',
            'download_image' => 'ดาวน์โหลดรูปภาพ',
            'calculate_price' => 'คำนวณราคา',
            'format_text' => 'จัดรูปแบบข้อความ'
        ];
    }
    
    public function apiProvider()
    {
        return $this->belongsTo(ApiProviderModel::class, 'api_provider_id');
    }
}