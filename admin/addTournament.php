<?
function addTournament($vars)
{
	$empire = $vars['empire_data'];
	
	//grab a list of two player series (currently tournaments only support two player series)
	$sql  = 'SELECT id, name from series where max_players = 2';
	$select = sc_mysql_query($sql);
	
	$numberOfSeries = 0;
	for ($i = 0; $series = mysql_fetch_assoc($select); $i++)
	{
		$numberOfSeries++;
		$seriesList[$i] = $series;
	}
	
	standardHeader('Add Tournament', $empire);
?>
<input type=hidden name="name" value="<? echo $vars['name']; ?>">
<input type=hidden name="pass" value="<? echo $vars['pass']; ?>">
<input type=hidden name="empireID" value="<? echo $empire['id']; ?>">
<input type=hidden name="section" value="admin">
<input type=hidden name="page" value="addTournament">

<?
	// show online players drop box
    echo "<div class=pageTitle>Add a Tournament</div>";
	echo drawButtons($empire); //EmpireName : create a series
	echo '<div class=message style="margin-top: 10pt;">';
	echo      'Local time and date: '.date('l, F j H:i:s T Y', time());
	echo onlinePlayers().empireMissive($empire);
	echo "<img class=spacerule src=\"images/spacerule.jpg\" ";
	echo      "width=\"100%\" height=10 alt=\"spacerule.jpg\">";
	echo '</div>';
?>

<div style="text-align: center;">
<table style="text-align: center; margin-left: auto; margin-right: auto;">
	<tr>
		<th style="text-align: right;">Tournament Name:</th>
		<td><input type=text size=40 maxlength=40 name="tournament_name"></td>
	</tr>
	<tr>
		<th style="text-align: right;">Description:</th>
		<td><textarea rows=5 cols=80 name="tournament_description"></textarea></td>
	</tr>
	<tr>
		<th style="text-align: right;">Start Time (M/D/Y):</th>
		<td>
			<input type=text size=2 maxlength=2 name="start_month" value="01"> /
			<input type=text size=2 maxlength=2 name="start_day" value="01"> /
			<input type=text size=4 maxlength=4 name="start_year" value="2009">
			<input type=text size=2 maxlength=2 name="start_hour" value="12"> :
			<input type=text size=2 maxlength=2 name="start_minute" value="00">
		</td>
	</tr>
	<tr>
		<th style="text-align: right;">Series:</th>
		<td><select name="series">
			<?php	for ($i = 0; $i < $numberOfSeries; $i++)
				{
					echo '<option value="' . $seriesList[$i]['id'] . '">' . $seriesList[$i]['name'] .
					     '</option>';
				} ?>
		</select></td>
	</tr>
</table>
<br>
<input type=submit name="confirm" value="Create">
<input type=submit name="confirm" value="Cancel">
</div>
<?
	footer();
}

#----------------------------------------------------------------------------------------------------------------------#

function addTournament_processing($vars)
{
	global $authenticated_as_admin;
	
	if (!$authenticated_as_admin)
		return loginFailed('Identity check failed.');

	$vars['empire_data'] = getEmpire($vars['name']);
	$empire = $vars['empire_data'];

	switch ($vars['action'])
		{
		case 'Password Games':	return passwordGameList($vars);
		case 'Custom Series':	return customSeries($vars);
		case 'Edit Profile':	return editProfile($vars);
		case 'Stat Viewer':		return statViewer($vars);
		case 'Game List':		return gameList($vars);
		case 'Game History':	return gameHistory($vars, '', 0, array());
		case 'Administration':	return mainPage_admin($vars);
		case 'Logout':			return mainPage();
		}
	
	if ($vars['confirm'] == 'Cancel')
		{
		sendEmpireMessage($empire, 'Tournament creation cancelled.');
		return mainPage_admin($vars);
		}

	// Various sanity checks.
	
	$invalid_data = false;

	if ($vars['tournament_name'] == '')
		{
		sendEmpireMessage($empire, 'No tournament name entered.');
		$invalid_data = true;
		}

	if (badNumericValue($vars['start_month'], 1, 12))
		{
		sendEmpireMessage($empire, 'An invalid starting month was entered.');
		$invalid_data = true;
		}

	if (badNumericValue($vars['start_day'], 1, 31))
		{
		sendEmpireMessage($empire, 'An invalid starting day was entered.');
		$invalid_data = true;
		}

	if (badNumericValue($vars['start_year'], 2009))
		{
		sendEmpireMessage($empire, 'An invalid starting year was entered.');
		$invalid_data = true;
		}

	if (badNumericValue($vars['start_hour'], 0, 23))
		{
		sendEmpireMessage($empire, 'An invalid starting hour was entered.');
		$invalid_data = true;
		}

	if (badNumericValue($vars['start_minute'], 0, 59))
		{
		sendEmpireMessage($empire, 'An invalid starting minute was entered.');
		$invalid_data = true;
		}
		
	//verify that the chosen series is a two player series
	$sql  = 'SELECT id, name from series where max_players = 2 AND id = ' . ((int) $vars['series']);
	$select = sc_mysql_query($sql);
	
	if (!$select || mysql_num_rows($select) == 0)
	{
		sendEmpireMessage($empire, 'An invalid series was selected.');
		$invalid_data = true;
	}	
	
	$numberOfSeries = 0;
	for ($i = 0; $series = mysql_fetch_assoc($select); $i++)
	{
		$numberOfSeries++;
		$seriesList[$i] = $series;
	}

	if ($invalid_data)
		return addTournament($vars);
	
	$starttime = mktime($vars['start_hour'], $vars['start_minute'], 0, $vars['start_month'], $vars['start_day'],
	                    $vars['start_year']);

	$values = array();
	$values[] = 'starttime = "'.$starttime.'"';
	$values[] = 'name = "'.mysql_real_escape_string($vars['tournament_name']).'"';
	$values[] = 'description = "'.mysql_real_escape_string($vars['tournament_description']).'"';
	$values[] = 'series = "'.((int)$vars['series']).'"';

	sc_mysql_query('INSERT INTO tournament SET '.implode(',', $values));

	sendEmpireMessage($empire, 'Tournament <span style="color: red;">'.stripslashes($vars['tournament_name']).'</span> successfully created.');

	mainPage_admin($vars);
}
?>