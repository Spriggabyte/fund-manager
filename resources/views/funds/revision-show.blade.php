<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $revisionFund->name }} - Revision from {{ $revision->created_at->format('M d, Y H:i:s') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        @media print {
            .no-print { display: none; }
        }
        .equity-indicator {
            background: linear-gradient(to right, #dc2626 0%, #dc2626 75%, #e5e7eb 75%, #e5e7eb 100%);
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-900">
    <div class="max-w-7xl mx-auto bg-white shadow-lg">
        <!-- Header with Revision Info -->
        <div class="no-print bg-red-600 text-white p-4 flex justify-between items-center">
            <div>
                <h1 class="text-xl font-bold">📋 Revision Preview</h1>
                <p class="text-red-100 text-sm">
                    Viewing revision from {{ $revision->created_at->format('M d, Y H:i:s') }} 
                    by {{ $revision->user->name ?? 'Unknown' }}
                </p>
                @if($revision->change_summary)
                <p class="text-red-100 text-sm">{{ $revision->change_summary }}</p>
                @endif
            </div>
            <div class="flex items-center space-x-3">
                <form method="POST" action="{{ route('funds.revisions.restore', [$fund, $revision]) }}" 
                      class="inline" 
                      onsubmit="return confirm('Are you sure you want to restore to this revision? This will create a new revision of the current state.')">
                    @csrf
                    <button type="submit" 
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition duration-150 ease-in-out flex items-center space-x-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        <span>Restore This Version</span>
                    </button>
                </form>
                <a href="{{ route('funds.revisions', $fund) }}" 
                   class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition duration-150 ease-in-out">
                    All Revisions
                </a>
                <a href="{{ route('funds.show', $fund) }}" 
                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition duration-150 ease-in-out">
                    Current Version
                </a>
            </div>
        </div>

        <!-- Fund Content (using the same layout as show.blade.php but with revision data) -->
        <!-- Page 2 -->
        <div class="bg-white mt-8 shadow-lg">
            <!-- Header -->
            <div class="bg-gray-800 text-white p-6 flex justify-between items-center">
                <div>
                    <div class="bg-red-500 text-white px-4 py-2 mb-4 inline-block font-semibold">
                        {{ $revisionFund->data['fund']['date'] ?? $revisionFund->updated_at->format('d F Y') }}
                    </div>
                    <h1 class="text-2xl font-bold">
                        {{ $revisionFund->data['fund']['name'] ?? $revisionFund->name }}
                    </h1>
                    <p class="text-gray-300 text-sm mt-2 max-w-3xl">
                        {{ $revisionFund->data['fund']['description'] ?? '' }}
                    </p>
                </div>
                <img src="{{ $revisionFund->data['fund']['logoUrl'] ?? 'data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 200 80\'%3E%3Ctext x=\'10\' y=\'50\' font-family=\'Arial\' font-size=\'40\' font-weight=\'bold\' fill=\'white\'%3EFOORD%3C/text%3E%3Ccircle cx=\'170\' cy=\'40\' r=\'25\' fill=\'%23f97316\'/%3E%3C/svg%3E' }}" alt="Foord Logo" class="h-16">
            </div>

            <div class="flex flex-col md:flex-row">
                <!-- Sidebar -->
                <div class="w-full md:w-80 bg-gray-100 p-6 text-sm">
                    <div class="space-y-4">
                        @if(isset($revisionFund->data['sidebar']))
                            @foreach ($revisionFund->data['sidebar'] as $key => $value)
                            <div>
                                <h3 class="font-bold text-gray-700 mb-2">{{ strtoupper(implode(' ', preg_split('/(?=[A-Z])/', $key, -1, PREG_SPLIT_NO_EMPTY))) }}</h3>
                                @if (is_array($value))
                                    <div class="equity-indicator h-6 rounded mb-2"></div>
                                    <p class="text-xs">{{ $value['description'] ?? '' }}</p>
                                @else
                                    <p>{!! $value !!}</p>
                                @endif
                            </div>
                            @endforeach
                        @endif
                    </div>
                </div>

                <!-- Main Content -->
                <div class="flex-1 p-8">
                    <!-- Asset Allocation -->
                    @if(isset($revisionFund->data['mainContent']['assetAllocation']))
                    <div class="mb-8">
                        <h3 class="text-lg font-bold mb-4">{{ $revisionFund->data['mainContent']['assetAllocation']['title'] }}</h3>
                        <p class="text-sm text-gray-600 mb-2">{{ $revisionFund->data['mainContent']['assetAllocation']['subtitle'] }}</p>
                        <div class="overflow-x-auto">
                            <table class="w-full border-collapse">
                                <thead>
                                    <tr class="bg-gray-200">
                                        @foreach ($revisionFund->data['mainContent']['assetAllocation']['headers'] as $header)
                                            <th class="border border-gray-300 px-4 py-2 {{ $loop->first ? 'text-left' : 'text-center' }}">
                                                {!! $header !!}
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($revisionFund->data['mainContent']['assetAllocation']['rows'] as $row)
                                    <tr class="{{ $loop->odd ? 'bg-gray-50' : '' }}">
                                        <td class="border border-gray-300 px-4 py-2">{{ $row['name'] }}</td>
                                        <td class="border border-gray-300 px-4 py-2 text-center">{{ $row['sa'] }}</td>
                                        <td class="border border-gray-300 px-4 py-2 text-center">{{ $row['foreign'] }}</td>
                                        <td class="border border-gray-300 px-4 py-2 text-center">{{ $row['total'] }}</td>
                                        <td class="border border-gray-300 px-4 py-2 text-center">{{ $row['change'] }}</td>
                                    </tr>
                                    @endforeach
                                    <tr class="bg-red-600 text-white">
                                        <td class="border border-gray-300 px-4 py-2 font-bold">{{ $revisionFund->data['mainContent']['assetAllocation']['total']['name'] }}</td>
                                        <td class="border border-gray-300 px-4 py-2 text-center font-bold">{{ $revisionFund->data['mainContent']['assetAllocation']['total']['sa'] }}</td>
                                        <td class="border border-gray-300 px-4 py-2 text-center font-bold">{{ $revisionFund->data['mainContent']['assetAllocation']['total']['foreign'] }}</td>
                                        <td class="border border-gray-300 px-4 py-2 text-center font-bold">{{ $revisionFund->data['mainContent']['assetAllocation']['total']['total'] }}</td>
                                        <td class="border border-gray-300 px-4 py-2 text-center"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif

                    <!-- Top 10 Investments -->
                    @if(isset($revisionFund->data['mainContent']['topInvestments']))
                    <div class="mb-8">
                        <h3 class="text-lg font-bold mb-4">{{ $revisionFund->data['mainContent']['topInvestments']['title'] }}</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full border-collapse">
                                <thead>
                                    <tr class="bg-gray-200">
                                        @foreach ($revisionFund->data['mainContent']['topInvestments']['headers'] as $header)
                                            <th class="border border-gray-300 px-4 py-2 {{ $loop->first ? 'text-left' : 'text-center' }}">{{ $header }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($revisionFund->data['mainContent']['topInvestments']['rows'] as $row)
                                    <tr class="{{ $loop->odd ? 'bg-gray-50' : '' }}">
                                        <td class="border border-gray-300 px-4 py-2">{{ $row['security'] }}</td>
                                        <td class="border border-gray-300 px-4 py-2">{{ $row['assetClass'] }}</td>
                                        <td class="border border-gray-300 px-4 py-2 text-center">{{ $row['market'] }}</td>
                                        <td class="border border-gray-300 px-4 py-2 text-center">{{ $row['percentage'] }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif

                    <!-- Charts (Placeholder for revision view) -->
                    @if(isset($revisionFund->data['mainContent']['charts']))
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <h2 class="text-xl font-semibold mb-4">INVESTMENT STRATEGY VS SA INFLATION</h2>
                            <div class="bg-gray-100 h-64 flex items-center justify-center text-gray-500">
                                <span>📊 Chart data available in live version</span>
                            </div>
                        </div>
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <h2 class="text-xl font-semibold mb-4">PORTFOLIO PERFORMANCE VS BENCHMARK</h2>
                            <div class="bg-gray-100 h-64 flex items-center justify-center text-gray-500">
                                <span>📈 Chart data available in live version</span>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</body>
</html>