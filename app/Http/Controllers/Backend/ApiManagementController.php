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
            'syncLogs' => function($query) {
                $query->latest()->take(5);
            },
            'schedules' => function($query) {
                $query->where('is_active', true);
            },
            'duplicates' => function($query) {
                $query->where('status', 'pending');
            }
        ])->get();
        
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
            $response = Http::withHeaders($headers)->timeout(30)->get($provider->url);
            
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
                                    !empty($config['period_url_pattern']);
                
                if ($hasMultiStepConfig && $recordCount > 0) {
                    // For multi-step APIs (GO365, TTN Japan), test detail/period endpoints for period counting
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
            if ($provider->code === 'go365') {
                $tours = $responseData['data'] ?? [];
            } else {
                $tours = is_array($responseData) ? $responseData : [];
            }
            
            // Test detail/period endpoint for first few tours
            $testLimit = min(3, count($tours)); // Test max 3 tours
            
            for ($i = 0; $i < $testLimit; $i++) {
                $tour = $tours[$i];
                
                if ($provider->code === 'go365') {
                    $tourId = $tour['tour_id'] ?? null;
                    if (!$tourId) continue;
                    
                    $detailUrl = str_replace('{tour_id}', $tourId, $provider->tour_detail_endpoint);
                    
                    $detailResponse = Http::withHeaders($headers)->timeout(10)->get($detailUrl);
                    
                    if ($detailResponse->successful()) {
                        $detailData = $detailResponse->json();
                        
                        if (isset($detailData['data'][0]['tour_period']) && is_array($detailData['data'][0]['tour_period'])) {
                            $periodCount += count($detailData['data'][0]['tour_period']);
                        }
                    }
                } else {
                    // For TTN Japan and other multi-step APIs
                    $tourId = null;
                    if (is_array($tour)) {
                        $tourId = $tour['P_ID'] ?? $tour['id'] ?? null;
                    } elseif (is_numeric($tour)) {
                        $tourId = $tour;
                    }
                    if (!$tourId) continue;
                    
                    // Check if provider has period_url_pattern in config
                    $config = is_string($provider->config) ? json_decode($provider->config, true) : ($provider->config ?? []);
                    
                    if (!empty($config['period_url_pattern'])) {
                        // TTN Japan: Use period endpoint directly
                        $periodUrl = str_replace('{id}', $tourId, $config['period_url_pattern']);
                        $periodResponse = Http::withHeaders($headers)->timeout(10)->get($periodUrl);
                        
                        if ($periodResponse->successful()) {
                            $periodData = $periodResponse->json();
                            
                            if (is_array($periodData)) {
                                // TTN Japan structure: array of period groups, each with Price array
                                foreach ($periodData as $periodGroup) {
                                    if (isset($periodGroup['Price']) && is_array($periodGroup['Price'])) {
                                        $periodCount += count($periodGroup['Price']);
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
                            $periodCount += $this->countPeriodsFromResponse($detailData, $provider);
                        }
                    }
                }
            }
            
            // Estimate total periods based on sample
            if ($testLimit > 0 && $periodCount > 0) {
                $avgPeriodsPerTour = $periodCount / $testLimit;
                $estimatedTotal = round($avgPeriodsPerTour * count($tours));
                return $estimatedTotal;
            }
            
            return $periodCount;
            
        } catch (\Exception $e) {
            Log::warning('Error testing multi-step periods', [
                'provider' => $provider->code,
                'error' => $e->getMessage()
            ]);
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
            
            // Look for arrays that match period field patterns (top-level)
            foreach ($periodArrayFields as $fieldName) {
                if (isset($responseData[$fieldName]) && is_array($responseData[$fieldName])) {
                    $array = $responseData[$fieldName];
                    
                    // Ensure it's a numerically indexed array (list of items, not associative)
                    if (array_keys($array) === range(0, count($array) - 1)) {
                        return count($array);
                    }
                }
            }
            
            // Look for nested arrays (e.g., data.schedules, result.periods)
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
            Log::warning('Error counting periods from response', [
                'provider_id' => $provider->id,
                'provider_code' => $provider->code,
                'error' => $e->getMessage()
            ]);
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
            Log::error('Manual sync error: ' . $e->getMessage());
            
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
            Log::error('Sync error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Sync error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function performSync($provider, $syncType = 'manual', $limit = 0)
    {
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
            
            Log::info('Making API request', [
                'provider' => $provider->name,
                'url' => $provider->url,
                'headers' => $headers
            ]);
            
            $response = Http::withHeaders($headers)->timeout(120)->get($provider->url);
            
            Log::info('API response received', [
                'provider' => $provider->name,
                'status' => $response->status(),
                'headers' => $response->headers(),
                'body_length' => strlen($response->body()),
                'first_100_chars' => substr($response->body(), 0, 100)
            ]);
            
            if (!$response->successful()) {
                throw new \Exception('API request failed with status: ' . $response->status());
            }

            $apiData = $response->json();
            Log::info('API data parsed - raw response', [
                'provider' => $provider->name,
                'data_type' => gettype($apiData),
                'is_array' => is_array($apiData),
                'raw_structure' => is_array($apiData) ? array_keys($apiData) : 'N/A',
                'sample_data' => is_array($apiData) ? array_slice($apiData, 0, 3, true) : $apiData
            ]);
            
            // Handle different API response structures
            $toursData = $apiData;
            if (is_array($apiData)) {
                // Check if this is a wrapped response (like GO365: {status, count, data})
                if (isset($apiData['data']) && is_array($apiData['data'])) {
                    $toursData = $apiData['data'];
                    Log::info('Extracted tours from wrapped response', [
                        'provider' => $provider->name,
                        'original_keys' => array_keys($apiData),
                        'tours_count' => count($toursData)
                    ]);
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
                Log::warning('API returned NULL or empty response', [
                    'provider' => $provider->name,
                    'url' => $provider->url,
                    'response_type' => gettype($apiData)
                ]);
                $toursData = [];
            } else {
                throw new \Exception('Invalid API response: Expected array or object but received ' . gettype($apiData));
            }
            
            // Apply limit if specified
            if ($limit > 0 && is_array($toursData) && count($toursData) > $limit) {
                $toursData = array_slice($toursData, 0, $limit);
                Log::info('Applied limit to tours data', [
                    'provider' => $provider->name,
                    'original_count' => $totalRecords ?? 0,
                    'limited_count' => count($toursData),
                    'limit' => $limit
                ]);
            }
            
            $totalRecords = is_array($toursData) ? count($toursData) : 0;
            
            Log::info('Final tours data structure', [
                'provider' => $provider->name,
                'tours_count' => $totalRecords,
                'first_tour_sample' => $totalRecords > 0 ? array_keys($toursData[0]) : 'N/A',
                'limit_applied' => $limit > 0 ? $limit : 'no limit'
            ]);
            
            $createdTours = 0;
            $updatedTours = 0;
            $duplicatedTours = 0;
            $errorCount = 0;
            $errors = [];

            if ($totalRecords > 0) {
                foreach ($toursData as $index => $tourData) {
                    try {
                        $currentIndex = $index + 1;
                        Log::info("Processing tour {$currentIndex}/{$totalRecords}", [
                            'provider' => $provider->code,
                            'tour_index' => $currentIndex
                        ]);
                        
                        $result = $this->processTourData($provider, $tourData, $syncLog);
                        
                        if ($result['action'] === 'created') {
                            $createdTours++;
                        } elseif ($result['action'] === 'updated') {
                            $updatedTours++;
                        } elseif ($result['action'] === 'duplicated') {
                            $duplicatedTours++;
                        }
                        
                    } catch (\Exception $e) {
                        $errorCount++;
                        $errors[] = $e->getMessage();
                        $currentIndex = $index + 1;
                        Log::error("Error processing tour {$currentIndex}: " . $e->getMessage());
                    }
                }
            }

            // อัปเดตข้อมูลการซิงค์ลงในฐานข้อมูล
            $syncLog->update([
                'status' => 'completed',
                'completed_at' => now(),
                'total_records' => $totalRecords,
                'created_tours' => $createdTours,
                'updated_tours' => $updatedTours,
                'duplicated_tours' => $duplicatedTours,
                'error_count' => $errorCount,
                'error_message' => $errorCount > 0 ? implode('; ', array_slice($errors, 0, 5)) : null,
                'summary' => [
                    'total_records' => $totalRecords,
                    'created_tours' => $createdTours,
                    'updated_tours' => $updatedTours,
                    'duplicated_tours' => $duplicatedTours,
                    'error_count' => $errorCount,
                    'errors' => array_slice($errors, 0, 10) // เก็บ error แค่ 10 ตัวแรก
                ]
            ]);

            return [
                'log_id' => $syncLog->id,
                'summary' => [
                    'total_records' => $totalRecords,
                    'created_tours' => $createdTours,
                    'duplicated_tours' => $duplicatedTours,
                    'error_count' => $errorCount
                ]
            ];

        } catch (\Exception $e) {
            $syncLog->update([
                'status' => 'failed',
                'completed_at' => now(),
                'error_message' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    private function performMultiStepSync($provider, $syncLog, $limit = 0)
    {
        Log::info('Starting multi-step sync', ['provider' => $provider->name]);
        
        // Step 1: Get list of IDs from main URL  
        $headers = $provider->headers ?? [];
        $response = Http::withHeaders($headers)->timeout(120)->get($provider->url);
        
        if (!$response->successful()) {
            throw new \Exception('Failed to get program IDs: ' . $response->status());
        }
        
        $responseData = $response->json();
        
        // Handle different API response structures
        if ($provider->code === 'go365') {
            // GO365 returns: {"success": true, "code": 200, "data": [tours], "total_rows": 337}
            $programIds = $responseData['data'] ?? [];
            if (!is_array($programIds)) {
                throw new \Exception('GO365 API: Expected data array not found');
            }
        } else {
            // TTN returns array directly
            $programIds = $responseData;
        }
        
        Log::info('Multi-step sync: Found programs', [
            'provider' => $provider->name, 
            'count' => count($programIds)
        ]);
        
        $createdTours = 0;
        $duplicatedTours = 0;
        $skippedTours = 0;
        $errorCount = 0;
        $errors = [];
        $processedCount = 0;
        
        // Apply limit to program IDs if specified
        if ($limit > 0 && count($programIds) > $limit) {
            $programIds = array_slice($programIds, 0, $limit);
        }
        
        $config = $provider->config ?? [];
        
        foreach ($programIds as $programData) {
            try {
                // Handle different API structures
                if ($provider->code === 'go365') {
                    // GO365 structure: programIds is array from /tours/search, need tour_id for detail
                    $tourId = $programData['tour_id'] ?? null;
                    if (!$tourId) {
                        Log::warning('GO365: Missing tour_id', ['data' => $programData]);
                        $errorCount++;
                        continue;
                    }
                    
                    $detailUrl = str_replace('{tour_id}', $tourId, $provider->tour_detail_endpoint);
                } else {
                    // TTN structure: use P_ID with config pattern
                    $programId = is_array($programData) ? ($programData['P_ID'] ?? null) : $programData;
                    if (!$programId) {
                        $errorCount++;
                        Log::error('No program ID found', ['program_data' => $programData]);
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
                
                $response = Http::withHeaders($headers)->timeout(60)->get($detailUrl);
                
                if (!$response->successful()) {
                    Log::warning('Failed to get program details', [
                        'provider' => $provider->name,
                        'url' => $detailUrl,
                        'status' => $response->status()
                    ]);
                    $errorCount++;
                    continue;
                }
                
                $programDetails = $response->json();
                
                // Handle different API response structures
                if ($provider->code === 'go365') {
                    // GO365: Process main tour and periods from detail response
                    $result = $this->processTourData($provider, $programData, $syncLog);
                    
                    if ($result['action'] === 'created') {
                        $createdTours++;
                        
                        // Process GO365 periods from detail response
                        if (isset($programDetails['data'][0]['tour_period']) && 
                            is_array($programDetails['data'][0]['tour_period'])) {
                            $this->processGO365Periods($provider, $result['tour_model'], $programDetails['data'][0]);
                        }
                    } elseif ($result['action'] === 'duplicated') {
                        $duplicatedTours++;
                    }
                    
                    $processedCount++;
                } else {
                    // TTN returns array, take first element
                    foreach ($programDetails as $program) {
                        $result = $this->processTourData($provider, $program, $syncLog);
                        
                        if ($result['action'] === 'created') {
                            $createdTours++;
                            
                            // Process periods if period URL pattern exists
                            if (!empty($config['period_url_pattern'])) {
                                // Use program's P_ID, not programData's
                                $programIdForPeriod = $program['P_ID'] ?? $programData['P_ID'];
                                $this->processPeriodsFromConfig($provider, $result['tour_model'], $programIdForPeriod);
                            }
                        } elseif ($result['action'] === 'skipped') {
                            $skippedTours++;
                        } elseif ($result['action'] === 'duplicated') {
                            $duplicatedTours++;
                        }
                        
                        $processedCount++;
                    }
                }
                
            } catch (\Exception $e) {
                $errorCount++;
                $errors[] = $e->getMessage();
                Log::error('Error processing program', [
                    'provider' => $provider->name,
                    'program_id' => is_array($programData) ? ($programData['P_ID'] ?? 'unknown') : $programData,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        // Update sync log
        $syncLog->update([
            'status' => 'completed',
            'completed_at' => now(),
            'total_records' => $processedCount,
            'created_tours' => $createdTours,
            'duplicated_tours' => $duplicatedTours,
            'error_count' => $errorCount,
            'error_message' => $errorCount > 0 ? implode('; ', array_slice($errors, 0, 5)) : null,
            'summary' => [
                'total_records' => $processedCount,
                'created_tours' => $createdTours,
                'duplicated_tours' => $duplicatedTours,
                'skipped_tours' => $skippedTours,
                'error_count' => $errorCount,
                'errors' => array_slice($errors, 0, 10)
            ]
        ]);
        
        return [
            'log_id' => $syncLog->id,
            'summary' => [
                'total_records' => $processedCount,
                'created_tours' => $createdTours,
                'duplicated_tours' => $duplicatedTours,
                'skipped_tours' => $skippedTours,
                'error_count' => $errorCount
            ]
        ];
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
            
            Log::info('Calling period endpoint', [
                'provider' => $provider->code,
                'program_id' => $programId,
                'period_url' => $periodUrl
            ]);
            
            $response = Http::withHeaders($headers)->timeout(60)->get($periodUrl);
            
            Log::info('Period API response', [
                'provider' => $provider->code,
                'status' => $response->status(),
                'successful' => $response->successful(),
                'body_length' => strlen($response->body())
            ]);
            
            if (!$response->successful()) {
                Log::warning('Failed to get periods', [
                    'provider' => $provider->name,
                    'program_id' => $programId,
                    'status' => $response->status()
                ]);
                return;
            }
            
            $periodsData = $response->json();
            
            Log::info('Period data received', [
                'provider' => $provider->code,
                'data_type' => gettype($periodsData),
                'is_array' => is_array($periodsData),
                'count' => is_array($periodsData) ? count($periodsData) : 0
            ]);
            
            // TTN Japan: Process periods with Price array (เหมือน headcode)
            if ($provider->code === 'ttn' || $provider->code === 'ttn_japan') {
                Log::info('Processing TTN Japan periods', [
                    'provider' => $provider->code,
                    'tour_id' => $tourModel->id,
                    'periods_count' => is_array($periodsData) ? count($periodsData) : 0
                ]);
                $this->processTTNJapanPeriods($provider, $tourModel, $periodsData);
            } else {
                // Generic period processing for other APIs
                if (count($periodsData) > 0) {
                    foreach ($periodsData as $periodData) {
                        if (isset($periodData['Price']) && is_array($periodData['Price'])) {
                            foreach ($periodData['Price'] as $priceData) {
                                // Create period using database mappings
                                $this->createPeriodFromArray($provider, array_merge($periodData, $priceData), $tourModel);
                            }
                        }
                    }
                }
            }
            
        } catch (\Exception $e) {
            Log::error('Error processing periods from config', [
                'provider' => $provider->name,
                'program_id' => $programId,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function processTTNJapanPeriods($provider, $tourModel, $periodsData)
    {
        // TTN Japan period logic (เหมือน headcode 100%)
        try {
            if (!is_array($periodsData) || count($periodsData) === 0) {
                return;
            }

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

                    Log::info('TTN Japan period created/updated', [
                        'tour_id' => $tourModel->id,
                        'period_id' => $periodModel->id,
                        'period_api_id' => $periodGroup['P_ID'],
                        'start_date' => $periodModel->start_date,
                        'price1' => $price1,
                        'price2' => $price2
                    ]);
                }
            }

        } catch (\Exception $e) {
            Log::error('Error processing TTN Japan periods', [
                'tour_id' => $tourModel->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function processGO365Periods($provider, $tourModel, $tourDetailData)
    {
        try {
            if (!isset($tourDetailData['tour_period']) || !is_array($tourDetailData['tour_period'])) {
                return;
            }

            foreach ($tourDetailData['tour_period'] as $periodData) {
                // Create period based on headcode logic
                $periodModel = new \App\Models\Backend\TourPeriodModel();
                
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
                
                $periodModel->api_type = 'go365';
                $periodModel->group_date = $periodData['period_date'] ? date('mY', strtotime($periodData['period_date'])) : null;
                
                $periodModel->save();
            }
            
            Log::info('GO365 periods processed', [
                'tour_id' => $tourModel->id,
                'period_count' => count($tourDetailData['tour_period'])
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error processing GO365 periods', [
                'tour_id' => $tourModel->id,
                'error' => $e->getMessage()
            ]);
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
            
            Log::info('Best Consortium: Fetching countries', [
                'url' => $countriesUrl
            ]);
            
            $response = Http::withHeaders($headers)->timeout(60)->get($countriesUrl);
            
            if (!$response->successful()) {
                throw new \Exception('Failed to fetch countries: ' . $response->status());
            }
            
            $countries = $response->json();
            
            if (!is_array($countries) || count($countries) === 0) {
                throw new \Exception('No countries found in API response');
            }
            
            Log::info('Best Consortium: Countries fetched', [
                'country_count' => count($countries),
                'countries' => array_map(function($c) {
                    return ['id' => $c['id'] ?? 'N/A', 'name' => $c['nameEng'] ?? 'N/A'];
                }, $countries)
            ]);
            
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
                    Log::warning('Best Consortium: Country missing ID', ['country' => $country]);
                    continue;
                }
                
                try {
                    $toursUrl = "https://tour-api.bestinternational.com/api/tour-programs/v2/{$countryId}";
                    
                    Log::info('Best Consortium: Fetching tours for country', [
                        'country_id' => $countryId,
                        'country_name' => $countryName,
                        'url' => $toursUrl
                    ]);
                    
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
                            Log::info('Best Consortium: Rate limit reached, sleeping', ['wait_time' => $waitTime]);
                            sleep($waitTime);
                            $response = Http::withHeaders($headers)->timeout(60)->get($toursUrl);
                        }
                    } elseif ($response->status() == 429) {
                        // Rate limit - retry after sleep
                        Log::warning('Best Consortium: Rate limit reached (429)', [
                            'country_id' => $countryId
                        ]);
                        sleep(60);
                        $response = Http::withHeaders($headers)->timeout(60)->get($toursUrl);
                    }
                    
                    // 404 = ไม่มี tours ในประเทศนี้ (ไม่ใช่ error ปกติ)
                    if ($response->status() == 404) {
                        Log::info('Best Consortium: No tours found for country (404)', [
                            'country_id' => $countryId,
                            'country_name' => $countryName
                        ]);
                        continue; // ข้ามประเทศนี้ไป ไม่นับเป็น error
                    }
                    
                    if (!$response->successful()) {
                        // Error อื่นๆ (ไม่ใช่ 404) ถึงจะนับเป็น error
                        $errorCount++;
                        $errors[] = "Country {$countryName} ({$countryId}): Status {$response->status()}";
                        Log::warning('Best Consortium: Failed to fetch country tours', [
                            'country_id' => $countryId,
                            'country_name' => $countryName,
                            'status' => $response->status()
                        ]);
                        continue;
                    }
                    
                    $tours = $response->json();
                    
                    if (is_array($tours) && count($tours) > 0) {
                        Log::info('Best Consortium: Tours fetched for country', [
                            'country_id' => $countryId,
                            'country_name' => $countryName,
                            'tour_count' => count($tours)
                        ]);
                        
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
                    Log::error('Best Consortium: Error fetching country tours', [
                        'country_id' => $countryId,
                        'country_name' => $countryName,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            // Apply limit if specified
            if ($limit > 0 && count($toursToProcess) > $limit) {
                $toursToProcess = array_slice($toursToProcess, 0, $limit);
                Log::info('Best Consortium: Applied limit to tours', [
                    'original_count' => $totalRecords,
                    'limited_count' => count($toursToProcess),
                    'limit' => $limit
                ]);
            }
            
            // Process all collected tours
            Log::info('Best Consortium: Starting to process tours', [
                'total_tours' => count($toursToProcess)
            ]);
            
            foreach ($toursToProcess as $index => $tourData) {
                try {
                    $currentIndex = $index + 1;
                    Log::info("Processing tour {$currentIndex}/" . count($toursToProcess), [
                        'provider' => $provider->code,
                        'tour_index' => $currentIndex
                    ]);
                    
                    $result = $this->processTourData($provider, $tourData, $syncLog);
                    
                    if ($result['action'] === 'created') {
                        $createdTours++;
                    } elseif ($result['action'] === 'updated') {
                        $createdTours++; // Count updates as processed
                    } elseif ($result['action'] === 'duplicated') {
                        $duplicatedTours++;
                    }
                    
                } catch (\Exception $e) {
                    $errorCount++;
                    $errors[] = $e->getMessage();
                    Log::error('Best Consortium: Error processing tour', [
                        'tour_index' => $index + 1,
                        'tour_code' => $tourData['code'] ?? 'N/A',
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            // Update sync log
            $syncLog->update([
                'status' => 'completed',
                'completed_at' => now(),
                'total_records' => count($toursToProcess),
                'created_tours' => $createdTours,
                'duplicated_tours' => $duplicatedTours,
                'error_count' => $errorCount,
                'error_message' => $errorCount > 0 ? implode('; ', array_slice($errors, 0, 5)) : null,
                'summary' => [
                    'total_countries' => count($countries),
                    'total_records' => count($toursToProcess),
                    'created_tours' => $createdTours,
                    'duplicated_tours' => $duplicatedTours,
                    'error_count' => $errorCount,
                    'errors' => array_slice($errors, 0, 10)
                ]
            ]);
            
            return [
                'log_id' => $syncLog->id,
                'summary' => [
                    'total_countries' => count($countries),
                    'total_records' => count($toursToProcess),
                    'created_tours' => $createdTours,
                    'duplicated_tours' => $duplicatedTours,
                    'error_count' => $errorCount
                ]
            ];
            
        } catch (\Exception $e) {
            Log::error('Best Consortium sync error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $syncLog->update([
                'status' => 'failed',
                'completed_at' => now(),
                'error_message' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    private function processTourData($provider, $tourData, $syncLog)
    {
        // ตรวจสอบ data type ก่อน
        Log::info('processTourData received data', [
            'provider' => $provider->name,
            'data_type' => gettype($tourData),
            'is_array' => is_array($tourData),
            'array_keys' => is_array($tourData) ? array_keys($tourData) : null,
            'has_tour_id' => is_array($tourData) && isset($tourData['tour_id']) ? 'YES' : 'NO',
            'first_few_keys' => is_array($tourData) ? array_slice(array_keys($tourData), 0, 10) : null,
            'tour_id_value' => is_array($tourData) && isset($tourData['tour_id']) ? $tourData['tour_id'] : 'NOT_FOUND'
        ]);
        
        if (!is_array($tourData)) {
            throw new \Exception('Invalid tour data: Expected array but received ' . gettype($tourData) . '. Data: ' . json_encode($tourData));
        }
        
        // ตรวจสอบว่าเป็น associative array หรือ indexed array
        $isAssociative = array_keys($tourData) !== range(0, count($tourData) - 1);
        if (!$isAssociative) {
            Log::error('Tour data is indexed array instead of associative array', [
                'provider' => $provider->name,
                'array_keys' => array_keys($tourData),
                'array_count' => count($tourData),
                'first_element_type' => isset($tourData[0]) ? gettype($tourData[0]) : 'none'
            ]);
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
                Log::error('API ID not found in tour data', [
                    'provider' => $provider->code,
                    'expected_field' => $apiIdField->api_field,
                    'tour_data_type' => gettype($tourData),
                    'tour_data_value' => is_array($tourData) ? 'array with ' . count($tourData) . ' elements' : $tourData,
                    'available_fields' => is_array($tourData) ? array_keys($tourData) : 'N/A - not an array',
                    'tour_data_sample' => is_array($tourData) ? array_slice($tourData, 0, 5, true) : 'N/A - not an array'
                ]);
                throw new \Exception('API ID not found in tour data. Expected field: ' . $apiIdField->api_field . '. Data type: ' . gettype($tourData));
            }
        }

        // ตรวจสอบว่ามีทัวร์นี้ในระบบแล้วหรือไม่ - ใช้ api_id เป็นมาตรฐานสำหรับทุก API
        $existingTour = TourModel::where([
            'api_id' => $apiId,
            'api_type' => $provider->code
        ])->whereNull('deleted_at')->first();
        
        // For GO365, also check by tour_code to prevent duplicate constraint violations
        if (!$existingTour && $provider->code === 'go365' && isset($tourData['tour_code'])) {
            $existingTour = TourModel::where([
                'code1' => $tourData['tour_code'],
                'api_type' => $provider->code
            ])->whereNull('deleted_at')->first();
        }

        if ($existingTour) {
            // ทัวร์มีอยู่แล้ว - อัปเดทข้อมูลแทนการสร้างใหม่
            Log::info('Updating existing tour', [
                'provider' => $provider->code,
                'tour_id' => $existingTour->id,
                'api_id' => $apiId
            ]);
            
            // Update existing tour with new data from API (with check_change fields)
            try {
                // เช็ค check_change fields ก่อน map (เหมือน headcode เดิม)
                $this->mapTourFieldsFromConfig($provider, $tourData, $existingTour, true); // true = is updating
                
                // สำหรับ TTN Japan - เพิ่ม city_id logic
                if ($provider->code === 'ttn' && isset($tourData['P_LOCATION'])) {
                    $this->applyTTNJapanCityLogic($tourData, $existingTour, true);
                }
            } catch (\Exception $e) {
                Log::error('Error updating tour fields', [
                    'provider' => $provider->code,
                    'tour_id' => $existingTour->id,
                    'error' => $e->getMessage()
                ]);
                throw $e;
            }
            
            // Process image and PDF updates (with check_change)
            $this->processImage($provider, $tourData, $existingTour, true); // true = is updating
            $this->processPDF($provider, $tourData, $existingTour);
            
            $existingTour->save();
            
            // Update periods - remove old ones and create new ones
            $existingTour->period()->delete();
            $this->processPeriods($provider, $tourData, $existingTour);
            
            // Update tour price based on new periods
            $this->updateTourPrice($existingTour);
            
            // Log as duplicate for tracking but indicate it was updated
            TourDuplicateModel::create([
                'api_provider_id' => $provider->id,
                'sync_log_id' => $syncLog->id,
                'api_id' => $apiId,
                'existing_tour_id' => $existingTour->id,
                'api_data' => $tourData,
                'status' => 'updated'  // Changed from 'pending' to 'updated'
            ]);
            
            return ['action' => 'updated', 'tour_id' => $existingTour->id, 'tour_model' => $existingTour];
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
        $tourModel->api_id = $apiId;  // ใช้ api_id เป็นมาตรฐานสำหรับทุก API
        
        // จัดการ wholesale_id ให้เฉพาะแต่ละ API (เหมือน headcode เดิม)
        $tourModel->group_id = 3; // Wholesale group
        if ($provider->code === 'ttn') {
            $tourModel->wholesale_id = 35; // TTN Japan
        } elseif ($provider->code === 'go365') {
            $tourModel->wholesale_id = 41; // GO365
        } elseif ($provider->code === 'zego') {
            $tourModel->wholesale_id = null; // Zego ไม่มี wholesale_id ตาย headcode
        } elseif ($provider->code === 'best') {
            $tourModel->wholesale_id = null; // Best ไม่มี wholesale_id ตาม headcode
        }
        
        // Map fields จาก API data using database mappings (including static values like api_type, data_type)
        try {
            $this->mapTourFieldsFromConfig($provider, $tourData, $tourModel, false); // false = is new
        } catch (\Exception $e) {
            Log::error('Error in mapTourFieldsFromConfig', [
                'provider' => $provider->code,
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
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
        
        try {
            $tourModel->save();
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle duplicate constraint violations
            if ($e->getCode() == 23000 && strpos($e->getMessage(), 'Duplicate entry') !== false) {
                // Try to find the existing tour again (might have been created by another process)
                $existingTour = TourModel::where([
                    'code1' => $tourData['tour_code'] ?? '',
                    'api_type' => $provider->code
                ])->orWhere([
                    'api_id' => $apiId,
                    'api_type' => $provider->code
                ])->whereNull('deleted_at')->first();
                
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
                    
                    return ['action' => 'duplicated', 'tour_id' => $existingTour->id, 'tour_model' => $existingTour];
                }
            }
            
            // Re-throw if not a duplicate constraint violation or couldn't find existing tour
            throw $e;
        }
        
        // Process periods if exists
        $this->processPeriods($provider, $tourData, $tourModel);
        
        // TTN Japan/All: ถ้าไม่มี periods ให้ลบ tour (ไม่ sync tours ที่ไม่มี periods)
        if (($provider->code === 'ttn_japan' || $provider->code === 'ttn_all')) {
            $periodCount = $tourModel->period()->count();
            if ($periodCount === 0) {
                Log::info('TTN Japan: Deleting tour without periods', [
                    'provider' => $provider->code,
                    'tour_id' => $tourModel->id,
                    'tour_code' => $tourModel->code,
                    'api_id' => $tourModel->api_id
                ]);
                $tourModel->delete();
                return ['action' => 'skipped', 'reason' => 'no_periods'];
            }
        }
        
        // Update tour price based on periods
        $this->updateTourPrice($tourModel);
        
        return ['action' => 'created', 'tour_id' => $tourModel->id, 'tour_model' => $tourModel];
    }

    private function mapTourFieldsFromConfig($provider, $tourData, $tourModel, $isUpdating = false)
    {
        $mappings = $provider->fieldMappings()->where('field_type', 'tour')->get();
        
        foreach ($mappings as $mapping) {
            $apiValue = $tourData[$mapping->api_field] ?? null;
            
            // Handle static value mappings (where api_field is empty)
            if (empty($mapping->api_field) && !empty($mapping->transformation_rules)) {
                $rules = is_string($mapping->transformation_rules) ? json_decode($mapping->transformation_rules, true) : $mapping->transformation_rules;
                if ($rules && isset($rules['static_value'])) {
                    $tourModel->{$mapping->local_field} = $rules['static_value'];
                    continue;
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
                } elseif ($mapping->local_field === 'country_id' && $mapping->api_field === 'tour_country') {
                    // Handle GO365 tour_country array - extract country_id values
                    $countryIds = [];
                    if (is_array($apiValue)) {
                        foreach ($apiValue as $countryObj) {
                            if (isset($countryObj['country_id'])) {
                                $countryIds[] = (string)$countryObj['country_id'];
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
                            
                            Log::info('Location detection', [
                                'provider' => $provider->code,
                                'field' => $fieldName,
                                'source_name' => $sourceName,
                                'detected_ids' => $detectedIds,
                                'final_value' => $tourModel->{$fieldName}
                            ]);
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
                } elseif ($mapping->local_field === 'airline_id' && $mapping->api_field === 'tour_airline') {
                    // Handle GO365 tour_airline object - extract airline_id
                    if (is_array($apiValue) && isset($apiValue['airline_id'])) {
                        $tourModel->airline_id = (int)$apiValue['airline_id'];
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
                } else {
                    // Apply transformation rules if exists
                    if (!empty($mapping->transformation_rules)) {
                        $apiValue = $this->applyTransformationRules($apiValue, $mapping->transformation_rules);
                    }
                    
                    // Handle different data types
                    $processedValue = $this->processFieldValue($apiValue, $mapping->data_type ?? 'string');
                    
                    // Debug log
                    Log::info('Mapping field', [
                        'field_type' => $mapping->field_type,
                        'local_field' => $mapping->local_field,
                        'api_field' => $mapping->api_field,
                        'value' => $processedValue
                    ]);
                    
                    // Set value to model
                    $tourModel->{$mapping->local_field} = $processedValue;
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
        
        // Apply TTN Japan specific logic (like original ApiController.php)
        if ($provider->code === 'ttn' || $provider->code === 'ttn_japan') {
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
                        $firstElement = $value[0];
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
                return (int)$value;
                
            case 'decimal':
            case 'float':
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
                    return $rule['static_value'] ?? $value;
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
                            Log::warning("Date conversion failed: " . $e->getMessage(), [
                                'value' => $value,
                                'from_format' => $fromFormat,
                                'to_format' => $toFormat
                            ]);
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
                            Log::warning("Date to month-year conversion failed: " . $e->getMessage());
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
                Log::info('Country mapping executed', [
                    'field_value' => $fieldValue,
                    'country_id' => $country->id,
                    'country_name' => $country->country_name_en
                ]);
            } else {
                $tourModel->country_id = '[]';
                Log::info('Country mapping - no match found', [
                    'field_value' => $fieldValue
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Country mapping error: ' . $e->getMessage());
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
                Log::info('Airline mapping executed', [
                    'field_value' => $fieldValue,
                    'airline_id' => $airline->id,
                    'airline_name' => $airline->travel_name
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Airline mapping error: ' . $e->getMessage());
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
                    
                    Log::info('Image processed via condition', [
                        'source_url' => $fieldValue,
                        'saved_path' => $newPath
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Image processing condition error: ' . $e->getMessage());
        }
    }
    
    private function executeFieldTransformation($condition, $tourData, $tourModel)
    {
        $fieldValue = $tourData[$condition->field_name] ?? null;
        if ($fieldValue === null) return;
        
        $rules = $condition->condition_rules;
        $targetField = $rules['target_field'] ?? $condition->field_name;
        $dataType = $rules['data_type'] ?? 'string';
        
        $processedValue = $this->processFieldValue($fieldValue, $dataType);
        $tourModel->{$targetField} = $processedValue;
        
        Log::info('Field transformation executed', [
            'source_field' => $condition->field_name,
            'target_field' => $targetField,
            'value' => $processedValue
        ]);
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
        
        Log::info('Text processing executed', [
            'source_field' => $condition->field_name,
            'target_field' => $targetField,
            'original_value' => $fieldValue,
            'processed_value' => $processedValue
        ]);
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
                    Log::info('Best Consortium airline mapped', [
                        'airline_name' => $tourData['airline_name'],
                        'matched_id' => $airline->id,
                        'matched_name' => $airline->travel_name
                    ]);
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
                    Log::info('Best Consortium country mapped', [
                        'country_name_eng' => $tourData['country_name_eng'],
                        'matched_id' => $country->id,
                        'matched_name' => $country->country_name_en
                    ]);
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
            Log::info('Skipping image update - user modified', [
                'provider' => $provider->code,
                'tour_id' => $tourModel->id
            ]);
            return; // ข้าม เพราะ user แก้ไขรูปเองแล้ว
        }
        
        $imageField = $provider->fieldMappings()
            ->where('field_type', 'tour')
            ->where('local_field', 'image')
            ->first();
            
        if (!$imageField) return;
        
        $imageUrl = $tourData[$imageField->api_field] ?? null;
        if (!$imageUrl) return;

        try {
            // Best Consortium: ใช้ Image::make($url) โดยตรง (เหมือน headcode) เพื่อหลีกเลี่ยง 403
            if ($provider->code === 'bestconsortium' || $provider->code === 'best_consortium') {
                $filename = basename($imageUrl);
                
                // Create directory if not exists
                $dirPath = 'upload/tour/' . $provider->code;
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
                    
                    Log::info("Best Consortium image downloaded successfully (direct method)", [
                        'provider' => $provider->code,
                        'url' => $imageUrl,
                        'saved_path' => $newPath
                    ]);
                }
                return;
            }
            
            // Providers อื่นๆ: ใช้ HTTP client
            $headers = [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'image/avif,image/webp,image/apng,image/svg+xml,image/*,*/*;q=0.8',
            ];
            
            // Check HTTP response first
            $response = Http::withOptions(['verify' => false])
                ->withHeaders($headers)
                ->get($imageUrl);
            
            if ($response->successful()) {
                $contentLength = $response->header('Content-Length');
                
                // Only proceed if content length is valid (like original code)
                if (!empty($contentLength) && intval($contentLength) > 0) {
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
                    $dirPath = 'upload/tour/' . $provider->code;
                    if (!Storage::disk('public')->exists($dirPath)) {
                        Storage::disk('public')->makeDirectory($dirPath, 0755, true);
                    }
                    
                    // Use response body for Image::make
                    $lg = Image::make($response->body());
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
                        
                        // Set image_check_change = 2 (ดึงจาก API) เหมือน headcode เดิม
                        if (!$isUpdating) {
                            $tourModel->image_check_change = 2;
                        }
                        
                        Log::info("Image downloaded successfully", [
                            'provider' => $provider->code,
                            'url' => $imageUrl,
                            'saved_path' => $newPath,
                            'content_length' => $contentLength
                        ]);
                    }
                } else {
                    Log::warning("Image has invalid content length", [
                        'provider' => $provider->code,
                        'url' => $imageUrl,
                        'content_length' => $contentLength
                    ]);
                }
            } else {
                Log::warning("Failed to download image", [
                    'provider' => $provider->code,
                    'url' => $imageUrl,
                    'status' => $response->status()
                ]);
            }
        } catch (\Exception $e) {
            // Best Consortium: ลอง Image::make() โดยตรงก่อน (เหมือน headcode)
            if ($provider->code === 'bestconsortium' || $provider->code === 'best_consortium') {
                try {
                    $filename = basename($imageUrl);
                    $dirPath = 'upload/tour/' . $provider->code;
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
                        
                        Log::info("Best Consortium image downloaded (fallback direct method)", [
                            'provider' => $provider->code,
                            'url' => $imageUrl,
                            'saved_path' => $newPath
                        ]);
                    }
                    return;
                } catch (\Exception $fallbackError) {
                    Log::error("Best Consortium image download failed", [
                        'provider' => $provider->code,
                        'url' => $imageUrl,
                        'error' => $fallbackError->getMessage()
                    ]);
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
                    $dirPath = 'upload/tour/' . $provider->code;
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
                        
                        Log::info("Image downloaded successfully (fallback method)", [
                            'provider' => $provider->code,
                            'url' => $imageUrl,
                            'saved_path' => $newPath
                        ]);
                    }
                }
            } catch (\Exception $fallbackError) {
                Log::error("Error processing image (both methods failed)", [
                    'provider' => $provider->code,
                    'url' => $imageUrl,
                    'primary_error' => $e->getMessage(),
                    'fallback_error' => $fallbackError->getMessage()
                ]);
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
        
        $pdfUrl = $tourData[$pdfField->api_field] ?? null;
        if (!$pdfUrl) return;

        try {
            // TTN Japan/All: PDF เป็น Google Drive link → บันทึก URL โดยตรง 
            // (Google Drive ไม่อนุญาต direct download ต้องใช้ API หรือ OAuth)
            if (($provider->code === 'ttn_japan' || $provider->code === 'ttn_all') && 
                (strpos($pdfUrl, 'drive.google.com') !== false || strpos($pdfUrl, 'docs.google.com') !== false)) {
                $tourModel->pdf_file = $pdfUrl;
                Log::info("TTN PDF: Saved Google Drive URL (cannot direct download)", [
                    'provider' => $provider->code,
                    'url' => $pdfUrl
                ]);
                return;
            }
            
            // Other APIs: Download PDF + insert header/footer
            $headers = get_headers($pdfUrl, 1);
            
            $response = Http::get($pdfUrl);
            
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
                                            Log::info("PDF header/footer applied (update)", [
                                                'provider' => $provider->code,
                                                'version' => $pdfversion
                                            ]);
                                        } catch (\Exception $e) {
                                            Log::error("Failed to apply PDF header/footer (update)", [
                                                'provider' => $provider->code,
                                                'error' => $e->getMessage()
                                            ]);
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
                                Log::info("Using provider-specific PDF header/footer", [
                                    'provider' => $provider->code,
                                    'header' => $provider->pdf_header,
                                    'footer' => $provider->pdf_footer
                                ]);
                            } else {
                                $img_pdf = \App\Models\Backend\ImagePDFModel::first();
                                if ($img_pdf && $img_pdf->status == 'on') {
                                    $applyHeaderFooter = true;
                                    Log::info("Using global PDF header/footer", [
                                        'provider' => $provider->code
                                    ]);
                                }
                            }
                            
                            if ($applyHeaderFooter) {
                                $filepdf = fopen(public_path($newPath), "r");
                                $line_first = fgets($filepdf);
                                fclose($filepdf);
                                preg_match_all('!\d+!', $line_first, $matches);
                                $pdfversion = implode('.', $matches[0]);
                                
                                Log::info("PDF version check", [
                                    'provider' => $provider->code,
                                    'version' => $pdfversion,
                                    'will_apply' => true
                                ]);
                                
                                // FPDI รองรับ PDF 1.4-1.7 ได้ ไม่ต้องเช็ค version แล้ว
                                try {
                                    $this->save_pdf($newPath, $provider);
                                    Log::info("PDF header/footer applied successfully", [
                                        'provider' => $provider->code,
                                        'path' => $newPath
                                    ]);
                                } catch (\Exception $e) {
                                    Log::error("Failed to apply PDF header/footer", [
                                        'provider' => $provider->code,
                                        'error' => $e->getMessage()
                                    ]);
                                }
                            } else {
                                Log::info("PDF header/footer not enabled", [
                                    'provider' => $provider->code,
                                    'provider_enabled' => $provider->pdf_header_footer_enabled
                                ]);
                            }
                            
                            $tourModel->pdf_file = $newPath;
                            $tourModel->date_mod_pdf = $lastModified;
                        }
                        
                        Log::info("PDF downloaded successfully", [
                            'provider' => $provider->code,
                            'url' => $pdfUrl,
                            'saved_path' => $newPath,
                            'file_size' => $newFileSize
                        ]);
                    }
                }
            } else {
                Log::warning("Failed to download PDF", [
                    'provider' => $provider->code,
                    'url' => $pdfUrl,
                    'status' => $response->status()
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Error processing PDF: " . $e->getMessage(), [
                'provider' => $provider->code,
                'url' => $pdfUrl,
                'error' => $e->getMessage()
            ]);
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
            
            Log::info("Using provider-specific PDF header/footer", [
                'provider' => $provider->code,
                'header' => $image_header,
                'footer' => $image_footer
            ]);
        } else {
            // ใช้ global setting
            $data = \App\Models\Backend\ImagePDFModel::first();
            $image_header = $data->header;
            $image_footer = $data->footer;
            
            Log::info("Using global PDF header/footer", [
                'header' => $image_header,
                'footer' => $image_footer
            ]);
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
        // Check if there are period mappings
        $periodMappings = $provider->fieldMappings()->where('field_type', 'period')->get();
        if ($periodMappings->isEmpty()) {
            Log::info('No period mappings found, creating default period', [
                'provider' => $provider->code,
                'tour_id' => $tourModel->id
            ]);
            $this->createDefaultPeriod($provider, $tourData, $tourModel);
            return;
        }
        
        // Strategy 1: Check for separate periods array field
        $periodsArrayField = $periodMappings->where('local_field', 'periods')->first();
        if ($periodsArrayField && isset($tourData[$periodsArrayField->api_field])) {
            $periodsArray = $tourData[$periodsArrayField->api_field];
            if (is_array($periodsArray) && !empty($periodsArray)) {
                Log::info('Found periods array, creating multiple periods', [
                    'provider' => $provider->code,
                    'tour_id' => $tourModel->id,
                    'periods_count' => count($periodsArray)
                ]);
                
                // เก็บ max discount percent สำหรับ Zego
                $maxDiscountPercents = [];
                
                foreach ($periodsArray as $periodData) {
                    $period = $this->createPeriodFromArray($provider, $periodData, $tourModel, $tourData);
                    
                    // Zego-specific: เก็บ discount percent
                    if ($provider->code === 'zego' && $period) {
                        $discountPercent = $this->calculateZegoPeriodDiscount($period);
                        $maxDiscountPercents[] = $discountPercent;
                    }
                }
                
                // Zego-specific: คำนวณ promotion หลังจากสร้าง periods ทั้งหมดแล้ว
                if ($provider->code === 'zego' && !empty($maxDiscountPercents)) {
                    $this->updateZegoPromotion($tourModel, max($maxDiscountPercents));
                }
                
                return;
            }
        }
        
        // Strategy 2: Check for direct period fields in tour data
        // BUT: Skip this for TTN Japan/All - they ALWAYS need period endpoint call
        if ($provider->code !== 'ttn_japan' && $provider->code !== 'ttn_all') {
            $directPeriodFields = [];
            foreach ($periodMappings as $mapping) {
                if ($mapping->local_field !== 'periods' && isset($tourData[$mapping->api_field])) {
                    $directPeriodFields[] = $mapping->api_field;
                }
            }
            
            if (!empty($directPeriodFields)) {
                Log::info('Found direct period fields in tour data', [
                    'provider' => $provider->code,
                    'tour_id' => $tourModel->id,
                    'found_fields' => $directPeriodFields
                ]);
                $this->createPeriodFromTourData($provider, $tourData, $tourModel);
                return;
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
            Log::info('Found common period fields in tour data', [
                'provider' => $provider->code,
                'tour_id' => $tourModel->id,
                'common_fields_found' => array_keys(array_intersect_key($tourData, array_flip($commonPeriodFields)))
            ]);
            $this->createPeriodFromTourData($provider, $tourData, $tourModel);
            return;
        }
        
        // Strategy 3: For multi-step APIs, call period endpoint (TTN Japan, GO365)
        $config = $provider->config ?? [];
        if (!empty($config['period_url_pattern'])) {
            Log::info('Multi-step API: Calling period endpoint', [
                'provider' => $provider->code,
                'tour_id' => $tourModel->id,
                'tour_api_id' => $tourModel->api_id
            ]);
            $this->processPeriodsFromConfig($provider, $tourModel, $tourModel->api_id);
            return;
        }
        
        // Strategy 4: Create default period if no period data found
        Log::info('No period data found, creating default period', [
            'provider' => $provider->code,
            'tour_id' => $tourModel->id
        ]);
        $this->createDefaultPeriod($provider, $tourData, $tourModel);
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
        for ($i = 0; $i < max(1, $periodCount); $i++) {
            $period = $this->createBasePeriod($provider, $tourModel);
            
            // Map ข้อมูลจาก field mappings
            foreach ($periodMappings as $mapping) {
                if ($mapping->local_field === 'periods') continue;
                
                $apiValue = $tourData[$mapping->api_field] ?? null;
                if ($apiValue !== null) {
                    $processedValue = $this->processFieldValue($apiValue, $mapping->data_type ?? 'string');
                    if (!empty($mapping->transformation_rules)) {
                        $processedValue = $this->applyTransformationRules($processedValue, $mapping->transformation_rules);
                    }
                    $period->{$mapping->local_field} = $processedValue;
                }
            }
            
            // Set fallback values from common field names
            $this->setFallbackPeriodValues($period, $tourData);
            
            Log::info('Creating period from tour data', [
                'provider' => $provider->code,
                'tour_id' => $tourModel->id,
                'period_code' => $period->period_code,
                'start_date' => $period->start_date,
                'end_date' => $period->end_date,
                'price1' => $period->price1
            ]);
            
            // Remove attributes that don't exist in the table
            $period->offsetUnset('status_period_text');
            
            $period->save();
        }
    }

    private function createPeriodFromArray($provider, $periodData, $tourModel, $tourData = null)
    {
        $periodMappings = $provider->fieldMappings()->where('field_type', 'period')->get();
        
        $period = $this->createBasePeriod($provider, $tourModel);
        
        // Map ข้อมูลจาก field mappings
        foreach ($periodMappings as $mapping) {
            if ($mapping->local_field === 'periods') continue;
            
            // For calculated fields, use tour data
            if ($mapping->api_field === 'calculated_from_time' && $tourData) {
                $apiValue = $tourData['time'] ?? null;
            } else {
                $apiValue = $periodData[$mapping->api_field] ?? null;
            }
            
            if ($apiValue !== null) {
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
        if ($provider->code === 'zego') {
            $this->calculateZegoSpecialPrices($period, $periodData, $tourData);
        } elseif ($provider->code === 'bestconsortium' || $provider->code === 'best') {
            $this->calculateBestPeriodData($period, $periodData, $tourData);
        }
        
        Log::info('Creating period from array data', [
            'provider' => $provider->code,
            'tour_id' => $tourModel->id,
            'period_code' => $period->period_code,
            'start_date' => $period->start_date,
            'end_date' => $period->end_date,
            'price1' => $period->price1
        ]);
        
        // Remove attributes that don't exist in the table
        $period->offsetUnset('status_period_text');
        
        $period->save();
        
        return $period;
    }

    private function createDefaultPeriod($provider, $tourData, $tourModel)
    {
        $period = $this->createBasePeriod($provider, $tourModel);
        
        // Set fallback values from tour data
        $this->setFallbackPeriodValues($period, $tourData);
        
        Log::info('Creating default period', [
            'provider' => $provider->code,
            'tour_id' => $tourModel->id,
            'period_code' => $period->period_code,
            'start_date' => $period->start_date,
            'end_date' => $period->end_date,
            'price1' => $period->price1
        ]);
        
        // Remove attributes that don't exist in the table
        $period->offsetUnset('status_period_text');
        
        $period->save();
    }

    private function createBasePeriod($provider, $tourModel)
    {
        $period = new TourPeriodModel();
        $period->tour_id = $tourModel->id;
        $period->api_type = $provider->code;
        
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
            $possibleStartFields = ['tour_date_min', 'start_date', 'departure_date', 'period_start', 'date_from'];
            foreach ($possibleStartFields as $field) {
                if (isset($data[$field])) {
                    $period->start_date = $data[$field];
                    break;
                }
            }
        }
        
        // Fallback values for end_date
        if (!$period->end_date) {
            $possibleEndFields = ['tour_date_max', 'end_date', 'return_date', 'period_end', 'date_to'];
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
        if (!$period->special_price1) {
            $possibleSpecialPriceFields = ['special_price1', 'discount_price', 'promotion_price', 'net_price'];
            foreach ($possibleSpecialPriceFields as $field) {
                if (isset($data[$field]) && is_numeric($data[$field])) {
                    $period->special_price1 = (float)$data[$field];
                    break;
                }
            }
            
            // ถ้าไม่มี special price ให้ใช้ราคาปกติ
            if (!$period->special_price1 && $period->price1) {
                $period->special_price1 = $period->price1;
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
        
        // ผู้ใหญ่พักคู่ (price1)
        $price_start = $periodData['Price'] ?? 0;
        $price_end = $periodData['Price_End'] ?? 0;
        
        $period->price1 = $price_start;
        if ($price_end > 0 && $price_start > $price_end) {
            // มีโปรโมชั่น
            $period->special_price1 = $price_end;
            $discount = $price_start - $price_end;
            $discountPercent = $price_start != 0 ? ($discount / $price_start) * 100 : 0;
            $maxDiscountPercent = max($maxDiscountPercent, $discountPercent);
        } else {
            // ไม่มีโปรโมชั่น
            $period->special_price1 = $price_start;
        }
        
        // ผู้ใหญ่พักเดี่ยว (price2)
        $price_single_start = $periodData['Price_Single_Bed'] ?? 0;
        $price_single_end = $periodData['Price_Single_Bed_End'] ?? 0;
        
        $period->price2 = $price_single_start;
        if ($price_single_end > 0 && $price_single_start > $price_single_end) {
            $period->special_price2 = $price_single_end;
            $discount = $price_single_start - $price_single_end;
            $discountPercent = $price_single_start != 0 ? ($discount / $price_single_start) * 100 : 0;
            $maxDiscountPercent = max($maxDiscountPercent, $discountPercent);
        } else {
            $period->special_price2 = $price_single_start;
        }
        
        // เด็กมีเตียง (price3)
        $price_child_start = $periodData['Price_Child'] ?? 0;
        $price_child_end = $periodData['Price_Child_End'] ?? 0;
        
        $period->price3 = $price_child_start;
        if ($price_child_end > 0 && $price_child_start > $price_child_end) {
            $period->special_price3 = $price_child_end;
            $discount = $price_child_start - $price_child_end;
            $discountPercent = $price_child_start != 0 ? ($discount / $price_child_start) * 100 : 0;
            $maxDiscountPercent = max($maxDiscountPercent, $discountPercent);
        } else {
            $period->special_price3 = $price_child_start;
        }
        
        // เด็กไม่มีเตียง (price4)
        $price_childnb_start = $periodData['Price_ChildNB'] ?? 0;
        $price_childnb_end = $periodData['Price_ChildNB_End'] ?? 0;
        
        $period->price4 = $price_childnb_start;
        if ($price_childnb_end > 0 && $price_childnb_start > $price_childnb_end) {
            $period->special_price4 = $price_childnb_end;
            $discount = $price_childnb_start - $price_childnb_end;
            $discountPercent = $price_childnb_start != 0 ? ($discount / $price_childnb_start) * 100 : 0;
            $maxDiscountPercent = max($maxDiscountPercent, $discountPercent);
        } else {
            $period->special_price4 = $price_childnb_start;
        }
        
        Log::info('Zego special prices calculated', [
            'period_id' => $period->period_code,
            'max_discount_percent' => $maxDiscountPercent,
            'price1' => $period->price1,
            'special_price1' => $period->special_price1,
            'price2' => $period->price2,
            'special_price2' => $period->special_price2
        ]);
        
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
        
        // 3. คำนวณ special_price จาก adultPrice_old - adultPrice
        $price1_old = isset($periodData['adultPrice_old']) ? floatval($periodData['adultPrice_old']) : 0;
        $price1 = isset($periodData['adultPrice']) ? floatval($periodData['adultPrice']) : 0;
        
        if ($price1_old > $price1 && $price1 > 0) {
            $discount = $price1_old - $price1;
            $period->price1 = $price1_old; // ราคาเต็ม
            
            if ($discount < $price1_old) {
                $period->special_price1 = $discount; // ส่วนลด
            } else {
                $period->special_price1 = 0.00;
            }
        } else {
            $period->price1 = $price1;
            $period->special_price1 = $price1; // ไม่มีส่วนลด ให้เท่ากับราคาปกติ
        }
        
        // ราคาอื่นๆ (ไม่คำนวณส่วนลด)
        $period->price2 = isset($periodData['singlePrice']) ? floatval($periodData['singlePrice']) : 0;
        $period->price3 = isset($periodData['childWbPrice']) ? floatval($periodData['childWbPrice']) : 0;
        $period->price4 = isset($periodData['childNbPrice']) ? floatval($periodData['childNbPrice']) : 0;
        
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
        
        // 6. Set group size
        if (isset($periodData['groupSize'])) {
            $period->group = (int)$periodData['groupSize'];
        }
        
        Log::info('Best period data calculated', [
            'period_code' => $period->period_code,
            'start_date' => $period->start_date,
            'end_date' => $period->end_date,
            'day' => $period->day,
            'night' => $period->night,
            'price1' => $period->price1,
            'special_price1' => $period->special_price1,
            'status_period' => $period->status_period,
            'count' => $period->count,
            'avbl' => $avbl
        ]);
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
     * อัพเดท promotion สำหรับ Zego tour ตาม max discount percent
     * - >= 30% = โปรไฟไหม้ (promotion1 = Y, promotion2 = N)
     * - > 0% และ < 30% = โปรธรรมดา (promotion1 = N, promotion2 = Y)
     * - 0% = ไม่มีโปร (promotion1 = N, promotion2 = N)
     */
    private function updateZegoPromotion($tourModel, $maxDiscountPercent)
    {
        if ($maxDiscountPercent >= 30) {
            $tourModel->promotion1 = 'Y';
            $tourModel->promotion2 = 'N';
            Log::info('Zego tour set as fire promotion', [
                'tour_id' => $tourModel->id,
                'discount_percent' => $maxDiscountPercent
            ]);
        } elseif ($maxDiscountPercent > 0) {
            $tourModel->promotion1 = 'N';
            $tourModel->promotion2 = 'Y';
            Log::info('Zego tour set as regular promotion', [
                'tour_id' => $tourModel->id,
                'discount_percent' => $maxDiscountPercent
            ]);
        } else {
            $tourModel->promotion1 = 'N';
            $tourModel->promotion2 = 'N';
            Log::info('Zego tour has no promotion', [
                'tour_id' => $tourModel->id,
                'discount_percent' => $maxDiscountPercent
            ]);
        }
        
        $tourModel->save();
    }

    private function updateTourPrice($tourModel)
    {
        $cheapestPeriod = TourPeriodModel::where('tour_id', $tourModel->id)
            ->where('api_type', $tourModel->api_type)
            ->whereNull('deleted_at')
            ->orderByRaw('(price1 - special_price1) ASC')
            ->first();
            
        if ($cheapestPeriod) {
            $netPrice = $cheapestPeriod->price1 - $cheapestPeriod->special_price1;
            
            // Calculate price group
            $priceGroup = 1;
            if ($netPrice > 10000 && $netPrice <= 20000) $priceGroup = 2;
            elseif ($netPrice > 20000 && $netPrice <= 30000) $priceGroup = 3;
            elseif ($netPrice > 30000 && $netPrice <= 50000) $priceGroup = 4;
            elseif ($netPrice > 50000 && $netPrice <= 80000) $priceGroup = 5;
            elseif ($netPrice > 80000) $priceGroup = 6;
            
            $tourModel->update([
                'price' => $cheapestPeriod->price1,
                'special_price' => $cheapestPeriod->special_price1,
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
        
        // Build query with filters if log_id is provided
        $query = $provider->duplicates()->with(['existingTour', 'syncLog'])
            ->where('status', 'pending');
            
        // Filter by log_id if provided
        if (request()->has('log_id')) {
            $query->where('sync_log_id', request('log_id'));
        }
        
        $duplicates = $query->latest()->paginate(20);
        
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
            
            // ใช้ performSync เดียวสำหรับทุก provider
            $result = $this->performSync($provider, 'manual', 0);
            
            return response()->json([
                'success' => true,
                'message' => 'Sync completed successfully',
                'data' => $result
            ]);
            
        } catch (\Exception $e) {
            Log::error('Sync provider error: ' . $e->getMessage(), [
                'provider_id' => $request->get('provider_id'),
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage()
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
            
            // If it's an array but not structured, try to get the first string value
            $locationValue = reset($locationValue);
            // If still not a string, convert to string
            if (!is_string($locationValue)) {
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
            Log::error('Error creating schedule: ' . $e->getMessage());
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
            Log::error('Error updating schedule: ' . $e->getMessage());
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
            Log::error('Error deleting schedule: ' . $e->getMessage());
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
                Log::info("Schedule {$scheduleId} is inactive, skipping");
                return;
            }

            $schedule->markAsRunning();
            
            Log::info("Starting scheduled sync for provider: {$schedule->apiProvider->name} (Schedule: {$schedule->name})");

            // เรียกใช้ performSync โดยผ่าน sync_limit หากมี และระบุว่าเป็น auto sync (scheduled)
            $limit = $schedule->sync_limit;
            $result = $this->performSync($schedule->apiProvider, 'auto', $limit);

            // performSync จะ return array with log_id และ summary หรือ throw exception
            $schedule->markAsSuccess();
            Log::info("Scheduled sync completed successfully for provider: {$schedule->apiProvider->name}");
            
            return [
                'success' => true,
                'log_id' => $result['log_id'],
                'summary' => $result['summary']
            ];

        } catch (\Exception $e) {
            Log::error("Error in scheduled sync for schedule {$scheduleId}: " . $e->getMessage());
            
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
            Log::error('Test schedule error: ' . $e->getMessage());
            
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
        // Force JAPAN as country (like original code) - เช็ค country_check_change ก่อน
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
        
        // Handle airline from P_AIRLINE code - เช็ค airline_check_change ก่อน
        if (!$isUpdating || !isset($tourModel->airline_check_change) || $tourModel->airline_check_change == null) {
            if (isset($tourData['P_AIRLINE']) && $tourData['P_AIRLINE']) {
                $airline = \App\Models\Backend\TravelTypeModel::where('code', $tourData['P_AIRLINE'])
                    ->where('status', 'on')
                    ->whereNull('deleted_at')
                    ->first();
                    
                if ($airline) {
                    $tourModel->airline_id = $airline->id;
                }
            }
        }
        
        // Set special configuration values (like original code) - เฉพาะ new tour เท่านั้น
        if (!$isUpdating) {
            $tourModel->image_check_change = 2; // 1 ไม่ดึงรูปจาก Api , 2 ดึงรูปจาก Api
            $tourModel->data_type = 2; // 1 system , 2 api
            $tourModel->api_type = 'ttn';
            
            // wholesale_id and group_id are already set by config, but ensure they're correct
            $tourModel->wholesale_id = 35; // TTN Japan specific
            $tourModel->group_id = 3;
        }
        
        Log::info('Applied TTN Japan specific logic', [
            'is_updating' => $isUpdating,
            'tour_api_id' => $tourData['P_ID'] ?? 'unknown',
            'country_id' => $tourModel->country_id,
            'city_id' => $tourModel->city_id ?? null,
            'airline_id' => $tourModel->airline_id ?? null,
            'p_location' => $tourData['P_LOCATION'] ?? null,
            'p_airline' => $tourData['P_AIRLINE'] ?? null
        ]);
    }
}