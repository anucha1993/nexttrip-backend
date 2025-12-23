<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Backend\ApiProviderModel;
use App\Models\Backend\TourModel;
use App\Http\Controllers\Backend\ApiManagementController;

class TestTourFactorySyncCommand extends Command
{
    protected $signature = 'test:tourfactory-sync {--limit=3 : Number of records to sync}';
    protected $description = 'Test Tour Factory API sync with limited records';

    public function handle()
    {
        $limit = $this->option('limit');
        
        $this->info("=== Testing TOUR FACTORY API with {$limit} records ===");
        
        // Find provider
        $provider = ApiProviderModel::where('code', 'tourfactory')
            ->orWhere('code', 'tour_factory')
            ->first();
            
        if (!$provider) {
            $this->error('✗ Tour Factory provider not found');
            return 1;
        }
        
        $this->info("✓ Tour Factory Provider found: {$provider->name}");
        $this->info("URL: {$provider->url}");
        
        $this->newLine();
        $this->info("🔄 Starting sync with {$limit} records limit...");
        $this->newLine();
        
        try {
            $controller = new ApiManagementController();
            $result = $controller->performSync($provider, 'manual', $limit);
            
            $this->info('✅ Sync completed successfully!');
            $this->info('Summary:');
            $summary = $result['summary'];
            $this->info("  - total_records: {$summary['total_records']}");
            $this->info("  - created_tours: {$summary['created_tours']}");
            $this->info("  - duplicated_tours: {$summary['duplicated_tours']}");
            if (isset($summary['skipped_tours'])) {
                $this->info("  - skipped_tours: {$summary['skipped_tours']}");
            }
            $this->info("  - error_count: {$summary['error_count']}");
            
            $this->newLine();
            $this->info('📊 Checking recent Tour Factory tours and periods...');
            
            $recentTours = TourModel::where('api_type', $provider->code)
                ->whereNull('deleted_at')
                ->orderBy('id', 'desc')
                ->limit(3)
                ->get();
                
            if ($recentTours->count() > 0) {
                $this->info("✓ Found {$recentTours->count()} recent tours:");
                foreach ($recentTours as $tour) {
                    $periodCount = $tour->period()->count();
                    $this->info("  - {$tour->code}: {$tour->name} ({$periodCount} periods)");
                }
            } else {
                $this->warn('No tours found after sync');
            }
            
            $this->newLine();
            $this->info('📝 Check logs for detailed information:');
            $this->line('Get-Content storage\logs\laravel.log | Select-Object -Last 20');
            
        } catch (\Exception $e) {
            $this->error('✗ Sync failed: ' . $e->getMessage());
            $this->error('Trace: ' . $e->getTraceAsString());
            return 1;
        }
        
        return 0;
    }
}
