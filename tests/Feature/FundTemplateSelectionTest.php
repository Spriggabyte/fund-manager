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

    public function test_internal_pdf_view_uses_conservative_pdf_template(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-conservative']);

        $view = (new FundController)->internalPdfView($fund);

        $this->assertSame('funds.pdf-conservative', $view->name());
    }

    public function test_internal_pdf_view_uses_bond_pdf_template(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-bond']);

        $view = (new FundController)->internalPdfView($fund);

        $this->assertSame('funds.pdf-bond', $view->name());
    }

    public function test_internal_pdf_view_uses_income_pdf_template(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-income']);

        $view = (new FundController)->internalPdfView($fund);

        $this->assertSame('funds.pdf-income', $view->name());
    }

    public function test_show_renders_income_template(): void
    {
        $user = User::factory()->create();
        $fund = Fund::factory()->for($user)->create(['template' => 'show-income']);

        $this->actingAs($user)->get(route('funds.show', $fund))
            ->assertOk()
            ->assertViewIs('funds.show-income');
    }

    public function test_internal_pdf_view_uses_domestic_pdf_template(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-domestic']);

        $view = (new FundController)->internalPdfView($fund);

        $this->assertSame('funds.pdf-domestic', $view->name());
    }

    public function test_show_renders_domestic_template(): void
    {
        $user = User::factory()->create();
        $fund = Fund::factory()->for($user)->create(['template' => 'show-domestic']);

        $this->actingAs($user)->get(route('funds.show', $fund))
            ->assertOk()
            ->assertViewIs('funds.show-domestic');
    }

    public function test_show_renders_bond_template(): void
    {
        $user = User::factory()->create();
        $fund = Fund::factory()->for($user)->create(['template' => 'show-bond']);

        $this->actingAs($user)->get(route('funds.show', $fund))
            ->assertOk()
            ->assertViewIs('funds.show-bond');
    }

    public function test_show_renders_conservative_template(): void
    {
        $user = User::factory()->create();
        $fund = Fund::factory()->for($user)->create(['template' => 'show-conservative']);

        $this->actingAs($user)->get(route('funds.show', $fund))
            ->assertOk()
            ->assertViewIs('funds.show-conservative');
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
