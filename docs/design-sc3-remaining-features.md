# Design: Remaining Stellar Crisis v3 Features

## Problem

The PHP port of Stellar Crisis supports `game_type = sc3` and has implemented **Jumpgate**, the first of four ship types introduced in classic SC 3.0. Three v3 ships remain unimplemented:

- **Carrier**
- **Builder**
- **Morpher**

Classic v3 also depends on a rich set of **series options** (tech access tiers, ship-type costs/loss/range, ship limits, homeworld resources, max ag ratio, and more). The PHP port only partially models these:

- `series_ship_type_options` / `game_ship_type_options` exist and are used almost exclusively for Jumpgate.
- The tech screen treats all v2 ship types as always developable; sc3 only bolts on Unrestricted entries from ship-type options.
- Restricted tech caps, trade-ins, Developed starting techs, Barred techs, and most bestiary parameters are missing or hardcoded at the server level.

Without the three ships and the series-option surface, authentic v3 series (Mad Scientist, Shifting Sands, Galactic Powers, etc.) cannot be configured or played correctly.

## Goal

Complete SC v3 feature parity for gameplay and series configuration:

1. Implement **Carrier**, **Builder**, and **Morpher** with behavior matching classic SC 3.0 (`reference/sc.pike` and Eric Robinson’s terminology notes).
2. Generalize **ship-type options** so all sixteen techs can be Developed / Unrestricted / Restricted / Barred, with per-type costs, loss, and range where applicable.
3. Add remaining **series-level** knobs needed to reproduce bestiary series (max ag ratio, ship limit, initial tech, homeworld resources, planet create cost, max planets, max allies, min pop to build, restricted/trade-in caps, stargate range, engineer loss).
4. Wire admin / custom-series UI, join-time starting techs, build costs, update-phase resolution, and series detail display through one consistent model.

Primary references:

- `reference/ericrobinsonterms.md` — definitions and ship behavior
- `reference/chasmysbestiaryv3.md` — historical series parameter catalog
- `reference/sc.pike` — authoritative update-order and combat logic
- Existing Jumpgate implementation — pattern to generalize

Deliverable of this document: a concrete design to implement later. **No code in this phase.**

## Non-goals

- Do not revive **virgin morpher** / “unkillable ship” cloaker-morph quirks from pre–3.01.8 pike (historical only).
- Do not intentionally recreate the **wormhole** builder/gate bug; treat as out of scope.
- Do not port Almonaster-specific rules or UI.
- Do not redesign the overall game UI or move off form-POST admin / game screens.
- Do not require seeding every bestiary series in the first implementation (seeding is a follow-on once knobs exist).
- Do not change sc2 game rules for series that remain `game_type = sc2`.
- Do not implement MkII or other lost variants.

## Terminology

| Term | Meaning |
|------|---------|
| **sc2 / sc3** | `series.game_type` / `games.game_type` values in this codebase |
| **Ship-type options** | Rows in `series_ship_type_options` copied to `game_ship_type_options` at spawn |
| **Status** | Tech access tier for a ship type in a series: Developed, Unrestricted, Restricted, Barred |
| **Developed** | Known at join (starting techs) |
| **Unrestricted** | Freely purchasable with a tech selection |
| **Restricted** | Purchasable only while under the series restricted-tech cap; may be forgotten via trade-in |
| **Barred** | Absent from the series (not developable) |
| **Trade-in** | Forgetting a restricted tech so it no longer counts against the cap (no tech refunded) |
| **Loss** | Permanent BR (and max BR) reduction for Carrier absorb, Engineer open/close, Jumpgate jump, Morpher morph |
| **Range multiplier** | PHP convention: gate range = `floor(BR × multiplier)`. Blank/null = infinite. Bestiary “2.5BR” = multiplier `2.5` |
| **Range divisor** | Pike convention: range = `BR / divisor`, divisor `0` = infinite. Reciprocal of multiplier when converting |
| **Planet create cost** | Series threshold; builders succeed if total BR of creators ≥ cost / 10 |
| **Morpher flag** | Persistent mark that a ship *is* a morpher even after its displayed `type` changes |

