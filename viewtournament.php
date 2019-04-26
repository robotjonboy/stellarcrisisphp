<?
function viewTournament($vars, $message = '')
{
	$empire = $vars['empire_data'];
	$tourney = $vars['tourney_data'];
	
	standardHeader('Tournaments', $empire);
	
?>
<input type=hidden name=name value="<? echo $vars['name']; ?>">
<input type=hidden name=pass value="<? echo $vars['pass']; ?>">
<input type=hidden name="empireID" value="<? echo $empire['id']; ?>">
<input type=hidden name="section" value="main">
<input type=hidden name="page" value="tournaments">

<?
	echo "<div class=pageTitle>View Tournament</div>";
	echo drawButtons($empire);
	echo '<div class=message style="margin-top: 10pt;">';
	echo      'Local time and date: '.date('l, F j H:i:s T Y', time());
	echo onlinePlayers().empireMissive($empire);
	echo "<img class=spacerule src=\"images/spacerule.jpg\" ";
	echo      "width=\"100%\" height=10 alt=\"spacerule.jpg\">";
	echo '</div>';
	
	//let's find out the status of this tournament
	if ($tourney['starttime'] > time())
	{
		//tournament is upcoming
	}
	elseif ($tourney['completed'])
	{
		//tournament is completed
	}
	else
	{
		//tournament is ongoing (or at least, it hasn't been marked as completed yet)
	}
?>

<table><tr><th>Join</th><th>Name</th><th>Description</th><th>Start Time</th><th>Status</th></tr>
<?php

echo '<tr><th><input type="submit" name="action" value="Join"/></th>';
echo '<td>' . $tourney['name'] . '</td><td>' . $tourney['description'] . '</td>';
echo '<td>' . date("F j, Y, g:i a", $tourneys['starttime']) . '</td><td>';

if ($tourney['completed'])
{
	echo 'Completed';
}
elseif ($tourney['starttime'] > time())
{
	echo 'Ongoing';
}
else
{
	echo 'Upcoming';
}
?>
</table>
<?php
	footer();
}
?>