<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $fund->data['fund']['name'] ?? $fund->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        @media print {
            .no-print { display: none; }
        }
        .equity-indicator {
            background: linear-gradient(to right, #dc2626 0%, #dc2626 75%, #e5e7eb 75%, #e5e7eb 100%);
        }
        .editable {
            cursor: text;
            transition: all 0.2s;
            min-height: 1.25rem;
        }
        .editable:hover {
            background-color: #f3f4f6;
            outline: 1px solid #d1d5db;
            border-radius: 0.25rem;
            padding: 0.125rem 0.25rem;
        }
        .editing {
            background-color: #fef3c7;
            outline: 2px solid #f59e0b;
            border-radius: 0.25rem;
            padding: 0.125rem 0.25rem;
        }
        .edit-input {
            background: transparent;
            border: none;
            outline: none;
            width: 100%;
            font-family: inherit;
            font-size: inherit;
            font-weight: inherit;
            color: inherit;
        }
        .notification {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 50;
            transform: translateX(100%);
            transition: transform 0.3s ease-in-out;
        }
        .notification.show {
            transform: translateX(0);
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-900" x-data="fundEditor()">
    <!-- Success/Error Notification -->
    <div x-show="notification.show" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="transform translate-x-full"
         x-transition:enter-end="transform translate-x-0"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="transform translate-x-0"
         x-transition:leave-end="transform translate-x-full"
         class="notification fixed top-4 right-4 z-50 max-w-sm">
        <div class="bg-white rounded-lg shadow-lg border border-gray-200 p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg x-show="notification.type === 'success'" class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <svg x-show="notification.type === 'error'" class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium" :class="notification.type === 'success' ? 'text-green-800' : 'text-red-800'" x-text="notification.message"></p>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto bg-white shadow-lg">
        <!-- Edit Mode Toggle -->
        <div class="no-print bg-gray-800 text-white p-4 flex justify-between items-center">
            <div class="flex items-center space-x-4">
                <button @click="toggleEditMode()" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition duration-150 ease-in-out">
                    <span x-show="!editMode">Enable Edit Mode</span>
                    <span x-show="editMode">Disable Edit Mode</span>
                </button>
                <span x-show="editMode" class="text-yellow-300 text-sm">✏️ Edit mode active - Click any text to edit</span>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('funds.pdf', $fund) }}" 
                   class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition duration-150 ease-in-out flex items-center space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span>Export PDF</span>
                </a>
                <a href="{{ route('funds.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                    Back to Funds
                </a>
            </div>
        </div>

        <!-- Page 2 -->
        <div class="bg-white mt-8 shadow-lg">
            <!-- Header -->
            <div class="bg-gray-800 text-white p-6 flex justify-between items-center">
                <div>
                    <div class="bg-red-500 text-white px-4 py-2 mb-4 inline-block font-semibold">
                        <span x-data="editableField('fund.date', '{{ $fund->data['fund']['date'] ?? $fund->updated_at->format('d F Y') }}')" 
                              @click="editMode && startEdit()"
                              :class="editMode ? 'editable' : ''"
                              x-text="value"></span>
                    </div>
                    <h1 class="text-2xl font-bold">
                        <span x-data="editableField('fund.name', '{{ $fund->data['fund']['name'] ?? $fund->name }}')" 
                              @click="editMode && startEdit()"
                              :class="editMode ? 'editable' : ''"
                              x-text="value"></span>
                    </h1>
                    <p class="text-gray-300 text-sm mt-2 max-w-3xl">
                        <span x-data="editableField('fund.description', '{{ $fund->data['fund']['description'] ?? '' }}')" 
                              @click="editMode && startEdit()"
                              :class="editMode ? 'editable' : ''"
                              x-text="value"></span>
                    </p>
                </div>
                <img src="{{ $fund->data['fund']['logoUrl'] ?? 'data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 200 80\'%3E%3Ctext x=\'10\' y=\'50\' font-family=\'Arial\' font-size=\'40\' font-weight=\'bold\' fill=\'white\'%3EFOORD%3C/text%3E%3Ccircle cx=\'170\' cy=\'40\' r=\'25\' fill=\'%23f97316\'/%3E%3C/svg%3E' }}" alt="Foord Logo" class="h-16">
            </div>

            <div class="flex flex-col md:flex-row">
                <!-- Sidebar -->
                <div class="w-full md:w-80 bg-gray-100 p-6 text-sm">
                    <div class="space-y-4">
                        @if(isset($fund->data['sidebar']))
                            @foreach ($fund->data['sidebar'] as $key => $value)
                            <div>
                                <h3 class="font-bold text-gray-700 mb-2">{{ strtoupper(implode(' ', preg_split('/(?=[A-Z])/', $key, -1, PREG_SPLIT_NO_EMPTY))) }}</h3>
                                @if (is_array($value))
                                    <div class="equity-indicator h-6 rounded mb-2"></div>
                                    <p class="text-xs">
                                        <span x-data="editableField('sidebar.{{ $key }}.description', '{{ $value['description'] ?? '' }}')" 
                                              @click="editMode && startEdit()"
                                              :class="editMode ? 'editable' : ''"
                                              x-text="value"></span>
                                    </p>
                                @else
                                    <p>
                                        <span x-data="editableField('sidebar.{{ $key }}', '{!! addslashes($value) !!}')" 
                                              @click="editMode && startEdit()"
                                              :class="editMode ? 'editable' : ''"
                                              x-html="value"></span>
                                    </p>
                                @endif
                            </div>
                            @endforeach
                        @endif
                    </div>
                </div>

                <!-- Main Content -->
                <div class="flex-1 p-8">
                    <!-- Asset Allocation -->
                    @if(isset($fund->data['mainContent']['assetAllocation']))
                    <div class="mb-8">
                        <h3 class="text-lg font-bold mb-4">
                            <span x-data="editableField('mainContent.assetAllocation.title', '{{ $fund->data['mainContent']['assetAllocation']['title'] }}')" 
                                  @click="editMode && startEdit()"
                                  :class="editMode ? 'editable' : ''"
                                  x-text="value"></span>
                        </h3>
                        <p class="text-sm text-gray-600 mb-2">
                            <span x-data="editableField('mainContent.assetAllocation.subtitle', '{{ $fund->data['mainContent']['assetAllocation']['subtitle'] }}')" 
                                  @click="editMode && startEdit()"
                                  :class="editMode ? 'editable' : ''"
                                  x-text="value"></span>
                        </p>
                        <div class="overflow-x-auto">
                            <table class="w-full border-collapse">
                                <thead>
                                    <tr class="bg-gray-200">
                                        @foreach ($fund->data['mainContent']['assetAllocation']['headers'] as $index => $header)
                                            <th class="border border-gray-300 px-4 py-2 {{ $loop->first ? 'text-left' : 'text-center' }}">
                                                <span x-data="editableField('mainContent.assetAllocation.headers.{{ $index }}', '{!! addslashes($header) !!}')" 
                                                      @click="editMode && startEdit()"
                                                      :class="editMode ? 'editable' : ''"
                                                      x-html="value"></span>
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($fund->data['mainContent']['assetAllocation']['rows'] as $rowIndex => $row)
                                    <tr class="{{ $loop->odd ? 'bg-gray-50' : '' }}">
                                        <td class="border border-gray-300 px-4 py-2">
                                            <span x-data="editableField('mainContent.assetAllocation.rows.{{ $rowIndex }}.name', '{{ $row['name'] }}')" 
                                                  @click="editMode && startEdit()"
                                                  :class="editMode ? 'editable' : ''"
                                                  x-text="value"></span>
                                        </td>
                                        <td class="border border-gray-300 px-4 py-2 text-center">
                                            <span x-data="editableField('mainContent.assetAllocation.rows.{{ $rowIndex }}.sa', '{{ $row['sa'] }}')" 
                                                  @click="editMode && startEdit()"
                                                  :class="editMode ? 'editable' : ''"
                                                  x-text="value"></span>
                                        </td>
                                        <td class="border border-gray-300 px-4 py-2 text-center">
                                            <span x-data="editableField('mainContent.assetAllocation.rows.{{ $rowIndex }}.foreign', '{{ $row['foreign'] }}')" 
                                                  @click="editMode && startEdit()"
                                                  :class="editMode ? 'editable' : ''"
                                                  x-text="value"></span>
                                        </td>
                                        <td class="border border-gray-300 px-4 py-2 text-center">
                                            <span x-data="editableField('mainContent.assetAllocation.rows.{{ $rowIndex }}.total', '{{ $row['total'] }}')" 
                                                  @click="editMode && startEdit()"
                                                  :class="editMode ? 'editable' : ''"
                                                  x-text="value"></span>
                                        </td>
                                        <td class="border border-gray-300 px-4 py-2 text-center">
                                            <span x-data="editableField('mainContent.assetAllocation.rows.{{ $rowIndex }}.change', '{{ $row['change'] }}')" 
                                                  @click="editMode && startEdit()"
                                                  :class="editMode ? 'editable' : ''"
                                                  x-text="value"></span>
                                        </td>
                                    </tr>
                                    @endforeach
                                    <tr class="bg-red-600 text-white">
                                        <td class="border border-gray-300 px-4 py-2 font-bold">
                                            <span x-data="editableField('mainContent.assetAllocation.total.name', '{{ $fund->data['mainContent']['assetAllocation']['total']['name'] }}')" 
                                                  @click="editMode && startEdit()"
                                                  :class="editMode ? 'editable' : ''"
                                                  x-text="value"></span>
                                        </td>
                                        <td class="border border-gray-300 px-4 py-2 text-center font-bold">
                                            <span x-data="editableField('mainContent.assetAllocation.total.sa', '{{ $fund->data['mainContent']['assetAllocation']['total']['sa'] }}')" 
                                                  @click="editMode && startEdit()"
                                                  :class="editMode ? 'editable' : ''"
                                                  x-text="value"></span>
                                        </td>
                                        <td class="border border-gray-300 px-4 py-2 text-center font-bold">
                                            <span x-data="editableField('mainContent.assetAllocation.total.foreign', '{{ $fund->data['mainContent']['assetAllocation']['total']['foreign'] }}')" 
                                                  @click="editMode && startEdit()"
                                                  :class="editMode ? 'editable' : ''"
                                                  x-text="value"></span>
                                        </td>
                                        <td class="border border-gray-300 px-4 py-2 text-center font-bold">
                                            <span x-data="editableField('mainContent.assetAllocation.total.total', '{{ $fund->data['mainContent']['assetAllocation']['total']['total'] }}')" 
                                                  @click="editMode && startEdit()"
                                                  :class="editMode ? 'editable' : ''"
                                                  x-text="value"></span>
                                        </td>
                                        <td class="border border-gray-300 px-4 py-2 text-center"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif

                    <!-- Top 10 Investments -->
                    @if(isset($fund->data['mainContent']['topInvestments']))
                    <div class="mb-8">
                        <h3 class="text-lg font-bold mb-4">
                            <span x-data="editableField('mainContent.topInvestments.title', '{{ $fund->data['mainContent']['topInvestments']['title'] }}')" 
                                  @click="editMode && startEdit()"
                                  :class="editMode ? 'editable' : ''"
                                  x-text="value"></span>
                        </h3>
                        <div class="overflow-x-auto">
                            <table class="w-full border-collapse">
                                <thead>
                                    <tr class="bg-gray-200">
                                        @foreach ($fund->data['mainContent']['topInvestments']['headers'] as $index => $header)
                                            <th class="border border-gray-300 px-4 py-2 {{ $loop->first ? 'text-left' : 'text-center' }}">
                                                <span x-data="editableField('mainContent.topInvestments.headers.{{ $index }}', '{{ $header }}')" 
                                                      @click="editMode && startEdit()"
                                                      :class="editMode ? 'editable' : ''"
                                                      x-text="value"></span>
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($fund->data['mainContent']['topInvestments']['rows'] as $rowIndex => $row)
                                    <tr class="{{ $loop->odd ? 'bg-gray-50' : '' }}">
                                        <td class="border border-gray-300 px-4 py-2">
                                            <span x-data="editableField('mainContent.topInvestments.rows.{{ $rowIndex }}.security', '{{ $row['security'] }}')" 
                                                  @click="editMode && startEdit()"
                                                  :class="editMode ? 'editable' : ''"
                                                  x-text="value"></span>
                                        </td>
                                        <td class="border border-gray-300 px-4 py-2">
                                            <span x-data="editableField('mainContent.topInvestments.rows.{{ $rowIndex }}.assetClass', '{{ $row['assetClass'] }}')" 
                                                  @click="editMode && startEdit()"
                                                  :class="editMode ? 'editable' : ''"
                                                  x-text="value"></span>
                                        </td>
                                        <td class="border border-gray-300 px-4 py-2 text-center">
                                            <span x-data="editableField('mainContent.topInvestments.rows.{{ $rowIndex }}.market', '{{ $row['market'] }}')" 
                                                  @click="editMode && startEdit()"
                                                  :class="editMode ? 'editable' : ''"
                                                  x-text="value"></span>
                                        </td>
                                        <td class="border border-gray-300 px-4 py-2 text-center">
                                            <span x-data="editableField('mainContent.topInvestments.rows.{{ $rowIndex }}.percentage', '{{ $row['percentage'] }}')" 
                                                  @click="editMode && startEdit()"
                                                  :class="editMode ? 'editable' : ''"
                                                  x-text="value"></span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif

                    <!-- Charts -->
                    @if(isset($fund->data['mainContent']['charts']))
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div>
                            <h3 class="text-lg font-bold mb-4">
                                <span x-data="editableField('mainContent.charts.investmentStrategy.title', '{{ $fund->data['mainContent']['charts']['investmentStrategy']['title'] }}')" 
                                      @click="editMode && startEdit()"
                                      :class="editMode ? 'editable' : ''"
                                      x-text="value"></span>
                            </h3>
                            <div class="bg-gray-100 h-64 flex items-center justify-center text-gray-500 mb-2">
                                <span x-data="editableField('mainContent.charts.investmentStrategy.chartPlaceholder', '{{ $fund->data['mainContent']['charts']['investmentStrategy']['chartPlaceholder'] }}')" 
                                      @click="editMode && startEdit()"
                                      :class="editMode ? 'editable' : ''"
                                      x-text="value"></span>
                            </div>
                            <p class="text-xs text-gray-600">
                                <span x-data="editableField('mainContent.charts.investmentStrategy.description', '{{ $fund->data['mainContent']['charts']['investmentStrategy']['description'] }}')" 
                                      @click="editMode && startEdit()"
                                      :class="editMode ? 'editable' : ''"
                                      x-text="value"></span>
                            </p>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold mb-4">
                                <span x-data="editableField('mainContent.charts.portfolioPerformance.title', '{{ $fund->data['mainContent']['charts']['portfolioPerformance']['title'] }}')" 
                                      @click="editMode && startEdit()"
                                      :class="editMode ? 'editable' : ''"
                                      x-text="value"></span>
                            </h3>
                            <div class="bg-gray-100 h-64 flex items-center justify-center text-gray-500 mb-2">
                                <span x-data="editableField('mainContent.charts.portfolioPerformance.chartPlaceholder', '{{ $fund->data['mainContent']['charts']['portfolioPerformance']['chartPlaceholder'] }}')" 
                                      @click="editMode && startEdit()"
                                      :class="editMode ? 'editable' : ''"
                                      x-text="value"></span>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Performance Table -->
                    @if(isset($fund->data['mainContent']['performanceTable']))
                    <div class="mb-8">
                        <h3 class="text-lg font-bold mb-4">
                            <span x-data="editableField('mainContent.performanceTable.title', '{{ $fund->data['mainContent']['performanceTable']['title'] }}')" 
                                  @click="editMode && startEdit()"
                                  :class="editMode ? 'editable' : ''"
                                  x-text="value"></span>
                        </h3>
                        <div class="overflow-x-auto">
                            <table class="w-full border-collapse text-sm">
                                <thead>
                                    <tr class="bg-gray-200">
                                        @foreach ($fund->data['mainContent']['performanceTable']['headers'] as $index => $header)
                                            <th class="border border-gray-300 px-3 py-2 {{ $loop->first ? 'text-left' : 'text-center' }}">
                                                <span x-data="editableField('mainContent.performanceTable.headers.{{ $index }}', '{!! addslashes($header) !!}')" 
                                                      @click="editMode && startEdit()"
                                                      :class="editMode ? 'editable' : ''"
                                                      x-html="value"></span>
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($fund->data['mainContent']['performanceTable']['rows'] as $rowIndex => $row)
                                    <tr class="{{ $loop->odd ? 'bg-gray-50' : '' }}">
                                        <td class="border border-gray-300 px-3 py-2">
                                            <span x-data="editableField('mainContent.performanceTable.rows.{{ $rowIndex }}.name', '{!! addslashes($row['name']) !!}')" 
                                                  @click="editMode && startEdit()"
                                                  :class="editMode ? 'editable' : ''"
                                                  x-html="value"></span>
                                        </td>
                                        <td class="border border-gray-300 px-3 py-2 text-center">
                                            <span x-data="editableField('mainContent.performanceTable.rows.{{ $rowIndex }}.cashValue', '{{ $row['cashValue'] }}')" 
                                                  @click="editMode && startEdit()"
                                                  :class="editMode ? 'editable' : ''"
                                                  x-text="value"></span>
                                        </td>
                                        <td class="border border-gray-300 px-3 py-2 text-center">
                                            <span x-data="editableField('mainContent.performanceTable.rows.{{ $rowIndex }}.sinceInception', '{{ $row['sinceInception'] }}')" 
                                                  @click="editMode && startEdit()"
                                                  :class="editMode ? 'editable' : ''"
                                                  x-text="value"></span>
                                        </td>
                                        <td class="border border-gray-300 px-3 py-2 text-center">
                                            <span x-data="editableField('mainContent.performanceTable.rows.{{ $rowIndex }}.15yrs', '{{ $row['15yrs'] }}')" 
                                                  @click="editMode && startEdit()"
                                                  :class="editMode ? 'editable' : ''"
                                                  x-text="value"></span>
                                        </td>
                                        <td class="border border-gray-300 px-3 py-2 text-center">
                                            <span x-data="editableField('mainContent.performanceTable.rows.{{ $rowIndex }}.10yrs', '{{ $row['10yrs'] }}')" 
                                                  @click="editMode && startEdit()"
                                                  :class="editMode ? 'editable' : ''"
                                                  x-text="value"></span>
                                        </td>
                                        <td class="border border-gray-300 px-3 py-2 text-center">
                                            <span x-data="editableField('mainContent.performanceTable.rows.{{ $rowIndex }}.7yrs', '{{ $row['7yrs'] }}')" 
                                                  @click="editMode && startEdit()"
                                                  :class="editMode ? 'editable' : ''"
                                                  x-text="value"></span>
                                        </td>
                                        <td class="border border-gray-300 px-3 py-2 text-center">
                                            <span x-data="editableField('mainContent.performanceTable.rows.{{ $rowIndex }}.5yrs', '{{ $row['5yrs'] }}')" 
                                                  @click="editMode && startEdit()"
                                                  :class="editMode ? 'editable' : ''"
                                                  x-text="value"></span>
                                        </td>
                                        <td class="border border-gray-300 px-3 py-2 text-center">
                                            <span x-data="editableField('mainContent.performanceTable.rows.{{ $rowIndex }}.3yrs', '{{ $row['3yrs'] }}')" 
                                                  @click="editMode && startEdit()"
                                                  :class="editMode ? 'editable' : ''"
                                                  x-text="value"></span>
                                        </td>
                                        <td class="border border-gray-300 px-3 py-2 text-center">
                                            <span x-data="editableField('mainContent.performanceTable.rows.{{ $rowIndex }}.1yr', '{{ $row['1yr'] }}')" 
                                                  @click="editMode && startEdit()"
                                                  :class="editMode ? 'editable' : ''"
                                                  x-text="value"></span>
                                        </td>
                                        <td class="border border-gray-300 px-3 py-2 text-center">
                                            <span x-data="editableField('mainContent.performanceTable.rows.{{ $rowIndex }}.thisMonth', '{{ $row['thisMonth'] }}')" 
                                                  @click="editMode && startEdit()"
                                                  :class="editMode ? 'editable' : ''"
                                                  x-text="value"></span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="text-xs text-gray-600 mt-2">
                            @if(isset($fund->data['mainContent']['performanceTable']['footnotes']))
                                @foreach ($fund->data['mainContent']['performanceTable']['footnotes'] as $index => $footnote)
                                    <p>
                                        <span x-data="editableField('mainContent.performanceTable.footnotes.{{ $index }}', '{!! addslashes($footnote) !!}')" 
                                              @click="editMode && startEdit()"
                                              :class="editMode ? 'editable' : ''"
                                              x-html="value"></span>
                                    </p>
                                @endforeach
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Page 3 -->
        <div class="bg-white mt-8 shadow-lg">
            <div class="flex flex-col md:flex-row">
                <!-- Sidebar - Important Information for Investors -->
                @if(isset($fund->data['importantInfo']))
                <div class="w-full md:w-80 bg-gray-800 text-white p-6 text-xs">
                    <h2 class="text-lg font-bold mb-4">
                        <span x-data="editableField('importantInfo.title', '{{ $fund->data['importantInfo']['title'] }}')" 
                              @click="editMode && startEdit()"
                              :class="editMode ? 'editable' : ''"
                              x-text="value"></span>
                    </h2>
                    <div class="space-y-3 text-gray-300">
                        @foreach ($fund->data['importantInfo']['paragraphs'] as $index => $paragraph)
                            <p>
                                <span x-data="editableField('importantInfo.paragraphs.{{ $index }}', '{{ addslashes($paragraph) }}')" 
                                      @click="editMode && startEdit()"
                                      :class="editMode ? 'editable' : ''"
                                      x-text="value"></span>
                            </p>
                        @endforeach
                        <p class="mt-4">
                            <span x-data="editableField('importantInfo.publishedDate', '{{ $fund->data['importantInfo']['publishedDate'] }}')" 
                                  @click="editMode && startEdit()"
                                  :class="editMode ? 'editable' : ''"
                                  x-text="value"></span>
                        </p>
                    </div>
                </div>
                @endif

                <!-- Main Content -->
                <div class="flex-1 p-8">
                    <!-- Fee Rates -->
                    @if(isset($fund->data['fees']['feeRates']))
                    <div class="mb-8">
                        <h3 class="text-lg font-bold mb-4">
                            <span x-data="editableField('fees.feeRates.title', '{{ $fund->data['fees']['feeRates']['title'] }}')" 
                                  @click="editMode && startEdit()"
                                  :class="editMode ? 'editable' : ''"
                                  x-text="value"></span>
                        </h3>
                        <div class="bg-gray-100 p-4 rounded">
                            <table class="w-full text-sm">
                                @foreach ($fund->data['fees']['feeRates']['rates'] as $index => $rate)
                                <tr>
                                    <td class="py-2">
                                        <span x-data="editableField('fees.feeRates.rates.{{ $index }}.name', '{{ $rate['name'] }}')" 
                                              @click="editMode && startEdit()"
                                              :class="editMode ? 'editable' : ''"
                                              x-text="value"></span>
                                    </td>
                                    <td class="text-right">
                                        <span x-data="editableField('fees.feeRates.rates.{{ $index }}.value', '{{ $rate['value'] }}')" 
                                              @click="editMode && startEdit()"
                                              :class="editMode ? 'editable' : ''"
                                              x-text="value"></span>
                                    </td>
                                </tr>
                                @endforeach
                                <tr>
                                    <td class="py-2 pt-4 font-semibold">
                                        <span x-data="editableField('fees.feeRates.globalFunds.title', '{{ $fund->data['fees']['feeRates']['globalFunds']['title'] }}')" 
                                              @click="editMode && startEdit()"
                                              :class="editMode ? 'editable' : ''"
                                              x-text="value"></span>
                                    </td>
                                    <td></td>
                                </tr>
                                @foreach ($fund->data['fees']['feeRates']['globalFunds']['funds'] as $index => $fund_item)
                                <tr>
                                    <td class="py-2">
                                        <span x-data="editableField('fees.feeRates.globalFunds.funds.{{ $index }}.name', '{{ $fund_item['name'] }}')" 
                                              @click="editMode && startEdit()"
                                              :class="editMode ? 'editable' : ''"
                                              x-text="value"></span>
                                    </td>
                                    <td class="text-right">
                                        <span x-data="editableField('fees.feeRates.globalFunds.funds.{{ $index }}.value', '{{ $fund_item['value'] }}')" 
                                              @click="editMode && startEdit()"
                                              :class="editMode ? 'editable' : ''"
                                              x-text="value"></span>
                                    </td>
                                </tr>
                                @endforeach
                            </table>
                        </div>
                        <p class="text-xs text-gray-600 mt-2">
                            <span x-data="editableField('fees.feeRates.description', '{{ $fund->data['fees']['feeRates']['description'] }}')" 
                                  @click="editMode && startEdit()"
                                  :class="editMode ? 'editable' : ''"
                                  x-text="value"></span>
                        </p>
                    </div>
                    @endif

                    <!-- Total Investment Charge -->
                    @if(isset($fund->data['fees']['totalInvestmentCharge']))
                    <div class="mb-8">
                        <h3 class="text-lg font-bold mb-4">
                            <span x-data="editableField('fees.totalInvestmentCharge.title', '{{ $fund->data['fees']['totalInvestmentCharge']['title'] }}')" 
                                  @click="editMode && startEdit()"
                                  :class="editMode ? 'editable' : ''"
                                  x-text="value"></span>
                        </h3>
                        <div class="overflow-x-auto">
                            <table class="w-full border-collapse">
                                <thead>
                                    <tr class="bg-gray-200">
                                        @foreach ($fund->data['fees']['totalInvestmentCharge']['headers'] as $index => $header)
                                            <th class="border border-gray-300 px-4 py-2 {{ $loop->first ? 'text-left' : 'text-center' }}">
                                                <span x-data="editableField('fees.totalInvestmentCharge.headers.{{ $index }}', '{{ $header }}')" 
                                                      @click="editMode && startEdit()"
                                                      :class="editMode ? 'editable' : ''"
                                                      x-text="value"></span>
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($fund->data['fees']['totalInvestmentCharge']['rows'] as $rowIndex => $row)
                                    <tr class="{{ $loop->odd ? 'bg-gray-50' : '' }}">
                                        <td class="border border-gray-300 px-4 py-2">
                                            <span x-data="editableField('fees.totalInvestmentCharge.rows.{{ $rowIndex }}.name', '{{ $row['name'] }}')" 
                                                  @click="editMode && startEdit()"
                                                  :class="editMode ? 'editable' : ''"
                                                  x-text="value"></span>
                                        </td>
                                        <td class="border border-gray-300 px-4 py-2 text-center">
                                            <span x-data="editableField('fees.totalInvestmentCharge.rows.{{ $rowIndex }}.12m', '{{ $row['12m'] }}')" 
                                                  @click="editMode && startEdit()"
                                                  :class="editMode ? 'editable' : ''"
                                                  x-text="value"></span>
                                        </td>
                                        <td class="border border-gray-300 px-4 py-2 text-center">
                                            <span x-data="editableField('fees.totalInvestmentCharge.rows.{{ $rowIndex }}.36m', '{{ $row['36m'] }}')" 
                                                  @click="editMode && startEdit()"
                                                  :class="editMode ? 'editable' : ''"
                                                  x-text="value"></span>
                                        </td>
                                    </tr>
                                    @endforeach
                                    <tr class="bg-red-600 text-white">
                                        <td class="border border-gray-300 px-4 py-2 font-bold">
                                            <span x-data="editableField('fees.totalInvestmentCharge.total.name', '{{ $fund->data['fees']['totalInvestmentCharge']['total']['name'] }}')" 
                                                  @click="editMode && startEdit()"
                                                  :class="editMode ? 'editable' : ''"
                                                  x-text="value"></span>
                                        </td>
                                        <td class="border border-gray-300 px-4 py-2 text-center font-bold">
                                            <span x-data="editableField('fees.totalInvestmentCharge.total.12m', '{{ $fund->data['fees']['totalInvestmentCharge']['total']['12m'] }}')" 
                                                  @click="editMode && startEdit()"
                                                  :class="editMode ? 'editable' : ''"
                                                  x-text="value"></span>
                                        </td>
                                        <td class="border border-gray-300 px-4 py-2 text-center font-bold">
                                            <span x-data="editableField('fees.totalInvestmentCharge.total.36m', '{{ $fund->data['fees']['totalInvestmentCharge']['total']['36m'] }}')" 
                                                  @click="editMode && startEdit()"
                                                  :class="editMode ? 'editable' : ''"
                                                  x-text="value"></span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <p class="text-xs text-gray-600 mt-4">
                            <span x-data="editableField('fees.totalInvestmentCharge.description', '{{ $fund->data['fees']['totalInvestmentCharge']['description'] }}')" 
                                  @click="editMode && startEdit()"
                                  :class="editMode ? 'editable' : ''"
                                  x-text="value"></span>
                        </p>
                    </div>
                    @endif
                    
                    <!-- Performance Fees -->
                    @if(isset($fund->data['fees']['performanceFees']))
                    <div class="mb-8">
                        <h3 class="text-lg font-bold mb-4">
                            <span x-data="editableField('fees.performanceFees.title', '{{ $fund->data['fees']['performanceFees']['title'] }}')" 
                                  @click="editMode && startEdit()"
                                  :class="editMode ? 'editable' : ''"
                                  x-text="value"></span>
                        </h3>
                        @foreach ($fund->data['fees']['performanceFees']['paragraphs'] as $index => $paragraph)
                            <p class="text-sm text-gray-600 mb-4">
                                <span x-data="editableField('fees.performanceFees.paragraphs.{{ $index }}', '{{ addslashes($paragraph) }}')" 
                                      @click="editMode && startEdit()"
                                      :class="editMode ? 'editable' : ''"
                                      x-text="value"></span>
                            </p>
                        @endforeach
                    </div>
                    @endif

                    <!-- Performance Fee Examples -->
                    @if(isset($fund->data['fees']['performanceFeeExamples']))
                    <div class="mb-8">
                        <h3 class="text-lg font-bold mb-4">
                            <span x-data="editableField('fees.performanceFeeExamples.title', '{{ $fund->data['fees']['performanceFeeExamples']['title'] }}')" 
                                  @click="editMode && startEdit()"
                                  :class="editMode ? 'editable' : ''"
                                  x-text="value"></span>
                        </h3>
                        <div class="overflow-x-auto">
                            <table class="w-full border-collapse">
                                <thead>
                                    <tr class="bg-gray-200">
                                        @foreach ($fund->data['fees']['performanceFeeExamples']['headers'] as $index => $header)
                                            <th class="border border-gray-300 px-4 py-2 {{ $loop->first ? 'text-left' : 'text-center' }}">
                                                <span x-data="editableField('fees.performanceFeeExamples.headers.{{ $index }}', '{{ $header }}')" 
                                                      @click="editMode && startEdit()"
                                                      :class="editMode ? 'editable' : ''"
                                                      x-text="value"></span>
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($fund->data['fees']['performanceFeeExamples']['rows'] as $rowIndex => $row)
                                    <tr class="{{ $loop->odd ? 'bg-gray-50' : '' }}">
                                        <td class="border border-gray-300 px-4 py-2">
                                            <span x-data="editableField('fees.performanceFeeExamples.rows.{{ $rowIndex }}.name', '{{ $row['name'] }}')" 
                                                  @click="editMode && startEdit()"
                                                  :class="editMode ? 'editable' : ''"
                                                  x-text="value"></span>
                                        </td>
                                        <td class="border border-gray-300 px-4 py-2 text-center">
                                            <span x-data="editableField('fees.performanceFeeExamples.rows.{{ $rowIndex }}.a', '{{ $row['a'] }}')" 
                                                  @click="editMode && startEdit()"
                                                  :class="editMode ? 'editable' : ''"
                                                  x-text="value"></span>
                                        </td>
                                        <td class="border border-gray-300 px-4 py-2 text-center">
                                            <span x-data="editableField('fees.performanceFeeExamples.rows.{{ $rowIndex }}.b', '{{ $row['b'] }}')" 
                                                  @click="editMode && startEdit()"
                                                  :class="editMode ? 'editable' : ''"
                                                  x-text="value"></span>
                                        </td>
                                        <td class="border border-gray-300 px-4 py-2 text-center">
                                            <span x-data="editableField('fees.performanceFeeExamples.rows.{{ $rowIndex }}.c', '{{ $row['c'] }}')" 
                                                  @click="editMode && startEdit()"
                                                  :class="editMode ? 'editable' : ''"
                                                  x-text="value"></span>
                                        </td>
                                        <td class="border border-gray-300 px-4 py-2 text-center">
                                            <span x-data="editableField('fees.performanceFeeExamples.rows.{{ $rowIndex }}.d', '{{ $row['d'] }}')" 
                                                  @click="editMode && startEdit()"
                                                  :class="editMode ? 'editable' : ''"
                                                  x-text="value"></span>
                                        </td>
                                    </tr>
                                    @endforeach
                                    <tr class="bg-red-600 text-white">
                                        <td class="border border-gray-300 px-4 py-2 font-bold">
                                            <span x-data="editableField('fees.performanceFeeExamples.total.name', '{{ $fund->data['fees']['performanceFeeExamples']['total']['name'] }}')" 
                                                  @click="editMode && startEdit()"
                                                  :class="editMode ? 'editable' : ''"
                                                  x-text="value"></span>
                                        </td>
                                        <td class="border border-gray-300 px-4 py-2 text-center font-bold">
                                            <span x-data="editableField('fees.performanceFeeExamples.total.a', '{{ $fund->data['fees']['performanceFeeExamples']['total']['a'] }}')" 
                                                  @click="editMode && startEdit()"
                                                  :class="editMode ? 'editable' : ''"
                                                  x-text="value"></span>
                                        </td>
                                        <td class="border border-gray-300 px-4 py-2 text-center font-bold">
                                            <span x-data="editableField('fees.performanceFeeExamples.total.b', '{{ $fund->data['fees']['performanceFeeExamples']['total']['b'] }}')" 
                                                  @click="editMode && startEdit()"
                                                  :class="editMode ? 'editable' : ''"
                                                  x-text="value"></span>
                                        </td>
                                        <td class="border border-gray-300 px-4 py-2 text-center font-bold">
                                            <span x-data="editableField('fees.performanceFeeExamples.total.c', '{{ $fund->data['fees']['performanceFeeExamples']['total']['c'] }}')" 
                                                  @click="editMode && startEdit()"
                                                  :class="editMode ? 'editable' : ''"
                                                  x-text="value"></span>
                                        </td>
                                        <td class="border border-gray-300 px-4 py-2 text-center font-bold">
                                            <span x-data="editableField('fees.performanceFeeExamples.total.d', '{!! addslashes($fund->data['fees']['performanceFeeExamples']['total']['d']) !!}')" 
                                                  @click="editMode && startEdit()"
                                                  :class="editMode ? 'editable' : ''"
                                                  x-html="value"></span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <p class="text-xs text-gray-600 mt-2">
                            <span x-data="editableField('fees.performanceFeeExamples.footnote', '{{ $fund->data['fees']['performanceFeeExamples']['footnote'] }}')" 
                                  @click="editMode && startEdit()"
                                  :class="editMode ? 'editable' : ''"
                                  x-text="value"></span>
                        </p>
                    </div>
                    @endif

                    <!-- Footer Information -->
                    @if(isset($fund->data['footer']))
                    <div class="mt-12 text-center">
                        <p class="text-sm text-gray-600 mb-4">
                            <span x-data="editableField('footer.info', '{{ $fund->data['footer']['info'] }}')" 
                                  @click="editMode && startEdit()"
                                  :class="editMode ? 'editable' : ''"
                                  x-text="value"></span>
                        </p>
                        <p class="text-sm text-gray-600 mb-6">
                            <span x-data="editableField('footer.freeOfCharge', '{{ $fund->data['footer']['freeOfCharge'] }}')" 
                                  @click="editMode && startEdit()"
                                  :class="editMode ? 'editable' : ''"
                                  x-text="value"></span>
                        </p>
                        <div class="text-sm">
                            <p>T. <span x-data="editableField('footer.contact.phone', '{{ $fund->data['footer']['contact']['phone'] }}')" 
                                      @click="editMode && startEdit()"
                                      :class="editMode ? 'editable' : ''"
                                      x-text="value"></span></p>
                            <p>E. <span x-data="editableField('footer.contact.email', '{{ $fund->data['footer']['contact']['email'] }}')" 
                                      @click="editMode && startEdit()"
                                      :class="editMode ? 'editable' : ''"
                                      x-text="value"></span></p>
                            <p><span x-data="editableField('footer.contact.website', '{{ $fund->data['footer']['contact']['website'] }}')" 
                                   @click="editMode && startEdit()"
                                   :class="editMode ? 'editable' : ''"
                                   x-text="value"></span></p>
                        </div>
                        <img src="{{ $fund->data['footer']['logoUrl'] }}" alt="Foord Logo" class="h-12 mx-auto mt-6">
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        // Global variables for better communication between components
        let globalFundEditor = null;

        function fundEditor() {
            return {
                editMode: false,
                notification: {
                    show: false,
                    type: 'success',
                    message: ''
                },

                init() {
                    globalFundEditor = this;
                },

                toggleEditMode() {
                    this.editMode = !this.editMode;
                },

                showNotification(type, message) {
                    this.notification = { show: true, type, message };
                    setTimeout(() => {
                        this.notification.show = false;
                    }, 3000);
                }
            }
        }

        function editableField(fieldPath, initialValue) {
            return {
                fieldPath: fieldPath,
                value: initialValue,
                originalValue: initialValue,
                editing: false,
                saving: false,

                get editMode() {
                    return globalFundEditor?.editMode || false;
                },

                startEdit() {
                    if (!this.editing && this.editMode) {
                        this.editing = true;
                        this.$nextTick(() => {
                            // Convert span to input for editing
                            const span = this.$el;
                            span.innerHTML = `<input type="text" class="edit-input" value="${this.value.replace(/"/g, '&quot;')}" />`;
                            const input = span.querySelector('.edit-input');
                            if (input) {
                                input.focus();
                                input.select();
                                
                                // Handle save on Enter
                                input.addEventListener('keydown', (e) => {
                                    if (e.key === 'Enter') {
                                        e.preventDefault();
                                        this.value = input.value;
                                        this.saveEdit();
                                    } else if (e.key === 'Escape') {
                                        e.preventDefault();
                                        this.cancelEdit();
                                    }
                                });

                                // Handle save on blur
                                input.addEventListener('blur', () => {
                                    this.value = input.value;
                                    this.saveEdit();
                                });
                            }
                        });
                    }
                },

                async saveEdit() {
                    if (this.saving || this.value === this.originalValue) {
                        this.cancelEdit();
                        return;
                    }

                    this.saving = true;

                    try {
                        const response = await fetch(`{{ route('funds.update-data', $fund) }}`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({
                                field: this.fieldPath,
                                value: this.value
                            })
                        });

                        const data = await response.json();

                        if (response.ok) {
                            this.originalValue = this.value;
                            this.editing = false;
                            this.updateDisplay();
                            globalFundEditor?.showNotification('success', 'Field updated successfully');
                        } else {
                            this.value = this.originalValue;
                            this.updateDisplay();
                            globalFundEditor?.showNotification('error', data.message || 'Error updating field');
                        }
                    } catch (error) {
                        this.value = this.originalValue;
                        this.updateDisplay();
                        globalFundEditor?.showNotification('error', 'Network error occurred');
                        console.error('Error:', error);
                    } finally {
                        this.saving = false;
                        this.editing = false;
                    }
                },

                cancelEdit() {
                    this.value = this.originalValue;
                    this.editing = false;
                    this.updateDisplay();
                },

                updateDisplay() {
                    if (!this.editing) {
                        this.$el.innerHTML = this.value;
                    }
                },

                init() {
                    this.updateDisplay();
                }
            }
        }
    </script>
</body>
</html>