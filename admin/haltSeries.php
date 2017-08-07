<?
function haltSeries($vars)
{
	standardHeader('Halt series', $vars['empire_data']);
?>
<div class=pageTitle>Halt series</div>

<input type=hidden name="name" value="<? echo $vars['name']; ?>">
<input type=hidden name="pass" value="<? echo $vars['pass']; ?>">
<input type=hidden name="section" value="admin">
<input type=hidden name="page" value="haltSeries">
<?
	echo drawButtons($vars['empire_data']).serverTime().onlinePlayers().empireMissive($vars['empire_data']);

	$select = sc_mysql_query('SELECT id, name FROM series WHERE halted = "0" ORDER BY name ASC');
?>
<img class=spacerule src="images/spacerule.jpg" width="100%">

<div style="text-align: center; margin-left: auto; margin-right: auto;">
<select name="seriesToHalt" onChange="document.forms[0].halt.disabled = (document.forms[0].seriesToHalt.value == 0)">
	<option value="">Halt series...
<?
	while ($series = mysql_fetch_array($select))
		echo '<option value="'.$series['id'].'">'.$series['name'];	
?>
</select>
</div>
<div style="text-align: center; margin-top: 10pt;">
	<input type=submit name=halt value="Halt" disabled>
	<input type=submit name=cancel value="Cancel">
</div>
<?
	footer();
}

#----------------------------------------------------------------------------------------------------------------------#

function haltSeries_processing($vars)
{
	if ($vars['cancel'])
		{
		sendEmpireMessage($vars['empire_data'], 'Halt series cancelled.');
		return mainPage_admin($vars);
		}
	else if ($vars['halt'])
		{
		$series = getSeries($vars['seriesToHalt']);

		// First kill off any games that have no one in it...
		$select = sc_mysql_query('SELECT * FROM games WHERE series_id = '.$series['id'].' AND player_count = 0');
		while ($game = mysql_fetch_array($select)) eraseGame($game['id']);
	
		// ...and decrease the game count so that when we resume the series we start back where we were.
		sc_mysql_query('UPDATE series SET game_count = (game_count-1) WHERE id = '.$series['id']);

		// Finally, halt the series.
		sc_mysql_query('UPDATE series SET halted = "1" WHERE id = '.$series['id']);

		sendEmpireMessage($vars['empire_data'], 'Series <span class=red>'.$series['name'].'</span> halted.');
		return haltSeries($vars);
		}

	sendEmpireMessage($vars['empire_data'], 'Invalid action.');
	return mainPage_admin($vars);
}
?>