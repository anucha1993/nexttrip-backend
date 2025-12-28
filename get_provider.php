<?php
require_once __DIR__."/vendor/autoload.php";
$app = require_once __DIR__."/bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$provider = App\Models\Backend\ApiProviderModel::where('code', 'like', '%super%')->first();
echo "Provider ID: {$provider->id}\n";
echo "Code: {$provider->code}\n";  
echo "Name: {$provider->name}\n";
echo "Status: {$provider->status}\n";