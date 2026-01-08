<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Backend\ApiProviderModel;
use App\Models\Backend\ApiFieldMappingModel;
use App\Models\Backend\ApiConditionModel;
use App\Models\Backend\ApiPromotionRuleModel;
use App\Models\Backend\ApiScheduleModel;
use App\Models\Backend\ApiSyncLogModel;
use App\Models\Backend\TourDuplicateModel;
use App\Models\Backend\ApiTestResultModel;
use App\Models\Backend\TourModel;
use App\Models\Backend\TourPeriodModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Carbon\Carbon;

class ApiManagementController extends Controller
{
    public function index()
    {
        $providers = ApiProviderModel::with([
            'lastSync',
            'schedules' => function($query) {
                $query->where('is_active', true);
            }
        ])
        ->withCount(['duplicates' => function($query) {
            $query->where('status', 'pending');
        }])
        ->get();
        
        return view('backend.pages.api-management.index', compact('providers'));
    }

    public function create()
    {
        return view('backend.pages.api-management.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:tb_api_providers,code',
            'url' => 'required|url',
            'period_endpoint' => 'nullable|string',
            'tour_detail_endpoint' => 'nullable|string',
            'requires_multi_step' => 'boolean',
            'url_parameters' => 'nullable|array',
            'description' => 'nullable|string',
            'headers' => 'nullable|array',
            'config' => 'nullable|array',
        ]);

