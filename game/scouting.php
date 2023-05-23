<?php
#-----------------------------------------------------------------------------------------------------------------------------------------#
# $planet is a coordinate that comes from the system screen in the sender's local space.
#
#

function buildScoutingReport($series, $game, $system, $recipient)
{
	global $server;
	
	// We skip this part if we are not playing in local space on this server.
	if (!$server['local_coordinates'])
	{
		$local_coordinates = $system['coordinates'];
	}
	else
	{
		// We need to determine the coordinates of this planet in the recipient's local space.
		// To do this we need to find his homeworld coordinates in galactic space and his preferred map origin.
		$sql =  'SELECT coordinates '.
				'FROM systems '.
				'WHERE game_id = '.$game['id'].' '.
				'AND homeworld = "'.$recipient['name'].'"';
		$select = sc_query($sql);
		$homeworld = sc_fetch_assoc($select);

		list($origin_x, $origin_y) = explode(',', $homeworld['coordinates']);
		list($offset_x, $offset_y) = explode(',', $recipient['map_origin']);

		$origin_x -= $offset_x;
		$origin_y -= $offset_y;

		list($x, $y) = explode(',', $system['coordinates']);
		$local_coordinates = ($x-$origin_x).','.($y-$origin_y);
	}

	$fields = array();
	$fields[] = 'owner';
	$fields[] = 'type';
	$fields[] = 'COUNT(type) AS ship_count';
	
	$conditions = array();
	$conditions[] = 'game_id = '.$game['id'];
	$conditions[] = 'location = "'.$system['coordinates'].'"';
	$conditions[] = 'cloaked = "0"';

	if (!$series['visible_builds'])
		$conditions[] = 'orders <> "build"';

	$sql =  'SELECT '.implode(',', $fields).' '.
			'FROM ships '.
			'WHERE '.implode(' AND ', $conditions).' '.
			'GROUP BY owner, type';
	$select = sc_query($sql);

	$ship_inventory = array();
	while ($ship = sc_fetch_assoc($select))
	{
		// Initialize the array for the owner if we have no ships for him yet.
		// This prevents an error with array_key_exists().
		if (!isset($ship_inventory[$ship['owner']]))
		{
			$ship_inventory[$ship['owner']] = array();
		}
		
		// Check for using the cloaker appear as attack rule; in scouting reports, they always appear as attacks.
		if ( $series['cloakers_as_attacks'] and ( $ship['type'] == 'Cloaker' ) )
		{
			// We rely on Attacks always being before Cloakers as they should with the sorted output of GROUP BY.
			if (array_key_exists('Attack', $ship_inventory[$ship['owner']]))
				$ship_inventory[$ship['owner']]['Attack'] += $ship['ship_count'];
			else
				$ship_inventory[$ship['owner']]['Attack'] = $ship['ship_count'];
		}
		else
		{
			$ship_inventory[$ship['owner']][$ship['type']] = $ship['ship_count'];
		}
	}
		
	// If we are not showing builds and there are building colony ships here, we need
	// to adjust the visible population.
	$population_adjustement = 0;
	if (!$series['visible_builds'])
	{
		$conditions = array();
		$conditions[] = 'game_id = '.$game['id'];
		$conditions[] = 'location = "'.$system['coordinates'].'"';
		$conditions[] = 'type = "Colony"';
		$conditions[] = 'orders = "build"';

		$sql =  'SELECT COUNT(*) as c '.
				'FROM ships '.
				'WHERE '.implode(' AND ', $conditions);
		$select = sc_query($sql , __FILE__.'*'.__LINE__);
		$result = $select->fetch_assoc();
		$population_adjustement = $result['c'];
	}

	if ($system['annihilated'])
		$icon = '<input type=image src="images/annihilated.gif">';
	else if ($system['owner'] == '')
		$icon = '<input type=image src="images/planet.gif">';
	else
	{
		$planet_owner = getEmpire($system['owner']);
		$icon = '<input type=image src="images/aliens/'.$planet_owner['icon'].'">';
	}

	// We use the $jumps array to determine where to draw the jump links, if there are any.
	$jumps = (!$system['jumps'] ? array() : explode(' ', $system['jumps']));
	list($x, $y) = explode(',', $system['coordinates']);
	
	$system_name = ($system['annihilated'] ? 'Remains of ' : '').$system['name'];
	
	if ($system['owner'])
		$system_name_color = ' class='.systemNameColor($recipient, $system['owner']);
	else
		$system_name_color = '';

	if ($system['annihilated'])
		$owner = '<br><span class=smallText>Annihilated</span>';
	else if ($system['owner'])
		$owner = '<br><span class=smallText>Owned by '.$system['owner'].'</span>';
	else
		$owner = '';
	
	$report = 	'<span'.$system_name_color.'>'.
				   $system_name.
				   ' ('.$local_coordinates.')'.
				'</span>'.
			    $owner.
			    '<table border=0 '.
						'cellpadding=0 '.
						'cellspacing=0 '.
						'style="font-size: 8pt; margin-left: auto; margin-right: auto; margin-top: 10pt;"'.
						'>'.
				  '<tr class=center>'.
					  '<td colspan=3>'.
						  (in_array($x.','.($y+1), $jumps) ? 
								'<img src="images/vert.gif">' :
								''
						  ).
					  '</td>'.
				  '</tr>'.
				  '<tr>'.
					  '<td>'.
						  (in_array(($x-1).','.$y, $jumps) ?
								'<img src="images/horz.gif">' :
								''
						  ).
					  '</td>'.
					  '<td>'.
						'<table border=0 '.
									'cellspacing=0 '.
									'cellpadding=5 '.
									'style="font-size: 8pt;"'.
						  '>'.
						  '<tr class=center>'.
							'<td'.$system_name_color.'>'.
								$system['mineral'].'<br>'.
								$system['agriculture'].'<br>'.
								'?'.
							'</td>'.
							'<td>'.
								$icon.
							'</td>'.
							'<td'.$system_name_color.'>'.
								$system['fuel'].'<br>'.
								($system['population']+$population_adjustement).'<br>?'.
							'</td>'.
						  '</tr>'.
						  '<tr class=center>'.
							'<td colspan=3'.$system_name_color.'>'.
								$system_name.
								' ('.$local_coordinates.')'.
							'</td>'.
						  '</tr>'.
						'</table>'.
					  '</td>'.
					  '<td valign=middle>'.(in_array(($x+1).','.$y, $jumps) ? 
												'<img src="images/horz.gif">' : 
												''
											).
					  '</td>'.
				  '</tr>'.
				  '<tr class=center>'.
					'<td colspan=3>'.
						(in_array($x.','.($y-1), $jumps) ? 
							'<img src="images/vert.gif">' : 
							''
						).
					'</td>'.
					'<td>'.
						'<table border=0 '.
								'cellspacing=0 '.
								'cellpadding=5 '.
								'style="font-size: 8pt;">'.
				'<tr class=center>'.
					'<td'.$system_name_color.'>'.
						$system['mineral'].'<br>'.
						$system['agriculture'].
						'<br>?</td><td>'.$icon.'</td>'.
				'<td'.$system_name_color.'>'.
						$system['fuel'].'<br>'.
						($system['population']+$population_adjustement).
						'<br>?</td></tr>'.
				'<tr class=center>'.
				'<td colspan=3'.$system_name_color.'>'.
				$system_name.' ('.$local_coordinates.')'.
				'</td></tr>'.
				'</table>'.
				'</td>'.
				'<td valign=middle>'.
				(in_array(($x+1).','.$y, $jumps) ? 
					'<img src="images/horz.gif">' :
					'').
				'</td>'.
				'</tr>'.
				'<tr class=center>'.
				'<td colspan=3>'.
					(in_array($x.','.($y-1), $jumps) ?
					'<img src="images/vert.gif">' :
					'').
				'</td>'.
				'</tr>'.
				'</table>';
		//end of $report
		
	if (count($ship_inventory))
	{
		$lines = array();

		foreach (array_keys($ship_inventory) as $owner)
		{
			$line = $owner.': ';

			foreach (array_keys($ship_inventory[$owner]) as $ship_type)
				$line .= 	'<span class=white>'.
								$ship_type.
							'</span>'.
							'('.$ship_inventory[$owner][$ship_type].') ';

			$lines[] = $line;
		}

		$ships = implode('<br>', $lines);
	}

	return array($report, $ships);
}

