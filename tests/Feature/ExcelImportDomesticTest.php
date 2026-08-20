<?php

namespace Tests\Feature;

use App\Models\Fund;
use App\Services\FundImport\FactsheetImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class ExcelImportDomesticTest extends TestCase
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

    public function test_domestic_asset_allocation_maps_single_sa_column_and_skips_zero_rows(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-domestic']);

        // 820 exports bare AA_TOTAL_* keys (no AA_DOM_/AA_FRGN_ split); the
        // published sheet drops asset classes with no holding (Corporate
        // bonds exports 0.0 with a blank sign).
        $path = $this->makeXlsx([
            ['Code', 'Value'],
            ['LAST_QUARTER_END', '30 June 2026'],
            ['AA_TOTAL_EQ', '61.3'],
            ['AA_TOTAL_DIFF_EQ', '0.6'],
            ['AA_TOTAL_DIFF_SIGN_EQ', '+'],
            ['AA_TOTAL_DEBT', '0.0'],
            ['AA_TOTAL_DIFF_DEBT', '0.0'],
            ['AA_TOTAL_DIFF_SIGN_DEBT', ''],
            ['AA_TOTAL_CASH', '18.0'],
            ['AA_TOTAL_DIFF_CASH', '0.8'],
            ['AA_TOTAL_DIFF_SIGN_CASH', '-'],
        ], 'domestic-factsheet');

        (new FactsheetImporter)->import($fund, $path);

        $allocation = $fund->asset_allocation;
        $rows = collect($allocation['rows'])->keyBy('name');

        $this->assertSame(['', 'SA (100)', 'CHANGE'], $allocation['headers']);
        $this->assertSame('Change since 30 June 2026', $allocation['subtitle']);
        $this->assertFalse($rows->has('Corporate bonds'));
        $this->assertSame('75', $rows['Equities']['limit']);
        $this->assertSame('▲ 0.6', $rows['Equities']['change']);
        $this->assertSame('▼ 0.8', $rows['Money market']['change']);
        $this->assertSame(100, $allocation['total']['sa']);
    }

    public function test_balanced_dom_frgn_export_still_uses_sa_foreign_layout(): void
    {
        $fund = Fund::factory()->create(['template' => 'show']);

        // The balanced export carries AA_TOTAL_* too — the AA_DOM_ split must
        // keep routing it to the SA/FOREIGN layout, not the domestic one.
        $path = $this->makeXlsx([
            ['Code', 'Value'],
            ['AA_DOM_EQ', '36.6'],
            ['AA_TOTAL_EQ', '55.4'],
            ['AA_TOTAL_DIFF_EQ', '0.7'],
            ['AA_TOTAL_DIFF_SIGN_EQ', '-'],
            ['AA_DOM_TOTAL', '64.6'],
            ['AA_FRGN_TOTAL', '35.4'],
        ], 'balanced-factsheet');

        (new FactsheetImporter)->import($fund, $path);

        $this->assertSame(
            ['', 'SA (100)', 'FOREIGN (45)', 'TOTAL', 'CHANGE'],
            $fund->asset_allocation['headers']
        );
    }

    public function test_number_of_units_commas_become_spaces(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-domestic']);

        // The published sheets space-separate thousands ("4 434"); the 820
        // feed exports commas.
        $path = $this->makeXlsx([
            ['Code', 'Value'],
            ['NUMBER_OF_UNITS', '4,517'],
        ], 'domestic-units');

        (new FactsheetImporter)->import($fund, $path);

        $this->assertSame('4 517', $fund->number_of_units);
    }
}
