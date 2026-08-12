# Deployment & Operations Runbook

Fund Manager — Laravel 12 app that generates branded PDF fact sheets (Blade +
Highcharts, rendered by Puppeteer/headless Chrome) with PDF generation handled
as queued work on Redis via Laravel Horizon.

This runbook is for the IT team operating the app. It covers deploy, rollback,
required configuration, and monitoring.

Standing up a **staging/review server** from scratch (Ubuntu provisioning,
scrubbed data, basic auth, and the gotchas that break PDF rendering behind a
gated host): [`docs/staging.md`](docs/staging.md).

---

## 1. Architecture at a glance

| Concern | Choice |
|---|---|
| Runtime | PHP 8.2+ (CI tests 8.2 & 8.3), Node 22, nginx + PHP-FPM |
| Database | MySQL (or any Laravel-supported DB); migrations run on deploy |
| Queue | Redis + Laravel Horizon (PDF renders run as jobs) |
| PDF rendering | Puppeteer driving Chrome/Chromium (heavy; queued) |
| Deploy | Deployer over SSH — atomic releases + symlink switch |
| CI/CD | GitHub Actions (`ci.yml` gates, `deploy.yml` ships on green main) |
| Error tracking | Sentry (web + queue) |
| Uptime | `/up` health endpoint; Horizon dashboard at `/horizon` |

---

## 2. Server prerequisites (one-time)

Install on the production host:

- PHP 8.2+ with extensions: `mbstring dom xml pdo pdo_mysql sqlite pcntl gd zip bcmath intl`
- Composer 2, Node 22 + npm
- Redis server (for the queue + Horizon)
- MySQL (or your chosen DB)
- Supervisor (to keep Horizon running)
- Chrome/Chromium + its runtime libraries (see §6)

Create the deploy user and directory (matches `DEPLOY_PATH`):

```bash
sudo mkdir -p /var/www/fund-manager
sudo chown deploy:www-data /var/www/fund-manager
```

Point the web server document root at `/var/www/fund-manager/current/public`.

---

## 3. Required environment variables

`.env` lives in Deployer's **shared** directory (`{deploy_path}/shared/.env`) and
persists across releases. Start from `.env.example`. Key values:

| Variable | Purpose |
|---|---|
| `APP_ENV=production`, `APP_DEBUG=false` | Production mode |
| `APP_KEY` | `php artisan key:generate` once |
| `APP_URL` | Public URL; **must be reachable by the queue worker** (signed PDF render URL) |
| `DB_*` | Database connection |
| `QUEUE_CONNECTION=redis` | PDF work runs on Redis/Horizon |
| `REDIS_CLIENT`, `REDIS_HOST`, `REDIS_PORT`, `REDIS_PASSWORD` | Redis connection (`predis` shipped; use `phpredis` for throughput) |
| `REDIS_QUEUE_RETRY_AFTER=300` | Must exceed the Horizon supervisor timeout (240) — prevents duplicate renders |
| `HORIZON_ADMINS` | Extra emails allowed to open `/horizon`. App admins (`is_admin`) already have access — this is for ops staff with no app account |
| `PUPPETEER_EXECUTABLE_PATH` | Path to Chrome, e.g. `/usr/bin/google-chrome-stable` (empty → bundled Chromium) |
| `PUPPETEER_TIMEOUT=180` | Render ceiling (< worker timeout) |
| `SENTRY_LARAVEL_DSN` | Sentry project DSN (empty disables reporting) |
| `SENTRY_TRACES_SAMPLE_RATE=0.2` | Performance trace sampling |
| `SENTRY_SEND_DEFAULT_PII=false` | Keep PII out of error reports |
| `LOG_CHANNEL=stack`, `LOG_STACK=daily`, `LOG_DAILY_DAYS=30`, `LOG_LEVEL=info` | Rotated logs |
| `MAIL_MAILER=smtp`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME` | Outgoing mail. The app's **only** live email is the password-reset link — leave `MAIL_MAILER=log` and users cannot reset their own passwords. TLS follows the port (465 implicit, else auto-STARTTLS). Setup, worked examples, and troubleshooting: [`docs/mail.md`](docs/mail.md). |
| `SFTP_HOST`, `SFTP_PORT`, `SFTP_USERNAME`, `SFTP_PASSWORD` / `SFTP_PRIVATE_KEY` (+`SFTP_PASSPHRASE`), `SFTP_ROOT` | Monthly fund-data feed. `funds:sync-data` (scheduled daily, 05:00) mirrors remote `YYYY-MM/{fund_code}/` xlsx exports to `storage/app/private/fund-data/`. Leave `SFTP_HOST` empty to disable the sync. Test with `php artisan funds:sync-data --dry-run`. Setup, import workflow, and troubleshooting: [`docs/sftp-data-feed.md`](docs/sftp-data-feed.md). |

**Never commit secrets.** All of the above are env-only; `.env.example`
documents every key with safe placeholders.

### Timeout layering (do not break)

```
proc timeout (PUPPETEER_TIMEOUT 180)
  < job timeout (GenerateFundPdfJob::$timeout 200)
    < Horizon supervisor timeout (config/horizon.php 240)
      < REDIS_QUEUE_RETRY_AFTER (300)
