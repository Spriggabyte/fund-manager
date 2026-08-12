# Fund Onboarding — Repeatable Setup Process

How to configure a fund fact sheet from the standard Foord Excel exports, as done for
fund 11 (811 Class A — Equity Fund). One of ~47 fact sheets follows this same process.

## 1. Source files

Each fund folder under `Funds/<code> Class <X> - <Name>/` contains:

| Folder | Contents | Role |
|---|---|---|
| `Data/` | `*_FACTSHEET.xlsx`, `*_PRICE_GRAPH.xlsx`, plus fund-type graphs (`*_ALSI_GRAPH.xlsx` for equity, `*_SA_INFLATION_GRAPH.xlsx` for balanced, …) | Imported data |
| `Design/` | Reference PDF | Ground truth for layout, labels, static text, number formats |
| `Publisher/` | Original `.pub` | Fallback source for template values |

## 2. Create / identify the fund record

The fund's `template` column drives rendering: `show` (balanced, default), `show-equity`,
`show-flexible`, `show-international`. PDF: `show-equity` → `pdf-equity.blade.php`,
everything else → the signed-off `pdf.blade.php` (frozen — never edit).

## 3. Seed static text (once per fund)

Fields not present in the Excel exports come from the fund's reference PDF, entered via
the edit form or tinker: `description`, sidebar prose (`domicile`, `management_company`,
`fund_managers`, `minimums`, `income_*`, `portfolio_orientation`,
`significant_restrictions`, `risk_of_loss`, `time_horizon`, `equity_indicator_description`),
`benchmark`, `category`, footer fields, `important_info_paragraphs`, `fees`
(feeRates/performanceFees/performanceFeeExamples text), performance-table
`headers`/`footnotes`, and for equity funds `chart_description` and an initial
`sector_allocation` (sector directions can't be derived on the first import — see §5).

Use em dashes (—) in `name`/`category` to match the designs.

## 4. Import the Excel exports

```bash
php artisan fund:import {fundId} "Funds/<fund folder>/Data" [--dry-run]
```

- Files are routed by filename via `FundImportManager`
  (`app/Services/FundImport/`): `*FACTSHEET*`, `*PRICE_GRAPH*`, `*INFLATION_GRAPH*`,
  `*ALSI_GRAPH*`, `*COST_REG28_GRAPH*` (flexible fund's Reg 28 comparison →
  `chart_data['strategyData']`). Unknown exports are reported and skipped — add a new
  importer class + registry line in `FundImportManager` to support them.
- A revision snapshot is created before every import (restore via the revisions UI).
- The factsheet importer auto-detects the fund variant from its keys
  (`AA_DOM_*` balanced / `AAOT_*` international / `AA_SHARE_*` equity / `ESAOT_*` sectors /
  `GEO_EXP_*` geography), so one importer serves all funds.
- Re-running monthly is idempotent: scalars, tables, and chart series are replaced;
  static text is preserved (the TER footnote and the equity chart-description
  percentage are refreshed in place).
- The same files can be uploaded through the fund edit page (Import Excel Data panel).
  Note that panel uses the older `ExcelImportService` and has no `COST_REG28_GRAPH` input.
- Once the fund has a `fund_code`, the monthly exports arrive automatically via the SFTP
  feed and can be imported from the edit page's data-feed card instead of the CLI —
  see [`docs/sftp-data-feed.md`](docs/sftp-data-feed.md).

## 5. Equity-fund specifics

- `chart_data['monthlyData']` (monthly relative-return bars) comes from the ALSI graph.
- `sector_allocation` holds the sector bars. The sheet gives change **magnitude but no
  sign**, so the importer derives each arrow direction by comparing against the
  previously stored value for that sector. Ties (value unchanged after rounding) keep the
  prior direction — check these against the design and correct via inline edit
  (`mainContent.sectorAllocation.sectors.N.direction`) if needed.
- Distributions render without the colon separator (`30/09/2025 249.20 cents`); other
  designs use `date: amount`. The importer switches on `template === 'show-equity'`.

## 5b. Flexible-fund specifics (fund 13, 817 Class A)

- `template = 'show-flexible'` renders `show-flexible.blade.php` and exports via
  `pdf-flexible.blade.php` (both mirror the signed-off balanced templates; the
  inflation chart is replaced by a second cash-value spline chart fed from
  `chart_data['strategyData']` — Fund vs the Reg 28-compliant Foord Balanced Fund).
- The fund is unconstrained: the factsheet importer skips the bracketed mandate
  limits and emits plain `SA`/`FOREIGN` headers, and it keeps the zero
  `— Foord global charges` TIC row that other funds omit (the published 817 fact
  sheet lists it, and the TIC CSS whitens `nth-child(6)` — dropping the row makes
  the red total row invisible).
- The factsheet export names the FTSE/JSE All Share row `Comparator 2`; both
  flexible templates rename it and add the reference's footnote superscripts
  (Fund³, Benchmark⁴, FTSE/JSE All Share⁵, Fund highest/lowest³·⁶) plus the blank
  spacer row at display time — the stored rows keep the raw import names.
- The paragraph under the charts comes from `chart_data['explanation']`
  (static text, preserved across imports).

## 5c. International-fund specifics (fund 14, 875 Class R)

- `template = 'show-international'` — the page template IS the print layout
  (A4 pages, `@media print`, `.no-print` chrome), so `internalPdfView` maps it
  to itself; there is no separate pdf-international template. The chart is
  Chart.js (canvas), which `PuppeteerPdfService` also waits for.
- The template shares the signed-off balanced page geometry (mm/pt values
  ported from `pdf.blade.php`): grey band 4→60mm with the 4mm white strip,
  26.5mm header, 45.9×10.9mm date badge at (9.05mm, 10mm) — navy here, with
  the title banner naartjie (the balanced colours inverted, per the 875
  reference) — 34mm banner, 60mm sidebar, and the signed-off table styling
  (1.1pt white separators, right-aligned values, row-grey fades). Alloc/sector
  bars scale relative to the block's largest value. Reference-only details:
  heading suffixes stay mixed case, empty performance cells render white, the
  cost-ratio table is three equal centred columns, page-2 links render gold
  (#c09000, underlined — the `linkify` formatter/helper pair), and the Lipper
  logo is `public/images/lipper-award.png` (extracted from the published PDF).
  Editable fields that need styled display (fund name suffix, heading
  suffixes, links) pass a formatter name to `editableField` so Alpine re-renders
  them styled after edits.
- Sidebar fields live in dedicated columns added by the
  `add_international_sidebar_fields` migration: `depository`,
  `investment_manager`, `sub_investment_manager`, `type_of_shares`,
  `fees_summary`, `lipper_award` (json), plus `page2_content` (json) for the
  page-2 narrative sections (sharePricing / moreAboutFund / lipperAward).
  Existing columns are re-labelled: category → MORNINGSTAR CATEGORY,
  minimums → INITIAL INVESTMENT AMOUNT, portfolio_size → TOTAL FUND SIZE,
  unit_price → MONTH END SHARE PRICE, number_of_units → NUMBER OF SHARES.
- The factsheet importer writes geographic exposure (reference naming/order:
  North America, Europe, Pacific, Emerging Asia, Africa & Middle East,
  EM Latin America) and `equitySectors` into the asset_allocation JSON; the
  template derives its geo table and sector bars from there. Portfolio size
  gets a `$` prefix for this template (others get `R`).
- The price-graph importer detects the international export by its column
  headers (US CPI / MSCI / WGBI) and emits `chart_data['performanceData']`
  with semantic keys (fund, usInflation, worldEquities, worldBonds) for the
  four-series log-scale performance chart.
- Performance rows display renamed with superscripts at render time (raw
  import names in data): Fund³, Benchmark→Peer group⁴, Comparator 2→US
  inflation⁵, 3→World equities⁶, 4→World bonds⁷, 5→Fund in euros³, 6→Fund in
  sterling³, highest/lowest³·⁸, with spacers before the euro/sterling rows and
  the highest/lowest rows. Footnotes render on page 2 under NOTES.

```bash
# Direct render (no queue):
php artisan tinker --execute='$f=App\Models\Fund::find(ID); echo app(App\Services\PuppeteerPdfService::class)->generatePdf($f);'

# Rasterize both PDFs and compare page by page:
pdftoppm -png -r 150 <generated>.pdf /tmp/gen
pdftoppm -png -r 150 "Funds/<fund folder>/Design/<reference>.pdf" /tmp/ref
pdftotext -layout <generated>.pdf - | diff - <(pdftotext -layout <reference>.pdf -)
```

Remember the reference PDF is usually one month older than the current exports — value
differences from the month gap are expected; layout/label/format differences are defects.

## 7. Verify

```bash
php artisan test          # includes importer + sector-allocation coverage
vendor/bin/phpstan analyse
```
