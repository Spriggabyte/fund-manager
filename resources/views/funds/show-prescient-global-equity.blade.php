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
           PRESCIENT FOORD GLOBAL EQUITY FEEDER FUND (823)
           Cloned from the Prescient international feeder sheet
           (822), which supplies the Prescient chrome unchanged:
           naartjie date badge, no class suffix in the banner, the
           MINIMUM DISCLOSURE DOCUMENT sidebar (same row set, down
           to RETURNS IN US$ and ISIN NUMBER), page 2's
           CONTRIBUTORS/POLICY OBJECTIVE/FEE RATES/TIC stack with
           no footer, and page 3's CONTACT DETAILS + GLOSSARY.

           Page 1's content column is the master fund's (877,
           show-global-equity), because 823 feeds Foord Global
           Equity and reports the same numbers. Deltas from 822,
           per the signed-off July 2026 reference in
           Funds/823 …/Design/:
             - title banner is DARK NAVY, not naartjie (the badge
               stays naartjie) — the one chrome difference
             - PORTFOLIO STRUCTURE % replaces the asset-allocation
               and equity-sector bars: one full-width list of bars
               carrying the change arrow AND a variance-to-MSCI-ACWI
               column (877's .ps-* block)
             - TOP 10 INVESTMENTS moves ABOVE the charts
             - GEOGRAPHIC EQUITY EXPOSURE is a grouped Fund vs MSCI
               ACWI column chart (877's #geoChart), not 822's table
             - ILLUSTRATIVE PERFORMANCE carries TWO series (Fund,
               Benchmark), ticks every 9 months from the Feb 2022
               inception as on 822
             - performance table: SINCE INCEPTION is wider than the
               other period columns and the sixth reads LAST 6
               MONTHS; rows are Fund / Benchmark / Peer group
             - page 2 opens with an ASSET ALLOCATION % table (the
               823 export carries no allocation keys — it is seeded
               static and needs a manual monthly update)
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
           875 reference: the date badge is dark navy.
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
            /* 822 reference: naartjie badge (the 875/809 sheets use navy) */
            background-color: var(--naartjie);
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
           TITLE BANNER — 34mm tall, text inset 7.75mm. The 823
           reference paints it DARK NAVY (#29363d); 822's is
           naartjie. The date badge stays naartjie on both.
           ===================================================== */
        .fund-banner {
            background-color: var(--dark-navy);
            color: var(--white);
            height: 34mm;
            box-sizing: border-box;
            padding: 3.6mm 3mm 0 7.75mm;
            margin: 0;
            width: 100%;
        }

        /* 822 reference: the title is one line spanning x 7.5mm → 205.3mm —
           the 809 sheet's 23pt wraps "FUND" onto a second line. */
        .fund-banner h1 {
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 500;
            font-size: 22.5pt;
            letter-spacing: 0.01em;
            text-transform: uppercase;
            margin: 0 0 2.9mm 0;
            line-height: 1.05;
        }

        .fund-banner .description {
            font-family: 'Merriweather', Georgia, serif;
            font-weight: 400;
            font-size: 9pt;
            line-height: 11.3pt;
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
            padding: 5.7mm 4mm 4mm 8mm;
            overflow: hidden;
        }

        .sidebar-section { margin-bottom: 1.15mm; }
        .sidebar-section:last-child { margin-bottom: 0; }

        /* 822 reference: the MINIMUM DISCLOSURE DOCUMENT heading opens the
           sidebar on two lines with a clear gap before the CLASS row. */
        .mdd-heading { margin-bottom: 2.6mm; }

        /* Feeder sidebar carries far more copy than the international page
           (distributions, orientation, restrictions, US$ note …) — the
           published 809 sheet sets it ~7pt on a tight leading to fit. */
        /* 822 reference: the sidebar labels are markedly smaller than the
           809 sheet's while the body copy matches — measured 0.83x. */
        .sidebar-section h3 {
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 500;
            font-size: 5.85pt;
            line-height: 8.2pt;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            color: #000;
            margin: 0;
        }

        .sidebar-section p,
        .sidebar-section .sidebar-value {
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 400;
            font-size: 7pt;
            line-height: 8.2pt;
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
            /* 823 reference (pdftotext -bbox / raster): the table cells span
               x=64.35mm → 203.37mm, so the content box is padded to those
               edges and the headings and bar labels are nudged back in by
               their own padding (they sit at x≈64.95 and 65.15). The right
               chart column starts at x=137.3mm. */
            padding: 4.46mm 5.85mm 4mm 4.35mm;
            min-width: 0;
            overflow: hidden;
        }

        /* === Section headings — dark navy. Page 1 only (pages 2 and 3
           use .page2-heading). Indented 0.6mm so they land at x=64.95mm
           while the tables below them start flush at 64.35mm. === */
        .section-heading {
            padding-left: 0.6mm;
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 500;
            font-size: 7.3pt;
            line-height: 8.8pt;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            color: var(--dark-navy);
            margin: 0 0 1.44mm 0;
        }

        /* 875 reference: bracketed qualifiers keep their mixed case
           ("(Effective exposure)") and render at the same size as the
           heading itself. */
        .section-heading .title-suffix {
            font-size: inherit;
            font-weight: 500;
            color: var(--dark-navy);
            text-transform: none;
            letter-spacing: 0.01em;
        }

        .section-subtitle {
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 500;
            font-size: 7.4pt;
            line-height: 8.9pt;
            letter-spacing: 0.01em;
            color: var(--dark-navy);
            margin: -0.5mm 0 2.1mm 0;
        }

        /* === Two-column layout === */
        .two-col {
            display: flex;
            gap: 5.1mm;
            margin-bottom: 3.96mm;
        }

        .two-col .col-left { flex: 1; min-width: 0; }
        .two-col .col-right { flex: 1; min-width: 0; }

        /* =====================================================
           PORTFOLIO STRUCTURE % — one full-width list of sector
           bars carrying the change arrow AND the variance-to-
           benchmark column (877's block; 822 has no equivalent).
           Measured off the 823 reference: labels at x=65.2mm,
           bars 97.7mm→127.3mm, the value right-aligned at
           135.5mm, the arrow at 160.0mm, the change right-aligned
           at 168.5mm and the variance at 202.8mm; rows on a 4.0mm
           pitch with 2.4mm bars.
           ===================================================== */
        .ps-section { margin-bottom: 3.22mm; }

        .ps-header {
            display: flex;
            align-items: flex-start;
            margin-bottom: 0.6mm;
            padding-left: 0.8mm;
        }

        .ps-header .ps-header-title { flex: 1; }

        .ps-header .ps-header-col {
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 400;
            font-size: 7.5pt;
            line-height: 3.7mm;
            letter-spacing: 0.01em;
            color: var(--dark-navy);
            text-align: right;
        }

        .ps-header .ps-header-col sup {
            font-size: 5pt;
            line-height: 0;
            vertical-align: baseline;
            position: relative;
            top: -0.35em;
        }

        /* The two header columns right-align on the change and variance
           columns below them (168.8mm and 202.0mm on the reference). */
        .ps-header .ps-header-change { width: 34.6mm; }
        .ps-header .ps-header-variance { width: 33.6mm; }

        .ps-row {
            display: flex;
            align-items: center;
            padding-left: 0.8mm;
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-size: 7.5pt;
            line-height: 4mm;
            color: #000;
        }

        .ps-label {
            width: 32.5mm;
            text-align: left;
            padding-right: 1mm;
            flex-shrink: 0;
            font-weight: 400;
        }

        /* Bars run 97.7mm → 127.3mm at full scale: a 29.6mm span on a
           32.1mm container, i.e. the longest bar reaches 92%. */
        .ps-bar-container {
            flex: 0 0 32.1mm;
            height: 2.4mm;
            position: relative;
        }

        .ps-bar {
            height: 2.4mm;
            background-color: var(--naartjie);
        }

        /* 823 reference: the Cash bar alone renders dark navy. */
        .ps-bar.navy { background-color: var(--dark-navy); }

        .ps-value {
            width: 3.4mm;
            text-align: right;
            flex-shrink: 0;
            font-weight: 400;
        }

        .ps-change {
            flex: 1;
            text-align: right;
            padding-right: 0.3mm;
        }

        .ps-variance {
            width: 33.6mm;
            text-align: right;
            flex-shrink: 0;
        }

        /* Reference arrows: black ▲ for up, steel-blue ▼ for down; the number
           stays black. Zero changes carry no arrow. The arrow sits 8.5mm left
           of the change value, so it is padded rather than butted against it. */
        .change-up { color: #000; }
        .change-down { color: #000; }
        .change-up::before { content: '▲'; font-size: 5.1pt; color: #000; margin-right: 6.4mm; }
        .change-down::before { content: '▼'; font-size: 5.1pt; color: var(--light-blue); margin-right: 6.4mm; }

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
            margin-right: -1.1pt;
            font-size: 7.5pt;
        }

        .foord-table th {
            background-color: var(--dark-navy);
            color: var(--white);
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 500;
            font-size: 7.5pt;
            line-height: 8.5pt;
            letter-spacing: 0;
            text-transform: uppercase;
            text-align: right;
            padding: 0.6mm 1.4mm 0.6mm 0.45mm;
        }

        .foord-table th:first-child { text-align: left; }

        .foord-table td {
            background-color: var(--row-grey-2);
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 400;
            font-size: 7.5pt;
            line-height: 8.5pt;
            padding: 0.62mm 1.4mm 0.62mm 0.45mm;
            text-align: right;
            overflow: hidden;
        }

        .foord-table td:first-child { text-align: left; }

        /* Superscript markers sit tight against the label at a modest
           raise (875 reference). vertical-align stays baseline and the
           raise is done with a relative offset so the Tailwind preflight
           (top: -0.5em) cannot over-raise them. */
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

        /* GEOGRAPHIC EQUITY EXPOSURE — the 823 sheet draws a grouped
           Fund vs MSCI ACWI column chart here (822 prints a table). Bars
           measured at 3.73mm wide on a 13.72mm group pitch, 0-70% axis. */
        .geo-chart-wrapper {
            height: 40.46mm;
            position: relative;
            margin-top: 2.14mm;
        }

        /* Top 10 — the 823 reference splits the 139.0mm table into
           SECURITY 41.5mm, SECTOR 41.8mm, MARKET 27.3mm and % OF FUND
           26.9mm; the last two are centred. Row backgrounds fade in
           pairs. Pitch 4.15mm. */
        .top10-table .foord-table td,
        .top10-table .foord-table th {
            padding-top: 0.44mm;
            padding-bottom: 0.44mm;
        }
        .top10-table .foord-table td:first-child,
        .top10-table .foord-table th:first-child {
            width: 29.67%;
            padding-left: 1.6mm;
        }
        .top10-table .foord-table td:nth-child(2),
        .top10-table .foord-table th:nth-child(2) {
            text-align: left;
            width: 29.92%;
            padding-left: 1.9mm;
        }
        .top10-table .foord-table td:nth-child(3),
        .top10-table .foord-table th:nth-child(3) {
            text-align: center;
            width: 19.51%;
            padding-left: 0.6mm;
        }
        .top10-table .foord-table td:nth-child(4),
        .top10-table .foord-table th:nth-child(4) {
            text-align: center;
            padding-left: 0.6mm;
        }
        /* Row-grey ramp sampled off the 823 reference: rows 1-4 #dddddd,
           5-6 #e6e6e6, 7-8 #ebebeb, 9-10 #f0f0f0 — one step lighter
           throughout than the 822/875 sheets, which open on #d4d4d4. */
        .top10-table .foord-table tbody tr:nth-child(1) td,
        .top10-table .foord-table tbody tr:nth-child(2) td,
        .top10-table .foord-table tbody tr:nth-child(3) td,
        .top10-table .foord-table tbody tr:nth-child(4) td { background-color: var(--row-grey-1); }
        .top10-table .foord-table tbody tr:nth-child(5) td,
        .top10-table .foord-table tbody tr:nth-child(6) td { background-color: var(--row-grey-2); }
        .top10-table .foord-table tbody tr:nth-child(7) td,
        .top10-table .foord-table tbody tr:nth-child(8) td { background-color: var(--row-grey-3); }
        .top10-table .foord-table tbody tr:nth-child(9) td,
        .top10-table .foord-table tbody tr:nth-child(10) td { background-color: var(--row-grey-4); }

        /* The footnotes sit 5.2mm under the last performance row on the
           reference; the shared .table-wrapper gutter is 1.2mm too generous. */
        .perf-table-wrapper { margin-bottom: 1.4mm; }

        /* Reference: the performance heading sits 5.8mm under the top-10 table */
        .top10-table { margin-bottom: 3.14mm; }

        /* Performance table — seven columns (name, cash value, since
           inception, 3 yrs, 1 yr, LAST 6 months, year to date). Unlike the
           822 sheet's uniform period columns, the 823 reference gives SINCE
           INCEPTION extra width: measured cell spans on the 139.0mm table
           are 36.4 / 20.8 / 21.7 / 14.2 / 14.2 / 14.4 / 14.1mm. */
        .perf-table th {
            font-size: 7pt;
            line-height: 8.7pt;
            text-align: right;
            padding: 0.35mm 0.5mm;
            vertical-align: bottom;
        }
        .perf-table th:first-child {
            text-align: left;
            width: 26.08%;
            padding-left: 1.1mm;
        }
        .perf-table th:nth-child(2) { width: 14.99%; }
        .perf-table th:nth-child(3) { width: 15.48%; }
        .perf-table th:nth-child(4) { width: 10.37%; }
        .perf-table th:nth-child(5) { width: 10.37%; }
        .perf-table th:nth-child(6) { width: 10.49%; }
        .perf-table th:nth-child(7) { width: 10.25%; }
        .perf-table td {
            color: #000;
            font-size: 7.5pt;
            line-height: 8pt;
            padding: 0.46mm 0.5mm;
        }
        .perf-table td:first-child {
            padding-left: 1.1mm;
            white-space: nowrap;
        }
        /* Row greys fade down the table (823 reference, sampled): Fund
           pink, Benchmark and Peer group #dddddd, the spacer #e6e6e6,
           then Fund highest/lowest #ebebeb. Six rows, not the 822
           sheet's eight. */
        .perf-table tbody tr td { background-color: var(--row-grey-1); }
        .perf-table tbody tr:nth-child(1) td { background-color: var(--naartjie-20); }
        .perf-table tbody tr:nth-child(2) td,
        .perf-table tbody tr:nth-child(3) td { background-color: var(--row-grey-1); }
        .perf-table tbody tr:nth-child(4).empty-row td { background-color: var(--row-grey-2) !important; }
        .perf-table tbody tr:nth-child(5) td,
        .perf-table tbody tr:nth-child(6) td { background-color: var(--row-grey-3); }
        .perf-table tbody tr td.cell-empty { background-color: var(--white); }

        /* Fee rates — two columns, values left-aligned at the midline;
           the underlying Foord global fund row renders pink. */
        .fee-rates-table .foord-table th:first-child,
        .fee-rates-table .foord-table td:first-child { width: 50%; }
        .fee-rates-table .foord-table td {
            text-align: left;
            padding-top: 0.52mm;
            padding-bottom: 0.52mm;
        }
        .fee-rates-table .foord-table tr.global-funds-header td {
            background-color: var(--white);
            padding-left: 0;
        }
        .fee-rates-table .foord-table tr.sub-item td {
            background-color: var(--naartjie-20);
        }

        /* Total investment charge — name column 55%, values centred */
        .tic-table .foord-table th:first-child,
        .tic-table .foord-table td:first-child {
            width: 51.1%;
            padding-left: 0.55mm;
        }
        .tic-table .foord-table th:not(:first-child),
        .tic-table .foord-table td:not(:first-child) { text-align: center; }
        .tic-table .foord-table td {
            padding-top: 0.61mm;
            padding-bottom: 0.61mm;
        }
        .tic-table .foord-table tr.total-row td {
            font-weight: 500;
            padding-top: 0.64mm;
            padding-bottom: 0.64mm;
        }
        .tic-table .foord-table th {
            padding-top: 0.76mm;
            padding-bottom: 0.76mm;
        }
        /* The TER note follows 1.8mm tighter than the shared table gutter. */
        .tic-table .table-wrapper { margin-bottom: 0.8mm; }

        /* === Chart === */
        .chart-wrapper {
            height: 41.78mm;
            position: relative;
            /* The reference opens this plot 1.7mm higher than the geographic
               one beside it, tight under the heading, and drops its legend
               1mm lower. */
            margin-top: -1.7mm;
            margin-bottom: 1mm;
        }

        .chart-wrapper canvas {
            width: 100% !important;
            height: 100% !important;
        }

        .chart-ytitle {
            position: absolute;
            left: -9mm;
            top: 18mm;
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

        /* 875 reference: hairline swatches (~1px at 150 dpi) and lighter
           slate legend text. */
        .chart-legend {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 0.4mm 4.2mm;
            margin-top: 0.44mm;
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-size: 6pt;
            color: #4d585e;
        }

        .chart-legend span {
            display: flex;
            align-items: center;
            gap: 1mm;
        }

        /* Measured off the reference: the geographic legend sets 9.15mm
           between its two items and 2.27mm between swatch and label; the
           performance legend sets 12.76mm and 0.46mm. */
        /* Both legends centre on their PLOT box rather than on the column,
           so each is nudged across by twice the offset. */
        .chart-legend.geo-legend { gap: 0.4mm 9.15mm; padding-left: 6.66mm; }
        .chart-legend.geo-legend span { gap: 2.27mm; }
        .chart-legend.perf-legend { gap: 0.4mm 12.76mm; padding-right: 2.36mm; }
        .chart-legend.perf-legend span { gap: 0.46mm; }

        .legend-line {
            width: 4.24mm;
            height: 0.2mm;
            display: inline-block;
        }

        /* The geographic chart's legend uses filled squares (823 reference)
           rather than the performance chart's hairlines. */
        .legend-square {
            width: 1.5mm;
            height: 1.5mm;
            display: inline-block;
        }

        /* === Footnotes === */
        .footnote {
            font-family: 'Lato', 'Avenir Next', sans-serif;
            font-size: 6pt;
            line-height: 7.2pt;
            color: var(--dark-navy);
            letter-spacing: 0.01em;
            /* Hanging indent: wrapped lines align after the superscript.
               The 0.85mm extra left padding lands the markers at x=65.2mm,
               as measured on the reference. */
            padding-left: 2.65mm;
            text-indent: -1.8mm;
        }

        .footnote sup {
            font-size: 4.5pt;
            line-height: 0;
            vertical-align: baseline;
            position: relative;
            top: -0.3em;
        }

        /* =====================================================
           PAGE 2 — info sidebar (signed-off geometry: navy box
           45.7 x 11mm at (9.15mm, 10mm), 6.5pt Lato Light text)
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
            margin: 10mm 5.2mm 0 9.15mm;
            text-align: center;
        }

        .important-info-header h2 {
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 500;
            font-size: 8pt;
            line-height: 10.9pt;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            margin: 0;
        }

        .info-sidebar-content { padding: 6.3mm 4mm 4mm 9mm; }

        /* Feeder reference: the SA important-information column runs longer
           than the international one — 6.5pt Lato Light keeps it on the page. */
        .info-sidebar-content p,
        .important-info-text {
            font-family: 'Lato', 'Avenir Next', sans-serif;
            font-weight: 300;
            font-size: 6.5pt;
            line-height: 7.23pt;
            letter-spacing: 0.01em;
            color: var(--dark-navy);
            margin: 0 0 1.3mm 0;
            text-align: left;
        }

        /* 822 reference: unlike the Foord-branded 809 sheet, URLs and email
           addresses set in the same colour as the body copy — no gold, no
           underline. The markup still routes through `linkify` so an edited
           value re-renders identically. */
        .ref-link {
            color: inherit;
            text-decoration: none;
        }

        .info-sidebar-content p:last-child { margin-bottom: 0; }

        /* 822 reference: the closing "This document is for information
           purposes only …" disclaimer sets in grey italics, with the issue
           date returning to the body colour beneath it. */
        .important-info-text.disclaimer {
            font-style: italic;
            color: var(--dark-navy-70);
        }

        /* === Page 3 — contact details + glossary === */
        .contact-heading {
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 400;
            font-size: 6.25pt;
            line-height: 8.2pt;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            color: var(--dark-navy-70);
            margin: 0 0 5.1mm 0;
        }

        /* Reference page 3: contact lines run on a 2.75mm pitch with a
           1.45mm gap between blocks — looser than the page 2 column. */
        .contact-block { margin-bottom: 2.05mm; }
        .contact-block:last-child { margin-bottom: 0; }

        .contact-block p { margin: 0; line-height: 7.8pt; }

        .contact-block strong {
            font-weight: 700;
            color: var(--dark-navy);
        }

        /* Reference: 3.3mm line pitch, 4.65mm between entries */
        .glossary-entry {
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 400;
            font-size: 7pt;
            line-height: 9.35pt;
            letter-spacing: 0.01em;
            color: #000;
            margin: 0 0 1.35mm 0;
        }

        /* Reference page 3: the glossary column starts 2mm left of the page 2
           column and its heading sits 0.9mm higher. */
        /* Page 3's glossary column is 3mm narrower than page 2's main column
           (823 reference: its longest line ends at x=200.8mm) and starts 1.1mm
           further left, with its heading nearly flush to the text. */
        .page2-content.page3-content { padding-top: 27mm; padding-left: 3.2mm; padding-right: 9.03mm; }
        .page2-content.page3-content .page2-heading { margin-bottom: 3mm; padding-left: 0.22mm; }

        .glossary-entry strong { font-weight: 700; }

        /* === Page 2 content === */
        .page2-content {
            flex: 1;
            /* 823 reference: page 2's tables span x 64.35mm → 203.20mm,
               0.7mm left of and 1.0mm wider than the 822 sheet's. */
            padding: 27.9mm 6.08mm 4mm 4.53mm;
            min-width: 0;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .page2-section { margin-bottom: 5.6mm; }

        /* ASSET ALLOCATION % — page 2 opens with a headerless two-column
           table (823 reference; 822 has no such block). The split falls at
           the midline and the figure right-aligns at x=140.6mm, i.e. 62.4mm
           short of the table's right edge — a Publisher quirk reproduced
           with padding rather than a third column. Row pitch 4.24mm.
           NOTE: the 823 export carries no allocation keys, so these three
           rows are seeded static and need a manual monthly update. */
        .asset-alloc-table .foord-table th:first-child,
        .asset-alloc-table .foord-table td:first-child { width: 50%; }
        .asset-alloc-table .foord-table td {
            text-align: right;
            padding-top: 0.53mm;
            padding-bottom: 0.53mm;
        }
        .asset-alloc-table .foord-table td:first-child {
            text-align: left;
            padding-left: 0.73mm;
        }
        .asset-alloc-table .foord-table td:nth-child(2) { padding-right: 62.4mm; }
        .asset-alloc-table { margin-bottom: 7.93mm; }

        /* Page-2 table type runs 7% larger than on the 822 sheet. The TIC
           table is the exception: it keeps the shared 7.5pt. */
        .page2-content .foord-table td,
        .page2-content .foord-table th { font-size: 8.03pt; }
        .page2-content .foord-table td:first-child,
        .page2-content .foord-table th:first-child { padding-left: 1.14mm; }
        .tic-table .foord-table td,
        .tic-table .foord-table th { font-size: 7.5pt; }

        /* 823 reference: the contributors/detractors label column runs to
           30.8% of the main column, the names fill the rest. */
        .contributors-table td:first-child { width: 30.8%; }
        /* Reference: the names read from the left of their cell. */
        .contributors-table td:nth-child(2) { text-align: left; padding-left: 1.3mm; }
        /* Block spacing measured off the reference: contributors → policy
           17.9mm, policy → fee rates 19.1mm, fee rates → TIC 31.7mm. */
        .contributors-table { margin-bottom: 7.92mm; }
        .policy-objective-section { margin-bottom: 11.19mm; }
        .fee-rates-table { margin-bottom: 7.57mm; }
        .tic-table { margin-bottom: 12.38mm; }

        .page2-heading {
            /* Indented off the table edge, as the page-1 headings are. */
            padding-left: 0.67mm;
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 500;
            font-size: 7.3pt;
            line-height: 8.8pt;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            color: var(--dark-navy);
            margin: 0 0 1.5mm 0;
        }

        /* The 823 sheet sets page-2 prose 10% smaller than the 822 sheet
           and its tables 7% larger — both measured with `pdftotext -bbox`
           string widths, not assumed from the clone. */
        .page2-body {
            padding-left: 0.6mm;
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 400;
            font-size: 7.41pt;
            line-height: 9.13pt;
            letter-spacing: 0.01em;
            color: #000;
        }

        .page2-note {
            font-family: 'Lato', 'Avenir Next', sans-serif;
            font-weight: 400;
            font-size: 7.8pt;
            line-height: 9.6pt;
            letter-spacing: 0.01em;
            color: var(--dark-navy);
            margin: 0.3mm 0;
            /* Hanging indent: wrapped lines align after the superscript */
            padding-left: 2.2mm;
            text-indent: -2.2mm;
        }

        /* Superscripts run inline with a modest raise (875 reference).
           vertical-align stays baseline with a relative offset so the
           Tailwind preflight (top: -0.5em) cannot push them onto a
           detached visual line. */
        .page2-note sup {
            font-size: 5.5pt;
            line-height: 0;
            vertical-align: baseline;
            position: relative;
            top: -0.3em;
        }

        /* === Footer — short naartjie rule, Merriweather body,
           Avenir Next Medium contact lines, all naartjie === */
        .footer-divider {
            margin-top: auto;
            padding-top: 5.5mm;
            border-top: none;
            position: relative;
        }

        .footer-divider::after {
            content: "";
            position: absolute;
            top: 0;
            left: 0.3mm;
            width: 9.5mm;
            height: 0;
            border-top: 0.35mm solid var(--naartjie);
        }

        .footer-info {
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

            <!-- Fund Name Banner — 822 reference carries NO class suffix;
                 the class prints in the sidebar CLASS row instead. -->
            <div class="fund-banner">
                @php
                    $fundName = $fund->data['fund']['name'] ?? $fund->name;
                    // Strip any "— CLASS X" the record carries so the banner
                    // reads PRESCIENT FOORD INTERNATIONAL FEEDER FUND alone.
                    $mainName = preg_replace('/\s*[-—–]\s*CLASS\s+[A-Z][0-9]*$/iu', '', $fundName);
                @endphp
                <h1>
                    <span x-data="editableField('fund.name', '{{ addslashes($fund->data['fund']['name'] ?? $fund->name) }}', 'fundNameNoClass')"
                          @click="editMode && startEdit()"
                          :class="editMode ? 'editable' : ''">{{ mb_strtoupper($mainName) }}</span>
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
                // Bracketed qualifiers in section headings render smaller
                // ("(Effective exposure)"), matching the signed-off templates.
                $renderHeading = function (string $title): string {
                    return preg_replace(
                        '/\s*\(([^)]+)\)/',
                        ' <span class="title-suffix">($1)</span>',
                        e($title)
                    );
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
                            // Prescient feeder sidebar per the published 822
                            // fact sheets. `foreignAssets` carries the RETURNS
                            // IN US$ note — the column is unused on feeder
                            // funds and keeps editable-field saves routed to a
                            // real column.
                            $labelMap = [
                                // The reference breaks the label after DOCUMENT
                                'mddHeading' => 'MINIMUM DISCLOSURE DOCUMENT<br>AND GENERAL INVESTOR REPORT',
                                'shareClass' => 'CLASS',
                                'domicile' => 'DOMICILE',
                                'managementCompany' => 'MANAGEMENT COMPANY',
                                'fundManagers' => 'MASTER FUND MANAGERS',
                                'inceptionDate' => 'INCEPTION DATE',
                                'baseCurrency' => 'BASE CURRENCY',
                                'equityIndicator' => 'EQUITY INDICATOR',
                                'category' => 'CATEGORY',
                                'benchmark' => 'BENCHMARK',
                                'minimums' => 'MINIMUM LUMP SUM / MONTHLY',
                                'portfolioSize' => 'PORTFOLIO SIZE',
                                'unitPrice' => 'UNIT PRICE',
                                'numberOfUnits' => 'NUMBER OF UNITS',
                                'lastDistributions' => 'DISTRIBUTIONS',
                                'incomeCharacteristics' => 'INCOME CHARACTERISTICS',
                                'portfolioOrientation' => 'PORTFOLIO ORIENTATION',
                                'significantRestrictions' => 'SIGNIFICANT RESTRICTIONS',
                                'riskIndicator' => 'RISK INDICATOR',
                                'riskIndicatorDefinition' => 'RISK INDICATOR DEFINITION',
                                'timeHorizon' => 'TIME HORIZON',
                                'foreignAssets' => 'RETURNS IN US$',
                                'isinNumber' => 'ISIN NUMBER',
                            ];

                            // Display order per the 822 reference sidebar
                            $displayOrder = [
                                'mddHeading', 'shareClass', 'domicile',
                                'managementCompany', 'fundManagers',
                                'inceptionDate', 'baseCurrency', 'equityIndicator',
                                'category', 'benchmark', 'minimums',
                                'portfolioSize', 'unitPrice',
                                'numberOfUnits', 'lastDistributions',
                                'incomeCharacteristics', 'portfolioOrientation',
                                'significantRestrictions', 'riskIndicator',
                                'riskIndicatorDefinition',
                                'timeHorizon', 'foreignAssets', 'isinNumber',
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
                                            /* 823 reference: NINE of ten dots filled (822
                                               shows six). The tenth renders as a solid grey
                                               dot, not a hollow ring — as on the 822 sheet,
                                               and still awaiting the client's ruling. */
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
                                @elseif ($key === 'mddHeading')
                                    {{-- 822 reference: two-line heading, no value,
                                         with a clear gap before the CLASS row --}}
                                    <div class="sidebar-section mdd-heading">
                                        <h3>{!! $label !!}</h3>
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
                    <!-- Portfolio Structure % — one full-width list of sector
                         bars carrying the change arrow and the variance to
                         MSCI ACWI (823/877 reference; the 822 sheet splits
                         this into asset-allocation and sector bars). -->
                    @if(!empty($fund->data['mainContent']['sectorAllocation']['sectors']))
                        @php
                            $psData = $fund->data['mainContent']['sectorAllocation'];
                            $psSectors = $psData['sectors'];
                            $psMax = max(1.0, ...array_map(fn ($r) => (float) ($r['value'] ?? 0), $psSectors));
                        @endphp
                        <div class="ps-section">
                            <div class="ps-header">
                                <div class="ps-header-title">
                                    <h3 class="section-heading" style="margin-bottom: 0; padding-left: 0;">
                                        <span x-data="editableField('mainContent.sectorAllocation.title', '{{ addslashes($psData['title'] ?? 'PORTFOLIO STRUCTURE %') }}')"
                                              @click="editMode && startEdit()"
                                              :class="editMode ? 'editable' : ''"
                                              x-text="value"></span>
                                    </h3>
                                </div>
                                <div class="ps-header-col ps-header-change">
                                    <span x-data="editableField('mainContent.sectorAllocation.subtitle', '{{ addslashes($psData['subtitle'] ?? '') }}')"
                                          @click="editMode && startEdit()"
                                          :class="editMode ? 'editable' : ''"
                                          x-html="value.replace(/Change since\s*/, 'Change since<br>')"></span>
                                </div>
                                <div class="ps-header-col ps-header-variance">Variance to<br>MSCI ACWI<sup>7</sup></div>
                            </div>
                            <div>
                                @foreach ($psSectors as $rowIndex => $row)
                                    @php
                                        /* The stored change already carries the arrow glyph
                                           ('▼ 0.5') — display only the number; the arrow is
                                           drawn by the change-up/down ::before. Zero changes
                                           show no arrow (reference). */
                                        $changeNumber = trim(str_replace(['▲', '▼'], '', (string) ($row['change'] ?? '')));
                                        $isZeroChange = is_numeric($changeNumber) && (float) $changeNumber == 0.0;
                                        $changeClass = $isZeroChange ? '' : ((($row['direction'] ?? '') === 'up') ? 'change-up' : ((($row['direction'] ?? '') === 'down') ? 'change-down' : ''));
                                        /* 823 reference: the Cash bar alone renders dark navy. */
                                        $barClass = strcasecmp(trim((string) $row['name']), 'Cash') === 0 ? 'ps-bar navy' : 'ps-bar';
                                    @endphp
                                    <div class="ps-row">
                                        <span class="ps-label">
                                            <span x-data="editableField('mainContent.sectorAllocation.sectors.{{ $rowIndex }}.name', '{{ $row['name'] }}')"
                                                  @click="editMode && startEdit()"
                                                  :class="editMode ? 'editable' : ''"
                                                  x-text="value"></span>
                                        </span>
                                        <div class="ps-bar-container">
                                            <div class="{{ $barClass }}" style="width: {{ round((float) ($row['value'] ?? 0) / $psMax * 92, 1) }}%;"></div>
                                        </div>
                                        <span class="ps-value">
                                            <span x-data="editableField('mainContent.sectorAllocation.sectors.{{ $rowIndex }}.value', '{{ $row['value'] ?? '' }}')"
                                                  @click="editMode && startEdit()"
                                                  :class="editMode ? 'editable' : ''"
                                                  x-text="value"></span>
                                        </span>
                                        <span class="ps-change {{ $changeClass }}">{{ $changeNumber }}</span>
                                        <span class="ps-variance">
                                            <span x-data="editableField('mainContent.sectorAllocation.sectors.{{ $rowIndex }}.variance', '{{ $row['variance'] ?? '' }}')"
                                                  @click="editMode && startEdit()"
                                                  :class="editMode ? 'editable' : ''"
                                                  x-text="value"></span>
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Top 10 Investments Table (full width) -->
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
                                                <td>
                                                    {{-- 823 reference: the SECTOR column prints in
                                                         title case ("Health Care") while the feed and
                                                         the structure bars use sentence case. --}}
                                                    <span x-data="editableField('mainContent.topInvestments.rows.{{ $rowIndex }}.assetClass', '{{ $row['assetClass'] }}', 'top10Sector')"
                                                          @click="editMode && startEdit()"
                                                          :class="editMode ? 'editable' : ''">{{ str_replace('Healthcare', 'Health Care', ucwords($row['assetClass'])) }}</span>
                                                </td>
                                                <td>
                                                    <span x-data="editableField('mainContent.topInvestments.rows.{{ $rowIndex }}.market', '{{ $row['market'] }}')"
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

                    <!-- Two-column: Geographic Equity Exposure + Illustrative
                         Performance. Both sit BELOW the top 10 on the 823
                         sheet (the 877 master sheet puts them above it). -->
                    <div class="two-col">
                        <!-- Left: grouped Fund vs MSCI ACWI column chart -->
                        <div class="col-left">
                            @if(!empty($fund->data['mainContent']['assetAllocation']['geographicEquityExposure']))
                                <div>
                                    <h3 class="section-heading">GEOGRAPHIC EQUITY EXPOSURE</h3>
                                    <div class="geo-chart-wrapper">
                                        <canvas id="geoChart"></canvas>
                                    </div>
                                    <div class="chart-legend geo-legend">
                                        <span><span class="legend-square" style="background: var(--naartjie);"></span> Fund</span>
                                        <span><span class="legend-square" style="background: var(--dark-navy);"></span> MSCI ACWI</span>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Right: Illustrative Performance (Fund vs Benchmark) -->
                        <div class="col-right">
                            @if(isset($fund->data['mainContent']['charts']))
                                <div>
                                    <h3 class="section-heading">
                                        <span x-data="editableField('mainContent.charts.title', '{{ $fund->data['mainContent']['charts']['title'] ?? 'ILLUSTRATIVE PERFORMANCE' }}')"
                                              @click="editMode && startEdit()"
                                              :class="editMode ? 'editable' : ''"
                                              x-text="value"></span>
                                    </h3>
                                    <div class="chart-wrapper">
                                        <div class="chart-ytitle">Cash Value<sup>2</sup> (R&rsquo;000)</div>
                                        <canvas id="performanceChart"></canvas>
                                    </div>
                                    {{-- 823 reference legend: Fund red, Benchmark dark navy. --}}
                                    <div class="chart-legend perf-legend">
                                        <span><span class="legend-line" style="background: var(--naartjie);"></span> Fund</span>
                                        <span><span class="legend-line" style="background: var(--dark-navy);"></span> Benchmark</span>
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
                            <div class="table-wrapper perf-table-wrapper">
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
                                        {{-- Display rules (823 reference): the import writes raw
                                             row names (Fund, Benchmark = FOORD_COMP_1, Comparator 2
                                             = FOORD_COMP_2, Fund highest/lowest); the fact sheet
                                             renames them with footnote superscripts and orders them
                                             Fund / Benchmark / Peer group, spacer, highest/lowest.
                                             Unlike the 822 sheet, Benchmark here is the MSCI ACWI
                                             index and the peer group is the second comparator. --}}
                                        @php
                                            $perfRowsRaw = $fund->data['mainContent']['performanceTable']['rows'];
                                            $perfColKeysIntl = $fund->data['mainContent']['performanceTable']['columnKeys'] ?? [];
                                            $intlNames = [
                                                'fund' => 'Fund <sup>3</sup>',
                                                'benchmark' => 'Benchmark <sup>4</sup>',
                                                'comparator 2' => 'Peer group <sup>3,5</sup>',
                                                'fund highest' => 'Fund highest <sup>3,6</sup>',
                                                'fund lowest' => 'Fund lowest <sup>3,6</sup>',
                                            ];
                                            $intlOrder = [
                                                ['fund', 'benchmark', 'comparator 2'],
                                                ['fund highest', 'fund lowest'],
                                            ];
                                            $rowsByKey = [];
                                            foreach ($perfRowsRaw as $i => $r) {
                                                $rowsByKey[strtolower(trim(strip_tags((string)($r['name'] ?? ''))))] = [$i, $r];
                                            }
                                        @endphp
                                        @foreach ($intlOrder as $groupIndex => $group)
                                            @if ($groupIndex > 0)
                                                <tr class="empty-row"><td colspan="{{ count($fund->data['mainContent']['performanceTable']['headers']) }}"></td></tr>
                                            @endif
                                            @foreach ($group as $rowKey)
                                                @continue(!isset($rowsByKey[$rowKey]))
                                                @php [$rowIndex, $row] = $rowsByKey[$rowKey]; @endphp
                                                <tr class="{{ $rowKey === 'fund' ? 'highlight-row' : '' }}">
                                                    <td>{!! $intlNames[$rowKey] !!}</td>
                                                    @foreach ($perfColKeysIntl as $colKey)
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

                            {{-- Feeder reference: footnotes sit at the foot of
                                 page 1, directly below the performance table. --}}
                            @if(!empty($fund->data['mainContent']['performanceTable']['footnotes']))
                                <div style="margin-top: 0.8mm;">
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
                            @php
                                // 822 reference: the closing "This document is
                                // for information purposes only …" paragraph
                                // sets in grey italics.
                                $lastInfoIndex = array_key_last($fund->data['importantInfo']['paragraphs']);
                            @endphp
                            @foreach ($fund->data['importantInfo']['paragraphs'] as $index => $paragraph)
                                <p class="important-info-text {{ $index === $lastInfoIndex ? 'disclaimer' : '' }}">
                                    <span x-data="editableField('importantInfo.paragraphs.{{ $index }}', '{{ addslashes($paragraph) }}', 'linkify')"
                                          @click="editMode && startEdit()"
                                          :class="editMode ? 'editable' : ''">{!! $linkify($paragraph) !!}</span>
                                </p>
                            @endforeach
                            <p class="important-info-text" style="margin-top: 2.5mm;">
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
                    <!-- Asset Allocation % — static (no feed keys for 823) -->
                    @if(!empty($fund->data['page2Content']['assetAllocation']['rows']))
                        <div class="page2-section asset-alloc-table">
                            <h3 class="page2-heading">
                                <span x-data="editableField('page2Content.assetAllocation.title', '{{ $fund->data['page2Content']['assetAllocation']['title'] ?? 'ASSET ALLOCATION %' }}')"
                                      @click="editMode && startEdit()"
                                      :class="editMode ? 'editable' : ''"
                                      x-text="value"></span>
                            </h3>
                            <div class="table-wrapper">
                                <table class="foord-table">
                                    <tbody>
                                        @foreach ($fund->data['page2Content']['assetAllocation']['rows'] as $rowIndex => $row)
                                            <tr>
                                                <td>
                                                    <span x-data="editableField('page2Content.assetAllocation.rows.{{ $rowIndex }}.name', '{{ addslashes($row['name'] ?? '') }}')"
                                                          @click="editMode && startEdit()"
                                                          :class="editMode ? 'editable' : ''"
                                                          x-text="value"></span>
                                                </td>
                                                <td>
                                                    <span x-data="editableField('page2Content.assetAllocation.rows.{{ $rowIndex }}.value', '{{ addslashes((string) ($row['value'] ?? '')) }}')"
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

                    <!-- Contributors / Detractors -->
                    @if(!empty($fund->data['page2Content']['contributorsDetractors']['rows']))
                        <div class="page2-section contributors-table">
                            <h3 class="page2-heading">
                                <span x-data="editableField('page2Content.contributorsDetractors.title', '{{ $fund->data['page2Content']['contributorsDetractors']['title'] ?? 'CONTRIBUTORS/DETRACTORS' }}')"
                                      @click="editMode && startEdit()"
                                      :class="editMode ? 'editable' : ''"
                                      x-text="value"></span>
                            </h3>
                            <div class="table-wrapper">
                                <table class="foord-table">
                                    <tbody>
                                        @foreach ($fund->data['page2Content']['contributorsDetractors']['rows'] as $rowIndex => $row)
                                            <tr>
                                                <td>{{ $row['name'] ?? '' }}</td>
                                                <td>
                                                    <span x-data="editableField('page2Content.contributorsDetractors.rows.{{ $rowIndex }}.value', '{{ addslashes($row['value'] ?? '') }}')"
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

                    <!-- Policy Objective -->
                    @if(!empty($fund->data['page2Content']['policyObjective']))
                        <div class="page2-section policy-objective-section">
                            <h3 class="page2-heading">
                                <span x-data="editableField('page2Content.policyObjective.title', '{{ $fund->data['page2Content']['policyObjective']['title'] ?? 'POLICY OBJECTIVE' }}')"
                                      @click="editMode && startEdit()"
                                      :class="editMode ? 'editable' : ''"
                                      x-text="value"></span>
                            </h3>
                            <p class="page2-body">
                                <span x-data="editableField('page2Content.policyObjective.text', '{{ addslashes($fund->data['page2Content']['policyObjective']['text'] ?? '') }}')"
                                      @click="editMode && startEdit()"
                                      :class="editMode ? 'editable' : ''"
                                      x-text="value"></span>
                            </p>
                        </div>
                    @endif

                    <!-- Fee Rates -->
                    @if(isset($fund->data['fees']['feeRates']))
                        <div class="page2-section fee-rates-table">
                            <h3 class="page2-heading">
                                <span x-data="editableField('fees.feeRates.title', '{{ $fund->data['fees']['feeRates']['title'] ?? 'FEE RATES' }}')"
                                      @click="editMode && startEdit()"
                                      :class="editMode ? 'editable' : ''"
                                      x-text="value"></span>
                            </h3>
                            <div class="table-wrapper">
                                <table class="foord-table">
                                    <tbody>
                                        @foreach ($fund->data['fees']['feeRates']['rates'] as $rowIndex => $rate)
                                            <tr>
                                                <td>
                                                    <span x-data="editableField('fees.feeRates.rates.{{ $rowIndex }}.name', '{{ addslashes($rate['name']) }}')"
                                                          @click="editMode && startEdit()"
                                                          :class="editMode ? 'editable' : ''"
                                                          x-text="value"></span>
                                                </td>
                                                <td>
                                                    <span x-data="editableField('fees.feeRates.rates.{{ $rowIndex }}.value', '{{ addslashes($rate['value']) }}')"
                                                          @click="editMode && startEdit()"
                                                          :class="editMode ? 'editable' : ''"
                                                          x-text="value"></span>
                                                </td>
                                            </tr>
                                        @endforeach
                                        @if(isset($fund->data['fees']['feeRates']['globalFunds']))
                                            <tr class="global-funds-header">
                                                <td colspan="2">{{ $fund->data['fees']['feeRates']['globalFunds']['title'] ?? 'Foord global funds:' }}</td>
                                            </tr>
                                            @foreach ($fund->data['fees']['feeRates']['globalFunds']['funds'] as $rowIndex => $gfund)
                                                @php $gName = ltrim($gfund['name'], "- \t"); @endphp
                                                <tr class="sub-item">
                                                    <td>- {{ $gName }}</td>
                                                    <td>
                                                        <span x-data="editableField('fees.feeRates.globalFunds.funds.{{ $rowIndex }}.value', '{{ addslashes($gfund['value']) }}')"
                                                              @click="editMode && startEdit()"
                                                              :class="editMode ? 'editable' : ''"
                                                              x-text="value"></span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    <!-- Total Investment Charge -->
                    @if(isset($fund->data['fees']['totalInvestmentCharge']))
                        <div class="page2-section tic-table">
                            <h3 class="page2-heading">
                                <span x-data="editableField('fees.totalInvestmentCharge.title', '{{ $fund->data['fees']['totalInvestmentCharge']['title'] ?? 'TOTAL INVESTMENT CHARGE %' }}')"
                                      @click="editMode && startEdit()"
                                      :class="editMode ? 'editable' : ''"
                                      x-text="value"></span>
                            </h3>
                            <div class="table-wrapper">
                                <table class="foord-table">
                                    <thead>
                                        <tr>
                                            @foreach ($fund->data['fees']['totalInvestmentCharge']['headers'] ?? ['', '12 MONTHS', '36 MONTHS'] as $header)
                                                <th>{{ $header }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($fund->data['fees']['totalInvestmentCharge']['rows'] ?? [] as $rowIndex => $row)
                                            <tr>
                                                <td>{{ $row['name'] }}</td>
                                                <td>{{ $fmt($row['12m'] ?? '', 2) }}</td>
                                                <td>{{ $fmt($row['36m'] ?? '', 2) }}</td>
                                            </tr>
                                        @endforeach
                                        @if(isset($fund->data['fees']['totalInvestmentCharge']['total']))
                                            <tr class="total-row">
                                                <td>{{ $fund->data['fees']['totalInvestmentCharge']['total']['name'] ?? 'Total investment charge' }}</td>
                                                <td>{{ $fmt($fund->data['fees']['totalInvestmentCharge']['total']['12m'] ?? '', 2) }}</td>
                                                <td>{{ $fmt($fund->data['fees']['totalInvestmentCharge']['total']['36m'] ?? '', 2) }}</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                            <p class="page2-body" style="margin-top: 0.59mm;">
                                <span x-data="editableField('fees.totalInvestmentCharge.description', '{{ addslashes($fund->data['fees']['totalInvestmentCharge']['description'] ?? '') }}')"
                                      @click="editMode && startEdit()"
                                      :class="editMode ? 'editable' : ''"
                                      x-text="value"></span>
                            </p>
                        </div>
                    @endif

                    <!-- Investing Offshore -->
                    @if(isset($fund->data['page2Content']['investingOffshore']))
                        <div class="page2-section">
                            <h3 class="page2-heading">
                                <span x-data="editableField('page2Content.investingOffshore.title', '{{ $fund->data['page2Content']['investingOffshore']['title'] ?? 'INVESTING OFFSHORE' }}')"
                                      @click="editMode && startEdit()"
                                      :class="editMode ? 'editable' : ''"
                                      x-text="value"></span>
                            </h3>
                            <p class="page2-body">
                                <span x-data="editableField('page2Content.investingOffshore.text', '{{ addslashes($fund->data['page2Content']['investingOffshore']['text'] ?? '') }}')"
                                      @click="editMode && startEdit()"
                                      :class="editMode ? 'editable' : ''"
                                      x-text="value"></span>
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- ==================== PAGE 3 ==================== -->
        {{-- 822 reference: contact details beside the glossary summary. Both
             blocks are static per fund and live in `page2_content`. --}}
        @if(!empty($fund->data['page2Content']['contactDetails']) || !empty($fund->data['page2Content']['glossary']))
        <div class="page page-break">
            <div class="main-body" style="min-height: 297mm;">
                <!-- Left Sidebar - Contact Details -->
                <div class="info-sidebar">
                    {{-- The navy header box repeats from page 2 --}}
                    <div class="important-info-header">
                        <h2>{{ $fund->data['importantInfo']['title'] ?? '' }}</h2>
                    </div>
                    <div class="info-sidebar-content">
                        <h3 class="contact-heading">{{ $fund->data['page2Content']['contactDetails']['title'] ?? 'CONTACT DETAILS' }}</h3>
                        @foreach ($fund->data['page2Content']['contactDetails']['blocks'] ?? [] as $blockIndex => $block)
                            <div class="contact-block">
                                @foreach ($block as $lineIndex => $line)
                                    <p class="important-info-text">
                                        @if(!empty($line['label']))<strong>{{ $line['label'] }}:</strong> @endif{!! $linkify($line['value'] ?? '') !!}
                                    </p>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Right Content - Glossary -->
                <div class="page2-content page3-content">
                    <div class="page2-section">
                        <h3 class="page2-heading">{{ $fund->data['page2Content']['glossary']['title'] ?? 'GLOSSARY SUMMARY' }}</h3>
                        @foreach ($fund->data['page2Content']['glossary']['entries'] ?? [] as $entry)
                            <p class="glossary-entry">
                                @if(!empty($entry['term']))
                                    {{-- The reference leaves "Liquidity risk:" unbolded --}}
                                    @if(($entry['bold'] ?? true))<strong>{{ $entry['term'] }}</strong>@else{{ $entry['term'] }}@endif
                                @endif
                                {{ $entry['definition'] ?? '' }}
                            </p>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endif
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
        // Display formatters — keep the styled rendering (e.g. the smaller
        // "— CLASS R" suffix) after Alpine re-renders an edited value.
        const editableFormatters = {
            // 822 banner drops any "— CLASS X" suffix (the class prints in
            // the sidebar CLASS row).
            fundNameNoClass(value) {
                return String(value).replace(/\s*[—–-]\s*CLASS\s+[A-Z][0-9]*$/i, '').toUpperCase();
            },
            headingSuffix(value) {
                return String(value).replace(/\s*\(([^)]+)\)/, ' <span class="title-suffix">($1)</span>');
            },
            // 823 reference: the TOP 10 sector column prints in title case
            top10Sector(value) {
                return String(value)
                    .replace(/\b\w/g, c => c.toUpperCase())
                    .replace(/Healthcare/, 'Health Care');
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

    @php
        $hasPerfChart = isset($fund->data['mainContent']['charts']);
        $hasGeoChart = !empty($fund->data['mainContent']['assetAllocation']['geographicEquityExposure']);
    @endphp
    @if($hasPerfChart || $hasGeoChart)
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const colors = {
            naartjie: '#d25347',
            darkNavy: '#29363d',
            lightGrey: '#c9c9c9',
            lightBlue: '#7a9cb4'
        };

        @if($hasGeoChart)
        // Grouped GEOGRAPHIC EQUITY EXPOSURE column chart (823 reference:
        // North America, EM Asia, Europe, Pacific; Fund red, MSCI ACWI navy,
        // 0-70% axis in 10% steps).
        const geoData = @json($fund->data['mainContent']['assetAllocation']['geographicEquityExposure']);
        new Chart(document.getElementById('geoChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: geoData.map(d => d.name),
                datasets: [
                    {
                        label: 'Fund',
                        data: geoData.map(d => d.fund),
                        backgroundColor: colors.naartjie,
                        // Reference geometry: 3.73mm bars in an 8.81mm pair on
                        // a 13.72mm category pitch (measured back off the
                        // rendered output, which draws them narrower than the
                        // nominal percentages suggest).
                        barPercentage: 0.788,
                        categoryPercentage: 0.747
                    },
                    {
                        label: 'MSCI ACWI',
                        data: geoData.map(d => d.benchmark),
                        backgroundColor: colors.darkNavy,
                        barPercentage: 0.788,
                        categoryPercentage: 0.747
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { enabled: true } },
                scales: {
                    x: {
                        grid: { display: false },
                        border: { color: '#9a9a9a' },
                        ticks: {
                            font: { size: 5.5, family: 'Avenir Next, Lato, sans-serif' },
                            color: '#535353'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        max: 70,
                        grid: { display: false },
                        border: { color: '#9a9a9a' },
                        ticks: {
                            stepSize: 10,
                            padding: 7,
                            font: { size: 6, family: 'Avenir Next, Lato, sans-serif' },
                            color: '#535353',
                            callback: (value) => value + '%'
                        }
                    }
                },
                // Reference plot box: x 73.1mm → 128.0mm, 0% baseline at
                // y=217.8mm with the 70% gridline at 185.3mm.
                layout: { padding: { right: 14 } }
            }
        });
        @endif

        @if($hasPerfChart)
        @php
            $chartPerformanceData = $fund->data['mainContent']['charts']['performanceData'] ?? [];
        @endphp
        const chartData = @json($chartPerformanceData);

        const monthNames = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        const formatChartDate = (label) => {
            const m = String(label).match(/^(\d{4})-(\d{2})$/);
            if (!m) return label;
            return monthNames[parseInt(m[2], 10) - 1] + ' ' + m[1].slice(-2);
        };

        // End value annotation plugin — the reference prints the closing cash
        // value beside each line ("R 188" / "R 137").
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
                    ctx.textAlign = 'left';
                    ctx.fillText(label, lastPoint.x + 4, lastPoint.y - 3);
                    ctx.restore();
                });
            }
        };

        // Scoped to this chart only — registering it globally also draws the
        // end labels on the geographic column chart ("R 6" / "R 8").
        const ctx = document.getElementById('performanceChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            plugins: [endValuePlugin],
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
                        label: 'Benchmark',
                        data: chartData.map(d => d.benchmark),
                        borderColor: colors.darkNavy,
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
                        // The reference draws no rule along the bottom of the
                        // plot: its only horizontal line is the 100 baseline,
                        // which the series dip below early on. That rule is
                        // drawn as the y axis's single gridline instead.
                        border: { display: false },
                        ticks: {
                            font: { size: 6, family: 'Avenir Next, Lato, sans-serif' },
                            color: '#535353',
                            padding: 0,
                            maxRotation: 0,
                            autoSkip: false,
                            // 823 reference ticks: Feb 22, Nov 22, Aug 23,
                            // May 24, Feb 25, Nov 25 — every 9 months anchored
                            // on the first data point (the series opens at the
                            // Feb 2022 inception, with no baseline row).
                            callback: function (value, index) {
                                return index % 9 === 0 ? formatChartDate(this.getLabelForValue(value)) : null;
                            }
                        }
                    },
                    y: {
                        display: true,
                        // Reference: LOG scale, no gridlines, only the 100
                        // origin labelled — its rule sits ~19.5% up the plot,
                        // which a log axis floored at 85 reproduces.
                        type: 'logarithmic',
                        grid: {
                            display: true,
                            drawTicks: false,
                            color: (ctx) => ctx.tick.value === 100 ? '#000' : 'transparent',
                            lineWidth: (ctx) => ctx.tick.value === 100 ? 1 : 0,
                        },
                        border: { color: '#000' },
                        ticks: {
                            font: { size: 6, family: 'Avenir Next, Lato, sans-serif' },
                            color: '#535353',
                            callback: (value) => value === 100 ? '100' : null
                        },
                        // Both series dip under the 100 baseline (the
                        // benchmark bottoms at 92.9), so the floor sits below
                        // it rather than on it. 85→200 puts the 100 rule 19%
                        // up the plot, as measured on the reference (19.5%).
                        min: 85,
                        max: 200,
                        beginAtZero: false
                    }
                },
                layout: {
                    // The plot box runs from the heading straight down; the
                    // right padding holds the R 188 / R 137 end labels.
                    padding: { right: 40 }
                }
            }
        });
        @endif
    </script>
    @endif
</body>
</html>
