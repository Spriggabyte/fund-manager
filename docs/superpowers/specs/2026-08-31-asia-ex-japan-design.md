# Foord Asia ex-Japan Fund (879 Classes R / R1) — design

Status: approved in chat 2026-08-31. Implementation follows the
FUND-ONBOARDING.md process; this document records the decisions that are
specific to 879 and the places where 879 does not fit the existing
Luxembourg abstractions.

## 1. Scope

Onboard two new fact sheets — 879 Class R and Class R1 — rendered from a new
`show-asia-ex-japan` page template and fed by the July 2026 exports in
`storage/app/private/fund-data/2026-07/879/`.

New fund records: **51** (879 / R) and **52** (879 / R1). Fund 50 is the 880
Australian feeder, added by a concurrent session; the next free
FUND-ONBOARDING section letter is **§5s** (§5r is the Australian feeder).

## 2. Base template

`show-asia-ex-japan` is cloned from **`show-hassen-shariah`** (878 Class R),
not from fund 9's balanced `show`.

The user framed the request as "based off fund 9", but the 879 live PDFs are
the Luxembourg sub-fund sheet: navy date badge on a red banner (fund 9 is the
inverse), a `MARKETING COMMUNICATION` Luxembourg sidebar ending in an ISIN,
navy table headers, and the page-2 ANNUALISED COST RATIO / PERFORMANCE FEES /
PERFORMANCE FEE EXAMPLES / SHARE PRICING / MORE ABOUT THE FUND stack that the
balanced sheet does not have at all. 878 matches all of that block for block.
Confirmed with the user before starting.

Like the other international templates the page **is** the print layout, so
`internalPdfView` maps `show-asia-ex-japan` to itself.

## 3. Page-1 grid

878 today | 879
--- | ---
PORTFOLIO STRUCTURE %, full width, with change + variance columns | PORTFOLIO STRUCTURE % (narrow, change only) **beside** TOP 10 INVESTMENTS (2 columns)
GEOGRAPHIC EQUITY EXPOSURE grouped bars ∥ PORTFOLIO PERFORMANCE VS BENCHMARK | GEOGRAPHIC COUNTRY EXPOSURE **pie** ∥ PORTFOLIO PERFORMANCE VS BENCHMARK
TOP 10 INVESTMENTS full width, 4 columns | *(none — moved to row 1)*
Performance table: CASH VALUE / SINCE INCEPTION / 5 YRS / 3 YRS / 1 YR / YTD / THIS MONTH | CASH VALUE / SINCE INCEPTION / **3 YRS / 1 YR / 6 MTHS / 3 MTHS** / YTD / THIS MONTH

### Sidebar

Drops 878's `SHARE CLASS R` row entirely — 879 carries the class in the banner
("FOORD ASIA EX-JAPAN FUND — CLASS R") instead, which 877/878 do not.

Relabelled rows: `INITIAL SUBSCRIPTION AMOUNT` (877/878: MINIMUM SUBSCRIPTION
AMOUNT), `FUND SIZE` (TOTAL PORTFOLIO SIZE), `FEES` with no class suffix
(FEES (CLASS R)). `FUND MANAGERS` is plural (Ishreth Hassen and Jing Cong Xue).
Single `INCEPTION DATE` (27 July 2021) so the `(FUND / CLASS)` qualifier drops,
as it already does when the stored date has no `/`.

Equity indicator: **nine of ten dots**, off-dot solid grey. This follows the
879 reference. Per the standing note on hollow off-dots, new clones ship solid
grey following their own reference and are not "fixed" on the strength of the
June amend alone.

### Performance-table rows

Renamed and ordered at display time, as 877/878 do: `Fund³` /
`MSCI Asia ex-Japan` / `Peer group⁴` (COMP_2), a blank spacer row, then
`Fund highest³ˑ⁵` / `Fund lowest³ˑ⁵`.

## 4. GEOGRAPHIC COUNTRY EXPOSURE — the new pie

The app has no pie in any Chart.js template, so this is genuinely new drawing
code.

