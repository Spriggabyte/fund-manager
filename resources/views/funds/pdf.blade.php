<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $fund->data['fund']['name'] ?? $fund->name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;500;700&family=Merriweather:wght@400;700&display=swap" rel="stylesheet">
    <style>
        /* =====================================================
           FOORD FUND FACT SHEET - PDF TEMPLATE
           Optimized for 2-page A4 layout
           ===================================================== */

        /* Foord Brand Colors */
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

        /* Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* A4 Page Setup */
        @page {
            size: A4 portrait;
            margin: 0;
        }

        html, body {
            width: 210mm;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Lato', 'Avenir Next', -apple-system, sans-serif;
            font-size: 7pt;
            line-height: 1.2;
            color: var(--off-black);
            background: var(--white);
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* Page Container - with left/right margins */
        .page {
            width: 210mm;
            height: 297mm;
            max-height: 297mm;
            overflow: hidden;
            padding: 8mm 12mm 8mm 12mm;
            position: relative;
            page-break-after: always;
            background: var(--white);
        }

        .page:last-child {
            page-break-after: auto;
        }

        /* =====================================================
           HEADER SECTION
           ===================================================== */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 3mm;
        }

        .date-badge {
            background-color: var(--naartjie);
            color: var(--white);
            padding: 1.5mm 3mm;
            font-family: 'Lato', sans-serif;
            font-weight: 500;
            font-size: 9pt;
            letter-spacing: 0.03em;
            text-transform: uppercase;
        }

        .logo {
            height: 10mm;
        }

        .logo img {
            height: 100%;
            width: auto;
        }

        /* =====================================================
           TITLE BANNER
           ===================================================== */
        .title-banner {
            background-color: var(--dark-navy);
            color: var(--white);
            padding: 4mm 5mm;
            margin-bottom: 0;
            margin-left: -12mm;
            margin-right: -12mm;
            width: calc(100% + 24mm);
        }

        .fund-name {
            font-family: 'Lato', sans-serif;
            font-weight: 500;
            font-size: 18pt;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            margin: 0 0 1.5mm 0;
            line-height: 1.1;
        }

        .fund-name .class-suffix {
            font-weight: 400;
            font-size: 14pt;
        }

        .fund-description {
            font-family: 'Merriweather', Georgia, serif;
            font-weight: 400;
            font-size: 8pt;
            line-height: 10pt;
            letter-spacing: 0.02em;
            margin: 0;
            opacity: 0.95;
        }

        /* =====================================================
           MAIN CONTENT LAYOUT
           ===================================================== */
        .content-wrapper {
            display: flex;
            flex-direction: row;
            margin-left: -12mm;
            margin-right: -12mm;
            width: calc(100% + 24mm);
            height: calc(297mm - 8mm - 8mm - 10mm - 28mm); /* page height - top padding - bottom padding - header - title */
        }

        .page-2 .content-wrapper {
            height: calc(297mm - 8mm - 8mm); /* page height - top padding - bottom padding */
        }

        /* Sidebar - 46mm width */
        .sidebar {
            width: 46mm;
            min-width: 46mm;
            max-width: 46mm;
            background-color: var(--dark-navy-15);
            padding: 4mm;
            overflow: hidden;
        }

        .sidebar-section {
            margin-bottom: 2mm;
        }

        .sidebar-section:last-child {
            margin-bottom: 0;
        }

        .sidebar-heading {
            font-family: 'Lato', sans-serif;
            font-weight: 600;
            font-size: 5.5pt;
            line-height: 6.5pt;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            color: var(--dark-navy);
            margin: 0 0 0.3mm 0;
        }

        .sidebar-text {
            font-family: 'Lato', sans-serif;
            font-weight: 400;
            font-size: 6.5pt;
            line-height: 7.5pt;
            letter-spacing: 0.01em;
            color: var(--off-black);
            margin: 0;
        }

        /* Equity Indicator Dots */
        .equity-indicator {
            display: flex;
            gap: 0.8mm;
            margin: 1mm 0;
        }

        .equity-dot {
            width: 1.4mm;
            height: 1.4mm;
            border-radius: 50%;
        }

        .equity-dot.filled {
            background-color: var(--naartjie);
        }

        .equity-dot.empty {
            background-color: var(--medium-grey);
        }

        /* Main Content Area */
        .main-content {
            flex: 1;
            padding: 4mm;
            min-width: 0;
            overflow: hidden;
        }

        /* =====================================================
           SECTION HEADINGS
           ===================================================== */
        .section-heading {
            font-family: 'Lato', sans-serif;
            font-weight: 600;
            font-size: 7pt;
            line-height: 8pt;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            color: var(--off-black);
            margin: 0 0 1mm 0;
        }

        .section-subheading {
            font-family: 'Lato', sans-serif;
            font-weight: 400;
            font-size: 5.5pt;
            line-height: 6pt;
            letter-spacing: 0.01em;
            color: var(--dark-grey);
            margin: -0.5mm 0 1mm 0;
        }

        /* =====================================================
           TABLES
           ===================================================== */
        .table-container {
            position: relative;
            margin-bottom: 2.5mm;
        }

        /* Naartjie accent bar on left */
        .table-container::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 0.8mm;
            background-color: var(--naartjie);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-left: 0.8mm;
            font-size: 6.5pt;
        }

        table th {
            background-color: var(--dark-navy);
            color: var(--white);
            font-family: 'Lato', sans-serif;
            font-weight: 500;
            font-size: 6pt;
            line-height: 6.5pt;
            letter-spacing: 0;
            text-transform: uppercase;
            text-align: center;
            padding: 1.5mm 1.5mm;
            border-right: 0.5pt solid var(--white);
        }

        table th:first-child {
            text-align: left;
        }

        table th:last-child {
            border-right: none;
        }

        table td {
            font-family: 'Lato', sans-serif;
            font-weight: 400;
            font-size: 6.5pt;
            line-height: 8pt;
            padding: 1mm 1.5mm;
            text-align: center;
            border-bottom: 0.25pt solid #e5e5e5;
        }

        table td:first-child {
            text-align: left;
        }

        /* Alternating row colors */
        table tbody tr:nth-child(odd) {
            background-color: var(--very-light-grey);
        }

        table tbody tr:nth-child(even) {
            background-color: var(--white);
        }

        /* Highlighted Foord fund rows */
        table tbody tr.highlight-row {
            background-color: var(--naartjie-20) !important;
        }

        table tbody tr.highlight-row td:first-child {
            color: var(--naartjie);
            font-weight: 500;
        }

        /* Total row */
        table tbody tr.total-row,
        table tfoot tr {
            background-color: var(--naartjie) !important;
            color: var(--white);
        }

        table tbody tr.total-row td,
        table tfoot td {
            font-weight: 500;
            color: var(--white);
            border-bottom: none;
        }

        /* Change indicators */
        .change-up {
            color: var(--naartjie);
        }

        .change-down {
            color: var(--dark-navy);
        }

        /* =====================================================
           CHARTS SECTION
           ===================================================== */
        .charts-row {
            display: flex;
            gap: 3mm;
            margin: 2mm 0;
        }

        .chart-container {
            flex: 1;
            min-width: 0;
        }

        .chart-title {
            font-family: 'Lato', sans-serif;
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

        .chart-wrapper canvas {
            width: 100% !important;
            height: 100% !important;
        }

        .chart-explanation {
            font-family: 'Lato', sans-serif;
            font-weight: 400;
            font-size: 5.5pt;
            line-height: 7pt;
            letter-spacing: 0.01em;
            color: var(--dark-grey);
            margin: 1.5mm 0 2mm 0;
        }

        /* =====================================================
           FOOTNOTES
           ===================================================== */
        .footnotes {
            font-family: 'Lato', sans-serif;
            font-weight: 400;
            font-size: 5pt;
            line-height: 6pt;
            letter-spacing: 0.01em;
            color: var(--dark-grey);
            margin-top: 1mm;
        }

        .footnotes p {
            margin: 0.3mm 0;
        }

        .footnotes sup {
            font-size: 4pt;
            vertical-align: super;
        }

        /* =====================================================
           PAGE 2 - IMPORTANT INFO SIDEBAR
           ===================================================== */
        .info-sidebar {
            width: 46mm;
            min-width: 46mm;
            max-width: 46mm;
            background-color: var(--dark-navy-15);
            padding: 0;
            overflow: hidden;
        }

        .info-sidebar-header {
            background-color: var(--dark-navy);
            color: var(--white);
            padding: 2.5mm 4mm;
            margin-bottom: 0;
        }

        .info-sidebar-header h2 {
            font-family: 'Lato', sans-serif;
            font-weight: 500;
            font-size: 6.5pt;
            line-height: 7.5pt;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            margin: 0;
        }

        .info-sidebar-content {
            padding: 3mm 4mm;
        }

        .info-sidebar-content p {
            font-family: 'Lato', sans-serif;
            font-weight: 400;
            font-size: 5pt;
            line-height: 6pt;
            letter-spacing: 0.01em;
            color: var(--off-black);
            margin: 0 0 1.5mm 0;
            text-align: justify;
        }

        .info-sidebar-content p:last-child {
            margin-bottom: 0;
        }

        /* =====================================================
           PAGE 2 - FEES SECTION
           ===================================================== */
        .fees-content {
            flex: 1;
            padding: 4mm;
            overflow: hidden;
        }

        .fee-rates-table {
            margin-bottom: 2.5mm;
        }

        .fee-rates-table table {
            margin-left: 0.8mm;
        }

        .fee-rates-table td {
            padding: 1mm 1.5mm;
            border-bottom: 0.25pt solid #e5e5e5;
        }

        .fee-rates-table td:last-child {
            text-align: right;
            font-weight: 500;
        }

        .fee-rates-table tr.sub-item td:first-child {
            padding-left: 3mm;
        }

        .fee-rates-table tr.global-funds-header td {
            background-color: var(--naartjie-20);
            color: var(--naartjie);
            font-weight: 500;
        }

        .fee-description {
            font-family: 'Lato', sans-serif;
            font-weight: 400;
            font-size: 5.5pt;
            line-height: 7pt;
            color: var(--dark-grey);
            margin: 1.5mm 0 2.5mm 0;
        }

        /* =====================================================
           FOOTER
           ===================================================== */
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
            font-family: 'Lato', sans-serif;
            font-weight: 500;
            font-size: 7pt;
            line-height: 9pt;
            letter-spacing: 0.02em;
            color: var(--naartjie);
        }

        .footer-contact p {
            margin: 0;
        }

        /* =====================================================
           UTILITY CLASSES
           ===================================================== */
        .text-naartjie { color: var(--naartjie); }
        .text-navy { color: var(--dark-navy); }
        .bg-naartjie { background-color: var(--naartjie); }
        .bg-navy { background-color: var(--dark-navy); }
        .font-medium { font-weight: 500; }

        /* Print optimizations */
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .page {
                page-break-after: always;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <!-- PAGE 1 -->
    <div class="page">
        <!-- Header -->
        <div class="header">
            <div class="date-badge">
                {{ strtoupper($fund->data['fund']['date'] ?? now()->format('d F Y')) }}
            </div>
            <div class="logo">
                <img src="{{ $fund->data['fund']['logoUrl'] ?? 'https://foord.co.za/themes/custom/mirum/logo.png' }}" alt="FOORD">
            </div>
        </div>

        <!-- Title Banner -->
        <div class="title-banner">
            @php
                $fundName = $fund->data['fund']['name'] ?? $fund->name;
                // Split the fund name and class if present
                if (preg_match('/^(.+?)\s*[-—]\s*(CLASS\s+[A-Z])$/i', $fundName, $matches)) {
                    $mainName = trim($matches[1]);
                    $classText = '— ' . strtoupper(trim($matches[2]));
                } else {
                    $mainName = $fundName;
                    $classText = '';
                }
            @endphp
            <h1 class="fund-name">
                {{ strtoupper($mainName) }}
                @if($classText)
                    <span class="class-suffix">{{ $classText }}</span>
                @endif
            </h1>
            <p class="fund-description">{{ $fund->data['fund']['description'] ?? '' }}</p>
        </div>

        <!-- Main Content -->
        <div class="content-wrapper">
            <!-- Sidebar -->
            <div class="sidebar">
                @php
                    // Define the exact order from the reference PDF
                    $sidebarOrder = [
                        'domicile',
                        'managementCompany',
                        'fundManagers',
                        'inceptionDate',
                        'baseCurrency',
                        'equityIndicator',
                        'category',
                        'benchmark',
                        'minimums',
                        'portfolioSize',
                        'unitPrice',
                        'numberOfUnits',
                        'lastDistributions',
                        'incomeDistributions',
                        'incomeCharacteristics',
                        'portfolioOrientation',
                        'significantRestrictions',
                        'foreignAssets',
                        'riskOfLoss',
                        'timeHorizon',
                        'isinNumber'
                    ];

                    $sidebar = $fund->data['sidebar'] ?? [];

                    // Label mapping
                    $labels = [
                        'domicile' => 'DOMICILE',
                        'managementCompany' => 'MANAGEMENT COMPANY',
                        'fundManagers' => 'FUND MANAGERS',
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
                        'isinNumber' => 'ISIN NUMBER'
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
                                    <p class="sidebar-text">{!! $value['description'] !!}</p>
                                @endif
                            @elseif (is_array($value))
                                <p class="sidebar-text">{!! $value['description'] ?? '' !!}</p>
                            @else
                                <p class="sidebar-text">{!! $value !!}</p>
                            @endif
                        </div>
                    @endif
                @endforeach
            </div>

            <!-- Main Content -->
            <div class="main-content">
                <!-- Asset Allocation Table -->
                @if(isset($fund->data['mainContent']['assetAllocation']))
                    <h3 class="section-heading">{{ $fund->data['mainContent']['assetAllocation']['title'] ?? 'ASSET ALLOCATION % (MAX LIMITS IN BRACKETS)' }}</h3>
                    @if(isset($fund->data['mainContent']['assetAllocation']['subtitle']))
                        <p class="section-subheading">{{ $fund->data['mainContent']['assetAllocation']['subtitle'] }}</p>
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
                            <tbody>
                                @foreach ($fund->data['mainContent']['assetAllocation']['rows'] as $row)
                                    <tr>
                                        <td>{{ $row['name'] }}</td>
                                        <td>{{ $row['sa'] }}</td>
                                        <td>{{ $row['foreign'] }}</td>
                                        <td>{{ $row['total'] }}</td>
                                        <td class="{{ ($row['changeDirection'] ?? '') === 'up' ? 'change-up' : (($row['changeDirection'] ?? '') === 'down' ? 'change-down' : '') }}">
                                            @if(isset($row['changeDirection']))
                                                {{ $row['changeDirection'] === 'up' ? '▲' : '▼' }}
                                            @endif
                                            {{ $row['change'] ?? '' }}
                                        </td>
                                    </tr>
                                @endforeach
                                <tr class="total-row">
                                    <td>{{ $fund->data['mainContent']['assetAllocation']['total']['name'] ?? 'TOTAL' }}</td>
                                    <td>{{ $fund->data['mainContent']['assetAllocation']['total']['sa'] }}</td>
                                    <td>{{ $fund->data['mainContent']['assetAllocation']['total']['foreign'] }}</td>
                                    <td>{{ $fund->data['mainContent']['assetAllocation']['total']['total'] }}</td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                @endif

                <!-- Top 10 Investments -->
                @if(isset($fund->data['mainContent']['topInvestments']))
                    <h3 class="section-heading">{{ $fund->data['mainContent']['topInvestments']['title'] ?? 'TOP 10 INVESTMENTS' }}</h3>

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
                                @foreach ($fund->data['mainContent']['topInvestments']['rows'] as $row)
                                    <tr class="{{ ($row['highlight'] ?? false) ? 'highlight-row' : '' }}">
                                        <td>{{ $row['security'] }}</td>
                                        <td>{{ $row['assetClass'] }}</td>
                                        <td>{{ $row['market'] }}</td>
                                        <td>{{ $row['percentage'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                <!-- Charts -->
                @if(isset($fund->data['mainContent']['charts']))
                    <div class="charts-row">
                        <div class="chart-container">
                            <h4 class="chart-title">INVESTMENT STRATEGY VS SA INFLATION</h4>
                            <div class="chart-wrapper">
                                <canvas id="inflationChart"></canvas>
                            </div>
                        </div>
                        <div class="chart-container">
                            <h4 class="chart-title">PORTFOLIO PERFORMANCE VS BENCHMARK</h4>
                            <div class="chart-wrapper">
                                <canvas id="portfolioChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <p class="chart-explanation">
                        In managing retirement portfolios, Foord aims to achieve returns that exceed inflation plus 5% per annum over any rolling five-year period. The chart illustrates that a composite return of similarly managed portfolios over any rolling five-year period has only once dipped below the South African inflation rate. It also demonstrates that real returns of 5% per annum are consistently achievable in mandates of this nature when measured over the appropriate long-term period.
                    </p>
                @endif

                <!-- Performance Table -->
                @if(isset($fund->data['mainContent']['performanceTable']))
                    <h3 class="section-heading">{{ $fund->data['mainContent']['performanceTable']['title'] ?? 'PORTFOLIO PERFORMANCE % (PERIODS GREATER THAN ONE YEAR ARE ANNUALISED)' }}</h3>

                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    @foreach ($fund->data['mainContent']['performanceTable']['headers'] as $header)
                                        <th>{!! $header !!}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($fund->data['mainContent']['performanceTable']['rows'] as $row)
                                    <tr class="{{ ($row['highlight'] ?? false) ? 'highlight-row' : '' }}">
                                        <td>{!! $row['name'] !!}</td>
                                        <td>{{ $row['cashValue'] ?? '' }}</td>
                                        <td>{{ $row['sinceInception'] ?? '' }}</td>
                                        @if(isset($row['15yrs']))
                                            <td>{{ $row['15yrs'] }}</td>
                                        @endif
                                        <td>{{ $row['10yrs'] ?? '' }}</td>
                                        <td>{{ $row['7yrs'] ?? '' }}</td>
                                        <td>{{ $row['5yrs'] ?? '' }}</td>
                                        <td>{{ $row['3yrs'] ?? '' }}</td>
                                        <td>{{ $row['1yr'] ?? '' }}</td>
                                        <td>{{ $row['thisMonth'] ?? '' }}</td>
                                    </tr>
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
                        @foreach ($fund->data['importantInfo']['paragraphs'] as $paragraph)
                            <p>{{ $paragraph }}</p>
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
                    <h3 class="section-heading">{{ $fund->data['fees']['feeRates']['title'] ?? 'FEE RATES' }}</h3>

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
                                        <tr class="sub-item">
                                            <td>- {{ $gfund['name'] }}</td>
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
                    <h3 class="section-heading">{{ $fund->data['fees']['totalInvestmentCharge']['title'] ?? 'TOTAL INVESTMENT CHARGE %' }}</h3>

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
                                        <td>{{ $row['12m'] }}</td>
                                        <td>{{ $row['36m'] }}</td>
                                    </tr>
                                @endforeach
                                <tr class="total-row">
                                    <td>{{ $fund->data['fees']['totalInvestmentCharge']['total']['name'] ?? 'Total investment charge' }}</td>
                                    <td>{{ $fund->data['fees']['totalInvestmentCharge']['total']['12m'] }}</td>
                                    <td>{{ $fund->data['fees']['totalInvestmentCharge']['total']['36m'] }}</td>
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
                    <h3 class="section-heading">{{ $fund->data['fees']['performanceFees']['title'] ?? 'PERFORMANCE FEES' }}</h3>
                    @foreach ($fund->data['fees']['performanceFees']['paragraphs'] as $paragraph)
                        <p class="fee-description">{{ $paragraph }}</p>
                    @endforeach
                @endif

                <!-- Performance Fee Examples -->
                @if(isset($fund->data['fees']['performanceFeeExamples']))
                    <h3 class="section-heading">{{ $fund->data['fees']['performanceFeeExamples']['title'] ?? 'PERFORMANCE FEE EXAMPLES %' }}</h3>

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
                                        <td>{{ $row['a'] }}</td>
                                        <td>{{ $row['b'] }}</td>
                                        <td>{{ $row['c'] }}</td>
                                        <td>{{ $row['d'] }}</td>
                                    </tr>
                                @endforeach
                                <tr class="total-row">
                                    <td>{{ $fund->data['fees']['performanceFeeExamples']['total']['name'] ?? 'Annual fee rate applied (excl. VAT)' }}</td>
                                    <td>{{ $fund->data['fees']['performanceFeeExamples']['total']['a'] }}</td>
                                    <td>{{ $fund->data['fees']['performanceFeeExamples']['total']['b'] }}</td>
                                    <td>{{ $fund->data['fees']['performanceFeeExamples']['total']['c'] }}</td>
                                    <td>{!! $fund->data['fees']['performanceFeeExamples']['total']['d'] !!}</td>
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

    <!-- Chart.js -->
    @if(isset($fund->data['mainContent']['charts']))
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const inflationData = @json($fund->data['mainContent']['charts']['inflationData'] ?? []);
            const portfolioData = @json($fund->data['mainContent']['charts']['portfolioData'] ?? []);

            // Foord color palette
            const colors = {
                naartjie: '#d25347',
                darkNavy: '#29363d',
                darkNavy70: '#697277',
                mediumGrey: '#9a9a9a',
                lightGrey: '#cccccc',
                darkGrey: '#535353'
            };

            Chart.defaults.font.family = "'Lato', sans-serif";
            Chart.defaults.font.size = 6;

            // Investment Strategy vs SA Inflation Chart
            if (inflationData.length > 0) {
                const inflationCtx = document.getElementById('inflationChart').getContext('2d');
                new Chart(inflationCtx, {
                    type: 'line',
                    data: {
                        labels: inflationData.map(item => item.date),
                        datasets: [
                            {
                                label: 'Composite',
                                data: inflationData.map(item => item.composite),
                                borderColor: colors.naartjie,
                                backgroundColor: 'transparent',
                                borderWidth: 1,
                                tension: 0.1,
                                pointRadius: 0,
                                order: 1
                            },
                            {
                                label: 'Excess',
                                data: inflationData.map(item => item.excess),
                                borderColor: 'transparent',
                                backgroundColor: colors.darkNavy,
                                fill: '+2',
                                order: 2
                            },
                            {
                                label: 'Inflation',
                                data: inflationData.map(item => item.inflation),
                                borderColor: 'transparent',
                                backgroundColor: colors.mediumGrey,
                                fill: '+1',
                                order: 3
                            },
                            {
                                label: '5% Hurdle',
                                data: inflationData.map(item => item.hurdle),
                                borderColor: 'transparent',
                                backgroundColor: colors.lightGrey,
                                fill: 'origin',
                                order: 4
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    usePointStyle: true,
                                    boxWidth: 3,
                                    padding: 6,
                                    font: { size: 5 }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: {
                                    font: { size: 5 },
                                    maxRotation: 0,
                                    autoSkip: true,
                                    maxTicksLimit: 5
                                }
                            },
                            y: {
                                min: -10,
                                max: 35,
                                ticks: {
                                    stepSize: 10,
                                    font: { size: 5 },
                                    callback: (v) => v + '%'
                                },
                                grid: { color: '#e5e5e5' }
                            }
                        }
                    }
                });
            }

            // Portfolio Performance vs Benchmark Chart
            if (portfolioData.length > 0) {
                const portfolioCtx = document.getElementById('portfolioChart').getContext('2d');
                new Chart(portfolioCtx, {
                    type: 'line',
                    data: {
                        labels: portfolioData.map(item => item.date),
                        datasets: [
                            {
                                label: 'Fund',
                                data: portfolioData.map(item => item.fund),
                                borderColor: colors.naartjie,
                                backgroundColor: 'transparent',
                                borderWidth: 1,
                                tension: 0.1,
                                pointRadius: 0
                            },
                            {
                                label: 'Benchmark',
                                data: portfolioData.map(item => item.benchmark),
                                borderColor: colors.darkNavy,
                                backgroundColor: 'transparent',
                                borderWidth: 1,
                                tension: 0.1,
                                pointRadius: 0
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    usePointStyle: true,
                                    boxWidth: 3,
                                    padding: 6,
                                    font: { size: 5 }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: {
                                    font: { size: 5 },
                                    maxRotation: 0,
                                    autoSkip: true,
                                    maxTicksLimit: 5
                                }
                            },
                            y: {
                                type: 'logarithmic',
                                ticks: {
                                    font: { size: 5 },
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
        });
    </script>
    @endif
</body>
</html>
