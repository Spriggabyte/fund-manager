<?php

namespace Tests\Feature;

use App\Models\Fund;
use App\Services\FundImport\LocalOverviewImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

/**
 * The FUND OVERVIEW: SOUTH AFRICA sheet is one export (LOCAL_OVERVIEW.xlsx)
 * covering five funds' performance, five funds' asset allocation, three
 * fixed-income funds' statistics, the bond maturity chart, the equity
 * sector chart and the quarterly synopsis/strategy bullets. The importer
 * owns every table skeleton; the stored record only contributes prose.
 */
class ExcelImportLocalOverviewTest extends TestCase
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

    private function overviewFund(array $attributes = []): Fund
    {
        return Fund::factory()->create(array_merge([
            'template' => 'show-local-overview',
            'fund_code' => 'LOC',
            'class_code' => null,
        ], $attributes));
    }

    /** @return array<string, array<string, mixed>> performance funds keyed by code */
    private function perfFunds(Fund $fund): array
    {
        $byCode = [];
        foreach ($fund->performance_table['groups'] as $group) {
            foreach ($group['funds'] as $entry) {
                $byCode[$entry['code']] = $entry;
            }
        }

        return $byCode;
    }

    /** @return array<string, array<string, mixed>> asset-allocation rows keyed by key */
    private function aaRows(Fund $fund): array
    {
        return collect($fund->asset_allocation['rows'])->keyBy('key')->all();
    }

    /** @return array<string, array<string, mixed>> fixed-income rows keyed by key */
    private function statRows(Fund $fund): array
    {
        return collect($fund->page2_content['fixedIncome']['rows'])->keyBy('key')->all();
    }

    // ── Routing ──────────────────────────────────────────────────────────

    public function test_supports_local_overview_exports_only(): void
    {
        $importer = new LocalOverviewImporter;

        $this->assertTrue($importer->supports('/feed/LOCAL_OVERVIEW.xlsx'));
        $this->assertTrue($importer->supports('local_overview_2049089080.xlsx'));
        $this->assertFalse($importer->supports('GLOBAL_OVERVIEW.xlsx'));
        $this->assertFalse($importer->supports('811A_FACTSHEET.xlsx'));
        $this->assertSame('local fund overview', $importer->label());
    }

    // ── Date + performance ───────────────────────────────────────────────

    public function test_maps_fund_date_and_three_value_maps_per_fund(): void
    {
        $fund = $this->overviewFund();

        $path = $this->makeXlsx([
            ['Code', 'Value', 'System Comment'],
            ['MONTH_END_DATE', '31 July 2026'],
            ['818_FOORD_10Y_TO_D', '8.7'],
            ['818_FOORD_5Y_TO_D', '10.1'],
            ['818_FOORD_3Y_TO_D', '11.0'],
            ['818_FOORD_1Y_TO_D', '7.7'],
            ['818_FOORD_COMP_1_10Y_TO_D', '8.7'],
            ['818_FOORD_COMP_1_1Y_TO_D', '8.5'],
            ['818_10_YEAR_MORNINGSTAR_FUND_AVG_RETURN', '8.1'],
            ['818_1_YEAR_MORNINGSTAR_FUND_AVG_RETURN', '10.7'],
            ['811_FOORD_20Y_TO_D', '11.5'],
            ['811_FOORD_COMP_1_20Y_TO_D', '12.3'],
            ['811_20_YEAR_MORNINGSTAR_FUND_AVG_RETURN', '10.8'],
        ], 'loc-perf');

        (new LocalOverviewImporter)->import($fund, $path);

        $this->assertSame('31 July 2026', $fund->fund_date);

        $funds = $this->perfFunds($fund);
        // The JSON cast drops the ".0" from integral floats, hence assertEquals for 11.0.
        $this->assertEquals(['10yrs' => 8.7, '5yrs' => 10.1, '3yrs' => 11.0, '1yr' => 7.7], $funds['818']['fund']);
        $this->assertSame(['10yrs' => 8.7, '1yr' => 8.5], $funds['818']['benchmark']);
        $this->assertSame(['10yrs' => 8.1, '1yr' => 10.7], $funds['818']['peer']);
        $this->assertSame(['20yrs' => 11.5], $funds['811']['fund']);
        $this->assertSame(['20yrs' => 12.3], $funds['811']['benchmark']);
        $this->assertSame(['20yrs' => 10.8], $funds['811']['peer']);
    }

    public function test_missing_performance_values_are_absent_keys(): void
    {
        $fund = $this->overviewFund();

        $path = $this->makeXlsx([
            ['Code', 'Value'],
            ['820_FOORD_20Y_TO_D', '—'],
            ['820_FOORD_15Y_TO_D', '-'],
            ['820_FOORD_10Y_TO_D', ''],
            ['820_FOORD_5Y_TO_D', 'n/a'],
            ['820_FOORD_3Y_TO_D', '17.34'],
            ['820_FOORD_1Y_TO_D', '14.8'],
        ], 'loc-perf-absent');

        (new LocalOverviewImporter)->import($fund, $path);

        $values = $this->perfFunds($fund)['820']['fund'];
        $this->assertSame(['3yrs' => 17.3, '1yr' => 14.8], $values);
        $this->assertArrayNotHasKey('20yrs', $values);
        $this->assertArrayNotHasKey('15yrs', $values);
        $this->assertArrayNotHasKey('10yrs', $values);
        $this->assertArrayNotHasKey('5yrs', $values);
    }

    public function test_bare_fund_gets_the_full_performance_skeleton(): void
    {
        $fund = $this->overviewFund(['performance_table' => null]);

        (new LocalOverviewImporter)->import($fund, $this->makeXlsx([['Code', 'Value']], 'loc-perf-bare'));

        $table = $fund->performance_table;
        $this->assertSame('PERFORMANCE %', $table['title']);
        $this->assertSame(['20 YEARS', '15 YEARS', '10 YEARS', '5 YEARS', '3 YEARS', '1 YEAR'], $table['headers']);
        $this->assertSame(['20yrs', '15yrs', '10yrs', '5yrs', '3yrs', '1yr'], $table['columnKeys']);
        $this->assertSame(['REGULATION 28', 'BEST INVESTMENT VIEW', 'SPECIALIST EQUITY'], array_column($table['groups'], 'label'));
        $this->assertSame(['818', '820', '810'], array_column($table['groups'][0]['funds'], 'code'));
        $this->assertSame(['817'], array_column($table['groups'][1]['funds'], 'code'));
        $this->assertSame(['811'], array_column($table['groups'][2]['funds'], 'code'));

        $funds = $this->perfFunds($fund);
        $this->assertSame('Foord Conservative', $funds['818']['name']);
        $this->assertSame('Class B2', $funds['818']['className']);
        $this->assertSame('Peer group: South Africa – Multi Asset – Medium Equity', $funds['818']['peerLabel']);
        $this->assertSame('Benchmark: CPI + 4% per annum', $funds['818']['benchmarkLabel']);
        $this->assertSame('Foord Domestic Balanced', $funds['820']['name']);
        $this->assertSame('Peer group: South Africa – Multi Asset – SA High Equity', $funds['820']['peerLabel']);
        $this->assertSame('Benchmark: Average peer group excluding Foord', $funds['820']['benchmarkLabel']);
        $this->assertSame('Foord Balanced', $funds['810']['name']);
        $this->assertSame('Peer group: South Africa – Multi Asset – High Equity', $funds['810']['peerLabel']);
        $this->assertSame('Benchmark: MV weighted peer group excluding Foord', $funds['810']['benchmarkLabel']);
        $this->assertSame('Foord Flexible', $funds['817']['name']);
        $this->assertSame('Peer group: Worldwide Multi Asset – Flexible', $funds['817']['peerLabel']);
        $this->assertSame('Benchmark: CPI + 5% per annum', $funds['817']['benchmarkLabel']);
        $this->assertSame('Foord Equity', $funds['811']['name']);
        $this->assertSame('Peer group: South Africa – Equity – General (SA only)', $funds['811']['peerLabel']);
        $this->assertSame('Benchmark: FTSE / JSE Capped All Share index', $funds['811']['benchmarkLabel']);
        // No values were exported: the maps exist but are empty.
        $this->assertSame([], $funds['818']['fund']);
        $this->assertSame([], $funds['818']['peer']);
        $this->assertSame([], $funds['818']['benchmark']);

        $this->assertSame([
            'Source: Foord, Stats SA, Morningstar (Peer group: provisional)',
            'Note: Investment returns for periods greater than one year are annualised. All performance numbers are shown net of fees and expenses.',
        ], $table['footnotes']);
    }

    public function test_seeded_performance_prose_survives_an_import(): void
    {
        $fund = $this->overviewFund([
            'performance_table' => [
                'title' => 'RETURNS %',
                'groups' => [
                    ['label' => 'REGULATION 28', 'funds' => [
                        ['code' => '810', 'name' => 'Foord Balanced Fund', 'className' => 'Class A',
                            'peerLabel' => 'Peer group: edited', 'benchmarkLabel' => 'Benchmark: edited',
                            'fund' => ['1yr' => 1.0]],
                    ]],
                ],
                'footnotes' => ['Source: edited'],
            ],
        ]);

        (new LocalOverviewImporter)->import($fund, $this->makeXlsx([
            ['Code', 'Value'],
            ['810_FOORD_1Y_TO_D', '9.1'],
        ], 'loc-perf-seeded'));

        $funds = $this->perfFunds($fund);
        $this->assertSame('RETURNS %', $fund->performance_table['title']);
        $this->assertSame(['Source: edited'], $fund->performance_table['footnotes']);
        $this->assertSame('Foord Balanced Fund', $funds['810']['name']);
        $this->assertSame('Class A', $funds['810']['className']);
        $this->assertSame('Peer group: edited', $funds['810']['peerLabel']);
        $this->assertSame('Benchmark: edited', $funds['810']['benchmarkLabel']);
        // Values are replaced wholesale — the stale 1.0 is gone.
        $this->assertSame(['1yr' => 9.1], $funds['810']['fund']);
        // The other four funds still get their defaults.
        $this->assertSame('Peer group: South Africa – Multi Asset – Medium Equity', $funds['818']['peerLabel']);
        $this->assertCount(3, $fund->performance_table['groups']);
    }

    // ── Asset allocation ─────────────────────────────────────────────────

    public function test_maps_asset_allocation_columns_rows_and_computed_total(): void
    {
        $fund = $this->overviewFund();

        $rows = [['Code', 'Value']];
        foreach (['DOM_TOTAL' => '95.5', 'DOM_EQ' => '0.0', 'DOM_PROP' => '1.6', 'DOM_DEBT' => '4.9', 'DOM_BOND' => '17.5', 'DOM_COMM' => '0.0', 'DOM_CASH' => '71.5',
            'FRGN_TOTAL' => '4.5', 'FRGN_EQ' => '0.0', 'FRGN_PROP' => '0.0', 'FRGN_DEBT' => '1.3', 'FRGN_BOND' => '5.1', 'FRGN_COMM' => '0.0', 'FRGN_CASH' => '-1.8', 'TOTAL_EQ' => '0.0'] as $k => $v) {
            $rows[] = ["824_AA_{$k}", $v];
        }
        $rows[] = ['810_AA_DOM_TOTAL', '58.8'];
        $rows[] = ['810_AA_FRGN_TOTAL', '41.2'];
        $rows[] = ['810_AA_DOM_EQ', '40.24'];
        $rows[] = ['810_AA_TOTAL_EQ', '68.3'];

        (new LocalOverviewImporter)->import($fund, $this->makeXlsx($rows, 'loc-aa'));

        $aa = $fund->asset_allocation;
        $this->assertSame('ASSET ALLOCATION %', $aa['title']);
        $this->assertSame(['824', '818', '820', '810', '817'], array_column($aa['columns'], 'code'));
        $this->assertSame('FOORD<br>FLEX INCOME', $aa['columns'][0]['label']);
        $this->assertSame('FOORD<br>DOMESTIC<br>BALANCED', $aa['columns'][2]['label']);

        $this->assertSame([
            'saTotal', 'domEq', 'domProp', 'domDebt', 'domBond', 'domComm', 'domCash',
            'foreignTotal', 'frgnEq', 'frgnProp', 'frgnDebt', 'frgnBond', 'frgnComm', 'frgnCash',
            'total', 'totalEq',
        ], array_column($aa['rows'], 'key'));

        $rows = $this->aaRows($fund);
        $this->assertSame('SOUTH AFRICA', $rows['saTotal']['label']);
        $this->assertSame('subtotal', $rows['saTotal']['style']);
        $this->assertSame('Money market', $rows['domCash']['label']);
        $this->assertSame('FOREIGN', $rows['foreignTotal']['label']);
        $this->assertSame('TOTAL', $rows['total']['label']);
        $this->assertSame('total', $rows['total']['style']);
        $this->assertSame('TOTAL EQUITY EXPOSURE', $rows['totalEq']['label']);
        $this->assertSame('subtotal', $rows['totalEq']['style']);

        $this->assertSame(95.5, $rows['saTotal']['values']['824']);
        $this->assertSame(71.5, $rows['domCash']['values']['824']);
        // Negatives preserved.
        $this->assertSame(-1.8, $rows['frgnCash']['values']['824']);
        // Integral floats lose their ".0" in the JSON cast; assertEquals here.
        $this->assertEquals(100.0, $rows['total']['values']['824']);
        $this->assertEquals(0.0, $rows['totalEq']['values']['824']);

        // Rounded to one decimal place.
        $this->assertSame(40.2, $rows['domEq']['values']['810']);
        $this->assertEquals(100.0, $rows['total']['values']['810']);
        $this->assertSame(68.3, $rows['totalEq']['values']['810']);

        // Funds without any AA keys have no column values (not zeros).
        $this->assertArrayNotHasKey('818', $rows['saTotal']['values']);
        $this->assertArrayNotHasKey('818', $rows['total']['values']);
        $this->assertArrayNotHasKey('817', $rows['totalEq']['values']);
    }

    public function test_seeded_asset_allocation_labels_survive_an_import(): void
    {
        $fund = $this->overviewFund([
            'asset_allocation' => [
                'title' => 'ALLOCATION %',
                'columns' => [['code' => '824', 'label' => 'FLEX<br>INCOME (edited)']],
                'rows' => [['key' => 'domCash', 'label' => 'Cash (edited)', 'values' => ['824' => 1.0]]],
            ],
        ]);

        (new LocalOverviewImporter)->import($fund, $this->makeXlsx([
            ['Code', 'Value'],
            ['824_AA_DOM_CASH', '71.5'],
        ], 'loc-aa-seeded'));

        $aa = $fund->asset_allocation;
        $this->assertSame('ALLOCATION %', $aa['title']);
        $this->assertSame('FLEX<br>INCOME (edited)', $aa['columns'][0]['label']);
        $this->assertSame('FOORD<br>CONSERVATIVE', $aa['columns'][1]['label']);
        $rows = $this->aaRows($fund);
        $this->assertSame('Cash (edited)', $rows['domCash']['label']);
        $this->assertSame(71.5, $rows['domCash']['values']['824']);
        $this->assertCount(16, $aa['rows']);
    }

    // ── Fixed-income statistics ──────────────────────────────────────────

    public function test_maps_fixed_income_statistics_with_albi_column(): void
    {
        $fund = $this->overviewFund();

        (new LocalOverviewImporter)->import($fund, $this->makeXlsx([
            ['Code', 'Value'],
            ['825_STAT_YIELD', '9.44%'],
            ['825_STAT_SA_FIXED_RATE_DURATION', '0.10'],
            ['825_STAT_SA_FLOATING_RATE_DURATION', '0.09'],
            ['825_STAT_SA_INFLATION_LINKED_DURATION', '0.58'],
            ['825_STAT_FOREIGN_FIXED_RATE_DURATION', '-'],
            ['825_STAT_FOREIGN_INFLATION_LINKED_DURATION', '-'],
            ['825_STAT_TOTAL_DURATION', '0.77'],
            ['824_STAT_YIELD', '8.91%'],
            ['824_STAT_SA_FIXED_RATE_DURATION', '0.12'],
            ['824_STAT_SA_FLOATING_RATE_DURATION', '0.08'],
            ['824_STAT_SA_INFLATION_LINKED_DURATION', '0.55'],
            ['824_STAT_FOREIGN_FIXED_RATE_DURATION', '0.01'],
            ['824_STAT_FOREIGN_INFLATION_LINKED_DURATION', '0.18'],
            ['824_STAT_TOTAL_DURATION', '0.94'],
            ['826_STAT_YIELD', '9.62%'],
            ['826_STAT_SA_FIXED_RATE_DURATION', '5.01'],
            ['826_STAT_SA_FLOATING_RATE_DURATION', '0.04'],
            ['826_STAT_SA_INFLATION_LINKED_DURATION', '1.06'],
            ['826_STAT_FOREIGN_FIXED_RATE_DURATION', '-'],
            ['826_STAT_FOREIGN_INFLATION_LINKED_DURATION', '-'],
            ['826_STAT_TOTAL_DURATION', '6.1'],
            ['826_BM_YIELD', '8.65%'],
            ['826_BM_SA_FIXED_RATE_DURATION_BOND', '6.2'],
            ['826_BM_SA_INFLATION_LINKED_DURATION', '-'],
            ['826_BM_SA_DURATION', '6.20'],
        ], 'loc-stats'));

        $block = $fund->page2_content['fixedIncome'];
        $this->assertSame('FIXED INCOME FUNDS', $block['title']);
        $this->assertSame('PORTFOLIO STATISTICS', $block['tableTitle']);
        $this->assertSame(['825', '824', '826', '826_BM'], array_column($block['columns'], 'code'));
        $this->assertSame('FOORD<br>INCOME', $block['columns'][0]['label']);
        $this->assertSame('FOORD<br>FLEX<br>INCOME', $block['columns'][1]['label']);
        $this->assertSame('FOORD<br>BOND', $block['columns'][2]['label']);
        $this->assertSame('ALBI', $block['columns'][3]['label']);
        $this->assertSame(
            ['yield', 'saFixed', 'saFloating', 'saInflation', 'offshoreFixed', 'offshoreInflation', 'totalDuration'],
            array_column($block['rows'], 'key')
        );

        $rows = $this->statRows($fund);
        $this->assertSame('Yield', $rows['yield']['label']);
        $this->assertSame('SA fixed rate duration', $rows['saFixed']['label']);
        $this->assertSame('SA floating rate duration', $rows['saFloating']['label']);
        $this->assertSame('SA inflation linked duration', $rows['saInflation']['label']);
        $this->assertSame('Offshore fixed rate duration', $rows['offshoreFixed']['label']);
        $this->assertSame('Offshore inflation linked duration', $rows['offshoreInflation']['label']);
        $this->assertSame('TOTAL DURATION', $rows['totalDuration']['label']);
        $this->assertSame('total', $rows['totalDuration']['style']);

        // Yield is the feed string as-is; durations are two-decimal strings.
        $this->assertSame(['825' => '9.44%', '824' => '8.91%', '826' => '9.62%', '826_BM' => '8.65%'], $rows['yield']['values']);
        $this->assertSame(['825' => '0.10', '824' => '0.12', '826' => '5.01', '826_BM' => '6.20'], $rows['saFixed']['values']);
        $this->assertSame(['825' => '0.09', '824' => '0.08', '826' => '0.04', '826_BM' => '-'], $rows['saFloating']['values']);
        $this->assertSame(['825' => '0.58', '824' => '0.55', '826' => '1.06', '826_BM' => '-'], $rows['saInflation']['values']);
        $this->assertSame(['825' => '-', '824' => '0.01', '826' => '-', '826_BM' => '-'], $rows['offshoreFixed']['values']);
        $this->assertSame(['825' => '-', '824' => '0.18', '826' => '-', '826_BM' => '-'], $rows['offshoreInflation']['values']);
        $this->assertSame(['825' => '0.77', '824' => '0.94', '826' => '6.10', '826_BM' => '6.20'], $rows['totalDuration']['values']);
    }

    public function test_unusable_statistics_keep_the_stored_value_or_fall_back_to_a_dash(): void
    {
        $fund = $this->overviewFund([
            'page2_content' => [
                'fixedIncome' => [
                    'rows' => [
                        ['key' => 'yield', 'values' => ['825' => '9.44%', '826' => '9.62%', '826_BM' => '8.65%']],
                        ['key' => 'saFixed', 'values' => ['824' => '0.12']],
                    ],
                ],
                'somethingElse' => ['keep' => true],
            ],
        ]);

        // August's export: every stat defective in one way or another.
        (new LocalOverviewImporter)->import($fund, $this->makeXlsx([
            ['Code', 'Value'],
            ['824_STAT_YIELD', '%'],
            ['824_STAT_SA_FIXED_RATE_DURATION', ''],
            ['825_STAT_YIELD', '%'],
            ['825_STAT_SA_FIXED_RATE_DURATION', ''],
            ['826_STAT_YIELD', 'ERR>>>0>>>LINK%'],
            ['826_STAT_SA_FIXED_RATE_DURATION', 'ERR'],
            ['826_BM_YIELD', '%'],
        ], 'loc-stats-defective'));

        $rows = $this->statRows($fund);
        // Stored values survive...
        $this->assertSame('9.44%', $rows['yield']['values']['825']);
        $this->assertSame('9.62%', $rows['yield']['values']['826']);
        $this->assertSame('8.65%', $rows['yield']['values']['826_BM']);
        $this->assertSame('0.12', $rows['saFixed']['values']['824']);
        // ...and cells with nothing stored fall back to a dash.
        $this->assertSame('-', $rows['yield']['values']['824']);
        $this->assertSame('-', $rows['saFixed']['values']['825']);
        $this->assertSame('-', $rows['saFixed']['values']['826']);
        $this->assertSame('-', $rows['saFloating']['values']['826_BM']);
        // Unrelated page2_content keys are preserved.
        $this->assertSame(['keep' => true], $fund->page2_content['somethingElse']);
    }

    public function test_seeded_fixed_income_labels_survive_an_import(): void
    {
        $fund = $this->overviewFund([
            'page2_content' => [
                'fixedIncome' => [
                    'title' => 'INCOME FUNDS (edited)',
                    'columns' => [['code' => '826_BM', 'label' => 'ALL BOND INDEX']],
                    'rows' => [['key' => 'yield', 'label' => 'Running yield']],
                ],
            ],
        ]);

        (new LocalOverviewImporter)->import($fund, $this->makeXlsx([['Code', 'Value']], 'loc-stats-seeded'));

        $block = $fund->page2_content['fixedIncome'];
        $this->assertSame('INCOME FUNDS (edited)', $block['title']);
        $this->assertSame('PORTFOLIO STATISTICS', $block['tableTitle']);
        $this->assertSame('ALL BOND INDEX', $block['columns'][3]['label']);
        $this->assertSame('Running yield', $this->statRows($fund)['yield']['label']);
    }

    // ── Maturity breakdown ───────────────────────────────────────────────

    public function test_maps_six_maturity_buckets_with_fund_and_benchmark(): void
    {
        $fund = $this->overviewFund(['chart_data' => ['performanceData' => ['keep' => true]]]);

        (new LocalOverviewImporter)->import($fund, $this->makeXlsx([
            ['Code', 'Value'],
            ['826_MATURITY_0_TO_1_YEAR', '-17'],
            ['826_MATURITY_BM_0_TO_1_YEAR', '10'],
            ['826_MATURITY_1_TO_3_YEARS', '20'],
            ['826_MATURITY_BM_1_TO_3_YEARS', '20'],
            ['826_MATURITY_3_TO_7_YEARS', '42'],
            ['826_MATURITY_BM_3_TO_7_YEARS', '30'],
            ['826_MATURITY_7_TO_12_YEARS', '15'],
            ['826_MATURITY_BM_7_TO_12_YEARS', '40'],
            ['826_MATURITY_12_TO_20_YEARS', '24.5'],
            ['826_MATURITY_BM_12_TO_20_YEARS', '-'],
            ['826_MATURITY_20_PLUS_YEARS', '16'],
            ['826_MATURITY_BM_20_PLUS_YEARS', '60'],
        ], 'loc-maturity'));

        $maturity = $fund->chart_data['maturityData'];
        $this->assertSame('Foord Bond Fund Maturity Breakdown', $maturity['title']);
        $this->assertSame(
            ['0-1 Year', '1-3 Years', '3-7 Years', '7-12 Years', '12-20 Years', '20+ Years'],
            array_column($maturity['categories'], 'name')
        );
        $this->assertSame(['name' => '0-1 Year', 'fund' => -17, 'benchmark' => 10], $maturity['categories'][0]);
        $this->assertSame(['name' => '3-7 Years', 'fund' => 42, 'benchmark' => 30], $maturity['categories'][2]);
        $this->assertSame(['name' => '12-20 Years', 'fund' => 24.5, 'benchmark' => 0], $maturity['categories'][4]);
        $this->assertSame(['name' => '20+ Years', 'fund' => 16, 'benchmark' => 60], $maturity['categories'][5]);
        // Other chart_data keys are preserved.
        $this->assertSame(['keep' => true], $fund->chart_data['performanceData']);
    }

    // ── Sector exposure ──────────────────────────────────────────────────

    public function test_maps_sectors_and_normalises_their_names(): void
    {
        $fund = $this->overviewFund();

        (new LocalOverviewImporter)->import($fund, $this->makeXlsx([
            ['Code', 'Value'],
            ['811_ESAOT_RANK_1_ITEM', 'Consumer/services'],
            ['811_ESAOT_RANK_1_CURRENT', '26'],
            ['811_ESAOT_RANK_1_BENCHMARK', '7'],
            ['811_ESAOT_RANK_2_ITEM', 'Money market'],
            ['811_ESAOT_RANK_2_CURRENT', '7'],
            ['811_ESAOT_RANK_2_BENCHMARK', '-'],
            ['811_ESAOT_RANK_3_ITEM', 'Capital goods / construction'],
            ['811_ESAOT_RANK_3_CURRENT', '5'],
            ['811_ESAOT_RANK_3_BENCHMARK', '0'],
            ['811_ESAOT_RANK_4_ITEM', 'PROPERTY'],
            ['811_ESAOT_RANK_4_CURRENT', '3'],
            ['811_ESAOT_RANK_4_BENCHMARK', '5'],
            ['811_ESAOT_RANK_5_ITEM', 'Commodities'],
            ['811_ESAOT_RANK_5_CURRENT', '1.5'],
            ['811_ESAOT_RANK_5_BENCHMARK', ''],
            ['811_ESAOT_RANK_6_ITEM', ''],
            ['811_ESAOT_RANK_6_CURRENT', '-'],
            ['811_ESAOT_RANK_6_BENCHMARK', '-'],
        ], 'loc-sectors'));

        $sectors = $fund->sector_allocation;
        $this->assertSame('SECTOR EXPOSURE (FOORD EQUITY)', $sectors['title']);
        $this->assertSame([
            ['name' => 'Consumer / services', 'fund' => 26, 'benchmark' => 7],
            ['name' => 'Money market', 'fund' => 7, 'benchmark' => 0],
            ['name' => 'Capital goods / construction', 'fund' => 5, 'benchmark' => 0],
            ['name' => 'Property', 'fund' => 3, 'benchmark' => 5],
            ['name' => 'Commodities', 'fund' => 1.5, 'benchmark' => 0],
        ], $sectors['sectors']);
    }

    public function test_sector_count_follows_the_export(): void
    {
        $rows = fn (int $n): array => array_merge([['Code', 'Value']], ...array_map(fn (int $i): array => [
            ["811_ESAOT_RANK_{$i}_ITEM", "Sector {$i}"],
            ["811_ESAOT_RANK_{$i}_CURRENT", (string) $i],
            ["811_ESAOT_RANK_{$i}_BENCHMARK", (string) ($i + 1)],
        ], range(1, $n)));

        $fund = $this->overviewFund();
        (new LocalOverviewImporter)->import($fund, $this->makeXlsx($rows(13), 'loc-sectors-13'));
        $this->assertCount(13, $fund->sector_allocation['sectors']);
        $this->assertSame(['name' => 'Sector 13', 'fund' => 13, 'benchmark' => 14], $fund->sector_allocation['sectors'][12]);

        (new LocalOverviewImporter)->import($fund, $this->makeXlsx($rows(12), 'loc-sectors-12'));
        $this->assertCount(12, $fund->sector_allocation['sectors']);
    }

    /**
     * The July export carries the sector list under bare ESAOT_RANK_n keys
     * (no 811_ prefix) and without BENCHMARK cells; a stored benchmark for
     * the same sector name survives, anything else reads 0.
     */
    public function test_sectors_fall_back_to_unprefixed_keys_and_keep_stored_benchmarks(): void
    {
        $fund = $this->overviewFund([
            'sector_allocation' => ['title' => 'SECTORS (edited)', 'sectors' => [
                ['name' => 'Consumer / services', 'fund' => 20, 'benchmark' => 7],
            ]],
        ]);

        (new LocalOverviewImporter)->import($fund, $this->makeXlsx([
            ['Code', 'Value'],
            ['ESAOT_RANK_1_ITEM', 'Consumer/services'],
            ['ESAOT_RANK_1_CURRENT', '26'],
            ['ESAOT_RANK_2_ITEM', 'Financials'],
            ['ESAOT_RANK_2_CURRENT', '18'],
            ['ESAOT_RANK_3_ITEM', ''],
            ['ESAOT_RANK_3_CURRENT', '-'],
        ], 'loc-sectors-unprefixed'));

        $this->assertSame('SECTORS (edited)', $fund->sector_allocation['title']);
        $this->assertSame([
            ['name' => 'Consumer / services', 'fund' => 26, 'benchmark' => 7],
            ['name' => 'Financials', 'fund' => 18, 'benchmark' => 0],
        ], $fund->sector_allocation['sectors']);
    }

    // ── Synopsis / strategy / quarter ────────────────────────────────────

    public function test_maps_synopsis_and_strategy_bullets_with_the_quarter(): void
    {
        $fund = $this->overviewFund();

        $rows = [['Code', 'Value'], ['MONTH_END_DATE', '31 July 2026']];
        foreach (['WORLD_SYN', 'SA_SYN', 'WORLD_STRAT', 'SA_STRAT'] as $prefix) {
            for ($i = 1; $i <= 5; $i++) {
                $rows[] = ["{$prefix}_{$i}", "{$prefix} bullet {$i}"];
            }
        }

        (new LocalOverviewImporter)->import($fund, $this->makeXlsx($rows, 'loc-bullets'));

        $synopsis = $fund->page2_content['synopsis'];
        $strategy = $fund->page2_content['strategy'];
        $this->assertSame('MARKET SYNOPSIS', $synopsis['title']);
        $this->assertSame('Q2 2026', $synopsis['quarter']);
        $this->assertSame('STRATEGY', $strategy['title']);
        $this->assertSame('Q2 2026', $strategy['quarter']);
        $this->assertCount(5, $synopsis['world']);
        $this->assertCount(5, $synopsis['southAfrica']);
        $this->assertCount(5, $strategy['world']);
        $this->assertCount(5, $strategy['southAfrica']);
        $this->assertSame('WORLD_SYN bullet 1', $synopsis['world'][0]);
        $this->assertSame('SA_SYN bullet 5', $synopsis['southAfrica'][4]);
        $this->assertSame('WORLD_STRAT bullet 3', $strategy['world'][2]);
        $this->assertSame('SA_STRAT bullet 1', $strategy['southAfrica'][0]);

        $this->assertSame('Note: Totals may not cast perfectly due to rounding', $fund->page2_content['roundingNote']);
        $this->assertSame([
            'heading' => 'SCAN QR CODE',
            'text' => 'to read regulatory disclosures or visit:',
            'url' => 'https://foord.co.za/terms-conditions-sa',
            'image' => 'images/qr-terms-sa.png',
        ], $fund->page2_content['qr']);
    }

    public function test_bullets_stop_at_the_first_blank_and_prose_survives(): void
    {
        $fund = $this->overviewFund([
            'page2_content' => [
                'synopsis' => ['title' => 'MARKET VIEW', 'quarter' => 'Q1 2026', 'world' => ['old'], 'southAfrica' => ['old']],
                'roundingNote' => 'Note: edited',
                'qr' => ['heading' => 'SCAN', 'text' => 'edited', 'url' => 'https://example.test', 'image' => 'images/x.png'],
            ],
        ]);

        (new LocalOverviewImporter)->import($fund, $this->makeXlsx([
            ['Code', 'Value'],
            ['WORLD_SYN_1', 'one'],
            ['WORLD_SYN_2', 'two'],
            ['WORLD_SYN_3', ''],
            ['WORLD_SYN_4', 'four'],
            ['SA_SYN_1', 'sa one'],
        ], 'loc-bullets-partial'));

        $synopsis = $fund->page2_content['synopsis'];
        $this->assertSame('MARKET VIEW', $synopsis['title']);
        // No MONTH_END_DATE in this export: the stored quarter is kept.
        $this->assertSame('Q1 2026', $synopsis['quarter']);
        $this->assertSame(['one', 'two'], $synopsis['world']);
        $this->assertSame(['sa one'], $synopsis['southAfrica']);
        $this->assertSame('Note: edited', $fund->page2_content['roundingNote']);
        $this->assertSame('edited', $fund->page2_content['qr']['text']);
    }

    public function test_quarter_label_is_the_last_completed_quarter(): void
    {
        $this->assertSame('Q2 2026', LocalOverviewImporter::quarterLabelFor('31 July 2026'));
        $this->assertSame('Q2 2026', LocalOverviewImporter::quarterLabelFor('31 August 2026'));
        $this->assertSame('Q2 2026', LocalOverviewImporter::quarterLabelFor('30 September 2026'));
        $this->assertSame('Q3 2026', LocalOverviewImporter::quarterLabelFor('31 October 2026'));
        $this->assertSame('Q4 2026', LocalOverviewImporter::quarterLabelFor('31 January 2027'));
        $this->assertSame('Q4 2026', LocalOverviewImporter::quarterLabelFor('31 March 2027'));
        $this->assertSame('Q1 2027', LocalOverviewImporter::quarterLabelFor('30 April 2027'));
        $this->assertNull(LocalOverviewImporter::quarterLabelFor('not a date'));
        $this->assertNull(LocalOverviewImporter::quarterLabelFor(''));
    }

    // ── Real export ──────────────────────────────────────────────────────

    public function test_imports_the_real_july_export(): void
    {
        $path = base_path('storage/app/private/fund-data/2026-07/LOC/LOCAL_OVERVIEW.xlsx');
        if (! is_file($path)) {
            $this->markTestSkipped('Real July LOCAL_OVERVIEW export not present.');
        }

        $fund = $this->overviewFund();
        (new LocalOverviewImporter)->import($fund, $path);

        $this->assertSame('31 July 2026', $fund->fund_date);
        $this->assertSame(8.7, $this->perfFunds($fund)['818']['fund']['10yrs']);
        $this->assertSame('9.44%', $this->statRows($fund)['yield']['values']['825']);
        $this->assertCount(6, $fund->chart_data['maturityData']['categories']);
        $this->assertSame('Consumer / services', $fund->sector_allocation['sectors'][0]['name']);
        $this->assertSame('Q2 2026', $fund->page2_content['synopsis']['quarter']);
        $this->assertCount(5, $fund->page2_content['strategy']['southAfrica']);
    }
}
