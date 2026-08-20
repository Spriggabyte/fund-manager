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
}
