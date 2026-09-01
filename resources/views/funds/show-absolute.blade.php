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

        /* Asset allocation bars + equity sector pie (816 layout: the fund is
           unconstrained and holds no foreign assets, so the SA/FOREIGN/TOTAL
           table gives way to one bar per asset class beside the sector pie).
           Keep in sync with pdf-absolute.blade.php. */
        .alloc-sector-row {
            display: flex;
            gap: 5.6mm;
            margin-bottom: 2.2mm;
        }
        .alloc-col { width: 66.35mm; min-width: 66.35mm; max-width: 66.35mm; }
        .sector-col { flex: 1; min-width: 0; }

        .alloc-rows { margin-top: 1.6mm; }

        .alloc-row {
            display: flex;
            align-items: center;
            height: 4.1mm;
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-size: 6.5pt;
            color: var(--off-black);
        }
        .alloc-row .alloc-name { width: 24.57mm; white-space: nowrap; overflow: hidden; }
        .alloc-row .alloc-bar-cell { width: 24.2mm; }
        .alloc-row .alloc-bar { height: 3.2mm; background-color: var(--naartjie); }
        .alloc-row .alloc-value { width: 6.58mm; text-align: right; }
        .alloc-row .alloc-arrow { width: 5mm; text-align: right; font-size: 4.6pt; }
        .alloc-row .alloc-arrow.up { color: #000; }
        .alloc-row .alloc-arrow.down { color: #7a9cb4; }
        .alloc-row .alloc-change { width: 6mm; text-align: right; }

        .sector-pie-wrapper { height: 44mm; position: relative; }
        .sector-pie-wrapper > div { width: 100% !important; height: 100% !important; }

        /* Charts — one full-width performance chart, not the balanced pair */
        .charts-row {
            display: block;
            margin: 2mm 0;
        }

        .chart-container { width: 62.2mm; }

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
                    $sidebarOrder = [
                        'domicile','managementCompany','fundManagers','inceptionDate','baseCurrency',
                        'equityIndicator','category','benchmark','minimums','portfolioSize','unitPrice',
                        'numberOfUnits','lastDistributions','incomeDistributions','incomeCharacteristics',
                        'portfolioOrientation','significantRestrictions','foreignAssets','riskOfLoss',
                        'timeHorizon','isinNumber',
                    ];
                    $sidebar = $fund->data['sidebar'] ?? [];
                    $labels = [
                        'domicile' => 'DOMICILE',
                        'managementCompany' => 'MANAGEMENT COMPANY',
                        'fundManagers' => 'FUND MANAGER',
                        'inceptionDate' => 'INCEPTION DATE',
                        'baseCurrency' => 'BASE CURRENCY',
                        'equityIndicator' => 'EQUITY INDICATOR',
                        'category' => 'CATEGORY',
                        'benchmark' => 'BENCHMARK',
                        'minimums' => 'NEW INVESTMENTS',
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
                        'isinNumber' => 'ISIN',
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
                                        $filled = $value['filled'] ?? 7;
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
                <!-- Asset Allocation bars + Equity Sector pie -->
                <div class="alloc-sector-row">
                    <div class="alloc-col">
                        @if(isset($fund->data['mainContent']['assetAllocation']))
                            <h3 class="section-heading">{!! $renderHeading($fund->data['mainContent']['assetAllocation']['title'] ?? 'ASSET ALLOCATION %') !!}</h3>
                            @if(isset($fund->data['mainContent']['assetAllocation']['subtitle']))
                                <p class="section-subheading">
                                    <span x-data="editableField('mainContent.assetAllocation.subtitle', '{{ addslashes($fund->data['mainContent']['assetAllocation']['subtitle']) }}')"
                                          @click="editMode && startEdit()"
                                          :class="editMode ? 'editable' : ''"
                                          x-text="value"></span>
                                </p>
                            @endif

                            @php
                                $allocRows = $fund->data['mainContent']['assetAllocation']['rows'] ?? [];
                                $allocMax = max(array_map(
                                    fn ($r) => (float) ($r['total'] ?? $r['value'] ?? 0),
                                    $allocRows
                                ) ?: [1]) ?: 1;
                            @endphp
                            <div class="alloc-rows">
                                @foreach ($allocRows as $rowIndex => $row)
                                    @php
                                        $value = (float) ($row['total'] ?? $row['value'] ?? 0);
                                        $rawChange = trim((string) ($row['change'] ?? ''));
                                        if (preg_match('/^([\x{25B2}\x{25BC}])\s*(.*)$/u', $rawChange, $cm)) {
                                            $arrowChar = $cm[1];
                                            $changeNum = $cm[2];
                                        } else {
                                            $arrowChar = '';
                                            $changeNum = $rawChange;
                                        }
                                        if ((float) str_replace(',', '', $changeNum) == 0.0) {
                                            $arrowChar = '';
                                        }
                                        $dir = $row['changeDirection'] ?? '';
                                    @endphp
                                    <div class="alloc-row">
                                        <span class="alloc-name">
                                            <span x-data="editableField('mainContent.assetAllocation.rows.{{ $rowIndex }}.name', '{{ addslashes($row['name']) }}')"
                                                  @click="editMode && startEdit()"
                                                  :class="editMode ? 'editable' : ''"
                                                  x-text="value"></span>
                                        </span>
                                        <span class="alloc-bar-cell">
                                            <span class="alloc-bar" style="display:block; width: {{ round($value / $allocMax * 95, 2) }}%;"></span>
                                        </span>
                                        <span class="alloc-value">{{ $fmt($value, 0) }}</span>
                                        <span class="alloc-arrow {{ $dir === 'down' ? 'down' : 'up' }}">{{ $arrowChar }}</span>
                                        <span class="alloc-change">{{ $changeNum }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="sector-col">
                        @if(!empty($fund->data['mainContent']['sectorAllocation']['sectors']))
                            <h3 class="section-heading">{!! $renderHeading($fund->data['mainContent']['sectorAllocation']['title'] ?? 'EQUITY SECTOR ALLOCATION %') !!}</h3>
                            <div class="sector-pie-wrapper">
                                <div id="sectorPie"></div>
                            </div>
                        @endif
                    </div>
                </div>

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

                <!-- Chart -->
                @if(isset($fund->data['mainContent']['charts']))
                    <div class="charts-row">
                        <div class="chart-container">
                            <h4 class="chart-title">PORTFOLIO PERFORMANCE VS BENCHMARK</h4>
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
                                {{-- Display order and footnote markers per the 816
                                     reference — the import stores raw row names. Keep
                                     in sync with pdf-absolute.blade.php. --}}
                                @php
                                    $perfRows = $fund->data['mainContent']['performanceTable']['rows'] ?? [];
                                    $perfMainRows = [];
                                    $perfHighLowRows = [];
                                    foreach ($perfRows as $row) {
                                        if (preg_match('/^fund\s+(highest|lowest)/i', trim(strip_tags((string) $row['name'])))) {
                                            $perfHighLowRows[] = $row;
                                        } else {
                                            $perfMainRows[] = $row;
                                        }
                                    }
                                    $decorateName = function (string $name) {
                                        $plain = trim(strip_tags($name));
                                        if (str_contains($name, '<sup') || preg_match('/[¹²³⁴⁵⁶⁷⁸⁹]/u', $name)) {
                                            return $name;
                                        }
                                        if (preg_match('/^fund\s+(highest|lowest)/i', $plain)) {
                                            return $name.'<sup>3,6</sup>';
                                        }
                                        if (stripos($plain, 'fund') === 0) {
                                            return $name.'<sup>3</sup>';
                                        }
                                        if (stripos($plain, 'benchmark') === 0) {
                                            return $name.'<sup>3,4</sup>';
                                        }
                                        if (preg_match('/^comparator/i', $plain)) {
                                            return 'FTSE/JSE All share<sup>5</sup>';
                                        }
                                        return $name.'<sup>5</sup>';
                                    };
                                    $renderPerfRow = function ($row, $highlight) use ($perfColKeys, $fmt, $decorateName) {
                                        $cells = '<td>'.$decorateName((string) $row['name']).'</td>';
                                        foreach ($perfColKeys as $colKey) {
                                            $value = $colKey && isset($row[$colKey]) ? ($colKey === 'cashValue' ? $row[$colKey] : $fmt($row[$colKey], 1)) : '';
                                            $cells .= '<td>'.e($value).'</td>';
                                        }
                                        return '<tr class="'.($highlight ? 'highlight-row' : '').'">'.$cells.'</tr>';
                                    };
                                @endphp
                                @foreach ($perfMainRows as $idx => $row)
                                    {!! $renderPerfRow($row, $idx === 0) !!}
                                @endforeach
                                @if ($perfHighLowRows !== [])
                                    <tr class="perf-spacer-row"><td colspan="{{ count($perfColKeys) + 1 }}">&nbsp;</td></tr>
                                    @foreach ($perfHighLowRows as $row)
                                        {!! $renderPerfRow($row, false) !!}
                                    @endforeach
                                @endif
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
                                        <td>{{ $row['name'] }}</td>
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
                            <tbody>
                                @foreach ($fund->data['fees']['performanceFeeExamples']['rows'] as $row)
                                    <tr>
                                        <td>{{ $row['name'] }}</td>
                                        <td>{{ $fmt($row['a'] ?? '', 1) }}</td>
                                        <td>{{ $fmt($row['b'] ?? '', 1) }}</td>
                                        <td>{{ $fmt($row['c'] ?? '', 1) }}</td>
                                        <td>{{ $fmt($row['d'] ?? '', 1) }}</td>
                                    </tr>
                                @endforeach
                                <tr class="total-row">
                                    <td>{{ $fund->data['fees']['performanceFeeExamples']['total']['name'] ?? 'Annual fee rate applied (excl. VAT)' }}</td>
                                    <td>{{ $fmt($fund->data['fees']['performanceFeeExamples']['total']['a'] ?? '', 1) }}</td>
                                    <td>{{ $fmt($fund->data['fees']['performanceFeeExamples']['total']['b'] ?? '', 1) }}</td>
                                    <td>{{ $fmt($fund->data['fees']['performanceFeeExamples']['total']['c'] ?? '', 1) }}</td>
                                    <td>{!! $fund->data['fees']['performanceFeeExamples']['total']['d'] ?? '' !!}</td>
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

    @if(isset($fund->data['mainContent']['charts']))
    @php
        // Pie slices in rank order, zero/blank sectors dropped.
        $sectorPieData = array_values(array_map(
            fn ($s) => ['name' => (string) ($s['name'] ?? ''), 'y' => (float) ($s['value'] ?? 0)],
            array_filter(
                $fund->data['mainContent']['sectorAllocation']['sectors'] ?? [],
                fn ($s) => (float) ($s['value'] ?? 0) > 0
            )
        ));
    @endphp
    <script src="https://cdn.jsdelivr.net/npm/highcharts@11/highcharts.js"></script>
    <script>
        /* Graphs mirror pdf-absolute.blade.php — the client compares the page
           against the PDF, so any change here must be made there too. */
        document.addEventListener('DOMContentLoaded', function () {
            const portfolioData = @json($fund->data['mainContent']['charts']['portfolioData'] ?? []);
            const sectorData = @json($sectorPieData);

            const colors = {
                naartjie: '#d25347',
                darkNavy: '#29363d',
                lightBlue: '#7a9cb4',
                lightGrey: '#cccccc',
                darkGrey: '#535353',
                mushroom: '#e2cea4',
                naartjie50: '#e9a9a3',
                offBlack: '#313131',
            };

            const MM = 96 / 25.4;

            Highcharts.setOptions({
                chart: { style: { fontFamily: "'Avenir Next', 'Lato', sans-serif" } },
                credits: { enabled: false },
                accessibility: { enabled: false },
            });

            // ---------------------------------- EQUITY SECTOR ALLOCATION pie
            if (sectorData.length > 0) {
                const sectorColors = [
                    colors.naartjie, colors.darkNavy, colors.lightGrey, colors.lightBlue,
                    colors.darkGrey, colors.mushroom, colors.naartjie50, colors.offBlack,
                ];

                const PIE_DIAMETER = 26 * MM;
                const PIE_CENTRE_X = 29.22 * MM;   // Highcharts adds its plot origin
                const PIE_CENTRE_Y = 16.4 * MM;
                const LABEL_GAP = 1.2 * MM;
                const LABEL_FONT_SIZE = '7.5px';

                const drawSectorLabels = function (chart) {
                    (chart.__sectorLabels || []).forEach(function (l) { l.destroy(); });
                    chart.__sectorLabels = [];

                    const series = chart.series[0];
                    if (!series || !series.points.length) return;

                    const cx = chart.plotLeft + series.center[0];
                    const cy = chart.plotTop + series.center[1];
                    const r = series.center[2] / 2;
                    const total = series.points.reduce(function (a, p) { return a + p.y; }, 0) || 1;

                    const labels = [];
                    let cumulative = 0;
                    series.points.forEach(function (point) {
                        const mid = ((cumulative + point.y / 2) / total) * 2 * Math.PI;
                        cumulative += point.y;

                        const el = chart.renderer
                            .text(point.name + '<br>' + Highcharts.numberFormat(point.y, 0) + '%', 0, 0)
                            .attr({ 'text-anchor': 'middle', zIndex: 6 })
                            .css({ fontSize: LABEL_FONT_SIZE, color: '#000', fontWeight: '400' })
                            .add();

                        const box = el.getBBox();
                        const hw = box.width / 2;
                        const hh = box.height / 2;
                        // getBBox() is cached by content, so keep the block's top
                        // offset now — it cannot be re-read after positioning.
                        const topOffset = box.y;
                        const dx = Math.sin(mid);
                        const dy = -Math.cos(mid);
                        const distance = r + LABEL_GAP + Math.abs(dx) * hw + Math.abs(dy) * hh;

                        labels.push({
                            el: el, hw: hw, hh: hh, topOffset: topOffset,
                            x: cx + dx * distance, y: cy + dy * distance,
                        });
                    });

                    const PAD = 2;
                    for (let pass = 0; pass < 80; pass++) {
                        let moved = false;
                        for (let i = 0; i < labels.length; i++) {
                            for (let j = i + 1; j < labels.length; j++) {
                                const a = labels[i];
                                const b = labels[j];
                                const ox = (a.hw + b.hw + PAD) - Math.abs(a.x - b.x);
                                const oy = (a.hh + b.hh + PAD) - Math.abs(a.y - b.y);
                                if (ox <= 0 || oy <= 0) continue;
                                if (ox < oy) {
                                    const push = (ox / 2) * (a.x <= b.x ? -1 : 1);
                                    a.x += push;
                                    b.x -= push;
                                } else {
                                    const push = (oy / 2) * (a.y <= b.y ? -1 : 1);
                                    a.y += push;
                                    b.y -= push;
                                }
                                moved = true;
                            }
                        }
                        labels.forEach(function (l) {
                            const dx = l.x - cx;
                            const dy = l.y - cy;
                            const dist = Math.sqrt(dx * dx + dy * dy) || 1;
                            const need = r + LABEL_GAP
                                + (Math.abs(dx) / dist) * l.hw
                                + (Math.abs(dy) / dist) * l.hh;
                            if (dist < need) {
                                l.x = cx + (dx / dist) * need;
                                l.y = cy + (dy / dist) * need;
                            }
                        });
                        if (!moved) break;
                    }

                    labels.forEach(function (l) {
                        l.x = Math.min(Math.max(l.x, l.hw + 1), chart.chartWidth - l.hw - 1);
                        l.y = Math.min(Math.max(l.y, l.hh + 1), chart.chartHeight - l.hh - 1);
                        l.el.attr({ x: l.x, y: l.y - l.hh - l.topOffset });
                        chart.__sectorLabels.push(l.el);
                    });
                };

                Highcharts.chart('sectorPie', {
                    chart: {
                        type: 'pie',
                        backgroundColor: 'transparent',
                        animation: false,
                        margin: [0, 0, 0, 0],
                        events: { render: function () { drawSectorLabels(this); } },
                    },
                    title: { text: null },
                    tooltip: { enabled: false },
                    legend: { enabled: false },
                    plotOptions: {
                        pie: {
                            animation: false,
                            size: PIE_DIAMETER,
                            center: [PIE_CENTRE_X, PIE_CENTRE_Y],
                            startAngle: 0,
                            borderColor: '#ffffff',
                            borderWidth: 1.3,
                            dataLabels: { enabled: false },
                            states: { hover: { enabled: false }, inactive: { opacity: 1 } },
                        },
                        series: { animation: false },
                    },
                    colors: sectorColors,
                    series: [{ data: sectorData }],
                });
            }

            // --------------------------- PORTFOLIO PERFORMANCE VS BENCHMARK
            if (portfolioData.length > 0) {
                const formatCashLabel = (v) => 'R ' + Math.round(v).toLocaleString('en-US');
                const formatXTickPortfolio = (label) => {
                    if (!label) return '';
                    const m = String(label).match(/^(\d{4})-(\d{2})$/);
                    if (!m) return label;
                    const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                    return months[parseInt(m[2], 10) - 1] + ' ' + m[1].slice(-2);
                };

                const portfolioDates = portfolioData.map(d => d.date);

                // Excel scales this axis to whole decades: 100 at the x-axis up
                // to the next power of ten above the highest series value.
                const seriesMax = portfolioData.reduce(
                    (m, d) => Math.max(m, d.fund ?? 0, d.benchmark ?? 0), 1);
                const axisMax = Math.pow(10, Math.ceil(Math.log10(seriesMax)));

                const portfolioTickPositions = (function () {
                    const idxByDate = {};
                    portfolioDates.forEach((d, i) => { idxByDate[d] = i; });
                    const anchorIdx = portfolioDates.length > 1 ? 1 : 0;
                    const anchor = portfolioDates[anchorIdx];
                    const firstYear = parseInt(anchor.slice(0, 4), 10);
                    const month = anchor.slice(5, 7);
                    const positions = [anchorIdx];
                    for (let y = firstYear + 3; y <= 2100; y += 3) {
                        const key = y + '-' + month;
                        if (idxByDate[key] !== undefined) positions.push(idxByDate[key]);
                    }
                    return positions;
                })();

                const lastOf = (key) => {
                    for (let i = portfolioData.length - 1; i >= 0; i--) {
                        if (portfolioData[i][key] != null) return portfolioData[i][key];
                    }
                    return null;
                };
                const lastFund = lastOf('fund');
                const lastBenchmark = lastOf('benchmark');
                const labelsCollide = lastFund && lastBenchmark
                    && Math.abs(Math.log10(lastFund / lastBenchmark)) < 0.06;
                const fundLabelY = labelsCollide ? (lastFund >= lastBenchmark ? -8 : 8) : 0;
                const benchmarkLabelY = -fundLabelY;

                Highcharts.chart('portfolioChart', {
                    chart: { type: 'spline', backgroundColor: 'transparent', spacing: [4, 34, 4, 0], animation: false },
                    title: { text: null },
                    xAxis: {
                        categories: portfolioDates,
                        tickWidth: 1,
                        tickLength: 3,
                        tickColor: '#000',
                        lineColor: '#000',
                        lineWidth: 1,
                        labels: {
                            style: { fontSize: '7px', color: '#000', textOverflow: 'none', whiteSpace: 'nowrap' },
                            formatter: function () { return formatXTickPortfolio(this.value); },
                            rotation: 0,
                            autoRotation: false,
                        },
                        tickPositions: portfolioTickPositions,
                    },
                    yAxis: {
                        type: 'logarithmic',
                        title: { text: "Cash Value² (R'000)", style: { fontSize: '7px', color: '#000' } },
                        gridLineWidth: 0,
                        lineColor: '#000',
                        lineWidth: 1,
                        tickWidth: 1,
                        tickLength: 3,
                        tickColor: '#000',
                        min: 100,
                        max: axisMax,
                        endOnTick: false,
                        startOnTick: false,
                        tickPositions: [Math.log10(100)],
                        labels: {
                            align: 'right',
                            x: -1,
                            style: { fontSize: '7px', color: '#000' },
                            formatter: function () { return this.value === 100 ? '100' : ''; },
                        },
                    },
                    legend: {
                        itemStyle: { fontSize: '7px', fontWeight: 'normal', color: colors.darkNavy },
                        symbolWidth: 14, symbolHeight: 1, symbolRadius: 0,
                        itemDistance: 40, margin: 8, padding: 0,
                    },
                    tooltip: { enabled: false },
                    plotOptions: {
                        spline: { marker: { enabled: false }, lineWidth: 1.1 },
                        series: { animation: false, legendSymbol: 'lineMarker' },
                    },
                    series: [
                        {
                            name: 'Fund', data: portfolioData.map(d => d.fund), color: colors.naartjie,
                            dataLabels: [{
                                enabled: true, align: 'left', verticalAlign: 'middle', x: 6, y: fundLabelY,
                                style: { fontSize: '8px', fontWeight: '500', color: colors.naartjie, textOutline: 'none' },
                                formatter: function () { return this.point.index === this.series.data.length - 1 ? formatCashLabel(this.y) : null; },
                                crop: false, overflow: 'allow', allowOverlap: true,
                            }],
                        },
                        {
                            name: 'Benchmark', data: portfolioData.map(d => d.benchmark), color: colors.darkNavy,
                            dataLabels: [{
                                enabled: true, align: 'left', verticalAlign: 'middle', x: 6, y: benchmarkLabelY,
                                style: { fontSize: '8px', fontWeight: '500', color: colors.darkNavy, textOutline: 'none' },
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