```

A slow render must never be re-queued while still running (would spawn duplicate
Chrome processes).

---

## 4. Deploy

### Automated (normal path)

1. Merge to `main`.
2. `ci.yml` runs (Pint, PHPStan, tests on PHP 8.2/8.3, asset build, `composer
   audit`, `npm audit`).
3. On CI success, `deploy.yml` runs Deployer over SSH into the `production`
   GitHub Environment (optionally gated by a manual approval).

Deployer performs, per release: `composer install --no-dev --optimize-autoloader`
→ `npm ci && npm run build` → provision Chromium → `php artisan migrate --force`
→ cache config/routes/views/events → **atomic symlink switch** → `/up`
health-check (auto-rollback on failure) → `php artisan horizon:terminate`.

### Manual deploy

```bash
# Requires the DEPLOY_* env vars and SSH access set locally.
vendor/bin/dep deploy production -v
```

### Required GitHub secrets (repo → Settings → Secrets → Actions, production env)

`SSH_PRIVATE_KEY`, `SSH_KNOWN_HOSTS`, `DEPLOY_HOST`, `DEPLOY_USER`,
`DEPLOY_PATH`, `DEPLOY_REPOSITORY`, `DEPLOY_HEALTHCHECK_URL` (e.g.
`https://your-domain/up`).

---

## 5. Rollback

Atomic — re-points `current` to the previous release (no rebuild):

```bash
vendor/bin/dep rollback production
```

The post-switch health check also triggers an **automatic** rollback if the new
release fails `/up`. To roll back a bad migration specifically:

```bash
ssh deploy@host 'cd /var/www/fund-manager/current && php artisan migrate:rollback --force'
```

---

## 6. Puppeteer / Chrome provisioning

**Recommended:** install system Chrome and point the app at it.

```bash
# Ubuntu — Chrome runtime libraries
sudo apt-get install -y ca-certificates fonts-liberation libasound2t64 \
  libatk-bridge2.0-0 libatk1.0-0 libc6 libcairo2 libcups2 libdbus-1-3 \
  libexpat1 libfontconfig1 libgbm1 libglib2.0-0 libgtk-3-0 libnspr4 libnss3 \
  libpango-1.0-0 libx11-6 libxcomposite1 libxdamage1 libxext6 libxfixes3 \
  libxrandr2 xdg-utils
# Install Google Chrome stable (.deb) then:
#   PUPPETEER_EXECUTABLE_PATH=/usr/bin/google-chrome-stable
```

**Fallback:** leave `PUPPETEER_EXECUTABLE_PATH` empty — the deploy runs
`npx puppeteer browsers install chrome` to provision a bundled Chromium.

---

## 7. Queue workers (Horizon)

Horizon runs the PDF render jobs. Keep it alive with Supervisor
(sample: `deploy/supervisor/fund-manager-horizon.conf`):

```bash
sudo cp deploy/supervisor/fund-manager-horizon.conf /etc/supervisor/conf.d/
sudo supervisorctl reread && sudo supervisorctl update
sudo supervisorctl status
```

Deploys call `php artisan horizon:terminate`; Supervisor restarts Horizon on the
new code. Dashboard: `/horizon` (restricted to app admins plus `HORIZON_ADMINS`
— see `docs/users.md`).

Ops commands:

```bash
php artisan horizon:status          # master status
php artisan queue:failed            # list failed jobs
php artisan queue:retry all         # retry failed jobs
```

### Scheduler

Add the Laravel scheduler cron (runs `horizon:snapshot`, `queue:prune-failed`,
`funds:prune-pdf-exports`, and the 05:00 `funds:sync-data` data-feed mirror —
see [`docs/sftp-data-feed.md`](docs/sftp-data-feed.md)):

```cron
* * * * * cd /var/www/fund-manager/current && php artisan schedule:run >> /dev/null 2>&1
```

---

## 8. Monitoring

- **Errors:** Sentry receives web + queued-job exceptions. Failed PDF jobs are
  logged with `fund_id`, `user_id`, and `template` context and recorded on the
  `fund_pdf_exports` row (`status=failed`, `error`).
- **Uptime:** `GET /up` returns 200 when the app boots — point your uptime
  monitor here. The deploy also gates on it.
- **Queue health:** `/horizon` shows throughput, wait times, and failures.
  Horizon fires `LongWaitDetected` past the `waits` threshold
  (`config/horizon.php`), but **no notification routing is currently wired** —
  the `Horizon::routeMailNotificationsTo()` / `routeSlackNotificationsTo()` calls
  in `HorizonServiceProvider::boot` are commented out. Uncomment one (mail needs
  working SMTP first — see [`docs/mail.md`](docs/mail.md)) to get alerts.
- **Logs:** daily-rotated under `storage/logs`, retained 30 days.

---

## 9. Dependency updates

- **Dependabot** opens weekly grouped PRs for Composer, npm, and GitHub Actions
  (`.github/dependabot.yml`).
- Every PR runs CI, which **fails on known vulnerabilities** via `composer audit`
  and `npm audit --audit-level=high`.
- Lock files (`composer.lock`, `package-lock.json`) are committed; merge
  Dependabot PRs once CI is green.
- Ad-hoc audit: `composer audit` / `npm audit`.

---

## 10. First deploy checklist

- [ ] Server prerequisites installed (§2), Chrome + libs (§6)
- [ ] Redis + MySQL running
- [ ] `shared/.env` created from `.env.example`, `APP_KEY` generated, all §3 vars set
- [ ] GitHub production-environment secrets set (§4)
- [ ] `vendor/bin/dep deploy production` succeeds; `/up` returns 200
- [ ] Supervisor running Horizon (§7); `/horizon` reachable by an admin
- [ ] Scheduler cron installed (§7)
- [ ] Sentry DSN set; trigger a test error and confirm it lands
- [ ] SMTP configured (`docs/mail.md` §4); test email sent and received (§6), and
      `/forgot-password` delivers a working reset link
- [ ] First admin created — `php artisan user:create "Name" name@foord.co.za --admin`
      (there is no public sign-up; see `docs/users.md` §2), then add the rest of
      the team from the in-app Users screen
- [ ] Export a fact sheet end-to-end and confirm the PDF downloads
