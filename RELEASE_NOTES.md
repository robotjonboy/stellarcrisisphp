# Stellar Crisis PHP 3.0.2

This release brings the PHP port much closer to classic SC 3.0 play, hardens shared-hosting installs, and adds packaging for operators (shared-hosting zip + optional Docker all-in-one).

## Highlights

- **SC 3.0 ships and series options** — Carrier, Builder, and Morpher, plus generalized ship-type options and series knobs for authentic sc3 series.
- **Security** — hashed passwords and session tokens, SQL injection and XSS fixes, per-empire RSS tokens, credentials kept out of the repo.
- **PHP 8** — runtime compatibility for current hosting stacks.
- **Operator packaging** — release zip with install/upgrade docs; optional Docker AIO image for local/LAN runs.

## Gameplay and UX

- Carrier, Builder, and Morpher implemented for `game_type = sc3`
- Series/admin UI for tech tiers (Developed / Unrestricted / Restricted / Barred), costs, loss, range, and related series parameters
- System screen: local 3×3 map, ship orders, and edge navigation links
- Builder rules: shared-HQ team builds, multi-origin links, random system names, no absolute coords leaked on failed creates
- Colonies cannot be built if system population would drop below 1
- Mobile usability improvements
- Message recipients stay selected after the select box loses focus
- Admin: spawn open games for series that lack them
- Many production fixes: scouting/closed links, surrender, jumpgate loss updates, series create/resume, map generation / city names, v3 ship options on timed updates, game-end / history hardening

## Security and reliability

- Password hashing (`password_hash`) and `auth_tokens` sessions (replaces plaintext password PK behavior)
- Password-protected game join verification fixed
- SQL injection hardening; XSS fix on profile comments
- RSS feeds use per-empire `rss_token` instead of guessable URLs
- `serverconfig.php` no longer tracked; ship `serverconfig.php.example` instead
- Application error log can live outside the docroot (`error_log_path`)

## Packaging and deploy

- Shared-hosting release zip via `./scripts/build-release-zip.sh` (working tree or `--tag`)
- Operator docs in the zip: `docs/INSTALL.md`, `docs/UPGRADE.md`, `docs/shared-hosting-error-log.md`
- Docker all-in-one image (`docs/design-aio-container.md`) for Apache + PHP + MariaDB in one container

## Upgrading from 3.0.1 / older installs

1. Backup the database, `history/`, and custom icons.
2. Overlay the release files; **do not overwrite** live `serverconfig.php`, history HTML, or `images/aliens/custom/` icons.
3. Add `$server['error_log_path']` (outside the docroot) if you use the private error log.
4. Run only the `upgrade_*.sql` scripts you have **not** already applied, in order (see `docs/UPGRADE.md`):

| Script | When |
|--------|------|
| `upgrade_sc3.sql` | Missing Carrier/Builder/Morpher or sc3 series columns |
| `upgrade_sc3_jumpgate_series.sql` | Jumpgate-only series need full ship-type option backfill |
| `upgrade_password_security.sql` | Missing `password_hash` / `auth_tokens` |
| `upgrade_rss_tokens.sql` | Missing `empires.rss_token` |

Fresh installs that import this release’s `sc.sql` skip those upgrade scripts.

## Compatibility notes

- Requires PHP 8-capable hosting (or the Docker AIO image).
- After the password-security upgrade, deploy the new PHP in the same cutover; players on old tabs may still POST `pass` until they navigate once.
- Existing empires get an RSS token lazily on the next Game List visit (or via the optional UPDATE in the SQL file).
