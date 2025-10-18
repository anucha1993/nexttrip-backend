<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Backend\ApiManagementController;

class TestTtnJapanSync extends Command
{
    protected $signature = 'test:ttn-japan';
    protected $description = 'Test TTN Japan API sync functionality';

    public function handle()
    {
        $this->info('Testing TTN Japan API...');
        
        try {
            // Create controller instance
            $controller = new ApiManagementController();
            
            // Test API connection using TTN's correct endpoint
            $this->info('Testing API connection...');
            
            $response = Http::withHeaders([
                "Content-Type" => "application/json; charset=UTF-8",
            ])->get('https://online.ttnconnect.com/api/agency/get-programId');
            
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
            
            // Test with first program
            $testProgram = $programs[0];
            $this->info("Testing with program ID: {$testProgram['P_ID']}");
            
            // Get program details
            $this->info('Fetching program details...');
            $response = Http::withHeaders([
                "Content-Type" => "application/json; charset=UTF-8",
            ])->get('https://online.ttnconnect.com/api/agency/program/' . $testProgram['P_ID']);
            
            if (!$response->successful()) {
                $this->error('Failed to fetch program details: ' . $response->status());
                $this->error('Response body: ' . $response->body());
                return 1;
            }
            
            $programDetails = $response->json();
            $this->info('Program details fetched successfully');
            
            if (empty($programDetails)) {
                $this->warn('No program details found');
                return 1;
            }
            
            $program = $programDetails[0]; // TTN API returns array of programs
            $this->info('Program Name: ' . $program['P_NAME']);
            $this->info('Program Days: ' . $program['P_DAY'] . ' days ' . $program['P_NIGHT'] . ' nights');
            $this->info('Program Price: ' . number_format($program['P_PRICE']));
            
            // Get periods for the first program
            $this->info('Fetching periods...');
            $response = Http::withHeaders([
                "Content-Type" => "application/json; charset=UTF-8",
            ])->get('https://online.ttnconnect.com/api/agency/program/period/' . $testProgram['P_ID']);
            
            if (!$response->successful()) {
                $this->error('Failed to fetch periods: ' . $response->status());
                $this->error('Response body: ' . $response->body());
                return 1;
            }
            
            $periods = $response->json();
            $this->info('Found ' . count($periods) . ' periods');
            
            if (!empty($periods)) {
                $period = $periods[0];
                $this->info('First period: ' . $period['P_DUE_START'] . ' - ' . $period['P_DUE_END']);
                if (isset($period['Price']) && !empty($period['Price'])) {
                    $price = $period['Price'][0];
                    $this->info('Price: Adult ' . number_format($price['P_ADULT_PRICE']) . ', Single ' . number_format($price['P_SINGLE_PRICE']));
                    $this->info('Available: ' . $price['P_AVAILABLE']);
                }
            }
            
            // Test sync through controller
            $this->info('Testing TTN Japan sync through controller...');
            
            $result = $controller->syncProvider(43); // TTN Japan provider ID is 43
            
            if ($result) {
                $this->info('✅ TTN Japan API sync test completed successfully');
                $this->info('API Structure Summary:');
                $this->info('- Programs endpoint: /api/agency/get-programId');
                $this->info('- Program details: /api/agency/program/{id}');  
                $this->info('- Period details: /api/agency/program/period/{id}');
                $this->info('- Total programs available: ' . count($programs));
                $this->info('- Full sync executed successfully');
            } else {
                $this->error('❌ TTN Japan API sync test failed');
                return 1;
            }
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error('Error testing TTN Japan API: ' . $e->getMessage());
            $this->error('Line: ' . $e->getLine());
            $this->error('File: ' . $e->getFile());
            return 1;
        }
        
        return 0;
    }
}