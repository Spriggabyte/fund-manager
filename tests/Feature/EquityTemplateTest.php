<?php

namespace Tests\Feature;

use App\Models\Fund;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EquityTemplateTest extends TestCase
{
    use RefreshDatabase;

    public function test_equity_standfirst_keeps_articles_with_the_next_word(): void
    {
        // Trello 438: "The fund is…" and "in the short…" start a line
        // instead of "The" / "in the" ending the one above.
        $user = User::factory()->create();
        $fund = Fund::factory()->for($user)->create([
            'template' => 'show-equity',
            'description' => 'Without assuming greater risk. The fund is appropriate for investors who can withstand volatility in the short to medium term.',
        ]);

        $html = $this->actingAs($user)->get(route('funds.show', $fund))->assertOk()->getContent();

        $this->assertStringContainsString('greater risk. The&nbsp;fund is appropriate', $html);
        $this->assertStringContainsString('volatility in&nbsp;the&nbsp;short to medium term.', $html);
    }
}
