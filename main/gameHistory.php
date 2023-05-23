<?php
function gameHistory($vars)
{
	global $server;
	
	$empire = $vars['empire_data'];

	standardHeader('Game History', $empire);
	
	$conditions = (array_key_exists('conditions', $vars) && is_array($vars['conditions']) && count($vars['conditions'])) ? ' WHERE '.implode(' AND ', $vars['conditions']) : '';
	
	$select = sc_query('SELECT COUNT(id) as c FROM gamelog '.$conditions);
	$line = $select->fetch_assoc();
	$recorded_games = $line['c'];
	
	if (!isset($vars['first_record']))
		{
		$vars['first_record'] = 0;
		$limit = $server['histories_per_page']+1;
		}
	else if ($vars['first_record'] == -1)
		{
		$last_page_count = ($recorded_games % $server['histories_per_page']);
		$limit = ($recorded_games-$last_page_count).', '.$server['histories_per_page'];
		}
	else
		$limit = $vars['first_record'].', '.($server['histories_per_page']+1);
	
	if (!array_key_exists('result', $vars) || !$vars['result'])
		$vars['result'] = 'All';

	$select = sc_query('SELECT * FROM gamelog'.$conditions.' ORDER BY id DESC LIMIT '.$limit);
?>
<div>
<input type=hidden name=name value="<?php echo $vars['name']; ?>">
<input type=hidden name=pass value="<?php echo $vars['pass']; ?>">
<input type=hidden name="section" value="main">
<input type=hidden name="page" value="gameHistory">
<input type=hidden name="first_record" value="<?php echo $vars['first_record']; ?>">
<input type=hidden name="record_count" value="<?php echo $recorded_games; ?>">

<div class=pageTitle>Game History</div>
<?php	
	echo drawButtons($empire).'<div class=message style="margin-top: 10pt;">Local time and date: '.date('l, F j H:i:s T Y', time()).'</div>'.
		 onlinePlayers().empireMissive($empire);
?>
<img class=spacerule src="images/spacerule.jpg" width="100%" height=10 alt="spacerule.jpg">
<div class=messageBold>There are <?php echo ($recorded_games ? $recorded_games : 'no').' recorded game'.($recorded_games != 1 ? 's' : ''); ?>.</div>
<?php
	if ($recorded_games)
		{
?>
<div style="margin-top: 10pt;">
<table cellpadding=6 border=0 width="80%" style="font-size: 9pt; margin-left: auto; margin-right: auto;">
	<colgroup>
		<col width="*">
		<col width="*">
		<col width="*">
		<col width="150">
		<col width="100">
	</colgroup>
	<tr>
		<th style="color: white; font-size: 10pt;">Game</th>
		<th style="color: white; font-size: 10pt;">Result</th>
		<th style="color: white; font-size: 10pt;">Nuked</th>
		<th style="color: white; font-size: 10pt;">End time</th>
		<th></th>
	</tr>
<?php
		$n = 0;
		while ($oldgame = $select->fetch_assoc())
			{
			if ($n <= $server['histories_per_page'])
				{
				$n++;
				
				switch ($oldgame['result'])
					{
					case 'win':			$result = 'Won by ';				break;
					case 'draw':		$result = 'Declared a draw by ';	break;
					case 'abandoned':	$result = 'Abandoned by ';			break;
					case 'no winner':	$result = 'No winners';				break;
					default:			$result = 'Bad result code.';
					}
				
				$emps_left = str_replace('=', ', ', substr($oldgame['emps_left'], 1, (strlen($oldgame['emps_left'])-2)));
				$emps_nuked = str_replace('=', ', ', substr($oldgame['emps_nuked'], 1, (strlen($oldgame['emps_nuked'])-2)));
?>
	<tr class=top>
		<td style="font-size: 8pt;">
			<?php echo '<a href="'.$server['history_read_URL'].strtr($oldgame['name'], " ?'", "___").'.html">'.str_replace(' ', '&nbsp;', $oldgame['name']).'</a>'; ?>
		</td>
		<td style="font-size: 8pt;"><?php echo $result.$emps_left; ?></td>
		<td style="font-size: 8pt;"><?php echo $emps_nuked; ?></td>
		<td class=center><?php echo date('H:i, M j Y', strtotime($oldgame['end_date'])); ?></td>
		<td class=center><?php echo ($oldgame['bridier'] == 'yes' ? 'Bridier game' : ''); ?></td>
	</tr>
<?php
				}
			}
?>
	<tr>
		<td colspan=2 style="padding-bottom: 10pt; border-bottom: 1pt dashed white;">
<?php
		if ($vars['first_record'] != 0)
			echo '<input type=submit name=action value="First Page"><input type=submit name=action value="Previous Page">';
		else
			echo '&nbsp;';
?>
		<td colspan=3 style="padding-bottom: 10pt; text-align: right; border-bottom: 1pt dashed white;">
<?php
		if ($n > $server['histories_per_page'])
			echo '<input type=submit name=action value="Next Page"><input type=submit name=action value="Last Page">';
		else
			echo '&nbsp;';
?>
		</td>
	</tr>
</table>
</div>

<div class=tableTitle style="margin-top: 10pt;">History Search</div>
<table cellspacing=6 style="margin-left: auto; margin-right: auto;">
	<tr>
		<td>Game Name:</td>
		<td colspan=2>Empire Name:</td>
	</tr>
	<tr>
		<td><input type=text name=gamename value="<?php echo (array_key_exists('gamename', $vars) && !is_null($vars['gamename'])) ? stripslashes($vars['gamename']) : ''; ?>" size=25 maxlength=30></td>
		<td colspan=2><input type=text name=empirename value="<?php echo (array_key_exists('empirename', $vars) && !is_null($vars['empirename'])) ? stripslashes($vars['empirename']) : ''; ?>" size=20 maxlength=20></td>
	</tr>
	<tr>
		<td><input type=checkbox name=bridier_only <?php echo ((array_key_exists('bridier_only', $vars) && $vars['bridier_only']) ? 'checked' : ''); ?>>Bridier games only</td>
		<td>
			<input type=radio name=result value="Win"<?php echo ($vars['result'] == 'Win' ? ' checked' : ''); ?>>Games won
			<input type=radio name=result value="Lose"<?php echo ($vars['result'] == 'Lose' ? ' checked' : ''); ?>>Games lost
			<input type=radio name=result value="Draw"<?php echo ($vars['result'] == 'Draw' ? ' checked' : ''); ?>>Draws
			<!-- <input type=radio name=result value='Abandon'<?php echo ($vars['result'] == 'Abandon' ? ' checked' : ''); ?>>Games abandoned -->
			<input type=radio name=result value="All"<?php echo ($vars['result'] == 'All' ? ' checked' : ''); ?>>All games
		</td>
		<td><input type=submit name=action value="Search"></td>
	</tr>
</table>
</div>
<?php
		}

	footer();
}

