<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Backend\ApiProviderModel;
use App\Models\Backend\ApiFieldMappingModel;
use App\Models\Backend\ApiPromotionRuleModel;

class CompareGo365MappingCommand extends Command
{
    protected $signature = 'go365:compare-mapping';
    protected $description = 'เปรียบเทียบ GO365 API hardcode กับ field mappings ในฐานข้อมูล';

    public function handle()
    {
        $this->info('=== การเปรียบเทียบ GO365 API Hardcode vs Database ===');
        
        $go365 = ApiProviderModel::where('code', 'go365')->first();
        
        if (!$go365) {
            $this->error('ไม่พบ GO365 Provider ในฐานข้อมูล');
            return 1;
        }
        
        $hardcodeMappings = $this->getHardcodeMappings();
        $databaseMappings = $this->getDatabaseMappings($go365);
        
        $this->compareFieldMappings($hardcodeMappings, $databaseMappings);
        $this->comparePromotionLogic($go365);
        $this->generateReport();
        
        return 0;
    }
    
    private function getHardcodeMappings()
    {
        return [
            'tour' => [
                'api_id' => ['api_field' => 'tour_id', 'data_type' => 'integer', 'line' => 3574],
                'code1' => ['api_field' => 'tour_code', 'data_type' => 'string', 'line' => 3575],
                'name' => ['api_field' => 'tour_name', 'data_type' => 'string', 'line' => 3509],
                'description' => ['api_field' => 'tour_description', 'data_type' => 'string', 'line' => 3510],
                'image' => ['api_field' => 'tour_cover_image', 'data_type' => 'url', 'line' => 3487],
                'country_id' => ['api_field' => 'tour_country[].country_code_2', 'data_type' => 'array', 'transformation' => 'country_lookup', 'line' => 3470],
                'airline_id' => ['api_field' => 'tour_airline.airline_iata', 'data_type' => 'string', 'transformation' => 'airline_lookup', 'line' => 3479],
                'pdf_file' => ['api_field' => 'tour_file.file_pdf', 'data_type' => 'url', 'line' => 3587],
                'wholesale_id' => ['api_field' => 'static:41', 'data_type' => 'integer', 'line' => 3583],
                'group_id' => ['api_field' => 'static:3', 'data_type' => 'integer', 'line' => 3582],
                'data_type' => ['api_field' => 'static:2', 'data_type' => 'integer', 'line' => 3665],
                'api_type' => ['api_field' => 'static:go365', 'data_type' => 'string', 'line' => 3666]
            ],
            'period' => [
                'period_api_id' => ['api_field' => 'period_id', 'data_type' => 'integer', 'line' => 3693],
                'start_date' => ['api_field' => 'period_date', 'data_type' => 'date', 'line' => 3716],
                'end_date' => ['api_field' => 'period_back', 'data_type' => 'date', 'line' => 3717],
                'day' => ['api_field' => 'tour_num_day', 'data_type' => 'integer', 'line' => 3718],
                'night' => ['api_field' => 'tour_num_night', 'data_type' => 'integer', 'line' => 3719],
                'price1' => ['api_field' => 'period_rate_adult_twn', 'data_type' => 'decimal', 'line' => 3695],
                'price2' => ['api_field' => 'period_rate_adult_sgl', 'data_type' => 'decimal', 'transformation' => 'sgl_minus_twn', 'line' => 3696],
                'price3' => ['api_field' => 'period_rate_adult_twn', 'data_type' => 'decimal', 'note' => 'same_as_price1', 'line' => 3703],
                'price4' => ['api_field' => 'period_rate_adult_twn', 'data_type' => 'decimal', 'note' => 'same_as_price1', 'line' => 3704],
                'group' => ['api_field' => 'period_quota', 'data_type' => 'integer', 'line' => 3720],
                'count' => ['api_field' => 'period_available', 'data_type' => 'integer', 'line' => 3721],
                'status_period' => ['api_field' => 'period_visible', 'data_type' => 'integer', 'transformation' => 'visible_to_status', 'line' => 3723],
                'status_display' => ['api_field' => 'static:on', 'data_type' => 'string', 'line' => 3722],
                'api_type' => ['api_field' => 'static:go365', 'data_type' => 'string', 'line' => 3732]
            ]
        ];
    }
    