#-----------------------------------------------------------------------------------------------------------------------------------------#

function scoutingScreen($vars)
{
	global $server;

	$series = $vars['series_data'];
	$game = $vars['game_data'];
	$player = $vars['player_data'];

	$conditions = array();
	$conditions[] = 'game_id = '.$game['id'];
	$conditions[] = 'empire = "'.$player['name'].'"';
	$conditions[] = 'opponent NOT LIKE "=Team_="';

	$sql = 	'SELECT * '.
			'FROM diplomacies '.
			'WHERE '.implode(' AND ', $conditions).' '.
			'ORDER BY status, opponent ASC';
	$select = sc_query($sql);

	$missive_recipients = array();
	while ($diplomacy = sc_fetch_assoc($select))
		$missive_recipients[] = $diplomacy['opponent'];

	if (!count($vars['scouting_systems']))
		$report = 'No planets included in report.';
	else
	{
		if ($select->num_rows)
		{
			$report = '<div class=center>'.
						'You will send the following scouting report.';
			if ($server['local_coordinates'])
				$report .= ' Coordinates will be translated to the '.
							'recipient\'s coordinate space.';

			$recipient_list = '<option>'.implode('<option>', $missive_recipients);
		}
		else
		{
			$report = '<div class=center>'.
						'The following is the scouting report you selected, however '.
						'you haven\'t met anyone else yet so it cannot be sent.';
		}
		$report .= 	'</div>'; //complete dialog

		//start new section
		$report .= 	'<div style="margin-top: 10pt;">'.
					'<table cellpadding=5 '.
						   'border=0 '.
						   'style="margin-left: auto; margin-right: auto;">';

		$tmp_reports = array();
		//cjp
		$tmp_reports[] = '<tr>[[['.$cjp_check_all.'</tr>';
		
		foreach ($vars['scouting_systems'] as $coordinates)
		{
			$system = getSystem($game['id'], xlateToGalactic($coordinates));

			// This is a preview for the player, so we use him as the recipient here.
			// Otherwise, we'd be giving him away the local coordinate space of the recipient!
			list($data, $ships) = buildScoutingReport($series, $game, $system, $player);

			$tmp_reports[] = '<tr>'.
								'<td valign=top align=center rowspan=2>'.
								'<input type=checkbox checked '.
									'name="scouting_systems[]" '.
									'value="'.$coordinates.'">'.
								'</td>'.
							 '<td style="text-align: left;">'.$data.'</td>'.
							 '<td valign=top style="text-align: left;">'.
								'Your comments:<p>'.
							 '<textarea align=top '.
										'name="notes:'.
										$coordinates.
										'" rows=5 cols=40>'.
							 '</textarea>'.
							 '</td>'.
							 '</tr>'.
							 '<tr>'.
							 '<td colspan=2 '.
									'style="text-align: left;">'.
								$ships.
							 '</td>'.
							 '</tr>';
		}

		$report .= implode('<tr><td colspan=3><hr></td></tr>', $tmp_reports).'</table></div>';
	}

	gameHeader($vars, 'Scouting Report');

	echo $report;
	
	//reciprient list
	if ($select->num_rows)
		echo '<div class=center>'.
				'Send report to:<br><br>'.
				'<select multiple name=missive_to[]>'.
					$recipient_list.
				'</select>'.
				'<br><br>'.
				'<input type=submit '.
						'name=gameAction '.
						'value="Transmit">'.
				'</div>';
	footer();
}

