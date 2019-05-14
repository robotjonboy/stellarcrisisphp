<?php
#----------------------------------------------------------------------------------------------------------------------#
require('info.php');
require('tech.php');
require('ships.php');
require('build.php');
require('systems.php');
require('map.php');
require('diplomacy.php');
require('messageHistory.php');
require('notes.php');
require('scouting.php');
require('invite.php');
require('password.php');
require('makemap.php');
#----------------------------------------------------------------------------------------------------------------------#

// Local coordinates variables and functions added 26.04.2002 by bc
// Translation to/from local coordinates is necessary only for the currently processing empire in this file
// Coordinates for the location of the homeworld for the player currently being processed
$hwX = 0;
$hwY = 0;

// This array is used for the current process and caches order lists where they are used.
// Statistically, the number of distinct order lists is low, when the number of ships is high.
$valid_orders_cache = array();

// This array is used mostly on the Ships and Fleets screen.
$system_cache = array();

#----------------------------------------------------------------------------------------------------------------------#

function xlateToLocal($galacticCoord)
{
	global $server, $hwX, $hwY;

	if (!$server['local_coordinates'])
		return $galacticCoord; // translation to galactic and getOffset should be disabled as well

	list($x, $y) = explode(',', $galacticCoord);

	return ($x-$hwX).','.($y-$hwY);
}

#----------------------------------------------------------------------------------------------------------------------#

function xlateToGalactic($localCoord)
{
	global $server, $hwX, $hwY;

	if (!$server['local_coordinates'])
		return $localCoord; // translation to local and getOffset should be disabled as well

	list($x, $y) = explode(',', $localCoord);
	
	return ($x+$hwX).','.($y+$hwY);
}

#----------------------------------------------------------------------------------------------------------------------#
# Given a string of jumps in galactic coordinates, this function return a string of jumps in local coordinates.
#

function localizeJumps($jumps)
{
	return ($jumps == '' ? '' : implode(' ', array_map('xlateToLocal', explode(' ', $jumps))));
}

#----------------------------------------------------------------------------------------------------------------------#
# This function layer was added to manage the processing flag, which keeps an update from running while players
# are setting values. By putting in the extra layer, we skip having to rewite around all the return statements
# in the original gameAction (which is now processGameAction()).
#