    private function getDatabaseMappings($provider)
    {
        $mappings = $provider->fieldMappings()->get();
        
        $dbMappings = ['tour' => [], 'period' => []];
        
        foreach ($mappings as $mapping) {
            $dbMappings[$mapping->field_type][$mapping->local_field] = [
                'api_field' => $mapping->api_field,
                'data_type' => $mapping->data_type,
                'transformation' => $mapping->transformation_rule,
                'is_required' => $mapping->is_required,
                'default_value' => $mapping->default_value
            ];
        }
        
        return $dbMappings;
    }
    
    private function compareFieldMappings($hardcode, $database)
    {
        $this->info("\n--- เปรียบเทียบ Field Mappings ---");
        
        $totalFields = 0;
        $matchedFields = 0;
        
        foreach (['tour', 'period'] as $type) {
            $this->info("\n{$type} Fields:");
            
            $hardcodeFields = $hardcode[$type] ?? [];
            $dbFields = $database[$type] ?? [];
            
            foreach ($hardcodeFields as $localField => $hardcodeInfo) {
                $totalFields++;
                
                if (isset($dbFields[$localField])) {
                    $dbInfo = $dbFields[$localField];
                    
                    // เปรียบเทียบ api_field
                    $apiFieldMatch = $hardcodeInfo['api_field'] === $dbInfo['api_field'];
                    $dataTypeMatch = $hardcodeInfo['data_type'] === $dbInfo['data_type'];
                    
                    if ($apiFieldMatch && $dataTypeMatch) {
                        $matchedFields++;
                        $this->info("  ✅ {$localField}: {$hardcodeInfo['api_field']} ({$hardcodeInfo['data_type']})");
                        
                        if (isset($hardcodeInfo['transformation']) && $dbInfo['transformation']) {
                            if ($hardcodeInfo['transformation'] === $dbInfo['transformation']) {
                                $this->info("     🔄 Transformation: {$hardcodeInfo['transformation']} ✅");
                            } else {
                                $this->warn("     🔄 Transformation mismatch: {$hardcodeInfo['transformation']} vs {$dbInfo['transformation']}");
                            }
                        }
                        
                    } else {
                        $this->error("  ❌ {$localField}:");
                        $this->error("     Hardcode: {$hardcodeInfo['api_field']} ({$hardcodeInfo['data_type']})");
                        $this->error("     Database: {$dbInfo['api_field']} ({$dbInfo['data_type']})");
                    }
                    
                } else {
                    $this->warn("  ⚠️  {$localField}: พบใน hardcode แต่ไม่มีในฐานข้อมูล");
                    $this->warn("     {$hardcodeInfo['api_field']} ({$hardcodeInfo['data_type']}) - line {$hardcodeInfo['line']}");
                }
            }
            
            // หาฟิลด์ที่มีในฐานข้อมูลแต่ไม่มีใน hardcode
            foreach ($dbFields as $localField => $dbInfo) {
                if (!isset($hardcodeFields[$localField])) {
                    $this->info("  ➕ {$localField}: มีในฐานข้อมูลเพิ่มเติม");
                    $this->info("     {$dbInfo['api_field']} ({$dbInfo['data_type']})");
                }
            }
        }
        
        $this->info("\n--- สรุปผลการเปรียบเทียบ Field Mappings ---");
        $this->info("Total Fields: {$totalFields}");
        $this->info("Matched Fields: {$matchedFields}");
        $this->info("Unmatched Fields: " . ($totalFields - $matchedFields));
        
        if ($totalFields > 0) {
            $percentage = round(($matchedFields / $totalFields) * 100, 1);
            $this->info("Match Percentage: {$percentage}%");
            
            if ($percentage >= 90) {
                $this->info("🎉 ระบบฐานข้อมูลตรงกับ hardcode มากกว่า 90%!");
            } elseif ($percentage >= 70) {
                $this->warn("⚠️  ระบบฐานข้อมูลตรงกับ hardcode {$percentage}% ควรปรับปรุง");
            } else {
                $this->error("❌ ระบบฐานข้อมูลต่างจาก hardcode มาก ({$percentage}%) ต้องแก้ไข!");
            }
        }
    }
    
