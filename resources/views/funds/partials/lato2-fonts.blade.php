{{--
    Lato 2.015 (latofonts.com, SIL OFL — public/fonts/Lato2/OFL.txt) as
    family 'Lato 2'. The Prescient Publisher sheets set their important-
    information and contact columns in this desktop Lato; Google Fonts
    still serves Lato 1.104, whose spaces are 30% narrower (386 vs 543
    units), whose glyph advances differ and whose Light is a lighter cut.
    In Lato 1 the copy packed a word or two more per line and printed
    thinner and greyer than the reference (QC 27 Oct 2026, fund 57).

    The fonts have no USE_TYPO_METRICS flag and their hhea and win metrics
    match (1974 / 426), so Chrome on macOS and Linux lays them out
    identically — no ascent/descent overrides needed.
--}}
<style>
@foreach ([['Light', 300, 'normal'], ['LightItalic', 300, 'italic'], ['Regular', 400, 'normal']] as [$__l2File, $__l2Weight, $__l2Style])
    @font-face {
        font-family: 'Lato 2';
        src: url('{{ asset('fonts/Lato2/Lato-'.$__l2File.'.ttf') }}') format('truetype');
        font-weight: {{ $__l2Weight }};
        font-style: {{ $__l2Style }};
        font-display: swap;
    }
@endforeach
</style>
