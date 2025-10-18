<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Backend\ApiProviderModel;
use App\Models\Backend\ApiFieldMappingModel;
use App\Models\Backend\ApiConditionModel;

class ApiProviderSeeder extends Seeder
{
    public function run()
    {
        // Zego API
        $zego = ApiProviderModel::create([
            'name' => 'Zego API',
            'code' => 'zego',
            'url' => 'https://www.zegoapi.com/v1.5/programtours',
            'headers' => [
                'Content-Type' => 'application/json',
                'auth-token' => env('ZEGO_API_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJfaWQiOiI2NDkxYTQ5ODFmM2RkMDJlMTQwNTAxNjciLCJpYXQiOjE2ODczMTU0OTh9.2WqkXy-a4DVktevsWrH0U_v9BRcvDnJg2-QNkzgNVfU')
            ],
            'config' => [
                'wholesale_id' => 3,
                'group_id' => 3,
                'image_resize' => ['width' => 600, 'height' => 600],
                'allowed_image_ext' => ['png', 'jpeg', 'jpg', 'webp'],
                'image_check_change' => 2
            ],
            'status' => 'active',
            'description' => 'Zego Tour API Provider - ดึงข้อมูลทัวร์จาก Zego API'
        ]);

        // Zego Field Mappings
        $zegoMappings = [
            // Tour fields
            ['field_type' => 'tour', 'local_field' => 'api_id', 'api_field' => 'ProductID', 'data_type' => 'string', 'is_required' => true],
            ['field_type' => 'tour', 'local_field' => 'code1', 'api_field' => 'ProductCode', 'data_type' => 'string'],
            ['field_type' => 'tour', 'local_field' => 'name', 'api_field' => 'ProductName', 'data_type' => 'string', 'is_required' => true],
            ['field_type' => 'tour', 'local_field' => 'description', 'api_field' => 'Highlight', 'data_type' => 'string', 'transformation_rules' => [['type' => 'string_replace', 'search' => "\n", 'replace' => '']]],
            ['field_type' => 'tour', 'local_field' => 'rating', 'api_field' => 'MaxHotelStars', 'data_type' => 'string'],
            ['field_type' => 'tour', 'local_field' => 'num_day', 'api_field' => 'Days', 'data_type' => 'string'],
            ['field_type' => 'tour', 'local_field' => 'image', 'api_field' => 'URLImage', 'data_type' => 'string'],
            ['field_type' => 'tour', 'local_field' => 'pdf_file', 'api_field' => 'FilePDF', 'data_type' => 'string'],
            ['field_type' => 'tour', 'local_field' => 'country_id', 'api_field' => 'CountryName', 'data_type' => 'json'],
            ['field_type' => 'tour', 'local_field' => 'airline_id', 'api_field' => 'AirlineCode', 'data_type' => 'string'],
            
            // Period fields
            ['field_type' => 'period', 'local_field' => 'periods', 'api_field' => 'Periods', 'data_type' => 'json'],
            ['field_type' => 'period', 'local_field' => 'period_api_id', 'api_field' => 'PeriodID', 'data_type' => 'string'],
            ['field_type' => 'period', 'local_field' => 'start_date', 'api_field' => 'PeriodStartDate', 'data_type' => 'date'],
            ['field_type' => 'period', 'local_field' => 'end_date', 'api_field' => 'PeriodEndDate', 'data_type' => 'date'],
            ['field_type' => 'period', 'local_field' => 'price1', 'api_field' => 'Price', 'data_type' => 'decimal'],
            ['field_type' => 'period', 'local_field' => 'price2', 'api_field' => 'Price_Single_Bed', 'data_type' => 'decimal'],
            ['field_type' => 'period', 'local_field' => 'price3', 'api_field' => 'Price_Child', 'data_type' => 'decimal'],
            ['field_type' => 'period', 'local_field' => 'price4', 'api_field' => 'Price_ChildNB', 'data_type' => 'decimal'],
            ['field_type' => 'period', 'local_field' => 'count', 'api_field' => 'Seat', 'data_type' => 'string'],
            ['field_type' => 'period', 'local_field' => 'group', 'api_field' => 'GroupSize', 'data_type' => 'string'],
            ['field_type' => 'period', 'local_field' => 'status_period_text', 'api_field' => 'PeriodStatus', 'data_type' => 'string'],
        ];

        foreach ($zegoMappings as $mapping) {
            ApiFieldMappingModel::create(array_merge($mapping, ['api_provider_id' => $zego->id]));
        }

        // Best Consortium API
        $bestconsortium = ApiProviderModel::create([
            'name' => 'Best Consortium API',
            'code' => 'bestconsortium',
            'url' => 'https://api.best-consortium.com/v1/series/country',
            'headers' => [
                'Content-Type' => 'application/json; charset=UTF-8'
            ],
            'config' => [
                'wholesale_id' => 11,
                'group_id' => 3,
                'image_resize' => ['width' => 600, 'height' => 600],
                'allowed_image_ext' => ['png', 'jpeg', 'jpg', 'webp']
            ],
            'status' => 'active',
            'description' => 'Best Consortium Tour API Provider'
        ]);

        // TTN Japan API
        $ttnJapan = ApiProviderModel::create([
            'name' => 'TTN Japan API',
            'code' => 'ttn_japan',
            'url' => 'https://online.ttnconnect.com/api/agency/get-programId',
            'headers' => [
                'Content-Type' => 'application/json; charset=UTF-8'
            ],
            'config' => [
                'wholesale_id' => 35,
                'group_id' => 3,
                'country_filter' => 'Japan'
            ],
            'status' => 'active',
            'description' => 'TTN Japan Tour API Provider - ทัวร์ญี่ปุ่น'
        ]);

        // TTN All API
        $ttnAll = ApiProviderModel::create([
            'name' => 'TTN All Countries API',
            'code' => 'ttn_all',
            'url' => 'https://online.ttnconnect.com/api/agency/get-programId',
            'headers' => [
                'Content-Type' => 'application/json; charset=UTF-8'
            ],
            'config' => [
                'wholesale_id' => 10,
                'group_id' => 3
            ],
            'status' => 'active',
            'description' => 'TTN All Countries Tour API Provider'
        ]);

        // iTravel API
        $itravel = ApiProviderModel::create([
            'name' => 'iTravel API',
            'code' => 'itravel',
            'url' => 'https://itravels.center/api/program',
            'headers' => [
                'Content-Type' => 'application/json',
                'itravels-secret' => env('ITRAVEL_API_KEY', 'f8fc60c5842687ac58473093987535dcfdca3dad9cf19862c92dbbba8eb73cd96bc9c611d4bc8291a901c15492981e475f4f')
            ],
            'config' => [
                'wholesale_id' => 5,
                'group_id' => 3
            ],
            'status' => 'active',
            'description' => 'iTravel Tour API Provider'
        ]);

        // Super Holiday API
        $superbholiday = ApiProviderModel::create([
            'name' => 'Super Holiday API',
            'code' => 'superbholiday',
            'url' => 'https://superbholidayz.com/superb/apiweb.php',
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'config' => [
                'wholesale_id' => 22,
                'group_id' => 3
            ],
            'status' => 'active',
            'description' => 'Super Holiday Tour API Provider'
        ]);

        // Tour Factory API
        $tourfactory = ApiProviderModel::create([
            'name' => 'Tour Factory API',
            'code' => 'tourfactory',
            'url' => 'https://api.tourfactory.co.th/v1/programtours',
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ],
            'config' => [
                'wholesale_id' => 55,
                'group_id' => 3
            ],
            'status' => 'active',
            'description' => 'Tour Factory API Provider'
        ]);

