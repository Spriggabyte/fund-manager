# Staging — Hosting the App on a Linux Server

How to stand up a staging copy of Fund Manager on Ubuntu 24.04 so a branch can
be reviewed before it reaches production.

Companion to [`DEPLOYMENT.md`](../DEPLOYMENT.md) (the production runbook). This
document does not repeat what is already there — it covers the one-time server
build, the staging-specific configuration, and **the four things that silently
break a staging box** (§9). Where production and staging are identical, it
points back rather than duplicating.

---

## 0. Before you start

Staging deploys **from git**, not from your working tree. Anything uncommitted
stays on your laptop.

```bash
git status --short          # must be clean, or you are shipping stale code
git push origin <branch>
```

Note the branch name — it becomes `STAGING_BRANCH` in §7.

You will need:

- A Linux host (this guide assumes **Ubuntu 24.04 LTS**) with sudo and 4 GB RAM.
  Headless Chrome is the memory hog; 2 GB will OOM under concurrent renders.
- ~20 GB disk. Each release carries its own `node_modules` (puppeteer and its
  bundled Chromium dominate), which is why staging keeps only 2 releases.
- A DNS `A` record for `staging.<your-domain>` pointing at the host.
- **Outbound HTTPS from the server** — see §9.2. This is not optional; PDFs
  render incorrectly without it.

### Run order

The sections are grouped by topic, not by execution order — a few have
dependencies that are easy to trip over. Work through them in this order:

| | Step | Why here |
|---|---|---|
| 1 | §2 Provision packages, user, directory | Nothing works without it |
| 2 | §3 Create the database | Needed before the deploy runs `migrate` |
| 3 | §4.1–4.2 Dump dev, import to staging | Only needs MySQL |
| 4 | §5 Write `shared/.env` **by hand** | Deployer symlinks it; the first deploy reads it |
| 5 | §6 nginx + basic auth + TLS | **Before** the deploy — its health check needs a web server answering |
| 6 | §7 First deploy | Puts code on the box |
| 7 | §4.2 `migrate` + §4.3 scrub | Needs `artisan`, so only possible now |
| 8 | §8 Horizon + scheduler | Needs the release path to exist |
| 9 | §10 Smoke test | Proves the lot |

Steps 4, 5 and 7 are the ones people get wrong: the env file and the web server
must both exist *before* the first deploy, and the data scrub can only happen
*after* it.

---

## 1. What staging is (and is not)

Same stack as production — nginx + PHP-FPM, MySQL, Redis + Horizon, Puppeteer
driving Chrome. The differences are deliberate:

| | Production | Staging |
|---|---|---|
| Deploy path | `/var/www/fund-manager` | `/var/www/fund-manager-staging` |
| Releases kept | 5 | 2 (disk) |
| Branch | `main` | whatever is under review |
| Access | app login | app login **behind HTTP basic auth** |
| `APP_ENV` | `production` | `staging` |
| `APP_DEBUG` | `false` | `false` — keep it false, stack traces leak config |
| Data feed | live SFTP | **disabled** (`SFTP_HOST` empty) |
| Mail | SMTP | `log` — nothing leaves the box |
| Sentry | live project | empty DSN, or a separate staging project |
| Data | real | a scrubbed dump (§4) |

Staging is **not** a rehearsal for the SFTP feed or for outgoing mail. Both are
deliberately inert so a staging box can never mail a real user or authenticate
against the provider's SFTP server. Test those in production or against a
throwaway account.

---

## 2. Provision the server (one-time)

### 2.1 Base packages

Ubuntu 24.04 ships PHP 8.3, which satisfies the app's `^8.2` requirement and is
one of the two versions CI tests — no third-party PPA needed.

```bash
sudo apt-get update
sudo apt-get install -y \
  nginx mysql-server redis-server supervisor certbot python3-certbot-nginx \
  git unzip curl apache2-utils \
  php8.3-fpm php8.3-cli php8.3-mbstring php8.3-dom php8.3-xml php8.3-curl \
  php8.3-mysql php8.3-sqlite3 php8.3-gd php8.3-zip php8.3-bcmath php8.3-intl
```

