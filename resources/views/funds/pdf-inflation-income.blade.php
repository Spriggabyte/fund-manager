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
            /* The 827 name is the longest in the range — the reference sets
               it at the balanced 23pt but tighter; 22.5pt/0 tracking keeps
               "… FUND — CLASS B2" on one line like the published sheet. */
            font-size: 22.5pt;
            letter-spacing: 0;
            white-space: nowrap;
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
            /* 5.1mm top lands the PORTFOLIO STRUCTURE header row at the
               825 reference y=65.4mm. */
            padding: 5.1mm 6mm 4mm 4mm;
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
           superscript marker ("Fund ⁶", not "Fund⁶"). */
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
           INFLATION LINKED INCOME PAGE 1 — full-width portfolio
           structure, statistics + maturity spread, credit exposure +
           cash chart, performance table, page-1 footnotes
           (827 reference: Foord Inflation Linked Income Fund
           Class B2 at 2026-06-30)
           ===================================================== */

        /* Portfolio structure — full content width, TOTAL | CHANGE columns.
           The published table lists ILB maturity buckets the feed does not
           carry, so the rows are stored values maintained by hand. */
        .structure-section {
            /* Lands the PORTFOLIO STATISTICS heading at the reference
               y=121.6mm. */
            margin: 0 0 6.2mm 0;
        }

        .structure-table table th:first-child,
        .structure-table table td:first-child {
            width: 51%;
        }
        .structure-table table th:nth-child(2),
        .structure-table table td:nth-child(2) {
            width: 24.6%;
        }
        .structure-table table th {
            white-space: nowrap;
        }
        .structure-table table td {
            /* 827 reference row pitch 4.04mm. */
            padding-top: 0.43mm;
            padding-bottom: 0.43mm;
        }

        /* Portfolio statistics (left) + maturity spread (right). */
        .stats-maturity-row {
            display: flex;
            gap: 6mm;
            /* Lands the CREDIT EXPOSURE heading at the reference y=164.5mm. */
            margin: 0 0 7.7mm 0;
        }
        .stats-block { width: 49%; min-width: 0; }

        /* Portfolio statistics — label/value pairs, no header row. The 827
           reference leaves ~1.5mm more air under the heading than the other
           tables (first row text lands at y=127.9mm). */
        .stats-table {
            margin-top: 2.5mm;
        }
        .stats-table table th:first-child,
        .stats-table table td:first-child {
            width: 72%;
        }
        .stats-table table th,
        .stats-table table td {
            padding-right: 2mm;
        }
        .stats-table table td {
            padding-top: 0.35mm;
            padding-bottom: 0.35mm;
            background-color: var(--row-grey-3);
        }
        /* 827 reference row shading: Real Yield darkest, Duration mid. */
        .stats-table table tbody tr:nth-child(1) td { background-color: var(--pfe-grey); }
        .stats-table table tbody tr:nth-child(2) td { background-color: var(--row-grey-2); }
        .stats-table table tr.stats-bold-row td { font-weight: 500; }
        .stats-table table td:first-child sup {
            margin-left: 0.4mm;
        }
        .stats-table table tr.stats-spacer-row td {
            padding: 0;
            height: 2.6mm;
            line-height: 2.6mm;
            font-size: 0;
        }

        /* The reference maturity block stops ~8.4mm short of the content
           column's right edge (values right-align at x≈195.6mm). */
        .maturity-spread-block { flex: 1; min-width: 0; padding-right: 8.4mm; }

        /* Credit exposure tables (left) + cash chart (right). */
        .credit-chart-row {
            display: flex;
            gap: 6mm;
            /* Lands the performance-table title at the reference y=218.8mm. */
            margin: 0 0 0 0;
        }
        .credit-block { width: 49%; min-width: 0; }

        /* Maturity spread — CSS bar list (labels left, naartjie bars,
           right-aligned value column at the block's right edge). */
        .maturity-spread-rows { margin-top: 2mm; }
        .maturity-spread-row {
            display: flex;
            align-items: center;
            /* 827 reference row pitch ~5.3mm (six buckets; the 3.45mm label
               line-height sets the row height, not the 2.6mm bar). */
            margin-bottom: 1.85mm;
        }
        .maturity-spread-row:last-child { margin-bottom: 0; }
        .maturity-spread-label {
            width: 17mm;
            min-width: 17mm;
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-size: 7.5pt;
            line-height: 3.45mm;
            color: #000;
        }
        .maturity-spread-track {
            flex: 1;
            min-width: 0;
        }
        .maturity-spread-bar {
            height: 2.6mm;
            background-color: var(--naartjie);
        }
        .maturity-spread-value {
            width: 8mm;
            min-width: 8mm;
            text-align: right;
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-size: 7.5pt;
            color: #000;
        }

        .credit-tables {
            display: flex;
            gap: 1.6mm;
            /* 827 reference: header row tops land at y=169.2mm. */
            margin-top: 1mm;
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

        /* Cash chart — the right half of the credit/chart row. */
        .portfolio-chart-block {
            flex: 1;
            min-width: 0;
        }

        /* Performance Table — columns: name 20.1%, cash 11.55%, since 12%, rest equal.
           827 reference: header top y=223.3mm, Fund row y=231.6mm, pitch 4.2mm. */
        .performance-table {
            /* padding, not margin — a margin collapses into the heading's. */
            padding-top: 0.8mm;
        }
        .performance-table table th {
            background-color: var(--dark-navy);
            color: var(--white);
            font-weight: 500;
            font-size: 7pt;
            line-height: 8.7pt;
            text-align: right;
            padding: 0.7mm 0.5mm;
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
            padding: 0.55mm 0.5mm;
        }
        .performance-table table td:first-child {
            padding-left: 1.5mm;
        }
        /* 827 row colours: Fund pink, Benchmark grey-4 (#f0f0f0 measured
           from the reference — lighter than the income template's grey-1) */
        .performance-table table tbody tr td { background-color: var(--row-grey-3); }
        .performance-table table tbody tr:nth-child(1) td { background-color: var(--naartjie-20); }
        .performance-table table tbody tr:nth-child(2) td { background-color: var(--row-grey-4); }
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

        /* Numbered footnotes print at the bottom of PAGE 1 on the 827
           reference (below the performance table), at body size — larger
           than the 6pt page-2 footnote style the other templates use. */
        .p1-footnotes {
            /* Lands the first footnote line at the reference y=249.1mm. */
            margin-top: 8.8mm;
        }
        .p1-footnotes .footnotes {
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-size: 7.5pt;
            line-height: 8.65pt;
            color: #000;
            padding-left: 0;
        }
        .p1-footnotes .footnotes p {
            margin: 0;
        }

        /* "*Estimated as the fund was incepted less than three years ago…"
           between the TIC table and the TER paragraph (827 reference). */
        .tic-footnote {
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 400;
            font-size: 7pt;
            line-height: 8.6pt;
            color: #000;
            /* Negative top margin offsets the table container's 2.6mm
               bottom margin (they collapse to 1.7mm — reference gap). */
            margin: -0.9mm 0 0 0;
        }

        /* TIC table — reference (App 2): label 50.2% (value column starts at
           x=134mm), two equal value columns with CENTRED headers and values,
           7pt text. First (TER) + last data row (Transaction costs) white;
           middle sub-item rows grey. Total row (.total-row) keeps red
           styling, Avenir Next Medium. */
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
               (TER)" measures 174px at 150dpi); 827 row pitch 4.3mm. */
            font-size: 7.5pt;
            padding-top: 0.62mm;
            padding-bottom: 0.62mm;
        }
        .tic-table table tbody tr td {
            background-color: var(--row-grey-2);
        }
        /* The 827 TIC has four data rows: TER + Transaction costs white,
           the two sub-item rows grey (like the income reference). */
        .tic-table table tbody tr:nth-child(1) td,
        .tic-table table tbody tr:nth-child(4) td {
            background-color: var(--white);
        }
        .tic-table table tr.total-row td {
            font-size: 7.5pt;
            font-weight: 500;
            padding-top: 0.95mm;
            padding-bottom: 0.95mm;
        }

        .tic-section {
            /* 827 reference: TOTAL INVESTMENT CHARGE % heading at y=49.0mm. */
            margin-top: 8.8mm;
        }
        .tic-section .tic-table {
            /* Reference header row top lands at y=53.7mm. */
            padding-top: 0.9mm;
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
            /* Measured from the 827 reference: plot 169.5→203.5mm (34mm)
               plus x labels + legend → 49.5mm wrapper (legend bottom lands
               at y≈216.9mm; the baseline is pinned by chart.marginBottom). */
            height: 49.5mm;
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
            /* Keep the rotated label vertically centred on the 50mm-high
               chart (-8mm at 39mm, -12mm at 47mm). */
            top: -13mm;
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

        .fee-description {
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 400;
            font-size: 7.5pt;
            line-height: 9.24pt;
            color: var(--dark-navy);
            /* 827 reference: the TER paragraph starts at y=95.4mm. */
            margin: 1.2mm 0 0 0;
        }

        /* TER paragraph is black in the reference (fee-rates text is navy) */
        .tic-section .fee-description {
            color: #000;
        }

        /* =====================================================
           FOOTER
           ===================================================== */
        /* Footer — short naartjie rule (like the reference "______"), then
           Merriweather body and Avenir Next Medium contact lines, all naartjie. */
        .footer {
            /* Lands "Please visit our website…" at the reference y=251.6mm
               (no numbered-footnote block on the 827 page 2, so the gap
               after the TER paragraph is wide). */
            margin-top: 128.6mm;
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
                                    // Reference formats the unit price to 2dp ("1052.89 cents");
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
                <!-- Portfolio Structure (full width — hand-maintained ILB bucket rows) -->
                @php
                    $structure = $fund->data['mainContent']['assetAllocation'] ?? null;
                    $stats = $fund->data['mainContent']['assetAllocation']['portfolioStatistics'] ?? null;
                @endphp
                @if(isset($structure['rows']))
                    <div class="structure-section">
                        <h3 class="section-heading">{{ $structure['title'] ?? 'PORTFOLIO STRUCTURE %' }}</h3>
                        @if(!empty($structure['subtitle']))
                            <p class="section-subheading">{{ $structure['subtitle'] }}</p>
                        @endif
                        <div class="table-container structure-table">
                            <table>
                                <thead>
                                    <tr>
                                        @foreach ($structure['headers'] ?? ['', 'TOTAL', 'CHANGE'] as $header)
                                            <th>{{ $header }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($structure['rows'] as $row)
                                        @php
                                            $dir = $row['changeDirection'] ?? '';
                                            $raw = trim((string) ($row['change'] ?? ''));
                                            $arrowClass = $dir === 'up' ? 'change-arrow-up' : ($dir === 'down' ? 'change-arrow-down' : '');
                                            if (preg_match('/^([▲▼])\s*(.*)$/u', $raw, $cm)) {
                                                [$arrowChar, $numPart] = [$cm[1], $cm[2]];
                                            } else {
                                                [$arrowChar, $numPart] = ['', $raw];
                                            }
                                            if ($arrowClass === '') {
                                                $arrowClass = $arrowChar === '▲' ? 'change-arrow-up' : 'change-arrow-down';
                                            }
                                        @endphp
                                        <tr>
                                            <td>{{ $row['name'] }}</td>
                                            <td>{{ $row['value'] ?? '' }}</td>
                                            <td class="change-cell">
                                                @if ($arrowChar)<span class="{{ $arrowClass }}">{{ $arrowChar }}</span>@endif {{ $numPart }}
                                            </td>
                                        </tr>
                                    @endforeach
                                    @if(isset($structure['total']))
                                        <tr class="total-row">
                                            <td>{{ $structure['total']['name'] ?? 'TOTAL' }}</td>
                                            <td>{{ $structure['total']['value'] ?? '' }}</td>
                                            <td></td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                <!-- Portfolio Statistics + Maturity Spread -->
                @php
                    $spread = $fund->data['mainContent']['charts']['maturitySpread'] ?? null;
                @endphp
                @if($stats || $spread)
                    <div class="stats-maturity-row">
                        @if($stats)
                            <div class="stats-block">
                                <h3 class="section-heading">{{ $stats['title'] ?? 'PORTFOLIO STATISTICS' }}</h3>
                                <div class="table-container stats-table">
                                    <table>
                                        <tbody>
                                            @foreach ($stats['rows'] ?? [] as $row)
                                                @if($row['spacer'] ?? false)
                                                    <tr class="stats-spacer-row"><td colspan="2">&nbsp;</td></tr>
                                                @else
                                                    <tr class="{{ ($row['bold'] ?? false) ? 'stats-bold-row' : '' }}">
                                                        <td>{{ $row['name'] }}@if(!empty($row['sup']))<sup>{{ $row['sup'] }}</sup>@endif</td>
                                                        <td>{{ $row['value'] ?? '' }}</td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                        @if($spread)
                            @php
                                $spreadMax = max(array_map(fn ($c) => (float) ($c['value'] ?? 0), $spread['categories'] ?? []) ?: [1]);
                            @endphp
                            <div class="maturity-spread-block">
                                <h3 class="section-heading">{{ $spread['title'] ?? 'MATURITY SPREAD %' }}</h3>
                                <div class="maturity-spread-rows">
                                    @foreach ($spread['categories'] ?? [] as $bucket)
                                        <div class="maturity-spread-row">
                                            <div class="maturity-spread-label">{{ $bucket['name'] }}</div>
                                            <div class="maturity-spread-track">
                                                <div class="maturity-spread-bar" style="width: {{ $spreadMax > 0 ? round(((float) ($bucket['value'] ?? 0)) / $spreadMax * 100, 1) : 0 }}%"></div>
                                            </div>
                                            <div class="maturity-spread-value">{{ $bucket['label'] ?? '' }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Credit Exposure + Portfolio vs Benchmark cash chart -->
                @php
                    $credit = $fund->data['mainContent']['assetAllocation']['creditExposure'] ?? null;
                    $hasPortfolioChart = isset($fund->data['mainContent']['charts']['portfolioData']);
                @endphp
                @if($credit || $hasPortfolioChart)
                    <div class="credit-chart-row">
                        @if($credit)
                            <div class="credit-block">
                                <h3 class="section-heading">{!! $renderHeading($credit['title'] ?? 'CREDIT EXPOSURE BREAKDOWN %') !!}</h3>
                                @php
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
                                                    {{-- The published sheet prints both totals as a nominal 100
                                                         (effective exposures; the rounding note covers the drift). --}}
                                                    <tr class="total-row"><td>TOTAL</td><td>100</td></tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        @if($hasPortfolioChart)
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
                                {{-- 827 display rules (per the published reference): only the
                                     Fund and Benchmark rows print — the import still stores the
                                     highest/lowest rows, but the published sheet omits them.
                                     Footnotes: Fund⁶; the Benchmark row carries no marker. --}}
                                @php
                                    $perfRows = $fund->data['mainContent']['performanceTable']['rows'] ?? [];
                                    $perfMainRows = array_values(array_filter($perfRows, fn ($row) =>
                                        ! preg_match('/^fund\s+(highest|lowest)/i', trim(strip_tags((string) $row['name'])))));
                                    $decorateInflationName = function (string $name) {
                                        $plain = trim(strip_tags($name));
                                        if (str_contains($name, '<sup') || preg_match('/[¹²³⁴⁵⁶⁷⁸⁹]/u', $name)) {
                                            return $name; // already decorated (hand-edited)
                                        }
                                        if (stripos($plain, 'fund') === 0) {
                                            return $name.'<sup>6</sup>';
                                        }
                                        return $name;
                                    };
                                    $renderPerfRow = function ($row, $highlight) use ($perfColKeys, $fmt, $normaliseSupers, $decorateInflationName) {
                                        $cells = '<td>'.$normaliseSupers($decorateInflationName((string)$row['name'])).'</td>';
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
                            </tbody>
                        </table>
                    </div>
                @endif

                <!-- Numbered footnotes (the 827 reference prints them at the
                     bottom of page 1, below the performance table) -->
                @if(isset($fund->data['mainContent']['performanceTable']['footnotes']))
                    <div class="p1-footnotes">
                        <div class="footnotes">
                            @foreach ($fund->data['mainContent']['performanceTable']['footnotes'] as $footnote)
                                <p>{!! $normaliseSupers($footnote) !!}</p>
                            @endforeach
                        </div>
                    </div>
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

                        @if(isset($fund->data['fees']['totalInvestmentCharge']['footnote']))
                            {{-- "*Estimated as the fund was incepted less than three
                                 years ago…" — pairs with the starred 36 MONTHS* header. --}}
                            <p class="tic-footnote">{{ $fund->data['fees']['totalInvestmentCharge']['footnote'] }}</p>
                        @endif

                        @if(isset($fund->data['fees']['totalInvestmentCharge']['description']))
                            <p class="fee-description">{{ $fund->data['fees']['totalInvestmentCharge']['description'] }}</p>
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

            // The portfolio chart is a cash-value chart in the signed-off balanced
            // style, but with STRAIGHT line segments (the published 827 chart shows
            // crisp corners, not spline smoothing), ticks every THREE months from
            // the first data point (Nov 24, Feb 25, … May 26) and a LINEAR y-axis
            // from the 100 baseline.
            const renderCashChart = (containerId, data, seriesDefs, legendItemDistance = 40) => {
                if (!data.length) return;
                const formatCashLabel = (v) => 'R ' + Math.round(v).toLocaleString('en-US');

                const maxVal = Math.max(
                    ...data.map(d => Math.max(...seriesDefs.map(s => d[s.key] || 0)))
                );
                // Max rounded up to the next 5 — the 827 reference places the
                // R 116 end label ~84% up the axis (yMax = 120).
                let yMax = Math.ceil(maxVal / 5) * 5;
                if (yMax - maxVal < 1) yMax += 5;

                const dates = data.map(d => d.date);
                const tickPositions = (function () {
                    const monthsSinceEpoch = (d) => parseInt(d.slice(0, 4), 10) * 12 + parseInt(d.slice(5, 7), 10);
                    const anchor = monthsSinceEpoch(dates[0]);
                    const positions = [];
                    dates.forEach((d, i) => {
                        if ((monthsSinceEpoch(d) - anchor) % 3 === 0) positions.push(i);
                    });
                    return positions;
                })();

                Highcharts.chart(containerId, {
                    chart: {
                        type: 'line', backgroundColor: 'transparent', spacing: [4, 46, 4, 0], animation: false,
                        // Pin the baseline: 54px from the wrapper bottom puts the
                        // x-axis at the reference y=203.5mm (independent of the
                        // label/legend boxes, which otherwise resize the plot).
                        marginBottom: 54,
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
                            style: { fontSize: '7.5px', color: '#000', textOverflow: 'none' },
                            formatter: function () { return formatXTickPortfolio(this.value); },
                            rotation: 0,
                            autoRotation: false,
                            overflow: 'allow',
                            // 827 reference: label tops sit 2.6mm below the axis.
                            y: 17,
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
                        // Calibrated so the x-axis baseline lands at the
                        // reference y=203.5mm with the 47.8mm wrapper.
                        margin: 7,
                        padding: 0,
                    },
                    tooltip: { enabled: false },
                    plotOptions: {
                        line: { marker: { enabled: false }, lineWidth: 1.75, clip: false },
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
