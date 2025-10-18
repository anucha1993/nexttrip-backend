<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Backend\ApiProviderModel;
use App\Models\Backend\ApiFieldMappingModel;
use App\Models\Backend\ApiPromotionRuleModel;
use Illuminate\Support\Facades\Http;

class AnalyzeGo365ApiCommand extends Command
{
    protected $signature = 'api:analyze-go365';
    protected $description = 'วิเคราะห์ GO365 API และเปรียบเทียบ hardcode กับฐานข้อมูล';

    public function handle()
    {
        $this->info('=== การวิเคราะห์ GO365 API ===');
        
        // ตรวจสอบ GO365 provider ในฐานข้อมูล
        $this->checkGo365Provider();
        
        // วิเคราะห์ hardcode จาก ApiController
        $this->analyzeHardcode();
        
        // ทดสอบ API endpoint
        $this->testApiEndpoint();
        
        // แนะนำการสร้าง GO365 provider
        $this->recommendSetup();
        
        return 0;
    }
    
    private function checkGo365Provider()
    {
        $this->info("\n--- ตรวจสอบ GO365 Provider ในฐานข้อมูล ---");
        
        $go365 = ApiProviderModel::where('code', 'go365')->first();
        
        if ($go365) {
            $this->info("✅ พบ GO365 Provider:");
            $this->info("   ID: {$go365->id}");
            $this->info("   Name: {$go365->name}");
            $this->info("   Code: {$go365->code}");
            $this->info("   Base URL: " . ($go365->base_url ?? 'ไม่ได้กำหนด'));
            $this->info("   API Endpoint: " . ($go365->api_endpoint ?? 'ไม่ได้กำหนด'));
            $this->info("   Status: " . ($go365->status ?? 'ไม่ได้กำหนด'));
            
            $fieldMappings = $go365->fieldMappings()->count();
            $promotionRules = $go365->promotionRules()->count();
            
            $this->info("   Field Mappings: {$fieldMappings}");
            $this->info("   Promotion Rules: {$promotionRules}");
            
            if ($fieldMappings == 0) {
                $this->warn("   ⚠️  ไม่มี Field Mappings");
            }
            
            if ($promotionRules == 0) {
                $this->warn("   ⚠️  ไม่มี Promotion Rules");
            }
            
        } else {
            $this->error("❌ ไม่พบ GO365 Provider ในฐานข้อมูล");
            
            // ค้นหา providers ที่คล้ายกัน
            $similarProviders = ApiProviderModel::where('name', 'like', '%365%')
                ->orWhere('code', 'like', '%365%')
                ->orWhere('name', 'like', '%go%')
                ->get();
            
            if ($similarProviders->count() > 0) {
                $this->info("   Providers ที่คล้ายกัน:");
                foreach ($similarProviders as $provider) {
                    $this->info("   - {$provider->name} ({$provider->code})");
                }
            }
        }
    }
    
    private function analyzeHardcode()
    {
        $this->info("\n--- วิเคราะห์ Hardcode ใน ApiController ---");
        
        $hardcodeInfo = [
            'api_url' => 'https://api.kaikongservice.com/api/v1/tours/search',
            'detail_url' => 'https://api.kaikongservice.com/api/v1/tours/detail/{tour_id}',
            'api_key_env' => 'GO365_API_KEY',
            'headers' => [
                'Content-Type' => 'application/json',
                'x-api-key' => 'env(GO365_API_KEY)'
            ],
            'wholesale_id' => 41,
            'api_type' => 'go365',
            'endpoints' => [
                'tours_search' => '/api/v1/tours/search',
                'tour_detail' => '/api/v1/tours/detail/{id}'
            ]
        ];
        
        $this->info("API Base URL: {$hardcodeInfo['api_url']}");
        $this->info("Detail Endpoint: {$hardcodeInfo['detail_url']}");
        $this->info("Wholesale ID: {$hardcodeInfo['wholesale_id']}");
        $this->info("API Type: {$hardcodeInfo['api_type']}");
        
        // วิเคราะห์ field mappings จาก hardcode
        $this->analyzeFieldMappings();
        
        // วิเคราะห์ promotion rules จาก hardcode
        $this->analyzePromotionRules();
    }
    