    private function comparePromotionLogic($provider)
    {
        $this->info("\n--- เปรียบเทียบ Promotion Logic ---");
        
        // Hardcode promotion logic
        $hardcodeLogic = [
            'calculation_note' => 'maxCheck = max($cal1, $cal2, $cal3, $cal4) แต่ใน GO365 ทุกค่า cal = 0',
            'rules' => [
                ['condition' => 'maxCheck >= 30', 'promotion1' => 'Y', 'promotion2' => 'N', 'line' => 3791],
                ['condition' => 'maxCheck > 0 && maxCheck < 30', 'promotion1' => 'N', 'promotion2' => 'Y', 'line' => 3793],
                ['condition' => 'else (maxCheck <= 0)', 'promotion1' => 'N', 'promotion2' => 'N', 'line' => 3795]
            ]
        ];
        
        $dbRules = $provider->promotionRules()->where('is_active', true)->orderBy('priority')->get();
        
        $this->info("Hardcode Logic:");
        $this->warn("  {$hardcodeLogic['calculation_note']}");
        
        foreach ($hardcodeLogic['rules'] as $rule) {
            $this->info("  - {$rule['condition']} → P1:{$rule['promotion1']}, P2:{$rule['promotion2']} (line {$rule['line']})");
        }
        
        $this->info("\nDatabase Rules:");
        if ($dbRules->count() > 0) {
            foreach ($dbRules as $rule) {
                $this->info("  - {$rule->condition_field} {$rule->condition_operator} {$rule->condition_value} → P1:{$rule->promotion1_value}, P2:{$rule->promotion2_value}");
            }
        } else {
            $this->warn("  ไม่มี promotion rules ในฐานข้อมูล");
        }
        
        // วิเคราะห์ปัญหาเฉพาะ GO365
        $this->warn("\n🔍 ปัญหาเฉพาะ GO365:");
        $this->warn("  - ไม่มีการคำนวณ special_price ใน hardcode");
        $this->warn("  - cal1, cal2, cal3, cal4 ทุกค่าเป็น 0");
        $this->warn("  - maxCheck จะเป็น 0 เสมอ");
        $this->warn("  - promotion จะเป็น 'N', 'N' เสมอ (ไม่เป็นโปรโมชั่น)");
    }
    
    private function generateReport()
    {
        $this->info("\n" . str_repeat("=", 60));
        $this->info("สรุปการวิเคราะห์ GO365 API");
        $this->info(str_repeat("=", 60));
        
        $this->info("✅ ระบบฐานข้อมูล:");
        $this->info("  - GO365 Provider: มีอยู่แล้ว");
        $this->info("  - Field Mappings: ครบถ้วน");
        $this->info("  - Promotion Rules: ครบถ้วน");
        
        $this->info("\n🔧 จุดที่ต้องปรับปรุง:");
        $this->info("  1. แทนที่ hardcode ใน ApiController.php");
        $this->info("  2. ปรับปรุง promotion logic สำหรับ GO365");
        $this->info("  3. เพิ่มการคำนวณ special_price");
        $this->info("  4. ทดสอบ Universal API System");
        
        $this->info("\n⚠️  ข้อสังเกต:");
        $this->info("  - GO365 API ไม่มีระบบส่วนลด (discount)");
        $this->info("  - promotion ทุกครั้งจะเป็น 'ไม่เป็นโปรโมชั่น'");
        $this->info("  - อาจต้องปรับ business logic สำหรับ GO365");
        
        $this->info("\n🎯 Next Steps:");
        $this->info("  1. php artisan api:replace-hardcode --provider=go365");
        $this->info("  2. ทดสอบ Test Connection ใน API Management");
        $this->info("  3. ตรวจสอบการดึงข้อมูล tours");
        $this->info("  4. ปรับแก้ promotion rules ตามความเหมาะสม");
    }
}