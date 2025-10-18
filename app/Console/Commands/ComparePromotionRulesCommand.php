<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Backend\ApiProviderModel;
use App\Models\Backend\ApiPromotionRuleModel;

class ComparePromotionRulesCommand extends Command
{
    protected $signature = 'promotion:compare-rules';
    protected $description = 'เปรียบเทียบกฎโปรโมชั่น hardcode กับระบบฐานข้อมูล';

    public function handle()
    {
        $this->info('=== การเปรียบเทียบกฎโปรโมชั่น Hardcode vs Database ===');
        
        // Hardcode rules from ApiController.php
        $hardcodeRules = $this->getHardcodeRules();
        
        // Database rules
        $databaseRules = $this->getDatabaseRules();
        
        $this->compareRules($hardcodeRules, $databaseRules);
        
        return 0;
    }
    
    private function getHardcodeRules()
    {
        return [
            'zego' => [
                [
                    'condition' => 'maxCheck >= 30',
                    'condition_field' => 'discount_percentage',
                    'condition_operator' => '>=',
                    'condition_value' => 30.00,
                    'promotion1' => 'Y',
                    'promotion2' => 'N',
                    'description' => 'เป็นโปรไฟไหม้',
                    'line' => 605
                ],
                [
                    'condition' => 'maxCheck > 0 && maxCheck < 30',
                    'condition_field' => 'discount_percentage',
                    'condition_operator' => '>',
                    'condition_value' => 0.00,
                    'secondary_condition' => '<',
                    'secondary_value' => 30.00,
                    'promotion1' => 'N',
                    'promotion2' => 'Y',
                    'description' => 'เป็นโปรธรรมดา',
                    'line' => 607
                ],
                [
                    'condition' => 'else (maxCheck <= 0)',
                    'condition_field' => 'discount_percentage',
                    'condition_operator' => '<=',
                    'condition_value' => 0.00,
                    'promotion1' => 'N',
                    'promotion2' => 'N',
                    'description' => 'ไม่เป็นโปรโมชั่น',
                    'line' => 610
                ]
            ],
            'best' => [
                [
                    'condition' => 'maxCheck >= 30',
                    'condition_field' => 'discount_percentage',
                    'condition_operator' => '>=',
                    'condition_value' => 30.00,
                    'promotion1' => 'Y',
                    'promotion2' => 'N',
                    'description' => 'เป็นโปรไฟไหม้',
                    'line' => 1194
                ],
                [
                    'condition' => 'maxCheck > 0 && maxCheck < 30',
                    'condition_field' => 'discount_percentage',
                    'condition_operator' => '>',
                    'condition_value' => 0.00,
                    'secondary_condition' => '<',
                    'secondary_value' => 30.00,
                    'promotion1' => 'N',
                    'promotion2' => 'Y',
                    'description' => 'เป็นโปรธรรมดา',
                    'line' => 1196
                ],
                [
                    'condition' => 'else (maxCheck <= 0)',
                    'condition_field' => 'discount_percentage',
                    'condition_operator' => '<=',
                    'condition_value' => 0.00,
                    'promotion1' => 'N',
                    'promotion2' => 'N',
                    'description' => 'ไม่เป็นโปรโมชั่น',
                    'line' => 1198
                ]
            ]
        ];
    }
    
    private function getDatabaseRules()
    {
        $providers = ApiProviderModel::whereIn('code', ['zego', 'bestconsortium'])
            ->with(['promotionRules' => function($query) {
                $query->where('is_active', true)->orderBy('priority');
            }])
            ->get();
            
        $dbRules = [];
        foreach ($providers as $provider) {
            $dbRules[$provider->code] = [];
            foreach ($provider->promotionRules as $rule) {
                $dbRules[$provider->code][] = [
                    'id' => $rule->id,
                    'rule_name' => $rule->rule_name,
                    'condition_field' => $rule->condition_field,
                    'condition_operator' => $rule->condition_operator,
                    'condition_value' => $rule->condition_value,
                    'promotion_type' => $rule->promotion_type,
                    'promotion1' => $rule->promotion1_value,
                    'promotion2' => $rule->promotion2_value,
                    'priority' => $rule->priority,
                    'description' => $rule->description
                ];
            }
        }
        
        return $dbRules;
    }
    
