# Foord Asia ex-Japan Fund (879 R / R1) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Publish the Foord Asia ex-Japan Fund fact sheet for Classes R and R1 from a new `show-asia-ex-japan` page template fed by the July 2026 exports.

**Architecture:** A new Blade page template cloned from `show-hassen-shariah` (878 Class R), which is the same Luxembourg sub-fund sheet block for block. The page IS the print layout, so `internalPdfView` maps it to itself. Page 1 rearranges into two two-column rows and gains a Chart.js pie fed by a new `FactsheetImporter` branch; page 2 is 878's with different copy and cost-table labels.

**Tech Stack:** Laravel 11 + Blade + Alpine.js, Chart.js (already loaded by this template family), PhpSpreadsheet for the Excel importers, PHPUnit, PHPStan, Puppeteer for PDF export.

**Spec:** `docs/superpowers/specs/2026-08-31-asia-ex-japan-design.md`

## Global Constraints

- **This repo is NOT a git repository.** There is nothing to commit. Every task ends with a **verification step** instead of a commit. Do not run `git init`.
- **A second Claude session is editing the same registration files** (it added `show-australian-feeder` mid-flight). Before every edit to a shared file, re-`grep` it. Make every change a **targeted string replace, never a file rewrite**. If a string does not match, re-read the file — do not assume your copy is current.
- New template name, exact: `show-asia-ex-japan`.
- New fund records: **51** = fund_code `879`, class_code `R`; **52** = fund_code `879`, class_code `R1`.
- FUND-ONBOARDING.md section letter: **§5s** (§5r is the 880 Australian feeder). Re-check the section index before writing; if `5s` is taken, use the next free letter.
- Reference PDFs are the two files currently at `Funds/879 - Asia ex-Japan Fund/`. They move to `Design/` with a ` (reference)` suffix. Generated deliverables land at the folder root.
- Currency is US dollars (`$`), never `A$` and never `R`.
- Sector name from the feed is published verbatim: **"Property"**, NOT "Real estate".
- Run tests with `php artisan test`, static analysis with `vendor/bin/phpstan analyse`. Both must be green before a task is considered done.

---

### Task 1: Register the `show-asia-ex-japan` template

Creates the template as an exact copy of `show-hassen-shariah` and wires it through every registration point, so later tasks have a page to edit and a route to look at. No visual changes yet.

**Files:**
- Create: `resources/views/funds/show-asia-ex-japan.blade.php` (copy of `show-hassen-shariah.blade.php`)
- Modify: `app/Models/Fund.php`
- Modify: `app/Http/Controllers/FundController.php` (`ALLOWED_TEMPLATES` and `internalPdfView`)
- Modify: `app/Http/Requests/StoreFundRequest.php`
- Modify: `resources/views/funds/edit.blade.php`
- Test: `tests/Feature/FundTemplateSelectionTest.php`

**Interfaces:**
- Consumes: nothing.
- Produces: the template string `'show-asia-ex-japan'`; the view `funds.show-asia-ex-japan`; `Fund::GLOBAL_EQUITY_TEMPLATES` including it.

- [ ] **Step 1: Write the failing tests**

Append to `tests/Feature/FundTemplateSelectionTest.php`, inside the class:

```php
    public function test_internal_pdf_view_uses_asia_ex_japan_page_template(): void
    {
        // The 879 sheet follows the Luxembourg pattern: the page IS the print
        // layout, so there is no separate pdf-* template.
        $fund = Fund::factory()->create(['template' => 'show-asia-ex-japan']);

        $view = (new FundController)->internalPdfView($fund);

        $this->assertSame('funds.show-asia-ex-japan', $view->name());
    }

    public function test_show_renders_asia_ex_japan_template(): void
    {
        $user = User::factory()->create();
        $fund = Fund::factory()->for($user)->create(['template' => 'show-asia-ex-japan']);

        $this->actingAs($user)->get(route('funds.show', $fund))
            ->assertOk()
            ->assertViewIs('funds.show-asia-ex-japan');
    }
```

- [ ] **Step 2: Run the tests to verify they fail**

Run: `php artisan test --filter=asia_ex_japan`
Expected: FAIL — `internalPdfView` returns `funds.show` (the fallback) and the show route does not resolve the view.

- [ ] **Step 3: Create the template file**

```bash
cp resources/views/funds/show-hassen-shariah.blade.php \
   resources/views/funds/show-asia-ex-japan.blade.php
```

- [ ] **Step 4: Register the template in the four PHP files**

Re-grep each file first (a concurrent session edits them), then apply targeted replaces.

`app/Models/Fund.php` — add to the constant:

```php
    public const GLOBAL_EQUITY_TEMPLATES = ['show-global-equity', 'show-hassen-shariah', 'show-australian-feeder', 'show-asia-ex-japan'];
```

`app/Http/Controllers/FundController.php` — append `, 'show-asia-ex-japan'` to the end of the `ALLOWED_TEMPLATES` array, and add to the `internalPdfView` match arm list next to the sibling line:

```php
            'show-asia-ex-japan' => 'show-asia-ex-japan',
```

`app/Http/Requests/StoreFundRequest.php` — append `,show-asia-ex-japan` inside the `in:` rule string.

`resources/views/funds/edit.blade.php` — append to the `@foreach` map:

```php
'show-asia-ex-japan' => 'Asia ex-Japan'
```

- [ ] **Step 5: Run the tests to verify they pass**

Run: `php artisan test --filter=FundTemplateSelectionTest`
Expected: PASS, including the pre-existing cases for the other templates.

- [ ] **Step 6: Verify**

Run: `php artisan test && vendor/bin/phpstan analyse`
Expected: whole suite green. Confirm with `grep -c "show-asia-ex-japan" app/Models/Fund.php app/Http/Controllers/FundController.php app/Http/Requests/StoreFundRequest.php resources/views/funds/edit.blade.php` that each file has at least one hit (FundController has 2) — this catches an edit lost to the concurrent session.

---

### Task 2: Geographic country exposure importer branch

The 879 feed carries `GEO_COUNTRY_RANK_*` keys that no importer reads. This adds the branch that feeds the pie.

**Files:**
- Modify: `app/Services/FundImport/FactsheetImporter.php` (`mapGeographicExposure`, around line 851)
- Test: `tests/Feature/ExcelImportAsiaExJapanTest.php` (create)

**Interfaces:**
- Consumes: `'show-asia-ex-japan'` from Task 1.
- Produces: `asset_allocation['geographicCountryExposure']` — a list of `['name' => string, 'value' => float]` in feed rank order, "Other" last. Task 7 renders it.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/ExcelImportAsiaExJapanTest.php`:

```php
<?php

namespace Tests\Feature;

use App\Models\Fund;
use App\Services\FundImport\FactsheetImporter;
use App\Services\FundImport\PriceGraphImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

