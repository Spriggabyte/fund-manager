<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

/**
 * There is no public registration, so this is how the first admin account on a
 * fresh deployment comes into existence. Everyone after that is normally added
 * from the Users screen.
 */
class CreateUserCommand extends Command
{
    protected $signature = 'user:create
        {name : Full name of the person}
        {email : Email address they will sign in with}
        {--admin : Grant account management, fund deletion and Horizon access}
        {--password= : Set the password non-interactively (prompted for if omitted)}
        {--no-force-change : Do not require the user to choose a new password at first sign-in}';

    protected $description = 'Create a user account';

    public function handle(): int
    {
        $name = $this->argument('name');
        $email = strtolower(trim($this->argument('email')));
        $password = $this->option('password') ?: $this->secret('Password');

        $validator = Validator::make(
            ['name' => $name, 'email' => $email, 'password' => $password],
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', 'unique:users,email'],
                'password' => ['required', Password::defaults()],
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user = new User;
        $user->name = $name;
        $user->email = $email;
        $user->password = $password;
        $user->is_admin = (bool) $this->option('admin');
        $user->must_change_password = ! $this->option('no-force-change');
        $user->save();

        $this->info("Created {$user->name} <{$user->email}>".($user->isAdmin() ? ' (admin)' : '').'.');

        if ($user->must_change_password) {
            $this->line('They will be asked to choose their own password at first sign-in.');
        }

        return self::SUCCESS;
    }
}
