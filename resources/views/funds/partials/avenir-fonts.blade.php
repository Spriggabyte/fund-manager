<style>
    /* Licensed Avenir Next LT Pro (Monotype), fonts/AvenirNextLTPro/ —
       replaces reliance on macOS's bundled "Avenir Next" system font so
       PDF/print rendering is correct on machines that don't have it
       installed (and so we're actually using the licensed copy). Weights
       match every 'Avenir Next' font-weight used across the factsheet
       templates (300/400/500/600/700, plus italic for 300/400/500).

       The .ttf files are TrueType-outline conversions of the licensed .otf
       (CFF) originals, made with scripts/otf2ttf.py. Chromium's PDF backend
       embeds CFF web fonts as unhinted Type3 fonts, which print noticeably
       heavier/rougher on some printers and viewers (Foord QC card 232);
       TrueType outlines are embedded as ordinary CIDFontType2 subsets. */
    @font-face {
        font-family: 'Avenir Next';
        src: url('{{ asset('fonts/AvenirNextLTPro/AvenirNextLTPro-Light.ttf') }}') format('truetype');
        font-weight: 300;
        font-style: normal;
        font-display: swap;
    }
    @font-face {
        font-family: 'Avenir Next';
        src: url('{{ asset('fonts/AvenirNextLTPro/AvenirNextLTPro-LightIt.ttf') }}') format('truetype');
        font-weight: 300;
        font-style: italic;
        font-display: swap;
    }
    @font-face {
        font-family: 'Avenir Next';
        src: url('{{ asset('fonts/AvenirNextLTPro/AvenirNextLTPro-Regular.ttf') }}') format('truetype');
        font-weight: 400;
        font-style: normal;
        font-display: swap;
    }
    @font-face {
        font-family: 'Avenir Next';
        src: url('{{ asset('fonts/AvenirNextLTPro/AvenirNextLTPro-It.ttf') }}') format('truetype');
        font-weight: 400;
        font-style: italic;
        font-display: swap;
    }
    @font-face {
        font-family: 'Avenir Next';
        src: url('{{ asset('fonts/AvenirNextLTPro/AvenirNextLTPro-Medium.ttf') }}') format('truetype');
        font-weight: 500;
        font-style: normal;
        font-display: swap;
    }
    @font-face {
        font-family: 'Avenir Next';
        src: url('{{ asset('fonts/AvenirNextLTPro/AvenirNextLTPro-MediumIt.ttf') }}') format('truetype');
        font-weight: 500;
        font-style: italic;
        font-display: swap;
    }
    @font-face {
        font-family: 'Avenir Next';
        src: url('{{ asset('fonts/AvenirNextLTPro/AvenirNextLTPro-Demi.ttf') }}') format('truetype');
        font-weight: 600;
        font-style: normal;
        font-display: swap;
    }
    @font-face {
        font-family: 'Avenir Next';
        src: url('{{ asset('fonts/AvenirNextLTPro/AvenirNextLTPro-Bold.ttf') }}') format('truetype');
        font-weight: 700;
        font-style: normal;
        font-display: swap;
    }
</style>
