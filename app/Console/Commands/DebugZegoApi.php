<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class DebugZegoApi extends Command
{
    protected $signature = 'zego:debug-api';
    protected $description = 'Debug Zego API response structure';

    public function handle()
    {
        $this->info('🔍 Debugging Zego API Response...');
        
        try {
            $response = Http::withHeaders([
                "Content-Type" => "application/json",
                "auth-token" => env('ZEGO_API_KEY'),
            ])->timeout(30)->get('https://www.zegoapi.com/v1.5/programtours');
            
            if ($response->successful()) {
                $data = $response->json();
                $recordCount = is_array($data) ? count($data) : 0;
                
                $this->info("✅ API Response Success!");
                $this->info("📊 Total Records: {$recordCount}");
                
                if ($recordCount > 0) {
                    // Check first record structure
                    $firstRecord = $data[0];
                    $this->info("🔍 First Record Keys: " . implode(', ', array_keys($firstRecord)));
                    
                    // Check for periods
                    $totalPeriods = 0;
                    $recordsWithPeriods = 0;
                    
                    foreach ($data as $index => $record) {
                        if (isset($record['Periods']) && is_array($record['Periods'])) {
                            $periodCount = count($record['Periods']);
                            $totalPeriods += $periodCount;
                            if ($periodCount > 0) {
                                $recordsWithPeriods++;
                            }
                            
                            if ($index < 3) { // Show first 3 records details
                                $this->info("  Record {$index}: ProductID={$record['ProductID']}, Periods={$periodCount}");
                            }
                        } else {
                            if ($index < 3) {
                                $this->info("  Record {$index}: ProductID={$record['ProductID']}, Periods=0 (no Periods key or empty)");
                            }
                        }
                    }
                    
                    $this->info("📅 Total Periods: {$totalPeriods}");
                    $this->info("📈 Records with Periods: {$recordsWithPeriods}/{$recordCount}");
                    
                    // Show sample period structure
                    foreach ($data as $record) {
                        if (isset($record['Periods']) && is_array($record['Periods']) && count($record['Periods']) > 0) {
                            $samplePeriod = $record['Periods'][0];
                            $this->info("🎯 Sample Period Keys: " . implode(', ', array_keys($samplePeriod)));
                            break;
                        }
                    }
                    
                } else {
                    $this->error("❌ No records found in API response!");
                }
                
            } else {
                $this->error("❌ API Request Failed: " . $response->status());
                $this->error("Response: " . $response->body());
            }
            
        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
        }
    }
}