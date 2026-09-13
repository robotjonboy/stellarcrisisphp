<?php
function buildScreen($vars)
{
	global $server,$mysqli;

	$series = $vars['series_data'];
	$game = $vars['game_data'];
	$player = $vars['player_data'];
	
	$game['id'] = (int)$game['id'];
	$min_pop = sc3MinPopToBuild($series);

	gameHeader($vars, 'Build');
	ratios($player);

	if ($player['tech_level'] < 1.0)
    	{
    	echo '<div class=messageBold>Your tech level is too low to build any ships.</div>';
	   	return footer();
	   	}
	
	// Get all the locations where the player can build.
	$conditions = array();
	$conditions[] = 'game_id = '.$game['id'];
	$conditions[] = 'owner = "'.$mysqli->real_escape_string($vars['name']).'"';
	$conditions[] = 'population >= '.$min_pop;
	$select = sc_query('SELECT name, coordinates, homeworld, population FROM systems WHERE '.implode(' AND ', $conditions).' ORDER BY name, coordinates ASC');

  	if (!$select->num_rows)
    	{
    	echo '<div class=messageBold>You have no planets with enough population for building.</div>';
	    return footer();
	   	}

	$colony_min_pop = sc3MinPopToBuildColony($series);
	$build_at = array();
	$colony_build_at = array();
	$max_colony_builds = 0;
	while ($planet = $select->fetch_assoc())
		{
		$option = '<option value="'.xlateToLocal($planet['coordinates']).'"'.($planet['homeworld'] ? ' selected' : '').'>'.
				  $planet['name'].' ('.xlateToLocal($planet['coordinates']).')';
		$build_at[] = $option;
		if ((int)$planet['population'] >= $colony_min_pop)
			{
			$colony_build_at[] = $option;
			$max_colony_builds = max($max_colony_builds, sc3MaxColonyBuilds($planet['population']));
			}
		}

	// Get all the legal BRs the player can build a ship at.
	$br_list = array();
	for ($x = 1; $x < floor(sqrt($player['tech_level'])); $x++)
		$br_list[] = '<option>'.$x;

	// Highest BR selected by default.
	$br_list[] = '<option selected>'.$x;
	  	
	$conditions = array();
	$conditions[] = 'game_id = '.$game['id'];
	$conditions[] = 'owner = "'.$mysqli->real_escape_string($vars['name']).'"';
	$conditions[] = 'orders = "build"';
	$current_builds = sc_query('SELECT type, COUNT(id) AS ship_count FROM ships WHERE '.implode(' AND ', $conditions).' GROUP BY type');
	
	if ($current_builds->num_rows)
		{
		echo '<div style="text-align: center;">Current builds:<div style="margin-bottom: 10pt;">';
		
		while ($row = $current_builds->fetch_assoc())
			echo $row['type'].' (<span class=whiteBold>'.$row['ship_count'].'</span>) ';

		echo '</div></div>';
		}

	$build_counts = array();
	for ($x = 0; $x <= $server['max_build_ships']; $x++)
		$build_counts[] = '<option>'.$x;

	$colony_build_counts = array();
	$colony_count_cap = min($server['max_build_ships'], $max_colony_builds);
	for ($x = 0; $x <= $colony_count_cap; $x++)
		$colony_build_counts[] = '<option>'.$x;
?>
<div>
<table style="margin-left: auto; margin-right: auto;">
	<tr>
		<th class=white style="padding-bottom: 5pt;">Type</th>
		<th class=white style="padding-bottom: 5pt;">Number</th>
		<th class=white style="padding-bottom: 5pt;">Name</th>
		<th class=white style="padding-bottom: 5pt;">Battlerank</th>
		<th class=white style="padding-bottom: 5pt;">Location</th>
	</tr>
<?php
	foreach (explode(' ', $player['techs']) as $tech)
		{
		if ($tech === '') continue;

		if ($tech === 'Colony' && !$colony_build_at)
			{
?>
	<tr>
		<td><?php echo htmlspecialchars($tech); ?></td>
		<td colspan=4>No planet can spare a colonist. Population cannot drop below 1.</td>
	</tr>
<?php
			continue;
			}

		$row_counts = ($tech === 'Colony' ? $colony_build_counts : $build_counts);
		$row_locations = ($tech === 'Colony' ? $colony_build_at : $build_at);
?>
	<tr>
		<td><?php echo htmlspecialchars($tech); ?></td>
		<th><select name="builds[<?php echo htmlspecialchars($tech); ?>][count]"><?php echo implode('', $row_counts); ?></select></th>
		<th><input type=text name="builds[<?php echo htmlspecialchars($tech); ?>][name]" size=15 maxlength=20></th>
		<th><select name="builds[<?php echo htmlspecialchars($tech); ?>][br]"><?php echo implode('', $br_list); ?></select></th>
		<th><select name="builds[<?php echo htmlspecialchars($tech); ?>][location]"><?php echo implode('', $row_locations); ?></select></th>
	</tr>
<?php
		}
?>
	<tr><th colspan=5 style="padding-top: 10pt;"><input type=submit name="build" value="Build"><input type=reset value="Reset"></th></tr>
</table>
</div>
<?php
	footer();
}

