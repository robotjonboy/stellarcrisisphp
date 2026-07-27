# Explanation of v3.0 series variables and terminology

## This Document

This text was copied from Eric Robinson's Home Page.  Eric Robinson played Stellar Crisis as Chasmys.  He was the designer of three Stellar Crisis v3 series: Mad Scientist, Shifting Sands, and Smarmy Purgatory.

## Series

The name the designer has given the series.

## Players

The maximum number of players that can join an instance of that game.
Little known fact: if the game "fills", but someone gets nuked or surrenders before the fifth update, then another player can join. At one time, even the player that just left could rejoin, but the code now blocks that from happening.

## Systems

The number of new planets that are added to the map whenever a new player joins the game.

## Allies

The maximum number of allies allowed to each player. Games labelled "Cutthroat" do not allow alliance. Those marked "Blood" or "Grudge" do not even allow truce or trade.
Example: a group of four players all mutually allied with one another would have three allies per player. This would be legal in a game that allowed at least three allies.

## Ships

The maximum number of ships that a player may have in play. This figure includes ships under construction, as well as those ordered to dismantle.

## Initial Tech

The starting Tech Level for any player that enters the game before the first update. Players that join later will get the highest Tech Level of the existing players. For every n² points of Tech Level, a player will have n tech selections banked (awaiting development).

## Tech

The amount that a player's Tech Level increases on update, assuming the player does not consume any mineral and fuel resources. Otherwise, the actual tech increase will be reduced in accordance with the player's ratio of consumption of minerals and fuel to their production:

```
Tech Increase = Tech x (1 - (Consumption/Production))
```

## Max Ag Ratio

An empire's Ag Ratio will be the lesser of its ratio of Agriculture to Population, or this maximum. Ag Ratio directly determines the growth of an empire's populations:

```
New Population = Ag Ratio x Old Population
```

Therefore, the Max will limit the prospects for pop-tricking. But for many games, this constraint weak enough to be irrelevant (e.g. with a "Max" of 100).

## Homeworld Resources

The agriculture, mineral, and fuel ratings of the home system. Games with high-resource homeworlds facilitate early building. Note that starting population will equal the homeworld's agriculture.

## Average Resources

The average agriculture, mineral, and fuel ratings of randomized systems. Systems created with builder ships will not be random, but will exactly equal the averages. Games with high resources increase the value of colonization.

## Agriculture (Ag)

The contribution that a planet makes towards feeding the empire's population. One point of agriculture enables the survival of one point of population. Excess agriculture leads to population growth, while insufficient agriculture leads to negative growth (see Ag Ratio). Unlike minerals and fuel, the actual agricultural production of a planet does not depend on the size of its population. The owner always receives the full value of the agriculture rating.

## Minerals (Min)

The mineral rating of a planet. The actual mineral production is the lesser of the planet's mineral rating and its population. Mineral production contributes to tech development, and is crucial for building and maintaining/repairing ships. Minerals constrain the speed at which a player may construct fully-effective ships. In most games — particularly those with a low Ship Limit — minerals are more important than fuel.

## Fuel

The fuel rating of a planet. The actual fuel production is the lesser of the planet's fuel rating and its population. Fuel production contributes to tech development, and is important for keeping ships operating at peak efficiency. Fuel contrains the absolute capacity for operating fully-effective ships.

## Min Pop to Build

Also called build threshold, this is amount of population required for a system to achieve "builder status" — that is, to gain the ability to build ships there.

## Technologies

The sixteen ship types. When a player purchases a tech by spending a tech selection, he gains the ability to construct ships of that type.

- Movable ships
  - Pre-combat ships
    - Cloaker (Cl) — modified for v3
    - Science (Sc)
  - Mid-combat ships
    - Attack (At)
    - Carrier (Ca) — new ship type for v3
    - Minesweeper (Ms)
  - Post-combat ships
    - Builder (Bu) — new ship type for v3
    - Colony (Co)
    - Doomsday (Do) — slightly expanded role in v3
    - Engineer (En)
    - Terraformer (Tf)
    - Troopship (Tr)
  - Special ship
    - Morpher (Mo) — new ship type for v3
- Stationary ships
  - Defenses
    - Minefield (Mf)
    - Satellite (Sa)
  - Gates
    - Jumpgate (Jg) — new ship type for v3
    - Stargate (Sg) — modified for v3

The above list is a somewhat awkward attempt to group the sixteen ship types into classes. These classes tend to share certain properties that are relevant for "timing".

## Developed Techs

The techs that are known by a player when first joining the game.

## Unrestricted Techs

The techs that a player may freely purchase by spending tech selections.

## Restricted Techs

The techs whose selection is constrained; see the maximum number of restricted techs allowed.

## Restricted Techs Allowed

The maximum number of restricted techs that a player may know at a time. An empire that already has the maximum number cannot purchase another.