Ship abbreviations (bestiary): At Attack, Bu Builder, Ca Carrier, Cl Cloaker, Co Colony, Do Doomsday, En Engineer, Jg Jumpgate, Mf Minefield, Mo Morpher, Ms Minesweeper, Sa Satellite, Sc Science, Sg Stargate, Tf Terraformer, Tr Troopship.

## Background: current behavior

### What already works

- Twelve classic ship types plus **Jumpgate** in `ship_types` (`version` v2 / v3).
- Jumpgate orders, range checks, loss on jump, special build/maint costs via ship-type options.
- Jumpgate annihilated-destination restriction in order listing.
- Series create/edit/custom UI fields for Jumpgate status, range multiplier, loss, build, maint.
- `build_cloakers_cloaked`, `cloakers_as_attacks`, avg resources, diplomacy ladder, tech multiple, visible builds, map options.
- Players table already has `traded_in` (unused or incomplete relative to full trade-in rules).
- UI stubs in `game/ships.php`: Builder `create` destination orders; Morpher morph-into-tech orders and italic type display when a `morpher` field is set.

### Gaps

| Area | Gap |
|------|-----|
| Ship types DB | No Carrier, Builder, Morpher rows |
| `ships` schema | No `morpher` column (UI already checks it) |
| Combat (`update.php`) | No Carrier DEST absorption |
| Update orders | No Builder create processing; no Morpher morph processing |
| Tech screen | sc3 Unrestricted bolt-on only; Restricted / Developed / Barred / trade-ins incomplete |
| Starting techs | Hardcoded `"Attack Science Colony"` in map/join path |
| Build costs | Only Jumpgate special costs applied |
| Engineer loss | Server global `$server['engineer_br_loss']`, not per series |
| Min pop to build | Server global `$server['builder_population']`, not per series |
| Stargate | No v3 range limit |
| Series table | Missing max ag ratio, max ships, initial tech, HW resources, planet create cost, restricted/trade-in caps |
| Series detail UI | Jumpgate-only ship-type options display |
| Builder cost reference | `ships.php` reads `$series['builder_cost']` but column does not exist on `series` |

### Jumpgate as the pattern to generalize

`spawnGame()` copies all `series_ship_type_options` into `game_ship_type_options`. Runtime code loads them onto `$series['ship_type_options']` / `$game['ship_type_options']` keyed by ship type. Build, tech, and update already special-case Jumpgate. The design extends that table and removes the Jumpgate-only assumption.

## Proposed design

### 1. Ship-type options model

**One options row per ship type that participates in the series**, for sc3 games. Status is always present. Extra columns used when relevant:

| Ship type | status | range_multiplier | loss | build_cost | maintenance_cost |
|-----------|--------|------------------|------|------------|------------------|
| Jumpgate | ✓ | ✓ | ✓ | ✓ | ✓ |
| Stargate | ✓ | ✓ (v3) | — | —* | —* |
| Carrier | ✓ | — | ✓ | ✓ | ✓ |
| Builder | ✓ | — | — | ✓ | ✓ |
| Morpher | ✓ | — | ✓ | ✓ | ✓ |
| Engineer | ✓ | — | ✓ | —* | —* |
| All others | ✓ | — | — | —* | —* |

\*Base mineral formulas still apply: `(BR+4)²` build and `2×BR` maint, plus special costs when set. Satellites and minefields remain fuel-exempt as today.

**Status vocabulary** (align with pike; clean up Jumpgate naming):

| Canonical status | Pike equivalent | Meaning |
|------------------|-----------------|---------|
| `Developed` | Developed | Starting tech |
| `Unrestricted` | Allowed | Freely developable |
| `Restricted` | Restricted | Counts against restricted cap |
| `Barred` | Not Allowed | Absent |

PHP Jumpgate currently uses `Available`. Treat **`Available` as an alias of `Developed`** during migration, then prefer `Developed` in new UI.

For **sc2** series: do not require options rows; keep today’s implicit “all twelve classic types unrestricted, no v3 types” behavior.

**Planet create cost** is series-level (not a Builder options column), because multiple builders share one threshold.

### 2. Series-level columns

Add to `series` (and copy onto games at spawn where the codebase already snapshots series fields onto games, or read from series for the game’s `series_id` — follow existing patterns per field):