/**
 * Foord Asia ex-Japan Fund (879, funds 51/52).
 *
 * Covers what the 879 feed does that no earlier sheet does: the
 * GEO_COUNTRY_RANK_* country split behind the pie, a top 10 with no sector
 * or market columns, the "Property" sector published verbatim, and a price
 * graph whose Class R1 fund column is headed "Fund Price (2nd)".
 */
class ExcelImportAsiaExJapanTest extends TestCase
{
    use RefreshDatabase;

    private array $tempFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->tempFiles as $file) {
            @unlink($file);
        }
        parent::tearDown();
    }

    private function makeXlsx(array $rows, string $name): string
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Set');
        $sheet->fromArray($rows, null, 'A1', true);

        $path = sys_get_temp_dir()."/{$name}.xlsx";
        (new Xlsx($spreadsheet))->save($path);
        $this->tempFiles[] = $path;

        return $path;
    }

    /**
     * The pie draws slices in feed rank order with the "RANK_7+" catch-all
     * pinned last, which is what reproduces the reference's clockwise order.
     */
    public function test_geographic_country_exposure_keeps_feed_rank_order_with_other_last(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-asia-ex-japan']);

        $path = $this->makeXlsx([
            ['Code', 'Value'],
            ['GEO_COUNTRY_RANK_1_ITEM', 'China'],
            ['GEO_COUNTRY_RANK_1_CURRENT', '44.3'],
            ['GEO_COUNTRY_RANK_2_ITEM', 'United States of America'],
            ['GEO_COUNTRY_RANK_2_CURRENT', '14.4'],
            ['GEO_COUNTRY_RANK_3_ITEM', 'Taiwan, Province of China'],
            ['GEO_COUNTRY_RANK_3_CURRENT', '9.7'],
            ['GEO_COUNTRY_RANK_4_ITEM', 'Korea'],
            ['GEO_COUNTRY_RANK_4_CURRENT', '8.7'],
            ['GEO_COUNTRY_RANK_5_ITEM', 'Singapore'],
            ['GEO_COUNTRY_RANK_5_CURRENT', '5.4'],
            ['GEO_COUNTRY_RANK_6_ITEM', 'Hong Kong'],
            ['GEO_COUNTRY_RANK_6_CURRENT', '4.5'],
            ['GEO_COUNTRY_RANK_7+_ITEM', 'Other'],
            ['GEO_COUNTRY_RANK_7+_CURRENT', '13.0'],
        ], 'asia_geo_country');
        (new FactsheetImporter)->import($fund, $path);
        $fund->save();

        $slices = $fund->fresh()->asset_allocation['geographicCountryExposure'];

        $this->assertSame(
            ['China', 'United States of America', 'Taiwan, Province of China', 'Korea', 'Singapore', 'Hong Kong', 'Other'],
            array_column($slices, 'name')
        );
        $this->assertEquals(44.3, $slices[0]['value']);
        $this->assertEquals(13.0, $slices[6]['value']);
    }

    /**
     * A month that reports fewer countries must not leave empty slices
     * behind, and the "Other" row is optional.
     */
    public function test_geographic_country_exposure_skips_unreported_ranks(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-asia-ex-japan']);

        $path = $this->makeXlsx([
            ['Code', 'Value'],
            ['GEO_COUNTRY_RANK_1_ITEM', 'China'],
            ['GEO_COUNTRY_RANK_1_CURRENT', '52.0'],
            ['GEO_COUNTRY_RANK_2_ITEM', 'Korea'],
            ['GEO_COUNTRY_RANK_2_CURRENT', '48.0'],
            ['GEO_COUNTRY_RANK_3_ITEM', ''],
            ['GEO_COUNTRY_RANK_3_CURRENT', '-'],
        ], 'asia_geo_country_short');
        (new FactsheetImporter)->import($fund, $path);
        $fund->save();

        $this->assertSame(
            ['China', 'Korea'],
            array_column($fund->fresh()->asset_allocation['geographicCountryExposure'], 'name')
        );
    }

    /**
     * Regression: the country branch must not swallow the 877/878 region
     * chart, which is keyed on GEO_EXP_*.
     */
    public function test_region_chart_is_untouched_by_the_country_branch(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-hassen-shariah']);

        $path = $this->makeXlsx([
            ['Code', 'Value'],
            ['GEO_EXP_US_EQTY', '38'],
            ['GEO_EXP_ASIA_EM_EQTY', '25'],
            ['GEO_EXP_EUR_EQTY', '29'],
            ['GEO_EXP_PAC_EQTY', '7'],
            ['GEO_EXP_US_EQTY_BM', '57'],
            ['GEO_EXP_ASIA_EM_EQTY_BM', '12'],
            ['GEO_EXP_EUR_EQTY_BM', '19'],
            ['GEO_EXP_PAC_EQTY_BM', '9'],
        ], 'asia_geo_region_regression');
        (new FactsheetImporter)->import($fund, $path);
        $fund->save();

        $allocation = $fund->fresh()->asset_allocation;

        $this->assertSame(
            ['North America', 'EM Asia', 'Europe', 'Pacific'],
            array_column($allocation['geographicEquityExposure'], 'name')
        );
        $this->assertArrayNotHasKey('geographicCountryExposure', $allocation);
    }
}
```

- [ ] **Step 2: Run the tests to verify they fail**

Run: `php artisan test --filter=ExcelImportAsiaExJapanTest`
Expected: the first two FAIL with "Undefined array key geographicCountryExposure"; the third PASSES already.

- [ ] **Step 3: Add the branch**

In `app/Services/FundImport/FactsheetImporter.php`, insert this as the **first statement inside `mapGeographicExposure()`**, before the `GEO_EXP_US_EQTY_BM` block:

```php
        // The 879 feed reports a country split (GEO_COUNTRY_RANK_n_ITEM /
        // _CURRENT, with a "RANK_7+" catch-all) rather than the region
        // exposure the other international sheets carry. It feeds the
        // GEOGRAPHIC COUNTRY EXPOSURE pie, whose slices are drawn in this
        // order — the feed already ranks descending with the catch-all last.
        if (isset($data['GEO_COUNTRY_RANK_1_ITEM'])) {
            $slices = [];
            foreach ([...range(1, 6), '7+'] as $rank) {
                $item = $data["GEO_COUNTRY_RANK_{$rank}_ITEM"] ?? null;
                $value = $data["GEO_COUNTRY_RANK_{$rank}_CURRENT"] ?? null;
                if (! $item || ! $this->isUsable($value)) {
                    continue;
                }
                $slices[] = [
                    'name' => (string) $item,
                    'value' => $this->toNumber($value),
                ];
            }

            if ($slices) {
                $assetAllocation = $fund->asset_allocation ?? [];
                $assetAllocation['geographicCountryExposure'] = $slices;
                $fund->asset_allocation = $assetAllocation;

                return;
            }
        }
