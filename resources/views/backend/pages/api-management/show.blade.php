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
            <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $provider->name }}</h1>
            <p class="text-gray-600">{{ $provider->description }}</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('api-management.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                <i class="lucide lucide-arrow-left w-4 h-4 mr-2"></i>
                Back to List
            </a>
            <a href="{{ route('api-management.edit', $provider->id) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                <i class="lucide lucide-edit w-4 h-4 mr-2"></i>
                Edit
            </a>
            <button onclick="testConnection({{ $provider->id }})" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                <i class="lucide lucide-wifi w-4 h-4 mr-2"></i>
                Test Connection
            </button>
            <button onclick="syncManual({{ $provider->id }})" class="inline-flex items-center px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700" id="sync-btn-{{ $provider->id }}">
                <i class="lucide lucide-refresh-cw w-4 h-4 mr-2" id="sync-icon-{{ $provider->id }}"></i>
                Manual Sync
            </button>
        </div>
    </div>

    <!-- Status and Overview Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Status</p>
                    <div class="flex items-center mt-2">
                        <span class="px-3 py-1 text-sm rounded-full {{ $provider->status == 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ ucfirst($provider->status) }}
                        </span>
                    </div>
                </div>
                <div class="p-3 {{ $provider->status == 'active' ? 'bg-green-100' : 'bg-red-100' }} rounded-lg">
                    <i class="lucide {{ $provider->status == 'active' ? 'lucide-check-circle text-green-600' : 'lucide-x-circle text-red-600' }} w-6 h-6"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Last Sync</p>
                    <p class="text-lg font-bold text-gray-900 mt-2">
                        @if($lastSync)
                            {{ $lastSync->created_at->diffForHumans() }}
                        @else
                            Never
                        @endif
                    </p>
                </div>
                <div class="p-3 bg-blue-100 rounded-lg">
                    <i class="lucide lucide-clock text-blue-600 w-6 h-6"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Synced</p>
                    <p class="text-lg font-bold text-gray-900 mt-2">{{ $syncStats['total'] ?? 0 }}</p>
                </div>
                <div class="p-3 bg-purple-100 rounded-lg">
                    <i class="lucide lucide-database text-purple-600 w-6 h-6"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Success Rate</p>
                    <p class="text-lg font-bold text-gray-900 mt-2">
                        @if($syncStats['total'] > 0)
                            {{ number_format(($syncStats['success'] / $syncStats['total']) * 100, 1) }}%
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

    <!-- Main Content Tabs -->
    <div class="bg-white rounded-lg shadow-sm border">
        <div class="border-b">
            <nav class="flex space-x-8 px-6">
                <button onclick="showTab('overview')" class="tab-button py-4 px-1 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap active" data-tab="overview">
                    <i class="lucide lucide-info w-4 h-4 inline mr-2"></i>
                    Overview
                </button>
                <button onclick="showTab('field-mappings')" class="tab-button py-4 px-1 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap" data-tab="field-mappings">
                    <i class="lucide lucide-git-merge w-4 h-4 inline mr-2"></i>
                    Field Mappings ({{ $provider->fieldMappings->count() }})
                </button>
                <button onclick="showTab('conditions')" class="tab-button py-4 px-1 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap" data-tab="conditions">
                    <i class="lucide lucide-filter w-4 h-4 inline mr-2"></i>
                    Conditions ({{ $provider->conditions->count() }})
                </button>
                <button onclick="showTab('schedules')" class="tab-button py-4 px-1 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap" data-tab="schedules">
                    <i class="lucide lucide-calendar w-4 h-4 inline mr-2"></i>
                    Schedules ({{ $provider->schedules->count() }})
                </button>
                <button onclick="showTab('recent-logs')" class="tab-button py-4 px-1 border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap" data-tab="recent-logs">
                    <i class="lucide lucide-file-text w-4 h-4 inline mr-2"></i>
                    Recent Logs
                </button>
            </nav>
        </div>

        <div class="p-6">
            <!-- Overview Tab -->
            <div id="tab-overview" class="tab-content">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Basic Information -->
                    <div class="space-y-6">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h3>
                            <div class="space-y-3">
                                <div class="flex justify-between py-2 border-b">
                                    <span class="font-medium text-gray-700">Provider Code:</span>
                                    <span class="text-gray-900">{{ $provider->code }}</span>
                                </div>
                                <div class="flex justify-between py-2 border-b">
                                    <span class="font-medium text-gray-700">API URL:</span>
                                    <span class="text-gray-900 text-sm break-all">{{ $provider->url }}</span>
                                </div>
                                @if($provider->requires_multi_step)
                                <div class="bg-blue-50 p-3 rounded-lg border-l-4 border-blue-400">
                                    <h4 class="font-medium text-blue-900 mb-2">🔗 Multi-step API Configuration</h4>
                                    <div class="space-y-2 text-sm">
                                        @if($provider->tour_detail_endpoint)
                                        <div class="flex justify-between">
                                            <span class="text-blue-700">Tour Detail Endpoint:</span>
                                            <code class="text-blue-900 bg-blue-100 px-2 py-1 rounded">{{ $provider->tour_detail_endpoint }}</code>
                                        </div>
                                        @endif
                                        @if($provider->period_endpoint)
                                        <div class="flex justify-between">
                                            <span class="text-blue-700">Period Endpoint:</span>
                                            <code class="text-blue-900 bg-blue-100 px-2 py-1 rounded">{{ $provider->period_endpoint }}</code>
                                        </div>
                                        @endif
                                        @if($provider->url_parameters)
                                        <div class="mt-2">
                                            <span class="text-blue-700 font-medium">URL Parameters:</span>
                                            <div class="mt-1 space-y-1">
                                                @foreach($provider->url_parameters as $key => $value)
                                                <div class="flex justify-between text-xs">
                                                    <span class="text-blue-600">{{ $key }}:</span>
                                                    <code class="text-blue-800">{{ $value }}</code>
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                @endif
                                <div class="flex justify-between py-2 border-b">
                                    <span class="font-medium text-gray-700">Created:</span>
                                    <span class="text-gray-900">{{ $provider->created_at->format('M d, Y H:i') }}</span>
                                </div>
                                <div class="flex justify-between py-2 border-b">
                                    <span class="font-medium text-gray-700">Updated:</span>
                                    <span class="text-gray-900">{{ $provider->updated_at->format('M d, Y H:i') }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- API Configuration -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Configuration</h3>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <pre class="text-sm text-gray-700 overflow-x-auto">{{ json_encode($provider->config, JSON_PRETTY_PRINT) }}</pre>
                            </div>
                        </div>
                    </div>

                    <!-- Headers and Authentication -->
                    <div class="space-y-6">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Request Headers</h3>
                            <div class="space-y-2">
                                @forelse($provider->headers as $key => $value)
                                <div class="flex justify-between items-center py-2 px-3 bg-gray-50 rounded-lg">
                                    <span class="font-medium text-gray-700">{{ $key }}:</span>
                                    <span class="text-gray-900 text-sm">
                                        @if(str_contains(strtolower($key), 'auth') || str_contains(strtolower($key), 'key') || str_contains(strtolower($key), 'token'))
                                            <span class="text-gray-400">••••••••••••</span>
                                        @else
                                            {{ $value }}
                                        @endif
                                    </span>
                                </div>
                                @empty
                                <p class="text-gray-500 italic">No headers configured</p>
                                @endforelse
                            </div>
                        </div>

                        <!-- Connection Status -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Connection Status</h3>
                            <div id="connection-status" class="p-4 rounded-lg border">
                                <p class="text-gray-600">Click "Test Connection" to check API status</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Field Mappings Tab -->
            <div id="tab-field-mappings" class="tab-content hidden">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Field Mappings Configuration</h3>
                
                <!-- Tour Fields -->
                <div class="mb-6">
                    <h4 class="text-md font-semibold text-gray-800 mb-3">Tour Fields</h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Local Field</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">API Field</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Required</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Default Value</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($provider->fieldMappings->where('field_type', 'tour') as $mapping)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $mapping->local_field }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $mapping->api_field }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">{{ $mapping->data_type }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if($mapping->is_required)
                                            <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">Required</span>
                                        @else
                                            <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">Optional</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $mapping->default_value ?? '-' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">No tour field mappings configured</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Period Fields -->
                <div>
                    <h4 class="text-md font-semibold text-gray-800 mb-3">Period Fields</h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Local Field</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">API Field</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Required</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Default Value</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($provider->fieldMappings->where('field_type', 'period') as $mapping)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $mapping->local_field }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $mapping->api_field }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">{{ $mapping->data_type }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if($mapping->is_required)
                                            <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">Required</span>
                                        @else
                                            <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">Optional</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $mapping->default_value ?? '-' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">No period field mappings configured</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Conditions Tab -->
            <div id="tab-conditions" class="tab-content hidden">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Filtering Conditions</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Field</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Operator</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Value</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($provider->conditions as $condition)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $condition->field_name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <span class="px-2 py-1 text-xs rounded-full bg-purple-100 text-purple-800">{{ $condition->operator }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $condition->value }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <span class="px-2 py-1 text-xs rounded-full {{ $condition->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ $condition->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-gray-500">No filtering conditions configured</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Schedules Tab -->
            <div id="tab-schedules" class="tab-content hidden">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Sync Schedules</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Schedule</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Next Run</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($provider->schedules as $schedule)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $schedule->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $schedule->cron_expression }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if($schedule->next_run_at)
                                        {{ $schedule->next_run_at->format('M d, Y H:i') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <span class="px-2 py-1 text-xs rounded-full {{ $schedule->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ $schedule->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-gray-500">No schedules configured</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Logs Tab -->
            <div id="tab-recent-logs" class="tab-content hidden">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Recent Sync Logs</h3>
                    <a href="{{ route('api-management.logs', $provider->id) }}" class="text-blue-600 hover:text-blue-800 text-sm">
                        View all logs →
                    </a>
                </div>
                <div class="space-y-4">
                    @forelse($recentLogs as $log)
                    <div class="border rounded-lg p-4 {{ $log->status == 'success' ? 'bg-green-50 border-green-200' : ($log->status == 'error' ? 'bg-red-50 border-red-200' : 'bg-yellow-50 border-yellow-200') }}">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <div class="flex items-center space-x-3 mb-2">
                                    <span class="px-2 py-1 text-xs rounded-full {{ $log->status == 'success' ? 'bg-green-100 text-green-800' : ($log->status == 'error' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                        {{ ucfirst($log->status) }}
                                    </span>
                                    <span class="text-sm text-gray-600">{{ $log->created_at->format('M d, Y H:i:s') }}</span>
                                </div>
                                @if($log->message)
                                <p class="text-sm text-gray-700 mb-2">{{ $log->message }}</p>
                                @endif
                                <div class="text-xs text-gray-600">
                                    Processed: {{ $log->records_processed ?? 0 }} | 
                                    Created: {{ $log->records_created ?? 0 }} | 
                                    Updated: {{ $log->records_updated ?? 0 }} |
                                    Failed: {{ $log->records_failed ?? 0 }}
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-8 text-gray-500">
                        <i class="lucide lucide-file-text w-12 h-12 mx-auto mb-4 text-gray-300"></i>
                        <p>No sync logs available</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.add('hidden');
    });
    
    // Remove active class from all tab buttons
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('active', 'border-blue-500', 'text-blue-600');
        button.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Show selected tab content
    document.getElementById('tab-' + tabName).classList.remove('hidden');
    
    // Add active class to selected tab button
    const activeButton = document.querySelector('[data-tab="' + tabName + '"]');
    activeButton.classList.add('active', 'border-blue-500', 'text-blue-600');
    activeButton.classList.remove('border-transparent', 'text-gray-500');
}

function testConnection(providerId) {
    const statusDiv = document.getElementById('connection-status');
    statusDiv.innerHTML = `
        <div class="flex items-center">
            <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-600 mr-2"></div>
            <span class="text-blue-600">Testing connection...</span>
        </div>
    `;

    fetch(`/webpanel/api-management/${providerId}/test-connection`, {
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
            statusDiv.innerHTML = `
                <div class="flex items-center text-green-600">
                    <i class="lucide lucide-check-circle w-5 h-5 mr-2"></i>
                    <div>
                        <div class="font-medium">Connection successful</div>
                        <div class="text-sm text-gray-600">Response time: ${data.response_time}ms</div>
                    </div>
                </div>
            `;
        } else {
            statusDiv.innerHTML = `
                <div class="flex items-center text-red-600">
                    <i class="lucide lucide-x-circle w-5 h-5 mr-2"></i>
                    <div>
                        <div class="font-medium">Connection failed</div>
                        <div class="text-sm text-gray-600">${data.message}</div>
                    </div>
                </div>
            `;
        }
    })
    .catch(error => {
        statusDiv.innerHTML = `
            <div class="flex items-center text-red-600">
                <i class="lucide lucide-x-circle w-5 h-5 mr-2"></i>
                <div class="font-medium">Connection test failed</div>
            </div>
        `;
    });
}

function syncManual(providerId) {
    const btn = document.getElementById(`sync-btn-${providerId}`);
    const icon = document.getElementById(`sync-icon-${providerId}`);
    
    btn.disabled = true;
    icon.classList.add('animate-spin');
    btn.innerHTML = '<i class="lucide lucide-refresh-cw w-4 h-4 mr-2 animate-spin"></i>Syncing...';

    fetch(`/webpanel/api-management/${providerId}/sync-manual`, {
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
                title: 'Sync Completed!',
                html: `
                    <div class="text-left">
                        <p><strong>Processed:</strong> ${data.stats.processed}</p>
                        <p><strong>Created:</strong> ${data.stats.created}</p>
                        <p><strong>Updated:</strong> ${data.stats.updated}</p>
                        <p><strong>Failed:</strong> ${data.stats.failed}</p>
                    </div>
                `,
                icon: 'success',
                confirmButtonText: 'OK'
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire({
                title: 'Sync Failed',
                text: data.message,
                icon: 'error',
                confirmButtonText: 'OK'
            });
        }
    })
    .catch(error => {
        Swal.fire({
            title: 'Error',
            text: 'Failed to perform sync',
            icon: 'error',
            confirmButtonText: 'OK'
        });
    })
    .finally(() => {
        btn.disabled = false;
        icon.classList.remove('animate-spin');
        btn.innerHTML = '<i class="lucide lucide-refresh-cw w-4 h-4 mr-2"></i>Manual Sync';
    });
}

// Initialize first tab as active
document.addEventListener('DOMContentLoaded', function() {
    showTab('overview');
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