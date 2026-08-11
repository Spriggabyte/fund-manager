# User Accounts

Fund Manager — who gets an account, how they get one, and what they can do.

Companion to [`DEPLOYMENT.md`](../DEPLOYMENT.md) and [`mail.md`](mail.md).

---

## 1. The model in one paragraph

Accounts are **provisioned by an administrator**. There is no public sign-up:
`/register` does not exist and cannot be reached. Every signed-in user shares the
same set of funds — anyone can create, edit, import into, and export any fund.
Two things are reserved for admins: **managing accounts** and **deleting funds**.
Accounts are switched off rather than deleted, so the record of who created each
fund survives staff turnover.

| | Regular user | Admin |
|---|---|---|
| View / edit / import any fund | ✅ | ✅ |
| Create funds, export PDFs | ✅ | ✅ |
| Delete a fund | ❌ | ✅ |
| Add, edit, disable accounts | ❌ | ✅ |
| Horizon queue dashboard (`/horizon`) | ❌ | ✅ |

`funds.user_id` records **who created** a fund, not who owns it. Deleting a user
row sets it to null and leaves the fund intact.

---

## 2. Creating the first admin (fresh deployment)

There are no accounts on a new install, and no way to sign up, so the first one
comes from the command line:

```bash
cd /var/www/fund-manager/current
php artisan user:create "Jane Smith" jane.smith@foord.co.za --admin
# prompts for a password (not echoed)
```

Then sign in at `/login`. You will be asked to choose your own password
immediately — see §4.

Add everyone else from the **Users** screen in the app rather than repeating this
command.

---

## 3. Day-to-day account management

**Users → Add User** (top-right of the Users screen, admins only).

You enter the person's name, email, and a **temporary password**, and decide
whether they are an admin. Hand the password over directly — in person, by
phone, or over Teams. They are prompted to replace it the moment they first sign
in, so it only has to survive the handover.

| Task | Where |
|---|---|
| Add someone | Users → Add User |
| Change a name or email | Users → Edit |
| Reset a forgotten password | Users → Edit → *Reset Password* (leave blank to keep the current one) |
| Grant or revoke admin | Users → Edit → *Administrator* checkbox |
| Off-board someone | Users → **Disable** |
| Bring them back | Users → **Enable** |

**Disabling** takes effect immediately: an active session is terminated on their
next request, and their next login attempt is refused. Their funds, revisions,
and authorship are untouched.

**You cannot disable or demote yourself.** This is deliberate — it is how a team
locks itself out of user management entirely. Ask another admin, or use the CLI.
Keep **at least two admins** for this reason.

---

## 4. Passwords

Admin-chosen passwords are temporary by design. Any password set by an admin —
whether at account creation or via *Reset Password* — sets a flag that pins the
user to a change-password screen until they choose their own. Nothing else in
the app is reachable until they do.

Users change their own password at any time from **Profile → Update Password**.

**Self-service reset (`/forgot-password`) needs SMTP.** Until
`MAIL_MAILER=smtp` is configured (see [`mail.md`](mail.md) §4), the reset email
is written to the log and never delivered, so the route is effectively dead. Use
*Reset Password* on the Users screen instead. Once SMTP works, `/forgot-password`
starts working with no code change.

---

## 5. Command line reference

For bootstrapping and for recovery when nobody can reach the Users screen —
a locked-out sole admin, or a deployment with no SMTP.

```bash
php artisan user:list                        # who exists, their role and status
php artisan user:create "Name" a@foord.co.za # prompts for a password
    [--admin] [--password=…] [--no-force-change]
php artisan user:password a@foord.co.za      # reset; prompts for the new password
    [--password=…] [--no-force-change]
```

Pass `--password=` only in scripts — it lands in your shell history. Omit it and
the command prompts without echoing.

`--no-force-change` skips the first-login password change. Use it only for your
own account when you are typing the password yourself.

Both commands enforce Laravel's default password rules, so a weak password is
rejected rather than silently accepted.

---

## 6. Off-boarding checklist

1. Users → **Disable** the account. They are signed out immediately.
2. Confirm with `php artisan user:list` — the status reads `disabled`.
3. If they were an admin, check at least one other admin remains.

Do **not** delete the row from the database. Nothing in the UI does, and the
fund-creation history is worth keeping.

---

## 7. Where this is implemented

| Concern | File |
|---|---|
| Shared fund access, admin-only delete | `app/Policies/FundPolicy.php` |
| Who may manage accounts | `app/Policies/UserPolicy.php` |
| Admin screens | `app/Http/Controllers/Admin/UserController.php`, `resources/views/admin/users/` |
| Admin-area gate | `app/Http/Middleware/EnsureUserIsAdmin.php` (alias `admin`) |
| Disabled accounts | `app/Http/Middleware/EnsureUserIsActive.php`, `app/Http/Requests/Auth/LoginRequest.php` |
| Forced password change | `app/Http/Middleware/EnsurePasswordIsChanged.php`, `app/Http/Controllers/Auth/PasswordChangeController.php` |
| No registration route | `routes/auth.php` |
| Horizon access | `app/Providers/HorizonServiceProvider.php` |

Coverage lives in `tests/Feature/Admin/UserManagementTest.php`,
`tests/Feature/Auth/{DisabledUserTest,ForcePasswordChangeTest,RegistrationTest}.php`,
`tests/Feature/SharedFundAccessTest.php`, and
`tests/Feature/Console/UserCommandsTest.php`.
