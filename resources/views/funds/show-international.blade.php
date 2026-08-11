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
           FOORD INTERNATIONAL FUND FACT SHEET
           Page geometry, typography and table styling ported from
           the signed-off balanced templates (pdf.blade.php /
           show.blade.php); colours inverted per the 875 reference
           (red title banner, navy date badge).
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
           TITLE BANNER — 34mm tall, naartjie (875 reference);
           text inset 7.75mm, same as the signed-off navy banner.
           ===================================================== */
        .fund-banner {
            background-color: var(--naartjie);
            color: var(--white);
            height: 34mm;
            box-sizing: border-box;
            padding: 3.6mm 6mm 0 7.75mm;
            margin: 0;
            width: 100%;
        }

        .fund-banner h1 {
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 500;
            font-size: 23pt;
            letter-spacing: 0.01em;
            text-transform: uppercase;
            margin: 0 0 1.1mm 0;
            line-height: 1.05;
        }

        .fund-banner h1 .class-suffix {
            font-weight: 500;
            font-size: 15pt;
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
            padding: 5.4mm 4mm 4mm 8mm;
            overflow: hidden;
        }

        .sidebar-section { margin-bottom: 1.35mm; }
        .sidebar-section:last-child { margin-bottom: 0; }

        .sidebar-section h3 {
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 500;
            font-size: 8pt;
            line-height: 10.5pt;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            color: #000;
            margin: 0;
        }

        .sidebar-section p,
        .sidebar-section .sidebar-value {
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 400;
            font-size: 8pt;
            line-height: 10.5pt;
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

        /* Lipper award — reference: logo left-aligned, no box */
        .lipper-award {
            margin-top: 4mm;
        }

        .lipper-award .award-logo {
            width: 40mm;
            height: auto;
            display: block;
        }

        .lipper-award .award-detail {
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 400;
            font-size: 8pt;
            line-height: 10.5pt;
            letter-spacing: 0.01em;
            color: #000;
            margin-top: 3mm;
        }

        /* === Content area — x=64mm → 204mm (140mm wide) === */
        .content-area {
            flex: 1;
            padding: 5.4mm 6mm 4mm 4mm;
            min-width: 0;
            overflow: hidden;
        }

        /* === Section headings — 9.5pt Avenir Next Medium, dark navy
           (875 reference: heading caps ~17px at 150 dpi) === */
        .section-heading {
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 500;
            font-size: 9.5pt;
            line-height: 11.4pt;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            color: var(--dark-navy);
            margin: 0 0 0.8mm 0;
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
            font-size: 9.5pt;
            line-height: 11.4pt;
            letter-spacing: 0.01em;
            color: var(--dark-navy);
            margin: -0.5mm 0 0.9mm 0;
        }

        /* === Two-column layout === */
        .two-col {
            display: flex;
            gap: 6mm;
            margin-bottom: 4.2mm;
        }

        .two-col .col-left { flex: 1; min-width: 0; }
        .two-col .col-right { flex: 1; min-width: 0; }

        /* === Asset allocation bars === */
        .alloc-row {
            display: flex;
            align-items: center;
            gap: 1mm;
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-size: 7.5pt;
            line-height: 4.5mm;
            color: #000;
        }

        /* Reference: labels flush left, bars start after the longest label */
        .alloc-label {
            width: 25mm;
            text-align: left;
            padding-right: 1mm;
            flex-shrink: 0;
            font-weight: 400;
        }

        .alloc-bar-container {
            flex: 1;
            height: 2.4mm;
            position: relative;
        }

        .alloc-bar {
            height: 2.4mm;
            background-color: var(--naartjie);
        }

        .alloc-value {
            width: 5mm;
            text-align: right;
            flex-shrink: 0;
            font-weight: 400;
        }

        .alloc-change {
            width: 9mm;
            text-align: right;
            flex-shrink: 0;
            font-size: 7.5pt;
        }

        /* Reference arrows: black ▲ for up, steel-blue ▼ for down; the number
           stays black. Zero changes carry no arrow. */
        .change-up { color: #000; }
        .change-down { color: #000; }
        .change-up::before { content: '▲ '; font-size: 5.1pt; color: #000; }
        .change-down::before { content: '▼ '; font-size: 5.1pt; color: var(--light-blue); }

        /* === Equity sector bars === */
        .sector-row {
            display: flex;
            align-items: center;
            gap: 1mm;
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-size: 7.5pt;
            line-height: 4.5mm;
            color: #000;
        }

        .sector-label {
            width: 34mm;
            text-align: left;
            padding-right: 1mm;
            flex-shrink: 0;
            font-weight: 400;
        }

        .sector-bar-container {
            flex: 1;
            height: 2.4mm;
            position: relative;
        }

        .sector-bar {
            height: 2.4mm;
            background-color: var(--naartjie);
        }

        .sector-value {
            width: 5mm;
            text-align: right;
            flex-shrink: 0;
            font-weight: 400;
        }

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
            padding: 0.6mm 1.4mm 0.6mm 1.5mm;
        }

        .foord-table th:first-child { text-align: left; }

        .foord-table td {
            background-color: var(--row-grey-2);
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 400;
            font-size: 7.5pt;
            line-height: 8.5pt;
            padding: 0.62mm 1.4mm 0.62mm 1.5mm;
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

        /* Geographic exposure — region 40%, three equal numeric columns */
        .geo-table .foord-table th:first-child,
        .geo-table .foord-table td:first-child { width: 45.5%; }
        .geo-table .foord-table td { background-color: var(--row-grey-2); }

        /* Top 10 — SECURITY 40.1%, ASSET CLASS 28.3% (left), MARKET and
           % OF FUND centred; row backgrounds fade in pairs. */
        .top10-table .foord-table td,
        .top10-table .foord-table th {
            padding-top: 0.45mm;
            padding-bottom: 0.45mm;
        }
        .top10-table .foord-table td:first-child,
        .top10-table .foord-table th:first-child {
            width: 40.1%;
            padding-left: 2.1mm;
        }
        .top10-table .foord-table td:nth-child(2),
        .top10-table .foord-table th:nth-child(2) {
            text-align: left;
            width: 28.3%;
            padding-left: 2.9mm;
        }
        .top10-table .foord-table td:nth-child(3),
        .top10-table .foord-table th:nth-child(3),
        .top10-table .foord-table td:nth-child(4),
        .top10-table .foord-table th:nth-child(4) {
            text-align: center;
            padding-left: 0.6mm;
        }
        /* Row-grey ramp measured off the 875 reference: rows 1-3 #d4d4d4,
           row 4 #dddddd, 5-6 #e6e6e6, 7-8 #ebebeb, 9-10 #f0f0f0. */
        .top10-table .foord-table tbody tr:nth-child(1) td,
        .top10-table .foord-table tbody tr:nth-child(2) td,
        .top10-table .foord-table tbody tr:nth-child(3) td { background-color: var(--row-grey-0); }
        .top10-table .foord-table tbody tr:nth-child(4) td { background-color: var(--row-grey-1); }
        .top10-table .foord-table tbody tr:nth-child(5) td,
        .top10-table .foord-table tbody tr:nth-child(6) td { background-color: var(--row-grey-2); }
        .top10-table .foord-table tbody tr:nth-child(7) td,
        .top10-table .foord-table tbody tr:nth-child(8) td { background-color: var(--row-grey-3); }
        .top10-table .foord-table tbody tr:nth-child(9) td,
        .top10-table .foord-table tbody tr:nth-child(10) td { background-color: var(--row-grey-4); }

        .top10-table { margin-bottom: 4.2mm; }

        /* Performance table — column grid measured off the 875 reference
           (separators at 533/644/760/833/908/982/1056/1130 px @150 dpi):
           name 18.75%, cash 13.42%, since inception 14.03%, then
           8.83/9.07/8.95/8.95/8.95 and the remainder for THIS MONTH. */
        .perf-table th {
            font-size: 7pt;
            line-height: 8.7pt;
            text-align: right;
            padding: 0.35mm 0.5mm;
            vertical-align: bottom;
        }
        .perf-table th:first-child {
            text-align: left;
            width: 18.75%;
            padding-left: 1.5mm;
        }
        .perf-table th:nth-child(2) { width: 13.42%; }
        .perf-table th:nth-child(3) { width: 14.03%; }
        .perf-table th:nth-child(4) { width: 8.83%; }
        .perf-table th:nth-child(5) { width: 9.07%; }
        .perf-table th:nth-child(6) { width: 8.95%; }
        .perf-table th:nth-child(7) { width: 8.95%; }
        .perf-table th:nth-child(8) { width: 8.95%; }
        .perf-table td {
            color: #000;
            font-size: 7.5pt;
            line-height: 8pt;
            padding: 0.45mm 0.5mm;
        }
        .perf-table td:first-child { padding-left: 1.5mm; }
        /* Row greys fade down the table (measured off the 875 reference):
           Peer group #d4d4d4, comparators #dddddd, euro/sterling rows
           #e6e6e6, highest/lowest #f0f0f0. */
        .perf-table tbody tr td { background-color: var(--row-grey-1); }
        .perf-table tbody tr:nth-child(1) td { background-color: var(--naartjie-20); }
        .perf-table tbody tr:nth-child(2) td { background-color: var(--row-grey-0); }
        .perf-table tbody tr:nth-child(7) td,
        .perf-table tbody tr:nth-child(8) td { background-color: var(--row-grey-2); }
        .perf-table tbody tr:nth-child(9).empty-row td { background-color: var(--row-grey-3) !important; }
        .perf-table tbody tr:nth-child(10) td,
        .perf-table tbody tr:nth-child(11) td { background-color: var(--row-grey-4); }
        .perf-table tbody tr td.cell-empty { background-color: var(--white); }

        /* Annualised cost ratio — reference: three equal ~46.4mm columns,
           headers and values centred */
        .cost-table .foord-table th:first-child,
        .cost-table .foord-table td:first-child {
            width: 33.2%;
            padding-left: 1.6mm;
        }
        .cost-table .foord-table th:not(:first-child),
        .cost-table .foord-table td:not(:first-child) { text-align: center; }
        .cost-table .foord-table th { font-size: 6pt; }
        .cost-table .foord-table td {
            font-size: 7pt;
            padding-top: 0.92mm;
            padding-bottom: 0.92mm;
        }
        .cost-table .foord-table tr.total-row td {
            font-size: 7pt;
            font-weight: 500;
            padding-top: 0.95mm;
            padding-bottom: 0.95mm;
        }

        /* === Chart === */
        .chart-wrapper {
            height: 47mm;
            position: relative;
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
            margin-top: 1mm;
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-size: 6pt;
            color: #4d585e;
        }

        .chart-legend span {
            display: flex;
            align-items: center;
            gap: 1mm;
        }

        .legend-line {
            width: 4.8mm;
            height: 0.2mm;
            display: inline-block;
        }

        /* === Footnotes === */
        .footnote {
            font-family: 'Lato', 'Avenir Next', sans-serif;
            font-size: 6pt;
            line-height: 7.2pt;
            color: var(--dark-navy);
            letter-spacing: 0.01em;
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

        /* Reference: 8.5pt Lato Light on a 9.6pt leading */
        .info-sidebar-content p,
        .important-info-text {
            font-family: 'Lato', 'Avenir Next', sans-serif;
            font-weight: 300;
            font-size: 8.5pt;
            line-height: 9.6pt;
            letter-spacing: 0.01em;
            color: var(--dark-navy);
            margin: 0 0 0.7mm 0;
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
            /* ANNUALISED COST RATIO % table header lands at y=31.7mm; the
               main column spans x 65.2mm → 202.9mm (875 reference) */
            padding: 26.8mm 7.1mm 4mm 5.2mm;
            min-width: 0;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .page2-section { margin-bottom: 5.6mm; }

        .page2-heading {
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 500;
            font-size: 9.5pt;
            line-height: 11.4pt;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            color: var(--dark-navy);
            margin: 0 0 1.5mm 0;
        }

        .page2-body {
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 400;
            font-size: 8.2pt;
            line-height: 10.1pt;
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

            <!-- Fund Name Banner (naartjie per the 875 reference) -->
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
                    <span x-data="editableField('fund.name', '{{ addslashes($fund->data['fund']['name'] ?? $fund->name) }}', 'fundName')"
                          @click="editMode && startEdit()"
                          :class="editMode ? 'editable' : ''">{{ mb_strtoupper($mainName) }}@if($classText) <span class="class-suffix">&mdash; {{ $classText }}</span>@endif</span>
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
                            $labelMap = [
                                'marketingCommunication' => 'MARKETING COMMUNICATION',
                                'subInvestmentManager' => 'SUB-INVESTMENT MANAGER',
                                'monthEndSharePrice' => 'MONTH END SHARE PRICE',
                                'morningstarCategory' => 'MORNINGSTAR CATEGORY',
                                'initialInvestmentAmount' => 'INITIAL INVESTMENT AMOUNT',
                                'totalFundSize' => 'TOTAL FUND SIZE',
                                'numberOfShares' => 'NUMBER OF SHARES',
                                'investmentManager' => 'INVESTMENT MANAGER',
                                'managementCompany' => 'MANAGEMENT COMPANY',
                                'fundManagers' => 'FUND MANAGERS',
                                'inceptionDate' => 'INCEPTION DATE',
                                'baseCurrency' => 'BASE CURRENCY',
                                'equityIndicator' => 'EQUITY INDICATOR',
                                'typeOfShares' => 'TYPE OF SHARES',
                                'timeHorizon' => 'TIME HORIZON',
                                'domicile' => 'DOMICILE',
                                'depository' => 'DEPOSITORY',
                                'isinNumber' => 'ISIN NUMBER',
                                'fees' => 'FEES',
                                'lipperAward' => 'REFINITIV LIPPER FUND AWARDS',
                            ];

                            // Define display order to match PDF
                            $displayOrder = [
                                'marketingCommunication', 'domicile', 'managementCompany', 'depository',
                                'investmentManager', 'subInvestmentManager', 'fundManagers',
                                'inceptionDate', 'baseCurrency', 'equityIndicator',
                                'morningstarCategory', 'typeOfShares', 'initialInvestmentAmount',
                                'totalFundSize', 'monthEndSharePrice', 'numberOfShares',
                                'timeHorizon', 'fees', 'isinNumber', 'lipperAward'
                            ];
                        @endphp

                        @foreach ($displayOrder as $key)
                            @if(isset($sidebar[$key]))
                                @php
                                    $value = $sidebar[$key];
                                    $label = $labelMap[$key] ?? strtoupper(implode(' ', preg_split('/(?=[A-Z])/', $key, -1, PREG_SPLIT_NO_EMPTY)));
                                @endphp

                                @if ($key === 'lipperAward' && is_array($value))
                                    {{-- Reference: the Refinitiv Lipper Fund Awards logo
                                         (extracted from the published 875 fact sheet),
                                         left-aligned with the award detail lines below. --}}
                                    <div class="lipper-award">
                                        <img src="{{ asset('images/lipper-award.png') }}" alt="Refinitiv Lipper Fund Awards {{ $value['year'] ?? '' }} Winner {{ $value['region'] ?? '' }}" class="award-logo">
                                        <div class="award-detail">
                                            Refinitiv Lipper Awards {{ $value['year'] ?? '' }}<br>
                                            {{ $value['category'] ?? '' }}<br>
                                            {{ $value['type'] ?? '' }}
                                        </div>
                                    </div>
                                @elseif ($key === 'equityIndicator' && is_array($value))
                                    <div class="sidebar-section">
                                        @php
                                            $filledDots = $value['filled'] ?? 7;
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
                                    {{-- Reference: larger bold black label with a clear gap below --}}
                                    <div class="sidebar-section" style="margin-bottom: 2.6mm;">
                                        <h3 style="font-size: 7pt; line-height: 8.2pt; font-weight: 700;">{{ $label }}</h3>
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
                    <!-- Two-column: Asset Allocation + Geographic Exposure -->
                    <div class="two-col">
                        <!-- Left: Asset Allocation -->
                        <div class="col-left">
                            @if(isset($fund->data['mainContent']['assetAllocation']))
                                <div style="margin-bottom: 8px;">
                                    <h3 class="section-heading">
                                        <span x-data="editableField('mainContent.assetAllocation.title', '{{ addslashes($fund->data['mainContent']['assetAllocation']['title']) }}', 'headingSuffix')"
                                              @click="editMode && startEdit()"
                                              :class="editMode ? 'editable' : ''">{!! $renderHeading($fund->data['mainContent']['assetAllocation']['title']) !!}</span>
                                    </h3>
                                    <p class="section-subtitle">
                                        <span x-data="editableField('mainContent.assetAllocation.subtitle', '{{ $fund->data['mainContent']['assetAllocation']['subtitle'] }}')"
                                              @click="editMode && startEdit()"
                                              :class="editMode ? 'editable' : ''"
                                              x-text="value"></span>
                                    </p>
                                    @php
                                        /* Reference bar scale: the largest value spans ~95%
                                           of the bar area; all bars are relative to it. */
                                        $allocMax = max(1.0, ...array_map(
                                            fn ($r) => (float) ($r['value'] ?? $r['total'] ?? 0),
                                            $fund->data['mainContent']['assetAllocation']['rows']
                                        ));
                                    @endphp
                                    <div>
                                        @foreach ($fund->data['mainContent']['assetAllocation']['rows'] as $rowIndex => $row)
                                            <div class="alloc-row">
                                                <span class="alloc-label">
                                                    <span x-data="editableField('mainContent.assetAllocation.rows.{{ $rowIndex }}.name', '{{ $row['name'] }}')"
                                                          @click="editMode && startEdit()"
                                                          :class="editMode ? 'editable' : ''"
                                                          x-text="value"></span>
                                                </span>
                                                <div class="alloc-bar-container">
                                                    <div class="alloc-bar" style="width: {{ round((float) ($row['value'] ?? $row['total'] ?? 0) / $allocMax * 95, 1) }}%;"></div>
                                                </div>
                                                <span class="alloc-value">
                                                    <span x-data="editableField('mainContent.assetAllocation.rows.{{ $rowIndex }}.value', '{{ ($row['value'] ?? $row['total'] ?? '') }}')"
                                                          @click="editMode && startEdit()"
                                                          :class="editMode ? 'editable' : ''"
                                                          x-text="value"></span>
                                                </span>
                                                @php
                                                    /* The stored change already carries the arrow glyph
                                                       ('▼ 2.4') — display only the number; the arrow is
                                                       drawn by the change-up/down ::before. Zero changes
                                                       show no arrow (reference). */
                                                    $changeNumber = trim(str_replace(['▲', '▼'], '', (string) ($row['change'] ?? '')));
                                                    $isZeroChange = is_numeric($changeNumber) && (float) $changeNumber == 0.0;
                                                    $changeClass = $isZeroChange ? '' : ((($row['changeDirection'] ?? '') === 'up') ? 'change-up' : ((($row['changeDirection'] ?? '') === 'down') ? 'change-down' : ''));
                                                @endphp
                                                <span class="alloc-change {{ $changeClass }}">{{ $changeNumber }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Equity Sector Allocation (rows live inside the
                                 asset_allocation JSON as `equitySectors`) -->
                            @if(!empty($fund->data['mainContent']['assetAllocation']['equitySectors']))
                                <div style="margin-bottom: 2mm;">
                                    <h3 class="section-heading">EQUITY SECTOR ALLOCATION %</h3>
                                    @php
                                        $sectorMax = max(1.0, ...array_map(
                                            fn ($r) => (float) ($r['percentage'] ?? 0),
                                            $fund->data['mainContent']['assetAllocation']['equitySectors']
                                        ));
                                    @endphp
                                    <div>
                                        @foreach ($fund->data['mainContent']['assetAllocation']['equitySectors'] as $rowIndex => $row)
                                            <div class="sector-row">
                                                <span class="sector-label">
                                                    <span x-data="editableField('mainContent.assetAllocation.equitySectors.{{ $rowIndex }}.name', '{{ $row['name'] }}')"
                                                          @click="editMode && startEdit()"
                                                          :class="editMode ? 'editable' : ''"
                                                          x-text="value"></span>
                                                </span>
                                                <div class="sector-bar-container">
                                                    <div class="sector-bar" style="width: {{ round((float) ($row['percentage'] ?? 0) / $sectorMax * 63, 1) }}%;"></div>
                                                </div>
                                                <span class="sector-value">
                                                    <span x-data="editableField('mainContent.assetAllocation.equitySectors.{{ $rowIndex }}.percentage', '{{ $row['percentage'] ?? '' }}')"
                                                          @click="editMode && startEdit()"
                                                          :class="editMode ? 'editable' : ''"
                                                          x-text="value"></span>
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Right: Geographic Exposure + Chart -->
                        <div class="col-right">
                            @if(!empty($fund->data['mainContent']['assetAllocation']['geographicExposure']))
                                @php
                                    /* Geographic exposure lives inside the asset_allocation JSON
                                       (`geographicExposure` rows + `geographicTotals`). Zero
                                       values display as a dash, per the published fact sheet. */
                                    $geoRows = $fund->data['mainContent']['assetAllocation']['geographicExposure'];
                                    $geoTotals = $fund->data['mainContent']['assetAllocation']['geographicTotals'] ?? [];
                                    $geoFmt = fn ($v) => (is_numeric($v) && (float) $v == 0.0) ? '-' : $v;
                                @endphp
                                <div class="geo-table" style="margin-bottom: 2mm;">
                                    <h3 class="section-heading">GEOGRAPHIC EXPOSURE %</h3>
                                    <p class="section-subtitle">(Gross exposure)</p>
                                    <div class="table-wrapper">
                                        <table class="foord-table">
                                            <thead>
                                                <tr>
                                                    <th></th>
                                                    <th>TOTAL</th>
                                                    <th>EQUITY</th>
                                                    <th>CASH</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($geoRows as $rowIndex => $row)
                                                    <tr>
                                                        <td>
                                                            <span x-data="editableField('mainContent.assetAllocation.geographicExposure.{{ $rowIndex }}.name', '{{ $row['name'] }}')"
                                                                  @click="editMode && startEdit()"
                                                                  :class="editMode ? 'editable' : ''"
                                                                  x-text="value"></span>
                                                        </td>
                                                        <td>{{ $geoFmt($row['total']) }}</td>
                                                        <td>{{ $geoFmt($row['equity']) }}</td>
                                                        <td>{{ $geoFmt($row['cash']) }}</td>
                                                    </tr>
                                                @endforeach
                                                <tr class="total-row">
                                                    <td>{{ $geoTotals['name'] ?? 'TOTAL' }}</td>
                                                    <td>{{ $geoTotals['total'] ?? '' }}</td>
                                                    <td>{{ $geoTotals['equity'] ?? '' }}</td>
                                                    <td>{{ $geoTotals['cash'] ?? '' }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif

                            <!-- Portfolio Performance Chart -->
                            @if(isset($fund->data['mainContent']['charts']))
                                <div>
                                    <h3 class="section-heading">
                                        <span x-data="editableField('mainContent.charts.title', '{{ $fund->data['mainContent']['charts']['title'] ?? 'PORTFOLIO PERFORMANCE' }}')"
                                              @click="editMode && startEdit()"
                                              :class="editMode ? 'editable' : ''"
                                              x-text="value"></span>
                                    </h3>
                                    <div class="chart-wrapper">
                                        <div class="chart-ytitle">Cash Value<sup>2</sup> ($&rsquo;000)</div>
                                        <canvas id="performanceChart"></canvas>
                                    </div>
                                    {{-- Legend colours per the 875 reference: Fund red, US inflation
                                         dark navy, World equities steel blue, World bonds light grey --}}
                                    <div class="chart-legend" style="max-width: 52mm; margin-left: auto; margin-right: auto;">
                                        <span><span class="legend-line" style="background: var(--naartjie);"></span> Fund</span>
                                        <span><span class="legend-line" style="background: var(--dark-navy);"></span> US inflation</span>
                                        <span><span class="legend-line" style="background: var(--light-blue);"></span> World equities</span>
                                        <span><span class="legend-line" style="background: #c9c9c9;"></span> World bonds</span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

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
                                                    <span x-data="editableField('mainContent.topInvestments.rows.{{ $rowIndex }}.assetClass', '{{ $row['assetClass'] }}')"
                                                          @click="editMode && startEdit()"
                                                          :class="editMode ? 'editable' : ''"
                                                          x-text="value"></span>
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
                                        {{-- International display rules (875 reference, mirrored in
                                             pdf-international.blade.php): the import writes raw row
                                             names (Fund, Benchmark, Comparator 2…6, Fund highest/
                                             lowest); the fact sheet displays them renamed with
                                             footnote superscripts, ordered Fund/Peer group/US
                                             inflation/World equities/World bonds, spacer, euro and
                                             sterling fund rows, spacer, highest/lowest. --}}
                                        @php
                                            $perfRowsRaw = $fund->data['mainContent']['performanceTable']['rows'];
                                            $perfColKeysIntl = $fund->data['mainContent']['performanceTable']['columnKeys'] ?? [];
                                            $intlNames = [
                                                'fund' => 'Fund <sup>3</sup>',
                                                'benchmark' => 'Peer group <sup>4</sup>',
                                                'comparator 2' => 'US inflation <sup>5</sup>',
                                                'comparator 3' => 'World equities <sup>6</sup>',
                                                'comparator 4' => 'World bonds <sup>7</sup>',
                                                'comparator 5' => 'Fund in euros <sup>3</sup>',
                                                'comparator 6' => 'Fund in sterling <sup>3</sup>',
                                                'fund highest' => 'Fund highest <sup>3,8</sup>',
                                                'fund lowest' => 'Fund lowest <sup>3,8</sup>',
                                            ];
                                            $intlOrder = [
                                                ['fund', 'benchmark', 'comparator 2', 'comparator 3', 'comparator 4'],
                                                ['comparator 5', 'comparator 6'],
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

                            {{-- Footnotes render on page 2 under NOTES (875 reference);
                                 see the page2Content.notes section below. --}}
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
                            @foreach ($fund->data['importantInfo']['paragraphs'] as $index => $paragraph)
                                <p class="important-info-text">
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
                    <!-- Annualised Cost Ratio -->
                    @if(isset($fund->data['fees']['annualisedCostRatio']))
                        <div class="page2-section cost-table">
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
                                                <td>
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
                            <p class="page2-body" style="margin-top: 5px;">
                                <span x-data="editableField('fees.annualisedCostRatio.description', '{{ addslashes($fund->data['fees']['annualisedCostRatio']['description'] ?? '') }}')"
                                      @click="editMode && startEdit()"
                                      :class="editMode ? 'editable' : ''"
                                      x-text="value"></span>
                            </p>
                        </div>
                    @endif

                    <!-- Share Pricing and Transactions -->
                    @if(isset($fund->data['page2Content']['sharePricing']))
                        <div class="page2-section">
                            <h3 class="page2-heading">
                                <span x-data="editableField('page2Content.sharePricing.title', '{{ $fund->data['page2Content']['sharePricing']['title'] }}')"
                                      @click="editMode && startEdit()"
                                      :class="editMode ? 'editable' : ''"
                                      x-text="value"></span>
                            </h3>
                            <p class="page2-body">
                                <span x-data="editableField('page2Content.sharePricing.text', '{{ addslashes($fund->data['page2Content']['sharePricing']['text']) }}', 'linkify')"
                                      @click="editMode && startEdit()"
                                      :class="editMode ? 'editable' : ''">{!! $linkify($fund->data['page2Content']['sharePricing']['text']) !!}</span>
                            </p>
                        </div>
                    @endif

                    <!-- More About the Fund -->
                    @if(isset($fund->data['page2Content']['moreAboutFund']))
                        <div class="page2-section">
                            <h3 class="page2-heading">
                                <span x-data="editableField('page2Content.moreAboutFund.title', '{{ $fund->data['page2Content']['moreAboutFund']['title'] }}')"
                                      @click="editMode && startEdit()"
                                      :class="editMode ? 'editable' : ''"
                                      x-text="value"></span>
                            </h3>
                            @foreach ($fund->data['page2Content']['moreAboutFund']['paragraphs'] as $index => $paragraph)
                                <p class="page2-body" style="margin-bottom: 2.2mm;">
                                    <span x-data="editableField('page2Content.moreAboutFund.paragraphs.{{ $index }}', '{{ addslashes($paragraph) }}', 'linkify')"
                                          @click="editMode && startEdit()"
                                          :class="editMode ? 'editable' : ''">{!! $linkify($paragraph) !!}</span>
                                </p>
                            @endforeach
                        </div>
                    @endif

                    <!-- Refinitiv Lipper Fund Award -->
                    @if(isset($fund->data['page2Content']['lipperAward']))
                        <div class="page2-section">
                            <h3 class="page2-heading">
                                <span x-data="editableField('page2Content.lipperAward.title', '{{ $fund->data['page2Content']['lipperAward']['title'] }}')"
                                      @click="editMode && startEdit()"
                                      :class="editMode ? 'editable' : ''"
                                      x-text="value"></span>
                            </h3>
                            <p class="page2-body">
                                <span x-data="editableField('page2Content.lipperAward.text', '{{ addslashes($fund->data['page2Content']['lipperAward']['text']) }}', 'linkify')"
                                      @click="editMode && startEdit()"
                                      :class="editMode ? 'editable' : ''">{!! $linkify($fund->data['page2Content']['lipperAward']['text']) !!}</span>
                            </p>
                        </div>
                    @endif

                    <!-- Notes (the performance-table footnotes, displayed on
                         page 2 per the 875 reference) -->
                    @if(!empty($fund->data['mainContent']['performanceTable']['footnotes']))
                        <div class="page2-section">
                            <h3 class="page2-heading">NOTES</h3>
                            <div>
                                @foreach ($fund->data['mainContent']['performanceTable']['footnotes'] as $index => $note)
                                    <p class="page2-note">
                                        <span x-data="editableField('mainContent.performanceTable.footnotes.{{ $index }}', '{!! addslashes($note) !!}')"
                                              @click="editMode && startEdit()"
                                              :class="editMode ? 'editable' : ''"
                                              x-html="value"></span>
                                    </p>
                                @endforeach
                            </div>
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
                                <p>E. <span x-data="editableField('footer.contact.email', '{{ $fund->data['footer']['contact']['email'] }}')"
                                           @click="editMode && startEdit()"
                                           :class="editMode ? 'editable' : ''"
                                           x-text="value"></span></p>
                                <p><span x-data="editableField('footer.contact.website', '{{ $fund->data['footer']['contact']['website'] }}')"
                                         @click="editMode && startEdit()"
                                         :class="editMode ? 'editable' : ''"
                                         x-text="value"></span></p>
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
        // Display formatters — keep the styled rendering (e.g. the smaller
        // "— CLASS R" suffix) after Alpine re-renders an edited value.
        const editableFormatters = {
            fundName(value) {
                const m = String(value).match(/^(.+?)\s*[—–-]\s*(CLASS\s+[A-Z][0-9]*)$/i);
                if (!m) return String(value).toUpperCase();
                return m[1].toUpperCase() + ' <span class="class-suffix">&mdash; ' + m[2].toUpperCase() + '</span>';
            },
            headingSuffix(value) {
                return String(value).replace(/\s*\(([^)]+)\)/, ' <span class="title-suffix">($1)</span>');
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

    @if(isset($fund->data['mainContent']['charts']))
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        @php
            $chartPerformanceData = $fund->data['mainContent']['charts']['performanceData'] ?? [];
        @endphp
        const chartData = @json($chartPerformanceData);

        const colors = {
            naartjie: '#d25347',
            darkNavy: '#29363d',
            lightGrey: '#c9c9c9',
            lightBlue: '#7a9cb4'
        };

        // Reference (875): Fund red, US inflation dark navy,
        // World equities steel blue, World bonds light grey.
        const lineColors = [colors.naartjie, colors.darkNavy, colors.lightBlue, colors.lightGrey];

        const monthNames = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        const formatChartDate = (label) => {
            const m = String(label).match(/^(\d{4})-(\d{2})$/);
            if (!m) return label;
            return monthNames[parseInt(m[2], 10) - 1] + ' ' + m[1].slice(-2);
        };

        // End value annotation plugin
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
                    const label = '$ ' + Math.round(lastValue).toLocaleString();
                    ctx.save();
                    ctx.font = 'bold 7px Avenir Next, Lato, sans-serif';
                    ctx.fillStyle = dataset.borderColor;
                    ctx.textAlign = 'left';
                    ctx.fillText(label, lastPoint.x + 4, lastPoint.y - 3);
                    ctx.restore();
                });
            }
        };

        Chart.register(endValuePlugin);

        const ctx = document.getElementById('performanceChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
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
                        label: 'US inflation',
                        data: chartData.map(d => d.usInflation),
                        borderColor: colors.darkNavy,
                        borderWidth: 1.5,
                        pointRadius: 0,
                        tension: 0.3,
                        fill: false
                    },
                    {
                        label: 'World equities',
                        data: chartData.map(d => d.worldEquities),
                        borderColor: colors.lightBlue,
                        borderWidth: 1.5,
                        pointRadius: 0,
                        tension: 0.3,
                        fill: false
                    },
                    {
                        label: 'World bonds',
                        data: chartData.map(d => d.worldBonds),
                        borderColor: colors.lightGrey,
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
                        border: { color: '#000' },
                        ticks: {
                            font: { size: 6, family: 'Avenir Next, Lato, sans-serif' },
                            color: '#535353',
                            maxRotation: 0,
                            autoSkip: false,
                            // Reference ticks: Mar 97, Mar 01, … — every 48
                            // months anchored on the first data point.
                            callback: function (value, index) {
                                return index % 48 === 0 ? formatChartDate(this.getLabelForValue(value)) : null;
                            }
                        }
                    },
                    y: {
                        display: true,
                        // Reference: LOG scale from the 100 baseline, no
                        // gridlines, only the 100 origin labelled.
                        type: 'logarithmic',
                        grid: { display: false },
                        border: { color: '#000' },
                        ticks: {
                            font: { size: 6, family: 'Avenir Next, Lato, sans-serif' },
                            color: '#535353',
                            callback: (value) => value === 100 ? '100' : null
                        },
                        min: 100,
                        beginAtZero: false
                    }
                },
                layout: {
                    padding: { right: 40, top: 10 }
                }
            }
        });
    </script>
    @endif
</body>
</html>
