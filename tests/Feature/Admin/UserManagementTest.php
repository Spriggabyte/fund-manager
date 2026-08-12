<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_area_requires_authentication(): void
    {
        $this->get(route('admin.users.index'))->assertRedirect(route('login'));
    }

    public function test_non_admins_are_forbidden_from_every_user_route(): void
    {
        $user = User::factory()->create();
        $target = User::factory()->create();

        $this->actingAs($user)->get(route('admin.users.index'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.users.create'))->assertForbidden();
        $this->actingAs($user)->post(route('admin.users.store'), [])->assertForbidden();
        $this->actingAs($user)->get(route('admin.users.edit', $target))->assertForbidden();
        $this->actingAs($user)->patch(route('admin.users.update', $target), [])->assertForbidden();
        $this->actingAs($user)->post(route('admin.users.disable', $target))->assertForbidden();
        $this->actingAs($user)->post(route('admin.users.enable', $target))->assertForbidden();
    }

    public function test_admin_can_list_users(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Ada Admin']);
        User::factory()->create(['name' => 'Sam Staff']);

        $this->actingAs($admin)->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Ada Admin')
            ->assertSee('Sam Staff');
    }

    public function test_admin_can_open_the_create_and_edit_screens(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['name' => 'Sam Staff']);

        $this->actingAs($admin)->get(route('admin.users.create'))->assertOk();
        $this->actingAs($admin)->get(route('admin.users.edit', $user))
            ->assertOk()
            ->assertSee('Sam Staff');
    }

    public function test_admins_cannot_edit_their_own_admin_flag_from_the_form(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('admin.users.edit', $admin))
            ->assertOk()
            ->assertSee('You cannot change your own admin rights');
    }

    public function test_admin_can_create_a_user_with_a_temporary_password(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Sam Staff',
            'email' => 'sam@example.com',
            'password' => 'correct-horse-battery',
            'password_confirmation' => 'correct-horse-battery',
            'is_admin' => '0',
        ])->assertRedirect(route('admin.users.index'));

        $user = User::where('email', 'sam@example.com')->firstOrFail();
        $this->assertFalse($user->isAdmin());
        $this->assertTrue($user->must_change_password);
        $this->assertTrue(Hash::check('correct-horse-battery', $user->password));
    }

    public function test_admin_can_grant_admin_rights_on_create(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Second Admin',
            'email' => 'second@example.com',
            'password' => 'correct-horse-battery',
            'password_confirmation' => 'correct-horse-battery',
            'is_admin' => '1',
        ])->assertRedirect(route('admin.users.index'));

        $this->assertTrue(User::where('email', 'second@example.com')->firstOrFail()->isAdmin());
    }

    public function test_duplicate_email_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->create(['email' => 'taken@example.com']);

        $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Clashing Person',
            'email' => 'taken@example.com',
            'password' => 'correct-horse-battery',
            'password_confirmation' => 'correct-horse-battery',
        ])->assertSessionHasErrors('email');
    }

    public function test_admin_can_edit_a_user_without_touching_their_password(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['name' => 'Old Name']);
        $originalHash = $user->password;

        $this->actingAs($admin)->patch(route('admin.users.update', $user), [
            'name' => 'New Name',
            'email' => $user->email,
            'password' => '',
            'is_admin' => '0',
        ])->assertRedirect(route('admin.users.index'));

        $user->refresh();
        $this->assertSame('New Name', $user->name);
        $this->assertSame($originalHash, $user->password);
        $this->assertFalse($user->must_change_password);
    }

    public function test_admin_resetting_a_password_makes_it_temporary(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $this->actingAs($admin)->patch(route('admin.users.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'password' => 'brand-new-secret',
            'password_confirmation' => 'brand-new-secret',
            'is_admin' => '0',
        ])->assertRedirect(route('admin.users.index'));

        $user->refresh();
        $this->assertTrue(Hash::check('brand-new-secret', $user->password));
        $this->assertTrue($user->must_change_password);
    }

    public function test_admin_can_disable_and_re_enable_another_user(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $this->actingAs($admin)->post(route('admin.users.disable', $user))
            ->assertRedirect(route('admin.users.index'));
        $this->assertTrue($user->fresh()->isDisabled());

        $this->actingAs($admin)->post(route('admin.users.enable', $user))
            ->assertRedirect(route('admin.users.index'));
        $this->assertFalse($user->fresh()->isDisabled());
    }

    public function test_admin_cannot_disable_themselves(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('admin.users.disable', $admin))
            ->assertForbidden();

        $this->assertFalse($admin->fresh()->isDisabled());
    }

    public function test_admin_cannot_demote_themselves(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->patch(route('admin.users.update', $admin), [
            'name' => $admin->name,
            'email' => $admin->email,
            'password' => '',
            'is_admin' => '0',
        ])->assertRedirect(route('admin.users.index'));

        $this->assertTrue($admin->fresh()->isAdmin());
    }

    public function test_users_link_is_only_shown_to_admins(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $this->actingAs($admin)->get(route('dashboard'))
            ->assertSee(route('admin.users.index'));

        $this->actingAs($user)->get(route('dashboard'))
            ->assertDontSee(route('admin.users.index'));
    }
}
