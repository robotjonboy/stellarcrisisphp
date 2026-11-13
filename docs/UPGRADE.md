# Shared hosting: update an existing install

Use these steps to overlay a **release zip** onto a server that already has Stellar Crisis PHP and a populated database.

For a first-time install, see [INSTALL.md](INSTALL.md).  
Docker AIO operators follow the container docs in the git repo, not this zip.

## Before you start

1. **Backup** the MySQL/MariaDB database (`mysqldump` or your host’s backup tool).
2. **Backup** writable dirs you care about: `history/`, `images/aliens/custom/`.
3. Prefer a quiet window (no formal maintenance mode is required).
4. Read which schema scripts you still need (below). Do **not** run scripts that were already applied.

Uploading over FTP/SFTP can leave old `.bak` and duplicate files on the host. The zip does not delete host junk; remove stale files yourself if needed.

## Overlay the code

1. Unpack the release zip beside (or on) your build machine.
2. Copy application files onto the live tree (FTP, SFTP, `rsync`, host file manager, etc.).
3. **Do not overwrite:**
   - Live `serverconfig.php`
   - Existing `history/*.html` (and keep history assets you customized)
   - Custom icons under `images/aliens/custom/` that players uploaded
4. **Do overwrite** PHP/CSS/JS application files, `sc.sql` / `upgrade_*.sql` in the tree (for your records), and operator docs.
5. This release **includes** `utility/`. After the update, ensure it is still not web-accessible (delete, move outside docroot, or deny via `.htaccess` / panel). Same for `*.sql` and `docs/`.

## Schema upgrades

Fresh installs that imported this release’s `sc.sql` already have the current schema — skip this section.

For older databases, run only the scripts you have **not** applied yet, in the order below. Check your schema first (phpMyAdmin, `SHOW COLUMNS`, `SHOW TABLES`).

```bash
mysql -u YOUR_USER -p --database=YOUR_DATABASE < upgrade_SOME_SCRIPT.sql
```

### 1. Pre-tournament / early SC3 Jumpgate (very old servers)

If the database predates tournaments / Jumpgate / ship-type options, apply the SQL in the project README “Upgrade” section (tournaments, `game_type`, Jumpgate, message/history ENUMs, `series_ship_type_options`, `game_ship_type_options`). That material is historical and is not a separate `upgrade_*.sql` file in this release.

**Skip if** you already have a `tournament` table and Jumpgate in `ship_types`.

### 2. `upgrade_sc3.sql` — SC3 remaining features

**Run if** you lack Carrier/Builder/Morpher in `ship_types`, or series columns such as `max_ag_ratio` / `max_ships`.

Adds SC 3.0 ships and series knobs; updates history ENUM; includes jumpgate-series ship-type option backfill for first-time SC3 upgrades.

**Do not run twice** without checking — `ALTER TABLE ... ADD COLUMN` will fail if columns already exist.

### 3. `upgrade_sc3_jumpgate_series.sql` — Jumpgate-only series backfill

**Run if** you already applied the SC3 remaining-features changes earlier **without** the jumpgate-series backfill, and existing sc3 series only have Jumpgate ship-type options.

Safe to re-run. Also embedded at the end of `upgrade_sc3.sql` for first-time upgrades via that file.

### 4. `upgrade_password_security.sql` — password hashes and session tokens

**Run if** `empires.password_hash` or the `auth_tokens` table is missing (plaintext-era databases).

- Changes primary key to `(name)`; makes `password` nullable; adds `password_hash`; creates `auth_tokens`.
- Deploy the **new PHP code** in the same update (old code expects plaintext PK behavior).

**Do not run twice** without checking whether `password_hash` / `auth_tokens` already exist.

### 5. `upgrade_rss_tokens.sql` — per-empire RSS tokens

**Run if** `empires.rss_token` is missing.

Adds `rss_token` and a unique key. Existing empires can receive a token lazily on next Game List visit (see comments in the SQL file).

**Do not run twice** if the column already exists.

## After schema changes

1. Confirm `serverconfig.php` still points at the right database and that `error_log_path` remains outside the docroot ([shared-hosting-error-log.md](shared-hosting-error-log.md)).
2. Smoke-test: login, game list, one in-game page; admin → View Errors if you use the file error log.
3. If you use password hashing: players on old open tabs may still POST `pass` until they navigate once; that is expected after `upgrade_password_security.sql`.

## Checklist

- [ ] Database and writable dirs backed up
- [ ] Code overlaid without clobbering `serverconfig.php`, history HTML, or custom icons
- [ ] Only needed `upgrade_*.sql` scripts run (none re-run blindly)
- [ ] `utility/` still locked down
- [ ] Login and a live game page work