| Column | Purpose | Notes |
|--------|---------|-------|
| `max_ag_ratio` | Cap agriculture ratio used for population growth | Bestiary “Max Ag Ratio”; default high (e.g. 100) for sc2-like feel |
| `max_ships` | Max ships including building/dismantling | `0` = unlimited |
| `initial_tech` | Tech level for players joining before first update | Late joiners still take highest existing tech level (classic rule) |
| `hw_ag`, `hw_min`, `hw_fuel` | Homeworld resource ratings | Starting population = HW agriculture |
| `min_pop_to_build` | Population for builder status | Replaces reliance on `$server['builder_population']` for series-aware play |
| `planet_create_cost` | Builder create threshold | Success if Σ BR ≥ cost / 10 |
| `max_planets` | Cap on systems in a game (map + builder creates) | Default `1000` (pike’s hard-coded value). Custom series may not exceed `$server['max_planets_custom']` (default `2000`) |
| `max_restricted_techs` | Cap on restricted techs known | Pike `ar_tech` |
| `max_trade_ins` | Trade-in opportunities per empire | Pike `tr_tech` |
| `max_allies` | Cap on concurrent alliances per player | NULL = unlimited; `0` = none; e.g. Galactic Powers uses `1`. Copied onto `games.max_allies` at spawn (enforced in diplomacy) |

Optional follow-on (not required for first playable v3):

| Column | Purpose |
|--------|---------|
| `range_ag`, `range_min`, `range_fuel` | Randomize averages in `[avg, avg+range]` (Snowball-style) |

**Diplomacy** still gates whether alliance/truce/trade exist at all (Blood / Grudge / Cutthroat). `max_allies` further limits how many alliances each player may hold when alliance is allowed.

**Engineer loss** and **Stargate range** live on ship-type options (Engineer.loss, Stargate.range_multiplier), not as duplicate series columns.

### 3. Player / tech state

| Field | Role |
|-------|------|
| `players.techs` | Space-separated known types (unchanged format) |
| `players.traded_in` | Count of trade-ins used |
| New: restricted-techs counter | Count of currently known techs that are Restricted in this game’s options (or store count of restricted developments; must stay consistent with forget/trade-in) |

On join:

1. Set `techs` from all options with status `Developed` (not hardcoded Attack/Science/Colony when sc3 options exist).
2. Set starting `tech_level` from `initial_tech` when joining before first update; otherwise classic “highest existing” rule.
3. Initialize trade-in / restricted counters to 0.

Tech screen (sc3):

- List Developed as owned starting techs.
- Offer Unrestricted when selections remain.
- Offer Restricted only if known restricted count &lt; `max_restricted_techs`.
- Offer trade-in (forget) for known Restricted techs while trade-ins remain; decrement counters; do not grant a free replacement tech.
- Never offer Barred types.
- sc2 path unchanged.

### 4. Three ships

#### Carrier

- Mobile mid-combat ship; no special orders.
- During DEST allocation in `update.php`, when a ship would be destroyed by DEST:
  - If type is Carrier and `br > carrier_loss`: subtract loss from `br` and `max_br`, subtract absorbed DEST equal to `(new_br)²` (fuel-ratio adjusted like other DEST math), leave ship alive, record damage missive.
  - Else destroy as today.
- Carrier does not absorb DEST aimed at allies.
- Still vulnerable to 2DV wipe and minefield explosion.
- Build: apply options build/maint costs like Jumpgate.

#### Builder

- Mobile post-combat ship.
- Order `create` to an adjacent empty galactic cell (already listed in `getValidOrders` stub).
- Resolution (post-combat, with/near gate phase per pike ordering):
  1. Group create attempts by destination.
  2. If the game already has `num_planets >= max_planets`, skip that destination entirely (pike leaves those builders unconsumed).
  3. Sum BR of participating builders; if ≥ `planet_create_cost / 10`, create system with avg ag/min/fuel, name it, mark explored for creator(s), increment `num_planets`.
  4. Open bidirectional jumps between the new system and every origin system builders operated from (single- or multi-origin).
  5. Otherwise (attempted create under the planet cap): consume all participating builders whether success or failure.
- History / missives for create success (and preferably failure notification — pike notes this as incomplete).
- UI offers create for any adjacent empty cell (pike); do **not** gate the order list on a single ship’s BR — multiple builders pool BR at resolution.

