# Shared hosting: new install

Use these steps for a **first-time** Stellar Crisis PHP install on Apache + PHP + MySQL/MariaDB (typical shared hosting).

For an existing server, see [UPGRADE.md](UPGRADE.md) instead.  
For Docker all-in-one, see the project README container section (Docker files are not in the shared-hosting zip).

## Prerequisites

- Linux or Windows shared host (or VPS) with Apache
- PHP 8.2+ recommended (`password_hash` / `password_verify`; short open tags must be enabled — `short_open_tag=On`)
- MySQL or MariaDB
- Extensions: `mysqli`, `openssl`

## 1. Unpack the release

Unzip the release archive and place the inner folder’s contents where Apache will serve them, for example:

```text
public_html/sc/     ← sc.php lives here
```

You may use the web root itself or any subfolder. Note the URL path for `serverconfig.php` later (`app_path`, history URLs).

## 2. Create the database

Create an empty database and a MySQL user with rights to it. Import the schema and seed data:

```bash
mysql -u YOUR_USER -p --database=YOUR_DATABASE < sc.sql
```

`sc.sql` creates tables, ship types, and the words dictionary. It does **not** create empires or series.

Do **not** run `upgrade_*.sql` on a database freshly loaded from this release’s `sc.sql`. Those scripts are for older databases only (see [UPGRADE.md](UPGRADE.md)).

## 3. Configure the application

```bash
cp serverconfig.php.example serverconfig.php
```

Edit at least:

| Setting | Purpose |
|---------|---------|
| `mysql_host`, `mysql_user`, `mysql_password`, `mysql_database` | Database connection |
| `domainname`, `app_path`, `servername` | How the site identifies itself and builds URLs |
| `error_log_path` | Absolute path **outside** the web root (see [shared-hosting-error-log.md](shared-hosting-error-log.md)) |
| `history_read_URL`, `history_write_path` | End-of-game history HTML |

Never commit or publish `serverconfig.php`. It is host-specific and secret.

## 4. Create an admin empire

From a shell that has PHP CLI (on the host or any machine), generate a password hash:

```bash
php -r 'echo password_hash("your-password-here", PASSWORD_DEFAULT), "\n";'
```

Insert the admin row (paste the hash):

```sql
INSERT INTO empires (name, last_ip, password, password_hash, is_admin, validation_info, comment, email, real_name)
VALUES ('admin', '', NULL, '$2y$10$...paste-hash-here...', '1', '', '', 'email', 'admin');
```

## 5. Writable directories

Ensure PHP can write:

- `history/` — end-of-game HTML
- `images/aliens/custom/` — uploaded empire icons

Create them if missing. Typical shared hosting: your account owns the files; `755` on directories is usually enough.

## 6. Lock down non-public paths

This release **includes** `utility/` (maintenance scripts). It must **not** be reachable over HTTP. Also protect SQL dumps, docs, and config.

Options (pick what your host allows):

1. Delete `utility/` after you no longer need it, **or** move it outside the web root.
2. Merge rules from `htaccess.example` into an `.htaccess` in the application directory (denies `serverconfig.php`, `.env`, `errorlog.html` if present).
3. Add Apache/panel rules denying web access to `utility/`, `*.sql`, and `docs/`.

Set up the private error log directory per [shared-hosting-error-log.md](shared-hosting-error-log.md).

## 7. Smoke test

1. Open `sc.php` in a browser (e.g. `https://your.domain/sc/sc.php`).
2. Log in as the admin empire.
3. Create a series and confirm the game list loads.

## Checklist

- [ ] `sc.sql` imported into an empty database
- [ ] `serverconfig.php` created from the example and edited
- [ ] Admin empire inserted with `password_hash`
- [ ] `history/` and `images/aliens/custom/` writable
- [ ] `utility/` (and SQL/docs/secrets) not publicly readable
- [ ] Error log path outside the docroot
- [ ] Login and series creation work
