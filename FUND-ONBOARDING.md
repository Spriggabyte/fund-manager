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
`show-flexible`, `show-conservative`, `show-international`, `show-feeder`, `show-absolute`,
`show-shariah`, `show-australian-feeder`. PDF:
`show-equity` → `pdf-equity.blade.php`, `show-flexible` → `pdf-flexible.blade.php`,
`show-conservative` → `pdf-conservative.blade.php`, `show-absolute` →
`pdf-absolute.blade.php`, `show-shariah` → `pdf-shariah.blade.php`,
the international/feeder page
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
  `GEO_EXP_*` geography / `PS_SA_EQUITY` Shariah balanced / `PS_SA_TOTAL` flex income),
  so one importer serves all funds. The branches are order-sensitive where two
  variants share a key prefix — see §5g and §5n.
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
- **July 2026 reference alignment** (fund 36 = Class B added via
  `fund:add-class 14 B`; statics identical to R per `Funds/Publisher/875 B.pub`
  — same inception date and fees; ISIN/price/shares/TER come from the class's
  own exports): section headings/subtitles dropped to the signed-off balanced
  7.5pt/9pt (the published sheets adopted it — heading caps ~12px at 150 dpi),
  the page-1 sidebar Lipper award block was removed (`lipper_award = null`;
  the page-2 REFINITIV LIPPER FUND AWARD text section stays), and the sector
  bars now render from the importer-maintained `sector_allocation` column
  (the stale `asset_allocation['equitySectors']` copy was removed).