#-----------------------------------------------------------------------------------------------------------------------------------------#

function scoutingScreen_processing($vars)
{
	$series = $vars['series_data'];
	$game = $vars['game_data'];
	$player = $vars['player_data'];

	if ($vars['gameAction'] != 'Transmit')
	{
		sendGameMessage($player, 'Scouting report cancelled.');
		return false;
	}
		
	if (!count($vars['scouting_systems']))	
		return sendGameMessage($player, 'No planets included in report. '.
										'Report cancelled.');
	if (!count($vars['missive_to']))		
		return sendGameMessage($player, 'No recipients selected. Report cancelled.');

	$date = date('D M j H:i:s T Y', time());

	foreach ($vars['missive_to'] as $recipient_name)
	{
		$recipient = getPlayer($game['id'], $recipient_name);
		$observations = array();
		foreach ($vars['scouting_systems'] as $observation)
		{
			$system = getSystem($game['id'], xlateToGalactic($observation));
			list($data, $ships) = buildScoutingReport($series, $game, $system,
														$recipient);
			$comment = sanitizeString($vars['notes:'.$observation]);
			$comment = str_replace("\n", '<br>', $comment);
			$observations[] = '<tr>'.
								'<td style="text-align: left;">'.
									$data.
								'</td>'.
								'<td valign=top style="text-align: left;">'.
									'<span class=messageHeader>'.
										$player['name'].'\'s comments:'.
									'</span>'.
									'<p>'.
									'<span class=messageTextPrivate>'.
										$comment.
									'</span>'.
								'</td>'.
								'</tr>'.
								'<tr>'.
									'<td colspan=2 style="text-align: left;">'.
										$ships.
									'</td>'.
								'</tr>';

			// Ok, if the recipient has already explored this planet, don't add it to the table. Useless clutter.
			// He'll still get the message version, though.
			$query = 'SELECT id '.
					 'FROM explored '.
					 'WHERE player_id = '.$recipient['id'].' '.
					 'AND coordinates = "'.$system['coordinates'].'"';
			$select = sc_query($query, __FILE__.'*'.__LINE__);
			if (!$select->num_rows)
			{
				// First, delete any existing report for this planet.
				$query = 'DELETE FROM scouting_reports '.
								'WHERE player_id = '.$recipient['id'].
								' AND coordinates = "'.$system['coordinates'].'"';
				sc_query($query, __FILE__.'*'.__LINE__);
				
				// AS - 2003.03.30
				// I encountered a bug where a scouting report had an empty coordinate entered. I don't how this
				// could have happenned since the coordinate is supposed to come from the systems screen. 
				//An interrupted form load?
				// Anyway, this caused an infinite loop in both the mini-map and map screens. 
				//Let's check for it until we know more.
				if (trim($system['coordinates']) == '') continue;

				$values = array();
				$values[] = 'player_id = '.$recipient['id'];
				$values[] = 'coordinates = "'.$system['coordinates'].'"';
				$values[] = 'jumps = "'.$system['jumps'].'"';
				$values[] = 'name = "'.$system['name'].' ['.$game['update_count'].']"';
				$values[] = 'mineral = "'.$system['mineral'].'"';
				$values[] = 'fuel = "'.$system['fuel'].'"';
				$values[] = 'agriculture = "'.$system['agriculture'].'"';
				$values[] = 'population = "'.$system['population'].'"';
				$values[] = 'annihilated = "'.$system['annihilated'].'"';
				
				if (strlen($system['owner'])) 
					$values[] = 'owner = "'.$system['owner'].'"';
				if (strlen($ships))				
					$values[] = 'ships = "'.addslashes($ships).'"';
				if (strlen($comment))			
					$values[] = 'comment = "'.addslashes($comment).'"';

				sc_query('INSERT INTO scouting_reports '.
								'SET '.implode(',', $values), 
								__FILE__.'*'.__LINE__);
			}
		}
		$message = '<table cellspacing=5>'.
					implode('<tr><td colspan=2><hr></td></tr>', 
							$observations).'</table>';
		sendPlayerMissive($player, $recipient['id'], 
							implode(', ', $vars['missive_to']), 'scout', $message);
	}

	return sendGameMessage($player, 'Scouting report sent to '.
							implode(', ', $vars['missive_to']));
}
?>
