<?php

namespace Tests\Feature;

use App\Models\Fund;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class FundSectorAllocationTest extends TestCase
{
    use RefreshDatabase;

    private function equityFund(User $user): Fund
    {
        return Fund::factory()->create([
            'user_id' => $user->id,
            'name' => 'FOORD EQUITY FUND — CLASS A',
            'template' => 'show-equity',
            'sector_allocation' => [
                'title' => 'EQUITY SECTOR ALLOCATION %',
                'subtitle' => 'Change since 31 January 2026',
                'sectors' => [
                    ['name' => 'Consumer/services', 'value' => 26, 'change' => '3.3', 'direction' => 'down'],
                ],
            ],
            'chart_description' => 'The chart illustrates that the portfolio has outperformed the benchmark 71% of the time when the market was down.',
        ]);
    }

    public function test_data_attribute_exposes_sector_allocation_and_chart_description(): void
    {
        $fund = $this->equityFund(User::factory()->create());

        $data = $fund->data;

        $this->assertSame('EQUITY SECTOR ALLOCATION %', $data['mainContent']['sectorAllocation']['title']);
        $this->assertStringContainsString('71%', $data['mainContent']['chartDescription']);
    }

    public function test_data_attribute_leaves_new_keys_null_for_non_equity_funds(): void
    {
        $fund = Fund::factory()->representative()->create();

        $this->assertNull($fund->data['mainContent']['sectorAllocation']);
        $this->assertNull($fund->data['mainContent']['chartDescription']);
    }

    public function test_update_data_routes_sector_allocation_paths_to_new_column(): void
    {
        $user = User::factory()->create();
        $fund = $this->equityFund($user);

        $this->actingAs($user)->patch(route('funds.update-data', $fund), [
            'field' => 'mainContent.sectorAllocation.sectors.0.change',
            'value' => '4.1',
        ])->assertOk();

        // setNestedValue casts numeric strings to numbers; the template
        // renders 4.1 identically.
        $this->assertSame(4.1, $fund->fresh()->sector_allocation['sectors'][0]['change']);
    }

    public function test_update_data_routes_chart_description_to_new_column(): void
    {
        $user = User::factory()->create();
        $fund = $this->equityFund($user);

        $this->actingAs($user)->patch(route('funds.update-data', $fund), [
            'field' => 'mainContent.chartDescription',
            'value' => 'Updated narrative.',
        ])->assertOk();

        $this->assertSame('Updated narrative.', $fund->fresh()->chart_description);
    }

    public function test_restore_from_data_round_trips_new_fields(): void
    {
        $fund = $this->equityFund(User::factory()->create());
        $snapshot = $fund->data;

        $fund->sector_allocation = null;
        $fund->chart_description = null;
        $fund->restoreFromData($snapshot);

        $this->assertSame('EQUITY SECTOR ALLOCATION %', $fund->sector_allocation['title']);
        $this->assertStringContainsString('71%', $fund->chart_description);
    }

    public function test_equity_show_view_renders_with_import_shaped_performance_rows(): void
    {
        $user = User::factory()->create();
        $fund = $this->equityFund($user);
        // Imported Fund highest/lowest rows have no cashValue/thisMonth keys.
        $fund->update([
            'performance_table' => [
                'title' => 'PORTFOLIO PERFORMANCE %',
                'headers' => ['', 'CASH VALUE', 'SINCE INCEPTION', '15 YRS', '10 YRS', '7 YRS', '5 YRS', '3 YRS', '1 YR', 'THIS MONTH'],
                'rows' => [
                    ['name' => 'Fund', 'cashValue' => 'R 2,552,024', 'sinceInception' => 14.8, '1yr' => 36.1, 'thisMonth' => 6.1],
                    ['name' => 'Benchmark', 'cashValue' => 'R 2,827,945', 'sinceInception' => 15.3, '1yr' => 55.3, 'thisMonth' => 7.2],
                    ['name' => 'Fund highest', 'sinceInception' => 75.4, '1yr' => 36.1],
                    ['name' => 'Fund lowest', 'sinceInception' => -29.9, '1yr' => 36.1],
                ],
            ],
        ]);

        $this->actingAs($user)->get(route('funds.show', $fund))
            ->assertOk()
            ->assertSee('Fund highest');
    }

    public function test_equity_pdf_view_renders_sector_section_and_description(): void
    {
        $fund = $this->equityFund(User::factory()->create());

        $url = URL::temporarySignedRoute('funds.internal.pdf-view', now()->addMinutes(5), ['fund' => $fund->id]);

        $this->get($url)
            ->assertOk()
            ->assertSee('EQUITY SECTOR ALLOCATION %')
            ->assertSee('Change since 31 January 2026')
            ->assertSee('outperformed the benchmark 71%');
    }
}
