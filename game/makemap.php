<?php
#-----------------------------------------------------------------------------------------------------------------------------------------#
# General wrapper function to process joining a game, whatever its type.
#

function joinGame($vars, $team = 0, $spawngame = true, $returninfoscreen = true)
{
	global $mysqli;
	$series = $vars['series_data'];
	$game = $vars['game_data'];

	$game['player_count'] += 1;

	$game_values = array();
	$game_values[] = 'player_count = '.$game['player_count'];

	// If this is the first player, set the game start parameters.
	if ($game['player_count'] == 1)
		{
		// Put started record in history.
		$history_values = array();
		$history_values[] = 'game_id = '.((int)$game['id']);
		$history_values[] = 'update_no = '.$game['update_count'];
		$history_values[] = 'coordinates = ""';
		$history_values[] = 'empire = "'.$mysqli->real_escape_string($vars['name']).'"';
		$history_values[] = 'event = "started"';
		$history_values[] = 'info = '.time();

		sc_query('INSERT INTO history SET '.implode(',', $history_values));

		$game['created_by'] = $vars['name'];
		$game['created_at'] = time();

		$game_values[] = 'created_by = "'.$game['created_by'].'"';
		$game_values[] = 'created_at = '.$game['created_at'];

		// Start a new game immediately; gameList() will manage what is possible for launching it.
		if ($spawngame) spawnGame($series['name']);
		}
	
	// store the update	
	sc_query('UPDATE games SET '.implode(',', $game_values).' WHERE id = '.((int)$game['id']));

	if ($series['team_game'])
		joinTeamGame($vars, $series, $game, $team);
	else
		joinRegularGame($vars, $series, $game);

	// Reload the updated $game array.
	$vars['game_data'] = $game;
		
	// If the game is full, set the closed flag and remove unanswered invitations.
	if ($game['player_count'] == $series['max_players'])
		{
		sc_query('UPDATE games SET closed = "1" WHERE id = '.((int)$game['id']));

		$game['closed'] = 1;

		// If it's a bridier game, finish setting up the results entry.
		if ($game['bridier'] >= 0)
			{
			$bridier_query = sc_query('SELECT * FROM bridier WHERE game_id = '.((int)$game['id']));
			$bdata = mysql_fetch_array($bridier_query);
			
			$empire = getEmpire($vars['name']);
			$opponent = getEmpire($bdata['empire1']);
			
			$fields = array();
			$fields[] = 'starting_rank1 = '.$opponent['bridier_rank'];
			$fields[] = 'starting_index1 = '.$opponent['bridier_index'];
			$fields[] = 'empire2 = "'.$vars['name'].'"';
			$fields[] = 'starting_rank2 = '.$empire['bridier_rank'];
			$fields[] = 'starting_index2 = '.$empire['bridier_index'];
			$fields[] = 'start_time = '.time();
			
			sc_query('UPDATE bridier SET '.implode(',',$fields).' WHERE game_id = '.$game['id']);
			}

		// Remove all invitations.
		sc_query('DELETE FROM invitations WHERE game_id = '.((int)$game['id']));
		
		// Remove any unused systems.
		sc_query('DELETE FROM systems WHERE game_id = '.((int)$game['id']).' AND system_active = "0"');
		}

	// Now get up to date player data for game history and passing into infoScreen
	$vars['player_data'] = $player = getPlayer($game['id'], $vars['name']);
	
	// Add player join to history.
	// First, we need to get homeworld location so we can put it in the history record.
	$select = sc_query('SELECT coordinates FROM systems WHERE game_id = '.((int)$game['id']).
	                         ' AND homeworld = "'.$mysqli->real_escape_string($vars['name']).'"');
	$homeworld = $select->fetch_assoc();
	
	$values = array();
	$values[] = 'game_id = '.((int)$game['id']);
	$values[] = 'update_no = '.((int)$game['update_count']);
	$values[] = 'coordinates = "'.$mysqli->real_escape_string($homeworld['coordinates']).'"';
	$values[] = 'empire = "'.$mysqli->real_escape_string($vars['name']).'"';
	$values[] = 'event = "joined"';
	
	if ($player['team'])
		$values[] = 'info = "Team '.$player['team'].'"';
	
	sc_query('INSERT INTO history SET '.implode(',', $values));

	$vars['series_data'] = $series;
	$vars['game_data'] = $game;

	sendGameMessage($player, 'Welcome to '.$series['name'].' '.$game['game_number'].', '.$player['name'].'.');

	if ($returninfoscreen) return infoScreen($vars);
}

#-----------------------------------------------------------------------------------------------------------------------------------------#
# Regular-game stuff that must happen when a player joins.
#

function joinRegularGame($vars, $series, &$game)
{
	global $mysqli;
	// If this is the second player to join in, set the appropriate update time fields.
	if ($game['player_count'] == 2)
		{
		sc_query('UPDATE games SET last_update ='.time().' WHERE id = '.((int)$game['id']));
		$game['last_update'] = time();
		}

	initPlayer($vars, $series, $game, 0);
	
	switch ($series['map_type'])
		{
		case 'prebuilt':
			if ($game['player_count'] == 1)
				{
				createPrebuiltMap($series, $game);

				$player = rand(1, $series['max_players']);
				fillPlayerPosition( $player, $vars, $series, $game );

				fixPrebuiltLinks($game);
				}
			else
				joinPrebuiltGame($vars, $series, $game);
			break;
		case 'balanced':
			// Balanced maps only supported for 2-player non-team games.
			if ($series['max_players'] == 2)
				{
				if ($game['player_count'] == 1)
					{
					createBalancedMap($series, $game);
					fillPlayerPosition(1, $vars, $series, $game);
					}
				else
					fillPlayerPosition(2, $vars, $series, $game);
				break;
				}
			// if this is not a 2-player game, fall through...
		case 'twisted':
			// Twisted maps only supported for 2-player non-team games.
			if ($series['max_players'] == 2)
				{
				if ($game['player_count'] == 1)
					{
					createTwistedMap($series, $game);
					fillPlayerPosition(1, $vars, $series, $game);
					}
				else
					fillPlayerPosition(2, $vars, $series, $game);
				break;
				}
			// if this is not a 2-player game, fall through...
		case 'mirror':
			// Mirror maps only supported for 2-player non-team games.
			if ($series['max_players'] == 2)
				{
				if ($game['player_count'] == 1)
					{
					createMirrorMap($series, $game);
					fillPlayerPosition(1, $vars, $series, $game);
					}
				else
					fillPlayerPosition(2, $vars, $series, $game);
				break;
				}
			// if this is not a 2-player game, fall through...
		case 'standard':
		default:
			// A new player! Generate his planets.
			generateMapForPlayer($vars['name'], $series, $game);
			
			// And update the new player's explored and systems.
			$player = getPlayer($game['id'], $vars['name']);

			$conditions = array();
			$conditions[] = 'game_id = '.((int)$game['id']);
			$conditions[] = 'empire = "'.$mysqli->real_escape_string($vars['name']).'"';
			sc_query('UPDATE explored SET player_id = '.((int)$player['id']).' WHERE '.implode(' AND ', $conditions), __FILE__.'*'.__LINE__);
			break;
		}
}

#-----------------------------------------------------------------------------------------------------------------------------------------#
# Team-game stuff that must happen when a player joins.
#

