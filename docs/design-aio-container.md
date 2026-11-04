# Design: All-in-One Container

## Problem

A playable Stellar Crisis PHP server is a small LAMP stack, but the install is still a pile of manual steps:

1. Provision Apache, PHP, and MySQL/MariaDB
2. Create a database and import `sc.sql`
3. Copy the tree into a web-served directory
4. Edit `serverconfig.php` (paths, DB credentials, server name, timezone)
5. Insert an admin empire with a hashed password
6. Keep `utility/` off the public web

That is fine on long-lived shared hosting (Homeserve, Rocket Empires). It is a poor fit for a new public instance, a laptop test server, a LAN night, or “clone this and play.” There is no image, no compose file, and no first-boot story.

## Goal

Ship **one container** that starts as a complete, empty Stellar Crisis server: web UI, PHP runtime, database, schema, and admin account.

A new operator should be able to:

1. Pull or build the image
2. Set a handful of environment variables (server name, admin password, published port)
3. Start one container with one volume
4. Open a browser, log in as admin, create a series, and play

Deliverable of this document: design for that all-in-one (AIO) container. **No code in this phase.**

## Non-goals

- Do not rewrite the game, move off form-POST PHP, or split the app into services.
- Do not replace the existing shared-hosting servers (Homeserve / Ronin, Rocket Empires) with this image. Those stay as they are.
- Do not require Kubernetes, Swarm, or a multi-container compose of Apache + PHP + MariaDB as the primary story. Compose may wrap the **single** image for volumes and ports.
- Do not include phpMyAdmin, a wiki, `sc_room`, or a discussion board in v1.
- Do not convert MyISAM tables to InnoDB, change charset from `latin1`, or “modernize” SQL mode as part of packaging.
- Do not bake this tree’s live `serverconfig.php`, player data, `sql backups/`, or `history/*.html` into the image.
- Do not implement HTTPS inside the container. TLS belongs on a reverse proxy in front, if needed.
- Do not add a REST API, installer GUI, or in-game “you are running in Docker” banner.
- Do not auto-seed a catalog of series. Empty schema plus one admin empire is enough; series stay an admin action.
- Do not add a keepalive, cron, or CLI updater that ticks games while nobody is browsing. Updates stay on-request, as they are today.
- Do not change game rules or PHP application behavior except the minimum packaging hooks listed under “Application changes.”

## Background: how the server actually runs

Stellar Crisis PHP is not a long-running game daemon. Apache serves `sc.php`; that script is the front controller. Almost every player action is a POST back to `sc.php`.

```
Browser  --POST sc.php-->  Apache/PHP
                              │
                              ├─ checkForUpdates()          (every non-admin request)
                              ├─ checkForTournamentUpdates()
                              ├─ auth_run_gate()
                              └─ section handler (login / main / game / admin)
                                        │
                                        └─ mysqli  -->  MariaDB/MySQL
```

Relevant facts for packaging:

| Item | Reality |
|------|---------|
| Web | Apache; entry point `sc.php`. `index.html` meta-refreshes to `/sc.php`. |
| PHP | 8.2-class runtime (`password_hash` / `password_verify`). Short open tags (`<? echo`) are used in many templates, so `short_open_tag=On` is required. `sc.php` sets `memory_limit=128M` and `set_time_limit(0)`. |
| DB | `mysqli` only. Schema and seed live in `sc.sql` (current: password hashes, `auth_tokens`, SC3 ship types and series columns). Engine is **MyISAM**, charset **latin1**. Code issues `BEGIN`/`COMMIT`; MyISAM ignores that. |
| Fresh DB | Tables plus `ship_types` and the `words` dictionary. **No empires, no series, no games.** |
| Updates | `checkForUpdates()` in `sc.php` runs on ordinary requests (including a GET of the login page). There is no CLI updater and no cron in-tree. If nobody is playing, nothing updates — that is intended. |
| Writes | `history/` (end-of-game HTML), `images/aliens/custom/` (icon uploads), `errorlog.html` (when `error_log_type=file`). |
| Secrets | `serverconfig.php` holds DB user/password, paths, and server identity. The copy in this tree is host-specific (`Ronin`, `/~jonboy/sc/sc.php`). |
| Mail | Optional. `require_valid_email` defaults false; `mail()` is used for empire-creation validation, email-change, and optional error reports. Play does not need SMTP. |
| Must stay private | `utility/`, `serverconfig.php`, `*.sql`, `docs/`, `reference/`, `sql backups/`. There is no `.htaccess` today; shared hosting often relies on not putting those under the docroot, or on luck. |
| External links | FAQ, wiki, and `sc_room` URLs in config. They are not part of this codebase. |

