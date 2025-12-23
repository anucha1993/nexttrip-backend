<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$providers = DB::table('tb_api_providers')
    ->where('name', 'like', '%TTN%')
    ->get(['id', 'name', 'code', 'status']);

echo "=== TTN Providers ===\n";
foreach ($providers as $p) {
    echo "{$p->id}: {$p->name} ({$p->code}) - {$p->status}\n";
}
