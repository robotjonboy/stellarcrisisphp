# Design Options: Mobile Usability

## Problem

Stellar Crisis is a classic table-based PHP web game. The UI still works on phones via browser zoom, but it was built for wide desktop screens and never adapted for small viewports or touch.

Today there is:

- No `viewport` meta tag (`standardHeader` in `sc.php`, `gameHeader` in `game/game.php`)
- No `@media` rules or other responsive CSS in `styles.css`
- Dense single-row nav button bars (`drawButtons`, in-game `gameAction` row)
- Wide content that forces horizontal scroll (map tables, ships tables, ratios row, game list)
- Hover-only player tooltips (`.playerListing a:hover span`)
- Popup windows for instant messages and series parameters (`sc.js` `blabBox`, `window.open` elsewhere)

Players can already play on mobile by pinching and scrolling. The goal is not a redesign; it is to reduce friction while keeping the classic look and interaction model.

## Goal

Identify practical options to improve **usability on phones and tablets** without changing the game’s identity: starfield, spacerules, submit-button navigation, HTML map, form-POST flow.

Deliverable of this document: a menu of options with trade-offs, so we can pick a small first slice later. **No code in this phase.**

## Non-goals

- Do not rebuild the UI in a modern framework (React, Bootstrap, etc.).
- Do not replace the HTML table map with canvas/WebGL in v1 of any mobile work.
- Do not redesign branding, colors, or desktop layout “for fashion.”
- Do not change game rules, turn timing, or auth (hidden `name`/`pass` form fields stay as they are unless a later auth project).
- Do not require a separate mobile app or mobile-only site.
- Do not change history viewer or admin UIs; polish player lobby + in-game only.

## Background: how the UI is built

| Layer | Reality |
|-------|---------|
| Stack | Server-rendered PHP, MySQL, `styles.css`, vanilla `sc.js` |
| Layout | Nested `<table>`s; almost no flex/grid |
| Chrome | `standardHeader` / `gameHeader`, spacerule images, centered submit buttons |
| Map | HTML table of systems; 40×40 icons (full map), 20×20 (mini-map); optional 10×10 zoom |
| Nav | One long row of `<input type=submit>` buttons |

The map already has a useful mobile-adjacent pattern: **Mini-Map → zoom to a 10×10 chunk**. That is a better starting point for phones than forcing the full galaxy table onto a narrow screen.

## Design principles (constraints on any option)

1. **Classic first** — Same screens, same labels, same button-driven navigation. Prefer CSS and small HTML tweaks over new UI paradigms.
2. **Additive** — Desktop should keep looking and behaving as it does today unless a change clearly helps both.
3. **Touch equals click** — Anything that only works on `:hover` is broken on phones.
4. **Map is special** — Treat map/mini-map as its own problem; do not solve it with the same tricks as the game list.
5. **Small diffs** — Prefer changes concentrated in `styles.css`, the two headers, and a few high-traffic pages.

---

## Pain points (priority order)

### P1 — Foundation

Without a viewport meta tag, mobile browsers often “fake” a ~980px desktop layout and leave the user permanently zoomed out or fighting the browser’s scale. This is the cheapest win and unlocks every CSS option below.

### P2 — Navigation

Lobby (`drawButtons`) and in-game (`gameHeader`) pack many adjacent submit buttons into one centered row. On a phone they wrap unpredictably or become tiny tap targets. Mis-taps (e.g. End Turn vs Notes) are costly.

### P3 — Map / Mini-Map (`game/map.php`)

Full map width ≈ (systems across) × (nested cell with 40×40 icon + stats). Large games overflow badly. Mini-map is denser but still not scaled to the viewport. Zoom-to-chunk already exists and should be leaned on.

### P4 — Dense data tables

Ships/Fleets, Build, Game List, ratios bar (`ratios()` — five metrics in one row): readable on desktop, awkward sideways scrolling on phones.

### P5 — Hover and popups

- Player listing tooltips require hover and absolute `left: 175px`.
- Instant message and series-parameter UIs use `window.open` — blocked or awkward on mobile, and an unnecessary split on desktop once we move to same-tab (F2).

### P6 — Secondary readability

Very small type (7–8pt on map labels and listings), `white-space: pre` on messages (long lines), fixed `cols=` textareas, login side-by-side `250pt` column.

---

## Options

Options are independent unless noted. They can be mixed into phases.

### Option A — Viewport + baseline CSS only

**What:** Add `<meta name="viewport" content="width=device-width, initial-scale=1">` to both headers. Add a small mobile media-query block in `styles.css`: fluid images/spacerules, slightly larger default tap targets for `input`/`select`/`textarea`, prevent accidental text overflow, maybe bump 7–8pt text one step on narrow screens.

**Pros:** Tiny change; immediately makes the site “phone-native” to scale; low risk to desktop if media queries are narrow-only.

