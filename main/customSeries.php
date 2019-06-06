<?php
function customSeries($vars)
{
	global $server;

	$empire = $vars['empire_data'];

	standardHeader('Custom Series', $empire);
?>
<div>
<input type=hidden name=name value="<?php echo $vars['name']; ?>">
<input type=hidden name=pass value="<?php echo $vars['pass']; ?>">
<input type=hidden name="section" value="main">
<input type=hidden name="page" value="customSeries">

<div class=pageTitle>Custom Series</div>
<?php
	echo drawButtons($empire).'<div class=message style="margin-top: 10pt;">Local time and date: '.date('l, F j H:i:s T Y', time()).'</div>'.
		 onlinePlayers().empireMissive($empire);
?>
<img class=spacerule src="images/spacerule.jpg" width="100%" height=10 alt="spacerule.jpg">

<div style="text-align: left; font-size: 10pt; margin: 0pt;">
This page allows you to create a series with parameters of your choosing (you need <?php echo $server['custom_series_wins_chunk']; ?> or more wins to gain access to this feature). Once this is done, anyone can start a game from this screen. Open games will appear on the regular game lists, but the series themselves will only be listed here.
<p>
Once a series has been created, the only control its creator has over it is to kill it if there are no active games. If there is a need to kill a specific game, you should <a href="mailto:<?php echo $server['admin_email']; ?>">contact the administrator</a>. There is also a limit of how many one can create: <?php echo $server['custom_series_per_wins_chunk'].' series per '.$server['custom_series_wins_chunk']; ?> wins.
</div>
<?php
	if (canCreateCustomSeries($empire) > 0)
		echo '<div style="text-align: center; margin-top: 10pt;"><input type=submit name=action value="Create a custom series"></div>';

	// Collect data about open games.
	$conditions = array();
	$conditions[] = 'games.closed = "0"';
	$conditions[] = 'games.password1 = ""';
	$conditions[] = 'players.team >= 0';
	$conditions[] = 'games.player_count > 0';
		
	$from = 'series INNER JOIN games ON series.id = games.series_id INNER JOIN players ON games.id = players.game_id';
	
	$open_game_exists = array();
	$select = sc_query('SELECT series.halted, series.id as series_id, COUNT(games.id) AS games_in_progress FROM '.$from.' WHERE '.implode(' AND ', $conditions).' GROUP BY series_id');
	while ($row = mysql_fetch_array($select))
		{
		$open_game_exists[$row['series_id']] = $row['games_in_progress'];
		}
		
	$output = $last_owner = '';
	
	$fields = array();
	$fields[] = 'games.id AS game_id';
	$fields[] = 'IF(creator = "'.$empire['name'].'", 0, 1) AS flag';
	$fields[] = 'series.id AS series_id';
	$fields[] = 'games.*';
	$fields[] = 'series.*';
	
	$tables = 'games INNER JOIN series ON games.series_id = series.id';
	$conditions = 'series.custom = "1" AND games.player_count = 0';

	$select = sc_query('SELECT '.implode(',', $fields).' FROM '.$tables.' WHERE '.$conditions.' ORDER BY flag, creator, series.update_time, series.name, game_number');
		
	ob_start();
	
	while ($row = mysql_fetch_array($select))
		{
		if ($last_owner != $row['creator'])
			{
?>
	<tr>
		<td colspan=3 style="color: white; font-size: 13pt; text-align: center;">
			<img class=spacerule src="images/spacerule.jpg" width="100%" height=10 alt="spacerule.jpg">
			<?php echo ($row['creator'] == $vars['name'] ? 'Your' : $row['creator'].'\'s'); ?> series
			<img class=spacerule_thin src="images/spacerule.jpg" width="100%" height=3 alt="spacerule.jpg">
		</td>
	</tr>
<?php
			}

		echo seriesDescription($row).'</tr><tr><td></td><td style="text-align: right;">';
				
		if ($row['bridier_allowed'])
			{
			echo 'Bridier:&nbsp;<select name="bridier['.$row['game_id'].']">'.
					 '<option selected value="-1">No Bridier'.
					 '<option value="0">Play anyone'.
					 '<option value="1">Minimum +1<option value="2">Minimum +2'.
					 '<option value="3">Minimum +3<option value="4">Minimum +4'.
					 '<option value="5">Minimum +5</select>&nbsp';
			}
						
		if (!isset($open_game_exists[$row['id']]))
			echo '<input type=submit name="create['.$row['game_id'].']" value="Open Game">&nbsp;';
				
		echo '<input type=submit name="createp['.$row['game_id'].']" value="Password Game">';
			
		if ($vars['name'] == $row['creator'])
			echo '&nbsp;<input type=submit name="killCustomSeries:'.$row['id'].'" value="Kill Series">';
			
		echo '</td></tr>';
		
		$last_owner = $row['creator'];
		}
		
	$custom_series = ob_get_contents();
	ob_end_clean();

	if (!strlen($custom_series))
		{
?>
<img class=spacerule src="images/spacerule.jpg" alt="spacerule.jpg">
<div class=messageBold>Sorry, there are no custom series available.</div>
<?php
		}
	else
		{
		echo '<table width="100%" border=0 cellspacing=0 cellpadding=5>';
		
		if (playerIsIdle($vars['name']))
			{
?>
	<tr>
		<td colspan=6 style="text-align: center; font-size: 16pt;">
			<img class=spacerule src="images/spacerule.jpg" alt="spacerule.jpg">
			You may not join or start games while idle in another.
		</td>
	</tr>
<?php
			}
		else
			echo $custom_series;
			
		echo '</table>';
		}
		
	echo '</div>';

	footer();
}

