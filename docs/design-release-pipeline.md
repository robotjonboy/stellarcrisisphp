# Design: Shared-Hosting Release Pipeline

## Problem

Shipping Stellar Crisis PHP to a shared-hosting account today is a manual copy of a working tree. That invites mistakes:

1. Live secrets travel with the tree (`serverconfig.php`, `.env`).
2. Runtime junk ships with the app (`history/*.html`, `errorlog.html`, `WS_FTP.LOG`, `*.bak`, IDE metadata).
3. Operators guessing which SQL to run for a **new** install vs an **update** of an existing database.
4. Design notes and operator docs live in `docs/` on the developer machine but are easy to leave behind when zipping by hand.
5. There is no named, repeatable artifact (“this zip is release X”) for Homeserve / Rocket Empires–style deploys.

The Docker AIO image (`docs/design-aio-container.md`) already covers “one container, first boot.” It does **not** replace the shared-hosting path those long-lived servers still use.

## Goal

Define a **simple release pipeline** whose only job is to produce a **zipfile** with everything needed to deploy Stellar Crisis PHP on typical shared hosting (Apache + PHP + MySQL/MariaDB), for either:

1. **New install** — empty database, first copy of the tree under `public_html` (or a subfolder).
2. **Update** — overlay newer code on an existing install, then run only the schema scripts that apply.

The zip includes **operator documentation** (install, update, config, security notes). Building the zip is a local, repeatable step from a clean source tree (or git tag). No fancy CD platform is required.

Deliverable of this document: design for that pipeline and zip layout. **Implemented:** `scripts/build-release-zip.sh`, `docs/INSTALL.md`, `docs/UPGRADE.md`.

## Non-goals

- Do not build an installer GUI, web wizard, or in-app “upgrade” button.
- Do not auto-apply SQL on the target host.
- Do not replace or depend on the Docker AIO image; that remains a separate packaging story.
- Do not push to shared hosting over FTP/SFTP from the pipeline. The artifact is a downloadable zip; the operator uploads it.
- Do not publish player data, live `serverconfig.php`, `.env`, or `history/*.html`.
- Do not require GitHub Actions, GitLab CI, or any hosted runner for v1. A script run on a developer machine is enough. CI may wrap the same script later.
- Do not minify, transpile, or otherwise transform PHP/CSS/JS.
- Do not change game rules or application behavior as part of packaging.
- Do not solve database backups, rollback of schema, or zero-downtime deploys.
- Do not produce Windows installers, Debian packages, or multi-flavor archives beyond one shared-hosting zip per release.

## Background: what a shared-hosting deploy actually is

Stellar Crisis PHP is a classic LAMP app. Apache serves `sc.php` as the front controller. Game updates run on ordinary requests (`checkForUpdates()`), not via cron. Secrets and host identity live in `serverconfig.php`. Writable runtime dirs include `history/` and `images/aliens/custom/`.

| Item | Reality |
|------|---------|
| Entry | `sc.php` (and `index.html` redirect) |
| Config | `serverconfig.php` (gitignored); template is `serverconfig.php.example` |
| Fresh schema | `sc.sql` (tables + `ship_types` + `words`; no empires/series) |
| Incremental schema | `upgrade_*.sql` files at repo root (password security, RSS tokens, SC3, etc.) |
| Operator docs today | `readme.md` (install + upgrade sections), `docs/shared-hosting-error-log.md`, design docs |
| Must not be world-readable | `serverconfig.php`, `.env`, `utility/`, `*.sql`, `docs/`, `reference/`, `sql backups/` |
| Host helpers | `htaccess.example` for optional Apache deny rules |

Current install steps (from `readme.md`) are: create DB → import `sc.sql` → copy tree → edit `serverconfig.php` → create admin empire → lock down `utility/`. Updates are “copy newer PHP, then run the matching upgrade SQL.”

## Terminology

| Term | Meaning |
|------|---------|
| **Release** | A named snapshot of the application intended for operators (e.g. version string + date or git tag). |
| **Release zip** | The single archive produced by the pipeline for shared hosting. |
| **Payload** | Files inside the zip that get uploaded under the app directory. |
| **New install** | Empty (or freshly created) database + first deployment of the tree. |
| **Update** | Existing database and tree; replace/overlay application files; run needed `upgrade_*.sql`. |
| **Operator** | Person who uploads the zip and configures the host. Distinct from the in-game admin empire. |
| **Build machine** | Developer laptop or CI runner that runs the packager; never the production shared host. |

