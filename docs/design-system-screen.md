# Design: System Screen — Local Map and Local Orders

## Problem

When a player clicks a system on the map, they land on the **system screen**: the Systems UI filtered to that one coordinate (`zoomed_planet` in `game/systems.php`). Today that screen shows world stats, rename / max population (if owned), ship inventory as counts only, and scouting-report inclusion.

Two friction points remain:

1. **Local context is missing.** The player sees jump coordinates as text, but not a picture of how this system sits among its neighbors. Returning to the full map (or mini-map zoom) just to see adjacent known systems is costly on large maps and especially on phones.
2. **Orders require a detour.** Changing ship or fleet orders means leaving the system screen for Ships or Fleets, finding the units among every unit in the game, then navigating back. On large maps that list is long; on mobile it is especially painful.

Neither gap should remove existing system-screen behavior.

## Goal

Extend the **single-system** view (map click → Systems with `zoomed_planet`) so that:

1. The player sees a **small local map** of this system and the systems around it (at most nine cells: a 3×3 neighborhood including diagonals), showing only **known** systems and the **links** between them.
2. The player can **set orders** for their ships and fleets that are **currently located at this system**, using the same order rules as the Ships / Fleets screens.

The full Systems list (no `zoomed_planet`) is unchanged except where shared helpers are extracted.

Deliverable of this document: a concrete design to implement later. **No code in this phase.**

## Non-goals

- Do not replace or redesign the full galaxy map / mini-map.
- Do not change fog-of-war rules (explored vs scouted vs unknown).
- Do not add REST/AJAX order APIs; stay on form-POST like the rest of the game.
- Do not rebuild the UI in a modern framework or canvas/WebGL for this feature.
- Do not change game rules for movement, exploration, diplomacy, or valid orders.
- Do not add order editing on foreign systems beyond what Ships already allows for ships present there (read foreign inventories; edit only own units).
- Do not change the history viewer or admin UIs.
- Do not remove rename, max population, scouting include, homeworld origin shift, or inventory summary.

## Terminology

| Term | Meaning |
|------|---------|
| **Systems list** | Systems action with no `zoomed_planet` — all explored systems |
| **System screen** | Systems action with `zoomed_planet` set (from map / mini-map click) |
| **Known system** | Present in `explored` **or** `scouting_reports` for this player |
| **Local map** | New 3×3 neighborhood widget on the system screen |
| **Grid neighbor** | One of the eight surrounding galactic cells (N/E/S/W + diagonals) |
| **Link / jump** | An actual connection in `systems.jumps` (orthogonal only; max four). Grid adjacency ≠ link |

In code and UI, a “world” is a **system** row; there is no separate world table.

## Background: current behavior

### Entry path

1. Map / mini-map cell: `<input type=image name="system:{local_coords}">` (`game/map.php`).
2. `mapScreen_processing()` sets `$vars['zoomed_planet']`.
3. `gameAction()` routes Map → Systems (or Mini-Map → Map zoom, then another click → Systems).
4. `systemsScreen($vars)` filters to that coordinate when `zoomed_planet` is set; otherwise lists all explored systems.
5. If the coordinate is scouted but not explored, `systemsScreen_fromScoutingReport()` shows a snapshot instead.

### What the system screen already does

- Icon, name (editable if owned), owner, local location, minerals / fuel / agriculture, population, max population (editable if owned).
- Jump list as localized coordinates.
- Ship inventory by owner / type (counts only).
- Scouting checkboxes + Scouting Report / Full Scouting Report (disabled when `diplomacy == 2`).
- On own homeworld: change map origin (“New homeworld coordinates”).

Processing (`systemsScreen_processing`): rename + max pop for owned systems; optional origin shift. It does **not** handle ship/fleet orders.

### Map, coordinates, links

- Storage is **galactic** `"x,y"`. UI often uses **local** coords via `xlateToLocal` / `xlateToGalactic` and `players.map_origin`.
- Links live in `systems.jumps` (space-separated galactic coords; orthogonal only).
- Map drawing builds bidirectional `$jump_presence` and paints `horz.gif` / `vert.gif`.
- Unknown cells are empty; scouted-only systems appear differently from explored (grey treatment on map).

### Orders today

- Orders are set only on Ships / Fleets screens (`game/ships.php`).
- Fields: `ship_orders[{id}]`, `fleet_orders[{id}]` (values like `order` or `order:local_arg`).
- Validation: `getValidOrders()` / `getFleetOrders()`; apply via `shipsScreen_processing` / `fleetsScreen_processing`.
- Ships in a fleet use `fleet_id > 0` and typically `orders = 'fleet'`; free ships have `fleet_id = 0`.
- Move targets are jumps ∩ **explored** (Science `explore` is the unexplored-jump exception).

