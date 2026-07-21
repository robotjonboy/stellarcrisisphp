# Design: Admin — Spawn Open Games for Series

## Problem

A **series** is a reusable game template. A **game** is one open (or in-progress) instance of that template that players can join.

When a series is created through the software (`admin/createSeries.php` or `main/customSeries.php`), the create path:

1. Inserts the `series` row
2. Inserts `series_ship_type_options` (e.g. Jumpgate settings)
3. Calls `spawnGame($series_name)` to create the first empty, open game

Inserting series rows directly into the database skips steps 2 and 3. Those series appear in the admin series lists but have **no joinable game**, so they do not show up for players as open games.

`spawnGame()` already exists in `sc.php` and is the single place that creates empty games. There is no dedicated admin action to call it for series that were added outside the create-series UI. Resume series is the closest relative: after un-halting, it spawns one empty game if none exists — but only for halted series being resumed.

## Goal

Add an admin function that lets an authenticated admin spawn open games for series that need them, without recreating the series or using the resume/halt flow.

Primary use case: series restored or bulk-inserted from SQL dumps / manual DB work.

## Non-goals

- Do not change how series are created through the normal create-series UI.
- Do not generate maps, players, or systems at spawn time (that remains join-time behavior in `joinGame()` / `makemap.php`).
- Do not add a REST API; stay on the existing form-POST admin pattern.
- Do not auto-run on every page load or cron; this is an explicit admin action.
- Do not implement a full “import series from SQL” wizard in this feature.

## Background: what `spawnGame()` does

For a non-halted series, `spawnGame($series_name)`:

1. Increments `series.game_count`
2. Inserts a `games` row with the next `game_number`, copying timing fields and `game_type` from the series
3. Copies all `series_ship_type_options` rows into `game_ship_type_options` for the new game
4. Returns the new `game_id`

The new game is empty (`player_count = 0`, `closed = 0`). It becomes visible as an open slot. Map generation happens when the first player joins.

If the series is `halted`, `spawnGame()` does nothing.

## Related existing admin flows

| Action | Relevance |
|--------|-----------|
| Create series | Spawns game #1 after insert |
| Resume series | Spawns one empty game if `player_count = 0` count is zero |
| Halt series | Deletes empty games and blocks further spawn |
| Kill game | May respawn if no open games remain |
| Edit series | Updates template; Jumpgate options also applied to empty games |

The new feature should feel like a sibling of **Resume series** / **Kill game**: pick series (or act on a filtered set), confirm, report result.

## Proposed feature name

**Spawn open games** (admin menu label)

Internal page key: `spawnOpenGames` (matches naming like `resumeSeries`, `killGame`).

## Behavior

### Which series need a spawn?

A series **needs an open game** when:

- `halted = '0'` (or equivalent false), **and**
- There is **no** game with `series_id = series.id AND player_count = 0`

Notes:

- Matching resume-series logic (`player_count = 0`) is preferred over `closed = 0`, so a partially filled open game does not get a second empty slot from this tool.
- Series that already have an empty open game are skipped (idempotent).
- Halted series are listed separately or shown as ineligible; spawning them is refused unless the admin un-halts first (reuse resume, or optionally offer “un-halt and spawn” later — out of scope for v1).

### Single vs bulk

**v1 recommendation: bulk-capable UI with explicit confirmation.**

1. Show a table/list of series that currently need an open game.
2. Default: all listed series selected (or a “Select all” control).
3. Admin can deselect individual series.
4. Submit → for each selected series, call `spawnGame($name)`.
5. Report success/skip/fail counts and names in the empire message (same pattern as other admin actions).

Also support acting on **one** series from the list (checkbox / single select is fine if a multi-select table is too heavy for the current UI style). Closest visual match to existing pages is a multi-select or checkbox list under a title, with Spawn / Cancel — similar to halt/resume’s dropdown, but a list is better here because the interesting set is “series missing games,” not “all series.”

### Ship-type options gap

DB-inserted series may also lack `series_ship_type_options`. `spawnGame()` still creates the game, but copies zero option rows. Jumpgate (sc3) series especially need those options.

**v1 recommendation:**

- Before spawning (or as a pre-check in the UI), detect series with `game_type = 'sc3'` (or any series) that have **no** `series_ship_type_options` rows.
- Surface a warning on the admin page for those series.
- Do **not** invent Jumpgate defaults in v1 unless we already have a shared helper from create-series that can be reused safely.
- Prefer: warn, and optionally skip sc3 series missing options until the admin fixes options via Edit series (or a follow-up task).

If create-series already has a clear default Jumpgate insert that can be extracted, a later iteration can “ensure default ship options then spawn.” Call that out as Phase 2.

### `game_count` / numbering

