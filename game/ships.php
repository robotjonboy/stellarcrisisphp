<?
function shipsScreen($vars)
{
	global $server, $system_cache;

	$series = $vars['series_data'];
	$game = $vars['game_data'];
	$player = $vars['player_data'];
	$empire = $vars['empire_data'];

	$conditions = array();
	$conditions[] = 'game_id = '.$game['id'];
	$conditions[] = 'owner = "'.$player['name'].'"';
	$conditions[] = 'fleet_id = 0';
	
	if ($empire['list_ships_by_system'])
		$order = 'location, type, max_br ASC';
	else
		$order = 'type, location, max_br ASC';
	
	$query = 'SELECT * FROM ships WHERE '.implode(' AND ', $conditions).' ORDER BY '.$order;
	
	$select_ships = sc_mysql_query($query);

	$builds = '';
	$ships = '';
	$fleets = '';
	$table = '';

	while ($ship = mysql_fetch_array($select_ships))
		{
		$valid_orders = getValidOrders($series, $game, $ship, $player);
		$orders = implode('', fixOrders($valid_orders, $series, $game, $player, $ship));

		$next_br = min($ship['br']*$player['mineral_ratio'], $ship['max_br']);

		$next_brcolor = brColor($next_br, $ship['max_br']);
		$current_brcolor = brColor($ship['br'], $ship['max_br']);

		$next_br = number_format($next_br, 3, '.', '');
		$current_br = ($ship['orders'] == 'build' ? '---' : '<span style="color: '.$current_brcolor.';">'.number_format($ship['br'], 3, '.', '').'</span>');

    	$row = '<tr class=center>'.
			   '<td><input type=text name="ship_name['.$ship['id'].']" value="'.stripslashes($ship['name']).'" size=15 maxlength=20></td>'.
			   '<td>'.$current_br.'</td>'.
			   '<td><span style="color: '.$next_brcolor.';">'.$next_br.'</span></td>'.
			   '<td>'.number_format($ship['max_br'], 1, '.', '').'</td>'.
			   '<td>'.xlateToLocal($ship['location']).'</td>'.
			   '<td><select name="ship_orders['.$ship['id'].']">'.$orders.'</select></td>'.
			   '<td>'.($ship['morpher'] ? '<i>'.$ship['type'].'</i>' : $ship['type']).'</td>'.
			   '</tr>';

		if ($ship['orders'] == 'build')
			$builds .= $row;
		else
			$ships .= $row;
		}
	
	if ($builds != '')
		$builds .= '<tr><th colspan=7 style="padding-top: 10pt;"><input type=submit name="gameAction" value="Cancel All Builds"></th></tr>';
	
	$conditions = array();
	$conditions[] = 'fleets.game_id = '.$game['id'];
	$conditions[] = 'fleets.owner = "'.$player['name'].'"';
	
	$query = 'SELECT fleets.*, systems.name as system_name, systems.annihilated '.
			 'FROM fleets INNER JOIN systems ON fleets.game_id = systems.game_id AND fleets.location = systems.coordinates '.
			 'WHERE '.implode(' AND ', $conditions).' ORDER BY fleets.location ASC';
	
	$select_fleets = sc_mysql_query($query);

	while ($fleet = mysql_fetch_array($select_fleets))
		{
		$fleet_orders = '';
		
		$system_name = ($fleet['annihilated'] ? 'Remains of ' : '').$fleet['system_name'];
		$local_coordinates = xlateToLocal($fleet['location']);

		$valid_fleet_orders = getFleetOrders($player, $fleet);

		foreach ($valid_fleet_orders as $orders)
			{
			list($orders, $arguments) = explode(':', $orders);
			
			$order_arguments = (strstr($arguments, ',') ? xlateToGalactic($arguments) : $arguments);
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
					
					// Use the system cache.
					if (isset($system_cache[$galactic_coordinates]))
						$destination = $system_cache[$galactic_coordinates];
					else
						{
						$destination = getSystem($game['id'], $galactic_coordinates);
						$system_cache[$galactic_coordinates] = $destination;
						}
						
					$menu = 'Move '.getDirection($fleet['location'], $galactic_coordinates).' to '.
							($destination['annihilated'] ? 'Remains of ' : '').$destination['name'].' ('.$arguments.')';
					break;
      			}
      			
			$fleet_orders .= $option.' value="'.$orders.':'.$arguments.'">'.$menu;
    		}
		
		$fields = array();
		$fields[] = 'SUM(POW(br, 2)) AS fleetStrength';
		$fields[] = 'SUM(POW(LEAST(br*'.($player['mineral_ratio'] ? $player['mineral_ratio'] : 0).', max_br), 2)) AS fleetNextStrength';
		$fields[] = 'SUM(POW(max_br, 2)) AS fleetMaxStrength';
		$fields[] = 'COUNT(*) AS shipCount';

		$select_ships = sc_mysql_query('SELECT '.implode(',', $fields).' FROM ships WHERE fleet_id = '.$fleet['id']);
		
		$fleetStrength = sqrt(mysql_result($select_ships, 0, 0));
		$fleetNextStrength = sqrt(mysql_result($select_ships, 0, 1));
		$fleetMaxStrength = sqrt(mysql_result($select_ships, 0, 2));
		$ship_count = mysql_result($select_ships, 0, 3);
		
		if ($fleetMaxStrength != 0)
			{
			$current_brcolor = brColor($fleetStrength, $fleetMaxStrength);
			$next_brcolor = brColor($fleetNextStrength, $fleetMaxStrength);
			}
			
		$fleetStrength = number_format($fleetStrength, 3, '.', '');
		$fleetNextStrength = number_format($fleetNextStrength, 3, '.', '');
		$fleetMaxStrength = number_format($fleetMaxStrength, 3, '.', '');
		
		// Ship inventory.
		$ship_inventory = array();
		$select_ships = sc_mysql_query('SELECT type, COUNT(*) as count FROM ships WHERE fleet_id = '.$fleet['id'].' GROUP BY type');
		while ($row = mysql_fetch_array($select_ships)) $ship_inventory[$row['type']] = $row['count'];
		
		$fleets .= '<tr class=center>'.
				   '<td><input type=text name="fleet_name['.$fleet['id'].']" value="'.stripslashes($fleet['name']).'" size=15 maxlength=20></td>'.
				   '<td><span style="color: '.$current_brcolor.';">'.$fleetStrength.'</span></td>'.
				   '<td><span style="color: '.$next_brcolor.';">'.$fleetNextStrength.'</span></td>'.
				   '<td>'.$fleetMaxStrength.'</td>'.
				   '<td>'.xlateToLocal($fleet['location']).'</td>'.
				   '<td><select name="fleet_orders['.$fleet['id'].']">'.$fleet_orders.'</select></td>'.
				   '<td>Fleet</td>'.
				   '</tr>';

		if (count($ship_inventory))
			{
			$fleets .= '<tr><td></td><td colspan=5><span class=whiteBold>'.$ship_count.' ships</span> - ';
			
			foreach ($ship_inventory as $type => $count)
				$fleets .= $type.' (<span class=whiteBold>'.$count.'</span>) ';

			$fleets .= '</td></tr>';
			}
		else
			$fleets .= '<tr><td></td><td colspan=5 align=left><span class=whiteBold>No ships</span></td></tr>';
		}

	$table = '<table width="100%"><tr><th>Name</th><th>Current BR</th><th>Next BR</th>'.
			 '<th>Max BR</th><th>Location</th><th>Orders</th><th>Type</th></tr>'.$builds;

	if ($builds and $ships)
		$table .= '<tr><th colspan=7><img class=spacerule src="images/spacerule.jpg" width="100%" height=5 alt="spacerule.jpg"></th></tr>';
	
	$table .= $ships;

	if (($builds or $ships) and $fleets)
		$table .= '<tr><th colspan=7><img class=spacerule src="images/spacerule.jpg" width="100%" height=5 alt="spacerule.jpg"></th></tr>';

	$table .= $fleets.'</table>';
	
	gameHeader($vars, 'Ships');

	ratios($player);

	if (!$builds and !$ships and !$fleets)
		echo '<div class=messageBold>You have no ships in service.</div>';
	else
		echo $table;

	footer();
}

