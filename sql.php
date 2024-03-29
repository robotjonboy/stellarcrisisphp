<?php
#--------------------------------------------------------------------------------------------------------------------#
# Function to make MySQL queries and attempt to trap and correct some key errors
#

function sc_query($query, $fileinfo = 'unknown*unknown', $ignored_errors = array())
{
	global $mysqli;
	
	// We'll loop endlessly here, until we either fully process an error or get a valid MySQL query result.
	while (true)
		{
		// Attempt, or re-attempt the query.
		if ($result = $mysqli->query($query))
			return $result;
		
		// If we get here, we have an SQL error.
		// Our error handler will take care of reporting the snag, but we need
		// to check a few others things by ourselves.
		$error_string = $mysqli->error;
		$error_number = $mysqli->errno;
		
		// First, attempt a rollback to cleanup what was done.
		$rollback_status = $mysqli->query('ROLLBACK');

		// If we have a deadlock, we'll re-issue the query after a few seconds.
		# 1205: "Lock wait timeout exceeded; Try restarting transaction"
		# 1213: "Deadlock found when trying to get lock; Try restarting transaction"
		if ($error_number == 1205 or $error_number == 1213)	sleep(5);
		
		// If MySQL has gone away, or the connection was lost, wait a few seconds
		// and see if it comes back. We probably should trap all the 2000 range errors like this.
		# 2006:	"MySQL server has gone away"
		# 2013:	"Lost connection to MySQL server during query"
		else if ($error_number == 2006 or $error_number == 2013) sleep(2);

		else
			{
			// Report the error and die.
			error_log($error_number.' - '.$error_string."\n\n".$query);
			trigger_error($error_number.' - '.$error_string."\n\n".$query);
			sqlError($rollback_status);
			}
		}
}

function sc_fetch_assoc($result) {
	return $result->fetch_assoc();
}

function sc_result($result, $number, $field=0) {
	mysqli_data_seek($result, $number);
	$row = mysqli_fetch_array($result);
	return $row[$field];
}

#--------------------------------------------------------------------------------------------------------------------#
# Returns whether or not the player has explored a planet.
#

function explored($player, $location)
{
	global $mysqli;
	
	$conditions = array();
	$conditions[] = 'player_id = '.((int)$player['id']);
	$conditions[] = 'coordinates = "'.$mysqli->real_escape_string($location).'"';

	$select = sc_query('SELECT id FROM explored WHERE '.implode(' AND ', $conditions));
	return $select->num_rows;
}

#--------------------------------------------------------------------------------------------------------------------#
# Returns whether or not a ship exists.
#

function shipExists($ship_id)
{
	$select = sc_query('SELECT id FROM ships WHERE id = '.((int)$ship_id));
	return $select->num_rows;
}

#--------------------------------------------------------------------------------------------------------------------#
# Returns a player's info, retrieved by his name.
# Players eliminated in team games have their team number negated so we can process them at the end of the game.
# We exclude them in this query; they are no longer considered in the game.
#

function getPlayer($game_id, $name)
{
	global $mysqli;
	$select = sc_query('SELECT * FROM players WHERE game_id = '.((int)$game_id).' AND name = "'.
	          $mysqli->real_escape_string($name).'" and team >= 0');
	return $select->fetch_assoc();
}

#--------------------------------------------------------------------------------------------------------------------#
# Returns a player record, retrieved by its ID.
#

function getPlayerByID($id)
{
	$select = sc_query('SELECT * FROM players WHERE id = '.((int)$id));
	return $select->fetch_assoc();
}

#--------------------------------------------------------------------------------------------------------------------#
# Returns an explored planet record, retrieved by its ID.

function getExploredByID($id)
{
	$select = sc_query('SELECT * FROM explored WHERE id = '.((int)$id));
	return $select->fetch_assoc();
}

#--------------------------------------------------------------------------------------------------------------------#
# Returns diplomatic info between two players.
#

function getDiplomacyWithOpponent($game_id, $name, $opponent)
{
	global $mysqli;
	$conditions = array();
	$conditions[] = 'game_id = '.((int)$game_id);
	$conditions[] = 'empire = "'.$mysqli->real_escape_string($name).'"';
	$conditions[] = 'opponent = "'.$mysqli->real_escape_string($opponent).'"';

	$select = sc_query('SELECT * FROM diplomacies WHERE '.implode(' AND ', $conditions));
	$result = $select->fetch_assoc();

	return $result;
}

#--------------------------------------------------------------------------------------------------------------------#
# Returns an empire record.
#

function getEmpire($name)
{
	global $mysqli;
	$select = sc_query('SELECT * FROM empires WHERE name = "'.$mysqli->real_escape_string($name).'"');
	return $select->fetch_assoc();
}

#--------------------------------------------------------------------------------------------------------------------#
# Returns an empire record, retrieved by its ID.
#

function getEmpireByID($id)
{
	$select = sc_query('SELECT * FROM empires WHERE id = '.((int)$id));
	return $select->fetch_assoc();
}

#----------------------------------------------------------------------------------------------------------------------#
# Returns a series record, retrieved by its ID.
#