```

- [ ] **Step 4: Run the tests to verify they pass**

Run: `php artisan test --filter=ExcelImportAsiaExJapanTest`
Expected: PASS, all three.

- [ ] **Step 5: Verify no regressions**

Run: `php artisan test && vendor/bin/phpstan analyse`
Expected: green. `ExcelImportHassenShariahTest` and the 877 tests in particular must still pass — they share `mapGeographicExposure`.

---

### Task 3: Split `LUX_TEMPLATES` and add 879's cost-table labels

`LUX_TEMPLATES` gates four behaviours and 879 differs on three. Adding 879 to the list would silently break them, so the constant is split by purpose.

**Files:**
- Modify: `app/Services/FundImport/FactsheetImporter.php` (constant at ~line 34, published-line suffix at ~line 159, cost table at ~line 1649)
- Test: `tests/Feature/ExcelImportAsiaExJapanTest.php`

**Interfaces:**
- Consumes: `'show-asia-ex-japan'` from Task 1.
- Produces: `fees['annualisedCostRatio']` with rows `TER — Basic`, `— Performance`, `Transaction costs (incl VAT)` and total `Total cost ratio` for 879; unchanged rows for 877/878. `important_info_published_date` ending in a full stop for 879.

- [ ] **Step 1: Write the failing tests**

Append to `tests/Feature/ExcelImportAsiaExJapanTest.php`:

```php
    /**
     * 879 differs from its 877/878 siblings on three of the four things the
     * old LUX_TEMPLATES constant gated at once.
     */
    public function test_cost_ratio_table_uses_the_asia_ex_japan_labels(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-asia-ex-japan']);

        $path = $this->makeXlsx([
            ['Code', 'Value'],
            ['GLOBAL_TER_BASIC_12_MONTH', '1.02'],
            ['GLOBAL_TER_BASIC_36_MONTH', '0.98'],
            ['GLOBAL_TER_PERFORMANCE_12_MONTH', '0.00'],
            ['GLOBAL_TER_PERFORMANCE_36_MONTH', '-0.10'],
            ['GLOBAL_TER_TRANSACTION_COSTS_12_MONTH', '0.07'],
            ['GLOBAL_TER_TRANSACTION_COSTS_36_MONTH', '0.08'],
            ['GLOBAL_TER_TOTAL_12_MONTH', '1.09'],
            ['GLOBAL_TER_TOTAL_36_MONTH', '0.96'],
        ], 'asia_cost_ratio');
        (new FactsheetImporter)->import($fund, $path);
        $fund->save();

        $acr = $fund->fresh()->fees['annualisedCostRatio'];

        $this->assertSame(
            ['TER — Basic', '— Performance', 'Transaction costs (incl VAT)'],
            array_column($acr['rows'], 'name')
        );
        $this->assertSame('Total cost ratio', $acr['total']['name']);
    }

    public function test_hassen_shariah_cost_ratio_labels_are_unchanged(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-hassen-shariah']);

        $path = $this->makeXlsx([
            ['Code', 'Value'],
            ['GLOBAL_TER_BASIC_12_MONTH', '1.14'],
            ['GLOBAL_TER_BASIC_36_MONTH', '1.10'],
            ['GLOBAL_TER_TRANSACTION_COSTS_12_MONTH', '0.08'],
            ['GLOBAL_TER_TRANSACTION_COSTS_36_MONTH', '0.08'],
            ['GLOBAL_TER_TOTAL_12_MONTH', '1.22'],
            ['GLOBAL_TER_TOTAL_36_MONTH', '1.18'],
        ], 'hassen_cost_ratio_regression');
        (new FactsheetImporter)->import($fund, $path);
        $fund->save();

        $acr = $fund->fresh()->fees['annualisedCostRatio'];

        $this->assertSame(['TER — Basic', 'Transaction costs'], array_column($acr['rows'], 'name'));
        $this->assertSame('Total investment charge', $acr['total']['name']);
    }

    public function test_published_line_ends_with_a_full_stop(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-asia-ex-japan']);

        $path = $this->makeXlsx([
            ['Code', 'Value'],
            ['PUBLISHED_DATE', '5 August 2026'],
        ], 'asia_published');
        (new FactsheetImporter)->import($fund, $path);
        $fund->save();

        $this->assertSame('Published on 05 August 2026.', $fund->fresh()->important_info_published_date);
    }

    public function test_lux_siblings_keep_their_full_stop_free_published_line(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-hassen-shariah']);

        $path = $this->makeXlsx([
            ['Code', 'Value'],
            ['PUBLISHED_DATE', '5 August 2026'],
        ], 'hassen_published_regression');
        (new FactsheetImporter)->import($fund, $path);
        $fund->save();

        $this->assertSame('Published on 05 August 2026', $fund->fresh()->important_info_published_date);
    }
```

- [ ] **Step 2: Run the tests to verify they fail**

Run: `php artisan test --filter=ExcelImportAsiaExJapanTest`
Expected: `test_cost_ratio_table_uses_the_asia_ex_japan_labels` FAILS (no `— Performance` row, `Transaction costs` unsuffixed) and `test_published_line_ends_with_a_full_stop` PASSES incidentally (879 is not in `LUX_TEMPLATES` yet). The two regression tests PASS.

- [ ] **Step 3: Replace the constant with purpose-named ones**

In `app/Services/FundImport/FactsheetImporter.php`, replace the `LUX_TEMPLATES` declaration and its docblock with:

```php
    /**
     * Sheets whose published line carries no full stop (877, 878). The 879
     * reference prints one, so it is deliberately absent here.
     *
     * @var list<string>
     */
    private const PUBLISHED_LINE_NO_STOP_TEMPLATES = ['show-global-equity', 'show-hassen-shariah'];

    /**
     * Sheets whose ANNUALISED COST RATIO breaks the performance-fee
     * component out as an indented "— Performance" row. Classes that levy no
     * performance fee export blank cells and drop the row regardless.
     *
     * @var list<string>
     */
    private const PERFORMANCE_COMPONENT_ROW_TEMPLATES = ['show-global-equity', 'show-hassen-shariah', 'show-asia-ex-japan'];

    /**
     * Cost-table row labels that differ per sheet. 877/878 total as "Total
     * investment charge" over a plain "Transaction costs" row; the 879
     * reference reads "Total cost ratio" over "Transaction costs (incl VAT)".
     *
     * @var array<string, array{transactionCosts: string, total: string}>
     */
    private const COST_TABLE_LABELS = [
        'show-asia-ex-japan' => [
            'transactionCosts' => 'Transaction costs (incl VAT)',
            'total' => 'Total cost ratio',
        ],
    ];

    private const COST_TABLE_LABELS_LUX = [
        'transactionCosts' => 'Transaction costs',
        'total' => 'Total investment charge',
    ];

    private const COST_TABLE_LABELS_DEFAULT = [
        'transactionCosts' => 'Transaction costs',
        'total' => 'Total cost ratio',
    ];
```

- [ ] **Step 4: Update the three call sites**

Published-line suffix (~line 159) — replace the `$suffix` line:

```php
            $suffix = in_array($fund->template ?? '', self::PUBLISHED_LINE_NO_STOP_TEMPLATES, true) ? '' : '.';
