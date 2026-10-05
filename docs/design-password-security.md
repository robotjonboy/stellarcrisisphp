# Design: Password Hashing and Session Tokens

## Problem

Empire authentication today stores and transmits passwords in plaintext:

1. **Database** — `empires.password` is `varchar(20)` and holds the actual password. The composite primary key is `(name, password)`.
2. **Continuity** — There are no PHP sessions and no auth cookies. Every authenticated page embeds the empire’s password in a hidden form field (`name` + `pass`). Each POST re-validates those credentials against the database in `sc.php`.
3. **Profile UI** — Edit Profile prefills the password fields with the stored plaintext value, so the password appears in HTML source.

A dump of the database, a leaked HTML form, browser history, or a proxy log can expose every active player’s password. That is unacceptable for a long-lived public game.

Constraints that rule out the usual “force password reset” migration:

- Many empires are years old; email addresses are often stale or unused.
- Players must keep playing without noticing a security project (no banners, no forced email reset).
- Open tabs that still carry a hidden `pass` must keep working after the DB plaintext is wiped, as long as that submitted password verifies against the new hash.

## Goal

Harden empire authentication in two complementary ways, with minimal player-visible change:

1. **Hash passwords at rest** in a new column, while still accepting existing plaintext passwords until each account is upgraded on next successful password use (lazy migration — no email reset). On upgrade, **null out** the original plaintext column immediately.
2. **Replace the hidden password field with a session token** for ongoing play, with token lifetime **up to one year**. The request that upgrades (or logs in) receives a token and continues without interruption. Other tabs still posting `pass` remain authenticated if `password_verify` succeeds against `password_hash`, then also move onto tokens.

Deliverable of this document: design for the password-security work. **Implemented** in `auth.php`, `upgrade_password_security.sql`, and related call sites.

## Non-goals

- Do not force password resets or email verification for existing empires.
- Do not keep plaintext in the DB after an account has been upgraded.
- Do not change game join passwords (`games.password1` / `password2`) — those are a separate feature.
- Do not change RSS opaque IDs (`md5(id + name + join_date)`).
- Do not rebuild auth around PHP `$_SESSION` or a modern framework.
- Do not add OAuth, 2FA, or password-strength meters in this project.
- Do not require players to clear cookies or update bookmarks.
- Do not change the `sc_login` cookie’s role (empire **name** prefills on the login form only).
- Do not hard-delete database rows for this feature (no `DELETE FROM auth_tokens`, no purging expired tokens). Use soft revoke only.

## Background: current auth model

```
Browser login form (name + pass)
        │ POST section=login
        ▼
sc.php gate: SELECT WHERE name=? AND password=?   ← plaintext SQL compare
        │ sets $authenticated / $authenticated_as_admin
        ▼
login(): $empire['password'] == $vars['pass']
        │ setcookie(sc_login = name, 24h)
        ▼
Every subsequent page:
  <input type=hidden name=name>
  <input type=hidden name=pass>   ← plaintext password echoed
        │ POST any action
        ▼
Gate re-check → section handler (main / game / admin)
```

Key facts:

| Item | Reality |
|------|---------|
| Gate auth | `sc.php` ~120–138: SQL equality on `name` + `password` |
| Login | `login()` plaintext `==` compare; then `gameList` |
| Continuity | Hidden `pass` on nearly every form (~30+ emit sites) |
| Cookie | `sc_login` = empire name only (24 hours) |
| Admin | Same password + `is_admin = '1'` |
| Schema | `password varchar(20)`; **PRIMARY KEY `(name, password)`** |
| PHP | 8.2 with `password_hash()` / `password_verify()` available |

Pages that emit the hidden password today include lobby (`main/gameList.php` and siblings), in-game header (`game/game.php`), admin pages, profile, tournaments, stats, and icon upload. Some emit `$vars['pass']` (POST echo); some emit `$empire['password']` (DB value). Edit Profile also puts the password in visible `type=password` inputs.

## Design principles

