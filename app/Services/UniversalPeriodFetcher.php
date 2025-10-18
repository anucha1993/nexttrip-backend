<?php

namespace App\Services;

use App\Models\Backend\ApiProviderModel;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UniversalPeriodFetcher
{
    protected $provider;
    protected $headers;
    
    public function __construct(ApiProviderModel $provider, array $headers = [])
    {
        $this->provider = $provider;
        $this->headers = $headers;
    }
    
    /**
     * Get periods count for any API provider
     */
    public function getPeriodCount($responseData, $limit = 5)
    {
        if (!$this->provider->requires_multi_step) {
            // ถ้าไม่ใช่ multi-step API ให้ใช้วิธีเดิม
            return $this->countDirectPeriods($responseData);
        }
        
        // Multi-step API handling
        return $this->countMultiStepPeriods($responseData, $limit);
    }
    
    /**
     * Count periods from direct API response
     */
    private function countDirectPeriods($responseData)
    {
        $periodCount = 0;
        
        if (is_array($responseData)) {
            foreach ($responseData as $record) {
                // Check various period field naming conventions
                $periodFields = ['Periods', 'periods', 'period', 'Period'];
                
                foreach ($periodFields as $field) {
                    if (isset($record[$field]) && is_array($record[$field])) {
                        $periodCount += count($record[$field]);
                        break; // Found periods, move to next record
                    }
                }
            }
        }
        
        return $periodCount;
    }
    
    /**
     * Count periods from multi-step API calls
     */
    private function countMultiStepPeriods($responseData, $limit = 5)
    {
        if (!is_array($responseData) || empty($responseData)) {
            return 0;
        }
        
        $periodCount = 0;
        $sampleRecords = array_slice($responseData, 0, $limit);
        $baseUrl = rtrim(parse_url($this->provider->url)['scheme'] . '://' . parse_url($this->provider->url)['host'], '/');
        $idField = $this->provider->url_parameters['tour_detail_id_field'] ?? 'P_ID';
        
        Log::info("Universal Period Fetcher: Processing " . count($sampleRecords) . " sample records");
        
        foreach ($sampleRecords as $programRecord) {
            if (!isset($programRecord[$idField])) {
                continue;
            }
            
            try {
                $recordId = $programRecord[$idField];
                
                // Step 1: Get tour details if needed
                if ($this->provider->tour_detail_endpoint) {
                    $tourDetailUrl = $baseUrl . str_replace('{' . $idField . '}', $recordId, $this->provider->tour_detail_endpoint);
                    
                    $tourResponse = Http::withHeaders($this->headers)
                        ->timeout(10)
                        ->get($tourDetailUrl);
                    
                    if (!$tourResponse->successful()) {
                        Log::warning("Failed to get tour details for ID: {$recordId}");
                        continue;
                    }
                    
                    $tourData = $tourResponse->json();
                    if (empty($tourData)) {
                        continue;
                    }
                    
                    // Use first tour detail record
                    $tourDetail = is_array($tourData) ? $tourData[0] : $tourData;
                    
                    // Update record ID if needed from tour detail
                    if (isset($tourDetail[$idField])) {
                        $recordId = $tourDetail[$idField];
                    }
                }
                
                // Step 2: Get periods
                if ($this->provider->period_endpoint) {
                    $periodUrl = $baseUrl . str_replace('{' . $idField . '}', $recordId, $this->provider->period_endpoint);
                    
                    $periodResponse = Http::withHeaders($this->headers)
                        ->timeout(10)
                        ->get($periodUrl);
                    
                    if ($periodResponse->successful()) {
                        $periodData = $periodResponse->json();
                        $recordPeriods = $this->extractPeriodsFromResponse($periodData);
                        $periodCount += $recordPeriods;
                        
                        Log::info("Record {$recordId}: Found {$recordPeriods} periods");
                    }
                }
                
            } catch (\Exception $e) {
                Log::warning("Error processing record {$recordId}: " . $e->getMessage());
                continue;
            }
        }
        
        // Estimate total periods based on sample
        if (count($sampleRecords) > 0 && $periodCount > 0) {
            $avgPeriodsPerRecord = $periodCount / count($sampleRecords);
            $totalEstimated = round($avgPeriodsPerRecord * count($responseData));
            
            Log::info("Period estimation: {$periodCount} periods in " . count($sampleRecords) . " samples, estimated total: {$totalEstimated}");
            
            return $totalEstimated;
        }
        
        return $periodCount;
    }
    
    /**
     * Extract period count from API response data
     */
    private function extractPeriodsFromResponse($periodData)
    {
        if (!is_array($periodData)) {
            return 0;
        }
        
        $count = 0;
        
        // Handle different period response structures
        foreach ($periodData as $periodGroup) {
            if (isset($periodGroup['Price']) && is_array($periodGroup['Price'])) {
                // TTN Japan structure
                $count += count($periodGroup['Price']);
            } elseif (isset($periodGroup['periods']) && is_array($periodGroup['periods'])) {
                // Generic periods array
                $count += count($periodGroup['periods']);
            } elseif (is_array($periodGroup)) {
                // Direct period array
                $count++;
            }
        }
        
        return $count;
    }
    
    /**
     * Get period field mappings for this provider
     */
    public function getPeriodMappings()
    {
        return $this->provider->fieldMappings()
            ->where('field_type', 'period')
            ->pluck('api_field', 'local_field')
            ->toArray();
    }
}