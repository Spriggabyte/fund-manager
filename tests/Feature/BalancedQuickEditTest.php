<?php

namespace Tests\Feature;

use App\Models\Fund;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Every table on the balanced fact sheet (template `show`, funds 9/10/16)
 * is quick-editable: titles, headers, row names, values and totals.
 */
class BalancedQuickEditTest extends TestCase
{
    use RefreshDatabase;

    private function balancedFund(User $user): Fund
    {
        return Fund::factory()->withData([
            'fund' => ['name' => 'FOORD BALANCED FUND — CLASS A', 'date' => '31 August 2026', 'template' => 'show'],
            'mainContent' => [
                'assetAllocation' => [
                    'title' => 'ASSET ALLOCATION % (MAX LIMITS IN BRACKETS)',
                    'subtitle' => 'Change since 30 June 2026',
                    'headers' => ['', 'SA (100)', 'FOREIGN (45)', 'TOTAL', 'CHANGE'],
                    'rows' => [
                        ['name' => 'Equities', 'limit' => '75', 'sa' => 40.2, 'foreign' => 28.1, 'total' => 68.3, 'change' => '▲ 0.9', 'changeDirection' => 'up'],
                        ['name' => 'Money market', 'limit' => '100', 'sa' => 9.5, 'foreign' => 5.2, 'total' => 14.8, 'change' => '▼ 2.1', 'changeDirection' => 'down'],
                    ],
                    'total' => ['name' => 'TOTAL', 'sa' => 58.8, 'foreign' => 41.2, 'total' => 100, 'change' => ''],
                ],
                'topInvestments' => [
                    'title' => 'TOP 10 INVESTMENTS',
                    'headers' => ['SECURITY', 'ASSET CLASS', 'MARKET', '% OF FUND'],
                    'rows' => [
                        ['security' => 'Foord International Fund', 'assetClass' => 'Foreign assets', 'market' => 'LUX', 'percentage' => 19.2],
                    ],
                ],
                'performanceTable' => [
                    'title' => 'PORTFOLIO PERFORMANCE % (PERIODS GREATER THAN ONE YEAR ARE ANNUALISED¹)',
                    'headers' => ['', 'CASH<br>VALUE²', 'SINCE<br>INCEPTION', '1<br>YR', 'THIS<br>MONTH'],
                    'rows' => [
                        ['name' => 'Fund', 'cashValue' => 'R 1,693,180', 'sinceInception' => 12.5, '1yr' => 8.7, 'thisMonth' => 1.1],
                        ['name' => 'Fund highest', 'sinceInception' => 61, '1yr' => 8.7],
                    ],
                ],
            ],
            'fees' => [
                'feeRates' => [
                    'title' => 'FEE RATES',
                    'rates' => [['name' => 'Initial, exit and switching fees', 'value' => '0.0%']],
                    'globalFunds' => [
                        'title' => 'Foord global funds:',
                        'funds' => [['name' => '- Foord International Fund', 'value' => '1.00%']],
                    ],
                ],
                'performanceFeeExamples' => [
                    'title' => 'PERFORMANCE FEE EXAMPLES %',
                    'headers' => ['', 'A', 'B', 'C', 'D'],
                    'rows' => [
                        ['name' => 'Relative performance', 'a' => '+2.0', 'b' => -2, 'c' => 0, 'd' => -6],
                    ],
                    'total' => ['name' => 'Annual fee rate applied (excl. VAT)', 'a' => 1.2, 'b' => 0.8, 'c' => 1, 'd' => '0.5*'],
                ],
            ],
        ])->create(['user_id' => $user->id]);
    }

    public function test_every_table_cell_on_the_balanced_sheet_is_editable(): void
    {
        $user = User::factory()->create();
        $fund = $this->balancedFund($user);

        $response = $this->actingAs($user)->get(route('funds.show', $fund));

        $response->assertOk();
        foreach ([
            // Asset allocation
            'mainContent.assetAllocation.title',
            'mainContent.assetAllocation.headers.1',
            'mainContent.assetAllocation.rows.0.sa',
            'mainContent.assetAllocation.rows.0.foreign',
            'mainContent.assetAllocation.rows.0.total',
            'mainContent.assetAllocation.rows.1.change',
            'mainContent.assetAllocation.total.name',
            'mainContent.assetAllocation.total.total',
            // Top 10
            'mainContent.topInvestments.title',
            'mainContent.topInvestments.headers.3',
            'mainContent.topInvestments.rows.0.assetClass',
            'mainContent.topInvestments.rows.0.market',
            'mainContent.topInvestments.rows.0.percentage',
            // Performance
            'mainContent.performanceTable.title',
            'mainContent.performanceTable.headers.1',
            'mainContent.performanceTable.rows.0.name',
            'mainContent.performanceTable.rows.0.cashValue',
            'mainContent.performanceTable.rows.0.sinceInception',
            'mainContent.performanceTable.rows.1.thisMonth',
            // Fee rates
            'fees.feeRates.title',
            'fees.feeRates.rates.0.name',
            'fees.feeRates.rates.0.value',
            'fees.feeRates.globalFunds.title',
            'fees.feeRates.globalFunds.funds.0.name',
            'fees.feeRates.globalFunds.funds.0.value',
            // Performance fee examples
            'fees.performanceFeeExamples.title',
            'fees.performanceFeeExamples.headers.1',
            'fees.performanceFeeExamples.rows.0.name',
            'fees.performanceFeeExamples.rows.0.a',
            'fees.performanceFeeExamples.total.name',
            'fees.performanceFeeExamples.total.d',
        ] as $path) {
            $response->assertSee("editableField('{$path}'", false);
        }
    }

    public function test_relabelled_headers_keep_their_columns_data(): void
    {
        $user = User::factory()->create();
        $fund = $this->balancedFund($user);
        $fees = $fund->fees;
        $fees['performanceFeeExamples']['headers'] = ['', 'W', 'X', 'Y', 'Z'];
        $aa = $fund->asset_allocation;
        $aa['headers'][1] = 'LOCAL (100)';
        $fund->update(['fees' => $fees, 'asset_allocation' => $aa]);

        $response = $this->actingAs($user)->get(route('funds.show', $fund));

        $response->assertSee("editableField('fees.performanceFeeExamples.rows.0.d', '-6.0', 'oneDecimal')", false);
        $response->assertSee("editableField('mainContent.assetAllocation.rows.0.sa', '40.2', 'oneDecimal')", false);
    }

    public function test_update_data_keeps_an_explicit_plus_sign(): void
    {
        $user = User::factory()->create();
        $fund = $this->balancedFund($user);

        $this->actingAs($user)->patchJson(route('funds.update-data', $fund), [
            'field' => 'fees.performanceFeeExamples.rows.0.a',
            'value' => '+3.0',
        ])->assertOk();
        $this->actingAs($user)->patchJson(route('funds.update-data', $fund), [
            'field' => 'fees.performanceFeeExamples.rows.0.b',
            'value' => '-3.0',
        ])->assertOk();

        $row = $fund->fresh()->fees['performanceFeeExamples']['rows'][0];
        $this->assertSame('+3.0', $row['a']);
        // Unsigned / negative figures are still stored as numbers.
        $this->assertEquals(-3, $row['b']);
        $this->assertIsNotString($row['b']);
    }
}
