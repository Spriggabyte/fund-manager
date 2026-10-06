<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $fund->data['fund']['name'] ?? $fund->name }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;500;700&family=Merriweather:wght@300;400;700&display=swap" rel="stylesheet">
    @include('funds.partials.avenir-fonts')
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
            /* Centred in the grey band (4mm–60mm): 4 + (56 − 45.9)/2 */
            left: 9.05mm;
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
            /* Trello 429: the logo's right edge (the "D") lines up with
               the right edge of the tables (203.45mm); it overhung by 1mm. */
            right: 6.55mm;
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
            /* Right inset 8mm: the reference wraps the objective after
               "subject to" (line 2 ends at 189.8mm; "prudential" would end
               past 202mm), our Merriweather is ~1.4% narrower. */
            padding: 3.6mm 8mm 0 7.75mm;
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

        /* Main Content Area — spans x=65.35mm → 204mm (reference sets the
           heading left edge at x≈65.3mm, measured 387px @150dpi). */
        .main-content {
            flex: 1;
            /* Trello 429: top of the first line of copy level with the
               grey column's (cap tops aligned across the two columns). */
            padding: 5.24mm 6mm 4mm 5.35mm;
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

        /* Per-row max limits rendered smaller than the asset-class name */
        td .row-limit,
        th .th-limit {
            font-size: 6pt;
        }
        th .th-limit {
            font-size: 7pt;
        }

        /* Asset allocation — label 24.4%, four equal numeric columns */
        .aa-table table th:first-child,
        .aa-table table td:first-child {
            width: 24.4%;
        }

        /* Reference inter-section rhythm: ~6mm between a table and the next heading */
        .aa-table,
        .top10-table {
            margin-bottom: 4.2mm;
        }

        /* Trello 432: headings centred in the navy header bars. Chrome
           paints the bar and the text baseline on whole pixels and the
           7.5pt caps are 7.08px tall, so an even-pixel bar leaves them half
           a pixel (0.12mm) off centre whatever partials/table-centring
           does. Odd-pixel bars (17px; 15px on the shallower top 10) let it
           centre them exactly. On page 1 the tables' bottom margins give
           the extra height back, so nothing below them moves. */
        .aa-table table th,
        .tic-table table th,
        .pfe-table table th {
            padding-top: calc((17px - 8.5pt) / 2);
            padding-bottom: calc((17px - 8.5pt) / 2);
        }
        .aa-table {
            margin-bottom: calc(4.2mm - (17px - 8.5pt - 1.2mm));
        }

        /* Performance Table — columns: name 20.1%, cash 11.55%, since 12%, 8 x 8.05% */
        .performance-table table th {
            background-color: var(--dark-navy);
            color: var(--white);
            font-weight: 500;
            font-size: 7pt;
            /* Trello 429: two-line headers set tighter (fund 39 ratio, 1.09 x
               the size); the space goes to the padding so the bar keeps its
               height. */
            line-height: 7.6pt;
            text-align: right;
            padding: 0.74mm 0.5mm;
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

        /* Top 10 Investments — SECURITY 40.1%, ASSET CLASS 28.3% (left),
           MARKET and % OF FUND 15.8% each (centred). Row backgrounds fade
           from pink (Foord funds) through progressively lighter greys. */
        .top10-table table td,
        .top10-table table th {
            padding-top: 0.45mm;
            padding-bottom: 0.45mm;
        }
        /* Trello 432: a 15px bar (odd, see the asset allocation header). */
        .top10-table table th {
            padding-top: calc((15px - 8.5pt) / 2);
            padding-bottom: calc((15px - 8.5pt) / 2);
        }
        .top10-table {
            margin-bottom: calc(4.2mm - (15px - 8.5pt - 0.9mm));
        }
        .top10-table table td:first-child,
        .top10-table table th:first-child {
            width: 40.1%;
            padding-left: 2.1mm;
        }
        .top10-table table td:nth-child(2),
        .top10-table table th:nth-child(2) {
            text-align: left;
            width: 28.3%;
            padding-left: 2.9mm;
        }
        .top10-table table td:nth-child(3),
        .top10-table table th:nth-child(3),
        .top10-table table td:nth-child(4),
        .top10-table table th:nth-child(4) {
            text-align: center;
            padding-left: 0.6mm;
        }
        .top10-table table tbody tr:nth-child(1) td,
        .top10-table table tbody tr:nth-child(2) td { background-color: var(--naartjie-20); }
        .top10-table table tbody tr:nth-child(3) td,
        .top10-table table tbody tr:nth-child(4) td { background-color: var(--row-grey-1); }
        .top10-table table tbody tr:nth-child(5) td,
        .top10-table table tbody tr:nth-child(6) td { background-color: var(--row-grey-2); }
        .top10-table table tbody tr:nth-child(7) td,
        .top10-table table tbody tr:nth-child(8) td { background-color: var(--row-grey-3); }
        .top10-table table tbody tr:nth-child(9) td,
        .top10-table table tbody tr:nth-child(10) td { background-color: var(--row-grey-4); }

        /* TIC table — reference (re-measured against the signed-off design):
           label column break at x≈135.5mm (51%), two equal value columns with
           CENTRED headers and values (content centred with the 2mm right
           padding only — measured text centre x≈896px @150dpi = cell centre
           minus half the right padding). 7.6pt values, 7.5pt headers. First
           (TER) + last data row (Transaction costs) white; middle sub-item
           rows grey. Total row (.total-row) keeps red styling. */
        .tic-table table th:first-child,
        .tic-table table td:first-child {
            width: 51%;
            padding-left: 1.6mm;
        }
        .tic-table table th,
        .tic-table table td {
            padding-right: 2mm;
        }
        .tic-table table th:not(:first-child),
        .tic-table table td:not(:first-child) {
            text-align: center;
            padding-left: 0;
        }
        .tic-table table th {
            font-size: 7.5pt;
        }
        .tic-table table td {
            font-size: 7.6pt;
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
            font-size: 7.6pt;
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
        /* Class B3 has three example columns of ~20.6mm each (measured
           August 2026 reference), so its label column is wider. */
        .pfe-table.pfe-cols-3 table th:first-child,
        .pfe-table.pfe-cols-3 table td:first-child {
            width: 55.2%;
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
           Reference arrows are 5.18pt Wingdings3 triangles measuring
           1.45 x 1.39mm of ink, sitting on the digit baseline with a 2.1mm gap
           before the number (PyMuPDF, Class A August 2026). Drawn as inline
           SVG so the size does not depend on the viewer's fallback font for
           ▲/▼ (the staging preview rendered the glyph visibly smaller). */
        td.change-cell { color: #000; }
        td.change-cell .change-arrow-up,
        td.change-cell .change-arrow-down {
            display: inline-block;
            width: 1.45mm;
            height: 1.39mm;
            margin-right: 2.1mm;
            vertical-align: baseline;
        }
        td.change-cell .change-arrow-up { color: #000; }
        td.change-cell .change-arrow-down { color: #7A9CB4; }

        /* WhatsApp 6 Oct: the triangles stand on one vertical whatever the
           figure's width — a "17.1" used to push its triangle a whole figure
           (~1.5mm) left of the "3.0" rows'. Every figure sits in a box as wide
           as "00.0" (Avenir Next figures are tabular: 0.58em each, point
           0.26em). In a table of one-figure changes the box overhangs the
           triangle's gap by a figure, so a "0.0" keeps its reference place. */
        td.change-cell .change-num {
            display: inline-block;
            min-width: 2em;
            margin-left: -0.58em;
            text-align: right;
        }
        /* A table with a two-figure change (.is-wide) drops the overhang: the
           whole triangle column moves one figure left, so the widest change
           keeps the reference gap instead of running into its triangle. The
           changeArrow formatter sets .is-wide after a quick edit, so :has()
           re-places the column live. */
        table:has(.change-num.is-wide) td.change-cell .change-num {
            margin-left: 0;
        }

        /* =====================================================
           CHARTS SECTION
           ===================================================== */
        .charts-row {
            display: flex;
            gap: 6mm;
            margin: 4mm 0 0 0;
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
            height: 47mm;
            position: relative;
        }

        .chart-wrapper > div {
            width: 100% !important;
            height: 100% !important;
        }

        /* Rotated y-axis caption for the performance chart — rendered in CSS so
           Highcharts doesn't reserve a full title column (the reference tucks
           it right beside the axis). */
        .chart-ytitle {
            position: absolute;
            left: -9mm;
            top: -12mm;
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
            margin: 1.6mm 0 3.1mm 0;
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
            /* Trello 429: the bar spans exactly the disclaimer copy
               column below it (9mm → 60mm − 4mm). */
            margin: 10mm 4mm 0 9mm;
            text-align: center;
        }

        .info-sidebar-header h2 {
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 500;
            font-size: 8pt;
            /* Trello 429: two-line headline set tighter (8pt on 8.7pt, as
               the performance headers). */
            line-height: 8.7pt;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            margin: 0;
        }

        .info-sidebar-content {
            padding: 6.3mm 4mm 4mm 9mm;
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
            /* FEE RATES heading baseline lands at y≈29.7mm like the reference;
               left inset 5.35mm matches p1 main content (ref heading x≈65.3mm). */
            padding: 27.3mm 6mm 4mm 5.35mm;
            overflow: hidden;
        }

        .fee-rates-table {
            margin-bottom: 0;
        }

        /* Label column width MUST equal the TIC table's first column (51%)
           so the two tables' column breaks align vertically (per Paul's
           red-line annotation on the SKM scan); 8pt labels and values
           (measured: ref labels 1.145x the old 7pt render). */
        .fee-rates-table td {
            padding: 0.3mm 1mm 0.3mm 1.6mm;
            font-size: 8pt;
            line-height: 9.4pt;
            background-color: var(--row-grey-2);
            color: var(--dark-navy);
            text-align: left;
        }

        .fee-rates-table td:first-child {
            /* 0.1% narrower than the TIC's 51% first column: this table has
               one fewer border-spacing gutter, so 50.9% lands the column break
               on exactly the same x as the TIC table's break below it. */
            width: 50.9%;
        }

        /* Trello 432: values start at the August reference's x (137.95mm),
           0.7mm further into the column than the labels' inset. */
        .fee-rates-table td:last-child:not([colspan]) {
            text-align: left;
            padding-left: 2.3mm;
        }

        /* "Foord global funds:" — white background, then two pink rows.
           Trello 268/432: plain Avenir Next in the same navy as the rest of
           the table (the August reference is Regular; Medium and black
           read as bold and darker). */
        .fee-rates-table tr.global-funds-header td {
            background-color: var(--white) !important;
            color: var(--dark-navy);
            font-weight: 400;
            text-align: left;
        }

        .fee-rates-table tr.sub-item td {
            background-color: var(--naartjie-20) !important;
            color: var(--dark-navy);
            font-weight: 400;
        }

        /* Reference sets the "- Foord ..." sub-item fund names FLUSH with the
           other row labels (no extra indent — verified against the signed-off
           balanced design). */
        .fee-rates-table tr.sub-item td:first-child {
            padding-left: 1.6mm;
        }

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

        /* Trello 432: PERFORMANCE FEES heading 4.75mm (baseline to baseline)
           above its copy, as in the August reference (was 5.55mm). */
        .performance-fees-section .section-heading + .performance-fees-text {
            margin-top: 1.6mm;
        }

        /* Trello 411: "The annual fee is adjusted…" is a new paragraph, so
           half a line of space goes before it (the reference runs the two
           paragraphs on as a plain line break). */
        .performance-fees-text + .performance-fees-text {
            margin-top: 4.62pt;
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
            width: 9.5mm;
            height: 0;
            border-top: 0.35mm solid var(--naartjie);
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
        /* =====================================================
           SCREEN CHROME (edit mode) - not printed
           ===================================================== */
        .editable { cursor: text; transition: all 0.15s; min-height: 1em; }
        /* A blank cell (e.g. a highest/lowest row's THIS MONTH) still needs a target. */
        .editable:empty { display: inline-block; min-width: 2em; }
        .editable:hover {
            background-color: rgba(245, 158, 11, 0.1);
            outline: 1px dashed #f59e0b;
            border-radius: 2px;
        }
        .editing { background-color: #fef3c7; outline: 2px solid #f59e0b; border-radius: 2px; }
        .edit-input {
            background: transparent; border: none; outline: none; width: 100%;
            font-family: inherit; font-size: inherit; font-weight: inherit;
            color: inherit; line-height: inherit; letter-spacing: inherit;
        }
        .notification {
            position: fixed; top: 1rem; right: 1rem; z-index: 50;
            transform: translateX(100%); transition: transform 0.3s ease-in-out;
        }
        .notification.show { transform: translateX(0); }
        .control-bar {
            background: var(--dark-navy); color: white; padding: 10px 16px;
            display: flex; justify-content: space-between; align-items: center;
            border-radius: 8px; margin: 12px 0;
            font-family: 'Avenir Next', 'Lato', -apple-system, sans-serif;
        }
        .control-bar button, .control-bar a {
            padding: 6px 14px; border-radius: 6px; font-size: 13px; font-weight: 500;
            text-decoration: none; transition: all 0.15s;
            display: inline-flex; align-items: center; gap: 6px;
        }
        .btn-naartjie { background: var(--naartjie); color: white; border: none; cursor: pointer; }
        .btn-naartjie:hover { background: #dd7e75; }
        .btn-grey { background: #697277; color: white; border: 1px solid #bfc3c5; }
        .btn-grey:hover { background: var(--dark-navy); }
        .btn-muted { background: #9a9a9a; color: white; }
        .btn-muted:hover { background: #535353; }
        @media print {
            .no-print { display: none !important; }
        }
        [x-cloak] { display: none !important; }
    </style>
    @include('funds.partials.global-fixes')
    @include('funds.partials.screen-centre')
</head>
<body x-data="fundEditor()">
    <!-- Notification (edit mode) -->
    <div x-show="notification.show" x-cloak class="notification no-print" :class="notification.show ? 'show' : ''">
        <div style="background: white; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); border: 1px solid #e5e7eb; padding: 12px 16px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <svg x-show="notification.type === 'success'" style="width: 18px; height: 18px; color: #22c55e;" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <svg x-show="notification.type === 'error'" style="width: 18px; height: 18px; color: #ef4444;" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
                <p style="font-size: 13px; font-weight: 500; margin: 0;" :style="notification.type === 'success' ? 'color: #166534' : 'color: #991b1b'" x-text="notification.message"></p>
            </div>
        </div>
    </div>

    <!-- Control bar (screen only) -->
    <div class="no-print control-bar" @if(request()->has('pdf')) style="display: none;" @endif>
        <div style="display: flex; align-items: center; gap: 12px;">
            <button @click="toggleEditMode()" class="btn-naartjie">
                <span x-show="!editMode">Enable Edit Mode</span>
                <span x-show="editMode" x-cloak>Disable Edit Mode</span>
            </button>
            <span x-show="editMode" x-cloak style="color: #e9a9a3; font-size: 13px;">Edit mode active &mdash; click any text to edit</span>
        </div>
        <div style="display: flex; align-items: center; gap: 8px;">
            <a href="{{ route('funds.edit', $fund) }}" class="btn-grey">Edit Fund</a>
            <a href="{{ route('funds.revisions', $fund) }}" class="btn-grey">Revisions</a>
            <a href="{{ route('funds.pdf', $fund) }}" class="btn-naartjie">Export PDF</a>
            <a href="{{ route('funds.index') }}" class="btn-muted">Back to Funds</a>
        </div>
    </div>
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
                    <span x-data="editableField('fund.date', '{{ addslashes($fund->data['fund']['date'] ?? now()->format('d F Y')) }}')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ $fund->data['fund']['date'] ?? now()->format('d F Y') }}</span>
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
                <span x-data="editableField('fund.name', '{{ addslashes($fund->data['fund']['name'] ?? $fund->name) }}', 'fundName')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">
                {{ mb_strtoupper($mainName) }}
                @if($classText)
                    <span class="class-suffix">&mdash; {{ $classText }}</span>
                @endif
                </span>
            </h1>
            <p class="fund-description"><span x-data="editableField('fund.description', '{{ addslashes($fund->data['fund']['description'] ?? '') }}')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ $fund->data['fund']['description'] ?? '' }}</span></p>
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
                                            <svg class="equity-dot {{ $i < $filled ? 'filled' : 'empty' }}" viewBox="0 0 10 10" xmlns="http://www.w3.org/2000/svg"><circle cx="5" cy="5" r="5"/></svg>
                                        @endfor
                                    </span>
                                </h3>
                                @if(isset($value['description']))
                                    <p class="sidebar-text"><span x-data="editableField('sidebar.{{ $key }}.description', '{{ addslashes($value['description']) }}')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{!! $value['description'] !!}</span></p>
                                @endif
                            @else
                                <h3 class="sidebar-heading">{{ $labels[$key] ?? strtoupper(implode(' ', preg_split('/(?=[A-Z])/', $key, -1, PREG_SPLIT_NO_EMPTY))) }}</h3>
                                @if (is_array($value))
                                    <p class="sidebar-text"><span x-data="editableField('sidebar.{{ $key }}.description', '{{ addslashes($value['description'] ?? '') }}')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{!! $value['description'] ?? '' !!}</span></p>
                                @else
                                    <p class="sidebar-text"><span x-data="editableField('sidebar.{{ $key }}', '{{ addslashes($value) }}')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{!! $value !!}</span></p>
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
                    @php $aaTitle = $fund->data['mainContent']['assetAllocation']['title'] ?? 'ASSET ALLOCATION % (MAX LIMITS IN BRACKETS)'; @endphp
                    <h3 class="section-heading"><span x-data="editableField('mainContent.assetAllocation.title', '{{ addslashes($aaTitle) }}', 'titleSuffix')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{!! $renderHeading($aaTitle) !!}</span></h3>
                    @if(isset($fund->data['mainContent']['assetAllocation']['subtitle']))
                        <p class="section-subheading"><span x-data="editableField('mainContent.assetAllocation.subtitle', '{{ addslashes($fund->data['mainContent']['assetAllocation']['subtitle']) }}')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ $fund->data['mainContent']['assetAllocation']['subtitle'] }}</span></p>
                    @endif

                    <div class="table-container aa-table">
                        <table>
                            <thead>
                                <tr>
                                    @foreach ($fund->data['mainContent']['assetAllocation']['headers'] as $hIndex => $header)
                                        <th><span x-data="editableField('mainContent.assetAllocation.headers.{{ $hIndex }}', '{{ addslashes(strip_tags((string) $header)) }}', 'thLimit')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{!! $renderTh(strip_tags((string) $header)) !!}</span></th>
                                    @endforeach
                                </tr>
                            </thead>
                            @php
                                $aaHeaders = $fund->data['mainContent']['assetAllocation']['headers'] ?? [];
                                $aaFirstRow = $fund->data['mainContent']['assetAllocation']['rows'][0] ?? [];
                                $aaColumnKeys = [];
                                $keyMap = ['SA (100)' => 'sa', 'FOREIGN (45)' => 'foreign', 'TOTAL' => 'total', 'CHANGE' => 'change'];
                                foreach (array_slice($aaHeaders, 1) as $i => $h) {
                                    $key = $keyMap[strtoupper(trim($h))] ?? strtolower(preg_replace('/[^a-zA-Z]/', '', $h) ?: 'col');
                                    // A header relabelled in edit mode keeps its column's data.
                                    if (! array_key_exists($key, $aaFirstRow) && isset(['sa', 'foreign', 'total', 'change'][$i])) {
                                        $key = ['sa', 'foreign', 'total', 'change'][$i];
                                    }
                                    $aaColumnKeys[] = $key;
                                }
                            @endphp
                            <tbody>
                                @foreach ($fund->data['mainContent']['assetAllocation']['rows'] as $rowIndex => $row)
                                    <tr>
                                        <td><span x-data="editableField('mainContent.assetAllocation.rows.{{ $rowIndex }}.name', '{{ addslashes($row['name'] ?? '') }}', 'assetName')" data-limit="{{ $row['limit'] ?? '' }}" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{!! $renderAssetName($row) !!}</span></td>
                                        @foreach ($aaColumnKeys as $colKey)
                                            @if ($colKey === 'change')
                                                @php
                                                    $raw = trim((string)($row['change'] ?? ''));
                                                    if (preg_match('/^([▲▼])\s*(.*)$/u', $raw, $cm)) {
                                                        $arrowChar = $cm[1];
                                                        $numPart = $cm[2];
                                                    } else {
                                                        $arrowChar = '';
                                                        $numPart = $raw;
                                                    }
                                                    // The triangle's colour follows its shape, so a change
                                                    // edited in edit mode never leaves changeDirection stale.
                                                    $arrowClass = $arrowChar === '▲' ? 'change-arrow-up' : 'change-arrow-down';
                                                @endphp
                                                <td class="change-cell"><span x-data="editableField('mainContent.assetAllocation.rows.{{ $rowIndex }}.change', '{{ addslashes($raw) }}', 'changeArrow')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">@if ($arrowChar)<svg class="{{ $arrowClass }}" viewBox="0 0 10 10" aria-label="{{ $arrowChar }}"><polygon fill="currentColor" points="{{ $arrowChar === '▲' ? '0,10 5,0 10,10' : '0,0 10,0 5,10' }}"/></svg>@endif<span class="change-num{{ preg_match_all('/\d/', (string) $fmt($numPart, 1)) >= 3 ? ' is-wide' : '' }}">{{ $fmt($numPart, 1) }}</span></span></td>
                                            @else
                                                <td><span x-data="editableField('mainContent.assetAllocation.rows.{{ $rowIndex }}.{{ $colKey }}', '{{ addslashes($fmt($row[$colKey] ?? '', 1)) }}', 'oneDecimal')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ $fmt($row[$colKey] ?? '', 1) }}</span></td>
                                            @endif
                                        @endforeach
                                    </tr>
                                @endforeach
                                @if(isset($fund->data['mainContent']['assetAllocation']['total']))
                                    @php $aaTotal = $fund->data['mainContent']['assetAllocation']['total']; @endphp
                                    <tr class="total-row">
                                        <td><span x-data="editableField('mainContent.assetAllocation.total.name', '{{ addslashes($aaTotal['name'] ?? 'TOTAL') }}')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ $aaTotal['name'] ?? 'TOTAL' }}</span></td>
                                        @foreach ($aaColumnKeys as $colKey)
                                            @if ($colKey === 'change')
                                                <td></td>
                                            @else
                                                <td><span x-data="editableField('mainContent.assetAllocation.total.{{ $colKey }}', '{{ addslashes($fmt($aaTotal[$colKey] ?? '', 1)) }}', 'oneDecimal')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ $fmt($aaTotal[$colKey] ?? '', 1) }}</span></td>
                                            @endif
                                        @endforeach
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                @endif

                <!-- Top 10 Investments -->
                @if(isset($fund->data['mainContent']['topInvestments']))
                    <h3 class="section-heading"><span x-data="editableField('mainContent.topInvestments.title', '{{ addslashes($fund->data['mainContent']['topInvestments']['title'] ?? 'TOP 10 INVESTMENTS') }}')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ $fund->data['mainContent']['topInvestments']['title'] ?? 'TOP 10 INVESTMENTS' }}</span></h3>

                    <div class="table-container top10-table">
                        <table>
                            <thead>
                                <tr>
                                    @foreach ($fund->data['mainContent']['topInvestments']['headers'] as $hIndex => $header)
                                        <th><span x-data="editableField('mainContent.topInvestments.headers.{{ $hIndex }}', '{{ addslashes($header) }}')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ $header }}</span></th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($fund->data['mainContent']['topInvestments']['rows'] as $idx => $row)
                                    <tr class="{{ ($row['highlight'] ?? false) || $idx < 2 ? 'highlight-row' : '' }}">
                                        <td><span x-data="editableField('mainContent.topInvestments.rows.{{ $idx }}.security', '{{ addslashes($row['security']) }}')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ $row['security'] }}</span></td>
                                        <td><span x-data="editableField('mainContent.topInvestments.rows.{{ $idx }}.assetClass', '{{ addslashes($row['assetClass'] ?? '') }}')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ $row['assetClass'] ?? '' }}</span></td>
                                        <td><span x-data="editableField('mainContent.topInvestments.rows.{{ $idx }}.market', '{{ addslashes($row['market'] ?? '') }}')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ $row['market'] ?? '' }}</span></td>
                                        <td><span x-data="editableField('mainContent.topInvestments.rows.{{ $idx }}.percentage', '{{ addslashes($fmt($row['percentage'] ?? '', 1)) }}', 'oneDecimal')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ $fmt($row['percentage'] ?? '', 1) }}</span></td>
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
                                <div class="chart-ytitle">Cash Value<sup>2</sup> (R&rsquo;000)</div>
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
                    {{-- Reference sets this heading's bracketed text at FULL heading size
                         (only ASSET ALLOCATION's "(MAX LIMITS IN BRACKETS)" is smaller). --}}
                    @php $perfTitle = $fund->data['mainContent']['performanceTable']['title'] ?? 'PORTFOLIO PERFORMANCE % (PERIODS GREATER THAN ONE YEAR ARE ANNUALISED¹)'; @endphp
                    <h3 class="section-heading"><span x-data="editableField('mainContent.performanceTable.title', '{{ addslashes($perfTitle) }}', 'supers')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{!! $normaliseSupers(e($perfTitle)) !!}</span></h3>

                    <div class="table-container performance-table">
                        <table>
                            <thead>
                                <tr>
                                    {{-- A header's text picks its column's data ($perfKeyMap):
                                         "<br>" breaks the line, so "20<br>YRS" shows 20 years. --}}
                                    @foreach ($perfHeaders as $hIndex => $header)
                                        <th><span x-data="editableField('mainContent.performanceTable.headers.{{ $hIndex }}', '{{ addslashes($header) }}', 'supers')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{!! $header !!}</span></th>
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
                                        $rawName = (string) $row['name'];
                                        // The footnote marker sits outside the editable name, so
                                        // renaming a row keeps it. (global-fixes closes the gap:
                                        // "Fund³", "Benchmark³,⁴".)
                                        $perfMarker = null;
                                        if (preg_match('/^fund\s+(highest|lowest)/i', $nameStr)) {
                                            // Highest/Lowest historical rows take footnotes 3 and 5.
                                            if (strpos($rawName, '3,5') === false && strpos($rawName, '³,⁵') === false) {
                                                $perfMarker = '3,5';
                                            }
                                        } elseif (stripos($nameStr, 'fund') === 0 && strpos($rawName, '³') === false && strpos($rawName, '<sup>3</sup>') === false) {
                                            $perfMarker = '3';
                                        } elseif (stripos($nameStr, 'benchmark') === 0 && strpos($rawName, '³,⁴') === false && strpos($rawName, '3,4') === false) {
                                            $perfMarker = '3,4';
                                        }
                                    @endphp
                                    <tr class="{{ $isTopFundRow ? 'highlight-row' : '' }}">
                                        <td><span x-data="editableField('mainContent.performanceTable.rows.{{ $idx }}.name', '{{ addslashes($rawName) }}', 'supers')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{!! $rawName !!}</span>@if ($perfMarker)<sup>{{ $perfMarker }}</sup>@endif</td>
                                        @foreach ($perfColKeys as $colKey)
                                            @if ($colKey)
                                                @php $perfCell = isset($row[$colKey]) ? ($colKey === 'cashValue' ? $row[$colKey] : $fmt($row[$colKey], 1)) : ''; @endphp
                                                <td><span x-data="editableField('mainContent.performanceTable.rows.{{ $idx }}.{{ $colKey }}', '{{ addslashes($perfCell) }}', '{{ $colKey === 'cashValue' ? '' : 'oneDecimal' }}')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ $perfCell }}</span></td>
                                            @else
                                                <td></td>
                                            @endif
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
                        @foreach ($fund->data['importantInfo']['paragraphs'] as $i => $paragraph)
                            <p><span x-data="editableField('importantInfo.paragraphs.{{ $i }}', '{{ addslashes($paragraph) }}')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ $paragraph }}</span></p>
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
                    <h3 class="section-heading"><span x-data="editableField('fees.feeRates.title', '{{ addslashes($fund->data['fees']['feeRates']['title'] ?? 'FEE RATES') }}')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ $fund->data['fees']['feeRates']['title'] ?? 'FEE RATES' }}</span></h3>

                    <div class="table-container fee-rates-table">
                        <table>
                            <tbody>
                                @foreach ($fund->data['fees']['feeRates']['rates'] as $rIndex => $rate)
                                    <tr>
                                        <td><span x-data="editableField('fees.feeRates.rates.{{ $rIndex }}.name', '{{ addslashes($rate['name']) }}')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ $rate['name'] }}</span></td>
                                        <td><span x-data="editableField('fees.feeRates.rates.{{ $rIndex }}.value', '{{ addslashes($rate['value']) }}')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ $rate['value'] }}</span></td>
                                    </tr>
                                @endforeach
                                @if(isset($fund->data['fees']['feeRates']['globalFunds']))
                                    <tr class="global-funds-header">
                                        <td colspan="2"><span x-data="editableField('fees.feeRates.globalFunds.title', '{{ addslashes($fund->data['fees']['feeRates']['globalFunds']['title'] ?? 'Foord global funds:') }}')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ $fund->data['fees']['feeRates']['globalFunds']['title'] ?? 'Foord global funds:' }}</span></td>
                                    </tr>
                                    @foreach ($fund->data['fees']['feeRates']['globalFunds']['funds'] as $gIndex => $gfund)
                                        @php
                                            $gName = ltrim($gfund['name'], "- \t");
                                        @endphp
                                        {{-- The "- " bullet is drawn here, so the edit is just the name. --}}
                                        <tr class="sub-item">
                                            <td>- <span x-data="editableField('fees.feeRates.globalFunds.funds.{{ $gIndex }}.name', '{{ addslashes($gName) }}')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ $gName }}</span></td>
                                            <td><span x-data="editableField('fees.feeRates.globalFunds.funds.{{ $gIndex }}.value', '{{ addslashes($gfund['value']) }}')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ $gfund['value'] }}</span></td>
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
                        <h3 class="section-heading"><span x-data="editableField('fees.totalInvestmentCharge.title', '{{ addslashes($fund->data['fees']['totalInvestmentCharge']['title'] ?? 'TOTAL INVESTMENT CHARGE %') }}')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ $fund->data['fees']['totalInvestmentCharge']['title'] ?? 'TOTAL INVESTMENT CHARGE %' }}</span></h3>

                        <div class="table-container tic-table">
                            <table>
                                <thead>
                                    <tr>
                                        @foreach ($fund->data['fees']['totalInvestmentCharge']['headers'] as $hIndex => $header)
                                            <th><span x-data="editableField('fees.totalInvestmentCharge.headers.{{ $hIndex }}', '{{ addslashes($header) }}')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ $header }}</span></th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($fund->data['fees']['totalInvestmentCharge']['rows'] as $rowIndex => $row)
                                        <tr>
                                            <td><span x-data="editableField('fees.totalInvestmentCharge.rows.{{ $rowIndex }}.name', '{{ addslashes($row['name']) }}')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ $row['name'] }}</span></td>
                                            <td><span x-data="editableField('fees.totalInvestmentCharge.rows.{{ $rowIndex }}.12m', '{{ $fmt($row['12m'] ?? '', 2) }}', 'twoDecimals')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ $fmt($row['12m'] ?? '', 2) }}</span></td>
                                            <td><span x-data="editableField('fees.totalInvestmentCharge.rows.{{ $rowIndex }}.36m', '{{ $fmt($row['36m'] ?? '', 2) }}', 'twoDecimals')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ $fmt($row['36m'] ?? '', 2) }}</span></td>
                                        </tr>
                                    @endforeach
                                    <tr class="total-row">
                                        <td><span x-data="editableField('fees.totalInvestmentCharge.total.name', '{{ addslashes($fund->data['fees']['totalInvestmentCharge']['total']['name'] ?? 'Total investment charge') }}')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ $fund->data['fees']['totalInvestmentCharge']['total']['name'] ?? 'Total investment charge' }}</span></td>
                                        <td><span x-data="editableField('fees.totalInvestmentCharge.total.12m', '{{ $fmt($fund->data['fees']['totalInvestmentCharge']['total']['12m'] ?? '', 2) }}', 'twoDecimals')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ $fmt($fund->data['fees']['totalInvestmentCharge']['total']['12m'] ?? '', 2) }}</span></td>
                                        <td><span x-data="editableField('fees.totalInvestmentCharge.total.36m', '{{ $fmt($fund->data['fees']['totalInvestmentCharge']['total']['36m'] ?? '', 2) }}', 'twoDecimals')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ $fmt($fund->data['fees']['totalInvestmentCharge']['total']['36m'] ?? '', 2) }}</span></td>
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
                    <h3 class="section-heading"><span x-data="editableField('fees.performanceFeeExamples.title', '{{ addslashes($fund->data['fees']['performanceFeeExamples']['title'] ?? 'PERFORMANCE FEE EXAMPLES %') }}')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ $fund->data['fees']['performanceFeeExamples']['title'] ?? 'PERFORMANCE FEE EXAMPLES %' }}</span></h3>

                    <div class="table-container pfe-table pfe-cols-{{ max(0, count($fund->data['fees']['performanceFeeExamples']['headers'] ?? []) - 1) }}">
                        <table>
                            <thead>
                                <tr>
                                    @foreach ($fund->data['fees']['performanceFeeExamples']['headers'] as $hIndex => $header)
                                        <th><span x-data="editableField('fees.performanceFeeExamples.headers.{{ $hIndex }}', '{{ addslashes($header) }}')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ $header }}</span></th>
                                    @endforeach
                                </tr>
                            </thead>
                            @php
                                // One example column per stored header: Classes A and B2
                                // show four (A–D), Class B3 three (A–C, two-year rolling,
                                // 0.4% fee). Keyed by position, so a header relabelled in
                                // edit mode keeps its column's data.
                                $pfeCols = array_slice(['a', 'b', 'c', 'd'], 0, max(0, count($fund->data['fees']['performanceFeeExamples']['headers'] ?? ['', 'A', 'B', 'C', 'D']) - 1));
                                $pfeTotal = $fund->data['fees']['performanceFeeExamples']['total'] ?? [];
                            @endphp
                            <tbody>
                                @foreach ($fund->data['fees']['performanceFeeExamples']['rows'] as $rowIndex => $row)
                                    <tr>
                                        <td><span x-data="editableField('fees.performanceFeeExamples.rows.{{ $rowIndex }}.name', '{{ addslashes($row['name']) }}')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ $row['name'] }}</span></td>
                                        @foreach ($pfeCols as $col)
                                            <td><span x-data="editableField('fees.performanceFeeExamples.rows.{{ $rowIndex }}.{{ $col }}', '{{ addslashes($fmt($row[$col] ?? '', 1)) }}', 'oneDecimal')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ $fmt($row[$col] ?? '', 1) }}</span></td>
                                        @endforeach
                                    </tr>
                                @endforeach
                                <tr class="total-row">
                                    <td><span x-data="editableField('fees.performanceFeeExamples.total.name', '{{ addslashes($pfeTotal['name'] ?? 'Annual fee rate applied (excl. VAT)') }}')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ $pfeTotal['name'] ?? 'Annual fee rate applied (excl. VAT)' }}</span></td>
                                    @foreach ($pfeCols as $col)
                                        {{-- Non-numeric totals ("0.5*" — minimum fee applies) print as stored. --}}
                                        <td><span x-data="editableField('fees.performanceFeeExamples.total.{{ $col }}', '{{ addslashes($fmt($pfeTotal[$col] ?? '', 1)) }}', 'oneDecimal')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ $fmt($pfeTotal[$col] ?? '', 1) }}</span></td>
                                    @endforeach
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
                        {{-- Trello 429: "the Foord team" starts the second line so the list reads cleanly. --}}
                        <p class="footer-text">{!! preg_replace('/,\s+(the Foord team)/u', ',<br>$1', e($fund->data['footer']['info'] ?? 'Please visit our website for more information regarding our investment track record, the Foord team, current and archived news items, or forms and documents.')) !!}</p>
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
                // areas with reversedStacks: false, so the stack reads bottom→top: Inflation (from 0%),
                // 5% Hurdle (inflation → inflation+5), Excess on top — per the published fact-sheet reference.
                const inflationSeries = inflationData.map(d => d.inflation);
                const hurdleSeries    = inflationData.map(d => d.hurdle ?? 5);
                const excessSeries    = inflationData.map(d => d.excess ?? (d.composite - d.inflation - (d.hurdle ?? 5)));
                const compositeSeries = inflationData.map(d => d.composite);

                // Highcharts SUBTRACTS negative values from a running stack, which would pull the dark
                // Excess band down over the 5% Hurdle band whenever the fund underperforms CPI+5%. The
                // published fact sheet (an Excel chart) keeps the Inflation/Hurdle bands intact and drops
                // negative Excess below the 0% line instead. Reproduce that by splitting Excess into a
                // positive series (main stack) and a negative series (its own stack, so it renders
                // downward from 0%).
                const excessPosSeries = excessSeries.map(v => (v > 0 ? v : 0));
                const excessNegSeries = excessSeries.map(v => (v < 0 ? v : 0));

                // Fixed Y-axis to match the published reference layout (-10% to 35%).
                const inflationYMin = -10;
                const inflationYMax = 35;
                const inflationTickPositionsY = [-10, -5, 0, 5, 10, 15, 20, 25, 30, 35];

                Highcharts.chart('inflationChart', {
                    chart: { type: 'area', backgroundColor: 'transparent', spacing: [4, 4, 4, 4], animation: false },
                    title: { text: null },
                    xAxis: {
                        categories: inflationDates,
                        // Reference: no axis line at -10% — the x-axis sits ON the zero
                        // line (drawn as a thick yAxis plotLine below); labels stay at
                        // the bottom of the plot.
                        tickWidth: 0,
                        lineWidth: 0,
                        labels: {
                            style: { fontSize: '8px', color: '#000' },
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
                        // Thick black line at 0% — the chart's visual x-axis.
                        plotLines: [{ value: 0, color: '#000000', width: 2, zIndex: 4 }],
                        // First stacked series (Inflation) at the BOTTOM of the stack, Excess on top,
                        // so the Composite spline runs along the top of the Excess band.
                        reversedStacks: false,
                        labels: {
                            style: { fontSize: '8px', color: '#000' },
                            formatter: function () { return this.value + '%'; },
                        },
                    },
                    legend: {
                        // Reference legend: ONE row of four items, left-aligned at the
                        // container edge, long thin rule swatches (24px long x ~1px thick
                        // @150dpi = 16 x 1 CSS px — rendered as 1px rectangles so the
                        // series lineWidth cannot thicken them).
                        align: 'left',
                        x: -4, // cancel spacingLeft so the first swatch sits at the container edge like the reference
                        itemStyle: { fontSize: '8px', fontWeight: 'normal', color: colors.darkNavy },
                        symbolWidth: 16,
                        symbolHeight: 1,
                        symbolRadius: 0,
                        // Reference: 0.5mm swatch-to-label gap, 2.5mm between items,
                        // all four items on ONE row (measured Class A August 2026).
                        symbolPadding: 2,
                        itemDistance: 9,
                        margin: 6,
                        padding: 0,
                    },
                    tooltip: { enabled: false },
                    plotOptions: {
                        area: { stacking: 'normal', marker: { enabled: false }, lineWidth: 1, fillOpacity: 1 },
                        spline: { marker: { enabled: false }, lineWidth: 2 },
                        // 'rectangle' collapses to a 2px dot at symbolHeight 1 under the
                        // bundled Highcharts 11 build; lineMarker (markers disabled)
                        // draws the reference's long thin rule.
                        series: { animation: false, legendSymbol: 'lineMarker' },
                    },
                    // Identical series config to the on-screen fund page (show.blade.php): stacked areas with
                    // reversedStacks: false on the yAxis (Inflation at the bottom from 0%, then 5% Hurdle,
                    // positive Excess on top) so the Composite spline runs along the top of the Excess band.
                    // Negative Excess lives in its own stack → dark spikes below the 0% line, leaving the
                    // Hurdle band intact (linkedTo keeps it out of the legend: 4 legend items exactly).
                    // Do NOT change stacking or series order here unless you make the SAME change in show.blade.php.
                    // step: 'center' on the Excess bands reproduces the reference's
                    // per-month Excel columns (visible as bar texture in the negative
                    // spikes around Dec-21).
                    series: [
                        { name: 'Composite', type: 'spline', data: compositeSeries, color: colors.naartjie, stacking: undefined, zIndex: 5 },
                        { name: 'Inflation', type: 'area',   data: inflationSeries, color: colors.lightBlue },
                        { name: '5% Hurdle', type: 'area',   data: hurdleSeries,    color: colors.lightGrey },
                        { name: 'Excess',    type: 'area',   data: excessPosSeries, color: colors.darkNavy, step: 'center' },
                        { name: 'Excess',    type: 'area',   data: excessNegSeries, color: colors.darkNavy, step: 'center', stack: 'negative', linkedTo: ':previous' },
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

                // Reference chart (Foord Balanced Class A design PDF, measured from
                // its vector paths): the y-axis is LINEAR from 0 with a "nice" max
                // just above the curve peak (peak * 1.05 rounded up to 100), and only
                // the "100" baseline value is labelled near the axis.
                const portfolioMaxVal = Math.max(
                    ...portfolioData.map(d => Math.max(d.fund || 0, d.benchmark || 0))
                );
                const portfolioYMax = Math.ceil(portfolioMaxVal * 1.05 / 100) * 100;

                // Calendar-aligned ticks every 4 years anchored on the first FULL
                // month (the reference labels Sep 02, Sep 06, … — the 100 baseline
                // point sits one month earlier and carries no tick).
                const portfolioDates = portfolioData.map(d => d.date);
                const portfolioTickPositions = (function () {
                    const idxByDate = {};
                    portfolioDates.forEach((d, i) => { idxByDate[d] = i; });
                    const anchor = portfolioDates.length > 1 ? portfolioDates[1] : portfolioDates[0];
                    const anchorIdx = portfolioDates.length > 1 ? 1 : 0;
                    const firstYear = parseInt(anchor.slice(0, 4), 10);
                    const month = anchor.slice(5, 7);
                    const positions = [anchorIdx];
                    for (let y = firstYear + 4; y <= 2030; y += 4) {
                        const key = y + '-' + month;
                        if (idxByDate[key] !== undefined) positions.push(idxByDate[key]);
                    }
                    return positions;
                })();

                Highcharts.chart('portfolioChart', {
                    chart: { type: 'spline', backgroundColor: 'transparent', spacing: [4, 46, 4, 0], animation: false },
                    title: { text: null },
                    xAxis: {
                        categories: portfolioDates,
                        tickWidth: 1,
                        tickLength: 3,
                        tickColor: '#000',
                        lineColor: '#000',
                        lineWidth: 1,
                        labels: {
                            style: { fontSize: '8px', color: '#000' },
                            formatter: function () { return formatXTickPortfolio(this.value); },
                            rotation: 0,
                            autoRotation: false,
                        },
                        tickPositions: portfolioTickPositions,
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
                        max: portfolioYMax,
                        endOnTick: false,
                        startOnTick: false,
                        tickPositions: [100],
                        labels: {
                            distance: 2,
                            y: -3,
                            style: { fontSize: '8px', color: '#000' },
                            formatter: function () {
                                return this.value === 100 ? '100' : '';
                            },
                        },
                    },
                    legend: {
                        // Long thin rule swatches like the reference (16 x 1 CSS px).
                        itemStyle: { fontSize: '8px', fontWeight: 'normal', color: colors.darkNavy },
                        symbolWidth: 16,
                        symbolHeight: 1,
                        symbolRadius: 0,
                        symbolPadding: 2,
                        itemDistance: 40,
                        margin: 6,
                        padding: 0,
                    },
                    tooltip: { enabled: false },
                    plotOptions: {
                        spline: { marker: { enabled: false }, lineWidth: 1.75 },
                        // See the inflation chart: 'rectangle' renders as a dot.
                        series: { animation: false, legendSymbol: 'lineMarker' },
                    },
                    series: [
                        {
                            name: 'Fund', data: portfolioData.map(d => d.fund), color: colors.naartjie,
                            dataLabels: [{
                                enabled: true, align: 'left', verticalAlign: 'middle', x: 6, y: 0,
                                style: { fontSize: '9px', fontWeight: '500', color: colors.naartjie, textOutline: 'none' },
                                formatter: function () { return this.point.index === this.series.data.length - 1 ? formatCashLabel(this.y) : null; },
                                crop: false, overflow: 'allow', allowOverlap: true,
                            }],
                        },
                        {
                            name: 'Benchmark', data: portfolioData.map(d => d.benchmark), color: colors.darkNavy,
                            dataLabels: [{
                                enabled: true, align: 'left', verticalAlign: 'middle', x: 6, y: 0,
                                style: { fontSize: '9px', fontWeight: '500', color: colors.darkNavy, textOutline: 'none' },
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
    <!-- ==================== EDIT MODE (screen only) ==================== -->
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
        // Display formatters re-create the styled server rendering after an edit.
        const editableFormatters = {
            // TIC values print to two decimals, as the blade's $fmt does.
            twoDecimals(value) {
                const s = String(value).trim();
                return /^-?\d*\.?\d+$/.test(s) ? Number(s).toFixed(2) : s;
            },
            fundName(value) {
                const m = String(value).match(/^(.+?)\s*[\u2014\u2013-]\s*(CLASS\s+[A-Z][0-9]*)$/i);
                if (!m) return String(value).toUpperCase();
                return m[1].toUpperCase() + ' <span class="class-suffix">&mdash; ' + m[2].toUpperCase() + '</span>';
            },
            // As the blade's $renderAssetName: the row's stored limit, unless
            // the name carries its own "(75)".
            assetName(value, el) {
                const s = String(value);
                if (el && el.dataset.limit && !s.includes('(')) return s + ' <span class="row-limit">(' + el.dataset.limit + ')</span>';
                return s.replace(/\s*\(([^)]+)\)\s*$/, ' <span class="row-limit">($1)</span>');
            },
            // As the blade's $renderHeading / $renderTh.
            titleSuffix(value) {
                return String(value).replace(/\s*\(([^)]+)\)\s*$/, ' <span class="title-suffix">($1)</span>');
            },
            thLimit(value) {
                return String(value).replace(/\s*\(([^)]+)\)\s*$/, ' <span class="th-limit">($1)</span>');
            },
            // Table figures print to one decimal, as the blade's $fmt does; an
            // explicit "+" is kept ("+2.0" in the fee examples).
            oneDecimal(value) {
                const s = String(value).trim();
                if (!/^[+-]?\d*\.?\d+$/.test(s)) return s;
                return (s[0] === '+' ? '+' : '') + Number(s).toFixed(1);
            },
            // "\u25b2 0.1" / "\u25bc 0.2" \u2014 the SVG triangle the blade draws, then the
            // figure in its .change-num box (one triangle column).
            changeArrow(value) {
                const m = String(value).trim().match(/^([\u25b2\u25bc])\s*(.*)$/);
                const num = (v) => {
                    const t = editableFormatters.oneDecimal(v);
                    return '<span class="change-num' + ((t.match(/\d/g) || []).length >= 3 ? ' is-wide' : '') + '">' + t + '</span>';
                };
                if (!m) return num(value);
                const up = m[1] === '\u25b2';
                return '<svg class="' + (up ? 'change-arrow-up' : 'change-arrow-down') + '" viewBox="0 0 10 10" aria-label="' + m[1] + '">'
                    + '<polygon fill="currentColor" points="' + (up ? '0,10 5,0 10,10' : '0,0 10,0 5,10') + '"/></svg>'
                    + num(m[2]);
            },
            // Unicode superscript digits as <sup>, as global-fixes does on load
            // ("VALUE\u00b2", "ANNUALISED\u00b9"); "<br>" in a header breaks the line.
            supers(value) {
                const map = {'\u2070': '0', '\u00b9': '1', '\u00b2': '2', '\u00b3': '3', '\u2074': '4', '\u2075': '5', '\u2076': '6', '\u2077': '7', '\u2078': '8', '\u2079': '9'};
                return String(value).replace(/[\u2070\u00b9\u00b2\u00b3\u2074\u2075\u2076\u2077\u2078\u2079]+(?:,[\u2070\u00b9\u00b2\u00b3\u2074\u2075\u2076\u2077\u2078\u2079]+)*/g, (run) => '<sup>' + run.replace(/[\u2070\u00b9\u00b2\u00b3\u2074\u2075\u2076\u2077\u2078\u2079]/g, (c) => map[c]) + '</sup>');
            }
        };
        // Input normalisers, run before saving, so retyping a figure as it
        // already prints ("100" for "100.0") is not a change. A change can
        // be typed as a signed figure ("-0.2", "+0.1", "0.1") and is stored
        // the way the importer writes it ("\u25bc 0.2"); an unsigned zero gets no
        // triangle.
        const editableParsers = {
            oneDecimal(value) {
                return editableFormatters.oneDecimal(value);
            },
            changeArrow(value) {
                const s = String(value).trim();
                const m = s.match(/^([\u25b2\u25bc+-])?\s*(\d*\.?\d+)$/);
                if (!m) return s;
                const n = Number(m[2]);
                if (m[1] === '\u25b2' || m[1] === '\u25bc') return m[1] + ' ' + n.toFixed(1);
                if (n === 0) return n.toFixed(1);
                return (m[1] === '-' ? '\u25bc ' : '\u25b2 ') + n.toFixed(1);
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
                            span.innerHTML = `<input type="text" class="edit-input" value="${String(this.value).replace(/"/g, '&quot;')}" />`;
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
                    const parse = this.formatter && editableParsers[this.formatter];
                    if (parse) this.value = parse(this.value);
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
                // The server-rendered markup is left untouched until a value
                // changes, so the PDF render is byte-identical to the template.
                updateDisplay() {
                    if (this.editing) return;
                    const fmt = this.formatter && editableFormatters[this.formatter];
                    this.$el.innerHTML = fmt ? fmt(this.value, this.$el) : this.value;
                }
            }
        }
    </script>
</body>
</html>
