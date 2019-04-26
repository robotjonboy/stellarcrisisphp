<?
#----------------------------------------------------------------------------------------------------------------------#

function statViewer($vars, $message = '')
{
	global $server;

	$select = sc_mysql_query('SELECT COUNT(*) AS count from empires');
	$empire_count = mysql_result($select, 0, 0);

	$empire = $vars['empire_data'];

	standardHeader('Stat Viewer', $empire);
?>
<input type=hidden name="name" value="<? echo $vars['name']; ?>">
<input type=hidden name="pass" value="<? echo $vars['pass']; ?>">
<input type=hidden name="section" value="main">
<input type=hidden name="page" value="statViewer">
<div class=pageTitle>Stat Viewer</div>
<?
	echo drawButtons($empire).
		'<div class=message style="margin-top: 10pt;">Local time and date: '.
			date('l, F j H:i:s T Y', time()).
		'</div>'.
		onlinePlayers().empireMissive($empire);
?>
<img class="spacerule"
	src="images/spacerule.jpg" 
	width="100%" 
	height=10 
	alt="spacerule.jpg">

<div class=messageBold>
	There are <? echo $empire_count; ?> registered players.
</div>

<div style="margin-top: 10pt; text-align: center;">
  <table border=0 style="margin-left: auto; margin-right: auto;">
<?
	if ($server['top_lists_enabled'])
		{
?>
	<tr>
		<th style="text-align: right;">
			Top Ten Scores
		</th>
		<td>
			<input type=submit name="top[win]" value="Go">
		</td>
	</tr>
	<!-- <tr><th style="text-align: right;">Top Ten Averages</th><td><input type=submit name="top:avg" value="Go"></td></tr> -->
	<tr>
		<th style="text-align: right;">
			Top Ten Nuke/Win Ratios
		</th>
		<td>
			<input type=submit name="top[wnr]" value="Go">
		</td>
	</tr>
	<tr>
		<th style="text-align: right;">
			Bridier Scores
		</th>
		<td>
			<input type=submit name="bridier[active]" value="Go">
		</td>
	</tr>
	<tr>
		<th style="text-align: right;">
			Bridier Scores (established)
		</th>
		<td>
			<input type=submit name="bridier[active_established]" value="Go">
		</td>
	</tr>
<?
		}
?>
  </table>
</div>

<div style="margin-top: 10pt; text-align: center;">
  <table border=0 style="margin-left: auto; margin-right: auto;">
	<tr>
		<th style="text-align: right;">
			Search by name:
		</th>
		<td>
			<input type=text name=search_name size=20 maxlength=20>
		</td>
		<td>
			<input type=submit name=action value=Search>
		</td>
	</tr>
  </table>
</div>

<div style="color: white; font-size: 7pt; text-align: center;">
	Partial names allowed, use * as a wildcard.
</div>
<?
	footer();
}

#----------------------------------------------------------------------------------------------------------------------#

function statViewer_processing($vars)
{
	if (isset($vars['top']))		//both top ten scores and top ten ratio
		return topTen($vars);
	
	if (isset($vars['bridier']))	//both bridier & bridier_established
		return bridierScores($vars);
		
	if ($vars['action'] == 'Search' or $vars['search_name'])
		return searchEmpire($vars);
}

#----------------------------------------------------------------------------------------------------------------------#

