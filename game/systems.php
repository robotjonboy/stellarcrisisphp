<?php
function systemsScreen($vars)
{
	global $server;

	$series = $vars['series_data'];
	$game = $vars['game_data'];
	$player = $vars['player_data'];
	$empire = $vars['empire_data'];
	$coordinates = $vars['zoomed_planet'];
	$cjp_check_all = $vars['cjp_check_all'];
	
	// If the system we want to show has not been explored, it's probably from a scouting report.
	// Go to the related screen instead.
	if ($coordinates != '')
	{
		$gCoordinates = xlateToGalactic($coordinates);
		if (!explored($player, $gCoordinates))
			return systemsScreen_fromScoutingReport($vars);
	}
		
	gameHeader($vars, 'Systems');
	ratios($player);
	echo '<div><table width="100%" border=0 style="text-align: center;">';
	echo "*******".$vars['cjp_check_all'].$cjp_check_all;

	$x = 0; // Used to track how many lines we've fetched.
	$ship_inventory = shipInventory($series, $player);
	$population_adjustment = populationAdjustment($player);

	$icons = array();
	$system_ids = array();
	
	$fields = array();
	$fields[] = 'systems.name';
	$fields[] = 'systems.coordinates';
	$fields[] = 'systems.owner';
	$fields[] = 'systems.annihilated';
	$fields[] = 'systems.coordinates';
	$fields[] = 'systems.jumps';
	$fields[] = 'systems.population';
	$fields[] = 'systems.max_population';
	$fields[] = 'systems.agriculture';
	$fields[] = 'systems.mineral';
	$fields[] = 'systems.fuel';
	$fields[] = 'systems.homeworld';
	$fields[] = 'systems.id';
	
	$from_conditions = array();
	$from_conditions[] = 'systems.game_id = explored.game_id';
	$from_conditions[] = 'systems.coordinates = explored.coordinates';
	$from = 'systems INNER JOIN explored ON ('.implode(' AND ', $from_conditions).')';
	
	$conditions = array();
	$conditions[] = 'explored.player_id = '.$player['id'].'';
	// Specific system requested from the map screen.
	if ($coordinates)
	{
		$conditions[] = 'explored.coordinates = "'.xlateToGalactic($coordinates).'"';
	}
	
	$select = sc_query('SELECT DISTINCT '.implode(',', $fields).
							' FROM '.$from.
							' WHERE '.implode(' AND ', $conditions).
							' ORDER BY explored.id');	
	while ($system = $select->fetch_assoc())
	{
		if ($system['owner'] == $empire['name'])
		{
			$system_ids[] = $system['id']; 	// We cache those for systemScreen_processing(). 
											// ONLY IF WE HAVE EDITABLE FIELDS!!!
		}
		// We're now going to set a few variables so that the output can be done more cleanly below.
		
		// Set some fields to be editable, if the system belongs to the user.
		if ($system['owner'] == $empire['name'])
		{
			$system_name = '<input type=text '. 
									'size=20 '.
									'maxlength=20 '.
									'name="system_name:'.$system['id'].'" '.
									'value="'.$system['name'].'">';
			$system_max_pop = '&nbsp;'.
								'<input type=text '.
										'size=4 '.
										'maxlength=5 '.
										'name="system_population:'.$system['id'].'" '.
										'value="'.$system['max_population'].'">';
		}
		else
		{
			$system_name = ($system['annihilated'] ? 
				'Remains of ' : '').$system['name'];
				$system_max_pop = '';
		}

		// Determine the icon of this planet. We cache results for empire-owned planets so we can reuse them.
		// This control structure is prioritized according to the probability of what we're looking for, although
		// I'm not sure if this helps the page load faster.
		if ($system['owner'] == $empire['name'])
		{
			$system_icon = '<img src="images/aliens/'.$empire['icon'].'" '.
							'width=40 '.
							'height=40>';
		}
		else if ($system['owner'] == '')
		{
			$system_icon = '<img src="images/planet.gif">';
		}
		else if ($system['owner'])
		{
			if (!isset($icons[$system['owner']]))
			{
				$other_empire = getEmpire($system['owner']);
				$icons[$system['owner']] = $other_empire['icon'];
			}

			$system_icon = '<img src="images/aliens/'.$icons[$system['owner']].'" '.
									'width=40 '.
									'height=40>';
		}
		else if ($system['annihilated'])
		{
			$system_icon = '<img src="images/annihilated.gif">';
		}
		
		// If we have ships on this system, the row count for it will be larger. Since the first cell is spanning
		// the whole record, we need to determine how many rows we'll have. The logic here is that we have one
		// row for the system's stats (e.g., minerals, fuel) and the rest is for the ship inventory, one per empire.
		if (isset($ship_inventory[$system['coordinates']]))
		{
			$rowspan = ' rowspan='."2";#(count(array_keys($ship_inventory[$system['coordinates']]))+1);
		}
		else
		{
			$rowspan = '';
		}
		// If this system has jumps, explode the list, translate to local space and implode back to a space-seperated list.
		if ($system['jumps'])
		{
			$jump_list = '('.implode(')<br>(', 
								array_map('xlateToLocal', 
									explode(' ', $system['jumps']))).')';
		}
		else
		{
			$jump_list = '(none)';
		}
		// No scouting reports in blood games.
		if ($series['diplomacy'] != 2)
		{
			//cjp  add checked if check all
			if ($cjp_check_all)
				$cjp_check= 'checked ';
			else
				$cjp_check = '';
			$cjp_check= 'checked '; //force until can pass check_all

			$scout_report_checkbox = '<input '.
								'type=checkbox '.
								'name="scouting_systems[]" '.
								'value="'.xlateToLocal($system['coordinates']).'" '.
								$cjp_check.
								'>'.
								'<br>'.
								'<span class=smallText>'.
								   'Include in<br>scouting report'.
								'</span>';
		}
		else
		{
			$scout_report_checkbox = '';
		}
		if ($system['owner'] and $system['owner'] != $empire['name'])
		{
			$system_owner = '<br>'.
							'<span class=smallText>'.
							   'Owner: '.$system['owner'].
							'</span>';
		}
		else
		{
			$system_owner = '';
		}
		
		$system_icon_and_name = $system_icon.
								'<br>'.
								'<span class=smallText>'.
								   $system_name.
								'</span>'.$system_owner.
								'<br>'.
								'<span class=smallText>'.
								   'Location: '.xlateToLocal($system['coordinates']).
								'</span>';

		$population = ($system['population']+
								$population_adjustment[$system['coordinates']]);

		if ($system['homeworld'] == $empire['name'])
		{
			$new_origin = '<p>'.
						'New homeworld coordinates: '.
						'<input type=hidden '.
						'name="old_origin" '.
						'value="'.xlateToLocal($system['coordinates']).
						'">'.
						'<input type=text '.
								'size=11 '.
								'maxlength=11 '.
								'name="system_origin" '.
								'value="'.xlateToLocal($system['coordinates']).
						'">'.
						'<p>';
		}
		else
		{
			$new_origin = '';
		}
?>
	<tr valign=top>
		<td style="text-align: center; 
					vertical-align: middle;" 
					<?php echo $rowspan; ?>
		>
			<?php echo $scout_report_checkbox; ?>
		</td>
		<td style="text-align: center;" 
			<?php echo $rowspan; ?>
			>
			<?php echo $system_icon_and_name; ?>
		</td>
		<td style="text-align: left;">
			<span class=white>Minerals:</span> 
				<?php echo $system['mineral'].' '; ?>
			<span class=white>Fuel:</span> 
				<?php echo $system['fuel'].' '; ?>
			<span class=white>Agriculture:</span> 
				<?php echo $system['agriculture'].' '; ?>
			<span class=white>Population:</span> 
				<?php echo $population.$system_max_pop.$new_origin; ?>
		</td>
		<td<?php echo $rowspan; ?>>
			<span class=white>
				Jumps:
			</span>
			<br>
			<?php echo $jump_list; ?>
		</td>
	</tr>
<?php
		if (isset($ship_inventory[$system['coordinates']]))
		{
			echo '<tr><td>';
			foreach (array_keys($ship_inventory[$system['coordinates']]) as $owner)
			{
				#echo '<tr><td style="text-align: left;">'.$owner.': ';
				echo '<div style="text-align: left;">'.$owner.': ';

				foreach ($ship_inventory[$system['coordinates']][$owner] 
					as $ship_type => $ship_count)
				{
					echo '<span class=white>'.$ship_type.'</span> ('.$ship_count.') ';
				}
				#echo '</td></tr>';
				echo '</div>';
			}
			echo '</td></tr>';
		}

		// We insert a seperator line between each system but not at the end of the table.
		if (++$x != $select->num_rows)
		{
			echo '<tr>'.
					'<td colspan=4>'.
						'<img class=spaceruleThin src="images/spacerule.jpg">'.
					'</td>'.
				'</tr>';
		}
	}

	// No scouting reports in blood games.
	if ($series['diplomacy'] != 2)
	{
		echo '<tr>'.
				'<th colspan=2>'.
					'<br>'.
					'<input type=submit '.
							'name=gameAction '.
							'value="Scouting Report">'.
				'</th>'.
				'<td colspan=2>'.
					'<br>'.
					'<input type=submit '.
							'name=gameAction '.
							'value="Full Scouting Report">'.

				'</td>'.
			'</tr>';
		//cjp show check all button then value =systems
	}
	echo '</table>'.
			'</div>'.
				'<input type=hidden '.
						'name=system_ids '.
						'value="'.implode('&', $system_ids).'">';
	footer();
}

