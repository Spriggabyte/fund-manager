<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DisabledUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_disabled_users_cannot_log_in(): void
    {
        $user = User::factory()->disabled()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_disabling_a_signed_in_user_ends_their_session(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('dashboard'))->assertOk();

        $user->disabled_at = now();
        $user->save();

        $this->actingAs($user)->get(route('dashboard'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_re_enabled_users_can_log_in_again(): void
    {
        $user = User::factory()->disabled()->create();

        $user->disabled_at = null;
        $user->save();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($user);
    }
}
