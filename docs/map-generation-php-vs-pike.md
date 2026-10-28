# Map generation: PHP vs classic Pike

## Why this exists

On 13 Oct 2026 Mighty Mouse broadcast:

> The php code does not seem have multiple connecting systems when someone joins. At least small sample my HW has been the crossroad in 2 of 3 for multiple empire only path. Maybe just poor luck. :)

This note compares join-time and layout map generation in this PHP server with the classic Pike implementation in `reference/sc.pike`. It is an analysis only: no code changes are proposed here.

Twisted 12 game 33 (mighty mouse vs Chasmys) is **not** an example of the topology complaint. Mighty Mouse’s homeworld there sat on a south-edge stub, not on a unique path between empires.

## Scope

Pike 3.x offers two layouts:

- **Traditional** (called “Random layout” in series text): grow a new cluster of systems when each player joins.
- **Mirror**: build player 1’s cluster, reflect it across a buffer border, and let player 2 occupy the other half.

This PHP server keeps both of those ideas and adds **twisted**, **balanced**, and **prebuilt** maps. Mighty Mouse’s remark is about **classic join-time attachment** (traditional / PHP `standard`). Mirror and twisted are included because that is where “multiple connecting systems” actually exist.

## Traditional join: the same algorithm

Pike traditional join and PHP `generateMapForPlayer` / `buildPlayerChain` / `addChain` / `selectHW` are the same generator.

When the map is empty, the first player’s cluster starts in empty space. When the map already exists, the server:

1. Picks a random existing system that still has an unused orthogonal side.
2. Picks one empty neighbor on that unused side.
3. Grows a new cluster of `systems_per_player` (Pike: `s_add`) worlds from that empty square, always keeping a jump back to the parent.
4. Chooses the new homeworld at random inside the new cluster, rejecting only a world that sits next to another homeworld.
5. Drops jumps that point at missing coordinates and fills in missing reverse jumps.

There is no second attachment search. There is no pass that walks the contact edge and opens extra jumps. There is no rule forbidding an existing homeworld as the join parent, and no rule that the new homeworld must be a leaf rather than a cut vertex.

Extra links into the old map appear only by accident: if the new cluster happens to list a jump toward another already-existing neighbor, the reverse-jump cleanup keeps it. If the cluster grows out into empty space — the usual case — the new empire is a peninsula with a single isthmus.

That is true in Pike and in PHP. Classic Pike did **not** create multiple connecting systems when someone joined a traditional game. PHP did not drop that feature; it was never there.

### Why a homeworld becomes the crossroad

Two patterns match “my HW has been the crossroad … for multiple empire only path”:

1. **A later player joins onto an existing edge homeworld.** Join parents are systems that still have unused sides. A frontier homeworld is a legal parent. If two later empires both fork off that world, the unique path between them is empire B — homeworld — empire C.
2. **The joiner’s own homeworld lands on the isthmus.** The new cluster is often tree-like. The homeworld is a random system in that tree. If it is not a leaf, traffic between the rest of the galaxy and the new empire’s back systems goes through it.

Neither implementation prefers non-homeworld parents or leaf homeworlds. “2 of 3” can still be luck in a tiny sample, but the generator has no bias the other way.

## Where PHP is harsher than Pike

The join **shape** matches. A few PHP details make unique paths more likely.

### Jump count while growing a cluster

Pike picks 1–4 jumps from a system (all four orthogonal directions are eligible, including a full 4-way that still includes the parent).

PHP picks 1 through one less than the number of legal directions. In the interior that is 1–3, never 4. Comments in the PHP grower call this more maze-like. After the parent is counted, Pike can still take every remaining side; PHP cannot. The new cluster is less likely to wrap around and touch the old map at a second point.

Not every chosen jump becomes a real world: both growers recurse until they hit the per-player planet cap, then leftover planned jumps vanish. Pike’s higher cap still leaves more chances for extra contact.

### Bounding box

Pike traditional maps are unbounded. A system is “full” only when it already has four jumps.

PHP classic join uses a box from 1 to twice `max_players × systems_per_player`. Near that box a system can be treated as full with fewer than four jumps, so later joiners have fewer legal forks.

### First-player origin

Pike scatters the first system around a jittered point near `2 × max_players × systems_per_player`. PHP plants it at the center of the box. This does not change later-joiner topology.

### Parent-side bookkeeping

Pike removes the parent from the list of extra jump candidates before choosing more sides. PHP intends to do the same but does not keep the removal, so the parent can be rolled again and skipped. That wastes rolls; it does not add a second connection to the old map.

## Mirror: this is where multiple connections are deliberate

Pike mirror is not grown per later joiner. Player 1’s cluster is built with the same chain grower, `gen_mirror` reflects it, and player 2 occupies the other homeworld. Changelog notes for 3.01.5 and 3.2.5 are about this path: longest possible border, homeworld nudged off the pivot, optional crosslinks between border worlds.

The seam is a **whole border**, not one fork:

- Choose the bounding-box side that already has the most planets.
- Insert a buffer world for every planet on that edge.
- Each buffer links the old side to the reflected side, so the number of inter-empire links equals the number of planets on that edge.
- Optionally add extra buffer-to-buffer links (Pike: one in six; PHP: one in four).
- Try a few times to keep the homeworld off the pivot edge so it is less likely to sit on the seam.

PHP `mirrorMap` is the same idea: longest edge, buffer strip, homeworld nudge, cross-links. Buffer resources follow the Pike 3.2 formula (homeworld resource over four, plus a random amount up to that same quarter).

If “multiple connecting systems” means a wide seam between two empires, that is **mirror**, in both codebases. It does not run when a third or fourth player joins a traditional game. The second player in a 2-player mirror does not grow a new peninsula; they inherit the prebuilt other half.

## Twisted is PHP-only

Pike has no twisted maps. PHP builds one half, rotates it 180 degrees, and scores possible alignments by most players connected, then most planet links. There is no buffer strip; the halves touch directly.

Doa’s PHP-vs-original note diagrams the difference:

- Mirror: extra buffer worlds along the seam (`A-H-B-M-b-h-a`).
- Twisted: the first player’s map joined back to itself with no extra systems (`A-H-B-d`).

Twisted *tries* to maximize seam links, but a given alignment can still be one or two jumps, and a homeworld may sit on that seam if the other side is not a homeworld. Comparing Twisted 12 to Pike traditional join is not a like-for-like regression. Comparing it to Pike mirror is closer in spirit (2-player, full map at start) but the seam construction is different.

PHP also has balanced (2-player equal-access homeworlds) and prebuilt (whole map generated up front, sections activated as players join). Pike has neither. Prebuilt can restore several links when a section wakes, because those links were generated with the full map; that is still not the traditional join grower.

## Bottom line

Mighty Mouse is right about this PHP server’s **classic** join: a new empire attaches at one unused jump. Extra connections are incidental. A homeworld can easily sit on the only path between empires.

Classic Pike does the same thing. PHP did not lose a Pike “multiple connections on join” pass.

PHP is the stricter version of that shared generator: fewer attempted jumps per system, plus a bounding box. That is enough to make “homeworld was the only path in 2 of 3” a plausible reading of the PHP generator, not only poor luck — and still consistent with Pike, where that outcome was already possible, just somewhat less often.

The Pike feature that *does* create multiple connecting systems is mirror’s buffer border. PHP already copied that. It does not run for classic multiplayer joins, and it does not run for twisted.