#----------------------------------------------------------------------------------------------------------------------#
# Processes ship orders and returns wether we should recalculate the player's ratios.
#

function shipsScreen_processing($vars)
{
	$series = $vars['series_data'];
	$game = $vars['game_data'];
	$player = $vars['player_data'];
	$empire = $vars['empire_data'];
	
	if ($vars['gameAction'] == 'Cancel All Builds')
		{
		// First, restore the population penalty for colony ships.
		$conditions = array();
		$conditions[] = 'game_id = "'.$game['id'].'"';
		$conditions[] = 'owner = "'.$player['name'].'"';
		$conditions[] = 'orders = "build"';
		$conditions[] = 'type = "Colony"';

		$select = sc_mysql_query('SELECT location, COUNT(id) AS pop_delta FROM ships WHERE '.implode(' AND ', $conditions).' GROUP BY location');
		while ($row = mysql_fetch_array($select))
			{
			$conditions = array();
			$conditions[] = 'game_id = "'.$game['id'].'"';
			$conditions[] = 'coordinates = "'.$row['location'].'"';

			sc_mysql_query('UPDATE systems SET population = (population+'.$row['pop_delta'].') WHERE '.implode(' AND ', $conditions));
			}
			
		// Discard the building ships.
		$conditions = array();
		$conditions[] = 'game_id = "'.$game['id'].'"';
		$conditions[] = 'owner = "'.$player['name'].'"';
		$conditions[] = 'orders = "build"';

		sc_mysql_query('DELETE FROM ships WHERE '.implode(' AND ', $conditions));
			
		recalculateRatios($vars);
		
		return true;
		}

	if (isset($vars['ship_name']))
		{
		// If we get here, we had ships listed. We are going to update their names and orders.

		$recalculateRatios = false;

		foreach ($vars['ship_name'] as $ship_id => $new_ship_name)
			{
			if (!$ship = getShipByID($ship_id)) continue;
			
			// This should take care of cheating where someone enters a ship ID that's not his.
			if ($ship['owner'] != $player['name']) continue;

			// Comparing the name does not slow things significantly, so if we can spare
			// an update transaction with the database, let's do it.
			if ($new_ship_name != $ship['name'])
				sc_mysql_query('UPDATE ships SET name = "'.sanitizeString($new_ship_name).'" WHERE id = '.$ship_id);

			$dirty_orders = explode(':', $vars['ship_orders'][$ship_id]);
			list($orders, $order_arguments) = $dirty_orders;
			
			// If the orders are the same, we skip as well.
			if ($ship['orders'] == $orders)
				{
				// Coordinates arguments are store in galactic space in the table. If we have coordinates as
				// arguments we need to convert them. Otherwise, we keep what we had.
				$arguments = (strpos($order_arguments, ',') ? xlateToGalactic($order_arguments) : $order_arguments);
				
				// Skip this iteration if the arguments are the same as before (the orders are too).
				if ($ship['order_arguments'] == $arguments) continue;
				}

			// Make sure the orders are valid by comparing the one given with the list of possible ones.
			if (in_array($dirty_orders, getValidOrders($series, $game, $ship, $player)))
				{
				if ($orders == 'cancel')
					{
					// If we cancel building of a colony ship, we have to restore the unit of population that was taken.
					if ($ship['type'] == 'Colony')
						{
						$conditions = array();
						$conditions[] = 'game_id = "'.$game['id'].'"';
						$conditions[] = 'coordinates = "'.$ship['location'].'"';
						sc_mysql_query('UPDATE systems SET population = (population+1) WHERE '.implode(' AND ', $conditions));
						}

					// Discard the ship.
					sc_mysql_query('DELETE FROM ships WHERE id = '.$ship_id);

					$recalculateRatios = true;
					}
				else if ($orders == 'build_at')
					{
					// Implementation of the ability to change the building location of a ship.
					// Note that the orders of the ship does not change. We only change its location;
					// this way, the update_game() code does not need to be changed. We are in fact
					// emulating a build cancelation and a re-issuing of building orders on another planet.
					sc_mysql_query('UPDATE ships SET location = "'.xlateToGalactic($order_arguments).'" WHERE id = '.$ship_id);

					// Adjust populations if this is a colony ship and we are switching planets.
					if ($ship['type'] == 'Colony')
						{
						$conditions = array();
						$conditions[0] = 'game_id = '.$game['id'];
						$conditions[1] = 'coordinates = "'.$ship['location'].'"';
						sc_mysql_query('UPDATE systems SET population = (population+1) WHERE '.implode(' AND ', $conditions));

						$conditions[1] = 'coordinates = "'.xlateToGalactic($order_arguments).'"';
						sc_mysql_query('UPDATE systems SET population = (population-1) WHERE '.implode(' AND ', $conditions));
						}
					}
				else if ($orders == 'fleet')
					{
					$values = array();
					$values[] = 'fleet_id = '.$order_arguments;
					$values[] = 'orders = "fleet"';
					$values[] = 'order_arguments = "'.$order_arguments.'"';
					sc_mysql_query('UPDATE ships SET '.implode(',', $values).' WHERE id = '.$ship_id);
					}
				else
					{
					$values = array();
					$values[] = 'orders = "'.$orders.'"';
					
					if ($orders == 'morph')
						$values[] = 'order_arguments = "'.$order_arguments.'"';
					else if (ereg($orders, 'explore|move|send|open|close|create'))
						$values[] = 'order_arguments = "'.xlateToGalactic($order_arguments).'"';
					else
						$values[] = 'order_arguments = NULL';

					sc_mysql_query('UPDATE ships SET '.implode(',', $values).' WHERE id = '.$ship_id);
					}
				}
			}
		
		if ($recalculateRatios)
			recalculateRatios($vars);
		}

	// Process fleets if we had some.
	if (isset($vars['fleet_name']))
		fleetsScreen_processing($vars, true);

	return false;
}