function joinTeamGame($vars, $series, &$game, $team)
{
	global $mysqli;
	
	$empire = $vars['empire_data'];
	
	// Figure out which team player should be on.
	$team_list = array(1 => array(), 2 => array());

	if ($game['player_count'] == 1)
		{
		// First player in-- create the whole map.
		createTwistedMap($series, $game);
		$team = 1;
		}
	else
		{
		// Make a list of who is in the game already.
	   	$player_query = sc_query('SELECT * FROM players WHERE game_id = '.((int)$game['id']),
	   	                               __FILE__.'*'.__LINE__);

		while ($player = mysql_fetch_array($player_query))
			$team_list[$player['team']][] = $player['name'];
		}

	// If not already assigned, assign player to the team with the least number of players.
	if ($team == 0)
		$team = (count($team_list[2]) < count($team_list[1]) ? 2 : 1);
	
	if (count($team_list[$team]) >= $series['max_players']/2)
		{
		sendEmpireMessage($empire, 'The team you were invited to join is full.');
		return gameList($vars);
		}

	// We use the systems table to find out what still available by looking at homeworlds
	
	$team_openings = array(1 => array(), 2 => array());

	$select = sc_query('SELECT owner, player_number FROM systems WHERE game_id = '.$game['id'].' AND owner <> ""');
	while ($system = $select->fetch_assoc())
		{
		if (!in_array($system['owner'], $team_list[1]) and !in_array($system['owner'], $team_list[2]))
			{
			$n = $system['player_number'];

			if ($n/2 != floor($n/2))
				$team_openings[1][] = $n;
			else
				$team_openings[2][] = $n;
			}
		}

	$player_num = $team_openings[$team][array_rand($team_openings[$team])];
	$player_slot = '=Player '.$player_num.'=';

	initPlayer($vars, $series, $game, $team, $player_slot);
	
	// We've picked the slot, now put the player into the game.
	fillPlayerPosition($player_num, $vars, $series, $game, $team);

	// Initialize the team diplomacy offer for this player to WAR.
	$values = array();
	$values[] = 'series_id = '.((int)$series['id']);
	$values[] = 'game_number = '.((int)$game['game_number']);
	$values[] = 'game_id = '.((int)$game['id']);
	$values[] = 'empire = "'.$mysqli->real_escape_string($vars['name']).'"';
	$values[] = 'opponent = "=Team'.($team == 1 ? 2 : 1).'="';
	$values[] = 'offer = "2"';
	$values[] = 'status = "2"';

	sc_query('INSERT INTO diplomacies SET '.implode(',', $values));
	
	// If the game is full, start the timer, close the game 
	if ($game['player_count'] == $series['max_players'])
		{
		// Team games don't start until full.
		sc_query('UPDATE games SET last_update = '.time().' WHERE id = '.((int)$game['id']));
		
		// Set local copies of variables so that info screen comes up correctly!
		$game['last_update'] = time();
		}
}

#-----------------------------------------------------------------------------------------------------------------------------------------#
# Prebuilt-map stuff that must happen when a player joins.
#

function joinPrebuiltGame($vars, $series, $game)
{
	// Load the entire map for this game
	$map = array();
	$select = sc_query('SELECT * FROM systems WHERE game_id = '.((int)$game['id']), __FILE__.'*'.__LINE__);
	while ($sys = $select->fetch_assoc()) $map[$sys['coordinates']] = $sys;

	// Look for inactive planets next to currently active ones-- these are the player positions we can activate now.
	$spots_open = array();
	foreach (array_keys($map) as $coord)
		if (!$map[$coord]['system_active'])
			{
			// We have an inactive system, now look for a neighboring active one
			// (we have to look from inactive to active because the jumps going the other way
			// will have been removed).

			$jumps = explode(' ', $map[$coord]['jumps']);

			foreach ($jumps as $jump)
				if ($map[$jump]['system_active'])
					if (!in_array($map[$coord]['player_number'], $spots_open))
						$spots_open[] = $map[$coord]['player_number'];
			}
			
	shuffle($spots_open);
	$player = current( $spots_open );
	
	fillPlayerPosition($player, $vars, $series, $game);
	
	$conditions = array();
	$conditions[] = 'game_id = '.((int)$game['id']);
	$conditions[] = 'player_number = '.((int)$player);

	$query = 'UPDATE systems SET system_active = "1" WHERE '.implode(' AND ',$conditions);
	sc_query($query, __FILE__.'*'.__LINE__);
	
	fixPrebuiltLinks($game);
}

#-----------------------------------------------------------------------------------------------------------------------------------------#
# Initialize a player to his spot in a prebuilt map.
#

function fillPlayerPosition($player_slot, $vars, $series, $game, $team = 0)
{
	global $mysqli;
	
	// Retrieve the player record to get the id...
	$player = getPlayer($game['id'], $vars['name']);

	// ...and update the new player's explored and systems.
	$values = array();
	$values[] = 'empire = "'.$mysqli->real_escape_string($vars['name']).'"';
	$values[] = 'player_id = '.((int)$player['id']);

	$conditions = array();
	$conditions[] = 'game_id = '.((int)$game['id']);
	$conditions[] = 'empire = "=Player '.$player_slot.'="';

	sc_query('UPDATE explored SET '.implode(',', $values).' WHERE '.implode(' AND ', $conditions), __FILE__.'*'.__LINE__);

	$values = array();
	$values[] = 'name = "'.$mysqli->real_escape_string($vars['name']).'"';
	$values[] = 'owner = "'.$mysqli->real_escape_string($vars['name']).'"';
	$values[] = 'homeworld = "'.$mysqli->real_escape_string($vars['name']).'"';

	$conditions = array();
	$conditions[] = 'game_id = '.((int)$game['id']);
	$conditions[] = 'homeworld = "=Player '.$player_slot.'="';

	sc_query('UPDATE systems SET '.implode(',', $values).' WHERE '.implode(' AND ', $conditions), __FILE__.'*'.__LINE__);

	$conditions = array();
	$conditions[] = 'game_id = '.((int)$game['id']);
	$conditions[] = 'player_number = '.((int)$player_slot);

	$query = 'UPDATE systems SET system_active = "1" WHERE '.implode(' AND ', $conditions);
	sc_query($query, __FILE__.'*'.__LINE__);
}

#-----------------------------------------------------------------------------------------------------------------------------------------#
# Removes links to unused planets in a prebuilt map.
#

function fixPrebuiltLinks($game)
{
	$map = array();
	
	// Load the active systems in the map
	$query = sc_query('SELECT * FROM systems WHERE game_id = '.$game['id'].' AND system_active = "1"', __FILE__.'*'.__LINE__);
	while ($sys = $query->fetch_assoc()) $map[$sys['coordinates']] = $sys;
	
	// Go through all the active systems and check for links.
	foreach (array_keys($map) as $coord)
		{
		// Get a jump list for the current system
		$jumps = explode(' ', $map[$coord]['jumps']);

		$new_jumps = array();

		// Check to make sure all the systems in the jump list are active-- and remove inactive ones.
		// Inactive ones that get removed will get added back in when that map section is turned on.
		foreach ($jumps as $jump)
			if (array_key_exists($jump, $map))
				{
				$new_jumps[] = $jump;
				
				// Also make sure that the current system is in the jump list for the system we've confirmed a jump to
				// (this is the part that adds links back in when the map section is turned on).
				if (!in_array($coord, explode(' ',$map[$jump]['jumps'])))
					{
					$map[$jump]['jumps'] .= ' '.$coord;

					$conditions = array();
					$conditions[] = 'game_id = '.$game['id'];
					$conditions[] = 'coordinates = "'.$jump.'"';

					sc_query('UPDATE systems SET jumps = "'.$map[$jump]['jumps'].'" WHERE '.implode(' AND ', $conditions), __FILE__.'*'.__LINE__);
					}
				}
				
		$map[$coord]['jumps'] = implode(' ',$new_jumps);

		$conditions = array();
		$conditions[] = 'game_id = '.$game['id'];
		$conditions[] = 'coordinates = "'.$coord.'"';

		sc_query('UPDATE systems SET jumps = "'.$map[$coord]['jumps'].'" WHERE '.implode(' AND ', $conditions), __FILE__.'*'.__LINE__);
		}
}

#----------------------------------------------------------------------------------------------------------------------#
# Initialize the player's record for this game.
#

function initPlayer($vars, $series, $game, $team, $player_slot = '')
{
	$empire = getEmpire($vars['name']);

	// The player's tech level is by default 1.0, unless there is someone in this game which has a higher tech level.
	// If this is the case, set the player's tech level to the highest tech level of the other players.
	$sql = 'SELECT GREATEST(1.0, MAX(tech_level)) as g FROM players WHERE game_id = '.$game['id'].' AND name <> "'.$vars['name'].'"';
	$select = sc_query($sql);
	$line = $select->fetch_assoc();
	$tech_level = $line['g'];
	
	if (!isset($tech_level))
	{
		$tech_level = 1.0;
	}
	
	$values = array();
	$values[] = 'series_id = '.$series['id'];
	$values[] = 'game_number = '.$game['game_number'];
	$values[] = 'game_id = '.$game['id']; // added 2002/06/20
	$values[] = 'name = "'.$vars['name'].'"';
	$values[] = 'team = '.$team;
	$values[] = 'team_spot = "'.$player_slot.'"';
	$values[] = 'mineral = 100'; // This needs to be configurable.
	$values[] = 'fuel = 100'; // This needs to be configurable.
	$values[] = 'agriculture = 100'; // This needs to be configurable.
	$values[] = 'population = 100'; // This needs to be configurable.
	$values[] = 'max_population = 100'; // This needs to be configurable.
	#$values[] = 'mineral_ratio = -1';		// 2003/05/06 -- Default value for these two fields
	#$values[] = 'fuel_ratio = -1';			// is now NULL, as it should be.
	$values[] = 'agriculture_ratio = 1.0';
	$values[] = 'tech_development = '.$series['tech_multiple'];
	$values[] = 'last_access = '.time();
	$values[] = 'last_update = '.$game['update_count'];	
	$values[] = 'tech_level = '.$tech_level;
	$values[] = 'techs = "Attack Science Colony"'; // Default techs; this may need to be configurable someday.
	// Dynamic calculation will be needed if we ever start games with HWs set other that 100/100/100
	$values[] = 'economic_power = 3';#.get_economic_power($series['id'], $game['game_number'], $vars['name']); // Do we need to calculate this
	$values[] = 'military_power = 0';#.get_military_power($series['id'], $game['game_number'], $vars['name']); // dynamically here?
	$values[] = 'ended_turn = "0"';
	$values[] = 'ip = "'.$_SERVER['REMOTE_ADDR'].'"';
	$values[] = 'map_origin = "'.$empire['map_origin'].'"';

	sc_query('INSERT INTO players SET '.implode(',', $values));
}

