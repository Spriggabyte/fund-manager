<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Admin-only account management. There is no public registration: every
 * account originates here or from `php artisan user:create`.
 */
class UserController extends Controller
{
    use AuthorizesRequests;

    public function index(): View
    {
        $users = User::withCount('funds')->orderBy('name')->get();

        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        return view('admin.users.create');
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = new User;
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->password = $validated['password'];
        // Admin-chosen passwords are temporary by definition — the account
        // holder replaces it at first login.
        $user->must_change_password = true;
        $user->is_admin = $request->boolean('is_admin');
        $user->save();

        return redirect()->route('admin.users.index')
            ->with('success', "Account created for {$user->name}. They will be asked to choose their own password when they first sign in.");
    }

    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        return view('admin.users.edit', compact('user'));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if (! empty($validated['password'])) {
            $user->password = $validated['password'];
            $user->must_change_password = true;
        }

        // An admin cannot demote themselves — see UserPolicy::disable().
        if ($request->user()->can('disable', $user)) {
            $user->is_admin = $request->boolean('is_admin');
        }

        $user->save();

        return redirect()->route('admin.users.index')
            ->with('success', "{$user->name} updated.");
    }

    public function disable(User $user): RedirectResponse
    {
        $this->authorize('disable', $user);

        $user->disabled_at = now();
        $user->save();

        return redirect()->route('admin.users.index')
            ->with('success', "{$user->name} can no longer sign in.");
    }

    public function enable(User $user): RedirectResponse
    {
        $this->authorize('disable', $user);

        $user->disabled_at = null;
        $user->save();

        return redirect()->route('admin.users.index')
            ->with('success', "{$user->name} can sign in again.");
    }
}
