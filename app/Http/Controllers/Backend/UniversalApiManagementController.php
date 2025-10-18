<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Backend\ApiProviderModel;
use App\Models\Backend\ApiSyncLogModel;
use App\Models\Backend\TourModel;
use App\Models\Backend\TourPeriodModel;
use App\Models\Backend\CountryModel;
use App\Models\Backend\CityModel;
use App\Models\Backend\TravelTypeModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Haruncpi\LaravelIdGenerator\IdGenerator;

class UniversalApiManagementController extends Controller
{
    /**
     * Sync any API provider based on database configuration
     */
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
            
            // ใช้ระบบ Universal Sync
            $result = $this->performUniversalSync($provider, 'manual', 5); // Test with 5 records
            
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
     * Universal sync method that works with any API provider
     */
    private function performUniversalSync($provider, $syncType = 'manual', $limit = 0)
    {
        // Create sync log
        $syncLog = ApiSyncLogModel::create([
            'api_provider_id' => $provider->id,
            'sync_type' => $syncType,
            'status' => 'running',
            'started_at' => now()
        ]);

        try {
            Log::info('Starting universal sync', ['provider' => $provider->name]);
            
            // Get headers from database
            $headers = is_string($provider->headers) ? json_decode($provider->headers, true) : ($provider->headers ?? []);
            
            // Call main API endpoint
            $response = Http::withHeaders($headers)->timeout(120)->get($provider->url);
            
            if (!$response->successful()) {
                throw new \Exception('API request failed with status: ' . $response->status());
            }

            $apiData = $response->json();
            Log::info('API data received', [
                'provider' => $provider->name,
                'count' => is_array($apiData) ? count($apiData) : 0
            ]);
            
            // Check if this provider requires multi-step API calls
            $config = is_string($provider->config) ? json_decode($provider->config, true) : ($provider->config ?? []);
            
            if (isset($config['detail_url_pattern'])) {
                // Multi-step sync (like TTN Japan)
                return $this->performMultiStepSync($provider, $apiData, $syncLog, $limit);
            } else {
                // Single-step sync (like other providers)
                return $this->performSingleStepSync($provider, $apiData, $syncLog, $limit);
            }
            
        } catch (\Exception $e) {
            $syncLog->update([
                'status' => 'error',
                'completed_at' => now(),
                'error_message' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Multi-step sync for providers like TTN Japan
     */
    private function performMultiStepSync($provider, $programIds, $syncLog, $limit = 0)
    {
        $config = is_string($provider->config) ? json_decode($provider->config, true) : ($provider->config ?? []);
        
        $createdCount = 0;
        $updatedCount = 0;
        $errorCount = 0;
        
        // Apply limit if specified
        if ($limit > 0 && is_array($programIds)) {
            $programIds = array_slice($programIds, 0, $limit);
        }
        
        $tour_ids = [];
        $tour_api_ids = [];
        $period_ids = [];
        $period_api_ids = [];
        
        foreach ($programIds as $programId) {
            try {
                // Get program ID from config
                $idField = $config['id_field'] ?? 'id';
                $apiId = $programId[$idField];
                
                // Get program details
                $detailUrl = str_replace('{id}', $apiId, $config['detail_url_pattern']);
                $headers = is_string($provider->headers) ? json_decode($provider->headers, true) : ($provider->headers ?? []);
                
                $response = Http::withHeaders($headers)->timeout(60)->get($detailUrl);
                
                if (!$response->successful()) {
                    Log::warning('Failed to get program details', [
                        'provider' => $provider->name,
                        'program_id' => $apiId,
                        'url' => $detailUrl
                    ]);
                    $errorCount++;
                    continue;
                }
                
                $programDetails = $response->json();
                
                // Process each program (TTN returns array)
                foreach ($programDetails as $program) {
                    // Check if tour exists
                    $tourModel = TourModel::where([
                        'api_id' => $program[$idField],
                        'api_type' => $provider->code
                    ])->whereNull('deleted_at')->first();
                    
                    $isNew = false;
                    if (!$tourModel) {
                        $tourModel = new TourModel();
                        $isNew = true;
                        
                        // Generate tour code
                        $tourModel->code = IdGenerator::generate([
                            'table' => 'tb_tour',
                            'field' => 'code',
                            'length' => 10,
                            'prefix' => 'NT' . date('ym'),
                            'reset_on_prefix_change' => true
                        ]);
                    }
                    
                    // Map tour fields from database configuration
                    $this->mapTourFieldsFromConfig($provider, $program, $tourModel);
                    
                    if ($tourModel->save()) {
                        $tour_ids[] = $tourModel->id;
                        $tour_api_ids[] = $tourModel->api_id;
                        
                        if ($isNew) {
                            $createdCount++;
                        } else {
                            $updatedCount++;
                        }
                        
                        // Process periods if configured
                        if (isset($config['period_url_pattern'])) {
                            $this->processPeriodsFromConfig($provider, $tourModel, $apiId, $period_ids, $period_api_ids);
                        }
                    }
                }
                
            } catch (\Exception $e) {
                Log::error('Error processing program', [
                    'provider' => $provider->name,
                    'program_id' => $apiId ?? 'unknown',
                    'error' => $e->getMessage()
                ]);
                $errorCount++;
            }
        }
        
        // Update sync log
        $syncLog->update([
            'status' => 'completed',
            'completed_at' => now(),
            'total_records' => count($programIds),
            'created_tours' => $createdCount,
            'duplicated_tours' => 0,
            'error_count' => $errorCount
        ]);
        
        Log::info('Multi-step sync completed', [
            'provider' => $provider->name,
            'created' => $createdCount,
            'updated' => $updatedCount,
            'errors' => $errorCount
        ]);
        
        return [
            'summary' => [
                'total_records' => count($programIds),
                'created_tours' => $createdCount,
                'updated_tours' => $updatedCount,
                'error_count' => $errorCount
            ]
        ];
    }
    
    /**
     * Single-step sync for other providers
     */
    private function performSingleStepSync($provider, $apiData, $syncLog, $limit = 0)
    {
        // Implementation for single-step providers (GO365, ZEGO, etc.)
        // This would be the existing logic from the original controller
        
        return [
            'summary' => [
                'total_records' => 0,
                'created_tours' => 0,
                'updated_tours' => 0,
                'error_count' => 0
            ]
        ];
    }
    
    /**
     * Map tour fields based on database field mappings
     */
    private function mapTourFieldsFromConfig($provider, $programData, $tourModel)
    {
        // Get tour field mappings from database
        $tourMappings = $provider->fieldMappings()->where('field_type', 'tour')->get();
        
        foreach ($tourMappings as $mapping) {
            $apiValue = $programData[$mapping->api_field] ?? null;
            
            if ($apiValue !== null) {
                // Apply transformation rules if exists
                if (!empty($mapping->transformation_rules)) {
                    $rules = is_string($mapping->transformation_rules) ? 
                        json_decode($mapping->transformation_rules, true) : 
                        $mapping->transformation_rules;
                    
                    $apiValue = $this->applyTransformationRules($apiValue, $rules);
                }
                
                // Handle special field mappings
                if ($mapping->local_field === 'country_id') {
                    $tourModel->country_id = $this->mapCountryFromConfig($provider, $apiValue);
                } elseif ($mapping->local_field === 'airline_id') {
                    $tourModel->airline_id = $this->mapAirlineFromConfig($provider, $apiValue);
                } else {
                    // Direct field mapping
                    $tourModel->{$mapping->local_field} = $this->processFieldValue($apiValue, $mapping->data_type);
                }
            }
        }
        
        // Set provider-specific values from config
        $config = is_string($provider->config) ? json_decode($provider->config, true) : ($provider->config ?? []);
        
        $tourModel->group_id = $config['group_id'] ?? 1;
        $tourModel->wholesale_id = $config['wholesale_id'] ?? 1;
        $tourModel->data_type = 2; // API data
        $tourModel->api_type = $provider->code;
    }
    
    /**
     * Process periods based on database configuration
     */
    private function processPeriodsFromConfig($provider, $tourModel, $programId, &$period_ids, &$period_api_ids)
    {
        try {
            $config = is_string($provider->config) ? json_decode($provider->config, true) : ($provider->config ?? []);
            $periodUrl = str_replace('{id}', $programId, $config['period_url_pattern']);
            $headers = is_string($provider->headers) ? json_decode($provider->headers, true) : ($provider->headers ?? []);
            
            $response = Http::withHeaders($headers)->timeout(60)->get($periodUrl);
            
            if (!$response->successful()) {
                Log::warning('Failed to get periods', [
                    'provider' => $provider->name,
                    'program_id' => $programId
                ]);
                return;
            }
            
            $periodsData = $response->json();
            
            foreach ($periodsData as $periodGroup) {
                if (isset($periodGroup['Price']) && is_array($periodGroup['Price'])) {
                    foreach ($periodGroup['Price'] as $priceData) {
                        $this->createPeriodFromConfig($provider, $tourModel, $periodGroup, $priceData, $period_ids, $period_api_ids);
                    }
                }
            }
            
        } catch (\Exception $e) {
            Log::error('Error processing periods', [
                'provider' => $provider->name,
                'program_id' => $programId,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Create period from database configuration
     */
    private function createPeriodFromConfig($provider, $tourModel, $periodGroup, $priceData, &$period_ids, &$period_api_ids)
    {
        $periodMappings = $provider->fieldMappings()->where('field_type', 'period')->get();
        
        // Check if period exists
        $existingPeriod = TourPeriodModel::where([
            'tour_id' => $tourModel->id,
            'period_api_id' => $periodGroup['P_ID'],
            'api_type' => $provider->code
        ])->whereNull('deleted_at')->first();
        
        $periodModel = $existingPeriod ?: new TourPeriodModel();
        
        // Map fields from database configuration
        foreach ($periodMappings as $mapping) {
            $apiValue = null;
            
            // Get value from appropriate source
            if (isset($priceData[$mapping->api_field])) {
                $apiValue = $priceData[$mapping->api_field];
            } elseif (isset($periodGroup[$mapping->api_field])) {
                $apiValue = $periodGroup[$mapping->api_field];
            }
            
            if ($apiValue !== null) {
                // Apply transformation rules
                if (!empty($mapping->transformation_rules)) {
                    $rules = is_string($mapping->transformation_rules) ? 
                        json_decode($mapping->transformation_rules, true) : 
                        $mapping->transformation_rules;
                    
                    $apiValue = $this->applyTransformationRules($apiValue, $rules);
                }
                
                $periodModel->{$mapping->local_field} = $this->processFieldValue($apiValue, $mapping->data_type);
            }
        }
        
        // Set required fields
        $periodModel->tour_id = $tourModel->id;
        $periodModel->api_type = $provider->code;
        $periodModel->status_display = 'on';
        
        if ($periodModel->save()) {
            $period_ids[] = $periodModel->id;
            $period_api_ids[] = $periodModel->period_api_id;
        }
    }
    
    /**
     * Map country based on configuration
     */
    private function mapCountryFromConfig($provider, $locationValue)
    {
        // Get conditions for country mapping
        $condition = $provider->conditions()
            ->where('condition_type', 'country_mapping')
            ->where('is_active', true)
            ->first();
        
        if ($condition) {
            $rules = is_string($condition->condition_rules) ? 
                json_decode($condition->condition_rules, true) : 
                $condition->condition_rules;
            
            if ($rules['type'] === 'fixed_country') {
                $country = CountryModel::where('country_name_en', 'like', '%' . $rules['country_filter'] . '%')
                    ->where('status', 'on')
                    ->whereNull('deleted_at')
                    ->first();
                
                return $country ? json_encode([$country->id]) : $rules['fallback'];
            }
        }
        
        return "[]";
    }
    
    /**
     * Map airline based on configuration
     */
    private function mapAirlineFromConfig($provider, $airlineCode)
    {
        if (empty($airlineCode)) return null;
        
        $airline = TravelTypeModel::where('code', $airlineCode)
            ->where('status', 'on')
            ->whereNull('deleted_at')
            ->first();
        
        return $airline ? $airline->id : null;
    }
    
    /**
     * Apply transformation rules
     */
    private function applyTransformationRules($value, $rules)
    {
        if (!is_array($rules)) return $value;
        
        foreach ($rules as $rule) {
            switch ($rule['type'] ?? '') {
                case 'date_to_month_year':
                    if ($value) {
                        try {
                            $date = \Carbon\Carbon::parse($value);
                            $value = $date->format('mY');
                        } catch (\Exception $e) {
                            // Keep original value if parsing fails
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
    
    /**
     * Process field value based on data type
     */
    private function processFieldValue($value, $dataType)
    {
        switch ($dataType) {
            case 'integer':
                return (int)$value;
            case 'decimal':
            case 'float':
                return (float)$value;
            case 'date':
                if ($value) {
                    try {
                        return \Carbon\Carbon::parse($value)->format('Y-m-d');
                    } catch (\Exception $e) {
                        return null;
                    }
                }
                return null;
            default:
                return $value;
        }
    }
}
?>