#----------------------------------------------------------------------------------------------------------------------#

function createPrebuiltMap($series, $game)
{
	$big_map = array();
	
	$lower_limit = $series['max_players']*$series['systems_per_player'];
	$upper_limit = floor($lower_limit + sqrt(($series['max_players'])*$series['systems_per_player']/$series['map_compression']));
	
	// Create a complete map using the regular map generation system.
	for ($player = 1; $player <= $series['max_players']; $player += 1)
		{
		$player_name = '=Player '.$player.'=';
		$chain = buildPlayerChain( $series['systems_per_player'], $big_map, $lower_limit, $upper_limit);
		$home = selectHW($player_name, $chain, $big_map);
		assignResources($series, $chain, $home, array_keys($chain));

		// Add every planet of this chain to the big map.
		foreach (array_keys($chain) as $c)
			{
			$big_map[$c] = $chain[$c];
			
			// 'player_number' is not stored in the database, but is used in performing the map twist.
			$big_map[$c]['player_number'] = $player;
			
			if ($c != $home)
				$big_map[$c]['name'] = nameSystem($big_map);
			}

		// Fix jumps after each segment to keep from having HWs next to each other.
		fixJumps($big_map);
		}
		
	saveMap($series, $game, $big_map);

	// Now set all the systems to inactive-- to be woken up as players join the game
	sc_query('UPDATE systems SET system_active = "0" WHERE game_id = '.$game['id'], __FILE__.'*'.__LINE__);
	
#	if (in_array('=Player 2=', $big_map))
#		echo 'Found!<br>';
//	drawMap( $big_map );
}

#-----------------------------------------------------------------------------------------------------------------------------------------#
# Like the name says.
#

function createBalancedMap($series, $game)
{
	$big_map = array();
	
	$lower_limit = $series['max_players']*$series['systems_per_player'];
	$upper_limit = floor($lower_limit + sqrt(($series['max_players'])*$series['systems_per_player']/$series['map_compression']));
	
	$chain1 = buildPlayerChain($series['systems_per_player'], $big_map, $lower_limit, $upper_limit);
	
	// Add every planet of this chain to the big map.
	foreach (array_keys($chain1) as $c)
		{
		$big_map[$c] = $chain1[$c];
		$big_map[$c]['player_number'] = 1;
		$big_map[$c]['name'] = nameSystem($big_map);
		}
		
	fixJumps($big_map);

	$chain2 = buildPlayerChain($series['systems_per_player'], $big_map, $lower_limit, $upper_limit);
	
	// Add every planet of this chain to the big map.
	foreach (array_keys($chain2) as $c)
		{
		$big_map[$c] = $chain2[$c];
		$big_map[$c]['player_number'] = 2;
		$big_map[$c]['name'] = nameSystem($big_map);
		}
		
	fixJumps($big_map);

	// Calculate the distances from each world to all the other worlds
	$map_distances = array();
	$big_map_indicies = array_keys($big_map);
	foreach ($big_map_indicies as $source)
		$map_distances[$source] = measure_map_from($source, $big_map);

	// Evaluate all the possible HW commbinations to find pairs with equal access to the map
	$i = 0;
	$min_diff = 10000;
	foreach (array_keys($chain1) as $hw1)
		{
		foreach (array_keys($chain2) as $hw2)
			{
			$jumps = explode(' ',$big_map[$hw1]['jumps']);

			if (!in_array($hw2, $jumps))	// don't pick worlds next to each other
				{
				$count1 = $count2 = 0;
				$dist1 = $dist2 = 0;
				
				foreach ($big_map_indicies as $sys)
					{
					// Project that each system will go to the closest player
					if ($map_distances[$hw1][$sys] < $map_distances[$hw2][$sys])
						{
						$count1++;
						$dist1 += $map_distances[$hw1][$sys];
						}
					else if ($map_distances[$hw1][$sys] > $map_distances[$hw2][$sys])
						{
						$count2++;
						$dist2 += $map_distances[$hw2][$sys];
						}
					// ties go to nobody
					}

				// The lower the difference in world counts, the more balanced the pair.
				$diff = abs($count1 - $count2);

				// If this is a new low, throw out any possible pairs found so far.
				if ($diff < $min_diff)
					{
					$min_diff = $diff;
					$i = 0;
					}

				// If this equals the low, save the possible pair.
				if ($diff == $min_diff)
					{
					$pair_data[$i]['hw1'] = $hw1;
					$pair_data[$i]['hw2'] = $hw2;
					$pair_data[$i]['balance'] = $diff;
					$pair_data[$i]['hw1 dispersion'] = $dist1;
					$pair_data[$i]['hw2 dispersion'] = $dist2;
					$i++;
					}
				}
			}
		}

	// Now, for all the possible pairs, compare their dispersion values (how close the
	// planets are to the HW) to find the pairs that are most nearly alike.
	$min_diff = 10000;
	$count = 0;
	for ($j = 0; $j < $i; $j++)
		{
		$diff = abs($pair_data[$j]['hw1 dispersion']-$pair_data[$j]['hw2 dispersion']);

		if ($diff < $min_diff)
			{
			$min_diff = $diff;
			$count = 0;
			}

		if ($diff == $min_diff) $count ++;
		}
	
	// If more than one match was found, choose a random pair.
	if ($count > 1)
		$k = rand(1, $count);
	else
		$k = 1;

	for ($j = 0; $j < $i; $j++)
		if (abs($pair_data[$j]['hw1 dispersion']-$pair_data[$j]['hw2 dispersion']) == $min_diff)
			{
			$k--;

			if ($k == 0) $pick = $j;
			}

	// Now, assign the homeworlds and resources for each chain and save it.
	$home = $pair_data[$pick]['hw1'];
  	$big_map[$home]['homeworld'] = '=Player 1=';
  	$big_map[$home]['owner'] = '=Player 1=';
  	$big_map[$home]['name'] = '=Player 1=';;
  	$big_map[$home]['mineral'] = 100;
  	$big_map[$home]['fuel'] = 100;
  	$big_map[$home]['agriculture'] = 100;
  	$big_map[$home]['population'] = 100;
  	$big_map[$home]['max_population'] = 100;
  	$big_map[$home]['coordinates'] = $home;
	assignResources($series, $big_map, $home, array_keys($chain1));
	
	$home = $pair_data[$pick]['hw2'];
  	$big_map[$home]['homeworld'] = '=Player 2=';
  	$big_map[$home]['owner'] = '=Player 2=';
  	$big_map[$home]['name'] = '=Player 2=';;
  	$big_map[$home]['mineral'] = 100;
  	$big_map[$home]['fuel'] = 100;
  	$big_map[$home]['agriculture'] = 100;
  	$big_map[$home]['population'] = 100;
  	$big_map[$home]['max_population'] = 100;
  	$big_map[$home]['coordinates'] = $home;
	assignResources($series, $big_map, $home, array_keys($chain2));
	saveMap($series, $game, $big_map);

/*
	// This is for debugging only
	foreach( $big_map_indicies as $sys )
		if ( $map_distances[$pair_data[$pick]['hw1']][$sys] < $map_distances[$pair_data[$pick]['hw2']][$sys] )
			{
			$big_map[$sys]['name'] .= '(1)';
			}
		else if ( $map_distances[$pair_data[$pick]['hw1']][$sys] > $map_distances[$pair_data[$pick]['hw2']][$sys] )
			{
			$big_map[$sys]['name'] .= '(2)';
			}
		else
			$big_map[$sys]['name'] .= '()';
	drawMap( $big_map );
*/
}

#
#-----------------------------------------------------------------------------------------------------------------------------------------#
#

