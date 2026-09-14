<?php

namespace Tests\Feature;

use App\Models\Fund;
use App\Services\FundImport\FundImportManager;
use App\Services\FundImport\GlobalOverviewImporter;
use App\Services\FundImport\LocalOverviewImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

/**
 * The FUND OVERVIEW: GLOBAL sheet is one export (GLOBAL_OVERVIEW.xlsx)
 * covering three funds' performance (875, 877, 879), their asset
 * allocation, three geographic-exposure pies, the 877 sector chart and the
 * quarterly world/Asia synopsis and strategy bullets. The importer owns
 * every table skeleton; the stored record contributes prose — and, until
 * the feed carries pie/sector figures, the chart values too.
 */
class ExcelImportGlobalOverviewTest extends TestCase
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
            'template' => 'show-global-overview',
            'fund_code' => 'GLB',
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

    /** @return array<string, array<string, mixed>> geographic-exposure pies keyed by code */
    private function pies(Fund $fund): array
    {
        return collect($fund->chart_data['geographicExposure']['funds'])->keyBy('code')->all();
    }

    /** A seeded three-pie block with one slice per fund. */
    private function seededPies(): array
    {
        return [
            'title' => 'GEO (edited)',
            'funds' => [
                ['code' => '875', 'label' => 'INTERNATIONAL (edited)', 'footnote' => '1', 'slices' => [['name' => 'North America', 'value' => 40.5]]],
                ['code' => '877', 'label' => 'FOORD GLOBAL EQUITY FUND', 'footnote' => '2', 'slices' => [['name' => 'Europe', 'value' => 33]]],
                ['code' => '879', 'label' => 'FOORD ASIA EX-JAPAN FUND', 'footnote' => '2', 'slices' => [['name' => 'China', 'value' => 50]]],
            ],
            'footnotes' => [['marker' => '1', 'text' => 'Gross (edited)'], ['marker' => '2', 'text' => 'Equity only exposure']],
        ];
    }

    // ── Routing ──────────────────────────────────────────────────────────

    public function test_supports_global_overview_exports_only(): void
    {
        $importer = new GlobalOverviewImporter;

        $this->assertTrue($importer->supports('/feed/GLOBAL_OVERVIEW.xlsx'));
        $this->assertTrue($importer->supports('global_overview_2049089080.xlsx'));
        $this->assertFalse($importer->supports('LOCAL_OVERVIEW.xlsx'));
        $this->assertFalse($importer->supports('877R1_FACTSHEET.xlsx'));
        $this->assertSame('global fund overview', $importer->label());
    }

    public function test_manager_routes_each_overview_sheet_to_its_own_importer(): void
    {
        $manager = new FundImportManager;

        $this->assertInstanceOf(GlobalOverviewImporter::class, $manager->importerFor('GLOBAL_OVERVIEW_123.xlsx'));
        $this->assertInstanceOf(LocalOverviewImporter::class, $manager->importerFor('LOCAL_OVERVIEW.xlsx'));
        $this->assertNotInstanceOf(GlobalOverviewImporter::class, $manager->importerFor('LOCAL_OVERVIEW.xlsx'));
    }

    // ── Date + performance ───────────────────────────────────────────────

    public function test_maps_fund_date_and_peer_from_comp_1_benchmark_from_comp_2(): void
    {
        $fund = $this->overviewFund();

        (new GlobalOverviewImporter)->import($fund, $this->makeXlsx([
            ['Code', 'Value', 'System Comment'],
            ['MONTH_END_DATE', '31 August 2026'],
            ['875_FOORD_20Y_TO_D', '6.0'],
            ['875_FOORD_10Y_TO_D', '6.8'],
            ['875_FOORD_1Y_TO_D', '19.5'],
            ['875_FOORD_COMP_1_20Y_TO_D', '5.2'],
            ['875_FOORD_COMP_1_1Y_TO_D', '12.2'],
            ['875_FOORD_COMP_2_20Y_TO_D', '2.5'],
            ['875_FOORD_COMP_2_1Y_TO_D', '2.94'],
            ['879_FOORD_3Y_TO_D', '15.8'],
            ['879_FOORD_COMP_1_3Y_TO_D', '21.3'],
            ['879_FOORD_COMP_2_3Y_TO_D', '24.6'],
        ], 'glb-perf'));

        $this->assertSame('31 August 2026', $fund->fund_date);

        $funds = $this->perfFunds($fund);
        // The JSON cast drops the ".0" from integral floats, hence assertEquals for 6.0.
        $this->assertEquals(['20yrs' => 6.0, '10yrs' => 6.8, '1yr' => 19.5], $funds['875']['fund']);
        $this->assertSame(['20yrs' => 5.2, '1yr' => 12.2], $funds['875']['peer']);
        $this->assertSame(['20yrs' => 2.5, '1yr' => 2.9], $funds['875']['benchmark']);
        $this->assertSame(['3yrs' => 15.8], $funds['879']['fund']);
        $this->assertSame(['3yrs' => 21.3], $funds['879']['peer']);
        $this->assertSame(['3yrs' => 24.6], $funds['879']['benchmark']);
        $this->assertSame([], $funds['877']['fund']);
    }

    public function test_em_dash_performance_values_are_absent_keys(): void
    {
        $fund = $this->overviewFund();

        (new GlobalOverviewImporter)->import($fund, $this->makeXlsx([
            ['Code', 'Value'],
            ['877_FOORD_20Y_TO_D', '—'],
            ['877_FOORD_15Y_TO_D', '—'],
            ['877_FOORD_10Y_TO_D', '8.0'],
            ['877_FOORD_5Y_TO_D', ''],
            ['877_FOORD_3Y_TO_D', '-'],
            ['877_FOORD_1Y_TO_D', '6.0'],
            ['877_FOORD_COMP_1_20Y_TO_D', '—'],
            ['877_FOORD_COMP_1_1Y_TO_D', '22.3'],
            ['877_FOORD_COMP_2_20Y_TO_D', '—'],
            ['877_FOORD_COMP_2_1Y_TO_D', '22.3'],
        ], 'glb-perf-absent'));

        $entry = $this->perfFunds($fund)['877'];
        $this->assertEquals(['10yrs' => 8.0, '1yr' => 6.0], $entry['fund']);
        $this->assertArrayNotHasKey('20yrs', $entry['fund']);
        $this->assertArrayNotHasKey('15yrs', $entry['fund']);
        $this->assertArrayNotHasKey('5yrs', $entry['fund']);
        $this->assertArrayNotHasKey('3yrs', $entry['fund']);
        $this->assertSame(['1yr' => 22.3], $entry['peer']);
        $this->assertSame(['1yr' => 22.3], $entry['benchmark']);
    }

    public function test_bare_fund_gets_the_full_performance_skeleton_with_feeder_notes(): void
    {
        $fund = $this->overviewFund(['performance_table' => null]);

        (new GlobalOverviewImporter)->import($fund, $this->makeXlsx([['Code', 'Value']], 'glb-perf-bare'));

        $table = $fund->performance_table;
        $this->assertSame('PERFORMANCE %', $table['title']);
        $this->assertSame(['20 YEARS', '15 YEARS', '10 YEARS', '5 YEARS', '3 YEARS', '1 YEAR'], $table['headers']);
        $this->assertSame(['20yrs', '15yrs', '10yrs', '5yrs', '3yrs', '1yr'], $table['columnKeys']);
        $this->assertSame(['BEST INVESTMENT VIEW', 'SPECIALIST EQUITY'], array_column($table['groups'], 'label'));
        $this->assertSame(['875'], array_column($table['groups'][0]['funds'], 'code'));
        $this->assertSame(['877', '879'], array_column($table['groups'][1]['funds'], 'code'));

        $funds = $this->perfFunds($fund);
        $this->assertSame('Foord International', $funds['875']['name']);
        $this->assertSame('Class R in USD', $funds['875']['className']);
        $this->assertSame('Peer group: Morningstar (USD Flexible Allocation)', $funds['875']['peerLabel']);
        $this->assertSame('Benchmark: US Inflation', $funds['875']['benchmarkLabel']);
        $this->assertSame('(SA Feeder Fund: Prescient Foord International Feeder Fund)', $funds['875']['feederNote']);

        $this->assertSame('Foord Global Equity', $funds['877']['name']);
        $this->assertSame('Class R1 in USD', $funds['877']['className']);
        $this->assertSame('Peer group: Morningstar (Global Large-Cap Blend Equity)', $funds['877']['peerLabel']);
        $this->assertSame('Benchmark: MSCI All Country World Net Total Return', $funds['877']['benchmarkLabel']);
        $this->assertSame('(SA Feeder Fund: Prescient Foord Global Equity Feeder Fund)', $funds['877']['feederNote']);

        $this->assertSame('Foord Asia ex-Japan', $funds['879']['name']);
        $this->assertSame('Class R in USD', $funds['879']['className']);
        $this->assertSame('Peer group: Morningstar (Asia ex-Japan Equity)', $funds['879']['peerLabel']);
        $this->assertSame('Benchmark: MSCI Asia ex-Japan USD', $funds['879']['benchmarkLabel']);
        $this->assertArrayNotHasKey('feederNote', $funds['879']);

        // No values were exported: the maps exist but are empty.
        $this->assertSame([], $funds['875']['fund']);
        $this->assertSame([], $funds['875']['peer']);
        $this->assertSame([], $funds['875']['benchmark']);

        $this->assertSame([
            'Source: Foord, Morningstar (Peer group: provisional).',
            'Note: Investment returns for periods greater than one year are annualised. All performance numbers are shown net of fees and expenses.',
            'US headline consumer price index. Source: Bloomberg L.P. (lagged by one month).',
        ], $table['footnotes']);
    }

    public function test_seeded_performance_prose_survives_an_import(): void
    {
        $fund = $this->overviewFund([
            'performance_table' => [
                'title' => 'RETURNS %',
                'groups' => [
                    ['label' => 'BEST INVESTMENT VIEW', 'funds' => [
                        ['code' => '875', 'name' => 'Foord International Fund', 'className' => 'Class B',
                            'peerLabel' => 'Peer group: edited', 'benchmarkLabel' => 'Benchmark: edited',
                            'feederNote' => '(Feeder: edited)', 'fund' => ['1yr' => 1.0]],
                    ]],
                ],
                'footnotes' => ['Source: edited'],
            ],
        ]);

        (new GlobalOverviewImporter)->import($fund, $this->makeXlsx([
            ['Code', 'Value'],
            ['875_FOORD_1Y_TO_D', '19.5'],
        ], 'glb-perf-seeded'));

        $funds = $this->perfFunds($fund);
        $this->assertSame('RETURNS %', $fund->performance_table['title']);
        $this->assertSame(['Source: edited'], $fund->performance_table['footnotes']);
        $this->assertSame('Foord International Fund', $funds['875']['name']);
        $this->assertSame('Class B', $funds['875']['className']);
        $this->assertSame('Peer group: edited', $funds['875']['peerLabel']);
        $this->assertSame('Benchmark: edited', $funds['875']['benchmarkLabel']);
        $this->assertSame('(Feeder: edited)', $funds['875']['feederNote']);
        // Values are replaced wholesale — the stale 1.0 is gone.
        $this->assertSame(['1yr' => 19.5], $funds['875']['fund']);
        // The other two funds still get their defaults.
        $this->assertSame('Peer group: Morningstar (Asia ex-Japan Equity)', $funds['879']['peerLabel']);
        $this->assertCount(2, $fund->performance_table['groups']);
    }

    // ── Asset allocation ─────────────────────────────────────────────────

    public function test_maps_asset_allocation_with_blank_hedged_equities_and_computed_total(): void
    {
        $fund = $this->overviewFund();

        (new GlobalOverviewImporter)->import($fund, $this->makeXlsx([
            ['Code', 'Value'],
            ['875_AA_TOTAL_EQ', '59.2'],
            ['875_AA_TOTAL_HEQ', ''],
            ['875_AA_TOTAL_PROP', '6.0'],
            ['875_AA_TOTAL_DEBT', '2.8'],
            ['875_AA_TOTAL_BOND', '5.8'],
            ['875_AA_TOTAL_COMM', '7.0'],
            ['875_AA_TOTAL_CASH', '19.2'],
            ['877_AA_TOTAL_EQ', '94.64'],
            ['877_AA_TOTAL_HEQ', '-'],
            ['877_AA_TOTAL_PROP', '0.7'],
            ['877_AA_TOTAL_DEBT', '0.0'],
            ['877_AA_TOTAL_BOND', '0.0'],
            ['877_AA_TOTAL_COMM', '0.0'],
            ['877_AA_TOTAL_CASH', '4.7'],
        ], 'glb-aa'));

        $aa = $fund->asset_allocation;
        $this->assertSame('ASSET ALLOCATION %', $aa['title']);
        $this->assertSame(['875', '877', '879'], array_column($aa['columns'], 'code'));
        $this->assertSame('FOORD<br>INTERNATIONAL', $aa['columns'][0]['label']);
        $this->assertSame('FOORD<br>GLOBAL EQUITY', $aa['columns'][1]['label']);
        $this->assertSame('FOORD<br>ASIA EX-JAPAN', $aa['columns'][2]['label']);
        $this->assertSame(['netEq', 'hedgedEq', 'prop', 'debt', 'bond', 'comm', 'cash', 'total'], array_column($aa['rows'], 'key'));

        $rows = $this->aaRows($fund);
        $this->assertSame('Net equities', $rows['netEq']['label']);
        $this->assertSame('Hedged equities', $rows['hedgedEq']['label']);
        $this->assertSame('muted', $rows['hedgedEq']['style']);
        $this->assertSame('Property', $rows['prop']['label']);
        $this->assertSame('Corporate bonds', $rows['debt']['label']);
        $this->assertSame('Government bonds', $rows['bond']['label']);
        $this->assertSame('Commodities', $rows['comm']['label']);
        $this->assertSame('Money market', $rows['cash']['label']);
        $this->assertSame('TOTAL', $rows['total']['label']);
        $this->assertSame('total', $rows['total']['style']);
        $this->assertArrayNotHasKey('style', $rows['netEq']);

        $this->assertSame(59.2, $rows['netEq']['values']['875']);
        // A blank (or "-") HEQ cell prints 0.0, as the reference does.
        $this->assertEquals(0.0, $rows['hedgedEq']['values']['875']);
        $this->assertArrayHasKey('875', $rows['hedgedEq']['values']);
        $this->assertEquals(0.0, $rows['hedgedEq']['values']['877']);
        $this->assertSame(19.2, $rows['cash']['values']['875']);
        $this->assertEquals(100.0, $rows['total']['values']['875']);
        // Rounded to one decimal place.
        $this->assertSame(94.6, $rows['netEq']['values']['877']);
        $this->assertEquals(100.0, $rows['total']['values']['877']);

        // A fund without any AA keys has no column values (not zeros).
        $this->assertArrayNotHasKey('879', $rows['netEq']['values']);
        $this->assertArrayNotHasKey('879', $rows['hedgedEq']['values']);
        $this->assertArrayNotHasKey('879', $rows['total']['values']);
    }

    public function test_seeded_asset_allocation_labels_survive_an_import(): void
    {
        $fund = $this->overviewFund([
            'asset_allocation' => [
                'title' => 'ALLOCATION %',
                'columns' => [['code' => '879', 'label' => 'ASIA (edited)']],
                'rows' => [['key' => 'cash', 'label' => 'Cash (edited)', 'values' => ['879' => 1.0]]],
            ],
        ]);

        (new GlobalOverviewImporter)->import($fund, $this->makeXlsx([
            ['Code', 'Value'],
            ['879_AA_TOTAL_CASH', '7.3'],
        ], 'glb-aa-seeded'));

        $aa = $fund->asset_allocation;
        $this->assertSame('ALLOCATION %', $aa['title']);
        $this->assertSame('FOORD<br>INTERNATIONAL', $aa['columns'][0]['label']);
        $this->assertSame('ASIA (edited)', $aa['columns'][2]['label']);
        $rows = $this->aaRows($fund);
        $this->assertSame('Cash (edited)', $rows['cash']['label']);
        $this->assertSame(7.3, $rows['cash']['values']['879']);
        // Only CASH was exported: the total is that one figure.
        $this->assertSame(7.3, $rows['total']['values']['879']);
        $this->assertCount(8, $aa['rows']);
    }

    // ── Geographic exposure ──────────────────────────────────────────────

    public function test_geographic_slices_are_kept_when_the_feed_carries_names_only(): void
    {
        $fund = $this->overviewFund(['chart_data' => [
            'geographicExposure' => $this->seededPies(),
            'performanceData' => ['keep' => true],
        ]]);

        (new GlobalOverviewImporter)->import($fund, $this->makeXlsx([
            ['Code', 'Value'],
            ['875_GEO_EXP_TOTAL_RANK_1', 'North America'],
            ['875_GEO_EXP_TOTAL_RANK_2', 'Europe'],
            ['877_GEO_EXP_TOTAL_RANK_1', 'Europe'],
            ['879_COUNTRY_EXP_RANK_1', 'China'],
            ['879_COUNTRY_EXP_RANK_2', 'Korea'],
        ], 'glb-geo-names'));

        $geo = $fund->chart_data['geographicExposure'];
        $this->assertSame('GEO (edited)', $geo['title']);
        $this->assertSame(['875', '877', '879'], array_column($geo['funds'], 'code'));
        $this->assertSame([['marker' => '1', 'text' => 'Gross (edited)'], ['marker' => '2', 'text' => 'Equity only exposure']], $geo['footnotes']);

        $pies = $this->pies($fund);
        $this->assertSame('INTERNATIONAL (edited)', $pies['875']['label']);
        $this->assertSame('1', $pies['875']['footnote']);
        $this->assertSame([['name' => 'North America', 'value' => 40.5]], $pies['875']['slices']);
        $this->assertSame([['name' => 'Europe', 'value' => 33]], $pies['877']['slices']);
        $this->assertSame([['name' => 'China', 'value' => 50]], $pies['879']['slices']);
        // Other chart_data keys are preserved.
        $this->assertSame(['keep' => true], $fund->chart_data['performanceData']);
    }

    public function test_bare_fund_gets_the_geographic_skeleton_with_empty_slices(): void
    {
        $fund = $this->overviewFund(['chart_data' => null]);

        (new GlobalOverviewImporter)->import($fund, $this->makeXlsx([
            ['Code', 'Value'],
            ['875_GEO_EXP_TOTAL_RANK_1', 'North America'],
        ], 'glb-geo-bare'));

        $geo = $fund->chart_data['geographicExposure'];
        $this->assertSame('GEOGRAPHIC EXPOSURE', $geo['title']);
        $this->assertSame([
            ['code' => '875', 'label' => 'FOORD INTERNATIONAL FUND', 'footnote' => '1', 'slices' => []],
            ['code' => '877', 'label' => 'FOORD GLOBAL EQUITY FUND', 'footnote' => '2', 'slices' => []],
            ['code' => '879', 'label' => 'FOORD ASIA EX-JAPAN FUND', 'footnote' => '2', 'slices' => []],
        ], $geo['funds']);
        $this->assertSame([['marker' => '1', 'text' => 'Gross exposure'], ['marker' => '2', 'text' => 'Equity only exposure']], $geo['footnotes']);
    }

    public function test_geographic_slices_are_rebuilt_only_for_funds_with_current_values(): void
    {
        $fund = $this->overviewFund(['chart_data' => ['geographicExposure' => $this->seededPies()]]);

        (new GlobalOverviewImporter)->import($fund, $this->makeXlsx([
            ['Code', 'Value'],
            ['875_GEO_EXP_TOTAL_RANK_1', 'North America'],
            ['875_GEO_EXP_TOTAL_RANK_1_CURRENT', '42.35'],
            ['875_GEO_EXP_TOTAL_RANK_2', 'Europe'],
            ['875_GEO_EXP_TOTAL_RANK_2_CURRENT', '30'],
            ['875_GEO_EXP_TOTAL_RANK_3', 'EM Asia'],
            ['875_GEO_EXP_TOTAL_RANK_3_CURRENT', '-'],
            ['875_GEO_EXP_TOTAL_RANK_4', 'Pacific'],
            ['875_GEO_EXP_TOTAL_RANK_5', ''],
            ['875_GEO_EXP_TOTAL_RANK_5_CURRENT', '9'],
            ['875_GEO_EXP_TOTAL_RANK_6', 'Africa + Middle East'],
            ['877_GEO_EXP_TOTAL_RANK_1', 'Europe'],
            ['877_GEO_EXP_TOTAL_RANK_2', 'North America'],
            ['879_COUNTRY_EXP_RANK_1', 'China'],
            ['879_COUNTRY_EXP_RANK_1_CURRENT', '48.1'],
            ['879_COUNTRY_EXP_RANK_2', 'Korea'],
            ['879_COUNTRY_EXP_RANK_2_CURRENT', '12.9'],
        ], 'glb-geo-values'));

        $pies = $this->pies($fund);
        // 875 has values: rebuilt from the feed, "-"/blank → 0, list ends at the blank rank.
        $this->assertEquals([
            ['name' => 'North America', 'value' => 42.4],
            ['name' => 'Europe', 'value' => 30.0],
            ['name' => 'EM Asia', 'value' => 0.0],
            ['name' => 'Pacific', 'value' => 0.0],
        ], $pies['875']['slices']);
        // 877 has names only: its seeded slices are untouched.
        $this->assertSame([['name' => 'Europe', 'value' => 33]], $pies['877']['slices']);
        // 879 has values too.
        $this->assertEquals([['name' => 'China', 'value' => 48.1], ['name' => 'Korea', 'value' => 12.9]], $pies['879']['slices']);
        // Prose still merges from the stored block.
        $this->assertSame('INTERNATIONAL (edited)', $pies['875']['label']);
    }

    // ── Sector exposure ──────────────────────────────────────────────────

    public function test_sector_list_is_kept_when_the_feed_carries_item_names_only(): void
    {
        $seeded = [
            ['name' => 'Communication services', 'fund' => 22, 'benchmark' => 9],
            ['name' => 'Real estate', 'fund' => 1.5, 'benchmark' => 2],
        ];
        $fund = $this->overviewFund(['sector_allocation' => ['title' => 'SECTORS (edited)', 'sectors' => $seeded]]);

        (new GlobalOverviewImporter)->import($fund, $this->makeXlsx([
            ['Code', 'Value'],
            ['877_ESAOT_RANK_1_ITEM', 'Communication services'],
            ['877_ESAOT_RANK_2_ITEM', 'Consumer discretionary'],
            ['877_ESAOT_RANK_3_ITEM', 'Property'],
        ], 'glb-sectors-names'));

        $this->assertSame('SECTORS (edited)', $fund->sector_allocation['title']);
        $this->assertSame($seeded, $fund->sector_allocation['sectors']);
    }

    public function test_bare_fund_gets_the_sector_title_and_an_empty_list(): void
    {
        $fund = $this->overviewFund(['sector_allocation' => null]);

        (new GlobalOverviewImporter)->import($fund, $this->makeXlsx([
            ['Code', 'Value'],
            ['877_ESAOT_RANK_1_ITEM', 'Communication services'],
        ], 'glb-sectors-bare'));

        $this->assertSame('SECTOR EXPOSURE (FOORD GLOBAL EQUITY FUND)', $fund->sector_allocation['title']);
        $this->assertSame([], $fund->sector_allocation['sectors']);
    }

    public function test_sector_list_is_rebuilt_when_current_values_arrive_and_property_becomes_real_estate(): void
    {
        $fund = $this->overviewFund(['sector_allocation' => ['sectors' => [
            ['name' => 'Communication services', 'fund' => 22, 'benchmark' => 9],
            ['name' => 'Real estate', 'fund' => 1.5, 'benchmark' => 2],
            ['name' => 'Gone', 'fund' => 5, 'benchmark' => 5],
        ]]]);

        (new GlobalOverviewImporter)->import($fund, $this->makeXlsx([
            ['Code', 'Value'],
            ['877_ESAOT_RANK_1_ITEM', 'Communication services'],
            ['877_ESAOT_RANK_1_CURRENT', '24'],
            ['877_ESAOT_RANK_1_BENCHMARK', '8'],
            ['877_ESAOT_RANK_2_ITEM', 'Consumer discretionary'],
            ['877_ESAOT_RANK_2_CURRENT', '15.5'],
            ['877_ESAOT_RANK_3_ITEM', 'Consumer/staples'],
            ['877_ESAOT_RANK_3_CURRENT', '-'],
            ['877_ESAOT_RANK_3_BENCHMARK', '6'],
            ['877_ESAOT_RANK_4_ITEM', 'PROPERTY'],
            ['877_ESAOT_RANK_4_CURRENT', '2'],
            ['877_ESAOT_RANK_5_ITEM', ''],
            ['877_ESAOT_RANK_5_CURRENT', '-'],
        ], 'glb-sectors-values'));

        $this->assertSame('SECTOR EXPOSURE (FOORD GLOBAL EQUITY FUND)', $fund->sector_allocation['title']);
        $this->assertSame([
            ['name' => 'Communication services', 'fund' => 24, 'benchmark' => 8],
            // No BENCHMARK cell and nothing stored under that name → 0.
            ['name' => 'Consumer discretionary', 'fund' => 15.5, 'benchmark' => 0],
            ['name' => 'Consumer / staples', 'fund' => 0, 'benchmark' => 6],
            // Property is renamed, so the stored Real estate benchmark is found.
            ['name' => 'Real estate', 'fund' => 2, 'benchmark' => 2],
        ], $fund->sector_allocation['sectors']);
    }

    // ── Synopsis / strategy / quarter ────────────────────────────────────

    public function test_maps_world_and_asia_bullets_with_the_quarter(): void
    {
        $fund = $this->overviewFund();

        $rows = [['Code', 'Value'], ['MONTH_END_DATE', '31 August 2026']];
        foreach (['WORLD_SYN', 'ASIA_SYN', 'WORLD_STRAT', 'ASIA_STRAT'] as $prefix) {
            for ($i = 1; $i <= 5; $i++) {
                $rows[] = ["{$prefix}_{$i}", "{$prefix} bullet {$i}"];
            }
        }

        (new GlobalOverviewImporter)->import($fund, $this->makeXlsx($rows, 'glb-bullets'));

        $synopsis = $fund->page2_content['synopsis'];
        $strategy = $fund->page2_content['strategy'];
        $this->assertSame('MARKET SYNOPSIS', $synopsis['title']);
        $this->assertSame('Q2 2026', $synopsis['quarter']);
        $this->assertSame('STRATEGY', $strategy['title']);
        $this->assertSame('Q2 2026', $strategy['quarter']);
        $this->assertSame(['title', 'quarter', 'world', 'asia'], array_keys($synopsis));
        $this->assertArrayNotHasKey('southAfrica', $strategy);
        $this->assertCount(5, $synopsis['world']);
        $this->assertCount(5, $synopsis['asia']);
        $this->assertCount(5, $strategy['world']);
        $this->assertCount(5, $strategy['asia']);
        $this->assertSame('WORLD_SYN bullet 1', $synopsis['world'][0]);
        $this->assertSame('ASIA_SYN bullet 5', $synopsis['asia'][4]);
        $this->assertSame('WORLD_STRAT bullet 3', $strategy['world'][2]);
        $this->assertSame('ASIA_STRAT bullet 1', $strategy['asia'][0]);

        $this->assertSame('Note: Totals may not cast perfectly due to rounding', $fund->page2_content['roundingNote']);
        $this->assertSame([
            'heading' => 'SCAN QR CODE',
            'text' => 'to read regulatory disclosures or visit:',
            'url' => 'https://foord.com/terms-conditions',
            'image' => 'images/qr-terms-global.png',
        ], $fund->page2_content['qr']);
    }

    public function test_bullets_keep_stored_prose_and_other_page2_keys(): void
    {
        $fund = $this->overviewFund([
            'page2_content' => [
                'synopsis' => ['title' => 'MARKET VIEW', 'quarter' => 'Q1 2026', 'world' => ['old'], 'asia' => ['old asia']],
                'roundingNote' => 'Note: edited',
                'qr' => ['heading' => 'SCAN', 'text' => 'edited', 'url' => 'https://example.test', 'image' => 'images/x.png'],
                'footer' => ['keep' => true],
            ],
        ]);

        // The July export carries no bullets at all.
        (new GlobalOverviewImporter)->import($fund, $this->makeXlsx([
            ['Code', 'Value'],
            ['WORLD_SYN_1', 'one'],
            ['WORLD_SYN_2', ''],
            ['WORLD_SYN_3', 'three'],
        ], 'glb-bullets-partial'));

        $synopsis = $fund->page2_content['synopsis'];
        $this->assertSame('MARKET VIEW', $synopsis['title']);
        // No MONTH_END_DATE in this export: the stored quarter is kept.
        $this->assertSame('Q1 2026', $synopsis['quarter']);
        $this->assertSame(['one'], $synopsis['world']);
        $this->assertSame(['old asia'], $synopsis['asia']);
        $this->assertSame([], $fund->page2_content['strategy']['asia']);
        $this->assertSame('Note: edited', $fund->page2_content['roundingNote']);
        $this->assertSame('edited', $fund->page2_content['qr']['text']);
        $this->assertSame(['keep' => true], $fund->page2_content['footer']);
    }

    public function test_quarter_label_is_inherited_from_the_shared_base(): void
    {
        $this->assertSame('Q2 2026', GlobalOverviewImporter::quarterLabelFor('31 August 2026'));
        $this->assertSame('Q4 2026', GlobalOverviewImporter::quarterLabelFor('31 January 2027'));
        $this->assertNull(GlobalOverviewImporter::quarterLabelFor(''));
    }

    // ── Real export ──────────────────────────────────────────────────────

    public function test_imports_the_real_july_export(): void
    {
        $path = base_path('storage/app/private/fund-data/2026-07/GLB/GLOBAL_OVERVIEW.xlsx');
        if (! is_file($path)) {
            $this->markTestSkipped('Real July GLOBAL_OVERVIEW export not present.');
        }

        $fund = $this->overviewFund([
            'chart_data' => ['geographicExposure' => $this->seededPies()],
            'sector_allocation' => ['sectors' => [['name' => 'Real estate', 'fund' => 1, 'benchmark' => 2]]],
        ]);
        (new GlobalOverviewImporter)->import($fund, $path);

        $this->assertSame('31 July 2026', $fund->fund_date);

        $funds = $this->perfFunds($fund);
        $this->assertSame(5.7, $funds['875']['fund']['20yrs']);
        $this->assertEquals(5.0, $funds['875']['peer']['20yrs']);
        $this->assertSame(2.5, $funds['875']['benchmark']['20yrs']);
        $this->assertSame(14.9, $funds['875']['fund']['1yr']);
        $this->assertArrayNotHasKey('20yrs', $funds['877']['fund']);
        $this->assertSame(7.9, $funds['877']['fund']['10yrs']);
        $this->assertSame(36.3, $funds['879']['peer']['1yr']);
        $this->assertSame(37.5, $funds['879']['benchmark']['1yr']);

        $rows = $this->aaRows($fund);
        $this->assertEquals(94.0, $rows['netEq']['values']['877']);
        // July has no HEQ rows at all; a fund with AA keys still prints 0.0.
        $this->assertEquals(0.0, $rows['hedgedEq']['values']['877']);
        $this->assertEquals(100.0, $rows['total']['values']['877']);
        $this->assertSame(89.8, $rows['netEq']['values']['879']);

        // Names only in the feed: every seeded pie survives.
        $pies = $this->pies($fund);
        $this->assertSame([['name' => 'China', 'value' => 50]], $pies['879']['slices']);
        $this->assertSame([['name' => 'North America', 'value' => 40.5]], $pies['875']['slices']);
        $this->assertSame([['name' => 'Real estate', 'fund' => 1, 'benchmark' => 2]], $fund->sector_allocation['sectors']);

        // July carries no bullets; the block still gets its skeleton.
        $this->assertSame('Q2 2026', $fund->page2_content['synopsis']['quarter']);
        $this->assertSame([], $fund->page2_content['synopsis']['asia']);
    }

    public function test_imports_the_real_august_export(): void
    {
        $path = base_path('storage/app/private/fund-data/2026-08/GLB/GLOBAL_OVERVIEW.xlsx');
        if (! is_file($path)) {
            $this->markTestSkipped('Real August GLOBAL_OVERVIEW export not present.');
        }

        $fund = $this->overviewFund();
        (new GlobalOverviewImporter)->import($fund, $path);

        $this->assertSame('31 August 2026', $fund->fund_date);
        $funds = $this->perfFunds($fund);
        $this->assertEquals(6.0, $funds['875']['fund']['20yrs']);
        $this->assertSame(5.2, $funds['875']['peer']['20yrs']);
        $this->assertSame(2.5, $funds['875']['benchmark']['20yrs']);
        $this->assertSame(94.6, $this->aaRows($fund)['netEq']['values']['877']);
        $this->assertEquals(0.0, $this->aaRows($fund)['hedgedEq']['values']['875']);
        $this->assertSame('Q2 2026', $fund->page2_content['synopsis']['quarter']);
        $this->assertCount(5, $fund->page2_content['synopsis']['asia']);
        $this->assertCount(5, $fund->page2_content['strategy']['world']);
        $this->assertSame('Remain cautious on resources sector', $fund->page2_content['strategy']['asia'][0]);
    }
}
