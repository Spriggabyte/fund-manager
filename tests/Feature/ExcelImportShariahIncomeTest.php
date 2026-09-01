<?php

namespace Tests\Feature;

use App\Models\Fund;
use App\Services\FundImport\FactsheetImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class ExcelImportShariahIncomeTest extends TestCase
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

    /** The 841 factsheet export, trimmed to the keys under test. */
    private function rows(array $extra = []): array
    {
        return array_merge([
            ['Code', 'Value'],
            ['MONTH_END_DATE', '31 July 2026'],
            ['LAST_QUARTER_END', '30 June 2026'],
            // 841 exports PS_SA_EQUITY (the 840 Shariah-balanced discriminator)
            // AND PS_SA_TOTAL (the flex-income one).
            ['PS_SA_EQUITY', '0.0'],
            ['PS_SA_PROPERTY', '0.5'],
            ['PS_SA_BOND', '24.1'],
            ['PS_SA_CASH', '8.4'],
            ['PS_SA_INCOME', '65.0'],
            ['PS_SA_TOTAL', '97.9'],
            ['PS_FOREIGN_EQUITY', '2.0'],
            ['PS_FOREIGN_PROPERTY', '0.0'],
            ['PS_FOREIGN_BOND', '0.0'],
            ['PS_FOREIGN_CASH', '0.1'],
            ['PS_FOREIGN_INCOME', '0.0'],
            ['PS_FOREIGN_TOTAL', '2.1'],
            ['PS_TOTAL_EQUITY', '2.0'],
            ['PS_TOTAL_PROPERTY', '0.5'],
            ['PS_TOTAL_BOND', '24.1'],
            ['PS_TOTAL_CASH', '8.5'],
            ['PS_TOTAL_INCOME', '65.0'],
            ['PS_TOTAL_CHANGE_EQUITY', '0.1'],
            ['PS_TOTAL_CHANGE_PROPERTY', '0.0'],
            ['PS_TOTAL_CHANGE_BOND', '0.1'],
            ['PS_TOTAL_CHANGE_CASH', '0.2'],
            ['PS_TOTAL_CHANGE_INCOME', '0.0'],
            ['PS_TOTAL_CHANGE_SIGN_EQUITY', '+'],
            ['PS_TOTAL_CHANGE_SIGN_PROPERTY', '+'],
            ['PS_TOTAL_CHANGE_SIGN_BOND', '+'],
            ['PS_TOTAL_CHANGE_SIGN_CASH', '-'],
            ['PS_TOTAL_CHANGE_SIGN_INCOME', '+'],
            ['MATURITY_0_TO_1_YEAR', '31'],
            ['MATURITY_1_TO_3_YEARS', '-'],
            ['MATURITY_3_TO_7_YEARS', '43'],
            ['MATURITY_7_TO_12_YEARS', '24'],
            ['MATURITY_12_PLUS_YEARS', '-'],
            ['MATURITY_PERPETUAL', '2'],
            ['RATING_F1_PLUS', '73'],
            ['RATING_F1', '-'],
            ['RATING_AAA', '24'],
            ['RATING_AA', '-'],
            ['RATING_A', '-'],
            ['RATING_OTHER', '2'],
            ['SECTOR_BANK', '73'],
            ['SECTOR_CORP', '-'],
            ['SECTOR_RSA', '24'],
            ['SECTOR_USGOV', '-'],
            ['SECTOR_OTHER', '2'],
        ], $extra);
    }

    private function import(Fund $fund, array $extra = []): Fund
    {
        $path = $this->makeXlsx($this->rows($extra), 'shariah-income-'.uniqid());
        (new FactsheetImporter)->import($fund, $path);
        $fund->save();

        return $fund->fresh();
    }

    public function test_portfolio_structure_uses_the_shariah_income_category_list(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-shariah-income']);

        $fund = $this->import($fund);

        $rows = $fund->asset_allocation['rows'];

        $this->assertSame(
            ['Cash and call', 'Equities', 'Income', 'Sukuks', 'Property'],
            array_column($rows, 'name'),
            'The Shariah-balanced branch (detected on PS_SA_EQUITY) must not claim this fund.'
        );
        $this->assertSame(['', 'SA', 'FOREIGN', 'TOTAL', 'CHANGE'], $fund->asset_allocation['headers']);
        $this->assertSame('Change since 30 June 2026', $fund->asset_allocation['subtitle']);
    }

    public function test_zero_holdings_print_as_zero_rather_than_a_dash(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-shariah-income']);

        $fund = $this->import($fund);

        $equities = collect($fund->asset_allocation['rows'])->firstWhere('name', 'Equities');

        $this->assertSame('0.0', $equities['sa']);
        $this->assertSame('2.0', $equities['foreign']);
        $this->assertSame('▲ 0.1', $equities['change']);
        $this->assertSame('up', $equities['changeDirection']);
    }

    public function test_total_row_prints_one_decimal_place(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-shariah-income']);

        $fund = $this->import($fund);

        $this->assertSame('100.0', $fund->asset_allocation['total']['total']);
        $this->assertSame('97.9', $fund->asset_allocation['total']['sa']);
        $this->assertArrayNotHasKey('foreignCurrencyHedge', $fund->asset_allocation);
    }

    public function test_a_zero_change_still_prints_its_arrow(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-shariah-income']);

        $fund = $this->import($fund);

        $income = collect($fund->asset_allocation['rows'])->firstWhere('name', 'Income');

        $this->assertSame('▲ 0.0', $income['change']);
    }

    public function test_maturity_spread_keeps_the_twelve_plus_and_perpetual_buckets(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-shariah-income']);

        $fund = $this->import($fund);

        $this->assertSame(
            ['0—1 years', '1—3 years', '3—7 years', '7—12 years', '> 12 years', 'Perpetual'],
            array_column($fund->chart_data['maturitySpread']['categories'], 'name')
        );
        $this->assertSame('-', $fund->chart_data['maturitySpread']['categories'][1]['label']);
    }

    public function test_credit_sector_table_keeps_its_dash_rows(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-shariah-income']);

        $fund = $this->import($fund);

        $this->assertSame(
            ['Big four banks', 'SA Corporates', 'SA Government', 'US Government', 'Other'],
            array_column($fund->asset_allocation['creditExposure']['sectors'], 'name')
        );
    }

    public function test_a_statistic_exported_as_a_bare_percent_sign_keeps_the_stored_value(): void
    {
        $fund = Fund::factory()->create([
            'template' => 'show-shariah-income',
            'asset_allocation' => [
                'portfolioStatistics' => [
                    'rows' => [
                        ['name' => 'Yield', 'sup' => '1', 'value' => '8.60%'],
                        ['name' => 'Spread to JIBAR', 'value' => '1.79%'],
                        ['name' => 'SA duration', 'sup' => '2', 'bold' => true, 'value' => '5.01'],
                    ],
                ],
            ],
        ]);

        // The 2026-07 feed drops the number and keeps the suffix.
        $fund = $this->import($fund, [
            ['STAT_YIELD', '%'],
            ['STAT_SPREAD_TO_JIBAR', '%'],
            ['STAT_SA_DURATION', ''],
        ]);

        $stats = collect($fund->asset_allocation['portfolioStatistics']['rows'])->keyBy('name');

        $this->assertSame('8.60%', $stats['Yield']['value']);
        $this->assertSame('1.79%', $stats['Spread to JIBAR']['value']);
        $this->assertSame('5.01', $stats['SA duration']['value']);
    }

    public function test_a_usable_statistic_still_overwrites_the_stored_value(): void
    {
        $fund = Fund::factory()->create([
            'template' => 'show-shariah-income',
            'asset_allocation' => [
                'portfolioStatistics' => ['rows' => [['name' => 'Yield', 'sup' => '1', 'value' => '8.60%']]],
            ],
        ]);

        $fund = $this->import($fund, [['STAT_YIELD', '9.12'], ['STAT_SPREAD_TO_JIBAR', '2.01']]);

        $stats = collect($fund->asset_allocation['portfolioStatistics']['rows'])->keyBy('name');

        $this->assertSame('9.12%', $stats['Yield']['value']);
    }

    public function test_the_shariah_balanced_fund_still_uses_its_own_allocation_branch(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-shariah']);

        $fund = $this->import($fund);

        $this->assertNotSame(
            ['Cash and call', 'Equities', 'Income', 'Sukuks', 'Property'],
            array_column($fund->asset_allocation['rows'], 'name'),
            'Fund 840 must keep mapShariahAssetAllocation.'
        );
    }

    public function test_performance_table_prints_only_the_fund_and_benchmark_rows(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-shariah-income']);

        $fund = $this->import($fund, [
            ['FOORD_I_TO_D', '8.0'],
            ['FOORD_CASH_VALUE', 'R 110,005'],
            ['FOORD_HIGHEST_INCEPTION', '8.1'],
            ['FOORD_LOWEST_INCEPTION', '8.0'],
            ['FOORD_COMP_1_I_TO_D', '7.1'],
            ['FOORD_COMP_1_CASH_VALUE', 'R 108,909'],
        ]);

        $this->assertSame(['Fund', 'Benchmark'], array_column($fund->performance_table['rows'], 'name'));
    }

    public function test_tic_drops_the_zero_performance_charge_row(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-shariah-income']);

        $fund = $this->import($fund, [
            ['SA_TER_TOTAL_EXPENSE_RATIO_12_MONTH', '0.55'],
            ['SA_TER_TOTAL_EXPENSE_RATIO_36_MONTH', '0.54'],
            ['SA_TER_MANAGERS_CHARGE_12_MONTH', '0.00'],
            ['SA_TER_MANAGERS_CHARGE_36_MONTH', '0.00'],
            ['SA_TER_PERFORMANCE_CHARGE_12_MONTH', '0.00'],
            ['SA_TER_PERFORMANCE_CHARGE_36_MONTH', '0.00'],
            ['SA_TER_FOORD_GLOBAL_CHARGE_12_MONTH', '0.00'],
            ['SA_TER_FOORD_GLOBAL_CHARGE_36_MONTH', '0.00'],
            ['SA_TER_VAT_AND_SUNDRY_COSTS_12_MONTH', '0.55'],
            ['SA_TER_VAT_AND_SUNDRY_COSTS_36_MONTH', '0.54'],
            ['SA_TER_TRANSACTIONS_COSTS_INCL_VAT_12_MONTH', '0.02'],
            ['SA_TER_TRANSACTIONS_COSTS_INCL_VAT_36_MONTH', '0.01'],
            ['SA_TER_TOTAL_INVESTMENT_CHARGE_12_MONTH', '0.57'],
            ['SA_TER_TOTAL_INVESTMENT_CHARGE_36_MONTH', '0.56'],
        ]);

        $this->assertSame([
            'Total expense ratio (TER)',
            '— Manager’s charge (basic)',
            '— VAT and sundry costs',
            'Transaction costs (incl VAT)',
        ], array_column($fund->fees['totalInvestmentCharge']['rows'], 'name'));
    }
}
