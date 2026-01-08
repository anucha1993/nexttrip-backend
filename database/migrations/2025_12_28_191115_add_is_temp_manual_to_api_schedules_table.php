<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsTempManualToApiSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tb_api_schedules', function (Blueprint $table) {
            $table->boolean('is_temp_manual')->default(false)->comment('Flag สำหรับ manual sync ชั่วคราว');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tb_api_schedules', function (Blueprint $table) {
            $table->dropColumn('is_temp_manual');
        });
    }
}