#----------------------------------------------------------------------------------------------------------------------#

function customSeries_processing($vars)
{
	global $server;

	$empire = $vars['empire_data'];
	
	if ($vars['action'] == 'Create a custom series')
    	{
    	if (!canCreateCustomSeries($empire))
			{
			sendEmpireMessage($empire, 'You do not have enough wins to create a custom series.'); 
			return customSeries($vars);
			}
		else
	    	return createCustomSeries($vars);
		}
		
	// Does the owner want to kill one of his series?
	foreach ($vars as $key => $value)
		{
		list($action, $series_id) = explode(':', $key);

		if ($action == 'killCustomSeries')
			{
			// Yep. Delete the series if there are no active games.
			$select = sc_query('SELECT id FROM games WHERE series_id = '.$series_id.' AND player_count > 0');
			if ($select->num_rows)
				return customSeries($vars, 'This series has active games. Deletion aborted.');
			else
				{
				#echo 'DELETE FROM series WHERE id = '.$series_id.' AND creator = "'.$vars['name'].'"';
				sc_query('DELETE FROM series WHERE id = '.$series_id.' AND creator = "'.$vars['name'].'"');
				
				if (mysql_affected_rows())
					{
					#echo 'DELETE FROM games WHERE series_id = '.$series_id.' AND player_count = 0';
					sc_query('DELETE FROM games WHERE series_id = '.$series_id.' AND player_count = 0');
					}

				return customSeries($vars, 'Series deleted.');
				}
			break;
			}
		}

	// User probably wants to start a game then. Let gameList_processing() handle that.
	gameList_processing($vars);
}

#----------------------------------------------------------------------------------------------------------------------#