There is no separate order API.

---

## Design principles

1. **Additive** — Keep every existing system-screen control; place new UI around it.
2. **Single-system only** — Local map and local orders appear when `zoomed_planet` is set, not on the full Systems list.
3. **Reuse, don’t fork** — Local map reuses map icon / jump-presence ideas; orders reuse `getValidOrders` / `getFleetOrders` and existing processors (or thin wrappers).
4. **Classic SC** — HTML table map fragment, form POSTs, same labels and order menus as Ships/Fleets.
5. **Mobile-friendly by locality** — The win is a short list of units at *this* system and a tiny map, not a new navigation model.
6. **Known only** — Never reveal unknown systems or invent links the player cannot see from known data.

---

## Feature 1 — Local neighborhood map

### Intent

Show the clicked system in context: itself plus the eight surrounding galactic cells, capped at nine positions. Only known systems appear as systems; links between known systems (and stubs toward known data) are drawn like the main map.

### Geometry

Center cell = galactic coordinates of `zoomed_planet`.

```
(x-1,y+1)  (x,y+1)  (x+1,y+1)
(x-1,y)    (x,y)    (x+1,y)
(x-1,y-1)  (x,y-1)  (x+1,y-1)
```

(Exact screen orientation should match the main map’s Y direction so players are not mentally flipped.)

This is a fixed 3×3 **window**, not “only systems that have jumps.” Empty grid cells with no known system stay empty. Diagonals are included even though jumps are never diagonal: a diagonal cell may still hold a known system that matters for planning.

### What appears in a cell

| Player knowledge | Display |
|------------------|---------|
| Explored | Same icon rules as map / systems screen (own empire icon, alien icon, empty planet, annihilated). Name and/or local coords as space allows (prefer compact: icon + local coord; name optional or truncated). |
| Scouted only | Scouted styling consistent with main map (grey name / distinct border); no live secrets beyond the scouting snapshot already used elsewhere. |
| Unknown / no system | Empty cell (blank). Do not show a planet icon. |

Own ships at the center (or optionally a tiny presence hint on other cells) are **not required** for v1; inventory and order sections cover presence at the focused system.

### Links

- Build jump presence for the systems that fall inside the 3×3 and are known, using each known system’s `jumps` field (same bidirectional idea as `map.php`).
- Draw `horz.gif` / `vert.gif` between adjacent cells when a jump exists **and** both endpoints are known **or** the jump is from a known system toward a neighbor cell (see stubs below).
- Diagonals never get jump graphics (rules forbid diagonal links).

**Stubs / edges:**

- A jump from the center (or any known cell) to a coordinate **outside** the 3×3: optional short stub at the edge pointing outward, or omit stubs in v1 and rely on the existing text jump list. Recommendation: **omit outward stubs in v1** to keep the widget simple; the text jump list remains.
- A jump toward an **unknown** in-grid neighbor: show a stub into the empty cell (player knows a link exists from the known side) **or** omit until the destination is known. Recommendation: **show stub from the known side** so the local map teaches connectivity without revealing the destination system.

### Interaction

- **Center cell:** not a navigation control (already viewing it); may be visually highlighted (border / brighter label).
- **Other known cells:** clickable like the main map (`system:{local_coords}` image submit or equivalent), posting back into Systems with a new `zoomed_planet`. That replaces the current system screen with the neighbor’s — expected and useful for hopping along a front.
- Scouted-only neighbors: same click path; land on `systemsScreen_fromScoutingReport` when not explored.
- Do not make empty cells clickable.

### Placement on the page

Recommended layout for the single-system screen (top → bottom):

1. Existing ratios / header chrome (unchanged).
2. **Local map** (new) — above or beside the existing system detail block.
3. Existing system detail (icon, name, stats, max pop, jumps text, scouting checkbox, homeworld origin).
4. Existing ship inventory summary (counts).
5. **Local orders** (new) — see Feature 2.
6. Existing action buttons (Scouting Report, etc.).

On narrow viewports, stack: local map full width under the title area, then detail, then orders. On wide desktops, local map may sit to the right or left of the detail block if that fits classic nested tables without breaking the Systems list page.

### Implementation notes (for later coding)

