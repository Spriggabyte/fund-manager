<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

/**
 * Recovery path when nobody can reach the Users screen — a locked-out sole
 * admin, or a deployment where SMTP (and therefore /forgot-password) is not
 * configured. See docs/users.md.
 */
class ResetUserPasswordCommand extends Command
{
    protected $signature = 'user:password
        {email : Email address of the account to reset}
        {--password= : Set the password non-interactively (prompted for if omitted)}
        {--no-force-change : Do not require the user to choose a new password at next sign-in}';

    protected $description = 'Reset a user password';

    public function handle(): int
    {
        $email = strtolower(trim($this->argument('email')));
        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->error("No account found for {$email}.");

            return self::FAILURE;
        }

        $password = $this->option('password') ?: $this->secret('New password');

        $validator = Validator::make(['password' => $password], [
            'password' => ['required', Password::defaults()],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user->password = $password;
        $user->must_change_password = ! $this->option('no-force-change');
        $user->save();

        $this->info("Password reset for {$user->name} <{$user->email}>.");

        if ($user->isDisabled()) {
            $this->warn('This account is disabled — it still cannot sign in. Enable it from the Users screen.');
        }

        return self::SUCCESS;
    }
}
