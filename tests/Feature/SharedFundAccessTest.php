<?php

namespace Tests\Feature;

use App\Models\Fund;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Funds are a shared team workspace: funds.user_id records who created a fund,
 * not who may see it. Deleting is the one action reserved for admins.
 */
class SharedFundAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_lists_funds_created_by_everyone(): void
    {
        $user = User::factory()->create();
        $colleague = User::factory()->create();

        Fund::factory()->create(['user_id' => $user->id, 'name' => 'My Fund']);
        Fund::factory()->create(['user_id' => $colleague->id, 'name' => 'Colleague Fund']);

        $this->actingAs($user)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('My Fund')
            ->assertSee('Colleague Fund');
    }

    public function test_a_colleague_can_open_and_edit_someone_elses_fund(): void
    {
        $creator = User::factory()->create();
        $colleague = User::factory()->create();
        $fund = Fund::factory()->create(['user_id' => $creator->id]);

        $this->actingAs($colleague)->get(route('funds.show', $fund))->assertOk();
        $this->actingAs($colleague)->get(route('funds.edit', $fund))->assertOk();
        $this->actingAs($colleague)->get(route('funds.revisions', $fund))->assertOk();
    }

    public function test_the_delete_control_is_hidden_from_non_admins(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $fund = Fund::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)->get(route('funds.edit', $fund))
            ->assertOk()
            ->assertDontSee('Delete Fund');

        $this->actingAs($admin)->get(route('funds.edit', $fund))
            ->assertOk()
            ->assertSee('Delete Fund');
    }

    public function test_deleting_a_user_leaves_their_funds_in_place(): void
    {
        $creator = User::factory()->create();
        $fund = Fund::factory()->create(['user_id' => $creator->id]);

        $creator->delete();

        $this->assertDatabaseHas('funds', ['id' => $fund->id, 'user_id' => null]);
    }

    public function test_a_fund_whose_creator_is_gone_is_still_usable(): void
    {
        $fund = Fund::factory()->create(['user_id' => User::factory()->create()->id]);
        $fund->user->delete();

        $this->actingAs(User::factory()->create())
            ->get(route('funds.show', $fund->fresh()))
            ->assertOk();
    }
}