- **Import order**: import the prior quarter-end month first to seed the AAOT
  precise-current baseline (e.g. `fund:import {id}
  "storage/app/private/fund-data/2026-06/875"` then `…/2026-07/875` — the
  folder holds every class; the command selects the fund's own class files).
- **Feed limitations, corrected by hand after each import** (all
  inline-editable; flagged to Foord Aug 2026): the published ASSET ALLOCATION
  panel is *effective* exposure but the feed's AAOT rows are *gross* since
  2026-06 (Jul: published Equities 58 ▼5.4 / Cash 22 ▲4.2 vs feed 69/10 with
  gross-basis deltas); the geo table's per-region EQUITY/CASH is *gross* on
  the published sheet but *effective* on the feed (Jul: North America 20/10
  vs feed 9/22; published Europe TOTAL rounds to 29 vs feed 30); the "latest
  audited TER" sentence lags on the feed (R: published 1.07% vs feed 1.05%).
  The ANNUALISED COST RATIO table itself refreshes from the GLOBAL_TER_*
  keys (the refresh must run after the TIC write — regression-tested in
  `ExcelImportInternationalTest`).

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

## 5i. Inflation-linked-income specifics (fund 35 — 827 Class B2)

- `template = 'show-inflation-income'` renders `show-inflation-income.blade.php`
  and exports via `pdf-inflation-income.blade.php` (cloned from the income
  pair, which shares the signed-off balanced geometry). Page 1 re-grids to:
  a FULL-WIDTH PORTFOLIO STRUCTURE % table (blank | TOTAL | CHANGE headers,
  name col 51% / TOTAL 24.6%, quarterly "Change since <quarter end>" subtitle,
  row pitch 4.04mm, TOTAL prints "100.0"); PORTFOLIO STATISTICS (left 49% —
  just Real Yield¹ + Duration²) beside MATURITY SPREAD % (six buckets incl.
  "> 12 years" AND "Perpetual", row pitch 5.3mm, block stops 8.4mm short of
  the right edge); CREDIT EXPOSURE (left 49% — the SECTOR table keeps its
  full fixed row list WITH dashes, unlike the other bond-family sheets)
  beside the PORTFOLIO VS BENCHMARK chart; a short performance table; and
  footnotes 1–6 + rounding note at the bottom of PAGE 1 at body size
  (7.5pt — not the 6pt page-2 style), starting y=249.1mm.
- **Portfolio structure is hand-maintained**: the published table lists ILB
  maturity buckets ("RSA ILB 2—3 years", "Corp ILB 2—3 years", … including a
  duplicated "Replica ILB 2—3 years" row, verbatim from the reference) that
  the feed does NOT carry — the 827 factsheet exports only the standard
  PS_* asset classes, which don't match. `FactsheetImporter::
  mapPortfolioStructure` returns early for this template, preserving the
  seeded rows/subtitle; name, value AND change are all inline-editable on
  the page (like the bond fund's ALBI benchmark bars).
- Statistics: `STAT_YIELD` → Real Yield¹ (percent), `STAT_SA_DURATION` →
  Duration² (2dp); the 2026-07 feed exports ERR for both, so the values were
  seeded from the June reference (4.01% / 2.54) and are preserved on ERR.
  No Spread to JIBAR row (feed exports 0.00%), no duration split (overridden
  dashes on the feed).
- Chart: STRAIGHT line segments (`type: 'line'` — the published chart shows
  crisp corners, not the other templates' spline), ticks every THREE months
  from inception (Nov 24, Feb 25, …), linear y-axis from the 100 baseline
  with yMax = ceil(max/5)·5 (R 116 sits ~84% up the axis), baseline pinned
  at y=203.5mm via `chart.marginBottom: 54` in the 49.5mm wrapper (the
  label/legend boxes otherwise resize the plot), x labels 2.6mm below the
  axis (`labels.y: 17`), legend at the wrapper bottom (~y=215mm).
- Performance table: headers CASH VALUE⁴ / SINCE INCEPTION / 1 YEAR /
  6 MONTHS / 3 MONTHS / YTD / THIS MONTH (no 3 YEARS — fund incepted
  Nov 2024); only the Fund⁶ and Benchmark rows print (the import still
  stores highest/lowest; the published sheet omits them, no spacer row);
  Benchmark row is grey-4 #f0f0f0 (lighter than income's grey-1); title
  suffix "…ANNUALISED⁵". Footnote ⁴ has no trailing full stop.
- The fund name is the longest in the range: `.fund-name` drops to 22.5pt
  with zero tracking + nowrap so "… FUND — CLASS B2" stays on one line.
- Page 2 = income page 2 without the numbered footnotes: FEE RATES (two
  rows — initial/exit 0.0%, Manager's charge "0.4 plus VAT", no % sign,
  per the reference), TIC headed "12 MONTHS | 36 MONTHS*" whose star pairs
  with the `fees.totalInvestmentCharge.footnote` "*Estimated as the fund
  was incepted less than three years ago…" printed between the table and
  the TER paragraph, then the footer (margin-top 128.6mm → y=251.6mm). The
  TIC drops the zero performance-charge row; white rows 1 and 4. The page-2
  sidebar KEEPS the "Additional detailed analysis…" paragraph (cloned from
  fund 33 — verbatim match with the 827 reference).
- Sidebar: FUND MANAGERS plural (Farzana Bayat and Rashaad Tayob), MINIMUM
  LUMP SUM / MONTHLY R20 000 / R1 000, FOREIGN ASSETS prose, ISIN last, no
  equity indicator dots. Statics keep the published wording verbatim, incl.
  "fixed incomes securities" in PORTFOLIO ORIENTATION.
- Seed script: `Funds/827 - Inflation Linked Income Fund/
  seed-827-inflation-income.php` (run via tinker `include`; re-runnable) —
  recreate from the June 2026 B2 reference in the same folder + this
  section if lost. Import: `php artisan fund:import 35
  "storage/app/private/fund-data/<YYYY-MM>/827"`.

## 5j. International-trust specifics (fund 37 — 874 Class B)

- `template = 'show-international-trust'` — cloned from `show-international`
  (fund 14/36), which is itself the print layout, so `internalPdfView` maps
  it to itself. Shares the July-2026 typography (7.5pt section/page-2
  headings) and the four-series log-scale Chart.js performance chart
  (874's feed starts Mar 1997, so the 48-month tick rule prints
  Mar 97 … Mar 25 as published).
- Sidebar (Guernsey unit-trust labels): MARKETING COMMUNICATION at 8.4pt,
  MASTER FUND (new `master_fund` column) and MASTER FUND RETURNS
  (`master_fund_returns`) prose, TYPE OF UNITS / MINIMUM INVESTMENT /
  MONTH END UNIT PRICE / NUMBER OF UNITS labels on the existing columns,
  SEDOL NUMBER last; no depository/sub-investment manager/Lipper. Equity
  indicator 6 of 10 dots; its description renders a step smaller (7.2pt).
  `investment_manager` / `fund_managers` store the reference's manual
  `<br>` breaks. Section gap 1.8mm (the sheet runs to y=285.5mm).
- Performance table has TWELVE body rows: the currency group gains
  `Comparator 7` → "Fund in rands³" (the blade prefixes the bare feed cash
  value with "R "); highest/lowest superscript **5,8** (published quirk);
  the spacer/highlight nth-child rules shift accordingly. Row pitch
  4.23mm (`td` padding 0.68mm).
- Importer: `$` portfolio-size prefix extended to this template; the geo
  column totals fall back to summing the column when the feed exports "-"
  (874's `GEO_EXP_EQTY_TOTAL` is "-" against a published 69). GLOBAL_TER
  refreshes the ANNUALISED COST RATIO table (see 5c); the "latest audited
  TER" sentence stays seeded (feed's FY figure 1.03% ≠ published 1.01%).
- AAOT change arrows need the same quarter-baseline recipe as 875 (§5c):
  seed each row's `precise` with the prior-quarter values before the first
  import (Jul 2026 baselines derived from the published arrows: Equities
  63.1, Cash 18.0, Commodities 6.9, Property 5.1, Govt bonds 4.5, Corp
  bonds 2.4).
- Page 2 re-spaced to the 874 reference: sidebar leading 8.6pt, body
  7.9pt/zero tracking (6-line TER paragraph), notes 8.6pt leading, section
  gap 9.2mm, NOTES pinned at y≈195.7mm (margin-top 29.7mm), footer bottom
  padding 6.1mm. MORE ABOUT THE FUND keeps the published mid-sentence
  paragraph break ("…borrow up to 10% of" / "NAV and does not engage…").
- Seed script: `Funds/874 - International Trust/
  seed-874-international-trust.php` (tinker `include`; re-runnable) —
  recreate from the July 2026 Class B reference in the same folder + this
  section if lost. Import: `php artisan fund:import 37
  "storage/app/private/fund-data/<YYYY-MM>/874"`.

## 5k. Global-equity (Luxembourg) specifics (funds 38/39/40 — 877 Classes R/B/R1)

- `template = 'show-global-equity'` — cloned from `show-international` and,
  like it, IS the print layout (`internalPdfView` maps it to itself). The
  banner carries NO class suffix; the class prints in the sidebar as the
  heading-only `SHARE CLASS <code>` row and in the `FEES (CLASS <code>)`
  label, both derived from `class_code` at display time.
- Sidebar: `MARKETING COMMUNICATION`, shareClass, domicile, management
  company, DEPOSITARY (the 877 sheets spell it with an A; column is still
  `depository`), investment/sub-investment managers, fund managers,
  `INCEPTION DATE (FUND / CLASS)` — the label drops the `(FUND / CLASS)`
  qualifier when the stored date has no `/` (Class B: "2 April 2013") —
  base currency, equity indicator (NINE of ten dots), Morningstar category,
  BENCHMARK, type of shares, MINIMUM SUBSCRIPTION AMOUNT (`minimums`,
  `white-space: nowrap` keeps it on one line) + SUBSEQUENT SUBSCRIPTION
  AMOUNT (own column), TOTAL PORTFOLIO SIZE, month-end share price, NUMBER
  OF SHARES (raw counts convert to "5.3 million" in the importer), time
  horizon, fees, ISIN. Sidebar pitch is denser than 875's: section
  margin-bottom 0.55mm, h3 tracking 0.01em.
- Page 1 grid: full-width PORTFOLIO STRUCTURE % sector bars (from
  `sector_allocation`; explicit ESAOT change signs; `variance` column from
  `ESAOT_RANK_n_VAR_TO_BM`, printed tight "+11.5"; feed "Property" renames
  to "Real estate"; the Cash bar renders dark navy; label 34mm / bar 38mm /
  value 3.5mm, row pitch 4.0mm; title top-aligns with the two-line
  "Change since <quarter end>" and "Variance to MSCI ACWI⁶" column heads),
  then GEOGRAPHIC EQUITY EXPOSURE⁶ (grouped Chart.js column chart from
  `asset_allocation['geographicEquityExposure']` — Fund naartjie vs MSCI
  ACWI navy, 0–70% y-axis with 10% tick marks, chunky bars
  categoryPercentage 0.82 / barPercentage 0.94) beside PORTFOLIO
  PERFORMANCE VS BENCHMARK (three-series log-scale line chart:
  fund/benchmark/peerGroup from the 3-series `performanceData`; ticks every
  36 months → Apr 13 … Apr 25; end-value plugin attached to THIS chart
  only — a global `Chart.register` annotates the bar chart too). Both
  wrappers 45mm. Legend reads "MSC AC World Index" — [sic], per the
  signed-off reference.
- TOP 10: headers SECURITY/SECTOR/MARKET/% OF FUND (`TOPX_SECURITY_SECTOR_n`
  feeds the assetClass column); the SECTOR column displays in Title Case
  with feed "Healthcare" printed "Health Care" (display-time only); uniform
  row grey (no 875 fade).
- Performance table: rows renamed/ordered at display time — Fund³ /
  MSCI AC World Index (no sup) / Peer group⁴, spacer, Fund in sterling³
  (comparator 3) / Fund in euros³ (comparator 4), spacer, highest/lowest³·⁵.
  Name column 24% so the MSCI row holds one line. Title
  "…ANNUALISED¹)" — unicode superscripts in seeded titles render via
  `$renderHeading`.
- Page 2: ANNUALISED COST RATIO % with the indented "— Performance"
  component row (importer skips it when the class exports blank
  performance-fee cells — B/R1) and the red "Total investment charge"
  total; PERFORMANCE FEES (3 paragraphs) + PERFORMANCE FEE EXAMPLES
  five-column table (`page2Content` — Class B carries NEITHER, it has no
  performance fee); sharePricing; moreAboutFund; NOTES; footer with the
  T-line only (email/website rows render only when populated). Column
  starts at y=20.8mm (higher than 875), body 7.4pt/9pt, notes 6.9pt/8.2pt.
  The sidebar TER paragraph's "The latest audited TER is X%." refreshes per
  class from `TER_FOR_FUND_FINANCIAL_YEAR_END` (R 0.96% / B 1.07% /
  R1 0.58%).
- Per-class statics come from the Publisher sources (`Funds/Publisher/877
  {B,R,R1}.pub` — extract with UTF-16LE decode): B has "Initial fees: None /
  Annual fees: 1.00% fixed", single inception date, and its notes 1/3 keep
  the original wording without the Class B look-through sentence; R is
  0.85% standard / 15.0% sharing / uncapped; R1 adds "Minimum annual fee:
  0.50%". Notes 1/5 keep the reference's verbatim quirks ("annualised
  periods great than", "rand return").
- Seed script: `Funds/877 - Global Equity Fund (Luxembourg)/
  seed-877-global-equity.php` (tinker `include`; re-runnable; seeds all
  three classes) — recreate from the Class R reference in
  `…/Design/` + this section if lost. Import: `php artisan fund:import
  38|39|40 "storage/app/private/fund-data/<YYYY-MM>/877"`.

## 5l. Absolute-return specifics (fund 41 — 816 Class A)

- `template = 'show-absolute'` renders `show-absolute.blade.php` and exports via
  `pdf-absolute.blade.php` — both clones of the **signed-off balanced** pair
  (fund 9 / 810 Class A), changed only where the 816 reference's graphs differ.
  Keep the two in sync; the client compares the page against the PDF.
- **Asset allocation is a bar chart, not a table.** The fund is unconstrained
  and holds no foreign assets, so the SA/FOREIGN/TOTAL/CHANGE table gives way to
  one naartjie bar per asset class. Column geometry measured off the reference
  (main content starts at x 65.35mm): name 0–24.57mm, bar track 24.57–48.77mm
  (longest holding = 95% of the track = 23.0mm), value right-aligned at
  55.35mm, arrow at 60.35mm, change at 66.35mm; row pitch 4.5mm, bar 3.6mm.
  Arrows follow the table convention — black ▲ up, steel-blue ▼ down, and
  **nothing at all when the change rounds to 0.0**.
- **EQUITY SECTOR ALLOCATION is a pie**, filling the right half of the same row
  (centre 171.8mm / 93.3mm, diameter ~29mm, hairline white slice separators).
  Slices run clockwise from twelve o'clock in the feed's rank order with a fixed
  palette: naartjie, dark navy, #cccccc, steel blue, dark grey, mushroom,
  naartjie-50. Two gotchas, both fixed in the template:
  - Highcharts' own outside dataLabels align to the plot edges, so the labels
    are drawn with `chart.renderer` instead — centred on each slice's bisector,
    then decluttered by nudging overlapping pairs apart (the reference separates
    its Healthcare/Industrials labels the same way).
  - `SVGElement.getBBox()` is **cached by content, not position**, so it cannot
    be re-read after positioning: capture the block's top offset at creation.
    Re-reading it doubles every label's y and throws the lower half of them off
    the (clipping) chart box.
  - Highcharts offsets a pixel `plotOptions.pie.center` by its own plot origin
    (~20px), so the constants are ~5.3mm short of the measured centre.
- **One chart, log scale.** The balanced pair of charts collapses into a single
  PORTFOLIO PERFORMANCE VS BENCHMARK spline over the LEFT HALF of the column
  only (plot box 49.1 × 33.9mm at x 68.8mm — hence `.chart-container` is sized,
  not stretched). The axis is **logarithmic** from 100 to the next whole decade
  (Excel's scaling — a linear axis visibly bows the curve); only the 100
  baseline is labelled, x ticks run every 36 months. The two series finish
  within a percent of each other, so their end labels are nudged ±9px apart.
  There is no explanatory paragraph under the chart.
- Row fades differ from the balanced sheet: TOP 10 starts at grey (`--pfe-grey`,
  then row-grey-1) instead of two pink Foord-fund rows, and the six-row
  performance table runs pink / pfe-grey / grey-1 / spacer / grey-4 / grey-4.
- The TIC table has five data rows (no "— Foord global charges"), so the white
  rows are `nth-child(1)` and **`nth-last-child(2)`** — the balanced sheet's
  `nth-child(6)` would whiten the red total row into invisibility.
- Sidebar label overrides: `FUND MANAGER` (singular), `NEW INVESTMENTS` (the
  `minimums` slot, "At the manager's discretion") and `ISIN` (not ISIN NUMBER);
  no FOREIGN ASSETS section. Category prints with plain hyphens
  ("South African - Multi Asset - Flexible"), unlike the balanced em dashes.
- Performance rows are renamed at display time like the flexible fund:
  Fund³, Benchmark³·⁴, `Comparator 2` → FTSE/JSE All share⁵, spacer,
  Fund highest/lowest³·⁶. Page 2: `FEE RATES (CLASS A)`, no Foord-global-fund
  sub-rows, "over- or underperformance" (hyphen), and
  `PERFORMANCE FEE EXAMPLES FOR THE FOORD ABSOLUTE RETURN FUND %`.
- Seed script: `Funds/816 - Absolute Return Fund/seed-816-absolute-return.php`
  (tinker `include`; re-runnable). Import:
  `php artisan fund:import 41 "storage/app/private/fund-data/<YYYY-MM>/816"`.
- **Feed caveat (2026-07):** `FUND_FINANCIAL_YEAR_END` /
  `TER_FOR_FUND_FINANCIAL_YEAR_END` came through as *31 March 2025 / 1.48%*,
  a year behind the published June sheet (*31 March 2026 / 2.42%*). The
  importer writes what the feed says; confirm with Foord before publishing.

## 5m. Prescient-feeder specifics (funds 42/43 — 822 Classes A/B2)

- `template = 'show-prescient-feeder'` — cloned from `show-feeder` (809) and,
  like it, IS the print layout (`internalPdfView` maps it to itself). Register
  a new template in FOUR places or the on-screen page silently falls back to
  `show.blade.php`: `FundController::ALLOWED_TEMPLATES`,
  `FundController::internalPdfView`, `StoreFundRequest`, and the
  `edit.blade.php` template picker.
- Prescient chrome vs the Foord-branded 809 sheet: naartjie date badge (not
  navy); banner carries NO class suffix (stripped in the blade and in the
  `fundNameNoClass` client formatter — the class prints in the sidebar CLASS
  row off `class_code`); URLs/emails set in body colour, so `.ref-link` is
  neutralised rather than gold.
- Sidebar additions: heading-only `mddHeading` row ("MINIMUM DISCLOSURE
  DOCUMENT<br>AND GENERAL INVESTOR REPORT" — the reference breaks after
  DOCUMENT), `shareClass` → CLASS, BENCHMARK, and RISK INDICATOR /
  RISK INDICATOR DEFINITION on new `risk_indicator` /
  `risk_indicator_definition` columns (migration
  `add_risk_indicator_fields_to_funds_table`; `risk_of_loss` keeps its own
  meaning on the Foord sheets). `fund_managers` carries an explicit `<br>`.
  Equity indicator: SIX of ten dots.
- Type sizes are the big departure from 809 — measured, not guessed:
  section and page-2 headings 7.3pt/8.8pt (0.77x the 809 sheet), subtitles
  7.4pt, sidebar labels 5.85pt (0.83x) with the 7pt body unchanged, glossary
  7pt/9.35pt. Table body, bar labels and top-10 copy already matched at 1.00x.
  Every table's first column sits 1.05mm left of the 809 sheet's, so the base
  `.foord-table` cell padding-left drops from 1.5mm to 0.45mm (top-10 and TIC
  first-column overrides scale with it).
- Page 1 geometry (all `pdftotext -bbox` measured): content column x=65.2mm,
  right column x=137.3mm, bar row pitch 4.0mm on both lists, geo rows 4.1mm,
  top-10 4.13mm, banner title 22.5pt on ONE line (23pt wraps "FUND"),
  EQUITY SECTOR ALLOCATION offset 9.2mm below the allocation bars.
- Chart: same four-series log-scale Chart.js line chart as 809, but ticks
  every 9 months anchored on index 0 (Feb 22 … Nov 25 — the 822 series opens
  at the Feb 2022 inception with no baseline row) and `min: 85`, because
  world bonds dip to 88.8 and end at R 99; the 809 sheet's `min: 100` clips
  that line.
- Performance table: SEVEN columns (name / CASH VALUE² / SINCE INCEPTION /
  3 YEARS / 1 YEAR / 6 MONTHS / YEAR TO DATE). The five period columns are a
  uniform 12.94%, cash value 13% (narrower wraps "CASH VALUE²" onto two
  lines), name 22.4%. Rows renamed at display time as on 809 except
  "US Inflation⁵" with a capital I.
- Page 2 adds CONTRIBUTORS/DETRACTORS and POLICY OBJECTIVE above FEE RATES
  and drops the footer entirely; the closing important-info paragraph renders
  grey italic (`.disclaimer` on the last paragraph) and the published line
  reads "Issue date …" — the importer branches on the template for that
  prefix. Block margins are per-section (6.7 / 11 / 7.1 / 5.4mm), not the
  uniform 5.6mm.
- Page 3 is new and identical for both classes: CONTACT DETAILS beside
  GLOSSARY SUMMARY, both stored in `page2_content` (`contactDetails.blocks`
  of `{label,value}` lines, `glossary.entries` of `{term,definition,bold}`).
  Its column starts at x=63.2mm — 2mm left of page 2 — hence the
  `.page2-content.page3-content` override (single-class specificity loses to
  `.page2-content`'s shorthand padding). Reference quirk: "Liquidity risk:"
  is the one glossary term that is NOT bold.
- Importer: the 822 export emits zero-value LAST_DISTRIBUTION rows, but the
  fund is a roll-up whose DISTRIBUTIONS row is static prose — `FactsheetImporter`
  skips distributions for this template so a monthly re-import stays
  idempotent. The 809 export has no distribution keys, so funds 19/20 are
  unaffected.
- Reference inconsistencies normalised (flag to the client): the Class A
  sheet reads "Jing Cong Zue" and credits footnote 6 to Bloomberg L.P.,
  while Class B2 and the 809 sheet read "Xue" and credit Factset — both
  classes carry Xue/Factset.
- Seed script: `Funds/822 - Prescient International Feeder Fund/
  seed-822-prescient-feeder.php` (tinker `include`; re-runnable; seeds both
  classes). Import: `php artisan fund:import 42|43
  "storage/app/private/fund-data/<YYYY-MM>/822"`.
- Verification: page landmarks within ±1.5mm on all three pages; AE-fuzz-5%
  at 150dpi A p1 24.8% / p2 13.1% / p3 10.3%, B2 25.8% / 15.7% / 10.3%
  (residual = the feed being one month newer than the reference, plus the
  canvas chart). A `pdftotext` token-set diff shows no missing or extra
  copy beyond the canvas tick labels.

## 5n. Shariah-balanced specifics (funds 44/45 — 840 Classes B and B3)

- `template = 'show-shariah'` renders `show-shariah.blade.php` and exports via
  `pdf-shariah.blade.php` (both cloned from the signed-off balanced pair,
  fund 9 — `show.blade.php` / `pdf.blade.php` stay frozen). Register a new
  template in FOUR places or the on-screen page silently falls back to
  `show.blade.php`: `FundController::ALLOWED_TEMPLATES`,
  `FundController::internalPdfView`, `StoreFundRequest`, and the
  `edit.blade.php` template picker.
- **Page 1 differences from balanced.** The two balanced charts collapse into
  a single Fund-vs-Benchmark cash chart over the left 51.2% of the content
  column: heading `PERFORMANCE VS BENCHMARK`, rotated `Cash Value² (R'000)`
  caption, quarterly x-ticks anchored on index 0 (Sep 24 … Jun 26), a `100`
  baseline label and two end-value annotations. No inflation chart and no
  explanation paragraph. The PRICE_GRAPH export carries a third (ECPI) series
  that the reference does not plot — imported, not drawn.
  - Chart type is **`line`, not `spline`** — the reference has sharp vertices
    at every monthly point — at `lineWidth: 1.1`.
  - `legendSymbol: 'rectangle'` collapses to a dot at `symbolHeight: 1`; use
    `'lineMarker'` with markers off (same trap as §5l).
  - The eight quarterly labels touch at this plot width and Highcharts
    ellipsizes them ("Sep …") by default — set `labels.allowOverlap: true`
    and `style.textOverflow: 'none'`. The reference runs them together.
  - y-axis max = `Math.ceil(peak) + 4`. The balanced sheet's round-up-to-
    hundreds rule flattens this fund's short history (peak ≈ 124) against
    the 100 floor.
- Asset allocation rows are Equities (75) / Listed property (25) / Sukuk (50)
  / Commodities (10) / Income (100) — the debt row is Sukuk and there is no
  separate money-market row (cash sits in Income).
- Performance table has EIGHT columns (name / CASH VALUE² / SINCE INCEPTION /
  1 YR / 6 MONTHS / 3 MONTHS / YTD / THIS MONTH) and only Fund³ and
  Benchmark³·⁴ rows — **no highest/lowest rolling rows** and no footnote 5,
  though the feed still exports `FOORD_HIGHEST_*`/`FOORD_LOWEST_*`. Store the
  YTD header as `<br>YTD` and never `&nbsp;<br>YTD`: the header→row-key map
  strips tags and collapses `/\s+/`, which does not match a non-breaking
  space, so an `&nbsp;` silently blanks the whole column.
- Sidebar: FUND MANAGER **singular** (Rashaad Tayob and Farzana Bayat);
  equity indicator 7 of 10, off-dots solid grey as on the signed-off fund 9
  pair. Both sidebars set type ~2.5% smaller than the balanced sheet
  (`.sidebar-text` 6.83pt, `.sidebar-heading` 5.85pt, page-2 column 6.52pt in
  a box 1.1mm narrower) — at the balanced sizes INCOME DISTRIBUTIONS and
  PORTFOLIO ORIENTATION wrap a word early and the page-2 column drifts ~10mm
  by the foot. Section gaps 1.555mm.
- **Page 2.** No PERFORMANCE FEES or PERFORMANCE FEE EXAMPLES sections (both
  templates render those only when the keys are present, so the seed clears
  them). A new **FOORD SHARIAH FUNDS** block (SAC / non-permissible-income
  prose) lives in `page2_content.shariahFunds`. The star sits on the
  `36 MONTHS*` heading, not on the TER row, and the footnote text differs per
  class ("annualised for three years" on B, "for a year" on B3 — a reference
  quirk kept verbatim).
  - The FOORD SHARIAH FUNDS block (top 152.8mm) and the footer (top 248.3mm)
    sit at the SAME y on both class references although the fee tables above
    them end 8mm apart, so both are absolutely positioned rather than flowed.
  - The FEE RATES value column starts at x=112.6mm, far left of the TIC's
    "12 MONTHS" column: the Shariah sheets carry long prose values ("Zero fee
    class…"), so Paul's align-the-two-column-breaks rule from
    [`pdf-reference-matching`] does NOT apply here. Fee labels are top-aligned
    (`vertical-align: top`) — B3's values run to two lines.
  - Watch margin collapse: `.tic-section`'s table margin-top collapses with
    the section heading's margin-bottom, and `.footer-contact`'s margin-top
    collapses with the preceding `.footer-text`'s margin-bottom. Set the gap
    on whichever side is larger or nothing moves.
- **Importer.** 840 exports its asset allocation on `PS_*` keys with a
  SA/FOREIGN/TOTAL split — `FactsheetImporter::mapShariahAssetAllocation`,
  detected on `PS_SA_EQUITY`. That branch **must come before** the
  flex-income `PS_SA_TOTAL` branch, which 840 also exports and which would
  otherwise map the allocation onto the cash/bond category list (824, 825 and
  827 do not export `PS_SA_EQUITY`). Change arrows come from
  `PS_TOTAL_CHANGE_SIGN_*` but are suppressed when the change is zero — the
  feed still signs those rows and the sheet prints a bare number. Change
  values are `number_format(..., 1)` because the feed types the cell
  inconsistently. Top-10 asset classes are pluralised
  (`Equity`→`Equities`, `Commodity`→`Commodities`) **scoped to this
  template** — twenty other signed-off sheets publish the singular wording.
- Seed script: `Funds/840 - Shariah Balanced Fund/seed-840-shariah.php`
  (tinker `include`; re-runnable; seeds both classes). Import:
  `php artisan fund:import 44|45
  "storage/app/private/fund-data/<YYYY-MM>/840"` — the class-file matcher
  already keeps `840B` distinct from `840B3`.
- **Reference inconsistencies (flagged to the client).** The two class sheets
  are a month apart and disagree: the chart heading reads "PERFORMANCE VS
  BENCHMARK" on B (July) and "PORTFOLIO VS BENCHMARK" on B3 (June); the axis
  caption is superscript ⁴ on B and ⁵ on B3 although both sheets carry only
  footnotes 1–4 and footnote 2 is the cash-value one. Per the client's
  ruling both classes render the newer B wording and a corrected ². The two
  references are also identical from the banner down to the asset-allocation
  TOTAL row and then diverge by up to 10mm (B3's TOP 10 sits 3.3mm higher,
  its chart block is shorter) — Publisher hand-placement drift, so both
  classes are built on the newer July grid. The same applies to the page-2
  gap below the fee table (21.4mm on B, 12.0mm on B3): a fixed 16.8mm follows
  the newer sheet and B3's TIC block therefore sits ~5.6mm below its own
  reference.
- Verification: page-1 and page-2 landmarks within ±1.4mm of the July Class B
  reference on both classes (the page-2 sidebar's last line drifts to 3.3mm);
  page-1 sidebar reproduces the reference line for line, 69/69. Residual
  value differences are real: the 27-Aug export restates the July figures the
  6-Aug sheet published (foreign Sukuk 7.0 vs 8.7, foreign Income 1.3 vs 0.0,
  TER 0.39 vs 0.18, Foord global charges 0.21 vs 0.00; foreign total still
  43.8), and PUBLISHED_DATE reads 12 August against the sheet's 06 August.

## 5o. Prescient-global-equity specifics (funds 46/47 — 823 Classes A/B2)

- `template = 'show-prescient-global-equity'` — a clone of
  `show-prescient-feeder` (822) for the Prescient chrome, with page 1's
  content column rebuilt from `show-global-equity` (877), the master
  fund it feeds. Register it in the same FOUR places (§5m) — the
  on-screen fallback trap applies unchanged; `FundTemplateSelectionTest`
  now guards both halves.
- What carries over from 822 untouched: naartjie date badge, banner with
  no class suffix, the whole sidebar row set (down to RETURNS IN US$ and
  ISIN NUMBER), page 2's CONTRIBUTORS / POLICY OBJECTIVE / FEE RATES /
  TIC / INVESTING OFFSHORE stack with no footer, and page 3's CONTACT
  DETAILS + GLOSSARY (including the un-bold "Liquidity risk:" quirk).
- The one chrome difference: the title banner is **dark navy**
  (`--dark-navy`), not naartjie. The badge stays naartjie. Equity
  indicator: NINE of ten dots (822 shows six); the tenth is a solid grey
  dot, as on 822 — still awaiting the client's ruling.
- Page 1 replaces 822's asset-allocation and equity-sector bars with a
  single **PORTFOLIO STRUCTURE %** list (877's `.ps-*` block): label,
  bar, value, change arrow and a variance-to-MSCI-ACWI column. Measured
  geometry: labels x=65.2mm, bars 97.7→127.3mm, value right-aligned at
  135.5mm, arrow at 160.0mm, change at 168.5mm, variance at 202.8mm,
  4.0mm row pitch, 2.4mm bars. The Cash bar alone renders navy.
- **TOP 10 INVESTMENTS moves above the charts** (877 puts it below), its
  second column is headed SECTOR (seed it — the importer's default is
  ASSET CLASS) and prints in title case with "Health Care" spelt out,
  reusing 877's `top10Sector` formatter.
- Charts sit side by side below the top 10:
  - **GEOGRAPHIC EQUITY EXPOSURE** — 877's grouped `#geoChart`, Fund
    naartjie vs MSCI ACWI navy, 0–70% axis. Reference geometry: 3.73mm
    bars in an 8.81mm pair on a 13.72mm pitch, plot 73.1→128.0mm with
    the 0% baseline at y=217.8mm. Chart.js draws bars narrower than the
    nominal percentages imply — measure the raster and scale
    `categoryPercentage` rather than trusting the arithmetic.
  - **ILLUSTRATIVE PERFORMANCE** — two series (Fund, Benchmark), log
    axis `min: 85, max: 200`, ticks every 9 months anchored on index 0
    (Feb 22 … Nov 25) exactly as on 822. The reference draws **no rule
    along the bottom of the plot**: its only horizontal line is the 100
    baseline, which both series dip below. Reproduce it as the y axis's
    single gridline with `x.border.display: false` — not as the x axis.
  - The end-value plugin ("R 188" / "R 137") must be passed in the line
    chart's own `plugins: []`, never `Chart.register`ed: globally
    registered it also labels the geographic chart's last bars.
  - Each legend centres on its **plot box**, not on its column, and the
    two space their items differently (9.15mm / 2.27mm for the
    geographic legend, 12.76mm / 0.46mm for the performance one).
- Performance table: seven columns, but SINCE INCEPTION is wider than
  the other periods (cells 36.4 / 20.8 / 21.7 / 14.2 / 14.2 / 14.4 /
  14.1mm on a 139.0mm table) and the sixth reads **LAST 6 MONTHS**.
  Rows are Fund / Benchmark (FOORD_COMP_1 = MSCI ACWI) / Peer group
  (FOORD_COMP_2), spacer, highest/lowest — 822 maps COMP_1 to the peer
  group instead, so the display names are not interchangeable.
- Type scale, measured with `pdftotext -bbox` string widths — do NOT
  assume the clone's: page 1 matches 822 exactly, but page 2 sets its
  **tables 7% larger** (8.03pt, the TIC table excepted at 7.5pt) and its
  **prose 10% smaller** (7.41pt). Page 3's glossary column is 3mm
  narrower than page 2's and starts 1.1mm further left.
- Page 2 opens with an **ASSET ALLOCATION %** table (Equity securities /
  Money market / Property) that 822 does not have. The 823 export
  carries no allocation keys at all — unlike 822's `AAOT_*` rows — so it
  is seeded static in `page2_content.assetAllocation` and **needs a
  manual update each month, or a feed change**. Flagged to the client.
  Its figure right-aligns 62.4mm short of the table's right edge, a
  Publisher quirk reproduced with padding rather than a third column.
- Importer changes, all data- or template-shape gated:
  `FactsheetImporter::PRESCIENT_TEMPLATES` now drives the "Issue date …"
  prefix and the zero-value-distribution skip (823's export emits them
  although the sheet prints static prose); `PORTFOLIO_STRUCTURE_TEMPLATES`
  drives the Property → "Real estate" rename. `PriceGraphImporter` gained
  a two-series branch for an MSCI benchmark in column D with **nothing in
  column E** — 821/878/879/880 also lead with an MSCI benchmark but carry
  a peer or second benchmark alongside, and must not fall into it
  (regression-tested).
- Seed script: `Funds/823 - Prescient Global Equity Feeder Fund/
  seed-823-prescient-global-equity.php` (tinker `include`; re-runnable;
  seeds both classes). Import: `php artisan fund:import 46|47
  "storage/app/private/fund-data/<YYYY-MM>/823"`.
- Only the Class A reference was supplied. B2's per-class values are
  derived from its own export (annual fee 0.10% ex VAT, TER 1.12/1.30,
  ISIN ZAE000307757); confirm against a B2 sheet when one arrives.
- Verification: every page landmark within ±0.5mm on all three pages
  (page 1 within ±0.5mm, page 2 ±0.5mm, page 3 ±0.5mm); pixel diff at
  150dpi p1 16.2% / p2 14.7% / p3 11.3%. A `pdftotext` token-set diff
  shows no missing or extra copy beyond the canvas tick labels, the
  reference's Wingdings arrows, and the feed being one month newer than
  the reference (peer-group revisions, TER 36-month).

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


## 5p. Shariah-income specifics (fund 49 — 841 Class B)

- `template = 'show-shariah-income'` renders `show-shariah-income.blade.php`
  and exports via `pdf-shariah-income.blade.php`, both cloned from the
  flex-income pair (824), which already carries the exact page-1 grid this
  sheet uses: full-width PORTFOLIO STRUCTURE %, then PORTFOLIO STATISTICS
  beside the MATURITY SPREAD % bars, then CREDIT EXPOSURE BREAKDOWN %
  beside the cash chart, then the performance table. Register the template
  in the same FOUR places as §5n or the on-screen page silently falls back
  to `show.blade.php`.
- **Page 1 differences from 824.** No foreign currency hedge/exposure rows
  (841 exports neither); the structure rows are the coarse PS_* classes
  (Cash and call / Equities / Income / Sukuks / Property) and zero holdings
  print "0.0", not "-"; the TOTAL row prints "100.0". The credit SECTOR
  table keeps its full fixed list WITH dashes (as 827 does, unlike 824/825).
  The performance table has EIGHT columns and only the Fund⁶ and Benchmark
  rows — no highest/lowest and no spacer, though the feed still exports
  `FOORD_HIGHEST_*`/`FOORD_LOWEST_*`. Footnotes 1–6 plus the rounding note
  print at the BOTTOM OF PAGE 1 (the 827 `.p1-footnotes` pattern), not on
  page 2, at 6.84pt.
- **Chart.** Heading `PERFORMANCE VS BENCHMARK`, `type: 'line'`
  (`lineWidth: 1.4`), quarterly x-ticks anchored on index 0 (May 25 … May
  26), rotated `Cash Value⁴ (R'000)` caption at 7.1pt, a `100` baseline
  label and R 110 / R 109 end annotations. y-axis 100 → `ceil(peak/2)*2`
  (= 112 against the 110.005 peak; measured off the reference — the
  balanced round-up-to-hundreds rule flattens a 100–110 series). The
  PRICE_GRAPH export carries a third (ECPI) series that is imported but not
  drawn. Highcharts centres the legend on the plot; the reference sits
  3.7mm right of that, hence `legend.x: 14`.
- **Page 2 is the 840 Shariah page**: FEE RATES (two rows), TIC headed
  `12 MONTHS | 36 MONTHS*` with the estimate footnote between the table and
  the TER paragraph, then the placed FOORD SHARIAH FUNDS block
  (`page2_content.shariahFunds`, top 152.8mm) and the placed footer
  (top 239.4mm, rule at that y, first text line 243.33mm). No PERFORMANCE
  FEES / PERFORMANCE FEE EXAMPLES, no monthly grid, no page-2 footnotes.
  The FEE RATES value column starts at x=111.2mm and does NOT align with
  the TIC's "12 MONTHS" break (x=136.4mm) — Paul's align-the-column-breaks
  rule does not apply to the Shariah sheets (§5n). TIC values and headers
  are CENTRED but sit ~1mm LEFT of their cell centres (carried on
  `padding-right`), and the header row is body size (7.49pt), not 6pt.
  White TIC rows are 1 and 4 (four data rows).
- **Importer.** 841 exports `PS_SA_EQUITY` — the 840 discriminator — *and*
  `PS_SA_TOTAL`, but publishes the flex-income structure, so the
  `mapShariahAssetAllocation` branch is excluded by template and 841 falls
  through to `mapPortfolioStructure`, which gets a template-scoped row list,
  1-decimal formatting and a "100.0" total. `mapMaturityBreakdown` and the
  zero-performance-charge TIC drop both gained `show-shariah-income`;
  `mapCreditExposure` gained it to `$keepDashSectors`.
- **`isUsableStat` (shared).** The 2026-07 feed exports a bare `'%'` for
  `STAT_YIELD`/`STAT_SPREAD_TO_JIBAR` and `''` for every duration — not
  `ERR` — so the old `isUsable` check would have written `'%'` into the
  published table. Statistic cells now additionally require a digit (or the
  feed's explicit `'-'` no-exposure marker). 841's stats are seeded from the
  July reference (Yield 8.60%, Spread to JIBAR 1.79%, SA duration 5.01 /
  5.01, the rest `'-'`) and preserved until the feed is fixed. **This same
  breakage is present in the 824, 825, 826 and 827 exports** — see the
  known-issues note below.
- Sidebar: FUND MANAGERS plural (Farzana Bayat and Rashaad Tayob), no
  equity indicator dots, ISIN last, category with en dashes ("South African
  — Multi Asset — Income"). Sidebar type is ~2% smaller than the
  flex-income sheet's (`.sidebar-text` 6.86pt / `.sidebar-heading` 5.88pt)
  and the section gap is 1.91mm — at 824's sizes PORTFOLIO ORIENTATION runs
  to three lines and the column drifts ~11.7mm by ISIN NUMBER. Section
  headings are 7.3pt (2.8% smaller than 824's). The published sheet's
  "INCOME DISIBUTIONS" typo is corrected, as on the other income sheets.
- Seed script: `Funds/841 - Shariah Income Fund/seed-841-shariah-income.php`
  (tinker `include`; re-runnable). Import:
  `php artisan fund:import 49 "storage/app/private/fund-data/<YYYY-MM>/841"`.
- Verification: page-1 and page-2 landmarks within ±0.7mm of the reference;
  the page-1 sidebar reproduces it line for line, and page 2's disclaimer
  column matches 85 of 87 lines (the two exceptions are Publisher
  line-breaker noise — the reference itself breaks at inconsistent widths).
  Residual VALUE differences are real: the 27-Aug export restates the July
  figures the 6-Aug sheet published — foreign cash 0.1 vs 0.0 (total cash
  8.5 vs 8.4), foreign equities 2.0 vs 2.1, rating Other 2 vs 3, TER 0.55
  vs 0.10 and total investment charge 0.57 vs 0.15 — and PUBLISHED_DATE
  reads 12 August against the sheet's 06 August. The feed also maps
  `SECTOR_RSA` (24) to "SA Government" where the published sheet shows
  "SA Corporates" 24 and "SA Government" `-`; per the client's ruling the
  existing mapping stands and the difference is flagged.

## Known issues in the shared statistics importer (found 2026-08-31)

Not caused by, and out of scope for, the 841 work, but the next monthly
import would damage published sheets:

- `mapPortfolioStatistics` picks the flex layout on
  `isset($data['STAT_SPREAD_TO_JIBAR'])` alone. The **826 bond** export
  carries that key, so re-importing fund 24/25/26 replaces the bond row set
  (Yield / Weighted average time to maturity / Total duration, with the
  BENCHMARK and RELATIVE TO ALBI columns) with the flex one and blanks
  every value.
- The **827** export carries neither `STAT_SPREAD_TO_JIBAR` nor the `SA_`
  duration keys, so fund 35 falls into the *bond* branch and would gain the
  bond row set and a RELATIVE TO ALBI header instead of its two-row
  Real Yield / Duration table.

Both need the branch to switch on `$fund->template` rather than on key
presence. Confirmed against the 2026-07 exports; no fund data was written
while verifying.

## 5q. Hassen-Shariah-global-equity specifics (fund 48 — 878 Class R)

- `template = 'show-hassen-shariah'` — cloned from `show-global-equity`
  (877 Class R), which the 878 reference matches block for block: the same
  Luxembourg sidebar, the full-width PORTFOLIO STRUCTURE % bars with the
  change + variance columns, GEOGRAPHIC EQUITY EXPOSURE beside PORTFOLIO
  PERFORMANCE VS BENCHMARK, TOP 10, the performance table, and the page-2
  cost-ratio / performance-fee / share-pricing / more-about / notes stack.
  Like the other international templates the page IS the print layout, so
  `internalPdfView` maps it to itself.
- Sidebar differences from 877: a **SHARIAH SUPERVISORY BOARD** row (Amanie
  Advisors Ltd) between MANAGEMENT COMPANY and DEPOSITARY — new
  `shariah_supervisory_board` column, added by the
  `add_shariah_supervisory_board_to_funds` migration; a singular **FUND
  MANAGER** label (Ishreth Hassen); a single INCEPTION DATE (4 January 2021,
  no `/` so the label drops the qualifier); **EIGHT** of ten equity dots
  (877 shows nine); and NUMBER OF SHARES prints the raw count (`511 025`) —
  the millions wording stays gated on `show-global-equity`.
- Page-1 content differences: the performance table drops the 10 YRS column
  and the sterling/euro rows and names the index row **Benchmark**; the
  variance column head reads `MSCI ACIWI` [sic]; the geographic chart adds a
  fifth **Other** column and runs to 60%; the performance chart is
  **linear**, not logarithmic (877 is log) — its only rules are the 100
  baseline and a vertical axis that stops where it meets it, both drawn by
  `baselinePlugin`; ticks are yearly (Jan 21 … Jan 26, `index % 12 === 1`).
- **Sector bars use a fixed scale (1.72mm per percentage point), not
  normalised to the month's largest holding.** Measuring 877's reference
  confirms the same scale there, so the normalise-to-max rule in
  `show-global-equity` is the odd one out.
- Type scale is measured per block: sidebar 7.88pt on a 9.35pt leading with
  5.0mm between sections (MARKETING COMMUNICATION 8.9pt), page-1 content
  8.03pt, section headings 7.84pt, page-2 prose 7.65pt, notes 7.59pt, cost
  table 8.01pt. Page 2's blocks each came from their own Publisher text box,
  so SHARE PRICING sets a narrower column (padding-right 4.1mm) and MORE
  ABOUT a looser 9.5pt leading — both scoped by class.
- Reference-only details: the SHARE PRICING block's `www.foord.com` is plain
  black while the MORE ABOUT and sidebar links run gold; the footer carries
  both the T and E lines (877 has T only); the footnote 5 wording is
  hyphenated ("12-month"); footnote 3 drops 877's Class B look-through
  sentence; the fee-example title is "…FOR FOORD-HASSEN SHARIAH EQUITY FUND"
  (no GLOBAL).
- **Data gap flagged to the client:** the export sends `ESAOT_RANK_11..13`
  with no item name, no change and a zero variance, so the two zero-weight
  sectors the reference prints (Communication services −0.4, Utilities −1.0)
  cannot be imported. They are seeded as
  `sector_allocation['zeroWeightSectors']` — preserved across imports
  because the factsheet importer only rewrites `['sectors']` — and their
  variances need a manual monthly update until the feed carries the rows.
- New `PriceGraphImporter` branch: the peer column is named `Fund Misc
  (3rd)` here where 877/879 use `Fund Benchmark (2nd)`. The match was
  widened to accept either; 821's `Fund Misc (1st)` in the same position
  still falls through to its own (unwritten) branch. Regression-tested in
  `ExcelImportHassenShariahTest`.
- Known cosmetic difference: the page-2 grey prose is a stack of Publisher
  text boxes of slightly different widths in the reference, so one paragraph
  wraps a word earlier there than a single CSS column can reproduce; the
  published-on line is held at the reference's y with extra leading.
- Seed script: `Funds/878 - Hassen Shariah Global Equity Fund/
  seed-878-hassen-shariah.php` (tinker `include`; re-runnable). Import:
  `php artisan fund:import 48 "storage/app/private/fund-data/<YYYY-MM>/878"`.

## 5r. Australian-feeder specifics (fund 50 — 880 Class A)

- `template = 'show-australian-feeder'` — cloned from `show-hassen-shariah`
  (878), which is itself the 877 Luxembourg layout. Like the other
  international templates the page IS the print layout, so `internalPdfView`
  maps it to itself. Page 1 keeps the whole Luxembourg grid: full-width
  PORTFOLIO STRUCTURE % bars with the change + variance columns, GEOGRAPHIC
  EQUITY EXPOSURE beside PORTFOLIO PERFORMANCE VS BENCHMARK, TOP 10, and the
  performance table.
- Sidebar differences from 878: **no** share class row, **no** equity
  indicator, and the Luxembourg management-company/depositary rows give way
  to the Australian **RESPONSIBLE ENTITY**, **CUSTODIAN** and **DISTRIBUTION
  PARTNER**, a four-line **FUND FEATURES** list and an **APIR / ARSN** row
  under the ISIN — five new columns added by the
  `add_australian_feeder_fields_to_funds` migration. BASE CURRENCY and TOTAL
  PORTFOLIO SIZE each carry a second `Master fund: …` line (the size line is
  appended by the importer from `MASTER_FUND_PORTFOLIO_SIZE`). MONTH END UNIT
  PRICE EXCL BUY/SELL SPREAD and NUMBER OF UNITS replace the share wording;
  the label carries a zero-width space after the slash so it breaks where the
  reference does. The sidebar closes with the **Zenith APPROVED** mark
  (`public/images/zenith-approved.png`, extracted from the reference).
- Everything on this sheet is quoted in Australian dollars: the importer
  prefixes `A$` to the portfolio size and the unit price (the feed exports a
  bare `$`) for this template only.
- Page-1 content differences: the variance head reads `MSCI ACWI` with
  footnote 5 (878 prints `MSCI ACIWI`⁶ [sic]); the Cash bar is naartjie like
  every other bar (877/878 pick it out in navy); sector bars run at a fixed
  **1.73mm per percentage point**; the geographic chart has four columns and
  runs to 70%; the performance chart is linear with 24-monthly ticks
  (Apr 13 … Apr 25) and all three legend keys on one line; the TOP 10 block is
  set ~6% smaller than the rest of the page (its own Publisher text box); and
  the performance table gains a bold "Past performance is not a guide to
  future performance" line plus a ninth **SINCE 11 AUG 22** column, with no
  highest/lowest rows.
- **The two inception columns need two sources.** SINCE INCEPTION runs from
  the master fund's launch (2 April 2013, where the price series starts) and
  SINCE 11 AUG 22 from the feeder class's own launch. The factsheet export
  carries only one of each — `FOORD_I_TO_D` is the fund's return since the
  class launched, while `FOORD_COMP_n_I_TO_D` run from the start of the
  comparators' series. `FactsheetImporter` moves the fund's figure into
  `sinceClassInception`, and `PriceGraphImporter` annualises the two missing
  corners off the indexed series (the fund's over the full span, the
  comparators' from the month-end before the class launched). Both reproduce
  the reference exactly: fund 9.3, benchmark 16.8.
- Page 2 drops the ANNUALISED COST RATIO, PERFORMANCE FEES and PERFORMANCE
  FEE EXAMPLES blocks entirely — it carries only UNIT PRICING AND
  TRANSACTIONS (the `page2Content.sharePricing` slot, retitled), MORE ABOUT
  THE FUND, NOTES and the footer, which prints the T, E and website lines.
  The column starts at y=26.87mm.
- Feed quirks: every `GLOBAL_TER_*` cell exports the error marker `ER9>>` and
  the ISIN column exports `0`. Both are now ignored rather than imported as
  0.00 / "0" — the registration codes are seeded statics. The price graph's
  peer column is named `Fund Benchmark (4th)`, a third spelling alongside
  877/879's `(2nd)` and 878's `Fund Misc (3rd)`.
- Known cosmetic difference: one page-2 sidebar paragraph wraps a word
  earlier in the reference than any single CSS measure reproduces (the two
  candidate lines are 0.03mm apart), so its last eight lines sit 3.2mm high;
  the paragraph gaps below it are set to hold every following block at the
  reference's y. Same class of issue as 878.
- Verification: page-1 landmarks 2 of 181 over ±0.5mm (both horizontal,
  ≤0.66mm); page-2 landmarks 11 of 77, all inside that one paragraph. Pixel
  diff at 150dpi: p1 11.1%, p2 10.2%.
- Reference: `Funds/880 - Global Equity Australian Fund/Design/… (reference).pdf`
  (the client's live sheet); the generated deliverable sits at the folder root.
  Seed script: `Funds/880 - Global Equity Australian Fund/
  seed-880-australian-feeder.php` (tinker `include`; re-runnable). Import:
  `php artisan fund:import 50 "storage/app/private/fund-data/<YYYY-MM>/880"`.

## 5s. Asia-ex-Japan specifics (funds 51/52 — 879 Classes R/R1)

- `template = 'show-asia-ex-japan'` — cloned from `show-hassen-shariah` (878),
  which supplies the Luxembourg sub-fund chrome: sidebar, banner, footer, and
  the page-2 cost-ratio / share-pricing / more-about / performance-fee stack.
  Page 1's grid differs from 878 because 879 is not Shariah-compliant and
  reports a **geographic pie**, not 878's bar-based geographic exposure:
  row 1 is PORTFOLIO STRUCTURE % beside a two-column TOP 10 INVESTMENTS table
  (878 runs PORTFOLIO STRUCTURE % full width with change + variance columns);
  row 2 pairs a GEOGRAPHIC COUNTRY EXPOSURE **pie** beside PORTFOLIO
  PERFORMANCE VS BENCHMARK, where 878 pairs a bar-based GEOGRAPHIC EQUITY
  EXPOSURE in the same slot. Like the other international templates the page
  IS the print layout, so `internalPdfView` maps it to itself.
- **The pie.** Chart.js draws it starting at 12 o'clock, clockwise, in the
  feed's own rank order (already descending, "Other" catch-all last), with a
  **positional** palette — `sliceColours = ['#d25347', '#29363d', '#cccccc',
  '#7a9cb4', '#535353', '#e2cea4', '#bfc3c5']` assigns by array index, not by
  country name, so the same colour lands on a different country if the rank
  order shifts month to month. Labels are drawn outside the pie by a custom
  `pieOutsideLabels` plugin (Chart.js has no built-in outside-label support)
  in grey, radially offset `outerRadius + 12`, with no leader lines; a
  greedy word-wrap at `LABEL_MAX_WIDTH = 62` (canvas px) reproduces the
  reference's wrap points for the two genuinely long labels ("Taiwan,
  Province of China…", "United States of America…") but not for two shorter
  ones ("Other 14.3%", "Hong Kong 4.9%") that the reference also wraps —
  Publisher's outside-label boxes were hand-placed per label there, not
  wrapped by a formula, so no single width value reproduces every line
  break; accepted as a cosmetic gap, same class of issue as 878's page-2
  paragraph wrap (§5q).
  **Bug already fixed, worth recording so it isn't reintroduced:** Task 7's
  first pass set `rotation: -90`, on the (wrong) assumption Chart.js pies
  start at 3 o'clock like some other charting libraries. Chart.js pie/
  doughnut charts already start at 12 o'clock and draw clockwise by
  default — `rotation: -90` rotated the whole pie a further quarter-turn
  anticlockwise, putting "China" at 9 o'clock instead of 12. Fixed to
  `rotation: 0` in review; a code comment at the call site says so directly.
  **Geometry, measured at Task 10:** flood-filling each slice's own fill
  colour at 200dpi and taking the extremes that each slice's angular span is
  guaranteed to cross — China/red gives the disk's top and right edges (its
  arc runs 0°→172°, crossing 90°), the next slice/navy gives the bottom
  (crossing 180°), and the slice that straddles 270° gives the left edge —
  the reference disk measures 280×279px (35.5×35.4mm) and this render
  35.7mm, a sub-pixel difference. The pie matches; `layout.padding` (`{top:
  16, bottom: 16, left: 68, right: 59}`) is unchanged from Task 7/9.
- **The performance chart is linear, not logarithmic** like the sibling 877
  (878 is also linear). Task 8 fitted both scales against the reference's
  pixel positions: a linear axis is 0.9px off at the 100 baseline and 4.9px
  off at the benchmark line's end point; a logarithmic axis is 19.7px off at
  that same end point — linear is the clear fit, and 877 being logarithmic
  is never assumed to carry over to a sibling.
- **`LUX_TEMPLATES` split**, in `FactsheetImporter`, into three constants
  because 879 doesn't belong to every Luxembourg-sheet behaviour at once:
  - `PUBLISHED_LINE_NO_STOP_TEMPLATES = ['show-global-equity',
    'show-hassen-shariah']` — gates the published-line suffix (`''` vs
    `'.'`). 879 is deliberately absent, so it gets the trailing full stop.
  - `PERFORMANCE_COMPONENT_ROW_TEMPLATES = ['show-global-equity',
    'show-hassen-shariah', 'show-asia-ex-japan']` — gates whether the
    indented `— Performance` row is emitted under TER — Basic (still also
    guarded by `$performance12 !== null`). 879 is included: its reference
    prints the row.
  - `COST_TABLE_LABELS` (a map keyed by template, currently only
    `show-asia-ex-japan`) plus two flat fallbacks, `COST_TABLE_LABELS_LUX`
    and `COST_TABLE_LABELS_DEFAULT`, gate the `transactionCosts` and
    `total` row labels. Lookup order: exact-template override (879) → LUX
    set (877/878: `'Transaction costs'` / `'Total investment charge'`) →
    default (`'Transaction costs'` / `'Total cost ratio'`) for every other
    template, including siblings not yet given their own override.
- **"Property" stays "Property."** The sector name arrives from the feed
  verbatim and 879 is deliberately left out of
  `PORTFOLIO_STRUCTURE_TEMPLATES` (`['show-global-equity',
  'show-hassen-shariah', 'show-prescient-global-equity',
  'show-australian-feeder']`), the only list that triggers the
  Property → "Real estate" rename. Do not add 879 to that array.
- **Footnotes render on page 1 here**, directly under the performance
  table — unlike 878, which prints them on page 2 under a NOTES heading.
  This matches the 879 reference layout exactly; it is not a shared
  page-1/page-2 toggle, just where each reference happens to put the block.
- **The TER explanatory paragraph renders in the page-2 main column**,
  beneath the ANNUALISED COST RATIO table, not in the page-2 sidebar where
  878's clone puts it. The text itself must stay the last entry of
  `importantInfo.paragraphs` (`$fund->important_info_paragraphs` on the
  model) — the sidebar loop explicitly skips rendering it there — because
  `FactsheetImporter::updateTerFootnote()` finds and rewrites the
  audited-TER percentage inside that same array on every monthly import.
  Moving the paragraph's *content* out of `importantInfo.paragraphs` (e.g.
  into its own data key) would silently stop the percentage refreshing.
- Sector bars use the 878-style fixed scale, not normalised to the month's
  largest holding: `$psBarScale = 0.61` mm per percentage point with a
  `min(18.5, …)` clamp. Verified at Task 10 by pixel-scanning all ten
  reference bars at 200dpi (common 773px left edge) and least-squares
  fitting width against percentage through the origin: 0.612mm/pt, so 0.61
  stands. Per-row mm/pt ranges 0.59–0.70 — normal jitter for a hand-drawn
  Publisher chart, not evidence of a different scale. The clamp doesn't
  bind at the largest bar (Consumer discretionary, 30% → 18.3mm, under the
  18.5mm cap) in either the reference or this render.
- Banner class suffix: `.fund-banner h1 .class-suffix` is 15pt (down from an
  18-carried-forward-then-16pt placeholder). Measured by cap-height pixel
  ratio at 200dpi: the title "FOORD ASIA EX-JAPAN FUND" stands 46px tall at
  its coded 23pt (2.0px/pt); the reference suffix "CLASS R" stands 30px
  tall, giving a target of 30 ÷ 2.0 = 15pt. At 15pt this render's suffix
  also measures 30px tall — an exact pixel match, for both classes (R and
  R1 differ only in the suffix text, not its size).
- **Known feed-vs-reference gaps, flagged to the client, not bugs:**
  fund size prints `$192.8 million` against the reference's `$192.7 million`
  (a rounding difference on the same underlying figure); the whole Peer
  group row and its chart end-value differ ($130,422 / 5.4 / 17.4 / 36.3 /
  11.4 / 4.6 / 20.6 / -4.9 here vs the reference's $127,461 / 5.0 / 16.5 /
  33.2 / 8.8 / 2.2 / 17.8 / -7.0 — the 27 July export restates the figures
  the reference's sheet published); geographic country percentages
  disagree across the board (China 44.3 vs 47.9, United States 14.4 vs 7.2,
  Taiwan 9.7 vs 10.5, Korea 8.7 vs 9.4, Singapore 5.4 vs 5.8, Hong Kong 4.5
  vs 4.9, Other 13.0 vs 14.3 — same restatement pattern); and Class R's
  audited TER prints 1.05% against the reference's 0.97%. None of these are
  importer or template defects — the July export simply restates numbers
  the reference's sheet published from an earlier cut of the same data.
- **Caution for a future feed with an unusable rank.** `mapGeographicExposure()`
  skips any `GEO_COUNTRY_RANK_n` whose `_ITEM` is empty or whose `_CURRENT`
  fails `isUsable()` (e.g. `'ERR'` or `'-'`), and the pie is drawn only from
  the ranks that survive. No 879 feed seen so far has actually hit this —
  every rank exported so far has been usable — but if one ever does, the
  skipped country's percentage is simply dropped rather than redistributed:
  Chart.js still draws the remaining slices as a full 360° circle (so their
  *angles* are silently renormalised against a total that no longer sums to
  100), while `pieOutsideLabels` keeps printing each slice's own feed
  percentage verbatim next to it. The result is a chart whose geometry
  (slice angles, renormalised) contradicts its own labels (unrenormalised
  feed percentages) — cosmetically fine per-slice, but the disk as a whole
  no longer visually represents 100%. Whoever maintains this template next
  should watch for that if a rank ever goes unusable in a live import.
- Seed script: `Funds/879 - Asia ex-Japan Fund/seed-879-asia-ex-japan.php`
  (tinker `include`; re-runnable; loops both classes since they share every
  static field except ISIN, the FEES block, and the audited-TER percentage).
  Import: `php artisan fund:import 51|52
  "storage/app/private/fund-data/<YYYY-MM>/879"`.

## 7. Verify

```bash
php artisan test          # includes importer + sector-allocation coverage
vendor/bin/phpstan analyse
```
