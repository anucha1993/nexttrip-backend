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
        // $schedule->command('db:backup')->dailyAt('00:05')->runInBackground();  // ทำงานอยู่เบื้องหลัง
        $schedule->call(function () {
            $ApiController = app(ApiController::class);
            $ApiController->package_tour();
        })->cron('30 9,12,15,18,21,0 * * *')->runInBackground();

        // $schedule->call(function () {
        //     $ApiController = app(ApiController::class);
        //     $ApiController->package_tour();
        // })->dailyAt('10:00');
        
        // $schedule->command('db:backup')->everyMinute();
        // $schedule->command('db:backup')->everyThreeMinutes()->runInBackground();
        // $schedule->command('inspire')->hourly();
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
