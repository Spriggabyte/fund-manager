<style>
    /* Licensed Avenir Next LT Pro (Monotype), fonts/AvenirNextLTPro/ —
       replaces reliance on macOS's bundled "Avenir Next" system font so
       PDF/print rendering is correct on machines that don't have it
       installed (and so we're actually using the licensed copy). Weights
       match every 'Avenir Next' font-weight used across the factsheet
       templates (300/400/500/600/700, plus italic for 300/400/500). */
    @font-face {
        font-family: 'Avenir Next';
        src: url('{{ asset('fonts/AvenirNextLTPro/AvenirNextLTPro-Light.otf') }}') format('opentype');
        font-weight: 300;
        font-style: normal;
        font-display: swap;
    }
    @font-face {
        font-family: 'Avenir Next';
        src: url('{{ asset('fonts/AvenirNextLTPro/AvenirNextLTPro-LightIt.otf') }}') format('opentype');
        font-weight: 300;
        font-style: italic;
        font-display: swap;
    }
    @font-face {
        font-family: 'Avenir Next';
        src: url('{{ asset('fonts/AvenirNextLTPro/AvenirNextLTPro-Regular.otf') }}') format('opentype');
        font-weight: 400;
        font-style: normal;
        font-display: swap;
    }
    @font-face {
        font-family: 'Avenir Next';
        src: url('{{ asset('fonts/AvenirNextLTPro/AvenirNextLTPro-It.otf') }}') format('opentype');
        font-weight: 400;
        font-style: italic;
        font-display: swap;
    }
    @font-face {
        font-family: 'Avenir Next';
        src: url('{{ asset('fonts/AvenirNextLTPro/AvenirNextLTPro-Medium.otf') }}') format('opentype');
        font-weight: 500;
        font-style: normal;
        font-display: swap;
    }
    @font-face {
        font-family: 'Avenir Next';
        src: url('{{ asset('fonts/AvenirNextLTPro/AvenirNextLTPro-MediumIt.otf') }}') format('opentype');
        font-weight: 500;
        font-style: italic;
        font-display: swap;
    }
    @font-face {
        font-family: 'Avenir Next';
        src: url('{{ asset('fonts/AvenirNextLTPro/AvenirNextLTPro-Demi.otf') }}') format('opentype');
        font-weight: 600;
        font-style: normal;
        font-display: swap;
    }
    @font-face {
        font-family: 'Avenir Next';
        src: url('{{ asset('fonts/AvenirNextLTPro/AvenirNextLTPro-Bold.otf') }}') format('opentype');
        font-weight: 700;
        font-style: normal;
        font-display: swap;
    }
</style>
