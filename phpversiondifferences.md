# SC PHP code vs. original code
Doa has provided a large list of differences between the php implementation and
the original implementation of Stellar Crisis.

# Numbers
1.  The .1's are gone and all ratios are true.
2.  Pop growth is faster.  The fractional part of pop is rounded instead of tossed before
adding 1.   SO 1.4 LIKE AT LUG GOES TO 2 BUT 1.6 GOES TO 3.  If pop is exactly .5 for
fractional part it alternates rounding up or down, so 1.5 rounds down, 2.5 rounds up, etc.
This means a pop trick that gets a planet to 48.5 will round up to 49 plus 1 is 50 and you
have a builder.

# Map
1.  The coordinates of planets are converted for each player to show as relative to that
player's homeworld.  
2.  You can set the homeworld coordinates fron the systems screen for your hw.  Many
players like either 0,0 or 50,50.
3.  All planets are assigned a randon name to provide a common point of reference since
one players coordinates are not the same as anothers.  Warning - Names occasionally get
duplicated.

# SHIPS
1.  Engineer loss is .5 not 1
2.  Neers can be set to open/close links even if their current br is below .5 and will
perform the order as long as they heal to above .5.
3.  Cloakers by default are built uncloaked as in old 2.8 but there are series options to
build them cloaked like at lug  or to have them appear as attacks when they to other
people when they uncloak.  or both.
4.  There is a profile setting to control if your ships are arranged by ship type or
location when you view the ships scren.

# COMBAT
1.  Nuke and troop doesnt work.  Troopships will only invade if the planet is not nuked.
2.  Empires are not removed until all nukes are completed for the round meaning that in a
simultaneous nuke situation players can nuke each other out of the game.
3.  In games allowing surrender,  a surrendering player is removed.   If there is only one
player left he gets the nuke credit, otherwise the nuke is lost and the surrendering
player is nuked with no one getting the credit.  There is no colonizing the hw to claim
the nuke of a surrendered player.

# Bridier
1.  All grudges may be started as bridier or non bridier.
2.  Bridier significance adjusts at 2x the old rate
3.  Until you join a game, you can see only your potential gain maxed as 5 or more  and
not the potential loss.   The actual gain/loss are visible once you join.

# BUILD VISIBILITY
The server specifies invisible builds as default but has the ability to have games with
visible builds.  The games offered do not have visible builds but it is possible someone
created a custom game with that enabled.

# NEW FEATURES
# MAP TYPES
In addition to classic/balanced and mirror, there is now also twisted.  While mirror maps
creat mirror systems and reflected or flipped the map across the mirror, twisted maps
simply rotate the first players map and join it back with no extra systems.  Compare the
two below...
A-H-B-M-b-h-a
    C-M-c
    D-M-d

A-H-B-d
    C-c
    D-b-h-a


# GAMES
There is a new game type called team game.  Team games do not start until totally filled
and the players are pre assigned to one of two teams.  you must still ally with your
teammates but the alliances are predetermined.   All players on the team will get credit
for the win whether they survive til the end or not.

# Diplomacy
1.  A powerful new setting above alliance is available in some games called SHARED-Hq.  
This setting immediiately makes all systems that either payer has explored available to
both players as if they had explored them and populates the map accordingly.  It also
allows players to jump to each others planets.   Note that should your share hq partner
get nuked you will lose access to the planets unless you had actually had a sci pass thru
them.
2.   There is A TEAM RADIO option that sends to all players on your team.  In team games
this includes members you haven't met.  In other games I believe it includes only allies.

# FLEETS
1.  Fleets appear on the ships screen after other ships so you can do your orders from one
place and not forget that fleet you created.
2.  Fleet orders include nuke and colonize and the ships in the fleet will perform the
function if appropriate ones survive.
3.  Fleet creation includes a choice to gather all ships at the planet into the fleet so
you don't have to add them individually.
4.  Fleet orders include options to disband or to disband the fleet and also all the
ships in it.
5.  Fleets may be shown condensed or expanded on the fleet screen.

# SCOUTING REPORTS
You may select systems to include or include all systems and send a scouting report to 
anyone you have met.  This will cause the systems to be added to their map.  They still 
have to be explored and no updates about what happens to them will be shared other than 
by further scouting reports.  They are distinguished by having a ? for the number of 
ships.

# Mini MAPS
In large games there is a mini maps option to get a look at the map as tiny icons without
the links but able to see the whole map.  This sometimes helps when you want a big picture
view of things.

# OTHER
1.  Some use of color to indicate status
2.  Custom games let people create their own series which are then available to anyone to
start under the custom games tab.
3.  You can see who is online and send them messages which they will see when they exit
games and return to main menu.
