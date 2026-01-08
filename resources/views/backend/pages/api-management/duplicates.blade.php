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
                {{-- <div class="intro-y col-span-12 md:col-span-6 lg:col-span-3">
                    <div class="box p-5">
                        <div class="flex">
                            <div class="mr-3 text-center">
                                <div class="text-base text-slate-500">Pending Review</div>
                                <div class="text-lg font-medium">{{ $duplicates->filter(function($d) { return $d->status == 'pending' || $d->status === null; })->count() }}</div>
                            </div>
                            <div class="ml-auto">
                                <div class="w-8 h-8 bg-pending/10 flex items-center justify-center rounded-full">
                                    <i data-lucide="clock" class="w-4 h-4 text-pending"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> --}}
            </div>

            <!-- Duplicates List -->
            <div class="intro-y box mt-5">
                <div class="p-5 border-b border-slate-200/60">
                    <h3 class="font-medium text-base">Duplicate Tours Found (Showing up to 300 records)</h3>
                    <div class="text-slate-500 mt-1">Review and manage duplicate tour entries</div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="table table-report -mt-2">
                        <thead>
                            <tr>
                                <th class="whitespace-nowrap">Detected</th>
                                <th class="whitespace-nowrap">API ID</th>
                                <th class="whitespace-nowrap">Existing Tour</th>
                                <th class="whitespace-nowrap">Tour Code</th>
                                <th class="whitespace-nowrap">Sync Type</th>
                                {{-- <th class="text-center whitespace-nowrap">Actions</th> --}}
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($duplicates as $duplicate)
                            <tr class="intro-x">
                                <td class="whitespace-nowrap">
                                    <div class="text-xs text-slate-500">{{ $duplicate->created_at->format('M d, Y') }}</div>
                                    <div class="text-xs text-slate-400">{{ $duplicate->created_at->format('H:i') }}</div>
                                </td>
                                <td class="whitespace-nowrap">
                                    <div class="font-medium">{{ $duplicate->api_data['api_id'] ?? ($duplicate->api_data['tour_id'] ?? ($duplicate->api_id ?? 'N/A')) }}</div>
                                </td>
                               
                                <td>
                                    @if($duplicate->existingTour)
                                    <div class="font-medium">{{ Str::limit($duplicate->existingTour->name ?? 'N/A', 50) }}</div>
                                    <div class="text-xs text-slate-500 mt-1">ID: {{ $duplicate->existingTour->id }}</div>
                                    @else
                                    <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap">
                                    @if($duplicate->existingTour)
                                    <span class="text-slate-600">{{ $duplicate->existingTour->code1 ?? 'N/A' }}</span>
                                    @else
                                    <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap">
                                    @if($duplicate->syncLog && $duplicate->syncLog->sync_type)
                                        <span class="px-2 py-1 rounded text-xs {{ $duplicate->syncLog->sync_type === 'manual' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' }}">
                                            {{ $duplicate->syncLog->sync_type === 'manual' ? '👤 Manual' : '⏰ Scheduled' }}
                                        </span>
                                    @else
                                        <span class="text-slate-400 text-xs">Manual Sync</span>
                                    @endif
                                </td>
                              
                                {{-- <td class="table-report__action w-56">
                                    <div class="flex justify-center items-center space-x-2">
                                        @if($duplicate->existingTour)
                                        <a href="/webpanel/tour/edit/{{ $duplicate->existingTour->id }}" 
                                           target="_blank"
                                           class="btn btn-sm btn-outline-secondary w-20" 
                                           title="View Tour">
                                            <i data-lucide="eye" class="w-4 h-4"></i>
                                        </a>
                                        @endif
                                        
                                        @if(!$duplicate->status || $duplicate->status == 'pending')
                                        <button onclick="mergeDuplicate({{ $duplicate->id }})" 
                                                class="btn btn-sm btn-success w-20"
                                                title="Merge">
                                            <i data-lucide="git-merge" class="w-4 h-4"></i>
                                        </button>
                                        <button onclick="ignoreDuplicate({{ $duplicate->id }})" 
                                                class="btn btn-sm btn-secondary w-20"
                                                title="Ignore">
                                            <i data-lucide="x-circle" class="w-4 h-4"></i>
                                        </button>
                                        @endif
                                    </div>
                                </td> --}}
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-12">
                                    <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <i data-lucide="check-circle" class="w-10 h-10 text-slate-400"></i>
                                    </div>
                                    <h3 class="font-medium text-slate-600 mb-2">No Duplicates Found</h3>
                                    <p class="text-slate-500">All tours from this API provider are unique.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
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