**Cons:** Alone, long button rows and wide maps still hurt; may expose layout issues that desktop zoom was hiding.

**Classic risk:** Low if visual styling stays the same and only sizing/spacing changes under a breakpoint.

**Recommendation weight:** Strong candidate for any first pass.

---

### Option B — Wrap / stack navigation bars

**What:** Keep the same buttons and labels. Under a mobile breakpoint, allow the button row to wrap with gaps, or stack into 2–3 columns / a short grid of full-width-ish buttons. Give **End Turn** and **Quit** extra separation (margin/gap and/or a visual break from the main nav cluster) so they are harder to hit by accident. **No confirmation dialog** — spacing only.

**Pros:** High impact for every screen; no new navigation model; still “the SC button bar.”

**Cons:** Takes more vertical space; wrapping can look slightly less “classic compact.” Need care so desktop remains a single tight row.

**Variants:**

| B1 | Soft wrap + padding only |
| B2 | Explicit 2-column button grid on narrow screens |
| B3 | Collapsible “More…” for secondary actions (Notes, Message History) — more modern; **higher classic risk** |

**Decision (End Turn / Quit):** Extra space on mobile; **avoid confirmation**. Apply as part of B1 or B2 (e.g. larger gap before those controls, or place them on their own wrapped row). Do not add “Are you sure?” prompts.

**Recommendation weight:** B1 or B2 preferred; avoid B3 unless needed after trying wrap.

---

### Option C — Map: lean on zoom, don’t redraw

**What:** On narrow screens, prefer or default toward mini-map / zoomed chunk behavior; ensure the map table sits in a horizontally scrollable container with visible scroll affordance; optionally slightly reduce icon size via CSS on mobile only (e.g. 40→28, 20→16) without changing PHP map logic.

**Pros:** Reuses existing zoom and mini-map; no canvas rewrite; keeps GIF icons and table map.

**Cons:** Still not as smooth as a true pinch-zoom map; CSS-shrunk icons make labels tighter; scroll-in-a-box can feel fiddly.

**Variants:**

| C1 | Scrollable map container + viewport only |
| C2 | C1 + slightly smaller icons under media query |
| C3 | Soft-default: if viewport is narrow and map is “large,” show mini-map first (or a one-line hint: “Use Mini-Map to zoom”) |
| C4 | Full canvas/SVG remake — **out of scope** for “light classic” work |

**Recommendation weight:** C1 + C2; consider C3 only if playtesting shows full map is unusable.

---

### Option D — Stack key wide rows (ratios, login, listings)

**What:** Under a breakpoint, reflow specific wide tables without rewriting pages:

- Ratios: wrap into 2–3 lines or a definition-list-like stack (same labels/values).
- Login: stack the 250pt MOTD/login columns vertically.
- Game list player columns: allow wrapping; ensure Join/Login stay tappable.

**Pros:** Targeted; preserves desktop tables; improves the screens people hit every session.

**Cons:** Table-to-stack CSS can be brittle with nested tables; may need a few HTML class hooks on the worst offenders.

**Recommendation weight:** Worth doing for ratios + login early; game list can follow.

---

### Option E — Touch-friendly tooltips (replace hover-only)

**What:** Change `.playerListing` tooltips so they work on tap/focus (e.g. `:focus-within`, or a small JS toggle), and position the popup within the viewport instead of fixed `left: 175px`.

**Pros:** Fixes a clearly broken mobile interaction; also helps desktop keyboard users.

**Cons:** Need a dismiss pattern (tap outside / second tap) so tooltips don’t stick; slightly more JS than pure CSS.

**Recommendation weight:** Small, clear win once viewport work lands.

---

### Option F — Replace popups with same-tab / inline forms

**What:** Instant message (`blabBox`) and series-parameter windows become either:

- Same-tab pages that return to the previous screen, or
- Inline expandable panels on the current page

**Pros:** Reliable on mobile; no popup blockers; one code path for all clients.

**Cons:** Slightly more PHP routing / UX work than keeping `window.open`.

**Variants:**

| F1 | Detect mobile / small viewport and use same-tab; keep `window.open` on desktop |
| F2 | Always same-tab / inline for everyone (simpler, one behavior) |

**Decision: F2.** Desktop users are not expected to miss popups. Drop `window.open` for IM and series parameters in favor of same-tab (or inline) flows everywhere. Not required for Package 1 if IM is rare on phones, but when popup work is done, do F2—do not retain a desktop-popup split.

---

### Option G — Ships / orders tables: progressive disclosure

**What:** On narrow screens, show a compact ship row (name, location, type) with orders controls stacked under each ship, or a “one ship at a time” focus. Desktop table unchanged.

**Pros:** Addresses the hardest non-map play surface.

**Cons:** More HTML/CSS structure change than A–E; risk of making fleet management feel different on mobile.

**Recommendation weight:** Phase 2 after foundation + nav + map scroll feel good.

---

