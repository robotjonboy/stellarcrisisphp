<?php
function joinTournament($vars, $message = '')
{
	$empire = $vars['empire_data'];
	$tourney = $vars['tourney_data'];
	
	//is the tournament open?
	if (time() > $tourney['starttime']) {
		sendEmpireMessage($empire, 'That tournament is no longer accepting entrants.');
		return tournaments($vars);
	}
	
	//is the empire an entrant?
	$sql = 'select * from tournamententrant where tournamentid = ' . $tourney['id'] . ' AND empireid = ' . $empire['id'];
	$select = sc_query($sql);
	
	if ($select->num_rows > 0) {
		sendEmpireMessage($empire, 'You have already entered this tournament.');
		return tournaments($vars);
	} else {
		//join tournament
		$sql = 'insert into tournamententrant (tournamentid, empireid) values (' . $tourney['id'] . ', ' . $empire['id'] . ')';
		sc_query($sql);
	}
	require_once('main/viewtournament.php');
	return viewTournament($vars);
}

?>