- Prefer integer x/y loops over string `BETWEEN` on coordinates (string ranges are brittle for negatives / multi-digit coords).
- Query known systems in the nine coordinates: `explored` ∪ `scouting_reports` for this `player_id`, keyed by galactic coord.
- Extract a small helper (e.g. `localNeighborhoodMap($player, $center_galactic)`) rather than copying all of `mapScreen`.
- Icon sizing: closer to mini-map (e.g. 20×20) than full map (40×40) so nine cells stay compact on phones.
- Respect `$server['local_coordinates']` for labels and click targets.
- Show the widget only when `zoomed_planet` is set (including scouted-only path, so scouted systems also get neighborhood context).

### Edge cases

- Map edge / missing systems: empty cells; map still 3×3.
- Annihilated known systems: same annihilated icon as elsewhere.
- Shared HQ explored systems: treat as explored (already in `explored`).
- Blood games / diplomacy quirks: map widget independent of scouting buttons.

---

## Feature 2 — Orders for ships and fleets at this system

### Intent

Let the player change orders for **their** ships and fleets whose `location` equals the focused system’s galactic coordinates, without opening the global Ships/Fleets lists.

### Scope of units

Include:

- Fleets owned by the player with `fleets.location = center`.
- Free ships owned by the player with `ships.location = center` and `fleet_id = 0`.

Exclude from editable order rows (v1):

- Ships that belong to a fleet (`fleet_id > 0`) as separate order dropdowns — they follow fleet orders. Show them nested under the fleet (read-only type/BR summary) or omit detail and only show the fleet row, matching Fleets-screen mental model.
- Foreign ships (inventory summary already covers visibility).
- Units at other locations.

Optional v1 stretch (only if cheap): allow expanding a fleet to set individual ship orders the way Fleets screen does. Default recommendation: **fleet-level orders + free ships only** for first ship; fleet member editing remains on Fleets.

### UI

Reuse Ships/Fleets presentation patterns already adapted for mobile (`shipsTable` card layout where applicable):

| Column / field | Content |
|----------------|---------|
| Name | Ship or fleet name (editable only if Ships already allows rename in the same submit path — see open questions; v1 can be display-only names to reduce coupling) |
| Type / composition | Ship type, or fleet size summary |
| BR | As on Ships |
| Orders | `<select name="ship_orders[id]">` / `fleet_orders[id]` built via `fixOrders` + `getValidOrders` / `getFleetOrders` |

Empty state: short line — “No ships or fleets at this system.”

Foreign system with own ships present: still show own units’ order controls (raid / transit cases). Owned-system-only edits for name/max pop remain as today.

### Submit / processing

Orders must apply through the **same validation and write path** as Ships/Fleets:

1. Preferred: from `systemsScreen_processing` (or a sibling called when previous play was Systems), if `ship_orders` / `fleet_orders` are present, call shared helpers extracted from `shipsScreen_processing` / fleet order updates — or invoke those processors’ order portions with the posted subset.
2. Ensure `game.php` routing runs systems processing when returning from the system screen submit (today rename/max-pop already depends on play = Systems; map click sets play from Map first — existing quirk; local orders submit will be an explicit Systems submit and is fine).
3. After save, redisplay the same system screen (`zoomed_planet` preserved via hidden field).

**Do not** invent new order strings; keep `order:local_arg` encoding and galactic storage for move targets.

### Coexistence with rename / max pop / scouting

One form (or clearly separated forms) on the page:

- **Recommendation:** one form wrapping detail + orders, with existing Systems submit buttons. Order selects POST together with rename/max pop. Scouting report buttons already post selected systems; ensure order fields either (a) are outside the scouting-only path or (b) round-trip harmlessly when sending a report.
- If a single form is risky for scouting actions, use two forms: (1) system edits + orders + “Update System”, (2) scouting checkboxes + report buttons — matching any existing split if present.

Hidden fields required: `zoomed_planet` (local), existing `system_ids`, name/pass game fields as elsewhere.

### Security / integrity

- Only update ships/fleets where `player_id` / owner matches the session player and `location` matches the focused system (reject tampered IDs for units elsewhere).
- Re-run `getValidOrders` / `getFleetOrders` server-side; ignore invalid posted orders (same as Ships).

### Mobile rationale

This is the main mobile win called out in the request: on a 40×40 game, scrolling the full ship list to order one stack at a frontier system is painful. The system screen is already the natural “I am looking at this place” context after a map tap.

---

## Out of scope for v1 (possible later)

