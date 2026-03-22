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
            height: 50px;
            position: relative;
        }

        .header-grey {
            width: 174px;
            min-width: 174px;
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
            text-transform: uppercase;
            padding: 6px 14px;
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
            height: 40px;
            width: auto;
        }

        /* === Fund Name Banner === */
        .fund-banner {
            background-color: var(--dark-navy);
            color: white;
            padding: 14px 16px 12px 16px;
        }

        .fund-banner h1 {
            font-family: 'Avenir Next', system-ui, sans-serif;
            font-weight: 500;
            font-size: 22pt;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            margin-bottom: 6px;
            line-height: 1.1;
        }

        .fund-banner .description {
            font-family: 'Merriweather', Georgia, serif;
            font-weight: 400;
            font-size: 7.5pt;
            line-height: 10pt;
            letter-spacing: 0.02em;
            color: rgba(255,255,255,0.9);
            max-width: 95%;
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
            padding: 12px 12px 16px 16px;
        }

        .sidebar-section {
            margin-bottom: 5px;
        }

        .sidebar-section h3 {
            font-family: 'Avenir Next', system-ui, sans-serif;
            font-weight: 600;
            font-size: 6pt;
            line-height: 7.5pt;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            color: var(--dark-navy);
            margin-bottom: 1px;
        }

        .sidebar-section p,
        .sidebar-section .sidebar-value {
            font-family: 'Avenir Next', system-ui, sans-serif;
            font-weight: 400;
            font-size: 7pt;
            line-height: 8.5pt;
            letter-spacing: 0.02em;
            color: var(--off-black);
        }

        /* Equity indicator dots */
        .equity-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 1.5px;
        }
        .equity-dot.filled { background-color: var(--naartjie); }
        .equity-dot.empty { background-color: var(--medium-grey); }

        /* Lipper Award box */
        .lipper-award {
            margin-top: 8px;
            border: 1px solid var(--dark-navy-30);
            padding: 8px 6px;
            text-align: center;
        }

        .lipper-award .award-icon {
            font-size: 18px;
            margin-bottom: 2px;
        }

        .lipper-award .award-title {
            font-family: 'Avenir Next', system-ui, sans-serif;
            font-weight: 700;
            font-size: 7pt;
            line-height: 9pt;
            color: var(--dark-navy);
            text-transform: uppercase;
        }

        .lipper-award .award-winner {
            font-family: 'Avenir Next', system-ui, sans-serif;
            font-weight: 600;
            font-size: 6.5pt;
            line-height: 8pt;
            color: var(--dark-navy);
            margin-top: 2px;
        }

        .lipper-award .award-detail {
            font-family: 'Avenir Next', system-ui, sans-serif;
            font-weight: 400;
            font-size: 6pt;
            line-height: 7.5pt;
            color: var(--off-black);
            margin-top: 3px;
        }

        /* === Content Area === */
        .content-area {
            flex: 1;
            padding: 12px 16px 16px 14px;
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
            margin-bottom: 4px;
        }

        .section-subtitle {
            font-family: 'Avenir Next', system-ui, sans-serif;
            font-weight: 400;
            font-size: 6pt;
            line-height: 7pt;
            color: var(--dark-grey);
            margin-bottom: 4px;
        }

        /* === Two-Column Layout === */
        .two-col {
            display: flex;
            gap: 14px;
            margin-bottom: 10px;
        }

        .two-col .col-left {
            flex: 1;
            min-width: 0;
        }

        .two-col .col-right {
            flex: 1;
            min-width: 0;
        }

        /* === Asset Allocation Bars === */
        .alloc-row {
            display: flex;
            align-items: center;
            gap: 4px;
            font-family: 'Avenir Next', system-ui, sans-serif;
            font-size: 7.5pt;
            line-height: 13px;
            color: var(--off-black);
        }

        .alloc-label {
            width: 105px;
            text-align: right;
            padding-right: 4px;
            flex-shrink: 0;
            font-weight: 400;
        }

        .alloc-bar-container {
            flex: 1;
            height: 10px;
            background-color: var(--dark-navy-10);
            position: relative;
        }

        .alloc-bar {
            height: 10px;
            background-color: var(--dark-navy);
        }

        .alloc-value {
            width: 18px;
            text-align: right;
            flex-shrink: 0;
            font-weight: 500;
        }

        .alloc-change {
            width: 32px;
            text-align: right;
            flex-shrink: 0;
            font-size: 7pt;
        }

        .change-up { color: var(--off-black); }
        .change-down { color: var(--off-black); }
        .change-up::before { content: '▲ '; font-size: 5pt; }
        .change-down::before { content: '▼ '; font-size: 5pt; }

        /* === Equity Sector Bars === */
        .sector-row {
            display: flex;
            align-items: center;
            gap: 4px;
            font-family: 'Avenir Next', system-ui, sans-serif;
            font-size: 7.5pt;
            line-height: 12px;
            color: var(--off-black);
        }

        .sector-label {
            width: 130px;
            text-align: right;
            padding-right: 4px;
            flex-shrink: 0;
            font-weight: 400;
        }

        .sector-bar-container {
            flex: 1;
            height: 10px;
            position: relative;
        }

        .sector-bar {
            height: 10px;
            background-color: var(--dark-navy);
        }

        .sector-value {
            width: 18px;
            text-align: right;
            flex-shrink: 0;
            font-weight: 500;
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
            padding: 5px 6px;
            text-align: left;
            letter-spacing: 0.02em;
            border-right: 1px solid rgba(255,255,255,0.2);
        }

        .foord-table th:last-child {
            border-right: none;
        }

        .foord-table th:not(:first-child) {
            text-align: center;
        }

        .foord-table td {
            font-size: 7.5pt;
            line-height: 11pt;
            padding: 3.5px 6px;
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

        /* === Chart === */
        canvas {
            max-height: 170px;
            width: 100% !important;
        }

        /* === Footnotes === */
        .footnote {
            font-family: 'Lato', sans-serif;
            font-size: 5.5pt;
            line-height: 7pt;
            color: var(--dark-grey);
            letter-spacing: 0.01em;
        }

        /* === Page 2 === */
        .important-info-header {
            background-color: var(--dark-navy);
            color: white;
            padding: 8px 10px;
            margin-bottom: 10px;
        }

        .important-info-header h2 {
            font-family: 'Avenir Next', system-ui, sans-serif;
            font-weight: 500;
            font-size: 7.5pt;
            line-height: 8pt;
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
            margin-top: 14px;
            padding-top: 10px;
        }

        .footer-info {
            font-family: 'Merriweather', Georgia, serif;
            font-weight: 400;
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
            width: 16px;
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
                                'marketingCommunication' => 'MARKETING COMMUNICATION',
                                'subInvestmentManager' => 'SUB-INVESTMENT MANAGER',
                                'monthEndSharePrice' => 'MONTH END SHARE PRICE',
                                'morningstarCategory' => 'MORNINGSTAR CATEGORY',
                                'initialInvestmentAmount' => 'INITIAL INVESTMENT AMOUNT',
                                'totalFundSize' => 'TOTAL FUND SIZE',
                                'numberOfShares' => 'NUMBER OF SHARES',
                                'investmentManager' => 'INVESTMENT MANAGER',
                                'managementCompany' => 'MANAGEMENT COMPANY',
                                'fundManagers' => 'FUND MANAGERS',
                                'inceptionDate' => 'INCEPTION DATE',
                                'baseCurrency' => 'BASE CURRENCY',
                                'equityIndicator' => 'EQUITY INDICATOR',
                                'typeOfShares' => 'TYPE OF SHARES',
                                'timeHorizon' => 'TIME HORIZON',
                                'domicile' => 'DOMICILE',
                                'depository' => 'DEPOSITORY',
                                'isinNumber' => 'ISIN NUMBER',
                                'fees' => 'FEES',
                                'lipperAward' => 'REFINITIV LIPPER FUND AWARDS',
                            ];

                            // Define display order to match PDF
                            $displayOrder = [
                                'marketingCommunication', 'domicile', 'managementCompany', 'depository',
                                'investmentManager', 'subInvestmentManager', 'fundManagers',
                                'inceptionDate', 'baseCurrency', 'equityIndicator',
                                'morningstarCategory', 'typeOfShares', 'initialInvestmentAmount',
                                'totalFundSize', 'monthEndSharePrice', 'numberOfShares',
                                'timeHorizon', 'fees', 'isinNumber', 'lipperAward'
                            ];
                        @endphp

                        @foreach ($displayOrder as $key)
                            @if(isset($sidebar[$key]))
                                @php
                                    $value = $sidebar[$key];
                                    $label = $labelMap[$key] ?? strtoupper(implode(' ', preg_split('/(?=[A-Z])/', $key, -1, PREG_SPLIT_NO_EMPTY)));
                                @endphp

                                @if ($key === 'lipperAward' && is_array($value))
                                    <div class="lipper-award">
                                        <div class="award-title">REFINITIV LIPPER<br>FUND AWARDS</div>
                                        <div class="award-winner">{{ $value['year'] ?? '' }} WINNER<br>{{ $value['region'] ?? '' }}</div>
                                        <div class="award-detail">
                                            Refinitiv Lipper Awards {{ $value['year'] ?? '' }}<br>
                                            {{ $value['category'] ?? '' }}<br>
                                            {{ $value['type'] ?? '' }}
                                        </div>
                                    </div>
                                @elseif ($key === 'equityIndicator' && is_array($value))
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
                                @elseif ($key === 'marketingCommunication')
                                    <div class="sidebar-section" style="margin-bottom: 8px;">
                                        <h3 style="font-size: 6.5pt; font-weight: 700;">{{ $label }}</h3>
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
                    <!-- Two-column: Asset Allocation + Geographic Exposure -->
                    <div class="two-col">
                        <!-- Left: Asset Allocation -->
                        <div class="col-left">
                            @if(isset($fund->data['mainContent']['assetAllocation']))
                                <div style="margin-bottom: 14px;">
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
                                    <div>
                                        @foreach ($fund->data['mainContent']['assetAllocation']['rows'] as $rowIndex => $row)
                                            <div class="alloc-row">
                                                <span class="alloc-label">
                                                    <span x-data="editableField('mainContent.assetAllocation.rows.{{ $rowIndex }}.name', '{{ $row['name'] }}')"
                                                          @click="editMode && startEdit()"
                                                          :class="editMode ? 'editable' : ''"
                                                          x-text="value"></span>
                                                </span>
                                                <div class="alloc-bar-container">
                                                    <div class="alloc-bar" style="width: {{ $row['value'] }}%;"></div>
                                                </div>
                                                <span class="alloc-value">
                                                    <span x-data="editableField('mainContent.assetAllocation.rows.{{ $rowIndex }}.value', '{{ $row['value'] }}')"
                                                          @click="editMode && startEdit()"
                                                          :class="editMode ? 'editable' : ''"
                                                          x-text="value"></span>
                                                </span>
                                                <span class="alloc-change {{ ($row['changeDirection'] ?? '') === 'up' ? 'change-up' : (($row['changeDirection'] ?? '') === 'down' ? 'change-down' : '') }}">
                                                    <span x-data="editableField('mainContent.assetAllocation.rows.{{ $rowIndex }}.change', '{{ $row['change'] }}')"
                                                          @click="editMode && startEdit()"
                                                          :class="editMode ? 'editable' : ''"
                                                          x-text="value"></span>
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Equity Sector Allocation -->
                            @if(isset($fund->data['mainContent']['equitySectorAllocation']))
                                <div style="margin-bottom: 14px;">
                                    <h3 class="section-heading">
                                        <span x-data="editableField('mainContent.equitySectorAllocation.title', '{{ $fund->data['mainContent']['equitySectorAllocation']['title'] }}')"
                                              @click="editMode && startEdit()"
                                              :class="editMode ? 'editable' : ''"
                                              x-text="value"></span>
                                    </h3>
                                    <div>
                                        @foreach ($fund->data['mainContent']['equitySectorAllocation']['rows'] as $rowIndex => $row)
                                            <div class="sector-row">
                                                <span class="sector-label">
                                                    <span x-data="editableField('mainContent.equitySectorAllocation.rows.{{ $rowIndex }}.name', '{{ $row['name'] }}')"
                                                          @click="editMode && startEdit()"
                                                          :class="editMode ? 'editable' : ''"
                                                          x-text="value"></span>
                                                </span>
                                                <div class="sector-bar-container">
                                                    <div class="sector-bar" style="width: {{ ($row['value'] / 20) * 100 }}%;"></div>
                                                </div>
                                                <span class="sector-value">
                                                    <span x-data="editableField('mainContent.equitySectorAllocation.rows.{{ $rowIndex }}.value', '{{ $row['value'] }}')"
                                                          @click="editMode && startEdit()"
                                                          :class="editMode ? 'editable' : ''"
                                                          x-text="value"></span>
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Right: Geographic Exposure + Chart -->
                        <div class="col-right">
                            @if(isset($fund->data['mainContent']['geographicExposure']))
                                <div style="margin-bottom: 14px;">
                                    <h3 class="section-heading">
                                        <span x-data="editableField('mainContent.geographicExposure.title', '{{ $fund->data['mainContent']['geographicExposure']['title'] }}')"
                                              @click="editMode && startEdit()"
                                              :class="editMode ? 'editable' : ''"
                                              x-text="value"></span>
                                    </h3>
                                    <p class="section-subtitle">
                                        <span x-data="editableField('mainContent.geographicExposure.subtitle', '{{ $fund->data['mainContent']['geographicExposure']['subtitle'] ?? '' }}')"
                                              @click="editMode && startEdit()"
                                              :class="editMode ? 'editable' : ''"
                                              x-text="value"></span>
                                    </p>
                                    <div class="table-wrapper">
                                        <table class="foord-table">
                                            <thead>
                                                <tr>
                                                    @foreach ($fund->data['mainContent']['geographicExposure']['headers'] as $header)
                                                        <th>{{ $header }}</th>
                                                    @endforeach
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($fund->data['mainContent']['geographicExposure']['rows'] as $rowIndex => $row)
                                                    <tr>
                                                        <td>
                                                            <span x-data="editableField('mainContent.geographicExposure.rows.{{ $rowIndex }}.name', '{{ $row['name'] }}')"
                                                                  @click="editMode && startEdit()"
                                                                  :class="editMode ? 'editable' : ''"
                                                                  x-text="value"></span>
                                                        </td>
                                                        <td>
                                                            <span x-data="editableField('mainContent.geographicExposure.rows.{{ $rowIndex }}.total', '{{ $row['total'] }}')"
                                                                  @click="editMode && startEdit()"
                                                                  :class="editMode ? 'editable' : ''"
                                                                  x-text="value"></span>
                                                        </td>
                                                        <td>
                                                            <span x-data="editableField('mainContent.geographicExposure.rows.{{ $rowIndex }}.equity', '{{ $row['equity'] }}')"
                                                                  @click="editMode && startEdit()"
                                                                  :class="editMode ? 'editable' : ''"
                                                                  x-text="value"></span>
                                                        </td>
                                                        <td>
                                                            <span x-data="editableField('mainContent.geographicExposure.rows.{{ $rowIndex }}.cash', '{{ $row['cash'] }}')"
                                                                  @click="editMode && startEdit()"
                                                                  :class="editMode ? 'editable' : ''"
                                                                  x-text="value"></span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                <tr class="total-row">
                                                    <td>{{ $fund->data['mainContent']['geographicExposure']['total']['name'] }}</td>
                                                    <td>{{ $fund->data['mainContent']['geographicExposure']['total']['total'] }}</td>
                                                    <td>{{ $fund->data['mainContent']['geographicExposure']['total']['equity'] }}</td>
                                                    <td>{{ $fund->data['mainContent']['geographicExposure']['total']['cash'] }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif

                            <!-- Portfolio Performance Chart -->
                            @if(isset($fund->data['mainContent']['charts']))
                                <div>
                                    <h3 class="section-heading">
                                        <span x-data="editableField('mainContent.charts.title', '{{ $fund->data['mainContent']['charts']['title'] ?? 'PORTFOLIO PERFORMANCE' }}')"
                                              @click="editMode && startEdit()"
                                              :class="editMode ? 'editable' : ''"
                                              x-text="value"></span>
                                    </h3>
                                    <div style="position: relative;">
                                        <div style="position: absolute; left: -2px; top: 40%; transform: rotate(-90deg) translateX(-50%); font-size: 5.5pt; color: var(--dark-grey); white-space: nowrap; transform-origin: left center;">Cash Value ($'000)</div>
                                        <div style="padding-left: 10px;">
                                            <canvas id="performanceChart" style="height: 160px;"></canvas>
                                        </div>
                                    </div>
                                    <div class="chart-legend" style="flex-wrap: wrap; justify-content: center; gap: 4px 14px;">
                                        <span><span class="legend-line" style="background: var(--naartjie);"></span> Fund</span>
                                        <span><span class="legend-line" style="background: var(--medium-grey);"></span> US inflation</span>
                                        <span><span class="legend-line" style="background: var(--dark-navy);"></span> World equities</span>
                                        <span><span class="legend-line" style="background: var(--light-blue);"></span> World bonds</span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Top 10 Investments Table (full width) -->
                    @if(isset($fund->data['mainContent']['topInvestments']))
                        <div style="margin-bottom: 10px;">
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
                                                <th @if($index <= 1) style="text-align: left;" @endif>{{ $header }}</th>
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
                                                    @foreach (($fund->data['mainContent']['performanceTable']['columnKeys'] ?? []) as $colKey)
                                                        @php $cellValue = $row[$colKey] ?? ''; @endphp
                                                        <td>
                                                            <span x-data="editableField('mainContent.performanceTable.rows.{{ $rowIndex }}.{{ $colKey }}', '{{ $cellValue }}')"
                                                                  @click="editMode && startEdit()"
                                                                  :class="editMode ? 'editable' : ''"
                                                                  x-text="value"></span>
                                                        </td>
                                                    @endforeach
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Footnotes -->
                            @if(isset($fund->data['mainContent']['performanceTable']['footnotes']))
                                <div style="margin-top: 5px;">
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
        <div class="page page-break">
            <div class="main-body">
                <!-- Left Sidebar - Important Information -->
                @if(isset($fund->data['importantInfo']))
                    <div class="sidebar" style="padding-top: 16px;">
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
                <div class="content-area" style="padding-top: 16px;">
                    <!-- Annualised Cost Ratio -->
                    @if(isset($fund->data['fees']['annualisedCostRatio']))
                        <div style="margin-bottom: 14px;">
                            <h3 class="page2-heading">
                                <span x-data="editableField('fees.annualisedCostRatio.title', '{{ $fund->data['fees']['annualisedCostRatio']['title'] }}')"
                                      @click="editMode && startEdit()"
                                      :class="editMode ? 'editable' : ''"
                                      x-text="value"></span>
                            </h3>
                            <div class="table-wrapper">
                                <table class="foord-table">
                                    <thead>
                                        <tr>
                                            @foreach ($fund->data['fees']['annualisedCostRatio']['headers'] as $header)
                                                <th>{{ $header }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($fund->data['fees']['annualisedCostRatio']['rows'] as $rowIndex => $row)
                                            <tr>
                                                <td>
                                                    <span x-data="editableField('fees.annualisedCostRatio.rows.{{ $rowIndex }}.name', '{{ $row['name'] }}')"
                                                          @click="editMode && startEdit()"
                                                          :class="editMode ? 'editable' : ''"
                                                          x-text="value"></span>
                                                </td>
                                                <td>
                                                    <span x-data="editableField('fees.annualisedCostRatio.rows.{{ $rowIndex }}.12m', '{{ $row['12m'] }}')"
                                                          @click="editMode && startEdit()"
                                                          :class="editMode ? 'editable' : ''"
                                                          x-text="value"></span>
                                                </td>
                                                <td>
                                                    <span x-data="editableField('fees.annualisedCostRatio.rows.{{ $rowIndex }}.36m', '{{ $row['36m'] }}')"
                                                          @click="editMode && startEdit()"
                                                          :class="editMode ? 'editable' : ''"
                                                          x-text="value"></span>
                                                </td>
                                            </tr>
                                        @endforeach
                                        <tr class="total-row">
                                            <td>{{ $fund->data['fees']['annualisedCostRatio']['total']['name'] }}</td>
                                            <td>{{ $fund->data['fees']['annualisedCostRatio']['total']['12m'] }}</td>
                                            <td>{{ $fund->data['fees']['annualisedCostRatio']['total']['36m'] }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <p class="page2-body" style="margin-top: 5px;">
                                <span x-data="editableField('fees.annualisedCostRatio.description', '{{ addslashes($fund->data['fees']['annualisedCostRatio']['description'] ?? '') }}')"
                                      @click="editMode && startEdit()"
                                      :class="editMode ? 'editable' : ''"
                                      x-text="value"></span>
                            </p>
                        </div>
                    @endif

                    <!-- Share Pricing and Transactions -->
                    @if(isset($fund->data['page2Content']['sharePricing']))
                        <div style="margin-bottom: 14px;">
                            <h3 class="page2-heading">
                                <span x-data="editableField('page2Content.sharePricing.title', '{{ $fund->data['page2Content']['sharePricing']['title'] }}')"
                                      @click="editMode && startEdit()"
                                      :class="editMode ? 'editable' : ''"
                                      x-text="value"></span>
                            </h3>
                            <p class="page2-body">
                                <span x-data="editableField('page2Content.sharePricing.text', '{{ addslashes($fund->data['page2Content']['sharePricing']['text']) }}')"
                                      @click="editMode && startEdit()"
                                      :class="editMode ? 'editable' : ''"
                                      x-text="value"></span>
                            </p>
                        </div>
                    @endif

                    <!-- More About the Fund -->
                    @if(isset($fund->data['page2Content']['moreAboutFund']))
                        <div style="margin-bottom: 14px;">
                            <h3 class="page2-heading">
                                <span x-data="editableField('page2Content.moreAboutFund.title', '{{ $fund->data['page2Content']['moreAboutFund']['title'] }}')"
                                      @click="editMode && startEdit()"
                                      :class="editMode ? 'editable' : ''"
                                      x-text="value"></span>
                            </h3>
                            @foreach ($fund->data['page2Content']['moreAboutFund']['paragraphs'] as $index => $paragraph)
                                <p class="page2-body" style="margin-bottom: 6px;">
                                    <span x-data="editableField('page2Content.moreAboutFund.paragraphs.{{ $index }}', '{{ addslashes($paragraph) }}')"
                                          @click="editMode && startEdit()"
                                          :class="editMode ? 'editable' : ''"
                                          x-text="value"></span>
                                </p>
                            @endforeach
                        </div>
                    @endif

                    <!-- Refinitiv Lipper Fund Award -->
                    @if(isset($fund->data['page2Content']['lipperAward']))
                        <div style="margin-bottom: 14px;">
                            <h3 class="page2-heading">
                                <span x-data="editableField('page2Content.lipperAward.title', '{{ $fund->data['page2Content']['lipperAward']['title'] }}')"
                                      @click="editMode && startEdit()"
                                      :class="editMode ? 'editable' : ''"
                                      x-text="value"></span>
                            </h3>
                            <p class="page2-body">
                                <span x-data="editableField('page2Content.lipperAward.text', '{{ addslashes($fund->data['page2Content']['lipperAward']['text']) }}')"
                                      @click="editMode && startEdit()"
                                      :class="editMode ? 'editable' : ''"
                                      x-text="value"></span>
                            </p>
                        </div>
                    @endif

                    <!-- Notes -->
                    @if(isset($fund->data['page2Content']['notes']))
                        <div style="margin-bottom: 14px;">
                            <h3 class="page2-heading">
                                <span x-data="editableField('page2Content.notes.title', '{{ $fund->data['page2Content']['notes']['title'] }}')"
                                      @click="editMode && startEdit()"
                                      :class="editMode ? 'editable' : ''"
                                      x-text="value"></span>
                            </h3>
                            <div>
                                @foreach ($fund->data['page2Content']['notes']['items'] as $index => $note)
                                    <p class="page2-note">
                                        <span x-data="editableField('page2Content.notes.items.{{ $index }}', '{!! addslashes($note) !!}')"
                                              @click="editMode && startEdit()"
                                              :class="editMode ? 'editable' : ''"
                                              x-html="value"></span>
                                    </p>
                                @endforeach
                            </div>
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
                            <div class="footer-contact" style="margin-top: 6px;">
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
        @php
            $chartPerformanceData = $fund->data['mainContent']['charts']['performanceData'] ?? [];
        @endphp
        const chartData = @json($chartPerformanceData);

        const colors = {
            naartjie: '#d25347',
            darkNavy: '#29363d',
            mediumGrey: '#9a9a9a',
            lightBlue: '#7a9cb4'
        };

        const lineColors = [colors.naartjie, colors.mediumGrey, colors.darkNavy, colors.lightBlue];

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
                    const label = '$ ' + Math.round(lastValue).toLocaleString();
                    ctx.save();
                    ctx.font = 'bold 7px Avenir Next, Lato, sans-serif';
                    ctx.fillStyle = dataset.borderColor;
                    ctx.textAlign = 'left';
                    ctx.fillText(label, lastPoint.x + 4, lastPoint.y - 3);
                    ctx.restore();
                });
            }
        };

        Chart.register(endValuePlugin);

        const ctx = document.getElementById('performanceChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.map(d => d.date),
                datasets: [
                    {
                        label: 'Fund',
                        data: chartData.map(d => d.fund),
                        borderColor: colors.naartjie,
                        borderWidth: 1.5,
                        pointRadius: 0,
                        tension: 0.3,
                        fill: false
                    },
                    {
                        label: 'US inflation',
                        data: chartData.map(d => d.usInflation),
                        borderColor: colors.mediumGrey,
                        borderWidth: 1.5,
                        pointRadius: 0,
                        tension: 0.3,
                        fill: false
                    },
                    {
                        label: 'World equities',
                        data: chartData.map(d => d.worldEquities),
                        borderColor: colors.darkNavy,
                        borderWidth: 1.5,
                        pointRadius: 0,
                        tension: 0.3,
                        fill: false
                    },
                    {
                        label: 'World bonds',
                        data: chartData.map(d => d.worldBonds),
                        borderColor: colors.lightBlue,
                        borderWidth: 1.5,
                        pointRadius: 0,
                        tension: 0.3,
                        fill: false
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { enabled: true }
                },
                scales: {
                    x: {
                        display: true,
                        grid: { display: false },
                        ticks: {
                            font: { size: 6, family: 'Avenir Next, Lato, sans-serif' },
                            color: '#535353',
                            maxRotation: 0,
                            autoSkip: true,
                            maxTicksLimit: 8
                        }
                    },
                    y: {
                        display: true,
                        grid: { color: '#f0f0f0', lineWidth: 0.5 },
                        ticks: {
                            font: { size: 6, family: 'Avenir Next, Lato, sans-serif' },
                            color: '#535353'
                        },
                        min: 100,
                        beginAtZero: false
                    }
                },
                layout: {
                    padding: { right: 40, top: 10 }
                }
            }
        });
    </script>
    @endif
</body>
</html>
