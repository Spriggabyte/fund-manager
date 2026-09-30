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
    $__gfIntl = in_array($__gfTemplate, \App\Models\Fund::INTERNATIONAL_TEMPLATES, true);
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
    .sidebar-heading,
    .sidebar-section .sidebar-heading {
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
@if($__gfTemplate === 'show-australian-feeder')
    /* Card 318: 880 sidebar — 0.85sp lines (9pt, set in the template)
       with 4pt after every paragraph, the Marketing communication label
       included (reference: 4.53–4.57mm heading-to-heading gaps). */
    .sidebar-section,
    .sidebar-section:first-child {
        margin-bottom: 4pt !important;
    }
    .sidebar-section:last-child {
        margin-bottom: 0 !important;
    }
@else
    .sidebar-section {
        margin-bottom: 0 !important;
    }
    /* The "Marketing communication" label is its own section with no body
       text; the design leaves ~1.8mm under it. */
    .sidebar-section:first-child {
        margin-bottom: 1.8mm !important;
    }
@endif

    /* ---- 298: international disclaimer column — Lato Light 8.5pt ---- */
    .info-sidebar-content p,
    .info-sidebar-content li {
        font-family: 'Lato', 'Avenir Next', sans-serif !important;
        font-weight: 300 !important;
        font-size: 8.5pt !important;
    }
@else
    /* ---- 292 + 300: local sidebar — body Avenir Next 7pt at 0.85 line
       spacing (8.2pt, matches the signed-off balanced design); the gap
       between sections is Trello 429's 1.85mm (was 4pt = 1.4mm).
       `.sidebar-section .sidebar-heading` is needed because the equity
       sheet's headings are <p>s: `.sidebar-section p` below would otherwise
       out-specify a bare `.sidebar-heading` and set them 7pt Regular
       (card 198). ---- */
    /* Trello 429: the rand-feeder family's <h3> headings (809/821/822/823)
       were missed by `.sidebar-heading` and stayed 7pt/8.2pt; their
       Publisher references set the same 6pt Medium heading as every other
       local sheet. Headings sit straight on their body copy (no margin) —
       fund 35's setting, which the reviewer holds up as the reference and
       which matches the Publisher rhythm (heading→body baseline ≈2.7mm). */
    .sidebar-heading,
    .sidebar-section .sidebar-heading,
    .sidebar-section h3 {
        font-family: 'Avenir Next', 'Lato', sans-serif !important;
        font-weight: 500 !important;
        font-size: 6pt !important;
        line-height: 6.8pt !important;
        letter-spacing: 0 !important;
        margin: 0 !important;
    }
    .sidebar-text,
    .sidebar-section p,
    .sidebar-section .sidebar-value {
        font-family: 'Avenir Next', 'Lato', sans-serif !important;
        font-weight: 400 !important;
        font-size: 7pt !important;
        line-height: 8.2pt !important;
        /* Trello 429: untracked, like the Publisher sheets — the 0.01em
           the templates carried set every line ~2% wider than the design,
           so copy wrapped a line early (809: two extra lines). */
        letter-spacing: 0 !important;
    }
    .sidebar-section {
        /* Trello 429: 1.85mm between one item's copy and the next heading
           (fund 35; Publisher body→next-heading baseline ≈4.36mm). */
        margin-bottom: 1.85mm !important;
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
    .ps-change.change-down::before { margin-right: 3mm; }
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

@unless($__gfIntl)
    /* ==== Trello 429 (30 Sept 2026) — global fixes, local funds ==== */

    /* Superscript footnote markers: one size, one height, no gap. The
       Publisher references set every marker at 65% of the copy it follows,
       raised 0.46em (top ≈1.2pt above the ascenders) and touching the word.
       Positioned relatively so the marker never changes a line's height.
       Unicode superscript digits are converted to <sup> by the script
       below, so data-supplied markers ("VALUE²", "ANNUALISED¹") match. */
    sup {
        font-size: 0.65em !important;
        line-height: 0 !important;
        vertical-align: baseline !important;
        position: relative !important;
        /* 0.708 × 0.65em ≈ 0.46em of the copy, in whole pixels so every
           marker sits the same height above its word (a fractional shift
           rounds differently row to row); rounded toward the baseline, so
           on 7.5pt copy the raise is 4px and the figure tops sit ~0.3mm
           above the ascenders — "slightly higher", and the same raise the
           international sheets use (card 430). */
        top: -0.708em !important; /* browsers without CSS round() */
        top: round(to-zero, -0.708em, 1px) !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
        letter-spacing: 0 !important;
    }

    /* Date centred in the naartjie badge. The badge flex-centres one line
       box; a line-height equal to the font's own height at 10pt (12pt =
       16px, ascent 13 + descent 3) leaves no leading for Chrome to round,
       so the date's cap height lands on the badge centre on every sheet
       (the feeder family inherited a leading that lifted it 0.33mm). */
    .date-badge {
        line-height: 12pt !important;
    }

    /* Performance table: headers stand on the bottom line of the navy bar,
       so single-line "YTD" / "THIS MONTH" line up with the two-line ones
       (reference: fund 39). Line spacing is set per template. */
    .performance-table table th,
    .perf-table th {
        vertical-align: bottom !important;
    }

    /* Numbered notes: the number sits in a fixed 1.4mm slot and every line
       of the note (first and wrapped) starts at the same x — the reviewer's
       dashed line runs down the text, not the numbers. The script below
       tags the leading marker and drops the space after it. Notes without
       a number ("Note: Totals may not cast…") stay flush with the numbers. */
    .note-hang {
        padding-left: 1.4mm !important;
        text-indent: -1.4mm !important;
    }
    .footnotes p:not(.note-hang),
    p.footnote:not(.note-hang) {
        padding-left: 0 !important;
        text-indent: 0 !important;
    }
    .note-hang sup.note-marker {
        display: inline-block;
        min-width: 1.4mm;
        text-indent: 0;
    }
@endunless
</style>
@unless($__gfIntl)
<script>
    // Trello 429: wrap Unicode superscript digits (stored headers and
    // headings carry "VALUE²", "ANNUALISED⁵"; Avenir Next has no ⁴–⁹, so
    // those fell back to Helvetica) in <sup> so every marker gets the rule
    // above. Runs after Alpine has rendered its x-text fields; charts draw
    // into SVG/canvas and are left alone.
    document.addEventListener('DOMContentLoaded', function () {
        var map = {'⁰': '0', '¹': '1', '²': '2', '³': '3', '⁴': '4', '⁵': '5', '⁶': '6', '⁷': '7', '⁸': '8', '⁹': '9'};
        var run = /[⁰¹²³⁴⁵⁶⁷⁸⁹]+(?:,[⁰¹²³⁴⁵⁶⁷⁸⁹]+)*/g;
        var walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT, {
            acceptNode: function (n) {
                if (!/[⁰¹²³⁴⁵⁶⁷⁸⁹]/.test(n.nodeValue)) return NodeFilter.FILTER_REJECT;
                return n.parentElement.closest('script, style, svg, textarea, sup, .no-print')
                    ? NodeFilter.FILTER_REJECT : NodeFilter.FILTER_ACCEPT;
            }
        });
        var nodes = [];
        while (walker.nextNode()) nodes.push(walker.currentNode);
        nodes.forEach(function (n) {
            var frag = document.createDocumentFragment(), text = n.nodeValue, last = 0, m;
            run.lastIndex = 0;
            while ((m = run.exec(text))) {
                frag.appendChild(document.createTextNode(text.slice(last, m.index)));
                var sup = document.createElement('sup');
                sup.textContent = m[0].replace(/[⁰¹²³⁴⁵⁶⁷⁸⁹]/g, function (c) { return map[c]; });
                frag.appendChild(sup);
                last = m.index + m[0].length;
            }
            frag.appendChild(document.createTextNode(text.slice(last)));
            n.parentNode.replaceChild(frag, n);
        });

        // No space between a word and its marker ("Fund ³" → "Fund³").
        document.querySelectorAll('.page sup').forEach(function (sup) {
            var prev = sup.previousSibling;
            while (prev && prev.nodeType === 1 && prev.lastChild) prev = prev.lastChild;
            if (prev && prev.nodeType === 3 && /\S\s+$/.test(prev.nodeValue)) prev.nodeValue = prev.nodeValue.replace(/\s+$/, '');
        });

        // Numbered notes: a <sup> before any other text is the note number.
        document.querySelectorAll('.footnotes p, p.footnote, p.footnotes').forEach(function (p) {
            var w = document.createTreeWalker(p, NodeFilter.SHOW_ELEMENT | NodeFilter.SHOW_TEXT), node, marker = null;
            while ((node = w.nextNode())) {
                if (node.nodeType === 1 && node.tagName === 'SUP') { marker = node; break; }
                if (node.nodeType === 3 && node.nodeValue.trim() !== '') break;
            }
            if (!marker) return;
            marker.classList.add('note-marker');
            p.classList.add('note-hang');
            var next = marker.nextSibling;
            if (next && next.nodeType === 3) next.nodeValue = next.nodeValue.replace(/^\s+/, '');
        });

    });

</script>
@include('funds.partials.table-centring')
@endunless
