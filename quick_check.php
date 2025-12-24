<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Get schema
$cols = DB::select("SHOW COLUMNS FROM tb_api_field_mappings");
echo "=== tb_api_field_mappings Columns ===\n";
foreach ($cols as $col) {
    echo "{$col->Field}\n";
}

echo "\n=== Sample Checkin Group Mapping ===\n";
$sample = DB::select("SELECT * FROM tb_api_field_mappings WHERE api_field = 'flight' LIMIT 1");
if (!empty($sample)) {
    print_r($sample[0]);
} else {
    echo "No flight mapping found\n";
}
