<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Backend\TourModel;

$tours = TourModel::where('api_type', 'checkingroup')
    ->orderBy('id', 'desc')
    ->limit(3)
    ->get();

echo "Recent Checkin Group tours:\n";
foreach ($tours as $t) {
    echo "ID: {$t->id}, Code: {$t->code1}, PDF: " . ($t->pdf_file ?: 'null') . "\n";
}
