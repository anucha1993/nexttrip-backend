<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Backend\ApiProviderModel;
use App\Models\Backend\ApiFieldMappingModel;

class AddZegoPeriodsArrayMapping extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // แก้ไข Zego periods field mapping จาก json เป็น array
        $zego = ApiProviderModel::where('code', 'zego')->first();
        
        if ($zego) {
            $periodsMapping = ApiFieldMappingModel::where('api_provider_id', $zego->id)
                ->where('field_type', 'period')
                ->where('local_field', 'periods')
                ->first();
            
            if ($periodsMapping) {
                $oldDataType = $periodsMapping->data_type;
                $periodsMapping->data_type = 'array';
                $periodsMapping->save();
                
                echo "✅ แก้ไข Zego periods mapping data_type จาก '{$oldDataType}' เป็น 'array'\n";
            } else {
                // ถ้าไม่มี ให้สร้างใหม่
                ApiFieldMappingModel::create([
                    'api_provider_id' => $zego->id,
                    'field_type' => 'period',
                    'local_field' => 'periods',
                    'api_field' => 'Periods',
                    'data_type' => 'array',
                    'is_required' => false,
                    'transformation_rules' => null
                ]);
                
                echo "✅ เพิ่ม Zego periods array field mapping สำเร็จ\n";
            }
        } else {
            echo "❌ ไม่พบ Zego API Provider\n";
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $zego = ApiProviderModel::where('code', 'zego')->first();
        
        if ($zego) {
            $periodsMapping = ApiFieldMappingModel::where('api_provider_id', $zego->id)
                ->where('field_type', 'period')
                ->where('local_field', 'periods')
                ->first();
                
            if ($periodsMapping) {
                $periodsMapping->data_type = 'json';
                $periodsMapping->save();
                
                echo "🔄 คืนค่า Zego periods mapping data_type เป็น 'json'\n";
            }
        }
    }
}
