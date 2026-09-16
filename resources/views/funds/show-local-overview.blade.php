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
           FOORD FUND OVERVIEW: SOUTH AFRICA — two-page multi-fund
           summary (fund code LOC). Cloned from show-domestic's chrome
           (palette, header, print rules, edit-mode JS); the body is a
           new layout with no sidebar. Every measurement below is in
           mm from the July 2026 Publisher reference
           (Funds/Fund overview - local/Local Fund Overview - July 2026.pdf).
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
            background-color: var(--naartjie);
            color: var(--white);
            font-family: 'Lato', 'Avenir Next', sans-serif;
            font-weight: 400;
            font-size: 8.7pt;
            letter-spacing: 0.01em;
            text-align: center;
            padding-top: 0.8mm;
        }
        .logo { position: absolute; top: 8.4mm; right: 10.5mm; height: 11mm; }
        .logo img { height: 100%; width: auto; }

        /* ---------- Title banner (navy) ---------- */
        .title-banner {
            position: relative;
            margin-left: 9.9mm;
            width: 190mm;
            height: 34.1mm;
            background-color: var(--dark-navy);
            color: var(--white);
            padding: 5.0mm 6mm 0 3.5mm;
        }
        .sheet-title {
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 500;
            font-size: 22.8pt;
            letter-spacing: 0.005em;
            text-transform: uppercase;
            line-height: 1.05;
            margin: 0 0 4.5mm 0;
        }
        .sheet-description {
            font-family: 'Merriweather', Georgia, serif;
            font-weight: 400;
            font-size: 9.2pt;
            line-height: 4.0mm;
            letter-spacing: 0.005em;
            margin-left: 0.7mm;
            color: var(--white);
        }

        /* ---------- Grey section bands ---------- */
        .band {
            height: 7.0mm;
            margin-left: 9.9mm;
            width: 190mm;
            background-color: var(--band-grey);
            color: var(--white);
            font-family: 'Avenir Next', 'Lato', sans-serif;
            font-weight: 500;
            font-size: 8pt;
            letter-spacing: 0.01em;
            text-transform: uppercase;
            display: flex;
            align-items: center;
            padding-left: 1.0mm;
        }
        /* Publisher places the synopsis/strategy bands and the footer bars
           1.3mm further right than the tables (11.2mm vs 9.9mm). */
        .band.band-shifted { margin-left: 11.2mm; width: 190.7mm; }

        /* ---------- Performance grid ---------- */
        .perf {
            margin-left: 9.5mm;
            width: 190.3mm;
            display: grid;
            grid-template-columns: 10.6mm 1.7mm 75.0mm repeat(6, 0.4mm 16.7mm);
            grid-auto-rows: auto;
            row-gap: 0.38mm;
            font-size: 8pt;
        }
        .perf .cell {
            display: flex;
            align-items: center;
            overflow: hidden;
            white-space: nowrap;
            height: 5.97mm;
        }
        .perf .head {
            background-color: var(--dark-navy);
            color: var(--white);
            font-weight: 500;
            font-size: 7.55pt;
            text-transform: uppercase;
            justify-content: center;
            height: 5.59mm;
        }
        .perf .head.head-label { grid-column: 1 / span 3; }
        .perf .cell.label { padding-left: 1.3mm; }
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
            font-size: 7.3pt;
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
        .perf .spacer-head { grid-column: 1 / -1; height: 3.6mm; }
        .perf .spacer-fund { grid-column: 2 / -1; height: 1.44mm; }
        .perf .spacer-group { grid-column: 1 / -1; height: 3.6mm; }

        .perf-footnotes {
            margin: 3.4mm 0 0 22.5mm;
            font-family: 'Lato', 'Avenir Next', sans-serif;
            font-size: 6.1pt;
            line-height: 2.5mm;
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
            font-size: 8.4pt;
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
        .bullet-col li::before {
            content: '';
            position: absolute;
            left: 1.4mm;
            top: 1.55mm;
            width: 1.0mm;
            height: 1.0mm;
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
            background-color: var(--naartjie);
            color: var(--white);
            font-family: 'Merriweather', Georgia, serif;
            font-size: 8.35pt;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        /* ---------- Page 2: statistics + asset allocation grids ---------- */
        .p2-row { display: flex; margin-left: 10.3mm; margin-top: 3.5mm; }

        .stats {
            width: 118.7mm;
            display: grid;
            grid-template-columns: 46.5mm repeat(4, 0.4mm 17.7mm);
            grid-auto-rows: auto;
            row-gap: 0.4mm;
            font-size: 8pt;
        }
        .aa {
            margin-left: 10.0mm;
            margin-top: 3.8mm;
            width: 189.8mm;
            display: grid;
            grid-template-columns: 57.9mm repeat(5, 0.4mm 26.0mm);
            grid-auto-rows: auto;
            row-gap: 0.4mm;
            font-size: 8pt;
        }
        .stats .cell, .aa .cell {
            display: flex;
            align-items: center;
            overflow: hidden;
            white-space: nowrap;
            background-color: var(--row-grey);
            height: 3.81mm;
        }
        .stats .head, .aa .head {
            background-color: var(--dark-navy);
            color: var(--white);
            font-weight: 500;
            text-transform: uppercase;
            align-items: flex-end;
            justify-content: flex-end;
            text-align: right;
            line-height: 3.9mm;
            padding: 0 1.95mm 0 0;
            white-space: normal;
        }
        .aa .head { padding-right: 3.2mm; }
        .stats .head { height: 11.5mm; }
        .aa .head { height: 11.56mm; }
        .stats .head.head-label, .aa .head.head-label { justify-content: flex-start; text-align: left; padding-left: 1.2mm; }
        .stats .cell.label, .aa .cell.label { padding-left: 1.2mm; }
        .stats .cell.value { justify-content: flex-end; padding-right: 1.95mm; }
        .aa .cell.value { justify-content: flex-end; padding-right: 3.2mm; }
        .stats .row-total .cell, .aa .row-total .cell { background-color: var(--naartjie); color: var(--white); font-weight: 500; text-transform: uppercase; }
        .aa .row-subtotal .cell { background-color: var(--naartjie-20); font-weight: 500; text-transform: uppercase; }
        .row-contents { display: contents; }

        .maturity-block { width: 64.5mm; margin-left: 3.3mm; margin-top: 0.4mm; }
        .maturity-block .chart-title {
            font-size: 6pt;
            text-align: center;
            line-height: 2.9mm;
            margin-bottom: 0.4mm;
            padding-left: 3mm;
        }
        #maturityChart { width: 64.5mm; height: 34.4mm; margin-top: -3.6mm; }

        .sector-block { margin-left: 9.9mm; width: 190mm; margin-top: 2.6mm; }
        #sectorChart { width: 190mm; height: 55mm; margin-top: 0.5mm; }

        .rounding-note {
            position: absolute;
            left: 16.0mm;
            top: 271.5mm;
            font-family: 'Lato', 'Avenir Next', sans-serif;
            font-size: 5.1pt;
            line-height: 2.2mm;
        }
        .qr-block {
            position: absolute;
            left: 131.6mm;
            top: 262.4mm;
            width: 48.1mm;
            height: 18.0mm;
            background-color: var(--dark-navy);
            color: var(--white);
            font-size: 6.45pt;
            line-height: 3.7mm;
            text-align: center;
            padding: 1.3mm 1mm 0;
        }
        .qr-block .qr-text { padding: 0 6.5mm; }
        .qr-block .qr-url { white-space: nowrap; }
        .qr-block .qr-heading { color: var(--naartjie); text-transform: uppercase; }
        .qr-block a { color: var(--white); text-decoration: underline; font-size: 7.2pt; }
        .qr-image {
            position: absolute;
            left: 181.4mm;
            top: 262.4mm;
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
                    // 3 rows per fund + a spacer row between funds
                    $span = $fundCount * 3 + max(0, $fundCount - 1);
                @endphp
                @if ($gi > 0)
                    <div class="spacer-group"></div>
                @endif
                <div class="group-label" style="grid-row: span {{ $span }};"><span x-data="editableField('mainContent.performanceTable.groups.{{ $gi }}.label', '{{ $attr($group['label'] ?? '') }}')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ $group['label'] ?? '' }}</span></div>
                @foreach ($group['funds'] ?? [] as $fi => $entry)
                    @php $base = "mainContent.performanceTable.groups.{$gi}.funds.{$fi}"; @endphp
                    @if ($fi > 0)
                        <div class="spacer-fund"></div>
                    @endif
                    @foreach (['fund', 'peer', 'benchmark'] as $rowKey)
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
            <div class="band band-shifted" style="margin-top: {{ $si === 0 ? '6.2mm' : '3.7mm' }};">
                <span x-data="editableField('page2Content.{{ $section }}.title', '{{ $attr($block['title'] ?? mb_strtoupper($section)) }}')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ $block['title'] ?? mb_strtoupper($section) }}</span>&nbsp;(<span x-data="editableField('page2Content.{{ $section }}.quarter', '{{ $attr($block['quarter'] ?? '') }}')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ $block['quarter'] ?? '' }}</span>)
            </div>
            <div class="bullet-cols">
                @foreach ([['world', 'WORLD'], ['southAfrica', 'SOUTH AFRICA']] as [$colKey, $colTitle])
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

        @php $fi = $p2['fixedIncome'] ?? []; @endphp
        <div class="band" style="margin-top: 5.4mm;"><span x-data="editableField('page2Content.fixedIncome.title', '{{ $attr($fi['title'] ?? 'FIXED INCOME FUNDS') }}')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ $fi['title'] ?? 'FIXED INCOME FUNDS' }}</span></div>

        <div class="p2-row">
            <div class="stats">
                <div class="cell head head-label"><span x-data="editableField('page2Content.fixedIncome.tableTitle', '{{ $attr($fi['tableTitle'] ?? 'PORTFOLIO STATISTICS') }}')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ $fi['tableTitle'] ?? 'PORTFOLIO STATISTICS' }}</span></div>
                @foreach ($fi['columns'] ?? [] as $col)
                    <div></div>
                    <div class="cell head">{!! $col['label'] ?? '' !!}</div>
                @endforeach
                @foreach ($fi['rows'] ?? [] as $ri => $row)
                    <div class="row-contents {{ ($row['style'] ?? '') === 'total' ? 'row-total' : '' }}">
                        <div class="cell label"><span x-data="editableField('page2Content.fixedIncome.rows.{{ $ri }}.label', '{{ $attr($row['label'] ?? '') }}')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ $row['label'] ?? '' }}</span></div>
                        @foreach ($fi['columns'] ?? [] as $col)
                            @php
                                $code = (string) $col['code'];
                                $v = $row['values'][$code] ?? '-';
                                $display = is_numeric($v) && ($row['key'] ?? '') !== 'yield' ? number_format((float) $v, 2) : (string) $v;
                            @endphp
                            <div></div>
                            <div class="cell value"><span x-data="editableField('page2Content.fixedIncome.rows.{{ $ri }}.values.{{ $code }}', '{{ $attr($v) }}', '{{ ($row['key'] ?? '') === 'yield' ? 'raw' : 'num2' }}')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ $display }}</span></div>
                        @endforeach
                    </div>
                @endforeach
            </div>
            @php $maturity = $charts['maturityData'] ?? []; @endphp
            <div class="maturity-block">
                <div class="chart-title"><span x-data="editableField('mainContent.charts.maturityData.title', '{{ $attr($maturity['title'] ?? 'Foord Bond Fund Maturity Breakdown') }}')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ $maturity['title'] ?? 'Foord Bond Fund Maturity Breakdown' }}</span></div>
                <div id="maturityChart"></div>
            </div>
        </div>

        <div class="band" style="margin-top: 7.4mm;"><span x-data="editableField('mainContent.assetAllocation.title', '{{ $attr($aa['title'] ?? 'ASSET ALLOCATION %') }}')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ $aa['title'] ?? 'ASSET ALLOCATION %' }}</span></div>

        <div class="aa">
            <div class="cell head head-label"></div>
            @foreach ($aa['columns'] ?? [] as $col)
                <div></div>
                <div class="cell head">{!! $col['label'] ?? '' !!}</div>
            @endforeach
            @foreach ($aa['rows'] ?? [] as $ri => $row)
                @php $style = $row['style'] ?? ''; @endphp
                <div class="row-contents {{ $style === 'total' ? 'row-total' : ($style === 'subtotal' ? 'row-subtotal' : '') }}">
                    <div class="cell label"><span x-data="editableField('mainContent.assetAllocation.rows.{{ $ri }}.label', '{{ $attr($row['label'] ?? '') }}')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ $row['label'] ?? '' }}</span></div>
                    @foreach ($aa['columns'] ?? [] as $col)
                        @php $code = (string) $col['code']; $v = $row['values'][$code] ?? null; @endphp
                        <div></div>
                        <div class="cell value"><span x-data="editableField('mainContent.assetAllocation.rows.{{ $ri }}.values.{{ $code }}', '{{ $attr($v) }}', 'num1')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ $num($v) }}</span></div>
                    @endforeach
                </div>
            @endforeach
        </div>

        <div class="band" style="margin-top: 7.4mm; margin-left: 10.2mm;"><span x-data="editableField('mainContent.sectorAllocation.title', '{{ $attr($sectors['title'] ?? 'SECTOR EXPOSURE (FOORD EQUITY)') }}')" @click="editMode && startEdit()" :class="editMode ? 'editable' : ''">{{ $sectors['title'] ?? 'SECTOR EXPOSURE (FOORD EQUITY)' }}</span></div>
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
        <img class="qr-image" src="{{ asset($qr['image'] ?? 'images/qr-terms-sa.png') }}" alt="QR code">

        <div class="footer-bar">T. {{ $footer['contact']['phone'] ?? '+27 21 532 6969' }} | E. {{ $footer['contact']['email'] ?? 'unittrusts@foord.co.za' }} | {{ $footer['contact']['website'] ?? 'www.foord.co.za' }}</div>
    </div>

    <!-- Highcharts -->
    <script src="https://cdn.jsdelivr.net/npm/highcharts@11/highcharts.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const maturityData = @json($charts['maturityData']['categories'] ?? []);
            const sectorData = @json($sectors['sectors'] ?? []);

            const colors = { naartjie: '#d25347', darkNavy: '#29363d' };

            Highcharts.setOptions({
                chart: { style: { fontFamily: "'Avenir Next', 'Lato', sans-serif" } },
                credits: { enabled: false },
                accessibility: { enabled: false },
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
                // The axis always starts at zero: a negative feed value (the
                // 826 0-1 year bucket some months) is clipped, not drawn
                // below the baseline, so the 0-50% frame never shifts.
                const yMin = 0;
                const yMax = Math.max(opts.minMax ?? 0, Math.ceil((Math.max(...values) + step * 0.05) / step) * step);
                const ticks = [];
                for (let t = yMin; t <= yMax; t += step) ticks.push(t);

                Highcharts.chart(containerId, {
                    chart: { type: 'column', backgroundColor: 'transparent', spacing: opts.spacing, animation: false },
                    title: { text: null },
                    xAxis: {
                        categories: rows.map(r => r.name),
                        lineWidth: 1, lineColor: '#000',
                        tickWidth: 0,
                        labels: {
                            style: { fontSize: opts.labelSize, color: '#000', textAlign: 'center', whiteSpace: 'normal', width: opts.labelWidth, textOverflow: 'none' },
                            useHTML: false,
                            formatter: function () { return opts.labelFormatter ? opts.labelFormatter(this.value) : this.value; },
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
                        plotLines: [{ value: 0, color: '#000', width: 1, zIndex: 4 }],
                        labels: {
                            style: { fontSize: opts.axisSize, color: '#000' },
                            formatter: function () { return this.value + '%'; },
                            x: opts.axisLabelX ?? -8,
                        },
                    },
                    legend: {
                        itemStyle: { fontSize: opts.legendSize, fontWeight: 'normal', color: '#000' },
                        symbolWidth: 6, symbolHeight: 6, symbolRadius: 0,
                        itemDistance: opts.legendGap, margin: opts.legendMargin, padding: 0,
                        x: opts.legendX ?? 0,
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

            renderGroupedColumns('maturityChart', maturityData, {
                tickInterval: 10, minMax: 50,
                spacing: [2, 2, 0, 0],
                labelSize: '5.3pt', axisSize: '5.3pt', legendSize: '6pt',
                legendGap: 38, legendMargin: 9, labelY: 12, legendX: 8, axisLabelX: -4,
                pointPadding: 0.05, groupPadding: 0.12,
                labelFormatter: (v) => String(v).replace(' ', '<br>'),
            });

            renderGroupedColumns('sectorChart', sectorData, {
                tickInterval: 5, minMax: 30,
                spacing: [4, 2, 0, 0],
                labelSize: '5.3pt', axisSize: '4.7pt', legendSize: '6pt',
                legendGap: 37, legendMargin: 21, labelY: 15,
                pointPadding: 0.02, groupPadding: 0.17,
                // Publisher wraps each sector label in a ~11mm box. Greedy
                // word-wrap at 11 characters reproduces every break in the
                // reference ("Consumer /" ⏎ "services", "Capital" ⏎ "goods /"
                // ⏎ "construction"); a slash stays with the word before it, and
                // a single word longer than the line is hyphenated
                // ("Telecommu-" ⏎ "nications").
                labelFormatter: (v) => wrapLabel(String(v), 11),
            });
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
