<?php
function killGame($vars)
{
	$empire = $vars['empire_data'];
	
	standardHeader('Kill Game', $empire);
?>
<div class=pageTitle>Kill a Game</div>
<input type=hidden name="name" value="<?php echo $vars['name']; ?>">
<input type=hidden name="pass" value="<?php echo $vars['pass']; ?>">
<input type=hidden name="section" value="admin">
<input type=hidden name="page" value="killGame">
<?php
	echo drawButtons($empire).serverTime().onlinePlayers().empireMissive($empire);
?>
<img class=spacerule src="images/spacerule.jpg">
<div style="text-align: center;">
	<select name="gameToKill">
		<option value=0>Kill game...
<?php
	$fields = array();
	$fields[] = 'series.name as name';
	$fields[] = 'series.update_time AS update_time';
	$fields[] = 'games.game_number as game_number';
	$fields[] = 'games.closed as closed';
	$fields[] = 'games.player_count as player_count';
	$fields[] = 'games.id as id';

	$join = 'games INNER JOIN series ON series.id = games.series_id';
	$order = 'ORDER BY series.name, games.game_number ASC';

	$select = sc_mysql_query('SELECT '.implode(',', $fields).' FROM '.$join.' WHERE games.player_count > 0 '.$order, __FILE__.'*'.__LINE__);
	while ($row = mysql_fetch_array($select))
		echo '<option value="'.$row['id'].'">'.$row['name'].' '.$row['game_number'].' ('.$row['player_count'].' players) - '.($row['update_time']/3600).' hours';	
?>
	</select>
</div>
<div style="text-align: center; margin-top: 10pt;">
	<input type=submit name="killGame" value="Kill">
	<input type=submit name="cancel" value="Cancel">
</div>
<?php
	footer();
}

#----------------------------------------------------------------------------------------------------------------------#

function killGame_processing($vars)
{
	if ($vars['cancel'])
		{
		sendEmpireMessage($vars['empire_data'], 'Kill game canceled.');
		return mainPage_admin($vars);
		}
		
	if ($vars['killGame'])
		{
		$game = getGameByID($vars['gameToKill']);
		$series = getSeries($game['series_id']);

		sc_mysql_query('DELETE FROM bridier WHERE game_id = '.$game['id']);
		eraseGame($game['id']);

		// Not in 2.8 code, but how will one start a new game if it doesn't appear on the game list screen?
		// This will spawn a new game if no other is open, the assumption being that we only wanted to kill a current game.
		$select = sc_mysql_query('SELECT id FROM games WHERE series_id = '.$game['series_id'].' AND closed = "0"');
		if (!mysql_num_rows($select)) spawnGame($series['name']);
			
		sendEmpireMessage($vars['empire_data'], 'Game <span class=red>'.$game['game_number'].'</span> of series <span class=red>'.$series['name'].'</span> killed.');
		return mainPage_admin($vars);
		}

	sendEmpireMessage($vars['empire_data'], 'Invalid action.');
	return mainPage_admin($vars);
}
?>