    private function analyzeFieldMappings()
    {
        $this->info("\n--- Field Mappings จาก Hardcode ---");
        
        $tourFields = [
            'api_id' => 'tour_id',
            'code1' => 'tour_code', 
            'name' => 'tour_name',
            'description' => 'tour_description',
            'image' => 'tour_cover_image',
            'country_id' => 'tour_country[].country_code_2',
            'airline_id' => 'tour_airline.airline_iata',
            'pdf_file' => 'tour_file.file_pdf'
        ];
        
        $periodFields = [
            'period_api_id' => 'period_id',
            'start_date' => 'period_date',
            'end_date' => 'period_back',
            'day' => 'tour_num_day',
            'night' => 'tour_num_night',
            'price1' => 'period_rate_adult_twn',
            'price2' => 'period_rate_adult_sgl (calculated)',
            'price3' => 'period_rate_adult_twn (same as price1)',
            'price4' => 'period_rate_adult_twn (same as price1)',
            'group' => 'period_quota',
            'count' => 'period_available',
            'status_period' => 'period_visible'
        ];
        
        $this->info("TOUR Fields:");
        foreach ($tourFields as $local => $api) {
            $this->info("  {$local} => {$api}");
        }
        
        $this->info("\nPERIOD Fields:");
        foreach ($periodFields as $local => $api) {
            $this->info("  {$local} => {$api}");
        }
    }
    
    private function analyzePromotionRules()
    {
        $this->info("\n--- Promotion Rules จาก Hardcode ---");
        
        $this->info("เงื่อนไขโปรโมชั่น (บรรทัด 3789-3795):");
        $this->info("1. maxCheck >= 30 → promotion1='Y', promotion2='N' (โปรไฟไหม้)");
        $this->info("2. maxCheck > 0 && maxCheck < 30 → promotion1='N', promotion2='Y' (โปรธรรมดา)");
        $this->info("3. else → promotion1='N', promotion2='N' (ไม่เป็นโปรโมชั่น)");
        
        $this->warn("⚠️  หมายเหตุ: maxCheck = max(cal1, cal2, cal3, cal4) แต่ใน GO365 ทุกค่า cal = 0");
        $this->warn("   เนื่องจากไม่มีการคำนวณ special_price ใน GO365 API");
    }
    