        // GO365 API
        $go365 = ApiProviderModel::create([
            'name' => 'GO365 API',
            'code' => 'go365',
            'url' => 'https://api.kaikongservice.com/api/v1/tours/search',
            'headers' => [
                'Content-Type' => 'application/json',
                'x-api-key' => env('GO365_API_KEY', 'eyJhbGciOiJIUzUxMiJ9.eyJhcGlfaWQiOjQ0LCJhZ2VudF9pZCI6MTAwNywidXNlcl9pZCI6MTc0OTYsImNvbXBhbnlfdGgiOiLguJrguKPguLTguKnguLHguJcg4LmA4LiZ4LmH4LiB4LiL4LmMIOC4l-C4o-C4tOC4oyDguK7guK3guKXguLTguYDguJTguKLguYwg4LiI4Liz4LiB4Lix4LiUIiwiY29tcGFueV9lbiI6Ik5FWFQgVFJJUCBIT0xJREFZIENPLixMVEQuIn0.Xitc2n14MPYDkirNEuXLLZkBU7wCrntB7KpNitqPkIMYZDdaakD4jdJUP0oR7oBwTJ2FuMCTfazaKCBYATygaw')
            ],
            'config' => [
                'wholesale_id' => 41,
                'group_id' => 3
            ],
            'status' => 'active',
            'description' => 'GO365 Tour API Provider'
        ]);