## Trade-Ins Allowed

The maximum number of restricted techs that an empire may "forget" during the course of the game. The term "trade-in" is a bit misleading, since an empire that forgets a tech this way does not get another tech in return. Instead, the player merely gets rid of a restricted tech that is counting against the maximum allowed. Assuming the player has at least one tech selection in reserve, he can then purchase a different restricted tech — one that otherwise would have been incompatible with his existing selections.

## Barred Techs

Any techs that do not appear in the list of developed, unrestricted, or restricted techs. Such techs are totally absent from the game.

## Movable Ships

A class of ships with the ability to move. There are actually two varieties of movement — "move" and "explore" — although most movable ships only have the first ability. An order to "move" enables a ship to enter a known system. An order to "explore" enables a science ship to enter an unknown system.
Movement occurs during the pre-combat phase. Therefore, a ship that is ordered to move out of a system will not fight there. It will fight in the new system if hostile ships are located there after all movement is completed.

When orbiting a hostile system, movable ships also have the ability to issue a nuke order. Nuke orders are resolved post-combat — the ship must survive combat for such an order to succeed. If successful:

- The system's population is eliminated.
- The system's resources are permanently halved.
- The system reverts to unowned status, and can be colonized again.
- If the system is the victim's homeword:
  - The victim is eliminated from the game.
  - The victim ships are all destroyed.
  - The victim's systems all revert to unowned status.
  - The victim's permanent record gets plus one "been nuked".
  - The nuker's permanent record gets plus one "nuke".

As with all ships, movable ships can be ordered to dismantle. Dismantling is pre-combat.

## Pre-combat ships

A class of ships whose unique abilities are resolved pre-combat.

### Cloaker

A movable ship type whose unique ability is to "cloak" and "uncloak". Such orders are resolved pre-combat. If uncloaked, the ship acts normally. If cloaked, the ship:

- Is invisible to other players.
- Does not participate in combat (although it can still be destroyed by a minefield, if something else causes the minefield to explode).
- Cannot issue nuke orders

In v3, cloakers are automatically cloaked when built. Actually, this feature is another series variable, but it happens to be turned "on" for all existing series.

### Science

A movable ship type whose special ability is to "explore" systems. Exploration occurs pre-combat. Once a system is explored:

- The system appears on the player's map, along with any unexplored jump points.
- The player's other ship types may "move" into that system.

## Mid-combat ships

A class of ships whose unique abilities are resolved during combat. Technically, the attack ship has no such abilities, but is included here because of its importance in combat.

### Attack

The cheapest movable ship type. It lacks any further abilities.

### Carrier

Sometimes called a super-attack, this is a very dangerous v3 movable ship type. It cannot perform any special orders, and it has no more damage-dealing firepower than any other ship. Instead, the carrier's strength is its resistance to destruction.
This resistance consists of a "quasi-immunity" to damage dealt during the DEST phase of combat. Whenever the battle routine targets a carrier for destruction from enemy DEST, a certain amount of DEST is countered and absorbed. The carrier takes only minor damage — Carrier Loss.

The exact amount of DEST absorbed is equal to the square of the carrier's current BR after taking Carrier Loss.

A carrier can be destroyed in three ways:

- Excessive Carrier Loss.
- By damage dealt as 2DV.
- By an exploding minefield.

By absorbing damage, a carrier also helps shield any other ships that the player has in the same system. However, a carrier cannot absorb damage destined for ships belonging to allies.

The carrier's resistance to destruction and tendency to shield other ships might sound like defensive abilities. Nevertheless, carriers are primarily an offensive weapon. To see why, consider a simple battle. One player has 10 carriers and the other has 15 attacks. Neither side has enough firepower to destroy the other's fleet — the carriers will destroy five attacks, while the attacks probably won't destroy any carriers. Hence both sides have ships that survive the battle. What happens when such a battle unfolds in an enemy system? That which survives, nukes!

### Minesweeper

A movable ship type with the special ability to prevent a minefield from exploding, assuming that at least one minesweeper survives the DEST and 2DV phases of combat.

## Post-combat ships

A class of ships whose unique abilities are resolved after combat. To use these abilities, a ship in this class must survive combat.

### Builder

This is a v3 movable ship type with the ability to create entire planets.
To create a new planet, construct some builder ships and move them to a system that adjoins an "empty" sector — one with no existing system. Give an order of the form "Create System(123,456)".

Creation orders are resolved post-combat, and succeed only if the total BR of the ship(s) giving the order is sufficiently high. The total BR must be at least one tenth of the game's Planet Create Cost. If the planet create cost is satisfied, the order is successful and a new planet is created. Either way, the builder ships are consumed in the attempt.

A newly created planet will have exactly average resources. The system is considered "explored" for the player that created it (see science).

