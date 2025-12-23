<?php

require_once __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$deleted = DB::table('tb_tour')
    ->where('api_type', 'ttn_all')
    ->whereNull('deleted_at')
    ->delete();

echo "Deleted $deleted TTN ALL tours\n";

$deletedPeriods = DB::table('tb_tour_period')
    ->where('api_type', 'ttn_all')
    ->whereNull('deleted_at')
    ->delete();

echo "Deleted $deletedPeriods TTN ALL periods\n";
