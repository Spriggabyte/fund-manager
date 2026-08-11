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
           Geometry measured off the signed-off design PDF at 200 dpi
           (1 px @200dpi = 0.127 mm). Body font is Avenir Next (macOS
           system font, matches the design); Lato Light for the title.
           ============================================================= */

        :root {
            --steel-blue: #7a9cb4;
            --naartjie: #d25347;
            --naartjie-20: #f6ddda;
            --dark-navy: #29363d;
            --dark-navy-70: #697277;
            --sidebar-grey: #dfe1e2;
            --medium-grey: #9a9a9a;
            --dark-grey: #535353;
            --body-grey: #414141;
            --off-black: #313131;
            --white: #ffffff;
            /* Table cell greys — the design uses a different grey per table */
            --cell-top10: #f0f0f0;
            --cell-standard: #e6e6e6;
            --cell-perf: #ebebeb;
            --cell-benchmark: #dddddd;
            --cell-benchmark-2: #d4d4d4;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        @page { size: A4 portrait; margin: 0; }

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

        /* ── Page Container ── */
        .page {
            width: 210mm;
            height: 297mm;
            max-height: 297mm;
            overflow: hidden;
            position: relative;
            page-break-after: always;
            background: var(--white);
            display: flex;
            flex-direction: column;
        }
        .page:last-child { page-break-after: auto; }

        /* ── Header (page 1) ──
           White page top; grey block only over the sidebar column
           (4mm in from the page edge), big date badge inside it. */
        .header-row {
            display: flex;
            height: 26.4mm;
            min-height: 26.4mm;
        }
        .header-sidebar-bg {
            margin-left: 4mm;
            width: 56mm;
            min-width: 56mm;
            background-color: var(--sidebar-grey);
            padding: 10mm 0 0 4.1mm;
        }
        .header-main {
            flex: 1;
            display: flex;
            justify-content: flex-end;
            align-items: flex-start;
            padding: 7mm 1.5mm 0 4mm;
        }

        .date-badge {
            background-color: var(--naartjie);
            color: var(--white);
            width: 45.9mm;
            height: 10.9mm;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 500;
            font-size: 12pt;
            letter-spacing: 0.01em;
        }

        .logo img {
            height: 13.7mm;
            width: auto;
        }

        /* ── Title Banner (full bleed) ── */
        .title-banner {
            background-color: var(--dark-navy);
            color: var(--white);
            height: 33.8mm;
            min-height: 33.8mm;
            padding: 2.6mm 8mm 0 8mm;
        }

        .fund-name {
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 500;
            font-size: 23.5pt;
            letter-spacing: 0.01em;
            text-transform: uppercase;
            margin: 0;
            line-height: 1.05;
        }

        .fund-name .class-suffix {
            font-weight: 500;
            font-size: 16pt;
        }

        .fund-description {
            font-family: 'Merriweather', Georgia, serif;
            font-weight: 400;
            font-size: 9pt;
            line-height: 4.3mm;
            /* Matches the signed-off banner (pdf.blade.php) — without this the
               description wraps one word wide of the reference. */
            letter-spacing: 0.01em;
            margin: 0.6mm 0 0 0;
        }

        /* ── Content Wrapper ── */
        .content-wrapper {
            display: flex;
            flex-direction: row;
            flex: 1;
            padding-left: 4mm;
        }

        /* ── Sidebar ── */
        .sidebar {
            width: 56mm;
            min-width: 56mm;
            max-width: 56mm;
            background-color: var(--sidebar-grey);
            padding: 5.5mm 2.2mm 3mm 4.1mm;
            overflow: hidden;
        }

        .sidebar-section { margin-bottom: 2.3mm; }
        .sidebar-section:last-child { margin-bottom: 0; }

        /* Reference: labels are SMALLER than the body and pure black; the body
           copy is the larger, near-black text (matches pdf.blade.php). */
        .sidebar-heading {
            font-weight: 500;
            font-size: 6pt;
            line-height: 2.4mm;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            color: #000000;
            margin: 0;
        }

        .sidebar-text {
            font-weight: 400;
            font-size: 7pt;
            line-height: 2.85mm;
            color: #000000;
            margin: 0;
        }

        /* Equity Indicator Dots — inline SVG circles, NOT border-radius spans:
           Chromium's print-to-PDF engine rasterises border-radius + background-color
           as a rounded rect (squashed dots in the exported PDF), while SVG circles
           stay perfectly round. Per the design the dots sit on the SAME line as
           the EQUITY INDICATOR heading. */
        .sidebar-heading-with-dots {
            display: flex;
            align-items: center;
            gap: 1.6mm;
        }
        .equity-indicator { display: flex; gap: 0.5mm; }
        .equity-dot { width: 1.4mm; height: 1.4mm; display: inline-block; flex: 0 0 1.4mm; overflow: visible; }
        .equity-dot.filled circle { fill: var(--naartjie); }
        .equity-dot.empty circle { fill: none; stroke: var(--medium-grey); stroke-width: 0.9; }

        /* ── Main Content ── */
        .main-content {
            flex: 1;
            padding: 3.7mm 6.6mm 0 4.5mm;
            min-width: 0;
            overflow: hidden;
        }

        /* ── Section Headings ── */
        .section-heading {
            font-weight: 500;
            font-size: 7.5pt;
            line-height: 9pt;
            letter-spacing: 0.015em;
            text-transform: uppercase;
            color: var(--dark-navy);
            margin: 0 0 1.4mm 0;
        }

        /* Reference: same size/colour as the heading itself */
        .section-subheading {
            font-weight: 500;
            font-size: 7.5pt;
            line-height: 9pt;
            color: var(--dark-navy);
            margin: -0.8mm 0 1.8mm 0;
        }

        /* ── Two-Column Grid ── */
        .two-col {
            display: flex;
            gap: 4mm;
            margin-bottom: 7.8mm;
        }
        .two-col > * { flex: 1; min-width: 0; }
        .two-col.row-2 { margin-bottom: 2.5mm; }

        /* ── Tables ── */
        .table-container {
            position: relative;
            margin-bottom: 2mm;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7pt;
        }

        table th {
            background-color: var(--dark-navy);
            color: var(--white);
            font-weight: 500;
            font-size: 6.5pt;
            line-height: 7.5pt;
            text-transform: uppercase;
            text-align: center;
            padding: 0.9mm 1.5mm;
            border-right: 0.4mm solid var(--white);
            border-bottom: 0.4mm solid var(--white);
        }
        table th:first-child { text-align: left; }
        table th:last-child { border-right: none; }

        table td {
            font-size: 7pt;
            line-height: 2.85mm;
            padding: 0.5mm 1.5mm;
            border-bottom: 0.4mm solid var(--white);
            border-right: 0.4mm solid var(--white);
            text-align: right;
        }
        table td:first-child { text-align: left; }
        table td:last-child { border-right: none; }

        /* Uniform light-grey rows separated by white gutters (not zebra). */
        table tbody td { background-color: var(--cell-top10); }

        .total-row td {
            background-color: var(--naartjie) !important;
            color: var(--white);
            font-weight: 500;
            border-bottom: none;
        }

        .highlight-row td { background-color: var(--naartjie-20) !important; }

        /* ── Sector Allocation Bars ──
           Bars use an ABSOLUTE scale (0.643mm per percentage point) so the
           chart matches the design regardless of the month's max value. */
        .sector-row {
            display: flex;
            align-items: center;
            height: 4.03mm;
        }
        .sector-label {
            width: 34.7mm;
            flex-shrink: 0;
            font-size: 7pt;
            line-height: 2.85mm;
            color: var(--off-black);
            padding-right: 1.5mm;
            white-space: nowrap;
        }
        .sector-bar-track {
            flex: 1;
            height: 3.05mm;
            position: relative;
        }
        .sector-bar-fill {
            height: 100%;
            background-color: var(--naartjie);
            min-width: 1mm;
            max-width: 100%;
        }
        .sector-value {
            width: 5mm;
            flex-shrink: 0;
            text-align: right;
            font-size: 7pt;
            line-height: 2.85mm;
            color: var(--off-black);
        }
        .sector-change {
            width: 8.2mm;
            flex-shrink: 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-left: 1.6mm;
            font-size: 7pt;
            line-height: 2.85mm;
            color: var(--off-black);
        }
        .sector-change .arrow { font-size: 5.5pt; }
        /* Reference: up-triangles are pure black (down stays steel blue) */
        .sector-change.up .arrow { color: #000000; }
        .sector-change.down .arrow { color: var(--steel-blue); }

        /* ── Asset Allocation Table ── */
        .asset-table { margin-bottom: 0; }
        .asset-table table tbody td { background-color: var(--cell-standard); }
        .asset-table table th {
            font-size: 6.5pt;
            padding: 0.9mm 1.5mm;
        }
        .asset-table table td {
            padding: 0.75mm 1.5mm;
        }
        .asset-table .indent td:first-child {
            padding-left: 3.5mm;
            color: var(--dark-navy-70);
        }

        /* ── Chart containers ── */
        .chart-wrapper { position: relative; }
        canvas { display: block; width: 100%; }

        /* ── Monthly Chart Legend ── */
        .monthly-legend {
            display: flex;
            justify-content: center;
            gap: 6mm;
            margin-top: 1.4mm;
            font-size: 6.3pt;
            line-height: 7.5pt;
            color: var(--off-black);
        }
        .monthly-legend-item { display: flex; align-items: center; gap: 1.2mm; }
        .monthly-legend-swatch { width: 2mm; height: 2mm; display: inline-block; }

        /* ── Chart Description ── */
        .chart-description {
            font-size: 6.3pt;
            line-height: 2.34mm;
            color: var(--body-grey);
            margin: 2.4mm 0 3mm 0;
        }

        /* ── Performance Table ── */
        .perf-table table td { background-color: var(--cell-perf); }
        .perf-table table th {
            font-size: 6pt;
            line-height: 7pt;
            padding: 1mm 1mm;
            text-align: right;
        }
        .perf-table table th:first-child { text-align: left; width: 20%; }
        .perf-table table td {
            font-size: 7.5pt;
            line-height: 2.75mm;
            padding: 0.4mm 1mm;
        }
        .perf-table .highlight-row td { background-color: var(--naartjie-20) !important; }
        .perf-table .row-benchmark td { background-color: var(--cell-benchmark); }
        .perf-table sup { font-size: 4.5pt; }

        /* Blank spacer row between Benchmark and Fund highest — a full-height
           grey row, slightly darker than the data rows, per the design. */
        .separator-row td {
            background-color: var(--cell-standard);
            height: 3.6mm;
            padding: 0;
            font-size: 1pt;
            line-height: 1pt;
        }

        /* ── Footnotes ── */
        .footnotes {
            margin-top: 1.6mm;
        }
        .footnotes p {
            font-size: 6pt;
            line-height: 2.35mm;
            color: var(--dark-grey);
        }
        /* Footnote markers: raised near-full-size digits (the design does NOT
           use the small precomposed Unicode superscript glyphs). */
        .footnotes sup {
            font-size: 5.5pt;
            line-height: 0;
            vertical-align: baseline;
            position: relative;
            top: -0.8mm;
        }

        /* ── Low Carbon Badge (cropped from the signed-off design) ── */
        .low-carbon-badge { margin-top: 3.5mm; }
        .low-carbon-badge img { width: 35mm; height: auto; display: block; margin-left: 3.5mm; }

        /* ============================
           PAGE 2 STYLES
           ============================ */
        /* Signed-off geometry (pdf.blade.php): navy box ~45.7 x 11mm at
           (9.15mm, 10mm), paragraphs 6.5pt Lato Light in dark navy — the
           published equity reference matches the balanced one here. */
        .page-2 .sidebar {
            padding: 10mm 5.2mm 4mm 5.15mm;
        }

        .imp-info-header {
            background-color: var(--dark-navy);
            color: var(--white);
            height: 11mm;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 0 2mm;
            margin-bottom: 6.3mm;
            font-weight: 500;
            font-size: 8pt;
            line-height: 10.9pt;
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }

        .imp-info-text {
            font-family: 'Lato', 'Avenir Next', sans-serif;
            font-weight: 300;
            font-size: 6.5pt;
            line-height: 7.33pt;
            letter-spacing: 0.01em;
            color: var(--dark-navy);
            margin-bottom: 1.4mm;
        }

        .page-2 .main-content {
            padding: 27.4mm 6.6mm 9mm 4.5mm;
            display: flex;
            flex-direction: column;
        }

        .fee-section { margin-bottom: 5.4mm; }

        /* Page-2 headings sit further off their tables than page 1 */
        .page-2 .section-heading { margin-bottom: 2.6mm; }

        .fee-table table td {
            text-align: left;
            font-size: 7pt;
            line-height: 2.85mm;
            padding: 0.65mm 2mm;
            background-color: var(--cell-standard);
        }
        .fee-table table td:first-child {
            width: 52%;
        }

        /* TIC table: main rows white, the "—" sub-rows shaded. */
        .tic-table table td { padding: 0.85mm 2mm; }
        .tic-table tbody td { background-color: var(--white); }
        .tic-table tbody .row-sub td { background-color: var(--cell-standard); }

        /* Performance fee examples: Foord row pink, benchmark row darker
           grey, remaining rows standard grey (mirrors the page-1 table). */
        .examples-table table th { text-align: right; padding-right: 2mm; }
        .examples-table table th:first-child { text-align: left; }
        .examples-table table td { padding: 0.8mm 2mm; background-color: var(--cell-standard); }
        .examples-table .row-foord td { background-color: var(--naartjie-20); }
        .examples-table .row-bench td { background-color: var(--cell-benchmark-2); }

        /* Page-2 numeric tables centre their value columns */
        .table-center table td:not(:first-child),
        .table-center table th:not(:first-child) { text-align: center; }

        .fee-description {
            font-size: 7.7pt;
            line-height: 3.25mm;
            color: #4d585e;
            margin: 2.3mm 0 0 0;
        }

        /* Reference: consecutive paragraphs run on with a plain line break —
           no paragraph spacing between them. */
        .perf-fees-text {
            font-size: 7.7pt;
            line-height: 3.25mm;
            color: #4d585e;
            margin-bottom: 0;
        }

        .perf-fee-footnote {
            font-size: 6.3pt;
            line-height: 7.5pt;
            color: var(--body-grey);
            margin-top: 1.2mm;
        }

        /* ── Footer (pinned to the bottom of page 2) ── */
        .footer {
            margin-top: auto;
        }
        .footer-separator {
            width: 14mm;
            border-top: 0.4mm solid var(--naartjie);
            margin-bottom: 4.5mm;
        }
        .footer-info {
            font-family: 'Merriweather', Georgia, serif;
            font-weight: 400;
            font-size: 8pt;
            line-height: 10.1pt;
            color: var(--naartjie);
            margin-bottom: 3.5mm;
        }
        .footer-free {
            font-family: 'Merriweather', Georgia, serif;
            font-weight: 400;
            font-size: 8pt;
            line-height: 10.1pt;
            color: var(--naartjie);
            margin-bottom: 5mm;
        }
        .footer-contact {
            font-weight: 500;
            font-size: 8.5pt;
            line-height: 3.8mm;
            letter-spacing: 0.01em;
            color: var(--naartjie);
        }
        .footer-contact p { margin: 0; }
        .footer-bottom {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }
        .footer-logo img { height: 10mm; width: auto; }
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
                <div class="logo">
                    <img src="{{ $fund->data['fund']['logoUrl'] ?? '' }}" alt="Foord">
                </div>
            </div>
        </div>

        <!-- Title Banner -->
        <div class="title-banner">
            @php
                $fullName = $fund->data['fund']['name'] ?? $fund->name;
                // Try to split at " – CLASS", " — CLASS" or " - CLASS"
                $parts = preg_split('/(\s[–—\-]\s(?:CLASS\s))/iu', $fullName, 2, PREG_SPLIT_DELIM_CAPTURE);
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
                            'minimums' => 'MINIMUM LUMP SUM / MONTHLY',
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
                                @if ($key === 'equityIndicator' && is_array($fund->data['sidebar'][$key]))
                                    @php
                                        $eq = $fund->data['sidebar'][$key];
                                        $filled = $eq['filled'] ?? 10;
                                        $total = $eq['total'] ?? 10;
                                    @endphp
                                    {{-- Dots sit inline with the heading, per the design --}}
                                    <div class="sidebar-heading-with-dots">
                                        <p class="sidebar-heading">{{ $label }}</p>
                                        <div class="equity-indicator">
                                            @for ($i = 0; $i < $total; $i++)
                                                <svg class="equity-dot {{ $i < $filled ? 'filled' : 'empty' }}" viewBox="0 0 10 10" xmlns="http://www.w3.org/2000/svg"><circle cx="5" cy="5" r="{{ $i < $filled ? '5' : '4.55' }}"/></svg>
                                            @endfor
                                        </div>
                                    </div>
                                    <p class="sidebar-text">{{ $eq['description'] ?? '' }}</p>
                                @elseif (is_array($fund->data['sidebar'][$key]))
                                    <p class="sidebar-heading">{{ $label }}</p>
                                    <p class="sidebar-text">{{ $fund->data['sidebar'][$key]['description'] ?? '' }}</p>
                                @else
                                    <p class="sidebar-heading">{{ $label }}</p>
                                    <p class="sidebar-text">{!! $fund->data['sidebar'][$key] !!}</p>
                                @endif
                            </div>
                        @endif
                    @endforeach

                    <div class="low-carbon-badge">
                        <img src="{{ asset('images/low-carbon.png') }}" alt="Achieved the Morningstar® Low Carbon designation">
                    </div>
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
                                        {{-- 0.643mm per percentage point (absolute scale, per design) --}}
                                        <div class="sector-bar-fill" style="width: {{ number_format($sector['value'] * 0.643, 2) }}mm;"></div>
                                    </div>
                                    <div class="sector-value">{{ $sector['value'] }}</div>
                                    <div class="sector-change {{ ($sector['direction'] ?? '') === 'up' ? 'up' : (($sector['direction'] ?? '') === 'down' ? 'down' : '') }}">
                                        @if(($sector['direction'] ?? '') === 'up')
                                            <span class="arrow">▲</span>
                                        @elseif(($sector['direction'] ?? '') === 'down')
                                            <span class="arrow">▼</span>
                                        @else
                                            <span class="arrow"></span>
                                        @endif
                                        <span>{{ $sector['change'] ?? '' }}</span>
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
                <div class="two-col row-2">
                    <!-- Top 10 Investments -->
                    @if(isset($fund->data['mainContent']['topInvestments']))
                        <div>
                            <h3 class="section-heading">{{ $fund->data['mainContent']['topInvestments']['title'] ?? 'TOP 10 INVESTMENTS' }}</h3>
                            <div class="table-container">
                                <table>
                                    <thead>
                                        <tr>
                                            @foreach ($fund->data['mainContent']['topInvestments']['headers'] as $header)
                                                <th @if($loop->last) style="width: 32%;" @endif>{{ $header }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($fund->data['mainContent']['topInvestments']['rows'] as $row)
                                            <tr>
                                                <td>{{ $row['security'] }}</td>
                                                <td>{{ is_numeric($row['percentage']) ? number_format((float) $row['percentage'], 1) : $row['percentage'] }}</td>
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
                                <canvas id="portfolioChart" style="height: 45mm;"></canvas>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Monthly Portfolio Performance vs Benchmark (bar chart) -->
                @if(isset($fund->data['mainContent']['charts']['monthlyData']))
                    <div>
                        <h3 class="section-heading">MONTHLY PORTFOLIO PERFORMANCE VS BENCHMARK</h3>
                        <div class="chart-wrapper">
                            <canvas id="monthlyChart" style="height: 43mm;"></canvas>
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
                                    @php
                                        // Reference design: superscript footnote markers on the row
                                        // names, the Fund row highlighted, values to one decimal.
                                        $perfMarkers = [
                                            'Fund' => '3',
                                            'Benchmark' => '4',
                                            'Fund highest' => '3,5',
                                            'Fund lowest' => '3,5',
                                        ];
                                        $perfCols = ['cashValue', 'sinceInception', '15yrs', '10yrs', '7yrs', '5yrs', '3yrs', '1yr', 'thisMonth'];
                                        $fmtPerf = fn ($v) => is_numeric($v) ? number_format((float) $v, 1) : ($v ?? '');
                                    @endphp
                                    @foreach ($fund->data['mainContent']['performanceTable']['rows'] as $rowIndex => $row)
                                        @if($rowIndex === 2)
                                            {{-- Blank grey row between Benchmark and Fund highest (per reference) --}}
                                            <tr class="separator-row">
                                                @foreach ($fund->data['mainContent']['performanceTable']['headers'] as $ignored)
                                                    <td></td>
                                                @endforeach
                                            </tr>
                                        @endif
                                        @php
                                            $name = $row['name'] ?? '';
                                            $plainName = trim(strip_tags($name));
                                            $marker = $perfMarkers[$plainName] ?? null;
                                            $rowClass = '';
                                            if ($row['highlight'] ?? ($plainName === 'Fund')) {
                                                $rowClass = 'highlight-row';
                                            } elseif ($plainName === 'Benchmark') {
                                                $rowClass = 'row-benchmark';
                                            }
                                        @endphp
                                        <tr class="{{ $rowClass }}">
                                            <td>{!! $name !!}@if($marker)<sup> {{ $marker }}</sup>@endif</td>
                                            <td>{{ $row['cashValue'] ?? '' }}</td>
                                            @foreach (array_slice($perfCols, 1) as $col)
                                                <td>{{ $fmtPerf($row[$col] ?? null) }}</td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Footnotes -->
                    @if(isset($fund->data['mainContent']['performanceTable']['footnotes']))
                        <div class="footnotes">
                            @php
                                // The design uses raised full-size digits, not the small
                                // precomposed Unicode superscript glyphs the data carries.
                                $supMap = ['¹' => '<sup>1</sup>', '²' => '<sup>2</sup>', '³' => '<sup>3</sup>', '⁴' => '<sup>4</sup>', '⁵' => '<sup>5</sup>', '⁶' => '<sup>6</sup>'];
                            @endphp
                            @foreach ($fund->data['mainContent']['performanceTable']['footnotes'] as $footnote)
                                <p>{!! strtr($footnote, $supMap) !!}</p>
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
                    <div class="fee-section">
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
                    <div class="fee-section">
                        <h3 class="section-heading">{{ $fund->data['fees']['totalInvestmentCharge']['title'] }}</h3>
                        @php
                            $fmtTic = fn ($v) => is_numeric($v) ? number_format((float) $v, 2) : ($v ?? '');
                        @endphp
                        <div class="fee-table tic-table table-center table-container">
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
                                        <tr class="{{ str_starts_with($row['name'], '—') ? 'row-sub' : '' }}">
                                            <td>{{ $row['name'] }}</td>
                                            <td>{{ $fmtTic($row['12m'] ?? null) }}</td>
                                            <td>{{ $fmtTic($row['36m'] ?? null) }}</td>
                                        </tr>
                                    @endforeach
                                    <tr class="total-row">
                                        <td>{{ $fund->data['fees']['totalInvestmentCharge']['total']['name'] }}</td>
                                        <td>{{ $fmtTic($fund->data['fees']['totalInvestmentCharge']['total']['12m'] ?? null) }}</td>
                                        <td>{{ $fmtTic($fund->data['fees']['totalInvestmentCharge']['total']['36m'] ?? null) }}</td>
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
                    <div class="fee-section">
                        <h3 class="section-heading">{{ $fund->data['fees']['performanceFees']['title'] }}</h3>
                        @foreach ($fund->data['fees']['performanceFees']['paragraphs'] as $paragraph)
                            <p class="perf-fees-text">{{ $paragraph }}</p>
                        @endforeach
                    </div>
                @endif

                <!-- Performance Fee Examples -->
                @if(isset($fund->data['fees']['performanceFeeExamples']))
                    <div class="fee-section">
                        <h3 class="section-heading">{{ $fund->data['fees']['performanceFeeExamples']['title'] }}</h3>
                        <div class="examples-table table-container">
                            <table>
                                <thead>
                                    <tr>
                                        @foreach ($fund->data['fees']['performanceFeeExamples']['headers'] as $header)
                                            <th>{{ $header }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($fund->data['fees']['performanceFeeExamples']['rows'] as $rowIndex => $row)
                                        <tr class="{{ $rowIndex === 0 ? 'row-foord' : ($rowIndex === 1 ? 'row-bench' : '') }}">
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
                        <div class="footer-separator"></div>
                        <p class="footer-info">{{ $fund->data['footer']['info'] }}</p>
                        <p class="footer-free">{{ $fund->data['footer']['freeOfCharge'] }}</p>
                        <div class="footer-bottom">
                            <div class="footer-contact">
                                <p>T. {{ $fund->data['footer']['contact']['phone'] }}</p>
                                <p>E. {{ $fund->data['footer']['contact']['email'] }}</p>
                                <p>{{ $fund->data['footer']['contact']['website'] }}</p>
                            </div>
                            <div class="footer-logo">
                                <img src="{{ asset('images/leaf.png') }}" alt="Foord">
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

        Chart.defaults.font.family = "'Avenir Next', 'Lato', sans-serif";
        Chart.defaults.font.size = 7;

        // "2002-09" → "Sep 02"; anything else passes through untouched.
        const MONTHS = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        function formatMonthLabel(value) {
            const m = /^(\d{4})-(\d{2})$/.exec(value);
            return m ? MONTHS[parseInt(m[2], 10) - 1] + ' ' + m[1].slice(2) : value;
        }
        // Reference design: x ticks every 48 months, anchored on the first
        // September (the fund's inception month) — "Sep 02 Sep 06 … Sep 22".
        // Returns the category indices so afterBuildTicks can drop all other
        // ticks (needed so the axis tick MARKS only appear at the labels,
        // matching the reference, instead of at all ~280 months).
        function anchoredTickIndices(dates) {
            const anchor = dates.findIndex(d => /^\d{4}-09$/.test(d));
            const idx = [];
            if (anchor >= 0) for (let i = anchor; i < dates.length; i += 48) idx.push(i);
            return idx;
        }

        // Annotation plugin for end-of-line labels. Labels are nudged apart
        // when the series converge (the log scale squeezes them together).
        const endLabelPlugin = {
            id: 'endLabels',
            afterDraw(chart) {
                const { ctx: c, data } = chart;
                const MIN_GAP = 9;
                const labels = data.datasets.map((ds, i) => {
                    const vals = ds.data;
                    const meta = chart.getDatasetMeta(i);
                    const lastPt = meta.data[meta.data.length - 1];
                    if (!lastPt) return null;
                    return {
                        text: 'R ' + Math.round(Number(vals[vals.length - 1])).toLocaleString(),
                        color: ds.borderColor,
                        x: lastPt.x + 4,
                        y: lastPt.y
                    };
                }).filter(Boolean).sort((a, b) => a.y - b.y);
                for (let i = 1; i < labels.length; i++) {
                    const gap = labels[i].y - labels[i - 1].y;
                    if (gap < MIN_GAP) {
                        const shift = (MIN_GAP - gap) / 2;
                        labels[i - 1].y -= shift;
                        labels[i].y += shift;
                    }
                }
                c.save();
                c.font = "500 9px 'Avenir Next', Lato, sans-serif";
                c.textAlign = 'left';
                c.textBaseline = 'middle';
                labels.forEach(l => {
                    c.fillStyle = l.color;
                    c.fillText(l.text, l.x, l.y);
                });
                c.restore();
            }
        };

        // Portfolio Performance vs Benchmark (line chart)
        @if(isset($fund->data['mainContent']['charts']['portfolioData']))
        {
            const data = @json($fund->data['mainContent']['charts']['portfolioData']);
            const dates = data.map(d => d.date);
            const keepTicks = new Set(anchoredTickIndices(dates));
            const ctx = document.getElementById('portfolioChart');
            if (ctx) {
                new Chart(ctx.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: dates,
                        datasets: [
                            {
                                label: 'Fund',
                                data: data.map(d => d.fund),
                                borderColor: C.naartjie,
                                backgroundColor: 'transparent',
                                borderWidth: 1.5,
                                tension: 0.1,
                                pointRadius: 0,
                                pointStyle: 'line'
                            },
                            {
                                label: 'Benchmark',
                                data: data.map(d => d.benchmark),
                                borderColor: C.darkNavy,
                                backgroundColor: 'transparent',
                                borderWidth: 1.5,
                                tension: 0.1,
                                pointRadius: 0,
                                pointStyle: 'line'
                            }
                        ]
                    },
                    plugins: [endLabelPlugin],
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: false,
                        layout: { padding: { right: 34 } },
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    // Reference legend samples are long thin rules
                                    usePointStyle: true,
                                    pointStyleWidth: 30,
                                    boxHeight: 8,
                                    padding: 8,
                                    color: C.darkNavy,
                                    font: { size: 8 }
                                }
                            },
                            tooltip: { enabled: false }
                        },
                        scales: {
                            x: {
                                // No gridlines, but black axis line + tick marks at the labels
                                border: { display: true, color: '#000', width: 1 },
                                grid: { drawOnChartArea: false, drawTicks: true, tickLength: 3, tickColor: '#000' },
                                afterBuildTicks: axis => { axis.ticks = axis.ticks.filter(t => keepTicks.has(t.value)); },
                                ticks: {
                                    font: { size: 8 },
                                    color: '#000',
                                    maxRotation: 0,
                                    autoSkip: false,
                                    callback: v => formatMonthLabel(dates[v])
                                }
                            },
                            y: {
                                type: 'logarithmic',
                                min: 100,
                                title: { display: true, text: 'Cash Value² (R\'000)', font: { size: 9 }, color: C.darkGrey },
                                border: { display: true, color: '#000', width: 1 },
                                ticks: {
                                    font: { size: 8 },
                                    color: '#000',
                                    // Per the reference design only the "100" label shows.
                                    callback: v => v === 100 ? '100' : ''
                                },
                                // Reference has NO horizontal gridlines — plain plot
                                grid: { display: false }
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
            const dates = data.map(d => d.date);
            const keepTicks = new Set(anchoredTickIndices(dates));
            const ctx = document.getElementById('monthlyChart');
            if (ctx) {
                new Chart(ctx.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: dates,
                        datasets: [{
                            data: data.map(d => d.relative),
                            backgroundColor: data.map(d => d.benchmarkNegative ? C.naartjie : C.darkNavy),
                            borderWidth: 0,
                            // Reference bars are ~2px at 150dpi with hairline gaps
                            barPercentage: 1,
                            categoryPercentage: 0.95
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: false,
                        // Pull the plot's edges in to the reference width
                        layout: { padding: { left: 6, right: 12 } },
                        plugins: {
                            legend: { display: false },
                            tooltip: { enabled: false }
                        },
                        scales: {
                            x: {
                                border: { display: true, color: '#000', width: 1 },
                                grid: { drawOnChartArea: false, drawTicks: true, tickLength: 3, tickColor: '#000' },
                                afterBuildTicks: axis => { axis.ticks = axis.ticks.filter(t => keepTicks.has(t.value)); },
                                ticks: {
                                    font: { size: 8 },
                                    color: '#000',
                                    maxRotation: 0,
                                    autoSkip: false,
                                    callback: v => formatMonthLabel(dates[v])
                                }
                            },
                            y: {
                                min: -10,
                                max: 10,
                                // Reference: y-axis line + tick marks, no gridlines
                                border: { display: true, color: '#000', width: 1 },
                                grid: { drawOnChartArea: false, drawTicks: true, tickLength: 3, tickColor: '#000' },
                                ticks: {
                                    stepSize: 5,
                                    font: { size: 8 },
                                    color: '#000',
                                    callback: v => v + '%'
                                }
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
