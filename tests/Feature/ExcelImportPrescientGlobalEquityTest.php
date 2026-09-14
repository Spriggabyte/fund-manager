<?php

namespace Tests\Feature;

use App\Models\Fund;
use App\Services\FundImport\FactsheetImporter;
use App\Services\FundImport\PriceGraphImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

/**
 * Prescient Foord Global Equity Feeder Fund (823, funds 46/47).
 *
 * Covers the three feed behaviours the template shares with the 822 sheet or
 * the 877 master sheet, plus the two-series price graph that is unique to it.
 */
class ExcelImportPrescientGlobalEquityTest extends TestCase
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

    /**
     * @param  list<array{0: string, 1: string}>  $extra
     */
    private function factsheetRows(array $extra = []): array
    {
        return array_merge([
            ['Code', 'Value'],
            ['MONTH_END_DATE', '31 July 2026'],
            ['LAST_QUARTER_END', '30 June 2026'],
            ['PUBLISHED_DATE', '7 August 2026'],
            ['ESAOT_RANK_1_ITEM', 'Communication services'],
            ['ESAOT_RANK_1_CURRENT', '19'],
            ['ESAOT_RANK_1_CHANGE_SIGN', '-'],
            ['ESAOT_RANK_1_CHANGE', '0.1'],
            ['ESAOT_RANK_1_VAR_TO_BM', '+ 11.4'],
            ['ESAOT_RANK_2_ITEM', 'Property'],
            ['ESAOT_RANK_2_CURRENT', '1'],
            ['ESAOT_RANK_2_CHANGE_SIGN', '+'],
            ['ESAOT_RANK_2_CHANGE', '0.1'],
            ['ESAOT_RANK_2_VAR_TO_BM', '- 1.0'],
        ], $extra);
    }

    /**
     * The published sheet prints "Real estate"; the feed exports "Property".
     */
    public function test_property_sector_is_renamed_real_estate_on_the_prescient_global_equity_sheet(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-prescient-global-equity']);

        $path = $this->makeXlsx($this->factsheetRows(), 'pge_factsheet_sectors');
        (new FactsheetImporter)->import($fund, $path);
        $fund->save();

        $sectors = collect($fund->fresh()->sector_allocation['sectors']);

        $this->assertSame(
            ['Communication services', 'Real estate'],
            $sectors->pluck('name')->all()
        );
        // The variance column loses the feed's space around the sign.
        $this->assertSame('+11.4', $sectors[0]['variance']);
        $this->assertSame('-1.0', $sectors[1]['variance']);
    }

    /**
     * The rename is scoped to the templates that draw PORTFOLIO STRUCTURE —
     * every other sheet publishes the feed's own wording.
     */
    public function test_property_sector_keeps_its_feed_name_on_other_templates(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-equity']);

        $path = $this->makeXlsx($this->factsheetRows(), 'equity_factsheet_sectors');
        (new FactsheetImporter)->import($fund, $path);
        $fund->save();

        $this->assertSame(
            ['Communication services', 'Property'],
            collect($fund->fresh()->sector_allocation['sectors'])->pluck('name')->all()
        );
    }

    public function test_published_line_reads_issue_date_and_zero_pads_the_day(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-prescient-global-equity']);

        $path = $this->makeXlsx($this->factsheetRows(), 'pge_factsheet_published');
        (new FactsheetImporter)->import($fund, $path);
        $fund->save();

        $this->assertSame('Issue date 07 August 2026.', $fund->fresh()->important_info_published_date);
    }

    /**
     * The fund feeds a roll-up that never distributes, so its DISTRIBUTIONS
     * row carries seeded prose — but the export still emits zero-value
     * distribution rows that a monthly re-import must not write over.
     */
    public function test_zero_value_distributions_do_not_clobber_the_seeded_prose(): void
    {
        $prose = 'The Foord Global Equity Fund, in which the fund invests, does not distribute its income.';
        $fund = Fund::factory()->create([
            'template' => 'show-prescient-global-equity',
            'last_distributions' => $prose,
        ]);

        $path = $this->makeXlsx($this->factsheetRows([
            ['LAST_DISTRIBUTION_DATE', '31/03/2026'],
            ['LAST_DISTRIBUTION_AMOUNT', '0.00 cents'],
            ['SECOND_LAST_DISTRIBUTION_DATE', '30/09/2025'],
            ['SECOND_LAST_DISTRIBUTION_AMOUNT', '0.00 cents'],
        ]), 'pge_factsheet_distributions');
        (new FactsheetImporter)->import($fund, $path);
        $fund->save();

        $this->assertSame($prose, $fund->fresh()->last_distributions);
    }

    /**
     * Fund price plus an MSCI benchmark and nothing else → the two-line
     * ILLUSTRATIVE PERFORMANCE chart.
     */
    public function test_two_series_price_graph_produces_fund_and_benchmark_performance_data(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-prescient-global-equity']);

        $path = $this->makeXlsx([
            ['Start Date', 'Description', '823 A Class [iR]', '823 Fund Benchmark [MSCI AC ZAR3PM]', ''],
            [44614, 'Feb 2022', 100.86617094, 101.3077843598, ''],
            [44614, 'Mar 2022', 97.36032712, 97.8292443527, ''],
            [44614, 'Jul 2026', 136.5307027, 187.5711200264, ''],
        ], 'pge_price_graph');
        (new PriceGraphImporter)->import($fund, $path);
        $fund->save();

        $performance = $fund->fresh()->chart_data['performanceData'];

        $this->assertCount(3, $performance);
        $this->assertSame(['date', 'fund', 'benchmark'], array_keys($performance[0]));
        $this->assertSame('2022-02', $performance[0]['date']);
        $this->assertEquals(100.87, $performance[0]['fund']);
        $this->assertEquals(101.31, $performance[0]['benchmark']);
        $this->assertEquals(136.53, $performance[2]['fund']);
        $this->assertEquals(187.57, $performance[2]['benchmark']);
    }

    /**
     * Regression guard: 821/878/879/880 also lead with an MSCI benchmark in
     * column D but carry a peer or second benchmark in column E, and take
     * the three-series branch rather than being silently flattened to two
     * series (the 821 branch keeps the peer group under `peerGroup`).
     */
    public function test_an_export_with_a_third_series_is_not_claimed_by_the_two_series_branch(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-prescient-global-equity']);

        $path = $this->makeXlsx([
            ['Start Date', 'Description', '821 A Class [iR]', '821 Fund Benchmark [MSCI AC ZAR3PM]', '821 Fund Misc (1st) [MRN GLB LCAP CCY ZAR]', ''],
            [44614, 'Feb 2022', 100.86, 101.31, 99.4, ''],
            [44614, 'Mar 2022', 97.36, 97.83, 96.1, ''],
        ], 'peer_price_graph');
        (new PriceGraphImporter)->import($fund, $path);
        $fund->save();

        $performance = $fund->fresh()->chart_data['performanceData'];
        $this->assertCount(2, $performance);
        $this->assertSame(['date', 'fund', 'benchmark', 'peerGroup'], array_keys($performance[0]));
        $this->assertEquals(99.4, $performance[0]['peerGroup']);
    }

    /**
     * Regression (QC 2026-09-14): $chartData is seeded from the fund's saved
     * chart_data, so a "not already set" guard on performanceData skipped
     * every monthly re-import after the first and left the ILLUSTRATIVE
     * PERFORMANCE line a month stale (R 137 drawn against a published R 139).
     */
    public function test_re_importing_a_newer_price_graph_refreshes_the_two_series_performance_data(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-prescient-global-equity']);

        $july = $this->makeXlsx([
            ['Start Date', 'Description', '823 A Class [iR]', '823 Fund Benchmark [MSCI AC ZAR3PM]', ''],
            [44614, 'Feb 2022', 100.86617094, 101.3077843598, ''],
            [44614, 'Jul 2026', 136.5307027, 187.5711200264, ''],
        ], 'pge_price_graph_july');
        (new PriceGraphImporter)->import($fund, $july);
        $fund->save();

        $august = $this->makeXlsx([
            ['Start Date', 'Description', '823 A Class [iR]', '823 Fund Benchmark [MSCI AC ZAR3PM]', ''],
            [44614, 'Feb 2022', 100.86617094, 101.3077843598, ''],
            [44614, 'Jul 2026', 136.5307027, 187.5711200264, ''],
            [44614, 'Aug 2026', 138.69499926, 187.6702017129, ''],
        ], 'pge_price_graph_august');
        $fund = $fund->fresh();
        (new PriceGraphImporter)->import($fund, $august);
        $fund->save();

        $performance = $fund->fresh()->chart_data['performanceData'];
        $this->assertCount(3, $performance);
        $this->assertSame('2026-08', $performance[2]['date']);
        $this->assertEquals(138.69, $performance[2]['fund']);
        $this->assertEquals(187.67, $performance[2]['benchmark']);
    }

    /**
     * The published PORTFOLIO STRUCTURE list reports its change against the
     * last quarter end ("Change since 30 June 2026" on the August sheet),
     * like the 821 feeder — not the prior month.
     */
    public function test_portfolio_structure_subtitle_uses_the_last_quarter_end(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-prescient-global-equity']);

        $path = $this->makeXlsx($this->factsheetRows([['MONTH_END_DATE', '31 August 2026']]), 'pge_factsheet_subtitle');
        (new FactsheetImporter)->import($fund, $path);
        $fund->save();

        $this->assertSame('Change since 30 June 2026', $fund->fresh()->sector_allocation['subtitle']);
    }
}