function measure_map_from( $possible_hw, $big_map )
{
	$map_dist = array();
	$distance = 0;
	$sys_list = array();
	$sys_list[] = $possible_hw;
	
	while (count($sys_list) > 0)
		{
		$new_list = array();

		foreach ($sys_list as $sys)
			{
			$map_dist[$sys] = $distance;

			$jumps = explode(' ', $big_map[$sys]['jumps']);

			foreach ($jumps as $jump)
				if (!array_key_exists($jump, $map_dist) and !in_array($jump, $new_list))
					$new_list[] = $jump;
			}
			
		$distance += 1;
		$sys_list = $new_list;
		}

	return $map_dist;
}

#-----------------------------------------------------------------------------------------------------------------------------------------#
# Like the name says.
#

function createTwistedMap($series, $game)
{
	$big_map = array();

	$lower_limit = $series['max_players']*$series['systems_per_player'];
	$upper_limit = floor($lower_limit + sqrt(($series['max_players']/2)*$series['systems_per_player']/$series['map_compression']));

	// build the map
	createMapSide($series, $big_map, $lower_limit, $upper_limit);
	twistMap($big_map, $series['max_players'], $lower_limit, $upper_limit);
	saveMap($series, $game, $big_map);
	
	// debug
//	drawMap( $big_map );
}

#-----------------------------------------------------------------------------------------------------------------------------------------#
# Like the name says.
#

function createMirrorMap($series, $game)
{
	$big_map = array();

	$lower_limit = $series['max_players']*$series['systems_per_player'];
	$upper_limit = floor($lower_limit + sqrt(($series['max_players']/2)*$series['systems_per_player']/$series['map_compression']));

	// build the map
	createMapSide($series, $big_map, $lower_limit, $upper_limit);
	mirrorMap($big_map, $series['max_players'], $lower_limit, $upper_limit);
	saveMap($series, $game, $big_map);

//	drawMap( $big_map ); // return;
}

#-----------------------------------------------------------------------------------------------------------------------------------------#
# Create half a map (used in twised or mirror maps).
#

function createMapSide($series, &$big_map, $lower_limit, $upper_limit)
{
	// Make half the map
	for ($player = 1; $player <= $series['max_players']/2; $player += 1)
		{
		$player_name = '=Player '.$player.'=';
		$chain = buildPlayerChain( $series['systems_per_player'], $big_map, $lower_limit, $upper_limit );
		$home = selectHW( $player_name, $chain, $big_map );
		assignResources( $series, $chain, $home, array_keys($chain) );

		// Add every planet of this chain to the big map.
		foreach (array_keys($chain) as $c)
			{
			$big_map[$c] = $chain[$c];
			
			// player number is not stored in the data base, but is used in
			// performing the map twist
			$big_map[$c]['player_number'] = $player;
			
			if ( $c != $home )
				$big_map[$c]['name'] = nameSystem( $big_map );
			}

		// fix jumps after each interation to keep HWs from ending up next to each other
		fixJumps($big_map);
		}
}

#-----------------------------------------------------------------------------------------------------------------------------------------#
# Utility function; flips the x and y components of a coordinate.
#

function flipCoord($coord, $xval, $yval)
{
    list($x, $y) = explode(',', $coord);

    return ($xval ? ($xval-$x) : $x).','.($yval ? ($yval-$y) : $y);
}

#-----------------------------------------------------------------------------------------------------------------------------------------#
# The map twisting function picks one edge (north, east, south, or west) of the passed
# half-map for mating to itself in the twist. The twist is accomplished by rotating the
# map 180 degrees and matching the picked edge to itself in the way that makes for the
# greatest number of interconnections (without connecting a system to it's rotated self)
#

