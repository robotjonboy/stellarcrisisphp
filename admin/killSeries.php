<?
function killSeries($vars)
{
	$empire = $vars['empire_data'];
	
	standardHeader('Kill Series', $empire);
?>
<div class=pageTitle>Kill a Series</div>
<input type=hidden name="name" value="<? echo $vars['name']; ?>">
<input type=hidden name="pass" value="<? echo $vars['pass']; ?>">
<input type=hidden name="section" value="admin">
<input type=hidden name="page" value="killSeries">
<?
	echo drawButtons($empire).serverTime().onlinePlayers().empireMissive($empire);
?>
<img class=spacerule src="images/spacerule.jpg">
<div style="text-align: center;">
	<select name="seriesToKill">
		<option value=0>Kill series...
<?
	$fields = array();
	$fields[] = 'series.id';
	$fields[] = 'series.name';
	$fields[] = 'COUNT(DISTINCT games.id) AS game_count';
	$fields[] = 'COUNT(DISTINCT players.id) AS player_count';

	$tables = 'series INNER JOIN games ON series.id = games.series_id INNER JOIN players ON games.id = players.game_id';
	$select = sc_mysql_query('SELECT '.implode(',', $fields).' FROM '.$tables.' GROUP BY id, name ORDER BY name');
	while ($series = mysql_fetch_array($select))
		echo '<option value="'.$series['id'].'">'.$series['name'].' ('.$series['game_count'].' game'.($series['game_count'] != 1 ? 's' : '').
			 ', '.$series['player_count'].' player'.($series['player_count'] != 1 ? 's' : '').')';
?>
	</select>
</div>
<div style="text-align: center; margin-top: 10pt;">
	<input type=submit name="killSeries" value="Kill">
	<input type=submit name="cancel" value="Cancel">
</div>
<?
	footer();
}

#----------------------------------------------------------------------------------------------------------------------#

function killSeries_processing($vars)
{
	if ($vars['cancel'])
		{
		sendEmpireMessage($vars['empire_data'], 'Kill series cancelled.');
		return mainPage_admin($vars);
		}
		
	if ($vars['killSeries'])
		{
		$series = getSeries($vars['seriesToKill']);

		// Inform the players their games have been killed.
		$tables = 'players INNER JOIN empires ON players.name = empires.name';

		$conditions = array();
		$conditions[] = 'players.series_id = '.$series['id'];
		$conditions[] = 'players.name <> "'.$vars['name'].'"'; // The admin may be in some games and is informed later on. Skip him.
			
		$select = sc_mysql_query('SELECT DISTINCT empires.* FROM '.$tables.' WHERE '.implode(' AND ', $conditions));
		while ($empire = mysql_fetch_array($select))
			sendEmpireMessage($empire, '<span class=red>'.$series['name'].'</span> has been killed.');
			
		// Cleanup.

		$tables = 'series INNER JOIN games ON series.id = games.series_id '.
						 'INNER JOIN players ON games.id = players.game_id '.
						 'INNER JOIN messages ON players.id = messages.player_id';
		sc_mysql_query('DELETE messages.* FROM '.$tables.' WHERE series.id = '.$series['id']);

		$tables = 'series INNER JOIN games ON series.id = games.series_id '.
						 'INNER JOIN players ON games.id = players.game_id '.
						 'INNER JOIN scouting_reports ON players.id = scouting_reports.player_id';
		sc_mysql_query('DELETE scouting_reports.* FROM '.$tables.' WHERE series.id = '.$series['id']);

		// Delete bridier data for ongoing games.
		$tables = 'series INNER JOIN games ON series.id = games.series_id '.
						 'INNER JOIN bridier ON games.id = bridier.game_id';
		sc_mysql_query('DELETE bridier.* FROM '.$tables.' WHERE series.id = '.$series['id']);

		$tables = 'series INNER JOIN games ON series.id = games.series_id '.
						 'INNER JOIN history ON games.id = history.game_id';
		sc_mysql_query('DELETE history.* FROM '.$tables.' WHERE series.id = '.$series['id']);

		sc_mysql_query('DELETE FROM diplomacies WHERE series_id = '.$series['id']);
		sc_mysql_query('DELETE FROM explored WHERE series_id = '.$series['id']);
		sc_mysql_query('DELETE FROM fleets WHERE series_id = '.$series['id']);
		sc_mysql_query('DELETE FROM games WHERE series_id = '.$series['id']);
		sc_mysql_query('DELETE FROM invitations WHERE series_id = '.$series['id']);
		sc_mysql_query('DELETE FROM players WHERE series_id = '.$series['id']);
		sc_mysql_query('DELETE FROM ships WHERE series_id = '.$series['id']);
		sc_mysql_query('DELETE FROM systems WHERE series_id = '.$series['id']);

		sc_mysql_query('DELETE FROM series WHERE id = '.$series['id']);

		sendEmpireMessage($vars['empire_data'], '<span class=red>'.$series['name'].'</span> has been killed.');
		return mainPage_admin($vars);
		}

	sendEmpireMessage($vars['empire_data'], 'Invalid action.');
	return mainPage_admin($vars);
}
?>