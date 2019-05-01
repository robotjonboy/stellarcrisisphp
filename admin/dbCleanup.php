<?php
function dbClean($vars)
{
	standardHeader('Database Cleanup', $vars['empire_data']);
echo "
<div class=pageTitle>Database Cleanup</div>
<div>
<input type=hidden name=\"name\" value=\"" . $vars['name'] . "\">
<input type=hidden name=\"pass\" value=\"" . $vars['pass'] . "\">
<input type=hidden name=\"section\" value=\"admin\">
<input type=hidden name=\"page\" value=\"dbClean\">";

	echo drawButtons($vars['empire_data']).serverTime().onlinePlayers().empireMissive($vars['empire_data']);
	echo "<img class=spacerule src=\"images/spacerule.jpg\">";

	if (isset($vars['clean']))
		{
		$execute = (isset($vars['execute']));
	
		switch ($vars['cleanUpAction'])
			{
			case 'Explored':			$output = clean_explored($execute);			break;
			case 'Fleets':				$output = clean_fleets($execute);			break;
			case 'Games':				$output = clean_games($execute);			break;
			case 'History':				$output = clean_history($execute);			break;
			case 'Jumps':				$output = clean_jumps($execute);			break;
			case 'Messages':			$output = clean_messages($execute);			break;
			case 'Players':				$output = clean_players($execute);			break;
			case 'Scouting Reports':	$output = clean_scoutingReports($execute);	break;
			case 'Ships':				$output = clean_ships($execute);			break;
			case 'Systems':				$output = clean_systems($execute);			break;
			}
		}
echo "
<div style=\"text-align: center;\">Cleanup to perform:
	<select name=\"cleanUpAction\">
		<option " . ($vars['cleanUpAction'] == 'Explored' ? ' selected' : '') . ">Explored
		<option " . ($vars['cleanUpAction'] == 'Fleets' ? ' selected' : '') . ">Fleets
		<option " . ($vars['cleanUpAction'] == 'Games' ? ' selected' : '') . ">Games
		<option " . ($vars['cleanUpAction'] == 'History' ? ' selected' : '') . ">History
		<option " . ($vars['cleanUpAction'] == 'Jumps' ? ' selected' : '') . ">Jumps
		<option " . ($vars['cleanUpAction'] == 'Messages' ? ' selected' : '') . ">Messages
		<option " . ($vars['cleanUpAction'] == 'Players' ? ' selected' : '') . ">Players
		<option " . ($vars['cleanUpAction'] == 'Scouting Reports' ? ' selected' : '') . ">Scouting Reports
		<option " . ($vars['cleanUpAction'] == 'Ships' ? ' selected' : '') . ">Ships
		<option " . ($vars['cleanUpAction'] == 'Systems' ? ' selected' : '') . ">Systems
	</select>
</div>

<div style=\"text-align: center; margin-top: 10pt;\">
	<input type=checkbox name=\"execute\">&nbsp;Correct discrepancies
</div>

<div style=\"text-align: center; margin-top: 10pt;\">
	<input type=submit name=\"clean\" value=\"Clean\">&nbsp;<input type=submit name=\"cancel\" value=\"Cancel\">
</div>";

	if ($output)
		{
echo "
<blockquote style=\"color: white; font-size: 10pt; text-align: left;\">
	<pre>" . implode("\n", $output) . "</pre>
</blockquote>";

		}

	footer();
}

#--------------------------------------------------------------------------------------------------------------------#

function dbClean_processing($vars)
{
	if (isset($vars['cancel']))
		{
		sendEmpireMessage($vars['empire_data'], 'Database cleanup cancelled.');
		return mainPage_admin($vars);
		}

	dbClean($vars);
}

#--------------------------------------------------------------------------------------------------------------------#
# This function will make sure that no jumps field has trailing spaces in it. Those can mess up history files and
# possibly lead to weird in-game behavior (i.e., black holes).
#

function clean_jumps($execute)
{
	$output = array();
	
	$select = sc_mysql_query('SELECT COUNT(*) FROM systems WHERE jumps REGEXP "^ +| +$"');
	if ($count = mysql_result($select, 0, 0))
		{
		$output[] = $count.' systems found with trailing spaces in jump list.'.($execute ? ' Trimmed.' : ' Will trim.');

		if ($execute)
			sc_mysql_query('UPDATE systems SET jumps = TRIM(jumps)');
		}
	else
		$output[] = 'No trailing spaces found in system jumps.';
		
	return $output;
}

#--------------------------------------------------------------------------------------------------------------------#

