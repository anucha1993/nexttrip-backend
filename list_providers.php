<?php
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== ALL API PROVIDERS ===" . PHP_EOL;
$providers = App\Models\Backend\ApiProviderModel::all();

foreach ($providers as $provider) {
    echo sprintf("ID: %d - Code: %s - Name: %s" . PHP_EOL,
        $provider->id,
        $provider->code,
        $provider->name
    );
}

echo PHP_EOL . "Total Providers: " . $providers->count() . PHP_EOL;