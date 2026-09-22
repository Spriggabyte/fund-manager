{{--
    Reviewer global typography spec — Foord QC board, 22 Sept 2026
    (Trello cards 285, 290–307, "Global Fixes" list).

    Included by every fact-sheet template straight after its own <style>
    block, so on equal specificity these declarations win by source order;
    !important is used where a template's more specific per-table selector
    (e.g. `.tic-table table td`) would otherwise override the reviewer's
    value. Chart geometry (y-axis captions, "100" baseline label, legend
    row gap, bar thickness, credit-table gap) is template-specific and lives
    in the individual templates — see FUND-ONBOARDING.md §6.

    "International" here means the six global fund sheets the reviewer
    listed (Asia ex-Japan, Global Equity Australian Feeder, Global Equity
    (Lux), International Fund, International Trust, Hassen Shariah). The
    rand feeder funds (809/821/822/823) count as LOCAL sheets.
--}}
@php
    $__gfTemplate = $fund->template ?? 'show';
    $__gfIntl = in_array($__gfTemplate, [
        'show-international', 'show-international-trust', 'show-global-equity',
        'show-hassen-shariah', 'show-australian-feeder', 'show-asia-ex-japan',
    ], true);
@endphp
<style>
    /* ---- 290: date badge — Avenir Next Medium 10pt (was Lato 10.4pt) ---- */
    .date-badge {
        font-family: 'Avenir Next', 'Lato', sans-serif !important;
        font-weight: 500 !important;
        font-size: 10pt !important;
        letter-spacing: 0.01em !important;
        word-spacing: normal !important;
    }

    /* ---- 293 + 285: every table cell vertically centred (text was sitting
       at the top of the cell with dead space below the value) ---- */
    table th,
    table td {
        vertical-align: middle !important;
    }

    /* ---- 299: headings above the page-2 tables — Avenir Next Medium 7.5pt
       (the feeder sheets had 9.5pt, the international sheets 7.3–8pt) ---- */
    .page2-heading,
    .fees-content .section-heading,
    .page2-section .section-heading {
        font-family: 'Avenir Next', 'Lato', sans-serif !important;
        font-weight: 500 !important;
        font-size: 7.5pt !important;
        line-height: 9pt !important;
    }

    /* ---- 299: page-2 fee / TIC / cost / PFE tables — 8pt Avenir Next,
       plain values (only the "Foord global funds" sub-item rows and the red
       total rows keep Medium, as in the design) ---- */
    .fee-rates-table td,
    .fee-table td,
    .tic-table td,
    .cost-table td,
    .pfe-table td {
        font-family: 'Avenir Next', 'Lato', sans-serif !important;
        font-size: 8pt !important;
    }
    .fee-rates-table tr:not(.sub-item):not(.global-funds-header):not(.total-row) td {
        font-weight: 400 !important;
    }

    /* ---- 294: notes under the performance charts — Avenir Next 7.5pt
       (the Equity sheet's note is 7pt by design and keeps its own rule) ---- */
    .chart-explanation {
        font-family: 'Avenir Next', 'Lato', sans-serif !important;
        font-weight: 400 !important;
        font-size: 7.5pt !important;
    }

    /* ---- 296: footer rule ends under the second "i" of "Please visit"
       (design: 14.8mm underscore run; "Please visi" measures 14.4mm) ---- */
    .footer::after,
    .footer-divider::after,
    .footer-separator {
        width: 14.6mm !important;
    }

@if($__gfIntl)
    /* ---- 297 + 301: international sidebar — headings Avenir Next Medium
       8pt, body Avenir Next 8pt, single line spacing (1sp = 11pt for this
       face), 1pt before / 2.1pt after each paragraph.
       EXCEPTION: the Australian feeder's sidebar (880) is ~80 lines and
       overflows page 1 by 34mm at 8pt/11pt, so it keeps its own measured
       7.5pt/9pt sizing; only the paragraph-spacing rules apply to it. ---- */
@if($__gfTemplate !== 'show-australian-feeder')
    .sidebar-section h3,
    .sidebar-heading {
        font-family: 'Avenir Next', 'Lato', sans-serif !important;
        font-weight: 500 !important;
        font-size: 8pt !important;
        line-height: 11pt !important;
        margin: 1pt 0 0 0 !important;
    }
    .sidebar-section p,
    .sidebar-section .sidebar-value,
    .sidebar-text,
    .lipper-award .award-detail {
        font-family: 'Avenir Next', 'Lato', sans-serif !important;
        font-weight: 400 !important;
        font-size: 8pt !important;
        line-height: 11pt !important;
        margin: 1pt 0 2.1pt 0 !important;
    }
