<?
function notesScreen($vars)
{
	$player = $vars['player_data'];

	gameHeader($vars, 'Notes');

	echo '<div style="text-align: center;"><textarea cols=100 rows=20 name="notes">'.stripslashes($player['notes']).'</textarea></div>';

	footer();
}

#----------------------------------------------------------------------------------------------------------------------#

function notesScreen_processing($vars)
{
	$notes = ($vars['notes'] ? '"'.addslashes($vars['notes']).'"' : 'NULL');

	sc_mysql_query('UPDATE players SET notes = '.$notes.' WHERE id = '.$vars['player_data']['id']);
}
?>