Why these extensions: `gd` + `zip` for phpspreadsheet (xlsx import) and
intervention/image; `intl` and `bcmath` for number formatting on the fact
sheets; `sqlite3` because the test suite uses it; `pcntl` ships enabled in the
CLI SAPI and Horizon needs it — confirm with `php -m | grep pcntl`.

`predis` is pure PHP, so no `php8.3-redis` extension is required. Install it
later if staging ever needs production-grade queue throughput.

### 2.2 Composer

```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### 2.3 Node 22 — install it system-wide

**Install from NodeSource, not nvm.** The PDF renderer shells out with the bare
command `node -e …`, so `node` has to be on the `PATH` of the `www-data` user
that Supervisor runs Horizon as. An nvm install lives in a login shell's
profile and is invisible there — see §9.3.

```bash
curl -fsSL https://deb.nodesource.com/setup_22.x | sudo -E bash -
sudo apt-get install -y nodejs

# Must print /usr/bin/node — anything else and PDF exports will fail.
sudo -u www-data which node
```

### 2.4 Chrome

Install system Chrome and point the app at it, rather than relying on the
Chromium that Puppeteer downloads per release.

```bash
# Chrome runtime libraries (24.04 names — note libasound2t64, not libasound2)
sudo apt-get install -y ca-certificates fonts-liberation libasound2t64 \
  libatk-bridge2.0-0 libatk1.0-0 libc6 libcairo2 libcups2 libdbus-1-3 \
  libexpat1 libfontconfig1 libgbm1 libglib2.0-0 libgtk-3-0 libnspr4 libnss3 \
  libpango-1.0-0 libx11-6 libxcomposite1 libxdamage1 libxext6 libxfixes3 \
  libxrandr2 xdg-utils

wget https://dl.google.com/linux/direct/google-chrome-stable_current_amd64.deb
sudo apt-get install -y ./google-chrome-stable_current_amd64.deb

google-chrome-stable --version    # sanity check
```

This binary becomes `PUPPETEER_EXECUTABLE_PATH=/usr/bin/google-chrome-stable`
in §5.

### 2.5 Deploy user and directory

```bash
sudo adduser --disabled-password --gecos "" deploy
sudo usermod -aG www-data deploy
sudo mkdir -p /var/www/fund-manager-staging
sudo chown deploy:www-data /var/www/fund-manager-staging
sudo chmod 2775 /var/www/fund-manager-staging

# Authorise the key you will deploy with
sudo -u deploy mkdir -p /home/deploy/.ssh
sudo -u deploy tee -a /home/deploy/.ssh/authorized_keys < ~/.ssh/id_ed25519.pub
sudo -u deploy chmod 700 /home/deploy/.ssh
sudo -u deploy chmod 600 /home/deploy/.ssh/authorized_keys
```

The server also needs read access to the GitHub repo — either add a deploy key
to `Spriggabyte/fund-manager` for `deploy@staging`, or forward your agent
(`ssh -A`) when running the deploy.

---

## 3. Database

```bash
sudo mysql -e "CREATE DATABASE foord_staging CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
sudo mysql -e "CREATE USER 'foord_staging'@'localhost' IDENTIFIED BY '<strong-password>';"
sudo mysql -e "GRANT ALL PRIVILEGES ON foord_staging.* TO 'foord_staging'@'localhost';"
sudo mysql -e "FLUSH PRIVILEGES;"
```

---

## 4. Seed staging with data, then scrub it

This section straddles the first deploy. **4.1 and 4.2's import** need only the
database from §3 and can be done now. **The `migrate` and the whole of 4.3**
run `artisan`, so they need code on the box — come back here once §7 has
completed. The full run order is at the end of §0.

### 4.1 Take a fresh dump — do not use the committed `foord.sql`

The `foord.sql` at the repo root is a **September 2025 snapshot** and records
only 5 of the current 13 migrations. Migrating on top of it works (every newer
migration is additive and nullable or defaulted), but the columns those
migrations add — `fund_code`, `class_code`, the structured/sector/international
fund fields — come out empty, so the equity, flexible and international
templates render half-blank. Dump the current dev database instead:

```bash
# On your machine
mysqldump --single-transaction --routines foord | gzip > foord-staging.sql.gz
scp foord-staging.sql.gz deploy@staging.example.com:~
```

### 4.2 Import, then migrate

```bash
# On the server
gunzip -c ~/foord-staging.sql.gz | mysql -u foord_staging -p foord_staging
```

Bring the schema up to date after the first deploy (§7) has put the code in
place:

```bash
cd /var/www/fund-manager-staging/current
php artisan migrate --force
```

### 4.3 Scrub

Real password hashes and sessions must not sit on a review box.

```bash
cd /var/www/fund-manager-staging/current