**Max planets:** Lugdunum pike used a server-global hard cap (`int max_planets = 1000`) with no per-series setting. This port makes it **per series**:

| Setting | Value |
|---------|--------|
| `series.max_planets` | Per-series cap; default **1000** |
| `$server['max_planets_custom']` | Ceiling for **custom series** create/edit; default **2000** |

Enforcement:

- Create is refused when that game’s system count is already ≥ `series.max_planets` (pike leaves those builders unconsumed).
- Count includes systems from map generation and successful builder creates (pike `num_planets`).
- Custom series UI validates `max_planets ≤ $server['max_planets_custom']`.
- Admin series create/edit is not bound by the custom ceiling (admins may set any positive value), unless we later add a separate admin ceiling.

Default `$server['max_planets_custom'] = 2000` in `serverconfig.php`.

#### Morpher

- Mobile special ship; add `ships.morpher` enum/tinyint, set `1` when built as Morpher, never cleared.
- Displayed `type` changes on morph; orders use morpher flag (italic type in ships/systems UI).
- Pre-combat: process `morph` into a tech the owner knows; apply morpher loss to br/max_br; consume if insufficient BR; on morph to Cloaker, auto-cloak if series `build_cloakers_cloaked`.
- Cloaked morpher: no morph orders until uncloaked (and cannot nuke while cloaked, like cloakers).
- Scouting / inventory: appear as current `type` (classic behavior).
- **Out of scope:** virgin morpher cloaked-at-build, morph-while-cloaked, delayed combat on uncloak.

### 5. Related v3 rule fixes bundled with options

| Rule | Change |
|------|--------|
| Stargate range | Apply `Stargate.range_multiplier` like Jumpgate (null = infinite) |
| Engineer loss | Read Engineer options `loss` when present; else fall back to `$server['engineer_br_loss']` |
| Min pop to build | Prefer `series.min_pop_to_build` over `$server['builder_population']` |
| Max ag ratio | Clamp empire ag ratio before population growth |
| Ship limit | Reject builds that would exceed `max_ships` (count includes under construction and dismantling, per Eric) |
| Max planets | Refuse builder create when game system count ≥ `series.max_planets` (builders unconsumed at cap, per pike) |
| Doomsday ↔ Jumpgate | Keep annihilated systems invalid as jump destinations |

### 6. Admin and series UI

Generalize create / edit / custom series:

- Per-tech status dropdowns (16 types for sc3).
- Conditional fields: Jumpgate/Stargate range; Carrier/Morpher/Engineer/Jumpgate loss; Carrier/Builder/Morpher/Jumpgate build & maint; series-level planet create cost, max planets, and other new columns.
- Custom series: clamp `max_planets` to `$server['max_planets_custom']` (default 2000).
- Series detail / game list: show tech lists and relevant costs/loss/range when those techs are not Barred (mirror pike detail table and bestiary groupings).

Edit series should update `series_ship_type_options` and empty games’ `game_ship_type_options`, as Jumpgate edit already does.

### 7. Database and migration

1. Insert `ship_types` rows: Carrier, Builder, Morpher (`mobile=1`, `version=v3`).
2. `ALTER TABLE ships ADD morpher ... DEFAULT '0'`.
3. Extend `series` with new columns and sensible defaults so existing sc2 series keep current feel.
4. Keep `*_ship_type_options` schema; begin writing rows for all configured sc3 techs (not only Jumpgate).
5. Add player restricted-tech counter if not derivable safely from `techs` + options alone.
6. Migration note: map existing Jumpgate `Available` → `Developed`.

Upgrade SQL documented in README style alongside prior Jumpgate upgrade block.

### 8. Update-phase ordering (target)

Align with pike for new work:

1. Pre-combat: cloak/uncloak, morph, move/explore, dismantle (existing + morph).
2. Combat: DEST (with Carrier absorb), 2DV, minefield explosion.
3. Post-combat: nuke, invade, colonize, terraform, annihilate, engineer open/close, **builder create**, stargate send, jumpgate jump.

Exact insertion point for create relative to gates should follow `sc.pike` (create handled before/alongside gating in the classic update). Implementers should re-read that section when coding and add regression notes.

### 9. Range convention

