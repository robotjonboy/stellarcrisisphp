<?php
function systemsScreen($vars)
{
	global $server;

	$series = $vars['series_data'];
	$game = $vars['game_data'];
	$player = $vars['player_data'];
	$empire = $vars['empire_data'];
	$coordinates = array_key_exists('zoomed_planet', $vars) ? $vars['zoomed_planet'] : null;
	
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

	$single_system = ($coordinates != '' && $coordinates !== null);
	if ($single_system)
		{
		echo '<input type=hidden name="zoomed_planet" value="'.
			htmlspecialchars($coordinates).'">';
		echo localNeighborhoodMap($vars, $coordinates);
		echo '<div class="localMapActions">'.
			'<input type=submit name="gameAction" value="All Systems">'.
			'</div>';
		}

	echo '<div><table width="100%" border=0 style="text-align: center;">';

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
							', explored.id as exploredid FROM '.$from.
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
			$scout_report_checkbox = '<input '.
								'type=checkbox '.
								'checked '.
								'name="scouting_systems[]" '.
								'value="'.xlateToLocal($system['coordinates']).'" '.
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
								(array_key_exists($system['coordinates'], $population_adjustment) ? $population_adjustment[$system['coordinates']] : 0));

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
	}
	echo '</table>'.
			'</div>'.
				'<input type=hidden '.
						'name=system_ids '.
						'value="'.implode('&', $system_ids).'">';

	if ($single_system)
		echo localSystemOrders($vars, xlateToGalactic($coordinates));

	footer();
}

#----------------------------------------------------------------------------------------------------------------------#
# Local coordinates of every system this player has explored, for Full Scouting Report.
#

function allExploredLocalCoordinates($player)
{
	$from_conditions = array();
	$from_conditions[] = 'systems.game_id = explored.game_id';
	$from_conditions[] = 'systems.coordinates = explored.coordinates';

	$sql = 'SELECT DISTINCT systems.coordinates, explored.id AS exploredid '.
			'FROM systems INNER JOIN explored ON ('.implode(' AND ', $from_conditions).') '.
			'WHERE explored.player_id = '.((int)$player['id']).' '.
			'ORDER BY explored.id';
	$select = sc_query($sql);

	$coordinates = array();
	while ($row = $select->fetch_assoc())
		$coordinates[] = xlateToLocal($row['coordinates']);

	return $coordinates;
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

	echo '<input type=hidden name="zoomed_planet" value="'.
		htmlspecialchars($vars['zoomed_planet']).'">';
	echo localNeighborhoodMap($vars, $vars['zoomed_planet']);
	echo '<div class="localMapActions">'.
		'<input type=submit name="gameAction" value="All Systems">'.
		'</div>';
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
	echo localSystemOrders($vars, $coordinates);
	footer();
}

#----------------------------------------------------------------------------------------------------------------------#

