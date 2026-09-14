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
 * Foord Global Equity Feeder Fund (821, funds 55/56).
 *
 * The sheet borrows the 809 feeder's chrome and the 877 master fund's page-1
 * content, so most importer behaviour is shared. Covered here are the four
 * feed quirks that are gated on the template.
 */
class ExcelImportGlobalEquityFeederTest extends TestCase
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

    private function factsheetRows(array $extra = []): array
    {
        return array_merge([
            ['Code', 'Value'],
            ['MONTH_END_DATE', '31 July 2026'],
            ['LAST_QUARTER_END', '30 June 2026'],
            ['PUBLISHED_DATE', '04 August 2026'],
            ['ESAOT_RANK_1_ITEM', 'Communication services'],
            ['ESAOT_RANK_1_CURRENT', '19'],
            ['ESAOT_RANK_1_CHANGE_SIGN', '-'],
            ['ESAOT_RANK_1_CHANGE', '0.1'],
            ['ESAOT_RANK_1_VAR_TO_BM', '+ 11.5'],
            ['ESAOT_RANK_2_ITEM', 'Property'],
            ['ESAOT_RANK_2_CURRENT', '1'],
            ['ESAOT_RANK_2_CHANGE_SIGN', '+'],
            ['ESAOT_RANK_2_CHANGE', '0.1'],
            ['ESAOT_RANK_2_VAR_TO_BM', '- 1.0'],
            ['TOPX_SECURITY_1', 'Tencent Holdings'],
            ['TOPX_SECURITY_SECTOR_1', 'Communication services'],
            ['TOPX_MARKET_1', 'HKG'],
            ['TOPX_PERCENT_OF_FUNDS_1', '6.6'],
        ], $extra);
    }

    /**
     * The published PORTFOLIO STRUCTURE list prints "Real estate" with the
     * feed's explicit change sign and a tight variance-to-benchmark value.
     */
    public function test_portfolio_structure_rows_follow_the_master_fund_conventions(): void
    {
        $fund = Fund::factory()->create(['template' => Fund::GLOBAL_EQUITY_FEEDER_TEMPLATE]);

        (new FactsheetImporter)->import($fund, $this->makeXlsx($this->factsheetRows(), 'gef_factsheet_sectors'));
        $fund->save();

        $sectors = $fund->fresh()->sector_allocation['sectors'];
        $this->assertSame('Communication services', $sectors[0]['name']);
        $this->assertSame('down', $sectors[0]['direction']);
        $this->assertSame('+11.5', $sectors[0]['variance']);
        $this->assertSame('Real estate', $sectors[1]['name']);
        $this->assertSame('up', $sectors[1]['direction']);
        $this->assertSame('-1.0', $sectors[1]['variance']);
        $this->assertSame('Change since 30 June 2026', $fund->fresh()->sector_allocation['subtitle']);
    }

    /**
     * The 821 export emits zero-value distribution rows although the sheet
     * prints static prose in its DISTRIBUTIONS row — the seeded sentence must
     * survive a monthly import, as it does on the Prescient feeders.
     */
    public function test_zero_value_distributions_do_not_clobber_the_seeded_sentence(): void
    {
        $sentence = 'The Foord Global Equity Fund, in which the fund invests, does not distribute its income.';
        $fund = Fund::factory()->create([
            'template' => Fund::GLOBAL_EQUITY_FEEDER_TEMPLATE,
            'last_distributions' => $sentence,
        ]);

        (new FactsheetImporter)->import($fund, $this->makeXlsx($this->factsheetRows([
            ['LAST_DISTRIBUTION_DATE', '31/03/2026'],
            ['LAST_DISTRIBUTION_AMOUNT', '0.00 cents'],
            ['SECOND_LAST_DISTRIBUTION_DATE', '30/09/2025'],
            ['SECOND_LAST_DISTRIBUTION_AMOUNT', '0.00 cents'],
        ]), 'gef_factsheet_distributions'));
        $fund->save();

        $this->assertSame($sentence, $fund->fresh()->last_distributions);
        $this->assertSame('Published on 04 August 2026.', $fund->fresh()->important_info_published_date);
    }

    /**
     * The seeded SECTOR header must survive the import (the importer's own
     * default is ASSET CLASS).
     */
    public function test_top_ten_keeps_the_seeded_sector_header(): void
    {
        $fund = Fund::factory()->create([
            'template' => Fund::GLOBAL_EQUITY_FEEDER_TEMPLATE,
            'top_investments' => ['title' => 'TOP 10 INVESTMENTS', 'headers' => ['SECURITY', 'SECTOR', 'MARKET', '% OF FUND']],
        ]);

        (new FactsheetImporter)->import($fund, $this->makeXlsx($this->factsheetRows(), 'gef_factsheet_top10'));
        $fund->save();

        $top = $fund->fresh()->top_investments;
        $this->assertSame(['SECURITY', 'SECTOR', 'MARKET', '% OF FUND'], $top['headers']);
        $this->assertSame('Communication services', $top['rows'][0]['assetClass']);
    }

    /**
     * The 821 price graph leads with the MSCI ACWI benchmark and carries the
     * Morningstar peer group as "Fund Misc (1st) [MRN …]" — three series,
     * re-emitted as fund / benchmark / peerGroup for the two-line chart.
     */
    public function test_price_graph_yields_fund_benchmark_and_peer_series(): void
    {
        $fund = Fund::factory()->create(['template' => Fund::GLOBAL_EQUITY_FEEDER_TEMPLATE]);

        $path = $this->makeXlsx([
            ['Start Date', 'Description', '821 A Class [iR]', '821 Fund Benchmark [MSCI AC ZAR3PM]', '821 Fund Misc (1st) [MRN GLB LCAP CCY ZAR]'],
            // The live export stores the indexed values as numbers with a
            // percentage cell format (100 displays as "10000.00%").
            ['01/05/2014', 'Apr 2014', 100, 100, 100],
            ['01/05/2014', 'May 2014', 100.958, 102.3709, 102.2833],
            ['01/05/2014', 'Jul 2026', 299.09, 528.3078, 387.567],
        ], 'gef_price_graph');
        (new PriceGraphImporter)->import($fund, $path);
        $fund->save();

        $performance = $fund->fresh()->chart_data['performanceData'];
        $this->assertCount(3, $performance);
        $this->assertSame('2014-04', $performance[0]['date']);
        $this->assertEquals(100, $performance[0]['fund']);
        $this->assertEquals(299.09, $performance[2]['fund']);
        $this->assertEquals(528.31, $performance[2]['benchmark']);
        $this->assertEquals(387.57, $performance[2]['peerGroup']);
    }
}