```

Cost table (~line 1649) — replace the contiguous run **from** the `$isGlobalEquity = in_array(...)` line **through** the closing `];` of the `$acr['total'] = [...]` assignment, inclusive (this run also contains the existing `$performance12 = $cell(...)` line, which the replacement re-declares), with:

```php
        $template = $fund->template ?? '';
        $labels = self::COST_TABLE_LABELS[$template]
            ?? (in_array($template, ['show-global-equity', 'show-hassen-shariah'], true)
                ? self::COST_TABLE_LABELS_LUX
                : self::COST_TABLE_LABELS_DEFAULT);

        $performance12 = $cell($data['GLOBAL_TER_PERFORMANCE_12_MONTH'] ?? null);
        if (in_array($template, self::PERFORMANCE_COMPONENT_ROW_TEMPLATES, true) && $performance12 !== null) {
            $rows[] = [
                'name' => '— Performance',
                '12m' => $performance12,
                '36m' => $cell($data['GLOBAL_TER_PERFORMANCE_36_MONTH'] ?? null),
            ];
        }
        $rows[] = [
            'name' => $labels['transactionCosts'],
            '12m' => $cell($data['GLOBAL_TER_TRANSACTION_COSTS_12_MONTH'] ?? null),
            '36m' => $cell($data['GLOBAL_TER_TRANSACTION_COSTS_36_MONTH'] ?? null),
        ];
        $acr['rows'] = $rows;
        $acr['total'] = [
            'name' => $labels['total'],
            '12m' => $cell($data['GLOBAL_TER_TOTAL_12_MONTH'] ?? null),
            '36m' => $cell($data['GLOBAL_TER_TOTAL_36_MONTH'] ?? null),
        ];
```

Then `grep -n "LUX_TEMPLATES" app/Services/FundImport/FactsheetImporter.php` — there must be **zero** hits left.

- [ ] **Step 5: Run the tests to verify they pass**

Run: `php artisan test --filter=ExcelImportAsiaExJapanTest`
Expected: PASS, all seven.

- [ ] **Step 6: Verify**

Run: `php artisan test && vendor/bin/phpstan analyse`
Expected: green. The 877/878 cost-table and published-line assertions in the existing suites are the ones that prove the split is behaviour-preserving.

---

### Task 4: Characterisation tests for the top 10, the "Property" sector and both price graphs

The spec predicts these already work. This task proves it and locks the behaviour in, so a later refactor cannot quietly break Class R1 or rename a sector. Expect no production code change; if a test fails, fix the importer.

**Files:**
- Test: `tests/Feature/ExcelImportAsiaExJapanTest.php`

**Interfaces:**
- Consumes: `'show-asia-ex-japan'` from Task 1.
- Produces: nothing new — guards `chart_data['performanceData']` and `sector_allocation['sectors']`.

- [ ] **Step 1: Write the tests**

Append to `tests/Feature/ExcelImportAsiaExJapanTest.php`:

```php
    /**
     * 877/878/823 rename the feed's "Property" sector to "Real estate"; the
     * 879 reference prints "Property", so this template must be left out of
     * PORTFOLIO_STRUCTURE_TEMPLATES.
     */
    public function test_property_sector_is_published_verbatim(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-asia-ex-japan']);

        $path = $this->makeXlsx([
            ['Code', 'Value'],
            ['ESAOT_RANK_1_ITEM', 'Consumer discretionary'],
            ['ESAOT_RANK_1_CURRENT', '30'],
            ['ESAOT_RANK_1_CHANGE_SIGN', '+'],
            ['ESAOT_RANK_1_CHANGE', '3.1'],
            ['ESAOT_RANK_2_ITEM', 'Property'],
            ['ESAOT_RANK_2_CURRENT', '3'],
            ['ESAOT_RANK_2_CHANGE_SIGN', '+'],
            ['ESAOT_RANK_2_CHANGE', '0.2'],
        ], 'asia_sectors');
        (new FactsheetImporter)->import($fund, $path);
        $fund->save();

        $sectors = $fund->fresh()->sector_allocation['sectors'];

        $this->assertSame(['Consumer discretionary', 'Property'], array_column($sectors, 'name'));
        $this->assertSame('up', $sectors[1]['direction']);
    }

    /**
     * The 879 feed sends no sector or market columns for the top 10 — the
     * sheet prints SECURITY and % OF FUND only.
     */
    public function test_top_investments_import_without_sector_or_market_columns(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-asia-ex-japan']);

        $path = $this->makeXlsx([
            ['Code', 'Value'],
            ['TOPX_SECURITY_1', 'TSMC'],
            ['TOPX_PERCENT_OF_FUNDS_1', '8.4'],
            ['TOPX_SECURITY_2', 'APR Corp/Korea'],
            ['TOPX_PERCENT_OF_FUNDS_2', '7.8'],
        ], 'asia_top10');
        (new FactsheetImporter)->import($fund, $path);
        $fund->save();

        $rows = $fund->fresh()->top_investments['rows'];

        $this->assertCount(2, $rows);
        $this->assertSame('TSMC', $rows[0]['security']);
        $this->assertEquals(8.4, $rows[0]['percentage']);
        $this->assertSame('', $rows[0]['assetClass']);
        $this->assertSame('', $rows[0]['market']);
    }

    public function test_class_r_price_graph_produces_three_series(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-asia-ex-japan']);

        $path = $this->makeXlsx([
            ['Start Date', 'Description', '879 Fund Price (1st) [FASFR USD HRD]', '879 Fund Benchmark [MSCI ASIA USD]', '879 Fund Benchmark (2nd) [AXJPG]'],
            [44404, 'Jul 2021', 102.68, 102.3022281918, 99.45],
            [44404, 'Jul 2026', 131.68, 152.4679219048, 130.42214601],
        ], 'asia_price_graph_r');
        (new PriceGraphImporter)->import($fund, $path);
        $fund->save();

        $performance = $fund->fresh()->chart_data['performanceData'];

        $this->assertSame(['date', 'fund', 'benchmark', 'peerGroup'], array_keys($performance[0]));
        $this->assertSame('2021-07', $performance[0]['date']);
        $this->assertEquals(131.68, $performance[1]['fund']);
        $this->assertEquals(152.47, $performance[1]['benchmark']);
        $this->assertEquals(130.42, $performance[1]['peerGroup']);
    }

    /**
     * Class R1's fund column is headed "Fund Price (2nd)" rather than
     * "(1st)". The three-series match keys off column E, so the R1 export
     * must route identically — this is the guard for that.
     */
    public function test_class_r1_price_graph_produces_three_series(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-asia-ex-japan']);

        $path = $this->makeXlsx([
            ['Start Date', 'Description', '879 Fund Price (2nd) [FASFR1 USD HRD]', '879 Fund Benchmark [MSCI ASIA USD]', '879 Fund Benchmark (2nd) [AXJPG]'],
            [44404, 'Jul 2021', 102.68, 102.3022281918, 99.45],
            [44404, 'Jul 2026', 134, 152.4679219048, 130.42214601],
        ], 'asia_price_graph_r1');
        (new PriceGraphImporter)->import($fund, $path);
        $fund->save();

        $performance = $fund->fresh()->chart_data['performanceData'];

        $this->assertSame(['date', 'fund', 'benchmark', 'peerGroup'], array_keys($performance[0]));
        $this->assertEquals(134, $performance[1]['fund']);
        $this->assertEquals(130.42, $performance[1]['peerGroup']);
    }
