<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('All Funds') }}
            </h2>
            <div class="flex space-x-3">
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

            <!-- Portfolio Summary -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center">
                        <div class="bg-green-100 rounded-lg p-3">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Total Portfolio Value</p>
                            <p class="text-2xl font-bold text-gray-900">
                                ${{ number_format($funds->sum(function ($fund) { return $fund->data['value'] ?? 0; }), 2) }}
                            </p>
                        </div>
                    </div>
                </div>

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
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-medium text-gray-900">Fund Portfolio</h3>
                        <div class="flex space-x-2">
                            <button class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1 rounded-md text-sm transition duration-150">
                                Filter
                            </button>
                            <button class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1 rounded-md text-sm transition duration-150">
                                Export
                            </button>
                        </div>
                    </div>
                </div>

                @if ($funds->count() > 0)
                    <!-- Desktop Table View -->
                    <div class="hidden md:block">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fund Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Value</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Performance</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Updated</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($funds as $fund)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                                        <span class="text-indigo-600 font-medium text-sm">{{ substr($fund->name, 0, 2) }}</span>
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
                                            @if($fund->class)
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                                    @if($fund->class === 'Equity') bg-green-100 text-green-800
                                                    @elseif($fund->class === 'Bond') bg-blue-100 text-blue-800
                                                    @else bg-purple-100 text-purple-800 @endif">
                                                    {{ $fund->class }}
                                                </span>
                                            @else
                                                <span class="text-gray-400 text-sm">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                                            @if(isset($fund->data['value']))
                                                ${{ number_format($fund->data['value'], 2) }}
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if(isset($fund->data['performance']) && is_string($fund->data['performance']))
                                                <span class="text-sm font-medium text-green-600">{{ $fund->data['performance'] }}</span>
                                            @else
                                                <span class="text-gray-400 text-sm">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $fund->updated_at->format('M j, Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-2">
                                                <a href="{{ route('funds.show', $fund) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                                                <a href="{{ route('funds.fact-sheet', $fund) }}" class="text-green-600 hover:text-green-900">Fact Sheet</a>
                                                <a href="{{ route('funds.edit', $fund) }}" class="text-gray-600 hover:text-gray-900">Edit</a>
                                                <form method="POST" action="{{ route('funds.destroy', $fund) }}" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure?')">Delete</button>
                                                </form>
                                            </div>
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
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <div class="flex justify-between items-start mb-3">
                                        <div>
                                            <h4 class="font-medium text-gray-900">
                                                <a href="{{ route('funds.show', $fund) }}" class="hover:text-indigo-600">{{ $fund->name }}</a>
                                            </h4>
                                            <p class="text-sm text-gray-500">{{ $fund->class ?? 'Unclassified' }} • ID: {{ $fund->id }}</p>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4 mb-3">
                                        <div>
                                            <p class="text-sm text-gray-500">Value</p>
                                            <p class="font-medium text-gray-900">
                                                @if(isset($fund->data['value']))
                                                    ${{ number_format($fund->data['value'], 2) }}
                                                @else
                                                    <span class="text-gray-400">-</span>
                                                @endif
                                            </p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-gray-500">Performance</p>
                                            <p class="font-medium text-green-600">
                                                @if(isset($fund->data['performance']) && is_string($fund->data['performance']))
                                                    {{ $fund->data['performance'] }}
                                                @else
                                                    <span class="text-gray-400">-</span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <p class="text-sm text-gray-500">Updated: {{ $fund->updated_at->format('M j, Y') }}</p>
                                        <div class="flex space-x-2">
                                            <a href="{{ route('funds.show', $fund) }}" class="text-indigo-600 hover:text-indigo-900 text-sm">View</a>
                                            <a href="{{ route('funds.fact-sheet', $fund) }}" class="text-green-600 hover:text-green-900 text-sm">Fact Sheet</a>
                                            <a href="{{ route('funds.edit', $fund) }}" class="text-gray-600 hover:text-gray-900 text-sm">Edit</a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
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