<?php
/**
 * Trigger iTravels Sync - ทดสอบ sync เดียว
 */

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\Backend\ApiManagementController;
use Illuminate\Support\Facades\DB;

echo "=== Trigger iTravels Sync ===\n\n";

// หา provider
$provider = DB::table('tb_api_providers')
    ->where('code', 'itravel')
    ->first();

if (!$provider) {
    echo "❌ Provider 'itravel' not found\n";
    exit;
}

echo "Provider: {$provider->code} (ID: {$provider->id})\n";
echo "Status: {$provider->status}\n\n";

// ดู tour ที่มีปัญหา
$tour = DB::table('tb_tour')
    ->where('api_type', 'itravel')
    ->where('code', 'KWL')  // ลองหา code KWL ก่อน
    ->first(['id', 'name', 'code', 'api_id']);

if (!$tour) {
    $tour = DB::table('tb_tour')
        ->where('api_type', 'itravel')
        ->first(['id', 'name', 'code', 'api_id']);
}

if ($tour) {
    echo "Tour: {$tour->name}\n";
    echo "  ID: {$tour->id}\n";
    echo "  code: {$tour->code}\n";
    echo "  api_id: {$tour->api_id}\n\n";
}

echo "To trigger sync, run:\n";
echo "php artisan api:sync --provider=itravel\n\n";

echo "Or through API endpoint:\n";
echo "POST /api/backend/api-management/sync-provider/{$provider->id}\n";