## Design principles

1. **One artifact.** One zip per release. Same zip serves new install and update; docs tell the operator which steps to skip.
2. **No secrets in the zip.** Never pack live `serverconfig.php`, `.env`, credentials, or player-generated content.
3. **Preserve the running site on update.** The zip must not instruct or encourage overwriting a live `serverconfig.php`, `history/*.html`, or custom icons the operator already has.
4. **Boring tools.** Prefer `zip` / `rsync` / a small shell script over a build framework. PHP is not required to build the zip.
5. **Docs travel with the code.** An operator who only has the zip can install or update without cloning the git repo.
6. **Explicit include/exclude lists.** Packaging by “zip the whole repo” is how secrets and junk leak. The pipeline uses a defined payload list (or rsync filters), not “everything under `.`”.
7. **Version is visible.** Zip filename and a small `VERSION` (or equivalent) file inside the archive identify the release.

## Proposed shape

### Trigger

v1: the operator of the **source** tree (developer) runs one command from the repo root. **Both modes are supported:**

```
# Working-tree release (local smoke tests, uncommitted fixes, pre-tag checks)
./scripts/build-release-zip.sh
./scripts/build-release-zip.sh --out dist/

# Tag-only release (preferred for anything given to production operators)
./scripts/build-release-zip.sh --tag v3.0.3
```

| Mode | Source of files | When to use |
|------|-----------------|-------------|
| **Working tree** (default) | Current checkout on disk, after the same include/exclude filters | Smoke-test the packager; verify a fix before tagging; builds that intentionally include local edits |
| **Tag** (`--tag <name>`) | `git archive` of that tag only (tracked files at the tag) | Public or production operator zips — reproducible, no untracked junk |

Same include/exclude rules apply in both modes. Tag mode must fail clearly if the tag is missing. Working-tree mode should warn if `serverconfig.php` / `.env` would otherwise be copyable, and still exclude them. For production hand-offs, prefer a tag build.

### Output

A single file, e.g.:

```
dist/stellar-crisis-php-<version>.zip
```

`<version>` comes from, in order of preference: explicit flag → `git describe --tags` → `serverconfig.php.example` `$server['version']` string → UTC date `YYYYMMDD`.

Inside the zip, one top-level directory so unzipping does not scatter files:

```
stellar-crisis-php-<version>/
  …payload…
```

### Pipeline steps (logical)

1. Resolve version string and output path.
2. Assemble a staging directory (temp dir or `git archive` extract).
3. Apply **include/exclude** rules (below).
4. Ensure operator docs are present in the staged tree (see Documentation).
5. Write `VERSION` (version, build date, optional git commit).
6. Create the zip from the staging root’s parent so the archive contains the versioned top folder.
7. Print the zip path and a short checklist (new install vs update pointers).
8. Delete the staging directory.

No upload step. No test suite gate required for v1 (nice-to-have later).

## Payload: what goes in the zip

### Include (application)

- PHP application: root PHP entrypoints and libraries (`sc.php`, `auth.php`, `sql.php`, `update.php`, `history.php`, …), plus `admin/`, `main/`, `game/`, `rss/`, `tournaments/`, `update/`.
- Static assets: `styles.css`, `sc.js`, icons/images under `images/` (stock alien icons). The `images/aliens/custom/` directory ships empty of icons (keep the folder / `.gitkeep` only) — no `.gif` files.
- `index.html`, `LICENSE`, `footer.php`, `ship_types.php`, and other files the running app needs.
- `serverconfig.php.example` (not `serverconfig.php`).
- `htaccess.example`.
- `.env.example` only if shared-hosting docs still reference env-style hints; otherwise omit `.env*` entirely for the shared-hosting zip. Prefer documenting `serverconfig.php` as the shared-hosting config path.
- Schema: `sc.sql`.
- Upgrade scripts: all `upgrade_*.sql` at the repo root that operators may need for older servers.
- `utility/` — **always ships.** Maintenance scripts belong in the release. INSTALL/UPGRADE must stress that `utility/` must not be web-accessible (delete after use, move outside docroot, or deny via `.htaccess`). Same warning as today’s README.

### Include (documentation)

