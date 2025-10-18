<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveWrongApiFieldsFromTbTourTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tb_tour', function (Blueprint $table) {
            // ลบคอลัมน์ที่เพิ่มผิด (ตรวจสอบว่ามีอยู่จริงก่อน)
            if (Schema::hasColumn('tb_tour', 'country_name')) {
                $table->dropColumn('country_name');
            }
            if (Schema::hasColumn('tb_tour', 'airline_code')) {
                $table->dropColumn('airline_code');
            }
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
            $table->string('country_name')->nullable()->after('image');
            $table->string('airline_code')->nullable()->after('country_name');
        });
    }
}