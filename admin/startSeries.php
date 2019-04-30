<?php
function startSeries($vars)
{
	$empire = $vars['empire_data'];
	
	standardHeader('Start Series', $empire);
?>
<input type=hidden name="name" value="<? echo $vars['name']; ?>">
<input type=hidden name="pass" value="<? echo $vars['pass']; ?>">
<input type=hidden name="empireID" value="<? echo $empire['id']; ?>">
<input type=hidden name="section" value="admin">
<input type=hidden name="page" value="startSeries">

<div class=pageTitle>Start a New Series</div>
<?
	echo drawButtons($empire).'<div class=message style="margin-top: 10pt;">Local time and date: '.date('l, F j H:i:s T Y', time()).'</div>'.
		 onlinePlayers().empireMissive($empire);
?>
<img class=spacerule src="images/spacerule.jpg" width="100%" height=10 alt="spacerule.jpg">
<div style="text-align: center;">
<table style="text-align: center; margin-left: auto; margin-right: auto;">
	<tr>
		<th style="text-align: right;">Series Name:</th>
		<td><input type=text size=40 maxlength=40 name="series_name"></td>
	</tr>
	<tr>
		<th style="text-align: right;">Update Time:</th>
		<td>
			<input type=text size=5 maxlength=5 name="update_time">
			<select name="update_time_unit">
				<option selected>Minutes
				<option>Hours
				<option>Days
			</select>
			<input type=checkbox name="weekend_updates" checked> Weekend Updates
		</td>
	</tr>
	<tr>
		<th style="text-align: right;">Diplomatic states allowed:</th>
		<td>
			<select name="diplomacy">
				<option value=6>All (Shared HQ)
				<option value=5 selected>Up to alliance
				<option value=4>Up to trade
				<option value=3>Up to truce
				<option value=2>War
			</select>
		</td>
	</tr>
	<tr>
		<th style="text-align: right;">Maximum players:</th>
		<td><input type=text size=3 maxlength=3 name="max_players"></td>
	</tr>
	<tr>
		<th style="text-align: right;">Team game:</th>
		<td>
			<select name="team_game">
				<option value=1>Yes
				<option value=0 selected>No
			</select>
		</td>
	</tr>
	<tr>
		<th style="text-align: right;">Map compresison:</th>
		<td><input type=text size=5 maxlength=5 name="map_compression" value="0.001"></td>
	</tr>
	<tr>
		<th style="text-align: right;">Systems per player:</th>
		<td><input type=text size=3 maxlength=3 name="systems_per_player"></td>
	</tr>
	<tr>
		<th style="text-align: right;">Map type:</th>
		<td>
			<select name="map_type">
				<option value=1 selected>Classic
				<option value=2>Pre-built (random placement)
				<option value=3>Twisted (2-player or team game only)
				<option value=4>Mirror (2-player game only)
				<option value=5>Balanced (2-player game only)
			</select>
		</td>
	</tr>
	<tr>
		<th style="text-align: right;">Map visible before game start:</th>
		<td>
			<select name="map_visible">
				<option value=1>Yes
				<option value=0 selected>No
			</select>
		</td>
	</tr>
	<tr>
		<th style="text-align: right;">Tech multiple:</th>
		<td><input type=text size=5 maxlength=5 name="tech_multiple"></td>
	</tr>
	<tr>
		<th style="text-align: right;">Average minerals:</th>
		<td><input type=text size=5 maxlength=4 name="avg_min" value="30"></td>
	</tr>
	<tr>
		<th style="text-align: right;">Average fuel:</th>
		<td><input type=text size=5 maxlength=4 name="avg_fuel" value="30"></td>
	</tr>
	<tr>
		<th style="text-align: right;">Average agriculture:</th>
		<td><input type=text size=5 maxlength=4 name="avg_ag" value="30"></td>
	</tr>
	<tr>
		<th style="text-align: right;">Minimum wins:</th>
		<td><input type=text size=5 name="min_wins" value="0"></td>
	</tr>
	<tr>
		<th style="text-align: right;">Maximum wins:</th>
		<td>
			<input type=text size=5 maxlength=7 name="max_wins">
			<input type=checkbox name="no_max_wins" checked>No maximum
		</td>
	</tr>
	<tr>
		<th style="text-align: right;">Can draw (2-player games only):</th>
		<td>
			<select name="can_draw">
				<option value=1 selected>Yes
				<option value=0>No
			</select>
		</td>
	</tr>
	<tr>
		<th style="text-align: right;">Can surrender (2-player games only):</th>
		<td>
			<select name="can_surrender">
				<option value=1 selected>Yes
				<option value=0>No
			</select>
		</td>
	</tr>
	<tr>
		<th style="text-align: right;">Visible builds:</th>
		<td>
			<select name="visible_builds">
				<option value=1>Yes
				<option value=0 selected>No
			</select>
		</td>
	</tr>
	<tr>
		<th style="text-align: right;">Cloakers appear as attacks:</th>
		<td>
			<select name="cloakers_as_attacks">
				<option value=1<? echo ($series['cloakers_as_attacks'] == 1 ? ' selected' : ''); ?>>Yes
				<option value=0<? echo ($series['cloakers_as_attacks'] == 0 ? ' selected' : ''); ?>>No
			</select>
		</td>
	</tr>
	<tr>
		<th style="text-align: right;">Bridier allowed:</th>
		<td>
			<select name="bridier_allowed">
				<option value=1>Yes
				<option value=0 selected>No
			</select>
		</td>
	</tr>
</table>
<br>
<input type=submit name="confirm" value="Start">
<input type=submit name="confirm" value="Cancel">
</div>
<?
	footer();
}

