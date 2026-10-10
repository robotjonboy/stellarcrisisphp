# Design: Surrender Halts Update Processing

## Problem

In **Twisted 12 game 33** (mighty mouse vs Chasmys), update 20 ended incorrectly:

1. Mighty mouse had a ship on Chasmys’s homeworld `(79,75)` set to nuke.
2. Chasmys had (or expected) a minefield defense that should have mattered before any nuke — at minimum, combat / minefield resolution should run before post-combat actions.
3. Mighty mouse **surrendered**.
4. Observed outcome: `(79,75)` was nuked by mighty mouse; mighty mouse’s surrender was recorded; Chasmys was eliminated as nuked out by mighty mouse. Chasmys’s homeworld ownership history shows the nuke; mighty mouse’s homeworld `(78,81)` was **not** nuked.

That is the wrong ending. A surrender should not let the surrendering player still score a homeworld nuke (and related win/nuke bookkeeping) on the same update.

### Apparent player diagnosis

It can look like “one player offering draw stops battles, but ship actions still fire.” The recorded history for this game is **surrender**, not draw. The underlying bug is in how a **one-sided surrender offer** interacts with diplomacy status, combat, and nukes during the same update.

## Goal

When a player (or team) successfully surrenders in a context where surrender ends the contest for that player:

1. **Halt the rest of update processing** for that game (no movement resolution past the surrender decision point, no combat, no minefields, no nukes, no colonize/invade/etc.).
2. **Award a nuke and a win** to the non-surrendering opponent (2-player) / opposing team (team game), using the same cleanup / history / Bridier paths as a normal win-by-elimination where practical.
3. Produce clear history and missives: surrender first; winner credited; no reciprocal “nuked out by the surrendering player” on that update.

Deliverable of this document: design for the surrender-halt work. **Implemented** in `update.php` (early-exit helpers and diplomacy hardening).

## Non-goals

- Do not change one-sided vs mutual **draw** offer rules (both sides must still agree to Draw; draw ends without wins). Mutual surrender is handled separately — see below — and ends like a draw.
- Do not redesign classic FAQ “colonize the abandoned homeworld to claim the nuke” surrender credit (this server already awards surrender nukes immediately — see `phpversiondifferences.md`). Keep immediate credit; only change **when** and **whether** the rest of the update runs.
- Do not change minefield / cloaker combat rules in this project (except insofar as combat must be allowed to run when surrender does **not** end the update early — see root cause).
- Do not add new diplomacy offer types or UI options beyond what `can_surrender` already allows.
- Do not change team-game surrender **voting** rules in `getTeamDiplomacy()` (who must offer what for a team to surrender or draw). Only change post-resolution halt / award / mutual-surrender-as-draw behavior.

## Incident reconstruction (Twisted 12 / 33)

| Fact | Evidence |
|------|----------|
| 2-player game | mighty mouse, Chasmys only |
| Final update | Update 20 |
| Physical nuke of Chasmys HW | History: `(79,75) was nuked by mighty mouse`; map `owner_history` for that system ends nuked |
| Mighty mouse HW not nuked | `(78,81)` ownership stays mighty mouse through last update |
| Surrender recorded | `mighty mouse surrendered.` |
| Elimination text | `Chasmys was nuked out of the game by mighty mouse.` |

So both a **surrender elimination** and a **nuke elimination** were queued and applied in one update, with asymmetric ship-action success.

## Root cause (current `update.php`)

### Update order today (simplified)

```
[team draw / team surrender early exit — team games only]
  → per-player diplomacy resolution (queues individual surrender into $eliminated)
  → ship orders (move, cloak, queue $nuke / $colonize / …)
  → combat (allegiances from diplomacy status)
  → minefield explosions (only if combat destroyed a Minefield)
  → post-combat actions (nuke, annihilate, invade, …)
  → process $eliminated (surrender + nuked out, etc.)
  → end-of-game draw / win checks
```

Team mutual draw already **returns early** before ship processing. Individual surrender does **not**.

### Bug 1 — One-sided surrender mutates the opponent’s status to `0`

Diplomacy offers: `0` = Surrender, `1` = Draw, `2` = War, …

When empire A offers surrender (`offer == 0`) and B does not:

- On A’s diplomacy row: A is queued into `$eliminated` as surrender-to-B. A’s `status` is **not** updated in that branch.
- On B’s diplomacy row: code falls into the normal “least offer wins” path and computes  
  `new_status = min(B.offer, A.offer)` → `min(2, 0) = 0`.  
  B’s `diplomacy.status` is written as **`0` (Surrender)**.

`0` is not War (`2`). Consequences in the **same** update:

| System | Effect of B’s status = 0 |
|--------|---------------------------|
| `allegiances()` | Only `status == 2` are enemies. B does not treat A as an enemy → **no (or one-sided) combat**. |
| Nuke processing | Requires `diplomacy.status == 2` for the nuking ship’s owner vs planet owner. **B cannot nuke A.** |
| A’s status | Often still War → **A can still nuke B.** |

That matches the incident: mighty mouse (surrendering) nuked Chasmys; Chasmys’s reciprocal nuke did not land; combat/minefield path did not behave like a normal war turn.

### Bug 2 — Surrender is deferred until after nukes

Even without Bug 1, queuing surrender into `$eliminated` early and applying it **after** post-combat nukes lets a surrendering player’s ships still fire. Combined with Bug 1, the survivor can be the one who was supposed to receive the surrender, while the surrendering player still gets nuke credit — a corrupted double bookkeeping outcome.

### Why “draw” is a red herring (for this incident)

- **One-sided draw** (`offer == 1` from one side only) intentionally leaves `status` unchanged; it should not by itself stop war combat.
- **Mutual draw** sets `status = 1` and, at end of update (or immediately in team games), ends the game with no wins — and nukes require War, so mutual draw should not produce nukes.
- This game’s history line is surrender. The combat skip is from status `0`, not from Draw (`1`).

Players may still describe “diplomacy offer stopped battles” correctly in spirit; the offer was surrender.

## Proposed behavior

### Principle

**Surrender is an end-of-game (or end-of-player) resolution decision.** Once a valid surrender is recognized for the update, do not resolve the war turn. Award the conceded nuke (and win, when the game is over) and stop.

This is stricter than the classic FAQ sequence (which listed surrender among closing calculations after nukes). This server already diverges on surrender credit; early halt is the safer competitive rule and matches the desired outcome for Twisted 12 / 33.

### 2-player games (`can_surrender`, `player_count == 2`)

Detect bilateral offers **before** ship orders / combat / post-combat actions (same spirit as team draw early exit).

**Exactly one** player’s offer is Surrender (`0`):

1. Do **not** run the rest of `update_game` for this game.
2. Record history: surrendering empire surrendered; surviving empire won (and received the nuke).
3. Update empire stats: surrendering `nuked += 1`; survivor `nukes += 1` and `wins += 1`.
4. Clear the surrendering player’s systems as unowned / emptied elimination cleanup — **not** as a ship nuke. Map / `owner_history` must **not** show the nuked-planet icon on the loser homeworld (or other cleared worlds) from this path.
5. Run normal game-end cleanup (`endGame`, Bridier if applicable, missives, map snapshot) so the history page matches other finished grudges.
6. Missives: clear “you surrendered” / “opponent surrendered; you win” text; no fake “nuked out by the surrendering player’s ships.”

**Both** players offer Surrender (`0`):

- Treat the same as a **mutual draw**: end the game immediately with no wins and no nuke credits; history/missives follow the existing draw end path (plus optional note that both surrendered, if easy — not required).
- Do **not** continue the update at War (replaces today’s mutual-surrender → War safeguard).

### Team games

Team surrender **voting** (`getTeamDiplomacy()` / who must offer Surrender vs Draw vs War) is **unchanged**. Only post-resolution behavior changes:

- If voting resolves to **one team** surrendering and the other not: **halt** after applying surrender cleanup / messaging; award nuke(s) and win to the remaining team; no combat or nukes; clear surrendered team’s board without nuke icons (same as 2-player).
- If voting resolves to **both teams** surrendering: treat the same as a **mutual team draw** — early `endGame` with no wins / no nuke credits (same outcome as both teams offering Draw today).

### Idle / ruin

Surrender **overrides** idle and ruin on that update.

