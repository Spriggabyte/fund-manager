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
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;500;700&family=Merriweather:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        /* =====================================================
           FOORD ASIA EX-JAPAN FUND (879) FACT SHEET — Classes R and R1
           Cloned from show-hassen-shariah.blade.php (878 Class R) —
           both sheets share the Luxembourg sub-fund chrome: sidebar
           layout, table styling, and page-2 performance-fee sections.

           879 differences, per its Class R reference:
             - the banner prints the class suffix ("— CLASS R") instead
               of leaving it to the sidebar
             - sidebar drops SHARE CLASS and SHARIAH SUPERVISORY BOARD,
               and uses a plural FUND MANAGERS label (Ishreth Hassen
               and Jing Cong Xue)
             - row 1 pairs PORTFOLIO STRUCTURE % (no variance column)
               beside the TOP 10 INVESTMENTS table, rather than either
               running full width
             - row 2 pairs a GEOGRAPHIC COUNTRY EXPOSURE pie beside the
               PORTFOLIO PERFORMANCE VS BENCHMARK chart, in place of
               878's grouped GEOGRAPHIC EQUITY EXPOSURE column chart
             - the performance table's periods are 3 YRS / 1 YR /
               6 MTHS / 3 MTHS, not 878's set
           ===================================================== */

        /* Foord Brand Colors — greys measured from the published reference PDF.
           Row greys fade in the order grey-1 (darkest) → grey-4 (lightest). */
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
            --light-grey: #cccccc;
            --dark-grey: #535353;
            --very-light-grey: #f4f4f4;
            --off-black: #313131;
            --white: #ffffff;
            --row-grey-0: #d4d4d4;
            --row-grey-1: #dddddd;
            --row-grey-2: #e6e6e6;
            --row-grey-3: #ebebeb;
            --row-grey-4: #f0f0f0;
            --light-blue: #7a9cb4;
            --mushroom: #e2cea4;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        @page {
            size: A4 portrait;
            margin: 0;
        }

        body {
            font-family: 'Avenir Next', 'Lato', -apple-system, sans-serif;
            font-size: 7.5pt;
            line-height: 1.2;
            color: #000;
            background: #e5e7eb;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            -webkit-font-smoothing: antialiased;
        }

        /* === Page container (A4) === */
        .page-container {
            width: 210mm;
            margin: 0 auto;
        }

        /* Grey sidebar band 4mm→60mm, full page height (reference: white
           4mm strip on the left edge) — identical to the signed-off pages. */
        .page {
            width: 210mm;
            height: 297mm;
            max-height: 297mm;
            overflow: hidden;
            position: relative;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            background: linear-gradient(to right, var(--white) 4mm, var(--dark-navy-15) 4mm, var(--dark-navy-15) 60mm, var(--white) 60mm);
        }

        .page + .page { margin-top: 16px; }

        @media print {
            .no-print { display: none !important; }
            .page-break { page-break-before: always; }
            body { background: white; }
            .page { box-shadow: none; margin: 0 !important; page-break-inside: avoid; }
        }

        body.pdf-mode { background: white; }
        body.pdf-mode .page-container { margin: 0; }
        body.pdf-mode .page { box-shadow: none; }
        body.pdf-mode .page + .page { margin-top: 0; }

        /* =====================================================
           HEADER — badge 45.9 x 10.9mm at (9.05mm, 10mm); logo
           13mm tall, top 9mm, right 5.5mm (signed-off geometry).
           879 reference: the date badge is dark navy too.
           ===================================================== */
        .header-row {
            position: relative;
            height: 26.5mm;
        }

        .date-badge {
            position: absolute;
            left: 9.05mm;
            top: 10mm;
            width: 45.9mm;
            height: 10.9mm;
            box-sizing: border-box;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--dark-navy);
            color: #ffffff;
            font-family: 'Lato', 'Avenir Next', sans-serif;
            font-weight: 500;
            font-size: 10.4pt;
            letter-spacing: 0.01em;
            word-spacing: 0.3mm;
            text-align: center;
        }

        .header-logo {
            position: absolute;
            top: 9mm;
            right: 5.5mm;
            height: 13mm;
        }

        .foord-logo { height: 100%; width: auto; }

        /* =====================================================
           TITLE BANNER — 34mm tall, naartjie (879 reference);
           text inset 7.75mm, same as the signed-off navy banner.
           The 879 banner prints the class suffix inline
           ("FOORD ASIA EX-JAPAN FUND — CLASS R") — unlike 877/878,
           which strip it into the sidebar SHARE CLASS row.
           ===================================================== */
        .fund-banner {
            background-color: var(--naartjie);
            color: var(--white);
            height: 34mm;
            box-sizing: border-box;
            padding: 3.1mm 6mm 0 7.75mm;
            margin: 0;
            width: 100%;
        }

        .fund-banner h1 {
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 500;
            font-size: 23pt;
            letter-spacing: 0.01em;
            text-transform: uppercase;
            margin: 0 0 4.25mm 0;
            line-height: 1.05;
        }

        /* 879 reference: the class suffix is visibly lighter and
           smaller than the fund name. Measured by cap-height pixel ratio
           at 200dpi (Task 10): title "FOORD ASIA EX-JAPAN FUND" = 46px
           at 23pt (2.0px/pt), reference suffix "CLASS R" = 30px tall,
           giving a target size of 30/2.0 = 15pt. */
        .fund-banner h1 .class-suffix {
            font-weight: 400;
            font-size: 15pt;
        }

        .fund-banner .description {
            font-family: 'Merriweather', Georgia, serif;
            font-weight: 400;
            font-size: 9.06pt;
            line-height: 11.34pt;
            letter-spacing: 0.01em;
            margin: 0;
            color: var(--white);
        }

        /* === Main body layout === */
        .main-body {
            display: flex;
            flex-direction: row;
            margin: 0;
            width: 100%;
            min-height: calc(297mm - 26.5mm - 34mm);
        }

        /* Sidebar — 60mm wide (grey band 4mm→60mm); text starts at x=8mm */
        .sidebar {
            width: 60mm;
            min-width: 60mm;
            max-width: 60mm;
            background-color: transparent;
            padding: 5.4mm 4mm 4mm 8mm;
            overflow: hidden;
        }

        /* 878 sidebar rhythm: 3.30mm between lines inside a section,
           5.00mm from a section's last line to the next heading. */
        .sidebar-section { margin-bottom: 1.70mm; }
        .sidebar-section:last-child { margin-bottom: 0; }

        .sidebar-section h3 {
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 500;
            font-size: 7.88pt;
            line-height: 9.35pt;
            letter-spacing: 0.01em;
            text-transform: uppercase;
            color: #000;
            margin: 0;
        }

        .sidebar-section p,
        .sidebar-section .sidebar-value {
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 400;
            font-size: 7.88pt;
            line-height: 9.35pt;
            letter-spacing: 0.01em;
            color: #000;
            margin: 0;
        }

        /* Equity indicator dots — inline SVG circles (border-radius spans
           rasterise as rounded rects in Chromium's print engine). */
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

        .equity-dot {
            width: 1.32mm;
            height: 1.32mm;
            display: inline-block;
            flex: 0 0 1.32mm;
            overflow: visible;
        }

        .equity-dot.filled circle { fill: var(--naartjie); }
        .equity-dot.empty circle { fill: var(--medium-grey); }

        /* === Content area — x=64mm → 204mm (140mm wide) === */
        .content-area {
            flex: 1;
            padding: 4.2mm 6mm 4mm 4mm;
            min-width: 0;
            overflow: hidden;
        }

        /* === Section headings — 7.5pt Avenir Next Medium, dark navy,
           per the signed-off balanced spec === */
        .section-heading {
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 500;
            font-size: 7.84pt;
            line-height: 9.4pt;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            color: var(--dark-navy);
            /* The reference indents its headings ~1mm inside the table grid. */
            margin: 0 0 0.8mm 0.95mm;
        }

        .section-heading .title-suffix {
            font-size: inherit;
            font-weight: 500;
            color: var(--dark-navy);
            text-transform: none;
            letter-spacing: 0.01em;
        }

        .section-heading sup {
            font-size: 5pt;
            line-height: 0;
            vertical-align: baseline;
            position: relative;
            top: -0.35em;
        }

        .section-subtitle {
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 500;
            font-size: 7.5pt;
            line-height: 9pt;
            letter-spacing: 0.01em;
            color: var(--dark-navy);
            margin: -0.5mm 0 0.9mm 0;
        }

        /* === Two-column layout === */
        .two-col {
            display: flex;
            gap: 6mm;
            margin-bottom: 2.6mm;
        }

        .two-col .col-left { flex: 1; min-width: 0; }
        .two-col .col-right { flex: 1; min-width: 0; }

        /* =====================================================
           PORTFOLIO STRUCTURE % — sector bars beside the TOP 10
           table (879 reference: no variance-to-benchmark column,
           unlike 877/878). Labels flush left, then the bar, the
           value, and the change arrow + number. Widths below are
           a placeholder fit for the half-width column pending
           Task 10's measurement loop.
           ===================================================== */
        .ps-section { margin-bottom: 6.2mm; }

        .ps-header {
            display: flex;
            align-items: flex-start;
            margin-bottom: 1.85mm;
        }

        .ps-header .ps-header-title { flex: 1; }

        .ps-row {
            display: flex;
            align-items: center;
            gap: 1mm;
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-size: 8.03pt;
            /* Fixed 4.0mm rows: the inline-block change columns would
               otherwise inflate the line box. */
            height: 4.0mm;
            line-height: 4.0mm;
            color: #000;
        }

        .ps-label {
            width: 33mm;
            text-align: left;
            padding-left: 1.2mm;
            padding-right: 1mm;
            flex-shrink: 0;
            font-weight: 400;
            white-space: nowrap;
        }

        .ps-bar-container {
            flex: 0 0 18.5mm;
            height: 2.4mm;
            position: relative;
        }

        .ps-bar {
            height: 2.4mm;
            background-color: var(--naartjie);
        }

        .ps-value {
            width: 3.5mm;
            text-align: right;
            flex-shrink: 0;
            font-weight: 400;
        }

        /* The arrow and the number occupy fixed columns so a wider change
           value never pushes the arrow left. */
        .ps-change {
            flex: 1;
            text-align: right;
            padding-right: 0.5mm;
        }

        .ps-arrow { display: inline-block; line-height: 4.0mm; }

        .ps-change-value {
            display: inline-block;
            width: 5mm;
            line-height: 4.0mm;
            text-align: right;
        }

        /* Reference arrows: black ▲ for up, steel-blue ▼ for down; the number
           stays black. Zero changes carry no arrow. */
        .change-up, .change-down { color: #000; }
        .change-up::before { content: '▲'; font-size: 5.75pt; color: #000; }
        .change-down::before { content: '▼'; font-size: 5.75pt; color: var(--light-blue); }

        /* =====================================================
           TABLES — signed-off styling: 1.1pt white separators,
           navy headers, right-aligned values, red total rows.
           ===================================================== */
        .table-wrapper {
            position: relative;
            margin-bottom: 2.6mm;
        }

        .foord-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: separate;
            border-spacing: 1.1pt 1.1pt;
            margin-left: -1.1pt;
            margin-right: 0;
            font-size: 8.03pt;
        }

        .foord-table th {
            background-color: var(--dark-navy);
            color: var(--white);
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 500;
            font-size: 8.03pt;
            line-height: 8.9pt;
            letter-spacing: 0;
            text-transform: uppercase;
            text-align: right;
            padding: 0.6mm 1.4mm 0.6mm 1.5mm;
        }

        .foord-table th:first-child { text-align: left; }

        .foord-table td {
            background-color: var(--row-grey-2);
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 400;
            font-size: 8.03pt;
            line-height: 8.9pt;
            padding: 0.62mm 1.4mm 0.62mm 1.5mm;
            text-align: right;
            overflow: hidden;
        }

        .foord-table td:first-child { text-align: left; }

        /* Superscript markers sit tight against the label at a modest
           raise (877 reference). */
        .foord-table td sup, .foord-table th sup {
            font-size: 5pt;
            line-height: 0;
            vertical-align: baseline;
            position: relative;
            top: -0.25em;
            margin-left: 0.25mm;
        }

        .foord-table tr.total-row td {
            background-color: var(--naartjie);
            font-weight: 500;
            color: var(--white);
        }

        .foord-table tbody tr.highlight-row td {
            background-color: var(--naartjie-20);
            color: #000;
        }

        .foord-table tbody tr.highlight-row td:first-child {
            color: #000;
            font-weight: 400;
        }

        /* Spacer rows in the performance table — grey like the signed-off page */
        .foord-table tr.empty-row td {
            background-color: var(--row-grey-2) !important;
            padding: 0;
            height: 3.58mm;
            line-height: 3.58mm;
            font-size: 0;
        }

        /* Top 10 — 879 reference has just two columns (SECURITY, % OF FUND),
           unlike 877/878's four (SECTOR and MARKET are dropped). Column
           widths are a placeholder proportion pending Task 10's loop. */
        .top10-table .foord-table td,
        .top10-table .foord-table th {
            padding-top: 0.41mm;
            padding-bottom: 0.41mm;
        }
        .top10-table .foord-table td:first-child,
        .top10-table .foord-table th:first-child {
            width: 72%;
            padding-left: 1.74mm;
        }
        .top10-table .foord-table tbody tr td { background-color: var(--row-grey-2); }

        .top10-table { margin-bottom: 5.2mm; }
        .top10-table .foord-table { margin-top: 0.7mm; }

        /* Performance table — name/cash/since-inception widths carried over
           from the 878 grid; the six period columns (3 YRS … THIS MONTH)
           corrected here. 878's CSS only set nth-child(4)-(7), leaving YTD
           and THIS MONTH unset — table-layout: fixed then split the small
           leftover width 50/50 between them, which was far too little for
           "MONTH" and clipped it against the page edge. The 879 reference
           (measured off its bbox layout) keeps these six columns close to
           equal width, so all six are given explicit, near-equal widths
           here, with a little extra on THIS MONTH so "MONTH" clears on its
           own line. Re-tune in Task 10's mm pass if needed. */
        .perf-table th {
            font-size: 8.03pt;
            line-height: 10.9pt;
            text-align: right;
            padding: 0.8mm 1.15mm 0.1mm 1.15mm;
            vertical-align: bottom;
        }
        /* Name column width carried over from 877, where it held the
           longer "MSCI AC World Index" benchmark name on one line. 879's
           own benchmark row reads "MSCI Asia ex-Japan" — shorter, so the
           same width holds it fine; verified against the 879 reference. */
        .perf-table th:first-child {
            text-align: left;
            width: 24.02%;
            padding-left: 1.27mm;
        }
        .perf-table th:nth-child(2) { width: 14.03%; }
        .perf-table th:nth-child(3) { width: 15.12%; }
        .perf-table th:nth-child(4) { width: 7.6%; }
        .perf-table th:nth-child(5) { width: 7.4%; }
        .perf-table th:nth-child(6) { width: 7.6%; }
        .perf-table th:nth-child(7) { width: 7.4%; }
        .perf-table th:nth-child(8) { width: 7.6%; }
        .perf-table th:last-child { width: 9.23%; }
        .perf-table td {
            color: #000;
            font-size: 8.03pt;
            line-height: 8.5pt;
            padding: 0.65mm 1.15mm;
        }
        .perf-table td:first-child { padding-left: 1.7mm; }
        .perf-table th:first-child { padding-left: 1.7mm; }
        .perf-table th:last-child,
        .perf-table td:last-child { padding-right: 1.1mm; }
        /* The reference leaves a deeper band before the highest/lowest rows. */
        .perf-table tr.empty-row td { height: 4.28mm; line-height: 4.28mm; }
        /* Row greys fade down the table (877 reference): Fund pink,
           MSCI #d4d4d4, Peer group #dddddd, sterling/euro rows #e6e6e6,
           highest/lowest #f0f0f0. */
        .perf-table tbody tr td { background-color: var(--row-grey-1); }
        .perf-table tbody tr:nth-child(1) td { background-color: var(--naartjie-20); }
        .perf-table tbody tr:nth-child(2) td { background-color: var(--row-grey-0); }
        .perf-table tbody tr:nth-child(5) td,
        .perf-table tbody tr:nth-child(6) td { background-color: var(--row-grey-2); }
        .perf-table tbody tr:nth-child(4).empty-row td,
        .perf-table tbody tr:nth-child(7).empty-row td { background-color: var(--row-grey-3) !important; }
        .perf-table tbody tr:nth-child(8) td,
        .perf-table tbody tr:nth-child(9) td { background-color: var(--row-grey-4); }
        .perf-table tbody tr td.cell-empty { background-color: var(--white); }

        /* Annualised cost ratio — reference: three equal ~46.4mm columns,
           headers and values centred. The "— Performance" component row
           indents under TER — Basic (877 reference). */
        .cost-table .foord-table th:first-child,
        .cost-table .foord-table td:first-child {
            width: 51.34%;
            padding-left: 1.36mm;
        }
        .cost-table .foord-table th:nth-child(2),
        .cost-table .foord-table td:nth-child(2) { width: 24.45%; }
        .cost-table .foord-table th:not(:first-child),
        .cost-table .foord-table td:not(:first-child) { text-align: center; padding-right: 3.1mm; }
        .cost-table .foord-table th { font-size: 8.01pt; padding-top: 0.3mm; }
        .cost-table .foord-table td {
            font-size: 8.01pt;
            padding-top: 0.65mm;
            padding-bottom: 0.65mm;
        }
        .cost-table .foord-table td.indent-cell { padding-left: 7mm; }
        .cost-table .foord-table tr.total-row td {
            font-size: 8.01pt;
            font-weight: 500;
            padding-top: 0.65mm;
            padding-bottom: 0.45mm;
        }

        /* Performance fee examples — name col + four equal PERIOD columns,
           right-aligned values (877 reference); the accrual row carries a
           second [calculation] line per cell. */
        .pfe-table .foord-table th:first-child,
        .pfe-table .foord-table td:first-child {
            width: 33.0%;
            padding-left: 1.36mm;
        }
        .pfe-table .foord-table th { font-size: 6.96pt; padding-top: 0.4mm; padding-bottom: 0.1mm; }
        /* Reference: the two-line accrual row sets its label on the lower line. */
        .pfe-table .foord-table tbody tr:last-child td { vertical-align: bottom; }
        .pfe-table .foord-table td {
            font-size: 7.53pt;
            padding-top: 0.3mm;
            padding-bottom: 0.3mm;
        }
        .pfe-note {
            font-family: 'Lato', 'Avenir Next', sans-serif;
            font-weight: 400;
            font-size: 6.92pt;
            line-height: 8.4pt;
            letter-spacing: 0.01em;
            color: var(--dark-navy);
            margin: 0.6mm 0 0 0;
            /* Wraps a word earlier than the prose above it (reference). */
            padding-left: 2.2mm;
            padding-right: 2.5mm;
            text-indent: -2.2mm;
        }
        .pfe-note sup {
            font-size: 4.8pt;
            line-height: 0;
            vertical-align: baseline;
            position: relative;
            top: -0.3em;
        }

        /* === Charts === */
        /* Plot boxes measured off the reference: the geographic chart's 0%
           baseline lands at y=167.5mm, the performance chart's plot runs
           133.1mm to 172.0mm. */
        .chart-wrapper {
            height: 45mm;
            position: relative;
        }

        /* Widened 3mm into the two-col gutter on the right — the same
           allowance .perf-wrapper already takes from its own side (see its
           margin-left: -3mm below) — so the extra canvas width can be
           spent on right-hand label room without the two columns
           overlapping (each claims half the 6mm gap). */
        .geo-pie-wrapper { height: 49mm; margin-right: -3mm; }
        /* The reference sets the "100" axis label outside the column's left
           edge, so the canvas is widened into the gutter and the plot's left
           padding grows to match. */
        .perf-wrapper { height: 46.06mm; margin-left: -3mm; }
        .perf-wrapper .chart-ytitle { left: -7mm; }

        .chart-wrapper canvas {
            width: 100% !important;
            height: 100% !important;
        }

        .chart-ytitle {
            position: absolute;
            left: -10mm;
            top: 10.3mm;
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

        /* 877 reference: hairline swatches and lighter slate legend text. */
        /* The reference legends are left-aligned under their plots and wrap
           (the performance legend runs onto a second line). */
        .chart-legend {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-start;
            gap: 0.5mm 7.3mm;
            line-height: 2.6mm;
            margin-top: 1.4mm;
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-size: 6pt;
            color: #4d585e;
        }

        .perf-legend { padding-left: 10.38mm; margin-top: 0.2mm; }

        .chart-legend span {
            display: flex;
            align-items: center;
            gap: 2.1mm;
        }

        .legend-line {
            width: 4.8mm;
            height: 0.2mm;
            display: inline-block;
        }

        .legend-square {
            width: 1.8mm;
            height: 1.8mm;
            display: inline-block;
        }

        /* === Footnotes ===
           The performance-table footnotes render on page 1, directly under
           the table (879 reference; 878's clone wrongly put them on page 2
           under a NOTES heading — moved here in Task 9). Sizing measured
           off the 879 reference at 150dpi: line pitch 19px = 9.12pt, glyph
           height consistent with ~7.6pt — matched here to 7.59pt/9.35pt.
           Hanging indent aligns wrapped lines after the superscript. */
        .perf-footnotes {
            margin-top: 2.1mm;
        }

        .footnote {
            font-family: 'Lato', 'Avenir Next', sans-serif;
            font-weight: 400;
            font-size: 7.59pt;
            line-height: 9.35pt;
            letter-spacing: 0.01em;
            color: var(--dark-navy);
            margin: 0;
            padding-left: 1.65mm;
            text-indent: -1.65mm;
        }

        .footnote sup {
            font-size: 5pt;
            line-height: 0;
            vertical-align: baseline;
            position: relative;
            top: -0.3em;
        }

        /* =====================================================
           PAGE 2 — info sidebar (signed-off geometry: navy box
           45.7 x 11mm at (9.15mm, 10mm))
           ===================================================== */
        .info-sidebar {
            width: 60mm;
            min-width: 60mm;
            max-width: 60mm;
            background-color: transparent;
            padding: 0;
            overflow: hidden;
        }

        .important-info-header {
            background-color: var(--dark-navy);
            color: var(--white);
            height: 11mm;
            box-sizing: border-box;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 2mm;
            margin: 10mm 3.44mm 0 11.01mm;
            text-align: center;
        }

        .important-info-header h2 {
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 500;
            font-size: 7.85pt;
            line-height: 10.9pt;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            margin: 0;
        }

        .info-sidebar-content { padding: 6.5mm 4.8mm 4mm 9mm; }

        /* Reference: 8.5pt Lato Light on a 9.6pt leading */
        .info-sidebar-content p,
        .important-info-text {
            font-family: 'Lato', 'Avenir Next', sans-serif;
            font-weight: 300;
            font-size: 8.5pt;
            line-height: 9.18pt;
            letter-spacing: 0.01em;
            color: var(--dark-navy);
            margin: 0 0 1.1mm 0;
            text-align: left;
        }

        /* Hyperlinks render gold with an underline (measured #c09000) */
        .ref-link {
            color: #c09000;
            text-decoration: underline;
        }

        .info-sidebar-content p:last-child { margin-bottom: 0; }

        /* === Page 2 content === */
        .page2-content {
            flex: 1;
            /* ANNUALISED COST RATIO % table header lands at y=20.6mm; the
               main column spans x 64.9mm → 202.9mm (877 reference) */
            padding: 20.8mm 5.5mm 7.7mm 4.86mm;
            min-width: 0;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .page2-section { margin-bottom: 2.6mm; }

        /* The reference sets each page-2 block in its own Publisher text box,
           so the measured column width and leading differ per block. */
        .pfe-table.page2-section { margin-bottom: 3.5mm; }
        .page2-section.share-pricing { padding-right: 4.1mm; margin-bottom: 2.1mm; }
        .page2-section.share-pricing .page2-body { line-height: 9.07pt; }
        .page2-section.more-about { margin-bottom: 1.8mm; }
        .page2-section.more-about .page2-body { line-height: 9.5pt; }
        .page2-section.more-about .page2-heading { margin-bottom: 0.5mm; }

        /* The reference's prose runs to x=204mm while its tables stop at
           203.2mm, so the tables pull back from the column's right edge. */
        .page2-content .foord-table { margin-left: -1.23mm; margin-right: 1.31mm; }

        .page2-heading {
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 500;
            font-size: 7.76pt;
            line-height: 9pt;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            color: var(--dark-navy);
            margin: 0 0 1.2mm 0;
        }

        .page2-body {
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 400;
            font-size: 7.65pt;
            line-height: 8.79pt;
            letter-spacing: 0.01em;
            color: #000;
        }

        /* === Footer — short naartjie rule, Merriweather body,
           Avenir Next Medium contact lines, all naartjie === */
        .footer-divider {
            margin-top: auto;
            padding-top: 3.9mm;
            border-top: none;
            position: relative;
        }

        .footer-divider::after {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 14.23mm;
            height: 0;
            border-top: 0.35mm solid var(--naartjie);
        }

        .footer-info {
            font-family: 'Merriweather', Georgia, serif;
            font-weight: 400;
            font-size: 8.1pt;
            line-height: 10.1pt;
            letter-spacing: 0.01em;
            color: var(--naartjie);
            margin: 0 0 2.8mm 0;
        }

        .footer-contact {
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 500;
            font-size: 7.9pt;
            line-height: 10.9pt;
            letter-spacing: 0.01em;
            color: var(--naartjie);
            position: relative;
            margin-top: 3.0mm;
        }

        .footer-contact p { margin: 0; }

        .footer-leaf {
            position: absolute;
            right: 4mm;
            top: 0;
            width: 11mm;
            height: auto;
        }

        /* =====================================================
           SCREEN CHROME (not printed)
           ===================================================== */
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

        .notification {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 50;
            transform: translateX(100%);
            transition: transform 0.3s ease-in-out;
        }
        .notification.show { transform: translateX(0); }

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
            <!-- Header: navy date badge + logo (signed-off geometry) -->
            <div class="header-row">
                <div class="date-badge">
                    <span x-data="editableField('fund.date', '{{ $fund->data['fund']['date'] ?? $fund->updated_at->format('d F Y') }}')"
                          @click="editMode && startEdit()"
                          :class="editMode ? 'editable' : ''"
                          x-text="value"></span>
                </div>
                <div class="header-logo">
                    <img src="{{ $fund->data['fund']['logoUrl'] ?? '' }}" alt="Foord Logo" class="foord-logo">
                </div>
            </div>

            <!-- Fund Name Banner (naartjie; class suffix prints inline —
                 879 reference: "FOORD ASIA EX-JAPAN FUND — CLASS R") -->
            <div class="fund-banner">
                @php
                    $fundName = $fund->data['fund']['name'] ?? $fund->name;
                    if (preg_match('/^(.+?)\s*[-—–]\s*(CLASS\s+[A-Z][0-9]*)$/iu', $fundName, $nameMatches)) {
                        $mainName = trim($nameMatches[1]);
                        $classText = mb_strtoupper(trim($nameMatches[2]));
                    } else {
                        $mainName = $fundName;
                        $classText = '';
                    }
                @endphp
                <h1>
                    <span x-data="editableField('fund.name', '{{ addslashes($fund->data['fund']['name'] ?? $fund->name) }}', 'fundNameWithClass')"
                          @click="editMode && startEdit()"
                          :class="editMode ? 'editable' : ''">{{ mb_strtoupper($mainName) }}@if($classText)<span class="class-suffix"> — {{ $classText }}</span>@endif</span>
                </h1>
                <p class="description">
                    <span x-data="editableField('fund.description', '{{ addslashes($fund->data['fund']['description'] ?? '') }}')"
                          @click="editMode && startEdit()"
                          :class="editMode ? 'editable' : ''"
                          x-text="value"></span>
                </p>
            </div>

            @php
                // One-decimal display for percentage cells (reference shows
                // 15.0 / 3.0 …); strings and signed values pass through.
                $fmt = function ($v, int $dp = 1) {
                    if ($v === null || $v === '') return '';
                    if (is_string($v) && str_starts_with(ltrim($v), '+')) return $v;
                    if (is_numeric($v)) return number_format((float) $v, $dp);
                    return (string) $v;
                };
                // Bracketed qualifiers in section headings render smaller;
                // trailing superscript digits inside the brackets keep their
                // superscript treatment ("…ANNUALISED¹").
                $renderHeading = function (string $title): string {
                    $html = preg_replace(
                        '/\s*\(([^)]+)\)/u',
                        ' <span class="title-suffix">($1)</span>',
                        e($title)
                    );

                    return strtr($html, [
                        '¹' => '<sup>1</sup>', '²' => '<sup>2</sup>', '³' => '<sup>3</sup>',
                        '⁴' => '<sup>4</sup>', '⁵' => '<sup>5</sup>', '⁶' => '<sup>6</sup>',
                        '⁷' => '<sup>7</sup>', '⁸' => '<sup>8</sup>',
                    ]);
                };
                // Reference: URLs and email addresses render naartjie
                // (mirrored client-side by the `linkify` display formatter).
                $linkify = function (string $text): string {
                    return preg_replace(
                        '/((?:www\.|https?:\/\/)[^\s,)]+|[\w.+-]+@[\w.-]+\.\w+)/',
                        '<span class="ref-link">$1</span>',
                        e($text)
                    );
                };
            @endphp

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
                                // 879 reference: "INITIAL", not "MINIMUM".
                                'minimumSubscriptionAmount' => 'INITIAL SUBSCRIPTION AMOUNT',
                                'subsequentSubscriptionAmount' => 'SUBSEQUENT SUBSCRIPTION AMOUNT',
                                // 879 reference: "FUND SIZE", not "TOTAL PORTFOLIO SIZE".
                                'totalFundSize' => 'FUND SIZE',
                                'numberOfShares' => 'NUMBER OF SHARES',
                                'investmentManager' => 'INVESTMENT MANAGER',
                                'managementCompany' => 'MANAGEMENT COMPANY',
                                // 879 names both managers (Ishreth Hassen and
                                // Jing Cong Xue).
                                'fundManagers' => 'FUND MANAGERS',
                                // 877 reference: the inception row pairs the fund
                                // and class dates; single-date classes (B) keep the
                                // plain label.
                                'inceptionDate' => str_contains((string) ($sidebar['inceptionDate'] ?? ''), '/')
                                    ? 'INCEPTION DATE (FUND / CLASS)'
                                    : 'INCEPTION DATE',
                                'baseCurrency' => 'BASE CURRENCY',
                                'equityIndicator' => 'EQUITY INDICATOR',
                                'benchmark' => 'BENCHMARK',
                                'typeOfShares' => 'TYPE OF SHARES',
                                'timeHorizon' => 'TIME HORIZON',
                                'domicile' => 'DOMICILE',
                                // The 877 sheet spells it DEPOSITARY (875 prints
                                // DEPOSITORY).
                                'depository' => 'DEPOSITARY',
                                'isinNumber' => 'ISIN NUMBER',
                                // 879 reference: no class suffix on the FEES heading.
                                'fees' => 'FEES',
                            ];

                            // Display order per the 879 reference sidebar — no
                            // SHARE CLASS row (the class prints in the banner
                            // instead) and no SHARIAH SUPERVISORY BOARD row.
                            $displayOrder = [
                                'marketingCommunication', 'domicile', 'managementCompany',
                                'depository', 'investmentManager', 'subInvestmentManager',
                                'fundManagers', 'inceptionDate', 'baseCurrency', 'equityIndicator',
                                'morningstarCategory', 'benchmark', 'typeOfShares',
                                'minimumSubscriptionAmount', 'subsequentSubscriptionAmount',
                                'totalFundSize', 'monthEndSharePrice', 'numberOfShares',
                                'timeHorizon', 'fees', 'isinNumber',
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
                                        @php
                                            /* 879 reference: nine of ten dots filled */
                                            $filledDots = $value['filled'] ?? 9;
                                            $totalDots = $value['total'] ?? 10;
                                        @endphp
                                        {{-- Heading + dots share one line; SVG circles stay
                                             round in Chromium's print engine. --}}
                                        <h3 class="equity-heading">
                                            {{ $label }}
                                            <span class="equity-indicator">
                                                @for ($i = 0; $i < $totalDots; $i++)
                                                    <svg class="equity-dot {{ $i < $filledDots ? 'filled' : 'empty' }}" viewBox="0 0 10 10"><circle cx="5" cy="5" r="5"/></svg>
                                                @endfor
                                            </span>
                                        </h3>
                                        <p>
                                            <span x-data="editableField('sidebar.{{ $key }}.description', '{{ addslashes($value['description'] ?? '') }}')"
                                                  @click="editMode && startEdit()"
                                                  :class="editMode ? 'editable' : ''"
                                                  x-text="value"></span>
                                        </p>
                                    </div>
                                @elseif ($key === 'marketingCommunication')
                                    {{-- Reference: the label is set ~13% larger than the
                                         other sidebar headings, same medium weight. --}}
                                    <div class="sidebar-section">
                                        <h3 style="font-size: 8.9pt; line-height: 10.7pt;">{{ $label }}</h3>
                                    </div>
                                @elseif (!is_array($value))
                                    <div class="sidebar-section">
                                        {{-- 879 reference: INITIAL SUBSCRIPTION AMOUNT holds one
                                             line (SUBSEQUENT … wraps); the data key is still
                                             'minimumSubscriptionAmount' (relabelled above). --}}
                                        <h3 @if($key === 'minimumSubscriptionAmount') style="white-space: nowrap;" @endif>{{ $label }}</h3>
                                        {{-- The reference sets the three-line FEES value
                                             on a looser 3.65mm pitch than the rest. --}}
                                        <p @if($key === 'fees') style="line-height: 10.35pt;" @endif>
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
                    <!-- Row 1: PORTFOLIO STRUCTURE % sector bars beside the
                         TOP 10 INVESTMENTS table (879 reference — neither
                         runs full width, and there is no variance column). -->
                    <div class="two-col">
                        <!-- Left: Portfolio Structure % -->
                        <div class="col-left">
                            @if(!empty($fund->data['mainContent']['sectorAllocation']['sectors']))
                                @php
                                    $psData = $fund->data['mainContent']['sectorAllocation'];
                                    $psSectors = $psData['sectors'];
                                    // The 878 feed reports no name for the sectors the fund
                                    // holds nothing in (ESAOT ranks 11-13 come through blank),
                                    // so the reference's zero-weight tail is seeded static and
                                    // appended here — see FUND-ONBOARDING.md §5p. The 879 July
                                    // feed sends none, but the loop is harmless and shared with
                                    // future months.
                                    foreach (($psData['zeroWeightSectors'] ?? []) as $zeroRow) {
                                        $psSectors[] = $zeroRow + ['value' => '-', 'change' => '-'];
                                    }
                                    // Bars are drawn at the reference's fixed scale
                                    // (0.61mm per percentage point, measured at 300dpi
                                    // against the published values), not normalised to
                                    // the month's largest holding. Widest published bar
                                    // (Consumer discretionary, 30%) measures 17.95mm.
                                    // Task 10 re-check: pixel-scanned all ten reference
                                    // bars at 200dpi (773px common left edge; per-row
                                    // width / percentage = 0.59-0.70mm/pt, jitter typical
                                    // of a hand-drawn Publisher chart) and least-squares
                                    // fit through the origin lands on 0.612mm/pt — 0.61
                                    // confirmed. The 18.5mm clamp doesn't bind at 30%
                                    // (18.3mm) in either the reference or this render.
                                    $psBarScale = 0.61;
                                @endphp
                                <div class="ps-section">
                                    <div class="ps-header">
                                        <div class="ps-header-title">
                                            <h3 class="section-heading" style="margin-bottom: 0;">
                                                <span x-data="editableField('mainContent.sectorAllocation.title', '{{ addslashes($psData['title'] ?? 'PORTFOLIO STRUCTURE %') }}')"
                                                      @click="editMode && startEdit()"
                                                      :class="editMode ? 'editable' : ''"
                                                      x-text="value"></span>
                                            </h3>
                                            {{-- 879 reference: the subtitle prints below the
                                                 title, not in a right-hand column (877/878). --}}
                                            <span class="section-subtitle" x-data="editableField('mainContent.sectorAllocation.subtitle', '{{ addslashes($psData['subtitle'] ?? '') }}')"
                                                  @click="editMode && startEdit()"
                                                  :class="editMode ? 'editable' : ''"
                                                  x-text="value"></span>
                                        </div>
                                    </div>
                                    <div>
                                        @foreach ($psSectors as $rowIndex => $row)
                                            @php
                                                $changeNumber = trim(str_replace(['▲', '▼'], '', (string) ($row['change'] ?? '')));
                                                // Show the arrow whenever a direction was recorded, even if the
                                                // magnitude rounds to 0.0 (e.g. Materials: CHANGE_SIGN '-', CHANGE
                                                // '0.0' — a real direction with a near-zero magnitude). Only
                                                // suppress it when direction is genuinely unknown (empty).
                                                $changeClass = ((($row['direction'] ?? '') === 'up') ? 'change-up' : ((($row['direction'] ?? '') === 'down') ? 'change-down' : ''));
                                                $barClass = 'ps-bar';
                                            @endphp
                                            <div class="ps-row">
                                                <span class="ps-label">
                                                    <span x-data="editableField('mainContent.sectorAllocation.sectors.{{ $rowIndex }}.name', '{{ $row['name'] }}')"
                                                          @click="editMode && startEdit()"
                                                          :class="editMode ? 'editable' : ''"
                                                          x-text="value"></span>
                                                </span>
                                                <div class="ps-bar-container">
                                                    <div class="{{ $barClass }}" style="width: {{ min(18.5, round((float) ($row['value'] ?? 0) * $psBarScale, 2)) }}mm;"></div>
                                                </div>
                                                <span class="ps-value">
                                                    <span x-data="editableField('mainContent.sectorAllocation.sectors.{{ $rowIndex }}.value', '{{ $row['value'] ?? '' }}')"
                                                          @click="editMode && startEdit()"
                                                          :class="editMode ? 'editable' : ''"
                                                          x-text="value"></span>
                                                </span>
                                                <span class="ps-change">
                                                    <span class="ps-arrow {{ $changeClass }}"></span><span class="ps-change-value">{{ $changeNumber }}</span>
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Right: Top 10 Investments -->
                        <div class="col-right">
                            @if(isset($fund->data['mainContent']['topInvestments']))
                                <div class="top10-table">
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
                                                        <th>{{ $header }}</th>
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
                                                        <td>{{ $fmt($row['percentage'] ?? '') }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Two-column: Geographic Country Exposure pie + Performance chart -->
                    <div class="two-col">
                        <!-- Left: Geographic Country Exposure (pie chart) -->
                        <div class="col-left">
                            @if(!empty($fund->data['mainContent']['assetAllocation']['geographicCountryExposure']))
                                <div>
                                    <h3 class="section-heading">GEOGRAPHIC COUNTRY EXPOSURE</h3>
                                    <div class="chart-wrapper geo-pie-wrapper">
                                        <canvas id="geoPieChart"></canvas>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Right: Portfolio Performance vs Benchmark -->
                        <div class="col-right">
                            @if(isset($fund->data['mainContent']['charts']))
                                <div>
                                    <h3 class="section-heading">
                                        <span x-data="editableField('mainContent.charts.title', '{{ $fund->data['mainContent']['charts']['title'] ?? 'PORTFOLIO PERFORMANCE VS BENCHMARK' }}')"
                                              @click="editMode && startEdit()"
                                              :class="editMode ? 'editable' : ''"
                                              x-text="value"></span>
                                    </h3>
                                    <div class="chart-wrapper perf-wrapper">
                                        <div class="chart-ytitle">Cash Value<sup>2</sup> ($&rsquo;000)</div>
                                        <canvas id="performanceChart"></canvas>
                                    </div>
                                    {{-- Legend per the 879 reference: Fund red, the MSCI
                                         Asia ex-Japan benchmark dark navy, the peer group
                                         steel blue. The reference wraps this legend onto two
                                         lines (Fund/MSCI on the first, Peer Group beneath
                                         Fund) via the legend's flex wrapping, not a <br>. --}}
                                    <div class="chart-legend perf-legend">
                                        <span><span class="legend-line" style="background: var(--naartjie);"></span> Fund</span>
                                        <span><span class="legend-line" style="background: var(--dark-navy);"></span> MSCI Asia ex-Japan USD</span>
                                        <span><span class="legend-line" style="background: var(--light-blue);"></span> Peer Group</span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Performance Table -->
                    @if(isset($fund->data['mainContent']['performanceTable']))
                        <div>
                            <h3 class="section-heading">
                                <span x-data="editableField('mainContent.performanceTable.title', '{!! addslashes($fund->data['mainContent']['performanceTable']['title']) !!}', 'headingSuffix')"
                                      @click="editMode && startEdit()"
                                      :class="editMode ? 'editable' : ''">{!! $renderHeading($fund->data['mainContent']['performanceTable']['title']) !!}</span>
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
                                        {{-- 879 display rules: the import writes raw row names
                                             (Fund, Benchmark, Comparator 2, Fund highest/
                                             lowest); the fact sheet renames them with footnote
                                             superscripts, ordered Fund/MSCI Asia ex-Japan/
                                             Peer group, spacer, highest/lowest. 879 has no
                                             sterling/euro comparator rows (877 only). --}}
                                        @php
                                            $perfRowsRaw = $fund->data['mainContent']['performanceTable']['rows'];
                                            $perfColKeysGe = $fund->data['mainContent']['performanceTable']['columnKeys'] ?? [];
                                            $geNames = [
                                                'fund' => 'Fund <sup>3</sup>',
                                                'benchmark' => 'MSCI Asia ex-Japan',
                                                'comparator 2' => 'Peer group <sup>4</sup>',
                                                'fund highest' => 'Fund highest <sup>3,5</sup>',
                                                'fund lowest' => 'Fund lowest <sup>3,5</sup>',
                                            ];
                                            // 879 quotes no sterling/euro share classes:
                                            // Fund/MSCI Asia ex-Japan/Peer group, spacer, highest/lowest.
                                            $geOrder = [
                                                ['fund', 'benchmark', 'comparator 2'],
                                                ['fund highest', 'fund lowest'],
                                            ];
                                            $rowsByKey = [];
                                            foreach ($perfRowsRaw as $i => $r) {
                                                $rowsByKey[strtolower(trim(strip_tags((string)($r['name'] ?? ''))))] = [$i, $r];
                                            }
                                        @endphp
                                        @foreach ($geOrder as $groupIndex => $group)
                                            @if ($groupIndex > 0)
                                                <tr class="empty-row"><td colspan="{{ count($fund->data['mainContent']['performanceTable']['headers']) }}"></td></tr>
                                            @endif
                                            @foreach ($group as $rowKey)
                                                @continue(!isset($rowsByKey[$rowKey]))
                                                @php [$rowIndex, $row] = $rowsByKey[$rowKey]; @endphp
                                                <tr class="{{ $rowKey === 'fund' ? 'highlight-row' : '' }}">
                                                    <td>{!! $geNames[$rowKey] !!}</td>
                                                    @foreach ($perfColKeysGe as $colKey)
                                                        @php
                                                            $cellValue = $row[$colKey] ?? '';
                                                            $cellDisplay = $colKey === 'cashValue' ? $cellValue : $fmt($cellValue);
                                                        @endphp
                                                        {{-- Empty cells render white in the reference --}}
                                                        <td class="{{ trim((string) $cellDisplay) === '' ? 'cell-empty' : '' }}">
                                                            <span x-data="editableField('mainContent.performanceTable.rows.{{ $rowIndex }}.{{ $colKey }}', '{{ $cellDisplay }}')"
                                                                  @click="editMode && startEdit()"
                                                                  :class="editMode ? 'editable' : ''"
                                                                  x-text="value"></span>
                                                        </td>
                                                    @endforeach
                                                </tr>
                                            @endforeach
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            {{-- 879 reference: the footnotes print on page 1, directly
                                 beneath this table, with no NOTES heading — page 1 carries
                                 the superscripts (ANNUALISED¹, CASH VALUE², Fund³, Peer
                                 group⁴, Fund highest³ˑ⁵) that these notes resolve, so they
                                 cannot live on page 2 as 878's clone had them. --}}
                            @if(!empty($fund->data['mainContent']['performanceTable']['footnotes']))
                                <div class="perf-footnotes">
                                    @foreach ($fund->data['mainContent']['performanceTable']['footnotes'] as $index => $note)
                                        <p class="footnote">
                                            <span x-data="editableField('mainContent.performanceTable.footnotes.{{ $index }}', '{!! addslashes($note) !!}')"
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
            <div class="main-body" style="min-height: 297mm;">
                <!-- Left Sidebar - Important Information -->
                @if(isset($fund->data['importantInfo']))
                    <div class="info-sidebar">
                        {{-- Navy header box mirrors the p1 date badge geometry --}}
                        <div class="important-info-header">
                            <h2>
                                <span x-data="editableField('importantInfo.title', '{{ $fund->data['importantInfo']['title'] }}')"
                                      @click="editMode && startEdit()"
                                      :class="editMode ? 'editable' : ''"
                                      x-text="value"></span>
                            </h2>
                        </div>
                        <div class="info-sidebar-content">
                            {{-- The TER paragraph lives in this same array (the
                                 importer's updateTerFootnote() refreshes its
                                 audited-TER percentage from here on every
                                 import), but the 879 reference prints it in the
                                 page-2 main column beneath the ANNUALISED COST
                                 RATIO table, not in this sidebar — skip it here
                                 and render it there instead. --}}
                            @foreach ($fund->data['importantInfo']['paragraphs'] as $index => $paragraph)
                                @continue(str_contains($paragraph, 'A TER is a measure'))
                                <p class="important-info-text">
                                    <span x-data="editableField('importantInfo.paragraphs.{{ $index }}', '{{ addslashes($paragraph) }}', 'linkify')"
                                          @click="editMode && startEdit()"
                                          :class="editMode ? 'editable' : ''">{!! $linkify($paragraph) !!}</span>
                                </p>
                            @endforeach
                            {{-- The reference's grey prose is set as a stack of
                                 Publisher text boxes of slightly different widths, so
                                 one paragraph wraps a word earlier there than it can
                                 here; the extra leading holds the published-on line at
                                 the reference's y. --}}
                            <p class="important-info-text" style="margin-top: 4mm;">
                                <span x-data="editableField('importantInfo.publishedDate', '{{ $fund->data['importantInfo']['publishedDate'] }}')"
                                      @click="editMode && startEdit()"
                                      :class="editMode ? 'editable' : ''"
                                      x-text="value"></span>
                            </p>
                        </div>
                    </div>
                @endif

                <!-- Right Content -->
                <div class="page2-content">
                    <!-- Annualised Cost Ratio -->
                    @if(isset($fund->data['fees']['annualisedCostRatio']))
                        <div class="page2-section cost-table" style="margin-bottom: 0;">
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
                                                {{-- The "— Performance" component row indents
                                                     under TER — Basic (877 reference) --}}
                                                <td class="{{ str_starts_with(trim((string) $row['name']), '—') ? 'indent-cell' : '' }}">
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
                            @if(!empty($fund->data['fees']['annualisedCostRatio']['description']))
                                <p class="page2-body" style="margin-top: 5px;">
                                    <span x-data="editableField('fees.annualisedCostRatio.description', '{{ addslashes($fund->data['fees']['annualisedCostRatio']['description']) }}')"
                                          @click="editMode && startEdit()"
                                          :class="editMode ? 'editable' : ''"
                                          x-text="value"></span>
                                </p>
                            @endif
                        </div>
                    @endif

                    {{-- TER explanatory paragraph. The content is the last
                         entry in importantInfo.paragraphs (see the sidebar
                         loop above, which skips it) so
                         FactsheetImporter::updateTerFootnote() keeps
                         refreshing its audited-TER percentage on every
                         import — only where it renders has moved. The 879
                         reference prints it here in the main column,
                         directly beneath the ANNUALISED COST RATIO table
                         (878's clone put it in the sidebar instead). --}}
                    @php
                        $terParagraphIndex = null;
                        foreach ($fund->data['importantInfo']['paragraphs'] ?? [] as $terIdx => $terParagraph) {
                            if (str_contains($terParagraph, 'A TER is a measure')) {
                                $terParagraphIndex = $terIdx;
                                break;
                            }
                        }
                    @endphp
                    @if($terParagraphIndex !== null)
                        <div class="page2-section ter-note">
                            <p class="page2-body" style="margin-top: 5px;">
                                <span x-data="editableField('importantInfo.paragraphs.{{ $terParagraphIndex }}', '{{ addslashes($fund->data['importantInfo']['paragraphs'][$terParagraphIndex]) }}', 'linkify')"
                                      @click="editMode && startEdit()"
                                      :class="editMode ? 'editable' : ''">{!! $linkify($fund->data['importantInfo']['paragraphs'][$terParagraphIndex]) !!}</span>
                            </p>
                        </div>
                    @endif

                    <!-- Share Pricing and Transactions -->
                    @if(isset($fund->data['page2Content']['sharePricing']))
                        <div class="page2-section share-pricing">
                            <h3 class="page2-heading">
                                <span x-data="editableField('page2Content.sharePricing.title', '{{ $fund->data['page2Content']['sharePricing']['title'] }}')"
                                      @click="editMode && startEdit()"
                                      :class="editMode ? 'editable' : ''"
                                      x-text="value"></span>
                            </h3>
                            {{-- The reference sets this block's www.foord.com in plain
                                 black; only the MORE ABOUT and sidebar links run gold. --}}
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
                        <div class="page2-section more-about">
                            <h3 class="page2-heading">
                                <span x-data="editableField('page2Content.moreAboutFund.title', '{{ $fund->data['page2Content']['moreAboutFund']['title'] }}')"
                                      @click="editMode && startEdit()"
                                      :class="editMode ? 'editable' : ''"
                                      x-text="value"></span>
                            </h3>
                            @foreach ($fund->data['page2Content']['moreAboutFund']['paragraphs'] as $index => $paragraph)
                                <p class="page2-body" style="margin-bottom: 1.4mm;">
                                    <span x-data="editableField('page2Content.moreAboutFund.paragraphs.{{ $index }}', '{{ addslashes($paragraph) }}', 'linkify')"
                                          @click="editMode && startEdit()"
                                          :class="editMode ? 'editable' : ''">{!! $linkify($paragraph) !!}</span>
                                </p>
                            @endforeach
                        </div>
                    @endif

                    {{-- 879 reference order: SHARE PRICING, then MORE ABOUT
                         THE FUND, then PERFORMANCE FEES / PERFORMANCE FEE
                         EXAMPLES last, immediately above the footer divider.
                         878's clone had fees first; that arrangement is
                         wrong for 879 and has been reordered to match. --}}

                    <!-- Performance Fees -->
                    @if(isset($fund->data['page2Content']['performanceFees']))
                        <div class="page2-section">
                            <h3 class="page2-heading">
                                <span x-data="editableField('page2Content.performanceFees.title', '{{ $fund->data['page2Content']['performanceFees']['title'] }}')"
                                      @click="editMode && startEdit()"
                                      :class="editMode ? 'editable' : ''"
                                      x-text="value"></span>
                            </h3>
                            @foreach ($fund->data['page2Content']['performanceFees']['paragraphs'] as $index => $paragraph)
                                <p class="page2-body" style="margin-bottom: 0.8mm;">
                                    <span x-data="editableField('page2Content.performanceFees.paragraphs.{{ $index }}', '{{ addslashes($paragraph) }}', 'linkify')"
                                          @click="editMode && startEdit()"
                                          :class="editMode ? 'editable' : ''">{!! $linkify($paragraph) !!}</span>
                                </p>
                            @endforeach
                        </div>
                    @endif

                    <!-- Performance Fee Examples -->
                    @if(isset($fund->data['page2Content']['performanceFeeExamples']))
                        @php $pfe = $fund->data['page2Content']['performanceFeeExamples']; @endphp
                        <div class="page2-section pfe-table">
                            <h3 class="page2-heading">
                                <span x-data="editableField('page2Content.performanceFeeExamples.title', '{{ $pfe['title'] }}')"
                                      @click="editMode && startEdit()"
                                      :class="editMode ? 'editable' : ''"
                                      x-text="value"></span>
                            </h3>
                            <div class="table-wrapper" style="margin-bottom: 0;">
                                <table class="foord-table">
                                    <thead>
                                        <tr>
                                            @foreach ($pfe['headers'] as $header)
                                                <th>{{ $header }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($pfe['rows'] as $rowIndex => $row)
                                            <tr>
                                                <td>
                                                    <span x-data="editableField('page2Content.performanceFeeExamples.rows.{{ $rowIndex }}.name', '{!! addslashes($row['name']) !!}')"
                                                          @click="editMode && startEdit()"
                                                          :class="editMode ? 'editable' : ''"
                                                          x-html="value"></span>
                                                </td>
                                                @foreach ($row['values'] as $valueIndex => $cell)
                                                    <td>
                                                        <span x-data="editableField('page2Content.performanceFeeExamples.rows.{{ $rowIndex }}.values.{{ $valueIndex }}', '{!! addslashes($cell) !!}')"
                                                              @click="editMode && startEdit()"
                                                              :class="editMode ? 'editable' : ''"
                                                              x-html="value"></span>
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @if(!empty($pfe['footnote']))
                                <p class="pfe-note">
                                    <span x-data="editableField('page2Content.performanceFeeExamples.footnote', '{!! addslashes($pfe['footnote']) !!}')"
                                          @click="editMode && startEdit()"
                                          :class="editMode ? 'editable' : ''"
                                          x-html="value"></span>
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
                            <div class="footer-contact">
                                <p>T. <span x-data="editableField('footer.contact.phone', '{{ $fund->data['footer']['contact']['phone'] }}')"
                                           @click="editMode && startEdit()"
                                           :class="editMode ? 'editable' : ''"
                                           x-text="value"></span></p>
                                {{-- The 877 footer carries the phone line only — the
                                     email/website rows print when populated. --}}
                                @if(!empty($fund->data['footer']['contact']['email']))
                                    <p>E. <span x-data="editableField('footer.contact.email', '{{ $fund->data['footer']['contact']['email'] }}')"
                                               @click="editMode && startEdit()"
                                               :class="editMode ? 'editable' : ''"
                                               x-text="value"></span></p>
                                @endif
                                @if(!empty($fund->data['footer']['contact']['website']))
                                    <p><span x-data="editableField('footer.contact.website', '{{ $fund->data['footer']['contact']['website'] }}')"
                                             @click="editMode && startEdit()"
                                             :class="editMode ? 'editable' : ''"
                                             x-text="value"></span></p>
                                @endif
                                {{-- Red Foord acorn leaf — same asset as the signed-off
                                     balanced/flexible templates --}}
                                <img src="{{ asset('images/leaf.png') }}" alt="" class="footer-leaf">
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
        // Display formatters — keep the styled rendering after Alpine
        // re-renders an edited value.
        const editableFormatters = {
            // The 879 banner prints the class suffix in a lighter, smaller
            // treatment. Alpine rewrites this span's innerHTML on init, so the
            // nested markup has to be rebuilt here or it is lost before the
            // PDF is captured.
            fundNameWithClass(value) {
                const m = String(value).match(/^(.+?)\s*[—–-]\s*(CLASS\s+[A-Z][0-9]*)$/i);
                if (!m) {
                    return String(value).toUpperCase();
                }
                return m[1].toUpperCase()
                    + '<span class="class-suffix"> — ' + m[2].toUpperCase() + '</span>';
            },
            headingSuffix(value) {
                return String(value)
                    .replace(/\s*\(([^)]+)\)/, ' <span class="title-suffix">($1)</span>')
                    .replace(/¹/g, '<sup>1</sup>').replace(/²/g, '<sup>2</sup>')
                    .replace(/³/g, '<sup>3</sup>').replace(/⁴/g, '<sup>4</sup>')
                    .replace(/⁵/g, '<sup>5</sup>').replace(/⁶/g, '<sup>6</sup>')
                    .replace(/⁷/g, '<sup>7</sup>').replace(/⁸/g, '<sup>8</sup>');
            },
            // Reference: URLs and email addresses render naartjie
            linkify(value) {
                return String(value)
                    .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                    .replace(/((?:www\.|https?:\/\/)[^\s,)]+|[\w.+-]+@[\w.-]+\.\w+)/g,
                        '<span class="ref-link">$1</span>');
            }
        };
        function editableField(fieldPath, initialValue, formatter) {
            return {
                fieldPath: fieldPath,
                value: initialValue,
                originalValue: initialValue,
                formatter: formatter || null,
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
                updateDisplay() {
                    if (this.editing) return;
                    const fmt = this.formatter && editableFormatters[this.formatter];
                    this.$el.innerHTML = fmt ? fmt(this.value) : this.value;
                },
                init() { this.updateDisplay(); }
            }
        }
    </script>

    @if(isset($fund->data['mainContent']['charts']) || !empty($fund->data['mainContent']['assetAllocation']['geographicCountryExposure']))
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const colors = {
            naartjie: '#d25347',
            darkNavy: '#29363d',
            lightBlue: '#7a9cb4'
        };

        const monthNames = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        const formatChartDate = (label) => {
            const m = String(label).match(/^(\d{4})-(\d{2})$/);
            if (!m) return label;
            return monthNames[parseInt(m[2], 10) - 1] + ' ' + m[1].slice(-2);
        };

        @if(!empty($fund->data['mainContent']['assetAllocation']['geographicCountryExposure']))
        // GEOGRAPHIC COUNTRY EXPOSURE pie. Geometry measured off the 879
        // reference: the pie starts at 12 o'clock and runs clockwise in feed
        // rank order (the feed already ranks descending with the "Other"
        // catch-all last), and the palette is positional, not per-country.
        const countryData = @json($fund->data['mainContent']['assetAllocation']['geographicCountryExposure']);
        const sliceColours = ['#d25347', '#29363d', '#cccccc', '#7a9cb4', '#535353', '#e2cea4', '#bfc3c5'];

        // Chart.js has no outside labels; the reference draws "Name %" in
        // grey, radially outside each slice, with no leader lines. Long
        // labels ("Taiwan, Province of China 10.5%", "United States of
        // America 7.2%") wrap onto two lines, matched to the reference's
        // wrap points.
        const pieLabelPlugin = {
            id: 'pieOutsideLabels',
            afterDraw(chart) {
                const { ctx } = chart;
                const meta = chart.getDatasetMeta(0);
                ctx.save();
                ctx.font = '7.2px Avenir Next, Lato, sans-serif';
                ctx.fillStyle = '#535353';

                // Task 10: measured against the 879 reference. A single
                // width threshold reproduces the two genuinely long
                // labels' wrap points exactly ("Taiwan, Province of
                // China…", "United States of America…") but the
                // reference also wraps two SHORTER labels ("Other
                // 14.3%", "Hong Kong 4.9%" onto 3 lines) that fit on one
                // line by width alone — Publisher's outside-label
                // boxes were hand-placed per label, not wrapped by a
                // formula, so no single width value reproduces every
                // line break. 62px is kept as the best-fit compromise;
                // see FUND-ONBOARDING §5s.
                const LABEL_MAX_WIDTH = 62;
                const LINE_HEIGHT = 8.6;

                const wrap = (text) => {
                    const words = text.split(' ');
                    const lines = [];
                    let line = '';
                    words.forEach(word => {
                        const candidate = line ? line + ' ' + word : word;
                        if (line && ctx.measureText(candidate).width > LABEL_MAX_WIDTH) {
                            lines.push(line);
                            line = word;
                        } else {
                            line = candidate;
                        }
                    });
                    if (line) lines.push(line);
                    return lines;
                };

                meta.data.forEach((arc, i) => {
                    const mid = (arc.startAngle + arc.endAngle) / 2;
                    const r = arc.outerRadius + 12;
                    const x = arc.x + Math.cos(mid) * r;
                    const y = arc.y + Math.sin(mid) * r;
                    ctx.textAlign = Math.cos(mid) < -0.05 ? 'right' : (Math.cos(mid) > 0.05 ? 'left' : 'center');
                    ctx.textBaseline = 'middle';
                    const lines = wrap(countryData[i].name + ' ' + Number(countryData[i].value).toFixed(1) + '%');
                    lines.forEach((lineText, li) => {
                        ctx.fillText(lineText, x, y + (li - (lines.length - 1) / 2) * LINE_HEIGHT);
                    });
                });
                ctx.restore();
            }
        };

        new Chart(document.getElementById('geoPieChart').getContext('2d'), {
            type: 'pie',
            plugins: [pieLabelPlugin],
            data: {
                labels: countryData.map(d => d.name),
                datasets: [{
                    data: countryData.map(d => d.value),
                    backgroundColor: countryData.map((d, i) => sliceColours[i % sliceColours.length]),
                    borderColor: '#fff',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: false,
                // Chart.js pie/doughnut charts already start at 12 o'clock
                // and draw clockwise by default — no rotation needed.
                rotation: 0,
                plugins: { legend: { display: false }, tooltip: { enabled: false } },
                // Vertical padding stays small so the radius isn't starved;
                // horizontal padding is wider to leave room for the outside
                // labels (measured against the reference's ⌀35.4mm pie).
                // The left/right split is asymmetric, not centred: the
                // left-hand labels ("Singapore 5.4%", "Hong Kong 4.5%",
                // "Taiwan, Province of China 9.7%") run longer than the
                // one right-hand label ("China 44.3%"), and at 58/58 the
                // 879 reference's Singapore label clipped against the
                // canvas edge. left+right is kept at the same total (116)
                // so availableWidth — the dimension that actually sets the
                // pie's radius here — is unchanged and the diameter doesn't
                // shrink; only the split moves, shifting the whole chart
                // area 20px right for label room.
                //
                // Task 10 measurement: flood-filling each slice colour at
                // 200dpi (China/red gives the disk's top+right extremes,
                // US/navy the bottom, Korea/blue-grey the left — their
                // angular spans each cross that cardinal point) gives a
                // reference disk of 280x279px = 35.5x35.4mm, and our own
                // render at the same settings comes out 281x281px =
                // 35.7mm — a sub-pixel, sub-0.3mm difference. The pie
                // matches; left/right/top/bottom padding above is
                // unchanged.
                layout: { padding: { top: 16, bottom: 16, left: 68, right: 59 } }
            }
        });
        @endif

        @if(isset($fund->data['mainContent']['charts']))
        const chartData = @json($fund->data['mainContent']['charts']['performanceData'] ?? []);

        // End value annotation plugin — the label sits just right of, and
        // level with, each series' last point ($ 152 / $ 132 / $ 130 for
        // 879 Class R). Two series can end close together (the reference's
        // own $132/$127 pair is only a few index points apart), so instead
        // of drawing at each point's raw y, the candidate positions are
        // collected first, sorted top-to-bottom, and any pair closer than a
        // label's line-height is pushed apart — never a value hard-coded
        // for this month's figures, since next month's will differ.
        const endValuePlugin = {
            id: 'endValueAnnotation',
            afterDraw(chart) {
                const { ctx } = chart;
                const MIN_LABEL_GAP = 8.4; // px, ~ one line-height at 7.9px font

                const entries = chart.data.datasets.map((dataset, i) => {
                    const meta = chart.getDatasetMeta(i);
                    if (meta.hidden) return null;
                    const lastPoint = meta.data[meta.data.length - 1];
                    if (!lastPoint) return null;
                    const lastValue = dataset.data[dataset.data.length - 1];
                    return {
                        label: '$ ' + Math.round(lastValue).toLocaleString(),
                        color: dataset.borderColor,
                        x: lastPoint.x,
                        y: lastPoint.y - 1.5
                    };
                }).filter(Boolean);

                entries.sort((a, b) => a.y - b.y);
                for (let i = 1; i < entries.length; i++) {
                    const gap = entries[i].y - entries[i - 1].y;
                    if (gap < MIN_LABEL_GAP) {
                        entries[i].y = entries[i - 1].y + MIN_LABEL_GAP;
                    }
                }

                ctx.save();
                ctx.font = '7.9px Avenir Next, Lato, sans-serif';
                ctx.textAlign = 'left';
                ctx.textBaseline = 'middle';
                entries.forEach(entry => {
                    ctx.fillStyle = entry.color;
                    ctx.fillText(entry.label, entry.x + 8.6, entry.y);
                });
                ctx.restore();
            }
        };

        // The reference draws no axis box: the only horizontal rule is the
        // 100 baseline (which every series dips below), and the vertical
        // rule stops where it meets that baseline. Both are drawn here, with
        // the "100" label set above the line as the reference places it.
        const baselinePlugin = {
            id: 'hundredBaseline',
            afterDraw(chart) {
                const { ctx, chartArea, scales } = chart;
                const yHundred = scales.y.getPixelForValue(100);
                ctx.save();
                ctx.strokeStyle = '#000';
                ctx.lineWidth = 1.2;
                ctx.beginPath();
                ctx.moveTo(chartArea.left, yHundred);
                ctx.lineTo(chartArea.right, yHundred);
                ctx.moveTo(chartArea.left, chartArea.top);
                ctx.lineTo(chartArea.left, yHundred);
                ctx.stroke();
                ctx.font = '7.9px Avenir Next, Lato, sans-serif';
                ctx.fillStyle = '#535353';
                ctx.textAlign = 'right';
                ctx.textBaseline = 'middle';
                ctx.fillText('100', chartArea.left - 8.7, yHundred - 11.9);
                ctx.restore();
            }
        };

        // Three series (879 reference): Fund red, the MSCI Asia ex-Japan
        // benchmark dark navy, the Morningstar peer group steel blue. The
        // end-value plugin attaches to this chart only (a global register
        // would annotate the geographic pie too).
        // Scale bounds per the reference: the plot clears the highest series
        // by 4 index points and drops 6.6 below the lowest, which puts the
        // 100 baseline ~2/3 of the way down the plot.
        const perfValues = chartData.flatMap(d => [d.fund, d.benchmark, d.peerGroup]).filter(v => typeof v === 'number');
        const perfMax = Math.max(...perfValues) + 4.3;
        const perfMin = Math.min(...perfValues) - 4.0;

        new Chart(document.getElementById('performanceChart').getContext('2d'), {
            type: 'line',
            plugins: [endValuePlugin, baselinePlugin],
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
                        label: 'MSCI Asia ex-Japan USD',
                        data: chartData.map(d => d.benchmark),
                        borderColor: colors.darkNavy,
                        borderWidth: 1.5,
                        pointRadius: 0,
                        tension: 0.3,
                        fill: false
                    },
                    {
                        label: 'Peer Group',
                        data: chartData.map(d => d.peerGroup),
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
                        border: { display: false },
                        ticks: {
                            font: { size: 7.9, family: 'Avenir Next, Lato, sans-serif' },
                            color: '#535353',
                            maxRotation: 0,
                            autoSkip: false,
                            padding: 3,
                            // Reference ticks: Jul 21, Jul 22, … Jul 26 — every
                            // 12 months from the series' opening month (879
                            // opens at Jul 2021, index 0 — unlike 878, whose
                            // series opens at Dec 2020 and so ticks off index 1).
                            callback: function (value, index) {
                                return index % 12 === 0 ? formatChartDate(this.getLabelForValue(value)) : null;
                            }
                        }
                    },
                    y: {
                        display: true,
                        // Linear scale, confirmed against the 879 reference
                        // raster: fitting the two known end values linearly
                        // puts the 100 baseline 0.9px out and the benchmark
                        // endpoint 4.9px out, where a log fit misses the
                        // endpoint by 19.7px. (877 is logarithmic; the
                        // sibling's scale type is never assumed.) Gridlines,
                        // ticks and the axis border are all drawn by
                        // baselinePlugin instead.
                        grid: { display: false },
                        border: { display: false },
                        ticks: { display: false },
                        min: perfMin,
                        max: perfMax
                    }
                },
                layout: {
                    padding: { right: 34, top: 4.2, left: 23.34 }
                }
            }
        });
        @endif
    </script>
    @endif
</body>
</html>