- Showing foreign order estimates or cloaked guesses on the local map.
- Pathfinding / multi-hop move UI from the local map.
- Editing builds on the system screen (Build screen remains).
- Defaulting map clicks to open system screen with orders focused / deep links from messages.
- Outward jump stubs beyond the 3×3.
- Per-ship orders inside fleets on this screen.

---

## Open decisions

Resolve before or during implementation:

| # | Question | Recommendation |
|---|----------|-----------------|
| 1 | Local map beside detail vs always above? | Above on mobile; beside on desktop if table layout allows without breakage. |
| 2 | Jump stubs into unknown in-grid cells? | Yes, stubs from known side only. |
| 3 | Outward stubs past the 3×3? | No in v1; keep text jump list. |
| 4 | Fleet member ship orders on system screen? | No in v1; fleet + free ships only. |
| 5 | Ship/fleet rename on system screen? | Display names only in v1; rename stays on Ships/Fleets unless reuse is trivial. |
| 6 | Scouted-only center: show local map? | Yes. |
| 7 | Click neighbor → same Systems zoom? | Yes. |
| 8 | Shared extract of order processing vs call existing processors? | Extract small shared functions if duplication is messy; otherwise call existing processing with filtered POST. |

---

## Implementation plan (when coding starts)

Suggested sequence so each step is testable:

1. **Helpers** — Resolve 3×3 galactic coords; load known systems (explored + scouted) for those coords; build jump-presence subset.
2. **Render local map** — HTML table widget; icons; links; center highlight; neighbor clicks; wire only into `systemsScreen` / scouted variant when `zoomed_planet` set.
3. **Query local units** — Player fleets + free ships at center location.
4. **Render order controls** — Reuse order menu builders from `ships.php`.
5. **Processing** — Accept and validate order POSTs from Systems; persist; preserve `zoomed_planet` on redisplay; keep rename/max-pop/scouting working.
6. **CSS** — Compact neighborhood map; ensure order table uses existing `shipsTable` / mobile rules; no desktop regression on Systems list.
7. **Manual test matrix** — See below.

No schema changes expected. No new tables.

### Likely files

| File | Role |
|------|------|
| `game/systems.php` | Primary UI + processing hooks |
| `game/ships.php` | Shared order validation / processing reuse |
| `game/map.php` | Reference for icons / jump drawing (possible shared helper later) |
| `game/game.php` | Only if routing / play handling needs a tweak |
| `styles.css` | Compact local map + stacking |
| `sql.php` | Only if new thin queries belong with `explored()` / `getSystem()` |

---

## Test plan

### Local map

- [ ] Explored center with eight known neighbors: all nine show; links match `jumps`.
- [ ] Unknown neighbors: empty cells; no info leak.
- [ ] Scouted-only neighbor: scouted styling; click opens scouted system view.
- [ ] Diagonal known system with no jump to center: system shown, no diagonal link graphic.
- [ ] Orthogonal jump to unknown in-grid cell: stub or empty per decision #2.
- [ ] Jump only to outside 3×3: text jump list still correct; no crash.
- [ ] Local vs galactic labels match main map for same player origin.
- [ ] Systems list (no zoom) unchanged — no local map.

### Orders

- [ ] Free ship at system: change move / standby; persists; appears on Ships screen.
- [ ] Fleet at system: change fleet order; members follow as today.
- [ ] Invalid forged ship id / wrong location: ignored.
- [ ] Science explore / cloaker / builder specials still only appear when valid.
- [ ] Rename + max pop still save when orders also posted.
- [ ] Scouting report still sends; does not wipe orders unexpectedly.
- [ ] Foreign system with own ships: can order own ships; cannot edit foreign max pop/name.
- [ ] No units: empty message; page otherwise normal.

### Mobile / regression

- [ ] Narrow viewport: local map readable without horizontal page scroll (or contained scroll).
- [ ] Order selects tappable; not clipped by map.
- [ ] Full map → click system → orders → update → still on same system.
- [ ] Mini-map zoom → click → system screen still works.

---

## Success criteria

- Player can understand local connectivity of a focused known system without returning to the full map.
- Player can issue legal orders to own fleets and free ships at that system in one round trip.
- Existing system-screen features behave as they do today.
- Fog of war and order legality unchanged.
- Desktop Systems list remains the familiar multi-system table.

## Relation to other docs

- Complements [design-mobile-usability.md](design-mobile-usability.md): local orders and a tiny neighborhood map reduce reliance on the full ship list and full galaxy table on phones, without requiring a canvas map rewrite.
- Does not overlap [design-spawn-open-games.md](design-spawn-open-games.md).