#----------------------------------------------------------------------------------------------------------------------#

function systemsScreen_fromScoutingReport($vars)
{
	$player = $vars['player_data'];
	$coordinates = xlateToGalactic($vars['zoomed_planet']);
	
	$select = sc_query('SELECT * '.
								'FROM scouting_reports '.
								'WHERE player_id = '.$player['id'].
								' AND coordinates = "'.$coordinates.'"');	
	$system = $select->fetch_assoc();
	
	$system['name'] = ($system['annihilated'] ? 'Remains of ' : '').$system['name'];

	if ($system['owner'] == '') $icon = '<img src="images/planet.gif">';
	else if ($system['annihilated']) $icon = '<img src="images/annihilated.gif">';
	else
	{
		$owner = getEmpire($system['owner']);
		$icon = '<img src="images/aliens/'.$owner['icon'].'">';
	}

	gameHeader($vars, 'Systems');

	ratios($player);
?>
<div>
<table width="100%" border=0>
	<tr valign=middle>
		<td rowspan=7 style="text-align: center; vertical-align: top;"><?php echo $icon; ?>
			<div class=smallText><?php echo $system['name']; ?></div>
			<?php echo ($system['owner'] ? 
				'<div class=smallText>Owner: '.$system['owner'].'</div>' :
				''); ?>
			<div class=smallText>
				Location: 
				<?php echo xlateToLocal($system['coordinates']); ?>
			</div>
		</td>
		<td style="padding-bottom: 10pt; vertical-align: top;">
			<span class=white>Minerals:</span> 
				<?php echo $system['mineral']; ?>
			<span class=white>Fuel:</span> 
				<?php echo $system['fuel']; ?>
			<span class=white>Agriculture:</span> 
				<?php echo $system['agriculture']; ?>
			<span class=white>Population:</span> 
				<?php echo $system['population']; ?>
		</td>
	</tr>
	<tr>
		<td style="padding-bottom: 10pt; padding-top: 10pt; vertical-align: top; border-top: 1pt dashed white;">
			<span class=white>
				Jumps:
			</span>
			&nbsp;
			<?php echo ($system['jumps'] ? 
						'('.str_replace(' ',') (', localizeJumps($system['jumps'])).')' :
						'(none)'); ?>
		</td>
	</tr>
<?php
	if ($system['ships'])
		{
?>
	
	<tr>
		<td style="padding-bottom: 10pt; 
					padding-top: 10pt; 
					vertical-align: top; 
					border-top: 1pt dashed white;">
			<div class=white>Ships:</div>
			<?php echo stripslashes(urldecode($system['ships'])); ?>
		</td>
	</tr>
<?php
		}

	if ($system['comment'])
		{
?>
	<tr>
		<td style="padding-top: 10pt; 
					vertical-align: top; 
					border-top: 1pt dashed white;">
			<div class=white>Comments:</div>
			<?php echo stripslashes(urldecode($system['comment'])); ?>
		</td>
	</tr>
<?php
		}
?>
</table>
</div>
<?php
	footer();
}

