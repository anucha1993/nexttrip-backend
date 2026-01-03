<?php

namespace App\Jobs;

use App\Models\Backend\ApiProviderModel;
use App\Models\Backend\ApiSyncLogModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncApiProviderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600; // 1 hour
    public $tries = 1;

    protected $providerId;
    protected $syncType;
    protected $syncLogId;

    /**
     * Create a new job instance.
     */
    public function __construct($providerId, $syncType = 'manual', $syncLogId = null)
    {
        $this->providerId = $providerId;
        $this->syncType = $syncType;
        $this->syncLogId = $syncLogId;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            $provider = ApiProviderModel::with(['fieldMappings', 'conditions'])->findOrFail($this->providerId);
            
            Log::info('SyncApiProviderJob started', [
                'provider_id' => $this->providerId,
                'provider_name' => $provider->name,
                'sync_type' => $this->syncType,
                'sync_log_id' => $this->syncLogId
            ]);

            // Get or update sync log
            if ($this->syncLogId) {
                $syncLog = ApiSyncLogModel::find($this->syncLogId);
                if ($syncLog) {
                    $syncLog->update(['status' => 'running', 'started_at' => now()]);
                }
            }

            // Perform sync using existing controller method
            $controller = app(\App\Http\Controllers\Backend\ApiManagementController::class);
            $result = $controller->performSync($provider, $this->syncType, 0);

            Log::info('SyncApiProviderJob completed', [
                'provider_id' => $this->providerId,
                'result' => $result
            ]);

        } catch (\Exception $e) {
            Log::error('SyncApiProviderJob failed', [
                'provider_id' => $this->providerId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Update sync log to failed
            if ($this->syncLogId) {
                $syncLog = ApiSyncLogModel::find($this->syncLogId);
                if ($syncLog) {
                    $syncLog->update([
                        'status' => 'failed',
                        'completed_at' => now(),
                        'error_message' => $e->getMessage()
                    ]);
                }
            }

            throw $e;
        }
    }
}
