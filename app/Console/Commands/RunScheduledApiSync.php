<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Backend\ApiScheduleModel;
use App\Http\Controllers\Backend\ApiManagementController;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class RunScheduledApiSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:sync-scheduled {--schedule-id= : Run specific schedule only}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run scheduled API synchronizations that are due to run';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('🚀 Starting scheduled API sync check...');
        
        try {
            $query = ApiScheduleModel::readyToRun()->with('apiProvider');
            
            // หากระบุ schedule-id เฉพาะ ให้ force run โดยไม่เช็คเวลา
            if ($scheduleId = $this->option('schedule-id')) {
                $query = ApiScheduleModel::where('id', $scheduleId)->where('is_active', true)->with('apiProvider');
                $this->info("🎯 Force running specific schedule ID: {$scheduleId}");
            }
            
            $schedules = $query->get();
            
            if ($schedules->isEmpty()) {
                $this->info('📋 No scheduled syncs are due to run at this time.');
                return 0;
            }

            $this->info("📊 Found {$schedules->count()} schedule(s) ready to run:");

            $controller = new ApiManagementController();
            $successCount = 0;
            $failureCount = 0;

            foreach ($schedules as $schedule) {
                $this->info("⏰ Running: {$schedule->name} (Provider: {$schedule->apiProvider->name})");
                
                try {
                    $result = $controller->runScheduledSync($schedule->id);
                    
                    if ($result['success']) {
                        $this->info("✅ Success: {$schedule->name}");
                        $successCount++;
                        
                        // แสดงข้อมูลสรุป
                        if (isset($result['summary'])) {
                            $this->line("   📈 {$result['summary']}");
                        }
                    } else {
                        $this->error("❌ Failed: {$schedule->name} - {$result['message']}");
                        $failureCount++;
                    }
                } catch (\Exception $e) {
                    $this->error("💥 Error running {$schedule->name}: " . $e->getMessage());
                    $failureCount++;
                }
                
                $this->line(''); // Empty line for readability
            }

            // สรุปผลการรัน
            $this->info('📊 Summary:');
            $this->info("✅ Successful: {$successCount}");
            if ($failureCount > 0) {
                $this->error("❌ Failed: {$failureCount}");
            }
            
            $this->info('🏁 Scheduled sync check completed.');
            
            return $failureCount > 0 ? 1 : 0;

        } catch (\Exception $e) {
            $this->error('💥 Fatal error in scheduled sync: ' . $e->getMessage());
            Log::error('Fatal error in RunScheduledApiSync command: ' . $e->getMessage());
            return 1;
        }
    }
}
