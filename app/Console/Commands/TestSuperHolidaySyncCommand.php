<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Backend\ApiProviderModel;
use App\Models\Backend\TourModel;
use App\Http\Controllers\Backend\ApiManagementController;

class TestSuperHolidaySyncCommand extends Command
{
    protected $signature = 'test:superholiday-sync {--limit=5 : Number of records to sync}';
    protected $description = 'Test Super Holiday API sync with limit';

    public function handle()
    {
        $limit = $this->option('limit');
        
        $this->info("=== Testing Super Holiday API with {$limit} records ===");
        
        // Find Super Holiday provider
        $provider = ApiProviderModel::where('code', 'superbholiday')
            ->orWhere('code', 'superb_holiday')
            ->orWhere('name', 'like', '%Super%Holiday%')
            ->first();
            
        if (!$provider) {
            $this->error('✗ Super Holiday Provider not found!');
            return 1;
        }
        
        $this->info("✓ Super Holiday Provider found: {$provider->name}");
        $this->info("URL: {$provider->url}");
        $this->newLine();
        
        $this->info("🔄 Starting sync with {$limit} records limit...");
        $this->newLine();
        
        try {
            $controller = new ApiManagementController();
            $result = $controller->performSync($provider, 'manual', $limit);
            
            $this->info('✅ Sync completed successfully!');
            $this->info('Summary:');
            $this->info("  - total_records: {$result['summary']['total_records']}");
            $this->info("  - created_tours: {$result['summary']['created_tours']}");
            $this->info("  - duplicated_tours: {$result['summary']['duplicated_tours']}");
            if (isset($result['summary']['skipped_tours'])) {
                $this->info("  - skipped_tours: {$result['summary']['skipped_tours']}");
            }
            $this->info("  - error_count: {$result['summary']['error_count']}");
            $this->newLine();
            
            // Check recent tours
            $this->info('📊 Checking recent Super Holiday tours and periods...');
            $recentTours = TourModel::where('api_type', 'superbholiday')
                ->orWhere('wholesale_id', 22)
                ->whereNull('deleted_at')
                ->orderBy('id', 'desc')
                ->take(3)
                ->get();
                
            if ($recentTours->count() > 0) {
                $this->info("✓ Found {$recentTours->count()} recent tours:");
                foreach ($recentTours as $tour) {
                    $periodCount = $tour->period()->count();
                    $this->info("  - {$tour->code}: {$tour->name} ({$periodCount} periods)");
                }
            } else {
                $this->warn('No tours found!');
            }
            
            $this->newLine();
            $this->info('📝 Check logs for detailed information:');
            $this->info('Get-Content storage\logs\laravel.log | Select-Object -Last 20');
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error('✗ Sync failed: ' . $e->getMessage());
            $this->error('Line: ' . $e->getLine());
            $this->error('File: ' . $e->getFile());
            return 1;
        }
    }
}
