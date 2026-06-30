<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $fund->data['fund']['name'] ?? $fund->name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;500;700&family=Merriweather:wght@300;400;700&display=swap" rel="stylesheet">
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
            font-family: 'Avenir Next', 'Lato', -apple-system, sans-serif;
            font-size: 7pt;
            line-height: 1.2;
            color: var(--off-black);
            background: var(--white);
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* Page Container - grey sidebar bg extends full height on left */
        .page {
            width: 210mm;
            height: 297mm;
            max-height: 297mm;
            overflow: hidden;
            padding: 0;
            position: relative;
            page-break-after: always;
            background: linear-gradient(to right, var(--white) 3mm, var(--dark-navy-15) 3mm, var(--dark-navy-15) 52mm, var(--white) 52mm);
        }

        .page:last-child {
            page-break-after: auto;
        }

        /* =====================================================
           HEADER SECTION
           ===================================================== */
        .header {
            display: flex;
            align-items: center;
            padding: 4mm 6mm 3mm 0;
            margin-bottom: 0;
            min-height: 26mm;
        }

        /* 52mm sidebar zone with white left-strip offset; centres the date badge */
        .date-zone {
            width: 52mm;
            padding-left: 3mm;
            box-sizing: border-box;
            display: flex;
            justify-content: center;
        }

        .date-badge {
            background-color: var(--naartjie);
            color: #ffffff;
            padding: 2.2mm 5mm;
            font-family: 'Lato', 'Avenir Next', sans-serif;
            font-weight: 400;
            font-size: 11pt;
            letter-spacing: 0.02em;
            text-align: center;
        }

        .logo {
            height: 12mm;
            margin-left: auto;
            align-self: flex-start;
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
            padding: 6mm 12mm 7mm 5mm;
            margin: 0;
            width: 100%;
        }

        .fund-name {
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 500;
            font-size: 22pt;
            letter-spacing: 0.01em;
            text-transform: uppercase;
            margin: 0 0 1.5mm 0;
            line-height: 1.05;
        }

        .fund-name .class-suffix {
            font-weight: 500;
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

        /* =====================================================
           MAIN CONTENT LAYOUT
           ===================================================== */
        .content-wrapper {
            display: flex;
            flex-direction: row;
            margin: 0;
            width: 100%;
            min-height: calc(297mm - 28mm - 32mm); /* page - header - title banner */
        }

        .page-2 .content-wrapper {
            min-height: 297mm;
        }

        /* Sidebar - 52mm width */
        .sidebar {
            width: 52mm;
            min-width: 52mm;
            max-width: 52mm;
            background-color: transparent;
            padding: 4mm 4mm 4mm 5mm;
            overflow: hidden;
        }

        .sidebar-section {
            margin-bottom: 2mm;
        }

        .sidebar-section:last-child {
            margin-bottom: 0;
        }

        .sidebar-heading {
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 600;
            font-size: 5.5pt;
            line-height: 6.5pt;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            color: var(--dark-navy);
            margin: 0 0 0.3mm 0;
        }

        .sidebar-text {
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 400;
            font-size: 6.5pt;
            line-height: 7.5pt;
            letter-spacing: 0.01em;
            color: var(--off-black);
            margin: 0;
        }

        /* Equity Indicator Dots — heading + dots share one line. */
        .equity-heading {
            display: flex;
            align-items: center;
            gap: 1.2mm;
            flex-wrap: nowrap;
        }

        .equity-indicator {
            display: inline-flex;
            gap: 0.5mm;
            align-items: center;
        }

        .equity-dot {
            width: 1.4mm;
            height: 1.4mm;
            border-radius: 50%;
            box-sizing: border-box;
            display: inline-block;
            flex: 0 0 1.4mm;
        }

        .equity-dot.filled {
            background-color: var(--naartjie);
            border: 0.12mm solid var(--naartjie);
        }

        .equity-dot.empty {
            background-color: transparent;
            border: 0.12mm solid var(--medium-grey);
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
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 500;
            font-size: 8pt;
            line-height: 9pt;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            color: var(--off-black);
            margin: 0 0 0.5mm 0;
        }

        .section-subheading {
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 500;
            font-size: 6.5pt;
            line-height: 7.5pt;
            letter-spacing: 0.01em;
            color: var(--off-black);
            margin: -0.3mm 0 0.8mm 0;
        }

        /* Smaller suffix style for parenthetical text in section headings */
        .section-heading .title-suffix {
            font-size: 6.5pt;
            font-weight: 500;
            color: var(--off-black);
            text-transform: uppercase;
            letter-spacing: 0.01em;
        }

        /* =====================================================
           TABLES
           ===================================================== */
        .table-container {
            position: relative;
            margin-bottom: 2.5mm;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 2.5px 1.5px;
            margin-left: -2.5px;
            margin-right: -2.5px;
            font-size: 7pt;
        }

        table th {
            background-color: var(--dark-navy);
            color: var(--white);
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 500;
            font-size: 6pt;
            line-height: 6.5pt;
            letter-spacing: 0;
            text-transform: uppercase;
            text-align: right;
            padding: 0.9mm 1.5mm;
        }

        table th:first-child {
            text-align: left;
        }

        table td {
            background-color: var(--very-light-grey);
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 400;
            font-size: 7pt;
            line-height: 8pt;
            padding: 1mm 2mm;
            text-align: right;
        }

        table td:first-child {
            text-align: left;
        }

        /* Performance Table — ticket #11: dark navy header w/ white text, darker grey body cells */
        .performance-table table th {
            background-color: var(--dark-navy);
            color: var(--white);
            font-weight: 500;
            text-align: right;
        }
        .performance-table table th:first-child {
            text-align: left;
        }
        .performance-table table td {
            background-color: var(--medium-grey-25);
            color: var(--off-black);
            font-size: 7pt;
            line-height: 8pt;
        }
        /* Blank spacer row between Benchmark and Fund highest */
        .performance-table table tr.perf-spacer-row td {
            background-color: transparent !important;
            padding: 0;
            height: 1.5mm;
            line-height: 1.5mm;
            font-size: 0;
        }

        /* Highlighted Foord fund rows — pink background, text colour same as table */
        table tbody tr.highlight-row td {
            background-color: var(--naartjie-20);
            color: var(--off-black);
        }

        table tbody tr.highlight-row td:first-child {
            color: var(--off-black);
            font-weight: 400;
        }

        /* Top 10 Investments — second column (ASSET CLASS) left-aligned */
        .top10-table table td:nth-child(2),
        .top10-table table th:nth-child(2) {
            text-align: left;
        }

        /* TIC table: first (TER) + last data row (Transaction costs) white;
           middle sub-item rows grey. Total row (.total-row) keeps red styling. */
        .tic-table table tbody tr td {
            background-color: var(--medium-grey-25);
        }
        .tic-table table tbody tr:nth-child(1) td,
        .tic-table table tbody tr:nth-child(6) td {
            background-color: var(--white);
        }

        /* Performance fee examples — row 1 pink, middle rows grey + black text,
           last row (.total-row) keeps the global red+white styling. */
        .pfe-table table tbody tr td {
            background-color: var(--medium-grey-25);
            color: var(--off-black);
            font-weight: 400;
        }
        .pfe-table table tbody tr:nth-child(1) td {
            background-color: var(--naartjie-20);
            color: var(--off-black);
            font-weight: 500;
        }

        /* #17: performance-fees text larger+darker, more spacing top/bottom */
        .performance-fees-section {
            margin: 4mm 0 4mm 0;
        }

        /* Match the vertical gap above other major fee sub-sections (TIC, PFE) so
           "TOTAL INVESTMENT CHARGE" sits at the same distance from the body text
           above it as "PERFORMANCE FEE EXAMPLES" does. */
        .tic-section, .pfe-section {
            margin-top: 4mm;
        }
        .performance-fees-text {
            font-size: 6.5pt;
            line-height: 8.5pt;
            color: var(--off-black);
            margin: 0 0 2mm 0;
        }

        /* Total row */
        table tbody tr.total-row td,
        table tfoot td {
            background-color: var(--naartjie);
            font-weight: 500;
            color: var(--white);
        }

        /* Change indicators — arrow coloured only; number inherits table colour */
        td.change-cell { color: var(--off-black); }
        td.change-cell .change-arrow-up { color: #000; }
        td.change-cell .change-arrow-down { color: #7A9CB4; }

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
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 500;
            font-size: 8pt;
            line-height: 9pt;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            color: var(--off-black);
            margin: 0 0 1mm 0;
        }

        .chart-wrapper {
            height: 38mm;
            position: relative;
        }

        .chart-wrapper > div {
            width: 100% !important;
            height: 100% !important;
        }

        .chart-explanation {
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 400;
            font-size: 6.5pt;
            line-height: 8pt;
            letter-spacing: 0.01em;
            color: var(--off-black);
            margin: 1.5mm 0 2mm 0;
        }

        /* =====================================================
           FOOTNOTES
           ===================================================== */
        .footnotes {
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 400;
            font-size: 6pt;
            line-height: 7.5pt;
            letter-spacing: 0.01em;
            color: var(--off-black);
            margin-top: 1mm;
        }

        .footnotes p {
            margin: 0.3mm 0;
        }

        .footnotes sup {
            font-size: 5pt;
            line-height: 0;
            vertical-align: super;
        }

        /* =====================================================
           PAGE 2 - IMPORTANT INFO SIDEBAR
           ===================================================== */
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
            padding: 1.6mm 3mm;
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

        .info-sidebar-content p:last-child {
            margin-bottom: 0;
        }

        /* =====================================================
           PAGE 2 - FEES SECTION
           ===================================================== */
        .fees-content {
            flex: 1;
            /* Align FEES heading with the first line of sidebar body text
               (sidebar margin-top 8mm + dark box ~6.4mm + margin-bottom 6mm ≈ 20mm). */
            padding: 20mm 5mm 4mm 5mm;
            overflow: hidden;
        }

        .fee-rates-table {
            margin-bottom: 2.5mm;
        }

        .fee-rates-table table {
            margin-left: 0;
        }

        /* #15: first 5 rows darker grey, shorter rows, both columns left-aligned */
        .fee-rates-table td {
            padding: 0.6mm 1.5mm;
            background-color: var(--medium-grey-25);
            text-align: left;
        }

        .fee-rates-table td:last-child:not([colspan]) {
            text-align: left;
            font-weight: 500;
        }

        /* "Foord global funds:" — white background (not red), then next two rows red+heavier. */
        .fee-rates-table tr.sub-item td:first-child {
            padding-left: 3mm;
        }

        .fee-rates-table tr.global-funds-header td {
            background-color: var(--white) !important;
            color: var(--off-black);
            font-weight: 500;
            text-align: left;
        }

        .fee-rates-table tr.sub-item td {
            background-color: var(--naartjie-20) !important;
            color: var(--off-black);
            font-weight: 500;
        }

        .fee-description {
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 400;
            font-size: 6.5pt;
            line-height: 8pt;
            color: var(--off-black);
            margin: 1.5mm 0 2.5mm 0;
        }

        /* =====================================================
           FOOTER
           ===================================================== */
        .footer {
            margin-top: 9mm;
            padding-top: 2mm;
            border-top: 0.5pt solid var(--naartjie);
            /* #19: shorter top rule — span only the first half of the footer width */
            position: relative;
        }
        .footer::before {
            /* override border-top with a shorter visual rule */
            content: "";
        }

        /* shorten top rule: clip via background trick — actual border-top is removed in favour of a fixed-width pseudo line */
        .footer {
            border-top: none;
        }
        .footer::after {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 40mm;
            height: 0;
            border-top: 0.6pt solid var(--naartjie);
        }

        .footer-text {
            font-family: 'Merriweather', Georgia, serif;
            font-weight: 300;
            font-size: 7pt;
            line-height: 9.5pt;
            letter-spacing: 0.02em;
            color: var(--naartjie);
            margin: 0 0 2mm 0;
        }

        .footer-contact {
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 400;
            font-size: 7pt;
            line-height: 9pt;
            letter-spacing: 0.02em;
            color: var(--naartjie);
            position: relative;
        }

        .footer-contact p {
            margin: 0;
        }

        /* Red Foord acorn leaf next to contact info */
        .footer-leaf {
            position: absolute;
            right: 0;
            top: 0;
            width: 14mm;
            height: auto;
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
    @php
        $fmt = function ($v, int $dp = 1) {
            if ($v === null || $v === '') {
                return '';
            }
            if (is_string($v) && str_starts_with(ltrim($v), '+')) {
                return $v;
            }
            if (is_numeric($v)) {
                return number_format((float) $v, $dp);
            }
            return (string) $v;
        };
        $renderHeading = function (string $title): string {
            return preg_replace(
                '/\s*\(([^)]+)\)\s*$/',
                ' <span class="title-suffix">($1)</span>',
                e($title)
            );
        };
        // Normalise Unicode superscript digits to <sup> tags so every footnote
        // number renders at exactly the same size (¹ glyph weight differs from
        // ³⁴⁵ across font families).
        $normaliseSupers = function (string $text): string {
            $map = ['⁰' => '0', '¹' => '1', '²' => '2', '³' => '3', '⁴' => '4', '⁵' => '5', '⁶' => '6', '⁷' => '7', '⁸' => '8', '⁹' => '9'];
            // Group runs of consecutive superscript digits (e.g. "³,⁴") into one tag.
            return preg_replace_callback('/[⁰¹²³⁴⁵⁶⁷⁸⁹](?:[,]?[⁰¹²³⁴⁵⁶⁷⁸⁹])*/u', function ($m) use ($map) {
                return '<sup>'.strtr($m[0], $map).'</sup>';
            }, $text);
        };
    @endphp
    <!-- PAGE 1 -->
    <div class="page">
        <!-- Header -->
        <div class="header">
            <div class="date-zone">
                <div class="date-badge">
                    {{ $fund->data['fund']['date'] ?? now()->format('d F Y') }}
                </div>
            </div>
            <div class="logo">
                <img src="{{ $fund->data['fund']['logoUrl'] ?? 'https://foord.co.za/themes/custom/mirum/logo.png' }}" alt="FOORD">
            </div>
        </div>

        <!-- Title Banner -->
        <div class="title-banner">
            @php
                $fundName = $fund->data['fund']['name'] ?? $fund->name;
                if (preg_match('/^(.+?)\s*[-—–]\s*(CLASS\s+[A-Z])$/iu', $fundName, $matches)) {
                    $mainName = trim($matches[1]);
                    $classText = mb_strtoupper(trim($matches[2]));
                } else {
                    $mainName = $fundName;
                    $classText = '';
                }
            @endphp
            <h1 class="fund-name">
                {{ mb_strtoupper($mainName) }}
                @if($classText)
                    <span class="class-suffix">&mdash; {{ $classText }}</span>
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
                            @if ($key === 'equityIndicator' && is_array($value))
                                @php
                                    $filled = $value['filled'] ?? 7;
                                    $total = $value['total'] ?? 10;
                                @endphp
                                {{-- Heading + dots share a single line so the dots sit
                                     immediately to the right of "EQUITY INDICATOR". --}}
                                <h3 class="sidebar-heading equity-heading">
                                    {{ $labels[$key] }}
                                    <span class="equity-indicator">
                                        @for ($i = 0; $i < $total; $i++)
                                            <span class="equity-dot {{ $i < $filled ? 'filled' : 'empty' }}"></span>
                                        @endfor
                                    </span>
                                </h3>
                                @if(isset($value['description']))
                                    <p class="sidebar-text">{!! $value['description'] !!}</p>
                                @endif
                            @else
                                <h3 class="sidebar-heading">{{ $labels[$key] ?? strtoupper(implode(' ', preg_split('/(?=[A-Z])/', $key, -1, PREG_SPLIT_NO_EMPTY))) }}</h3>
                                @if (is_array($value))
                                    <p class="sidebar-text">{!! $value['description'] ?? '' !!}</p>
                                @else
                                    <p class="sidebar-text">{!! $value !!}</p>
                                @endif
                            @endif
                        </div>
                    @endif
                @endforeach
            </div>

            <!-- Main Content -->
            <div class="main-content">
                <!-- Asset Allocation Table -->
                @if(isset($fund->data['mainContent']['assetAllocation']))
                    <h3 class="section-heading">{!! $renderHeading($fund->data['mainContent']['assetAllocation']['title'] ?? 'ASSET ALLOCATION % (MAX LIMITS IN BRACKETS)') !!}</h3>
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
                            @php
                                $aaHeaders = $fund->data['mainContent']['assetAllocation']['headers'] ?? [];
                                $aaColumnKeys = [];
                                $keyMap = ['SA (100)' => 'sa', 'FOREIGN (45)' => 'foreign', 'TOTAL' => 'total', 'CHANGE' => 'change'];
                                foreach (array_slice($aaHeaders, 1) as $h) {
                                    $aaColumnKeys[] = $keyMap[strtoupper(trim($h))] ?? strtolower(preg_replace('/[^a-zA-Z]/', '', $h) ?: 'col');
                                }
                            @endphp
                            <tbody>
                                @foreach ($fund->data['mainContent']['assetAllocation']['rows'] as $row)
                                    <tr>
                                        <td>{{ $row['name'] }}</td>
                                        @foreach ($aaColumnKeys as $colKey)
                                            @if ($colKey === 'change')
                                                @php
                                                    $dir = $row['changeDirection'] ?? '';
                                                    $raw = trim((string)($row['change'] ?? ''));
                                                    $arrowClass = $dir === 'up' ? 'change-arrow-up' : ($dir === 'down' ? 'change-arrow-down' : '');
                                                    if (preg_match('/^([▲▼])\s*(.*)$/u', $raw, $cm)) {
                                                        $arrowChar = $cm[1];
                                                        $numPart = $cm[2];
                                                    } else {
                                                        $arrowChar = '';
                                                        $numPart = $raw;
                                                    }
                                                @endphp
                                                <td class="change-cell">
                                                    @if ($arrowChar)<span class="{{ $arrowClass }}">{{ $arrowChar }}</span>@endif {{ $numPart }}
                                                </td>
                                            @else
                                                <td>{{ $fmt($row[$colKey] ?? '', 1) }}</td>
                                            @endif
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
                    <h3 class="section-heading">{{ $fund->data['mainContent']['topInvestments']['title'] ?? 'TOP 10 INVESTMENTS' }}</h3>

                    <div class="table-container top10-table">
                        <table>
                            <thead>
                                <tr>
                                    @foreach ($fund->data['mainContent']['topInvestments']['headers'] as $header)
                                        <th>{{ $header }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($fund->data['mainContent']['topInvestments']['rows'] as $idx => $row)
                                    <tr class="{{ ($row['highlight'] ?? false) || $idx < 2 ? 'highlight-row' : '' }}">
                                        <td>{{ $row['security'] }}</td>
                                        <td>{{ $row['assetClass'] }}</td>
                                        <td>{{ $row['market'] }}</td>
                                        <td>{{ $fmt($row['percentage'] ?? '', 1) }}</td>
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
                                <div id="inflationChart"></div>
                            </div>
                        </div>
                        <div class="chart-container">
                            <h4 class="chart-title">PORTFOLIO PERFORMANCE VS BENCHMARK</h4>
                            <div class="chart-wrapper">
                                <div id="portfolioChart"></div>
                            </div>
                        </div>
                    </div>

                    <p class="chart-explanation">
                        In managing retirement portfolios, Foord aims to achieve returns that exceed inflation plus 5% per annum over any rolling five-year period. The chart illustrates that a composite return of similarly managed portfolios over any rolling five-year period has only once dipped below the South African inflation rate. It also demonstrates that real returns of 5% per annum are consistently achievable in mandates of this nature when measured over the appropriate long-term period.
                    </p>
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
                    <h3 class="section-heading">{!! $normaliseSupers($renderHeading($fund->data['mainContent']['performanceTable']['title'] ?? 'PORTFOLIO PERFORMANCE % (PERIODS GREATER THAN ONE YEAR ARE ANNUALISED)')) !!}</h3>

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
                                @php
                                    $perfRows = $fund->data['mainContent']['performanceTable']['rows'] ?? [];
                                    // Highlight only the very first data row (the Foord fund row).
                                    // Insert a blank spacer row between "Benchmark" and "Fund highest".
                                @endphp
                                @foreach ($perfRows as $idx => $row)
                                    @php
                                        $nameStr = trim(strip_tags((string)$row['name']));
                                        $isTopFundRow = $idx === 0;
                                        $displayName = $row['name'];
                                        $lowerName = strtolower($nameStr);
                                        if (preg_match('/^fund\s+(highest|lowest)/i', $nameStr)) {
                                            // Highest/Lowest historical rows take footnotes 3 and 5.
                                            if (strpos($displayName, '3,5') === false && strpos($displayName, '³,⁵') === false) {
                                                $displayName .= '<sup>3,5</sup>';
                                            }
                                        } elseif (stripos($nameStr, 'fund') === 0 && strpos($displayName, '³') === false && strpos($displayName, '<sup>3</sup>') === false) {
                                            $displayName .= '<sup>3</sup>';
                                        } elseif (stripos($nameStr, 'benchmark') === 0 && strpos($displayName, '³,⁴') === false && strpos($displayName, '3,4') === false) {
                                            $displayName .= '<sup>3,4</sup>';
                                        }
                                    @endphp
                                    <tr class="{{ $isTopFundRow ? 'highlight-row' : '' }}">
                                        <td>{!! $displayName !!}</td>
                                        @foreach ($perfColKeys as $colKey)
                                            <td>{{ $colKey && isset($row[$colKey]) ? (in_array($colKey, ['cashValue']) ? $row[$colKey] : $fmt($row[$colKey], 1)) : '' }}</td>
                                        @endforeach
                                    </tr>
                                    @if (stripos($nameStr, 'benchmark') === 0)
                                        <tr class="perf-spacer-row">
                                            <td colspan="{{ count($perfColKeys) + 1 }}">&nbsp;</td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if(isset($fund->data['mainContent']['performanceTable']['footnotes']))
                        <div class="footnotes">
                            @foreach ($fund->data['mainContent']['performanceTable']['footnotes'] as $footnote)
                                <p>{!! $normaliseSupers($footnote) !!}</p>
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
                        <p style="margin-top: 2mm;">{{ $fund->data['importantInfo']['publishedDate'] }}</p>
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
                                        @php
                                            $gName = ltrim($gfund['name'], "- \t");
                                        @endphp
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
                    <div class="tic-section">
                        <h3 class="section-heading">{{ $fund->data['fees']['totalInvestmentCharge']['title'] ?? 'TOTAL INVESTMENT CHARGE %' }}</h3>

                        <div class="table-container tic-table">
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
                    </div>
                @endif

                <!-- Performance Fees -->
                @if(isset($fund->data['fees']['performanceFees']))
                    <div class="performance-fees-section">
                        <h3 class="section-heading">{{ $fund->data['fees']['performanceFees']['title'] ?? 'PERFORMANCE FEES' }}</h3>
                        @foreach ($fund->data['fees']['performanceFees']['paragraphs'] as $paragraph)
                            <p class="fee-description performance-fees-text">{{ $paragraph }}</p>
                        @endforeach
                    </div>
                @endif

                <!-- Performance Fee Examples -->
                @if(isset($fund->data['fees']['performanceFeeExamples']))
                    <div class="pfe-section">
                    <h3 class="section-heading">{{ $fund->data['fees']['performanceFeeExamples']['title'] ?? 'PERFORMANCE FEE EXAMPLES %' }}</h3>

                    <div class="table-container pfe-table">
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
                    </div>
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
                            <img class="footer-leaf" src="{{ asset('images/leaf.png') }}" alt="">

                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Highcharts -->
    @if(isset($fund->data['mainContent']['charts']))
    <script src="https://cdn.jsdelivr.net/npm/highcharts@11/highcharts.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const inflationData = @json($fund->data['mainContent']['charts']['inflationData'] ?? []);
            const portfolioData = @json($fund->data['mainContent']['charts']['portfolioData'] ?? []);

            const colors = {
                naartjie: '#d25347',
                darkNavy: '#29363d',
                lightBlue: '#7a9cb4',
                lightGrey: '#cccccc',
                darkGrey: '#535353',
                offBlack: '#313131',
            };

            Highcharts.setOptions({
                chart: { style: { fontFamily: "'Avenir Next', 'Lato', sans-serif" } },
                credits: { enabled: false },
                accessibility: { enabled: false },
            });

            const formatXTickInflation = (label) => {
                if (!label) return '';
                const m = label.match(/^(\d{4})-(\d{2})$/);
                if (!m) return label;
                const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                return months[parseInt(m[2], 10) - 1] + '-' + m[1].slice(-2);
            };

            const formatXTickPortfolio = (label) => {
                if (!label) return '';
                const m = label.match(/^(\d{4})-(\d{2})$/);
                if (!m) return label;
                const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                return months[parseInt(m[2], 10) - 1] + ' ' + m[1].slice(-2);
            };

            // Investment Strategy vs SA Inflation
            if (inflationData.length > 0) {
                // Calendar-aligned ticks: every 8 years on December, plus the first available date.
                const inflationDates = inflationData.map(d => d.date);
                const inflationTickPositions = (function () {
                    const idxByDate = {};
                    inflationDates.forEach((d, i) => { idxByDate[d] = i; });
                    const first = inflationDates[0];
                    const firstYear = parseInt(first.slice(0, 4), 10);
                    const positions = [0];
                    // Step every 8 years from the first December that exists in the data.
                    const startDec = firstYear + ((12 - parseInt(first.slice(5, 7), 10) + 12) % 12 === 0 ? 0 : 0); // start from same year if Dec, else next
                    // Use first date's year as base for the December sequence
                    for (let y = firstYear; y <= 2030; y += 8) {
                        const key = y + '-12';
                        if (idxByDate[key] !== undefined && idxByDate[key] !== 0) positions.push(idxByDate[key]);
                    }
                    return positions;
                })();

                // IMPORTANT: keep this chart identical to the on-screen fund page (show.blade.php). Stacked
                // areas with Highcharts' DEFAULT reversedStacks, so the LAST stacked series (Excess) renders at
                // the bottom of the stack and Inflation as the upper band — matching what the page shows.
                // Negative excess (fund below the CPI+5% hurdle) stacks below the 0 line → dark spikes out the bottom.
                const inflationSeries = inflationData.map(d => d.inflation);
                const hurdleSeries    = inflationData.map(d => d.hurdle ?? 5);
                const excessSeries    = inflationData.map(d => d.excess ?? (d.composite - d.inflation - (d.hurdle ?? 5)));
                const compositeSeries = inflationData.map(d => d.composite);

                // Fixed Y-axis to match the published reference layout (-10% to 35%).
                const inflationYMin = -10;
                const inflationYMax = 35;
                const inflationTickPositionsY = [-10, -5, 0, 5, 10, 15, 20, 25, 30, 35];

                Highcharts.chart('inflationChart', {
                    chart: { type: 'area', backgroundColor: 'transparent', spacing: [4, 4, 4, 4], animation: false },
                    title: { text: null },
                    xAxis: {
                        categories: inflationDates,
                        tickWidth: 1,
                        tickLength: 3,
                        tickColor: '#000',
                        lineColor: '#000',
                        lineWidth: 1,
                        labels: {
                            style: { fontSize: '6px', color: colors.offBlack },
                            formatter: function () { return formatXTickInflation(this.value); },
                            rotation: 0,
                        },
                        tickPositions: inflationTickPositions,
                    },
                    yAxis: {
                        title: { text: null },
                        min: inflationYMin, max: inflationYMax,
                        tickPositions: inflationTickPositionsY,
                        gridLineWidth: 0,
                        lineColor: '#000',
                        lineWidth: 1,
                        tickWidth: 1,
                        tickLength: 3,
                        tickColor: '#000',
                        endOnTick: false,
                        startOnTick: false,
                        labels: {
                            style: { fontSize: '6px', color: colors.offBlack },
                            formatter: function () { return this.value + '%'; },
                        },
                    },
                    legend: {
                        itemStyle: { fontSize: '6px', fontWeight: 'normal', color: colors.offBlack },
                        symbolWidth: 14,
                        symbolHeight: 6,
                        symbolRadius: 0,
                        itemDistance: 12,
                        margin: 4,
                        padding: 0,
                    },
                    tooltip: { enabled: false },
                    plotOptions: {
                        area: { stacking: 'normal', marker: { enabled: false }, lineWidth: 0, fillOpacity: 1 },
                        spline: { marker: { enabled: false }, lineWidth: 1 },
                        series: { animation: false },
                    },
                    // Identical series config to the on-screen fund page (show.blade.php): stacked areas with
                    // DEFAULT reversedStacks and no per-area zIndex (last series, Excess, sits at the bottom).
                    // Negative Excess stacks below the 0 line (dark spikes out the bottom). Composite spline on top.
                    // Do NOT add reversedStacks or per-area zIndex here unless you make the SAME change in show.blade.php.
                    series: [
                        { name: 'Composite', type: 'spline', data: compositeSeries, color: colors.naartjie, stacking: undefined, zIndex: 5 },
                        { name: 'Inflation', type: 'area',   data: inflationSeries, color: colors.lightBlue },
                        { name: '5% Hurdle', type: 'area',   data: hurdleSeries,    color: colors.lightGrey },
                        { name: 'Excess',    type: 'area',   data: excessSeries,    color: colors.darkNavy },
                    ],
                });
            }

            // Portfolio Performance vs Benchmark
            if (portfolioData.length > 0) {
                const lastFund = portfolioData[portfolioData.length - 1].fund;
                const lastBenchmark = portfolioData[portfolioData.length - 1].benchmark;
                const formatValue = (v) => 'R ' + Math.round(v / 1).toLocaleString('en-US').replace(/,/g, ',');
                // Display the cash values in thousands of R (the chart shows "R 1,487" for ~1,487,000 cents → R-thousand)
                const formatCashLabel = (v) => 'R ' + Math.round(v).toLocaleString('en-US');

                // Compute a tight log-scale max so the curves fill the plot (no large empty band above).
                const portfolioMaxVal = Math.max(
                    ...portfolioData.map(d => Math.max(d.fund || 0, d.benchmark || 0))
                );
                // Just ~10% above the peak so the data labels sit comfortably near the top.
                const portfolioYMax = portfolioMaxVal * 1.1;

                // Calendar-aligned ticks every 4 years on the inception month, no rotation.
                const portfolioDates = portfolioData.map(d => d.date);
                const portfolioTickPositions = (function () {
                    const idxByDate = {};
                    portfolioDates.forEach((d, i) => { idxByDate[d] = i; });
                    const first = portfolioDates[0];
                    const firstYear = parseInt(first.slice(0, 4), 10);
                    const month = first.slice(5, 7);
                    const positions = [0];
                    for (let y = firstYear + 4; y <= 2030; y += 4) {
                        const key = y + '-' + month;
                        if (idxByDate[key] !== undefined) positions.push(idxByDate[key]);
                    }
                    return positions;
                })();

                Highcharts.chart('portfolioChart', {
                    chart: { type: 'spline', backgroundColor: 'transparent', spacing: [4, 48, 4, 4], animation: false },
                    title: { text: null },
                    xAxis: {
                        categories: portfolioDates,
                        tickWidth: 1,
                        tickLength: 3,
                        tickColor: '#000',
                        lineColor: '#000',
                        lineWidth: 1,
                        labels: {
                            style: { fontSize: '6px', color: colors.offBlack },
                            formatter: function () { return formatXTickPortfolio(this.value); },
                            rotation: 0,
                            autoRotation: false,
                        },
                        tickPositions: portfolioTickPositions,
                    },
                    yAxis: {
                        title: { text: "Cash Value² (R'000)", style: { fontSize: '6px', color: colors.offBlack, fontWeight: '400' } },
                        type: 'logarithmic',
                        gridLineWidth: 0,
                        lineColor: '#000',
                        lineWidth: 1,
                        tickWidth: 1,
                        tickLength: 3,
                        tickColor: '#000',
                        min: 100,
                        max: portfolioYMax,
                        endOnTick: false,
                        startOnTick: false,
                        // One tick per decade — only the "100" label is rendered (see formatter).
                        tickInterval: 1,
                        labels: {
                            style: { fontSize: '6px', color: colors.offBlack },
                            formatter: function () {
                                // Per client amend: show only the 100 label.
                                return this.value === 100 ? '100' : '';
                            },
                        },
                    },
                    legend: {
                        itemStyle: { fontSize: '6px', fontWeight: 'normal', color: colors.offBlack },
                        symbolWidth: 14,
                        symbolHeight: 2,
                        symbolRadius: 0,
                        itemDistance: 12,
                        margin: 4,
                        padding: 0,
                    },
                    tooltip: { enabled: false },
                    plotOptions: {
                        spline: { marker: { enabled: false }, lineWidth: 1 },
                        series: { animation: false },
                    },
                    series: [
                        {
                            name: 'Fund', data: portfolioData.map(d => d.fund), color: colors.naartjie,
                            dataLabels: [{
                                enabled: true, align: 'left', verticalAlign: 'middle', x: 6, y: -4,
                                style: { fontSize: '6px', fontWeight: 'bold', color: colors.naartjie, textOutline: 'none' },
                                formatter: function () { return this.point.index === this.series.data.length - 1 ? formatCashLabel(this.y) : null; },
                                crop: false, overflow: 'allow', allowOverlap: true,
                            }],
                        },
                        {
                            name: 'Benchmark', data: portfolioData.map(d => d.benchmark), color: colors.darkNavy,
                            dataLabels: [{
                                enabled: true, align: 'left', verticalAlign: 'middle', x: 6, y: 4,
                                style: { fontSize: '6px', fontWeight: 'bold', color: colors.darkNavy, textOutline: 'none' },
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