#-----------------------------------------------------------------------------------------------------------------------------------------#

function gameHistory_processing($vars)
{
	global $server;

	$vars['empire_data'] = $empire = getEmpire($vars['name']);
	
	// The first record to display. If we are initiating a search, we default to the first record.
	$vars['first_record'] = ($vars['action'] == 'Search' ? 0 : $vars['first_record']);
	
	$conditions = array();

	if ($vars['gamename'])		$conditions[] = 'name REGEXP "'.$vars['gamename'].'"';
	if (array_key_exists('bridier_only', $vars) && $vars['bridier_only'])	$conditions[] = 'bridier = "yes"';
	
	// If the empire name was specified, we add conditions for game results.
	if ($vars['empirename'])
		{
		switch ($vars['result'])
			{
			case 'Draw':
				$conditions[] = 'result = "draw"';
				$conditions[] = 'emps_left REGEXP "='.$vars['empirename'].'="';
				break;
			case 'Win':
				$conditions[] = 'emps_left REGEXP "='.$vars['empirename'].'="';
				break;
			case 'Lose':
				$conditions[] = 'emps_nuked REGEXP "='.$vars['empirename'].'="';
				break;
			/*case 'Abandon':
				$conditions[] = 'emps_left REGEXP "='.$vars['empirename'].'="';
				$conditions[] = 'result = "abandoned"';
				break;*/
			case 'All':
				$conditions[] = '(CONCAT(emps_left, emps_nuked) REGEXP "='.$vars['empirename'].'=")';
				break;
			}
		}
	else if ($vars['result'] == 'Draw')
		$conditions[] = 'result = "draw"';
	
	switch ($vars['action'])
		{
		case 'First Page':
			$vars['first_record'] = 0;
			break;
		case 'Previous Page':
			if ($vars['first_record'] == -1)
				{
				$select = sc_query('SELECT COUNT(id) as c FROM gamelog '.$conditions);
				$line = $select->fetch_assoc();
				$recorded_games = $line['c'];
				$last_page_count = ($recorded_games % $server['histories_per_page']);
				$vars['first_record'] = $recorded_games-$last_page_count-$server['histories_per_page']-1;
				}
			else
				$vars['first_record'] = max(0, $vars['first_record']-$server['histories_per_page']-1);
			break;
		case 'Next Page':
			$vars['first_record'] = $vars['first_record']+$server['histories_per_page']+1;
			break;
		case 'Last Page':
			$vars['first_record'] = -1;
			break;
		}
	
	$vars['conditions'] = $conditions;
			
	gameHistory($vars);
}
?>
