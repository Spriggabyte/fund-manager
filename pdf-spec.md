# Foord Flexible Fund of Funds – Class A — PDF Spec Sheet

## Page Dimensions
- A4: 794px wide at 96dpi (210mm)
- Page min-height: 1123px (297mm)

## Colour Palette (from design-reference)
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

## Typography (from design-reference)
| Element | Font | Size | Weight | Line-Height | Kerning | Colour |
|---|---|---|---|---|---|---|
| Date badge | Avenir Next | 10pt | Medium (500) | auto | 0.03em | White on Naartjie |
| Fund name | Avenir Next | 23pt | Medium (500) | 1.1 | 0.05em | White on Dark Navy |
| Description | Merriweather | 7pt | Regular (400) | 9.5pt | 0.02em | White (90% opacity) on Dark Navy |
| Section headings | Avenir Next | 7.5pt | Medium (500) | 8pt | 0.03em | Off-black |
| Section subtitle | Avenir Next | 6pt | Regular (400) | 7pt | 0 | Dark Grey |
| Sidebar label | Avenir Next | 6pt | Bold (700) | 7.5pt | 0.03em | Dark Navy |
| Sidebar value | Avenir Next | 7pt | Regular (400) | 7.5pt | 0.02em | Off-black |
| Table heading | Avenir Next | 7pt | Medium (500) | 7.5pt | 0.02em | White on Dark Navy |
| Table body | Avenir Next | 7.5pt | Regular (400) | 10pt | 0 | Off-black |
| Perf table head | Avenir Next | 6.5pt | Medium (500) | 7pt | 0 | White on Dark Navy |
| Perf table body | Avenir Next | 7pt | Regular (400) | 9pt | 0 | Off-black |
| Chart explanation | Avenir Next | 6pt | Regular (400) | 7pt | 0 | Dark Grey |
| Footnotes | Lato | 5.5pt | Regular (400) | 6.5pt | 0.01em | Dark Grey |
| Page 2 body | Avenir Next | 7pt | Regular (400) | 9pt | 0.01em | Dark Grey |
| Important info text | Lato | 5.5pt | Light (300) | 6.5pt | 0 | Off-black |
| Footer info | Merriweather | 7.5pt | Regular (400) | 10pt | 0.02em | Naartjie |
| Footer contact | Avenir Next | 7.5pt | Medium (500) | 10pt | 0.03em | Naartjie |

## Layout Measurements (from grid spec)
- Header row height: 78px
- Header grey panel width: 218px (extends from sidebar)
- Sidebar width: 174px (46mm)
- Content area padding: 6px 14px 8px 12px
- Naartjie stripe height: 3px
- Banner padding: 8px 16px 6px 16px
- Table left accent bar: 3px naartjie
- Bottom margin: 11mm (~42px)
- Top margin header: 10mm (~38px) — handled by header-row height
- Space between sections: ~5.5mm (~21px) as column gap

## Table Styling
- Header: Dark Navy bg, white text, 4px 6px padding, border-right 1px solid rgba(255,255,255,0.4)
- Body rows: Alternating Very Light Grey (#f4f4f4) and white
- Border: 1px solid #e5e5e5 bottom border on cells
- Total row: Naartjie bg, white text
- Highlight row: 20% Naartjie bg, Naartjie text on first col
- Vertical table strokes: White, 1pt (border-right on th)

## Page 1 Content Order
1. Header (date badge + logo)
2. Fund banner (name + description)
3. Naartjie stripe
4. Sidebar (left) + Content (right)
   - ASSET ALLOCATION % (SA, FOREIGN, TOTAL, CHANGE columns + TOTAL row in naartjie)
   - TOP 10 INVESTMENTS (SECURITY, ASSET CLASS, MARKET, % OF FUND)
   - Two charts side by side: Strategy vs Reg 28 | Performance vs Benchmark
   - Chart explanation paragraph
   - PORTFOLIO PERFORMANCE % table (CASH VALUE, SINCE INCEPTION, 15 YRS, 10 YRS, 7 YRS, 5 YRS, 3 YRS, 1 YR, THIS MONTH)
   - Footnotes

## Page 2 Content Order
1. Left sidebar: IMPORTANT INFORMATION FOR INVESTORS
2. Right content:
   - FEE RATES (simple 2-col table, no header row)
   - Fee description paragraph
   - TOTAL INVESTMENT CHARGE % table (header + rows + total)
   - TER description paragraph
   - PERFORMANCE FEES heading + body paragraphs
   - PERFORMANCE FEE EXAMPLES % table
   - Footnote
   - Footer (naartjie divider, info text, contact details, small logo)
