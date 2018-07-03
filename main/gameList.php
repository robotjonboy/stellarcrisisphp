<?
function gameList($vars)
{
	$empire = getEmpire($vars['name']);

    $player_games = '';
    $open_games = '';
	$invite_games = '';
	$empty_games = '';
    
    ##################################
	# Listing of the player's games. #
	##################################

	ob_start();
	
	$fields = 	'series.*, 
				games.id AS game_id, 
				players.id AS player_id, 
				players.ended_turn';
	$tables = 	'series '.
				'INNER JOIN games ON series.id = games.series_id '.
				'INNER JOIN players ON games.id = players.game_id';
	$conditions = 'players.name = "'.$empire['name'].'" AND team >= 0';	
	$order = 'ORDER BY series.update_time, series.name, games.game_number ASC';

	$select = sc_mysql_query('SELECT '.$fields.
							' FROM '.$tables.
							' WHERE '.$conditions.' '.
							$order);
	while ($row = mysql_fetch_array($select))
		{
		$game = getGameByID($row['game_id']);

		$popup = 'onClick="window.open(\'sc.php?seriesParameters='.$row['id'].'\',\'seriesParameters'.$row['id'].'\',\'height=500,width=600,scrollbars=yes\')"';
		$series_description_link = '<a style="text-decoration: none; 
										color: red;" 
										href="javascript:void(0)" '.
										$popup.
									'>'.
									$row['name'].
									'</a>';
									($row['creator'] != 'admin' ? 
				'<div style="font-size: 7pt;">Created by '.$row['creator'].'</div>' : '');
	
		$player_listing = '<span class="playerListing">'.
						   '<a href="javascript:void(0)">'.
						    '[&nbsp;list<span>'.
						     playerList($row['game_id'], $empire).
						    '</span>&nbsp;]'.
						   '</a>'.
						  '</span>';

		$bridier_estimate = ($game['bridier'] >= 0 ? 
			'<div>'.bridierEstimate($row, $game, $empire).'</div>' : '');
		
		$select_messages = sc_mysql_query('SELECT COUNT(*) 
									FROM messages 
									WHERE player_id = '.$row['player_id'].' 
										AND flag = "0"');
		if ($unread_messages = mysql_result($select_messages, 0, 0))
			$messages = '<div class=green>You have '.
						$unread_messages.
						' unread message'.
							($unread_messages != 1 ? 's' : '').
						'.</div>';
		else
			$messages = '';

		$update_readiness = '<span class='.($row['ended_turn'] ? 
											'greenBold>' : 
											'redBold>not ').' ready</span>';

		if ($game['closed'])
			$players = 'Closed game, <br>'.$game['player_count'].' players left.';
		else
			{
			// If the game is still open, we need to hide the "Open game" button.
			// The $open_game_exists array tracks what series are concerned.
			if (!$game['password1'])
				$open_game_exists[$game['series_id']] = true;
			
			$players = $game['player_count'].' of '.$row['max_players'].' players';
			}
?>
	<tr>
		<th style="text-align: left; vertical-align: top; color: red;"><? echo ($row['id'] != $last_series_id ? $series_description_link : ''); ?></th>
		<th style="text-align: left; color: white; vertical-align: top;">Game <? echo $game['game_number']; ?></th>
		<td style="vertical-align: top;"><? echo $players.'&nbsp;'.$player_listing; ?></td>
		<td style="text-align: center; vertical-align: top;"><? echo $bridier_estimate.$game['update_count']; ?> updates</td>
		<td style="text-align: center; vertical-align: top;"><input type=submit name="login[<? echo $game['id']; ?>]" value="Login"></td>
		<td>
			<? echo nextUpdate($row, $game); ?>
			<div>You are <? echo $update_readiness; ?> for an update.</div>
			<? echo $messages; ?>
		</td>
	</tr>
<?
		$last_series_id = $row['id'];

		// This game will not be listed in open games.
		$excluded_games[$game['id']] = true;
		}

	$player_games = ob_get_contents();
	ob_end_clean();

	################################
	# Listing of invitation games. #
	################################

	ob_start();

	// Determine if the player is idle; we don't let idlers start new games.
	if (!$idle = playerIsIdle($vars['name']))
		{
		$last_series_id = 0;
		
		$tables = 'invitations INNER JOIN series ON invitations.series_id = series.id';

		$conditions = array();
		$conditions[] = 'empire = "'.$empire['name'].'"';
		$conditions[] = 'status = "None"';

		$select = sc_mysql_query('SELECT * FROM '.$tables.' WHERE '.implode(' AND ', $conditions).' ORDER BY invitations.id');
		while ($row = mysql_fetch_array($select))
			{
			if ($excluded_games[$row['game_id']]) continue;

			$series = $row;

			$game = getGameByID($row['game_id']);
			
			$players = $game['player_count'].' of '.$series['max_players'].' players';
			$player_listing = '&nbsp;<span class="playerListing">'.
							  '<a href="javascript:void(0)">[&nbsp;list<span>'.playerList($game['id'], $empire).'</span>&nbsp;]</a></span>';

			// If the game is still open, we need to hide the "Open game" button.
			// The $open_game_exists array tracks what series are concerned.
			if (!$game['password1'])
				$open_game_exists[$row['series_id']] = true;

			if ($row['series_id'] != $last_series_id)
				echo seriesDescription($series);
?>
	<tr>
		<td></td>
		<td colspan=6 class=yellow><? echo $game['created_by'].($series['max_players'] > 2 ? ' invites' : ' challenges'); ?> you to join:</td>
	</tr>
	<tr>
		<td></td>
		<th style="text-align: left; vertical-align: top; color: white;">Game <? echo $game['game_number']; ?></th>
		<td style="vertical-align: top;"><? echo $players.$player_listing; ?></td>
		<td style="text-align: center; vertical-align: top;">
			<? echo ($series['bridier_allowed'] ? bridierEstimate($series, $game, $empire) : $game['update_count'].' updates'); ?>
		</td>
		<td style="text-align: center; vertical-align: top;">
			<input type=submit name="accept[<? echo $game['id']; ?>]" value="Accept"><input type=submit name="decline[<? echo $game['id']; ?>]" value="Decline">
		</td>
		<td><? echo nextUpdate($series, $game); ?></td>
	</tr>
<?
			if ($row['message'])
				{
?>
	<tr valign=top>
		<td colspan=2></td>
		<th class=white>Message from <? echo $game['created_by']; ?>:</td>
		<td colspan=2 class=white><? echo stripslashes($row['message']); ?></td>
		<td class=center>
			<a class=smallText href="javascript:blabReply('<? echo $game['created_by']; ?>')">Send Message to <? echo $game['created_by']; ?></a>
		</td>
	</tr>
<?
				}
				
			$last_series_id = $series['id'];

			// This game will not be listed in open games.
			$excluded_games[$game['id']] = true;
			}
		}

	$invitations = ob_get_contents();
	ob_end_clean();

	##########################
	# Listing of open games. #
	##########################
	
	if (!$idle)
		{
		ob_start();
	
		$tables = 'series INNER JOIN games ON series.id = games.series_id INNER JOIN players ON games.id = players.game_id';

		$conditions = array();
		$conditions[] = 'games.player_count > 0';
		$conditions[] = 'games.closed = "0"';
		$conditions[] = 'password1 = ""';
		$conditions[] = 'players.name <> "'.$empire['name'].'"';

		$order = 'ORDER BY series.update_time, series.name, games.game_number ASC';

		$select = sc_mysql_query('SELECT DISTINCT games.* FROM '.$tables.' WHERE '.implode(' AND ', $conditions).' '.$order);
		while ($game = mysql_fetch_array($select))
			{
			if ($excluded_games[$game['id']]) continue;
			
			$series = getSeries($game['series_id']);
			$series_header = seriesDescription($series);
			
			// If the game is still open, we need to hide the "Open game" button.
			// The $open_game_exists array tracks what series are concerned.
			if (!$game['password1'])
				$open_game_exists[$game['series_id']] = true;
					
			// Find bridier estimate and don't include this game in player's list if score isn't high enough.
			if (($bridier_text = bridierEstimate($series, $game, $empire)) == 'Insufficient rank')
				continue;

			$players = $game['player_count'].' of '.$series['max_players'].' players';

			if ($game['bridier'] < 0)
				$player_listing = '&nbsp;<span class="playerListing">'.
								  '<a href="javascript:">[&nbsp;list<span>'.playerList($game['id'], $empire).'</span>&nbsp;]</a></span>';
			else
				$player_listing = '';

			echo $series_header;
?>
	<tr>
		<td></td>
		<th style="color: white; text-align: left; vertical-align: top;">Game <? echo $game['game_number']; ?></th>
		<td><? echo $players.$player_listing; ?></td>
		<td style="text-align: center;"><? echo ($series['bridier_allowed'] ? $bridier_text : $game['update_count'].' updates'); ?></td>
		<td style="text-align: center;"><input type=submit name="join[<? echo $game['id']; ?>]" value="Join"></td>
		<td><? echo nextUpdate($series, $game); ?></td>
	</tr>
<?
			}

		$open_games = ob_get_contents();
		ob_end_clean();
		}

	###########################
	# Listing of empty games. #
	###########################

	if (!$idle)
		{
		ob_start();
		
		$fields = 'games.id AS game_id, series.id AS series_id, games.*, series.*';
		$tables = 'games INNER JOIN series ON games.series_id = series.id';
		$conditions = 'series.custom = "0" AND games.player_count = 0';

		$select = sc_mysql_query('SELECT '.$fields.' FROM '.$tables.' WHERE '.$conditions.' ORDER BY series.update_time, series.name, game_number');
		while ($row = mysql_fetch_array($select))
			{				
			#$series = getSeries($game['series_id']);

			echo seriesDescription($row).'<tr><td colspan=6 style="text-align: right;">';

			if ($row['bridier_allowed'])
				echo 'Bridier:&nbsp;<select name="bridier['.$row['game_id'].']">'.
					 '<option selected value="-1">No Bridier'.
					 '<option value="0">Play anyone'.
					 '<option value="1">Minimum +1<option value="2">Minimum +2'.
					 '<option value="3">Minimum +3<option value="4">Minimum +4'.
					 '<option value="5">Minimum +5</select>&nbsp';

			if (!$open_game_exists[$row['series_id']])
				echo '<input type=submit name="create['.$row['game_id'].']" value="Open Game">&nbsp';
				
			echo '<input type=submit name="createp['.$row['game_id'].']" value="Password Game"></td></tr>';
			}
		}

	$empty_games = ob_get_contents();
	ob_end_clean();
	
	standardHeader('Game List', $empire);
?>
<div class=pageTitle><? echo $vars['name']; ?>: Game List</div>
<div>
<input type=hidden name="name" value="<? echo $vars['name']; ?>">
<input type=hidden name="pass" value="<? echo $vars['pass']; ?>">
<input type=hidden name="empireID" value="<? echo $empire['id']; ?>">
<input type=hidden name="section" value="main">
<input type=hidden name="page" value="gameList">
<?
	echo drawButtons($empire).
			serverTime().
			'<div style="margin-top: 10pt; font-size: 8pt; text-align: center;">
			<a href="./rss/open_games.php">
				<img src="./images/rss.gif" 
					style="vertical-align: middle;" 
					border=0>
			</a> 
			Open games
			&nbsp;&nbsp;'.
			'<a href="./rss/your_games.php?id='.
						md5($empire['id'].
						$empire['name'].
						$empire['join_date']).
						'">'.
				'<img src="./images/rss.gif" 
					style="vertical-align: middle;" 
					border=0>'.
			'</a> 
			Your Games
			&nbsp;&nbsp;'.
			'<a href="./rss/updates.php?id='.
						md5($empire['id'].
						$empire['name'].
						$empire['join_date']).
						'">'.
				'<img src="./images/rss.gif" 
					style="vertical-align: middle;" 
					border=0>'.
			'</a> 
			Your updates
			</div>'.
			onlinePlayers().
			empireMissive($empire);

	if (!strlen($player_games.$invitations.$open_games.$empty_games))
		{
		echo '<img class=spacerule src="images/spacerule.jpg" alt="spacerule.jpg">'.
			 '<div class=messageBold>Sorry, there are no games in progress at this time.</div>';
		}
	else
		{
		echo '<table width="100%" border=0 cellspacing=0 cellpadding=5>';
		
		if ($invitations)
		{
?>
	<tr>
		<td colspan=6 style="color: white; text-align: center;">
			<img class="spacerule"
				style="margin-bottom: 5pt;" 
				src="images/spacerule.jpg" 
				alt="spacerule.jpg">
			<div style="font-size: 13pt;">
			Invitations
			</span>
			<div style="font-size: 8pt;">
			Click on a series name for a full list of its parameters.
			</span>
			<img class="spaceruleThin" 
				src="images/spacerule.jpg" 
				alt="spacerule.jpg">
		</td>
	</tr>
<?
			echo $invitations;
		}
		if ($player_games)
		{
?>
	<tr>
		<td colspan=6 style="color: white; text-align: center;">
			<img class=spacerule style="margin-bottom: 5pt;" src="images/spacerule.jpg" alt="spacerule.jpg">
			<div style="font-size: 13pt;">Your Games</div>
			<div style="font-size: 8pt;">Click on a series name for a full list of its parameters.</div>
			<img class=spaceruleThin src="images/spacerule.jpg" alt="spacerule.jpg">
		</td>
	</tr>
<?
			echo $player_games;
		}
		if ($idle)
		{
?>
	<tr>
		<td colspan=6>
			<img class=spacerule src="images/spacerule.jpg" alt="spacerule.jpg">
			<div style="font-size: 16pt">You may not join or start games while idle in another.</div>
		</td>
	</tr>
<?
		}
		else
		{
			if ($open_games)
			{
?>
	<tr>
		<td colspan=6 style="color: white; text-align: center;">
			<img class=spacerule style="margin-bottom: 5pt;" src="images/spacerule.jpg" alt="spacerule.jpg">
			<div style="font-size: 13pt;">Open games</div>
			<div style="font-size: 8pt;">Click on a series name for a full list of its parameters.</div>
			<img class=spaceruleThin src="images/spacerule.jpg" alt="spacerule.jpg">
		</td>
	</tr>
<?
				echo $open_games;
			}
			if ($empty_games)
			{
?>
	<tr>
		<td colspan=6 style="color: white; text-align: center;">
			<img class=spacerule style="margin-bottom: 5pt;" src="images/spacerule.jpg" alt="spacerule.jpg">
			<div style="font-size: 13pt;">Games you can start</div>
			<div style="font-size: 9pt;">More can be found on the custom series screen.</div>
			<div style="font-size: 8pt;">Click on a series name for a full list of its parameters.</div>
			<img class=spaceruleThin src="images/spacerule.jpg" alt="spacerule.jpg">
		</td>
	</tr>
<?
				echo $empty_games;
			}
		}
		echo '</table>';
	}
	echo '</div>';
	footer();
}

#----------------------------------------------------------------------------------------------------------------------#

function passwordGameList($vars)
{
	global $server;

	$empire = getEmpire($vars['name']);

	$last_series_id = 0;

	// Determine if the player is idle; we don't let idlers start new games.
	if (!$idle = playerIsIdle($vars['name']))
		{
		ob_start();
	
		$tables = 'games LEFT JOIN players ON games.id = players.game_id INNER JOIN series ON games.series_id = series.id';
			
		$conditions = array();
		$conditions[] = 'games.closed = "0"';
		$conditions[] = 'games.player_count <> 0';
		$conditions[] = 'games.password1 <> ""';
		$conditions[] = 'players.name <> "'.$empire['name'].'"';

		$order = 'series.name, games.game_number';

		$select = sc_mysql_query('SELECT DISTINCT games.* FROM '.$tables.' WHERE '.implode(' AND ', $conditions).' ORDER BY '.$order);
		while ($game = mysql_fetch_array($select))
			{
			$series = getSeries($game['series_id']);
			
			$bridier_text = bridierEstimate($series, $game, $empire);
			
			if ($bridier_text == 'Insufficient rank') continue;
			
			if ($series['id'] != $last_series_id) echo seriesDescription($series);
?>					
	<tr>
		<td></td>
		<th style="text-align: left; color: white;">Game <? echo $game['game_number']; ?></th>
		<td>
			<? echo $game['player_count'].' of '.$series['max_players']; ?> players&nbsp;
			<span class="playerListing">
				<a href="javascript:void(0)">[&nbsp;list<span><? echo playerList($game['id'], $empire); ?></span>&nbsp;]</a>
			</span>
		</td>
		<td style="text-align: center;"><? echo ($series['bridier_allowed'] ? $bridier_text : $game['update_count'].' updates'); ?></td>
		<th>
			Password:&nbsp;<input type=password name="gamePassword[<? echo $game['id']; ?>]" size=10 maxlength=10>
			<input type=submit name="join[<? echo $game['id']; ?>]" value="Join">
		</th>
		<td><? echo nextUpdate($series, $game); ?></td>
	</tr>
<?
			$last_series_id = $series['id'];
			}

		$listing = ob_get_contents();
		ob_end_clean();
		}
	
	standardHeader('Game List', $empire);
?>
<div class=pageTitle><? echo $vars['name']; ?>: Game List</div>

<input type=hidden name=name value="<? echo $vars['name']; ?>">
<input type=hidden name=pass value="<? echo $vars['pass']; ?>">
<input type=hidden name="section" value="main">
<input type=hidden name="page" value="gameList">
<?
	echo drawButtons($empire).
			serverTime().
			'<div style="margin-top: 10pt; font-size: 8pt; text-align: center;">
			<a href="./rss/open_games.php">
				<img src="./images/rss.gif" 
					style="vertical-align: middle;" 
					border=0>
			</a> 
			Open games
			&nbsp;&nbsp;'.
			'<a href="./rss/updates.php?id='.
						md5($empire['id'].
						$empire['name'].
						$empire['join_date']).
						'">'.
				'<img src="./images/rss.gif" 
					style="vertical-align: middle;" 
					border=0>'.
			'</a> 
			Your updates
			</div>'.
			onlinePlayers().
			empireMissive($empire);

	if (!$listing)
		{
?>
<img class=spacerule src="images/spacerule.jpg" width="100%" alt="spacerule.jpg">
<div class=messageBold>There are no password games available at this time.</div>
<?
		}
	else
		{
		echo '<table width="100%" border=0 cellspacing=0 cellpadding=5>';
		
		if ($idle)
			{
?>
	<tr>
		<td colspan=6>
			<img class=spacerule src="images/spacerule.jpg" alt="spacerule.jpg">
			<div style="font-size: 16pt">You may not join or start games while idle in another.</div>
		</td>
	</tr>
<?
			}
		else
			{
?>
	<tr>
		<td colspan=6 style="color: white; font-size: 13pt; text-align: center;">
			<img style="margin-bottom: 5pt;" class=spacerule src="images/spacerule.jpg" alt="spacerule.jpg">
			Open games
			<div style="font-size: 8pt;">Click on a series name for a full list of its parameters.</div>
			<img class=spaceruleThin src="images/spacerule.jpg" alt="spacerule.jpg">
		</td>
	</tr>
<?
			echo $listing;
			}
			
		echo '</table>';
		}

	footer();
}

#----------------------------------------------------------------------------------------------------------------------#

function gameList_processing($vars)
{
	$empire = $vars['empire_data'];
	
	// Buttons on the game list screens are mini-arrays named after the action.
	// Their first element's key is the game ID we need to know about.
	foreach (array('login', 'accept', 'decline', 'join', 'create', 'createp') as $button)
		{
		if (isset($vars[$button]))
			{
			list($game_id) = array_keys($vars[$button]);

			$vars['game_data'] = $game = getGameByID($game_id);

			if (!$game)
				{
				sendEmpireMessage($empire, 'That game no longer exists.');
				return gameList($vars);
				}
			else
				{
				$vars['series_data'] = $series = getSeries($game['series_id']);
				$action = $button;
				}

			break;
			}
		}

	if ($action == 'decline')
		{		
		sc_mysql_query('UPDATE invitations SET status = "Declined" WHERE game_id = '.$game_id.' AND empire = "'.$empire['name'].'"');
		sendEmpireMessage($empire, 'Invitation declined.');
		return gameList($vars);
		}

	if (ereg('(create|createp)', $action) and $game['num_players'])
		{
		// This will happen with out-of-date game lists.
		sendEmpireMessage($empire, 'Sorry, that game was started by someone else.');
		return gameList($vars);
		}

	if (ereg('(join|accept)', $action) and $game['closed'])
		{
		sendEmpireMessage($empire, 'Sorry, that game has filled up.');
		return gameList($vars);
		}

	if (ereg('(create|createp|login|join|accept)', $action))
		{	
		$select = sc_mysql_query('SELECT * FROM players WHERE game_id = '.$game_id.' AND name = "'.$empire['name'].'" AND team >= 0');
		$vars['player_data'] = $player = mysql_fetch_array($select);
		
		// If the player is already in the game, we can skip everything else and just log in.
		if ($player)
			return loginGame($vars);

		if ($action == 'accept')
			{
			$select = sc_mysql_query('SELECT * FROM invitations WHERE game_id = '.$game['id'].' AND empire = "'.$empire['name'].'"');
			$invitation = mysql_fetch_array($select);
			
			sc_mysql_query('UPDATE invitations SET status = "Accepted" WHERE id = '.$invitation['id']);

			if ($series['team_game'])
				return joinGame($vars, $invitation['team']);
			else
				return joinGame($vars);
			}

		// Does the player have the required wins to get in?
		// We check this after accepting invitations (win restrictions don't apply to them).
		if ($empire['wins'] < $series['min_wins'] or ($empire['wins'] > $series['max_wins'] and $series['max_wins'] != -1))
			{
			sendEmpireMessage($empire, 'You have either too many or too few wins for that series.');
			return gameList($vars);
			}
		
		if (ereg('(create|createp|join)', $action))
			{
			if ($series['bridier_allowed'] and $action != 'join')
				{
				// Set up bridier according to what the user chose in the pop-up menu on the game list.
				$bridier = $vars['bridier'][$game['id']];

				if ($bridier >= 0)
					{
					$values = array();
					$values[] = 'game_id = '.$game['id'];
					$values[] = 'series_name = "'.$series['name'].'"';
					$values[] = 'game_number = '.$game['game_number'];
					$values[] = 'empire1 = "'.$empire['name'].'"';
		
					// For some reason, this creates duplicates sometimes. We'll ignore those errors for now.
					sc_mysql_query('INSERT IGNORE INTO bridier SET '.implode(',', $values));
					}
		
				sc_mysql_query('UPDATE games SET bridier = "'.$bridier.'" WHERE id = '.$game['id']);
				}
		
			// Create initial passwords for password game creation.
			if ($action == 'createp')
				{
				// Non-team games only use the password1 field.
				// Passworded team games need both fields to be set.
				$game['password1'] = rand();
				$game['password2'] = ($series['team_game'] ? rand() : '');

				$values = array();
				$values[] = 'password1 = "'.$game['password1'].'"';
				$values[] = 'password2 = "'.$game['password2'].'"';

				sc_mysql_query('UPDATE games SET '.implode(',', $values).' WHERE id = '.$game['id']);
				
				// We'll need the password when we log into the game. Add it to the other POST variables.
				$vars['gamePassword'][$game['id']] = $game['password1'];
				$vars['game_data'] = $game;
				}

			return loginGame($vars);
			}
		}

	sendEmpireMessage($empire, 'Invalid action.');
	return gameList($vars);
}

#----------------------------------------------------------------------------------------------------------------------#

function loginGame($vars)
{
	global $server;
	
	$series = $vars['series_data'];
	$game = $vars['game_data'];
	$player = $vars['player_data'];
	$empire = $vars['empire_data'];

	// Welcome the player back if he is returning to this game.
	if ($player)
		{
		sendGameMessage($player, 'Welcome back, '.$player['name'].'.');
		return infoScreen($vars);
		}

	// Process team game logins
	if ($series['team_game'])
	    return loginTeamGame($vars);
	
	if ($vars['gamePassword'][$game['id']] != $game['password1'])
		{
		sendEmpireMessage($empire, 'Incorrect password for <span class=red>'.$series['name'].' '.$game['game_number'].'</span>.');
        return passwordGameList($vars);
		}
	
	return joinGame($vars);
}

#----------------------------------------------------------------------------------------------------------------------#

function loginTeamGame($vars)
{
	$series = $vars['series_data'];
	$game = $vars['game_data'];
	$empire = $vars['empire_data'];

    // Check passworded games. If the player has the password for the game, ignore the wins limits: they were expected
    if ($game['password1'] != '' or $game['password2'] != '')
		{
			 if ($vars['gamePassword'][$game['id']] == $game['password1'])	return joinGame($vars, 1);
		else if ($vars['gamePassword'][$game['id']] == $game['password2'])	return joinGame($vars, 2);
		else
			{
			sendEmpireMessage($empire, 'Incorrect password for <span class=red>'.$series['name'].' '.$game['game_number'].'</span>.');
			return passwordGameList($vars);
			}
		}

    return joinGame($vars, 0);	
}

#----------------------------------------------------------------------------------------------------------------------#
# WARNING: There is substantially similar code in passwordGameList-- if you change it here, check it there
#
# Looks for a bridier entry	for the current series/game
# If not a Bridier game, returns a null string
# If the game is in progress, returns the estimated win/loss potential for the player
# If the player is the only one in the game, returns their select Bridier limit
# If the game is open, returns their potential gain or "Insufficient rank" if they
# can't give the opponent enough points to satisfy their Bridier condition

function bridierEstimate($series, $game, $empire)
{
	if ($series['bridier_allowed'])
		{
		// It is, so see if this game is for ranking
		if ($game['bridier'] >= 0)
			{
			// Game is for ranking, so estimate win/lose amounts
			$bridier_query = sc_mysql_query('SELECT * FROM bridier WHERE game_id = '.$game['id']);
			
			if ($bdata = mysql_fetch_array($bridier_query))
				{
				if ($empire['name'] == $bdata['empire1'])
					{
					// If the viewing player is in the 1st bridier slot, his opponent is in the second.
					// However, if there is only one player in the game, there is no opponent so show the setting.
					if ($game['player_count'] == 1)
						return 'Bridier game: '.($game['bridier'] > 0 ? 'minimum&nbsp;+'.$game['bridier'] : 'play anyone');
					else
						{
						list($win, $lose) = calculateBridier($empire['bridier_rank'], $empire['bridier_index'], $bdata['starting_rank2'], $bdata['starting_index2']);
						$win_delta = $win;
						list($win, $lose) = calculateBridier($bdata['starting_rank2'], $bdata['starting_index2'], $empire['bridier_rank'], $empire['bridier_index']);
						$lose_delta = $lose;

						return 'Bridier game: +'.$win_delta.' / -'.$lose_delta;
						}
					}
				else
					{
					// If the player is in the second slot or not in the game their opponent is in the first slot.
					if ($game['player_count'] == 1)
						{
						$opponent = getEmpire($bdata['empire1']);
							
						// Make sure the empire meets the minimum for entry.
						list($win, $lose) = calculateBridier($opponent['bridier_rank'], $opponent['bridier_index'], $empire['bridier_rank'], $empire['bridier_index']);
							
						if ($win < $game['bridier'])
							return 'Insufficient rank';
							
						// Now calculate the potential winnings for the player joining the game.
						list( $win, $lose ) = calculateBridier($empire['bridier_rank'], $empire['bridier_index'], $opponent['bridier_rank'], $opponent['bridier_index']);
						
						if ($win >= 5)
							return 'Bridier game: +5 or more potential';
						else
							return 'Bridier game: +'.$win.' potential';
						}
					else
						{
						list($win, $lose) = calculateBridier($empire['bridier_rank'], $empire['bridier_index'], $bdata['starting_rank1'], $bdata['starting_index1']);
						$win_delta = $win;							
						list($win, $lose) = calculateBridier($bdata['starting_rank1'], $bdata['starting_index1'], $empire['bridier_rank'], $empire['bridier_index']);
						$lose_delta = $lose;

						return 'Bridier game: +'.$win_delta.' / -'.$lose_delta;
						}
					}
				}
			else
				{
				// If we can't find a results record, it's an error so remove the bridier flag from the game.
				$game['bridier'] = -1;
				sc_mysql_query('UPDATE games SET bridier = -1 WHERE game_id = '.$game['id']);
				}
			}
		else
			return 'Not for Bridier ranking.';
		}

	return '';
}

#----------------------------------------------------------------------------------------------------------------------#
# Retrieves any pending messages for the empire (user). These are displayed on non-game screens.
#

function empireMissive($empire)
{
	$select = sc_mysql_query('SELECT sender, text FROM messages WHERE empire_id = '.$empire['id'].' AND flag = "0" ORDER BY id ASC');
	
	if (mysql_num_rows($select))
		{		
		$messages = array();
		while ($row = mysql_fetch_array($select))
			{
			$message = stripslashes(urldecode($row['text']));
			
			// If this is not a server message, a person sent it. Allow replying.
			if ($row['sender'] != '')
				$message .= '<a class=smallText href="javascript:blabReply(\''.$row['sender'].'\')">Reply to '.$row['sender'].'</a>';
			
			$messages[] = $message;
			}
			
		// We could just mark the messages as read and keep them, but we don't.
		sc_mysql_query('DELETE FROM messages WHERE empire_id = '.$empire['id']);
		
		return '<div style="text-align: center; margin-top: 10pt;">'.implode('<br>', $messages).'</div>';
		}
	else
		return '';
}

#----------------------------------------------------------------------------------------------------------------------#

function sendMessage_processing($vars)
{
	global $authenticated;
	
	if ($authenticated)
		{
		$header_args = array();
		$header_args['sender'] = $vars['name'];
		$header_args['type'] = 'instant';
		$header_args['time'] = time();
			
		$message = '<table width=50% border=0 cellspacing=0 cellpadding=0 style="margin-left: auto; margin-right: auto;">'.
				   '<tr><td>'.
				   messageHeader($header_args).
				   htmlentities($vars['message']).
				   '</td></tr></table>';

		$recipient = getEmpire($vars['recipient']);

		$values = array();
		$values[] = 'time = '.time();
		$values[] = 'sender = "'.$vars['name'].'"';
		$values[] = 'empire_id = "'.$recipient['id'].'"';
		$values[] = 'text = "'.addslashes($message).'"';
		$values[] = 'type = "instant"';
		
		sc_mysql_query('INSERT INTO messages SET '.implode(',', $values));
		}

	echo '<body onLoad="window.close()">';
}

#----------------------------------------------------------------------------------------------------------------------#

function seriesDescription($series)
{
	$update_time = ($series['update_time'] % 3600 == 0 ? ($series['update_time']/3600).' hours' : ($series['update_time']/60).' minutes');

	switch ($series['map_type'])
		{
		case 'standard':
			$map_text = 'Classic SC map. ';
			break;
		case 'prebuilt':
			$map_text = 'Random placement map (first player can be on the edge). ';
			break;
		case 'twisted':
			$map_text = 'Twisted map. ';
			break;
		case 'mirror':
			$map_text = 'Mirror map. ';
			break;
		case 'balanced':
			$map_text = 'Balanced map. ';
			break;
		default:
			$map_text = '';
			break;
		}


	if ($series['avg_min'] != $series['avg_fuel'] or $series['avg_min'] != $series['avg_ag'])
		$average_resources = $series['avg_min'].'/'.$series['avg_fuel'].'/'.$series['avg_ag'].' average minerals/fuel/agriculture';
	else
		$average_resources = $series['average_resources'].' average resources';

	$description = '<tr><th width=150 style="text-align: left; color: red;"><a style="text-decoration: none; color: red;" href="javascript:void(0)" '.
				   'onClick="window.open(\'sc.php?seriesParameters='.$series['id'].'\',\'seriesParameters'.$series['id'].'\','.
				   '\'height=500,width=600,scrollbars=yes\')">'.$series['name'].'</a>'.
				   ($series['creator'] != 'admin' ? '<div style="font-size: 7pt;">Created by '.$series['creator'].'</div>' : '').'</th>'.
				   '<td colspan=5 style="text-align: left;">'.($series['team_game'] ? '<span class=green><b>Team game.</b></span> ' : '').
				   ($series['diplomacy'] == 6 ? '<span class=blue><b>Shared HQ.</b></span> ' : '').
				   'Updates every '.$update_time.', '.($series['weekend_updates'] ? '' : 'no weekend updates, ').
				   $series['max_players'].' players, '.$series['systems_per_player'].' systems per player. '.
				   $map_text.( !$series['map_visible'] ? 'Map hidden until game starts. ' : '' ).
				   number_format($series['tech_multiple'], 2, '.', '').' tech, '.$average_resources.', ';
		
	if (!$series['min_wins'] and $series['max_wins'] == -1)
		$description .= 'anyone can join.';
    else if ($series['max_wins'] == -1)
    	$description .= $series['min_wins'].' or more wins needed to join.';
    else
    	$description .= 'from '.$series['min_wins'].' to '.$series['max_wins'].' wins needed to join.';
    	
    if ($series['build_cloakers_cloaked'])
    	$description .= ' <span style="color: #FF99FF;">Cloakers built cloaked.</span> ';
    	
    if ($series['cloakers_as_attacks'])
    	$description .= ' <span style="color: #FF99FF;">Cloakers appear as attacks.</span> ';
    	
   	// Diplomacy settings for this series.
    switch ($series['diplomacy'])
		{
		case 2: $description .= ' Truce, trade and alliances not allowed.';	break;
		case 3: $description .= ' Trade and alliances not allowed.';		break;
		case 4: $description .= ' No alliances allowed.';					break;
		}

	$description .= '</td></tr>';

	return $description;
}

#----------------------------------------------------------------------------------------------------------------------#
# Returns a table showing the players currently in a game. This is used on the game list, on a mouseOver().
#

function playerList($gameID, $empire)
{
	$player_list = '<div>';
	#$player_list = '<table style="text-align: left;" width="100%">';
	
	$fields = array();
	$fields[] = 'players.name';
	$fields[] = 'players.team';
	$fields[] = 'empires.icon';
	$fields[] = 'series.map_visible';
	$fields[] = 'games.closed';

	$from = 'players INNER JOIN empires ON players.name = empires.name '.
					'INNER JOIN series ON players.series_id = series.id '.
					'INNER JOIN games ON players.game_id = games.id';
	
	$order = 'IF (series.map_visible = "1" 
				OR games.closed = "1", players.team, 1), players.name ASC';
	
	$select = sc_mysql_query('SELECT '.implode(',', $fields).' FROM '.$from.' WHERE players.game_id = "'.$gameID.'" AND players.team >= 0 ORDER BY '.$order);

	if (mysql_num_rows($select) > 10)
		{
		$x = 0;
		$columns = ceil(mysql_num_rows($select)/5);

		while ($row = mysql_fetch_array($select))
			{
			#if ($x == 0) $player_list .= '<tr valign=top>';
			#else if (($x % 5) == 0) $player_list .= '<tr valign=top>';
			
			$player_list .= '<div style="width=50; 
									padding: 2pt; 
									font-size: 8pt; 
									text-align: left">'.
								$row['name'].
							'</div>';
			#$player_list .= '<td>'./*($empire['show_icons'] ? '<img src="images/aliens/'.$row['icon'].'" width=20 height=20>' : '').*/'</td>'.
			#				'<td>'.str_replace(' ', '&nbsp;', $row['name']).'</td>';

			#if ((++$x % 5) == 0) $player_list .= '</tr>';
			}
		}
	else
		{
		$current_team = 0;
		while ($row = mysql_fetch_array($select))
			{
			// Only show team affiliation if the map is visible or the game is closed to prevent team shopping.
			// If we do show it, only do when we switch teams while going through the records. This will have the effect
			// of having "headers" for each team on the list.
			if (($row['map_visible'] or $row['closed']) 
				and $current_team != $row['team'])
			{
				$current_team = $row['team'];
				$player_list .= '<div style="background-color: #003; 
									width=50; padding: 2pt; 
									font-size: 8pt; 
									text-align: left">'.
								$row['name'].
								'</div>';
				#$player_list .= '<tr><td colspan=2 style="background-color: #000033; text-align: center;">Team '.$current_team.'</td></tr>';
			}

			#$player_list .= '<tr><td>'./*($empire['show_icons'] ? '<img src="images/aliens/'.$row['icon'].'" width=20 height=20>' : '').*/'</td>'.
			#				'<td>'.$row['name'].'</td></tr>';
			
			$player_list .= '<div style="width=50; 
								padding: 2pt; 
								font-size: 8pt; 
								text-align: left">'.
								$row['name'].
								'</div>';
			}
		}

	#return $player_list.'</table>';
	return $player_list.'</div>';
}