function gameAction($vars)
{
	global $server, $ship_types, $authenticated, $hwX, $hwY;

	if (!$authenticated)
		return loginFailed('Identity check failed.');

	// Get and store database records for game processing functions.
	$empire = getEmpire($vars['name']);
	$vars['empire_data'] = $empire;
	
	// Is the game still around?
	if (!$series = getSeries($vars['series_id']) or !$game = getGameByID($vars['game_id']))
		{
		sendEmpireMessage($empire, 'That game no longer exists.');
		return gameList($vars);
		}

	$vars['series_data'] = $series;
	$vars['game_data'] = $game;

	// Is the player still in this game?
	$player = getPlayer($game['id'], $vars['name']);

	if (!$player or $player['team'] < 0)
		{
		sendEmpireMessage($empire, 'You are no longer in that game.');
		return gameList($vars);
		}

	$vars['player_data'] = $player;

	// COMMIT previous work and start a new transaction.
	sc_mysql_query('BEGIN');
	
	// Get and lock this game's record.
	$select = sc_mysql_query('SELECT * FROM games WHERE id = '.((int)$vars['game_id']).' FOR UPDATE');

	// Find this player's homeworld to initialize his local coordinate system.
  	$select = sc_mysql_query('SELECT coordinates FROM systems WHERE game_id = "'.((int)$game['id']).
  	                         '" AND homeworld = "'.mysql_real_escape_string($vars['name']).'"');
	$homeworld = mysql_fetch_array($select);
	
	// Here we set the global $hwX and $hwY variables. They are used on many game screens
	// whenever we need to convert to local coordinate space and back.
	list($hwX, $hwY) = explode(',', $homeworld['coordinates']);
	list($offset_x, $offset_y) = explode(',', $player['map_origin']);
	
	$hwX -= $offset_x;
	$hwY -= $offset_y;
	
	// Determine if the player should be able to access the map screen (map shopper deterrent).
	$map_allowed = ($series['map_visible'] or (!$series['team_game'] and $game['player_count'] > 1) or $game['closed']);
	
	// Update the player's last access in this game.
	// We also update the player's last known IP address.
	// This will be used to determine if there are any potential multi-empers in the game, if we want this.
	$values = array();
	$values[] = 'ip = "'.$_SERVER['REMOTE_ADDR'].'"';
	$values[] = 'last_access = '.time();
	$values[] = 'last_update = '.$game['update_count'];
	sc_mysql_query('UPDATE players SET '.implode(',', $values).' WHERE id = '.$player['id']);

	// This is where we process form data from various screens. What we return is determined later.
	// If the processing functions return true, it means we want to stay on the screen we were on (the user needs to be
	// informed of something). The two slight exceptions are the Map and Mini-Map processing which return a zoomed-on planet.
	// In those cases, a coordinate is return and we use a different screen to zoom in.
	switch ($vars['play'])
	{
   		case 'Info':												break;
   		case 'Set Passwords':	if (passwordScreen_processing($vars)) $vars['gameAction'] = 'Set Password';	break;
   		case 'Invitations':	inviteScreen_processing($vars);							break;
		case 'Tech':		if (techScreen_processing($vars)) $vars['gameAction'] = 'Tech';			break;
   		case 'Ships':		if (shipsScreen_processing($vars)) $vars['gameAction'] = 'Ships';		break;
		case 'Fleets':		if (fleetsScreen_processing($vars)) $vars['gameAction'] = 'Fleets';		break;
		case 'Build':		if (buildScreen_processing($vars)) $vars['gameAction'] = 'Build';		break;
		case 'Systems':		systemsScreen_processing($vars);						break;
		case 'Map':			if (mapScreen_processing($vars)) $vars['gameAction'] = 'Systems';		break;
		case 'Mini-Map':	if (mapScreen_processing($vars)) $vars['gameAction'] = 'Map';			break;
		case 'Diplomacy':	if (diplomacyScreen_processing($vars)) $vars['gameAction'] = 'Diplomacy';	break;
		case 'Message History':	if (messageHistory_processing($vars)) $vars['gameAction'] = 'Message History';	 break;
		case 'Notes':	   	notesScreen_processing($vars);							break;
		case 'Scouting Report':	
			if (scoutingScreen_processing($vars)) $vars['gameAction'] = 'Systems';			
			break;
		//cjp - add check all
		case 'Full Scouting Report':	
			$vars['cjp_check_all'] = 1;
			if (scoutingScreen_processing($vars)) 
			{
				//systemsScreen_processing($vars);
				$vars['gameAction'] = 'Systems';
			}
			break;
		default:		sendGameMessage($player, 'Invalid play action.');
  	}

	// COMMIT what we've done and begin a new transaction.
	// No more modifications are needed to the database.
	sc_mysql_query('BEGIN');
	
	// Refetch these; they may have changed somewhere above.
  	$vars['series_data'] = $series = getSeries($vars['series_id']); // Can this really change?
	$vars['player_data'] = $player = getPlayer($game['id'], $vars['name']);
	$vars['game_data'] = $game = getGameByID($game['id']);

	// I hope this catches the fleet screen bug I've been having (invalid $player array being passed).
	if (!$player)
		{
		// Log the error.
		trigger_error('Invalid $player record found in gameAction(). Fix me.');
		$vars['gameAction'] = 'DEAD';
		}
		
  	// After processing, we go to the page the user requested.
	switch ($vars['gameAction'])
		{
		case 'Info':				return infoScreen($vars, $message);
		case 'Change Password':
		case 'Change Passwords':
		case 'Set Password':
        case 'Set Passwords':		return passwordScreen($vars);        
        case 'Invite Players':		return inviteScreen($vars);
	 	case 'Tech':				return techScreen($vars);
	 	case 'Ships':				return shipsScreen($vars);
   		case 'Fleets':				return fleetsScreen($vars);
	 	case 'Build':				return buildScreen($vars);
	 	case 'Systems':				if ($map_allowed) return systemsScreen($vars);
		case 'Scouting Report':		if ($map_allowed) return scoutingScreen($vars);
		case 'Full Scouting Report':	
			// redraw system list with checked boxes
			$cjp_check_all = 1;
			if ($map_allowed) return systemsScreen($vars);
	 	case 'Map':					if ($map_allowed) return mapScreen($vars);
	 	case 'Mini-Map':			if ($map_allowed) return miniMapScreen($vars);
	 	case 'Diplomacy':			return diplomacyScreen($vars);
	 	case 'Message History':		return messageHistory($vars);
	 	case 'Notes':				return notesScreen($vars, $message);
        
        // This is currenlty disabled due to abuse.
		/*case 'Pause game':
        	pauseGame($game);
        	$game['on_hold'] = '1';
        	return infoScreen($vars, $series, $game, $player, 'Game paused.');
        case 'Resume game':
        	resumeGame($game);
        	$game['on_hold'] = '0';
        	return infoScreen($vars, $series, $game, $player, 'Game resumed.');*/
        	
	 	case 'End Turn':
			$player['ended_turn'] = 1;
			$vars['player_data'] = $player;
		 	sc_mysql_query('UPDATE players SET ended_turn = "1" WHERE id = '.$player['id']);
		 	
			// Check to see if an update is needed.
			switch (endTurnUpdate($series, $game))
				{
				case 0:
					// Update processed, game ended in either a draw or a win-- results are in empire missive.
					return gameList($vars);
				case 1:
					// Update processed, game still running.
					// Is the player still around? If not, go to the game list, instead of a broken Info screen.
					if (!$player = getPlayer($game['id'], $vars['name']))
						{
						sendEmpireMessage($empire, 'That game no longer exists.');
						return gameList($vars);
						}
					else
						{
						// This seems redundant. The player gets the update missive at the same time...
						$player['ended_turn'] = 0;
						$vars['player_data'] = $player;
						sendGameMessage($player, 'An update has occurred.');
						return infoScreen($vars);
						}
				case 2:
					// Update not processed (somebody hasn't ended turn)
					sendGameMessage($player, 'You are now ready for an update.');
					return infoScreen($vars);
				}
			break;

		case 'Cancel End Turn':
			sendGameMessage($player, 'You are no longer ready for an update.');

			$player['ended_turn'] = 0;
			$vars['player_data'] = $player;
			sc_mysql_query('UPDATE players SET ended_turn = "0" WHERE id = '.((int)$player['id']));

			return infoScreen($vars);

		case 'Quit':
			// If there's only one player, we can just scrap the game,	
			if ($game['player_count'] == 1)
				{
				sendEmpireMessage($empire, 'Fine, flee from <span style="color: red;">'.$series['name'].' '.$vars['game_number'].'</span> like the coward you are!');
				eraseGame($game['id']);

				// Also remove the bridier record.
				sc_mysql_query('DELETE FROM bridier WHERE game_id = '.$game['id']);

				return gameList($vars);
				}
			else if ($series['team_game'] and !$game['closed'])
				{
				// If there is more than one player, we can only quit if it's an unstarted team game without a password.
				if ($game['password'])
					{
					sendGameMessage($player, 'You cannot leave a password protected game after another player has joined.');
					return infoScreen($vars);
					}
					
				playerLeftTeamGame($series, $game, $player, $empire);
					
				return gameList($vars);
				}
			else
				{
				sendGameMessage($player, 'The game has started. You cannot leave.');
				return infoScreen($vars);
				}
			break;
		case 'Exit':
			return gameList($vars);
	 	default:
			sendGameMessage($player, 'Invalid button identifier ('.$vars['gameAction'].').');
	 		return infoScreen($vars);
		}
}

