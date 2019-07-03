<?php
function tournaments($vars, $message = '')
{
	$empire = $vars['empire_data'];
	
	//grab a list of tournaments
	$sql  = 'SELECT * from tournament order by starttime desc';
	$select = sc_query($sql);
	
	$numberOfTournaments = 0;
	for ($i = 0; $tourney = $select->fetch_assoc(); $i++)
	{
		//grab series name
		$sql2 = 'select id, name from series where id = ' . $tourney['series'];
		$result2 = sc_query($sql2);
		$series = $result2->fetch_assoc();
		$tourney['seriesname'] = $series['name'];
		
		$tourneys[$i] = $tourney;
		
		
		$numberOfTournaments++;
	}

	standardHeader('Tournaments', $empire);
?>
<input type=hidden name=name value="<?php echo $vars['name']; ?>">
<input type=hidden name=pass value="<?php echo $vars['pass']; ?>">
<input type=hidden name="empireID" value="<?php echo $empire['id']; ?>">
<input type=hidden name="section" value="main">
<input type=hidden name="page" value="tournaments">

<?php
	// show online players drop box
    	echo "<div class=pageTitle>Tournaments</div>";
	echo drawButtons($empire); //EmpireName : create a series
	echo '<div class=message style="margin-top: 10pt;">';
	echo      'Local time and date: '.date('l, F j H:i:s T Y', time());
	echo onlinePlayers().empireMissive($empire);
	echo "<img class=spacerule src=\"images/spacerule.jpg\" ";
	echo      "width=\"100%\" height=10 alt=\"spacerule.jpg\">";
	echo '</div>';
?>

<table><tr><th>View</th><th>Name</th><th>Series</th><th>Description</th><th>Start Time</th><th>Status</th></tr>
<?php

for ($i = 0; $i < $numberOfTournaments; $i++)
{
	echo '<tr><td><input type=submit name="view[' . $tourneys[$i]['id'] . ']" value="View"/></td>';

	echo '<td>' . $tourneys[$i]['name'] . '</td>';
	echo '<td><a style="text-decoration: none; color: red;" href="javascript:void(0)" ';
	echo 'onClick="window.open(\'sc.php?seriesParameters=' . $tourneys[$i]['series'] . '\',\'seriesParameters2\',\'height=500,width=600,scrollbars=yes\')">' . $tourneys[$i]['seriesname'] . '</a></td>';
	echo '<td>' . $tourneys[$i]['description'] . '</td>';
	echo '<td>' . date("F j, Y, g:i a", $tourneys[$i]['starttime']) . '</td><td>';
	
	if ($tourneys[$i]['completed'])
	{
		echo 'Completed';
	}
	elseif ($tourneys[$i]['starttime'] < time())
	{
		echo 'Ongoing';
	}
	else
	{
		echo 'Upcoming';
	}
} ?>
</table>
<?php
	footer();
}

function tournaments_processing($vars) {
	$empire = $vars['empire_data'];
	
	// Buttons on the game list screens are mini-arrays named after the action.
	// Their first element's key is the tournament ID we need to know about.
	foreach (array('view', 'join', 'quittourney') as $button)
		{
		if (isset($vars[$button]))
			{
			list($tourney_id) = array_keys($vars[$button]);

			$vars['tourney_data'] = $tourney = getTourneyByID($tourney_id);

			if (!$tourney)
				{
				sendEmpireMessage($empire, 'That tournament no longer exists.');
				return tournaments($vars);
				}
				else
				{
				$action = $button;
				}

			break;
			}
		}
		
	if ($action == 'view')
		{
			require_once('main/viewtournament.php');
			return viewTournament($vars);
		}

	if ($action == 'join')
		{
			require_once('main/jointournament.php');
			return joinTournament($vars);
		}
		
	if ($action == 'quittourney')
		{
			require_once('main/quittournament.php');
			return quitTournament($vars);
		}
		
	return tournaments($vars);
}
?>