function twistMap(&$big_map, $n_players, $lower_limit, $upper_limit)
{
	$boundaries = array('north' => array(), 'east' => array(), 'south' => array(), 'west' => array());

	$directions = array_keys($boundaries);
	$big_map_indicies = array_keys($big_map);

	$top = $lower_limit;
	$bottom = $upper_limit;
	$left = $upper_limit;
	$right = $lower_limit;

	// Find the coordinate limits of the existing half-map.
	foreach ($big_map_indicies as $coord)
		{
	    list($x, $y) = explode(',', $coord);
	    
	    if ($x > $right) $right = $x;
	    if ($x < $left) $left = $x;
	    if ($y > $top) $top = $y;
	    if ($y < $bottom) $bottom = $y;
		}

	// Initialize the boundaries arrays. We initialize everything to upper limit
	// because we will be calculating the distance to the edge, which will always
	// be less than that.
	for ($y = $bottom; $y <= $top; $y++)
		{
		$boundaries['west'][$y] = array('dist' => $upper_limit, 'player' => -1, 'coord' => "");
		$boundaries['east'][$y] = array('dist' => $upper_limit, 'player' => -1, 'coord' => "");
		}

	for ($x = $left; $x <= $right; $x++)
		{
		$boundaries['north'][$x] = array('dist' => $upper_limit, 'player' => -1, 'coord' => "");
		$boundaries['south'][$x] = array('dist' => $upper_limit, 'player' => -1, 'coord' => "");
		}
	

	// Now, run along each edge calculating the distance from the edge to the coordinate limit.
	// Note that this works by calculating how far each planet is from each limit and replacing
	// the current value if the calculated one is closer.
	foreach ($big_map_indicies as $coord)
		{
	    list($x, $y) = explode(',', $coord);

		if (($x-$left) < $boundaries['west'][$y]['dist'])
			{
	        $boundaries['west'][$y]['dist'] = ($x-$left);
	        $boundaries['west'][$y]['player'] = $big_map[$coord]['player_number'];
	        $boundaries['west'][$y]['coord'] = $coord;
			}

		if (($y-$bottom) < $boundaries['south'][$x]['dist'])
			{
	        $boundaries['south'][$x]['dist'] = ($y-$bottom);
	        $boundaries['south'][$x]['player'] = $big_map[$coord]['player_number'];
	        $boundaries['south'][$x]['coord'] = $coord;
			}
		
		if (($right-$x) < $boundaries['east'][$y]['dist'])
			{
	        $boundaries['east'][$y]['dist'] = ($right-$x);
	        $boundaries['east'][$y]['player'] = $big_map[$coord]['player_number'];
	        $boundaries['east'][$y]['coord'] = $coord;
			}
		
		if (($top-$y) < $boundaries['north'][$x]['dist'])
			{
	        $boundaries['north'][$x]['dist'] = ($top-$y);
	        $boundaries['north'][$x]['player'] = $big_map[$coord]['player_number'];
	        $boundaries['north'][$x]['coord'] = $coord;
			}
		}

	// For each edge and each way that edge could be matched with itself after a twist,
	// calculate the number of players that could be connected. Currently we look only for
	// straight across connections. In many cases it would also be possible to make sideways 
	// connections as well, and a future enhancement might want to do that.
	foreach ($directions as $edge)
		{
	    ksort( $boundaries[$edge] );          // put in map order for matching
	    
		$rows = array_keys($boundaries[$edge]);
	    $boundaries[$edge]['players'] = 0;
	    $boundaries[$edge]['links'] = array();
	    $start = 0;
	    
	    // We do the possible alignments in two steps-- that's because for half of the match
	    // we need to change the ending planet while for the other half we need to change the start.
	    for ($end = 0; $end < count($rows); $end++)
			{
	    	// Find the distance from the edge that will match in this alignment.
	        $target = $upper_limit;
	        for ($i = $start, $j = $end; $i <= $end; $i++, $j--)
		        {
	            $total = $boundaries[$edge][$rows[$i]]['dist'] + $boundaries[$edge][$rows[$j]]['dist'];
	            if ($total < $target) $target = $total;
			    }
	        
	        // Mow find all the planets that match in this alignment.
	        $p = array();
	        $links = array();
	        for ($i = $start, $j = $end; $i <= $end; $i++, $j--)
				{
	        	// Matches are different players (except 2 player games) with planets at the 'match distance' and not both homeworlds.
	            if (
	                 ( ( ($n_players == 2) or ($boundaries[$edge][$rows[$i]]['player'] != $boundaries[$edge][$rows[$j]]['player']) ) ) and
	                 ( ($boundaries[$edge][$rows[$i]]['dist'] + $boundaries[$edge][$rows[$j]]['dist']) == $target ) and
	                 ( ($big_map[$boundaries[$edge][$rows[$i]]['coord']]['homeworld'] == "") or ($big_map[$boundaries[$edge][$rows[$j]]['coord']]['homeworld'] == "") )
	               )
	               {
	                   $links[] = $boundaries[$edge][$rows[$i]]['coord'].' '.$boundaries[$edge][$rows[$j]]['coord'];
					   if (!in_array($boundaries[$edge][$rows[$i]]['player'], $p)) $p[]=$boundaries[$edge][$rows[$i]]['player'];
					   if (!in_array($boundaries[$edge][$rows[$j]]['player'], $p)) $p[]=$boundaries[$edge][$rows[$j]]['player'];
	               }
				}

	        // And if this is a better match than any we have found so far for this edge, save it.
	        // The best match is defned as connecting the largest number of players and, if more than
	        // one alignment yields that number of players, the largest number of planets.
	        if (count($p) > $boundaries[$edge]['players'])
				{
	            $boundaries[$edge]['players'] = count($p);
	            $boundaries[$edge]['links'] = $links;
	            $boundaries[$edge]['offset'] = $end-(count($rows)-1);
	            $boundaries[$edge]['target'] = $target;
				}
			else if (count($p) == $boundaries[$edge]['players'] and count($links) > count($boundaries[$edge]['links']))
				{
	            $boundaries[$edge]['links'] = $links;
	            $boundaries[$edge]['offset'] = $end-(count($rows)-1);
	            $boundaries[$edge]['target'] = $target;
				}
			}

	    // This is the second half of the alignment testing
	    $end = count($rows) -1;
	    for ( $start=1; $start<count($rows); $start++ )
	    {
	        $target = $upper_limit;
	        for ( $i=$start,$j=$end; $i<=$end; $i++,$j-- )
	        {
	            $total = $boundaries[$edge][$rows[$i]]['dist'] + $boundaries[$edge][$rows[$j]]['dist'];
	            if ( $total < $target ) $target = $total;
	        }
	        $p = array();
	        $links = array();
	        for ( $i=$start,$j=$end; $i<=$end; $i++,$j-- )
	        {
	            if (
	                 ( ( ($n_players == 2) or ($boundaries[$edge][$rows[$i]]['player'] != $boundaries[$edge][$rows[$j]]['player']) ) ) and
	                 ( ($boundaries[$edge][$rows[$i]]['dist'] + $boundaries[$edge][$rows[$j]]['dist']) == $target ) and
	                 ( ($big_map[$boundaries[$edge][$rows[$i]]['coord']]['homeworld'] == "") or ($big_map[$boundaries[$edge][$rows[$j]]['coord']]['homeworld'] == "") )
	               )
	               {
	                   $links[] = $boundaries[$edge][$rows[$i]]['coord'].' '.$boundaries[$edge][$rows[$j]]['coord'];
	                   if ( !in_array( $boundaries[$edge][$rows[$i]]['player'], $p ) ) $p[]=$boundaries[$edge][$rows[$i]]['player'];
	                   if ( !in_array( $boundaries[$edge][$rows[$j]]['player'], $p ) ) $p[]=$boundaries[$edge][$rows[$j]]['player'];
	               }
	        }
	        if ( count($p) > $boundaries[$edge]['players'] )
	        {
	            $boundaries[$edge]['players'] = count($p);
	            $boundaries[$edge]['links'] = $links;
	            $boundaries[$edge]['offset'] = $start;
	            $boundaries[$edge]['target'] = $target;
	        }
	        if ( (count($p) == $boundaries[$edge]['players']) and ( count($links) > count($boundaries[$edge]['links'])) )
	        {
	            $boundaries[$edge]['links'] = $links;
	            $boundaries[$edge]['offset'] = $start;
	            $boundaries[$edge]['target'] = $target;
	        }
	    }
	}

	// look through the matches we found and pick the best
	$picked_boundary = 'unknown';
	$max_players = 0;
	$max_links = 0;
	foreach ( $directions as $edge )
	    if ( $boundaries[$edge]['players'] == $max_players )
	    {
	        if ( count($boundaries[$edge]['links']) > $max_planets )
	            {
	                $picked_boundary = $edge;
	                $max_players = $boundaries[$edge]['players'];
	                $max_planets = count($boundaries[$edge]['links']);
	            }
	    }
	    else if ( $boundaries[$edge]['players'] > $max_players )
	    {
	        	$picked_boundary = $edge;
	            $max_players = $boundaries[$edge]['players'];
	            $max_planets = count($boundaries[$edge]['links']);
	    }

	// calculate the transform for the chosen boundary
	switch ( $picked_boundary )
	{
	    case 'north':
	    	 $xval = $left + $right + $boundaries[$picked_boundary]['offset'];
	    	 $yval = 2 * $top - ($boundaries[$picked_boundary]['target'] - 1);
	    	 break;
	    case 'south':
	    	 $xval = $left + $right + $boundaries[$picked_boundary]['offset'];
	    	 $yval = 2 * $bottom + ($boundaries[$picked_boundary]['target'] - 1);
	    	 break;
	    case 'east':
	    	 $xval = 2 * $right - ($boundaries[$picked_boundary]['target'] - 1);
	    	 $yval = $top + $bottom + $boundaries[$picked_boundary]['offset'];
	    	 break;
	    case 'west':
	    	 $xval = 2 * $left + ($boundaries[$picked_boundary]['target'] - 1);
	    	 $yval = $top + $bottom + $boundaries[$picked_boundary]['offset'];
	    	 break;
	}

	// and FINALLY, copy over the planets using the transform
	copyMap( $big_map, $n_players, $xval, $yval );
	
	// and then fix up the links
	foreach ( $boundaries[$picked_boundary]['links'] as $pair )
	{
	    list($from,$to) = explode(' ',$pair);
	    $to = flipCoord($to,$xval,$yval);
	    $big_map[$from]['jumps'] .= ' '.$to;
	    $big_map[$to]['jumps'] .= ' '.$from;
	}

}

#
#-----------------------------------------------------------------------------------------------------------------------------------------#
#