#----------------------------------------------------------------------------------------------------------------------#

function startSeries_processing($vars)
{
	global $authenticated_as_admin;
	
	$map_types = array(1 => 'standard', 2 => 'prebuilt', 3 => 'twisted', 4 => 'mirror', 5 => 'balanced');
	
	if (!$authenticated_as_admin)
		return loginFailed('Identity check failed.');

	$vars['empire_data'] = getEmpire($vars['name']);
	$empire = $vars['empire_data'];

	switch ($vars['action'])
		{
		case 'Password Games':	return passwordGameList($vars);
		case 'Custom Series':	return customSeries($vars);
		case 'Edit Profile':	return editProfile($vars);
		case 'Stat Viewer':		return statViewer($vars);
		case 'Game List':		return gameList($vars);
		case 'Game History':	return gameHistory($vars, '', 0, array());
		case 'Administration':	return mainPage_admin($vars);
		case 'Logout':			return mainPage();
		}
	
	if ($vars['confirm'] == 'Cancel')
		{
		sendEmpireMessage($empire, 'Series creation cancelled.');
		return mainPage_admin($vars);
		}

	// Various sanity checks.
	
	$invalid_data = false;

	if ($vars['series_name'] == '')
		{
		sendEmpireMessage($empire, 'No series name entered.');
		$invalid_data = true;
		}

	// Make sure the series name is not already in use.
	if (getSeriesByName($vars['series_name']))
		{
		sendEmpireMessage($empire, 'That series name is already in use.');
		$invalid_data = true;
		}

	if (badNumericValue($vars['map_compression'], 0.001, 0.8))
		{
		sendEmpireMessage($empire, 'Map compression must be between 0.001 and 0.8.');
		$invalid_data = true;
		}

	if (ereg('=', $vars['series_name']))
		{
		sendEmpireMessage($empire, 'The series name cannot contain an "=".');
		$invalid_data = true;
		}

	if (badNumericValue($vars['update_time'], 1))
		{
		sendEmpireMessage($empire, 'An invalid update time was entered.');
		$invalid_data = true;
		}

	if (badNumericValue($vars['max_players'], 2))
		{
		sendEmpireMessage($empire, 'An invalid player maximum was entered.');
		$invalid_data = true;
		}

	if (badNumericValue($vars['systems_per_player'], 3))
		{
		sendEmpireMessage($empire, 'An invalid systems per player was entered.');
		$invalid_data = true;
		}

	if (badNumericValue($vars['tech_multiple'], 0))
		{
		sendEmpireMessage($empire, 'An invalid tech multiple was entered.');
		$invalid_data = true;
		}

	if (badNumericValue($vars['min_wins'], 0))
		{
		sendEmpireMessage($empire, 'No minimum wins entered.');
		$invalid_data = true;
		}

	if ($vars['bridier_allowed'] and $vars['max_players'] != 2)
		{
		sendEmpireMessage($empire, 'Bridier is for 2 player games only.');
		$invalid_data = true;
		}

	if ($vars['map_type'] == 2 and $vars['max_players']*$vars['systems_per_player'] > 250)
		{
		sendEmpireMessage($empire, 'Pre-built map not allowed for more than 250 total systems.');
		$invalid_data = true;
		}

	if ($vars['no_max_wins'] == '' and badNumericValue($vars['max_wins'], 0))
		{
		sendEmpireMessage($empire, 'No maximum wins entered.');
		$invalid_data = true;
		}

	if ($vars['no_max_wins'] == '' and badNumericValue($vars['max_wins'], $vars['min_wins']))
		{
		sendEmpireMessage($empire, 'Maximum wins was less than minimum wins.');
		$invalid_data = true;
		}

	if ($vars['team_game'])
		{
		if (floor($vars['max_players']/2) != $vars['max_players']/2)
			{
			sendEmpireMessage($empire, 'Team games must have an even number of players.');
			$invalid_data = true;
			}

		if ($vars['diplomacy'] < 5)
			{
			sendEmpireMessage($empire, 'Team games must allow alliance.');
			$invalid_data = true;
			}

		if ($vars['map_type'] != 3)
			{
			$vars['map_type'] = 3;
			sendEmpireMessage($empire, 'Map type set to twisted for team game.');
			}
		}
	else if ($vars['map_type'] == 3 and $vars['max_players'] > 2)
		{
		sendEmpireMessage($empire, 'Twisted map type not allowed in non-team games with more than two players.');
		$invalid_data = true;
		}
	else if ($vars['map_type'] == 4 and $vars['max_players'] > 2)
		{
		sendEmpireMessage($empire, 'Mirror map type not allowed in non-team games with more than two players.');
		$invalid_data = true;
		}
	else if ($vars['map_type'] == 5 and $vars['max_players'] > 2)
		{
		sendEmpireMessage($empire, 'Balanced map not allowed in games with more than two players.');
		$invalid_data = true;
		}

	if ($invalid_data)
		return startSeries($vars);

	switch ($vars['update_time_unit'])
  		{
   		case 'Minutes':	$vars['update_time'] *= 60;		break;
   		case 'Hours':	$vars['update_time'] *= 3600;	break;
   		case 'Days':	$vars['update_time'] *= 86400;	break;
  		}

	$vars['weekend_updates'] = ($vars['weekend_updates'] != '' ? 1 : 0);
	$vars['max_wins'] = ($vars['no_max_wins'] != '' ? -1 : $vars['max_wins']);

	$values = array();
	$values[] = 'name = "'.$vars['series_name'].'"';

	$values[] = 'average_resources = "'.$vars['avg_min'].'"';
	$values[] = 'avg_ag = "'.$vars['avg_ag'].'"';
	$values[] = 'avg_fuel = "'.$vars['avg_fuel'].'"';
	$values[] = 'avg_min = "'.$vars['avg_min'].'"';
	$values[] = 'bridier_allowed = "'.$vars['bridier_allowed'].'"';
	$values[] = 'build_cloakers_cloaked = "1"'; //no input
	$values[] = 'can_draw = "'.$vars['can_draw'].'"';
	$values[] = 'can_surrender = "'.$vars['can_surrender'].'"';
	$values[] = 'cloakers_as_attacks = "'.$vars['cloakers_as_attacks'].'"';
	$values[] = 'creator = "Admin"'; //no input
	$values[] = 'diplomacy = "'.$vars['diplomacy'].'"';
			// game_count
			// halted
	$values[] = 'map_compression = "'.$vars['map_compression'].'"';
	$values[] = 'map_type = "'.$map_types[$vars['map_type']].'"';
	$values[] = 'map_visible = "'.$vars['map_visible'].'"';
	$values[] = 'max_players = "'.$vars['max_players'].'"';
	$values[] = 'max_wins = "'.$vars['max_wins'].'"';
	$values[] = 'min_wins = "'.$vars['min_wins'].'"';
	$values[] = 'systems_per_player = "'.$vars['systems_per_player'].'"';
	$values[] = 'team_game = "'.$vars['team_game'].'"';
	$values[] = 'tech_multiple = "'.$vars['tech_multiple'].'"';
	$values[] = 'update_time = "'.$vars['update_time'].'"';
	$values[] = 'visible_builds = "'.$vars['visible_builds'].'"';
	$values[] = 'weekend_updates = "'.$vars['weekend_updates'].'"';
	$cjpsql='INSERT INTO series SET '.implode(',', $values);
	echo "<p>".$cjpsql."<p>";

	sc_mysql_query('INSERT INTO series SET '.implode(',', $values));

	spawnGame($vars['series_name']);

	sendEmpireMessage($empire, 'Series <span style="color: red;">'.stripslashes($vars['series_name']).'</span> successfully started.');

	mainPage_admin($vars);
}
?>