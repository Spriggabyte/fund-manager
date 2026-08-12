# Outgoing Mail (SMTP)

Fund Manager — how email works, how to configure an SMTP relay, and how to
verify delivery.

Companion to [`DEPLOYMENT.md`](../DEPLOYMENT.md). Read §1 first: the app sends far
less email than you probably expect.

---

## 1. What actually sends email today

**The application has no email features of its own.** There are no Mailable
classes (`app/Mail/` does not exist), no custom notifications
(`app/Notifications/` does not exist), no mail templates, and no `Mail::` call
sites anywhere in `app/`. Nothing about fund imports, PDF exports, or the SFTP
sync sends mail to anyone.

The only email that can leave this app is Laravel's own authentication mail:

| Email | Class | Trigger | Status |
|---|---|---|---|
| Password reset | `Illuminate\Auth\Notifications\ResetPassword` | `POST /forgot-password` (`routes/auth.php:28`) | **Active** — the only live email |
| Email verification | `Illuminate\Auth\Notifications\VerifyEmail` | `POST /email/verification-notification` | **Disabled** — see below |

**Password reset** is the one email that matters. A user submits their address at
`/forgot-password`; `PasswordResetLinkController::store()`
(`app/Http/Controllers/Auth/PasswordResetLinkController.php:36`) calls
`Password::sendResetLink()`, which sends a signed reset link. Token settings live
in `config/auth.php:95-102`:

- link expires after **60 minutes** (`'expire' => 60`)
- a user can only request one every **60 seconds** (`'throttle' => 60`)
- tokens are stored in the `password_reset_tokens` table

If SMTP is not configured, **users cannot reset their own passwords** — but this
is no longer a lockout. An admin resets the password from the in-app Users screen
or with `php artisan user:password`, and the user is prompted to choose their own
at next sign-in (see [`users.md`](users.md) §4). Configuring SMTP turns
`/forgot-password` into a working self-service route with no code change.

**Email verification is switched off.** `app/Models/User.php:5` has the
`MustVerifyEmail` import commented out and the class does not implement the
interface, so the `Registered` event listener no-ops and `VerifyEmail` never
sends. Note that `routes/web.php:20` puts `verified` middleware on `/dashboard` —
because the user model is not `MustVerifyEmail`, **that middleware currently
passes everyone through**. It looks like verification is enforced; it is not.
See §8 before turning it on.

**Branding:** the reset email uses Laravel's default markdown template, which is
unbranded rather than mis-branded — it pulls `APP_NAME` throughout, so it already
reads "Fund Manager" in the header, the sign-off, and the
`© Fund Manager. All rights reserved.` footer. There is no Laravel logo or
wording anywhere in it. What it lacks is any Foord styling: no logo, no brand
colours, default typography. To change that, publish the templates and edit them:

```bash
php artisan vendor:publish --tag=laravel-mail   # → resources/views/vendor/mail/
```

---

## 2. How the mail pipeline works

```
POST /forgot-password
  └─ Password::sendResetLink()                    PasswordResetLinkController.php:36
      └─ User (Notifiable) ->notify(ResetPassword)
          └─ mail channel → renders the markdown template
              └─ mailer named by MAIL_MAILER      config/mail.php:17
                  └─ Symfony Mailer transport (smtp | log | array | sendmail)
                      └─ your relay
```

Two behaviours worth knowing before you point this at a production relay:

**It is synchronous.** `ResetPassword` extends `Notification` — not
`ShouldQueue` — and nothing in this app queues mail. The SMTP conversation
happens *inside* the `POST /forgot-password` request, while the user waits. Relay
latency is user-facing latency. (The Redis/Horizon queue described in
`DEPLOYMENT.md` §7 handles PDF renders only; mail never touches it.)

**The socket timeout is PHP's default, not Laravel's.** `config/mail.php:48` sets
`'timeout' => null`, and `MailManager::configureSmtpTransport()` only applies a
timeout when the value `isset()` — null therefore falls through to PHP's
`default_socket_timeout` (60 seconds on a stock install; check with
`php -r "echo ini_get('default_socket_timeout');"`). So an unreachable relay
stalls `/forgot-password` for up to a minute before erroring. If you need it
tighter, set a number instead of `null` in `config/mail.php`.

---

## 3. Configuration reference

`config/mail.php` is the unmodified Laravel 12 file. Every value below comes from
the environment.