#----------------------------------------------------------------------------------------------------------------------#

function gameHeader($vars, $title)
{
    global $server;

	$series = $vars['series_data'];
	$game = $vars['game_data'];
	$player = $vars['player_data'];
	$empire = $vars['empire_data'];

	$update = '';
	$next_update = '';

	// Ability to un-end your turn.
	if ($player['ended_turn'])
		$end_turn_button = '<input type=submit name=gameAction value="Cancel End Turn">';
	else
		$end_turn_button = '<input type=submit name=gameAction value="End Turn">';

	$quit_button = $mini_map = '';
	$exit_button = '<input type=submit name=gameAction value=Exit>';
	$password_button = '';

    if ($series['systems_per_player']*$series['max_players'] > $server['minimap_threshold'])
		$mini_map = '<input type=submit name=gameAction value="Mini-Map">';

	// Dump queued messages to the player if he's not on the message history screen.
	if ($player['id'] != '')
		{
		$conditions = array();
		$conditions[] = 'player_id = '.((int)$player['id']);
		$conditions[] = 'flag = "0"';
		
		if ($title == 'Message History')
			$conditions[] = 'type = "game_message"';
		
		$select = sc_mysql_query('SELECT * FROM messages WHERE '.implode(' AND ', $conditions).' ORDER BY type, id');
	
		if (mysql_num_rows($select))
			{
			$missives = array();

			while ($row = mysql_fetch_array($select))
				{
				// The message header is handled in messageHeader(), but first we 
				// adjust the sender and recipient if the player's name is in them.
				$row['recipient'] = str_replace($player['name'], 'you', $row['recipient']);
				
				if ($row['sender'] == $player['name'])
					$row['sender'] = 'You';
	
				$missives[] = formatMessage($row);
				}
							
			$missive = '<div style="margin-top: 10pt;"><table style="margin-left: auto; margin-right: auto;">'.
					   '<tr><td style="text-align: left;">'.implode('<br>', $missives).'</td></tr>'.
					   '</table></div>';
			
			// We don't keep game messages and scouting reports after displaying them once.
			sc_mysql_query('DELETE FROM messages WHERE (type = "scout" OR type = "game_message") AND player_id = '.
			               ((int)$player['id']));
			
			// The rest is marked as read.
			sc_mysql_query('UPDATE messages SET flag = "1" WHERE player_id = '.((int)$player['id']));
			}
		}

	$weekend = (ereg('(Sat|Sun)', date('D', time())));
	$time_to_update = $game['last_update']+$game['update_time']-time();

	$map_buttons = '<input type=submit name=gameAction value=Systems><input type=submit name=gameAction value=Map>';
	
	// Do pre-start processing; the game hasn't started if there's only 1 player in a regular game or if it's an unfilled team game.
	if ($game['player_count'] == 1 or ($series['team_game'] == 1 and $game['closed'] == 0))
		{
		if ($series['team_game'])
			$next_update = 'Waiting for all players to join.';
		else
			$next_update = 'Waiting for other players.';
		
		// This is to make sure the game doesn't update itself as soon as a second player joins in.
		sc_mysql_query('UPDATE games SET last_update = '.time().' WHERE id = '.((int)$game['id']));
		
		$time_to_update = $game['update_time'];

		// Allow players to quit if they are the only player in the game or in team games when the map is not visible.
		// REMEMBER- we already checked if this is a team game that didn't start above-- be careful if you move this code!
		if (!$series['map_visible'] or $game['player_count'] == 1)
			$quit_button = '<input type=submit name=gameAction value=Quit>';

		// This game hasn't started yet. If the map isn't visible before the start, remove all the map revealing buttons.
		if (!$series['map_visible'])
			{
			$map_buttons = '';
			$mini_map = '';
			}
		}
	else
		{
		// The game has started; calculate the time to the next update.
		if (!$game['weekend_updates'] and $weekend)
			$next_update = 'Game is on hold for the weekend.';
		else if ($game['on_hold'] and $game['closed'] == 0)
			$next_update = 'Game is paused.';
		else
			$next_update = 'Update '.($game['update_count']+1).' in '.secondsToString($time_to_update).'.';
		}

  	// Depending on what screen we are, when the screen auto-updates we will need to "click" one of
 	// the buttons at the top of the page. This is done through Javascript and this is where we build the command.
 	// curse these magic numbers-- they are the ordinal element IDs in the form. I think we could do better!
	switch ($title)
		{
		case 'Info':		$gotoDoc = 7;	break;
		case 'Tech':		$gotoDoc = 8;	break;
   		case 'Ships':		$gotoDoc = 9;	break;
   		case 'Fleets':		$gotoDoc = 10;	break;
   		case 'Build':		$gotoDoc = 11;	break;
   		case 'Systems':		$gotoDoc = 12;	break;
   		case 'Map':			$gotoDoc = 13;	break;
   		case 'Diplomacy':	$gotoDoc = 14;	break;
   		default:			$gotoDoc = 7;	// used to default to exit or end turn-- now let's do info
  		}

	// Can set passwords when just one player.
	if ($game['player_count'] == 1 and $game['game_password'] == '' and $title != 'Set Passwords')
		{
		if ($series['team_game'])
			$password_button = '<input type=submit name=gameAction value="'.($game['password1'] == '' ? 'Set' : 'Change').' Passwords">';
		else
			$password_button = '<input type=submit name=gameAction value="'.($game['password1'] == '' ? 'Set' : 'Change').' Password">';
		
		$gotoDoc += 1;	// set button adds one element to form
		}
		
	if ($game['created_by'] == $player['name'] and !$game['closed'])
		$invite_button = '<input type=submit name=gameAction value="Invite Players">';
		
	/*if ($game['created_by'] == $player['name'] and !$game['closed'] and $game['player_count'] > 1)
		{
		if ($game['on_hold'])
			$on_hold_button = '<input type=submit name=gameAction value="Resume game">';
		else
			$on_hold_button = '<input type=submit name=gameAction value="Pause game">';
		}*/

	// finally, set the JavaScript command for the timeout
  	$gotoDoc = "'document.forms[0].elements[".$gotoDoc."].click()'";
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html><head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<link rel=StyleSheet href="styles.css" type="text/css">
<script src="sc.js" type="text/javascript"></script>
<?php
	if ($empire['draw_background'] == 0)
		echo '<style>body { background: none; background-color: black; }</style>';
	else
		{
		if ($empire['custom_bg_url'] != '')
			echo '<style>body { background: url("'.$empire['custom_bg_url'].'"); background-color: black; }</style>';
		
		echo '<style>body { background-attachment: '.$empire['background_attachment'].'; }</style>';
		}
?>
<title>SC <?php echo $server['version'].' @ '.$server['servername'].': '.$title.': '.$series['name'].' '.$game['game_number']; ?></title>
<?php 
// page updates are too fast on 4 minute games -cannot move ships
// echo '<body'.'>'.$server['standard_header_text']; 
echo '<body'.($empire['auto_update'] ? ' onLoad="setTimeout('.$gotoDoc.','.($time_to_update*1000).')"' : '').'>'.$server['standard_header_text']; 
?>
</head>
<form method=post action="sc.php">
<input type=hidden name=name value="<?php echo $player['name']; ?>">
<input type=hidden name=pass value="<?php echo $empire['password']; ?>">
<input type=hidden name=series_id value="<?php echo $series['id']; ?>">
<input type=hidden name=game_number value="<?php echo $game['game_number']; ?>">
<input type=hidden name=game_id value="<?php echo $game['id']; ?>">
<input type=hidden name=section value="game">
<input type=hidden name=play value="<?php echo $title; ?>">
<?php //echo $cjp_check_all. '=========='; ?>

<div class=pageTitle><?php echo $player['name'].' '.$title.': '.$series['name'].' '.$game['game_number']; ?></div>
<?php
	if ($title != 'Info')
		echo '<div style="text-align: center; margin-top: 5pt;">'.$next_update.'</div>';

	// Stupid line breaks that put spaces between the buttons... we need to condense.
	echo ($password_button ? '<div style="text-align: center; margin-top: 5pt;"">'.$password_button.'</div>' : '').
		 '<div style="text-align: center; margin-top: 5pt;""><input type=submit name=gameAction value=Info><input type=submit name=gameAction value=Tech>'.
		 '<input type=submit name=gameAction value=Ships><input type=submit name=gameAction value=Fleets>'.
		 '<input type=submit name=gameAction value=Build>'.$map_buttons.$mini_map.
		 '<input type=submit name=gameAction value=Diplomacy><input type=submit name=gameAction value="Message History">'.
		 '<input type=submit name="gameAction" value="Notes">'.$end_turn_button.$quit_button.$exit_button.$on_hold_button.
		 ($invite_button ? '<div style="text-align: center; margin-top: 5pt;"">'.$invite_button.'</div>' : '').'</div>'.
		 ($missive ? $missive : '').'<img class=spacerule src="images/spacerule.jpg" width="100%" height=10 alt="spacerule.jpg">'.$missive_history;
}

