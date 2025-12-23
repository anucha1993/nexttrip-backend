<?php

require_once __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$tables = DB::select('SHOW TABLES');

echo "=== Tables with 'field' or 'mapping' ===\n\n";

foreach($tables as $t) {
    $table = array_values((array)$t)[0];
    if(stripos($table, 'field') !== false || stripos($table, 'mapping') !== false) {
        echo "  - $table\n";
    }
}
