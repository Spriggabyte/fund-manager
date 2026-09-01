<?php

namespace Tests\Feature;

use App\Models\Fund;
use App\Services\FundImport\FactsheetImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class ExcelImportInflationIncomeTest extends TestCase
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

    public function test_hand_maintained_portfolio_structure_is_preserved(): void
    {
        // The published 827 structure table lists ILB maturity buckets the
        // feed does not carry — the stored rows are seeded from the reference
        // and must survive an import that exports the standard PS_* keys.
        $seededRows = [
            ['name' => 'Money market', 'value' => '4.5', 'change' => '▼ 0.2', 'changeDirection' => 'down'],
            ['name' => 'RSA ILB 2—3 years', 'value' => '19.1', 'change' => '▼ 0.7', 'changeDirection' => 'down'],
        ];

        $fund = Fund::factory()->create([
            'template' => 'show-inflation-income',
            'asset_allocation' => [
                'title' => 'PORTFOLIO STRUCTURE %',
                'subtitle' => 'Change since 31 March 2026',
                'headers' => ['', 'TOTAL', 'CHANGE'],
                'rows' => $seededRows,
                'total' => ['name' => 'TOTAL', 'value' => '100.0', 'change' => ''],
            ],
        ]);

        $path = $this->makeXlsx([
            ['Code', 'Value'],
            ['MONTH_END_DATE', '31 July 2026'],
            ['LAST_QUARTER_END', '30 June 2026'],
            ['PS_SA_CASH_AND_CALL', '-27'],
            ['PS_TOTAL_CASH_AND_CALL', '-27'],
            ['PS_TOTAL_MONEY_MARKET', '29'],
            ['PS_TOTAL_INFLATION_LINKED_BONDS', '96'],
            ['PS_TOTAL_CHANGE_INFLATION_LINKED_BONDS', '0.7'],
            ['PS_TOTAL_CHANGE_SIGN_INFLATION_LINKED_BONDS', '+'],
            ['PS_SA_TOTAL', '100'],
            ['PS_FOREIGN_TOTAL', '-'],
        ], 'inflation-structure');

        (new FactsheetImporter)->import($fund, $path);

        $structure = $fund->asset_allocation;

        $this->assertSame($seededRows, $structure['rows']);
        $this->assertSame('Change since 31 March 2026', $structure['subtitle']);
        $this->assertSame(['', 'TOTAL', 'CHANGE'], $structure['headers']);
        $this->assertSame('100.0', $structure['total']['value']);
    }

    public function test_statistics_are_real_yield_and_duration_with_err_preserve(): void
    {
        $fund = Fund::factory()->create([
            'template' => 'show-inflation-income',
            'asset_allocation' => [
                'portfolioStatistics' => [
                    'rows' => [
                        ['name' => 'Real Yield', 'sup' => '1', 'value' => '4.01%'],
                        ['name' => 'Duration', 'sup' => '2', 'value' => '2.54'],
                    ],
                ],
            ],
        ]);

        $path = $this->makeXlsx([
            ['Code', 'Value'],
            ['STAT_YIELD', 'ERR'],
            ['STAT_SPREAD_TO_JIBAR', '0.00%'],
            ['STAT_SA_DURATION', 'ERR'],
            // Overridden dashes on the 827 feed must not add rows.
            ['STAT_SA_FIXED_RATE_DURATION', '-'],
            ['STAT_FOREIGN_DURATION', '-'],
        ], 'inflation-stats-err');

        (new FactsheetImporter)->import($fund, $path);

        $rows = $fund->asset_allocation['portfolioStatistics']['rows'];

        // The published 827 table has exactly two rows — no Spread to JIBAR,
        // no duration split. ERR cells keep the seeded reference values.
        $this->assertSame(['Real Yield', 'Duration'], array_column($rows, 'name'));
        $this->assertSame('4.01%', $rows[0]['value']);
        $this->assertSame('1', $rows[0]['sup']);
        $this->assertSame('2.54', $rows[1]['value']);
    }

    public function test_statistics_use_feed_values_when_usable(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-inflation-income']);

        $path = $this->makeXlsx([
            ['Code', 'Value'],
            ['STAT_YIELD', '4.2'],
            ['STAT_SPREAD_TO_JIBAR', '0.00%'],
            ['STAT_SA_DURATION', '2.6'],
        ], 'inflation-stats');

        (new FactsheetImporter)->import($fund, $path);

        $rows = $fund->asset_allocation['portfolioStatistics']['rows'];

        $this->assertSame('4.20%', $rows[0]['value']);
        $this->assertSame('2.60', $rows[1]['value']);
    }

    public function test_credit_exposure_keeps_dash_sector_rows(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-inflation-income']);

        $path = $this->makeXlsx([
            ['Code', 'Value'],
            ['RATING_F1_PLUS', '34'],
            ['RATING_F1', '-'],
            ['RATING_AAA', '52'],
            ['RATING_AA', '14'],
            ['RATING_A', '-'],
            ['RATING_OTHER', '-'],
            ['SECTOR_BANK', '13'],
            ['SECTOR_CORP', '12'],
            ['SECTOR_RSA', '74'],
            ['SECTOR_USGOV', '-'],
            ['SECTOR_OTHER', '-'],
        ], 'inflation-credit');

        (new FactsheetImporter)->import($fund, $path);

        $credit = $fund->asset_allocation['creditExposure'];

        // The published 827 sheet prints the FULL fixed sector list with
        // dashes (the other bond-family sheets drop dash sectors).
        $this->assertSame(
            ['Big four banks', 'SA Corporates', 'SA Government', 'US Government', 'Other'],
            array_column($credit['sectors'], 'name')
        );
        $this->assertSame('-', $credit['sectors'][3]['value']);
        $this->assertSame('-', $credit['sectors'][4]['value']);
        $this->assertSame('-', $credit['ratings'][1]['value']);
    }

    public function test_maturity_spread_has_six_buckets_including_perpetual(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-inflation-income']);

        $path = $this->makeXlsx([
            ['Code', 'Value'],
            ['MATURITY_0_TO_1_YEAR', '2'],
            ['MATURITY_1_TO_3_YEARS', '79'],
            ['MATURITY_3_TO_7_YEARS', '19'],
            ['MATURITY_7_TO_12_YEARS', '-'],
            ['MATURITY_12_PLUS_YEARS', '-'],
            ['MATURITY_PERPETUAL', '-'],
            ['MATURITY_12_TO_20_YEARS', '-'],
            ['MATURITY_20_PLUS_YEARS', '-'],
        ], 'inflation-maturity');

        (new FactsheetImporter)->import($fund, $path);

        $spread = $fund->chart_data['maturitySpread'];

        // Unlike the income fund (which folds perpetual into "> 12 years"),
        // the 827 reference lists all six buckets, dashes included.
        $this->assertSame(
            ['0—1 years', '1—3 years', '3—7 years', '7—12 years', '> 12 years', 'Perpetual'],
            array_column($spread['categories'], 'name')
        );
        $this->assertSame('-', $spread['categories'][5]['label']);
        $this->assertArrayNotHasKey('maturityData', $fund->chart_data);
    }

    public function test_tic_omits_zero_performance_charge_row(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-inflation-income']);

        $path = $this->makeXlsx([
            ['Code', 'Value'],
            ['SA_TER_TOTAL_EXPENSE_RATIO_12_MONTH', '0.51'],
            ['SA_TER_TOTAL_EXPENSE_RATIO_36_MONTH', '0.51'],
            ['SA_TER_MANAGERS_CHARGE_12_MONTH', '0.40'],
            ['SA_TER_MANAGERS_CHARGE_36_MONTH', '0.40'],
            ['SA_TER_PERFORMANCE_CHARGE_12_MONTH', '0.00'],
            ['SA_TER_PERFORMANCE_CHARGE_36_MONTH', '0.00'],
            ['SA_TER_FOORD_GLOBAL_CHARGE_12_MONTH', '0.00'],
            ['SA_TER_FOORD_GLOBAL_CHARGE_36_MONTH', '0.00'],
            ['SA_TER_VAT_AND_SUNDRY_COSTS_12_MONTH', '0.11'],
            ['SA_TER_VAT_AND_SUNDRY_COSTS_36_MONTH', '0.11'],
            ['SA_TER_TRANSACTIONS_COSTS_INCL_VAT_12_MONTH', '0.00'],
            ['SA_TER_TRANSACTIONS_COSTS_INCL_VAT_36_MONTH', '0.00'],
            ['SA_TER_TOTAL_INVESTMENT_CHARGE_12_MONTH', '0.52'],
            ['SA_TER_TOTAL_INVESTMENT_CHARGE_36_MONTH', '0.51'],
        ], 'inflation-tic');

        (new FactsheetImporter)->import($fund, $path);

        $this->assertSame(
            ['Total expense ratio (TER)', '— Manager’s charge (basic)', '— VAT and sundry costs', 'Transaction costs (incl VAT)'],
            array_column($fund->fees['totalInvestmentCharge']['rows'], 'name')
        );
    }
}