If the builder ships all gave the creation order from the same point of origin, then there will also be a jump automatically opened between that point of origin and the new system.

If the ships were in different locations when executing the order, then the opening of the link seems variable:

- Stargate server — a jump is definitely opened, but perhaps not from the desired direction. There seems to be a specific order of preference for the various possible directions (I've forgotten the exact rule here)
- Lugdunum — no jump is opened (usually?)

For historical interest, see wormholes.

### Colony

A movable ship type whose special ability is to populate and claim ownership of an unoccupied system.
An order to colonize can be given only if the system has no existing population. If succesful, the colony ship is consumed, and the population of the system is set equal to the square of the current BR of the colony ship. If several colony ships attempt to colonize the same system, only one will succeed and be consumed.

### Doomsday

A movable ship type whose special ability is to annihilate systems. Annihilating a system permanently reduces its population and resources to zero, and precludes colonization. In v3, annihilation has one additional effect: it prevents a jumpgate from targetting that planet as a destination.

### Engineer

A movable ship type whose special ability is to open and close jumps between adjacent systems. Each operation (open or close) requires an order, and incurs Engineer Loss.

### Terraformer

A movable ship type whose special ability is to increase the agriculture rating of a system. Terraforming a system adds ten times the ship's current BR to the ag rating. The ship is consumed by the order.

### Troopship

A movable ship type whose special ability is to "invade" an enemy system, transferring control of the world and its population to the owner of the troopship. A troopship can successfully invade a planet with a population up to ten times its current BR. If the invasion order is unsuccessful, the population is reduced by twice the troopship's current BR. Other troopships that attempt invasion later in the same turn might then succeed against the smaller population.
Invasion orders are resolved after nuke orders. If a combination of attacking ships is ordered to simultaneously nuke and invade the same planet, the nuke, if successful, will occur first, reducing the population to zero, and greatly increasing the chance of a successful invasion. Such a combination is known as the "trooptrick".

## Special ship: Morpher

A v3 movable ship type that forms a class unto itself. Its special ability is to shapeshift into any other ship technology known by the same empire. Changing form:

- Incurs Morpher Loss.
- Requires an order ("Change into -type-").
- Resolves pre-combat (with a few quirks on Lugdunum).
- Enables the morpher to gain the abilities of the new form.

A morpher "appears as" a ship of the same type it is currently emulating. For example, a morpher that changes into a minesweeper will appear as such on the systems menu of any player who can see the ship, including the owner. Even the owner's ship menu will list the ship as a minesweeper. He can tell it's a morpher by looking at the orders available to the ship; if it can "change" into something else, then he knows it's really a morpher in minesweeper form.

The morpher-cloaker requires two additional points of explanation. First, when a morpher changes into cloaker form, it is automatically cloaked if the series variable for "cloakers cloaked when built" is "yes". If not, you will need a separate order to cloak the ship. Second, a cloaked morpher cannot be given orders to change into other ship types. You must uncloak it first.

Morphers are possibly the most challenging of all the techs. True mastery of the morpher first requires mastery of the other ship types. That alone is not enough — morphers can achieve certain strange things that no other ship type can.

For historical perspective, you may be interested to know that in older versions of the code, the morpher was an even stranger beast than it is now.

### Virgin Morphers

A morpher that had never morphed into another ship type was in a very privileged state. First of all, it was cloaked when built. If the ship changed into some other type, it lost virgin status — even if it changed back into "morpher" form, it would no longer be cloaked. When combined with the following two bullet points, this arguably made the Virgin Morpher the oddest entity in all of Stellar Crisis.

### Uncloak Method

A cloaked morpher could change directly to another ship type, say an (uncloaked) minefield. (This was very powerful, but due to the following bullet point, a bit less unbalanced than it sounds.)

### Uncloak Timing

An uncloaking morpher did not participate in combat on the turn it became visible. It fought the turn after, assuming it didn't recloak. One trick that exploited this behavior was the "unkillable ship" — which in reality was two ships in opposite phases, one cloaking as the other uncloaked. It was the ultimate in cloaker harrassment.

## Stationary Ships

A class of ships that lack the ability to move or nuke. Such ships are not transported by gates. Like any ship, a stationary ship can be ordered to dismantle before combat.

## Defenses

A class of stationary ships whose abilities are resolved during combat. Coincidentally, the ships in this class consume no fuel.

### Minefield

A defensive stationary ship type whose special abilities are as follows:

- Immunity to the DEST phase of combat. What this means is that the minefield will be "passed over" as a target for DEST. Unlike a carrier, it does not actually absorb DEST.
- Will "explode" under certain conditions, destroying all ships in the sector and killing half the population.

A minefield will only explode if:

- It is destroyed (by 2DV)
- On the turn the minefield is destroyed, no minesweepers are present in the same system after normal combat is resolved.

It is best to think of "explosion" as a third phase of combat that occurs after the DEST and 2DV phases are resolved. Even though explosions only happen after "normal" combat, they are still "part" of combat as a whole. Events that happen after combat — like gating — are resolved only after explosions.

### Satellite

A defensive stationary ship type. Has no special abilities. The cheapest of all ships.

## Gates

A class of stationary ships, consisting of jumpgate and stargate. Both types have the post-combat ability to transport other ships to distant systems. Though they differ in their details, they share many properties.
To use a gate, you provide it an order that specifies the coordinates of the desired destination for your transported ships. The destination must be within the gate's range as determined by current BR and the game's range settings. The destination will also have other requirements, depending on the type of gate.

The ships menu will only list legal destinations for a particular gate. In other words, if you can give the order, that system is a legal destination — at least at the time you gave the order.

However, gate orders only execute after combat, and can therefore fail if:

- The gate is destroyed.
- Some combination of overbuilding and/or combat lowers the gate's BR enough to put the intended target out of range.

If successful, transport occurs. However, gates only operate on ships that:

- Are movable.
- Are in the same system as the gate.
- Belong to the gate's owner.

Note that since transport is post-combat, freshly gated ships do not fight in the destination system on the turn of arrival.
For historical interest, see wormholes.

### Jumpgate

This is a v3 stationary ship type. See gates for an explanation of gate operation generally, including range limitations. A jumpgate differs from a stargate in that:

- Transport requires an order such as "Jump to System(123,456)".
- Jump orders incur Jumpgate Loss).
- The destination system must be:
  - Already explored (see science).
  - Not annihilated (see doomsday).