| Key | Default | Purpose |
|---|---|---|
| `MAIL_MAILER` | `log` | Which mailer to use. **Production must be `smtp`.** |
| `MAIL_HOST` | `127.0.0.1` | Relay hostname. |
| `MAIL_PORT` | `2525` | Relay port. Also selects TLS mode — see below. |
| `MAIL_USERNAME` | *(null)* | SMTP AUTH username. Literal `null` means no auth. |
| `MAIL_PASSWORD` | *(null)* | SMTP AUTH password. |
| `MAIL_FROM_ADDRESS` | `hello@example.com` | Global From address. **Must be changed.** |
| `MAIL_FROM_NAME` | `${APP_NAME}` | Global From name. |
| `MAIL_SCHEME` | *(null)* | Force `smtp` (STARTTLS) or `smtps` (implicit TLS). Normally leave unset. |
| `MAIL_EHLO_DOMAIN` | host of `APP_URL` | Domain sent in the SMTP `EHLO`. Some relays check it. |
| `MAIL_URL` | *(unset)* | Full DSN (`smtp://user:pass@host:587`) instead of the individual keys. |
| `MAIL_LOG_CHANNEL` | *(unset)* | Which log channel the `log` mailer writes to. Defaults to the `stack`. |

### TLS: the port decides, not `MAIL_ENCRYPTION`

`MAIL_ENCRYPTION` **does nothing in this project.** The `smtp` array in
`config/mail.php:40-50` has no `encryption` key, so the value is never read.
Laravel picks the scheme from the port instead
(`MailManager::createSmtpTransport`, `vendor/laravel/framework/src/Illuminate/Mail/MailManager.php:193-197`):

| Port | Resulting scheme | Behaviour |
|---|---|---|
| `465` | `smtps` | Implicit TLS — the connection is encrypted from the first byte. |
| anything else | `smtp` | Plain connect, then **STARTTLS automatically** if the server advertises it. |

Symfony's auto-TLS means port **587 needs no extra configuration** — it upgrades
to TLS on its own. Only set `MAIL_SCHEME` if you need to override that.

To *require* encryption (fail rather than fall back to plaintext), or to relax
certificate checking against an internal relay, add the option to the `smtp`
array in `config/mail.php` — these are passed straight through to the Symfony DSN:

```php
'smtp' => [
    // ...
    'require_tls' => true,    // refuse to send unencrypted
    // 'verify_peer' => false,  // internal relay with a self-signed certificate
],
```

### Which transports actually work

`config/mail.php` lists `ses`, `postmark`, and `resend`, but **their packages are
not installed** — `composer.json` requires none of them. Selecting one throws at
send time. Usable transports:

| Mailer | Use |
|---|---|
| `smtp` | Production. |
| `log` | Writes the rendered message to the log instead of sending. Current default. |
| `array` | Keeps messages in memory; forced by `phpunit.xml:27` for the test suite. |
| `sendmail` | Local MTA via `MAIL_SENDMAIL_PATH`. |
| `failover` | Configured as `['smtp', 'log']` but unused — set `MAIL_MAILER=failover` to fall back to the log when the relay is down. |

To use SendGrid/Mailgun/Postmark, either use their **generic SMTP endpoint**
(no package needed — see §4) or `composer require` the transport package first.

---

## 4. Setting up SMTP

Pick one of the blocks below, put it in `.env`, then apply it (steps at the end
of this section).

### Microsoft 365 / Exchange Online

```dotenv
MAIL_MAILER=smtp
MAIL_HOST=smtp.office365.com
MAIL_PORT=587
MAIL_USERNAME=fundmanager@yourdomain.com
MAIL_PASSWORD="<app password or service-account password>"
MAIL_FROM_ADDRESS="fundmanager@yourdomain.com"
MAIL_FROM_NAME="${APP_NAME}"
```

Tenant-side prerequisites — these cause most failures:

- **SMTP AUTH must be enabled on the mailbox.** It is off by default:
  `Set-CASMailbox -Identity fundmanager@yourdomain.com -SmtpClientAuthenticationDisabled $false`
- **Basic auth is blocked in most tenants.** Use an app password (requires MFA on
  the account) or ask IT for a mail-enabled service account with SMTP AUTH
  allowed.
- `MAIL_FROM_ADDRESS` must be the authenticated mailbox or an address it has
  *Send As* rights to. Microsoft rejects mismatches.