```

- [ ] **Step 2: Run the tests**

Run: `php artisan test --filter=ExcelImportAsiaExJapanTest`
Expected: PASS with no production change. If `test_property_sector_is_published_verbatim` fails, someone has added `show-asia-ex-japan` to `PORTFOLIO_STRUCTURE_TEMPLATES` — remove it. If either price-graph test fails, widen the peer-column match in `PriceGraphImporter::import` rather than changing the fixtures.

- [ ] **Step 3: Verify**

Run: `php artisan test && vendor/bin/phpstan analyse`
Expected: green.

---

### Task 5: Seed both classes and import the July 2026 data

**Files:**
- Create: `Funds/879 - Asia ex-Japan Fund/seed-879-asia-ex-japan.php`
- Move: the two reference PDFs into `Funds/879 - Asia ex-Japan Fund/Design/`

**Interfaces:**
- Consumes: the template and importer branches from Tasks 1–4.
- Produces: funds **51** (Class R) and **52** (Class R1), populated. Tasks 6–9 render them.

- [ ] **Step 1: Move the reference PDFs**

```bash
cd "Funds/879 - Asia ex-Japan Fund"
mkdir -p Design
mv "Foord Asia ex-Japan Fund Class R at 2026-07-31.pdf" \
   "Design/Foord Asia ex-Japan Fund Class R at 2026-07-31 (reference).pdf"
mv "Foord Asia ex-Japan Fund Class R1 at 2026-07-31.pdf" \
   "Design/Foord Asia ex-Japan Fund Class R1 at 2026-07-31 (reference).pdf"
```

- [ ] **Step 2: Write the seed script**

Create `Funds/879 - Asia ex-Japan Fund/seed-879-asia-ex-japan.php`, modelled on `Funds/878 - Hassen Shariah Global Equity Fund/seed-878-hassen-shariah.php` (read that file first and follow its shape exactly: `firstOrNew`, preserve `performance_table`/`page2_content` on re-run, `echo` the seeded name at the end).

Loop over a per-class array so both classes seed from one file:

```php
$classes = [
    'R' => [
        'isin' => 'LU2107516614',
        'fees' => 'Standard minimum annual fee: 0.85%<br>Performance fee sharing rate: 15%<br>Maximum annual fee: uncapped',
        'auditedTer' => '0.97%',
    ],
    'R1' => [
        'isin' => 'LU2107516705',
        'fees' => 'Standard minimum annual fee: 0.50%<br>Performance fee sharing rate: 15%<br>Maximum annual fee: uncapped',
        'auditedTer' => '0.71%',
    ],
];
```

Shared statics, transcribed from `Design/… Class R … (reference).pdf`:

- `name` — `'FOORD ASIA EX-JAPAN FUND — CLASS '.$classCode`
- `template` — `'show-asia-ex-japan'`
- `description` — "The fund aims to achieve long-term capital growth from an actively managed and diversified portfolio of listed equities whose businesses are predominantly focused on the Asia ex-Japan region and to thereby outperform its MSCI Asia ex-Japan benchmark, without assuming greater risk. The fund is appropriate for investors with a long investment horizon and who can withstand bouts of investment volatility in the short to medium term."
- `domicile` `'Luxembourg'`; `management_company` `'FundSight S.A.'`; `depository` `'CACEIS Bank, Luxembourg Branch'`; `investment_manager` `'Foord Asset Management (Guernsey) Limited'`; `sub_investment_manager` `'Foord Asset Management (Singapore) Pte. Limited'`
- `fund_managers` `'Ishreth Hassen and Jing Cong Xue'`; `inception_date` `'27 July 2021'`; `base_currency` `'US dollars'`
- `category` `'Asia ex-Japan Equity'`; `benchmark` `'MSCI All Country Asia ex-Japan net total return (USD) Index'`
- `type_of_shares` `'Accumulation'`; `minimums` `'US$10 000'`; `subsequent_subscription_amount` `'US$1 000'`; `time_horizon` `'Longer than five years'`
- `equity_indicator_description` `'Indicates the relative weight of equities in the portfolio. A higher weight could result in increased volatility of returns.'`
- `important_info_title` `'IMPORTANT INFORMATION FOR INVESTORS'`; footer T line `'+65 6521 1100 | +27 21 532 6969'`, E line `'investments@foord.com'`
- **Do NOT set** `shariah_supervisory_board`.

Structured seeds:

```php
$fund->performance_table = array_merge($existingPerformanceTable, [
    'title' => 'PORTFOLIO PERFORMANCE % (PERIODS GREATER THAN ONE YEAR ARE ANNUALISED¹)',
    'headers' => ['', 'CASH<br>VALUE²', 'SINCE<br>INCEPTION', '3<br>YRS', '1<br>YR', '6<br>MTHS', '3<br>MTHS', 'YTD', 'THIS<br>MONTH'],
    'columnKeys' => ['cashValue', 'sinceInception', '3yrs', '1yr', '6months', '3months', 'ytd', 'thisMonth'],
    'footnotes' => $footnotes,
]);

$topInvestments = $fund->top_investments ?? [];
$topInvestments['title'] = 'TOP 10 INVESTMENTS';
$topInvestments['headers'] = ['SECURITY', '% OF FUND'];
$fund->top_investments = $topInvestments;

$sectorAllocation = $fund->sector_allocation ?? [];
$sectorAllocation['title'] = 'PORTFOLIO STRUCTURE %';
$fund->sector_allocation = $sectorAllocation;