1. **Invisible migration** — Login and open sessions continue without banners, email reset, or “update your password” interstitial. Newly rendered pages use tokens; old forms still posting `pass` keep working if the password verifies.
2. **Lazy upgrade, eager erase** — The first time we successfully verify a plaintext password for an account, write the hash to a new column and **immediately null the plaintext `password` column**. Do not batch-rewrite the table offline (we do not know the plaintexts without the users).
3. **Dual-path continuity** — The gate accepts a valid session **token**, or a submitted **`pass` that verifies** (against `password_hash` if set, else against legacy plaintext — then upgrade). Wiping DB plaintext does not require rejecting form passwords.
4. **Never store what we can avoid** — After upgrade or login, new forms emit a token, not the password. Plaintext does not remain in the DB “just in case.”
5. **Small surface for the gate** — Centralize verify / upgrade / token issue / token check in one place (`sc.php` gate + small helpers). Do not scatter crypto logic across thirty templates.
6. **Classic form-POST preserved** — Keep `<form method=post action="sc.php">` and hidden fields; only the secret changes from password to token.
7. **Soft deletes only** — For this feature, never `DELETE` rows from the database. Revoke tokens (and any similar cleanup) by updating a status/timestamp column. Expired or revoked rows stay in place and are ignored on lookup.

---

## Part A — Password hashing

### Algorithm

Use PHP’s password API:

- Store with `password_hash($plain, PASSWORD_DEFAULT)` (currently bcrypt on this PHP build; may become Argon2 in future PHP — that is fine).
- Verify with `password_verify($plain, $hash)`.
- Optionally `password_needs_rehash` on successful verify to bump cost/algorithm later.

Do **not** invent a custom salt column or MD5/SHA wrapper. Do **not** store the hash in the existing `password` column.

### Schema changes (decided)

| Change | Decision |
|--------|----------|
| Primary key | **`PRIMARY KEY (name)`** — drop composite `(name, password)`. `id` stays as unique auto-increment for FKs (e.g. `auth_tokens.empire_id`). |
| Keep `password` | Existing `varchar(20)` column remains for **legacy plaintext only**. Make it **nullable**. After upgrade: `password = NULL`. |
| New column | **`password_hash varchar(255) NULL`** — holds PHP `password_hash()` output. `NULL` means “not yet migrated” (or brand-new row not yet written). |

End state for a migrated empire:

- `password` → `NULL` (plaintext erased)
- `password_hash` → `$2y$...` (or successor algorithm string)
- identity → `PRIMARY KEY (name)`

Migration SQL is an implementation detail; Phase 0 must run before any hash is written. Existing plaintext values stay in `password` until each account’s lazy upgrade.

### How to tell migrated vs legacy

| `password` | `password_hash` | Meaning |
|------------|-----------------|---------|
| non-null | `NULL` | Legacy plaintext account (not yet upgraded) |
| `NULL` | non-null | Migrated (or created after this project) |
| non-null | non-null | Should not happen; if seen, prefer `password_hash` and null `password` |
| `NULL` | `NULL` | Invalid / broken row |

### Password verification helper (login and gate)

Load empire by **name only** (never `WHERE password = ?` for auth once this ships).

One shared verify path for any submitted `pass` (login form **or** legacy hidden field):

1. If `password_hash` is set → `password_verify($pass, password_hash)`; on success authenticate (rehash into `password_hash` if `password_needs_rehash`; ensure `password` stays NULL).
2. Else if `password` is non-null and equals submitted `$pass` → authenticate, then **upgrade atomically**:
   - `password_hash = password_hash($pass, PASSWORD_DEFAULT)`
   - `password = NULL`
3. Else → reject (“Incorrect password” / gate fails).

On success from either branch, queue a session **token** for the outgoing page so the client stops relying on hidden `pass` as soon as practical.

Consequence: after the first upgrade, the DB no longer has plaintext, but sibling tabs that still POST the real password in `pass` continue to authenticate via `password_verify` and then pick up their own tokens. No need to keep plaintext in the database for multi-tab continuity.

**Important:** After deploy, the gate SQL must change from equality-on-password to name lookup + PHP verify / token check. Leaving the old SQL in place would break every migrated account.

### Create empire / email validation

Today (when `require_valid_email` is on):

- A temporary password is stored in `empires.password`
- The player’s chosen password sits in `validation_info` as `C/<chosen>`
- First login with the temp password promotes `C/<chosen>` into `password`

Design:

- Store the **temporary** login secret in `password_hash` (hashed); leave `password` NULL.
- Keep the chosen password in `validation_info` only until first successful login, then write it into `password_hash` and clear `validation_info`.
- `blockEmail` and related temp-password checks must use the verify helper against `password_hash`, not SQL equality on plaintext.

