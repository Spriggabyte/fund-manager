# Iteration Log — Foord Equity Fund Class A

## Iteration 1 — 2026-03-23
### Changes:
- Created pdf-equity.blade.php from scratch based on reference PDF
- Pure CSS layout (no Tailwind CDN), optimized for Puppeteer rendering
- Implemented all sections: header, sidebar, sector allocation, asset allocation, charts, performance table, footnotes, page 2 fees/footer

### Web view differences found:
- N/A (focused on PDF template first)

### PDF output differences found:
- Header missing grey sidebar panel → will fix
- Monthly chart Y-axis auto-scaled instead of -10% to 10% → will fix
- No Low Carbon badge → data not available, documented in conversion-notes.md
- Performance table missing separator between Benchmark and Fund highest rows → will fix

### Status: CONTINUING

## Iteration 2 — 2026-03-23
### Changes:
- Added header grey sidebar panel (header-row with header-sidebar-bg)
- Added separator row between Benchmark and Fund highest in performance table
- Added end-of-line R-value annotations to portfolio chart (endLabelPlugin)
- Added Y-axis "Cash Value (R'000)" label to portfolio chart
- Fixed monthly chart Y-axis to -10% to 10% with 5% step size
- Reduced monthly chart X-axis tick limit for readability

### Status: CONTINUING

## Iteration 3 — 2026-03-23
### Changes:
- Updated show-equity.blade.php: removed donut chart, made asset allocation table-only
- Updated asset-legend-table CSS to use dark navy header (matching reference PDF)
- Removed unused donut chart JavaScript from web view
- Final comparison: both pages match reference closely

### Web view differences fixed:
- Asset allocation had donut chart + table → now table-only matching reference
- Asset table header was plain text → now dark navy bg with white text

### PDF output differences remaining (documented limitations):
- Low Carbon badge: not in fund data (conversion-notes.md)
- Font: Lato instead of Avenir Next (conversion-notes.md)
- Chart rendering: Chart.js vs original tool (conversion-notes.md)

### Status: RESOLVED

## Iteration 4 — 2026-03-23
### Changes:
- Fixed description font size: 6.5pt → 8pt Merriweather, line-height 8.5pt → 10pt (now wraps to 4 lines matching reference)
- Fixed portfolio chart legend: circles → line dashes (boxWidth: 15, boxHeight: 1, usePointStyle: false)
- Fixed monthly chart X-axis: maxTicksLimit 5 → 6 (now shows Sep 02, Sep 06, Sep 10, Sep 14, Sep 18, Sep 22)
- Fixed sector label wrapping: added white-space: nowrap, widened label to 30mm (prevents "Capital goods/construction" from wrapping)

### Web view differences found:
- None (web view uses separate template, unaffected by PDF changes)

### PDF output differences found:
- Description font size too small → fixed (8pt now matches reference's 4-line layout)
- Portfolio chart legend used circles instead of line dashes → fixed
- Monthly chart X-axis labels misaligned (auto-calculated) → fixed to September-aligned every 4 years
- Sector label "Capital goods/construction" wrapped to 2 lines → fixed with nowrap + wider label

### Remaining issues: 0 (new), 3 (accepted limitations from previous iterations)
### Status: RESOLVED
