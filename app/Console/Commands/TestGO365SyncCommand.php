<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Backend\ApiProviderModel;
use App\Http\Controllers\Backend\ApiManagementController;

class TestGO365SyncCommand extends Command
{
    protected $signature = 'test:go365-sync {--limit=5 : Number of records to sync}';
    protected $description = 'Test GO365 API sync with limited records';

    public function handle()
    {
        $limit = $this->option('limit');
        
        $this->info("=== Testing GO365 API with {$limit} records ===");
        
        // Find GO365 provider
        $provider = ApiProviderModel::where('code', 'go365')->first();
        
        if (!$provider) {
            $this->error('GO365 Provider not found!');
            return 1;
        }
        
        $this->info("✓ GO365 Provider found: {$provider->name}");
        
        // Perform manual sync with limit
        $controller = new ApiManagementController();
        
        try {
            $this->info("\n🔄 Starting sync with {$limit} records limit...\n");
            
            $result = $controller->syncManual($provider->id, $limit);
            
            if ($result->getData()->success) {
                $summary = $result->getData()->summary;
                
                $this->info("✅ Sync completed successfully!");
                $this->info("Summary:");
                $this->info("  - total_records: " . ($summary->total_records ?? 0));
                $this->info("  - created_tours: " . ($summary->created_tours ?? 0));
                $this->info("  - duplicated_tours: " . ($summary->duplicated_tours ?? 0));
                $this->info("  - error_count: " . ($summary->error_count ?? 0));
            } else {
                $this->error("❌ Sync failed: " . ($result->getData()->message ?? 'Unknown error'));
                return 1;
            }
            
            // Check recent tours
            $this->info("\n📊 Checking recent GO365 tours and periods...");
            
            $recentTours = \DB::table('tb_tour')
                ->where('api_type', 'go365')
                ->orderBy('id', 'desc')
                ->limit($limit)
                ->get(['id', 'code1', 'name']);
            
            if ($recentTours->count() > 0) {
                $this->info("✓ Found {$recentTours->count()} recent tours:");
                foreach ($recentTours as $tour) {
                    $periodCount = \DB::table('tb_tour_period')
                        ->where('tour_id', $tour->id)
                        ->count();
                    $this->info("  - {$tour->code1}: {$tour->name} ({$periodCount} periods)");
                }
            } else {
                $this->warn("⚠ No tours found after sync");
            }
            
            $this->info("\n📝 Check logs for detailed information:");
            $this->info("Get-Content storage\\logs\\laravel.log | Select-Object -Last 20");
            
        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }
        
        return 0;
    }
}