PHP extensions actually used: `mysqli`, `openssl` (bcrypt), and `mail()` if those features are on. Icon upload is a GIF file copy; no GD.

## Terminology

| Term | Meaning |
|------|---------|
| **AIO container** | One OCI image, one running container, all processes inside it (web + PHP + database). |
| **Volume** | A named Docker/Podman volume (or bind mount) that outlives the container. Game state lives here. |
| **First boot** | The first start on an empty volume: initialize MariaDB, import `sc.sql`, write config, create the admin empire. |
| **Operator** | The person who starts the container. Distinct from the in-game admin empire. |
| **Application changes** | Tiny, packaging-oriented tweaks to this repo (Apache snippet, config template, `.dockerignore`). Not gameplay work. |

## Design principles

1. **One container is the product.** `docker run` / `podman run` with a volume and a port is sufficient. Compose is sugar, not architecture.
2. **Data outlives the image.** Rebuilding or replacing the image must not wipe empires, games, history files, or custom icons.
3. **Config from the environment.** Do not copy this host’s `serverconfig.php` into the image. Generate config at start from env vars plus defaults.
4. **Match the app, don’t “fix” it.** PHP 8.2+, Apache, MariaDB, MyISAM, latin1, short open tags, permissive SQL mode. Packaging is the wrong place to fight the 2019-era schema.
5. **Empty but playable.** After first boot the operator can log in as admin. They still create series through the existing admin UI.
6. **Secure the tree the hosting README already warns about.** Deny web access to `utility/` and secrets even if someone bind-mounts the full repo.

## Proposed shape

### Image

One Dockerfile at the repo root (or `docker/Dockerfile` with the build context at the repo root).

**Base:** Debian stable. That matches “now running on Debian Linux” in `footer.php` and keeps `apt` packages boring. Do not use Ubuntu.

**Inside the image:**

- Apache 2.4 + `mod_php` 8.2 or 8.3 (one less process than php-fpm; closest to shared hosting)
- MariaDB server (10.11+ or 11.x; MyISAM still required)
- A process supervisor (recommended: **s6-overlay**; acceptable: supervisord)
- `curl` (or equivalent) for the healthcheck
- App tree copied into the docroot
- First-boot and config-render scripts
- Apache vhost that sets the PHP flags this app needs

**Not in the image:** live DB files, generated history, uploaded icons, operator secrets.

**`.dockerignore`:** `.git`, `history/*.html` (keep `history/` as a directory), `images/aliens/custom/*` (keep a `.gitkeep`), `sql backups/`, `errorlog.html`, `debug_data.html`, agent/cursor junk, the host `serverconfig.php`.

### Why not three containers?

A compose stack of `web` + `php` + `db` is the textbook Docker layout. It is also three images, a network, and a `serverconfig` host name of `db` instead of `localhost`. The point of AIO is that a player who has never run MariaDB can still host a game. One volume, one port, one restart policy.

The usual objections to co-locating a database (independent scaling, separate lifecycle) do not apply to a turn-based game that tops out at a few dozen concurrent browsers.

### Process model

Supervisor starts, in order:

1. **MariaDB** — wait until it accepts connections on localhost
2. **First-boot / config** — oneshot: init datadir if empty, import schema if empty, render `serverconfig.php`, ensure admin empire
3. **Apache** — foreground-equivalent under the supervisor

PID 1 is the supervisor, not Apache. Signals (`docker stop`) must stop Apache and MariaDB cleanly so InnoDB/ARIA recovery is not the issue — and so MyISAM tables are flushed.

```
supervisor (PID 1)
 ├── mariadbd
 └── apache2
```

Game updates stay on the existing web path: a player (or admin) request to `sc.php` runs `checkForUpdates()`. The container does not poll itself.

### Filesystem and volume

One volume mounted at `/data`:

```
/data
  mysql/          MariaDB datadir
  history/        generated game-history HTML
  icons/          uploaded GIFs (images/aliens/custom)
  logs/           errorlog.html and Apache/PHP logs if we keep them here
  config/         rendered serverconfig.php (optional; see config section)
```

