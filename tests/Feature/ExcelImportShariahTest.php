<?php

namespace Tests\Feature;

use App\Models\Fund;
use App\Services\FundImport\FactsheetImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class ExcelImportShariahTest extends TestCase
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

    /**
     * @param  list<array{0: string, 1: string}>  $extra
     */
    private function shariahAllocationRows(array $extra = []): array
    {
        return array_merge([
            ['Code', 'Value'],
            ['LAST_QUARTER_END', '30 June 2026'],
            ['PS_SA_EQUITY', '33.0'],
            ['PS_SA_PROPERTY', '3.1'],
            ['PS_SA_BOND', '1.9'],
            ['PS_SA_COMM', '3.7'],
            ['PS_SA_INCOME', '14.5'],
            ['PS_SA_TOTAL', '56.2'],
            ['PS_FOREIGN_EQUITY', '35.5'],
            ['PS_FOREIGN_PROPERTY', '0.0'],
            ['PS_FOREIGN_BOND', '7.0'],
            ['PS_FOREIGN_COMM', '0.0'],
            ['PS_FOREIGN_INCOME', '1.3'],
            ['PS_FOREIGN_TOTAL', '43.8'],
            ['PS_TOTAL_EQUITY', '68.4'],
            ['PS_TOTAL_PROPERTY', '3.1'],
            ['PS_TOTAL_BOND', '8.9'],
            ['PS_TOTAL_COMM', '3.7'],
            ['PS_TOTAL_INCOME', '15.8'],
            ['PS_TOTAL_CHANGE_EQUITY', '0.4'],
            ['PS_TOTAL_CHANGE_PROPERTY', '0.0'],
            ['PS_TOTAL_CHANGE_BOND', '0.0'],
            ['PS_TOTAL_CHANGE_COMM', '0.0'],
            ['PS_TOTAL_CHANGE_INCOME', '0.4'],
            ['PS_TOTAL_CHANGE_SIGN_EQUITY', '-'],
            ['PS_TOTAL_CHANGE_SIGN_PROPERTY', '-'],
            ['PS_TOTAL_CHANGE_SIGN_BOND', '+'],
            ['PS_TOTAL_CHANGE_SIGN_COMM', '-'],
            ['PS_TOTAL_CHANGE_SIGN_INCOME', '+'],
        ], $extra);
    }

    public function test_shariah_asset_allocation_maps_sa_foreign_total_columns(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-shariah']);

        $path = $this->makeXlsx($this->shariahAllocationRows(), 'shariah_factsheet');
        (new FactsheetImporter)->import($fund, $path);
        $fund->save();

        $aa = $fund->fresh()->asset_allocation;

        $this->assertSame(['', 'SA (100)', 'FOREIGN (45)', 'TOTAL', 'CHANGE'], $aa['headers']);
        $this->assertSame('Change since 30 June 2026', $aa['subtitle']);

        // The debt row is Shariah-compliant Sukuk, and there is no separate
        // money-market row — cash sits inside Income.
        $this->assertSame(
            ['Equities', 'Listed property', 'Sukuk', 'Commodities', 'Income'],
            array_column($aa['rows'], 'name')
        );
        $this->assertSame(['75', '25', '50', '10', '100'], array_column($aa['rows'], 'limit'));

        // toNumber() narrows whole numbers to int; the blade formats every
        // cell to one decimal at render time, so compare loosely here.
        $rows = collect($aa['rows'])->keyBy('name');
        $this->assertEquals(33.0, $rows['Equities']['sa']);
        $this->assertEquals(35.5, $rows['Equities']['foreign']);
        $this->assertEquals(68.4, $rows['Equities']['total']);

        $this->assertEquals(56.2, $aa['total']['sa']);
        $this->assertEquals(43.8, $aa['total']['foreign']);
        $this->assertEquals(100, $aa['total']['total']);
    }

    public function test_shariah_change_arrow_is_omitted_when_the_change_is_zero(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-shariah']);

        $path = $this->makeXlsx($this->shariahAllocationRows(), 'shariah_factsheet_arrows');
        (new FactsheetImporter)->import($fund, $path);
        $fund->save();

        $rows = collect($fund->fresh()->asset_allocation['rows'])->keyBy('name');

        $this->assertSame('▼ 0.4', $rows['Equities']['change']);
        $this->assertSame('down', $rows['Equities']['changeDirection']);
        $this->assertSame('▲ 0.4', $rows['Income']['change']);
        $this->assertSame('up', $rows['Income']['changeDirection']);

        // Sukuk exports a "+" sign against a 0.0 change; the published sheet
        // prints the bare number, so no arrow may be rendered.
        $this->assertSame('0.0', $rows['Sukuk']['change']);
        $this->assertSame('', $rows['Sukuk']['changeDirection']);
        $this->assertSame('0.0', $rows['Listed property']['change']);
        $this->assertSame('', $rows['Listed property']['changeDirection']);
    }

    public function test_shariah_allocation_is_not_claimed_by_the_flex_income_branch(): void
    {
        // Regression guard: 840 exports PS_SA_TOTAL as well, so a Shariah fund
        // routed by that key alone would be mapped onto the flex-income
        // cash/bond category list instead of an asset allocation.
        $fund = Fund::factory()->create(['template' => 'show-shariah']);

        $path = $this->makeXlsx($this->shariahAllocationRows(), 'shariah_branch');
        (new FactsheetImporter)->import($fund, $path);
        $fund->save();

        $aa = $fund->fresh()->asset_allocation;

        $this->assertArrayHasKey('total', $aa);
        $this->assertContains('Sukuk', array_column($aa['rows'], 'name'));
        $this->assertNotContains('Cash and call', array_column($aa['rows'], 'name'));
        $this->assertNotContains('Money market', array_column($aa['rows'], 'name'));
    }

    public function test_shariah_top_ten_asset_classes_are_pluralised(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-shariah']);

        $path = $this->makeXlsx([
            ['Code', 'Value'],
            ['TOPX_SECURITY_1', 'NewGold'],
            ['TOPX_ASSET_CLASS_1', 'Commodity'],
            ['TOPX_MARKET_1', 'ZAF'],
            ['TOPX_PERCENT_OF_FUNDS_1', '3.7'],
            ['TOPX_SECURITY_2', 'AngloGold Ashanti'],
            ['TOPX_ASSET_CLASS_2', 'Equity'],
            ['TOPX_MARKET_2', 'USA'],
            ['TOPX_PERCENT_OF_FUNDS_2', '2.8'],
            ['TOPX_SECURITY_3', 'Dow Jones Islamic ETF'],
            ['TOPX_ASSET_CLASS_3', 'Foreign assets'],
            ['TOPX_MARKET_3', 'GBR'],
            ['TOPX_PERCENT_OF_FUNDS_3', '5.8'],
        ], 'shariah_top10');
        (new FactsheetImporter)->import($fund, $path);
        $fund->save();

        $this->assertSame(
            ['Commodities', 'Equities', 'Foreign assets'],
            array_column($fund->fresh()->top_investments['rows'], 'assetClass')
        );
    }

    public function test_other_templates_keep_the_feeds_singular_asset_classes(): void
    {
        // Twenty signed-off fact sheets publish the feed's singular wording;
        // the pluralisation must stay scoped to the Shariah template.
        $fund = Fund::factory()->create(['template' => 'show']);

        $path = $this->makeXlsx([
            ['Code', 'Value'],
            ['TOPX_SECURITY_1', 'NewGold'],
            ['TOPX_ASSET_CLASS_1', 'Commodity'],
            ['TOPX_MARKET_1', 'ZAF'],
            ['TOPX_PERCENT_OF_FUNDS_1', '3.7'],
        ], 'balanced_top10');
        (new FactsheetImporter)->import($fund, $path);
        $fund->save();

        $this->assertSame('Commodity', $fund->fresh()->top_investments['rows'][0]['assetClass']);
    }

    public function test_shariah_performance_table_has_no_highest_lowest_rows(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-shariah']);

        $path = $this->makeXlsx([
            ['Code', 'Value'],
            ['FOORD_CASH_VALUE', 'R 120,461'],
            ['FOORD_I_TO_D', '10.5'],
            ['FOORD_1Y_TO_D', '12.7'],
            ['FOORD_COMP_1_CASH_VALUE', 'R 115,591'],
            ['FOORD_COMP_1_I_TO_D', '8.1'],
            ['FOORD_HIGHEST_INCEPTION', '22.0'],
            ['FOORD_LOWEST_INCEPTION', '10.4'],
        ], 'shariah_performance');
        (new FactsheetImporter)->import($fund, $path);
        $fund->save();

        $names = array_column($fund->fresh()->performance_table['rows'], 'name');

        $this->assertSame(['Fund', 'Benchmark'], $names);
    }
}
