<?
function viewTournament($vars, $message = '')
{
	$empire = $vars['empire_data'];
	$tourney = $vars['tourney_data'];
	
	//is the empire an entrant?
	$sql = 'select * from tournamententrant where tournamentid = ' . $tourney['id'] . ' AND empireid = ' . $empire['id'];
	$select = sc_mysql_query($sql);
	
	if (mysql_num_rows($select) > 0) {
		$empireisentrant = true;
	} else {
		$empireisentrant = false;
	}
	
	//has the tourney started?
	if ($tourney['starttime'] < time()) {
		$tourney['started'] = true;
	} else {
		$tourney['started'] = false;
	}
	
	//grab a list of active entrants
	$sql  = 'SELECT empireid, e.id, name, eliminated from tournamententrant te, empires e where tournamentid = ' . $tourney['id'] .
		  ' AND empireid = e.id AND eliminated = false';
	$select = sc_mysql_query($sql);
	
	$numberOfEntrants = 0;
	for ($i = 0; $entrant = mysql_fetch_assoc($select); $i++)
	{
		$entrants[$i] = $entrant;
		
		$numberOfEntrants++;
	}
	
	//eliminated entrants
	$sql  = 'SELECT empireid, e.id, name, eliminated from tournamententrant te, empires e where tournamentid = ' . $tourney['id'] .
		  ' AND empireid = e.id AND eliminated = true';
	$select = sc_mysql_query($sql);
	
	$numberOfEliminatedEntrants = 0;
	for ($i = 0; $entrant = mysql_fetch_assoc($select); $i++)
	{
		$eliminatedEntrants[$i] = $entrant;
		
		$numberOfEliminatedEntrants++;
	}
	
	//grab a list of tournament games
	$sql = 'select * from tournamentgame where tournament = ' . $tourney['id'] . ' order by round desc';
	$select = sc_mysql_query($sql);
	
	$numberOfCurrentRoundGames = 0;
	$numberOfPreviousRoundGames = 0;
	$currentRound = 1;
	while ($tourneyGame = mysql_fetch_assoc($select)) {
		$currentRound = max($currentRound, $tourneyGame['round']);
		
		//grab player names
		$sql2 = 'select game_id, name from players where game_id = ' . $tourneyGame['id'];
		$result2 = sc_mysql_query($sql);
		
		//grab winner name
		if (isset($tourneyGame['winner'])) {
			$sql2 = 'select id, name from empires where id = ' . $tourneyGame['winner'];
			$result2 = sc_mysql_query($sql2);
			$empire = mysql_fetch_assoc($result2);
			
			$tourneyGame['winnerName'] = $empire['name'];
		}
		
		if ($tourneyGame['round'] == $currentRound) {
			$currentRoundGames[$numberOfCurrentRoundGames++] = $tourneyGame;
		} else {
			$previousRoundGames[$numberOfPreviousRoundGames++] = $tourneyGame;
		}
	}
	
	standardHeader('Tournaments', $empire);
?>
<input type=hidden name=name value="<? echo $vars['name']; ?>">
<input type=hidden name=pass value="<? echo $vars['pass']; ?>">
<input type=hidden name="empireID" value="<? echo $empire['id']; ?>">
<input type=hidden name="section" value="main">
<input type=hidden name="page" value="tournaments">

<?
	// show online players drop box
    	echo "<div class=pageTitle>Tournaments</div>";
	echo drawButtons($empire); //EmpireName : create a series
	echo '<div class=message style="margin-top: 10pt;">';
	echo      'Local time and date: '.date('l, F j H:i:s T Y', time());
	echo onlinePlayers().empireMissive($empire);
	echo "<img class=spacerule src=\"images/spacerule.jpg\" ";
	echo      "width=\"100%\" height=10 alt=\"spacerule.jpg\">";
	echo '</div>';

if ($tourney['started']) {
?>

	<h3>Current Round Games</h3>
	<table><tr><th>Empire</th><th>Empire</th><th>Winner</th></tr>
<?php

	for ($i = 0; $i < $numberOfCurrentRoundGames; $i++)
	{
		echo '<tr><td>' . $currentRoundGames[$i]['firstempire'] . '</td>';
		echo '<td>' . $currentRoundGames[$i]['secondempire'] . '</td>';
		echo '<td>' . $currentRoundGames[$i]['winnerName'] . '</td></tr>';
	} ?>
	</table>
	<h3>Previous Games</h3>
	<table><tr><th>Round</th><th>Empire</th><th>Empire</th><th>Winner</th></tr>
	<?php

	for ($i = 0; $i < $numberOfPreviousRoundGames; $i++)
	{
		echo '<tr><td>' . $previousRoundGames[$i]['round'] . '</td>';
		echo '<td>' . $previousRoundGames[$i]['firstempire'] . '</td>';
		echo '<td>' . $previousRoundGames[$i]['secondempire'] . '</td>';
		echo '<td>' . $previousRoundGames[$i]['winnerName'] . '</td></tr>';
	}
	?></table><h3>Remaining Empires</h3><?php
} else {
	if ($empireisentrant) {
		//quit tourney
		echo '<input type=submit name="quittourney[' . $tourney['id'] . ']" value="Quit Tournament"/>';
	} else {
		//join tourney
		echo '<input type=submit name="join[' . $tourney['id'] . ']" value="Join Tournament"/>';
	}
	
	?><h3>Entered Empires</h3><?php
}

for ($i = 0; $i < $numberOfEntrants; $i++) {
	echo $entrants[$i]['name'] . '<br/>';
}

if ($tourney['started']) { ?>
	<h3>Eliminated Empires</h3>
<?php
	for ($i = 0; $i < $numberOfEliminatedEntrants; $i++) {
		echo $eliminatedEntrants[$i]['name'] . '<br/>';
	}
}

?>

<?php
	footer();
}

?>