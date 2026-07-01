<?php

namespace Tests\Feature;

use App\Models\Fund;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class FundPdfViewTest extends TestCase
{
    use RefreshDatabase;

    private function signedPdfViewUrl(Fund $fund): string
    {
        return URL::temporarySignedRoute(
            'funds.internal.pdf-view',
            now()->addMinutes(5),
            ['fund' => $fund->id]
        );
    }

    public function test_internal_pdf_view_renders_fact_sheet_with_required_bindings(): void
    {
        $fund = Fund::factory()->representative()->create();

        $response = $this->get($this->signedPdfViewUrl($fund));

        $response->assertOk()
            ->assertSee('Foord Balanced Fund')          // fund name binding
            ->assertSee('12.3')                          // performance figure
            ->assertSee('id="inflationChart"', false)    // chart containers
            ->assertSee('id="portfolioChart"', false);
    }

    public function test_internal_pdf_view_embeds_wellformed_chart_payload(): void
    {
        $fund = Fund::factory()->representative()->create();

        $response = $this->get($this->signedPdfViewUrl($fund));

        // The @json chart payloads must reach the Highcharts init script.
        $response->assertSee('"composite":12.3', false)
            ->assertSee('"benchmark":108', false);
    }

    public function test_internal_pdf_view_rejects_unsigned_requests(): void
    {
        $fund = Fund::factory()->representative()->create();

        $this->get(route('funds.internal.pdf-view', $fund))
            ->assertForbidden();
    }
}
