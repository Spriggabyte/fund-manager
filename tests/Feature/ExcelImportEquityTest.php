<?php

namespace Tests\Feature;

use App\Models\Fund;
use App\Services\FundImport\AlsiGraphImporter;
use App\Services\FundImport\CostReg28GraphImporter;
use App\Services\FundImport\FactsheetImporter;
use App\Services\FundImport\FundImportManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class ExcelImportEquityTest extends TestCase
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

    public function test_alsi_graph_import_maps_monthly_relative_returns(): void
    {
        $fund = Fund::factory()->create([
            'chart_data' => ['monthlyData' => [['date' => 'stub', 'relative' => 0, 'benchmarkNegative' => false]]],
        ]);

        // Excel serials: 37529 = Sep 2002, 37560 = Oct 2002, 37590 = Nov 2002
        $path = $this->makeXlsx([
            ['Month End Date', 'Graph Name', 'Field Name 1', 'Value 1', 'Field Name 2', 'Value 2'],
            [37529, 'FS.811:GRAPH_1', 'Relative Return (Alsi -ve)', 3.39808656, 'Relative Return (Alsi +ve)', 0],
            [37560, 'FS.811:GRAPH_1', 'Relative Return (Alsi -ve)', 0, 'Relative Return (Alsi +ve)', 2.501],
            [37590, 'FS.811:GRAPH_1', 'Relative Return (Alsi -ve)', 0, 'Relative Return (Alsi +ve)', -1.024],
        ], 'alsi-test');

        (new AlsiGraphImporter)->import($fund, $path);

        $monthly = $fund->chart_data['monthlyData'];

        $this->assertCount(3, $monthly);
        $this->assertSame(['date' => '2002-09', 'relative' => 3.4, 'benchmarkNegative' => true], $monthly[0]);
        $this->assertSame(['date' => '2002-10', 'relative' => 2.5, 'benchmarkNegative' => false], $monthly[1]);
        // A negative "+ve" value: fund lagged a rising market.
        $this->assertSame(['date' => '2002-11', 'relative' => -1.02, 'benchmarkNegative' => false], $monthly[2]);
    }

    public function test_factsheet_import_maps_equity_asset_allocation(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-equity']);

        $path = $this->makeXlsx([
            ['MONTH_END_DATE', '28 February 2026'],
            ['AA_SHARE_CURRENT', '92'], ['AA_SHARE_PRIOR', '96'],
            ['AA_RES_CURRENT', '23'], ['AA_RES_PRIOR', '21'],
            ['AA_FIN_CURRENT', '16'], ['AA_FIN_PRIOR', '19'],
            ['AA_IND_CURRENT', '52'], ['AA_IND_PRIOR', '56'],
            ['AA_PROPERTY_CURRENT', '3'], ['AA_PROPERTY_PRIOR', '3'],
            ['AA_COMMOD_CURRENT', '-'], ['AA_COMMOD_PRIOR', '-'],
            ['AA_CASH_CURRENT', '6'], ['AA_CASH_PRIOR', '1'],
        ], 'equity-aa-test');

        (new FactsheetImporter)->import($fund, $path);

        $aa = $fund->asset_allocation;

        $this->assertSame(['', '28 FEB 2026', '31 JAN 2026'], $aa['headers']);
        $this->assertSame('JSE equity securities', $aa['rows'][0]['name']);
        $this->assertSame('92', $aa['rows'][0]['current']);
        $this->assertSame('96', $aa['rows'][0]['previous']);
        $this->assertTrue($aa['rows'][1]['indent']); // — Resources
        $this->assertSame('-', $aa['rows'][5]['current']); // Commodities dash preserved
        $total = end($aa['rows']);
        $this->assertTrue($total['isTotal']);
        $this->assertSame('100', $total['current']);
    }

    public function test_factsheet_import_maps_sector_allocation_with_derived_directions(): void
    {
        $fund = Fund::factory()->create([
            'template' => 'show-equity',
            'sector_allocation' => [
                'title' => 'EQUITY SECTOR ALLOCATION %',
                'subtitle' => 'Change since 31 December 2025',
                'sectors' => [
                    ['name' => 'Consumer/services', 'value' => 29, 'change' => '0.1', 'direction' => 'down'],
                    ['name' => 'Precious metals', 'value' => 17, 'change' => '2.4', 'direction' => 'up'],
                    ['name' => 'Commodity cyclicals', 'value' => 5, 'change' => '0.1', 'direction' => 'up'],
                ],
            ],
        ]);

        $path = $this->makeXlsx([
            ['MONTH_END_DATE', '28 February 2026'],
            ['ESAOT_RANK_1_ITEM', 'Consumer/services'],
            ['ESAOT_RANK_1_CURRENT', '26'], ['ESAOT_RANK_1_CHANGE', '3.3'],
            ['ESAOT_RANK_2_ITEM', 'Precious metals'],
            ['ESAOT_RANK_2_CURRENT', '21'], ['ESAOT_RANK_2_CHANGE', '3.8'],
            ['ESAOT_RANK_3_ITEM', 'Commodity cyclicals'],
            ['ESAOT_RANK_3_CURRENT', '5'], ['ESAOT_RANK_3_CHANGE', '0.3'],
        ], 'equity-sector-test');

        (new FactsheetImporter)->import($fund, $path);

        $sectors = $fund->sector_allocation['sectors'];

        $this->assertSame('Change since 31 January 2026', $fund->sector_allocation['subtitle']);
        // 29 → 26: derived down
        $this->assertSame(['name' => 'Consumer/services', 'value' => 26, 'change' => '3.3', 'direction' => 'down'], $sectors[0]);
        // 17 → 21: derived up
        $this->assertSame('up', $sectors[1]['direction']);
        // 5 → 5 tie: keeps the previously stored direction
        $this->assertSame('up', $sectors[2]['direction']);
    }

    /**
     * The 811 equity sheet compares ASSET ALLOCATION and EQUITY SECTOR
     * ALLOCATION against the last QUARTER end, not the prior month (Helena
     * comparison spreadsheet finding #7/#8, QC 2026-09-14/15). The feed's
     * own AA_*_PRIOR / ESAOT_*_CHANGE columns are only quarter-end-accurate
     * on the first month of a quarter, so the importer must carry a
     * baseline forward across a quarter for the second and third monthly
     * imports to compare correctly.
     */
    public function test_factsheet_import_compares_equity_allocations_to_last_quarter_end(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-equity']);

        // June 2026: a quarter-end sheet. Its CURRENT column becomes the
        // baseline the next two monthly sheets compare against.
        (new FactsheetImporter)->import($fund, $this->makeXlsx([
            ['MONTH_END_DATE', '30 June 2026'],
            ['LAST_QUARTER_END', '31 March 2026'],
            ['AA_SHARE_CURRENT', '87'], ['AA_SHARE_PRIOR', '87'],
            ['AA_PROPERTY_CURRENT', '2'], ['AA_PROPERTY_PRIOR', '2'],
            ['AA_CASH_CURRENT', '11'], ['AA_CASH_PRIOR', '11'],
            ['ESAOT_RANK_1_ITEM', 'Consumer/services'], ['ESAOT_RANK_1_CURRENT', '29'], ['ESAOT_RANK_1_CHANGE', '29.0'],
        ], 'equity-q-june'));

        // July 2026: first month of the new quarter. The feed's PRIOR
        // column genuinely is the June quarter-end snapshot.
        (new FactsheetImporter)->import($fund, $this->makeXlsx([
            ['MONTH_END_DATE', '31 July 2026'],
            ['LAST_QUARTER_END', '30 June 2026'],
            ['AA_SHARE_CURRENT', '86'], ['AA_SHARE_PRIOR', '87'],
            ['AA_PROPERTY_CURRENT', '3'], ['AA_PROPERTY_PRIOR', '2'],
            ['AA_CASH_CURRENT', '11'], ['AA_CASH_PRIOR', '11'],
            ['ESAOT_RANK_1_ITEM', 'Consumer/services'], ['ESAOT_RANK_1_CURRENT', '30'],
            ['ESAOT_RANK_1_CHANGE_SIGN', '+'], ['ESAOT_RANK_1_CHANGE', '0.8'],
        ], 'equity-q-july'));

        // August 2026: second month of the quarter. The feed's own PRIOR
        // column is now July (month-over-month), so the importer must use
        // the carried-forward June baseline instead.
        (new FactsheetImporter)->import($fund, $this->makeXlsx([
            ['MONTH_END_DATE', '31 August 2026'],
            ['LAST_QUARTER_END', '30 June 2026'],
            ['AA_SHARE_CURRENT', '89'], ['AA_SHARE_PRIOR', '86'],
            ['AA_PROPERTY_CURRENT', '3'], ['AA_PROPERTY_PRIOR', '3'],
            ['AA_CASH_CURRENT', '7'], ['AA_CASH_PRIOR', '11'],
            ['ESAOT_RANK_1_ITEM', 'Consumer/services'], ['ESAOT_RANK_1_CURRENT', '29'],
            ['ESAOT_RANK_1_CHANGE_SIGN', '-'], ['ESAOT_RANK_1_CHANGE', '0.1'],
        ], 'equity-q-august'));

        $aa = $fund->asset_allocation;
        $this->assertSame(['', '31 AUG 2026', '30 JUN 2026'], $aa['headers']);
        $this->assertSame('87', $aa['rows'][0]['previous']); // JSE equity securities vs June, not July's 86
        $this->assertSame('2', $aa['rows'][1]['previous']); // JSE property vs June, not July's 3

        $this->assertSame('Change since 30 June 2026', $fund->sector_allocation['subtitle']);
    }

    public function test_factsheet_import_refreshes_chart_description_and_scalars(): void
    {
        $fund = Fund::factory()->create([
            'template' => 'show-equity',
            'chart_description' => 'The chart illustrates that the portfolio has outperformed the benchmark 71% of the time when the market was down.',
        ]);

        $path = $this->makeXlsx([
            ['MONTH_END_DATE', '28 February 2026'],
            ['PORTFOLIO_SIZE', '5.0 billion'],
            ['BETTER_THAN_ALSI_WHEN_ALSI_NEGATIVE', '72'],
            ['LAST_DISTRIBUTION_DATE', '30/09/2025'],
            ['LAST_DISTRIBUTION_AMOUNT', '249.20 cents'],
        ], 'equity-scalar-test');

        (new FactsheetImporter)->import($fund, $path);

        $this->assertStringContainsString('outperformed the benchmark 72% of the time', $fund->chart_description);
        $this->assertSame('R5.0 billion', $fund->portfolio_size);
        // Equity design shows distributions without the colon separator.
        $this->assertSame('30/09/2025 249.20 cents', $fund->last_distributions);
    }

    public function test_factsheet_import_skips_zero_foord_global_charges_row(): void
    {
        $fund = Fund::factory()->create();

        $path = $this->makeXlsx([
            ['SA_TER_TOTAL_EXPENSE_RATIO_12_MONTH', '0.74'],
            ['SA_TER_TOTAL_EXPENSE_RATIO_36_MONTH', '1.41'],
            ['SA_TER_MANAGERS_CHARGE_12_MONTH', '1.00'],
            ['SA_TER_MANAGERS_CHARGE_36_MONTH', '1.00'],
            ['SA_TER_FOORD_GLOBAL_CHARGE_12_MONTH', '0.00'],
            ['SA_TER_FOORD_GLOBAL_CHARGE_36_MONTH', '0.00'],
        ], 'tic-test');

        (new FactsheetImporter)->import($fund, $path);

        $names = array_column($fund->fees['totalInvestmentCharge']['rows'], 'name');

        $this->assertContains('Total expense ratio (TER)', $names);
        $this->assertNotContains('— Foord global charges', $names);
    }

    public function test_import_manager_routes_files_by_name_and_reports_unknown(): void
    {
        $manager = new FundImportManager;

        $this->assertInstanceOf(FactsheetImporter::class, $manager->importerFor('811A_FACTSHEET.xlsx'));
        $this->assertInstanceOf(AlsiGraphImporter::class, $manager->importerFor('811_ALSI_GRAPH.xlsx'));
        $this->assertInstanceOf(CostReg28GraphImporter::class, $manager->importerFor('817A_COST_REG28_GRAPH.xlsx'));
        $this->assertNull($manager->importerFor('811_UNKNOWN_EXPORT.xlsx'));
    }
}