- **Store and display multipliers** (`range_multiplier`), matching current Jumpgate UI (“× BR”).
- When importing bestiary text such as `2.5BR` or `BR/2`, convert to multiplier `2.5` or `0.5`.
- When reading pike `stargate` / `jumpgate` divisor fields, convert: multiplier = divisor &gt; 0 ? `1/divisor` : null (infinite).

## Implementation phases

Recommended order (options foundation first so ships and series packs share one model):

### Phase 1 — Series options foundation

- Schema for series-level columns and player restricted counter.
- Generalize ship-type options CRUD and loading (all statuses for all types).
- Tech screen: Developed / Unrestricted / Restricted / Barred / trade-ins / caps.
- Join path: starting techs from Developed; initial tech level.
- Series detail display for tech lists and Jumpgate-plus-generic options.
- No new ship combat/orders yet (Carrier/Builder/Morpher may appear as developable types only after Phase 2+ inserts and logic).

### Phase 2 — Carrier

- DB ship type + build costs from options.
- DEST absorption in `update.php`.
- Missives / history as needed.
- Admin fields for Carrier loss/build/maint.

### Phase 3 — Builder

- DB ship type + build/maint; series `planet_create_cost` and `max_planets` (default 1000).
- Server config `$server['max_planets_custom']` (default 2000); enforce on custom series only.
- Execute `create` in update; system insert, explored, jump open, consume ships; respect max planets.
- Fix order gating to use planet create cost.
- History / missives.

### Phase 4 — Morpher

- DB ship type + `ships.morpher`.
- Morph resolution pre-combat; cloaker interaction; order rules.
- Scouting/UI consistency.

### Phase 5 — Remaining series knobs and gate polish

- Max ships, max ag ratio, HW resources, min pop to build, engineer loss per series, stargate range.
- Enforce ship limit on build.
- Clamp ag ratio in economy/update.

### Phase 6 — Series pack (optional follow-on)

- Seed notable bestiary series (e.g. Mad Scientist, Shifting Sands, Smarmy Purgatory, Galactic Powers) using the new knobs.
- Document any intentional deviations from Lugdunum vs Stargate historical servers.

## Testing notes (for later implementation)

- Combat: carriers vs attacks in enemy system (nuking after mutual survival); carrier vs 2DV; carrier + minefield; fuel-ratio interaction.
- Builder: success/fail thresholds; multi-origin create opens jumps from all origins; explored-only for creator.
- Morpher: morph loss consume; morph to cloaker auto-cloak; no morph while cloaked; italic identity after morph.
- Tech: restricted cap, trade-in does not refund selection, barred absent, developed at join.
- Ship limit includes building and dismantling orders.
- sc2 series regression: unchanged tech list and no v3 ships.

## Open questions

Resolved for this design unless revisited:

| Topic | Decision |
|-------|----------|
| Virgin morphers | Out of scope |
| Wormholes | Out of scope |
| Phase order | Options foundation → Carrier → Builder → Morpher → remaining knobs → optional series pack |
| Range model | Keep PHP multiplier; convert when importing pike/bestiary |
| Max planets | Per series (`series.max_planets`, default 1000). Custom series capped by `$server['max_planets_custom']` (default 2000). Admin series unbound by that ceiling |
| Available vs Developed | Prefer Developed in new UI; Available accepted as alias |
| Max allies | Per series (`series.max_allies`, NULL = unlimited). Copied to `games.max_allies` at spawn; diplomacy enforces |
| Resource ranges | Deferred |
| Builder failure missives | Included in Phase 3 |
| Games snapshot | Series knobs read from series at runtime; ship-type options still copied to games at spawn |

## References

- `reference/ericrobinsonterms.md`
- `reference/chasmysbestiaryv3.md`
- `reference/sc.pike` (combat ~2486+, morph ~2350+, create ~2590+, series admin ~8320+)
- `readme.md` (Jumpgate upgrade history)
- `docs/design-spawn-open-games.md` (spawn copies ship-type options)
- Existing Jumpgate touchpoints: `game/ships.php`, `game/build.php`, `game/tech.php`, `update.php`, `admin/createSeries.php`, `admin/editSeries.php`, `admin/jumpgatefunctions.php`, `main/customSeries.php`
