<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApiManagementTables extends Migration
{
    public function up()
    {
        // ตาราง API Providers
        Schema::create('tb_api_providers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('ชื่อ API Provider');
            $table->string('code')->unique()->comment('รหัส API (เช่น zego, bestconsortium)');
            $table->string('url')->comment('URL API');
            $table->json('headers')->nullable()->comment('Headers สำหรับ API');
            $table->json('config')->nullable()->comment('การตั้งค่าอื่นๆ');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->text('description')->nullable()->comment('คำอธิบาย');
            $table->timestamps();
        });

        // ตาราง API Mapping Fields
        Schema::create('tb_api_field_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('api_provider_id')->constrained('tb_api_providers')->onDelete('cascade');
            $table->string('field_type')->comment('ประเภทฟิลด์ (tour, period)');
            $table->string('local_field')->comment('ชื่อฟิลด์ในระบบ');
            $table->string('api_field')->comment('ชื่อฟิลด์ใน API');
            $table->string('data_type')->default('string')->comment('ประเภทข้อมูล');
            $table->json('transformation_rules')->nullable()->comment('กฎการแปลงข้อมูล');
            $table->boolean('is_required')->default(false);
            $table->timestamps();
        });

        // ตาราง API Conditions
        Schema::create('tb_api_conditions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('api_provider_id')->constrained('tb_api_providers')->onDelete('cascade');
            $table->string('condition_type')->comment('ประเภทเงื่อนไข (price_calculation, image_processing, etc.)');
            $table->string('field_name')->comment('ชื่อฟิลด์ที่จะใช้เงื่อนไข');
            $table->json('condition_rules')->comment('กฎเงื่อนไข');
            $table->integer('priority')->default(0)->comment('ลำดับความสำคัญ');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ตาราง API Schedule
        Schema::create('tb_api_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('api_provider_id')->constrained('tb_api_providers')->onDelete('cascade');
            $table->string('schedule_type')->comment('ประเภทการจัดตาราง (cron, interval)');
            $table->string('schedule_value')->comment('ค่าการจัดตาราง (cron expression หรือ minutes)');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_run_at')->nullable();
            $table->timestamp('next_run_at')->nullable();
            $table->timestamps();
        });

        // ตาราง API Sync Logs
        Schema::create('tb_api_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('api_provider_id')->constrained('tb_api_providers')->onDelete('cascade');
            $table->enum('sync_type', ['auto', 'manual'])->default('manual');
            $table->enum('status', ['running', 'completed', 'failed'])->default('running');
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->integer('total_records')->default(0);
            $table->integer('created_tours')->default(0);
            $table->integer('updated_tours')->default(0);
            $table->integer('duplicated_tours')->default(0);
            $table->integer('error_count')->default(0);
            $table->text('error_message')->nullable();
            $table->json('summary')->nullable()->comment('สรุปผลการ sync');
            $table->timestamps();
        });

        // ตาราง Duplicate Tours
        Schema::create('tb_tour_duplicates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('api_provider_id')->constrained('tb_api_providers')->onDelete('cascade');
            $table->foreignId('sync_log_id')->constrained('tb_api_sync_logs')->onDelete('cascade');
            $table->string('api_id')->comment('ID จาก API');
            $table->integer('existing_tour_id')->nullable()->comment('ID ทัวร์ที่มีอยู่แล้ว');
            $table->json('api_data')->comment('ข้อมูลจาก API');
            $table->json('comparison_result')->nullable()->comment('ผลการเปรียบเทียบ');
            $table->enum('status', ['pending', 'merged', 'ignored'])->default('pending');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });

        // ตาราง API Test Results
        Schema::create('tb_api_test_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('api_provider_id')->constrained('tb_api_providers')->onDelete('cascade');
            $table->enum('test_type', ['connection', 'data_format', 'full_test'])->default('connection');
            $table->enum('status', ['success', 'failed'])->default('failed');
            $table->text('response_message')->nullable();
            $table->json('response_data')->nullable();
            $table->decimal('response_time', 8, 3)->nullable()->comment('เวลาตอบสนองเป็นวินาที');
            $table->integer('response_size')->nullable()->comment('ขนาดข้อมูลที่ได้รับ');
            $table->timestamp('tested_at');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tb_api_test_results');
        Schema::dropIfExists('tb_tour_duplicates');
        Schema::dropIfExists('tb_api_sync_logs');
        Schema::dropIfExists('tb_api_schedules');
        Schema::dropIfExists('tb_api_conditions');
        Schema::dropIfExists('tb_api_field_mappings');
        Schema::dropIfExists('tb_api_providers');
    }
}