- Consider a **high-volume connector** instead of mailbox auth if IT prefers it —
  that is an unauthenticated relay restricted by source IP, in which case leave
  `MAIL_USERNAME`/`MAIL_PASSWORD` as `null`.

### Generic authenticated relay

STARTTLS on 587 (preferred):

```dotenv
MAIL_MAILER=smtp
MAIL_HOST=smtp.yourprovider.com
MAIL_PORT=587
MAIL_USERNAME=<username>
MAIL_PASSWORD="<password>"
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="${APP_NAME}"
```

Implicit TLS on 465 — change the port only; the scheme follows automatically:

```dotenv
MAIL_PORT=465
```

Unauthenticated internal relay (IP-restricted, port 25):

```dotenv
MAIL_MAILER=smtp
MAIL_HOST=relay.internal.yourdomain.com
MAIL_PORT=25
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_EHLO_DOMAIN=fundmanager.yourdomain.com
```

### Transactional provider over SMTP

The Postmark/Resend/SES packages are not installed, so use the SMTP endpoint:

```dotenv
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net        # or smtp.mailgun.org / smtp.postmarkapp.com
MAIL_PORT=587
MAIL_USERNAME=apikey               # SendGrid uses the literal string "apikey"
MAIL_PASSWORD="<api key>"
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### Deliverability

- The `MAIL_FROM_ADDRESS` domain must authorise the relay in **SPF**, and ideally
  sign with **DKIM**. Without both, a DMARC-enforcing recipient will reject or
  junk the reset email.
- Use a real, monitored domain — not `example.com`, and not a bare hostname.
- `MAIL_EHLO_DOMAIN` defaults to the host of `APP_URL`. If `APP_URL` is an
  internal hostname, set `MAIL_EHLO_DOMAIN` to the public domain; strict relays
  reject unresolvable EHLO names.

### Applying the change in production

`.env` lives in Deployer's **shared** directory and survives releases
(`DEPLOYMENT.md` §3):

```bash
ssh deploy@host
vi /var/www/fund-manager/shared/.env          # edit the MAIL_* block

