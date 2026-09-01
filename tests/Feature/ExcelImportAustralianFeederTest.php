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
 * Foord Global Equity Australian Feeder Fund (880, fund 50).
 *
 * Covers the feed behaviours that differ from the Luxembourg sheets the
 * template was cloned from: Australian-dollar scalars with the master fund's
 * size on a second line, the "0" ISIN placeholder, the GLOBAL_TER error
 * markers, the price graph whose peer column is named "Fund Benchmark (4th)",
 * and the table's two inception columns.
 */
class ExcelImportAustralianFeederTest extends TestCase
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

    public function test_scalars_are_reported_in_australian_dollars(): void
    {
        $fund = Fund::factory()->create([
            'template' => 'show-australian-feeder',
            'isin_number' => 'AU60ETL37743',
        ]);

        $path = $this->makeXlsx([
            ['Code', 'Value'],
            ['PORTFOLIO_SIZE', '11.6 million'],
            ['MASTER_FUND_PORTFOLIO_SIZE', '851.1 million'],
            ['UNIT_PRICE', '$22.35'],
            ['NUMBER_OF_UNITS', '519 004'],
            // The 880 export has no ISIN column of its own.
            ['ISIN', '0'],
        ], 'au_feeder_scalars');

        (new FactsheetImporter)->import($fund, $path);

        $this->assertSame('A$11.6 million<br>Master fund: $851.1 million', $fund->portfolio_size);
        $this->assertSame('A$22.35', $fund->unit_price);
        $this->assertSame('519 004', $fund->number_of_units);
        // The seeded registration code survives the feed's placeholder.
        $this->assertSame('AU60ETL37743', $fund->isin_number);
    }

    public function test_unusable_global_ter_cells_do_not_import_as_zero(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-australian-feeder']);

        $path = $this->makeXlsx([
            ['Code', 'Value'],
            ['GLOBAL_TER_BASIC_12_MONTH', 'ER9>>'],
            ['GLOBAL_TER_BASIC_36_MONTH', 'ER9>>'],
            ['GLOBAL_TER_TRANSACTION_COSTS_12_MONTH', 'ER9>>'],
            ['GLOBAL_TER_TOTAL_12_MONTH', 'ER9>>'],
        ], 'au_feeder_ter');

        (new FactsheetImporter)->import($fund, $path);

        $this->assertNull($fund->fees);
    }

    public function test_the_performance_table_carries_no_highest_or_lowest_rows(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-australian-feeder']);

        $path = $this->makeXlsx([
            ['Code', 'Value'],
            ['FOORD_CASH_VALUE', '$ 327,597'],
            ['FOORD_I_TO_D', '8.4'],
            ['FOORD_1Y_TO_D', '-3.3'],
            ['FOORD_HIGHEST_Y1', '10.5'],
            ['FOORD_LOWEST_Y1', '-3.3'],
            ['FOORD_COMP_1_CASH_VALUE', '$ 590,019'],
            ['FOORD_COMP_1_I_TO_D', '14.2'],
        ], 'au_feeder_perf');

        (new FactsheetImporter)->import($fund, $path);

        $names = array_column($fund->performance_table['rows'], 'name');
        $this->assertSame(['Fund', 'Benchmark'], $names);

        // The fund's own I_TO_D is measured from the class launch and belongs
        // in the SINCE 11 AUG 22 column; the benchmark's runs from the master
        // fund's launch and stays under SINCE INCEPTION.
        $fundRow = $fund->performance_table['rows'][0];
        $this->assertSame(8.4, $fundRow['sinceClassInception']);
        $this->assertArrayNotHasKey('sinceInception', $fundRow);
        $this->assertSame(14.2, $fund->performance_table['rows'][1]['sinceInception']);
    }

    /**
     * The 880 price graph names its peer column "Fund Benchmark (4th)" where
     * 877/879 use "(2nd)" and 878 uses "Fund Misc (3rd)".
     */
    public function test_the_fourth_benchmark_column_feeds_the_peer_group_series(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-australian-feeder']);

        $path = $this->makeXlsx([
            ['Start Date', 'Description', '880 Fund Price (1st) [AUSF AUD HRD]', '880 Fund Benchmark (2nd) [MSCI AC AUD]', '880 Fund Benchmark (4th) [AUSPG]'],
            [41366, 'Apr 2013', 102.21, 103.82, 103.04],
            [41366, 'May 2013', 112.06, 112.19, 112.35],
        ], 'au_feeder_price');

        (new PriceGraphImporter)->import($fund, $path);

        $this->assertSame(
            ['date' => '2013-05', 'fund' => 112.06, 'benchmark' => 112.19, 'peerGroup' => 112.35],
            $fund->chart_data['performanceData'][1]
        );
    }

    /**
     * The two inception columns the factsheet export cannot fill are
     * annualised off the indexed price series: the fund's return since the
     * master fund's launch, and the comparators' since the class's.
     */
    public function test_inception_columns_are_annualised_from_the_price_series(): void
    {
        $fund = Fund::factory()->create([
            'template' => 'show-australian-feeder',
            'inception_date' => '11 August 2022',
            'performance_table' => [
                'rows' => [
                    ['name' => 'Fund', 'sinceClassInception' => 8.4],
                    ['name' => 'Benchmark', 'sinceInception' => 14.2],
                ],
            ],
        ]);

        // Two years of monthly rows: the class base month (July) then twelve
        // more, so the fund doubles over 13 months from a base of 100.
        $rows = [['Start Date', 'Description', 'Fund Price (1st)', 'Fund Benchmark (2nd) [MSCI]', 'Fund Benchmark (4th)']];
        $rows[] = [41366, 'Jul 2022', 100.0, 200.0, 150.0];
        for ($i = 1; $i <= 12; $i++) {
            $rows[] = [41366, sprintf('%s 2023', ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'][$i - 1]), 100.0 + $i, 200.0 + $i, 150.0 + $i];
        }
        $rows[count($rows) - 1] = [41366, 'Dec 2023', 200.0, 400.0, 300.0];

        $path = $this->makeXlsx($rows, 'au_feeder_inception');

        (new PriceGraphImporter)->import($fund, $path);

        $storedRows = $fund->performance_table['rows'];

        // The fund: 13 rows means a 13-month span from the base of 100.
        $this->assertEquals(round((2 ** (12 / 13) - 1) * 100, 1), $storedRows[0]['sinceInception']);
        // Its own since-launch figure comes from the feed, untouched.
        $this->assertSame(8.4, $storedRows[0]['sinceClassInception']);

        // The benchmark: doubled over the twelve months after the July base.
        $this->assertEquals(100.0, $storedRows[1]['sinceClassInception']);
        $this->assertSame(14.2, $storedRows[1]['sinceInception']);
    }
}