#-----------------------------------------------------------------------------------------------------------------------------------------#
# Returns a string indicating when the next update for a game will occur.
#

function nextUpdate($series, $game)
{
	$weekend = preg_match('/Sat|Sun/i', date('D', time()));
	
    if ($game['player_count'] == 1 or ($series['team_game'] and $game['closed'] == 0))
		$next_update = 'Waiting for other players.';
	else if (!$game['weekend_updates'] and $weekend)
		$next_update = 'Game is on hold for the weekend.';
	else if (inHolidayBreak())
		$next_update = 'Game is paused for the holidays.';
	else if ($game['on_hold'] and $game['closed'] == 0)
		$next_update = 'Game is paused.';
	else if ($game['update_count'] == 0)
		$next_update = 'First update in '.secondsToString($game['last_update']+$game['update_time']-time()).'.';
	else
		$next_update = 'Next update in '.secondsToString($game['last_update']+$game['update_time']-time()).'.';
		
	return $next_update;
}

#-----------------------------------------------------------------------------------------------------------------------------------------#
# Returns a form pop-up menu listing the players currently logged in and/or in a game. Time-out is hard-coded at 30 minutes.
# Selecting one will bring up a window allowing the user to send a message to the player.
#

function onlinePlayers()
{
	global $server;
	
	$empires = '(SELECT name FROM empires WHERE UNIX_TIMESTAMP() - last_login < 30*60)';
	$players = '(SELECT DISTINCT name FROM players WHERE UNIX_TIMESTAMP() - last_access < 30*60)';
	
	$select = sc_mysql_query($empires.' UNION '.$players.' ORDER BY name');
	if ($count = mysql_num_rows($select))
	{
		$list = '<div style="text-align: center; margin-top: 10pt;">'.
				'<select name=onlinePlayers onChange="blab()">'.
				'<option value=1 selected>'.
				$count.' player'.($count != 1 ? 's' : '').' currently online...';
		while ($row = mysql_fetch_array($select))
		{
				$list .= '<option value="'.$row['name'].'">'.$row['name'];
		}
		return $list.'</select></div>';
	}
	else
		return '';
}

#-----------------------------------------------------------------------------------------------------------------------------------------#
# Returns the number of games in which a player is idling (i.e., true if he's idling, false otherwise).
#

function playerIsIdle($player_name)
{
	global $server;
	
	$tables = 'players INNER JOIN games ON players.game_id = games.id';

	$conditions = array();
	$conditions[] = 'players.name = "'.$player_name.'"';
	$conditions[] = '(games.update_count - players.last_update) > '.$server['updates_to_idle'];
	$conditions[] = 'team > 0';

	$select = sc_mysql_query('SELECT COUNT(*) FROM '.$tables.' WHERE '.implode(' AND ', $conditions));
	
	return mysql_result($select, 0, 0);
}
/* ---- edit history ---
2007-12-14  changed .rss to .php to make rss work on normal server
lines 477 484
*/
?>