    private function testApiEndpoint()
    {
        $this->info("\n--- ทดสอบ API Endpoint ---");
        
        $apiKey = env('GO365_API_KEY');
        if (!$apiKey) {
            $this->error("❌ ไม่พบ GO365_API_KEY ใน .env");
            return;
        }
        
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'x-api-key' => $apiKey
            ])->timeout(10)->get('https://api.kaikongservice.com/api/v1/tours/search');
            
            if ($response->successful()) {
                $data = $response->json();
                $this->info("✅ API ตอบสนองสำเร็จ");
                
                if (isset($data['data']) && is_array($data['data'])) {
                    $count = count($data['data']);
                    $this->info("   จำนวน tours: {$count}");
                    
                    if ($count > 0) {
                        $firstTour = $data['data'][0];
                        $this->info("   ตัวอย่างข้อมูล tour แรก:");
                        $this->info("   - tour_id: " . ($firstTour['tour_id'] ?? 'N/A'));
                        $this->info("   - tour_name: " . ($firstTour['tour_name'] ?? 'N/A'));
                        $this->info("   - tour_code: " . ($firstTour['tour_code'] ?? 'N/A'));
                    }
                } else {
                    $this->warn("   ไม่พบข้อมูล tours ใน response");
                }
                
            } else {
                $this->error("❌ API ตอบสนองไม่สำเร็จ: " . $response->status());
            }
            
        } catch (\Exception $e) {
            $this->error("❌ เกิดข้อผิดพลาดในการเชื่อมต่อ API: " . $e->getMessage());
        }
    }
    
    private function recommendSetup()
    {
        $this->info("\n--- คำแนะนำการตั้งค่า GO365 API ---");
        
        $go365 = ApiProviderModel::where('code', 'go365')->first();
        
        if (!$go365) {
            $this->info("🔧 การสร้าง GO365 Provider:");
            $this->info("1. สร้าง API Provider ในฐานข้อมูล");
            $this->info("2. กำหนด field mappings");
            $this->info("3. สร้าง promotion rules");
            $this->info("4. ทดสอบการเชื่อมต่อ");
            
            if ($this->confirm('ต้องการสร้าง GO365 Provider อัตโนมัติหรือไม่?')) {
                $this->createGo365Provider();
            }
        } else {
            $this->info("🔧 การปรับปรุง GO365 Provider:");
            
            if ($go365->fieldMappings()->count() == 0) {
                $this->info("1. ✅ เพิ่ม field mappings");
                if ($this->confirm('ต้องการเพิ่ม field mappings หรือไม่?')) {
                    $this->createFieldMappings($go365);
                }
            }
            
            if ($go365->promotionRules()->count() == 0) {
                $this->info("2. ✅ เพิ่ม promotion rules");
                if ($this->confirm('ต้องการเพิ่ม promotion rules หรือไม่?')) {
                    $this->createPromotionRules($go365);
                }
            }
        }
        
        $this->info("\n🎯 ขั้นตอนต่อไป:");
        $this->info("- แทนที่ hardcode ใน ApiController ด้วย Universal API System");
        $this->info("- ทดสอบ Test Connection ผ่าน API Management UI");
        $this->info("- ตรวจสอบการดึงข้อมูล tours และ periods");
        $this->info("- ปรับปรุง promotion rules ตามความต้องการ");
    }
    
    private function createGo365Provider()
    {
        try {
            $provider = ApiProviderModel::create([
                'name' => 'GO365 API',
                'code' => 'go365',
                'base_url' => 'https://api.kaikongservice.com',
                'api_endpoint' => '/api/v1/tours/search',
                'headers' => json_encode([
                    'Content-Type' => 'application/json',
                    'x-api-key' => '${GO365_API_KEY}'
                ]),
                'status' => 'active',
                'description' => 'GO365 Tours API Integration',
                'additional_config' => json_encode([
                    'detail_endpoint' => '/api/v1/tours/detail/{id}',
                    'wholesale_id' => 41,
                    'requires_multi_step' => true
                ])
            ]);
            
            $this->info("✅ สร้าง GO365 Provider สำเร็จ (ID: {$provider->id})");
            
            // สร้าง field mappings และ promotion rules
            $this->createFieldMappings($provider);
            $this->createPromotionRules($provider);
            
        } catch (\Exception $e) {
            $this->error("❌ เกิดข้อผิดพลาดในการสร้าง provider: " . $e->getMessage());
        }
    }
    
    private function createFieldMappings($provider)
    {
        $tourMappings = [
            ['local_field' => 'api_id', 'api_field' => 'tour_id', 'data_type' => 'integer'],
            ['local_field' => 'code1', 'api_field' => 'tour_code', 'data_type' => 'string'],
            ['local_field' => 'name', 'api_field' => 'tour_name', 'data_type' => 'string'],
            ['local_field' => 'description', 'api_field' => 'tour_description', 'data_type' => 'string'],
            ['local_field' => 'image', 'api_field' => 'tour_cover_image', 'data_type' => 'url'],
            ['local_field' => 'country_id', 'api_field' => 'tour_country', 'data_type' => 'array', 'transformation_rule' => 'country_code_lookup'],
            ['local_field' => 'airline_id', 'api_field' => 'tour_airline.airline_iata', 'data_type' => 'string', 'transformation_rule' => 'airline_lookup'],
            ['local_field' => 'pdf_file', 'api_field' => 'tour_file.file_pdf', 'data_type' => 'url'],
        ];
        
        $periodMappings = [
            ['local_field' => 'period_api_id', 'api_field' => 'period_id', 'data_type' => 'integer'],
            ['local_field' => 'start_date', 'api_field' => 'period_date', 'data_type' => 'date'],
            ['local_field' => 'end_date', 'api_field' => 'period_back', 'data_type' => 'date'],
            ['local_field' => 'day', 'api_field' => 'tour_num_day', 'data_type' => 'integer'],
            ['local_field' => 'night', 'api_field' => 'tour_num_night', 'data_type' => 'integer'],
            ['local_field' => 'price1', 'api_field' => 'period_rate_adult_twn', 'data_type' => 'decimal'],
            ['local_field' => 'price2', 'api_field' => 'period_rate_adult_sgl', 'data_type' => 'decimal', 'transformation_rule' => 'sgl_minus_twn'],
            ['local_field' => 'price3', 'api_field' => 'period_rate_adult_twn', 'data_type' => 'decimal'],
            ['local_field' => 'price4', 'api_field' => 'period_rate_adult_twn', 'data_type' => 'decimal'],
            ['local_field' => 'group', 'api_field' => 'period_quota', 'data_type' => 'integer'],
            ['local_field' => 'count', 'api_field' => 'period_available', 'data_type' => 'integer'],
            ['local_field' => 'status_period', 'api_field' => 'period_visible', 'data_type' => 'integer', 'transformation_rule' => 'visible_to_status'],
        ];
        
        $created = 0;
        
        foreach ($tourMappings as $mapping) {
            ApiFieldMappingModel::create(array_merge($mapping, [
                'api_provider_id' => $provider->id,
                'field_type' => 'tour'
            ]));
            $created++;
        }
        
        foreach ($periodMappings as $mapping) {
            ApiFieldMappingModel::create(array_merge($mapping, [
                'api_provider_id' => $provider->id,
                'field_type' => 'period'
            ]));
            $created++;
        }
        
        $this->info("✅ สร้าง field mappings สำเร็จ: {$created} mappings");
    }
    
    private function createPromotionRules($provider)
    {
        $rules = [
            [
                'rule_name' => 'Fire Sale Rule',
                'condition_field' => 'discount_percentage',
                'condition_operator' => '>=',
                'condition_value' => 30.00,
                'promotion_type' => 'fire_sale',
                'promotion1_value' => 'Y',
                'promotion2_value' => 'N',
                'priority' => 1,
                'description' => 'โปรไฟไหม้ สำหรับส่วนลด >= 30%'
            ],
            [
                'rule_name' => 'Normal Promotion Rule',
                'condition_field' => 'discount_percentage',
                'condition_operator' => '>',
                'condition_value' => 0.00,
                'promotion_type' => 'normal',
                'promotion1_value' => 'N',
                'promotion2_value' => 'Y',
                'priority' => 2,
                'description' => 'โปรธรรมดา สำหรับส่วนลด > 0%'
            ],
            [
                'rule_name' => 'No Promotion Rule',
                'condition_field' => 'discount_percentage',
                'condition_operator' => '<=',
                'condition_value' => 0.00,
                'promotion_type' => 'none',
                'promotion1_value' => 'N',
                'promotion2_value' => 'N',
                'priority' => 3,
                'description' => 'ไม่เป็นโปรโมชั่น'
            ]
        ];
        
        foreach ($rules as $rule) {
            ApiPromotionRuleModel::create(array_merge($rule, [
                'api_provider_id' => $provider->id,
                'is_active' => true
            ]));
        }
        
        $this->info("✅ สร้าง promotion rules สำเร็จ: " . count($rules) . " rules");
    }
}