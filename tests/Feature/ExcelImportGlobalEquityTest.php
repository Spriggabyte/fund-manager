<?php

namespace Tests\Feature;

use App\Models\Fund;
use App\Services\FundImport\FactsheetImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

/**
 * Foord Global Equity Fund (Luxembourg) (877, funds 38/39/40 — Classes R/B/R1).
 *
 * QC 2026-09-14/15 regressions: the PORTFOLIO STRUCTURE "Change since"
 * subtitle was missing 877 from its quarter-baseline template list, the R1
 * export sends blank GLOBAL_TER_PERFORMANCE cells every month despite
 * carrying a performance fee, and the Class B reference keeps a legacy
 * "FEES (CLASS A)" sidebar label.
 */
class ExcelImportGlobalEquityTest extends TestCase
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

    public function test_portfolio_structure_subtitle_uses_the_last_quarter_end(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-global-equity']);

        $path = $this->makeXlsx([
            ['Code', 'Value'],
            ['MONTH_END_DATE', '31 August 2026'],
            ['LAST_QUARTER_END', '30 June 2026'],
            ['ESAOT_RANK_1_ITEM', 'Communication services'],
            ['ESAOT_RANK_1_CURRENT', '19'],
            ['ESAOT_RANK_1_CHANGE_SIGN', '-'],
            ['ESAOT_RANK_1_CHANGE', '1.0'],
            ['ESAOT_RANK_1_VAR_TO_BM', '+ 11.0'],
        ], 'global_equity_subtitle');
        (new FactsheetImporter)->import($fund, $path);
        $fund->save();

        $this->assertSame('Change since 30 June 2026', $fund->fresh()->sector_allocation['subtitle']);
    }

    /**
     * 877 R1 exports blank GLOBAL_TER_PERFORMANCE cells every month even
     * though it carries a performance fee and the reference prints
     * "— Performance 0.00 / 0.00". A stored row (seeded once from the
     * reference) must survive the blank re-import; a fund with no stored
     * row (Class B, which has no performance fee) must not gain one.
     */
    public function test_blank_performance_fee_preserves_a_stored_row(): void
    {
        $fund = Fund::factory()->create([
            'template' => 'show-global-equity',
            'fees' => [
                'annualisedCostRatio' => [
                    'rows' => [
                        ['name' => 'TER — Basic', '12m' => '0.20', '36m' => '0.40'],
                        ['name' => '— Performance', '12m' => '0.00', '36m' => '0.00'],
                    ],
                ],
            ],
        ]);

        $path = $this->makeXlsx([
            ['Code', 'Value'],
            ['GLOBAL_TER_BASIC_12_MONTH', '0.23'],
            ['GLOBAL_TER_BASIC_36_MONTH', '0.47'],
            ['GLOBAL_TER_TRANSACTION_COSTS_12_MONTH', '0.03'],
            ['GLOBAL_TER_TRANSACTION_COSTS_36_MONTH', '0.07'],
            ['GLOBAL_TER_TOTAL_12_MONTH', '0.26'],
            ['GLOBAL_TER_TOTAL_36_MONTH', '0.54'],
        ], 'global_equity_r1_cost_ratio');
        (new FactsheetImporter)->import($fund, $path);
        $fund->save();

        $rows = $fund->fresh()->fees['annualisedCostRatio']['rows'];
        $this->assertSame(['TER — Basic', '— Performance', 'Transaction costs'], array_column($rows, 'name'));
        $performanceRow = collect($rows)->firstWhere('name', '— Performance');
        $this->assertSame('0.00', $performanceRow['12m']);

        $withoutStoredRow = Fund::factory()->create([
            'template' => 'show-global-equity',
            'fees' => [
                'annualisedCostRatio' => [
                    'rows' => [
                        ['name' => 'TER — Basic', '12m' => '1.06', '36m' => '1.05'],
                    ],
                ],
            ],
        ]);
        (new FactsheetImporter)->import($withoutStoredRow, $path);
        $withoutStoredRow->save();

        $bRows = $withoutStoredRow->fresh()->fees['annualisedCostRatio']['rows'];
        $this->assertSame(['TER — Basic', 'Transaction costs'], array_column($bRows, 'name'));
    }
}
