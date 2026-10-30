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
    @include('funds.partials.avenir-fonts')
    @include('funds.partials.lato2-fonts')
    <style>
        /* =====================================================
           PRESCIENT FOORD ASIA EX-JAPAN FEEDER FUND (829)
           Cloned from the Prescient global equity feeder sheet
           (823, show-prescient-global-equity), which supplies the
           Prescient chrome unchanged: naartjie date badge, dark
           navy title banner with no class suffix, the MINIMUM
           DISCLOSURE DOCUMENT sidebar, page 2's FEE RATES / TIC /
           INVESTING OFFSHORE stack with no footer, and page 3's
           CONTACT DETAILS + GLOSSARY.

           829 is a NEW fund (incepted 30 September 2026) feeding
           the Foord Asia ex-Japan Fund (879), and its export
           carries the master fund's numbers. Deltas from 823, per
           the 30 Sept 2026 Class A reference in Funds/829/:
             - sidebar: INVESTMENT POLICY under CLASS (stored in
               `master_fund`), CATEGORY reads ASISA CLASSIFICATION,
               no DOMICILE row
             - page 1 follows the 879 master sheet: PORTFOLIO
               STRUCTURE % (no variance column) beside a two-column
               TOP 10, then the GEOGRAPHIC COUNTRY EXPOSURE pie
             - no performance chart or table: PORTFOLIO PERFORMANCE
               prints the "Regulation prohibits…" notice
             - ASSET ALLOCATION %, CONTRIBUTORS/DETRACTORS and
               POLICY OBJECTIVE move from page 2 to page 1, above a
               "* Figures are based on Master Fund at …" note
             - TIC headers carry an asterisk ("12 MONTHS*") keyed
               to an "Estimated as the fund was incepted less than a
               year ago" note
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
            /* 822 reference: naartjie badge (the 875/809 sheets use navy);
               the 829 reference prints the date in capitals. */
            background-color: var(--naartjie);
            text-transform: uppercase;
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
            /* Trello 429: the logo's right edge (the "D") lines up with
               the right edge of the tables (203.45mm); it overhung by 1mm. */
            right: 6.55mm;
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
            /* Trello 426: the standfirst sits closer under the title —
               5.84mm baseline to baseline, as on the August sheet (was 7.41). */
            margin: 0 0 1.33mm 0;
            line-height: 1.05;
        }

        /* Trello 426: the standfirst ends on the tables' right edge
           (203.46mm) so its lines break where the August sheet's do —
           "The" and "that is" start lines 3 and 4. */
        .fund-banner .description {
            font-family: 'Merriweather', Georgia, serif;
            font-weight: 400;
            font-size: 9pt;
            line-height: 11.3pt;
            letter-spacing: 0.01em;
            margin: 0 3.54mm 0 0;
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
            /* Trello 429: top of the first line of copy level with the
               right-hand column's (cap tops aligned across the two columns). */
            padding: 4.85mm 4mm 4mm 8mm;
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

        /* === Content area — the 829 reference's visible table cells span
           x=64.37mm → 203.61mm (PyMuPDF fills, white 1.1pt separators
           drawn over them), so the content box is padded to those edges.
           Headings and bar labels sit ~0.9mm in (x≈65.2mm). === */
        .content-area {
            flex: 1;
            /* Tables print 0.67mm short of their box's right edge (the
               negative border-spacing margins), hence the uneven pads. */
            padding: 5.05mm 5.72mm 4mm 4.45mm;
            min-width: 0;
            overflow: hidden;
        }

        /* === Section headings — dark navy. The upper three (PORTFOLIO
           STRUCTURE, TOP 10, GEOGRAPHIC COUNTRY EXPOSURE) are Avenir Next
           Medium 8pt on the 829 reference (8.04pt); the sections below
           the pie use page 2's 7.5pt .page2-heading. === */
        .section-heading {
            padding-left: 0.62mm;
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 500;
            font-size: 8pt;
            line-height: 9.4pt;
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

        /* === Row 1: PORTFOLIO STRUCTURE % beside TOP 10 (879 layout). The
           829 reference's top-10 cells span x=136.37 → 203.61mm, so the
           right column is a fixed 67.24mm and the left one takes the rest
           less a 2mm gutter. Its heading sits 1mm above the left one's
           (baselines 67.02 / 68.04mm). === */
        .two-col {
            display: flex;
            gap: 2mm;
            margin-bottom: 0;
        }

        .two-col .col-left { flex: 1; min-width: 0; }
        .two-col .col-right { flex: 0 0 67.8mm; min-width: 0; margin-top: -1mm; }

        /* =====================================================
           PORTFOLIO STRUCTURE % — the 879 master sheet's list: label,
           bar, value and change arrow, no variance column. Measured off
           the 829 reference: labels at x=65.24mm, bars from 98.17mm
           at 0.633mm per percentage point (least-squares fit through
           the origin; 29% = 18.30mm), 4.22mm bars on a 5.0mm pitch,
           value right-aligned at 122.27mm, arrow 123.26→125.62mm and
           the change right-aligned at 131.59mm.
           ===================================================== */
        .ps-section { margin-bottom: 0; }

        /* The reference leaves the "Change since" line's room empty: 10.96mm
           from the heading's baseline to the first row's. */
        .ps-header { margin-bottom: 7.03mm; }

        .ps-row {
            display: flex;
            align-items: center;
            padding-left: 0.79mm;
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-size: 8pt;
            line-height: 5mm;
            color: var(--dark-navy);
        }

        .ps-label {
            flex: 0 0 32.93mm;
            text-align: left;
            white-space: nowrap;
            font-weight: 400;
        }

        .ps-bar-container {
            flex: 0 0 19.6mm;
            height: 4.22mm;
            position: relative;
        }

        .ps-bar {
            height: 4.22mm;
            background-color: var(--naartjie);
        }

        /* QC 27 Oct 2026: value ink ends on the reference's 121.38mm and the
           change's on 130.69mm (both were 0.76mm right). */
        .ps-value {
            flex: 0 0 3.74mm;
            text-align: right;
            font-weight: 400;
        }

        /* The arrow fills the slot between the value and the change figure,
           so the change's right edge stays put whatever its width. Baseline
           alignment sits the arrow's base on the figure's baseline (as on the
           reference). Centred, the two rounded to whole px separately and the
           5mm (18.9px) row pitch left one arrow in three or four 1px high. */
        .ps-change {
            flex: 0 0 9.32mm;
            display: inline-flex;
            justify-content: flex-end;
            align-items: baseline;
        }

        /* Reference arrows: black ▲ for up, steel-blue ▼ for down (SVG from
           global-fixes); the figure stays navy. QC 27 Oct 2026: the arrow
           hugged the value — it now takes the whole slot and centres in it;
           the 0.45mm pad nudges it onto the reference's 123.4mm (2.0mm clear
           of the value, 1.5mm of the change). Size: after the global
           include. */
        .ps-arrow {
            flex: 1 1 auto;
            display: inline-flex;
            justify-content: center;
            padding-left: 0.45mm;
        }
        .ps-arrow.change-up::before,
        .ps-arrow.change-down::before { margin: 0 !important; }
        .change-up::before { content: '▲'; font-size: 5.1pt; color: #000; }
        .change-down::before { content: '▼'; font-size: 5.1pt; color: var(--light-blue); }

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

        /* Top 10 — two columns on the 829 reference (SECURITY / % OF
           FUND), visible cells 136.37 → 183.03mm and 183.41 → 203.61mm.
           Header bar 4.62mm, body rows on a 4.96mm pitch; SECURITY starts
           1.75mm in, the percentages right-align 0.51mm inside the edge.
           Row backgrounds fade in pairs (same ramp as 823). */
        .top10-table .foord-table th,
        .top10-table .foord-table td {
            font-size: 8pt;
            line-height: 1;
            padding-top: 0 !important;
            padding-bottom: 0 !important;
            padding-right: 0.51mm;
        }
        /* Chrome prints table rows ~0.45px shorter than their nominal height
           plus spacing, so these are set to land the reference's 4.62mm bar
           and 4.96mm pitch. */
        .top10-table .foord-table th { height: 4.75mm; }
        .top10-table .foord-table td { height: 4.69mm; color: #000; }
        .top10-table .foord-table td:first-child,
        .top10-table .foord-table th:first-child {
            width: 69.4%;
            padding-left: 1.75mm;
        }
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
        .top10-table .table-wrapper { margin-bottom: 0; }

        /* =====================================================
           GEOGRAPHIC COUNTRY EXPOSURE — inline SVG pie (vector, so
           the labels print sharp; the canvas labels on other sheets
           print soft). 829 reference: the chart box spans x 64.12 →
           131.86mm, y 141.06 → 190.66mm; the ⌀36mm pie is centred at
           (104.57, 168.50)mm, starts at 12 o'clock and runs clockwise
           in feed rank order with the 879 positional palette. Labels
           are Avenir Next 6.75pt black on a 3.43mm pitch.
           ===================================================== */
        /* 13.04mm from the last bar row's baseline to the heading's. */
        .geo-section { margin-top: 8.98mm; }
        .geo-section .section-heading { margin-bottom: 0; }
        .geo-pie {
            display: block;
            width: 67.74mm;
            height: 49.61mm;
            margin-left: -0.5mm;
            margin-top: 3.35mm;
            overflow: visible;
        }
        .geo-pie path { stroke: #fff; stroke-width: 0.26; stroke-linejoin: round; }
        .geo-pie text {
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-size: 2.381px; /* 6.75pt in the viewBox's mm units */
            fill: #000;
        }

        /* =====================================================
           Below the pie: PORTFOLIO PERFORMANCE notice, then ASSET
           ALLOCATION %, CONTRIBUTORS/DETRACTORS and POLICY OBJECTIVE —
           the 823 sheet's page-2 blocks, set here with page 2's 7.5pt
           headings and 8pt navy table type (829 reference).
           ===================================================== */
        .p1-lower .page2-section { margin-bottom: 0; }
        .p1-lower .page2-heading { padding-left: 0.87mm; margin-bottom: 1.5mm; }
        .p1-lower .foord-table td { font-size: 8pt; color: var(--dark-navy); }
        .performance-notice-section { margin-top: 8.42mm; }
        .performance-notice {
            margin-top: 0.37mm;
            padding-left: 0.83mm;
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-size: 8pt;
            line-height: 9.4pt;
            color: var(--dark-navy);
        }
        .p1-lower .asset-alloc-table { margin-top: 9.83mm; }
        .p1-lower .contributors-table { margin-top: 8.8mm; }
        .p1-lower .contributors-table .page2-heading { margin-bottom: 1.18mm; }
        .p1-lower .policy-objective-section { margin-top: 8.02mm; }
        .p1-lower .policy-objective-section .page2-heading { margin-bottom: 2.7mm; }
        /* Row pitches 4.33mm (allocation) and 4.61mm (contributors). */
        .p1-lower .foord-table td { padding-top: 0 !important; padding-bottom: 0 !important; line-height: 1; height: 4.05mm; }
        .p1-lower .contributors-table .foord-table td { height: 4.3mm; }
        .p1-lower .table-wrapper { margin-bottom: 0; }
        .p1-footnotes { margin-top: 6.39mm; }
        .p1-footnotes .footnote { line-height: 10px; }


        /* Fee rates — two columns, values left-aligned at the midline;
           the underlying Foord global fund row renders pink. */
        .fee-rates-table .foord-table th:first-child,
        .fee-rates-table .foord-table td:first-child { width: 50%; }
        .fee-rates-table .foord-table td {
            text-align: left;
            padding-top: 0.52mm;
            padding-bottom: 0.52mm;
        }
        /* Reference: the unshaded "Foord global funds:" label lines up with
           the row labels above it (x=65.6mm), not the table edge. */
        .fee-rates-table .foord-table tr.global-funds-header td {
            background-color: var(--white);
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
        /* Reference: the TER row and the Transaction costs row are unshaded;
           only the indented "–" component rows carry the grey. */
        .tic-table .foord-table tbody tr:first-child td,
        .tic-table .foord-table tbody tr:nth-last-child(2) td { background-color: var(--white); }
        .tic-table .foord-table tr.total-row td {
            font-weight: 500;
            padding-top: 0.64mm;
            padding-bottom: 0.64mm;
        }
        .tic-table .foord-table th {
            padding-top: 0.76mm;
            padding-bottom: 0.76mm;
        }
        /* Trello 426: breathing room between the naartjie total row and the
           TER note — 3.05mm to the note's cap tops (was 1.85mm; August
           sheet 2.0mm, which Carla found too tight). */
        .tic-table .table-wrapper { margin-bottom: 2.6mm; }

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
            /* Trello 429: the bar spans exactly the disclaimer copy
               column below it (9mm → 60mm − 4mm). */
            margin: 10mm 4mm 0 9mm;
            text-align: center;
        }

        .important-info-header h2 {
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

        /* QC 27 Oct 2026: a 45.88mm measure breaks every page 2–3 line
           where the 829 reference does. */
        .info-sidebar-content { padding: 6.57mm 5.12mm 4mm 9mm; }

        /* QC 27 Oct 2026 ("font on staging" vs "font on example"): the 829
           reference sets pages 2–3 in Lato 2.015 Light 6.48pt, black,
           untracked and unkerned (Publisher doesn't kern small text;
           partials/lato2-fonts). Family and size are forced over
           global-fixes 295 after its include. 10px leading = 2.65mm
           (reference 2.59mm) — whole px, so Chrome prints every line on the
           same pitch; 5px between paragraphs gives the reference's 3.98mm
           baseline gap. */
        .info-sidebar-content p,
        .important-info-text {
            font-family: 'Lato 2', 'Lato', sans-serif;
            font-weight: 300;
            font-size: 6.48pt;
            line-height: 10px;
            letter-spacing: 0;
            font-kerning: none;
            color: #000;
            margin: 0 0 5px 0;
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

        /* 829 reference: the closing "This document is for information
           purposes only …" disclaimer and the issue date beneath it set in
           navy (the body copy is black); the disclaimer in italics. */
        .important-info-text.disclaimer {
            font-style: italic;
            color: var(--dark-navy);
        }
        .important-info-text.issue-date { color: var(--dark-navy); }

        /* === Page 3 — contact details + glossary === */
        /* 829 reference: Lato 2 Light 6.5pt navy, 8.35mm above the first
           contact line. */
        .contact-heading {
            font-family: 'Lato 2', 'Lato', sans-serif;
            font-weight: 300;
            font-size: 6.5pt;
            line-height: 10px;
            letter-spacing: 0;
            font-kerning: none;
            text-transform: uppercase;
            color: var(--dark-navy);
            margin: 0 0 5.6mm 0;
        }

        /* Reference page 3: contact lines run on a 2.75mm pitch (10px =
           2.65mm, whole px as page 2) with 1.4mm between blocks (6px); the
           label-less paragraph block has a blank line above it (11px). */
        .contact-block { margin-bottom: 6px; }
        .contact-block.contact-prose { margin-top: 11px; }
        .contact-block:last-child { margin-bottom: 0; }

        .contact-block p { margin: 0; line-height: 10px; }

        /* Labels in Lato 2 Regular, black like their values. */
        .contact-block strong {
            font-weight: 400;
            color: #000;
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
            padding: 29.33mm 6.08mm 4mm 4.53mm;
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
           Fed from AA_TOTAL_EQ / _CASH / _PROP since the August 2026
           export (QC card 33; FactsheetImporter). */
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
        .fee-rates-table { margin-bottom: 6.27mm; }
        .tic-table { margin-bottom: 10.01mm; }
        .investing-offshore-section .page2-heading { margin-bottom: 2.75mm; }

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
            font-size: 7.56pt;
            line-height: 9.25pt;
            /* Untracked, so the TER and offshore paragraphs break where the
               829 reference's do. */
            letter-spacing: 0;
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
    @include('funds.partials.global-fixes')
    <style>
        /* Sidebar — 823's whole-px line boxes (Trello 459: Chrome snaps text
           to whole px, and global's fractional 6.8pt/8.2pt let 1px steps land
           inside a section): an 8px heading box (heading→copy 10px = 2.65mm,
           reference 2.75mm) and 11px copy (2.91mm pitch, reference 2.87mm).
           The 829 sidebar packs its sections tighter than 823's (copy → next
           heading 3.64–3.69mm), so the gap is smaller; it lands ISIN NUMBER
           on the reference's 290.72mm baseline. The copy column ends at 56mm:
           DISTRIBUTIONS then breaks on three lines and RETURNS IN US$ before
           "ex-Japan", as on the reference. */
        .sidebar { padding-top: 5.17mm; padding-right: 4mm; }
        .sidebar-section { margin-bottom: 1.3mm !important; }
        .sidebar-section.mdd-heading { margin-bottom: 2.87mm !important; }
        .sidebar-section h3 { line-height: 8px !important; }
        .sidebar-section p,
        .sidebar-section .sidebar-value { line-height: 11px !important; }

        /* Portfolio-structure arrows at the 829 reference's Wingdings size
           (2.03 x 1.78mm; global default 1.7 x 1.55mm). Chrome snaps a
           background tile to whole px, so a 2.05 x 1.76mm box printed as
           7 x 6px (1.77mm wide); an 8 x 7px box prints the 10:9 SVG at
           2.06 x 1.85mm on every row. */
        .ps-arrow.change-up::before,
        .ps-arrow.change-down::before {
            width: 8px !important;
            height: 7px !important;
            background-size: 8px 7px !important;
        }

        /* Pages 2–3 column: Lato 2.015 at the reference's 6.48pt, over
           global-fixes 295's Google Lato 6.5pt (see .important-info-text). */
        .info-sidebar-content p,
        .info-sidebar-content li {
            font-family: 'Lato 2', 'Lato', sans-serif !important;
            font-size: 6.48pt !important;
        }

        /* MINIMUM DISCLOSURE heading: Avenir Next Medium 7.5pt on a 3.04mm
           pitch (reference 7.56pt), larger than the 6pt row labels. */
        .sidebar-section.mdd-heading h3 {
            font-size: 7.5pt !important;
            line-height: 8.6pt !important;
        }

        /* Navy table copy (823 card 281); only the pink Foord-fund row and
           the TIC body stay black, and the red TIC total row is Regular. */
        .asset-alloc-table .foord-table td,
        .contributors-table .foord-table td,
        .fee-rates-table .foord-table tr:not(.sub-item) td { color: var(--dark-navy); }
        .policy-objective-section .page2-body { color: #000; }
        .tic-table .foord-table tr.total-row td { font-weight: 400; }

        /* 829 reference value columns: fee values 3.95mm into their cells;
           page-1 contributors' names 3.9mm in, allocation labels 1.21mm in
           and the allocation figures right-aligned at x=144.29mm. */
        .fee-rates-table .foord-table td:nth-child(2) { padding-left: 3.95mm; }
        .p1-lower .foord-table td:first-child { padding-left: 1.21mm; }
        .p1-lower .contributors-table td:nth-child(2) { padding-left: 3.9mm; }
        .p1-lower .asset-alloc-table .foord-table td:nth-child(2) { padding-right: 59.38mm; }

        /* Page-1 notes sit flush with the headings (x=65.24mm). */
        .p1-footnotes p.footnote { padding-left: 0.87mm !important; text-indent: 0 !important; }

        /* Whole-pixel fee + TIC rows (823 card 386), with a top pad that
           drops Avenir Next's high cap height onto the centre. Chrome prints
           a row's pitch as its height plus ~1px of spacing, so 15px / 16px
           give the 829 reference's 4.2mm / 4.47mm pitches. */
        .fee-rates-table .foord-table td,
        .tic-table .foord-table td {
            padding-bottom: 0 !important;
            line-height: 1 !important;
        }
        .fee-rates-table .foord-table td { height: 15px; padding-top: 1.5px !important; }
        .tic-table .foord-table td { height: 16px; padding-top: 1px !important; }

        /* "* Estimated as the fund was incepted less than a year ago…" —
           Avenir Next 7pt black on a 3.04mm pitch, 5.3mm under the total
           row, with the TER paragraph 6.5mm below it. */
        .tic-estimate-note {
            padding-left: 0.6mm;
            padding-right: 1.5mm;
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-size: 7pt;
            line-height: 8.6pt;
            color: #000;
            margin: 0 0 3.7mm 0;
        }
    </style>
    @include('funds.partials.screen-centre')
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
                <a href="{{ route('funds.edit', $fund) }}" class="btn-grey">
                    <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Edit Fund
                </a>
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
                            // Prescient feeder sidebar per the 829 reference.
                            // Two rows reuse columns the feeder sheets leave
                            // empty, so editable-field saves still route to a
                            // real column: `masterFund` carries INVESTMENT
                            // POLICY and `foreignAssets` the RETURNS IN US$
                            // note (as on 823).
                            $labelMap = [
                                // The reference breaks the label after DOCUMENT
                                'mddHeading' => 'MINIMUM DISCLOSURE DOCUMENT<br>AND GENERAL INVESTOR REPORT',
                                'shareClass' => 'CLASS',
                                'masterFund' => 'INVESTMENT POLICY',
                                'managementCompany' => 'MANAGEMENT COMPANY',
                                'fundManagers' => 'MASTER FUND MANAGERS',
                                'inceptionDate' => 'INCEPTION DATE',
                                'baseCurrency' => 'BASE CURRENCY',
                                'equityIndicator' => 'EQUITY INDICATOR',
                                'category' => 'ASISA CLASSIFICATION',
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

                            // Display order per the 829 reference sidebar (no
                            // DOMICILE row)
                            $displayOrder = [
                                'mddHeading', 'shareClass', 'masterFund',
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
                    <!-- Row 1: PORTFOLIO STRUCTURE % beside TOP 10 INVESTMENTS
                         (879 master sheet layout; no variance column). -->
                    <div class="two-col">
                        <div class="col-left">
                            @if(!empty($fund->data['mainContent']['sectorAllocation']['sectors']))
                                @php
                                    $psData = $fund->data['mainContent']['sectorAllocation'];
                                    // Fixed scale, not normalised to the month's largest
                                    // holding: 0.633mm per percentage point (829 reference,
                                    // least-squares fit; 29% = 18.30mm), clamped to the
                                    // bar column.
                                    $psBarScale = 0.633;
                                @endphp
                                <div class="ps-section">
                                    <div class="ps-header">
                                        <h3 class="section-heading" style="margin-bottom: 0;">
                                            <span x-data="editableField('mainContent.sectorAllocation.title', '{{ addslashes($psData['title'] ?? 'PORTFOLIO STRUCTURE %') }}')"
                                                  @click="editMode && startEdit()"
                                                  :class="editMode ? 'editable' : ''"
                                                  x-text="value"></span>
                                        </h3>
                                    </div>
                                    <div>
                                        @foreach ($psData['sectors'] as $rowIndex => $row)
                                            @php
                                                /* The stored change carries no glyph on this feed (an
                                                   explicit CHANGE_SIGN per sector); strip one anyway.
                                                   The arrow is the SVG ::before from global-fixes.
                                                   Zero changes show no arrow. */
                                                $changeNumber = trim(str_replace(['▲', '▼'], '', (string) ($row['change'] ?? '')));
                                                $isZeroChange = is_numeric($changeNumber) && (float) $changeNumber == 0.0;
                                                $changeClass = $isZeroChange ? '' : ((($row['direction'] ?? '') === 'up') ? 'change-up' : ((($row['direction'] ?? '') === 'down') ? 'change-down' : ''));
                                            @endphp
                                            <div class="ps-row">
                                                <span class="ps-label">
                                                    <span x-data="editableField('mainContent.sectorAllocation.sectors.{{ $rowIndex }}.name', '{{ addslashes($row['name']) }}')"
                                                          @click="editMode && startEdit()"
                                                          :class="editMode ? 'editable' : ''"
                                                          x-text="value"></span>
                                                </span>
                                                <div class="ps-bar-container">
                                                    <div class="ps-bar" style="width: {{ min(19.6, round((float) ($row['value'] ?? 0) * $psBarScale, 2)) }}mm;"></div>
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

                        <div class="col-right">
                            @if(isset($fund->data['mainContent']['topInvestments']))
                                <div class="top10-table">
                                    <h3 class="section-heading">
                                        <span x-data="editableField('mainContent.topInvestments.title', '{{ addslashes($fund->data['mainContent']['topInvestments']['title'] ?? 'TOP 10 INVESTMENTS') }}')"
                                              @click="editMode && startEdit()"
                                              :class="editMode ? 'editable' : ''"
                                              x-text="value"></span>
                                    </h3>
                                    <div class="table-wrapper">
                                        <table class="foord-table">
                                            <thead>
                                                <tr>
                                                    {{-- 829 reference: SECURITY and % OF FUND only. --}}
                                                    <th>{{ $fund->data['mainContent']['topInvestments']['headers'][0] ?? 'SECURITY' }}</th>
                                                    <th>{{ last($fund->data['mainContent']['topInvestments']['headers'] ?? []) ?: '% OF FUND' }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($fund->data['mainContent']['topInvestments']['rows'] ?? [] as $rowIndex => $row)
                                                    <tr>
                                                        <td>
                                                            <span x-data="editableField('mainContent.topInvestments.rows.{{ $rowIndex }}.security', '{{ addslashes($row['security'] ?? '') }}')"
                                                                  @click="editMode && startEdit()"
                                                                  :class="editMode ? 'editable' : ''"
                                                                  x-text="value"></span>
                                                        </td>
                                                        <td><span x-data="editableField('mainContent.topInvestments.rows.{{ $rowIndex }}.percentage', '{{ addslashes($fmt($row['percentage'] ?? '')) }}', 'oneDecimal')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ $fmt($row['percentage'] ?? '') }}</span></td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- GEOGRAPHIC COUNTRY EXPOSURE — inline SVG pie (feed:
                         GEO_COUNTRY_RANK_n, the master fund's country split) -->
                    @if(!empty($fund->data['mainContent']['assetAllocation']['geographicCountryExposure']))
                        @php
                            $geoSlices = array_values(array_filter(
                                $fund->data['mainContent']['assetAllocation']['geographicCountryExposure'],
                                fn ($s) => is_numeric($s['value'] ?? null) && (float) $s['value'] > 0
                            ));
                            $geoTotal = array_sum(array_map(fn ($s) => (float) $s['value'], $geoSlices));
                            // 879 positional palette (rank order, not per country).
                            $pieColours = ['#d25347', '#29363d', '#cccccc', '#7a9cb4', '#535353', '#e2cea4', '#bfc3c5'];
                            // Pie geometry in the SVG's mm user units (see .geo-pie).
                            $pieCx = 40.45;
                            $pieCy = 27.44;
                            $pieR = 18.0;
                            $pieAngle = 0.0;
                            $pieSlices = [];
                            foreach ($geoSlices as $i => $slice) {
                                $sweep = (float) $slice['value'] / $geoTotal * 2 * M_PI;
                                $a0 = $pieAngle;
                                $a1 = $pieAngle + $sweep;
                                $pieAngle = $a1;
                                // Clockwise from 12 o'clock: x = cx + r·sin a, y = cy − r·cos a.
                                $pt = fn ($a) => sprintf('%.3f %.3f', $pieCx + $pieR * sin($a), $pieCy - $pieR * cos($a));
                                $d = $sweep >= 2 * M_PI - 1e-6
                                    ? sprintf('M%.3f %.3f A%.3f %.3f 0 1 1 %.3f %.3f A%.3f %.3f 0 1 1 %.3f %.3f Z', $pieCx, $pieCy - $pieR, $pieR, $pieR, $pieCx, $pieCy + $pieR, $pieR, $pieR, $pieCx, $pieCy - $pieR)
                                    : sprintf('M%.3f %.3f L%s A%.3f %.3f 0 %d 1 %s Z', $pieCx, $pieCy, $pt($a0), $pieR, $pieR, $sweep > M_PI ? 1 : 0, $pt($a1));
                                $pieSlices[] = [
                                    'd' => $d,
                                    'colour' => $pieColours[$i % count($pieColours)],
                                    'mid' => ($a0 + $a1) / 2,
                                    'name' => (string) $slice['name'],
                                    'pct' => number_format((float) $slice['value'], 1).'%',
                                ];
                            }
                        @endphp
                        <div class="geo-section">
                            <h3 class="section-heading">
                                <span x-data="editableField('mainContent.assetAllocation.geographicCountryTitle', '{{ addslashes($fund->data['mainContent']['assetAllocation']['geographicCountryTitle'] ?? 'GEOGRAPHIC COUNTRY EXPOSURE') }}')"
                                      @click="editMode && startEdit()"
                                      :class="editMode ? 'editable' : ''"
                                      x-text="value"></span>
                            </h3>
                            <svg class="geo-pie" viewBox="0 0 67.74 49.61" data-cx="{{ $pieCx }}" data-cy="{{ $pieCy }}" data-r="{{ $pieR }}" xmlns="http://www.w3.org/2000/svg">
                                @foreach ($pieSlices as $slice)
                                    <path d="{{ $slice['d'] }}" fill="{{ $slice['colour'] }}"></path>
                                @endforeach
                                {{-- Labels are laid out by layoutPieLabels() once the
                                     fonts have loaded (it measures the text). --}}
                                @foreach ($pieSlices as $slice)
                                    <text class="pie-label" data-mid="{{ round($slice['mid'], 5) }}" data-name="{{ $slice['name'] }}" data-pct="{{ $slice['pct'] }}" x="{{ round($pieCx + ($pieR + 2.6) * sin($slice['mid']), 2) }}" y="{{ round($pieCy - ($pieR + 2.6) * cos($slice['mid']), 2) }}" text-anchor="middle">{{ $slice['name'] }} {{ $slice['pct'] }}</text>
                                @endforeach
                            </svg>
                        </div>
                    @endif

                    <div class="p1-lower">
                        <!-- PORTFOLIO PERFORMANCE — a new fund may not report
                             returns for periods under a year (829 reference). -->
                        @if(!empty($fund->data['mainContent']['performanceTable']['notice']))
                            <div class="page2-section performance-notice-section">
                                <h3 class="page2-heading">
                                    <span x-data="editableField('mainContent.performanceTable.title', '{{ addslashes($fund->data['mainContent']['performanceTable']['title'] ?? 'PORTFOLIO PERFORMANCE') }}')"
                                          @click="editMode && startEdit()"
                                          :class="editMode ? 'editable' : ''"
                                          x-text="value"></span>
                                </h3>
                                <p class="performance-notice">
                                    <span x-data="editableField('mainContent.performanceTable.notice', '{{ addslashes($fund->data['mainContent']['performanceTable']['notice']) }}')"
                                          @click="editMode && startEdit()"
                                          :class="editMode ? 'editable' : ''"
                                          x-text="value"></span>
                                </p>
                            </div>
                        @endif

                        <!-- Asset Allocation % — AA_TOTAL_* (one decimal) -->
                        @if(!empty($fund->data['page2Content']['assetAllocation']['rows']))
                            <div class="page2-section asset-alloc-table">
                                <h3 class="page2-heading">
                                    <span x-data="editableField('page2Content.assetAllocation.title', '{{ addslashes($fund->data['page2Content']['assetAllocation']['title'] ?? 'ASSET ALLOCATION %') }}')"
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
                                                        <span x-data="editableField('page2Content.assetAllocation.rows.{{ $rowIndex }}.value', '{{ addslashes($fmt($row['value'] ?? '')) }}', 'oneDecimal')"
                                                              @click="editMode && startEdit()"
                                                              :class="editMode ? 'editable' : ''">{{ $fmt($row['value'] ?? '') }}</span>
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
                                    <span x-data="editableField('page2Content.contributorsDetractors.title', '{{ addslashes($fund->data['page2Content']['contributorsDetractors']['title'] ?? 'CONTRIBUTORS/DETRACTORS') }}')"
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
                                    <span x-data="editableField('page2Content.policyObjective.title', '{{ addslashes($fund->data['page2Content']['policyObjective']['title'] ?? 'POLICY OBJECTIVE') }}')"
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

                        {{-- "* Figures are based on Master Fund at …" — the importer
                             keeps the date on the export's month end. --}}
                        @if(!empty($fund->data['mainContent']['performanceTable']['footnotes']))
                            <div class="p1-footnotes">
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
                            <p class="important-info-text issue-date">
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
                    {{-- ASSET ALLOCATION, CONTRIBUTORS/DETRACTORS and POLICY
                         OBJECTIVE print on page 1 of the 829 sheet. --}}
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
                                            @foreach ($fund->data['fees']['totalInvestmentCharge']['headers'] ?? ['', '12 MONTHS', '36 MONTHS'] as $hIndex => $header)
                                                <th><span x-data="editableField('fees.totalInvestmentCharge.headers.{{ $hIndex }}', '{{ addslashes($header) }}')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ $header }}</span></th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($fund->data['fees']['totalInvestmentCharge']['rows'] ?? [] as $rowIndex => $row)
                                            <tr>
                                                <td><span x-data="editableField('fees.totalInvestmentCharge.rows.{{ $rowIndex }}.name', '{{ addslashes($row['name']) }}')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ $row['name'] }}</span></td>
                                                <td><span x-data="editableField('fees.totalInvestmentCharge.rows.{{ $rowIndex }}.12m', '{{ $fmt($row['12m'] ?? '', 2) }}', 'twoDecimals')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ $fmt($row['12m'] ?? '', 2) }}</span></td>
                                                <td><span x-data="editableField('fees.totalInvestmentCharge.rows.{{ $rowIndex }}.36m', '{{ $fmt($row['36m'] ?? '', 2) }}', 'twoDecimals')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ $fmt($row['36m'] ?? '', 2) }}</span></td>
                                            </tr>
                                        @endforeach
                                        @if(isset($fund->data['fees']['totalInvestmentCharge']['total']))
                                            <tr class="total-row">
                                                <td><span x-data="editableField('fees.totalInvestmentCharge.total.name', '{{ addslashes($fund->data['fees']['totalInvestmentCharge']['total']['name'] ?? 'Total investment charge') }}')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ $fund->data['fees']['totalInvestmentCharge']['total']['name'] ?? 'Total investment charge' }}</span></td>
                                                <td><span x-data="editableField('fees.totalInvestmentCharge.total.12m', '{{ $fmt($fund->data['fees']['totalInvestmentCharge']['total']['12m'] ?? '', 2) }}', 'twoDecimals')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ $fmt($fund->data['fees']['totalInvestmentCharge']['total']['12m'] ?? '', 2) }}</span></td>
                                                <td><span x-data="editableField('fees.totalInvestmentCharge.total.36m', '{{ $fmt($fund->data['fees']['totalInvestmentCharge']['total']['36m'] ?? '', 2) }}', 'twoDecimals')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ $fmt($fund->data['fees']['totalInvestmentCharge']['total']['36m'] ?? '', 2) }}</span></td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                            {{-- New fund: the TIC figures are estimates (the "*" on
                                 the 12/36 MONTHS headers). --}}
                            @if(!empty($fund->data['fees']['totalInvestmentCharge']['note']))
                                <p class="tic-estimate-note">
                                    <span x-data="editableField('fees.totalInvestmentCharge.note', '{{ addslashes($fund->data['fees']['totalInvestmentCharge']['note']) }}')"
                                          @click="editMode && startEdit()"
                                          :class="editMode ? 'editable' : ''"
                                          x-text="value"></span>
                                </p>
                            @endif
                            <p class="page2-body">
                                <span x-data="editableField('fees.totalInvestmentCharge.description', '{{ addslashes($fund->data['fees']['totalInvestmentCharge']['description'] ?? '') }}')"
                                      @click="editMode && startEdit()"
                                      :class="editMode ? 'editable' : ''"
                                      x-text="value"></span>
                            </p>
                        </div>
                    @endif

                    <!-- Investing Offshore -->
                    @if(isset($fund->data['page2Content']['investingOffshore']))
                        <div class="page2-section investing-offshore-section">
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
                            <div class="contact-block {{ collect($block)->every(fn ($line) => empty($line['label'])) ? 'contact-prose' : '' }}">
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
            // TIC values print to two decimals, as the blade's $fmt does.
            twoDecimals(value) {
                const s = String(value).trim();
                return /^-?\d*\.?\d+$/.test(s) ? Number(s).toFixed(2) : s;
            },
            // 822 banner drops any "— CLASS X" suffix (the class prints in
            // the sidebar CLASS row).
            fundNameNoClass(value) {
                return String(value).replace(/\s*[—–-]\s*CLASS\s+[A-Z][0-9]*$/i, '').toUpperCase();
            },
            headingSuffix(value) {
                return String(value).replace(/\s*\(([^)]+)\)/, ' <span class="title-suffix">($1)</span>');
            },
            // Top-10 and allocation percentages print to one decimal, as
            // the blade's $fmt does.
            oneDecimal(value) {
                const s = String(value).trim();
                return /^-?\d*\.?\d+$/.test(s) ? Number(s).toFixed(1) : s;
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

    <script>
        // GEOGRAPHIC COUNTRY EXPOSURE labels. The pie is drawn server-side;
        // the labels need the font's measured widths, so they are laid out
        // here: "Name %" in black outside each slice with no leader lines
        // (829/879 references). Labels above the pie's shoulder and on its
        // right stack the name over the percentage ("Other / 12.7%",
        // "China / 44.1%"); the rest run on one line, wrapped at 19.5mm
        // ("United States of / America 8.4%"). Each block is placed so its
        // edge facing the pie sits 2.6mm outside the rim along the slice's
        // mid-angle, and overlapping neighbours are pushed apart.
        function layoutPieLabels() {
            const svg = document.querySelector('.geo-pie');
            if (!svg) return;
            const cx = +svg.dataset.cx, cy = +svg.dataset.cy, r = +svg.dataset.r;
            const GAP = 2.6, MAX_WIDTH = 19.5, LINE = 3.43, CAP = 0.708 * 2.381;
            const NS = 'http://www.w3.org/2000/svg';
            const boxes = [];

            svg.querySelectorAll('text.pie-label').forEach(label => {
                const mid = +label.dataset.mid;
                const dx = Math.sin(mid), dy = -Math.cos(mid);
                const measure = s => { label.textContent = s; return label.getComputedTextLength(); };
                const wrap = text => {
                    const lines = [];
                    let line = '';
                    text.split(' ').forEach(word => {
                        const candidate = line ? line + ' ' + word : word;
                        if (line && measure(candidate) > MAX_WIDTH) {
                            lines.push(line);
                            line = word;
                        } else {
                            line = candidate;
                        }
                    });
                    if (line) lines.push(line);
                    return lines;
                };
                const stacked = dy < -0.4 || dx > 0.05;
                const lines = stacked ? [...wrap(label.dataset.name), label.dataset.pct] : wrap(label.dataset.name + ' ' + label.dataset.pct);
                const w = Math.max(...lines.map(measure));
                const h = lines.length * LINE;
                const ax = cx + dx * (r + GAP), ay = cy + dy * (r + GAP);
                boxes.push({
                    label, lines, w, h, dy,
                    x: Math.max(0.3, ax - w / 2 + dx * w / 2),
                    y: ay - h / 2 + dy * h / 2,
                });
            });

            // Push overlapping neighbours apart vertically (away from the
            // pie's equator), keeping the earlier label in place.
            for (let i = 0; i < boxes.length; i++) {
                for (let j = 0; j < i; j++) {
                    const a = boxes[j], b = boxes[i];
                    const overlapX = a.x < b.x + b.w && b.x < a.x + a.w;
                    const overlapY = a.y < b.y + b.h && b.y < a.y + a.h;
                    if (!overlapX || !overlapY) continue;
                    b.y = b.y >= a.y ? a.y + a.h : a.y - b.h;
                }
            }

            boxes.forEach(({ label, lines, w, x, y, dy }) => {
                label.textContent = '';
                // Right-hand labels read from the left edge; the others
                // centre their lines in the block, as the reference does.
                const right = x > cx;
                label.setAttribute('text-anchor', right ? 'start' : 'middle');
                lines.forEach((text, i) => {
                    const tspan = document.createElementNS(NS, 'tspan');
                    tspan.setAttribute('x', (right ? x : x + w / 2).toFixed(2));
                    tspan.setAttribute('y', (y + i * LINE + (LINE + CAP) / 2).toFixed(2));
                    tspan.textContent = text;
                    label.appendChild(tspan);
                });
            });
        }
        layoutPieLabels();
        document.fonts && document.fonts.ready.then(layoutPieLabels);
    </script>
</body>
</html>
