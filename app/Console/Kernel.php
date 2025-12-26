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
        
        // Get all active schedules
        $schedules = \DB::table('tb_api_schedules')
            ->where('is_active', 1)
            ->get();
        
        foreach ($schedules as $schedule) {
            // Check if schedule should run now
            if ($this->shouldRunSchedule($schedule, $now)) {
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
                        // Run sync
                        $controller = app(\App\Http\Controllers\Backend\ApiManagementController::class);
                        $result = $controller->performSync($provider, 'scheduled', $schedule->sync_limit);
                        
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
                            'result' => $result
                        ]);
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
        // If never run, should run if past scheduled time
        if (!$schedule->last_run_at) {
            return true;
        }
        
        $lastRun = \Carbon\Carbon::parse($schedule->last_run_at);
        
        switch ($schedule->frequency) {
            case 'hourly':
                $interval = $schedule->interval_minutes ?? 60;
                return $now->diffInMinutes($lastRun) >= $interval;
                
            case 'daily':
                if (!$schedule->run_time) return false;
                $runTime = \Carbon\Carbon::parse($schedule->run_time);
                return $now->format('H:i') === $runTime->format('H:i') && 
                       $now->diffInMinutes($lastRun) >= 1440; // Once per day
                
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
                // For cron, check if at least 1 minute passed
                return $now->diffInMinutes($lastRun) >= 1;
        }
        
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
                return $now->addMinutes($interval);
                
            case 'daily':
                if (!$schedule->run_time) return null;
                $next = \Carbon\Carbon::parse($schedule->run_time);
                if ($next->isPast()) {
                    $next->addDay();
                }
                return $next;
                
            case 'weekly':
                if (!$schedule->run_time || !$schedule->days_of_week) return null;
                $daysOfWeek = json_decode($schedule->days_of_week, true) ?? [];
                $runTime = \Carbon\Carbon::parse($schedule->run_time);
                
                // Find next day of week
                foreach (range(1, 7) as $daysAhead) {
                    $next = $now->copy()->addDays($daysAhead);
                    if (in_array($next->dayOfWeek, $daysOfWeek)) {
                        return $next->setTimeFromTimeString($runTime->format('H:i:s'));
                    }
                }
                return null;
                
            case 'monthly':
                if (!$schedule->run_time || !$schedule->day_of_month) return null;
                $next = $now->copy()->day($schedule->day_of_month);
                if ($next->isPast()) {
                    $next->addMonth();
                }
                $runTime = \Carbon\Carbon::parse($schedule->run_time);
                return $next->setTimeFromTimeString($runTime->format('H:i:s'));
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
