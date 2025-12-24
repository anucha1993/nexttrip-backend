<?php
require __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$provider = DB::table('tb_api_providers')->where('code', 'best')->first();

echo "Best Consortium Provider Config:\n";
echo "URL: " . $provider->url . "\n";
echo "Headers: " . $provider->headers . "\n";