**Library choice: Chart.js, not Highcharts.** `show-absolute` (816) does have a
working sector pie with outside labels, but it is Highcharts, loaded from a CDN
and placed by a hand-written renderer with known `getBBox`/`center` traps.
Borrowing it would mean a second charting library on a page whose performance
chart is already Chart.js, and a second readiness signal for
`PuppeteerPdfService` to wait on. The 879 pie is seven slices with no leader
lines, so the outside-label plugin is short; keeping one library on the page is
worth more than reusing that renderer.

Geometry and palette were measured off the 300dpi reference raster rather than
guessed:

- centre ⌀ **35.4mm** (radius 209px at 300dpi)
- **starts at 12 o'clock and runs clockwise**, in feed rank order — RANK_1 …
  RANK_6, then `RANK_7+` ("Other") last. Verified by solving the arc: the
  China slice's terminating edge sits at 172.1° clockwise against a computed
  sweep of 172.4°, i.e. a start angle of 0°.
- slice colours by index, sampled at two radii per slice and exact to the byte:

  index | colour | 879 July slice
  --- | --- | ---
  1 | `#D25347` | China
  2 | `#29363D` | Taiwan
  3 | `#CCCCCC` | Korea
  4 | `#7A9CB4` | United States of America
  5 | `#535353` | Singapore
  6 | `#E2CEA4` | Hong Kong
  7 / Other | `#BFC3C5` | Other

  The palette is positional, not per-country: the feed already ranks
  descending with Other pinned last, so the same index order reproduces the
  reference. Colours cycle if a future month sends more than seven slices.
- external grey `Name %` labels, **no leader lines**, white separators between
  slices.

Chart.js has no outside-label support, so labels are drawn by a custom plugin
registered the same way as the existing `baselinePlugin` — and, per the 823
trap, **scoped to this chart instance only**, never via a global
`Chart.register`, which would annotate every other chart on the page.

## 5. PORTFOLIO PERFORMANCE VS BENCHMARK

Three series (fund / benchmark / peerGroup) from `chart_data['performanceData']`.

**Linear scale, not logarithmic.** Fitted against the reference raster using
the two independently known end values (fund 131.68, benchmark 152.47) and the
measured 100 baseline: linear puts the baseline 0.9px out and the benchmark
endpoint 4.9px out; logarithmic misses the benchmark endpoint by 19.7px. This
matches 878 and differs from 877, which is logarithmic — the sibling's scale
type is never assumed.

Yearly x ticks (Jul 21 … Jul 26); end-value labels at the right, coloured per
series; the only rules are the 100 baseline and a vertical axis stopping where
it meets it, both from `baselinePlugin`, with `border.display: false` on both
scales.

## 6. Importer changes

### 6.1 New geographic-country branch

`FactsheetImporter::mapGeographicCountryExposure()`, fired by the presence of
`GEO_COUNTRY_RANK_1_ITEM`. Reads `GEO_COUNTRY_RANK_{1..6}_{ITEM,CURRENT}` plus
`GEO_COUNTRY_RANK_7+_{ITEM,CURRENT}` and writes
`asset_allocation['geographicCountryExposure']`. It runs **before** the
`GEO_EXP_*` branches and returns early, so the existing 875/877/878 paths are
untouched.

Absent keys leave the branch unentered and the previously stored slices
standing, matching how every other block survives a partial export.

### 6.2 Splitting `LUX_TEMPLATES`

`LUX_TEMPLATES` currently gates three unrelated behaviours, and **879 splits
every one of them**:

behaviour | 877 / 878 | 879
--- | --- | ---
published line full stop | omitted | **present** ("Published on 05 August 2026.")
indented `— Performance` cost row | present | **present** (0.00 / -0.10)
total row label | `Total investment charge` | **`Total cost ratio`**
transaction-costs row label | `Transaction costs` | **`Transaction costs (incl VAT)`**

So the constant is replaced by purpose-named ones rather than adding 879 to it:
a no-full-stop list (877/878 only), a performance-component-row list (877, 878,
879), and per-template labels for the total and transaction-costs rows. This is
a targeted improvement to code the work touches, not a general refactor — the
existing 877/878/875 behaviour is unchanged by construction and covered by
their tests.

### 6.3 No change needed

- `PriceGraphImporter` already routes both classes: column D carries
  `MSCI ASIA USD` and column E `879 Fund Benchmark (2nd) [AXJPG]`, which the
  three-series branch matches. Class R1's column C header is
  `879 Fund Price (2nd)` — non-empty, so `$fundCol` still resolves. Covered by
  a regression test rather than a code change.