#----------------------------------------------------------------------------------------------------------------------#

function systemsScreen_processing($vars)
{
	global $hwX, $hwY, $mysqli;

	$series = $vars['series_data'];
	$game = $vars['game_data'];
	$player = $vars['player_data'];
	$empire = $vars['empire_data'];
		
	// Only go in here if the requested map origin is different from the old one.
	if ($vars['old_origin'] != $vars['system_origin'])
		{
		if (sscanf($vars['system_origin'], "%d,%d", $x, $y) == 2)
			{
			if ($x > -9999 and $x < 9999 and $y > -9999 and $y < 9999)
				{
				sc_query('UPDATE players '.
								'SET map_origin = "'.$x.','.$y.'" '.
								'WHERE id = '.$player['id']);

				// Reset $hwX and $hwY so that the next screen will draw with the new coordinates.
				// We can do this here because we won't play with coordinates again until we draw the new screen.
				list($hwX, $hwY) = explode(',', xlateToGalactic($vars['old_origin']));

				$hwX -= $x;
				$hwY -= $y;

				sendGameMessage($player, 'Homeworld coordinates reset to ('.
											$x.','.$y.').');
				}
			}
		else
			sendGameMessage($player, $vars['system_origin'].
							' is not a valid coordinate.');
		}

	// Don't loop blindly through the whole POST array. We cached the system IDs on the system screen.
	$affected_rows = 0;
	foreach (explode('&', $vars['system_ids']) as $id)
		{
		// I've seen it too-- but how does it happen???
		if ($id == '') continue;
		
		$values = array();
		$values[] = 'name = "'.htmlspecialchars($vars['system_name:'.$id]).'"';
		$values[] = 'max_population = "'.max(1, $vars['system_population:'.$id]).'"';

		sc_query('UPDATE systems '.
						'SET '.implode(',', $values).
						' WHERE id = '.$id.
						' AND owner = "'.$player['name'].'"');
		$affected_rows += $mysqli->affected_rows;
		}

	if ($affected_rows)
		{
		recalculateTargetPopulation($player);
		recalculateRatios($vars);
		}
}