#----------------------------------------------------------------------------------------------------------------------#

function fleetsScreen($vars)
{
	global $server, $system_cache;

	$series = $vars['series_data'];
	$game = $vars['game_data'];
	$player = $vars['player_data'];
	$empire = $vars['empire_data'];

	$fleet_ids = array();
	$ship_ids = array();

	$select = sc_mysql_query('SELECT * FROM fleets WHERE player_id = '.$player['id']);
	while ($fleet = mysql_fetch_array($select))
		{
		$valid_fleet_orders = getFleetOrders($player, $fleet);
		
		if (isset($system_cache[$fleet['location']]))
			$system = $system_cache[$fleet['location']];
		else
			{
			$system = getSystem($game['id'], $fleet['location']);
			$system_cache[$fleet['location']] = $system;
			}

		$system_name = ($system['annihilated'] ? 'Remains of ' : '').$system['name'];
		$local_coordinates = xlateToLocal($system['coordinates']);

		$fleet_orders = '';
		foreach ($valid_fleet_orders as $orders)
			{
			list($orders, $arguments) = explode(':', $orders);

			$order_arguments = (strstr($arguments, ',') ? xlateToGalactic($arguments) : $arguments);
			$selected = ($orders.':'.$order_arguments == $fleet['orders'].':'.$fleet['order_arguments']);

			$option = '<option'.($selected ? ' selected' : '');

      		switch ($orders)
      			{
       			case 'terraform':	$menu = 'Terraform '.$system_name.' ('.$local_coordinates.')';		break;
       			case 'colonize':	$menu = 'Colonize '.$system_name.' ('.$local_coordinates.')';		break;
       			case 'nuke':		$menu = 'Nuke '.$system_name.' ('.$local_coordinates.')';			break;
       			case 'invade':		$menu = 'Invade '.$system_name.' ('.$local_coordinates.')';			break;
       			case 'pickup':		$menu = 'Gather ships at '.$system_name.' ('.$local_coordinates.')';	break;
       			case 'disband':		$menu = 'Disband Fleet';											break;
				case 'disbandall':	$menu = 'Disband Fleet and Ships';									break;
				case 'standby': 	$menu = 'Standby at '.$system_name.' ('.$local_coordinates.')';		break;
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
							($destination['annihilated'] ? 'Remains of ' : '').$destination['name'].' ('.$arguments.')';
					break;
      			}
      			
			$fleet_orders .= $option.' value="'.$orders.':'.$arguments.'">'.$menu;
    		}

    	$fleet_br = 0.0;
    	$fleet_nextbr = 0.0;
    	$fleet_maxbr = 0.0;
			
		$fleetStrength = 0.0;
		$fleetNextStrength = 0.0;
		$fleetMaxStrength = 0.0;

		$ship_list = '';

    	if ($fleet['collapsed'] == 0)
			{
    		$select_ships = sc_mysql_query('SELECT * FROM ships WHERE fleet_id = '.$fleet['id'].' ORDER BY type, location, max_br ASC');
			
			if (mysql_num_rows($select_ships))
				$ship_list = '<tr><th colspan=7 style="vertical-align: top;">'.
							 '<img class=spaceruleThin style="margin-bottom: 5pt; margin-top: 5pt;" src="images/spacerule.jpg"></th></tr>'.
							 '<tr><th>Ship Name</th><th>Current BR</th><th>Next BR</th><th>Max BR</th><th>Orders</th><th colspan=2>Type</th></tr>';
				 
			while ($ship = mysql_fetch_array($select_ships))
				{
				$valid_orders = getValidOrders($series, $game, $ship, $player);

				$ship_orders = fixOrders($valid_orders, $series, $game, $player, $ship);
				$ship_orders = implode('', $ship_orders);

				$fleetStrength += $ship['br']*$ship['br'];
				$fleetNextStrength += $ship['max_br']*$ship['max_br'];
				$fleetMaxStrength += pow(min($ship['br']*$player['mineral_ratio'], $ship['max_br']), 2.0);

				$next_br = min($ship['br']*$player['mineral_ratio'], $ship['max_br']);

				$brcolor = brColor($ship['br'], $ship['max_br']);
				$next_brcolor = brColor($next_br, $ship['max_br']);

				$ship_list .= '<tr class=center>'.
							  '<td><input type=text name="ship_name['.$ship['id'].']" value="'.stripslashes($ship['name']).'" size=15 maxlength=20></td>'.
							  '<td><span style="color: '.$brcolor.';">'.number_format($ship['br'],  3, '.', '').'</span></td>'.
							  '<td><span style="color: '.$next_brcolor.';">'.number_format($next_br,  3, '.', '').'</span></td>'.
							  '<td>'.number_format($ship['max_br'], 3, '.', '').' </td>'.
							  '<td><select name="ship_orders['.$ship['id'].']">'.$ship_orders.'</select></td>'.
							  '<td colspan=2>'.$ship['type'].'</td></tr>';

				$ship_ids[] = $ship['id'];
				}
			}
		else
			{
				$ship_list = '<tr><th colspan=7 style="vertical-align: top;">'.
							 '<img class=spaceruleThin style="margin-bottom: 5pt; margin-top: 5pt;" src="images/spacerule.jpg"></th></tr>';
			$ship_count = 0;
			$ship_inventory = array();
			$select_ships = sc_mysql_query('SELECT type, COUNT(id) as count FROM ships WHERE fleet_id = '.$fleet['id'].' GROUP BY type');
			while ($row = mysql_fetch_array($select_ships))
				{
				$ship_count += $row['count'];
				$ship_inventory[$row['type']] = $row['count'];
				}

    		$fields = array();
			$fields[] = 'SUM(POW(br, 2)) as fleetStrength';
			$fields[] = 'SUM(POW(LEAST(br*'.($player['mineral_ratio'] ? $player['mineral_ratio'] : 0).', max_br), 2)) as fleetNextStrength';
			$fields[] = 'SUM(POW(max_br, 2)) as fleetMaxStrength';

			$select_ships = sc_mysql_query('SELECT '.implode(',', $fields).' FROM ships WHERE fleet_id = '.$fleet['id']);
			
			$fleetStrength = mysql_result($select_ships, 0, 0);
			$fleetNextStrength = mysql_result($select_ships, 0, 1);
			$fleetMaxStrength = mysql_result($select_ships, 0, 2);

			$ship_list .= '<tr class=center><td colspan=7><span class=whiteBold>'.$ship_count.' ship'.($ship_count != 1 ? 's' : '').'</span> - ';

			foreach (array_keys($ship_inventory) as $type)
				$ship_list .= $type.' (<span class=whiteBold>'.$ship_inventory[$type].'</span>) ';

			$ship_list .= '</td></tr>';
			}

		$fleet_br = sqrt($fleetStrength);
		$fleet_nextbr = sqrt($fleetNextStrength);
		$fleet_maxbr = sqrt($fleetMaxStrength);

		$brcolor = '';
		$next_brcolor = '';
		
		if ($fleet_maxbr > 0)
			{
			$brcolor = brColor($fleet_br, $fleet_maxbr);
			$next_brcolor = brColor($fleet_nextbr, $fleet_maxbr);
			}
		
		ob_start();
?>
<table width="100%" border=1>
	<tr class=center>
		<td>
			<table width="100%" border=0 cellpadding=0>
				<tr>
					<th>Fleet Name</th>
					<th>Current BR</th>
					<th>Next BR</th>
					<th>Max BR</th>
					<th>Orders</th>
					<th>Location</th>
					<th rowspan=2>
<?
		if ($fleet['collapsed'])
			echo '<input type=submit name="expand['.$fleet['id'].']" value="Expand">';
		else
			echo '<input type=submit name="collapse['.$fleet['id'].']" value="Collapse">';
?>
					</th>
				</tr>
				<tr class=center>
					<td><input type=text name="fleet_name[<? echo $fleet['id']; ?>]" value="<? echo stripslashes($fleet['name']); ?>" size=14 maxlength=20></td>
					<td style="color: <? echo $brcolor; ?>;"><? echo number_format($fleet_br, 3, '.', ''); ?></td>
				   	<td style="color: <? echo $next_brcolor; ?>;"><? echo number_format($fleet_nextbr, 3, '.', ''); ?></td>
				   	<td><? echo number_format($fleet_maxbr, 3, '.', ''); ?></td>
				   	<td><select name="fleet_orders[<? echo $fleet['id']; ?>]"><? echo $fleet_orders; ?></select></td>
				   	<td><? echo ($system['annihilated'] ? 'Remains of ': '').$system['name'].' ('.xlateToLocal($system['coordinates']).')'; ?></td>
				</tr>
<?
		if ($ship_list) echo $ship_list;
?>
			</table>
		</td>
	</tr>
</table>
<?
		$fleet_list .= ob_get_contents();
		ob_end_clean();
		
		$fleet_ids[] = $fleet['id'];
		}

	gameHeader($vars, 'Fleets');

	ratios($player);

	// Location list for new fleets.
	$fields = array();
	$fields[] = 'location';
	$fields[] = 'COUNT(ships.id) as ships';
	$fields[] = 'systems.name as name';
	$fields[] = 'systems.annihilated as annihilated';
	
	$conditions = array();
	$conditions[] = 'ships.game_id = '.$game['id'];
	$conditions[] = 'ships.owner = "'.$vars['name'].'"';
	$conditions[] = 'FIND_IN_SET(ships.type, "Stargate,Satellite,Minefield") = 0';
	
	$from = 'ships INNER JOIN systems ON ships.game_id = systems.game_id AND ships.location = systems.coordinates';
	
	$select = sc_mysql_query('SELECT '.implode(',', $fields).' FROM '.$from.' WHERE '.implode(' AND ', $conditions).' GROUP BY location ORDER BY name ASC');	
	
	if (mysql_num_rows($select))
		{
?>
<div>
<table style="margin-left: auto; margin-right: auto;">
	<caption class=tableCaption>Form a New Fleet</caption>
	<tr>
		<th style="text-align: right;">Name:</th>
		<td><input type=text size=20 maxlength=20 name="new_fleet_name"></td>
	</tr>
	<tr>
		<th style="text-align: right;">Location:</th>
		<td>
			<select name="new_fleet_location">
<?
		while ($row = mysql_fetch_array($select))
			echo '<option value="'.xlateToLocal($row['location']).'">'.
				 ($row['annihilated'] ? 'Remains of ' : '').
				 $row['name'].' ('.xlateToLocal($row['location']).') - '.$row['ships'].' ship'.($row['ships'] > 1 ? 's' : '');
?>
			</select>
		</td>
	</tr>
	<tr>
		<td colspan=2 style="text-align: right;">
			<input type=submit name="new_fleet:create" value="Create empty fleet">
			<input type=submit name="new_fleet:create_and_gather" value="Create and gather ships">
		</td>
	</tr>
</table>
</div>
<?
		}
	else
		echo '<div class=messageBold>You have no ships to gather in fleets.</div>'.$new_fleet;
	
	echo '<input type=hidden name=fleet_ids value="'.implode(' ', $fleet_ids).'">'.
		 '<input type=hidden name=ship_ids value="'.implode(' ', $ship_ids).'">'.
		 '<div style="margin-top: 10pt;">'.$fleet_list.'</div>';

	footer();
}

