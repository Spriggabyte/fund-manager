# SFTP Data Feed — Downloading & Importing Fund Data

How the monthly Foord Excel exports get from the provider's SFTP server into a
fund's fact sheet.

Companion to [`DEPLOYMENT.md`](../DEPLOYMENT.md) (deploy/ops) and
[`FUND-ONBOARDING.md`](../FUND-ONBOARDING.md) (setting up a *new* fund from
scratch, and what each importer writes). This document covers the recurring
monthly path: **download, then import.**

---

## 1. The two stages

They are deliberately separate. Downloading is automatic and touches nothing;
importing is a manual, per-fund decision that rewrites fund data.

```
  SFTP server                     local disk                      fund record
  {SFTP_ROOT}/                    storage/app/private/
    2026-06/                        fund-data/2026-06/
      810/810A_FACTSHEET.xlsx  ──►    810/810A_FACTSHEET.xlsx  ──►  fund 9
      811/811A_FACTSHEET.xlsx  ──►    811/811A_FACTSHEET.xlsx  ──►  fund 11

        stage 1: funds:sync-data          stage 2: import card
        (scheduled daily, 05:00)          or `php artisan fund:import`
        read-only, idempotent             snapshots a revision, then writes
```

**Stage 1 never modifies a fund.** It only mirrors files. A month appearing in
`storage/app/private/fund-data/` means the data is *available*, not applied — an
editor still has to click Import on the fund's edit page.

---

## 2. Layout and the `fund_code` mapping

**Remote** (relative to `SFTP_ROOT`): `{YYYY-MM}/{fund_code}/*.xlsx`
**Local:** `storage/app/private/fund-data/{YYYY-MM}/{fund_code}/*.xlsx`

The month folder must match `YYYY-MM` exactly. Any other top-level remote
directory — `archive/`, `incoming/`, stray files — is **ignored**, so the
provider can keep other content in the same tree safely.

Two columns join a fund record to the feed, both editable on the fund edit page:

- **`fund_code`** — the remote folder name (`810`).
- **`class_code`** — the share class within it (`A`, `B2`, `B3`, `R`, `R1`).

A folder is per fund code but its files are per **share class**, so both are
needed:

```
810/  810A_FACTSHEET.xlsx   810B2_FACTSHEET.xlsx   810B3_FACTSHEET.xlsx
      810A_PRICE_GRAPH.xlsx 810B2_PRICE_GRAPH.xlsx 810B3_PRICE_GRAPH.xlsx
      810_SA_INFLATION_GRAPH.xlsx        ← no class token: shared by all classes
```

Current mapping:

| Fund | ID | `fund_code` | `class_code` | Template |
|---|---|---|---|---|
| Foord Balanced Fund — Class A | 9 | `810` | `A` | `show` |
| Foord Balanced Fund — Class B2 | 10 | `810` | `B2` | `show` |
| Foord Balanced Fund — Class B3 | 16 | `810` | `B3` | `show` |
| Foord Equity Fund — Class A | 11 | `811` | `A` | `show-equity` |
| Foord Flexible Fund of Funds — Class A | 13 | `817` | `A` | `show-flexible` |
| Foord International Fund — Class R | 14 | `875` | `R` | `show-international` |

Seeded by the `add_fund_code_to_funds_table` and `add_class_code_to_funds_table`
migrations. The feed publishes 22 fund codes and 44 class variants; only the
funds above have a signed-off template and static text so far.

Uniqueness is on the **pair** `(fund_code, class_code)` — several funds share code
`810`, but only one can be class `A`. A fund left without a `class_code` shows a
prompt instead of the import card, because importing a multi-class folder without
one would pull in whichever class sorted last.

---

## 3. Configuration

The feed uses a Laravel `sftp` filesystem disk (`config/filesystems.php:50-61`),
backed by `league/flysystem-sftp-v3`. All values are environment-only:

| Variable | Purpose |
|---|---|
| `SFTP_HOST` | Server hostname. **Empty disables the whole feed** — the sync no-ops. |
| `SFTP_PORT` | Default `22`. |
| `SFTP_USERNAME` | Login user. |
| `SFTP_PASSWORD` | Password auth — use this *or* a key, not both. |
| `SFTP_PRIVATE_KEY` | Path to a private key file on the server (preferred). |
| `SFTP_PASSPHRASE` | Passphrase, if the key has one. |
| `SFTP_ROOT` | Remote directory holding the `YYYY-MM` folders. Empty = login directory. |

Two fixed settings worth knowing: `'timeout' => 30` (per-operation), and
`'throw' => true` — failures raise exceptions rather than returning false, which
is what lets the sync report per-file errors instead of silently skipping.

### Key-based auth (recommended)

```bash
# on the app server, as the deploy user
ssh-keygen -t ed25519 -f ~/.ssh/foord_feed -C "fund-manager@fund-manager"
chmod 600 ~/.ssh/foord_feed
cat ~/.ssh/foord_feed.pub          # send this to the feed provider
```

```dotenv
SFTP_HOST=feed.provider.example.com
SFTP_PORT=22
SFTP_USERNAME=foord-fundmanager
SFTP_PASSWORD=
SFTP_PRIVATE_KEY=/home/deploy/.ssh/foord_feed
SFTP_PASSPHRASE=
SFTP_ROOT=/exports/factsheets
```

The key must be readable by **both** the web user and the queue/scheduler user if
they differ. `SFTP_PRIVATE_KEY` is a *path*, not the key material.

### Password auth

```dotenv
SFTP_HOST=feed.provider.example.com
SFTP_USERNAME=foord-fundmanager
SFTP_PASSWORD="<password>"
SFTP_PRIVATE_KEY=
SFTP_ROOT=/exports/factsheets
```

After editing `.env` in production, rebuild the config cache — an edit alone does
nothing (same rule as [`docs/mail.md`](mail.md) §4):

```bash
cd /var/www/fund-manager/current
php artisan config:clear && php artisan config:cache
```

Confirm the credentials independently before blaming the app:

```bash
sftp -i /home/deploy/.ssh/foord_feed foord-fundmanager@feed.provider.example.com
# then: ls /exports/factsheets   → should list YYYY-MM folders
```

---

## 4. Stage 1 — downloading (`funds:sync-data`)

```bash
php artisan funds:sync-data                 # mirror every month folder
php artisan funds:sync-data --dry-run       # list what would download, write nothing
php artisan funds:sync-data --month=2026-06 # one month only
```

Output is a per-file list plus a summary line:

```
  ↓ fund-data/2026-06/810/810A_FACTSHEET.xlsx
Months seen: 2026-05, 2026-06 | Downloaded: 1 | Skipped (up to date): 7 | Errors: 0
```

**Scheduled daily at 05:00** (`routes/console.php`), `onOneServer` and
`runInBackground`, so a new month folder is picked up whenever the provider
publishes it — there is no monthly cron to adjust. This requires the scheduler
cron from `DEPLOYMENT.md` §7 to be installed.

Behaviour worth relying on:

- **Idempotent.** A file is downloaded only when it is missing locally *or* the
  remote size differs. Re-running costs a directory listing. A replaced or
  late-arriving export is picked up automatically on the next run because its
  size changed.
- **Safe when unconfigured.** With `SFTP_HOST` empty it prints
  `SFTP_HOST is not configured — nothing to sync.` and exits **0** — so the
  scheduler does not fail on a server where the feed isn't set up yet.
- **Per-file error isolation.** One unreadable file is recorded in the report and
  the rest still download. The command exits **1** if any file errored, so a
  wrapper or monitor can detect it.
- **Never deletes.** It only adds. Removing a remote month leaves the local copy
  in place.

---

## 5. Stage 2 — importing a month into a fund

### From the fund edit page (the normal path)

The **Import from data feed** card at the top of `/funds/{id}/edit` lists every
downloaded month that contains at least one `.xlsx` in *this fund's* code folder,
newest first, with the file count. Click **Import {month}** and confirm.

What happens: a revision snapshot is taken (`Before data feed import (2026-06)`),
every recognised export in that folder is imported in registry order, and the
fund is saved. The flash message names what was imported and anything skipped.

Guard rails, all covered by `tests/Feature/FundImportMonthTest.php`:

- requires the `update` policy on the fund (you can only import into your own funds)
- refuses if the fund has no `fund_code` set
- errors if the month was never downloaded
- the `{month}` route segment is constrained to `\d{4}-\d{2}`; anything else 404s
- **every import is reversible** via the revisions UI

If the card shows no months: check the fund's `fund_code` matches a remote folder
name, and that `funds:sync-data` has actually run.

### From the CLI

```bash
php artisan fund:import {fundId} storage/app/private/fund-data/2026-06/810 --dry-run
php artisan fund:import {fundId} storage/app/private/fund-data/2026-06/810
```

`--dry-run` reports the files that would be imported and the fund fields that
would change, without saving. Any directory of exports works — the downloaded
month folders are just one source (see `FUND-ONBOARDING.md` §4 for importing
straight from a `Funds/<fund>/Data` folder).

### Adding a share class

When a fund gains a class, clone an existing one rather than building it up from
nothing — the static text (sidebar prose, fees wording, important-info
paragraphs) is identical across classes and is not in the Excel exports:

```bash
php artisan fund:add-class 9 B3 --import=2026-07
```

This clones fund 9's content, names the new fund by swapping the trailing
`CLASS x` token, clears the values that must come from the class's own export
(`isin_number`, `unit_price`, `number_of_units`, `last_distributions`), and — with
`--import` — imports that month immediately. It refuses to create a
`(fund_code, class_code)` pair that already exists. `--name=` overrides the
derived name.

Afterwards, check the fields that genuinely differ per class and are **not** in
the factsheet — typically minimum investment amounts and fee-rate wording — against
that class's reference PDF.

### Which files are recognised

Selection happens in two steps: first the fund's **share class** is picked out of
the folder, then each remaining file is routed to an importer.

**Step 1 — class selection.** A file belongs to the fund when the token between
the fund code and the first underscore is either empty (shared by every class) or
equal to the fund's `class_code`. The underscore is what keeps `840B` distinct
from `840B3`, and `877R` from `877R1`:

| Folder | Class | Selected |
|---|---|---|
| 810 | `A` | `810A_FACTSHEET`, `810A_PRICE_GRAPH`, `810_SA_INFLATION_GRAPH` |
| 810 | `B2` | `810B2_FACTSHEET`, `810B2_PRICE_GRAPH`, `810_SA_INFLATION_GRAPH` |
| 840 | `B` | `840B_*` only — **not** `840B3_*` |

Other classes' files are reported as *"Ignored (other share classes)"*, kept
separate from `skipped` (which means no importer is registered — see below).
Files not named for the fund code at all are left alone, so ad-hoc directories
such as `Funds/<fund>/Data` keep working.

**Step 2 — importer routing.** `FundImportManager` routes by **substring of the
filename**, case-insensitive:

| Filename contains | Importer | Writes |
|---|---|---|
| `FACTSHEET` | `FactsheetImporter` | tables and scalars (runs **first**) |
| `PRICE_GRAPH` | `PriceGraphImporter` | `chart_data['performanceData']` |
| `INFLATION_GRAPH` | `InflationGraphImporter` | inflation chart series |
| `ALSI_GRAPH` | `AlsiGraphImporter` | `chart_data['monthlyData']` (equity) |
| `COST_REG28_GRAPH` | `CostReg28GraphImporter` | `chart_data['strategyData']` (flexible) |

Registry order is deliberate: the factsheet rewrites tables and scalars, the
graph importers only touch `chart_data`, so the factsheet must run first.

Unrecognised `.xlsx` files are **reported and skipped, never silently ignored** —
that report is the signal to add an importer. See `FUND-ONBOARDING.md` §4 for
what each importer writes and the per-fund-type quirks.

### Gotcha: the manual upload form is not the same code path

The **Import Excel Data** panel further down the edit page (four file inputs:
factsheet, price graph, inflation graph, ALSI graph) still uses the older
`ExcelImportService`, not `FundImportManager`. Consequences:

- it has **no input for `COST_REG28_GRAPH`**, so the flexible fund's Reg 28
  comparison chart cannot be updated that way
- files are routed by which input you upload them to, not by filename

