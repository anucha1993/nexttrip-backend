<?php

require_once __DIR__ . '/vendor/autoload.php';

// Initialize Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Backend\ApiProviderModel;
use App\Http\Controllers\Backend\ApiManagementController;

echo "=== GO365 Full Sync Test ===\n\n";

try {
    // Get GO365 provider
    $provider = ApiProviderModel::where('code', 'go365')->first();
    
    if (!$provider) {
        echo "❌ GO365 provider not found\n";
        exit;
    }
    
    echo "✅ GO365 provider found: {$provider->name}\n";
    echo "   Status: {$provider->status}\n\n";
    
    // Create controller instance
    $controller = new ApiManagementController();
    
    // Create a sync log
    $syncLog = new \App\Models\Backend\ApiSyncLogModel();
    $syncLog->api_provider_id = $provider->id;
    $syncLog->sync_type = 'manual';
    $syncLog->status = 'running';
    $syncLog->total_records = 0;
    $syncLog->created_tours = 0;
    $syncLog->updated_tours = 0;
    $syncLog->duplicated_tours = 0;
    $syncLog->error_count = 0;
    $syncLog->started_at = now();
    $syncLog->save();
    
    echo "🔄 Starting GO365 sync with limit of 3 tours...\n";
    echo "   Sync log ID: {$syncLog->id}\n\n";
    
    try {
        // Use reflection to call the performSync method
        $reflection = new ReflectionClass($controller);
        $syncMethod = $reflection->getMethod('performSync');
        $syncMethod->setAccessible(true);
        
        // Call with limit
        $result = $syncMethod->invoke($controller, $provider, 'manual', 3); // Limit to 3 tours
        
        echo "🎯 Sync completed!\n";
        echo "   Result: " . json_encode($result, JSON_PRETTY_PRINT) . "\n\n";
        
        // Check sync log
        $syncLog->refresh();
        echo "📊 Final Statistics:\n";
        echo "   Status: {$syncLog->status}\n";
        echo "   Total Records: {$syncLog->total_records}\n";
        echo "   Created Tours: {$syncLog->created_tours}\n";
        echo "   Updated Tours: {$syncLog->updated_tours}\n";
        echo "   Duplicated Tours: {$syncLog->duplicated_tours}\n";
        echo "   Error Count: {$syncLog->error_count}\n";
        
        if ($syncLog->error_message) {
            echo "   Error Message: {$syncLog->error_message}\n";
        }
        
        // Check period counts
        $tourIds = \App\Models\Backend\TourModel::where('api_type', 'go365')
                                               ->where('created_at', '>=', $syncLog->started_at)
                                               ->pluck('id');
        
        if ($tourIds->count() > 0) {
            $periodCounts = \App\Models\Backend\TourPeriodModel::whereIn('tour_id', $tourIds)
                                                             ->selectRaw('tour_id, COUNT(*) as period_count')
                                                             ->groupBy('tour_id')
                                                             ->get();
            
            echo "\n🗓️ Period Statistics:\n";
            foreach ($periodCounts as $count) {
                echo "   Tour ID {$count->tour_id}: {$count->period_count} periods\n";
            }
        }
        
    } catch (Exception $e) {
        echo "❌ Sync error: " . $e->getMessage() . "\n";
        echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
        
        // Update sync log with error
        $syncLog->status = 'error';
        $syncLog->error_count = 1;
        $syncLog->error_message = $e->getMessage();
        $syncLog->completed_at = now();
        $syncLog->save();
    }
    
} catch (Exception $e) {
    echo "❌ Setup error: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}