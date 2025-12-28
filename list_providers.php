<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Available API Providers ===\n";

$providers = App\Models\Backend\ApiProviderModel::all(['id', 'name', 'code']);
foreach($providers as $p) {
    echo "ID: {$p->id}, Name: {$p->name}, Code: {$p->code}\n";
}
?>