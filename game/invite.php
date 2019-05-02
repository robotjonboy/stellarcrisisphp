<?php
function inviteScreen($vars)
{
	$empire = $vars['empire_data'];
	$series = $vars['series_data'];
	$game = $vars['game_data'];
	$player = $vars['player_data'];
	
	$team_choice = array('Either', 'Team 1', 'Team 2');
	
	if ($game['closed'])
		{
		sendGameMessage($player, 'Game closed. No more invitations allowed.');
		return infoScreen($vars);
		}
		
	gameHeader($vars, 'Invitations');
	
	$emps_invited = array();
	
	$current_invitations = sc_mysql_query('SELECT * FROM invitations WHERE game_id = '.((int)$game['id']));
	
	echo '<div style="text-align: center;"><table cellpadding=2 style="margin-left: auto; margin-right: auto;">';
	
	if (mysql_num_rows($current_invitations))
		echo '<tr><th>Empire</th>'.($series['team_game'] ? '<th>Team</th>' : '').'<th>Message</th><th colspan=2>Response</th></tr>';
		
	while ($invitation = mysql_fetch_array($current_invitations))
		{
		// Count unresponded invitations-- we keep them around so that the inviter can watch the status.
		if ($invitation['status'] == 'None')
			$emps_invited[$invitation['team']] += 1;

		echo '<tr>'.
			 '<td>'.$invitation['empire'].'</td>'.
			 ($series['team_game'] ? '<td class=center>'.$team_choice[$invitation['team']] : '').'</td>'.
			 '<td>'.stripslashes($invitation['message']).'</td>'.
			 '<td class=center>'.$invitation['status'].'</td>'.
			 '<td><input type=checkbox name="uninvited[]" value="'.$invitation['empire'].'" '.
			 ($invitation['status'] != 'None' ? 'checked>&nbsp;Delete' : '>&nbsp;Withdraw').' Invitation</td></tr>';
		}
		
	if ($series['team_game'])
		{
		// Get count of players currently in game.
		$select = sc_mysql_query('SELECT team, COUNT(*) as team_count FROM players WHERE game_id = '.
		                         ((int)$game['id']).' GROUP BY team');
		$team_count[ mysql_result($select, 0, 0) ] = mysql_result($select, 0, 1);
		$total_emps = $emps_invited[1] + $emps_invited[2];
		}
	else
		$total_emps = $emps_invited[0];
	
	if ($total_emps + $game['player_count'] < $series['max_players'])
		{
		if (mysql_num_rows($current_invitations))
			echo '<tr><td colspan=5><hr></td></tr>';

		echo '<tr><td class=center>'.onlineInvitablePlayers().' or <br><input type=text name=invite_name size=20 maxlength=20"></td>';

		if ($series['team_game'])
			{
			echo '<td><select name=invite_team>';
			$both_open = true;
	
			if (($emps_invited[1]+$team_count[1]) < $series['max_players']/2)
				echo '<option value="1">Team 1';
			else
				$both_open = false;
	
			if (($emps_invited[2]+$team_count[2]) < $series['max_players']/2)
				echo '<option value="2">Team 2';
			else
				$both_open = false;
	
			if ($both_open)
				echo '<option value="0" selected>Either'; 
	
			echo '</select></td>';
			}
	
		echo '<td><textarea name=messageText rows=4 cols=30></textarea></td><td></td>'.
		     '<td><input type=submit name=Add value=Add></td></tr>';
		}
	 echo '</table></center>';
	
	footer();
}

#
#-----------------------------------------------------------------------------------------------------------------------------------------#
#

function inviteScreen_processing(&$vars)
{
	$series = $vars['series_data'];
	$game = $vars['game_data'];
	$player = $vars['player_data'];
	
	// Look for invitations to withdraw...
	if (count($vars['uninvited']))
		foreach ($vars['uninvited'] as $empire)
			sc_mysql_query('DELETE FROM invitations WHERE game_id = '.((int)$game['id']).
			               ' AND empire = "'.mysql_real_escape_string($empire).'"');

	// ...and to add.
	if ($vars['Add'] == 'Add')
		{
		$vars['gameAction'] = 'Invite Players';
		
		$player_to_invite = '';
		
		if ($vars['invite_name'] != '')
			$player_to_invite = $vars['invite_name'];
		else if ($vars['onlinePlayers'] != '')
			$player_to_invite = urldecode($vars['onlinePlayers']);

		$select = sc_mysql_query('SELECT empire FROM invitations WHERE game_id = '.((int)$game['id']).
		                         ' AND empire = "'.mysql_real_escape_string($player_to_invite).'"');
		if (mysql_num_rows($select))
			return sendGameMessage($player, 'Empire '.$player_to_invite.' is already invited.');
		else if ($player_to_invite != '' and $empire = getEmpire($player_to_invite))
			{
			// Get the name from the empire record to get the capitalization right.
			$player_to_invite = $empire['name'];

			if ($invited_player = getPlayer($game['id'], $player_to_invite))
				return sendGameMessage($player, 'Empire '.$player_to_invite.' is already in the game.');
								
			$values = array();
			$values[] = 'series_id = '.((int)$series['id']);
			$values[] = 'game_number = '.((int)$game['game_number']);
			$values[] = 'game_id = '.((int)$game['id']);
			$values[] = 'empire ="'.mysql_real_escape_string($empire['name']).'"';
			
			if ($vars['messageText'])
				{
				// Sanitize.
				$text = htmlspecialchars($vars['messageText']);				
				$text = str_replace("\n", '<br>', $text);
				$values[] = 'message = "'.addslashes($text).'"';
				}
			
			if ($series['team_game'])
				$values[] = 'team = '.$vars['invite_team'];

			sc_mysql_query('INSERT INTO invitations SET '.implode(',', $values));
			
			// I think we should add a missive to the invited emp here, but I don't want to mess
			// with it until the new missive code is merged in
			return sendGameMessage($player, 'Empire '.$player_to_invite.' invited.');
			}
		else
			return sendGameMessage($player, 'Empire '.$player_to_invite.' not found.');
		}
}

#
#-----------------------------------------------------------------------------------------------------------------------------------------#
# Returns a form pop-up menu listing the players currently logged in and/or in a game.
# Time-out is hard-coded at 30 minutes.
#

function onlineInvitablePlayers()
{
	$list = '';
	
	$empires = '(SELECT name FROM empires WHERE UNIX_TIMESTAMP() - last_login < 30*60)';
	$players = '(SELECT DISTINCT name FROM players WHERE UNIX_TIMESTAMP() - last_access < 30*60)';
	
	$select = sc_mysql_query($empires.' UNION '.$players.' ORDER BY name');
	
	if ($count = mysql_num_rows($select))
		{
		$list = '<div class=center><select name=onlinePlayers>'.
				'<option value="" selected>'.$count.' player'.($count != 1 ? 's' : '').' currently online...'.
				'<option value="">';

		while ($row = mysql_fetch_array($select))
			$list .= '<option value="'.urlencode($row['name']).'">'.$row['name'];
		
		$list .= '</select></div>';
		}

	return $list;
}
?>