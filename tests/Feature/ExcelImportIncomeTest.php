<?php

namespace Tests\Feature;

use App\Models\Fund;
use App\Services\FundImport\FactsheetImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class ExcelImportIncomeTest extends TestCase
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

    public function test_income_portfolio_structure_is_single_value_column(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-income']);

        $path = $this->makeXlsx([
            ['Code', 'Value'],
            ['MONTH_END_DATE', '31 July 2026'],
            ['LAST_QUARTER_END', '30 June 2026'],
            ['PS_SA_CASH_AND_CALL', '-3'],
            ['PS_TOTAL_CASH_AND_CALL', '-3'],
            ['PS_TOTAL_CHANGE_CASH_AND_CALL', '1.4'],
            ['PS_TOTAL_CHANGE_SIGN_CASH_AND_CALL', '-'],
            ['PS_TOTAL_MONEY_MARKET', '19'],
            ['PS_TOTAL_CHANGE_MONEY_MARKET', '5.7'],
            ['PS_TOTAL_CHANGE_SIGN_MONEY_MARKET', '-'],
            ['PS_TOTAL_FLOATING_RATE_NOTES', '60'],
            ['PS_TOTAL_CHANGE_FLOATING_RATE_NOTES', '6.5'],
            ['PS_TOTAL_CHANGE_SIGN_FLOATING_RATE_NOTES', '+'],
            ['PS_TOTAL_FIXED_RATE_BONDS', '3'],
            ['PS_TOTAL_CHANGE_FIXED_RATE_BONDS', '0.0'],
            ['PS_TOTAL_CHANGE_SIGN_FIXED_RATE_BONDS', '+'],
            ['PS_TOTAL_FIXED_RATE_NCDS', '-'],
            ['PS_TOTAL_CHANGE_FIXED_RATE_NCDS', '-'],
            ['PS_TOTAL_CHANGE_SIGN_FIXED_RATE_NCDS', ''],
            ['PS_TOTAL_INFLATION_LINKED_BONDS', '21'],
            ['PS_TOTAL_CHANGE_INFLATION_LINKED_BONDS', '0.5'],
            ['PS_TOTAL_CHANGE_SIGN_INFLATION_LINKED_BONDS', '+'],
            // Asset classes outside the published six-row list must be ignored.
            ['PS_TOTAL_PREFERENCE_SHARES', '-'],
            ['PS_TOTAL_PROPERTY', '-'],
            ['PS_SA_TOTAL', '100'],
            ['PS_FOREIGN_TOTAL', '-'],
        ], 'income-structure');

        (new FactsheetImporter)->import($fund, $path);

        $structure = $fund->asset_allocation;

        $this->assertSame('PORTFOLIO STRUCTURE %', $structure['title']);
        $this->assertSame('Change since 30 June 2026', $structure['subtitle']);
        $this->assertSame(['ASSET CLASS', '31 JUL 2026', 'CHANGE'], $structure['headers']);

        // The published 825 sheet lists exactly these six asset classes.
        $this->assertSame(
            ['Cash and call', 'Money market', 'Floating rate notes', 'Fixed rate bonds', 'Fixed rate NCDs', 'Inflation linked bonds'],
            array_column($structure['rows'], 'name')
        );

        $rows = collect($structure['rows'])->keyBy('name');
        // Negative effective exposures print as-is; the sign key drives the arrow.
        $this->assertSame('-3', $rows['Cash and call']['value']);
        $this->assertSame('▼ 1.4', $rows['Cash and call']['change']);
        $this->assertSame('down', $rows['Cash and call']['changeDirection']);
        // A zero change with a "+" sign keeps its up arrow (825 reference).
        $this->assertSame('▲ 0.0', $rows['Fixed rate bonds']['change']);
        // Rows with no change sign print a bare dash, no arrow.
        $this->assertSame('-', $rows['Fixed rate NCDs']['change']);
        $this->assertSame('', $rows['Fixed rate NCDs']['changeDirection']);

        $this->assertSame('TOTAL', $structure['total']['name']);
        $this->assertSame('100', $structure['total']['value']);
        // No SA/FOREIGN split and no foreign-currency rows on the income sheet.
        $this->assertArrayNotHasKey('sa', $structure['rows'][0]);
        $this->assertArrayNotHasKey('foreignCurrencyHedge', $structure);
    }

    public function test_income_statistics_err_cells_preserve_seeded_values(): void
    {
        $fund = Fund::factory()->create([
            'template' => 'show-income',
            'asset_allocation' => [
                'portfolioStatistics' => [
                    'rows' => [
                        ['name' => 'Yield', 'sup' => '1', 'value' => '9.44%'],
                        ['name' => 'SA duration', 'sup' => '2', 'bold' => true, 'value' => '0.77'],
                    ],
                ],
            ],
        ]);

        $path = $this->makeXlsx([
            ['Code', 'Value'],
            ['STAT_YIELD', 'ERR'],
            ['STAT_SPREAD_TO_JIBAR', '2.46'],
            ['STAT_SA_DURATION', 'ERR'],
            ['STAT_SA_FIXED_RATE_DURATION', '0.10'],
            // The income fund holds no offshore assets — the sheet's foreign
            // duration keys must not add rows.
            ['STAT_FOREIGN_DURATION', 'ERR'],
            ['STAT_FOREIGN_FIXED_RATE_DURATION', 'ERR'],
        ], 'income-stats');

        (new FactsheetImporter)->import($fund, $path);

        $rows = collect($fund->asset_allocation['portfolioStatistics']['rows'])
            ->filter(fn ($row) => isset($row['name']))
            ->keyBy('name');

        // The published 825 layout: Yield, Spread to JIBAR, then the SA
        // duration group with bare sub-row labels (no "SA" prefix).
        $this->assertSame(
            ['Yield', 'Spread to JIBAR', 'SA duration', '— Fixed rate duration', '— Floating rate duration', '— Inflation linked duration'],
            $rows->keys()->all()
        );

        // ERR keeps the seeded value; usable cells are formatted per row type.
        $this->assertSame('9.44%', $rows['Yield']['value']);
        $this->assertSame('2.46%', $rows['Spread to JIBAR']['value']);
        $this->assertSame('0.77', $rows['SA duration']['value']);
        $this->assertSame('0.10', $rows['— Fixed rate duration']['value']);
        $this->assertSame('', $rows['— Floating rate duration']['value']);
        $this->assertTrue($rows['SA duration']['bold']);
    }

    public function test_income_maturity_spread_merges_twelve_plus_and_perpetual(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-income']);

        $path = $this->makeXlsx([
            ['Code', 'Value'],
            ['MATURITY_0_TO_1_YEAR', '16'],
            ['MATURITY_1_TO_3_YEARS', '53'],
            ['MATURITY_3_TO_7_YEARS', '31'],
            ['MATURITY_7_TO_12_YEARS', '-'],
            ['MATURITY_12_PLUS_YEARS', '2'],
            ['MATURITY_PERPETUAL', '1'],
            // Bond-only split buckets on the same sheet must be ignored.
            ['MATURITY_12_TO_20_YEARS', '-'],
            ['MATURITY_20_PLUS_YEARS', '-'],
        ], 'income-maturity');

        (new FactsheetImporter)->import($fund, $path);

        $spread = $fund->chart_data['maturitySpread'];

        $this->assertSame('MATURITY SPREAD %', $spread['title']);
        // The 825 reference has no Perpetual row — perpetual paper folds into
        // the "> 12 years" bucket.
        $this->assertSame(
            ['0—1 years', '1—3 years', '3—7 years', '7—12 years', '> 12 years'],
            array_column($spread['categories'], 'name')
        );
        $this->assertSame('-', $spread['categories'][3]['label']);
        $this->assertSame(3, $spread['categories'][4]['value']);
        $this->assertSame('3', $spread['categories'][4]['label']);
        // The income template writes no bond maturityData.
        $this->assertArrayNotHasKey('maturityData', $fund->chart_data);
    }

    public function test_income_tic_omits_zero_performance_charge_row(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-income']);

        $path = $this->makeXlsx([
            ['Code', 'Value'],
            ['SA_TER_TOTAL_EXPENSE_RATIO_12_MONTH', '0.47'],
            ['SA_TER_TOTAL_EXPENSE_RATIO_36_MONTH', '0.44'],
            ['SA_TER_MANAGERS_CHARGE_12_MONTH', '0.30'],
            ['SA_TER_MANAGERS_CHARGE_36_MONTH', '0.30'],
            ['SA_TER_PERFORMANCE_CHARGE_12_MONTH', '0.00'],
            ['SA_TER_PERFORMANCE_CHARGE_36_MONTH', '0.00'],
            ['SA_TER_FOORD_GLOBAL_CHARGE_12_MONTH', '0.00'],
            ['SA_TER_FOORD_GLOBAL_CHARGE_36_MONTH', '0.00'],
            ['SA_TER_VAT_AND_SUNDRY_COSTS_12_MONTH', '0.16'],
            ['SA_TER_VAT_AND_SUNDRY_COSTS_36_MONTH', '0.14'],
            ['SA_TER_TRANSACTIONS_COSTS_INCL_VAT_12_MONTH', '0.00'],
            ['SA_TER_TRANSACTIONS_COSTS_INCL_VAT_36_MONTH', '0.00'],
            ['SA_TER_TOTAL_INVESTMENT_CHARGE_12_MONTH', '0.47'],
            ['SA_TER_TOTAL_INVESTMENT_CHARGE_36_MONTH', '0.44'],
        ], 'income-tic');

        (new FactsheetImporter)->import($fund, $path);

        $names = array_column($fund->fees['totalInvestmentCharge']['rows'], 'name');

        $this->assertNotContains('— Performance charge', $names);
        $this->assertNotContains('— Foord global charges', $names);
        $this->assertSame(
            ['Total expense ratio (TER)', '— Manager’s charge (basic)', '— VAT and sundry costs', 'Transaction costs (incl VAT)'],
            $names
        );
    }
}
