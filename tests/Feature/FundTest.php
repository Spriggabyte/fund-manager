<?php

namespace Tests\Feature;

use App\Models\Fund;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FundTest extends TestCase
{
    use RefreshDatabase;

    public function test_funds_index_requires_authentication(): void
    {
        $response = $this->get(route('funds.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_their_funds(): void
    {
        $user = User::factory()->create();
        Fund::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('funds.index'));

        $response->assertOk();
    }

    public function test_create_form_is_accessible_to_authenticated_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('funds.create'));

        $response->assertOk();
    }

    public function test_user_can_create_a_fund(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('funds.store'), [
            'name' => 'Foord Balanced Fund',
            'class' => 'A',
        ]);

        $response->assertRedirect(route('funds.index'));
        $this->assertDatabaseHas('funds', [
            'name' => 'Foord Balanced Fund',
            'class' => 'A',
            'user_id' => $user->id,
        ]);
    }

    public function test_fund_name_is_required_on_create(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('funds.store'), [
            'name' => '',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_user_can_view_their_own_fund(): void
    {
        $user = User::factory()->create();
        $fund = Fund::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('funds.show', $fund));

        $response->assertOk();
    }

    public function test_user_can_view_a_fund_created_by_a_colleague(): void
    {
        $creator = User::factory()->create();
        $other = User::factory()->create();
        $fund = Fund::factory()->create(['user_id' => $creator->id]);

        $response = $this->actingAs($other)->get(route('funds.show', $fund));

        $response->assertOk();
    }

    public function test_user_can_edit_their_own_fund(): void
    {
        $user = User::factory()->create();
        $fund = Fund::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('funds.edit', $fund));

        $response->assertOk();
    }

    public function test_user_can_update_their_own_fund(): void
    {
        $user = User::factory()->create();
        $fund = Fund::factory()->create(['user_id' => $user->id, 'name' => 'Old Name']);

        $response = $this->actingAs($user)->put(route('funds.update', $fund), [
            'name' => 'New Name',
            'class' => 'B',
        ]);

        $response->assertRedirect(route('funds.show', $fund));
        $this->assertDatabaseHas('funds', ['id' => $fund->id, 'name' => 'New Name']);
    }

    public function test_user_can_update_a_fund_created_by_a_colleague(): void
    {
        $creator = User::factory()->create();
        $other = User::factory()->create();
        $fund = Fund::factory()->create(['user_id' => $creator->id, 'name' => 'Old Name']);

        $response = $this->actingAs($other)->put(route('funds.update', $fund), [
            'name' => 'New Name',
            'class' => 'B',
        ]);

        $response->assertRedirect(route('funds.show', $fund));
        $this->assertDatabaseHas('funds', ['id' => $fund->id, 'name' => 'New Name']);
    }

    public function test_admin_can_delete_a_fund(): void
    {
        $admin = User::factory()->admin()->create();
        $fund = Fund::factory()->create(['user_id' => User::factory()->create()->id]);

        $response = $this->actingAs($admin)->delete(route('funds.destroy', $fund));

        $response->assertRedirect(route('funds.index'));
        $this->assertDatabaseMissing('funds', ['id' => $fund->id]);
    }

    public function test_non_admin_cannot_delete_a_fund_even_their_own(): void
    {
        $user = User::factory()->create();
        $fund = Fund::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->delete(route('funds.destroy', $fund));

        $response->assertForbidden();
        $this->assertDatabaseHas('funds', ['id' => $fund->id]);
    }

    public function test_update_data_endpoint_updates_nested_field(): void
    {
        $user = User::factory()->create();
        $fund = Fund::factory()->create(['user_id' => $user->id, 'name' => 'Old']);

        $response = $this->actingAs($user)->patchJson(route('funds.update-data', $fund), [
            'field' => 'fund.name',
            'value' => 'New Name',
        ]);

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertEquals('New Name', $fund->fresh()->data['fund']['name']);
    }

    public function test_update_data_endpoint_is_open_to_any_signed_in_user(): void
    {
        $creator = User::factory()->create();
        $other = User::factory()->create();
        $fund = Fund::factory()->create(['user_id' => $creator->id]);

        $response = $this->actingAs($other)->patchJson(route('funds.update-data', $fund), [
            'field' => 'fund.name',
            'value' => 'Edited by a colleague',
        ]);

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertEquals('Edited by a colleague', $fund->fresh()->data['fund']['name']);
    }

    public function test_fund_index_shows_every_teams_fund(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        Fund::factory()->create(['user_id' => $user->id, 'name' => 'My Fund']);
        Fund::factory()->create(['user_id' => $other->id, 'name' => 'Other Fund']);

        $response = $this->actingAs($user)->get(route('funds.index'));

        $response->assertOk();
        $response->assertSee('My Fund');
        $response->assertSee('Other Fund');
    }
}
