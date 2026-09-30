{{--
    Reviewer "Global fixes — global funds" (Foord QC board card 430,
    30 Sept 2026) — one set of corrections for the six international
    sheets: Asia ex-Japan (879), Global Equity Australian Feeder (880),
    Global Equity (Lux) (877), International Fund (875), International
    Trust (874) and Foord-Hassen Shariah Global Equity (878).

    Included by those six templates LAST in <head> (after global-fixes and
    any template overrides that follow it), so equal-specificity rules win
    on source order. Fund 39 (877 Class B) was the reviewer's "working
    well" reference for the sidebar rhythm and the performance-table
    header; its settings are carried to the other sheets here.
--}}
@php
    $__gifTemplate = $fund->template ?? 'show';
@endphp
<style>
    /* ---- Same text layout on the server as on a Mac ----
       Avenir Next LT Pro sets USE_TYPO_METRICS, so Chrome on Linux (the
       staging / production PDF export) lays every line out on the OS/2
       typo metrics (758 / 242 / 200) while Chrome on macOS uses hhea
       (978 / 250 / 0): all Avenir text on these sheets printed ~1px higher
       on staging than in a local render, and table values sat right up
       against the top of their rows. Pinning the hhea metrics makes both
       platforms identical (the local sheets get the same pin in
       partials/avenir-fonts, card 429). ---- */
@foreach ([['Light', 300, 'normal'], ['LightIt', 300, 'italic'], ['Regular', 400, 'normal'], ['It', 400, 'italic'], ['Medium', 500, 'normal'], ['MediumIt', 500, 'italic'], ['Demi', 600, 'normal'], ['Bold', 700, 'normal']] as [$__gifFile, $__gifWeight, $__gifStyle])
    @font-face {
        font-family: 'Avenir Next';
        src: url('{{ asset('fonts/AvenirNextLTPro/AvenirNextLTPro-'.$__gifFile.'.ttf') }}') format('truetype');
        font-weight: {{ $__gifWeight }};
        font-style: {{ $__gifStyle }};
        font-display: swap;
        ascent-override: 97.8%;
        descent-override: 25%;
        line-gap-override: 0%;
    }
@endforeach

    /* ---- Cap-centred Avenir Next (table cells and navy bars) ----
       Chrome centres a line box on the font's ascent/descent, each rounded
       to whole pixels, and floors the half-leading; at 7.5–8pt on these
       sheets' 8.5–8.9pt leading that leaves capitals and figures up to
       1.1px (0.3mm) above the middle of the row even with hhea metrics.
       The same files re-declared with ascent 80% / descent 0% land the
       baseline within ±0.56px of the cap-centred position for every
       size/leading pair the six sheets use (fitted against Chrome's
       rounding; hhea alone is out by up to 1.1px, typo metrics 2.1px).
       Line-gap keeps 'normal' line height at the face's 1.228em. ---- */
@foreach ([['Regular', 400], ['Medium', 500]] as [$__gifFile, $__gifWeight])
    @font-face {
        font-family: 'Avenir Next Cap';
        src: url('{{ asset('fonts/AvenirNextLTPro/AvenirNextLTPro-'.$__gifFile.'.ttf') }}') format('truetype');
        font-weight: {{ $__gifWeight }};
        font-style: normal;
        ascent-override: 80%;
        descent-override: 0%;
        line-gap-override: 42.8%;
    }
@endforeach

    /* ---- PG 1: date centred in the dark navy badge ---- */
    .date-badge {
        font-family: 'Avenir Next Cap', 'Avenir Next', 'Lato', sans-serif !important;
    }

    /* ---- PG 1: logo pulled left so its right edge lines up with the
       right-hand edge of the tables below (203.46mm; was 204.6mm) ---- */
    .header-logo {
        right: 6.55mm !important;
    }

    /* ---- PG 1 + 2: every heading, total and value centred in its row
       (and not crowding the top of the cell) ---- */
    table th,
    table td,
    .fee-rates-table td,
    .fee-table td,
    .tic-table td,
    .cost-table td,
    .pfe-table td {
        font-family: 'Avenir Next Cap', 'Avenir Next', 'Lato', sans-serif !important;
    }

    /* ---- PG 1: PORTFOLIO PERFORMANCE header — tighter two-line headings
       (fund 39's leading, rounded to 9pt = a whole 12px so every sheet
       lands the same 3.18mm line pitch) with each header row's height
       unchanged, and one-line headings (YTD) on the bottom line with the
       rest (880 had them top-aligned, card 365; the reviewer now wants
       bottom on every global sheet). ---- */
    table.perf-table thead th {
        line-height: 9pt !important;
        vertical-align: bottom !important;
@switch($__gifTemplate)
    @case('show-asia-ex-japan')
    @case('show-hassen-shariah')
        padding-top: 1.12mm !important;
        padding-bottom: 1.12mm !important;
        @break
    @case('show-international')
    @case('show-international-trust')
        padding-top: 0.875mm !important;
        padding-bottom: 0.875mm !important;
        @break
    @case('show-australian-feeder')
        padding-top: 0.75mm !important;
        padding-bottom: 0.75mm !important;
        @break
    @default
        padding-top: 1.195mm !important;
        padding-bottom: 1.195mm !important;
@endswitch
    }

    /* ---- PG 1: tops of copy aligned across the grey column and the
       right-hand column — the cap top of MARKETING COMMUNICATION level with
       the cap tops of the first headings on the right (was 0.2–1mm apart;
       the larger 8.9pt label had shared a baseline, not a cap line). ---- */
