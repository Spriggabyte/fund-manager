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
        /* =============================================================
           FOORD EQUITY FUND – CLASS A — PDF TEMPLATE
           Pixel-perfect A4 layout for Puppeteer PDF generation
           ============================================================= */

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
            --light-grey: #cccccc;
            --dark-grey: #535353;
            --very-light-grey: #f4f4f4;
            --off-black: #313131;
            --white: #ffffff;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        @page { size: A4 portrait; margin: 0; }

        html, body {
            width: 210mm;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Lato', -apple-system, sans-serif;
            font-size: 6.5pt;
            line-height: 1.2;
            color: var(--off-black);
            background: var(--white);
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* ── Page Container ── */
        .page {
            width: 210mm;
            height: 297mm;
            max-height: 297mm;
            overflow: hidden;
            position: relative;
            page-break-after: always;
            background: var(--white);
        }
        .page:last-child { page-break-after: auto; }

        /* ── Header ── */
        .header-row {
            display: flex;
            min-height: 20mm;
        }
        .header-sidebar-bg {
            width: 46mm;
            min-width: 46mm;
            background-color: var(--dark-navy-15);
            padding: 5mm 3.5mm;
            display: flex;
            align-items: flex-start;
        }
        .header-main {
            flex: 1;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 5mm 6mm 3mm 4mm;
        }

        .date-badge {
            background-color: var(--naartjie);
            color: var(--white);
            padding: 1.8mm 3.5mm;
            font-weight: 500;
            font-size: 9pt;
            letter-spacing: 0.03em;
        }

        .logo img {
            height: 10mm;
            width: auto;
        }

        /* ── Naartjie Stripe ── */
        .naartjie-stripe {
            height: 0.8mm;
            background-color: var(--naartjie);
        }

        /* ── Title Banner ── */
        .title-banner {
            background-color: var(--dark-navy);
            color: var(--white);
            padding: 4mm 8mm 3.5mm 8mm;
        }

        .fund-name {
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
            font-size: 6.5pt;
            line-height: 8.5pt;
            letter-spacing: 0.02em;
            margin: 0;
            opacity: 0.95;
        }

        /* ── Content Wrapper ── */
        .content-wrapper {
            display: flex;
            flex-direction: row;
            flex: 1;
        }

        /* ── Sidebar ── */
        .sidebar {
            width: 46mm;
            min-width: 46mm;
            max-width: 46mm;
            background-color: var(--dark-navy-15);
            padding: 3mm 3.5mm;
            overflow: hidden;
        }

        .sidebar-section { margin-bottom: 1.8mm; }
        .sidebar-section:last-child { margin-bottom: 0; }

        .sidebar-heading {
            font-weight: 700;
            font-size: 5.5pt;
            line-height: 6.5pt;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            color: var(--dark-navy);
            margin: 0 0 0.2mm 0;
        }

        .sidebar-text {
            font-weight: 400;
            font-size: 6pt;
            line-height: 7pt;
            letter-spacing: 0.01em;
            color: var(--off-black);
            margin: 0;
        }

        /* Equity Indicator Dots */
        .equity-indicator { display: flex; gap: 0.6mm; margin: 0.8mm 0; }
        .equity-dot { width: 1.8mm; height: 1.8mm; border-radius: 50%; }
        .equity-dot.filled { background-color: var(--naartjie); }
        .equity-dot.empty { background-color: var(--medium-grey); }

        /* Low Carbon Badge */
        .low-carbon-badge { margin-top: 3mm; }
        .low-carbon-badge img { height: 10mm; width: auto; }

        /* ── Main Content ── */
        .main-content {
            flex: 1;
            padding: 3mm 5mm 3mm 4mm;
            min-width: 0;
            overflow: hidden;
        }

        /* ── Section Headings ── */
        .section-heading {
            font-weight: 600;
            font-size: 7pt;
            line-height: 8pt;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            color: var(--off-black);
            margin: 0 0 1mm 0;
        }

        .section-subheading {
            font-weight: 400;
            font-size: 5.5pt;
            line-height: 6pt;
            color: var(--dark-grey);
            margin: -0.5mm 0 1mm 0;
        }

        /* ── Two-Column Grid ── */
        .two-col {
            display: flex;
            gap: 4mm;
            margin-bottom: 2.5mm;
        }
        .two-col > * { flex: 1; min-width: 0; }

        /* ── Tables ── */
        .table-container {
            position: relative;
            margin-bottom: 2mm;
        }

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
            font-weight: 500;
            font-size: 6pt;
            line-height: 6.5pt;
            text-transform: uppercase;
            text-align: center;
            padding: 1.2mm 1.5mm;
            border-right: 0.5pt solid rgba(255,255,255,0.4);
        }
        table th:first-child { text-align: left; }
        table th:last-child { border-right: none; }

        table td {
            font-size: 6.5pt;
            line-height: 8pt;
            padding: 1mm 1.5mm;
            border-bottom: 0.3pt solid #e5e5e5;
            text-align: center;
        }
        table td:first-child { text-align: left; }

        table tbody tr:nth-child(odd) { background-color: var(--very-light-grey); }
        table tbody tr:nth-child(even) { background-color: var(--white); }

        .total-row td {
            background-color: var(--naartjie) !important;
            color: var(--white);
            font-weight: 600;
            border-bottom: none;
        }

        .highlight-row td { background-color: var(--naartjie-20) !important; }
        .highlight-row td:first-child { color: var(--naartjie); font-weight: 600; }

        /* No accent bar variant */
        .table-no-accent { position: relative; }
        .table-no-accent table { margin-left: 0; }

        /* ── Sector Allocation Bars ── */
        .sector-row {
            display: flex;
            align-items: center;
            margin-bottom: 0.3mm;
        }
        .sector-label {
            width: 28mm;
            flex-shrink: 0;
            font-size: 6.5pt;
            line-height: 8pt;
            color: var(--off-black);
            padding-right: 1.5mm;
        }
        .sector-bar-track {
            flex: 1;
            height: 3mm;
            position: relative;
        }
        .sector-bar-fill {
            height: 100%;
            background-color: var(--naartjie);
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding-right: 0.8mm;
            min-width: 4mm;
        }
        .sector-bar-value {
            color: var(--white);
            font-size: 6pt;
            font-weight: 700;
            line-height: 3mm;
        }
        .sector-change {
            width: 10mm;
            flex-shrink: 0;
            text-align: right;
            font-size: 6pt;
            line-height: 8pt;
            padding-left: 1mm;
        }
        .sector-change .arrow { font-size: 5pt; margin-right: 0.5mm; }
        .sector-change.up { color: var(--naartjie); }
        .sector-change.down { color: var(--dark-navy); }

        /* ── Asset Allocation Table (no chart) ── */
        .asset-table { margin-bottom: 0; }
        .asset-table table { font-size: 6.5pt; }
        .asset-table table th {
            font-size: 5.5pt;
            padding: 1mm 1.5mm;
        }
        .asset-table table td {
            padding: 0.8mm 1.5mm;
        }
        .asset-table .indent td:first-child {
            padding-left: 3mm;
            color: var(--dark-navy-70);
            font-size: 6pt;
        }

        /* ── Chart containers ── */
        .chart-wrapper { position: relative; }
        canvas { display: block; width: 100%; }

        /* ── Monthly Chart Legend ── */
        .monthly-legend {
            display: flex;
            justify-content: center;
            gap: 5mm;
            margin-top: 1mm;
            font-size: 5.5pt;
            line-height: 7pt;
            color: var(--off-black);
        }
        .monthly-legend-item { display: flex; align-items: center; gap: 1mm; }
        .monthly-legend-swatch { width: 2mm; height: 2mm; display: inline-block; }

        /* ── Chart Description ── */
        .chart-description {
            font-size: 5.5pt;
            line-height: 7pt;
            color: var(--dark-grey);
            margin: 1.5mm 0 2mm 0;
        }

        /* ── Performance Table ── */
        .perf-table table th {
            font-size: 5.5pt;
            line-height: 6pt;
            padding: 1mm 1mm;
        }
        .perf-table table td {
            font-size: 6.5pt;
            line-height: 7pt;
            padding: 1mm 1mm;
        }

        /* ── Separator Row ── */
        .separator-row td {
            background-color: var(--white) !important;
            border-bottom: none !important;
            padding: 0.5mm 0 !important;
        }

        /* ── Footnotes ── */
        .footnotes {
            margin-top: 1.5mm;
        }
        .footnotes p {
            font-size: 5pt;
            line-height: 6pt;
            color: var(--dark-grey);
            letter-spacing: 0.01em;
        }

        /* ============================
           PAGE 2 STYLES
           ============================ */
        .page-2 .sidebar {
            padding: 3.5mm;
        }

        .imp-info-header {
            background-color: var(--dark-navy);
            color: var(--white);
            padding: 2mm 3mm;
            margin-bottom: 2.5mm;
            font-weight: 600;
            font-size: 7pt;
            line-height: 9pt;
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }

        .imp-info-text {
            font-weight: 300;
            font-size: 5pt;
            line-height: 6pt;
            color: var(--off-black);
            margin-bottom: 1.5mm;
        }

        .page-2 .main-content { padding: 4mm 6mm 4mm 4mm; }

        .fee-table table td {
            text-align: left;
            font-size: 6.5pt;
            line-height: 8pt;
            padding: 1mm 2mm;
        }
        .fee-table table td:first-child {
            width: 55%;
        }

        .fee-description {
            font-size: 6pt;
            line-height: 7.5pt;
            color: var(--dark-grey);
            margin: 1.5mm 0 3mm 0;
        }

        .perf-fees-text {
            font-size: 6pt;
            line-height: 7.5pt;
            color: var(--dark-grey);
            margin-bottom: 1.5mm;
        }

        .perf-fee-footnote {
            font-size: 5.5pt;
            line-height: 7pt;
            color: var(--dark-grey);
            margin-top: 1mm;
        }

        /* ── Footer ── */
        .footer {
            margin-top: 4mm;
            padding-top: 2.5mm;
            border-top: 0.5mm solid var(--naartjie);
        }
        .footer-info {
            font-family: 'Merriweather', Georgia, serif;
            font-weight: 400;
            font-size: 7pt;
            line-height: 9pt;
            letter-spacing: 0.02em;
            color: var(--naartjie);
            margin-bottom: 1.5mm;
        }
        .footer-free {
            font-family: 'Merriweather', Georgia, serif;
            font-style: italic;
            font-weight: 400;
            font-size: 7pt;
            line-height: 9pt;
            color: var(--naartjie);
            margin-bottom: 2mm;
        }
        .footer-contact {
            font-weight: 500;
            font-size: 7pt;
            line-height: 9pt;
            letter-spacing: 0.03em;
            color: var(--naartjie);
        }
        .footer-contact p { margin: 0; }
        .footer-bottom {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }
        .footer-logo img { height: 8mm; width: auto; }
    </style>
