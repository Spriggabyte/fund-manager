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
 * Foord-Hassen Shariah Global Equity Fund (878, fund 48).
 *
 * Covers the three feed behaviours that differ from the 877 sheet the
 * template was cloned from: the fifth "Other" geographic column, the raw
 * share count that must NOT be worded as millions, and the price graph whose
 * peer column is named "Fund Misc (3rd)" rather than "Fund Benchmark (2nd)".
 */
class ExcelImportHassenShariahTest extends TestCase
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
     * The 878 geographic chart carries a fifth "Other" pair; 877's feed has
     * no OTH keys and must still emit its four regions.
     */
    public function test_geographic_exposure_includes_the_other_region(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-hassen-shariah']);

        $path = $this->makeXlsx([
            ['Code', 'Value'],
            ['GEO_EXP_US_EQTY', '38'],
            ['GEO_EXP_ASIA_EM_EQTY', '25'],
            ['GEO_EXP_EUR_EQTY', '29'],
            ['GEO_EXP_PAC_EQTY', '7'],
            ['GEO_EXP_OTH_EQTY', '0'],
            ['GEO_EXP_US_EQTY_BM', '57'],
            ['GEO_EXP_ASIA_EM_EQTY_BM', '12'],
            ['GEO_EXP_EUR_EQTY_BM', '19'],
            ['GEO_EXP_PAC_EQTY_BM', '9'],
            ['GEO_EXP_OTH_EQTY_BM', '2'],
        ], 'hassen_geo');
        (new FactsheetImporter)->import($fund, $path);
        $fund->save();

        $geo = $fund->fresh()->asset_allocation['geographicEquityExposure'];

        $this->assertSame(
            ['North America', 'EM Asia', 'Europe', 'Pacific', 'Other'],
            array_column($geo, 'name')
        );
        $this->assertSame(0, $geo[4]['fund']);
        $this->assertSame(2, $geo[4]['benchmark']);
    }

    public function test_global_equity_feed_without_other_keys_keeps_its_four_regions(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-global-equity']);

        $path = $this->makeXlsx([
            ['Code', 'Value'],
            ['GEO_EXP_US_EQTY', '31'],
            ['GEO_EXP_ASIA_EM_EQTY', '30'],
            ['GEO_EXP_EUR_EQTY', '33'],
            ['GEO_EXP_PAC_EQTY', '6'],
            ['GEO_EXP_US_EQTY_BM', '65'],
            ['GEO_EXP_ASIA_EM_EQTY_BM', '9'],
            ['GEO_EXP_EUR_EQTY_BM', '15'],
            ['GEO_EXP_PAC_EQTY_BM', '8'],
        ], 'lux_geo');
        (new FactsheetImporter)->import($fund, $path);
        $fund->save();

        $this->assertSame(
            ['North America', 'EM Asia', 'Europe', 'Pacific'],
            array_column($fund->fresh()->asset_allocation['geographicEquityExposure'], 'name')
        );
    }

    /**
     * The 877 sheets print "5.3 million"; the 878 reference prints the raw
     * count, space-separated.
     */
    public function test_number_of_shares_keeps_the_raw_count(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-hassen-shariah']);

        $path = $this->makeXlsx([
            ['Code', 'Value'],
            ['NUMBER_OF_UNITS', '511,025'],
            ['PORTFOLIO_SIZE', '26.6 million'],
        ], 'hassen_units');
        (new FactsheetImporter)->import($fund, $path);
        $fund->save();

        $fresh = $fund->fresh();
        $this->assertSame('511 025', $fresh->number_of_units);
        // Dollar prefix, like the other Luxembourg sub-funds.
        $this->assertSame('$26.6 million', $fresh->portfolio_size);
    }

    /**
     * The published line carries no full stop on the Luxembourg sheets.
     */
    public function test_published_line_has_no_full_stop(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-hassen-shariah']);

        $path = $this->makeXlsx([
            ['Code', 'Value'],
            ['PUBLISHED_DATE', '5 August 2026'],
        ], 'hassen_published');
        (new FactsheetImporter)->import($fund, $path);
        $fund->save();

        $this->assertSame('Published on 05 August 2026', $fund->fresh()->important_info_published_date);
    }

    /**
     * Fund price, MSCI benchmark and a peer column named "Fund Misc (3rd)"
     * → the three-series PORTFOLIO PERFORMANCE VS BENCHMARK chart.
     */
    public function test_misc_third_peer_column_produces_three_series(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-hassen-shariah']);

        $path = $this->makeXlsx([
            ['Start Date', 'Description', '878 Fund Price (1st) [FSEFR USD HRD]', '878 Fund Benchmark [MSCI ISLAM USD]', '878 Fund Misc (3rd) [MRN 878 PEER MTH]'],
            [44197, 'Dec 2020', 100, 100, 100],
            [44197, 'Jan 2021', 99.36, 100.5463553271, 100.79],
            [44197, 'Jul 2026', 140.8, 188.4592901752, 159.02262738],
        ], 'hassen_price_graph');
        (new PriceGraphImporter)->import($fund, $path);
        $fund->save();

        $performance = $fund->fresh()->chart_data['performanceData'];

        $this->assertCount(3, $performance);
        $this->assertSame(['date', 'fund', 'benchmark', 'peerGroup'], array_keys($performance[0]));
        $this->assertSame('2021-01', $performance[1]['date']);
        $this->assertEquals(99.36, $performance[1]['fund']);
        $this->assertEquals(100.55, $performance[1]['benchmark']);
        $this->assertEquals(100.79, $performance[1]['peerGroup']);
        $this->assertEquals(159.02, $performance[2]['peerGroup']);
    }

    /**
     * Regression guard for the widened peer-column match: 821 carries a
     * "Fund Misc (1st)" column in the same position and must still be left
     * for its own branch.
     */
    public function test_misc_first_column_is_not_read_as_a_peer_series(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-hassen-shariah']);

        $path = $this->makeXlsx([
            ['Start Date', 'Description', '821 A Class [iR]', '821 Fund Benchmark [MSCI AC ZAR3PM]', '821 Fund Misc (1st) [MRN GLB LCAP CCY ZAR]'],
            [44614, 'Feb 2022', 100.86, 101.31, 99.4],
            [44614, 'Mar 2022', 97.36, 97.83, 96.1],
        ], 'misc_first_price_graph');
        (new PriceGraphImporter)->import($fund, $path);
        $fund->save();

        $this->assertArrayNotHasKey('performanceData', $fund->fresh()->chart_data);
    }
}