        // GO365 Field Mappings
        $go365Mappings = [
            // Tour fields
            ['field_type' => 'tour', 'local_field' => 'api_id', 'api_field' => 'tour_id', 'data_type' => 'string', 'is_required' => true],
            ['field_type' => 'tour', 'local_field' => 'code1', 'api_field' => 'tour_code', 'data_type' => 'string'],
            ['field_type' => 'tour', 'local_field' => 'name', 'api_field' => 'tour_name', 'data_type' => 'string', 'is_required' => true],
            ['field_type' => 'tour', 'local_field' => 'description', 'api_field' => 'tour_remark', 'data_type' => 'string'],
            ['field_type' => 'tour', 'local_field' => 'rating', 'api_field' => 'tour_hotel_star', 'data_type' => 'string'],
            ['field_type' => 'tour', 'local_field' => 'num_day', 'api_field' => 'tour_day_night', 'data_type' => 'string'],
            ['field_type' => 'tour', 'local_field' => 'image', 'api_field' => 'tour_cover_image', 'data_type' => 'string'],
            ['field_type' => 'tour', 'local_field' => 'pdf_file', 'api_field' => 'tour_pdf', 'data_type' => 'string'],
            ['field_type' => 'tour', 'local_field' => 'country_id', 'api_field' => 'tour_country', 'data_type' => 'json'],
            ['field_type' => 'tour', 'local_field' => 'airline_id', 'api_field' => 'tour_airline', 'data_type' => 'json'],
            
            // Period fields
            ['field_type' => 'period', 'local_field' => 'periods', 'api_field' => 'tour_period', 'data_type' => 'json'],
            ['field_type' => 'period', 'local_field' => 'period_api_id', 'api_field' => 'period_id', 'data_type' => 'string'],
            ['field_type' => 'period', 'local_field' => 'start_date', 'api_field' => 'period_start_date', 'data_type' => 'date'],
            ['field_type' => 'period', 'local_field' => 'end_date', 'api_field' => 'period_end_date', 'data_type' => 'date'],
            ['field_type' => 'period', 'local_field' => 'price1', 'api_field' => 'period_price_adult', 'data_type' => 'decimal'],
            ['field_type' => 'period', 'local_field' => 'price2', 'api_field' => 'period_price_single_bed', 'data_type' => 'decimal'],
            ['field_type' => 'period', 'local_field' => 'price3', 'api_field' => 'period_price_child', 'data_type' => 'decimal'],
            ['field_type' => 'period', 'local_field' => 'price4', 'api_field' => 'period_price_child_no_bed', 'data_type' => 'decimal'],
            ['field_type' => 'period', 'local_field' => 'count', 'api_field' => 'period_seat', 'data_type' => 'string'],
            ['field_type' => 'period', 'local_field' => 'group', 'api_field' => 'period_group_size', 'data_type' => 'string'],
            ['field_type' => 'period', 'local_field' => 'status_period_text', 'api_field' => 'period_status', 'data_type' => 'string'],
        ];

        foreach ($go365Mappings as $mapping) {
            ApiFieldMappingModel::create(array_merge($mapping, ['api_provider_id' => $go365->id]));
        }

