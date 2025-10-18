<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use App\Models\Backend\ApiProviderModel;
use App\Models\Backend\ApiScheduleModel;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🚀 Setting up default schedules for all API providers...\n\n";

try {
    // Get all active API providers
    $providers = ApiProviderModel::where('status', 'active')->get();
    
    if ($providers->isEmpty()) {
        echo "❌ No active API providers found.\n";
        exit(1);
    }
    
    echo "📊 Found {$providers->count()} active API providers:\n";
    foreach ($providers as $provider) {
        echo "  - {$provider->name} ({$provider->code})\n";
    }
    echo "\n";

    $createdCount = 0;
    $skippedCount = 0;

    foreach ($providers as $provider) {
        echo "🔧 Processing: {$provider->name} ({$provider->code})\n";
        
        // Check if schedule already exists
        $existingSchedule = ApiScheduleModel::where('api_provider_id', $provider->id)->first();
        
        if ($existingSchedule) {
            echo "  ⏭️  Schedule already exists: {$existingSchedule->name}\n";
            $skippedCount++;
            continue;
        }

        // Create default schedule based on provider type
        $scheduleConfig = getDefaultScheduleConfig($provider);
        
        $schedule = ApiScheduleModel::create([
            'api_provider_id' => $provider->id,
            'name' => $scheduleConfig['name'],
            'frequency' => $scheduleConfig['frequency'],
            'run_time' => $scheduleConfig['run_time'],
            'interval_minutes' => $scheduleConfig['interval_minutes'],
            'days_of_week' => $scheduleConfig['days_of_week'],
            'day_of_month' => $scheduleConfig['day_of_month'],
            'cron_expression' => $scheduleConfig['cron_expression'],
            'sync_limit' => $scheduleConfig['sync_limit'],
            'is_active' => true
        ]);

        // Calculate next run time
        $schedule->updateNextRunTime();
        
        echo "  ✅ Created: {$schedule->name}\n";
        echo "     📅 Schedule: {$schedule->schedule_description}\n";
        echo "     ⏰ Next run: {$schedule->next_run_at->format('Y-m-d H:i:s')}\n";
        
        $createdCount++;
    }

    echo "\n";
    echo "🎉 Setup completed!\n";
    echo "✅ Created schedules: {$createdCount}\n";
    echo "⏭️  Skipped (existing): {$skippedCount}\n";
    echo "\n";
    echo "📋 Summary of created schedules:\n";
    
    // Show all schedules
    $allSchedules = ApiScheduleModel::with('apiProvider')->where('is_active', true)->get();
    foreach ($allSchedules as $schedule) {
        echo "  🔄 {$schedule->apiProvider->name}: {$schedule->name}\n";
        echo "     ⏰ {$schedule->schedule_description}\n";
        echo "     🕐 Next: {$schedule->next_run_at->format('d/m/Y H:i')}\n";
        echo "\n";
    }
    
    echo "🚀 To start automatic syncing, run:\n";
    echo "   php artisan api:sync-scheduled\n";
    echo "\n";
    echo "📖 For detailed instructions, see SCHEDULER_GUIDE.md\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📍 Line: " . $e->getLine() . "\n";
    echo "📁 File: " . $e->getFile() . "\n";
    exit(1);
}

/**
 * Get default schedule configuration for each provider type
 */
function getDefaultScheduleConfig($provider) {
    $providerCode = strtolower($provider->code);
    
    // ตั้งค่าเริ่มต้นตามประเภท API
    switch ($providerCode) {
        case 'itravel':
            return [
                'name' => 'อัปเดตรายวัน iTravel',
                'frequency' => 'daily',
                'run_time' => '08:00:00',
                'interval_minutes' => null,
                'days_of_week' => null,
                'day_of_month' => null,
                'cron_expression' => null,
                'sync_limit' => null // ไม่จำกัด
            ];

        case 'go365':
            return [
                'name' => 'ซิงค์รายวัน GO365',
                'frequency' => 'daily',
                'run_time' => '09:30:00',
                'interval_minutes' => null,
                'days_of_week' => null,
                'day_of_month' => null,
                'cron_expression' => null,
                'sync_limit' => 100 // จำกัด 100 tours ต่อครั้ง
            ];

        case 'ttnjapan':
        case 'ttn_japan':
            return [
                'name' => 'อัปเดตรายวัน TTN Japan',
                'frequency' => 'daily',
                'run_time' => '10:00:00',
                'interval_minutes' => null,
                'days_of_week' => null,
                'day_of_month' => null,
                'cron_expression' => null,
                'sync_limit' => 50 // จำกัด 50 tours เนื่องจาก multi-step API
            ];

        case 'zego':
            return [
                'name' => 'ซิงค์รายวัน Zego',
                'frequency' => 'daily',
                'run_time' => '11:00:00',
                'interval_minutes' => null,
                'days_of_week' => null,
                'day_of_month' => null,
                'cron_expression' => null,
                'sync_limit' => null
            ];

        case 'bestconsortium':
        case 'best_consortium':
            return [
                'name' => 'อัปเดตรายวัน Best Consortium',
                'frequency' => 'daily',
                'run_time' => '12:00:00',
                'interval_minutes' => null,
                'days_of_week' => null,
                'day_of_month' => null,
                'cron_expression' => null,
                'sync_limit' => null
            ];

        case 'package':
            return [
                'name' => 'ซิงค์รายวัน Package API',
                'frequency' => 'daily',
                'run_time' => '13:00:00',
                'interval_minutes' => null,
                'days_of_week' => null,
                'day_of_month' => null,
                'cron_expression' => null,
                'sync_limit' => null
            ];

        default:
            // ค่าเริ่มต้นสำหรับ API อื่นๆ
            return [
                'name' => "ซิงค์รายวัน {$provider->name}",
                'frequency' => 'daily',
                'run_time' => '14:00:00',
                'interval_minutes' => null,
                'days_of_week' => null,
                'day_of_month' => null,
                'cron_expression' => null,
                'sync_limit' => null
            ];
    }
}