# Drop every session, queued job and cached value carried over in the dump
php artisan tinker --execute="DB::table('sessions')->truncate(); \
  DB::table('cache')->truncate(); DB::table('jobs')->truncate(); \
  DB::table('failed_jobs')->truncate(); \
  DB::table('password_reset_tokens')->truncate();"

# Revoke inherited access, then create one known staging admin
php artisan tinker --execute="App\Models\User::query()->update([ \
  'is_admin' => false, 'disabled_at' => now()]);"
php artisan user:create "Staging Admin" staging@foord.co.za --admin --no-force-change

php artisan user:list      # confirm exactly one enabled admin
```

`user:create` prompts for the password (there is no public sign-up). Add
`--password=…` to script it. To re-open an inherited account instead, use
`php artisan user:password <email>`. See [`users.md`](users.md).

---

## 5. `shared/.env`

Deployer keeps `.env` in `{deploy_path}/shared/` so it survives releases. Create
it **before** the first deploy:

```bash
sudo -u deploy mkdir -p /var/www/fund-manager-staging/shared
sudo -u deploy nano /var/www/fund-manager-staging/shared/.env
```

Start from [`.env.example`](../.env.example) and set:

| Variable | Staging value | Why |
|---|---|---|
| `APP_NAME` | `"Fund Manager (Staging)"` | Visible in the UI — makes it obvious which box you are on |
| `APP_ENV` | `staging` | |
| `APP_DEBUG` | `false` | Debug pages leak env values; a review box is not private |
| `APP_KEY` | `base64:$(openssl rand -base64 32)` | Generate it **now**, by hand — the first deploy runs `migrate` and `config:cache` against this file, and a fresh key here means there is no chicken-and-egg with `key:generate`. Never reuse production's |
| `APP_URL` | `https://staging.example.com` | **Must match the public hostname exactly** — signed PDF-render URLs are built from it (§9.1) |
| `DB_CONNECTION` | `mysql` | |
| `DB_DATABASE` / `DB_USERNAME` / `DB_PASSWORD` | from §3 | |
| `QUEUE_CONNECTION` | `redis` | PDF renders are queued work |
| `REDIS_CLIENT` | `predis` | No PHP extension needed |
| `REDIS_PREFIX` | `fund_manager_staging_database_` | Matters **only if Redis is shared with production**, where identical prefixes would let the two environments read each other's queues. Both this and `HORIZON_PREFIX` default to a slug of `APP_NAME`, so the distinct `APP_NAME` above already separates them — set them explicitly anyway if a shared Redis is in play, rather than relying on a name nobody realises is load-bearing |
| `HORIZON_PREFIX` | `fund_manager_staging_horizon:` | Same reason |
| `REDIS_QUEUE_RETRY_AFTER` | `300` | Part of the timeout ladder — see §9.4 |
| `PUPPETEER_EXECUTABLE_PATH` | `/usr/bin/google-chrome-stable` | From §2.4 |
| `PUPPETEER_TIMEOUT` | `180` | Timeout ladder |
| `HORIZON_ADMINS` | reviewer emails, comma-separated | Grants `/horizon` without an app admin account |
| `SFTP_HOST` | *(empty)* | Disables `funds:sync-data`. **Never copy the dev SFTP block** — it holds live Foord credentials |
| `MAIL_MAILER` | `log` | Staging must not email real users; resets land in `storage/logs` |
| `SENTRY_LARAVEL_DSN` | *(empty)* | Or a separate staging project — do not pollute production's issue feed |
| `LOG_LEVEL` | `debug` | Fine here; production uses `info` |

```bash
chmod 640 /var/www/fund-manager-staging/shared/.env
```

---

## 6. nginx, TLS and basic auth

**Do this before the first deploy.** Deployer health-checks `/up` after the
symlink switch and rolls back if it fails — with no web server there is nothing
to answer, and on a first deploy there is no previous release to roll back to.
The web server has to be listening first.