function clean_explored($execute)
{
	$output = array();

	//-------------------------------------------------------------------------------------------
	// Purge explored records that shouldn't be there (erased games).

	$tables = 'explored LEFT JOIN games ON explored.game_id = games.id';
	$select = sc_mysql_query('SELECT COUNT(*) FROM '.$tables.' WHERE ISNULL(games.id)');
	if ($count = mysql_result($select, 0, 0))
		{
		$output[] = $count.' invalid explored record(s) found.'.($execute ? ' Deleted.' : ' Will delete them.');
		
		if ($execute)
			sc_mysql_query('DELETE explored.* FROM '.$tables.' WHERE ISNULL(games.id)');
		}
	else
		$output[] = 'No invalid explored records found.';
	
	//-------------------------------------------------------------------------------------------
	// Ensure that every explored record has a player ID attached to it. We skip records that start and 
	// end with '=' since those are placeholders for players in games with prebuilt maps.
	
	$tables = 'explored INNER JOIN players ON explored.game_id = players.game_id AND explored.empire = players.name';

	$select = sc_mysql_query('SELECT COUNT(*) FROM '.$tables.' WHERE player_id = 0 AND NOT empire REGEXP "^=.*=$"');
	if ($count = mysql_result($select, 0, 0))
		{
		$output[] = $count.' explored record(s) with no player ID set.'.($execute ? ' Corrected.' : ' Will set player ID field.');
				
		if ($execute)
			sc_mysql_query('UPDATE '.$tables.' SET explored.player_id = players.id WHERE explored.player_id = 0 AND NOT explored.empire REGEXP "^=.*=$"');
		}
	else
		$output[] = 'No explored records without a player ID.';
				
	//-------------------------------------------------------------------------------------------
	// Ensure that every homeworld is explored.
	
	$tables = 'systems s LEFT JOIN explored e ON s.game_id = e.game_id AND s.coordinates = e.coordinates AND s.homeworld = e.empire';
	$select = sc_mysql_query('SELECT s.* FROM '.$tables.' WHERE s.homeworld <> "" AND ISNULL(e.id)');
	if ($count = mysql_num_rows($select))
		{
		$output[] = $count.' unexplored homeworld(s) found.'.($execute ? ' Corrected.' : ' Will create exploration records.');
		
		if ($execute)
			{
			while ($row = mysql_fetch_array($select))
				{
				$player = getPlayer($row['game_id'], $row['homeworld']);

				$values = array();
				$values[] = 'series_id = '.$row['series_id'];
				$values[] = 'game_number = '.$row['game_number'];
				$values[] = 'game_id = '.$row['game_id'];
				$values[] = 'empire = "'.$row['homeworld'].'"';
				$values[] = 'player_id = "'.$player['id'].'"';
				$values[] = 'coordinates = "'.$row['coordinates'].'"';
				
				sc_mysql_query('INSERT INTO explored SET '.implode(',', $values));
				}
			}
		}
	else
		$output[] = 'No unexplored homeworlds found.';

	//-------------------------------------------------------------------------------------------
	// Ensure that all colonized planets are explored by their owner.

	$tables = 'systems s LEFT JOIN explored e ON s.game_id = e.game_id AND s.coordinates = e.coordinates AND s.owner = e.empire';
	$select = sc_mysql_query('SELECT s.*, e.player_id FROM '.$tables.' WHERE s.owner <> "" AND ISNULL(e.id)');
	if ($count = mysql_num_rows($select))
		{
		$output[] = $count.' unexplored colonized planet(s) found.'.($execute ? ' Corrected.' : ' Will create exploration records.');
		
		if ($execute)
			{
			while ($row = mysql_fetch_array($select))
				{
				$values = array();
				$values[] = 'series_id = '.$row['series_id'];
				$values[] = 'game_number = '.$row['game_number'];
				$values[] = 'game_id = '.$row['game_id'];
				$values[] = 'empire = "'.$row['owner'].'"';
				$values[] = 'player_id = "'.$row['player_id'].'"';
				$values[] = 'coordinates = "'.$row['coordinates'].'"';
				
				sc_mysql_query('INSERT INTO explored SET '.implode(',', $values));
				}
			}
		}
	else
		$output[] = 'No unexplored colonized planets found.';
		
	return $output;
}

#--------------------------------------------------------------------------------------------------------------------#

function clean_systems($execute)
{
	$output = array();
	
	$tables = 'systems LEFT JOIN games ON systems.game_id = games.id';
	$select = sc_mysql_query('SELECT COUNT(*) FROM '.$tables.' WHERE ISNULL(games.id)');
	if ($count = mysql_result($select, 0, 0))
		{
		$output[] = $count.' system(s) found to not be in any game.'.($execute ? ' Deleted.' : ' Will delete them.');
		
		if ($execute)
			sc_mysql_query('DELETE systems.* FROM '.$tables.' WHERE ISNULL(games.id)');
		}
	else
		$output[] = 'No invalid systems found.';
		
	return $output;
}