        // iTravel Field Mappings
        $itravelMappings = [
            ['field_type' => 'tour', 'local_field' => 'api_id', 'api_field' => 'id', 'data_type' => 'string', 'is_required' => true],
            ['field_type' => 'tour', 'local_field' => 'code1', 'api_field' => 'code', 'data_type' => 'string', 'is_required' => true],
            ['field_type' => 'tour', 'local_field' => 'name', 'api_field' => 'title', 'data_type' => 'string', 'is_required' => true],
            ['field_type' => 'tour', 'local_field' => 'description', 'api_field' => 'detail', 'data_type' => 'string'],
            ['field_type' => 'tour', 'local_field' => 'num_day', 'api_field' => 'day_and_night', 'data_type' => 'string'],
            ['field_type' => 'tour', 'local_field' => 'image', 'api_field' => 'image', 'data_type' => 'string'],
            ['field_type' => 'tour', 'local_field' => 'pdf_file', 'api_field' => 'pdf', 'data_type' => 'string'],
            
            // Period fields
            ['field_type' => 'period', 'local_field' => 'periods', 'api_field' => 'periods', 'data_type' => 'json'],
            ['field_type' => 'period', 'local_field' => 'start_date', 'api_field' => 'start_date', 'data_type' => 'date'],
            ['field_type' => 'period', 'local_field' => 'end_date', 'api_field' => 'end_date', 'data_type' => 'date'],
            ['field_type' => 'period', 'local_field' => 'price1', 'api_field' => 'price_adult', 'data_type' => 'decimal'],
            ['field_type' => 'period', 'local_field' => 'price3', 'api_field' => 'price_child', 'data_type' => 'decimal'],
            ['field_type' => 'period', 'local_field' => 'count', 'api_field' => 'seat', 'data_type' => 'string'],
        ];

        foreach ($itravelMappings as $mapping) {
            ApiFieldMappingModel::create(array_merge($mapping, ['api_provider_id' => $itravel->id]));
        }

        // Tour Factory Field Mappings
        $tourfactoryMappings = [
            ['field_type' => 'tour', 'local_field' => 'api_id', 'api_field' => 'id', 'data_type' => 'string', 'is_required' => true],
            ['field_type' => 'tour', 'local_field' => 'code1', 'api_field' => 'tour_code', 'data_type' => 'string'],
            ['field_type' => 'tour', 'local_field' => 'name', 'api_field' => 'tour_name', 'data_type' => 'string', 'is_required' => true],
            ['field_type' => 'tour', 'local_field' => 'description', 'api_field' => 'tour_highlight', 'data_type' => 'string'],
            ['field_type' => 'tour', 'local_field' => 'rating', 'api_field' => 'tour_hotel_star', 'data_type' => 'string'],
            ['field_type' => 'tour', 'local_field' => 'num_day', 'api_field' => 'tour_day', 'data_type' => 'string'],
            ['field_type' => 'tour', 'local_field' => 'image', 'api_field' => 'tour_image', 'data_type' => 'string'],
            ['field_type' => 'tour', 'local_field' => 'pdf_file', 'api_field' => 'tour_pdf', 'data_type' => 'string'],
            ['field_type' => 'tour', 'local_field' => 'country_id', 'api_field' => 'country_name', 'data_type' => 'string'],
            ['field_type' => 'tour', 'local_field' => 'airline_id', 'api_field' => 'airline_code', 'data_type' => 'string'],
            
            // Period fields
            ['field_type' => 'period', 'local_field' => 'periods', 'api_field' => 'periods', 'data_type' => 'json'],
            ['field_type' => 'period', 'local_field' => 'period_api_id', 'api_field' => 'period_id', 'data_type' => 'string'],
            ['field_type' => 'period', 'local_field' => 'start_date', 'api_field' => 'start_date', 'data_type' => 'date'],
            ['field_type' => 'period', 'local_field' => 'end_date', 'api_field' => 'end_date', 'data_type' => 'date'],
            ['field_type' => 'period', 'local_field' => 'price1', 'api_field' => 'price_adult', 'data_type' => 'decimal'],
            ['field_type' => 'period', 'local_field' => 'price2', 'api_field' => 'price_single', 'data_type' => 'decimal'],
            ['field_type' => 'period', 'local_field' => 'price3', 'api_field' => 'price_child', 'data_type' => 'decimal'],
            ['field_type' => 'period', 'local_field' => 'count', 'api_field' => 'seat_available', 'data_type' => 'string'],
        ];

        foreach ($tourfactoryMappings as $mapping) {
            ApiFieldMappingModel::create(array_merge($mapping, ['api_provider_id' => $tourfactory->id]));
        }
    }
}