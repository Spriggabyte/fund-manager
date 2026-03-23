# Conversion Notes — Foord Equity Fund Class A

## Architecture
- `show-equity.blade.php` — web view template (editable, with Alpine.js edit mode)
- `pdf-equity.blade.php` — dedicated PDF template (used by Puppeteer for PDF generation)
- Both templates render the same data but have independent styles
- PDF route: `/internal/funds/{id}/pdf-view` bypasses auth for Puppeteer

## PDF Template
The PDF template (`pdf-equity.blade.php`) was already well-designed with:
- CSS variables for the Foord colour palette
- mm-based measurements matching A4 grid specs
- Proper side-by-side layouts at 210mm width
- Chart.js integration with appropriate sizing

## Web View Changes (show-equity.blade.php)
Iteration 1-4 changes:
- Replaced Tailwind responsive classes (lg:grid-cols-2) with explicit flex layouts
  that work at 794px (A4 width) viewport
- Added light grey header strip with date badge and Foord logo
- Added naartjie stripe between header and navy banner
- Reduced all font sizes to match PDF spec
- Added pdf-page class for A4 sizing (794x1123px, overflow hidden)
- Fixed sector/asset, top10/chart side-by-side layouts
- Added Chart.js endValueLabels plugin for R annotations
- Added Y-axis title to portfolio chart
- Differentiated "FOORD EQUITY FUND" vs "– CLASS A" font sizes
- Fixed monthly chart Y-axis scale to match reference (±10%)
- Centered "IMPORTANT INFORMATION FOR INVESTORS" header
- Added Foord logo to page 2 footer

## Known Limitations
- Sidebar item order is data-driven and differs from reference PDF
  (the data JSON determines display order, not the template)
- "Low Carbon" Morningstar badge not present in current fund data
- Font: Avenir Next (used in original PDF) is not available as web font;
  Lato is used as the closest free alternative
- Chart rendering differs slightly between Chart.js and the original
  (likely generated with a different tool)