function createCustomSeries($vars)
{
	$empire = $vars['empire_data'];
	
	standardHeader('Create a Custom Series', $empire);
?>
<div class=pageTitle><?php echo $vars['name']; ?>: Create a Custom Series</div>
<div>
<input type=hidden name=name value="<?php echo $vars['name']; ?>">
<input type=hidden name=pass value="<?php echo $vars['pass']; ?>">
<input type=hidden name=section value="main">
<input type=hidden name=page value="createCustomSeries">
<?php
	drawButtons($empire);
	
	echo '<div class=message style="margin-top: 10pt;">Local time and date: '.date('l, F j H:i:s T Y', time()).'</div>'.onlinePlayers();
	
	if ($empire_missive = empireMissive($empire)) echo $empire_missive;
	if ($message) echo '<div style="text-align: center; margin-top: 10pt;">'.$message.'</div>';
?>
<img class=spacerule src="images/spacerule.jpg" width="100%" height=10 alt="spacerule.jpg">
<div>
<table style="text-align: left; margin-left: auto; margin-right: auto;">
	<tr>
		<th style="text-align: right;">Series Name:</th>
		<td><input type=text size=40 maxlength=40 name="series_name" value="<?php echo $vars['series_name']; ?>"></td>
	</tr>
	<tr>
		<th style="text-align: right;">Update Time:</th>
		<td>
			<input type=text size=5 maxlength=5 name="update_time" value="<?php echo $vars['update_time']; ?>">
			<select name="update_time_unit">
				<option<?php echo ($vars['update_time_unit'] == 'Minutes' ? ' selected' : ''); ?>>Minutes
				<option<?php echo ($vars['update_time_unit'] == 'Hours' ? ' selected' : ''); ?>>Hours
				<option<?php echo ($vars['update_time_unit'] == 'Days' ? ' selected' : ''); ?>>Days
			</select>
			<input type=checkbox name="weekend_updates" checked> Weekend Updates
		</td>
	</tr>
	<tr>
		<th style="text-align: right;">Diplomatic states allowed:</th>
		<td>
			<select name="diplomacy">
				<option value=5<?php echo ($vars['diplomacy'] == 5 ? ' selected' : ''); ?>>Up to alliance
				<option value=4<?php echo ($vars['diplomacy'] == 4 ? ' selected' : ''); ?>>Up to trade
				<option value=3<?php echo ($vars['diplomacy'] == 3 ? ' selected' : ''); ?>>Up to truce
				<option value=2<?php echo ($vars['diplomacy'] == 2 ? ' selected' : ''); ?>>War
				<option value=6<?php echo ($vars['diplomacy'] == 6 ? ' selected' : ''); ?>>All (Shared HQ)
			</select>
		</td>
	</tr>
	<tr>
		<th style="text-align: right;">Maximum players:</th>
		<td><input type=text size=3 maxlength=3 name="max_players" value="<?php echo $vars['max_players']; ?>"></td>
	</tr>
	<tr>
		<th style="text-align: right;">Team game:</th>
		<td>
			<select name="team_game">
				<option value=0<?php echo ($vars['team_game'] == 0 ? ' selected' : ''); ?>>No
				<option value=1<?php echo ($vars['team_game'] == 1 ? ' selected' : ''); ?>>Yes
			</select>
		</td>
	</tr>
	<tr>
		<th style="text-align: right;">Systems per player:</th>
		<td><input type=text size=3 maxlength=3 name="systems_per_player" value="<?php echo $vars['systems_per_player']; ?>"></td>
	</tr>
	<tr>
		<th style="text-align: right;">Map type:</th>
		<td>
			<select name="map_type">
				<option value=1<?php echo ($vars['map_type'] == 1 ? ' selected' : ''); ?>>Classic
				<option value=2<?php echo ($vars['map_type'] == 2 ? ' selected' : ''); ?>>Pre-built (random placement)
				<option value=3<?php echo ($vars['map_type'] == 3 ? ' selected' : ''); ?>>Twisted (2-player or team game only)
				<option value=4<?php echo ($vars['map_type'] == 4 ? ' selected' : ''); ?>>Mirror (2-player game only)
				<option value=5<?php echo ($vars['map_type'] == 5 ? ' selected' : ''); ?>>Balanced (2-player game only)
			</select>
		</td>
	</tr>
	<tr>
		<th style="text-align: right;">Map visible before game start:</th>
		<td>
			<select name="map_visible">
				<option value=0<?php echo ($vars['map_visible'] == 0 ? ' selected' : ''); ?>>No
				<option value=1<?php echo ($vars['map_visible'] == 1 ? ' selected' : ''); ?>>Yes
			</select>
		</td>
	</tr>
	<tr>
		<th style="text-align: right;">Tech multiple:</th>
		<td><input type=text size=5 maxlength=5 name="tech_multiple" value="<?php echo $vars['tech_multiple']; ?>"></td>
	</tr>
	<tr>
		<th style="text-align: right;">Average minerals:</th>
		<td><input type=text size=5 maxlength=4 name="avg_min" value="30" value="<?php echo $vars['avg_min']; ?>"></td>
	</tr>
	<tr>
		<th style="text-align: right;">Average fuel:</th>
		<td><input type=text size=5 maxlength=4 name="avg_fuel" value="30" value="<?php echo $vars['avg_fuel']; ?>"></td>
	</tr>
	<tr>
		<th style="text-align: right;">Average agriculture:</th>
		<td><input type=text size=5 maxlength=4 name="avg_ag" value="30" value="<?php echo $vars['avg_ag']; ?>"></td>
	</tr>
	<tr>
		<th style="text-align: right;">Minimum wins:</th>
		<td><input type=text size=5 name="min_wins" value="0" value="<?php echo $vars['min_wins']; ?>"></td>
	</tr>
	<tr>
		<th style="text-align: right;">Maximum wins:</th>
		<td>
			<input type=text size=5 maxlength=7 name="max_wins" value="<?php echo $vars['max_wins']; ?>">
			<input type=checkbox name="no_max_wins" checked>No maximum
		</td>
	</tr>
	<tr>
		<th style="text-align: right;">Can draw (2-player games only):</th>
		<td>
			<select name="can_draw">
				<option value=1<?php echo ($vars['can_draw'] == 1 ? ' selected' : ''); ?>>Yes
				<option value=0<?php echo ($vars['can_draw'] == 0 ? ' selected' : ''); ?>>No
			</select>
		</td>
	</tr>
	<tr>
		<th style="text-align: right;">Can surrender (2-player games only):</th>
		<td>
			<select name="can_surrender">
				<option value=1<?php echo ($vars['can_surrender'] == 0 ? ' selected' : ''); ?>>Yes
				<option value=0<?php echo ($vars['can_surrender'] == 0 ? ' selected' : ''); ?>>No
			</select>
		</td>
	</tr>
	<tr>
		<th style="text-align: right;">Visible builds:</th>
		<td>
			<select name="visible_builds">
				<option value=1<?php echo ($vars['visible_builds'] == 0 ? ' selected' : ''); ?>>Yes
				<option value=0<?php echo ($vars['visible_builds'] == 0 ? ' selected' : ''); ?>>No
			</select>
		</td>
	</tr>
	<tr>
		<th align=right>Cloakers built cloaked:</th>
		<td align=left>
			<select name="build_cloakers_cloaked">
				<option value=1<?php echo ($series['build_cloakers_cloaked'] == 1 ? ' selected' : ''); ?>>Yes
				<option value=0<?php echo ($series['build_cloakers_cloaked'] == 0 ? ' selected' : ''); ?>>No
			</select>
		</td>
	</tr>
	<tr>
		<th align=right>Cloakers appear as attacks:</th>
		<td align=left>
			<select name="cloakers_as_attacks">
				<option value=1<?php echo ($series['cloakers_as_attacks'] == 1 ? ' selected' : ''); ?>>Yes
				<option value=0<?php echo ($series['cloakers_as_attacks'] == 0 ? ' selected' : ''); ?>>No
			</select>
		</td>
	</tr>
	<tr>
		<th style="text-align: right;">Bridier allowed:</th>
		<td>
			<select name="bridier_allowed">
				<option value=1<?php echo ($vars['bridier_allowed'] == 0 ? ' selected' : ''); ?>>Yes
				<option value=0<?php echo ($vars['bridier_allowed'] == 0 ? ' selected' : ''); ?>>No
			</select>
		</td>
	</tr>
</table>
<br>
<input type=submit name="confirmCreateCustomSeries" value="Create">
<input type=submit name="confirmCreateCustomSeries" value="Cancel">
</p>
<?php
	footer();
}