function mirrorMap( &$big_map, $n_players, $lower_limit, $upper_limit )
{
	global $server;
	
	$edge_counts = array( 'top'=>0, 'bottom'=>0, 'left'=>0, 'right'=>0 );
	$top = $lower_limit;
	$bottom = $upper_limit;
	$left = $upper_limit;
	$right = $lower_limit;
	
	$big_map_indicies = array_keys($big_map);

	// find the coordinate limits of the existing half-map and how many planets are at the limit
	foreach ( $big_map_indicies as $coord )
	{
	    list( $x, $y ) = explode( ',', $coord );
	    
	    if ( $x > $right )
	    {
	    	$right = $x;
	    	$edge_counts['right'] = 1;
	    }
	    else if ( $x == $right )
	    	$edge_counts['right'] += 1;
	    
	    if ( $x < $left ) 
	    {
	    	$left = $x;
	    	$edge_counts['left'] = 1;
	    }
	    else if ( $x == $left )
	    	$edge_counts['left'] += 1;
	    
	    if ( $y > $top )
	    {
	    	$top = $y;
	    	$edge_counts['top'] = 1;
	    }
	    else if ( $y == $top )
	    	$edge_counts['top'] += 1;
	    
	    if ( $y < $bottom )
	    {
	    	$bottom = $y;
	    	$edge_counts['bottom'] = 1;
	    }
	    else if ( $y == $bottom )
	    	$edge_counts['bottom'] += 1;
	}
	
	// pick the edge that has the most planets 
	arsort( $edge_counts );
	$picked_edge = key( $edge_counts );
	
	// if it's a 2 player game, we tend to not have homeworlds on the boundary
	// this is to mimic the SC3.2 behavior
	// we do it now so that it's automatically mirrored along with everything else
	if ( $n_players == 2 )
	{
		foreach ( $big_map_indicies as $coord )
		{
			if ( $big_map[$coord]['homeworld'] != '')
				break;
		}
		$home = $coord;	// save result for later!
		$tries = 0;
		do
		{
			list($x,$y) = explode(',',$coord);
			// if coordinate is not on the mirror edge, exit the loop (note the break *2*!)
			switch ( $picked_edge )
			{
				case 'top':
					if ( $y != $top ) break 2;
					break;
				case 'bottom':
					if ( $y != $bottom ) break 2;
					break;
				case 'left':
					if ( $x != $left ) break 2;
					break;
				case 'right':
					if ( $x != $right ) break 2;
					break;
			}
			// OK, this one *is* on the edge, so try another
			$coord = array_rand($big_map);
			$tries++;
		} while ($tries < 2); // 3.2 does 3 tries-- our first try was the original HW location
		
		// Now, if we got a none edge location, swap the system data to get the HW off the edge
		if ( ($tries < 3) and ($coord != $home))
		{
			$temp = $big_map[$coord];
			$big_map[$coord] = $big_map[$home];			// new system has HW data
			$big_map[$coord]['coordinates'] = $coord;	// fix cooridnates and jumps!
			$big_map[$coord]['jumps'] = $temp['jumps'];
			$temp['jumps'] = $big_map[$home]['jumps'];
			$temp['coordinates'] = $home;
			$big_map[$home] = $temp;					// old HW has new HW's old data
			$home = coord;
		} 
	}
	
	switch ( $picked_edge )
	{
		case 'top':
			$xval = 0;
			$yval = 2 * ( $top + 1 );
			$buffer = $top + 1;
			break;
		case 'bottom':
			$xval = 0;
			$yval = 2 * ( $bottom - 1 );
			$buffer = $bottom - 1;
			break;
		case 'left':
			$xval = 2 * ( $left - 1);
			$yval = 0;
			$buffer = $left - 1;
			break;
		case 'right':
			$xval = 2 * ( $right + 1);
			$yval = 0;
			$buffer = $right + 1;
			break;
	}
	
	// add links on chosen edge and calculate buffer planet positions
	$buffer_planets = array();
	foreach($big_map_indicies as $coord)
		{
		list($x, $y) = explode(',', $coord);

		if ( ($picked_edge == 'top' and $y == $top) or ($picked_edge == 'bottom' and $y == $bottom) )
			{
			$buffer_planets[] = $x.','.$buffer;
			$big_map[$coord]['jumps'] .= ' '.$x.','.$buffer;
			}
		else if ( (($picked_edge == 'left') and ($x == $left)) or (($picked_edge == 'right') and ($x == $right)) )
			{
			$buffer_planets[] = $buffer.','.$y;
			$big_map[$coord]['jumps'] .= ' '.$buffer.','.$y;
			}
		}
	
	// mirror the half-map. (Note the links will get mirrored too)
	copyMap( $big_map, $n_players, $xval, $yval );
	
	// add in the buffer planets and their links
	foreach( $buffer_planets as $coord )
	{
		list( $x, $y  ) = explode(',',$coord);
		switch ( $picked_edge )
		{
			case 'top':
			case 'bottom':
				$jumps = $x.','.($y-1).' '.$x.','.($y+1);
				$neighbors = array( ($x-1).','.$y, ($x+1).','.$y );
				break;
			case 'left':
			case 'right':
				$jumps = ($x-1).','.$y.' '.($x+1).','.$y;
				$neighbors = array( $x.','.($y-1), $x.','.($y+1) );
				break;
		}
		$big_map[$coord]['coordinates'] = $coord;
		$big_map[$coord]['jumps'] = $jumps;
		
		// Check for cross links- We only look from this new system to previously added
		// buffer systems. That gives each possible link one shot at getting created.
		foreach ( $neighbors as $target )
			if ( array_key_exists( $target, $big_map ) and ( rand(1,4) == 1 ) )
			{
				$big_map[$coord]['jumps'] .= ' '.$target;
				$big_map[$target]['jumps'] .= ' '.$coord;
			}
			
		// resource assigments in 3.2 are random(HW resource/4) + HW resource/4
		// (In earlier versions they were even higher!)
		// we'll need to change this if we go to variable starting resources
		// The thing I don't like about them is that they are instantly recognizable as buffer worlds
		$big_map[$coord]['mineral'] = rand(0,25) + 25;
		$big_map[$coord]['fuel'] = rand(0,25) + 25 ;
		$big_map[$coord]['agriculture'] = rand(0,25) + 25 ;
		$big_map[$coord]['population'] = 0;
		$big_map[$coord]['max_population'] = max($big_map[$coord]['mineral'],$big_map[$coord]['fuel']);
		$big_map[$coord]['player_number'] = 0;	// buffer planets don't match to any player
		$big_map[$coord]['homeworld'] = '';
		$big_map[$coord]['owner'] = '';
		$big_map[$coord]['name'] = nameSystem( $big_map );
	}
}

#
#-----------------------------------------------------------------------------------------------------------------------------------------#
# copyMap supports the half map copy activities of both twistMap and mirrorMap
#

function copyMap( &$big_map, $n_players, $xval, $yval )
{
	foreach ( array_keys($big_map) as $coord )
	{
		$new_coord = flipCoord( $coord, $xval, $yval );
//		$big_map[$new_coord]['player_number'] = 2*$series['max_players'] - $big_map[$coord]['player_number'];
		$big_map[$new_coord]['coordinates'] = $new_coord;
		$jumps = explode(' ',$big_map[$coord]['jumps']);
		$new_jumps = array();
		foreach ($jumps as $jump)
			$new_jumps[] = flipCoord($jump,$xval,$yval);
	    $big_map[$new_coord]['jumps'] = implode(' ',$new_jumps);
	    $big_map[$new_coord]['mineral'] = $big_map[$coord]['mineral'];
		$big_map[$new_coord]['fuel'] = $big_map[$coord]['fuel'];
		$big_map[$new_coord]['agriculture'] = $big_map[$coord]['agriculture'];
		$big_map[$new_coord]['population'] = $big_map[$coord]['population'];
		$big_map[$new_coord]['max_population'] = $big_map[$coord]['max_population'];
		$player = $n_players - $big_map[$coord]['player_number'] + 1;
		$big_map[$new_coord]['player_number'] = $player;

		if ( $big_map[$coord]['homeworld'] == '')
		{
			$big_map[$new_coord]['owner'] = '';
			$big_map[$new_coord]['homeworld'] = '';
			$big_map[$new_coord]['name'] = nameSystem( $big_map );
		}
		else
		{
			$big_map[$new_coord]['owner'] = '=Player '.$player.'=';
			$big_map[$new_coord]['homeworld'] = '=Player '.$player.'=';
			$big_map[$new_coord]['name'] = '=Player '.$player.'=';
		}
	}
}

#
#-----------------------------------------------------------------------------------------------------------------------------------------#
#

function generateMapForPlayer($name, $series, $game)
{
	global $server;

	// Get the current map, if there is one.
	$big_map = array();
	$select = sc_query('SELECT * FROM systems WHERE series_id = '.$series['id'].' AND game_number = '.$game['game_number'], __FILE__.'*'.__LINE__);
	while ($system = $select->fetch_assoc())
		$big_map[$system['coordinates']] = $system;

	$chain = buildPlayerChain( $series['systems_per_player'], $big_map, 1, 2*$series['max_players']*$series['systems_per_player'] );
	foreach ( array_keys($chain) as $c )
		$chain[$c]['player_number'] = $game['player_count'];
	$home = selectHW( $name, $chain, $big_map );
	assignResources( $series, $chain, $home, array_keys($chain) );

	// Add every planet of this chain to the big map.
  	foreach (array_keys($chain) as $c)
  	{
  		if ( $c != $home )
	  		$chain[$c]['name'] = nameSystem( $bigmap );
  		$big_map[$c] = $chain[$c];
  	}

	// save here so that system names get saved
	saveMap( $series, $game, $chain );

	$big_map_indices = array_keys($big_map);

  	// Now, for each planet in the map...
	foreach ($big_map_indices as $ixa)
  		{
		// ...get an array of its jumps...
    	$jump = array();
    	$j_list = explode(' ', $big_map[$ixa]['jumps']);

		// ...and for each of them...
    	foreach ($j_list as $ixb)
      		// ...if that jumped-to-system exists in the map...
			if ( in_array($ixb, $big_map_indices) )
      			{
				// ... add it to the source system's temporary jump array...
				$jump[] = $ixb;
				// ...and if the source system is not in the destination system's
				// jump list, append it to it! That way we have two-way access
				// across these two systems.
				if ( !in_array($ixa, explode(' ', $big_map[$ixb]['jumps'])) )
					{
					$big_map[$ixb]['jumps'] .= ' '.$ixa;
					$jumps = $big_map[$ixb]['jumps'];

					$conditions = array();
					$conditions[] = 'series_id = '.$series['id'];
					$conditions[] = 'game_number = '.$game['game_number'];
					$conditions[] = 'coordinates = "'.$ixb.'"';

					sc_query('UPDATE systems SET jumps = "'.$jumps.'" WHERE '.implode(' AND ', $conditions), __FILE__.'*'.__LINE__);
					}
				}

		// Commit the final jump list of the sytem we started checking to the chain.
    	$jumps = $big_map[$ixa]['jumps'] = implode(' ', $jump);
		
		$conditions = array();
		$conditions[] = 'series_id = '.$series['id'];
		$conditions[] = 'game_number = '.$game['game_number'];
		$conditions[] = 'coordinates = "'.$ixa.'"';

		sc_query('UPDATE systems SET jumps = "'.$jumps.'" WHERE '.implode(' AND ', $conditions), __FILE__.'*'.__LINE__);
		}

	// debug
//	drawMap( $big_map ); // return;
}

