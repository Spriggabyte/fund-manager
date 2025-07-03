<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $data['fund']['name'] }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
        <!-- Page 2 -->
        <div class="bg-white mt-8 shadow-lg">
            <!-- Header -->
            <div class="bg-gray-800 text-white p-6 flex justify-between items-center">
                <div>
                    <div class="bg-red-500 text-white px-4 py-2 mb-4 inline-block font-semibold">{{ $data['fund']['date'] }}</div>
                    <h1 class="text-2xl font-bold">{{ $data['fund']['name'] }}</h1>
                    <p class="text-gray-300 text-sm mt-2 max-w-3xl">
                        {{ $data['fund']['description'] }}
                    </p>
                </div>
                <img src="{{ $data['fund']['logoUrl'] }}" alt="Foord Logo" class="h-16">
            </div>

            <div class="flex flex-col md:flex-row">
                <!-- Sidebar -->
                <div class="w-full md:w-80 bg-gray-100 p-6 text-sm">
                    <div class="space-y-4">
                        @foreach ($data['sidebar'] as $key => $value)
                        <div>
                            <h3 class="font-bold text-gray-700 mb-2">{{ strtoupper(implode(' ', preg_split('/(?=[A-Z])/', $key, -1, PREG_SPLIT_NO_EMPTY))) }}</h3>
                            @if (is_array($value))
                                <div class="equity-indicator h-6 rounded mb-2"></div>
                                <p class="text-xs">{{ $value['description'] }}</p>
                            @else
                                <p>{!! $value !!}</p>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Main Content -->
                <div class="flex-1 p-8">
                    <!-- Asset Allocation -->
                    <div class="mb-8">
                        <h3 class="text-lg font-bold mb-4">{{ $data['mainContent']['assetAllocation']['title'] }}</h3>
                        <p class="text-sm text-gray-600 mb-2">{{ $data['mainContent']['assetAllocation']['subtitle'] }}</p>
                        <div class="overflow-x-auto">
                            <table class="w-full border-collapse">
                                <thead>
                                    <tr class="bg-gray-200">
                                        @foreach ($data['mainContent']['assetAllocation']['headers'] as $header)
                                            <th class="border border-gray-300 px-4 py-2 {{ $loop->first ? 'text-left' : 'text-center' }}">{!! $header !!}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($data['mainContent']['assetAllocation']['rows'] as $row)
                                    <tr class="{{ $loop->odd ? 'bg-gray-50' : '' }}">
                                        <td class="border border-gray-300 px-4 py-2">{{ $row['name'] }}</td>
                                        <td class="border border-gray-300 px-4 py-2 text-center">{{ $row['sa'] }}</td>
                                        <td class="border border-gray-300 px-4 py-2 text-center">{{ $row['foreign'] }}</td>
                                        <td class="border border-gray-300 px-4 py-2 text-center">{{ $row['total'] }}</td>
                                        <td class="border border-gray-300 px-4 py-2 text-center">{{ $row['change'] }}</td>
                                    </tr>
                                    @endforeach
                                    <tr class="bg-red-600 text-white">
                                        <td class="border border-gray-300 px-4 py-2 font-bold">{{ $data['mainContent']['assetAllocation']['total']['name'] }}</td>
                                        <td class="border border-gray-300 px-4 py-2 text-center font-bold">{{ $data['mainContent']['assetAllocation']['total']['sa'] }}</td>
                                        <td class="border border-gray-300 px-4 py-2 text-center font-bold">{{ $data['mainContent']['assetAllocation']['total']['foreign'] }}</td>
                                        <td class="border border-gray-300 px-4 py-2 text-center font-bold">{{ $data['mainContent']['assetAllocation']['total']['total'] }}</td>
                                        <td class="border border-gray-300 px-4 py-2 text-center"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Top 10 Investments -->
                    <div class="mb-8">
                        <h3 class="text-lg font-bold mb-4">{{ $data['mainContent']['topInvestments']['title'] }}</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full border-collapse">
                                <thead>
                                    <tr class="bg-gray-200">
                                        @foreach ($data['mainContent']['topInvestments']['headers'] as $header)
                                            <th class="border border-gray-300 px-4 py-2 {{ $loop->first ? 'text-left' : 'text-center' }}">{{ $header }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($data['mainContent']['topInvestments']['rows'] as $row)
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

                    <!-- Charts -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div>
                            <h3 class="text-lg font-bold mb-4">{{ $data['mainContent']['charts']['investmentStrategy']['title'] }}</h3>
                            <div class="bg-gray-100 h-64 flex items-center justify-center text-gray-500 mb-2">
                                {{ $data['mainContent']['charts']['investmentStrategy']['chartPlaceholder'] }}
                            </div>
                            <p class="text-xs text-gray-600">
                                {{ $data['mainContent']['charts']['investmentStrategy']['description'] }}
                            </p>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold mb-4">{{ $data['mainContent']['charts']['portfolioPerformance']['title'] }}</h3>
                            <div class="bg-gray-100 h-64 flex items-center justify-center text-gray-500 mb-2">
                                {{ $data['mainContent']['charts']['portfolioPerformance']['chartPlaceholder'] }}
                            </div>
                        </div>
                    </div>

                    <!-- Performance Table -->
                    <div class="mb-8">
                        <h3 class="text-lg font-bold mb-4">{{ $data['mainContent']['performanceTable']['title'] }}</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full border-collapse text-sm">
                                <thead>
                                    <tr class="bg-gray-200">
                                        @foreach ($data['mainContent']['performanceTable']['headers'] as $header)
                                            <th class="border border-gray-300 px-3 py-2 {{ $loop->first ? 'text-left' : 'text-center' }}">{!! $header !!}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($data['mainContent']['performanceTable']['rows'] as $row)
                                    <tr class="{{ $loop->odd ? 'bg-gray-50' : '' }}">
                                        <td class="border border-gray-300 px-3 py-2">{!! $row['name'] !!}</td>
                                        <td class="border border-gray-300 px-3 py-2 text-center">{{ $row['cashValue'] }}</td>
                                        <td class="border border-gray-300 px-3 py-2 text-center">{{ $row['sinceInception'] }}</td>
                                        <td class="border border-gray-300 px-3 py-2 text-center">{{ $row['15yrs'] }}</td>
                                        <td class="border border-gray-300 px-3 py-2 text-center">{{ $row['10yrs'] }}</td>
                                        <td class="border border-gray-300 px-3 py-2 text-center">{{ $row['7yrs'] }}</td>
                                        <td class="border border-gray-300 px-3 py-2 text-center">{{ $row['5yrs'] }}</td>
                                        <td class="border border-gray-300 px-3 py-2 text-center">{{ $row['3yrs'] }}</td>
                                        <td class="border border-gray-300 px-3 py-2 text-center">{{ $row['1yr'] }}</td>
                                        <td class="border border-gray-300 px-3 py-2 text-center">{{ $row['thisMonth'] }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="text-xs text-gray-600 mt-2">
                            @foreach ($data['mainContent']['performanceTable']['footnotes'] as $footnote)
                                <p>{!! $footnote !!}</p>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Page 3 -->
        <div class="bg-white mt-8 shadow-lg">
            <div class="flex flex-col md:flex-row">
                <!-- Sidebar - Important Information for Investors -->
                <div class="w-full md:w-80 bg-gray-800 text-white p-6 text-xs">
                    <h2 class="text-lg font-bold mb-4">{{ $data['importantInfo']['title'] }}</h2>
                    <div class="space-y-3 text-gray-300">
                        @foreach ($data['importantInfo']['paragraphs'] as $paragraph)
                            <p>{{ $paragraph }}</p>
                        @endforeach
                        <p class="mt-4">{{ $data['importantInfo']['publishedDate'] }}</p>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="flex-1 p-8">
                    <!-- Fee Rates -->
                    <div class="mb-8">
                        <h3 class="text-lg font-bold mb-4">{{ $data['fees']['feeRates']['title'] }}</h3>
                        <div class="bg-gray-100 p-4 rounded">
                            <table class="w-full text-sm">
                                @foreach ($data['fees']['feeRates']['rates'] as $rate)
                                <tr>
                                    <td class="py-2">{{ $rate['name'] }}</td>
                                    <td class="text-right">{{ $rate['value'] }}</td>
                                </tr>
                                @endforeach
                                <tr>
                                    <td class="py-2 pt-4 font-semibold">{{ $data['fees']['feeRates']['globalFunds']['title'] }}</td>
                                    <td></td>
                                </tr>
                                @foreach ($data['fees']['feeRates']['globalFunds']['funds'] as $fund)
                                <tr>
                                    <td class="py-2">{{ $fund['name'] }}</td>
                                    <td class="text-right">{{ $fund['value'] }}</td>
                                </tr>
                                @endforeach
                            </table>
                        </div>
                        <p class="text-xs text-gray-600 mt-2">{{ $data['fees']['feeRates']['description'] }}</p>
                    </div>

                    <!-- Total Investment Charge -->
                    <div class="mb-8">
                        <h3 class="text-lg font-bold mb-4">{{ $data['fees']['totalInvestmentCharge']['title'] }}</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full border-collapse">
                                <thead>
                                    <tr class="bg-gray-200">
                                        @foreach ($data['fees']['totalInvestmentCharge']['headers'] as $header)
                                            <th class="border border-gray-300 px-4 py-2 {{ $loop->first ? 'text-left' : 'text-center' }}">{{ $header }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($data['fees']['totalInvestmentCharge']['rows'] as $row)
                                    <tr class="{{ $loop->odd ? 'bg-gray-50' : '' }}">
                                        <td class="border border-gray-300 px-4 py-2">{{ $row['name'] }}</td>
                                        <td class="border border-gray-300 px-4 py-2 text-center">{{ $row['12m'] }}</td>
                                        <td class="border border-gray-300 px-4 py-2 text-center">{{ $row['36m'] }}</td>
                                    </tr>
                                    @endforeach
                                    <tr class="bg-red-600 text-white">
                                        <td class="border border-gray-300 px-4 py-2 font-bold">{{ $data['fees']['totalInvestmentCharge']['total']['name'] }}</td>
                                        <td class="border border-gray-300 px-4 py-2 text-center font-bold">{{ $data['fees']['totalInvestmentCharge']['total']['12m'] }}</td>
                                        <td class="border border-gray-300 px-4 py-2 text-center font-bold">{{ $data['fees']['totalInvestmentCharge']['total']['36m'] }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <p class="text-xs text-gray-600 mt-4">{{ $data['fees']['totalInvestmentCharge']['description'] }}</p>
                    </div>
                    
                    <!-- Performance Fees -->
                    <div class="mb-8">
                        <h3 class="text-lg font-bold mb-4">{{ $data['fees']['performanceFees']['title'] }}</h3>
                        @foreach ($data['fees']['performanceFees']['paragraphs'] as $paragraph)
                            <p class="text-sm text-gray-600 mb-4">{{ $paragraph }}</p>
                        @endforeach
                    </div>

                    <!-- Performance Fee Examples -->
                    <div class="mb-8">
                        <h3 class="text-lg font-bold mb-4">{{ $data['fees']['performanceFeeExamples']['title'] }}</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full border-collapse">
                                <thead>
                                    <tr class="bg-gray-200">
                                        @foreach ($data['fees']['performanceFeeExamples']['headers'] as $header)
                                            <th class="border border-gray-300 px-4 py-2 {{ $loop->first ? 'text-left' : 'text-center' }}">{{ $header }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($data['fees']['performanceFeeExamples']['rows'] as $row)
                                    <tr class="{{ $loop->odd ? 'bg-gray-50' : '' }}">
                                        <td class="border border-gray-300 px-4 py-2">{{ $row['name'] }}</td>
                                        <td class="border border-gray-300 px-4 py-2 text-center">{{ $row['a'] }}</td>
                                        <td class="border border-gray-300 px-4 py-2 text-center">{{ $row['b'] }}</td>
                                        <td class="border border-gray-300 px-4 py-2 text-center">{{ $row['c'] }}</td>
                                        <td class="border border-gray-300 px-4 py-2 text-center">{{ $row['d'] }}</td>
                                    </tr>
                                    @endforeach
                                    <tr class="bg-red-600 text-white">
                                        <td class="border border-gray-300 px-4 py-2 font-bold">{{ $data['fees']['performanceFeeExamples']['total']['name'] }}</td>
                                        <td class="border border-gray-300 px-4 py-2 text-center font-bold">{{ $data['fees']['performanceFeeExamples']['total']['a'] }}</td>
                                        <td class="border border-gray-300 px-4 py-2 text-center font-bold">{{ $data['fees']['performanceFeeExamples']['total']['b'] }}</td>
                                        <td class="border border-gray-300 px-4 py-2 text-center font-bold">{{ $data['fees']['performanceFeeExamples']['total']['c'] }}</td>
                                        <td class="border border-gray-300 px-4 py-2 text-center font-bold">{!! $data['fees']['performanceFeeExamples']['total']['d'] !!}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <p class="text-xs text-gray-600 mt-2">{{ $data['fees']['performanceFeeExamples']['footnote'] }}</p>
                    </div>

                    <!-- Footer Information -->
                    <div class="mt-12 text-center">
                        <p class="text-sm text-gray-600 mb-4">{{ $data['footer']['info'] }}</p>
                        <p class="text-sm text-gray-600 mb-6">{{ $data['footer']['freeOfCharge'] }}</p>
                        <div class="text-sm">
                            <p>T. {{ $data['footer']['contact']['phone'] }}</p>
                            <p>E. {{ $data['footer']['contact']['email'] }}</p>
                            <p>{{ $data['footer']['contact']['website'] }}</p>
                        </div>
                        <img src="{{ $data['footer']['logoUrl'] }}" alt="Foord Logo" class="h-12 mx-auto mt-6">
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>