function getSeries($id)
{
	$select = sc_query('SELECT * FROM series WHERE id = '.((int)$id));

	$series = $select->fetch_assoc();

	if ($series['game_type'] == 'sc3') {
		$result = sc_query('select * from series_ship_type_options where series_id = ' . $series['id']);

		$series['ship_type_options'] = [];

		while ($line = $result->fetch_assoc()) {
			$series['ship_type_options'][$line['ship_type']] = $line;
		}
	}
  
  return $series;
}

#--------------------------------------------------------------------------------------------------------------------#
# Returns a series' info, retrieved by its name.
#

function getSeriesByName($name)
{
	global $mysqli;
	
	$select = sc_query('SELECT * FROM series WHERE name = "'.$mysqli->real_escape_string($name).'"');

	$series = $select->fetch_assoc();

	if ($series['game_type'] == 'sc3') {
		$result = sc_query('select * from series_ship_type_options where series_id = ' . $series['id']);

		$series['ship_type_options'] = [];

		while ($line = $result->fetch_assoc()) {
			$series['ship_type_options'][$line['ship_type']] = $line;
		}
	}
    
	return $series;
}

#--------------------------------------------------------------------------------------------------------------------#
# Returns a game record, by its series ID and game number.
#

function getGame($series_id, $game_number)
{
	$select = sc_query('SELECT * FROM games WHERE series_id = "'.((int)$series_id).
	                         '" AND game_number = '.((int)$game_number));

	$game = $select->fetch_assoc();

	if ($game['game_type'] == 'sc3') {
		$result = sc_query('select * from game_ship_type_options where game_id = ' . $game['id']);
	
		$game['ship_type_options'] = [];

		while ($line = $result->fetch_assoc()) {
			$game['ship_type_options'][$line['ship_type']] = $line;
		}
	}

  return $game;
}

#--------------------------------------------------------------------------------------------------------------------#
# Returns a game record, retrieved by its ID.
#

function getGameByID($game_id)
{
	$select = sc_query('SELECT * FROM games WHERE id = '.((int)$game_id));

  $game = $select->fetch_assoc();

  if ($game && $game['game_type'] == 'sc3') {
    $result = sc_query('select * from game_ship_type_options where game_id = ' . $game['id']);

    $game['ship_type_options'] = [];

    while ($line = $result->fetch_assoc()) {
      $game['ship_type_options'][$line['ship_type']] = $line;
    }
  }
  
  return $game;
}

#--------------------------------------------------------------------------------------------------------------------#
# Returns a tournament record, retrieved by its ID.
#

function getTourneyByID($tourney_id)
{
	$select = sc_query('SELECT * FROM tournament WHERE id = '.((int)$tourney_id));
    return $select->fetch_assoc();
}

#--------------------------------------------------------------------------------------------------------------------#
# Returns a ship record, retrieved by its ID.
#

function getShipByID($id)
{
	$select = sc_query('SELECT * FROM ships WHERE id = '.((int)$id));
	return $select->fetch_assoc();
}

#--------------------------------------------------------------------------------------------------------------------#
# Returns a fleet record, retrieved by its ID.
#

function getFleetByID($id)
{
	$select = sc_query('SELECT * FROM fleets WHERE id = '.((int)$id));
	return $select->fetch_assoc();
}

#--------------------------------------------------------------------------------------------------------------------#
# Returns a system's info.
# We add a the "system_active = 1" condition to block out inactive systems in pre-built maps.
# This is necessary in order to prevent neering into them!
#

function getSystem($game_id, $coordinates)
{
	global $mysqli;
	
	$conditions = array();
	$conditions[] = 'game_id = '.((int)$game_id);
	$conditions[] = 'coordinates = "'.$mysqli->real_escape_string($coordinates).'"';
	$conditions[] = 'system_active = "1"';

	$select = sc_query('SELECT * FROM systems WHERE '.implode(' AND ', $conditions));
	return $select->fetch_assoc();
}

#----------------------------------------------------------------------------------------------------------------------#
# Returns a system record, retrieved by its ID.
#

function getSystemByID($id)
{
	$select = sc_query('SELECT * FROM systems WHERE id = '.((int)$id));
	return $select->fetch_assoc();
}

#--------------------------------------------------------------------------------------------------------------------#
# Wipes out a game's info, by game ID.
#

function eraseGame($game_id)
{
	$game_id = (int) $game_id;
	
	$tables = 'scouting_reports INNER JOIN players ON scouting_reports.player_id = players.id';
	sc_query('DELETE scouting_reports.* FROM '.$tables.' WHERE players.game_id = '.$game_id);

	$tables = 'messages INNER JOIN players ON messages.player_id = players.id';
	sc_query('DELETE messages.* FROM '.$tables.' WHERE players.game_id = '.$game_id);

	sc_query('DELETE FROM diplomacies WHERE game_id = '.$game_id);
	sc_query('DELETE FROM explored WHERE game_id = '.$game_id);
	sc_query('DELETE FROM fleets WHERE game_id = '.$game_id);
	sc_query('DELETE FROM game_ship_type_options where game_id = '.$game_id);
	sc_query('DELETE FROM games WHERE id = '.$game_id);
	sc_query('DELETE FROM history WHERE game_id = '.$game_id);
	sc_query('DELETE FROM invitations WHERE game_id = '.$game_id);
	sc_query('DELETE FROM players WHERE game_id = '.$game_id);
	sc_query('DELETE FROM ships WHERE game_id = '.$game_id);
	sc_query('DELETE FROM systems WHERE game_id = '.$game_id);
}
?>
