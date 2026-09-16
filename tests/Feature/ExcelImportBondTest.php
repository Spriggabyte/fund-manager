<?php

namespace Tests\Feature;

use App\Models\Fund;
use App\Services\FundImport\FactsheetImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class ExcelImportBondTest extends TestCase
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

    /**
     * Write rows (arrays of cell values) to a temp xlsx with a "Data Set" sheet.
     */
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

    public function test_maturity_breakdown_maps_fund_bars_and_preserves_benchmark(): void
    {
        $fund = Fund::factory()->create([
            'template' => 'show-bond',
            'chart_data' => [
                'maturityData' => [
                    'categories' => [
                        ['name' => '3-7 Years', 'fund' => 40.2, 'benchmark' => 26.9, 'change' => '(+22.2%)'],
                    ],
                ],
            ],
        ]);

        $path = $this->makeXlsx([
            ['Code', 'Value'],
            ['MATURITY_0_TO_1_YEAR', '-14'],
            ['MATURITY_1_TO_3_YEARS', '19'],
            ['MATURITY_3_TO_7_YEARS', '39'],
            ['MATURITY_7_TO_12_YEARS', '18'],
            ['MATURITY_12_TO_20_YEARS', '23'],
            ['MATURITY_20_PLUS_YEARS', '16'],
            ['MAT_CHANGE_3_TO_7_YEARS', 'ERR'],
            ['MAT_CHANGE_7_TO_12_YEARS', '-10.3'],
            ['LAST_QUARTER_END', '30 June 2026'],
        ], 'bond-maturity');

        (new FactsheetImporter)->import($fund, $path);

        $maturity = $fund->chart_data['maturityData'];
        $categories = collect($maturity['categories'])->keyBy('name');

        $this->assertSame('Change since 30 June 2026', $maturity['subtitle']);
        $this->assertCount(6, $maturity['categories']);
        $this->assertSame(-14, $categories['0-1 Year']['fund']);
        // The feed has no benchmark buckets — the stored value is preserved.
        $this->assertSame(26.9, $categories['3-7 Years']['benchmark']);
        // ERR change keeps the stored label; a numeric change is reformatted.
        $this->assertSame('(+22.2%)', $categories['3-7 Years']['change']);
        $this->assertSame('(-10.3%)', $categories['7-12 Years']['change']);
    }

    public function test_portfolio_statistics_err_cells_preserve_stored_values(): void
    {
        $fund = Fund::factory()->create([
            'template' => 'show-bond',
            'asset_allocation' => [
                'portfolioStatistics' => [
                    'rows' => [
                        ['name' => 'Yield', 'sup' => '1', 'fund' => '10.17%', 'benchmark' => '9.27%', 'relative' => ''],
                    ],
                ],
            ],
        ]);

        $path = $this->makeXlsx([
            ['Code', 'Value'],
            ['STAT_YIELD', 'ERR'],
            ['BM_YIELD', '9.31'],
            ['STAT_SA_DURATION', '6.66'],
            ['BM_SA_DURATION', 'ERR'],
            ['VAR_TO_BM_SA_DURATION', 'ERR'],
            ['STAT_SA_FLOATING_RATE_DURATION', '0.05'],
        ], 'bond-stats');

        (new FactsheetImporter)->import($fund, $path);

        $rows = collect($fund->asset_allocation['portfolioStatistics']['rows'])
            ->filter(fn ($row) => isset($row['name']))
            ->keyBy('name');

        // ERR keeps the stored value; usable cells are formatted per row type.
        $this->assertSame('10.17%', $rows['Yield']['fund']);
        $this->assertSame('9.31%', $rows['Yield']['benchmark']);
        $this->assertSame('6.66', $rows['Total duration']['fund']);
        // Nothing stored + ERR renders as a dash.
        $this->assertSame('-', $rows['Total duration']['benchmark']);
        // The floating-rate relative column repeats the fund's own duration.
        $this->assertSame('0.05', $rows['— Floating rate duration']['relative']);
    }

    public function test_credit_exposure_keeps_rating_dashes_and_drops_empty_sectors(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-bond']);

        $path = $this->makeXlsx([
            ['Code', 'Value'],
            ['RATING_F1_PLUS', '-14'],
            ['RATING_F1', '-'],
            ['RATING_AAA', '106'],
            ['RATING_AA', '8'],
            ['RATING_A', '-'],
            ['RATING_OTHER', '-'],
            ['SECTOR_USGOV', '-'],
            ['SECTOR_RSA', '105'],
            ['SECTOR_CORP', '8'],
            ['SECTOR_BANK', '-13'],
            ['SECTOR_OTHER', '-'],
        ], 'bond-credit');

        (new FactsheetImporter)->import($fund, $path);

        $credit = $fund->asset_allocation['creditExposure'];

        // The rating table keeps its fixed six rows, dashes included.
        $this->assertSame(['F1+', 'F1', 'AAA', 'AA', 'A', 'Other'], array_column($credit['ratings'], 'name'));
        $this->assertSame('-14', $credit['ratings'][0]['value']);
        // The sector table lists only sectors with exposure, reference order.
        $this->assertSame(
            [['name' => 'Big four banks', 'value' => '-13'], ['name' => 'SA Corporates', 'value' => '8'], ['name' => 'SA Government', 'value' => '105']],
            $credit['sectors']
        );
    }

    public function test_monthly_performance_maps_year_grid_from_month_end(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-bond']);

        $path = $this->makeXlsx([
            ['Code', 'Value'],
            ['MONTH_END_DATE', '31 July 2026'],
            ['YEAR_1_MONTH_OCT', '0.26'],
            ['YEAR_1_YTD', '4.4'],
            ['YEAR_2_MONTH_JAN', '2.64'],
            ['YEAR_2_YTD', '9.6'],
            ['YEAR_3_YTD', '15.8'],
            ['YEAR_4_YTD', '19.8'],
            ['YEAR_5_MONTH_JUL', '-0.89'],
            ['YEAR_5_YTD', '4.4'],
        ], 'bond-monthly');

        (new FactsheetImporter)->import($fund, $path);

        $years = collect($fund->chart_data['monthlyPerformance']['years'])->keyBy('year');

        // YEAR_5 is the sheet's month-end year; YEAR_1 is four years earlier.
        // (PHP casts the numeric-string collection keys to ints.)
        $this->assertSame([2022, 2023, 2024, 2025, 2026], $years->keys()->all());
        $this->assertSame('0.26', $years['2022']['months']['oct']);
        $this->assertNull($years['2022']['months']['jan']);
        $this->assertSame('-0.89', $years['2026']['months']['jul']);
        $this->assertSame('4.4', $years['2026']['ytd']);
    }

    public function test_bond_tic_omits_zero_performance_charge_row(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-bond']);

        $path = $this->makeXlsx([
            ['Code', 'Value'],
            ['SA_TER_TOTAL_EXPENSE_RATIO_12_MONTH', '0.62'],
            ['SA_TER_TOTAL_EXPENSE_RATIO_36_MONTH', '0.68'],
            ['SA_TER_MANAGERS_CHARGE_12_MONTH', '0.50'],
            ['SA_TER_MANAGERS_CHARGE_36_MONTH', '0.50'],
            ['SA_TER_PERFORMANCE_CHARGE_12_MONTH', '0.00'],
            ['SA_TER_PERFORMANCE_CHARGE_36_MONTH', '0.00'],
            ['SA_TER_VAT_AND_SUNDRY_COSTS_12_MONTH', '0.11'],
            ['SA_TER_VAT_AND_SUNDRY_COSTS_36_MONTH', '0.18'],
            ['SA_TER_TRANSACTIONS_COSTS_INCL_VAT_12_MONTH', '0.00'],
            ['SA_TER_TRANSACTIONS_COSTS_INCL_VAT_36_MONTH', '0.00'],
            ['SA_TER_TOTAL_INVESTMENT_CHARGE_12_MONTH', '0.62'],
            ['SA_TER_TOTAL_INVESTMENT_CHARGE_36_MONTH', '0.68'],
        ], 'bond-tic');

        (new FactsheetImporter)->import($fund, $path);

        $names = array_column($fund->fees['totalInvestmentCharge']['rows'], 'name');

        $this->assertNotContains('— Performance charge', $names);
        $this->assertContains('— VAT and sundry costs', $names);
    }

    /*
     * QC 2026-09-14 regression cover: the 826 export carries the flex income
     * STAT_SPREAD_TO_JIBAR key, which used to flip the bond fund onto the flex
     * statistics layout and blank every published value; the July+ exports
     * also dropped the SA_ prefix from the duration keys, and broken months
     * export a bare "%" instead of ERR.
     */

    /** The seeded bond statistics table, as published on the reference sheet. */
    private function seededBondStatistics(): array
    {
        return [
            'title' => 'PORTFOLIO STATISTICS',
            'headers' => ['', 'FUND', 'BENCHMARK', 'RELATIVE TO ALBI'],
            'rows' => [
                ['name' => 'Yield', 'sup' => '1', 'fund' => '9.89%', 'benchmark' => '9.10%', 'relative' => ''],
                ['name' => 'Weighted average time to maturity', 'fund' => '11.60 years', 'benchmark' => '11.55 years', 'relative' => ''],
                ['spacer' => true],
                ['name' => 'Total duration', 'sup' => '2', 'fund' => '6.16', 'benchmark' => '6.33', 'relative' => '-0.17'],
                ['name' => '— Fixed rate duration', 'fund' => '5.02', 'benchmark' => '6.33', 'relative' => '-1.31'],
                ['name' => '— Inflation linked duration', 'fund' => '1.12', 'benchmark' => '-', 'relative' => '1.12'],
                ['name' => '— Floating rate duration', 'fund' => '0.02', 'benchmark' => '-', 'relative' => '0.02'],
            ],
        ];
    }

    public function test_bond_statistics_keep_the_bond_layout_despite_the_spread_to_jibar_key(): void
    {
        $fund = Fund::factory()->create([
            'template' => 'show-bond',
            'asset_allocation' => ['portfolioStatistics' => $this->seededBondStatistics()],
        ]);

        // The August 2026 826 export: STAT_SPREAD_TO_JIBAR present, the
        // number dropped from every statistic ("%", " years", blank).
        $path = $this->makeXlsx([
            ['Code', 'Value'],
            ['MONTH_END_DATE', '31 August 2026'],
            ['STAT_YIELD', '%'],
            ['STAT_WEIGHTED_AVERAGE_TTM', ' years'],
            ['STAT_SPREAD_TO_JIBAR', '%'],
            ['STAT_DURATION', ''],
            ['STAT_FIXED_RATE_DURATION', ''],
            ['STAT_INFLATION_LINKED_DURATION', ''],
            ['STAT_FLOATING_RATE_DURATION', ''],
            ['BM_YIELD', '%'],
            ['BM_WEIGHTED_AVERAGE_TTM', ' years'],
            ['BM_DURATION', ''],
            ['BM_FIXED_RATE_DURATION', ''],
            ['BM_INFLATION_LINKED_DURATION', '-'],
            ['BM_FLOATING_RATE_DURATION', '-'],
            ['VAR_TO_BM_DURATION', ''],
            ['VAR_TO_BM_FIXED_RATE_DURATION', ''],
            ['VAR_TO_BM_INFLATION_LINKED_DURATION', ''],
            ['VAR_TO_BM_FLOATING_RATE_DURATION', ''],
        ], 'bond-stats-err');

        (new FactsheetImporter)->import($fund, $path);

        $stats = $fund->asset_allocation['portfolioStatistics'];

        $this->assertSame(['', 'FUND', 'BENCHMARK', 'RELATIVE TO ALBI'], $stats['headers']);
        $this->assertSame(
            ['Yield', 'Weighted average time to maturity', 'Total duration', '— Fixed rate duration', '— Inflation linked duration', '— Floating rate duration'],
            array_values(array_filter(array_column($stats['rows'], 'name')))
        );

        $rows = collect($stats['rows'])->filter(fn ($row) => isset($row['name']))->keyBy('name');
        // Every unusable cell keeps its seeded value.
        $this->assertSame('9.89%', $rows['Yield']['fund']);
        $this->assertSame('9.10%', $rows['Yield']['benchmark']);
        $this->assertSame('11.60 years', $rows['Weighted average time to maturity']['fund']);
        $this->assertSame('6.16', $rows['Total duration']['fund']);
        $this->assertSame('-0.17', $rows['Total duration']['relative']);
        $this->assertSame('0.02', $rows['— Floating rate duration']['relative']);
        // The feed's explicit "-" (ALBI has no inflation linked / floating paper) is usable.
        $this->assertSame('-', $rows['— Inflation linked duration']['benchmark']);
        $this->assertSame('-', $rows['— Floating rate duration']['benchmark']);
    }

    public function test_bond_statistics_read_the_unprefixed_duration_keys(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-bond']);

        $path = $this->makeXlsx([
            ['Code', 'Value'],
            ['STAT_YIELD', '9.89'],
            ['STAT_WEIGHTED_AVERAGE_TTM', '11.6'],
            ['STAT_SPREAD_TO_JIBAR', '%'],
            ['STAT_DURATION', '6.16'],
            ['STAT_FIXED_RATE_DURATION', '5.02'],
            ['STAT_INFLATION_LINKED_DURATION', '1.12'],
            ['STAT_FLOATING_RATE_DURATION', '0.02'],
            ['BM_YIELD', '9.1'],
            ['BM_WEIGHTED_AVERAGE_TTM', '11.55'],
            ['BM_DURATION', '6.33'],
            ['BM_FIXED_RATE_DURATION', '6.33'],
            ['BM_INFLATION_LINKED_DURATION', '-'],
            ['BM_FLOATING_RATE_DURATION', '-'],
            ['VAR_TO_BM_DURATION', '-0.17'],
            ['VAR_TO_BM_FIXED_RATE_DURATION', '-1.31'],
            ['VAR_TO_BM_INFLATION_LINKED_DURATION', '1.12'],
            ['VAR_TO_BM_FLOATING_RATE_DURATION', '0.02'],
        ], 'bond-stats-values');

        (new FactsheetImporter)->import($fund, $path);

        $rows = collect($fund->asset_allocation['portfolioStatistics']['rows'])
            ->filter(fn ($row) => isset($row['name']))
            ->keyBy('name');

        $this->assertSame('9.89%', $rows['Yield']['fund']);
        $this->assertSame('9.10%', $rows['Yield']['benchmark']);
        $this->assertSame('11.60 years', $rows['Weighted average time to maturity']['fund']);
        $this->assertSame('6.16', $rows['Total duration']['fund']);
        $this->assertSame('6.33', $rows['Total duration']['benchmark']);
        $this->assertSame('-0.17', $rows['Total duration']['relative']);
        $this->assertSame('-1.31', $rows['— Fixed rate duration']['relative']);
        $this->assertSame('-', $rows['— Inflation linked duration']['benchmark']);
        $this->assertSame('0.02', $rows['— Floating rate duration']['relative']);
    }

    public function test_flex_income_statistics_still_take_the_flex_layout(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-flex-income']);

        $path = $this->makeXlsx([
            ['Code', 'Value'],
            ['STAT_YIELD', '9.44'],
            ['STAT_SPREAD_TO_JIBAR', '2.46'],
            ['STAT_SA_DURATION', '0.77'],
        ], 'flex-stats-guard');

        (new FactsheetImporter)->import($fund, $path);

        $stats = $fund->asset_allocation['portfolioStatistics'];
        $this->assertArrayNotHasKey('headers', $stats);
        $rows = collect($stats['rows'])->filter(fn ($row) => isset($row['name']))->keyBy('name');
        // JIBAR was retired in 2026; the 824 sheet now labels this row
        // "Spread to Zaronia" while the feed key stays STAT_SPREAD_TO_JIBAR.
        $this->assertSame('2.46%', $rows['Spread to Zaronia']['value']);
        $this->assertSame('0.77', $rows['SA duration']['value']);
        $this->assertArrayNotHasKey('Total duration', $rows->all());
    }

    public function test_maturity_change_labels_survive_a_bare_percent_export(): void
    {
        $fund = Fund::factory()->create([
            'template' => 'show-bond',
            'chart_data' => [
                'maturityData' => [
                    'title' => 'MATURITY BREAKDOWN',
                    'categories' => [
                        ['name' => '0-1 Year', 'fund' => -16, 'benchmark' => 0, 'change' => '(+0.0%)'],
                        ['name' => '1-3 Years', 'fund' => 19, 'benchmark' => 3.2, 'change' => '(+0.0%)'],
                        ['name' => '3-7 Years', 'fund' => 45, 'benchmark' => 28, 'change' => '(+13.5%)'],
                        ['name' => '7-12 Years', 'fund' => 14, 'benchmark' => 27.6, 'change' => '(-12.3%)'],
                        ['name' => '12-20 Years', 'fund' => 23, 'benchmark' => 23.3, 'change' => '(+0.3%)'],
                        ['name' => '20+ Years', 'fund' => 16, 'benchmark' => 17.2, 'change' => '(-1.5%)'],
                    ],
                ],
            ],
        ]);

        $path = $this->makeXlsx([
            ['Code', 'Value'],
            ['MONTH_END_DATE', '31 August 2026'],
            ['LAST_QUARTER_END', '30 June 2026'],
            ['MATURITY_0_TO_1_YEAR', '-17'],
            ['MAT_CHANGE_0_TO_1_YEARS', '%'],
            ['MATURITY_1_TO_3_YEARS', '20'],
            ['MAT_CHANGE_1_TO_3_YEARS', '%'],
            ['MATURITY_3_TO_7_YEARS', '42'],
            ['MAT_CHANGE_3_TO_7_YEARS', 'ERR'],
            ['MATURITY_7_TO_12_YEARS', '15'],
            ['MAT_CHANGE_7_TO_12_YEARS', '-12.3'],
            ['MATURITY_12_TO_20_YEARS', '24'],
            ['MAT_CHANGE_12_TO_20_YEARS', '0.3'],
            ['MATURITY_20_PLUS_YEARS', '16'],
            ['MAT_CHANGE_20_PLUS_YEARS', '-1.5'],
        ], 'bond-maturity-percent');

        (new FactsheetImporter)->import($fund, $path);

        $categories = collect($fund->chart_data['maturityData']['categories'])->keyBy('name');

        $this->assertSame('Change since 30 June 2026', $fund->chart_data['maturityData']['subtitle']);
        // Fund bars come from the feed; the hand-maintained ALBI bars survive.
        $this->assertSame(-17, $categories['0-1 Year']['fund']);
        $this->assertSame(42, $categories['3-7 Years']['fund']);
        $this->assertSame(28, $categories['3-7 Years']['benchmark']);
        // A bare "%" or ERR keeps the stored label; numeric changes are formatted.
        $this->assertSame('(+0.0%)', $categories['0-1 Year']['change']);
        $this->assertSame('(+13.5%)', $categories['3-7 Years']['change']);
        $this->assertSame('(-12.3%)', $categories['7-12 Years']['change']);
        $this->assertSame('(+0.3%)', $categories['12-20 Years']['change']);
    }
}