#--------------------------------------------------------------------------------------------------------------------#

function clean_messages($execute)
{
	$output = array();

	//-------------------------------------------------------------------------------------------
	// Delete messages for players that don't exist anymore.

	$tables = 'messages LEFT JOIN players ON messages.player_id = players.id';
	$select = sc_mysql_query('SELECT COUNT(*) FROM '.$tables.' WHERE ISNULL(players.id) AND messages.player_id <> 0');
	if ($count = mysql_result($select, 0, 0))
		{
		$output[] = $count.' message(s) found for non-existant players.'.($execute ? ' Deleted.' : ' Will delete them.');
		
		if ($execute)
			sc_mysql_query('DELETE messages.* FROM '.$tables.' WHERE ISNULL(players.id) AND messages.player_id <> 0');
		}
	else
		$output[] = 'No messages for non-existant players found.';

	//-------------------------------------------------------------------------------------------
	// Delete messages that are for no one.

	$conditions = array();
	$conditions[] = 'player_id = 0';
	$conditions[] = 'empire_id = 0';
	$conditions[] = 'type <> "motd"';
	$conditions[] = 'type <> "privacy"';
	$conditions[] = 'type <> "policy"';
	$conditions[] = 'type <> "tos"';
	$conditions[] = 'type <> "news"';

	$select = sc_mysql_query('SELECT COUNT(*) FROM messages WHERE '.implode(' AND ', $conditions));
	if ($count = mysql_result($select, 0, 0))
		{
		$output[] = $count.' message(s) found with no recipient.'.($execute ? ' Deleted.' : ' Will delete them.');
		
		if ($execute)
			sc_mysql_query('DELETE FROM messages WHERE '.implode(' AND ', $conditions));
		}
	else
		$output[] = 'No messages with no recipient found.';

	//-------------------------------------------------------------------------------------------
	// Delete old non-games messages.

	$conditions = array();
	$conditions[] = '(UNIX_TIMESTAMP()-time) > (86400*30*6)';
	$conditions[] = 'ISNULL(player_id)';
	$conditions[] = 'type <> "motd"';
	$conditions[] = 'type <> "privacy"';
	$conditions[] = 'type <> "policy"';
	$conditions[] = 'type <> "tos"';
	$conditions[] = 'type <> "news"';

	$select = sc_mysql_query('SELECT COUNT(*) FROM messages WHERE '.implode(' AND ', $conditions));
	if ($count = mysql_result($select, 0, 0))
		{
		$output[] = $count.' non-game message(s) found older than six months.'.($execute ? ' Deleted.' : ' Will delete them.');
		
		if ($execute)
			sc_mysql_query('DELETE FROM messages WHERE '.implode(' AND ', $conditions));
		}
	else
		$output[] = 'No old messages found.';
		
	return $output;
}

#--------------------------------------------------------------------------------------------------------------------#
# Scouting reports for a planet should be deleted when it is explored. This function cleans up any left-over records.
#

function clean_scoutingReports($execute)
{
	$output = array();
	
	//-------------------------------------------------------------------------------------------

	$tables = 'scouting_reports s INNER JOIN explored e ON s.player_id = e.player_id AND s.coordinates = e.coordinates';
	$select = sc_mysql_query('SELECT COUNT(*) FROM '.$tables);
	if ($count = mysql_result($select, 0, 0))
		{
		$output[] = $count.' redundant scouting report(s) found.'.($execute ? ' Deleted.' : ' Will delete them.');
		
		if ($execute)
			sc_mysql_query('DELETE s.* FROM '.$tables);
		}
	else
		$output[] = 'No redundant scouting reports found.';
	
	//-------------------------------------------------------------------------------------------

	$tables = 'scouting_reports LEFT JOIN players ON scouting_reports.player_id = players.id ';
	$select = sc_mysql_query('SELECT COUNT(*) FROM '.$tables.' WHERE ISNULL(players.id)');
	if ($count = mysql_result($select, 0, 0))
		{
		$output[] = $count.' invalid scouting reports found.'.($execute ? ' Deleted.' : ' Will delete them.');
		
		if ($execute)
			sc_mysql_query('DELETE scouting_reports.* FROM '.$tables.' WHERE ISNULL(players.id)');
		}
	else
		$output[] = 'No invalid scouting reports found.';
		
	return $output;
}

#--------------------------------------------------------------------------------------------------------------------#
# Removes players for which we cannot find a game (invalid player ID).
#