### Stargate

This is a v3 stationary ship type. See gates for an explanation of gate operation generally, including range limitations. The range requirement is what distinguishes the v3 stargate from earlier versions. A stargate is distinguished from a jumpgate in that:

- Transport requires an order such as "Send to System(123,456)".
- A Send order does not incur BR Loss.
- The destination system must belong to the gate's owner.

## Wormholes

At one time, a bug in the code allowed builder ships and gates to interact in a most peculiar way. Consider a group of builders with a gate in the same system (the "origin" system). The builders are ordered to create another system and the gate is ordered to transport the builders to a third. What happens? In the buggy version, the creation order worked, but no jump was opened between the origin system and the created system. Instead, the jump was opened between the created system and the gate's destination!
This type of non-linear jump point is known as a "wormhole". Intentionally creating a wormhole could potentially backfire. The wormhole worked in both directions and could be explored and used by any player. But to permanently rid yourself of the wormhole, you only had to close it with an engineer. Once closed, it could never be reopened.

## Loss

The amount of permanent BR lost by certain ships as a result of special events and actions:

- Carrier — when it absorbs an otherwise lethal dose of DEST.
- Engineer — when it attempts to open or close a jump.
- Jumpgate — when it attempts to transport ships to another system.
- Morpher — when it attempts to change into another ship type.

A ship that does not have sufficient BR to pay for Loss is destroyed, and any order it was trying to execute will fail. An engineer, jumpgate, or morpher whose BR is at least equal to the amount of Loss incurred, but less than twice this figure, is consumed by the (successful) execution of its orders.

## Range

The "distance" that a stargate or jumpgate can tranport ships, based on its current BR. On the official series parameters as seen on the server, the number that controls range is called a "range divisor". To determine how far the gate can jump or send, divide the gate's BR by the range divisor. If the range divisor is zero, the range is infinite. On the Bestiary chart, I've expressed the range controller in a way that I consider easier to understand (e.g., instead of saying the range divisor is 0.4, I would write that the range is 2.5 x BR).
The relevant distance between two systems is not the actual linear distance but the greater of the two orthogonal distances (vertical and horizontal). For example, the distance between (0,0) and (3,4) is four.

Range is actually checked twice:

- During display of the ships menu. Systems that are out of range of a given gate will not be selectable as the gate's destination.
- During order execution. A gate that is weakened due to combat or overbuild might not have sufficient BR to reach its intended destination. If so, no transport occurs.

## Special Build Cost

The amount of minerals required to build a ship of a certain type, above and beyond the amount needed as a function of its BR. Therefore the total cost in minerals to build such a ship is:

```
(BR + 4)² + Special Build Cost
```

## Special Maint Cost

The amount of minerals required to maintain a ship of a certain type, above an beyond the amount needed as a function of its BR. Therefore the total cost in minerals to maintain such a ship is:

```
2 x BR + Special Maint Cost
```

Note that the total fuel cost of a ship is always twice the total maintenance cost (except for satellites and minefields, which consume no fuel).

## Planet Create Cost

This number is used to determine whether builder ships succeed in creating a new planet. The total BR of the ships giving the create order is multiplied by ten, then compared against the create cost. If the total BR is high enough, the creation order succeeds. If not, the order fails. Either way, the builder ships are consumed.