#
#-----------------------------------------------------------------------------------------------------------------------------------------#
# Process instructions from the Fleets screen. Since fleets are also processed from the Ships screen, 
# the $ships_processed arguments indicates if we need to process the ships (or else they'll be processed twice).
#

function fleetsScreen_processing($vars, $ships_processed = false)
{

	$series = $vars['series_data'];
	$game = $vars['game_data'];
	$player = $vars['player_data'];
	$empire = $vars['empire_data'];
	
	$create = '';
	$dismantle_all = array();

	// Start with the ships we see on the fleet screen.
	if (isset($vars['ship_name']) and !$ships_processed)
		{
		foreach ($vars['ship_name'] as $ship_id => $new_ship_name)
			{
			if (!$ship = getShipByID($ship_id)) continue;
			if ($ship['owner'] != $player['name']) continue;
			
			// Update the ship's name; we specify the owner field to make sure no cheater affects fleets he doesn't own.
			if ($ship['name'] != $new_ship_name)
				sc_mysql_query('UPDATE ships SET name = "'.addslashes($new_ship_name).'" WHERE id = '.$ship_id);
				
			$dirty_orders = explode(':', $vars['ship_orders'][$ship_id]);
			list($orders, $order_arguments) = $dirty_orders;
			
			// If the orders are the same, we skip as well.
			if ($ship['orders'] == $orders)
				{
				// Coordinates arguments are store in galactic space in the table. If we have coordinates as
				// arguments we need to convert them. Otherwise, we keep what we had.
				$arguments = (strpos($order_arguments, ',') ? xlateToGalactic($order_arguments) : $order_arguments);
				
				if ($ship['order_arguments'] == $arguments) continue;
				}

			$valid_orders = getValidOrders($series, $game, $ship, $player);

			// Make sure the orders are valid by comparing the one given with the list of possible ones.
			// Also, check the owner of this ship. Someone else could order a ship to be dismantled
			// by hacking the ship screen HTML and specifying someone else's ship ID.
			if (in_array($dirty_orders, $valid_orders))
				{
				list($orders, $arguments) = $dirty_orders;
				
				$values = array();
				
				if (ereg($orders, 'explore|move'))
					{
					$values[] = 'fleet_id = 0';
					$values[] = 'orders = "'.$orders.'"';
					$values[] = 'order_arguments = "'.xlateToGalactic($arguments).'"';
					}
				else if ($orders == 'fleet')
					{
					$values[] = 'fleet_id = '.$arguments;
					$values[] = 'orders = "fleet"';
					$values[] = 'order_arguments = "'.$arguments.'"';
					}
				else if (ereg($orders, 'close|open'))
					{
					$values[] = 'fleet_id = 0';
					$values[] = 'orders = "'.$orders.'"';
					$values[] = 'order_arguments = "'.xlateToGalactic($arguments).'"';
					}
				else
					{
					$values[] = 'fleet_id = 0';
					$values[] = 'orders = "'.$orders.'"';
					$values[] = 'order_arguments = "'.$arguments.'"';
					}

				sc_mysql_query('UPDATE ships SET '.implode(',', $values).' WHERE id = '.$ship_id);
				}
			}
		}
		
	if (isset($vars['fleet_name']))
		{
		foreach ($vars['fleet_name'] as $fleet_id => $new_fleet_name)
			{
			if (!$fleet = getFleetByID($fleet_id)) continue;
			if ($fleet['owner'] != $vars['name']) continue;

			if ($fleet['name'] != $new_fleet_name)
				sc_mysql_query('UPDATE fleets SET name = "'.addslashes($new_fleet_name).'" WHERE id = '.$fleet_id);
				
			list($orders, $order_arguments) = explode(':', $vars['fleet_orders'][$fleet_id]);

			// If the orders are the same, we skip as well.
			if ($fleet['orders'] == $orders)
				{
				// Coordinates arguments are store in galactic space in the table. If we have coordinates as
				// arguments we need to convert them. Otherwise, we keep what we had.
				$arguments = (strpos($order_arguments, ',') ? xlateToGalactic($order_arguments) : $order_arguments);

				if ($fleet['order_arguments'] == $arguments) continue;
				
				$dirty_orders = $orders.':'.$order_arguments;
				}
			else
				$dirty_orders = $orders.($order_arguments ? ':'.$order_arguments : '');

			if (in_array($dirty_orders, getFleetOrders($player, $fleet)))
				{
				switch ($orders)
					{
					case 'disband':
						sc_mysql_query('UPDATE ships SET orders = "standby", order_arguments = NULL, fleet_id = 0 WHERE fleet_id = '.$fleet_id);
						sc_mysql_query('DELETE FROM fleets WHERE id = '.$fleet_id);
						break;
					case 'disbandall':
						sc_mysql_query('UPDATE ships SET orders = "dismantle", order_arguments = NULL, fleet_id = 0 WHERE fleet_id = '.$fleet_id);
						sc_mysql_query('DELETE FROM fleets WHERE id = '.$fleet_id);
						break;
					case 'pickup':
						$values = array();
						$values[] = 'orders = "fleet"';
						$values[] = 'order_arguments = '.$fleet_id;
						$values[] = 'fleet_id = '.$fleet_id;
						
						$conditions = array();
						$conditions[] = 'game_id = '.$game['id'];		
						$conditions[] = 'owner = "'.$vars['name'].'"';
						$conditions[] = 'location = "'.$fleet['location'].'"';
						$conditions[] = 'fleet_id = 0';
						$conditions[] = 'FIND_IN_SET(type, "Stargate,Jumpgate,Satellite,Minefield") = 0';
						$conditions[] = 'orders = "standby"';				

						sc_mysql_query('UPDATE ships SET '.implode(',', $values).' WHERE '.implode(' AND ', $conditions));
						break;
					case 'standby':
					case 'nuke':
					case 'colonize':
					case 'terraform':
					case 'invade':
						sc_mysql_query('UPDATE fleets SET orders = "'.$orders.'", order_arguments = NULL WHERE id = '.$fleet_id);
						break;
					case 'move':
						$arguments = xlateToGalactic($order_arguments);							
						sc_mysql_query('UPDATE fleets SET orders = "move", order_arguments = "'.$arguments.'" WHERE id = '.$fleet_id);
						break;
					}
				}
			}
		}

	if (isset($vars['expand']))
		{
		sc_mysql_query('UPDATE fleets SET collapsed = "0" WHERE id = '.key($vars['expand']).' AND owner = "'.$player['name'].'"');
		return sendGameMessage($player, 'Fleet expanded.');
		}
	else if (isset($vars['collapse']))
		{
		sc_mysql_query('UPDATE fleets SET collapsed = "1" WHERE id = '.key($vars['collapse']).' AND owner = "'.$player['name'].'"');
		return sendGameMessage($player, 'Fleet collapsed.');
		}

	if ($vars['new_fleet:create'])
		{
		$vars['new_fleet_name'] = ($vars['new_fleet_name'] == '' ? nameFleet() : $vars['new_fleet_name']);

		$field_values = array();
		$field_values[] = 'series_id = '.$vars['series_id'];
		$field_values[] = 'game_number = '.$vars['game_number'];
		$field_values[] = 'game_id = '.$game['id'];
		$field_values[] = 'player_id = '.$player['id'];
		$field_values[] = 'name = "'.addslashes(htmlspecialchars($vars['new_fleet_name'])).'"';
		$field_values[] = 'owner = "'.$vars['name'].'"';
		$field_values[] = 'location = "'.xlateToGalactic($vars['new_fleet_location']).'"';
		$field_values[] = 'orders = "standby"';

		sc_mysql_query('INSERT INTO fleets SET '.implode(',', $field_values));

		return sendGameMessage($player, 'Fleet created.');
		}
	else if ($vars['new_fleet:create_and_gather'])
		{
		$vars['new_fleet_name'] = ($vars['new_fleet_name'] == '' ? nameFleet() : $vars['new_fleet_name']);

		$field_values = array();
		$field_values[] = 'series_id = '.$vars['series_id'];
		$field_values[] = 'game_number = '.$vars['game_number'];
		$field_values[] = 'game_id = '.$game['id'];
		$field_values[] = 'player_id = '.$player['id'];
		$field_values[] = 'name = "'.$vars['new_fleet_name'].'"';
		$field_values[] = 'owner = "'.$vars['name'].'"';
		$field_values[] = 'location = "'.xlateToGalactic($vars['new_fleet_location']).'"';
		$field_values[] = 'orders = "standby"';

		sc_mysql_query('INSERT INTO fleets SET '.implode(',', $field_values));
		
		$new_fleet_id = mysql_insert_id();
		
		$conditions = array();
		$conditions[] = 'game_id = '.$game['id'];		
		$conditions[] = 'owner = "'.$vars['name'].'"';
		$conditions[] = 'location = "'.xlateToGalactic($vars['new_fleet_location']).'"';
		$conditions[] = 'fleet_id = 0';
		$conditions[] = 'FIND_IN_SET(type, "Stargate,Satellite,Minefield") = 0';
		$conditions[] = 'orders = "standby"';				
		sc_mysql_query('UPDATE ships SET orders = "fleet", order_arguments = "'.$new_fleet_id.'", fleet_id = '.$new_fleet_id.' WHERE '.implode(' AND ', $conditions));
						
		sc_mysql_query('UPDATE fleets SET collapsed = "1" WHERE id = '.$new_fleet_id.' AND owner = "'.$vars['name'].'"');
			
		return sendGameMessage($player, 'Fleet created and ships gathered.');
		}
	else if ($vars['new_fleet:cancel'])
		return sendGameMessage($player, 'Fleet creation cancelled.');
}