Inside the container, the app lives at a fixed path, e.g. `/var/www/sc`. Writable locations are **not** left as anonymous layers:

| App path | Backed by |
|----------|-----------|
| MariaDB datadir | `/data/mysql` |
| `history/` | bind/symlink to `/data/history` |
| `images/aliens/custom/` | bind/symlink to `/data/icons` |
| `errorlog.html` | `/data/logs/errorlog.html` or config `error_log_path` pointed there |

Code stays in the image. Operators who want to hack PHP bind-mount the working tree over `/var/www/sc` (dev override) and still keep `/data` for state.

Permissions: Apache worker user (`www-data`) must own history, icons, and the error log. MariaDB uses its own user on `/data/mysql`. First-boot chowns these if the volume was created as root.

### HTTP layout

Serve the game at **`/`** by default:

- `DirectoryIndex sc.php`
- `http://host:8080/sc.php` is the login/front controller
- Static files (`styles.css`, `sc.js`, `images/`, `history/`) are siblings, as they are in this tree

`$server['app_path']` becomes `/sc.php`. `$server['history_read_URL']` is derived from `SC_PUBLIC_URL` (e.g. `http://localhost:8080/history/`).

Known path mismatches in the current tree that the container vhost or a tiny app tweak should neutralize:

- `index.html` refreshes to `/sc.php` (good for `/` layout; wrong if someone publishes under a subpath)
- `standardHeader` shortcut icon is hardcoded `/sc/favicon.ico`
- This host’s `serverconfig.php` uses `/~jonboy/sc/sc.php`

**v1 recommendation:** only first-class support for publishing at `/`. Subpath (`/sc/`) is a later option via `SC_APP_PATH`. Do not try to preserve `~user` URLs.

Apache extras for this app:

- `short_open_tag = On`
- `upload_max_filesize` / `post_max_size` at least 16k (icon limit is 15k) — 2M is fine
- `max_execution_time = 0` (updates catch up in a loop; `sc.php` already calls `set_time_limit(0)`)
- `Apache Timeout` 300+ so a backlog of updates on the first player request after idle is not killed
- Deny by location: `/utility`, `serverconfig.php`, `*.sql`, `/docs`, `/reference`, `/docker` if present
- Do not list directories

RSS PHP under `rss/` stays public; those scripts are player-facing.

### Configuration

**Source of truth at runtime: environment variables.** On each start (or first boot only — see open questions), a script writes `serverconfig.php` from a template that is **not** the live Ronin file.

Required on first boot:

| Variable | Purpose |
|----------|---------|
| `SC_ADMIN_PASSWORD` | Password for the seeded admin empire. Refuse first boot if missing or empty. |

Recommended:

| Variable | Default | Purpose |
|----------|---------|---------|
| `SC_SERVER_NAME` | `Stellar Crisis` | `$server['servername']` |
| `SC_ADMIN_NAME` | `admin` | Empire name, `is_admin=1` |
| `SC_ADMIN_EMAIL` | `admin@localhost` | `$server['admin_email']` |
| `SC_DOMAIN` | `localhost` | `$server['domainname']` |
| `SC_PUBLIC_URL` | `http://localhost:8080` | Used to build `app_path`, history URL, optional `server_URL` |
| `SC_APP_PATH` | `/sc.php` | `$server['app_path']` |
| `TZ` | `America/New_York` | Container TZ **and** `date_default_timezone_set` (current code hardcodes New York) |
| `MYSQL_ROOT_PASSWORD` | generated and stored on the volume on first boot if unset | MariaDB root |
| `MYSQL_DATABASE` | `sc` | |
| `MYSQL_USER` | `sc` | App DB user (not root) |
| `MYSQL_PASSWORD` | generated and stored on the volume if unset | |

The app’s mysqli target is always `127.0.0.1` (or `localhost` via TCP). Do not use a socket path that changes between MariaDB packages without testing.

Other `$server[...]` knobs (`custom_series_allowed`, `tournaments`, `icon_upload_allowed`, `require_valid_email`, FAQ/wiki URLs) get sane defaults matching a private/new server:

