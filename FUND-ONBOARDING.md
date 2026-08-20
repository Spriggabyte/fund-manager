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
`show-flexible`, `show-conservative`, `show-international`, `show-feeder`. PDF:
`show-equity` → `pdf-equity.blade.php`, `show-flexible` → `pdf-flexible.blade.php`,
`show-conservative` → `pdf-conservative.blade.php`, the international/feeder page
templates map to themselves, everything else → the signed-off `pdf.blade.php`
(frozen — never edit).

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
  `chart_data['strategyData']`), `*ROLLING_1_YEAR_GRAPH*` (conservative fund's
  rolling one-year return bars → `chart_data['rollingReturnData']`). Unknown exports
  are reported and skipped — add a new importer class + registry line in
  `FundImportManager` to support them.
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
- Class B2 (fund 30) via `fund:add-class 13 B2 --import=2026-07` — identical layout to
  Class A; only three static values differ (from the live `Funds/Publisher/817 B2.pub`):
  Standard annual fee **0.6% plus VAT**, Minimum annual fee **0.1% plus VAT**, and the
  PERFORMANCE FEE EXAMPLES total row **0.8 / 0.4 / 0.6 / 0.1\***. There is no B2
  COST_REG28 export — `strategyData` is carried over from Class A by the clone.
- **.pub caution**: the B2 `.pub` was cloned from 818 and still contains dead
  conservative-fund text boxes (globalFunds fee rows, five-column examples,
  "…FOR FOORD CONSERVATIVE FUND (CLASS A)" title) in earlier byte regions. The live
  page text is the *last* copy in the file (~1.64 MB offset in `817 B2.pub`) — always
  extract that region when reading statics from a cloned `.pub`.

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

## 5d. Conservative-fund specifics (funds 21/22/23 — 818 Classes A/B2/B3)

- `template = 'show-conservative'` renders `show-conservative.blade.php` and exports
  via `pdf-conservative.blade.php` (both cloned from the flexible pair, which shares
  the signed-off balanced geometry). Differences from flexible: the left chart is the
  rolling one-year return **bar** chart (`chart_data['rollingReturnData']`, from the
  shared `818_ROLLING_1_YEAR_GRAPH.xlsx` — the "<code> Fund Published" column, decimal
  fractions ×100) — y-axis ticked every 5% from just below the series minimum
  (−1%, 4%, … 24%), no gridlines, dark 0% baseline, no legend, x labels every second
  December; the right chart keeps the balanced cash-value spline but with 2-year tick
  pitch (Jan 14 … Jan 26 — the shorter history halves the balanced 4-year pitch).
- Performance-table display decorations: Fund³, Benchmark⁴, highest/lowest³·⁵, spacer
  row before highest/lowest. The reference keeps the colon in "Note: Totals may not
  cast perfectly…" (flexible drops it). Footnote ⁴'s "(estimated for <Month Year>)" is
  refreshed by every factsheet import from `MONTH_END_DATE_MMMM_YYYY`.
- Asset allocation: same `AA_DOM_*` layout as balanced but the equity mandate cap is
  60% — `FactsheetImporter` switches `Equities (60)` on `template === 'show-conservative'`.