$chartData = $fund->chart_data ?? [];
$chartData['title'] = 'PORTFOLIO PERFORMANCE VS BENCHMARK';
$chartData['geoTitle'] = 'GEOGRAPHIC COUNTRY EXPOSURE';
$fund->chart_data = $chartData;
```

Footnotes, verbatim from the reference (note 5 has no closing full stop):

```php
$footnotes = [
    '<sup>1</sup> Returns in USD unless otherwise stated and annualised for periods greater than one year, meaning they are converted to reflect the average yearly return for each period presented.',
    '<sup>2</sup> Current value of 100 000 notional currency units invested at inception (graphically represented in $’000s above).',
    '<sup>3</sup> Performance, net of fees and expenses, is calculated for the portfolio on a single pricing basis (i.e. NAV to NAV rolling monthly basis). Individual investor performance may differ as a result of the actual investment date. Past performance of the fund is not indicative of its future performance.',
    '<sup>4</sup> Asia ex-Japan Equity (provisional). Source: Morningstar.',
    '<sup>5</sup> Highest and lowest actual 12 month dollar return achieved in the period',
    'The portfolio information is presented using effective exposure.',
    'Note: Totals may not cast perfectly due to rounding.',
];
```

`page2_content` — copy 878's `performanceFees` paragraphs verbatim, then:

- `performanceFeeExamples['title']` = `'PERFORMANCE FEE EXAMPLES FOR FOORD ASIA EX-JAPAN'`, same rows/values as 878 (identical 15% sharing rate and worked example).
- `sharePricing['text']` — 878's text with the cut-off changed to **08h00**: "… All dealing application requests must be received before 08h00 (Central European time) on each Valuation Day."
- `moreAboutFund` paragraph 1 — 878's with "It is a medium-high-risk fund; rated 5 out of 7 using the Synthetic Risk and Reward Indictor (SRRI) calculation methodology guided by the European Commission." and **no** Shariah-criteria clause: "The fund is actively managed and not constrained by the benchmark in its portfolio positioning. The Manager decides on the portfolio's asset selection, regional allocation, sector views and overall level of exposure to the market to take advantage of investment opportunities." Paragraph 2 is 878's verbatim.
- `important_info_paragraphs` — 878's, with the audited-TER sentence carrying the per-class value from `$classes`.

- [ ] **Step 3: Run the seed**

```bash
php artisan tinker --execute='include "Funds/879 - Asia ex-Japan Fund/seed-879-asia-ex-japan.php";'
```

Expected: two lines, `Seeded fund 51 — FOORD ASIA EX-JAPAN FUND — CLASS R` and `Seeded fund 52 — … CLASS R1`. If the ids differ (the concurrent session may have added more funds), use the ids it prints for the rest of the plan and note them.

- [ ] **Step 4: Import both classes**

```bash
php artisan fund:import 51 "storage/app/private/fund-data/2026-07/879"
php artisan fund:import 52 "storage/app/private/fund-data/2026-07/879"
```

The directory holds both classes' exports, but `FundImportManager::filesForClass` already filters on `fund_code` + `class_code` — its `/^879([A-Za-z][0-9]*)?_/` pattern yields token `R` for `879R_FACTSHEET.xlsx` and `R1` for `879R1_FACTSHEET.xlsx`, so each fund reads only its own pair. Confirm that held: Class R unit price `$13.17`, Class R1 `$13.40`. If both funds land on the same price, the class token did not match and `class_code` is wrong on the seeded record.

- [ ] **Step 5: Verify the stored data**

```bash
php artisan tinker --execute='
$f = App\Models\Fund::find(51);
echo $f->unit_price, " | ", $f->portfolio_size, PHP_EOL;
print_r(array_column($f->asset_allocation["geographicCountryExposure"], "name"));
print_r(array_column($f->sector_allocation["sectors"], "name"));
echo count($f->chart_data["performanceData"]), " chart points", PHP_EOL;
echo $f->fees["annualisedCostRatio"]["total"]["name"], PHP_EOL;
'
```

Expected: `$13.17 | $192.8 million`; seven country slices ending in `Other`; ten sectors ending in `Property`; 61 chart points; `Total cost ratio`.

---

### Task 6: Page-1 grid — sidebar, banner, and the structure ∥ top-10 row

**Files:**
- Modify: `resources/views/funds/show-asia-ex-japan.blade.php`

**Interfaces:**
- Consumes: funds 51/52 from Task 5.
- Produces: the `.two-col` row-1 markup that Task 7 puts the pie beside.

- [ ] **Step 1: Banner — restore the class suffix**

879 prints the class in the banner ("FOORD ASIA EX-JAPAN FUND — CLASS R") where 877/878 strip it into the sidebar. Replace the banner `@php` block and `<h1>` so `$classText` renders in a `.class-suffix` span, and change the `editableField` formatter argument from `'fundNameNoClass'` to none:

```blade
                @php
                    $fundName = $fund->data['fund']['name'] ?? $fund->name;
                    if (preg_match('/^(.+?)\s*[-—–]\s*(CLASS\s+[A-Z][0-9]*)$/iu', $fundName, $nameMatches)) {
                        $mainName = trim($nameMatches[1]);
                        $classText = mb_strtoupper(trim($nameMatches[2]));
                    } else {
                        $mainName = $fundName;
                        $classText = '';
                    }
                @endphp
                <h1>
                    <span x-data="editableField('fund.name', '{{ addslashes($fund->data['fund']['name'] ?? $fund->name) }}')"
                          @click="editMode && startEdit()"
                          :class="editMode ? 'editable' : ''">{{ mb_strtoupper($mainName) }}@if($classText)<span class="class-suffix"> — {{ $classText }}</span>@endif</span>
                </h1>
```

Add to the stylesheet, beside the existing `.fund-banner h1` rule:

```css
        .fund-banner h1 .class-suffix { font-weight: 400; }
```

Size the suffix by measuring the reference (see Task 10's loop) — it is visibly lighter and smaller than the fund name.

- [ ] **Step 2: Sidebar — labels, order and dots**

In the sidebar `@php` block:

- `$labelMap`: change `'fundManagers' => 'FUND MANAGERS'` (plural), `'minimumSubscriptionAmount' => 'INITIAL SUBSCRIPTION AMOUNT'`, `'totalFundSize' => 'FUND SIZE'`, `'fees' => 'FEES'` (drop the class suffix).
- `$displayOrder`: remove `'shareClass'` and `'shariahSupervisoryBoard'`. The order becomes:

```php
                            $displayOrder = [
                                'marketingCommunication', 'domicile', 'managementCompany',
                                'depository', 'investmentManager', 'subInvestmentManager',
                                'fundManagers', 'inceptionDate', 'baseCurrency', 'equityIndicator',
                                'morningstarCategory', 'benchmark', 'typeOfShares',
                                'minimumSubscriptionAmount', 'subsequentSubscriptionAmount',
                                'totalFundSize', 'monthEndSharePrice', 'numberOfShares',
                                'timeHorizon', 'fees', 'isinNumber',
                            ];
```

- Equity dots: change the default from 8 to **9**, and update the comment:

```php
                                            /* 879 reference: nine of ten dots filled */
                                            $filledDots = $value['filled'] ?? 9;
```

Leave `.equity-dot.empty circle { fill: var(--medium-grey); }` as it is — the 879 reference shows a solid grey off-dot, and per the standing note new clones follow their own reference rather than the June hollow-dot amend.

- [ ] **Step 3: Row 1 — structure beside top 10**

Wrap the existing `.ps-section` block and the TOP 10 block in a shared `.two-col` row, moving the TOP 10 block up from below the charts. Delete the `.ps-header-variance` header cell and the `.ps-variance` span from each row (879 has no variance column). Change the top-10 header/body to two columns, dropping the sector and market cells.

The structure block's subtitle stays — it renders "Change since 30 June 2026" **below** the title here, not in a right-hand column, so move the `ps-header-change` span under `ps-header-title` and drop the `x-html` line-break replace:

```blade
                                    <span x-data="editableField('mainContent.sectorAllocation.subtitle', '{{ addslashes($psData['subtitle'] ?? '') }}')"
                                          @click="editMode && startEdit()"
                                          :class="editMode ? 'editable' : ''"
                                          x-text="value"></span>