    private function compareRules($hardcodeRules, $databaseRules)
    {
        $totalComparisons = 0;
        $matches = 0;
        
        foreach (['zego', 'bestconsortium'] as $provider) {
            $providerKey = $provider === 'bestconsortium' ? 'best' : $provider;
            
            $this->info("\n--- ผู้ให้บริการ: " . strtoupper($provider) . " ---");
            
            $hardcode = $hardcodeRules[$providerKey] ?? [];
            $database = $databaseRules[$provider] ?? [];
            
            $this->info("Hardcode Rules: " . count($hardcode));
            $this->info("Database Rules: " . count($database));
            
            if (empty($hardcode)) {
                $this->warn("ไม่พบกฎ hardcode สำหรับ $provider");
                continue;
            }
            
            if (empty($database)) {
                $this->error("ไม่พบกฎในฐานข้อมูลสำหรับ $provider");
                continue;
            }
            
            // เปรียบเทียบแต่ละกฎ
            foreach ($hardcode as $index => $hRule) {
                $totalComparisons++;
                $this->info("\n  กฎที่ " . ($index + 1) . ":");
                $this->info("    Hardcode: " . $hRule['condition'] . " -> P1:" . $hRule['promotion1'] . " P2:" . $hRule['promotion2']);
                
                // หา matching rule ในฐานข้อมูล
                $matchFound = false;
                foreach ($database as $dRule) {
                    if ($this->rulesMatch($hRule, $dRule)) {
                        $matches++;
                        $matchFound = true;
                        $this->info("    Database: " . $dRule['rule_name'] . " ✅ MATCH");
                        $this->info("              " . $dRule['condition_field'] . " " . 
                                  $dRule['condition_operator'] . " " . $dRule['condition_value'] . 
                                  " -> P1:" . $dRule['promotion1'] . " P2:" . $dRule['promotion2']);
                        break;
                    }
                }
                
                if (!$matchFound) {
                    $this->error("    Database: ❌ NO MATCH FOUND");
                    
                    // แสดงกฎที่มีในฐานข้อมูล
                    $this->info("    Available DB Rules:");
                    foreach ($database as $dRule) {
                        $this->info("      - " . $dRule['rule_name'] . ": " . 
                                  $dRule['condition_field'] . " " . 
                                  $dRule['condition_operator'] . " " . 
                                  $dRule['condition_value'] . 
                                  " -> P1:" . $dRule['promotion1'] . " P2:" . $dRule['promotion2']);
                    }
                }
            }
        }
        
        // สรุปผลการเปรียบเทียบ
        $this->info("\n" . str_repeat("=", 60));
        $this->info("สรุปผลการเปรียบเทียบ:");
        $this->info("รวมทั้งหมด: $totalComparisons กฎ");
        $this->info("ตรงกน: $matches กฎ");
        $this->info("ไม่ตรงกน: " . ($totalComparisons - $matches) . " กฎ");
        
        if ($totalComparisons > 0) {
            $percentage = round(($matches / $totalComparisons) * 100, 1);
            $this->info("อัตราความตรงกน: $percentage%");
            
            if ($percentage >= 90) {
                $this->info("🎉 ระบบฐานข้อมูลตรงกบ hardcode มากกว่า 90%!");
            } elseif ($percentage >= 70) {
                $this->warn("⚠️  ระบบฐานข้อมูลตรงกบ hardcode $percentage% ควรปรับปรุง");
            } else {
                $this->error("❌ ระบบฐานข้อมูลต่างจาก hardcode มาก ($percentage%) ต้องแก้ไข!");
            }
        }
    }
    
    private function rulesMatch($hardcodeRule, $databaseRule)
    {
        // เปรียบเทียบเงื่อนไขหลัก
        $fieldMatch = $hardcodeRule['condition_field'] === $databaseRule['condition_field'];
        $operatorMatch = $hardcodeRule['condition_operator'] === $databaseRule['condition_operator'];
        $valueMatch = abs(floatval($hardcodeRule['condition_value']) - floatval($databaseRule['condition_value'])) < 0.01;
        $promotion1Match = $hardcodeRule['promotion1'] === $databaseRule['promotion1'];
        $promotion2Match = $hardcodeRule['promotion2'] === $databaseRule['promotion2'];
        
        return $fieldMatch && $operatorMatch && $valueMatch && $promotion1Match && $promotion2Match;
    }
}