- tournaments on
- custom series on
- icon upload on
- `require_tos` / `require_valid_email` off (no SMTP in v1)
- `error_log_type` = `file` into `/data/logs`
- `history_write_path` = `/data/history` (absolute, so SAPI cwd cannot sneak in — `history.php` already respects an absolute path)
- `fleetNameSource` / `systemNameSource` = `words`

Operators who need full control can bind-mount a complete `serverconfig.php` over the generated file. If that file is present and `SC_CONFIG_FILE` (or a well-known path on `/data/config`) exists, **do not overwrite it**. That is the escape hatch for settings the env surface does not expose.

**Never** generate config from the repo’s `serverconfig.php`; that file has real credentials.

### First boot

Detect empty data: `/data/mysql` has no `mysql` system database, **or** the `sc` database has no `empires` table. Be conservative: if MariaDB datadir already exists, do not re-import `sc.sql`.

Sequence:

1. Initialize MariaDB datadir if needed (`mariadb-install-db` / `mysql_install_db` as appropriate for the package).
2. Start mysqld, wait for ready.
3. Create `MYSQL_USER` / `MYSQL_DATABASE` / grants.
4. `mysql sc < /usr/share/sc/sc.sql` (copy of repo `sc.sql` shipped in the image).
5. **Do not** run `upgrade_*.sql` on a fresh `sc.sql`. Current `sc.sql` already has password-hash columns, `auth_tokens`, Jumpgate/Carrier/Builder/Morpher, and SC3 series columns. Upgrade scripts remain for **imported old dumps** (manual, documented, not automatic).
6. Render `serverconfig.php` if the operator did not supply one.
7. Create the admin empire: `password_hash` via PHP CLI, `password` NULL, `is_admin=1`, empty `validation_info`. Same approach as the README insert, not a plaintext `password` column.
8. Touch `/data/.bootstrapped` (or equivalent) so later starts skip schema import and admin insert.
9. Start Apache.

Idempotency:

- If the admin name already exists, do not reset its password on later starts.
- If `SC_ADMIN_PASSWORD` is set on a later start, **ignore it** unless an explicit `SC_RESET_ADMIN_PASSWORD=1` is provided (dangerous; document it). Default is “first boot only.”

After first boot the Game List is empty. That is correct. The operator logs in and uses **Create series** (which already calls `spawnGame()`).

### Game updates

Leave the existing model alone. `checkForUpdates()` runs as part of ordinary `sc.php` requests. If no one is playing, the clock does not need to advance: no keepalive loop, no cron, no CLI `update_game()`.

When a player returns after idle time, that request’s `checkForUpdates()` already catches up missed turns in its inner `while`. Apache Timeout and `set_time_limit(0)` exist for that catch-up, not for a background ticker.

Do not add a PHP CLI path that calls `update_game()` directly. That would duplicate the web path and miss tournament checks, holiday-break handling, and passworded-game cleanup.

### Networking and run contract

Published port: **8080 on the host → 80 in the container** as the documented default (unprivileged host port). Image EXPOSEs 80.

Example (illustrative, not an implementation):

```
docker run -d --name sc \
  -p 8080:80 \
  -v sc-data:/data \
  -e SC_ADMIN_PASSWORD=... \
  -e SC_SERVER_NAME=Ronin-LAN \
  -e SC_PUBLIC_URL=http://192.168.1.10:8080 \
  sc-aio
```

A one-service `docker-compose.yml` may exist solely to name the volume, pass env, and set `restart: unless-stopped`.

Healthcheck: MariaDB ping **and** HTTP 200 from a **static** file (e.g. `/styles.css` or `/images/favicon.ico`). Do not healthcheck `sc.php` — that would run `checkForUpdates()` on the probe interval and become an accidental keepalive. Give first boot a long `start-period` (schema + `words` import is a large SQL file).

Resources: suggest **1 GB RAM** minimum (MariaDB + Apache + a catch-up update). CPU is idle between turns.

Stop: 30s+ grace so MyISAM flushes.

### TLS, mail, and the reverse proxy

- **TLS:** operator puts Caddy/nginx/Apache on the host or a sibling container and proxies to `:8080`. Set `SC_PUBLIC_URL` to the public `https://` origin so history links and `$server['app_path']` are right. The AIO vhost stays HTTP.
- **Mail:** v1 ships without MTA. Keep `require_valid_email=false`. If someone turns it on without mail, empire creation fails the way it already does. Optional later: `msmtp` + `SC_SMTP_*`.

### Security