#----------------------------------------------------------------------------------------------------------------------#

function getFleetOrders($player, $fleet)
{
	global $system_cache, $moving_ships;
	
	// Make sure we don't waste our time here (it happens).
	if ($fleet['game_id'] == '') return array();

	// Fleet's location data.
	if (isset($system_cache[$fleet['location']]))
		$system = $system_cache[$fleet['location']];
	else
		{
		$system = getSystem($player['game_id'], $fleet['location']);
		$system_cache[$fleet['location']] = $system;
		}
		
	if (!$system) return array();

	// Diplomatic status the player has with the owner of the planet where this fleet is.
	$diplomacy = getDiplomacyWithOpponent($fleet['game_id'], $fleet['owner'], $system['owner']);
	
	$orders = array();
   	$orders[] = 'standby';
	$orders[] = 'pickup';
	$orders[] = 'disband';
	$orders[] = 'disbandall';

	// Get the possible movement orders for this fleet.
	if (strlen($system['jumps']))
		{
		$conditions = array();
   		$conditions[] = 'player_id = '.$player['id'];
		$conditions[] = '(coordinates = "'.implode('" OR coordinates = "', explode(' ', $system['jumps'])).'")';
		
		$select = sc_mysql_query('SELECT coordinates FROM explored WHERE '.implode(' AND ', $conditions));
		while ($row = mysql_fetch_array($select)) $orders[] = 'move:'.xlateToLocal($row['coordinates']);
		}

	// What this basically does is determine if a given ship type is present in the fleet, thus allowing a "global" order to be given to all ships.
	$fields = array();
	$fields[] = 'SUM(IF(cloaked = "0" AND "'.$diplomacy['status'].'" = 2, 1, 0)) AS can_nuke';
	$fields[] = 'SUM(IF(type = "Troopship" AND "'.$diplomacy['status'].'" = 2, 1, 0)) AS can_invade';
	$fields[] = 'SUM(IF(type = "Colony" AND "'.$system['owner'].'" = "" AND "'.$system['annihilated'].'" = "0", 1, 0)) AS can_colonize';
	$fields[] = 'SUM(IF(type = "Terraformer" AND owner = "'.$system['owner'].'" AND '.$system['agriculture'].' < '.max($system['mineral'], $system['fuel']).', 1, 0)) AS can_terraform';

	$select = sc_mysql_query('SELECT '.implode(',', $fields).' FROM ships WHERE fleet_id = '.$fleet['id']);
	$fleet_ships = mysql_fetch_array($select);

    if ($fleet_ships['can_nuke']) $orders[] = 'nuke';
	if ($fleet_ships['can_invade']) $orders[] = 'invade';
	if ($fleet_ships['can_colonize']) $orders[] = 'colonize';
	if ($fleet_ships['can_terraform']) $orders[] = 'terraform';

	// Doomsday ships could get the same treatment. Science ships would need to split up from the mother fleet (into a new one?).

	sort($orders);

	return $orders;
}

