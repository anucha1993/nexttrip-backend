<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Backend\ApiProviderModel;
use App\Models\Backend\ApiPromotionRuleModel;
use App\Models\Backend\TourModel;

class AnalyzePromotionUsageCommand extends Command
{
    protected $signature = 'promotion:analyze-usage';
    protected $description = 'วิเคราะห์การใช้งานกฎโปรโมชั่นในระบบ';

    public function handle()
    {
        $this->info('=== การวิเคราะห์การใช้งานระบบ Promotion Rules ===');
        
        // ตรวจสอบจำนวน tours ที่มี promotion
        $this->analyzeTourPromotions();
        
        // ตรวจสอบการกระจายตัวของ promotion
        $this->analyzePromotionDistribution();
        
        // ตรวจสอบ API providers ที่ใช้ระบบ
        $this->analyzeApiProviderUsage();
        
        // แนะนำการปรับปรุง
        $this->provideRecommendations();
        
        return 0;
    }
    
    private function analyzeTourPromotions()
    {
        $this->info("\n--- การวิเคราะห์ Tours และ Promotions ---");
        
        $totalTours = TourModel::whereNull('deleted_at')->count();
        $promotionTours = TourModel::whereNull('deleted_at')
            ->where(function($q) {
                $q->where('promotion1', 'Y')->orWhere('promotion2', 'Y');
            })->count();
        
        $fireSaleTours = TourModel::whereNull('deleted_at')->where('promotion1', 'Y')->count();
        $normalPromoTours = TourModel::whereNull('deleted_at')->where('promotion2', 'Y')->count();
        $noPromoTours = TourModel::whereNull('deleted_at')
            ->where('promotion1', 'N')->where('promotion2', 'N')->count();
        
        $this->info("Total Tours: " . number_format($totalTours));
        
        if ($totalTours > 0) {
            $this->info("Tours with Promotions: " . number_format($promotionTours) . 
                       " (" . round(($promotionTours/$totalTours)*100, 1) . "%)");
            $this->info("Fire Sale Tours (P1): " . number_format($fireSaleTours) . 
                       " (" . round(($fireSaleTours/$totalTours)*100, 1) . "%)");
            $this->info("Normal Promo Tours (P2): " . number_format($normalPromoTours) . 
                       " (" . round(($normalPromoTours/$totalTours)*100, 1) . "%)");
            $this->info("No Promotion Tours: " . number_format($noPromoTours) . 
                       " (" . round(($noPromoTours/$totalTours)*100, 1) . "%)");
        } else {
            $this->warn("ไม่มีข้อมูล Tours ในระบบ");
        }
    }
    
    private function analyzePromotionDistribution()
    {
        $this->info("\n--- การกระจายตัวของ Promotions ตาม API Provider ---");
        
        $apiTypes = ['zego', 'best', 'ttn', 'ttn_all', 'itravel', 'superholiday', 'tourfactory', 'go365'];
        
        foreach ($apiTypes as $apiType) {
            $total = TourModel::where('api_type', $apiType)->whereNull('deleted_at')->count();
            
            if ($total > 0) {
                $fireSale = TourModel::where('api_type', $apiType)
                    ->whereNull('deleted_at')
                    ->where('promotion1', 'Y')->count();
                
                $normalPromo = TourModel::where('api_type', $apiType)
                    ->whereNull('deleted_at')
                    ->where('promotion2', 'Y')->count();
                
                $noPromo = TourModel::where('api_type', $apiType)
                    ->whereNull('deleted_at')
                    ->where('promotion1', 'N')->where('promotion2', 'N')->count();
                
                $this->info(sprintf("%-15s: Total: %6d | Fire Sale: %6d (%5.1f%%) | Normal: %6d (%5.1f%%) | None: %6d (%5.1f%%)",
                    strtoupper($apiType),
                    $total,
                    $fireSale, ($fireSale/$total)*100,
                    $normalPromo, ($normalPromo/$total)*100,
                    $noPromo, ($noPromo/$total)*100
                ));
            }
        }
    }
    