function systemsScreen_processing(&$vars)
{
	global $hwX, $hwY, $mysqli;

	$series = $vars['series_data'];
	$game = $vars['game_data'];
	$player = $vars['player_data'];
	$empire = $vars['empire_data'];
		
	// Only go in here if the requested map origin is different from the old one.
	if (array_key_exists('system_origin', $vars) && $vars['old_origin'] != $vars['system_origin'])
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
	if (array_key_exists('system_ids', $vars) && $vars['system_ids'] != '')
		{
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
		}

	if ($affected_rows)
		{
		recalculateTargetPopulation($player);
		recalculateRatios($vars);
		}

	systemsScreen_processLocalOrders($vars);
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

#----------------------------------------------------------------------------------------------------------------------#
# 3×3 neighborhood map for the single-system screen. Only known (explored or scouted) systems are shown.
# Cells use the same full-map layout as mapScreen (resources, ships, name), not mini-map icons.
#

function localNeighborhoodMap($vars, $center_local)
{
	$series = $vars['series_data'];
	$player = $vars['player_data'];
	$empire = $vars['empire_data'];
	$game = $vars['game_data'];

	$center = xlateToGalactic($center_local);
	list($cx, $cy) = explode(',', $center);
	$cx = (int)$cx;
	$cy = (int)$cy;

	$x_min = $cx - 1;
	$x_max = $cx + 1;
	$y_min = $cy - 1;
	$y_max = $cy + 1;

	$coord_list = array();
	for ($y = $y_min; $y <= $y_max; $y++)
		for ($x = $x_min; $x <= $x_max; $x++)
			$coord_list[] = $x.','.$y;

	$in_list = '"'.implode('","', $coord_list).'"';

	$systems = array();
	$icons = array();
	$colors_for_owner = array();

	$fields = array();
	$fields[] = 'systems.coordinates';
	$fields[] = 'systems.owner';
	$fields[] = 'systems.annihilated';
	$fields[] = 'systems.jumps';
	$fields[] = 'systems.name';
	$fields[] = 'systems.mineral';
	$fields[] = 'systems.agriculture';
	$fields[] = 'systems.fuel';
	$fields[] = 'systems.population';

	$tables = 'systems INNER JOIN explored ON systems.game_id = explored.game_id '.
			  'AND systems.coordinates = explored.coordinates';
	$conditions = array();
	$conditions[] = 'explored.player_id = '.$player['id'];
	$conditions[] = 'systems.game_id = '.$game['id'];
	$conditions[] = 'systems.coordinates IN ('.$in_list.')';

	$select = sc_query('SELECT '.implode(',', $fields).', "yes" AS explored FROM '.$tables.
						' WHERE '.implode(' AND ', $conditions));
	while ($row = $select->fetch_assoc())
		{
		$systems[$row['coordinates']] = $row;
		}

	$scout_fields = array();
	$scout_fields[] = 'scouting_reports.coordinates';
	$scout_fields[] = 'scouting_reports.owner';
	$scout_fields[] = 'scouting_reports.annihilated';
	$scout_fields[] = 'scouting_reports.jumps';
	$scout_fields[] = 'scouting_reports.name';
	$scout_fields[] = 'scouting_reports.mineral';
	$scout_fields[] = 'scouting_reports.agriculture';
	$scout_fields[] = 'scouting_reports.fuel';
	$scout_fields[] = 'scouting_reports.population';

	$conditions = array();
	$conditions[] = 'scouting_reports.player_id = '.$player['id'];
	$conditions[] = 'scouting_reports.coordinates IN ('.$in_list.')';

	$select = sc_query('SELECT '.implode(',', $scout_fields).', "no" AS explored FROM scouting_reports'.
						' WHERE '.implode(' AND ', $conditions));
	while ($row = $select->fetch_assoc())
		{
		if (isset($systems[$row['coordinates']]))
			continue;

		$systems[$row['coordinates']] = $row;
		}

	$jump_presence = buildMapJumpPresence($systems);

	// Ship counts for explored systems in the neighborhood (same rules as mapScreen).
	$ship_counts = array();
	$population_adjustements = array();

	$ship_fields = array();
	$ship_fields[] = 'location';
	$ship_fields[] = 'SUM(IF(owner = "'.$player['name'].'", 1, 0)) AS friendly';
	$ship_fields[] = 'SUM(IF(owner <> "'.$player['name'].'" AND cloaked = "0"'.
				($series['visible_builds'] ? '' : ' AND orders <> "build"').', 1, 0)) AS enemy';
	$ship_fields[] = 'SUM(IF(owner <> "'.$player['name'].'" AND type = "Colony" AND orders = "build", 1, 0)) AS population_adjustment';

	$from = 'ships INNER JOIN explored ON ships.game_id = explored.game_id AND ships.location = explored.coordinates';
	$conditions = array();
	$conditions[] = 'explored.player_id = '.$player['id'];
	$conditions[] = 'ships.game_id = '.$game['id'];
	$conditions[] = 'ships.location IN ('.$in_list.')';

	$select = sc_query('SELECT '.implode(', ', $ship_fields).' FROM '.$from.
						' WHERE '.implode(' AND ', $conditions).' GROUP BY location');
	while ($row = $select->fetch_assoc())
		{
		$ship_counts[$row['location']]['friendly'] = $row['friendly'];
		$ship_counts[$row['location']]['enemy'] = $row['enemy'];
		$population_adjustements[$row['location']] = $row['population_adjustment'];
		}

	$map = '<div class="localMapWrap"><table class="localMap" border=0 cellpadding=0 cellspacing=2>'.
		   '<tr><td></td>';

	// Northward stubs from the top edge (links only; no systems beyond the 3×3).
	for ($x = $x_min; $x <= $x_max; $x++)
		{
		$here = $x.','.$y_max;
		$north = $x.','.($y_max+1);
		$map .= '<th>';
		if (array_key_exists($here, $jump_presence)
			&& array_key_exists($north, $jump_presence[$here])
			&& $jump_presence[$here][$north])
			$map .= '<img src="images/vert.gif" height=10 width=1 alt="">';
		$map .= '</th><td></td>';
		}

	$map .= '</tr>';

	for ($y = $y_max; $y >= $y_min; $y--)
		{
		$map .= '<tr><td>';
		// Westward stub from the left edge.
		$here = $x_min.','.$y;
		$west = ($x_min-1).','.$y;
		if (array_key_exists($here, $jump_presence)
			&& array_key_exists($west, $jump_presence[$here])
			&& $jump_presence[$here][$west])
			$map .= '<img src="images/horz.gif" width=10 height=1 alt="">';
		$map .= '</td>';

		for ($x = $x_min; $x <= $x_max; $x++)
			{
			$coordinates = $x.','.$y;
			$is_center = ($coordinates === $center);

			if (isset($systems[$coordinates]))
				{
				$system = $systems[$coordinates];
				$local = xlateToLocal($coordinates);

				if ($system['owner'] == '' && !$system['annihilated'])
					$src = 'images/planet.gif';
				else if ($system['annihilated'])
					$src = 'images/annihilated.gif';
				else if ($system['owner'] == $empire['name'])
					$src = 'images/aliens/'.$empire['icon'];
				else
					{
					if (!isset($icons[$system['owner']]))
						{
						$other_empire = getEmpire($system['owner']);
						$icons[$system['owner']] = $other_empire['icon'];
						}
					$src = 'images/aliens/'.$icons[$system['owner']];
					}

				if ($is_center)
					$icon = '<img src="'.$src.'" class="mapIcon" height=40 width=40 alt="">';
				else
					$icon = '<input type=image src="'.$src.'" '.
							'name="system:'.$local.'" '.
							'class="mapIcon" height=40 width=40>';

				if ($system['explored'] == 'yes')
					{
					$own_ship_count = isset($ship_counts[$coordinates]['friendly'])
						? $ship_counts[$coordinates]['friendly'] : 0;
					$other_ship_count = isset($ship_counts[$coordinates]['enemy'])
						? $ship_counts[$coordinates]['enemy'] : 0;
					$population_adjustement = isset($population_adjustements[$coordinates])
						? $population_adjustements[$coordinates] : 0;
					}
				else
					{
					$own_ship_count = '?';
					$other_ship_count = '?';
					$population_adjustement = 0;
					}

				if (!isset($colors_for_owner[$system['owner']]))
					$colors_for_owner[$system['owner']] = systemNameColor($player, $system['owner']);

				if ($system['explored'] == 'no')
					$system_name_color = ' class=grey';
				else if ($colors_for_owner[$system['owner']] != '')
					$system_name_color = ' class='.$colors_for_owner[$system['owner']];
				else
					$system_name_color = '';

				$icon_cell_style = '';
				if ($is_center || $empire['name'] == $system['owner'])
					$icon_cell_style = ' style="border: 1pt solid white;"';

				$cell = '<table border=0 cellspacing=0 cellpadding=0 class="localMapSystem">'.
					'<tr><td style="text-align: center;"'.$system_name_color.'>'.
					$system['mineral'].'<br>'.$system['agriculture'].'<br>('.$own_ship_count.')'.
					'</td>'.
					'<td class=center'.$icon_cell_style.'>'.$icon.'</td>'.
					'<td style="text-align: center;"'.$system_name_color.'>'.
					$system['fuel'].'<br>'.($system['population']+$population_adjustement).
					'<br>('.$other_ship_count.')'.
					'</td></tr>'.
					'<tr><td style="text-align: center;" colspan=3'.$system_name_color.'>'.
					($system['annihilated'] ? 'Remains of ' : '').
					$system['name'].
					($empire['show_coordinates'] ? ' ('.$local.')' : '').
					'</td></tr></table>';

				$map .= '<td class="localMapCell'.($is_center ? ' localMapCenter' : '').'">'.$cell.'</td>';
				}
			else
				$map .= '<td class="localMapCell localMapEmpty"></td>';

			$map .= '<td>';
			$east = ($x+1).','.$y;
			// In-grid east links, plus eastward stubs from the right edge.
			if (array_key_exists($coordinates, $jump_presence)
				&& array_key_exists($east, $jump_presence[$coordinates])
				&& $jump_presence[$coordinates][$east])
				$map .= '<img src="images/horz.gif" width=10 height=1 alt="">';
			$map .= '</td>';
			}

		$map .= '</tr><tr><td></td>';

		for ($x = $x_min; $x <= $x_max; $x++)
			{
			$here = $x.','.$y;
			$south = $x.','.($y-1);
			$map .= '<th>';
			// In-grid south links, plus southward stubs from the bottom edge.
			if (array_key_exists($here, $jump_presence)
				&& array_key_exists($south, $jump_presence[$here])
				&& $jump_presence[$here][$south])
				$map .= '<img src="images/vert.gif" height=10 width=1 alt="">';
			$map .= '</th><td></td>';
			}

		$map .= '</tr>';
		}

	$map .= '</table></div>';

	return $map;
}

#----------------------------------------------------------------------------------------------------------------------#
# Order controls for the player's fleets and free ships at the focused system.
#

function localSystemOrders($vars, $center_galactic)
{
	global $system_cache;

	$series = $vars['series_data'];
	$game = $vars['game_data'];
	$player = $vars['player_data'];

	$ships_html = '';
	$fleets_html = '';

	$conditions = array();
	$conditions[] = 'game_id = '.$game['id'];
	$conditions[] = 'owner = "'.$player['name'].'"';
	$conditions[] = 'location = "'.$center_galactic.'"';
	$conditions[] = 'fleet_id = 0';

	$select = sc_query('SELECT * FROM ships WHERE '.implode(' AND ', $conditions).
						' ORDER BY type, max_br ASC');
	while ($ship = $select->fetch_assoc())
		{
		$valid_orders = getValidOrders($series, $game, $ship, $player);
		$orders = implode('', fixOrders($valid_orders, $series, $game, $player, $ship));

		$next_br = min($ship['br']*$player['mineral_ratio'], $ship['max_br']);
		$next_brcolor = brColor($next_br, $ship['max_br']);
		$current_brcolor = brColor($ship['br'], $ship['max_br']);
		$next_br = number_format($next_br, 3, '.', '');
		$current_br = ($ship['orders'] == 'build'
			? '---'
			: '<span style="color: '.$current_brcolor.';">'.number_format($ship['br'], 3, '.', '').'</span>');

		$name = stripslashes($ship['name']);
		$ships_html .= '<tr class="center shipRow">'.
			'<td data-label="Name">'.htmlspecialchars($name).
			'<input type=hidden name="ship_name['.$ship['id'].']" value="'.htmlspecialchars($name, ENT_QUOTES).'">'.
			'</td>'.
			'<td data-label="Current BR">'.$current_br.'</td>'.
			'<td data-label="Next BR"><span style="color: '.$next_brcolor.';">'.$next_br.'</span></td>'.
			'<td data-label="Max BR">'.number_format($ship['max_br'], 1, '.', '').'</td>'.
			'<td data-label="Orders"><select name="ship_orders['.$ship['id'].']">'.$orders.'</select></td>'.
			'<td data-label="Type">'.
			((array_key_exists('morpher', $ship) && $ship['morpher']) ? '<i>'.$ship['type'].'</i>' : $ship['type']).
			'</td>'.
			'</tr>';
		}

	$conditions = array();
	$conditions[] = 'fleets.game_id = '.$game['id'];
	$conditions[] = 'fleets.owner = "'.$player['name'].'"';
	$conditions[] = 'fleets.location = "'.$center_galactic.'"';

	$query = 'SELECT fleets.*, systems.name as system_name, systems.annihilated '.
			 'FROM fleets INNER JOIN systems ON fleets.game_id = systems.game_id '.
			 'AND fleets.location = systems.coordinates '.
			 'WHERE '.implode(' AND ', $conditions).' ORDER BY fleets.name ASC';

	$select = sc_query($query);
	while ($fleet = $select->fetch_assoc())
		{
		$system_name = ($fleet['annihilated'] ? 'Remains of ' : '').$fleet['system_name'];
		$local_coordinates = xlateToLocal($fleet['location']);
		$fleet_orders = '';

		foreach (getFleetOrders($player, $fleet) as $orders)
			{
			list($orders, $arguments) = str_contains($orders, ':')
				? explode(':', $orders)
				: array($orders, null);

			$order_arguments = (isset($arguments) && strstr($arguments, ',')
				? xlateToGalactic($arguments)
				: $arguments);
			$selected = ($orders.':'.$order_arguments == $fleet['orders'].':'.$fleet['order_arguments']);
			$option = '<option'.($selected ? ' selected' : '');

			switch ($orders)
				{
				case 'terraform':	$menu = 'Terraform '.$system_name.' ('.$local_coordinates.')';			break;
				case 'colonize':	$menu = 'Colonize '.$system_name.' ('.$local_coordinates.')';			break;
				case 'nuke':		$menu = 'Nuke '.$system_name.' ('.$local_coordinates.')';				break;
				case 'invade':		$menu = 'Invade '.$system_name.' ('.$local_coordinates.')';				break;
				case 'pickup':		$menu = 'Gather ships at '.$system_name.' ('.$local_coordinates.')';	break;
				case 'disband':		$menu = 'Disband Fleet';												break;
				case 'disbandall':	$menu = 'Disband Fleet and Ships';										break;
				case 'standby': 	$menu = 'Standby at '.$system_name.' ('.$local_coordinates.')';			break;
				case 'move':
					$galactic_coordinates = xlateToGalactic($arguments);
					if (isset($system_cache[$galactic_coordinates]))
						$destination = $system_cache[$galactic_coordinates];
					else
						{
						$destination = getSystem($game['id'], $galactic_coordinates);
						$system_cache[$galactic_coordinates] = $destination;
						}
					$menu = 'Move '.getDirection($fleet['location'], $galactic_coordinates).' to '.
							($destination['annihilated'] ? 'Remains of ' : '').$destination['name'].
							' ('.$arguments.')';
					break;
				default:
					$menu = $orders;
					break;
				}

			$fleet_orders .= $option.' value="'.$orders.':'.$arguments.'">'.$menu;
			}

		$fields = array();
		$fields[] = 'SUM(POW(br, 2)) AS fleetStrength';
		$fields[] = 'SUM(POW(LEAST(br*'.($player['mineral_ratio'] ? $player['mineral_ratio'] : 0).', max_br), 2)) AS fleetNextStrength';
		$fields[] = 'SUM(POW(max_br, 2)) AS fleetMaxStrength';
		$fields[] = 'COUNT(*) AS shipCount';

		$select_ships = sc_query('SELECT '.implode(',', $fields).' FROM ships WHERE fleet_id = '.$fleet['id']);
		$line = $select_ships->fetch_assoc();
		$fleetStrength = isset($line['fleetStrength']) ? sqrt($line['fleetStrength']) : 0;
		$fleetNextStrength = isset($line['fleetNextStrength']) ? sqrt($line['fleetNextStrength']) : 0;
		$fleetMaxStrength = isset($line['fleetMaxStrength']) ? sqrt($line['fleetMaxStrength']) : 0;
		$ship_count = $line['shipCount'];

		if ($fleetMaxStrength != 0)
			{
			$current_brcolor = brColor($fleetStrength, $fleetMaxStrength);
			$next_brcolor = brColor($fleetNextStrength, $fleetMaxStrength);
			}
		else
			{
			$current_brcolor = '';
			$next_brcolor = '';
			}

		$fleetStrength = number_format($fleetStrength, 3, '.', '');
		$fleetNextStrength = number_format($fleetNextStrength, 3, '.', '');
		$fleetMaxStrength = number_format($fleetMaxStrength, 3, '.', '');

		$ship_inventory = array();
		$select_ships = sc_query('SELECT type, COUNT(*) as count FROM ships WHERE fleet_id = '.$fleet['id'].' GROUP BY type');
		while ($row = $select_ships->fetch_assoc())
			$ship_inventory[$row['type']] = $row['count'];

		$name = stripslashes($fleet['name']);
		$fleets_html .= '<tr class="center shipRow">'.
			'<td data-label="Name">'.htmlspecialchars($name).
			'<input type=hidden name="fleet_name['.$fleet['id'].']" value="'.htmlspecialchars($name, ENT_QUOTES).'">'.
			'</td>'.
			'<td data-label="Current BR"><span style="color: '.$current_brcolor.';">'.$fleetStrength.'</span></td>'.
			'<td data-label="Next BR"><span style="color: '.$next_brcolor.';">'.$fleetNextStrength.'</span></td>'.
			'<td data-label="Max BR">'.$fleetMaxStrength.'</td>'.
			'<td data-label="Orders"><select name="fleet_orders['.$fleet['id'].']">'.$fleet_orders.'</select></td>'.
			'<td data-label="Type">Fleet</td>'.
			'</tr>';

		if (count($ship_inventory))
			{
			$fleets_html .= '<tr><td></td><td colspan=5><span class=whiteBold>'.$ship_count.' ships</span> - ';
			foreach ($ship_inventory as $type => $count)
				$fleets_html .= $type.' (<span class=whiteBold>'.$count.'</span>) ';
			$fleets_html .= '</td></tr>';
			}
		else
			$fleets_html .= '<tr><td></td><td colspan=5 align=left><span class=whiteBold>No ships</span></td></tr>';
		}

	$html = '<div class="localOrders">'.
			'<div class="localOrdersTitle">Orders at this system</div>';

	if ($ships_html == '' && $fleets_html == '')
		{
		$html .= '<div class="messageBold">No ships or fleets at this system.</div>';
		}
	else
		{
		$html .= '<table class="shipsTable" width="100%">'.
				 '<tr class="shipsTableHeader">'.
				 '<th>Name</th><th>Current BR</th><th>Next BR</th>'.
				 '<th>Max BR</th><th>Orders</th><th>Type</th></tr>';
		$html .= $ships_html;
		if ($ships_html != '' && $fleets_html != '')
			$html .= '<tr><th colspan=6><img class=spacerule src="images/spacerule.jpg" width="100%" height=5 alt=""></th></tr>';
		$html .= $fleets_html.'</table>';
		$html .= '<div class="localOrdersActions">'.
				 '<input type=submit name="gameAction" value="Update System">'.
				 '</div>';
		}

	$html .= '</div>';

	return $html;
}

#----------------------------------------------------------------------------------------------------------------------#
# Apply ship/fleet order POSTs from the system screen, restricted to units at zoomed_planet.
#

function systemsScreen_processLocalOrders(&$vars)
{
	if (!array_key_exists('zoomed_planet', $vars) || $vars['zoomed_planet'] == '')
		return;

	if (!isset($vars['ship_orders']) && !isset($vars['fleet_orders']))
		return;

	$player = $vars['player_data'];
	$location = xlateToGalactic($vars['zoomed_planet']);

	$filtered_ship_name = array();
	$filtered_ship_orders = array();

	if (isset($vars['ship_orders']) && is_array($vars['ship_orders']))
		{
		foreach ($vars['ship_orders'] as $ship_id => $orders)
			{
			if (!$ship = getShipByID($ship_id)) continue;
			if ($ship['owner'] != $player['name']) continue;
			if ($ship['location'] != $location) continue;
			if ($ship['fleet_id'] != 0) continue;

			$filtered_ship_orders[$ship_id] = $orders;
			$filtered_ship_name[$ship_id] = (isset($vars['ship_name'][$ship_id])
				? $vars['ship_name'][$ship_id]
				: $ship['name']);
			}
		}

	$filtered_fleet_name = array();
	$filtered_fleet_orders = array();

	if (isset($vars['fleet_orders']) && is_array($vars['fleet_orders']))
		{
		foreach ($vars['fleet_orders'] as $fleet_id => $orders)
			{
			if (!$fleet = getFleetByID($fleet_id)) continue;
			if ($fleet['owner'] != $player['name']) continue;
			if ($fleet['location'] != $location) continue;

			$filtered_fleet_orders[$fleet_id] = $orders;
			$filtered_fleet_name[$fleet_id] = (isset($vars['fleet_name'][$fleet_id])
				? $vars['fleet_name'][$fleet_id]
				: $fleet['name']);
			}
		}

	if (!$filtered_ship_name && !$filtered_fleet_name)
		return;

	$order_vars = $vars;
	if ($filtered_ship_name)
		{
		$order_vars['ship_name'] = $filtered_ship_name;
		$order_vars['ship_orders'] = $filtered_ship_orders;
		}
	else
		{
		unset($order_vars['ship_name']);
		unset($order_vars['ship_orders']);
		}

	if ($filtered_fleet_name)
		{
		$order_vars['fleet_name'] = $filtered_fleet_name;
		$order_vars['fleet_orders'] = $filtered_fleet_orders;
		}
	else
		{
		unset($order_vars['fleet_name']);
		unset($order_vars['fleet_orders']);
		}

	shipsScreen_processing($order_vars);
}
?>
