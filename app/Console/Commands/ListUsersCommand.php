<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ListUsersCommand extends Command
{
    protected $signature = 'user:list';

    protected $description = 'List user accounts';

    public function handle(): int
    {
        $users = User::orderBy('name')->get();

        if ($users->isEmpty()) {
            $this->warn('No accounts exist yet. Create the first admin with:  php artisan user:create "Name" name@example.com --admin');

            return self::SUCCESS;
        }

        $this->table(
            ['ID', 'Name', 'Email', 'Role', 'Status'],
            $users->map(fn (User $user) => [
                $user->id,
                $user->name,
                $user->email,
                $user->isAdmin() ? 'admin' : 'user',
                $user->isDisabled()
                    ? 'disabled '.$user->disabled_at?->format('Y-m-d')
                    : ($user->must_change_password ? 'temporary password' : 'active'),
            ])->all()
        );

        return self::SUCCESS;
    }
}
