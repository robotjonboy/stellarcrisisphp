<?php
function buildScreen($vars)
{
	global $server,$mysqli;

	$series = $vars['series_data'];
	$game = $vars['game_data'];
	$player = $vars['player_data'];
	
	$game['id'] = (int)$game['id'];

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
	$conditions[] = 'population >= '.$server['builder_population'];
	$select = sc_mysql_query('SELECT name, coordinates, homeworld FROM systems WHERE '.implode(' AND ', $conditions).' ORDER BY name, coordinates ASC');

  	if (!$select->num_rows)
    	{
    	echo '<div class=messageBold>You have no planets with enough population for building.</div>';
	    return footer();
	   	}
	
	$build_at = array();
	while ($planet = $select->fetch_assoc())
		$build_at[] = '<option value="'.xlateToLocal($planet['coordinates']).'"'.($planet['homeworld'] ? ' selected' : '').'>'.
				 	  $planet['name'].' ('.xlateToLocal($planet['coordinates']).')';

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
	$current_builds = sc_mysql_query('SELECT type, COUNT(id) AS ship_count FROM ships WHERE '.implode(' AND ', $conditions).' GROUP BY type');
	
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
?>
	<tr>
		<td><?php echo $tech; ?></td>
		<th><select name="builds[<?php echo $tech; ?>][count]"><?php echo implode('', $build_counts); ?></select></th>
		<th><input name="builds[<?php echo $tech; ?>][name]" size=15 maxlength=20></th>
		<th><select name="builds[<?php echo $tech; ?>][br]"><?php echo implode('', $br_list); ?></select></th>
		<th><select name="builds[<?php echo $tech; ?>][location]"><?php echo implode('', $build_at); ?></select></th>
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
		
	$outbuild = array();
	$departing_colonists = array();

	if (isset($vars['build']))
		{
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
				
			if ($system['owner'] != $vars['name'] or $system['population'] < $server['builder_population'])
				{
				$outbuild[] = 'You cannot build ships at '.xlateToGalactic($build_location).'.';
				continue;
				}
				
			if (!in_array($ship_type, explode(' ', $player['techs'])))
				{
				$outbuild[] = 'You have not developped the '.$ship_type.' tech.';
				continue;
				}
						
			if ($build_count > $server['max_build_ships'] )
				{
				$outbuild[] = 'You cannot build that many ships ('.$server['max_build_ships'].' is the maximum).';
				continue;
				}

			// Base costs; extras added later.
			$build_cost 	  = pow((float)$br+4.0, 2.0);
			$maintenance_cost = $br*2.0;
			$fuel_cost 		  = $br*4.0;

			switch ($ship_type)
				{
				case 'Colony':
					// Population cost for colony ships. We substract this later.
					$departing_colonists[$system['id']] += $build_count;
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
				
			// Build the requested amount of ships.
			for ($x = 0; $x < $build_count; $x++)
				sc_mysql_query('INSERT INTO ships SET '.implode(',', $values));

			$outbuild[] = $build_count.' '.$ship_type.(in_array($ship_type, $moving_ships) ? ' ship' : '').($build_count != 1 ? 's' : '').
						  ($build_name ? ' named '.$build_name : '').' (BR '.$br.') built at '.$system['name'].' ('.$build_location.').';
			}

		// Farewell, colonists!
		foreach (array_keys($departing_colonists) as $system_id)
			sc_mysql_query('UPDATE systems SET population = (population-'.$departing_colonists[$system_id].') WHERE id = '.$system_id);

		recalculateRatios($vars);
		
		if (!count($outbuild))
			$outbuild[] = 'No building data entered; no ships built.';
			
		return sendGameMessage($player, implode('<br>', $outbuild));
		}
	else
		return false;
}
?>