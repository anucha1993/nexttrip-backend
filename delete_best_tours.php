<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

DB::table('tb_tour')->where('wholesale_id', 11)->where('data_type', 2)->delete();
DB::table('tb_period')->whereIn('tour_id', function($query) {
    $query->select('id')->from('tb_tour')->where('wholesale_id', 11)->where('data_type', 2);
})->delete();

echo "Deleted all Best Consortium tours and periods\n";