#-----------------------------------------------------------------------------------------------------------------------------------------#

function buildScreen_processing($vars)
{
	global $server, $moving_ships;
	
	$series = $vars['series_data'];
	$game = $vars['game_data'];
	$player = $vars['player_data'];
	$empire = $vars['empire_data'];
	$min_pop = sc3MinPopToBuild($series);
	$effective_moving = getEffectiveMovingShips($game['game_type'] ?? 'sc2');
		
	$outbuild = array();
	$departing_colonists = array();

	if (isset($vars['build']))
		{
		$max_ships = isset($series['max_ships']) ? (int)$series['max_ships'] : 0;
		$current_ship_count = 0;
		if ($max_ships > 0)
		{
			$r = sc_query('SELECT COUNT(*) AS c FROM ships WHERE game_id = '.(int)$game['id'].' AND owner = "'.addslashes($vars['name']).'"');
			$row = $r->fetch_assoc();
			$current_ship_count = (int)$row['c'];
		}

		foreach ($vars['builds'] as $ship_type => $build_data)
			{
			$build_count = $build_data['count'];

			if (!is_numeric($build_count) or $build_count <= 0)
				continue;

			$build_name = $build_data['name'];
			$build_location = $build_data['location'];

			// The build page could still be hacked to allow fractionnal BRs.
			// This is not a feature (yet?) so we don't allow this.
			$br = floor($build_data['br']);

			$system = getSystem($game['id'], xlateToGalactic($build_location));

			if ($br < 1 or $br > sqrt($player['tech_level']))
				{
				$outbuild[] = 'You cannot build ships of BR '.$br.'.';
				continue;
				}
				
			if ($system['owner'] != $vars['name'] or $system['population'] < $min_pop)
				{
				$outbuild[] = 'You cannot build ships at '.xlateToGalactic($build_location).'.';
				continue;
				}

			if ($ship_type === 'Colony')
				{
				$already = (array_key_exists($system['id'], $departing_colonists) ? $departing_colonists[$system['id']] : 0);
				$available = sc3MaxColonyBuilds($system['population']) - $already;
				if ($available < 1)
					{
					$outbuild[] = 'You cannot build colony ships at '.$system['name'].' ('.$build_location.'); population would drop below 1.';
					continue;
					}
				if ($build_count > $available)
					{
					$outbuild[] = 'Only '.$available.' colony ship'.($available == 1 ? '' : 's').' can be built at '.$system['name'].' ('.$build_location.'); population cannot drop below 1.';
					$build_count = $available;
					}
				}
				
			if (!in_array($ship_type, explode(' ', $player['techs'])))
				{
				$outbuild[] = 'You have not developed the '.$ship_type.' tech.';
				continue;
				}
						
			if ($build_count > $server['max_build_ships'] )
				{
				$outbuild[] = 'You cannot build that many ships ('.$server['max_build_ships'].' is the maximum).';
				continue;
				}

			if ($max_ships > 0 && ($current_ship_count + $build_count) > $max_ships)
				{
				$outbuild[] = 'Ship limit is '.$max_ships.'; you cannot build that many ships.';
				continue;
				}

			// Base costs; extras added later.
			$build_cost 	  = pow((float)$br+4.0, 2.0);
			$maintenance_cost = $br*2.0;
			$fuel_cost 		  = $br*4.0;

			$option_costs = sc3SpecialBuildCosts($game, $ship_type);
			if ($option_costs !== null)
				{
				$build_cost += $option_costs[0];
				$maintenance_cost += $option_costs[1];
				if (in_array($ship_type, array('Jumpgate', 'Carrier', 'Builder', 'Morpher'), true))
					$fuel_cost += 2 * $option_costs[1]; // fuel = 2x maint special, matching Jumpgate pattern (+32 for +16 maint)
				}
			else
			{
			switch ($ship_type)
				{
				case 'Colony':
				case 'Science':
				case 'Cloaker':
				case 'Doomsday':
				case 'Troopship':
				case 'Terraformer':
				case 'Minesweeper':
					$build_cost += 25;
					$maintenance_cost += 4;
					$fuel_cost += 8;
					break;
				case 'Stargate':
				case 'Engineer':
					$build_cost += 100;
					$maintenance_cost += 16;
					$fuel_cost += 32;
					break;
				case 'Minefield':
					$build_cost += 10;
					$maintenance_cost += 2;
					$fuel_cost = 0;
					break;
				case 'Satellite':
					$build_cost -= 10;
					$maintenance_cost = max(2, ($maintenance_cost-2));
					$fuel_cost = 0;
					break;
				}
			}

			if ($ship_type === 'Colony')
				$departing_colonists[$system['id']] = (array_key_exists($system['id'], $departing_colonists) ? $departing_colonists[$system['id']] : 0) + $build_count;
						
			$values = array();
			$values[] = 'series_id = '.$vars['series_id'];
			$values[] = 'game_number = '.$vars['game_number'];
			$values[] = 'game_id = '.$game['id'];
			$values[] = 'player_id = '.$player['id'];
			$values[] = 'name = "'.$build_name.'"';
			$values[] = 'br = '.$br;
			$values[] = 'max_br = '.$br;
			$values[] = 'type = "'.$ship_type.'"';
			$values[] = 'location = "'.xlateToGalactic($build_location).'"';
			$values[] = 'orders = "build"';
			$values[] = 'owner = "'.$vars['name'].'"';
			$values[] = 'build_cost = '.$build_cost;
			$values[] = 'maintenance_cost = '.$maintenance_cost;
			$values[] = 'fuel_cost = '.$fuel_cost;
					
			if ($ship_type == 'Cloaker' and $series['build_cloakers_cloaked'])
				$values[] = 'cloaked = "1"';

			if ($ship_type == 'Morpher')
				$values[] = 'morpher = "1"';
				
			// Build the requested amount of ships.
			for ($x = 0; $x < $build_count; $x++)
				sc_query('INSERT INTO ships SET '.implode(',', $values));

			$current_ship_count += $build_count;

			$outbuild[] = $build_count.' '.$ship_type.(in_array($ship_type, $effective_moving) ? ' ship' : '').($build_count != 1 ? 's' : '').
						  ($build_name ? ' named '.$build_name : '').' (BR '.$br.') built at '.$system['name'].' ('.$build_location.').';
			}

		// Farewell, colonists!
		foreach (array_keys($departing_colonists) as $system_id)
			sc_query('UPDATE systems SET population = (population-'.$departing_colonists[$system_id].') WHERE id = '.$system_id);

		recalculateRatios($vars);
		
		if (!count($outbuild))
			$outbuild[] = 'No building data entered; no ships built.';
			
		return sendGameMessage($player, implode('<br>', $outbuild));
		}
	else
		return false;
}
?>