```

Keep the fixed bar scale (`$psBarScale = 1.72`) as the starting value and re-measure it in Task 10 — the 879 bars are narrower than 878's because the block is narrower.

Keep the `zeroWeightSectors` append: the 879 July feed sends none, but the loop is harmless and the block is shared with future months.

- [ ] **Step 4: Look at the page**

Run: `php artisan serve` (or use the existing `fund-manager.test` host) and open `/funds/51`.
Expected: sidebar with no SHARE CLASS row and the new labels; structure bars and the two-column top 10 side by side; the old geographic bar chart still below (Task 7 replaces it). Nothing fatals.

- [ ] **Step 5: Verify**

Run: `php artisan test && vendor/bin/phpstan analyse`
Expected: green.

---

### Task 7: The GEOGRAPHIC COUNTRY EXPOSURE pie

**Files:**
- Modify: `resources/views/funds/show-asia-ex-japan.blade.php`

**Interfaces:**
- Consumes: `asset_allocation['geographicCountryExposure']` from Task 2, the row-2 `.two-col` from Task 6.
- Produces: the `#geoPieChart` canvas.

- [ ] **Step 1: Replace the geo chart markup**

Swap the `geographicEquityExposure` block in the left column for:

```blade
                            @if(!empty($fund->data['mainContent']['assetAllocation']['geographicCountryExposure']))
                                <div>
                                    <h3 class="section-heading">GEOGRAPHIC COUNTRY EXPOSURE</h3>
                                    <div class="chart-wrapper geo-pie-wrapper">
                                        <canvas id="geoPieChart"></canvas>
                                    </div>
                                </div>
                            @endif
```

There is no legend element — the slice labels sit outside the pie.

- [ ] **Step 2: Replace the geo chart script**

Swap the `geoChart` bar-chart block for the pie. The label plugin is registered **per-instance** in the `plugins:` array, never through `Chart.register` — a global registration would draw these labels over the performance chart too.

```js
        @if(!empty($fund->data['mainContent']['assetAllocation']['geographicCountryExposure']))
        // GEOGRAPHIC COUNTRY EXPOSURE pie. Geometry measured off the 879
        // reference: the pie starts at 12 o'clock and runs clockwise in feed
        // rank order (the feed already ranks descending with the "Other"
        // catch-all last), and the palette is positional, not per-country.
        const countryData = @json($fund->data['mainContent']['assetAllocation']['geographicCountryExposure']);
        const sliceColours = ['#d25347', '#29363d', '#cccccc', '#7a9cb4', '#535353', '#e2cea4', '#bfc3c5'];

        // Chart.js has no outside labels; the reference draws "Name %" in
        // grey, radially outside each slice, with no leader lines.
        const pieLabelPlugin = {
            id: 'pieOutsideLabels',
            afterDraw(chart) {
                const { ctx } = chart;
                const meta = chart.getDatasetMeta(0);
                ctx.save();
                ctx.font = '7.2px Avenir Next, Lato, sans-serif';
                ctx.fillStyle = '#535353';
                meta.data.forEach((arc, i) => {
                    const mid = (arc.startAngle + arc.endAngle) / 2;
                    const r = arc.outerRadius + 12;
                    const x = arc.x + Math.cos(mid) * r;
                    const y = arc.y + Math.sin(mid) * r;
                    ctx.textAlign = Math.cos(mid) < -0.05 ? 'right' : (Math.cos(mid) > 0.05 ? 'left' : 'center');
                    ctx.textBaseline = 'middle';
                    const label = countryData[i].name + ' ' + Number(countryData[i].value).toFixed(1) + '%';
                    ctx.fillText(label, x, y);
                });
                ctx.restore();
            }
        };

        new Chart(document.getElementById('geoPieChart').getContext('2d'), {
            type: 'pie',
            plugins: [pieLabelPlugin],
            data: {
                labels: countryData.map(d => d.name),
                datasets: [{
                    data: countryData.map(d => d.value),
                    backgroundColor: countryData.map((d, i) => sliceColours[i % sliceColours.length]),
                    borderColor: '#fff',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: false,
                // 12 o'clock start, clockwise — Chart.js measures from 3
                // o'clock, so rotate back a quarter turn.
                rotation: -90,
                plugins: { legend: { display: false }, tooltip: { enabled: false } },
                layout: { padding: 46 }
            }
        });
        @endif
```

- [ ] **Step 3: Long labels must wrap**

"Taiwan, Province of China 10.5%" and "United States of America 7.2%" are set on two lines in the reference. Replace the single `ctx.fillText` in the plugin with a greedy wrap at a measured pixel width, centring the block vertically on the slice's mid-angle:

```js
                const LABEL_MAX_WIDTH = 62;   // px at render scale; re-measure in Task 10
                const LINE_HEIGHT = 8.6;

                const wrap = (text) => {
                    const words = text.split(' ');
                    const lines = [];
                    let line = '';
                    words.forEach(word => {
                        const candidate = line ? line + ' ' + word : word;
                        if (line && ctx.measureText(candidate).width > LABEL_MAX_WIDTH) {
                            lines.push(line);
                            line = word;
                        } else {
                            line = candidate;
                        }
                    });
                    if (line) lines.push(line);
                    return lines;
                };
```

and inside the `meta.data.forEach`, in place of the single `fillText`:

```js
                    const lines = wrap(countryData[i].name + ' ' + Number(countryData[i].value).toFixed(1) + '%');
                    lines.forEach((lineText, li) => {
                        ctx.fillText(lineText, x, y + (li - (lines.length - 1) / 2) * LINE_HEIGHT);
                    });
```

Tune `LABEL_MAX_WIDTH` until the wrap points match the reference exactly: `Taiwan, Province` / `of China 10.5%` and `United States of` / `America 7.2%`.

- [ ] **Step 4: Look at the page**

Open `/funds/51`.
Expected: a seven-slice pie, China red and largest starting at 12 o'clock running clockwise, grey labels outside with no leader lines, no legend, and the performance chart beside it unaffected by the label plugin.

- [ ] **Step 5: Verify**

Run: `php artisan test && vendor/bin/phpstan analyse`
Expected: green.

---

### Task 8: Performance chart ticks and the performance table

**Files:**
- Modify: `resources/views/funds/show-asia-ex-japan.blade.php`