cd /var/www/fund-manager/current
php artisan config:clear
php artisan config:cache                      # config is cached — editing .env alone changes nothing
php artisan horizon:terminate                 # workers reload with the new config
```

**Do not skip `config:cache`.** The deploy caches config; a `.env` edit has no
effect until the cache is rebuilt. Then verify with §6 before walking away.

---

## 5. Local development

### Mailpit (recommended)

Catches everything on `localhost` and gives you a web inbox — real SMTP, real
rendering, nothing escapes the machine.

```bash
brew install mailpit
mailpit                      # SMTP on :1025, web UI on http://localhost:8025
```

```dotenv
MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="fundmanager@foord.test"
MAIL_FROM_NAME="${APP_NAME}"
```

Run `php artisan config:clear` after editing `.env`, then open
<http://localhost:8025>.

### `MAIL_MAILER=log` (the current default)

Zero setup — the fully rendered message is appended to `storage/logs/laravel.log`
and never sent. Fine for confirming *that* mail fires, useless for checking how
it looks.

```bash
tail -f storage/logs/laravel.log
```

One caveat worth remembering: this writes **recipient addresses and full message
bodies in plaintext** into logs retained for 30 days (`LOG_DAILY_DAYS=30`).
Acceptable locally; never use the `log` mailer in production for that reason.

### Tests

`phpunit.xml:27` forces `MAIL_MAILER=array`, so the suite never opens a socket.
`tests/Feature/Auth/PasswordResetTest.php` fakes at the notification layer
(`Notification::fake()`), so **no test verifies rendering, the From address, or
transport health** — §6 is the only check that does.

---

## 6. Verifying delivery

**1. Confirm the running config is what you think it is.** This reads the
*cached* config, which is the point:

```bash
php artisan tinker --execute="dump(config('mail.default'), config('mail.mailers.smtp'), config('mail.from'));"
```

`mail.default` must be `smtp`, not `log`.

**2. Send a test message through the real transport:**

```bash
php artisan tinker --execute="Mail::raw('Fund Manager SMTP test', fn(\$m) => \$m->to('you@yourdomain.com')->subject('Fund Manager SMTP test'));"
```

Silence means success — the transport throws on failure. Check the inbox
(or <http://localhost:8025> with Mailpit).

**3. Exercise the real path end to end.** Visit `/forgot-password`, submit a real
user's address, confirm the email arrives and the reset link works. This is the
only check that covers rendering, the signed URL, and `APP_URL` all at once — a
wrong `APP_URL` produces an email that arrives but whose link is broken.

Repeat step 3 after any change to `APP_URL` or the mail templates.

---

## 7. Troubleshooting

| Symptom | Cause / fix |
|---|---|
| Nothing arrives, no error | `MAIL_MAILER` is still `log` — check `storage/logs/laravel.log`, then set `smtp` and re-run `config:cache`. |
| `.env` edited but nothing changed | Config cache is stale. `php artisan config:clear && php artisan config:cache`, then `horizon:terminate`. |
| `/forgot-password` hangs ~60s then errors | Relay unreachable (firewall, wrong host/port). The send is synchronous and falls back to PHP's `default_socket_timeout` — see §2. |
| `Expected response code "250" but got code "535"` | Bad credentials, or SMTP AUTH disabled on the mailbox (Microsoft 365 — see §4). |
| `Expected response code "250" but got code "550"` | Relay refuses the From address — it must match the authenticated mailbox/domain. |
| `stream_socket_enable_crypto` / TLS handshake failure | TLS mode wrong for the port. Use 587 (auto STARTTLS) or 465 (implicit); override with `MAIL_SCHEME` only if needed. Self-signed internal relay → `'verify_peer' => false` (§3). |
| `Unable to connect with STARTTLS` | Relay does not support TLS on that port; try 465, or the plaintext internal-relay block in §4. |
| `Unsupported mail transport [postmark]` | Package not installed — use the provider's SMTP endpoint (§3). |
| Mail sends but lands in spam | SPF/DKIM missing for the `MAIL_FROM_ADDRESS` domain (§4). |
| Email arrives, reset link 404s or is "invalid" | `APP_URL` is wrong or the link expired (60 min, `config/auth.php:99`). |
| "Please wait before retrying" | Reset throttle — 60 s per user (`config/auth.php:100`). |
| Reset email is unstyled / has no Foord logo | Default mail templates never published or branded (§1). |

**Where errors surface:** an SMTP `TransportException` is written to
`storage/logs/` and, when `SENTRY_LARAVEL_DSN` is set, reported to Sentry (wired
at `bootstrap/app.php:20`). `SENTRY_SEND_DEFAULT_PII=false` keeps recipient
addresses out of Sentry. There is **no** alert dedicated to mail failure — if the
relay dies, you find out from the logs or from a user who cannot reset their
password.

---

## 8. Known gaps

Deliberately not implemented. Listed so nobody assumes otherwise:

- **No queued mail.** Sends happen in-request (§2). Adding `ShouldQueue` requires
  a custom notification class overriding the framework's.
- **No branded templates.** Reset mail renders with Laravel's default markdown
  theme. It carries no Laravel branding (it uses `APP_NAME`), but equally no
  Foord logo or brand colours, until `vendor:publish --tag=laravel-mail` is run
  and the views are styled.
- **No operational alerting by email.** Nothing emails anyone on a failed queue
  job, a failed PDF render
  (`app/Jobs/GenerateFundPdfJob.php` logs and records `status=failed` only), a
  failed `funds:sync-data` run, or Horizon's `LongWaitDetected`
  (`config/horizon.php:115`). The `Horizon::routeMailNotificationsTo()` line in
  `app/Providers/HorizonServiceProvider.php` is **commented out** — despite what
  `DEPLOYMENT.md` §8 implies, no notifications are routed anywhere. Enabling it
  is a one-line change once SMTP works.
- **No scheduled-task failure mail.** `routes/console.php` uses no
  `->emailOutputOnFailure()`.
- **Email verification is off** (§1), and largely moot now that accounts are
  created by an admin who already knows the address
  ([`users.md`](users.md)). Turning it on means uncommenting the import
  in `app/Models/User.php` and adding `implements MustVerifyEmail` to the class.
  Two consequences land at once: verification emails start sending, **and** the
  dormant `verified` middleware on `/dashboard` (`routes/web.php:20`) starts
  locking out every existing user whose `email_verified_at` is null. Get SMTP
  working and backfill `email_verified_at` for existing users first.
- **No mail test coverage.** No `Mail::fake()` anywhere; nothing asserts rendered
  content, the From address, or that a transport is reachable.