When email validation is off: insert into `password_hash` at create time; leave `password` NULL. Never write new plaintext.

### Edit Profile

- Stop prefilling `pass_new` / `pass_check` with the current password (impossible once hashed, and unsafe today).
- Leave those fields empty; changing password still requires typing the new value twice.
- On save: write `password_hash` from `pass_new`; keep `password` NULL.
- After a password change: soft-revoke that empire’s session tokens (see Part B) so stolen tokens do not survive a password change. The current browser request should receive a **new** token in the next form render so the player is not logged out of the tab they just used.

### What players see

Nothing different on login or play. Edit Profile password boxes are blank instead of showing dots for the existing password. That matches normal web practice and does not require explanation. Sibling tabs with an old hidden `pass` keep working until they navigate once and receive a `token`.

---

## Part B — Session tokens (replace hidden password)

### Role of the token

After a successful authentication, the server issues an opaque session token. Authenticated pages put that token in a hidden field instead of the password. Subsequent POSTs authenticate with `name` + token.

Suggested hidden field name: **`token`** (new). Keep accepting **`pass`** whenever it verifies (legacy open forms and the login / create flows). Prefer emitting `token` on all newly rendered authenticated pages.

Do **not** reuse the `pass` field to carry tokens. Mixing “sometimes password, sometimes token” in one field makes gate logic and password-change flows harder to reason about and easier to get wrong.

### Token properties

| Property | Choice |
|----------|--------|
| Entropy | At least 128 bits of CSPRNG (`random_bytes`); encode as hex or base64url for the form value |
| Storage | Store only a **hash** of the token (e.g. SHA-256), never the raw token |
| Binding | Empire id (or unique name) + expiry |
| Lifetime | **Up to one year** from issue (see expiry policy below) |
| Transport | Hidden form field (same POST model as today); optionally also set an HttpOnly cookie later — **not required for v1** |
| Multi-session | Allow multiple concurrent tokens per empire (different browsers / devices) |

### Storage: new table (recommended)

```
auth_tokens
  id            INT AUTO_INCREMENT PRIMARY KEY
  empire_id     INT NOT NULL          -- FK-ish to empires.id
  token_hash    CHAR(64) NOT NULL     -- sha256 hex of raw token
  created_at    INT NOT NULL          -- unix time
  expires_at    INT NOT NULL          -- unix time (created_at + up to 1 year)
  last_used_at  INT NULL             -- optional, forensics
  revoked_at    INT NULL             -- soft delete: NULL = active; unix time when revoked
  INDEX (token_hash)
  INDEX (empire_id)
  INDEX (expires_at)
```

Why a table instead of a single column on `empires`:

- Players often keep multiple tabs or machines on a long-running game.
- Password change can soft-revoke all rows for that `empire_id` (`revoked_at = now`).
- Expired / revoked history remains for audit; no hard deletes.
- No need to pick “which” session wins.

### Expiry and soft revoke policy

- **Max lifetime:** 1 year from token creation (`expires_at = created_at + 365 days`).
- **v1 recommendation: fixed expiry, no sliding window.** A token issued at login remains valid until that instant one year later or until soft-revoked. Simpler; matches “up to one year.”
- Optional later: sliding refresh (extend `expires_at` on use, still capped at 1 year from `created_at`). Not needed for v1.
- **Lookup validity:** token hash matches **and** `revoked_at IS NULL` **and** `expires_at > now`.
- **No hard deletes:** logout, password change, and any maintenance only set `revoked_at`. Do not `DELETE` token rows. Do not add a purge/cron that removes them.

### Issue points

Issue (or refresh into the response) a token when:

1. `login()` succeeds
2. Empire creation completes and the player is dropped into the normal authenticated UI
3. Password change succeeds (new token; old tokens revoked)
4. **First authenticated request that still used legacy `pass`** — after verifying the password, issue a token and have the **next** page render emit `token` instead of `pass`. That way, merely navigating once after deploy upgrades the browser’s forms without asking the player to log in again.

Do not issue a token on failed auth.

### Gate authentication (dual path)

Replace the single SQL password compare with ordered checks:

1. If `name` is empty → unauthenticated (as today).
2. Load empire by name (canonical capitalization from DB into `$_POST['name']` as today).
3. **Token path:** if `token` POST is non-empty → lookup `auth_tokens` by hash of submitted token, require matching `empire_id`, `revoked_at IS NULL`, `expires_at > now` → set `$authenticated` / admin flag.
4. **Password path:** else if `pass` POST is non-empty → shared verify helper from Part A (`password_verify` against `password_hash`, or plaintext match then upgrade); on success set `$authenticated`, queue token issue.
5. Else → unauthenticated at the gate.

Login form continues to POST `pass` (real password) through the same verify helper. After success, pages emit `token`, not `pass`.

Section handlers (`main.php`, `game.php`, `admin.php`, etc.) keep checking `$authenticated` / `$authenticated_as_admin` only — no change to call sites beyond the gate and form emitters.

### Form emitters

All sites that currently output:

```html
<input type=hidden name="pass" value="...password...">
```

should output (when a token is available for this request):

```html
<input type=hidden name="token" value="...opaque token...">
```

and **stop** outputting the password.

To avoid editing thirty files forever, **v1 recommendation:** introduce a small helper used by headers and pages, e.g. `authHiddenFields($vars)` that prints `name` + `token` (and does not print `pass`). Migrate emitters to the helper; leftover direct `pass` echoes are a checklist item before calling the project done.

Pages that today read `$empire['password']` for the hidden field (`gameHeader`, admin header, editProfile) must stop doing that entirely.

### Continuity (no forced logouts)

| Client state after deploy | Behavior |
|---------------------------|----------|
| New login | Password checked against plaintext or `password_hash` → upgrade if needed (null plaintext) → token issued → forms use `token` |
| Open tab with hidden `pass`, account not yet upgraded | Verify succeeds → write `password_hash`, **null `password`**, this tab gets `token` |
| Open tab with hidden `pass`, account **already** upgraded | `password_verify` against `password_hash` succeeds → this tab gets `token` |
| Open tab already on `token` | Token path succeeds until expiry / password change |
| Bookmark to `sc.php` with no credentials | Login page (unchanged) |

No mass invalidation on deploy. No email. No forced re-login. Plaintext is erased from the DB at first upgrade; form passwords remain acceptable for continuity because they can still verify against the hash.

### Revocation (soft delete only)

Soft-revoke tokens by setting `revoked_at = unix_timestamp()` (never `DELETE`):

- Password is changed → set `revoked_at` on **all** tokens for that empire
- Logout → if a token was presented, set `revoked_at` on **that** token only; other devices remain signed in until their tokens expire or are revoked
- Optional later: explicit “log out everywhere” → soft-revoke all tokens for that empire (not required for v1)

Logout UX stays the same: return to the public main page without credentials. A reused revoked token fails the gate because `revoked_at` is set.

---

## Interaction of A and B

```
                    ┌─ token ──────────────────────────────────┐
POST name + secret ─┤                                          ├→ $authenticated
                    └─ pass (verify hash, else plaintext+upgrade) ┘   │
                                                                      ▼
                                    if password path + not yet upgraded:
                                      write password_hash, NULL password
                                    ensure token for response forms
                                                                      ▼
                                    render pages with hidden token only
```

Hashing (new column) protects the database. Nulling `password` erases plaintext at first opportunity. Tokens protect the HTML/POST channel going forward. Submitting `pass` remains valid whenever it verifies against `password_hash` (or legacy plaintext before upgrade).

---

## Phased rollout

### Phase 0 — Schema

1. Drop composite primary key `(name, password)`; add **`PRIMARY KEY (name)`**. Keep unique `id`.
2. Add nullable **`password_hash varchar(255) NULL`**.
3. Alter **`password`** to allow `NULL` (legacy plaintext until upgrade).
4. Create `auth_tokens` table.

No behavior change yet if code is unchanged; safe to apply first.

### Phase 1 — Gate + login + helpers (behavior change)

1. Central verify / lazy-upgrade (hash + null plaintext) / token issue / token check helpers.
2. Switch `sc.php` gate and `login()` to the dual path described above.
3. New writes (createEmpire / editProfile) go to `password_hash` only; `password` stays NULL.
4. Fix Edit Profile prefills.
5. Point `standardHeader` / `gameHeader` / admin header (and then remaining pages) at `authHiddenFields()`.

After Phase 1, successful password use erases that row’s plaintext and moves clients onto tokens as they navigate. Sibling tabs still posting `pass` keep working via `password_verify` until they too receive a token.

