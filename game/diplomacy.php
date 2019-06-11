<?php
function diplomacyScreen($vars)
{
	global $server,$mysqli;

	$empire = $vars['empire_data'];
	$series = $vars['series_data'];
	$game = $vars['game_data'];
	$player = $vars['player_data'];
	
	$allowed_offers = array();
	$missive_recipients = array();
	$diplomacy_list = array();
	$last_status = array();

	$offer_to_team = '';

	if ($series['team_game'])
		{
		$diplomacy_list[1] = $diplomacy_list[2] = '';
		$last_status[1] = $last_status[2] = '';
		}
	else
		{
		$diplomacy_list[0] = '';
		$last_status[0] = '';
		}
	
	// Get all the diplomatic statuses the player has with other empires.
	$conditions = array();
	$conditions[] = 'game_id = '.((int)$game['id']);
	$conditions[] = 'empire = "'.$mysqli->real_escape_string($empire['name']).'"';
	$query = sc_query('SELECT * FROM diplomacies WHERE '.implode(' AND ', $conditions).' ORDER BY status, opponent ASC');
	while ($diplomacy = $query->fetch_assoc())
		{
		// Check for offers to the other team. "=" is not allowed in an empire name, so this can't be confused.
		if ($diplomacy['opponent'] == '=Team1=' or $diplomacy['opponent'] == '=Team2=')
			{
			$offer_to_team = '<select name="diplomacy['.$diplomacy['id'].']">';
			
			for ($offer = 0; $offer <= 2; $offer++)
				$offer_to_team .= '<option'.($diplomacy['offer'] == $offer ? ' selected' : '').' value="'.$offer.'">'.diplomacyString($offer);
			
			$offer_to_team .= '</select>';
			}
		else
			{
			// Get the other empire's name and run a query to get his data.
			$opponent_empire = getEmpire($diplomacy['opponent']);
			$opponent_player = getPlayer($game['id'], $diplomacy['opponent']);
			$opponent_diplomacy = getDiplomacyWithOpponent($game['id'], $diplomacy['opponent'], $diplomacy['empire']);

			// Default allowed states.
				 if ($diplomacy['status'] == 2) $allowed_offers = array(0, 1, 2, 3);
			else if ($diplomacy['status'] == 3) $allowed_offers = array(2, 3, 4);
			else if ($diplomacy['status'] == 4) $allowed_offers = array(3, 4, 5);
			else if ($diplomacy['status'] == 5) $allowed_offers = array(4, 5, 6);
			else if ($diplomacy['status'] == 6) $allowed_offers = array(5, 6);

			$offer_string = '';

			foreach ($allowed_offers as $offer)
				{
				if ($offer == 0 and $series['can_surrender'] and $game['player_count'] == 2)
					{
					$offer_string .= '<option'.($diplomacy['offer'] == $offer ? ' selected' : '').' value="'.$offer.'">'.diplomacyString($offer);
					continue;
					}
				else if ($offer == 1 and $series['can_draw'] and $game['player_count'] == 2)
					{
					$offer_string .= '<option'.($diplomacy['offer'] == $offer ? ' selected' : '').' value="'.$offer.'">'.diplomacyString($offer);
					continue;
					}
				else if ($offer > 1 and $offer <= $series['diplomacy'] )
					if ($series['team_game'] != 1 or $offer < 6)
						$offer_string .= '<option'.($diplomacy['offer'] == $offer ? ' selected' : '').' value="'.$offer.'">'.diplomacyString($offer);
					else if ($player['team'] == $opponent_player['team'])
						$offer_string .= '<option'.($diplomacy['offer'] == $offer ? ' selected' : '').' value="'.$offer.'">'.diplomacyString($offer);
				}

			if ($offer_string == '')
				$offer_string = 'N/A';
			else
				{
				$offer_string = '<select name="diplomacy['.$diplomacy['id'].']">'.$offer_string.'</select>';
				$missive_recipients[] = $diplomacy['opponent'];
				}

			if ($diplomacy['status'] != $last_status[$opponent_player['team']])
				$diplomacy_list[$opponent_player['team']] .= '<tr><th colspan=8 style="padding: 5pt; margin-top: 10pt; border-top: 1pt dashed white; border-bottom: 1pt dashed white;">Status: '.
								   '<span class=white>'.diplomacyString($diplomacy['status']).'</span></th></tr>'.
								   '<tr><th>Empire</th><th>Alien</th><th>Economic</th><th>Military</th><th>They Offer</th>'.
								   '<th>You Offer</th><th>Status</th><th>Access</th></tr>';

			$idle_updates = $game['update_count']-$opponent_player['last_update'];

			$diplomacy_list[$opponent_player['team']] .= '<tr><td valign=top rowspan=3>'.$diplomacy['opponent'].'</td><td>'.
							   '<img src="images/aliens/'.$opponent_empire['icon'].'" height=40 width=40></td>'.
							   '<td class=center>'.$opponent_player['economic_power'].'</td><td class=center>'.$opponent_player['military_power'].'</td>'.
							   '<td class=center>'.diplomacyString($opponent_diplomacy['offer']).'</td><td class=center>'.$offer_string.'</td>'.
							   '<td class=center>'.diplomacyString($diplomacy['status']).'</td>'.
							   '<td class=center>'.secondsToString(time()-$opponent_player['last_access']).' ago</td></tr>'.
							   '<tr><th>Wins</th><th>Nukes</th><th>Nuked</th><th>Ruined</th><th>Max Econ</th><th>Max Mil</th>'.
							   '<th rowspan=2><div style="color: '.($opponent_player['ended_turn'] ? 'lime' : 'red').';">'.
							   ($opponent_player['ended_turn'] ? 'Ready' : 'Not ready').'</div>'.
							   ($idle_updates > $server['updates_to_idle'] ? '<div style="color: lime;">Idle for '.$idle_updates.' updates</div>' : '').
							   '</th></tr>'.
							   '<tr><td class=center>'.$opponent_empire['wins'].'</td>'.
							   '<td class=center>'.$opponent_empire['nukes'].'</td>'.
							   '<td class=center>'.$opponent_empire['nuked'].'</td>'.
							   '<td class=center>'.$opponent_empire['ruined'].'</td>'.
							   '<td class=center>'.$opponent_empire['max_economic_power'].'</td>'.
							   '<td class=center>'.$opponent_empire['max_military_power'].'</td></tr>';

			$last_status[$opponent_player['team']] = $diplomacy['status'];
			}
		}
	
	// Now get team diplomacy settings for team games
	if ($series['team_game']) $team_offer = getTeamDiplomacy($game);
	
	// No diplomacy in blood games.
	if ($series['diplomacy'] != 2)
		$recipient_list = (count($missive_recipients) > 0 ? '<option>' : '').implode('<option>', $missive_recipients);

	$recipient_list .= '<option>Broadcast';

	if ($series['team_game']) $recipient_list .= '<option>Team Radio';

	gameHeader($vars, 'Diplomacy');

	if ($server['multiemp_warning'])
		{
		$select = sc_query('SELECT name, ip FROM players WHERE game_id = '.$game['id'], __FILE__.'*'.__LINE__);
		while ($row = $select->fetch_assoc()) $ip_addresses[$row['ip']][] = $row['name'];

		foreach (array_keys($ip_addresses) as $key)
			if ( count($ip_addresses[$key]) > 1 )
				{
				$multi_empers[$key] = implode(" / ", $ip_addresses[$key]);
				$multi_empers_count += count($ip_addresses[$key]);
				}

		if ($multi_empers_count)
			echo '<div class=messageBold>'.$multi_empers_count.' players currently do not have unique IP addresses'.
				 '<br>('.implode(') - (', $multi_empers).')</div>'.
				 '<img class=spacerule src="images/spacerule.jpg" width="100%" height=10 alt="spacerule.jpg">';
		}
?>
<div>
<table border=0 cellpadding=3 style="margin-left: auto; margin-right: auto;">
	<tr>
		<th rowspan=4></th>
		<th>You</th>
		<th>Economic</th>
		<th>Military</th>
		<th colspan=3></th>
		<th rowspan=4><span class=<?php echo ($player['ended_turn'] ? 'green>Ready' : 'red>Not ready'); ?></span></th>
	</tr>
	<tr style="text-align: center;">
		<td><img src="images/aliens/<?php echo $empire['icon']; ?>" height=40 width=40></td>
		<td class=center><?php echo $player['economic_power']; ?></td>
		<td class=center><?php echo $player['military_power']; ?></td>
		<td colspan=3></td>
	</tr>
	<tr>
		<th>Wins</th>
		<th>Nukes</th>
		<th>Nuked</th>
		<th>Ruined</th>
		<th>Max Econ</th>
		<th>Max Mil</th>
	</tr>
	<tr>
		<td class=center><?php echo $empire['wins']; ?></td>
		<td class=center><?php echo $empire['nukes']; ?></td>
		<td class=center><?php echo $empire['nuked']; ?></td>
		<td class=center><?php echo $empire['ruined']; ?></td>
		<td class=center><?php echo $empire['max_economic_power']; ?></td>
		<td class=center><?php echo $empire['max_military_power']; ?></td>
	</tr>
<?php
	if ($series['team_game'])		
		{
		$other_team = ($player['team'] == 1 ? 2 : 1);
?>
	<tr style="background-color: #000066; text-align: center;">
		<td colspan=3><span style="color: white; font-size: 13pt;">Your Team</span></td>
		<td colspan=3>Team offers: <?php echo diplomacyString($team_offer[$other_team]); ?></td>
		<td colspan=2>You offer: <?php echo $offer_to_team; ?></td>
	</tr>
<?php
		echo $diplomacy_list[$player['team']];
?>
	<tr style="background-color: #660000; text-align: center;">
		<td colspan=3><span style="color: white; font-size: 13pt;">Opposing Team</span></td>
		<td colspan=3>They offer: <?php echo diplomacyString($team_offer[$player['team']]); ?></td>
		<td colspan=2></td>
	</tr>
<?php
		echo $diplomacy_list[$other_team];
		}
	else
		echo $diplomacy_list[0];
?>
</table>
</div>

<div style="margin-top: 10pt;">
<table style="margin-left: auto; margin-right: auto;">
	<tr>
		<th>Send message to:</th>
		<td rowspan=3><textarea name=missive rows=10 cols=50></textarea></td>
	</tr>
	<tr>
		<th><select multiple name="recipients[]" size=6><?php echo $recipient_list; ?></select></th>
	</tr>
	<tr>
		<th><input type=submit name="send_message" value="Send message"></th>
	</tr>
</table>
</div>
<?php
	footer();
}