#----------------------------------------------------------------------------------------------------------------------#

function diplomacyString($status)
{
	switch ($status)
  		{
   		case 0: 	return 'Surrender';
   		case 1: 	return 'Draw';
   		case 2: 	return 'War';
   		case 3: 	return 'Truce';
   		case 4: 	return 'Trade';
   		case 5:		return 'Alliance';
   		case 6:		return 'Shared HQ';
   		default:	return 'Unknown!';
  		}
}

#-----------------------------------------------------------------------------------------------------------------------------------------#
# Ratios, displayed on most game screens.
#

function ratios($player)
{
?>
<table width="100%">
	<tr>
		<td style="text-align: right; color: white;">Maintenance Ratio:</td>
		<td><?php echo ($player['mineral_ratio'] ? number_format($player['mineral_ratio'], 5, '.', '') : '--'); ?></td>
		<td style="text-align: right; color: white;">Fuel Ratio:</td>
		<td><?php echo ($player['fuel_ratio'] ? number_format($player['fuel_ratio'], 5, '.', '') : '--'); ?></td>
		<td style="text-align: right; color: white;">Agriculture Ratio:</td>
		<td><?php echo ($player['agriculture_ratio'] ? number_format($player['agriculture_ratio'], 5, '.', '') : '--'); ?></td>
	 	<td style="text-align: right; color: white;">Tech Level:</td>
		<td><?php echo number_format($player['tech_level'], 5, '.', ''); ?></td>
		<td style="text-align: right; color: white;">Tech Development:</td>
		<td><?php echo number_format($player['tech_development'], 5, '.', ''); ?></td>
	</tr>
</table>
<img class=spacerule src="images/spacerule.jpg" alt="spacerule.jpg">
<?php
}

