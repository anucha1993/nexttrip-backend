<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Backend\ApiProviderModel;
use App\Http\Controllers\Backend\ApiManagementController;
use Illuminate\Http\Request;

class TestZegoSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:zego-sync {--limit=10}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Zego API sync with specified limit';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $limit = $this->option('limit');
        
        $this->info("=== Testing ZEGO API with {$limit} records ===");
        $this->newLine();

        // Find Zego provider
        $provider = ApiProviderModel::where('code', 'ZEGO')->first();
        if (!$provider) {
            $this->error('❌ ZEGO provider not found');
            return 1;
        }

        $this->info("✓ ZEGO Provider found: {$provider->name}");
        $this->info("URL: {$provider->url}");
        $this->newLine();

        // Create controller instance
        $controller = new ApiManagementController();

        // Create mock request with limit
        $request = new Request();
        $request->merge([
            'provider' => 'ZEGO', 
            'limit' => $limit
        ]);

        $this->info("🔄 Starting sync with {$limit} records limit...");
        $this->newLine();

        try {
            $result = $controller->syncFromProvider($request);
            $responseData = json_decode($result->getContent(), true);
            
            if ($responseData['success']) {
                $this->info('✅ Sync completed successfully!');
                $this->info('Summary:');
                foreach ($responseData['summary'] as $key => $value) {
                    $this->info("  - {$key}: {$value}");
                }
                
                // Check recent tours and their periods
                $this->newLine();
                $this->info('📊 Checking recent ZEGO tours and periods...');
                $recentTours = \App\Models\Backend\TourModel::where('api_type', 'ZEGO')
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get();
                    
                if ($recentTours->isEmpty()) {
                    $this->error('❌ No ZEGO tours found in database');
                } else {
                    foreach ($recentTours as $tour) {
                        $periodCount = \App\Models\Backend\TourPeriodModel::where('tour_id', $tour->id)->count();
                        $this->info("  Tour: {$tour->code} | API ID: {$tour->api_id} | Periods: {$periodCount}");
                        
                        // Check if periods exist
                        if ($periodCount == 0) {
                            $this->warn('    ⚠️ No periods found for this tour!');
                        }
                    }
                }
                
            } else {
                $this->error('❌ Sync failed: ' . $responseData['message']);
            }
            
        } catch (\Exception $e) {
            $this->error('❌ Exception occurred: ' . $e->getMessage());
            $this->error('Stack trace:');
            $this->error($e->getTraceAsString());
        }

        $this->newLine();
        $this->info('📝 Check logs for detailed information:');
        $this->info('tail storage/logs/laravel.log');
        
        return 0;
    }
}
