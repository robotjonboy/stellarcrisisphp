<?php
#--------------------------------------------------------------------------------------------------------------------#
# Localize a galactic coordinate to a local one. Used in update_game().
#

function localize($galacticCoord, $player, $homeworlds)
{
	global $server;#, $homeworlds;

	if (!$server['local_coordinates'])
		return $galacticCoord; 

	list($x, $y) = explode(',', $galacticCoord);
	return ($x-$homeworlds[$player]['x']).','.($y-$homeworlds[$player]['y']);
}

#--------------------------------------------------------------------------------------------------------------------#
# Return values:
#
#	- 0: The game has updated and is now over.
#	- 1: Regular update has occured.
#

function update_game($series, &$game, $update_time)
{
	global $missive, $history, $server, $ship_types, $moving_ships, $mysqli;
	
	//ysql_query('BEGIN');
	
	// Commit any previous transactions. This ensures that future threads wait for the update
	// to finish since the semaphore will now be visible to them.
	#sc_query('COMMIT');
	
	// Start the update. If anything goes wrong, we'll end up in a pre-update state.
	// Note, if the update was triggered by ended turn, all the players in the game will have ended 
	// turn when they check their screen, leading to a bizarre situation. They would need to cancel their "end turn"
	// and end it again. This should be remedied (clearing the ended turn flag on rollback?).
	#sc_query('START TRANSACTION');

	// Update the game record to reflect that the update has occurred. We also update the $game array
	// so we don't need to reload it from the database.
	$game['update_count'] += 1;
	$game['last_update'] = $update_time;
	
	$query = 'UPDATE games SET update_count = '.$game['update_count'].', last_update = '.$game['last_update'].' WHERE id = '.$game['id'];
	sc_query($query);
	
	$history = array();
	$history[] = array('', '', 'update', $update_time);
	
	$homeworlds = array();
	$saved_map = array();
	$missive = array();
	$destroyed = array();

	// Lock tables so we can work in peace.
	// Don't forget to unlock them before we exit this function!
	/*$tables = array();
	$tables[] = 'diplomacies WRITE';
	$tables[] = 'empires WRITE';
	$tables[] = 'explored WRITE';
	$tables[] = 'fleets WRITE';
	$tables[] = 'games WRITE';
	$tables[] = 'players WRITE';
	$tables[] = 'series WRITE';
	$tables[] = 'ships WRITE';
	$tables[] = 'systems WRITE';
	$tables[] = 'messages WRITE';
	$tables[] = 'scouting_reports WRITE';
	$tables[] = 'invitations WRITE';
	$tables[] = 'bridier WRITE';
	$tables[] = 'history WRITE';
	$tables[] = 'gamelog WRITE';
	//we do not write to ship_types or words
	sc_query('LOCK TABLES '.implode(',', $tables), __FILE__.'*'.__LINE__);*/

	srand(time()); //cjp make rand() work properly

	// We check team draws and team surrenders before running the update because of the voting nature
	// of team diplomacy; if an emp gets eliminated during the turn it could change the voting.
	if ($series['team_game'])
	{
		// Get team diplomacy settings for team games - $team_offer index specifies the team being offered to.
		$team_offer = getTeamDiplomacy($game);
		
		// Test for draw.
		if ($team_offer[1] == 1 and $team_offer[2] == 1)
		{
			// Both teams choose to draw.
			$missive_update = '<font color=FF0000>'.
							$series['name'].' '.
							$game['game_number'].
							'</font> has ended in a draw.<br>';
			
			// Send a messages to the players, and record the event.
			$select = sc_query('SELECT * FROM players WHERE game_id = '.$game['id'], __FILE__.'*'.__LINE__);
			while ($player = $select->fetch_assoc())
			{
				$history[] = array('', $player['name'], 'draw', 'Team '.$player['team']);
				
				$empire = getEmpire($player['name']);
				sendEmpireMessage($empire, '<span class=red>'.$series['name'].' '.$game['game_number'].'</span> has ended in a draw.');
			}

			// Game's over... cleanup time.
			endGame($series, $game, $history, $saved_map);
			return 0;
		}

		// Check for surrenders- I *will* let both sides surrender at once if they really want! :P
		// $team specifies the team being offered TO-- i.e. the opposing team from the emp making the offer
		// The award of victory will happen normally at the end of the update
		for ($team = 1; $team <= 2; $team++)
		{
			$offering_team = ($team == 1 ? 2 : 1);

			if ($team_offer[$team] == '0')
			{				
				$select = sc_query('SELECT * FROM players WHERE game_id = '.$game['id'].' AND ABS(team) = '.$offering_team, __FILE__.'*'.__LINE__);
				while ($player = $select->fetch_assoc())
				{
					// Only players still left are actually surrendering
					if ( $player['team'] > 0 )
						$history[] = array( '', $player['name'], 'surrender', 'Team '.$player['team'] );
					
					sc_query('DELETE FROM ships WHERE game_id = '.$game['id'].' AND owner = "'.$player['name'].'"', __FILE__.'*'.__LINE__);
					sc_query('DELETE FROM fleets WHERE game_id = '.$game['id'].' AND owner = "'.$player['name'].'"', __FILE__.'*'.__LINE__);
					sc_query('DELETE FROM explored WHERE game_id = '.$game['id'].' AND empire = "'.$player['name'].'"', __FILE__.'*'.__LINE__);
					sc_query('DELETE FROM diplomacies WHERE game_id = '.$game['id'].' AND empire = "'.$player['name'].'"', __FILE__.'*'.__LINE__);

					// We can delete this here because we are sending the message and setting the nuked immediately (below)
					sc_query('DELETE FROM players WHERE id = '.$player['id'], __FILE__.'*'.__LINE__);
					sc_query('DELETE FROM scouting_reports WHERE player_id = "'.$player['id'].'"', __FILE__.'*'.__LINE__);
					sc_query('DELETE FROM messages WHERE player_id = "'.$player['id'].'"', __FILE__.'*'.__LINE__);
					sc_query('UPDATE games SET player_count = (player_count-1) WHERE id = '.$game['id'], __FILE__.'*'.__LINE__);
					
					if ($player['team'] > 0)
						sc_query('UPDATE empires SET nuked = (nuked + 1) WHERE name = "'.$player['name'].'"', __FILE__.'*'.__LINE__);

					$fields = array();
					$fields[] = 'homeworld = ""';
					$fields[] = 'owner = ""';
					$fields[] = 'population = 0';
					
					sc_query('UPDATE systems SET '.implode(',', $fields).' WHERE game_id = '.$game['id'].' AND owner = "'.$player['name'].'"');
				
					$empire = getEmpire($player['name']);
					sendEmpireMessage($empire, 'Your team has surrendered in <span class=red>'.$series['name'].' '.$game['game_number'].'</span>.');
				}

				$select = sc_query('SELECT * FROM players WHERE game_id = '.$game['id'].' AND team = '.$team, __FILE__.'*'.__LINE__);
				while ($player = $select->fetch_assoc())
				{
					$empire = getEmpire($player['name']);
					sendEmpireMessage($empire, 'The opposing team has surrendered in <span class=red>'.$series['name'].' '.$game['game_number'].'</span>.');
				}
			}
		}
	}

	$nuke = array();
	$colonize = array();
	$annihilate = array();
	$terraform = array();
	$invade = array();
	$send = array();
	$open = array();
	$close = array();
	$eliminated = array();
	$buffered_missive = array();
	$system_cache = array();

	/*$game['update_count'] += 1;
	$game['last_update'] = $update_time;

	$values = array();
	$values[] = 'update_count = '.$game['update_count'];
	$values[] = 'last_update = '.$game['last_update'];
	sc_query('UPDATE games SET '.implode(',', $values).' WHERE id = '.$game['id']);*/

	// Here we loop through each player for this game to see what's up with him, or her.
	$select = sc_query('SELECT * FROM players WHERE game_id = '.$game['id'].' AND team >= 0', __FILE__.'*'.__LINE__);
	while ($player = $select->fetch_assoc())
    	{
    		// Put the players homeworld coordinates in the homeworld array for localizing missives.
		// Note: If we need another coordinate space in this initial loop, we'll have to preload ALL the homeworld coordinates
	    $conditions   =  array();
		$conditions[] = 'game_id = '.$game['id'];
		$conditions[] = 'homeworld = "'.$player['name'].'"';
		
		$system_query = sc_query('SELECT coordinates FROM systems WHERE '.implode(' AND ', $conditions).' LIMIT 1', __FILE__.'*'.__LINE__);

		$system = $system_query->fetch_assoc();
		
		list($x, $y) = explode(',', $system['coordinates']);
		$homeworlds[$player['name']]['x'] = $x;
		$homeworlds[$player['name']]['y'] = $y;

		list($x, $y) = explode(',', $player['map_origin']);
		$homeworlds[$player['name']]['x'] -= $x;
		$homeworlds[$player['name']]['y'] -= $y;
		
		// If the player has been idle too many updates, he is now automatically ending his turn.
		$player['ended_turn'] = (($game['update_count'] - $player['last_update']) > $server['updates_to_idle'] ? 1 : 0);
		
		// Increment the player's tech level by what it's supposed to be incremented.
        	$player['tech_level'] += $player['tech_development'];

		// Inform the player if he has tech developments waiting.
	        if ($techsWaiting = techsWaiting(array('player_data' => $player)))
			$buffered_missive[$player['id']]['techWaiting'][] = 'You have '.$techsWaiting.' new tech development(s) waiting.';
			
		// For every diplomatic state this player has with another player, change the status to the new one
        	// if both empires agreed on it and inform him of his status in the update text.
	        $diplomacy_select = sc_query( 'SELECT * FROM diplomacies '.
												'WHERE game_id = '.$game['id'].' '.
												'AND empire = "'.$player['name'].'" '.
												'ORDER BY status ASC',
												__FILE__.'*'.__LINE__);

		while ($diplomacy = $diplomacy_select->fetch_assoc)
		{
			if ( strncmp('=Team', $diplomacy['opponent'], 5) ) // Ignore the team diplomacy settings
			{
				// Is the other player still around?
				if ($opponent = getDiplomacyWithOpponent($game['id'], 
														  $diplomacy['opponent'], 
														  $diplomacy['empire']
														 )
					)
				{
					// Prevent two people from surrendering to each other. In this case, revert to war for both.
					if ($diplomacy['offer'] == 0 and $opponent['offer'] == 0)
						sc_query('UPDATE diplomacies SET status = "2" WHERE id = '.$diplomacy['id'],
									   __FILE__.'*'.__LINE__);
					else if ($diplomacy['offer'] == 0)
					{
						// Handle surrender. If a player surrenders, the other player does not have to do anything.
						// When the update occurs (i.e., now!), the player surrendering is nuked.
						$eliminated[] = array('victim' => $diplomacy['empire'], 
												'doer' => $diplomacy['opponent'], 
												'method' => 'surrender');
					}
					else
					{
						if ($diplomacy['offer'] == 0 and $opponent['offer'] == 0) $new_status = 2;
						else if ($diplomacy['offer'] == 1 and $opponent['offer'] == 1) $new_status = 1;
						else if ($diplomacy['offer'] == 1 or $opponent['offer'] == 1) 
							$new_status = $diplomacy['status'];
						else $new_status = min($diplomacy['offer'], $opponent['offer']);
						
						if ($new_status > 1 and 
							$new_status != $diplomacy['status'] and 
							$new_status != $opponent['status']
							)							
							$history[] = array('', 
											   $diplomacy['empire'], 
											   diplomacyString($new_status), 
											   $diplomacy['opponent'] );

						if ($series['diplomacy'] == 6)
						{
							// Update the explored data during Shared HQ if we just got there.
							// After this, they will be added as they are explored.
							if ($new_status == 6)
								importExplored($player, getPlayer($game['id'], $diplomacy['opponent']));
							else if ($new_status == 5)
								convertSharedHQToScoutingReports($series, $game['update_count'], 
																$player, $diplomacy['opponent']);
						}
						$sql=	'UPDATE diplomacies '.
								'SET status = "'.$new_status.'" '.
								'WHERE id = '.$diplomacy['id'];	
						sc_query($sql, __FILE__.'*'.__LINE__);

						// Inform the player of this.
						$buffered_missive[$player['id']]['diplomaticStatus'][] = 
							diplomacyString($new_status).' with '.$diplomacy['opponent'].'.';
					}
				}
				else 
					sc_query('DELETE FROM diplomacies WHERE id = '.$diplomacy['id'], __FILE__.'*'.__LINE__);
			}
		}

	        // Update population for the player's planets.
		$conditions = array();
		$conditions[] = 'game_id = '.$game['id'];
		$conditions[] = 'owner = "'.$player['name'].'"';
		
		$new_population = 'LEAST(population*'.$player['agriculture_ratio'].'+1, max_population)';

		sc_query('UPDATE systems SET population = '.$new_population.' WHERE '.implode(' AND ', $conditions));

		// Prepare arrays used for this player's ship processing.
		$explored_planets = array();
		$built_ships = array();
		$dismantled_ships = array();

		// Go through this player's ships, in a random order.
		$conditions = array();
		$conditions[] = 'game_id = '.$game['id'];
		$conditions[] = 'owner = "'.$player['name'].'"';

	    // cjp rand() used this way is known not to work and it didn't at iceberg
		// perhaps it needs a seed to rand eg seed = time();  srand(seed);	
		// or call it twice to get it going 
		// or make rand work with a number based on #of rows and then loop through dbase
		// it wouldn't be quite as random but not too bad.
		// ** I put srand(time()); at start of the routine
		$ship_query = sc_query('SELECT * FROM ships WHERE '.
						implode(' AND ', $conditions).
						' ORDER BY RAND()');
	
		while ($ship = $ship_query->fetch_assoc())
        	{
        		// Instead of refetching system data for every ship yet another time when we process
			// their orders, we'll fetch it once here and cache it for later use. System data
			// does not change in this loop so this should be safe.
			if (!isset($system_cache[$ship['location']]))
			{
				$system = getSystem($game['id'], $ship['location']);
				$system_cache[$ship['location']] = $system;
			}		

			$system = $system_cache[$ship['location']];

			// Is this ship cloaked?
			$ignore_this_ship = $ship['cloaked'];

			// Adjust the ship's BR by the maintenance ratio.
			$ship['br'] = min($ship['max_br'], $ship['br']*$player['mineral_ratio']);

			// Get the orders of this ship and set the new orders to "Stand by".
			$orders = $ship['orders'];
			$order_arguments = $ship['order_arguments'];
			$ship['orders'] = 'standby';
			$ship['order_arguments'] = null;

			// Process orders that have arguments (order:argument).
			#list($field, $value) = explode(':', $orders);
			list($field, $value) = array($orders, $order_arguments);

			switch ($field)
			{
				case 'fleet':
					// The ship stays with the fleet after the update; reset its orders to reflect that.
					$ship['orders'] = $orders;
					$ship['order_arguments'] = $order_arguments;

					$fleet = getFleetByID($order_arguments);

					// The orders for this ship will be the fleet's orders.
					// The first check is for movement orders (an x,y coordinate).
					if (count(explode(',', $fleet['order_arguments'])) == 2)
						$ship['location'] = $fleet['order_arguments'];
					else if (!$ship['cloaked'])
					{
						if ($fleet['orders'] == 'colonize' and $ship['type'] == 'Colony')
							$colonize[ $ship['location'] ][] = $ship['id'];
						else if ($fleet['orders'] == 'terraform' and $ship['type'] == 'Terraformer')
							$terraform[ $ship['location'] ][] = $ship['id'];
						else if ($fleet['orders'] == 'invade' and $ship['type'] == 'Troopship')
							$invade[ $ship['location'] ][] = $ship['id'];
						else if ($fleet['orders'] == 'nuke')
							$nuke[ $ship['location'] ][] = $ship['id'];
					}
					break;
				case 'send':
					$send[ $ship['location'] ][ $value ] = $ship['id'];
					break;
				case 'open':
					$open[ $ship['location'] ][ $ship['id'] ] = $value;
					$missive[ $ship['location'] ]['neer'] = array();
					break;
		                case 'close':
					$close[ $ship['location'] ][ $ship['id'] ] = $value;
					$missive[ $ship['location'] ]['neer'] = array();
					break;
                		case 'explore':
		                case 'move':
					// We treat both moving and exploring here, due to similarity.

					// Update the ship's location.
					$ship['location'] = $value;
					
					if ($field == 'explore' and !explored($player, $value))
					{
						// Clean up any previous records. There really shouldn't be any, but while we're testing
						// the Shared HQ feature we need to be extra carefeul.
						$conditions = array();
						$conditions[] = 'player_id = "'.$player['id'].'"';
						$conditions[] = 'coordinates = "'.$value.'"';
	
						sc_query('DELETE FROM explored WHERE '.implode(' AND ', $conditions));
						
						$values = array();
						$values[] = 'series_id = '.$series['id'];
						$values[] = 'game_number = '.$game['game_number'];
						$values[] = 'game_id = '.$game['id'];
						$values[] = 'empire = "'.$player['name'].'"';
						$values[] = 'player_id = "'.$player['id'].'"';
						$values[] = 'coordinates = "'.$value.'"';
						$values[] = 'update_explored = "'.$game['update_count'].'"';
	
						sc_query('INSERT INTO explored SET '.implode(',', $values));
						
						// Share the wealth, if we have to.
						if ($series['diplomacy'] == 6)
							addExploredToFriends($player, $mysqli->insert_id);

						// Remove this planet from the scouting reports if it's there; we don't need it anymore.
						$conditions = array();
						$conditions[] = 'player_id = "'.$player['id'].'"';
						$conditions[] = 'coordinates = "'.$value.'"';

						sc_query('DELETE FROM scouting_reports WHERE '.implode(' AND ', $conditions));

						// Save the coordinates for the update report.
						$explored_planets[$value] = 1;
					}
						
					$destination = getSystem($game['id'], $value);
					
					if ($destination['owner'] != '' and $destination['owner'] != $player['name'])
					{
						// So the system belongs to someone else... Did we make first contact?
						if (!$diplomacy = getDiplomacyWithOpponent($game['id'], $player['name'], $destination['owner']))
						{
							// Yup, ship-to-system first contact.
							$fields = array();
							$fields[0] = 'series_id = '.$series['id'];
							$fields[1] = 'game_number = '.$game['game_number'];
							$fields[2] = 'game_id = '.$game['id'];
							$fields[3] = 'empire = "'.$player['name'].'"';
							$fields[4] = 'opponent = "'.$destination['owner'].'"';

							sc_query('INSERT INTO diplomacies SET '.implode(',', $fields));

							$fields[3] = 'empire = "'.$destination['owner'].'"';
							$fields[4] = 'opponent = "'.$player['name'].'"';

							sc_query('INSERT INTO diplomacies SET '.implode(',', $fields));

							$history[] = array($destination['coordinates'], $player['name'], 'ship to system', $destination['owner']);
							
							$missive[$destination['coordinates']]['first_contact'][$player['name']][] = 
								'You have had first contact with '.$destination['owner'].' in '.$destination['name'].' (*coord*) (ship to system).';
							
							$missive[$destination['coordinates']]['first_contact'][$destination['owner']][] = 
								'You have had first contact with '.$player['name'].' in '.$destination['name'].' (*coord*) (system to ship).';
						}
					}
				break; //case move
                	}

			// Process regular orders.
			switch ($orders)
			{
				case 'standby':
					break;
				case 'build':
					$built_ships[ $ship['location'] ][ $ship['type'] ]++;
					break;
				case 'dismantle':
					$dismantled_ships[ $ship['type'] ]++;
					$ignore_this_ship = 1;
					sc_query('DELETE FROM ships WHERE id = '.$ship['id']);
					break;
				case 'nuke':
					$nuke[ $ship['location'] ][] = $ship['id'];
					break;
				case 'cloak':
					$ship['cloaked'] = 1;
					$ignore_this_ship = 1;
					break;
				 case 'uncloak':
					$ship['cloaked'] = 0;
					$ignore_this_ship = 0;
					if ( $series['cloakers_as_attacks'] )
						$missive[$system['coordinates']]['uncloak'][$ship['owner']][] = $ship['name'];
					break;
				 case 'destroy':
					$annihilate[ $ship['location'] ][] = $ship['id'];
					break;
				 case 'terraform':
					$terraform[ $ship['location'] ][] = $ship['id'];
					break;
				 case 'invade':
					$invade[ $ship['location'] ][] = $ship['id'];
					break;
				 case 'colonize':
					$colonize[ $ship['location'] ][] = $ship['id'];
					break;
			}

			// Here, we finally update the ship's record in the database, if we haven't deleted it yet.
			if ($orders != 'dismantle')
			{
				$values = array();
				$values[] = 'cloaked = "'.$ship['cloaked'].'"';
				$values[] = 'location = "'.$ship['location'].'"';
				$values[] = 'br = "'.$ship['br'].'"';
				$values[] = 'orders = "'.$ship['orders'].'"';
				$values[] = 'order_arguments = '.($ship['order_arguments'] ? '"'.$ship['order_arguments'].'"' : '\'NULL\'');

				sc_query('UPDATE ships SET '.implode(',', $values).' WHERE id = '.$ship['id']);
			}
		} //end while ships to process

		# array[ (building location) ][ (ship type) ] = (number of ships built)
	    	foreach (array_keys($built_ships) as $location)
	    	{
			// This query is quite expensive just to get the system name! Fix this!
			#$system = getSystem($game['id'], $location, __FILE__.'*'.__LINE__);
			$system = $system_cache[$location];

			foreach (array_keys($built_ships[$location]) as $ship_type)
			{
    				$build_count = $built_ships[$location][$ship_type];
				// $missive is an array - made this a string //cjp
				$amissive  = $build_count;
				$amissive .= ' ';
				$amissive .= $ship_type;
				$amissive .= (in_array($ship_type, $moving_ships) ? ' ship' : ''); //ship or ship's
				$amissive .= ($build_count != 1 ? 's were' : ' was'); //was or were
				$amissive .= ' built in ';
				$amissive .= $system['name'];
				$amissive .= ' (';
				$amissive .= localize($location, $player['name'], $homeworlds); //co-ordinates
				$amissive .= ').';

				$buffered_missive[$player['id']]['builds'][] = $amissive;
			}
		}

		# array[ (ship type) ] = (number of ships dismantled)
		foreach (array_keys($dismantled_ships) as $ship_type)
    		{
			$buffered_missive[$player['id']]['dismantling'][] = $dismantled_ships[$ship_type].' '.$ship_type.
			(in_array($ship_type, $moving_ships) ? ' ship' : '').
			($dismantled_ships[$ship_type] != 1 ? 's were' : ' was').' dismantled.';
		}

		foreach (array_keys($explored_planets) as $location)
			{
			$system = getSystem($game['id'], $location, __FILE__.'*'.__LINE__);
			
			$buffered_missive[$player['id']]['exploration'][] = '<span class=updateExplore>'.
																($system['annihilated'] ? 'Remains of ' : '').$system['name'].' ('.
																localize($location, $player['name'], $homeworlds).') was explored.</span>';
			}
			
		$values = array();
		$values[] = 'tech_level = "'.$player['tech_level'].'"';
		$values[] = 'ended_turn = "'.$player['ended_turn'].'"';
		sc_query('UPDATE players SET '.implode(',', $values).' WHERE id = '.$player['id']);

		// Finally, we process orders for non-idle fleets. Their ships have acted already, but we need to update the fleets so their orders
		// appear correctly on the fleet screen. We don't check the validity of the fleet orders since in any case, the ships have acted
		// and their orders in turn have been validated. If a ship can move somewhere and it's part of a fleet, that fleet can too.
		$conditions = array();
		$conditions[] = 'game_id = '.$game['id'];
		$conditions[] = '(orders = "move" or orders = "explore")';
		$conditions[] = 'owner = "'.$player['name'].'"';
		
		sc_query('UPDATE fleets SET location = order_arguments WHERE '.implode(' AND ', $conditions));

		// All fleets are now on stand by.
		$conditions = array();
		$conditions[] = 'game_id = '.$game['id'];
		$conditions[] = 'owner = "'.$player['name'].'"';
		
		sc_query('UPDATE fleets SET orders = "standby", order_arguments = NULL WHERE '.implode(' AND ', $conditions));
		}

	$mined = array();
	$swept = array();
	$en = array();

	#####################################################################################################################################
	#
	// Battle calculations.
	$query = sc_query('SELECT * FROM systems WHERE game_id = '.$game['id'], __FILE__.'*'.__LINE__);
	while ($system = $query->fetch_assoc())
		{
		// save map data in case game ends this turn
		$saved_map[$system['coordinates']] = array();
		$saved_map[$system['coordinates']]['mineral'] = $system['mineral'];
		$saved_map[$system['coordinates']]['fuel'] = $system['fuel'];
		$saved_map[$system['coordinates']]['agriculture'] = $system['agriculture'];
		$saved_map[$system['coordinates']]['population'] = $system['population'];

		if ($system['annihilated'])
			$saved_map[$system['coordinates']]['owner'] = '=annihilated=';
		else if (!$system['owner'])
			$saved_map[$system['coordinates']]['owner'] = '=unowned=';
		else
			$saved_map[$system['coordinates']]['owner'] = $system['owner'];
	
		$conditions = array();
		$conditions[] = 'game_id = '.$game['id'];
		$conditions[] = 'location = "'.$system['coordinates'].'"';
		$conditions[] = 'cloaked = "0"';

		$emps_present = sc_query('SELECT DISTINCT owner FROM ships WHERE '.implode(' AND ',$conditions), __FILE__.'*'.__LINE__);

		$missive[$system['coordinates']]['dest'] = array();
		$missive[$system['coordinates']]['sight'] = array();

		// No battles if no one is here.
		if ($emps_present->num_rows == 0) continue;
		
		$empire_inventory = array();
		while ( $row = $emps_present->fetch_assoc() )
			$empire_inventory[] = $row['owner'];
		
		$saved_map[$system['coordinates']]['ships'] = array();
		$mined[$system['coordinates']] = false;
		$swept[$system['coordinates']] = false;
		
		$destroyed[$system['coordinates']] = array();
		#$sighted[$system['coordinates']] = array();

		$battle_points = array();
		$damage_received = array();

		// For each ship owner in this system, calculate their total battle points.
		foreach ($empire_inventory as $ship_owner)
			{
			$conditions = array();
			$conditions[] = 'game_id = '.$game['id'];
			$conditions[] = 'location = "'.$system['coordinates'].'"';
			$conditions[] = 'owner = "'.$ship_owner.'"';
			$conditions[] = 'cloaked = "0"';
			$select = sc_query('SELECT SUM(br*br) as s, COUNT(id) as c FROM ships WHERE '.implode(' AND ', $conditions), __FILE__.'*'.__LINE__);
			$line = $select->fetch_assoc();
			$battle_points[$ship_owner] = $line['s'];
			$damage_received[$ship_owner] = 0;

			$destroyed[$system['coordinates']][$ship_owner] = array();
			#$sighted[$system['coordinates']][$ship_owner] = array();

			// Set up arrays so that they are in a fixed, known order.
			foreach (array_keys($ship_types) as $type)
				{
				$destroyed[$system['coordinates']][$ship_owner][$type] = 0;
				#$sighted[$system['coordinates']][$ship_owner][$type] = 0;
				}
	
			$allegiances[$ship_owner] = allegiances($empire_inventory, $system, $series, $game, $ship_owner, $missive, $history);

			// get count of this players ships for the saved map data
			$saved_map[$system['coordinates']]['ships'][$ship_owner] = $line['c'];
			
			$player = getPlayer($game['id'], $ship_owner);
			
			if ($player['fuel_ratio'] < 1.0 and $player['fuel_ratio'] > 0)
				$battle_points[$ship_owner] *= $player['fuel_ratio'];
			}

		// Now allocate each players battle strength to the players they are fighting
		foreach ($empire_inventory as $ship_owner)
			{
			list($enemies, $allies) = $allegiances[$ship_owner];
			
			if (!count($enemies)) continue;
			
			$opposing_battle_points = 0;
			
			foreach ($enemies as $enemy) 
				$opposing_battle_points += $battle_points[$enemy];

			foreach ($enemies as $enemy) 
				$damage_received[$enemy] += $battle_points[$ship_owner]*($battle_points[$enemy]/$opposing_battle_points);
			}
		
		// Finally, have each player take the damage they are receiving
		foreach ($empire_inventory as $ship_owner)
			{
			$dest = $damage_received[$ship_owner]/2;

			// 5 Feb 2003-- changing so that fuel-ratio affects ability to take damage
			$player = getPlayer($game['id'], $ship_owner);
			$fuel_ratio = 1.0;
			if ( $player['fuel_ratio'] < 1.0 and $player['fuel_ratio'] > 0 )
				$fuel_ratio = $player['fuel_ratio'];

			$conditions = array();
			$conditions[] = 'game_id = '.$game['id'];
			$conditions[] = 'location = "'.$system['coordinates'].'"';
			$conditions[] = 'owner = "'.$ship_owner.'"';
			$conditions[] = 'cloaked = "0"';
			$conditions[] = 'type <> "Minefield"'; // As the FAQ states, minefields are immune to DEST.
			
			$select = sc_query('SELECT * FROM ships WHERE '.implode(' AND ', $conditions).' ORDER BY RAND()', __FILE__.'*'.__LINE__);

			while ( $ship = $select->fetch_assoc() )
				{
				if ( (pow((float)$ship['br'], 2)*$fuel_ratio) <= $dest )
					{
					// If we get here, this ship is toast.
					$missive[$system['coordinates']]['dest'][$ship_owner][] = $ship['name'];
					$destroyed[$system['coordinates']][$ship_owner][$ship['type']] += 1;

					// Some of the destructive power has been used up; adjust it.
					$dest -= pow((float)$ship['br'], 2) * $fuel_ratio;

					// Destroy the ship.
					sc_query('DELETE FROM ships WHERE id = '.$ship['id']);
					}
				}

			$conditions = array();
			$conditions[] = 'game_id = '.$game['id'];
			$conditions[] = 'location = "'.$system['coordinates'].'"';
			$conditions[] = 'owner = "'.$ship_owner.'"';
			$conditions[] = 'cloaked = "0"';
			$select = sc_query('SELECT SUM(br*br) as s FROM ships WHERE '.implode(' AND ', $conditions), __FILE__.'*'.__LINE__);
			$line = $select->fetch_assoc();
			if ($BP_remaining = ($line['s']*$fuel_ratio))
				{
				$damage_ratio = 1-(($damage_received[$ship_owner]/2 + $dest)/$BP_remaining);
				
				$conditions = array();
				$conditions[] = 'game_id = '.$game['id'];
				$conditions[] = 'location = "'.$system['coordinates'].'"';
				$conditions[] = 'owner = "'.$ship_owner.'"';
				$conditions[] = 'cloaked = "0"';
				
				if ($damage_ratio <= 0)
					{
					$select = sc_query('SELECT * FROM ships WHERE '.implode(' AND ', $conditions), __FILE__.'*'.__LINE__);
					while ($ship = $select->fetch_assoc())
						{
						$missive[$system['coordinates']]['dest'][$ship_owner][] = $ship['name'];
						$destroyed[$system['coordinates']][$ship_owner][$ship['type']] += 1;
						
						if ($ship['type'] == 'Minefield') 
							$mined[$system['coordinates']] = 1;
						}
						
					sc_query('DELETE FROM ships WHERE '.implode(' AND ', $conditions), __FILE__.'*'.__LINE__);
					}
				else
					{
					$select = sc_query('SELECT * FROM ships WHERE '.implode(' AND ', $conditions), __FILE__.'*'.__LINE__);
						
					while ( $ship = $select->fetch_assoc() )
						{
						$missive[$system['coordinates']]['sight'][$ship_owner][] = $ship['name'];
						#$sighted[$system['coordinates']][$ship_owner][$ship['type']] += 1;
						if ($ship['type'] == 'Minesweeper') 
							$swept[$system['coordinates']] = 1;
						}

					sc_query('UPDATE ships SET br = (br*'.$damage_ratio.') WHERE '.implode(' AND ', $conditions), __FILE__.'*'.__LINE__);
					}
				}
			}
		}
	#
	#####################################################################################################################################

    // Process minefields.
    foreach (array_keys($mined) as $location)
		{
		if ($mined[$location] and !$swept[$location])
            {
			$system = getSystem($game['id'], $location, __FILE__.'*'.__LINE__);

			// All the ships in this system are toast. Collect their names for posterity.
			$select = sc_query('SELECT * FROM ships WHERE game_id = '.$game['id'].' AND location = "'.$location.'"');
            while ($ship = $select->fetch_assoc())
				{
            	$missive[$location]['dest'][ $ship['owner'] ][ $ship['id'] ] = $ship['name'];
				$destroyed[$system['coordinates']][$ship['owner']][$ship['type']] += 1;
				}

			// Blow them up; no minesweepers were able to save them.
            sc_query('DELETE FROM ships WHERE game_id = '.$game['id'].' AND location = "'.$location.'"');

			// Exploding minefields destroy half the population.
			sc_query('UPDATE systems SET population = '.max($system['population']/2, 1).' WHERE id = '.$system['id']);

			$missive[$location]['sys'] = '<span class=updateNuke>A minefield exploded in '.$system['name'].' (*coord*).</span>';
			$history[] = array( $system['coordinates'], '', 'minefield', '' );
            }
		}

	// Process stargate orders.
    // send[ (origin) ][ (destination) ] = (ship ID of the stargate)
	foreach (array_keys($send) as $origin)
        foreach (array_keys($send[$origin]) as $destination)
            {
            if ($stargate = getShipByID($send[$origin][$destination]))
                {
				$conditions = array();
				$conditions[] = 'game_id = '.$game['id'];
				$conditions[] = 'owner = "'.$stargate['owner'].'"';
				$conditions[] = 'location = "'.$stargate['location'].'"';
				$conditions[] = 'FIND_IN_SET(type, "Stargate,Minefield,Satellite") = 0';
				$sql=	'UPDATE ships '.
						'SET location = "'.$destination.'" '.
						'WHERE '.implode(' AND ', $conditions);
				sc_query($sql, __FILE__.'*'.__LINE__);

				// Transport any fleets present in this location as well. The ships will have moved already.
				$conditions = array();
				$conditions[] = 'game_id = '.$game['id'];
				$conditions[] = 'owner = "'.$stargate['owner'].'"';
				$conditions[] = 'location = "'.$stargate['location'].'"';
				$sql=	'UPDATE fleets '.
						'SET location = "'.$destination.'" '.
						'WHERE '.implode(' AND ', $conditions);
				sc_query($sql, __FILE__.'*'.__LINE__);
				}
        	}

	// Process nukes.
    foreach (array_keys($nuke) as $location)
        {
        $system = getSystem($game['id'], $location, __FILE__.'*'.__LINE__);

		foreach ($nuke[$location] as $ship_id)
			{
			if ($ship = getShipByID($ship_id))
            	{
            	// Make sure the nuker is at war with the other empire.
            	$diplomacy = getDiplomacyWithOpponent($game['id'], $ship['owner'], $system['owner']);      	
            	if ($diplomacy['status'] != 2) continue;
					
            	// Record the nuke event.
                $missive[$location]['sys'] = '<font class=updateNuke>'.$system['name'].' (*coord*) was nuked by '.
											 (strlen($ship['name']) ? $ship['name'].' of '.$ship['owner'] : $ship['owner']).'.</font>';
				$history[] = array( $location, $ship['owner'], 'nuked', $ship['name'] );

				// If it's a homeworld, save the elimination for later...
				if ($system['homeworld'] != '')
					$eliminated[] = array('victim' => $system['homeworld'], 'doer' => $ship['owner'], 'method' => 'nuked');

				// ...and update the planet info.
                $values = array();
                $values[] = 'population = 0';
                $values[] = 'owner = ""';
                $values[] = 'homeworld = ""';
                $values[] = 'mineral = (mineral/2)';
                $values[] = 'fuel = (fuel/2)';
                $values[] = 'agriculture = (agriculture/2)';
                $values[] = 'max_population = GREATEST(1, max_population/2)';
                sc_query('UPDATE systems SET '.implode(',', $values).' WHERE id = '.$system['id'], __FILE__.'*'.__LINE__);

				// Exit the loop; no one can nuke this system again in this update.
				break;
                }
			}
        }

	// Process Doomsday actions.
    foreach (array_keys($annihilate) as $location)
        {
        $system = getSystem($game['id'], $location, __FILE__.'*'.__LINE__);

		foreach ($annihilate[$location] as $ship_id)
			{
			if ($ship = getShipByID($ship_id))
                {
                // Document the annihilation.
                $missive[$location]['sys'] = '<font class=updateNuke>'.$system['name'].' (*coord*) was annihilated by '.
											 (strlen($ship['name']) ? $ship['name'].' of '.$ship['owner'].'.' : $ship['owner']).'.</font>';
				$history[] = array( $location, $ship['owner'], 'annihilated', $ship['name'] );

				// If it's a homeworld, save the elimination for later...
				if ($system['homeworld'] != '')
					$eliminated[] = array('victim' => $system['homeworld'], 'doer' => $ship['owner'], 'method' => 'annihilated');

				// ...and update the planet info.
                $values = array();
                $values[] = 'mineral = 0';
                $values[] = 'fuel = 0';
                $values[] = 'agriculture = 0';
                $values[] = 'max_population = 0';
                $values[] = 'population = 0';
                $values[] = 'owner = ""';
                $values[] = 'annihilated = "1"';
                $values[] = 'homeworld = ""';
				#$values[] = 'name = 'Remains of System'';
                sc_query('UPDATE systems SET '.implode(',', $values).' WHERE id = '.$system['id'], __FILE__.'*'.__LINE__);

				// Exit the loop; no one can annihilate this system again in this update.
				break;
                }
        	}
        }

	// Process invasion actions.
	foreach (array_keys($invade) as $location)
        {
        // Assume no success for this system.
		$invasion_successful = false;

        $system = getSystem($game['id'], $location, __FILE__.'*'.__LINE__);

		// Can't invade if it's been nuked or obliterated.
		// An obliterated planet should not have the owner set; we could probably remove the first check...
        if ($system['annihilated'] or $system['owner'] == '') continue;

        foreach ($invade[$location] as $ship_id)
            {
			if ($ship = getShipByID($ship_id))
                {
            	// Make sure the nuker is at war with the other empire.
            	$diplomacy = getDiplomacyWithOpponent($game['id'], $ship['owner'], $system['owner']);      	
            	if ($diplomacy['status'] != 2) continue;
				
				if ($ship['br']*10 > $system['population'])
                    {
					$invasion_successful = true;

                    // Record the invasion.
                    $missive[$location]['sys'] = '<font class=updateInvade>'.$system['name'].' (*coord*) was successfully invaded by '.
                    							 ( strlen($ship['name']) ? $ship['name'].' of '.$ship['owner'] : $ship['owner'] ).
												 '.</font>';

					$history[] = array($location, $ship['owner'], 'invaded', $ship['name']);
					
					// If it's a homeworld, save the elimination for later...
					if ($system['homeworld'] != '')
						$eliminated[] = array('victim' => $system['homeworld'], 'doer' => $ship['owner'], 'method' => 'invaded');
					
					// ...and update the planet info to reflect the new ownership.
					$fields = array();
					$fields[] = 'homeworld = ""';
					$fields[] = 'owner = "'.$ship['owner'].'"';
					$fields[] = 'population = '.round($system['population']/2);
					sc_query('UPDATE systems SET '.implode(',', $fields).' WHERE id = '.$system['id'], __FILE__.'*'.__LINE__);

					// Ensure we have this planet explored if we invade it - this removes oddities in Shared HQ games.
					// The reasonning? If you dropped from Shared HQ as you were trooping, you'd lose the planet if you hadn't explored
					// it yourself. But you INVADED it. So you see it. End of story. :)
					if ($series['diplomacy'] == 6)
						{
						$player = getPlayer($game['id'], $ship['owner']);
						
						$conditions = array();
						$conditions[] = 'player_id = "'.$player['id'].'"';
						$conditions[] = 'coordinates = "'.$system['coordinates'].'"';
	
						sc_query('DELETE FROM explored WHERE '.implode(' AND ', $conditions), __FILE__.'*'.__LINE__);
							
						$values = array();
						$values[] = 'series_id = '.$series['id'];
						$values[] = 'game_number = '.$game['game_number'];
						$values[] = 'game_id = '.$game['id'];
						$values[] = 'empire = "'.$ship['owner'].'"';
						$values[] = 'player_id = "'.$player['id'].'"';
						$values[] = 'coordinates = "'.$system['coordinates'].'"';
						$values[] = 'update_explored = "'.$game['update_count'].'"';

						sc_query('INSERT INTO explored SET '.implode(',', $values), __FILE__.'*'.__LINE__);

						addExploredToFriends($player, $mysqli->insert_id);
						}
                    }
                else
                    {
                    $missive[$location]['sys'] = '<font class=updateFailedInvasion>'.$system['name'].
												 ' (*coord*) was unsuccessfully invaded by '.
												 ( strlen($ship['name']) ? $ship['name'].' of '.$ship['owner'] : $ship['owner'] ).'.</font>';

					$history[] = array( $location, $ship['owner'], 'unsuccessfully invaded', $ship['name'] );
					
					$system['population'] -= floor(2*$ship['br']);
					
					sc_query('UPDATE systems SET population = '.$system['population'].' WHERE id = '.$system['id'], __FILE__.'*'.__LINE__);
                    }

				// Discard the ship.
				sc_query('DELETE FROM ships WHERE id = '.$ship['id'], __FILE__.'*'.__LINE__);
				
				// Exit the loop for this system only if our invasion was successful.
				if ($invasion_successful) break;
                }
			}
		}

	// Process colonization actions.
   foreach (array_keys($colonize) as $location)
        {
        $system = getSystem($game['id'], $location, __FILE__.'*'.__LINE__);

		// Can't colonize if the planet has been obliterated.
        if ($system['annihilated']) continue;

        foreach ($colonize[$location] as $ship_id)
			{
			if ($ship = getShipByID($ship_id))
            	{
                $missive[$location]['sys'] = '<font class=updateColonize>'.$system['name'].' (*coord*) was colonized by '.
											 ( strlen($ship['name']) ? $ship['name'].' of '.$ship['owner'] : $ship['owner'] ).'.</font>';
				$history[] = array( $location, $ship['owner'], 'colonized', $ship['name'] );

				$initial_population = max( pow((float)$ship['br'], 2), 1);
				
				// reset max pop in case previous owner had it set too low.
		    	$max_pop = max(1, max($system['mineral'], $system['fuel']));

                sc_query(	'UPDATE systems SET'.
								' population = '.$initial_population.
								', owner = "'.$ship['owner'].
								'", max_population = '.$max_pop.
								' WHERE id = '.$system['id'],
								__FILE__.'*'.__LINE__);

				// Ensure we have this planet explored - this removes oddities in Shared HQ games.
				// The reasonning? If you dropped from Shared HQ as you were colonizing, you'd lose the planet if you hadn't explored
				// it yourself. But you COLONIZED it. So you see it. End of story. :)
				if ($series['diplomacy'] == 6)
					{
					$player = getPlayer($game['id'], $ship['owner']);
					
					$conditions = array();
					$conditions[] = 'player_id = "'.$player['id'].'"';
					$conditions[] = 'coordinates = "'.$system['coordinates'].'"';

					sc_query('DELETE FROM explored WHERE '.implode(' AND ', $conditions), 
									__FILE__.'*'.__LINE__);
						
					$values = array();
					$values[] = 'series_id = '.$series['id'];
					$values[] = 'game_number = '.$game['game_number'];
					$values[] = 'game_id = '.$game['id'];
					$values[] = 'empire = "'.$ship['owner'].'"';
					$values[] = 'player_id = "'.$player['id'].'"';
					$values[] = 'coordinates = "'.$system['coordinates'].'"';
					$values[] = 'update_explored = "'.$game['update_count'].'"';
					sc_query('INSERT INTO explored SET '.implode(',', $values), __FILE__.'*'.__LINE__);
					addExploredToFriends($player, $mysqli->insert_id);
					}

				sc_query('DELETE FROM ships WHERE id = '.$ship['id'], __FILE__.'*'.__LINE__);

				// This planet has been colonized; nothing more can happen.
				break;
                }
        	}
        }

	// Process terraforming actions.
    foreach (array_keys($terraform) as $location)
        {
		$system = getSystem($game['id'], $location, __FILE__.'*'.__LINE__);

		// Too late?
		if ($system['annihilated']) continue;

		$terraform_count = 0;

		foreach ($terraform[$location] as $ship_id)
			{
			if ($ship = getShipByID($ship_id))
				{
				$system['agriculture'] = min($system['agriculture']+floor($ship['br']*10), max($system['mineral'], $system['fuel']));

                $terraform_count++;
				$history[] = array($location, $ship['owner'], 'terraformed', $ship['name']);

				sc_query('UPDATE systems SET agriculture = '.$system['agriculture'].' WHERE id = '.$system['id'], __FILE__.'*'.__LINE__);
                sc_query('DELETE FROM ships WHERE id = '.$ship['id'], __FILE__.'*'.__LINE__);
				}

			// Exit the loop for this system if it can't be terraformed anymore.
			if ($system['agriculture'] >= max($system['mineral'], $system['fuel'])) break;
			}

		// Report the terraforming if any was done.
		if ($terraform_count)
			{
			switch ($terraform_count)
				{
				case 1: $terraform_count_string = ''; break;
				case 2: $terraform_count_string = 'twice '; break;
				default: $terraform_count_string = $terraform_count.' times ';
				}
				
			$missive[$location]['sys'] = '<span class=updateTerraform>'.$system['name'].' (*coord*) '.'was terraformed '.
										 $terraform_count_string.'by '.$ship['owner'].'.</span>';
			}
        }

    // Process "close" orders for enginner.
    foreach (array_keys($close) as $location)
        {
		// Get the list of jumps for the system where the enginner is.
		$system = getSystem($game['id'], $location, __FILE__.'*'.__LINE__);

		// If there are no jumps, we must explicitly set $jumps to be a null array as explode
		// will return an array containing the input string if the separator is not found!
		$jumps = ($system['jumps'] == '' ? array() : explode(' ', $system['jumps']));

        foreach (array_keys($close[$location]) as $ship_id)
            {
            $ship = getShipByID($ship_id);

            // If the ship exists, has enough BR and the jump to be closed exists...
            if ($ship and $ship['br'] >= $server['engineer_br_loss'] and in_array($close[$location][$ship_id], $jumps))
                {
				// So the enginner CAN close the requested jump.
				// Here we get the list of jumps for the other sytem; the one about to be close off.
				$system2 = getSystem($game['id'], $close[$location][$ship_id], __FILE__.'*'.__LINE__);
				$jumps2 = ($system2['jumps'] == '' ? array() : explode(' ', $system2['jumps']));

				// Remove the remote system from this system's jumps and this system from the remote one's jumps.
                #unset($jumps[$system2['coordinates']], $jumps2[$system['coordinates']]);
                
				// Same as above.
				$jumps = array_remove($close[$location][$ship_id], $jumps);
               	$jumps2 = array_remove($system['coordinates'], $jumps2);

				// Update the database.
                sc_query('UPDATE systems SET jumps = "'.implode(' ', $jumps).'" WHERE id = '.$system['id'], __FILE__.'*'.__LINE__);
				sc_query('UPDATE systems SET jumps = "'.implode(' ', $jumps2).'" WHERE id = "'.$system2['id'].'"', __FILE__.'*'.__LINE__);

				$new_maxbr = $ship['max_br'] - $server['engineer_br_loss'];

				if ($new_maxbr < $server['engineer_br_loss'])
					{
					// Destroy the ship.
					$engineer_consumed = true;
                    sc_query('DELETE FROM ships WHERE id = '.$ship['id'], __FILE__.'*'.__LINE__);
					}
				else
					{
					// Cripple the ship.
					$engineer_consumed = false;

					$fields = array();
					$fields[] = 'br = (br - '.$server['engineer_br_loss'].')';
					$fields[] = 'max_br = '.$new_maxbr;
					sc_query('UPDATE ships SET '.implode(', ', $fields).' WHERE id = '.$ship_id, __FILE__.'*'.__LINE__);
					}

				$action = array();
				$action['type'] = 'close';
				$action['target'] = $close[$location][$ship_id];
				$action['vessel'] = ($ship['name'] ? $ship['name'].' of '.$ship['owner'] : $ship['owner']);
				$action['consumed'] = $engineer_consumed;

                $missive[$location]['neer'][] = $action;
				$history[] = array( $location, $ship['owner'], 'closed', $close[$location][$ship_id].'/'.$ship['name'].'/'.($engineer_consumed?'yes':'no') );
                }
            }
        }

    // Process "open" orders for enginner.
    foreach (array_keys($open) as $location)
        {
		// Get the list of jumps for the system where the enginner is.
		$system = getSystem($game['id'], $location, __FILE__.'*'.__LINE__);

		// If there are no jumps, we must explicitly set $jumps to be a null array as explode
		// will return an array containing the input string if the separator is not found!
		$jumps = ($system['jumps'] == '' ? array() : explode(' ', $system['jumps']));

        foreach (array_keys($open[$location]) as $ship_id)
            {
			$ship = getShipByID($ship_id);
			if ($ship and $ship['br'] >= $server['engineer_br_loss'] and !in_array($open[$location][$ship_id], $jumps))
                {
				// So the enginner CAN open the requested jump.
				// Here we get the list of jumps from the other system; the one about to be open up.
				$system2 = getSystem($game['id'], $open[$location][$ship_id], __FILE__.'*'.__LINE__);
				$jumps2 = ($system2['jumps'] == '' ? array() : explode(' ', $system2['jumps']));

				// Add the remote system to this system's jumps and this system to the remote one's jumps.
                $jumps[] = $open[$location][$ship_id];
				$jumps2[] = $system['coordinates'];

				// Update the database.
                sc_query('UPDATE systems SET jumps = "'.implode(' ', $jumps).'" WHERE id = '.$system['id'], __FILE__.'*'.__LINE__);
				sc_query('UPDATE systems SET jumps = "'.implode(' ', $jumps2).'" WHERE id = '.$system2['id'], __FILE__.'*'.__LINE__);

				$new_maxbr = $ship['max_br'] - $server['engineer_br_loss'];

				if ($new_maxbr < $server['engineer_br_loss'])
					{
					// Destroy the ship.
					$engineer_consumed = true;
                    sc_query('DELETE FROM ships WHERE id = '.$ship['id'], __FILE__.'*'.__LINE__);
					}
				else
					{
					// Cripple the ship.
					$engineer_consumed = false;

					$fields = array();
					$fields[] = 'br = (br - '.$server['engineer_br_loss'].')';
					$fields[] = 'max_br = '.$new_maxbr;
					sc_query('UPDATE ships SET '.implode(', ', $fields).' WHERE id = '.$ship_id, __FILE__.'*'.__LINE__);
					}

				$action = array();
				$action['type'] = 'open';
				$action['target'] = $open[$location][$ship_id];
				$action['vessel'] = ($ship['name'] ? $ship['name'].' of '.$ship['owner'] : $ship['owner']);
				$action['consumed'] = $engineer_consumed;

                $missive[$location]['neer'][] = $action;
				$history[] = array( $location, $ship['owner'], 'opened', $open[$location][$ship_id].'/'.$ship['name'].'/'.($engineer_consumed?'yes':'no') );
                }
            }
        }

	// Update history with destroyed and sightings
	foreach (array_keys($destroyed) as $location)
		foreach (array_keys($destroyed[$location]) as $owner)
			{
			$total_ships = 0;
			$ship_list = '';
			foreach ($destroyed[$location][$owner] as $type=>$count)
				{
				if ( $count > 0 )
					{
					// put in separating comma if there are preceeding ships in the list
					if ( $total_ships > 0 )
						$ship_list .= ', ';
					$total_ships += $count;
					$ship_list .= $count.' '.$type;
					}
				}

			if ( $total_ships > 0 )
				{
/*
					if ( $total_ships > 1 )
					$ship_list .= ' ships';
				else
					$ship_list .= ' ship';
*/
				$history[] = array( $location, $owner, 'destroyed', $ship_list );
				}
			}
/*
	foreach (array_keys($sighted) as $location)
		foreach (array_keys($sighted[$location]) as $owner)
		{
			$total_ships = 0;
			$ship_list = '';
			foreach ($sighted[$location][$owner] as $type=>$count)
			{
				if ( $count > 0 )
				{
					// put in separating comma if there are preceeding ships in the list
					if ( $total_ships > 0 )
						$ship_list .= ', ';
					$total_ships += $count;
					$ship_list .= $count.' '.$type;
				}
			}
			if ( $total_ships > 0 )
			{
				if ( $total_ships > 1 )
					$ship_list .= ' ships';
				else
					$ship_list .= ' ship';
				$history[] = array( $location, $owner, 'sighted', $ship_list );
			}
		}
*/
	
	// Sort the history array so all the information for each system ends up together
	sort( $history );

	$general_missive = '';
	
	// Now clean up the eliminated players.
	if (count($eliminated) > 0)
		foreach ($eliminated as $nuke)
			{
			switch ($nuke['method'])
				{
				case 'surrender':
					$method = 'surrendered to '.$nuke['doer'];
					$history[] = array( '', $nuke['victim'], 'surrender', '' );
					break;
				default:
					$method = 'were '.$nuke['method'].' out by '.$nuke['doer'];
					$history[] = array( '', $nuke['victim'], $nuke['method'].' out', $nuke['doer'] );
					break;
				}

			$empire = getEmpire($nuke['victim']);
			sendEmpireMessage($empire, 'You '.$method.' in <span class=red>'.$series['name'].' '.$game['game_number'].'</span>.');

			sc_query('UPDATE empires SET nuked = (nuked+1) WHERE name = "'.$nuke['victim'].'"', __FILE__.'*'.__LINE__);
			sc_query('UPDATE empires SET nukes = (nukes+1) WHERE name = "'.$nuke['doer'].'"', __FILE__.'*'.__LINE__);
			sc_query('UPDATE games SET player_count = (player_count-1) WHERE id = '.$game['id'], __FILE__.'*'.__LINE__);
			$game['player_count'] -= 1;
			
			$player = getPlayer($game['id'], $nuke['victim']);

			// Clean up.
			if ($series['team_game'])
				{
				// Set team to a negative number but leave player in list for team games so that we can award the team win later.
				sc_query('UPDATE players SET team = (-team) WHERE id = '.$player['id'], __FILE__.'*'.__LINE__);
				}
			else
				{
				// Otherwise, just go ahead an remove the player from the table.
				sc_query('DELETE FROM players WHERE id = "'.$player['id'].'"', __FILE__.'*'.__LINE__);
				}
			
			sc_query('DELETE FROM ships WHERE game_id = "'.$game['id'].'" AND owner = "'.$nuke['victim'].'"', __FILE__.'*'.__LINE__);
			sc_query('DELETE FROM fleets WHERE game_id = "'.$game['id'].'" AND owner = "'.$nuke['victim'].'"', __FILE__.'*'.__LINE__);
			sc_query('DELETE FROM explored WHERE game_id = "'.$game['id'].'" AND empire = "'.$nuke['victim'].'"', __FILE__.'*'.__LINE__);
			sc_query('DELETE FROM scouting_reports WHERE player_id = "'.$player['id'].'"', __FILE__.'*'.__LINE__);
			sc_query('DELETE FROM messages WHERE player_id = "'.$player['id'].'"', __FILE__.'*'.__LINE__);

			$conditions = array();
			$conditions[] = 'game_id = '.$game['id'];
			$conditions[] = '(empire = "'.$nuke['victim'].'" OR opponent = "'.$nuke['victim'].'")';
			sc_query('DELETE FROM diplomacies WHERE '.implode(' AND ', $conditions), __FILE__.'*'.__LINE__);

			$values = array();
			$values[] = 'homeworld = ""';
			$values[] = 'owner = ""';	
			$values[] = 'population = 0';			
			sc_query('UPDATE systems SET '.implode(',', $values).' WHERE game_id = '.$game['id'].' AND owner = "'.$nuke['victim'].'"', __FILE__.'*'.__LINE__);
			
			$general_missive .= $nuke['doer'].' has obliterated '.$nuke['victim'].'.<br>';
			}

	// Check for empires that are falling into ruins and send the relevant missive to players.
    $select = sc_query('SELECT * FROM players WHERE game_id = '.$game['id'].' AND team >= 0', __FILE__.'*'.__LINE__);
    while ($player = $select->fetch_assoc())
        {
		if ( ($game['update_count']-$player['last_update']) > $server['updates_to_ruin'] )
            {
            $empire = getEmpire($player['name']);
			sendEmpireMessage($empire, 'You fell into ruin in <span class=red>'.$series['name'].' '.$game['game_number'].'</span>.');
			
			$general_missive .= $player['name'].' has fallen into ruins<br>';
			$history[] = array( '', $player['name'], 'ruins', '' );
           	
            // Update the player's record to reflect his ruinage and remove him from the game.
            sc_query('UPDATE empires SET ruined = (ruined+1) WHERE name = "'.$player['name'].'"', __FILE__.'*'.__LINE__);
            
			sc_query('UPDATE games SET player_count = (player_count-1) WHERE id = '.$game['id'], __FILE__.'*'.__LINE__);
				
			$game['player_count'] -= 1;
		
			// Nuke the player's homeworld.
			$fields = array();
			$fields[] = 'mineral = (mineral/2)';
			$fields[] = 'fuel = (fuel/2)';
			$fields[] = 'agriculture = (agriculture/2)';
			$fields[] = 'max_population = GREATEST(1, max_population/2)';
			$fields[] = 'population = 0';
			$fields[] = 'homeworld = ""';
			$fields[] = 'owner = ""';

			$conditions = array();
			$conditions[] = 'game_id = '.$game['id'];
			$conditions[] = 'homeworld = "'.$player['name'].'"';

			sc_query('UPDATE systems SET '.implode(',', $fields).' WHERE '.implode(' AND ', $conditions), __FILE__.'*'.__LINE__);

			// Remove ownership and homeworld status for his planets.
			$fields = array(); $conditions = array();
			$fields[] = 'owner = ""';
			$fields[] = 'homeworld = ""';
			$fields[] = 'population = 0';

			$conditions = array();
			$conditions[] = 'game_id = '.$game['id'];
			$conditions[] = 'owner = "'.$player['name'].'"';
			sc_query('UPDATE systems SET '.implode(', ', $fields).' WHERE '.implode(' AND ', $conditions), __FILE__.'*'.__LINE__);

			// Delete the player, or move to inactive in a team game,
			if ($series['team_game'])
				sc_query('UPDATE players SET team = -team WHERE id = '.$player['id'], __FILE__.'*'.__LINE__);
			else
				sc_query('DELETE FROM players WHERE id = '.$player['id'], __FILE__.'*'.__LINE__);

			// Delete his ships and fleets.
			$conditions = array();
			$conditions[0] = 'game_id = '.$game['id'];
			$conditions[1] = 'owner = "'.$player['name'].'"';
			sc_query('DELETE FROM ships WHERE '.implode(' AND ', $conditions), __FILE__.'*'.__LINE__);
			sc_query('DELETE FROM fleets WHERE '.implode(' AND ', $conditions), __FILE__.'*'.__LINE__);

			// ...related diplomatic records...
			$conditions[1] = '(empire = "'.$player['name'].'" OR opponent = "'.$player['name'].'")';
			sc_query('DELETE FROM diplomacies WHERE '.implode(' AND ', $conditions), __FILE__.'*'.__LINE__);

			// ... and his explored map data...
			$conditions[1] = 'empire = "'.$player['name'].'"';
			sc_query('DELETE FROM explored WHERE '.implode(' AND ', $conditions), __FILE__.'*'.__LINE__);

			// ... and his scouting reports.
			sc_query('DELETE FROM scouting_reports WHERE player_id = '.$player['id'], __FILE__.'*'.__LINE__);

			// ... and his messages.
			sc_query('DELETE FROM messages WHERE player_id = '.$player['id'], __FILE__.'*'.__LINE__);
			}

		// Dump the missive array to each player, depending on whether they can see the planet.
		if(is_array($missive)){ //cjp
		foreach (array_keys($missive) as $location) 
		{
			// Skip if the player hasn't explored this planet.
			if ( !explored($player, $location) ) continue;

			$system = getSystem($game['id'], $location, __FILE__.'*'.__LINE__);

			// This is where we list the ships that uncloaked.
			if ( array_key_exists( 'uncloak', $missive[$location] ) )
				foreach (array_keys($missive[$location]['uncloak']) as $ship_owner)
					{
					// Skip if there are no ships to see.
					if (!count($missive[$location]['uncloak'][$ship_owner])) continue;

					$missive_update = '<font class=updateUncloak>'.implode(', ', $missive[$location]['uncloak'][$ship_owner]).
									  ' of '.$ship_owner.' uncloaked in '.
									  ($system['annihilated'] ? 'Remains of ' : '').$system['name'].' ('.
									  localize($location, $player['name'], $homeworlds).').</font>';

					$buffered_missive[$player['id']]['destruction'][] = $missive_update;
					}

			// This is where we list the ships that were destroyed.
			if (is_array($missive[$location]['dest']))
				{
				foreach (array_keys($missive[$location]['dest']) as $ship_owner)
					{
					// Skip if there are no ships to see.
					if (!count($missive[$location]['dest'][$ship_owner])) continue;
	
					$verb = ( count($missive[$location]['dest'][$ship_owner]) == 1 ? 'was' : 'were');
	
					$missive_update = '<font class=updateDestroy>'.implode(', ', $missive[$location]['dest'][$ship_owner]).
									  ' of '.$ship_owner.' '.$verb.' destroyed in '.
									  ($system['annihilated'] ? 'Remains of ' : '').$system['name'].' ('.
									  localize($location, $player['name'], $homeworlds).').</font>';
	
					$buffered_missive[$player['id']]['destruction'][] = $missive_update;
					}
				}
				
			// This is where we list the ships that were seen.
			if (is_array($missive[$location]['sight']))
				{
				foreach (array_keys($missive[$location]['sight']) as $ship_owner)
					{
					// Skip if there are no ships to see.
					if (!count($missive[$location]['sight'][$ship_owner])) continue;
	
					// Skip if we are looking at the empire's own ships.
					if ($ship_owner == $player['name']) continue;
	
					$verb = ( count($missive[$location]['sight'][$ship_owner]) == 1 ? 'was' : 'were');
	
					$missive_update = implode(', ', $missive[$location]['sight'][$ship_owner]).' of '.
									  $ship_owner.' '.$verb.' sighted in '.
									  ($system['annihilated'] ? 'Remains of ' : '').$system['name'].' ('.
									  localize($location, $player['name'], $homeworlds).').';
	
					$buffered_missive[$player['id']]['sightings'][] = $missive_update;
					}
				}
				
			// This is where we list first contacts.
			if (is_array($missive[$location]) && array_key_exists('first_contact', $missive[$location]) && count($missive[$location]['first_contact']))
				foreach (array_keys($missive[$location]['first_contact']) as $empire_name)
					{
					// Skip if this message is not for this empire.
					if ($empire_name != $player['name'] or $empire_name == '') continue;

					$message = str_replace('*coord*', localize($location, $player['name'],$homeworlds), $missive[$location]['first_contact'][$empire_name]);
					$missive_update = '<font class=updateFirstContact>'.implode('<br>', $message).'</font>';

					$buffered_missive[$player['id']]['first_contact'][] = $missive_update;
					}

			// Report actions performed in this system.
			if (array_key_exists('sys', $missive[$location]) && $missive[$location]['sys'])
				{
				$missive_update = str_replace('*coord*', localize($location, $player['name'], $homeworlds), $missive[$location]['sys']);

				$buffered_missive[$player['id']]['actions'][] = $missive_update;
				}

			// Add information about jump openings and closings
			if (array_key_exists('neer', $missive[$location]) && count($missive[$location]['neer']) > 0 )
				foreach ( $missive[$location]['neer'] as $action )
					{
					if ( $action['consumed'] )
						$missive_update = '<font class=updateEngineer>The effort to '.$action['type'].' the jump from '.
										  '('.localize($location,$player['name'],$homeworlds).') to ('.localize($action['target'],$player['name'],$homeworlds).') '.
										  'has consumed '.$action['vessel'].'.</font>';
					else
						$missive_update = '<font class=updateEngineer>The jump from ('.localize($location,$player['name'],$homeworlds).') to '.
										  '('.localize($action['target'],$player['name'],$homeworlds).') was '.
										  ($action['type'] == 'open' ? 'opened' : 'closed').' by '.$action['vessel'].'.</font>';

					$buffered_missive[$player['id']]['engineers'][] = $missive_update;
					}
			}
		} //if(is_array($missive))

		// don't reclaculate for eliminated players
		if ($player = getPlayerByID($player['id']))
			recalculateRatios(array('series_data' => $series, 'game_data' => $game, 'player_data' => $player, 'empire_data' => getEmpire($player['name'])));
		}

	// One last update to the general missive.
	$select = sc_query('SELECT * FROM players WHERE game_id = '.$game['id'].' AND ended_turn = "1" AND team >= 0', __FILE__.'*'.__LINE__);
	if ($idling_empires = $select->num_rows)
		$general_missive .= $idling_empires.' empire'.($idling_empires > 1 ? 's are' : ' is').' idling.<br>';

	if ($general_missive != '')
		foreach (array_keys($buffered_missive) as $player_id)
			$buffered_missive[$player_id]['general'][] = $general_missive;

	foreach (array_keys($buffered_missive) as $player_id)
		{
		$complete_missive = '';
		$last_type = '';
		
		$message_types = array( 'techWaiting', 'diplomaticStatus', 'builds', 'dismantling', 'exploration', 'destruction', 
								'sightings', 'first_contact', 'actions', 'engineers', 'general' );
		
		#foreach (array_keys($buffered_missive[$player_id]) as $type)
		foreach ($message_types as $type)
		{
			if (array_key_exists($type, $buffered_missive[$player_id]) && count($buffered_missive[$player_id][$type]) > 0) {
				foreach ($buffered_missive[$player_id][$type] as $message) {
					if ($last_type != '' and $type != $last_type)
						$complete_missive .= '<br>';
					
					$complete_missive .= ($last_type != '' ? '<br>' : '').$message;
					
					$last_type = $type;
				}
			}
		}
				
		$values = array();
		$values[] = 'time = '.$update_time;
		$values[] = 'sender = "Update '.$game['update_count'].'"';
		$values[] = 'player_id = '.$player_id;
		$values[] = 'text = "'.urlencode($complete_missive).'"';
		$values[] = 'type = "update"';
		
		$query = 'INSERT INTO messages SET '.implode(',', $values);

		sc_query($query, __FILE__.'*'.__LINE__);
		}

	$series = getSeries($series['id']);
	$game = getGame($series['id'], $game['game_number']);

	// If any of these are still equal to 1 after the following checks, it means the game is over/drawn.
    $game_over = 1;
    $draw = 1;

	// Check to see if everyone is allied. If so, $game_over will still be equal to 1 after this loop.
	// This will also check if two players have chosen to draw the game; a special case for grudge games.
	// But first, check to see if there is only one player left. In this case, the game is over.
	if ($game['player_count'] <= 1)
		{
		$game_over = 1;
		$draw = 0;
		}
	else
		{
		$conditions = array();
		$conditions[] = 'game_id = '.$game['id'];
		$conditions[] = 'team >= 0';
		$player_select = sc_query('SELECT * FROM players WHERE '.implode(' AND ', $conditions), __FILE__.'*'.__LINE__);
		while ($player = $player_select->fetch_assoc())
			{
			$conditions = array();
			$conditions[] = 'game_id = '.$game['id'];
			$conditions[] = 'empire = "'.$player['name'].'"';
			$conditions[] = 'opponent NOT LIKE "=Team_="';
			$diplomacy_select = sc_query('SELECT * FROM diplomacies WHERE '.implode(' AND ', $conditions), __FILE__.'*'.__LINE__);

			// First check: does this player have diplomatic relations with everyone else? If not, we can't end this game
			// since he hasn't met everyone yet.
			if ( $diplomacy_select->num_rows < ($game['player_count']-1) )
				{
				// Break if we get here; this player has not relations with everyone: they have to meet! No need to continue searching.
				$game_over = 0;
				$draw = 0;
				break;
				}

			// Is everyone still at war? For grudge games, this will always fail. The players must nuke each other.
			// In games where you have to ally, the only way to end the game is for the last players to be allied.
			while ($diplomacy = $diplomacy_select->fetch_assoc())
				{
				if ($diplomacy['status'] != 1) $draw = 0;
				if ($diplomacy['status'] < 5) $game_over = 0;

				// No need to continue if both variables have been set to 0.
				if (!$draw and !$game_over) break;
				}
			}
		}

	// Handle the case where both players chose to draw the game. (Team game draw is handled at the beginning of update_game)
	if ($game['closed'] and $draw)
        {
		$message = '<font color=FF0000>'.$series['name'].' '.$game['game_number'].' has ended in a draw.</font><br>';

		// The game is closed and both players chose to draw.
		$select = sc_query('SELECT * FROM players WHERE game_id = '.$game['id'], __FILE__.'*'.__LINE__);
		while ($player = $select->fetch_assoc())
			{
			$empire = getEmpire($player['name']);
			sendEmpireMessage($empire, '<span class=red>'.$series['name'].' '.$game['game_number'].'</span> has ended in a draw.');
									
			$history[] = array( '', $player['name'], 'draw', '' );
			}

		// Game's over... cleanup time.
		endGame( $series, $game, $history, $saved_map );
		
		return 0;
        }

    if ($game['closed'] and $game_over)
        {
		// The game is closed and it seems that everyone left has won.
		// Inform the remaining players that they have won.

        $winning_team = 0;
		$select = sc_query('SELECT * FROM players WHERE game_id = '.$game['id'].' AND team >= 0', __FILE__.'*'.__LINE__);
		while ($player = $select->fetch_assoc())
			{
			$empire = getEmpire($player['name']);
		
			if ($series['team_game']) $winning_team = $player['team'];

			$history[] = array( '', $player['name'], 'won', ($series['team_game']?'Team '.$player['team']:'') );
			
			sc_query('UPDATE empires SET wins = (wins+1) WHERE name = "'.$player['name'].'"', __FILE__.'*'.__LINE__);

			sendEmpireMessage($empire, 'You have won <span class=red>'.$series['name'].' '.$game['game_number'].'</span>.');

			// do Bridier-- we can do this inside the loop ONLY becuase Bridier games have just one winner
			// code currently relies on $player containing the info for that winner
			if ( $game['bridier'] >= 0 )
				{
				$bridier_query = sc_query('SELECT * FROM bridier WHERE game_id = '.$game['id'], __FILE__.'*'.__LINE__);
				$bdata = $bridier_query->fetch_assoc();
				if ( $player['name'] == $bdata['empire1'] )
					{
					$opponent = getEmpire( $bdata['empire2'] );
					// have to calculate twice-- once against each empires starting socre!
					list( $a, $b ) = calculateBridier( $empire['bridier_rank'], $empire['bridier_index'], $bdata['starting_rank2'], $bdata['starting_index2'] );
					$win = $a;
					list( $a, $b ) = calculateBridier( $bdata['starting_rank1'], $bdata['starting_index1'], $opponent['bridier_rank'], $opponent['bridier_index'] );
					$lose = $b;
					$winner = '1';
					$loser = '2';
					}
				else
					{
					$opponent = getEmpire( $bdata['empire1'] );
					// have to calculate twice-- once against each empires starting socre!
					list( $a, $b ) = calculateBridier( $empire['bridier_rank'], $empire['bridier_index'], $bdata['starting_rank1'], $bdata['starting_index1'] );
					$win = $a;
					list( $a, $b ) = calculateBridier( $bdata['starting_rank2'], $bdata['starting_index2'], $opponent['bridier_rank'], $opponent['bridier_index'] );
					$lose = $b;
					$winner = '2';
					$loser = '1';
					}

				// update bridier results table
				$values = array();
				$values[] = 'winner = '.$winner;
				$values[] = 'ending_rank'.$winner.' = '.($empire['bridier_rank']+$win);
				$values[] = 'ending_rank'.$loser.' = '.($opponent['bridier_rank']-$lose);
				$values[] = 'end_time = '.time();
				sc_query('UPDATE bridier SET '.implode(', ',$values).' WHERE game_id = '.$game['id'], __FILE__.'*'.__LINE__);
				
				// and then update the empire records
				
				$new_index = $empire['bridier_index'] - ( $win>25 ? 50 : 2*$win );
				
				$binfo = $bdata['starting_rank'.$winner].','.$empire['bridier_rank'].",$win,".($empire['bridier_rank']+$win).
					'/'.$bdata['starting_index'.$winner].','.$empire['bridier_index'].','.(2*$win).",$new_index";
				$history[] = array( '', $bdata['empire'.$winner], 'bridier', $binfo );
				
				$values = array();
				$values[] = 'bridier_rank = '.($empire['bridier_rank']+$win);
				$values[] = 'bridier_index = '.( $new_index<100 ? 100 : $new_index );
				$values[] = 'bridier_update = '.time();
				$values[] = 'bridier_delta = '.$win;
				sc_query('UPDATE empires SET '.implode(', ',$values).' WHERE id = '.$empire['id'], __FILE__.'*'.__LINE__);

				$new_index = $opponent['bridier_index'] - ( $lose>25 ? 50 : 2*$lose );

				$binfo = $bdata['starting_rank'.$loser].','.$opponent['bridier_rank'].",$lose,".($opponent['bridier_rank']-$lose).
					'/'.$bdata['starting_index'.$loser].','.$opponent['bridier_index'].','.(2*$lose).",$new_index";
				$history[] = array( '', $bdata['empire'.$loser], 'bridier', $binfo );
				
				$values = array();
				$values[] = 'bridier_rank = '.($opponent['bridier_rank']-$lose);
				$values[] = 'bridier_index = '.( $new_index<100 ? 100 : $new_index );
				$values[] = 'bridier_update = '.time();
				$values[] = 'bridier_delta = -'.$lose;
				sc_query('UPDATE empires SET '.implode(', ',$values).' WHERE id = '.$opponent['id'], __FILE__.'*'.__LINE__);
				}
			}
			
		// Now process any previously nuked players in team games-- winning team has wins coming,
		// and losing team gets to know how the game turned out
		if ($winning_team > 0)
			{
			$select = sc_query('SELECT * FROM players WHERE game_id = '.$game['id'].' AND team = (-'.$winning_team.')');
			while ($player = $select->fetch_assoc())
				{
				$empire = getEmpire($player['name']);
		
				$history[] = array( '', $player['name'], 'won', ($series['team_game']?'Team '.$winning_team.' (nuked)':''));

				sc_query('UPDATE empires SET wins = (wins+1) WHERE name = "'.$player['name'].'"', __FILE__.'*'.__LINE__);
				
				sendEmpireMessage($empire, 'Your team has won <span class=red>'.$series['name'].' '.$game['game_number'].'</span>.');
				}
	
			$losing_team = ($winning_team == 1 ? 2 : 1);
			
			$select = sc_query('SELECT * FROM players WHERE team = (-'.$losing_team.') AND game_id = '.$game['id']);
			while ($player = $select->fetch_assoc())
				{
				$empire = getEmpire($player['name']);				
				sendEmpireMessage($empire, 'Your team has lost <span class=red>'.$series['name'].' '.$game['game_number'].'</span>.');
				}
			}


		// Game's over... cleanup time.
		endGame( $series, $game, $history, $saved_map );
		return 0;
        }
	
	if (!$game['closed'] and $game['update_count'] >= $server['updates_to_close'])
		{
		// We updated enough; the game is now closed
		sc_query('UPDATE games SET closed = "1" WHERE id = '.$game['id'], __FILE__.'*'.__LINE__);

		// remove all invitations for a closed game
		sc_query('DELETE FROM invitations WHERE game_id = '.$game['id'], __FILE__.'*'.__LINE__);
		
		// remove unused planets in a pre-built map
		if ( $series['map_type'] == 'prebuilt' )
			sc_query('DELETE FROM systems WHERE game_id = '.$game['id'].' AND system_active = "0"', __FILE__.'*'.__LINE__);
        }

	write_history($game, $history);
    #sc_query('UNLOCK TABLES', __FILE__.'*'.__LINE__);
    
	return 1;
}

