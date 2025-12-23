<?php

require_once __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== All API Schedules ===\n\n";

$schedules = DB::table('tb_api_schedules as s')
    ->join('tb_api_providers as p', 's.api_provider_id', '=', 'p.id')
    ->select('s.*', 'p.name as provider_name', 'p.code as provider_code')
    ->orderBy('s.id')
    ->get();

foreach($schedules as $schedule) {
    echo "Schedule #{$schedule->id}:\n";
    echo "  Provider: {$schedule->provider_name} ({$schedule->provider_code})\n";
    echo "  Name: {$schedule->name}\n";
    echo "  Frequency: {$schedule->frequency}\n";
    echo "  Run Time: {$schedule->run_time}\n";
    echo "  Active: " . ($schedule->is_active ? 'Yes' : 'No') . "\n";
    echo "  Last Run: " . ($schedule->last_run_at ?: 'Never') . "\n";
    echo "\n";
}
