<?
function infoScreen($vars)
{
	$series = $vars['series_data'];
	$game = $vars['game_data'];
	$player = $vars['player_data'];
		
	// Determine how many players ended turn or not.
	$conditions = array();
	$conditions[] = 'game_id = '.((int)$game['id']);
	$conditions[] = 'ended_turn = "0"';
	$conditions[] = 'team >= 0';
	$select = sc_mysql_query('SELECT COUNT(*) FROM players WHERE '.implode(' AND ', $conditions));
	
	$not_ended_turn = mysql_result($select, 0, 0);
	$ended_turn = $game['player_count']-$not_ended_turn;

	$weekend = (ereg('Sat|Sun', date('D', time())));
    
    // If there is only one player in the game (or not full in a team game), the next update won't happen until someone else
	// joins. Otherwise, calculate when the next update will occur.
	if ($game['player_count'] == 1 or ($series['team_game'] == 1 and $game['closed'] == 0))
		{
		$last_update = '---';
		$next_update = 'Waiting for players.';
		}
	else if (inHolidayBreak())
		{
		$last_update = '---';
		$next_update = 'Game is on hold for the holidays.';
		}
	else if (!$game['weekend_updates'] and $weekend)
		{
		$last_update = '---';
		$next_update = 'Game is on hold for the weekend.';
		}
	else if ($game['on_hold'] and $game['closed'] == 0)
		{
		$last_update = '---';
		$next_update = 'Game is paused.';
		}
	else
		{
		$last_update = ($game['update_count'] > 0 ? secondsToString(time() - $game['last_update']).' ago' : 'none');
		$next_update = 'Update '.($game['update_count']+1).' in '.secondsToString($game['last_update']+$game['update_time']-time()).'.';
		}
		
	gameHeader($vars, 'Info');
?>
<table width="100%" style="text-align: left;">
	<tr>
		<th colspan=6 style="text-align: center;">
			<div class=tableTitle>Game Information</div>
			<div style="font-size: 8pt; margin-bottom: 10pt;">
				<a href="#" onClick="window.open('sc.php?seriesParameters=<? echo $series['id']; ?>','seriesParameters<? echo $series['id']; ?>','height=500,width=600,scrollbars=yes');return false;">Series parameters</a>
			</div>
		</th>
	</tr>
	<tr>
		<td align=right class=white>Update Count:</td>
		<td><? echo $game['update_count']; ?></td>
		<td align=right class=white>Last Update:</td>
		<td><? echo $last_update; ?></td>
		<td class=white style="text-align: center;" colspan=2><? echo $next_update; ?></td>
	</tr>
	<tr>
		<td style="text-align: center;" colspan=3>You are <? echo ($player['ended_turn'] ? '' : 'not '); ?>ready for an update</td>
		<td style="text-align: center;" colspan=3><? echo $ended_turn.' of '.$game['player_count']; ?> players are ready for an update</td>
	</tr>
	<tr><th colspan=6><div class=tableTitle>Empire Totals</div></tr>
	<tr>
		<td class=white>Minerals:</td><td><? echo $player['mineral']; ?></td>
		<td class=white>Fuel:</td><td><? echo $player['fuel']; ?></td>
		<td class=white>Agriculture:</td><td><? echo floor($player['agriculture']); ?></td>
	</tr>
	<tr>
		<td class=white>Population:</td><td><? echo floor($player['population']); ?></td>
		<td class=white>Target Population:</td><td><? echo $player['max_population']; ?></td>
		<td class=white>Tech Level:</td><td><? echo number_format($player['tech_level'], 5, '.', ''); ?></td>
	</tr>
	<tr>
		<td class=white>Economic Power:</td><td><? echo $player['economic_power']; ?></td>
		<td class=white>Military Power:</td><td><? echo $player['military_power']; ?></td>
		<td class=white>Tech Development:</td><td><? echo number_format($player['tech_development'], 5, '.', ''); ?></td>
	</tr>
	<tr><th colspan=6><div class=tableTitle>Ratios and Usage</div></th></tr>
	<tr>
		<td class=white>Maintenance Ratio:</td>
		<td><? echo ($player['mineral_ratio'] ? number_format($player['mineral_ratio'], 5, '.', '') : '--'); ?></td>
		<td class=white>Fuel Ratio:</td>
		<td><? echo ($player['fuel_ratio'] ? number_format($player['fuel_ratio'], 5, '.', '') : '--'); ?></td>
		<td class=white>Agriculture Ratio:</td>
		<td><? echo ($player['agriculture_ratio'] ? number_format($player['agriculture_ratio'], 5, '.', '') : '--'); ?></td>
	</tr>
	<tr>
		<td class=white>Total Build:</td><td><? echo $player['build']; ?></td>
		<td class=white>Total Maintenance:</td><td><? echo $player['maintenance']; ?></td>
		<td class=white>Total Fuel Use:</td><td><? echo $player['fuel_use']; ?></td>
	</tr>
</table>
<?
	footer();
}
?>