<?php
function notesScreen($vars)
{
	$player = $vars['player_data'];

	gameHeader($vars, 'Notes');

	echo '<div style="text-align: center;"><textarea cols=100 rows=20 name="notes">'.stripslashes(array_key_exists('notes', $player) && isset($player['notes']) ? $player['notes'] : '').'</textarea></div>';

	footer();
}

#----------------------------------------------------------------------------------------------------------------------#

function notesScreen_processing($vars)
{
	$notes = (!empty($vars['notes']) ? '"'.sc_escape($vars['notes']).'"' : 'NULL');

	sc_query('UPDATE players SET notes = '.$notes.' WHERE id = '.((int)$vars['player_data']['id']));
}
?>