#----------------------------------------------------------------------------------------------------------------------#
# Returns an array of all the valid orders for a ship. Each element of this array is itself an array consisting of
# the order as the first element and any relevant order argument as the second element.
#

function getValidOrders($series, $game, $ship, $player)
{
	global $moving_ships, $server, $valid_orders_cache, $system_cache;

	// Don't waste time if we somehow have an empty ship ID. This should NOT happen.
	if ($ship['id'] == '') return array();
	
	$orders = array();

	// If it's currently building; that's all we can do.
	// Here we add all the possible builders, so that the player can switch the building location.
	if ($ship['orders'] == 'build' and isset($valid_orders_cache[$ship['location']]['build'][$ship['type']]))
		return $valid_orders_cache[$ship['location']]['build'][$ship['type']];
	else if ($ship['orders'] == 'build')
		{
		$build_at = array();
		$build_at[] = array('build', ''); // Default order; build the ship at its current location.
		$build_at[] = array('cancel', '');

		// Get all the planets the player can build on, except where he is now.
		$conditions = array();
		$conditions[] = 'game_id = '.$game['id'];
		$conditions[] = 'owner = "'.$ship['owner'].'"';
		$conditions[] = 'coordinates <> "'.$ship['location'].'"';
		$conditions[] = 'population >= '.$server['builder_population'];
		
		$select = sc_mysql_query('SELECT coordinates FROM systems WHERE '.implode(' AND ', $conditions).' ORDER BY name, coordinates ASC');
		while ($system = mysql_fetch_array($select))
			$build_at[] = array('build_at', xlateToLocal($system['coordinates']));

		// Cache this list for later use.
		$valid_orders_cache[$ship['location']]['build'][$ship['type']] = $build_at;
		
		return $valid_orders_cache[$ship['location']]['build'][$ship['type']];
		}

	// Get a list of our friends in Shared HQ games.
	$friends = array();
	if ($series['diplomacy'] == 6)
		{
		$conditions = array();
		$conditions[] = 'game_id = '.$game['id'];
		$conditions[] = 'empire = "'.$ship['owner'].'"';
		$conditions[] = 'status = "6"';
		
		$select = sc_mysql_query('SELECT opponent FROM diplomacies WHERE '.implode(' AND ', $conditions));
		while ($row = mysql_fetch_array($select)) $friends[] = $row['opponent'];
		}
		
	// If we already cached the order list of this ship type at this location, don't bother recreating it.
	// We don't cache orders for some ship types due to the variable nature of their special orders.
	//if (!ereg($ship['type'], 'Cloaker|Stargate|Jumpgate|Builder'))
	//	if (isset($valid_orders_cache[$ship['location']]['nonbuild'][$ship['type']]))
	//		return $valid_orders_cache[$ship['location']]['nonbuild'][$ship['type']];

	// Ok, so we don't have cached orders for this ship. Let's determine them.
	
	// Get data for the system we are in, and diplomatic status with the system's owner.
	
	$query = 'SELECT systems.*, diplomacies.status AS dip_status '.
			 'FROM systems LEFT JOIN diplomacies ON systems.game_id = diplomacies.game_id AND systems.owner = diplomacies.opponent AND diplomacies.empire = "'.$player['name'].'"'.
			 'WHERE systems.game_id = '.$game['id'].' AND systems.coordinates = "'.$ship['location'].'"';

	$select = sc_mysql_query($query);
	$system = mysql_fetch_array($select);

	$jumps = ($system['jumps'] ? explode(' ', $system['jumps']) : array());

	// All ships have this order.
	$orders[] = array('standby', '');

	if (in_array($ship['type'], $moving_ships))
		{
		// All moving ships can join fleets; here we get a listing of all the fleets present in this system.
		$conditions = array();
		$conditions[] = 'game_id = '.$game['id'];
		$conditions[] = 'owner = "'.$ship['owner'].'"';
		$conditions[] = 'location = "'.$ship['location'].'"';

		$select = sc_mysql_query('SELECT id FROM fleets WHERE '.implode(' AND ', $conditions));
		while ($fleet = mysql_fetch_array($select)) $orders[] = array('fleet', $fleet['id']);

		// Here we determine where the ship may move to. Skip if there are no jumps.
		if ($system['jumps'])
			{
			// This array tracks what planets we see so we can determine which ones
			// to add to a science ship's orders.
			$explored = array();
			
			// We use the explored table for this. In Shared HQ games, we have some extra records flagged
			// with the 'from_shared_hq' field. We don't need any extra processing to include them.
			$conditions = array();
			$conditions[] = 'player_id = '.$player['id'];
			$conditions[] = '(coordinates = "'.implode('" OR coordinates = "', $jumps).'")';
				
			$select = sc_mysql_query('SELECT coordinates FROM explored WHERE '.implode(' AND ', $conditions).' LIMIT 4');
			while ($row = mysql_fetch_array($select))
				{
				$orders[] = array('move', xlateToLocal($row['coordinates']));
				$explored[$row['coordinates']] = true; // So we fail the test below: we see the planet!
				}

			// If the system is not explored and the ship is a science ship, the ship can explore it.
			foreach ($jumps as $jump)
				if (!$explored[$jump] and $ship['type'] == 'Science')
				{
					$orders[] = array('explore', xlateToLocal($jump));
				}
			}
		
		// All uncloaked moving ships can nuke a planet if at war with the owner.
		if ($system['dip_status'] == 2 and !$ship['cloaked'])
			$orders[] = array('nuke', '');
		}
	
	// Special case for Morphers. We use a special field, since their type can change.
	if ($ship['morpher'])
		{
		// We list all of the player's tech, except the current one.
		// The Morpher tech is included; maybe the player wants it for some reason.
		foreach (explode(' ', $player['techs']) as $ship_type)
			if ($ship['type'] != $ship_type)
				$orders[] = array('morph', $ship_type);
		}

	// Other ship-type-specific orders.
	switch ($ship['type'])
		{
		case 'Troopship':
			if ($system['dip_status'] == 2)
				$orders[] = array('invade', '');
			break;
		case 'Doomsday':
			// Empty or ennemy non-annihilated planets.
			if (!$system['annihilated'] and ($system['owner'] == '' or $system['dip_status'] == 2))
				$orders[] = array('destroy', '');
			break;
		case 'Colony':
			if (!$system['annihilated'] and $system['owner'] == '')
				$orders[] = array('colonize', '');
			break;
		case 'Terraformer':
			// Skip this if the system can't be terraformed anymore or if it's not even colonized.
			if ($system['owner'] == '' or $system['agriculture'] >= max($system['mineral'], $system['fuel']))
				break;
			
			// Is it ours?
			if ($system['owner'] == $ship['owner'])
				{
				$orders[] = array('terraform', '');
				break;
				}

			// In Shared HQ games, we can terraform friendly planets.
			if ($series['diplomacy'] == 6 and in_array($system['owner'], $friends))
				$orders[] = array('terraform', '');
			break;
		case 'Cloaker':
			$orders[] = array(($ship['cloaked'] ? 'uncloak' : 'cloak'), '');
			break;
		case 'Builder':
			if ($ship['br'] < $series['builder_cost']/10)
				break;
				
			list($x,$y) = explode(',', $ship['location']);

			foreach (potentialJumps($x,$y) as $jump)
				if (!getSystem($game['id'], $jump))
					$orders[] = array('create', xlateToLocal($jump));
			break;
		case 'Engineer':
			foreach ($jumps as $jump) $orders[] = array('close', xlateToLocal($jump));
			
			// Possibility to open a closed jump. We skip if we already have four jumps here.
			if (count($jumps) < 4)
				{
				list($x,$y) = explode(',', $ship['location']);
		
				$potential_jumps = array();
				foreach (potentialJumps($x,$y) as $jump)
					if (!in_array($jump, $jumps))
						$potential_jumps[] = $jump;
				
				$conditions = array();
				$conditions[] = 'game_id = '.$game['id'];
				$conditions[] = '(coordinates = "'.implode('" OR coordinates = "', $potential_jumps).'")';

				$select = sc_mysql_query('SELECT coordinates FROM systems WHERE '.implode(' AND ', $conditions).' LIMIT 4');
				while ($row = mysql_fetch_array($select)) $orders[] = array('open', xlateToLocal($row['coordinates']));
				}
			break;
		case 'Stargate':
			$query_for_owner = 'SELECT name, coordinates FROM systems '.
							   'WHERE game_id = '.$game['id'].' AND coordinates <> "'.$ship['location'].'" AND owner = "'.$ship['owner'].'"';
			
			// Add friendly planets if we are in a Shared HQ game.
			if ($series['diplomacy'] == 6)
				{
				$queries = array();
				
				foreach ($friends as $friend)
					{
					$queries_for_friends[] = '(SELECT name, coordinates FROM systems '.
											  'WHERE game_id = '.$game['id'].' AND coordinates <> "'.$ship['location'].'" AND owner = "'.$friend.'")';
					}
					
				if (count($queries_for_friends))
					$query_for_friends = implode(' UNION ', $queries_for_friends);
				}

			if (isset($query_for_friends))
				$query = '('.$query_for_owner.') UNION '.$query_for_friends.' ORDER BY name, coordinates ASC';
			else
				$query = $query_for_owner.' ORDER BY name, coordinates ASC';

			$select = sc_mysql_query($query);
			while ($system = mysql_fetch_array($select))
				$orders[] = array('send', xlateToLocal($system['coordinates']));
			break;
		case 'Jumpgate':
			// Jumpgates send ships up to BR*(range divisor) planets away, and the destination must
			// not be annihilated. We calculate the range first.
	
			list($x,$y) = explode(',', $ship['location']);

			$range = floor($ship['br']*$series['jumpgate_range_multiplier']);

			$min_x = $x-$range;
			$max_x = $x+$range;
			$min_y = $y-$range;
			$max_y = $y+$range;
	
			$conditions = array();
			$conditions[] = 'systems.game_id = '.$game['id'];
			$conditions[] = 'systems.coordinates <> "'.$ship['location'].'"';
			$conditions[] = 'systems.annihilated = "0"';
			$conditions[] = 'explored.empire = "'.$player['name'].'"';

			$query = 'SELECT systems.name, systems.coordinates '.
					 'FROM systems INNER JOIN explored ON systems.game_id = explored.game_id AND systems.coordinates = explored.coordinates '.
					 'WHERE '.implode(' AND ', $conditions).' ORDER BY systems.name ASC';

			$select = sc_mysql_query($query);

			while ($system = mysql_fetch_array($select))
				{
				list($x,$y) = explode(',', $system['coordinates']);
				
				if ($x >= $min_x and $x <= $max_x and $y >= $min_y and $y <= $max_y)
					$orders[] = array('send', xlateToLocal($system['coordinates']));
				}
			break;
		}

 	$orders[] = array('dismantle', '');

	// Store the orders in the cache for future use.
	$valid_orders_cache[$ship['location']]['nonbuild'][$ship['type']] = $orders;

 	return $valid_orders_cache[$ship['location']]['nonbuild'][$ship['type']];
}