</head>
<body>
    <!-- ═══════════════════════════════════════════════════════════
         PAGE 1
         ═══════════════════════════════════════════════════════════ -->
    <div class="page page-1">
        <!-- Header -->
        <div class="header-row">
            <div class="header-sidebar-bg">
                <div class="date-badge">{{ $fund->data['fund']['date'] ?? $fund->updated_at->format('d F Y') }}</div>
            </div>
            <div class="header-main">
                <div></div>
                <div class="logo">
                    <img src="{{ $fund->data['fund']['logoUrl'] ?? '' }}" alt="Foord">
                </div>
            </div>
        </div>

        <!-- Naartjie Stripe -->
        <div class="naartjie-stripe"></div>

        <!-- Title Banner -->
        <div class="title-banner">
            @php
                $fullName = $fund->data['fund']['name'] ?? $fund->name;
                // Try to split at " – CLASS" or " - CLASS"
                $parts = preg_split('/(\s[–\-]\s(?:CLASS\s))/i', $fullName, 2, PREG_SPLIT_DELIM_CAPTURE);
            @endphp
            <h1 class="fund-name">
                @if(count($parts) >= 3)
                    {{ $parts[0] }} <span class="class-suffix">{{ $parts[1] }}{{ $parts[2] }}</span>
                @else
                    {{ $fullName }}
                @endif
            </h1>
            <p class="fund-description">{{ $fund->data['fund']['description'] ?? '' }}</p>
        </div>

        <!-- Content Wrapper -->
        <div class="content-wrapper">
            <!-- Sidebar -->
            <div class="sidebar">
                @if(isset($fund->data['sidebar']))
                    @php
                        $sidebarLabels = [
                            'domicile' => 'DOMICILE',
                            'managementCompany' => 'MANAGEMENT COMPANY',
                            'fundManagers' => 'FUND MANAGERS',
                            'inceptionDate' => 'INCEPTION DATE',
                            'baseCurrency' => 'BASE CURRENCY',
                            'equityIndicator' => 'EQUITY INDICATOR',
                            'category' => 'CATEGORY',
                            'benchmark' => 'BENCHMARK',
                            'minimumLumpSumMonthly' => 'MINIMUM LUMP SUM / MONTHLY',
                            'portfolioSize' => 'PORTFOLIO SIZE',
                            'unitPrice' => 'UNIT PRICE',
                            'numberOfUnits' => 'NUMBER OF UNITS',
                            'lastDistributions' => 'LAST DISTRIBUTIONS',
                            'incomeDistributions' => 'INCOME DISTRIBUTIONS',
                            'incomeCharacteristics' => 'INCOME CHARACTERISTICS',
                            'portfolioOrientation' => 'PORTFOLIO ORIENTATION',
                            'significantRestrictions' => 'SIGNIFICANT RESTRICTIONS',
                            'riskOfLoss' => 'RISK OF LOSS',
                            'timeHorizon' => 'TIME HORIZON',
                            'isinNumber' => 'ISIN NUMBER',
                        ];
                    @endphp
                    @foreach ($sidebarLabels as $key => $label)
                        @if(isset($fund->data['sidebar'][$key]))
                            <div class="sidebar-section">
                                <p class="sidebar-heading">{{ $label }}</p>
                                @if ($key === 'equityIndicator' && is_array($fund->data['sidebar'][$key]))
                                    @php
                                        $eq = $fund->data['sidebar'][$key];
                                        $filled = $eq['filled'] ?? 7;
                                        $total = $eq['total'] ?? 10;
                                    @endphp
                                    <div class="equity-indicator">
                                        @for ($i = 0; $i < $total; $i++)
                                            <span class="equity-dot {{ $i < $filled ? 'filled' : 'empty' }}"></span>
                                        @endfor
                                    </div>
                                    <p class="sidebar-text">{{ $eq['description'] ?? '' }}</p>
                                @elseif (is_array($fund->data['sidebar'][$key]))
                                    <p class="sidebar-text">{{ $fund->data['sidebar'][$key]['description'] ?? '' }}</p>
                                @else
                                    <p class="sidebar-text">{!! $fund->data['sidebar'][$key] !!}</p>
                                @endif
                            </div>
                        @endif
                    @endforeach
                @endif
            </div>

            <!-- Main Content -->
            <div class="main-content">
                <!-- Row 1: Sector Allocation + Asset Allocation -->
                <div class="two-col">
                    <!-- Equity Sector Allocation -->
                    @if(isset($fund->data['mainContent']['sectorAllocation']))
                        <div>
                            <h3 class="section-heading">{{ $fund->data['mainContent']['sectorAllocation']['title'] }}</h3>
                            <p class="section-subheading">{{ $fund->data['mainContent']['sectorAllocation']['subtitle'] }}</p>
                            @foreach ($fund->data['mainContent']['sectorAllocation']['sectors'] as $sector)
                                <div class="sector-row">
                                    <div class="sector-label">{{ $sector['name'] }}</div>
                                    <div class="sector-bar-track">
                                        <div class="sector-bar-fill" style="width: {{ max(($sector['value'] / 35) * 100, 8) }}%;">
                                            <span class="sector-bar-value">{{ $sector['value'] }}</span>
                                        </div>
                                    </div>
                                    <div class="sector-change {{ ($sector['direction'] ?? '') === 'up' ? 'up' : (($sector['direction'] ?? '') === 'down' ? 'down' : '') }}">
                                        @if(($sector['direction'] ?? '') === 'up')
                                            <span class="arrow">▲</span>
                                        @elseif(($sector['direction'] ?? '') === 'down')
                                            <span class="arrow">▼</span>
                                        @endif
                                        {{ $sector['change'] ?? '' }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <!-- Asset Allocation (table only, no donut) -->
                    @if(isset($fund->data['mainContent']['assetAllocation']))
                        <div>
                            <h3 class="section-heading">{{ $fund->data['mainContent']['assetAllocation']['title'] }}</h3>
                            <div class="asset-table table-container">
                                <table>
                                    <thead>
                                        <tr>
                                            @foreach ($fund->data['mainContent']['assetAllocation']['headers'] as $header)
                                                <th>{{ $header }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($fund->data['mainContent']['assetAllocation']['rows'] as $row)
                                            <tr class="{{ ($row['isTotal'] ?? false) ? 'total-row' : '' }} {{ ($row['indent'] ?? false) ? 'indent' : '' }}">
                                                <td>{{ $row['name'] }}</td>
                                                <td>{{ $row['current'] }}</td>
                                                <td>{{ $row['previous'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Row 2: Top 10 Investments + Portfolio Performance Chart -->
                <div class="two-col">
                    <!-- Top 10 Investments -->
                    @if(isset($fund->data['mainContent']['topInvestments']))
                        <div>
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
                                            <tr>
                                                <td>{{ $row['security'] }}</td>
                                                <td>{{ $row['percentage'] }}</td>
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
                            <h3 class="section-heading">PORTFOLIO PERFORMANCE VS BENCHMARK</h3>
                            <div class="chart-wrapper">
                                <canvas id="portfolioChart" style="height: 38mm;"></canvas>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Monthly Portfolio Performance vs Benchmark (bar chart) -->
                @if(isset($fund->data['mainContent']['charts']['monthlyData']))
                    <div>
                        <h3 class="section-heading">MONTHLY PORTFOLIO PERFORMANCE VS BENCHMARK</h3>
                        <div class="chart-wrapper">
                            <canvas id="monthlyChart" style="height: 28mm;"></canvas>
                        </div>
                        <div class="monthly-legend">
                            <div class="monthly-legend-item">
                                <span class="monthly-legend-swatch" style="background-color: var(--naartjie);"></span>
                                Months when benchmark is negative
                            </div>
                            <div class="monthly-legend-item">
                                <span class="monthly-legend-swatch" style="background-color: var(--dark-navy);"></span>
                                Months when benchmark is positive
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Chart Description -->
                @if(isset($fund->data['mainContent']['chartDescription']))
                    <p class="chart-description">{{ $fund->data['mainContent']['chartDescription'] }}</p>
                @endif

                <!-- Performance Table -->
                @if(isset($fund->data['mainContent']['performanceTable']))
                    <div class="perf-table">
                        <h3 class="section-heading">{!! $fund->data['mainContent']['performanceTable']['title'] !!}</h3>
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
                                    @foreach ($fund->data['mainContent']['performanceTable']['rows'] as $rowIndex => $row)
                                        @if($rowIndex === 2)
                                            {{-- Separator row between Benchmark and Fund highest --}}
                                            <tr class="separator-row"><td colspan="{{ count($fund->data['mainContent']['performanceTable']['headers']) }}" style="padding: 0.5mm 0; border-bottom: none; background: white !important;"></td></tr>
                                        @endif
                                        <tr class="{{ ($row['highlight'] ?? false) ? 'highlight-row' : '' }}">
                                            <td>{!! $row['name'] !!}</td>
                                            <td>{{ $row['cashValue'] }}</td>
                                            <td>{{ $row['sinceInception'] }}</td>
                                            @if(isset($row['15yrs']))<td>{{ $row['15yrs'] }}</td>@endif
                                            <td>{{ $row['10yrs'] }}</td>
                                            <td>{{ $row['7yrs'] }}</td>
                                            <td>{{ $row['5yrs'] }}</td>
                                            <td>{{ $row['3yrs'] }}</td>
                                            <td>{{ $row['1yr'] }}</td>
                                            <td>{{ $row['thisMonth'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Footnotes -->
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

    <!-- ═══════════════════════════════════════════════════════════
         PAGE 2
         ═══════════════════════════════════════════════════════════ -->
    <div class="page page-2">
        <div class="content-wrapper">
            <!-- Sidebar: Important Information -->
            @if(isset($fund->data['importantInfo']))
                <div class="sidebar">
                    <div class="imp-info-header">{{ $fund->data['importantInfo']['title'] }}</div>
                    @foreach ($fund->data['importantInfo']['paragraphs'] as $paragraph)
                        <p class="imp-info-text">{{ $paragraph }}</p>
                    @endforeach
                    <p class="imp-info-text" style="margin-top: 2mm;">{{ $fund->data['importantInfo']['publishedDate'] }}</p>
                </div>
            @endif

            <!-- Main Content: Fees -->
            <div class="main-content">
                <!-- Fee Rates -->
                @if(isset($fund->data['fees']['feeRates']))
                    <div style="margin-bottom: 3mm;">
                        <h3 class="section-heading">{{ $fund->data['fees']['feeRates']['title'] }}</h3>
                        <div class="fee-table table-container">
                            <table>
                                <tbody>
                                    @foreach ($fund->data['fees']['feeRates']['rates'] as $rate)
                                        <tr>
                                            <td>{{ $rate['name'] }}</td>
                                            <td>{{ $rate['value'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if(!empty($fund->data['fees']['feeRates']['description']))
                            <p class="fee-description">{{ $fund->data['fees']['feeRates']['description'] }}</p>
                        @endif
                    </div>
                @endif

                <!-- Total Investment Charge -->
                @if(isset($fund->data['fees']['totalInvestmentCharge']))
                    <div style="margin-bottom: 3mm;">
                        <h3 class="section-heading">{{ $fund->data['fees']['totalInvestmentCharge']['title'] }}</h3>
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
                                        <td>{{ $fund->data['fees']['totalInvestmentCharge']['total']['name'] }}</td>
                                        <td>{{ $fund->data['fees']['totalInvestmentCharge']['total']['12m'] }}</td>
                                        <td>{{ $fund->data['fees']['totalInvestmentCharge']['total']['36m'] }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        @if(!empty($fund->data['fees']['totalInvestmentCharge']['description']))
                            <p class="fee-description">{{ $fund->data['fees']['totalInvestmentCharge']['description'] }}</p>
                        @endif
                    </div>
                @endif

                <!-- Performance Fees -->
                @if(isset($fund->data['fees']['performanceFees']))
                    <div style="margin-bottom: 3mm;">
                        <h3 class="section-heading">{{ $fund->data['fees']['performanceFees']['title'] }}</h3>
                        @foreach ($fund->data['fees']['performanceFees']['paragraphs'] as $paragraph)
                            <p class="perf-fees-text">{{ $paragraph }}</p>
                        @endforeach
                    </div>
                @endif

                <!-- Performance Fee Examples -->
                @if(isset($fund->data['fees']['performanceFeeExamples']))
                    <div style="margin-bottom: 3mm;">
                        <h3 class="section-heading">{{ $fund->data['fees']['performanceFeeExamples']['title'] }}</h3>
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
                                        <td>{{ $fund->data['fees']['performanceFeeExamples']['total']['name'] }}</td>
                                        <td>{{ $fund->data['fees']['performanceFeeExamples']['total']['a'] }}</td>
                                        <td>{{ $fund->data['fees']['performanceFeeExamples']['total']['b'] }}</td>
                                        <td>{{ $fund->data['fees']['performanceFeeExamples']['total']['c'] }}</td>
                                        <td>{!! $fund->data['fees']['performanceFeeExamples']['total']['d'] !!}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        @if(!empty($fund->data['fees']['performanceFeeExamples']['footnote']))
                            <p class="perf-fee-footnote">{{ $fund->data['fees']['performanceFeeExamples']['footnote'] }}</p>
                        @endif
                    </div>
                @endif

                <!-- Footer -->
                @if(isset($fund->data['footer']))
                    <div class="footer">
                        <p class="footer-info">{{ $fund->data['footer']['info'] }}</p>
                        <p class="footer-free">{{ $fund->data['footer']['freeOfCharge'] }}</p>
                        <div class="footer-bottom">
                            <div class="footer-contact">
                                <p>T. {{ $fund->data['footer']['contact']['phone'] }}</p>
                                <p>E. {{ $fund->data['footer']['contact']['email'] }}</p>
                                <p>{{ $fund->data['footer']['contact']['website'] }}</p>
                            </div>
                            <div class="footer-logo">
                                <img src="{{ $fund->data['fund']['logoUrl'] ?? '' }}" alt="Foord">
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════
         CHART.JS
         ═══════════════════════════════════════════════════════════ -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const C = {
            naartjie: '#d25347',
            darkNavy: '#29363d',
            mediumGrey: '#9a9a9a',
            darkGrey: '#535353'
        };

        Chart.defaults.font.family = "'Lato', sans-serif";
        Chart.defaults.font.size = 7;

        // Annotation plugin for end-of-line labels
        const endLabelPlugin = {
            id: 'endLabels',
            afterDraw(chart) {
                const { ctx: c, data, scales: { y } } = chart;
                data.datasets.forEach((ds, i) => {
                    const vals = ds.data;
                    const last = vals[vals.length - 1];
                    const meta = chart.getDatasetMeta(i);
                    const lastPt = meta.data[meta.data.length - 1];
                    if (!lastPt) return;
                    c.save();
                    c.font = '600 7px Lato, sans-serif';
                    c.fillStyle = ds.borderColor;
                    c.textAlign = 'left';
                    c.textBaseline = 'middle';
                    const label = 'R ' + Number(last).toLocaleString();
                    c.fillText(label, lastPt.x + 4, lastPt.y);
                    c.restore();
                });
            }
        };

        // Portfolio Performance vs Benchmark (line chart)
        @if(isset($fund->data['mainContent']['charts']['portfolioData']))
        {
            const data = @json($fund->data['mainContent']['charts']['portfolioData']);
            const ctx = document.getElementById('portfolioChart');
            if (ctx) {
                new Chart(ctx.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: data.map(d => d.date),
                        datasets: [
                            {
                                label: 'Fund',
                                data: data.map(d => d.fund),
                                borderColor: C.naartjie,
                                backgroundColor: 'transparent',
                                borderWidth: 1.5,
                                tension: 0.1,
                                pointRadius: 0
                            },
                            {
                                label: 'Benchmark',
                                data: data.map(d => d.benchmark),
                                borderColor: C.darkNavy,
                                backgroundColor: 'transparent',
                                borderWidth: 1.5,
                                tension: 0.1,
                                pointRadius: 0
                            }
                        ]
                    },
                    plugins: [endLabelPlugin],
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: false,
                        layout: { padding: { right: 32 } },
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { usePointStyle: true, boxWidth: 5, padding: 8, font: { size: 6 } }
                            },
                            tooltip: { enabled: false }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { font: { size: 6 }, maxRotation: 0, autoSkip: true, maxTicksLimit: 6 }
                            },
                            y: {
                                type: 'logarithmic',
                                title: { display: true, text: 'Cash Value (R\'000)', font: { size: 5.5 }, color: C.darkGrey },
                                ticks: {
                                    font: { size: 6 },
                                    callback: v => { if ([100,1000,10000].includes(v)) return v.toLocaleString(); return ''; }
                                },
                                grid: { color: '#e5e5e5' }
                            }
                        }
                    }
                });
            }
        }
        @endif

        // Monthly Portfolio Performance vs Benchmark (bar chart)
        @if(isset($fund->data['mainContent']['charts']['monthlyData']))
        {
            const data = @json($fund->data['mainContent']['charts']['monthlyData']);
            const ctx = document.getElementById('monthlyChart');
            if (ctx) {
                new Chart(ctx.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: data.map(d => d.date),
                        datasets: [{
                            data: data.map(d => d.relative),
                            backgroundColor: data.map(d => d.benchmarkNegative ? C.naartjie : C.darkNavy),
                            borderWidth: 0,
                            barPercentage: 0.9,
                            categoryPercentage: 0.95
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: { enabled: false }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { font: { size: 5.5 }, maxRotation: 0, autoSkip: true, maxTicksLimit: 5 }
                            },
                            y: {
                                min: -10,
                                max: 10,
                                ticks: {
                                    stepSize: 5,
                                    font: { size: 6 },
                                    callback: v => v + '%'
                                },
                                grid: { color: '#e5e5e5' }
                            }
                        }
                    }
                });
            }
        }
        @endif
    </script>
</body>
</html>
