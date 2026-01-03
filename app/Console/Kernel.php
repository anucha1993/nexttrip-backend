<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Http\Controllers\Functions\ApiController;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Run API Management Scheduled Syncs
        $schedule->call(function () {
            $this->runScheduledApiSyncs();
        })->everyMinute(); // Check every minute for due schedules
        
        // Legacy: Old package_tour method (keep for backup)
        // $schedule->call(function () {
        //     $ApiController = app(ApiController::class);
        //     $ApiController->package_tour();
        // })->cron('30 9,12,15,18,21,0 * * *')->runInBackground();
    }
    
    /**
     * Run scheduled API syncs based on database schedules
     */
    protected function runScheduledApiSyncs()
    {
        $now = now();
        
        \Log::info("Checking scheduled API syncs", ['current_time' => $now->format('Y-m-d H:i:s')]);
        
        // Get all active schedules
        $schedules = \DB::table('tb_api_schedules')
            ->where('is_active', 1)
            ->get();
        
        \Log::info("Found {$schedules->count()} active schedules");
        
        foreach ($schedules as $schedule) {
            \Log::info("Checking schedule", [
                'schedule_id' => $schedule->id,
                'name' => $schedule->name,
                'next_run_at' => $schedule->next_run_at
            ]);
            
            // Check if schedule should run now
            if ($this->shouldRunSchedule($schedule, $now)) {
                \Log::info("Schedule should run NOW", ['schedule_id' => $schedule->id]);
                
                // Update last_run_at and last_status
                \DB::table('tb_api_schedules')
                    ->where('id', $schedule->id)
                    ->update([
                        'last_run_at' => $now,
                        'last_status' => 'running',
                        'updated_at' => $now
                    ]);
                
                try {
                    // Get provider using Model (not DB query) for proper relationship handling
                    $provider = \App\Models\Backend\ApiProviderModel::where('id', $schedule->api_provider_id)
                        ->first();
                    
                    if ($provider && $provider->status === 'active') {
                        // ถ้าใช้ QUEUE_CONNECTION=sync ให้รันตรงๆ (ไม่ผ่าน queue)
                        if (config('queue.default') === 'sync') {
                            \Log::info("Running sync directly (sync mode)", ['schedule_id' => $schedule->id]);
                            
                            // Run sync directly without queue (0 = no limit)
                            $syncLimit = $schedule->sync_limit ?? 0;
                            $controller = app(\App\Http\Controllers\Backend\ApiManagementController::class);
                            $result = $controller->performSync($provider, 'scheduled', $syncLimit);
                                                        // Reload schedule to get updated last_run_at
                            $schedule = \DB::table('tb_api_schedules')->where('id', $schedule->id)->first();
                                                        // Update success status
                            \DB::table('tb_api_schedules')
                                ->where('id', $schedule->id)
                                ->update([
                                    'last_status' => 'success',
                                    'last_error' => null,
                                    'next_run_at' => $this->calculateNextRun($schedule),
                                    'updated_at' => $now
                                ]);
                            
                            \Log::info("Scheduled sync completed", [
                                'schedule_id' => $schedule->id,
                                'provider' => $provider->name,
                                'sync_limit' => $syncLimit,
                                'result' => $result
                            ]);
                            
                            // Delete temporary schedule (is_temp = 1)
                            if (isset($schedule->is_temp) && $schedule->is_temp == 1) {
                                \DB::table('tb_api_schedules')->where('id', $schedule->id)->delete();
                                \Log::info("Temporary schedule deleted", ['schedule_id' => $schedule->id]);
                            }
                            
                            // Update script-filter.js after sync
                            try {
                                $homeController = app(\App\Http\Controllers\Frontend\HomeController::class);
                                $homeController->get_data(new \Illuminate\Http\Request());
                                \Log::info("Updated script-filter.js after sync");
                            } catch (\Exception $e) {
                                \Log::error("Failed to update script-filter.js: " . $e->getMessage());
                            }
                        } else {
                            // Use queue for async processing
                            \Log::info("Dispatching sync to queue", ['schedule_id' => $schedule->id]);
                            
                            // Create sync log
                            $syncLog = \App\Models\Backend\ApiSyncLogModel::create([
                                'api_provider_id' => $provider->id,
                                'sync_type' => 'scheduled',
                                'status' => 'running',
                                'started_at' => $now,
                            ]);
                            
                            // Dispatch job to queue
                            \App\Jobs\SyncApiProviderJob::dispatch(
                                $provider->id,
                                $syncLog->id,
                                'scheduled',
                                $syncLimit
                            );
                            
                            // Reload schedule to get updated last_run_at
                            $schedule = \DB::table('tb_api_schedules')->where('id', $schedule->id)->first();
                            
                            // Update schedule with next run time immediately
                            \DB::table('tb_api_schedules')
                                ->where('id', $schedule->id)
                                ->update([
                                    'last_status' => 'queued',
                                    'last_error' => null,
                                    'next_run_at' => $this->calculateNextRun($schedule),
                                    'updated_at' => $now
                                ]);
                            
                            \Log::info("Scheduled sync dispatched to queue", [
                                'schedule_id' => $schedule->id,
                                'provider' => $provider->name,
                                'sync_log_id' => $syncLog->id
                            ]);
                        }
                    }
                } catch (\Exception $e) {
                    // Update failed status
                    \DB::table('tb_api_schedules')
                        ->where('id', $schedule->id)
                        ->update([
                            'last_status' => 'failed',
                            'last_error' => $e->getMessage(),
                            'updated_at' => $now
                        ]);
                    
                    \Log::error("Scheduled sync failed", [
                        'schedule_id' => $schedule->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }
    }
    
    /**
     * Check if schedule should run now
     */
    protected function shouldRunSchedule($schedule, $now)
    {
        // ✅ ถ้ามี next_run_at ให้เช็คว่าถึงเวลาหรือยัง
        if ($schedule->next_run_at) {
            $nextRun = \Carbon\Carbon::parse($schedule->next_run_at);
            return $now->gte($nextRun);
        }
        
        // ถ้าไม่มี next_run_at แต่เคยรันแล้ว ให้คำนวณจาก last_run_at
        if ($schedule->last_run_at) {
            $lastRun = \Carbon\Carbon::parse($schedule->last_run_at);
            
            switch ($schedule->frequency) {
                case 'hourly':
                    $interval = $schedule->interval_minutes ?? 60;
                    return $now->diffInMinutes($lastRun) >= $interval;
                    
                case 'daily':
                    if (!$schedule->run_time) return false;
                    $runTime = \Carbon\Carbon::parse($schedule->run_time);
                    return $now->format('H:i') === $runTime->format('H:i') && 
                           $now->diffInMinutes($lastRun) >= 1440;
                    
                case 'weekly':
                    if (!$schedule->run_time || !$schedule->days_of_week) return false;
                    $daysOfWeek = json_decode($schedule->days_of_week, true) ?? [];
                    $runTime = \Carbon\Carbon::parse($schedule->run_time);
                    return in_array($now->dayOfWeek, $daysOfWeek) &&
                           $now->format('H:i') === $runTime->format('H:i') &&
                           $now->diffInMinutes($lastRun) >= 1440;
                    
                case 'monthly':
                    if (!$schedule->run_time || !$schedule->day_of_month) return false;
                    $runTime = \Carbon\Carbon::parse($schedule->run_time);
                    return $now->day === $schedule->day_of_month &&
                           $now->format('H:i') === $runTime->format('H:i') &&
                           $now->diffInMinutes($lastRun) >= 1440;
                    
                case 'custom':
                    if (!$schedule->cron_expression) return false;
                    return $now->diffInMinutes($lastRun) >= 1;
            }
        }
        
        // ❌ ถ้าไม่มีทั้ง next_run_at และ last_run_at = schedule มีปัญหา
        \Log::warning("Schedule #{$schedule->id} ({$schedule->name}) has no next_run_at or last_run_at");
        return false;
    }
    
    /**
     * Calculate next run time
     */
    protected function calculateNextRun($schedule)
    {
        $now = now();
        
        switch ($schedule->frequency) {
            case 'hourly':
                $interval = $schedule->interval_minutes ?? 60;
                // คำนวณจาก last_run_at หรือ now
                $baseTime = $schedule->last_run_at ? \Carbon\Carbon::parse($schedule->last_run_at) : $now;
                return $baseTime->addMinutes($interval);
                
            case 'daily':
                if (!$schedule->run_time) return null;
                
                // Parse เวลาที่ต้องการรัน (เช่น "14:27:00")
                $timeOnly = \Carbon\Carbon::parse($schedule->run_time)->format('H:i:s');
                
                // สร้าง next run จาก last_run_at หรือ วันนี้
                if ($schedule->last_run_at) {
                    $next = \Carbon\Carbon::parse($schedule->last_run_at)->addDay();
                } else {
                    $next = $now->copy();
                }
                
                // ตั้งเวลา
                $next->setTimeFromTimeString($timeOnly);
                
                // ถ้ายังไม่ถึงเวลาวันนี้ ให้ใช้วันนี้เลย
                if ($next->lt($now)) {
                    $next = $now->copy()->setTimeFromTimeString($timeOnly);
                    if ($next->lt($now)) {
                        $next->addDay();
                    }
                }
                
                return $next;
                
            case 'weekly':
                if (!$schedule->run_time || !$schedule->days_of_week) return null;
                $daysOfWeek = json_decode($schedule->days_of_week, true) ?? [];
                $runTime = \Carbon\Carbon::parse($schedule->run_time);
                
                // เริ่มจากวันถัดไปหลัง last_run_at หรือ วันนี้
                $startDate = $schedule->last_run_at ? \Carbon\Carbon::parse($schedule->last_run_at)->addDay() : $now->copy();
                
                // Find next day of week (max 7 days)
                for ($i = 0; $i < 7; $i++) {
                    $checkDate = $startDate->copy()->addDays($i);
                    if (in_array($checkDate->dayOfWeek, $daysOfWeek)) {
                        return $checkDate->setTimeFromTimeString($runTime->format('H:i:s'));
                    }
                }
                return null;
                
            case 'monthly':
                if (!$schedule->run_time || !$schedule->day_of_month) return null;
                
                // เริ่มจาก last_run_at หรือ เดือนนี้
                if ($schedule->last_run_at) {
                    $next = \Carbon\Carbon::parse($schedule->last_run_at)->addMonth();
                } else {
                    $next = $now->copy();
                }
                
                $next->day($schedule->day_of_month);
                $runTime = \Carbon\Carbon::parse($schedule->run_time);
                $next->setTimeFromTimeString($runTime->format('H:i:s'));
                
                // ถ้ายังไม่ถึงเวลาเดือนนี้ ให้ใช้เดือนนี้
                if ($next->lt($now)) {
                    $next = $now->copy()->day($schedule->day_of_month)->setTimeFromTimeString($runTime->format('H:i:s'));
                    if ($next->lt($now)) {
                        $next->addMonth();
                    }
                }
                
                return $next;
        }
        
        return null;
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
