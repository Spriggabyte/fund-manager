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
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700&family=Merriweather:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <style>
        /* === Base Reset & Fonts === */
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Avenir Next', 'Lato', system-ui, sans-serif;
            color: #313131;
            background: #e5e7eb;
            -webkit-font-smoothing: antialiased;
        }

        /* === Color Variables === */
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
            --off-black: #313131;
            --dark-grey: #535353;
            --medium-grey: #9a9a9a;
            --light-grey: #cccccc;
            --very-light-grey: #f4f4f4;
            --light-blue: #7a9cb4;
            --mushroom: #e2cea4;
            --mushroom-50: #f1e7d2;
        }

        /* === Page Container (A4 proportions) === */
        .page-container {
            max-width: 794px;
            margin: 0 auto;
        }

        .page {
            background: white;
            width: 100%;
            position: relative;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            min-height: 1123px;
        }

        .page + .page {
            margin-top: 16px;
        }

        /* === Print / PDF mode === */
        @media print {
            .no-print { display: none !important; }
            .page-break { page-break-before: always; }
            body { background: white; }
            .page { box-shadow: none; margin: 0; }
        }

        body.pdf-mode {
            background: white;
        }
        body.pdf-mode .page-container { max-width: 100%; }
        body.pdf-mode .page { box-shadow: none; }
        body.pdf-mode .page + .page { margin-top: 0; }

        /* === Header Area === */
        .header-row {
            display: flex;
            height: 78px;
            position: relative;
        }

        .header-grey {
            width: 218px;
            min-width: 218px;
            background-color: var(--dark-navy-15);
            display: flex;
            align-items: center;
            padding-left: 16px;
        }

        .date-badge {
            background-color: var(--naartjie);
            color: white;
            font-family: 'Avenir Next', system-ui, sans-serif;
            font-weight: 500;
            font-size: 10pt;
            letter-spacing: 0.03em;
            padding: 8px 18px;
            white-space: nowrap;
        }

        .header-white {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding-right: 16px;
        }

        .foord-logo {
            height: 46px;
            width: auto;
        }

        /* === Fund Name Banner === */
        .fund-banner {
            background-color: var(--dark-navy);
            color: white;
            padding: 8px 16px 6px 16px;
        }

        .fund-banner h1 {
            font-family: 'Avenir Next', system-ui, sans-serif;
            font-weight: 500;
            font-size: 23pt;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            margin-bottom: 4px;
            line-height: 1.1;
        }

        .fund-banner .description {
            font-family: 'Merriweather', Georgia, serif;
            font-weight: 400;
            font-style: italic;
            font-size: 7pt;
            line-height: 9.5pt;
            letter-spacing: 0.02em;
            color: rgba(255,255,255,0.9);
            max-width: 100%;
        }

        /* === Naartjie Stripe === */
        .naartjie-stripe {
            height: 3px;
            background-color: var(--naartjie);
        }

        /* === Main Body Layout === */
        .main-body {
            display: flex;
            min-height: 200px;
        }

        /* === Sidebar === */
        .sidebar {
            width: 174px;
            min-width: 174px;
            background-color: var(--dark-navy-15);
            padding: 10px 10px 10px 14px;
        }

        .sidebar-section {
            margin-bottom: 1px;
        }

        .sidebar-section h3 {
            font-family: 'Avenir Next', system-ui, sans-serif;
            font-weight: 700;
            font-size: 6pt;
            line-height: 7.5pt;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            color: var(--dark-navy);
            margin-top: 4px;
            margin-bottom: 1px;
        }

        .sidebar-section:first-child h3 {
            margin-top: 0;
        }

        .sidebar-section p,
        .sidebar-section .sidebar-value {
            font-family: 'Avenir Next', system-ui, sans-serif;
            font-weight: 400;
            font-size: 7pt;
            line-height: 7.5pt;
            letter-spacing: 0.02em;
            color: var(--off-black);
        }

        /* Equity indicator dots */
        .equity-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 1.5px;
        }
        .equity-dot.filled { background-color: var(--naartjie); }
        .equity-dot.empty { background-color: var(--medium-grey); }

        /* === Content Area === */
        .content-area {
            flex: 1;
            padding: 6px 14px 8px 12px;
        }

        /* === Section Headings === */
        .section-heading {
            font-family: 'Avenir Next', system-ui, sans-serif;
            font-weight: 500;
            font-size: 7.5pt;
            line-height: 8pt;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            color: var(--off-black);
            margin-bottom: 2px;
        }

        .section-subtitle {
            font-family: 'Avenir Next', system-ui, sans-serif;
            font-weight: 400;
            font-size: 6pt;
            line-height: 7pt;
            color: var(--dark-grey);
            margin-bottom: 2px;
        }

        /* === Two-Column Layout === */
        .two-col {
            display: flex;
            gap: 10px;
            margin-bottom: 6px;
        }

        .two-col .col-left {
            flex: 1;
            min-width: 0;
        }

        .two-col .col-right {
            flex: 1;
            min-width: 0;
        }

        /* === Tables === */
        .table-wrapper {
            position: relative;
            padding-left: 3px;
        }

        .table-wrapper::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 3px;
            background-color: var(--naartjie);
        }

        .foord-table {
            border-collapse: collapse;
            width: 100%;
            font-family: 'Avenir Next', system-ui, sans-serif;
        }

        .foord-table th {
            background-color: var(--dark-navy);
            color: white;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 7pt;
            line-height: 7.5pt;
            padding: 4px 6px;
            text-align: left;
            letter-spacing: 0.02em;
            border-right: 1px solid rgba(255,255,255,0.4);
        }

        .foord-table th:last-child {
            border-right: none;
        }

        .foord-table th:not(:first-child) {
            text-align: center;
        }

        .foord-table td {
            font-size: 7.5pt;
            line-height: 10pt;
            padding: 2.5px 6px;
            border-bottom: 1px solid #e5e5e5;
            font-weight: 400;
        }

        .foord-table td:not(:first-child) {
            text-align: center;
        }

        .foord-table tbody tr:nth-child(odd) {
            background-color: var(--very-light-grey);
        }

        .foord-table tbody tr:nth-child(even) {
            background-color: white;
        }

        .foord-table .total-row {
            background-color: var(--naartjie) !important;
            color: white;
            font-weight: 500;
        }

        .foord-table .total-row td {
            border-bottom: none;
        }

        .foord-table .highlight-row {
            background-color: var(--naartjie-20) !important;
        }

        .foord-table .highlight-row td:first-child {
            color: var(--naartjie);
            font-weight: 500;
        }

        .foord-table .empty-row td {
            height: 4px;
            padding: 0;
            border-bottom: none;
            background-color: white !important;
        }

        /* Performance table specific */
        .perf-table th {
            font-size: 6.5pt;
            line-height: 7pt;
            padding: 4px 4px;
            vertical-align: bottom;
        }

        .perf-table td {
            font-size: 7pt;
            line-height: 9pt;
            padding: 3px 4px;
        }

        /* Fee rates table (no header, simple layout, no naartjie bar) */
        .fee-table {
            border-collapse: collapse;
            width: 100%;
            font-family: 'Avenir Next', system-ui, sans-serif;
        }

        .fee-table td {
            font-size: 7.5pt;
            line-height: 10pt;
            padding: 3px 6px;
            border-bottom: 1px solid #e5e5e5;
            font-weight: 400;
            background-color: white;
        }

        /* === Arrow indicators === */
        .change-up { color: var(--off-black); }
        .change-down { color: var(--off-black); }
        .change-up::before { content: '▲ '; font-size: 5pt; }
        .change-down::before { content: '▼ '; font-size: 5pt; }

        /* === Chart === */
        canvas {
            max-height: 142px;
            width: 100% !important;
        }

        /* Chart explanation text */
        .chart-explanation {
            font-family: 'Avenir Next', system-ui, sans-serif;
            font-weight: 400;
            font-size: 6pt;
            line-height: 7pt;
            color: var(--dark-grey);
            letter-spacing: 0.01em;
        }

        /* === Footnotes === */
        .footnote {
            font-family: 'Lato', sans-serif;
            font-size: 5.5pt;
            line-height: 6.5pt;
            color: var(--dark-grey);
            letter-spacing: 0.01em;
        }

        /* === Page 2 === */
        .important-info-header {
            background-color: var(--dark-navy);
            color: white;
            padding: 8px 8px;
            margin-bottom: 10px;
        }

        .important-info-header h2 {
            font-family: 'Avenir Next', system-ui, sans-serif;
            font-weight: 500;
            font-size: 7pt;
            line-height: 8.5pt;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .important-info-text {
            font-family: 'Lato', sans-serif;
            font-weight: 300;
            font-size: 5.5pt;
            line-height: 6.5pt;
            color: var(--off-black);
            margin-bottom: 4px;
        }

        .page2-heading {
            font-family: 'Avenir Next', system-ui, sans-serif;
            font-weight: 500;
            font-size: 7.5pt;
            line-height: 8pt;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            color: var(--off-black);
            margin-bottom: 5px;
        }

        .page2-body {
            font-family: 'Avenir Next', system-ui, sans-serif;
            font-weight: 400;
            font-size: 7pt;
            line-height: 9pt;
            color: var(--dark-grey);
            letter-spacing: 0.01em;
        }

        .page2-note {
            font-family: 'Lato', sans-serif;
            font-weight: 400;
            font-size: 5.5pt;
            line-height: 7pt;
            color: var(--dark-grey);
        }

        /* === Footer === */
        .footer-divider {
            border-top: 1px solid var(--naartjie);
            margin-top: auto;
            padding-top: 10px;
        }

        .footer-info {
            font-family: 'Merriweather', Georgia, serif;
            font-weight: 400;
            font-style: italic;
            font-size: 7.5pt;
            line-height: 10pt;
            letter-spacing: 0.02em;
            color: var(--naartjie);
            margin-bottom: 6px;
        }

        .footer-contact {
            font-family: 'Avenir Next', system-ui, sans-serif;
            font-weight: 500;
            font-size: 7.5pt;
            line-height: 10pt;
            letter-spacing: 0.03em;
            color: var(--naartjie);
        }

        .footer-logo {
            float: right;
            height: 30px;
            margin-top: -20px;
        }

        /* === Editable Fields === */
        .editable {
            cursor: text;
            transition: all 0.15s;
            min-height: 1em;
        }

        .editable:hover {
            background-color: rgba(245, 158, 11, 0.1);
            outline: 1px dashed #f59e0b;
            border-radius: 2px;
            padding: 1px 3px;
        }

        .editing {
            background-color: #fef3c7;
            outline: 2px solid #f59e0b;
            border-radius: 2px;
            padding: 1px 3px;
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
            line-height: inherit;
            letter-spacing: inherit;
        }

        /* === Notification === */
        .notification {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 50;
            transform: translateX(100%);
            transition: transform 0.3s ease-in-out;
        }
        .notification.show { transform: translateX(0); }

        /* === Control Bar === */
        .control-bar {
            background: var(--dark-navy);
            color: white;
            padding: 10px 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 8px;
            margin: 12px 0;
        }

        .control-bar button,
        .control-bar a {
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.15s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-naartjie { background: var(--naartjie); color: white; border: none; cursor: pointer; }
        .btn-naartjie:hover { background: var(--naartjie-75); }
        .btn-grey { background: var(--dark-navy-70); color: white; border: 1px solid var(--dark-navy-30); }
        .btn-grey:hover { background: var(--dark-navy); }
        .btn-muted { background: var(--medium-grey); color: white; }
        .btn-muted:hover { background: var(--dark-grey); }

        /* Chart legend */
        .chart-legend {
            display: flex;
            justify-content: center;
            gap: 12px;
            margin-top: 4px;
            font-size: 6pt;
            color: var(--dark-grey);
        }

        .chart-legend span {
            display: flex;
            align-items: center;
            gap: 3px;
        }

        .legend-line {
            width: 18px;
            height: 2px;
            display: inline-block;
        }
    </style>
</head>
<body class="@if(request()->has('pdf')) pdf-mode @endif" x-data="fundEditor()">
    <!-- Notification -->
    <div x-show="notification.show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="transform translate-x-full"
         x-transition:enter-end="transform translate-x-0"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="transform translate-x-0"
         x-transition:leave-end="transform translate-x-full"
         class="notification fixed top-4 right-4 z-50 max-w-sm">
        <div style="background: white; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); border: 1px solid #e5e7eb; padding: 12px 16px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <svg x-show="notification.type === 'success'" style="width: 18px; height: 18px; color: #22c55e;" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <svg x-show="notification.type === 'error'" style="width: 18px; height: 18px; color: #ef4444;" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
                <p style="font-size: 13px; font-weight: 500;" :style="notification.type === 'success' ? 'color: #166534' : 'color: #991b1b'" x-text="notification.message"></p>
            </div>
        </div>
    </div>

    <div class="page-container">
        <!-- Control Bar -->
        <div class="no-print control-bar" @if(request()->has('pdf')) style="display: none;" @endif>
            <div style="display: flex; align-items: center; gap: 12px;">
                <button @click="toggleEditMode()" class="btn-naartjie">
                    <span x-show="!editMode">Enable Edit Mode</span>
                    <span x-show="editMode">Disable Edit Mode</span>
                </button>
                <span x-show="editMode" style="color: var(--naartjie-50); font-size: 13px;">Edit mode active - Click any text to edit</span>
            </div>
            <div style="display: flex; align-items: center; gap: 8px;">
                <a href="{{ route('funds.revisions', $fund) }}" class="btn-grey">
                    <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Revisions
                </a>
                <a href="{{ route('funds.pdf', $fund) }}" class="btn-naartjie">
                    <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Export PDF
                </a>
                <a href="{{ route('funds.index') }}" class="btn-muted">Back to Funds</a>
            </div>
        </div>

        <!-- ==================== PAGE 1 ==================== -->
        <div class="page">
            <!-- Header Row: Grey left + White right -->
            <div class="header-row">
                <div class="header-grey">
                    <div class="date-badge">
                        <span x-data="editableField('fund.date', '{{ $fund->data['fund']['date'] ?? $fund->updated_at->format('d F Y') }}')"
                              @click="editMode && startEdit()"
                              :class="editMode ? 'editable' : ''"
                              x-text="value"></span>
                    </div>
                </div>
                <div class="header-white">
                    <img src="{{ $fund->data['fund']['logoUrl'] ?? '' }}" alt="Foord Logo" class="foord-logo">
                </div>
            </div>

            <!-- Fund Name Banner -->
            <div class="fund-banner">
                <h1>
                    <span x-data="editableField('fund.name', '{{ $fund->data['fund']['name'] ?? $fund->name }}')"
                          @click="editMode && startEdit()"
                          :class="editMode ? 'editable' : ''"
                          x-text="value"></span>
                </h1>
                <p class="description">
                    <span x-data="editableField('fund.description', '{{ addslashes($fund->data['fund']['description'] ?? '') }}')"
                          @click="editMode && startEdit()"
                          :class="editMode ? 'editable' : ''"
                          x-text="value"></span>
                </p>
            </div>

            <!-- Naartjie Stripe -->
            <div class="naartjie-stripe"></div>

            <!-- Main Body: Sidebar + Content -->
            <div class="main-body">
                <!-- Sidebar -->
                <div class="sidebar">
                    @if(isset($fund->data['sidebar']))
                        @php
                            $sidebar = $fund->data['sidebar'];
                            $labelMap = [
                                'domicile' => 'DOMICILE',
                                'managementCompany' => 'MANAGEMENT COMPANY',
                                'fundManager' => 'FUND MANAGER',
                                'fundManagers' => 'FUND MANAGERS',
                                'inceptionDate' => 'INCEPTION DATE',
                                'baseCurrency' => 'BASE CURRENCY',
                                'equityIndicator' => 'EQUITY INDICATOR',
                                'category' => 'CATEGORY',
                                'benchmark' => 'BENCHMARK',
                                'minimumLumpSum' => 'MINIMUM LUMP SUM / MONTHLY',
                                'minimumLumpSumMonthly' => 'MINIMUM LUMP SUM / MONTHLY',
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

                            $displayOrder = [
                                'domicile', 'managementCompany', 'fundManager', 'fundManagers',
                                'inceptionDate', 'baseCurrency', 'equityIndicator',
                                'category', 'benchmark', 'minimumLumpSum', 'minimumLumpSumMonthly',
                                'portfolioSize', 'unitPrice', 'numberOfUnits',
                                'lastDistributions', 'incomeDistributions', 'incomeCharacteristics',
                                'portfolioOrientation', 'significantRestrictions',
                                'foreignAssets', 'riskOfLoss', 'timeHorizon', 'isinNumber',
                            ];
                        @endphp

                        @foreach ($displayOrder as $key)
                            @if(isset($sidebar[$key]))
                                @php
                                    $value = $sidebar[$key];
                                    $label = $labelMap[$key] ?? strtoupper(implode(' ', preg_split('/(?=[A-Z])/', $key, -1, PREG_SPLIT_NO_EMPTY)));
                                @endphp

                                @if ($key === 'equityIndicator' && is_array($value))
                                    <div class="sidebar-section">
                                        <h3>{{ $label }}</h3>
                                        <div style="display: flex; align-items: center; margin: 2px 0;">
                                            @php
                                                $filledDots = $value['filled'] ?? 7;
                                                $totalDots = $value['total'] ?? 10;
                                            @endphp
                                            @for ($i = 0; $i < $totalDots; $i++)
                                                <span class="equity-dot {{ $i < $filledDots ? 'filled' : 'empty' }}"></span>
                                            @endfor
                                        </div>
                                        <p>
                                            <span x-data="editableField('sidebar.{{ $key }}.description', '{{ addslashes($value['description'] ?? '') }}')"
                                                  @click="editMode && startEdit()"
                                                  :class="editMode ? 'editable' : ''"
                                                  x-text="value"></span>
                                        </p>
                                    </div>
                                @elseif (!is_array($value))
                                    <div class="sidebar-section">
                                        <h3>{{ $label }}</h3>
                                        <p>
                                            <span x-data="editableField('sidebar.{{ $key }}', '{!! addslashes($value) !!}')"
                                                  @click="editMode && startEdit()"
                                                  :class="editMode ? 'editable' : ''"
                                                  x-html="value"></span>
                                        </p>
                                    </div>
                                @elseif (is_array($value) && isset($value['description']))
                                    <div class="sidebar-section">
                                        <h3>{{ $label }}</h3>
                                        <p>
                                            <span x-data="editableField('sidebar.{{ $key }}.description', '{{ addslashes($value['description'] ?? '') }}')"
                                                  @click="editMode && startEdit()"
                                                  :class="editMode ? 'editable' : ''"
                                                  x-text="value"></span>
                                        </p>
                                    </div>
                                @endif
                            @endif
                        @endforeach
                    @endif
                </div>

                <!-- Content Area -->
                <div class="content-area">
                    <!-- Asset Allocation Table -->
                    @if(isset($fund->data['mainContent']['assetAllocation']))
                        <div style="margin-bottom: 6px;">
                            <h3 class="section-heading">
                                <span x-data="editableField('mainContent.assetAllocation.title', '{{ $fund->data['mainContent']['assetAllocation']['title'] }}')"
                                      @click="editMode && startEdit()"
                                      :class="editMode ? 'editable' : ''"
                                      x-text="value"></span>
                            </h3>
                            <p class="section-subtitle">
                                <span x-data="editableField('mainContent.assetAllocation.subtitle', '{{ $fund->data['mainContent']['assetAllocation']['subtitle'] }}')"
                                      @click="editMode && startEdit()"
                                      :class="editMode ? 'editable' : ''"
                                      x-text="value"></span>
                            </p>
                            <div class="table-wrapper">
                                <table class="foord-table">
                                    <thead>
                                        <tr>
                                            @foreach ($fund->data['mainContent']['assetAllocation']['headers'] as $index => $header)
                                                <th class="{{ $loop->first ? 'text-left' : 'text-center' }}">
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
                                            <tr>
                                                <td>
                                                    <span x-data="editableField('mainContent.assetAllocation.rows.{{ $rowIndex }}.name', '{{ $row['name'] }}')"
                                                          @click="editMode && startEdit()"
                                                          :class="editMode ? 'editable' : ''"
                                                          x-text="value"></span>
                                                </td>
                                                <td>
                                                    <span x-data="editableField('mainContent.assetAllocation.rows.{{ $rowIndex }}.sa', '{{ $row['sa'] }}')"
                                                          @click="editMode && startEdit()"
                                                          :class="editMode ? 'editable' : ''"
                                                          x-text="value"></span>
                                                </td>
                                                <td>
                                                    <span x-data="editableField('mainContent.assetAllocation.rows.{{ $rowIndex }}.foreign', '{{ $row['foreign'] }}')"
                                                          @click="editMode && startEdit()"
                                                          :class="editMode ? 'editable' : ''"
                                                          x-text="value"></span>
                                                </td>
                                                <td>
                                                    <span x-data="editableField('mainContent.assetAllocation.rows.{{ $rowIndex }}.total', '{{ $row['total'] }}')"
                                                          @click="editMode && startEdit()"
                                                          :class="editMode ? 'editable' : ''"
                                                          x-text="value"></span>
                                                </td>
                                                <td>
                                                    @if(isset($row['changeDirection']))
                                                        <span class="{{ $row['changeDirection'] === 'up' ? 'change-up' : 'change-down' }}"></span>
                                                    @endif
                                                    <span x-data="editableField('mainContent.assetAllocation.rows.{{ $rowIndex }}.change', '{{ $row['change'] }}')"
                                                          @click="editMode && startEdit()"
                                                          :class="editMode ? 'editable' : ''"
                                                          x-text="value"></span>
                                                </td>
                                            </tr>
                                        @endforeach
                                        <tr class="total-row">
                                            <td class="font-medium">
                                                <span x-data="editableField('mainContent.assetAllocation.total.name', '{{ $fund->data['mainContent']['assetAllocation']['total']['name'] }}')"
                                                      @click="editMode && startEdit()"
                                                      :class="editMode ? 'editable' : ''"
                                                      x-text="value"></span>
                                            </td>
                                            <td class="font-medium">
                                                <span x-data="editableField('mainContent.assetAllocation.total.sa', '{{ $fund->data['mainContent']['assetAllocation']['total']['sa'] }}')"
                                                      @click="editMode && startEdit()"
                                                      :class="editMode ? 'editable' : ''"
                                                      x-text="value"></span>
                                            </td>
                                            <td class="font-medium">
                                                <span x-data="editableField('mainContent.assetAllocation.total.foreign', '{{ $fund->data['mainContent']['assetAllocation']['total']['foreign'] }}')"
                                                      @click="editMode && startEdit()"
                                                      :class="editMode ? 'editable' : ''"
                                                      x-text="value"></span>
                                            </td>
                                            <td class="font-medium">
                                                <span x-data="editableField('mainContent.assetAllocation.total.total', '{{ $fund->data['mainContent']['assetAllocation']['total']['total'] }}')"
                                                      @click="editMode && startEdit()"
                                                      :class="editMode ? 'editable' : ''"
                                                      x-text="value"></span>
                                            </td>
                                            <td></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    <!-- Top Investments Table -->
                    @if(isset($fund->data['mainContent']['topInvestments']))
                        <div style="margin-bottom: 6px;">
                            <h3 class="section-heading">
                                <span x-data="editableField('mainContent.topInvestments.title', '{{ $fund->data['mainContent']['topInvestments']['title'] }}')"
                                      @click="editMode && startEdit()"
                                      :class="editMode ? 'editable' : ''"
                                      x-text="value"></span>
                            </h3>
                            <div class="table-wrapper">
                                <table class="foord-table">
                                    <thead>
                                        <tr>
                                            @foreach ($fund->data['mainContent']['topInvestments']['headers'] as $index => $header)
                                                <th @if($index <= 1) style="text-align: left;" @endif>
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
                                            <tr class="{{ ($row['highlight'] ?? false) ? 'highlight-row' : '' }}">
                                                <td class="{{ ($row['highlight'] ?? false) ? 'text-naartjie font-medium' : '' }}">
                                                    <span x-data="editableField('mainContent.topInvestments.rows.{{ $rowIndex }}.security', '{{ $row['security'] }}')"
                                                          @click="editMode && startEdit()"
                                                          :class="editMode ? 'editable' : ''"
                                                          x-text="value"></span>
                                                </td>
                                                <td style="text-align: left;">
                                                    <span x-data="editableField('mainContent.topInvestments.rows.{{ $rowIndex }}.assetClass', '{{ $row['assetClass'] }}')"
                                                          @click="editMode && startEdit()"
                                                          :class="editMode ? 'editable' : ''"
                                                          x-text="value"></span>
                                                </td>
                                                <td>
                                                    <span x-data="editableField('mainContent.topInvestments.rows.{{ $rowIndex }}.market', '{{ $row['market'] }}')"
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

                    <!-- Charts Section: Two charts side by side -->
                    @if(isset($fund->data['mainContent']['charts']))
                        <div class="two-col" style="margin-bottom: 4px;">
                            <div class="col-left">
                                <h3 class="section-heading" style="font-size: 6.5pt;">
                                    <span x-data="editableField('mainContent.charts.leftTitle', '{{ $fund->data['mainContent']['charts']['leftTitle'] ?? 'INVESTMENT STRATEGY VS REG 28 PORTFOLIOS' }}')"
                                          @click="editMode && startEdit()"
                                          :class="editMode ? 'editable' : ''"
                                          x-text="value"></span>
                                </h3>
                                <div style="position: relative;">
                                    <div style="position: absolute; left: -2px; top: 40%; transform: rotate(-90deg) translateX(-50%); font-size: 5pt; color: var(--dark-grey); white-space: nowrap; transform-origin: left center;">Cash Value<sup>2</sup> (R'000)</div>
                                    <div style="padding-left: 10px;">
                                        <canvas id="strategyChart" style="height: 130px;"></canvas>
                                    </div>
                                </div>
                                <div class="chart-legend" style="gap: 8px;">
                                    <span><span class="legend-line" style="background: var(--naartjie);"></span> Fund</span>
                                    <span><span class="legend-line" style="background: var(--dark-navy);"></span> Foord Regulation 28</span>
                                </div>
                            </div>
                            <div class="col-right">
                                <h3 class="section-heading" style="font-size: 6.5pt;">
                                    <span x-data="editableField('mainContent.charts.rightTitle', '{{ $fund->data['mainContent']['charts']['rightTitle'] ?? 'PORTFOLIO PERFORMANCE VS BENCHMARK' }}')"
                                          @click="editMode && startEdit()"
                                          :class="editMode ? 'editable' : ''"
                                          x-text="value"></span>
                                </h3>
                                <div style="position: relative;">
                                    <div style="position: absolute; left: -2px; top: 40%; transform: rotate(-90deg) translateX(-50%); font-size: 5pt; color: var(--dark-grey); white-space: nowrap; transform-origin: left center;">Cash Value<sup>2</sup> (R'000)</div>
                                    <div style="padding-left: 10px;">
                                        <canvas id="portfolioChart" style="height: 130px;"></canvas>
                                    </div>
                                </div>
                                <div class="chart-legend" style="gap: 8px;">
                                    <span><span class="legend-line" style="background: var(--naartjie);"></span> Fund</span>
                                    <span><span class="legend-line" style="background: var(--dark-navy);"></span> Benchmark</span>
                                </div>
                            </div>
                        </div>

                        <!-- Chart explanation text -->
                        <p class="chart-explanation" style="margin-bottom: 6px;">
                            <span x-data="editableField('mainContent.charts.explanation', '{{ addslashes($fund->data['mainContent']['charts']['explanation'] ?? '') }}')"
                                  @click="editMode && startEdit()"
                                  :class="editMode ? 'editable' : ''"
                                  x-text="value"></span>
                        </p>
                    @endif

                    <!-- Performance Table -->
                    @if(isset($fund->data['mainContent']['performanceTable']))
                        <div>
                            <h3 class="section-heading" style="font-size: 6.5pt; line-height: 7.5pt;">
                                <span x-data="editableField('mainContent.performanceTable.title', '{!! addslashes($fund->data['mainContent']['performanceTable']['title']) !!}')"
                                      @click="editMode && startEdit()"
                                      :class="editMode ? 'editable' : ''"
                                      x-html="value"></span>
                            </h3>
                            <div class="table-wrapper">
                                <table class="foord-table perf-table">
                                    <thead>
                                        <tr>
                                            @foreach ($fund->data['mainContent']['performanceTable']['headers'] as $index => $header)
                                                <th>
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
                                            @if(empty($row['name']) && empty($row['cashValue']))
                                                <tr class="empty-row"><td colspan="{{ count($fund->data['mainContent']['performanceTable']['headers']) }}"></td></tr>
                                            @else
                                                <tr class="{{ ($row['highlight'] ?? false) ? 'highlight-row' : '' }}">
                                                    <td>
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
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Footnotes -->
                            @if(isset($fund->data['mainContent']['performanceTable']['footnotes']))
                                <div style="margin-top: 3px;">
                                    @foreach ($fund->data['mainContent']['performanceTable']['footnotes'] as $index => $footnote)
                                        <p class="footnote">
                                            <span x-data="editableField('mainContent.performanceTable.footnotes.{{ $index }}', '{!! addslashes($footnote) !!}')"
                                                  @click="editMode && startEdit()"
                                                  :class="editMode ? 'editable' : ''"
                                                  x-html="value"></span>
                                        </p>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- ==================== PAGE 2 ==================== -->
        <div class="page page-break" style="display: flex; flex-direction: column;">
            <!-- Beige header area (matches PDF page 2 top) -->
            <div style="display: flex;">
                <div style="width: 174px; min-width: 174px; background-color: var(--dark-navy-15); height: 55px;"></div>
                <div style="flex: 1; background-color: var(--mushroom-50); height: 55px;"></div>
            </div>
            <div class="main-body" style="flex: 1;">
                <!-- Left Sidebar - Important Information -->
                @if(isset($fund->data['importantInfo']))
                    <div class="sidebar" style="padding-top: 8px;">
                        <div class="important-info-header">
                            <h2>
                                <span x-data="editableField('importantInfo.title', '{{ $fund->data['importantInfo']['title'] }}')"
                                      @click="editMode && startEdit()"
                                      :class="editMode ? 'editable' : ''"
                                      x-text="value"></span>
                            </h2>
                        </div>
                        <div>
                            @foreach ($fund->data['importantInfo']['paragraphs'] as $index => $paragraph)
                                <p class="important-info-text">
                                    <span x-data="editableField('importantInfo.paragraphs.{{ $index }}', '{{ addslashes($paragraph) }}')"
                                          @click="editMode && startEdit()"
                                          :class="editMode ? 'editable' : ''"
                                          x-text="value"></span>
                                </p>
                            @endforeach
                            <p class="important-info-text" style="margin-top: 6px; font-weight: 400;">
                                <span x-data="editableField('importantInfo.publishedDate', '{{ $fund->data['importantInfo']['publishedDate'] }}')"
                                      @click="editMode && startEdit()"
                                      :class="editMode ? 'editable' : ''"
                                      x-text="value"></span>
                            </p>
                        </div>
                    </div>
                @endif

                <!-- Right Content -->
                <div class="content-area" style="padding-top: 16px; display: flex; flex-direction: column;">
                    <!-- Fee Rates -->
                    @if(isset($fund->data['fees']['feeRates']))
                        <div style="margin-bottom: 14px;">
                            <h3 class="page2-heading">
                                <span x-data="editableField('fees.feeRates.title', '{{ $fund->data['fees']['feeRates']['title'] }}')"
                                      @click="editMode && startEdit()"
                                      :class="editMode ? 'editable' : ''"
                                      x-text="value"></span>
                            </h3>
                            <table class="fee-table">
                                <tbody>
                                    @foreach ($fund->data['fees']['feeRates']['rates'] as $index => $rate)
                                        <tr>
                                            <td>
                                                <span x-data="editableField('fees.feeRates.rates.{{ $index }}.name', '{{ $rate['name'] }}')"
                                                      @click="editMode && startEdit()"
                                                      :class="editMode ? 'editable' : ''"
                                                      x-text="value"></span>
                                            </td>
                                            <td>
                                                <span x-data="editableField('fees.feeRates.rates.{{ $index }}.value', '{{ $rate['value'] }}')"
                                                      @click="editMode && startEdit()"
                                                      :class="editMode ? 'editable' : ''"
                                                      x-text="value"></span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <p class="page2-body" style="margin-top: 5px;">
                                <span x-data="editableField('fees.feeRates.description', '{{ addslashes($fund->data['fees']['feeRates']['description'] ?? '') }}')"
                                      @click="editMode && startEdit()"
                                      :class="editMode ? 'editable' : ''"
                                      x-text="value"></span>
                            </p>
                        </div>
                    @endif

                    <!-- Total Investment Charge -->
                    @if(isset($fund->data['fees']['totalInvestmentCharge']))
                        <div style="margin-bottom: 14px;">
                            <h3 class="page2-heading">
                                <span x-data="editableField('fees.totalInvestmentCharge.title', '{{ $fund->data['fees']['totalInvestmentCharge']['title'] }}')"
                                      @click="editMode && startEdit()"
                                      :class="editMode ? 'editable' : ''"
                                      x-text="value"></span>
                            </h3>
                            <div class="table-wrapper">
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
                            <p class="page2-body" style="margin-top: 5px;">
                                <span x-data="editableField('fees.totalInvestmentCharge.description', '{{ addslashes($fund->data['fees']['totalInvestmentCharge']['description'] ?? '') }}')"
                                      @click="editMode && startEdit()"
                                      :class="editMode ? 'editable' : ''"
                                      x-text="value"></span>
                            </p>
                        </div>
                    @endif

                    <!-- Performance Fees -->
                    @if(isset($fund->data['fees']['performanceFees']))
                        <div style="margin-bottom: 14px;">
                            <h3 class="page2-heading">
                                <span x-data="editableField('fees.performanceFees.title', '{{ $fund->data['fees']['performanceFees']['title'] }}')"
                                      @click="editMode && startEdit()"
                                      :class="editMode ? 'editable' : ''"
                                      x-text="value"></span>
                            </h3>
                            @foreach ($fund->data['fees']['performanceFees']['paragraphs'] as $index => $paragraph)
                                <p class="page2-body" style="margin-bottom: 6px;">
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
                        <div style="margin-bottom: 14px;">
                            <h3 class="page2-heading">
                                <span x-data="editableField('fees.performanceFeeExamples.title', '{{ $fund->data['fees']['performanceFeeExamples']['title'] }}')"
                                      @click="editMode && startEdit()"
                                      :class="editMode ? 'editable' : ''"
                                      x-text="value"></span>
                            </h3>
                            <div class="table-wrapper">
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
                            @if(isset($fund->data['fees']['performanceFeeExamples']['footnote']))
                                <p class="page2-note" style="margin-top: 3px;">
                                    <span x-data="editableField('fees.performanceFeeExamples.footnote', '{{ addslashes($fund->data['fees']['performanceFeeExamples']['footnote'] ?? '') }}')"
                                          @click="editMode && startEdit()"
                                          :class="editMode ? 'editable' : ''"
                                          x-text="value"></span>
                                </p>
                            @endif
                        </div>
                    @endif

                    <!-- Footer -->
                    @if(isset($fund->data['footer']))
                        <div class="footer-divider">
                            <p class="footer-info">
                                <span x-data="editableField('footer.info', '{{ addslashes($fund->data['footer']['info']) }}')"
                                      @click="editMode && startEdit()"
                                      :class="editMode ? 'editable' : ''"
                                      x-text="value"></span>
                            </p>
                            <p class="footer-info">
                                <span x-data="editableField('footer.freeOfCharge', '{{ $fund->data['footer']['freeOfCharge'] }}')"
                                      @click="editMode && startEdit()"
                                      :class="editMode ? 'editable' : ''"
                                      x-text="value"></span>
                            </p>
                            <div class="footer-contact" style="margin-top: 6px; position: relative;">
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
                                <!-- Foord feather icon -->
                                <svg style="position: absolute; right: 0; bottom: -5px; width: 28px; height: 35px; opacity: 0.7;" viewBox="0 0 28 35" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M14 0C8 3 3 10 2 18C1 26 3 35 3 35C3 35 6 30 10 28C14 26 18 27 18 27C18 27 14 22 10 20C6 18 2 18 2 18C2 18 4 12 8 8C12 4 14 0 14 0Z" fill="#c4956a"/>
                                    <path d="M14 0C20 3 25 10 26 18C27 26 25 35 25 35C25 35 22 30 18 28C14 26 10 27 10 27C10 27 14 22 18 20C22 18 26 18 26 18C26 18 24 12 20 8C16 4 14 0 14 0Z" fill="#b07850"/>
                                </svg>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== JAVASCRIPT ==================== -->
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

    @if(isset($fund->data['mainContent']['charts']))
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const strategyData = @json($fund->data['mainContent']['charts']['strategyData'] ?? []);
        const portfolioData = @json($fund->data['mainContent']['charts']['portfolioData'] ?? []);
        const strategyLabels = @json($fund->data['mainContent']['charts']['strategyLabels'] ?? ['Fund', 'Foord Regulation 28']);
        const portfolioLabels = @json($fund->data['mainContent']['charts']['portfolioLabels'] ?? ['Fund', 'Benchmark']);

        const colors = {
            naartjie: '#d25347',
            darkNavy: '#29363d',
            mediumGrey: '#9a9a9a',
            lightGrey: '#cccccc',
            darkGrey: '#535353'
        };

        // End value annotation plugin
        const endValuePlugin = {
            id: 'endValueAnnotation',
            afterDraw(chart) {
                const { ctx } = chart;
                chart.data.datasets.forEach((dataset, i) => {
                    const meta = chart.getDatasetMeta(i);
                    if (meta.hidden) return;
                    const lastPoint = meta.data[meta.data.length - 1];
                    if (!lastPoint) return;
                    const lastValue = dataset.data[dataset.data.length - 1];
                    const label = 'R ' + Math.round(lastValue).toLocaleString();
                    ctx.save();
                    ctx.font = 'bold 7px Avenir Next, Lato, sans-serif';
                    ctx.fillStyle = dataset.borderColor;
                    ctx.textAlign = 'right';
                    ctx.fillText(label, lastPoint.x + 2, lastPoint.y - 6);
                    ctx.restore();
                });
            }
        };

        Chart.register(endValuePlugin);

        // Investment Strategy vs Reg 28 Portfolios Chart
        const strategyCtx = document.getElementById('strategyChart').getContext('2d');
        new Chart(strategyCtx, {
            type: 'line',
            data: {
                labels: strategyData.map(item => item.date),
                datasets: [
                    {
                        label: strategyLabels[0],
                        data: strategyData.map(item => item.fund),
                        borderColor: colors.naartjie,
                        backgroundColor: 'transparent',
                        borderWidth: 1.5,
                        tension: 0.3,
                        pointRadius: 0
                    },
                    {
                        label: strategyLabels[1],
                        data: strategyData.map(item => item.comparison),
                        borderColor: colors.darkNavy,
                        backgroundColor: 'transparent',
                        borderWidth: 1.5,
                        tension: 0.3,
                        pointRadius: 0
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: { padding: { top: 15, right: 5 } },
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: { label: (c) => `${c.dataset.label}: R ${Math.round(c.parsed.y).toLocaleString()}` }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 6, family: 'Avenir Next, Lato, sans-serif' }, color: '#535353', maxRotation: 0, autoSkip: true, maxTicksLimit: 5 }
                    },
                    y: {
                        type: 'logarithmic',
                        min: 100,
                        ticks: {
                            font: { size: 6, family: 'Avenir Next, Lato, sans-serif' },
                            color: '#535353',
                            callback: (v) => {
                                if (v === 100 || v === 200 || v === 500 || v === 1000) return v;
                                return null;
                            }
                        },
                        grid: { color: '#f0f0f0', lineWidth: 0.5 }
                    }
                }
            }
        });

        // Portfolio Performance vs Benchmark Chart
        const portfolioCtx = document.getElementById('portfolioChart').getContext('2d');
        new Chart(portfolioCtx, {
            type: 'line',
            data: {
                labels: portfolioData.map(item => item.date),
                datasets: [
                    {
                        label: portfolioLabels[0],
                        data: portfolioData.map(item => item.fund),
                        borderColor: colors.naartjie,
                        backgroundColor: 'transparent',
                        borderWidth: 1.5,
                        tension: 0.3,
                        pointRadius: 0
                    },
                    {
                        label: portfolioLabels[1],
                        data: portfolioData.map(item => item.benchmark),
                        borderColor: colors.darkNavy,
                        backgroundColor: 'transparent',
                        borderWidth: 1.5,
                        tension: 0.3,
                        pointRadius: 0
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: { padding: { top: 15, right: 5 } },
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: { label: (c) => `${c.dataset.label}: R ${Math.round(c.parsed.y).toLocaleString()}` }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 6, family: 'Avenir Next, Lato, sans-serif' }, color: '#535353', maxRotation: 0, autoSkip: true, maxTicksLimit: 5 }
                    },
                    y: {
                        type: 'logarithmic',
                        min: 100,
                        ticks: {
                            font: { size: 6, family: 'Avenir Next, Lato, sans-serif' },
                            color: '#535353',
                            callback: (v) => {
                                if (v === 100 || v === 200 || v === 500 || v === 1000) return v;
                                return null;
                            }
                        },
                        grid: { color: '#f0f0f0', lineWidth: 0.5 }
                    }
                }
            }
        });
    </script>
    @endif
</body>
</html>
