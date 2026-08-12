<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Only admins manage accounts.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, User $model): bool
    {
        return $user->isAdmin();
    }

    /**
     * Disabling or demoting yourself is how a team locks itself out of user
     * management entirely, so an admin may never do either to their own
     * account. Another admin can.
     */
    public function disable(User $user, User $model): bool
    {
        return $user->isAdmin() && $user->id !== $model->id;
    }

    /**
     * Accounts are never deleted from the UI — deleting one detaches its fund
     * authorship. `php artisan user:list` plus a disable is the supported path.
     */
    public function delete(User $user, User $model): bool
    {
        return false;
    }
}