function topTen($vars)
{
	list($type) = array_keys($vars['top']);
	
	$empire = $vars['empire_data'];

	// Regardless of what top ten list was requested, we always get these fields.
	$fields = 'name, real_name, email, wins, nukes, nuked, ruined, email_visible';

	switch($type)
		{
		case 'win':
			$label = 'Winners';
			$formula = '(wins + nukes/2 - nuked/2 - ruined)';
			$select = sc_mysql_query('SELECT '.$fields.', '.$formula.' AS score FROM empires '.
									 'WHERE wins ORDER BY score DESC, name ASC LIMIT 10');
			break;
		case 'avg':
			// This one is usually disabled because it's not really relevant. Any ideas for something else to rate?
			$label = 'Averages';
			$formula = '(wins/(wins + nuked + ruined))';$secondary_formula;
			$secondary_formula = '(wins + nukes/2 - nuked/2 - ruined)'; // actually, the 'winners' formula
			$select = sc_mysql_query('SELECT '.$fields.', '.$formula.' AS score, '.$secondary_formula.' AS win_score FROM empires '.
									 'WHERE (wins + nuked + ruined) ORDER BY score DESC, win_score DESC LIMIT 10');
			break;
		case 'wnr':
			$label = 'Nuke/Win Ratios';
			$formula = '(nukes/(wins + nuked + ruined))';
			$select = sc_mysql_query('SELECT '.$fields.', '.$formula.' AS score FROM empires '.
									 'WHERE (wins + nuked + ruined) ORDER BY score DESC, name ASC LIMIT 10');
			break;
		}

	standardHeader('Top Ten '.$label, $empire);
?>
<div>
<input type=hidden name="name" value="<? echo $vars['name']; ?>">
<input type=hidden name="pass" value="<? echo $vars['pass']; ?>">
<input type=hidden name="section" value="main">
<input type=hidden name="page" value="statViewer">
<div class=pageTitle>Top Ten <? echo $label; ?></div>
<?
	echo drawButtons($empire).serverTime().onlinePlayers().empireMissive($empire);
?>
<img class=spacerule src="images/spacerule.jpg" width="100%" height=10 alt="spacerule.jpg">
<div>
<table cellspacing=5 style="font-size: 10pt; text-align: center; margin-top: 10pt; margin-bottom: 10pt; margin-left: auto; margin-right: auto;">
	<tr valign=top>
		<th align=left>Empire</th>
		<th align=left>Real Name</th>
		<th align=left>E-Mail</th>
		<th>Wins</th>
		<th>Nukes</th>
		<th>Been Nuked</th>
		<th>Ruins</th>
		<th>Score</th>
	</tr>
<?
	while ($row = mysql_fetch_array($select))
		{
?>
	<tr>
		<td><? echo $row['name']; ?></td>
		<td><? echo $row['real_name']; ?></td>
		<td>
		<?
			if ($row['email_visible'])
				echo '<a href="mailto:'.$row['email'].'">'.$row['email'].'</a></td>';
			else
				echo '<i>hidden</i>';
		?>
		</td>
		<td style="text-align: center;"><? echo $row['wins']; ?></td>
		<td style="text-align: center;"><? echo $row['nukes']; ?></td>
		<td style="text-align: center;"><? echo $row['nuked']; ?></td>
		<td style="text-align: center;"><? echo $row['ruined']; ?></td>
		<td style="text-align: right;"><? echo $row['score']; ?></td>
	</tr>
<?
		}
?>
</table>
</div>
<?
	footer();
}

#----------------------------------------------------------------------------------------------------------------------#