#----------------------------------------------------------------------------------------------------------------------#

function diplomacyScreen_processing($vars)
{
	$series = $vars['series_data'];
	$game = $vars['game_data'];
	$player = $vars['player_data'];
	
	if (isset($vars['diplomacy']))
		foreach ($vars['diplomacy'] as $id => $offer)
			sc_query('UPDATE diplomacies SET offer = "'.$offer.'" WHERE id = '.$id);
	
	// To enforce alliance limits we drop any alliance offer to trade if we are offering it to too many empires.
	// Current allies aren't affected - those aren't offers per se.
	if ($game['diplomacy'] != 2 and is_numeric($game['max_allies']) and $game['max_allies'] >= 0)
		{
		$select = sc_query('SELECT COUNT(*) as c FROM diplomacies WHERE game_id = '.$game['id'].' AND empire = "'.$player['name'].'" AND offer = "5"');
		$line = $select->fetch_assoc();
		if ($line['c'] > $game['max_allies'])
			{
			sendPlayerMissive($player, $player['id'], '', 'game_message', 'Alliance limit reached. Offers have been dropped down to truce.');
			sc_query('UPDATE diplomacies SET offer = "4" WHERE game_id = '.$game['id'].' AND empire = "'.$player['name'].'" AND status <> "5"');
			}
		}
	
	if (isset($vars['send_message']))
		{
		$message = $vars['missive'];
		
		if (!$message)
			return sendPlayerMissive($player, $player['id'], '', 'game_message', 'Empty message. Nothing was sent.');
		else if (!count($vars['recipients']))
			return sendPlayerMissive($player, $player['id'], '', 'game_message', 'No recipients selected. Message not sent.');
		else
			{		
			$message = htmlentities($message);

			if (in_array('Broadcast', $vars['recipients']))
				{
				$select = sc_query('SELECT id FROM players WHERE game_id = '.$game['id']);
				while ($row = $select->fetch_assoc())
					sendPlayerMissive($player, $row['id'], '', 'broadcast', $message);
				}
			else if (in_array('Team Radio', $vars['recipients']))
				{
				$select = sc_query('SELECT id FROM players WHERE game_id = '.$game['id'].' AND team = "'.$player['team'].'"');
				while ($row = $select->fetch_assoc())
					sendPlayerMissive($player, $row['id'], '', 'team', $message);
				}
			else
				{
				$recipient_list = prettyList($vars['recipients']);
				
				foreach ($vars['recipients'] as $recipient)
					{
					$recipient_player = getPlayer($game['id'], $recipient);
					sendPlayerMissive($player, $recipient_player['id'], $recipient_list, 'private', $message);
					}
					
				// Keep sent messages.
				sendPlayerMissive($player, $player['id'], $recipient_list, 'private', $message);
				}
			}

		return true;
		}

	return false;
}

#----------------------------------------------------------------------------------------------------------------------#

function prettyList($dirty_list)
{
	if (count($dirty_list) >= 2)
		{
		$chunk_1 = implode(', ', array_slice($dirty_list, 0, -2));
		$chunk_2 = implode(' and ', array_slice($dirty_list, -2));

		return $pretty_list = $chunk_1.($chunk_1 ? ', ' : '').$chunk_2;
		}
	else
		return $pretty_list = $dirty_list[0];
}
?>