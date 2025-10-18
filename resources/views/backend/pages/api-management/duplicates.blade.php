<!DOCTYPE html>
<html lang="en" class="light">
<!-- BEGIN: Head -->
<head>
    <!-- BEGIN: CSS Assets-->
    @include("backend.layout.css")
    <!-- END: CSS Assets-->
    
    <style>
        .duplicate-item {
            border-left: 4px solid #f59e0b;
            background-color: #fef3c7;
        }
        .existing-tour {
            border-left: 4px solid #10b981;
            background-color: #d1fae5;
        }
    </style>
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

            <div class="intro-y flex items-center mt-8">
                <h2 class="text-lg font-medium mr-auto">Duplicate Tours - {{ $provider->name }}</h2>
                <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
                    <a href="{{ route('api-management.show', $provider->id) }}" class="btn btn-secondary shadow-md mr-2">
                        <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
                        Back to Provider
                    </a>
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

            <!-- Statistics -->
            <div class="grid grid-cols-12 gap-6 mt-5">
                <div class="intro-y col-span-12 md:col-span-6 lg:col-span-3">
                    <div class="box p-5">
                        <div class="flex">
                            <div class="mr-3 text-center">
                                <div class="text-base text-slate-500">Total Duplicates</div>
                                <div class="text-lg font-medium">{{ $duplicates->total() }}</div>
                            </div>
                            <div class="ml-auto">
                                <div class="w-8 h-8 bg-warning/10 flex items-center justify-center rounded-full">
                                    <i data-lucide="alert-triangle" class="w-4 h-4 text-warning"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="intro-y col-span-12 md:col-span-6 lg:col-span-3">
                    <div class="box p-5">
                        <div class="flex">
                            <div class="mr-3 text-center">
                                <div class="text-base text-slate-500">Pending Review</div>
                                <div class="text-lg font-medium">{{ $duplicates->where('status', 'pending')->count() }}</div>
                            </div>
                            <div class="ml-auto">
                                <div class="w-8 h-8 bg-pending/10 flex items-center justify-center rounded-full">
                                    <i data-lucide="clock" class="w-4 h-4 text-pending"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Duplicates List -->
            <div class="intro-y box mt-5">
                <div class="p-5 border-b border-slate-200/60">
                    <h3 class="font-medium text-base">Duplicate Tours Found</h3>
                    <div class="text-slate-500 mt-1">Review and manage duplicate tour entries</div>
                </div>
                
                <div class="p-5">
                    @forelse($duplicates as $duplicate)
                        <div class="mb-8 last:mb-0">
                            <!-- Duplicate Tour (New from API) -->
                            <div class="duplicate-item border rounded-lg p-4 mb-4">
                                <div class="flex justify-between items-start mb-3">
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-warning">
                                            <i data-lucide="alert-triangle" class="w-4 h-4 inline mr-2"></i>
                                            New Tour (From API)
                                        </h4>
                                        <div class="text-sm text-slate-600 mt-1">
                                            Detected: {{ $duplicate->created_at->format('M d, Y H:i') }}
                                            @if($duplicate->syncLog && $duplicate->syncLog->sync_type)
                                                <span class="ml-2 px-2 py-1 rounded text-xs {{ $duplicate->syncLog->sync_type === 'manual' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700' }}">
                                                    {{ $duplicate->syncLog->sync_type === 'manual' ? '👤 Manual Sync' : '⏰ Scheduled Sync' }}
                                                </span>
                                            @elseif($duplicate->sync_log_id)
                                                <span class="ml-2 px-2 py-1 rounded text-xs bg-gray-100 text-gray-700">
                                                    🔄 Unknown Sync
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex space-x-2">
                                        <span class="px-2 py-1 bg-warning/20 text-warning rounded text-xs">
                                            {{ ucfirst($duplicate->status) }}
                                        </span>
                                        <span class="px-2 py-1 bg-slate-100 text-slate-600 rounded text-xs">
                                            Similarity: {{ $duplicate->similarity_score ?? 'N/A' }}%
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <div class="text-xs text-slate-500 uppercase tracking-wide">Tour Name</div>
                                        <div class="font-medium">{{ $duplicate->duplicate_data['name'] ?? 'N/A' }}</div>
                                    </div>
                                    <div>
                                        <div class="text-xs text-slate-500 uppercase tracking-wide">API ID</div>
                                        <div class="font-medium">{{ $duplicate->duplicate_data['api_id'] ?? 'N/A' }}</div>
                                    </div>
                                    <div>
                                        <div class="text-xs text-slate-500 uppercase tracking-wide">Country</div>
                                        <div class="font-medium">{{ $duplicate->duplicate_data['country_name'] ?? 'N/A' }}</div>
                                    </div>
                                </div>
                                
                                @if($duplicate->duplicate_data['description'])
                                <div class="mt-3">
                                    <div class="text-xs text-slate-500 uppercase tracking-wide">Description</div>
                                    <div class="text-sm mt-1">{{ Str::limit($duplicate->duplicate_data['description'], 200) }}</div>
                                </div>
                                @endif
                            </div>

                            <!-- Existing Tour (In Database) -->
                            @if($duplicate->existingTour)
                            <div class="existing-tour border rounded-lg p-4 mb-4">
                                <div class="flex justify-between items-start mb-3">
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-success">
                                            <i data-lucide="database" class="w-4 h-4 inline mr-2"></i>
                                            Existing Tour (In Database)
                                        </h4>
                                        <div class="text-sm text-slate-600 mt-1">
                                            Created: {{ $duplicate->existingTour->created_at->format('M d, Y H:i') }}
                                        </div>
                                    </div>
                                    <div class="flex space-x-2">
                                        <a href="{{ route('tour.edit', $duplicate->existingTour->id) }}" target="_blank" 
                                           class="px-2 py-1 bg-primary/20 text-primary rounded text-xs hover:bg-primary/30">
                                            View Tour
                                        </a>
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <div class="text-xs text-slate-500 uppercase tracking-wide">Tour Name</div>
                                        <div class="font-medium">{{ $duplicate->existingTour->name ?? 'N/A' }}</div>
                                    </div>
                                    <div>
                                        <div class="text-xs text-slate-500 uppercase tracking-wide">Tour Code</div>
                                        <div class="font-medium">{{ $duplicate->existingTour->code1 ?? 'N/A' }}</div>
                                    </div>
                                    <div>
                                        <div class="text-xs text-slate-500 uppercase tracking-wide">Country</div>
                                        <div class="font-medium">{{ $duplicate->existingTour->country_name ?? 'N/A' }}</div>
                                    </div>
                                </div>
                                
                                @if($duplicate->existingTour->description)
                                <div class="mt-3">
                                    <div class="text-xs text-slate-500 uppercase tracking-wide">Description</div>
                                    <div class="text-sm mt-1">{{ Str::limit($duplicate->existingTour->description, 200) }}</div>
                                </div>
                                @endif
                            </div>
                            @endif

                            <!-- Matching Fields -->
                            @if($duplicate->matching_fields)
                            <div class="border border-slate-200 rounded-lg p-4 mb-4">
                                <h5 class="font-medium mb-3">
                                    <i data-lucide="git-merge" class="w-4 h-4 inline mr-2"></i>
                                    Matching Fields
                                </h5>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($duplicate->matching_fields as $field)
                                        <span class="px-2 py-1 bg-slate-100 text-slate-700 rounded text-xs">
                                            {{ ucwords(str_replace('_', ' ', $field)) }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            <!-- Action Buttons -->
                            @if($duplicate->status == 'pending')
                            <div class="flex justify-end space-x-3 pt-3 border-t">
                                <button onclick="mergeDuplicate({{ $duplicate->id }})" 
                                        class="btn btn-success btn-sm">
                                    <i data-lucide="git-merge" class="w-4 h-4 mr-2"></i>
                                    Merge & Update
                                </button>
                                <button onclick="ignoreDuplicate({{ $duplicate->id }})" 
                                        class="btn btn-secondary btn-sm">
                                    <i data-lucide="x-circle" class="w-4 h-4 mr-2"></i>
                                    Ignore
                                </button>
                                <button onclick="showComparison({{ $duplicate->id }})" 
                                        class="btn btn-outline-secondary btn-sm">
                                    <i data-lucide="eye" class="w-4 h-4 mr-2"></i>
                                    Compare Details
                                </button>
                            </div>
                            @else
                            <div class="flex justify-end pt-3 border-t">
                                <span class="px-3 py-1 bg-slate-100 text-slate-600 rounded text-sm">
                                    Status: {{ ucfirst($duplicate->status) }}
                                    @if($duplicate->resolved_at)
                                        - {{ $duplicate->resolved_at->format('M d, Y H:i') }}
                                    @endif
                                </span>
                            </div>
                            @endif
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i data-lucide="check-circle" class="w-10 h-10 text-slate-400"></i>
                            </div>
                            <h3 class="font-medium text-slate-600 mb-2">No Duplicates Found</h3>
                            <p class="text-slate-500">All tours from this API provider are unique.</p>
                        </div>
                    @endforelse
                </div>

                <!-- Pagination -->
                @if($duplicates->hasPages())
                <div class="p-5 border-t border-slate-200/60">
                    {{ $duplicates->links() }}
                </div>
                @endif
            </div>

        </div>
        <!-- END: Content -->
    </div>
    
    <!-- BEGIN: JS Assets-->
    @include("backend.layout.script")
    <!-- END: JS Assets-->

    <script>
        function mergeDuplicate(duplicateId) {
            Swal.fire({
                title: 'Merge Duplicate Tour?',
                text: 'This will update the existing tour with new API data. This action cannot be undone.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#059669',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, merge it',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`/webpanel/api-management/duplicates/${duplicateId}/merge`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: 'Merged Successfully!',
                                text: 'The duplicate tour has been merged with the existing tour.',
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: data.message || 'Failed to merge duplicate',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            title: 'Error',
                            text: 'Failed to merge duplicate',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    });
                }
            });
        }

        function ignoreDuplicate(duplicateId) {
            Swal.fire({
                title: 'Ignore Duplicate Tour?',
                text: 'This will mark the duplicate as ignored and it won\'t appear in future reviews.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#6b7280',
                cancelButtonColor: '#ef4444',
                confirmButtonText: 'Yes, ignore it',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`/webpanel/api-management/duplicates/${duplicateId}/ignore`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: 'Ignored Successfully!',
                                text: 'The duplicate tour has been marked as ignored.',
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: data.message || 'Failed to ignore duplicate',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            title: 'Error',
                            text: 'Failed to ignore duplicate',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    });
                }
            });
        }

        function showComparison(duplicateId) {
            // Implementation for detailed comparison modal
            Swal.fire({
                title: 'Detailed Comparison',
                html: '<p>Detailed comparison feature will be implemented here.</p>',
                icon: 'info',
                confirmButtonText: 'Close'
            });
        }
    </script>
</body>
</html>