#----------------------------------------------------------------------------------------------------------------------#

function recalculateTargetPopulation($player)
{
	$select = sc_query('SELECT SUM(max_population) as m FROM systems WHERE game_id = '.$player['game_id'].' AND owner = "'.$player['name'].'"');
	$line = $select->fetch_assoc();
	sc_query('UPDATE players SET max_population = '.$line['m'].' WHERE id = '.$player['id']);
}

#----------------------------------------------------------------------------------------------------------------------#
# For a given player, returns an array describing what ships are present on a system he sees.
#
# $ship_inventory: array[ coordinates ][ owner ][ ship_type ] = quantity
#

function shipInventory($series, $player)
{
	$ship_inventory = array();
	
	$fields = array();
	$fields[] = 'ships.location';
	$fields[] = 'ships.owner';
	$fields[] = 'ships.type';
	$fields[] = 'COUNT(ships.type) AS ship_count';
	
	// We join with the explored table to only check systems the player sees.
	$from = 'ships INNER JOIN explored ON ships.game_id = explored.game_id AND ships.location = explored.coordinates';

	// We only look for ships that are either the player's or non-cloaked (i.e., all of them, except alien cloaked ships).
	$conditions = array();
	$conditions[] = 'ships.game_id = '.$player['game_id'];
	$conditions[] = 'IF(ships.owner = "'.$player['name'].'" OR cloaked = "0", 1, 0)';
	$conditions[] = 'explored.player_id = '.$player['id'];

	// Skip building ships if this series specifies so.
	if (!$series['visible_builds'])
		$conditions[] = 'IF(owner = "'.$player['name'].'" OR orders <> "build", 1, 0)';

	$query = 'SELECT '.implode(',', $fields).
			' FROM '.$from.
			' WHERE '.implode(' AND ', $conditions).
			' GROUP BY location, ships.owner, type';

	$select = sc_query($query, __FILE__.'*'.__LINE__);

	while ($ship = $select->fetch_assoc())
		{
		// check for using the cloaker appear as attack rule-- however, for the owner they always appear as cloakers
		if ($series['cloakers_as_attacks'] 
			and $ship['owner'] != $player['name'] 
			and $ship['type'] == 'Cloaker')
		{
			// relies on attacks always being before cloakers as they should with the sorted output of GROUP BY
			if (isset($ship_inventory[$ship['location']][$ship['owner']]['Attack']))
			{
				$ship_inventory[$ship['location']][$ship['owner']]['Attack'] 
					+= $ship['ship_count'];
			}
			else
			{
				$ship_inventory[$ship['location']][$ship['owner']]['Attack'] 
					= $ship['ship_count'];
			}
		}
		else
			$ship_inventory[$ship['location']][$ship['owner']][$ship['type']] = $ship['ship_count'];
		}

	return $ship_inventory;
}

#----------------------------------------------------------------------------------------------------------------------#
# For a given player, returns an array indicating by how much the population of a system he sees needs to be
# adjusted due to colony ships being built (each ship substracts one population unit). This function is only 
# called if builds are invisible for this series.
#
# $population_adjustment: array[ coordinates ] = adjustement
#

function populationAdjustment($player)
{
	$population_adjustment = array();
	
	$fields = array();
	$fields[] = 'ships.location';
	$fields[] = 'COUNT(ships.id) AS ship_count';
	
	// We join with the explored table to only check systems the player sees.
	$from = 'ships INNER JOIN explored '.
			'ON ships.game_id = explored.game_id '.
			'AND ships.location = explored.coordinates';
	
	// We're looking for colony ships that are being built by other players.
	$conditions = array();
	$conditions[] = 'ships.game_id = '.$player['game_id'];
	$conditions[] = 'ships.type = "Colony"';
	$conditions[] = 'ships.orders = "build"';
	$conditions[] = 'ships.owner <> "'.$player['name'].'"';
	$conditions[] = 'explored.player_id = '.$player['id'];

	$query = 'SELECT '.implode(',', $fields).
			' FROM '.$from.
			' WHERE '.implode(' AND ', $conditions).
			' GROUP BY ships.location';

	$select = sc_query($query, __FILE__.'*'.__LINE__);
	while ($row = $select->fetch_assoc())
	{
		$population_adjustment[$row['location']] = $row['ship_count'];
	}
	
	return $population_adjustment;
}
?>
