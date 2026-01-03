<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$now = \Carbon\Carbon::now();

// Update Zego schedule to run now
DB::table('tb_api_schedules')
    ->where('id', 88)
    ->update([
        'next_run_at' => $now->format('Y-m-d H:i:s'),
        'updated_at' => $now
    ]);

echo "Updated schedule #88 next_run_at to NOW: " . $now->format('Y-m-d H:i:s') . "\n";
echo "Run: php artisan schedule:run\n";
