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
 * Foord Asia ex-Japan Fund (879, funds 51/52).
 *
 * Covers what the 879 feed does that no earlier sheet does: the
 * GEO_COUNTRY_RANK_* country split behind the pie, a top 10 with no sector
 * or market columns, the "Property" sector published verbatim, and a price
 * graph whose Class R1 fund column is headed "Fund Price (2nd)".
 */
class ExcelImportAsiaExJapanTest extends TestCase
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
     * The pie draws slices in feed rank order with the "RANK_7+" catch-all
     * pinned last, which is what reproduces the reference's clockwise order.
     */
    public function test_geographic_country_exposure_keeps_feed_rank_order_with_other_last(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-asia-ex-japan']);

        $path = $this->makeXlsx([
            ['Code', 'Value'],
            ['GEO_COUNTRY_RANK_1_ITEM', 'China'],
            ['GEO_COUNTRY_RANK_1_CURRENT', '44.3'],
            ['GEO_COUNTRY_RANK_2_ITEM', 'United States of America'],
            ['GEO_COUNTRY_RANK_2_CURRENT', '14.4'],
            ['GEO_COUNTRY_RANK_3_ITEM', 'Taiwan, Province of China'],
            ['GEO_COUNTRY_RANK_3_CURRENT', '9.7'],
            ['GEO_COUNTRY_RANK_4_ITEM', 'Korea'],
            ['GEO_COUNTRY_RANK_4_CURRENT', '8.7'],
            ['GEO_COUNTRY_RANK_5_ITEM', 'Singapore'],
            ['GEO_COUNTRY_RANK_5_CURRENT', '5.4'],
            ['GEO_COUNTRY_RANK_6_ITEM', 'Hong Kong'],
            ['GEO_COUNTRY_RANK_6_CURRENT', '4.5'],
            ['GEO_COUNTRY_RANK_7+_ITEM', 'Other'],
            ['GEO_COUNTRY_RANK_7+_CURRENT', '13.0'],
        ], 'asia_geo_country');
        (new FactsheetImporter)->import($fund, $path);
        $fund->save();

        $slices = $fund->fresh()->asset_allocation['geographicCountryExposure'];

        $this->assertSame(
            ['China', 'United States of America', 'Taiwan, Province of China', 'Korea', 'Singapore', 'Hong Kong', 'Other'],
            array_column($slices, 'name')
        );
        $this->assertEquals(44.3, $slices[0]['value']);
        $this->assertEquals(13.0, $slices[6]['value']);
    }

    /**
     * A month that reports fewer countries must not leave empty slices
     * behind, and the "Other" row is optional.
     */
    public function test_geographic_country_exposure_skips_unreported_ranks(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-asia-ex-japan']);

        $path = $this->makeXlsx([
            ['Code', 'Value'],
            ['GEO_COUNTRY_RANK_1_ITEM', 'China'],
            ['GEO_COUNTRY_RANK_1_CURRENT', '52.0'],
            ['GEO_COUNTRY_RANK_2_ITEM', 'Korea'],
            ['GEO_COUNTRY_RANK_2_CURRENT', '48.0'],
            ['GEO_COUNTRY_RANK_3_ITEM', ''],
            ['GEO_COUNTRY_RANK_3_CURRENT', '-'],
            ['GEO_COUNTRY_RANK_4_ITEM', 'Malaysia'],
            ['GEO_COUNTRY_RANK_4_CURRENT', 'ERR'],
        ], 'asia_geo_country_short');
        (new FactsheetImporter)->import($fund, $path);
        $fund->save();

        $names = array_column($fund->fresh()->asset_allocation['geographicCountryExposure'], 'name');

        $this->assertSame(['China', 'Korea'], $names);
        $this->assertNotContains('Malaysia', $names);
    }

    /**
     * Regression: the country branch must not swallow the 877/878 region
     * chart, which is keyed on GEO_EXP_*.
     */
    public function test_region_chart_is_untouched_by_the_country_branch(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-hassen-shariah']);

        $path = $this->makeXlsx([
            ['Code', 'Value'],
            ['GEO_EXP_US_EQTY', '38'],
            ['GEO_EXP_ASIA_EM_EQTY', '25'],
            ['GEO_EXP_EUR_EQTY', '29'],
            ['GEO_EXP_PAC_EQTY', '7'],
            ['GEO_EXP_US_EQTY_BM', '57'],
            ['GEO_EXP_ASIA_EM_EQTY_BM', '12'],
            ['GEO_EXP_EUR_EQTY_BM', '19'],
            ['GEO_EXP_PAC_EQTY_BM', '9'],
        ], 'asia_geo_region_regression');
        (new FactsheetImporter)->import($fund, $path);
        $fund->save();

        $allocation = $fund->fresh()->asset_allocation;

        $this->assertSame(
            ['North America', 'EM Asia', 'Europe', 'Pacific'],
            array_column($allocation['geographicEquityExposure'], 'name')
        );
        $this->assertArrayNotHasKey('geographicCountryExposure', $allocation);
    }

    /**
     * 879 differs from its 877/878 siblings on three of the four things the
     * old LUX_TEMPLATES constant gated at once.
     */
    public function test_cost_ratio_table_uses_the_asia_ex_japan_labels(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-asia-ex-japan']);

        $path = $this->makeXlsx([
            ['Code', 'Value'],
            ['GLOBAL_TER_BASIC_12_MONTH', '1.02'],
            ['GLOBAL_TER_BASIC_36_MONTH', '0.98'],
            ['GLOBAL_TER_PERFORMANCE_12_MONTH', '0.00'],
            ['GLOBAL_TER_PERFORMANCE_36_MONTH', '-0.10'],
            ['GLOBAL_TER_TRANSACTION_COSTS_12_MONTH', '0.07'],
            ['GLOBAL_TER_TRANSACTION_COSTS_36_MONTH', '0.08'],
            ['GLOBAL_TER_TOTAL_12_MONTH', '1.09'],
            ['GLOBAL_TER_TOTAL_36_MONTH', '0.96'],
        ], 'asia_cost_ratio');
        (new FactsheetImporter)->import($fund, $path);
        $fund->save();

        $acr = $fund->fresh()->fees['annualisedCostRatio'];

        $this->assertSame(
            ['TER — Basic', '— Performance', 'Transaction costs (incl VAT)'],
            array_column($acr['rows'], 'name')
        );
        $this->assertSame('Total cost ratio', $acr['total']['name']);
    }

    public function test_hassen_shariah_cost_ratio_labels_are_unchanged(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-hassen-shariah']);

        $path = $this->makeXlsx([
            ['Code', 'Value'],
            ['GLOBAL_TER_BASIC_12_MONTH', '1.14'],
            ['GLOBAL_TER_BASIC_36_MONTH', '1.10'],
            ['GLOBAL_TER_PERFORMANCE_12_MONTH', '0.05'],
            ['GLOBAL_TER_PERFORMANCE_36_MONTH', '0.04'],
            ['GLOBAL_TER_TRANSACTION_COSTS_12_MONTH', '0.08'],
            ['GLOBAL_TER_TRANSACTION_COSTS_36_MONTH', '0.08'],
            ['GLOBAL_TER_TOTAL_12_MONTH', '1.22'],
            ['GLOBAL_TER_TOTAL_36_MONTH', '1.18'],
        ], 'hassen_cost_ratio_regression');
        (new FactsheetImporter)->import($fund, $path);
        $fund->save();

        $acr = $fund->fresh()->fees['annualisedCostRatio'];

        $this->assertSame(['TER — Basic', '— Performance', 'Transaction costs'], array_column($acr['rows'], 'name'));
        $this->assertSame('Total investment charge', $acr['total']['name']);
    }

    public function test_published_line_ends_with_a_full_stop(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-asia-ex-japan']);

        $path = $this->makeXlsx([
            ['Code', 'Value'],
            ['PUBLISHED_DATE', '5 August 2026'],
        ], 'asia_published');
        (new FactsheetImporter)->import($fund, $path);
        $fund->save();

        $this->assertSame('Published on 05 August 2026.', $fund->fresh()->important_info_published_date);
    }

    public function test_lux_siblings_keep_their_full_stop_free_published_line(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-hassen-shariah']);

        $path = $this->makeXlsx([
            ['Code', 'Value'],
            ['PUBLISHED_DATE', '5 August 2026'],
        ], 'hassen_published_regression');
        (new FactsheetImporter)->import($fund, $path);
        $fund->save();

        $this->assertSame('Published on 05 August 2026', $fund->fresh()->important_info_published_date);
    }

    /**
     * 877/878/823 rename the feed's "Property" sector to "Real estate"; the
     * 879 reference prints "Property", so this template must be left out of
     * PORTFOLIO_STRUCTURE_TEMPLATES.
     */
    public function test_property_sector_is_published_verbatim(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-asia-ex-japan']);

        $path = $this->makeXlsx([
            ['Code', 'Value'],
            ['ESAOT_RANK_1_ITEM', 'Consumer discretionary'],
            ['ESAOT_RANK_1_CURRENT', '30'],
            ['ESAOT_RANK_1_CHANGE_SIGN', '+'],
            ['ESAOT_RANK_1_CHANGE', '3.1'],
            ['ESAOT_RANK_2_ITEM', 'Property'],
            ['ESAOT_RANK_2_CURRENT', '3'],
            ['ESAOT_RANK_2_CHANGE_SIGN', '+'],
            ['ESAOT_RANK_2_CHANGE', '0.2'],
        ], 'asia_sectors');
        (new FactsheetImporter)->import($fund, $path);
        $fund->save();

        $sectors = $fund->fresh()->sector_allocation['sectors'];

        $this->assertSame(['Consumer discretionary', 'Property'], array_column($sectors, 'name'));
        $this->assertSame('up', $sectors[1]['direction']);
    }

    /**
     * The 879 feed sends no sector or market columns for the top 10 — the
     * sheet prints SECURITY and % OF FUND only.
     */
    public function test_top_investments_import_without_sector_or_market_columns(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-asia-ex-japan']);

        $path = $this->makeXlsx([
            ['Code', 'Value'],
            ['TOPX_SECURITY_1', 'TSMC'],
            ['TOPX_PERCENT_OF_FUNDS_1', '8.4'],
            ['TOPX_SECURITY_2', 'APR Corp/Korea'],
            ['TOPX_PERCENT_OF_FUNDS_2', '7.8'],
        ], 'asia_top10');
        (new FactsheetImporter)->import($fund, $path);
        $fund->save();

        $rows = $fund->fresh()->top_investments['rows'];

        $this->assertCount(2, $rows);
        $this->assertSame('TSMC', $rows[0]['security']);
        $this->assertEquals(8.4, $rows[0]['percentage']);
        $this->assertSame('', $rows[0]['assetClass']);
        $this->assertSame('', $rows[0]['market']);
    }

    public function test_class_r_price_graph_produces_three_series(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-asia-ex-japan']);

        $path = $this->makeXlsx([
            ['Start Date', 'Description', '879 Fund Price (1st) [FASFR USD HRD]', '879 Fund Benchmark [MSCI ASIA USD]', '879 Fund Benchmark (2nd) [AXJPG]'],
            [44404, 'Jul 2021', 102.68, 102.3022281918, 99.45],
            [44404, 'Jul 2026', 131.68, 152.4679219048, 130.42214601],
        ], 'asia_price_graph_r');
        (new PriceGraphImporter)->import($fund, $path);
        $fund->save();

        $performance = $fund->fresh()->chart_data['performanceData'];

        $this->assertSame(['date', 'fund', 'benchmark', 'peerGroup'], array_keys($performance[0]));
        $this->assertSame('2021-07', $performance[0]['date']);
        $this->assertEquals(131.68, $performance[1]['fund']);
        $this->assertEquals(152.47, $performance[1]['benchmark']);
        $this->assertEquals(130.42, $performance[1]['peerGroup']);
    }

    /**
     * Class R1's fund column is headed "Fund Price (2nd)" rather than "(1st)".
     * The importer identifies that column positionally, so this is a smoke
     * test that the R1 export still yields three series — NOT a guard on the
     * header text. The real Class R1 risk is file routing, covered below.
     */
    public function test_class_r1_price_graph_produces_three_series(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-asia-ex-japan']);

        $path = $this->makeXlsx([
            ['Start Date', 'Description', '879 Fund Price (2nd) [FASFR1 USD HRD]', '879 Fund Benchmark [MSCI ASIA USD]', '879 Fund Benchmark (2nd) [AXJPG]'],
            [44404, 'Jul 2021', 102.68, 102.3022281918, 99.45],
            [44404, 'Jul 2026', 134, 152.4679219048, 130.42214601],
        ], 'asia_price_graph_r1');
        (new PriceGraphImporter)->import($fund, $path);
        $fund->save();

        $performance = $fund->fresh()->chart_data['performanceData'];

        $this->assertSame(['date', 'fund', 'benchmark', 'peerGroup'], array_keys($performance[0]));
        $this->assertEquals(134, $performance[1]['fund']);
        $this->assertEquals(130.42, $performance[1]['peerGroup']);
    }

    /**
     * The 879 directory holds both classes' exports, so every import depends
     * on FundImportManager splitting them by class token: "879R1_*" must not
     * be read as class R, nor "879R_*" as class R1. Getting this wrong would
     * silently populate both funds from one class's figures.
     */
    public function test_class_exports_are_routed_to_their_own_class(): void
    {
        $files = [
            '/feed/879R_FACTSHEET.xlsx',
            '/feed/879R_PRICE_GRAPH.xlsx',
            '/feed/879R1_FACTSHEET.xlsx',
            '/feed/879R1_PRICE_GRAPH.xlsx',
        ];

        $manager = new \App\Services\FundImport\FundImportManager;

        $this->assertSame(
            ['/feed/879R_FACTSHEET.xlsx', '/feed/879R_PRICE_GRAPH.xlsx'],
            $manager->filesForClass($files, '879', 'R')
        );
        $this->assertSame(
            ['/feed/879R1_FACTSHEET.xlsx', '/feed/879R1_PRICE_GRAPH.xlsx'],
            $manager->filesForClass($files, '879', 'R1')
        );
    }

    /**
     * 879 is a US-dollar Luxembourg sub-fund. The currency prefix is chosen
     * from an allowlist that this template was originally missing from, which
     * imported the fund size as "R192.8 million" onto a dollar sheet.
     */
    public function test_portfolio_size_is_prefixed_in_us_dollars(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-asia-ex-japan']);

        $path = $this->makeXlsx([
            ['Code', 'Value'],
            ['PORTFOLIO_SIZE', '192.8 million'],
        ], 'asia_portfolio_size');
        (new FactsheetImporter)->import($fund, $path);
        $fund->save();

        $this->assertSame('$192.8 million', $fund->fresh()->portfolio_size);
    }
}
