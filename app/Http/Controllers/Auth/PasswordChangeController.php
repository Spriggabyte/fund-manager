<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * First-login password change. An admin chooses the initial password, so it is
 * known to somebody else until the account holder replaces it here.
 */
class PasswordChangeController extends Controller
{
    public function edit(): View
    {
        return view('auth.change-password');
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = $request->user();
        $user->password = $validated['password'];
        $user->must_change_password = false;
        $user->save();

        return redirect()->route('dashboard')
            ->with('success', 'Password updated.');
    }
}