#
################################################################################################################################
# This function goes through the ship list and checks to see if the player is at war with their owners.
# Depending on what we find, we add their names to the player's ennemy or ally list.
#

function allegiances($emps_there, $system, $series, $game, $player_name, &$missive, &$history)
{
	$enemies = array();
	$allies = array();

	foreach ( $emps_there as $potential_enemy)
		{
		// Skip if this ship is the player's.
		if ($player_name == $potential_enemy) continue;

		if (!$diplomacy = getDiplomacyWithOpponent($game['id'], $player_name, $potential_enemy))
			{
			$values = array();
			$values[0] = 'series_id = '.$series['id'];
			$values[1] = 'game_number = '.$game['game_number'];
			$values[2] = 'game_id = '.$game['id'];
			$values[3] = 'empire = "'.$player_name.'"';
			$values[4] = 'opponent = "'.$potential_enemy.'"';
			sc_query('INSERT INTO diplomacies SET '.implode(', ', $values), __FILE__.'*'.__LINE__);

			$history[] = array( $system['coordinates'], $player_name, 'ship to ship', $potential_enemy );

			// *coord* gets replaced with the appropriate string.
			$missive[$system['coordinates']]['first_contact'][$player_name][] =
				'You have had first contact with '.$potential_enemy.' in (*coord*) (ship to ship).';

			$values[3] = 'empire = "'.$potential_enemy.'"';
			$values[4] = 'opponent = "'.$player_name.'"';
			sc_query('INSERT INTO diplomacies SET '.implode(', ', $values), __FILE__.'*'.__LINE__);

			$missive[$system['coordinates']]['first_contact'][$potential_enemy][] =
				'You have had first contact with '.$player_name.' in (*coord*) (ship to ship).';

			$diplomacy['status'] = 2;
			}

		if ($diplomacy['status'] == 2) $enemies[] = $potential_enemy;
		else if ($diplomacy['status'] >= 3) $allies[] = $potential_enemy;
		}

	return array($enemies, $allies);
}
/*
Change log
2007-02-01 added srand(time()); at start of update routine to see it it makes rand() work properly.
colonization was not randon on iceberg. It seemed to go to the lower alphabetic player


*/
?>
