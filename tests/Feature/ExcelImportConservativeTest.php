<?php

namespace Tests\Feature;

use App\Models\Fund;
use App\Services\FundImport\FactsheetImporter;
use App\Services\FundImport\FundImportManager;
use App\Services\FundImport\RollingReturnGraphImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class ExcelImportConservativeTest extends TestCase
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

    public function test_rolling_return_graph_import_maps_published_fund_series(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-conservative']);

        // The export carries decimal fractions; leading months without a full
        // one-year history are empty and must be skipped. The published series
        // is the "<code> Fund Published" column, not the per-class ones.
        $path = $this->makeXlsx([
            ['Start Date', 'Description', '818 Fund Published', '818 A Class [iR]'],
            [41609, 'Nov 2014 (1Y)', null, null],
            [41640, 'Dec 2014 (1Y)', 0.0956252786, 0.0855522803],
            [41671, 'Jan 2015 (1Y)', 0.1229, 0.1085],
        ], 'rolling-test');

        (new RollingReturnGraphImporter)->import($fund, $path);

        $rolling = $fund->chart_data['rollingReturnData'];

        $this->assertCount(2, $rolling);
        $this->assertSame(['date' => '2014-12', 'value' => 9.56], $rolling[0]);
        $this->assertSame(['date' => '2015-01', 'value' => 12.29], $rolling[1]);
    }

    public function test_import_manager_routes_rolling_return_graph_exports(): void
    {
        $manager = new FundImportManager;

        $this->assertInstanceOf(
            RollingReturnGraphImporter::class,
            $manager->importerFor('818_ROLLING_1_YEAR_GRAPH.xlsx')
        );
    }

    public function test_conservative_asset_allocation_uses_sixty_percent_equity_limit(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-conservative']);

        $path = $this->makeXlsx([
            ['Code', 'Value'],
            ['AA_DOM_EQ', '36.6'],
            ['AA_FRGN_EQ', '18.8'],
            ['AA_TOTAL_EQ', '55.4'],
            ['AA_TOTAL_DIFF_EQ', '0.7'],
            ['AA_TOTAL_DIFF_SIGN_EQ', '-'],
            ['AA_DOM_TOTAL', '64.6'],
            ['AA_FRGN_TOTAL', '35.4'],
        ], 'conservative-factsheet');

        (new FactsheetImporter)->import($fund, $path);

        $rows = collect($fund->asset_allocation['rows'])->keyBy('name');

        $this->assertSame('60', $rows['Equities']['limit']);
        $this->assertSame('▼ 0.7', $rows['Equities']['change']);
    }

    public function test_factsheet_import_refreshes_estimated_for_footnote(): void
    {
        $fund = Fund::factory()->create([
            'template' => 'show-conservative',
            'performance_table' => [
                'footnotes' => [
                    '³ Net of fees and expenses',
                    '⁴ Source: Stats SA, performance as calculated by Foord (estimated for March 2026)',
                ],
            ],
        ]);

        $path = $this->makeXlsx([
            ['Code', 'Value'],
            ['MONTH_END_DATE_MMMM_YYYY', 'July 2026'],
            ['FOORD_M_TO_D', '1.2'],
        ], 'conservative-footnote');

        (new FactsheetImporter)->import($fund, $path);

        $this->assertSame(
            '⁴ Source: Stats SA, performance as calculated by Foord (estimated for July 2026)',
            $fund->performance_table['footnotes'][1]
        );
        $this->assertSame('³ Net of fees and expenses', $fund->performance_table['footnotes'][0]);
    }
}