function clean_players($execute)
{
	$output = array();
	
	$tables = 'players LEFT JOIN games on players.game_id = games.id';
	$select = sc_mysql_query('SELECT COUNT(*) FROM '.$tables.' WHERE ISNULL(games.id)');
	if ($count = mysql_result($select, 0, 0))
		{
		$output[] = $count.' non-existant player(s) found.'.($execute ? ' Deleted.' : ' Will delete them.');

		if ($execute)
			sc_mysql_query('DELETE players.* FROM '.$tables.' WHERE ISNULL(games.id)');
		}
	else
		$output[] = 'No non-existant players found.';
		
	return $output;
}

#--------------------------------------------------------------------------------------------------------------------#

function clean_games($execute)
{
	$output = array();

	$tables = 'games LEFT JOIN players ON games.id = players.game_id';
	$select = sc_mysql_query('SELECT COUNT(*) FROM '.$tables.' WHERE ISNULL(players.game_id) AND games.update_count > 0');
	if ($count = mysql_result($select, 0, 0))
		{
		$output[] = $count.' started games found with no players.'.($execute ? ' Deleted.' : ' Will delete them.');

		if ($execute)
			sc_mysql_query('DELETE games.* FROM '.$tables.' WHERE ISNULL(players.game_id) AND games.update_count > 0');
		}
	else
		$output[] = 'No bogus games found.';

	return $output;
}

#--------------------------------------------------------------------------------------------------------------------#

function clean_history($execute)
{
	$output = array();

	$tables = 'history LEFT JOIN games ON history.game_id = games.id';
	$select = sc_mysql_query('SELECT COUNT(*) FROM '.$tables.' WHERE ISNULL(games.id)');
	if ($count = mysql_result($select, 0, 0))
		{
		$output[] = $count.' history record(s) found for non-existant games.'.($execute ? ' Deleted.' : ' Will delete them.');

		if ($execute)
			sc_mysql_query('DELETE history.* FROM '.$tables.' WHERE ISNULL(games.id)');
		}
	else
		$output[] = 'No bogus history records found.';
		
	return $output;
}

#--------------------------------------------------------------------------------------------------------------------#

function clean_fleets($execute)
{
	$output = array();

	$tables = 'ships LEFT JOIN fleets ON ships.fleet_id = fleets.id';
	$select = sc_mysql_query('SELECT COUNT(*) FROM '.$tables.' WHERE ships.fleet_id <> 0 AND ISNULL(fleets.id)');
	if ($count = mysql_result($select, 0, 0))
		{
		$output[] = $count.' ship(s) found to be in a non-existant fleet.'.($execute ? ' Fleet IDs stripped.' : ' Will strip out fleet IDs.');

		if ($execute)
			sc_mysql_query('UPDATE '.$tables.' SET ships.fleet_id = 0 WHERE ships.fleet_id <> 0 AND ISNULL(fleets.id)');
		}
	else
		$output[] = 'No non-existant fleets found.';
	
	//-------------------------------------------------------------------------------------------

	$tables = 'ships INNER JOIN fleets ON ships.fleet_id = fleets.id';
	$select = sc_mysql_query('SELECT COUNT(*) FROM '.$tables.' WHERE fleets.location <> ships.location');
	if ($count = mysql_result($select, 0, 0))
		{
		$output[] = $count.' errant ship(s) found.'.($execute ? ' Fleet IDs stripped.' : ' Will strip out fleet IDs.');

		if ($execute)
			sc_mysql_query('UPDATE '.$tables.' SET fleet_id = 0, orders = "standby" WHERE fleets.location <> ships.location');
		}
	else
		$output[] = 'No errant ships found.';

	//-------------------------------------------------------------------------------------------

	$tables = 'fleets LEFT JOIN players ON fleets.player_id = players.id ';
	$select = sc_mysql_query('SELECT COUNT(*) FROM '.$tables.' WHERE ISNULL(players.id)');
	if ($count = mysql_result($select, 0, 0))
		{
		$output[] = $count.' invalid fleet(s) found.'.($execute ? ' Deleted.' : ' Will delete them.');
		
		if ($execute)
			sc_mysql_query('DELETE fleets.* FROM '.$tables.' WHERE ISNULL(players.id)');
		}
	else
		$output[] = 'No invalid fleets found.';
		
	return $output;
}

#--------------------------------------------------------------------------------------------------------------------#

function clean_ships($execute)
{
	$output = array();

	$select = sc_mysql_query('SELECT COUNT(*) FROM ships WHERE player_id = 0');
	if ($count = mysql_result($select, 0, 0))
		{
		$output[] = $count.' ship(s) found with no player ID.'.($execute ? ' Corrected.' : ' Will set player ID field.');

		if ($execute)
			{
			$tables = 'ships INNER JOIN players ON ships.game_id = players.game_id AND ships.owner = players.name';
			sc_mysql_query('UPDATE '.$tables.' SET ships.player_id = players.id WHERE player_id = 0');
			}
		}
	else
		$output[] = 'No incomplete ship records found.';
		
	return $output;
}
?>