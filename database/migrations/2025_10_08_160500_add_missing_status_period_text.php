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
        // เพิ่ม status_period_text field ที่หายไป
        $zego = ApiProviderModel::where('code', 'zego')->first();
        
        if ($zego) {
            ApiFieldMappingModel::create([
                'api_provider_id' => $zego->id,
                'field_type' => 'period',
                'local_field' => 'status_period_text',
                'api_field' => 'PeriodStatus',
                'data_type' => 'string',
                'transformation_rules' => [
                    [
                        'type' => 'status_text_mapping',
                        'description' => 'Keep original text value for display'
                    ]
                ]
            ]);
            
            echo "✅ เพิ่ม status_period_text field สำเร็จ\n";
        }
    }

    public function down()
    {
        $zego = ApiProviderModel::where('code', 'zego')->first();
        
        if ($zego) {
            ApiFieldMappingModel::where('api_provider_id', $zego->id)
                ->where('local_field', 'status_period_text')
                ->delete();
                
            echo "🔄 ลบ status_period_text field สำเร็จ\n";
        }
    }
};