#----------------------------------------------------------------------------------------------------------------------#
# Returns orders for a ship, from getValidOrders(), as an array of <option>s for display to the user.
#

function fixOrders($dirty_orders, $series, $game, $player, $ship)
{
	global $system_cache;
	
	list($current_orders, $current_order_arguments) = array($ship['orders'], $ship['order_arguments']);

	if ($current_orders != '')
		if (ereg($current_orders, 'explore|move|send|open|close|create'))
			$current_order_arguments = xlateToLocal($current_order_arguments);

	for ($fixed_orders = array(), $x = 0, $j = 0; $j < count($dirty_orders); $j++, $x++)
  		{
  		$selected = false;

    	// Use the cache if we already looked up this system.
		if (isset($system_cache[$ship['location']]))
			$system = $system_cache[$ship['location']];
    	else
			{
			$system = getSystem($game['id'], $ship['location']);
			$system_cache[$ship['location']] = $system;
			}

		$system_name = ($system['annihilated'] ? 'Remains of ': '').$system['name'];
    	$local_coordinates = xlateToLocal($system['coordinates']);

    	switch ($dirty_orders[$j][0]) // The order itself; the arguments are in $dirty_orders[$j][1].
    		{
     		case 'destroy':		$fixed_orders[$x] = 'Annihilate '.$system_name.' ('.$local_coordinates.')';	break;
     		case 'invade':		$fixed_orders[$x] = 'Invade '.$system_name.' ('.$local_coordinates.')';		break;
			case 'terraform':	$fixed_orders[$x] = 'Terraform '.$system_name.' ('.$local_coordinates.')';	break;
			case 'cloak':		$fixed_orders[$x] = 'Cloak';												break;
			case 'uncloak':		$fixed_orders[$x] = 'Uncloak';												break;
			case 'colonize':	$fixed_orders[$x] = 'Colonize '.$system_name.' ('.$local_coordinates.')';	break;
			case 'nuke':		$fixed_orders[$x] = 'Nuke '.$system_name.' ('.$local_coordinates.')';		break;
			case 'dismantle':	$fixed_orders[$x] = 'Dismantle';											break;
			case 'standby':		$fixed_orders[$x] = 'Standby at '.$system_name.' ('.$local_coordinates.')';	break;
			case 'build':		$fixed_orders[$x] = 'Build at '.$system_name.' ('.$local_coordinates.')';	break;
			case 'cancel':		$fixed_orders[$x] = 'Cancel Build';											break;
			default:
				{
				list($orders, $order_arguments) = $dirty_orders[$j];

      			if ($orders == 'fleet')
					{
	  				$fleet = getFleetByID($order_arguments);
	  				$fixed_orders[$x] = 'Join fleet '.$fleet['name'];

	  				if ($ship['fleet_id'] == $fleet['id']) $selected = true;
	  				}
				else
					{
					$galactic_coordinates = xlateToGalactic($order_arguments);

					if (isset($system_cache[$galactic_coordinates]))
						$system = $system_cache[$galactic_coordinates];
					else
						{
						$system = getSystem($game['id'], $galactic_coordinates);
						$system_cache[$galactic_coordinates] = $system;
						}
					
					$system_name = ($system['annihilated'] ? 'Remains of ' : '').$system['name'];

					switch ($orders)
						{
						case 'build_at':	$fixed_orders[$x] = 'Build at '.$system_name.' ('.$order_arguments.')';	break;
						case 'create':		$fixed_orders[$x] = 'Create a new system to the '.getDirection($ship['location'], $galactic_coordinates).' ('.$order_arguments.')'; break;
						case 'morph':		$fixed_orders[$x] = 'Change into '.$order_arguments;					break;
						case 'send':		$fixed_orders[$x] = 'Send to '.$system_name.' ('.$order_arguments.')';	break;

						case 'explore':
						case 'move':
							$direction = getDirection($ship['location'], $galactic_coordinates);
							$destination = ($orders == 'explore' ? '('.$order_arguments.')' : 'to '.$system_name.' ('.$order_arguments.')');
							$fixed_orders[$x] = ucfirst($orders).' '.$direction.' '.$destination;
							break;

						case 'close':
						case 'open':
							$explored = explored($player, $galactic_coordinates);
							$direction = getDirection($ship['location'], $galactic_coordinates);
							$system_name = ($explored ? $system_name : '(unexplored)');
							$fixed_orders[$x] = ucfirst($orders).' '.$direction.' '.$system_name.' ('.$order_arguments.')';
							break;
						}
					}
				}
    		}

		if (!$ship['fleet_id'] and $dirty_orders[$j][0] == $current_orders and $dirty_orders[$j][1] == $current_order_arguments)
			$selected = true;

		$fixed_orders[$x] = '<option '.($selected ? 'selected ' : '').
							'value="'.$dirty_orders[$j][0].':'.$dirty_orders[$j][1].'">'.$fixed_orders[$x];
  		}

	// Don't sort Stargate orders; that was done in the original SQL query.
	// Build orders have the "cancel" item as the second choice; we keep this convenience.
	if ($ship['type'] != 'Stargate' and $ship['type'] != 'Jumpgate' and $ship['orders'] != 'build')
		sort($fixed_orders);
	
	return $fixed_orders;
}

