<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Backend\ApiProviderModel;

class TestViewDataCommand extends Command
{
    protected $signature = 'test:view-data {id?}';
    protected $description = 'ทดสอบข้อมูลสำหรับ view edit.blade.php';

    public function handle()
    {
        $id = $this->argument('id') ?? 48; // GO365 ID
        
        $this->info("=== ทดสอบข้อมูลสำหรับ API Provider ID: {$id} ===");
        
        try {
            $provider = ApiProviderModel::with(['fieldMappings', 'conditions', 'schedules', 'promotionRules'])->findOrFail($id);
            
            $this->info("✅ Provider loaded: {$provider->name}");
            $this->info("   Code: {$provider->code}");
            
            // ทดสอบ headers
            $this->info("\n--- Headers ---");
            $headers = $provider->headers;
            $this->info("Headers type: " . gettype($headers));
            
            if (is_string($headers)) {
                $this->warn("Headers is string: {$headers}");
                $decoded = json_decode($headers, true);
                $this->info("Decoded headers: " . print_r($decoded, true));
            } elseif (is_array($headers)) {
                $this->info("Headers is array: " . print_r($headers, true));
            } else {
                $this->info("Headers is null or other type");
            }
            
            // ทดสอบ relationships
            $this->info("\n--- Relationships ---");
            $this->info("Field Mappings count: " . $provider->fieldMappings->count());
            $this->info("Conditions count: " . $provider->conditions->count());
            $this->info("Promotion Rules count: " . $provider->promotionRules->count());
            
            // แสดงรายละเอียด conditions
            $this->info("\n--- Conditions Details ---");
            foreach ($provider->conditions as $index => $condition) {
                $this->info("  [{$index}] {$condition->condition_type} - {$condition->field_name}");
            }
            
            // ทดสอบ foreach ใน PHP
            $this->info("\n--- PHP Foreach Test ---");
            $headerCount = 0;
            $oldHeaders = null; // old() function จะ return null ใน command
            
            if ($oldHeaders) {
                $headers = $oldHeaders;
            } elseif ($provider->headers) {
                $headers = is_string($provider->headers) ? json_decode($provider->headers, true) : $provider->headers;
            } else {
                $headers = [];
            }
            
            if ($headers && is_array($headers)) {
                foreach ($headers as $key => $value) {
                    $this->info("  Header {$headerCount}: {$key} = {$value}");
                    $headerCount++;
                }
            } else {
                $this->info("  No headers to iterate");
            }
            
            // ทดสอบ raw headers จากฐานข้อมูล
            $this->info("\n--- Raw Database Check ---");
            $rawHeaders = $provider->getRawOriginal('headers');
            $this->info("Raw headers type: " . gettype($rawHeaders));
            $this->info("Raw headers value: " . ($rawHeaders ?? 'null'));
            
            $this->info("\n✅ ทดสอบเสร็จสิ้น - ไม่มี error");
            
        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            $this->error("Line: " . $e->getLine());
            $this->error("File: " . $e->getFile());
        }
    }
}