Ship a **small operator-only** doc set inside the zip (paths under the versioned root). Do **not** ship full design documents.

| Path in zip | Role |
|-------------|------|
| `README.md` | Project overview + pointers into `docs/` for install/update (keep or trim the historical upgrade paste if duplicated). |
| `VERSION` | Release identity. |
| `docs/INSTALL.md` | **New install** checklist for shared hosting (derived from current README install section + error-log / admin creation). |
| `docs/UPGRADE.md` | **Update** checklist: backup → overlay code → which SQL to run → what not to overwrite. |
| `docs/shared-hosting-error-log.md` | Private error log path and permissions. |
| `LICENSE` | GPL v2. |

**Minimum bar:** those three operator docs plus README/LICENSE/`VERSION` are present and accurate for the files in that zip.

**Explicitly omit from the zip:** `docs/design-*.md`, `docs/map-generation-php-vs-pike.md`, and other design/analysis notes. Those stay in git for developers. No `--with-design-docs` flag in v1.

`reference/` (lore / external notes) is **excluded** to keep the zip smaller.

### Exclude (never pack)

| Pattern / path | Why |
|----------------|-----|
| `.git/`, `.gitignore`, `.cursor/`, IDE (`.project`, `.buildpath`, `.settings/`) | Dev-only |
| `serverconfig.php`, `.env` | Secrets / host-specific |
| `history/*.html` | Player/runtime data |
| `images/aliens/custom/*.gif` | Uploaded / host-specific empire icons (keep the empty custom dir) |
| `errorlog.html` | Runtime log |
| `sql backups/` | Local dumps |
| `docker/`, `docker-compose.yml`, `.dockerignore` | Separate AIO product |
| `*.bak`, `Copy of *.php`, `Thumbs.db`, `WS_FTP.LOG` | Junk |
| `dist/` | Avoid nesting previous zips |
| `scripts/` | Packager lives in git; not needed on the shared host |
| `docs/design-*.md`, other design/analysis markdown under `docs/` except INSTALL, UPGRADE, and `shared-hosting-error-log.md` | Developer design notes; not operator docs |
| `reference/` | Lore / external notes; not required to install or run |
| Agent / editor scratch, `phpversiondifferences.md` (dev note) | Not needed to run the game |

`images/aliens/custom/` must appear in the zip as a writable directory (e.g. with `.gitkeep`) but **without any `.gif` files**, even if some are tracked in git. Those icons are host/player-specific and must not overwrite or leak from a build machine.

## New install vs update (operator flow)

Both flows use the **same** zip. Documentation splits the steps.

### New install (summary)

1. Unzip on the build machine or upload and unzip on the host into the chosen app directory (e.g. `public_html/sc`).
2. Create an empty MySQL/MariaDB database.
3. Import `sc.sql`.
4. Copy `serverconfig.php.example` → `serverconfig.php`; edit DB credentials, paths, server name, `error_log_path` (outside docroot).
5. Create the admin empire (hash via `password_hash`, INSERT as in README / `docs/INSTALL.md`).
6. Ensure `history/` and custom-icon dirs are writable by PHP.
7. Lock down `utility/`, SQL files, and docs (deny or remove from web).
8. Optionally merge `htaccess.example` into `.htaccess`.
9. Open `sc.php`, log in as admin, create a series.

Do **not** run `upgrade_*.sql` on a database freshly loaded from current `sc.sql` unless a specific script says it is still required (current `sc.sql` should already embody the latest schema).

### Update (summary)

1. Backup the database and any writable dirs (`history/`, custom icons).
2. Put the site in a quiet window if possible (no requirement for a formal maintenance mode in v1).
3. Unpack the zip beside the install; copy/overlay **application** files onto the live tree.
4. **Do not overwrite** live `serverconfig.php`, `history/*.html`, or custom icons.
5. Compare live schema to the release notes / `docs/UPGRADE.md` and run only the `upgrade_*.sql` scripts not yet applied (order documented there).
6. Smoke-test login, game list, and one in-game page; check admin error viewer if configured.

`docs/UPGRADE.md` should maintain an ordered list of upgrade scripts with “run if missing column/table X” guidance, based on the existing README upgrade sections and root `upgrade_*.sql` files.

## Documentation product (what we author for the zip)

For implementation (later), split operator truth out of the long historical `readme.md` where needed:

