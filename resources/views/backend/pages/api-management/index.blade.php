<!DOCTYPE html>
<html lang="en" class="light">
<!-- BEGIN: Head -->
<head>
    <!-- BEGIN: CSS Assets-->
    @include("backend.layout.css")
    <!-- END: CSS Assets-->
    
    <style>
        .status-badge { font-size: 12px; padding: 4px 8px; }
        .status-active { background-color: #10b981; color: white; }
        .status-inactive { background-color: #ef4444; color: white; }
        .api-card { transition: transform 0.2s; }
        .api-card:hover { transform: translateY(-2px); }
        .sync-stats { font-size: 12px; color: #6b7280; }
    </style>
</head>

<body class="py-5">
    @include("backend.layout.mobile-menu")
    <div class="flex">
        @include("backend.layout.side-menu")
        
        <div class="content">
            @include("backend.layout.topbar")
            
            <div class="intro-y flex items-center mt-8">
                <h2 class="text-lg font-medium mr-auto">API Management</h2>
                <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
                    <a href="{{ route('api-management.create') }}" class="btn btn-primary shadow-md mr-2">เพิ่ม API Provider</a>
                </div>
            </div>

            <!-- Success/Error Messages -->
            @if(session('success'))
                <div class="alert alert-success show mb-2 mt-4" role="alert">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger show mb-2 mt-4" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            <!-- API Providers Grid -->
            <div class="grid grid-cols-12 gap-6 mt-5">
                @forelse($providers as $provider)
                    <div class="intro-y col-span-12 md:col-span-6 lg:col-span-4">
                        <div class="box api-card">
                            <div class="p-5">
                                <div class="flex items-center">
                                    <div class="ml-auto">
                                        <span class="status-badge rounded-full {{ $provider->status === 'active' ? 'status-active' : 'status-inactive' }}">
                                            {{ ucfirst($provider->status) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="text-center lg:text-left mt-3">
                                    <a href="{{ route('api-management.show', $provider->id) }}" class="font-medium">{{ $provider->name }}</a>
                                    <div class="text-slate-500 text-xs mt-0.5">{{ $provider->code }}</div>
                                    <div class="text-slate-500 text-xs mt-2">{{ $provider->description }}</div>
                                </div>
                                
                                <!-- Last Sync & Schedule Info -->
                                <div class="mt-3 pt-3 border-t border-slate-200 space-y-2">
                                    <!-- Last Sync Info -->
                                    @if($provider->syncLogs->count() > 0)
                                        @php $lastSync = $provider->syncLogs->first(); @endphp
                                        <div class="text-xs">
                                            <div class="flex items-center justify-between mb-1">
                                                <span class="text-slate-600 font-medium">Last Sync:</span>
                                                <span class="text-slate-500">{{ $lastSync->started_at->format('d/m/Y H:i') }} ({{ $lastSync->started_at->diffForHumans() }})</span>
                                            </div>
                                            <div class="flex items-center justify-between">
                                                <span class="text-slate-600">Type:</span>
                                                <span class="px-2 py-1 rounded text-xs {{ $lastSync->sync_type === 'manual' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700' }}">
                                                    {{ $lastSync->sync_type === 'manual' ? '👤 Manual' : '⏰ Scheduled' }}
                                                </span>
                                            </div>
                                            <div class="flex items-center justify-between mt-1">
                                                <span class="text-slate-600">Status:</span>
                                                <span class="px-2 py-1 rounded text-xs 
                                                    @if($lastSync->status === 'completed') bg-green-100 text-green-700
                                                    @elseif($lastSync->status === 'failed') bg-red-100 text-red-700
                                                    @elseif($lastSync->status === 'running') bg-blue-100 text-blue-700
                                                    @elseif($lastSync->status === 'queued') bg-yellow-100 text-yellow-700
                                                    @else bg-gray-100 text-gray-700
                                                    @endif" data-status="{{ $lastSync->status }}">
                                                    @if($lastSync->status === 'completed') ✅ Success
                                                    @elseif($lastSync->status === 'failed') ❌ Failed
                                                    @elseif($lastSync->status === 'running') 🔄 Running
                                                    @elseif($lastSync->status === 'queued') ⏳ Queued
                                                    @else 📋 {{ ucfirst($lastSync->status) }}
                                                    @endif
                                                </span>
                                            </div>
                                            <div class="grid grid-cols-4 gap-2 mt-2 text-xs">
                                                <div class="text-center p-1 bg-green-50 rounded">
                                                    <div class="font-medium text-green-700">{{ $lastSync->created_tours ?? 0 }}</div>
                                                    <div class="text-green-600">Created</div>
                                                </div>
                                                <div class="text-center p-1 bg-blue-50 rounded">
                                                    <div class="font-medium text-blue-700">{{ $lastSync->updated_tours ?? 0 }}</div>
                                                    <div class="text-blue-600">Updated</div>
                                                </div>
                                                <div class="text-center p-1 bg-yellow-50 rounded">
                                                    <div class="font-medium text-yellow-700">{{ $lastSync->duplicated_tours ?? 0 }}</div>
                                                    <div class="text-yellow-600">Duplicated</div>
                                                </div>
                                                <div class="text-center p-1 bg-red-50 rounded">
                                                    <div class="font-medium text-red-700">{{ $lastSync->error_count ?? 0 }}</div>
                                                    <div class="text-red-600">Errors</div>
                                                </div>
                                            </div>
                                            @if($lastSync->error_message)
                                                <div class="mt-2 p-2 bg-red-50 border-l-2 border-red-200 rounded text-xs">
                                                    <div class="font-medium text-red-700">Last Error:</div>
                                                    <div class="text-red-600">{{ Str::limit($lastSync->error_message, 100) }}</div>
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <div class="text-xs text-slate-400 text-center py-2">
                                            <i data-lucide="clock" class="w-4 h-4 mx-auto mb-1"></i>
                                            <div>No sync history yet</div>
                                        </div>
                                    @endif

                                    <!-- Schedule Status -->
                                    @if($provider->schedules->where('is_active', true)->count() > 0)
                                        @php $activeSchedule = $provider->schedules->where('is_active', true)->first(); @endphp
                                        <div class="text-xs border-t border-slate-100 pt-2">
                                            <div class="flex items-center justify-between mb-1">
                                                <span class="text-slate-600 font-medium">Next Schedule:</span>
                                                <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded text-xs">
                                                    ⏰ Active
                                                </span>
                                            </div>
                                            <div class="flex items-center justify-between">
                                                <span class="text-slate-600">{{ $activeSchedule->name }}</span>
                                                @if($activeSchedule->next_run_at)
                                                    <span class="text-slate-500">{{ $activeSchedule->next_run_at->format('d/m/Y H:i') }}</span>
                                                @else
                                                    <span class="text-red-500">Not scheduled</span>
                                                @endif
                                            </div>
                                            <div class="text-slate-500 text-xs">{{ $activeSchedule->schedule_description }}</div>
                                        </div>
                                    @else

                                        <div class="text-xs text-slate-400 text-center py-2 border-t border-slate-100">
                                            <i data-lucide="calendar-x" class="w-4 h-4 mx-auto mb-1"></i>
                                            <div>No active schedules</div>
                                        </div>
                                        
                                    @endif
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex items-center mt-4 pt-4 border-t border-slate-200">
                                    <button onclick="testConnection({{ $provider->id }})" class="btn btn-outline-secondary btn-sm mr-1" title="Test Connection">
                                        <i data-lucide="wifi" class="w-4 h-4"></i>
                                    </button>
                                    <button onclick="syncManual({{ $provider->id }})" class="btn btn-outline-primary btn-sm mr-1" title="Manual Sync" {{ $provider->status !== 'active' ? 'disabled' : '' }}>
                                        <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                                    </button>
                                    @if($provider->schedules->where('is_active', true)->count() > 0)
                                        @php $activeSchedule = $provider->schedules->where('is_active', true)->first(); @endphp
                                        <button onclick="testScheduledSync({{ $provider->id }}, {{ $activeSchedule->id }})" class="btn btn-outline-success btn-sm mr-1" title="Test Schedule" {{ $provider->status !== 'active' ? 'disabled' : '' }}>
                                            <i data-lucide="clock" class="w-4 h-4"></i>
                                        </button>
                                    @endif
                                    <a href="{{ route('api-management.logs', $provider->id) }}" class="btn btn-outline-warning btn-sm mr-1" title="View Logs">
                                        <i data-lucide="file-text" class="w-4 h-4"></i>
                                    </a>
                                    {{-- <a href="{{ route('api-management.duplicates', $provider->id) }}" class="btn btn-outline-danger btn-sm mr-1" title="View Duplicates">
                                        <i data-lucide="copy" class="w-4 h-4"></i>
                                        @if($provider->duplicates()->where('status', 'pending')->count() > 0)
                                            <span class="ml-1 bg-red-500 text-white rounded-full px-1 text-xs">{{ $provider->duplicates()->where('status', 'pending')->count() }}</span>
                                        @endif
                                    </a> --}}
                                    <div class="dropdown ml-auto">
                                        <button class="dropdown-toggle btn btn-outline-secondary btn-sm" aria-expanded="false" data-tw-toggle="dropdown">
                                            <i data-lucide="more-horizontal" class="w-4 h-4"></i>
                                        </button>
                                        <div class="dropdown-menu">
                                            <div class="dropdown-content">
                                                <a href="{{ route('api-management.show', $provider->id) }}" class="dropdown-item">
                                                    <i data-lucide="eye" class="w-4 h-4 mr-2"></i> View
                                                </a>
                                                <a href="{{ route('api-management.edit', $provider->id) }}" class="dropdown-item">
                                                    <i data-lucide="edit-2" class="w-4 h-4 mr-2"></i> Edit
                                                </a>
                                                <button onclick="toggleStatus({{ $provider->id }})" class="dropdown-item w-full text-left">
                                                    <i data-lucide="{{ $provider->status === 'active' ? 'pause' : 'play' }}" class="w-4 h-4 mr-2"></i> 
                                                    {{ $provider->status === 'active' ? 'Deactivate' : 'Activate' }}
                                                </button>

                                                <div class="dropdown-divider"></div>
                                                <button onclick="deleteProvider({{ $provider->id }})" class="dropdown-item text-danger w-full text-left">
                                                    <i data-lucide="trash-2" class="w-4 h-4 mr-2"></i> Delete
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-12">
                        <div class="box">
                            <div class="p-5 text-center">
                                <i data-lucide="database" class="w-16 h-16 text-slate-300 mx-auto mb-4"></i>
                                <h3 class="font-medium text-slate-500">No API Providers</h3>
                                <p class="text-slate-400 mt-2">เริ่มต้นโดยการเพิ่ม API Provider แรกของคุณ</p>
                                <a href="{{ route('api-management.create') }}" class="btn btn-primary mt-4">เพิ่ม API Provider</a>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Loading Modal -->
    <div id="loading-modal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-body p-5 text-center">
                    <i data-lucide="loader" class="w-8 h-8 text-primary mx-auto animate-spin mb-3"></i>
                    <div class="text-base font-medium">Processing...</div>
                    <div class="text-slate-500 mt-2">Please wait while we process your request.</div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript for API Management -->
    
    <script>
        function testConnection(providerId) {
            const modal = tailwind.Modal.getOrCreateInstance(document.querySelector("#loading-modal"));
            modal.show();
            
            fetch(`/webpanel/api-management/${providerId}/test-connection`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                modal.hide();
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Connection Successful!',
                        html: `
                            <div class="text-left space-y-2">
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                                    <h4 class="font-semibold text-blue-800 mb-2">📊 API Data Summary</h4>
                                    <div class="grid grid-cols-2 gap-4 text-sm">
                                        <div>
                                            <span class="font-medium text-blue-700">🗂️ Total Records:</span>
                                            <span class="text-blue-900 font-bold">${data.record_count || 0}</span>
                                        </div>
                                        <div>
                                            <span class="font-medium text-blue-700">📅 Total Periods:</span>
                                            <span class="text-blue-900 font-bold">${data.period_count || 0}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                                    <h4 class="font-semibold text-gray-800 mb-2">🔧 Technical Info</h4>
                                    <div class="text-sm space-y-1">
                                        <p><span class="font-medium text-gray-700">⚡ Response Time:</span> ${data.response_time}s</p>
                                        <p><span class="font-medium text-gray-700">📦 Response Size:</span> ${(data.response_size / 1024).toFixed(2)} KB</p>
                                        <p><span class="font-medium text-gray-700">📋 Data Available:</span> ${data.data ? '✅ Yes' : '❌ No'}</p>
                                    </div>
                                </div>
                            </div>
                        `,
                        confirmButtonText: 'OK',
                        width: '600px'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Connection Failed!',
                        text: data.message,
                        confirmButtonText: 'OK'
                    });
                }
            })
            .catch(error => {
                modal.hide();
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'An error occurred while testing the connection.',
                    confirmButtonText: 'OK'
                });
            });
        }

        function syncManual(providerId) {
            Swal.fire({
                title: 'Confirm Manual Sync',
                text: 'Are you sure you want to start manual sync for this API?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Start Sync!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show brief loading
                    Swal.fire({
                        title: 'Starting Sync...',
                        text: 'Please wait',
                        icon: 'info',
                        timer: 1500,
                        showConfirmButton: false
                    });
                    
                    fetch(`/webpanel/api-management/${providerId}/sync-manual-async`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Show completion result based on status
                            if (data.status === 'completed') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Manual Sync Completed!',
                                    html: `
                                        <div class="text-left">
                                            <p><strong>API:</strong> ${data.provider_name}</p>
                                            <p><strong>Status:</strong> ✅ Completed Successfully</p>
                                        </div>
                                    `,
                                    confirmButtonText: 'View Details',
                                    timer: 4000
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                // For other statuses (queued, running)
                                Swal.fire({
                                    icon: 'info',
                                    title: 'Sync Started!',
                                    html: `
                                        <div class="text-left">
                                            <p><strong>API:</strong> ${data.provider_name}</p>
                                            <p><strong>Status:</strong> Processing</p>
                                        </div>
                                    `,
                                    confirmButtonText: 'OK',
                                    timer: 3000
                                }).then(() => {
                                    location.reload();
                                });
                            }
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Failed to Start Sync',
                                text: data.message,
                                confirmButtonText: 'OK'
                            });
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'An error occurred while starting sync.',
                            confirmButtonText: 'OK'
                        });
                    });
                }
            });
        }

        // Enhanced monitor sync progress function
        function monitorSyncProgress(syncLogId, providerId) {
            let checkCount = 0;
            const maxChecks = 200; // Maximum 200 checks (10 minutes at 3-second intervals)
            
            const checkInterval = setInterval(() => {
                checkCount++;
                
                fetch(`/webpanel/api-management/sync-status/${syncLogId}`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log(`Sync ${syncLogId} status:`, data.status, `(check ${checkCount})`);
                        
                        // Check if sync is stale (stuck)
                        if (data.is_stale) {
                            clearInterval(checkInterval);
                            Swal.fire({
                                icon: 'warning',
                                title: 'Sync May Be Stuck',
                                html: `
                                    <div class="text-left">
                                        <p>Sync has been running for over 10 minutes.</p>
                                        <p><strong>Status:</strong> ${data.status}</p>
                                        <p><strong>Duration:</strong> ${Math.floor(data.duration / 60)} minutes</p>
                                        <p class="mt-2 text-sm text-orange-600">Please check the logs or contact administrator.</p>
                                    </div>
                                `,
                                confirmButtonText: 'Check Logs',
                                showCancelButton: true,
                                cancelButtonText: 'Close'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.open(`/webpanel/api-management/${providerId}/logs`, '_blank');
                                }
                                location.reload();
                            });
                            return;
                        }
                        
                        // Check if sync is completed or failed
                        if (data.status === 'completed' || data.status === 'failed') {
                            clearInterval(checkInterval);
                            
                            // Show completion notification
                            if (data.status === 'completed') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Sync Completed Successfully!',
                                    html: `
                                        <div class="text-left">
                                            <p><strong>Provider:</strong> ${data.provider_name}</p>
                                            <p><strong>Duration:</strong> ${data.duration ? Math.floor(data.duration / 60) + 'm ' + (data.duration % 60) + 's' : 'N/A'}</p>
                                            <hr class="my-2">
                                            <p><strong>📈 Created:</strong> ${data.created_tours}</p>
                                            <p><strong>🔄 Updated:</strong> ${data.updated_tours}</p>
                                            <p><strong>📋 Duplicated:</strong> ${data.duplicated_tours}</p>
                                            <p><strong>❌ Errors:</strong> ${data.error_count}</p>
                                        </div>
                                    `,
                                    timer: 5000,
                                    showConfirmButton: true,
                                    confirmButtonText: 'View Details'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        window.open(`/webpanel/api-management/${providerId}/logs`, '_blank');
                                    }
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Sync Failed!',
                                    html: `
                                        <div class="text-left">
                                            <p><strong>Provider:</strong> ${data.provider_name}</p>
                                            <p><strong>Error:</strong> ${data.error_message || 'Unknown error occurred'}</p>
                                            <p class="mt-2 text-sm text-red-600">Please check the logs for more details.</p>
                                        </div>
                                    `,
                                    confirmButtonText: 'View Logs',
                                    showCancelButton: true,
                                    cancelButtonText: 'Close'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        window.open(`/webpanel/api-management/${providerId}/logs`, '_blank');
                                    }
                                });
                            }
                            
                            // Refresh page to show updated status
                            setTimeout(() => location.reload(), 6000);
                        }
                        
                        // Progress indicator for long-running syncs
                        if (data.status === 'running' && data.duration > 30) {
                            console.log(`Sync still running... Duration: ${Math.floor(data.duration / 60)}m ${data.duration % 60}s`);
                        }
                        
                    } else {
                        console.error('Failed to check sync status:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error checking sync status:', error);
                    
                    // If we can't check status for 5 consecutive times, stop monitoring
                    if (checkCount > 5 && checkCount % 5 === 0) {
                        clearInterval(checkInterval);
                        Swal.fire({
                            icon: 'warning',
                            title: 'Connection Issue',
                            text: 'Unable to monitor sync status. Please refresh the page manually to check progress.',
                            confirmButtonText: 'Refresh Now',
                            showCancelButton: true,
                            cancelButtonText: 'Cancel'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                location.reload();
                            }
                        });
                    }
                });
                
                // Stop monitoring after maximum checks
                if (checkCount >= maxChecks) {
                    clearInterval(checkInterval);
                    Swal.fire({
                        icon: 'info',
                        title: 'Monitoring Stopped',
                        text: 'Sync monitoring has been stopped after 10 minutes. Please refresh to check current status.',
                        confirmButtonText: 'Refresh'
                    }).then(() => location.reload());
                }
                
            }, 3000); // Check every 3 seconds
        }

        function testScheduledSync(providerId, scheduleId) {
            Swal.fire({
                title: 'Test Scheduled Sync?',
                text: 'This will run the scheduled sync for testing purposes.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, test it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    const modal = tailwind.Modal.getOrCreateInstance(document.querySelector("#loading-modal"));
                    modal.show();
                    
                    // Use the existing artisan command route with schedule-id parameter
                    fetch(`/webpanel/api-management/${providerId}/schedules/${scheduleId}/test`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        modal.hide();
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Scheduled Sync Test Completed!',
                                html: `
                                    <div class="text-left">
                                        <p><strong>Type:</strong> <span class="inline-block px-2 py-1 bg-green-100 text-green-800 text-xs rounded">⏰ Scheduled</span></p>
                                        <p><strong>Total Records:</strong> ${data.summary.total_records}</p>
                                        <p><strong>Created Tours:</strong> ${data.summary.created_tours}</p>
                                        <p><strong>Updated Tours:</strong> ${data.summary.updated_tours || 0}</p>
                                        <p><strong>Duplicated Tours:</strong> ${data.summary.duplicated_tours}</p>
                                        <p><strong>Errors:</strong> ${data.summary.error_count}</p>
                                    </div>
                                `,
                                confirmButtonText: 'OK'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Scheduled Sync Test Failed!',
                                text: data.message,
                                confirmButtonText: 'OK'
                            });
                        }
                    })
                    .catch(error => {
                        modal.hide();
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'An error occurred during scheduled sync test.',
                            confirmButtonText: 'OK'
                        });
                    });
                }
            });
        }

        function toggleStatus(providerId) {
            fetch(`/webpanel/api-management/${providerId}/toggle-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: data.message,
                        confirmButtonText: 'OK'
                    });
                }
            });
        }

        function deleteProvider(providerId) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/webpanel/api-management/${providerId}`;
                    form.innerHTML = `
                        <input type="hidden" name="_method" value="DELETE">
                        <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').getAttribute('content')}">
                    `;
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }
    </script>

    <!-- Auto-refresh for running syncs -->
    <script>
        // Debug: Add console logging for sync monitoring
        window.SyncMonitor = {
            activeSyncs: new Map(),
            
            log: function(message) {
                const timestamp = new Date().toLocaleTimeString();
                console.log(`[${timestamp}] Sync Monitor: ${message}`);
            },
            
            trackSync: function(syncLogId, providerId, status) {
                this.activeSyncs.set(syncLogId, {
                    providerId: providerId,
                    status: status,
                    startTime: Date.now()
                });
                this.log(`Tracking sync ${syncLogId} (Provider: ${providerId}, Status: ${status})`);
            },
            
            updateSync: function(syncLogId, status) {
                if (this.activeSyncs.has(syncLogId)) {
                    const sync = this.activeSyncs.get(syncLogId);
                    sync.status = status;
                    sync.lastUpdate = Date.now();
                    this.log(`Updated sync ${syncLogId} status to: ${status}`);
                    
                    if (status === 'completed' || status === 'failed') {
                        this.activeSyncs.delete(syncLogId);
                        this.log(`Removed completed sync ${syncLogId} from tracking`);
                    }
                }
            },
            
            getActiveSyncCount: function() {
                return this.activeSyncs.size;
            }
        };
        // Check for stale syncs on page load (no auto-refresh)
        document.addEventListener('DOMContentLoaded', function() {
            const runningElements = document.querySelectorAll('[data-status="running"], [data-status="queued"]');
            
            if (runningElements.length > 0) {
                console.log('Found running/queued syncs, checking for stale syncs...');
                
                // Check for stale syncs on page load only
                runningElements.forEach(element => {
                    const syncCard = element.closest('.api-card');
                    if (syncCard) {
                        const syncTime = element.closest('.text-xs').querySelector('.text-slate-500');
                        if (syncTime && syncTime.textContent.includes('ago')) {
                            // If sync started more than 10 minutes ago, mark as potentially stale
                            const timeText = syncTime.textContent;
                            if (timeText.includes('hour') || (timeText.includes('minute') && parseInt(timeText) > 10)) {
                                element.style.border = '2px solid #f59e0b';
                                element.title = 'This sync may be stuck. Check logs for details.';
                            }
                        }
                    }
                });
                
                // No auto-refresh - only update via monitorSyncProgress when manual sync is started
            }
        });
    </script>

        </div>
        <!-- END: Content -->
    </div>
    
    <!-- BEGIN: JS Assets-->
    @include("backend.layout.script")
    <!-- END: JS Assets-->
</body>
</html>