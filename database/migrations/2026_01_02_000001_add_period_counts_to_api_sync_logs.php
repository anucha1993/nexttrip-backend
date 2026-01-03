<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPeriodCountsToApiSyncLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tb_api_sync_logs', function (Blueprint $table) {
            $table->integer('created_periods')->default(0)->after('created_tours');
            $table->integer('updated_periods')->default(0)->after('created_periods');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tb_api_sync_logs', function (Blueprint $table) {
            $table->dropColumn(['created_periods', 'updated_periods']);
        });
    }
}
