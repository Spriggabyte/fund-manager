<?php

namespace Tests\Feature;

use App\Http\Controllers\FundController;
use App\Models\Fund;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FundTemplateSelectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_internal_pdf_view_uses_pdf_template_by_default(): void
    {
        $fund = Fund::factory()->create(['template' => 'show']);

        $view = (new FundController)->internalPdfView($fund);

        $this->assertSame('funds.pdf', $view->name());
    }

    public function test_internal_pdf_view_uses_equity_pdf_template(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-equity']);

        $view = (new FundController)->internalPdfView($fund);

        $this->assertSame('funds.pdf-equity', $view->name());
    }

    public function test_internal_pdf_view_falls_back_to_pdf_for_unknown_template(): void
    {
        $fund = Fund::factory()->create(['template' => 'does-not-exist']);

        $view = (new FundController)->internalPdfView($fund);

        $this->assertSame('funds.pdf', $view->name());
    }

    public function test_show_falls_back_to_default_template_for_unknown_value(): void
    {
        $user = User::factory()->create();
        $fund = Fund::factory()->for($user)->create(['template' => 'bogus-template']);

        $this->actingAs($user)->get(route('funds.show', $fund))
            ->assertOk()
            ->assertViewIs('funds.show');
    }
}