1. **`docs/INSTALL.md`** — shared-hosting new install only; short checklist; link to error-log doc; admin creation; post-install lockdown.
2. **`docs/UPGRADE.md`** — overlay rules; ordered upgrade SQL; “do not run twice” warnings; note that Docker users follow AIO docs instead.
3. **Zip-root `README.md`** — brief identity + “start here” links to INSTALL / UPGRADE. May remain the existing project README with a clear “Shared hosting releases” section at the top pointing at those docs.

Design docs (`docs/design-*.md` and similar) **do not ship** in the release zip. They remain in the git repo for developers only.

## Versioning and naming

- Zip: `stellar-crisis-php-<version>.zip`
- Inner folder: same prefix/version
- `VERSION` file: plain text, at least `version=` and `built=` (ISO date); optional `commit=`

Align `<version>` with `$server['version']` in `serverconfig.php.example` when cutting a release so the UI footer and the zip agree. Changing that string remains a manual release chore unless a later script updates both.

## Relationship to Docker AIO

| Concern | Shared-hosting zip | AIO container |
|---------|--------------------|---------------|
| Audience | Homeserve-style hosts | Laptop / LAN / new public instance |
| Config | Edit `serverconfig.php` | Env vars → rendered config |
| DB bootstrap | Operator runs `sc.sql` | Entrypoint on first boot |
| Artifact | `.zip` | OCI image |
| Pipeline | `build-release-zip.sh` | `docker build` |

Do not put Docker files in the shared-hosting zip. Do not make the zip builder build images.

## Security checklist (packaging)

- Fail the build (or warn loudly) if staging still contains `serverconfig.php` with real credentials, `.env`, or `history/*.html`.
- Tag builds use `git archive`, so untracked secrets never enter those zips. Working-tree builds must still apply the exclude list and refuse or warn if secrets appear in staging.
- Remind operators in UPGRADE.md that uploading over FTP can leave old `.bak` files; they should not rely on the zip to delete host junk.
- Remind operators in INSTALL.md that `utility/` ships and must be locked down.

## Acceptance criteria

- [ ] A documented command produces one zip under `dist/` (or equivalent).
- [ ] Zip has a single versioned top-level directory.
- [ ] No `serverconfig.php`, `.env`, `.git/`, `docker/`, `history/*.html`, or `images/aliens/custom/*.gif` inside the zip.
- [ ] `sc.sql`, current `upgrade_*.sql`, `serverconfig.php.example`, and `htaccess.example` are present.
- [ ] `utility/` is present in the zip; INSTALL/UPGRADE warn that it must not be web-accessible.
- [ ] `docs/INSTALL.md`, `docs/UPGRADE.md`, and `docs/shared-hosting-error-log.md` are present and sufficient for new install and update without the git repo.
- [ ] No `docs/design-*.md` (or other design/analysis docs) in the zip.
- [ ] Same zip can be used for both flows by following those docs.
- [ ] Packager supports working-tree builds and `--tag` builds; tag mode fails if the tag is missing.
- [ ] `VERSION` (or zip filename) identifies the release.
- [ ] Application PHP/CSS/JS/images needed to run the game are present and layout matches a normal app directory (so unzip-into-`sc/` works).

## Implementation

1. `docs/INSTALL.md` and `docs/UPGRADE.md` — operator checklists.
2. `scripts/build-release-zip.sh` — working-tree and `--tag` modes; `utility/` in; design docs out.
3. `dist/` in `.gitignore`.
4. Production hand-offs: tag git, then `./scripts/build-release-zip.sh --tag <name>`.

## Decisions (resolved)

| Topic | Decision |
|-------|----------|
| `utility/` | **Ships** in every release zip; docs require lockdown. |
| Design docs | **Omit** `docs/design-*.md` and similar analysis notes from the zip. Operator docs only: INSTALL, UPGRADE, shared-hosting-error-log. |
| Build source | **Both** working-tree (default) and tag-only (`--tag`). Prefer tags for production operator zips. |

## Out of scope / later

- GitHub Release upload automation
- Checksums (`SHA256SUMS`) beside the zip
- Separate “docs-only” or “sql-only” archives
- Automated schema version table inside the database
- FTP deploy script

---

**Status:** Implemented. Build with `./scripts/build-release-zip.sh` (or `--tag`).
