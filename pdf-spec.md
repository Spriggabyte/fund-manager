# Foord Equity Fund – Class A — PDF Spec Sheet

## Page Dimensions
- A4: 794px wide at 96dpi (210mm)
- Page height: 1123px (297mm)
- Puppeteer viewport: 794×1123 at deviceScaleFactor 2
- PDF margins: 0 (template handles all spacing internally)

## PDF Generation
- Engine: Puppeteer (puppeteer-core) via PuppeteerPdfService
- Chrome path: /Applications/Google Chrome.app/Contents/MacOS/Google Chrome
- Internal URL: /internal/funds/{id}/pdf-view
- Template: funds/pdf-equity.blade.php (falls back to pdf.blade.php)
- Chart.js loaded from CDN, 2s + 1.5s wait for chart rendering
- printBackground: true, displayHeaderFooter: false, preferCSSPageSize: true

## Colour Palette
| Name | Hex |
|---|---|
| Naartjie | #d25347 |
| 75% Naartjie | #dd7e75 |
| 50% Naartjie | #e9a9a3 |
| 20% Naartjie | #f6dcd9 |
| Dark Navy | #29363d |
| 70% Dark Navy | #697277 |
| 30% Dark Navy | #bfc3c5 |
| 15% Dark Navy | #dde1e2 |
| 10% Dark Navy | #e9ebec |
| Off-black | #313131 |
| Dark Grey | #535353 |
| Medium Grey | #9a9a9a |
| Light Grey | #cccccc |
| Very Light Grey | #f4f4f4 |
| Light Blue | #7a9cb4 |
| Mushroom | #e2cea4 |
| 50% Mushroom | #f1e7d2 |

## Typography
| Element | Font | Size | Weight | Line-Height | Kerning | Colour |
|---|---|---|---|---|---|---|
| Date badge | Lato | 9pt | 500 | auto | 0.03em | White on Naartjie |
| Fund name | Lato | 18pt | 500 | 1.1 | 0.05em | White on Dark Navy |
| Class suffix | Lato | 14pt | 400 | 1.1 | 0.05em | White on Dark Navy |
| Description | Merriweather | 8pt | 400 | 10pt | 0.02em | White (95% opacity) on Dark Navy |
| Sidebar heading | Lato | 5.5pt | 600 | 6.5pt | 0.02em | Dark Navy |
| Sidebar text | Lato | 6.5pt | 400 | 7.5pt | 0.01em | Off-black |
| Section headings | Lato | 7pt | 600 | 8pt | 0.02em | Off-black |
| Section subtitle | Lato | 5.5pt | 400 | 6pt | 0.01em | Dark Grey |
| Table heading | Lato | 6pt | 500 | 6.5pt | 0 | White on Dark Navy |
| Table body | Lato | 6.5pt | 400 | 8pt | 0 | Off-black |
| Perf table head | Lato | 5.5pt | 500 | 6pt | 0 | White on Dark Navy |
| Perf table body | Lato | 6.5pt | 400 | 8pt | 0 | Off-black |
| Chart explanation | Lato | 5.5pt | 400 | 7pt | 0 | Dark Grey |
| Footnotes | Lato | 5pt | 400 | 6pt | 0.01em | Dark Grey |
| Page 2 body | Lato | 6pt | 400 | 7pt | 0 | Dark Grey |
| Important info text | Lato | 5pt | 300 | 6pt | 0 | Off-black |
| Footer info | Merriweather | 7pt | 400 | 9pt | 0.02em | Naartjie |
| Footer contact | Lato | 7pt | 500 | 9pt | 0.03em | Naartjie |

## Layout Measurements
- Page padding: 8mm top, 12mm left/right, 8mm bottom
- Sidebar width: 46mm
- Sidebar padding: 4mm
- Main content padding: 4mm
- Naartjie stripe: 0.8mm height (between header and title)
- Title banner padding: 4mm 5mm
- Title banner extends full width (negative margin -12mm each side)
- Table accent bar: 0.8mm naartjie (left side)
- Table left margin: 0.8mm (for accent bar)
- Bottom margin: ~42px (11mm)

## Table Styling
- Header: Dark Navy bg, white text, 1.5mm padding, border-right 0.5pt solid white
- Body rows: Alternating Very Light Grey (#f4f4f4) and white
- Border: 0.3pt solid #e5e5e5 bottom border on cells
- Total row: Naartjie bg, white text, font-weight 600
- Highlight row: 20% Naartjie bg, Naartjie text on first col
- First column text-align: left; others: center

## Sector Allocation Bars
- Label width: ~28mm
- Bar height: 3mm
- Bar color: Naartjie
- Value text inside bar: white, 6pt, bold
- Change column: ~10mm, right-aligned
- Arrow indicators: ▲ (naartjie) ▼ (dark navy), 5pt
- Row gap: 0.3mm

## Asset Allocation Table (Page 1, right of sector bars)
- NO donut chart — just a table
- Headers: dark navy background, white text
- Column headers: empty first col, "31 JAN 2026", "31 DEC 2025"
- Rows: JSE equity securities, – Resources, – Financials, – Industrials, JSE property, Commodities, Money market
- TOTAL row in naartjie red
- Indent rows (– prefixed) in dark-navy-70 color, slightly smaller font

## Page 1 Content Order
1. Header (date badge + logo)
2. Naartjie stripe (0.8mm)
3. Fund banner (name + description) — full width
4. Content wrapper: Sidebar (left) + Content (right)
   - Row 1 (side by side): EQUITY SECTOR ALLOCATION % | ASSET ALLOCATION %
   - Row 2 (side by side): TOP 10 INVESTMENTS | PORTFOLIO PERFORMANCE VS BENCHMARK (line chart)
   - Full width: MONTHLY PORTFOLIO PERFORMANCE VS BENCHMARK (bar chart)
   - Legend: orange square = benchmark negative, navy square = benchmark positive
   - Chart explanation paragraph
   - Full width: PORTFOLIO PERFORMANCE % table
   - Footnotes (numbered, small text)

## Page 2 Content Order
1. Left sidebar: IMPORTANT INFORMATION FOR INVESTORS (dark navy header box + paragraphs)
2. Right content:
   - FEE RATES (simple 2-col table, no header row)
   - Fee description paragraph
   - TOTAL INVESTMENT CHARGE % table (header + rows + total in naartjie)
   - TER description paragraph
   - PERFORMANCE FEES heading + body paragraphs
   - PERFORMANCE FEE EXAMPLES % table (with columns A, B, C, D + total row)
   - Footnote ("* Minimum fees apply")
   - Horizontal naartjie divider
   - Footer: info text (Merriweather), free-of-charge text, contact details, small Foord logo