Prefer the data-feed card or `fund:import` whenever the files are available; the
upload form is the fallback for funds with no `fund_code` (currently Balanced
Class B) or one-off files that never hit the feed.

---

## 6. Verifying the setup

```bash
# 1. credentials + remote layout, independent of the app
sftp -i <key> <user>@<host>          # ls should show YYYY-MM folders

# 2. what the app can see, without writing anything
php artisan funds:sync-data --dry-run

# 3. a single month for real
php artisan funds:sync-data --month=2026-07
find storage/app/private/fund-data -name '*.xlsx' | head

# 4. preview an import — reports changed fields, saves nothing
php artisan fund:import 9 storage/app/private/fund-data/2026-07/810 --dry-run
```

Step 4 is also the class check: it must list only this fund's class plus the
shared graphs, with the rest under *"Ignored (other share classes)"*.

Then open the fund's edit page and confirm the month appears in the import card.
Finally import it and export the PDF to confirm the new figures render.

Monthly routine once configured: the sync runs itself; an editor opens each fund,
clicks Import for the new month, checks the fact sheet, and exports.

---

## 7. Troubleshooting

| Symptom | Cause / fix |
|---|---|
| `SFTP_HOST is not configured — nothing to sync.` | The feed is disabled. Set `SFTP_HOST` (and rebuild the config cache). |
| Command succeeds, `Months seen: (none)` | Connected, but `SFTP_ROOT` points at the wrong directory, or no folder matches `YYYY-MM`. Check with `sftp` directly. |
| `Connection refused` / timeouts | Host, port, or firewall. The disk timeout is 30s per operation. |
| Authentication failures | Key path wrong or unreadable by the scheduler user; `SFTP_PRIVATE_KEY` must be a **path**. Set either password or key, not both. |
| Files download but no import card appears | The fund's `fund_code` doesn't match the remote folder name, or the folder holds no `.xlsx`. |
| Card says to set a **Class Code** | The fund has `fund_code` but no `class_code`. Set it to the class token in the filenames (`A`, `B2`, `B3`, `R`). |
| "Set this fund's Fund Code before importing" | The fund has no `fund_code` — set it on the edit page. |
| A fund shows another class's price/ISIN/TER | Its `class_code` is wrong or missing. Fix it, re-import, or restore the revision taken before the import. |
| File count on the card looks low | Correct — the count is per class, not per folder. Class A of 810 is 3 files, not 7. |
| "No recognised export files found" | Filenames don't contain a known token (§5). The skipped list names them. |
| A month imports but a chart doesn't change | That export wasn't in the folder, or no importer is registered for it — check the skipped list. |
| Re-running downloads everything again | Local file sizes differ from remote — usually a partial earlier download. Harmless; it self-heals. |
| Wrong month imported | Restore the fund from the revision taken immediately before the import. |

Errors are written to `storage/logs/`. Sentry captures exceptions from scheduled
commands when `SENTRY_LARAVEL_DSN` is set (`bootstrap/app.php:20`).

---

## 8. Known gaps

- **No alerting when the sync fails.** A failed run exits 1 and logs, but nothing
  emails or notifies anyone — a silently stalled feed shows up only as a missing
  month in the import card. `routes/console.php` uses no
  `->emailOutputOnFailure()`. See [`docs/mail.md`](mail.md) §8; SMTP must work
  first.
- **Downloaded data is never pruned.** `funds:prune-pdf-exports` handles PDFs;
  nothing removes old `fund-data/YYYY-MM` folders. The exports are small, but
  storage grows by one folder per month indefinitely.
- **No host-key verification** beyond what the SFTP client does by default —
  there is no pinned `hostFingerprint` in the disk config.
- **The manual upload form is class-unaware** as well as unable to import
  `COST_REG28_GRAPH` (§5) — you pick the files, so you also pick the class.
- **Only 6 of the feed's 44 fund/class variants exist as records.** Adding one is
  `fund:add-class` (§5) plus the static text from its reference PDF
  (`FUND-ONBOARDING.md` §3).
- **Nothing verifies the export is for the month it sits in.** The folder name is
  trusted; the factsheet's own `MONTH_END_DATE` is imported but not cross-checked
  against the folder.