#
#-----------------------------------------------------------------------------------------------------------------------------------------#
#

function buildPlayerChain( $nplanets, &$big_map, $lower_limit, $upper_limit )
{
	do
		{
		// Initialize our chain and get all the coordinates from the map.
		$chain = array();

		if ( count($big_map) == 0 )
			{
			// The map is currently empty; we will start building it at coordinates 'dx,dy'.
			$dx = floor( ($lower_limit+$upper_limit) / 2 );
			$dy = floor( ($lower_limit+$upper_limit) / 2 );

			$sx = $sy = 0;
			}
		else
			{
			// A map already exists!
			do
				{
				// Loop until we find a planet in the map which does not have all
				// its possible jumps occupied. To find it, we choose a random planet
				// from the map and construct an array of its jumps. We then test to
				// see if it has them all coming out of it or not.
				do
					{
					$s_sys = array_rand($big_map);
					// get source coordinates for new system
					list( $sx, $sy ) = explode(',',$s_sys);

				    // First, we construct an array with all the possible jumps this planet can have.
				    $j_list = array();
				    if ( $sx < $upper_limit )
				       	$j_list[] = ($sx+1).','.$sy;
                    if ( $sy < $upper_limit )
				       	$j_list[] = $sx.','.($sy+1);
                    if ( $sx > $lower_limit )
				   		$j_list[] = ($sx-1).','.$sy;
                	if ( $sy > $lower_limit )
                   		$j_list[] = $sx.','.($sy-1);
					$s_j = explode(' ', $big_map[$s_sys]['jumps']);
					}
				while ( count($s_j) == count($j_list) );

			    // Ok, we have a planet from which we can start building our chain.

				// New we loop through this source planet's potential jumps until we get
				// a planet which is not in the source planet's jump list. In other words, we
				// can fork off here.
				do
					{
					$d_sys = $j_list[array_rand($j_list)];
					}
				while ( in_array($d_sys, $s_j) );

				}
			while ( array_key_exists( $d_sys, $big_map ) );

			// destination coordinates for the new planet.
			list($dx,$dy) = explode(',',$d_sys);
			}

		// Starting from this new planet, recurse until we create all the planets
		// for this player.
		$chain = addChain($chain, $big_map, $nplanets, $dx, $dy, $sx, $sy, $lower_limit, $upper_limit );
		}
	while ( count($chain) != $nplanets );
	
	return ($chain);
}

#
#-----------------------------------------------------------------------------------------------------------------------------------------#
#

function selectHW( $player_name, &$chain, &$big_map )
{
  	do
  		{
		// Randomly select a planet to be the player's homeworld
		// and get an array of its jumps.
    	$home = array_rand($chain);
    	$h_jmp = explode(' ', $chain[$home]['jumps']);

		// We will do this as long as that homeworld-to-be has
		// a non-player planet next to it which happens to be another homeworld.
		// We have at most 4 iterations for this loop before we chose another
		// homeworld, depending on the number of jumps this planet has.
    	$fail = 0;

		foreach ($h_jmp as $ixa)
      		if ( !array_key_exists($ixa, $chain ) )
				if ( array_key_exists($ixa, $big_map) )
	  				if ( $big_map[$ixa]['homeworld'] != '') $fail = 1;
  		}
	while ($fail == 1);
	
	// OK, we found a homeworld. Set it up.
  	$chain[$home]['homeworld'] = $player_name;
  	$chain[$home]['owner'] = $player_name;
  	$chain[$home]['name'] = $player_name;
  	$chain[$home]['mineral'] = 100;
  	$chain[$home]['fuel'] = 100;
  	$chain[$home]['agriculture'] = 100;
  	$chain[$home]['population'] = 100;
  	$chain[$home]['max_population'] = 100;
  	$chain[$home]['coordinates'] = $home;

	return ( $home );
}

#
#-----------------------------------------------------------------------------------------------------------------------------------------#
#

function assignResources( $series, &$chain, $home, $indexes )
{
	global $server;
	
	$count = count($indexes);

	// Resource totals; calculated according to the average resources for a system.
	$min_t = ($series['systems_per_player']-1)*$series['avg_min'];
	$ag_t = ($series['systems_per_player']-1)*$series['avg_ag'];
	$fuel_t = ($series['systems_per_player']-1)*$series['avg_fuel'];

	// For every planet belonging to the player that is not his homeworld,
	// we initiliaze its values and assign a random quantity of ressources,
	// deducting it from the total. When we get to the last planet,
	// we dump the remaining quantities on it.
	foreach ($indexes as $ixa)
	{
		if ( $ixa != $home )
		{
	    	if (--$count > 1)
	    		{
				// Average value is multiplied by 2 so that any given planet can have from 0 to 2*avg resource
	      		$min_t  -= $chain[$ixa]['mineral']     = rand(0, $min_t*2/$count);
	      		$ag_t   -= $chain[$ixa]['agriculture'] = rand(0, $ag_t*2/$count);
	      		$fuel_t -= $chain[$ixa]['fuel'] 	   = rand(0, $fuel_t*2/$count);
	    		}
	    	else
	    		{
				// The last system gets the bread crumbs-- which can be bigger than 2*average!
	      		$chain[$ixa]['mineral'] = $min_t;
	      		$chain[$ixa]['agriculture'] = $ag_t;
	      		$chain[$ixa]['fuel'] = $fuel_t;
	    		}

			// Set the maximum population of this planet accordingly.
	    	$max_pop = max($chain[$ixa]['mineral'],$chain[$ixa]['fuel']);

			$chain[$ixa]['population'] = 0;
			$chain[$ixa]['max_population'] = $max_pop;
			$chain[$ixa]['coordinates'] = $ixa;
			$chain[$ixa]['owner'] = '';
			$chain[$ixa]['homeworld'] = '';
		}
	}
}

#
#-----------------------------------------------------------------------------------------------------------------------------------------#
#

function addChain(&$chain, &$big_map, $nplanets, $x, $y, $xin, $yin, $lower_limit, $upper_limit)
{
	// If the number of planets in this chain is the number we want,
	// exit this recursive loop; we are done! The '>=' is just for safety,
	// I guess, because we have a really big problem if some players have more
	// planets than the others...
	if ( count($chain) >= $nplanets ) return $chain;

	// Add a slot to the chain array, for the new planet.
  	$chain[$x.','.$y] = array();

	// Construct an array of all its possible jumps. 
	$j_list = array( );
	if ( $x < $upper_limit )
	   $j_list[] = ($x+1).','.$y;
	if ( $y < $upper_limit )
       $j_list[] = $x.','.($y+1);
    if ( $x > $lower_limit )
	    $j_list[] = ($x-1).','.$y;
    if ( $y > $lower_limit )
        $j_list[] = $x.','.($y-1);

	// Randomly determine how many jumps we will add to this system
	// from the array we constructed above. An upper limit of 4 (or equal to the
	// number of possible jumps if the map is bounded) will produce
	// a map with almost no dead-ends; a value of 2 will make it more
	// maze-like. Tuneable.
	// You can fine tune by picking a random of a larger range and dividing down.
	// For example $j = floor(rand(1,35)/$10); which (effectively) sets the upper
	// end of the range to 3.5. This would be interesting as a series dependent
	// variable.
	// NOTE that this doesn't have as much effect on the final map as you would
	// think it might-- not all links selected here will actually get generated.
	// This is because the program selects possible links now and then creates
	// planets recursively. If it hits the maximum number of planets before all
	// of these links have actually been created-- poof! the extras go away.
    $j = rand( 1, (count($j_list)-1) );
  	$jump = array();

	// If this is not the first system we create, xin and yin will
	// be non-zero, thus true. This means that the source planet
	// exists and we therefore remove that system from the jump list
	// and add it to the system's temporary jump list.
  	if ($xin and $yin)
  		{
  		array_remove($xin.','.$yin, $j_list);
    	$jump[] = $xin.','.$yin;
    	$j--;
  		}

	// Loop until we've chosen all the jumps we can.
  	while ($j > 0)
  		{
		// Choose a jump at random...
    	$j_to = rand(0, count($j_list)-1);
    	if ( !in_array($j_list[$j_to], $jump) )
    		{
			// ...and add it to the list if it's not already in the list.
      		$jump[] = $j_list[$j_to];
      		$j--;
    		}
  		}

	// Commit the jump list to the system in the chain.
  	$chain[$x.','.$y]['jumps'] = implode(' ', $jump);

	// Now, for each jump we have for this system...
	for ($j = 0; $j < count($jump); $j++)
  		{
		// ...if the jumped-to system already exists, either in the map or in the chain, skip this.
    	if ( ( array_key_exists($jump[$j], $chain) or array_key_exists($jump[$j], $big_map) ) ) continue;

		// Otherwise, start adding more systems from each of those 'empty' jump, recursively.
		// Isn't recursion beautiful? :)
    	$dest = explode(',', $jump[$j]);
    	$chain = addChain($chain, $big_map, $nplanets, $dest[0], $dest[1], $x, $y, $lower_limit, $upper_limit);
  		}

  	return $chain;
}

