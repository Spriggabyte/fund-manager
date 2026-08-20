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
            padding: 3.6mm 6mm 0 7.75mm;
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
            padding: 5.4mm 6mm 4mm 5.35mm;
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

        /* Asset allocation — 820 reference: label 39.5%, single SA column
           and the CHANGE column splitting the rest (~30% each) */
        .aa-table table th:first-child,
        .aa-table table td:first-child {
            width: 39.5%;
        }

        /* Reference inter-section rhythm: ~6mm between a table and the next heading */
        .aa-table,
        .top10-table {
            margin-bottom: 4.2mm;
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

        /* Top 10 Investments — 820 reference: three EQUAL left-aligned
           columns (SECURITY / ASSET CLASS / % OF FUND — no MARKET column,
           every holding is ZAF). Rows 1-7 keep the default grey; 8-9 and 10
           step lighter (measured 230/235/240). */
        .top10-table table td,
        .top10-table table th {
            padding-top: 0.45mm;
            padding-bottom: 0.45mm;
        }
        .top10-table table td:first-child,
        .top10-table table th:first-child {
            width: 33.3%;
            padding-left: 2.1mm;
        }
        .top10-table table td:nth-child(2),
        .top10-table table th:nth-child(2),
        .top10-table table td:nth-child(3),
        .top10-table table th:nth-child(3) {
            text-align: left;
            width: 33.3%;
            padding-left: 2.9mm;
        }
        .top10-table table tbody tr:nth-child(8) td,
        .top10-table table tbody tr:nth-child(9) td { background-color: var(--row-grey-3); }
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
        /* Domestic TIC has five rows (no Foord global charges) — TER and
           Transaction costs print white like the reference. */
        .tic-table table tbody tr:nth-child(1) td,
        .tic-table table tbody tr:nth-child(5) td {
            background-color: var(--white);
        }
        .tic-footnote {
            margin: 1.2mm 0 2.5mm 0;
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
        }
        td.change-cell .change-arrow-up { color: #000; }
        td.change-cell .change-arrow-down { color: #7A9CB4; }

        /* =====================================================
           CHARTS SECTION
           ===================================================== */
        .charts-row {
            display: flex;
            gap: 6mm;
            /* Reference rhythm: ~8mm between the TOP 10 table and the chart
               title; x-axis lands at 211.3mm; ~10mm from the legend down to
               the PORTFOLIO PERFORMANCE heading (header band at 238.4mm). */
            margin: 10mm 0 9mm 0;
        }

        /* Single chart at the left of the content column — sized so the
           Highcharts plot area lands on the reference's ≈52.3mm width
           (plot x 69.8→122.1mm on the measured March 2026 reference). */
        .chart-container {
            flex: none;
            width: 70mm;
            min-width: 0;
        }

        .chart-title sup {
            font-size: 4.5pt;
            line-height: 0;
            vertical-align: super;
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

        /* Reference plot is 33mm tall (axis top 178.3mm → x-axis 211.3mm);
           the extra ~12.5mm covers the x labels and legend. The title sits
           ~7mm above the plot top. */
        .chart-wrapper {
            height: 45.5mm;
            position: relative;
        }
        .chart-container .chart-title {
            margin-bottom: 5.3mm;
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
            left: -12.5mm;
            /* Vertically centred on the 33mm plot (plot middle ≈192mm) */
            top: -5.3mm;
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
            margin: 10mm 5.2mm 0 9.15mm;
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

        /* PERFORMANCE FEES: the reference runs its two data paragraphs as a
           plain line break (no paragraph spacing) — collapse the
           .fee-description top margin between consecutive paragraphs. */
        .performance-fees-text + .performance-fees-text {
            margin-top: 0;
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
                    // Exact order from the 820 reference PDF: NEW INVESTMENTS
                    // replaces the minimums row and there is no FOREIGN ASSETS
                    // section — the fund is domestic only.
                    $sidebarOrder = [
                        'domicile',
                        'managementCompany',
                        'fundManagers',
                        'inceptionDate',
                        'baseCurrency',
                        'equityIndicator',
                        'category',
                        'benchmark',
                        'newInvestments',
                        'portfolioSize',
                        'unitPrice',
                        'numberOfUnits',
                        'lastDistributions',
                        'incomeDistributions',
                        'incomeCharacteristics',
                        'portfolioOrientation',
                        'significantRestrictions',
                        'riskOfLoss',
                        'timeHorizon',
                        'isinNumber'
                    ];

                    $sidebar = $fund->data['sidebar'] ?? [];

                    // Label mapping
                    $labels = [
                        'domicile' => 'DOMICILE',
                        'managementCompany' => 'MANAGEMENT COMPANY',
                        'fundManagers' => 'FUND MANAGER',
                        'inceptionDate' => 'INCEPTION DATE',
                        'baseCurrency' => 'BASE CURRENCY',
                        'equityIndicator' => 'EQUITY INDICATOR',
                        'category' => 'CATEGORY',
                        'benchmark' => 'BENCHMARK',
                        'newInvestments' => 'NEW INVESTMENTS',
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
                                    // 820 reference: 6 of 10 dots filled.
                                    $filled = $value['filled'] ?? 6;
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

                    <div class="table-container aa-table">
                        <table>
                            <thead>
                                <tr>
                                    @foreach ($fund->data['mainContent']['assetAllocation']['headers'] as $header)
                                        <th>{!! $renderTh(strip_tags((string) $header)) !!}</th>
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
                                        <td>{!! $renderAssetName($row) !!}</td>
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
                            {{-- 820 reference: three columns only (every holding is ZAF,
                                 so there is no MARKET column) and no pink Foord-fund
                                 rows — the fade runs plain grey top to bottom. --}}
                            <tbody>
                                @foreach ($fund->data['mainContent']['topInvestments']['rows'] as $idx => $row)
                                    <tr class="{{ ($row['highlight'] ?? false) ? 'highlight-row' : '' }}">
                                        <td>{{ $row['security'] }}</td>
                                        <td>{{ $row['assetClass'] }}</td>
                                        <td>{{ $fmt($row['percentage'] ?? '', 1) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                <!-- Chart — the 820 reference has a single Fund-vs-Benchmark cash
                     chart on the left of the content column (plot ≈52×33mm,
                     measured from the March 2026 reference); there is no
                     inflation chart or explanation paragraph. -->
                @if(isset($fund->data['mainContent']['charts']['portfolioData']))
                    <div class="charts-row">
                        <div class="chart-container">
                            <h4 class="chart-title">PORTFOLIO PERFORMANCE VS BENCHMARK<sup>3,4,5</sup></h4>
                            <div class="chart-wrapper">
                                <div class="chart-ytitle">Cash Value<sup>2</sup> (R&rsquo;000)</div>
                                <div id="portfolioChart"></div>
                            </div>
                        </div>
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
                    <h3 class="section-heading">{!! $normaliseSupers(e($fund->data['mainContent']['performanceTable']['title'] ?? 'PORTFOLIO PERFORMANCE % (PERIODS GREATER THAN ONE YEAR ARE ANNUALISED¹)')) !!}</h3>

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
                                        // 820 reference markers: "Fund ³,⁵", "Benchmark ³,⁴",
                                        // "Fund highest/lowest ³,⁶" (⁵ = nil fees before
                                        // 2 June 2025, ⁴ = the benchmark-change footnote).
                                        if (preg_match('/^fund\s+(highest|lowest)/i', $nameStr)) {
                                            if (strpos($displayName, '3,6') === false && strpos($displayName, '³,⁶') === false) {
                                                $displayName .= ' <sup>3,6</sup>';
                                            }
                                        } elseif (stripos($nameStr, 'fund') === 0 && strpos($displayName, '3,5') === false && strpos($displayName, '³,⁵') === false) {
                                            $displayName .= ' <sup>3,5</sup>';
                                        } elseif (stripos($nameStr, 'benchmark') === 0 && strpos($displayName, '³,⁴') === false && strpos($displayName, '3,4') === false) {
                                            $displayName .= ' <sup>3,4</sup>';
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
                                            {{-- The B2 reference stars the TER row ("Total expense
                                                 ratio (TER)*") to point at the estimated-fee
                                                 footnote below the table. --}}
                                            <td>{{ $row['name'] }}@if(isset($fund->data['fees']['totalInvestmentCharge']['footnote']) && str_contains($row['name'], '(TER)'))*@endif</td>
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
                            <p class="footnotes tic-footnote">{{ $fund->data['fees']['totalInvestmentCharge']['footnote'] }}</p>
                        @endif
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
                            @php
                                // Column letters come from the stored headers: the B2
                                // sheet has four example columns (A–D), the B3 sheet
                                // three (A–C).
                                $pfeCols = array_map(
                                    fn ($h) => strtolower(trim($h)),
                                    array_slice($fund->data['fees']['performanceFeeExamples']['headers'] ?? ['', 'A', 'B', 'C', 'D'], 1)
                                );
                            @endphp
                            <tbody>
                                @foreach ($fund->data['fees']['performanceFeeExamples']['rows'] as $row)
                                    <tr>
                                        <td>{{ $row['name'] }}</td>
                                        @foreach ($pfeCols as $col)
                                            <td>{{ $fmt($row[$col] ?? '', 1) }}</td>
                                        @endforeach
                                    </tr>
                                @endforeach
                                <tr class="total-row">
                                    <td>{{ $fund->data['fees']['performanceFeeExamples']['total']['name'] ?? 'Annual fee rate applied (excl. VAT)' }}</td>
                                    @foreach ($pfeCols as $col)
                                        <td>{!! is_numeric($fund->data['fees']['performanceFeeExamples']['total'][$col] ?? null) ? $fmt($fund->data['fees']['performanceFeeExamples']['total'][$col], 1) : e($fund->data['fees']['performanceFeeExamples']['total'][$col] ?? '') !!}</td>
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
    @if(isset($fund->data['mainContent']['charts']['portfolioData']))
    <script src="https://cdn.jsdelivr.net/npm/highcharts@11/highcharts.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
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

            const formatXTickPortfolio = (label) => {
                if (!label) return '';
                const m = label.match(/^(\d{4})-(\d{2})$/);
                if (!m) return label;
                const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                return months[parseInt(m[2], 10) - 1] + ' ' + m[1].slice(-2);
            };

            // Portfolio Performance vs Benchmark
            if (portfolioData.length > 0) {
                const lastFund = portfolioData[portfolioData.length - 1].fund;
                const lastBenchmark = portfolioData[portfolioData.length - 1].benchmark;
                // The series end almost together, so the reference stacks the two
                // end labels (higher value above, lower below) instead of letting
                // them overlap at their exact y positions.
                const labelGapPx = 11;
                const separate = Math.abs(lastFund - lastBenchmark) < 30;
                const fundLabelY = separate ? (lastFund >= lastBenchmark ? -labelGapPx / 2 : labelGapPx / 2) : 0;
                const benchmarkLabelY = separate ? (lastBenchmark > lastFund ? -labelGapPx / 2 : labelGapPx / 2) : 0;
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

                // Calendar-aligned ticks every TWO years anchored on the first FULL
                // month (the 820 reference labels Jan 14, Jan 16, … Jan 26 — the
                // shorter history halves the balanced fund's 4-year pitch; the 100
                // baseline point sits one month earlier and carries no tick).
                const portfolioDates = portfolioData.map(d => d.date);
                const portfolioTickPositions = (function () {
                    const idxByDate = {};
                    portfolioDates.forEach((d, i) => { idxByDate[d] = i; });
                    const anchor = portfolioDates.length > 1 ? portfolioDates[1] : portfolioDates[0];
                    const anchorIdx = portfolioDates.length > 1 ? 1 : 0;
                    const firstYear = parseInt(anchor.slice(0, 4), 10);
                    const month = anchor.slice(5, 7);
                    const positions = [anchorIdx];
                    for (let y = firstYear + 2; y <= 2040; y += 2) {
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
                            // textOverflow 'none' stops Highcharts ellipsizing the
                            // final "Jan 26" label at the plot edge.
                            style: { fontSize: '8px', color: '#000', textOverflow: 'none', whiteSpace: 'nowrap' },
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
                            y: 8,
                            style: { fontSize: '8px', color: '#000' },
                            formatter: function () {
                                return this.value === 100 ? '100' : '';
                            },
                        },
                    },
                    legend: {
                        // Long thin rule swatches like the reference (the 820 print
                        // shows a clearly visible ~0.5mm rule, so 2px not 1px).
                        itemStyle: { fontSize: '8px', fontWeight: 'normal', color: colors.darkNavy },
                        symbolWidth: 16,
                        symbolHeight: 2,
                        symbolRadius: 0,
                        itemDistance: 40,
                        margin: 6,
                        padding: 0,
                    },
                    tooltip: { enabled: false },
                    plotOptions: {
                        spline: { marker: { enabled: false }, lineWidth: 1.75 },
                        // Default lineMarker legend symbol (marker disabled) draws the
                        // reference's long thin rule; the 'rectangle' symbol renders as
                        // a 2px dot under the bundled Highcharts 11 build.
                        series: { animation: false },
                    },
                    series: [
                        {
                            name: 'Fund', data: portfolioData.map(d => d.fund), color: colors.naartjie,
                            dataLabels: [{
                                enabled: true, align: 'left', verticalAlign: 'middle', x: 6, y: fundLabelY,
                                style: { fontSize: '9px', fontWeight: '500', color: colors.naartjie, textOutline: 'none' },
                                formatter: function () { return this.point.index === this.series.data.length - 1 ? formatCashLabel(this.y) : null; },
                                crop: false, overflow: 'allow', allowOverlap: true,
                            }],
                        },
                        {
                            name: 'Benchmark', data: portfolioData.map(d => d.benchmark), color: colors.darkNavy,
                            dataLabels: [{
                                enabled: true, align: 'left', verticalAlign: 'middle', x: 6, y: benchmarkLabelY,
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
</body>
</html>
