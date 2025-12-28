    /**
     * Start manual sync asynchronously (non-blocking) - NEW METHOD
     */
    public function startManualSyncAsync(Request $request, $id)
    {
        $provider = ApiProviderModel::with(['fieldMappings', 'conditions'])->findOrFail($id);
        $limit = (int)$request->get('limit', 0);
        
        if ($provider->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'API Provider is not active!'
            ], 400);
        }

        try {
            // Create sync log entry immediately with 'queued' status
            $syncLog = \App\Models\Backend\SyncLogModel::create([
                'api_provider_id' => $provider->id,
                'sync_type' => 'manual_async',
                'status' => 'queued',
                'started_at' => now(),
                'total_records' => 0,
                'created_tours' => 0,
                'updated_tours' => 0,
                'duplicated_tours' => 0,
                'error_count' => 0,
                'summary' => json_encode([
                    'status' => 'queued', 
                    'message' => 'Sync queued for background processing'
                ])
            ]);
            
            // Run sync in background using Laravel's process
            $command = [
                'php', 'artisan', 'sync:provider', $provider->code,
                '--limit=' . $limit,
                '--log-id=' . $syncLog->id
            ];
            
            // For Windows, use different approach
            if (PHP_OS_FAMILY === 'Windows') {
                $cmdString = 'start /b ' . implode(' ', $command);
                exec($cmdString);
            } else {
                exec(implode(' ', $command) . ' > /dev/null 2>&1 &');
            }
            
            return response()->json([
                'success' => true,
                'sync_log_id' => $syncLog->id,
                'message' => "เริ่มการ sync {$provider->name} แล้ว กรุณาดู log สำหรับผลลัพธ์",
                'provider_name' => $provider->name,
                'status' => 'queued',
                'note' => 'การ sync กำลังทำงานในพื้นหลัง สามารถปิด popup นี้ได้'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to start async manual sync', [
                'provider' => $provider->name,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'ไม่สามารถเริ่ม sync ได้: ' . $e->getMessage()
            ], 500);
        }
    }