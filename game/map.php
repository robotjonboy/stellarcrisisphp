<?php
function mapScreen($vars)
{
	global $server;

	$series = $vars['series_data'];
	$game = $vars['game_data'];
	$player = $vars['player_data'];
	$empire = $vars['empire_data'];
	$zoomed_planet = $vars['zoomed_planet'];
	
	// Is this a mini-map zoom request?
	if ($zoomed_planet != '')
		{
		// Ok, we want a 10x10 chunk of the map, centered on the player's selection.
		list($x,$y) = explode(',', xlateToGalactic($zoomed_planet));

		$x_min = $x-5; $x_max = $x+5;
		$y_min = $y-5; $y_max = $y+5;
		}
	else
		{
		$y_min = $x_min = 1000000;
		$y_max = $x_max = 0; // If galactic coordinates are negative, could this be a problem?
		}

	// This array will be used to reference the existence of links between any two planets.
	$jump_presence = array();
	
	$explored = array();
	$systems = array();
	$icons = array();

	$fields = array();
	$fields[] = 'explored.coordinates';
	$fields[] = 'owner';
	$fields[] = 'annihilated';
	$fields[] = 'jumps';
	$fields[] = 'mineral';
	$fields[] = 'agriculture';
	$fields[] = 'fuel';
	$fields[] = 'population';
	$fields[] = 'name';
	
	$tables = 'explored INNER JOIN systems ON explored.game_id = systems.game_id AND explored.coordinates = systems.coordinates';

	$conditions = array();
	$conditions[] = 'explored.game_id = '.$game['id'];
	$conditions[] = 'explored.empire = "'.$player['name'].'"';
	
	if ($zoomed_planet != '')
		$conditions[] = 'explored.coordinates BETWEEN "'.$x_min.','.$y_min.'" AND "'.$x_max.','.$y_max.'"';

	$explored_query = 'SELECT '.implode(',', $fields).', "yes" AS explored  FROM '.$tables.' WHERE '.implode(' AND ', $conditions);
	
	$fields[0] = 'scouting_reports.coordinates';
	
	$scouted_query = 'SELECT '.implode(',', $fields).', "no" AS explored FROM scouting_reports WHERE scouting_reports.player_id = '.$player['id'];

	$select = sc_mysql_query($explored_query.' UNION '.$scouted_query);
	while ($row = mysql_fetch_array($select))
		{
		$systems[ $row['coordinates'] ]['coordinates'] 	= $row['coordinates'];
		$systems[ $row['coordinates'] ]['owner'] 		= $row['owner'];
		$systems[ $row['coordinates'] ]['annihilated'] 	= $row['annihilated'];
		$systems[ $row['coordinates'] ]['jumps'] 		= $row['jumps'];
		$systems[ $row['coordinates'] ]['mineral']		= $row['mineral'];
		$systems[ $row['coordinates'] ]['agriculture']	= $row['agriculture'];
		$systems[ $row['coordinates'] ]['fuel']			= $row['fuel'];
		$systems[ $row['coordinates'] ]['population']	= $row['population'];
		$systems[ $row['coordinates'] ]['name'] 		= $row['name'];
		$systems[ $row['coordinates'] ]['explored'] 	= $row['explored'];

    	// Don't do this if we're zooming in: the range was set from the clicked-on system.
    	if ($zoomed_planet == '')
    		{
			list($x,$y) = split(',', $row['coordinates']);

    		$x_min = min($x, $x_min);
    		$x_max = max($x, $x_max);
    		$y_min = min($y, $y_min);
    		$y_max = max($y, $y_max);
			}

		$jumps = ($row['jumps'] ? explode(' ', $row['jumps']) : array());
		
		foreach ($jumps as $jump)
			{
			$jump_presence[$jump][$row['coordinates']] = true;			
			$jump_presence[$row['coordinates']][$jump] = true;
			}
		}

	// Before we actually start building the map, we'll also build an array of friendly/enemy ship counts
	// for each planet. This used to be done with a seperate query for EVERY planet.
	
	$ship_counts = array();
	$population_adjustements = array();

	$fields = array();
	$fields[] = 'location';
	$fields[] = 'SUM(IF(owner = "'.$player['name'].'", 1, 0)) AS friendly';
	$fields[] = 'SUM(IF(owner <> "'.$player['name'].'" AND cloaked = "0"'.
				($series['visible_builds'] ? '' : ' AND orders <> "build"').', 1, 0)) AS enemy';
	$fields[] = 'SUM(IF(owner <> "'.$player['name'].'" AND type = "Colony" AND orders = "build", 1, 0)) AS population_adjustment';

	// Join with the explored table so we skip planets we don't see.
	$from = 'ships INNER JOIN explored on ships.game_id = explored.game_id AND ships.location = explored.coordinates';

	$conditions = array();
	$conditions[] = 'explored.empire = "'.$player['name'].'"';
	$conditions[] = 'ships.game_id = '.$game['id'];

	$select = sc_mysql_query('SELECT '.implode(', ', $fields).' FROM '.$from.' WHERE '.implode(' AND ', $conditions).' GROUP BY location');
	while ($row = mysql_fetch_array($select))
		{
		$ship_counts[$row['location']]['friendly'] = $row['friendly'];
		$ship_counts[$row['location']]['enemy'] = $row['enemy'];
		$population_adjustements[$row['location']] = $row['population_adjustment'];
		}
	
	// Actual building of the map begins here.
	$map = '<table border=0 cellpadding=0 cellspacing=2 style="font-size: 8pt; margin-left: auto; margin-right: auto;">'.
		   '<tr><td></td>';
	
	// Top row; jumps going north.
	for ($x = $x_min; $x <= $x_max; $x++)
		$map .= '<th>'.
				($jump_presence[$x.','.($y_max+1)][$x.','.$y_max] ? '<img src="images/vert.gif" height=10 width=1>' : '').
				'</th><td></td>';
		
	$map .= '</tr>';
	
	for ($y = $y_max; $y >= $y_min; $y--)
		{
    	$map .= '<tr><td>';
    	
    	// First column; jumps going west. After this, we only check for jumps going east.
		if ($jump_presence[($x_min-1).','.$y][$x_min.','.$y])
			$map .= '<img src="images/horz.gif" width=10 height=1>';
			
		$map .= '</td>';

    	for ($x = $x_min; $x <= $x_max; $x++)
    		{
    		$coordinates = $x.','.$y;

    		if (isset($systems[$coordinates]))
      			{
				$system = $systems[$coordinates];

				// Depending on the state and ownership of the system, draw a different icon for the planet.
				$icon = '<input type=image src=';
				
				if ($system['owner'] == '')
					$icon .= '"images/planet.gif"';
				else if ($system['owner'] == $name)
					$icon .= '"images/aliens/'.$empire['icon'].'"';
				else if ($system['owner'] != '')
					{
					if (!$icons[$system['owner']])
						{
						$other_empire = getEmpire($system['owner']);
						$icons[$system['owner']] = $other_empire['icon'];
						}

					$icon .= '"images/aliens/'.$icons[$system['owner']].'"';
					}
				else if ($system['annihilated'])
					$icon .= '"images/annihilated.gif" name="system:'.xlateToLocal($coordinates).'"';

				$icon .= ' name="system:'.xlateToLocal($coordinates).'" height=40 width=40>';

				if ($system['explored'] == 'yes')
					{
					if (isset($ship_counts[$system['coordinates']]['friendly']))
						$own_ship_count = $ship_counts[$system['coordinates']]['friendly'];
					else
						$own_ship_count = 0;

					if (isset($ship_counts[$system['coordinates']]['enemy']))
						$other_ship_count = $ship_counts[$system['coordinates']]['enemy'];
					else
						$other_ship_count = 0;

					if (isset($population_adjustements[$system['coordinates']]))
						$population_adjustement = $population_adjustements[$system['coordinates']];
					else
						$population_adjustement = 0;
					}
				else
					{
					$own_ship_count = '?';
					$other_ship_count = '?';
					$population_adjustement = 0;
					}

				// Cache the result of the query; we don't want to determine the same thing a thousand times over...
				if (!$colors_for_owner[$system['owner']])
					$colors_for_owner[$system['owner']] = systemNameColor($player, $system['owner']);

				if ($system['explored'] == 'no')
					$system_name_color = ' class=grey';
				else if ($colors_for_owner[$system['owner']] != '')
					$system_name_color = ' class='.$colors_for_owner[$system['owner']];
				else
					$system_name_color = '';

				$map .= '<td class=center style="vertical-align: top;"><table border=0 cellspacing=0 cellpadding=0 style="margin-left: auto; margin-right: auto; font-size: 8pt;">'.
					    '<tr><td style="text-align: center;" '.$system_name_color.'>'.$system['mineral'].'<br>'.$system['agriculture'].'<br>('.$own_ship_count.')'.'</td>'.
					    '<td class=center'.($empire['name'] == $system['owner'] ? ' style="border: 1pt solid white;"' : '').'>'.$icon.'</td>'.
					    '<td style="text-align: center;" '.$system_name_color.'>'.$system['fuel'].'<br>'.($system['population']+$population_adjustement).
						'<br>('.$other_ship_count.')'.'</td>'.
					    '</tr><tr><td style="text-align: center;" colspan=3'.$system_name_color.'>'.($system['annihilated'] ? 'Remains of ' : '').
					    $system['name'].($empire['show_coordinates'] ? ' ('.xlateToLocal($coordinates).')' : '').
					    '</td></tr></table><td>';

				if ($jump_presence[($x+1).','.$y][$coordinates])
					$map .= '<img src="images/horz.gif" width=10 height=1>';
				
				$map .= '</td>';
      			}
      		else
      			{
      			$map .= '<td></td><td>';

				if ($jump_presence[($x+1).','.$y][$coordinates])
					$map .= '<img src="images/horz.gif" width=10 height=1>';
				
				$map .= '</td>';
    			}
    		}
    	    	
   		$map .= '</tr><tr><td></td>';
	
		for ($x = $x_min; $x <= $x_max; $x++)
			$map .= '<th>'.($jump_presence[$x.','.($y-1)][$x.','.$y] ? '<img src="images/vert.gif" height=10 width=1>' : '').'</th><td></td>';
			
		$map .= '</tr>';
  		}
  		
  	$map .= '</table>';

	gameHeader($vars, 'Map');

	ratios($player);
?>
<div class=messageBold>Click on a system for an up close view.</div>
<div style="margin-top: 10pt;"><?php echo $map; ?></div>
<?php
	footer();
}