#
#-----------------------------------------------------------------------------------------------------------------------------------------#
#

function fixJumps( &$map )
{
	$map_indices = array_keys($map);

	// Now, for each planet in the map or map fragment
	foreach ($map_indices as $ixa)
	{
		// ...get an array of its jumps...
		$jump = array();
		$j_list = explode(' ', $map[$ixa]['jumps']);

		// ...and for each of them...
		foreach ($j_list as $ixb)
	        // ...if that jumped-to-system exists in the map...
			if ( in_array($ixb, $map_indices) )
	  	    {
				// ... add it to the source system's temporary jump array...
				array_push($jump, $ixb);
				// ...and if the source system is not in the destination system's
				// jump list, append it to it! That way we have two-way access
				// across these two systems.
				if ( !in_array($ixa, explode(' ', $map[$ixb]['jumps'])) )
				{
					$map[$ixb]['jumps'] .= ' '.$ixa;
					$jumps = $map[$ixb]['jumps'];
				}
			}

		// Commit the final jump list of the sytem we started checking to the chain.
		$jumps = $map[$ixa]['jumps'] = implode(' ', $jump);
	}
}

#
#-----------------------------------------------------------------------------------------------------------------------------------------#
# Writes the map section passed in map to the database
# Can be used for either a chain or an entire map
#

function saveMap( $series, $game, $map )
{
	$map_indicies = array_keys($map);
	
	foreach( $map_indicies as $coord )
		{
		// save the system data
		$values = array();
		$values[] = 'series_id = '.$series['id'];
		$values[] = 'game_number = '.$game['game_number'];
		$values[] = 'game_id = '.$game['id'];
		
		foreach ( $map[$coord] as $f => $v )
			switch ($f)
			{
				case 'coordinates':
				case 'owner':
				case 'homeworld':
				case 'name':
				case 'jumps':
					$values[] = $f.' = "'.$v.'"';
					break;
				default:
					$values[] = $f.' = '.$v;
					break;
			}
		
		sc_query('INSERT INTO systems SET '.implode(', ', $values), __FILE__.'*'.__LINE__);
		
		$player = getPlayer($game['id'], $name);
		
		// set the explored array while we're at it
		// note: initial player names have been surrounded with equal sign characters to prevent sneaky
		// cheaters from getting access-- equals is an illegal character in an empire name
		if ( ($name = $map[$coord]['homeworld']) != '')
			{
			$values = array();
			$values[] = 'series_id = '.$series['id'];
			$values[] = 'game_number = '.$game['game_number'];
			$values[] = 'empire = "'.$name.'"';
			$values[] = 'game_id = '.$game['id'];
			$values[] = 'coordinates = "'.$coord.'"';
			$values[] = 'update_explored = 0';
			
			$query = 'INSERT INTO explored SET '.implode(',', $values);
			
			sc_query($query, __FILE__.'*'.__LINE__);
			}
		}
}

#-----------------------------------------------------------------------------------------------------------------------------------------#
# For testing map generation algorithms. Just pass it an array of planets generated from the above algorithms.
#

function showMap($game)
{
	$map = array();
	
	// load the active systems in the map
	$select = sc_query('SELECT * FROM systems WHERE game_id = '.$game['id'].' AND system_active = "1"', __FILE__.'*'.__LINE__);
	while ($system = $select->fetch_assoc())
		$map[$system['coordinates']] = $system;
	
	drawMap($map);
}

function drawMap( &$big_map )
{
	$colors = array( '#ff0000', '#33ccff', '#ffff99', '#66ff99', '#ffccff' ,'#cccccc', '#ff9900', '#ffcccc', '#3333ff', '#ff3333', '#33ff33', '#cc33cc' );
	$y_min = $x_min = 1000000;
	$y_max = $x_max = 0;
	$map = '';

	$big_map_indicies=array_keys($big_map);

	foreach ($big_map_indicies as $coord)
		{
		list($x,$y) = split( ',' , $coord );

		$x_min = min($x, $x_min);
		$x_max = max($x, $x_max);
		$y_min = min($y, $y_min);
		$y_max = max($y, $y_max);
		}

	// Actual building of the map.
	for ($y = $y_max; $y >= $y_min; $y--)
		{
		$south = $map_chunk = $north = '<tr align=center>';

		for ($x = $x_min; $x <= $x_max; $x++)
			{
			if (in_array($x.','.$y, $big_map_indicies))
				{
				$coordinates = $x.','.$y;

				$jumps = explode(' ', $big_map[$coordinates]['jumps']);

				// North jump
				if (in_array($x.','.($y+1), $jumps)) $south .= '<td align=center colspan=3><img src="images/vert.gif"></td>';
				else $south .= '<td colspan=3></td>';
				// South jump
				if (in_array($x.','.($y-1), $jumps)) $north .= '<td align=center colspan=3><img src="images/vert.gif"></td>';
				else $north .= '<td colspan=3></td>';
				// West jump
				if (in_array(($x-1).','.$y, $jumps)) $west = '<img src="images/horz.gif">';
				else $west = '';
				// East jump
				if (in_array(($x+1).','.$y, $jumps)) $east = '<img src="images/horz.gif">';
				else $east = '';

				if ($big_map[$coordinates]['owner'] == "")
					$icon = '<img border=0 src="images/planet.gif">';
				else
					$icon = '<img border=0 src="images/aliens/alien1'.$big_map[$coordinates]['player_number'].'.gif">';

				$map_chunk .= '<td align=left>'.$west.'</td><td align=center>'.
							  '<table border=0 cellspacing=0 cellpadding=0 width=70 height=70>'.
							  '<tr align=center valign=middle><td><font size=1>'.$big_map[$coordinates]['mineral'].
							  '<br>'.$big_map[$coordinates]['agriculture'].
							  '<br>(0)</td><td>'.$icon.'</td><td><font size=1>'.$big_map[$coordinates]['fuel'].
							  '<br>'.$big_map[$coordinates]['population'].
							  '<br>(0)</font></td></tr>'.
							  '<tr align=center><td colspan=3><font size=1 color='.$colors[$big_map[$coordinates]['player_number']].'>'.
							  $big_map[$coordinates]['name'].' '.$coordinates.'</font></td></tr></table>'.
							  '<td align=right>'.$east.'</td>';
				}
			else
				{
				$south .= '<td colspan=3></td>';
				$map_chunk .= '<td colspan=3></td>';
				$north .= '<td colspan=3></td>';
				}
			}

		$map .= $south.'</tr>'.$map_chunk.'</tr>'.$north.'</tr>';
		}

	echo '<p align=center><img class=spacerule src="images/spacerule.jpg" width=100% height=10>'.
		 '<p><table border=0 cellpadding=0 cellspacing=0>'.$map.'</table></p>';
}

#-----------------------------------------------------------------------------------------------------------------------------------------#
# Selects a system name per the systemNameSource parameter in the $server array.
#
	
function nameSystem(&$big_map)
{
	global $server;
	
	if ($server['systemNameSource'] == 'random')
		return randomName();
	else if ($server['systemNameSource'] != '')
		{
		// If the source is another string, it's the name of the SQL table where we'll find the name.
		// Of course, don't name your table 'random'. Duh.
		//
		// We could to an "ORDER BY RAND() LIMIT 1", but with the table on Iceberg being over 85 thousand records, that would
		// incur a *massive* performance penalty, since we're sorting the entire table for each query. For big maps, this
		// adds up to a very long wait. Hence, we find a random id (COUNT(*) is very fast) and pick out the word.
		$word_count = sc_query('SELECT COUNT(*) as c FROM '.$server['systemNameSource'], __FILE__.'*'.__LINE__);
		$line = $word_count->fetch_assoc();
		$random_id = rand(1, $line['c']);
		
		$select = sc_query('SELECT word FROM '.$server['systemNameSource'].' WHERE id = '.$random_id, __FILE__.'*'.__LINE__);
		$word = $select->fetch_assoc();
		
		return ucfirst($word['word']);
		}
	else
		return('System');
}
?>