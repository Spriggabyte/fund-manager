<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $fund->data['fund']['name'] ?? $fund->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700&family=Merriweather:wght@400;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'naartjie': '#d25347',
                        'naartjie-75': '#dd7e75',
                        'naartjie-50': '#e9a9a3',
                        'naartjie-20': '#f6dcd9',
                        'dark-navy': '#29363d',
                        'dark-navy-70': '#697277',
                        'dark-navy-30': '#bfc3c5',
                        'dark-navy-15': '#dde1e2',
                        'dark-navy-10': '#e9ebec',
                        'light-grey': '#cccccc',
                        'light-blue': '#7a9cb4',
                        'light-blue-50': '#bdceda',
                        'dark-grey': '#535353',
                        'medium-grey': '#9a9a9a',
                        'medium-grey-25': '#e6e6e6',
                        'medium-grey-20': '#ebebeb',
                        'medium-grey-15': '#f0f0f0',
                        'very-light-grey': '#f4f4f4',
                        'mushroom': '#e2cea4',
                        'mushroom-50': '#f1e7d2',
                        'off-black': '#313131',
                    },
                    fontFamily: {
                        'avenir': ['Avenir Next', 'system-ui', 'sans-serif'],
                        'merriweather': ['Merriweather', 'Georgia', 'serif'],
                        'lato': ['Lato', 'sans-serif'],
                    },
                    fontSize: {
                        'xxs': ['6px', { lineHeight: '7px' }],
                        'xs-custom': ['7px', { lineHeight: '7.5px' }],
                        'sm-custom': ['8px', { lineHeight: '9.5px' }],
                        'base-custom': ['9px', { lineHeight: '11px' }],
                        'table-head': ['7.5px', { lineHeight: '7.5px' }],
                        'table-body': ['8px', { lineHeight: '12px' }],
                        'section-head': ['10px', { lineHeight: '12px' }],
                    },
                    letterSpacing: {
                        'wide-custom': '0.02em',
                        'wider-custom': '0.03em',
                        'widest-custom': '0.05em',
                    }
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700&family=Merriweather:wght@400;700&display=swap');

        @media print {
            .no-print { display: none; }
            .page-break { page-break-before: always; }
        }

        /* PDF mode styles */
        body.pdf-mode {
            background: white;
        }
        body.pdf-mode .max-w-4xl {
            max-width: 100%;
        }
        body.pdf-mode .bg-white {
            box-shadow: none;
        }
        body.pdf-mode .my-4 {
            margin-top: 0;
            margin-bottom: 0;
        }

        /* Equity indicator dots */
        .equity-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 2px;
        }
        .equity-dot.filled { background-color: #d25347; }
        .equity-dot.empty { background-color: #9a9a9a; }

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

        /* Table styling */
        .foord-table {
            border-collapse: collapse;
            width: 100%;
            font-family: 'Lato', sans-serif;
        }
        .foord-table th {
            background-color: #29363d;
            color: white;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 7.5px;
            line-height: 7.5px;
            padding: 6px 8px;
            text-align: left;
            letter-spacing: 0.02em;
        }
        .foord-table th:not(:first-child) {
            text-align: center;
        }
        .foord-table td {
            font-size: 8px;
            line-height: 12px;
            padding: 5px 8px;
            border-bottom: 1px solid #e5e5e5;
        }
        .foord-table td:not(:first-child) {
            text-align: center;
        }
        .foord-table tbody tr:nth-child(odd) {
            background-color: #f4f4f4;
        }
        .foord-table tbody tr:nth-child(even) {
            background-color: white;
        }
        .foord-table .total-row {
            background-color: #d25347 !important;
            color: white;
            font-weight: 500;
        }
        .foord-table .highlight-row {
            background-color: #f6dcd9 !important;
        }
        .foord-table .highlight-row td:first-child {
            color: #d25347;
            font-weight: 500;
        }

        /* Naartjie accent bar on left of tables */
        .table-container {
            position: relative;
            padding-left: 3px;
        }
        .table-container::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 3px;
            background-color: #d25347;
        }

        /* Chart container */
        canvas {
            max-height: 180px;
        }

        /* Sidebar styling */
        .sidebar-section h3 {
            font-family: 'Lato', sans-serif;
            font-weight: 500;
            font-size: 6px;
            line-height: 7.5px;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            color: #29363d;
            margin-bottom: 2px;
        }
        .sidebar-section p {
            font-family: 'Lato', sans-serif;
            font-weight: 400;
            font-size: 7px;
            line-height: 8.5px;
            color: #313131;
        }

        /* Arrow indicators for change column */
        .change-up::before {
            content: '▲';
            margin-right: 2px;
            font-size: 6px;
        }
        .change-down::before {
            content: '▼';
            margin-right: 2px;
            font-size: 6px;
        }

        /* Sector allocation horizontal bar styling */
        .sector-bar-row {
            display: flex;
            align-items: center;
            margin-bottom: 1px;
            font-family: 'Lato', sans-serif;
        }
        .sector-bar-label {
            width: 100px;
            flex-shrink: 0;
            color: #313131;
            font-size: 7px;
            line-height: 9px;
            text-align: left;
            padding-right: 6px;
        }
        .sector-bar-track {
            flex: 1;
            height: 12px;
            background-color: transparent;
            position: relative;
            display: flex;
            align-items: center;
        }
        .sector-bar-fill {
            height: 100%;
            background-color: #d25347;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding-right: 3px;
            min-width: 16px;
        }
        .sector-bar-value {
            color: white;
            font-size: 7px;
            font-weight: 700;
            line-height: 12px;
        }
        .sector-bar-change {
            width: 38px;
            flex-shrink: 0;
            text-align: right;
            font-size: 7px;
            padding-left: 4px;
        }
        .sector-bar-change.up { color: #d25347; }
        .sector-bar-change.down { color: #29363d; }

        /* Asset allocation table (matching foord-table style) */
        .asset-legend-table {
            font-family: 'Lato', sans-serif;
            font-size: 7px;
            line-height: 10px;
            width: 100%;
            border-collapse: collapse;
        }
        .asset-legend-table th {
            background-color: #29363d;
            color: white;
            font-size: 6.5px;
            font-weight: 500;
            text-transform: uppercase;
            padding: 4px 6px;
            text-align: right;
            letter-spacing: 0.02em;
            border-right: 1px solid rgba(255,255,255,0.4);
        }
        .asset-legend-table th:first-child {
            text-align: left;
        }
        .asset-legend-table th:last-child {
            border-right: none;
        }
        .asset-legend-table td {
            padding: 3px 6px;
            border-bottom: 1px solid #e5e5e5;
        }
        .asset-legend-table td:not(:first-child) {
            text-align: right;
        }
        .asset-legend-table tbody tr:nth-child(odd) {
            background-color: #f4f4f4;
        }
        .asset-legend-table tbody tr:nth-child(even) {
            background-color: white;
        }
        .asset-legend-table .indent {
            padding-left: 10px;
            color: #697277;
            font-size: 6.5px;
        }
        .asset-legend-table .total-row td {
            background-color: #d25347 !important;
            color: white;
            font-weight: 700;
            border-bottom: none;
        }

        /* Monthly chart legend */
        .monthly-legend {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 6px;
            font-family: 'Lato', sans-serif;
            font-size: 6.5px;
            line-height: 8px;
            color: #313131;
        }
        .monthly-legend-item {
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .monthly-legend-swatch {
            width: 7px;
            height: 7px;
            display: inline-block;
            flex-shrink: 0;
        }
    </style>
</head>
<body class="bg-gray-100 text-off-black font-lato @if(request()->has('pdf')) pdf-mode @endif" x-data="fundEditor()">
    <!-- Notification -->
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

    <div class="max-w-4xl mx-auto">
        <!-- Control Bar -->
        <div class="no-print bg-dark-navy text-white my-4 p-4 rounded-lg flex justify-between items-center" @if(request()->has('pdf')) style="display: none;" @endif>
            <div class="flex items-center space-x-4">
                <button @click="toggleEditMode()"
                        class="bg-naartjie hover:bg-naartjie-75 text-white px-4 py-2 rounded-lg transition duration-150 ease-in-out">
                    <span x-show="!editMode">Enable Edit Mode</span>
                    <span x-show="editMode">Disable Edit Mode</span>
                </button>
                <span x-show="editMode" class="text-naartjie-50 text-sm">Edit mode active - Click any text to edit</span>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('funds.revisions', $fund) }}"
                   class="bg-dark-navy-70 hover:bg-dark-navy text-white px-4 py-2 rounded-lg transition duration-150 ease-in-out flex items-center space-x-2 border border-dark-navy-30">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>Revisions</span>
                </a>
                <a href="{{ route('funds.pdf', $fund) }}"
                   class="bg-naartjie hover:bg-naartjie-75 text-white px-4 py-2 rounded-lg transition duration-150 ease-in-out flex items-center space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span>Export PDF</span>
                </a>
                <a href="{{ route('funds.index') }}" class="bg-medium-grey hover:bg-dark-grey text-white px-4 py-2 rounded-lg">
                    Back to Funds
                </a>
            </div>
        </div>

        <!-- Page 1 -->
        <div class="bg-white shadow-lg">
            <!-- Header Section -->
            <div class="relative">
                <!-- Date Badge -->
                <div class="absolute top-4 left-4">
                    <div class="bg-naartjie text-white px-4 py-2 font-medium text-xs uppercase tracking-wider">
                        <span x-data="editableField('fund.date', '{{ $fund->data['fund']['date'] ?? $fund->updated_at->format('d F Y') }}')"
                              @click="editMode && startEdit()"
                              :class="editMode ? 'editable' : ''"
                              x-text="value"></span>
                    </div>
                </div>

                <!-- Logo -->
                <div class="absolute top-4 right-4">
                    <img src="{{ $fund->data['fund']['logoUrl'] ?? '' }}" alt="Foord Logo" class="h-10">
                </div>
            </div>

            <!-- Fund Name Banner -->
            <div class="bg-dark-navy text-white pt-16 pb-4 px-6">
                <h1 class="text-2xl font-medium uppercase tracking-widest mb-2">
                    <span x-data="editableField('fund.name', '{{ $fund->data['fund']['name'] ?? $fund->name }}')"
                          @click="editMode && startEdit()"
                          :class="editMode ? 'editable' : ''"
                          x-text="value"></span>
                </h1>
                <p class="font-merriweather text-sm leading-relaxed text-gray-200 max-w-3xl">
                    <span x-data="editableField('fund.description', '{{ $fund->data['fund']['description'] ?? '' }}')"
                          @click="editMode && startEdit()"
                          :class="editMode ? 'editable' : ''"
                          x-text="value"></span>
                </p>
            </div>

            <!-- Main Content Area -->
            <div class="flex flex-col md:flex-row">
                <!-- Left Sidebar -->
                <div class="w-full md:w-48 bg-dark-navy-15 p-4">
                    <div class="space-y-3">
                        @if(isset($fund->data['sidebar']))
                            @foreach ($fund->data['sidebar'] as $key => $value)
                                <div class="sidebar-section">
                                    <h3 class="font-medium text-dark-navy uppercase tracking-wide" style="font-size: 6px; line-height: 7.5px;">
                                        {{ strtoupper(implode(' ', preg_split('/(?=[A-Z])/', $key, -1, PREG_SPLIT_NO_EMPTY))) }}
                                    </h3>
                                    @if ($key === 'equityIndicator' && is_array($value))
                                        <div class="flex items-center my-1">
                                            @php
                                                $filledDots = $value['filled'] ?? 7;
                                                $totalDots = $value['total'] ?? 10;
                                            @endphp
                                            @for ($i = 0; $i < $totalDots; $i++)
                                                <span class="equity-dot {{ $i < $filledDots ? 'filled' : 'empty' }}"></span>
                                            @endfor
                                        </div>
                                        <p class="text-off-black" style="font-size: 7px; line-height: 8.5px;">
                                            <span x-data="editableField('sidebar.{{ $key }}.description', '{{ $value['description'] ?? '' }}')"
                                                  @click="editMode && startEdit()"
                                                  :class="editMode ? 'editable' : ''"
                                                  x-text="value"></span>
                                        </p>
                                    @elseif (is_array($value))
                                        <p class="text-off-black" style="font-size: 7px; line-height: 8.5px;">
                                            <span x-data="editableField('sidebar.{{ $key }}.description', '{{ $value['description'] ?? '' }}')"
                                                  @click="editMode && startEdit()"
                                                  :class="editMode ? 'editable' : ''"
                                                  x-text="value"></span>
                                        </p>
                                    @else
                                        <p class="text-off-black" style="font-size: 7px; line-height: 8.5px;">
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

                <!-- Right Content -->
                <div class="flex-1 p-4">
                    <!-- Top Row: Sector Allocation + Asset Allocation side by side -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
                        <!-- Equity Sector Allocation (horizontal bars) -->
                        @if(isset($fund->data['mainContent']['sectorAllocation']))
                            <div>
                                <h3 class="text-xs font-medium uppercase tracking-wide text-off-black mb-0.5">
                                    <span x-data="editableField('mainContent.sectorAllocation.title', '{{ $fund->data['mainContent']['sectorAllocation']['title'] }}')"
                                          @click="editMode && startEdit()"
                                          :class="editMode ? 'editable' : ''"
                                          x-text="value"></span>
                                </h3>
                                <p class="text-xxs text-dark-grey mb-2">
                                    <span x-data="editableField('mainContent.sectorAllocation.subtitle', '{{ $fund->data['mainContent']['sectorAllocation']['subtitle'] }}')"
                                          @click="editMode && startEdit()"
                                          :class="editMode ? 'editable' : ''"
                                          x-text="value"></span>
                                </p>
                                <div>
                                    @foreach ($fund->data['mainContent']['sectorAllocation']['sectors'] as $sIndex => $sector)
                                        <div class="sector-bar-row">
                                            <div class="sector-bar-label">
                                                <span x-data="editableField('mainContent.sectorAllocation.sectors.{{ $sIndex }}.name', '{{ $sector['name'] }}')"
                                                      @click="editMode && startEdit()"
                                                      :class="editMode ? 'editable' : ''"
                                                      x-text="value"></span>
                                            </div>
                                            <div class="sector-bar-track">
                                                <div class="sector-bar-fill" style="width: {{ max(($sector['value'] / 45) * 100, 4) }}%;">
                                                    <span class="sector-bar-value">{{ $sector['value'] }}</span>
                                                </div>
                                            </div>
                                            <div class="sector-bar-change {{ ($sector['direction'] ?? '') === 'up' ? 'up' : (($sector['direction'] ?? '') === 'down' ? 'down' : '') }}">
                                                @if(isset($sector['direction']) && $sector['direction'] === 'up')
                                                    <span class="change-up"></span>
                                                @elseif(isset($sector['direction']) && $sector['direction'] === 'down')
                                                    <span class="change-down"></span>
                                                @endif
                                                <span x-data="editableField('mainContent.sectorAllocation.sectors.{{ $sIndex }}.change', '{{ $sector['change'] ?? '' }}')"
                                                      @click="editMode && startEdit()"
                                                      :class="editMode ? 'editable' : ''"
                                                      x-text="value"></span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Asset Allocation (table only, no donut chart) -->
                        @if(isset($fund->data['mainContent']['assetAllocation']))
                            <div>
                                <h3 class="text-xs font-medium uppercase tracking-wide text-off-black mb-2">
                                    <span x-data="editableField('mainContent.assetAllocation.title', '{{ $fund->data['mainContent']['assetAllocation']['title'] }}')"
                                          @click="editMode && startEdit()"
                                          :class="editMode ? 'editable' : ''"
                                          x-text="value"></span>
                                </h3>
                                <div class="table-container">
                                    <table class="asset-legend-table">
                                        <thead>
                                            <tr>
                                                @foreach ($fund->data['mainContent']['assetAllocation']['headers'] as $index => $header)
                                                    <th>
                                                        <span x-data="editableField('mainContent.assetAllocation.headers.{{ $index }}', '{{ $header }}')"
                                                              @click="editMode && startEdit()"
                                                              :class="editMode ? 'editable' : ''"
                                                              x-text="value"></span>
                                                    </th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($fund->data['mainContent']['assetAllocation']['rows'] as $rowIndex => $row)
                                                <tr class="{{ ($row['isTotal'] ?? false) ? 'total-row' : '' }}">
                                                    <td class="{{ ($row['indent'] ?? false) ? 'indent' : '' }}">
                                                        <span x-data="editableField('mainContent.assetAllocation.rows.{{ $rowIndex }}.name', '{{ $row['name'] }}')"
                                                              @click="editMode && startEdit()"
                                                              :class="editMode ? 'editable' : ''"
                                                              x-text="value"></span>
                                                    </td>
                                                    <td>
                                                        <span x-data="editableField('mainContent.assetAllocation.rows.{{ $rowIndex }}.current', '{{ $row['current'] }}')"
                                                              @click="editMode && startEdit()"
                                                              :class="editMode ? 'editable' : ''"
                                                              x-text="value"></span>
                                                    </td>
                                                    <td>
                                                        <span x-data="editableField('mainContent.assetAllocation.rows.{{ $rowIndex }}.previous', '{{ $row['previous'] }}')"
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
                    </div>

                    <!-- Middle Row: Top 10 Investments + Portfolio Performance side by side -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
                        <!-- Top 10 Investments Table -->
                        @if(isset($fund->data['mainContent']['topInvestments']))
                            <div>
                                <h3 class="text-xs font-medium uppercase tracking-wide text-off-black mb-2">
                                    <span x-data="editableField('mainContent.topInvestments.title', '{{ $fund->data['mainContent']['topInvestments']['title'] }}')"
                                          @click="editMode && startEdit()"
                                          :class="editMode ? 'editable' : ''"
                                          x-text="value"></span>
                                </h3>
                                <div class="table-container">
                                    <table class="foord-table">
                                        <thead>
                                            <tr>
                                                @foreach ($fund->data['mainContent']['topInvestments']['headers'] as $index => $header)
                                                    <th class="{{ $loop->first ? 'text-left' : 'text-center' }}">
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
                                                <tr>
                                                    <td>
                                                        <span x-data="editableField('mainContent.topInvestments.rows.{{ $rowIndex }}.security', '{{ $row['security'] }}')"
                                                              @click="editMode && startEdit()"
                                                              :class="editMode ? 'editable' : ''"
                                                              x-text="value"></span>
                                                    </td>
                                                    <td>
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

                        <!-- Portfolio Performance vs Benchmark (line chart) -->
                        @if(isset($fund->data['mainContent']['charts']['portfolioData']))
                            <div>
                                <h3 class="text-xs font-medium uppercase tracking-wide text-off-black mb-2">PORTFOLIO PERFORMANCE VS BENCHMARK</h3>
                                <canvas id="portfolioChart" style="max-height: 160px;"></canvas>
                            </div>
                        @endif
                    </div>

                    <!-- Monthly Portfolio Performance vs Benchmark (bar chart) -->
                    @if(isset($fund->data['mainContent']['charts']['monthlyData']))
                        <div class="mb-4">
                            <h3 class="text-xs font-medium uppercase tracking-wide text-off-black mb-2">MONTHLY PORTFOLIO PERFORMANCE VS BENCHMARK</h3>
                            <canvas id="monthlyChart" style="max-height: 140px;"></canvas>
                            <div class="monthly-legend">
                                <div class="monthly-legend-item">
                                    <span class="monthly-legend-swatch" style="background-color: #d25347;"></span>
                                    Months when benchmark is negative
                                </div>
                                <div class="monthly-legend-item">
                                    <span class="monthly-legend-swatch" style="background-color: #29363d;"></span>
                                    Months when benchmark is positive
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Chart explanation text -->
                    @if(isset($fund->data['mainContent']['chartDescription']))
                        <p class="text-dark-grey mb-4" style="font-size: 6.5px; line-height: 8px;">
                            <span x-data="editableField('mainContent.chartDescription', '{{ addslashes($fund->data['mainContent']['chartDescription']) }}')"
                                  @click="editMode && startEdit()"
                                  :class="editMode ? 'editable' : ''"
                                  x-text="value"></span>
                        </p>
                    @endif

                    <!-- Performance Table -->
                    @if(isset($fund->data['mainContent']['performanceTable']))
                        <div>
                            <h3 class="text-xs font-medium uppercase tracking-wide text-off-black mb-2">
                                <span x-data="editableField('mainContent.performanceTable.title', '{{ $fund->data['mainContent']['performanceTable']['title'] }}')"
                                      @click="editMode && startEdit()"
                                      :class="editMode ? 'editable' : ''"
                                      x-text="value"></span>
                            </h3>
                            <div class="table-container">
                                <table class="foord-table">
                                    <thead>
                                        <tr>
                                            @foreach ($fund->data['mainContent']['performanceTable']['headers'] as $index => $header)
                                                <th class="{{ $loop->first ? 'text-left' : 'text-center' }}">
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
                                            <tr class="{{ ($row['highlight'] ?? false) ? 'highlight-row' : '' }}">
                                                <td class="{{ ($row['highlight'] ?? false) ? 'text-naartjie font-medium' : '' }}">
                                                    <span x-data="editableField('mainContent.performanceTable.rows.{{ $rowIndex }}.name', '{!! addslashes($row['name']) !!}')"
                                                          @click="editMode && startEdit()"
                                                          :class="editMode ? 'editable' : ''"
                                                          x-html="value"></span>
                                                </td>
                                                <td>
                                                    <span x-data="editableField('mainContent.performanceTable.rows.{{ $rowIndex }}.cashValue', '{{ $row['cashValue'] }}')"
                                                          @click="editMode && startEdit()"
                                                          :class="editMode ? 'editable' : ''"
                                                          x-text="value"></span>
                                                </td>
                                                <td>
                                                    <span x-data="editableField('mainContent.performanceTable.rows.{{ $rowIndex }}.sinceInception', '{{ $row['sinceInception'] }}')"
                                                          @click="editMode && startEdit()"
                                                          :class="editMode ? 'editable' : ''"
                                                          x-text="value"></span>
                                                </td>
                                                @if(isset($row['15yrs']))
                                                <td>
                                                    <span x-data="editableField('mainContent.performanceTable.rows.{{ $rowIndex }}.15yrs', '{{ $row['15yrs'] }}')"
                                                          @click="editMode && startEdit()"
                                                          :class="editMode ? 'editable' : ''"
                                                          x-text="value"></span>
                                                </td>
                                                @endif
                                                <td>
                                                    <span x-data="editableField('mainContent.performanceTable.rows.{{ $rowIndex }}.10yrs', '{{ $row['10yrs'] }}')"
                                                          @click="editMode && startEdit()"
                                                          :class="editMode ? 'editable' : ''"
                                                          x-text="value"></span>
                                                </td>
                                                <td>
                                                    <span x-data="editableField('mainContent.performanceTable.rows.{{ $rowIndex }}.7yrs', '{{ $row['7yrs'] }}')"
                                                          @click="editMode && startEdit()"
                                                          :class="editMode ? 'editable' : ''"
                                                          x-text="value"></span>
                                                </td>
                                                <td>
                                                    <span x-data="editableField('mainContent.performanceTable.rows.{{ $rowIndex }}.5yrs', '{{ $row['5yrs'] }}')"
                                                          @click="editMode && startEdit()"
                                                          :class="editMode ? 'editable' : ''"
                                                          x-text="value"></span>
                                                </td>
                                                <td>
                                                    <span x-data="editableField('mainContent.performanceTable.rows.{{ $rowIndex }}.3yrs', '{{ $row['3yrs'] }}')"
                                                          @click="editMode && startEdit()"
                                                          :class="editMode ? 'editable' : ''"
                                                          x-text="value"></span>
                                                </td>
                                                <td>
                                                    <span x-data="editableField('mainContent.performanceTable.rows.{{ $rowIndex }}.1yr', '{{ $row['1yr'] }}')"
                                                          @click="editMode && startEdit()"
                                                          :class="editMode ? 'editable' : ''"
                                                          x-text="value"></span>
                                                </td>
                                                <td>
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

                            <!-- Footnotes -->
                            <div class="mt-2 space-y-0.5">
                                @if(isset($fund->data['mainContent']['performanceTable']['footnotes']))
                                    @foreach ($fund->data['mainContent']['performanceTable']['footnotes'] as $index => $footnote)
                                        <p class="text-xxs text-dark-grey">
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

        <!-- Page 2 -->
        <div class="bg-white mt-4 shadow-lg page-break">
            <div class="flex flex-col md:flex-row">
                <!-- Left Sidebar - Important Information -->
                @if(isset($fund->data['importantInfo']))
                    <div class="w-full md:w-48 bg-dark-navy-15 p-4">
                        <div class="bg-dark-navy text-white px-3 py-2 mb-4">
                            <h2 class="text-xs font-medium uppercase tracking-wide">
                                <span x-data="editableField('importantInfo.title', '{{ $fund->data['importantInfo']['title'] }}')"
                                      @click="editMode && startEdit()"
                                      :class="editMode ? 'editable' : ''"
                                      x-text="value"></span>
                            </h2>
                        </div>
                        <div class="space-y-2">
                            @foreach ($fund->data['importantInfo']['paragraphs'] as $index => $paragraph)
                                <p class="text-off-black" style="font-size: 6px; line-height: 7px;">
                                    <span x-data="editableField('importantInfo.paragraphs.{{ $index }}', '{{ addslashes($paragraph) }}')"
                                          @click="editMode && startEdit()"
                                          :class="editMode ? 'editable' : ''"
                                          x-text="value"></span>
                                </p>
                            @endforeach
                            <p class="text-off-black pt-2" style="font-size: 6px; line-height: 7px;">
                                <span x-data="editableField('importantInfo.publishedDate', '{{ $fund->data['importantInfo']['publishedDate'] }}')"
                                      @click="editMode && startEdit()"
                                      :class="editMode ? 'editable' : ''"
                                      x-text="value"></span>
                            </p>
                        </div>
                    </div>
                @endif

                <!-- Right Content - Fees -->
                <div class="flex-1 p-4">
                    <!-- Fee Rates -->
                    @if(isset($fund->data['fees']['feeRates']))
                        <div class="mb-5">
                            <h3 class="text-xs font-medium uppercase tracking-wide text-off-black mb-2">
                                <span x-data="editableField('fees.feeRates.title', '{{ $fund->data['fees']['feeRates']['title'] }}')"
                                      @click="editMode && startEdit()"
                                      :class="editMode ? 'editable' : ''"
                                      x-text="value"></span>
                            </h3>
                            <div class="table-container">
                                <table class="foord-table">
                                    <tbody>
                                        @foreach ($fund->data['fees']['feeRates']['rates'] as $index => $rate)
                                            <tr>
                                                <td class="text-left">
                                                    <span x-data="editableField('fees.feeRates.rates.{{ $index }}.name', '{{ $rate['name'] }}')"
                                                          @click="editMode && startEdit()"
                                                          :class="editMode ? 'editable' : ''"
                                                          x-text="value"></span>
                                                </td>
                                                <td class="text-left">
                                                    <span x-data="editableField('fees.feeRates.rates.{{ $index }}.value', '{{ $rate['value'] }}')"
                                                          @click="editMode && startEdit()"
                                                          :class="editMode ? 'editable' : ''"
                                                          x-text="value"></span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <p class="text-xxs text-dark-grey mt-2">
                                <span x-data="editableField('fees.feeRates.description', '{{ addslashes($fund->data['fees']['feeRates']['description'] ?? '') }}')"
                                      @click="editMode && startEdit()"
                                      :class="editMode ? 'editable' : ''"
                                      x-text="value"></span>
                            </p>
                        </div>
                    @endif

                    <!-- Total Investment Charge -->
                    @if(isset($fund->data['fees']['totalInvestmentCharge']))
                        <div class="mb-5">
                            <h3 class="text-xs font-medium uppercase tracking-wide text-off-black mb-2">
                                <span x-data="editableField('fees.totalInvestmentCharge.title', '{{ $fund->data['fees']['totalInvestmentCharge']['title'] }}')"
                                      @click="editMode && startEdit()"
                                      :class="editMode ? 'editable' : ''"
                                      x-text="value"></span>
                            </h3>
                            <div class="table-container">
                                <table class="foord-table">
                                    <thead>
                                        <tr>
                                            @foreach ($fund->data['fees']['totalInvestmentCharge']['headers'] as $index => $header)
                                                <th class="{{ $loop->first ? 'text-left' : 'text-center' }}">
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
                                            <tr>
                                                <td>
                                                    <span x-data="editableField('fees.totalInvestmentCharge.rows.{{ $rowIndex }}.name', '{{ $row['name'] }}')"
                                                          @click="editMode && startEdit()"
                                                          :class="editMode ? 'editable' : ''"
                                                          x-text="value"></span>
                                                </td>
                                                <td>
                                                    <span x-data="editableField('fees.totalInvestmentCharge.rows.{{ $rowIndex }}.12m', '{{ $row['12m'] }}')"
                                                          @click="editMode && startEdit()"
                                                          :class="editMode ? 'editable' : ''"
                                                          x-text="value"></span>
                                                </td>
                                                <td>
                                                    <span x-data="editableField('fees.totalInvestmentCharge.rows.{{ $rowIndex }}.36m', '{{ $row['36m'] }}')"
                                                          @click="editMode && startEdit()"
                                                          :class="editMode ? 'editable' : ''"
                                                          x-text="value"></span>
                                                </td>
                                            </tr>
                                        @endforeach
                                        <tr class="total-row">
                                            <td class="font-medium">
                                                <span x-data="editableField('fees.totalInvestmentCharge.total.name', '{{ $fund->data['fees']['totalInvestmentCharge']['total']['name'] }}')"
                                                      @click="editMode && startEdit()"
                                                      :class="editMode ? 'editable' : ''"
                                                      x-text="value"></span>
                                            </td>
                                            <td class="font-medium">
                                                <span x-data="editableField('fees.totalInvestmentCharge.total.12m', '{{ $fund->data['fees']['totalInvestmentCharge']['total']['12m'] }}')"
                                                      @click="editMode && startEdit()"
                                                      :class="editMode ? 'editable' : ''"
                                                      x-text="value"></span>
                                            </td>
                                            <td class="font-medium">
                                                <span x-data="editableField('fees.totalInvestmentCharge.total.36m', '{{ $fund->data['fees']['totalInvestmentCharge']['total']['36m'] }}')"
                                                      @click="editMode && startEdit()"
                                                      :class="editMode ? 'editable' : ''"
                                                      x-text="value"></span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <p class="text-xxs text-dark-grey mt-2">
                                <span x-data="editableField('fees.totalInvestmentCharge.description', '{{ addslashes($fund->data['fees']['totalInvestmentCharge']['description'] ?? '') }}')"
                                      @click="editMode && startEdit()"
                                      :class="editMode ? 'editable' : ''"
                                      x-text="value"></span>
                            </p>
                        </div>
                    @endif

                    <!-- Performance Fees -->
                    @if(isset($fund->data['fees']['performanceFees']))
                        <div class="mb-5">
                            <h3 class="text-xs font-medium uppercase tracking-wide text-off-black mb-2">
                                <span x-data="editableField('fees.performanceFees.title', '{{ $fund->data['fees']['performanceFees']['title'] }}')"
                                      @click="editMode && startEdit()"
                                      :class="editMode ? 'editable' : ''"
                                      x-text="value"></span>
                            </h3>
                            @foreach ($fund->data['fees']['performanceFees']['paragraphs'] as $index => $paragraph)
                                <p class="text-xxs text-dark-grey mb-2 leading-relaxed">
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
                        <div class="mb-5">
                            <h3 class="text-xs font-medium uppercase tracking-wide text-off-black mb-2">
                                <span x-data="editableField('fees.performanceFeeExamples.title', '{{ $fund->data['fees']['performanceFeeExamples']['title'] }}')"
                                      @click="editMode && startEdit()"
                                      :class="editMode ? 'editable' : ''"
                                      x-text="value"></span>
                            </h3>
                            <div class="table-container">
                                <table class="foord-table">
                                    <thead>
                                        <tr>
                                            @foreach ($fund->data['fees']['performanceFeeExamples']['headers'] as $index => $header)
                                                <th class="{{ $loop->first ? 'text-left' : 'text-center' }}">
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
                                            <tr>
                                                <td>
                                                    <span x-data="editableField('fees.performanceFeeExamples.rows.{{ $rowIndex }}.name', '{{ $row['name'] }}')"
                                                          @click="editMode && startEdit()"
                                                          :class="editMode ? 'editable' : ''"
                                                          x-text="value"></span>
                                                </td>
                                                <td>
                                                    <span x-data="editableField('fees.performanceFeeExamples.rows.{{ $rowIndex }}.a', '{{ $row['a'] }}')"
                                                          @click="editMode && startEdit()"
                                                          :class="editMode ? 'editable' : ''"
                                                          x-text="value"></span>
                                                </td>
                                                <td>
                                                    <span x-data="editableField('fees.performanceFeeExamples.rows.{{ $rowIndex }}.b', '{{ $row['b'] }}')"
                                                          @click="editMode && startEdit()"
                                                          :class="editMode ? 'editable' : ''"
                                                          x-text="value"></span>
                                                </td>
                                                <td>
                                                    <span x-data="editableField('fees.performanceFeeExamples.rows.{{ $rowIndex }}.c', '{{ $row['c'] }}')"
                                                          @click="editMode && startEdit()"
                                                          :class="editMode ? 'editable' : ''"
                                                          x-text="value"></span>
                                                </td>
                                                <td>
                                                    <span x-data="editableField('fees.performanceFeeExamples.rows.{{ $rowIndex }}.d', '{{ $row['d'] }}')"
                                                          @click="editMode && startEdit()"
                                                          :class="editMode ? 'editable' : ''"
                                                          x-text="value"></span>
                                                </td>
                                            </tr>
                                        @endforeach
                                        <tr class="total-row">
                                            <td class="font-medium">
                                                <span x-data="editableField('fees.performanceFeeExamples.total.name', '{{ $fund->data['fees']['performanceFeeExamples']['total']['name'] }}')"
                                                      @click="editMode && startEdit()"
                                                      :class="editMode ? 'editable' : ''"
                                                      x-text="value"></span>
                                            </td>
                                            <td class="font-medium">
                                                <span x-data="editableField('fees.performanceFeeExamples.total.a', '{{ $fund->data['fees']['performanceFeeExamples']['total']['a'] }}')"
                                                      @click="editMode && startEdit()"
                                                      :class="editMode ? 'editable' : ''"
                                                      x-text="value"></span>
                                            </td>
                                            <td class="font-medium">
                                                <span x-data="editableField('fees.performanceFeeExamples.total.b', '{{ $fund->data['fees']['performanceFeeExamples']['total']['b'] }}')"
                                                      @click="editMode && startEdit()"
                                                      :class="editMode ? 'editable' : ''"
                                                      x-text="value"></span>
                                            </td>
                                            <td class="font-medium">
                                                <span x-data="editableField('fees.performanceFeeExamples.total.c', '{{ $fund->data['fees']['performanceFeeExamples']['total']['c'] }}')"
                                                      @click="editMode && startEdit()"
                                                      :class="editMode ? 'editable' : ''"
                                                      x-text="value"></span>
                                            </td>
                                            <td class="font-medium">
                                                <span x-data="editableField('fees.performanceFeeExamples.total.d', '{!! addslashes($fund->data['fees']['performanceFeeExamples']['total']['d']) !!}')"
                                                      @click="editMode && startEdit()"
                                                      :class="editMode ? 'editable' : ''"
                                                      x-html="value"></span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <p class="text-xxs text-dark-grey mt-2">
                                <span x-data="editableField('fees.performanceFeeExamples.footnote', '{{ addslashes($fund->data['fees']['performanceFeeExamples']['footnote'] ?? '') }}')"
                                      @click="editMode && startEdit()"
                                      :class="editMode ? 'editable' : ''"
                                      x-text="value"></span>
                            </p>
                        </div>
                    @endif

                    <!-- Footer -->
                    @if(isset($fund->data['footer']))
                        <div class="mt-6 pt-4 border-t border-naartjie">
                            <div class="text-naartjie font-merriweather mb-3">
                                <p class="text-xs leading-relaxed">
                                    <span x-data="editableField('footer.info', '{{ $fund->data['footer']['info'] }}')"
                                          @click="editMode && startEdit()"
                                          :class="editMode ? 'editable' : ''"
                                          x-text="value"></span>
                                </p>
                                <p class="text-xs leading-relaxed mt-2">
                                    <span x-data="editableField('footer.freeOfCharge', '{{ $fund->data['footer']['freeOfCharge'] }}')"
                                          @click="editMode && startEdit()"
                                          :class="editMode ? 'editable' : ''"
                                          x-text="value"></span>
                                </p>
                            </div>
                            <div class="text-naartjie text-xs font-medium">
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
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </div>

    <script>
        let globalFundEditor = null;
        function fundEditor() {
            return {
                editMode: false,
                notification: { show: false, type: 'success', message: '' },
                init() { globalFundEditor = this; },
                toggleEditMode() { this.editMode = !this.editMode; },
                showNotification(type, message) {
                    this.notification = { show: true, type, message };
                    setTimeout(() => { this.notification.show = false; }, 3000);
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
                get editMode() { return globalFundEditor?.editMode || false; },
                startEdit() {
                    if (!this.editing && this.editMode) {
                        this.editing = true;
                        this.$nextTick(() => {
                            const span = this.$el;
                            span.innerHTML = `<input type="text" class="edit-input" value="${this.value.replace(/"/g, '&quot;')}" />`;
                            const input = span.querySelector('.edit-input');
                            if (input) {
                                input.focus();
                                input.select();
                                input.addEventListener('keydown', (e) => {
                                    if (e.key === 'Enter') { e.preventDefault(); this.value = input.value; this.saveEdit(); }
                                    else if (e.key === 'Escape') { e.preventDefault(); this.cancelEdit(); }
                                });
                                input.addEventListener('blur', () => { this.value = input.value; this.saveEdit(); });
                            }
                        });
                    }
                },
                async saveEdit() {
                    if (this.saving || this.value === this.originalValue) { this.cancelEdit(); return; }
                    this.saving = true;
                    try {
                        const response = await fetch(`{{ route('funds.update-data', $fund) }}`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({ field: this.fieldPath, value: this.value })
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
                updateDisplay() { if (!this.editing) { this.$el.innerHTML = this.value; } },
                init() { this.updateDisplay(); }
            }
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Foord color palette
        const colors = {
            naartjie: '#d25347',
            darkNavy: '#29363d',
            mediumGrey: '#9a9a9a',
            lightGrey: '#cccccc',
            darkGrey: '#535353'
        };

        // Portfolio Performance vs Benchmark Chart
        @if(isset($fund->data['mainContent']['charts']['portfolioData']))
        const portfolioData = @json($fund->data['mainContent']['charts']['portfolioData']);
        const portfolioCtx = document.getElementById('portfolioChart');
        if (portfolioCtx) {
            new Chart(portfolioCtx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: portfolioData.map(item => item.date),
                    datasets: [
                        {
                            label: 'Fund',
                            data: portfolioData.map(item => item.fund),
                            borderColor: colors.naartjie,
                            backgroundColor: 'transparent',
                            borderWidth: 2,
                            tension: 0.1,
                            pointRadius: 0
                        },
                        {
                            label: 'Benchmark',
                            data: portfolioData.map(item => item.benchmark),
                            borderColor: colors.darkNavy,
                            backgroundColor: 'transparent',
                            borderWidth: 2,
                            tension: 0.1,
                            pointRadius: 0
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                boxWidth: 6,
                                padding: 15,
                                font: { size: 8, family: 'Lato' }
                            }
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            callbacks: { label: (c) => `${c.dataset.label}: R ${c.parsed.y.toLocaleString()}` }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { font: { size: 7, family: 'Lato' }, maxRotation: 0, autoSkip: true, maxTicksLimit: 6 }
                        },
                        y: {
                            type: 'logarithmic',
                            ticks: {
                                font: { size: 7, family: 'Lato' },
                                callback: (v) => {
                                    if (v === 100 || v === 1000 || v === 10000) return 'R ' + v.toLocaleString();
                                    return null;
                                }
                            },
                            grid: { color: '#e5e5e5' }
                        }
                    }
                }
            });
        }
        @endif

        // Monthly Portfolio Performance vs Benchmark Chart
        @if(isset($fund->data['mainContent']['charts']['monthlyData']))
        const monthlyData = @json($fund->data['mainContent']['charts']['monthlyData']);
        const monthlyCtx = document.getElementById('monthlyChart');
        if (monthlyCtx) {
            new Chart(monthlyCtx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: monthlyData.map(item => item.date),
                    datasets: [{
                        label: 'Relative Performance',
                        data: monthlyData.map(item => item.relative),
                        backgroundColor: monthlyData.map(item =>
                            item.benchmarkNegative ? colors.naartjie : colors.darkNavy
                        ),
                        borderWidth: 0,
                        barPercentage: 1.0,
                        categoryPercentage: 1.0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: (c) => `Relative: ${c.parsed.y.toFixed(1)}%`
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: {
                                font: { size: 7, family: 'Lato' },
                                maxRotation: 0,
                                autoSkip: true,
                                maxTicksLimit: 6
                            }
                        },
                        y: {
                            grid: { color: '#e5e5e5' },
                            ticks: {
                                font: { size: 7, family: 'Lato' },
                                callback: (v) => v + '%'
                            }
                        }
                    }
                }
            });
        }
        @endif
    </script>

</body>
</html>