#----------------------------------------------------------------------------------------------------------------------#

function mapScreen_processing(&$vars)
{
	// Don't need coordinate translation here because systemScreen is set up to handle local coordinate input.
	foreach (array_keys($vars) as $key)
		{
		list($system, $coordinates) = explode(":", $key);

		if ($system == 'system')
			{
			$x = explode(',', $coordinates);
			$y = explode('_', $x[1]);
			
			$vars['zoomed_planet'] = $x[0].','.$y[0];
			
			return true;
			}
		}

	return false;
}

#----------------------------------------------------------------------------------------------------------------------#

function miniMapScreen($vars)
{
	$minimap_data = array();

	$series = $vars['series_data'];
	$game = $vars['game_data'];
	$player = $vars['player_data'];
	$empire = $vars['empire_data'];

	$y_min = $x_min = 1000000;
	$y_max = $x_max = 0;

	$from = 'explored INNER JOIN systems ON explored.game_id = systems.game_id AND explored.coordinates = systems.coordinates';
	$query = 'SELECT * FROM '.$from.' WHERE explored.game_id = '.$game['id'].' AND explored.empire = "'.$vars['name'].'"';
	$select = sc_mysql_query($query, __FILE__.'*'.__LINE__);

	while ($row = mysql_fetch_array($select))
		{
		$minimap_data[$row['coordinates']]['explored'] = true;
		$minimap_data[$row['coordinates']]['owner'] = $row['owner'];
		$minimap_data[$row['coordinates']]['annihilated'] = $row['annihilated'];

    	list($x, $y) = explode(',', $row['coordinates']);

    	$x_min = min($x, $x_min);
    	$x_max = max($x, $x_max);
    	$y_min = min($y, $y_min);
    	$y_max = max($y, $y_max);
		}

	// Add scouting report planets.
	$query = 'SELECT * FROM scouting_reports WHERE player_id = '.$player['id'];
	$select = sc_mysql_query($query, __FILE__.'*'.__LINE__);
	while ($row = mysql_fetch_array($select))
		{
		if ($minimap_data[$row['coordinates']]) continue;
		
		$minimap_data[$row['coordinates']]['explored'] = false;
		$minimap_data[$row['coordinates']]['owner'] = $row['owner'];
		$minimap_data[$row['coordinates']]['annihilated'] = $row['annihilated'];

		list($x, $y) = explode(',', $row['coordinates']);

    	$x_min = min($x, $x_min);
    	$x_max = max($x, $x_max);
    	$y_min = min($y, $y_min);
    	$y_max = max($y, $y_max);
		}

	// Get coordinate offset via xlateToGalacitc so that local coords can all be disable in one place.
	list($hwX, $hwY) = explode(',', xlateToGalactic('0,0'));

	$map .= '<tr><td></td>';

  	for ($x = $x_min; $x <= $x_max; $x++)
  		$map .= '<td>'.($x-$hwX).'</td>';

	$map .= '<td></td></tr>';

	// Actual building of the map.
	for ($y = $y_max; $y >= $y_min; $y--)
		{
		$map_chunk = '<tr><td>'.($y-$hwY).'</td>';

    	for ($x = $x_min; $x <= $x_max; $x++)
    		{
    		$coordinates = $x.','.$y;

			if (in_array($coordinates, array_keys($minimap_data)))
      			{
				$local_coordinates = xlateToLocal($coordinates);
				$system_owner = $minimap_data[$coordinates]['owner'];
				$annihilated = $minimap_data[$coordinates]['annihilated'];
				$explored = $minimap_data[$coordinates]['explored'];

      			// Depending on the state and ownership of the system, draw a different icon for the planet.
				// The system was annihilated...
				if ($annihilated) $image = 'images/annihilated.gif';
				else if ($system_owner == '') $image = 'images/planet.gif';
				else if ($system_owner == $name) $image = 'images/aliens/'.$empire['icon'].'';
				else
					{
					// Cache the result of the query; we don't want to determine the same thing a thousand times over...
					if (!$icon_cache[$system_owner])
						{
						$other_empire = getEmpire($system_owner);
						$icon_cache[$system_owner] = $other_empire['icon'];
						}

					$image = 'images/aliens/'.$icon_cache[$system_owner].'';
					}

				$icon = '<input type=image src="'.$image.'" width=20 height=20 name="system:'.$local_coordinates.'">';
				
				// Cache results, again.
				if (!$border_colors[$system_owner])
					$border_colors[$system_owner] = systemNameColor($player, $system_owner);

				if (!$explored) $border = ' style="border: 1pt solid #505050;"';
				else if ($border_colors[$system_owner]) $border = ' style="border: 1pt solid '.$border_colors[$system_owner].';"';
				else $border = '';

				$map_chunk .= '<td'.$border.'>'.$icon.'</td>';
      			}
      		else
      			$map_chunk .= '<td></td>';
			}

      	$map .= $map_chunk.'<td>'.($y-$hwY).'</td></tr>';
  		}

  	$map .= '<tr><td></td>';

  	for ($x = $x_min; $x <= $x_max; $x++)
  		$map .= '<td>'.($x-$hwX).'</td>';

	$map .= '<td></td></tr>';

	gameHeader($vars, 'Mini-Map');

	ratios($player);
?>
<div class=messageBold>Click on a system for an up close view.</div>
<div style="margin-top: 10pt;">
<table border=0 cellpadding=0 cellspacing=1 style="text-align: center; font-size: 8pt; margin-left: auto; margin-right: auto;">
<?php echo $map; ?>
</table>
</div>
<?php
	footer();
}

#----------------------------------------------------------------------------------------------------------------------#
# Returns a string to color the system name according to the diplomatic status the player has with its owner.
# If we do return one, a space is appended at the beginning, since wherever it's used, we need to seperate the color
# attribute from the rest of the HTML code if we have one.
#

function systemNameColor($player, $planet_owner)
{	
	if ($planet_owner == $player['name'])
		return 'white'; // It's the player's.
	else if ($planet_owner == '')
		return ''; // Nobody lives here.

	$diplomacy = getDiplomacyWithOpponent($player['game_id'], $player['name'], $planet_owner);
				
	switch ($diplomacy['status'])
		{
		case '': case 2:	return 'red'; // Players haven't met yet, or they're at war
		case 3:				return 'pink'; // Truce
		case 4:				return 'yellow'; // Trade
		case 5:				return 'green'; // Alliance
		case 6:				return 'blue'; // Shared HQ
		}
}
?>