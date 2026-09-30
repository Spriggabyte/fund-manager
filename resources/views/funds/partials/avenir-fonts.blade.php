{{--
    Licensed Avenir Next LT Pro (Monotype), fonts/AvenirNextLTPro/ —
    replaces reliance on macOS's bundled "Avenir Next" system font so
    PDF/print rendering is correct on machines that don't have it
    installed (and so we're actually using the licensed copy). Weights
    match every 'Avenir Next' font-weight used across the factsheet
    templates (300/400/500/600/700, plus italic for 300/400/500).

    The .ttf files are TrueType-outline conversions of the licensed .otf
    (CFF) originals, made with scripts/otf2ttf.py. Chromium's PDF backend
    embeds CFF web fonts as unhinted Type3 fonts, which print noticeably
    heavier/rougher on some printers and viewers (Foord QC card 232);
    TrueType outlines are embedded as ordinary CIDFontType2 subsets.

    Vertical metrics (Trello 429, local sheets): the font sets
    USE_TYPO_METRICS, so Chrome on Linux (staging / production PDF export)
    lays text out on the OS/2 typo metrics (ascent 758, descent 242, line
    gap 200) while Chrome on macOS uses hhea (978 / 250 / 0). With a fixed
    line-height that puts every line of Avenir text ~1px HIGHER on the
    server than in a local render — table values, headers and badge dates
    sat "too close to the top" of their rows on staging only. Pinning the
    metrics to the hhea values makes both platforms lay out identically, and
    with them the line box centres the cap height (content-area centre
    0.364em above the baseline, caps 0.354em).
--}}
@php
    $__afPin = ! in_array($fund->template ?? 'show', \App\Models\Fund::INTERNATIONAL_TEMPLATES, true);
    $__afFaces = [
        ['Light', 300, 'normal'], ['LightIt', 300, 'italic'],
        ['Regular', 400, 'normal'], ['It', 400, 'italic'],
        ['Medium', 500, 'normal'], ['MediumIt', 500, 'italic'],
        ['Demi', 600, 'normal'], ['Bold', 700, 'normal'],
    ];
@endphp
<style>
@foreach ($__afFaces as [$__afFile, $__afWeight, $__afStyle])
    @font-face {
        font-family: 'Avenir Next';
        src: url('{{ asset('fonts/AvenirNextLTPro/AvenirNextLTPro-'.$__afFile.'.ttf') }}') format('truetype');
        font-weight: {{ $__afWeight }};
        font-style: {{ $__afStyle }};
        font-display: swap;
@if ($__afPin)
        ascent-override: 97.8%;
        descent-override: 25%;
        line-gap-override: 0%;
@endif
    }
@endforeach
@if ($__afPin)
    {{-- Cap-centred twin for table cells (partials/global-fixes). Chrome
         rounds ascent/descent to whole px and floors the half-leading, so
         even on hhea metrics 7–8pt Avenir on 8–9.4pt leading sat 0–1px
         above the middle of its row; ascent 80% / descent 0% puts the
         baseline within ±0.5px of the cap-centred position for any
         leading. Same face the international sheets use (card 430). --}}
@foreach ([['Light', 300], ['Regular', 400], ['Medium', 500], ['Demi', 600], ['Bold', 700]] as [$__afFile, $__afWeight])
    @font-face {
        font-family: 'Avenir Next Cap';
        src: url('{{ asset('fonts/AvenirNextLTPro/AvenirNextLTPro-'.$__afFile.'.ttf') }}') format('truetype');
        font-weight: {{ $__afWeight }};
        font-style: normal;
        font-display: swap;
        ascent-override: 80%;
        descent-override: 0%;
        line-gap-override: 42.8%;
    }
@endforeach
@endif
</style>