That means sourcing the conf from your **local checkout**, not from the server
(nothing is deployed there yet):

```bash
# From your machine
scp deploy/nginx/fund-manager-staging.conf \
    deploy@staging.example.com:/tmp/fund-manager-staging.conf
```

```bash
# On the server
sudo mv /tmp/fund-manager-staging.conf /etc/nginx/sites-available/fund-manager-staging
sudo nano /etc/nginx/sites-available/fund-manager-staging   # set server_name
sudo ln -s /etc/nginx/sites-available/fund-manager-staging /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default

sudo htpasswd -c /etc/nginx/.htpasswd-staging foord     # prompts for a password
sudo mkdir -p /var/www/html                             # ACME challenge root
sudo nginx -t && sudo systemctl reload nginx
```

nginx starts happily with a document root that does not exist yet — it will
serve 404/502 until the first release lands, which is fine. Now issue the
certificate:

```bash
sudo certbot --nginx -d staging.example.com
```

Certbot rewrites the file to add the `listen 443` and `ssl_certificate` lines.

The conf gates the whole site behind basic auth with **three exemptions**, all
load-bearing:

- `location ^~ /internal/` — the route headless Chrome fetches to render a fact
  sheet. Without the exemption every PDF export fails with a 401. It stays safe
  because Laravel's `signed` middleware already rejects unsigned requests. See
  §9.1.
- `location = /up` — the health endpoint Deployer curls after the symlink
  switch. It carries no credentials and returns nothing sensitive.
- `location ^~ /.well-known/acme-challenge/` — Let's Encrypt fetches this
  anonymously to prove domain control. Behind basic auth, certbot cannot issue
  a certificate, and the unattended **renewal** fails silently 60 days later.

### Resolve the app's own hostname locally

The queue worker fetches `APP_URL` over the network. Send it straight back to
nginx instead of out through the firewall and back (many hosts do not support
NAT hairpinning, and the request simply times out):

```bash
echo "127.0.0.1 staging.example.com" | sudo tee -a /etc/hosts
```

### Firewall

Allow inbound 80/443, and **allow outbound 443** — the fact-sheet templates load
Highcharts, Chart.js, Google Fonts and the Foord logo from the public internet
at render time (§9.2).

```bash
sudo ufw allow OpenSSH && sudo ufw allow 'Nginx Full' && sudo ufw enable
```

---

## 7. Deploy

The `staging` host already exists in [`deploy.php`](../deploy.php). Point it at
your server with env vars, from your **local checkout**:

```bash
export STAGING_HOST=staging.example.com
export STAGING_USER=deploy
export STAGING_PATH=/var/www/fund-manager-staging
export STAGING_BRANCH=production-readiness      # the branch under review
export STAGING_HEALTHCHECK_URL=https://staging.example.com/up
export DEPLOY_REPOSITORY=git@github.com:Spriggabyte/fund-manager.git

vendor/bin/dep deploy staging -v
```

Per release Deployer runs: `composer install --no-dev --optimize-autoloader` →
`npm ci && npm run build` → provision Chromium → `php artisan migrate --force`
→ cache config/routes/views/events → **atomic symlink switch** → `/up`
health check → `horizon:terminate`.

The health check runs *after* the switch and **rolls back automatically** if the
new release fails it. To roll back by hand:

```bash
vendor/bin/dep rollback staging
```

First deploy only — link the public storage directory:

```bash
ssh deploy@staging.example.com
cd /var/www/fund-manager-staging/current
php artisan storage:link
```

`APP_KEY` is already set from §5, so there is no `key:generate` step here.

---

## 8. Horizon and the scheduler

```bash
sudo cp /var/www/fund-manager-staging/current/deploy/supervisor/fund-manager-staging-horizon.conf \
        /etc/supervisor/conf.d/
sudo supervisorctl reread && sudo supervisorctl update
sudo supervisorctl status fund-manager-staging-horizon
```

Scheduler cron (the `funds:sync-data` entry no-ops while `SFTP_HOST` is empty):

```cron
* * * * * cd /var/www/fund-manager-staging/current && php artisan schedule:run >> /dev/null 2>&1
```

Dashboard at `/horizon`, open to app admins plus `HORIZON_ADMINS`.

---

## 9. The four things that break staging

