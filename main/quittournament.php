<?
function quitTournament($vars, $message = '')
{
	$empire = $vars['empire_data'];
	$tourney = $vars['tourney_data'];
	
	//is the tournament open?
	if (time() > $tourney['starttime']) {
		sendEmpireMessage($empire, 'You cannot quit a tournament that has already started.');
		return tournaments($vars);
	}
	
	//is the empire an entrant?
	$sql = 'select * from tournamententrant where tournamentid = ' . $tourney['id'] . ' AND empireid = ' . $empire['id'];
	$select = sc_query($sql);
	
	if ($select->num_rows == 0) {
		sendEmpireMessage($empire, 'You cannot quit a tournament that you have not joined.');
		return tournaments($vars);
	} else {
		//quit tournament
		$sql = 'delete from tournamententrant where tournamentid = ' . $tourney['id'] . ' AND empireid = ' . $empire['id'];
		sc_query($sql);
	}
	require_once('main/viewtournament.php');
	return viewTournament($vars);
}

?>