#----------------------------------------------------------------------------------------------------------------------#
# Returns the direction a ship is heading to.
#

function getDirection($origin, $destination)
{
	list($origin_x,		 $origin_y)		 = explode(',', $origin);
	list($destination_x, $destination_y) = explode(',', $destination);

	switch($destination_y-$origin_y)
		{
		case  1:	return 'north';
		case -1:	return 'south';
		}

	switch($destination_x-$origin_x)
		{
		case  1:	return 'east';
		case -1:	return 'west';
		}
}

#----------------------------------------------------------------------------------------------------------------------#

function pauseGame($game)
{
	sc_mysql_query('UPDATE games SET on_hold = "1" WHERE id = '.((int)$game['id']));
}

#----------------------------------------------------------------------------------------------------------------------#

function resumeGame($game)
{
	sc_mysql_query('UPDATE games SET on_hold = "0" WHERE id = '.((int)$game['id']));
}

#----------------------------------------------------------------------------------------------------------------------#
# Return values:
#
#	- 0: The game has updated and is now over. This comes from update_game().
#	- 1: Regular update has occured. This comes from update_game().
#	- 2: Criteria for update-on-end-turn not met. Nothing done.
#

function endTurnUpdate($series, $game)
{
	// The game should update on ending turn only if it's closed. This flag is set either in update_game() at the
	// appropriate conditions or when the last person joins the game so it's safe to only check this value here.	
	if (!$game['closed']) return 2;

	// If it's closed, we update only if everyone ended their turn.
	$select = sc_mysql_query('SELECT COUNT(*) FROM players WHERE game_id = '.$game['id'].' AND ended_turn = "0" AND team >= 0');
	if (mysql_result($select, 0, 0)) return 2;
	
	// Ok then.
	return update_game($series, $game, time());
}

