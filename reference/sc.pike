/* Stellar Crisis module for Roxen (http://www.roxen.com/)
   Copyright (C)1996 by Sylvan Clebsch

   This program is free software; you can redistribute it and/or modify
   it under the terms of the GNU General Public License as published by
   the Free Software Foundation; either version 2 of the License, or
   (at your option) any later version.

   This program is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with this program; if not, write to the Free Software
   Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.

   You can contact the author, Sylvan Clebsch, via email at
   sylvan@gnu.ai.mit.edu or via paper mail at:

   Sylvan Clebsch
   334 Hawthorne St.
   Palo Alto, CA 94301

   You can also finger sylvan@gnu.ai.mit.edu for not very up to date info.
   This package should include this file, a support directory full of
   graphics and a brief instructions file. Good luck, you'll need it...

   Version 2.9 modifications by Del Hart, hart@cs.wustl.edu
        Administration modifications by Rowan Deppeler, rdepp@cybernex.net.au

   Version 3.0 changes by Del Hart, hart@cs.wustl.edu
            with a bit of help from Rowan Deppeler, rdepp@cybernex.net.au
   Version 3.01 changes for Roxen V1.2 by Rowan Deppeler, rdepp@cybernex.net.au

   Version 3.01.4 changes by Andy Graves.
       Fixed the build hack.
       Reinstated limits on number of wins to join a series.
       Fixed Dunwell's bug (trade-in button outside table).
       Fixed an indexing error in the engineering code in discussion with Del.
       A few cosmetic text layout tidy-ups.
       Fixed the 'Player Visibility' anomaly.
       Renamed 'vis' to 'visb', called it 'Build Visibility' and reversed its sense.
         Fixed the bug whereby it showed up in reverse on the Detailed Game List.
       Altered do_dump_data () to use admin_inc instead of hard-coded 7200.
       Implemented Del's fix for the End Turn horror.
       Fixed spelling of 'etiquette'.
       Altered admin 'FAQ URL' parameter to 'Help URL' as the FAQ is something else.
       Gave the SC FAQ button its own configuration parameter, now correctly called 'FAQ URL'.
       Fixed the bug whereby the most ships you could build was one less than the maximum specified for the series.
       Changed the default Max Ag Ratio from 1 (unplayable) to 100 (easy tricking).
       Changed the default Max No. of Wins from 999 to 9999 in case The Voyager plays v3.
       Altered the Game List to say "Updates every x" instead of "Updates every x for the first y then every z" when x = z.
       Attempting to make the code work with both Roxen 1.1 and 1.2 with a single #define alteration.
       Made the server's main text colour configurable at the Roxen admin level, and each series can have its own text colour indepentently.
       Removed 'Restricted Game' and 'Joining Password' from the Start Series and Edit Series screens. They were causing trouble.
       Straightened up the headers on the Admin Game View table.
       Made the buttons on the 'Profile: Select Alien' screen match the number of aliens specified in Roxen config.
       The phrase "Detailed Game List" seems more like English to me than "Game Detailed List" :)
       That which has traditionally been called a Win/Nuke ratio is in fact a Nukes/Game ratio.
       First attempt to fix the Homeworld Invasion bug, with a suggestion from Del.

   Version 3.01.5 changes by Andy Graves.
       Alteration to the mirror map generation code to ensure the longest
         possible border and slightly less resource-rich buffer planets.
   ??    Improvement to the ships screen - ships are now shown in the same
         order that they appear in the systems screen.

   Version 3.01.6 changes by Rowan Deppeler (Frodo)   {a large amount done before Andy's fixes but not released}
       Corrected auto update javascript problem when using graphic buttons.
       Add game name & number to page headers.
       Clean up some redundant code ie case statements etc.
       Add random systems per player as per SC2.8.6 - Aliza/Buglady I think?
       Clean up additional message text in series displays.
       Re-coded Tech screen to ensure legibility and to keep everything lined up!
       Re-coded systems screen using color coding and table formatting.
       Added planet color coding on the map screen based upon type of greatest resources.
       Added Alt tags to graphic buttons.
       Tried a fix for the 'no spawning after max games' bug.
       Fixed bug when logging in anonymously for game stats etc.
       Added an admin 'broadcast message to all players' option.
       Added database cleanup (clear out unused empires) and player cleanup functions. (uninitialised variables from old versions)
       Corrected 'Weekend update' checkbox processing in edit series.
       Started adding user 'game admin' stuff.

    Version 3.01.7 changes by Rowan Deppeler (Frodo)
       Incorporated Andy's mirror stuff aka V3.01.5 above.
       Added brief game info header to login game list.
       Some minor mods to game list information.
       Modified player flags handling to allow easier use.
       Fixed a lot of bugs with the random number of systems game generation.
       Fixed a few small admin bugs in layouts and series editing.
       Added javascript counter to pages if requested.

    Version 3.01.7a changes by Rowan Deppeler (Frodo)
       Fixed Game full description formatting problem.
       Updated some minor formatting alloyances in game list.
       Fixed phrasing of tech developments header in info_screen()

    Version 3.01.7a changes by Rowan Deppeler (Frodo)
       Added variable planet resource options. (Created on an individual game basis)

    Version 3.01.8 changes by Del Hart
       Fix morphers so they cloak correctly. Morphers start visible, they can morph into cloakers
        they can't morph while cloaked
       Variablize cloakers cloaked when built,  check the table layout on this....
       Fix carrier truncation of missive
       Fix draw in history
       Added notification of game ending for losers
       Changed add history to be more of a general game cleanup , it does the history, notification, and the game deletion
       Added hooks for checking the number of planets built
       Fix the minefield over an empty planet bug,
       Attempt at fixing max alliance code.

    Version 3.01.9 changes by Rowan Deppeler (Frodo)
       Altered add_history() to write each completed game to a html file for later viewing.

    Version 3.01.10 changes by Rowan Deppeler (Frodo)
      Put back ASCII database save routines.
      Added Admin functions to allow save/load as required.
      Added Server setup option to allow specification of default database type.

    Version 3.01.11 changes by Rowan Deppeler (Frodo)
      Filtered out '%' in Sterilisestring() to prevent cheating nasties

    Version 3.01.12 changes by Andy Graves (Lugdunum) October 1999
      Fixed a few small omissions to keep it working with Roxen 1.1.
      Restored the ships screen ordering code - there is no problem with 'implications'. :)
      Made Separate Data Color (sic!) a Player Profile option. The Admin has no such luck.
      Made new Ships screen column order a server-wide config option.
      Added a possible fix for the problem where maps with negative co-ordinates
          contained tracts of empty space.
      Made the text on the top row of buttons on each screen black.
          As far as I can tell, this affects only X-windows users.
      Removed the 'Upload custom icon' button - it was only causing confusion.
      Some players were having an inactive 'Admin' button added to their Game List. Removed it for now.
      Major changes to Game List:
        Open games are listed before games which you can start.
        Detailed Game List shows only relevent detail.
        Number precision improved.
      Fixed the bug which gives 'Indexing a basic type' error when a player being nuked is last to press ET.

    Version 3.01.13 changes by Del Hart January 2000
      Fixed first update delayed by
      added spaces to carrier and morpher messages
      eliminated wormholes
      removed brs from annihilated planet names
      fixed tech selection to stay nonnegative

    Version 3.01.14 changes by Del Hart January 2000
      Reworked the variable planet resource options

    Version 3.01.15 changes by Andy Graves January 2000
      Fixed a bug which prevented the variable-systems-per-player feature from being added to old series.

    Version 3.01.16 changes by Andy Graves January 2000
      Enhancement to Ratios line, and added it to some other screens.
      Attempt to rid the galaxy of negative co-ordinates.
      Another little change to the Game List (Series name on Detail button)
      A much-requested cosmetic fix to the <title> string.
      A couple more fixes for truncated update text. Hopefully this is the last of them :)
      Fixed do_dump_data() to respect the ASCII database choice when doing a regular admin dump.
      User's Stat Viewer no longer grasses up IP Address. Admin Empire View still does, of course :)
      First attempt at 'Projected BR' column on Ships screen, but I commented it out coz I got scared.
      Cleaning up (s)printf() calls, to prevent anything else like the '%'-cheat.
      Added a WebTV mode, to try to help those poor people out of their misery.
        First enhancement is a wider max pop field on the systems screen.
      Game List no longer tries to predict the First update time of a game with < 2 players.
      Slight improvement to the Ships screen, and an attempt to make the equivalent improvement
        to the Fleets screen to the one I made to Ships in v3.01.5.
      Tech screen improvements - thanks Del.

    Version 3.01.17 changes by Andy Graves January 2000
      Tidied up the bottom line of the history. That comma was really annoying me :)
      First stab at fixing the surrender awards code as I want it.
      Unbottled on the Projected BR.
      Fixed a wee buggette in gen_mirror() - val initialisation misplaced.

    Version 3.01.18 changes by Andy Graves January 2000
      Removed the Separate Map flag and the associated redundant code.
      Added Show Next BR flag, so people can choose not to see it if their screen is cluttered.
      Removed the Update Countdown flag.
      Added Enhanced Ratios Line flag. Some poor people have to play with 640x480!
      Fixed the bug whereby only the first word of an Empire Name showed on the Create New Empire
        and Edit Profile screens. There was no need to have it a modifiable field at all.
      Fixed the overallying bug - again!  :)
      Made Surrender and Draw available only if permitted by a series option.

    Version 3.01.19 changes by Andy Graves January 2000
      Fixed bugs in Surrender awards and Game List.

    Version 3.01.20 changes by Andy Graves January 2000
      Fixed bug which could cause premature game end if one player was allied with everyone else.

    Version 3.01.21 changes by Andy Graves January 2000
      Another revision to the 'First Update Delay' code.
      A cloaked cloaker over a system owned by an unmet player could cause First Contact - no more.
      However, that being the case, it's now necessary that uncloaking a cloaker over such a
        system *should* cause First Contact.

    Version 3.01.22 changes by Andy Graves January 2000
      Incorporated Bridier Ranking System (Grudge games only).
      Set player's last update on pressing End Turn, so that someone who ends turn but doesn't do 
        anything else doesn't go idle.
      History - added more diplomacy notes and reinstated the note of ship destruction.
      First stab at getting rid of the long strings of commas from the update text.
      If builds are invisible, also make changes in pop and max_pop of a planet
        invisible to other than the owner until after the update.
      Record players in the same game with the same IP address.

    Version 3.01.23 changes by Andy Graves January 2000
      Attempt to be stricter about what is allowed in names (empire, planet, ship, fleet).
      Moved ship destruction history outside the player loop.

    Version 3.01.24 changes by Andy Graves January 2000
      Bug fixes to create_player(), Admin Top Ten, and sterilizeName().
      Map in History.

    Version 3.01.25 changes by Andy Graves February 2000
      More History cosmetic changes.

    Version 3.01.26 changes by Andy Graves February 2000
      Yet more History cosmetic changes.

    Version 3.2.0 4th March 2000 by Andy Graves
      No more changes, just an official release.

    Version 3.2.1 7th March 2000 by Andy Graves
      Kludge to let the virgin morphers out of limbo.

    Version 3.2.2 18th March 2000 by Andy Graves
      "When Morphers change into Cloakers, whether they are immediately cloaked or require
       a turn to do so is governed by the 'Cloakers built cloaked' setting for the series"
      I wrote that in the release notes, but it wasn't true. Del has made it true with this patch.
      A cloaker which was built uncloaked nevertheless did not fight on the turn it was built. Fixed.
      A morpher which was consumed by changing effort was being changed anyway, but not any more.
      Put Ratios Line on map screen.
      Put a 3x3 maplet on the single-planet Systems screen.
      Made the map scalable and re-centerable in WebTV mode.

    Version 3.2.3 4th April 2000 by Andy Graves
      Added Next BR feature to the Fleets screen.
      Fixed the bug which prevented planet2.gif appearing for annihilated planets.
      When Minerals and Agriculture for a planet were equal, but Fuel was less than either,
        the map nevertheless showed planet.gif (the Fuel-planet icon). Fixed.
      Fixed a cosmetic bug in Admin Empire View - WebTV Mode indicator outside table.

    Version 3.2.4 26th April 2000 by Andy Graves
      Alterations to series definition defaults.
      Allowed Max Ships parameter of zero to mean No Limit.
      Improved cheat reporting.
      Fixed a bug in add_history() which was trying to draw the map when the game
        had no winner (all had ruined).

    Version 3.2.5 6th August 2000 by Andy Graves
      Bridier awards are now based on starting, rather than ending, Rank and Index.
      Game List shows potential Bridier awards - Win only until the game is joined;
        thereafter both Win and Lose potential.
      The Empire who starts a Bridier game is allowed to select a Challenge level,
        to prevent games with zero or tiny awards.
      According to a series option, Map and Systems screens may be hidden from 
        player 1 until player 2 has joined.
      Gate range is now subject to combat loss as well as overbuild loss.
      Detailed Game List no longer offered - superceded by the series details buttons.
      Split Game List server option. If selected, Open and New Game Lists separate
        from main Game List which shows only games the Empire is already playing.
      History list is in cronological order, without the strange discontinuities.
      gen_mirror() altered to load the choice of homeworld location away from the
        pivot edge.
      gen_mirror() altered to allow the possibility of crosslinks between the border
        planets.

    Version 3.2.6 17th August 2000 by Andy Graves
      Cosmetic map tidying:
        - Transparent spacers where no links exists between planets: horzt.gif and vertt.gif.
        - wrap names of annihilated planets.
      Fixed the Carrier 'current BR less than loss' bug - thanks Del & Eric.
      Fixed the Max Pop on Built Planets bug.
      'Allowed number of Restricted:' and 'Number of Trade-ins:' only appears when there are
        Restricted Techs specified.
      No ship limit (max_ship = 0) shows as "No Limit" rather than "0" on the series details.
      Bridier award table now shows on the end of game message as well as the history.
      Added <br> to the report of game-end to ex-players.

    Version 3.2.7 28th August 2000 by Andy Graves
      Fixed the bug in apply_bridier which allowed index to drop below 100.

    Version 3.2.8 7th September 2000 by Andy Graves
      Fixed the bug which crashed the system if a Bridier game ended in a ruin.
      Removed varargs from dump_data() and dump_asciidata(), for Roxen 1.3.

    Version 3.2.9 7th September 2000 by Andy Graves
      Some more changes for Roxen 1.3 compatibility.
      Added optional mouse-sensitivity to maps (mini-systems window).
      Improved themability of some images.
      Removed the duplicate 'time till update' message given to those who don't choose 'Update Countdown'.
      Added Victory Sneer to profile.

    Version 3.2.10 12th December 2000 by Andy Graves
      Attached the Auto Update feature to its flag.
      Implementing per-game passwords.
      Removed redundant password field from the Creating a New Empire screen.

    Version 3.2.11 8th January 2001 by Andy Graves
      Made fuel ratio apply to ship's resistence to damage, as well as its ability to inflict damage.
      Altered the position of the Login button on the front screen, so that it doesn't look so silly on MSIE.
      Altered the Game Password feature so that it only works with Spawn First series - otherwise it's too easy for a miscreant to lock the series up.
      Multi-player password-protected games now show as such on the Open Game List after the second player enters.
      Pressing Enter instead of hitting Submit Password on the Game Password screen now works.
      Fixed the bug whereby having the Auto-update flag unset made game text go bright red.
      The defence against pressing End Turn after the update had occurred (update since last access) wasn't working, but is now.
      Made the mini-systems pop-up show the right population figures to the system owner.
      Implemented Unend Turn.
      Changed the Reset Next Update feature to save the database, rather than load it, to respond sensibly to the asciidata flag,
        and to send a meaningful message back to the administrator, rather than the former meaningless screen with button.

    Version 3.2.12 19th January 2002 by Andy Graves
      "Sorry, your Bridier status is inadequate for this game" should no longer appear. Such games don't show up on the Open Game List,
        unless they are password-protected. Knowing the password overrides any challenge-level consideration.
      Implemented the hidden e-mail address option. Players can hide their e-mail address from other players, but not from the administrator. This is to encourage 
        players to register an address even if they want to remain anonymous, so the admin can mail lost passwords.
      Tidied up the Stat Viewers for both the admin and players. In particular, removed the sponsor/team stuff.
      Removed "Restricted games" and "Joining password" from the Series Create and Series Edit screens. They didn't do anything anyway.
      Series now appear on the Game Lists in the order specified in the empire's Profile.
      Removed the hardly necessary "always available" text from Game List series descriptions.

    Version 3.2.13 January 2002 by Andy Graves
      Fixed bug (misplaced end-of-comment) in empire_view_data().
      Abandoned the "update since last access" defence, as it caused all kinds of curious effects on weekends. This should be fixed *after*
        the update timing code is revamped so that the server actually has a clue when the last update really was.
    
    Version 3.2.14 27 Feb 2002
      Applied missive patch (which has been around for a while) (D.Moreaux)
      Fixed problems when mountpoint is different from / (D.Moreaux)
      Series list now display if you are idle (D.Moreaux)
      Fixed max_pop when colonizing a planet owned by someone else (agt)
      A player can be banned from any alliance with player:ALL <D.Moreaux)

   */


#include <module.h>

#undef SC_DEBUG
#define MAX_FD 250
#define true 1
#define false 0

#define MAX(a,b) ((a) > (b) ? (a) : (b))

// database scan stuff for game lists
#define DBSTUFF \
             "u_time": \
        case "time_limit": \
        case "over_time": \
        case "u_delay": \
        case "weekend": \
        case "from": \
        case "to": \
        case "p_max": \
        case "s_add": \
        case "s_min": \
        case "s_max": \
        case "min_rsrc": \
        case "max_rsrc": \
        case "min_ag": \
        case "max_ag": \
        case "blood": \
        case "layout": \
        case "t_mult": \
        case "wins": \
        case "winmax": \
        case "simage": \
        case "g_cnt": \
        case "r_tech": \
        case "u_tech": \
        case "d_tech": \
        case "ar_tech": \
        case "tr_tech": \
        case "color" : \
        case "vis": \
        case "visp": \
        case "init_cloak" : \
        case "spawn_first": \
        case "bridier": \
        case "stargate": \
        case "jumpgate": \
        case "eng_loss": \
        case "jump_loss": \
        case "morph_loss": \
        case "car_loss": \
        case "max": \
        case "max_ship": \
        case "init_tech": \
        case "max_allies": \
        case "surrdraw": \
        case "morpher_build": \
        case "morpher_maint": \
        case "builder_build": \
        case "builder_maint": \
        case "jumpgate_build": \
        case "jumpgate_maint": \
        case "carrier_build": \
        case "carrier_maint": \
        case "avg_ag": \
        case "avg_min": \
        case "avg_fuel": \
        case "range_ag": \
        case "range_min": \
        case "range_fuel": \
        case "home_ag": \
        case "home_min": \
        case "home_fuel": \
        case "pla_build": \
        case "min_build": \
        case "full_desc": \
        case "blind": \
        case "desc": \
        case "count": \
        case "s_pass" : \
        case "restricted"

// APG 15/01/1999:
// I was unable to compile Roxen 1.1 as downloaded, because the Linux C library
// already had a function clone(). So I altered all instances of clone() in the
// Roxen source to clonex(). If you did this too, uncomment the next line:
// #define clone clonex


///////////////// Series variables
// u_delay  - how many extra seconds before the first update
// s_min    - minimum number of planets per player
// s_max    - maximum number of planets per player
// s_add    - (deprecated?) average number of planets per player
// min_rsrc - (deprecated)
// max_rsrc - (deprecated)
// avg_ag, avg_min, avg_fuel - really minimum ("avg" is for backwards // compatability)
//                             average of that resource per planet
// range_ag, range_min, range_fuel - how much above the avg_X the resource
//                                   average can be

///////////////// Game variables
// u_first - when the first update occurs
// u_last  - time of the last update
// u_time  - how long between updates
// s_add   - number of planets per player in this game
// avg_ag, avg_min, avg_fuel - the average amount of each resource for this
//                             excluding the homeworlds

// Uncomment the correct line for the Roxen version in use:
// #define ROXEN_VER "1.1"
#define ROXEN_VER "1.2"

inherit "module";
inherit "roxenlib";


// Change this if any mods are done
string version="3.2.14pre1";

mapping d=([]);
mapping p=([]);
mapping top=([]);

int last_admin=time();
int max_planets = 1000;  // arbitrary limit on the number of planets

// fix: add Engineer to both lists
string *all_tech=({"Attack", "Science", "Colony", "Stargate", "Cloaker",
                   "Satellite", "Terraformer", "Troopship", "Doomsday",
                   "Minefield", "Minesweeper", "Engineer",
                   "Builder", "Morpher", "Carrier", "Jumpgate"});
string *move_ships=({"Attack", "Science", "Colony", "Cloaker", "Terraformer",
                     "Troopship", "Doomsday", "Minesweeper", "Engineer",
                     "Builder", "Morpher", "Carrier"});

//string SERIES_COLOR = "#00C1FF";   // light blue
//string TEXT_COLOR = "#00FFDA";     // pale blue/green       FFE000";     // gold
//string LOGIN_COLOR = "#00C1FF";    // blue/green
string VLINK_COLOR = "#00C1FF";    // light blue
string LNK_COLOR = "#00C1FF";      // light blue
string ALINK_COLOR = "#FF9462";    // peach
string LOG_COLOR = "#00FFDA";      // pale blue/green
string MISS_COLOR = "#CCFFCC";     // pale yellow
string MESSG_COLOR = "CCFFFF";     // lavendar
string MAIL_COLOR = "#0066FF";     // nice blue
string READY_COLOR = "#00FF00";    // green
string NOT_READY_COLOR = "#FF0000";// red
string IDLE_COLOR = "#FF0000";      // red
string OPPONENT_COLOR = "#FFFF00"; // yellow
string STAT_COLOR = "#00C1FF";     // blue/green
string DATA_COLOR = "#FFFFCC";     // light yellow


/* ----------- Player option definitions ------------- */
int flags_bgimage = 1;
int flags_autoupd = 2;
int flags_eratios = 4;
int flags_gbutton = 8;
int flags_usejavascript = 16;
int flags_nextbr = 32;
int flags_sepdata = 64;
int flags_webtv = 128;
int flags_sensitivemap = 256;


mapping truceban; // empires that aren't allowed to truce.

array register_module()
{
  return ({
    MODULE_LOCATION,
      "Stellar Crisis "+version,
      "This is the Stellar Crisis game module. The support directory must "
      "be mounted as a filesystem.",
      });
}

#define S_RATIOS_LINE \
sprintf(\
"<table border=0 width=100%%><tr valign=top>"\
        "<td>Maintenance Ratio:</td><td>"+s_font+"%0.3f"+e_font+"</td>"\
        "<td>Fuel Ratio:</td><td>"+s_font+"%0.3f"+e_font+"</td>"\
        "<td>Tech Level:</td><td>"+s_font+"%0.3f"+e_font+"</td>"\
        "<td>Tech Development:</td><td>"+s_font+"%0.3f"+e_font+"</td>"\
        "</tr></table>"\
        "<p><img src=\""+theme_dir(name)+"/spacerule.gif\" height=10 width=100%%><p>", \
        d[series][game]["p"][name]["min_r"],\
        d[series][game]["p"][name]["fuel_r"],\
        d[series][game]["p"][name]["tech"],\
        d[series][game]["p"][name]["tech_d"])

#define E_RATIOS_LINE \
sprintf(\
"<table border=0 width=100%%><tr valign=top>"\
        "<td>Minerals:</td><td>"+s_font+"%d"+e_font+"</td>"\
        "<td>Fuel:</td><td>"+s_font+"%d"+e_font+"</td>"\
        "<td>Agriculture:</td><td>"+s_font+"%d"+e_font+"</td>"\
        "<td>Ag Ratio:</td><td>"+s_font+"%0.3f"+e_font+"</td>"\
        "</tr><tr>"\
        "<td>Maintenance Ratio:</td><td>"+s_font+"%0.3f"+e_font+"</td>"\
        "<td>Fuel Ratio:</td><td>"+s_font+"%0.3f"+e_font+"</td>"\
        "<td>Tech Level:</td><td>"+s_font+"%0.3f"+e_font+"</td>"\
        "<td>Tech Development:</td><td>"+s_font+"%0.3f"+e_font+"</td>"\
        "</tr></table>"\
        "<p><img src=\""+theme_dir(name)+"/spacerule.gif\" height=10 width=100%%><p>", \
        d[series][game]["p"][name]["min"],\
        d[series][game]["p"][name]["fuel"],\
        d[series][game]["p"][name]["ag"],\
        d[series][game]["p"][name]["ag_r"],\
        d[series][game]["p"][name]["min_r"],\
        d[series][game]["p"][name]["fuel_r"],\
        d[series][game]["p"][name]["tech"],\
        d[series][game]["p"][name]["tech_d"])

#define RATIOS_LINE ((p[name]["flags"] & flags_eratios)?E_RATIOS_LINE:S_RATIOS_LINE)

static string server_color, heading_color, help_url, faq_url;
static string mountpoint, datapath;
static string html_dir, ad_name, ad_mail, ad_pass;
static string dump_file, debug_file, hist_file, servername, log_msg;
static string history_1, history_2, history_3;
static int aliens, avg_rsrc, home_rsrc, admin_inc, close_game, hist_buff;
static int shipscr_format, glist_format, toplist, enable_bridier, split_gl;

string query_location() { return mountpoint; }
string query_name() { return "Mounted on "+mountpoint; }

string theme_dir(string);

string map_hist(string name, string series, string game)
{
  string map="";
  string ixa, la, lb, lc, jb, jf, gf;
  string *jump;
  int x, y, x_max, y_max, x_min, y_min, own, other;
  int mine, fuel, agri, firstplanet = 1;
  string s_font="<font size=1>";

  foreach(indices(d[series][game]["s"]), ixa) {
    sscanf(ixa, "%d,%d", x, y);
    if (firstplanet == 1) {
      firstplanet = 0;
      x_min = x_max = x;
      y_min = y_max = y;
    } else {
      if(x<x_min) x_min=x;
      if(x>x_max) x_max=x;
      if(y<y_min) y_min=y;
      if(y>y_max) y_max=y;
    }
  }

  mapping sf=([]);

  // 2.9C count the number of ships at each world
  foreach(indices(d[series][game]["f"]), ixa) {
    if(!mappingp(sf[d[series][game]["f"][ixa]["loc"]])) {
      sf[d[series][game]["f"][ixa]["loc"]]=([]);
    } // if

    if(d[series][game]["f"][ixa]["owner"]==name) {
      sf[d[series][game]["f"][ixa]["loc"]]["own"]++;
    } else {
      sf[d[series][game]["f"][ixa]["loc"]]["other"]++;
    } // if
  } // 2.9C foreach ship

  for(y=y_max; y>=y_min; y--) {
    la=lb=lc="<tr>";
    for(x=x_min; x<=x_max; x++) {
      if(search(indices(d[series][game]["s"]), x+","+y)!=-1) {
        jump=d[series][game]["s"][x+","+y]["jump"]/" ";

        if(search(jump, x+","+(y+1))!=-1)
          la+="<td></td><td align=center><img src=\""+theme_dir("")+"/vert.gif\"></td><td></td>";
        else
          la+="<td></td><td align=center><img src=\""+theme_dir("")+"/vertt.gif\"></td><td></td>";

        if(search(jump, x+","+(y-1))!=-1)
          lc+="<td></td><td align=center><img src=\""+theme_dir("")+"/vert.gif\"></td><td></td>";
        else
          lc+="<td></td><td align=center><img src=\""+theme_dir("")+"/vertt.gif\"></td><td></td>";

        if(search(jump, (x-1)+","+y)!=-1)
          jb="<img src=\""+theme_dir("")+"/horz.gif\">";
        else
          jb="<img src=\""+theme_dir("")+"/horzt.gif\">";

        if(search(jump, (x+1)+","+y)!=-1)
          jf="<img src=\""+theme_dir("")+"/horz.gif\">";
        else
          jf="<img src=\""+theme_dir("")+"/horzt.gif\">";

        mine = d[series][game]["s"][x+","+y]["min"];
        fuel = d[series][game]["s"][x+","+y]["fuel"];
        agri = d[series][game]["s"][x+","+y]["ag"];

        if(d[series][game]["s"][x+","+y]["annihilated"]) {
          gf="<img border=0 src=\""+theme_dir("")+"/planet2.gif\" alt=\"m:"+x+","+y+"\">";
        } else if(d[series][game]["s"][x+","+y]["owner"]=="") {
          if ( (mine > fuel) && (mine > agri) )
            gf="<img border=0 src=\""+theme_dir("")+"/planetm.gif\" alt=\"m:"+x+","+y+"\">";
          else if ( (agri >= mine) && (agri > fuel) )
            gf="<img border=0 src=\""+theme_dir("")+"/planetg.gif\" alt=\"m:"+x+","+y+"\">";
          else
            gf="<img border=0 src=\""+theme_dir("")+"/planet.gif\" alt=\"m:"+x+","+y+"\">";
        } else if(d[series][game]["s"][x+","+y]["owner"]==name) {
          gf=("<img border=1 alt=\"m:"+x+","+y+"\" src=\""+html_dir+
              "/aliens/"+p[name]["alien"]+"\">");
        } else {
          gf=("<img border=0 alt=\"m:"+x+","+y+"\" src=\""+html_dir+
              "/aliens/"+p[d[series][game]["s"][x+","+y]["owner"]]["alien"]+"\">");
        }
        if(mappingp(sf[x+","+y])) {
          own=sf[x+","+y]["own"];
          other=sf[x+","+y]["other"];
        } else {
          own=0;
          other=0;
        }

        lb+=("<td>"+jb+"</td>"+
             "<td><table border=0 cellspacing=0 cellpadding=0><tr>"+
             "<td align=center>"+s_font+d[series][game]["s"][x+","+y]["min"]+"</font></td>"+
             "<td rowspan=3>"+gf+"</td>"+
             "<td align=center>"+s_font+d[series][game]["s"][x+","+y]["fuel"]+"</font></td>"+
             "</tr><tr>"+
             "<td align=center>"+s_font+d[series][game]["s"][x+","+y]["ag"]+"</font></td>"+
             "<td align=center>"+s_font+((d[series]["vis"]||d[series][game]["s"][x+","+y]["owner"]==name)?
             d[series][game]["s"][x+","+y]["pop"]:d[series][game]["s"][x+","+y]["vis_pop"])+"</font></td>"+
             "</tr><tr>"+
             "<td align=center>"+s_font+"("+own+")</font></td>"+
             "<td align=center>"+s_font+"("+other+")</font></td>"+
             "</tr><tr>"+
             "<td colspan=3 align=center><font size=1>"+
             replace(d[series][game]["s"][x+","+y]["name"], ({"remains of "}), ({"remains of<br>"}))+
             (d[series][game]["s"][x+","+y]["home"]!=""?"<br>("+d[series][game]["s"][x+","+y]["home"]+" HW)":"")+
             "</font></td>"+
             "</tr></table></td><td>"+jf+"</td>");
      } else {
        // 2.9 we need to insert a empty picture here to keep
        // the spacing correct, if it is the min
        if ((x==x_min) || (y==y_max)) {
          la+="<td></td><td></td><td></td>";
          lb+="<td></td><td align=center><img src=\""+theme_dir("")+"/empty.gif\"></td><td></td>";
          lc+="<td></td><td></td><td></td>";
        } else {
          la+="<td></td><td></td><td></td>";
          lb+="<td></td><td></td><td></td>";
          lc+="<td></td><td></td><td></td>";
        } // if need spacing
      } // 2.9C if
    } // 2.9C for
    map+=la+"</tr>"+lb+"</tr>"+lc+"</tr>";
  } // 2.9C for

  return ("<b>Game Map for "+name+" (Update "+d[series][game]["u_cnt"]+")</b><p><table border=0 cellpadding=0 cellspacing=0>"+map+"</table>");
} // 2.9C map_hist

// 3 make the history info available
// I've changed this a bit to make it more of a general game end cleanup
void add_history (string series, string game, string result)
{
  string game_file, i_game_file;
  string hist_header;
  string hist_footer;
  string winner, ixa;
  string map;

  // add the history of this game to the global history
  hist_buff++;
  if (hist_buff > 20) { // magic number
    hist_buff = 0;
    history_1 = history_2;
    history_2 = history_3;
    history_3 = "";
  } // if

  foreach(indices(d[series][game]["p"]), ixa)
    winner = ixa;

  if (!winner || winner == "") {
    map = "<b>Sorry, map unavailable</b>";
  } else {
    map = d[series][game]["p"][winner]["full_map"];
    if (!map || map == "") {
      map = map_hist(winner, series, game);
    }
  }

  if (d[series][game]["bridier"] && d[series][game]["rank_table"]) {
    map += d[series][game]["rank_table"];
  }

  i_game_file = html_dir+"/history/"+series+"."+game+".html";
  i_game_file = replace(i_game_file,({" ", "(", ")", "\n"}),({"_", "_", "_","_"}));
  game_file = datapath+i_game_file;
  hist_header = "<html><head><title>SC "+servername+": "+series+":"+game+"</title></head>"+
                "<body bgcolor=#000000 background=\""+html_dir+"/starfield.gif\" "+
                "VLINK="+VLINK_COLOR+" LINK="+LNK_COLOR+" ALINK="+ALINK_COLOR+" TEXT="+d[series]["color"]+">\n"+
                "<table border=0 cols=2 width=\"100%\">\n"+
                "<tr><td valign=top><b><font size=+2 color="+heading_color+">"+series+":"+game+"</font></b>\n"+
                "<br><b><font size=2>Game completed: "+ ctime(time())+"</font></b></td>\n"+
                "<td><div align=right><img src=\""+html_dir+"/sclogo.gif\" alt=\"Stellar Crisis Logo\">"+
                "</div></td></tr></table>\n"+
                "<p><img src=\""+html_dir+"/spacerule.gif\" height=10  width=100%><p>";

  hist_footer = "<p><br clear=all><img src=\""+html_dir+"/spacerule.gif\" height=10 width=100%><p>\n"+
                "<center>"+
                "<b><a href="+help_url+">Help</a> is available</b><br>\n"+
                "Mail the server admin <a href=\"mailto:"+ad_mail+"\">"+ad_mail+"</a> if you have problems or suggestions<br></center>\n"+
                "</form></body></html>\n";

  history_3 += "<tr><td align=center>"+ctime(time())+"</td>"+
               "<td align=center><b><a href=\""+i_game_file+"\">"+series+" "+game+"</a></b></td>"+
               "<td align=center>"+result+"</td><td align=center>";

#if ROXEN_VER == "1.2"
  Stdio.write_file(game_file, hist_header+d[series][game]["history"]+"<p><center>"+map+"</center>"+hist_footer);
#else
  write_file(game_file, hist_header+d[series][game]["history"]+"<p><center>"+map+"</center>"+hist_footer);
#endif

  // BACKCOMPATABILITY -- this if can be thrown away once all of the games
  // have the nuked mapping
  if (mappingp(d[series][game]["nuked"])) {

    // now let's tell the losers about how the game ended
    int first = 1;
    foreach(indices(d[series][game]["nuked"]), ixa) {
      p[ixa]["missive"] += series + " " + game + " has ended.<br>";              // add info to player missive
      if (first != 1) {
        history_3 += ", ";
      }
      first = 0;
      history_3 += ixa;
    } // foreach
  } // if


  // cleanup the game stuff
  d[series]["count"]--;
  d[series]-=([game:""]);

} // add_history

// 3.2 sterner safety than sterilizeString()
string sterilizeName (string s, int type)
// type == 1 => ship name, others have type == 0
{
  int again, i, l = strlen(s);
  string c, valid = "0123456789_ -'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
  array invalid = ({"  ", " -", "- ", "--", " _", "_ ", "__", "_-", "-_", "''"});
  array quote = ({"' ", " '"});

// Single commas are allowed in planet and empire names, but not in ship names
  if (type == 0) {
    valid += ",";
    invalid += ({",,"});
  }

// Replace any single invalid character with a single space.
  for (i = 0; i < l; i++) {
    c = sprintf("%c", s[i]);
    if (search(valid, c) == -1) {
      s = replace(s, c, " ");
    }
  }
// While there is any further need to do so...
  again = 1;
  while (again) {
    again = 0;
// ...remove quotes (as opposed to apostrophes), and...
    foreach(indices(quote), i) {
      while (search(s, quote[i]) != -1) {
        s = replace(s, quote[i], " ");
        again = 1;
      }
    }
// ...replace any invalid pairs of characters with a single instance of the first of them.
    foreach(indices(invalid), i) {
      while (search(s, invalid[i]) != -1) {
        s = replace(s, invalid[i], sprintf("%c", invalid[i][0]));
        again = 1;
      }
    }
  }

// If the first character is a space or a quote, ditch it.
  if (strlen(s) && (s[0] == ' ' || s[0] == '\'')) {
    s = s[1..];
  }

// Truncate to a sensible length
  if (strlen(s) > 20) {
    s = s[..19];
  }

// If the last character is a space or a quote, ditch it.
  l = strlen(s);
  if (l && (s[l-1] == ' ' || s[l-1] == '\'')) {
    s = s[..l-2];
  }

// Ship names can be null, planet and empire names should not be
  if (type == 0 && strlen(s)==0) {
    return "Elvis";
  }

  return s;
}

// 2.9 we want to make it html safe
string sterilizeString (string data) {
  return replace(data,
                 ({"&", "<", ">", "\n"}),
                 ({"&amp;", "&lt", "&gt", "<br>"}));
} // sterilizeString

void debug(string data)
{
#ifdef SC_DEBUG
#if ROXEN_VER == "1.2"
  object debug_fp=Stdio.File();
#else
  object debug_fp=clone((program)"/precompiled/file");
#endif
  debug_fp->open(debug_file, "wac");
  debug_fp->write(data+" "+ctime(time()));
  debug_fp->close();
#endif
}

#ifdef SC_DEBUG
int fd_fail()
{
#if ROXEN_VER == "1.2"
  object fp=Stdio.File();
#else
  object fp=clone((program)"/precompiled/file");
#endif
  if(fp=open("/tmp/sc_fd_test", "rwc")) fp->close();
  if(!fp || fp->query_fd()>(MAX_FD-4)) return 1;
  return 0;
}
#endif

string nctime(int time)
{
  string t=ctime(time);
  return (t[0..(sizeof(t)-2)]);
}

string time_conv(int sec)
{
  string *next=({});
  int hrs = sec/3600;
  sec -= hrs*3600;
  int min = sec/60;
  sec -= min*60;
  if(hrs) next += ({ hrs+" hrs"});
  if(min) next += ({ min+" min"});
  if(sec) next += ({ sec+" sec"});
  if(!sizeof(next))
    next += ({sec+" sec"});
  return(next*", ");
}

/* ---------- Profile option functions ------------ */
string theme_dir(string name)
{
/* -----------------21/11/97 9:30--------------------
    Keep all graphics in the "/theme/std" directory
    Allowing for inclusion of additional themes
 --------------------------------------------------*/
    string thm_dir = html_dir+"/theme/std";

    if ( name == "" )
        return (thm_dir);
    if(name == ad_name)                 // no profile for admin user yet 29 Oct '97
        return (thm_dir);

//    if (p[name]["flags"] & flags_gbutton)     // using graphics
//        th_dir += "/theme/std";

    if ( p[name]["theme_url"] == "" )
        return (thm_dir);
    else
      return (p[name]["theme_url"]);

}

string header(string title, string name)
    //html page header and title block
{
  int backgrnd=1;                       // assume background on

  if(name != ad_name) {                 // no profile for admin user yet 29 Oct '97
    if ( name != "" )
      backgrnd = (p[name]["flags"] & flags_bgimage);
  }

  return ("<html><head><title>"+title+": "+"SC "+version+servername+"</title></head>"+
          "<body bgcolor=#000000 "+(backgrnd?" background=\""+theme_dir(name)+"/starfield.gif\" ":"")+
//          "<body bgcolor=#000000 background=\""+html_dir+"/starfield.gif\""+
          "VLINK="+VLINK_COLOR+" LINK="+LNK_COLOR+" ALINK="+ALINK_COLOR+" TEXT="+server_color+"><form method=post>\n");
}

string mini_header(string title, string name, string series)
{
  int backgrnd = (p[name]["flags"] & flags_bgimage);
  string SCOLOR=(string)d[series]["color"];
  if(search(SCOLOR, "#")!=0) {
   SCOLOR = server_color;
  }

  return ("<html><head><title>"+title+": "+"SC "+version+servername+"</title></head>"+
          "<body bgcolor=#000000 "+(backgrnd?" background=\""+theme_dir(name)+"/starfield.gif\" ":"")+
          "TEXT="+SCOLOR+" onBlur=\"self.close()\"><form method=post>\n");
}

/* ---------- Page title and Logo ----------------- */
string pageheader(string title, string name)
{
  return ("<table border=0 cols=2 width=\"100%\" >"+
            "<tr><td valign=top><b><font size=+3 color="+heading_color+">"+title+"</font></b>"+
            "<br><b><font size=2>Local time and date: "+ ctime(time())+"</font></b></td>"+
            "<td><div align=right><img src=\""+theme_dir(name)+"/sclogo.gif\" alt=\"Stellar Crisis Logo\"></div></td></tr></table>");
}

mapping fr_log_buttons(string name, string c_pass)
{
  int backgrnd=1;

  if(name != ad_name) {                 // no profile for admin user yet 29 Oct '97
    if ( name != "" )
      backgrnd = (p[name]["flags"] & flags_bgimage);
  }

    return (["data":"<html>"+
          "<body bgcolor=#000000 "+(backgrnd?" background=\""+theme_dir(name)+"/starfield.gif\" ":"")+
          "<form method=post>"+
          "<input type=hidden name=\"name\" value=\""+name+"\">"+
          "<input type=hidden name=\"c_pass\" value=\""+c_pass+"\">"+
          "<input type=hidden name=\"action\" value=\"g_list\">"+
          pageheader("Empire "+name, name)+
          "<p><a href=/ target=\"main\"></a><input type=submit name=\"l:game\" value=\"Game List\">"+
          "<input type=submit name=\"l:gamefull\" value=\"Detailed Game List\">"+
          "<input type=submit name=\"l:prof\" value=\"Edit Profile\">"+
          "<input type=submit name=\"l:stat\" value=\"Stat Viewer\">"+
          "<input type=submit name=\"l:hist\" value=\"History Viewer\">"+
          "<input type=submit name=\"l:exit\" value=\"Exit\">"
//          "<br><b>Local time and date: "+ ctime(time())+"</b>"+
          "<p><img src=\""+theme_dir(name)+"/spacerule.gif\" height=10  width=100%><p>"+
          "</form></body></html>", "type":"text/html"]);
}

mapping frheader(string name, string c_pass)
    // frame set header
{
  return (["data":"<html><head><title>SC "+version+servername+"</title></head>"+
          "<frameset cols=\"100%\" rows=\"175,*\" framespacing=0 frameborder=\"no\" border=0>"+
          "<frame name=\"head\" framespacing=0 frameborder=\"no\" scrolling=\"yes\" noresize border=0 marginwidth=0 marginheight=0"+
          " src="+mountpoint+"?frload=fr_logbtn?"+name+"?"+c_pass+"\">"+
          "<frame name=\"main\" framespacing=0 frameborder=\"no\" scrolling=\"auto\" noresize"+
          " src="+mountpoint+"?frload=fr_gamelst?"+name+"?"+c_pass+"\">"+
          "</frameset></html>",
          "type":"text/html"]);
}

string gheader(string title, string name, string c_pass, string series,
               string game, string message)
    // page header for game functions etc
{
  int delay;
  string gotoDoc = "";
  string update = "";
  string map_size = "";
  string end_btns;
  string jsmap = "";
  string missive=d[series][game]["p"][name]["missive"];
  int gbutn;
  string countmsg = "";
  string autoreload = "";
#if ROXEN_VER == "1.2"
  object data_fp=Stdio.File();
#else
  object data_fp=clone((program)"/precompiled/file");
#endif

  string SCOLOR=(string)d[series]["color"];
  if(search(SCOLOR, "#")!=0) {
   SCOLOR = server_color;
  }

  if (p[name]["flags"] & flags_webtv) {
    map_size+=" Map Size: <input type=text name=\"mapsize\" value="+d[series][game]["p"][name]["mapsize"]+
      " size=4 maxlength=4>";
  }
  gbutn = (p[name]["flags"] & flags_gbutton);
  end_btns = "<input name=\"p:exit\" type="+
                (gbutn?"image src=\""+theme_dir(name)+"/gexit.gif\" alt=Exit>"
                      :"submit value=\"Exit\">");

  d[series][game]["p"][name]["missive"]="";
  if(missive!="") {
    missive="<p><table><tr><td>"+missive+"</td></tr></table>";
  } // if

  if (d[series][game]["u_cnt"] == 0) {
    delay = d[series][game]["u_first"]-time();
    if (d[series][game]["full"] == 1) {
      end_btns = (gbutn?"<input name=\"p:quit\" type=image src=\""+theme_dir(name)+"/gquit.gif\" alt=Quit>"+
                        "<input name=\"p:exit\" type=image src=\""+theme_dir(name)+"/gexit.gif\" alt=Exit>"
                       : "<input type=submit name=\"p:quit\" value=\"Quit\">"+
                         "<input type=submit name=\"p:exit\" value=\"Exit\">");
      if(title != "Info") {
        update = "<p align=center>Next update when another player has joined.";
      } // if

    } else if(title != "Info") {
      update = "<p><center>Next update in <B>"+time_conv(delay)+"</B></center>";

    } // if
  } else {
    delay = d[series][game]["u_time"]+d[series][game]["u_last"]-time();
    if(title != "Info") {
      update = "<p><center>Next update in <B>"+time_conv(delay)+"</B></center>";
    } // if
  }


  if ((p[name]->flags & flags_autoupd)) {
    switch(title) {
    case "Info":
      gotoDoc = "'document.forms[0].elements[6].click()'";
      break;
    case "Tech":
      gotoDoc = "'document.forms[0].elements[7].click()'";
      break;
    case "Ships":
      gotoDoc = "'document.forms[0].elements[8].click()'";
      break;
    case "Fleets":
      gotoDoc = "'document.forms[0].elements[9].click()'";
      break;
    case "Build":
      gotoDoc = "'document.forms[0].elements[10].click()'";
      break;
    case "Systems":
      gotoDoc = "'document.forms[0].elements[11].click()'";
      break;
    case "Map":
      gotoDoc = "'document.forms[0].elements[12].click()'";
      break;
    case "Diplomacy":
      gotoDoc = "'document.forms[0].elements[13].click()'";
      break;
    default:
      gotoDoc = "'document.forms[0].elements[14].click()'";
    }
    if (gbutn) {
      gotoDoc = "'document.forms[0].submit()'";
    }
    gotoDoc = " onLoad=\"setTimeout("+gotoDoc+", "+(delay*1000)+")\"";
  }

   // Auto update and counter stuff
  if ((p[name]->flags & flags_usejavascript)) {
    //    perror("Javascript file : "+datapath+html_dir+"/counter.js"+"\n");
    autoreload=("<script><!--\nvar t = (new Date()).getTime()/1000+"+delay+";\n// --></script>\n");

    if (data_fp->open(datapath+html_dir+"/counter.js", "r")) {
      update = data_fp->read(100000000);
      data_fp->close();
      countmsg = sprintf( update);
      update = "";
      gotoDoc = "";
    } else {
      perror("...unable to open"+datapath+html_dir+"/counter.js\n");
    }
//    perror("end of javascript stuff!!\n");
//  } else {
//    countmsg = sprintf("<p align=left><font color=lightblue>"
//                 "<b>Stellar Crisis V3 - %s until"
//                 " update.</b></font>", time_conv(delay));
  }
/*    --------------------------------------------------*/
/*
  /* 2.9 debugging stuff
     string debugg= "";
     string index;
     foreach (indices (d[series][game]["p"][name]["dip"]), index) {
     debugg += "Dip status with " + index + " = " + (d[series][game]["p"][name]["dip"][index]) + "<br>";
     debugg += "Set Dip status with " + index + " = " + (d[series][game]["p"][name]["set_dip"][index]) + "<br>";
     } // foreach
     */

    int backgrnd=1;
    if ( name != "" )
      backgrnd = (p[name]["flags"] & flags_bgimage);

  if ((title == "Map" || title == "Systems") && p[name]["flags"] & flags_sensitivemap) {
    jsmap = "<script language=javascript>\n"+
      "<!-- Hide code\n"+
      "var url;\n"+
      "function winopen_member(url) {\n"+
      "msg_member=open(url, \"member_window\","+
        "\"toolbar=no,location=no,directories=no,status=no,menubar=no,"+
        "scrollbars=yes,resizable=yes,copyhistory=no,width=600,height=180\");\n"+
      "}\n"+
      "// End hide code -->\n"+
      "</script>\n";
  }
 
  return (
//          header(title+sprintf(": %s %s", series, game), name)+
//          sprintf(": %s %s", series, game)
          "<html><head><title>"+title+sprintf(": %s %s", series, game)+": "+"SC "+version+servername+"</title>\n"+
          jsmap+"\n"+
          "</head>"+
          "<body bgcolor=#000000 "+(backgrnd?" background=\""+theme_dir(name)+"/starfield.gif\" ":"")+
          "VLINK="+VLINK_COLOR+" LINK="+SCOLOR+" ALINK="+ALINK_COLOR+" TEXT="+SCOLOR+
//          " onLoad=\"setTimeout("+gotoDoc+", "+(delay*1000)+")\"><form name=playopt method=post>\n"+
          gotoDoc+">"+autoreload+countmsg+"<form name=playopts method=post>\n"+
          "<input type=hidden name=\"name\" value=\""+name+"\">"+
          "<input type=hidden name=\"c_pass\" value=\""+c_pass+"\">"+
          "<input type=hidden name=\"series\" value=\""+series+"\">"+
          "<input type=hidden name=\"game\" value=\""+game+"\">"+
          "<input type=hidden name=\"action\" value=\"play\">"+
          "<input type=hidden name=\"play\" value=\""+title+"\">"+"\n"+
//          "<center><h1>"+name+" "+title+
//          sprintf(": %s %s", series, game)+"</h1>"+update+
//          pageheader( name+" "+title+sprintf(": %s %s", series, game)+update, name)+
          "<table border=0 cols=3 width=\"100%\" >"+
          "<tr><td valign=top><b><font size=+2 color="+heading_color+">"+title+sprintf(" : %s %s for %s", series, game, name)+"</font></b></td>"+
          "<td align=top>"+update+"</td>"+
          "<td><div align=right><img src=\""+theme_dir(name)+"/sclogo.gif\"></div></td></tr></table>"+

          (gbutn?"<input name=\"p:info\" type=image src=\""+theme_dir(name)+"/info.gif\" alt=Info>"+
                 "<input name=\"p:tech\" type=image src=\""+theme_dir(name)+"/tech.gif\" alt=Tech>"+
                 "<input name=\"p:ships\" type=image src=\""+theme_dir(name)+"/ships.gif\" alt=Ships>"+
                 "<input name=\"p:fleets\" type=image src=\""+theme_dir(name)+"/fleets.gif\" alt=Fleets>"+
                 "<input name=\"p:build\" type=image src=\""+theme_dir(name)+"/build.gif\" alt=Build>"+
                 ((d[series]["blind"] && d[series][game]["full"]==1)?"":
                 "<input name=\"p:systems\" type=image src=\""+theme_dir(name)+"/systems.gif\" alt=Systems>"+
                 "<input name=\"p:map\" type=image src=\""+theme_dir(name)+"/map.gif\" alt=Map>")+
                 "<input name=\"p:diplomacy\" type=image src=\""+theme_dir(name)+"/diplom.gif\" alt=Diplomacy>"+
                 (d[series][game]["p"][name]["end"]?
                 "<input name=\"p:unend\" type=image src=\""+theme_dir(name)+"/unendturn.gif\" alt=\"Unend Turn\">":
                 "<input name=\"p:end\" type=image src=\""+theme_dir(name)+"/endturn.gif\" alt=\"End Turn\">")
               : "<font color=\"#000000\"><input type=submit name=\"p:info\" value=\"Info\">"+
                 "<input type=submit name=\"p:tech\" value=\"Tech\">"+
                 "<input type=submit name=\"p:ships\" value=\"Ships\">"+
                 "<input type=submit name=\"p:fleets\" value=\"Fleets\">"+
                 "<input type=submit name=\"p:build\" value=\"Build\">"+
                 ((d[series]["blind"] && d[series][game]["full"]==1)?"":
                 "<input type=submit name=\"p:systems\" value=\"Systems\">"+
                 "<input type=submit name=\"p:map\" value=\"Map\">")+
                 "<input type=submit name=\"p:diplomacy\" value=\"Diplomacy\">"+
                 (d[series][game]["p"][name]["end"]?
                 "<input type=submit name=\"p:unend\" value=\"Unend Turn\">":
                 "<input type=submit name=\"p:end\" value=\"End Turn\">"))+
          end_btns+"</font>"+map_size+"<p><b>"+message+"</b><font color="+MISS_COLOR+">"+missive+"</font></center>"+
          "<p><img src=\""+theme_dir(name)+"/spacerule.gif\" height=10 width=100%>\n<p>");

} // gheader()

string footer(string name)
{
  int delay;
  string countmsg = "";
  string autoreload = "";
#if ROXEN_VER == "1.2"
  object data_fp=Stdio.File();
#else
  object data_fp=clone((program)"/precompiled/file");
#endif

/* -----------------28/04/99 13:22-------------------
need to add series & game to passed variables to use counter here

  if (d[series][game]["u_cnt"] == 0) {
    delay = d[series][game]["u_first"]-time();
  } else {
    delay = d[series][game]["u_time"]+d[series][game]["u_last"]-time();
  }

  // Auto update and counter stuff
  if ((p[name]->flags & flags_usejavascript)) {
    autoreload=("<script><!--\nvar t = (new Date()).getTime()/1000+"+delay+";\n// --></script>\n");

    if (data_fp->open(datapath+html_dir+"/counter.js", "r")) {
      update = data_fp->read(100000000);
      data_fp->close();
      countmsg = sprintf( update);
      update = "";
      gotoDoc = "";
    }
  } else {
    countmsg = sprintf("<p align=center><font color=lightblue>"
                 "<b>Stellar Crisis V3 - %s until"
                 " update.</b></font>", time_conv(delay));
  }
 --------------------------------------------------*/
  return
    ("<p><br clear=all><img src=\""+theme_dir(name)+"/spacerule.gif\" height=10 width=100%><p>"+
     "<center>"+
     "<b><a target=_blank href="+help_url+">Help</a> is available</b><br>"+
     "Mail the server admin <a href=\"mailto:"+ad_mail+"\">"+ad_mail+"</a> if you have problems or suggestions<br></center>"+
//     ((p[name]->flags & flags_usejavascript)?"<input name=counter size=32>":"")+
     "</form>"+autoreload+countmsg+"</body></html>");
}


mapping no_op(string a) {
  return
    (["data":
     header("No Op","")+
//     "<center><h1>Action Defined but Unimplemented</h1></center>"+
     pageheader("Action defined but not implemented..\nDebug Output:<p>"+a+"<p>","")+
     footer(""),
     "type":"text/html"]);
}

int id_check(string name, string c_pass)
{
  if(!mappingp(p[name])) return 0;
  return(crypt((string)p[name]["pass"], c_pass));
}

int admin_id_check(string name, string c_pass, object id)
{
  int valid_admin;

  int match=crypt(ad_pass, c_pass);
  valid_admin = (match | (name==ad_name));
#if ROXEN_VER == "1.2"
  if ( !valid_admin )
  {
    report_notice( "SC "+version+": Failed administrative login from "
     + roxen->blocking_ip_to_host(id->remoteaddr)+"." );
  }
#endif
  return valid_admin;
}

/* -----------------16/10/97 10:47-------------------
** Game play options
** --------------------------------------------------*/

string disp_g_missive(string name)
{
  string missive=p[name]["missive"];
  if(missive != "") missive += "<br>";
  missive += sprintf("%s",(string)p[name]["bcast"]);
  p[name]["bcast"] = "";
  p[name]["missive"]="";
  if(missive!="")
    return("<center><table><tr><td>"+missive+"</td></tr></table></center>");
  return("");
}

string dip_word(int status)
{
  // 2.9 added 0-2 and moved the others up appropriately
  switch(status)
    {
    case 0:
      return "Surrender";
    case 1:
      return "Draw";
    case 2:
      return "War";
    case 3:
      return "Truce";
    case 4:
      return "Trade";
    case 5:
      return "Alliance";
    default:
      return "Unknown";
    }
}

string dip_offer(int status, int new, int blood, int banished, int surrdraw)
{
  // 2.9C new is what we are currently offering
  // status is what we are currently at
  string offer="";

  // 2.9 can only war, changed 'magic numbers'
  // i'm guessing this is needed so people don't cheat
  if((banished) && (status>2)) {
    status = 2;
  } // if

  // 2.9 Make the surrender option available
  if (status==2 && surrdraw) {
    offer+="<option ";
    if(new==0)
      offer+="selected ";
    offer+="value=\""+0+"\">"+dip_word(0);
  } // if

  // 2.9 Offer one worse
  if(status>=1 && (status!=2 || surrdraw)) {
    offer+="<option ";
    if(new==(status-1))
      offer+="selected ";
    offer+="value=\""+(status-1)+"\">"+dip_word(status-1);
  }


  offer+="<option ";
  if(new==status)
    offer+="selected ";
  offer+="value=\""+status+"\">"+dip_word(status);

  // 2.9 If we should then offer a more peaceful status
  if (banished) return offer;

  if(((blood==0) && status<5) ||
     ((blood==2) && status<4) ||
     ((blood==1) && status<2)) {
    offer+="<option ";
    if(new>=(status+1))
      offer+="selected ";
    offer+="value=\""+(status+1)+"\">"+dip_word(status+1);
  }
  return offer;
}

int get_econ_pow(string series, string game, string name)
{
  return((d[series][game]["p"][name]["min"]+
          d[series][game]["p"][name]["fuel"]+
          d[series][game]["p"][name]["ag"])/100);
}

int get_mil_pow(string series, string game, string name)
{
  string ixa;
  int tot;
  foreach(indices(d[series][game]["f"]), ixa) {

    if(d[series][game]["f"][ixa]["owner"]!=name) {
      continue;
    } // if
    if ((d[series][game]["f"][ixa]["orders"]=="build") &&
        (d[series]["vis"] == 0)) {
      continue;
    } // if

    // 2.9 fix replaced Build with build so now you can't see
    // how much someone is building
    // 3.0 changed it so it depends on the 'vis' series setting
    tot+=(int) pow(d[series][game]["f"][ixa]["br"], 2.0);
  }
  return(tot/50);
}

string *get_valid_orders(string series, string game, string fleet,
                         string *expl, void|int|string fleeter)
{
  string type=d[series][game]["f"][fleet]["type"];
  string loc=d[series][game]["f"][fleet]["loc"];
  string owner=d[series][game]["f"][fleet]["owner"];
  int cloak = d[series][game]["f"][fleet]["cloak"];
  string ixa;
  string name;
//  string name=id["variables"]["name"]; ?? or is it name of fleet etc...
  string *jumps;

  string *orders=({});

  if(fleeter)
    {
      array order = d[series][game]["f"][fleet]["orders"]/":";
      if(order[0] == "fleet" &&
         (intp(fleeter) || (stringp(fleeter) && order[1] != fleeter)))
        return 0;
      else if(stringp(fleeter) && order[0] != "fleet")
        return 0;
    }

  if(search(move_ships, type)!=-1) {
    foreach(indices(d[series][game]["t"]), ixa) {
      if(d[series][game]["t"][ixa]["loc"]!=loc ||
         d[series][game]["t"][ixa]["owner"] !=
         d[series][game]["f"][fleet]["owner"])
        continue;

      orders+=({"fleet:"+ixa});
    }
  }

  orders += ({"standby"});

  if(d[series][game]["f"][fleet]["orders"]=="build")
    return ({"build", "cancel"});


  if(search(move_ships, type)!=-1) {
    jumps=d[series][game]["s"][loc]["jump"]/" " - ({"", " "});

    if(type=="Science") {
      orders+=jumps;
    } else {
      foreach(jumps, ixa) {
        if(search(expl, ixa)!=-1) {
          orders+=({ixa});
        } // if
      } // foreach
    } // if science

    if(!d[series][game]["s"][loc]->annihilated) {
      if((d[series][game]["s"][loc]["owner"]!=owner) &&
         ((cloak==0) || (cloak==2))) {
        // 2.9 change magic numbers from 0 to < 3
        if((d[series][game]["p"][owner]["dip"][d[series][game]["s"][loc]["owner"]] < 3) &&
           (d[series][game]["p"][owner]["set_dip"][d[series][game]["s"][loc]["owner"]]) < 3) {

          if(d[series][game]["s"][loc]["pop"]>0) {
              orders+=({"nuke"});
              if(type=="Troopship") {
                orders+=({"invade"});
              } // if troopship
          } // pop > 0

          if(type=="Doomsday") {
            orders+=({"destroy"});
          } // if doomsday

        } // if we are at war with the owner

      } // if we aren't the owner and we aren't cloaked

      if((d[series][game]["s"][loc]["pop"]==0) &&
         (type=="Colony")) {
        orders+=({"col"});
      } // if colony and the pop == 0

      if((d[series][game]["s"][loc]["owner"]==owner) &&
         (d[series][game]["s"][loc]["ag"]<d[series][game]["s"][loc]["min"] ||
          d[series][game]["s"][loc]["ag"]<d[series][game]["s"][loc]["fuel"]) &&
         (type=="Terraformer")) {
        orders+=({"form"});
      } // if terraformer....

    } // if the location isn't annihilated

    if(type=="Cloaker") {
      if((cloak==0) || (cloak==2)) {
        orders+=({"cloak"});
      } else {
        orders+=({"uncloak"});
      } // if we are cloaked
    } // if cloaker

    if(type=="Engineer") {
      if (d[series][game]["f"][fleet]["br"] >= d[series]["eng_loss"]) {
        foreach(jumps, ixa) {
          orders+=({"close:"+ixa});
        } // foreach of the open links
        array x=(loc/",");
        string *j_list=({((int) x[0]+1)+","+x[1],
                         x[0]+","+((int) x[1]+1),
                         ((int) x[0]-1)+","+x[1],
                         x[0]+","+((int) x[1]-1)})-jumps;
                         foreach(j_list, ixa) {
                           if(search(indices(d[series][game]["s"]), ixa)!=-1) {
                             orders+=({"open:"+ixa});
                           } // if there is a system there
                         } // the link doesn't exist
      } // if have the br
    } // if engineer

    if(type=="Builder") {
      // 3.0 order create:
      array x=(loc/",");
      string *j_list=({((int) x[0]+1)+","+x[1],
                       x[0]+","+((int) x[1]+1),
                       ((int) x[0]-1)+","+x[1],
                       x[0]+","+((int) x[1]-1)})-jumps;
                       foreach(j_list, ixa) {
                         if(search(indices(d[series][game]["s"]), ixa)==-1) {
                           orders+=({"create:"+ixa});
                         } // if there is a system there
                       } // the link doesn't exist
    } // if


  } // if we are a movable ship

  if (type=="Stargate") {
    foreach(indices(d[series][game]["s"]), ixa) {
      if(d[series][game]["s"][ixa]["owner"]==owner && ixa!=loc) {
        // 3.0 if it is in range or we aren't using ranges
        if (d[series]["stargate"] > 0) {
          int r = d[series][game]["f"][fleet]["br"]/d[series]["stargate"];
          array x=(loc/",");
          array dest=(ixa/",");
          if ((((int)x[0])-((int)dest[0]) > r) || (((int)dest[0])-((int)x[0])>r) ||
              (((int)x[1])-((int)dest[1]) > r) || (((int)dest[1])-((int)x[1])>r)) {
            continue;
          } // if
        } // if
        orders+=({"send:"+ixa});
      } // if it is somewhere else and we own it
    } // foreach system in the game
  } // if stargate

  if(type=="Jumpgate") {
    if (d[series][game]["f"][fleet]["br"] >= d[series]["jump_loss"]) {
      // 3.0 order jumpto:
      foreach(indices(d[series][game]["s"]), ixa) {
        if((search(expl, ixa)!=-1) && (!d[series][game]["s"][ixa]->annihilated)) {
          // 3.0 if it is in range or we aren't using ranges
          if ( d[series]["jumpgate"] > 0) {
            int r = d[series][game]["f"][fleet]["br"] / d[series]["jumpgate"];
            array x=(loc/",");
            array dest=(ixa/",");
            if (( ((int)x[0])-(((int)dest[0])) > r) || ( (((int)dest[0])-((int)x[0]))>r) ||
                (( ((int)x[1])-((int)dest[1])) > r) || (( ((int)dest[1])-((int)x[1]))>r)) {
              continue;
            } // if
          } // if
          orders+=({"jumpto:"+ixa});
        } // if it is somewhere else and we haven't explored it and it isn't annihilated
      } // foreach system in the game
    } // if
  } // if

  if ( cloak==2 ) {
  string *devs=d[series][game]["p"][owner]["devs"]/" ";
    if (d[series][game]["f"][fleet]["br"] >= d[series]["morph_loss"]) {
      foreach(devs, ixa) {
        if (type!=ixa) {
          orders+=({"morph:"+ixa});
        } // if
      } // foreach tech
    } // if
  } // if

// If this code is loaded over a database inherited from a version of the code
// prior to v3.01.8, then this little fix is needed to let the poor invisible
// virgin morphers out of the grey limbo in which they would otherwise be 
// forever trapped. Under this code, a ship with 'cloak==3' is actually a 
// morpher, but must have 'type=="Cloaker"'...

  if ( type=="Morpher" && cloak==3 ) {
    orders +=({"uncloak"});
  }

  // 2.9C this is from when stargates only sent to other stargates
  //if(ixa!=fleet &&
  //d[series][game]["f"][ixa]["type"]=="Stargate" &&
  //d[series][game]["f"][ixa]["owner"]==d[series][game]["f"][fleet]["owner"])
  //orders+=({"send:"+d[series][game]["f"][ixa]["loc"]});


  orders+=({"dismantle"});

  return orders;
}

string *get_fleet_orders(string series, string game,
                         string fleet, string *expl)
{
  string loc=d[series][game]["t"][fleet]["loc"];
  string ixa;
  string *orders=({});

  if(mappingp(d[series][game]["s"][loc])) {
    orders=({"standby"});
    string *jumps=d[series][game]["s"][loc]["jump"]/" ";
    foreach(jumps, ixa)
      if(search(expl, ixa)!=-1) orders+=({ixa});
  }

  orders+=({"disband", "disbandall"});

  return orders;
}

void recalc_ratios(string series, string game, string name)
{
  string ixa;
  int trade;
  float pt;

  d[series][game]["p"][name]["build"]=0;
  d[series][game]["p"][name]["maint"]=0;
  d[series][game]["p"][name]["f_use"]=0;

  d[series][game]["p"][name]["min"]=0;
  d[series][game]["p"][name]["fuel"]=0;
  d[series][game]["p"][name]["ag"]=0;
  d[series][game]["p"][name]["pop"]=0;
  d[series][game]["p"][name]["max_pop"]=0;

  // 2.9C go through and add up all of the ship costs
  // Ideally change this so that the build cost is kept on a per game basis
  // rather than a per ship basis
  foreach(indices(d[series][game]["f"]), ixa) {
    if(d[series][game]["f"][ixa]["owner"]!=name) continue;
    if(d[series][game]["f"][ixa]["orders"]=="build")
      d[series][game]["p"][name]["build"]+=d[series][game]["f"][ixa]["build"];
    else {
      d[series][game]["p"][name]["maint"]+=d[series][game]["f"][ixa]["maint"];
      d[series][game]["p"][name]["f_use"]+=d[series][game]["f"][ixa]["fuel"];
    }
  }

  foreach(indices(d[series][game]["s"]), ixa) {
    if(d[series][game]["s"][ixa]["owner"]!=name) continue;

    if(d[series][game]["s"][ixa]["pop"]>d[series][game]["s"][ixa]["min"])
      d[series][game]["p"][name]["min"]+=d[series][game]["s"][ixa]["min"];
    else d[series][game]["p"][name]["min"]+=d[series][game]["s"][ixa]["pop"];

    if(d[series][game]["s"][ixa]["pop"]>d[series][game]["s"][ixa]["fuel"])
      d[series][game]["p"][name]["fuel"]+=d[series][game]["s"][ixa]["fuel"];
    else d[series][game]["p"][name]["fuel"]+=d[series][game]["s"][ixa]["pop"];

    d[series][game]["p"][name]["ag"]+=d[series][game]["s"][ixa]["ag"];
    d[series][game]["p"][name]["pop"]+=d[series][game]["s"][ixa]["pop"];
    d[series][game]["p"][name]["max_pop"]+=d[series][game]["s"][ixa]["max_pop"];
  }

  foreach(indices(d[series][game]["p"][name]["dip"]), ixa)
    if(d[series][game]["p"][name]["dip"][ixa]>3) trade++;

  pt=1.0+((float) trade/10.0);
  d[series][game]["p"][name]["min"]=(int) ((float) d[series][game]["p"][name]["min"]*pt);
  d[series][game]["p"][name]["fuel"]=(int) ((float) d[series][game]["p"][name]["fuel"]*pt);
  d[series][game]["p"][name]["ag"]=(int) ((float) d[series][game]["p"][name]["ag"]*pt);

  d[series][game]["p"][name]["e_pow"]=get_econ_pow(series, game, name);
  d[series][game]["p"][name]["m_pow"]=get_mil_pow(series, game, name);

  // debug stuff for index error
  //perror(sprintf("Variables  Series,Game,Name: %s %s %s\n", series,game,name));
  //perror(sprintf("Series e_pow: %s\n", (string) d[series][game]["p"][name]["e_pow"]));
  //perror(sprintf("Player max_e_pow: %s\n",(string) p[name]["max_e_pow"]));


  if(d[series][game]["p"][name]["e_pow"]>p[name]["max_e_pow"])
    p[name]["max_e_pow"]=d[series][game]["p"][name]["e_pow"];
  if(d[series][game]["p"][name]["m_pow"]>p[name]["max_m_pow"])
    p[name]["max_m_pow"]=d[series][game]["p"][name]["m_pow"];

  d[series][game]["p"][name]["min_r"]=
    (((float) d[series][game]["p"][name]["min"]+0.1)/
     ((float) d[series][game]["p"][name]["build"]+
      (float) d[series][game]["p"][name]["maint"]+0.1));
  d[series][game]["p"][name]["fuel_r"]=
    (((float) d[series][game]["p"][name]["fuel"]+0.1)/
     ((float) d[series][game]["p"][name]["f_use"]+0.1));
  d[series][game]["p"][name]["ag_r"]=
    (((float) d[series][game]["p"][name]["ag"]+0.1)/
     ((float) d[series][game]["p"][name]["pop"]+0.1));

  // 2.9 add in ag ratio limit
  if (d[series][game]["p"][name]["ag_r"] > d[series]["max_ag"])
  	d[series][game]["p"][name]["ag_r"]=d[series]["max_ag"];

  d[series][game]["p"][name]["tech_d"]=
    (((float) d[series][game]["p"][name]["min"]+
      (float) d[series][game]["p"][name]["fuel"]-
      (float) d[series][game]["p"][name]["build"]-
      (float) d[series][game]["p"][name]["maint"]-
      (float) d[series][game]["p"][name]["f_use"]+0.1)/
     ((float) d[series][game]["p"][name]["min"]+
      (float) d[series][game]["p"][name]["fuel"]+0.1)*
     d[series]["t_mult"]);
}

void spawn_game(string series)
{
  if(d[series]["count"] >= d[series]["max"]) {
    return;
  } // if

  string ogame=(string) d[series]["g_cnt"];
  string game=(string) ++d[series]["g_cnt"];

//diff  d[series][ogame]["spawn"]=1;

  d[series][game]=([]);
//diff  d[series][game]["spawn"]=0;
  d[series][game]["full"]=0;
  d[series][game]["u_first"]=time()+d[series]["u_delay"]+d[series]["u_time"];
  d[series][game]["u_last"]=time();
  d[series][game]["u_time"]=d[series]["u_time"];
  d[series][game]["u_cnt"]=0;
  d[series][game]["f_cnt"]=0;
  d[series][game]["t_cnt"]=0;

// ##@ 23/2/99

  if( d[series]["s_min"] )
    d[series][game]["s_add"]=d[series]["s_min"] + random(d[series]["s_max"]-d[series]["s_min"]+1);
  else
    d[series][game]["s_add"]=d[series]["s_add"];
//  d[series][game]["avg_rsrc"]=d[series]["min_rsrc"] + random(d[series]["max_rsrc"]-d[series]["min_rsrc"]+1);
// ##@ 23/2/99

  // set the (possibly random) average for each of the resources in this game
  d[series][game]["avg_ag"] = d[series]["avg_ag"] + random (d[series]["range_ag"]+1);
  d[series][game]["avg_min"] = d[series]["avg_min"] + random (d[series]["range_min"]+1);
  d[series][game]["avg_fuel"] = d[series]["avg_fuel"] + random (d[series]["range_fuel"]+1);

  if (enable_bridier) {
    d[series][game]["bridier"] = d[series]["bridier"];
  }

  d[series][game]["p"]=([]);
  d[series][game]["f"]=([]);
  d[series][game]["t"]=([]);
  d[series][game]["s"]=([]);
  d[series][game]["nuked"]=([]);

  d[series][game]["num_planets"] = 0;

  // sc3 history stuff
  d[series][game]["history"] = "<hr><table width=80%><!--series--><tr>"+
                                 "<td colspan=2><font color=\""+server_color+"\" size=+1>"+series+" "+game+"</font></td></tr>"+
                               "<tr><td><font color=\""+server_color+"\">Update</font></td>"+
                                 "<td width=75%><font color=\""+server_color+"\">Events</font></td></tr>";

  d[series]["count"]++;
}

void disestablish (string player)
{
  int absence = (int) ((time() - p[player]["bridier_time"]) / 2629800);
  int ceil = 100 * (absence - 1);

  if (absence > 2)
    p[player]["bridier_index"] = ((ceil > p[player]["bridier_index"])?ceil:p[player]["bridier_index"]);
  if (p[player]["bridier_index"] > 500)
    p[player]["bridier_index"] = 500;
}

void prep_top_ten ();

mapping calc_bridier (int wrank, int wind, int lrank, int lind)
{
  mapping newbrid;
  int stake = 10;
  int drank = wrank - lrank;

  if (drank > 0) stake--;
  if (drank > 10) stake--;
  if (drank > 20) stake--;
  if (drank > 30) stake--;
  if (drank > 40) stake--;
  if (drank > 60) stake--;
  if (drank > 80) stake--;
  if (drank > 100) stake--;
  if (drank > 140) stake--;
  if (drank > 200) stake--;

  float wi = (float) wind / 100.0;
  float li = (float) lind / 100.0;
  int wadj = (int) (stake * (1 + (19 * wi) - li - (3 * wi * li)) / 16);
  int ladj = (int) (stake * (1 + (19 * li) - wi - (3 * li * wi)) / 16);

  newbrid = ([]);
  newbrid ["wrank"] = wrank + wadj;
  newbrid ["lrank"] = lrank - ladj;
  if (newbrid ["lrank"] < 100)
    newbrid ["lrank"] = 100;

  newbrid ["wind"] = wind - wadj;
  if (newbrid ["wind"] < 100)
    newbrid ["wind"] = 100;

  newbrid ["lind"] = lind - ladj;
  if (newbrid ["lind"] < 100)
    newbrid ["lind"] = 100;

  return newbrid;
}

void apply_bridier (string series, string game, string winner, string loser)
{
  int now = time();
  int absence;
  int wrank, wind, lrank, lind;
  mapping newbrid;

  if (!p[winner]["bridier_index"]) {
    p[winner]["bridier_rank"]=500;
  }
  if (!p[loser]["bridier_index"]) {
    p[loser]["bridier_rank"]=500;
  }

  disestablish(winner);
  disestablish(loser);

  if (d[series][game]["p"][winner]["bridier_rank"])
    wrank = d[series][game]["p"][winner]["bridier_rank"];
  else
	wrank = p[winner]["bridier_rank"];

  if (d[series][game]["p"][winner]["bridier_index"])
    wind = d[series][game]["p"][winner]["bridier_index"];
  else
	wind = p[winner]["bridier_index"];

  if (d[series][game]["p"][loser]["bridier_rank"])
    lrank = d[series][game]["p"][loser]["bridier_rank"];
  else
	lrank = p[loser]["bridier_rank"];

  if (d[series][game]["p"][loser]["bridier_index"])
    lind = d[series][game]["p"][loser]["bridier_index"];
  else
	lind = p[loser]["bridier_index"];

  newbrid = calc_bridier (wrank, wind, lrank, lind);

  d[series][game]["rank_table"] = "<p><table cellpadding=3><tr><th>Bridier Awards</th><th colspan=2 align=center>"+
    winner+"</th><th colspan=2 align=center>"+loser+"</th></tr><tr><th></th><td align=center>Rank</td>"+
    "<td align=center>Index</td><td align=center>Rank</td><td align=center>Index</td></tr><tr>"+
    "<td>Basis:</td><td align=center>"+wrank+"</td><td align=center>"+wind+"</td>"+
    "<td align=center>"+lrank+"</td><td align=center>"+lind+"</td></tr>"+
    "<td>Awards:</td><td align=center>+"+(newbrid ["wrank"] - wrank)+"</td><td align=center>-"+(wind - newbrid ["wind"])+"</td>"+
    "<td align=center>-"+(lrank - newbrid ["lrank"])+"</td><td align=center>-"+(lind - newbrid ["lind"])+"</td></tr>"+
    "<td>Before:</td><td align=center>"+p[winner]["bridier_rank"]+"</td><td align=center>"+p[winner]["bridier_index"]+"</td>"+
    "<td align=center>"+p[loser]["bridier_rank"]+"</td><td align=center>"+p[loser]["bridier_index"]+"</td></tr>";

  p[winner]["bridier_rank"] += newbrid ["wrank"] - wrank;
  p[winner]["bridier_index"] -= wind - newbrid ["wind"];
  p[loser]["bridier_rank"] -= lrank - newbrid ["lrank"];
  p[loser]["bridier_index"] -= lind - newbrid ["lind"];

  if (p[winner]["bridier_index"] < 100) {
    p[winner]["bridier_index"] = 100;
  }
  if (p[loser]["bridier_rank"] < 100) {
    p[loser]["bridier_rank"] = 100;
  }
  if (p[loser]["bridier_index"] < 100) {
    p[loser]["bridier_index"] = 100;
  }

  d[series][game]["rank_table"] += "<tr><td>After:</td>"+
    "<td align=center>"+p[winner]["bridier_rank"]+"</td><td align=center>"+p[winner]["bridier_index"]+"</td>"+
    "<td align=center>"+p[loser]["bridier_rank"]+"</td><td align=center>"+p[loser]["bridier_index"]+"</td></tr></table>";

  p[winner]["bridier_time"] = now;
  p[loser]["bridier_time"] = now;

  prep_top_ten ();
}

string ship_string(array ships)
{
  string ixa, test, result="";
  int named=0;
  int unnamed=0;
  int first=1;

  foreach(ships, ixa) {
    if (ixa=="") {
      unnamed++;
    } else {
      named++;
    }
  }
  if (named==0) {
    if (unnamed==0) {
      return ""; // Shouldn't happen!
    }
    result=unnamed+" ship"+(unnamed>1?"s":"");
  } else if (unnamed) {
    foreach(ships, ixa) {
      if (ixa=="") {
        continue;
      }
      if (first) {
        first=0;
      } else {
        result+=", ";
      }
      test=replace(ixa, ({",", " and "}), ({"|", "|"}));
      if (test==ixa) {
        result+=ixa;
      } else {
        result+="\""+ixa+"\"";
      }
    }
    result+=" and "+unnamed+" other ship"+(unnamed>1?"s":"");
  } else {
    foreach(ships, ixa) {
      if (first) {
        first=0;
      } else {
        if (named<2) {
          result+=" and ";
        } else {
          result+=", ";
        }
      }
      test=replace(ixa, ({",", " and "}), ({"|", "|"}));
      if (test==ixa) {
        result+=ixa;
      } else {
        result+="\""+ixa+"\"";
      }
      named--;
    }
  }
  return result;
}

int update_game(string series, string game)
{
  string ixa, ixb, ixc, loc, other, orders;
  int noreg;
  string *expl, *a;
  mapping sf=([]);
  mapping nuke=([]);
  mapping col=([]);
  mapping destroy=([]);
  mapping form=([]);
  mapping invade=([]);
  mapping send=([]);
  mapping open=([]);
  mapping close=([]);
  // 3.0 additions
  mapping buildplan=([]);
  mapping jto=([]);

  mapping missive=([]);

#if ROXEN_VER == "1.2"
  object cheat_fp = Stdio.File();
#else
  object cheat_fp = clone((program)"/precompiled/file");
#endif

  cheat_fp->open(dump_file+".cheat", "wac");

  foreach(indices(d[series][game]["p"]), ixa) {
    d[series][game]["p"][ixa]["full_map"]=map_hist(ixa, series, game);
    foreach(indices(d[series][game]["p"]), ixb) {
      if (ixa<ixb && p[ixa]["last_ip"]==p[ixb]["last_ip"]) {
        cheat_fp->write(series+"|"+game+"|"+(string)(d[series][game]["u_cnt"]+1)+"|"+ixa+"|"+ixb+"|"+p[ixa]["last_ip"]+"|"+(string)time()+"\n");
      }
    }
  }

  cheat_fp->close();

  d[series][game]["u_cnt"]++;
  d[series][game]["u_last"]=time();

  d[series][game]["history"] +=
//      "<tr><td><!--UPDATE-->"+d[series][game]["u_cnt"]+"</td><td><table><tr>Update occurred at "+nctime(time())+"</tr>";
      "<tr><td><!--UPDATE-->"+d[series][game]["u_cnt"]+"</td><td>Update occurred at "+nctime(time())+"<br>";

  foreach(indices(d[series][game]["p"]), ixa) {
    int idle=d[series][game]["u_cnt"]-d[series][game]["p"][ixa]["l_up"];
    if (idle>2) {
      d[series][game]["p"][ixa]["end"]=1;
      d[series][game]["history"] += "Empire "+ixa+(idle>3?
        (idle>7?(idle>10?" is living on borrowed time.<br>":" is <b>still</b> idle.<br>"):
        " is still idle.<br>"):" has grown decadent and idle.<br>");
    } else {
      d[series][game]["p"][ixa]["end"]=0;
    } // if
    d[series][game]["p"][ixa]["missive"]+=
      "<!--UPDATE-->Update "+d[series][game]["u_cnt"]+" occurred at "+nctime(time())+"<br>";

    // update the player's tech level
    d[series][game]["p"][ixa]["tech"]+=d[series][game]["p"][ixa]["tech_d"];

    if(d[series][game]["p"][ixa]["tech"]>0.0) {
      if((int) sqrt(d[series][game]["p"][ixa]["tech"])>
         d[series][game]["p"][ixa]["devel"]) {
        d[series][game]["p"][ixa]["missive"]+=
          ("<!--U:TECH-->You have "+((int) sqrt(d[series][game]["p"][ixa]["tech"])-
                        d[series][game]["p"][ixa]["devel"])+
           " new tech development(s) waiting.<br>");
      } // if there are pending techs
    } // if tech is greater than 0, i guess it croaks on imaginary numbers

    int ally_count = 0;
//removed 7/12/97
//    foreach(indices(d[series][game]["p"][ixa]["dip"]), ixb) {
//      if (d[series][game]["p"][ixa]["dip"][ixb] == 5)
//        ally_count++;
//    }

    // output the status with everyone else and update it too
// There must be an easier way of doing this. How about multiplying the set_dips together?
    foreach(indices(d[series][game]["p"][ixa]["dip"]), ixb) {
      if(mappingp(d[series][game]["p"][ixb])) {
		switch (d[series][game]["p"][ixa]["set_dip"][ixb]) {
        case 1: // draw
          if (d[series][game]["p"][ixb]["set_dip"][ixa] == 1) {
            d[series][game]["p"][ixa]["dip"][ixb]=1;
          } else {
            d[series][game]["p"][ixa]["dip"][ixb]=2;
          } // if
          break;
        case 2: // war
          if ((ixa<ixb) && (d[series][game]["p"][ixa]["dip"][ixb]>2)) {
            d[series][game]["history"] += "Truce broken between " +ixa + " and "+ixb+"<br>";
          } // if
          d[series][game]["p"][ixa]["dip"][ixb]=2;
          break;
        case 3: // truce
          if (d[series][game]["p"][ixb]["set_dip"][ixa] >= 3) {
            if (ixa<ixb) {
              if (d[series][game]["p"][ixa]["dip"][ixb]>3) {
                d[series][game]["history"] += "Trade abandoned between " +ixa + " and "+ixb+"<br>";
              } else if (d[series][game]["p"][ixa]["dip"][ixb]<3) {
                d[series][game]["history"] += "Truce negotiated between " +ixa + " and "+ixb+"<br>";
              }
            } // if
            d[series][game]["p"][ixa]["dip"][ixb]=3;
          } else {
            if ((ixa<ixb) && (d[series][game]["p"][ixa]["dip"][ixb]>2)) {
              d[series][game]["history"] += "Truce broken between " +ixa + " and "+ixb+"<br>";
            } // if
            d[series][game]["p"][ixa]["dip"][ixb]=2;
          } // if
          break;
        case 4: // trade
          if ((ixa<ixb) && (d[series][game]["p"][ixa]["dip"][ixb]==5)) {
            d[series][game]["history"] += "Alliance dissolved between " +ixa + " and "+ixb+"<br>";
          } // if
          if (d[series][game]["p"][ixb]["set_dip"][ixa] >= 4) {
            if ((ixa<ixb) && (d[series][game]["p"][ixa]["dip"][ixb]<4)) {
              d[series][game]["history"] += "Trade commenced between " +ixa + " and "+ixb+"<br>";
            }
            d[series][game]["p"][ixa]["dip"][ixb]=4;
          } else if (d[series][game]["p"][ixb]["set_dip"][ixa] == 3) {
            if ((ixa<ixb) && (d[series][game]["p"][ixa]["dip"][ixb]>3)) {
              d[series][game]["history"] += "Trade abandoned between " +ixa + " and "+ixb+"<br>";
            }
            d[series][game]["p"][ixa]["dip"][ixb]=3;
          } else {
            if ((ixa<ixb) && (d[series][game]["p"][ixa]["dip"][ixb]>2)) {
              d[series][game]["history"] += "Truce broken between " +ixa + " and "+ixb+"<br>";
            } // if
            d[series][game]["p"][ixa]["dip"][ixb]=2;
          } // if
          break;
        case 5: // ally
          if (d[series][game]["p"][ixb]["set_dip"][ixa] == 5) {
            // check the max number of ally option
            if (ally_count >= d[series]["max_allies"]) {
              d[series][game]["p"][ixa]["set_dip"][ixb]=4;
              d[series][game]["p"][ixa]["dip"][ixb]=4;
              d[series][game]["p"][ixb]["set_dip"][ixa]=4;
              d[series][game]["p"][ixb]["dip"][ixa]=4;
              d[series][game]["p"][ixa]["missive"]+=
                "You may not ally with "+ixb+" - too many allies";
              d[series][game]["p"][ixb]["missive"]+=
                "You may not ally with "+ixa+" - too many allies";
            } else {
              if ((ixa<ixb) && (d[series][game]["p"][ixa]["dip"][ixb]!=5)) {
                d[series][game]["history"] += "Alliance declared between " +ixa + " and "+ixb+"<br>";
              } // if
              ally_count++;
              d[series][game]["p"][ixa]["dip"][ixb]=5;
            } // if
          } else if (d[series][game]["p"][ixb]["set_dip"][ixa] == 4) {
            if ((ixa<ixb) && (d[series][game]["p"][ixa]["dip"][ixb]==5)) {
              d[series][game]["history"] += "Alliance dissolved between " +ixa + " and "+ixb+"<br>";
            } // if
            d[series][game]["p"][ixa]["dip"][ixb]=4;
          } else {
            if ((ixa<ixb) && (d[series][game]["p"][ixa]["dip"][ixb]>=4)) {
              d[series][game]["history"] += "Trade abandoned between " +ixa + " and "+ixb+"<br>";
            } // if
            d[series][game]["p"][ixa]["dip"][ixb]=3;
          } // if
          break;
        default:
          d[series][game]["p"][ixa]["dip"][ixb]=2;
	    } // switch

        d[series][game]["p"][ixa]["missive"]+=
          (dip_word(d[series][game]["p"][ixa]["dip"][ixb])+
           " with "+ixb+"<br>");
      }
      else d[series][game]["p"][ixa]["dip"]-=([ixb:""]);
    }

    if( !d[series][game]["p"][ixa]["expl"] )
    {
      perror(sprintf( "SC 3.01: Nothing explored for player %s, "
       "%s %s. Replacing with empty string", ixa, series, game ));
      d[series][game]["p"][ixa]["expl"]="";             // What went wrong here?
    }

    expl=d[series][game]["p"][ixa]["expl"]/" ";

    foreach(expl, ixb) {
      // if explored got out of sync with what is in the database
      if(!d[series][game]["s"][ixb]) {
        d[series][game]["s"][ixb] = ([
                                      "max_pop":39,
                                      "vis_max_pop":39,
                                      "pop":0,
                                      "vis_pop":0,
                                      "min":35+random(40),
                                      "name":"Bug fixed system",
                                      "jump":"",
                                      "fuel":14+random(40),
                                      "owner":"",
                                      "ag":40+random(40),
                                      "home":""

        ]);
        perror("Fixed missing planet: "+ixb+"\n");
      }

      // doing owner specific stuff (i.e. we only want to do it once)
      if(d[series][game]["s"][ixb]["owner"]!=ixa) continue;
      d[series][game]["s"][ixb]["pop"]=
        (int) ((float) d[series][game]["s"][ixb]["pop"]*
               d[series][game]["p"][ixa]["ag_r"])+1;
      if(d[series][game]["s"][ixb]["pop"]>d[series][game]["s"][ixb]["max_pop"])
        d[series][game]["s"][ixb]["pop"]=d[series][game]["s"][ixb]["max_pop"];
      if(d[series][game]["s"][ixb]["pop"]<1)
        d[series][game]["s"][ixb]["pop"]=1;
    }

    foreach(indices(d[series][game]["f"]), ixb) {
      // doing owner specific stuff (i.e. we only want to do it once)
      if(d[series][game]["f"][ixb]["owner"]!=ixa) continue;

      // 3.0 change
      noreg=d[series][game]["f"][ixb]["cloak"];
      switch (noreg) {
      case 2:
        noreg=0;
        break;
      case 3:
        noreg=1;
        break;
      } // switch

      d[series][game]["f"][ixb]["br"]*=d[series][game]["p"][ixa]["min_r"];
      if(d[series][game]["f"][ixb]["br"]>(float)
         d[series][game]["f"][ixb]["max_br"])
        d[series][game]["f"][ixb]["br"]=(float)
          d[series][game]["f"][ixb]["max_br"];

      orders=d[series][game]["f"][ixb]["orders"];
      d[series][game]["f"][ixb]["orders"]="standby";
      if(sizeof(orders/":")>1)
        if((orders/":")[0]=="fleet" &&
           mappingp(d[series][game]["t"][(orders/":")[1]])) {
          d[series][game]["f"][ixb]["orders"]=orders;
          orders=d[series][game]["t"][(orders/":")[1]]["orders"];
        }

      if(orders!="build" && search(get_valid_orders(series, game, ixb, expl), orders)==-1) {
        d[series][game]["f"][ixb]["orders"]="standby";
      } else {
        switch(orders)
          {
          case "standby":
            break;
          case "build":
            d[series][game]["p"][ixa]["missive"]+= "<!--U:BUILD-->"+
              d[series][game]["f"][ixb]["name"]+" ("+
              d[series][game]["f"][ixb]["type"]+") was built at "+
              d[series][game]["s"][d[series][game]["f"][ixb]["loc"]]["name"]+".<br>";
            if (d[series][game]["f"][ixb]["type"]=="Cloaker") {
              noreg=d[series]["init_cloak"];
              d[series][game]["f"][ixb]["cloak"]=d[series]["init_cloak"];
            } // if cloaker
            if (d[series][game]["f"][ixb]["type"]=="Morpher") {
              d[series][game]["f"][ixb]["cloak"]=2;
            } // if morpher
            break;
          case "dismantle":
            d[series][game]["p"][ixa]["missive"]+="<!--U:DIS-->Ship "+
              d[series][game]["f"][ixb]["name"]+" dismantled.<br>";
            d[series][game]["p"][d[series][game]["f"][ixb]["owner"]]["ship_count"]--;
            d[series][game]["f"]-=([ixb:""]);
            noreg=1;
            break;
          case "nuke":
            if(!arrayp(nuke[d[series][game]["f"][ixb]["loc"]]))
              nuke[d[series][game]["f"][ixb]["loc"]]=({});
            nuke[d[series][game]["f"][ixb]["loc"]]+=({ixb});
            break;
          case "cloak":
            d[series][game]["f"][ixb]["cloak"]+=1;
            noreg=1;
            break;
          case "uncloak":
            d[series][game]["f"][ixb]["cloak"]-=1;
            noreg=0;
            loc=d[series][game]["f"][ixb]["loc"];
            other=d[series][game]["s"][loc]["owner"];
            if((other!="") && (other!=ixa)) {
              if(search(indices(d[series][game]["p"][ixa]["dip"]), other)==-1) {
                d[series][game]["p"][ixa]["dip"][other]=2;
                d[series][game]["p"][ixa]["set_dip"][other]=2;
                d[series][game]["p"][ixa]["missive"]+=
                  ("You have had first contact with "+other+
                   " in "+(d[series][game]["s"][loc]["name"]-"<br>")+
                   " (ship to system)<br>");

                d[series][game]["p"][other]["dip"][ixa]=2;
                d[series][game]["p"][other]["set_dip"][ixa]=2;
                d[series][game]["p"][other]["missive"]+=
                  ("You have had first contact with "+ixa+
                   " in "+(d[series][game]["s"][loc]["name"]-"<br>")+
                   " (system to ship)<br>");
                d[series][game]["history"] += "First contact between " +ixa + " and " + other +
                                     " in "+(d[series][game]["s"][loc]["name"]-"<br>")+"<br>";
              }
            }
            break;
          case "destroy":
            if(!arrayp(destroy[d[series][game]["f"][ixb]["loc"]]))
              destroy[d[series][game]["f"][ixb]["loc"]]=({});
            destroy[d[series][game]["f"][ixb]["loc"]]+=({ixb});
            break;
          case "form":
            if(!arrayp(form[d[series][game]["f"][ixb]["loc"]]))
              form[d[series][game]["f"][ixb]["loc"]]=({});
            form[d[series][game]["f"][ixb]["loc"]]+=({ixb});
            break;
          case "invade":
            if(!arrayp(invade[d[series][game]["f"][ixb]["loc"]]))
              invade[d[series][game]["f"][ixb]["loc"]]=({});
            invade[d[series][game]["f"][ixb]["loc"]]+=({ixb});
            break;
          case "col":
            if(!arrayp(col[d[series][game]["f"][ixb]["loc"]]))
              col[d[series][game]["f"][ixb]["loc"]]=({});
            col[d[series][game]["f"][ixb]["loc"]]+=({ixb});
            break;
          default:
            a=orders/":";
            if(sizeof(a)<2) {
              if(search(expl, orders)==-1) {
                expl+=({orders});
                d[series][game]["p"][ixa]["expl"]=expl*" ";
                d[series][game]["p"][ixa]["missive"]+=
                  (d[series][game]["f"][ixb]["name"]!=""?
                   d[series][game]["f"][ixb]["name"]+" explored ":"Explored ")+
                   d[series][game]["s"][orders]["name"]+
                   "<br>";
              }

              loc=d[series][game]["f"][ixb]["loc"]=orders;  // ?????    why = orders ??????

              other=d[series][game]["s"][loc]["owner"];

              if((other!="") && (other!=ixa) && !noreg) {
                if(search(indices(d[series][game]["p"][ixa]["dip"]), other)==-1) {
                  d[series][game]["p"][ixa]["dip"][other]=2;
                  d[series][game]["p"][ixa]["set_dip"][other]=2;
                  d[series][game]["p"][ixa]["missive"]+=
                    ("You have had first contact with "+other+
                     " in "+(d[series][game]["s"][loc]["name"]-"<br>")+
                     " (ship to system)<br>");

                  d[series][game]["p"][other]["dip"][ixa]=2;
                  d[series][game]["p"][other]["set_dip"][ixa]=2;
                  d[series][game]["p"][other]["missive"]+=
                    ("You have had first contact with "+ixa+
                     " in "+(d[series][game]["s"][loc]["name"]-"<br>")+
                     " (system to ship)<br>");

                  d[series][game]["history"] += "First contact between " +ixa + " and " + other +
                                       " in "+(d[series][game]["s"][loc]["name"]-"<br>")+"<br>";
                }
              }
            } else {
              if(a[0]=="send") {
                if(!mappingp(send[d[series][game]["f"][ixb]["loc"]]))
                  send[d[series][game]["f"][ixb]["loc"]]=([]);
                send[d[series][game]["f"][ixb]["loc"]][ixb]=a[1];
              } else if(a[0]=="open") {
                if(!mappingp(open[d[series][game]["f"][ixb]["loc"]]))
                  open[d[series][game]["f"][ixb]["loc"]]=([]);
                open[d[series][game]["f"][ixb]["loc"]][ixb]=a[1];
              } else if(a[0]=="close") {
                if(!mappingp(close[d[series][game]["f"][ixb]["loc"]]))
                  close[d[series][game]["f"][ixb]["loc"]]=([]);
                close[d[series][game]["f"][ixb]["loc"]][ixb]=a[1];
                // 3.0 additions
              } else if (a[0]=="create") {
                if(!arrayp(buildplan[a[1]]))
                  buildplan[a[1]]=({});
                buildplan[a[1]]+=({ixb});
              } else if (a[0]=="jumpto") {
                if(!mappingp(jto[d[series][game]["f"][ixb]["loc"]]))
                  jto[d[series][game]["f"][ixb]["loc"]]=([]);
                jto[d[series][game]["f"][ixb]["loc"]][ixb]=a[1];
              } else if (a[0]=="morph") {
			    d[series][game]["f"][ixb]["max_br"] =
					(float)d[series][game]["f"][ixb]["max_br"] - d[series]["morph_loss"];
				d[series][game]["f"][ixb]["br"] -= d[series]["morph_loss"];
				if(d[series][game]["f"][ixb]["max_br"] < d[series]["morph_loss"] ||
					 d[series][game]["f"][ixb]["br"] <= 0.0) {
					d[series][game]["p"][ixa]["missive"]+=("<!--U:morc-->The effort to morph has consumed"+
										 d[series][game]["f"][ixb]["name"]+" of "+
										 ixa+".<br>");
					d[series][game]["p"][ixa]["ship_count"]--;
					d[series][game]["f"]-=([ixb:""]);
				  } else {
                    d[series][game]["p"][ixa]["missive"]+= ("<!--U:morph-->"
                                  +d[series][game]["f"][ixb]["name"]+" of "+
                                  ixa+" has changed into a " +a[1]+".<br>");
                    if (a[1] == "Cloaker") {
                      noreg=d[series]["init_cloak"];
                      d[series][game]["f"][ixb]["cloak"]+=d[series]["init_cloak"];
                    } // if it changed into a cloaker
                    d[series][game]["f"][ixb]["type"]=a[1];
				  } // if killed
              } // if :
            }
          }
      }

      if(noreg!=1) {
        // 3.0C create a combat location
        if(!mappingp(sf[d[series][game]["f"][ixb]["loc"]])) {
          sf[d[series][game]["f"][ixb]["loc"]]=([]);
        } // if

        // 3C if this partic isn't here yet create a slot for them
        if(!arrayp(sf[d[series][game]["f"][ixb]["loc"]][d[series][game]["f"][ixb]["owner"]])) {
          sf[d[series][game]["f"][ixb]["loc"]][d[series][game]["f"][ixb]["owner"]]=({});
        } // if

        // 3C add the ship
        sf[d[series][game]["f"][ixb]["loc"]][d[series][game]["f"][ixb]["owner"]]+=({ixb});
      } // if not cloaked
    } // foreach ship

    foreach(indices(d[series][game]["t"]), ixb) {
      if(d[series][game]["t"][ixb]["owner"]!=ixa) continue;

      if(search(get_fleet_orders(series, game, ixb, expl),
                d[series][game]["t"][ixb]["orders"])!=-1) {
        if(d[series][game]["t"][ixb]["orders"]!="standby") {
          d[series][game]["t"][ixb]["loc"]=d[series][game]["t"][ixb]["orders"];
        } // if we're moving
        d[series][game]["t"][ixb]["orders"]="standby";
      } // if we can go there
    } // foreach fleet
  } // foreach player

  // 2.9C ship combat
  mapping asb, enb, en, br;

  float dmg, dest;
  mapping mined=([]);
  mapping swept=([]);

  // 3C foreach system, check for ships doing stuff
  foreach(indices(d[series][game]["s"]), ixa) {
    if(!mappingp(sf[ixa])) continue;

    if (!mappingp(missive[ixa])) {
      missive[ixa]=([]);
      missive[ixa]["dest"]=([]);
      missive[ixa]["sight"]=([]);
      missive[ixa]["sys"]="";
    } // if

    mined[ixa]=0;
    swept[ixa]=0;

    br=([]);
    en=([]);
    enb=([]);
    asb=([]);

    // 3C for every player who has ships here
    foreach(indices(sf[ixa]), ixb) {
      missive[ixa]["dest"][ixb]=({});
      missive[ixa]["sight"][ixb]=({});

      br[ixb]=0.0;
      enb[ixb]=0.0;
      asb[ixb]=0.0;

      // 3C foreach ship that player has here
      foreach(sf[ixa][ixb], ixc)
        if(mappingp(d[series][game]["f"][ixc]))
          br[ixb]+=pow(d[series][game]["f"][ixc]["br"], 2.0);

      if(d[series][game]["p"][ixb]["fuel_r"]<1.0)
        br[ixb]*=d[series][game]["p"][ixb]["fuel_r"];

      en[ixb]=({});

      // 3C now check to see who is our enemy here
      foreach(indices(sf[ixa]), ixc) {
        if(ixb==ixc) continue;

        // 2.9 changed magic number 0 to 2
        if(search(indices(d[series][game]["p"][ixb]["dip"]), ixc)==-1) {
          d[series][game]["p"][ixb]["dip"][ixc]=2;
          d[series][game]["p"][ixb]["set_dip"][ixc]=2;
          d[series][game]["p"][ixb]["missive"]+=
            ("<!--U:Contact-->You have had first contact with "+ixc+
             " in "+ixa+" (ship to ship)<br>");

          d[series][game]["p"][ixc]["dip"][ixb]=2;
          d[series][game]["p"][ixc]["set_dip"][ixb]=2;
          d[series][game]["p"][ixc]["missive"]+=
            ("<!--U:Contact-->You have had first contact with "+ixb+
             " in "+ixa+" (ship to ship)<br>");

          d[series][game]["history"] += "First contact between "+ixb+" and "+ixc+" in "+ixa+"<br>";

        }

        // 2.9 changed ==0 to <3
        if(d[series][game]["p"][ixb]["dip"][ixc]<3) en[ixb]+=({ixc});
      }
    } // 3C for everyone who has ships here

    // 3C now that we've done the initial data structure, go through and
    // calculate how much damage is done to each player
    foreach(indices(sf[ixa]), ixb) {
      foreach(en[ixb], ixc)
        enb[ixb]+=br[ixc];
      foreach(en[ixb], ixc)
        if(enb[ixb]>0.0) asb[ixc]+=(br[ixc]/enb[ixb])*br[ixb];
    }

    foreach(indices(sf[ixa]), ixb) {
      dest=asb[ixb]/2.0;
      foreach(sf[ixa][ixb], ixc) {
        if(mappingp(d[series][game]["f"][ixc])) {
          float effective_bp;
          effective_bp = pow(d[series][game]["f"][ixc]["br"], 2.0);
          if(d[series][game]["p"][ixb]["fuel_r"]<1.0)
            effective_bp*=d[series][game]["p"][ixb]["fuel_r"];
          if(effective_bp<=dest) {
            if((d[series][game]["f"][ixc]["type"]=="Minefield")) {
              continue;
            } // if minefield (immune to primary damage)
            if(d[series][game]["f"][ixc]["type"]=="Carrier") {
              if(d[series][game]["f"][ixc]["br"] > d[series]["car_loss"]) {
                d[series][game]["f"][ixc]["max_br"] -= d[series]["car_loss"];
                br[ixb]-=effective_bp;
                d[series][game]["f"][ixc]["br"] -= d[series]["car_loss"];
                effective_bp = pow(d[series][game]["f"][ixc]["br"], 2.0);
                if(d[series][game]["p"][ixb]["fuel_r"]<1.0)
                  effective_bp*=d[series][game]["p"][ixb]["fuel_r"];
                dest-=effective_bp;
                br[ixb]+=effective_bp;
                d[series][game]["p"][ixb]["missive"]+= ("<!--U:carl-->"
                                  +d[series][game]["f"][ixc]["name"]+" of "+
                                  ixb+" has taken damage.<br>");
                continue;
              } // if carrier has enough current br to take carrier loss
            } // if carrier
            missive[ixa]["dest"][ixb]+=({d[series][game]["f"][ixc]["name"]});
            dest-=effective_bp;
            br[ixb]-=effective_bp;
            sf[ixa][ixb]-=({ixc});
            d[series][game]["p"][d[series][game]["f"][ixc]["owner"]]["ship_count"]--;
            d[series][game]["f"]-=([ixc:""]);
          }
        }
      }
      if(br[ixb]>0.0) {
        dmg=1.0-(((asb[ixb]/2.0)+dest)/br[ixb]);
        // 3C if everyone is wiped out
        if(dmg<=0.0) {
          foreach(sf[ixa][ixb], ixc) {
            missive[ixa]["dest"][ixb]+=({d[series][game]["f"][ixc]["name"]});
            if((d[series][game]["f"][ixc]["type"]=="Minefield")) {
              mined[ixa]=1;
            } // if minefield (exploded by secondary damage)
            d[series][game]["p"][d[series][game]["f"][ixc]["owner"]]["ship_count"]--;
            d[series][game]["f"]-=([ixc:""]);
          }
        } else {
          foreach(sf[ixa][ixb], ixc) { // each remaining ship, sighting, sweeping and secondary damage
            missive[ixa]["sight"][ixb]+=({d[series][game]["f"][ixc]["name"]});
            if(d[series][game]["f"][ixc]["type"]=="Minesweeper") {
              swept[ixa]=1;
            } // if minesweeper
            d[series][game]["f"][ixc]["br"]*=dmg;
          }
        } // 3C wiped out or not
      } // 3C if there are any ships left
    }
  }
  // 2.9C end ship combat

  string g_missive="";
  string home;

  foreach(indices(mined), ixa)
    if(mined[ixa] && !swept[ixa]) {
      foreach(indices(d[series][game]["f"]), ixb)
        if(d[series][game]["f"][ixb]["loc"]==ixa) {
          missive[ixa]["dest"][d[series][game]["f"][ixb]["owner"]]+=
            ({d[series][game]["f"][ixb]["name"]});
          d[series][game]["p"][d[series][game]["f"][ixb]["owner"]]["ship_count"]--;
          d[series][game]["f"]-=([ixb:""]);
        }

      d[series][game]["s"][ixa]["pop"]/=2;
      if ( (d[series][game]["s"][ixa]["pop"]<1) && (d[series][game]["s"][ixa]["owner"] != "") ) {
        d[series][game]["s"][ixa]["pop"]=1;
      } //if

      missive[ixa]["sys"]+=
        "<!--U:Boom-->A minefield exploded in "+(d[series][game]["s"][ixa]["name"]-"<br>")+".<br>";

      d[series][game]["history"] += "A minefield exploded in system "+ixa+"<br>";
//      d[series][game]["history"] += "A minefield exploded in "+(d[series][game]["s"][ixa]["name"]-"<br>")+"<br>";

    }


  // 3 building code
  int built;
  int work;

  foreach(indices(buildplan), ixa) {
    built=0;
    work = 0;
    //if(d[series][game]["s"][ixa]->annihilated)
     // continue;

    // check to make sure too many planets aren't built
    if (d[series][game]["num_planets"] >= max_planets) {
      continue;
    } // if

    foreach(buildplan[ixa], ixb) {
      if(mappingp(d[series][game]["f"][ixb]) && !built) {
        work += d[series][game]["f"][ixb]["br"] * 10;
        if(work >= d[series]["pla_build"]) {
          built=1;
          d[series][game]["num_planets"]++;
          string loc = d[series][game]["f"][ixb]["loc"];
          d[series][game]["s"][ixa] = ([
              "max_pop":((d[series][game]["avg_fuel"]>d[series][game]["avg_min"])?d[series][game]["avg_fuel"]:d[series][game]["avg_min"]),
              "vis_max_pop":((d[series][game]["avg_fuel"]>d[series][game]["avg_min"])?d[series][game]["avg_fuel"]:d[series][game]["avg_min"]),
              "pop":0,
              "vis_pop":0,
              "min":(d[series][game]["avg_min"]||d[series]["avg_min"]), // or is for legacy
              "name":"System "+ixa,
              "jump":loc,
              "fuel":(d[series][game]["avg_fuel"]||d[series]["avg_fuel"]), // or is for legacy
              "owner":"",
              "ag":(d[series][game]["avg_ag"]||d[series]["avg_ag"]), // or is for legacy
              "home":""]);
          // 3.d probably don't need these 4 lines...
    		missive[ixa]=([]);
    		missive[ixa]["dest"]=([]);
    		missive[ixa]["sight"]=([]);
    		missive[ixa]["sys"]="";

        // set up an open link
  	      array jumps;
          jumps = d[series][game]["s"][loc]["jump"]/" ";
          jumps += ({ ixa });
          d[series][game]["s"][loc]["jump"] = jumps * " ";

		// add this planet as explored
		  array exp;
          exp = d[series][game]["p"][d[series][game]["f"][ixb]["owner"]]["expl"]/" ";
	      exp += ({ixa});
          d[series][game]["p"][d[series][game]["f"][ixb]["owner"]]["expl"] = exp*" ";

          d[series][game]["p"][d[series][game]["f"][ixb]["owner"]]["missive"]+=
          "<!--U:cre-->"+((d[series][game]["s"][ixa]["name"]-"<br>"))+
                                 " was successfully created.<br>";
//          d[series][game]["history"] += "<tr>Planet " + ixa + " created.</tr>";
          d[series][game]["history"] += "System " + ixa + " created.<br>";
        } // we have enough to create it
        d[series][game]["p"][d[series][game]["f"][ixb]["owner"]]["ship_count"]--;
        d[series][game]["f"]-=([ixb:""]);
      } // if it hasn't been built yet
    } // foreach ship trying to build this planet

    //if (built==0) {
      // it might be nice to say that it failed....
      // Note for future ref:
      // Need to build an array of owners of builders which attempted to create this system within the above loop,
      //   and here notify each of them of the failure. Actually we should probably notify all the owners of 
      //   success as well, not just the owner of the last successful ship :)   APG
    //} // if
  } // foreach system we are trying to build


  int sent;

  // 3C do stargating
  // note only 1 stargate can be used at a location
  foreach(indices(send), ixa) {
    sent=0;
    foreach(indices(send[ixa]), ixb)
      // 3C if the gate hasn't been destroyed or used, and still has the clout to send that far
      if(mappingp(d[series][game]["f"][ixb]) && !sent &&
          (search(get_valid_orders(series, game, ixb,
              d[series][game]["p"][d[series][game]["f"][ixb]["owner"]]["expl"]/" "),"send:"+send[ixa][ixb])!=-1)) {
        // 3C look for ships at the same location
        foreach(indices(d[series][game]["f"]), ixc) {
          if(d[series][game]["f"][ixc]["loc"]==
             d[series][game]["f"][ixb]["loc"] &&
             search(move_ships, d[series][game]["f"][ixc]["type"])!=-1 &&
             d[series][game]["f"][ixc]["owner"]==
             d[series][game]["f"][ixb]["owner"]) {
            d[series][game]["f"][ixc]["loc"]=send[ixa][ixb];
          }

          sent=1;
        }

        // 3C lets also gate the fleets
// -----------------16/10/97 2:19--------------------seems broken
        foreach(indices(d[series][game]["t"]), ixc) {
          if(d[series][game]["t"][ixc]["loc"]==
             d[series][game]["f"][ixb]["loc"] &&
             d[series][game]["t"][ixc]["owner"]==
             d[series][game]["f"][ixb]["owner"]) {
            d[series][game]["t"][ixc]["loc"]=send[ixa][ixb];
          }
        }
//
      }
  } // stargating

  // 3C do jumpgating
  // note only 1 jumpgate can be used at a location
	// this was indices(send), but i think it should be jto
  foreach(indices(jto), ixa) {
    sent=0;
    foreach(indices(jto[ixa]), ixb)
      // 3C if the gate hasn't been destroyed or used, and still has the clout to send that far
      if(mappingp(d[series][game]["f"][ixb]) && !sent &&
          (search(get_valid_orders(series, game, ixb,
              d[series][game]["p"][d[series][game]["f"][ixb]["owner"]]["expl"]/" "),"jumpto:"+jto[ixa][ixb])!=-1)) {
        // 3C look for ships at the same location
        foreach(indices(d[series][game]["f"]), ixc) {
          if(d[series][game]["f"][ixc]["loc"]==
             d[series][game]["f"][ixb]["loc"] &&
             search(move_ships, d[series][game]["f"][ixc]["type"])!=-1 &&
             d[series][game]["f"][ixc]["owner"]==
             d[series][game]["f"][ixb]["owner"]) {
            d[series][game]["f"][ixc]["loc"]=jto[ixa][ixb];
          }
          sent=1;
        }

        // 3C lets also gate the fleets
 // -----------------16/10/97 2:21--------------------  probably is broken
        foreach(indices(d[series][game]["t"]), ixc) {
          if( (d[series][game]["t"][ixc]["loc"] == d[series][game]["f"][ixb]["loc"]) &&
              (d[series][game]["t"][ixc]["owner"] == d[series][game]["f"][ixb]["owner"]) ) {
            d[series][game]["t"][ixc]["loc"]=jto[ixa][ixb];
          }
        }
//
        // now apply the cost
          d[series][game]["f"][ixb]["max_br"] = (float)d[series][game]["f"][ixb]["max_br"] - d[series]["jump_loss"];
          d[series][game]["f"][ixb]["br"] -= d[series]["jump_loss"];
          if(d[series][game]["f"][ixb]["max_br"] <= d[series]["jump_loss"] ||
             d[series][game]["f"][ixb]["br"] <= 0.0) {
            missive[ixa]["sys"]+=("<!--U:Jump-->The effort to use the jumpgate at "+ixa+" to "+
                                 jto[ixa][ixb]+" has consumed "+
                                 d[series][game]["f"][ixb]["name"]+" of "+
                                 d[series][game]["f"][ixb]["owner"]+".<br>");
            d[series][game]["p"][d[series][game]["f"][ixb]["owner"]]["ship_count"]--;
            d[series][game]["f"]-=([ixb:""]);
          } // if consumed

      }
  } // jumpgating

  int nuked;

  // 2.9C for every nuke order
  foreach(indices(nuke), ixa) {
    nuked=0;

	// 2.9C if it was annilated before we ignore it
    if(d[series][game]["s"][ixa]->annihilated)
      continue;

    foreach(nuke[ixa], ixb)
      if(mappingp(d[series][game]["f"][ixb]) && !nuked) {
        nuked=1;

        d[series][game]["s"][ixa]["min"]/=2;
        d[series][game]["s"][ixa]["fuel"]/=2;
        d[series][game]["s"][ixa]["ag"]/=2;
        d[series][game]["s"][ixa]["max_pop"]=
            MAX(d[series][game]["s"][ixa]["min"],
                d[series][game]["s"][ixa]["fuel"]);

        d[series][game]["s"][ixa]["pop"]=0;
        d[series][game]["s"][ixa]["owner"]="";

        home=d[series][game]["s"][ixa]["home"];


        string attacker = d[series][game]["f"][ixb]["owner"];
        string syst = (d[series][game]["s"][ixa]["name"]-"<br>");
        // fix it so that in multiworld games multiple nukes
        // can't occur
        if((home=="") || (!mappingp(d[series][game]["p"][home]))) {
          missive[ixa]["sys"]+=
            "<!--U:nuke-->" + (syst+" was nuked by "+
             d[series][game]["f"][ixb]["name"]+" of "+
             attacker+".<br>");
            //d[series][game]["history"] += "<tr>Planet "+syst+" nuked  by " + attacker + "</tr>";
            d[series][game]["history"] += "System "+ixa+" nuked  by " + attacker + "<br>";
        } else {
//          d[series][game]["history"] += "<tr>Empire "+home+" nuked out of the game by " +
//            attacker + "</tr>";
          d[series][game]["history"] += "Empire "+home+" nuked out of the game by "+attacker + "<br>";
          g_missive+="<!--U:kill-->" + home +" has been obliterated.<br>";
          d[series][game]["s"][ixa]["home"]="";

          // add player for later game result notification
          // BACKCOMPATABILITY - This if is only until all of the games have this field
          if (!mappingp(d[series][game]["nuked"])) {
            d[series][game]["nuked"] = ([]);
          } // if
          d[series][game]["nuked"][home] = 1;

          p[home]["missive"] += ("You were nuked out of "+series+" "+game+" by "+attacker+"<p>"+p[attacker]["sneer"]+"<p>");
          p[home]["killed"]++;

          if (d[series][game]["bridier"]) {
            apply_bridier(series, game, attacker, home);
            if (d[series][game]["rank_table"]) {
              p[home]["missive"] += d[series][game]["rank_table"];
            }
          }

          //2.9 changed it so if you were idle you are considered
          // ruined also
          int update_count = d[series][game]["u_cnt"];
          int last_update = d[series][game]["p"][home]["l_up"];
          if ((update_count-last_update)>2) {
            p[home]["ruins"]++;
          } // if
          p[attacker]["kills"]++;

          foreach(indices(d[series][game]["s"]), ixc)
            if(d[series][game]["s"][ixc]["owner"]==home) {
              d[series][game]["s"][ixc]["pop"]=0;
              d[series][game]["s"][ixc]["owner"]="";
            }

          foreach(indices(d[series][game]["f"]), ixc)
            if(d[series][game]["f"][ixc]["owner"]==home)
              d[series][game]["f"]-=([ixc:""]);

          foreach(indices(d[series][game]["t"]), ixc)
            if(d[series][game]["t"][ixc]["owner"]==home)
              d[series][game]["t"]-=([ixc:""]);

          d[series][game]["p"]-=([home:""]);
        }
      }
  }

  int destroyed;

  foreach(indices(destroy), ixa) {
    destroyed=0;

    foreach(destroy[ixa], ixb)
      if(mappingp(d[series][game]["f"][ixb]) && !destroyed) {
        destroyed=1;

        d[series][game]["s"][ixa]["min"]=0;
        d[series][game]["s"][ixa]["fuel"]=0;
        d[series][game]["s"][ixa]["ag"]=0;
        d[series][game]["s"][ixa]["max_pop"]=0;

        d[series][game]["s"][ixa]["pop"]=0;
        d[series][game]["s"][ixa]["owner"]="";
        d[series][game]["s"][ixa]["annihilated"]=1;
        home=d[series][game]["s"][ixa]["home"];

        string attacker = d[series][game]["f"][ixb]["owner"];
        string syst = (d[series][game]["s"][ixa]["name"]-"<br>");

        if((home=="") || (!mappingp(d[series][game]["p"][home]))) {
          missive[ixa]["sys"]+= "<!--U:annih-->" +
            (d[series][game]["s"][ixa]["name"]+" was annihilated by "+
             d[series][game]["f"][ixb]["name"]+" of "+
             attacker+".<br>");
          d[series][game]["s"][ixa]["name"]="remains of "+syst;
          //d[series][game]["history"] += "<tr>Planet "+syst+" annihilated by " + attacker + "</tr>";
          d[series][game]["history"] += "System "+ixa+" annihilated by " + attacker + "<br>";
        } else {
//          d[series][game]["history"] += "<tr>Empire "+home+" annihilated out of the game by " + attacker + "</tr>";
          d[series][game]["history"] += "Empire "+home+" annihilated out of the game by " + attacker + "<br>";
          g_missive += "<!--U:annk-->" + home+" has been wiped from the face of the universe.<br>";
          p[home]["missive"]+=("You were annihilated out of "+series+" "+game+" by "+attacker+"<p>"+p[attacker]["sneer"]+"<p>");
          d[series][game]["s"][ixa]["home"]="";
          p[home]["killed"]++;
          p[attacker]["kills"]++;

          if (d[series][game]["bridier"]) {
            apply_bridier(series, game, attacker, home);
            if (d[series][game]["rank_table"]) {
              p[home]["missive"] += d[series][game]["rank_table"];
            }
          }

          // add player for later game result notification
          // BACKCOMPATABILITY - This if is only until all of the games have this field
          if (!mappingp(d[series][game]["nuked"])) {
            d[series][game]["nuked"] = ([]);
          } // if
          d[series][game]["nuked"][home] = 1;

          foreach(indices(d[series][game]["s"]), ixc)
            if(d[series][game]["s"][ixc]["owner"]==home) {
              d[series][game]["s"][ixc]["pop"]=0;
              d[series][game]["s"][ixc]["owner"]="";
            }

          foreach(indices(d[series][game]["f"]), ixc)
            if(d[series][game]["f"][ixc]["owner"]==home)
              d[series][game]["f"]-=([ixc:""]);

          foreach(indices(d[series][game]["t"]), ixc)
            if(d[series][game]["t"][ixc]["owner"]==home)
              d[series][game]["t"]-=([ixc:""]);

          d[series][game]["p"]-=([home:""]);
        }
      }
  }

  int invaded;

  foreach(indices(invade), ixa) {
    invaded=0;
    if(d[series][game]["s"][ixa]->annihilated)
      continue;

    foreach(invade[ixa], ixb)
      if(mappingp(d[series][game]["f"][ixb]) && !invaded) {
        if((int) d[series][game]["f"][ixb]["br"]*10 >
           d[series][game]["s"][ixa]["pop"]) {
          invaded=1;

          d[series][game]["s"][ixa]["pop"]/=2;
          d[series][game]["s"][ixa]["owner"]=
            d[series][game]["f"][ixb]["owner"];

          home=d[series][game]["s"][ixa]["home"];

          string attacker = d[series][game]["f"][ixb]["owner"];
          string syst = (d[series][game]["s"][ixa]["name"]-"<br>");

          // fix it so that in multiworld games multiple nukes
          // can't occur
          if((home=="") || (!mappingp(d[series][game]["p"][home]))) {
            missive[ixa]["sys"]+="<!--U:inv-->"+(syst+
                                 " was successfully invaded by "+
                                 d[series][game]["f"][ixb]["name"]+" of "+
                                 attacker+".<br>");
            //d[series][game]["history"] += "<tr>Planet "+syst+" invaded by " + attacker + "</tr>";
            d[series][game]["history"] += "System "+ixa+" invaded by " + attacker + "<br>";
          } else {
//          d[series][game]["history"] += "<tr>Empire "+home+" invaded out of the game by " + attacker + "</tr>";
            d[series][game]["history"] += "Empire "+home+" invaded out of the game by " + attacker + "<br>";
            g_missive+="<!--U:invk-->"+home+" has been taken over.<br>";
            p[home]["missive"]+=("You were invaded out of "+series+" "+game+" by "+attacker+"<p>"+p[attacker]["sneer"]+"<p>");
            p[home]["killed"]++;
            p[attacker]["kills"]++;

            if (d[series][game]["bridier"]) {
              apply_bridier(series, game, attacker, home);
              if (d[series][game]["rank_table"]) {
                p[home]["missive"] += d[series][game]["rank_table"];
              }
            }

            // add player for later game result notification
            // BACKCOMPATABILITY - This if is only until all of the games have this field
            if (!mappingp(d[series][game]["nuked"])) {
              d[series][game]["nuked"] = ([]);
            } // if
              d[series][game]["nuked"][home] = 1;


            // 2.9 get rid of the invasion/multiple nuke bug
            // 3 i don't think this is necessary any more, but it can't hurt
            d[series][game]["s"][ixa]["home"]="";

            foreach(indices(d[series][game]["s"]), ixc)
              if(d[series][game]["s"][ixc]["owner"]==home) {
                d[series][game]["s"][ixc]["pop"]=0;
                d[series][game]["s"][ixc]["owner"]="";
              }

            foreach(indices(d[series][game]["f"]), ixc)
              if(d[series][game]["f"][ixc]["owner"]==home)
                d[series][game]["f"]-=([ixc:""]);

            foreach(indices(d[series][game]["t"]), ixc)
              if(d[series][game]["t"][ixc]["owner"]==home)
                d[series][game]["t"]-=([ixc:""]);

            d[series][game]["p"]-=([home:""]);
          }
        } else {
          d[series][game]["s"][ixa]["pop"]-=
            (int) d[series][game]["f"][ixb]["br"]*2;

          missive[ixa]["sys"]+="<!--U:uinv-->" + ((d[series][game]["s"][ixa]["name"]-"<br>")+
                               " was unsuccessfully invaded by "+
                               d[series][game]["f"][ixb]["name"]+" of "+
                               d[series][game]["f"][ixb]["owner"]+".<br>");
        }

        d[series][game]["p"][d[series][game]["f"][ixb]["owner"]]["ship_count"]--;
        d[series][game]["f"]-=([ixb:""]);
      }
  }


  // 2.9C Colonization code
  int coled;

  foreach(indices(col), ixa) {
    coled=0;
    if(d[series][game]["s"][ixa]->annihilated)
      continue;
    foreach(col[ixa], ixb)
      if(mappingp(d[series][game]["f"][ixb]) && !coled) {
        coled=1;

        missive[ixa]["sys"]+="<!--U:colo-->"+((d[series][game]["s"][ixa]["name"]-"<br>")+
                             " was colonized by "+
                             d[series][game]["f"][ixb]["name"]+" of "+
                             d[series][game]["f"][ixb]["owner"]+".<br>");

//        d[series][game]["history"] += (d[series][game]["s"][ixa]["name"]-"<br>")+" was colonized by "+
//                            (d[series][game]["f"][ixb]["name"]=""?"un-named ship":d[series][game]["f"][ixb]["name"])+" of "+
//                             d[series][game]["f"][ixb]["owner"]+".<br>";
        d[series][game]["history"] += "System " + ixa+" was colonized by "+
                             d[series][game]["f"][ixb]["owner"]+".<br>";

        // 2.9 check to see if we are colonizing someone who
        // surrendered
        string old_empire = d[series][game]["s"][ixa]["home"];
        if (old_empire != "") {
// Surrender change!  APG
//          p[old_empire]["killed"]++;
          string owner = d[series][game]["f"][ixb]["owner"];
          p[owner]["kills"]++;
// in case of multi-player mulithome games
          foreach(indices(d[series][game]["s"]), ixc) {
            if (d[series][game]["s"][ixc]["home"]==old_empire)
              d[series][game]["s"][ixc]["home"] = "";
          }
          g_missive += "<!--U:colt-->"+owner+" has taken over the remains of "+ old_empire+"'s empire<br>";

          d[series][game]["history"] += owner+" took over the remains of "+ old_empire+"'s empire<br>";
        } // if

        d[series][game]["s"][ixa]["pop"]=
          (int) pow(d[series][game]["f"][ixb]["br"], 2.0);
        if(d[series][game]["s"][ixa]["pop"]<1)
          d[series][game]["s"][ixa]["pop"]=1;
        d[series][game]["s"][ixa]["owner"]=d[series][game]["f"][ixb]["owner"];
        d[series][game]["p"][d[series][game]["f"][ixb]["owner"]]["ship_count"]--;
        d[series][game]["f"]-=([ixb:""]);
      }
  } // 2.9C foreach colonization

  // 2.9C Terraforming code
  int formed;
  int max;

  foreach(indices(form), ixa) {
    formed=0;
    if(d[series][game]["s"][ixa]->annihilated)
      continue;

    foreach(form[ixa], ixb)
      if(mappingp(d[series][game]["f"][ixb]) && !formed) {
        formed=1;

        missive[ixa]["sys"]+="<!--U:terr-->"+((d[series][game]["s"][ixa]["name"]-"<br>")+
                             " was terraformed by "+
                             d[series][game]["f"][ixb]["name"]+" of "+
                             d[series][game]["f"][ixb]["owner"]+".<br>");

//        d[series][game]["history"] += (d[series][game]["s"][ixa]["name"]-"<br>")+" was terraformed by "+
//                             d[series][game]["f"][ixb]["name"]+" of "+
//                             d[series][game]["f"][ixb]["owner"]+".<br>";
        d[series][game]["history"] += "System " + ixa +" was terraformed by "+
                             d[series][game]["f"][ixb]["owner"]+".<br>";

        d[series][game]["s"][ixa]["ag"]+=
          (int) (d[series][game]["f"][ixb]["br"]*10.0);
        if(d[series][game]["s"][ixa]["min"]>d[series][game]["s"][ixa]["fuel"])
          max=d[series][game]["s"][ixa]["min"];
        else max=d[series][game]["s"][ixa]["fuel"];

        if(d[series][game]["s"][ixa]["ag"]>max)
          d[series][game]["s"][ixa]["ag"]=max;

        d[series][game]["p"][d[series][game]["f"][ixb]["owner"]]["ship_count"]--;
        d[series][game]["f"]-=([ixb:""]);
      }
  }
  array jumps, jumps2;

  // 2.9C Engineering code
  foreach(indices(close), ixa) {
    jumps = d[series][game]["s"][ixa]["jump"]/" ";
    foreach(indices(close[ixa]), ixb) {
      if(mappingp(d[series][game]["f"][ixb]) &&
         d[series][game]["f"][ixb]["br"] >= d[series]["eng_loss"] &&
         search(jumps, close[ixa][ixb]) != -1)
        {
          jumps2 = d[series][game]["s"][close[ixa][ixb]]["jump"]/" ";
          jumps -= ({ close[ixa][ixb] , ""});
          jumps2 -= ({ ixa , ""});

          d[series][game]["s"][ixa]["jump"] = jumps * " ";
          d[series][game]["s"][close[ixa][ixb]]["jump"] = jumps2 * " ";
          d[series][game]["f"][ixb]["max_br"] =
            (float)d[series][game]["f"][ixb]["max_br"] - d[series]["eng_loss"];
          d[series][game]["f"][ixb]["br"] -= d[series]["eng_loss"];
          d[series][game]["history"] += "Jump from "+ixa+" to "+close[ixa][ixb]+" was closed by "+
                                 d[series][game]["f"][ixb]["owner"]+".<br>";
          if(d[series][game]["f"][ixb]["max_br"] < d[series]["eng_loss"] ||
             d[series][game]["f"][ixb]["br"] <= 0.0) {
            missive[ixa]["sys"]+=("<!--U:engc-->The effort to close the jump from "+ixa+" to "+
                                 close[ixa][ixb]+" has consumed "+
                                 d[series][game]["f"][ixb]["name"]+" of "+
                                 d[series][game]["f"][ixb]["owner"]+".<br>");

            d[series][game]["p"][d[series][game]["f"][ixb]["owner"]]["ship_count"]--;
            d[series][game]["f"]-=([ixb:""]);
          } else {
            missive[ixa]["sys"]+= ("<!--U:enga-->Jump from "+ixa+" to "+close[ixa][ixb]+
                                  " was closed by "
                                  +d[series][game]["f"][ixb]["name"]+" of "+
                                  d[series][game]["f"][ixb]["owner"]+".<br>");
          }
        }
    }
  }
  foreach(indices(open), ixa) {
    jumps = d[series][game]["s"][ixa]["jump"]/" ";
    if(!mappingp(open[ixa]))
      continue;
    foreach(indices(open[ixa]), ixb) {
      if(mappingp(d[series][game]["f"][ixb]) &&
         d[series][game]["f"][ixb]["br"] >= d[series]["eng_loss"] &&
         search(jumps, open[ixa][ixb]) == -1)
        {
          jumps2 = d[series][game]["s"][open[ixa][ixb]]["jump"]/" ";
          jumps += ({ open[ixa][ixb] });
          jumps2 += ({ ixa });
          d[series][game]["s"][ixa]["jump"] = jumps * " ";
          d[series][game]["s"][open[ixa][ixb]]["jump"] = jumps2 * " ";
          d[series][game]["f"][ixb]["max_br"] =
            (float)d[series][game]["f"][ixb]["max_br"] - d[series]["eng_loss"];
          d[series][game]["f"][ixb]["br"] -= d[series]["eng_loss"];
          d[series][game]["history"] += "Jump from "+ixa+" to "+open[ixa][ixb]+" was opened by "+
                                 d[series][game]["f"][ixb]["owner"]+".<br>";
          if(d[series][game]["f"][ixb]["max_br"] < d[series]["eng_loss"] ||
             d[series][game]["f"][ixb]["br"] <= 0.0) {
            missive[ixa]["sys"]+=("<!--U:engc-->The effort to open the jump from "+ixa+" to "+
                                 open[ixa][ixb]+" has consumed "+
                                 d[series][game]["f"][ixb]["name"]+" of "+
                                 d[series][game]["f"][ixb]["owner"]+".<br>");

            d[series][game]["p"][d[series][game]["f"][ixb]["owner"]]["ship_count"]--;
            d[series][game]["f"]-=([ixb:""]);
          } else {
            missive[ixa]["sys"]+= ("<!--U:enga-->Jump from "+ixa+" to "+open[ixa][ixb]+
                                  " was opened by "
                                  +d[series][game]["f"][ixb]["name"]+" of "+
                                  d[series][game]["f"][ixb]["owner"]+".<br>");
          }
        }
    }
  }

  string verb;
  string shipstring;

  // 2.9C end of turn stuff, i.e. ruined, notice magic number 12
  foreach(indices(d[series][game]["p"]), ixa) {
    int num_players = 0;
    string other_player;
    if((d[series][game]["p"][ixa]["l_up"]+12)<d[series][game]["u_cnt"]) {
      if ( p[ixa] ) {
        p[ixa]["ruins"]++;
        p[ixa]["missive"] += "<!--G:ruin-->You fell into ruin in "+series+" "+game+"<br>";
      }

      g_missive+="<!--U:ruin-->"+ixa+" has fallen into ruins<br>";
//      d[series][game]["history"] += "<tr>" + ixa + " fell into ruins.</tr>";
      d[series][game]["history"] += ixa + " fell into ruins.<br>";

      // add player for later game result notification
      // BACKCOMPATABILITY - This if is only until all of the games have this field
      if (!mappingp(d[series][game]["nuked"])) {
        d[series][game]["nuked"] = ([]);
      } // if
      d[series][game]["nuked"][ixa] = 1;

      foreach(indices(d[series][game]["p"]), ixb) {
        if (ixa==ixb) continue;
        num_players++;
        other_player = ixb;
      }

      if (num_players==1 && d[series][game]["bridier"]) {
        apply_bridier(series, game, other_player, ixa);
      }

      d[series][game]["p"]-=([ixa:""]);

      foreach(indices(d[series][game]["f"]), ixb)
        if(d[series][game]["f"][ixb]["owner"]==ixa)
          d[series][game]["f"]-=([ixb:""]);

      foreach(indices(d[series][game]["s"]), ixb) {
        if(d[series][game]["s"][ixb]["owner"]==ixa) {
          d[series][game]["s"][ixb]["pop"]=0;
          d[series][game]["s"][ixb]["owner"]="";
        }
        if(d[series][game]["s"][ixb]["home"]==ixa) {
          d[series][game]["s"][ixb]["min"]/=2;
          d[series][game]["s"][ixb]["fuel"]/=2;
          d[series][game]["s"][ixb]["ag"]/=2;
          d[series][game]["s"][ixb]["max_pop"]=
              MAX(d[series][game]["s"][ixb]["min"],
                  d[series][game]["s"][ixb]["fuel"]);
          d[series][game]["s"][ixb]["home"]="";
        }
      }
      continue;
    } // 2.9 if ruin

    int surrendered = 0;
    foreach (indices(d[series][game]["p"][ixa]["set_dip"]), ixb) {
      if (ixa==ixb) continue;
      if (d[series][game]["p"][ixa]["set_dip"][ixb] ==0) surrendered = 1;
      num_players++;
      other_player = ixb;
    } // 2.9 surrender check

    if(surrendered==1) {
      p[ixa]["missive"]+="<!--G:surr-->You surrendered "+series+" "+game + "<br>";
      g_missive+="<!--G:surr-->"+ixa+" has surrendered<br>";
      d[series][game]["history"] += ixa + " has surrendered.<br>";

      // add player for later game result notification
      // BACKCOMPATABILITY - This if is only until all of the games have this field
      if (!mappingp(d[series][game]["nuked"])) {
        d[series][game]["nuked"] = ([]);
      } // if
      d[series][game]["nuked"][ixa] = 1;
// Surrender change!  A 'killed' should be added whether they get colonised later or not. APG
      p[ixa]["killed"]++;

      if (num_players==1 && d[series][game]["bridier"]) {
        apply_bridier(series, game, other_player, ixa);
        if (d[series][game]["rank_table"]) {
          p[ixa]["missive"] += d[series][game]["rank_table"];
        }
      }

      // this didn't seem to be working, so i moved the player delete
      // to after the num player check.
// Surrender change!  Neither the 'kills' nor the 'killed' need be incremented here. The
// 'killed' is dealt with above, and the 'kill' will come out in the wash at the end.  APG
//      if (num_players==1) {
//        p[ixa]["killed"]++;
//        p[ixb]["kills"]++;     That should have been other_player, not ixb!
//      } // 2.9 1 other player
      d[series][game]["p"]-=([ixa:""]);

      foreach(indices(d[series][game]["f"]), ixb)
        if(d[series][game]["f"][ixb]["owner"]==ixa)
          d[series][game]["f"]-=([ixb:""]);

      foreach(indices(d[series][game]["s"]), ixb) {
        if(d[series][game]["s"][ixb]["owner"]==ixa) {
          d[series][game]["s"][ixb]["pop"]=0;
          d[series][game]["s"][ixb]["owner"]="";
        }
        if(d[series][game]["s"][ixb]["home"]==ixa) {
          if (surrendered) {
            d[series][game]["s"][ixb]["min"]/=2;
            d[series][game]["s"][ixb]["fuel"]/=2;
            d[series][game]["s"][ixb]["ag"]/=2;
            d[series][game]["s"][ixb]["max_pop"]=
                MAX(d[series][game]["s"][ixb]["min"],
                    d[series][game]["s"][ixb]["fuel"]);
            surrendered = 0;
            // 2.9 i'll leave this set so someone can get the nuke
            //    d[series][game]["s"][ixb]["home"]="";
          } else {

            // 2.9 in games with multi world start only one of the start
            // planets is available for surrender colonization
            d[series][game]["s"][ixb]["home"]="";
          } // if
        } // if
      }
      continue;
    } // 2.9 if surrender

    expl=d[series][game]["p"][ixa]["expl"]/" ";
    foreach(indices(missive), ixb) {
      if(search(expl, ixb)==-1) continue;

      foreach(indices(missive[ixb]["dest"]), ixc) {
        if(!sizeof(missive[ixb]["dest"][ixc])) continue;

        if(sizeof(missive[ixb]["dest"][ixc])==1)
          verb="was";
        else
          verb="were";

        shipstring=ship_string(missive[ixb]["dest"][ixc]);

        d[series][game]["p"][ixa]["missive"] +=
          "<!--U:dest-->"+shipstring+" of "+ixc+" "+verb+" destroyed in "+
           (d[series][game]["s"][ixb]["name"]-"<br>")+"<br>";
      }

      foreach(indices(missive[ixb]["sight"]), ixc) {
        if(ixc==ixa) continue;
        if(!sizeof(missive[ixb]["sight"][ixc])) continue;

        if(sizeof(missive[ixb]["sight"][ixc])==1) verb="was";
        else verb="were";

        shipstring=ship_string(missive[ixb]["sight"][ixc]);

        d[series][game]["p"][ixa]["missive"]+=
          "<!--U:seen-->"+shipstring+" of "+ixc+" "+verb+" sighted in "+
           (d[series][game]["s"][ixb]["name"]-"<br>")+"<br>";
      }

      if(missive[ixb]["sys"]!="")
        d[series][game]["p"][ixa]["missive"]+=missive[ixb]["sys"];
    } // 2.9C foreach missive

	// 2.9C I don't know why this was here.
    //d[series][game]["p"][ixa]["missive"]+="<br>"+g_missive;

    recalc_ratios(series, game, ixa);
  } // 2.9C foreach check for ruin

// fresh look at destruction history
  foreach(indices(missive), ixa) {
    foreach(indices(missive[ixa]["dest"]), ixb) {
      if(!sizeof(missive[ixa]["dest"][ixb])) continue;
      if(sizeof(missive[ixa]["dest"][ixb])==1)
        verb="was";
      else
        verb="were";
      shipstring=ship_string(missive[ixa]["dest"][ixb]);
      d[series][game]["history"] += shipstring+" of "+ixb+" "+verb+" destroyed in "+
                                    (d[series][game]["s"][ixa]["name"]-"<br>")+"<br>";
    }
  }

  foreach(indices(d[series][game]["s"]), ixa) {
    d[series][game]["s"][ixa]["vis_pop"]=d[series][game]["s"][ixa]["pop"];
    d[series][game]["s"][ixa]["vis_max_pop"]=d[series][game]["s"][ixa]["max_pop"];
  }

  int fail=0;
  int fail2=0;

  // 2.9C see if a peaceful solution exists
  foreach(indices(d[series][game]["p"]), ixa) {
	// 2.9 add general news since we are going through everyone
    d[series][game]["p"][ixa]["missive"]+="<br>"+g_missive;

    foreach(indices(d[series][game]["p"]), ixb) {
      if(ixa==ixb) continue;
  
      // 2.9 check to see if everyone tied or is allied
      int dip = d[series][game]["p"][ixa]["dip"][ixb];
      if(((dip<5) && (dip > 1)) || (dip==0)) fail=1;
      if(dip==1) fail2=1;
    } // 2.9C foreach empire ixb
  } // 3.2 foreach empire ixa

  // 2.9 a tie occurred
  if((d[series][game]["full"]>=d[series]["p_max"]) && fail2 && !fail) {
    int n_drawers = 0;
    string drawers = "";
    foreach(indices(d[series][game]["p"]), ixa) {
      p[ixa]["missive"]+="<!--G:draw-->You have tied "+series+" "+game+"<br>";
      if (n_drawers != 0)
        drawers += ", ";
      drawers += ixa;
      n_drawers++;
    }

//    d[series][game]["history"] += "<tr>Game declared a draw.</tr></table></td></tr></table>";
    d[series][game]["history"] += "<font color=\""+server_color+"\">Game declared a draw.</font></td></tr></table>";
    add_history (series, game, "Tied between "+drawers);

    return 0;
  } // 2.9C if tied

  if((d[series][game]["full"]>=d[series]["p_max"]) && !fail) {
// Surrender change!  APG
    int n_winners = 0;
    string winner = "";

    foreach(indices(d[series][game]["p"]), ixa) {
      if (n_winners != 0)
        winner += ", ";
      p[ixa]["missive"]+="<!--G:winn-->You have won "+series+" "+game+"<br>";
      if (d[series][game]["bridier"] && d[series][game]["rank_table"]) {
        p[ixa]["missive"] += d[series][game]["rank_table"];
      }
      p[ixa]["wins"]++;
      winner += ixa;
// Surrender change!  APG
      n_winners++;
    }
//    d[series][game]["history"] += "</tr></table></td></tr></table>";
    d[series][game]["history"] += "Game won by "+winner+"</td></tr></table>";

// Surrender change!  APG
    if (n_winners == 1) {
      foreach(indices(d[series][game]["s"]), ixa) {
        if (d[series][game]["s"][ixa]["home"] != winner && d[series][game]["s"][ixa]["home"] != "") {
          p[winner]["kills"]++;
// in case of multihomes
          foreach(indices(d[series][game]["s"]), ixb) {
            if (d[series][game]["s"][ixb]["home"]==d[series][game]["s"][ixa]["home"])
              d[series][game]["s"][ixb]["home"]="";
          }
        }
      }
    }

    add_history (series, game, "Won by "+winner);

    return 0;
  } // 2.9C if !fail

  if(!sizeof(d[series][game]["p"]) && d[series][game]["full"]) {
// 3 i think i need to change this to not check the spawn option and just rely on count
    //if(!d[series][game]["spawn"]) spawn_game(series);
    spawn_game(series);
//    d[series][game]["history"] += "<tr>The game ended tragically.</tr></table></td></tr></table>";
    d[series][game]["history"] += "<font color=\""+server_color+"\"The game ended tragically.</font></td></tr></table>";
    add_history ( series, game, "Tragedy" );
    return 0;
  } // 2.9C if spawn

// 3 check to see what kind of spawn option we are using
  if((d[series][game]["u_cnt"] >= close_game &&
      d[series][game]["full"] < d[series]["p_max"])) {
    d[series][game]["full"]=d[series]["p_max"];
    if (d[series]["spawn_first"]==0) {
		spawn_game(series);
	} // if
  } // 2.9C spawn a new game since we are now closed

//  d[series][game]["history"] += "</table></td></tr>";
  d[series][game]["history"] += "</td></tr>";

  foreach(indices(d[series][game]["p"]), ixa) {
    d[series][game]["p"][ixa]["full_map"]="";
  }

  return 1;
} // update_game

int check_for_game(string series, string game)
{
  if(!mappingp(d[series])) return 0;
  if(!mappingp(d[series][game])) return 0;
  return 1;
}

void check_for_updates()
{
  string series, game, day;
  int hour;
  int endgame;

  foreach(indices(d), series)
    foreach(indices(d[series]), game) {
      if(!mappingp(d[series][game])) continue;
      if(!d[series][game]["p"] || !sizeof(d[series][game]["p"])) continue;

      day = ctime(time())[0..2];

      if((d[series]["weekend"] == 0 && (day == "Sat" || day == "Sun")) ||
          d[series][game]["full"]  < 2)  {
        // It's a weekend or only one player. This means
        // no update and time to update should be set to maximum time.

        d[series][game]["u_last"] = time()-1;
        d[series][game]["u_first"] = time() + d[series]["u_delay"] + d[series]["u_time"];

      } else {
        // 2.9 put the time limit in
        if ((time()-d[series][game]["u_first"])>d[series]["time_limit"]) {
          d[series][game]["u_time"] = d[series]["over_time"];
        } // if time limit exceeded

        if(time()>(d[series][game]["u_cnt"]?(d[series][game]["u_last"]+d[series][game]["u_time"]):(d[series][game]["u_first"]))) {
          hour = (int)ctime(time())[11..12];
          if(!(hour<d[series]["from"] || hour>d[series]["to"])) {

            // fix for no re-spawning of maxed out game series
            endgame = update_game(series, game);                                  // check for respawn of max games
//            if ( (endgame == 0) && (d[series]["count"] == (d[series]["max"]-1)) )  // maximum count game has just ended
//              spawn_game(series);                                                 // no new game would have been started
//  keeps respawing until max empty games listed   6/4/99
          }
        }
      }
    }
}


int end_turn_update(string series, string game)
{
  string ixa;
  int endgame;

  if (d[series][game]["full"] < d[series]["p_max"]) return 2;
  foreach(indices(d[series][game]["p"]), ixa) {
    if(!d[series][game]["p"][ixa]["end"]) return 2;
  }

// i commented this (3 lines) out because it didn't make sense -- Del
//    if(time() < d[series][game]["u_first"]) {
//      d[series][game]["u_first"]=time();
//    } // if

// fix for no re-spawning of maxed out game series
  endgame = update_game(series, game);                                  // check for respawn of max games
//  if ( (endgame == 0) && (d[series]["count"] == (d[series]["max"]-1)) )  // maximum count game has just ended
//    spawn_game(series);                                                 // no new game would have been started
//  keeps respawing until max empty games listed   6/4/99

  return endgame;                                                       // need return value for testing;
}


// 2.9 admin add login message to top of form
mapping login_form()
{
  return (["data":
    header("Login","")+
    "<script language=\"JavaScript\">\n"+
    "<!--\n"+
    "function hlppath(){ window.open('"+help_url+"', '', 'scrollbars,height=300,width=620')}\n"+
    "function faqpath(){ window.open('"+faq_url+"', '', 'scrollbars,height=300,width=620')}\n"+
    "function etiqHelp(){ window.open( '"+html_dir+"/etiquette.html', '', 'scrollbars,height=300,width=620')}\n"+
    "function nameHelp(){ window.open('"+html_dir+"/loginhelp.html', '', 'height=240,width=200')\n}"+
    "function passHelp(){ window.open('"+html_dir+"/passhelp.html', '', 'height=300,width=200')}\n"+
    "function credits(){ window.open('"+html_dir+"/credits.html', '', 'scrollbars,height=300,width=540')}\n"+
    "//--></script></head>"+
    pageheader( QUERY(servername), "" )+
    "<form><font color=\"#000000\">"+
    "<input type=\"button\" value=\"SC FAQ\" name=\"Faq\" onClick=\"faqpath()\">&nbsp;&nbsp;"+
    "<input type=\"button\" value=\"SC Etiquette\" name=\"Etiq\" onClick=\"etiqHelp()\">&nbsp;&nbsp;"+
    "<input type=\"button\" value=\"Help\" name=\"Help\" onClick=\"hlppath()\">&nbsp;&nbsp;"+
    "<input type=\"button\" value=\"Credits\" name=\"Credits\" onClick=\"credits()\">"+
    "</font></form></center>"+
    "<img src=\""+theme_dir("")+"/spacerule.gif\" height=10 width=100%><br clear><p></p>"+
    "<form method=post>"+
    "<div align=right>"+
    "<table width=100%>"+
    "<tr><td width=60% align=left><b><font size=+1>Greetings mighty Warlord, your Empire awaits...</font></td>"+
      "<td align=right>Player Name:</b></td>"+
      "<td><input type=text name=\"name\" size=20 maxlength=20></td>"+
      "<td><input type=\"button\" value=\"Info\" name=\"Info\" onClick=\"nameHelp()\"></td></tr>"+
    "<tr><td align=left><b><font size=+1>Please sign in.</font></td><td align=right>Password:</b></td>"+
      "<td><input type=password  name=\"pass\" size=20 maxlength=20></td>"+
      "<td><input type=\"button\" value=\"Info\" name=\"Info\" onClick=\"passHelp()\"></td></tr>"+
    "<tr><td></td><td></td><td><input type=submit name=\"action\" value=\"Login\"></td></tr>"+
    "</table></div>"+
    "</form><br>&nbsp;<br>&nbsp;<br>"+
    "<font color="+MESSG_COLOR+">"+log_msg+"</font>"+
     QUERY(motd)+footer(""),
    "type":"text/html"]);
}

mapping login_failed(string message)
{
  return (["data":
          header("Login Failure", "")+
          pageheader("Login Failed", "")+
          "<p><b>"+message+" Please try again.</b>"+
          "<p><img src=\""+theme_dir("")+"/spacerule.gif\" height=10 width=100%><p>"+
          "<div align=right>"+
          "<table width=100%>"+
          "<tr><td width=60% align=left><b><font size=+1>Pardon Warlord, which Empire??</font></td>"+
          "<td align=right>Player Name:</B></TD>"+
          "<td><input type=text name=\"name\" size=20 maxlength=20></TD>"+
          "<td></td><td><input type=\"button\" value=\"Info\" name=\"Info\" onClick=\"nameHelp()\"></td></tr>"+
          "<tr><td align=left><b><font size=+1>Please sign in.</font></td><td align=right>Password:</b></td>"+
          "<td><input type=password  name=\"pass\" size=20 maxlength=20></TD>"+
          "<td></td><td><input type=\"button\" value=\"Info\" name=\"Info\" onClick=\"passHelp()\"></td></tr>"+
          "<tr><td></td><td align=\"right\" colspan=2><input type=submit name=\"action\" value=\"Login\"></TD><td></td></TR>"+
          "</table></div>"+
          footer(""),
          "type":"text/html"]);
}

// utility function for making numbers appear nicely - anyone know a better way to do this?
int required_precision(float d)
{
  int i;
  float l;

  for (i = 1; i < 6; i++) {
    l = d * pow (10.0, (float) i);
    if (floor (l) == l)
      break;
  }
  return i;
}

string series_info(string series, string name, int full)
{
  string ag_string, min_string, fuel_string;
  string u_time, over_time, time_limit, weekend;
  string SCOLOR;
  string up_text, layout, ratios;
  string systems, resources, access, ally, playtime;
  string desc, s_info, s_sep;
  string s_font, e_font;
  string bridier;

  if (p[name]["flags"] & flags_sepdata) {
    s_font="<font color="+DATA_COLOR+">";
    e_font="</font>";
  } else {
    s_font=e_font="";
  }

  SCOLOR = (string)d[series]["color"];
  if(search(SCOLOR, "#")!=0) {
    SCOLOR = server_color;
    d[series]["color"] = SCOLOR;
  }

  // series timing details
  if(d[series]["u_time"] % 3600 == 0)
    u_time = (d[series]["u_time"]/3600)+" hours";
  else
    u_time = (d[series]["u_time"] / 60)+" minutes";

  // 2.9 set the overtime and time limit
  if(d[series]["over_time"] % 3600 == 0)
    over_time = (d[series]["over_time"]/3600)+" hours";
  else
    over_time = (d[series]["over_time"] / 60)+" minutes";

  if(d[series]["time_limit"] % 3600 == 0)
    time_limit = (d[series]["time_limit"]/3600)+" hours";
  else
    time_limit = (d[series]["time_limit"] / 60)+" minutes";

  if (over_time == u_time)
    up_text = u_time;
  else
    up_text = u_time+" for the first "+time_limit+" then every "+over_time;

  // game availability                              ...  is this really needed????
  if ( (d[series]["to"] - d[series]["from"]) >= 23 )
    playtime = "";
  else
    playtime = ", available from "+d[series]["from"]+" until "+d[series]["to"]+" hours";

  if(d[series]["weekend"])
    weekend="";
  else
    weekend=", no weekend updates";

  // systems info
  if( d[series]["s_min"] )
    systems = (d[series]["s_min"] == d[series]["s_max"] ? d[series]["s_min"] :
              "between " + d[series]["s_min"] + " and " + d[series]["s_max"]);
  else
    systems = d[series]["s_add"];

  // resources
  resources = "average Min/Fuel/Ag resources "+d[series]["avg_min"];
  if (d[series]["range_min"] > 0) {
    resources += "-"+(d[series]["avg_min"]+d[series]["range_min"]);
  }
  resources += "/"+d[series]["avg_fuel"];
  if (d[series]["range_fuel"] > 0) {
    resources += "-"+(d[series]["avg_fuel"]+d[series]["range_fuel"]);
  }
  resources += "/"+d[series]["avg_ag"];
  if (d[series]["range_ag"] > 0) {
    resources += "-"+(d[series]["avg_ag"]+d[series]["range_ag"]);
  }

  // game entry
  if( d[series]["wins"] == 0 && d[series]["winmax"] < 0 )
    access = "Anyone";
  else if( d[series]["wins"] == d[series]["winmax"] )
    access = d[series]["wins"] +" wins";
  else if( d[series]["winmax"] < 0 )
    access = d[series]["wins"] +" or more wins";
  else
    access = "Between "+d[series]["wins"]+" and "+ d[series]["winmax"] +" wins";

  // alliances
  if (d[series]->blood == 0) {
    ally = "Truce/Trade and ";
    if (d[series]["max_allies"] > 1)
      ally += d[series]["max_allies"] + " alliances allowed";
    else
      ally += "1 alliance allowed";
  }
  else if (d[series]->blood == 1)
    ally = "Truce, trade or alliances not allowed";
  else
    ally = "Truce/Trade only, no alliances allowed";

  // layout
  if (d[series]->layout == 1) {
    layout="Random layout";
  } else {
    if (d[series]["min_ag"] > 0) {
      layout="Multihome Mirrored layout with "+d[series]["min_ag"]+" minimum starting Ag";
    } else {
      layout="Mirrored layout";
    }
  }

  // ratios
  ratios=sprintf("%.*f max Ag ratio, %.*f tech advance",
    required_precision(d[series]["max_ag"]), d[series]["max_ag"],
    required_precision(d[series]["t_mult"]), d[series]["t_mult"]);

  // separator :)
  if (glist_format) {
    s_sep="<br>";
  } else {
    s_sep=", ";
  }

  bridier=(enable_bridier?(d[series]["bridier"]?"<b>Bridier Ranking Series.</b> ":""):"");

  // brief description
  s_info=s_font+bridier+"Updates every "+up_text+playtime+weekend+s_sep+
         d[series]["p_max"]+" players with "+systems+" systems per player"+s_sep+
         resources+s_sep+
         ratios+s_sep+
         ally+", "+layout+s_sep+
         access +" can join."+e_font;

  if (d[series]["range_ag"] == 0) {
    ag_string = "<td> Avg System Ag: </td><td>"+s_font+d[series]["avg_ag"]+e_font+"</td>";
  } else {
    ag_string = "<td> Avg System Ag between: </td><td>"+s_font+d[series]["avg_ag"]+" and "+
      (d[series]["avg_ag"]+d[series]["range_ag"])+e_font+"</td>";
  } // if

  if (d[series]["range_min"] == 0) {
    min_string = "<td> Avg System Min: </td><td>"+s_font+d[series]["avg_min"]+e_font+"</td>";
  } else {
    min_string = "<td> Avg System Min between: </td><td>"+s_font+d[series]["avg_min"]+" and "+
      (d[series]["avg_min"]+d[series]["range_min"])+e_font+"</td>";
  } // if

  if (d[series]["range_fuel"] == 0) {
    fuel_string = "<td> Avg System Fuel: </td><td>"+s_font+d[series]["avg_fuel"]+e_font+"</td>";
  } else {
    fuel_string = "<td> Avg System Fuel between: </td><td>"+s_font+d[series]["avg_fuel"]+" and "+
      (d[series]["avg_fuel"]+d[series]["range_fuel"])+e_font+"</td>";
  } // if

  if (full==0) {
    desc = d[series]["desc"]+"</td></tr><tr><td align=center><input type=submit name=\"l:"+series+
      ":detail\" value=\""+series+" Detail\"></td><td></td><td colspan=5>"+s_info;
  } else {
    int cloaker, stargate, engineer, jumpgate, carrier, builder, morpher;
    string tech_list = d[series]["r_tech"]+d[series]["u_tech"]+d[series]["d_tech"];

    cloaker = stargate = engineer = jumpgate = carrier = builder = morpher = 0;
    foreach(tech_list/" ", string p_tech) {
      if (p_tech == "Cloaker")
        cloaker = 1;
      if (p_tech == "Stargate")
        stargate = 1;
      if (p_tech == "Engineer")
        engineer = 1;
      if (p_tech == "Jumpgate")
        jumpgate = 1;
      if (p_tech == "Carrier")
        carrier = 1;
      if (p_tech == "Builder")
        builder = 1;
      if (p_tech == "Morpher")
        morpher = 1;
    }
    desc = (full==1?d[series]["full_desc"]+"</td></tr>":"");
    desc +=
      ("<tr>"+(full==2?"":
       "<td align=center><b><font color="+SCOLOR+">"+series+" series</font></b></td>"+
       "<td><img src="+d[series]["simage"]+"></td>")+
       "<td colspan="+(full==2?"6":"5")+">"+s_info+"<br>"+
       "<table>"+
//       "<tr><td>Restricted access: </td><td>"+s_font+(d[series]["restricted"]?"Yes":"No")+e_font+"</td></tr>"+
       "<tr><td> Restricted Tech: </td><td>"+s_font+d[series]["r_tech"]+e_font+"</td></tr>"+
       "<tr><td> Unrestricted Tech: </td><td>"+s_font+d[series]["u_tech"]+e_font+"</td></tr>"+
       "<tr><td> Developed Tech: </td><td>"+s_font+d[series]["d_tech"]+e_font+"</td></tr>"+
       ((d[series]["r_tech"]!="")?
       "<tr><td> Allowed number of Restricted: </td><td>"+s_font+d[series]["ar_tech"]+e_font+"</td></tr>"+
       "<tr><td> Number of trade-ins: </td><td>"+s_font+d[series]["tr_tech"]+e_font+"</td></tr>":"")+
       "</table><table width=100%>"+
       "<tr><td> Player Visibility: </td><td>"+s_font+(d[series]["visp"]?"Yes":"No")+e_font+"</td>");
     if (cloaker)
       desc+=
         ("<td> Cloakers built cloaked: </td><td>"+s_font+(d[series]["init_cloak"]?"Yes":"No")+e_font+"</td>");
     desc+=
       ("<td> Build Visibility: </td><td>"+s_font+(d[series]["vis"]?"Yes":"No")+e_font+"</td></tr>");
     if (stargate)
       desc+=
         sprintf("<tr><td> Stargate Range Divisor: </td><td>"+s_font+"%.*f"+e_font+"</td></tr>",
           required_precision(d[series]["stargate"]), d[series]["stargate"]);
     if (jumpgate)
       desc+=
         sprintf("<tr><td> Jumpgate Range Divisor: </td><td>"+s_font+"%.*f"+e_font+"</td></tr>",
           required_precision(d[series]["jumpgate"]), d[series]["jumpgate"]);
     desc+=
       ("<tr><td> Max Num Ships: </td><td>"+s_font+(d[series]["max_ship"]?d[series]["max_ship"]:"No Limit")+e_font+"</td>"+
         "<td> Max Number of Allies: </td><td>"+s_font+d[series]["max_allies"]+e_font+"</td>"+
         "<td> Surrender/Draw Allowed: </td><td>"+s_font+(d[series]["surrdraw"]?"Yes":"No")+e_font+"</tr>");
     desc+=
       sprintf("<tr><td> Initial Tech: </td><td>"+s_font+"%.*f"+e_font+"</td></tr>",
         required_precision(d[series]["init_tech"]), d[series]["init_tech"]);
     if (engineer)
       desc+=
         sprintf("<tr><td> Engineer Loss: </td><td>"+s_font+"%.*f"+e_font+"</td></tr>",
           required_precision(d[series]["eng_loss"]), d[series]["eng_loss"]);
     if (morpher)
       desc+=
         sprintf("<tr><td> Morpher Build Cost: </td><td>"+s_font+d[series]["morpher_build"]+e_font+"</td>"+
           "<td> Morpher Maint Cost: </td><td>"+s_font+d[series]["morpher_maint"]+e_font+"</td>"+
           "<td> Morpher Loss: </td><td>"+s_font+"%.*f"+e_font+"</td></tr>",
           required_precision(d[series]["morph_loss"]), d[series]["morph_loss"]);
     if (builder)
       desc+=
         ("<tr><td> Builder Build Cost: </td><td>"+s_font+d[series]["builder_build"]+e_font+"</td>"+
           "<td> Builder Maint Cost: </td><td>"+s_font+d[series]["builder_maint"]+e_font+"</td>"+
           "<td> Planet Build cost: </td><td>"+s_font+d[series]["pla_build"]+e_font+"</td></tr>");
     if (jumpgate)
       desc+=
         sprintf("<tr><td> Jumpgate Build Cost: </td><td>"+s_font+d[series]["jumpgate_build"]+e_font+"</td>"+
           "<td> Jumpgate Maint Cost: </td><td>"+s_font+d[series]["jumpgate_maint"]+e_font+"</td>"+
           "<td> Jumpgate Loss: </td><td>"+s_font+"%.*f"+e_font+"</td></tr>",
           required_precision(d[series]["jump_loss"]), d[series]["jump_loss"]);
     if (carrier)
       desc+=
         sprintf("<tr><td> Carrier Build Cost: </td><td>"+s_font+d[series]["carrier_build"]+e_font+"</td>"+
           "<td> Carrier Maint Cost: </td><td>"+s_font+d[series]["carrier_maint"]+e_font+"</td>"+
           "<td> Carrier Loss: </td><td>"+s_font+"%.*f"+e_font+"</td></tr>",
           required_precision(d[series]["car_loss"]), d[series]["car_loss"]);

     desc+=
       ("<tr>"+ag_string + min_string+fuel_string+"</tr>"+
       "<tr><td> Home System Ag: </td><td>"+s_font+d[series]["home_ag"]+e_font+"</td>"+
       "<td> Home System Min: </td><td>"+s_font+d[series]["home_min"]+e_font+"</td>"+
       "<td> Home System Fuel: </td><td>"+s_font+d[series]["home_fuel"]+e_font+"</td></tr>"+
       "<tr><td> Minimum Pop to Build: </td><td>"+s_font+d[series]["min_build"]+e_font+"</td>"+
       "<td> Blind until Start: </td><td>"+s_font+(d[series]["blind"]?"Yes":"No")+e_font+"</td>"+
       "</tr></table></td></tr>");
  } // if
  return desc;
}

int glsort_update_a (string a, string b) {
  return (d[a]["u_time"] > d[b]["u_time"]);
}

int glsort_update_d (string a, string b) {
  return (d[a]["u_time"] < d[b]["u_time"]);
}

int glsort_tech_a (string a, string b) {
  return (d[a]["t_mult"] > d[b]["t_mult"]);
}

int glsort_tech_d (string a, string b) {
  return (d[a]["t_mult"] < d[b]["t_mult"]);
}

int glsort_play_a (string a, string b) {
  return (d[a]["p_max"] > d[b]["p_max"]);
}

int glsort_play_d (string a, string b) {
  return (d[a]["p_max"] < d[b]["p_max"]);
}

int glsort_sys_a (string a, string b) {
  return ((d[a]["s_max"]+d[a]["s_min"])/2 > (d[b]["s_max"]+d[b]["s_min"])/2);
}

int glsort_sys_d (string a, string b) {
  return ((d[a]["s_max"]+d[a]["s_min"])/2 < (d[b]["s_max"]+d[b]["s_min"])/2);
}

int glsort_rsrc_a (string a, string b) {
  int ra = (d[a]["avg_ag"]+d[a]["avg_min"]+d[a]["avg_fuel"]+((d[a]["range_ag"]+d[a]["range_min"]+d[a]["range_fuel"])/2)); 
  int rb = (d[b]["avg_ag"]+d[b]["avg_min"]+d[b]["avg_fuel"]+((d[b]["range_ag"]+d[b]["range_min"]+d[b]["range_fuel"])/2)); 
  return ra > rb;
}

int glsort_rsrc_d (string a, string b) {
  int ra = (d[a]["avg_ag"]+d[a]["avg_min"]+d[a]["avg_fuel"]+((d[a]["range_ag"]+d[a]["range_min"]+d[a]["range_fuel"])/2)); 
  int rb = (d[b]["avg_ag"]+d[b]["avg_min"]+d[b]["avg_fuel"]+((d[b]["range_ag"]+d[b]["range_min"]+d[b]["range_fuel"])/2)); 
  return ra < rb;
}

int glsort_dipl_a (string a, string b) {
  int ra=d[a]["blood"];
  int rb=d[b]["blood"];

  if (!ra) ra=d[a]["max_allies"]+2;
  if (!rb) rb=d[b]["max_allies"]+2;
  return ra > rb;
}

int glsort_dipl_d (string a, string b) {
  int ra=d[a]["blood"];
  int rb=d[b]["blood"];

  if (!ra) ra=d[a]["max_allies"]+2;
  if (!rb) rb=d[b]["max_allies"]+2;
  return ra < rb;
}

static array glsort_func = ({
  ({glsort_update_a, glsort_update_d}),
  ({glsort_tech_a, glsort_tech_d}),
  ({glsort_play_a, glsort_play_d}),
  ({glsort_sys_a, glsort_sys_d}),
  ({glsort_rsrc_a, glsort_rsrc_d}),
  ({glsort_dipl_a, glsort_dipl_d})
});

// 2.9C substituted 'series' for 'a' for readability (and then 'game_num' for 'b')

// 'full' refers to the level of detail for each game
//   0 means minimal detail - the standard Game List.
//   1 means full detail - the Detailed Game List.
// 'type' refers to which games are shown.
//   0 means all games, as in the traditional Game Lists.
//   1 means all the games the empire is currently playing.
//   2 means all currently open (started) games (except those covered by 1)
//   3 means all new (spawned) games without players yet.

mapping game_list(string name, string c_pass, int full, int type, string message)
{
  int done;
  int series_full, avail;
  string series, b, game_num, index, players, first;
  string s_head, sl_list, sj_list, sn_list, l_list="", j_list="", n_list="", listing;
  string SCOLOR;
  string whole_list;
  string s_font, e_font;
  array ser_arr=({});
  int ser_ix, ser_n, i;

  int gbutn=0;                          // assume standard buttons
  if ( name != "" )
    gbutn = (p[name]["flags"] & flags_gbutton);

  if(!stringp(p[name]["glsort"])) p[name]["glsort"]="001020";

  if (p[name]["flags"] & flags_sepdata) {
    s_font="<font color="+DATA_COLOR+">";
    e_font="</font>";
  } else {
    s_font=e_font="";
  }

  foreach(indices(d), series) {
    ser_arr += ({series});
    ser_n++;
  }

  for (i = 2; i >= 0; i--) {
    int j, k, m, n;

    j = 2 * i;
    k = j + 1;
    m = (int) (p[name]["glsort"][j..j]);
    n = (int) (p[name]["glsort"][k..k]);
#if ROXEN_VER == "1.2"
    ser_arr = Array.sort_array(ser_arr,
#else
    ser_arr = sort_array(ser_arr,
#endif
        glsort_func[m][n]);
  }

  for (ser_ix = 0; ser_ix < ser_n; ser_ix++) {
    series = ser_arr[ser_ix];

    SCOLOR = (string)d[series]["color"];
    if(search(SCOLOR, "#")!=0) {
      SCOLOR = server_color;
      d[series]["color"] = SCOLOR;
    }

    // 2.9C series header
    s_head=("<tr><td align=center><b><font color="+SCOLOR+">"+series+" series</font></b></td>"+
            "<td><img src="+d[series]["simage"]+" alt=\"Series image\"></td>"+
            "<td colspan=5>"+series_info(series, name, full)+
            "</td></tr>");
/*
    // short game description info
    if (full==0)
      s_head += "<tr><td></td><td></td><td colspan=5>"+s_info+"</td></tr>";
*/

    sl_list="";
    sj_list="";
    sn_list="";
    series_full = 1;                  // assume series is full (max games)
    avail = 1;                        // assume series is available
    foreach(indices(d[series]), b) {

      done=0;
      switch(b)  {
        case DBSTUFF :                       // ignore series parameters except....
          break;
        default:       
          game_num = b;                      // ... the game number
          int brid_bar = 0;
          if(!d[series][game_num]["full"]) {
            if(d[series][game_num]["u_first"]-time() < d[series]["u_time"]) { // not sure if this is necessary now
              d[series][game_num]["u_first"] = time() + d[series]["u_delay"] + d[series]["u_time"];
            }
            first = "First update after 2 players join";
          } else if (d[series][game_num]["full"]==1) {
            first = "First update when another player has joined";
          } else {
            if(d[series][game_num]["u_cnt"] == 0)
              first = "First update in "+time_conv(d[series][game_num]["u_first"]-time());
            else
              first = "Update in "+time_conv(d[series][game_num]["u_last"]+d[series][game_num]["u_time"]-time());
          }

          //check for ongoing games
          if(!d[series][game_num]["p"]) {
            perror(sprintf("Game: %O (%O) has no players...removing it.\n",series,game_num,));

            d[series]["count"]--;
            d[series] -= ([game_num:""]);
            break;
          }

          // 2.9C I don't understand why this is iterating through all
          // of the player names if they are looking for a specific one
          // added player listing
          listing = "";
          foreach(indices(d[series][game_num]["p"]), index) {
            if (index!=name) {
              if (enable_bridier && d[series][game_num]["bridier"]==1) {
                mapping nbw, nbl;
                int win, lose;
                string brid_pot;
                if (d[series][game_num]["p"][index]["bridier_index"]) {
                  if (mappingp(d[series][game_num]["p"][name])) {
                    nbw = calc_bridier (d[series][game_num]["p"][name]["bridier_rank"], d[series][game_num]["p"][name]["bridier_index"],
                        d[series][game_num]["p"][index]["bridier_rank"], d[series][game_num]["p"][index]["bridier_index"]);
                    nbl = calc_bridier (d[series][game_num]["p"][index]["bridier_rank"], d[series][game_num]["p"][index]["bridier_index"],
                        d[series][game_num]["p"][name]["bridier_rank"], d[series][game_num]["p"][name]["bridier_index"]);
                    win = nbw ["wrank"] - d[series][game_num]["p"][name]["bridier_rank"];
                    lose = d[series][game_num]["p"][name]["bridier_rank"] - nbl ["lrank"];
                    brid_pot = "<td></td><td></td><td>Bridier Potential - Win: "+s_font+"+"+win+e_font+
                               " Lose: "+s_font+"-"+lose+e_font+"</td><td></td></tr>\n";
                  } else {
                    nbl = calc_bridier (d[series][game_num]["p"][index]["bridier_rank"],
                        d[series][game_num]["p"][index]["bridier_index"], p[name]["bridier_rank"], p[name]["bridier_index"]);
                    if ((nbl ["wrank"] - d[series][game_num]["p"][index]["bridier_rank"]) < d[series][game_num]["challenge"])
                      brid_bar = 1;
                    nbw = calc_bridier (p[name]["bridier_rank"], p[name]["bridier_index"],
                        d[series][game_num]["p"][index]["bridier_rank"], d[series][game_num]["p"][index]["bridier_index"]);
                    win = nbw ["wrank"] - p[name]["bridier_rank"];
                    // lose = p[name]["bridier_rank"] - nbl ["lrank"];
                    brid_pot = "<td></td><td></td><td>Bridier Win Potential: "+s_font+"+"+win+e_font+"</td><td></td></tr>\n";
                  }
                }
                listing += "<tr><td></td><td></td><td></td>";
                if (d[series]["visp"]==1) {       //2.9admin added color coding for clarity
                  listing += "<td align=center><font color="+OPPONENT_COLOR+">"+index+"</font></td>";
                } else {
                  listing += "<td></td>";
                } //if
                if (d[series][game_num]["p"][index]["bridier_index"]) {
                  listing += brid_pot;
                }
              } else {
                if (d[series]["visp"]==1) {       //2.9admin added color coding for clarity
                  listing += ("<tr><td></td><td></td><td></td><td align=center>"+
                            "<font color="+OPPONENT_COLOR+">"+index+
                            //" of " + p[index]["sponsor"]+
                            //" of " + p[p[index]["sponsor"]]["sponsor"]+
                            "</font></td><td></td><td></td><td></td></tr>\n");
                } //if
              } // if bridier
              continue;
            } // if not this player

            if(d[series][game_num]["full"] < d[series]["p_max"]) {
              players = d[series][game_num]["full"]+" of "+d[series]["p_max"]+" players";
			} else {
              players = "Full game, "+sizeof(d[series][game_num]["p"])+" players left.";
			} // if

            int idle= d[series][game_num]["u_cnt"]-d[series][game_num]["p"][index]["l_up"];
            string ready=(idle>2)?"<font color="+IDLE_COLOR+"> <b>idle</b></font> ":(d[series][game_num]["p"][index]["end"]?"<font color="+READY_COLOR+"> ready </font> ":"<font color="+NOT_READY_COLOR+"> <b>not ready</b></font> ");

            //2.9admin added color coding for clarity
            sl_list+=("<tr><td></td><td></td><td align=center><b><font color="+OPPONENT_COLOR+">Game "+game_num+"</font></b></td>"+
                      "<td align=center><font color="+OPPONENT_COLOR+">"+players+"</font></td>"+
                      "<td align=center><font color="+OPPONENT_COLOR+">"+d[series][game_num]["u_cnt"]+" updates</font></td>"+
                      "<td align=right><input name=\"l:"+series+":"+game_num+"\" type="+
                      (gbutn?"image src=\""+theme_dir(name)+"/login.gif\" alt=Login>"
                            :"submit  value=\"Login\">")+
                      "</td><td>"+first+". You are "+ready+
                      "for an update.</font></td></tr>");

            series_full = 0;
            done=1;
          } // 2.9C foreach player

          // 2.9 add the other players who are playing
          if (done==1) {
			sl_list += listing;
            listing = "";
		  } // if

          // 2.9c if there are none that we are logged into and
          // this player is elegible for this series
// commented this out for now
// put back listing based upon wins/sponsors 29th July '98
          if((done==0) && (p[name]->wins >= d[series]->wins) &&
                          ((p[name]->wins <= d[series]->winmax) || (d[series]->winmax < 0)) ){
//                     && (search(d[series]["invited"], p[p[name]["sponsor"]]["sponsor"])!=-1)) {
//          if (done==0) {
            if (d[series][game_num]["full"]==0) {

              sn_list+=("<tr><td align=center>"+
                        ((enable_bridier && d[series][game_num]["bridier"])?
                        "&nbsp;&nbsp;Challenge:&nbsp;&nbsp;<select name=\"c:"+series+":"+game_num+"\">"+
                        "<option selected>0<option>1<option>2<option>3<option>4"+
                        "<option>5<option>6<option>7<option>8<option>9<option>10</select>":"")+
                        "</td><td></td><td align=center><b><font color="+OPPONENT_COLOR+">Game "+game_num+"</font></b></td>"+
                        "<td align=center><font color="+OPPONENT_COLOR+">"+d[series][game_num]["full"]+" of "+
                        d[series]["p_max"]+" players</font></td>"+
                        "<td align=center><font color="+OPPONENT_COLOR+">"+d[series][game_num]["u_cnt"]+" updates</font></td>"+
                        "<td align=right>"+
                        "<input name=\"l:"+series+":"+game_num+"\""+
                        " type="+(gbutn?"image src=\""+theme_dir(name)+"/start.gif\" alt=Start":
                        "submit  value=\"Start\"")+"></td><td><font color="+READY_COLOR+">"+first+".</font></td></tr>");

              series_full = 0;
              listing = "";
            } else if ((d[series][game_num]["full"] < d[series]["p_max"]) &&
                (brid_bar == 0 || d[series][game_num]["gpass"] && d[series][game_num]["gpass"]!="")) {
              string pass_alert="";
//            if(d[series][game_num]["full"]==1 && d[series][game_num]["gpass"] && d[series][game_num]["gpass"]!="") {
              if(d[series][game_num]["gpass"] && d[series][game_num]["gpass"]!="") {
                pass_alert="<br><font size=-1 color="+NOT_READY_COLOR+">Password-Protected</font>";
              }
              sj_list+=("<tr><td></td><td></td><td align=center><b><font color="+OPPONENT_COLOR+">Game "+game_num+"</font></b></td>"+
                        "<td align=center><font color="+OPPONENT_COLOR+">"+d[series][game_num]["full"]+" of "+
                        d[series]["p_max"]+" players"+pass_alert+"</font></td>"+
                        "<td align=center><font color="+OPPONENT_COLOR+">"+d[series][game_num]["u_cnt"]+" updates</font></td>"+
                        "<td align=right><input name=\"l:"+series+":"+game_num+"\""+
                        " type="+(gbutn?"image src=\""+theme_dir(name)+"/join.gif\" alt=Join>"
                                       :"submit  value=\"Join\">")+
                        "</td><td><font color="+READY_COLOR+">"+first+".</font></td></tr>");

              series_full = 0;
              sj_list += listing;
              listing = "";
            } // 2.9C if full
          }  else {
            avail = 0;           // not eligible for this series of games
          } // 2.9C if done
        } // 2.9C switch
      } // switch series

    // if series is full, remove join option from list but display details
    if ( avail && series_full ) {
        sn_list+=("<tr><td></td><td></td>"+
                   "<td align=center colspan=3><font color="+OPPONENT_COLOR+">Game unavailable.</font></td>"+
                   "<td></td><td><font color="+READY_COLOR+">Maximum games in progress</font></td>"+
                   "</tr>");
    } //  next series

    if(sl_list != "")
      l_list += s_head+sl_list;
    if(sj_list != "")
      j_list += s_head+sj_list;
    if(sn_list != "")
      n_list += s_head+sn_list;
  } // 2.9C foreach series

  string head_string;

  switch (type) {
  case 1:
    head_string = "Current Game List";
    whole_list = "<p><img src=\""+theme_dir(name)+"/spacerule.gif\" height=10  width=100%><p>";
    if (l_list == "") {
      whole_list+= 
        "<center><b>You are not currently in any games.</b></center>";
    } else {
      whole_list+=
        "<table>"+l_list+"</table>";
    }
    break;
  case 2:
    head_string = "Open Game List";
    whole_list = "<p><img src=\""+theme_dir(name)+"/spacerule.gif\" height=10  width=100%><p>";
    if (j_list == "") {
      whole_list+= 
        "<center><b>Sorry, there are no open games for you to join.</b></center>";
    } else {
      whole_list+=
        "<table>"+j_list+"</table>";
    }
    break;
  case 3:
    head_string = "New Game List";
    whole_list = "<p><img src=\""+theme_dir(name)+"/spacerule.gif\" height=10  width=100%><p>";
    if (n_list == "") {
      whole_list+= 
        "<center><b>Sorry, there are no new games you can start.</b></center>";
    } else {
      whole_list+=
        "<table>"+n_list+"</table>";
    }
    break;
  default:
    head_string = "Game List";
    whole_list="";
    if (l_list == "" && j_list == "" && n_list == "") {
      whole_list = 
        "<p><img src=\""+theme_dir(name)+"/spacerule.gif\" height=10  width=100%><p>"+
        "<center><b>Sorry, there are no games in progress at this time.</b></center>";
    } else {
      if (l_list != "") {
        whole_list+=
          "<p><img src=\""+theme_dir(name)+"/spacerule.gif\" height=10  width=100%><p>"+
          "<table>"+l_list+"</table>";
      }
      if (j_list != "") {
        whole_list+=
          "<p><img src=\""+theme_dir(name)+"/spacerule.gif\" height=10  width=100%><p>"+
          "<table>"+j_list+"</table>";
      }
      if (n_list != "") {
        whole_list+=
          "<p><img src=\""+theme_dir(name)+"/spacerule.gif\" height=10  width=100%><p>"+
          "<table>"+n_list+"</table>";
      }
    }
    break;
  }
/*
          (l_list == "" && j_list == "" ?
           "<center><b>Sorry, there are no games in progress at this time.":
           (l_list == "" ?
            "<table>"+ j_list +"</table>":
            (j_list == "" ?
             "<table>"+ l_list +"</table>":
             ("<table>"+l_list+"</table>"
              "<p><img src=\""+theme_dir(name)+"/spacerule.gif\" height=10  width=100%><p>"
              "<table>"+j_list+"</table>"))))
*/

  // check for frames option
//  if (p[name]["flags"] & useframe) {
  if (name == "Frame") {
    return (["data":
          header(head_string, name)+
/*          "<a target=head></a>"+
          pageheader(head_string+" for "+name, name)+
          "<input type=hidden name=\"name\" value=\""+name+"\">"+
          "<input type=hidden name=\"c_pass\" value=\""+c_pass+"\">"+
          "<input type=hidden name=\"action\" value=\"g_list\">"+
          "<p><input type=submit name=\"l:game\" value=\"Game List\">"+
          "<input type=submit name=\"l:gamefull\" value=\"Detailed Game List\">"+
          "<input type=submit name=\"l:prof\" value=\"Edit Profile\">"+
          "<input type=submit name=\"l:stat\" value=\"Stat Viewer\">"+
          "<input type=submit name=\"l:hist\" value=\"History Viewer\">"+
          "<input type=submit name=\"l:exit\" value=\"Exit\">"
          "<br><b>Local time and date: "+ ctime(time())+"</b>"+
//          "<p><img src=\""+theme_dir(name)+"/spacerule.gif\" height=10  width=100%><p>"+
*/
          "<a target=main></a>"+
          disp_g_missive(name)+message+
//          "</center>"+
          whole_list
          +footer(name),
          "type":"text/html"]);
  }

  // standard game list

  return (["data":
          header(head_string, name)+
          "<input type=hidden name=\"name\" value=\""+name+"\">"+
          "<input type=hidden name=\"c_pass\" value=\""+c_pass+"\">"+
          "<input type=hidden name=\"action\" value=\"g_list\">"+
          pageheader(head_string+" for "+name, name)+"<p>"+
          (gbutn?
            (split_gl?
              "<input type=image src=\""+theme_dir(name)+"/game.gif\" name=\"l:gameown\" alt=\"Your Games\">"+
              "<input type=image src=\""+theme_dir(name)+"/gameo.gif\" name=\"l:gameopen\" alt=\"Open Games\">"+
              "<input type=image src=\""+theme_dir(name)+"/gamen.gif\" name=\"l:gamenew\" alt=\"New Games\">"
            :
              "<input type=image src=\""+theme_dir(name)+"/game.gif\" name=\"l:game\" alt=\"Games\">")+
            // "<input type=image src=\""+theme_dir(name)+"/gamef.gif\" name=\"l:gamefull\" alt=\"Game description\">"+
            "<input type=image src=\""+theme_dir(name)+"/profile.gif\" name=\"l:prof\" alt=Profile>"+
            "<input type=image src=\""+theme_dir(name)+"/stats.gif\" name=\"l:stat\" alt=Stats>"+
            "<input type=image src=\""+theme_dir(name)+"/history.gif\" name=\"l:hist\" alt=History>"+
//            (p[name]["grp_admin"]?"<input type=image src=\""+theme_dir(name)+"/admin.gif\" name=\"l:gadmin\" alt=\"Game admin\">":"")+
            "<input type=image src=\""+theme_dir(name)+"/exit.gif\" name=\"l:exit\" alt=Exit>"
          : "<font color=\"#000000\">"+
            (split_gl?
              "<input type=submit name=\"l:gameown\" value=\"Game List\">"+
              "<input type=submit name=\"l:gameopen\" value=\"Open Game List\">"+
              "<input type=submit name=\"l:gamenew\" value=\"New Game List\">"
            :
              "<input type=submit name=\"l:game\" value=\"Game List\">")+
            // "<input type=submit name=\"l:gamefull\" value=\"Detailed Game List\">"+
            "<input type=submit name=\"l:prof\" value=\"Edit Profile\">"+
            "<input type=submit name=\"l:stat\" value=\"Stat Viewer\">"+
            "<input type=submit name=\"l:hist\" value=\"History Viewer\">"+
//            (p[name]["grp_admin"]?"<input type=submit name=\"l:gadmin\" value=\"Admin\">":"")+
            "<input type=submit name=\"l:exit\" value=\"Exit\"></font><br>")+
//          "<b>Local time and date: "+ ctime(time())+"</b>"+
          // 2.9 Fix -- disp_g_missive(name)+message+"</b></center>"+
          disp_g_missive(name)+"<br>"+message+

          whole_list

          +footer(name),
          "type":"text/html"]);
}

mapping series_detail(string name, string c_pass, string series, string message)
{
  int gbutn=0;                          // assume standard buttons
  if ( name != "" )
    gbutn = (p[name]["flags"] & flags_gbutton);

  return (["data":
          header(series+" Series Details", name)+
          "<input type=hidden name=\"name\" value=\""+name+"\">"+
          "<input type=hidden name=\"c_pass\" value=\""+c_pass+"\">"+
          "<input type=hidden name=\"action\" value=\"g_list\">"+
          pageheader(series+" Series Details for "+name, name)+"<p>"+
          (gbutn?
            (split_gl?
              "<input type=image src=\""+theme_dir(name)+"/game.gif\" name=\"l:gameown\" alt=\"Your Games\">"+
              "<input type=image src=\""+theme_dir(name)+"/gameo.gif\" name=\"l:gameopen\" alt=\"Open Games\">"+
              "<input type=image src=\""+theme_dir(name)+"/gamen.gif\" name=\"l:gamenew\" alt=\"New Games\">"
            :
              "<input type=image src=\""+theme_dir(name)+"/game.gif\" name=\"l:game\" alt=\"Games\">")+
            // "<input type=image src=\""+theme_dir(name)+"/gamef.gif\" name=\"l:gamefull\" alt=\"Game description\">"+
            "<input type=image src=\""+theme_dir(name)+"/profile.gif\" name=\"l:prof\" alt=Profile>"+
            "<input type=image src=\""+theme_dir(name)+"/stats.gif\" name=\"l:stat\" alt=Stats>"+
            "<input type=image src=\""+theme_dir(name)+"/history.gif\" name=\"l:hist\" alt=History>"+
//            (p[name]["grp_admin"]?"<input type=image src=\""+theme_dir(name)+"/admin.gif\" name=\"l:gadmin\" alt=\"Game admin\">":"")+
            "<input type=image src=\""+theme_dir(name)+"/exit.gif\" name=\"l:exit\" alt=Exit>"
          : "<font color=\"#000000\">"+
            (split_gl?
              "<input type=submit name=\"l:gameown\" value=\"Game List\">"+
              "<input type=submit name=\"l:gameopen\" value=\"Open Game List\">"+
              "<input type=submit name=\"l:gamenew\" value=\"New Game List\">"
            :
              "<input type=submit name=\"l:game\" value=\"Game List\">")+
            // "<input type=submit name=\"l:gamefull\" value=\"Detailed Game List\">"+
            "<input type=submit name=\"l:prof\" value=\"Edit Profile\">"+
            "<input type=submit name=\"l:stat\" value=\"Stat Viewer\">"+
            "<input type=submit name=\"l:hist\" value=\"History Viewer\">"+
//            (p[name]["grp_admin"]?"<input type=submit name=\"l:gadmin\" value=\"Admin\">":"")+
            "<input type=submit name=\"l:exit\" value=\"Exit\"></font><br>")+
//          "<b>Local time and date: "+ ctime(time())+"</b>"+
          // 2.9 Fix -- disp_g_missive(name)+message+"</b></center>"+
          disp_g_missive(name)+"<br>"+message+
          "<p><img src=\""+theme_dir(name)+"/spacerule.gif\" height=10  width=100%><p>"+
          "<table><tr><td colspan=2></td><td>"+series_info(series, name, 1)+"</table>"
          +footer(name),
          "type":"text/html"]);
}

// 2.9 code chunk to do mirror mappings

int* random_planet (mapping systems) {
  string *tmp = indices(systems);
  string *is;
  int choose = random (sizeof(systems));
  is = tmp[choose]/",";
  int *ret = allocate (2);
  ret[0] = (int) is[0];
  ret[1] = (int) is[1];
  return ret;
} // choose_planet

// 2.9 create a planet that is a reflection of the one given by x and y
string reflected_planet (int x, int y, int pivot, int dir_ns, int dir) {

  int diff;
  if (dir_ns == 1) {
    diff = y - (pivot + dir);
    diff = y - diff - diff;
    return x+","+diff;
  } else {
    diff = x - (pivot + dir);
    diff = x - diff - diff;
    return diff+","+y;
  } // if

} // reflected_planet

string reflect_jumps (int pivot, int dir_ns, int dir, string old_jumps) {

  string *jumps = old_jumps/" ";
  string planet, ret;
  string *coords = allocate(2);
  int ok = 0;
  foreach (jumps, planet) {
    coords = planet/",";
    if (ok == 0) {
      ret = reflected_planet ((int)coords[0], (int)coords[1], pivot, dir_ns, dir);
      ok = 1;
    } else {
      ret += " " + reflected_planet ((int)coords[0], (int)coords[1], pivot, dir_ns, dir);
    } // if
  } // foreach

  return ret;

} // reflect_jumps

mapping madd_chain(mapping chain, int max, int x, int y, int xin, int yin)
{
  if (sizeof(chain) >= max) return chain;
  //write ("entering with max = " + max + "x = "  + x + ", y = " + y + ", xin = " + xin + ",yin = " + yin + ", size = " + "\n");

  // this is our current location
  chain[x+","+y]=([]);

  // these are the possible jumps
  string *j_list=({((int) x+1)+","+y,
                   x+","+((int) y+1),
                   ((int) x-1)+","+y,
                   x+","+((int) y-1)});

  // how many links, at least 1 and at most 4
  int j=random(4)+1;
  int j_to;
  // dest used to be *int but it complained about bad type...
  string *dest;
  string *jump=({});

  // if i came from somewhere i can't go back there
  // but i need a link to there
  if(xin && yin) {
    j_list-=({j_list[search(j_list, xin+","+yin)]});
    jump=({xin+","+yin});
    j--;
  }

  // choose j other jumps
  while(j) {
    j_to=random(sizeof(j_list));
    if(search(jump, j_list[j_to])==-1) {
      jump+=({j_list[j_to]});
      j--;
    }
  }

  // put in our choices
  string here = x+","+y;
  chain[here]["jump"]=jump*" ";

  // check all of these to see if we can go in that direction
  for(j=0; j<sizeof(jump); j++) {
    if(search(indices(chain), jump[j])!=-1) continue;
    dest =jump[j]/",";
    chain=madd_chain(chain, max, (int) dest[0], (int) dest[1], x, y);
  } // for


  return chain;
}

void border_crossing (mapping systems, string here, string there) {
  if ((search(indices(systems), there)!=-1) &&   // it exists
      (search(systems[here]["jump"]/" ", there)==-1) &&             // no link already
      random(6)==0) {                            // offchance
    systems[here]["jump"]+=" "+there;            // link from here to there
    systems[there]["jump"]+=" "+here;            // link from there to here
  }
}

void gen_mirror (mapping systems, string series, string game, string name) {

  int n_max, s_max, e_max, w_max;
  array (int) loc = random_planet (systems);
  n_max = s_max = loc[1];
  e_max = w_max = loc[0];
  string *coords = allocate (2);
  int *val = allocate (4);
  val[0]=val[1]=val[2]=val[3]=0;
  // set the random resources, integrating the traditional allocation scheme
  int avg_rsc, min_t, ag_t, fuel_t;
  int count=sizeof(systems);
  int s_add = d[series][game]["s_add"] || d[series]["s_add"];  // in case of older series

//  min_t=d[series]["s_add"]*d[series]["avg_min"];
//  ag_t=d[series]["s_add"]*d[series]["avg_ag"];
//  fuel_t=d[series]["s_add"]*d[series]["avg_fuel"];

//  min_t  = s_add*d[series]["avg_min"];
//  ag_t   = s_add*d[series]["avg_ag"];
//  fuel_t = s_add*d[series]["avg_fuel"];

  ag_t  = (s_add - 1) * d[series][game]["avg_ag"];
  min_t  = (s_add - 1) * d[series][game]["avg_min"];
  fuel_t  = (s_add - 1) * d[series][game]["avg_fuel"];

  string here;
  foreach (indices(systems), here) {

    if(--count>0) {
      min_t-=systems[here]["min"]=random(min_t*2/(count+1));
      ag_t-=systems[here]["ag"]=random(ag_t*2/(count+1));
      fuel_t-=systems[here]["fuel"]=random(fuel_t*2/(count+1));
    } else {
      systems[here]["min"]=min_t;
      systems[here]["ag"]=ag_t;
      systems[here]["fuel"]=fuel_t;
    }
    systems[here]["max_pop"]=(systems[here]["min"]>systems[here]["fuel"]?systems[here]["min"]:systems[here]["fuel"]);
    systems[here]["vis_max_pop"]=systems[here]["max_pop"];
  	systems[here]["owner"]="";
  	systems[here]["home"]="";
  	systems[here]["name"]= "System " + here;

    // while we are going through them all let's find the bounding box
    // APG - and the number of planets along each side of the bounding box,
    // val[0] is east, val[1] is west, val[2] is north, val[3] is south.
    coords = here/",";
    if (((int) coords[0]) > e_max) {
      e_max = (int)coords[0];
      val[0] = 1;
    } else if (((int) coords[0]) == e_max) {
      val[0]++;
    }
    if (((int) coords[0]) < w_max) {
      w_max = (int)coords[0];
      val[1] = 1;
    } else if (((int) coords[0]) == w_max) {
      val[1]++;
    }
    if (((int) coords[1]) > n_max) {
      n_max = (int)coords[1];
      val[2] = 1;
    } else if (((int) coords[1]) == n_max) {
      val[2]++;
    }
    if (((int) coords[1]) < s_max) {
      s_max = (int)coords[1];
      val[3] = 1;
    } else if (((int) coords[1]) == s_max) {
      val[3]++;
    }
  } // foreach system

// APG - I've moved this section calculating pivot, dir, dir_ns and dir_ew
// up to before the homeworld location is decided, so I can use it to load
// the choice of homeworld location away from the pivot.

  /* -----------------7/04/99 12:23--------------------
          Andy's mirror stuff used instead

  // choose a direction
  int dir_ns = random(2);
  int dir = random(2) * 2 - 1;
  int dir_ew = 1 - dir_ns;
  int pivot;
  if (dir_ns==1) {
	if (dir == 1) { pivot = n_max;
	} else { pivot = s_max; } // if
  } else {
	if (dir == 1) { pivot = e_max;
    } else { pivot = w_max; } // if
  } // if
   -------------------------------------------------- */

  int dir, dir_ns, dir_ew, pivot;
  int i, j = 1, maxval = 0;
  // APG - find out the largest number of planets along any side of the
  // bounding box (maxval), and how many sides have that many (j).
  for (i = 0; i < 4; i++) {
    if (val[i] > maxval) {
      maxval = val[i];
      j = 1;
    } else if (val[i] == maxval) {
      j++;
    }
  }
  // APG - choose one of the good sides.
  i = random(j);
  for (j = 0; j < 3; j++) {
    if (val[j] == maxval) {
      if (i == 0) {
        break;
      }
      --i;
    }
  }
  switch (j) {
  case 0:
    pivot = e_max;
    dir_ns = 0;
    dir_ew = 1;
    dir = 1;
    break;
  case 1:
    pivot = w_max;
    dir_ns = 0;
    dir_ew = 1;
    dir = -1;
    break;
  case 2:
    pivot = n_max;
    dir_ns = 1;
    dir_ew = 0;
    dir = 1;
    break;
  case 3:
    pivot = s_max;
    dir_ns = 1;
    dir_ew = 0;
    dir = -1;
    break;
  }

  // choose the homeworld
  int *hw;
  for (i = 3; i; i--) {
    hw = random_planet (systems);
    if (dir_ns && (hw[1] != pivot))
      break;
    if (dir_ew && (hw[0] != pivot))
      break;
  }
  here = hw[0]+","+hw[1];
  systems[here]["owner"]=name;
  systems[here]["home"]=name;
  d[series][game]["p"][name]["expl"]=here;

  // minimum_ag is a resource I added to determine
  // whether or not to do a traditional setup, and if
  // not how much ag should be the minimum to start with
  int res = d[series]["min_ag"];
  // decide if this is the standard setup or not
  if (res == 0) { // standard
    systems[here]["name"]="HW";
    systems[here]["min"]=d[series]["home_min"];
  	systems[here]["fuel"]=d[series]["home_fuel"];
  	systems[here]["ag"]=d[series]["home_ag"];
  	systems[here]["pop"]=d[series]["home_ag"];
  	systems[here]["vis_pop"]=d[series]["home_ag"];
    systems[here]["max_pop"]=(d[series]["home_min"]>d[series]["home_fuel"]?d[series]["home_min"]:d[series]["home_fuel"]);
    systems[here]["vis_max_pop"]=systems[here]["max_pop"];
  } else {
    systems[here]["name"] = "HW:1";
    systems[here]["min"] += d[series]["home_min"]/4;
    systems[here]["fuel"] += d[series]["home_fuel"]/4;
    systems[here]["ag"] += d[series]["home_ag"]/4;
    systems[here]["max_pop"]+=(d[series]["home_min"]>d[series]["home_fuel"]?d[series]["home_min"]:d[series]["home_fuel"])/4;
    systems[here]["vis_max_pop"]=systems[here]["max_pop"];
    int ag = systems[here]["ag"];
    systems[here]["pop"]=ag;
    systems[here]["vis_pop"]=ag;

    int i = 1;
    while ((ag < res) && (i< sizeof(systems))) {
      i++;
      int *ip;
      string plan;
      do {
        for (j = 3; j; j--) {
          ip = random_planet (systems);
          if (dir_ns && (ip[1] != pivot))
            break;
          if (dir_ew && (ip[0] != pivot))
            break;
        }
        plan = ip[0]+","+ip[1];
      } while (systems[plan]["owner"] != "");

      // boost the home systems a bit
      systems[plan]["min"] += d[series]["home_min"]/4;
      systems[plan]["fuel"] += d[series]["home_fuel"]/4;
      systems[plan]["ag"] += d[series]["home_ag"]/4;
      systems[plan]["max_pop"]+=(d[series]["home_min"]>d[series]["home_fuel"]?d[series]["home_min"]:d[series]["home_fuel"])/4;
      systems[here]["vis_max_pop"]=systems[here]["max_pop"];

      ag += systems[plan]["ag"];
      systems[plan]["owner"]=name;
      systems[plan]["home"]=name;
      systems[plan]["name"]= "HW:" + i;
      systems[plan]["pop"]=systems[plan]["ag"];
      systems[plan]["vis_pop"]=systems[plan]["ag"];
      d[series][game]["p"][name]["expl"] += " " + plan;
    } // while ag too low

  } // if homeworld
  // now we need to get rid of extraneous links

  // i am borrowing this, i think it does what i think it does :)
  string *jump;
  string *j_list, ixa, ixb;
  foreach(indices(systems), ixa) {
    jump=({});
    j_list=systems[ixa]["jump"]/" ";
    foreach(j_list, ixb)
      if(search(indices(systems), ixb)!=-1) {
        jump+=({ixb});
        if(search(systems[ixb]["jump"]/" ", ixa)==-1)
          systems[ixb]["jump"]+=" "+ixa;
      }
    systems[ixa]["jump"]=jump*" ";
  }

  // ok now that we have player one almost set up lets do the
  // mirror thing

  mapping nw = ([]);
  foreach (indices(systems), here) {
	coords = here/",";
    int x = (int)coords[0];
    int y = (int)coords[1];

    // border world
	if (((x==pivot) && (dir_ew==1)) || ((y==pivot)&&(dir_ns==1))) {
      // make new planet
      int nx = x + dir_ew * dir;
      int ny = y + dir_ns * dir;
      string plan = nx+","+ny;
      nw[plan] = ([]);
      int min = random (d[series]["home_min"]/4) + d[series]["home_min"]/4;
      nw[plan]["min"]=min;
      int fuel = random (d[series]["home_fuel"]/4) + d[series]["home_fuel"]/4;
      nw[plan]["fuel"]=fuel;
      nw[plan]["ag"]=random (d[series]["home_ag"]/4) + d[series]["home_ag"]/4;
      nw[plan]["pop"]=0;
      nw[plan]["vis_pop"]=0;
      nw[plan]["max_pop"]=(min>fuel)?min:fuel;
      nw[plan]["vis_max_pop"]=(min>fuel)?min:fuel;
      nw[plan]["owner"]="";
      nw[plan]["home"]="";
      nw[plan]["name"]= "System " + plan;
      nw[plan]["jump"]= here + " " + (nx+dir_ew*dir) + "," + (ny+dir_ns*dir);
      systems[here]["jump"] += " " + plan;
    } // if

    string plan = reflected_planet (x, y, pivot, dir_ns, dir);
    //write (plan + " reflection of " + here + "\n");
 	nw[plan] = ([]);
  	nw[plan]["min"]=systems[here]["min"];
  	nw[plan]["fuel"]=systems[here]["fuel"];
  	nw[plan]["ag"]=systems[here]["ag"];
  	nw[plan]["pop"]=systems[here]["pop"];
  	nw[plan]["vis_pop"]=systems[here]["vis_pop"];
  	nw[plan]["max_pop"]=systems[here]["max_pop"];
  	nw[plan]["vis_max_pop"]=systems[here]["vis_max_pop"];
  	nw[plan]["owner"]="";
  	nw[plan]["home"]="";
    if (systems[here]["home"] != "") {
      nw[plan]["name"] = systems[here]["name"];
      systems[here]["name"]=replace(systems[here]["name"], "HW", name);
    } else {
      nw[plan]["name"]= "System " + plan;
    } // if
    nw[plan]["jump"]= reflect_jumps (pivot, dir_ns, dir, systems[here]["jump"]);


  } // foreach
  systems += nw;
  // yeah we did it :)

// APG - adding possible links between border worlds
  foreach (indices(systems), here) {
	coords = here/",";
    int x = (int)coords[0];
    int y = (int)coords[1];

    // border world
	if (((x==pivot+dir) && (dir_ew==1)) || ((y==pivot+dir) && (dir_ns==1))) {
      if (dir_ew) {
        border_crossing (systems, here, x+","+(y+1));   // next planet north
        border_crossing (systems, here, x+","+(y-1));   // next planet south
      }
      if (dir_ns) {
        border_crossing (systems, here, (x+1)+","+y);   // next planet east
        border_crossing (systems, here, (x-1)+","+y);   // next planet west
      }
    }
  } // foreach

  d[series][game]["s"] = ([]);
  d[series][game]["s"]+=systems;

  recalc_ratios (series, game, name);

  return;
} // gen_mirror

void add_player (string name, string series, string game) {

  // if this is going to be used for clan warfare this
  // will need to be more sophisticated
  // basically go through and sub things in

  d[series][game]["p"][name]["expl"] = "";
  string ixa;
  foreach (indices(d[series][game]["s"]), ixa) {
    if ((search (d[series][game]["s"][ixa]["name"], "HW") > -1) &&
        ((d[series][game]["s"][ixa]["owner"]) == "")) {
      d[series][game]["s"][ixa]["name"] = replace(d[series][game]["s"][ixa]["name"], "HW", name);
      d[series][game]["s"][ixa]["home"] = name;
      d[series][game]["s"][ixa]["owner"] = name;
      if ((d[series][game]["p"][name]["expl"]) != "") {
        d[series][game]["p"][name]["expl"] += " ";
      } // if
      d[series][game]["p"][name]["expl"] += ixa;
    } // if
  } // foreach

  recalc_ratios (series, game, name);

  return;
} // add_player

mapping add_chain(mapping chain, string series, string game, int x,
                  int y, int xin, int yin)

{
  int s_add = d[series][game]["s_add"] || d[series]["s_add"];  // in case of older series

//  if(sizeof(chain)>=d[series]["s_add"]) return chain;
  if(sizeof(chain) >= s_add) return chain;

  chain[x+","+y]=([]);
  string *j_list=({((int) x+1)+","+y,
                   x+","+((int) y+1),
                   ((int) x-1)+","+y,
                   x+","+((int) y-1)});
  int j=random(4)+1;
  int j_to;
  array dest;
  array jump=({});

  if(xin && yin) {
    j_list-=({j_list[search(j_list, xin+","+yin)]});
    jump=({xin+","+yin});
    j--;
  }

  while(j) {
    j_to=random(sizeof(j_list));
    if(search(jump, j_list[j_to])==-1) {
      jump+=({j_list[j_to]});
      j--;
    }
  }

  chain[x+","+y]["jump"]=jump*" ";

  for(j=0; j<sizeof(jump); j++) {
    if((search(indices(chain), jump[j])!=-1) || (search(indices(d[series][game]["s"]), jump[j])!=-1)) continue;
    dest=jump[j]/",";
    chain=add_chain(chain, series, game, (int) dest[0], (int) dest[1], x, y);
  }

  return chain;
}

string info_data(string name, string c_pass, string series, string game, string message)
{
  string ixa, prev, next;
  string ready="";
  string setpass="";
  int i, j;
//  int newmap;
//  string loadmap;
  string s_font, e_font;

  if (p[name]["flags"] & flags_sepdata) {
    s_font="<font color="+DATA_COLOR+">";
    e_font="</font>";
  } else {
    s_font=e_font="";
  }

//  newmap = (p[name]["flags"] & flags_sepmap);
//  loadmap = "<SCRIPT language=JavaScript>\n"+
//            "<!--\n"+
//            "function loadmap(){ window.open( '/support/newmap.html', '', 'scrollbars,height=300,width=620')}\n"+
//            "//--></SCRIPT>\n";


  foreach(indices(d[series][game]["p"]), ixa) {
    if(d[series][game]["p"][ixa]["end"]) i++;
    j++;
  }

  if(!d[series][game]["p"][name]["end"]) ready="not ";


  if(d[series][game]["full"] == 1) {
    next = "waiting for players";
  } else if(d[series][game]["u_cnt"] == 0) {
    next = time_conv(d[series][game]["u_first"]-time());
  } else {
    next = time_conv(d[series][game]["u_time"]+d[series][game]["u_last"]-time());
  }

  if((d[series]["spawn_first"] == 1) && (d[series][game]["full"] < d[series]["p_max"])) {
    if(d[series][game]["gpass"] && (d[series][game]["gpass"]!="")) {
      setpass = "<tr><td>Password:</td><td>"+s_font+sterilizeString(d[series][game]["gpass"])+e_font+"</td></tr>";
    } else if (d[series][game]["full"]==1) {
      setpass = "<tr><td>Password:</td><td><input type=password name=gpass size=10 maxlength=10></td>"+
        "<td><input type=submit name=\"u:gpass\" value=\"Set\"></td></tr>";
	}
  }

  if (d[series][game]["u_cnt"] > 0) {
    prev = time_conv(time()-d[series][game]["u_last"])+" ago";
  } else {
    prev = "none";
  } 

  if(d[series][game]["p"][name]["notes"] == 0) {
    d[series][game]["p"][name]["notes"]="";
  }

  return (gheader("Info", name, c_pass, series, game, message)+
//          (newmap?loadmap:"")+
          "<table border=0 width=100%>"+
          "<tr><th colspan=6><font size=4>Game Information</font></th></tr>"+setpass+
          "<tr valign=top><td>Update Count:</td><td>"+s_font+d[series][game]["u_cnt"]+e_font+"</td>"+
          "<td>Last Update:</td><td>"+s_font+prev+e_font+"</td>"+
          "<td>Next Update:</td><td>"+s_font+next+e_font+"</td></tr>"+
          "<tr valign=top><td align=center colspan=3>"+s_font+"You are "+ready+"ready for an update"+e_font+"</td>"+
          "<td align=center colspan=3>"+s_font+i+" of "+j+" players are ready for an update"+e_font+"</td></tr>"+
          "<tr valign=top><th colspan=6><font size=4>Empire Totals</font></th></tr>"+
          "<tr valign=top><td>Minerals:</td><td>"+s_font+d[series][game]["p"][name]["min"]+e_font+"</td>"+
          "<td>Fuel:</td><td>"+s_font+d[series][game]["p"][name]["fuel"]+e_font+"</td>"+
          "<td>Agriculture:</td><td>"+s_font+d[series][game]["p"][name]["ag"]+e_font+"</td></tr>"+
          "<tr valign=top><td>Population:</td><td>"+s_font+d[series][game]["p"][name]["pop"]+e_font+"</td>"+
          "<td>Target Population:</td><td>"+s_font+d[series][game]["p"][name]["max_pop"]+e_font+"</td>"+
          "<td>Tech Level:</td><td>"+s_font+d[series][game]["p"][name]["tech"]+e_font+"</td></tr>"+
          "<tr valign=top><td>Economic Power:</td><td>"+s_font+d[series][game]["p"][name]["e_pow"]+e_font+"</td>"+
          "<td>Military Power:</td><td>"+s_font+d[series][game]["p"][name]["m_pow"]+e_font+"</td>"+
          "<td>Tech Development:</td><td>"+s_font+d[series][game]["p"][name]["tech_d"]+e_font+"</td></tr>"+
          "<tr valign=top><th colspan=6><font size=4>Ratios and Usage</font></th></tr>"+
          "<tr valign=top><td>Maintenance Ratio:</td><td>"+s_font+d[series][game]["p"][name]["min_r"]+e_font+"</td>"+
          "<td>Fuel Ratio:</td><td>"+s_font+d[series][game]["p"][name]["fuel_r"]+e_font+"</td>"+
          "<td>Agriculture Ratio:</td><td>"+s_font+d[series][game]["p"][name]["ag_r"]+e_font+"</td></tr>"+
          "<tr valign=top><td>Total Build:</td><td>"+s_font+d[series][game]["p"][name]["build"]+e_font+"</td>"+
          "<td>Total Maintenance:</td><td>"+s_font+d[series][game]["p"][name]["maint"]+e_font+"</td>"+
          "<td>Total Fuel Use:</td><td>"+s_font+d[series][game]["p"][name]["f_use"]+e_font+"</td></tr>"+
          "<tr valign=top><th colspan=6><br><br><img src=\""+theme_dir(name)+"/spacerule.gif\" height=10 width=100%><br><br></th></tr>"+
          "<tr valign=top><th colspan=6><font size=4>Game Notes</font></th></tr>"+
          "<tr><td><input type=submit name=\"u:notes\" value=\"Update Notes\"></td>"+
          "<td colspan=4 align=center><textarea name=\"notes\" rows=10 cols=60 wrap=hard>"+
          replace(d[series][game]["p"][name]["notes"], ({"<br>"}), ({"\n"}))+"</textarea></td><td></tr></tr>"+
          "<tr valign=top><th colspan=6><br><br><img src=\""+theme_dir(name)+"/spacerule.gif\" height=10 width=100%><br><br></th></tr>"+
          "<tr valign=top><th colspan=6><font size=4>Series Constants</font></th></tr>"+
          series_info(series, name, 2)+
          "</table>"+
          footer(name));
}

mapping info_screen(string name, string c_pass, string series, string game, string message)
{
  return (["data":info_data(name, c_pass, series, game, message),
          "type":"text/html"]);
}

mapping tech_screen(string name, string c_pass, string series, string game, string message)
{
  string t_list="";
  string *devs=d[series][game]["p"][name]["devs"]/" ";
  string *undev=all_tech-devs;
  int i, num;
  string s_font, e_font;

  if (p[name]["flags"] & flags_sepdata) {
    s_font="<font color="+DATA_COLOR+">";
    e_font="</font>";
  } else {
    s_font=e_font="";
  }
  if(d[series][game]["p"][name]["tech"]>=0.0)
    num=(int) sqrt(d[series][game]["p"][name]["tech"])-
      d[series][game]["p"][name]["devel"];
  else num=0;

  int rnum = d[series]["ar_tech"] - d[series][game]["p"][name]["dev_r"];
  int tnum = d[series]["tr_tech"] - d[series][game]["p"][name]["dev_t"];

  // developed techs (first rows at top of table)
  for(i=0; i<sizeof(devs); i++) {

    // first two cells of table - developed techs
    // 3.0 allow a trade-in if possible
    t_list += "<tr><td>";
    if ((tnum > 0) && (search (d[series]["r_tech"], devs[i]) != -1)) {
      t_list+=s_font+"<b>trade-in</b>"+e_font+"</td><td align=center><input type=submit name=\"t:"+devs[i]+"\" value=\""+devs[i]+"\"></td>";
    } else {
      t_list+="</td><td align=center>"+s_font+devs[i]+e_font+"</td>";
    } // if

    // cells 3&4 - restricted techs
    if(sizeof(undev)>i) {
      t_list+="<td align=center>";
      if (search (d[series]["r_tech"], undev[i]) != -1) {
        if(num>0) {
          t_list+="<input type=submit name=\"t:"+undev[i]+"\" value=\""+undev[i]+"\"></td><td>"+s_font+"<b>restricted</b>"+e_font;
        } else {
          t_list+=s_font+undev[i]+e_font+"</td><td>"+s_font+"<b>restricted</b>"+e_font+"</td><td>";
        } // if
      } // if

      // cells 3&4 - undeveloped techs
      // need to make sure that we allow this tech to be developed
      if (search (d[series]["u_tech"], undev[i]) != -1) {
        if(num>0) {
          t_list+="<input type=submit name=\"t:"+undev[i]+"\" value=\""+undev[i]+"\"></td><td>";
        } else {
          t_list+=s_font+undev[i]+e_font+"</td><td>";
        } // if
      } // if
      t_list+="</td></tr>";
    }
  }

  // balance of undeveloped techs
  if(i<sizeof(undev))
    for(; i<sizeof(undev); i++) {
      t_list+="<tr><td></td><td></td><td align=center>";

      // restricted techs
      if (search (d[series]["r_tech"], undev[i]) != -1) {
        if(num>0) {
          t_list+="<input type=submit name=\"t:"+undev[i]+"\" value=\""+undev[i]+"\"></td><td>"+s_font+"<b>restricted</b>"+e_font;
        } else {
          t_list+=s_font+undev[i]+e_font+"</td><td>"+s_font+"<b>restricted</b>"+e_font;
        } // if
      } // if

      // undeveloped techs
      if (search (d[series]["u_tech"], undev[i]) != -1) {
        if(num>0) {
          t_list+="<input type=submit name=\"t:"+undev[i]+"\" value=\""+undev[i]+"\"></td><td>";
        } else {
          t_list+=s_font+undev[i]+e_font+"</td><td>";
        } // if
      } // if
      t_list+="</td></tr>";
    }

   string techlist;
   if (num == 0) {
     techlist = "You have no developments available.<br>";
   } else {
     techlist = "You have "+num+" developments available.<br>";
   }

  // write out some headers
  if (d[series]["ar_tech"]) {
    techlist += "You have used " + d[series][game]["p"][name]["dev_r"] + " of a possible " + d[series]["ar_tech"] + " restricted techs.<br>";
  } // if

  if (d[series]["tr_tech"]) {
    techlist += "You have used " + d[series][game]["p"][name]["dev_t"] + " of your " + d[series]["tr_tech"] + " trade-in opportunities.<br>";
  } // if


  return (["data":
          gheader("Tech", name, c_pass, series, game, message)+RATIOS_LINE+
          "<p align=center><b>"+
//          "You have "+num+
//          " developments available " + rnum +
//          " of which can be restricted developments with " + tnum + " left"+
//          " left."+
          techlist+
          "Tech Level "+
          d[series][game]["p"][name]["tech"]+" (BR "+
          (d[series][game]["p"][name]["tech"] > 0.0 ?
           (int) sqrt(d[series][game]["p"][name]["tech"]) : 0)+
          ")</b><center><p><table width=50%>"+
          "<tr><td></td><th>Developed</th><th>Undeveloped</th><td></td></tr>"+
          t_list+
          "</table></center>"+
          footer(name),
          "type":"text/html"]);
}

mapping build_screen(string name, string c_pass, string series, string game, string message)
{
  int j;
  string s_font, e_font;

  if (p[name]["flags"] & flags_sepdata) {
    s_font="<font color="+DATA_COLOR+">";
    e_font="</font>";
  } else {
    s_font=e_font="";
  }

  string rank="";
  if(d[series][game]["p"][name]["tech"]>=1.0) {
    for(j=1; j<(int) sqrt(d[series][game]["p"][name]["tech"]); j++) {
      rank+="<option>"+j;
    }
    rank+="<option selected>"+j;
  } else
    return (["data":
            gheader("Build", name, c_pass, series, game, message)+RATIOS_LINE+
            "<center><b>Your tech level is too low to build any ships.</b></center>"+
            footer(name),
            "type":"text/html"]);

  mixed b_at=({});
  string ixa;
  foreach(indices(d[series][game]["s"]), ixa) {
    if((d[series][game]["s"][ixa]["owner"] != name) ||
       (d[series][game]["s"][ixa]["pop"] < d[series]["min_build"])) continue;
    b_at += ({"<option value=\""+ixa+"\""});
    if(d[series][game]["s"][ixa]["home"]==name)
      b_at[-1] += " selected";
    b_at[-1] += ">"+ (d[series][game]["s"][ixa]["name"]-"<br>")+ " ("+ixa+")";
  }
  if(!sizeof(b_at))
    return (["data":
            gheader("Build", name, c_pass, series, game, message)+RATIOS_LINE+
            "<center><b>You have no planets with enough population for "
            "building.</b></center>"+ footer(name),
            "type":"text/html"]);

#if ROXEN_VER == "1.2"
    b_at = Array.sort_array(b_at, lambda(string a, string b) {
#else
    b_at = sort_array(b_at, lambda(string a, string b) {
#endif

    int aa, bb;
    aa = search(a, ">");
    bb = search(b, ">");
    return lower_case(a[aa..]) > lower_case(b[bb..]);
  }) * "\n";


  string *devs=d[series][game]["p"][name]["devs"]/" ";
  string build=("<tr valig=top><th align=left>Type</th><th>Number</th><th>Name</th>"
                "<th>Battlerank</th><th>Build ships at</th></tr>");

  foreach(devs, ixa)
    {
      build += sprintf("<tr valign=center><td>%s</td><td align=center>"
                       "<select name=\"b:num:%s\"><option>0<option>1<option>2"
                       "<option>3<option>4<option>5<option>6<option>7<option>8"
                       "</select></td><td align=center><input name=\"b:"
                       "name:%s\" size=15 maxlength=20></td><td align=center>"
                       "<select name=\"b:br:%s\">%s</select></td><td align=center>"
                       "<select name=\"b:at:%s\">%s</select>"
                       "</td></tr>\n", ixa, ixa, ixa, ixa, rank, ixa, b_at);
    }

  return (["data":
          gheader("Build", name, c_pass, series, game, message)+
          RATIOS_LINE+"<center><table>"+
          build+
          "<tr><td><input type=submit name=\"c:y\" value=\"Build\">"+
          "<input type=submit name=\"c:n\" value=\"Cancel\"></td></tr>"+
          "</table></center>"+
          footer(name),
          "type":"text/html"]);
}

string fix_orders(array a,
                  string series,
                  string game,
                  string loc,
                  array expl, string ixa, void|int infleet)
{
  array orders=({});
  int j;

  for(j = 0; j < sizeof(a); j++)
    {
      string tmp = d[series][game]["s"][loc]["name"]-"<br>";
      if(a[j] == d[series][game]["f"][ixa]["orders"])
        orders += ({"<option selected value=\""+a[j]+"\">"});
      else
        orders += ({"<option value=\""+a[j]+"\">"});
      switch(a[j])
        {
        case "destroy":
          orders[-1]+="Annihilate "+tmp;
          break;
        case "invade":
          orders[-1]+="Invade "+tmp;
          break;
        case "form":
          orders[-1]+="Terraform "+tmp;
          break;
        case "cloak":
          orders[-1]+="Cloak";
          break;
        case "uncloak":
          orders[-1]+="Uncloak";
          break;
        case "col":
          orders[-1]+="Colonize "+tmp;
          break;
        case "nuke":
          orders[-1]+="Nuke "+tmp;
          break;
        case "dismantle":
          orders[-1]+="Dismantle";
          break;
        case "standby":
          orders[-1]+="Standby at "+tmp;
          break;
        case "build":
          orders[-1]+="Build at "+tmp;
          break;
        case "cancel":
          orders[-1]+="Cancel Build";
          break;
        default:

          string *split=a[j]/":";
          if(sizeof(split)<2) {
            if(search(expl, a[j]) == -1)
              orders[-1]+="Explore "+a[j];
            else
              orders[-1] +=
                ("Move to "+d[series][game]["s"][a[j]]["name"]+" ("+
                 a[j]+")");
          } else {
            int explored;
            explored = search(expl, split[1]) != -1;
            if(split[0]=="send") {
              orders[-1] += ("Send to "+d[series][game]["s"][split[1]]["name"]+
                             " ("+split[1]+")");

            } else if(split[0]=="jumpto") {
              orders[-1] += ("Jump to "+split[1] + " (" +d[series][game]["s"][split[1]]["name"] + ") ");

            } else if(split[0]=="morph") {
              orders[-1] += ("Change into "+split[1]);

            } else if(split[0]=="create") {
              orders[-1] += ("Create "+split[1]);

            } else if(split[0]=="close") {
              orders[-1] += ("Close "+split[1] +" ("+
                             (explored ? d[series][game]["s"][split[1]]["name"] :
                              "Unexplored") +")");

            } else if(split[0]=="open") {
              orders[-1] += ("Open "+split[1] +" ("+
                             (explored ? d[series][game]["s"][split[1]]["name"] :
                              "Unexplored") +")");

            } else if(split[0]=="fleet") {
              orders[-1]+="Fleet "+d[series][game]["t"][split[1]]["name"];
            }
          }
        }
    }

#if ROXEN_VER == "1.2"
    return  Array.sort_array(orders, lambda(string a, string b) {
#else
    return  sort_array(orders, lambda(string a, string b) {
#endif

    int aa, bb;
    aa = search(a, ">");
    bb = search(b, ">");
    return lower_case(a[aa..]) > lower_case(b[bb..]);
  }) * "\n";
}

string ships_data(string name, string c_pass, string series,
                     string game, string message)
{
  string f_list="";
  string ixa, ixb, loc;
  mixed orders;
  string *a;
  int j;
  float next_br;
  string *expl=d[series][game]["p"][name]["expl"]/" ";
  string s_font, e_font;
  string stab_head;

  if (p[name]["flags"] & flags_sepdata) {
    s_font="<font color="+DATA_COLOR+">";
    e_font="</font>";
  } else {
    s_font=e_font="";
  }

  foreach(expl, ixa) {
    foreach(indices(d[series][game]["f"]), ixb) {
      if(d[series][game]["f"][ixb]["owner"]!=name) continue;
      loc=d[series][game]["f"][ixb]["loc"];
      if(loc!=ixa) continue;

      if (p[name]["flags"] & flags_nextbr) {
        next_br=d[series][game]["f"][ixb]["br"]*d[series][game]["p"][name]["min_r"];
        if (next_br>d[series][game]["f"][ixb]["max_br"])
          next_br=d[series][game]["f"][ixb]["max_br"];
      }

      a=get_valid_orders(series, game, ixb, expl, 1);
      if(!a)
        continue;

      orders = fix_orders(a, series, game, loc, expl, ixb);

      if (shipscr_format) {
        f_list +=
          sprintf("<tr><td align=center>"+
                  "<input type=text name=\"n:"+ixb+"\" value=\"%s\""+" size=8 maxlength=20></td>"+
                  "<td align=center>"+s_font+d[series][game]["f"][ixb]["type"]+e_font+"</td>"+
                  "<td align=center>"+s_font+"%.3f"+e_font+"</td>%s"+
                  "<td align=center>"+s_font+"%.3f"+e_font+"</td>"+
                  "<td align=center>"+s_font+"%s"+e_font+"</td>"+
                  "<td align=center><select name=\"o:"+ixb+"\">%s</select></td>"+
                  "</tr>",
                  d[series][game]["f"][ixb]["name"],
                  d[series][game]["f"][ixb]["br"],
                  ((p[name]["flags"] & flags_nextbr)?sprintf("<td align=center>"+s_font+"%.3f"+e_font+"</td>", next_br):""),
                  d[series][game]["f"][ixb]["max_br"],
                  d[series][game]["f"][ixb]["loc"],
                  orders);
      } else {
        f_list +=
          sprintf("<tr><td align=center>"+
                  "<input type=text name=\"n:"+ixb+"\" value=\"%s\""+"size=8 maxlength=20></td>"+
                  "<td align=center>"+s_font+"%.3f"+e_font+"</td>%s"+
                  "<td align=center>"+s_font+"%.3f"+e_font+"</td>"+
                  "<td align=center>"+s_font+"%s"+e_font+"</td>"+
                  "<td align=center><select name=\"o:"+ixb+"\">%s</select></td>"+
                  "<td align=center>"+s_font+d[series][game]["f"][ixb]["type"]+e_font+"</td>"+
                  "</tr>",
                  d[series][game]["f"][ixb]["name"],
                  d[series][game]["f"][ixb]["br"],
                  ((p[name]["flags"] & flags_nextbr)?sprintf("<td align=center>"+s_font+"%.3f"+e_font+"</td>", next_br):""),
                  d[series][game]["f"][ixb]["max_br"],
                  d[series][game]["f"][ixb]["loc"],
                  orders);
      }
    }
  }

  if(f_list=="")
    return (gheader("Ships", name, c_pass, series, game, message)+RATIOS_LINE+
            "<center><b>You have no ships in service.</b></center>"+
            footer(name));

  stab_head="<tr><th>Name</th><th>";
  if (shipscr_format) {
    stab_head+="Type</th><th>";
  }
  stab_head+="BR</th><th>";
  if (p[name]["flags"] & flags_nextbr) {
    stab_head+="Next BR</th><th>";
  }
  stab_head+="Max BR</th><th>Loc</th><th>Orders</th>";
  if (shipscr_format) {
    stab_head+="</tr>";
  } else {
    stab_head+="<th>Type</th></tr>";
  }

  return (gheader("Ships", name, c_pass, series, game, message)+RATIOS_LINE+
          "<center><table width=100%>"+stab_head+f_list+
          "</table></center>"+ footer(name));
}

mapping ships_screen(string name, string c_pass, string series,
                     string game, string message)
{
  return (["data":ships_data(name, c_pass, series, game, message),
           "type":"text/html"]);
}

string fleets_data(string name, string c_pass,
                      string series, string game, string message)
{
  string f_list="";
  string ixa, ixb, ixc, orders, loc, new_fleet;
  string *a;
  int j;
  float next_br;
  string *expl=d[series][game]["p"][name]["expl"]/" ";
  string s_font, e_font;

  if (p[name]["flags"] & flags_sepdata) {
    s_font="<font color="+DATA_COLOR+">";
    e_font="</font>";
  } else {
    s_font=e_font="";
  }

  foreach(expl, ixa) {
    foreach(indices(d[series][game]["t"]), ixb) {
      if(d[series][game]["t"][ixb]["owner"]!=name) continue;
      loc=d[series][game]["t"][ixb]["loc"];
      if(loc!=ixa) continue;
  
      a=get_fleet_orders(series, game, ixb, expl);
      orders="";
  
      for(j=0; j<sizeof(a); j++) {
        if(a[j]==d[series][game]["t"][ixb]["orders"])
          orders+="<option selected value=\""+a[j]+"\">";
        else orders+="<option value=\""+a[j]+"\">";
        switch(a[j])
          {
          case "disband":
            orders+="Disband Fleet";
            break;
  
          case "disbandall":
            orders+="Disband Fleet and Ships";
            break;
  
          case "standby":
            orders+="Standby at "+(d[series][game]["s"][loc]["name"] -"<br>");
            break;
          default:
            if(search(expl, a[j])==-1) orders+="Explore "+a[j];
            else orders+="Move to "+
                   (d[series][game]["s"][a[j]]["name"] -"<br>")+" ("+a[j]+")";
          }
      }
  
      string ships_list = "";
      float br = 0.0, nextbr = 0.0, maxbr = 0.0;
      foreach(indices(d[series][game]["f"]), ixc) {
        if(d[series][game]["f"][ixc]["owner"]!=name) continue;
        loc=d[series][game]["f"][ixc]["loc"];
  
        a=get_valid_orders(series, game, ixc, expl, ixb);
        if(!a)
          continue;
  
        if (p[name]["flags"] & flags_nextbr) {
          next_br=d[series][game]["f"][ixc]["br"]*d[series][game]["p"][name]["min_r"];
          if (next_br>d[series][game]["f"][ixc]["max_br"])
            next_br=d[series][game]["f"][ixc]["max_br"];
        }

        string sorders = fix_orders(a, series, game, loc, expl, ixc, 1);
  
        br += d[series][game]["f"][ixc]["br"] * d[series][game]["f"][ixc]["br"];
        if (p[name]["flags"] & flags_nextbr) nextbr += next_br * next_br;
        maxbr += d[series][game]["f"][ixc]["max_br"] *
          d[series][game]["f"][ixc]["max_br"];
        ships_list +=
          sprintf("<tr align=left><td>"+
                  "<input type=text name=\"ns:"+ixc
                  +"\" value=\"%s\""+
                  "size=14 maxlength=20></td>"+
                  "<td align=center>"+s_font+"%.3f"+e_font+"</td>%s"+
                  "<td align=center>"+s_font+"%.3f"+e_font+"</td>"+
                  "<td align=center><select name=\"os:"+ixc+"\">%s</select></td>"+
                  "<td>"+s_font+d[series][game]["f"][ixc]["type"]+e_font+"</td>"+
                  "</tr>",
                  d[series][game]["f"][ixc]["name"],
                  d[series][game]["f"][ixc]["br"],
                  ((p[name]["flags"] & flags_nextbr)?sprintf("<td align=center>"+s_font+"%.3f"+e_font+"</td>", next_br):""),
                  d[series][game]["f"][ixc]["max_br"],
                  sorders);
      }
      br = sqrt(br);
      if (p[name]["flags"] & flags_nextbr) nextbr = sqrt(nextbr);
      maxbr = sqrt(maxbr);
      f_list +=
        sprintf("<table width=100%% border=1><tr align=center>"+
                "<td><table width=100%% cellspacing=0 cellpadding=0>"+
                "<tr align=left><th>Fleet Name</th><th align=center>Strength</th>"+
                ((p[name]["flags"] & flags_nextbr)?"<th align=center>Next Str</th>":"")+
                "<th align=center>Max Str</th>"+
                "<th align=center>Orders</th><th>Location</th></tr>"+
                "<tr align=left><td><input type=text name=\"n:"+ixb+
                "\" value=\"%s\""+
                "size=14 maxlength=20></td>"+
                "<td align=center>"+s_font+"%.3f"+e_font+"</td>%s"+
                "<td align=center>"+s_font+"%.3f"+e_font+"</td>"+
                "<td align=center><select name=\"o:"+ixb+"\">%s</select></td>"+
                "<td>"+s_font+"%s ("+ixa+")"+e_font+"</td>"+
                "</tr><tr><td colspan=6><hr></td></tr>"+
                "<tr align=left><th>Name</th><th align=center>BR</th>"+
                ((p[name]["flags"] & flags_nextbr)?"<th align=center>Next BR</th>":"")+
                "<th align=center>Max BR</th>"+
                "<th align=center>Orders</th><th>Type</th></tr>"+
                "%s</table></td></tr></table>",
                d[series][game]["t"][ixb]["name"], br,
                ((p[name]["flags"] & flags_nextbr)?sprintf("<td align=center>"+s_font+"%.3f"+e_font+"</td>", nextbr):""),
                maxbr, orders,
                d[series][game]["s"][ixa]["name"],
                ships_list);
  
    }
  }
  string *loc_list=({});

  foreach(indices(d[series][game]["f"]), ixa) {
    if(d[series][game]["f"][ixa]["owner"]!=name) continue;
    if(search(loc_list, d[series][game]["f"][ixa]["loc"])==-1)
      loc_list+=({d[series][game]["f"][ixa]["loc"]});
  }

  if(sizeof(loc_list)) {
    string loc_list2="";

    foreach(loc_list, ixa)
      loc_list2+="<option value=\""+ixa+"\">"+(d[series][game]["s"][ixa]["name"]-"<br>")+" ("+ixa+")";

    new_fleet=("<center><b>Form a New Fleet</b><table>"+
               "<tr><td align=right><b>Name:</b></td>"+
               "<td><input type=text size=20 maxlength=20 name=\"fname\"></td></tr>"+
               "<tr><td align=right><b>Location:</b></td>"+
               "<td><select name=\"floc\">"+loc_list2+"</select></td></tr>"+
               "<tr><td colspan=2 align=right><input type=submit name=\"nf:y\" value=\"Create\">"+
               "<input type=submit name=\"nf:n\" value=\"Cancel\"></td></tr>"+
               "</table></center>");
  } else new_fleet="";

  if(f_list=="")
    return (gheader("Fleets", name, c_pass, series, game, message)+RATIOS_LINE+
            "<center><b>You have no operational fleets.</b></center><p>"+
            new_fleet+
            footer(name));

  return (gheader("Fleets", name, c_pass, series, game, message)+RATIOS_LINE+
          new_fleet+"<center>"+
          f_list+"</center>"+
          footer(name));
}

mapping fleets_screen(string name, string c_pass,
                      string series, string game, string message)
{
  return (["data":fleets_data(name, c_pass, series, game, message),
          "type":"text/html"]);
}

string map_data(string name, string c_pass, string series, string game, int size, string loc);

string systems_data(string name, string c_pass, string series, string game, string message, string system, int mini)
{
  string s_list="";
  string ixa, ixb, ixc, sname, spop, smpop, loc, owner, type;
  string *expl=d[series][game]["p"][name]["expl"]/" ";
  mapping fl=([]);
  mapping type=([]);
  int cloak;
  int pop_width;
  string s_font, e_font;
  string maplet="";

  if (p[name]["flags"] & flags_sepdata) {
    s_font="<font color="+DATA_COLOR+">";
    e_font="</font>";
  } else {
    s_font=e_font="";
  }
  if (p[name]["flags"] & flags_webtv) {
    pop_width=10;
  } else {
    pop_width=4;
  }

  if((system!="") && (search(expl, system)!=-1)) {
    expl=({system});
    maplet=map_data(name, c_pass, series, game, 1, system);
  }

  // 2.9C count the number and type of ships at each system
  foreach(indices(d[series][game]["f"]), ixa) {
    loc=d[series][game]["f"][ixa]["loc"];
    owner=d[series][game]["f"][ixa]["owner"];
    type=d[series][game]["f"][ixa]["type"];

    // 2.9 change so enemies can't see other's builds
    cloak = d[series][game]["f"][ixa]["cloak"];
    if(((cloak==1) || (cloak==3)) && (owner!=name)) {
      continue;
    } // if

    // 3.0 show builds only if that option is on
    if (((d[series][game]["f"][ixa]["orders"]=="build" && owner!=name)) &&
      (d[series]["vis"] == 0)) {
      continue;
    } // if

    if(!mappingp(fl[loc])) fl[loc]=([]);
    if(!mappingp(fl[loc][owner])) fl[loc][owner]=([]);
    fl[loc][owner][type]++;
  }

  foreach(expl, ixa) {
    if(d[series][game]["s"][ixa]["owner"]==name && ! mini) {
      sname=("<input type=text size=20 maxlength=20 name=\"n:"+ixa+"\" value=\""+
             (d[series][game]["s"][ixa]["name"]-"<br>")+"\">");
      smpop=("<input type=text size="+pop_width+" maxlength=4 name=\"mp:"+ixa+"\" value=\""+
             d[series][game]["s"][ixa]["max_pop"]+"\">");
      spop=d[series][game]["s"][ixa]["pop"];
    } else {
      sname="<b>"+(d[series][game]["s"][ixa]["name"]-"<br>")+"</b>";
      smpop=((d[series]["vis"]||d[series][game]["s"][ixa]["owner"]==name)?d[series][game]["s"][ixa]["max_pop"]:d[series][game]["s"][ixa]["vis_max_pop"]);
      spop=((d[series]["vis"]||d[series][game]["s"][ixa]["owner"]==name)?d[series][game]["s"][ixa]["pop"]:d[series][game]["s"][ixa]["vis_pop"]);
    }

    s_list+=("<tr><td colspan=9><hr></td></tr>"+
             "<tr><td>"+sname+"</td>"+
             "<td align=center>"+s_font+ixa+e_font+"</td>"+
             "<td align=center>"+s_font+d[series][game]["s"][ixa]["min"]+e_font+"</td>"+
             "<td align=center>"+s_font+d[series][game]["s"][ixa]["fuel"]+e_font+"</td>"+
             "<td align=center>"+s_font+d[series][game]["s"][ixa]["ag"]+e_font+"</td>"+
             "<td align=center>"+s_font+spop+e_font+"</td>"+
             "<td align=center>"+s_font+smpop+e_font+"</td>"+
             "<td align=center>"+s_font+d[series][game]["s"][ixa]["owner"]+e_font+"</td>"+
             "<td align=center>"+s_font+d[series][game]["s"][ixa]["jump"]+e_font+"</td></tr>");

    if(mappingp(fl[ixa])) {
      type=([]);

      s_list+="<tr><td></td><td align=center><b>Empire</b></td>";

      foreach(indices(fl[ixa]), ixb)
        foreach(indices(fl[ixa][ixb]), ixc)
        type[ixc]=1;

      foreach(all_tech, ixb)
        if(type[ixb]) s_list+="<td align=center><b>"+ixb+"</b></td>";

      s_list+="</tr>";

      foreach(indices(fl[ixa]), ixb) {
        s_list+="<tr><td></td><td align=center>"+s_font+ixb+e_font+"</td>";

        foreach(all_tech, ixc)
          if(type[ixc]) s_list+="<td align=center>"+s_font+fl[ixa][ixb][ixc]+e_font+"</td>";

        s_list+="</tr>";
      }
    }
  }

  if (mini) {
    return (mini_header("System "+system, name, series)+
          "<center><table>"+
          "<tr><th>Name</th><th>Loc</th><th>Min</th><th>Fuel</th><th>Ag</th><th>Pop</th><th>Max Pop</th>"+
          "<th>Owner</th><th>Jumps</th></tr>"+
          s_list+
          "</table></center><p><p></body</html>");
  } else {
    return (gheader("Systems", name, c_pass, series, game, message)+RATIOS_LINE+
          "<center><table>"+
          "<tr><th>Name</th><th>Loc</th><th>Min</th><th>Fuel</th><th>Ag</th><th>Pop</th><th>Max Pop</th>"+
          "<th>Owner</th><th>Jumps</th></tr>"+
          s_list+
          "</table></center><p><p>"+maplet+
          footer(name));
  }
}

mapping systems_screen(string name, string c_pass, string series, string game, string message, string system, int mini)
{
  if(mappingp(d[series][game])) {
    if (d[series]["blind"] && d[series][game]["full"]==1) {
      return info_screen(name, c_pass, series, game, "The Systems screen is not accessible until the game has begun");
    }
    return (["data":systems_data(name, c_pass, series, game, message, system, mini),
            "type":"text/html"]);
  } else {
    return (["data":mini_header("Game Over", name, series)+"<center><p><p><h3>Game Over</h3></center></body></html>",
            "type":"text/html"]);
  }
}

string admin_systems_screen(string series, string game)
{
  string s_list="";
  string ixa, ixb, ixc, ixd, sname, smpop, loc, owner, type;
  mapping fl=([]);
  mapping type=([]);
  string *stags;
  int bol = 0;

  // 2.9C count the number and type of ships at each system
  foreach(indices(d[series][game]["f"]), ixa) {
    loc=d[series][game]["f"][ixa]["loc"];
    owner=d[series][game]["f"][ixa]["owner"];
    type=d[series][game]["f"][ixa]["type"];

    if(!mappingp(fl[loc])) fl[loc]=([]);
    if(!mappingp(fl[loc][owner])) fl[loc][owner]=([]);
    fl[loc][owner][type]++;
  }

  stags = ({"name", "ag", "min", "fuel", "pop", "max_pop", "jump", "owner", "home"});

  s_list += "<table width = 100%>";
  foreach(indices (d[series][game]["s"]), ixa) {
    if (bol==0) {
      bol = 1;
      s_list += "<tr><th><font color="+DATA_COLOR+">loc</font></th>";
      foreach (stags, ixd) {
        s_list+="<th><font color="+DATA_COLOR+"><b>"+ixd+"</b></font></th>";
      } // foreach
      s_list+= "</tr>";
    } // if

    s_list+= "<tr><td colspan=10><hr></td></tr><tr><td align=center><font color="+DATA_COLOR+">"+ixa+"</font></td>";
    foreach (stags, ixd) {
      s_list+="<td align=center><font color="+DATA_COLOR+">"+d[series][game]["s"][ixa][ixd]+"</font></td>";
    } // foreach
    s_list+= "</tr>";


    if(mappingp(fl[ixa])) {
      type=([]);

      s_list+="<tr><td></td><td align=center><b>Empire</b></td>";

      foreach(indices(fl[ixa]), ixb)
        foreach(indices(fl[ixa][ixb]), ixc)
        type[ixc]=1;

      foreach(all_tech, ixb)
        if(type[ixb]) s_list+="<td align=center><font color="+DATA_COLOR+"><b>"+ixb+"</b></font></td>";

      s_list+="</tr>";

      foreach(indices(fl[ixa]), ixb) {
        s_list+="<tr><td></td><td align=center><font color="+DATA_COLOR+">"+ixb+"</font></td>";

        foreach(all_tech, ixc)
          if(type[ixc]) s_list+="<td align=center><font color="+DATA_COLOR+">"+fl[ixa][ixb][ixc]+"</font></td>";

        s_list+="</tr>";
      }
    }
  }

  s_list += "</table>";
  return (s_list);

}

string clean_url(string s)
{
	return replace(s, ({" ", "'"}), ({"+", "%27"}));
}

string unclean_url(string s)
{
	return replace(s, ({"+", "%27"}), ({" ", "'"}));
}

string map_data(string name, string c_pass, string series, string game, int size, string loc)
{
  string map="";
  string ixa, la, lb, lc, jb, jf, gf;
  string omo;
  string *expl=d[series][game]["p"][name]["expl"]/" ";
  string *jump;
  int cloak;
  int xb, yb, x, y, x_max, y_max, x_min, y_min, own, other;
  int mine, fuel, agri, firstplanet = 1;
  int s_add = d[series][game]["s_add"] || d[series]["s_add"];  // in case of older series
  string s_font;

  if (p[name]["flags"] & flags_sepdata) {
    s_font="<font size=1 color="+DATA_COLOR+">";
  } else {
    s_font="<font size=1>";
  }

  // 2.9C changed 2 to 10 (magic numbers, yeah :)
//  y_min=x_min=d[series]["s_add"]*d[series]["p_max"]*10;
//  y_min = x_min = s_add*d[series]["p_max"]*10;

  if (size) {
    sscanf(loc, "%d,%d", xb, yb);
  }
  foreach(expl, ixa) {
    sscanf(ixa, "%d,%d", x, y);
    if (size) {
      if (((xb - x > size) || (x - xb > size)) || ((yb - y > size) || (y - yb > size))) {
        expl-=({ixa});
        continue;
      }
    }
    if (firstplanet == 1) {
      firstplanet = 0;
      x_min = x_max = x;
      y_min = y_max = y;
    } else {
      if(x<x_min) x_min=x;
      if(x>x_max) x_max=x;
      if(y<y_min) y_min=y;
      if(y>y_max) y_max=y;
    }
  }

  mapping sf=([]);

  // 2.9C count the number of ships at each world
  foreach(indices(d[series][game]["f"]), ixa) {
    if(!mappingp(sf[d[series][game]["f"][ixa]["loc"]])) {
      sf[d[series][game]["f"][ixa]["loc"]]=([]);
    } // if

    if(d[series][game]["f"][ixa]["owner"]==name) {
      sf[d[series][game]["f"][ixa]["loc"]]["own"]++;

      // 2.9 make sure the enemy doesn't know what ships are being built
    } else {
      cloak = d[series][game]["f"][ixa]["cloak"];
      if (((cloak==0) || (cloak==2)) &&
          ((d[series][game]["f"][ixa]["orders"]!="build") ||
           (d[series]["vis"] != 0))) {
             sf[d[series][game]["f"][ixa]["loc"]]["other"]++;
      } // if
    } // if
  } // 2.9C foreach ship

  for(y=y_max; y>=y_min; y--) {
    la=lb=lc="\n<tr>";
    for(x=x_min; x<=x_max; x++) {
      if(search(expl, x+","+y)!=-1) {
        jump=d[series][game]["s"][x+","+y]["jump"]/" ";

        if(search(jump, x+","+(y+1))!=-1)
          la+="<td></td><td align=center><img src=\""+theme_dir(name)+"/vert.gif\"></td><td></td>";
        else
          la+="<td></td><td align=center><img src=\""+theme_dir("")+"/vertt.gif\"></td><td></td>";

        if(search(jump, x+","+(y-1))!=-1)
          lc+="<td></td><td align=center><img src=\""+theme_dir(name)+"/vert.gif\"></td><td></td>";
        else
          lc+="<td></td><td align=center><img src=\""+theme_dir("")+"/vertt.gif\"></td><td></td>";

        if(search(jump, (x-1)+","+y)!=-1)
          jb="<img src=\""+theme_dir(name)+"/horz.gif\">";
        else
          jb="<img src=\""+theme_dir("")+"/horzt.gif\">";

        if(search(jump, (x+1)+","+y)!=-1)
          jf="<img src=\""+theme_dir(name)+"/horz.gif\">";
        else
          jf="<img src=\""+theme_dir("")+"/horzt.gif\">";

        mine = d[series][game]["s"][x+","+y]["min"];
        fuel = d[series][game]["s"][x+","+y]["fuel"];
        agri = d[series][game]["s"][x+","+y]["ag"];

        string omo;
        if (p[name]["flags"] & flags_sensitivemap) {
          omo = "<td valign=bottom><a href=\""+mountpoint+"?frload=fr_system?"+clean_url(name)+"?"+c_pass+"?"+
             clean_url(series)+"?"+game+"?"+x+","+y+"\""+
             " onMouseOver=\"winopen_member("+
             "'"+mountpoint+"?frload=fr_system?"+clean_url(name)+"?"+c_pass+"?"+
             clean_url(series)+"?"+game+"?"+x+","+y+"' "+
             "); return true;\"><img src=\""+theme_dir(name)+"/mini-mag.gif\" border=0></a></td>"+
             "<td colspan=2";
        } else {
          omo = "";
          omo = "<td colspan=3";
        }

        if(d[series][game]["s"][x+","+y]["annihilated"]) {
          gf="<input type=image border=0 src=\""+theme_dir(name)+"/planet2.gif\" name=\"m:"+x+","+y+"\">";
        } else if(d[series][game]["s"][x+","+y]["owner"]=="") {
          if ( (mine > fuel) && (mine > agri) )
            gf="<input type=image border=0 src=\""+theme_dir(name)+"/planetm.gif\" name=\"m:"+x+","+y+"\">";
          else if ( (agri >= mine) && (agri > fuel) )
            gf="<input type=image border=0 src=\""+theme_dir(name)+"/planetg.gif\" name=\"m:"+x+","+y+"\">";
          else
            gf="<input type=image border=0 src=\""+theme_dir(name)+"/planet.gif\" name=\"m:"+x+","+y+"\">";
        } else if(d[series][game]["s"][x+","+y]["owner"]==name) {
          gf=("<input type=image border=1 name=\"m:"+x+","+y+"\" src=\""+html_dir+
              "/aliens/"+p[name]["alien"]+"\">");
        } else {
          gf=("<input type=image border=0 name=\"m:"+x+","+y+"\" src=\""+html_dir+
              "/aliens/"+p[d[series][game]["s"][x+","+y]["owner"]]["alien"]+"\">");
        }
        if(mappingp(sf[x+","+y])) {
          own=sf[x+","+y]["own"];
          other=sf[x+","+y]["other"];
        } else {
          own=0;
          other=0;
        }

        lb+=("<td>"+jb+"</td>"+
             "<td><table border=0 cellspacing=0 cellpadding=0><tr>"+
             "<td align=center>"+s_font+d[series][game]["s"][x+","+y]["min"]+"</font></td>"+
             "<td rowspan=3>"+gf+"</td>"+
             "<td align=center>"+s_font+d[series][game]["s"][x+","+y]["fuel"]+"</font></td>"+
             "</tr><tr>"+
             "<td align=center>"+s_font+d[series][game]["s"][x+","+y]["ag"]+"</font></td>"+
             "<td align=center>"+s_font+((d[series]["vis"]||d[series][game]["s"][x+","+y]["owner"]==name)?
             d[series][game]["s"][x+","+y]["pop"]:d[series][game]["s"][x+","+y]["vis_pop"])+"</font></td>"+
             "</tr><tr>"+
             "<td align=center>"+s_font+"("+own+")</font></td>"+
             "<td align=center>"+s_font+"("+other+")</font></td>"+
             "</tr><tr>"+omo+" align=center><font size=1>"+
             replace(d[series][game]["s"][x+","+y]["name"], ({"remains of "}), ({"remains of<br>"}))+
             "</font></td></tr></table></td><td>"+jf+"</td>");
      } else {
        // 2.9 we need to insert a empty picture here to keep
        // the spacing correct, if it is the min
        if ((x==x_min) || (y==y_max)) {
          la+="<td></td><td></td><td></td>";
          lb+="<td></td><td align=center><img src=\""+theme_dir("")+"/empty.gif\"></td><td></td>";
          lc+="<td></td><td></td><td></td>";
        } else {
          la+="<td></td><td></td><td></td>";
          lb+="<td></td><td></td><td></td>";
          lc+="<td></td><td></td><td></td>";
        } // if need spacing
      } // 2.9C if
    } // 2.9C for
    map+=la+"</tr>"+lb+"</tr>"+lc+"</tr>";
  } // 2.9C for

  return ("<center><b>Click on a system for an up close view.</b><p>"+
          "<table border=0 cellpadding=0 cellspacing=0>"+
          map+
          "</table></center>");
} // 3.2 map_data

string fix_anchors(array expl, string series, string game, string name, string loc)
{
  array anchors=({});
  string ixa;

  foreach(expl, ixa) {
    if (ixa==loc) {
      anchors += ({"<option selected value=\""+ixa+"\">"});
    } else {
      anchors += ({"<option value=\""+ixa+"\">"});
    }
    anchors[-1] += (d[series][game]["s"][ixa]["name"]-"<br>")+" ("+ixa+")";
  }
#if ROXEN_VER == "1.2"
  return Array.sort_array(anchors, lambda(string a, string b) {
#else
  return sort_array(anchors, lambda(string a, string b) {
#endif

    int aa, bb;
    aa = search(a, ">");
    bb = search(b, ">");
    return lower_case(a[aa..]) > lower_case(b[bb..]);
  }) * "\n";
}

mapping map_screen(string name, string c_pass, string series, string game, string message, int size, string loc)
{
  string *expl=d[series][game]["p"][name]["expl"]/" ";
  string s_font, e_font;
  string index, anchor="";

  if (d[series]["blind"] && d[series][game]["full"]==1) {
    return info_screen(name, c_pass, series, game, "The Map screen is not accessible until the game has begun");
  }

  if (p[name]["flags"] & flags_sepdata) {
    s_font="<font color="+DATA_COLOR+">";
    e_font="</font>";
  } else {
    s_font=e_font="";
  }

  if ((p[name]["flags"] & flags_webtv) && ((int) d[series][game]["p"][name]["mapsize"])!=0) {
    anchor+="<p><center>Center Map at: <font color=\"#000000\"><select name=\"mapanchor\">"+
            fix_anchors(expl, series, game, name, loc)+"</select></font></center>";
  }

  return (["data":
          gheader("Map", name, c_pass, series, game, message)+RATIOS_LINE+
          map_data(name, c_pass, series, game, size, loc)+anchor+
          footer(name),
          "type":"text/html"]);
} // 3.2 map_screen

string diplom_data(string name, string c_pass, string series, string game, string message)
{
  string d_list="";
  string m_list="";
  string ixa, status, offer, ready;
  int stat, banished;
  mapping e = ([]);
  string s_font, e_font;

  if (p[name]["flags"] & flags_sepdata) {
    s_font="<font color="+DATA_COLOR+">";
    e_font="</font>";
  } else {
    s_font=e_font="";
  }

  foreach(indices(d[series][game]["p"][name]["dip"]), ixa) {
    if(enable_bridier) {
      if (!p[ixa]["bridier_index"]) {
        p[ixa]["bridier_index"]=p[ixa]["bridier_rank"]=500;
      }
    }
  }

#if ROXEN_VER == "1.2"
  Array.map(indices(d[series][game]["p"][name]["dip"]),
#else
  map_array(indices(d[series][game]["p"][name]["dip"]),
#endif
    lambda(string s, mapping m) {
    m[lower_case(s)] = s;
  }, e);

#if ROXEN_VER == "1.2"
  foreach(({ name }) + Array.sort_array(indices(e)), ixa) {
#else
  foreach(({ name }) + sort_array(indices(e)), ixa) {
#endif
    if(name != ixa)
      ixa = e[ixa];
    if(!mappingp(d[series][game]["p"][ixa])) {
      d[series][game]["p"][name]["dip"]-=([ixa:""]);
      continue;
    }
    if(ixa != name)
      {
        banished = truceban[name] && (search(truceban[name], ixa) != -1 || (search(truceban[name],"ALL") != -1));

        if(d[series]->blood != 1 && !banished)
          m_list+="<option>"+ixa;

        stat=(d[series][game]["p"][name]["dip"][ixa]>
              d[series][game]["p"][ixa]["dip"][name]?
              d[series][game]["p"][ixa]["dip"][name]:
              d[series][game]["p"][name]["dip"][ixa]);
        if(banished)
          d[series][game]["p"][name]["set_dip"][ixa]=2;

        status=dip_word(stat);
      } else {
        status = "N/A";
        banished = 0;
      }
    ready=d[series][game]["p"][ixa]["end"]?"Yes":"No";

    if(name == ixa)
      offer = "N/A";
    else
      offer=("<select name=\"d:"+ixa+"\">"+
             dip_offer(stat, d[series][game]["p"][name]["set_dip"][ixa],
                       d[series]->blood, banished, d[series]->surrdraw)+
             "</select>");

    d_list+=("<tr><th>Empire</th><th>Alien</th><th>Economic</th><th>Military</th>"+
             "<th>They Offer</th><th>You Offer</th><th>Status</th><th>Access</th></tr>"+
             "<tr><td align=center>"+(ixa == name ? "<b>You</b>":ixa)
             +"</td>"+
             "<td align=center><img src=\""+html_dir+"/aliens/"+p[ixa]["alien"]+"\"></td>"+
             "<td align=center>"+s_font+d[series][game]["p"][ixa]["e_pow"]+e_font+"</td>"+
             "<td align=center>"+s_font+d[series][game]["p"][ixa]["m_pow"]+e_font+"</td>"+
             "<td align=center>"+s_font+
             (ixa == name ? "N/A":
              dip_word(d[series][game]["p"][ixa]["set_dip"][name])+e_font+"</td>")+
             "<td align=center>"+s_font+offer+e_font+"</td>"+
             "<td align=center>"+s_font+status+e_font+"</td>"+
             "<td align=center>"+s_font+time_conv(time()-d[series][game]["p"][ixa]["l_ac"])+" ago"+e_font+"</td>"+
             "</tr><tr>"+(enable_bridier?
             "<th>Wins</th><th>Nukes</th><th>Nuked</th><th>Ruined</th><th>Bridier</th><th>Max Econ</th><th>Max Mil</th>":
             "<td></td><th>Wins</th><th>Nukes</th><th>Nuked</th><th>Ruined</th><th>Max Econ</th><th>Max Mil</th>")+
             "<th>Ready</th></tr><tr>"+(enable_bridier?"":"<td></td>")+
             "<td align=center>"+s_font+p[ixa]["wins"]+e_font+"</td>"+
             "<td align=center>"+s_font+p[ixa]["kills"]+e_font+"</td>"+
             "<td align=center>"+s_font+p[ixa]["killed"]+e_font+"</td>"+
             "<td align=center>"+s_font+p[ixa]["ruins"]+e_font+"</td>"+
             (enable_bridier?"<td align=center>"+s_font+p[ixa]["bridier_rank"]+":"+p[ixa]["bridier_index"]+e_font+"</td>":"")+
             "<td align=center>"+s_font+p[ixa]["max_e_pow"]+e_font+"</td>"+
             "<td align=center>"+s_font+p[ixa]["max_m_pow"]+e_font+"</td>"+
             "<td align=center>"+s_font+ready+e_font+"</td>"+
             "</tr>");
  }

  m_list+="<option>Broadcast";

  return (gheader("Diplomacy", name, c_pass, series, game, message)+RATIOS_LINE+
          "<center><table>"+
          d_list+
          "</table>"+
          "<table><tr><th>Send message to:</th>"
          "<td rowspan=3>"
          "<textarea name=\"missive\" rows=10 cols=50 wrap=hard></textarea>"
          "</td></tr>"
          "<tr><th><select multiple name=\"missive_to\">"+m_list+"</select>"+
          "</th></tr><tr><th><input type=submit name=\"s:y\" "
          "value=\"Send message\"></th></tr></table>"+
          "</center>"+
          footer(name));
}

mapping diplom_screen(string name, string c_pass, string series, string game, string message)
{
  return (["data":diplom_data(name, c_pass, series, game, message),
          "type":"text/html"]);
}

void recalc_targ_pop(string series, string game, string name)
{
  string ixa;
  d[series][game]["p"][name]["max_pop"]=0;

  foreach(indices(d[series][game]["s"]), ixa)
    if(d[series][game]["s"][ixa]["owner"]==name)
      d[series][game]["p"][name]["max_pop"]+=d[series][game]["s"][ixa]["max_pop"];
}

// 2.9C this is where most of the player's actions are processed
mapping play_action(object id)
{
  string name=id["variables"]["name"];
  string c_pass=id["variables"]["c_pass"];
  string series=id["variables"]["series"];
  string game=id["variables"]["game"];
  string *a;
  string index;
  string message="";

  // 2.9 make sure they don't do something nasty
  name = sterilizeString(name);
  if(!id_check(name, c_pass))
    return login_failed("Identity check failed.");

  if(!check_for_game(series, game))
    return game_list(name, c_pass, 0, split_gl, "That game no longer exists.");

  if(!mappingp(d[series][game]["p"][name]))
    return game_list(name, c_pass, 0, split_gl, "You are no longer in that game.");

  // 3.0 this may seem a little weird, but I want to prevent toolkit
  // info accesses from showing up as the player being active.
  // besides, Info really isn't an action :)
  if (id["variables"]["play"] != "Info") {
    d[series][game]["p"][name]["l_ac"]=time();
    d[series][game]["p"][name]["l_up"]=d[series][game]["u_cnt"];
  } // if

  if (p[name]["flags"] & flags_webtv) {
    d[series][game]["p"][name]["mapsize"]=(int) id["variables"]["mapsize"];
  }

  switch(id["variables"]["play"])
    {
    case "Info":
      foreach(indices(id["variables"]), index) {
        a=index/":";
        if(a[0]=="u") {
          if(a[1]=="notes") {
            if(mappingp(d[series][game]["p"][name]))
              d[series][game]["p"][name]["notes"]=replace(id["variables"]["notes"], ({"\n"}), ({"<br>"}));
            return info_screen(name, c_pass, series, game, "Game Notes updated");
          }
          if(a[1]=="gpass") {
            if(mappingp(d[series][game])) {
              if (d[series]["spawn_first"] == 1) {
                if(d[series][game]["full"]>1) {
                  return info_screen(name, c_pass, series, game, "Someone else has joined. You can't set a password.");
                } else if(d[series][game]["full"]>=d[series]["p_max"]) {
                  return info_screen(name, c_pass, series, game, "Game full - no need for password.");
                } else if(d[series][game]["gpass"] && d[series][game]["gpass"]!="") {
                  return info_screen(name, c_pass, series, game, "Game Password previously set.");
                } else {
                  d[series][game]["gpass"]=id["variables"]["gpass"];
                  return info_screen(name, c_pass, series, game, "Game Password set.");
                }
              } else {
                return info_screen(name, c_pass, series, game, "Game Password may not be set for this series.");
              }
			}
            return info_screen(name, c_pass, series, game, "Game Password set.");
          }
        }
      }
      break;

    case "Tech":
      foreach(indices(id["variables"]), index) {
        a=index/":";
        if(a[0]=="t")  {
          if(sizeof(a)>1) {
            if((d[series][game]["p"][name]["tech"]<0.0) ||
               ((int) sqrt(d[series][game]["p"][name]["tech"]) <= d[series][game]["p"][name]["devel"]) )
              return info_screen(name, c_pass, series, game, "You have no tech developments left.");

            array devs = d[series][game]["p"][name]["devs"]/" ";
            if(search((d[series]["u_tech"]/" ")-(devs),a[1])==-1) {
              if (search((d[series]["r_tech"]/" ")-(devs), a[1])==-1) {
                  if (d[series]["tr_tech"] > d[series][game]["p"][name]["dev_t"]) {
                    devs -= ({ a[1] , ""});
                    d[series][game]["p"][name]["dev_r"]--;
                    d[series][game]["p"][name]["devs"] = devs*" ";
                    d[series][game]["p"][name]["dev_t"]++;
                    return tech_screen(name, c_pass, series, game,"You have traded-in "+a[1]+".");
                  } else {
                    return tech_screen(name, c_pass, series, game, "You have no trade-ins left.");
                  } // if
              } else {
                if (d[series]["ar_tech"] > d[series][game]["p"][name]["dev_r"]) {
                  d[series][game]["p"][name]["dev_r"]++;
                } else {
                  return tech_screen(name, c_pass, series, game,
                                     "Your empire's technology is incompatable with "+a[1]+".");
                } // if
              } // if
            } // if

            d[series][game]["p"][name]["devel"]++;
            d[series][game]["p"][name]["devs"]+=" "+a[1];
            return tech_screen(name, c_pass, series, game,"You have developed "+a[1]+".");
          }  // if
        }  // if
      }  // for
      break;

    case "Ships":
      string *expl=d[series][game]["p"][name]["expl"]/" ";
      int recalc;
      foreach(indices(id["variables"]), index) {
        a=index/":";
        if(a[0]=="n")
          if(mappingp(d[series][game]["f"][a[1]]))
            // 2.9 make sure they don't put a mean ship name in
            d[series][game]["f"][a[1]]["name"]=sterilizeName(id["variables"][index], 1);
        if(a[0]=="o")
          if(mappingp(d[series][game]["f"][a[1]])) {
            if(search(get_valid_orders(series, game, a[1], expl),id["variables"][index]) != -1)
              if(id["variables"][index]=="cancel") {
                if(d[series][game]["f"][a[1]]["type"]=="Colony")
                  d[series][game]["s"][d[series][game]["f"][a[1]]["loc"]]["pop"]++;
                recalc=1;
                d[series][game]["f"]-=([a[1]:""]);
                d[series][game]["p"][name]["ship_count"]--;
              } else d[series][game]["f"][a[1]]["orders"]=id["variables"][index];
          }
      }
      if(recalc) recalc_ratios(series, game, name);
      break;

    case "Fleets":
      string create="";
      string *expl=d[series][game]["p"][name]["expl"]/" ";

      foreach(indices(id["variables"]), index) {
        a=index/":";
        switch(a[0])
          {
          case "n":
            if(mappingp(d[series][game]["t"][a[1]]))
              // 2.9 make sure they don't give a mean fleet name
              d[series][game]["t"][a[1]]["name"]=sterilizeName(id["variables"][index], 1);
            break;

          case "o":
            if(mappingp(d[series][game]["t"][a[1]]))
              if(search(get_fleet_orders(series, game, a[1], expl),id["variables"][index])!=-1) {
                d[series][game]["t"][a[1]]["orders"]=id["variables"][index];
                if(d[series][game]["t"][a[1]]["orders"]=="disband" ||
                   d[series][game]["t"][a[1]]["orders"]=="disbandall") {
                  string ixa;
                  foreach(indices(d[series][game]["f"]), ixa) {
                    if(d[series][game]["f"][ixa]["orders"] == "fleet:"+a[1])
                      if(d[series][game]["t"][a[1]]["orders"]=="disband")
                        d[series][game]["f"][ixa]["orders"] = "standby";
                      else
                        d[series][game]["f"][ixa]["orders"] = "disband";
                  }
                  d[series][game]["t"]-=([a[1]:""]);
                }
              }
            break;

          case "ns":
            if(mappingp(d[series][game]["f"][a[1]]))
              // 2.9 html fix
              d[series][game]["f"][a[1]]["name"]=sterilizeName(id["variables"][index], 1);
            break;

          case "os":
            if(mappingp(d[series][game]["f"][a[1]])) {
              if(search(get_valid_orders(series, game, a[1], expl),id["variables"][index]) != -1)
                if(id["variables"][index]=="cancel") {
                  if(d[series][game]["f"][a[1]]["type"]=="Colony")
                    d[series][game]["s"][d[series][game]["f"][a[1]]["loc"]]["pop"]++;
                  recalc=1;
                  d[series][game]["f"]-=([a[1]:""]);
                  d[series][game]["p"][name]["ship_count"]--;
                } else d[series][game]["f"][a[1]]["orders"]=id["variables"][index];
            }
            break;

          case "nf":
            if(a[1]=="y") {
              index=(string) ++d[series][game]["t_cnt"];
              d[series][game]["t"][index]=([]);
              // 2.9 html fix
              d[series][game]["t"][index]["name"]=sterilizeName(id["variables"]["fname"], 1);
              d[series][game]["t"][index]["loc"]=id["variables"]["floc"];
              d[series][game]["t"][index]["orders"]="standby";
              d[series][game]["t"][index]["owner"]=name;

              if(d[series][game]["t"][index]["name"]=="")
                d[series][game]["t"][index]["name"]="(unnamed)";
              create=d[series][game]["t"][index]["name"];
            } else if(a[1]=="n")
              create="cancel";
          }
      }
      if(recalc)
        recalc_ratios(series, game, name);
      if(create=="cancel")
        return fleets_screen(name, c_pass, series, game,
                             "Fleet creation cancelled.");
      if(create!="")
        return fleets_screen(name, c_pass, series, game,
                             "Fleet created.");
      break;

    case "Build":
      string outbuild = "";
      foreach(indices(id["variables"]), index) {
        a=index/":";
        if(a[0]=="c") {
          if(a[1]=="n")
            return info_screen(name, c_pass, series, game, "Build cancelled.");
          if(a[1]=="y") {
            foreach(indices(id->variables), index) {
              a=index/":";
              if(a[0]!="b" || a[1] != "num" || !(int)id->variables[index])
                continue;
              int i=(int) id["variables"]["b:num:"+a[2]];
              int j;

// This is the build hack !!
// ^2 bug fix as per peter bortas's suggestion
              id["variables"]["b:br:"+a[2]] = floor((float) id["variables"]["b:br:"+a[2]]);
              int br = (int)id->variables["b:br:"+a[2]];
              if( br>pow(d[series][game]["p"][name]["tech"], 0.5) || (br < 1) )
              {
                outbuild += ( "You may not build ships of BR " + br+"\n<br>" );
                continue;
              }

              if((d[series][game]["s"][id["variables"]["b:at:"+a[2]]]["owner"]!=name)
                 || (d[series][game]["s"][id["variables"]["b:at:"+a[2]]]["pop"]<d[series]["min_build"]))
              {
                outbuild += ("Can no longer build at "+id["variables"]["b:at:"+a[2]] +"\n<br>");
                continue;
              }

              if (d[series]["max_ship"] > 0) {
                if((d[series][game]["p"][name]["ship_count"] +i) > d[series]["max_ship"]) {
                  outbuild += ("Too many ships already exist.\n<br>");
                  continue;
                }
              }

              if(search(d[series][game]["p"][name]["devs"]/" ", a[2])==-1) {
                outbuild += ("Can't build ships of type "+a[2]+".\n<br>");
                continue;
              }

              int actual_built = 0;
              for(j=0; j<i; j++) {
                actual_built++;
                index=(string) ++d[series][game]["f_cnt"];
                d[series][game]["f"][index]=([]);
                // 2.9 html fix
                d[series][game]["f"][index]["name"]=sterilizeName(id["variables"]["b:name:"+a[2]], 1);
                d[series][game]["f"][index]["br"]=(float) id["variables"]["b:br:"+a[2]];
                d[series][game]["f"][index]["max_br"]=(float) id["variables"]["b:br:"+a[2]];
                d[series][game]["f"][index]["type"]=a[2];
                d[series][game]["f"][index]["loc"]=id["variables"]["b:at:"+a[2]];
                d[series][game]["f"][index]["orders"]="build";
                d[series][game]["f"][index]["owner"]=name;
                d[series][game]["f"][index]["build"]=(int)pow(d[series][game]["f"][index]["max_br"]+4.0, 2.0);
                d[series][game]["f"][index]["maint"]=(int)(d[series][game]["f"][index]["max_br"]*2.0);
                d[series][game]["f"][index]["fuel"]=(int)(d[series][game]["f"][index]["max_br"]*4.0);

                switch(d[series][game]["f"][index]["type"])
                  {
                  case "Colony":
                    if (d[series][game]["s"][d[series][game]["f"][index]["loc"]]["pop"]<2) {
                      // need to undo the ship building since there isn't
                      // enough people on the planet
                      actual_built--;
                      d[series][game]["f"]-=([index:""]);
                      break;
                    } else {
                      d[series][game]["s"][d[series][game]["f"][index]["loc"]]["pop"]-=1;
                    } // if
                  case "Science":
                  case "Cloaker":
                  case "Doomsday":
                  case "Troopship":
                  case "Terraformer":
                  case "Minesweeper":
                    d[series][game]["f"][index]["build"]+=25;
                    d[series][game]["f"][index]["maint"]+=4;
                    d[series][game]["f"][index]["fuel"]+=8;
                    break;
                  case "Morpher":
                    d[series][game]["f"][index]["build"]+=d[series]["morpher_build"];
                    d[series][game]["f"][index]["maint"]+=d[series]["morpher_maint"];
                    d[series][game]["f"][index]["fuel"]+=d[series]["morpher_maint"]*2;
                    break;
                  case "Carrier":
                    d[series][game]["f"][index]["build"]+=d[series]["carrier_build"];
                    d[series][game]["f"][index]["maint"]+=d[series]["carrier_maint"];
                    d[series][game]["f"][index]["fuel"]+=d[series]["carrier_maint"]*2;
                    break;
                  case "Jumpgate":
                    d[series][game]["f"][index]["build"]+=d[series]["jumpgate_build"];
                    d[series][game]["f"][index]["maint"]+=d[series]["jumpgate_maint"];
                    d[series][game]["f"][index]["fuel"]+=d[series]["jumpgate_maint"]*2;
                    break;
                  case "Builder":
                    d[series][game]["f"][index]["build"]+=d[series]["builder_build"];
                    d[series][game]["f"][index]["maint"]+=d[series]["builder_maint"];
                    d[series][game]["f"][index]["fuel"]+=d[series]["builder_maint"]*2;
                    break;
                  case "Stargate":
                  case "Engineer":
                    d[series][game]["f"][index]["build"]+=100;
                    d[series][game]["f"][index]["maint"]+=16;
                    d[series][game]["f"][index]["fuel"]+=32;
                    break;
                  case "Minefield":
                    d[series][game]["f"][index]["build"]+=10;
                    d[series][game]["f"][index]["maint"]+=2;
                    d[series][game]["f"][index]["fuel"]=0;
                    break;
                  case "Satellite":
                    d[series][game]["f"][index]["build"]-=10;
                    d[series][game]["f"][index]["maint"]-=2;
                    if(!d[series][game]["f"][index]["maint"]) d[series][game]["f"][index]["maint"]=2;
                    d[series][game]["f"][index]["fuel"]=0;
                    break;
                  default:
                    break;
                  } // switch
              } // foreach

              d[series][game]["p"][name]["ship_count"] += actual_built;

              //outbuild += (id["variables"]["b:num:"+a[2]]+" of "+a[2]+
              outbuild += (actual_built+" of "+a[2]+
                           " ship "+id["variables"]["b:name:"+a[2]]+" (BR "+
                           id["variables"]["b:br:"+a[2]]+
                           ") to be built at "+d[series][game]["s"][id["variables"]["b:at:"+a[2]]]["name"]+
                           " ("+id["variables"]["b:at:"+a[2]]+").\n<br>");

            }
          }
          recalc_ratios(series, game, name);
          return build_screen(name, c_pass, series, game, outbuild);
        }
      }
      break;

    case "Systems":
      string planet="";

      foreach(indices(id["variables"]), index) {
        a=index/":";
        if(a[0]=="n") {
          if(mappingp(d[series][game]["s"][a[1]])) {
            if(d[series][game]["s"][a[1]]["owner"]==name) {
              // 2.9 html fix
              d[series][game]["s"][a[1]]["name"]=sterilizeName(id["variables"][index], 0);
            }
          }
        }
        if(a[0]=="mp") {
          if(mappingp(d[series][game]["s"][a[1]])) {
            if(d[series][game]["s"][a[1]]["owner"]==name) {
              d[series][game]["s"][a[1]]["max_pop"]=(int) id["variables"][index];
            }
          }
        }
        if(a[0]=="m") {
          a=a[1]/".";
          planet=a[0];
        }
      }

      recalc_targ_pop(series, game, name);
      if(planet!="") return systems_screen(name, c_pass, series, game, "", planet, 0);
      break;

    case "Map":
      string planet="";

      if ((p[name]["flags"] & flags_webtv) && (d[series][game]["p"][name]["mapsize"] != 0)) {
        d[series][game]["p"][name]["mapanchor"]=id["variables"]["mapanchor"];
      }

      foreach(indices(id["variables"]), index) {
        a=index/":";
        if(a[0]=="m") {
          a=a[1]/".";
          planet=a[0];
        }
      }
      if(planet!="") return systems_screen(name, c_pass, series, game, "", planet, 0);
      break;

    case "Diplomacy":
      string missive, ixa;
      mixed sent;
      foreach(indices(id["variables"]), index) {
        a=index/":";
        if(a[0]=="d")
          if(search(indices(d[series][game]["p"][name]["dip"]), a[1])!=-1)
            if((((int) id["variables"][index]-d[series][game]["p"][name]["dip"][a[1]]<2) &&
                ((int) id["variables"][index]-d[series][game]["p"][name]["dip"][a[1]]>-2)) ||
               ((int) id["variables"][index]==0))
              d[series][game]["p"][name]["set_dip"][a[1]]=(int) id["variables"][index];
        if(a[0]=="s")
          if(a[1]=="y") {
            if(id->variables->missive_to)  {
              string sendto, tmpm;

              // 2.9C these were already fixed, yeah
              missive=sterilizeString(id["variables"]["missive"]);

              foreach(id->variables->missive_to/"\0", sendto) {
                if(sendto=="Broadcast") {
                  tmpm=name+" broadcasted at "+nctime(time())+":<pre>"+missive+"</pre><br>";

                  foreach(indices(d[series][game]["p"]), ixa)
                     d[series][game]["p"][ixa]["missive"]+=tmpm;

                  if(!sent)
                    sent = 1;
                } else {
                  if(mappingp(d[series][game]["p"][sendto]))
                    d[series][game]["p"][sendto]["missive"] +=
                      name+" sent at "+nctime(time())+":<pre>"+missive+"</pre><br>";

                  if(!stringp(sent))
                    sent = sendto;
                  else
                    sent += ", "+sendto;
                }
              }                       // for
            } else
              sent=-1;
          }
      }
      if(sent==1)
        return diplom_screen(name, c_pass, series, game,
                             "Message broadcasted to everyone.");
      else if(sent==-1)
        return diplom_screen(name, c_pass, series, game,
                             "Message sent to no one.");
      else if(stringp(sent)) {
        array foo;
        if(sizeof(foo = sent/", ") > 1)
          sent = (foo[0..sizeof(foo)-2]*", ") + " and "+ foo[-1];
        return diplom_screen(name, c_pass, series, game,
                               "Message sent to "+sent+".");
      }
      break;

    default:
      message="Invalid play action.";
    }

  // this checks which button has been selected
  foreach(indices(id["variables"]), index) {
    a=index/".";                        // strip '.' in case of graphic buttons
    a=a[0]/":";
    if(a[0]=="p") break;
  }
//  if(!a || sizeof(a) < 2) {             // no option selected ..ie autoreload
  if( a[0] != "p") {                   // no option selected ..ie autoreload
    a = ({"", ""});
    a[1] = lower_case(id["variables"]["play"]);
  }

  switch(a[1])
    {
    case "info":
      return info_screen(name, c_pass, series, game, message);
    case "tech":
      return tech_screen(name, c_pass, series, game, message);
    case "ships":
      return ships_screen(name, c_pass, series, game, message);
    case "fleets":
      return fleets_screen(name, c_pass, series, game, message);
    case "build":
      return build_screen(name, c_pass, series, game, message);
    case "systems":
      return systems_screen(name, c_pass, series, game, message, "", 0);
    case "map":
      int mapsize=d[series][game]["p"][name]["mapsize"];
      if ((p[name]["flags"] & flags_webtv) && (mapsize!=0)) {
        string anchor=d[series][game]["p"][name]["mapanchor"];
        if (!anchor || anchor=="") {
          string *expl=d[series][game]["p"][name]["expl"]/" ";
          foreach (expl, index) {
            if (d[series][game]["s"][index]["home"]==name) {
              anchor=index;
              break;
            }
            d[series][game]["p"][name]["mapanchor"]=anchor;
          }
        }
        return map_screen(name, c_pass, series, game, message, mapsize, anchor);
      } else {
        return map_screen(name, c_pass, series, game, message, 0, "");
      }
    case "diplomacy":
      return diplom_screen(name, c_pass, series, game, message);
    case "data":
  	  string missive=d[series][game]["p"][name]["missive"];
	  // 3 strangeness where missive was being 0
	  if (missive=="0") {
		missive = "";
        d[series][game]["p"][name]["missive"]="";
      } // if
      return (["data":
//???              missive + "<!--AWT-->" +
              info_data(name, c_pass, series, game, "") + "<!--AWT-->" +
              ships_data(name, c_pass, series, game, "") + "<!--AWT-->" +
              fleets_data(name, c_pass, series, game, "") + "<!--AWT-->" +
              systems_data(name, c_pass, series, game, "", "", 0) + "<!--AWT-->" +
              diplom_data(name, c_pass, series, game, ""),
              "type":"text/html"]);

    case "datanm":
  	  string missive=d[series][game]["p"][name]["missive"];
      string data =
              info_data(name, c_pass, series, game, "") + "<!--AWT-->" +
              ships_data(name, c_pass, series, game, "") + "<!--AWT-->" +
              fleets_data(name, c_pass, series, game, "") + "<!--AWT-->" +
              systems_data(name, c_pass, series, game, "", "", 0) + "<!--AWT-->" +
              diplom_data(name, c_pass, series, game, "");
      d[series][game]["p"][name]["missive"]=missive;
      return (["data": data, "type":"text/html"]);


    case "unend":
      if((d[series][game]["u_cnt"] > 0) && (time() <= d[series][game]["u_last"])) {
        string missive=d[series][game]["p"][name]["missive"];
        // 3 strangeness where missive was being 0
        if (missive=="0") {
          missive = "";
          d[series][game]["p"][name]["missive"]="";
        }
        return (["data":
//???              missive + "<!--AWT-->" +
              info_data(name, c_pass, series, game, message+" An update has already occurred."),
              "type":"text/html"]);
      } // if

      d[series][game]["p"][name]["end"]=0;
      d[series][game]["p"][name]["l_ac"]=time();

      string missive=d[series][game]["p"][name]["missive"];
      // 3 strangeness where missive was being 0
      if (missive=="0") {
        missive = "";
        d[series][game]["p"][name]["missive"]="";
      }
      return (["data":
//???            missive + "<!--AWT-->" +
            info_data(name, c_pass, series, game, message+" You are now unready for an update."),
            "type":"text/html"]);

    case "end":
      if((d[series][game]["u_cnt"] > 0) && (time() <= d[series][game]["u_last"])) {
        string missive=d[series][game]["p"][name]["missive"];
        // 3 strangeness where missive was being 0
        if (missive=="0") {
          missive = "";
          d[series][game]["p"][name]["missive"]="";
        }
        return (["data":
//???              missive + "<!--AWT-->" +
              info_data(name, c_pass, series, game, message+" An update has already occurred."),
              "type":"text/html"]);
      } // if

      d[series][game]["p"][name]["end"]=1;
      d[series][game]["p"][name]["l_ac"]=time();
      d[series][game]["p"][name]["l_up"]=d[series][game]["u_cnt"];

      int test=end_turn_update(series, game);
      if(check_for_game(series, game) && !mappingp(d[series][game]["p"][name])) {
        return game_list(name, c_pass, 0, split_gl, "");
      }
      if(test==1) {
        string missive=d[series][game]["p"][name]["missive"];
        // 3 strangeness where missive was being 0
        if (missive=="0") {
          missive = "";
          d[series][game]["p"][name]["missive"]="";
        }
        return (["data":
//???              missive + "<!--AWT-->" +
              info_data(name, c_pass, series, game, message+" An update has occurred."),
              "type":"text/html"]);

      } // if
      if(test==2) {
        string missive=d[series][game]["p"][name]["missive"];
        // 3 strangeness where missive was being 0
        if (missive=="0") {
          missive = "";
          d[series][game]["p"][name]["missive"]="";
        }
        return (["data":
//???              missive + "<!--AWT-->" +
              info_data(name, c_pass, series, game, message+" You are now ready for an update."),
              "type":"text/html"]);
      } // if
      return game_list(name, c_pass, 0, split_gl, "That game no longer exists.");


    case "quit":
      string ixa, ixb;
      if (d[series][game]["full"] != 1)
        return info_screen(name, c_pass, series, game,
                           "Someone else has joined. You can't leave.");

      else {
        string abandoner;
        foreach(indices(d[series][game]["p"]), ixa) {
          p[ixa]["missive"]+=("Too bad  "+series+" "+game+
                              " didn't work out, maybe next time.\n");
          abandoner = ixa;
        }
        // 3 check to see if we spawn first
        if (d[series]["spawn_first"] == 0) {
          spawn_game(series);
          //d[series][game]["spawn"]=1;
        } // if
        d[series][game]["history"] += "<tr><td colspan=2>Game abandoned " + nctime(time()) + "</td></tr></table>";
        add_history ( series, game, "Abandoned by "+abandoner);

        return game_list(name, c_pass, 0, split_gl, "That game no longer exists.");
      }

    case "exit":
      return game_list(name, c_pass, 0, split_gl, "");

    default:
      return info_screen(name, c_pass, series, game,
                         message+" Invalid button identifier -> "+a[0]+","+a[1]+","+a[2]);
    }
}

mapping get_game_pass(string name, string c_pass, string series, string game)
{

  int gbutn=0;                          // assume standard buttons
  if ( name != "" )
    gbutn = (p[name]["flags"] & flags_gbutton);

  return (["data":
          header("Password-Protected Game", name)+
          "<input type=hidden name=\"name\" value=\""+name+"\">"+
          "<input type=hidden name=\"c_pass\" value=\""+c_pass+"\">"+
          "<input type=hidden name=\"series\" value=\""+series+"\">"+
          "<input type=hidden name=\"game\" value=\""+game+"\">"+
          "<input type=hidden name=\"action\" value=\"g_list\">"+
          pageheader("Password-Protected Entry to "+series+" "+game+" for "+name, name)+"<p>"+
          (gbutn?
            (split_gl?
              "<input type=image src=\""+theme_dir(name)+"/game.gif\" name=\"l:gameown\" alt=\"Your Games\">"+
              "<input type=image src=\""+theme_dir(name)+"/gameo.gif\" name=\"l:gameopen\" alt=\"Open Games\">"+
              "<input type=image src=\""+theme_dir(name)+"/gamen.gif\" name=\"l:gamenew\" alt=\"New Games\">"
            :
              "<input type=image src=\""+theme_dir(name)+"/game.gif\" name=\"l:game\" alt=\"Games\">")+
            "<input type=image src=\""+theme_dir(name)+"/profile.gif\" name=\"l:prof\" alt=Profile>"+
            "<input type=image src=\""+theme_dir(name)+"/stats.gif\" name=\"l:stat\" alt=Stats>"+
            "<input type=image src=\""+theme_dir(name)+"/history.gif\" name=\"l:hist\" alt=History>"+
            "<input type=image src=\""+theme_dir(name)+"/exit.gif\" name=\"l:exit\" alt=Exit>"
          : "<font color=\"#000000\">"+
            (split_gl?
              "<input type=submit name=\"l:gameown\" value=\"Game List\">"+
              "<input type=submit name=\"l:gameopen\" value=\"Open Game List\">"+
              "<input type=submit name=\"l:gamenew\" value=\"New Game List\">"
            :
              "<input type=submit name=\"l:game\" value=\"Game List\">")+
            "<input type=submit name=\"l:prof\" value=\"Edit Profile\">"+
            "<input type=submit name=\"l:stat\" value=\"Stat Viewer\">"+
            "<input type=submit name=\"l:hist\" value=\"History Viewer\">"+
            "<input type=submit name=\"l:exit\" value=\"Exit\"></font><br>")+
          disp_g_missive(name)+"<br>"+
          "<p><img src=\""+theme_dir(name)+"/spacerule.gif\" height=10  width=100%><p>"+
          "<center>"+series+" "+game+" has been password-protected by its creator.<br>"+
          "Please supply the password to enter the game:<br>"+
          "<table cellpadding=4><tr><td><input type=password name=\"gpass\" size=10 maxlength=10></td>"+
          "<td><font color=\"#000000\"><input type=submit name=\"l:gpass\" value=\"Submit Password\"></font></td></tr></table>"+
          "<input type=hidden name=\"enter:gpass\"></center>"+footer(name),
          "type":"text/html"]);
}

mapping login_game(string name, string c_pass, string series, string game, int cl, string gpass)
{
  string ixa, ixb;
  float j=d[series]["init_tech"];

  foreach(indices(d[series][game]["p"]), ixa)
    if(ixa==name)
      return info_screen(name, c_pass, series, game,
                         "Welcome Back, "+name+".");

  if(d[series][game]["full"]>=d[series]["p_max"])
    return game_list(name, c_pass, 0, split_gl,
                     "Sorry, that game filled up while you were logging in.");

  if(d[series][game]["gpass"] && d[series][game]["gpass"]!="") {
    if(gpass!=d[series][game]["gpass"]) {
      return get_game_pass(name, c_pass, series, game);
    }
  }

  if(enable_bridier && d[series][game]["bridier"]) {
    if(d[series][game]["full"]==0) {
      d[series][game]["challenge"] = cl;
    } else {
      foreach(indices(d[series][game]["p"]), ixa) {
        if (!d[series][game]["gpass"] || d[series][game]["gpass"]=="") {
          mapping nbw = calc_bridier (d[series][game]["p"][ixa]["bridier_rank"], d[series][game]["p"][ixa]["bridier_index"],
              p[name]["bridier_rank"], p[name]["bridier_index"]);
          if ((!d[series][game]["gpass"] || d[series][game]["gpass"]=="") && (nbw ["wrank"] - d[series][game]["p"][ixa]["bridier_rank"]) < d[series][game]["challenge"]) {
            return game_list(name, c_pass, 0, split_gl,
                       "Sorry, your Bridier status is inadequate for that game.");
          }
        }
      }
    }
  }

  if( p[name]->wins < d[series]->wins
      || (p[name]->wins > d[series]->winmax && d[series]->winmax >0) )
    return game_list(name, c_pass, 0, split_gl,
                     "You have either too many or too few wins for that series.");

  //if (search(d[series]["invited"], p[p[name]["sponsor"]]["sponsor"])==-1) {
    //return game_list(name, c_pass, 0,
                     //"You are not welcome in that type of game yet.");
  //} // if

  d[series][game]["history"] += "<tr><td>"+d[series][game]["u_cnt"]+"</td><td>Empire " + name + " joins the game " + nctime(time()) + ".</td></tr>";
  d[series][game]["full"]++;

// To prevent games which were spawned by an earlier version of the code from being unable to allocate resources:
  if (!d[series][game]["avg_ag"])
    d[series][game]["avg_ag"] = d[series]["avg_ag"] + random (d[series]["range_ag"]+1);
  if (!d[series][game]["avg_min"])
    d[series][game]["avg_min"] = d[series]["avg_min"] + random (d[series]["range_min"]+1);
  if (!d[series][game]["avg_fuel"])
    d[series][game]["avg_fuel"] = d[series]["avg_fuel"] + random (d[series]["range_fuel"]+1);

  // 2.9 fix, remove hard coded 100's and replaced them with home_rsrc
  d[series][game]["p"][name]=([]);
  d[series][game]["p"][name]["min"]=d[series]["home_min"];
  d[series][game]["p"][name]["fuel"]=d[series]["home_fuel"];
  d[series][game]["p"][name]["ag"]=d[series]["home_ag"];
  d[series][game]["p"][name]["pop"]=d[series]["home_ag"];
  d[series][game]["p"][name]["vis_pop"]=d[series]["home_ag"];
  d[series][game]["p"][name]["max_pop"]=(d[series]["home_min"]>d[series]["home_fuel"])?d[series]["home_min"]:d[series]["home_fuel"];
  d[series][game]["p"][name]["vis_max_pop"]=d[series][game]["p"][name]["max_pop"];
  d[series][game]["p"][name]["min_r"]=(float) 1000;
  d[series][game]["p"][name]["fuel_r"]=(float) 1000;
  d[series][game]["p"][name]["ag_r"]=(float) 1;
  d[series][game]["p"][name]["maint"]=0;
  d[series][game]["p"][name]["build"]=0;
  d[series][game]["p"][name]["f_use"]=0;
  d[series][game]["p"][name]["ship_count"]=0;
  d[series][game]["p"][name]["tech_d"]=(float) d[series]["t_mult"];
  d[series][game]["p"][name]["l_ac"]=time();
  d[series][game]["p"][name]["l_up"]=d[series][game]["u_cnt"];

  if (enable_bridier && d[series][game]["bridier"]) {
    if (!p[name]["bridier_index"]) {
      p[name]["bridier_rank"] = 500;
      p[name]["bridier_index"] = 500;
    }
    d[series][game]["p"][name]["bridier_rank"]=p[name]["bridier_rank"];
    d[series][game]["p"][name]["bridier_index"]=p[name]["bridier_index"];
  }

  // 3c new players get the max of the current players
  foreach(indices(d[series][game]["p"]), ixa) {
    if(ixa==name) continue;
    if ((float) d[series][game]["p"][ixa]["tech"]>j)
      j=(float) d[series][game]["p"][ixa]["tech"];
  }

  // check for the max num of planets
  d[series][game]["num_planets"] += d[series][game]["s_add"];

  d[series][game]["p"][name]["tech"]=(float) j;

  d[series][game]["p"][name]["dev_r"] = 0;
  d[series][game]["p"][name]["dev_t"] = 0;
  d[series][game]["p"][name]["devel"]= 0; // (int) sqrt((float) j);
  d[series][game]["p"][name]["devs"]=d[series]["d_tech"];

  d[series][game]["p"][name]["missive"]="";
  d[series][game]["p"][name]["notes"]="";
  d[series][game]["p"][name]["e_pow"]=get_econ_pow(series, game, name);
  d[series][game]["p"][name]["m_pow"]=get_mil_pow(series, game, name);

  d[series][game]["p"][name]["dip"]=([]);
  d[series][game]["p"][name]["set_dip"]=([]);
  d[series][game]["p"][name]["end"]=0;

  // changed to spawn first
  if (d[series]["spawn_first"]==0) {
    if(d[series][game]["full"] >= d[series]["p_max"]) {
       //!d[series][game]["spawn"]) {
      spawn_game(series);
    }
  } else {
    if ((d[series][game]["full"] == 1)) {
        //(!d[series][game]["spawn"])) {
      spawn_game(series);
    } // if
  } // if

  // 2.9 now is the new start time
  if (d[series][game]["full"] == 2) {
  	d[series][game]["u_first"] = time() + d[series]["u_delay"] + d[series]["u_time"];
  } // if

  // 2.9 - If this is a mirror game and it has just been filled
  // then just plop the player in - needs to be changed
  // to work for bigger than 2 player games
  if ((d[series]["layout"] == 2) && (d[series][game]["full"] == 2)) {
    add_player (name, series, game);
    return info_screen(name, c_pass, series, game, "Welcome to "+series+" "+game+", "+name+".");
  } // if


  mapping chain;
  array s_reg, s_j, j_list;
  array s_sys, d_sys;
  int dx, dy, dz, sx, sy;

  int s_add = d[series][game]["s_add"] || d[series]["s_add"];  // in case of older series

  do {
    chain=([]);
    s_reg=indices(d[series][game]["s"]);

    if(!sizeof(s_reg)) {
      // 2.9 added factor of 5 to help make sure mirror directions
      // aren't too obvious
//      dx=dy=d[series]["p_max"]*d[series]["s_add"]*5;
//      sx=sy=0;

      // Attempt at a pseudo-normal distribution, around zero
      // dx=random(123)+random(123)+random(123)+random(123)+random(123)
      // dy=random(123)+random(123)+random(123)+random(123)+random(123)
//      dx=random(d[series]["p_max"]) * random(s_add) +
//        random(d[series]["p_max"]) * random(s_add) +
//        random(d[series]["p_max"]) * random(s_add) +
//        random(d[series]["p_max"]) * random(s_add) -
//        random(d[series]["p_max"] * s_add);
//      dy=random(d[series]["p_max"]) * random(s_add) +
//        random(d[series]["p_max"]) * random(s_add) +
//        random(d[series]["p_max"]) * random(s_add) +
//        random(d[series]["p_max"]) * random(s_add) -
//        random(d[series]["p_max"] * s_add);
// Let's get rid of these negative co-ordinates, without giving *too* much away...
      dz=d[series]["p_max"]*s_add;
      dx=(2*dz)+random(dz);
      dy=(2*dz)+random(dz);
      sx=sy=0;

    } else {

      do {
        do {
          s_sys=s_reg[random(sizeof(s_reg))]/",";
          s_j=d[series][game]["s"][s_sys[0]+","+s_sys[1]]["jump"]/" ";
        } while(sizeof(s_j)==4);

        j_list=({((int) s_sys[0]+1)+","+s_sys[1],
                 s_sys[0]+","+((int) s_sys[1]+1),
                 ((int) s_sys[0]-1)+","+s_sys[1],
                 s_sys[0]+","+((int) s_sys[1]-1)});

        do {
          d_sys=j_list[random(sizeof(j_list))]/",";
        } while(search(s_j, d_sys[0]+","+d_sys[1])!=-1);
      } while(search(s_reg, d_sys[0]+","+d_sys[1])!=-1);

      dx=(int) d_sys[0];
      dy=(int) d_sys[1];
      sx=(int) s_sys[0];
      sy=(int) s_sys[1];

    }

    chain=add_chain(chain, series, game, dx, dy, sx, sy);

//  } while(sizeof(chain)!=d[series]["s_add"]);
  } while(sizeof(chain) != s_add);

  // 2.9 if we made it this far it means we now need to
  // generate the mirror - might need changing for
  // multiple players above 2
  if (d[series]["layout"] == 2) {
    gen_mirror (chain, series, game, name);
    return info_screen(name, c_pass, series, game, "Welcome to "+series+" "+game+", "+name+".");
  } // if


  s_reg=indices(chain);

  string home;
  string *h_jmp;
  int fail;

  do {
    fail=0;
    home=s_reg[random(sizeof(s_reg))];
    h_jmp=chain[home]["jump"]/" ";
    foreach(h_jmp, ixa)
      if(search(indices(chain), ixa)==-1)
        if(search(indices(d[series][game]["s"]), ixa)!=-1)
          if(d[series][game]["s"][ixa]["home"]!="") fail=1;
  } while(fail==1);

  chain[home]["min"]=d[series]["home_min"];
  chain[home]["fuel"]=d[series]["home_fuel"];
  chain[home]["ag"]=d[series]["home_ag"];
  chain[home]["pop"]=d[series]["home_ag"];
  chain[home]["vis_pop"]=d[series]["home_ag"];
  chain[home]["max_pop"]=(d[series]["home_min"]>d[series]["home_fuel"])?d[series]["home_min"]:d[series]["home_fuel"];
  chain[home]["vis_max_pop"]=chain[home]["max_pop"];
  chain[home]["owner"]=name;
  chain[home]["home"]=name;
  chain[home]["name"]=name;

  int min_t, ag_t, fuel_t;
  int count=sizeof(chain);
//  min_t=(d[series]["s_add"]-1)*d[series]["avg_min"];
//  ag_t=(d[series]["s_add"]-1)*d[series]["avg_ag"];
//  fuel_t=(d[series]["s_add"]-1)*d[series]["avg_fuel"];
  min_t  = (s_add-1)*d[series][game]["avg_min"];
  ag_t   = (s_add-1)*d[series][game]["avg_ag"];
  fuel_t = (s_add-1)*d[series][game]["avg_fuel"];

  foreach(indices(chain), ixa) {
    if(home==ixa) continue;

    chain[ixa]["name"]="System "+ixa;
    chain[ixa]["pop"]=0;
    chain[ixa]["vis_pop"]=0;
    chain[ixa]["owner"]="";
    chain[ixa]["home"]="";

    if(--count>1) {
      min_t-=chain[ixa]["min"]=random(min_t*2/count);
      ag_t-=chain[ixa]["ag"]=random(ag_t*2/count);
      fuel_t-=chain[ixa]["fuel"]=random(fuel_t*2/count);
    } else {
      chain[ixa]["min"]=min_t;
      chain[ixa]["ag"]=ag_t;
      chain[ixa]["fuel"]=fuel_t;
    }
    chain[ixa]["max_pop"]=chain[ixa]["min"]>chain[ixa]["fuel"]?chain[ixa]["min"]:chain[ixa]["fuel"];
    chain[ixa]["vis_max_pop"]=chain[ixa]["max_pop"];
  }

  d[series][game]["s"]+=chain;
  d[series][game]["p"][name]["expl"]=home;

  string *jump;
  foreach(indices(d[series][game]["s"]), ixa) {
    jump=({});
    j_list=d[series][game]["s"][ixa]["jump"]/" ";
    foreach(j_list, ixb)
      if(search(indices(d[series][game]["s"]), ixb)!=-1) {
        jump+=({ixb});
        if(search(d[series][game]["s"][ixb]["jump"]/" ", ixa)==-1)
          d[series][game]["s"][ixb]["jump"]+=" "+ixa;
      }
    d[series][game]["s"][ixa]["jump"]=jump*" ";
  }

  return info_screen(name, c_pass, series, game, "Welcome to "+series+" "+game+", "+name+".");
}

string alien_list(string current, int start)
{
  int i;
  int alienend;
  string a_list="";
  string s_icon;
  string e_icon;

  if ( start == 0) start = 1;
  alienend = start+19;                  // twenty icons displayed
  if ( alienend > aliens ) alienend = aliens;

  a_list = "<font color="+server_color+">";

  s_icon = (string) start;
  e_icon = (string) alienend;
  a_list += "Icons "+s_icon+" to "+e_icon+"<p>";

  for(i=start; i<=alienend; i++) {
    a_list+="<input type=image src=\""+html_dir+"/aliens/alien"+i+".gif\" name=\"alien:alien"+i+".gif\" alt=\"alien "+i+"\"";

    if ( "alien"+i+".gif" != current )
      a_list+=" border=\"0\"";
    else
      a_list+=" border=\"2\"";
    a_list+=">";
  }
  a_list+="</font>";
  return a_list;
}


/* ---------- New Player Options display-------------- */
mapping create_player(string name, string pass)
{
  string glsort_table = "";
  int i;

  for (i = 0; i < 3; i++) {
    glsort_table += "<tr><td><b>";
    switch (i) {
    case 0:
      glsort_table += "1st";
      break;
    case 1:
      glsort_table += "2nd";
      break;
    case 2:
      glsort_table += "3rd";
      break;
    }
    glsort_table += ":</b></td><td><select name=gl:order:"+i+"><option "+(i==0?"selected ":"")+"value=0>"+
        "Update Time<option "+(i==1?"selected ":"")+"value=1>Tech Advance<option "+(i==2?"selected ":"")+"value=2>"+
        "Number of Players<option value=3>Systems per Player<option value=4>Average Resources<option value=5>Diplomacy</select></td>"+
        "<td><select name=gl:ad:"+i+"><option selected value=0>Ascending<option value=1>Descending</select></td></tr>";
  }

  return (["data":
          header("New Empire", "")+
          "<input type=hidden name=\"name\" value=\""+name+"\">"+
          "<input type=hidden name=\"pass\" value=\""+pass+"\">"+
          "<input type=hidden name=\"action\" value=\"create\">"+
          pageheader( "Creating a New Empire for "+name, "" )+
          "<p><font color=\"#000000\"><input type=submit name=\"cancel\" value=\"Cancel\"></font>"+
          "<p><img src=\""+theme_dir("")+"/spacerule.gif\" height=10 width=100%>"+
          "<table cellspacing=0 valign=\"center\" ><b>"+
//          "<tr><td align=right valign=center><b>Password :</b></td>"+
//            "<td><input type=password size=8 name=password value="+pass+"></td></tr>"+
          "<tr><td align=right valign=center><b>Password (again) :</b></td>"+
            "<td><input type=password size=8 name=pass_check value=\"\"></td>"+
            "<td><font color=\""+MISS_COLOR+"\"><b>Verify your password !!</b></font></td></tr>"+
          "<tr><td align=right valign=center><b>Real Name :</b></td>"+
            "<td><input type=string size=20 name=real_name value=\"\"></td><td>Nice to know..</td></tr>"+
          "<tr><td align=right valign=center><b>Email :</b></td>"
            "<td><input type=string size=30 name=e-mail value=\"\"> <b>Hidden :</b> <input type=checkbox name=hide-mail></td><td>You should register your e-mail address here in case you ever need to ask the administrator to remind you of your password. If you don't want other players to be able to see the address, check the Hidden box.</td></tr>"+
          "<tr><td align=right valign=center><b>Icon :</b></td>"+
            "<td><input type=image src=\""+html_dir+"/aliens/alien1.gif\" name=\"icon\" border=1></td>"+
            "<td><font color=\""+MISS_COLOR+"\"><b>Click icon to change</b></font></td></tr>"+
          "<tr><td align=right valign=center><b>Homepage URL :</b></td>"+
            "<td><input type=string size=40 name=url value=\"\"></td></tr>"+
          "<tr><td align=right valign=center><b>Theme URL :</b></td>"+
            "<td><input type=string size=40 name=theme_url value=\"\"></td>"+
            "<td>If you have a Web-based directory of custom images you would like to be used with your game pages, please type its address here. If you are unsure, leave the field blank, as an incorrect entry can adversely affect your display.</td></tr>"+

/*
           "<tr><td align=right valign=center>Time offset :</b></td>"+
            "<td><input size=3 type=number name=\"time_offset\" value=\"0\">"+
            "<select name=\"time_unit\"><option selected value=0>Minutes"+
            "<option value=1>Hours</select></td></tr>"+
*/
 /*       "<TR><TD ALIGN=right>Clan member :</TD>"+
          "<TD><SELECT name=\"clan\"><OPTION selected value=0>Independant"+
          "<OPTION value=1>Avengers<OPTION value=2>Ice Nomads"+
          "<OPTION value=3>Kaa<OPTION value=4>Red Triad<OPTION value=5>W.A.R."+
          "<OPTION value=6>Five Rings<OPTION value=7>Untouchables<OPTION value=8>Heroes Reborn"+
          "<OPTION value=9>Hell<OPTION value=10>Juggernaut<OPTION value=11>Apocalypse</SELECT></TD></TR>"+

          // 3 Team stuff
          "<tr><td align=right><b>Current Team:</b></td><td>Independant</td></tr>"+
          "<tr><td align=right><b>Seek new team:</b></td>"+
            "<td><input type=checkbox name=\"seeking_sponsor\"></td></tr>"+
*/

          "<tr><td align=right valign=center><b>Options :</b></td>"+
            "<td><table><tr><td><input name=\"flag:1\" type=checkbox checked><nobr>Background Image</nobr>"+
            "<br><input name=\"flag:4\" type=checkbox><nobr>Enhanced Ratios Line</nobr>"+
            "<br><input name=\"flag:32\" type=checkbox><nobr>Show Next BR</nobr>"+
            "<br><input name=\"flag:64\" type=checkbox><nobr>Separate Data Color</nobr>"+
            "<br><input name=\"flag:128\" type=checkbox><nobr>WebTV Mode</nobr></td>"+

          "<td align=right valign=center><b>Javascript Options :</b></td>"+
            "<td><input name=\"flag:2\" type=checkbox checked><nobr>Auto Update</nobr>"+
            "<br><input name=\"flag:8\" type=checkbox><nobr>Graphical buttons</nobr>"+
            "<br><input name=\"flag:16\" type=checkbox><nobr>Update Countdown</nobr>"+
            "<br><input name=\"flag:256\" type=checkbox><nobr>Sensitive Map</nobr></td></tr></table></td></tr>"+

          "<tr><td align=right valign=center><b>Game List<br>Sort Order :</b></td>"+
            "<td><table>"+glsort_table+"</table></td><td>The order in which Game Lists display series is determined by the preferences you specify here. \"1st\" is the primary order; series of equal primary value are sorted by the \"2nd\" criterion, etc.</td></tr>"+

          "<tr><td align=right valign=center><b>Comments :</b></td>"+
            "<td><textarea wrap=hard rows=8 cols=50 name=comments></textarea></td></tr>"+
          "<tr><td align=right valign=center><b>Victory Sneer :</b></td>"+
            "<td><textarea wrap=hard rows=8 cols=50 name=sneer></textarea></td></tr>"+
          "<tr><td align=right valign=top colspan=\"2\"><input type=submit  name=\"save\" value=\"Save Profile\"></td></tr>"+
          "</table></b>&nbsp;</td></tr></table></form>"+
          footer(""),
          "type":"text/html"]);
}


/* ---------- Player Profile display-------------- */
mapping edit_profile(string name, string c_pass, string message)
{
  if(!stringp(p[name]["comments"])) p[name]["comments"]=(string) "";
  if(!stringp(p[name]["sneer"])) p[name]["sneer"]=(string) "";
  if(!stringp(p[name]["glsort"])) p[name]["glsort"]="001020";

  int pflags = p[name]["flags"];
  string glsort = p[name]["glsort"];

  string glsort_table = "";
  int i, j, k;

  for (i = 0; i < 3; i++) {
    glsort_table += "<tr><td><b>";
    switch (i) {
    case 0:
      glsort_table += "1st";
      break;
    case 1:
      glsort_table += "2nd";
      break;
    case 2:
      glsort_table += "3rd";
      break;
    }
    j = i * 2;
    k = j + 1;
    glsort_table += ":</b></td><td><select name=gl:order:"+i+"><option "+((glsort[j..j]=="0")?"selected ":"")+"value=0>"+
        "Update Time<option "+((glsort[j..j]=="1")?"selected ":"")+"value=1>Tech Advance<option "+((glsort[j..j]=="2")?"selected ":"")+"value=2>"+
        "Number of Players<option "+((glsort[j..j]=="3")?"selected ":"")+"value=3>Systems per Player<option "+((glsort[j..j]=="4")?"selected ":"")+"value=4>"+
        "Average Resources<option "+((glsort[j..j]=="5")?"selected ":"")+"value=5>Diplomacy</select></td>"+
        "<td><select name=gl:ad:"+i+"><option "+((glsort[k..k]=="0")?"selected ":"")+"value=0>Ascending"+
        "<option "+((glsort[k..k]=="1")?"selected ":"")+"value=1>Descending</select></td></tr>";
  }

  return (["data":
          header("Edit Profile", name)+
          "<input type=hidden name=\"name\" value=\""+name+"\">"+
          "<input type=hidden name=\"c_pass\" value=\""+c_pass+"\">"+
          "<input type=hidden name=\"action\" value=\"edit_prof\">"+
          pageheader("Edit "+name+" Profile", name)+"<p>"+
          ((pflags & flags_gbutton)?
            (split_gl?
              "<input type=image src=\""+theme_dir(name)+"/game.gif\" name=\"alien:gameown\" alt=\"Your Games\">"+
              "<input type=image src=\""+theme_dir(name)+"/gameo.gif\" name=\"alien:gameopen\" alt=\"Open Games\">"+
              "<input type=image src=\""+theme_dir(name)+"/gamen.gif\" name=\"alien:gamenew\" alt=\"New Games\">"
            :
              "<input type=image src=\""+theme_dir(name)+"/game.gif\" name=\"alien:game\" alt=\"Games\">")+
            // "<input type=image src=\""+theme_dir(name)+"/gamef.gif\" name=\"alien:gamefull\" alt=\"Game details\">"+
//            "<input type=image src=\""+theme_dir(name)+"/profile.gif\" name=\"alien:prof\" alt=Profile>"+
            "<input type=image src=\""+theme_dir(name)+"/stats.gif\" name=\"alien:stat\" alt=Stats>"+
            "<input type=image src=\""+theme_dir(name)+"/history.gif\" name=\"alien:hist\" alt=History>"+
            "<input type=image src=\""+theme_dir(name)+"/exit.gif\" name=\"alien:exit\" alt=Exit>"
          : "<font color=\"#000000\">"+
            (split_gl?
              "<input type=submit name=\"alien:gameown\" value=\"Game List\">"+
              "<input type=submit name=\"alien:gameopen\" value=\"Open Game List\">"+
              "<input type=submit name=\"alien:gamenew\" value=\"New Game List\">"
            :
              "<input name=\"alien:game\" type=submit value=\"Game List\">")+
            // "<input name=\"alien:gamefull\" type=submit value=\"Detailed Game list\">"+
//            "<input name=\"alien:prof\" type=submit value=\"Edit Profile\">"+
            "<input name=\"alien:stat\" type=submit value=\"Stat Viewer\">"+
            "<input name=\"alien:hist\" type=submit value=\"History Viewer\">"+
            "<input name=\"alien:exit\" type=submit value=\"Exit\"></font>")+
          "<p><b>"+message+"</b>"+
          "<p><img src=\""+theme_dir(name)+"/spacerule.gif\" height=10  width=100%><p>"+

        "<table cellspacing=0 valign=\"center\" >"+
        "<tr><td align=right valign=center><b>Password :</b></td>"+
          "<td><input type=password size=8 name=pass_new value="+p[name]["pass"]+"></td></tr>"+
        "<tr><td align=right valign=center><b>Password (again) :</b></td>"+
          "<td><input type=password size=8 name=pass_check value="+p[name]["pass"]+"></td></tr>"+
        "<tr><td align=right valign=center><b>Real Name :</b></td>"+
          "<td><input type=string size=20 name=real_name value="+p[name]["real_name"]+"></td></tr>"+
        "<tr><td align=right valign=center><b>Email :</b></td>"
          "<td><input type=string size=30 name=e-mail value="+p[name]["e-mail"]+"> <b>Hidden :</b> <input type=checkbox name=hide-mail"+(p[name]["hide-mail"]?" checked":"")+"></td><td>You should register your e-mail address here in case you ever need to ask the administrator to remind you of your password. If you don't want other players to be able to see the address, check the Hidden box.</td></tr>"+
        "<tr><td align=right valign=center><b>Icon :</td>"+
          "<td><input type=image src=\""+html_dir+"/aliens/"+p[name]["alien"]+"\" name=\"icon\" border=2></td><td>"+p[name]["alien"]+"</td></tr>"+
        "<tr><td align=right valign=center><b>Homepage URL :</b></td>"+
          "<td><input type=string size=40 name=url value="+p[name]["home_url"]+"></td></tr>"+
        "<tr><td align=right valign=center><b>Theme URL :</b></td>"+
           "<td><input type=string size=40 name=theme_url value="+p[name]["theme_url"]+"></td>"+
           "<td>If you have a Web-based directory of custom images you would like to be used with your game pages, please type its address here. If you are unsure, leave the field blank, as an incorrect entry can adversely affect your display.</td></tr>"+
//            "<td><input type=string size=40 name=theme_url value=\"\"></td></tr>"+

/*      not really sure it time info is needed except as a 'bell&whistle'
        "<tr><td align=right valign=center><b>Time offset :</td>"+
          "<td><input size=3 type=number name=\"time_offset\" value="+p[name]["timezone"]+">"+
          "<select name=\"time_unit\"><option selected value=0>Minutes"+
          "<option value=1>Hours</select></td></tr>"+

          // 3 Team stuff
          "<tr><td align=right><b>Current Team:</b></td>"+
          "<td>"+p[name]["sponsor"]+(p[name]["grp_admin"]?"  Administrator":"")+"</td></tr>"+

          // 3 Team stuff
          "<tr><td align=right><b>Seek new team:</b></td>"+
          "<td><input type=checkbox "+(p[name]["seeking"]?" checked":"")+" name=\"seeking_sponsor\"></td></tr>"+
//          " value=\""+ p[name]["seeking"]+"\"></td></tr>"+
          "<tr><td align=right><b>Leave current team:</b></td>"+
          "<td><input type=checkbox name=\"lose_sponsor\"></td></tr>"+
 */

          // 3 options
        "<tr><td align=right valign=center><b>Options :</b></td>"+
          "<td><table><tr><td><input name=\"flag:1\" type=checkbox "+(pflags & flags_bgimage?" checked":"")+"><nobr>Background Image</nobr>"+
          "<br><input name=\"flag:4\" type=checkbox "+(pflags & flags_eratios?" checked":"")+"><nobr>Enhanced Ratios Line</nobr>"+
          "<br><input name=\"flag:32\" type=checkbox "+(pflags & flags_nextbr?" checked":"")+"><nobr>Show Next BR</nobr>"+
          "<br><input name=\"flag:64\" type=checkbox "+(pflags & flags_sepdata?" checked":"")+"><nobr>Separate Data Color</nobr>"+
          "<br><input name=\"flag:128\" type=checkbox "+(pflags & flags_webtv?" checked":"")+"><nobr>WebTV Mode</nobr></td>"+

        "<td align=right valign=center><b>Javascript Options :</b></td>"+
          "<td><input name=\"flag:2\" type=checkbox "+(pflags & flags_autoupd?" checked":"")+"><nobr>Auto Update</nobr>"+
          "<br><input name=\"flag:8\" type=checkbox "+(pflags & flags_gbutton?" checked":"")+"><nobr>Graphical Buttons</nobr>"+
          "<br><input name=\"flag:16\" type=checkbox "+(pflags & flags_usejavascript?" checked":"")+"><nobr>Update Countdown</nobr>"+
          "<br><input name=\"flag:256\" type=checkbox "+(pflags & flags_sensitivemap?" checked":"")+"><nobr>Sensitive Map</nobr></td></tr></table></td></tr>"+

        "<tr><td align=right valign=center><b>Game List<br>Sort Order :</b></td>"+
          "<td><table>"+glsort_table+"</table></td><td>The order in which Game Lists display series is determined by the preferences you specify here. \"1st\" is the primary order; series of equal primary value are sorted by the \"2nd\" criterion, etc.</td></tr>"+

        "<tr><td align=right><b>Last Login:</b></td><td>"+nctime(p[name]["l_log"])+
          " from "+p[name]["last_ip"]+"</td></tr>"+

        "<tr><td align=right valign=center><b>Comments :</b></td>"+
          "<td><textarea wrap=hard rows=8 cols=50 name=comments>"+(p[name]["comments"]/"<br>")*"\n"+"</textarea></td></tr>"+
        "<tr><td align=right valign=center><b>Victory Sneer :</b></td>"+
          "<td><textarea wrap=hard rows=8 cols=50 name=sneer>"+(p[name]["sneer"]/"<br>")*"\n"+"</textarea></td></tr>"+
        "<tr><td align=right valign=top colspan=\"2\"><input type=submit name=\"save\" value=\"Save Profile\"></td></tr>"+
        "</table></b>&nbsp;</td></tr></table></form>"+
        footer(name),
        "type":"text/html"]);
}

/* ---------- Player Icons display-------------- */
mapping profile_icon(string name, string c_pass, string alien, int group)
{
  string alien_buttons="";
  int bstart, bend;

  for (bstart = 1; bstart <= aliens; bstart += 20) {
    bend = bstart + 19;
    if (bend > aliens) {
      bend = aliens;
    }
    alien_buttons += "<input type=submit name=\"pf:"+bstart+"\" value=\""+bstart+"-"+bend+"\">";
  }

  return (["data":
          header("Player Icon", name)+
          pageheader("Player Icon for "+name, name)+"<p><font color=\"#000000\">"+alien_buttons+
          "<input type=submit name=\"cancel\" value=\"Cancel\"></font><p>"+
          "<input type=hidden name=\"name\" value=\""+name+"\">"+
          "<input type=hidden name=\"c_pass\" value=\""+c_pass+"\">"+
          "<input type=hidden name=\"icon\" value=\""+alien+"\">"+
          "<input type=hidden name=\"action\" value=\"prof_icon\">"+
          "<p><img src=\""+theme_dir(name)+"/spacerule.gif\" height=10  width=100%><p>"+
          alien_list(alien,group)+
//          "<BR><p><INPUT type=submit name=uploadcustom value=\"Upload a custom icon\">"+
          footer(name),
          "type":"text/html"]);
}

/* ---------- Player Icon Options -------------- */
mapping profile_icon2(object id)
{
  string name=id["variables"]["name"];
  string c_pass=id["variables"]["c_pass"];
  string icon=id["variables"]["icon"];
  string *alien;
  string *a;
  string index;
  int group=0;

//  if(!id_check(name, c_pass))
//    return login_failed("Identity check failed.");

  if(id["variables"]["cancel"]=="Cancel")
    return edit_profile(name,c_pass, "Icon update cancelled");

  if(id["variables"]["uploadcustom"] == "Upload a custom icon")
    return edit_profile(name,c_pass, "Sorry...  Still working on this...");


  // check if group button pressed
  foreach(indices(id["variables"]), index) {
    a=index/":";
    if(a[0]=="pf") {
      group= (int) a[1];
      break;
    }
  }
  if ( group > 0)
    return profile_icon(name, c_pass,icon,group);

 // see if an icon has been selected
  foreach(indices(id["variables"]), index) {
      alien=index/":";
      if(alien[0] != "alien") continue;
      if ( alien=="") continue;         // no values passed... not selected
      alien=alien[1]/".";
      p[name]["alien"]=alien[0]+"."+alien[1];        // returned as "alien1.gif.x" use full icon name ie "alien1.gif"
      break;
  }
  if ( alien[0] != "" )
    return edit_profile(name,c_pass,"");

}


/* ---------- Statistics Selection Display -------------- */
mapping stat_viewer(string name, string c_pass, string message)
{
  int pflags = p[name]["flags"];
  return (["data":
          header("Stat Viewer", name)+
          "<input type=hidden name=\"name\" value=\""+name+"\">"+
          "<input type=hidden name=\"c_pass\" value=\""+c_pass+"\">"+
          "<input type=hidden name=\"action\" value=\"stat_view\">"+
          pageheader("Statistics", name)+"<p>"+
          ((pflags & flags_gbutton)?
            (split_gl?
              "<input type=image src=\""+theme_dir(name)+"/game.gif\" name=\"s:gameown\" alt=\"Your Games\">"+
              "<input type=image src=\""+theme_dir(name)+"/gameo.gif\" name=\"s:gameopen\" alt=\"Open Games\">"+
              "<input type=image src=\""+theme_dir(name)+"/gamen.gif\" name=\"s:gamenew\" alt=\"New Games\">"
            :
              "<input type=image src=\""+theme_dir(name)+"/game.gif\" name=\"s:game\" alt=\"Games\">")+
            // "<input type=image src=\""+theme_dir(name)+"/gamef.gif\" name=\"s:gamefull\" alt=\"Game details\">"+
            "<input type=image src=\""+theme_dir(name)+"/profile.gif\" name=\"s:prof\" alt=Profile>"+
//            "<input type=image src=\""+theme_dir(name)+"/stats.gif\" name=\"s:stat\" alt=Stats>"+
            "<input type=image src=\""+theme_dir(name)+"/history.gif\" name=\"s:hist\" alt=History>"+
            "<input type=image src=\""+theme_dir(name)+"/exit.gif\" name=\"s:exit\" alt=Exit>"
          : "<font color=\"#000000\">"+
            (split_gl?
              "<input name=\"s:gameown\" type=submit value=\"Game List\">"+
              "<input name=\"s:gameopen\" type=submit value=\"Open Game List\">"+
              "<input name=\"s:gamenew\" type=submit value=\"New Game List\">"
            :
              "<input name=\"s:game\" type=submit value=\"Game List\">")+
            // "<input name=\"s:gamefull\" type=submit value=\"Detailed Game List\">"+
            "<input name=\"s:prof\" type=submit value=\"Edit Profile\">"+
//            "<input name=\"s:stat\" type=submit value=\"Stat Viewer\">"+
            "<input name=\"s:hist\" type=submit value=\"History Viewer\">"+
            "<input name=\"s:exit\" type=submit value=\"Exit\"></font>")+
          "<p><b>"+message+"</b>"+
          "<p><img src=\""+theme_dir(name)+"/spacerule.gif\" height=10  width=100%><p>"+
          "<center>"+
          "<b><font color="+READY_COLOR+">There are "+sizeof(indices(p))+" registered players.</font></b><p>"+
          ((toplist || enable_bridier)?"<table>"+(enable_bridier?
          "<tr><td align=right><b>Top Ten Bridier Ranks (established players)</b></td>"+
          "<td><input type=submit name=\"s:bre\" value=\"Ok\"></td></tr>"+
          "<tr><td align=right><b>Top Ten Bridier Ranks (all players)</b></td>"+
          "<td><input type=submit name=\"s:brd\" value=\"Ok\"></td></tr>":"")+
          (toplist?"<tr><td align=right><b>Top Ten Winners</b></td>"+  // 2.9 admin put into table for clarity
          "<td><input type=submit name=\"s:top\" value=\"Ok\"></td></tr>"+
          "<tr><td align=right><b>Top Ten Averages</b></td>"+
          "<td><input type=submit name=\"s:avg\" value=\"Ok\"></td></tr>"+
          "<tr><td align=right><b>Top Ten Nuke/Game Ratios</b></td>"+
          "<td><input type=submit name=\"s:wnr\" value=\"Ok\"><td></tr>":"")+"</table>":"")+
          "<table><tr><td align=right><b>Name is (regexp allowed):</b></td>"+
          "<td><input type=text name=\"sname\" size=20 maxlength=20></td></tr>"+
          "<tr><td align=right colspan=2>"+
          "<input type=submit name=\"s:search\" value=\"Search\"></td></tr></table>"+
          "<table><tr><td><input type=submit name=\"s:[Aa]\" value=\" A \"></td>"+  // sc2.9admin search buttons
          "<td><input type=submit name=\"s:[Bb]\" value=\" B \"></td>"+
          "<td><input type=submit name=\"s:[Cc]\" value=\" C \"></td>"+
          "<td><input type=submit name=\"s:[Dd]\" value=\" D \"></td>"+
          "<td><input type=submit name=\"s:[Ee]\" value=\" E \"></td>"+
          "<td><input type=submit name=\"s:[Ff]\" value=\" F \"></td>"+
          "<td><input type=submit name=\"s:[Gg]\" value=\" G \"></td>"+
          "<td><input type=submit name=\"s:[Hh]\" value=\" H \"></td>"+
          "<td><input type=submit name=\"s:[Ii]\" value=\"  I  \"></td>"+
          "<td><input type=submit name=\"s:[Jj]\" value=\"  J  \"></td>"+
          "<td><input type=submit name=\"s:[Kk]\" value=\" K \"></td>"+
          "<td><input type=submit name=\"s:[Ll]\" value=\" L \"></td>"+
          "<td><input type=submit name=\"s:[Mm]\" value=\" M \"></td></tr>"+
          "<tr><td><input type=submit name=\"s:[Nn]\" value=\" N \"></td>"+
          "<td><input type=submit name=\"s:[Oo]\" value=\" O \"></td>"+
          "<td><input type=submit name=\"s:[Pp]\" value=\" P \"></td>"+
          "<td><input type=submit name=\"s:[Qq]\" value=\" Q \"></td>"+
          "<td><input type=submit name=\"s:[Rr]\" value=\" R \"></td>"+
          "<td><input type=submit name=\"s:[Ss]\" value=\" S \"></td>"+
          "<td><input type=submit name=\"s:[Tt]\" value=\" T \"></td>"+
          "<td><input type=submit name=\"s:[Uu]\" value=\" U \"></td>"+
          "<td><input type=submit name=\"s:[Vv]\" value=\" V \"></td>"+
          "<td><input type=submit name=\"s:[Ww]\" value=\" W \"></td>"+
          "<td><input type=submit name=\"s:[Xx]\" value=\" X \"></td>"+
          "<td><input type=submit name=\"s:[Yy]\" value=\" Y \"></td>"+
          "<td><input type=submit name=\"s:[Zz]\" value=\" Z  \"></td></tr></table>"+
          "</center>"+footer(name),
          "type":"text/html"]);
}

/* ---------- Player History display-------------- */
mapping hist_viewer(string name, string c_pass, string message)
{
  int pflags = p[name]["flags"];
  return (["data":
          header("History Viewer", name)+
          "<input type=hidden name=\"name\" value=\""+name+"\">"+
          "<input type=hidden name=\"c_pass\" value=\""+c_pass+"\">"+
          "<input type=hidden name=\"action\" value=\"hist_view\">"+
          pageheader("History", name)+"<p>"+
          ((pflags & flags_gbutton)?
            (split_gl?
              "<input type=image src=\""+theme_dir(name)+"/game.gif\" name=\"s:gameown\" alt=\"Your Games\">"+
              "<input type=image src=\""+theme_dir(name)+"/gameo.gif\" name=\"s:gameopen\" alt=\"Open Games\">"+
              "<input type=image src=\""+theme_dir(name)+"/gamen.gif\" name=\"s:gamenew\" alt=\"New Games\">"
            :
              "<input type=image src=\""+theme_dir(name)+"/game.gif\" name=\"s:game\" alt=\"Games\">")+
            // "<input type=image src=\""+theme_dir(name)+"/gamef.gif\" name=\"s:gamefull\" alt=\"Game details\">"+
            "<input type=image src=\""+theme_dir(name)+"/profile.gif\" name=\"s:prof\" alt=Profile>"+
            "<input type=image src=\""+theme_dir(name)+"/stats.gif\" name=\"s:stat\" alt=Stats>"+
//            "<input type=image src=\""+theme_dir(name)+"/history.gif\" name=\"s:hist\" alt=History>"+
            "<input type=image src=\""+theme_dir(name)+"/exit.gif\" name=\"s:exit\" alt=Exit>"
          : "<font color=\"#000000\">"+
            (split_gl?
              "<input name=\"s:gameown\" type=submit value=\"Game List\">"+
              "<input name=\"s:gameopen\" type=submit value=\"Open Game List\">"+
              "<input name=\"s:gamenew\" type=submit value=\"New Game List\">"
            :
              "<input name=\"s:game\" type=submit value=\"Game List\">")+
            // "<input name=\"s:gamefull\" type=submit value=\"Detailed Game List\">"+
            "<input name=\"s:prof\" type=submit value=\"Edit Profile\">"+
            "<input name=\"s:stat\" type=submit value=\"Stat Viewer\">"+
//            "<input name=\"s:hist\" type=submit value=\"History Viewer\">"+
            "<input name=\"s:exit\" type=submit value=\"Exit\"></font>")+
          "<p><b>"+message+"</b>"+
          "<p><img src=\""+theme_dir(name)+"/spacerule.gif\" height=10  width=100%><p>"+
          "<center><table cellpadding=5><tr><th width=20%>Time</th><th width=20%>Game</th><th width=20%>Result</th><th>Other Players</th></tr>"+
          history_1+history_2+history_3+
          "</table></center>"+footer(name),
          "type":"text/html"]);
}

/* ---------- Player Game Options -------------- */
mapping game_list2(object id) {
  string name=id["variables"]["name"];
  string c_pass=id["variables"]["c_pass"];
  string *a;
  int enter=0;
  string index;

  if(!id_check(name, c_pass))
    return login_failed("Identity check failed.");

  foreach(indices(id["variables"]), index) {
    a=index/".";                        // strip '.' graphics return "l:series:b:join.x"    (or "l:exit.y")
    a=a[0]/":";
    if(a[0]=="enter")
      enter=1; 
    if(a[0]=="l")
      break;
  }
//  if ((int)(p[name]["flags"] & flags_gbutton)) {
//    a=a[1]/".";
//    a[1] = a[0];
//  }
  if(a[0]=="l") {
    if(a[1]=="exit") return login_form();
    if(a[1]=="prof") return edit_profile(name, c_pass, "");
    if(a[1]=="stat") return stat_viewer(name, c_pass, "");
    if(a[1]=="hist") return hist_viewer(name, c_pass, "");
    if(a[1]=="game") return game_list(name, c_pass, 0, 0, "");
    if(a[1]=="gameown") return game_list(name, c_pass, 0, 1, "");
    if(a[1]=="gameopen") return game_list(name, c_pass, 0, 2, "");
    if(a[1]=="gamenew") return game_list(name, c_pass, 0, 3, "");
    if(a[1]=="gamefull") return game_list(name, c_pass, 1, 0, "");
    if(a[1]=="gpass") enter=1;
  }
  if (enter==1) {
    string series=id["variables"]["series"];
    string game=id["variables"]["game"];
    string gpass=id["variables"]["gpass"];
    if(!mappingp(d[series])) {
      return game_list(name, c_pass, 0, split_gl, "That series no longer exists - "+series);
    }
    if(!mappingp(d[series][game])) {
      return game_list(name, c_pass, 0, split_gl, "That game no longer exists.");
    }
    return login_game(name, c_pass, series, game, 0, gpass);
  }

  if(!mappingp(d[a[1]])) return game_list(name, c_pass, 0, split_gl, "That series no longer exists - "+((string) a[1])+".");

  if(a[2]=="detail") return series_detail(name, c_pass, a[1], "");

  if(!mappingp(d[a[1]][a[2]])) return game_list(name, c_pass, 0, split_gl, "That game no longer exists.");

  if (id["variables"]["c:"+a[1]+":"+a[2]])
    return login_game(name, c_pass, a[1], a[2], (int) id["variables"]["c:"+a[1]+":"+a[2]], "");
  else
    return login_game(name, c_pass, a[1], a[2], 0, "");
}

/* ----------- Player profile options -------------- */
void common_profile(string name, object id)
{
  string *a;
  string index;
  int flags = 0;
  string glsort = "001020";

  p[name]["real_name"]=sterilizeName(id["variables"]["real_name"], 0);
  p[name]["e-mail"]=sterilizeString (id["variables"]["e-mail"]);
  if (id["variables"]["hide-mail"]=="on") {
    p[name]["hide-mail"]=1;
  } else {
    p[name]["hide-mail"]=0;
  }
  p[name]["comments"]=sterilizeString(id["variables"]["comments"]);
  p[name]["sneer"]=sterilizeString(id["variables"]["sneer"]);
  p[name]["home_url"] = (id["variables"]["url"]);
  p[name]["theme_url"] = (id["variables"]["theme_url"]);

  foreach(indices(id["variables"]), index) {
    a = index/":";
    if (a[0] == "flag") {
      if (id["variables"][index] == "on") {
        flags += (int) a[1];
      }
    } else if (a[0] == "gl") {
      int k=((int) a[2]) * 2;
      if (a[1] == "order") {
        glsort = glsort[0..(k-1)]+id["variables"][index]+glsort[(k+1)..5];
      } else if (a[1] == "ad") {
        glsort = glsort[0..(k)]+id["variables"][index]+glsort[(k+2)..5];
      }
    }
  }
  p[name]["flags"] = flags;
  p[name]["glsort"] = glsort;
}

mapping edit_profile2(object id)
{
  string name=id["variables"]["name"];
  string c_pass=id["variables"]["c_pass"];
  string index;
  string *a;

  if(!id_check(name, c_pass))
    return login_failed("Identity check failed.");

  foreach(indices(id["variables"]), index) {
    a=index/".";                         // graphics buttons return "l:exit.x"    (or "l:exit.y")
    a=a[0]/":";
    if(a[0]=="alien") break;
  }

  if ( a[0]=="alien") {
    if(a[1]=="game") return game_list(name, c_pass, 0, 0, "Profile changes cancelled.");
    if(a[1]=="gameown") return game_list(name, c_pass, 0, 1, "Profile changes cancelled.");
    if(a[1]=="gameopen") return game_list(name, c_pass, 0, 2, "Profile changes cancelled.");
    if(a[1]=="gamenew") return game_list(name, c_pass, 0, 3, "Profile changes cancelled.");
    if(a[1]=="gamefull") return game_list(name, c_pass, 1, 0, "Profile changes cancelled.");
    if(a[1]=="stat") return stat_viewer(name, c_pass, "Profile changes cancelled.");
    if(a[1]=="hist") return hist_viewer(name, c_pass, "Profile changes cancelled.");
    if(a[1]=="prof") return edit_profile(name, c_pass, "Profile changes cancelled.");
    if(a[1]=="exit") return login_form();
  }

  // icon selected??
  if (id["variables"]["save"] !="Save Profile") {
    p[name]["alien"]="alien1.gif";            // default to something
    return profile_icon(name, c_pass, p[name]["alien"],1);
  }


  if(id["variables"]["pass_new"]==id["variables"]["pass_check"]) {
    // 2.9 html fix
    p[name]["pass"]=id["variables"]["pass_new"];
    common_profile(name, id);
/*
    p[name]["seeking"]=id["variables"]["seeking_sponsor"];
    if (id["variables"]["lose_sponsor"]=="on") {
      p[name]["sponsor"] = "Independent";
      p[name]["spon_admin"] = 0;
      p[name]["pgroup"] = "";
    }
*/

    //  p[name]["comments"]=replace(id["variables"]["comments"], ({"<", ">"}), ({"&lt", "&gt"}));
    //  p[name]["comments"]=(p[name]["comments"]/"\n")*"<br>";

    foreach(indices(id["variables"]), index) {
      a=index/":";
      if(a[0] != "alien") continue;
      a=a[1]/".";
      p[name]["alien"]=a[0]+"."+a[1];            // was 0 .. use full icon name
      break;
    }

/*
    int time_offset=(int) id["variables"]["time_offset"];
    if(time_offset > 0) {
    switch(id["variables"]["time_unit"]) {
      case "Minutes" :
        time_offset *= 60;
      case "Hours":
        time_offset *= 3600;
    }
    p[name]["timezone"] = time_offset;
 */

    return game_list(name, c_pass, 0, split_gl, "Profile changes accepted");

  } else {
    return edit_profile(name, c_pass, "The two password fields were different. Please try again.");
  }
} // edit_profile2


/* ---------- Top Ten List generation ------------- */
void prep_top_ten()
{
  mapping score=([]);
  mapping tmp=([]);

  string ixa, ixb, mem;
  int i, now=time();
  float pri, sec;

  top=([]);

  score["win"]=([]);
  score["avg"]=([]);
  score["wnr"]=([]);
  score["brd"]=([]);
  score["bre"]=([]);

  foreach(indices(p), ixa) {
    if(mappingp(p[ixa])) {
      if(enable_bridier) {
        if (!p[ixa]["bridier_index"]) {
          p[ixa]["bridier_index"]=p[ixa]["bridier_rank"]=500;
        }
        disestablish(ixa);
        if (p[ixa]["bridier_index"]<495) {
          score["brd"][ixa]=p[ixa]["bridier_rank"];
        }
        if (p[ixa]["bridier_index"]==100) {
          score["bre"][ixa]=p[ixa]["bridier_rank"];
        }
      }
      score["win"][ixa]=((float) p[ixa]["wins"]+
                         ((float) p[ixa]["kills"]/2.0)-
                         ((float) p[ixa]["killed"]/2.0)-
                         (float) p[ixa]["ruins"]);
      score["avg"][ixa]=p[ixa]["wins"]?((float) p[ixa]["wins"]/
                                        ((float) p[ixa]["wins"]+
                                         (float) p[ixa]["killed"]+
                                         (float) p[ixa]["ruins"])):0.0;
      score["wnr"][ixa]=(p[ixa]["wins"]>2)?((float) p[ixa]["kills"]/
                                            ((float) p[ixa]["wins"]+
                                             (float) p[ixa]["killed"]+
                                             (float) p[ixa]["ruins"])):0.0;
    }
  }

  foreach(indices(score), ixa) {
    tmp=copy_value(score[ixa]);
    top[ixa]="";

    for(i=0; i<10; i++) {
      pri=0.0;
      sec=0.0;
      mem="";

      foreach(indices(tmp), ixb) {
        if(tmp[ixb]>pri || (tmp[ixb]==pri && score["win"][ixb]>sec)) {
          pri=tmp[ixb];
          sec=score["win"][ixb];
          mem=ixb;
        }
      }

      tmp-=([mem:""]);
      if(p[mem] && sizeof(p[mem])) {
        if(p[mem]["hide-mail"]==1) {
          top[ixa]+=sprintf("<tr><td><font color="+STAT_COLOR+">%s</font></td>"+
                            "<td align=left>%s</td>"+
                            "<td></td>",
                            mem||"", p[mem]["real_name"]||"");
        } else {
          top[ixa]+=sprintf("<tr><td><font color="+STAT_COLOR+">%s</font></td>"+
                            "<td align=left>%s</td>"+
                            "<td align=left><a href=mailto:%s>%s</a></td>",
                            mem||"", p[mem]["real_name"]||"",
                            p[mem]["e-mail"]||"", p[mem]["e-mail"]||"");
        }
        if (ixa=="brd"||ixa=="bre") {
          top[ixa]+=sprintf("<td align=center>%O</td>"+
                            "<td align=center>%O</td>"+
                            "</tr>", pri, p[mem]["bridier_index"]);
        } else {
          top[ixa]+=sprintf("<td align=center>%O</td>"+
                            "<td align=center>%O</td>"+
                            "<td align=center>%O</td>"+
                            "<td align=center>%O</td>"+
                            "<td align=center>%O</td>"+
                            "</tr>", p[mem]["wins"],
                            p[mem]["kills"], p[mem]["killed"],
                            p[mem]["ruins"], pri);
        }
      }
    }
  }
  perror(sprintf("Top Lists prepared in %s.\n", time_conv(time() - now)));
}

/* ---------- Top Ten list display ------------- */
mapping top_ten(string name, string c_pass, string type)
{
  string label;

  switch(type)
    {
    case "win":
      label="Winners";
      break;
    case "avg":
      label="Averages";
      break;
    case "wnr":
      label="Nuke/Game Ratios";
      break;
    case "brd":
      label="Bridier Ranks<br>All Players: Index &lt 495";
      break;
    case "bre":
      label="Bridier Ranks<br>Established Players: Index = 100";
      break;
    }

  int pflags = 0;
  if(name != "anonymous")
    pflags = p[name]["flags"];

  return (["data":
          header("Stat Viewer", name)+
          "<input type=hidden name=\"name\" value=\""+name+"\">"+
          "<input type=hidden name=\"c_pass\" value=\""+c_pass+"\">"+
          "<input type=hidden name=\"action\" value=\"stat_view\">"+
          pageheader("Top Ten "+label, name)+"<p>"+
          ((pflags & flags_gbutton)?
            (split_gl?
              "<input type=image src=\""+theme_dir(name)+"/game.gif\" name=\"s:gameown\" alt=\"Your Games\">"+
              "<input type=image src=\""+theme_dir(name)+"/gameo.gif\" name=\"s:gameopen\" alt=\"Open Games\">"+
              "<input type=image src=\""+theme_dir(name)+"/gamen.gif\" name=\"s:gamenew\" alt=\"New Games\">"
            :
              "<input type=image src=\""+theme_dir(name)+"/game.gif\" name=\"s:game\" alt=\"Games\">")+
            // "<input type=image src=\""+theme_dir(name)+"/gamef.gif\" name=\"s:gamefull\" alt=\"Game details\">"+
            "<input type=image src=\""+theme_dir(name)+"/profile.gif\" name=\"s:prof\" alt=Profile>"+
            "<input type=image src=\""+theme_dir(name)+"/stats.gif\" name=\"s:stat\" alt=Stats>"+
            "<input type=image src=\""+theme_dir(name)+"/history.gif\" name=\"s:hist\" alt=History>"+
            "<input type=image src=\""+theme_dir(name)+"/exit.gif\" name=\"s:exit\" alt=Exit>"
          : "<font color=\"#000000\">"+
            (split_gl?
              "<input type=submit name=\"s:gameown\" value=\"Game List\">"+
              "<input type=submit name=\"s:gameopen\" value=\"Open Game List\">"+
              "<input type=submit name=\"s:gamenew\" value=\"New Game List\">"
            :
              "<input type=submit name=\"s:game\" value=\"Game List\">")+
            // "<input type=submit name=\"s:gamefull\" value=\"Detailed Game List\">"+
            "<input type=submit name=\"s:prof\" value=\"Edit Profile\">"+
            "<input type=submit name=\"s:stat\" value=\"Stat Viewer\">"+
            "<input type=submit name=\"s:hist\" value=\"History Viewer\">"+
            "<input type=submit name=\"s:exit\" value=\"Exit\"></font>")+
          "<p><img src=\""+theme_dir(name)+"/spacerule.gif\" height=10  width=100%><p>"+
          "<center><table>"+
          "<tr valign=top><th align=left>Empire</th><th align=left>Real Name</th><th align=left>E-Mail</th>"+
          ((type=="brd"||type=="bre")?"<th>Rank</th><th>Index</th>":
          "<th>Wins</th><th>Nukes</th><th>Been Nuked</th><th>Ruins</th><th>Score</th>")+"</tr>"+
          top[type]+
          "</table></center>"+
          footer(name),
          "type":"text/html"]);
}

string *regexp_match(string *s, string reg)
{
#if ROXEN_VER == "1.2"
  object r = Regexp( reg );
  return Array.filter(s,r->match);
#else
  object r = clone((program)"/precompiled/regexp", reg);
  return filter_array(s,r->match);
#endif
}

/* ---------- Statistics options ------------- */
string stat_view_data(string empire)
{
  return("<p><h3>"+empire+" Stats:</h3><table>"+
     "<tr><td align=right><b>Real Name:</b></td><td colspan=3>"+
     p[empire]["real_name"]+"</td></tr>"+
     "<tr><td align=right><b>E-Mail:</b></td><td colspan=3>"+
     ((p[empire]["hide-mail"]==1)?"":p[empire]["e-mail"])+"</td></tr>"+
     "<tr><td align=right><b>Wins:</b></td><td>"+p[empire]["wins"]+"</td>"+
     "<td align=right><b>Nukes:</b></td><td>"+p[empire]["kills"]+
     "</td></tr>"+
     "<tr><td align=right><b>Been Nuked:</b></td><td>"+
     p[empire]["killed"]+"</td>"+
     "<td align=right><b>Ruins:</b></td><td>"+p[empire]["ruins"]+
     "</td></tr>"+
     "<tr><td align=right><b>Max Econ:</b></td><td>"+
     p[empire]["max_e_pow"]+"</td>"+
     "<td align=right><b>Max Mil:</b></td><td>"+
     p[empire]["max_m_pow"]+"</td></tr>"+(enable_bridier?
     "<tr><td align=right><b>Bridier Rank:</b></td><td>"+
     p[empire]["bridier_rank"]+"</td>"+
     "<td align=right><b>Bridier Index:</b></td><td>"+
     p[empire]["bridier_index"]+"</td></tr>":"")+
// Looks like someone had the same idea
     "<tr><td align=right><b>Last Login:</b></td><td colspan=3>"+
     nctime(p[empire]["l_log"])+"</td></tr>"+
//               "<tr><td align=right><b>Last Login:</b></td><td>"+nctime(p[empire]["l_log"])+
//                 " from "+p[empire]["last_ip"]+"</td></tr>"+
     "<tr><td align=center colspan=4><img src=\""+
     html_dir+"/aliens/"+p[empire]["alien"]+"\"></td></tr>"+
     "<tr><td colspan=4><pre>"+(p[empire]["comments"]||"")+
     "</pre></td></tr></table></p>");
}

mapping stat_viewer2(object id) {
  string name=id["variables"]["name"];
  string c_pass=id["variables"]["c_pass"];
  string *a;
  string index;

  if(!id->variables->anonymous)
    if(!id_check(name, c_pass))
      return login_failed("Identity check failed.");

  foreach(indices(id["variables"]), index) {
    a=index/".";                         // graphics buttons return "s:exit.x"    (or "s:exit.y")
    a=a[0]/":";
    if(a[0]=="s")
      break;
  }
//  if ((int)(p[name]["flags"] & flags_gbutton)) {
//    a=a[1]/".";
//    a[1] = a[0];
//  }

  if(sizeof(a) < 2)
    a = ({"s", "search"}); // If someone press return in the "search" form.

  switch(a[1])
    {
    case "exit":
      return login_form();
    case "prof":
      return edit_profile(name, c_pass, "");
    case "stat":
      return stat_viewer(name, c_pass, "");
    case "hist":
      return hist_viewer(name, c_pass, "");
    case "game":
      return game_list(name, c_pass, 0, 0, "");
    case "gameown":
      return game_list(name, c_pass, 0, 1, "");
    case "gameopen":
      return game_list(name, c_pass, 0, 2, "");
    case "gamenew":
      return game_list(name, c_pass, 0, 3, "");
    case "gamefull":
      return game_list(name, c_pass, 1, 0, "");
    case "top":
      return top_ten(name, c_pass, "win");
    case "avg":
      return top_ten(name, c_pass, "avg");
    case "wnr":
      return top_ten(name, c_pass, "wnr");
    case "brd":
      return top_ten(name, c_pass, "brd");
    case "bre":
      return top_ten(name, c_pass, "bre");
    case "cancel":
      return stat_viewer(name, c_pass, "Search cancelled.");
    default:
      string s=id["variables"]["sname"];
      array emps;
      string r="<b>There are no matching empires.</b>";
      int SearchCount=-1;

      // sc2.9admin  search buttons
      if ( s == "") {
        s = a[1];
        SearchCount = 0;
      }

      if(mappingp(p[s]))
        r=stat_view_data(s);
      else {
        emps = regexp_match(indices(p), "^"+s);
        if(sizeof(emps) > 200)  {
          r = sprintf("<h3>Too many matching empires (%d).</h3>", sizeof(emps));

        } else if(sizeof(emps) > 1) {
          r = sprintf("<h3>There are %d matching empires.</h3>"
                      "<h4>Click on the name of the empire you want to know "
                      "more about.</h4><table><tr>", sizeof(emps));
          foreach(emps, s) {
            r += sprintf("<td align=center><input type=submit name=sname value=\"%s\"></td>\n", s);
            SearchCount += 1;
            if ( SearchCount > 3) {
              r+= "</tr><tr>";
              SearchCount = 0;
            }
          }
          r += "</tr></table>";
        } else if(sizeof(emps) == 1) {
          s = emps[0];
          if(mappingp(p[s]))
            r=stat_view_data(s);
        }
      }

      int pflags = 0;
      if(id->variables->anonymous)
        r += "<input type=hidden name=anonymous value=1>";
      else
        pflags = p[name]["flags"];

      return (["data":
              header("Stat Viewer", name)+
              "<input type=hidden name=\"name\" value=\""+name+"\">"+
              "<input type=hidden name=\"c_pass\" value=\""+c_pass+"\">"+
              "<input type=hidden name=\"action\" value=\"stat_view\">"+
              (id->variables->anonymous ?
                pageheader("Statistics", name) :
               (pageheader("Statistics", name)+"<p>"+
                ((pflags & flags_gbutton)?
                  (split_gl?
                    "<input type=image src=\""+theme_dir(name)+"/game.gif\" name=\"s:gameown\" alt=\"Your Games\">"+
                    "<input type=image src=\""+theme_dir(name)+"/gameo.gif\" name=\"s:gameopen\" alt=\"Open Games\">"+
                    "<input type=image src=\""+theme_dir(name)+"/gamen.gif\" name=\"s:gamenew\" alt=\"New Games\">"
                  :
                    "<input type=image src=\""+theme_dir(name)+"/game.gif\" name=\"s:game\" alt=\"Games\">")+
                  // "<input type=image src=\""+theme_dir(name)+"/gamef.gif\" name=\"s:gamefull\" alt=\"Game details\">"+
                  "<input type=image src=\""+theme_dir(name)+"/profile.gif\" name=\"s:prof\" alt=Profile>"+
                  "<input type=image src=\""+theme_dir(name)+"/stats.gif\" name=\"s:stat\" alt=Stats>"+
                  "<input type=image src=\""+theme_dir(name)+"/history.gif\" name=\"s:hist\" alt=History>"+
                  "<input type=image src=\""+theme_dir(name)+"/exit.gif\" name=\"s:exit\" alt=Exit>"
                : "<font color=\"#000000\">"+
                  (split_gl?
                    "<input type=submit name=\"s:gameown\" value=\"Game List\">"+
                    "<input type=submit name=\"s:gameopen\" value=\"Open Game List\">"+
                    "<input type=submit name=\"s:gamenew\" value=\"New Game List\">"
                  :
                    "<input type=submit name=\"s:game\" value=\"Game List\">")+
                  // "<input type=submit name=\"s:gamefull\" value=\"Detailed Game List\">"+
                  "<input type=submit name=\"s:prof\" value=\"Edit Profile\">"+
                  "<input type=submit name=\"s:stat\" value=\"Stat Viewer\">"+
                  "<input type=submit name=\"s:hist\" value=\"History Viewer\">"+
                  "<input type=submit name=\"s:exit\" value=\"Exit\"></font>")))+
              "<p><img src=\""+theme_dir(name)+"/spacerule.gif\" height=10  width=100%><p>"+
              "<center>"+r+"</center>"+
              footer(name),
              "type":"text/html"]);
    }
}

mapping hist_viewer2(object id) {
  string name=id["variables"]["name"];
  string c_pass=id["variables"]["c_pass"];
  string *a;
  string index;

  if(!id->variables->anonymous)
    if(!id_check(name, c_pass))
      return login_failed("Identity check failed.");

  foreach(indices(id["variables"]), index) {
    a=index/".";                         // graphics buttons return "s:exit.x"    (or "s:exit.y")
    a=a[0]/":";
    if(a[0]=="s")
      break;
  }

  if(sizeof(a) < 2)
    a = ({"s", "search"}); // If someone press return in the "search" form.

  switch(a[1])
    {
    case "exit":
      return login_form();
    case "prof":
      return edit_profile(name, c_pass, "");
    case "stat":
      return stat_viewer(name, c_pass, "");
    case "hist":
      return hist_viewer(name, c_pass, "");
    case "game":
      return game_list(name, c_pass, 0, 0, "");
    case "gameown":
      return game_list(name, c_pass, 0, 1, "");
    case "gameopen":
      return game_list(name, c_pass, 0, 2, "");
    case "gamenew":
      return game_list(name, c_pass, 0, 3, "");
    case "gamefull":
      return game_list(name, c_pass, 1, 0, "");
    case "top":
      return top_ten(name, c_pass, "win");
    case "avg":
      return top_ten(name, c_pass, "avg");
    case "wnr":
      return top_ten(name, c_pass, "wnr");
    case "cancel":
      return stat_viewer(name, c_pass, "Search cancelled.");
    default:
      return game_list(name, c_pass, 0, split_gl, "");
  } // switch
}





/* --------------------------------------------------
*              Server Admin Functions               *
 --------------------------------------------------*/
mapping admin_login(string name, string c_pass, string message)
{
  return (["data":
          header("Server Admin", name)+
          "<input type=hidden name=\"name\" value=\""+name+"\">"+
          "<input type=hidden name=\"c_pass\" value=\""+c_pass+"\">"+
          "<input type=hidden name=\"action\" value=\"admin\">"+
          pageheader("Server Administration", name)+
          "<font color=\"#000000\"><input type=submit name=\"ad:exit\" value=\"Exit Server Admin\"></font>"+
          "<p><img src=\""+theme_dir(name)+"/spacerule.gif\" height=10  width=100%><p>"+
          "<center><font color="+LOG_COLOR+"><b>"+message+"</b></font><table>"+  // 2.9admin
          "<tr><td align=right><b>Login message</b></td>"+  // 2.9admin add login message option
          "<td><input type=submit name=\"ad:logmsg\" value=\"Ok\"></td>"+
          "<td align=right><b>Broadcast message</b></td>"+
          "<td><input type=submit name=\"ad:bcast\" value=\"Ok\"></td></tr>"+
          "<tr><td></td></tr><tr><td align=right><b>Series list</b></td>"+
          "<td><input type=submit name=\"ad:sli\" value=\"Ok\"></td>"+
          "<td align=right><b>Start a new series</b></td>"+
          "<td><input type=submit name=\"ad:sst\" value=\"Ok\"></td></tr>"+
//          "<tr><td align=right><b>Halt a series</b></td>"+
//          "<td><input type=submit name=\"ad:sha\" value=\"Ok\"></td>"+
          "<tr><td align=right><b>Edit a series</b></td>"+  // 2.9admin add edit series option
          "<td><input type=submit name=\"ad:edser\" value=\"Ok\"></td>"+
//          "<tr><td align=right><b>Resume a series</b></td>"+
//          "<td><input type=submit name=\"ad:sre\" value=\"Ok\"></td>"+
          "<td align=right><b>Kill a series</b></td>"+
          "<td><input type=submit name=\"ad:ski\" value=\"Ok\"></td></tr>"+

          // 3 reset next update time
          "<tr><td></td></tr><tr><td align=right><b>Reset Next Update</b></td>"+
          "<td><input type=submit name=\"ad:rti\" value=\"Ok\"></td></tr>"+
          "<tr><td align=right><b>Remove Unused Empires</b></td>"+
          "<td><input type=submit name=\"ad:clean\" value=\"Ok\"></td>"+
          "<td align=right><b>Check player profiles</b></td>"+
          "<td><input type=submit name=\"ad:playchk\" value=\"Ok\"></td></tr>"+

// 2.9 add game view
          "<tr><td></td></tr><tr><td align=right><b>View a game</b></td>"+
          "<td><input type=submit name=\"ad:gvi\" value=\"Ok\"></td>"+
          "<td align=right><b>Kill a game</b></td>"+
          "<td><input type=submit name=\"ad:gki\" value=\"Ok\"></td></tr>"+

          "<tr><td></td></tr><tr><td align=right><b>Empire view</b></td>"+  // sc2.9admin
          "<td><input type=submit name=\"ad:emp\" value=\"Ok\"></td></tr>"+
          "<tr><td></td></tr><tr><td align=right><b>Save the database</b></td>"+
          "<td><input type=submit name=\"ad:dump\" value=\"Ok\"></td>"+
          "<td align=right><b>Load the database</b></td>"+
          "<td><input type=submit name=\"ad:load\" value=\"Ok\"></td></tr>"+
          "<tr><td align=right><b>Save ASCII database</b></td>"+
          "<td><input type=submit name=\"ad:asdump\" value=\"Ok\"></td>"+
          "<td align=right><b>Load ASCII database</b></td>"+
          "<td><input type=submit name=\"ad:asload\" value=\"Ok\"></td></tr>"+
          "<tr><td></td></tr><tr><td align=right><b>Restart the server</b></td>"+
          "<td><input type=submit name=\"ad:restart\" value=\"Ok\"></td></tr>"+
//         "<tr><td align=right><b>Exit</b></td>"+
//          "<td><input type=submit name=\"ad:exit\" value=\"Ok\"></td></tr>"+
          "</table></center>"+
          footer(name),
          "type":"text/html"]);
}



mapping start_series(string name, string c_pass, string message)
{
  string str, ixc, s_d, s_a, s_r, s_n;
  int cnt, row;
  row = cnt = 0;

          str =
          header("Start Series", name)+
          "<input type=hidden name=\"name\" value=\""+name+"\">"+
          "<input type=hidden name=\"c_pass\" value=\""+c_pass+"\">"+
          "<input type=hidden name=\"action\" value=\"start_series\">"+
//          "<center><h1>Start a New Series</h1></center>"+
          pageheader("Start a new Series", name)+
          "<p><img src=\""+theme_dir(name)+"/spacerule.gif\" height=10  width=100%><p>"+
          "<center><b>"+message+"</b><table>"+
          "<tr><td align=right><b>Series Name:</b></td>"+
          "<td><input type=text size=20 maxlength=20 name=\"s_name\"></td></tr>";

          // series colour
            str +=
            "<tr><td align=right><b>Series Colour:</b></td>"+
            "<td><input type=text size=7 maxlength=7 name=\"series_color\" value=\""+server_color+"\"></td></tr>";

/* --------------------------------------------------
   Now done at Game level rather than Series level.

          // Allow restricted games
            str +=
            "<tr><td align=right><b>Restricted games:</b></td>"+
            "<td> <select name=\"s_restrict\"> <option selected value=0> No <option value=1> Yes </select> </td>";

 -----------------29/10/97 17:19-------------------
   Option is to allow restricted access to a game.
  ie Invite, team etc games where the Team or admin
  has the option to set a password for that game
 --------------------------------------------------
          // Series password
            str +=
            "<td align=right><b>Joining password:</b></td>"+
            "<td><input type=text size=10 maxlength=10 name=\"series_pass\" value=\"\"></td></tr>";
//            str += "</table></center>";

 ---------------------------------------------------- */


// SC3.0 descriptions
          // Brief Description that the admin enters
            str +=
          "<tr><td align=right><b>Brief Description:</b></td>"+
          "<td colspan=3 WIDTH=\"75%\"><textarea name=\"desc\" rows=2 cols=60></textarea></td></tr>";

          // Full Description - this should be automatically generated eventually
            str +=
          "<tr><td align=right><b>Full Description:</b></td>"+
          "<td colspan=3 WIDTH=\"75%\"><textarea name=\"full_desc\" rows=6 cols=60></textarea></td></tr>";

          // series image
          str+=
          "<tr><td align=right><b>Series Image:</b></td>"+
          "<td colspan=3><textarea name=\"seriesimage\" rows=2 cols=60>/support/empty.gif</textarea></td></tr>";

          str +=
          "<tr><td align=right><b>Update Time:</b></td>"+
          "<td><input type=text size=8 maxlength=8 name=\"u_time\" value=\"24\">"+
          "<select name=\"u_meas\"><option>Minutes<option selected>Hours</select></td></tr>"+

          "<tr><td align=right><b>Time Limit:</b></td>"+
          "<td><input type=text size=8 maxlength=8 name=\"time_limit\" value=\"24\">"+
          "<select name=\"u_tmeas\"><option>Minutes<option selected>Hours</select></td></tr>"+

          "<tr><td align=right><b>Overtime Update Time:</b></td>"+
          "<td><input type=text size=8 maxlength=8 name=\"over_time\" value=\"24\">"+
          "<select name=\"u_omeas\"><option>Minutes<option selected>Hours</select></td></tr>"+

          "<tr><td></td><td><input type=checkbox checked name=\"weekend\"> Weekend Updates</td></tr>"+
          "<tr><td></td><td>From: <input type=text name=\"from\" value=\"0\" size=2 maxlength=2> "+
          "To: <input type=text name=\"to\" value=\"23\" size=2 maxlength=2></td></tr>"+
          "<tr><td align=right><b>First Update Delayed:</b></td>"+
          "<td><input type=text size=8 maxlength=8 name=\"u_delay\" value=\"24\">"+
          "<select name=\"ud_meas\"><option>Minutes<option selected>Hours<option>Days</select></td></tr>"+
          "</table></center>";


          // begin 3.0 stuff
          // add the option for what tech is allowed in this series
          // allowed maps to u_tech and restricted maps to r_tech
          // some that aren't included below are:
          //    For each player there are 2 new variables:
          //      dev_r which is how many restricted techs have been developed
          //      ship_count which is how many ships have been built by the player
          //
          // invited should be removed unless the player stuff is done

          str += "<center><HR WIDTH=\"80%\"><B><FONT COLOR="+heading_color+" SIZE=+2>Game Options</FONT></B></CENTER>";
          str += "<center><table>";

          foreach(all_tech, ixc) {
            if ( cnt == 0 ) {         // new row of table, 3 entries/row
              str += "<tr>";
            }
            switch (row) {
            case 0:
              s_a = s_r = s_n = "";
              s_d = "selected ";
              break;
            case 1:
            case 2:
            case 3:
              s_d = s_r = s_n = "";
              s_a = "selected ";
              break;
            default:
              s_a = s_r = s_d = "";
              s_n = "selected ";
              break;
            }
            cnt++;
            str += "<td align=right><b>" + ixc + ":</b></td> <td><select name=\"" + ixc + "\"> <option "+s_d+"value=dev>Developed<option "+s_a+"value=allowed>"+
              "Allowed<option "+s_r+"value=restricted>Restricted<option "+s_n+"value=na>Not Allowed</select></td>";

            if ( cnt == 3 ) {         // end of row
              str += "</tr>";
              cnt = 0;
              row++;
            }
          } // foreach tech
          str += "</tr>";               // make sure tag is finished

            // how many restricted techs can be used
            str +=
          "<tr><td align=right><b>Restricted Tech Allowed:</b></td>"+
          "<td><input type=text size=5 maxlength=5 name=\"ar_tech\" value=\"0\"></td>";

            // how many trade-ins are allowed
            str +=
          "<td align=right><b>Number of Trade-ins:</b></td>"+
          "<td><input type=text size=5 maxlength=5 name=\"tr_tech\" value=\"0\"></td></tr>";

          // Stargate Ranged
            str +=
            "<tr><td align=right><b>Stargate Range Divisor:</b></td>"+
          "<td><input type=text size=5 maxlength=5 name=\"stargate\" value=\"0\"></td>";

          // Jumpgate Ranged
            str +=
            "<td align=right><b>Jumpgate Range Divisor:</b></td>"+
          "<td><input type=text size=5 maxlength=5 name=\"jumpgate\" value=\"1\"></td></tr>";
            str += "</table></center>";

          // Losses....
          str += "<center><HR WIDTH=\"80%\"><B><FONT COLOR="+heading_color+" SIZE=+2>Ship Parameters</FONT></B></CENTER>";
          str += "<center><table>";

            // Max num of ships per player
            str +=
          "<tr><td align=right><b>Max num of ships per player:</b></td>"+
          "<td><input type=text size=10 maxlength=10 name=\"max_ship\" value=\"0\"></td><td align=right><b>0 -> No Maximum</b></td></tr>";

            // engineer loss
            str +=
          "<tr><td align=right><b>Engineer Loss:</b></td>"+
          "<td><input type=text size=10 maxlength=10 name=\"eng_loss\" value=\"0.5\"></td>";

            // Jumpgate loss
            str +=
          "<td align=right><b>Jumpgate Loss:</b></td>"+
          "<td><input type=text size=10 maxlength=10 name=\"jump_loss\" value=\"0.5\"></td></tr>";

            // Morpher loss
            str +=
          "<tr><td align=right><b>Morpher Loss:</b></td>"+
          "<td><input type=text size=10 maxlength=10 name=\"morph_loss\" value=\"0.5\"></td>";

            // Carrier loss
            str +=
          "<td align=right><b>Carrier Loss:</b></td>"+
          "<td><input type=text size=10 maxlength=10 name=\"car_loss\" value=\"0.5\"></td></tr>";

           // Morpher build cost
            str +=
          "<tr><td align=right><b>Morpher build cost:</b></td>"+
          "<td><input type=text size=10 maxlength=10 name=\"morpher_build\" value=\"75\"></td>";

            // Mopher maint cost
            str +=
          "<td align=right><b>Morpher maint cost:</b></td>"+
          "<td><input type=text size=10 maxlength=10 name=\"morpher_maint\"value=\"12\"></td></tr>";

            // builder build cost
            str +=
          "<tr><td align=right><b>Builder build cost:</b></td>"+
          "<td><input type=text size=10 maxlength=10 name=\"builder_build\"value=\"75\"></td>";

            // buildermaint cost
            str +=
          "<td align=right><b>Builder maint cost:</b></td>"+
          "<td><input type=text size=10 maxlength=10 name=\"builder_maint\"value=\"16\"></td></tr>";

            // jumpgate build cost
            str +=
          "<tr><td align=right><b>Jumpgate build cost:</b></td>"+
          "<td><input type=text size=10 maxlength=10 name=\"jumpgate_build\"value=\"100\"></td>";

            // jumpgatemaint cost
            str +=
          "<td align=right><b>Jumpgate maint cost:</b></td>"+
          "<td><input type=text size=10 maxlength=10 name=\"jumpgate_maint\"value=\"24\"></td></tr>";

            // carrier build cost
            str +=
          "<tr><td align=right><b>Carrier build cost:</b></td>"+
          "<td><input type=text size=10 maxlength=10 name=\"carrier_build\"value=\"100\"></td>";

            // carriermaint cost
            str +=
          "<td align=right><b>Carrier maint cost:</b></td>"+
          "<td><input type=text size=10 maxlength=10 name=\"carrier_maint\"value=\"16\"></td></tr>";
            str += "</table></center>";

            // Planet info
            str += "<center><HR WIDTH=\"80%\"><B><FONT COLOR="+heading_color+" SIZE=+2>Planet options</FONT></B></CENTER>";
            str += "<center><table>";


            // average food per planet
            str +=
          "<tr><td align=right><b>Average Ag:</b></td>"+
          "<td><input type=text size=10 maxlength=10 name=\"avg_ag\" value="+avg_rsrc+"></td>";

            // average min per planet
            str +=
          "<td align=right><b>Average Mineral:</b></td>"+
          "<td><input type=text size=10 maxlength=10 name=\"avg_min\" value="+avg_rsrc+"></td>";

            // average fuel per planet
            str +=
          "<td align=right><b>Average Fuel:</b></td>"+
          "<td><input type=text size=10 maxlength=10 name=\"avg_fuel\" value="+avg_rsrc+"></td></tr>";

            // range food per planet
            str +=
          "<tr><td align=right><b>Ag Range:</b></td>"+
          "<td><input type=text size=10 maxlength=10 name=\"range_ag\" value=0></td>";

            // range min per planet
            str +=
          "<td align=right><b>Mineral Range:</b></td>"+
          "<td><input type=text size=10 maxlength=10 name=\"range_min\" value=0></td>";

            // range fuel per planet
            str +=
          "<td align=right><b>Fuel Range:</b></td>"+
          "<td><input type=text size=10 maxlength=10 name=\"range_fuel\" value=0></td></tr>";

            // Home food per planet
            str +=
          "<tr><td align=right><b>Home Ag:</b></td>"+
          "<td><input type=text size=10 maxlength=10 name=\"home_ag\" value="+home_rsrc+"></td>";

            // Home min per planet
            str +=
          "<td align=right><b>Home Mineral:</b></td>"+
          "<td><input type=text size=10 maxlength=10 name=\"home_min\" value="+home_rsrc+"></td>";

            // Home fuel per planet
            str +=
          "<td align=right><b>Home Fuel:</b></td>"+
          "<td><input type=text size=10 maxlength=10 name=\"home_fuel\" value="+home_rsrc+"></td></tr>";

          //  // Home planet resources variation
//              str +=
//            "<tr><td align=right><b>Min. Homeworld Resources: Average - </b></td>"+
//            "<td><input type=text size=20 maxlength=20 name=\"min_hrsrc\" value=0></td></tr>"+
//            "<tr><td align=right><b>Max. Homeworld Resources: Average + </b></td>"+
//            "<td><input type=text size=20 maxlength=20 name=\"max_hrsrc\" value=0></td></tr>";


            // Cost to build a planet... defaults to 90
            str +=
          "<tr><td align=right><b>Planet Cost:</b></td>"+
          "<td><input type=text size=10 maxlength=10 name=\"pla_build\" value=90></td>";

            // Minimum Pop to build
            str +=
          "<td align=right><b>Min Pop to Build:</b></td>"+
          "<td><input type=text size=10 maxlength=10 name=\"min_build\" value=50></td></tr>";
            str += "</table></center>";

            // other game options
            str += "<center><HR WIDTH=\"80%\"><B><FONT COLOR="+heading_color+" SIZE=+2>Other options</FONT></B></CENTER>";
            str += "<center><table>";

            // Max num of games
            // a game will not be spawned if "count" is greater than "max"
            str +=
          "<tr><td align=right><b>Max num of games:</b></td>"+
          "<td><input type=text size=10 maxlength=10 name=\"max\" value=\"10\"></td>";

            // spawn_first
            str +=
            "<td align=right><b>Spawn First:</b></td>"+
            "<td> <select name=\"spawn_first\"> <option value=0>No <option value=1> Yes </select> </td>";

            // Bridier Ranking
            if (enable_bridier) {
              str +=
              "<td align=right><b>Bridier Ranking Series:</b></td>"+
              "<td> <select name=\"bridier\"> <option value=0>No <option value=1> Yes </select> </td></tr>";
            } else {
              str += "</tr>";
            }

            // Alliances etc...
            str +=
          "<tr><td align=right><b>Allow alliances/trade/truce:</b></td>"+
          "<td><select name=\"ally\"><option>Yes<option>No"+
          "<option value=Trade>Only trade/truce</select></td>";

            // Max num of allies
            str +=
          "<td align=right><b>Max num of allies:</b></td>"+
          "<td><input type=text size=10 maxlength=10 name=\"max_allies\" value=\"1\"></td>";

            // Surrender/Draw 
            str +=
          "<td align=right><b>Allow Surrender/Draw:</b></td>"+
          "<td><select name=\"surrdraw\"><option value=1>Yes<option value=0>No</select></td></tr>";

           // player visibility in the game list
            str +=
            "<tr><td align=right><b>Player Visibility:</b></td>"+
            "<td><select name=\"visp\"> <option value=0> No <option value=1> Yes </select> </td>";

           // build cloakers cloaked
            str +=
            "<td align=right><b>Build cloakers cloaked:</b></td>"+
            "<td><select name=\"init_cloak\"> <option value=0> No <option selected value=1> Yes </select> </td>";

           // visibility
            str +=
            "<td align=right><b>Build Visibility:</b></td>"+
            "<td><select name=\"vis\"><option value=0 selected>No<option value=1>Yes</select></td></tr>";



          // end 3.0 stuff

          str +=
          "<tr><td align=right><b>Layout:</b></td>"+
          "<td><select name=\"layout\"><option>Traditional<option>Mirror</select></td>"+
          "<td align=right><b>Blind until Start:</b></td>"+
          "<td><select name=\"blind\"><option value=0 selected>No<option value=1>Yes</select></td></tr>"+

          "<tr><td align=right><b>Player Max:</b></td>"+
          "<td><input type=text size=20 maxlength=20 name=\"p_max\" value=6></td></tr>"+
// ##@ 23/2/99
//          "<tr><td align=right><b>Systems per Player:</b></td>"+
//          "<td><input type=text size=20 maxlength=20 name=\"s_add\" value=8></td></tr>"+
          "<tr><td align=right><b>Min. Systems per Player:</b></td>"+
          "<td><input type=text size=20 maxlength=20 name=\"s_min\" value=8></td>"+
          "<td align=right><b>Max. Systems per Player:</b></td>"+
          "<td><input type=text size=20 maxlength=20 name=\"s_max\" value=10></td></tr>"+

// ##@ 23/2/99

          "<tr><td align=right><b>Minimum Agriculture (0==traditional):</b></td>"+
          "<td><input type=text size=20 maxlength=20 name=\"min_ag\" value=0></td>"+
          "<td align=right><b>Maximum Agriculture Ratio:</b></td>"+
          "<td><input type=text size=20 maxlength=20 name=\"max_ag\" value=100></td></tr>";

          // Tech levels
          str +=
          "<tr><td align=right><b>Tech Multiple:</b></td>"+
          "<td><input type=text size=20 maxlength=20 name=\"t_mult\" value=\"1.5\"></td>";

          // Initial Tech
          str +=
          "<td align=right><b>Initial Tech Level:</b></td>"+
          "<td><input type=text size=10 maxlength=10 name=\"init_tech\" value=\"1\"></td></tr>";

          str +=
          "<tr><td align=right><b>Minimum Wins:</b></td>"+
          "<td><input type=text size=20 maxlength=20 name=\"wins\" value=0></td>"+
          "<td align=right><b>Maximum Wins:</b></td>"+
          "<td><input type=text size=20 maxlength=20 name=\"winmax\" value=-1></td></tr>"+
          "<tr><td align=right></td><td></td>"+
          "<td align=right><b>(-1 -> No Maximum)</b></td><td></td></tr>"+

          "<tr></tr><tr><td></td><td colspan=2 align=right>"+
          "<input type=submit name=\"confirm\" value=\"Start Series\">"+
          "<input type=submit name=\"confirm\" value=\"Cancel\"></td></tr>"+
          "</table></center>"+
          footer(name);

  return (["data":str,
          "type":"text/html"]);
}

mapping start_series2(object id)
{
  string name=id["variables"]["name"];
  string c_pass=id["variables"]["c_pass"];
  string index;

  if(!admin_id_check(name, c_pass, id))
    return login_failed("Identity checking failed during login. Please try again.");  // do  not advertise admin stuff!!

  if(id["variables"]["confirm"]=="Cancel")
    return admin_login(name, c_pass, "Series creation cancelled.");

  foreach(indices(d), index)
    if(id["variables"]["s_name"]==index)
      return start_series(name, c_pass, "That series name is already in use.");

  string s_name=id["variables"]["s_name"];
  if(search(s_name, "=")!=-1)
    return start_series(name, c_pass, "The series name contained an =.");

  int u_time=(int) id["variables"]["u_time"];
  if(u_time==0)
    return start_series(name, c_pass, "An invalid update time was entered.");

  if(id["variables"]["u_meas"]=="Hours") u_time*=3600;
  else u_time*=60;

  // 2.9 put in the time limit and overtime
  int time_limit=(int) id["variables"]["time_limit"];
  if(time_limit==0)
    return start_series(name, c_pass, "An invalid time limit was entered.");

  switch(id["variables"]["u_tmeas"])
    {
    case "Hours":
      time_limit*=3600;
      break;
    case "Days":
      time_limit*=86400;
      break;
    default:
      time_limit*=60;
    }
  int over_time=(int) id["variables"]["over_time"];
  if(over_time==0)
    return start_series(name, c_pass, "An invalid overtime update time was entered.");

  if(id["variables"]["u_omeas"]=="Hours") over_time*=3600;
  else over_time*=60;
  // 2.9 end of time limit and over time

  int weekend=(int) (id["variables"]["weekend"]=="on");
  int from=(int) id["variables"]["from"];
  int to=(int) id["variables"]["to"];

  int u_delay=(int) id["variables"]["u_delay"];
  switch(id["variables"]["ud_meas"])
    {
    case "Hours":
      u_delay*=3600;
      break;
    case "Days":
      u_delay*=86400;
      break;
    default:
      u_delay*=60;
    }

  int p_max=(int) id["variables"]["p_max"];
  if(p_max<2)
    return start_series(name, c_pass,
                        "An invalid player maximum was entered.");

  if(p_max>2 && (int) id["variables"]["bridier"])
    return start_series(name, c_pass,
                        "Bridier Ranking may be set only on 2-player games at present.");

// ##@ 23/2/99
//  int s_add=(int) id["variables"]["s_add"];
//  if(s_add<3)
//    return start_series(name, c_pass,
//                        "Need at least three (3) systems per player.");

  int s_add, s_min, s_max;
  if( id["variables"]["s_add"] )
    s_add=(int) id["variables"]["s_add"];
  else
  {
    s_min=(int) id["variables"]["s_min"];
    s_max=(int) id["variables"]["s_max"];
  }

  if(s_min<3 || s_max<3)
    return start_series(name, c_pass, "Systems per player must be more than two.");
  if(s_max<s_min)
    return start_series(name, c_pass, "Max. systems per player was less than Min.");

// ##@ 23/2/99


  // 2.9 added minimum agriculture
  int min_ag=(int) id["variables"]["min_ag"];
  if (min_ag<0) min_ag = 0;

  // 2.9 added maximum agriculture
  float max_ag=(float) id["variables"]["max_ag"];
  if (max_ag<1) max_ag = 1.0;

  float t_mult=(float) id["variables"]["t_mult"];
  if(t_mult==0)
    return start_series(name, c_pass, "Tech multiple needs to be greater than zero.");

  int wins=(int) id["variables"]["wins"];
  int winmax=(int) id["variables"]["winmax"];
  if ( wins < 0 ) wins = 0;
  if(winmax<wins && winmax != -1)
    return start_series(name, c_pass, "Max wins was less than min wins.");

  string simage = (string) id["variables"]["seriesimage"];

  d[s_name]=([]);
  // Begin 3.0 stuff

// need to do r_tech and u_tech arrays
  	d[s_name]["r_tech"] = "";
  	d[s_name]["u_tech"] = "";
    d[s_name]["d_tech"] = "";

	string ixc;
	foreach (all_tech, ixc) {
		switch(id["variables"][ixc]) {
            case "dev":
              if (d[s_name]["d_tech"] != "") {
                  d[s_name]["d_tech"] += " ";
              } // if
              d[s_name]["d_tech"] += ixc;
              break;
            case "allowed":
				d[s_name]["u_tech"] += ixc + " ";
				break;
			case "na":
				break;
			case "restricted":
				d[s_name]["r_tech"] += ixc + " ";
				break;
		} // switch
	} // foreach


    d[s_name]["ar_tech"] =(int) id["variables"]["ar_tech"];
    d[s_name]["tr_tech"] =(int) id["variables"]["tr_tech"];
    d[s_name]["vis"] = (int) id["variables"]["vis"];
    d[s_name]["visp"] = (int) id["variables"]["visp"];
    d[s_name]["init_cloak"] = (int) id["variables"]["init_cloak"];
    d[s_name]["spawn_first"] = (int) id["variables"]["spawn_first"];
    d[s_name]["bridier"] = (int) id["variables"]["bridier"];
    d[s_name]["stargate"] = (float) id["variables"]["stargate"];
    d[s_name]["jumpgate"] = (float) id["variables"]["jumpgate"];
    d[s_name]["eng_loss"] = (float) id["variables"]["eng_loss"];
    d[s_name]["jump_loss"] = (float) id["variables"]["jump_loss"];
    d[s_name]["morph_loss"] = (float) id["variables"]["morph_loss"];
    d[s_name]["car_loss"] = (float) id["variables"]["car_loss"];
    d[s_name]["max"] = (int) id["variables"]["max"];
    d[s_name]["max_ship"] = (int) id["variables"]["max_ship"];
    d[s_name]["init_tech"] = (float) id["variables"]["init_tech"];

    d[s_name]["morpher_build"] = (int) id["variables"]["morpher_build"];
    d[s_name]["morpher_maint"] = (int) id["variables"]["morpher_maint"];
    d[s_name]["builder_build"] = (int) id["variables"]["builder_build"];
    d[s_name]["builder_maint"] = (int) id["variables"]["builder_maint"];

	d[s_name]["jumpgate_build"] = (int) id["variables"]["jumpgate_build"];
	d[s_name]["jumpgate_maint"] = (int) id["variables"]["jumpgate_maint"];

	d[s_name]["carrier_build"] = (int) id["variables"]["carrier_build"];
	d[s_name]["carrier_maint"] = (int) id["variables"]["carrier_maint"];

	d[s_name]["avg_ag"] = (int) id["variables"]["avg_ag"];
	d[s_name]["avg_min"] = (int) id["variables"]["avg_min"];
	d[s_name]["avg_fuel"] = (int) id["variables"]["avg_fuel"];

	d[s_name]["range_ag"] = (int) id["variables"]["range_ag"];
	d[s_name]["range_min"] = (int) id["variables"]["range_min"];
	d[s_name]["range_fuel"] = (int) id["variables"]["range_fuel"];

	d[s_name]["home_ag"] = (int) id["variables"]["home_ag"];
	d[s_name]["home_min"] = (int) id["variables"]["home_min"];
	d[s_name]["home_fuel"] = (int) id["variables"]["home_fuel"];
//  not implemented yet
//    d[s_name]->min_hrsrc = (int) id["variables"]["min_hrsrc"];
//    d[s_name]->max_hrsrc = (int) id["variables"]["max_hrsrc"];

	d[s_name]["pla_build"] = (int) id["variables"]["pla_build"];
    d[s_name]["min_build"] = (int) id["variables"]["min_build"];

    d[s_name]["desc"] = (string) id["variables"]["desc"];

    d[s_name]["full_desc"] = (string) id["variables"]["full_desc"];
    if ( d[s_name]["full_desc"] == "" )
      d[s_name]["full_desc"] = d[s_name]["desc"];

	d[s_name]["count"] = 0;

/*
    d[s_name]["restricted"] = (int) id["variables"]["s_restrict"];
    d[s_name]["s_pass"] =  (string) id["variables"]["series_pass"];
*/

  // end 3.0 stuff

  d[s_name]["color"]= (string) id["variables"]["series_color"];

  d[s_name]["u_time"]=u_time;
  d[s_name]["time_limit"]=time_limit;
  d[s_name]["over_time"]=over_time;
  d[s_name]["weekend"]=weekend;
  d[s_name]["from"]=from;
  d[s_name]["to"]=to;
  d[s_name]["u_delay"]=u_delay;
  d[s_name]["p_max"]=p_max;

//  if( d[s_name]["s_add"] )   how could there be if we are creating a new series?
//    d[s_name]["s_add"]=s_add;
//  else {
  d[s_name]["s_min"]=s_min;
  d[s_name]["s_max"]=s_max;
//  }
  d[s_name]["min_ag"]=min_ag;
  d[s_name]["max_ag"]=max_ag;
  d[s_name]["t_mult"]=t_mult;
  d[s_name]["wins"]=wins;
  d[s_name]["winmax"]=winmax;
  d[s_name]["simage"]=simage;
  d[s_name]["g_cnt"]=(int) 0;

  d[s_name]["max_allies"] = (int) id["variables"]["max_allies"];
  if(id->variables->ally == "No") {
    d[s_name]["blood"]=1;
    d[s_name]["max_allies"] = 0;
  }
  else if(id->variables->ally == "Trade")
    d[s_name]["blood"]=2;
  else
    d[s_name]["blood"]=0;

  d[s_name]["surrdraw"] = (int) id["variables"]["surrdraw"];
  d[s_name]["blind"] = (int) id["variables"]["blind"];

  if(id->variables->layout=="Traditional")
    d[s_name]["layout"]=1;
  else
    d[s_name]["layout"]=2;

/* -----------------16/10/97 2:57--------------------
  d[s_name]["full_desc"] +=
    "<tr><td align=center><b><font color="+d[s_name]["color"]+">"+s_name+" series</font></b></td>"+
            "<td><img src="+d[s_name]["simage"]+"></td>"+
            "<td colspan=5>Updates every "+u_time+" for the first "+time_limit+
            " then every " +over_time + ", "+weekend+
            "active from "+d[s_name]["from"]+" until "+d[s_name]["to"]+" hours, "+
            d[s_name]["p_max"]+" players, "+
            d[s_name]["s_add"]+" systems per player, "+
            d[s_name]["min_ag"] + " minimum starting ag " +
            d[s_name]["max_ag"] + " max ag ratio " +
            d[s_name]["t_mult"]+" tech, "+
            "between "+d[s_name]["wins"]+" and "+
            d[s_name]["winmax"]+" wins can join. "+
            (d[s_name]->blood == 1 ? "Truce, trade or alliance not allowed.":
             d[s_name]->blood == 2 ? "No alliances allowed. " : "")+
            (d[s_name]->layout == 1 ? "": "Mirrored Layout. ")+
    	"<table>"+
	    "<tr><td>Restricted Tech: </td><td>" + d[s_name]["r_tech"] + "</td></tr>" +
	    "<tr><td> Unrestricted Tech: </td><td>" + d[s_name]["u_tech"] + "</td></tr>" +
        "<tr><td> Developed Tech: </td><td>" + d[s_name]["d_tech"] + "</td></tr>" +
        "<tr><td> Allowed number of Restricted: </td><td>" + d[s_name]["ar_tech"] + "</td></tr>" +
        "<tr><td> Build Visibility: </td><td>" + (d[s_name]["vis"]?"Yes":"No") + "</td></tr>" +
	      "<tr><td> Type of stargates: </td><td>" + d[s_name]["stargate"] + "</td></tr>" +
	      "<tr><td> Engineer Loss: </td><td>" + d[s_name]["eng_loss"] + "</td></tr>" +
	      "<tr><td> Jumpgate Loss: </td><td>" + d[s_name]["jump_loss"] + "</td></tr>" +
	      "<tr><td> Morpher Loss: </td><td>" + d[s_name]["morph_loss"] + "</td></tr>" +
	      "<tr><td> Carrier Loss: </td><td>" + d[s_name]["car_loss"] + "</td></tr>" +
	      "<tr><td> Max Num Ships: </td><td>" + d[s_name]["max_ship"] + "</td></tr>" +
	      "<tr><td> Initial Tech: </td><td>" + d[s_name]["init_tech"] + "</td></tr>" +
	      "<tr><td> Max Number of Allies: </td><td>" + d[s_name]["max_allies"] + "</td></tr>" +
	      "<tr><td> Morpher Build Cost: </td><td>" + d[s_name]["morpher_build"] + "</td></tr>" +
	      "<tr><td> Morpher Maint Cost: </td><td>" + d[s_name]["morpher_maint"] + "</td></tr>" +
	      "<tr><td> Builder Build Cost: </td><td>" + d[s_name]["builder_build"] + "</td></tr>" +
	      "<tr><td> Builder Maint Cost: </td><td>" + d[s_name]["builder_maint"] + "</td></tr>" +
	      "<tr><td> Jumpgate Build Cost: </td><td>" + d[s_name]["jumpgate_build"] + "</td></tr>" +
	      "<tr><td> Jumpgate Maint Cost: </td><td>" + d[s_name]["jumpgate_maint"] + "</td></tr>" +
	      "<tr><td> Carrier Build Cost: </td><td>" + d[s_name]["carrier_build"] + "</td></tr>" +
	      "<tr><td> Carrier Maint Cost: </td><td>" + d[s_name]["carrier_maint"] + "</td></tr>" +
	      "<tr><td> Avg System Ag: </td><td>" + d[s_name]["avg_ag"] + "</td></tr>" +
          "<tr><td> Avg System Minerals: </td><td>" + d[s_name]["avg_min"] + "</td></tr>" +
	      "<tr><td> Avg System Fuel: </td><td>" + d[s_name]["avg_fuel"] + "</td></tr>" +
	      "<tr><td> Home System Ag: </td><td>" + d[s_name]["home_ag"] + "</td></tr>" +
          "<tr><td> Home System Minerals: </td><td>" + d[s_name]["home_min"] + "</td></tr>" +
	      "<tr><td> Home System Fuel: </td><td>" + d[s_name]["home_fuel"] + "</td></tr>" +
	      "<tr><td> Planet Build cost: </td><td>" + d[s_name]["pla_build"] + "</td></tr></table></td></tr>";
*/

  d[s_name]["0"] = ([]);
  spawn_game(s_name);

  return admin_login(name, c_pass, "Series "+s_name+" successfully started.");
}


mapping kill_game(string name, string c_pass)
{
  string ixa, ixb;
  string k_list="";

  foreach(indices(d), ixa) {
    k_list+="<tr><td align=right><b>"+ixa+" series</b></td></tr>";
    foreach(indices(d[ixa]), ixb) {
      switch(ixb)  {
        case DBSTUFF :                       // ignore series parameters except....
          break;
        default:                             // ...game number
          //	if(ixb==(string) d[ixa]["g_cnt"]) break;
          k_list+=("<tr><td>Game "+ixb+"</td>"+
                   "<td><input type=submit name=\"k:"+ixa+":"+ixb+"\" value=\"Ok\"></td></tr>");
        }
    }
  }

  return (["data":
          header("Kill Game", name)+
          "<input type=hidden name=\"name\" value=\""+name+"\">"+
          "<input type=hidden name=\"c_pass\" value=\""+c_pass+"\">"+
          "<input type=hidden name=\"action\" value=\"kill_game\">"+
//          "<center><b>Kill a Game</b></center>"+
          pageheader("Kill a Game", name)+
          "<p><img src=\""+theme_dir(name)+"/spacerule.gif\" height=10  width=100%><p>"+
          "<center><table><td align=right>Cancel this action</td>"+
          "<td><input type=submit name=\"k:cancel\" value=\"Cancel\"></td></tr>"+
          k_list+
          "</table></center>"+
          footer(name),
          "type":"text/html"]);
}

mapping kill_game2(object id)
{
  string name=id["variables"]["name"];
  string c_pass=id["variables"]["c_pass"];

  if(!admin_id_check(name, c_pass, id))
    return login_failed("Identity checking failed during login. Please try again.");  // do  not advertise admin stuff!!

  string index;
  string *a;

  foreach(indices(id["variables"]), index) {
    a=index/":";
    if(a[0]=="k") break;
  }

  if(a[1]=="cancel")
    return admin_login(name, c_pass, "Game kill cancelled.");

  d[a[1]]-=([a[2]:""]);
  d[a[1]]["count"]--;

  return admin_login(name, c_pass, "Game "+a[2]+" of series "+a[1]+" killed.");
}

// 2.9 function
mapping view_game(string name, string c_pass)
{
  string ixa, ixb;
  string k_list="";

  foreach(indices(d), ixa) {
    k_list+="<tr><td align=right><b>"+ixa+" series</b></td></tr>";
    foreach(indices(d[ixa]), ixb) {
      switch(ixb) {
        case DBSTUFF :                       // ignore series parameters except....
          break;
        default:                             // ...game number
          //	if(ixb==(string) d[ixa]["g_cnt"]) break;
          k_list+=("<tr><td>Game "+ixb+"</td>"+
                   "<td><input type=submit name=\"view:"+ixa+":"+ixb+"\" value=\"Ok\"></td></tr>");
      }
    }
  }

  return (["data":
          header("View Game", name)+
          "<input type=hidden name=\"name\" value=\""+name+"\">"+
          "<input type=hidden name=\"c_pass\" value=\""+c_pass+"\">"+
          "<input type=hidden name=\"action\" value=\"view_game\">"+
//          "<center><b>View a Game</b></center>"+
          pageheader("View a Game", name)+
          "<p><img src=\""+theme_dir(name)+"/spacerule.gif\" height=10  width=100%><p>"+
          "<center><table><td align=right>Cancel this action</td>"+
          "<td><input type=submit name=\"view:cancel\" value=\"Cancel\"></td></tr>"+
          k_list+
          "</table></center>"+
          footer(name),
          "type":"text/html"]);
}

mapping view_game2(object id)
{
  string name=id["variables"]["name"];
  string c_pass=id["variables"]["c_pass"];

  if(!admin_id_check(name, c_pass, id))
    return login_failed("Identity checking failed during login. Please try again.");  // do  not advertise admin stuff!!

  string index;
  string *a;

  foreach(indices(id["variables"]), index) {
    a=index/":";
    if(a[0]=="view") break;
  }

  if(a[1]=="cancel")
    return admin_login(name, c_pass, "Game view cancelled.");

  return admin_login(name, c_pass, admin_systems_screen (a[1],a[2]));
}


mapping kill_series(string name, string c_pass)
{
  string index;
  string k_list="";

  foreach(indices(d), index)
    k_list+=("<tr><td align=right>"+index+" series</td>"+
             "<td><input type=submit name=\"k:"+index+"\" value=\"Ok\"></td></tr>");

  return (["data":
          header("Kill a Series", name)+
          "<input type=hidden name=\"name\" value=\""+name+"\">"+
          "<input type=hidden name=\"c_pass\" value=\""+c_pass+"\">"+
          "<input type=hidden name=\"action\" value=\"kill_series\">"+
//          "<center><b>Kill a Series</b></center>"+
          pageheader("Kill Series", name)+
          "<p><img src=\""+theme_dir(name)+"/spacerule.gif\" height=10  width=100%><p>"+
          "<center><table><td align=right>Cancel this action</td>"+
          "<td><input type=submit name=\"k:cancel\" value=\"Cancel\"></td></tr>"+
          k_list+
          "</table></center>"+
          footer(name),
          "type":"text/html"]);
}

mapping kill_series2(object id)
{
  string name=id["variables"]["name"];
  string c_pass=id["variables"]["c_pass"];

  if(!admin_id_check(name, c_pass, id))
    return login_failed("Identity checking failed during login. Please try again.");  // do  not advertise admin stuff!!

  string index;
  string *a;
  string kill;

  foreach(indices(id["variables"]), index) {
    a=index/":";
    if(a[0] != "k") continue;
    kill=a[1];
    break;
  }

  if(kill=="cancel")
    return admin_login(name, c_pass, "Series kill cancelled.");

  d-=([kill:""]);

  return admin_login(name, c_pass, "Series "+kill+" killed.");
}

/*
 mapping flow_series(string name, string c_pass, int state)
{
  string index;
  string f_list="";

  foreach(indices(d), index)
    {
      if(d[index][(string) d[index]["g_cnt"]]["spawn"]==state)
        f_list+=("<tr><td align=right>"+index+" series</td>"+
                 "<td><input type=submit name=\"f:"+index+
                 "\" value=\"Ok\"></td></tr>");
    }
  string word=state?"Resume":"Halt";
  return (["data":
          header("Series Flow", name)+
          "<input type=hidden name=\"name\" value=\""+name+"\">"+
          "<input type=hidden name=\"c_pass\" value=\""+c_pass+"\">"+
          "<input type=hidden name=\"action\" value=\"flow_series\">"+
          "<input type=hidden name=\"state\" value=\""+state+"\">"+
          "<center><h1>"+word+" a Series</h1></center>"+
          "<p><img src=\""+theme_dir(name)+"/spacerule.gif\" width=100%><p>"+
          "<center><table><td align=right>Cancel this action</td>"+
          "<td><input type=submit name=\"f:cancel\" "
          "value=\"Cancel\"></td></tr>"+f_list+"</table></center>"+footer(name),
          "type":"text/html"]);
}

mapping flow_series2(object id)
{
  string name=id["variables"]["name"];
  string c_pass=id["variables"]["c_pass"];
  int state=(int) id["variables"]["state"];

  if(!admin_id_check(name, c_pass, id))
    return login_failed("Identity checking failed during an administrative login.");

  string index;
  string *a;
  string flow;

  foreach(indices(id["variables"]), index) {
    a=index/":";
    if(a[0] != "f") continue;
    flow=a[1];
    break;
  }

  if(flow=="cancel")
    return admin_login(name, c_pass, "Series flow change cancelled.");

  d[flow][(string) d[flow]["g_cnt"]]["spawn"]=!state;

  string word=state?"resumed":"halted";

  return admin_login(name, c_pass, "Series "+flow+" "+word+".");
}
 */

//  Database save using encode_value()
// varargs void dump_data(int split, int exit_now) {
void dump_data() {

  string ixa, ixb, ixc, ixd, ixe, ixf, type;
  int pid, start;

  mapping players = copy_value(p);      // duplicate databases toprevent corruption during save
  mapping games = copy_value(d);

/* -----------------17/05/98 17:18-------------------
    Removed server fork when saving files.
    Delays server whilst saving but does not use
        so much memory. (should be actually quicker)

    Added 4 layer backup rotation


  if(split)
    if((pid=fork())<0) {
      debug("Data_dump fork error");
      return;
    }

  if(pid==0) {
    start = time();
    perror("Data dump started...");
*/

// removed #if efun(encode_value) ...   always use 'encode_value'
    start = time();
    perror("Database save started using encode().\n");
#if ROXEN_VER == "1.2"
    Stdio.write_file(dump_file+".pl.new", encode_value(players));
    Stdio.write_file(dump_file+".g.new", encode_value(games));
#else
    write_file(dump_file+".pl.new", encode_value(players));
    write_file(dump_file+".g.new", encode_value(games));
#endif

    mv(dump_file+".pl.3", dump_file+".pl.4");
    mv(dump_file+".pl.2", dump_file+".pl.3");
    mv(dump_file+".pl.1", dump_file+".pl.2");
    mv(dump_file+".pl", dump_file+".pl.1");
    mv(dump_file+".pl.new", dump_file+".pl");

    mv(dump_file+".g.3", dump_file+".g.4");
    mv(dump_file+".g.2", dump_file+".g.3");
    mv(dump_file+".g.1", dump_file+".g.2");
    mv(dump_file+".g", dump_file+".g.1");
    mv(dump_file+".g.new", dump_file+".g");

    perror(sprintf("Completed in %s.\n", time_conv(time() - start)));

//   Server fork stuff
//    if(exit_now || split)
//      exit(0);
//  }
}


//  Database save using ASCII format()
//varargs void dump_asciidata(int split, int exit_now) {
void dump_asciidata() {

  string ixa, ixb, ixc, ixd, ixe, ixf, type;
  int pid, start;

  mapping players = copy_value(p);      // duplicate databases toprevent corruption during save
  mapping games = copy_value(d);

/* -----------------17/05/98 17:18-------------------
    Removed server fork when saving files.
    Delays server whilst saving but does not use
        so much memory. (should be actually quicker)

    Added 4 layer backup rotation


  if(split)
    if((pid=fork())<0) {
      debug("Data_dump fork error");
      return;
    }

  if(pid==0) {
    start = time();
    perror("Data dump started...");
*/
#if ROXEN_VER == "1.2"
    object dump_fp = Stdio.File();
#else
   object dump_fp = clone((program)"/precompiled/file");
#endif

    start = time();
    perror("Database save started using ASCII format:\n");

    dump_fp->open(dump_file+".new", "wc");
    dump_fp->write("p=\n");

    foreach(indices(p), ixa) {
      dump_fp->write("0/m="+ixa+"=\n");

      foreach(indices(p[ixa]), ixb) {
        if(stringp(p[ixa][ixb])) type="s";
        else if(intp(p[ixa][ixb])) type="i";
        else if(floatp(p[ixa][ixb])) type="f";
        else type="x";

        dump_fp->write("1/"+type+"="+ixb+"="+p[ixa][ixb]+"\n");
      }
    }

    dump_fp->write("d=\n");
    foreach(indices(d), ixa) {
      dump_fp->write("0/m="+ixa+"=\n");

      foreach(indices(d[ixa]), ixb) {
        if(mappingp(d[ixa][ixb])) {
          dump_fp->write("1/m="+ixb+"=\n");

          foreach(indices(d[ixa][ixb]), ixc) {
            if(mappingp(d[ixa][ixb][ixc])) {
              dump_fp->write("2/m="+ixc+"=\n");

              foreach(indices(d[ixa][ixb][ixc]), ixd) {
                if(mappingp(d[ixa][ixb][ixc][ixd])) {
                  dump_fp->write("3/m="+ixd+"=\n");

                  foreach(indices(d[ixa][ixb][ixc][ixd]), ixe) {
                    if(mappingp(d[ixa][ixb][ixc][ixd][ixe])) {
                      dump_fp->write("4/m="+ixe+"=\n");

                      foreach(indices(d[ixa][ixb][ixc][ixd][ixe]), ixf) {
                        if(stringp(d[ixa][ixb][ixc][ixd][ixe][ixf])) type="s";
                        else if(intp(d[ixa][ixb][ixc][ixd][ixe][ixf])) type="i";
                        else if(floatp(d[ixa][ixb][ixc][ixd][ixe][ixf])) type="f";
                        else type="x";

                        dump_fp->write("5/"+type+"="+ixf+"="+
                                       d[ixa][ixb][ixc][ixd][ixe][ixf]+"\n");
                      }
                    } else {
                      if(stringp(d[ixa][ixb][ixc][ixd][ixe])) type="s";
                      else if(intp(d[ixa][ixb][ixc][ixd][ixe])) type="i";
                      else if(floatp(d[ixa][ixb][ixc][ixd][ixe])) type="f";
                      else type="x";

                      dump_fp->write("4/"+type+"="+ixe+"="+
                                     d[ixa][ixb][ixc][ixd][ixe]+"\n");
                    }
                  }
                } else {
                  if(stringp(d[ixa][ixb][ixc][ixd])) type="s";
                  else if(intp(d[ixa][ixb][ixc][ixd])) type="i";
                  else if(floatp(d[ixa][ixb][ixc][ixd])) type="f";
                  else type="x";

                  dump_fp->write("3/"+type+"="+ixd+"="+
                                 d[ixa][ixb][ixc][ixd]+"\n");
                }
              }
            } else {
              if(stringp(d[ixa][ixb][ixc])) type="s";
              else if(intp(d[ixa][ixb][ixc])) type="i";
              else if(floatp(d[ixa][ixb][ixc])) type="f";
              else type="x";

              dump_fp->write("2/"+type+"="+ixc+"="+d[ixa][ixb][ixc]+"\n");
            }
          }
        } else {
          if(stringp(d[ixa][ixb])) type="s";
          else if(intp(d[ixa][ixb])) type="i";
          else if(floatp(d[ixa][ixb])) type="f";
          else type="x";

          dump_fp->write("1/"+type+"="+ixb+"="+d[ixa][ixb]+"\n");
        }
      }
    }

    dump_fp->write("end\n");
    dump_fp->close();
    destruct(dump_fp);

    mv(dump_file+".3", dump_file+".4");
    mv(dump_file+".2", dump_file+".3");
    mv(dump_file+".1", dump_file+".2");
    mv(dump_file, dump_file+".1");
    mv(dump_file+".new", dump_file);

//    mv(dump_file, dump_file+".old");
//    mv(dump_file+".new", dump_file);
    perror(sprintf("Completed in %s.\n", time_conv(time() - start)));
}



#ifdef SC_DEBUG
void auto_dump()
{
  if(fd_fail()) {
    debug("fd failure, restarting server");
//    dump_data(0, 1);
    dump_data();
  }

  call_out(auto_dump, 300);
}
#endif

mapping admin_dump_data(string name, string c_pass)
{
//  dump_data(1);
  dump_data();
  return admin_login(name, c_pass, "Database save in progress.");
}

mapping admin_dump_asdata(string name, string c_pass)
{
//  dump_asciidata(1);
  dump_asciidata();
  return admin_login(name, c_pass, "ASCII Database save in progress.");
}

// Database load using decode_value()
int load_data()
{
  mixed *a;
  int i, j, games, start, start2;
  string type, key, val;
  string buf, line, line2;
  array a;

#if ROXEN_VER == "1.2"
    object dump_fp=Stdio.File();
#else
   object dump_fp=clone((program)"/precompiled/file");
#endif

//  object dump_fp=clone((program)"/precompiled/file");
//  object dump_fp=Stdio.File();

  p=([]);
  d=([]);
  mapping b=([]);
  start = start2 = time();

  // 2.9 these didn't work for me, but you may want to try
  // them for yourself

// removed #if efun(decode_value)...  alwayse use 'decode_value'

/*    perror("Restoring (using decode_value players: "+dump_file+".pl :");
    p = decode_value(read_file(dump_file+".pl"));
    perror(sprintf("done in %s.\nRestoring saved games: "+dump_file+".g :",
                     time_conv(time() - start)));
    d = decode_value(read_file(dump_file+".g"));
    perror(sprintf("done in %s.\nLoading done in %s.\n",
                     time_conv(time() - start),
                     time_conv(time() - start2)));

*/
  if(dump_fp->open(dump_file+".pl", "r")) {

    perror("Restoring (using decode_value players: "+dump_file+".pl :");
    p = decode_value(dump_fp->read(100000000));
    dump_fp->close();
    perror(sprintf("done in %s.\nRestoring saved games: "+dump_file+".g :",
                     time_conv(time() - start)));
    start = time();
    if(dump_fp->open(dump_file+".g", "r")) {
      d = decode_value(dump_fp->read(100000000));
      dump_fp->close();
    }
    perror(sprintf("done in %s.\nLoading done in %s.\n",
                     time_conv(time() - start),
                     time_conv(time() - start2)));
    destruct(dump_fp);
    return 1;
  } else {
    perror("Unable to open player database! ");
    return 0;
  }
//#endif
}


// Database load using ASCII format
int load_asciidata()
{
  mixed *a;
  int i, j, games, start, start2;
  string type, key, val;
  string buf, line, line2;
  array a;

#if ROXEN_VER == "1.2"
    object dump_fp=Stdio.File();
#else
   object dump_fp=clone((program)"/precompiled/file");
#endif
//  object dump_fp=clone((program)"/precompiled/file");
//  object dump_fp=Stdio.File();

  p=([]);
  d=([]);
  games = 0;
  mapping b=([]);
  start = start2 = time();

  if(!dump_fp->open(dump_file, "r"))
    return 0;

  for(buf=""; strlen(line = dump_fp->read(1024) ||"");) {
    buf += line;
    while(sscanf(buf, "%s\n%s", line, buf) == 2) {
      if (line == "p=") {
        perror("Restoring players: ");
        continue;
      } else if (line == "d=") {
        perror(sprintf("completed in %s.\nRestoring saved games: ", time_conv(time() - start)));
        start = time();
        games = 1;
        continue;
      } else if (line=="end") {
        perror(sprintf("completed in %s.\nLoading done in %s.\n",
                                 time_conv(time() - start),
                                 time_conv(time() - start2)));
        break;
      }

      sscanf(line, "%d/%s=%s=%s", j, type, key, val);
      b[j] = key;
      if (!games) {                     // restoring players details
        switch(j) {
          case 0:
            switch(type) {
              case "s":
                p[b[0]]=((string) val)-"\0" ;
                break;
              case "i":
                p[b[0]]=(int) val;
                break;
              case "f":
                p[b[0]]=(float) val;
                break;
              case "m":
                p[b[0]]=([]);
                break;
              default:
                perror("Invalid line ("+i+"): "+line+"\n");
                dump_fp->close();
                return 0;
            }
            break;
          case 1:
            switch(type) {
              case "s":
                p[b[0]][b[1]]=((string) val)-"\0";
                break;
              case "i":
                p[b[0]][b[1]]=(int) val;
                break;
              case "f":
                p[b[0]][b[1]]=(float) val;
                break;
              case "m":
                p[b[0]][b[1]]=([]);
                break;
              default:
                perror("Invalid line ("+i+"): "+line+"\n");
                dump_fp->close();
                return 0;
            }
            break;
          default:
            perror("Invalid line ("+i+"): "+line+"\n");
            dump_fp->close();
            return 0;
        }                               // switch
      } else {                          // restoring games
        switch(j) {
          case 0:
            switch(type) {
              case "s":
                d[b[0]]=((string) val)-"\0";
                break;
              case "i":
                d[b[0]]=(int) val;
                break;
              case "f":
                d[b[0]]=(float) val;
                break;
              case "m":
                d[b[0]]=([]);
                break;
              default:
                perror("Invalid line ("+i+"): "+line+"\n");
                dump_fp->close();
                return 0;
            }
            break;
          case 1:
            switch(type) {
              case "s":
                d[b[0]][b[1]]=((string) val)-"\0";
                break;
              case "i":
                d[b[0]][b[1]]=(int) val;
                break;
              case "f":
                d[b[0]][b[1]]=(float) val;
                break;
              case "m":
                d[b[0]][b[1]]=([]);
                break;
              default:
                perror("Invalid line ("+i+"): "+line+"\n");
                dump_fp->close();
                return 0;
            }
            break;
          case 2:
            switch(type) {
              case "s":
                d[b[0]][b[1]][b[2]]=((string) val)-"\0";
                break;
              case "i":
                d[b[0]][b[1]][b[2]]=(int) val;
                break;
              case "f":
                d[b[0]][b[1]][b[2]]=(float) val;
                break;
              case "m":
                d[b[0]][b[1]][b[2]]=([]);
                break;
              default:
                perror("Invalid line ("+i+"): "+line+"\n");
                dump_fp->close();
                return 0;
            }
            break;
          case 3:
            switch(type) {
              case "s":
                d[b[0]][b[1]][b[2]][b[3]]=((string) val)-"\0";
                break;
              case "i":
                d[b[0]][b[1]][b[2]][b[3]]=(int) val;
                break;
              case "f":
                d[b[0]][b[1]][b[2]][b[3]]=(float) val;
                break;
              case "m":
                d[b[0]][b[1]][b[2]][b[3]]=([]);
                break;
              default:
                perror("Invalid line ("+i+"): "+line+"\n");
                dump_fp->close();
                return 0;
            }
            break;
          case 4:
            switch(type) {
              case "s":
                d[b[0]][b[1]][b[2]][b[3]][b[4]]=((string) val)-"\0";
                break;
              case "i":
                d[b[0]][b[1]][b[2]][b[3]][b[4]]=(int) val;
                break;
              case "f":
                d[b[0]][b[1]][b[2]][b[3]][b[4]]=(float) val;
                break;
              case "m":
                d[b[0]][b[1]][b[2]][b[3]][b[4]]=([]);
                break;
              default:
                perror("Invalid line ("+i+"): "+line+"\n");
                dump_fp->close();
                return 0;
            }
            break;
          case 5:
            switch(type) {
              case "s":
                d[b[0]][b[1]][b[2]][b[3]][b[4]][b[5]]=(string) val-"\0";
                break;
              case "i":
                d[b[0]][b[1]][b[2]][b[3]][b[4]][b[5]]=(int) val;
                break;
              case "f":
                d[b[0]][b[1]][b[2]][b[3]][b[4]][b[5]]=(float) val;
                break;
              case "m":
                d[b[0]][b[1]][b[2]][b[3]][b[4]][b[5]]=([]);
                break;
              default:
                perror("Invalid line ("+i+"): "+line+"\n");
                dump_fp->close();
                return 0;
            }
            break;
          default:
            perror("Invalid line ("+i+"): "+line+"\n");
            dump_fp->close();
            return 0;
        }                               // switch(j)
      }                                 // if (!games)
    }                                   // while
  }                                     // for
  dump_fp->close();
  destruct(dump_fp);
  return 1;
}                                       // dump_asciidata()


mapping admin_load_data(string name, string c_pass)
{
  int i=load_data();
  if(i) return admin_login(name, c_pass, "Database load successful.");
  d=([]);
  p=([]);
  return admin_login(name, c_pass, "Database load failed.");
}

mapping admin_load_asdata(string name, string c_pass)
{
  int i=load_asciidata();
  if(i) return admin_login(name, c_pass, "ASCII Database load successful.");
  d=([]);
  p=([]);
  return admin_login(name, c_pass, "ASCII Database load failed.");
}

mapping fix_times(string name, string c_pass)
{
  string ixa, ixb;
  int asciidb = QUERY(asciidata);
  int last=time()-1;

  if (asciidb) {
    dump_asciidata();
  } else {
    dump_data();
  }

  foreach(indices(d), ixa) {
    foreach(indices(d[ixa]), ixb) {
      switch(ixb) {
           case DBSTUFF :                       // ignore series parameters except....
          break;
        default:
          d[ixa][ixb]["u_last"] = last;
          //	if(ixb==(string) d[ixa]["g_cnt"]) break;
      } // switch
    }
  }

  return admin_login(name, c_pass, (asciidb?"ASCII ":"")+"Database dumped and Time of Last Update set to "+ctime(last)+" for all games");
//  return (["data":
//          header("Fix Update Times", name)+
//          "<input type=hidden name=\"name\" value=\""+name+"\">"+
//          "<input type=hidden name=\"c_pass\" value=\""+c_pass+"\">"+
//          "<input type=hidden name=\"action\" value=\"rti\">"+
////          "<center><b>Fix Update times</b></center>"+
//          pageheader("Fix Update times", name)+
//          "<p><img src=\""+theme_dir(name)+"/spacerule.gif\" width=100%><p>"+
//          "<center><table><td align=right>Cancel this action</td>"+
//          "<td><input type=submit name=\"k:cancel\" value=\"Continue\"></td></tr>"+
//          "</table></center>"+
//          footer(name),
//          "type":"text/html"]);
}


// 2.9admin Additional Admin functions
//       Login message entry
mapping login_msg(string name, string c_pass)
{
  return (["data":
          header("Login message", name)+
//          "<center><h1>Stellar Crisis v"+version+"</h1></center>"
            pageheader("Login Message", name)+
          "<img src=\""+theme_dir(name)+"/spacerule.gif\" height=10  width=100%><br clear>"
          "<input type=hidden name=\"name\" value=\""+name+"\">"+
          "<input type=hidden name=\"c_pass\" value=\""+c_pass+"\">"+
          "<input type=hidden name=\"action\" value=\"login_message\">"+
          "<center><b>Login message</b>"
          "<p><textarea name=\"logmsg\" rows=10 cols=60>"+log_msg+"</textarea></p>"
          "<font color=\"#000000\"><input type=submit name=\"l:setmsg\" value=\"  Save  \">"
          "<input type=reset value=\"  Clear  \"></font></center>"
          "<img src=\""+theme_dir(name)+"/spacerule.gif\" height=10  width=100%><br clear>"
          "<center><table><td align=right>Cancel this action</td>"+
          "<td><input type=submit name=\"l:cancel\" "+
          "value=\" Cancel \"></td></tr></table></center>",
          "type":"text/html"]);
}

mapping login_msg2(object id)
{
  string name=id["variables"]["name"];
  string c_pass=id["variables"]["c_pass"];

  if(!admin_id_check(name, c_pass, id))
    return login_failed("Identity checking failed during login. Please try again.");  // do  not advertise admin stuff!!

  string index;
  string *a;
  string cmd;

  foreach(indices(id["variables"]), index) {
    a=index/":";
    if(a[0] != "l") continue;
    cmd=a[1];
    break;
  }

  if(cmd=="cancel")
    return admin_login(name, c_pass, "Login message change cancelled.");

  log_msg=id["variables"]["logmsg"];
  return admin_login(name, c_pass, "Login message set");
}

//  Edit current series
mapping edit_series(string name, string c_pass)
{
  string index;
  string e_list="";

  foreach(indices(d), index)
    e_list+=("<tr><td align=right>"+index+" series</td>"+
             "<td><input type=submit name=\"ed:"+index+"\" value=\" Edit \"></td></tr>");

  return (["data":
          header("Edit Series", name)+
          "<input type=hidden name=\"name\" value=\""+name+"\">"+
          "<input type=hidden name=\"c_pass\" value=\""+c_pass+"\">"+
          "<input type=hidden name=\"action\" value=\"edit_series\">"+
          pageheader("Edit a Series", name)+
          "<p><img src=\""+theme_dir(name)+"/spacerule.gif\" height=10  width=100%><p>"+
          "<center><table><td align=right>Cancel this action</td>"+
          "<td><input type=submit name=\"ed:cancel\" value=\"Cancel\"></td></tr>"+
          e_list+
          "</table></center>"+
          footer(name),
          "type":"text/html"]);
}

mapping edit_series2(object id, string message)
{
  string str;
  int cnt;
  string name=id["variables"]["name"];
  string c_pass=id["variables"]["c_pass"];

  if(!admin_id_check(name, c_pass, id))
    return login_failed("Identity checking failed during login. Please try again.");  // do  not advertise admin stuff!!

  string index;
  string *a;
  string edit;

  foreach(indices(id["variables"]), index) {
    a=index/":";
    if(a[0] != "ed") continue;
    edit=a[1];
    break;
  }

  if(edit=="cancel")
    return admin_login(name, c_pass, "Series edit cancelled.");

  string u_time, u_meas, over_time, u_omeas, time_limit, u_tmeas, u_delay, u_dmeas;
  string sel_mins, sel_hrs, sel_days, sel_blood, sel_layout;
  string sel_develop, sel_restrict, sel_allow, sel_not, sel_yes, sel_no;

  sel_mins = "<option selected>Minutes<option>Hours";
  sel_hrs = "<option>Minutes<option selected>Hours";
  sel_days = "<option>Minutes<option>Hours<option selected>Days";

  sel_develop = " <option selected value=dev>Developed<option value=allowed>"+
                    "Allowed<option value=restricted>Restricted<option value=na>Not Allowed";
  sel_restrict = " <option value=dev>Developed<option value=allowed>"+
                    "Allowed<option selected value=restricted>Restricted<option value=na>Not Allowed";
  sel_allow = " <option value=dev>Developed<option selected value=allowed>"+
                    "Allowed<option value=restricted>Restricted<option value=na>Not Allowed";
  sel_not = " <option value=dev>Developed<option value=allowed>"+
                    "Allowed<option value=restricted>Restricted<option selected value=na>Not Allowed";
  sel_yes = " <option value=0>No<option selected value=1>Yes";
  sel_no = " <option value=0>No<option selected value=1>Yes";

  u_meas = sel_mins;
  u_omeas = u_meas;
  u_tmeas = u_meas;
  u_dmeas = u_meas;

  if(d[edit]["u_time"] % 3600 == 0)
    {
      u_time = (d[edit]["u_time"]/3600);
      u_meas = sel_hrs;
    }
  else
    u_time = (d[edit]["u_time"] / 60);

  if(d[edit]["over_time"] % 3600 == 0)
    {
      over_time = (d[edit]["over_time"]/3600);
      u_omeas = sel_hrs;
    }
  else
    over_time = (d[edit]["over_time"] / 60);

  if(d[edit]["time_limit"] % 3600 == 0)
    {
      time_limit = (d[edit]["time_limit"]/3600);
      u_tmeas = sel_hrs;
    }
  else
    time_limit = (d[edit]["time_limit"] / 60);

  if(d[edit]["u_delay"] % 86400 == 0)
    {
      u_delay = (d[edit]["u_delay"]/86400);
      u_dmeas = sel_days;
    }
  else if(d[edit]["u_delay"] % 3600 == 0)
    {
      u_delay = (d[edit]["u_delay"]/3600);
      u_dmeas = sel_hrs;
    }
  else
    u_delay = (d[edit]["u_delay"] / 60);

  if(d[edit]->blood == 1)
    sel_blood = "<option>Yes<option selected>No<option value=Trade>Only trade / truce";
  else if(d[edit]->blood == 2)
    sel_blood = "<option>Yes<option>No<option selected value=Trade>Only trade / truce";
  else
    sel_blood = "<option selected>Yes<option>No<option value=Trade>Only trade / truce";

  if(d[edit]->layout == 2)
    sel_layout = "<option>Traditional<option selected>Mirror";
  else
    sel_layout = "<option selected>Traditional<option>Mirror";

  if ( !d[edit]["s_min"] )                          // editing an older series without variable systems?
  {
    d[edit]["s_min"] = d[edit]["s_add"];            // ensure we have sensible data
    d[edit]["s_max"] = d[edit]["s_add"];
  }

  // post the current series data
          str =
          header("Edit Series", name)+
          "<input type=hidden name=\"name\" value=\""+name+"\">"+
          "<input type=hidden name=\"c_pass\" value=\""+c_pass+"\">"+
          "<input type=hidden name=\"action\" value=\"update_series\">"+
          "<input type=hidden name=\"ed:"+edit+"\" value=\"editseries\">"+
          pageheader("Edit Series parameters", name)+
          "<font color=\"#000000\"><input type=submit name=\"update\" value=\"Update\">"+
          "<input type=submit name=\"respawn\" value=\"Respawn\">"+
          "<input type=submit name=\"confirm\" value=\"Cancel\"></font>"+
          "<p><img src=\""+theme_dir(name)+"/spacerule.gif\" height=10  width=100%><p>"+
          "<center><b><font color="+server_color+">"+message+"</font></b><table>"+

          "<tr><td align=right><b>Series Name:</b></td>"+
          "<td><input type=text size=20 maxlength=20 name=\"s_name\" value=\""+edit+"\"></td></tr>";

          // series colour
          str +=
          "<tr><td align=right><b>Series Color:</b></td>"+
          "<td><input type=text size=7 maxlength=7 name=\"series_color\" value=\""+d[edit]["color"]+"\"></td></tr>";

/* --------------------------------------------------
   Now done at Game level rather than Series level.

          // Allow restricted games
            str +=
            "<tr><td align=right><b>Restricted games:</b></td>"+
            "<td> <select name=\"s_restrict\"> "+
            (d[edit]["restricted"]?"<option value=0>No <option selected value=1> Yes":"<option selected value=0>No <option value=1> Yes")+
            " </select> </td>";

          // Series password
            str +=
            "<td align=right><b>Joining password:</b></td>"+
            "<td><input type=text size=10 maxlength=10 name=\"series_pass\" value=\""+
            (d[edit]["s_pass"]==(""||"0")?"(None)":d[edit]["s_pass"])+"\"></td></tr>";

 ---------------------------------------------------- */

// SC3.0 descriptions
          str +=
          "<tr><td align=right><b>Full Description:</b></td>"+
          "<td colspan=3 WIDTH=\"75%\"><textarea name=\"full_desc\" rows=6 cols=60>"+d[edit]["full_desc"]+"</textarea></td></tr>";

          // Brief Description that the admin enters
            str +=
          "<tr><td align=right><b>Brief Description:</b></td>"+
          "<td colspan=3 WIDTH=\"75%\"><textarea name=\"desc\" rows=2 cols=60>"+d[edit]["desc"]+"</textarea></td></tr>";

          // series image
          str+=
          "<tr><td align=right><b>Series Image:</b></td>"+
          "<td colspan=3><textarea name=\"seriesimage\" rows=2 cols=60>"+d[edit]["simage"]+"</textarea></td></tr>";

          str +=
          "<tr><td align=right><b>Update Time:</b></td>"+
          "<td><input type=text size=8 maxlength=8 name=\"u_time\" value=\""+u_time+"\">"+
          "<select name=\"u_meas\">"+u_meas+"</select></td></tr>"+

          "<tr><td align=right><b>Time Limit:</b></td>"+
          "<td><input type=text size=8 maxlength=8 name=\"time_limit\" value=\""+time_limit+"\">"+
          "<select name=\"u_tmeas\">"+u_tmeas+"</select></td></tr>"+

          "<tr><td align=right><b>Overtime Update Time:</b></td>"+
          "<td><input type=text size=8 maxlength=8 name=\"over_time\" value=\""+over_time+"\">"+
          "<select name=\"u_omeas\">"+u_omeas+"</select></td></tr>"+

          "<tr><td></td><td><input type=checkbox name=\"weekend\" "+(d[edit]["weekend"]?" checked":"") +"> Weekend Updates </td></tr>"+
          "<tr><td></td><td>From: <input type=text name=\"from\" value=\""+d[edit]["from"]+"\" size=2 maxlength=2> "+
          "To: <input type=text name=\"to\" value=\""+d[edit]["to"]+"\" size=2 maxlength=2></td></tr>"+
          "<tr><td align=right><b>First Update Delayed:</b></td>"+
          "<td><input type=text size=8 maxlength=8 name=\"u_delay\" value=\""+u_delay+"\">"+
          "<select name=\"ud_meas\">"+u_omeas+"</select></td></tr>"+
          "</table></center>";


          // begin 3.0 stuff
          // add the option for what tech is allowed in this series
          // allowed maps to u_tech and restricted maps to r_tech
          // some that aren't included below are:
          //    For each player there are 2 new variables:
          //      dev_r which is how many restricted techs have been developed
          //      ship_count which is how many ships have been built by the player
          //
          // invited should be removed unless the player stuff is done

          str += "<center><HR WIDTH=\"80%\"><B><FONT COLOR="+heading_color+" SIZE=+2>Game Options</FONT></B></CENTER>";
          str += "<center><table>";

          int ixc;
          string sel_opt;

          foreach(all_tech, ixc) {
            if ( cnt == 0 ) {         // new row of table, 3 entries/row
              str += "<tr>";
            }
            cnt++;
            sel_opt = sel_not;          // assume not allowed
            if (search (d[edit]["r_tech"], ixc) != -1)
                sel_opt = sel_restrict;
            else if (search (d[edit]["u_tech"], ixc) != -1)
                sel_opt = sel_allow;
            else if (search (d[edit]["d_tech"], ixc) != -1)
                sel_opt = sel_develop;

            str += "<td align=right><b>" + ixc + ":</b></td> <td><select name=\"" + ixc + "\">"+sel_opt+"</select></td>";
//                " <option value=dev>Developed<option value=allowed>"+
//              "Allowed<option value=restricted>Restricted<option value=na>Not Allowed</select></td>";

            if ( cnt == 3 ) {         // end of row
              str += "</tr>";
              cnt = 0;
            }
          } // foreach tech
          str += "</tr>";               // make sure tag is finished

            // how many restricted techs can be used
            str +=
          "<tr><td align=right><b>Restricted Tech Allowed:</b></td>"+
          "<td><input type=text size=5 maxlength=5 name=\"ar_tech\" value="+d[edit]["ar_tech"]+"></td>";

            // how many trade-ins are allowed
            str +=
          "<td align=right><b>Number of Trade-ins:</b></td>"+
          "<td><input type=text size=5 maxlength=5 name=\"tr_tech\" value="+d[edit]["tr_tech"]+"></td></tr>";

          // Stargate Ranged
            str +=
            "<tr><td align=right><b>Stargate Range Divisor:</b></td>"+
          "<td><input type=text size=5 maxlength=5 name=\"stargate\" value="+d[edit]["stargate"]+"></td>";

          // Jumpgate Ranged
            str +=
            "<tr><td align=right><b>Jumpgate Range Divisor:</b></td>"+
          "<td><input type=text size=5 maxlength=5 name=\"jumpgate\" value="+d[edit]["jumpgate"]+"></td></tr>";
            str += "</table></center>";

          // Losses....
          str += "<center><HR WIDTH=\"80%\"><B><FONT COLOR="+heading_color+" SIZE=+2>Ship Parameters</FONT></B></CENTER>";
          str += "<center><table>";

            // Max num of ships per player
            str +=
          "<tr><td align=right><b>Max num of ships per player:</b></td>"+
          "<td><input type=text size=10 maxlength=10 name=\"max_ship\" value="+d[edit]["max_ship"]+"></td><td align=right><b>0 -> No Maximum</b></td></tr>";

            // engineer loss
            str +=
          "<tr><td align=right><b>Engineer Loss:</b></td>"+
          "<td><input type=text size=10 maxlength=10 name=\"eng_loss\" value="+d[edit]["eng_loss"]+"></td>";

            // Jumpgate loss
            str +=
          "<td align=right><b>Jumpgate Loss:</b></td>"+
          "<td><input type=text size=10 maxlength=10 name=\"jump_loss\" value="+d[edit]["jump_loss"]+"></td></tr>";

            // Morpher loss
            str +=
          "<tr><td align=right><b>Morpher Loss:</b></td>"+
          "<td><input type=text size=10 maxlength=10 name=\"morph_loss\" value="+d[edit]["morph_loss"]+"></td>";

            // Carrier loss
            str +=
          "<td align=right><b>Carrier Loss:</b></td>"+
          "<td><input type=text size=10 maxlength=10 name=\"car_loss\" value="+d[edit]["car_loss"]+"></td></tr>";

           // Morpher build cost
            str +=
          "<tr><td align=right><b>Morpher build cost:</b></td>"+
          "<td><input type=text size=10 maxlength=10 name=\"morpher_build\" value="+d[edit]["morpher_build"]+"></td>";

            // Mopher maint cost
            str +=
          "<td align=right><b>Morpher maint cost:</b></td>"+
          "<td><input type=text size=10 maxlength=10 name=\"morpher_maint\"value="+d[edit]["morpher_maint"]+"></td></tr>";

            // builder build cost
            str +=
          "<tr><td align=right><b>Builder build cost:</b></td>"+
          "<td><input type=text size=10 maxlength=10 name=\"builder_build\"value="+d[edit]["builder_build"]+"></td>";

            // buildermaint cost
            str +=
          "<td align=right><b>Builder maint cost:</b></td>"+
          "<td><input type=text size=10 maxlength=10 name=\"builder_maint\"value="+d[edit]["builder_maint"]+"></td></tr>";

            // jumpgate build cost
            str +=
          "<tr><td align=right><b>Jumpgate build cost:</b></td>"+
          "<td><input type=text size=10 maxlength=10 name=\"jumpgate_build\"value="+d[edit]["jumpgate_build"]+"></td>";

            // jumpgatemaint cost
            str +=
          "<td align=right><b>Jumpgate maint cost:</b></td>"+
          "<td><input type=text size=10 maxlength=10 name=\"jumpgate_maint\"value="+d[edit]["jumpgate_maint"]+"></td></tr>";

            // carrier build cost
            str +=
          "<tr><td align=right><b>Carrier build cost:</b></td>"+
          "<td><input type=text size=10 maxlength=10 name=\"carrier_build\"value="+d[edit]["carrier_build"]+"></td>";

            // carriermaint cost
            str +=
          "<td align=right><b>Carrier maint cost:</b></td>"+
          "<td><input type=text size=10 maxlength=10 name=\"carrier_maint\"value="+d[edit]["carrier_maint"]+"></td></tr>";
            str += "</table></center>";

            // Planet info
            str += "<center><HR WIDTH=\"80%\"><B><FONT COLOR="+heading_color+" SIZE=+2>Planet options</FONT></B></CENTER>";
            str += "<center><table>";

            // average food per planet
            str +=
          "<tr><td align=right><b>Average Ag:</b></td>"+
          "<td><input type=text size=10 maxlength=10 name=\"avg_ag\" value="+d[edit]["avg_ag"]+"></td>";

            // average min per planet
            str +=
          "<td align=right><b>Average Mineral:</b></td>"+
          "<td><input type=text size=10 maxlength=10 name=\"avg_min\" value="+d[edit]["avg_min"]+"></td>";

            // average fuel per planet
            str +=
          "<td align=right><b>Average Fuel:</b></td>"+
          "<td><input type=text size=10 maxlength=10 name=\"avg_fuel\" value="+d[edit]["avg_fuel"]+"></td></tr>";

            // range food per planet
            str +=
          "<tr><td align=right><b>Ag Range:</b></td>"+
          "<td><input type=text size=10 maxlength=10 name=\"range_ag\" value="+d[edit]["range_ag"]+"></td>";

            // range min per planet
            str +=
          "<td align=right><b>Mineral Range:</b></td>"+
          "<td><input type=text size=10 maxlength=10 name=\"range_min\" value="+d[edit]["range_min"]+"></td>";

            // range fuel per planet
            str +=
          "<td align=right><b>Fuel Range:</b></td>"+
          "<td><input type=text size=10 maxlength=10 name=\"range_fuel\" value="+d[edit]["range_fuel"]+"></td></tr>";

            // Home food per planet
            str +=
          "<tr><td align=right><b>Home Ag:</b></td>"+
          "<td><input type=text size=10 maxlength=10 name=\"home_ag\" value="+d[edit]["home_ag"]+"></td>";

            // Home min per planet
            str +=
          "<td align=right><b>Home Mineral:</b></td>"+
          "<td><input type=text size=10 maxlength=10 name=\"home_min\" value="+d[edit]["home_min"]+"></td>";

            // Home fuel per planet
            str +=
          "<td align=right><b>Home Fuel:</b></td>"+
          "<td><input type=text size=10 maxlength=10 name=\"home_fuel\" value="+d[edit]["home_fuel"]+"></td></tr>";

// ### 11/5/99 added variable resource generation and used existing entries plus +/- options for individual items of average entry.
          // planet resources variation
//            str +=
//          "<tr><td align=right><b>Min. Homeworld Resources: Average - </b></td>"+
//          "<td><input type=text size=20 maxlength=20 name=\"min_hrsrc\" value=0></td></tr>"+
//          "<tr><td align=right><b>Max. Homeworld Resources: Average + </b></td>"+
//          "<td><input type=text size=20 maxlength=20 name=\"max_hrsrc\" value=0></td></tr>";

            // Cost to build a planet
            str +=
          "<tr><td align=right><b>Planet Cost:</b></td>"+
          "<td><input type=text size=10 maxlength=10 name=\"pla_build\" value="+d[edit]["pla_build"]+"></td>";

            // Minimum Pop to build
            str +=
          "<td align=right><b>Min Pop to Build:</b></td>"+
          "<td><input type=text size=10 maxlength=10 name=\"min_build\" value="+d[edit]["min_build"]+"></td></tr>";
            str += "</table></center>";

            // Other options
            str += "<center><HR WIDTH=\"80%\"><B><FONT COLOR="+heading_color+" SIZE=+2>Other options</FONT></B></CENTER>";
            str += "<center><table>";

            // Max num of games
            // a game will not be spawned if "count" is greater than "max"
            str +=
          "<tr><td align=right><b>Max num of games:</b></td>"+
          "<td><input type=text size=10 maxlength=10 name=\"max\" value="+d[edit]["max"]+"></td>";

            // current game count
            // fiddle in case needed to reset/update faulty game count
            str +=
          "<td align=right><b>Current games:</b></td>"+
          "<td><input type=text size=10 maxlength=10 name=\"g_count\" value="+d[edit]["count"]+"></td></tr>";

            // spawn_first
            str +=
            "<tr><td align=right><b>Spawn First:</b></td>"+
            "<td> <select name=\"spawn_first\"> "+
            (d[edit]["spawn_first"]?"<option value=0>No <option selected value=1> Yes":"<option selected value=0>No <option value=1> Yes")+
            " </select> </td>";

            // Bridier Ranking
            if (enable_bridier) {
              str +=
              "<td align=right><b>Bridier Ranking Series:</b></td>"+
              "<td> <select name=\"bridier\"> "+
              (d[edit]["bridier"]?"<option value=0>No <option selected value=1> Yes":"<option selected value=0>No <option value=1> Yes")+
              " </select> </td></tr>";
            } else {
              str += "</tr>";
            }

            // Alliances etc...
            str +=
          "<tr><td align=right><b>Allow alliances / trade / truce:</b></td>"+
          "<td><select name=\"ally\">"+sel_blood+"</select></td>";

            // Max num of allies
            str +=
          "<td align=right><b>Max num of allies:</b></td>"+
          "<td><input type=text size=10 maxlength=10 name=\"max_allies\" value="+d[edit]["max_allies"]+"></td>";

            // Surrender/Draw 
            str +=
            "<td align=right><b>Allow Surrender/Draw:</b></td>"+
            "<td><select name=\"surrdraw\"> "+
            (d[edit]["surrdraw"]?"<option selected value=1>Yes<option value=0>No":"<option value=1>Yes<option selected value=0>No")+
            "</select></td></tr>";

           // player visibility in the game list
            str +=
            "<tr><td align=right><b>Player Visibility:</b></td>"+
            "<td><select name=\"visp\"> "+
            (d[edit]["visp"]?"<option value=0>No <option selected value=1> Yes":"<option selected value=0>No <option value=1> Yes")+
            " </select> </td>";

           // Build cloakers cloaked
            str +=
            "<td align=right><b>Cloakers built cloaked:</b></td>"+
            "<td><select name=\"init_cloak\"> "+
            (d[edit]["init_cloak"]?"<option value=0>No <option selected value=1> Yes":"<option selected value=0>No <option value=1> Yes")+
            " </select> </td>";

           // visibility
 //        "<tr><td> 2.9 Visibility: </td><td>" + d[s_name]["vis"] + "</td></tr>" +
           str +=
            "<td align=right><b>Build Visibility:</b></td>"+
            "<td><select name=\"vis\"> "+
            (d[edit]["vis"]?"<option value=0>No<option selected value=1>Yes":"<option selected value=0>No<option value=1>Yes")+
            " </select> </td></tr>";



          // end 3.0 stuff

          str +=
          "<tr><td align=right><b>Layout:</b></td>"+
          "<td><select name=\"layout\">"+sel_layout+"</select></td>"+
          "<td align=right><b>Blind until Start:</b></td>"+
          "<td><select name=\"blind\">"+
          (d[edit]["blind"]?"<option value=0>No<option selected value=1>Yes":"<option selected value=0>No<option value=1>Yes")+
          "</select></td></tr>"+

          "<tr><td align=right><b>Player Max:</b></td>"+
          "<td><input type=text size=20 maxlength=20 name=\"p_max\" value=\""+d[edit]["p_max"]+"\"></td></tr>"+
//          "<tr><td align=right><b>Systems per Player:</b></td>"+
//          "<td><input type=text size=20 maxlength=20 name=\"s_add\" value=\""+d[edit]["s_add"]+"\"></td></tr>"+
          "<tr><td align=right><b>Min. Systems per Player:</b></td>"+
          "<td><input type=text size=20 maxlength=20 name=\"s_min\" value=\""+d[edit]["s_min"]+"\"></td>"+
          "<td align=right><b>Max. Systems per Player:</b></td>"+
          "<td><input type=text size=20 maxlength=20 name=\"s_max\" value=\""+d[edit]["s_max"]+"\"></td></tr>"+
//          "<tr><td align=right><b>Systems per Player:</b></td>"+"<td>"+d[edit]["s_add"]+"</td></tr>"+

          "<tr><td align=right><b>Minimum Agriculture (0==traditional):</b></td>"+
          "<td><input type=text size=20 maxlength=20 name=\"min_ag\" value=\""+d[edit]["min_ag"]+"\"></td>"+
          "<td align=right><b>Maximum Agriculture Ratio:</b></td>"+
          "<td><input type=text size=20 maxlength=20 name=\"max_ag\" value=\""+d[edit]["max_ag"]+"\"></td></tr>";

          // Tech levels
          str +=
          "<tr><td align=right><b>Tech Multiple:</b></td>"+
          "<td><input type=text size=20 maxlength=20 name=\"t_mult\" value=\""+d[edit]["t_mult"]+"\"></td>";

          // Initial Tech
          str +=
          "<td align=right><b>Initial Tech Level:</b></td>"+
          "<td><input type=text size=10 maxlength=10 name=\"init_tech\" value="+d[edit]["init_tech"]+"></td></tr>";

          str +=
          "<tr><td align=right><b>Minimum Wins:</b></td>"+
          "<td><input type=text size=20 maxlength=20 name=\"wins\" value=\""+d[edit]["wins"]+"\"></td>"+
          "<td align=right><b>Maximum Wins:</b></td>"+
          "<td><input type=text size=20 maxlength=20 name=\"winmax\" value=\""+d[edit]["winmax"]+"\"></td></tr>"+
          "<tr><td align=right></td><td></td>"+
          "<td align=right><b>(-1 -> No Maximum)</b></td><td></td></tr>"+

          "<tr></tr><tr><td></td><td colspan=2 align=right>"+
          "<input type=submit name=\"update\" value=\"Update\">"+
          "<input type=submit name=\"respawn\" value=\"Respawn\">"+
          "<input type=submit name=\"confirm\" value=\"Cancel\"></td></tr>"+
          "</table></center>"+
          footer(name);

  return (["data":str,
          "type":"text/html"]);

}


mapping edit_series3(object id)
{
  string name=id["variables"]["name"];
  string c_pass=id["variables"]["c_pass"];
  string index;
  string *a;
  string s_edit;

  if(!admin_id_check(name, c_pass, id))
    return login_failed("Identity checking failed during login. Please try again.");  // do  not advertise admin stuff!!

  if(id["variables"]["confirm"]=="Cancel")
    return admin_login(name, c_pass, "Series edit cancelled.");

  foreach(indices(id["variables"]), index) {
    a=index/":";
    if(a[0] != "ed") continue;
    s_edit=a[1];
    break;
  }

  // patch to respawn faulty game/series 12/2/98
  if(id["variables"]["respawn"]=="Respawn") {
    spawn_game(s_edit);
    return admin_login(name, c_pass, "Series respawned.");
  }

  if(search(s_edit, "=")!=-1)
    return edit_series2(id, "The series name contained an =.");

  int u_time=(int) id["variables"]["u_time"];
  if(u_time==0)
    return edit_series2(id, "An invalid update time was entered.");

  if(id["variables"]["u_meas"]=="Hours") u_time*=3600;
  else u_time*=60;

  // 2.9 put in the time limit and overtime
  int time_limit=(int) id["variables"]["time_limit"];
  if(time_limit==0)
    return edit_series2(id, "An invalid time limit was entered.");

  switch(id["variables"]["u_tmeas"])
    {
    case "Hours":
      time_limit*=3600;
      break;
    case "Days":
      time_limit*=86400;
      break;
    default:
      time_limit*=60;
    }
  int over_time=(int) id["variables"]["over_time"];
  if(over_time==0)
    return edit_series2(id, "An invalid overtime update time was entered.");

  if(id["variables"]["u_omeas"]=="Hours") over_time*=3600;
  else over_time*=60;
  // 2.9 end of time limit and over time

  int weekend=(int) (id["variables"]["weekend"] == "on");
  int from=(int) id["variables"]["from"];
  int to=(int) id["variables"]["to"];

  int u_delay=(int) id["variables"]["u_delay"];
  switch(id["variables"]["ud_meas"])
    {
    case "Hours":
      u_delay*=3600;
      break;
    case "Days":
      u_delay*=86400;
      break;
    default:
      u_delay*=60;
    }

  int p_max=(int) id["variables"]["p_max"];
  if(p_max==0)
    return edit_series2(id, "An invalid player maximum was entered.");

  if(p_max>2 && (int) id["variables"]["bridier"])
    return edit_series2(id, "Bridier Ranking may be set only on 2-player games at present.");

// ##@ 23/2/99
//  int s_add=(int) id["variables"]["s_add"];
//  if(s_add<3)
//    return start_series(name, c_pass,
//                        "Need at least three (3) systems per player.");

  int s_add, s_min, s_max;
  if( id["variables"]["s_add"] )                           // hardly likely - takes a very big browser cache :)
    s_add=(int) id["variables"]["s_add"];
  else
  {
    s_min=(int) id["variables"]["s_min"];
    s_max=(int) id["variables"]["s_max"];
    s_add = s_max;                                         //for update of old type series if needed
  }

  if(s_min<3 || s_max<3)
    return edit_series2(id, "Systems per player must be more than two.");
  if(s_max<s_min)
    return edit_series2(id, "Max. systems per player was less than Min.");

  //int min_rsrc=(int) id["variables"]["min_rsrc"];
  //int max_rsrc=(int) id["variables"]["max_rsrc"];
  //if ( min_rsrc > max_rsrc )
  //  return edit_series2(id, "Max. resource average was less than Min.");
// ##@ 23/2/99

  // 2.9 added minimum agriculture
  int min_ag=(int) id["variables"]["min_ag"];
  if (min_ag<0) min_ag = 0;

  // 2.9 added maximum agriculture
  float max_ag=(float) id["variables"]["max_ag"];
  if (max_ag<1) max_ag = 1.0;

  float t_mult=(float) id["variables"]["t_mult"];
  if(t_mult==0)
    return edit_series2(id, "An invalid tech multiple was entered.");

  int wins=(int) id["variables"]["wins"];
  int winmax=(int) id["variables"]["winmax"];
  if ( wins < 0 ) wins = 0;
  if(winmax<wins && winmax != -1)
    return edit_series2(id, "Max wins was less than min wins.");

  string simage = (string) id["variables"]["seriesimage"];

  // Begin 3.0 stuff

  // need to do r_tech and u_tech arrays
    d[s_edit]["r_tech"] = "";
    d[s_edit]["u_tech"] = "";
    d[s_edit]["d_tech"] = "";

	string ixc;
	foreach (all_tech, ixc) {
		switch(id["variables"][ixc]) {
            case "dev":
              if (d[s_edit]["d_tech"] != "") {
                  d[s_edit]["d_tech"] += " ";
              } // if
              d[s_edit]["d_tech"] += ixc;
              break;
            case "allowed":
                d[s_edit]["u_tech"] += ixc + " ";
				break;
			case "na":
				break;
			case "restricted":
                d[s_edit]["r_tech"] += ixc + " ";
				break;
		} // switch
	} // foreach


    d[s_edit]["ar_tech"] =(int) id["variables"]["ar_tech"];
    d[s_edit]["tr_tech"] =(int) id["variables"]["tr_tech"];
    d[s_edit]["vis"] = (int) id["variables"]["vis"];
    d[s_edit]["visp"] = (int) id["variables"]["visp"];
    d[s_edit]["init_cloak"] = (int) id["variables"]["init_cloak"];
    d[s_edit]["spawn_first"] = (int) id["variables"]["spawn_first"];
    d[s_edit]["bridier"] = (int) id["variables"]["bridier"];
    d[s_edit]["stargate"] = (float) id["variables"]["stargate"];
    d[s_edit]["jumpgate"] = (float) id["variables"]["jumpgate"];
    d[s_edit]["eng_loss"] = (float) id["variables"]["eng_loss"];
    d[s_edit]["jump_loss"] = (float) id["variables"]["jump_loss"];
    d[s_edit]["morph_loss"] = (float) id["variables"]["morph_loss"];
    d[s_edit]["car_loss"] = (float) id["variables"]["car_loss"];
    d[s_edit]["max"] = (int) id["variables"]["max"];
    d[s_edit]["max_ship"] = (int) id["variables"]["max_ship"];
    d[s_edit]["init_tech"] = (float) id["variables"]["init_tech"];
    d[s_edit]["max_allies"] = (int) id["variables"]["max_allies"];
    d[s_edit]["surrdraw"] = (int) id["variables"]["surrdraw"];
    d[s_edit]["blind"] = (int) id["variables"]["blind"];

    d[s_edit]["morpher_build"] = (int) id["variables"]["morpher_build"];
    d[s_edit]["morpher_maint"] = (int) id["variables"]["morpher_maint"];
    d[s_edit]["builder_build"] = (int) id["variables"]["builder_build"];
    d[s_edit]["builder_maint"] = (int) id["variables"]["builder_maint"];

    d[s_edit]["jumpgate_build"] = (int) id["variables"]["jumpgate_build"];
    d[s_edit]["jumpgate_maint"] = (int) id["variables"]["jumpgate_maint"];

    d[s_edit]["carrier_build"] = (int) id["variables"]["carrier_build"];
    d[s_edit]["carrier_maint"] = (int) id["variables"]["carrier_maint"];

    d[s_edit]["avg_ag"] = (int) id["variables"]["avg_ag"];
    d[s_edit]["avg_min"] = (int) id["variables"]["avg_min"];
    d[s_edit]["avg_fuel"] = (int) id["variables"]["avg_fuel"];

    d[s_edit]["range_ag"] = (int) id["variables"]["range_ag"];
    d[s_edit]["range_min"] = (int) id["variables"]["range_min"];
    d[s_edit]["range_fuel"] = (int) id["variables"]["range_fuel"];

    d[s_edit]["home_ag"] = (int) id["variables"]["home_ag"];
    d[s_edit]["home_min"] = (int) id["variables"]["home_min"];
    d[s_edit]["home_fuel"] = (int) id["variables"]["home_fuel"];
// homeworld randomising not implemented yet
//    d[s_edit]->min_hrsrc = (int) id["variables"]["min_hrsrc"];
//    d[s_edit]->max_hrsrc = (int) id["variables"]["max_hrsrc"];

    d[s_edit]["pla_build"] = (int) id["variables"]["pla_build"];
    d[s_edit]["min_build"] = (int) id["variables"]["min_build"];

    d[s_edit]["desc"] = (string) id["variables"]["desc"];

    d[s_edit]["full_desc"] = (string) id["variables"]["full_desc"];
    if ( d[s_edit]["full_desc"] == "" )
      d[s_edit]["full_desc"] = d[s_edit]["desc"];

    d[s_edit]["count"] = (int) id["variables"]["g_count"];

/*
    d[s_edit]["restricted"] = (int) id["variables"]["s_restrict"];
    d[s_edit]["s_pass"] =  (string) id["variables"]["series_pass"];
*/
  // end 3.0 stuff

  d[s_edit]["color"] = (string) id["variables"]["series_color"];

  d[s_edit]["u_time"]=u_time;
  d[s_edit]["time_limit"]=time_limit;
  d[s_edit]["over_time"]=over_time;
  d[s_edit]["weekend"]=weekend;
  d[s_edit]["from"]=from;
  d[s_edit]["to"]=to;
  d[s_edit]["u_delay"]=u_delay;
  d[s_edit]["p_max"]=p_max;
  if( d[s_edit]["s_add"] )
    d[s_edit]["s_add"]=s_add;
//  else {    This was preventing s_min and s_max from being written into an old series that already had s_add
  d[s_edit]["s_min"]=s_min;
  d[s_edit]["s_max"]=s_max;
//  }

  d[s_edit]["min_ag"]=min_ag;
  d[s_edit]["max_ag"]=max_ag;
  d[s_edit]["t_mult"]=t_mult;
  d[s_edit]["wins"]=wins;
  d[s_edit]["winmax"]=winmax;
  d[s_edit]["simage"]=simage;
  //d[s_edit]["g_cnt"]=(int) 2;


  d[s_edit]["max_allies"] = (int) id["variables"]["max_allies"];
  if(id->variables->ally == "No") {
    d[s_edit]["blood"]=1;
    d[s_edit]["max_allies"] = 0;
  }
  else if(id->variables->ally == "Trade")
    d[s_edit]["blood"]=2;
  else
    d[s_edit]["blood"]=0;

  if(id->variables->layout == "Traditional")
    d[s_edit]["layout"]=1;
  else
    d[s_edit]["layout"]=2;

  return admin_login(name, c_pass, s_edit+" series successfully updated.");
}


/* ---------- Admin Series List display ---------------- */
mapping list_series(string name, string c_pass)
{
  string series, u_time, over_time, time_limit, index, weekend, gamestate;
  string s_head, sl_list="";
  string up_text;
  string systems, resources, access, ally, playtime;


  foreach(indices(d), series) {
    if(d[series]["u_time"] % 3600 == 0)
      u_time = (d[series]["u_time"]/3600)+" hours";
    else
      u_time = (d[series]["u_time"] / 60)+" minutes";

    // 2.9 set the overtime and time limit
    if(d[series]["over_time"] % 3600 == 0)
      over_time = (d[series]["over_time"]/3600)+" hours";
    else
      over_time = (d[series]["over_time"] / 60)+" minutes";

    if (over_time == u_time)
      up_text = u_time;
    else
      up_text = u_time+" for the first "+time_limit+" then every "+over_time;

    // systems info
    if( d[series]["s_min"] )
      systems = (d[series]["s_min"] == d[series]["s_max"] ? d[series]["s_min"] :
                "between " + d[series]["s_min"] + " and " + d[series]["s_max"]);
    else
      systems = d[series]["s_add"];

    // resources
    resources = "average Min/Fuel/Ag resources "+d[series]["avg_min"];
    if (d[series]["range_min"] > 0) {
      resources += "-"+(d[series]["avg_min"]+d[series]["range_min"]);
    }
    resources += "/"+d[series]["avg_fuel"];
    if (d[series]["range_fuel"] > 0) {
      resources += "-"+(d[series]["avg_fuel"]+d[series]["range_fuel"]);
    }
    resources += "/"+d[series]["avg_ag"];
    if (d[series]["range_ag"] > 0) {
      resources += "-"+(d[series]["avg_ag"]+d[series]["range_ag"]);
    }

    // game entry
    if( d[series]["wins"] == 0 && d[series]["winmax"] < 0 )
      access = "Anyone";
    else if( d[series]["wins"] == d[series]["winmax"] )
      access = d[series]["wins"] +" wins";
    else if( d[series]["winmax"] < 0 )
      access = d[series]["wins"] +" or more wins";
    else
      access = "Between "+d[series]["wins"]+" and "+ d[series]["winmax"] +" wins";

    // alliances
    if (d[series]->blood == 0) {
      ally = "Truce/Trade and ";
      if (d[series]["max_allies"] > 1)
        ally += d[series]["max_allies"] + " alliances allowed.";
      else
        ally += "1 alliance allowed.";
    }
    else if (d[series]->blood == 1)
      ally = "Truce, trade or alliances not allowed.";
    else
      ally = "Truce/Trade only, no alliances allowed.";

    // time limits
    if(d[series]["time_limit"] % 3600 == 0)
      time_limit = (d[series]["time_limit"]/3600)+" hours";
    else
      time_limit = (d[series]["time_limit"] / 60)+" minutes";

    // game availability                              ...  is this really needed????
    if ( (d[series]["to"] - d[series]["from"]) >= 23 )
      playtime = "always available";
    else
      playtime = "available from "+d[series]["from"]+" until "+d[series]["to"]+" hours";

    if(d[series]["weekend"])
      weekend="";
    else
      weekend=", no weekend updates";

//    gamestate = d[series][(string) d[series]["g_cnt"]]["spawn"];

    // 2.9C series header
    s_head=("<tr><td align=center><b><font color="+d[series]["color"]+">"+series+" series</font></b></td>"+
            "<td><img src="+d[series]["simage"]+"></td>"+
            "<td colspan=5>Updates every "+up_text+ ", "+playtime+weekend+"<br>"+
//            "Active from "+d[series]["from"]+" until "+d[series]["to"]+" hours"+ weekend+"<br>"+
            d[series]["p_max"]+" players with "+systems + " systems per player, <br>"+
//            d[series]["s_add"]+" systems per player<br>"+
//            resources +" system resources<br>"+
            resources+"<br>"+
            d[series]["min_ag"] + " minimum starting ag with a " + d[series]["max_ag"] + " max ag ratio<br>" +
            d[series]["t_mult"]+" tech ratio<br>"+
            ally+", "+
//            (d[series]->blood == 1 ? "Truce, trade or alliance not allowed.":
//             d[series]->blood == 2 ? "No alliances allowed. " : "")+
            (d[series]->layout == 1 ? "Random": "Mirrored")+" layout<br>"+
//            "Between "+d[series]["wins"]+" and "+ d[series]["winmax"]+" wins can join. "+
            access+" can join. "+
            "<br>Current game count : "+d[series]["count"] +
            "</td></tr>"+
            "<tr><td align=center><input type=submit name=\"ed:"+series+"\" value=\" Edit \"> "+
            "<input type=submit name=\"kill:"+series+"\" value=\" Kill \"> "+
 //           (gamestate ? "<input type=submit name=\"resume:"+series+"\" value=\"Resume\">" :
 //            "<input type=submit name=\"halt:"+series+"\" value=\" Halt \">")+
            "</td></tr><tr></tr>");
    sl_list+=s_head;

  } // 2.9C foreach series

  return (["data":
          header("Series List", name)+
          "<input type=hidden name=\"name\" value=\""+name+"\">"+
          "<input type=hidden name=\"c_pass\" value=\""+c_pass+"\">"+
          "<input type=hidden name=\"action\" value=\"series_list\">"+
          pageheader("Admin Series List", name)+
          "<p><font color=\"#000000\"><input type=submit name=\"sl:start\" value=\" New Series \">"+
          "  <input type=submit name=\"sl:cancel\" value=\"  Exit  \"></font><br>"+
//          "<b>Local time and date: "+ ctime(time())+"</b></p>"+
          "<p><img src=\""+theme_dir(name)+"/spacerule.gif\" height=10  width=100%></p>"+
          (sl_list == "" ?
           "<center><b>Sorry, there are no games in progress at this time.</b></center>":
           "<table>"+sl_list+"</table>")+
          footer(name),
          "type":"text/html"]);
}

/* ---------- Admin Series List options ---------------- */
mapping list_series2(object id)
{
  string name=id["variables"]["name"];
  string c_pass=id["variables"]["c_pass"];

  if(!admin_id_check(name, c_pass, id))
    return login_failed("Identity checking failed during login. Please try again.");  // do  not advertise admin stuff!!

  string index;
  string *a, cmd;
  string sedit, action="none", series="", gamestate;

  foreach(indices(id["variables"]), index) {
    a=index/":";
    cmd = a[0];

    switch (cmd) {
    case "sl" :                       // start new / exit
      action = a[1];
      break;
    case "ed" :                       // edit
      action = "edit";
      break;
    case "kill" :
      action = "kill";
      series = a[1];
      break;
/*
     case "halt" :
      action = "flow";
      series = a[1];
      break;
    case "resume" :
      action = "flow";
      series = a[1];
      break;
*/
    default:
      continue;                       // if not command
    }
    break;
  }

  if(action=="cancel")
    return admin_login(name, c_pass, "");

  if(action == "start")
    return start_series(name, c_pass, "");

  if ( action == "edit")
    return edit_series2(id, "");

  if (action == "kill") {
    d-=([series:""]);
    return admin_login(name, c_pass, "Series "+series+" killed.");
  }

/*
   if ( action == "flow") {
    gamestate = d[series][(string) d[series]["g_cnt"]]["spawn"];
    d[series][(string) d[series]["g_cnt"]]["spawn"] = !gamestate;

    string word = gamestate?"resumed":"halted";

    return admin_login(name, c_pass, "Series "+series+" "+word+".");
  }
*/

  // if all else fails!!
  return admin_login(name, c_pass, "Unknown action "+cmd+" ?");
}

/* ---------- Admin Top Ten lists display ---------------- */
mapping evtop_ten(string name, string c_pass, string type)
{
  string label;

  switch(type)
    {
    case "win":
      label="Winners";
      break;
    case "avg":
      label="Averages";
      break;
    case "wnr":
      label="Nuke/Game Ratios";
      break;
    case "brd":
      label="Bridier Ranks<br>All Players: Index &lt 495";
      break;
    case "bre":
      label="Bridier Ranks<br>Established Players: Index = 100";
      break;
    }

  return (["data":
          header("Admin Empire Viewer", name)+
          "<input type=hidden name=\"name\" value=\""+name+"\">"+
          "<input type=hidden name=\"c_pass\" value=\""+c_pass+"\">"+
          "<input type=hidden name=\"action\" value=\"empire_view\">"+
          pageheader("Top Ten "+label, name)+"<p>"+
          "<font color=\"#000000\"><input type=submit name=\"ev:back\" value=\" Cancel \"></font></p>"+
          "<p><img src=\""+theme_dir(name)+"/spacerule.gif\" height=10  width=100%></p>"+
          "<center><table>"+
          "<tr valign=top><th align=left>Empire</th><th align=left>Real Name</th><th align=left>E-Mail</th>"+
          ((type=="brd"||type=="bre")?"<th>Rank</th><th>Index</th>":
          "<th>Wins</th><th>Nukes</th><th>Been Nuked</th><th>Ruins</th><th>Score</th>")+"</tr>"+
          top[type]+
          "</table></center>"+
          footer(name),
          "type":"text/html"]);
}

/* ---------- Admin Player view display ---------------- */
mapping empire_viewer(string name, string c_pass, string message)
{
  return (["data":
          header("Admin Empire Viewer", name)+
          "<input type=hidden name=\"name\" value=\""+name+"\">"+
          "<input type=hidden name=\"c_pass\" value=\""+c_pass+"\">"+
          "<input type=hidden name=\"action\" value=\"empire_view\">"+
          pageheader("Admin Player Viewer", name)+
          "<font color=\"#000000\"><input type=submit name=\"ev:cancel\" value=\" Cancel \"></font><p>"+
          "<b>"+message+"</b>"+
          "<p><img src=\""+theme_dir(name)+"/spacerule.gif\" height=10  width=100%><p>"+
          "<center>"+
          "<b><font color="+LOG_COLOR+">There are "+sizeof(indices(p))+" registered players.</font></b><p>"+
          "<table>"+(enable_bridier?
          "<tr><td align=right><b>Top Ten Bridier Ranks (established players)</b></td>"+
          "<td><input type=submit name=\"ev:bre\" value=\"Ok\"></td></tr>"+
          "<tr><td align=right><b>Top Ten Bridier Ranks (all players)</b></td>"+
          "<td><input type=submit name=\"ev:brd\" value=\"Ok\"></td></tr>":"")+
          "<tr><td align=right><b>Top Ten Winners</b></td>"  // 2.9 admin put into table for clarity
          "<td><input type=submit name=\"ev:top\" value=\"Ok\"></td></tr>"
          "<tr><td align=right><b>Top Ten Averages</b></td>"
          "<td><input type=submit name=\"ev:avg\" value=\"Ok\"></td></tr>"
          "<tr><td align=right><b>Top Ten Nuke/Game Ratios</b></td>"
          "<td><input type=submit name=\"ev:wnr\" value=\"Ok\"><td></tr></table>"+
          "<table><tr><td align=right><b>Name is (regexp allowed):</b></td>"+
          "<td><input type=text name=\"sname\" size=20 maxlength=20></td></tr>"+
          "<tr><td align=right colspan=2>"+
          "<input type=submit name=\"ev:search\" value=\"Search\"></td></tr></table>"+
          "<table><tr><td><input type=submit name=\"ev:[Aa]\" value=\" A \"></td>\n"+  // search buttons
          "<td><input type=submit name=\"ev:[Bb]\" value=\" B \"></td>\n"+
          "<td><input type=submit name=\"ev:[Cc]\" value=\" C \"></td>\n"+
          "<td><input type=submit name=\"ev:[Dd]\" value=\" D \"></td>\n"+
          "<td><input type=submit name=\"ev:[Ee]\" value=\" E \"></td>\n"+
          "<td><input type=submit name=\"ev:[Ff]\" value=\" F \"></td>\n"+
          "<td><input type=submit name=\"ev:[Gg]\" value=\" G \"></td>\n"+
          "<td><input type=submit name=\"ev:[Hh]\" value=\" H \"></td>\n"+
          "<td><input type=submit name=\"ev:[Ii]\" value=\"  I  \"></td>\n"+
          "<td><input type=submit name=\"ev:[Jj]\" value=\"  J  \"></td>\n"+
          "<td><input type=submit name=\"ev:[Kk]\" value=\" K \"></td>\n"+
          "<td><input type=submit name=\"ev:[Ll]\" value=\" L \"></td>\n"+
          "<td><input type=submit name=\"ev:[Mm]\" value=\" M \"></td></tr>\n"+
          "<tr><td><input type=submit name=\"ev:[Nn]\" value=\" N \"></td>\n"+
          "<td><input type=submit name=\"ev:[Oo]\" value=\" O \"></td>\n"+
          "<td><input type=submit name=\"ev:[Pp]\" value=\" P \"></td>\n"+
          "<td><input type=submit name=\"ev:[Qq]\" value=\" Q \"></td>\n"+
          "<td><input type=submit name=\"ev:[Rr]\" value=\" R \"></td>\n"+
          "<td><input type=submit name=\"ev:[Ss]\" value=\" S \"></td>\n"+
          "<td><input type=submit name=\"ev:[Tt]\" value=\" T \"></td>\n"+
          "<td><input type=submit name=\"ev:[Uu]\" value=\" U \"></td>\n"+
          "<td><input type=submit name=\"ev:[Vv]\" value=\" V \"></td>\n"+
          "<td><input type=submit name=\"ev:[Ww]\" value=\" W \"></td>\n"+
          "<td><input type=submit name=\"ev:[Xx]\" value=\" X \"></td>\n"+
          "<td><input type=submit name=\"ev:[Yy]\" value=\" Y \"></td>\n"+
          "<td><input type=submit name=\"ev:[Zz]\" value=\" Z  \"></td></tr></table>\n"+
          "</center>"+footer(name),
          "type":"text/html"]);
}

/* ---------- Admin Player selection display ---------------- */
string empire_view_data(string empire)
{
  int pflags = p[empire]["flags"];

  return ("<p><font color=\""+MISS_COLOR+"\" size=+2>"+empire+" Profile</font><table>"+
     "<input type=hidden name=\"sname\" value=\""+empire+"\">"+
     "<tr><td align=right><b>Real Name:</b></td><td colspan=3>"+p[empire]["real_name"]+"</td></tr>"+
     "<tr><td align=right><b>Password:</b></td><td colspan=3>"+p[empire]["pass"]+"</td></tr>"+
/*     "<tr><td align=right><b>Current Team:</b></td><td>"+p[empire]["sponsor"]+"</td></tr>"+
     "<tr><td align=right><b>Seeking new team:</b></td><td>"+(p[empire]["seeking"]?"Yes":"No")+"</td></tr>"+
     "<tr><td align=right><b>Administrator :</b></td><td>"+
       (p[empire]["grp_admin"]?"Yes</td><td>"+p[empire]["pgroup"]:"No")+"</td></tr>"+
*/
     "<tr><td align=right><b>E-Mail:</b></td><td colspan=3>"+p[empire]["e-mail"]+(p[empire]["hide-mail"]?" (Hidden)":"")+"</td></tr>"+
     "<tr><td align=right valign=center><b>Homepage URL :</b></td><td>"+p[empire]["home_url"]+"</td></tr>"+
     "<tr><td align=right valign=center><b>Theme URL :</b></td><td>"+p[empire]["theme_url"]+"</td></tr>"+
     "<tr><td align=right><b>Wins:</b></td><td>"+p[empire]["wins"]+"</td>"+
       "<td align=right><b>Nukes:</b></td><td>"+p[empire]["kills"]+"</td></tr>"+
     "<tr><td align=right><b>Been Nuked:</b></td><td>"+p[empire]["killed"]+"</td>"+
       "<td align=right><b>Ruins:</b></td><td>"+p[empire]["ruins"]+"</td></tr>"+
     "<tr><td align=right><b>Max Econ:</b></td><td>"+p[empire]["max_e_pow"]+"</td>"+
       "<td align=right><b>Max Mil:</b></td><td>"+p[empire]["max_m_pow"]+"</td></tr>"+(enable_bridier?
     "<tr><td align=right><b>Bridier Rank:</b></td><td>"+p[empire]["bridier_rank"]+"</td>"+
       "<td align=right><b>Bridier Index:</b></td><td>"+p[empire]["bridier_index"]+"</td></tr>":"")+
     "<tr><td align=right><b>Last Login:</b></td><td colspan=3>"+nctime(p[empire]["l_log"])+"</td></tr>"+
     "<tr><td align=right valign=center><b>Options :</b></td>"+
       "<td>"+(pflags & flags_bgimage?"Background Image":"")+
       (pflags & flags_autoupd?"<br>Auto Update":"")+
       (pflags & flags_eratios?"<br>Enhanced Ratios Line":"")+
       (pflags & flags_gbutton?"<br>Graphical buttons":"")+
       (pflags & flags_usejavascript?"<br>Update Countdown":"")+
       (pflags & flags_nextbr?"<br>Show Next BR":"")+
       (pflags & flags_sepdata?"<br>Separate Data Color":"")+
       (pflags & flags_webtv?"<br>WebTV Mode":"")+
       (pflags & flags_sensitivemap?"<br>Sensitive Map":"")+"</tr>"+
     "<tr><td align=center colspan=4><img src=\""+html_dir+"/aliens/"+p[empire]["alien"]+"\"></td></tr>"+
     "<tr><td align=right><b>Last Login:</b></td><td>"+nctime(p[empire]["l_log"])+
       " from "+p[empire]["last_ip"]+"</td></tr>"+
     "<tr><td colspan=4><pre>"+(p[empire]["comments"]||"")+"</pre></td></tr>"+
     "<tr><td><b>Victory Sneer:</b></td><td colspan=3><pre>"+(p[empire]["sneer"]||"")+"</pre></td></tr>"+
     "</table></p>");
}

mapping empire_viewer2(object id)
{
  string name=id["variables"]["name"];
  string c_pass=id["variables"]["c_pass"];
  string *a;
  string index;
  string empire=id["variables"]["sname"];
  array emps;
  string r="<b>There are no matching empires.</b>";
  int SearchCount=-1;
  int pflags;

  foreach(indices(id["variables"]), index) {
    a=index/":";
    if(a[0]=="ev")
      break;
  }

  if(sizeof(a) < 2)
    a = ({"s", "search"}); // If someone press return in the "search" form.

  switch(a[1])
    {
    case "back":
      return empire_viewer(name, c_pass, "");
      //   case "stat":
      //    return stat_viewer(name, c_pass, "");
      //   case "game":
      //    return game_list(name, c_pass, "");
    case "bre":
      return evtop_ten(name, c_pass, "bre");
    case "brd":
      return evtop_ten(name, c_pass, "brd");
    case "top":
      return evtop_ten(name, c_pass, "win");
    case "avg":
      return evtop_ten(name, c_pass, "avg");
    case "wnr":
      return evtop_ten(name, c_pass, "wnr");
    case "cancel":
      return admin_login(name, c_pass, "Empire viewer cancelled.");
    default:

      // sc2.9admin  search buttons
      if ( empire == "") {
        empire = a[1];
        SearchCount = 0;
      }

      if(mappingp(p[empire])) {
        r=empire_view_data(empire);
      } else {
        emps = regexp_match(indices(p), "^"+empire);
        if(sizeof(emps) > 200) {
          r = sprintf("<h3>Too many matching empires (%d).</h3>", sizeof(emps));
        } else if(sizeof(emps) > 1) {
          r = sprintf("<h3>There are %d matching empires.</h3>"
                      "<h4>Click on the name of the empire you want to know "
                      "more about.</h4><table><tr>", sizeof(emps));
          foreach(emps, empire) {
            r += sprintf("<td align=center><input type=submit name=sname value=\"%s\"></td>\n", empire);
            SearchCount += 1;
            if ( SearchCount > 3 ) {
              SearchCount = 0;
              r+= "</tr><tr>";
            }
          }
          r += "</tr></table>";
        } else if(sizeof(emps) == 1) {
          empire = emps[0];
          if(mappingp(p[empire])) {
            r=empire_view_data(empire);
          }
        }
      }

      return (["data":
              header("Empire Stat Viewer", name)+
              "<input type=hidden name=\"name\" value=\""+name+"\">"+
              "<input type=hidden name=\"c_pass\" value=\""+c_pass+"\">"+
              (SearchCount > -1 ?
               "<input type=hidden name=\"action\" value=\"empire_view\">" :
               "<input type=hidden name=\"action\" value=\"empire_edit\">")+
//              "<center><h1>Admin Empire Stat Viewer</h1><p>"+
               pageheader("Admin Empire Stats/Profile Viewer", name)+"<font color=\"#000000\">"+
              (SearchCount > -1 ? "" :
               "<input type=submit name=\"ev:edit\" value=\" Edit \"> "
               "<input type=submit name=\"ev:kill\" value=\" Kill \"> ") +
//               "<input type=submit name=\"ev:view\" value=\" View \"> ")+
              "<input type=submit name=\"ev:cancel\" value=\" Cancel \"></font></p>"+
              "</center>"+
              "<p><img src=\""+theme_dir(name)+"/spacerule.gif\" height=10  width=100%></p>"+
              "<center>"+r+"</center>"+
              footer(name),
              "type":"text/html"]);
    }
}






/* ---------- Admin Player edit display ---------------- */
mapping empire_edit(object id)
{
  string name=id["variables"]["name"];
  string c_pass=id["variables"]["c_pass"];
  string empire=id["variables"]["sname"];

  if(!admin_id_check(name, c_pass, id))
    return login_failed("Identity checking failed during login. Please try again.");  // do  not advertise admin stuff!!

  string index;
  string *a;

  foreach(indices(id["variables"]), index) {
    a=index/":";
    if(a[0]=="ev") break;
  }

  if(a[1]=="cancel")
    return empire_viewer(name, c_pass, "");

  //d[a[1]]-=([a[2]:""]);   kill game

  if(mappingp(p[empire])) {
    int pflags = p[empire]["flags"];
    return (["data":
            header("Empire Editor", name)+
            "<input type=hidden name=\"name\" value=\""+name+"\">"+
            "<input type=hidden name=\"c_pass\" value=\""+c_pass+"\">"+
            "<input type=hidden name=\"action\" value=\"empire_update\">"+
            "<input type=hidden name=\"sname\" value=\""+empire+"\">"+
            pageheader("Admin Empire Editor",name)+
            "<font color=\"#000000\"><input type=submit name=\"kill\" value=\" Kill \"> "+
            "<input type=submit name=\"update\" value=\" Update \"> "+
            "<input type=submit name=\"clrcom\" value=\" Clear Comments \"> "+
            "<input type=submit name=\"clrsneer\" value=\" Clear Sneer \"> "+
            "<input type=submit name=\"cancel\" value=\" Cancel \"></font></p></center>"+
            "<p><img src=\""+theme_dir(name)+"/spacerule.gif\" height=10  width=100%></p>"+
            "<center><p><font color=\""+MISS_COLOR+"\" size=+2>"+empire+" Profile</font><table>"+
            "<tr><td align=right><b>Real Name:</b></td><td colspan=3>"+
              "<input type=text name=\"real_name\" size=20 value=\""+p[empire]["real_name"]+"\"></td></tr>"+
            "<tr><td align=right><b>Password:</b></td><td colspan=3>"+
              "<input type=text name=\"pword\" size=20 value=\""+p[empire]["pass"]+"\"></td></tr>"+
/*            "<tr><td align=right><b>Current Team:</b></td><td>"+p[empire]["sponsor"]+"</td></tr>"+
            "<tr><td align=right><b>Seek new team:</b></td>"+
              "<td><input type=checkbox "+(p[empire]["seeking"]?" checked":"")+" name=\"seeking_sponsor\"></td>"+
              "<td align=right><b>Leave current team:</b></td>"+
              "<td><input type=checkbox name=\"lose_sponsor\"></td></tr>"+
            "<tr><td align=right><b>Administrator :</b></td>"+
              "<td><input type=checkbox "+(p[empire]["grp_admin"]?" checked":"")+" name=\"grp_admin\"></td>"+
              "<td><input type=text name=\"pgroup\" size=30 value=\""+p[empire]["pgroup"]+"\"></td></tr>"+
          "<TD><SELECT name=\"clan\"><OPTION selected value=0>Independant"+
          "<OPTION value=1>Avengers<OPTION value=2>Ice Nomads"+
          "<OPTION value=3>Kaa<OPTION value=4>Red Triad<OPTION value=5>W.A.R."+
          "<OPTION value=6>Five Rings<OPTION value=7>Untouchables<OPTION value=8>Heroes Reborn"+
          "<OPTION value=9>Hell<OPTION value=10>Juggernaut<OPTION value=11>Apocalypse</SELECT></TD></TR>"+
*/
           "<tr><td align=right><b>E-Mail:</b></td><td colspan=3>"+
              "<input type=text name=\"e-mail\" size=20 value=\""+p[empire]["e-mail"]+"\"> "+
              "<b>Hidden :</b> <input type=checkbox name=hide-mail"+(p[empire]["hide-mail"]?" checked":"")+"</td></tr>"+
            "<tr><td align=right valign=center><b>Homepage URL :</b></td>"+
              "<td><input type=string size=40 name=url value="+p[empire]["home_url"]+"></td></tr>"+
            "<tr><td align=right valign=center><b>Theme URL :</b></td>"+
              "<td><input type=string size=40 name=theme_url value="+p[empire]["theme_url"]+"></td></tr>"+
            "<tr><td align=right><b>Wins:</b></td><td>"+
              "<input type=text name=\"wins\" size=6 value=\""+p[empire]["wins"]+"\"></td>"+
            "<td align=right><b>Nukes:</b></td><td>"+
              "<input type=text name=\"kills\" size=6 value=\""+p[empire]["kills"]+"\"></td></tr>"+
            "<tr><td align=right><b>Been Nuked:</b></td><td>"+
              "<input type=text name=\"nukes\" size=6 value=\""+p[empire]["killed"]+"\"></td>"+
            "<td align=right><b>Ruins:</b></td><td>"+
              "<input type=text name=\"ruins\" size=6 value=\""+p[empire]["ruins"]+"\"></td></tr>"+
            "<tr><td align=right><b>Max Econ:</b></td><td>"+
              "<input type=text name=\"max_pow\" size=6 value=\""+p[empire]["max_e_pow"]+"\"></td>"+
            "<td align=right><b>Max Mil:</b></td><td>"+
              "<input type=text name=\"max_mil\" size=6 value=\""+p[empire]["max_m_pow"]+"\"></td></tr>"+(enable_bridier?
            "<tr><td align=right><b>Bridier Rank:</b></td><td>"+
              "<input type=text name=\"bridier_rank\" size=6 value=\""+p[empire]["bridier_rank"]+"\"></td>"+
            "<td align=right><b>Bridier Index:</b></td><td>"+
              "<input type=text name=\"bridier_index\" size=6 value=\""+p[empire]["bridier_index"]+"\"></td></tr>":"")+
            "<tr><td align=right><b>Last Login:</b></td><td colspan=3>"+nctime(p[empire]["l_log"])+"</td></tr>"+
            "<tr><td align=right valign=center><b>Options :</b></td>"+
              "<td><input name=\"flag:1\" type=checkbox "+(pflags & flags_bgimage?" checked":"")+"><nobr>Background Image</nobr>"+
              "<br><input name=\"flag:4\" type=checkbox "+(pflags & flags_eratios?" checked":"")+"><nobr>Enhanced Ratios Line</nobr>"+
              "<br><input name=\"flag:32\" type=checkbox "+(pflags & flags_nextbr?" checked":"")+"><nobr>Show Next BR</nobr>"+
              "<br><input name=\"flag:64\" type=checkbox "+(pflags & flags_sepdata?" checked":"")+"><nobr>Separate Data Color</nobr>"+
              "<br><input name=\"flag:128\" type=checkbox "+(pflags & flags_webtv?" checked":"")+"><nobr>WebTV Mode</nobr></td>"+
            "<td align=right valign=center><b>Javascript Options :</b></td>"+
              "<td><input name=\"flag:2\" type=checkbox "+(pflags & flags_autoupd?" checked":"")+"><nobr>Auto Update</nobr>"+
              "<br><input name=\"flag:8\" type=checkbox "+(pflags & flags_gbutton?" checked":"")+"><nobr>Graphical Buttons</nobr>"+
              "<br><input name=\"flag:16\" type=checkbox "+(pflags & flags_usejavascript?" checked":"")+"><nobr>Update Countdown</nobr>"+
              "<br><input name=\"flag:128\" type=checkbox "+(pflags & flags_sensitivemap?" checked":"")+"><nobr>Sensitive Map</nobr></td></tr>"+
            "<tr><td align=center colspan=2><img src=\""+html_dir+"/aliens/"+p[empire]["alien"]+"\"></td>"+
              "<td colspan=2><input type=text name=\"icon\" size=30 value=\""+p[empire]["alien"]+"\"></td></tr>"+

            "<tr><td align=right><b>Last Login:</b></td><td>"+nctime(p[empire]["l_log"])+
              " from "+p[empire]["last_ip"]+"</td></tr>"+

            "<tr><td colspan=4><pre>"+(p[empire]["comments"]||"")+"</pre></td></tr>"+
            "<tr><td><b>Victory Sneer:</b></td><td colspan=3><pre>"+(p[empire]["sneer"]||"")+"</pre></td></tr>"+
            "</table></center>"+
            footer(name),
            "type":"text/html"]);
  }


  return empire_viewer(name, c_pass, "Empire "+empire+" Unknown command?");
}

/* ---------- Admin Player update ---------------- */
mapping empire_update(object id)
{

  //  mixed *alien;

  string index;
  string name = id["variables"]["name"];
  string c_pass = id["variables"]["c_pass"];
  string empire = id["variables"]["sname"];
  string *a;

  if(!admin_id_check(name, c_pass, id))
    return login_failed("Identity checking failed during login. Please try again.");  // do  not advertise admin stuff!!

  //  if(search((string) name, "=")!=-1)
  //    return login_failed("Your empire name contained a =.");

  //  if(!sizeof(name))
  //    return login_failed("You must enter a name.");

  foreach(indices(id["variables"]), index) {
    if(index == "cancel") return empire_viewer(name, c_pass, "Empire edit cancelled");
    if(index == "clrcom") {
      p[empire]["comments"]="";
      return empire_viewer2(id);
    }
    if(index == "clrsneer") {
      p[empire]["sneer"]="";
      return empire_viewer2(id);
    }


    if(index == "update") {
      //    p[empire]["pass"]=pass;
      p[empire]["real_name"]=sterilizeName(id["variables"]["real_name"], 0);
      p[empire]["e-mail"]=sterilizeString(id["variables"]["e-mail"]);
      if (id["variables"]["hide-mail"]=="on") {
        p[empire]["hide-mail"]=1;
      } else {
        p[empire]["hide-mail"]=0;
      }
      p[empire]["wins"]= (int) id["variables"]["wins"];
      p[empire]["kills"]= (int) id["variables"]["kills"];
      p[empire]["ruins"]= (int) id["variables"]["ruins"];
      p[empire]["killed"]= (int) id["variables"]["nukes"];
      p[empire]["max_e_pow"]= (int) id["variables"]["max_pow"];
      p[empire]["max_m_pow"]= (int) id["variables"]["max_mil"];
      if (enable_bridier) {
        p[empire]["bridier_rank"]= (int) id["variables"]["bridier_rank"];
        p[empire]["bridier_index"]= (int) id["variables"]["bridier_index"];
      }
      p[empire]["alien"]= (string) id["variables"]["icon"];

      // Team & group admin stuff
      p[empire]["seeking"]=id["variables"]["seeking_sponsor"];
      if (id["variables"]["lose_sponsor"]=="on")
        p[empire]["sponsor"] = "Independent";
      p[empire]["grp_admin"] =  id["variables"]["grp_admin"];
      if ( p[empire]["grp_admin"] ) {
       p[empire]["pgroup"] = id["variables"]["pgroup"];
       p[empire]["sponsor"] = p[empire]["pgroup"];
      } else
        p[empire]["pgroup"] = "";

      p[empire]["home_url"] = (id["variables"]["url"]);
      p[empire]["theme_url"] = (id["variables"]["theme_url"]);

/*
    int time_offset=(int) id["variables"]["time_offset"];
    if(time_offset > 0) {
    switch(id["variables"]["time_unit"]) {
      case "Minutes" :
        time_offset *= 60;
      case "Hours":
        time_offset *= 3600;
    }
    p[name]["timezone"] = time_offset;
 */

      int flags = 0;

      foreach(indices(id["variables"]), index) {
        a = index/":";
        if(a[0] != "flag") continue;
        if (id["variables"][index] == "on")
          flags += (int) a[1];
      }
      p[empire]["flags"] = flags;

      return empire_viewer2(id);
    }
  }

  return empire_viewer(name, c_pass, "Unknown command ");
}

/* ---------- Admin Restart ---------------- */
void restart_server()
{
//    dump_data(0, 1);
    dump_data();
}

mapping admin_restart(string name, string c_pass)
{
  call_out(restart_server,5);
  return (["data":
          header("Server Restart", "")+
          pageheader("Server Restart", "")+
          "<p><img src=\""+theme_dir("")+"/spacerule.gif\" height=10  width=100%><p>"+
          "<input type=hidden name=\"name\" value=\""+name+"\">"+
          "<input type=hidden name=\"pass\" value=\""+ad_pass+"\">"+
          "<center><font color="+LOG_COLOR+" size=+2><b>The server is now restarting<br>"+
          "Please wait about a minute and then login again</b></font><br>"+
          "<table><tr><td align=right><b>Login again</b></td>"+
          "<br><td><input type=submit name=\"action\" value=\"Login\"></td></tr>"+
          "</table></center>"+
          footer(""),
          "type":"text/html"]);
}

/* ---------- Admin Database clean -------------- */
string database_clean()
{
    array(string) cleaned = ({});
    array(string) removed = ({});

//    perror("Database clean: old players : %O",p);

    multiset ingame = (<>);

  // Check what players are currently in games.
  foreach (indices (d), string series)
    foreach (indices (d[series]), string game)
    {
      if (! mappingp (d[series][game])) continue;
      foreach (indices (d[series][game]["p"]), string empire)
        ingame += (< empire >);
    }

    foreach( indices(p), string name ) {
        // need to check if players are currently in games
      if(p[name]->wins == 0) {
        if(p[name]->kills == 0) {
          if(!ingame[name]) {
            if((time()-(p[name]->l_log))>86400*100) {
              cleaned += ({ p[name]->caps||name });
//            removed += ({ name, p[name] });
//            removed += (<name>);
              removed += ({name});
              perror("\nDatabase purge: "+name+" removed - last login "+nctime(p[name]->l_log));
              m_delete(p, name);
            } else {
              perror("\nDatabase purge: "+name+" retained - last login "+nctime(p[name]->l_log));
            }
          } else {
            perror("\nDatabase purge: "+name+" retained - in a game");
          }
        } else {
          perror("\nDatabase purge: "+name+" retained - has "+p[name]->kills+" nukes");
        }
      } else {
        perror("\nDatabase purge: "+name+" retained - has "+p[name]->wins+" wins");
      }
    }
//    perror(" Cleaned : %O",p);

//    perror("\nDatabase clean: Removed players : %O", removed);
    if(sizeof(cleaned))
        return( "Players "+(cleaned*", ")+" were removed from the database.");
    else
        return ("No players were removed from database.");
}


string player_clean()
{

  int PCheck = 0;
  perror("\nDatabase check: Players : %O",p);  // this will dump all player details to the log file

  foreach(indices(p),string name)
  {
    if(!mappingp(p[name])) continue;           // player doe not exist???

    // check some player details
    PCheck ++;

    //p[name]["pass"]=pass;
    p[name]["real_name"] = sterilizeName (p[name]["real_name"], 0);
    p[name]["e-mail"] = sterilizeString (p[name]["e-mail"]);

    if ( p[name]["alien"] == 0 )                    // make sure some sort of icon is set
      p[name]["alien"] = "alien1.gif";

    //p[name]["wins"]=0;
    //p[name]["kills"]=0;
    //p[name]["ruins"]=0;
    //p[name]["killed"]=0;
    if ( !(p[name]["max_e_pow"]) )
      p[name]["max_e_pow"]=0;
    if ( !(p[name]["max_m_pow"]) )
      p[name]["max_m_pow"]=0;
    if ( !(p[name]["missive"]) )
      p[name]["missive"]="";
    if ( !(p[name]["l_log"]) )
      p[name]["l_log"]=time();
    if ( !(p[name]["last_ip"]) )
      p[name]["last_ip"] = "";

    p[name]["comments"] = sterilizeString(p[name]["comments"]);
    p[name]["sneer"] = sterilizeString(p[name]["sneer"]);

    // additional V3 stuff
    if ( !(p[name]["sponsor"]) )
      p[name]["sponsor"] = "Independent";
    if ( !(p[name]["seeking"]) )
      p[name]["seeking"] = 0;
    if ( !(p[name]["grp_admin"]) )
       p[name]["grp_admin"] = 0;
    if ( !(p[name]["pgroup"]) )
      p[name]["pgroup"] = "";

    //p[name]["home_url"] = (id["variables"]["url"]);
    //p[name]["theme_url"] = (id["variables"]["theme_url"]);
    // p[name]["timezone"] = time_offset;

    if ( !(p[name]["bcast"]) )
      p[name]["bcast"] = "";
    if ( !(p[name]["flags"]) )
      p[name]["flags"] = 0;
  }

  perror("\nDatabase check: Players checked : %O",PCheck);

  if(PCheck > 0)
    return( "Players "+PCheck+" were checked.");
  else
    return ("No player entries were checked.");
}


mapping admin_broadcast(string name, string c_pass)
{
 return (["data":
   header("Admin Broadcast","")+
   pageheader("Admin Broadcast", "")+
   "<p><img src=\""+theme_dir("")+"/spacerule.gif\" height=10  width=100%><p>"+
   "<input type=hidden name=\"name\" value=\""+name+"\">"+
   "<input type=hidden name=\"c_pass\" value=\""+c_pass+"\">"+
   "<input type=hidden name=\"action\" value=\"bcast_msg\">"+
   "<p><center><b>There are currently "+sizeof(indices(p))+" players</b></center><p>"+
   "<p><center><table><tr><td><b>Message:</b></td>"+
   "<td><input type=text name=\"b_message\" size=30 maxlength=50></td>"+
   "<td><input type=submit name=\"conf\" value=\"Send\"><td>"+
   "<td><input type=submit name=\"conf\" value=\"Cancel\"></td></tr></table><p></center>"+
    footer(""),
    "type":"text/html"]);
}

mapping admin_broadcast2(object id)
{
  string name = id["variables"]["name"];
  string c_pass = id["variables"]["c_pass"];

  if(!admin_id_check(name, c_pass, id))
    return login_failed("Identity checking failed during login. Please try again.");  // do  not advertise admin stuff!!

  if(id["variables"]["conf"] == "Cancel")
     return admin_login(name,c_pass,"Operation Canceled");
  foreach(indices(p),string empName)
  {
    if(!mappingp(p[empName])) continue;
    p[empName]["bcast"] = sprintf("%s brodcasted at %s <br><br><center> %s</center>", name,nctime(time()),(string)id["variables"]["b_message"]);
  }
  return admin_login(name,c_pass,"Message sent");

}


/* ---------- Admin Menu options ---------------- */
mapping admin_login2(object id)
{
  string index;
  string *a;
  string cmd;

  string name=id["variables"]["name"];
  string c_pass=id["variables"]["c_pass"];

  if(!admin_id_check(name, c_pass, id))
    return login_failed("Identity checking failed during login. Please try again.");  // do  not advertise admin stuff!!

  foreach(indices(id["variables"]), index) {
    a=index/":";
    if(a[0] != "ad") continue;
    cmd=a[1];
    break;
  }

  switch(cmd)
    {
    case "sst":
      return start_series(name, c_pass, "");
/*   case "sha":
      return flow_series(name, c_pass, 0);
    case "sre":
      return flow_series(name, c_pass, 1);
*/
    case "ski":
      return kill_series(name, c_pass);
    case "gki":
      return kill_game(name, c_pass);
    case "rti":
      return fix_times(name, c_pass);
    case "gvi":
      return view_game(name, c_pass);
    case "dump":
      return admin_dump_data(name, c_pass);
    case "load":
      return admin_load_data(name, c_pass);
    case "asdump":
      return admin_dump_asdata(name, c_pass);
    case "asload":
      return admin_load_asdata(name, c_pass);
    case "restart":
      return admin_restart(name, c_pass);
//      call_out(dump_data(0, 1),15);
//      call_out(dump_data(),15);
//      return login_form();              // this does not seem to get executed ???
    case "exit":
      return login_form();
    case "logmsg" :
      return login_msg(name, c_pass);
    case "sli" :
      return list_series(name, c_pass);
    case "edser" :
      return edit_series(name, c_pass);
    case "emp" :
      return empire_viewer(name, c_pass,"");
    case "clean" :
      return admin_login(name, c_pass, database_clean());
    case "playchk" :
      return admin_login(name, c_pass, player_clean());
    case "bcast" :
      return(admin_broadcast(name, c_pass));
    default:
      return login_failed("A non-valid choice was entered from the administrative screen.");
    }
}

/* ---------- Player login ---------------- */
mapping login_player(object id)
{
  string name=id["variables"]["name"];
  string pass=id["variables"]["pass"];
  string index;

  if(name == ad_name) {
    if(pass == ad_pass) {
      string c_pass=crypt(pass);
      return admin_login(name, c_pass, "");
    } else {
      return login_failed("Invalid password entered for this login.");  // do not advertise the admin login !!
    }
  } else if(p[name]) {
    if(p[name]["pass"] == pass) {
      string c_pass=crypt(pass);
      p[name]["l_log"]=time();
      p[name]["last_ip"] = roxen->quick_ip_to_host(id->remoteaddr);

//     if (p[name]["flags"] & useframe)
      if ( name == "Frame" )
        return frheader(name,c_pass);

      return game_list(name, c_pass, 0, split_gl, "");
    } else {
      return login_failed("Invalid password entered for this empire.");
    }
  } else {
      // check for duplicate empire names... ignoring case
      foreach(indices(p), index) {
      if(lower_case(index) == lower_case(name))
        return login_failed("Sorry... this empire name is already used.");
      }

    return create_player(sterilizeName(name, 0), pass);
  }
}

/* ---------- New Player creation ---------------- */
mapping create_player2(object id)
{
  string index;
  mixed *alien;
  mixed icon;

  string name=sterilizeName (id["variables"]["name"], 0);
  string pass=id["variables"]["pass"];

  if(!sizeof(name))
    return login_failed("You must enter an alphanumeric name.");

  if(search((string) name, "=")!=-1)
    return login_failed("Your empire name contained a =.");


  // re-check for duplicate empire names... ignoring case
  foreach(indices(p), index)
    if(lower_case(index) == lower_case(name))
     return login_failed("Sorry... this empire name is already used.");

  foreach(indices(id["variables"]), index)
    if(index == "cancel") return login_form();

/*      Still not sure if time info is useful :(
   int time_offset=(int) id["variables"]["time_offset"];
  if(time_offset > 0) {
    switch(id["variables"]["time_unit"]) {
      case "Minutes" :
        time_offset *= 60;
      case "Hours":
        time_offset *= 3600;
    }
  }
 */

  if(id["variables"]["pass_check"] == pass) {
    p[name]=([]);
    p[name]["pass"]=pass;
    common_profile(name, id);

    foreach(indices(id["variables"]), index) {
      alien=index/":";
      if(alien[0] != "alien") continue;
      alien=alien[1]/".";
      p[name]["alien"]=alien[0]+"."+alien[1];        // was 0 ... use full name
      break;
    }
    if ( p[name]["alien"] == 0 )                    // make sure some sort of icon is set
      p[name]["alien"] = "alien1.gif";

    p[name]["wins"]=0;
    p[name]["kills"]=0;
    p[name]["ruins"]=0;
    p[name]["killed"]=0;
    p[name]["max_e_pow"]=0;
    p[name]["max_m_pow"]=0;
    p[name]["missive"]="";
    p[name]["l_log"]=time();
    p[name]["last_ip"] = roxen->quick_ip_to_host(id->remoteaddr);

/*
    // additional V3 stuff
    p[name]["sponsor"] = "Independent";
    p[name]["seeking"] = 0;
    p[name]["grp_admin"] = 0;
    p[name]["pgroup"] = "";
*/
   // p[name]["timezone"] = time_offset;

    p[name]["bcast"] = "";

    string c_pass=crypt(pass);

    // icon selected??
    if (id["variables"]["save"] !="Save Profile")
       return profile_icon(name, c_pass, "alien1.gif",1);

    return game_list(name, c_pass, 0, split_gl, "");
  } else {
    return login_failed("Your password didn't match the first one you typed in.");
  }
}

/* --------------------------------------------------
*               Main game setup options             *
 --------------------------------------------------*/
void create()
{
  defvar("mountpoint", "/sc", "Mount point", TYPE_LOCATION,
         "SC location in www filesystem. ie. http://your.site/sc");
  defvar("searchpath", "/sc", "Search path to SC files", TYPE_LOCATION,
         "SC directory location in real filesystem.");
  defvar("motd", "", "Message of the day", TYPE_TEXT_FIELD,
         "The message of the day. This text is displayed on the login form.");
  defvar("truce", "", "Empires that cannot truce", TYPE_TEXT_FIELD,
         "Restrict empires to truce with certain other empires.<p>"
         "Syntax: empire:empire1,empire1,...,empireN");
  defvar("support_dir", "/support", "Support Directory", TYPE_LOCATION,
         "Where SC will find it's files. This needs to be "
         "mounted as a filesystem.");
  defvar("alien_cnt", 128, "Number of aliens", TYPE_INT,
         "The number of alien portraits available in the support area.");
  /*
  defvar("brloss", 1.0, "Engineer BR loss", TYPE_FLOAT,
         "Every time an engineer closes or opens a link he lose "
         "this much BR and MAX BR.");
         */
  defvar("home_rsrc", 100, "Home System Resources", TYPE_INT,
         "The resource total for home systems.");
  defvar("avg_rsrc", 30, "Average Resources", TYPE_INT,
         "The average resource total for every system.");
  defvar("close_game", 5, "Updates before a game is closed", TYPE_INT,
         "After this many updates a game is closed, and no one can join.");
  defvar("admin_inc", 3600, "Admin Increment", TYPE_INT,
         "The approximate number of seconds between automatic administrative functions.");
  defvar("servername", "SC"+version, "The name of the server", TYPE_STRING,
         "The name of this server. It's shown in the game list title.");
  defvar("notoplist", 0, "Disable top lists", TYPE_FLAG,
         "If yes, top lists will be disabled from the statviewer. The reason "
         "for this is that some people can't resist cheating, just to get up "
         "on the list.");
  defvar("ad_name", "admin", "Administrator's Name", TYPE_STRING,
         "The login name for the administrator.");
  defvar("ad_mail", "Change me!", "Administrator's E-mail address",
         TYPE_STRING, "The e-mail address of the administrator.");
  defvar("ad_pass", "Change me!", "Administrator's Password", TYPE_STRING,
         "The login password for the administrator.");
  defvar("dump_file", "../logs/sc.dump", "Dump File", TYPE_STRING,
         "Where SC will dump its data for backups and crash protection.");
  defvar("hist_file", "/history/sc.hist", "History File", TYPE_STRING,
         "Path under support directory where SC will write its game history data.");
  defvar("debug_file", "../logs/sc.debug", "Debug File", TYPE_STRING,
         "Where SC will write debugging output to.");
  defvar("faq_url", "http://agt.resf.insa-lyon.fr:9130/support/faq.html", "FAQ URL",
         TYPE_STRING,
         "Where the SC FAQ is located. It defaults to the Lugdunum.");
  defvar("help_url", "http://stellar.gamestats.com/SC.main.html", "Help URL", TYPE_STRING,
          "Where to go for help, news and resources. It defaults to the Stellar Crisis Room.");
  defvar("heading_color", "#00C1FF", "Color of Heading Text", TYPE_STRING,
         "Expressed in #RRGGBB format.");
  defvar("server_color", "#00C1FF", "Color of Body Text", TYPE_STRING,
         "Expressed in #RRGGBB format, for the screens external to games.<br>"+
         "Colour for individual game series can be configured independently, but will take this value by default.");
  defvar("asciidata", 0, "Save database in ASCII format", TYPE_FLAG,
         "If Yes, default database load/save will use the single ASCII file format.<br>"
         "If No, database load/save will use the encode() functions with seperate player and game files.");
  defvar("shipscr_format", 1, "New Ships Screen Format", TYPE_FLAG,
         "If Yes, new format (Stargate): Ship Type in column 2.<br>"
         "If No, old format (Lugdunum): Ship Type on the right.");
  defvar("glist_format", 1, "Stacked Basic Game List Info", TYPE_FLAG,
         "If Yes, Stacked format (Stargate).<br>"
         "If No, Strung format (Lugdunum).");
  defvar("split_gl", 1, "Split Game List", TYPE_FLAG,
         "If Yes, initial screen on Login shows only this empire's current games"+
         " - open and new game lists have separate buttons.<br>"+
         "If No, initial screen shows all games - heavy on bandwidth.");
  defvar("enable_bridier", 0, "Enable Bridier Ranking", TYPE_FLAG,
         "If Yes, the Bridier Ranking System is enabled.<br>"
         "If No, the Bridier Ranking System is disabled.");

}

void do_dump_data()
{
  int asciidb = QUERY(asciidata);

  if (asciidb)
//    dump_asciidata(1);
    dump_asciidata();
  else
//    dump_data(1);
    dump_data();
  call_out(do_dump_data, admin_inc);
}

/* ---------- Top Level options ------------- */
mapping find_file(string f, object id)
{
  int i;
  check_for_updates();

//  if( f == "remote_status" )
//    return http_string_answer( remote_status( id ), "text/plain" );

  if(time()-last_admin>admin_inc) {
    last_admin=time();
    prep_top_ten();

#ifdef SC_DEBUG
//    dump_data(1);
    dump_data();
#endif

  }
  if(id->not_query != mountpoint)
    return 0;

  if(id->method == "GET") {
    string frload = (string) id["variables"]["frload"];
    string name;
    string c_pass;
    string series;
    string game;
    string system;
    string *parm;
    parm = frload/"?";
    if ( frload != "0") {
//        log_msg = "frload: "+frload+" parm: "+parm[0];
//        return login_form();
        name = unclean_url((string) parm[1]);
        c_pass = (string) parm[2];
        if(!id_check(name, c_pass))
          return login_failed("Identity check failed.");
        if ( parm[0] == "fr_logbtn" )
          return fr_log_buttons(name,c_pass);
        if ( parm[0] == "fr_gamelst" )
          return game_list(name,c_pass,0,split_gl, "");
        if ( parm[0] == "fr_system" ) {
          series = unclean_url((string) parm [3]);
          game = (string) parm [4];
          system = (string) parm [5];
          return systems_screen(name,c_pass,series,game,"",system, 1);
        }

    } else
      return login_form();
  }

/* ---------- Main  player actions ----------- */
  switch(id["variables"]["action"])
    {
    case "Login":
      return login_player(id);
    case "create":
      return create_player2(id);
    case "g_list":
      return game_list2(id);
    case "edit_prof":
      return edit_profile2(id);
    case "prof_icon":
      return profile_icon2(id);
    case "hist_view":
      return hist_viewer2(id);
    case "stat_view":
      return stat_viewer2(id);

/* ---------- Startup frame actions ----------- */
//    case "fr_logbtn" :
//      return fr_log_buttons(id);
//    case "f_login" :
//      return fr_login_form();

/* ---------- Server Admin Options ----------- */
    case "admin":
      return admin_login2(id);
    case "start_series":
      return start_series2(id);
/*
     case "flow_series":
      return flow_series2(id);
*/
    case "kill_series":
      return kill_series2(id);
    case "kill_game":
      return kill_game2(id);
    case "view_game":
      return view_game2(id);
    case "play":
      return play_action(id);
    case "login_message" :               // 2.9admin functions
      return login_msg2(id);
    case "edit_series" :
      return edit_series2(id, "");
    case "update_series" :
      return edit_series3(id);
    case "series_list" :
      return list_series2(id);
    case "empire_view":
      return empire_viewer2(id);
    case "empire_edit":
      return empire_edit(id);
    case "empire_update":
      return empire_update(id);
    case "bcast_msg" :
      return admin_broadcast2(id);;
    default:
      return
        (["data":
         header("Undef Action","")+
//       "<center><b>Undefined Action</b></center>"+
         pageheader("Undefined Action...", "")+
         "Action: "+id["variables"]["action"]+"<p>"+
         "If action is being reported as 0, an error has taken place in "
         "transmission. Please use the back function of your browser, and "
         "try again. If action is being reported as a defined string, please "
         "inform <a href=\"mailto:"+ad_mail+"\">"+ad_mail+
         "</a> of the event, and <i>exactly</i> what you "
         "were doing when you received this message. Thank you.\n"+
         footer(""),
         "type":"text/html"]);
    }
}

void start()
{
  string s;
  truceban = ([]);
  foreach(QUERY(truce)/"\n", s)  {
    array tmp = s/":";
    if(sizeof(tmp) == 2)
      truceban[tmp[0]] = tmp[1]/",";
  }
  if(strlen(servername = QUERY(servername)))
    servername = " @ "+servername;

  mountpoint = QUERY(mountpoint);
  html_dir = QUERY(support_dir);
  datapath = QUERY(searchpath);
  ad_name = QUERY(ad_name);
  ad_mail = QUERY(ad_mail);
  ad_pass = QUERY(ad_pass);
  dump_file = QUERY(dump_file);
  hist_file = QUERY(hist_file);
  debug_file = QUERY(debug_file);
  faq_url = QUERY(faq_url);
  help_url = QUERY(help_url);
  aliens = QUERY(alien_cnt);
  avg_rsrc = QUERY(avg_rsrc);
  home_rsrc = QUERY(home_rsrc);
  admin_inc = QUERY(admin_inc);
  close_game = QUERY(close_game);
  server_color = QUERY(server_color);
  heading_color = QUERY(heading_color);
  shipscr_format = QUERY(shipscr_format);
  glist_format = QUERY(glist_format);
  enable_bridier = QUERY(enable_bridier);
  split_gl = QUERY(split_gl);
  toplist = ! QUERY(notoplist);
  log_msg = "";
  history_1 = "";
  history_2 = "";
  history_3 = "";
  hist_buff = 0;


  int asciidb = QUERY(asciidata);

  if(p && sizeof(p)) {
    if ( asciidb )
//      dump_asciidata(0);
      dump_asciidata();
    else
//      dump_data(0);
      dump_data();
  } else {
    if ( asciidb ) {
      if(!load_asciidata()) {
        d=([]);
        p=([]);
      }
    } else {
      if(!load_data()) {                // uses encode-value()
        d=([]);
        p=([]);
      }
    }
    prep_top_ten();
    call_out(do_dump_data, admin_inc);

#ifdef SC_DEBUG
    call_out(auto_dump, 300);
#endif
  }
}
