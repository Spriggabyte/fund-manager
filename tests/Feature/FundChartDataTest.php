<?php

namespace Tests\Feature;

use App\Models\Fund;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FundChartDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_chart_data_payload_is_well_formed(): void
    {
        $fund = Fund::factory()->representative()->create();

        $charts = $fund->chart_data;

        $this->assertIsArray($charts);
        $this->assertArrayHasKey('inflationData', $charts);
        $this->assertArrayHasKey('portfolioData', $charts);
        $this->assertNotEmpty($charts['inflationData']);
        $this->assertNotEmpty($charts['portfolioData']);

        foreach ($charts['inflationData'] as $row) {
            foreach (['date', 'inflation', 'hurdle', 'excess', 'composite'] as $key) {
                $this->assertArrayHasKey($key, $row);
            }
        }

        foreach ($charts['portfolioData'] as $row) {
            foreach (['date', 'fund', 'benchmark'] as $key) {
                $this->assertArrayHasKey($key, $row);
            }
        }
    }
}