@endif
    .sidebar-section {
        margin-bottom: 0 !important;
    }
    /* The "Marketing communication" label is its own section with no body
       text; the design leaves ~1.8mm under it. */
    .sidebar-section:first-child {
        margin-bottom: 1.8mm !important;
    }

    /* ---- 298: international disclaimer column — Lato Light 8.5pt ---- */
    .info-sidebar-content p,
    .info-sidebar-content li {
        font-family: 'Lato', 'Avenir Next', sans-serif !important;
        font-weight: 300 !important;
        font-size: 8.5pt !important;
    }
@else
    /* ---- 292 + 300: local sidebar — body Avenir Next 7pt at 0.85 line
       spacing (8.2pt, matches the signed-off balanced design), 1pt before /
       3pt after each paragraph (4pt = 1.4mm between sections) ---- */
    .sidebar-heading {
        font-family: 'Avenir Next', 'Lato', sans-serif !important;
        font-weight: 500 !important;
        font-size: 6pt !important;
        line-height: 6.8pt !important;
    }
    .sidebar-text,
    .sidebar-section p,
    .sidebar-section .sidebar-value {
        font-family: 'Avenir Next', 'Lato', sans-serif !important;
        font-weight: 400 !important;
        font-size: 7pt !important;
        line-height: 8.2pt !important;
    }
    .sidebar-section {
        /* The rand feeder's (809) sidebar is 1.5mm too long at 4pt, so it
           keeps its measured 1.15mm section gap. */
        margin-bottom: {{ $__gfTemplate === 'show-feeder' ? '1.15mm' : '1.4mm' }} !important;
    }
    .sidebar-section:last-child {
        margin-bottom: 0 !important;
    }

    /* ---- 295: local disclaimer column — Lato Light 6.5pt ---- */
    .info-sidebar-content p,
    .info-sidebar-content li {
        font-family: 'Lato', 'Avenir Next', sans-serif !important;
        font-weight: 300 !important;
        font-size: 6.5pt !important;
    }
@endif

    /* ---- 305 + 307: change triangles. Drawn as fixed-size SVG (1.7 x
       1.55mm ≈ the digit cap height) instead of the ▲/▼ glyphs, whose size
       depended on whichever fallback font the viewer's machine supplied
       (Avenir Next has no geometric-shape glyphs, so the staging preview
       showed tiny triangles). Up = black, down = steel blue. ---- */
    .change-up::before,
    .change-down::before,
    .alloc-arrow.change-up::before,
    .alloc-arrow.change-down::before,
    .ps-arrow.change-up::before,
    .ps-arrow.change-down::before {
        content: '' !important;
        display: inline-block !important;
        width: 1.7mm !important;
        height: 1.55mm !important;
        font-size: 0 !important;
        line-height: 0 !important;
        vertical-align: -0.05mm !important;
        transform: none !important;
        background-repeat: no-repeat !important;
        background-position: center !important;
        background-size: 1.7mm 1.55mm !important;
        margin: 0 0.9mm 0 0;
    }
    .change-up::before,
    .alloc-arrow.change-up::before,
    .ps-arrow.change-up::before {
        background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 10 9'><path d='M5 0L10 9H0z' fill='%23000000'/></svg>") !important;
    }
    .change-down::before,
    .alloc-arrow.change-down::before,
    .ps-arrow.change-down::before {
        background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 10 9'><path d='M0 0h10L5 9z' fill='%237a9cb4'/></svg>") !important;
    }
@if($__gfTemplate === 'show-global-equity-feeder')
    .ps-change.change-up::before,
    .ps-change.change-down::before { margin-right: 2.7mm; }
@elseif($__gfTemplate === 'show-prescient-global-equity')
    .ps-change.change-up::before,
    .ps-change.change-down::before { margin-right: 6.4mm; }
@endif

    /* The balanced-family tables already draw inline-SVG arrows; bring them
       up to the same size (design measured 1.45 x 1.39mm; reviewer asked
       for larger). */
    td.change-cell .change-arrow-up,
    td.change-cell .change-arrow-down {
        width: 1.7mm !important;
        height: 1.55mm !important;
    }

    /* 307: in the asset-allocation bar rows the triangle sits centred in
       the gap between the value column and the change figure — the arrow
       is a flex item that takes up the spare width, so it centres itself
       regardless of the number's width. */
    .alloc-change.change-up,
    .alloc-change.change-down {
        display: inline-flex !important;
        justify-content: flex-end;
        align-items: center;
    }
    .alloc-change.change-up::before,
    .alloc-change.change-down::before {
        flex: 1 1 auto;
        margin-right: 0 !important;
    }
    .alloc-arrow {
        display: inline-flex !important;
        justify-content: center;
        align-items: center;
    }
    .alloc-arrow.change-up::before,
    .alloc-arrow.change-down::before {
        margin-right: 0 !important;
    }

    /* ---- 302: two-line chart legends — rows sit closer together ---- */
    .chart-legend {
        row-gap: 0.15mm !important;
    }
</style>
