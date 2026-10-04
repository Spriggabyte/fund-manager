<?php

namespace Tests\Feature;

use App\Models\Fund;
use App\Services\FundImport\FactsheetImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class ExcelImportInternationalTest extends TestCase
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

    public function test_annualised_cost_ratio_refreshes_alongside_total_investment_charge(): void
    {
        $fund = Fund::factory()->create([
            'template' => 'show-international',
            'fees' => [
                'annualisedCostRatio' => [
                    'title' => 'ANNUALISED COST RATIO %',
                    'headers' => ['', '12 MONTHS', '36 MONTHS'],
                    'rows' => [
                        ['name' => 'TER — Basic', '12m' => '1.05', '36m' => '1.04'],
                        ['name' => 'Transaction costs', '12m' => '0.05', '36m' => '0.05'],
                    ],
                    'total' => ['name' => 'Total cost ratio', '12m' => '1.10', '36m' => '1.09'],
                    'description' => 'A TER is a measure… The latest audited TER is 1.05%.',
                ],
            ],
        ]);

        $path = $this->makeXlsx([
            ['Code', 'Value'],
            ['MONTH_END_DATE', '31 July 2026'],
            ['GLOBAL_TER_BASIC_12_MONTH', '1.06'],
            ['GLOBAL_TER_BASIC_36_MONTH', '1.05'],
            ['GLOBAL_TER_TRANSACTION_COSTS_12_MONTH', '0.04'],
            ['GLOBAL_TER_TRANSACTION_COSTS_36_MONTH', '0.05'],
            ['GLOBAL_TER_TOTAL_12_MONTH', '1.10'],
            ['GLOBAL_TER_TOTAL_36_MONTH', '1.10'],
        ], 'international-acr');

        (new FactsheetImporter)->import($fund, $path);

        $acr = $fund->fees['annualisedCostRatio'];

        // The refreshed cost ratio must survive the totalInvestmentCharge
        // write that follows it (it previously restored a stale fees copy).
        $this->assertSame('1.06', $acr['rows'][0]['12m']);
        $this->assertSame('1.05', $acr['rows'][0]['36m']);
        $this->assertSame('0.04', $acr['rows'][1]['12m']);
        $this->assertSame('1.10', $acr['total']['12m']);
        $this->assertSame('1.10', $acr['total']['36m']);

        // Seeded statics stay untouched.
        $this->assertSame('A TER is a measure… The latest audited TER is 1.05%.', $acr['description']);

        // The TIC rows written after the refresh are still stored too.
        $this->assertSame('Total investment charge', $fund->fees['totalInvestmentCharge']['total']['name']);
    }

    public function test_geographic_totals_fall_back_to_column_sums_when_feed_exports_dash(): void
    {
        // The 874 trust feed exports GEO_EXP_EQTY_TOTAL as "-" while the
        // regions carry values — the published sheet prints the summed 69.
        $fund = Fund::factory()->create(['template' => 'show-international-trust']);

        $path = $this->makeXlsx([
            ['Code', 'Value'],
            ['GEO_EXP_US_TOTAL', '38'],
            ['GEO_EXP_US_EQTY', '20'],
            ['GEO_EXP_US_CASH', '10'],
            ['GEO_EXP_EUR_TOTAL', '30'],
            ['GEO_EXP_EUR_EQTY', '24'],
            ['GEO_EXP_EUR_CASH', '-'],
            ['GEO_EXP_PAC_TOTAL', '10'],
            ['GEO_EXP_PAC_EQTY', '6'],
            ['GEO_EXP_PAC_CASH', '-'],
            ['GEO_EXP_ASIA_EM_TOTAL', '21'],
            ['GEO_EXP_ASIA_EM_EQTY', '19'],
            ['GEO_EXP_ASIA_EM_CASH', '-'],
            ['GEO_EXP_AFRME_TOTAL', '1'],
            ['GEO_EXP_AFRME_EQTY', '-'],
            ['GEO_EXP_AFRME_CASH', '-'],
            ['GEO_EXP_EQTY_TOTAL', '-'],
            ['GEO_EXP_CASH_TOTAL', '10'],
        ], 'trust-geo');

        (new FactsheetImporter)->import($fund, $path);

        $totals = $fund->asset_allocation['geographicTotals'];
        $this->assertSame(100, $totals['total']);
        $this->assertEquals(69, $totals['equity']); // summed fallback
        $this->assertEquals(10, $totals['cash']);   // exported value kept
    }

    public function test_trust_portfolio_size_gets_dollar_prefix(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-international-trust']);

        $path = $this->makeXlsx([
            ['Code', 'Value'],
            ['PORTFOLIO_SIZE', '382.2 million'],
        ], 'trust-size');

        (new FactsheetImporter)->import($fund, $path);

        $this->assertSame('$382.2 million', $fund->portfolio_size);
    }

    /**
     * Fund 37 as published for Aug 2026, before the Sept import.
     */
    private function trustWithPublishedLows(): Fund
    {
        return Fund::factory()->create([
            'template' => 'show-international-trust',
            'performance_table' => [
                'rows' => [
                    ['name' => 'Fund', '1yr' => 19.5],
                    ['name' => 'Fund highest', 'sinceInception' => 47.3, '10yrs' => 41.9, '5yrs' => 41.9, '3yrs' => 41.9, '1yr' => 19.5],
                    ['name' => 'Fund lowest', 'sinceInception' => -31.6, '10yrs' => -13.7, '5yrs' => -13.7, '3yrs' => -2.0, '1yr' => 19.5],
                ],
            ],
        ]);
    }

    private function rollingRow(Fund $fund, string $name): array
    {
        return collect($fund->performance_table['rows'])->firstWhere('name', $name);
    }

    public function test_impossible_minus_100_lows_keep_the_stored_cells_and_the_one_year_return(): void
    {
        $fund = $this->trustWithPublishedLows();

        // 874B export of 2 Oct 2026: every FOORD_LOWEST_* was -100.0.
        $path = $this->makeXlsx([
            ['Code', 'Value'],
            ['FOORD_1Y_TO_D', '8.9'],
            ['FOORD_HIGHEST_INCEPTION', '47.3'],
            ['FOORD_HIGHEST_Y10', '41.9'],
            ['FOORD_HIGHEST_Y5', '41.9'],
            ['FOORD_HIGHEST_Y3', '41.9'],
            ['FOORD_HIGHEST_Y1', '8.9'],
            ['FOORD_LOWEST_INCEPTION', '-100.0'],
            ['FOORD_LOWEST_Y10', '-100.0'],
            ['FOORD_LOWEST_Y5', '-100.0'],
            ['FOORD_LOWEST_Y3', '-100.0'],
            ['FOORD_LOWEST_Y1', '-100.0'],
        ], 'trust-lows-minus-100');

        (new FactsheetImporter)->import($fund, $path);

        $lowest = $this->rollingRow($fund, 'Fund lowest');
        $this->assertEquals(-31.6, $lowest['sinceInception']);
        $this->assertEquals(-13.7, $lowest['10yrs']);
        $this->assertEquals(-13.7, $lowest['5yrs']);
        $this->assertEquals(-2.0, $lowest['3yrs']);
        $this->assertEquals(8.9, $lowest['1yr']);

        // The highest row passes the 1-year check and imports as sent.
        $highest = $this->rollingRow($fund, 'Fund highest');
        $this->assertEquals(8.9, $highest['1yr']);
        $this->assertEquals(47.3, $highest['sinceInception']);
    }

    public function test_lows_from_another_series_keep_the_stored_cells(): void
    {
        $fund = $this->trustWithPublishedLows();

        // 874B export for Aug 2026: the lows' 1-year cell (-3.5) is 880A's
        // 1-year return, not the fund's 19.5.
        $path = $this->makeXlsx([
            ['Code', 'Value'],
            ['FOORD_1Y_TO_D', '19.5'],
            ['FOORD_LOWEST_INCEPTION', '-33.5'],
            ['FOORD_LOWEST_Y10', '-33.5'],
            ['FOORD_LOWEST_Y5', '-33.5'],
            ['FOORD_LOWEST_Y3', '-6.3'],
            ['FOORD_LOWEST_Y1', '-3.5'],
        ], 'trust-lows-off-series');

        (new FactsheetImporter)->import($fund, $path);

        $lowest = $this->rollingRow($fund, 'Fund lowest');
        $this->assertEquals(-31.6, $lowest['sinceInception']);
        $this->assertEquals(-13.7, $lowest['10yrs']);
        $this->assertEquals(-13.7, $lowest['5yrs']);
        $this->assertEquals(-2.0, $lowest['3yrs']);
        $this->assertEquals(19.5, $lowest['1yr']);
    }

    public function test_consistent_lows_import_from_the_feed(): void
    {
        $fund = $this->trustWithPublishedLows();

        // 874B export for June 2026, wired to the fund's own series.
        $path = $this->makeXlsx([
            ['Code', 'Value'],
            ['FOORD_1Y_TO_D', '14.1'],
            ['FOORD_LOWEST_INCEPTION', '-31.6'],
            ['FOORD_LOWEST_Y10', '-13.7'],
            ['FOORD_LOWEST_Y5', '-13.7'],
            ['FOORD_LOWEST_Y3', '-5.1'],
            ['FOORD_LOWEST_Y1', '14.1'],
        ], 'trust-lows-consistent');

        (new FactsheetImporter)->import($fund, $path);

        $lowest = $this->rollingRow($fund, 'Fund lowest');
        $this->assertEquals(-5.1, $lowest['3yrs']);
        $this->assertEquals(14.1, $lowest['1yr']);
    }
}