        DB::beginTransaction();
        try {
            $provider = ApiProviderModel::create([
                'name' => $request->name,
                'code' => $request->code,
                'url' => $request->url,
                'period_endpoint' => $request->period_endpoint,
                'tour_detail_endpoint' => $request->tour_detail_endpoint,
                'requires_multi_step' => $request->boolean('requires_multi_step'),
                'url_parameters' => $request->url_parameters ?? [],
                'headers' => $request->headers ?? [],
                'config' => $request->config ?? [],
                'description' => $request->description,
                'status' => $request->status ?? 'active'
            ]);

            // สร้าง Field Mappings ถ้ามี
            if ($request->has('field_mappings') && is_array($request->field_mappings)) {
                foreach ($request->field_mappings as $mapping) {
                    if (!empty($mapping['local_field'])) {
                        // กำหนด transformation_rules สำหรับ static values
                        $transformationRules = $mapping['transformation_rules'] ?? [];
                        
                        // ถ้ามี static_value ให้สร้าง transformation rules
                        if (!empty($mapping['static_value']) && empty($mapping['api_field'])) {
                            $transformationRules = [
                                'type' => 'static_value',
                                'static_value' => $mapping['static_value']
                            ];
                        }
                        
                        ApiFieldMappingModel::create([
                            'api_provider_id' => $provider->id,
                            'field_type' => $mapping['field_type'] ?? 'tour',
                            'local_field' => $mapping['local_field'],
                            'api_field' => $mapping['api_field'] ?? '',
                            'data_type' => $mapping['data_type'] ?? 'string',
                            'transformation_rules' => $transformationRules,
                            'is_required' => $mapping['is_required'] ?? false
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('api-management.index')->with('success', 'API Provider created successfully!');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error creating API Provider: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $provider = ApiProviderModel::with([
            'fieldMappings', 'conditions', 'schedules', 
            'syncLogs', 'testResults'
        ])->findOrFail($id);
        
        // Get last sync log
        $lastSync = ApiSyncLogModel::where('api_provider_id', $id)
            ->orderBy('created_at', 'desc')
            ->first();
            
        // Get sync statistics
        $syncStats = [
            'total' => ApiSyncLogModel::where('api_provider_id', $id)->count(),
            'success' => ApiSyncLogModel::where('api_provider_id', $id)->where('status', 'completed')->count(),
            'failed' => ApiSyncLogModel::where('api_provider_id', $id)->where('status', 'failed')->count(),
        ];
        
        // Get recent logs (last 5)
        $recentLogs = ApiSyncLogModel::where('api_provider_id', $id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        return view('backend.pages.api-management.show', compact('provider', 'lastSync', 'syncStats', 'recentLogs'));
    }

    public function edit($id)
    {
        $provider = ApiProviderModel::with(['fieldMappings', 'conditions', 'schedules', 'promotionRules'])->findOrFail($id);
        return view('backend.pages.api-management.edit', compact('provider'));
    }

    public function update(Request $request, $id)
    {
        // Debug: Log incoming request data
        logger('=== API Management Update Debug ===');
        logger('Headers from request: ' . json_encode($request->input('headers', [])));
        logger('All request data: ' . json_encode($request->all()));
        
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:tb_api_providers,code,' . $id,
            'url' => 'required|url',
            'period_endpoint' => 'nullable|string',
            'tour_detail_endpoint' => 'nullable|string',
            'requires_multi_step' => 'boolean',
            'url_parameters' => 'nullable|array',
            'description' => 'nullable|string',
            'headers' => 'nullable|array',
            'config' => 'nullable|array',
            'pdf_header' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
            'pdf_footer' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
            'pdf_header_footer_enabled' => 'nullable|string|in:on,off',
        ]);

        DB::beginTransaction();
        try {
            $provider = ApiProviderModel::findOrFail($id);
            
            // Process headers array from form structure to associative array
            $headers = [];
            if ($request->has('headers') && is_array($request->input('headers'))) {
                logger('Processing headers array: ' . json_encode($request->input('headers')));
                foreach ($request->input('headers') as $index => $header) {
                    logger("Header $index: " . json_encode($header));
                    if (isset($header['key']) && isset($header['value']) && 
                        !empty(trim($header['key'])) && !empty(trim($header['value']))) {
                        $headers[trim($header['key'])] = trim($header['value']);
                        logger("Added header: " . trim($header['key']) . " = " . trim($header['value']));
                    }
                }
            }
            
            logger('Final headers array: ' . json_encode($headers));
            
            // Handle PDF Header file upload
            $pdfHeaderPath = $provider->pdf_header;
            if ($request->hasFile('pdf_header')) {
                // Delete old file if exists
                if ($provider->pdf_header && file_exists(public_path($provider->pdf_header))) {
                    unlink(public_path($provider->pdf_header));
                }
                
                $file = $request->file('pdf_header');
                $filename = 'pdf_header_' . $provider->code . '_' . time() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('upload/pdf_headers'), $filename);
                $pdfHeaderPath = 'upload/pdf_headers/' . $filename;
            } elseif ($request->input('remove_pdf_header') == '1' && $provider->pdf_header) {
                // Remove header if requested
                if (file_exists(public_path($provider->pdf_header))) {
                    unlink(public_path($provider->pdf_header));
                }
                $pdfHeaderPath = null;
            }
            
            // Handle PDF Footer file upload
            $pdfFooterPath = $provider->pdf_footer;
            if ($request->hasFile('pdf_footer')) {
                // Delete old file if exists
                if ($provider->pdf_footer && file_exists(public_path($provider->pdf_footer))) {
                    unlink(public_path($provider->pdf_footer));
                }
                
                $file = $request->file('pdf_footer');
                $filename = 'pdf_footer_' . $provider->code . '_' . time() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('upload/pdf_footers'), $filename);
                $pdfFooterPath = 'upload/pdf_footers/' . $filename;
            } elseif ($request->input('remove_pdf_footer') == '1' && $provider->pdf_footer) {
                // Remove footer if requested
                if (file_exists(public_path($provider->pdf_footer))) {
                    unlink(public_path($provider->pdf_footer));
                }
                $pdfFooterPath = null;
            }
            
            $updateResult = $provider->update([
                'name' => $request->name,
                'code' => $request->code,
                'url' => $request->url,
                'period_endpoint' => $request->period_endpoint,
                'tour_detail_endpoint' => $request->tour_detail_endpoint,
                'requires_multi_step' => $request->boolean('requires_multi_step'),
                'url_parameters' => $request->url_parameters ?? [],
                'headers' => $headers,
                'config' => $request->config ?? [],
                'description' => $request->description,
                'status' => $request->status ?? 'active',
                'pdf_header' => $pdfHeaderPath,
                'pdf_footer' => $pdfFooterPath,
                'pdf_header_footer_enabled' => $request->input('pdf_header_footer_enabled', 'off'),
            ]);
            
            logger('Update result: ' . ($updateResult ? 'success' : 'failed'));
            logger('Provider headers after update: ' . json_encode($provider->fresh()->headers));

            // อัปเดต Field Mappings
            if ($request->has('field_mappings') && is_array($request->field_mappings)) {
                // ลบ mappings เดิม
                $provider->fieldMappings()->delete();
                
                // สร้าง mappings ใหม่
                foreach ($request->field_mappings as $mapping) {
                    if (!empty($mapping['local_field'])) {
                        // กำหนด transformation_rules สำหรับ static values
                        $transformationRules = $mapping['transformation_rules'] ?? [];
                        
                        $apiField = $mapping['api_field'] ?? '';
                        $staticValue = $mapping['static_value'] ?? '';
                        
                        // ตรวจสอบว่า api_field ขึ้นต้นด้วย "static:" หรือไม่
                        if (strpos($apiField, 'static:') === 0) {
                            // แยก static value จาก api_field
                            $staticValue = substr($apiField, 7); // ตัด "static:" ออก
                            $apiField = ''; // clear api_field
                        }
                        
                        // ถ้ามี static_value หรือ static prefix ให้สร้าง transformation rules
                        if (!empty($staticValue)) {
                            $transformationRules = [
                                'type' => 'static_value',
                                'static_value' => $staticValue
                            ];
                            $apiField = ''; // ถ้าเป็น static value ให้ clear api_field
                        }
                        
                        ApiFieldMappingModel::create([
                            'api_provider_id' => $provider->id,
                            'field_type' => $mapping['field_type'] ?? 'tour',
                            'local_field' => $mapping['local_field'],
                            'api_field' => $apiField,
                            'data_type' => $mapping['data_type'] ?? 'string',
                            'transformation_rules' => $transformationRules,
                            'is_required' => $mapping['is_required'] ?? false
                        ]);
                    }
                }
            }

            // อัปเดต Promotion Rules
            if ($request->has('promotion_rules') && is_array($request->promotion_rules)) {
                // ลบ promotion rules เดิม
                $provider->promotionRules()->delete();
                
                // สร้าง promotion rules ใหม่
                foreach ($request->promotion_rules as $rule) {
                    if (!empty($rule['rule_name']) && !empty($rule['condition_field'])) {
                        ApiPromotionRuleModel::create([
                            'api_provider_id' => $provider->id,
                            'rule_name' => $rule['rule_name'],
                            'condition_field' => $rule['condition_field'],
                            'condition_operator' => $rule['condition_operator'],
                            'condition_value' => $rule['condition_value'],
                            'promotion_type' => $rule['promotion_type'],
                            'promotion1_value' => $rule['promotion1_value'] ?? 'N',
                            'promotion2_value' => $rule['promotion2_value'] ?? 'N',
                            'priority' => $rule['priority'] ?? 1,
                            'is_active' => $rule['is_active'] ?? false,
                            'description' => $rule['description'] ?? null
                        ]);
                    }
                }
            }

            // อัปเดต Conditions
            if ($request->has('conditions') && is_array($request->conditions)) {
                // ลบ conditions เดิม
                $provider->conditions()->delete();
                
                // สร้าง conditions ใหม่
                foreach ($request->conditions as $condition) {
                    if (!empty($condition['condition_type']) && !empty($condition['field_name'])) {
                        ApiConditionModel::create([
                            'api_provider_id' => $provider->id,
                            'condition_type' => $condition['condition_type'],
                            'field_name' => $condition['field_name'],
                            'operator' => $condition['operator'] ?? 'EXISTS',
                            'value' => $condition['value'] ?? null,
                            'action_type' => $condition['action_type'] ?? 'set_value',
                            'priority' => $condition['priority'] ?? 1,
                            'is_active' => $condition['is_active'] ?? false,
                            'condition_rules' => [], // จะเพิ่มภายหลัง
                            'action_rules' => [] // จะเพิ่มภายหลัง
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('api-management.show', $id)->with('success', 'API Provider updated successfully!');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error updating API Provider: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $provider = ApiProviderModel::findOrFail($id);
            $provider->delete();
            
            return redirect()->route('api-management.index')->with('success', 'API Provider deleted successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Error deleting API Provider: ' . $e->getMessage());
        }
    }

    public function toggleStatus($id)
    {
        try {
            $provider = ApiProviderModel::findOrFail($id);
            $provider->status = $provider->status === 'active' ? 'inactive' : 'active';
            $provider->save();

            return response()->json([
                'success' => true,
                'status' => $provider->status,
                'message' => 'Status updated successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating status: ' . $e->getMessage()
            ], 500);
        }
    }

    public function testConnection($id)
    {
        $provider = ApiProviderModel::findOrFail($id);
        
        $startTime = microtime(true);
        
        try {
            $headers = $provider->headers ?? [];
            
            // Special handling for Super Holiday (multi-category API)
            if ($provider->code === 'superbholiday' || $provider->code === 'superb_holiday') {
                return $this->testSuperHolidayConnection($provider, $startTime, $headers);
            }
            
            // Special handling for Best Consortium (multi-country API)
            if ($provider->code === 'bestconsortium' || $provider->code === 'best_consortium' || $provider->code === 'best') {
                return $this->testBestConsortiumConnection($provider, $startTime, $headers);
            }
            
            // Special handling for TTN ALL (multi-step API with complex period structure)
            if ($provider->code === 'ttn_all') {
                return $this->testTTNAllConnection($provider, $startTime, $headers);
            }
            
            // Special handling for Checkin Group (periods array in tour data)
            if ($provider->code === 'checkingroup' || $provider->code === 'checkin_group') {
                return $this->testCheckinGroupConnection($provider, $startTime, $headers);
            }
            
            // GO365/QeBooking requires POST with parameters to get all tours (default is only 20)
            if ($provider->code === 'go365' || $provider->code === 'qebooking') {
                $response = Http::withHeaders($headers)->timeout(30)->post($provider->url, [
                    "search" => "",
                    "country_id" => [],
                    "start_page" => "1",
                    "limit_page" => "300",
                    "date_start" => "",
                    "date_end" => "",
                    "sort" => "price_min"
                ]);
            } else {
                $response = Http::withHeaders($headers)->timeout(30)->get($provider->url);
            }
            
            $endTime = microtime(true);
            $responseTime = round(($endTime - $startTime), 3);
            $responseSize = strlen($response->body());
            
            // Count records and periods in API response
            $recordCount = 0;
            $periodCount = 0;
            $responseData = null;
            
            if ($response->successful()) {
                $responseData = $response->json();
                
                // Count records based on provider type
                if (isset($responseData['data']) && is_array($responseData['data'])) {
                    $recordCount = count($responseData['data']);
                } else {
                    $recordCount = is_array($responseData) ? count($responseData) : 0;
                }
                
                // Count periods based on provider configuration
                $config = is_string($provider->config) ? json_decode($provider->config, true) : ($provider->config ?? []);
                $hasMultiStepConfig = $provider->requires_multi_step || 
                                    !empty($provider->tour_detail_endpoint) ||
                                    !empty($config['period_url_pattern']) ||
                                    !empty($config['detail_url_pattern']);
                
                if ($hasMultiStepConfig && $recordCount > 0) {
                    // For multi-step APIs (GO365, TTN Japan, iTravel), test detail/period endpoints for period counting
                    $periodCount = $this->testMultiStepPeriods($provider, $responseData, $headers);
                } else {
                    // For single-step APIs, count periods directly from main response
                    $periodCount = $this->countPeriodsFromResponse($responseData, $provider);
                }
            }
            
            $testResult = ApiTestResultModel::create([
                'api_provider_id' => $provider->id,
                'test_type' => 'connection',
                'status' => $response->successful() ? 'success' : 'failed',
                'response_message' => $response->successful() ? 
                    "Connection successful - Records: {$recordCount}, Periods: {$periodCount}" : 
                    'Connection failed: ' . $response->status(),
                'response_data' => $responseData,
                'response_time' => $responseTime,
                'response_size' => $responseSize,
                'tested_at' => now()
            ]);

            // Prepare success message
            $message = $response->successful() ? 
                "Connection successful! Found {$recordCount} records with {$periodCount} periods total." : 
                'Connection failed!';
            
            // Add multi-step API specific note
            if ($response->successful() && $provider->requires_multi_step && $periodCount > 0) {
                $message .= " (Multi-step API: Period count estimated from sample data)";
            }
            
            return response()->json([
                'success' => $response->successful(),
                'status' => $response->status(),
                'response_time' => $responseTime,
                'response_size' => $responseSize,
                'record_count' => $recordCount,
                'period_count' => $periodCount,
                'data' => $responseData,
                'message' => $message,
                'provider_code' => $provider->code
            ]);

        } catch (\Exception $e) {
            $endTime = microtime(true);
            $responseTime = round(($endTime - $startTime), 3);
            
            ApiTestResultModel::create([
                'api_provider_id' => $provider->id,
                'test_type' => 'connection',
                'status' => 'failed',
                'response_message' => 'Connection error: ' . $e->getMessage(),
                'response_time' => $responseTime,
                'tested_at' => now()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Connection error: ' . $e->getMessage(),
                'response_time' => $responseTime
            ], 500);
        }
    }

    private function testMultiStepPeriods($provider, $responseData, $headers)
    {
        try {
            $periodCount = 0;
            $tours = [];
            
            // Get tours from response based on provider
            if ($provider->code === 'go365' || $provider->code === 'qebooking' || $provider->code === 'probookingcenter') {
                $tours = $responseData['data'] ?? [];
            } elseif ($provider->code === 'itravel' || $provider->code === 'itravels') {
                // iTravel returns {result: "success", data: [tours]}
                $tours = $responseData['data'] ?? [];
            } else {
                $tours = is_array($responseData) ? $responseData : [];
            }
            
            // Test detail/period endpoint for first few tours
            $testLimit = min(3, count($tours)); // Test max 3 tours
            $testedToursCount = 0;
            
            for ($i = 0; $i < $testLimit; $i++) {
                $tour = $tours[$i];
                
                if ($provider->code === 'go365' || $provider->code === 'qebooking') {
                    $tourId = $tour['tour_id'] ?? null;
                    if (!$tourId) continue;
                    
                    $detailUrl = str_replace('{tour_id}', $tourId, $provider->tour_detail_endpoint);
                    
                    $detailResponse = Http::withHeaders($headers)->timeout(10)->get($detailUrl);
                    
                    if ($detailResponse->successful()) {
                        $detailData = $detailResponse->json();
                        
                        if (isset($detailData['data'][0]['tour_period']) && is_array($detailData['data'][0]['tour_period'])) {
                            $periodCount += count($detailData['data'][0]['tour_period']);
                            $testedToursCount++;
                        }
                    }
                } elseif ($provider->code === 'probookingcenter') {
                    // ProBookingCenter: Use period endpoint to get all periods (only once)
                    if ($i === 0) {
                        $periodResponse = Http::withHeaders($headers)->timeout(10)->get($provider->period_endpoint);
                        
                        if ($periodResponse->successful()) {
                            $periodData = $periodResponse->json();
                            
                            if (isset($periodData['data']) && is_array($periodData['data'])) {
                                // Simply count all periods in the response
                                $periodCount = count($periodData['data']);
                                $testedToursCount = 1; // Mark as tested
                            }
                        }
                    }
                    // Exit loop after processing period endpoint
                    break;
                } elseif ($provider->code === 'itravel' || $provider->code === 'itravels') {
                    // iTravel: Detail endpoint returns {result: "success", data: [periods]}
                    $tourCode = $tour['code'] ?? null;
                    if (!$tourCode) continue;
                    
                    $config = is_string($provider->config) ? json_decode($provider->config, true) : ($provider->config ?? []);
                    $detailUrlPattern = $config['detail_url_pattern'] ?? '/api/program/{code}';
                    
                    // Build full URL
                    if (!str_starts_with($detailUrlPattern, 'http')) {
                        $baseUrl = rtrim($provider->url, '/');
                        $baseUrl = preg_replace('#/api/program$#', '', $baseUrl);
                        $detailUrlPattern = $baseUrl . $detailUrlPattern;
                    }
                    
                    $detailUrl = str_replace('{code}', $tourCode, $detailUrlPattern);
                    
                    $detailResponse = Http::withHeaders($headers)->timeout(10)->get($detailUrl);
                    
                    if ($detailResponse->successful()) {
                        $detailData = $detailResponse->json();
                        
                        // iTravel returns {result: "success", data: [periods]}
                        if (isset($detailData['data']) && is_array($detailData['data'])) {
                            $periodCount += count($detailData['data']);
                            $testedToursCount++;
                        }
                    }
                } else {
                    // For TTN Japan/All and other multi-step APIs
                    $tourId = null;
                    if (is_array($tour)) {
                        // TTN uses P_ID field
                        $tourId = $tour['P_ID'] ?? $tour['id'] ?? $tour['tour_id'] ?? null;
                    } elseif (is_numeric($tour)) {
                        $tourId = $tour;
                    }
                    if (!$tourId) continue;
                    
                    // Check if provider has period_url_pattern in config
                    $config = is_string($provider->config) ? json_decode($provider->config, true) : ($provider->config ?? []);
                    
                    if (!empty($config['period_url_pattern'])) {
                        // TTN Japan/All: Use period endpoint directly
                        $periodUrl = str_replace('{id}', $tourId, $config['period_url_pattern']);
                        $periodResponse = Http::withHeaders($headers)->timeout(10)->get($periodUrl);
                        
                        if ($periodResponse->successful()) {
                            $periodData = $periodResponse->json();
                            
                            if (is_array($periodData)) {
                                // TTN structure: Array of period objects
                                // Each object can have multiple formats:
                                
                                // Format 1: Direct period array (TTN Japan old structure)
                                if (isset($periodData[0]['P_ID']) || isset($periodData[0]['period_id'])) {
                                    $periodCount += count($periodData);
                                    $testedToursCount++;
                                }
                                // Format 2: Period groups with Price array (TTN All new structure)
                                elseif (isset($periodData[0]['Price']) && is_array($periodData[0]['Price'])) {
                                    foreach ($periodData as $periodGroup) {
                                        if (isset($periodGroup['Price']) && is_array($periodGroup['Price'])) {
                                            $periodCount += count($periodGroup['Price']);
                                        }
                                    }
                                    $testedToursCount++;
                                }
                                // Format 3: Try common period field names
                                else {
                                    $periodFieldNames = ['period', 'periods', 'Periods'];
                                    $foundPeriods = false;
                                    
                                    foreach ($periodFieldNames as $fieldName) {
                                        if (isset($periodData[$fieldName]) && is_array($periodData[$fieldName])) {
                                            $periodCount += count($periodData[$fieldName]);
                                            $foundPeriods = true;
                                            break;
                                        }
                                    }
                                    
                                    // Fallback: count array length
                                    if (!$foundPeriods) {
                                        $periodCount += count($periodData);
                                    }
                                    
                                    if ($foundPeriods || count($periodData) > 0) {
                                        $testedToursCount++;
                                    }
                                }
                            }
                        }
                    } else {
                        // Fallback to detail endpoint for other providers
                        $detailUrl = str_replace('{id}', $tourId, $provider->tour_detail_endpoint ?? '');
                        if (!$detailUrl) continue;
                        
                        $detailResponse = Http::withHeaders($headers)->timeout(10)->get($detailUrl);
                        
                        if ($detailResponse->successful()) {
                            $detailData = $detailResponse->json();
                            $counted = $this->countPeriodsFromResponse($detailData, $provider);
                            if ($counted > 0) {
                                $periodCount += $counted;
                                $testedToursCount++;
                            }
                        }
                    }
                }
            }
            
            // Estimate total periods based on sample (use testedToursCount not testLimit for accuracy)
            if ($testedToursCount > 0 && $periodCount > 0 && count($tours) > $testedToursCount) {
                $avgPeriodsPerTour = $periodCount / $testedToursCount;
                $estimatedTotal = round($avgPeriodsPerTour * count($tours));
                
                // Log::info('Estimating total periods from sample', [
                    // 'provider' => $provider->code,
                    // 'tested_tours' => $testedToursCount,
                    // 'periods_from_sample' => $periodCount,
                    // 'avg_periods_per_tour' => $avgPeriodsPerTour,
                    // 'total_tours' => count($tours),
                    // 'estimated_total_periods' => $estimatedTotal
                // ]);
                
                return $estimatedTotal;
            }
            
            // Log::info('Returning actual period count (no estimation)', [
                // 'provider' => $provider->code,
                // 'period_count' => $periodCount,
                // 'test_limit' => $testLimit,
                // 'tour_count' => count($tours)
            // ]);
            
            return $periodCount;
            
        } catch (\Exception $e) {
            // Log::warning('Error testing multi-step periods', [
                // 'provider' => $provider->code,
                // 'error' => $e->getMessage()
            // ]);
            return 0;
        }
    }

    private function countPeriodsFromResponse($responseData, $provider)
    {
        try {
            if (!is_array($responseData)) {
                return 0;
            }
            
            // Get period array field names based on field mappings
            $periodArrayFields = $this->identifyPeriodArrayFields($provider->id);
            
            $totalPeriods = 0;
            
            // Strategy 1: Check if responseData is an array of tours (common for Zego, Tour Factory)
            // Each tour has its own periods array
            $isArrayOfTours = false;
            if (array_keys($responseData) === range(0, count($responseData) - 1) && count($responseData) > 0) {
                $firstItem = $responseData[0];
                if (is_array($firstItem)) {
                    // Check if first item has period fields
                    foreach ($periodArrayFields as $fieldName) {
                        if (isset($firstItem[$fieldName]) && is_array($firstItem[$fieldName])) {
                            $isArrayOfTours = true;
                            break;
                        }
                    }
                }
            }
            
            if ($isArrayOfTours) {
                // Count periods from all tours
                foreach ($responseData as $tourData) {
                    if (is_array($tourData)) {
                        foreach ($periodArrayFields as $fieldName) {
                            if (isset($tourData[$fieldName]) && is_array($tourData[$fieldName])) {
                                $periodsArray = $tourData[$fieldName];
                                if (array_keys($periodsArray) === range(0, count($periodsArray) - 1)) {
                                    $totalPeriods += count($periodsArray);
                                    break; // Found periods for this tour, move to next tour
                                }
                            }
                        }
                    }
                }
                
                if ($totalPeriods > 0) {
                    // Log::info('Counted periods from array of tours', [
                        // 'provider' => $provider->code,
                        // 'tours_count' => count($responseData),
                        // 'total_periods' => $totalPeriods
                    // ]);
                    return $totalPeriods;
                }
            }
            
            // Strategy 2: Check if responseData has 'data' wrapper with array of tours
            if (isset($responseData['data']) && is_array($responseData['data'])) {
                $tours = $responseData['data'];
                if (array_keys($tours) === range(0, count($tours) - 1) && count($tours) > 0) {
                    foreach ($tours as $tourData) {
                        if (is_array($tourData)) {
                            foreach ($periodArrayFields as $fieldName) {
                                if (isset($tourData[$fieldName]) && is_array($tourData[$fieldName])) {
                                    $periodsArray = $tourData[$fieldName];
                                    if (array_keys($periodsArray) === range(0, count($periodsArray) - 1)) {
                                        $totalPeriods += count($periodsArray);
                                        break;
                                    }
                                }
                            }
                        }
                    }
                    
                    if ($totalPeriods > 0) {
                        // Log::info('Counted periods from data wrapper', [
                            // 'provider' => $provider->code,
                            // 'tours_count' => count($tours),
                            // 'total_periods' => $totalPeriods
                        // ]);
                        return $totalPeriods;
                    }
                }
            }
            
            // Strategy 3: Look for arrays that match period field patterns (top-level single tour)
            foreach ($periodArrayFields as $fieldName) {
                if (isset($responseData[$fieldName]) && is_array($responseData[$fieldName])) {
                    $array = $responseData[$fieldName];
                    
                    // Ensure it's a numerically indexed array (list of items, not associative)
                    if (array_keys($array) === range(0, count($array) - 1)) {
                        return count($array);
                    }
                }
            }
            
            // Strategy 4: Look for nested arrays (e.g., data.schedules, result.periods)
            foreach ($responseData as $topKey => $topValue) {
                if (is_array($topValue)) {
                    foreach ($periodArrayFields as $fieldName) {
                        if (isset($topValue[$fieldName]) && is_array($topValue[$fieldName])) {
                            $array = $topValue[$fieldName];
                            
                            // Ensure it's a numerically indexed array
                            if (array_keys($array) === range(0, count($array) - 1)) {
                                return count($array);
                            }
                        }
                    }
                }
            }
            
        } catch (\Exception $e) {
            // Log::warning('Error counting periods from response', [
                // 'provider_id' => $provider->id,
                // 'provider_code' => $provider->code,
                // 'error' => $e->getMessage()
            // ]);
        }
        
        return 0;
    }
    
    private function identifyPeriodArrayFields($providerId)
    {
        // Get period field mappings for this provider
        $periodMappings = DB::table('tb_api_field_mappings')
            ->where('api_provider_id', $providerId)
            ->where('field_type', 'period')
            ->get();
        
        $periodArrayFields = [];
        
        if ($periodMappings->isNotEmpty()) {
            foreach ($periodMappings as $mapping) {
                $apiField = $mapping->api_field;
                
                // Extract parent array field from various patterns
                $parentField = $this->extractParentArrayField($apiField);
                
                if ($parentField && !in_array($parentField, $periodArrayFields)) {
                    $periodArrayFields[] = $parentField;
                }
            }
        }
        
        // If no period array fields found from mappings, use common patterns
        if (empty($periodArrayFields)) {
            $periodArrayFields = [
                'Periods', 'periods', 'period', 'PERIOD', 
                'departure_dates', 'schedules', 'dates',
                'availability', 'departures', 'program_periods'
            ];
        }
        
        return $periodArrayFields;
    }
    
    private function extractParentArrayField($apiField)
    {
        // Handle different API field patterns:
        // "Periods[].PeriodID" -> "Periods"
        // "periods.0.id" -> "periods" 
        // "data.periods[].start_date" -> "periods"
        // "PR_PERIODS" -> null (not nested)
        
        if (strpos($apiField, '[].') !== false) {
            // Pattern: "Periods[].PeriodID"
            return explode('[].', $apiField)[0];
        }
        
        if (strpos($apiField, '.') !== false) {
            $parts = explode('.', $apiField);
            
            // Look for numeric indices indicating array access
            foreach ($parts as $i => $part) {
                if (is_numeric($part) && $i > 0) {
                    // Previous part is likely the array name
                    return $parts[$i - 1];
                }
            }
            
            // For simple dot notation like "periods.id", check if first part looks like array name
            if (count($parts) >= 2) {
                $firstPart = $parts[0];
                // Common array indicators
                if (in_array(strtolower($firstPart), ['periods', 'period', 'departures', 'schedules', 'dates'])) {
                    return $firstPart;
                }
            }
        }
        
        return null; // Not a nested field
    }

    private function testSuperHolidayConnection($provider, $startTime, $headers)
    {
        try {
            // Super Holiday: Test all category IDs (same as full sync)
            $testCategoryIds = [21, 29, 28, 23, 25, 24, 18, 2, 3, 17, 1, 19];
            $allRecords = [];
            $responseSize = 0;
            
            foreach ($testCategoryIds as $catId) {
                $url = $provider->url . '?id=' . $catId;
                
                try {
                    $response = Http::withHeaders($headers)->timeout(10)->get($url);
                    
                    if ($response->successful()) {
                        $data = $response->json();
                        $responseSize += strlen($response->body());
                        
                        if (is_array($data)) {
                            $allRecords = array_merge($allRecords, $data);
                        }
                    }
                } catch (\Exception $e) {
                    // Log::warning("Super Holiday test: Category {$catId} failed", [
                        // 'error' => $e->getMessage()
                    // ]);
                }
            }
            
            // Super Holiday: Group records by mainid (1 tour = multiple periods)
            // Each record in API response is 1 period, not 1 tour!
            $tourGroups = [];
            foreach ($allRecords as $record) {
                $mainid = $record['mainid'] ?? null;
                if ($mainid) {
                    if (!isset($tourGroups[$mainid])) {
                        $tourGroups[$mainid] = [];
                    }
                    $tourGroups[$mainid][] = $record;
                }
            }
            
            $totalRecords = count($allRecords);
            $totalTours = count($tourGroups);
            $totalPeriods = $totalRecords; // Each record is 1 periodหหหห
            
            $endTime = microtime(true);
            $responseTime = round(($endTime - $startTime), 3);
            
            $message = "Connection successful! Found {$totalRecords} period records from {$totalTours} unique tours (all 12 categories).";
            
            ApiTestResultModel::create([
                'api_provider_id' => $provider->id,
                'test_type' => 'connection',
                'status' => 'success',
                'response_message' => $message,
                'response_time' => $responseTime,
                'response_size' => $responseSize,
                'tested_at' => now()
            ]);
            
            return response()->json([
                'success' => true,
                'status' => 200,
                'response_time' => $responseTime,
                'response_size' => $responseSize,
                'record_count' => $totalTours, // Unique tours (what UI expects)
                'period_count' => $totalPeriods, // Total period records
                'data' => true,
                'message' => $message,
                'provider_code' => $provider->code
            ]);
            
        } catch (\Exception $e) {
            $endTime = microtime(true);
            $responseTime = round(($endTime - $startTime), 3);
            
            return response()->json([
                'success' => false,
                'message' => 'Connection error: ' . $e->getMessage(),
                'response_time' => $responseTime
            ], 500);
        }
    }

    private function testBestConsortiumConnection($provider, $startTime, $headers)
    {
        try {
            // Best Consortium: Get all countries then fetch tours from each country
            // Step 1: Get countries list
            $countriesUrl = 'https://api.best-consortium.com/v1/series/country';
            $countriesResponse = Http::withHeaders($headers)->timeout(10)->get($countriesUrl);
            
            if (!$countriesResponse->successful()) {
                $endTime = microtime(true);
                $responseTime = round(($endTime - $startTime), 3);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to fetch countries: ' . $countriesResponse->status(),
                    'response_time' => $responseTime
                ], 500);
            }
            
            $countries = $countriesResponse->json();
            if (!is_array($countries) || count($countries) === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No countries found in API response',
                    'response_time' => 0
                ], 500);
            }
            
            // Step 2: Loop through all countries and count tours/periods
            $totalRecords = 0;
            $totalPeriods = 0;
            $responseSize = strlen($countriesResponse->body());
            $processedCountries = 0;
            
            foreach ($countries as $country) {
                $countryId = $country['id'] ?? null;
                if (!$countryId) continue;
                
                $url = str_replace('{countryId}', $countryId, $provider->url);
                
                try {
                    $response = Http::withHeaders($headers)->timeout(10)->get($url);
                    
                    if ($response->successful()) {
                        $data = $response->json();
                        $responseSize += strlen($response->body());
                        
                        if (is_array($data)) {
                            $tourCount = count($data);
                            $totalRecords += $tourCount;
                            
                            // Count periods from tours
                            foreach ($data as $tour) {
                                // Try common period field names
                                $periodFields = ['periods', 'Periods', 'period', 'dates', 'departures'];
                                $periodsFound = false;
                                
                                foreach ($periodFields as $periodField) {
                                    if (isset($tour[$periodField]) && is_array($tour[$periodField])) {
                                        $totalPeriods += count($tour[$periodField]);
                                        $periodsFound = true;
                                        break;
                                    }
                                }
                                
                                // If no period array found, estimate based on typical Best Consortium structure
                                if (!$periodsFound) {
                                    // Best typically has 3-10 periods per tour
                                    $totalPeriods += 6; // Average estimate
                                }
                            }
                            
                            $processedCountries++;
                        }
                    }
                } catch (\Exception $e) {
                    // Log::warning("Best Consortium test: Country {$countryId} failed", [
                        // 'error' => $e->getMessage()
                    // ]);
                }
            }
            
            $endTime = microtime(true);
            $responseTime = round(($endTime - $startTime), 3);
            
            $message = "Connection successful! Found {$totalRecords} tours with {$totalPeriods} periods from {$processedCountries} countries.";
            
            ApiTestResultModel::create([
                'api_provider_id' => $provider->id,
                'test_type' => 'connection',
                'status' => 'success',
                'response_message' => $message,
                'response_time' => $responseTime,
                'response_size' => $responseSize,
                'tested_at' => now()
            ]);
            
            return response()->json([
                'success' => true,
                'status' => 200,
                'response_time' => $responseTime,
                'response_size' => $responseSize,
                'record_count' => $totalRecords,
                'period_count' => $totalPeriods,
                'data' => true,
                'message' => $message,
                'provider_code' => $provider->code
            ]);
            
        } catch (\Exception $e) {
            $endTime = microtime(true);
            $responseTime = round(($endTime - $startTime), 3);
            
            return response()->json([
                'success' => false,
                'message' => 'Connection error: ' . $e->getMessage(),
                'response_time' => $responseTime
            ], 500);
        }
    }

    private function testTTNAllConnection($provider, $startTime, $headers)
    {
        try {
            // TTN ALL: Main endpoint returns list of tours
            $response = Http::withHeaders($headers)->timeout(30)->get($provider->url);
            
            if (!$response->successful()) {
                $endTime = microtime(true);
                $responseTime = round(($endTime - $startTime), 3);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to fetch tours: ' . $response->status(),
                    'response_time' => $responseTime
                ], 500);
            }
            
            $tours = $response->json();
            $totalRecords = is_array($tours) ? count($tours) : 0;
            $responseSize = strlen($response->body());
            
            if ($totalRecords === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tours found in API response',
                    'response_time' => 0
                ], 500);
            }
            
            // Get period URL pattern from config
            $config = is_string($provider->config) ? json_decode($provider->config, true) : ($provider->config ?? []);
            $periodUrlPattern = $config['period_url_pattern'] ?? $provider->tour_detail_endpoint ?? null;
            
            if (!$periodUrlPattern) {
                // Fallback: Try to construct from known TTN ALL pattern
                // TTN ALL period endpoint: https://www.ttnplus.co.th/api/program/{id}
                if (strpos($provider->url, 'ttnplus.co.th') !== false) {
                    $periodUrlPattern = 'https://www.ttnplus.co.th/api/program/{id}';
                }
            }
            
            if (!$periodUrlPattern) {
                return response()->json([
                    'success' => false,
                    'message' => 'TTN ALL: period_url_pattern not configured in provider settings',
                    'response_time' => 0
                ], 500);
            }
            
            // Test periods from first 5 tours
            $totalPeriods = 0;
            $testLimit = min(5, $totalRecords);
            
            for ($i = 0; $i < $testLimit; $i++) {
                $tour = $tours[$i];
                $tourId = $tour['P_ID'] ?? null;
                
                if (!$tourId) continue;
                
                $periodUrl = str_replace('{id}', $tourId, $periodUrlPattern);
                
                try {
                    $periodResponse = Http::withHeaders($headers)->timeout(10)->get($periodUrl);
                    
                    if ($periodResponse->successful()) {
                        $periodData = $periodResponse->json();
                        
                        if (is_array($periodData) && !empty($periodData)) {
                            // Check different period structures
                            $firstItem = $periodData[0];
                            
                            // Format 1: Direct period array (has P_DUE_START field)
                            if (isset($firstItem['P_DUE_START']) || isset($firstItem['P_ID'])) {
                                $totalPeriods += count($periodData);
                            }
                            // Format 2: Period groups with Price array
                            elseif (isset($firstItem['Price']) && is_array($firstItem['Price'])) {
                                foreach ($periodData as $group) {
                                    if (isset($group['Price']) && is_array($group['Price'])) {
                                        $totalPeriods += count($group['Price']);
                                    }
                                }
                            }
                            // Format 3: Fallback - count array length
                            else {
                                $totalPeriods += count($periodData);
                            }
                        }
                    }
                } catch (\Exception $e) {
                    // Log::warning("TTN ALL test: Failed to get periods for tour {$tourId}", [
                        // 'error' => $e->getMessage()
                    // ]);
                }
            }
            
            $endTime = microtime(true);
            $responseTime = round(($endTime - $startTime), 3);
            
            // Estimate total periods
            $estimatedPeriods = 0;
            if ($testLimit > 0 && $totalPeriods > 0) {
                $avgPeriodsPerTour = $totalPeriods / $testLimit;
                $estimatedPeriods = round($avgPeriodsPerTour * $totalRecords);
            }
            
            $message = "Connection successful! Found {$totalRecords} tours with estimated {$estimatedPeriods} periods (sampled {$testLimit} tours with {$totalPeriods} periods).";
            
            ApiTestResultModel::create([
                'api_provider_id' => $provider->id,
                'test_type' => 'connection',
                'status' => 'success',
                'response_message' => $message,
                'response_time' => $responseTime,
                'response_size' => $responseSize,
                'tested_at' => now()
            ]);
            
            return response()->json([
                'success' => true,
                'status' => 200,
                'response_time' => $responseTime,
                'response_size' => $responseSize,
                'record_count' => $totalRecords,
                'period_count' => $estimatedPeriods,
                'data' => true,
                'message' => $message,
                'provider_code' => $provider->code
            ]);
            
        } catch (\Exception $e) {
            $endTime = microtime(true);
            $responseTime = round(($endTime - $startTime), 3);
            
            return response()->json([
                'success' => false,
                'message' => 'Connection error: ' . $e->getMessage(),
                'response_time' => $responseTime
            ], 500);
        }
    }

    private function testCheckinGroupConnection($provider, $startTime, $headers)
    {
        try {
            // Checkin Group: Main endpoint returns tours with periods array
            $response = Http::withHeaders($headers)->timeout(30)->get($provider->url);
            
            if (!$response->successful()) {
                $endTime = microtime(true);
                $responseTime = round(($endTime - $startTime), 3);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to fetch tours: ' . $response->status(),
                    'response_time' => $responseTime
                ], 500);
            }
            
            $tours = $response->json();
            $totalRecords = is_array($tours) ? count($tours) : 0;
            $responseSize = strlen($response->body());
            
            if ($totalRecords === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tours found in API response',
                    'response_time' => 0
                ], 500);
            }
            
            // Count periods from all tours
            $totalPeriods = 0;
            foreach ($tours as $tour) {
                if (isset($tour['periods']) && is_array($tour['periods'])) {
                    $totalPeriods += count($tour['periods']);
                }
            }
            
            $endTime = microtime(true);
            $responseTime = round(($endTime - $startTime), 3);
            
            $message = "Connection successful! Found {$totalRecords} tours with {$totalPeriods} periods total.";
            
            ApiTestResultModel::create([
                'api_provider_id' => $provider->id,
                'test_type' => 'connection',
                'status' => 'success',
                'response_message' => $message,
                'response_time' => $responseTime,
                'response_size' => $responseSize,
                'tested_at' => now()
            ]);
            
            return response()->json([
                'success' => true,
                'status' => 200,
                'response_time' => $responseTime,
                'response_size' => $responseSize,
                'record_count' => $totalRecords,
                'period_count' => $totalPeriods,
                'data' => true,
                'message' => $message,
                'provider_code' => $provider->code
            ]);
            
        } catch (\Exception $e) {
            $endTime = microtime(true);
            $responseTime = round(($endTime - $startTime), 3);
            
            return response()->json([
                'success' => false,
                'message' => 'Connection error: ' . $e->getMessage(),
                'response_time' => $responseTime
            ], 500);
        }
    }

    public function syncManualImmediate($id)
    {
        $provider = ApiProviderModel::with(['fieldMappings', 'conditions'])->findOrFail($id);
        
        if ($provider->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'API Provider is not active!'
            ], 400);
        }

        try {
            // Log::emergency('IMMEDIATE ZEGO SYNC: Starting immediate manual sync for provider: ' . $provider->code);
            
            // ทำ immediate sync แทนการสร้าง schedule
            $syncResult = $this->performSync($provider, 'manual', 0);
            
            return response()->json([
                'success' => true,
                'message' => 'Immediate sync completed!',
                'sync_log_id' => $syncResult['log_id'],
                'summary' => $syncResult['summary']
            ]);
        } catch (\Exception $e) {
            // Log::error('Immediate sync failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Sync error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function syncManualTemp($id)
    {
        $provider = ApiProviderModel::with(['fieldMappings', 'conditions'])->findOrFail($id);
        
        if ($provider->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'API Provider is not active!'
            ], 400);
        }

        try {
            $now = now();
            $nextRun = $now->copy()->addMinutes(1); // รันใน 1 นาที
            
            // สร้าง temporary schedule
            $schedule = new ApiScheduleModel();
            $schedule->api_provider_id = $provider->id;
            $schedule->name = 'Manual Sync (Temp)';
            $schedule->frequency = 'once';
            $schedule->run_time = $nextRun->format('H:i:s');
            $schedule->is_active = 1;
            $schedule->is_temp = 1; // Flag สำหรับ temporary schedule
            $schedule->sync_limit = null; // No limit - sync all records
            $schedule->next_run_at = $nextRun;
            $schedule->created_at = $now;
            $schedule->updated_at = $now;
            $schedule->save();
            
            // Log::info('Temporary schedule created for manual sync', [
                // 'provider_id' => $provider->id,
                // 'schedule_id' => $schedule->id,
                // 'next_run_at' => $nextRun->format('Y-m-d H:i:s')
            // ]);
            
            return response()->json([
                'success' => true,
                'schedule_id' => $schedule->id,
                'next_run_at' => $nextRun->format('d/m/Y H:i:s'),
                'sync_limit' => $schedule->sync_limit,
                'message' => 'Temporary schedule created successfully!'
            ]);
        } catch (\Exception $e) {
            // Log::error('Failed to create temporary schedule: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error creating schedule: ' . $e->getMessage()
            ], 500);
        }
    }

    public function syncManual($id, $limit = null)
    {
        $provider = ApiProviderModel::with(['fieldMappings', 'conditions'])->findOrFail($id);
        
        if ($provider->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'API Provider is not active!'
            ], 400);
        }

        try {
            $syncResult = $this->performSync($provider, 'manual', $limit);
            
            return response()->json([
                'success' => true,
                'sync_log_id' => $syncResult['log_id'],
                'message' => 'Manual sync completed successfully!',
                'summary' => $syncResult['summary']
            ]);
        } catch (\Exception $e) {
            // Log::error('Manual sync error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Sync error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function syncFromProvider(Request $request)
    {
        $providerCode = $request->get('provider');
        $limit = (int)$request->get('limit', 0);
        
        $provider = ApiProviderModel::where('code', $providerCode)->first();
        
        if (!$provider) {
            return response()->json([
                'success' => false,
                'message' => 'Provider not found'
            ], 404);
        }
        
        if ($provider->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'API Provider is not active!'
            ], 400);
        }

        try {
            $syncResult = $this->performSync($provider, 'manual', $limit);
            
            return response()->json([
                'success' => true,
                'sync_log_id' => $syncResult['log_id'],
                'message' => 'Sync completed successfully!',
                'summary' => $syncResult['summary']
            ]);
        } catch (\Exception $e) {
            // Log::error('Sync error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Sync error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function performSync($provider, $syncType = 'manual', $limit = 0)
    {
        // Special memory management for TTN_ALL
        if ($provider->code === 'ttn_all') {
            ini_set('memory_limit', '4G');
            ini_set('max_execution_time', 3600); // 1 hour
            Log::info('TTN_ALL sync: Increased memory and execution limits');
        }
        
        // สร้าง sync log
        $syncLog = ApiSyncLogModel::create([
            'api_provider_id' => $provider->id,
            'sync_type' => $syncType,
            'status' => 'running',
            'started_at' => now()
        ]);

        try {
            // Best Consortium: Special multi-country sync (เหมือน headcode)
            if ($provider->code === 'bestconsortium' || $provider->code === 'best_consortium') {
                return $this->performBestConsortiumSync($provider, $syncLog, $limit);
            }
            
            // Super Holiday: Special multi-category sync (เหมือน headcode)
            if ($provider->code === 'superbholiday' || $provider->code === 'superb_holiday') {
                return $this->performSuperHolidaySync($provider, $syncLog, $limit);
            }
            
            // Check if this is a multi-step API (like TTN Japan or GO365)
            $config = $provider->config ?? [];
            if ($provider->requires_multi_step || 
                !empty($provider->tour_detail_endpoint) ||
                !empty($config['detail_url_pattern']) || 
                !empty($config['period_url_pattern'])) {
                return $this->performMultiStepSync($provider, $syncLog, $limit);
            }

            // Standard single-step API sync
            $headers = [];
            if (!empty($provider->headers)) {
                $headers = is_string($provider->headers) ? json_decode($provider->headers, true) : $provider->headers;
                $headers = $headers ?? [];
            }
            
            // Log::info('Making API request', [
                // 'provider' => $provider->name,
                // 'url' => $provider->url,
                // 'headers' => $headers
            // ]);
            
            $response = Http::withHeaders($headers)->timeout(120)->get($provider->url);
            
            // Log::info('API response received', [
                // 'provider' => $provider->name,
                // 'status' => $response->status(),
                // 'headers' => $response->headers(),
                // 'body_length' => strlen($response->body()),
                // 'first_100_chars' => substr($response->body(), 0, 100)
            // ]);
            
            if (!$response->successful()) {
                throw new \Exception('API request failed with status: ' . $response->status());
            }

            $apiData = $response->json();
            // Log::info('API data parsed - raw response', [
                // 'provider' => $provider->name,
                // 'data_type' => gettype($apiData),
                // 'is_array' => is_array($apiData),
                // 'raw_structure' => is_array($apiData) ? array_keys($apiData) : 'N/A',
                // 'sample_data' => is_array($apiData) ? array_slice($apiData, 0, 3, true) : $apiData
            // ]);
            
            // Handle different API response structures
            $toursData = $apiData;
            if (is_array($apiData)) {
                // Check if this is a wrapped response (like GO365: {status, count, data})
                if (isset($apiData['data']) && is_array($apiData['data'])) {
                    $toursData = $apiData['data'];
                    // Log::info('Extracted tours from wrapped response', [
                        // 'provider' => $provider->name,
                        // 'original_keys' => array_keys($apiData),
                        // 'tours_count' => count($toursData)
                    // ]);
                }
                // Check if this is already a direct array of tours
                elseif (count($apiData) > 0 && isset($apiData[0]) && is_array($apiData[0])) {
                    // Already a direct array of tours
                    $toursData = $apiData;
                }
                // Handle empty or invalid responses
                else {
                    $toursData = [];
                }
            } elseif ($apiData === null || $apiData === '') {
                // Handle NULL or empty responses
                // Log::warning('API returned NULL or empty response', [
                    // 'provider' => $provider->name,
                    // 'url' => $provider->url,
                    // 'response_type' => gettype($apiData)
                // ]);
                $toursData = [];
            } else {
                throw new \Exception('Invalid API response: Expected array or object but received ' . gettype($apiData));
            }
            
            // Apply limit if specified
            if ($limit > 0 && is_array($toursData) && count($toursData) > $limit) {
                $toursData = array_slice($toursData, 0, $limit);
                // Log::info('Applied limit to tours data', [
                    // 'provider' => $provider->name,
                    // 'original_count' => $totalRecords ?? 0,
                    // 'limited_count' => count($toursData),
                    // 'limit' => $limit
                // ]);
            }
            
            $totalRecords = is_array($toursData) ? count($toursData) : 0;
            
            // Log::info('Final tours data structure', [
                // 'provider' => $provider->name,
                // 'tours_count' => $totalRecords,
                // 'first_tour_sample' => $totalRecords > 0 ? array_keys($toursData[0]) : 'N/A',
                // 'limit_applied' => $limit > 0 ? $limit : 'no limit'
            // ]);
            
            $createdTours = 0;
            $updatedTours = 0;
            $duplicatedTours = 0;
            $createdPeriods = 0;
            $updatedPeriods = 0;
            $errorCount = 0;
            $errors = [];

            if ($totalRecords > 0) {
                foreach ($toursData as $index => $tourData) {
                    try {
                        $currentIndex = $index + 1;
                        // Log::info("Processing tour {$currentIndex}/{$totalRecords}", [
                            // 'provider' => $provider->code,
                            // 'tour_index' => $currentIndex
                        // ]);
                        
                        $result = $this->processTourData($provider, $tourData, $syncLog);
                        
                        // Debug log
                        // Log::info("Tour processed result", [
                            // 'provider' => $provider->code,
                            // 'action' => $result['action'] ?? 'unknown',
                            // 'periods_count' => $result['periods_count'] ?? 0,
                            // 'tour_index' => $currentIndex
                        // ]);
                        
                        if ($result['action'] === 'created') {
                            $createdTours++;
                            $createdPeriods += $result['periods_count'] ?? 0;
                        } elseif ($result['action'] === 'updated') {
                            $updatedTours++;
                            $updatedPeriods += $result['periods_count'] ?? 0;
                        } elseif ($result['action'] === 'duplicated') {
                            $duplicatedTours++;
                            // Duplicated tours มี periods ใหม่ที่ถูกสร้าง ให้นับเป็น created
                            $createdPeriods += $result['periods_count'] ?? 0;
                        }
                        
                    } catch (\Exception $e) {
                        $errorCount++;
                        $errors[] = $e->getMessage();
                        $currentIndex = $index + 1;
                        // Log::error("Error processing tour {$currentIndex}: " . $e->getMessage());
                    }
                }
            }

            // Soft delete logic for Zego and Tour Factory (headcode behavior)
            $apiTypesWithSoftDelete = ['zego', 'tourfactory', 'tour_factory'];
            
            if (in_array($provider->code, $apiTypesWithSoftDelete)) {
                $syncedTourIds = [];
                $syncedTourApiIds = [];
                $syncedPeriodIds = [];
                $syncedPeriodApiIds = [];
                
                $apiType = $provider->code === 'tour_factory' ? 'tourfactory' : $provider->code;
                
                $syncedTours = \App\Models\Backend\TourModel::where('api_type', $apiType)
                    ->whereNull('deleted_at')
                    ->get(['id', 'api_id']);
                    
                foreach ($syncedTours as $tour) {
                    $syncedTourIds[] = $tour->id;
                    if ($tour->api_id) {
                        $syncedTourApiIds[] = $tour->api_id;
                    }
                    
                    $periods = \App\Models\Backend\TourPeriodModel::where('tour_id', $tour->id)
                        ->where('api_type', $apiType)
                        ->whereNull('deleted_at')
                        ->get(['id', 'period_api_id']);
                        
                    foreach ($periods as $period) {
                        $syncedPeriodIds[] = $period->id;
                        if ($period->period_api_id) {
                            $syncedPeriodApiIds[] = $period->period_api_id;
                        }
                    }
                }
                
                if (!empty($syncedTourIds) && !empty($syncedTourApiIds)) {
                    \App\Models\Backend\TourModel::whereNotIn('id', $syncedTourIds)
                        ->whereNotIn('api_id', $syncedTourApiIds)
                        ->where('api_type', $apiType)
                        ->whereNull('deleted_at')
                        ->update(['deleted_at' => date('Y-m-d H:i:s')]);
                        
                    \App\Models\Backend\TourModel::whereIn('id', $syncedTourIds)
                        ->whereIn('api_id', $syncedTourApiIds)
                        ->where('api_type', $apiType)
                        ->update(['deleted_at' => null]);
                        
                    // Log::info($provider->code . ' tour soft delete completed', [
                        // 'synced_tours' => count($syncedTourIds)
                    // ]);
                }
                
                if (!empty($syncedPeriodIds) && !empty($syncedPeriodApiIds)) {
                    \App\Models\Backend\TourPeriodModel::whereNotIn('id', $syncedPeriodIds)
                        ->whereNotIn('period_api_id', $syncedPeriodApiIds)
                        ->where('api_type', $apiType)
                        ->whereNull('deleted_at')
                        ->update(['deleted_at' => date('Y-m-d H:i:s')]);
                        
                    \App\Models\Backend\TourPeriodModel::whereIn('id', $syncedPeriodIds)
                        ->whereIn('period_api_id', $syncedPeriodApiIds)
                        ->where('api_type', $apiType)
                        ->update(['deleted_at' => null]);
                        
                    // Log::info($provider->code . ' period soft delete completed', [
                        // 'synced_periods' => count($syncedPeriodIds)
                    // ]);
                }
            }

            // อัปเดตข้อมูลการซิงค์ลงในฐานข้อมูล
            // Log::info('Updating sync log with final counts', [
                // 'provider' => $provider->code,
                // 'createdTours' => $createdTours,
                // 'duplicatedTours' => $duplicatedTours,
                // 'createdPeriods' => $createdPeriods,
                // 'updatedPeriods' => $updatedPeriods,
                // 'totalRecords' => $totalRecords
            // ]);
            
            // Force save with direct DB update
            DB::table('tb_api_sync_logs')
                ->where('id', $syncLog->id)
                ->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'total_records' => $totalRecords,
                    'created_tours' => $createdTours,
                    'updated_tours' => $updatedTours,
                    'created_periods' => $createdPeriods,
                    'updated_periods' => $updatedPeriods,
                    'duplicated_tours' => $duplicatedTours,
                    'error_count' => $errorCount,
                    'error_message' => $errorCount > 0 ? implode('; ', array_slice($errors, 0, 5)) : null,
                    'updated_at' => now()
                ]);
            
            // Log::info('Sync log FORCE updated via DB::table', [
                // 'log_id' => $syncLog->id,
                // 'created_periods' => $createdPeriods,
                // 'updated_periods' => $updatedPeriods
            // ]);

            return [
                'log_id' => $syncLog->id,
                'summary' => [
                    'total_records' => $totalRecords,
                    'created_tours' => $createdTours,
                    'created_periods' => $createdPeriods,
                    'updated_periods' => $updatedPeriods,
                    'duplicated_tours' => $duplicatedTours,
                    'error_count' => $errorCount
                ]
            ];

        } catch (\Exception $e) {
            DB::table('tb_api_sync_logs')
                ->where('id', $syncLog->id)
                ->update([
                    'status' => 'failed',
                    'completed_at' => now(),
                    'error_message' => $e->getMessage(),
                    'updated_at' => now()
                ]);
            
            throw $e;
        }
    }

    private function performMultiStepSync($provider, $syncLog, $limit = 0)
    {
        Log::info('Starting multi-step sync', ['provider' => $provider->name, 'limit' => $limit]);
        
        try {
            // Update sync log with progress
            $syncLog->update(['status' => 'running', 'updated_at' => now()]);
            
            // Step 1: Get list of IDs from main URL with extended timeout for TTN_ALL
            $headers = $provider->headers ?? [];
            $timeout = ($provider->code === 'ttn_all') ? 180 : 120; // 3 minutes for TTN_ALL
            
            Log::info('Fetching program list', ['provider' => $provider->code, 'timeout' => $timeout]);
            
            // GO365/QeBooking requires POST with parameters to get all tours (default is only 20)
            if ($provider->code === 'go365' || $provider->code === 'qebooking') {
                $response = Http::withHeaders($headers)->timeout($timeout)->post($provider->url, [
                    "search" => "",
                    "country_id" => [],
                    "start_page" => "1",
                    "limit_page" => "300",
                    "date_start" => "",
                    "date_end" => "",
                    "sort" => "price_min"
                ]);
            } elseif ($provider->code === 'ttn_all') {
                // TTN_ALL needs specific parameters
                $config = $provider->config ?? [];
                $url = $provider->url;
                if (isset($config['wholesale_id']) && isset($config['group_id'])) {
                    $url .= '?wholesale_id=' . $config['wholesale_id'] . '&group_id=' . $config['group_id'];
                }
                $response = Http::withHeaders($headers)->timeout($timeout)->get($url);
            } else {
                $response = Http::withHeaders($headers)->timeout($timeout)->get($provider->url);
            }
            
            if (!$response->successful()) {
                $errorMsg = 'Failed to get program IDs from ' . $provider->code . ': ' . $response->status();
                Log::error($errorMsg, ['response_body' => substr($response->body(), 0, 500)]);
                throw new \Exception($errorMsg);
            }
            
            Log::info('Program list fetched successfully', [
                'provider' => $provider->code,
                'response_size' => strlen($response->body()),
                'status' => $response->status()
            ]);
        
        $responseData = $response->json();
        
        // Handle different API response structures
        if ($provider->code === 'go365' || $provider->code === 'qebooking' || $provider->code === 'probookingcenter') {
            // GO365/QeBooking/ProBookingCenter returns: {"success": true, "code": 200, "data": [tours], "total_rows": 337}
            $programIds = $responseData['data'] ?? [];
            if (!is_array($programIds)) {
                throw new \Exception($provider->name . ' API: Expected data array not found');
            }
        } elseif ($provider->code === 'itravels' || $provider->code === 'itravel') {
            // iTravels returns: {"result": "success", "data": [tours]}
            $programIds = $responseData['data'] ?? [];
            if (!is_array($programIds)) {
                throw new \Exception('iTravels API: Expected data array not found. Got: ' . gettype($responseData['data'] ?? null));
            }
        } else {
            // TTN returns array directly
            $programIds = $responseData;
        }
        
        // Log::info('Multi-step sync: Found programs', [
            // 'provider' => $provider->name, 
            // 'count' => count($programIds)
        // ]);
        
        $createdTours = 0;
        $duplicatedTours = 0;
        $skippedTours = 0;
        $createdPeriods = 0;
        $updatedPeriods = 0;
        $errorCount = 0;
        $errors = [];
        $processedCount = 0;
        
        // Apply limit to program IDs if specified
        if ($limit > 0 && count($programIds) > $limit) {
            $programIds = array_slice($programIds, 0, $limit);
        }
        
        $config = $provider->config ?? [];
        
        $totalPrograms = count($programIds);
        Log::info('Starting to process programs', [
            'provider' => $provider->code,
            'total' => $totalPrograms,
            'limit' => $limit
        ]);
        
        // For ProBookingCenter: Store tour models for period processing
        $syncedTourModels = [];
        
        foreach ($programIds as $index => $programData) {
            try {
                // Update progress every 10 records for TTN_ALL
                if ($provider->code === 'ttn_all' && ($index % 10 === 0 || $index === $totalPrograms - 1)) {
                    $progress = round(($index + 1) / $totalPrograms * 100, 2);
                    Log::info('TTN_ALL sync progress', [
                        'processed' => $index + 1,
                        'total' => $totalPrograms,
                        'progress' => $progress . '%'
                    ]);
                    
                    // Update sync log with progress
                    $syncLog->update([
                        'status' => 'running',
                        'updated_at' => now()
                    ]);
                }
                
                // Handle different API structures
                if ($provider->code === 'go365' || $provider->code === 'qebooking') {
                    // GO365/QeBooking structure: programIds is array from /tours/search, need tour_id for detail
                    $tourId = $programData['tour_id'] ?? null;
                    if (!$tourId) {
                        Log::warning($provider->code . ': Missing tour_id', ['data' => $programData]);
                        $errorCount++;
                        continue;
                    }
                    
                    $detailUrl = str_replace('{tour_id}', $tourId, $provider->tour_detail_endpoint);
                } elseif ($provider->code === 'probookingcenter') {
                    // ProBookingCenter: Tours list contains all needed data, no detail endpoint needed
                    // Process tour directly without fetching detail
                    $result = $this->processTourData($provider, $programData, $syncLog);
                    
                    if ($result['action'] === 'created') {
                        $createdTours++;
                    } elseif ($result['action'] === 'duplicated') {
                        $duplicatedTours++;
                    }
                    
                    // Store tour model for period processing later
                    $syncedTourModels[] = $result['tour_model'];
                    
                    $processedCount++;
                    continue; // Skip detail fetch for ProBookingCenter
                } elseif ($provider->code === 'itravels' || $provider->code === 'itravel') {
                    // iTravels structure: use 'code' field with detail_url_pattern
                    $programId = is_array($programData) ? ($programData['code'] ?? null) : $programData;
                    if (!$programId) {
                        $errorCount++;
                        // Log::error('iTravels: No program code found', ['program_data' => $programData]);
                        continue;
                    }
                    
                    $detailUrlPattern = $config['detail_url_pattern'] ?? '/api/program/{code}';
                    
                    // Build full URL if pattern doesn't start with http
                    if (!str_starts_with($detailUrlPattern, 'http')) {
                        $baseUrl = rtrim($provider->url, '/');
                        // Remove /api/program from base URL if it exists
                        $baseUrl = preg_replace('#/api/program$#', '', $baseUrl);
                        $detailUrlPattern = $baseUrl . $detailUrlPattern;
                    }
                    
                    $detailUrl = str_replace('{code}', $programId, $detailUrlPattern);
                } else {
                    // TTN structure: use P_ID with config pattern
                    $programId = is_array($programData) ? ($programData['P_ID'] ?? null) : $programData;
                    if (!$programId) {
                        $errorCount++;
                        Log::error('No program ID found', ['program_data' => $programData, 'provider' => $provider->code]);
                        continue;
                    }
                    
                    $detailUrlPattern = $config['detail_url_pattern'] ?? '';
                    
                    // Build full URL if pattern doesn't start with http
                    if (!str_starts_with($detailUrlPattern, 'http')) {
                        $baseUrl = rtrim($provider->url, '/');
                        $detailUrlPattern = $baseUrl . '/' . ltrim($detailUrlPattern, '/');
                    }
                    
                    $detailUrl = str_replace('{id}', $programId, $detailUrlPattern);
                }
                
                // Use longer timeout for TTN_ALL detail calls
                $detailTimeout = ($provider->code === 'ttn_all') ? 90 : 60;
                $response = Http::withHeaders($headers)->timeout($detailTimeout)->get($detailUrl);
                
                if (!$response->successful()) {
                    $errorMsg = 'Failed to get program details';
                    Log::warning($errorMsg, [
                        'provider' => $provider->name,
                        'url' => $detailUrl,
                        'status' => $response->status(),
                        'program_id' => $programId ?? 'unknown',
                        'response_body' => substr($response->body(), 0, 200)
                    ]);
                    
                    $errors[] = $errorMsg . ' for ' . ($programId ?? 'unknown') . ': HTTP ' . $response->status();
                    $errorCount++;
                    
                    // For TTN_ALL, continue processing other records instead of failing completely
                    if ($provider->code === 'ttn_all') {
                        continue;
                    }
                    
                    // For critical providers, fail fast if error rate is too high
                    if ($errorCount > 5 && ($errorCount / ($index + 1)) > 0.5) {
                        throw new \Exception('Too many consecutive errors, stopping sync');
                    }
                    
                    continue;
                }
                
                $programDetails = $response->json();
                
                // Handle different API response structures
                if ($provider->code === 'go365' || $provider->code === 'qebooking') {
                    // GO365/QeBooking: Process main tour and periods from detail response
                    $result = $this->processTourData($provider, $programData, $syncLog);
                    
                    // Track creation status
                    if ($result['action'] === 'created') {
                        $createdTours++;
                        $createdPeriods += $result['periods_count'] ?? 0;
                    } elseif ($result['action'] === 'updated') {
                        $updatedPeriods += $result['periods_count'] ?? 0;
                    } elseif ($result['action'] === 'duplicated') {
                        $duplicatedTours++;
                        // Duplicated tours มี periods ใหม่ที่ถูกสร้าง ให้นับเป็น created
                        $createdPeriods += $result['periods_count'] ?? 0;
                    }
                    
                    // Process GO365/QeBooking periods from detail response (for both new and existing tours)
                    if (isset($programDetails['data'][0]['tour_period']) && 
                        is_array($programDetails['data'][0]['tour_period'])) {
                        $periodsCreatedCount = $this->processGO365Periods($provider, $result['tour_model'], $programDetails['data'][0]);
                        $createdPeriods += $periodsCreatedCount; // Add to total count
                    }
                    
                    $processedCount++;
                } elseif ($provider->code === 'itravels' || $provider->code === 'itravel') {
                    // iTravels: Detail response structure is {result: "success", data: [periods array]}
                    // The data array contains the periods directly, not nested under a 'periods' key
                    $periods = $programDetails['data'] ?? [];
                    
                    // Log::info('iTravels: Processing tour and periods', [
                        // 'provider' => $provider->code,
                        // 'tour_code' => $programData['code'] ?? 'UNKNOWN',
                        // 'periods_is_array' => is_array($periods),
                        // 'periods_count' => is_array($periods) ? count($periods) : 0,
                        // 'periods_sample' => is_array($periods) && !empty($periods) ? array_keys($periods[0] ?? []) : 'EMPTY'
                    // ]);
                    
                    // Use program data from main list for tour info
                    $result = $this->processTourData($provider, $programData, $syncLog);
                    
                    // ★★★ FIX: รวม 'duplicated' เข้ามาด้วย เพราะ tour ที่มีอยู่แล้วก็ต้องดึง periods ★★★
                    if ($result['action'] === 'created' || $result['action'] === 'updated' || $result['action'] === 'duplicated') {
                        if ($result['action'] === 'created') {
                            $createdTours++;
                            $createdPeriods += $result['periods_count'] ?? 0;
                        } elseif ($result['action'] === 'duplicated') {
                            $duplicatedTours++;
                            // Duplicated tours มี periods ใหม่ที่ถูกสร้าง ให้นับเป็น updated
                            $updatedPeriods += $result['periods_count'] ?? 0;
                        } else {
                            $updatedPeriods += $result['periods_count'] ?? 0;
                        }
                        
                        // Log::info('Tour processed, now processing periods', [
                            // 'action' => $result['action'],
                            // 'tour_id' => $result['tour_model']->id,
                            // 'periods_count' => count($periods)
                        // ]);
                        
                        // Process periods - the data array IS the periods array
                        // ★★★ เหมือน headcode เดิม 100%: ไม่ delete period ทั้งหมด แต่ update existing period ★★★
                        if (is_array($periods) && !empty($periods)) {
                            // Log::info('iTravels: About to loop periods', [
                                // 'tour_id' => $result['tour_model']->id,
                                // 'periods_count' => count($periods),
                                // 'first_period_keys' => array_keys($periods[0])
                            // ]);
                            
                            $max = [];
                            $period_ids = [];
                            $period_api_ids = [];
                            
                            foreach ($periods as $singlePeriod) {
                                try {
                                    // headcode: ค้นหา period ที่มีอยู่แล้วโดยใช้ period_api_id
                                    // $data3 = TourPeriodModel::where(['tour_id'=>$data->id, 'period_api_id'=>$call2['id'], 'api_type'=>'itravel'])->whereNull('deleted_at')->first();
                                    $periodApiId = $singlePeriod['id'] ?? null;
                                    
                                    $existingPeriod = null;
                                    if ($periodApiId) {
                                        $existingPeriod = \App\Models\Backend\TourPeriodModel::where([
                                            'tour_id' => $result['tour_model']->id,
                                            'period_api_id' => $periodApiId,
                                            'api_type' => $provider->code
                                        ])->whereNull('deleted_at')->first();
                                    }
                                    
                                    // headcode: ถ้าไม่เจอ สร้างใหม่, ถ้าเจอแล้ว update
                                    $period = $this->createPeriodFromArray($provider, $singlePeriod, $result['tour_model'], $programData, $existingPeriod);
                                    
                                    if ($period) {
                                        $period_ids[] = $period->id;
                                        $period_api_ids[] = $period->period_api_id;
                                    }
                                } catch (\Exception $e) {
                                    // Log::error('iTravels: Error creating period', [
                                        // 'tour_id' => $result['tour_model']->id,
                                        // 'error' => $e->getMessage(),
                                        // 'period_keys' => is_array($singlePeriod) ? array_keys($singlePeriod) : 'NOT_ARRAY'
                                    // ]);
                                }
                            }
                            
                            // headcode: ลบ period ที่ไม่อยู่ใน list ใหม่ (soft delete)
                            // TourPeriodModel::whereNotIn('id',$period)->whereNotIn('period_api_id',$period_api_id)->where('api_type','itravel')->update(['deleted_at'=>date('Y-m-d H:i:s')]);
                            if (!empty($period_ids)) {
                                \App\Models\Backend\TourPeriodModel::where('tour_id', $result['tour_model']->id)
                                    ->where('api_type', $provider->code)
                                    ->whereNotIn('id', $period_ids)
                                    ->whereNotIn('period_api_id', $period_api_ids)
                                    ->update(['deleted_at' => date('Y-m-d H:i:s')]);
                            }
                            
                            // ★★★ iTravels: Update tour price และ promotion เหมือน headcode ★★★
                            // headcode: TourModel::where(['id'=>$data->id, 'api_type'=>'itravel'])->update(['num_day'=>..,'price'=>..,'price_group'=>..,'special_price'=>..]);
                            $this->updateTourPrice($result['tour_model']);
                            // iTravels ไม่มี special_price ดังนั้น discount = 0 (ไม่มี promotion)
                            $this->updateTourPromotion($result['tour_model'], 0);
                            
                            // Add to appropriate counter
                            if ($result['action'] === 'created') {
                                $createdPeriods += count($period_ids);
                            } else {
                                $updatedPeriods += count($period_ids);
                            }
                        } else {
                            // Log::warning('iTravels: No periods found in detail response', [
                                // 'provider' => $provider->code,
                                // 'tour_id' => $result['tour_model']->id,
                                // 'tour_code' => $programData['code'] ?? 'UNKNOWN',
                                // 'tour_name' => $result['tour_model']->name ?? 'UNKNOWN',
                                // 'periods_is_array' => is_array($periods),
                                // 'periods_empty' => is_array($periods) && empty($periods)
                            // ]);
                        }
                    } elseif ($result['action'] === 'duplicated') {
                        $duplicatedTours++;
                        // Duplicated tours มี periods ใหม่ที่ถูกสร้าง ให้นับเป็น created
                        $createdPeriods += $result['periods_count'] ?? 0;
                    }
                    
                    $processedCount++;
                } else {
                    // TTN returns array, take first element
                    foreach ($programDetails as $program) {
                        $result = $this->processTourData($provider, $program, $syncLog);
                        
                        if ($result['action'] === 'created') {
                            $createdTours++;
                            $createdPeriods += $result['periods_count'] ?? 0;
                            
                            // Process periods if period URL pattern exists
                            if (!empty($config['period_url_pattern'])) {
                                // Use program's P_ID, not programData's
                                $programIdForPeriod = $program['P_ID'] ?? $programData['P_ID'];
                                $periodsCount = $this->processPeriodsFromConfig($provider, $result['tour_model'], $programIdForPeriod);
                                $createdPeriods += $periodsCount;
                            }
                        } elseif ($result['action'] === 'updated') {
                            $updatedPeriods += $result['periods_count'] ?? 0;
                        } elseif ($result['action'] === 'duplicated') {
                            $duplicatedTours++;
                            // Duplicated tours มี periods ใหม่ที่ถูกสร้าง ให้นับเป็น created
                            $createdPeriods += $result['periods_count'] ?? 0;
                        }
                        
                        $processedCount++;
                    }
                }
                
            } catch (\Exception $e) {
                $errorCount++;
                $errors[] = $e->getMessage();
                // Log::error('Error processing program', [
                    // 'provider' => $provider->name,
                    // 'program_id' => is_array($programData) ? ($programData['P_ID'] ?? 'unknown') : $programData,
                    // 'error' => $e->getMessage()
                // ]);
            }
        }
        
        // ProBookingCenter: Process periods for all tours (including existing ones)
        if ($provider->code === 'probookingcenter') {
            try {
                // Get all ProBookingCenter tours from database (not just newly synced)
                $allPBCTours = TourModel::where('api_type', 'probookingcenter')
                    ->whereNull('deleted_at')
                    ->get();
                
                Log::info('ProBookingCenter: Processing periods for all tours', [
                    'total_tours' => $allPBCTours->count()
                ]);
                
                // Process periods for each tour using seriesCode parameter
                foreach ($allPBCTours as $tour) {
                    $seriesCode = $tour->code1; // Use code1 as seriesCode
                    
                    if (empty($seriesCode)) continue;
                    
                    // Fetch periods for this specific tour using seriesCode parameter
                    $periodResponse = Http::withHeaders($headers)
                        ->timeout(30)
                        ->get($provider->period_endpoint, ['seriesCode' => $seriesCode]);
                    
                    if ($periodResponse->successful()) {
                        $periodData = $periodResponse->json();
                        $periods = $periodData['data'] ?? [];
                        
                        if (count($periods) > 0) {
                            $periodsProcessed = $this->processProBookingCenterPeriods($provider, $tour, $periods);
                            $createdPeriods += $periodsProcessed['created'];
                            $updatedPeriods += $periodsProcessed['updated'];
                        }
                    }
                    
                    // Small delay to avoid overwhelming API
                    usleep(100000); // 0.1 second
                }
                
                Log::info('ProBookingCenter: Periods processing completed', [
                    'created' => $createdPeriods,
                    'updated' => $updatedPeriods
                ]);
                
            } catch (\Exception $e) {
                $errorCount++;
                $errors[] = 'ProBookingCenter periods: ' . $e->getMessage();
            }
        }
        
        // Soft delete logic for all multi-step APIs (headcode behavior)
        $apiTypesWithSoftDelete = ['go365', 'qebooking', 'probookingcenter', 'ttn', 'ttn_japan', 'ttn_all', 'itravel', 'itravels'];
        
        if (in_array($provider->code, $apiTypesWithSoftDelete)) {
            // Collect all tour/period IDs that were synced
            $syncedTourIds = [];
            $syncedTourApiIds = [];
            $syncedPeriodIds = [];
            $syncedPeriodApiIds = [];
            
            $syncedTours = \App\Models\Backend\TourModel::where('api_type', $provider->code)
                ->whereNull('deleted_at')
                ->get(['id', 'api_id']);
                
            foreach ($syncedTours as $tour) {
                $syncedTourIds[] = $tour->id;
                if ($tour->api_id) {
                    $syncedTourApiIds[] = $tour->api_id;
                }
                
                $periods = \App\Models\Backend\TourPeriodModel::where('tour_id', $tour->id)
                    ->where('api_type', $provider->code)
                    ->whereNull('deleted_at')
                    ->get(['id', 'period_api_id']);
                    
                foreach ($periods as $period) {
                    $syncedPeriodIds[] = $period->id;
                    if ($period->period_api_id) {
                        $syncedPeriodApiIds[] = $period->period_api_id;
                    }
                }
            }
            
            if (!empty($syncedTourIds) && !empty($syncedTourApiIds)) {
                // Soft delete tours not in synced list (disappeared from API)
                \App\Models\Backend\TourModel::whereNotIn('id', $syncedTourIds)
                    ->whereNotIn('api_id', $syncedTourApiIds)
                    ->where('api_type', $provider->code)
                    ->whereNull('deleted_at')
                    ->update(['deleted_at' => date('Y-m-d H:i:s')]);
                    
                // Restore tours that are in synced list (returned to API)
                \App\Models\Backend\TourModel::whereIn('id', $syncedTourIds)
                    ->whereIn('api_id', $syncedTourApiIds)
                    ->where('api_type', $provider->code)
                    ->update(['deleted_at' => null]);
                    
                // Log::info($provider->code . ' tour soft delete completed', [
                    // 'synced_tours' => count($syncedTourIds)
                // ]);
            }
            
            if (!empty($syncedPeriodIds) && !empty($syncedPeriodApiIds)) {
                // Soft delete periods not in synced list
                \App\Models\Backend\TourPeriodModel::whereNotIn('id', $syncedPeriodIds)
                    ->whereNotIn('period_api_id', $syncedPeriodApiIds)
                    ->where('api_type', $provider->code)
                    ->whereNull('deleted_at')
                    ->update(['deleted_at' => date('Y-m-d H:i:s')]);
                    
                // Restore periods that are in synced list
                \App\Models\Backend\TourPeriodModel::whereIn('id', $syncedPeriodIds)
                    ->whereIn('period_api_id', $syncedPeriodApiIds)
                    ->where('api_type', $provider->code)
                    ->update(['deleted_at' => null]);
                    
                // Log::info($provider->code . ' period soft delete completed', [
                    // 'synced_periods' => count($syncedPeriodIds)
                // ]);
            }
        }
        
        // Update sync log
        DB::table('tb_api_sync_logs')
            ->where('id', $syncLog->id)
            ->update([
                'status' => 'completed',
                'completed_at' => now(),
                'total_records' => $processedCount,
                'created_tours' => $createdTours,
                'created_periods' => $createdPeriods,
                'updated_periods' => $updatedPeriods,
                'duplicated_tours' => $duplicatedTours,
                'error_count' => $errorCount,
                'error_message' => $errorCount > 0 ? implode('; ', array_slice($errors, 0, 5)) : null,
                'updated_at' => now()
            ]);
        
        return [
            'log_id' => $syncLog->id,
            'summary' => [
                'total_records' => $processedCount,
                'created_tours' => $createdTours,
                'created_periods' => $createdPeriods,
                'updated_periods' => $updatedPeriods,
                'duplicated_tours' => $duplicatedTours,
                'skipped_tours' => $skippedTours,
                'error_count' => $errorCount
            ]
        ];
        
        } catch (\Exception $e) {
            // Handle fatal errors that stop the entire sync
            // Log::error('Multi-step sync failed', [
                // 'provider' => $provider->name,
                // 'error' => $e->getMessage(),
                // 'trace' => $e->getTraceAsString()
            // ]);
            
            DB::table('tb_api_sync_logs')
                ->where('id', $syncLog->id)
                ->update([
                    'status' => 'failed',
                    'completed_at' => now(),
                    'error_count' => 1,
                    'error_message' => $e->getMessage(),
                    'updated_at' => now()
                ]);
            
            throw $e; // Re-throw to be caught by caller
        }
    }

    private function processPeriodsFromConfig($provider, $tourModel, $programId)
    {
        try {
            $headers = $provider->headers ?? [];
            $config = $provider->config ?? [];
            
            $periodUrlPattern = $config['period_url_pattern'] ?? '';
            
            // Build full URL if pattern doesn't start with http
            if (!str_starts_with($periodUrlPattern, 'http')) {
                $baseUrl = rtrim($provider->url, '/');
                $periodUrlPattern = $baseUrl . '/' . ltrim($periodUrlPattern, '/');
            }
            
            $periodUrl = str_replace('{id}', $programId, $periodUrlPattern);
            
            // Log::info('Calling period endpoint', [
                // 'provider' => $provider->code,
                // 'program_id' => $programId,
                // 'period_url' => $periodUrl
            // ]);
            
            $response = Http::withHeaders($headers)->timeout(60)->get($periodUrl);
            
            // Log::info('Period API response', [
                // 'provider' => $provider->code,
                // 'status' => $response->status(),
                // 'successful' => $response->successful(),
                // 'body_length' => strlen($response->body())
            // ]);
            
            if (!$response->successful()) {
                // Log::warning('Failed to get periods', [
                    // 'provider' => $provider->name,
                    // 'program_id' => $programId,
                    // 'status' => $response->status()
                // ]);
                return 0;
            }
            
            $periodsData = $response->json();
            
            // Log::info('Period data received', [
                // 'provider' => $provider->code,
                // 'data_type' => gettype($periodsData),
                // 'is_array' => is_array($periodsData),
                // 'count' => is_array($periodsData) ? count($periodsData) : 0
            // ]);
            
            // TTN Japan: Process periods with Price array (เหมือน headcode)
            if ($provider->code === 'ttn' || $provider->code === 'ttn_japan') {
                // Log::info('Processing TTN Japan periods', [
                    // 'provider' => $provider->code,
                    // 'tour_id' => $tourModel->id,
                    // 'periods_count' => is_array($periodsData) ? count($periodsData) : 0
                // ]);
                return $this->processTTNJapanPeriods($provider, $tourModel, $periodsData);
            } else {
                // Generic period processing for other APIs
                $createdCount = 0;
                if (count($periodsData) > 0) {
                    foreach ($periodsData as $periodData) {
                        if (isset($periodData['Price']) && is_array($periodData['Price'])) {
                            foreach ($periodData['Price'] as $priceData) {
                                // Create period using database mappings
                                $this->createPeriodFromArray($provider, array_merge($periodData, $priceData), $tourModel);
                                $createdCount++;
                            }
                        }
                    }
                }
                return $createdCount;
            }
            
        } catch (\Exception $e) {
            // Log::error('Error processing periods from config', [
                // 'provider' => $provider->name,
                // 'program_id' => $programId,
                // 'error' => $e->getMessage()
            // ]);
            return 0;
        }
    }

    private function processTTNJapanPeriods($provider, $tourModel, $periodsData)
    {
        // TTN Japan period logic (เหมือน headcode 100%)
        try {
            if (!is_array($periodsData) || count($periodsData) === 0) {
                return 0;
            }

            $createdCount = 0;
            foreach ($periodsData as $periodGroup) {
                if (!isset($periodGroup['Price']) || !is_array($periodGroup['Price'])) {
                    continue;
                }

                foreach ($periodGroup['Price'] as $priceData) {
                    // ตรวจสอบว่ามี period นี้อยู่แล้วหรือไม่
                    $periodModel = \App\Models\Backend\TourPeriodModel::where([
                        'tour_id' => $tourModel->id,
                        'period_api_id' => $periodGroup['P_ID'],
                        'api_type' => 'ttn'
                    ])
                    ->whereNull('deleted_at')
                    ->first();

                    if (!$periodModel) {
                        $periodModel = new \App\Models\Backend\TourPeriodModel();
                    }

                    // ราคา (เหมือน headcode)
                    $price1 = $priceData['P_ADULT_PRICE'] ?? 0; // ผู้ใหญ่พักคู่
                    $price2 = $priceData['P_SINGLE_PRICE'] ?? 0; // ผู้ใหญ่พักเดี่ยว

                    $periodModel->tour_id = $tourModel->id;
                    $periodModel->period_api_id = $periodGroup['P_ID'];
                    $periodModel->group_date = date('mY', strtotime($periodGroup['P_DUE_START']));
                    $periodModel->start_date = $periodGroup['P_DUE_START'];
                    $periodModel->end_date = $periodGroup['P_DUE_END'];
                    $periodModel->day = $tourModel->day; // เอาจาก tour หลัก
                    $periodModel->night = $tourModel->night; // เอาจาก tour หลัก
                    $periodModel->price1 = $price1;
                    $periodModel->price2 = $price2;
                    $periodModel->group = $priceData['P_VOLUME'] ?? 0;
                    $periodModel->count = $priceData['P_AVAILABLE'] ?? 0;
                    $periodModel->status_display = 'on';

                    // Status period (เหมือน headcode)
                    if ($priceData['P_AVAILABLE'] === 'Open') {
                        $periodModel->status_period = 1;
                    } elseif ($priceData['P_AVAILABLE'] === 'ChangePrice') {
                        $periodModel->status_period = 3;
                    } else {
                        $periodModel->status_period = 1; // default
                    }

                    $periodModel->api_type = 'ttn';
                    $periodModel->save();
                    $createdCount++;

                    // Log::info('TTN Japan period created/updated', [
                        // 'tour_id' => $tourModel->id,
                        // 'period_id' => $periodModel->id,
                        // 'period_api_id' => $periodGroup['P_ID'],
                        // 'start_date' => $periodModel->start_date,
                        // 'price1' => $price1,
                        // 'price2' => $price2
                    // ]);
                }
            }

            return $createdCount;
        } catch (\Exception $e) {
            // Log::error('Error processing TTN Japan periods', [
                // 'tour_id' => $tourModel->id,
                // 'error' => $e->getMessage()
            // ]);
            return 0;
        }
    }

    private function processGO365Periods($provider, $tourModel, $tourDetailData)
    {
        try {
            if (!isset($tourDetailData['tour_period']) || !is_array($tourDetailData['tour_period'])) {
                return;
            }

            $maxDiscounts = []; // เก็บ max discount % จากทุก period (สำหรับ promotion logic)

            foreach ($tourDetailData['tour_period'] as $periodData) {
                // Check if period already exists (like headcode and TTN logic)
                $periodModel = \App\Models\Backend\TourPeriodModel::where([
                    'tour_id' => $tourModel->id,
                    'period_api_id' => $periodData['period_id'] ?? null,
                    'api_type' => $provider->code
                ])
                ->whereNull('deleted_at')
                ->first();

                // Create new if not exists
                if (!$periodModel) {
                    $periodModel = new \App\Models\Backend\TourPeriodModel();
                }
                
                // Map fields according to headcode
                $periodModel->tour_id = $tourModel->id;
                $periodModel->period_api_id = $periodData['period_id'] ?? null;
                $periodModel->start_date = $periodData['period_date'] ?? null;
                $periodModel->end_date = $periodData['period_back'] ?? null;
                
                // Use parent tour data for day/night
                $periodModel->day = $tourDetailData['tour_num_day'] ?? $tourModel->day ?? null;
                $periodModel->night = $tourDetailData['tour_num_night'] ?? $tourModel->night ?? null;
                
                // Price calculations as per headcode
                $price1 = $periodData['period_rate_adult_twn'] ?? 0;
                $price2_cal = $periodData['period_rate_adult_sgl'] ?? 0;
                
                $periodModel->price1 = $price1;
                $periodModel->price2 = ($price2_cal >= $price1) ? ($price2_cal - $price1) : 0;
                $periodModel->price3 = $price1; // Child with bed = adult twin
                $periodModel->price4 = $price1; // Child no bed = adult twin
                
                $periodModel->group = $periodData['period_quota'] ?? 0;
                $periodModel->count = $periodData['period_available'] ?? 0;
                $periodModel->status_display = 'on';
                
                // Status period logic as per headcode
                $periodVisible = $periodData['period_visible'] ?? 0;
                $periodModel->status_period = ($periodVisible == 1 || $periodVisible == 2) ? 1 : 3;
                
                $periodModel->api_type = $provider->code; // go365 or qebooking
                $periodModel->group_date = $periodData['period_date'] ? date('mY', strtotime($periodData['period_date'])) : null;
                
                $periodModel->save();
                
                // Calculate discount % for promotion logic (headcode logic)
                // Note: headcode has variables $cal1-$cal4 but code is missing
                // Assuming it's based on special_price percentage
                if ($periodModel->special_price1 > 0 && $price1 > 0) {
                    $discountPercent = ($periodModel->special_price1 / $price1) * 100;
                    $maxDiscounts[] = $discountPercent;
                }
            }
            
            // Calculate price_group based on cheapest period (headcode lines 3745-3785)
            $allPeriods = \App\Models\Backend\TourPeriodModel::where('tour_id', $tourModel->id)
                ->where('api_type', $provider->code) // Use provider code (go365 or qebooking)
                ->whereNull('deleted_at')
                ->get();
                
            if ($allPeriods->count() > 0) {
                // Find cheapest period (price1 - special_price1)
                $cheapestPeriod = $allPeriods->sortBy(function ($item) {
                    return $item->price1 - ($item->special_price1 ?? 0);
                })->first();
                
                if ($cheapestPeriod) {
                    $num_day = '';
                    if ($cheapestPeriod->day && $cheapestPeriod->night) {
                        $num_day = $cheapestPeriod->day . ' วัน ' . $cheapestPeriod->night . ' คืน';
                    }
                    
                    $price = $cheapestPeriod->price1;
                    $special_price = $cheapestPeriod->special_price1 ?? 0;
                    $net_price = $price - $special_price;
                    
                    // Calculate price_group (headcode logic)
                    $price_group = 0;
                    if ($net_price > 0) {
                        if ($net_price <= 10000) {
                            $price_group = 1;
                        } elseif ($net_price <= 20000) {
                            $price_group = 2;
                        } elseif ($net_price <= 30000) {
                            $price_group = 3;
                        } elseif ($net_price <= 50000) {
                            $price_group = 4;
                        } elseif ($net_price <= 80000) {
                            $price_group = 5;
                        } else {
                            $price_group = 6;
                        }
                    }
                    
                    // Update tour with price info
                    $tourModel->num_day = $num_day;
                    $tourModel->price = $price;
                    $tourModel->price_group = $price_group;
                    $tourModel->special_price = $special_price;
                }
            }
            
            // Promotion logic (headcode lines 3789-3796)
            if (!empty($maxDiscounts)) {
                $maxDiscount = max($maxDiscounts);
                
                if ($maxDiscount >= 30) {
                    // โปรไฟไหม้
                    $tourModel->promotion1 = 'Y';
                    $tourModel->promotion2 = 'N';
                } elseif ($maxDiscount > 0 && $maxDiscount < 30) {
                    // โปรธรรมดา
                    $tourModel->promotion1 = 'N';
                    $tourModel->promotion2 = 'Y';
                } else {
                    // ไม่มีโปร
                    $tourModel->promotion1 = 'N';
                    $tourModel->promotion2 = 'N';
                }
            } else {
                // No periods with discount
                $tourModel->promotion1 = 'N';
                $tourModel->promotion2 = 'N';
            }
            
            $tourModel->save();
            
            // Log::info('GO365 periods processed', [
                // 'tour_id' => $tourModel->id,
                // 'period_count' => count($tourDetailData['tour_period']),
                // 'price_group' => $tourModel->price_group,
                // 'promotion1' => $tourModel->promotion1,
                // 'promotion2' => $tourModel->promotion2
            // ]);
            
            return count($tourDetailData['tour_period']); // Return period count
            
        } catch (\Exception $e) {
            // Log::error('Error processing GO365 periods', [
                // 'tour_id' => $tourModel->id,
                // 'error' => $e->getMessage()
            // ]);
            return 0; // Return 0 on error
        }
    }

    private function performBestConsortiumSync($provider, $syncLog, $limit = 0)
    {
        // Best Consortium: Multi-country sync (เหมือน headcode 100%)
        // Step 1: ดึงรายชื่อประเทศทั้งหมด
        // Step 2: วนลูปแต่ละประเทศดึง tours
        
        $headers = [];
        if (!empty($provider->headers)) {
            $headers = is_string($provider->headers) ? json_decode($provider->headers, true) : $provider->headers;
            $headers = $headers ?? [];
        }
        
        try {
            // Step 1: Get all countries
            $countriesUrl = 'https://api.best-consortium.com/v1/series/country';
            
            // Log::info('Best Consortium: Fetching countries', [
                // 'url' => $countriesUrl
            // ]);
            
            $response = Http::withHeaders($headers)->timeout(60)->get($countriesUrl);
            
            if (!$response->successful()) {
                throw new \Exception('Failed to fetch countries: ' . $response->status());
            }
            
            $countries = $response->json();
            
            if (!is_array($countries) || count($countries) === 0) {
                throw new \Exception('No countries found in API response');
            }
            
            // Log::info('Best Consortium: Countries fetched', [
            //     'country_count' => count($countries),
            //     'countries' => array_map(function($c) {
            //         return ['id' => $c['id'] ?? 'N/A', 'name' => $c['nameEng'] ?? 'N/A'];
            //     }, $countries)
            // ]);
            
            // Step 2: Loop through each country and fetch tours
            $createdTours = 0;
            $duplicatedTours = 0;
            $errorCount = 0;
            $errors = [];
            $totalRecords = 0;
            $toursToProcess = [];
            
            foreach ($countries as $country) {
                $countryId = $country['id'] ?? null;
                $countryName = $country['nameEng'] ?? 'Unknown';
                
                if (!$countryId) {
                    // Log::warning('Best Consortium: Country missing ID', ['country' => $country]);
                    continue;
                }
                
                try {
                    $toursUrl = "https://tour-api.bestinternational.com/api/tour-programs/v2/{$countryId}";
                    
                    // Log::info('Best Consortium: Fetching tours for country', [
                        // 'country_id' => $countryId,
                        // 'country_name' => $countryName,
                        // 'url' => $toursUrl
                    // ]);
                    
                    // Handle rate limiting (เหมือน headcode)
                    $response = Http::withHeaders($headers)->timeout(60)->get($toursUrl);
                    
                    $remaining = $response->header('X-RateLimit-Remaining');
                    $resetTime = $response->header('X-RateLimit-Reset');
                    
                    if ($remaining !== null && $resetTime !== null) {
                        if ($remaining == "" || $remaining <= 0) {
                            if (!is_numeric($resetTime)) {
                                $resetTime = time() + 60;
                            }
                            $waitTime = max(1, $resetTime - time());
                            // Log::info('Best Consortium: Rate limit reached, sleeping', ['wait_time' => $waitTime]);
                            sleep($waitTime);
                            $response = Http::withHeaders($headers)->timeout(60)->get($toursUrl);
                        }
                    } elseif ($response->status() == 429) {
                        // Rate limit - retry after sleep
                        // Log::warning('Best Consortium: Rate limit reached (429)', [
                            // 'country_id' => $countryId
                        // ]);
                        sleep(60);
                        $response = Http::withHeaders($headers)->timeout(60)->get($toursUrl);
                    }
                    
                    // 404 = ไม่มี tours ในประเทศนี้ (ไม่ใช่ error ปกติ)
                    if ($response->status() == 404) {
                        // Log::info('Best Consortium: No tours found for country (404)', [
                            // 'country_id' => $countryId,
                            // 'country_name' => $countryName
                        // ]);
                        continue; // ข้ามประเทศนี้ไป ไม่นับเป็น error
                    }
                    
                    if (!$response->successful()) {
                        // Error อื่นๆ (ไม่ใช่ 404) ถึงจะนับเป็น error
                        $errorCount++;
                        $errors[] = "Country {$countryName} ({$countryId}): Status {$response->status()}";
                        // Log::warning('Best Consortium: Failed to fetch country tours', [
                            // 'country_id' => $countryId,
                            // 'country_name' => $countryName,
                            // 'status' => $response->status()
                        // ]);
                        continue;
                    }
                    
                    $tours = $response->json();
                    
                    if (is_array($tours) && count($tours) > 0) {
                        // Log::info('Best Consortium: Tours fetched for country', [
                            // 'country_id' => $countryId,
                            // 'country_name' => $countryName,
                            // 'tour_count' => count($tours)
                        // ]);
                        
                        // เก็บ country info ไว้ใน tour data
                        foreach ($tours as &$tour) {
                            $tour['_country_id'] = $countryId;
                            $tour['_country_name_eng'] = $countryName;
                            $toursToProcess[] = $tour;
                        }
                        
                        $totalRecords += count($tours);
                    }
                    
                } catch (\Exception $e) {
                    $errorCount++;
                    $errors[] = "Country {$countryName}: {$e->getMessage()}";
                    // Log::error('Best Consortium: Error fetching country tours', [
                        // 'country_id' => $countryId,
                        // 'country_name' => $countryName,
                        // 'error' => $e->getMessage()
                    // ]);
                }
            }
            
            // Apply limit if specified
            if ($limit > 0 && count($toursToProcess) > $limit) {
                $toursToProcess = array_slice($toursToProcess, 0, $limit);
                // Log::info('Best Consortium: Applied limit to tours', [
                    // 'original_count' => $totalRecords,
                    // 'limited_count' => count($toursToProcess),
                    // 'limit' => $limit
                // ]);
            }
            
            // Process all collected tours
            $createdPeriods = 0;
            $updatedPeriods = 0;
            
            // Log::info('Best Consortium: Starting to process tours', [
                // 'total_tours' => count($toursToProcess)
            // ]);
            
            foreach ($toursToProcess as $index => $tourData) {
                try {
                    $currentIndex = $index + 1;
                    // Log::info("Processing tour {$currentIndex}/" . count($toursToProcess), [
                        // 'provider' => $provider->code,
                        // 'tour_index' => $currentIndex
                    // ]);
                    
                    $result = $this->processTourData($provider, $tourData, $syncLog);
                    
                    if ($result['action'] === 'created') {
                        $createdTours++;
                        $createdPeriods += $result['periods_count'] ?? 0;
                    } elseif ($result['action'] === 'updated') {
                        $createdTours++; // Count updates as processed
                        $updatedPeriods += $result['periods_count'] ?? 0;
                    } elseif ($result['action'] === 'duplicated') {
                        $duplicatedTours++;
                        // Duplicated tours มี periods ใหม่ที่ถูกสร้าง ให้นับเป็น created
                        $createdPeriods += $result['periods_count'] ?? 0;
                    }
                    
                } catch (\Exception $e) {
                    $errorCount++;
                    $errors[] = $e->getMessage();
                    // Log::error('Best Consortium: Error processing tour', [
                        // 'tour_index' => $index + 1,
                        // 'tour_code' => $tourData['code'] ?? 'N/A',
                        // 'error' => $e->getMessage()
                    // ]);
                }
            }
            
            // Soft delete logic for Best Consortium (headcode behavior)
            $syncedTourIds = [];
            $syncedTourApiIds = [];
            $syncedPeriodIds = [];
            $syncedPeriodApiIds = [];
            
            $syncedTours = \App\Models\Backend\TourModel::where('api_type', 'best')
                ->whereNull('deleted_at')
                ->get(['id', 'api_id']);
                
            foreach ($syncedTours as $tour) {
                $syncedTourIds[] = $tour->id;
                if ($tour->api_id) {
                    $syncedTourApiIds[] = $tour->api_id;
                }
                
                $periods = \App\Models\Backend\TourPeriodModel::where('tour_id', $tour->id)
                    ->where('api_type', 'best')
                    ->whereNull('deleted_at')
                    ->get(['id', 'period_api_id']);
                    
                foreach ($periods as $period) {
                    $syncedPeriodIds[] = $period->id;
                    if ($period->period_api_id) {
                        $syncedPeriodApiIds[] = $period->period_api_id;
                    }
                }
            }
            
            if (!empty($syncedTourIds) && !empty($syncedTourApiIds)) {
                \App\Models\Backend\TourModel::whereNotIn('id', $syncedTourIds)
                    ->whereNotIn('api_id', $syncedTourApiIds)
                    ->where('api_type', 'best')
                    ->whereNull('deleted_at')
                    ->update(['deleted_at' => date('Y-m-d H:i:s')]);
                    
                \App\Models\Backend\TourModel::whereIn('id', $syncedTourIds)
                    ->whereIn('api_id', $syncedTourApiIds)
                    ->where('api_type', 'best')
                    ->update(['deleted_at' => null]);
                    
                // Log::info('Best Consortium tour soft delete completed', [
                    // 'synced_tours' => count($syncedTourIds)
                // ]);
            }
            
            if (!empty($syncedPeriodIds) && !empty($syncedPeriodApiIds)) {
                \App\Models\Backend\TourPeriodModel::whereNotIn('id', $syncedPeriodIds)
                    ->whereNotIn('period_api_id', $syncedPeriodApiIds)
                    ->where('api_type', 'best')
                    ->whereNull('deleted_at')
                    ->update(['deleted_at' => date('Y-m-d H:i:s')]);
                    
                \App\Models\Backend\TourPeriodModel::whereIn('id', $syncedPeriodIds)
                    ->whereIn('period_api_id', $syncedPeriodApiIds)
                    ->where('api_type', 'best')
                    ->update(['deleted_at' => null]);
                    
                // Log::info('Best Consortium period soft delete completed', [
                    // 'synced_periods' => count($syncedPeriodIds)
                // ]);
            }
            
            // Update sync log
            DB::table('tb_api_sync_logs')
                ->where('id', $syncLog->id)
                ->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'total_records' => count($toursToProcess),
                    'created_tours' => $createdTours,
                    'duplicated_tours' => $duplicatedTours,
                    'created_periods' => $createdPeriods,
                    'updated_periods' => $updatedPeriods,
                    'error_count' => $errorCount,
                    'error_message' => $errorCount > 0 ? implode('; ', array_slice($errors, 0, 5)) : null,
                    'updated_at' => now()
                ]);
            
            return [
                'log_id' => $syncLog->id,
                'summary' => [
                    'total_countries' => count($countries),
                    'total_records' => count($toursToProcess),
                    'created_tours' => $createdTours,
                    'duplicated_tours' => $duplicatedTours,
                    'created_periods' => $createdPeriods,
                    'updated_periods' => $updatedPeriods,
                    'error_count' => $errorCount
                ]
            ];
            
        } catch (\Exception $e) {
            // Log::error('Best Consortium sync error', [
                // 'error' => $e->getMessage(),
                // 'trace' => $e->getTraceAsString()
            // ]);
            
            DB::table('tb_api_sync_logs')
                ->where('id', $syncLog->id)
                ->update([
                    'status' => 'failed',
                    'completed_at' => now(),
                    'error_message' => $e->getMessage(),
                    'updated_at' => now()
                ]);
            
            throw $e;
        }
    }

    private function performSuperHolidaySync($provider, $syncLog, $limit = 0)
    {
        // Super Holiday: Loop through hardcoded category IDs (เหมือน headcode 100%)
        // ตาม headcode: [21,29,28,23,25,24,18,2,3,17,1,19]
        
        $categoryIds = [21, 29, 28, 23, 25, 24, 18, 2, 3, 17, 1, 19];
        
        // Log::info('Super Holiday: Starting multi-category sync', [
            // 'provider' => $provider->name,
            // 'total_categories' => count($categoryIds),
            // 'category_ids' => $categoryIds,
            // 'limit' => $limit
        // ]);
        
        $headers = [];
        if (!empty($provider->headers)) {
            $headers = is_string($provider->headers) ? json_decode($provider->headers, true) : $provider->headers;
            $headers = $headers ?? [];
        }
        
        $allTours = [];
        $createdTours = 0;
        $duplicatedTours = 0;
        $errorCount = 0;
        $errors = [];
        
        try {
            // Loop through each category ID
            foreach ($categoryIds as $catId) {
                $url = $provider->url . '?id=' . $catId;
                
                // Log::info("Super Holiday: Fetching category {$catId}", [
                    // 'url' => $url
                // ]);
                
                try {
                    $response = Http::withHeaders($headers)->timeout(120)->get($url);
                    
                    if (!$response->successful()) {
                        // Log::warning("Super Holiday: Category {$catId} failed", [
                            // 'status' => $response->status()
                        // ]);
                        continue;
                    }
                    
                    $data = $response->json();
                    
                    if (!empty($data) && is_array($data)) {
                        // Log::info("Super Holiday: Category {$catId} returned tours", [
                            // 'count' => count($data)
                        // ]);
                        $allTours = array_merge($allTours, $data);
                    }
                    
                } catch (\Exception $e) {
                    // Log::error("Super Holiday: Error fetching category {$catId}", [
                        // 'error' => $e->getMessage()
                    // ]);
                    continue;
                }
            }
            
            // Log::info('Super Holiday: All categories fetched', [
                // 'total_records' => count($allTours)
            // ]);
            
            // Super Holiday: Group records by mainid (1 tour = multiple periods)
            // Each record in API response is 1 period, not 1 tour!
            $tourGroups = [];
            foreach ($allTours as $record) {
                $mainid = $record['mainid'] ?? null;
                if ($mainid) {
                    if (!isset($tourGroups[$mainid])) {
                        $tourGroups[$mainid] = [];
                    }
                    $tourGroups[$mainid][] = $record;
                }
            }
            
            // Log::info('Super Holiday: Grouped records by tour', [
                // 'total_records' => count($allTours),
                // 'total_tours' => count($tourGroups)
            // ]);
            
            // Apply limit if specified (limit by tours, not records)
            if ($limit > 0 && count($tourGroups) > $limit) {
                $tourGroups = array_slice($tourGroups, 0, $limit, true);
                // Log::info('Super Holiday: Applied limit', [
                    // 'limited_tours' => count($tourGroups),
                    // 'limit' => $limit
                // ]);
            }
            
            // Process tours (each tour has multiple periods)
            $createdPeriods = 0;
            $updatedPeriods = 0;
            
            foreach ($tourGroups as $mainid => $periodRecords) {
                try {
                    // Use first record for tour data (all periods have same tour info)
                    $tourData = $periodRecords[0];
                    
                    // Add all periods to tour data
                    $tourData['periods'] = $periodRecords;
                    
                    $result = $this->processTourData($provider, $tourData, $syncLog);
                    
                    // Count periods (number of period records for this tour)
                    $periodCount = count($periodRecords);
                    
                    if ($result['action'] === 'created') {
                        $createdTours++;
                        $createdPeriods += $periodCount;
                    } elseif ($result['action'] === 'updated') {
                        $createdTours++;
                        $updatedPeriods += $periodCount;
                    } elseif ($result['action'] === 'duplicated') {
                        $duplicatedTours++;
                        // Duplicated tours มี periods ใหม่ที่ถูกสร้าง ให้นับเป็น created
                        $createdPeriods += $periodCount;
                    }
                    
                } catch (\Exception $e) {
                    $errorCount++;
                    $errors[] = $e->getMessage();
                    // Log::error('Super Holiday: Error processing tour', [
                        // 'mainid' => $mainid,
                        // 'error' => $e->getMessage()
                    // ]);
                }
            }
            
            // Soft delete logic for Super Holiday (headcode behavior)
            $syncedTourIds = [];
            $syncedTourApiIds = [];
            $syncedPeriodIds = [];
            $syncedPeriodCodes = [];
            
            $syncedTours = \App\Models\Backend\TourModel::where('api_type', 'superbholiday')
                ->whereNull('deleted_at')
                ->get(['id', 'api_id']);
                
            foreach ($syncedTours as $tour) {
                $syncedTourIds[] = $tour->id;
                if ($tour->api_id) {
                    $syncedTourApiIds[] = $tour->api_id;
                }
                
                // Super Holiday uses period_code instead of period_api_id
                $periods = \App\Models\Backend\TourPeriodModel::where('tour_id', $tour->id)
                    ->where('api_type', 'superbholiday')
                    ->whereNull('deleted_at')
                    ->get(['id', 'period_code']);
                    
                foreach ($periods as $period) {
                    $syncedPeriodIds[] = $period->id;
                    if ($period->period_code) {
                        $syncedPeriodCodes[] = $period->period_code;
                    }
                }
            }
            
            if (!empty($syncedTourIds) && !empty($syncedTourApiIds)) {
                \App\Models\Backend\TourModel::whereNotIn('id', $syncedTourIds)
                    ->whereNotIn('api_id', $syncedTourApiIds)
                    ->where('api_type', 'superbholiday')
                    ->whereNull('deleted_at')
                    ->update(['deleted_at' => date('Y-m-d H:i:s')]);
                    
                \App\Models\Backend\TourModel::whereIn('id', $syncedTourIds)
                    ->whereIn('api_id', $syncedTourApiIds)
                    ->where('api_type', 'superbholiday')
                    ->update(['deleted_at' => null]);
                    
                // Log::info('Super Holiday tour soft delete completed', [
                    // 'synced_tours' => count($syncedTourIds)
                // ]);
            }
            
            if (!empty($syncedPeriodIds) && !empty($syncedPeriodCodes)) {
                \App\Models\Backend\TourPeriodModel::whereNotIn('id', $syncedPeriodIds)
                    ->whereNotIn('period_code', $syncedPeriodCodes)
                    ->where('api_type', 'superbholiday')
                    ->whereNull('deleted_at')
                    ->update(['deleted_at' => date('Y-m-d H:i:s')]);
                    
                \App\Models\Backend\TourPeriodModel::whereIn('id', $syncedPeriodIds)
                    ->whereIn('period_code', $syncedPeriodCodes)
                    ->where('api_type', 'superbholiday')
                    ->update(['deleted_at' => null]);
                    
                // Log::info('Super Holiday period soft delete completed', [
                    // 'synced_periods' => count($syncedPeriodIds)
                // ]);
            }
            
            // Update sync log
            DB::table('tb_api_sync_logs')
                ->where('id', $syncLog->id)
                ->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'total_records' => count($allTours),
                    'created_tours' => $createdTours,
                    'duplicated_tours' => $duplicatedTours,
                    'created_periods' => $createdPeriods,
                    'updated_periods' => $updatedPeriods,
                    'error_count' => $errorCount,
                    'error_message' => $errorCount > 0 ? implode('; ', array_slice($errors, 0, 5)) : null,
                    'updated_at' => now()
                ]);
            
            return [
                'log_id' => $syncLog->id,
                'summary' => [
                    'total_categories' => count($categoryIds),
                    'total_records' => count($allTours),
                    'total_tours' => count($tourGroups),
                    'created_tours' => $createdTours,
                    'duplicated_tours' => $duplicatedTours,
                    'error_count' => $errorCount
                ]
            ];
            
        } catch (\Exception $e) {
            // Log::error('Super Holiday sync error', [
                // 'error' => $e->getMessage(),
                // 'trace' => $e->getTraceAsString()
            // ]);
            
            DB::table('tb_api_sync_logs')
                ->where('id', $syncLog->id)
                ->update([
                    'status' => 'failed',
                    'completed_at' => now(),
                    'error_message' => $e->getMessage(),
                    'updated_at' => now()
                ]);
            
            throw $e;
        }
    }

    private function processTourData($provider, $tourData, $syncLog)
    {
        // ตรวจสอบ data type ก่อน
        // Log::info('processTourData received data', [
            // 'provider' => $provider->name,
            // 'data_type' => gettype($tourData),
            // 'is_array' => is_array($tourData),
            // 'array_keys' => is_array($tourData) ? array_keys($tourData) : null,
            // 'has_tour_id' => is_array($tourData) && isset($tourData['tour_id']) ? 'YES' : 'NO',
            // 'first_few_keys' => is_array($tourData) ? array_slice(array_keys($tourData), 0, 10) : null,
            // 'tour_id_value' => is_array($tourData) && isset($tourData['tour_id']) ? $tourData['tour_id'] : 'NOT_FOUND'
        // ]);
        
        if (!is_array($tourData)) {
            throw new \Exception('Invalid tour data: Expected array but received ' . gettype($tourData) . '. Data: ' . json_encode($tourData));
        }
        
        // ตรวจสอบว่าเป็น associative array หรือ indexed array
        $isAssociative = array_keys($tourData) !== range(0, count($tourData) - 1);
        if (!$isAssociative) {
            // Log::error('Tour data is indexed array instead of associative array', [
                // 'provider' => $provider->name,
                // 'array_keys' => array_keys($tourData),
                // 'array_count' => count($tourData),
                // 'first_element_type' => isset($tourData[0]) ? gettype($tourData[0]) : 'none'
            // ]);
            throw new \Exception('Invalid tour data structure: Expected associative array but received indexed array');
        }
        
        // ตรวจสอบว่ามี API ID หรือไม่
        $apiIdField = $provider->fieldMappings()
            ->where('field_type', 'tour')
            ->where('local_field', 'api_id')
            ->first();
            
        if (!$apiIdField) {
            throw new \Exception('API ID field mapping not found');
        }

        $apiId = $tourData[$apiIdField->api_field] ?? null;
        if (!$apiId) {
            // ลอง fallback กับ field names ที่เป็นไปได้
            $possibleFields = ['tour_id', 'id', 'product_id', 'ProductID', 'programtour_id'];
            foreach ($possibleFields as $field) {
                if (isset($tourData[$field])) {
                    $apiId = $tourData[$field];
                    break;
                }
            }
            
            if (!$apiId) {
                // Log::error('API ID not found in tour data', [
                    // 'provider' => $provider->code,
                    // 'expected_field' => $apiIdField->api_field,
                    // 'tour_data_type' => gettype($tourData),
                    // 'tour_data_value' => is_array($tourData) ? 'array with ' . count($tourData) . ' elements' : $tourData,
                    // 'available_fields' => is_array($tourData) ? array_keys($tourData) : 'N/A - not an array',
                    // 'tour_data_sample' => is_array($tourData) ? array_slice($tourData, 0, 5, true) : 'N/A - not an array'
                // ]);
                throw new \Exception('API ID not found in tour data. Expected field: ' . $apiIdField->api_field . '. Data type: ' . gettype($tourData));
            }
        }

        // ตรวจสอบว่ามีทัวร์นี้ในระบบแล้วหรือไม่
        $existingTour = null;
        
        // For iTravel: Skip api_id check (API doesn't provide unique numeric ID for tours)
        // iTravels API ใช้ 'code' เป็น identifier หลัก ไม่มี 'id' หรือ 'programtour_id'
        // headcode: $data = TourModel::where(['code1'=>$call1['code'], 'api_type'=>'itravel'])->first();
        // ProBookingCenter: Also use code1 (api_id is integer, can't store seriesCode string)
        if ($provider->code === 'itravel' || $provider->code === 'itravels' || $provider->code === 'probookingcenter') {
            $codeField = ($provider->code === 'probookingcenter') ? 'seriesCode' : 'code';
            
            if (isset($tourData[$codeField])) {
                $existingTour = TourModel::where([
                    'code1' => $tourData[$codeField],
                    'api_type' => $provider->code
                ])->whereNull('deleted_at')->first();
                
                // Debug logging for soft-deleted tours
                if (!$existingTour) {
                    // Check if tour exists but is soft deleted
                    $softDeletedTour = TourModel::where([
                        'code1' => $tourData[$codeField],
                        'api_type' => $provider->code
                    ])->whereNotNull('deleted_at')->first();
                    
                    if ($softDeletedTour) {
                        Log::info('Tour is soft-deleted, will create new one', [
                            'provider' => $provider->code,
                            'code1' => $tourData[$codeField],
                            'old_tour_id' => $softDeletedTour->id,
                            'deleted_at' => $softDeletedTour->deleted_at
                        ]);
                    }
                }
            }
            
            // Don't set api_id for these APIs (headcode doesn't use it / api_id is integer)
            // Only use code1 as identifier
            $apiId = $tourData[$codeField] ?? null; // Use code as fallback for api_id field
        } else {
            // For other APIs: Use api_id as standard
            $existingTour = TourModel::where([
                'api_id' => $apiId,
                'api_type' => $provider->code
            ])->whereNull('deleted_at')->first();
        }
        
        // Universal code1 check for all APIs to prevent duplicate constraint violations
        if (!$existingTour) {
            // Get the field mapping for code1 to know which API field maps to code1
            $code1Mapping = $provider->fieldMappings()
                ->where('field_type', 'tour')
                ->where('local_field', 'code1')
                ->first();
            
            if ($code1Mapping && isset($tourData[$code1Mapping->api_field])) {
                $code1Value = $tourData[$code1Mapping->api_field];
                
                if (!empty($code1Value)) {
                    $existingTour = TourModel::where('code1', $code1Value)
                        ->whereNull('deleted_at')
                        ->first();
                    
                    if ($existingTour) {
                        // Log::info('Found existing tour by code1 (universal check)', [
                            // 'provider' => $provider->code,
                            // 'api_field' => $code1Mapping->api_field,
                            // 'code1' => $code1Value,
                            // 'tour_id' => $existingTour->id,
                            // 'existing_api_type' => $existingTour->api_type
                        // ]);
                    }
                }
            } else {
                // Fallback: Try common field names if no mapping found
                $possibleCodeFields = ['tour_code', 'code', 'id', 'product_code', 'program_code'];
                foreach ($possibleCodeFields as $field) {
                    if (isset($tourData[$field]) && !empty($tourData[$field])) {
                        $existingTour = TourModel::where('code1', $tourData[$field])
                            ->whereNull('deleted_at')
                            ->first();
                        
                        if ($existingTour) {
                            // Log::info('Found existing tour by code1 (fallback check)', [
                                // 'provider' => $provider->code,
                                // 'field_used' => $field,
                                // 'code1' => $tourData[$field],
                                // 'tour_id' => $existingTour->id,
                                // 'existing_api_type' => $existingTour->api_type
                            // ]);
                            break;
                        }
                    }
                }
            }
        }

        if ($existingTour) {
            // ทัวร์มีอยู่แล้ว - อัปเดทข้อมูลแทนการสร้างใหม่
            // Log::info('Updating existing tour', [
                // 'provider' => $provider->code,
                // 'tour_id' => $existingTour->id,
                // 'api_id' => $apiId
            // ]);
            
            // Update existing tour with new data from API (with check_change fields)
            try {
                // เช็ค check_change fields ก่อน map (เหมือน headcode เดิม)
                $this->mapTourFieldsFromConfig($provider, $tourData, $existingTour, true); // true = is updating
                
                // สำหรับ TTN Japan - เพิ่ม city_id logic
                if ($provider->code === 'ttn' && isset($tourData['P_LOCATION'])) {
                    $this->applyTTNJapanCityLogic($tourData, $existingTour, true);
                }
            } catch (\Exception $e) {
                // Log::error('Error updating tour fields', [
                    // 'provider' => $provider->code,
                    // 'tour_id' => $existingTour->id,
                    // 'error' => $e->getMessage()
                // ]);
                throw $e;
            }
            
            // Process image and PDF updates (with check_change)
            $this->processImage($provider, $tourData, $existingTour, true); // true = is updating
            $this->processPDF($provider, $tourData, $existingTour);
            
            $existingTour->save();
            
            // Update periods - remove old ones and create new ones
            // ProBookingCenter: Skip period processing here (done in separate section)
            if ($provider->code === 'probookingcenter') {
                $updatedPeriodsCount = 0; // Periods will be processed later
            } else {
                $oldPeriodsCount = $existingTour->period()->count();
                $existingTour->period()->delete();
                $updatedPeriodsCount = $this->processPeriods($provider, $tourData, $existingTour);
                
                // Update tour price based on new periods
                $this->updateTourPrice($existingTour);
            }
            
            // Log as duplicate for tracking
            TourDuplicateModel::create([
                'api_provider_id' => $provider->id,
                'sync_log_id' => $syncLog->id,
                'api_id' => $apiId,
                'existing_tour_id' => $existingTour->id,
                'api_data' => $tourData,
                'comparison_result' => [
                    'similarity_score' => 100,
                    'matching_fields' => ['api_id'],
                    'differences' => []
                ],
                'status' => 'pending'  // Use pending so it shows in duplicates page
            ]);
            
            return [
                'action' => 'duplicated',  // Changed from 'updated' to 'duplicated' - existing tours counted as duplicates
                'tour_id' => $existingTour->id, 
                'tour_model' => $existingTour,
                'periods_count' => $updatedPeriodsCount
            ];
        }

        // สร้างทัวร์ใหม่
        $tourModel = new TourModel();
        
        // Generate tour code
        $tourCode = IdGenerator::generate([
            'table' => 'tb_tour',
            'field' => 'code',
            'length' => 10,
            'prefix' => 'NT' . date('ym'),
            'reset_on_prefix_change' => true
        ]);

        $tourModel->code = $tourCode;
        
        // ★★★ iTravels: Auto-generate api_id เพราะ API ไม่ได้ส่ง tour id มา ★★★
        // สร้าง unique api_id จาก max(api_id) + 1 สำหรับ iTravels
        // api_id ไม่ได้เป็น AUTO_INCREMENT ต้องรันเอง
        if ($provider->code === 'itravel' || $provider->code === 'itravels') {
            // หา max api_id ที่เป็นตัวเลขสำหรับ provider นี้
            $maxApiId = \DB::table('tb_tour')
                ->where('api_type', $provider->code)
                ->whereNotNull('api_id')
                ->max(\DB::raw('CAST(api_id AS UNSIGNED)'));
            
            // Generate next api_id (เริ่มจาก 1 ถ้ายังไม่มีเลย)
            $nextApiId = ($maxApiId && $maxApiId > 0) ? ($maxApiId + 1) : 1;
            $tourModel->api_id = $nextApiId;
            
            Log::info('iTravels: Auto-generated api_id', [
                'provider' => $provider->code,
                'max_api_id' => $maxApiId,
                'new_api_id' => $tourModel->api_id,
                'code1' => $tourData['code'] ?? 'UNKNOWN'
            ]);
        } else {
            $tourModel->api_id = $apiId;  // ใช้ api_id จาก API สำหรับ API อื่นๆ
        }
        
        // ⚠️ CHECK DUPLICATE CODE1 BEFORE MAPPING - Use API data directly
        $code1Mapping = $provider->fieldMappings()
            ->where('field_type', 'tour')
            ->where('local_field', 'code1')
            ->first();
        
        if ($code1Mapping && isset($tourData[$code1Mapping->api_field])) {
            $code1Value = $tourData[$code1Mapping->api_field];
            
            if (!empty($code1Value)) {
                // Log::info('Checking duplicate code1 from API data', [
                    // 'provider' => $provider->code,
                    // 'api_field' => $code1Mapping->api_field,
                    // 'code1_value' => $code1Value,
                    // 'api_id' => $apiId
                // ]);
                
                // Use transaction lock to prevent race condition
                // IMPORTANT: Check including soft deleted tours because uk_tour_code1 constraint checks ALL rows
                $duplicateTour = \DB::transaction(function() use ($code1Value) {
                    // First check active tours
                    $activeTour = TourModel::where('code1', $code1Value)
                        ->whereNull('deleted_at')
                        ->lockForUpdate()
                        ->first();
                    
                    if ($activeTour) {
                        return $activeTour;
                    }
                    
                    // Also check soft deleted tours (constraint will fail anyway)
                    return TourModel::where('code1', $code1Value)
                        ->whereNotNull('deleted_at')
                        ->lockForUpdate()
                        ->first();
                });
                
                if ($duplicateTour) {
                    $isDeleted = $duplicateTour->deleted_at !== null;
                    
                    // Special handling for Tour Factory - more detailed logging
                    // if ($provider->code === 'tourfactory' || $provider->code === 'tour_factory') {
                    //     Log::warning('TourFactory: Duplicate code1 detected', [
                    //         'code1' => $code1Value,
                    //         'existing_tour_id' => $duplicateTour->id,
                    //         'existing_api_type' => $duplicateTour->api_type,
                    //         'is_deleted' => $isDeleted,
                    //         'new_api_id' => $apiId
                    //     ]);
                    // }
                    
                    // Log::warning('Duplicate code1 found - SKIPPING tour creation', [
                        // 'provider' => $provider->code,
                        // 'code1' => $code1Value,
                        // 'existing_tour_id' => $duplicateTour->id,
                        // 'existing_api_type' => $duplicateTour->api_type,
                        // 'is_deleted' => $isDeleted,
                        // 'deleted_at' => $duplicateTour->deleted_at,
                        // 'api_id' => $apiId
                    // ]);
                    
                    // Log as duplicate
                    TourDuplicateModel::create([
                        'api_provider_id' => $provider->id,
                        'sync_log_id' => $syncLog->id,
                        'api_id' => $apiId,
                        'existing_tour_id' => $duplicateTour->id,
                        'api_data' => $tourData,
                        'status' => 'duplicate_skipped'
                    ]);
                    
                    return [
                        'action' => 'duplicated',  // Changed from 'skipped' to 'duplicated' for consistency
                        'reason' => 'duplicate_code1',
                        'tour_id' => $duplicateTour->id,
                        'tour_model' => $duplicateTour,
                        'periods_count' => 0
                    ];
                }
                
                // Log::info('No duplicate found, proceeding with tour creation', [
                    // 'code1' => $code1Value
                // ]);
            }
        }
        
        // จัดการ wholesale_id ให้เฉพาะแต่ละ API (เหมือน headcode เดิม)
        $tourModel->group_id = 3; // Wholesale group
        if ($provider->code === 'ttn') {
            $tourModel->wholesale_id = 35; // TTN Japan
        } elseif ($provider->code === 'go365') {
            $tourModel->wholesale_id = 41; // GO365
        } elseif ($provider->code === 'qebooking') {
            $tourModel->wholesale_id = 67; // QeBooking
        } elseif ($provider->code === 'probookingcenter') {
            $tourModel->wholesale_id = 39; // ProBookingCenter
        } elseif ($provider->code === 'superbholiday' || $provider->code === 'superb_holiday') {
            $tourModel->wholesale_id = 22; // Super Holiday (ตาม headcode)
            $tourModel->group_id = 3; // Super Holiday (ตาม headcode)
        } elseif ($provider->code === 'zego') {
            $tourModel->wholesale_id = null; // Zego ไม่มี wholesale_id ตาม headcode
        } elseif ($provider->code === 'best') {
            $tourModel->wholesale_id = null; // Best ไม่มี wholesale_id ตาม headcode
        }
        
        // Map fields จาก API data using database mappings (including static values like api_type, data_type)
        try {
            $this->mapTourFieldsFromConfig($provider, $tourData, $tourModel, false); // false = is new
        } catch (\Exception $e) {
            // Log::error('Error in mapTourFieldsFromConfig', [
                // 'provider' => $provider->code,
                // 'error' => $e->getMessage(),
                // 'line' => $e->getLine(),
                // 'file' => $e->getFile(),
                // 'trace' => $e->getTraceAsString()
            // ]);
            throw $e;
        }
        
        // For Super Holiday, if code1 is empty, use api_id to avoid unique constraint violation
        if (($provider->code === 'superholiday' || $provider->code === 'super_holiday') && empty($tourModel->code1)) {
            $tourModel->code1 = $apiId;
            // Log::info('Super Holiday: Set code1 to api_id (was empty)', [
                // 'api_id' => $apiId,
                // 'tour_code' => $tourModel->code
            // ]);
        }
        
        // สำหรับ TTN Japan - เพิ่ม city_id logic (เหมือน headcode เดิม)
        if ($provider->code === 'ttn' && isset($tourData['P_LOCATION'])) {
            $this->applyTTNJapanCityLogic($tourData, $tourModel, false);
        }
        
        // Set image_check_change = 2 สำหรับทัวร์ใหม่ (ให้ดึงรูปจาก API)
        $tourModel->image_check_change = 2;
        
        // Process image if exists
        $this->processImage($provider, $tourData, $tourModel, false); // false = creating new
        
        // Process PDF if exists  
        $this->processPDF($provider, $tourData, $tourModel);
        
        // *** DEBUG: Log all attributes before save ***
        // Log::info('About to save new tour', [
            // 'provider' => $provider->code,
            // 'api_id' => $apiId,
            // 'code' => $tourModel->code,
            // 'code1' => $tourModel->code1,
            // 'name' => $tourModel->name,
            // 'all_attributes' => $tourModel->getAttributes()
        // ]);
        
        try {
            $tourModel->save();
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle duplicate constraint violations - just skip and log
            if ($e->getCode() == 23000 && strpos($e->getMessage(), 'Duplicate entry') !== false) {
                // Log::warning('Caught duplicate constraint violation during save - SKIPPING', [
                    // 'provider' => $provider->code,
                    // 'code1' => $tourModel->code1,
                    // 'api_id' => $apiId,
                    // 'error' => $e->getMessage()
                // ]);
                
                // Try to find the existing tour
                $existingTour = TourModel::where('code1', $tourModel->code1)
                    ->whereNull('deleted_at')
                    ->first();
                
                if (!$existingTour) {
                    $existingTour = TourModel::where([
                        'api_id' => $apiId,
                        'api_type' => $provider->code
                    ])->whereNull('deleted_at')->first();
                }
                
                if ($existingTour) {
                    // Log as duplicate
                    TourDuplicateModel::create([
                        'api_provider_id' => $provider->id,
                        'sync_log_id' => $syncLog->id,
                        'api_id' => $apiId,
                        'existing_tour_id' => $existingTour->id,
                        'api_data' => $tourData,
                        'status' => 'duplicate_constraint'
                    ]);
                    
                    return [
                        'action' => 'skipped', 
                        'reason' => 'duplicate_constraint',
                        'tour_id' => $existingTour->id, 
                        'tour_model' => $existingTour,
                        'periods_count' => 0
                    ];
                }
            }
            
            // Re-throw if not a duplicate constraint violation or couldn't find existing tour
            // Log::error('Failed to save tour, re-throwing exception', [
                // 'provider' => $provider->code,
                // 'api_id' => $apiId,
                // 'error' => $e->getMessage()
            // ]);
            throw $e;
        }
        
        // Process periods if exists
        $createdPeriodsCount = $this->processPeriods($provider, $tourData, $tourModel);
        
        // TTN Japan/All: ถ้าไม่มี periods ให้ลบ tour (ไม่ sync tours ที่ไม่มี periods)
        if (($provider->code === 'ttn_japan' || $provider->code === 'ttn_all')) {
            $periodCount = $tourModel->period()->count();
            if ($periodCount === 0) {
                // Log::info('TTN Japan: Deleting tour without periods', [
                    // 'provider' => $provider->code,
                    // 'tour_id' => $tourModel->id,
                    // 'tour_code' => $tourModel->code,
                    // 'api_id' => $tourModel->api_id
                // ]);
                $tourModel->delete();
                return ['action' => 'skipped', 'reason' => 'no_periods', 'periods_count' => 0];
            }
        }
        
        // Update tour price based on periods
        $this->updateTourPrice($tourModel);
        
        return [
            'action' => 'created', 
            'tour_id' => $tourModel->id, 
            'tour_model' => $tourModel,
            'periods_count' => $createdPeriodsCount
        ];
    }

    private function mapTourFieldsFromConfig($provider, $tourData, $tourModel, $isUpdating = false)
    {
        $mappings = $provider->fieldMappings()->where('field_type', 'tour')->get();
        
        foreach ($mappings as $mapping) {
            $apiValue = $tourData[$mapping->api_field] ?? null;
            
            // Skip pdf_file mapping - let processPDF handle it (supports nested fields)
            if ($mapping->local_field === 'pdf_file') {
                continue;
            }
            
            // Skip image mapping - let processImage() handle it (download + resize เหมือน headcode เดิม)
            if ($mapping->local_field === 'image') {
                continue;
            }
            
            // Handle static value mappings (where api_field is empty)
            if (empty($mapping->api_field) && !empty($mapping->transformation_rules)) {
                $rules = is_string($mapping->transformation_rules) ? json_decode($mapping->transformation_rules, true) : $mapping->transformation_rules;
                
                // Handle array of rules or single rule
                if (is_array($rules)) {
                    // Check if it's array of rules (has numeric keys)
                    if (isset($rules[0]) && is_array($rules[0])) {
                        foreach ($rules as $rule) {
                            if (isset($rule['type']) && $rule['type'] === 'static_value' && isset($rule['value'])) {
                                $tourModel->{$mapping->local_field} = $rule['value'];
                                break;
                            }
                        }
                    } else {
                        // Single rule object
                        if (isset($rules['type']) && $rules['type'] === 'static_value' && isset($rules['value'])) {
                            $tourModel->{$mapping->local_field} = $rules['value'];
                        } elseif (isset($rules['static_value'])) {
                            // Legacy format
                            $tourModel->{$mapping->local_field} = $rules['static_value'];
                        }
                    }
                    continue;
                }
            }
            
            // Handle nested fields (e.g., tour_file.file_pdf for GO365)
            if (!empty($mapping->transformation_rules)) {
                $rules = is_string($mapping->transformation_rules) ? json_decode($mapping->transformation_rules, true) : $mapping->transformation_rules;
                if (isset($rules['nested_field'])) {
                    // Extract nested value from array/object
                    if (is_array($apiValue) && isset($apiValue[$rules['nested_field']])) {
                        $apiValue = $apiValue[$rules['nested_field']];
                        // Log::info('Extracted nested field', [
                            // 'provider' => $provider->code,
                            // 'local_field' => $mapping->local_field,
                            // 'api_field' => $mapping->api_field,
                            // 'nested_field' => $rules['nested_field'],
                            // 'value' => $apiValue
                        // ]);
                    }
                }
            }
            
            if ($apiValue !== null) {
                // เช็ค check_change fields ก่อน update (เหมือน headcode เดิม 100%)
                // ถ้าเป็น update และ user แก้ไขไว้แล้ว = ข้าม field นั้น
                if ($isUpdating) {
                    if ($mapping->local_field === 'name' && $tourModel->name_check_change != null) {
                        continue;
                    }
                    if ($mapping->local_field === 'country_id' && $tourModel->country_check_change != null) {
                        continue;
                    }
                    if ($mapping->local_field === 'airline_id' && $tourModel->airline_check_change != null) {
                        continue;
                    }
                    if ($mapping->local_field === 'description' && $tourModel->description_check_change != null) {
                        continue;
                    }
                }
                
                // Handle special field mappings
                if ($mapping->local_field === 'country_id' && $mapping->api_field === 'CountryName') {
                    // Find country by name and create JSON array
                    $country = \App\Models\Backend\CountryModel::where('country_name_en', 'like', '%' . $apiValue . '%')
                        ->where('status', 'on')
                        ->whereNull('deleted_at')
                        ->first();
                    
                    if ($country) {
                        $tourModel->country_id = json_encode([$country->id]);
                    } else {
                        $tourModel->country_id = '[]';
                    }
                } elseif ($mapping->local_field === 'country_id' && $mapping->api_field === 'Country') {
                    // Handle Super Holiday Country field - find by country_name_th (like headcode)
                    if ($apiValue) {
                        $country = \App\Models\Backend\CountryModel::where('country_name_th', 'like', '%' . $apiValue . '%')
                            ->where('status', 'on')
                            ->whereNull('deleted_at')
                            ->first();
                        
                        if ($country) {
                            $tourModel->country_id = json_encode([(string)$country->id]);
                            
                            // Log::info('Super Holiday: Country mapped', [
                                // 'country_name' => $apiValue,
                                // 'country_id' => $country->id
                            // ]);
                        } else {
                            $tourModel->country_id = '[]';
                        }
                    }
                } elseif ($mapping->local_field === 'country_id' && $mapping->api_field === 'tour_country') {
                    // Handle GO365 tour_country array - use country_code_2 (iso2) like headcode
                    $countryIds = [];
                    if (is_array($apiValue)) {
                        foreach ($apiValue as $countryObj) {
                            if (isset($countryObj['country_code_2'])) {
                                // Find country by iso2 code (more accurate than using country_id from API)
                                $country = \App\Models\Backend\CountryModel::where('iso2', $countryObj['country_code_2'])
                                    ->where('status', 'on')
                                    ->whereNull('deleted_at')
                                    ->first();
                                if ($country) {
                                    $countryIds[] = (string)$country->id;
                                }
                            }
                        }
                    }
                    $tourModel->country_id = json_encode($countryIds);
                } elseif ($mapping->local_field === 'country_id' && $mapping->api_field === 'countries') {
                    // Handle Checkin Group countries array - has "name", "code" (iso2), "icon"
                    $countryIds = [];
                    if (is_array($apiValue)) {
                        $processedCodes = []; // ป้องกัน duplicate (บางทีส่ง JP มา 2 records)
                        foreach ($apiValue as $countryObj) {
                            $countryCode = $countryObj['code'] ?? null;
                            if ($countryCode && !in_array($countryCode, $processedCodes)) {
                                // Find country by iso2 code
                                $country = \App\Models\Backend\CountryModel::where('iso2', $countryCode)
                                    ->where('status', 'on')
                                    ->whereNull('deleted_at')
                                    ->first();
                                if ($country) {
                                    $countryIds[] = (string)$country->id;
                                    $processedCodes[] = $countryCode;
                                    
                                    // Log::info('Checkin Group: Country mapped', [
                                        // 'country_name' => $countryObj['name'] ?? '',
                                        // 'country_code' => $countryCode,
                                        // 'country_id' => $country->id
                                    // ]);
                                }
                            }
                        }
                    }
                    $tourModel->country_id = json_encode($countryIds);
                } elseif (in_array($mapping->local_field, ['country_id', 'city_id', 'province_id']) && !empty($mapping->transformation_rules)) {
                    // Handle location detection from tour name using transformation rules (generic for all APIs)
                    if (!empty($mapping->transformation_rules)) {
                        $rules = is_string($mapping->transformation_rules) ? json_decode($mapping->transformation_rules, true) : $mapping->transformation_rules;
                        $detectionType = $rules['type'] ?? null;
                        if ($detectionType && str_ends_with($detectionType, '_detection_from_name') && isset($rules['rules'])) {
                            $detectedIds = [];
                            $sourceName = (string)$apiValue;
                            $fieldName = $mapping->local_field;
                            
                            // Check each rule pattern in source text
                            foreach ($rules['rules'] as $pattern => $locationId) {
                                if (strpos($sourceName, $pattern) !== false) {
                                    $detectedIds[] = (string)$locationId;
                                    // For country, take only first match; for city/province, allow multiple
                                    if ($fieldName === 'country_id') {
                                        break;
                                    }
                                }
                            }
                            
                            $tourModel->{$fieldName} = json_encode($detectedIds);
                            
                            // Log::info('Location detection', [
                                // 'provider' => $provider->code,
                                // 'field' => $fieldName,
                                // 'source_name' => $sourceName,
                                // 'detected_ids' => $detectedIds,
                                // 'final_value' => $tourModel->{$fieldName}
                            // ]);
                        }
                    }

                } elseif ($mapping->local_field === 'airline_id' && $mapping->api_field === 'AirlineCode') {
                    // Find airline by code
                    $airline = \App\Models\Backend\TravelTypeModel::where('code', $apiValue)
                        ->where('status', 'on')
                        ->whereNull('deleted_at')
                        ->first();
                    
                    if ($airline) {
                        $tourModel->airline_id = $airline->id;
                    }
                } elseif ($mapping->local_field === 'airline_id' && $mapping->api_field === 'aey') {
                    // Handle Super Holiday aey field - extract code from "(CODE)" format
                    // Example: "THAI AIRWAYS (TG)" -> "TG"
                    if ($apiValue) {
                        $code = null;
                        if (preg_match('/\(([^)]+)\)/', $apiValue, $matches)) {
                            $code = trim($matches[1]);
                        }
                        
                        if ($code) {
                            $airline = \App\Models\Backend\TravelTypeModel::where('code', $code)
                                ->where('status', 'on')
                                ->whereNull('deleted_at')
                                ->first();
                            
                            if ($airline) {
                                $tourModel->airline_id = $airline->id;
                                
                                // Log::info('Super Holiday: Extracted airline code', [
                                    // 'original_value' => $apiValue,
                                    // 'extracted_code' => $code,
                                    // 'airline_id' => $airline->id
                                // ]);
                            }
                        }
                    }
                } elseif ($mapping->local_field === 'airline_id' && $mapping->api_field === 'tour_airline') {
                    // Handle GO365 tour_airline object - extract airline_iata (เหมือน headcode)
                    // headcode: if(isset($call1['tour_airline']['airline_iata']) && $call1['tour_airline']['airline_iata']){
                    //           $airline = TravelTypeModel::where('code',$call1['tour_airline']['airline_iata'])->...
                    if (is_array($apiValue) && isset($apiValue['airline_iata']) && $apiValue['airline_iata']) {
                        $airline = \App\Models\Backend\TravelTypeModel::where('code', $apiValue['airline_iata'])
                            ->where('status', 'on')
                            ->whereNull('deleted_at')
                            ->first();
                        if ($airline) {
                            $tourModel->airline_id = $airline->id;
                        }
                    }
                } elseif ($mapping->local_field === 'airline_id' && $mapping->api_field === 'vehicle') {
                    // Handle Checkin Group vehicle field - extract code from "(CODE)" format
                    // Example: "AirAsia X (XJ)" -> "XJ"
                    if ($apiValue && is_string($apiValue)) {
                        $code = null;
                        if (preg_match('/\(([A-Z0-9]+)\)/', $apiValue, $matches)) {
                            $code = trim($matches[1]);
                        }
                        
                        if ($code) {
                            // Use orderBy('id', 'desc') to get the latest record (some codes have duplicates)
                            $airline = \App\Models\Backend\TravelTypeModel::where('code', $code)
                                ->where('status', 'on')
                                ->whereNull('deleted_at')
                                ->orderBy('id', 'desc')
                                ->first();
                            
                            if ($airline) {
                                $tourModel->airline_id = $airline->id;
                                
                                // Log::info('Checkin Group: Extracted airline code from vehicle', [
                                    // 'original_value' => $apiValue,
                                    // 'extracted_code' => $code,
                                    // 'airline_id' => $airline->id
                                // ]);
                            } else {
                                // Log::warning('Checkin Group: Airline not found', [
                                    // 'original_value' => $apiValue,
                                    // 'extracted_code' => $code
                                // ]);
                            }
                        }
                    }
                } elseif ($mapping->local_field === 'airline_name' && $mapping->api_field === 'airline_name') {
                    // Handle Best Consortium airline_name - find airline by name
                    if ($apiValue) {
                        $airline = \App\Models\Backend\TravelTypeModel::where('travel_name', 'like', '%' . $apiValue . '%')
                            ->where('status', 'on')
                            ->whereNull('deleted_at')
                            ->first();
                        if ($airline) {
                            $tourModel->airline_id = $airline->id;
                        }
                    }
                } elseif ($mapping->local_field === 'day' && $mapping->api_field === 'day') {
                    // Handle Checkin Group day field - create num_day string
                    // Note: tb_tour doesn't have 'day' and 'night' columns, only 'num_day' (varchar)
                    $nightValue = $tourData['night'] ?? null;
                    if ($apiValue && $nightValue !== null) {
                        $tourModel->num_day = (int)$apiValue . ' วัน ' . (int)$nightValue . ' คืน';
                        
                        // Log::info('Checkin Group: Set num_day', [
                            // 'day' => $apiValue,
                            // 'night' => $nightValue,
                            // 'num_day' => $tourModel->num_day
                        // ]);
                    }
                } else {
                    // Skip fields that are arrays and should be handled specially (like periods, plans, countries)
                    // These fields don't have a direct string mapping
                    if (is_array($apiValue) && in_array($mapping->api_field, ['periods', 'plans', 'countries', 'tour_country', 'tour_airline', 'vehicle'])) {
                        // Skip - these are handled elsewhere or are structural data
                        continue;
                    }
                    
                    // If apiValue is still an array but not in skip list, convert to JSON
                    if (is_array($apiValue)) {
                        $apiValue = json_encode($apiValue);
                    }
                    
                    // Apply transformation rules if exists
                    if (!empty($mapping->transformation_rules)) {
                        $apiValue = $this->applyTransformationRules($apiValue, $mapping->transformation_rules);
                    }
                    
                    // Handle different data types
                    $processedValue = $this->processFieldValue($apiValue, $mapping->data_type ?? 'string');
                    
                    // Debug log
                    // Log::info('Mapping field', [
                        // 'field_type' => $mapping->field_type,
                        // 'local_field' => $mapping->local_field,
                        // 'api_field' => $mapping->api_field,
                        // 'value' => $processedValue
                    // ]);
                    
                    // Set value to model
                    $tourModel->{$mapping->local_field} = $processedValue;
                }
            }
        }
        
        // ★★★ iTravels: Hardcode airline_id logic เหมือน headcode 100% ★★★
        // headcode: $code_airline = substr($call1['departure_by'], 0, 2);
        // iTravels API ไม่มี country field และใช้ departure_by (เช่น "XJ606") เพื่อดึง airline code
        if ($provider->code === 'itravel' || $provider->code === 'itravels') {
            // Airline: ดึงจาก departure_by (เช่น "XJ606" → "XJ")
            if (!$isUpdating || $tourModel->airline_check_change === null) {
                $departureBy = $tourData['departure_by'] ?? null;
                if ($departureBy && $departureBy !== "null" && !empty($departureBy)) {
                    $code_airline = substr($departureBy, 0, 2);
                    
                    $airline = \App\Models\Backend\TravelTypeModel::where('code', $code_airline)
                        ->where('status', 'on')
                        ->whereNull('deleted_at')
                        ->first();
                    
                    if ($airline) {
                        $tourModel->airline_id = $airline->id;
                        
                        // Log::info('iTravels: Extracted airline code from departure_by', [
                            // 'departure_by' => $departureBy,
                            // 'extracted_code' => $code_airline,
                            // 'airline_id' => $airline->id
                        // ]);
                    }
                }
            }
            
            // Country: ดึงจากชื่อทัวร์ (headcode เดิมไม่มี country ใน API)
            if (!$isUpdating || $tourModel->country_check_change === null) {
                if (!empty($tourData['name'])) {
                    $detectedCountries = \App\Helpers\Helper::detectCountryFromName($tourData['name']);
                    if (!empty($detectedCountries)) {
                        $tourModel->country_id = json_encode($detectedCountries);
                    }
                }
            }
        }
        
        // ★★★ ProBookingCenter: Hardcode airline_id และ country_id logic ★★★
        // airline format: "Thai Vietjet (VZ)" - extract code from parentheses
        // country: ใช้ countryName field
        if ($provider->code === 'probookingcenter') {
            // Airline extraction (เหมือน TTN_ALL)
            if (!$isUpdating || !isset($tourModel->airline_check_change) || $tourModel->airline_check_change == null) {
                if (isset($tourData['airline']) && $tourData['airline']) {
                    $parts = explode('(', $tourData['airline']);
                    $code_airline = "";
                    if (isset($parts[1])) {
                        $code_airline = trim($parts[1], ') ');
                    }
                    
                    if ($code_airline) {
                        $airline = \App\Models\Backend\TravelTypeModel::where('code', $code_airline)
                            ->where('status', 'on')
                            ->whereNull('deleted_at')
                            ->first();
                            
                        if ($airline) {
                            $tourModel->airline_id = $airline->id;
                        }
                    }
                }
            }
            
            // Country from countryName field
            if (!$isUpdating || !isset($tourModel->country_check_change) || $tourModel->country_check_change == null) {
                if (isset($tourData['countryName']) && $tourData['countryName']) {
                    $country = \App\Models\Backend\CountryModel::where('country_name_en', 'like', '%' . $tourData['countryName'] . '%')
                        ->where('status', 'on')
                        ->whereNull('deleted_at')
                        ->first();
                        
                    if ($country) {
                        $tourModel->country_id = json_encode([(string)$country->id]);
                    } else {
                        $tourModel->country_id = '[]';
                    }
                }
            }
        }
        
        // Set wholesale_id and group_id from provider config
        if (isset($provider->config['wholesale_id'])) {
            $tourModel->wholesale_id = $provider->config['wholesale_id'];
        }
        
        if (isset($provider->config['group_id'])) {
            $tourModel->group_id = $provider->config['group_id'];
        }
        
        // Apply special logic for TTN APIs country_id and city_id
        $this->handleTTNCountryAndCity($provider, $tourData, $tourModel);
        
        // Apply TTN Japan/All specific logic (like original ApiController.php)
        if ($provider->code === 'ttn' || $provider->code === 'ttn_japan' || $provider->code === 'ttn_all') {
            $this->applyTTNJapanSpecificLogic($provider, $tourData, $tourModel, $isUpdating);
        }
        
        // Apply Best Consortium specific logic (like original ApiController.php)
        if ($provider->code === 'bestconsortium' || $provider->code === 'best') {
            $this->applyBestConsortiumSpecificLogic($provider, $tourData, $tourModel, $isUpdating);
        }
        
        // Apply conditions
        $this->applyConditions($provider, $tourData, $tourModel);
    }

    private function processFieldValue($value, $dataType)
    {
        switch ($dataType) {
            case 'json':
                // Convert arrays/objects to JSON string
                if (is_array($value) || is_object($value)) {
                    return json_encode($value);
                }
                return $value;
                
            case 'string':
                // Convert to string, handle arrays by taking first element or converting to string
                if (is_array($value)) {
                    if (count($value) > 0) {
                        // Get first element (works for both indexed and associative arrays)
                        $firstElement = reset($value);
                        // If first element is also an array/object, convert to JSON
                        if (is_array($firstElement) || is_object($firstElement)) {
                            return json_encode($firstElement);
                        }
                        return (string)$firstElement;
                    }
                    return '';
                }
                if (is_object($value)) {
                    return json_encode($value);
                }
                return (string)$value;
                
            case 'integer':
                if (is_array($value)) {
                    return 0; // Return 0 for array values in integer context
                }
                return (int)$value;
                
            case 'decimal':
            case 'float':
                if (is_array($value)) {
                    return 0.0; // Return 0.0 for array values in decimal/float context
                }
                return (float)$value;
                
            case 'date':
                if ($value) {
                    try {
                        $date = new \DateTime($value);
                        return $date->format('Y-m-d');
                    } catch (\Exception $e) {
                        return null;
                    }
                }
                return null;
                
            case 'datetime':
                if ($value) {
                    try {
                        $date = new \DateTime($value);
                        return $date->format('Y-m-d H:i:s');
                    } catch (\Exception $e) {
                        return null;
                    }
                }
                return null;
                
            default:
                // Safety: convert arrays to JSON to prevent "Array to string conversion" error
                if (is_array($value) || is_object($value)) {
                    return json_encode($value);
                }
                return $value;
        }
    }

    private function applyTransformationRules($value, $rules)
    {
        // If rules is a JSON string, decode it
        if (is_string($rules)) {
            $rules = json_decode($rules, true);
            if (!$rules) return $value;
        }
        
        // Handle single rule object (not array of rules)
        if (!isset($rules[0]) && isset($rules['type'])) {
            $rules = [$rules];
        }
        
        foreach ($rules as $rule) {
            switch ($rule['type'] ?? '') {
                case 'static_value':
                    // For static values, ignore input and return the static value
                    return $rule['value'] ?? $rule['static_value'] ?? $value;
                    break;
                case 'string_replace':
                    $value = str_replace($rule['search'] ?? '', $rule['replace'] ?? '', $value);
                    break;
                case 'json_encode':
                    if (is_array($value)) {
                        $value = json_encode($value);
                    }
                    break;
                case 'array_wrap':
                    if (!is_array($value)) {
                        $value = [$value];
                    }
                    $value = json_encode($value);
                    break;
                case 'date_format_conversion':
                    if ($value) {
                        try {
                            $fromFormat = $rule['from_format'] ?? 'm/d/Y';
                            $toFormat = $rule['to_format'] ?? 'Y-m-d';
                            
                            // Create date from specific format
                            $date = \DateTime::createFromFormat($fromFormat, $value);
                            if ($date) {
                                $value = $date->format($toFormat);
                            }
                        } catch (\Exception $e) {
                            // Log::warning("Date conversion failed: " . $e->getMessage(), [
                                // 'value' => $value,
                                // 'from_format' => $fromFormat,
                                // 'to_format' => $toFormat
                            // ]);
                        }
                    }
                    break;
                case 'status_conversion':
                    if (isset($rule['rules'][$value])) {
                        $value = $rule['rules'][$value];
                    } elseif ($rule['fallback'] === 'numeric' && is_numeric($value)) {
                        $value = (int)$value;
                    }
                    break;
                case 'extract_days_from_time':
                    if ($value && isset($rule['pattern'])) {
                        // Handle Thai text like "3 วัน 2 คืน"
                        if (preg_match('/(\d+)\s*วัน/', $value, $matches)) {
                            $value = (int)$matches[1];
                        } else {
                            $value = $rule['fallback'] ?? 1;
                        }
                    }
                    break;
                case 'extract_nights_from_time':
                    if ($value && isset($rule['pattern'])) {
                        // Handle Thai text like "3 วัน 2 คืน"
                        if (preg_match('/(\d+)\s*คืน/', $value, $matches)) {
                            $value = (int)$matches[1];
                        } else {
                            $value = $rule['fallback'] ?? 0;
                        }
                    }
                    break;
                case 'date_to_month_year':
                    if ($value) {
                        try {
                            $fromFormat = $rule['from_format'] ?? 'm/d/Y';
                            $toFormat = $rule['to_format'] ?? 'mY';
                            
                            $date = \DateTime::createFromFormat($fromFormat, $value);
                            if ($date) {
                                $value = $date->format($toFormat);
                            }
                        } catch (\Exception $e) {
                            // Log::warning("Date to month-year conversion failed: " . $e->getMessage());
                        }
                    }
                    break;
                case 'status_to_period_status':
                    if (isset($rule['rules'][$value])) {
                        $value = $rule['rules'][$value];
                    } else {
                        $value = $rule['fallback'] ?? 1;
                    }
                    break;
            }
        }
        
        return $value;
    }

    private function applyConditions($provider, $tourData, $tourModel)
    {
        $conditions = $provider->conditions()->where('is_active', true)->orderBy('priority')->get();
        
        foreach ($conditions as $condition) {
            $this->executeCondition($condition, $tourData, $tourModel);
        }
    }

    private function executeCondition($condition, $tourData, $tourModel)
    {
        // Check if condition applies to this data
        if (!$this->checkCondition($condition, $tourData)) {
            return; // Condition doesn't match, skip
        }
        
        $rules = $condition->condition_rules;
        $actionRules = $condition->action_rules;
        
        switch ($condition->condition_type) {
            case 'country_mapping':
                $this->executeCountryMapping($condition, $tourData, $tourModel);
                break;
            case 'airline_mapping':
                $this->executeAirlineMapping($condition, $tourData, $tourModel);
                break;
            case 'image_processing':
                $this->executeImageProcessing($condition, $tourData, $tourModel);
                break;
            case 'data_update_check':
                $this->executeDataUpdateCheck($condition, $tourData, $tourModel);
                break;
            case 'field_transformation':
                $this->executeFieldTransformation($condition, $tourData, $tourModel);
                break;
            case 'text_processing':
                $this->executeTextProcessing($condition, $tourData, $tourModel);
                break;
            case 'data_validation':
                $this->executeDataValidation($condition, $tourData, $tourModel);
                break;
        }
    }
    
    private function checkCondition($condition, $tourData)
    {
        $fieldValue = $tourData[$condition->field_name] ?? null;
        
        switch ($condition->operator) {
            case 'EXISTS':
                return !empty($fieldValue);
            case 'NOT EXISTS':
                return empty($fieldValue);
            case '=':
                return $fieldValue == $condition->value;
            case '!=':
                return $fieldValue != $condition->value;
            case 'LIKE':
                return strpos($fieldValue, $condition->value) !== false;
            case 'NOT LIKE':
                return strpos($fieldValue, $condition->value) === false;
            default:
                return false;
        }
    }
    
    private function executeCountryMapping($condition, $tourData, $tourModel)
    {
        $fieldValue = $tourData[$condition->field_name] ?? null;
        if (!$fieldValue) return;
        
        $rules = $condition->condition_rules;
        
        try {
            $country = \App\Models\Backend\CountryModel::where('country_name_en', 'like', '%' . $fieldValue . '%')
                ->where('status', 'on')
                ->whereNull('deleted_at')
                ->first();
                
            if ($country) {
                $tourModel->country_id = json_encode([$country->id]);
                // Log::info('Country mapping executed', [
                    // 'field_value' => $fieldValue,
                    // 'country_id' => $country->id,
                    // 'country_name' => $country->country_name_en
                // ]);
            } else {
                $tourModel->country_id = '[]';
                // Log::info('Country mapping - no match found', [
                    // 'field_value' => $fieldValue
                // ]);
            }
        } catch (\Exception $e) {
            // Log::error('Country mapping error: ' . $e->getMessage());
            $tourModel->country_id = '[]';
        }
    }
    
    private function executeAirlineMapping($condition, $tourData, $tourModel)
    {
        $fieldValue = $tourData[$condition->field_name] ?? null;
        if (!$fieldValue) return;
        
        try {
            $airline = \App\Models\Backend\TravelTypeModel::where('code', $fieldValue)
                ->where('status', 'on')
                ->whereNull('deleted_at')
                ->first();
                
            if ($airline) {
                $tourModel->airline_id = $airline->id;
                // Log::info('Airline mapping executed', [
                    // 'field_value' => $fieldValue,
                    // 'airline_id' => $airline->id,
                    // 'airline_name' => $airline->travel_name
                // ]);
            }
        } catch (\Exception $e) {
            // Log::error('Airline mapping error: ' . $e->getMessage());
        }
    }
    
    private function executeImageProcessing($condition, $tourData, $tourModel)
    {
        $fieldValue = $tourData[$condition->field_name] ?? null;
        if (!$fieldValue) return;
        
        $rules = $condition->condition_rules;
        $savePath = $rules['save_path'] ?? 'upload/tour/default/';
        
        try {
            $response = Http::withOptions(['verify' => false])->get($fieldValue);
            
            if ($response->successful()) {
                $filename = basename($fieldValue);
                
                if (!Storage::disk('public')->exists($savePath)) {
                    Storage::disk('public')->makeDirectory($savePath, 0755, true);
                }
                
                $lg = Image::make($response->body());
                $ext = explode("/", $lg->mime());
                
                $width = $rules['resize_width'] ?? 600;
                $height = $rules['resize_height'] ?? 600;
                $lg->resize($width, $height)->stream();
                
                $allowedExt = $rules['allowed_extensions'] ?? ['png', 'jpeg', 'jpg', 'webp'];
                if (in_array($ext[1], $allowedExt)) {
                    $newPath = $savePath . $filename;
                    Storage::disk('public')->put($newPath, $lg);
                    $tourModel->image = $newPath;
                    
                    // Log::info('Image processed via condition', [
                        // 'source_url' => $fieldValue,
                        // 'saved_path' => $newPath
                    // ]);
                }
            }
        } catch (\Exception $e) {
            // Log::error('Image processing condition error: ' . $e->getMessage());
        }
    }
    
    private function executeFieldTransformation($condition, $tourData, $tourModel)
    {
        $fieldValue = $tourData[$condition->field_name] ?? null;
        if ($fieldValue === null) return;
        
        // Convert array to JSON to prevent "Array to string conversion" error
        if (is_array($fieldValue)) {
            $fieldValue = json_encode($fieldValue);
        }
        
        $rules = $condition->condition_rules;
        $targetField = $rules['target_field'] ?? $condition->field_name;
        $dataType = $rules['data_type'] ?? 'string';
        
        $processedValue = $this->processFieldValue($fieldValue, $dataType);
        $tourModel->{$targetField} = $processedValue;
        
        // Log::info('Field transformation executed', [
            // 'source_field' => $condition->field_name,
            // 'target_field' => $targetField,
            // 'value' => $processedValue
        // ]);
    }
    
    private function executeTextProcessing($condition, $tourData, $tourModel)
    {
        $fieldValue = $tourData[$condition->field_name] ?? null;
        if (!$fieldValue) return;
        
        $rules = $condition->condition_rules;
        $targetField = $rules['target_field'] ?? $condition->field_name;
        $transformations = $rules['transformations'] ?? [];
        
        $processedValue = $fieldValue;
        foreach ($transformations as $transform) {
            if ($transform['type'] === 'string_replace') {
                $processedValue = str_replace($transform['search'], $transform['replace'], $processedValue);
            }
        }
        
        $tourModel->{$targetField} = $processedValue;
        
        // Log::info('Text processing executed', [
            // 'source_field' => $condition->field_name,
            // 'target_field' => $targetField,
            // 'original_value' => $fieldValue,
            // 'processed_value' => $processedValue
        // ]);
    }
    
    private function executeDataUpdateCheck($condition, $tourData, $tourModel)
    {
        // This handles conditional updates based on existing data
        $rules = $condition->condition_rules;
        $checkField = $rules['check_field'] ?? null;
        
        if ($checkField) {
            // Check if the field is null or needs update
            $currentValue = $tourModel->{$checkField} ?? null;
            if ($currentValue === null) {
                $executeAction = $rules['execute_action'] ?? null;
                
                if ($executeAction === 'country_mapping') {
                    $this->executeCountryMapping($condition, $tourData, $tourModel);
                } elseif ($executeAction === 'airline_mapping') {
                    $this->executeAirlineMapping($condition, $tourData, $tourModel);
                }
            }
        }
    }
    
    private function executeDataValidation($condition, $tourData, $tourModel)
    {
        // Implement data validation logic
        // This could skip records that don't meet certain criteria
    }

    /**
     * Apply TTN Japan specific city_id logic (เหมือน headcode เดิม 100%)
     */
    private function applyTTNJapanCityLogic($tourData, $tourModel, $isUpdating = false)
    {
        // เช็ค country_check_change ก่อน (เหมือน headcode)
        if ($isUpdating && $tourModel->country_check_change != null) {
            return; // user แก้ไขไว้แล้ว ข้ามไป
        }
        
        if (isset($tourData['P_LOCATION']) && !empty($tourData['P_LOCATION'])) {
            $city = \App\Models\Backend\CityModel::where('city_name_en', 'like', '%' . $tourData['P_LOCATION'] . '%')
                ->where('status', 'on')
                ->whereNull('deleted_at')
                ->first();
            
            if ($city) {
                $arr_ci = [(string)$city->id];
                $tourModel->city_id = json_encode($arr_ci);
            } else {
                $tourModel->city_id = '[]';
            }
        }
    }

    /**
     * Apply Best Consortium specific logic (เหมือน headcode เดิม 100%)
     * - Airline mapping: ใช้ airline_name แทน code (LIKE search)
     * - Country mapping: ใช้ country_name_eng (LIKE search)
     */
    private function applyBestConsortiumSpecificLogic($provider, $tourData, $tourModel, $isUpdating = false)
    {
        // Airline mapping
        if (!($isUpdating && $tourModel->airline_check_change != null)) {
            if (isset($tourData['airline_name']) && !empty($tourData['airline_name'])) {
                $airline = \App\Models\Backend\TravelTypeModel::where('travel_name', 'like', '%' . $tourData['airline_name'] . '%')
                    ->where('status', 'on')
                    ->whereNull('deleted_at')
                    ->first();
                
                if ($airline) {
                    $tourModel->airline_id = $airline->id;
                    // Log::info('Best Consortium airline mapped', [
                        // 'airline_name' => $tourData['airline_name'],
                        // 'matched_id' => $airline->id,
                        // 'matched_name' => $airline->travel_name
                    // ]);
                }
            }
        }
        
        // Country mapping from country_name_eng
        if (!($isUpdating && $tourModel->country_check_change != null)) {
            if (isset($tourData['country_name_eng']) && !empty($tourData['country_name_eng'])) {
                $country = \App\Models\Backend\CountryModel::where('country_name_en', 'like', '%' . $tourData['country_name_eng'] . '%')
                    ->where('status', 'on')
                    ->whereNull('deleted_at')
                    ->first();
                
                if ($country) {
                    $tourModel->country_id = json_encode([(string)$country->id]);
                    // Log::info('Best Consortium country mapped', [
                        // 'country_name_eng' => $tourData['country_name_eng'],
                        // 'matched_id' => $country->id,
                        // 'matched_name' => $country->country_name_en
                    // ]);
                }
            }
        }
    }


    private function processImage($provider, $tourData, $tourModel, $isUpdating = false)
    {
        // เช็ค image_check_change ก่อน (เหมือน headcode เดิม)
        // ถ้า image_check_change = 1 = user แก้ไขแล้ว ไม่ต้องดึงจาก API
        // ถ้า image_check_change = 2 หรือ null = ดึงจาก API ได้
        if ($isUpdating && isset($tourModel->image_check_change) && $tourModel->image_check_change == 1) {
            // Log::info('Skipping image update - user modified', [
                // 'provider' => $provider->code,
                // 'tour_id' => $tourModel->id
            // ]);
            return; // ข้าม เพราะ user แก้ไขรูปเองแล้ว
        }
        
        $imageField = $provider->fieldMappings()
            ->where('field_type', 'tour')
            ->where('local_field', 'image')
            ->first();
            
        if (!$imageField) return;
        
        $imageUrl = $tourData[$imageField->api_field] ?? null;
        if (!$imageUrl) return;
        
        // กำหนด folder เหมือน headcode เดิมทุก API
        $imageFolders = [
            'go365' => 'go365api',
            'qebooking' => 'qebookingapi',
            'probookingcenter' => 'probookingcenterapi',
            'zego' => 'zegoapi',
            'bestconsortium' => 'bestconsortiumapi',
            'best_consortium' => 'bestconsortiumapi',
            'best' => 'bestconsortiumapi',
            'ttn' => 'ttnapi',
            'ttn_japan' => 'ttnapi',
            'ttn_all' => 'ttn_allapi',
            'itravel' => 'itravelapi',
            'superbholiday' => 'superbholidayapi',
            'superb_holiday' => 'superbholidayapi',
            'tourfactory' => 'tourfactoryapi',
            'tour_factory' => 'tourfactoryapi',
            'go365' => 'go365api',
            'checkingroup' => 'checkingroupapi',
            'checkin_group' => 'checkingroupapi',
        ];
        $folderName = $imageFolders[$provider->code] ?? ($provider->code . 'api');

        try {
            // Best Consortium: ใช้ Image::make($url) โดยตรง (เหมือน headcode) เพื่อหลีกเลี่ยง 403
            if ($provider->code === 'bestconsortium' || $provider->code === 'best_consortium' || $provider->code === 'best') {
                $filename = basename($imageUrl);
                
                // Create directory if not exists
                $dirPath = 'upload/tour/' . $folderName;
                if (!Storage::disk('public')->exists($dirPath)) {
                    Storage::disk('public')->makeDirectory($dirPath, 0755, true);
                }
                
                // ใช้ Image::make() โดยตรงเหมือน headcode
                $lg = Image::make($imageUrl);
                $ext = explode("/", $lg->mime());
                $lg->resize(600, 600)->stream();
                
                $allowedExt = ['png', 'jpeg', 'jpg', 'webp'];
                if (in_array($ext[1], $allowedExt)) {
                    // Delete old image if updating
                    if ($isUpdating && $tourModel->image != null) {
                        Storage::disk('public')->delete($tourModel->image);
                    }
                    
                    $newPath = $dirPath . '/' . $filename;
                    Storage::disk('public')->put($newPath, $lg);
                    $tourModel->image = $newPath;
                    
                    // Set image_check_change = 2 (ดึงจาก API)
                    if (!$isUpdating) {
                        $tourModel->image_check_change = 2;
                    }
                    
                    // Log::info("Best Consortium image downloaded successfully (direct method)", [
                        // 'provider' => $provider->code,
                        // 'url' => $imageUrl,
                        // 'saved_path' => $newPath
                    // ]);
                }
                return;
            }
            
            // Providers อื่นๆ: ใช้ HTTP client
            $headers = [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'image/avif,image/webp,image/apng,image/svg+xml,image/*,*/*;q=0.8',
            ];
            
            // Tour Factory: ต้องใส่ Referer header (เหมือน headcode)
            if ($provider->code === 'tourfactory' || $provider->code === 'tour_factory') {
                $headers['Referer'] = 'https://booking.tourfactory.co.th/';
            }
            
            // Check HTTP response first
            $response = Http::withOptions(['verify' => false])
                ->withHeaders($headers)
                ->get($imageUrl);
            
            if ($response->successful()) {
                $contentLength = $response->header('Content-Length');
                $body = $response->body();
                
                // ดาวน์โหลดรูปถ้ามี content (ไม่ต้องเช็ค Content-Length เพราะบาง server ไม่ส่ง)
                if (!empty($body) && strlen($body) > 0) {
                    // TTN Japan has special URL structure - extract filename from query params
                    $filename = basename($imageUrl);
                    if ($provider->code === 'ttn' || $provider->code === 'ttn_japan') {
                        $urlParts = parse_url($imageUrl);
                        if (isset($urlParts['query'])) {
                            parse_str($urlParts['query'], $queryParams);
                            $filePath = $queryParams['url'] ?? '';
                            if ($filePath) {
                                $filename = basename($filePath);
                            }
                        }
                    }
                    
                    // Create directory if not exists  
                    $dirPath = 'upload/tour/' . $folderName;
                    if (!Storage::disk('public')->exists($dirPath)) {
                        Storage::disk('public')->makeDirectory($dirPath, 0755, true);
                    }
                    
                    // Use response body for Image::make
                    $lg = Image::make($body);
                    $ext = explode("/", $lg->mime());
                    $lg->resize(600, 600)->stream();
                    
                    $allowedExt = ['png', 'jpeg', 'jpg', 'webp'];
                    if (in_array($ext[1], $allowedExt)) {
                        // Generate proper filename with extension
                        $baseFilename = basename($imageUrl);
                        // If filename doesn't have extension, add it from mime type
                        if (!preg_match('/\.(png|jpe?g|webp)$/i', $baseFilename)) {
                            $extension = ($ext[1] === 'jpeg') ? 'jpg' : $ext[1];
                            $baseFilename = $baseFilename . '.' . $extension;
                        }
                        
                        // Delete old image if updating
                        if ($isUpdating && $tourModel->image != null) {
                            Storage::disk('public')->delete($tourModel->image);
                        }
                        
                        $newPath = $dirPath . '/' . $baseFilename;
                        Storage::disk('public')->put($newPath, $lg);
                        $tourModel->image = $newPath;
                        
                        // Set image_check_change = 2 (ดึงจาก API) เหมือน headcode เดิม
                        if (!$isUpdating) {
                            $tourModel->image_check_change = 2;
                        }
                        
                        // Log::info("Image downloaded successfully", [
                            // 'provider' => $provider->code,
                            // 'url' => $imageUrl,
                            // 'saved_path' => $newPath,
                            // 'content_length' => $contentLength ?? strlen($body)
                        // ]);
                    }
                } else {
                    // Log::warning("Image has empty body", [
                        // 'provider' => $provider->code,
                        // 'url' => $imageUrl,
                        // 'body_length' => strlen($body ?? '')
                    // ]);
                }
            } else {
                // Log::warning("Failed to download image", [
                    // 'provider' => $provider->code,
                    // 'url' => $imageUrl,
                    // 'status' => $response->status()
                // ]);
            }
        } catch (\Exception $e) {
            // Best Consortium: ลอง Image::make() โดยตรงก่อน (เหมือน headcode)
            if ($provider->code === 'bestconsortium' || $provider->code === 'best_consortium' || $provider->code === 'best') {
                try {
                    $filename = basename($imageUrl);
                    $dirPath = 'upload/tour/' . $folderName;
                    if (!Storage::disk('public')->exists($dirPath)) {
                        Storage::disk('public')->makeDirectory($dirPath, 0755, true);
                    }
                    
                    $lg = Image::make($imageUrl);
                    $ext = explode("/", $lg->mime());
                    $lg->resize(600, 600)->stream();
                    
                    $allowedExt = ['png', 'jpeg', 'jpg', 'webp'];
                    if (in_array($ext[1], $allowedExt)) {
                        $newPath = $dirPath . '/' . $filename;
                        Storage::disk('public')->put($newPath, $lg);
                        $tourModel->image = $newPath;
                        
                        // Log::info("Best Consortium image downloaded (fallback direct method)", [
                            // 'provider' => $provider->code,
                            // 'url' => $imageUrl,
                            // 'saved_path' => $newPath
                        // ]);
                    }
                    return;
                } catch (\Exception $fallbackError) {
                    // Log::error("Best Consortium image download failed", [
                        // 'provider' => $provider->code,
                        // 'url' => $imageUrl,
                        // 'error' => $fallbackError->getMessage()
                    // ]);
                    return;
                }
            }
            
            // Providers อื่นๆ: fallback to HTTP client
            try {
                $headers = [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Accept' => 'image/avif,image/webp,image/apng,image/svg+xml,image/*,*/*;q=0.8',
                ];
                
                $response = Http::withOptions(['verify' => false])
                    ->withHeaders($headers)
                    ->get($imageUrl);
                    
                if ($response->successful()) {
                    $filename = basename($imageUrl);
                    $dirPath = 'upload/tour/' . $folderName;
                    if (!Storage::disk('public')->exists($dirPath)) {
                        Storage::disk('public')->makeDirectory($dirPath, 0755, true);
                    }
                    
                    $lg = Image::make($response->body());
                    $ext = explode("/", $lg->mime());
                    $lg->resize(600, 600)->stream();
                    
                    $allowedExt = ['png', 'jpeg', 'jpg', 'webp'];
                    if (in_array($ext[1], $allowedExt)) {
                        $newPath = $dirPath . '/' . $filename;
                        Storage::disk('public')->put($newPath, $lg);
                        $tourModel->image = $newPath;
                        
                        // Log::info("Image downloaded successfully (fallback method)", [
                            // 'provider' => $provider->code,
                            // 'url' => $imageUrl,
                            // 'saved_path' => $newPath
                        // ]);
                    }
                }
            } catch (\Exception $fallbackError) {
                // Log::error("Error processing image (both methods failed)", [
                    // 'provider' => $provider->code,
                    // 'url' => $imageUrl,
                    // 'primary_error' => $e->getMessage(),
                    // 'fallback_error' => $fallbackError->getMessage()
                // ]);
            }
        }
    }

    private function processPDF($provider, $tourData, $tourModel)
    {
        $pdfField = $provider->fieldMappings()
            ->where('field_type', 'tour')
            ->where('local_field', 'pdf_file')
            ->first();
            
        if (!$pdfField) return;
        
        // Handle dot notation (e.g., "tour_file.file_pdf" for GO365)
        $apiField = $pdfField->api_field;
        $pdfUrl = null;
        
        if (strpos($apiField, '.') !== false) {
            // Extract nested field using dot notation
            $parts = explode('.', $apiField);
            $pdfUrl = $tourData[$parts[0]] ?? null;
            
            // Navigate through nested structure
            for ($i = 1; $i < count($parts); $i++) {
                if (is_array($pdfUrl) && isset($pdfUrl[$parts[$i]])) {
                    $pdfUrl = $pdfUrl[$parts[$i]];
                } else {
                    $pdfUrl = null;
                    break;
                }
            }
        } else {
            $pdfUrl = $tourData[$apiField] ?? null;
        }
        
        if (!$pdfUrl) return;
        
        // Handle nested fields via transformation_rules (legacy support)
        if (!empty($pdfField->transformation_rules)) {
            $rules = is_string($pdfField->transformation_rules) ? json_decode($pdfField->transformation_rules, true) : $pdfField->transformation_rules;
            if (isset($rules['nested_field'])) {
                // Extract nested value from array/object
                if (is_array($pdfUrl) && isset($pdfUrl[$rules['nested_field']])) {
                    $pdfUrl = $pdfUrl[$rules['nested_field']];
                }
            }
        }
        
        // After extraction, pdfUrl must be a string
        if (!is_string($pdfUrl) || empty($pdfUrl)) {
            // Log::warning("PDF URL is not a valid string", [
                // 'provider' => $provider->code,
                // 'pdf_value' => $pdfUrl
            // ]);
            return;
        }

        try {
            // TTN Japan/All: PDF เป็น Google Drive link → บันทึก URL โดยตรง 
            // (Google Drive ไม่อนุญาต direct download ต้องใช้ API หรือ OAuth)
            if (($provider->code === 'ttn_japan' || $provider->code === 'ttn_all') && 
                (strpos($pdfUrl, 'drive.google.com') !== false || strpos($pdfUrl, 'docs.google.com') !== false)) {
                $tourModel->pdf_file = $pdfUrl;
                // Log::info("TTN PDF: Saved Google Drive URL (cannot direct download)", [
                    // 'provider' => $provider->code,
                    // 'url' => $pdfUrl
                // ]);
                return;
            }
            
            // Other APIs: Download PDF + insert header/footer
            $headers = get_headers($pdfUrl, 1);
            
            // Tour Factory & Checkin Group: ต้องใส่ Referer header
            $httpOptions = [];
            if ($provider->code === 'tourfactory' || $provider->code === 'tour_factory') {
                $httpOptions = [
                    'headers' => [
                        'Referer' => 'https://booking.tourfactory.co.th/',
                        'User-Agent' => 'Mozilla/5.0',
                        'Accept' => 'application/pdf'
                    ]
                ];
                $response = Http::withOptions($httpOptions)->get($pdfUrl);
            } elseif ($provider->code === 'checkingroup' || $provider->code === 'checkin_group') {
                // Checkin Group: Try with referer and user agent
                $httpOptions = [
                    'headers' => [
                        'Referer' => 'https://booking.checkingroup.co.th/',
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                        'Accept' => 'application/pdf,application/octet-stream,*/*'
                    ]
                ];
                $response = Http::withOptions($httpOptions)->timeout(60)->get($pdfUrl);
                
                // If still fails (410/404), log warning and save URL instead
                if (!$response->successful()) {
                    // Log::warning("Checkin Group PDF not accessible via direct download", [
                        // 'provider' => $provider->code,
                        // 'url' => $pdfUrl,
                        // 'status' => $response->status(),
                        // 'note' => 'Saving URL instead - may need manual download or API access'
                    // ]);
                    
                    // Save URL as fallback
                    $tourModel->pdf_file = $pdfUrl;
                    return;
                }
            } else {
                $response = Http::get($pdfUrl);
            }
            
            if ($response->successful()) {
                
                if ($response->header('Content-Type')) {
                    $contentType = $response->header('Content-Type');
                    
                    if (strpos($contentType, 'application/pdf') !== false) {
                        
                        // แก้ไข: เอา query string ออกก่อน basename
                        $urlPath = parse_url($pdfUrl, PHP_URL_PATH);
                        $filename = basename($urlPath ?: $pdfUrl);
                        
                        // Sanitize filename: เอาอักขระพิเศษออก
                        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
                        
                        // ถ้าไม่มี extension .pdf ให้เพิ่มให้
                        if (!str_ends_with(strtolower($filename), '.pdf')) {
                            $filename .= '.pdf';
                        }
                        
                        $ext = explode(".", $filename);
                        $dirPath = 'upload/tour/pdf_file/' . $provider->code . 'api';
                        $newPath = $dirPath . '/' . $filename;
                        $newFileSize = strlen($response->body());
                        
                        // Create directory if not exists
                        if (!Storage::disk('public')->exists($dirPath)) {
                            Storage::disk('public')->makeDirectory($dirPath, 0755, true);
                        }
                        
                        if (Storage::disk('public')->exists($tourModel->pdf_file)) {
                            // เช็ค Date Modified
                            if (isset($headers['Last-Modified'])) {
                                $lastModified = date("Y-m-d H:i:s", strtotime($headers['Last-Modified']));
                                $oldModified = $tourModel->date_mod_pdf;
                                
                                if ($lastModified != $oldModified) {
                                    Storage::disk('public')->delete($tourModel->pdf_file);
                                    
                                    Storage::disk('public')->put($newPath, $response->body());
                                    
                                    // Check PDF version and apply header/footer
                                    $applyHeaderFooter = false;
                                    
                                    // ตรวจสอบว่าจะใช้ provider-specific หรือ global setting
                                    if ($provider->pdf_header_footer_enabled === 'on') {
                                        $applyHeaderFooter = true;
                                    } else {
                                        $img_pdf = \App\Models\Backend\ImagePDFModel::first();
                                        if ($img_pdf && $img_pdf->status == 'on') {
                                            $applyHeaderFooter = true;
                                        }
                                    }
                                    
                                    if ($applyHeaderFooter) {
                                        $filepdf = fopen(public_path($newPath), "r");
                                        $line_first = fgets($filepdf);
                                        fclose($filepdf);
                                        preg_match_all('!\d+!', $line_first, $matches);
                                        $pdfversion = implode('.', $matches[0]);
                                        
                                        // FPDI รองรับ PDF 1.4-1.7 ได้ ไม่ต้องเช็ค version แล้ว
                                        try {
                                            $this->save_pdf($newPath, $provider);
                                            // Log::info("PDF header/footer applied (update)", [
                                                // 'provider' => $provider->code,
                                                // 'version' => $pdfversion
                                            // ]);
                                        } catch (\Exception $e) {
                                            // Log::error("Failed to apply PDF header/footer (update)", [
                                                // 'provider' => $provider->code,
                                                // 'error' => $e->getMessage()
                                            // ]);
                                        }
                                    }
                                    
                                    $tourModel->pdf_file = $newPath;
                                    $tourModel->date_mod_pdf = $lastModified;
                                }
                            }
                        } else {
                            // ไฟล์ PDF ยังไม่มี - ดาวน์โหลดใหม่
                            $lastModified = isset($headers['Last-Modified']) 
                                ? date("Y-m-d H:i:s", strtotime($headers['Last-Modified'])) 
                                : date("Y-m-d H:i:s");
                            
                            Storage::disk('public')->put($newPath, $response->body());
                            
                            // Check PDF version and apply header/footer
                            $applyHeaderFooter = false;
                            
                            // ตรวจสอบว่าจะใช้ provider-specific หรือ global setting
                            if ($provider->pdf_header_footer_enabled === 'on') {
                                $applyHeaderFooter = true;
                                // Log::info("Using provider-specific PDF header/footer", [
                                    // 'provider' => $provider->code,
                                    // 'header' => $provider->pdf_header,
                                    // 'footer' => $provider->pdf_footer
                                // ]);
                            } else {
                                $img_pdf = \App\Models\Backend\ImagePDFModel::first();
                                if ($img_pdf && $img_pdf->status == 'on') {
                                    $applyHeaderFooter = true;
                                    // Log::info("Using global PDF header/footer", [
                                        // 'provider' => $provider->code
                                    // ]);
                                }
                            }
                            
                            if ($applyHeaderFooter) {
                                $filepdf = fopen(public_path($newPath), "r");
                                $line_first = fgets($filepdf);
                                fclose($filepdf);
                                preg_match_all('!\d+!', $line_first, $matches);
                                $pdfversion = implode('.', $matches[0]);
                                
                                // Log::info("PDF version check", [
                                    // 'provider' => $provider->code,
                                    // 'version' => $pdfversion,
                                    // 'will_apply' => true
                                // ]);
                                
                                // FPDI รองรับ PDF 1.4-1.7 ได้ ไม่ต้องเช็ค version แล้ว
                                try {
                                    $this->save_pdf($newPath, $provider);
                                    // Log::info("PDF header/footer applied successfully", [
                                        // 'provider' => $provider->code,
                                        // 'path' => $newPath
                                    // ]);
                                } catch (\Exception $e) {
                                    // Log::error("Failed to apply PDF header/footer", [
                                        // 'provider' => $provider->code,
                                        // 'error' => $e->getMessage()
                                    // ]);
                                }
                            } else {
                                // Log::info("PDF header/footer not enabled", [
                                    // 'provider' => $provider->code,
                                    // 'provider_enabled' => $provider->pdf_header_footer_enabled
                                // ]);
                            }
                            
                            $tourModel->pdf_file = $newPath;
                            $tourModel->date_mod_pdf = $lastModified;
                        }
                        
                        // Log::info("PDF downloaded successfully", [
                            // 'provider' => $provider->code,
                            // 'url' => $pdfUrl,
                            // 'saved_path' => $newPath,
                            // 'file_size' => $newFileSize
                        // ]);
                    }
                }
            } else {
                // Log::warning("Failed to download PDF", [
                    // 'provider' => $provider->code,
                    // 'url' => $pdfUrl,
                    // 'status' => $response->status()
                // ]);
            }
        } catch (\Exception $e) {
            // Log::error("Error processing PDF: " . $e->getMessage(), [
                // 'provider' => $provider->code,
                // 'url' => $pdfUrl,
                // 'error' => $e->getMessage()
            // ]);
        }
    }
    
    private function save_pdf($filename, $provider = null)
    {
        // ตรวจสอบว่ามี provider-specific header/footer หรือไม่
        if ($provider && $provider->pdf_header_footer_enabled === 'on') {
            $image_header = $provider->pdf_header;
            $image_footer = $provider->pdf_footer;
            
            // ถ้าไม่มีรูป ให้ใช้ global setting
            if (!$image_header || !$image_footer) {
                $data = \App\Models\Backend\ImagePDFModel::first();
                $image_header = $image_header ?: $data->header;
                $image_footer = $image_footer ?: $data->footer;
            }
            
            // Log::info("Using provider-specific PDF header/footer", [
                // 'provider' => $provider->code,
                // 'header' => $image_header,
                // 'footer' => $image_footer
            // ]);
        } else {
            // ใช้ global setting
            $data = \App\Models\Backend\ImagePDFModel::first();
            $image_header = $data->header;
            $image_footer = $data->footer;
            
            // Log::info("Using global PDF header/footer", [
                // 'header' => $image_header,
                // 'footer' => $image_footer
            // ]);
        }

        $filePath = public_path($filename);
        $outputFilePath = public_path($filename);
        
        $fpdi = new \setasign\Fpdi\Fpdi;
        $count = $fpdi->setSourceFile($filePath);
  
        for ($i=1; $i<=$count; $i++) {
            $template = $fpdi->importPage($i);
            $size = $fpdi->getTemplateSize($template);
            $fpdi->AddPage($size['orientation'], array($size['width'], $size['height']));
            $fpdi->useTemplate($template);
            
            if ($image_header && file_exists(public_path($image_header))) {
                $fpdi->Image(public_path($image_header), 0, 0, 210);
            }
            
            if ($image_footer && file_exists(public_path($image_footer))) {
                $fpdi->Image(public_path($image_footer), 0, 285, 210);
            }
        }

        $fpdi->Output($outputFilePath, 'F');
    }

    private function processPeriods($provider, $tourData, $tourModel)
    {
        // Skip period processing for GO365/QeBooking - periods are processed from detail endpoint only
        if ($provider->code === 'go365' || $provider->code === 'qebooking') {
            // Log::info('Skipping processPeriods for GO365 - periods handled by processGO365Periods from detail endpoint', [
                // 'provider' => $provider->code,
                // 'tour_id' => $tourModel->id
            // ]);
            return 0;
        }
        
        // ★★★ Skip period processing for iTravels - periods are processed from detail endpoint (/{code}) ★★★
        // iTravels: List endpoint ไม่มี date_start/date_end - ต้องดึงจาก detail endpoint เท่านั้น
        // ถ้าสร้าง default period ตรงนี้จะได้ date = null ซึ่งกลายเป็น 01/01/1970
        if ($provider->code === 'itravel' || $provider->code === 'itravels') {
            return 0;
        }
        
        // Check if there are period mappings
        $periodMappings = $provider->fieldMappings()->where('field_type', 'period')->get();
        if ($periodMappings->isEmpty()) {
            // Log::info('No period mappings found, creating default period', [
                // 'provider' => $provider->code,
                // 'tour_id' => $tourModel->id
            // ]);
            return $this->createDefaultPeriod($provider, $tourData, $tourModel);
        }
        
        // Strategy 1: Check for separate periods array field
        // First, check field mappings for array type (e.g., Zego uses 'Periods')
        $arrayMapping = $provider->fieldMappings()
            ->where('field_type', 'period')
            ->where('data_type', 'array')
            ->first();
        
        // Log::info('Checking for periods array field mapping', [
            // 'provider' => $provider->code,
            // 'array_mapping_found' => $arrayMapping ? true : false,
            // 'api_field' => $arrayMapping ? $arrayMapping->api_field : null,
            // 'local_field' => $arrayMapping ? $arrayMapping->local_field : null
        // ]);
        
        // DEBUG FORCE LOG
        if ($provider->code === 'zego') {
            \Log::emergency('ZEGO DEBUG: Starting period check', [
                'tour_id' => $tourModel->id,
                'provider' => $provider->code,
                'array_mapping' => $arrayMapping ? $arrayMapping->api_field : 'NO_MAPPING'
            ]);
        }
        
        $periodArrayFields = ['period', 'periods', 'Periods', 'tour_periods'];
        
        // Add the mapped field from database if exists
        if ($arrayMapping && !in_array($arrayMapping->api_field, $periodArrayFields)) {
            array_unshift($periodArrayFields, $arrayMapping->api_field);
            // Log::info('Added mapped field to search list', [
                // 'provider' => $provider->code,
                // 'api_field' => $arrayMapping->api_field,
                // 'search_order' => $periodArrayFields
            // ]);
        }
        
        // Special case for Zego: Try common field names even without mappings
        if ($provider->code === 'zego' && !$arrayMapping) {
            $zegoFields = ['Periods', 'periods', 'period_data', 'schedules', 'departure_dates'];
            $periodArrayFields = array_merge($zegoFields, $periodArrayFields);
            // Log::info('Added Zego fallback fields to search', [
                // 'provider' => $provider->code,
                // 'fallback_fields' => $zegoFields,
                // 'full_search_order' => $periodArrayFields
            // ]);
        }
        
        // Special case for TTN_ALL: periods are inline in 'period' field (not 'periods')
        // Headcode uses $call['period'] to get period array
        if ($provider->code === 'ttn_all') {
            array_unshift($periodArrayFields, 'period');
        }
        
        $periodsArray = null;
        $foundPeriodField = null;
        
        foreach ($periodArrayFields as $fieldName) {
            if (isset($tourData[$fieldName]) && is_array($tourData[$fieldName]) && !empty($tourData[$fieldName])) {
                $periodsArray = $tourData[$fieldName];
                $foundPeriodField = $fieldName;
                break;
            }
        }
        
        // Debug: Log what fields are actually available in tourData
        if (!$periodsArray && $provider->code === 'zego') {
            // Log::warning('Zego: No periods found in expected fields', [
                // 'provider' => $provider->code,
                // 'tour_id' => $tourModel->id ?? 'unknown',
                // 'searched_fields' => $periodArrayFields,
                // 'available_fields' => array_keys($tourData),
                // 'tour_data_sample' => array_slice($tourData, 0, 8, true)  // First 8 fields
            // ]);
            
            // Try to find any array fields that might be periods
            $arrayFields = [];
            foreach ($tourData as $key => $value) {
                if (is_array($value) && !empty($value)) {
                    $arrayFields[$key] = [
                        'count' => count($value),
                        'first_item_keys' => is_array($value[0]) ? array_keys($value[0]) : ['not_array']
                    ];
                }
            }
            
            // Log::info('Zego: Found array fields in tour data', [
                // 'provider' => $provider->code,
                // 'tour_id' => $tourModel->id ?? 'unknown',
                // 'array_fields_details' => $arrayFields
            // ]);
        }
        
        if ($periodsArray) {
            // Log::info('Found periods array in tour data, creating multiple periods', [
                // 'provider' => $provider->code,
                // 'tour_id' => $tourModel->id,
                // 'period_field' => $foundPeriodField,
                // 'periods_count' => count($periodsArray)
            // ]);
            
            // DEBUG: Log first period structure
            if (!empty($periodsArray)) {
                // Log::info('Zego: First period structure', [
                    // 'provider' => $provider->code,
                    // 'tour_id' => $tourModel->id,
                    // 'first_period' => array_slice($periodsArray[0], 0, 5, true)
                // ]);
            }
            
            // เก็บ max discount percent สำหรับทุก API (เหมือน headcode)
            $maxDiscountPercents = [];
            $createdCount = 0;
            
            foreach ($periodsArray as $periodData) {
                try {
                    $period = $this->createPeriodFromArray($provider, $periodData, $tourModel, $tourData);
                    
                    if ($period) {
                        $createdCount++;
                        // Log::info('Period created successfully', [
                            // 'provider' => $provider->code,
                            // 'tour_id' => $tourModel->id,
                            // 'period_id' => $period->id,
                            // 'created_count' => $createdCount
                        // ]);
                        
                        // คำนวณ discount percent สำหรับทุก API (เหมือน headcode)
                        // สำหรับ Best Consortium ใช้ค่าที่คำนวณมาแล้วจาก calculateBestPeriodData
                        if (($provider->code === 'bestconsortium' || $provider->code === 'best') && isset($period->discount_percent)) {
                            $discountPercent = $period->discount_percent;
                        } else {
                            $discountPercent = $this->calculatePeriodDiscountPercent($period);
                        }
                        $maxDiscountPercents[] = $discountPercent;
                    } else {
                        // Log::warning('createPeriodFromArray returned null', [
                            // 'provider' => $provider->code,
                            // 'tour_id' => $tourModel->id
                        // ]);
                    }
                } catch (\Exception $e) {
                    // Log::error('Error creating period from array', [
                        // 'provider' => $provider->code,
                        // 'tour_id' => $tourModel->id,
                        // 'error' => $e->getMessage(),
                        // 'trace' => $e->getTraceAsString()
                    // ]);
                }
            }
            
            // คำนวณ promotion สำหรับทุก API (เหมือน headcode เดิม 100%)
            if (!empty($maxDiscountPercents)) {
                $this->updateTourPromotion($tourModel, max($maxDiscountPercents));
            }
            
            // Log::info('Finished creating periods from array', [
                // 'provider' => $provider->code,
                // 'tour_id' => $tourModel->id,
                // 'total_created' => $createdCount,
                // 'array_length' => count($periodsArray)
            // ]);
            
            return $createdCount;
        }
        
        // Strategy 2: Check for direct period fields in tour data
        // BUT: Skip this for TTN Japan/All - they ALWAYS need period endpoint call
        // AND: Skip for iTravel - they return periods from detail endpoint (/{code})
        if ($provider->code !== 'ttn_japan' && $provider->code !== 'ttn_all' && 
            $provider->code !== 'itravel' && $provider->code !== 'itravels') {
            $directPeriodFields = [];
            foreach ($periodMappings as $mapping) {
                if ($mapping->local_field !== 'periods' && isset($tourData[$mapping->api_field])) {
                    $directPeriodFields[] = $mapping->api_field;
                }
            }
            
            if (!empty($directPeriodFields)) {
                // Log::info('Found direct period fields in tour data', [
                    // 'provider' => $provider->code,
                    // 'tour_id' => $tourModel->id,
                    // 'found_fields' => $directPeriodFields
                // ]);
                return $this->createPeriodFromTourData($provider, $tourData, $tourModel);
            }
        }
        
        // Strategy 2.5: For providers like GO365, check common period fields even without mappings
        $commonPeriodFields = ['tour_date_min', 'tour_date_max', 'tour_price_start', 'tour_period_count'];
        $hasCommonPeriodData = false;
        foreach ($commonPeriodFields as $field) {
            if (isset($tourData[$field])) {
                $hasCommonPeriodData = true;
                break;
            }
        }
        
        if ($hasCommonPeriodData) {
            // Log::info('Found common period fields in tour data', [
                // 'provider' => $provider->code,
                // 'tour_id' => $tourModel->id,
                // 'common_fields_found' => array_keys(array_intersect_key($tourData, array_flip($commonPeriodFields)))
            // ]);
            return $this->createPeriodFromTourData($provider, $tourData, $tourModel);
        }
        
        // Strategy 3: For multi-step APIs, call period endpoint (TTN Japan, GO365)
        $config = $provider->config ?? [];
        if (!empty($config['period_url_pattern'])) {
            // Log::info('Multi-step API: Calling period endpoint', [
                // 'provider' => $provider->code,
                // 'tour_id' => $tourModel->id,
                // 'tour_api_id' => $tourModel->api_id
            // ]);
            return $this->processPeriodsFromConfig($provider, $tourModel, $tourModel->api_id);
        }
        
        // Strategy 4: Create default period if no period data found
        // Log::info('No period data found, creating default period', [
            // 'provider' => $provider->code,
            // 'tour_id' => $tourModel->id
        // ]);
        return $this->createDefaultPeriod($provider, $tourData, $tourModel);
    }

    private function createPeriodFromTourData($provider, $tourData, $tourModel)
    {
        $periodMappings = $provider->fieldMappings()->where('field_type', 'period')->get();
        
        // ตรวจสอบจำนวน periods ที่ต้องสร้าง
        $periodCount = 1;
        
        // ลองหาจำนวน periods จากฟิลด์ต่างๆ
        $possibleCountFields = ['tour_period_count', 'period_count', 'periods_available', 'departure_count'];
        foreach ($possibleCountFields as $field) {
            if (isset($tourData[$field]) && is_numeric($tourData[$field]) && $tourData[$field] > 0) {
                $periodCount = (int)$tourData[$field];
                break;
            }
        }
        
        // สร้าง period ตามจำนวนที่ระบุ
        $createdCount = 0;
        for ($i = 0; $i < max(1, $periodCount); $i++) {
            $period = $this->createBasePeriod($provider, $tourModel);
            
            // Map ข้อมูลจาก field mappings
            foreach ($periodMappings as $mapping) {
                if ($mapping->local_field === 'periods') continue;
                
                $apiValue = $tourData[$mapping->api_field] ?? null;
                if ($apiValue !== null) {
                    // Convert array to JSON to prevent "Array to string conversion" error
                    if (is_array($apiValue)) {
                        $apiValue = json_encode($apiValue);
                    }
                    $processedValue = $this->processFieldValue($apiValue, $mapping->data_type ?? 'string');
                    if (!empty($mapping->transformation_rules)) {
                        $processedValue = $this->applyTransformationRules($processedValue, $mapping->transformation_rules);
                    }
                    $period->{$mapping->local_field} = $processedValue;
                }
            }
            
            // Set fallback values from common field names
            $this->setFallbackPeriodValues($period, $tourData);
            
            // Log::info('Creating period from tour data', [
                // 'provider' => $provider->code,
                // 'tour_id' => $tourModel->id,
                // 'period_code' => $period->period_code,
                // 'start_date' => $period->start_date,
                // 'end_date' => $period->end_date,
                // 'price1' => $period->price1
            // ]);
            
            // Remove attributes that don't exist in the table
            $period->offsetUnset('status_period_text');
            
            $period->save();
            $createdCount++;
        }
        
        return $createdCount;
    }

    private function createPeriodFromArray($provider, $periodData, $tourModel, $tourData = null, $existingPeriod = null)
    {
        $periodMappings = $provider->fieldMappings()->where('field_type', 'period')->get();
        
        // ★★★ ถ้าส่ง $existingPeriod มา (จาก iTravels) ให้ใช้ตรงนั้น ★★★
        // Check if period already exists (like headcode)
        if (!$existingPeriod) {
            if ($provider->code === 'superbholiday' || $provider->code === 'superb_holiday') {
                // Super Holiday: Check by period_code
                $periodCodeValue = $periodData['pid'] ?? null;
                if ($periodCodeValue) {
                    $existingPeriod = TourPeriodModel::where([
                        'tour_id' => $tourModel->id,
                        'period_code' => $periodCodeValue,
                        'api_type' => $provider->code
                    ])
                    ->whereNull('deleted_at')
                    ->first();
                }
            } else {
                // All other APIs: Check by period_api_id
                $periodApiId = null;
                
                // Extract period_api_id based on API structure
                if ($provider->code === 'go365') {
                    $periodApiId = $periodData['tour_period_id'] ?? null;
                } elseif ($provider->code === 'zego') {
                    $periodApiId = $periodData['PeriodID'] ?? null;
                } elseif ($provider->code === 'best' || $provider->code === 'bestconsortium' || $provider->code === 'best_consortium') {
                    $periodApiId = $periodData['pid'] ?? null;
                } elseif ($provider->code === 'ttn' || $provider->code === 'ttn_japan') {
                    $periodApiId = $periodData['P_ID'] ?? null;
                } elseif ($provider->code === 'ttn_all') {
                    $periodApiId = $periodData['P_ID'] ?? null;
                } elseif ($provider->code === 'itravel' || $provider->code === 'itravels') {
                    $periodApiId = $periodData['id'] ?? null;
                } elseif ($provider->code === 'tourfactory' || $provider->code === 'tour_factory') {
                    $periodApiId = $periodData['id'] ?? null;
                } else {
                    // Generic: Try to find period_api_id from field mappings
                    $periodApiIdMapping = $periodMappings->firstWhere('local_field', 'period_api_id');
                    if ($periodApiIdMapping && !empty($periodApiIdMapping->api_field)) {
                        $periodApiId = data_get($periodData, $periodApiIdMapping->api_field);
                    }
                }
                
                if ($periodApiId) {
                    $existingPeriod = TourPeriodModel::where([
                        'tour_id' => $tourModel->id,
                        'period_api_id' => $periodApiId,
                        'api_type' => $provider->code
                    ])
                    ->whereNull('deleted_at')
                    ->first();
                }
            }
        }
        
        $period = $existingPeriod ?? $this->createBasePeriod($provider, $tourModel);
        
        // Debug log
        // Log::info('createPeriodFromArray called', [
            // 'provider' => $provider->code,
            // 'tour_id' => $tourModel->id,
            // 'periodData_keys' => array_keys($periodData),
            // 'date_start' => $periodData['date_start'] ?? 'NOT FOUND',
            // 'date_end' => $periodData['date_end'] ?? 'NOT FOUND',
            // 'price' => $periodData['price'] ?? 'NOT FOUND',
            // 'mappings_count' => $periodMappings->count()
        // ]);
        
        // Map ข้อมูลจาก field mappings
        foreach ($periodMappings as $mapping) {
            if ($mapping->local_field === 'periods') continue;
            
            // For calculated fields, use tour data
            if ($mapping->api_field === 'calculated_from_time' && $tourData) {
                $apiValue = $tourData['time'] ?? null;
            } else {
                // Support nested fields using data_get (e.g., "price.adult.0.price")
                $apiValue = data_get($periodData, $mapping->api_field);
            }
            
            if ($apiValue !== null) {
                // Convert array to JSON to prevent "Array to string conversion" error
                if (is_array($apiValue)) {
                    $apiValue = json_encode($apiValue);
                }
                $processedValue = $this->processFieldValue($apiValue, $mapping->data_type ?? 'string');
                if (!empty($mapping->transformation_rules)) {
                    $processedValue = $this->applyTransformationRules($processedValue, $mapping->transformation_rules);
                }
                $period->{$mapping->local_field} = $processedValue;
            }
        }
        
        // Set fallback values from period data
        $this->setFallbackPeriodValues($period, $periodData);
        
        // Provider-specific calculations
        $discountPercent = 0; // Default discount percent
        if ($provider->code === 'zego') {
            $this->calculateZegoSpecialPrices($period, $periodData, $tourData);
        } elseif ($provider->code === 'bestconsortium' || $provider->code === 'best') {
            $discountPercent = $this->calculateBestPeriodData($period, $periodData, $tourData);
        } elseif ($provider->code === 'checkingroup' || $provider->code === 'checkin_group') {
            $this->calculateCheckinGroupPrices($period, $periodData);
        } elseif ($provider->code === 'ttn_all') {
            // TTN_ALL: Calculate prices using headcode logic
            // headcode uses: P_ADULT (price1), P_SINGLE (price2), P_NEWPRICE (new price)
            $this->calculateTtnAllPrices($period, $periodData, $tourData);
        } elseif ($provider->code === 'probookingcenter') {
            // ProBookingCenter: regularPrice vs salesPrice (discount calculation)
            $this->calculateProBookingCenterPrices($period, $periodData);
        } elseif ($provider->code === 'itravel' || $provider->code === 'itravels') {
            // ★★★ iTravels: Hardcode logic เหมือน headcode 100% ★★★
            // headcode: $data3->period_api_id = $call2['id'];
            $period->period_api_id = $periodData['id'] ?? $period->period_api_id;
            
            // ★★★ iTravels: ดึงราคาจาก nested price structure เหมือน headcode ★★★
            // headcode: $price1 = isset($call2['price']['adult'][0]['price']) ? $call2['price']['adult'][0]['price'] : 0;
            $period->price1 = isset($periodData['price']['adult'][0]['price']) ? (float)$periodData['price']['adult'][0]['price'] : 0; // ผู้ใหญ่พักคู่
            $period->price2 = isset($periodData['price']['single_person'][0]['price']) ? (float)$periodData['price']['single_person'][0]['price'] : 0; // ผู้ใหญ่พักเดี่ยว
            $period->price3 = 0; // เด็กมีเตียง (headcode ไม่มี)
            $period->price4 = isset($periodData['price']['child'][0]['price']) ? (float)$periodData['price']['child'][0]['price'] : 0; // เด็กไม่มีเตียง
            
            // ★★★ iTravels: ดึงวันที่จาก period data ★★★
            // headcode: $data3->start_date = $call2['date_start'];
            $period->start_date = $periodData['date_start'] ?? null;
            $period->end_date = $periodData['date_end'] ?? null;
            
            // headcode: $data3->group = $call2['seat']; $data3->count = $call2['available_seat'];
            $period->group = $periodData['seat'] ?? 0;
            $period->count = $periodData['available_seat'] ?? 0;
            
            // headcode: $data3->day = $call1['day']; $data3->night = $call1['night'];
            if ($tourData) {
                $period->day = $tourData['day'] ?? $period->day;
                $period->night = $tourData['night'] ?? $period->night;
            }
            
            // headcode: status_display = "on" (always)
            $period->status_display = "on";
            
            // headcode: status_period ตาม status
            // if($call2['status'] == "AVAILABLE"){ $data3->status_period = 1; }
            // else if($call2['status'] == "WAITLIST"){ $data3->status_period = 2; }
            // else if($call2['status'] == "FULL" || $call2['status'] == "NO_CANCEL"){ $data3->status_period = 3; }
            $status = $periodData['status'] ?? null;
            if ($status === 'AVAILABLE') {
                $period->status_period = 1;
            } elseif ($status === 'WAITLIST') {
                $period->status_period = 2;
            } elseif ($status === 'FULL' || $status === 'NO_CANCEL') {
                $period->status_period = 3;
            }
        }
        
        // ★★★ IMPORTANT: Set group_date เป็น format mY (เช่น "012026") เหมือน headcode ทุก API ★★★
        // headcode: $data3->group_date = date('mY',strtotime($pe['PeriodStartDate']));
        if ($period->start_date) {
            $period->group_date = date('mY', strtotime($period->start_date));
        }
        
        // Log::info('Creating period from array data', [
            // 'provider' => $provider->code,
            // 'tour_id' => $tourModel->id,
            // 'period_code' => $period->period_code,
            // 'start_date' => $period->start_date,
            // 'end_date' => $period->end_date,
            // 'price1' => $period->price1
        // ]);
        
        // Remove attributes that don't exist in the table
        $period->offsetUnset('status_period_text');
        
        $period->save();
        
        // Store discount percentage in period for later use in promotion calculation
        if (($provider->code === 'bestconsortium' || $provider->code === 'best') && $discountPercent > 0) {
            $period->discount_percent = $discountPercent; // Store for updateTourPromotion
        }
        
        return $period;
    }

    private function createDefaultPeriod($provider, $tourData, $tourModel)
    {
        $period = $this->createBasePeriod($provider, $tourModel);
        
        // Set fallback values from tour data
        $this->setFallbackPeriodValues($period, $tourData);
        
        // ★★★ IMPORTANT: Set group_date เป็น format mY (เช่น "012026") เหมือน headcode ทุก API ★★★
        if ($period->start_date) {
            $period->group_date = date('mY', strtotime($period->start_date));
        }
        
        // Log::info('Creating default period', [
            // 'provider' => $provider->code,
            // 'tour_id' => $tourModel->id,
            // 'period_code' => $period->period_code,
            // 'start_date' => $period->start_date,
            // 'end_date' => $period->end_date,
            // 'price1' => $period->price1
        // ]);
        
        // Remove attributes that don't exist in the table
        $period->offsetUnset('status_period_text');
        
        $period->save();
        
        return 1;
    }

    private function createBasePeriod($provider, $tourModel)
    {
        $period = new TourPeriodModel();
        $period->tour_id = $tourModel->id;
        $period->api_type = $provider->code;
        
        // Set status_display to 'on' for all API syncs (like TTN and GO365)
        $period->status_display = 'on';
        
        // Generate period code - ใช้ period_code แทน code
        $periodCode = IdGenerator::generate([
            'table' => 'tb_tour_period',
            'field' => 'period_code',
            'length' => 10,
            'prefix' => 'PD' . date('ym'),
            'reset_on_prefix_change' => true
        ]);
        $period->period_code = $periodCode;
        
        return $period;
    }

    private function setFallbackPeriodValues($period, $data)
    {
        // Fallback values for start_date
        if (!$period->start_date) {
            // ★★★ เพิ่ม 'date_start' สำหรับ iTravels API ★★★
            $possibleStartFields = ['date_start', 'tour_date_min', 'start_date', 'departure_date', 'period_start', 'date_from'];
            foreach ($possibleStartFields as $field) {
                if (isset($data[$field])) {
                    $period->start_date = $data[$field];
                    break;
                }
            }
        }
        
        // Fallback values for end_date
        if (!$period->end_date) {
            // ★★★ เพิ่ม 'date_end' สำหรับ iTravels API ★★★
            $possibleEndFields = ['date_end', 'tour_date_max', 'end_date', 'return_date', 'period_end', 'date_to'];
            foreach ($possibleEndFields as $field) {
                if (isset($data[$field])) {
                    $period->end_date = $data[$field];
                    break;
                }
            }
        }
        
        // Fallback values for price1
        if (!$period->price1) {
            $possiblePriceFields = ['tour_price_start', 'price1', 'adult_price', 'selling_price', 'price', 'cost'];
            foreach ($possiblePriceFields as $field) {
                if (isset($data[$field]) && is_numeric($data[$field])) {
                    $period->price1 = (float)$data[$field];
                    break;
                }
            }
        }
        
        // Fallback values for special_price1
        // หมายเหตุ: special_price คือ "ส่วนลด" ไม่ใช่ "ราคาโปร"
        // ดังนั้นถ้าไม่มีส่วนลด ให้เป็น 0 หรือ null ไม่ใช่ราคาเต็ม
        if (!isset($period->special_price1) || $period->special_price1 === null) {
            $possibleSpecialPriceFields = ['special_price1', 'discount_amount'];
            foreach ($possibleSpecialPriceFields as $field) {
                if (isset($data[$field]) && is_numeric($data[$field])) {
                    $period->special_price1 = (float)$data[$field];
                    break;
                }
            }
            
            // ถ้าไม่มี special price ให้เป็น 0 (ไม่มีส่วนลด)
            if (!isset($period->special_price1) || $period->special_price1 === null) {
                $period->special_price1 = 0;
            }
        }
        
        // Fallback values for other common fields
        if (!$period->count && isset($data['seat_available'])) {
            $period->count = (int)$data['seat_available'];
        }
        
        if (!$period->group && isset($data['group_size'])) {
            $period->group = (int)$data['group_size'];
        }
    }

    private function createPeriod($provider, $periodData, $tourModel)
    {
        // This method is now a wrapper that calls createPeriodFromArray
        // for backward compatibility
        $this->createPeriodFromArray($provider, $periodData, $tourModel);
    }

    /**
     * คำนวณ special_price สำหรับ Zego API ตาม logic ของ headcode
     * - Price = ราคาเต็ม (price1, price2, price3, price4)
     * - Price_End = ราคาโปร (special_price1, special_price2, special_price3, special_price4)
     * - ถ้า Price_End = 0 หมายถึงไม่มีโปร ให้ special_price = Price
     */
    private function calculateZegoSpecialPrices($period, $periodData, $tourData)
    {
        $maxDiscountPercent = 0;
        
        // ผู้ใหญ่พักคู่ (price1) - เหมือน headcode เดิม 100%
        $price_start = $periodData['Price'] ?? 0;
        $price_end = $periodData['Price_End'] ?? 0;
        
        if ($price_start > $price_end && $price_end > 0) {
            $cal = $price_start - $price_end;
            if ($cal > 0) {
                $period->price1 = $price_start;
                if ($cal < $price_start) {
                    $period->special_price1 = $cal; // ส่วนลด = price_start - price_end
                } else {
                    $period->special_price1 = 0.00;
                }
                $maxDiscountPercent = max($maxDiscountPercent, ($cal / $price_start) * 100);
            }
        } else {
            $period->price1 = $price_start;
            $period->special_price1 = 0; // ไม่มีส่วนลด = 0
        }
        
        // ผู้ใหญ่พักเดี่ยว (price2) - เหมือน headcode เดิม 100%
        $price_single_start = $periodData['Price_Single_Bed'] ?? 0;
        $price_single_end = $periodData['Price_Single_Bed_End'] ?? 0;
        
        if ($price_single_start > $price_single_end && $price_single_end > 0) {
            $cal = $price_single_start - $price_single_end;
            if ($cal > 0) {
                $period->price2 = $price_single_start;
                if ($cal < $price_single_start) {
                    $period->special_price2 = $cal; // ส่วนลด
                } else {
                    $period->special_price2 = 0.00;
                }
                $maxDiscountPercent = max($maxDiscountPercent, ($cal / $price_single_start) * 100);
            }
        } else {
            $period->price2 = $price_single_start;
            $period->special_price2 = 0;
        }
        
        // เด็กมีเตียง (price3) - เหมือน headcode เดิม 100%
        $price_child_start = $periodData['Price_Child'] ?? 0;
        $price_child_end = $periodData['Price_Child_End'] ?? 0;
        
        if ($price_child_start > $price_child_end && $price_child_end > 0) {
            $cal = $price_child_start - $price_child_end;
            if ($cal > 0) {
                $period->price3 = $price_child_start;
                if ($cal < $price_child_start) {
                    $period->special_price3 = $cal; // ส่วนลด
                } else {
                    $period->special_price3 = 0.00;
                }
                $maxDiscountPercent = max($maxDiscountPercent, ($cal / $price_child_start) * 100);
            }
        } else {
            $period->price3 = $price_child_start;
            $period->special_price3 = 0;
        }
        
        // เด็กไม่มีเตียง (price4) - เหมือน headcode เดิม 100%
        $price_childnb_start = $periodData['Price_ChildNB'] ?? 0;
        $price_childnb_end = $periodData['Price_ChildNB_End'] ?? 0;
        
        if ($price_childnb_start > $price_childnb_end && $price_childnb_end > 0) {
            $cal = $price_childnb_start - $price_childnb_end;
            if ($cal > 0) {
                $period->price4 = $price_childnb_start;
                if ($cal < $price_childnb_start) {
                    $period->special_price4 = $cal; // ส่วนลด
                } else {
                    $period->special_price4 = 0.00;
                }
                $maxDiscountPercent = max($maxDiscountPercent, ($cal / $price_childnb_start) * 100);
            }
        } else {
            $period->price4 = $price_childnb_start;
            $period->special_price4 = 0;
        }
        
        // status_period จาก PeriodStatus (เหมือน headcode เดิม 100%)
        $periodStatus = $periodData['PeriodStatus'] ?? '';
        if ($periodStatus === 'Book') {
            $period->status_period = 1;
        } elseif ($periodStatus === 'Waitlist') {
            $period->status_period = 2;
        } elseif ($periodStatus === 'Close Group' || $periodStatus === 'Soldout') {
            $period->status_period = 3;
        } else {
            $period->status_period = 1; // default
        }
        
        // day/night จาก tour level (เหมือน headcode เดิม 100%)
        // Headcode: $data3->day = $call['Days']; $data3->night = $call['Nights'];
        if (isset($tourData['Days'])) {
            $period->day = (int)$tourData['Days'];
        }
        if (isset($tourData['Nights'])) {
            $period->night = (int)$tourData['Nights'];
        }
        
        // group_date จาก start_date (เหมือน headcode เดิม 100%)
        // Headcode: $data3->group_date = date('mY',strtotime($pe['PeriodStartDate']));
        if (isset($periodData['PeriodStartDate']) && !$period->group_date) {
            $period->group_date = date('mY', strtotime($periodData['PeriodStartDate']));
        }
        
        // period_api_id จาก PeriodID (เหมือน headcode เดิม 100%)
        // Headcode: $data3->period_api_id = $pe['PeriodID'];
        if (isset($periodData['PeriodID'])) {
            $period->period_api_id = $periodData['PeriodID'];
        }
        
        // group จาก GroupSize และ count จาก Seat (เหมือน headcode เดิม 100%)
        // Headcode: $data3->group = $pe['GroupSize']; $data3->count = $pe['Seat'];
        if (isset($periodData['GroupSize'])) {
            $period->group = (int)$periodData['GroupSize'];
        }

        if (isset($periodData['Seat'])) {
            $period->count = (int)$periodData['Seat'];
        }

        // Log::info('Zego special prices calculated', [
            // 'period_id' => $period->period_code,
            // 'max_discount_percent' => $maxDiscountPercent,
            // 'price1' => $period->price1,
            // 'special_price1' => $period->special_price1,
            // 'price2' => $period->price2,
            // 'special_price2' => $period->special_price2,
            // 'status_period' => $period->status_period,
            // 'period_status_raw' => $periodStatus
        // ]);
        
        // เก็บค่า max discount percent สำหรับคำนวณ promotion ทีหลัง
        return $maxDiscountPercent;
    }

    /**
     * คำนวณข้อมูล Period สำหรับ Best Consortium API (ตาม headcode เดิม 100%)
     * - แปลงวันที่ MM/DD/YYYY → YYYY-MM-DD
     * - Extract day/night จาก time field "5 วัน 3 คืน"
     * - คำนวณ special_price จาก adultPrice_old - adultPrice
     * - Map status_period จาก avbl field
     * - Set count = 0 ถ้า avbl เป็นสถานะพิเศษ
     */
    private function calculateBestPeriodData($period, $periodData, $tourData)
    {
        // 1. แปลงวันที่ MM/DD/YYYY → YYYY-MM-DD
        if (isset($periodData['dateGo']) && !empty($periodData['dateGo'])) {

            $go = explode('/', $periodData['dateGo']);

            if (count($go) === 3) {
                $period->start_date = "{$go[2]}-{$go[0]}-{$go[1]}"; // YYYY-MM-DD
                $period->group_date = date('mY', strtotime($period->start_date));
            }
        }
        
        if (isset($periodData['dateBack']) && !empty($periodData['dateBack'])) {
            $back = explode('/', $periodData['dateBack']);
            if (count($back) === 3) {
                $period->end_date = "{$back[2]}-{$back[0]}-{$back[1]}"; // YYYY-MM-DD
            }
        }
        
        // 2. Extract day/night จาก time field "5 วัน 3 คืน"
        if ($tourData && isset($tourData['time'])) {
            preg_match_all('/\d+/', $tourData['time'], $matches);
            $period->day = isset($matches[0][0]) ? (int)$matches[0][0] : 0;
            $period->night = isset($matches[0][1]) ? (int)$matches[0][1] : 0;
        }
        
        // 3. คำนวณ special_price และ percentage จาก adultPrice_old - adultPrice (เหมือน headcode 100%)
        $price1_old = isset($periodData['adultPrice_old']) ? floatval($periodData['adultPrice_old']) : 0;
        $price1 = isset($periodData['adultPrice']) ? floatval($periodData['adultPrice']) : 0;
        
        $cal1 = 0; // Percentage for promotion calculation
        
        if ($price1_old > $price1 && $price1 > 0) {
            $discount = $price1_old - $price1;
            $period->price1 = $price1_old; // ราคาเต็ม
            
            if ($discount < $price1_old) {
                $period->special_price1 = $discount; // ส่วนลด
                
                // คำนวณ percentage เหมือน headcode เดิม
                if ($price1_old != 0) {
                    $cal1 = ($discount / $price1_old) * 100;
                }
            } else {
                $period->special_price1 = 0.00;
            }
        } else {
            // ไม่มีส่วนลด - เหมือน headcode เดิม
            $period->price1 = $price1;
            $period->special_price1 = 0; // ไม่มีส่วนลด = 0
            $cal1 = 0;
        }
        
        // ราคาอื่นๆ (ไม่คำนวณส่วนลด - เหมือน headcode เดิม)
        $period->price2 = isset($periodData['singlePrice']) ? floatval($periodData['singlePrice']) : 0;
        $period->special_price2 = 0; // ไม่มีส่วนลด
        $period->price3 = isset($periodData['childWbPrice']) ? floatval($periodData['childWbPrice']) : 0;
        $period->special_price3 = 0; // ไม่มีส่วนลด
        $period->price4 = isset($periodData['childNbPrice']) ? floatval($periodData['childNbPrice']) : 0;
        $period->special_price4 = 0; // ไม่มีส่วนลด
        
        // 4. Map status_period จาก avbl field
        $avbl = $periodData['avbl'] ?? '';
        
        if ($avbl === 'เต็ม' || $avbl === 'ปิดกรุ๊ป') {
            $period->status_period = 3; // Full/Closed
        } elseif ($avbl === 'รอชำระเงิน' || $avbl === 'รอคิว' || $avbl === 'W/L') {
            $period->status_period = 2; // Waiting
        } else {
            $period->status_period = 1; // Available
        }
        
        // 5. Set count = 0 ถ้า avbl เป็นสถานะพิเศษ
        if ($avbl === 'เต็ม' || $avbl === 'ปิดกรุ๊ป' || $avbl === 'รอคิว' || $avbl === 'รอชำระเงิน' || $avbl === 'W/L') {
            $period->count = 0;
        } else {
            $period->count = is_numeric($avbl) ? (int)$avbl : 0;
        }
        
        // 6. Return percentage สำหรับการคำนวณ promotion (เหมือน headcode เดิม)
        return $cal1;
        // 6. Set group size
        if (isset($periodData['groupSize'])) {
            $period->group = (int)$periodData['groupSize'];
        }
        
        // Log::info('Best period data calculated', [
            // 'period_code' => $period->period_code,
            // 'start_date' => $period->start_date,
            // 'end_date' => $period->end_date,
            // 'day' => $period->day,
            // 'night' => $period->night,
            // 'price1' => $period->price1,
            // 'special_price1' => $period->special_price1,
            // 'status_period' => $period->status_period,
            // 'count' => $period->count,
            // 'avbl' => $avbl
        // ]);
    }

    /**
     * คำนวณราคาสำหรับ Checkin Group API
     * - Checkin Group ไม่มีระบบส่วนลด
     * - special_price1-4 ต้องเป็น 0 เสมอ (ไม่มีส่วนลด)
     */
    private function calculateCheckinGroupPrices($period, $periodData)
    {
        // Checkin Group ไม่มีส่วนลด - special_price ต้องเป็น 0
        $period->special_price1 = 0;
        $period->special_price2 = 0;
        $period->special_price3 = 0;
        $period->special_price4 = 0;
        
        // status_period default = 1 (Available)
        $period->status_period = 1;
        
        // Log::info('Checkin Group prices calculated', [
            // 'period_code' => $period->period_code,
            // 'price1' => $period->price1,
            // 'price2' => $period->price2,
            // 'price3' => $period->price3,
            // 'price4' => $period->price4,
            // 'special_price1' => $period->special_price1,
            // 'special_price2' => $period->special_price2,
            // 'special_price3' => $period->special_price3,
            // 'special_price4' => $period->special_price4
        // ]);
    }

    /**
     * Calculate TTN_ALL period prices (100% match headcode)
     * headcode: 
     * - P_ADULT = ราคาผู้ใหญ่พักคู่ (price1)
     * - P_SINGLE = ราคาผู้ใหญ่พักเดี่ยว (price2)
     * - P_NEWPRICE = ราคาใหม่ (ถ้า < price1 แสดงว่ามีส่วนลด)
     * - special_price1 = price1 - P_NEWPRICE (ส่วนลด ไม่ใช่ราคาสุดท้าย)
     * - P_VOLUME = จำนวนคนในกรุ๊ป
     * - P_AVAILABLE = จำนวนที่ว่าง
     * - P_DUE_START, P_DUE_END = วันเริ่มต้น, สิ้นสุด
     * - P_DAY, P_NIGHT = จำนวนวัน, คืน (อยู่ใน tourData)
     */
    private function calculateTtnAllPrices($period, $periodData, $tourData = null)
    {
        $priceNew = floatval($periodData['P_NEWPRICE'] ?? 0);  // ราคาใหม่
        $price1 = floatval($periodData['P_ADULT'] ?? 0);       // ผู้ใหญ่พักคู่
        $price2 = floatval($periodData['P_SINGLE'] ?? 0);      // ผู้ใหญ่พักเดี่ยว
        
        // Set period_api_id
        $period->period_api_id = $periodData['P_ID'] ?? null;
        
        // Set dates
        $period->start_date = $periodData['P_DUE_START'] ?? null;
        $period->end_date = $periodData['P_DUE_END'] ?? null;
        
        // Get day/night from main tour data
        if ($tourData) {
            $period->day = $tourData['P_DAY'] ?? null;
            $period->night = $tourData['P_NIGHT'] ?? null;
        }
        
        // Set group and count
        $period->group = $periodData['P_VOLUME'] ?? null;
        $period->count = $periodData['P_AVAILABLE'] ?? null;
        
        // Calculate price1 and special_price1 (ตาม headcode 100%)
        // headcode: ถ้า price1 > P_NEWPRICE และ P_NEWPRICE > 0 → มีส่วนลด
        if ($price1 > $priceNew && $priceNew > 0) {
            $cal = $price1 - $priceNew;  // ส่วนลด
            if ($cal > 0) {
                $period->price1 = $price1;
                if ($cal < $price1) {
                    $period->special_price1 = $cal;  // ส่วนลด (ไม่ใช่ราคาสุดท้าย)
                } else {
                    $period->special_price1 = 0.00;
                }
            }
        } else {
            $period->price1 = $price1;
            $period->special_price1 = 0.00;  // ไม่มีส่วนลด
        }
        
        // price2 = P_SINGLE (ผู้ใหญ่พักเดี่ยว ไม่คำนวณส่วนลด)
        $period->price2 = $price2;
        $period->special_price2 = 0.00;
        
        // Set status_period based on availability (ตาม headcode)
        $available = intval($periodData['P_AVAILABLE'] ?? 0);
        if ($available > 0) {
            $period->status_period = 1;  // Available
        } else {
            $period->status_period = 3;  // Sold out
        }
        
        // Set display status
        $period->status_display = 'on';
    }

    /**
     * Calculate ProBookingCenter period prices
     * - regularPrice = ราคาเต็ม (price1, price2, price3, price4)
     * - salesPrice = ราคาโปร (ถ้ามีส่วนลด)
     * - special_price = regularPrice - salesPrice (ส่วนลด)
     */
    private function calculateProBookingCenterPrices($period, $periodData)
    {
        // Adult twin bed (price1)
        $regularAdult = floatval($periodData['regularPrice']['adult'] ?? 0);
        $salesAdult = floatval($periodData['salesPrice']['adult'] ?? 0);
        
        $period->price1 = $regularAdult;
        if ($salesAdult > 0 && $salesAdult < $regularAdult) {
            $period->special_price1 = $regularAdult - $salesAdult; // ส่วนลด
        } else {
            $period->special_price1 = 0;
        }
        
        // Single bed (price2)
        $regularSingle = floatval($periodData['regularPrice']['single'] ?? 0);
        $salesSingle = floatval($periodData['salesPrice']['single'] ?? 0);
        
        $period->price2 = $regularSingle;
        if ($salesSingle > 0 && $salesSingle < $regularSingle) {
            $period->special_price2 = $regularSingle - $salesSingle;
        } else {
            $period->special_price2 = 0;
        }
        
        // Child with bed (price3)
        $regularChild = floatval($periodData['regularPrice']['child'] ?? 0);
        $salesChild = floatval($periodData['salesPrice']['child'] ?? 0);
        
        $period->price3 = $regularChild;
        if ($salesChild > 0 && $salesChild < $regularChild) {
            $period->special_price3 = $regularChild - $salesChild;
        } else {
            $period->special_price3 = 0;
        }
        
        // Child no bed (price4)
        $regularChildNoBed = floatval($periodData['regularPrice']['childNoBed'] ?? 0);
        $salesChildNoBed = floatval($periodData['salesPrice']['childNoBed'] ?? 0);
        
        $period->price4 = $regularChildNoBed;
        if ($salesChildNoBed > 0 && $salesChildNoBed < $regularChildNoBed) {
            $period->special_price4 = $regularChildNoBed - $salesChildNoBed;
        } else {
            $period->special_price4 = 0;
        }
        
        // Status period from status field
        $status = $periodData['status'] ?? '';
        if ($status === 'W/L') {
            $period->status_period = 2; // Waiting list
        } elseif ($periodData['confirmTravel'] ?? false) {
            $period->status_period = 1; // Confirmed/Available
        } else {
            $period->status_period = 3; // Soldout
        }
        
        $period->status_display = 'on';
    }

    /**
     * Process ProBookingCenter periods for a tour
     * Returns ['created' => count, 'updated' => count]
     */
    private function processProBookingCenterPeriods($provider, $tour, $periods)
    {
        $created = 0;
        $updated = 0;
        $syncedPeriodCodes = []; // Track synced period codes for soft delete
        
        foreach ($periods as $periodData) {
            try {
                $periodCode = $periodData['periodCode'] ?? null;
                if (!$periodCode) continue;
                
                // Find existing period by period_code (periodCode is encrypted string, too long for int field)
                $period = \App\Models\Backend\TourPeriodModel::where('tour_id', $tour->id)
                    ->where('period_code', $periodCode)
                    ->whereNull('deleted_at')
                    ->first();
                
                $isNew = false;
                if (!$period) {
                    $period = new \App\Models\Backend\TourPeriodModel();
                    $period->tour_id = $tour->id;
                    $period->period_code = $periodCode; // Use period_code (varchar) instead of period_api_id (int)
                    $period->api_type = 'probookingcenter';
                    $isNew = true;
                }
                
                // Set dates (format: d/m/Y → Y-m-d)
                $startDate = $periodData['periodStart'] ?? null;
                $endDate = $periodData['periodEnd'] ?? null;
                
                if ($startDate) {
                    $period->start_date = \Carbon\Carbon::createFromFormat('d/m/Y', $startDate)->format('Y-m-d');
                }
                if ($endDate) {
                    $period->end_date = \Carbon\Carbon::createFromFormat('d/m/Y', $endDate)->format('Y-m-d');
                }
                
                // Set day/night from parent tour
                $period->day = $tour->day ?? null;
                $period->night = $tour->night ?? null;
                
                // Set group_date
                if ($startDate) {
                    $dateObj = \Carbon\Carbon::createFromFormat('d/m/Y', $startDate);
                    $period->group_date = $dateObj->format('mY');
                }
                
                // Set group and count
                $period->group = $periodData['busName'] ?? null;
                $period->count = $periodData['available'] ?? 0;
                
                // Calculate prices
                $this->calculateProBookingCenterPrices($period, $periodData);
                
                // Save period
                $period->save();
                
                // Track synced period code
                $syncedPeriodCodes[] = $periodCode;
                
                if ($isNew) {
                    $created++;
                } else {
                    $updated++;
                }
                
            } catch (\Exception $e) {
                // Log error but continue with other periods
                \Log::error('ProBookingCenter period error', [
                    'tour_id' => $tour->id,
                    'period_code' => $periodData['periodCode'] ?? 'unknown',
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        // Soft delete periods that were not in this sync (headcode behavior)
        if (count($syncedPeriodCodes) > 0) {
            \App\Models\Backend\TourPeriodModel::where('tour_id', $tour->id)
                ->where('api_type', 'probookingcenter')
                ->whereNotIn('period_code', $syncedPeriodCodes)
                ->whereNull('deleted_at')
                ->update(['deleted_at' => now()]);
        }
        
        // Update tour price from cheapest period (like GO365 logic)
        $this->updateTourPriceFromPeriods($tour);
        
        return ['created' => $created, 'updated' => $updated];
    }
    
    /**
     * Update tour price, special_price, price_group from cheapest period
     * Used by ProBookingCenter and other APIs
     */
    private function updateTourPriceFromPeriods($tour)
    {
        $allPeriods = \App\Models\Backend\TourPeriodModel::where('tour_id', $tour->id)
            ->whereNull('deleted_at')
            ->get();
        
        if ($allPeriods->count() > 0) {
            // Find cheapest period (price1 - special_price1)
            $cheapestPeriod = $allPeriods->sortBy(function ($item) {
                return $item->price1 - ($item->special_price1 ?? 0);
            })->first();
            
            if ($cheapestPeriod) {
                $num_day = '';
                if ($cheapestPeriod->day && $cheapestPeriod->night) {
                    $num_day = $cheapestPeriod->day . ' วัน ' . $cheapestPeriod->night . ' คืน';
                }
                
                $price = $cheapestPeriod->price1;
                $special_price = $cheapestPeriod->special_price1 ?? 0;
                $net_price = $price - $special_price;
                
                // Calculate price_group
                $price_group = 0;
                if ($net_price > 0) {
                    if ($net_price <= 10000) {
                        $price_group = 1;
                    } elseif ($net_price <= 20000) {
                        $price_group = 2;
                    } elseif ($net_price <= 30000) {
                        $price_group = 3;
                    } elseif ($net_price <= 50000) {
                        $price_group = 4;
                    } elseif ($net_price <= 80000) {
                        $price_group = 5;
                    } else {
                        $price_group = 6;
                    }
                }
                
                // Update tour with price info
                $tour->num_day = $num_day;
                $tour->price = $price;
                $tour->price_group = $price_group;
                $tour->special_price = $special_price;
                
                // Calculate promotion flags
                if ($special_price > 0 && $price > 0) {
                    $discountPercent = ($special_price / $price) * 100;
                    if ($discountPercent >= 30) {
                        $tour->promotion1 = 'Y';
                        $tour->promotion2 = 'N';
                    } elseif ($discountPercent > 0) {
                        $tour->promotion1 = 'N';
                        $tour->promotion2 = 'Y';
                    } else {
                        $tour->promotion1 = 'N';
                        $tour->promotion2 = 'N';
                    }
                } else {
                    $tour->promotion1 = 'N';
                    $tour->promotion2 = 'N';
                }
                
                $tour->save();
            }
        }
    }

    /**
     * คำนวณ discount percent ของ period (สำหรับ Zego)
     */
    private function calculateZegoPeriodDiscount($period)
    {
        $discountPercents = [];
        
        // price1 (ผู้ใหญ่พักคู่)
        if ($period->price1 > 0 && $period->special_price1 > 0) {
            $discount = $period->price1 - $period->special_price1;
            if ($discount > 0) {
                $discountPercents[] = ($discount / $period->price1) * 100;
            }
        }
        
        // price2 (ผู้ใหญ่พักเดี่ยว)
        if ($period->price2 > 0 && $period->special_price2 > 0) {
            $discount = $period->price2 - $period->special_price2;
            if ($discount > 0) {
                $discountPercents[] = ($discount / $period->price2) * 100;
            }
        }
        
        // price3 (เด็กมีเตียง)
        if ($period->price3 > 0 && $period->special_price3 > 0) {
            $discount = $period->price3 - $period->special_price3;
            if ($discount > 0) {
                $discountPercents[] = ($discount / $period->price3) * 100;
            }
        }
        
        // price4 (เด็กไม่มีเตียง)
        if ($period->price4 > 0 && $period->special_price4 > 0) {
            $discount = $period->price4 - $period->special_price4;
            if ($discount > 0) {
                $discountPercents[] = ($discount / $period->price4) * 100;
            }
        }
        
        return !empty($discountPercents) ? max($discountPercents) : 0;
    }

    /**
     * คำนวณ discount percent ของ period (สำหรับทุก API - เหมือน headcode)
     * ใช้สูตร: (special_price / price) * 100
     */
    private function calculatePeriodDiscountPercent($period)
    {
        // สำหรับ Best Consortium ใช้ค่าที่เก็บไว้จาก calculateBestPeriodData
        if (isset($period->discount_percent)) {
            return $period->discount_percent;
        }
        
        $discountPercents = [];
        
        // price1 - ถ้ามี special_price1 และ price1 > 0
        if ($period->price1 > 0 && $period->special_price1 > 0) {
            // สูตรเหมือน headcode: (special_price / price) * 100
            $discountPercents[] = ($period->special_price1 / $period->price1) * 100;
        }
        
        // price2 - ถ้ามี special_price2 และ price2 > 0
        if ($period->price2 > 0 && $period->special_price2 > 0) {
            $total = $period->price1 + $period->price2;
            if ($total > 0) {
                $discountPercents[] = ($period->special_price2 / $total) * 100;
            }
        }
        
        // price3 - ถ้ามี special_price3 และ price3 > 0
        if ($period->price3 > 0 && $period->special_price3 > 0) {
            $discountPercents[] = ($period->special_price3 / $period->price3) * 100;
        }
        
        // price4 - ถ้ามี special_price4 และ price4 > 0
        if ($period->price4 > 0 && $period->special_price4 > 0) {
            $discountPercents[] = ($period->special_price4 / $period->price4) * 100;
        }
        
        return !empty($discountPercents) ? max($discountPercents) : 0;
    }

    /**
     * อัพเดท promotion สำหรับทุก API ตาม max discount percent (เหมือน headcode เดิม 100%)
     * - >= 30% = โปรไฟไหม้ (promotion1 = Y, promotion2 = N)
     * - > 0% และ < 30% = โปรธรรมดา (promotion1 = N, promotion2 = Y)
     * - 0% = ไม่มีโปร (promotion1 = N, promotion2 = N)
     */
    private function updateTourPromotion($tourModel, $maxDiscountPercent)
    {
        if ($maxDiscountPercent > 0 && $maxDiscountPercent >= 30) {
            $tourModel->promotion1 = 'Y';
            $tourModel->promotion2 = 'N';
            // Log::info('Tour set as fire promotion (โปรไฟไหม้)', [
                // 'tour_id' => $tourModel->id,
                // 'api_type' => $tourModel->api_type,
                // 'discount_percent' => $maxDiscountPercent
            // ]);
        } elseif ($maxDiscountPercent > 0 && $maxDiscountPercent < 30) {
            $tourModel->promotion1 = 'N';
            $tourModel->promotion2 = 'Y';
            // Log::info('Tour set as regular promotion (โปรโมชั่นทัวร์)', [
                // 'tour_id' => $tourModel->id,
                // 'api_type' => $tourModel->api_type,
                // 'discount_percent' => $maxDiscountPercent
            // ]);
        } else {
            $tourModel->promotion1 = 'N';
            $tourModel->promotion2 = 'N';
            // Log::info('Tour has no promotion', [
                // 'tour_id' => $tourModel->id,
                // 'api_type' => $tourModel->api_type,
                // 'discount_percent' => $maxDiscountPercent
            // ]);
        }
        
        $tourModel->save();
    }

    /**
     * อัพเดท promotion สำหรับ Zego tour ตาม max discount percent
     * - >= 30% = โปรไฟไหม้ (promotion1 = Y, promotion2 = N)
     * - > 0% และ < 30% = โปรธรรมดา (promotion1 = N, promotion2 = Y)
     * - 0% = ไม่มีโปร (promotion1 = N, promotion2 = N)
     */
    private function updateZegoPromotion($tourModel, $maxDiscountPercent)
    {
        // ใช้ method เดียวกันกับ updateTourPromotion
        $this->updateTourPromotion($tourModel, $maxDiscountPercent);
    }

    private function updateTourPrice($tourModel)
    {
        $cheapestPeriod = TourPeriodModel::where('tour_id', $tourModel->id)
            ->where('api_type', $tourModel->api_type)
            ->whereNull('deleted_at')
            ->orderByRaw('(price1 - COALESCE(special_price1, 0)) ASC')
            ->first();
            
        if ($cheapestPeriod) {
            $netPrice = $cheapestPeriod->price1 - ($cheapestPeriod->special_price1 ?? 0);
            
            // Calculate price group (เหมือน headcode 100%)
            $priceGroup = 0;
            if ($netPrice > 0) {
                if ($netPrice <= 10000) {
                    $priceGroup = 1;
                } elseif ($netPrice > 10000 && $netPrice <= 20000) {
                    $priceGroup = 2;
                } elseif ($netPrice > 20000 && $netPrice <= 30000) {
                    $priceGroup = 3;
                } elseif ($netPrice > 30000 && $netPrice <= 50000) {
                    $priceGroup = 4;
                } elseif ($netPrice > 50000 && $netPrice <= 80000) {
                    $priceGroup = 5;
                } elseif ($netPrice > 80000) {
                    $priceGroup = 6;
                }
            }
            
            // Calculate num_day (เหมือน headcode 100%)
            // headcode: $num_day = $data5->day.' วัน '.$data5->night.' คืน';
            $numDay = '';
            if ($cheapestPeriod->day && $cheapestPeriod->night) {
                $numDay = $cheapestPeriod->day . ' วัน ' . $cheapestPeriod->night . ' คืน';
            }
            
            $tourModel->update([
                'num_day' => $numDay,
                'price' => $cheapestPeriod->price1,
                'special_price' => $cheapestPeriod->special_price1 ?? 0,
                'price_group' => $priceGroup
            ]);
        }
    }

    public function logs($id, Request $request)
    {
        $provider = ApiProviderModel::findOrFail($id);
        
        // Build query with filters
        $query = $provider->syncLogs();
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('date_from')) {
            $query->whereDate('started_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('started_at', '<=', $request->date_to);
        }
        
        $logs = $query->orderBy('started_at', 'desc')->paginate(20);
        
        // Get statistics
        $stats = [
            'total' => $provider->syncLogs()->count(),
            'success' => $provider->syncLogs()->where('status', 'completed')->count(),
            'failed' => $provider->syncLogs()->where('status', 'failed')->count(),
        ];
        
        return view('backend.pages.api-management.logs', compact('provider', 'logs', 'stats'));
    }

    public function logDetails($logId)
    {
        $log = ApiSyncLogModel::findOrFail($logId);
        
        $duration = null;
        if ($log->started_at && $log->completed_at) {
            $startTime = \Carbon\Carbon::parse($log->started_at);
            $endTime = \Carbon\Carbon::parse($log->completed_at);
            
            // Check if end time is before start time (data issue)
            if ($endTime->lt($startTime)) {
                $duration = 'Invalid time data';
            } else {
                $duration = $startTime->diffInSeconds($endTime) . 's';
            }
        }
        
        return response()->json([
            'id' => $log->id,
            'status' => $log->status,
            'sync_type' => $log->sync_type,
            'message' => $log->error_message ?: 'API Sync Process',
            'duration' => $duration,
            'records_processed' => $log->total_records ?? 0,
            'records_created' => $log->created_tours ?? 0,
            'records_updated' => $log->updated_tours ?? 0,
            'records_duplicated' => $log->duplicated_tours ?? 0,
            'records_failed' => $log->error_count ?? 0,
            'created_periods' => $log->created_periods ?? 0,
            'updated_periods' => $log->updated_periods ?? 0,
            'error_message' => $log->error_message,
            'summary' => $log->summary,
            'started_at' => $log->started_at ? $log->started_at->format('M d, Y H:i:s') : null,
            'completed_at' => $log->completed_at ? $log->completed_at->format('M d, Y H:i:s') : null
        ]);
    }

    public function clearLogs($id)
    {
        try {
            $provider = ApiProviderModel::findOrFail($id);
            $provider->syncLogs()->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'All logs cleared successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear logs: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteLog($logId)
    {
        try {
            $log = ApiSyncLogModel::findOrFail($logId);
            $log->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Log deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete log: ' . $e->getMessage()
            ], 500);
        }
    }

    public function duplicates($id)
    {
        $provider = ApiProviderModel::findOrFail($id);
        
        // Build query - show all duplicates regardless of status
        $query = $provider->duplicates()->with(['existingTour', 'syncLog']);
            
        // Filter by log_id if provided
        if (request()->has('log_id')) {
            $query->where('sync_log_id', request('log_id'));
        }
        
        $duplicates = $query->latest()->paginate(300);
        
        return view('backend.pages.api-management.duplicates', compact('provider', 'duplicates'));
    }

    public function mergeDuplicate($duplicateId)
    {
        try {
            $duplicate = TourDuplicateModel::with('existingTour')->findOrFail($duplicateId);
            
            // Mark as merged - สามารถเพิ่มลอจิกการ merge ตามต้องการ
            $duplicate->update([
                'status' => 'merged',
                'processed_at' => now()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Duplicate merged successfully!'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error merging duplicate: ' . $e->getMessage()
            ], 500);
        }
    }

    public function ignoreDuplicate($duplicateId)
    {
        try {
            $duplicate = TourDuplicateModel::findOrFail($duplicateId);
            
            $duplicate->update([
                'status' => 'ignored',
                'processed_at' => now()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Duplicate ignored successfully!'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error ignoring duplicate: ' . $e->getMessage()
            ], 500);
        }
    }

    public function syncProvider(Request $request)
    {
        try {
            $providerId = $request->get('provider_id');
            $provider = ApiProviderModel::with(['fieldMappings', 'conditions'])->findOrFail($providerId);
            
            if ($provider->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'API Provider is not active!'
                ], 400);
            }
            
            // สร้าง sync log ก่อน
            $syncLog = \App\Models\Backend\ApiSyncLogModel::create([
                'api_provider_id' => $provider->id,
                'sync_type' => 'manual',
                'status' => 'running',
                'started_at' => now()
            ]);
            
            // Dispatch job to queue
            \App\Jobs\SyncApiProviderJob::dispatch($provider->id, 'manual', $syncLog->id);
            
            return response()->json([
                'success' => true,
                'message' => 'Sync started in background',
                'sync_log_id' => $syncLog->id
            ]);
            
        } catch (\Exception $e) {
            // Log::error('Sync provider error: ' . $e->getMessage(), [
                // 'provider_id' => $request->get('provider_id'),
                // 'error' => $e->getMessage(),
                // 'line' => $e->getLine(),
                // 'file' => $e->getFile()
            // ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getSyncStatus($syncLogId)
    {
        try {
            $syncLog = \App\Models\Backend\ApiSyncLogModel::findOrFail($syncLogId);
            
            return response()->json([
                'success' => true,
                'status' => $syncLog->status,
                'data' => [
                    'started_at' => $syncLog->started_at,
                    'completed_at' => $syncLog->completed_at,
                    'total_records' => $syncLog->total_records,
                    'created_tours' => $syncLog->created_tours,
                    'updated_tours' => $syncLog->updated_tours,
                    'created_periods' => $syncLog->created_periods,
                    'updated_periods' => $syncLog->updated_periods,
                    'duplicated_tours' => $syncLog->duplicated_tours,
                    'error_count' => $syncLog->error_count,
                    'error_message' => $syncLog->error_message
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error getting sync status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Detect country from location string using transformation rules
     * 
     * @param string|null $locationValue
     * @param string|null $transformationRules
     * @param string $providerCode
     * @return string JSON encoded array of country IDs
     */
    private function detectCountryFromLocation($locationValue, $transformationRules, $providerCode)
    {
        $rules = null;
        if ($transformationRules) {
            $rules = is_string($transformationRules) ? json_decode($transformationRules, true) : $transformationRules;
        }
        
        $arr = [];
        
        if ($locationValue) {
            // Try to find country by location name
            $country = \App\Models\Backend\CountryModel::where(function($query) use ($locationValue) {
                $query->where('country_name_en', 'like', '%' . $locationValue . '%')
                      ->orWhere('country_name_th', 'like', '%' . $locationValue . '%');
            })
            ->where('status', 'on')
            ->whereNull('deleted_at')
            ->first();
            
            if ($country) {
                $arr[] = (string)$country->id;
            }
        }
        
        // If no country found and there's a fallback rule (for TTN Japan)
        if (empty($arr) && $rules && isset($rules['fallback_country'])) {
            $fallbackCountry = \App\Models\Backend\CountryModel::where('country_name_en', 'like', '%' . $rules['fallback_country'] . '%')
                ->where('status', 'on')
                ->whereNull('deleted_at')
                ->first();
            
            if ($fallbackCountry) {
                $arr[] = (string)$fallbackCountry->id;
            }
        }
        
        return json_encode($arr);
    }

    /**
     * Handle Universal country_id and city_id logic using transformation rules
     * Works with any API provider based on field mappings and transformation rules
     * ///
     * @param object $provider
     * @param array $tourData
     * @param object $tourModel
     */
    private function handleTTNCountryAndCity($provider, $tourData, $tourModel)
    {
        // Get field mappings with transformation rules for this provider
        $countryMapping = $provider->fieldMappings()
            ->where('field_type', 'tour')
            ->where('local_field', 'country_id')
            ->first();
            
        $cityMapping = $provider->fieldMappings()
            ->where('field_type', 'tour')
            ->where('local_field', 'city_id')
            ->first();
        
        // Handle country_id - check if country_check_change is null (like original code)
        if (!isset($tourModel->country_check_change) || $tourModel->country_check_change == null) {
            if ($countryMapping && isset($tourData[$countryMapping->api_field])) {
                $locationValue = $tourData[$countryMapping->api_field];
                $rules = null;
                if ($countryMapping->transformation_rules) {
                    $rules = is_string($countryMapping->transformation_rules) ? 
                        json_decode($countryMapping->transformation_rules, true) : 
                        $countryMapping->transformation_rules;
                }
                
                $tourModel->country_id = $this->processCountryDetection($locationValue, $rules, $provider);
            }
        }
        
        // Handle city_id using transformation rules  
        if ($cityMapping && isset($tourData[$cityMapping->api_field])) {
            $locationValue = $tourData[$cityMapping->api_field];
            $rules = null;
            if ($cityMapping->transformation_rules) {
                $rules = is_string($cityMapping->transformation_rules) ? 
                    json_decode($cityMapping->transformation_rules, true) : 
                    $cityMapping->transformation_rules;
            }
            
            $tourModel->city_id = $this->processCityDetection($locationValue, $rules);
        }
    }

    /**
     * Process country detection using universal transformation rules
     * Includes provider config fallback as requested
     */
    private function processCountryDetection($locationValue, $rules, $provider = null)
    {
        if (!$locationValue) {
            return '[]';
        }
        
        // Handle array input by converting to string or extracting first element
        if (is_array($locationValue)) {
            // If it's an empty array, return empty result
            if (empty($locationValue)) {
                return '[]';
            }
            
            // Check if this is GO365 structured country data
            $firstElement = reset($locationValue);
            if (is_array($firstElement) && isset($firstElement['country_name_en'])) {
                // This is structured country data from GO365, extract all country IDs
                $countryIds = [];
                foreach ($locationValue as $countryData) {
                    if (isset($countryData['country_name_en'])) {
                        // Try to find matching country in our database
                        $country = \App\Models\Backend\CountryModel::where(function($query) use ($countryData) {
                            $query->where('country_name_en', 'like', '%' . $countryData['country_name_en'] . '%')
                                  ->orWhere('country_name_th', 'like', '%' . $countryData['country_name_th'] . '%');
                        })
                        ->where('status', 'on')
                        ->whereNull('deleted_at')
                        ->first();
                        
                        if ($country) {
                            $countryIds[] = (string)$country->id;
                        }
                    }
                }
                return json_encode($countryIds);
            }
            
            // Check if this is Checkin Group format (array with 'name', 'code')
            if (is_array($firstElement) && isset($firstElement['code'])) {
                // This is structured country data from Checkin Group, use iso2 code
                $countryIds = [];
                $processedCodes = []; // Prevent duplicates (some tours have same country code multiple times)
                foreach ($locationValue as $countryData) {
                    $countryCode = $countryData['code'] ?? null;
                    if ($countryCode && !in_array($countryCode, $processedCodes)) {
                        // Try to find matching country in our database by iso2
                        $country = \App\Models\Backend\CountryModel::where('iso2', $countryCode)
                            ->where('status', 'on')
                            ->whereNull('deleted_at')
                            ->first();
                        
                        if ($country) {
                            $countryIds[] = (string)$country->id;
                            $processedCodes[] = $countryCode;
                            
                            // Log::info('Checkin Group: Country mapped via processCountryDetection', [
                                // 'country_name' => $countryData['name'] ?? '',
                                // 'country_code' => $countryCode,
                                // 'country_id' => $country->id
                            // ]);
                        }
                    }
                }
                return json_encode($countryIds);
            }
            
            // If it's an array but not structured, try to get the first string value
            $locationValue = reset($locationValue);
            // If still not a string (e.g., nested array), convert to JSON then use as string
            if (!is_string($locationValue)) {
                if (is_array($locationValue)) {
                    // If it's still an array, just return empty - this shouldn't map to country
                    return '[]';
                }
                $locationValue = (string)$locationValue;
            }
        } else if (!is_string($locationValue)) {
            $locationValue = (string)$locationValue;
        }
        
        $arr = [];
        
        // Try to find country by location name
        $country = \App\Models\Backend\CountryModel::where(function($query) use ($locationValue) {
            $query->where('country_name_en', 'like', '%' . $locationValue . '%')
                  ->orWhere('country_name_th', 'like', '%' . $locationValue . '%');
        })
        ->where('status', 'on')
        ->whereNull('deleted_at')
        ->first();
        
        if ($country) {
            $arr[] = (string)$country->id;
        } else {
            // Try fallback strategies when country not found
            $fallbackCountryName = null;
            
            // Strategy 1: Check rules for fallback_country
            if ($rules && isset($rules['fallback_country'])) {
                $fallbackCountryName = $rules['fallback_country'];
            } 
            // Strategy 2: Check provider config for fallback_country (as requested)
            else if ($provider && $rules && isset($rules['use_provider_fallback']) && $rules['use_provider_fallback']) {
                $providerConfig = $provider->config ?? [];
                if (isset($providerConfig['fallback_country'])) {
                    $fallbackCountryName = $providerConfig['fallback_country'];
                }
            }
            
            // Apply fallback if found
            if ($fallbackCountryName) {
                $fallbackCountry = \App\Models\Backend\CountryModel::where('country_name_en', 'like', '%' . $fallbackCountryName . '%')
                    ->where('status', 'on')
                    ->whereNull('deleted_at')
                    ->first();
                
                if ($fallbackCountry) {
                    $arr[] = (string)$fallbackCountry->id;
                }
            }
        }
        
        return json_encode($arr);
    }

    /**
     * Process city detection using universal transformation rules
     */
    private function processCityDetection($locationValue, $rules)
    {
        if (!$locationValue) {
            return '[]';
        }
        
        // Handle array input by converting to string or extracting first element
        if (is_array($locationValue)) {
            // If it's an array, try to get the first string value
            $locationValue = reset($locationValue);
            // If still not a string, convert to string
            if (!is_string($locationValue)) {
                $locationValue = (string)$locationValue;
            }
        } else if (!is_string($locationValue)) {
            $locationValue = (string)$locationValue;
        }
        
        $arr_ci = [];
        
        $city = \App\Models\Backend\CityModel::where('city_name_en', 'like', '%' . $locationValue . '%')
            ->where('status', 'on')
            ->whereNull('deleted_at')
            ->first();
        
        if ($city) {
            $arr_ci[] = (string)$city->id;
        }
        
        return json_encode($arr_ci);
    }

    // TTN Japan hardcoded methods removed - now uses Universal API Management System
    // All TTN Japan configuration is stored in database via setup_ttn_provider.php

    /**
     * Apply promotion rules to tour data
     */
    public function applyPromotionRules($tourData, $apiProviderId)
    {
        $promotionRules = ApiPromotionRuleModel::forProvider($apiProviderId)->active()->get();
        
        if ($promotionRules->isEmpty()) {
            // Default values if no rules
            return [
                'promotion1' => 'N',
                'promotion2' => 'N'
            ];
        }
        
        foreach ($promotionRules as $rule) {
            $fieldValue = $this->extractFieldValue($tourData, $rule->condition_field);
            
            if ($fieldValue !== null && $rule->checkCondition($fieldValue)) {
                return $rule->getPromotionValues();
            }
        }
        
        // Default if no rules match
        return [
            'promotion1' => 'N',
            'promotion2' => 'N'
        ];
    }
    
    /**
     * Extract field value from tour data for promotion rule evaluation
     */
    private function extractFieldValue($tourData, $fieldName)
    {
        // Handle nested array access
        if (strpos($fieldName, '.') !== false) {
            $keys = explode('.', $fieldName);
            $value = $tourData;
            
            foreach ($keys as $key) {
                if (is_array($value) && isset($value[$key])) {
                    $value = $value[$key];
                } else {
                    return null;
                }
            }
            
            return $value;
        }
        
        // Handle direct field access
        if (isset($tourData[$fieldName])) {
            return $tourData[$fieldName];
        }
        
        // Handle calculated discount percentage (common case)
        if ($fieldName === 'discount_percentage' && 
            isset($tourData['original_price']) && 
            isset($tourData['special_price1'])) {
            
            $originalPrice = (float) $tourData['original_price'];
            $specialPrice = (float) $tourData['special_price1'];
            
            if ($originalPrice > 0 && $specialPrice > 0 && $originalPrice > $specialPrice) {
                return (($originalPrice - $specialPrice) / $originalPrice) * 100;
            }
        }
        
        return null;
    }

    // ==================== SCHEDULER METHODS ====================

    /**
     * Get schedule details for editing
     */
    public function getSchedule($id, $scheduleId)
    {
        try {
            $provider = ApiProviderModel::findOrFail($id);
            $schedule = ApiScheduleModel::where('api_provider_id', $id)
                                     ->where('id', $scheduleId)
                                     ->firstOrFail();
            
            return response()->json([
                'success' => true,
                'schedule' => $schedule
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'ไม่พบตารางเวลาที่ระบุ'
            ], 404);
        }
    }

    /**
     * Create new schedule
    */
    public function createSchedule(Request $request, $id)
    {
        try {
            $provider = ApiProviderModel::findOrFail($id);
            
            $request->validate([
                'name' => 'required|string|max:255',
                'frequency' => 'required|in:daily,hourly,weekly,monthly,custom',
                'run_time' => 'nullable|date_format:H:i',
                'interval_minutes' => 'nullable|integer|min:1',
                'days_of_week' => 'nullable|array',
                'days_of_week.*' => 'integer|between:0,6',
                'day_of_month' => 'nullable|integer|between:1,31',
                'cron_expression' => 'nullable|string',
                'sync_limit' => 'nullable|integer|min:1',
                'is_active' => 'required|boolean'
            ]);

            // ตรวจสอบข้อมูลตามความถี่
            $this->validateScheduleData($request);

            $scheduleData = [
                'api_provider_id' => $id,
                'name' => $request->name,
                'frequency' => $request->frequency,
                'run_time' => $request->run_time,
                'interval_minutes' => $request->interval_minutes,
                'days_of_week' => $request->days_of_week,
                'day_of_month' => $request->day_of_month,
                'cron_expression' => $request->cron_expression,
                'sync_limit' => $request->sync_limit,
                'is_active' => $request->is_active
            ];

            $schedule = ApiScheduleModel::create($scheduleData);
            
            // คำนวณเวลารันถัดไป
            $schedule->updateNextRunTime();

            return response()->json([
                'success' => true,
                'message' => 'สร้างตารางเวลาสำเร็จ',
                'schedule' => $schedule
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'ข้อมูลไม่ถูกต้อง',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            // Log::error('Error creating schedule: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการสร้างตารางเวลา: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update existing schedule
     */
    public function updateSchedule(Request $request, $id, $scheduleId)
    {
        try {
            $provider = ApiProviderModel::findOrFail($id);
            $schedule = ApiScheduleModel::where('api_provider_id', $id)
                                     ->where('id', $scheduleId)
                                     ->firstOrFail();
            
            $request->validate([
                'name' => 'required|string|max:255',
                'frequency' => 'required|in:daily,hourly,weekly,monthly,custom',
                'run_time' => 'nullable|date_format:H:i',
                'interval_minutes' => 'nullable|integer|min:1',
                'days_of_week' => 'nullable|array',
                'days_of_week.*' => 'integer|between:0,6',
                'day_of_month' => 'nullable|integer|between:1,31',
                'cron_expression' => 'nullable|string',
                'sync_limit' => 'nullable|integer|min:1',
                'is_active' => 'required|boolean'
            ]);

            // ตรวจสอบข้อมูลตามความถี่
            $this->validateScheduleData($request);

            $schedule->update([
                'name' => $request->name,
                'frequency' => $request->frequency,
                'run_time' => $request->run_time,
                'interval_minutes' => $request->interval_minutes,
                'days_of_week' => $request->days_of_week,
                'day_of_month' => $request->day_of_month,
                'cron_expression' => $request->cron_expression,
                'sync_limit' => $request->sync_limit,
                'is_active' => $request->is_active
            ]);
            
            // คำนวณเวลารันถัดไปใหม่
            $schedule->updateNextRunTime();

            return response()->json([
                'success' => true,
                'message' => 'อัปเดตตารางเวลาสำเร็จ',
                'schedule' => $schedule
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'ข้อมูลไม่ถูกต้อง',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            // Log::error('Error updating schedule: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการอัปเดตตารางเวลา: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete schedule
     */
    public function deleteSchedule($id, $scheduleId)
    {
        try {
            $provider = ApiProviderModel::findOrFail($id);
            $schedule = ApiScheduleModel::where('api_provider_id', $id)
                                     ->where('id', $scheduleId)
                                     ->firstOrFail();
            
            $schedule->delete();

            return response()->json([
                'success' => true,
                'message' => 'ลบตารางเวลาสำเร็จ'
            ]);

        } catch (\Exception $e) {
            // Log::error('Error deleting schedule: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการลบตารางเวลา: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate schedule data based on frequency
     */
    private function validateScheduleData(Request $request)
    {
        $frequency = $request->frequency;
        
        switch ($frequency) {
            case 'hourly':
                $request->validate([
                    'interval_minutes' => 'required|integer|min:1'
                ], [
                    'interval_minutes.required' => 'กรุณาระบุช่วงเวลา (นาที) สำหรับการรันทุกชั่วโมง'
                ]);
                break;
                
            case 'daily':
                $request->validate([
                    'run_time' => 'required|date_format:H:i'
                ], [
                    'run_time.required' => 'กรุณาระบุเวลาสำหรับการรันรายวัน'
                ]);
                break;
                
            case 'weekly':
                $request->validate([
                    'run_time' => 'required|date_format:H:i',
                    'days_of_week' => 'required|array|min:1',
                    'days_of_week.*' => 'integer|between:0,6'
                ], [
                    'run_time.required' => 'กรุณาระบุเวลาสำหรับการรันรายสัปดาห์',
                    'days_of_week.required' => 'กรุณาเลือกวันที่ต้องการรันอย่างน้อย 1 วัน',
                    'days_of_week.min' => 'กรุณาเลือกวันที่ต้องการรันอย่างน้อย 1 วัน'
                ]);
                break;
                
            case 'monthly':
                $request->validate([
                    'run_time' => 'required|date_format:H:i',
                    'day_of_month' => 'required|integer|between:1,31'
                ], [
                    'run_time.required' => 'กรุณาระบุเวลาสำหรับการรันรายเดือน',
                    'day_of_month.required' => 'กรุณาระบุวันที่ของเดือนสำหรับการรัน'
                ]);
                break;
                
            case 'custom':
                $request->validate([
                    'cron_expression' => 'required|string'
                ], [
                    'cron_expression.required' => 'กรุณาระบุ Cron Expression'
                ]);
                break;
        }
    }

    /**
     * Run scheduled sync for a specific schedule
     */
    public function runScheduledSync($scheduleId)
    {
        try {
            $schedule = ApiScheduleModel::with('apiProvider')->findOrFail($scheduleId);
            
            if (!$schedule->is_active) {
                // Log::info("Schedule {$scheduleId} is inactive, skipping");
                return;
            }

            $schedule->markAsRunning();
            
            // Log::info("Starting scheduled sync for provider: {$schedule->apiProvider->name} (Schedule: {$schedule->name})");

            // เรียกใช้ performSync โดยผ่าน sync_limit หากมี และระบุว่าเป็น auto sync (scheduled)
            $limit = $schedule->sync_limit;
            $result = $this->performSync($schedule->apiProvider, 'auto', $limit);

            // performSync จะ return array with log_id และ summary หรือ throw exception
            $schedule->markAsSuccess();
            // Log::info("Scheduled sync completed successfully for provider: {$schedule->apiProvider->name}");
            
            return [
                'success' => true,
                'log_id' => $result['log_id'],
                'summary' => $result['summary']
            ];

        } catch (\Exception $e) {
            // Log::error("Error in scheduled sync for schedule {$scheduleId}: " . $e->getMessage());
            
            if (isset($schedule)) {
                $schedule->markAsFailed($e->getMessage());
            }
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Test scheduled sync - run a schedule manually for testing
     */
    public function testSchedule($id, $scheduleId)
    {
        try {
            $provider = ApiProviderModel::findOrFail($id);
            $schedule = ApiScheduleModel::where('api_provider_id', $id)
                                     ->where('id', $scheduleId)
                                     ->firstOrFail();
            
            if ($provider->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'API Provider is not active!'
                ], 400);
            }

            if (!$schedule->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Schedule is not active!'
                ], 400);
            }

            // Run the scheduled sync
            $result = $this->runScheduledSync($scheduleId);
            
            return response()->json($result);

        } catch (\Exception $e) {
            // Log::error('Test schedule error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Test schedule error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Apply TTN Japan specific logic like original ApiController.php
     * Handles country (JAPAN), city (P_LOCATION), airline (P_AIRLINE), and special settings
     */
    private function applyTTNJapanSpecificLogic($provider, $tourData, $tourModel, $isUpdating = false)
    {
        // TTN_ALL: Detect country from P_LOCATION field (ตาม headcode 100%)
        // headcode: $country = CountryModel::where('country_name_en', 'like', '%'.$call['P_LOCATION'].'%')
        if ($provider->code === 'ttn_all') {
            if (!$isUpdating || !isset($tourModel->country_check_change) || $tourModel->country_check_change == null) {
                if (isset($tourData['P_LOCATION']) && $tourData['P_LOCATION']) {
                    $country = \App\Models\Backend\CountryModel::where('country_name_en', 'like', '%' . $tourData['P_LOCATION'] . '%')
                        ->where('status', 'on')
                        ->whereNull('deleted_at')
                        ->first();
                        
                    if ($country) {
                        $tourModel->country_id = json_encode([(string)$country->id]);
                    } else {
                        $tourModel->country_id = '[]';
                    }
                }
            }
        } else {
            // TTN Japan: Force JAPAN as country (like original code) - เช็ค country_check_change ก่อน
            if (!$isUpdating || !isset($tourModel->country_check_change) || $tourModel->country_check_change == null) {
                $country = \App\Models\Backend\CountryModel::where('country_name_en', 'like', '%JAPAN%')
                    ->where('status', 'on')
                    ->whereNull('deleted_at')
                    ->first();
                    
                if ($country) {
                    $tourModel->country_id = json_encode([(string)$country->id]);
                } else {
                    $tourModel->country_id = '[]';
                }
                
                // Handle city from P_LOCATION (same country_check_change condition)
                if (isset($tourData['P_LOCATION']) && $tourData['P_LOCATION']) {
                    $city = \App\Models\Backend\CityModel::where('city_name_en', 'like', '%' . $tourData['P_LOCATION'] . '%')
                        ->where('status', 'on')
                        ->whereNull('deleted_at')
                        ->first();
                        
                    if ($city) {
                        $tourModel->city_id = json_encode([(string)$city->id]);
                    } else {
                        $tourModel->city_id = '[]';
                    }
                }
            }
        }
        
        // Handle airline from P_AIRLINE code - เช็ค airline_check_change ก่อน
        // P_AIRLINE format is "Austrian Airlines (OS)" - extract code from parentheses (ตาม headcode)
        if (!$isUpdating || !isset($tourModel->airline_check_change) || $tourModel->airline_check_change == null) {
            if (isset($tourData['P_AIRLINE']) && $tourData['P_AIRLINE']) {
                // Extract airline code from parentheses, e.g., "Austrian Airlines (OS)" -> "OS"
                $parts = explode('(', $tourData['P_AIRLINE']);
                $code_airline = "";
                if (isset($parts[1])) {
                    $code_airline = trim($parts[1], ') ');
                }
                
                if ($code_airline) {
                    $airline = \App\Models\Backend\TravelTypeModel::where('code', $code_airline)
                        ->where('status', 'on')
                        ->whereNull('deleted_at')
                        ->first();
                        
                    if ($airline) {
                        $tourModel->airline_id = $airline->id;
                    }
                }
            }
        }
        
        // Set data_type for all API tours (both new and update)
        $tourModel->data_type = 2; // 1 system , 2 api
        
        // Set special configuration values (like original code) - เฉพาะ new tour เท่านั้น
        if (!$isUpdating) {
            $tourModel->image_check_change = 2; // 1 ไม่ดึงรูปจาก Api , 2 ดึงรูปจาก Api
            $tourModel->api_type = 'ttn';
            
            // wholesale_id and group_id are already set by config, but ensure they're correct
            $tourModel->wholesale_id = 35; // TTN Japan specific
            $tourModel->group_id = 3;
        }
        // Log::info('Applied TTN Japan specific logic', [
            // 'is_updating' => $isUpdating,
            // 'tour_api_id' => $tourData['P_ID'] ?? 'unknown',
            // 'country_id' => $tourModel->country_id,
            // 'city_id' => $tourModel->city_id ?? null,
            // 'airline_id' => $tourModel->airline_id ?? null,
            // 'p_location' => $tourData['P_LOCATION'] ?? null,
            // 'p_airline' => $tourData['P_AIRLINE'] ?? null
        // ]);
    }
}