- `mapTopInvestments` already handles a feed with no sector/market columns;
  those keys arrive empty and the template simply renders two columns.

### 6.4 Deliberately not applied

`show-asia-ex-japan` is **not** added to `PORTFOLIO_STRUCTURE_TEMPLATES`. That
list renames the feed's "Property" sector to "Real estate"; the 879 reference
prints **"Property"**.

## 7. Page 2

Structurally 878's page. Content differences from the 879 reference: SRRI
**5 of 7, medium-high** (878 is 4 of 7, medium) and no Shariah-criteria
sentence; dealing cut-off **08h00** Central European time (878: 16h00);
fee-example title `PERFORMANCE FEE EXAMPLES FOR FOORD ASIA EX-JAPAN`; the cost
table labels in §6.2; both footer T and E lines.

## 8. Data flow

```
seed-879-asia-ex-japan.php   (statics, both classes, re-runnable)
        ↓
php artisan fund:import 51|52 "storage/app/private/fund-data/2026-07/879"
        ↓
/funds/51, /funds/52          (show-asia-ex-japan)
        ↓
internalPdfView → itself → PuppeteerPdfService
```

## 9. Known feed-vs-reference gaps (flagged, not corrected)

Per the user's decision, the feed is imported as-is and the discrepancies are
reported rather than papered over with seeded values.

1. **Geographic country split.** The export disagrees with the signed-off PDF
   on every slice and carries its own comment on `GEO_COUNTRY_RANK_1_CURRENT`:
   *"Dont agree with July figures, must ask Helena"*. Feed: China 44.3 /
   USA 14.4 / Taiwan 9.7 / Korea 8.7 / Singapore 5.4 / Hong Kong 4.5 /
   Other 13.0. PDF: 47.9 / 7.2 / 10.5 / 9.4 / 5.8 / 4.9 / 14.3. Both total
   100.0. **Consequence for verification:** the rendered pie cannot be checked
   against the reference by value — slice geometry, order, palette and label
   placement are verified instead.
2. **Peer group row and chart line.** Feed cash value $130,422 against the
   PDF's $127,461, and every period differs (e.g. 1 YR 36.3 vs 33.2). The Fund
   and MSCI rows match the reference exactly, so this is isolated to the
   Morningstar peer series, which the sheet itself marks provisional.
3. **`PORTFOLIO_SIZE`** exports `192.8 million` where the PDF prints
   `$192.7 million`.

## 10. Testing

- `tests/Feature/ExcelImportAsiaExJapanTest.php` — the geographic-country
  branch (including `RANK_7+`), the two-column top 10, the performance-table
  column set, "Property" left unrenamed, and the three-series price graph for
  **both** R and R1 (the `Fund Price (2nd)` column-C header).
- `tests/Feature/FundTemplateSelectionTest.php` — template resolves to its view
  and to itself as the PDF view.
- Regression: the existing 877/878/823/821 importer tests must stay green
  across the `LUX_TEMPLATES` split and the new geo branch.
- `php artisan test` and `vendor/bin/phpstan analyse`.
- Visual: `pdftoppm` at 150dpi against `Design/… (reference).pdf`, with the
  bbox-measurement loop for type scale and block positions.

## 11. Files

Reference PDFs move to `Funds/879 - Asia ex-Japan Fund/Design/… (reference).pdf`;
generated deliverables land at the folder root, matching 877/878.

New: `resources/views/funds/show-asia-ex-japan.blade.php`,
`Funds/879 - Asia ex-Japan Fund/seed-879-asia-ex-japan.php`,
`tests/Feature/ExcelImportAsiaExJapanTest.php`.

Edited: `app/Models/Fund.php`, `app/Http/Controllers/FundController.php`
(allowed templates + `internalPdfView`), `app/Http/Requests/StoreFundRequest.php`,
`app/Services/FundImport/FactsheetImporter.php`,
`resources/views/funds/edit.blade.php`, `FUND-ONBOARDING.md` (new §5s).

**Concurrency note:** a second session is editing the same registration files.
Every edit is a targeted string replace, never a file rewrite, and the shared
files are re-grepped before each change.
