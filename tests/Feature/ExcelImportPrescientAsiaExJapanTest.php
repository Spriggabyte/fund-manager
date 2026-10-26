<?php

namespace Tests\Feature;

use App\Models\Fund;
use App\Services\FundImport\FactsheetImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

/**
 * Prescient Foord Asia ex-Japan Feeder Fund (829, fund 57) — a new fund.
 *
 * Its September 2026 export carries the master fund's (879) data and sends
 * ERR / ER5>> for everything a new fund cannot report yet; the seeded
 * "Not available—New Fund" rows and the estimated TIC must survive a
 * monthly import.
 */
class ExcelImportPrescientAsiaExJapanTest extends TestCase
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
     * The shape of the 829A Sept 2026 export (trimmed).
     *
     * @param  list<array{0: string, 1: string}>  $extra
     */
    private function factsheetRows(array $extra = []): array
    {
        return array_merge([
            ['Code', 'Value'],
            ['MONTH_END_DATE', '30 September 2026'],
            ['LAST_QUARTER_END', '30 June 2026'],
            ['PORTFOLIO_SIZE', 'ERR'],
            ['UNIT_PRICE', 'ERR'],
            ['NUMBER_OF_UNITS', 'ERR'],
            ['PUBLISHED_DATE', '02 September 2026'],
            ['LAST_DISTRIBUTION_DATE', 'ER3>>'],
            ['LAST_DISTRIBUTION_AMOUNT', 'ER3>> cents'],
            ['ESAOT_RANK_1_ITEM', 'Consumer discretionary'],
            ['ESAOT_RANK_1_CURRENT', '29'],
            ['ESAOT_RANK_1_CHANGE_SIGN', '+'],
            ['ESAOT_RANK_1_CHANGE', '2.8'],
            ['ESAOT_RANK_2_ITEM', 'Property'],
            ['ESAOT_RANK_2_CURRENT', '3'],
            ['ESAOT_RANK_2_CHANGE_SIGN', '+'],
            ['ESAOT_RANK_2_CHANGE', '0.4'],
            ['GEO_COUNTRY_RANK_1_ITEM', 'China'],
            ['GEO_COUNTRY_RANK_1_CURRENT', '46.6'],
            ['GEO_COUNTRY_RANK_7+_ITEM', 'Other'],
            ['GEO_COUNTRY_RANK_7+_CURRENT', '12.7'],
            ['AA_TOTAL_EQ', '88.5'],
            ['AA_TOTAL_CASH', '8.5'],
            ['AA_TOTAL_PROP', '2.9'],
            ['CONTRIBUTORS', 'TSMC, Alphabet, Shell, Quanta Services'],
            ['DETRACTORS', 'Wise, Alibaba, Tencent, Whitehaven Coal'],
            ['FOORD_CASH_VALUE', 'R 138,651'],
            ['FOORD_1Y_TO_D', '2.5'],
            ['SA_TER_TOTAL_EXPENSE_RATIO_12_MONTH', 'ER5>>'],
            ['SA_TER_TOTAL_EXPENSE_RATIO_36_MONTH', 'ER6>>'],
            ['SA_TER_MANAGERS_CHARGE_12_MONTH', 'ER5>>'],
            ['SA_TER_MANAGERS_CHARGE_36_MONTH', 'ER6>>'],
            ['SA_TER_TOTAL_INVESTMENT_CHARGE_12_MONTH', 'ER5>>'],
            ['SA_TER_TOTAL_INVESTMENT_CHARGE_36_MONTH', 'ER6>>'],
        ], $extra);
    }

    private function seededFund(array $attributes = []): Fund
    {
        return Fund::factory()->create(array_merge([
            'template' => 'show-prescient-asia-ex-japan',
            'fund_code' => '829',
            'class_code' => 'A',
            'portfolio_size' => 'Not available—New Fund',
            'unit_price' => 'Not available—New Fund',
            'number_of_units' => 'Not available—New Fund',
            'last_distributions' => 'The Foord Asia ex-Japan Fund, in which the fund invests, does not distribute its income.',
            'important_info_published_date' => 'Issue date 30 September 2026.',
            'isin_number' => 'ZAE000368882',
            'performance_table' => [
                'title' => 'PORTFOLIO PERFORMANCE',
                'notice' => 'Regulation prohibits performance reporting for periods less than one year.',
                'footnotes' => [
                    '* Figures are based on Master Fund at 31 August 2026. Feeder Fund data will be available in the next publication.',
                    'Note: Totals may not cast perfectly due to rounding',
                ],
            ],
            'fees' => [
                'totalInvestmentCharge' => [
                    'headers' => ['', '12 MONTHS*', '36 MONTHS*'],
                    'rows' => [['name' => 'Total expense ratio (TER)', '12m' => 1.46, '36m' => 1.40]],
                    'total' => ['name' => 'Total investment charge', '12m' => 1.46, '36m' => 1.40],
                ],
            ],
        ], $attributes));
    }

    private function import(Fund $fund, array $rows, string $name): Fund
    {
        (new FactsheetImporter)->import($fund, $this->makeXlsx($rows, $name));
        $fund->save();

        return $fund->fresh();
    }

    public function test_err_scalars_keep_the_seeded_new_fund_text(): void
    {
        $fund = $this->import($this->seededFund(), $this->factsheetRows(), 'axj_err_scalars');

        $this->assertSame('Not available—New Fund', $fund->portfolio_size);
        $this->assertSame('Not available—New Fund', $fund->unit_price);
        $this->assertSame('Not available—New Fund', $fund->number_of_units);
        // Static distributions prose; stale PUBLISHED_DATE skipped.
        $this->assertSame('The Foord Asia ex-Japan Fund, in which the fund invests, does not distribute its income.', $fund->last_distributions);
        $this->assertSame('Issue date 30 September 2026.', $fund->important_info_published_date);
    }

    public function test_error_marker_ter_cells_keep_the_estimated_tic(): void
    {
        $fund = $this->import($this->seededFund(), $this->factsheetRows(), 'axj_ter');

        $tic = $fund->fees['totalInvestmentCharge'];
        $this->assertSame(1.46, (float) $tic['rows'][0]['12m']);
        $this->assertSame(1.46, (float) $tic['total']['12m']);
        $this->assertSame(1.4, (float) $tic['total']['36m']);
    }

    /**
     * The 829 export ships 822 Class A's ISIN; ISINs are unique per class,
     * so one another fund holds is refused.
     */
    public function test_isin_already_held_by_another_fund_is_not_imported(): void
    {
        Fund::factory()->create(['isin_number' => 'ZAE000307708']);

        $fund = $this->import($this->seededFund(), $this->factsheetRows([['ISIN', 'ZAE000307708']]), 'axj_isin_dup');
        $this->assertSame('ZAE000368882', $fund->isin_number);

        $fund = $this->import($fund, $this->factsheetRows([['ISIN', 'ZAE000999999']]), 'axj_isin_new');
        $this->assertSame('ZAE000999999', $fund->isin_number);
    }

    public function test_performance_rows_are_not_imported_and_the_master_fund_note_follows_the_month_end(): void
    {
        $fund = $this->import($this->seededFund(), $this->factsheetRows(), 'axj_perf');

        $table = $fund->performance_table;
        $this->assertArrayNotHasKey('rows', $table);
        $this->assertSame('Regulation prohibits performance reporting for periods less than one year.', $table['notice']);
        $this->assertSame(
            '* Figures are based on Master Fund at 30 September 2026. Feeder Fund data will be available in the next publication.',
            $table['footnotes'][0]
        );
    }

    public function test_allocation_contributors_sectors_and_countries_come_from_the_feed(): void
    {
        $fund = $this->import($this->seededFund([
            'page2_content' => ['assetAllocation' => ['title' => 'ASSET ALLOCATION %*']],
        ]), $this->factsheetRows(), 'axj_blocks');

        $this->assertSame('ASSET ALLOCATION %*', $fund->page2_content['assetAllocation']['title']);
        $this->assertSame(['88.5', '8.5', '2.9'], array_column($fund->page2_content['assetAllocation']['rows'], 'value'));
        $this->assertSame('TSMC, Alphabet, Shell, Quanta Services', $fund->page2_content['contributorsDetractors']['rows'][0]['value']);
        // The 829 sheet prints "Property", unlike the 823/877 structure lists.
        $this->assertSame(['Consumer discretionary', 'Property'], array_column($fund->sector_allocation['sectors'], 'name'));
        $this->assertSame(['China', 'Other'], array_column($fund->asset_allocation['geographicCountryExposure'], 'name'));
    }

    /**
     * The guards are about unusable cells, not the template: a usable size
     * still imports with the rand prefix on any sheet.
     */
    public function test_usable_portfolio_size_still_imports(): void
    {
        $fund = $this->import(
            $this->seededFund(),
            $this->factsheetRows([['PORTFOLIO_SIZE', '12.5 million'], ['UNIT_PRICE', '100.00 cents']]),
            'axj_usable'
        );

        $this->assertSame('R12.5 million', $fund->portfolio_size);
        $this->assertSame('100.00 cents', $fund->unit_price);
    }

    public function test_sidebar_carries_the_prescient_rows_and_investment_policy(): void
    {
        $fund = $this->seededFund(['master_fund' => 'The portfolio will, apart from assets in liquid form, …']);

        $sidebar = $fund->data['sidebar'];
        $this->assertTrue($sidebar['mddHeading']);
        $this->assertSame('A', $sidebar['shareClass']);
        $this->assertSame('The portfolio will, apart from assets in liquid form, …', $sidebar['masterFund']);
    }
}
