<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Backend\ApiProviderModel;
use App\Http\Controllers\Backend\ApiManagementController;

class TestTTNAllSyncCommand extends Command
{
    protected $signature = 'test:ttnall-sync {--limit=5 : Number of records to sync}';
    protected $description = 'Test TTN ALL API sync with specified limit';

    public function handle()
    {
        $limit = (int)$this->option('limit');
        
        $this->info("=== Testing TTN ALL API with {$limit} records ===");
        
        // Get TTN ALL provider (ID: 52)
        $provider = ApiProviderModel::with(['fieldMappings'])->find(52);
        
        if (!$provider) {
            $this->error('✗ TTN ALL Provider not found (ID: 52)');
            return 1;
        }
        
        $this->info("✓ TTN ALL Provider found: {$provider->name}");
        $this->line("URL: {$provider->url}");
        $this->line('');
        
        $this->info('🔄 Starting sync with ' . $limit . ' records limit...');
        $this->line('');
        
        // Perform sync
        $controller = new ApiManagementController();
        try {
            $result = $controller->performSync($provider, 'manual', $limit);
            
            $this->info('✅ Sync completed successfully!');
            $this->line('Summary:');
            $this->line('  - total_records: ' . $result['summary']['total_records']);
            $this->line('  - created_tours: ' . $result['summary']['created_tours']);
            $this->line('  - duplicated_tours: ' . $result['summary']['duplicated_tours']);
            $this->line('  - error_count: ' . $result['summary']['error_count']);
            
            // Check recent tours
            $this->line('');
            $this->info('📊 Checking recent TTN ALL tours and periods...');
            
            $recentTours = \DB::table('tb_tour')
                ->where('api_type', $provider->code)
                ->orderBy('id', 'desc')
                ->limit($limit)
                ->get(['id', 'code1', 'name']);
            
            if ($recentTours->isEmpty()) {
                $this->warn('⚠ No tours found after sync');
            } else {
                $this->info("✓ Found {$recentTours->count()} recent tours:");
                foreach ($recentTours as $tour) {
                    $periods = \DB::table('tb_tour_period')->where('tour_id', $tour->id)->count();
                    $this->line("  - {$tour->code1}: " . substr($tour->name, 0, 100) . " ({$periods} periods)");
                }
            }
            
            $this->line('');
            $this->info('📝 Check logs for detailed information:');
            $this->line('Get-Content storage\logs\laravel.log | Select-Object -Last 20');
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error('✗ Sync failed: ' . $e->getMessage());
            $this->line('Stack trace:');
            $this->line($e->getTraceAsString());
            return 1;
        }
    }
}