@switch($__gifTemplate)
    @case('show-asia-ex-japan')
    .sidebar { padding-top: 5.89mm !important; }
        @break
    @case('show-international-trust')
    .sidebar { padding-top: 5.1mm !important; }
        @break
    @case('show-global-equity')
    .sidebar { padding-top: 3.93mm !important; }
        @break
    @case('show-hassen-shariah')
    .sidebar { padding-top: 4.04mm !important; }
        @break
    @case('show-australian-feeder')
    /* 880's packed sidebar cannot move down, so the right column moves up. */
    .content-area { padding-top: 3.22mm !important; }
        @break
@endswitch
@if(in_array($__gifTemplate, ['show-hassen-shariah', 'show-australian-feeder'], true))
    /* "Change since / Variance to" sat 0.26mm below PORTFOLIO STRUCTURE %
       (10.9pt vs 9.4pt leading). */
    .ps-header .ps-header-col { position: relative; top: -0.75pt; }
@endif

    /* ---- PG 1: grey column — each sub-head sits tight on its value
       (same 9.35pt pitch as the value lines), with a 1.7mm gap before the
       next sub-head (fund 39's setting). The 880 feeder's long sidebar
       already runs heading-tight at 7.5pt/9pt with 4pt gaps and has no
       room for more. ---- */
@if(in_array($__gifTemplate, ['show-asia-ex-japan', 'show-international', 'show-international-trust'], true))
    .sidebar .sidebar-section h3,
    .sidebar .sidebar-section p,
    .sidebar .sidebar-section .sidebar-value {
        line-height: 9.35pt !important;
        margin: 0 !important;
    }
    .sidebar .sidebar-section { margin-bottom: 1.7mm !important; }
    .sidebar .sidebar-section:first-child { margin-bottom: 1.8mm !important; }
    .sidebar .sidebar-section:last-child { margin-bottom: 0 !important; }
    .sidebar .sidebar-section h3.marketing-communication { line-height: 10.4pt !important; }
@endif

    /* ---- Superscript note numbers — one 5pt size, set tight against the
       word (no space), tops a little above the ascender line of the text
       they follow (0.17mm on 8pt rows and headings, 0.3mm on the 7.5pt
       notes). Exception (reviewer): in the upper-case headings of the dark
       navy header bars the number tops out at the cap height. The raises
       are whole CSS px: Chrome snaps each text run's baseline to the pixel
       grid separately, so a fractional raise (e.g. 4.47px) printed 4px on
       one row and 5px on the next. ---- */
    .foord-table td sup,
    .section-heading sup,
    .ps-header sup,
    .ps-header-col sup,
    .footnote sup,
    .page2-note sup,
    .pfe-note sup,
    .foord-table th sup {
        font-size: 5pt !important;
        line-height: 0 !important;
        vertical-align: baseline !important;
        position: relative !important;
        top: -4px !important;
        margin: 0 !important;
        letter-spacing: 0 !important;
    }
    /* 6.9pt performance-fee note; dark navy header cells (caps only) */
    .pfe-note sup,
    .foord-table th sup { top: -3px !important; }

    /* ---- Notes: one hanging indent — the number sits in a fixed 1.7mm
       slot so every note's text (first and wrapped lines) starts on the
       same vertical; unnumbered notes start level with the numbers. ---- */
    .footnote,
    .page2-note,
    .pfe-note {
        padding-left: 1.7mm !important;
        text-indent: -1.7mm !important;
    }
    .note-num {
        display: inline-block;
        width: 1.7mm;
        text-indent: 0;
    }

    /* ---- PG 2: the IMPORTANT INFORMATION bar spans exactly the width of
       the grey-column copy below it; headline centred in the bar on its
       cap height, with tighter line spacing. ---- */
    .important-info-header {
        padding: 0 2mm !important;
@switch($__gifTemplate)
    @case('show-global-equity')
        margin-left: 9mm !important;
        margin-right: 4mm !important;
        @break
    @case('show-international')
        margin-left: 8mm !important;
        margin-right: 3mm !important;
        @break
    @case('show-international-trust')
        margin-left: 7.6mm !important;
        margin-right: 3.3mm !important;
        @break
    @default
        margin-left: 9mm !important;
        margin-right: 4.8mm !important;
@endswitch
    }
    .important-info-header h2 {
        font-family: 'Avenir Next Cap', 'Avenir Next', 'Lato', sans-serif !important;
        line-height: 9.4pt !important;
        /* keeps the two-line break after INFORMATION in the wider bar */
        max-width: 40mm;
        /* letter-spacing adds trailing space after each line's last letter;
           the matching lead keeps each line optically centred */
        padding-left: 0.02em;
    }
</style>
<script>
    // Client-side twins of App\Support\FactsheetText, spread into each
    // template's editableFormatters so an edited value re-renders the same.
    window.intlFormatters = {
        supDigits(value) {
            const digits = { '¹': '1', '²': '2', '³': '3', '⁴': '4', '⁵': '5', '⁶': '6', '⁷': '7', '⁸': '8', '⁹': '9' };
            return String(value).replace(/[¹²³⁴⁵⁶⁷⁸⁹]+/g,
                (m) => '<sup>' + [...m].map((c) => digits[c]).join('') + '</sup>');
        },
        supInsideBracket(value) {
            return String(value).replace(/\)([¹²³⁴⁵⁶⁷⁸⁹]+)/, '$1)');
        },
        noteHang(value) {
            return window.intlFormatters.supDigits(value)
                .replace(/^\s*(<sup>[^<]*<\/sup>)\s*/, '<span class="note-num">$1</span>');
        },
        footerInfo(value) {
            return String(value)
                .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                .replace(/(track record,)\s+/, '$1<br>');
        },
    };
</script>