function bridierScores($vars)
{
	$empire = $vars['empire_data'];

	standardHeader('Bridier Scores', $empire);

	$conditions = array();
	$conditions[] = 'bridier_index <> 500';
	
	// Default to displaying only if they've got a result in the last 8 weeks.
	
	if ($vars['bridier']['active'])
		$conditions[] = 'bridier_update > '.(time()-8*7*86400);
	
	if ($vars['bridier']['active_established'])
		{
		$conditions[] = 'bridier_update > '.(time()-8*7*86400);
		$conditions[] = 'bridier_index = 100';
		}

	if ($vars['bridier']['all_established'])
		$conditions[] = 'bridier_index = 100';

	$query = 'SELECT * FROM empires WHERE '.implode(' AND ', $conditions).' '.
			 'ORDER BY bridier_rank DESC, bridier_delta DESC, bridier_index ASC';
	
	$select = sc_mysql_query($query);	
	$active_players = mysql_num_rows($select);	
		
	if ($vars['bridier']['active'])				$caption = "(Game result within last 8 weeks.)";
	if ($vars['bridier']['active_established'])	$caption = "(Established players, game result within last 8 weeks.)";
	if ($vars['bridier']['all'])				$caption = "(All players with any Bridier result.)";
	if ($vars['bridier']['all_established'])	$caption = "(All established players, no time limit.)";
?>
<div>
<input type=hidden name="name" value="<? echo $vars['name']; ?>">
<input type=hidden name="pass" value="<? echo $vars['pass']; ?>">
<input type=hidden name="section" value="main">
<input type=hidden name="page" value="statViewer">
<div class=pageTitle>Bridier Scores</div>
<?
	echo drawButtons($empire).'<div class=message style="margin-top: 10pt;">Local time and date: '.date('l, F j H:i:s T Y', time()).'</div>'.
		 onlinePlayers().empireMissive($empire);
?>
<img class=spacerule src="images/spacerule.jpg" width="100%" height=10 alt="spacerule.jpg">
<div align=center class=messageBold>
	<? echo $active_players.''.(ereg('All', $caption) ? ' ' : ' active ').'player'.($active_players != 1 ? 's' : '').'<br>'.$caption; ?>
</div>
<?
	if ($active_players)
		{
?>
<div style="text-align: center; margin-top: 10pt;">
<table cellspacing=5 style="font-size: 10pt; text-align: center; margin-left: auto; margin-right: auto;">
	<tr>
		<th>Empire</th>
		<th>Bridier Rank</th>
		<th colspan=2>Last Change</th>
		<th>Bridier Index</th>
	</tr>
<?
		while ($row = mysql_fetch_array($select))
			{
			$seconds = (time() - $row['bridier_update']);

			if ($seconds < 86400)
				$time_delta = number_format($seconds/3600, 0, '.', '').' hours';
			else if ($seconds < 7*86400)
				$time_delta = number_format($seconds/86400, 1, '.', '').' days';
			else if ($seconds < 6*30*86400)
				$time_delta = number_format($seconds/(86400*7), 0, '.', '').' weeks';
			else
				$time_delta = number_format($seconds/(6*30*86400), 0, '.', '').' months';

			$class = ($row['bridier_delta'] < 0 ? 'red' : 'green');
?>
	<tr>
		<td style="text-align: left;"><? echo $row['name']; ?></td>
		<td style="text-align: center;"><? echo $row['bridier_rank']; ?></td>
		<td class=<? echo $class; ?>><? echo ($row['bridier_delta'] > 0 ? '+' : '').$row['bridier_delta']; ?></td>
		<td style="text-align: right;">(<? echo $time_delta; ?>)</td>
		<td style="text-align: center;"><? echo $row['bridier_index']; ?></td>
	</tr>
<?
			}
?>
</table>
</div>
<?
		}

	echo '<div style="text-align: center; margin-top: 10pt;">';

	if ($vars['bridier']['all'])				echo 'Show Only Active Bridier Scores <input type=submit name="bridier[active]" value="Go">';
	if ($vars['bridier']['active'])				echo 'Show All Bridier Scores <input type=submit name="bridier[all]" value="Go">';
	if ($vars['bridier']['active_established'])	echo 'Show All Established Bridier Scores <input type=submit name="bridier[all_established]" value="Go">';
	if ($vars['bridier']['all_established'])	echo 'Show Only Active Players <input type=submit name="bridier[active_established]" value="Go">';

	echo '</div>';

	footer();
}

#----------------------------------------------------------------------------------------------------------------------#

