<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tb_api_promotion_rules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('api_provider_id');
            $table->string('rule_name', 100)->comment('ชื่อกฎ เช่น "Fire Sale Rule", "Normal Promo Rule"');
            $table->string('condition_field', 50)->comment('ฟิลด์ที่ใช้เป็นเงื่อนไข เช่น "discount_percentage"');
            $table->string('condition_operator', 10)->comment('เครื่องหมายเปรียบเทียบ >=, <=, =, >, <');
            $table->decimal('condition_value', 10, 2)->comment('ค่าเปรียบเทียบ เช่น 30.00');
            $table->enum('promotion_type', ['fire_sale', 'normal', 'none'])->comment('ประเภทโปรโมชั่น');
            $table->char('promotion1_value', 1)->default('N')->comment('ค่า promotion1 (Y/N)');
            $table->char('promotion2_value', 1)->default('N')->comment('ค่า promotion2 (Y/N)');
            $table->integer('priority')->default(1)->comment('ลำดับความสำคัญ (ต่ำ = ทำก่อน)');
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable()->comment('รายละเอียดกฎ');
            $table->timestamps();
            
            $table->foreign('api_provider_id')->references('id')->on('tb_api_providers')->onDelete('cascade');
            $table->index(['api_provider_id', 'is_active']);
            $table->index(['priority']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tb_api_promotion_rules');
    }
};