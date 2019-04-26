<?
function resumeSeries($vars)
{
	standardHeader('Resume Series', $vars['empire_data']);
?>
<div class=pageTitle>Resume a Series</div>

<input type=hidden name="name" value="<? echo $vars['name']; ?>">
<input type=hidden name="pass" value="<? echo $vars['pass']; ?>">
<input type=hidden name="section" value="admin">
<input type=hidden name="page" value="resumeSeries">
<?
	echo drawButtons($vars['empire_data']).serverTime().onlinePlayers().empireMissive($vars['empire_data']);

	$select = sc_mysql_query('SELECT id, name FROM series WHERE halted = "1" ORDER BY name ASC');
?>
<img class=spacerule src="images/spacerule.jpg" width="100%">

<div style="text-align: center; margin-left: auto; margin-right: auto;">
<select name="seriesToResume" onChange="document.forms[0].resume.disabled = (document.forms[0].seriesToResume.value == 0)">
	<option value="">Resume series...
<?
	while ($series = mysql_fetch_array($select))
		echo '<option value="'.$series['id'].'">'.$series['name'];	
?>
</select>
</div>
<div style="text-align: center; margin-top: 10pt;">
	<input type=submit name=resume value="Resume" disabled>
	<input type=submit name=cancel value="Cancel">
</div>
<?
	footer();
}

#
#-----------------------------------------------------------------------------------------------------------------------------------------#
#

function resumeSeries_processing($vars)
{
	global $server;
	
	if ($vars['cancel'])
		{
		sendEmpireMessage($vars['empire_data'], 'Resume series cancelled.');
		return mainPage_admin($vars);
		}
	else if ($vars['resume'])
		{
		$series = getSeries($vars['seriesToResume']);

		sc_mysql_query('UPDATE series SET halted = "0" WHERE id = '.$series['id']);

		// Not in 2.8 code, but how will one start a new game if it doesn't appear on the game list screen?
		// This will spawn a new game if no other is open.
		$select_empty_games = sc_mysql_query('SELECT COUNT(*) FROM games WHERE series_id = '.$series['id'].' AND player_count = 0');
		if (!mysql_result($select_empty_games, 0, 0))
			spawnGame($series['name']);

		sendEmpireMessage($vars['empire_data'], 'Series <span class=red>'.$series['name'].'</span> resumed.');
		return resumeSeries($vars);
		}

	sendEmpireMessage($vars['empire_data'], 'Invalid action.');
	return mainPage_admin($vars);
}
?>