Always go through `spawnGame()`. Do not hand-insert `games` rows. That keeps `game_count` and `game_number` consistent with the rest of the server.

For freshly inserted series with `game_count = 0`, the first spawn becomes game #1, which is correct.

### Idempotency and safety

- Re-running the tool after a successful spawn should show an empty “needs spawn” list (or skip all).
- Never spawn for halted series in v1.
- Never spawn a second empty game if one already exists.
- Admin-only (`$authenticated_as_admin`), same gate as other admin pages.
- No change to player-facing custom series.

## UI / UX

Follow existing admin patterns in `admin/*.php`:

- `standardHeader`, hidden `name` / `pass` / `section=admin` / `page=spawnOpenGames`
- `drawButtons`, spacerule, centered controls, Cancel returns to admin home
- Empire message for results

### Screen contents

1. **Title:** Spawn Open Games
2. **Short explanation:** One sentence — creates the empty joinable game for series that don’t have one (e.g. after a DB import).
3. **List of candidates** (series needing spawn), including at least:
   - Series name
   - `game_type`
   - `game_count`
   - Warning flag if ship options are missing
4. If the candidate list is empty: message “All active series already have an open game.” + Cancel/Back.
5. **Spawn** submit (enabled only when at least one series is selected)
6. **Cancel**

### Admin menu

Add under Administration dropdown in `mainPage_admin()`:

- Label: `Spawn open games`
- Value: `spawnOpenGames`

Wire through:

- `require('spawnOpenGames.php');` in `admin.php`
- `case 'spawnOpenGames':` in `adminAction` → `spawnOpenGames_processing`
- `case 'spawnOpenGames':` in `mainPage_admin_processing` → `spawnOpenGames($vars)`

## Processing logic (pseudo)

```
on Cancel → message + mainPage_admin
on Spawn:
  for each selected series_id:
    load series
    if halted → skip (count as skipped)
    if exists game with player_count = 0 → skip
    if (policy) missing ship options for sc3 → skip or warn-and-spawn (choose one; prefer skip with message)
    spawnGame(series.name)
    record success
  sendEmpireMessage with summary
  redisplay spawnOpenGames page (refreshed candidate list)
```

Prefer **mysqli / `$select->fetch_assoc()`** style consistent with modernized pages; do not copy the legacy `mysql_fetch_array` / `mysql_result` usage still present in `resumeSeries.php`.

## Files to add / touch (implementation later)

| File | Change |
|------|--------|
| `admin/spawnOpenGames.php` | **New** — form + processing |
| `admin/admin.php` | require, menu option, routing cases |
| (optional) small helper in `sql.php` or `sc.php` | e.g. `seriesNeedsOpenGame($series_id)` — only if it keeps call sites clearer |

No schema migration required.

## Testing plan

1. Insert a series via SQL with `halted = 0`, `game_count = 0`, no games → appears in candidate list → Spawn → one open game appears on Game List; `game_count` becomes 1.
2. Run Spawn again → series no longer listed; no duplicate empty game.
3. Series with an in-progress game (`player_count > 0`) but no empty game → listed and spawn creates the next open slot (same as join/resume maintenance intent).
4. Halted series → not spawned by this tool (not listed, or listed disabled).
5. sc3 series without `series_ship_type_options` → warning and agreed skip/spawn behavior verified.
6. Cancel returns to admin home with no DB changes.
7. Non-admin cannot reach the page.

## Phased delivery

### Phase 1 (this feature)

- Admin page listing series missing an empty open game
- Bulk/single spawn via existing `spawnGame()`
- Warnings for missing ship-type options; skip unsafe sc3 cases if needed
- Menu + routing wiring

### Phase 2 (optional follow-ups)

- “Ensure default Jumpgate / ship options” for series missing `series_ship_type_options`, reusing create-series defaults
- Fix resume-series to use mysqli consistently (related hygiene, not required for this feature)
- One-shot “repair after import” that also validates `game_type` and other columns missing from old dumps

## Open questions

1. **Partially filled open games:** Confirm we only care about `player_count = 0` (recommended), not “any `closed = 0`.”
2. **Missing ship options:** Skip vs spawn-anyway for sc3? Recommendation: skip with a clear message pointing at Edit series.
3. **Include custom series?** Recommendation: yes — any non-halted series missing an empty game, whether `custom` is 0 or 1.
4. **Should the page also offer “spawn even if an empty game exists”?** Recommendation: no — that duplicates slots and is rarely useful; kill/resume already cover edge cases.

## Summary

Expose `spawnGame()` through a small admin screen that finds active series without an empty open game and creates those games on demand. That closes the gap between DB-inserted series and series created through the software, without changing the core spawn implementation or the normal create-series path.