**Interfaces:**
- Consumes: `chart_data['performanceData']` (Task 4's guarantee), `performance_table` seeded in Task 5.
- Produces: the finished page-1 lower half.

- [ ] **Step 1: Fix the x-axis ticks**

878's series opens at Dec 2020 so its ticks are `index % 12 === 1`. 879 opens at **Jul 2021** (index 0) and the reference ticks read Jul 21 … Jul 26:

```js
                            callback: function (value, index) {
                                return index % 12 === 0 ? formatChartDate(this.getLabelForValue(value)) : null;
                            }
```

- [ ] **Step 2: Fix the legend copy**

```blade
                                    <div class="chart-legend perf-legend">
                                        <span><span class="legend-line" style="background: var(--naartjie);"></span> Fund</span>
                                        <span><span class="legend-line" style="background: var(--dark-navy);"></span> MSCI Asia ex-Japan USD</span>
                                        <span><span class="legend-line" style="background: var(--light-blue);"></span> Peer Group</span>
                                    </div>
```

The reference wraps this legend onto two lines — Fund and MSCI on the first, Peer Group beneath Fund. Match that with the legend's flex wrapping, not a hard `<br>`.

- [ ] **Step 3: Keep the scale linear**

Leave the `y` scale as the linear one inherited from 878 and update the comment to record the evidence:

```js
                        // Linear scale, confirmed against the 879 reference
                        // raster: fitting the two known end values linearly
                        // puts the 100 baseline 0.9px out and the benchmark
                        // endpoint 4.9px out, where a log fit misses the
                        // endpoint by 19.7px. (877 is logarithmic; the
                        // sibling's scale type is never assumed.)
```

Re-fit `perfMax`/`perfMin` in Task 10 against the reference — 878's `+4.3 / -4.0` padding was measured for 878's series, not this one.

- [ ] **Step 4: Rename the performance-table rows**

The importer writes `Fund` / `Benchmark` / `Comparator 2` / `Fund highest` / `Fund lowest`. Find the display-time rename block that 878 uses for its rows and map, for this template:

- `Fund` → `Fund<sup>3</sup>`
- `Benchmark` → `MSCI Asia ex-Japan`
- `Comparator 2` → `Peer group<sup>4</sup>`
- a blank spacer row
- `Fund highest` → `Fund highest<sup>3,5</sup>`, `Fund lowest` → `Fund lowest<sup>3,5</sup>`

Drop 877's Class B sterling/euro comparator rows — 879 has none.

- [ ] **Step 5: Look at the page**

Open `/funds/51` and `/funds/52`.
Expected: yearly ticks Jul 21 … Jul 26; end labels `$ 152` black, `$ 132` red, `$ 130` blue for Class R (the peer value is the feed's `$130,422`, not the reference's `$127,461` — see the flagged gaps); table columns CASH VALUE / SINCE INCEPTION / 3 YRS / 1 YR / 6 MTHS / 3 MTHS / YTD / THIS MONTH.

- [ ] **Step 6: Verify**

Run: `php artisan test && vendor/bin/phpstan analyse`
Expected: green.

---

### Task 9: Page 2

Statics come from the seed (Task 5); this task is the markup deltas only.

**Files:**
- Modify: `resources/views/funds/show-asia-ex-japan.blade.php`

- [ ] **Step 1: Check what the seed already covers**

Open `/funds/51` page 2. The cost-table labels (Task 3), the SRRI sentence, the 08h00 cut-off and the fee-example title (Task 5) should already be right. Only fix in the template what is not data-driven.

- [ ] **Step 2: Fix the section order and any 878-specific markup**

Compare against `Design/Foord Asia ex-Japan Fund Class R at 2026-07-31 (reference).pdf`:

```bash
diff <(pdftotext -layout "Funds/879 - Asia ex-Japan Fund/Design/Foord Asia ex-Japan Fund Class R at 2026-07-31 (reference).pdf" - | sed -n '100,200p') \
     <(pdftotext -layout out.pdf - | sed -n '100,200p')
```

Remove any Shariah-specific markup the clone carried over. Both footer T and E lines render (879 prints both, like 878).

- [ ] **Step 3: Verify**

Run: `php artisan test && vendor/bin/phpstan analyse`
Expected: green.

---

### Task 10: Visual match, documentation, and final verification

**Files:**
- Modify: `resources/views/funds/show-asia-ex-japan.blade.php` (measurements)
- Modify: `FUND-ONBOARDING.md` (new §5s)
- Create: the two deliverable PDFs at `Funds/879 - Asia ex-Japan Fund/`

- [ ] **Step 1: Render and compare**

Export both classes to PDF through the app, then:

```bash
pdftoppm -f 1 -l 1 -r 150 -png "Funds/879 - Asia ex-Japan Fund/Design/Foord Asia ex-Japan Fund Class R at 2026-07-31 (reference).pdf" /tmp/ref
pdftoppm -f 1 -l 1 -r 150 -png "Funds/879 - Asia ex-Japan Fund/Foord Asia ex-Japan Fund Class R at 2026-07-31.pdf" /tmp/out
diff <(pdftotext -layout "Funds/879 - Asia ex-Japan Fund/Design/Foord Asia ex-Japan Fund Class R at 2026-07-31 (reference).pdf" -) \
     <(pdftotext -layout "Funds/879 - Asia ex-Japan Fund/Foord Asia ex-Japan Fund Class R at 2026-07-31.pdf" -)
```

Work the text diff to zero first — it catches wrong labels, wrong wording and wrong column sets far faster than looking at pixels. The three known feed-vs-reference gaps (country slices, peer group row/line, `$192.8` vs `$192.7` fund size) will remain and are expected; everything else must match.

- [ ] **Step 2: Measure the remaining geometry**

Use the bbox-measurement loop from FUND-ONBOARDING §pdf-reference-matching for: the banner class-suffix size, the structure bar scale (mm per percentage point, measured on this narrower block), the pie centre and radius (target ⌀ 35.4mm), the pie label offsets, and the performance chart's `perfMax`/`perfMin` padding. Measure **string widths on identical text and take the ratio** for type sizes rather than guessing point values.

- [ ] **Step 3: Write FUND-ONBOARDING.md §5s**

Re-check the section index first (`grep -n "^## 5" FUND-ONBOARDING.md`) in case the concurrent session took `5s`. Follow §5q's shape and cover: the template's parentage and why the page-1 grid differs; the pie's measured geometry, clockwise-from-12 rank order and positional palette; the linear-not-log finding with its residuals; the `LUX_TEMPLATES` split and what each new constant gates; "Property" deliberately not renamed; the seed-script path and the `fund:import 51|52` command.

Add the three feed-vs-reference gaps to the document's **Known issues** section.

- [ ] **Step 4: Final verification**

Run: `php artisan test && vendor/bin/phpstan analyse`
Expected: fully green, including every pre-existing suite.

Then re-grep the shared files one last time to confirm no edit was lost to the concurrent session:

```bash
grep -c "show-asia-ex-japan" app/Models/Fund.php app/Http/Controllers/FundController.php \
  app/Http/Requests/StoreFundRequest.php app/Services/FundImport/FactsheetImporter.php \
  resources/views/funds/edit.blade.php
```

Expected: non-zero for all five.

- [ ] **Step 5: Report the flagged data gaps**

Report to the user, with figures: the geographic country split disagreement (including the feed's own "Dont agree with July figures, must ask Helena" comment), the peer-group row and chart line, and the `PORTFOLIO_SIZE` rounding. These are client questions, not bugs to fix.