#----------------------------------------------------------------------------------------------------------------------#

function techsWaiting($vars)
{
	global $ship_types;

	if ($vars['player_data']['tech_level'] < 1)
		return 0;
	else
		{
		$developed_tech_count = count(explode(' ', $vars['player_data']['techs']));
		
		$allowed_techs = array_keys($ship_types);

		// This is the maximum we can display.
		$undeveloped_tech_count = (count($allowed_techs)-$developed_tech_count);

		// The maximum number of techs we can have at this level.
		$maximum_tech_count = (floor(sqrt($vars['player_data']['tech_level']))+3);
		
		$techs_available = $maximum_tech_count-$developed_tech_count;

		return min($techs_available, $undeveloped_tech_count);
		}
}

#----------------------------------------------------------------------------------------------------------------------#

function playerLeftTeamGame($series, &$game, $player, $empire)
{
	// Reset the player's explored HW to the pre-join values.
	sc_mysql_query('UPDATE explored SET empire = "'.mysql_real_escape_string($player['team_spot']).
	               '" WHERE game_id = '.((int)$game['id']).' AND empire = "'.
	               mysql_real_escape_string($player['name']).'"');

	$values = array();
	$values[] = 'name = "'.mysql_real_escape_string($player['team_spot']).'"';
	$values[] = 'owner = "'.mysql_real_escape_string($player['team_spot']).'"';
	$values[] = 'homeworld = "'.mysql_real_escape_string($player['team_spot']).'"';

	sc_mysql_query('UPDATE systems SET '.implode(',', $values).' WHERE game_id = '.$game['id'].' AND homeworld = "'.$player['name'].'"');
	
 //cjp 20070402 $empire was blank when sendempiremessage was called.
	//$empire = getEmpire($vars['name']); //did not work
	//$empire = $vars['empire_data']; //did not work, now passed in as parameter jrc 20130809


	sendempiremessage($empire, 
					'Fine, flee from '.
					'<span style="color: red;">'.
					$series['name'].' '.$player['game_number'].
					'</span> '.
					'like the coward you are!');
		
	// Delete the player's messages from this game, if any.
	sc_mysql_query('DELETE FROM messages WHERE player_id = '.((int)$player['id']));

	// Remove joining record from history.
	// We wipe everything for this player since there can't be anything else (the game hasn't started).
	sc_mysql_query('DELETE FROM history WHERE empire = "'.mysql_real_escape_string($player['name']).'"');
	
	sc_mysql_query('DELETE FROM players WHERE id = '.((int)$player['id']));
	sc_mysql_query('DELETE FROM ships WHERE player_id = '.((int)$player['id']));
	sc_mysql_query('DELETE FROM fleets WHERE player_id = '.((int)$player['id']));
	sc_mysql_query('DELETE FROM diplomacies WHERE game_id = '.((int)$game['id']).' AND empire = "'.
	               mysql_real_escape_string($player['name']).'"');

	// Decrement the number of players; this player is leaving the game.
	sc_mysql_query('UPDATE games SET player_count = (player_count-1) WHERE id = '.((int)$game['id']));

	$game['player_count'] -= 1;
}

#----------------------------------------------------------------------------------------------------------------------#

function sendPlayerMissive($player, $recipient_id, $recipients, $type, $message)
{
	global $mysqli;
	
	$values = array();
	$values[] = 'time = '.time();
	$values[] = 'sender = "'.$mysqli->real_escape_string($player['name']).'"';
	
	if ($recipients)
		$values[] = 'recipient = "'.$recipients.'"';

	$values[] = 'player_id = '.((int)$recipient_id);
	$values[] = 'text = "'.addslashes($message).'"';
	$values[] = 'type = "'.$mysqli->real_escape_string($type).'"';
	
	sc_mysql_query('INSERT INTO messages SET '.implode(',', $values));

	return true;
}

#----------------------------------------------------------------------------------------------------------------------#

function sendGameMessage($player, $message)
{
	return sendPlayerMissive($player, $player['id'], '', 'game_message', $message);
}
?>
