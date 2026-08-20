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

        /* Foord Brand Colors — greys measured from the published reference PDF
           (Foord Balanced Fund Class A at 2026-01-31). Row greys fade in the
           order grey-1 (darkest) → grey-4 (lightest). */
        :root {
            --naartjie: #d25347;
            --naartjie-75: #dd7e75;
            --naartjie-50: #e9a9a3;
            --naartjie-20: #f6ddda;
            --dark-navy: #29363d;
            --dark-navy-70: #697277;
            --dark-navy-30: #bfc3c5;
            --dark-navy-15: #dfe1e2;
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
            --row-grey-1: #dddddd;
            --row-grey-2: #e6e6e6;
            --row-grey-3: #ebebeb;
            --row-grey-4: #f0f0f0;
            --pfe-grey: #d4d4d4;
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
            font-size: 7.5pt;
            line-height: 1.2;
            color: #000;
            background: var(--white);
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* Page Container - grey sidebar band 4mm→60mm, full page height
           (reference: white 4mm strip on the left edge). */
        .page {
            width: 210mm;
            height: 297mm;
            max-height: 297mm;
            overflow: hidden;
            padding: 0;
            position: relative;
            page-break-after: always;
            background: linear-gradient(to right, var(--white) 4mm, var(--dark-navy-15) 4mm, var(--dark-navy-15) 60mm, var(--white) 60mm);
        }

        .page:last-child {
            page-break-after: auto;
        }

        /* =====================================================
           HEADER SECTION
           ===================================================== */
        /* Reference geometry: red date badge 46 x 11mm at (8mm, 10mm);
           logo 51.7 x 13mm, right edge at 204.5mm, top at 9mm. */
        .header {
            position: relative;
            height: 26.5mm;
        }

        /* Reference (Foord Balanced Fund Class A at 2026-01-31): badge
           45.9 x 10.9mm at (8mm, 10mm); text asc-to-desc 3.34mm (~10.4pt),
           medium weight, optically centred. */
        .date-badge {
            position: absolute;
            /* Reference badge left edge sits at 8mm (x=47px at 150dpi), NOT
               centred in the grey band. */
            left: 8mm;
            top: 10mm;
            width: 45.9mm;
            height: 10.9mm;
            box-sizing: border-box;
            display: flex;
            align-items: center;
            justify-content: center;
            /* No optical correction: the reference text sits ~0.2mm below
               geometric centre, which Lato's tall ascent produces naturally. */
            background-color: var(--naartjie);
            color: #ffffff;
            font-family: 'Lato', 'Avenir Next', sans-serif;
            font-weight: 500;
            font-size: 10.4pt;
            letter-spacing: 0.01em;
            /* Avenir Next's word space is ~0.3mm wider than Lato's. */
            word-spacing: 0.3mm;
            text-align: center;
        }

        .logo {
            position: absolute;
            top: 9mm;
            right: 5.5mm;
            height: 13mm;
        }

        .logo img {
            height: 100%;
            width: auto;
        }

        /* =====================================================
           TITLE BANNER
           ===================================================== */
        /* Reference: navy band 34mm tall (26.5mm → 60.5mm), text inset 7.75mm
           from the page's left edge (aligned with the sidebar text). */
        .title-banner {
            background-color: var(--dark-navy);
            color: var(--white);
            height: 34mm;
            box-sizing: border-box;
            /* Reference title block sits ~1.5mm lower inside the navy band. */
            padding: 5.1mm 6mm 0 7.75mm;
            margin: 0;
            width: 100%;
        }

        .fund-name {
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 500;
            font-size: 23pt;
            letter-spacing: 0.01em;
            text-transform: uppercase;
            margin: 0 0 1.1mm 0;
            line-height: 1.05;
        }

        .fund-name .class-suffix {
            font-weight: 500;
            font-size: 15pt;
        }

        .fund-description {
            font-family: 'Merriweather', Georgia, serif;
            font-weight: 400;
            font-size: 9pt;
            line-height: 11.3pt;
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
            min-height: calc(297mm - 26.5mm - 34mm); /* page - header - title banner */
        }

        .page-2 .content-wrapper {
            min-height: 297mm;
        }

        /* Sidebar - 60mm wide (grey band 4mm→60mm); text starts at x=8mm */
        .sidebar {
            width: 60mm;
            min-width: 60mm;
            max-width: 60mm;
            background-color: transparent;
            padding: 5.4mm 4mm 4mm 8mm;
            overflow: hidden;
        }

        .sidebar-section {
            margin-bottom: 1.05mm;
        }

        .sidebar-section:last-child {
            margin-bottom: 0;
        }

        .sidebar-heading {
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 500;
            font-size: 6pt;
            line-height: 6.8pt;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            color: #000;
            margin: 0;
        }

        .sidebar-text {
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 400;
            font-size: 7pt;
            line-height: 8.2pt;
            letter-spacing: 0.01em;
            color: #000;
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
            gap: 0.33mm;
            align-items: center;
        }

        /* The dots are inline SVG circles, NOT border-radius spans: Chromium's
           print-to-PDF engine rasterises border-radius + background-color as a
           rounded rect (squashed dots in the exported PDF), while SVG circles
           stay perfectly round. */
        .equity-dot {
            width: 1.32mm;
            height: 1.32mm;
            display: inline-block;
            flex: 0 0 1.32mm;
            /* The circle touches the viewBox edge; without this, sub-pixel
               rounding of the box clips a flat sliver off the circle edge. */
            overflow: visible;
        }

        .equity-dot.filled circle {
            fill: var(--naartjie);
        }

        /* Reference: unfilled dots are solid grey, not outlined */
        .equity-dot.empty circle {
            fill: var(--medium-grey);
        }

        /* Main Content Area — spans x=64mm → 204mm (140mm wide) */
        .main-content {
            flex: 1;
            /* 6.4mm top lands the ASSET ALLOCATION header row at the
               reference y=442px/150dpi. */
            padding: 6.4mm 6mm 4mm 4mm;
            min-width: 0;
            overflow: hidden;
        }

        /* =====================================================
           SECTION HEADINGS — 7.5pt Avenir Next Medium, dark navy
           ===================================================== */
        .section-heading {
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 500;
            font-size: 7.5pt;
            line-height: 9pt;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            color: var(--dark-navy);
            margin: 0 0 0.8mm 0;
        }

        .section-subheading {
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 500;
            font-size: 7.5pt;
            line-height: 9pt;
            letter-spacing: 0.01em;
            color: var(--dark-navy);
            margin: -0.5mm 0 0.9mm 0;
        }

        /* Smaller suffix style for parenthetical text in section headings */
        .section-heading .title-suffix {
            font-size: 6pt;
            font-weight: 500;
            color: var(--dark-navy);
            text-transform: uppercase;
            letter-spacing: 0.01em;
        }

        /* =====================================================
           TABLES
           ===================================================== */
        .table-container {
            position: relative;
            margin-bottom: 2.6mm;
        }

        /* White cell separators are 0.38mm in the reference */
        table {
            width: 100%;
            table-layout: fixed;
            border-collapse: separate;
            border-spacing: 1.1pt 1.1pt;
            margin-left: -1.1pt;
            margin-right: -1.1pt;
            font-size: 7.5pt;
        }

        table th {
            background-color: var(--dark-navy);
            color: var(--white);
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 500;
            font-size: 7.5pt;
            line-height: 8.5pt;
            letter-spacing: 0;
            text-transform: uppercase;
            text-align: right;
            padding: 0.6mm 1.4mm 0.6mm 1.5mm;
        }

        table th:first-child {
            text-align: left;
        }

        table td {
            background-color: var(--row-grey-2);
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 400;
            font-size: 7.5pt;
            line-height: 8.5pt;
            padding: 0.62mm 1.4mm 0.62mm 1.5mm;
            text-align: right;
            overflow: hidden;
        }

        table td:first-child {
            text-align: left;
        }

        /* Superscript footnote markers must not inflate row heights */
        table td sup, table th sup {
            font-size: 5pt;
            line-height: 0;
            vertical-align: super;
        }

        /* Reference sets a thin space between the row label and its
           superscript marker ("Fund ³", not "Fund³"). */
        .performance-table table td:first-child sup {
            margin-left: 0.4mm;
        }

        /* Per-row max limits rendered smaller than the asset-class name */
        td .row-limit,
        th .th-limit {
            font-size: 6pt;
        }
        th .th-limit {
            font-size: 7pt;
        }

        /* =====================================================
           BOND PAGE 1 — maturity chart, statistics, credit exposure
           (826 reference: Foord Bond Fund Class A at 2026-03-31)
           ===================================================== */

        /* Maturity breakdown — full-width grouped Fund/Benchmark bar chart.
           The wrapper height includes the two-line bucket labels and the
           centred legend beneath the plot. */
        .maturity-section {
            /* Lands PORTFOLIO STATISTICS on the reference y≈811px/150dpi. */
            margin-bottom: 9.5mm;
        }
        .maturity-chart-wrapper {
            height: 52mm;
            position: relative;
        }
        .maturity-chart-wrapper > div {
            width: 100% !important;
            height: 100% !important;
        }

        /* Portfolio statistics — wide label column, three numeric columns
           (FUND / BENCHMARK / RELATIVE TO ALBI), values right-aligned. */
        .stats-section {
            margin-bottom: 5mm;
        }
        .stats-table table th:first-child,
        .stats-table table td:first-child {
            width: 46%;
        }
        /* Reference column breaks: FUND 15%, BENCHMARK 17%, RELATIVE TO
           ALBI 22% (wide enough for a single-line header). */
        .stats-table table th:nth-child(2),
        .stats-table table td:nth-child(2) { width: 15%; }
        .stats-table table th:nth-child(3),
        .stats-table table td:nth-child(3) { width: 17%; }
        .stats-table table th,
        .stats-table table td {
            padding-right: 2mm;
        }
        .stats-table table td {
            padding-top: 0.8mm;
            padding-bottom: 0.8mm;
            background-color: var(--row-grey-3);
        }
        /* Reference row shading: Yield darkest, WATM mid, everything below
           (including the spacer) grey-3; the Total duration row is medium. */
        .stats-table table tbody tr:nth-child(1) td { background-color: var(--pfe-grey); }
        .stats-table table tbody tr:nth-child(2) td { background-color: var(--row-grey-2); }
        .stats-table table tbody tr:nth-child(4) td { font-weight: 500; }
        .stats-table table td:first-child sup {
            margin-left: 0.4mm;
        }
        .stats-table table tr.stats-spacer-row td {
            padding: 0;
            height: 2.6mm;
            line-height: 2.6mm;
            font-size: 0;
        }

        /* Credit exposure tables + portfolio-vs-benchmark chart row */
        .credit-charts-row {
            display: flex;
            gap: 6mm;
            /* Lands the performance table on the reference y≈1441px/150dpi. */
            margin: 0 0 10mm 0;
        }
        .credit-block {
            width: 47%;
            min-width: 0;
        }
        .portfolio-chart-block {
            flex: 1;
            min-width: 0;
        }
        .credit-tables {
            display: flex;
            gap: 1.6mm;
        }
        .credit-tables .table-container {
            flex: 1;
            margin-bottom: 0;
        }
        .credit-tables table td {
            padding-top: 0.62mm;
            padding-bottom: 0.62mm;
        }
        /* Padding rows (no content) keep the full row pitch so both TOTAL
           rows sit level (border-box: height includes the cell padding). */
        .credit-tables table td:empty {
            height: 4.25mm;
        }
        /* % value column — narrow, right-aligned like the reference. */
        .credit-tables table th:last-child,
        .credit-tables table td:last-child {
            width: 26%;
        }
        /* Reference sets a thin space before the header marker ("RATING ³"). */
        .credit-tables table th sup {
            margin-left: 0.4mm;
        }

        /* Performance Table — columns: name 20.1%, cash 11.55%, since 12%, 8 x 8.05% */
        .performance-table table th {
            background-color: var(--dark-navy);
            color: var(--white);
            font-weight: 500;
            font-size: 7pt;
            line-height: 8.7pt;
            text-align: right;
            padding: 0.35mm 0.5mm;
        }
        .performance-table table th:first-child {
            text-align: left;
            width: 20.1%;
            padding-left: 1.5mm;
        }
        .performance-table table th:nth-child(2) { width: 11.55%; }
        .performance-table table th:nth-child(3) { width: 12%; }
        .performance-table table td {
            color: #000;
            font-size: 7.5pt;
            line-height: 8pt;
            padding: 0.45mm 0.5mm;
        }
        .performance-table table td:first-child {
            padding-left: 1.5mm;
        }
        /* Row colour fade: Fund pink, Benchmark grey-1, spacer grey-2, highest/lowest grey-3 */
        .performance-table table tbody tr td { background-color: var(--row-grey-3); }
        .performance-table table tbody tr:nth-child(1) td { background-color: var(--naartjie-20); }
        .performance-table table tbody tr:nth-child(2) td { background-color: var(--row-grey-1); }
        /* Spacer row between Benchmark and Fund highest — grey like the reference */
        .performance-table table tr.perf-spacer-row td {
            background-color: var(--row-grey-2) !important;
            padding: 0;
            height: 3.58mm;
            line-height: 3.58mm;
            font-size: 0;
        }

        /* Highlighted Foord fund rows — pink background, text colour same as table */
        table tbody tr.highlight-row td {
            background-color: var(--naartjie-20);
            color: #000;
        }

        table tbody tr.highlight-row td:first-child {
            color: #000;
            font-weight: 400;
        }

        /* Page-1 pointer to the page-2 footnotes ("*Please refer footnotes
           overleaf.") */
        .overleaf-note {
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 400;
            font-size: 7pt;
            color: #000;
            margin-top: 3mm;
        }

        /* =====================================================
           BOND PAGE 2 — monthly performance grid + footnotes
           ===================================================== */
        /* MONTHLY PERFORMANCE % — year label column + JAN..DEC + YTD. */
        .monthly-table table th {
            font-size: 6.5pt;
            padding: 0.6mm 0.7mm;
        }
        .monthly-table table th:first-child {
            width: 7%;
        }
        .monthly-table table td {
            font-size: 7pt;
            padding: 0.7mm 0.7mm;
        }
        .monthly-section {
            margin-bottom: 6mm;
        }

        /* The numbered footnote block sits between the TER paragraph and
           the footer. */
        .page2-footnotes {
            margin-top: 54mm;
        }
        .page2-footnotes .footnotes {
            font-size: 6.5pt;
            line-height: 8pt;
            color: #000;
            padding-left: 0;
        }

        /* TIC table — reference (App 2): label 50.2% (value column starts at
           x=134mm), two equal value columns with RIGHT-ALIGNED headers and
           values (right edge inset ~2mm), 7pt text. First (TER) + last data
           row (Transaction costs) white; middle sub-item rows grey. Total row
           (.total-row) keeps red styling, Avenir Next Medium. */
        .tic-table table th:first-child,
        .tic-table table td:first-child {
            width: 50.2%;
            padding-left: 1.6mm;
        }
        .tic-table table th,
        .tic-table table td {
            padding-right: 2mm;
        }
        /* Flexible reference: 12/36-month headers and numeric values are
           CENTRED in their columns (balanced right-aligns them). */
        .tic-table table th:not(:first-child),
        .tic-table table td:not(:first-child) {
            text-align: center;
            padding-left: 1mm;
            padding-right: 1mm;
        }
        .tic-table table th {
            font-size: 6pt;
        }
        .tic-table table td {
            /* Reference labels ~7% larger than 7pt ("Total expense ratio
               (TER)" measures 174px at 150dpi). */
            font-size: 7.5pt;
            padding-top: 0.92mm;
            padding-bottom: 0.92mm;
        }
        .tic-table table tbody tr td {
            background-color: var(--row-grey-2);
        }
        .tic-table table tbody tr:nth-child(1) td,
        .tic-table table tbody tr:nth-child(6) td {
            background-color: var(--white);
        }
        .tic-table table tr.total-row td {
            font-size: 7.5pt;
            font-weight: 500;
            padding-top: 0.95mm;
            padding-bottom: 0.95mm;
        }

        /* Performance fee examples — label 48.2%, four 12.95% columns,
           8pt text, values right-aligned. Row 1 pink (not bold), row 2
           darker grey, remaining rows grey. Total row red and taller. */
        .pfe-table table th:first-child,
        .pfe-table table td:first-child {
            width: 48.2%;
            padding-left: 1.6mm;
        }
        .pfe-table table th,
        .pfe-table table td {
            padding-right: 2.3mm;
        }
        .pfe-table table td {
            font-size: 8pt;
            line-height: 9.4pt;
            padding-top: 0.42mm;
            padding-bottom: 0.42mm;
        }
        .pfe-table table tbody tr td {
            background-color: var(--row-grey-2);
            color: #000;
            font-weight: 400;
        }
        .pfe-table table tbody tr:nth-child(1) td {
            background-color: var(--naartjie-20);
            color: #000;
            font-weight: 400;
        }
        .pfe-table table tbody tr:nth-child(2) td {
            background-color: var(--pfe-grey);
        }
        .pfe-table table tr.total-row td {
            font-size: 8pt;
            font-weight: 400;
            padding-top: 1.18mm;
            padding-bottom: 1.18mm;
        }

        /* "* Minimum fees apply" is black in the reference (p1 footnotes are navy) */
        .pfe-section .footnotes {
            color: #000;
        }

        /* Performance-fees narrative — 7.5pt navy, continuous line rhythm */
        .performance-fees-section {
            margin: 6.3mm 0 0 0;
        }

        .tic-section {
            margin-top: 6mm;
        }
        .pfe-section {
            margin-top: 7.5mm;
        }
        .performance-fees-text {
            font-size: 7.5pt;
            line-height: 9.24pt;
            color: var(--dark-navy);
            margin: 0;
        }

        /* Total row */
        table tbody tr.total-row td,
        table tfoot td {
            background-color: var(--naartjie);
            font-weight: 500;
            color: var(--white);
        }

        /* Change indicators — arrow coloured only; number inherits table colour.
           Reference arrows are ~5pt Wingdings triangles, smaller than the digits. */
        td.change-cell { color: #000; }
        td.change-cell .change-arrow-up,
        td.change-cell .change-arrow-down {
            font-size: 5.1pt;
            /* Reference gap between triangle and value is ~8-9px at 150dpi;
               the bare word space only gave ~4px. */
            margin-right: 0.8mm;
        }
        td.change-cell .change-arrow-up { color: #000; }
        td.change-cell .change-arrow-down { color: #7A9CB4; }

        /* =====================================================
           CHARTS SECTION
           ===================================================== */
        .charts-row {
            display: flex;
            gap: 6mm;
            /* Reduced from 3mm when the chart wrappers grew to 46mm: keeps the
               performance table anchored at the reference y position. */
            margin: 0.5mm 0 0 0;
        }

        .chart-container {
            flex: 1;
            min-width: 0;
        }

        .chart-title {
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 500;
            font-size: 7.5pt;
            line-height: 9pt;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            color: var(--dark-navy);
            margin: 0 0 0.8mm 0;
        }

        .chart-wrapper {
            /* Measured from the flexible reference: y-axis line 195px at
               150dpi (33mm plot) → 46mm wrapper including x labels + legend.
               (39mm rendered the plot area ~22% too short.) */
            height: 46mm;
            position: relative;
        }

        .chart-wrapper > div {
            width: 100% !important;
            height: 100% !important;
        }

        /* Legend samples: the reference draws thin (~1px printed) rules; the
           default legend line inherits the 1.75px series stroke. */
        .chart-wrapper .highcharts-legend-item .highcharts-graph {
            stroke-width: 1px;
        }

        /* Rotated y-axis caption for the performance chart — rendered in CSS so
           Highcharts doesn't reserve a full title column (the reference tucks
           it right beside the axis). */
        .chart-ytitle {
            position: absolute;
            left: -9mm;
            /* Keep the rotated label vertically centred on the 46mm-high
               chart (-8mm at 39mm, -12mm at 47mm). */
            top: -11.5mm;
            width: 22mm;
            text-align: center;
            transform: rotate(-90deg);
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-size: 6pt;
            color: #000;
            z-index: 2;
        }
        .chart-ytitle sup {
            font-size: 3.9pt;
            line-height: 0;
            vertical-align: super;
        }

        .chart-explanation {
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 400;
            font-size: 7.5pt;
            line-height: 9.25pt;
            letter-spacing: 0.01em;
            color: #000;
            margin: 1.5mm 0 2.4mm 0;
        }

        /* =====================================================
           FOOTNOTES — 6pt Lato, dark navy (per reference)
           ===================================================== */
        .footnotes {
            font-family: 'Lato', 'Avenir Next', sans-serif;
            font-weight: 400;
            font-size: 6pt;
            line-height: 7.2pt;
            letter-spacing: 0.01em;
            color: var(--dark-navy);
            margin-top: 1.2mm;
            padding-left: 1.2mm;
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
            width: 60mm;
            min-width: 60mm;
            max-width: 60mm;
            background-color: transparent;
            padding: 0;
            overflow: hidden;
        }

        /* Navy header box 45.7 x 11mm at (9.15mm, 10mm) — mirrors the p1 date badge */
        .info-sidebar-header {
            background-color: var(--dark-navy);
            color: var(--white);
            height: 11mm;
            box-sizing: border-box;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 2mm;
            /* Reference box spans x 11.0→56.5mm (65→334px at 150dpi):
               1.7mm right of the p1 date badge's grey-band position. */
            margin: 10mm 3.5mm 0 10.85mm;
            text-align: center;
        }

        .info-sidebar-header h2 {
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 500;
            font-size: 8pt;
            line-height: 10.9pt;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            margin: 0;
        }

        .info-sidebar-content {
            /* Right inset 6.2mm: reference disclaimer text wraps at x≈318px/
               150dpi (53.8mm); 4mm let lines run ~1.7mm too wide. */
            padding: 6.3mm 6.2mm 4mm 9mm;
        }

        /* Reference: 6.5pt Lato Light, dark navy */
        .info-sidebar-content p {
            font-family: 'Lato', 'Avenir Next', sans-serif;
            font-weight: 300;
            font-size: 6.5pt;
            line-height: 7.33pt;
            letter-spacing: 0.01em;
            color: var(--dark-navy);
            margin: 0 0 1.4mm 0;
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
            /* FEE RATES heading baseline lands at y≈29.7mm like the reference */
            padding: 27.3mm 6mm 4mm 4mm;
            overflow: hidden;
        }

        .fee-rates-table {
            margin-bottom: 0;
        }

        /* Label column width MUST equal the TIC table's first column (50.2%)
           so the two tables' column breaks align vertically (per Paul's
           red-line annotation on the SKM scan); 7pt labels, 7pt medium values. */
        .fee-rates-table td {
            padding: 0.3mm 1mm 0.3mm 1.6mm;
            /* Reference sets this table ~14% larger than the old 7pt
               ("Initial, exit and switching fees" measures 217px at 150dpi);
               row pitch unchanged (line-height still 9.4pt). */
            font-size: 8pt;
            line-height: 9.4pt;
            background-color: var(--row-grey-2);
            color: var(--dark-navy);
            text-align: left;
        }

        .fee-rates-table td:first-child {
            /* Reference value column starts at x≈816px/150dpi (138.2mm):
               ~2.5mm right of the TIC table's 50.2% break. */
            width: 51.9%;
        }

        .fee-rates-table td:last-child:not([colspan]) {
            text-align: left;
            font-weight: 500;
            padding-left: 1.6mm;
        }

        /* "Foord global funds:" — white background, then two pink rows (black text).
           Reference sets these three rows in Avenir Next Medium. */
        .fee-rates-table tr.global-funds-header td {
            background-color: var(--white) !important;
            color: var(--dark-navy);
            font-weight: 500;
            text-align: left;
        }

        .fee-rates-table tr.sub-item td {
            background-color: var(--naartjie-20) !important;
            color: #000;
            font-weight: 500;
        }

        /* Conservative reference: the sub-item fund names sit flush with the
           other row labels ("— Foord International"), no extra indent. */

        .fee-description {
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 400;
            font-size: 7.5pt;
            line-height: 9.24pt;
            color: var(--dark-navy);
            margin: 2.4mm 0 0 0;
        }

        /* TER paragraph is black in the reference (fee-rates/perf-fees are navy) */
        .tic-section .fee-description {
            color: #000;
        }

        /* =====================================================
           FOOTER
           ===================================================== */
        /* Footer — short naartjie rule (like the reference "______"), then
           Merriweather body and Avenir Next Medium contact lines, all naartjie. */
        .footer {
            margin-top: 8mm;
            padding-top: 5.5mm;
            border-top: none;
            position: relative;
        }
        .footer::after {
            content: "";
            position: absolute;
            top: 0;
            left: 0.3mm;
            /* Reference rule: 14.1mm long, ~1px (150dpi) thick. */
            width: 14.1mm;
            height: 0;
            border-top: 0.18mm solid var(--naartjie);
        }

        .footer-text {
            font-family: 'Merriweather', Georgia, serif;
            font-weight: 400;
            font-size: 8pt;
            line-height: 10.1pt;
            letter-spacing: 0.01em;
            color: var(--naartjie);
            margin: 0 0 3.5mm 0;
        }

        .footer-contact {
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 500;
            font-size: 8pt;
            line-height: 10.9pt;
            letter-spacing: 0.01em;
            color: var(--naartjie);
            position: relative;
            margin-top: 3.6mm;
        }

        .footer-contact p {
            margin: 0;
        }

        /* Red Foord acorn leaf next to contact info — 11mm wide per reference */
        .footer-leaf {
            position: absolute;
            right: 4mm;
            top: 0;
            width: 11mm;
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
        // Table headers like "SA (100)" — the bracketed limit renders slightly smaller.
        $renderTh = function (string $header): string {
            return preg_replace(
                '/\s*\(([^)]+)\)\s*$/',
                ' <span class="th-limit">($1)</span>',
                e($header)
            );
        };
        // Asset-class rows like "Equities (75)" — per-row max limit in 6pt.
        // Accepts either an explicit 'limit' key or a limit embedded in the name.
        $renderAssetName = function (array $row): string {
            $name = (string) ($row['name'] ?? '');
            if (isset($row['limit']) && $row['limit'] !== '' && ! preg_match('/\(/', $name)) {
                return e($name).' <span class="row-limit">('.e((string) $row['limit']).')</span>';
            }
            return preg_replace(
                '/\s*\(([^)]+)\)\s*$/',
                ' <span class="row-limit">($1)</span>',
                e($name)
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
                if (preg_match('/^(.+?)\s*[-—–]\s*(CLASS\s+[A-Z][0-9]*)$/iu', $fundName, $matches)) {
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
                                    $filled = $value['filled'] ?? 5;
                                    $total = $value['total'] ?? 10;
                                @endphp
                                {{-- Heading + dots share a single line so the dots sit
                                     immediately to the right of "EQUITY INDICATOR". --}}
                                <h3 class="sidebar-heading equity-heading">
                                    {{ $labels[$key] }}
                                    <span class="equity-indicator">
                                        @for ($i = 0; $i < $total; $i++)
                                            <svg class="equity-dot {{ $i < $filled ? 'filled' : 'empty' }}" viewBox="0 0 10 10" xmlns="http://www.w3.org/2000/svg"><circle cx="5" cy="5" r="5"/></svg>
                                        @endfor
                                    </span>
                                </h3>
                                @if(isset($value['description']))
                                    <p class="sidebar-text">{!! $value['description'] !!}</p>
                                @endif
                            @else
                                <h3 class="sidebar-heading">{{ $labels[$key] ?? strtoupper(implode(' ', preg_split('/(?=[A-Z])/', $key, -1, PREG_SPLIT_NO_EMPTY))) }}</h3>
                                @php
                                    // Reference formats the unit price to 2dp ("5080.44 cents");
                                    // the feed sometimes supplies 1dp ("5114.8 cents").
                                    if ($key === 'unitPrice') {
                                        $formatCents = function ($v) {
                                            return is_string($v)
                                                ? preg_replace_callback(
                                                    '/(\d+(?:\.\d+)?)(?=\s*cents\b)/i',
                                                    fn ($m) => number_format((float) $m[1], 2, '.', ''),
                                                    $v,
                                                    1
                                                )
                                                : $v;
                                        };
                                        if (is_array($value) && isset($value['description'])) {
                                            $value['description'] = $formatCents($value['description']);
                                        } else {
                                            $value = $formatCents($value);
                                        }
                                    }
                                @endphp
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
                <!-- Maturity Breakdown (grouped Fund/Benchmark bar chart) -->
                @if(isset($fund->data['mainContent']['charts']['maturityData']))
                    @php $maturity = $fund->data['mainContent']['charts']['maturityData']; @endphp
                    <div class="maturity-section">
                        <h3 class="section-heading">{{ $maturity['title'] ?? 'MATURITY BREAKDOWN' }}</h3>
                        @if(!empty($maturity['subtitle']))
                            <p class="section-subheading">{{ $maturity['subtitle'] }}</p>
                        @endif
                        <div class="maturity-chart-wrapper">
                            <div id="maturityChart"></div>
                        </div>
                    </div>
                @endif

                <!-- Portfolio Statistics -->
                @if(isset($fund->data['mainContent']['assetAllocation']['portfolioStatistics']))
                    @php $stats = $fund->data['mainContent']['assetAllocation']['portfolioStatistics']; @endphp
                    <div class="stats-section">
                        <h3 class="section-heading">{{ $stats['title'] ?? 'PORTFOLIO STATISTICS' }}</h3>
                        <div class="table-container stats-table">
                            <table>
                                <thead>
                                    <tr>
                                        @foreach ($stats['headers'] ?? ['', 'FUND', 'BENCHMARK', 'RELATIVE TO ALBI'] as $header)
                                            <th>{{ $header }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($stats['rows'] ?? [] as $row)
                                        @if($row['spacer'] ?? false)
                                            <tr class="stats-spacer-row"><td colspan="4">&nbsp;</td></tr>
                                        @else
                                            <tr>
                                                <td>{{ $row['name'] }}@if(!empty($row['sup']))<sup>{{ $row['sup'] }}</sup>@endif</td>
                                                <td>{{ $row['fund'] ?? '' }}</td>
                                                <td>{{ $row['benchmark'] ?? '' }}</td>
                                                <td>{{ $row['relative'] ?? '' }}</td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                <!-- Credit Exposure + Portfolio vs Benchmark -->
                @php $credit = $fund->data['mainContent']['assetAllocation']['creditExposure'] ?? null; @endphp
                @if($credit || isset($fund->data['mainContent']['charts']['portfolioData']))
                    <div class="credit-charts-row">
                        @if($credit)
                            <div class="credit-block">
                                <h3 class="section-heading">{!! $renderHeading($credit['title'] ?? 'CREDIT EXPOSURE BREAKDOWN %') !!}</h3>
                                @php
                                    $creditTotal = fn (array $rows) => (string) round(array_sum(array_map(
                                        fn ($r) => is_numeric($r['value'] ?? null) ? (float) $r['value'] : 0,
                                        $rows
                                    )));
                                    // The shorter table pads with empty (shaded) rows so both
                                    // TOTAL rows sit level, per the reference.
                                    $creditRatings = $credit['ratings'] ?? [];
                                    $creditSectors = $credit['sectors'] ?? [];
                                    $creditRowCount = max(count($creditRatings), count($creditSectors));
                                @endphp
                                <div class="credit-tables">
                                    @foreach ([['RATING<sup>3</sup>', $creditRatings], ['SECTOR', $creditSectors]] as [$creditHeading, $creditRows])
                                        <div class="table-container">
                                            <table>
                                                <thead>
                                                    <tr><th>{!! $creditHeading !!}</th><th>%</th></tr>
                                                </thead>
                                                <tbody>
                                                    @for ($i = 0; $i < $creditRowCount; $i++)
                                                        <tr>
                                                            <td>{{ $creditRows[$i]['name'] ?? '' }}</td>
                                                            <td>{{ $creditRows[$i]['value'] ?? '' }}</td>
                                                        </tr>
                                                    @endfor
                                                    <tr class="total-row"><td>TOTAL</td><td>{{ $creditTotal($creditRows) }}</td></tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        @if(isset($fund->data['mainContent']['charts']['portfolioData']))
                            <div class="portfolio-chart-block">
                                <h3 class="section-heading">{{ $fund->data['mainContent']['charts']['rightTitle'] ?? 'PORTFOLIO VS BENCHMARK' }}</h3>
                                <div class="chart-wrapper">
                                    <div class="chart-ytitle">Cash Value<sup>4</sup> (R&rsquo;000)</div>
                                    <div id="portfolioChart"></div>
                                </div>
                            </div>
                        @endif
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
                            // Bond reference spells the year columns out in full.
                            '20 YEARS' => '20yrs', '15 YEARS' => '15yrs', '10 YEARS' => '10yrs',
                            '7 YEARS' => '7yrs', '5 YEARS' => '5yrs', '3 YEARS' => '3yrs',
                            '2 YEARS' => '2yrs', '1 YEAR' => '1yr',
                        ];
                        $perfColKeys = [];
                        foreach (array_slice($perfHeaders, 1) as $h) {
                            $clean = preg_replace('/[¹²³⁴⁵⁶⁷⁸⁹⁰]/u', '', strip_tags(str_replace('<br>', ' ', $h)));
                            $clean = strtoupper(trim(preg_replace('/\s+/', ' ', $clean)));
                            $perfColKeys[] = $perfKeyMap[$clean] ?? null;
                        }
                    @endphp
                    {{-- Reference sets this heading's bracketed text at FULL heading size
                         (only ASSET ALLOCATION's "(MAX LIMITS IN BRACKETS)" is smaller). --}}
                    <h3 class="section-heading">{!! $normaliseSupers(e($fund->data['mainContent']['performanceTable']['title'] ?? 'PORTFOLIO PERFORMANCE % (PERIODS GREATER THAN ONE YEAR ARE ANNUALISED⁵)')) !!}</h3>

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
                                {{-- Bond fund display rules (per the signed-off 826 reference
                                     PDF): the import writes raw row names (Fund, Benchmark,
                                     Fund highest/lowest). Display order puts a blank spacer before
                                     the highest/lowest rows. Footnotes: Fund⁶, highest/lowest⁶,⁷;
                                     the Benchmark row carries no marker. --}}
                                @php
                                    $perfMainRows = [];
                                    $perfHighLowRows = [];
                                    foreach ($perfRows as $row) {
                                        if (preg_match('/^fund\s+(highest|lowest)/i', trim(strip_tags((string)$row['name'])))) {
                                            $perfHighLowRows[] = $row;
                                        } else {
                                            $perfMainRows[] = $row;
                                        }
                                    }
                                    $decorateBondName = function (string $name) {
                                        $plain = trim(strip_tags($name));
                                        if (str_contains($name, '<sup') || preg_match('/[¹²³⁴⁵⁶⁷⁸⁹]/u', $name)) {
                                            return $name; // already decorated (hand-edited)
                                        }
                                        if (preg_match('/^fund\s+(highest|lowest)/i', $plain)) {
                                            return $name.'<sup>6,7</sup>';
                                        }
                                        if (stripos($plain, 'fund') === 0) {
                                            return $name.'<sup>6</sup>';
                                        }
                                        return $name;
                                    };
                                    $renderPerfRow = function ($row, $highlight) use ($perfColKeys, $fmt, $normaliseSupers, $decorateBondName) {
                                        $cells = '<td>'.$normaliseSupers($decorateBondName((string)$row['name'])).'</td>';
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
                                    <tr class="perf-spacer-row">
                                        <td colspan="{{ count($perfColKeys) + 1 }}">&nbsp;</td>
                                    </tr>
                                    @foreach ($perfHighLowRows as $row)
                                        {!! $renderPerfRow($row, false) !!}
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>

                    {{-- The bond fund's numbered footnotes print on page 2; page 1
                         carries only the pointer line (826 reference). --}}
                    <p class="overleaf-note">{{ $fund->data['mainContent']['performanceTable']['overleafNote'] ?? '*Please refer footnotes overleaf.' }}</p>
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
                        <p>{{ $fund->data['importantInfo']['publishedDate'] }}</p>
                    @endif
                </div>
            </div>

            <!-- Fees Content -->
            <div class="fees-content">
                <!-- Monthly Performance -->
                @if(isset($fund->data['mainContent']['charts']['monthlyPerformance']['years']))
                    @php $monthly = $fund->data['mainContent']['charts']['monthlyPerformance']; @endphp
                    <div class="monthly-section">
                        <h3 class="section-heading">{{ $monthly['title'] ?? 'MONTHLY PERFORMANCE %' }}</h3>
                        <div class="table-container monthly-table">
                            <table>
                                <thead>
                                    <tr>
                                        <th></th>
                                        @foreach (['JAN','FEB','MAR','APR','MAY','JUN','JUL','AUG','SEP','OCT','NOV','DEC','YTD'] as $m)
                                            <th>{{ $m }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($monthly['years'] as $yearRow)
                                        <tr>
                                            <td>{{ $yearRow['year'] }}</td>
                                            @foreach (['jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec'] as $m)
                                                <td>{{ $yearRow['months'][$m] ?? '' }}</td>
                                            @endforeach
                                            <td>{{ $yearRow['ytd'] ?? '' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

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
                                            <td>— {{ $gName }}</td>
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

                <!-- Numbered footnotes (826 reference prints them here, above the footer) -->
                @if(isset($fund->data['mainContent']['performanceTable']['footnotes']))
                    <div class="page2-footnotes">
                        <div class="footnotes">
                            @foreach ($fund->data['mainContent']['performanceTable']['footnotes'] as $footnote)
                                <p>{!! $normaliseSupers($footnote) !!}</p>
                            @endforeach
                        </div>
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
            const maturityData = @json($fund->data['mainContent']['charts']['maturityData']['categories'] ?? []);
            const portfolioData = @json($fund->data['mainContent']['charts']['portfolioData'] ?? []);
            const portfolioLabels = @json($fund->data['mainContent']['charts']['portfolioLabels'] ?? ['Fund', 'Benchmark']);

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

            const formatXTickPortfolio = (label) => {
                if (!label) return '';
                const m = label.match(/^(\d{4})-(\d{2})$/);
                if (!m) return label;
                const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                return months[parseInt(m[2], 10) - 1] + ' ' + m[1].slice(-2);
            };

            // The portfolio chart is a cash-value spline chart in the exact style of the
            // signed-off Balanced portfolio chart (pdf.blade.php). The 826 reference
            // ticks every SIX months from the first data point (Oct 22, Apr 23, …
            // Oct 25 — the fund's short history quarters the balanced fund's 2-year
            // pitch), LINEAR y-axis from the 100 baseline.
            const renderCashChart = (containerId, data, seriesDefs, legendItemDistance = 40) => {
                if (!data.length) return;
                const formatCashLabel = (v) => 'R ' + Math.round(v).toLocaleString('en-US');

                const maxVal = Math.max(
                    ...data.map(d => Math.max(...seriesDefs.map(s => d[s.key] || 0)))
                );
                const yMax = Math.ceil(maxVal * 1.05 / 100) * 100;

                const dates = data.map(d => d.date);
                const tickPositions = (function () {
                    const monthsSinceEpoch = (d) => parseInt(d.slice(0, 4), 10) * 12 + parseInt(d.slice(5, 7), 10);
                    const anchor = monthsSinceEpoch(dates[0]);
                    const positions = [];
                    dates.forEach((d, i) => {
                        if ((monthsSinceEpoch(d) - anchor) % 6 === 0) positions.push(i);
                    });
                    return positions;
                })();

                Highcharts.chart(containerId, {
                    chart: {
                        type: 'spline', backgroundColor: 'transparent', spacing: [4, 46, 4, 0], animation: false,
                    },
                    title: { text: null },
                    xAxis: {
                        categories: dates,
                        tickWidth: 1,
                        tickLength: 3,
                        tickColor: '#000',
                        lineColor: '#000',
                        lineWidth: 1,
                        labels: {
                            // Eight 6-monthly labels share the half-width plot —
                            // slightly smaller than the balanced chart's 8px, and
                            // never ellipsized (the right spacing reserves room).
                            style: { fontSize: '7.5px', color: '#000', textOverflow: 'none' },
                            formatter: function () { return formatXTickPortfolio(this.value); },
                            rotation: 0,
                            autoRotation: false,
                            overflow: 'allow',
                        },
                        tickPositions: tickPositions,
                    },
                    yAxis: {
                        title: { text: null },
                        // LINEAR axis from 0 — measured from the published reference
                        // chart (see note above). Only the 100 baseline is labelled.
                        gridLineWidth: 0,
                        lineColor: '#000',
                        lineWidth: 1,
                        tickWidth: 1,
                        tickLength: 3,
                        tickColor: '#000',
                        // Axis crosses at the 100 baseline like the reference (the
                        // curve's first point sits ON the x-axis line).
                        min: 100,
                        max: yMax,
                        endOnTick: false,
                        startOnTick: false,
                        tickPositions: [100],
                        labels: {
                            distance: 2,
                            y: 8,
                            style: { fontSize: '8px', color: '#000' },
                            formatter: function () {
                                return this.value === 100 ? '100' : '';
                            },
                        },
                    },
                    legend: {
                        itemStyle: { fontSize: '8px', fontWeight: 'normal', color: colors.darkNavy },
                        // Reference: 26px rules at 150dpi (~17 CSS px) abutting the
                        // label (gap ~2px), entries spaced well apart.
                        symbolWidth: 17,
                        symbolHeight: 2,
                        symbolRadius: 0,
                        symbolPadding: 2,
                        itemDistance: legendItemDistance,
                        margin: 6,
                        padding: 0,
                    },
                    tooltip: { enabled: false },
                    plotOptions: {
                        // clip:false lets the 2008 dip draw BELOW the 100 baseline /
                        // x-axis line exactly like the reference (the axis stays at
                        // 100; the series is simply not clipped to the plot area).
                        spline: { marker: { enabled: false }, lineWidth: 1.75, clip: false },
                        series: { animation: false, clip: false },
                    },
                    series: seriesDefs.map(s => ({
                        name: s.name, data: data.map(d => d[s.key]), color: s.color,
                        dataLabels: [{
                            enabled: true, align: 'left', verticalAlign: 'middle', x: 6, y: 0,
                            style: { fontSize: '9px', fontWeight: '500', color: s.color, textOutline: 'none' },
                            formatter: function () { return this.point.index === this.series.data.length - 1 ? formatCashLabel(this.y) : null; },
                            crop: false, overflow: 'allow', allowOverlap: true,
                        }],
                    })),
                });
            };

            // Maturity breakdown (top chart): grouped Fund/Benchmark columns per
            // the signed-off 826 reference — y-axis ticked every 10% (0%–50% on
            // the reference), no gridlines, two-line category labels (bucket +
            // quarter-on-quarter change), centred square-marker legend below.
            if (maturityData.length > 0) {
                const allValues = maturityData.flatMap(d => [d.fund ?? 0, d.benchmark ?? 0]);
                const matMin = Math.min(0, Math.floor(Math.min(...allValues) / 10) * 10);
                const matMax = Math.ceil((Math.max(...allValues) + 5) / 10) * 10;
                const matTicks = [];
                for (let t = matMin; t <= matMax; t += 10) matTicks.push(t);

                Highcharts.chart('maturityChart', {
                    chart: { type: 'column', backgroundColor: 'transparent', spacing: [4, 4, 2, 0], animation: false },
                    title: { text: null },
                    xAxis: {
                        categories: maturityData.map(d => d.name),
                        lineWidth: 0,
                        tickWidth: 0,
                        labels: {
                            style: { fontSize: '8px', color: '#000', textAlign: 'center' },
                            formatter: function () {
                                const entry = maturityData[this.pos] || {};
                                return entry.change ? this.value + '<br>' + entry.change : this.value;
                            },
                            rotation: 0,
                            autoRotation: false,
                        },
                    },
                    yAxis: {
                        title: { text: null },
                        min: matTicks[0], max: matTicks[matTicks.length - 1],
                        tickPositions: matTicks,
                        gridLineWidth: 0,
                        lineWidth: 1, lineColor: '#000',
                        tickWidth: 1, tickLength: 3, tickColor: '#000',
                        minorTickInterval: 5, minorTickWidth: 1, minorTickLength: 3,
                        minorTickColor: '#000', minorGridLineWidth: 0,
                        plotLines: [{ value: 0, color: '#000', width: 1, zIndex: 4 }],
                        labels: {
                            style: { fontSize: '8px', color: '#000' },
                            formatter: function () { return this.value + '%'; },
                        },
                    },
                    legend: {
                        itemStyle: { fontSize: '8px', fontWeight: 'normal', color: '#000' },
                        symbolWidth: 8, symbolHeight: 8, symbolRadius: 0,
                        itemDistance: 24, margin: 8, padding: 0,
                        squareSymbol: true,
                    },
                    tooltip: { enabled: false },
                    plotOptions: {
                        // borderRadius 0: Highcharts 11 rounds column tops by
                        // default; the reference bars are square.
                        column: { pointPadding: 0.02, groupPadding: 0.18, borderWidth: 0, borderRadius: 0 },
                        series: { animation: false },
                    },
                    series: [
                        { name: 'Fund', data: maturityData.map(d => d.fund ?? null), color: colors.naartjie },
                        { name: 'Benchmark', data: maturityData.map(d => d.benchmark ?? null), color: colors.darkNavy },
                    ],
                });
            }

            // Portfolio Performance vs Benchmark
            renderCashChart('portfolioChart', portfolioData, [
                { key: 'fund', name: portfolioLabels[0] ?? 'Fund', color: colors.naartjie },
                { key: 'benchmark', name: portfolioLabels[1] ?? 'Benchmark', color: colors.darkNavy },
            ], 49);
        });
    </script>
    @endif
</body>
</html>
