<?php

namespace Tests\Feature\Auth;

use App\Models\Fund;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ForcePasswordChangeTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_with_a_temporary_password_is_redirected_from_the_app(): void
    {
        $user = User::factory()->mustChangePassword()->create();
        $fund = Fund::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)->get(route('dashboard'))->assertRedirect(route('password.change'));
        $this->actingAs($user)->get(route('funds.show', $fund))->assertRedirect(route('password.change'));
    }

    public function test_the_change_password_screen_itself_is_reachable(): void
    {
        $user = User::factory()->mustChangePassword()->create();

        $this->actingAs($user)->get(route('password.change'))->assertOk();
    }

    public function test_changing_the_password_releases_the_user(): void
    {
        $user = User::factory()->mustChangePassword()->create();

        $this->actingAs($user)->put(route('password.change.update'), [
            'password' => 'my-own-secret-phrase',
            'password_confirmation' => 'my-own-secret-phrase',
        ])->assertRedirect(route('dashboard'));

        $user->refresh();
        $this->assertFalse($user->must_change_password);
        $this->assertTrue(Hash::check('my-own-secret-phrase', $user->password));

        $this->actingAs($user)->get(route('dashboard'))->assertOk();
    }

    public function test_a_weak_or_unconfirmed_password_is_rejected(): void
    {
        $user = User::factory()->mustChangePassword()->create();

        $this->actingAs($user)->put(route('password.change.update'), [
            'password' => 'short',
            'password_confirmation' => 'different',
        ])->assertSessionHasErrors('password');

        $this->assertTrue($user->fresh()->must_change_password);
    }

    public function test_users_without_the_flag_are_unaffected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('dashboard'))->assertOk();
    }
}
