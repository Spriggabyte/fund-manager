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

    public function test_portfolio_structure_maps_numbered_ps_item_rows(): void
    {
        $fund = Fund::factory()->create([
            'template' => 'show-inflation-income',
            'asset_allocation' => [
                'title' => 'PORTFOLIO STRUCTURE %',
                'headers' => ['', 'TOTAL', 'CHANGE'],
                'rows' => [['name' => 'RSA ILB 4—8 years', 'value' => '8.2', 'change' => '▼ 0.2', 'changeDirection' => 'down']],
            ],
        ]);

        // Trello 110: the re-exported August 2026 827 feed (first three rows).
        $path = $this->makeXlsx([
            ['Code', 'Value'],
            ['MONTH_END_DATE', '31 August 2026'],
            ['LAST_QUARTER_END', '30 June 2026'],
            ['PS_ITEM_NAME_1', 'Money Market'],
            ['PS_ITEM_WEIGHT_1', '2.5'],
            ['PS_TOTAL_CHANGE_1', '2.0'],
            ['PS_TOTAL_CHANGE_SIGN_1', '-'],
            ['PS_ITEM_NAME_2', 'RSA ILB 1-2 years'],
            ['PS_ITEM_WEIGHT_2', '16.8'],
            ['PS_TOTAL_CHANGE_2', '2.3'],
            ['PS_TOTAL_CHANGE_SIGN_2', '-'],
            ['PS_ITEM_NAME_3', 'Replica ILB 3-5 years'],
            ['PS_ITEM_WEIGHT_3', '3.1'],
            ['PS_TOTAL_CHANGE_3', '0.0'],
            ['PS_TOTAL_CHANGE_SIGN_3', '-'],
        ], 'inflation-structure');

        (new FactsheetImporter)->import($fund, $path);

        $structure = $fund->asset_allocation;

        $this->assertSame('Change since 30 June 2026', $structure['subtitle']);
        $this->assertSame(['', 'TOTAL', 'CHANGE'], $structure['headers']);
        $this->assertSame([
            ['name' => 'Money market', 'value' => '2.5', 'change' => '▼ 2.0', 'changeDirection' => 'down'],
            ['name' => 'RSA ILB 1—2 years', 'value' => '16.8', 'change' => '▼ 2.3', 'changeDirection' => 'down'],
            // A "-" sign keeps its down triangle on a 0.0 change (published).
            ['name' => 'Replica ILB 3—5 years', 'value' => '3.1', 'change' => '▼ 0.0', 'changeDirection' => 'down'],
        ], $structure['rows']);
        $this->assertSame('100.0', $structure['total']['value']);
    }

    public function test_benchmark_stats_sa_note_is_added_before_rounding_note(): void
    {
        $fund = Fund::factory()->create([
            'template' => 'show-inflation-income',
            'performance_table' => [
                'footnotes' => ['⁶ Net of fees and expenses.', 'Note: Totals may not cast perfectly due to rounding.'],
            ],
        ]);

        $path = $this->makeXlsx([
            ['Code', 'Value'],
            ['MONTH_END_DATE', '31 August 2026'],
            ['MONTH_END_DATE_MMMM_YYYY', 'August 2026'],
            ['PERF_FUND_1_YEAR', '11.1'],
            ['PERF_BM_1_YEAR', '4.4'],
        ], 'inflation-footnotes');

        (new FactsheetImporter)->import($fund, $path);
        (new FactsheetImporter)->import($fund, $path);

        $this->assertSame([
            '⁶ Net of fees and expenses.',
            '⁷ Source: Stats SA, performance as calculated by Foord (estimated for August 2026)',
            'Note: Totals may not cast perfectly due to rounding.',
        ], $fund->performance_table['footnotes']);
    }
}
