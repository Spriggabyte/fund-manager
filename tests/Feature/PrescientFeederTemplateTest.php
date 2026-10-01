<?php

namespace Tests\Feature;

use App\Models\Fund;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrescientFeederTemplateTest extends TestCase
{
    use RefreshDatabase;

    public function test_prescient_feeder_standfirst_keeps_phrases_together(): void
    {
        // Trello 443: "South African" and "a hard-currency" start a line
        // instead of breaking across one (the 809 sheet's card 436 rule).
        $user = User::factory()->create();
        $fund = Fund::factory()->for($user)->create([
            'template' => 'show-prescient-feeder',
            'description' => 'The fund is appropriate for South African investors seeking growth from a hard-currency portfolio.',
        ]);

        $html = $this->actingAs($user)->get(route('funds.show', $fund))->assertOk()->getContent();

        $this->assertStringContainsString('for South&nbsp;African investors', $html);
        $this->assertStringContainsString('from a&nbsp;<span class="nobr">hard-currency</span> portfolio', $html);
    }
}