- Generated DB passwords live on the volume (`/data/config/secrets` or similar), mode 0600, not in image layers.
- Admin password only via env at first boot; it is hashed before insert; env can be omitted afterwards.
- Web deny-list for `utility/` and secrets, even in a bind-mount-the-repo dev setup.
- Listen on container port 80; do not publish MariaDB. No `3306:3306` in the default compose.
- Run Apache and PHP as `www-data`. MariaDB as `mysql`. Do not run the app as root.
- Rootless Podman: document UID mapping for `/data`; MariaDB is the usual pain point. v1 officially supports rootful Docker/Podman; rootless is best-effort.
- Do not enable phpMyAdmin or a root shell on a published port.

### Backup and restore

Backup is the volume:

1. `docker stop sc` (consistent MyISAM files)
2. Copy `/data` (or `docker run --volumes-from` / `podman volume export`)
3. Start again

SQL dump alternative: `docker exec sc mariadb-dump -u sc -p sc`. Restore is a **new** empty volume plus import — not a second automatic first-boot path in v1. Document it.

History HTML and custom icons are not in the database; a dump-only backup loses them. Prefer the whole `/data` tree.

### Dev vs packaged use

| Mode | Code | Data |
|------|------|------|
| Packaged (default) | Copied into the image at build | `/data` volume |
| Dev | Bind-mount repo onto `/var/www/sc` | same `/data` |
| Import old server | Image code | Operator places a dump into MySQL after first boot, **or** replaces `/data/mysql` with a migrated datadir (unsupported unless documented after a test) |

Dev bind-mounts must not hide the container’s generated `serverconfig.php`. Either:

- generate it into `/data/config/serverconfig.php` and have `server.php` include that path when `SC_CONFIG_PATH` is set, or
- keep generated config outside the bind-mounted tree (`/etc/sc/serverconfig.php`) and change `server.php` to include that path when present.

**Recommended application hook:** `server.php` already includes `serverconfig.php`. Add a single extra include: if `getenv('SC_CONFIG_PATH')` is set, include that file instead. That is the only PHP change strictly required for clean bind-mounts and for not writing secrets into the git tree.

### Application changes (minimal, later)

When implementation starts, keep PHP diffs tiny:

1. `server.php` — honor `SC_CONFIG_PATH` (env) for the config file.
2. Optional: favicon href relative (`images/favicon.ico` or `favicon.ico`) so `/sc/favicon.ico` is not required.
3. Optional: `index.html` refresh target from a relative `sc.php` rather than `/sc.php`.

No gameplay diffs. No schema diffs. `sc.sql` is the first-boot schema as-is.

Apache/PHP/supervisor files live under `docker/` in the repo. README gets a short “Container” section pointing at this design and the run command.

## Alternatives considered

| Option | Why not as the primary design |
|--------|-------------------------------|
| Compose of separate `web`, `php`, `db` containers | Better textbook isolation; worse “just run it.” Can be a later sibling, not v1. |
| Nextcloud-style AIO master that spawns other containers | Needs Docker socket, too much machinery. |
| SQLite | Would require rewriting every `mysqli` call and the schema. Out of scope. |
| php-fpm + nginx | Fine technically; farther from current Apache hosting and more processes. |
| Keepalive GET loop (or host cron curling `sc.php`) | Ticks games when nobody is playing. Not needed; catch-up already runs on the next real request. |
| Bake Ronin `serverconfig.php` | Leaks credentials; wrong paths. |
| Auto-run `upgrade_*.sql` always | Fresh `sc.sql` already includes those changes; re-running is unsafe (password-security README already says do not run twice). |
| Seed default series (Grudge, etc.) | Needs a maintained catalog and ship-type options; admin Create series already spawns game #1. Follow-on, not v1. |
| HTTPS in-container (Certbot) | Port 80/443, stored certs, renewal — a second product. Reverse proxy instead. |

## Phased delivery

### Phase 1 — local playable AIO

- Dockerfile + supervisor + Apache vhost + MariaDB
- `/data` volume layout
- Env-driven config + `SC_CONFIG_PATH` include
- First boot: `sc.sql` + admin empire
- Deny `utility/` and secrets
- `.dockerignore`
- Documented `docker run` / one-service compose
- Healthcheck with a long start period

Success: from a clean machine, start the container, log in as admin, create a 2-player series, join two empires, and see an update fire when a player hits the site.

