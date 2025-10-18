<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('tb_api_providers', function (Blueprint $table) {
            // เพิ่ม field สำหรับ endpoint สำหรับ periods
            $table->string('period_endpoint')->nullable()->after('url');
            
            // เพิ่ม field สำหรับ tour detail endpoint (สำหรับกรณีที่ต้องเรียก API หลายขั้น)
            $table->string('tour_detail_endpoint')->nullable()->after('period_endpoint');
            
            // เพิ่ม field สำหรับกำหนดว่า API ต้องเรียกแบบหลายขั้นหรือไม่
            $table->boolean('requires_multi_step')->default(false)->after('tour_detail_endpoint');
            
            // เพิ่ม field สำหรับเก็บ URL parameters pattern
            $table->json('url_parameters')->nullable()->after('requires_multi_step');
        });
    }

    public function down()
    {
        Schema::table('tb_api_providers', function (Blueprint $table) {
            $table->dropColumn(['period_endpoint', 'tour_detail_endpoint', 'requires_multi_step', 'url_parameters']);
        });
    }
};