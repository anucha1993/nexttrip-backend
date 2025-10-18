<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddApiFieldsToTbTourTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tb_tour', function (Blueprint $table) {
            // เพิ่มคอลัมน์ที่ API ต้องการ
            $table->string('country_name')->nullable()->after('image');
            $table->string('airline_code')->nullable()->after('country_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tb_tour', function (Blueprint $table) {
            $table->dropColumn(['country_name', 'airline_code']);
        });
    }
}