- Sidebar equity indicator defaults to **5** of 10 dots; FUND MANAGERS label is plural
  (flexible's is singular).
- FEE RATES: "Foord global funds" subhead is an unshaded row and the two global-fund
  rows below it are pink, names flush-left with an em dash ("— Foord International").
  PERFORMANCE FEE EXAMPLES has five columns (A–E) whose values are stored as display
  strings rendered verbatim (the reference prints sharing-rate "10" in column A), and
  two footnotes (`performanceFeeExamples.footnotes` array).
- The explanation paragraph under the charts lives in `chart_data['explanation']`
  (static, preserved across imports).

## 5e. Bond-fund specifics (funds 24/25/26 — 826 Classes A/B2/B3)

- `template = 'show-bond'` renders `show-bond.blade.php` and exports via
  `pdf-bond.blade.php` (cloned from the conservative pair, which shares the
  signed-off balanced geometry). Page 1 replaces the usual tables/charts with:
  a full-width MATURITY BREAKDOWN grouped Fund/Benchmark bar chart (square
  bars, 10% major + 5% minor y ticks, two-line category labels "3-7 Years /
  (+22.2%)", centred square-marker legend); a PORTFOLIO STATISTICS table
  (FUND / BENCHMARK / RELATIVE TO ALBI, label col 46%, Yield row pfe-grey,
  WATM grey-2, rest grey-3, bold Total duration row, grey spacer row); two
  side-by-side CREDIT EXPOSURE tables (RATING keeps its fixed six rows with
  dashes; SECTOR lists only non-dash sectors and pads with empty shaded rows
  so both naartjie TOTAL rows sit level — totals computed at render, print
  100); and the PORTFOLIO VS BENCHMARK cash chart with SIX-month tick pitch
  (Oct 22, Apr 23, … — the short history quarters the balanced 2-year pitch)
  and y-title "Cash Value⁴ (R'000)".
- Factsheet importer bond mappings (keyed off the export's own keys):
  `MATURITY_*` → `chart_data['maturityData']` fund bars (uses the
  12-20/20+ split, not `MATURITY_12_PLUS`); the ALBI benchmark bars are NOT
  on the feed — they are stored values the importer preserves (seeded from
  the reference chart; maintain by hand). `MAT_CHANGE_*` → the bracketed
  change labels; `STAT_*/BM_*/VAR_TO_BM_*` →
  `asset_allocation['portfolioStatistics']` (display strings: "10.17%",
  "11.98 years", 2dp durations; floating-rate relative repeats the fund
  value); `RATING_*/SECTOR_*` → `asset_allocation['creditExposure']`
  (effective exposures — negatives and >100 are legitimate);
  `YEAR_n_MONTH_*/YEAR_n_YTD` → `chart_data['monthlyPerformance']` for the
  page-2 MONTHLY PERFORMANCE % grid (months 2dp, YTD as exported; YEAR_5 =
  month-end year anchors the calendar labels).
- **ERR handling**: the 826 feed exports `ERR` for every STAT_/BM_/VAR_ key
  and all MAT_CHANGE_ keys some months (all of 2026-07). ERR/absent cells
  preserve the previously stored value, so a broken feed month never blanks
  the published tables — fix values by hand (stats cells are inline-editable
  on the page) and the next good feed month replaces them automatically.
- Performance table: headers spell the years out (`3<br>YEARS`, `1<br>YEAR`,
  6/3 MONTHS), title suffix "…ANNUALISED⁵". Display decorations: Fund⁶,
  highest/lowest⁶·⁷, the Benchmark row carries NO marker. Page 1 prints only
  "*Please refer footnotes overleaf." (`performance_table['overleafNote']`);
  the seven numbered footnotes + rounding note render on page 2 between the
  TER paragraph and the footer (footnote wording, including "it's", matches
  the published reference verbatim).
- Page 2: MONTHLY PERFORMANCE % grid above FEE RATES. FEE RATES is titled
  per class ("FEE RATES (CLASS B2)") with two rows only (initial/exit 0.0%,
  Manager's charge 0.5%/0.25%/0.18% plus VAT for A/B2/B3 — statics, seeded
  per class). No performance-fee sections. The TIC omits the zero
  "— Performance charge" row (`FactsheetImporter` switches on
  `template === 'show-bond'`).
- No equity indicator dots (leave `equity_indicator_description` null).
  The published reference has a sidebar typo "INCOME DISIBUTIONS"; the
  template uses the correct INCOME DISTRIBUTIONS label deliberately.
- Seed script (Class A statics): scratchpad `seed-826-bond.php` (session
  c8ac7f54…) — recreate from the reference PDF + this section if lost;
  B2/B3 via `fund:add-class 24 B2 --import=2026-07` then set the per-class
  fee title/Manager's charge.

## 5f. Flex-income-fund specifics (funds 27/28/29 — 824 Classes A/B2/B3)

- `template = 'show-flex-income'` renders `show-flex-income.blade.php` and exports
  via `pdf-flex-income.blade.php` (cloned from the bond pair, which shares the
  signed-off balanced geometry; graphs keep the bond styling per the sign-off).
  Page 1 stacks: a full-width PORTFOLIO STRUCTURE % table (balanced-style
  SA/FOREIGN/TOTAL/CHANGE with per-row arrows — name column 34%, reference row
  pitch 4.1mm — plus a bold white "Foreign currency hedge" row and a naartjie
  "Foreign currency exposure" row under the total, both valued in the FOREIGN
  column, the hedge in accounting brackets); a PORTFOLIO STATISTICS label/value
  table (left 47%: Yield¹ pfe-grey, Spread to JIBAR grey-2, spacer, then the
  SA duration² and Offshore duration² groups in medium weight with em-dashed
  sub-rows) beside the MATURITY SPREAD % horizontal bar list (CSS bars, not
  Highcharts: naartjie bars scaled to the largest bucket, right-aligned value
  column, zero/dash buckets keep a zero-length bar labelled "-"); the two
  CREDIT EXPOSURE tables (RATING⁴ header; the sector list includes "Other");
  and the bond-style PORTFOLIO VS BENCHMARK cash chart (6-month ticks,
  y-title "Cash Value⁵ (R'000)").
- Factsheet importer flex mappings (all in `FactsheetImporter`):
  `PS_SA_*/PS_FOREIGN_*/PS_TOTAL_*` → `asset_allocation['rows']` display
  strings (zero holdings print "-", negative effective exposures print as-is);
  `PS_TOTAL_CHANGE_*` + `PS_TOTAL_CHANGE_SIGN_*` → the arrowed change column
  (blank sign → bare "-"); `FOREIGN_CURRENCY_HEDGE/EXPOSURE` → the two extra
  rows. Flex `STAT_*` keys (detected by `STAT_SPREAD_TO_JIBAR`) →
  `portfolioStatistics` single-value rows; the maturity buckets use
  `MATURITY_12_PLUS_YEARS` + `MATURITY_PERPETUAL` → `chart_data['maturitySpread']`
  (switched on `template === 'show-flex-income'` because the bond feed shares
  the same keys). The TIC drops the zero performance-charge row like the bond.
- **Feed ERR**: the 2026-07 824 factsheets export ERR for every STAT_ key —
  stats were seeded from the March reference (scratchpad
  `seed-824-flex-income.php`, session 2483638c) and are preserved on ERR;
  cells are inline-editable on the page and auto-refresh when the feed is fixed.
- Both credit TOTAL rows print a nominal 100 (the July sectors cast to 101;
  the published convention covers this with the rounding note).
- Perf decorations Fund⁷ / highest-lowest⁷·⁸, Benchmark unmarked; title suffix
  "…ANNUALISED⁶"; headers CASH VALUE⁵ / SINCE INCEPTION / 3 YEARS / 1 YEAR /
  6 MONTHS / 3 MONTHS / YTD / THIS MONTH. Footnotes 1–8 print on page 2
  (¹ yield with the published "it's" typos, ² duration, ³ TIPS, ⁴ ratings,
  ⁵ cash value — no trailing full stop, per the reference, ⁶ annualised,
  ⁷ net of fees, ⁸ highest/lowest "achieved in the period.") + rounding note.
  Sidebar keeps the corrected INCOME DISTRIBUTIONS label (published doc has
  the DISIBUTIONS typo); the benchmark stores an explicit `<br>` before
  "(Stefi Call)" to match the reference wrap.
- Fee rates per class: Manager's charge 0.5%/0.4%/0.25% plus VAT (A/B2/B3).
  The Class A reference titles the section plain "FEE RATES"; B2/B3 follow the
  bond convention "FEE RATES (CLASS B2/B3)". No equity indicator dots.
- B2/B3 via `fund:add-class 27 B2 --import=2026-07` then set the per-class fee
  title/Manager's charge.

## 5g. Domestic-balanced specifics (funds 31/32 — 820 Classes B2/B3)

- `template = 'show-domestic'` renders `show-domestic.blade.php` and exports via
  `pdf-domestic.blade.php` (cloned from the signed-off balanced pair, fund 9).
  Page 1 differences from balanced: a SINGLE Fund-vs-Benchmark cash chart at the
  left of the content column (no inflation chart — the feed has no inflation
  graph export — and no explanation paragraph); chart title carries sups ³,⁴,⁵;
  plot ≈52.3×33mm (measured: x 69.8→122.1mm, x-axis at 211.3mm), 2-year x-tick
  pitch (Jan 14 … Jan 26), end labels stack when the series finish within 30
  index points (higher value above), legend uses the default lineMarker rule
  (the balanced `legendSymbol: 'rectangle'` renders as a 2px dot under
  Highcharts 11 — keep the default here).
- Asset allocation: the 820 feed exports bare `AA_TOTAL_*` keys with **no
  AA_DOM_/AA_FRGN_ split** — `FactsheetImporter::mapDomesticAssetAllocation`
  (detection: `AA_TOTAL_EQ` present without `AA_DOM_*`, so it must stay after
  the balanced branch) emits a single `SA (100)` column + CHANGE and skips
  zero-value rows (the published sheet lists no Corporate bonds row while
  `AA_TOTAL_DEBT` exports 0.0). Label column 39.5% in the PDF.
- TOP 10 INVESTMENTS has three equal columns (SECURITY / ASSET CLASS /
  % OF FUND — no MARKET, every holding ZAF; seeded headers preserved by the
  importer) and NO pink Foord rows — plain grey fade (rows 1-7 grey-2,
  8-9 grey-3, 10 grey-4).
- Sidebar: FUND MANAGER singular (Nick Balkin); NEW INVESTMENTS ("At the
  manager's discretion") replaces the minimums row — same `minimums` column,
  exposed as `sidebar.newInvestments` when `template === 'show-domestic'`;
  no FOREIGN ASSETS section; equity indicator 6 of 10 dots; TIME HORIZON
  "Longer than three years."; the importer converts NUMBER_OF_UNITS commas to
  spaces ("4,517" → "4 517", the published convention).
- Performance table: headers CASH VALUE² / SINCE INCEPTION / 10 / 7 / 5 / 3
  YRS / 1 YR / THIS MONTH; render-time decorations Fund ³,⁵ / Benchmark ³,⁴ /
  highest-lowest ³,⁶ + spacer row; footnotes 1–6 (⁴ = ASISA benchmark change
  from 2 June 2025 incl. Watch™, ⁵ = "Before 2 June 2025 nil fees") + rounding
  note.
- Fees are per class from the Publisher files (no Class A exists):
  **B2** — standard 0.6% + VAT, sharing 15% (over– and under-outperformance),
  minimum 0.1% + VAT, 1-year rolling basis, four example columns A–D, and the
  TIC carries the "*Estimated as the class fee rate was changed on 2 June
  2025…" footnote (`fees.totalInvestmentCharge.footnote` — its presence also
  stars the TER row at render time).
  **B3** — standard 0.5% + VAT, sharing "15% of outperformance", minimum
  0.5% + VAT, TWO-year rolling basis, three example columns A–C
  (0.8 / 0.5* / 0.5), standard TER-year footnote refreshed by the importer.
  Both templates size the example columns from the stored `headers`.
- The page-2 sidebar drops the balanced "Additional detailed analysis…"
  paragraph (not in the 820 reference). TIC white rows are 1 and 5 (five data
  rows — no Foord global charges row on a domestic-only fund).
- Seed scripts: scratchpad `seed-820-domestic.php` + `seed-820-b3-fees.php`
  (session 6c7e35cd…) — recreate from the March 2026 B2 reference +
  `Funds/Publisher/820 B2/B3.pub` (UTF-16 strings in the trailing ~1.7MB) if
  lost; B3 via `fund:add-class <B2 id> B3 --import=2026-07`.

## 5h. Income-fund specifics (funds 33/34 — 825 Classes B2/B3)

- `template = 'show-income'` renders `show-income.blade.php` and exports via
  `pdf-income.blade.php` (cloned from the flex-income pair, which shares the
  signed-off balanced geometry). Page 1 re-grids the flex layout into paired
  rows: PORTFOLIO STRUCTURE % (left 49%) beside PORTFOLIO STATISTICS;
  MATURITY SPREAD % beside CREDIT EXPOSURE BREAKDOWN % (RATING³ header);
  then the PORTFOLIO VS BENCHMARK cash chart ALONE at 63% width —
  NINE-month x-tick pitch (Oct 22, Jul 23, … Jul 26), y-title "Cash Value⁴
  (R'000)", and a TIGHT y-max (ceil of max×1.05 to the next 5, not the
  balanced next-100 — the reference puts the R143 end label ~77% up the
  axis); then the performance table and the MONTHLY PERFORMANCE % grid,
  which prints at the bottom of PAGE 1 (not page 2), above the overleaf note.
- Portfolio structure: the 825 feed exports the flex `PS_*` keys but the
  published table has no SA/FOREIGN split — `FactsheetImporter` (income
  branch of `mapPortfolioStructure`) emits a single value column headed by
  the month-end date (`ASSET CLASS | 31 JUL 2026 | CHANGE`), fixed to the
  reference's six asset classes (Cash and call … Inflation linked bonds),
  with no foreign-currency hedge/exposure rows. Name column 54%; the date
  header is `white-space: nowrap`.
- Portfolio statistics: SA-only rows (Yield¹, Spread to JIBAR, spacer,
  SA duration² group with bare "— Fixed/Floating/Inflation linked duration"
  sub-labels — no "SA" prefix, no Offshore group). The 2026-07 feed exports
  ERR for every STAT_ key; values were seeded from the published July B2
  reference (Yield 9.44%, Spread 2.46%, SA duration 0.77 / 0.10 / 0.09 /
  0.58) and are preserved on ERR, inline-editable on the page.
- Maturity spread: five buckets only — 0—1 / 1—3 / 3—7 / 7—12 / "> 12 years"
  (12_PLUS + PERPETUAL merged; the reference has no Perpetual row).
- Perf decorations Fund⁶ / highest-lowest⁶·⁷, Benchmark unmarked; title
  suffix "…ANNUALISED⁵"; headers CASH VALUE⁴ / SINCE INCEPTION / 3 YEARS /
  1 YEAR / 6 MONTHS / 3 MONTHS / YTD / THIS MONTH. Footnotes 1–7 print on
  page 2 (¹ yield with the published "it's" typos, ² duration, ³ credit
  rating, ⁴ cash value — WITH trailing full stop here, ⁵ annualised, ⁶ net
  of fees, ⁷ highest/lowest "achieved." — no "in the period") + rounding
  note. The TIC drops the zero performance-charge row; its white rows are
  1 and 4 (four data rows).
- Page 2 is the simplified flex page 2 without the monthly grid: FEE RATES
  (two rows), TIC, TER paragraph, then footnotes 1–7 + footer (94mm gap).
  There is no Class A: B2 is the primary class. Fees per class —
  Manager's charge **0.3% plus VAT** (B2) / **0.2% plus VAT** (B3, from its
  `SA_TER_MANAGERS_CHARGE` export); both title the section plain "FEE RATES".
- Sidebar keeps the corrected INCOME DISTRIBUTIONS label (published doc has
  the DISIBUTIONS typo); FOREIGN ASSETS "N/A"; ISIN NUMBER last (from the
  feed's ISIN key); category uses plain hyphens ("South African - Interest
  Bearing - Short Term") matching the reference; no minimums, no equity
  indicator dots.
- Seed script: scratchpad `seed-825-income.php` (session 1a67fd85…) —
  recreate from the July 2026 B2 reference
  (`Funds/825 Income Fund/Foord Income Fund Class B2 at 2026-07-31.pdf`)
  + this section if lost; B3 via `fund:add-class 33 B3 --import=2026-07`
  then set the B3 Manager's charge. Disclaimer paragraphs and footer are
  cloned from fund 27; `logo_url` must be set explicitly (an empty string
  defeats the blade's `??` fallback and renders a broken logo).

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
