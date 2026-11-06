<?php

namespace Tests\Feature;

use App\Models\Fund;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * `fund:add-class` clones a fund's static content into a new share class.
 * The B2 class of the Prescient Asia ex-Japan feeder (829) was created this
 * way from Class A (fund 57).
 */
class AddFundClassCommandTest extends TestCase
{
    use RefreshDatabase;

    private function classA(): Fund
    {
        return Fund::factory()->create([
            'user_id' => User::factory()->create()->id,
            'name' => 'PRESCIENT FOORD ASIA EX-JAPAN FEEDER FUND — CLASS A',
            'class' => 'A',
            'fund_code' => '829',
            'class_code' => 'A',
            'template' => 'show-prescient-asia-ex-japan',
            'isin_number' => 'ZAE000368882',
            'unit_price' => '955.25 cents',
            'number_of_units' => '0.1 million',
            'portfolio_size' => 'R1.0 million',
            'last_distributions' => 'The Foord Asia ex-Japan Fund, in which the fund invests, does not distribute its income.',
            'fees' => ['feeRates' => ['title' => 'FEE RATES (CLASS A)']],
        ]);
    }

    public function test_clones_the_static_content_under_the_new_class(): void
    {
        $source = $this->classA();

        $this->artisan('fund:add-class', ['source' => $source->id, 'class' => 'b2'])
            ->assertExitCode(0);

        $clone = Fund::where('fund_code', '829')->where('class_code', 'B2')->firstOrFail();

        $this->assertSame('PRESCIENT FOORD ASIA EX-JAPAN FEEDER FUND — CLASS B2', $clone->name);
        // Both the share-class column the importer selects files by AND the
        // legacy `class` column (revisions, fund lists) carry the new class.
        $this->assertSame('B2', $clone->class_code);
        $this->assertSame('B2', $clone->class);
        $this->assertSame('show-prescient-asia-ex-japan', $clone->template);
        $this->assertSame($source->user_id, $clone->user_id);
        $this->assertSame('R1.0 million', $clone->portfolio_size);
        $this->assertSame('FEE RATES (CLASS A)', $clone->fees['feeRates']['title']);
    }

    public function test_per_class_fields_are_not_inherited(): void
    {
        $source = $this->classA();

        $this->artisan('fund:add-class', ['source' => $source->id, 'class' => 'B2'])
            ->assertExitCode(0);

        $clone = Fund::where('fund_code', '829')->where('class_code', 'B2')->firstOrFail();

        $this->assertNull($clone->isin_number);
        $this->assertNull($clone->unit_price);
        $this->assertNull($clone->number_of_units);
        $this->assertNull($clone->last_distributions);
        // The source is untouched.
        $this->assertSame('ZAE000368882', $source->fresh()->isin_number);
    }

    public function test_refuses_a_class_that_already_exists(): void
    {
        $source = $this->classA();

        $this->artisan('fund:add-class', ['source' => $source->id, 'class' => 'A'])
            ->assertExitCode(1);

        $this->assertSame(1, Fund::where('fund_code', '829')->count());
    }
}