#
#-----------------------------------------------------------------------------------------------------------------------------------------#
#

function createCustomSeries_processing($vars)
{
	global $server;

	$map_types = array(1 => 'standard', 2 => 'prebuilt', 3 => 'twisted', 4 => 'mirror', 5 => 'balanced');
		
	$empire = $vars['empire_data'];
		
	if ($vars['confirmCreateCustomSeries'] == 'Cancel')
		{
		sendEmpireMessage($empire, 'Series creation cancelled.');
		return customSeries($vars);
		}
	
	$messages = array();
	$errors = array();
		
	if (getSeriesByName($vars['series_name']))												$errors[] = 'That series name is already in use.';
	if ($vars['series_name'] == '' or ereg('=', $vars['series_name']))						$errors[] = 'Invalid series name.';
	if (badNumericValue($vars['update_time'], 1, -1))										$errors[] = 'Invalid update time.';
	if (badNumericValue($vars['max_players'], 2, 50))										$errors[] = 'Invalid maximum players (2 to 50 allowed).';
	if (badNumericValue($vars['systems_per_player'], 3, 50))								$errors[] = 'Invalid systems per player (3 to 50 allowed).';
	if (badNumericValue($vars['tech_multiple'], 0, 50))										$errors[] = 'Invalid tech multiple (0 to 50 allowed).';
	if (badNumericValue($vars['avg_min'], 0, 500))											$errors[] = 'Invalid average minerals (2 to 500 allowed).';
	if (badNumericValue($vars['avg_fuel'], 0, 500))											$errors[] = 'Invalid average fuel (2 to 500 allowed).';
	if (badNumericValue($vars['avg_ag'], 0, 500))											$errors[] = 'Invalid average agriculture (2 to 500 allowed).';
	if (badNumericValue($vars['min_wins'], 0, -1))											$errors[] = 'Invalid minimum wins.';
	if ($vars['no_max_wins'] == '' and badNumericValue($vars['max_wins'], 0, -1))			$errors[] = 'Invalid maximum wins.';
	if ($vars['no_max_wins'] == '' and $vars['max_wins'] < $vars['min_wins'])				$errors[] = 'Maximum wins was less than minimum wins.';
	if ($vars['bridier_allowed'] and $vars['max_players'] != 2)								$errors[] = 'Bridier is for 2 player games only.';
	if ($vars['map_type'] == 2 and $vars['max_players']*$vars['systems_per_player'] > 250)	$errors[] = 'Pre-built map not allowed for more than 250 total systems';

	if ($vars['team_game'])
		{
		if ($vars['max_players'] % 2)	$errors[] = 'Team games must have an even number of players.';
		if ($vars['diplomacy'] < 5)		$errors[] = 'Team games must allow alliance.';
		
		if ($vars['map_type'] != 3)
			{
			$vars['map_type'] = 3;
			$messages[] = 'Map type set to twisted for team game.';
			}
		}
	else if ($vars['map_type'] == 3 and $vars['max_players'] > 2)
		$errors[] = 'Twisted map type not allowed in non-team games with more than two players.';
	else if ($vars['map_type'] == 4 and $vars['max_players'] > 2)
		$errors[] = 'Mirror map type not allowed in non-team games with more than two players.';
	else if ($vars['map_type'] == 5 and $vars['max_players'] > 2)
		$errors[] = 'Balanced map not allowed in games with more than two players.';

	if ($errors)
		{
		sendEmpireMessage($empire, implode('<br>', $errors));
		return createCustomSeries($vars);
		}

	switch ($vars['update_time_unit'])
  		{
   		case 'Minutes':	$vars['update_time'] *= 60;		break;
   		case 'Hours':	$vars['update_time'] *= 3600;	break;
   		case 'Days':	$vars['update_time'] *= 86400;	break;
   		default:		$vars['update_time'] *= 60; // Assume minutes if nothing (!) is specified.
  		}

	$vars['weekend_updates'] = ($vars['weekend_updates'] != '' ? 1 : 0);
	$vars['max_wins'] = ($vars['no_max_wins'] != '' ? -1 : $vars['max_wins']);

	$values = array();
	$values[] = 'name = "'.$vars['series_name'].'"';
	$values[] = 'update_time = "'.$vars['update_time'].'"';
	$values[] = 'weekend_updates = "'.$vars['weekend_updates'].'"';
	$values[] = 'diplomacy = "'.$vars['diplomacy'].'"';
	$values[] = 'max_players = "'.$vars['max_players'].'"';
	$values[] = 'team_game = "'.$vars['team_game'].'"';
	$values[] = 'map_type = "'.$map_types[$vars['map_type']].'"';
	$values[] = 'map_visible = "'.$vars['map_visible'].'"';
	$values[] = 'systems_per_player = "'.$vars['systems_per_player'].'"';
	$values[] = 'tech_multiple = "'.$vars['tech_multiple'].'"';
	$values[] = 'min_wins = "'.$vars['min_wins'].'"';
	$values[] = 'max_wins = "'.$vars['max_wins'].'"';
	$values[] = 'can_draw = "'.$vars['can_draw'].'"';
	$values[] = 'can_surrender = "'.$vars['can_surrender'].'"';
	$values[] = 'visible_builds = "'.$vars['visible_builds'].'"';
	$values[] = 'cloakers_as_attacks = "'.$vars['cloakers_as_attacks'].'"';
	$values[] = 'build_cloakers_cloaked = "'.$vars['build_cloakers_cloaked'].'"';
	$values[] = 'average_resources = "'.$vars['avg_min'].'"';
	$values[] = 'avg_min = "'.$vars['avg_min'].'"';
	$values[] = 'avg_fuel = "'.$vars['avg_fuel'].'"';
	$values[] = 'avg_ag = "'.$vars['avg_ag'].'"';
	$values[] = 'bridier_allowed = "'.$vars['bridier_allowed'].'"';
	$values[] = 'custom = "1"';
	$values[] = 'creator = "'.$empire['name'].'"';

	sc_query('INSERT INTO series SET '.implode(',', $values));

	spawnGame($vars['series_name']);

	$messages[] = 'Series <span class=red>'.stripslashes($vars['series_name']).'</span> successfully started.';
	
	sendEmpireMessage($empire, implode('<br>', $messages));

	customSeries($vars);
}

