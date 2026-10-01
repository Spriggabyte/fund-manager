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
        function lineRect(node, last) {
            var range = document.createRange();
            range.selectNodeContents(node);
            var rects = range.getClientRects();
            return rects.length ? rects[last ? rects.length - 1 : 0] : null;
        }
        // Opt-in painted-pixel model (`--centre-snap: 1`, card 438 — the
        // equity sheet): the default rounding below can miss by a pixel.
        // Chrome paints a line's baseline at round(line top) +
        // floor(half-leading) + round(ascent), not at the rounded layout
        // baseline; the text box (ascent only, descent 0) sits floor(half-
        // leading) below the line top.
        function paintedBaseline(rect, cell, shift) {
            var lh = parseFloat(getComputedStyle(cell).lineHeight) || rect.height;
            var hl = Math.floor((lh - rect.height) / 2);
            return Math.round(rect.top + shift - hl) + hl + Math.round(rect.height);
        }
        // ...and a 1px collapsed rule between two rows straddles their
        // boundary, so its pixel row lands inside whichever cell's rounded
        // box the rounding gives it to: that row is not part of the band
        // the text has to centre in.
        function visibleBand(cell, r, shift) {
            var top = Math.round(r.top + shift), bot = Math.round(r.bottom + shift);
            var wb = Math.round(parseFloat(getComputedStyle(cell).borderBottomWidth)) || 0;
            var tr = cell.parentElement;
            var prev = tr.previousElementSibling || (tr.parentElement.previousElementSibling || {}).lastElementChild;
            var wt = Math.round(parseFloat(getComputedStyle(cell).borderTopWidth)) || 0;
            if (prev && prev.firstElementChild) wt = Math.max(wt, Math.round(parseFloat(getComputedStyle(prev.firstElementChild).borderBottomWidth)) || 0);
            if (wb && Math.round(r.bottom + shift - wb / 2) < bot) bot -= wb;
            if (wt && Math.round(r.top + shift - wt / 2) + wt > top) top += wt;
            return (top + bot) / 2;
        }
        // Whole pixels the cell's text must move down (negative = up).
        function offset(cell) {
            var nodes = textNodes(cell);
            if (!nodes.length || cell.querySelector('div, p, table, ul, canvas')) return null;
            var shift = -cell.closest('.page').getBoundingClientRect().top;
            var r = cell.getBoundingClientRect();
            var first = nodes[0], last = nodes[nodes.length - 1];
            var r0 = lineRect(first, false), r1 = lineRect(last, true);
            if (r0 === null || r1 === null) return null;
            var cap = CAP * parseFloat(getComputedStyle(first.parentElement).fontSize);
            if (getComputedStyle(cell).getPropertyValue('--centre-snap').trim() === '1') {
                var mid = visibleBand(cell, r, shift);
                return Math.round(mid - (paintedBaseline(r0, first.parentElement, shift) - cap + paintedBaseline(r1, last.parentElement, shift)) / 2);
            }
            var capTop = Math.round(r0.bottom + shift) - cap;
            var base = Math.round(r1.bottom + shift);
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
