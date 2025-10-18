<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class FixDayNightFieldMappings extends Migration
{
    public function up()
    {
        // Update day and night field mappings from 'tour' to 'period' field_type
        DB::table('tb_api_field_mappings')
            ->where('api_provider_id', 49)
            ->where('local_field', 'day')
            ->where('api_field', 'P_DAY')
            ->update(['field_type' => 'period']);
            
        DB::table('tb_api_field_mappings')
            ->where('api_provider_id', 49)
            ->where('local_field', 'night')
            ->where('api_field', 'P_NIGHT')
            ->update(['field_type' => 'period']);
    }

    public function down()
    {
        // Revert changes
        DB::table('tb_api_field_mappings')
            ->where('api_provider_id', 49)
            ->where('local_field', 'day')
            ->where('api_field', 'P_DAY')
            ->update(['field_type' => 'tour']);
            
        DB::table('tb_api_field_mappings')
            ->where('api_provider_id', 49)
            ->where('local_field', 'night')
            ->where('api_field', 'P_NIGHT')
            ->update(['field_type' => 'tour']);
    }
}