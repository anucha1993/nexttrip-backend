<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\TourApiProvider;

echo "=== Updating iTravel Config ===\n\n";

$provider = TourApiProvider::where('code', 'itravels')->first();

if (!$provider) {
    echo "❌ Provider not found!\n";
    exit(1);
}

$config = $provider->config ?? [];
$config['detail_url_pattern'] = '/api/program/{code}';

$provider->config = $config;
$provider->save();

echo "✓ Updated config:\n";
echo json_encode($config, JSON_PRETTY_PRINT) . "\n\n";

echo "Done!\n";
