<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Backend\ApiProviderModel;
use App\Http\Controllers\Backend\ApiManagementController;

class TestTTNJapanSyncCommand extends Command
{
    protected $signature = 'test:ttn-japan-sync {--limit=5 : Number of records to sync}';
    protected $description = 'Test TTN Japan API sync with limit';

    public function handle()
    {
        $limit = $this->option('limit');
        
        $this->info("=== Testing TTN JAPAN API with {$limit} records ===");
        
        // Find TTN Japan provider
        $provider = ApiProviderModel::where('code', 'ttn_japan')->first();
        
        if (!$provider) {
            $this->error('TTN Japan Provider not found!');
            return 1;
        }
        
        $this->info("✓ TTN Japan Provider found: {$provider->name}");
        $this->info("URL: {$provider->url}");
        $this->newLine();
        
        // Perform sync
        $this->info("🔄 Starting sync with {$limit} records limit...");
        $this->newLine();
        
        try {
            $controller = new ApiManagementController();
            $syncResult = $controller->performSync($provider, 'manual', $limit);
            
            $this->info("✅ Sync completed successfully!");
            $this->info("Summary:");
            $this->info("  - total_records: {$syncResult['summary']['total_records']}");
            $this->info("  - created_tours: {$syncResult['summary']['created_tours']}");
            $this->info("  - duplicated_tours: {$syncResult['summary']['duplicated_tours']}");
            $this->info("  - error_count: {$syncResult['summary']['error_count']}");
            
            $this->newLine();
            $this->info("📊 Checking recent TTN Japan tours and periods...");
            
            // Check recent tours
            $tours = \DB::table('tb_tour')
                ->where('wholesale_id', 35)
                ->where('data_type', 2)
                ->where('api_type', 'ttn')
                ->orderBy('id', 'desc')
                ->limit(3)
                ->get(['id', 'code', 'name']);
            
            if ($tours->count() > 0) {
                $this->info("✓ Found {$tours->count()} recent tours:");
                foreach ($tours as $tour) {
                    $periodCount = \DB::table('tb_tour_period')
                        ->where('tour_id', $tour->id)
                        ->where('api_type', 'ttn')
                        ->count();
                    
                    $this->info("  - {$tour->code}: {$tour->name} ({$periodCount} periods)");
                }
            } else {
                $this->warn("❌ No TTN Japan tours found in database");
            }
            
            $this->newLine();
            $this->info("📝 Check logs for detailed information:");
            $this->line("Get-Content storage\\logs\\laravel.log | Select-Object -Last 20");
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error("❌ Sync failed: {$e->getMessage()}");
            $this->error($e->getTraceAsString());
            return 1;
        }
    }
}
