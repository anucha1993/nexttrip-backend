<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Backend\TourModel;

echo "Direct query test:\n";
$tour = TourModel::where('code1', 'BT-MMR16_8M')->whereNull('deleted_at')->first();
if ($tour) {
    echo "FOUND: ID={$tour->id}, api_type={$tour->api_type}, code={$tour->code}\n";
} else {
    echo "NOT FOUND\n";
}

// Check with deleted
$tourWithDeleted = TourModel::withTrashed()->where('code1', 'BT-MMR16_8M')->first();
if ($tourWithDeleted) {
    echo "With deleted: ID={$tourWithDeleted->id}, deleted_at=" . ($tourWithDeleted->deleted_at ? $tourWithDeleted->deleted_at : 'NULL') . "\n";
}

// Check database connection
echo "\nDatabase: " . config('database.default') . "\n";
echo "Host: " . config('database.connections.mysql.host') . "\n";
echo "Database: " . config('database.connections.mysql.database') . "\n";
