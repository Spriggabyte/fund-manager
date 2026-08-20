<?php

namespace Tests\Feature;

use App\Models\Fund;
use App\Services\FundImport\FactsheetImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class ExcelImportFlexIncomeTest extends TestCase
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

    public function test_portfolio_structure_maps_rows_arrows_and_fx_lines(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-flex-income']);

        $path = $this->makeXlsx([
            ['Code', 'Value'],
            ['PS_SA_CASH_AND_CALL', '-5'],
            ['PS_FOREIGN_CASH_AND_CALL', '2'],
            ['PS_TOTAL_CASH_AND_CALL', '-3'],
            ['PS_TOTAL_CHANGE_CASH_AND_CALL', '0.2'],
            ['PS_TOTAL_CHANGE_SIGN_CASH_AND_CALL', '-'],
            ['PS_SA_PREFERENCE_SHARES', '0'],
            ['PS_TOTAL_PREFERENCE_SHARES', '0'],
            ['PS_TOTAL_CHANGE_PREFERENCE_SHARES', '0.0'],
            ['PS_TOTAL_CHANGE_SIGN_PREFERENCE_SHARES', '+'],
            ['PS_TOTAL_FIXED_RATE_NCDS', '-'],
            ['PS_TOTAL_CHANGE_FIXED_RATE_NCDS', '-'],
            ['PS_TOTAL_CHANGE_SIGN_FIXED_RATE_NCDS', ''],
            ['PS_SA_TOTAL', '90'],
            ['PS_FOREIGN_TOTAL', '10'],
            ['FOREIGN_CURRENCY_HEDGE', '6'],
            ['FOREIGN_CURRENCY_EXPOSURE', '4'],
            ['LAST_QUARTER_END', '31 December 2025'],
        ], 'flex-structure');

        (new FactsheetImporter)->import($fund, $path);

        $structure = $fund->asset_allocation;
        $rows = collect($structure['rows'])->keyBy('name');

        $this->assertSame('PORTFOLIO STRUCTURE %', $structure['title']);
        $this->assertSame('Change since 31 December 2025', $structure['subtitle']);
        $this->assertSame(['', 'SA', 'FOREIGN', 'TOTAL', 'CHANGE'], $structure['headers']);

        // Negative effective exposures print as-is; the sign key drives the arrow.
        $this->assertSame('-5', $rows['Cash and call']['sa']);
        $this->assertSame('▼ 0.2', $rows['Cash and call']['change']);
        $this->assertSame('down', $rows['Cash and call']['changeDirection']);

        // Zero holdings print as dashes but a zero change keeps its arrow.
        $this->assertSame('-', $rows['Preference shares']['sa']);
        $this->assertSame('▲ 0.0', $rows['Preference shares']['change']);

        // Rows with no change sign print a bare dash, no arrow.
        $this->assertSame('-', $rows['Fixed rate NCDs']['change']);
        $this->assertSame('', $rows['Fixed rate NCDs']['changeDirection']);

        $this->assertSame('90', $structure['total']['sa']);
        $this->assertSame('100', $structure['total']['total']);
        // Hedge prints in accounting brackets; both sit under FOREIGN.
        $this->assertSame('(6)', $structure['foreignCurrencyHedge']);
        $this->assertSame('4', $structure['foreignCurrencyExposure']);
    }

    public function test_flex_statistics_err_cells_preserve_seeded_values(): void
    {
        $fund = Fund::factory()->create([
            'template' => 'show-flex-income',
            'asset_allocation' => [
                'portfolioStatistics' => [
                    'rows' => [
                        ['name' => 'Yield', 'sup' => '1', 'value' => '8.75%'],
                        ['name' => 'SA duration', 'sup' => '2', 'bold' => true, 'value' => '0.93'],
                    ],
                ],
            ],
        ]);

        $path = $this->makeXlsx([
            ['Code', 'Value'],
            ['STAT_YIELD', 'ERR'],
            ['STAT_SPREAD_TO_JIBAR', '2.04'],
            ['STAT_SA_DURATION', 'ERR'],
            ['STAT_FOREIGN_DURATION', '0.21'],
        ], 'flex-stats');

        (new FactsheetImporter)->import($fund, $path);

        $rows = collect($fund->asset_allocation['portfolioStatistics']['rows'])
            ->filter(fn ($row) => isset($row['name']))
            ->keyBy('name');

        // ERR keeps the seeded value; usable cells are formatted per row type.
        $this->assertSame('8.75%', $rows['Yield']['value']);
        $this->assertSame('2.04%', $rows['Spread to JIBAR']['value']);
        $this->assertSame('0.93', $rows['SA duration']['value']);
        $this->assertSame('0.21', $rows['Offshore duration']['value']);
        // Nothing stored + no feed value renders empty.
        $this->assertSame('', $rows['— Offshore inflation linked']['value']);
        // The duration group rows keep their bold display flag.
        $this->assertTrue($rows['SA duration']['bold']);
    }

    public function test_flex_maturity_spread_uses_twelve_plus_and_perpetual_buckets(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-flex-income']);

        $path = $this->makeXlsx([
            ['Code', 'Value'],
            ['MATURITY_0_TO_1_YEAR', '39'],
            ['MATURITY_1_TO_3_YEARS', '37'],
            ['MATURITY_3_TO_7_YEARS', '24'],
            ['MATURITY_7_TO_12_YEARS', '1'],
            ['MATURITY_12_PLUS_YEARS', '-'],
            ['MATURITY_PERPETUAL', '0'],
            // Bond-only split buckets on the same sheet must be ignored.
            ['MATURITY_12_TO_20_YEARS', '-'],
            ['MATURITY_20_PLUS_YEARS', '-'],
        ], 'flex-maturity');

        (new FactsheetImporter)->import($fund, $path);

        $spread = $fund->chart_data['maturitySpread'];

        $this->assertSame('MATURITY SPREAD %', $spread['title']);
        $this->assertSame(
            ['0—1 years', '1—3 years', '3—7 years', '7—12 years', '> 12 years', 'Perpetual'],
            array_column($spread['categories'], 'name')
        );
        $this->assertSame(39, $spread['categories'][0]['value']);
        $this->assertSame('39', $spread['categories'][0]['label']);
        // Zero or dashed buckets keep a zero-length bar labelled "-".
        $this->assertSame('-', $spread['categories'][4]['label']);
        $this->assertSame('-', $spread['categories'][5]['label']);
        // The flex template writes no bond maturityData.
        $this->assertArrayNotHasKey('maturityData', $fund->chart_data);
    }

    public function test_flex_tic_omits_zero_performance_charge_row(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-flex-income']);

        $path = $this->makeXlsx([
            ['Code', 'Value'],
            ['SA_TER_TOTAL_EXPENSE_RATIO_12_MONTH', '0.66'],
            ['SA_TER_TOTAL_EXPENSE_RATIO_36_MONTH', '0.65'],
            ['SA_TER_MANAGERS_CHARGE_12_MONTH', '0.50'],
            ['SA_TER_MANAGERS_CHARGE_36_MONTH', '0.50'],
            ['SA_TER_PERFORMANCE_CHARGE_12_MONTH', '0.00'],
            ['SA_TER_PERFORMANCE_CHARGE_36_MONTH', '0.00'],
            ['SA_TER_FOORD_GLOBAL_CHARGE_12_MONTH', '0.00'],
            ['SA_TER_FOORD_GLOBAL_CHARGE_36_MONTH', '0.00'],
            ['SA_TER_VAT_AND_SUNDRY_COSTS_12_MONTH', '0.15'],
            ['SA_TER_VAT_AND_SUNDRY_COSTS_36_MONTH', '0.15'],
            ['SA_TER_TRANSACTIONS_COSTS_INCL_VAT_12_MONTH', '0.01'],
            ['SA_TER_TRANSACTIONS_COSTS_INCL_VAT_36_MONTH', '0.01'],
            ['SA_TER_TOTAL_INVESTMENT_CHARGE_12_MONTH', '0.67'],
            ['SA_TER_TOTAL_INVESTMENT_CHARGE_36_MONTH', '0.66'],
        ], 'flex-tic');

        (new FactsheetImporter)->import($fund, $path);

        $names = array_column($fund->fees['totalInvestmentCharge']['rows'], 'name');

        $this->assertNotContains('— Performance charge', $names);
        $this->assertNotContains('— Foord global charges', $names);
        $this->assertContains('— VAT and sundry costs', $names);
    }
}