#----------------------------------------------------------------------------------------------------------------------#

function nameFleet()
{
	global $server;
	
	if ($server['fleetNameSource'] == 'random')
		return randomName();
	else if ($server['fleetNameSource'] != '')
		{		
		$select_word_count = sc_mysql_query('SELECT COUNT(*) FROM '.$server['fleetNameSource']);
		$random_id = rand(1, mysql_result($select_word_count, 0, 0));
		
		$select = sc_mysql_query('SELECT word FROM '.$server['fleetNameSource'].' WHERE id = '.$random_id);
		$word = mysql_fetch_array($select);
		
		return ucfirst($word['word']);
		}
	else
		return('');
}

#----------------------------------------------------------------------------------------------------------------------#
# Returns a hexidecimal RGB value for a BR, depending on how close it is to its maximum value.
# This could probably be tweaked some more; the colors don't always look right.
#
# The continuum is:
#	[severely damaged] (red) -> (orange) -> (yellow) -> (green) [healthy]
#

function brColor($br, $max_br)
{
	if ($br < 1) return '#FF0000';
	else if ($br == $max_br) return '#00FF00';
	
	$color = floor(($br/$max_br)*255);
	$color = max($color, 150); // Don't get to dark in that yellow.
	
	return '#'.str_pad(dechex($color), 2, '0', STR_PAD_LEFT).str_pad(dechex($color), 2, '0', STR_PAD_LEFT).'00';
}

#----------------------------------------------------------------------------------------------------------------------#
# Returns an array of potential jumps coming out of a system.
#

function potentialJumps($x, $y)
{
	return array(($x+1).','.$y, $x.','.($y+1), ($x-1).','.$y, $x.','.($y-1));
}
?>
