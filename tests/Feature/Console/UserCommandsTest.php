<?php

namespace Tests\Feature\Console;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserCommandsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_create_makes_an_admin_with_a_temporary_password(): void
    {
        $this->artisan('user:create', [
            'name' => 'Ada Admin',
            'email' => 'Ada@Example.com',
            '--admin' => true,
            '--password' => 'correct-horse-battery',
        ])->assertSuccessful();

        $user = User::where('email', 'ada@example.com')->firstOrFail();
        $this->assertTrue($user->isAdmin());
        $this->assertTrue($user->must_change_password);
        $this->assertTrue(Hash::check('correct-horse-battery', $user->password));
    }

    public function test_user_create_can_skip_the_forced_change(): void
    {
        $this->artisan('user:create', [
            'name' => 'Sam Staff',
            'email' => 'sam@example.com',
            '--password' => 'correct-horse-battery',
            '--no-force-change' => true,
        ])->assertSuccessful();

        $user = User::where('email', 'sam@example.com')->firstOrFail();
        $this->assertFalse($user->isAdmin());
        $this->assertFalse($user->must_change_password);
    }

    public function test_user_create_rejects_a_duplicate_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $this->artisan('user:create', [
            'name' => 'Clashing Person',
            'email' => 'taken@example.com',
            '--password' => 'correct-horse-battery',
        ])->assertFailed();

        $this->assertSame(1, User::where('email', 'taken@example.com')->count());
    }

    public function test_user_create_rejects_a_weak_password(): void
    {
        $this->artisan('user:create', [
            'name' => 'Sam Staff',
            'email' => 'sam@example.com',
            '--password' => 'short',
        ])->assertFailed();

        $this->assertDatabaseMissing('users', ['email' => 'sam@example.com']);
    }

    public function test_user_password_resets_an_existing_account(): void
    {
        $user = User::factory()->create();

        $this->artisan('user:password', [
            'email' => $user->email,
            '--password' => 'brand-new-secret',
        ])->assertSuccessful();

        $user->refresh();
        $this->assertTrue(Hash::check('brand-new-secret', $user->password));
        $this->assertTrue($user->must_change_password);
    }

    public function test_user_password_fails_for_an_unknown_address(): void
    {
        $this->artisan('user:password', [
            'email' => 'nobody@example.com',
            '--password' => 'brand-new-secret',
        ])->assertFailed();
    }

    public function test_user_list_shows_accounts(): void
    {
        User::factory()->admin()->create(['name' => 'Ada Admin', 'email' => 'ada@example.com']);

        $this->artisan('user:list')
            ->expectsOutputToContain('ada@example.com')
            ->assertSuccessful();
    }
}