function searchEmpire($vars)
{
	global $server;
	
	$empire = $vars['empire_data'];

	standardHeader('Stat Viewer', $empire);
?>
<input type=hidden name="name" value="<? echo $vars['name']; ?>">
<input type=hidden name="pass" value="<? echo $vars['pass']; ?>">
<input type=hidden name="section" value="main">
<input type=hidden name="page" value="statViewer">
<div class=pageTitle>Stat Viewer</div>
<?
	echo drawButtons($empire).serverTime().onlinePlayers().empireMissive($empire);
?>
<img class=spacerule src="images/spacerule.jpg" alt="spacerule.jpg">
<?
	// Try an exact match first.
	$exact_search = sc_mysql_query('SELECT * FROM empires WHERE name = "'.$vars['search_name'].'"');
	
	// If the search string was empty, assume we want everything. Not that this will matter on a server with lots of players.
	if ($vars['search_name'] == '') $vars['search_name'] = '%';
	else $vars['search_name'] = str_replace('*', '%', $vars['search_name']);
	
	// Now try multiple matches.
	$fuzzy_search = sc_mysql_query('SELECT * FROM empires WHERE name LIKE "%'.$vars['search_name'].'%" ORDER BY name ASC');
	
	// If we get one match either way, show it.
	if (mysql_num_rows($exact_search) == 1 or mysql_num_rows($fuzzy_search) == 1)
		{
		if (!$empire = mysql_fetch_array($exact_search))
			$empire = mysql_fetch_array($fuzzy_search);
?>
<table border=0 cellpadding=5 style="margin-left: auto; margin-right: auto;">
	<tr>
		<td><img src="images/aliens/<? echo $empire['icon']; ?>" width=40 height=40 alt="<? echo $empire['icon']; ?>"></td>
		<td colspan=3 class=smallTitleLeft><? echo $empire['name']; ?></td>
	</tr>
	<tr>
		<th style="text-align: right;">Real Name:</th>
		<td><? echo $empire['real_name']; ?></td>
		<th style="text-align: right;">Wins:</th>
		<td><? echo $empire['wins']; ?></td>
	</tr>
	<tr>
		<th style="text-align: right;">E-Mail:</th>
		<td>
		<?
			if ($empire['email_visible'])
				echo '<a href="mailto:'.$empire['email'].'">'.$empire['email'].'</a></td>';
			else
				echo '<i>hidden</i>';
		?>
		<th style="text-align: right;">Nukes:</th>
		<td><? echo $empire['nukes']; ?></td>
	</tr>
	<tr>
		<th style="text-align: right;">Last Login:</th>
		<td><? echo date('l, F j H:i:s T Y', $empire['last_login']); ?></td>
		<th style="text-align: right;">Been Nuked:</th>
		<td><? echo $empire['nuked']; ?></td>
	</tr>
	<tr>
		<th style="text-align: right;" rowspan=4 valign=top>Joined on:</th>
		<td rowspan=4 valign=top>
<?								 
		// Special case for empires that joined before we started tracking join dates.
		// This only applies to the Iceberg server; you can remove this check if you start from scratch.
		echo ($empire['join_date'] == 1018409756 ? 'Before or on '.date('l, F j Y', 1018409756) : date('l, F j Y', $empire['join_date']));
?>
		</td>
		<th style="text-align: right;">Ruins:</th>
		<td><? echo $empire['ruined']; ?></td>
	</tr>
	<tr>
		<th style="text-align: right;">Max Econ:</th>
		<td><? echo $empire['max_economic_power']; ?></td>
	</tr>
	<tr>
		<th style="text-align: right;">Max Mil:</th>
		<td><? echo $empire['max_military_power']; ?></td>
	</tr>
	<tr>
		<th style="text-align: right;">Bridier (rank:index):</th>
		<td><? echo $empire['bridier_rank'].'<b>:</b>'.$empire['bridier_index']; ?></td>
<?
		if ($empire['comment'] != '')
			{
?>
	<tr><td colspan=4><img class=spaceruleThin src="images/spacerule.jpg" width="100%" height=10 alt="spacerule.jpg"></td></tr>
	<tr><td colspan=4><pre><? echo stripslashes(urldecode($empire['comment'])); ?></pre></td></tr>
<?					
			}
		
		echo '</table>';
		}
	else
		{
		// Ok, so we have more than one match (or none!), and this would be with the fuzzy query.
		if (mysql_num_rows($fuzzy_search) > $server['max_search_results'])
			echo '<div class=messageBold>Too many matching empires ('.mysql_num_rows($fuzzy_search).').</div>';
		else if ($matches = mysql_num_rows($fuzzy_search))
			{
?>
<div class=messageBold>
	There are <? echo $matches; ?> matching empire<? echo ($matches != 1 ? 's' : ''); ?>.
	<br>Click on the name of the empire you want to know more about.
</div>
<div style="text-align: center; margin-top: 10pt;">
<table cellspacing=5 style="margin-left: auto; margin-right: auto;">
	<tr>
		<td>
<?
			while ($empire = mysql_fetch_array($fuzzy_search))
				echo  '<input type=submit name=search_name value="'.$empire['name'].'">';
?>
		</td>
	</tr>
</table>
</div>
<?
			}
		else
			echo  '<div class=messageBold>There are no matching empires.</div>';
		}

	footer();
}
?>