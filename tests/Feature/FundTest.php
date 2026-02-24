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

    public function test_user_cannot_view_another_users_fund(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $fund = Fund::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($other)->get(route('funds.show', $fund));

        $response->assertForbidden();
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

        $response->assertRedirect(route('funds.index'));
        $this->assertDatabaseHas('funds', ['id' => $fund->id, 'name' => 'New Name']);
    }

    public function test_user_cannot_update_another_users_fund(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $fund = Fund::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($other)->put(route('funds.update', $fund), [
            'name' => 'Hacked Name',
        ]);

        $response->assertForbidden();
    }

    public function test_user_can_delete_their_own_fund(): void
    {
        $user = User::factory()->create();
        $fund = Fund::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->delete(route('funds.destroy', $fund));

        $response->assertRedirect(route('funds.index'));
        $this->assertDatabaseMissing('funds', ['id' => $fund->id]);
    }

    public function test_user_cannot_delete_another_users_fund(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $fund = Fund::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($other)->delete(route('funds.destroy', $fund));

        $response->assertForbidden();
        $this->assertDatabaseHas('funds', ['id' => $fund->id]);
    }

    public function test_update_data_endpoint_updates_nested_field(): void
    {
        $user = User::factory()->create();
        $fund = Fund::factory()->create(['user_id' => $user->id, 'data' => ['fund' => ['name' => 'Old']]]);

        $response = $this->actingAs($user)->patchJson(route('funds.update-data', $fund), [
            'field' => 'fund.name',
            'value' => 'New Name',
        ]);

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertEquals('New Name', $fund->fresh()->data['fund']['name']);
    }

    public function test_update_data_endpoint_is_restricted_to_fund_owner(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $fund = Fund::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($other)->patchJson(route('funds.update-data', $fund), [
            'field' => 'fund.name',
            'value' => 'Hacked',
        ]);

        $response->assertForbidden();
    }

    public function test_fund_index_only_shows_users_own_funds(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        Fund::factory()->create(['user_id' => $user->id, 'name' => 'My Fund']);
        Fund::factory()->create(['user_id' => $other->id, 'name' => 'Other Fund']);

        $response = $this->actingAs($user)->get(route('funds.index'));

        $response->assertOk();
        $response->assertSee('My Fund');
        $response->assertDontSee('Other Fund');
    }
}