### Phase 2 — Emitter sweep + cleanup

1. Finish converting every hidden `pass` emitter (including backups only if still reachable; prefer deleting dead duplicates later).
2. Grep for `empire['password']` used in HTML; eliminate.
3. Optional: metrics — count of empires with `password IS NOT NULL` (still pending lazy upgrade). No player-facing nag.
4. Do **not** add a hard-delete / purge job for `auth_tokens`.

### Phase 3 — (Optional, far later) Stop accepting `pass` on non-login POSTs

Only after virtually all clients are on tokens **and** we are willing to break ancient open tabs: reject `pass` on section handlers other than `login` / create-empire. **Not recommended soon.** Accepting a verifying `pass` is what keeps old open forms alive after plaintext wipe, and it costs little. Token remains the preferred path for all newly rendered pages.

---

## Threat notes (honest scope)

| Threat | Mitigated? |
|--------|------------|
| DB dump reveals all passwords | Yes for each account after its lazy upgrade (`password` nulled, only `password_hash` remains) |
| HTML form / referrer / proxy shows password | Yes, for newly rendered pages (token instead) |
| Stolen token from HTML | Token is bearer secret until expiry or password change — better than password (not reusable on other sites; revocable) |
| XSS steals hidden field | Still possible (token or password); out of scope |
| Online password guessing | Unchanged (no rate limit in v1); can be a later project |
| Game join passwords | Unchanged |
| Sibling tab still posting `pass` after upgrade | Still authenticates via `password_verify`; then receives `token` |

---

## Testing checklist (for implementation phase)

- [ ] New empire: `password_hash` set, `password` NULL; login works; forms contain `token`, not password
- [ ] Existing plaintext empire: first login succeeds; `password_hash` set; `password` is NULL; second login works via hash
- [ ] Open tab with hidden `pass` on not-yet-upgraded account: succeeds once, upgrades row, receives `token`
- [ ] Second tab still posting `pass` after upgrade: still authenticates via `password_verify`; receives `token`
- [ ] Wrong `pass` after upgrade rejected
- [ ] Token older than one year rejected
- [ ] Password change: updates `password_hash` only; `password` stays NULL; old tokens rejected; current tab gets new token; old hidden `pass` with previous password fails
- [ ] Admin (`is_admin`) works via token and via verifying `pass`
- [ ] Email-validation create path (if enabled): temp secret in `password_hash`; promotion works
- [ ] Edit Profile password fields empty; changing password works
- [ ] Logout returns to public page; presented token gets `revoked_at` set (row remains); reused token fails
- [ ] No code path in this feature issues `DELETE` against `auth_tokens` (or other tables)
- [ ] Identity check still fails with wrong/missing credentials (same user-visible errors)
- [ ] RSS feeds and game `password1`/`password2` unaffected
- [ ] Primary key is `name` only; inserts/updates that previously assumed composite PK still work

---

## Decisions

| Topic | Decision |
|-------|----------|
| Primary key | **`PRIMARY KEY (name)`**; drop `(name, password)`. Keep unique `id` for `auth_tokens.empire_id`. |
| Where the hash lives | **New column `password_hash`**; do not overwrite/reuse `password` for the hash. |
| Plaintext after upgrade | **`password = NULL` immediately** on successful upgrade. |
| Form `pass` after upgrade | **Accept** if `password_verify` succeeds against `password_hash`; then issue `token`. |
| Logout | Soft-revoke the presented token (`revoked_at`); other devices’ tokens remain until expiry or password change. |
| Token removal | **Soft deletes only** — `revoked_at`; never hard `DELETE` for this feature. |
| Sliding expiry | Fixed 1-year expiry for v1 (no sliding window). |
| Phase 3 | Optional / deferred; dual path (token or verifying `pass`) stays for v1. |

---

## Summary

| Today | After this project |
|-------|--------------------|
| Password in DB plaintext (`password` column) | Hash in `password_hash`; `password` nulled on first successful password use |
| PK `(name, password)` | PK `(name)` |
| Hidden `pass` = real password | Hidden `token` = opaque year-long session secret |
| Open tabs all share echoed password | New pages use tokens; old `pass` forms keep working if they verify against the hash |
| Email reset required by typical migrations | Not used; lazy upgrade only |
| Edit Profile shows current password | Fields blank; change still works |

Implementation should follow in a later change set after this design is accepted.