### Option H — Profile preference: “Mobile-friendly layout”

**What:** An Edit Profile checkbox that adds a class on `<body>` (or a cookie) enabling the mobile CSS even on tablets / desktop-narrow windows, or forcing mini-map-first behavior.

**Pros:** Opt-in; zero surprise for classic desktop die-hards; useful for testing.

**Cons:** Extra setting to maintain; two layouts to reason about.

**Recommendation weight:** Optional; only if auto media queries prove controversial. Prefer automatic breakpoints first.

---

### Option I — Do almost nothing (document “best effort”)

**What:** Add only viewport + a short help note (“use landscape + Mini-Map on phones”). No further CSS.

**Pros:** Minimal maintenance.

**Cons:** Leaves most pain points intact; feels unfinished once viewport is added.

**Recommendation weight:** Baseline floor only; not a full answer if the goal is real usability improvement.

---

## Suggested packages (how to combine options)

These are **packages**, not commitments — pick one after discussion.

### Package 1 — “Light touch” (recommended starting point)

- **A** viewport + baseline CSS  
- **B1** nav wrap + tap padding; **extra space before End Turn / Quit on mobile (no confirmation)**  
- **C1** scrollable map container  
- **E** tap-capable tooltips  

Keeps the classic look, fixes the worst daily friction, avoids structural PHP rewrites.

### Package 2 — “Playable on a commute”

Package 1, plus:

- **C2** slightly smaller map icons on narrow screens  
- **D** ratios + login stack  
- **F2** same-tab / inline IM and series params for everyone (no popups)  

### Package 3 — “Mobile as a first-class client”

Package 2, plus:

- **G** ships/orders reflow  
- Optional **C3** mini-map-first hint/default  
- Optional **H** profile toggle  

Higher effort; still not a redesign if desktop paths stay intact.

---

## Surfaces to touch later (implementation map)

| Area | Primary files |
|------|----------------|
| Viewport / headers | `sc.php` (`standardHeader`), `game/game.php` (`gameHeader`) — not `history.php` / admin |
| Shared CSS | `styles.css` |
| Lobby nav | `drawButtons()` in `sc.php` |
| In-game nav + ratios | `gameHeader()`, `ratios()` in `game/game.php` |
| Map | `game/map.php` |
| Tooltips | `.playerListing` in `styles.css` (+ small `sc.js` if needed) |
| Popups | `sc.js` (`blabBox`), callers in game list / info / tournaments |
| Ships | `game/ships.php` (Package 3) |
| Login layout | `mainPage()` in `sc.php` |

No schema migration required for any option above.

---

## What we should explicitly preserve

- Submit-button navigation (not a hamburger-only IA)
- Spacerule + starfield aesthetic
- HTML table map with empire icons
- Existing page names and game actions (Info, Tech, Ships, Map, …)
- Desktop single-row button bar appearance at full width
- Form-POST architecture

---

## Open questions

1. **How important is phone play vs tablet?** Landscape phone + Package 1 may be enough; portrait ships/map are harder.
2. **End Turn / Quit on mobile:** **Decided** — extra separation/spacing only; **no confirmation** dialog.
3. **Popup policy:** **Decided — F2.** Same-tab / inline for everyone; no desktop `window.open` split.
4. **Auto-update (`onLoad` setTimeout click):** **Decided — leave alone.** No mobile-specific disable or special casing; keep existing profile/behavior as-is.
5. **Breakpoint choice:** **Decided — 768px.** Media queries target phones and narrow windows at `max-width: 768px`.
6. **History viewer / admin:** **Decided — out of scope.** Leave history and admin UIs alone; focus mobile work on player lobby + in-game.

---

## Implementation status

**Package 3 implemented** (2026-07-20), skipping optional **C3** and **H**.

Includes: A, B1 (+ End Turn/Quit spacing), C1, C2, D, E, F2, G.

## Testing plan (when an option is chosen)

1. Phone (iOS Safari + Android Chrome): login → game list → join/continue → Info → Map → Mini-Map zoom → Ships → Diplomacy → End Turn path without mis-taps.
2. Desktop Chrome at full width: visual regression — button bars still single-row classic look; map unchanged.
3. Desktop narrow window / DevTools device mode: confirm media queries, no double-zoom weirdness after viewport meta.
4. Touch and desktop: player listing tooltip open/dismiss; IM and series params work same-tab (no popup) on both.
5. Large map game: horizontal scroll works; zoomed 10×10 remains usable.
6. Landscape vs portrait: note which package is “good enough.”

---

## Summary

The interface does not need a redesign. The highest-value work is **viewport + small CSS**, **wrapping the existing button bars**, **making the map scroll/zoom-friendly**, and **fixing hover-only / popup patterns**. Ships-table reflow and profile toggles are optional later layers.

**Recommended discussion target:** Package 1, then Package 2 (including F2 same-tab flows for everyone).
