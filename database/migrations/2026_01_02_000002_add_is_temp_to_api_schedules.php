<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsTempToApiSchedules extends Migration
{
    public function up()
    {
        Schema::table('tb_api_schedules', function (Blueprint $table) {
            $table->tinyInteger('is_temp')->default(0)->after('is_active')->comment('1=temporary schedule (auto-delete after run)');
        });
    }

    public function down()
    {
        Schema::table('tb_api_schedules', function (Blueprint $table) {
            $table->dropColumn('is_temp');
        });
    }
}