### Phase 2 — operator conveniences

- Bind-mount dev documented and tested
- Optional `SC_APP_PATH` subpath
- `msmtp` for `mail()`
- Backup/restore notes tested with a real volume
- Sample reverse-proxy snippet (Caddy or nginx)
- Optional `SC_RESET_ADMIN_PASSWORD`

### Phase 3 — extras (only if needed)

- Seed series SQL pack
- phpMyAdmin as an **opt-in** sidecar, not in the AIO image
- Rootless Podman Quadlet
- Import helper for old `mysqldump` files (detect missing columns, apply `upgrade_*.sql` in order)

## Risks

- **MariaDB SQL mode.** Modern defaults (strict, `ONLY_FULL_GROUP_BY`, etc.) can break queries that survived 5.6/early MariaDB. Pin a documented `sql_mode` in the in-container MariaDB config (likely `NO_ENGINE_SUBSTITUTION` plus `NO_AUTO_VALUE_ON_ZERO` to match `sc.sql`). Prove this on first implementation by creating a series, joining, updating, and ending a game.
- **MyISAM + kill -9.** Unclean shutdown can corrupt tables. Supervisor must pass SIGTERM and wait; document `docker stop` not `kill -9`.
- **`words` import size.** `sc.sql` is large because of the dictionary. First boot is slower than a typical PHP app; healthcheck `start-period` must reflect that.
- **Update catch-up after idle.** If nobody has loaded `sc.php` for a long time, the next player request may run many `update_game()` calls and exceed Apache Timeout. High Timeout + `set_time_limit(0)` is the mitigation; still test a large backlog.
- **Short open tags.** A PHP image that ships `short_open_tag=Off` will render broken pages with no obvious PHP error. The vhost must force it on.
- **Bind-mount overwriting config.** Without `SC_CONFIG_PATH` outside the tree, a repo mount brings the Ronin `serverconfig.php` into the container. Treat that as a footgun and implement the env override first.

## Test plan (when implemented)

1. First boot with only `SC_ADMIN_PASSWORD` set → login page loads; admin can sign in.
2. Second start of the same volume → no schema re-import, admin password unchanged.
3. Create series → open game appears (existing `spawnGame()` path).
4. Two empires join and load pages until an update; a later request after the due time produces the next update. An idle container with no browsers does not tick.
5. Upload a custom icon → file appears under `/data/icons` and survives container recreate.
6. Finish a game → history HTML under `/data/history` and is reachable in the browser.
7. `utility/` URL returns 403/404.
8. Rebuild image, reuse volume → game still there.
9. Stop/start (SIGTERM) → MariaDB comes up without repair.
10. Dev bind-mount of the repo + `SC_CONFIG_PATH` → does not use host DB credentials.

## Open questions

1. **s6-overlay vs supervisord?** Recommendation: s6 if we are willing to take its layout; supervisord if we want the implementation to stay obvious for a future packager who is not a container specialist. Either satisfies the design.
2. **PHP 8.2 vs 8.3 vs Debian default?** Recommendation: Debian stable’s `libapache2-mod-php`, as long as it is ≥ 8.2. Do not pin an EOL 8.1. If stable is still on an older PHP, use `debian:bookworm-backports` or the current stable that ships ≥ 8.2 — still Debian, not Ubuntu.
3. **Regenerate `serverconfig.php` every start from env, or only when missing?** Recommendation: if `/data/config/serverconfig.php` exists, leave it; otherwise generate. Env changes after first boot then require deleting that file or using the bind-mount escape hatch. Simpler than merging.
4. **Publish as `ghcr.io/...` or only “build it yourself”?** Out of scope for the design of the container itself; Phase 1 can be build-local only.
5. **Should first boot create a sample series?** Recommendation: no for v1. Easy to add a `SC_SEED_DEMO=1` later.

## Summary

Package Stellar Crisis PHP as a single container: Apache + PHP 8.2+ + MariaDB, with all mutable state on one `/data` volume. First boot loads current `sc.sql`, writes config from the environment, and inserts a hashed admin empire. Games update only when someone hits `sc.php`, same as today — no background ticker. Do not migrate the existing public servers, do not split the stack, and do not change gameplay. The only intended PHP hook is reading config from `SC_CONFIG_PATH` so secrets never have to live in the git tree.
