{{--
    Web preview only: centre the A4 sheet (and its edit toolbar) in the
    browser window. Templates pin `html, body` to 210mm with zero margin so
    the print layout starts at the page origin; on screen that left-aligns
    everything. Scoped to `screen` so the Puppeteer PDF export (print media)
    is untouched. Included last in every fact-sheet <head> so it wins on
    source order.
--}}
<style>
    @media screen {
        html { width: auto !important; }
        body {
            width: 210mm !important;
            margin-left: auto !important;
            margin-right: auto !important;
        }
    }
</style>
