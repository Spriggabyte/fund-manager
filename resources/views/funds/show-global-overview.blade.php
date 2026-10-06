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
        /* Static TrueType Merriweather (as show-income): Google Fonts serves
           a variable font that Chromium's PDF backend embeds as Type3
           (Trello 402: banner and footer copy looked tight / heavy). */
        @font-face {
            font-family: 'Merriweather TT';
            src: url('{{ asset('fonts/Merriweather/Merriweather-Regular.ttf') }}') format('truetype');
            font-weight: 400;
            font-style: normal;
            font-display: swap;
        }

        /* =====================================================
           FOORD FUND OVERVIEW: GLOBAL — two-page multi-fund summary
           (fund code GLB), cloned from show-local-overview with the
           colours inverted (navy badge, naartjie banner, navy footer),
           taller performance rows with feeder-fund note rows, WORLD/ASIA
           bullet columns, three geographic pies and a three-fund
           allocation table. Every measurement below is in mm from the
           July 2026 Publisher reference
           (Funds/Fund overview - global/Global Fund Overview - July 2026.pdf).
           ===================================================== */
        :root {
            --naartjie: #d25347;
            --naartjie-75: #dd7e75;
            --naartjie-50: #e9a9a3;
            --naartjie-20: #f6ddda;
            --dark-navy: #29363d;
            --dark-navy-70: #697277;
            --dark-navy-30: #bfc3c5;
            --dark-navy-15: #dfe1e2;
            --band-grey: #a9afb1;
            --peer-grey: #d4d4d4;
            --row-grey: #e6e6e6;
            --medium-grey: #9a9a9a;
            --light-grey: #cccccc;
            --dark-grey: #535353;
            --off-black: #313131;
            --white: #ffffff;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        @page { size: A4 portrait; margin: 0; }

        html, body { width: 210mm; margin: 0; padding: 0; }

        body {
            font-family: 'Avenir Next', 'Lato', -apple-system, sans-serif;
            font-size: 8pt;
            line-height: 1.2;
            color: #000;
            background: var(--white);
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .page {
            width: 210mm;
            height: 297mm;
            max-height: 297mm;
            overflow: hidden;
            position: relative;
            page-break-after: always;
            background: var(--white);
        }
        .page:last-child { page-break-after: auto; }

        /* ---------- Header: naartjie date badge + logo ---------- */
        .header { position: relative; height: 23.2mm; }
        .date-badge {
            position: absolute;
            left: 9.9mm;
            top: 8.4mm;
            width: 50.8mm;
            height: 11.0mm;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--dark-navy);
            color: var(--white);
            /* Trello 368: Avenir Next Medium 10pt, as on every other sheet. */
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 500;
            font-size: 10pt;
            /* Trello 429: a line box exactly the font's height at 10pt (16px)
               leaves no leading for Chrome to round — date centred (the 0.3mm
               top padding it replaces set the date 0.2mm low). */
            line-height: 12pt;
            letter-spacing: 0.01em;
            text-align: center;
        }
        /* The badge's edges and the date's baseline are painted on whole
           pixels; at this badge's position they round 0.78px apart (date
           0.2mm low). Half a pixel up lands the baseline on the nearer one. */
        .date-badge > span { position: relative; top: -0.5px; }
        .logo { position: absolute; top: 8.4mm; right: 10.5mm; height: 11mm; }
        .logo img { height: 100%; width: auto; }

        /* ---------- Title banner (navy) ---------- */
        .title-banner {
            position: relative;
            margin-left: 9.9mm;
            width: 190mm;
            height: 34.1mm;
            background-color: var(--naartjie);
            color: var(--white);
            padding: 5.0mm 6mm 0 3.5mm;
        }
        .sheet-title {
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 500;
            font-size: 23pt;
            letter-spacing: 0.005em;
            text-transform: uppercase;
            line-height: 1.05;
            /* Trello 321: tighter title → body gap (reference: body cap
               line 1mm under the title box). */
            margin: 0 0 2.0mm 0;
        }
        /* Trello 402: the reference's 9pt, tracked a little more open than
           the Publisher sheet (0.03em; line 1 still ends at "track"). */
        .sheet-description {
            font-family: 'Merriweather TT', 'Merriweather', Georgia, serif;
            font-weight: 400;
            font-size: 9pt;
            line-height: 4.0mm;
            letter-spacing: 0.03em;
            margin-left: 0.7mm;
            color: var(--white);
        }

        /* ---------- Grey section bands ---------- */
        /* Trello 391 / 402: heading centred top to bottom in its band. The
           cap-centred Avenir twin (partials/avenir-fonts) centres the caps
           in the line box; centreOverviewText() below trims the whole-pixel
           rounding, using the symmetric 1mm paddings as its travel. */
        .band {
            height: 7.0mm;
            margin-left: 9.9mm;
            width: 190mm;
            background-color: var(--band-grey);
            color: var(--white);
            font-family: 'Avenir Next Cap', 'Avenir Next', 'Lato', sans-serif;
            font-weight: 500;
            font-size: 8pt;
            letter-spacing: 0.01em;
            text-transform: uppercase;
            display: flex;
            align-items: center;
            padding: 1.0mm 0 1.0mm 1.0mm;
        }
        /* Publisher places the synopsis/strategy bands and the footer bars
           1.3mm further right than the tables (11.2mm vs 9.9mm). */
        .band.band-shifted { margin-left: 11.2mm; width: 190.7mm; }

        /* ---------- Performance grid ---------- */
        /* Trello 402: every white line in the two tables is exactly 1px
           (0.75pt). Chrome paints cell backgrounds on whole pixels, so the
           old 0.35/0.38/0.4mm gaps (1.3–1.5px) came out 1px or 2px (even 3px)
           depending on where they fell; a whole-pixel gap between fractional
           edges always rounds to itself. The width/height given up by the
           thinner gaps goes back into the cells, so columns end and rows
           repeat exactly where they did. */
        .perf {
            --row-comp: calc(0.38mm - 1px);
            /* n rows carry the compensation but only n − 1 gaps shrank. */
            margin-bottom: calc(0mm - var(--row-comp));
            margin-left: 9.9mm;
            width: 189.2mm;
            display: grid;
            grid-template-columns: 11.7mm 1.7mm 77.7mm repeat(6, 1px calc(16.35mm - 1px));
            grid-auto-rows: auto;
            row-gap: 1px;
            font-size: 8pt;
        }
        .perf .cell {
            display: flex;
            align-items: center;
            overflow: hidden;
            white-space: nowrap;
            height: calc(8.7mm + var(--row-comp));
            /* Trello 373 / 391: text centred top to bottom — cap-centred
               face, symmetric padding, whole-pixel trim by
               centreOverviewText(). */
            font-family: 'Avenir Next Cap', 'Avenir Next', 'Lato', sans-serif;
            padding-top: 0.5mm;
            padding-bottom: 0.5mm;
        }
        .perf .head {
            background-color: var(--dark-navy);
            color: var(--white);
            font-weight: 500;
            font-size: 7.55pt;
            text-transform: uppercase;
            justify-content: center;
            height: calc(5.59mm + var(--row-comp));
        }
        .perf .head.head-label { grid-column: 1 / span 3; }
        .perf .cell.label { padding-left: 1.4mm; }
        /* Prose-only fourth row under a fund: "(SA Feeder Fund: …)" */
        .perf .row-feeder .cell { background-color: var(--naartjie-20); height: calc(4.83mm + var(--row-comp)); grid-column: 3 / -1; }
        /* Publisher's benchmark rows are a touch shorter than the fund/peer rows. */
        .perf .row-benchmark .cell { height: calc(8.2mm + var(--row-comp)); }
        .perf .cell.value { justify-content: flex-end; padding-right: 1.75mm; }
        .perf .row-fund .cell { background-color: var(--naartjie-20); }
        .perf .row-peer .cell { background-color: var(--peer-grey); }
        .perf .row-benchmark .cell { background-color: var(--row-grey); }
        .perf .fund-name { font-weight: 600; }
        /* The rotated label is absolutely positioned inside its cell so its
           text length never contributes to the grid's row sizing; the cell's
           height (from the rows it spans) is what makes the text wrap into
           vertical lines, exactly as Publisher's text box does. */
        .perf .group-label {
            grid-column: 1;
            position: relative;
            background-color: var(--naartjie-20);
            min-height: 0;
        }
        .perf .group-label > span {
            position: absolute;
            inset: 0;
            color: var(--naartjie);
            font-weight: 500;
            font-size: 7.6pt;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            line-height: 3.1mm;
            padding: 0 0.6mm;
        }
        /* Spacer rows: the gap between funds inside a group (2.2mm) and
           between groups (3.9mm) — implemented as explicit grid rows so the
           group label can span them. */
        .perf .spacer-head { grid-column: 1 / -1; height: calc(2.6mm + var(--row-comp)); }
        .perf .spacer-fund { grid-column: 2 / -1; height: calc(2.5mm + var(--row-comp)); }
        .perf .spacer-group { grid-column: 1 / -1; height: calc(3.5mm + var(--row-comp)); }

        /* Trello 402 brought the source notes 3.2mm closer to the table;
           Trello 449 nudged them 2.1mm back down so the first baseline sits
           5.0mm under the table, as on the local overview. The synopsis
           band's margin takes the difference, so the band doesn't move. */
        .perf-footnotes {
            margin: 3.5mm 0 0 24.6mm;
            font-family: 'Lato', 'Avenir Next', sans-serif;
            font-size: 6.1pt;
            line-height: 2.6mm;
            color: #000;
        }
        .perf-footnotes p::before { content: '\2212\00a0'; }

        /* ---------- Synopsis / strategy bullet columns ---------- */
        .bullet-cols {
            margin-left: 11.2mm;
            width: 190.7mm;
            display: grid;
            grid-template-columns: 90.6mm 1fr;
            margin-top: 4.4mm;
        }
        .bullet-col h3 {
            font-family: 'Lato', 'Avenir Next', sans-serif;
            font-weight: 400;
            /* Publisher sets these in Calibri 10pt; Lato 9.2pt matches its width. */
            font-size: 9.2pt;
            line-height: 3.9mm;
            text-transform: uppercase;
            margin: 0 0 1.0mm 1.2mm;
            color: #000;
        }
        .bullet-col ul { list-style: none; margin: 0; padding: 0; }
        .bullet-col li {
            position: relative;
            padding-left: 6.2mm;
            font-size: 8.5pt;
            line-height: 4.13mm;
            color: #000;
        }
        /* Trello 402: a 1.0mm (3.78px) dot was snapped to 3×3, 3×4, 4×3 or
           4×4px depending on its position — ovals of different sizes. A
           whole-pixel box always paints at that size; same centre as before. */
        .bullet-col li::before {
            content: '';
            position: absolute;
            left: calc(1.9mm - 2px);
            top: calc(2.05mm - 2px);
            width: 4px;
            height: 4px;
            border-radius: 50%;
            background-color: var(--naartjie);
        }

        /* ---------- Footer bar (both pages) ---------- */
        .footer-bar {
            position: absolute;
            left: 11.2mm;
            top: 283.1mm;
            width: 190.9mm;
            height: 7.0mm;
            background-color: var(--dark-navy);
            color: var(--white);
            /* Trello 402: static Merriweather at the reference's 8pt, tracked
               open to match the banner copy. */
            font-family: 'Merriweather TT', 'Merriweather', Georgia, serif;
            font-size: 8pt;
            letter-spacing: 0.03em;
            /* The static face's line box sits its caps 0.3mm above centre. */
            padding-top: 0.6mm;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        /* ---------- Page 2: geographic pies ---------- */
        .geo {
            position: relative;
            margin-left: 9.9mm;
            width: 190mm;
            height: 84.2mm;
        }
        .geo .geo-title {
            position: absolute;
            top: 3.3mm;
            width: 40mm;
            text-align: center;
            font-weight: 500;
            font-size: 8.1pt;
            text-transform: uppercase;
            white-space: nowrap;
        }
        .geo .geo-title sup { font-size: 5pt; vertical-align: super; line-height: 0; }
        .geo .geo-pie { position: absolute; top: 8.5mm; width: 60mm; height: 46mm; margin-left: -9mm; }
        /* Pies are Ø40mm (Trello 227: "a bit bigger" than the 36.6mm
           reference) centred at y 31.5mm, low enough that a small slice's
           outside label clears the title; the SVG box is larger than the pie
           so outside-rim labels on small slices are not clipped. It is
           60mm wide (pie centre unchanged): Highcharts ellipsis-truncates an
           outside label that overflows the plot box, and a 50mm box cut the
           Asia pie's left-flank "4.9%" (Hong Kong) down to "%" (Trello 374). */
        /* A small slice at 12 o'clock puts its outside label 10px past the
           rim, i.e. against the box's top edge (the 875 pie's "0.7%" glyph
           tops sat 0.3px above it), and Highcharts' inline overflow: hidden
           on the render-to div and its container, plus the inline <svg>,
           clipped them flat. Let the labels paint past the box instead of
           moving the pies. */
        .geo .geo-pie,
        .geo .geo-pie .highcharts-container,
        .geo .geo-pie svg { overflow: visible !important; }
        .geo .geo-legend {
            position: absolute;
            top: 54.0mm;
            list-style: none;
            font-size: 6.2pt;
            line-height: 3.2mm;
        }
        .geo .geo-legend li { position: relative; padding-left: 3.3mm; white-space: nowrap; }
        /* Whole-pixel dots (see .bullet-col li::before): 1.5mm painted as
           5×6 or 6×6px. */
        .geo .geo-legend li::before {
            content: '';
            position: absolute;
            left: calc(0.95mm - 3px);
            top: calc(1.6mm - 3px);
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background-color: var(--dot, #000);
        }
        .geo .geo-footnotes {
            position: absolute;
            left: 0.9mm;
            top: 75.1mm;
            font-size: 6pt;
            line-height: 2.55mm;
        }
        .geo .geo-footnotes sup { font-size: 4.3pt; vertical-align: super; line-height: 0; margin-right: 0.3mm; }

        /* ---------- Page 2: asset allocation grid ---------- */
        /* 1px white lines, cells widened/heightened to keep the pitch — see .perf. */
        .aa {
            --row-comp: calc(0.4mm - 1px);
            margin-bottom: calc(0mm - var(--row-comp));
            margin-left: 9.8mm;
            margin-top: 3.5mm;
            width: 189.3mm;
            display: grid;
            grid-template-columns: 46.8mm repeat(3, 1px calc(47.5mm - 1px));
            grid-auto-rows: auto;
            row-gap: 1px;
            font-size: 8pt;
        }
        .aa .cell {
            display: flex;
            align-items: center;
            overflow: hidden;
            white-space: nowrap;
            background-color: var(--row-grey);
            height: calc(3.81mm + var(--row-comp));
            /* Centred top to bottom as .perf .cell (Trello 374 / 391). */
            font-family: 'Avenir Next Cap', 'Avenir Next', 'Lato', sans-serif;
            padding-top: 0.3mm;
            padding-bottom: 0.3mm;
        }
        .aa .head {
            background-color: var(--dark-navy);
            color: var(--white);
            font-weight: 500;
            text-transform: uppercase;
            /* Trello 374: the three fund columns are right-aligned (headers
               and values 3.4mm in from the column edge, as the reference).
               Trello 402: the two header lines set solid (8pt on 8pt, the
               designer's sample: 7.8pt pitch), block centred in the cell. */
            justify-content: flex-end;
            text-align: right;
            line-height: 8pt;
            padding: 0.5mm 3.4mm 0.5mm 0;
            white-space: normal;
            height: calc(7.7mm + var(--row-comp));
        }
        .aa .head.head-label { justify-content: flex-start; text-align: left; padding-left: 1.2mm; }
        .aa .cell.label { padding-left: 1.2mm; }
        .aa .cell.value { justify-content: flex-end; padding-right: 3.4mm; }
        .aa .row-total .cell { background-color: var(--naartjie); color: var(--white); font-weight: 500; text-transform: uppercase; }
        /* The cap-centred twin has no italic faces; keep the real italic. */
        .aa .row-muted .cell { font-style: italic; font-family: 'Avenir Next', 'Lato', sans-serif; }
        .row-contents { display: contents; }

        .sector-block { margin-left: 9.9mm; width: 190mm; margin-top: 2.6mm; }
        /* Trello 402: the sector names sit 7px (1.85mm) closer to the axis
           (labelY below); the box is 7px shorter so the plot keeps its size
           and the legend comes up with the names. */
        #sectorChart { width: 190mm; height: calc(70mm - 7px); margin-top: 4.5mm; }

        /* Trello 449: back beside the QR block (Trello 402 had lifted it to
           the chart legend). It is for the whole overview, not the chart,
           and sits where the local overview puts it. */
        .rounding-note {
            position: absolute;
            left: 15.7mm;
            top: 271.6mm;
            font-family: 'Lato', 'Avenir Next', sans-serif;
            font-size: 5.1pt;
            line-height: 2.2mm;
        }
        .qr-block {
            position: absolute;
            left: 134.6mm;
            top: 262.9mm;
            width: 45.1mm;
            height: 18.0mm;
            background-color: var(--dark-navy);
            color: var(--white);
            font-size: 6.45pt;
            line-height: 3.7mm;
            text-align: center;
            padding: 1.3mm 1mm 0;
        }
        .qr-block .qr-text { padding: 0 5mm; }
        .qr-block .qr-url { white-space: nowrap; }
        .qr-block .qr-heading { color: var(--naartjie); text-transform: uppercase; }
        /* Trello 402: the URL at the block's one size (it was 7.2pt). */
        .qr-block a { color: var(--white); text-decoration: none; }
        .qr-image {
            position: absolute;
            left: 181.4mm;
            top: 262.9mm;
            width: 18.0mm;
            height: 18.0mm;
        }

        /* Print optimizations */
        @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .page { page-break-after: always; page-break-inside: avoid; }
        }
        /* =====================================================
           SCREEN CHROME (edit mode) - not printed
           ===================================================== */
        .editable { cursor: text; transition: all 0.15s; min-height: 1em; }
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
        $data = $fund->data;
        $perf = $data['mainContent']['performanceTable'] ?? [];
        $aa = $data['mainContent']['assetAllocation'] ?? [];
        $sectors = $data['mainContent']['sectorAllocation'] ?? [];
        $charts = $data['mainContent']['charts'] ?? [];
        $p2 = $data['page2Content'] ?? [];
        $footer = $data['footer'] ?? [];
        $date = $data['fund']['date'] ?? now()->format('d F Y');

        // Numeric cells: absent/blank → en dash; numbers → fixed decimals.
        // Inline edits coerce '10.0' to float 10, so always re-format here.
        $num = function ($v, int $dp = 1): string {
            if ($v === null || $v === '') {
                return '—';
            }
            return is_numeric($v) ? number_format((float) $v, $dp) : (string) $v;
        };
        $attr = fn ($v) => addslashes((string) ($v ?? ''));
        // Rotated group labels wrap onto two lines before the last word
        // ("BEST INVESTMENT / VIEW", "SPECIALIST / EQUITY") — Trello 226.
        $wrapLast = function (string $label): string {
            $words = preg_split('/\s+/', trim($label));
            if (count($words) < 2) {
                return e($label);
            }
            $last = array_pop($words);
            return e(implode(' ', $words)).'<br>'.e($last);
        };
        $columnKeys = $perf['columnKeys'] ?? ['20yrs', '15yrs', '10yrs', '5yrs', '3yrs', '1yr'];
        $headers = $perf['headers'] ?? ['20 YEARS', '15 YEARS', '10 YEARS', '5 YEARS', '3 YEARS', '1 YEAR'];
    @endphp
    <!-- ==================== PAGE 1 ==================== -->
    <div class="page">
        <div class="header">
            <div class="date-badge">
                <span x-data="editableField('fund.date', '{{ $attr($date) }}')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ $date }}</span>
            </div>
            <div class="logo">
                <img src="{{ $data['fund']['logoUrl'] ?: 'https://foord.co.za/themes/custom/mirum/logo.png' }}" alt="FOORD">
            </div>
        </div>

        <div class="title-banner">
            <h1 class="sheet-title"><span x-data="editableField('fund.name', '{{ $attr($data['fund']['name'] ?? $fund->name) }}', 'upper')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ mb_strtoupper($data['fund']['name'] ?? $fund->name) }}</span></h1>
            <p class="sheet-description"><span x-data="editableField('fund.description', '{{ $attr($data['fund']['description'] ?? '') }}')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ $data['fund']['description'] ?? '' }}</span></p>
        </div>

        <div class="band" style="margin-top: 3.8mm;"><span x-data="editableField('mainContent.performanceTable.title', '{{ $attr($perf['title'] ?? 'PERFORMANCE %') }}')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ $perf['title'] ?? 'PERFORMANCE %' }}</span></div>

        <div class="perf" style="margin-top: 3.0mm;">
            <div class="cell head head-label"></div>
            @foreach ($headers as $hi => $header)
                <div></div>
                <div class="cell head">{{ $header }}</div>
            @endforeach

            <div class="spacer-head"></div>
            @foreach ($perf['groups'] ?? [] as $gi => $group)
                @php
                    $fundCount = count($group['funds'] ?? []);
                    // 3 rows per fund (+ a feeder-note row where present) + a spacer row between funds
                    $span = max(0, $fundCount - 1);
                    foreach ($group['funds'] ?? [] as $entry) {
                        $span += empty($entry['feederNote']) ? 3 : 4;
                    }
                @endphp
                @if ($gi > 0)
                    <div class="spacer-group"></div>
                @endif
                <div class="group-label" style="grid-row: span {{ $span }};"><span><span x-data="editableField('mainContent.performanceTable.groups.{{ $gi }}.label', '{{ $attr($group['label'] ?? '') }}', 'wrapLast')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{!! $wrapLast($group['label'] ?? '') !!}</span></span></div>
                @foreach ($group['funds'] ?? [] as $fi => $entry)
                    @php $base = "mainContent.performanceTable.groups.{$gi}.funds.{$fi}"; @endphp
                    @if ($fi > 0)
                        <div class="spacer-fund"></div>
                    @endif
                    @foreach (array_filter(['fund', 'peer', 'benchmark', 'feeder'], fn ($k) => $k !== 'feeder' || ! empty($entry['feederNote'])) as $rowKey)
                        @if ($rowKey === 'feeder')
                        <div class="row-contents row-feeder">
                            <div></div>
                            <div class="cell label"><span x-data="editableField('{{ $base }}.feederNote', '{{ $attr($entry['feederNote']) }}')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ $entry['feederNote'] }}</span></div>
                        </div>
                        @continue
                        @endif
                        <div class="row-contents row-{{ $rowKey }}">
                            <div></div>
                            <div class="cell label">
                                @if ($rowKey === 'fund')
                                    <span class="fund-name" x-data="editableField('{{ $base }}.name', '{{ $attr($entry['name'] ?? '') }}')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ $entry['name'] ?? '' }}</span>&nbsp;<span x-data="editableField('{{ $base }}.className', '{{ $attr($entry['className'] ?? '') }}', 'parens')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">({{ $entry['className'] ?? '' }})</span>
                                @elseif ($rowKey === 'peer')
                                    <span x-data="editableField('{{ $base }}.peerLabel', '{{ $attr($entry['peerLabel'] ?? '') }}')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ $entry['peerLabel'] ?? '' }}</span>
                                @else
                                    <span x-data="editableField('{{ $base }}.benchmarkLabel', '{{ $attr($entry['benchmarkLabel'] ?? '') }}')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ $entry['benchmarkLabel'] ?? '' }}</span>
                                @endif
                            </div>
                            @foreach ($columnKeys as $key)
                                @php $v = $entry[$rowKey][$key] ?? null; @endphp
                                <div></div>
                                <div class="cell value"><span x-data="editableField('{{ $base }}.{{ $rowKey }}.{{ $key }}', '{{ $attr($v) }}', 'num1')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ $num($v) }}</span></div>
                            @endforeach
                        </div>
                    @endforeach
                @endforeach
            @endforeach
        </div>

        <div class="perf-footnotes">
            @foreach ($perf['footnotes'] ?? [] as $ni => $note)
                <p><span x-data="editableField('mainContent.performanceTable.footnotes.{{ $ni }}', '{{ $attr($note) }}')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ $note }}</span></p>
            @endforeach
        </div>

        @foreach (['synopsis', 'strategy'] as $si => $section)
            @php $block = $p2[$section] ?? []; @endphp
            <div class="band band-shifted" style="margin-top: {{ $si === 0 ? '5.7mm' : '5.9mm' }};">
                <span x-data="editableField('page2Content.{{ $section }}.title', '{{ $attr($block['title'] ?? mb_strtoupper($section)) }}')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ $block['title'] ?? mb_strtoupper($section) }}</span>&nbsp;(<span x-data="editableField('page2Content.{{ $section }}.quarter', '{{ $attr($block['quarter'] ?? '') }}')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ $block['quarter'] ?? '' }}</span>)
            </div>
            <div class="bullet-cols">
                @foreach ([['world', 'WORLD'], ['asia', 'ASIA']] as [$colKey, $colTitle])
                    <div class="bullet-col">
                        <h3>{{ $colTitle }}</h3>
                        <ul>
                            @foreach ($block[$colKey] ?? [] as $bi => $bullet)
                                <li><span x-data="editableField('page2Content.{{ $section }}.{{ $colKey }}.{{ $bi }}', '{{ $attr($bullet) }}')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ $bullet }}</span></li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        @endforeach

        <div class="footer-bar"><span x-data="editableField('footer.info', '{{ $attr($footer['info'] ?? '') }}')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ $footer['info'] ?? '' }}</span></div>
    </div>

    <!-- ==================== PAGE 2 ==================== -->
    <div class="page">
        <div class="header">
            <div class="date-badge"><span>{{ $date }}</span></div>
            <div class="logo">
                <img src="{{ $data['fund']['logoUrl'] ?: 'https://foord.co.za/themes/custom/mirum/logo.png' }}" alt="FOORD">
            </div>
        </div>

        @php
            $geo = $charts['geographicExposure'] ?? [];
            // Trello 374: a region with no exposure (the reference's 0.0%
            // "EM Latin America") gets neither a slice nor a legend key.
            foreach ($geo['funds'] ?? [] as $gi => $pie) {
                $geo['funds'][$gi]['slices'] = array_values(array_filter(
                    $pie['slices'] ?? [],
                    fn ($sl) => is_numeric($sl['value'] ?? null) && (float) $sl['value'] > 0
                ));
            }
            $pieLeft = ['875' => 8.2, '877' => 74.5, '879' => 142.0]; // relative to the .geo box (page x − 9.9mm)
            // Slate (#697277) comes before pale grey: the Asia pie uses seven
            // colours and pale grey next to light grey made "Other" and
            // Korea/Taiwan indistinguishable (Trello 227, 28 Sept).
            $sliceColours = ['#d25347', '#29363d', '#cccccc', '#7a9cb4', '#535353', '#e2cea4', '#697277', '#bfc3c5'];
            // Trello 227: a region keeps one colour across the three pies
            // (North America = the Asia pie's United States, etc.). Names not
            // listed take the first palette colour not already claimed in that
            // pie, in rank order.
            $regionColours = [
                'north america' => '#d25347',
                'united states of america' => '#d25347',
                'europe' => '#29363d',
                'em asia' => '#cccccc',
                'pacific' => '#7a9cb4',
                'africa & middle east' => '#e2cea4',
                'em latin america' => '#535353',
            ];
            $pieColours = [];
            foreach ($geo['funds'] ?? [] as $gi => $pie) {
                $names = array_map(fn ($sl) => mb_strtolower(trim($sl['name'] ?? '')), $pie['slices'] ?? []);
                $taken = array_values(array_filter(array_map(fn ($n) => $regionColours[$n] ?? null, $names)));
                $free = array_values(array_diff($sliceColours, $taken));
                $pieColours[$gi] = array_map(function ($n) use ($regionColours, &$free, $sliceColours) {
                    return $regionColours[$n] ?? (array_shift($free) ?? $sliceColours[0]);
                }, $names);
            }
        @endphp
        <div class="band" style="margin-top: 10.5mm;"><span x-data="editableField('mainContent.charts.geographicExposure.title', '{{ $attr($geo['title'] ?? 'GEOGRAPHIC EXPOSURE') }}')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ $geo['title'] ?? 'GEOGRAPHIC EXPOSURE' }}</span></div>

        <div class="geo" id="geoPieCharts">
            @foreach ($geo['funds'] ?? [] as $gi => $pie)
                @php $left = $pieLeft[(string) ($pie['code'] ?? '')] ?? (8.2 + $gi * 66.9); @endphp
                <div class="geo-title" style="left: {{ $left - 1.5 }}mm;"><span x-data="editableField('mainContent.charts.geographicExposure.funds.{{ $gi }}.label', '{{ $attr($pie['label'] ?? '') }}')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ $pie['label'] ?? '' }}</span>@if (! empty($pie['footnote']))<sup>{{ $pie['footnote'] }}</sup>@endif</div>
                <div class="geo-pie" id="geoPie{{ $gi }}" style="left: {{ $left }}mm;"></div>
                <ul class="geo-legend" style="left: {{ $left + 3.3 }}mm;">
                    @foreach ($pie['slices'] ?? [] as $si => $slice)
                        <li style="--dot: {{ $pieColours[$gi][$si] ?? $sliceColours[0] }};">{{ $slice['name'] ?? '' }}</li>
                    @endforeach
                </ul>
            @endforeach
            <div class="geo-footnotes">
                @foreach ($geo['footnotes'] ?? [] as $ni => $note)
                    <div><sup>{{ $note['marker'] ?? '' }}</sup><span x-data="editableField('mainContent.charts.geographicExposure.footnotes.{{ $ni }}.text', '{{ $attr($note['text'] ?? '') }}')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ $note['text'] ?? '' }}</span></div>
                @endforeach
            </div>
        </div>

        <div class="band"><span x-data="editableField('mainContent.assetAllocation.title', '{{ $attr($aa['title'] ?? 'ASSET ALLOCATION %') }}')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ $aa['title'] ?? 'ASSET ALLOCATION %' }}</span></div>

        <div class="aa">
            <div class="cell head head-label"></div>
            @foreach ($aa['columns'] ?? [] as $col)
                <div></div>
                <div class="cell head">{!! $col['label'] ?? '' !!}</div>
            @endforeach
            @foreach ($aa['rows'] ?? [] as $ri => $row)
                @php $style = $row['style'] ?? ''; @endphp
                <div class="row-contents {{ $style === 'total' ? 'row-total' : ($style === 'muted' ? 'row-muted' : '') }}">
                    <div class="cell label"><span x-data="editableField('mainContent.assetAllocation.rows.{{ $ri }}.label', '{{ $attr($row['label'] ?? '') }}')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ $row['label'] ?? '' }}</span></div>
                    @foreach ($aa['columns'] ?? [] as $col)
                        @php $code = (string) $col['code']; $v = $row['values'][$code] ?? null; @endphp
                        <div></div>
                        <div class="cell value"><span x-data="editableField('mainContent.assetAllocation.rows.{{ $ri }}.values.{{ $code }}', '{{ $attr($v) }}', 'num1')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ $num($v) }}</span></div>
                    @endforeach
                </div>
            @endforeach
        </div>

        <div class="band" style="margin-top: 4.2mm;"><span x-data="editableField('mainContent.sectorAllocation.title', '{{ $attr($sectors['title'] ?? 'SECTOR EXPOSURE (FOORD GLOBAL EQUITY FUND)') }}')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ $sectors['title'] ?? 'SECTOR EXPOSURE (FOORD GLOBAL EQUITY FUND)' }}</span></div>
        <div class="sector-block">
            <div id="sectorChart"></div>
        </div>

        <p class="rounding-note"><span x-data="editableField('page2Content.roundingNote', '{{ $attr($p2['roundingNote'] ?? '') }}')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ $p2['roundingNote'] ?? '' }}</span></p>
        @php $qr = $p2['qr'] ?? []; @endphp
        <div class="qr-block">
            <div class="qr-heading"><span x-data="editableField('page2Content.qr.heading', '{{ $attr($qr['heading'] ?? 'SCAN QR CODE') }}')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ $qr['heading'] ?? 'SCAN QR CODE' }}</span></div>
            <div class="qr-text"><span x-data="editableField('page2Content.qr.text', '{{ $attr($qr['text'] ?? '') }}')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ $qr['text'] ?? '' }}</span></div>
            <div class="qr-url"><a href="{{ $qr['url'] ?? '#' }}"><span x-data="editableField('page2Content.qr.url', '{{ $attr($qr['url'] ?? '') }}')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ $qr['url'] ?? '' }}</span></a></div>
        </div>
        <img class="qr-image" src="{{ asset($qr['image'] ?? 'images/qr-terms-global.png') }}" alt="QR code">

        <div class="footer-bar">T. {{ $footer['contact']['phone'] ?? '+27 21 532 6969' }} | E. {{ $footer['contact']['email'] ?? 'unittrusts@foord.co.za' }} | {{ $footer['contact']['website'] ?? 'www.foord.co.za' }}</div>
    </div>

    <!-- Highcharts -->
    <script src="https://cdn.jsdelivr.net/npm/highcharts@11/highcharts.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const pies = @json(array_values(array_map(fn ($p) => $p['slices'] ?? [], $geo['funds'] ?? [])));
            const sectorData = @json($sectors['sectors'] ?? []);
            const pieColours = @json($pieColours);

            const colors = { naartjie: '#d25347', darkNavy: '#29363d' };

            Highcharts.setOptions({
                chart: { style: { fontFamily: "'Avenir Next', 'Lato', sans-serif" } },
                credits: { enabled: false },
                accessibility: { enabled: false },
            });

            // Geographic pies: Highcharts starts at 12 o'clock and draws
            // clockwise in feed rank order; colours are keyed by region
            // (server-side $pieColours) so a region matches across pies.
            // Percentages sit inside the slice — white on dark slices, black
            // on light ones — and slices under 5% get a black label outside
            // the rim joined by a short leader line, as the reference does.
            const isLight = (hex) => {
                const n = parseInt(hex.slice(1), 16);
                const [r, g, b] = [(n >> 16) & 255, (n >> 8) & 255, n & 255];
                return (0.299 * r + 0.587 * g + 0.114 * b) > 150;
            };
            const pieLabelStyle = (color) => ({ fontSize: '6.4pt', fontWeight: '500', color, textOutline: 'none' });
            pies.forEach((slices, i) => {
                if (!slices.length) return;
                Highcharts.chart('geoPie' + i, {
                    chart: { type: 'pie', backgroundColor: 'transparent', spacing: [0, 0, 0, 0], animation: false },
                    title: { text: null },
                    tooltip: { enabled: false },
                    legend: { enabled: false },
                    plotOptions: {
                        pie: {
                            size: 151,
                            center: ['50%', '50%'],
                            borderWidth: 0.75, borderColor: '#ffffff',
                            startAngle: 0,
                            dataLabels: {
                                enabled: true,
                                distance: -24,
                                connectorWidth: 0,
                                allowOverlap: true,
                                crop: false, overflow: 'allow',
                                formatter: function () { return this.y > 0 ? Number(this.y).toFixed(1) + '%' : null; },
                            },
                            animation: false,
                        },
                    },
                    series: [{
                        data: slices.map((s, k) => {
                            const color = (pieColours[i] || [])[k] || '#d25347';
                            const point = {
                                name: s.name, y: Number(s.value ?? 0), color,
                                dataLabels: { style: pieLabelStyle(isLight(color) ? '#000000' : '#ffffff') },
                            };
                            if (point.y < 5) {
                                point.dataLabels = {
                                    distance: 10,
                                    connectorWidth: 0.6, connectorColor: '#7f7f7f', connectorPadding: 1,
                                    connectorShape: 'straight',
                                    style: pieLabelStyle('#000000'),
                                };
                            }
                            return point;
                        }),
                    }],
                });
            });

            const wrapLabel = (text, max) => {
                const tokens = [];
                for (const word of text.split(' ')) {
                    if (word === '/' && tokens.length) tokens[tokens.length - 1] += ' /';
                    else tokens.push(word);
                }
                const lines = [];
                let line = '';
                for (const tok of tokens) {
                    if (!line) { line = tok; continue; }
                    if ((line + ' ' + tok).length > max) { lines.push(line); line = tok; }
                    else line += ' ' + tok;
                }
                if (line) lines.push(line);
                return lines
                    .map(l => (l.length > max + 2 && !l.includes(' ')) ? l.slice(0, max - 2) + '-<br>' + l.slice(max - 2) : l)
                    .join('<br>');
            };

            // Grouped Fund/Benchmark columns, square bars, no gridlines, ticks
            // outward, centred square-marker legend below the category labels.
            const renderGroupedColumns = (containerId, rows, opts) => {
                if (!rows.length) return;
                const values = rows.flatMap(r => [Number(r.fund ?? 0), Number(r.benchmark ?? 0)]);
                const step = opts.tickInterval;
                const yMin = 0;
                const yMax = Math.max(opts.minMax ?? 0, Math.ceil((Math.max(...values) + step * 0.05) / step) * step);
                const ticks = [];
                for (let t = yMin; t <= yMax; t += step) ticks.push(t);

                Highcharts.chart(containerId, {
                    chart: { type: 'column', backgroundColor: 'transparent', spacing: opts.spacing, animation: false },
                    title: { text: null },
                    xAxis: {
                        categories: rows.map(r => r.name),
                        // Trello 229: light-grey axis with a small notch
                        // between each sector.
                        lineWidth: 1, lineColor: '#bfbfbf',
                        tickWidth: 1, tickLength: 5, tickColor: '#bfbfbf',
                        tickmarkPlacement: 'between',
                        labels: {
                            style: { fontSize: opts.labelSize, color: '#000', textAlign: 'center', whiteSpace: 'normal', textOverflow: 'none' },
                            useHTML: false,
                            formatter: function () { return wrapLabel(String(this.value), 13); },
                            rotation: 0, autoRotation: false,
                            y: opts.labelY ?? 10,
                        },
                    },
                    yAxis: {
                        title: { text: null },
                        min: ticks[0], max: ticks[ticks.length - 1],
                        tickPositions: ticks,
                        gridLineWidth: 0,
                        lineWidth: 1, lineColor: '#000',
                        tickWidth: 1, tickLength: 3, tickColor: '#000',
                        labels: {
                            style: { fontSize: opts.axisSize, color: '#000' },
                            formatter: function () { return this.value + '%'; },
                            x: -8,
                        },
                    },
                    legend: {
                        itemStyle: { fontSize: opts.legendSize, fontWeight: 'normal', color: '#000' },
                        symbolWidth: 6, symbolHeight: 6, symbolRadius: 0,
                        itemDistance: opts.legendGap, margin: opts.legendMargin, padding: 0,
                        squareSymbol: true,
                    },
                    tooltip: { enabled: false },
                    plotOptions: {
                        column: { pointPadding: opts.pointPadding, groupPadding: opts.groupPadding, borderWidth: 0, borderRadius: 0 },
                        series: { animation: false },
                    },
                    series: [
                        { name: 'Fund', data: rows.map(r => (r.fund === null || r.fund === undefined) ? null : Number(r.fund)), color: colors.naartjie },
                        { name: 'Benchmark', data: rows.map(r => (r.benchmark === null || r.benchmark === undefined) ? null : Number(r.benchmark)), color: colors.darkNavy },
                    ],
                });
            };

            renderGroupedColumns('sectorChart', sectorData, {
                tickInterval: 5, minMax: 35,
                spacing: [4, 2, 0, 0],
                labelSize: '5.5pt', axisSize: '6pt', legendSize: '6pt',
                legendGap: 37, legendMargin: 8, labelY: 15,
                // Trello 229: a clear gap between the Fund and Benchmark bars.
                pointPadding: 0.09, groupPadding: 0.14,
            });
        });
    </script>
    <script>
        // Trello 391 / 402: band headings and table text centred top to
        // bottom. The same idea as partials/table-centring (which only walks
        // <table> rows): Chrome paints a box's edges and its text baseline at
        // whole pixels, so per element predict both painted positions (from
        // the top of the element's own page — each PDF page is its own grid)
        // and move whole pixels of padding from bottom to top until the cap
        // height sits within half a pixel of the middle. Heights never change.
        function centreOverviewText() {
            var CAP = 0.708; // Avenir Next cap height / em
            function textNodes(el) {
                var out = [], w = document.createTreeWalker(el, NodeFilter.SHOW_TEXT, {
                    acceptNode: function (n) {
                        return /[0-9A-Za-z]/.test(n.nodeValue) && !n.parentElement.closest('sup') ? NodeFilter.FILTER_ACCEPT : NodeFilter.FILTER_REJECT;
                    }
                });
                while (w.nextNode()) out.push(w.currentNode);
                return out;
            }
            // A text line's box ends at baseline + descent: 0 for the
            // cap-centred twin, 25% (pinned, rounded to px) for Avenir Next.
            function baselineAt(node, last) {
                var range = document.createRange();
                range.selectNodeContents(node);
                var rects = range.getClientRects();
                if (!rects.length) return null;
                var cs = getComputedStyle(node.parentElement);
                var descent = /^"?Avenir Next Cap/.test(cs.fontFamily) ? 0 : Math.round(0.25 * parseFloat(cs.fontSize));
                return rects[last ? rects.length - 1 : 0].bottom - descent;
            }
            function offset(el) {
                var nodes = textNodes(el);
                if (!nodes.length) return null;
                var shift = -el.closest('.page').getBoundingClientRect().top;
                var r = el.getBoundingClientRect();
                var b0 = baselineAt(nodes[0], false), b1 = baselineAt(nodes[nodes.length - 1], true);
                if (b0 === null || b1 === null) return null;
                var capTop = Math.round(b0 + shift) - CAP * parseFloat(getComputedStyle(nodes[0].parentElement).fontSize);
                var base = Math.round(b1 + shift);
                var mid = (Math.round(r.top + shift) + Math.round(r.bottom + shift)) / 2;
                return Math.round(mid - (capTop + base) / 2);
            }
            function move(el, d) {
                if (!d) return;
                var cs = getComputedStyle(el), pt = parseFloat(cs.paddingTop), pb = parseFloat(cs.paddingBottom);
                d = Math.max(-pt, Math.min(pb, d));
                el.style.paddingTop = (pt + d) + 'px';
                el.style.paddingBottom = (pb - d) + 'px';
            }
            document.querySelectorAll('.page .band, .page .perf .cell, .page .aa .cell').forEach(function (el) {
                move(el, offset(el));
            });
        }
        document.addEventListener('DOMContentLoaded', function () {
            if (!document.fonts) return centreOverviewText();
            // Measure only once the faces the cells use are in.
            Promise.all(['400', '500', '600'].map(function (w) {
                return document.fonts.load(w + ' 8pt "Avenir Next Cap"');
            }).concat(document.fonts.load('italic 400 8pt "Avenir Next"')))
                .catch(function () {})
                .then(function () { return document.fonts.ready; })
                .then(centreOverviewText);
        });
    </script>
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
            upper(value) { return String(value).toUpperCase(); },
            parens(value) { return '(' + value + ')'; },
            wrapLast(value) {
                const esc = String(value).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
                const words = esc.trim().split(/\s+/);
                return words.length < 2 ? esc : words.slice(0, -1).join(' ') + '<br>' + words[words.length - 1];
            },
            raw(value) { return value === '' ? '-' : String(value); },
            num1(value) { return (value === '' || isNaN(Number(value))) ? (value === '' ? '—' : String(value)) : Number(value).toFixed(1); },
            num2(value) { return (value === '' || isNaN(Number(value))) ? (value === '' ? '-' : String(value)) : Number(value).toFixed(2); },
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
                    this.$el.innerHTML = fmt ? fmt(this.value) : this.value;
                }
            }
        }
    </script>
</body>
</html>
