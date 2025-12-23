<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Deleting GO365 tours ===\n";

$count = DB::table('tb_tour')->where('api_type', 'go365')->count();
echo "Found {$count} tours to delete\n";

DB::table('tb_tour')->where('api_type', 'go365')->delete();

echo "✓ Deleted!\n";
