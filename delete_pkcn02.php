<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Backend\TourModel;
use App\Models\Backend\TourPeriodModel;

$tour = TourModel::where('code1', 'PKCN02')->first();
if ($tour) {
    echo "Deleting tour ID: {$tour->id}\n";
    TourPeriodModel::where('tour_id', $tour->id)->forceDelete();
    $tour->forceDelete();
    echo "Deleted!\n";
} else {
    echo "Tour not found\n";
}
