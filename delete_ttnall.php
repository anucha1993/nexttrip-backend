<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$deleted = DB::table('tb_tour')->whereIn('id', [45612, 45611, 45610])->delete();
echo "Deleted {$deleted} tours\n";
