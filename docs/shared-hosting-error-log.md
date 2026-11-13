# Shared hosting: secure error log setup

Stellar Crisis appends HTML bug reports to a file when `error_log_type` is `file`. That file must **not** be downloadable over HTTP. Use a directory **outside** your web root (`public_html`, `www`, `htdocs`, etc.) and open the log only via **Administration → View Errors** while logged in as an admin empire.

(Design background for developers lives in the git repo as `docs/design-private-error-log.md`; it is not required for this setup.)

## 1. Find your home and web root

Typical layout (names vary by host):

```text
/home/YOURUSER/
  public_html/          ← web root (DocumentRoot)
    sc/                 ← this application (sc.php, …)
  private/              ← NOT served by Apache (create this)
    sc-logs/
      errorlog.html
```

Confirm with your host’s file manager or SSH: anything under `public_html` is usually public. Prefer a sibling of `public_html`, not a folder inside it.

## 2. Create the private log directory

SSH example (replace `YOURUSER` and adjust the PHP user if your host documents a different one):

```bash
mkdir -p /home/YOURUSER/private/sc-logs

# Move an existing webroot log if present (keeps history)
if [ -f /home/YOURUSER/public_html/sc/errorlog.html ]; then
  mv /home/YOURUSER/public_html/sc/errorlog.html /home/YOURUSER/private/sc-logs/errorlog.html
fi

touch /home/YOURUSER/private/sc-logs/errorlog.html

# On most shared hosts, PHP runs as YOURUSER — ownership stays with you:
chmod 750 /home/YOURUSER/private
chmod 750 /home/YOURUSER/private/sc-logs
chmod 640 /home/YOURUSER/private/sc-logs/errorlog.html
```

### If PHP runs as a different user (e.g. `nobody`, `apache`, `www-data`)

Your host’s docs will say. Then the **web server user** must own the log (or have group write), and every path component must be traversable (`x`) by that user.

**Preferred (needs permission to `chown`):**

```bash
# Example only — use the account your host specifies
chown -R nobody:YOURUSER /home/YOURUSER/private/sc-logs
chmod 750 /home/YOURUSER/private
chmod 750 /home/YOURUSER/private/sc-logs
chmod 640 /home/YOURUSER/private/sc-logs/errorlog.html
```

**If you cannot `chown` to the web user** (common without root), use one of:

**A — POSIX ACL** (if `setfacl` exists; preferred when the web user is `nobody` / `www-data`):

```bash
chmod 711 /home/YOURUSER/private
chmod 750 /home/YOURUSER/private/sc-logs
chmod 640 /home/YOURUSER/private/sc-logs/errorlog.html
setfacl -m u:nobody:rwx /home/YOURUSER/private/sc-logs
setfacl -m m::rwx /home/YOURUSER/private/sc-logs
setfacl -m u:nobody:rw  /home/YOURUSER/private/sc-logs/errorlog.html
setfacl -m m::rw /home/YOURUSER/private/sc-logs/errorlog.html
setfacl -d -m u:nobody:rw /home/YOURUSER/private/sc-logs   # default ACL for new files
# Confirm with getfacl — "effective" rights for nobody must include w on the file.
```

Replace `nobody` with `www-data` or whatever user `ps aux | grep apache` / your host documents.

**B — shared group** (e.g. `nogroup` when the web user is in that group *and* you can `chgrp`):

```bash
chmod 711 /home/YOURUSER/private          # others: traverse only (no list)
chgrp -R nogroup /home/YOURUSER/private/sc-logs
chmod 770 /home/YOURUSER/private/sc-logs
chmod 660 /home/YOURUSER/private/sc-logs/errorlog.html
```

Also ensure `/home/YOURUSER` is at least `711` or `755` so the web user can traverse into `private/` (many hosts already use `755` on home).

**Critical on Debian/Ubuntu:** if the log directory is owned by `www-data` but the parent `private/` is still `750` (`drwxr-x---`) owned by your user, Apache **cannot enter** `private/` at all. Symptom: Admin → View Errors says “path configured” but “not readable”. Fix the parent:

```bash
chmod 711 /home/YOURUSER/private   # others: traverse only (no list)
# Confirm with: namei -l /home/YOURUSER/private/sc-logs/errorlog.html
# Every component needs x for the PHP user (www-data).
```

On many shared hosts PHP runs **as your account** (suexec / PHP-FPM pool). In that case the simple `chmod 750` / `640` under your ownership is enough — no `chown` or ACL.

Check which user PHP is: `ps aux | grep apache` or `grep APACHE_RUN_USER /etc/apache2/envvars` (often `www-data` on Debian).

### Modes to avoid

| Bad | Why |
|-----|-----|
| `666` / `777` on the file or directory | Any local account can read or overwrite the log |
| Log file under `public_html/` | Apache can serve it as a static URL |

## 3. Point `serverconfig.php` at the private file

Edit the config **outside** or protected from HTTP (this project expects `serverconfig.php` next to the app but gitignored; prefer keeping secrets out of the docroot when the host allows).

```php
$server['error_log_type'] = 'file';
$server['error_log_path'] = '/home/YOURUSER/private/sc-logs/errorlog.html';
```

Use an **absolute** path. Relative paths are resolved against the app directory and are easy to leave inside the web root by mistake.

## 4. Remove the old public file

```bash
rm -f /home/YOURUSER/public_html/sc/errorlog.html
```

Visit `https://YOURDOMAIN/sc/errorlog.html` (or your real app URL). You should get **404** (or **403** if you add the deny rule below). You must **not** see the log.

## 5. Defense in depth: deny `errorlog.html` via HTTP

If the host allows `.htaccess` in the app directory, add (or merge):

```apache
<Files "errorlog.html">
	Require all denied
</Files>
# Apache 2.2 hosts may need:
# <Files "errorlog.html">
#	Order allow,deny
#	Deny from all
# </Files>
```

Some panels offer “deny access to file” without raw `.htaccess`. Use that for `errorlog.html` if present.

## 6. Verify

1. Log in as an empire with `is_admin = 1`.
2. **Administration → View Errors → Go** — you should see the HTML log (or “No error log” if empty/unreadable).
3. Trigger a test condition that logs a warning (or wait for a real one); confirm the private file grows and stays mode `640` (not `666`).
4. Confirm the direct URL to `errorlog.html` under the site still fails.

## 7. Day-2 operations

- **Read via SSH:** `less /home/YOURUSER/private/sc-logs/errorlog.html`
- **Truncate:** ` : > /home/YOURUSER/private/sc-logs/errorlog.html` then `chmod 640` that file again if needed
- **Rotate:** move aside and `touch` a new file with `640`

## Troubleshooting

| Symptom | Likely cause |
|---------|----------------|
| View Errors: cannot read log | Wrong path in `error_log_path`; directory not traversable; file owned by another user |
| Log still at old URL | File or symlink still under web root; CDN/cache; wrong vhost docroot |
| New log is `666` | Host umask; app tries `chmod 0640` after write — check host allows chmod |
| Nothing logged | `error_log_type` not `file`; path not writable; errors are `E_NOTICE` only (handler skips notices) |

## Docker note

The AIO image writes to `/data/logs/errorlog.html` and serves the log only through the admin UI. Operators do not need these shared-hosting steps inside the container; see `docs/design-private-error-log.md` and `docker/entrypoint.sh`.
