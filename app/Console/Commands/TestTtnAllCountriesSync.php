<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Backend\ApiManagementController;

class TestTtnAllCountriesSync extends Command
{
    protected $signature = 'test:ttn-all {--limit=5}';
    protected $description = 'Test TTN All Countries API sync functionality';

    public function handle()
    {
        $this->info('Testing TTN All Countries API...');
        
        try {
            // Create controller instance
            $controller = new ApiManagementController();
            
            // Test API connection using TTN's correct endpoint
            $this->info('Testing API connection...');
            
            $response = Http::withHeaders([
                "Content-Type" => "application/json; charset=UTF-8",
            ])->get('https://online.ttnconnect.com/api/agency/get-programId?wholesale_id=10&group_id=3');
            
            if (!$response->successful()) {
                $this->error('API connection failed: ' . $response->status());
                $this->error('Response body: ' . $response->body());
                return 1;
            }
            
            $programs = $response->json();
            $this->info('API connection successful. Found ' . count($programs) . ' programs.');
            
            if (empty($programs)) {
                $this->warn('No programs found');
                return 1;
            }
            
            // Get provider ID (TTN All Countries)
            $provider = \DB::table('tb_api_providers')->where('code', 'ttn_all')->first();
            if (!$provider) {
                $this->error('TTN All Countries provider not found!');
                return 1;
            }
            
            $limit = $this->option('limit');
            $testPrograms = array_slice($programs, 0, $limit);
            
            $this->info("Processing {$limit} programs...");
            
            foreach ($testPrograms as $index => $program) {
                $this->info("\n=== Processing Program " . ($index + 1) . "/{$limit} ===");
                $this->info("Program ID: {$program['P_ID']}");
                
                // Get program details
                $this->info('Fetching program details...');
                $detailResponse = Http::withHeaders([
                    "Content-Type" => "application/json; charset=UTF-8",
                ])->get("https://online.ttnconnect.com/api/agency/program/{$program['P_ID']}");
                
                if (!$detailResponse->successful()) {
                    $this->error('Failed to fetch program details: ' . $detailResponse->status());
                    continue;
                }
                
                $programDetail = $detailResponse->json();
                $this->info('Program Name: ' . ($programDetail['P_NAME'] ?? 'N/A'));
                $this->info('Location: ' . ($programDetail['P_LOCATION'] ?? 'N/A'));
                
                // Test country detection
                $location = $programDetail['P_LOCATION'] ?? '';
                $this->info("Testing country detection for location: {$location}");
                
                // Simulate the country detection process
                $countryResult = $this->testCountryDetection($location, $provider);
                $this->info("Country detection result: {$countryResult}");
                
                // Test city detection  
                $cityResult = $this->testCityDetection($location, $provider);
                $this->info("City detection result: {$cityResult}");
                
                // Get periods
                $this->info('Fetching periods...');
                $periodResponse = Http::withHeaders([
                    "Content-Type" => "application/json; charset=UTF-8",
                ])->get("https://online.ttnconnect.com/api/agency/program/period/{$program['P_ID']}");
                
                if ($periodResponse->successful()) {
                    $periods = $periodResponse->json();
                    $this->info('Found ' . count($periods) . ' periods');
                } else {
                    $this->warn('Failed to fetch periods: ' . $periodResponse->status());
                }
            }
            
            $this->info("\n✅ Test completed successfully!");
            
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return 1;
        }
        
        return 0;
    }
    
    private function testCountryDetection($location, $provider)
    {
        if (empty($location)) {
            return '[]';
        }
        
        // Try to find country by location
        $country = \DB::table('tb_country')
            ->where(function($query) use ($location) {
                $query->where('country_name_en', 'like', '%' . $location . '%')
                      ->orWhere('country_name_th', 'like', '%' . $location . '%');
            })
            ->where('status', 'on')
            ->whereNull('deleted_at')
            ->first();
        
        if ($country) {
            return json_encode([(string)$country->id]);
        }
        
        // Use provider fallback
        $config = json_decode($provider->config, true);
        if (isset($config['fallback_country'])) {
            $fallback = \DB::table('tb_country')
                ->where('country_name_en', 'like', '%' . $config['fallback_country'] . '%')
                ->where('status', 'on')
                ->whereNull('deleted_at')
                ->first();
            
            if ($fallback) {
                return json_encode([(string)$fallback->id]);
            }
        }
        
        return '[]';
    }
    
    private function testCityDetection($location, $provider)
    {
        if (empty($location)) {
            return '[]';
        }
        
        // Try to find city by location
        $city = \DB::table('tb_city')
            ->where(function($query) use ($location) {
                $query->where('city_name_en', 'like', '%' . $location . '%')
                      ->orWhere('city_name_th', 'like', '%' . $location . '%');
            })
            ->where('status', 'on')
            ->whereNull('deleted_at')
            ->first();
        
        if ($city) {
            return json_encode([(string)$city->id]);
        }
        
        return '[]';
    }
}