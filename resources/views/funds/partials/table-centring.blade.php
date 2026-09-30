{{--
    Trello 429 (30 Sept 2026): every heading, value and total centred in its
    table row. Included by partials/global-fixes on the local fact sheets
    and by the two overview sheets; the international sheets have their own
    equivalent in partials/global-intl-fixes (card 430).

    1. Table text uses 'Avenir Next Cap', the twin of Avenir Next declared in
       partials/avenir-fonts with ascent 80% / descent 0% (same glyphs; only
       the line-box metrics differ), so a line box centres the cap height.
    2. Chrome still paints a cell's background and its text baseline at
       whole-pixel positions, so the script below corrects what that
       rounding leaves over, per cell, for whatever data the sheet carries.
--}}
<style>
    .page table th,
    .page table td,
    .page table th *,
    .page table td * {
        font-family: 'Avenir Next Cap', 'Avenir Next', 'Lato', sans-serif !important;
    }
</style>
<script>
    // Every heading, value and total centred in its row. Chrome
    // paints a cell's background and its text baseline at whole-pixel
    // positions (layout position rounded), so depending on where a row
    // falls on the pixel grid its text lands up to a pixel off centre —
    // and that changes month to month with the data. For each cell this
    // predicts the painted position of the text (cap top of the first line
    // to baseline of the last) and of the cell, and moves whole pixels of
    // padding from bottom to top (or back) until the text is centred within
    // half a pixel. Row heights never change. Positions are measured from
    // the top of the cell's own page: Chrome paints each PDF page from its
    // own origin, so that is the grid the rounding happens on. A performance
    // table's header row moves as one so single-line headers ("YTD") stay
    // on the bottom line with the two-line ones.
    function centreTableText() {
        var CAP = 0.708;
        function textNodes(cell) {
            var out = [], w = document.createTreeWalker(cell, NodeFilter.SHOW_TEXT, {
                acceptNode: function (n) {
                    // Letters/figures only: glyph arrows (▲▼) come from a
                    // fallback font whose line box is not Avenir's.
                    return /[0-9A-Za-z]/.test(n.nodeValue) && !n.parentElement.closest('sup') ? NodeFilter.FILTER_ACCEPT : NodeFilter.FILTER_REJECT;
                }
            });
            while (w.nextNode()) out.push(w.currentNode);
            return out;
        }
        // Table text is set in 'Avenir Next Cap' (descent 0), so the bottom
        // of a text line's box is its baseline — also inside the inline-flex
        // wrappers that pair a change triangle with its number.
        function baselineAt(node, last) {
            var range = document.createRange();
            range.selectNodeContents(node);
            var rects = range.getClientRects();
            return rects.length ? rects[last ? rects.length - 1 : 0].bottom : null;
        }
        // Whole pixels the cell's text must move down (negative = up).
        function offset(cell) {
            var nodes = textNodes(cell);
            if (!nodes.length || cell.querySelector('div, p, table, ul, canvas')) return null;
            var shift = -cell.closest('.page').getBoundingClientRect().top;
            var r = cell.getBoundingClientRect();
            var first = nodes[0], last = nodes[nodes.length - 1];
            var b0 = baselineAt(first, false), b1 = baselineAt(last, true);
            if (b0 === null || b1 === null) return null;
            var capTop = Math.round(b0 + shift) - CAP * parseFloat(getComputedStyle(first.parentElement).fontSize);
            var base = Math.round(b1 + shift);
            var cellMid = (Math.round(r.top + shift) + Math.round(r.bottom + shift)) / 2;
            return Math.round(cellMid - (capTop + base) / 2);
        }
        function move(cell, d) {
            if (!d) return;
            var cs = getComputedStyle(cell), pt = parseFloat(cs.paddingTop), pb = parseFloat(cs.paddingBottom);
            d = Math.max(-pt, Math.min(pb, d));
            cell.style.setProperty('padding-top', (pt + d) + 'px', 'important');
            cell.style.setProperty('padding-bottom', (pb - d) + 'px', 'important');
        }
        document.querySelectorAll('.page table tr').forEach(function (tr) {
            var cells = Array.prototype.slice.call(tr.children).filter(function (c) { return /^T[DH]$/.test(c.tagName); });
            var perfHead = tr.closest('.performance-table, .perf-table') && cells.length && cells.every(function (c) { return c.tagName === 'TH'; });
            if (perfHead) {
                // Centre the tallest (two-line) header; the rest follow it.
                var tallest = null, height = 0;
                cells.forEach(function (c) {
                    if (!textNodes(c).length) return;
                    var range = document.createRange();
                    range.selectNodeContents(c);
                    var h = range.getBoundingClientRect().height;
                    if (h > height) { height = h; tallest = c; }
                });
                var d = tallest ? offset(tallest) : null;
                if (d) cells.forEach(function (c) { move(c, d); });
                return;
            }
            cells.forEach(function (c) { move(c, offset(c)); });
        });
    }

    // After the page's own DOMContentLoaded work (e.g. global-fixes turning
    // Unicode superscripts into <sup>) and once the fonts are in. Not
    // requestAnimationFrame: background tabs never run it.
    document.addEventListener('DOMContentLoaded', function () {
        (document.fonts ? document.fonts.ready : Promise.resolve()).then(centreTableText);
    });
</script>
