<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordIsChanged
{
    /**
     * Routes a user with an admin-set temporary password may still reach.
     *
     * @var list<string>
     */
    private const ALLOWED = [
        'password.change',
        'password.change.update',
        'logout',
    ];

    /**
     * Admins choose the initial password, so it is known to somebody other than
     * the account holder until they replace it. Hold them on the change screen
     * until they do.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->must_change_password && ! $request->routeIs(self::ALLOWED)) {
            return redirect()->route('password.change');
        }

        return $next($request);
    }
}
