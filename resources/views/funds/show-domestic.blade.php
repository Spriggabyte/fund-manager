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
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;500;700&family=Merriweather:wght@400;700&display=swap" rel="stylesheet">
    <style>
        /* =====================================================
           FOORD FUND FACT SHEET - shared with pdf.blade.php
           ===================================================== */

        :root {
            --naartjie: #d25347;
            --naartjie-75: #dd7e75;
            --naartjie-50: #e9a9a3;
            --naartjie-20: #f6dcd9;
            --dark-navy: #29363d;
            --dark-navy-70: #697277;
            --dark-navy-30: #bfc3c5;
            --dark-navy-15: #dde1e2;
            --dark-navy-10: #e9ebec;
            --medium-grey: #9a9a9a;
            --medium-grey-25: #e6e6e6;
            --medium-grey-20: #ebebeb;
            --medium-grey-15: #f0f0f0;
            --light-grey: #cccccc;
            --dark-grey: #535353;
            --very-light-grey: #f4f4f4;
            --off-black: #313131;
            --white: #ffffff;
        }

        body {
            font-family: 'Avenir Next', 'Lato', -apple-system, sans-serif;
            font-size: 7pt;
            line-height: 1.2;
            color: var(--off-black);
            background: #e5e7eb;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* Page container — matches the PDF's A4 page exactly */
        .page {
            width: 210mm;
            min-height: 297mm;
            margin: 16px auto;
            padding: 0;
            position: relative;
            overflow: hidden;
            background: linear-gradient(to right, var(--dark-navy-15) 52mm, var(--white) 52mm);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 4mm 12mm 4mm 5mm;
            min-height: 26mm;
        }

        .date-badge {
            background-color: var(--naartjie);
            color: var(--white);
            padding: 3mm 5mm;
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 400;
            font-size: 11pt;
            letter-spacing: 0.02em;
        }

        .logo { height: 12mm; }
        .logo img { height: 100%; width: auto; }

        /* Title banner */
        .title-banner {
            background-color: var(--dark-navy);
            color: var(--white);
            padding: 6mm 12mm 7mm 5mm;
            margin: 0;
            width: 100%;
        }

        .fund-name {
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 500;
            font-size: 20pt;
            letter-spacing: 0.01em;
            text-transform: uppercase;
            margin: 0 0 1.5mm 0;
            line-height: 1.05;
        }

        .fund-name .class-suffix {
            font-weight: 400;
            font-size: 16pt;
        }

        .fund-description {
            font-family: 'Merriweather', Georgia, serif;
            font-weight: 400;
            font-size: 8pt;
            line-height: 12pt;
            letter-spacing: 0.01em;
            margin: 0;
            color: var(--white);
        }

        /* Main content layout */
        .content-wrapper {
            display: flex;
            flex-direction: row;
            margin: 0;
            width: 100%;
        }

        /* Sidebar */
        .sidebar {
            width: 52mm;
            min-width: 52mm;
            max-width: 52mm;
            background-color: transparent;
            padding: 4mm 4mm 4mm 5mm;
            overflow: hidden;
        }

        .sidebar-section { margin-bottom: 2mm; }
        .sidebar-section:last-child { margin-bottom: 0; }

        .sidebar-heading {
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 600;
            font-size: 5pt;
            line-height: 6pt;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            color: var(--dark-navy);
            margin: 0 0 0.3mm 0;
        }

        .sidebar-text {
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 400;
            font-size: 6pt;
            line-height: 7pt;
            letter-spacing: 0.01em;
            color: var(--off-black);
            margin: 0;
        }

        .equity-indicator {
            display: flex;
            gap: 0.8mm;
            margin: 1mm 0;
        }

        .equity-dot {
            width: 1.4mm;
            height: 1.4mm;
            border-radius: 50%;
            display: inline-block;
        }

        .equity-dot.filled { background-color: var(--naartjie); }
        .equity-dot.empty  { background-color: var(--medium-grey); }

        /* Main content */
        .main-content {
            flex: 1;
            padding: 4mm;
            min-width: 0;
            overflow: hidden;
        }

        .section-heading {
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 500;
            font-size: 9pt;
            line-height: 10pt;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            color: var(--off-black);
            margin: 0 0 0.5mm 0;
        }

        .section-subheading {
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 400;
            font-size: 6pt;
            line-height: 7pt;
            letter-spacing: 0.01em;
            color: var(--off-black);
            margin: -0.3mm 0 0.8mm 0;
        }

        .section-heading .title-suffix {
            font-size: 7pt;
            font-weight: 400;
            color: var(--off-black);
            text-transform: uppercase;
            letter-spacing: 0.01em;
        }

        /* Tables — separated cells with thin white gaps */
        .table-container {
            position: relative;
            margin-bottom: 2.5mm;
        }

        .page table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 1.5px 1px;
            margin-left: -1.5px;
            margin-right: -1.5px;
            font-size: 6.5pt;
        }

        .page table th {
            background-color: var(--dark-navy);
            color: var(--white);
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 500;
            font-size: 6pt;
            line-height: 6.5pt;
            letter-spacing: 0;
            text-transform: uppercase;
            text-align: center;
            padding: 1.4mm 1.5mm;
        }

        .page table th:first-child { text-align: left; }

        .page table td {
            background-color: var(--very-light-grey);
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 400;
            font-size: 6.5pt;
            line-height: 7.5pt;
            padding: 1mm 2mm;
            text-align: center;
        }

        .page table td:first-child { text-align: left; }

        .performance-table table th {
            background-color: var(--dark-navy-15);
            color: var(--dark-navy);
            font-weight: 600;
        }

        .page table tbody tr.highlight-row td {
            background-color: var(--naartjie-20);
        }

        .page table tbody tr.highlight-row td:first-child {
            color: var(--naartjie);
            font-weight: 500;
        }

        .page table tbody tr.total-row td,
        .page table tfoot td {
            background-color: var(--naartjie);
            font-weight: 500;
            color: var(--white);
        }

        .change-up   { color: var(--naartjie); }
        .change-down { color: var(--naartjie); }

        /* Charts */
        .charts-row {
            display: flex;
            gap: 3mm;
            margin: 2mm 0;
        }

        .chart-container { flex: 1; min-width: 0; }

        .chart-title {
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 600;
            font-size: 6pt;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            color: var(--off-black);
            margin-bottom: 1mm;
        }

        .chart-wrapper {
            height: 32mm;
            position: relative;
        }

        .chart-wrapper > div {
            width: 100% !important;
            height: 100% !important;
        }

        .chart-explanation {
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 400;
            font-size: 5.5pt;
            line-height: 7pt;
            letter-spacing: 0.01em;
            color: var(--dark-grey);
            margin: 1.5mm 0 2mm 0;
        }

        .footnotes {
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 400;
            font-size: 5pt;
            line-height: 6pt;
            letter-spacing: 0.01em;
            color: var(--dark-grey);
            margin-top: 1mm;
        }

        .footnotes p { margin: 0.3mm 0; }
        .footnotes sup { font-size: 4pt; vertical-align: super; }

        /* Page 2 - important info sidebar */
        .info-sidebar {
            width: 52mm;
            min-width: 52mm;
            max-width: 52mm;
            background-color: transparent;
            padding: 0;
            overflow: hidden;
        }

        .info-sidebar-header {
            background-color: var(--dark-navy);
            color: var(--white);
            padding: 3mm 3mm;
            margin: 8mm 3mm 6mm 3mm;
            text-align: center;
        }

        .info-sidebar-header h2 {
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 500;
            font-size: 7.5pt;
            line-height: 9pt;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            margin: 0;
        }

        .info-sidebar-content {
            padding: 0 4mm 4mm 5mm;
        }

        .info-sidebar-content p {
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 400;
            font-size: 5.5pt;
            line-height: 7pt;
            letter-spacing: 0.01em;
            color: var(--off-black);
            margin: 0 0 2mm 0;
            text-align: left;
        }

        .info-sidebar-content p:last-child { margin-bottom: 0; }

        /* Page 2 - fees */
        .fees-content {
            flex: 1;
            padding: 12mm 5mm 4mm 5mm;
            overflow: hidden;
        }

        .fee-rates-table { margin-bottom: 2.5mm; }

        .fee-rates-table table { margin-left: 0; }

        .page .fee-rates-table td {
            padding: 1mm 1.5mm;
            background-color: var(--very-light-grey);
        }

        .fee-rates-table td:last-child:not([colspan]) {
            text-align: right;
            font-weight: 500;
        }

        .fee-rates-table tr.sub-item td:first-child {
            padding-left: 3mm;
        }

        .page .fee-rates-table tr.global-funds-header td {
            background-color: var(--naartjie-20) !important;
            color: var(--naartjie);
            font-weight: 500;
            text-align: left;
        }

        .fee-description {
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 400;
            font-size: 5.5pt;
            line-height: 7pt;
            color: var(--dark-grey);
            margin: 1.5mm 0 2.5mm 0;
        }

        /* Footer */
        .footer {
            margin-top: 4mm;
            padding-top: 2mm;
            border-top: 0.5pt solid var(--naartjie);
        }

        .footer-text {
            font-family: 'Merriweather', Georgia, serif;
            font-weight: 400;
            font-size: 7pt;
            line-height: 9pt;
            letter-spacing: 0.02em;
            color: var(--naartjie);
            margin: 0 0 1.5mm 0;
        }

        .footer-contact {
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 500;
            font-size: 7pt;
            line-height: 9pt;
            letter-spacing: 0.02em;
            color: var(--naartjie);
        }

        .footer-contact p { margin: 0; }

        /* =====================================================
           EDITOR-SPECIFIC STYLES (web only)
           ===================================================== */
        .editable {
            cursor: text;
            transition: background-color 0.15s, outline-color 0.15s;
        }

        .editable:hover {
            background-color: rgba(255, 235, 153, 0.5);
            outline: 1px solid #f59e0b;
            border-radius: 2px;
        }

        .editing {
            background-color: #fef3c7;
            outline: 2px solid #f59e0b;
            border-radius: 2px;
        }

        .edit-input {
            background: transparent;
            border: none;
            outline: none;
            width: 100%;
            font-family: inherit;
            font-size: inherit;
            font-weight: inherit;
            line-height: inherit;
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

        .notification.show { transform: translateX(0); }

        @media print {
            body { background: white; }
            .no-print { display: none !important; }
            .page {
                margin: 0;
                box-shadow: none;
                page-break-after: always;
            }
            .page:last-child { page-break-after: auto; }
        }
    </style>
</head>
<body x-data="fundEditor()">
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

    <!-- Control bar -->
    <div class="no-print bg-[#29363d] text-white p-4 flex justify-between items-center" style="max-width: 210mm; margin: 16px auto;">
        <div class="flex items-center space-x-4">
            <button @click="toggleEditMode()"
                    class="bg-[#d25347] hover:bg-[#dd7e75] text-white px-4 py-2 rounded transition">
                <span x-show="!editMode">Enable Edit Mode</span>
                <span x-show="editMode">Disable Edit Mode</span>
            </button>
            <span x-show="editMode" class="text-[#e9a9a3] text-sm">Edit mode active — click any text to edit</span>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('funds.revisions', $fund) }}"
               class="bg-[#697277] hover:bg-[#29363d] text-white px-4 py-2 rounded border border-[#bfc3c5]">
                Revisions
            </a>
            <a href="{{ route('funds.pdf', $fund) }}"
               class="bg-[#d25347] hover:bg-[#dd7e75] text-white px-4 py-2 rounded">
                Export PDF
            </a>
            <a href="{{ route('funds.index') }}" class="bg-[#9a9a9a] hover:bg-[#535353] text-white px-4 py-2 rounded">
                Back to Funds
            </a>
        </div>
    </div>

    @php
        $fmt = function ($v, int $dp = 1) {
            if ($v === null || $v === '') return '';
            if (is_string($v) && str_starts_with(ltrim($v), '+')) return $v;
            if (is_numeric($v)) return number_format((float) $v, $dp);
            return (string) $v;
        };
        $renderHeading = function (string $title): string {
            return preg_replace(
                '/\s*\(([^)]+)\)\s*$/',
                ' <span class="title-suffix">($1)</span>',
                e($title)
            );
        };
    @endphp

    <!-- PAGE 1 -->
    <div class="page">
        <!-- Header -->
        <div class="header">
            <div class="date-badge">
                <span x-data="editableField('fund.date', '{{ $fund->data['fund']['date'] ?? now()->format('d F Y') }}')"
                      @click="editMode && startEdit()"
                      :class="editMode ? 'editable' : ''"
                      x-text="value"></span>
            </div>
            <div class="logo">
                <img src="{{ $fund->data['fund']['logoUrl'] ?? 'https://foord.co.za/themes/custom/mirum/logo.png' }}" alt="FOORD">
            </div>
        </div>

        <!-- Title Banner -->
        <div class="title-banner">
            @php
                $fundName = $fund->data['fund']['name'] ?? $fund->name;
                if (preg_match('/^(.+?)\s*[-—–]\s*(CLASS\s+[A-Z][0-9]*)$/iu', $fundName, $matches)) {
                    $mainName = trim($matches[1]);
                    $classText = mb_strtoupper(trim($matches[2]));
                } else {
                    $mainName = $fundName;
                    $classText = '';
                }
            @endphp
            <h1 class="fund-name">
                <span x-data="editableField('fund.name', '{{ addslashes($fundName) }}')"
                      @click="editMode && startEdit()"
                      :class="editMode ? 'editable' : ''"
                      x-text="value.toUpperCase()"></span>
            </h1>
            <p class="fund-description">
                <span x-data="editableField('fund.description', '{{ addslashes($fund->data['fund']['description'] ?? '') }}')"
                      @click="editMode && startEdit()"
                      :class="editMode ? 'editable' : ''"
                      x-text="value"></span>
            </p>
        </div>

        <!-- Main content -->
        <div class="content-wrapper">
            <!-- Sidebar -->
            <div class="sidebar">
                @php
                    // 820 reference order: NEW INVESTMENTS replaces the minimums
                    // row (same column, different label) and there is no
                    // FOREIGN ASSETS section — the fund is domestic only.
                    $sidebarOrder = [
                        'domicile','managementCompany','fundManagers','inceptionDate','baseCurrency',
                        'equityIndicator','category','benchmark','newInvestments','portfolioSize','unitPrice',
                        'numberOfUnits','lastDistributions','incomeDistributions','incomeCharacteristics',
                        'portfolioOrientation','significantRestrictions','riskOfLoss',
                        'timeHorizon','isinNumber',
                    ];
                    $sidebar = $fund->data['sidebar'] ?? [];
                    $labels = [
                        'domicile' => 'DOMICILE',
                        'managementCompany' => 'MANAGEMENT COMPANY',
                        'fundManagers' => 'FUND MANAGER',
                        'newInvestments' => 'NEW INVESTMENTS',
                        'inceptionDate' => 'INCEPTION DATE',
                        'baseCurrency' => 'BASE CURRENCY',
                        'equityIndicator' => 'EQUITY INDICATOR',
                        'category' => 'CATEGORY',
                        'benchmark' => 'BENCHMARK',
                        'minimums' => 'MINIMUM LUMP SUM / MONTHLY',
                        'portfolioSize' => 'PORTFOLIO SIZE',
                        'unitPrice' => 'UNIT PRICE',
                        'numberOfUnits' => 'NUMBER OF UNITS',
                        'lastDistributions' => 'LAST DISTRIBUTIONS',
                        'incomeDistributions' => 'INCOME DISTRIBUTIONS',
                        'incomeCharacteristics' => 'INCOME CHARACTERISTICS',
                        'portfolioOrientation' => 'PORTFOLIO ORIENTATION',
                        'significantRestrictions' => 'SIGNIFICANT RESTRICTIONS',
                        'foreignAssets' => 'FOREIGN ASSETS',
                        'riskOfLoss' => 'RISK OF LOSS',
                        'timeHorizon' => 'TIME HORIZON',
                        'isinNumber' => 'ISIN NUMBER',
                    ];
                @endphp

                @foreach ($sidebarOrder as $key)
                    @if(isset($sidebar[$key]))
                        @php $value = $sidebar[$key]; @endphp
                        <div class="sidebar-section">
                            <h3 class="sidebar-heading">{{ $labels[$key] ?? strtoupper(implode(' ', preg_split('/(?=[A-Z])/', $key, -1, PREG_SPLIT_NO_EMPTY))) }}</h3>
                            @if ($key === 'equityIndicator' && is_array($value))
                                <div class="equity-indicator">
                                    @php
                                        // 820 reference: 6 of 10 dots filled
                                        $filled = $value['filled'] ?? 6;
                                        $total = $value['total'] ?? 10;
                                    @endphp
                                    @for ($i = 0; $i < $total; $i++)
                                        <span class="equity-dot {{ $i < $filled ? 'filled' : 'empty' }}"></span>
                                    @endfor
                                </div>
                                @if(isset($value['description']))
                                    <p class="sidebar-text">
                                        <span x-data="editableField('sidebar.{{ $key }}.description', '{{ addslashes($value['description']) }}')"
                                              @click="editMode && startEdit()"
                                              :class="editMode ? 'editable' : ''"
                                              x-text="value"></span>
                                    </p>
                                @endif
                            @elseif (is_array($value))
                                <p class="sidebar-text">
                                    <span x-data="editableField('sidebar.{{ $key }}.description', '{{ addslashes($value['description'] ?? '') }}')"
                                          @click="editMode && startEdit()"
                                          :class="editMode ? 'editable' : ''"
                                          x-text="value"></span>
                                </p>
                            @else
                                <p class="sidebar-text">
                                    <span x-data="editableField('sidebar.{{ $key }}', '{{ addslashes($value) }}')"
                                          @click="editMode && startEdit()"
                                          :class="editMode ? 'editable' : ''"
                                          x-text="value"></span>
                                </p>
                            @endif
                        </div>
                    @endif
                @endforeach
            </div>

            <!-- Main content -->
            <div class="main-content">
                <!-- Asset Allocation Table -->
                @if(isset($fund->data['mainContent']['assetAllocation']))
                    <h3 class="section-heading">{!! $renderHeading($fund->data['mainContent']['assetAllocation']['title'] ?? 'ASSET ALLOCATION % (MAX LIMITS IN BRACKETS)') !!}</h3>
                    @if(isset($fund->data['mainContent']['assetAllocation']['subtitle']))
                        <p class="section-subheading">
                            <span x-data="editableField('mainContent.assetAllocation.subtitle', '{{ addslashes($fund->data['mainContent']['assetAllocation']['subtitle']) }}')"
                                  @click="editMode && startEdit()"
                                  :class="editMode ? 'editable' : ''"
                                  x-text="value"></span>
                        </p>
                    @endif

                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    @foreach ($fund->data['mainContent']['assetAllocation']['headers'] as $header)
                                        <th>{!! $header !!}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            @php
                                $aaHeaders = $fund->data['mainContent']['assetAllocation']['headers'] ?? [];
                                $aaColumnKeys = [];
                                $keyMap = ['SA (100)' => 'sa', 'FOREIGN (45)' => 'foreign', 'TOTAL' => 'total', 'CHANGE' => 'change'];
                                foreach (array_slice($aaHeaders, 1) as $h) {
                                    $aaColumnKeys[] = $keyMap[strtoupper(trim($h))] ?? strtolower(preg_replace('/[^a-zA-Z]/', '', $h) ?: 'col');
                                }
                            @endphp
                            <tbody>
                                @foreach ($fund->data['mainContent']['assetAllocation']['rows'] as $rowIndex => $row)
                                    <tr>
                                        <td>
                                            <span x-data="editableField('mainContent.assetAllocation.rows.{{ $rowIndex }}.name', '{{ addslashes($row['name']) }}')"
                                                  @click="editMode && startEdit()"
                                                  :class="editMode ? 'editable' : ''"
                                                  x-text="value"></span>
                                            @if(!empty($row['limit']))
                                                <span class="text-xs" style="font-size: 0.8em;">({{ $row['limit'] }})</span>
                                            @endif
                                        </td>
                                        @foreach ($aaColumnKeys as $colKey)
                                            <td class="{{ $colKey === 'change' ? (($row['changeDirection'] ?? '') === 'up' ? 'change-up' : (($row['changeDirection'] ?? '') === 'down' ? 'change-down' : '')) : '' }}">
                                                {{ $colKey === 'change' ? ($row['change'] ?? '') : $fmt($row[$colKey] ?? '', 1) }}
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                                @if(isset($fund->data['mainContent']['assetAllocation']['total']))
                                    @php $aaTotal = $fund->data['mainContent']['assetAllocation']['total']; @endphp
                                    <tr class="total-row">
                                        <td>{{ $aaTotal['name'] ?? 'TOTAL' }}</td>
                                        @foreach ($aaColumnKeys as $colKey)
                                            <td>{{ $colKey !== 'change' ? $fmt($aaTotal[$colKey] ?? '', 1) : '' }}</td>
                                        @endforeach
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                @endif

                <!-- Top 10 Investments -->
                @if(isset($fund->data['mainContent']['topInvestments']))
                    <h3 class="section-heading">{!! $renderHeading($fund->data['mainContent']['topInvestments']['title'] ?? 'TOP 10 INVESTMENTS') !!}</h3>

                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    @foreach ($fund->data['mainContent']['topInvestments']['headers'] as $header)
                                        <th>{{ $header }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($fund->data['mainContent']['topInvestments']['rows'] as $rowIndex => $row)
                                    <tr class="{{ ($row['highlight'] ?? false) ? 'highlight-row' : '' }}">
                                        <td>
                                            <span x-data="editableField('mainContent.topInvestments.rows.{{ $rowIndex }}.security', '{{ addslashes($row['security']) }}')"
                                                  @click="editMode && startEdit()"
                                                  :class="editMode ? 'editable' : ''"
                                                  x-text="value"></span>
                                        </td>
                                        <td>{{ $row['assetClass'] }}</td>
                                        <td>{{ $row['market'] }}</td>
                                        <td>{{ $fmt($row['percentage'] ?? '', 1) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                <!-- Chart — the 820 reference has a single Fund-vs-Benchmark cash
                     chart (no inflation chart: the feed carries no inflation
                     graph export for this fund). -->
                @if(isset($fund->data['mainContent']['charts']['portfolioData']))
                    <div class="charts-row">
                        <div class="chart-container" style="max-width: 60%;">
                            <h4 class="chart-title">PORTFOLIO PERFORMANCE VS BENCHMARK<sup>3,4,5</sup></h4>
                            <div class="chart-wrapper">
                                <div id="portfolioChart"></div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Performance Table -->
                @if(isset($fund->data['mainContent']['performanceTable']))
                    @php
                        $perfHeaders = $fund->data['mainContent']['performanceTable']['headers'] ?? [];
                        $perfKeyMap = [
                            'CASH VALUE' => 'cashValue',
                            'SINCE INCEPTION' => 'sinceInception',
                            '20 YRS' => '20yrs', '15 YRS' => '15yrs', '10 YRS' => '10yrs',
                            '7 YRS' => '7yrs', '5 YRS' => '5yrs', '3 YRS' => '3yrs', '2 YRS' => '2yrs',
                            '1 YR' => '1yr', 'YTD' => 'ytd', 'THIS MONTH' => 'thisMonth',
                            '6 MONTHS' => '6months', '3 MONTHS' => '3months',
                        ];
                        $perfColKeys = [];
                        foreach (array_slice($perfHeaders, 1) as $h) {
                            $clean = preg_replace('/[¹²³⁴⁵⁶⁷⁸⁹⁰]/u', '', strip_tags(str_replace('<br>', ' ', $h)));
                            $clean = strtoupper(trim(preg_replace('/\s+/', ' ', $clean)));
                            $perfColKeys[] = $perfKeyMap[$clean] ?? null;
                        }
                    @endphp
                    <h3 class="section-heading">{!! $renderHeading($fund->data['mainContent']['performanceTable']['title'] ?? 'PORTFOLIO PERFORMANCE % (PERIODS GREATER THAN ONE YEAR ARE ANNUALISED)') !!}</h3>

                    <div class="table-container performance-table">
                        <table>
                            <thead>
                                <tr>
                                    @foreach ($perfHeaders as $header)
                                        <th>{!! $header !!}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($fund->data['mainContent']['performanceTable']['rows'] as $row)
                                    @php
                                        // 820 reference markers: Fund ³,⁵ / Benchmark ³,⁴ /
                                        // Fund highest and lowest ³,⁶ with a blank spacer
                                        // row before the highest/lowest pair.
                                        $nameStr = trim(strip_tags((string) $row['name']));
                                        $displayName = $row['name'];
                                        if (! preg_match('/[¹²³⁴⁵⁶⁷⁸⁹⁰]|<sup>/u', $displayName)) {
                                            if (preg_match('/^fund\s+(highest|lowest)/i', $nameStr)) {
                                                $displayName .= ' <sup>3,6</sup>';
                                            } elseif (stripos($nameStr, 'fund') === 0) {
                                                $displayName .= ' <sup>3,5</sup>';
                                            } elseif (stripos($nameStr, 'benchmark') === 0) {
                                                $displayName .= ' <sup>3,4</sup>';
                                            }
                                        }
                                    @endphp
                                    <tr class="{{ ($row['highlight'] ?? false) ? 'highlight-row' : '' }}">
                                        <td>{!! $displayName !!}</td>
                                        @foreach ($perfColKeys as $colKey)
                                            <td>{{ $colKey && isset($row[$colKey]) ? (in_array($colKey, ['cashValue']) ? $row[$colKey] : $fmt($row[$colKey], 1)) : '' }}</td>
                                        @endforeach
                                    </tr>
                                    @if (stripos($nameStr, 'benchmark') === 0)
                                        <tr>
                                            <td colspan="{{ count($perfColKeys) + 1 }}" style="background: transparent;">&nbsp;</td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if(isset($fund->data['mainContent']['performanceTable']['footnotes']))
                        <div class="footnotes">
                            @foreach ($fund->data['mainContent']['performanceTable']['footnotes'] as $footnote)
                                <p>{!! $footnote !!}</p>
                            @endforeach
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>

    <!-- PAGE 2 -->
    <div class="page page-2">
        <div class="content-wrapper">
            <!-- Important Information Sidebar -->
            <div class="info-sidebar">
                <div class="info-sidebar-header">
                    <h2>{{ $fund->data['importantInfo']['title'] ?? 'IMPORTANT INFORMATION FOR INVESTORS' }}</h2>
                </div>
                <div class="info-sidebar-content">
                    @if(isset($fund->data['importantInfo']['paragraphs']))
                        @foreach ($fund->data['importantInfo']['paragraphs'] as $i => $paragraph)
                            <p>
                                <span x-data="editableField('importantInfo.paragraphs.{{ $i }}', '{{ addslashes($paragraph) }}')"
                                      @click="editMode && startEdit()"
                                      :class="editMode ? 'editable' : ''"
                                      x-text="value"></span>
                            </p>
                        @endforeach
                    @endif
                    @if(isset($fund->data['importantInfo']['publishedDate']))
                        <p style="margin-top: 2mm; font-weight: 500;">{{ $fund->data['importantInfo']['publishedDate'] }}</p>
                    @endif
                </div>
            </div>

            <!-- Fees Content -->
            <div class="fees-content">
                <!-- Fee Rates -->
                @if(isset($fund->data['fees']['feeRates']))
                    <h3 class="section-heading">{!! $renderHeading($fund->data['fees']['feeRates']['title'] ?? 'FEE RATES') !!}</h3>

                    <div class="table-container fee-rates-table">
                        <table>
                            <tbody>
                                @foreach ($fund->data['fees']['feeRates']['rates'] as $rate)
                                    <tr>
                                        <td>{{ $rate['name'] }}</td>
                                        <td>{{ $rate['value'] }}</td>
                                    </tr>
                                @endforeach
                                @if(isset($fund->data['fees']['feeRates']['globalFunds']))
                                    <tr class="global-funds-header">
                                        <td colspan="2">{{ $fund->data['fees']['feeRates']['globalFunds']['title'] ?? 'Foord global funds:' }}</td>
                                    </tr>
                                    @foreach ($fund->data['fees']['feeRates']['globalFunds']['funds'] as $gfund)
                                        @php $gName = ltrim($gfund['name'], "- \t"); @endphp
                                        <tr class="sub-item">
                                            <td>- {{ $gName }}</td>
                                            <td>{{ $gfund['value'] }}</td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>

                    @if(isset($fund->data['fees']['feeRates']['description']))
                        <p class="fee-description">{{ $fund->data['fees']['feeRates']['description'] }}</p>
                    @endif
                @endif

                <!-- Total Investment Charge -->
                @if(isset($fund->data['fees']['totalInvestmentCharge']))
                    <h3 class="section-heading">{!! $renderHeading($fund->data['fees']['totalInvestmentCharge']['title'] ?? 'TOTAL INVESTMENT CHARGE %') !!}</h3>

                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    @foreach ($fund->data['fees']['totalInvestmentCharge']['headers'] as $header)
                                        <th>{{ $header }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($fund->data['fees']['totalInvestmentCharge']['rows'] as $row)
                                    <tr>
                                        {{-- The B2 reference stars the TER row ("Total expense
                                             ratio (TER)*") to point at the estimated-fee
                                             footnote below the table. --}}
                                        <td>{{ $row['name'] }}@if(isset($fund->data['fees']['totalInvestmentCharge']['footnote']) && str_contains($row['name'], '(TER)'))*@endif</td>
                                        <td>{{ $fmt($row['12m'] ?? '', 2) }}</td>
                                        <td>{{ $fmt($row['36m'] ?? '', 2) }}</td>
                                    </tr>
                                @endforeach
                                <tr class="total-row">
                                    <td>{{ $fund->data['fees']['totalInvestmentCharge']['total']['name'] ?? 'Total investment charge' }}</td>
                                    <td>{{ $fmt($fund->data['fees']['totalInvestmentCharge']['total']['12m'] ?? '', 2) }}</td>
                                    <td>{{ $fmt($fund->data['fees']['totalInvestmentCharge']['total']['36m'] ?? '', 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    @if(isset($fund->data['fees']['totalInvestmentCharge']['footnote']))
                        <p class="footnotes">{{ $fund->data['fees']['totalInvestmentCharge']['footnote'] }}</p>
                    @endif
                    @if(isset($fund->data['fees']['totalInvestmentCharge']['description']))
                        <p class="fee-description">{{ $fund->data['fees']['totalInvestmentCharge']['description'] }}</p>
                    @endif
                @endif

                <!-- Performance Fees -->
                @if(isset($fund->data['fees']['performanceFees']))
                    <h3 class="section-heading">{!! $renderHeading($fund->data['fees']['performanceFees']['title'] ?? 'PERFORMANCE FEES') !!}</h3>
                    @foreach ($fund->data['fees']['performanceFees']['paragraphs'] as $paragraph)
                        <p class="fee-description">{{ $paragraph }}</p>
                    @endforeach
                @endif

                <!-- Performance Fee Examples -->
                @if(isset($fund->data['fees']['performanceFeeExamples']))
                    <h3 class="section-heading">{!! $renderHeading($fund->data['fees']['performanceFeeExamples']['title'] ?? 'PERFORMANCE FEE EXAMPLES %') !!}</h3>

                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    @foreach ($fund->data['fees']['performanceFeeExamples']['headers'] as $header)
                                        <th>{{ $header }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            @php
                                // Column letters come from the stored headers: the B2
                                // sheet has four example columns (A–D), the B3 sheet
                                // three (A–C).
                                $pfeCols = array_map(
                                    fn ($h) => strtolower(trim($h)),
                                    array_slice($fund->data['fees']['performanceFeeExamples']['headers'] ?? ['', 'A', 'B', 'C', 'D'], 1)
                                );
                            @endphp
                            <tbody>
                                @foreach ($fund->data['fees']['performanceFeeExamples']['rows'] as $row)
                                    <tr>
                                        <td>{{ $row['name'] }}</td>
                                        @foreach ($pfeCols as $col)
                                            <td>{{ $fmt($row[$col] ?? '', 1) }}</td>
                                        @endforeach
                                    </tr>
                                @endforeach
                                <tr class="total-row">
                                    <td>{{ $fund->data['fees']['performanceFeeExamples']['total']['name'] ?? 'Annual fee rate applied (excl. VAT)' }}</td>
                                    @foreach ($pfeCols as $col)
                                        <td>{!! is_numeric($fund->data['fees']['performanceFeeExamples']['total'][$col] ?? null) ? $fmt($fund->data['fees']['performanceFeeExamples']['total'][$col], 1) : e($fund->data['fees']['performanceFeeExamples']['total'][$col] ?? '') !!}</td>
                                    @endforeach
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    @if(isset($fund->data['fees']['performanceFeeExamples']['footnote']))
                        <p class="footnotes">{{ $fund->data['fees']['performanceFeeExamples']['footnote'] }}</p>
                    @endif
                @endif

                <!-- Footer -->
                @if(isset($fund->data['footer']))
                    <div class="footer">
                        <p class="footer-text">{{ $fund->data['footer']['info'] ?? 'Please visit our website for more information regarding our investment track record, the Foord team, current and archived news items, or forms and documents.' }}</p>
                        <p class="footer-text">{{ $fund->data['footer']['freeOfCharge'] ?? 'This information is provided free of charge.' }}</p>
                        <div class="footer-contact">
                            <p>T. {{ $fund->data['footer']['contact']['phone'] ?? '+27 21 532 6969' }}</p>
                            <p>E. {{ $fund->data['footer']['contact']['email'] ?? 'unittrusts@foord.co.za' }}</p>
                            <p>{{ $fund->data['footer']['contact']['website'] ?? 'www.foord.co.za' }}</p>
                        </div>
                    </div>
                @endif
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
                            span.innerHTML = `<input type="text" class="edit-input" value="${String(this.value).replace(/"/g, '&quot;')}" />`;
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

    @if(isset($fund->data['mainContent']['charts']['portfolioData']))
    <script src="https://cdn.jsdelivr.net/npm/highcharts@11/highcharts.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const portfolioData = @json($fund->data['mainContent']['charts']['portfolioData'] ?? []);

            const colors = {
                naartjie: '#d25347',
                darkNavy: '#29363d',
                lightBlue: '#7a9cb4',
                lightGrey: '#cccccc',
                darkGrey: '#535353',
            };

            Highcharts.setOptions({
                chart: { style: { fontFamily: "'Avenir Next', 'Lato', sans-serif" } },
                credits: { enabled: false },
                accessibility: { enabled: false },
            });

            const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
            const formatXTickPortfolio = (label) => {
                if (!label) return '';
                const m = label.match(/^(\d{4})-(\d{2})$/);
                if (!m) return label;
                return months[parseInt(m[2], 10) - 1] + ' ' + m[1].slice(-2);
            };
            // Calendar-aligned ticks every TWO years anchored on the first full
            // month — the 820 reference labels Jan 14, Jan 16, … Jan 26 (the
            // shorter history halves the balanced fund's 4-year pitch).
            const twoYearTicks = (dates) => {
                const idxByDate = {};
                dates.forEach((d, i) => { idxByDate[d] = i; });
                const anchorIdx = dates.length > 1 ? 1 : 0;
                const anchor = dates[anchorIdx];
                const firstYear = parseInt(anchor.slice(0, 4), 10);
                const month = anchor.slice(5, 7);
                const positions = [anchorIdx];
                for (let y = firstYear + 2; y <= 2040; y += 2) {
                    const key = y + '-' + month;
                    if (idxByDate[key] !== undefined) positions.push(idxByDate[key]);
                }
                return positions;
            };

            if (portfolioData.length > 0) {
                const formatCashLabel = (v) => 'R ' + Math.round(v).toLocaleString('en-US');
                // LINEAR y-axis from 0 with a tight max — matches the published
                // fact sheet and the PDF template (pdf.blade.php). Keep in sync.
                const portfolioMaxVal = Math.max(
                    ...portfolioData.map(d => Math.max(d.fund || 0, d.benchmark || 0))
                );
                const portfolioYMax = Math.ceil(portfolioMaxVal * 1.05 / 100) * 100;

                Highcharts.chart('portfolioChart', {
                    chart: { type: 'spline', backgroundColor: 'transparent', spacing: [4, 60, 4, 4], animation: false },
                    title: { text: null },
                    xAxis: {
                        categories: portfolioData.map(d => d.date),
                        tickWidth: 0,
                        lineColor: '#999',
                        labels: {
                            style: { fontSize: '7px', color: colors.darkGrey },
                            formatter: function () { return formatXTickPortfolio(this.value); },
                        },
                        tickPositions: twoYearTicks(portfolioData.map(d => d.date)),
                    },
                    yAxis: {
                        title: { text: "Cash Value² (R'000)", style: { fontSize: '7px', color: colors.darkGrey } },
                        gridLineColor: '#e5e5e5',
                        min: 100,
                        max: portfolioYMax,
                        endOnTick: false,
                        startOnTick: false,
                        tickPositions: [100],
                        labels: {
                            style: { fontSize: '7px', color: colors.darkGrey },
                            formatter: function () { return this.value === 100 ? '100' : ''; },
                        },
                    },
                    legend: {
                        itemStyle: { fontSize: '7px', fontWeight: 'normal', color: colors.darkGrey },
                        symbolWidth: 14, symbolHeight: 2, symbolRadius: 0,
                        itemDistance: 12, margin: 4, padding: 0,
                    },
                    tooltip: { enabled: false },
                    plotOptions: {
                        spline: { marker: { enabled: false }, lineWidth: 1.5 },
                        series: { animation: false },
                    },
                    series: [
                        {
                            name: 'Fund', data: portfolioData.map(d => d.fund), color: colors.naartjie,
                            dataLabels: [{
                                enabled: true, align: 'left', verticalAlign: 'middle', x: 6,
                                style: { fontSize: '7px', fontWeight: 'bold', color: colors.naartjie, textOutline: 'none' },
                                formatter: function () { return this.point.index === this.series.data.length - 1 ? formatCashLabel(this.y) : null; },
                                crop: false, overflow: 'allow', allowOverlap: true,
                            }],
                        },
                        {
                            name: 'Benchmark', data: portfolioData.map(d => d.benchmark), color: colors.darkNavy,
                            dataLabels: [{
                                enabled: true, align: 'left', verticalAlign: 'middle', x: 6,
                                style: { fontSize: '7px', fontWeight: 'bold', color: colors.darkNavy, textOutline: 'none' },
                                formatter: function () { return this.point.index === this.series.data.length - 1 ? formatCashLabel(this.y) : null; },
                                crop: false, overflow: 'allow', allowOverlap: true,
                            }],
                        },
                    ],
                });
            }
        });
    </script>
    @endif
</body>
</html>