- Early surrender (or mutual-surrender-as-draw) detection runs **before** idle auto-end-turn effects that matter for elimination and **before** ruin processing.
- A player who would have fallen into ruins on this update but has a resolving surrender (as surrenderer or as recipient of a one-sided opponent surrender, or as party to mutual surrender → draw) does **not** get a ruin outcome for this game; the surrender/draw end applies instead.
- No need to change how many updates count as idle/ruin in general — only the precedence when surrender ends the game on the same tick.

### Multi-player (`player_count > 2`)

UI already gates surrender offers to `player_count == 2` in `game/diplomacy.php`. Design assumes surrender remains **2-player-only** for individual diplomacy.

If any code path still queues `$eliminated` surrender with more than two players alive, do not invent “win the whole game” for the offer target. Prefer: refuse / ignore illegal surrender, or eliminate only the surrendering player **without** running their ships — but that is out of scope unless we find a live path. Primary fix targets the 2-player (and team) cases the UI allows.

## Design principles

1. **Surrender beats ships** — A conceded game does not resolve fleets.
2. **Surrender beats idle/ruin** — A resolving surrender (or mutual-surrender draw) on an update takes precedence over ruin/idle elimination for that update.
3. **One nuke, one winner** — In a one-sided 2-player surrender, exactly one empire gets nuke credit and the win; the surrendering empire gets a nuked; no second elimination from the surrendering player’s nuke orders.
4. **Mutual surrender = draw** — Two individuals or two teams both surrendering ends like a draw (no wins, no nuke credits), not War and not a double loss-with-nukes.
5. **No nuke icon on surrender clear** — Board cleanup empties ownership/population without recording a nuked-planet map state from ship nukes.
6. **Detect early, return early** — Mirror team-draw’s `endGame` + `return` pattern rather than patching allegiances after the fact.
7. **Fix the status=`0` footgun even if early-exit lands first** — Do not write `diplomacy.status = 0` as a living state for anyone still in the game. Surrender is an elimination event, not a standing diplomatic status used by combat.
8. **Team voting unchanged** — Only halt / award / mutual-as-draw after votes resolve.

## Recommended implementation approach (later)

No code in this phase. Suggested shape for implementation:

### A. Early surrender resolution (primary)

Near the start of `update_game()` (unify with / extend the existing team draw–surrender block):

1. For non-team 2-player games with `can_surrender`: load the single bilateral diplomacy pair.
2. If **both** `offer == 0`: call the same path as mutual draw (`endGame` as draw; no wins/nukes), then `return`.
3. If **exactly one** `offer == 0`: call a small helper, e.g. `resolveSurrenderWin($series, $game, $surrendering, $winner, &$history, $saved_map)`, then `return`.
4. Helper responsibilities: history rows, empire nukes/nuked/wins, Bridier, player/ship/diplomacy cleanup **without** nuke map icons, `endGame`, empire messages.

Reuse existing draw / win / elimination code paths where possible to avoid divergent Bridier or history formats — but **do not** go through `$nuke` ship arrays for board clear.

### B. Stop writing status `0` for active players (hardening)

Even with early exit, remove or guard the `min(offer)` path so an opponent’s surrender offer cannot set the survivor’s `status` to `0` during a continuing update (e.g. if early-exit is skipped due to a bug, or in any future multi-elimination scenario).

Options (pick one at implement time):

- Never persist `status = 0`; only queue elimination.
- If `opponent.offer == 0` and self is not surrendering, skip status update and continue (war) — only valid if surrender is handled exclusively by early exit.

### C. Remove deferred individual surrender from mid-diplomacy `$eliminated` (once A exists)

Avoid double-processing: early exit should be the only individual-surrender path for 2-player games. Delete or dead-code the “queue surrender into `$eliminated` then process after nukes” branch for that case.

### D. Team path

Keep `getTeamDiplomacy()` voting as-is. After votes resolve:

- Both teams surrender → same early exit as both teams draw.
- One team surrenders → same “award win + halt” helper as 2-player one-sided surrender (team win bookkeeping), instead of relying on a full turn of empty combat.

## History / missive / map expectations

For a clean one-sided 2-player surrender ending:

```
… prior update events …
mighty mouse surrendered.
Chasmys has won Twisted 12 33.   // or existing win missive wording
```

Should **not** include on that update:

- `(x,y) was nuked by mighty mouse` against Chasmys’s homeworld from the surrendering player’s orders
- `Chasmys was nuked out of the game by mighty mouse`
- A map **nuke icon** / nuked-planet `owner_history` state on the surrendering empire’s homeworld (or other cleared systems) caused by this cleanup

Map: survivor’s worlds unchanged by fake nukes; surrendering player’s systems cleared as unowned / emptied **without** the nuked-planet visual. Exact history event enum strings should match what `history.php` already renders for `surrender` / `won` / draw.

For mutual surrender (individuals or teams): same presentation as an existing draw end (no wins); ships do not act.

## Files likely touched (implementation later)

| File | Role |
|------|------|
| `update.php` | Early surrender detection; stop status=`0`; remove deferred surrender+nuke interaction; team halt alignment |
| Possibly small helper in `sc.php` / `update.php` | Shared `resolveSurrenderWin` / endGame wiring |
| `history.php` | Only if win/surrender ordering or text needs adjustment |
| `docs/design-surrender-halts-update.md` | This document |

No schema migration expected.

## Testing plan

1. **Replay scenario (conceptual):** 2-player, both have nuke orders on each other’s HW, defender has minefield, attacker surrenders → update ends in surrender win for defender; **no** nuke events; defender gets nuke+win; attacker gets nuked; game closed; loser HW has **no** nuke icon on the history map.
2. **Surrender only, no ships:** Still awards nuke+win; early exit; empty combat not required.
3. **Mutual individual surrender:** Ends as draw (no wins, no nuke credits); no ship resolution.
4. **Mutual team surrender:** Ends as draw (same as mutual team draw); voting rules unchanged.
5. **Mutual draw:** Unchanged; draw end; no wins.
6. **One-sided draw + war:** Combat and nukes still resolve (status stays War).
7. **One-sided team surrender:** Opposing team wins without combat/nuke phase; voting unchanged.
8. **Surrender vs ruin:** Player at ruin threshold who surrenders (or whose opponent’s one-sided surrender ends the game) → surrender/draw outcome, not ruin.
9. **Bridier grudge:** One-sided surrender awards Bridier the same as a normal 2-player win for the survivor; mutual surrender does not (draw).
10. **Regression:** Normal war nuke-out with no surrender still allows simultaneous reciprocal homeworld nukes (`phpversiondifferences.md` item 2) — out of scope to change unless explicitly desired later.

## Phased delivery

### Phase 1 (this feature)

- Early-exit one-sided surrender win for 2-player `can_surrender` games (board clear without nuke icon)
- Mutual surrender (individual or team) → draw early exit
- Hardening: do not persist `status = 0` for continuing players
- Team one-sided surrender halt + win (voting unchanged)
- Surrender precedence over idle/ruin on the same update
- History/missive/Bridier parity with normal win or draw as applicable

### Phase 2 (optional)

- Broader “diplomacy offers that end the game” audit (draw early-exit for non-team 2-player before ship orders, not only at the bottom)
- Explicit automated regression fixture from Twisted 12 / 33 orders if update can be driven offline

## Decided (was open questions)

1. **Map nuke icon:** Surrender clears the board **without** a nuked-planet map icon. Elimination cleanup only — not a ship nuke event.
2. **Mutual surrender:** If two individual empires both surrender, or both teams surrender, treat it the **same as a draw** (no wins, no nuke credits, early end).
3. **Idle / ruin:** Surrender **overrides** idle/ruin on that update; skip ruin when surrender or mutual-surrender-draw resolves.
4. **Team voting:** `getTeamDiplomacy()` / offer aggregation rules stay as they are; only post-resolution halt, award, and mutual-as-draw change.

## Summary

Twisted 12 game 33 exposed a surrender bug: a one-sided surrender offer writes diplomacy `status = 0` for the survivor (suppressing battles and their nukes) while the surrendering player’s War status and deferred elimination still allow their nukes to fire. The fix is to treat a valid one-sided 2-player (and completing team) surrender like an early game-end: **stop the update, award nuke and win to the non-surrendering side, clear the board without nuke icons, and never resolve fleets on a conceded turn.** Mutual surrender ends as a draw. Surrender beats idle/ruin; team voting is unchanged.
