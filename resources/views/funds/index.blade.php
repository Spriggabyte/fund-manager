<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('All Funds') }}
            </h2>
            <div class="flex space-x-3" x-data="{ busy: null }">
                <form method="POST" action="{{ route('funds.data-feed.download') }}" @submit="busy = 'download'">
                    @csrf
                    <button type="submit" :disabled="busy" class="bg-emerald-600 hover:bg-emerald-700 disabled:opacity-60 text-white font-bold py-2 px-4 rounded-lg transition duration-150 ease-in-out"
                            title="Download the newest month of fund data from the SFTP server">
                        <span x-text="busy === 'download' ? 'Downloading…' : 'Download Latest Data'">Download Latest Data</span>
                    </button>
                </form>
                <form method="POST" action="{{ route('funds.data-feed.import') }}"
                      @submit="if (! confirm('Import the latest downloaded data into every fund with a fund code? Existing values will be overwritten; a revision is saved before each import.')) { $event.preventDefault(); return; } busy = 'import'">
                    @csrf
                    <button type="submit" :disabled="busy" class="bg-amber-600 hover:bg-amber-700 disabled:opacity-60 text-white font-bold py-2 px-4 rounded-lg transition duration-150 ease-in-out"
                            title="Import each fund's newest downloaded month">
                        <span x-text="busy === 'import' ? 'Importing…' : 'Import Latest Data'">Import Latest Data</span>
                    </button>
                </form>
                <a href="{{ route('funds.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg transition duration-150 ease-in-out">
                    Create New Fund
                </a>
                <a href="{{ route('dashboard') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg transition duration-150 ease-in-out">
                    Back to Dashboard
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Success Message -->
            @if (session('success'))
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-800">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                    <p class="text-sm text-red-800">{{ session('error') }}</p>
                </div>
            @endif

            <!-- Portfolio Summary -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center">
                        <div class="bg-blue-100 rounded-lg p-3">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Total Funds</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $funds->count() }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center">
                        <div class="bg-purple-100 rounded-lg p-3">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Fund Classes</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $funds->whereNotNull('class')->unique('class')->count() }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Funds Table -->
            <div class="bg-white rounded-lg shadow-md">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-medium text-gray-900">Fund Portfolio</h3>
                    </div>
                </div>

                @if ($funds->count() > 0)
                    <x-fund-filters :funds="$funds">
                    <!-- Desktop Table View -->
                    <div class="hidden md:block">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fund Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Class</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Updated</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($funds as $fund)
                                    <tr class="hover:bg-gray-50" x-show="matches(@js($fund->name), @js($fund->displayClass() ?? ''))">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                                        <span class="text-indigo-600 font-medium text-sm" title="Fund code (SFTP data feed)">{{ $fund->fund_code ?: substr($fund->name, 0, 2) }}</span>
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        <a href="{{ route('funds.show', $fund) }}" class="hover:text-indigo-600">{{ $fund->name }}</a>
                                                    </div>
                                                    <div class="text-sm text-gray-500">ID: {{ $fund->id }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($fund->displayClass())
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-indigo-100 text-indigo-800">
                                                    {{ $fund->displayClass() }}
                                                </span>
                                            @else
                                                <span class="text-gray-400 text-sm">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <x-fund-updated :fund="$fund" />
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <x-fund-actions :fund="$fund" />
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card View -->
                    <div class="md:hidden">
                        <div class="space-y-4 p-4">
                            @foreach ($funds as $fund)
                                <div class="bg-gray-50 rounded-lg p-4" x-show="matches(@js($fund->name), @js($fund->displayClass() ?? ''))">
                                    <div class="flex justify-between items-start mb-3">
                                        <div>
                                            <h4 class="font-medium text-gray-900">
                                                <a href="{{ route('funds.show', $fund) }}" class="hover:text-indigo-600">{{ $fund->name }}</a>
                                            </h4>
                                            <p class="text-sm text-gray-500">{{ $fund->displayClass() ? 'Class '.$fund->displayClass() : 'Unclassified' }} • ID: {{ $fund->id }}</p>
                                        </div>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <x-fund-updated :fund="$fund" inline />
                                        <x-fund-actions :fund="$fund" />
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    </x-fund-filters>
                @else
                    <!-- Empty State -->
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2a2 2 0 00-2 2v3a2 2 0 01-2 2h-3m7-10V8a2 2 0 00-2-2H6a2 2 0 00-2-2z"></path>
                        </svg>
                        <h3 class="mt-4 text-lg font-medium text-gray-900">No funds yet</h3>
                        <p class="mt-2 text-sm text-gray-500">Get started by creating your first fund.</p>
                        <div class="mt-6">
                            <a href="{{ route('funds.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg transition duration-150 ease-in-out">
                                Create Your First Fund
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>