Read this section before debugging anything. These account for most first-day
failures, and none of them produce an obvious error message.

### 9.1 Basic auth blocking the PDF renderer

**Symptom:** every PDF export fails; the job error mentions a 401 or an empty
render.

The renderer builds a signed URL against `APP_URL` and the worker's Chrome
fetches it *over HTTP* — it is a real network request that hits nginx like any
other. Blanket `auth_basic` returns 401 before Laravel ever sees it.

Fixed by the `location ^~ /internal/ { auth_basic off; }` block in the supplied
nginx conf. If you wrote your own conf, add it. Verify:

```bash
curl -sI https://staging.example.com/internal/funds/1/pdf-view | head -1
# want: HTTP/2 403   (signed middleware rejecting an unsigned request)
# bad:  HTTP/2 401   (basic auth is still in front of it)
```

### 9.2 No outbound internet

**Symptom:** PDFs generate but charts are missing and the type falls back to a
system serif.

Every render pulls these from the public internet:

| Asset | Host |
|---|---|
| Highcharts 11 | `cdn.jsdelivr.net` |
| Chart.js | `cdn.jsdelivr.net` |
| Lato + Merriweather | `fonts.googleapis.com`, `fonts.gstatic.com` |
| Foord logo | `foord.co.za` |

```bash
curl -sI https://cdn.jsdelivr.net/npm/highcharts@11/highcharts.js | head -1
curl -sI https://fonts.googleapis.com | head -1
```

If the network is locked down, the fix is to vendor these assets locally rather
than to open the firewall — but that is a code change, not a staging setting.

### 9.3 `node` not on the worker's PATH

**Symptom:** PDF jobs fail immediately with `sh: 1: node: not found`.

The renderer runs `node -e <script>` via `proc_open`. Supervisor runs Horizon as
`www-data`, which does not read your login profile — so an nvm-managed Node is
invisible, even though `node -v` works fine over SSH.

```bash
sudo -u www-data which node      # must print /usr/bin/node
```

Install from NodeSource (§2.3). `NODE_PATH` is handled by the app: it defaults
to the release's `node_modules`, which `npm ci` populates on every deploy.

### 9.4 A broken timeout ladder

**Symptom:** duplicate Chrome processes, memory exhaustion, the same PDF
rendered twice.

```
PUPPETEER_TIMEOUT (180)
  < job timeout (GenerateFundPdfJob::$timeout 200)
    < Horizon supervisor timeout (config/horizon.php 240)
      < REDIS_QUEUE_RETRY_AFTER (300)
```

A slow render must never be released back to the queue while it is still
running. If you raise `PUPPETEER_TIMEOUT` on staging to debug a slow fact sheet,
raise everything above it too.

---

## 10. Smoke test

Work through this after the first deploy. Step 4 is the one that matters — it
exercises Chrome, egress, node resolution and the queue in a single action.

- [ ] `curl -fsS https://staging.example.com/up` → 200
- [ ] `curl -sI https://staging.example.com/` → 401 without credentials, 200 with `-u foord:…`
- [ ] `curl -sI https://staging.example.com/internal/funds/1/pdf-view` → **403, not 401** (§9.1)
- [ ] Sign in as the staging admin from §4.3; `/horizon` loads and shows a running master
- [ ] **Export a fact sheet end to end** — open a fund → Export PDF → download.
      Open the PDF and confirm the charts rendered and the type is Lato /
      Merriweather, not a fallback serif
- [ ] `php artisan queue:failed` → empty
- [ ] `sudo -u www-data which node` → `/usr/bin/node`
- [ ] `vendor/bin/dep rollback staging`, confirm the site still serves, then
      re-deploy — prove rollback works before you need it

---

## 11. Routine use

```bash
# Ship the current branch
export STAGING_BRANCH=<branch> && vendor/bin/dep deploy staging -v

# Watch what happened
ssh deploy@staging.example.com 'tail -f /var/www/fund-manager-staging/shared/storage/logs/laravel.log'

# Queue state
cd /var/www/fund-manager-staging/current
php artisan horizon:status
php artisan queue:failed
php artisan queue:retry all

# Refresh staging data from a newer dev dump — repeat §4
```

Refresh the dump whenever the schema moves; a staging box running week-old data
against new fact-sheet templates produces confusing review feedback rather than
useful bug reports.
