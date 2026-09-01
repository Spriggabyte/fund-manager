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

    public function test_internal_pdf_view_uses_shariah_income_pdf_template(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-shariah-income']);

        $view = (new FundController)->internalPdfView($fund);

        $this->assertSame('funds.pdf-shariah-income', $view->name());
    }

    public function test_show_renders_shariah_income_template(): void
    {
        $user = User::factory()->create();
        $fund = Fund::factory()->for($user)->create(['template' => 'show-shariah-income']);

        $this->actingAs($user)->get(route('funds.show', $fund))
            ->assertOk()
            ->assertViewIs('funds.show-shariah-income');
    }

    public function test_internal_pdf_view_uses_inflation_income_pdf_template(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-inflation-income']);

        $view = (new FundController)->internalPdfView($fund);

        $this->assertSame('funds.pdf-inflation-income', $view->name());
    }

    public function test_show_renders_inflation_income_template(): void
    {
        $user = User::factory()->create();
        $fund = Fund::factory()->for($user)->create(['template' => 'show-inflation-income']);

        $this->actingAs($user)->get(route('funds.show', $fund))
            ->assertOk()
            ->assertViewIs('funds.show-inflation-income');
    }

    public function test_internal_pdf_view_uses_global_equity_page_template(): void
    {
        // The global equity page template is itself the print layout, like
        // the other international templates.
        $fund = Fund::factory()->create(['template' => 'show-global-equity']);

        $view = (new FundController)->internalPdfView($fund);

        $this->assertSame('funds.show-global-equity', $view->name());
    }

    public function test_show_renders_global_equity_template(): void
    {
        $user = User::factory()->create();
        $fund = Fund::factory()->for($user)->create(['template' => 'show-global-equity']);

        $this->actingAs($user)->get(route('funds.show', $fund))
            ->assertOk()
            ->assertViewIs('funds.show-global-equity');
    }

    public function test_internal_pdf_view_uses_hassen_shariah_page_template(): void
    {
        // The 878 sheet is a Luxembourg-style template: the page IS the print
        // layout.
        $fund = Fund::factory()->create(['template' => 'show-hassen-shariah']);

        $view = (new FundController)->internalPdfView($fund);

        $this->assertSame('funds.show-hassen-shariah', $view->name());
    }

    public function test_show_renders_hassen_shariah_template(): void
    {
        $user = User::factory()->create();
        $fund = Fund::factory()->for($user)->create(['template' => 'show-hassen-shariah']);

        $this->actingAs($user)->get(route('funds.show', $fund))
            ->assertOk()
            ->assertViewIs('funds.show-hassen-shariah');
    }

    public function test_internal_pdf_view_uses_australian_feeder_page_template(): void
    {
        // The 880 sheet follows the Luxembourg pattern: the page IS the print
        // layout.
        $fund = Fund::factory()->create(['template' => 'show-australian-feeder']);

        $view = (new FundController)->internalPdfView($fund);

        $this->assertSame('funds.show-australian-feeder', $view->name());
    }

    public function test_show_renders_australian_feeder_template(): void
    {
        $user = User::factory()->create();
        $fund = Fund::factory()->for($user)->create(['template' => 'show-australian-feeder']);

        $this->actingAs($user)->get(route('funds.show', $fund))
            ->assertOk()
            ->assertViewIs('funds.show-australian-feeder');
    }

    public function test_internal_pdf_view_uses_prescient_global_equity_page_template(): void
    {
        // Like the other Prescient/international templates, the page IS the
        // print layout.
        $fund = Fund::factory()->create(['template' => 'show-prescient-global-equity']);

        $view = (new FundController)->internalPdfView($fund);

        $this->assertSame('funds.show-prescient-global-equity', $view->name());
    }

    /**
     * The trap from the 822 onboarding: leave a new template out of
     * ALLOWED_TEMPLATES and the PDF renders correctly while /funds/{id}
     * silently falls back to show.blade.php.
     */
    public function test_show_renders_prescient_global_equity_template(): void
    {
        $user = User::factory()->create();
        $fund = Fund::factory()->for($user)->create(['template' => 'show-prescient-global-equity']);

        $this->actingAs($user)->get(route('funds.show', $fund))
            ->assertOk()
            ->assertViewIs('funds.show-prescient-global-equity');
    }

    public function test_internal_pdf_view_uses_absolute_pdf_template(): void
    {
        $fund = Fund::factory()->create(['template' => 'show-absolute']);

        $view = (new FundController)->internalPdfView($fund);

        $this->assertSame('funds.pdf-absolute', $view->name());
    }

    public function test_show_renders_absolute_template(): void
    {
        $user = User::factory()->create();
        $fund = Fund::factory()->for($user)->create(['template' => 'show-absolute']);

        $this->actingAs($user)->get(route('funds.show', $fund))
            ->assertOk()
            ->assertViewIs('funds.show-absolute');
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

    public function test_internal_pdf_view_uses_international_trust_page_template(): void
    {
        // The trust page template is itself the print layout, like the
        // other international templates.
        $fund = Fund::factory()->create(['template' => 'show-international-trust']);

        $view = (new FundController)->internalPdfView($fund);

        $this->assertSame('funds.show-international-trust', $view->name());
    }

    public function test_show_renders_international_trust_template(): void
    {
        $user = User::factory()->create();
        $fund = Fund::factory()->for($user)->create(['template' => 'show-international-trust']);

        $this->actingAs($user)->get(route('funds.show', $fund))
            ->assertOk()
            ->assertViewIs('funds.show-international-trust');
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

    public function test_internal_pdf_view_uses_asia_ex_japan_page_template(): void
    {
        // The 879 sheet follows the Luxembourg pattern: the page IS the print
        // layout, so there is no separate pdf-* template.
        $fund = Fund::factory()->create(['template' => 'show-asia-ex-japan']);

        $view = (new FundController)->internalPdfView($fund);

        $this->assertSame('funds.show-asia-ex-japan', $view->name());
    }

    public function test_show_renders_asia_ex_japan_template(): void
    {
        $user = User::factory()->create();
        $fund = Fund::factory()->for($user)->create(['template' => 'show-asia-ex-japan']);

        $this->actingAs($user)->get(route('funds.show', $fund))
            ->assertOk()
            ->assertViewIs('funds.show-asia-ex-japan');
    }
}
