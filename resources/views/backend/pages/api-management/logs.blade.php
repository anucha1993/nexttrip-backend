<!DOCTYPE html>
<html lang="en" class="light">
<!-- BEGIN: Head -->
<head>
    <!-- BEGIN: CSS Assets-->
    @include("backend.layout.css")
    <!-- END: CSS Assets-->
</head>
<!-- END: Head -->

<body class="py-5">
    <!-- BEGIN: Mobile Menu -->
    @include("backend.layout.mobile-menu")
    <!-- END: Mobile Menu -->
    <div class="flex">
        <!-- BEGIN: Side Menu -->
        @include("backend.layout.side-menu")
        <!-- END: Side Menu -->

        <!-- BEGIN: Content -->
        <div class="content">
            <!-- BEGIN: Top Bar -->
            @include("backend.layout.topbar")
            <!-- END: Top Bar -->
<div class="container mx-auto px-4">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">API Sync Logs</h1>
            <p class="text-gray-600">{{ $provider->name }} - Synchronization History</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('api-management.show', $provider->id) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                <i class="lucide lucide-arrow-left w-4 h-4 mr-2"></i>
                Back to Provider
            </a>
            <button onclick="clearLogs()" class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                <i class="lucide lucide-trash-2 w-4 h-4 mr-2"></i>
                Clear Logs
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Syncs</p>
                    <p class="text-2xl font-bold text-gray-900 mt-2">{{ $stats['total'] }}</p>
                </div>
                <div class="p-3 bg-blue-100 rounded-lg">
                    <i class="lucide lucide-activity text-blue-600 w-6 h-6"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Successful</p>
                    <p class="text-2xl font-bold text-green-600 mt-2">{{ $stats['success'] }}</p>
                </div>
                <div class="p-3 bg-green-100 rounded-lg">
                    <i class="lucide lucide-check-circle text-green-600 w-6 h-6"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Failed</p>
                    <p class="text-2xl font-bold text-red-600 mt-2">{{ $stats['failed'] }}</p>
                </div>
                <div class="p-3 bg-red-100 rounded-lg">
                    <i class="lucide lucide-x-circle text-red-600 w-6 h-6"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Success Rate</p>
                    <p class="text-2xl font-bold text-orange-600 mt-2">
                        @if($stats['total'] > 0)
                            {{ number_format(($stats['success'] / $stats['total']) * 100, 1) }}%
                        @else
                            -
                        @endif
                    </p>
                </div>
                <div class="p-3 bg-orange-100 rounded-lg">
                    <i class="lucide lucide-trending-up text-orange-600 w-6 h-6"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
        <form method="GET" action="{{ route('api-management.logs', $provider->id) }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select name="status" id="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All Status</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                    <option value="running" {{ request('status') == 'running' ? 'selected' : '' }}>Running</option>
                </select>
            </div>

            <div>
                <label for="date_from" class="block text-sm font-medium text-gray-700 mb-2">From Date</label>
                <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div>
                <label for="date_to" class="block text-sm font-medium text-gray-700 mb-2">To Date</label>
                <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div class="flex items-end space-x-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <i class="lucide lucide-filter w-4 h-4 inline mr-2"></i>
                    Filter
                </button>
                <a href="{{ route('api-management.logs', $provider->id) }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                    <i class="lucide lucide-x w-4 h-4"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Logs Table -->
    <div class="bg-white rounded-lg shadow-sm border">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Sync Logs ({{ $logs->total() }} records)</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Message</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Records</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($logs as $log)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ 
                                $log->sync_type == 'manual' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800'
                            }}">
                                @if($log->sync_type == 'manual')
                                    👤 Manual
                                @elseif($log->sync_type == 'auto')
                                    ⏰ Scheduled
                                @else
                                    ❓ Unknown
                                @endif
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ 
                                $log->status == 'completed' ? 'bg-green-100 text-green-800' : 
                                ($log->status == 'failed' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') 
                            }}">
                                {{ ucfirst($log->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            @if($log->started_at)
                                <div>{{ $log->started_at->format('M d, Y') }}</div>
                                <div class="text-xs text-gray-500">{{ $log->started_at->format('H:i:s') }}</div>
                            @else
                                <div class="text-gray-400">No date</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            <div class="max-w-xs truncate" title="{{ $log->error_message }}">
                                {{ $log->error_message ?? 'API Sync Process' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <div class="flex flex-col space-y-1 text-xs">
                                <span class="text-blue-600">Total: {{ $log->total_records ?? 0 }}</span>
                                <span class="text-green-600">Created: {{ $log->created_tours ?? 0 }}</span>
                                <span class="text-orange-600">Updated: {{ $log->updated_tours ?? 0 }}</span>
                                <span class="text-purple-600">Duplicated: {{ $log->duplicated_tours ?? 0 }}</span>
                                @if($log->error_count > 0)
                                <span class="text-red-600">Errors: {{ $log->error_count ?? 0 }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if($log->started_at && $log->completed_at)
                                @php
                                    $startTime = \Carbon\Carbon::parse($log->started_at);
                                    $endTime = \Carbon\Carbon::parse($log->completed_at);
                                    // Check if end time is before start time (data issue)
                                    if ($endTime->lt($startTime)) {
                                        $duration = 'Invalid';
                                    } else {
                                        $duration = $startTime->diffInSeconds($endTime) . 's';
                                    }
                                @endphp
                                @if($duration === 'Invalid')
                                    <span class="text-red-500" title="End time is before start time">Invalid</span>
                                @else
                                    {{ $duration }}
                                @endif
                            @elseif($log->status == 'running')
                                <span class="text-yellow-600">Running...</span>
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center space-x-2">
                                <!-- View Details Button -->
                                <button onclick="showLogDetails({{ $log->id }})" 
                                        class="inline-flex items-center px-2 py-1 text-xs bg-blue-100 text-blue-700 rounded hover:bg-blue-200"
                                        title="View Details">
                                    <i class="lucide lucide-eye w-3 h-3 mr-1"></i>
                                    Details
                                </button>
                                
                                @if($log->error_count > 0 || $log->error_message)
                                <!-- View Errors Button -->
                                <button onclick="showErrorDetails({{ $log->id }})" 
                                        class="inline-flex items-center px-2 py-1 text-xs bg-red-100 text-red-700 rounded hover:bg-red-200"
                                        title="View Errors">
                                    <i class="lucide lucide-alert-circle w-3 h-3 mr-1"></i>
                                    Errors
                                </button>
                                @endif
                                
                                @if($log->status == 'completed' && $log->duplicated_tours > 0)
                                <!-- View Duplicates Button -->
                                <a href="{{ route('api-management.duplicates', $provider->id) }}?log_id={{ $log->id }}" 
                                   class="inline-flex items-center px-2 py-1 text-xs bg-yellow-100 text-yellow-700 rounded hover:bg-yellow-200"
                                   title="View Duplicates">
                                    <i class="lucide lucide-copy w-3 h-3 mr-1"></i>
                                    Duplicates
                                </a>
                                @endif
                                
                                <!-- Delete Log Button -->
                                <button onclick="deleteLog({{ $log->id }})" 
                                        class="inline-flex items-center px-2 py-1 text-xs bg-gray-100 text-gray-700 rounded hover:bg-gray-200"
                                        title="Delete Log">
                                    <i class="lucide lucide-trash-2 w-3 h-3 mr-1"></i>
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="text-gray-500">
                                <i class="lucide lucide-file-text w-12 h-12 mx-auto mb-4 text-gray-300"></i>
                                <p class="text-lg font-medium">No sync logs found</p>
                                <p class="text-sm">Sync logs will appear here after running synchronization</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $logs->appends(request()->query())->links() }}
        </div>
        @endif
             
    </div>
</div>

<!-- Log Details Modal -->
<div id="logDetailsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center pb-3 border-b">
            <h3 class="text-lg font-semibold text-gray-900">Sync Log Details</h3>
            <button onclick="closeLogDetails()" class="text-gray-400 hover:text-gray-600">
                <i class="lucide lucide-x w-6 h-6"></i>
            </button>
        </div>
        <div id="logDetailsContent" class="mt-4">
            <!-- Content will be loaded here -->
        </div>
    </div>
</div>

<!-- Error Details Modal -->
<div id="errorDetailsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center pb-3 border-b">
            <h3 class="text-lg font-semibold text-gray-900 text-red-600">Error Details</h3>
            <button onclick="closeErrorDetails()" class="text-gray-400 hover:text-gray-600">
                <i class="lucide lucide-x w-6 h-6"></i>
            </button>
        </div>
        <div id="errorDetailsContent" class="mt-4">
            <!-- Content will be loaded here -->
        </div>
    </div>
</div>

<script>
function showLogDetails(logId) {
    fetch(`/webpanel/api-management/logs/${logId}`, {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('logDetailsContent').innerHTML = `
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <span class="px-2 py-1 text-xs font-medium rounded-full ${
                            data.status === 'completed' ? 'bg-green-100 text-green-800' : 
                            (data.status === 'failed' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800')
                        }">${data.status.charAt(0).toUpperCase() + data.status.slice(1)}</span>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Duration</label>
                        <p class="text-sm text-gray-900">${data.duration || '-'}</p>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Started At</label>
                        <p class="text-sm text-gray-900">${data.started_at || '-'}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Completed At</label>
                        <p class="text-sm text-gray-900">${data.completed_at || 'Not completed'}</p>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Message</label>
                    <p class="text-sm text-gray-900">${data.message || 'No message'}</p>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Total Records</label>
                        <p class="text-sm text-gray-900">${data.records_processed || 0}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Created Tours</label>
                        <p class="text-sm text-gray-900">${data.records_created || 0}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Updated Tours</label>
                        <p class="text-sm text-gray-900">${data.records_updated || 0}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Duplicated Tours</label>
                        <p class="text-sm text-gray-900">${data.records_duplicated || 0}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Errors</label>
                        <p class="text-sm text-gray-900">${data.records_failed || 0}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Sync Type</label>
                        <p class="text-sm text-gray-900">
                            ${data.sync_type === 'manual' ? '👤 Manual' : (data.sync_type === 'auto' ? '⏰ Scheduled' : '❓ Unknown')}
                        </p>
                    </div>
                </div>
                
                ${data.response_data ? `
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Response Data</label>
                        <pre class="mt-1 text-xs bg-gray-100 p-3 rounded-lg overflow-x-auto">${JSON.stringify(data.response_data, null, 2)}</pre>
                    </div>
                ` : ''}
            </div>
        `;
        document.getElementById('logDetailsModal').classList.remove('hidden');
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

function closeLogDetails() {
    document.getElementById('logDetailsModal').classList.add('hidden');
}

function showErrorDetails(logId) {
    fetch(`/webpanel/api-management/logs/${logId}`, {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('errorDetailsContent').innerHTML = `
            <div class="space-y-4">
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <i class="lucide lucide-alert-circle text-red-500 w-5 h-5 mr-2"></i>
                        <h4 class="text-sm font-medium text-red-800">Error Information</h4>
                    </div>
                    <div class="mt-2 text-sm text-red-700">
                        ${data.message || 'An error occurred during sync'}
                    </div>
                </div>
                
                ${data.error_details ? `
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Error Details</label>
                        <pre class="mt-1 text-xs bg-gray-100 p-3 rounded-lg overflow-x-auto text-red-600">${JSON.stringify(data.error_details, null, 2)}</pre>
                    </div>
                ` : ''}
                
                ${data.response_data ? `
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Response Data</label>
                        <pre class="mt-1 text-xs bg-gray-100 p-3 rounded-lg overflow-x-auto">${JSON.stringify(data.response_data, null, 2)}</pre>
                    </div>
                ` : ''}
            </div>
        `;
        document.getElementById('errorDetailsModal').classList.remove('hidden');
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

function closeErrorDetails() {
    document.getElementById('errorDetailsModal').classList.add('hidden');
}

function clearLogs() {
    Swal.fire({
        title: 'Clear All Logs?',
        text: 'This will permanently delete all sync logs for this provider. This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, clear logs',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/webpanel/api-management/{{ $provider->id }}/clear-logs`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Logs Cleared!',
                        text: 'All sync logs have been cleared successfully.',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: data.message || 'Failed to clear logs',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    title: 'Error',
                    text: 'Failed to clear logs',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            });
        }
    });
}

// Delete individual log
function deleteLog(logId) {
    Swal.fire({
        title: 'Delete Log?',
        text: 'Are you sure you want to delete this sync log? This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/webpanel/api-management/logs/${logId}/delete`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'Log has been deleted successfully.',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: data.message || 'Failed to delete log',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    title: 'Error',
                    text: 'Failed to delete log',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            });
        }
    });
}

// Show error details
function showErrorDetails(logId) {
    fetch(`/webpanel/api-management/logs/${logId}/details`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('errorLogId').textContent = data.id;
            document.getElementById('errorMessage').textContent = data.error_message || 'No error message';
            document.getElementById('errorCount').textContent = data.error_count || 0;
            document.getElementById('errorDetailsModal').classList.remove('hidden');
        })
        .catch(error => {
            Swal.fire({
                title: 'Error',
                text: 'Failed to load error details',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        });
}

// Close error details modal
function closeErrorDetails() {
    document.getElementById('errorDetailsModal').classList.add('hidden');
}

// Close modals when clicking outside
window.onclick = function(event) {
    const logModal = document.getElementById('logDetailsModal');
    const errorModal = document.getElementById('errorDetailsModal');
    
    if (event.target == logModal) {
        closeLogDetails();
    }
    if (event.target == errorModal) {
        closeErrorDetails();
    }
}
</script>
        </div>
        <!-- END: Content -->
    </div>
    
    <!-- BEGIN: JS Assets-->
    @include("backend.layout.script")
    <!-- END: JS Assets-->
</body>
</html>