#----------------------------------------------------------------------------------------------------------------------#

function customSeriesDescription($vars, $series)
{
	$update_time = ($series['update_time'] % 3600 == 0 ? ($series['update_time']/3600).' hours' : ($series['update_time']/60).' minutes');

	switch ($series['map_type'])
		{
		case 'standard':	$map_type = 'Classic SC map. ';											break;
		case 'prebuilt':	$map_type = 'Random placement map (first player can be on the edge). ';	break;
		case 'twisted':		$map_type = 'Twisted map. ';											break;
		case 'mirror':		$map_type = 'Mirror map. ';												break;
		case 'balanced':	$map_type = 'Balanced map. ';											break;
		default:			$map_type = 'Unknown map type.';										break;
		}

	if ($series['avg_min'] != $series['avg_fuel'] or $series['avg_min'] != $series['avg_ag'])
		$average_resources = $series['avg_min'].'/'.$series['avg_fuel'].'/'.$series['avg_ag'].' average minerals/fuel/agriculture';
	else
		$average_resources = $series['average_resources'].' average resources';
    	
   	// Diplomacy settings for this series.
    switch ($series['diplomacy'])
		{
		case 2: $diplomacy = ' Truce, trade and alliances not allowed.';	break;
		case 3: $diplomacy = ' Trade and alliances not allowed.';			break;
		case 4: $diplomacy = ' No alliances allowed.';						break;
		case 6: $shared_hq = '<span class=blueBold>Shared HQ.</span> ';		break;
		}

	$description = '<tr><th style="text-align: left; vertical-align: top; color: red;">'.$series['name'].'</th>'.
				   '<td colspan=5>'.($series['team_game'] ? '<span class=greenBold>Team game.</span> ' : '').
				   $shared_hq.'Updates every '.$update_time.', '.($series['weekend_updates'] ? '' : 'no weekend updates, ').
				   $series['max_players'].' players, '.$series['systems_per_player'].' systems per player. '.
				   $map_type.(!$series['map_visible'] ? 'Map hidden until game starts. ' : '' ).
				   number_format($series['tech_multiple'], 1, '.', '').' tech, '.$average_resources.', ';
		
	if (!$series['min_wins'] and $series['max_wins'] == -1)
		$description .= 'anyone can join.';
    else if ($series['max_wins'] == -1)
    	$description .= $series['min_wins'].' or more wins needed to join.';
    else
    	$description .= 'from '.$series['min_wins'].' to '.$series['max_wins'].' wins needed to join.';
    	
    if ($series['build_cloakers_cloaked'])
    	$description .= ' <span style="color: fuchsia;">Cloakers built cloaked.</span> ';

    if ($series['cloakers_as_attacks'])
    	$description .= ' <span style="color: fuchsia;">Cloakers appear as attacks.</span> ';

	$description .= $diplomacy.'</td>';

	return $description;
}

#----------------------------------------------------------------------------------------------------------------------#

function badNumericValue($string, $minimum, $maximum = -1)
{	
	if (!is_numeric($string))
		return true;
	else if ($string < $minimum)
		return true;
	else if ($maximum != -1 and $string > $maximum)
		return true;
		
	return false;
}

#----------------------------------------------------------------------------------------------------------------------#
# Returns wether or not the empire can create a custom series. Used in createCustomSeries() to control the button's
# visibility.
#

function canCreateCustomSeries($empire)
{
	global $server;
	
	$select = sc_query('SELECT COUNT(*) FROM series WHERE creator = "'.$empire['name'].'"');
	
	$allowed_series = floor(($empire['wins']/$server['custom_series_wins_chunk'])*$server['custom_series_per_wins_chunk']);
	
	return ($empire['wins'] >= $server['custom_series_wins_chunk'] and mysql_result($select, 0, 0) < $allowed_series);
}
?>