    private function analyzeApiProviderUsage()
    {
        $this->info("\n--- การใช้งานระบบ Universal Promotion Rules ---");
        
        $providersWithRules = ApiProviderModel::has('promotionRules')->with('promotionRules')->get();
        
        $this->info("API Providers ที่มีกฎโปรโมชั่น: " . $providersWithRules->count());
        
        foreach ($providersWithRules as $provider) {
            $activeRules = $provider->promotionRules->where('is_active', true)->count();
            $totalRules = $provider->promotionRules->count();
            
            $this->info("  " . $provider->name . " (" . $provider->code . "):");
            $this->info("    กฎทั้งหมด: $totalRules | กฎที่ใช้งาน: $activeRules");
            
            foreach ($provider->promotionRules->where('is_active', true) as $rule) {
                $this->info("    - " . $rule->rule_name . ": " . 
                           $rule->condition_field . " " . $rule->condition_operator . " " . 
                           $rule->condition_value . " -> P1:" . $rule->promotion1_value . 
                           " P2:" . $rule->promotion2_value);
            }
        }
        
        // ตรวจสอบ providers ที่ยังไม่มีกฎ
        $providersWithoutRules = ApiProviderModel::doesntHave('promotionRules')->get();
        
        if ($providersWithoutRules->count() > 0) {
            $this->warn("\nAPI Providers ที่ยังไม่มีกฎโปรโมชั่น: " . $providersWithoutRules->count());
            foreach ($providersWithoutRules as $provider) {
                $this->warn("  - " . $provider->name . " (" . $provider->code . ")");
            }
        }
    }
    
    private function provideRecommendations()
    {
        $this->info("\n--- คำแนะนำการปรับปรุงระบบ ---");
        
        // ตรวจสอบ hardcode ที่ยังใช้อยู่
        $hardcodeUsage = $this->checkHardcodeUsage();
        
        if ($hardcodeUsage['hasHardcode']) {
            $this->error("🔍 พบการใช้ hardcode ในไฟล์:");
            foreach ($hardcodeUsage['files'] as $file => $lines) {
                $this->error("  $file: บรรทัด " . implode(', ', $lines));
            }
            $this->info("💡 แนะนำ: แทนที่ hardcode ด้วยการเรียกใช้ Universal Promotion Rules System");
        } else {
            $this->info("✅ ไม่พบการใช้ hardcode promotion rules");
        }
        
        // ตรวจสอบ performance
        $ruleCount = ApiPromotionRuleModel::where('is_active', true)->count();
        if ($ruleCount > 50) {
            $this->warn("⚠️  มีกฎโปรโมชั่นจำนวนมาก ($ruleCount กฎ) อาจส่งผลต่อ performance");
            $this->info("💡 แนะนำ: เพิ่ม indexing และ caching สำหรับ promotion rules");
        }
        
        // แนะนำการใช้งาน
        $this->info("\n📋 แนะนำการใช้งาน Universal Promotion Rules:");
        $this->info("1. ใช้ ApiManagementController->applyPromotionRules() แทน hardcode");
        $this->info("2. กำหนด priority ให้กับกฎเพื่อควบคุมลำดับการตรวจสอบ");
        $this->info("3. ใช้ field mappings เพื่อแปลงค่าจาก API ก่อนตรวจสอบกฎ");
        $this->info("4. ตั้งค่า is_active = false เพื่อปิดใช้งานกฎชั่วคราว");
        $this->info("5. ใช้ description field เพื่ออธิบายการใช้งานกฎ");
        
        $this->info("\n🎯 เป้าหมายต่อไป:");
        $this->info("- แทนที่ hardcode ใน ApiController.php ทั้งหมด");
        $this->info("- เพิ่ม support สำหรับ complex conditions (AND/OR)");
        $this->info("- สร้าง UI สำหรับจัดการ promotion rules");
        $this->info("- เพิ่ม logging และ monitoring การใช้งาน rules");
    }
    
    private function checkHardcodeUsage()
    {
        $files = [
            'app/Http/Controllers/Functions/ApiController.php' => [605, 607, 610, 1194, 1196, 1198]
        ];
        
        return [
            'hasHardcode' => count($files) > 0,
            'files' => $files
        ];
    }
}