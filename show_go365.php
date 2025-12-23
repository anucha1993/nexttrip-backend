<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$provider = DB::table('tb_api_providers')->where('code', 'go365')->first();
echo json_encode($provider, JSON_PRETTY_PRINT);
