<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $fund->name }} - Revisions</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="bg-gray-50 text-gray-900">
    <div class="max-w-7xl mx-auto bg-white shadow-lg">
        <!-- Header -->
        <div class="bg-gray-800 text-white p-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold">{{ $fund->name }} - Revisions</h1>
                <p class="text-gray-300 text-sm mt-2">View and restore previous versions of this fund</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('funds.show', $fund) }}" 
                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition duration-150 ease-in-out">
                    Back to Fund
                </a>
                <a href="{{ route('funds.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                    All Funds
                </a>
            </div>
        </div>

        <!-- Success Message -->
        @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 m-6 rounded">
            {{ session('success') }}
        </div>
        @endif

        <!-- Current Version Info -->
        <div class="p-6 border-b border-gray-200 bg-blue-50">
            <h2 class="text-lg font-semibold text-blue-800 mb-2">Current Version</h2>
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-sm text-gray-600">Last updated: {{ $fund->updated_at->format('M d, Y H:i:s') }}</p>
                    <p class="text-sm text-gray-600">Total revisions: {{ $revisions->total() }}</p>
                </div>
                <span class="bg-blue-200 text-blue-800 px-3 py-1 rounded-full text-sm font-medium">Current</span>
            </div>
        </div>

        <!-- Revisions List -->
        <div class="p-6">
            @if($revisions->count() > 0)
                <div class="space-y-4">
                    @foreach($revisions as $revision)
                    <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition duration-150">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <div class="flex items-center space-x-3 mb-2">
                                    <h3 class="font-semibold text-gray-900">
                                        Revision #{{ $revisions->total() - $loop->index }}
                                    </h3>
                                    <span class="text-xs text-gray-500">
                                        {{ $revision->created_at->format('M d, Y H:i:s') }}
                                    </span>
                                    <span class="text-xs text-gray-500">
                                        by {{ $revision->user->name ?? 'Unknown' }}
                                    </span>
                                </div>
                                
                                @if($revision->change_summary)
                                <p class="text-sm text-gray-600 mb-2">{{ $revision->change_summary }}</p>
                                @endif
                                
                                @if($revision->changed_field)
                                <div class="text-xs bg-gray-100 rounded p-2">
                                    <p><strong>Field:</strong> {{ $revision->changed_field }}</p>
                                    @if($revision->old_value !== null)
                                    <p><strong>From:</strong> <span class="text-red-600">{{ Str::limit($revision->old_value, 100) }}</span></p>
                                    @endif
                                    @if($revision->new_value !== null)
                                    <p><strong>To:</strong> <span class="text-green-600">{{ Str::limit($revision->new_value, 100) }}</span></p>
                                    @endif
                                </div>
                                @endif
                            </div>
                            
                            <div class="flex space-x-2 ml-4">
                                <a href="{{ route('funds.revisions.show', [$fund, $revision]) }}" 
                                   class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm transition duration-150">
                                    View
                                </a>
                                <form method="POST" action="{{ route('funds.revisions.restore', [$fund, $revision]) }}" 
                                      class="inline" 
                                      onsubmit="return confirm('Are you sure you want to restore to this revision? This will create a new revision of the current state.')">
                                    @csrf
                                    <button type="submit" 
                                            class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm transition duration-150">
                                        Restore
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $revisions->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No revisions yet</h3>
                    <p class="mt-1 text-sm text-gray-500">Revisions will appear here when you make edits to the fund.</p>
                </div>
            @endif
        </div>
    </div>
</body>
</html>