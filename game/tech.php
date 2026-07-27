<?php
function techScreen($vars)
{
	global $ship_types;

	$techs_waiting = techsWaiting($vars);
	$tech_level = number_format($vars['player_data']['tech_level'], 4, '.', ' ');
	$current_br = ($vars['player_data']['tech_level'] > 0.0 ? floor(sqrt($vars['player_data']['tech_level'])) : 0);
	$game = $vars['game_data'];
	$series = $vars['series_data'];
	$player = $vars['player_data'];
	$is_sc3 = ($game['game_type'] == 'sc3');

	gameHeader($vars, 'Tech');
?>
<div class=messageBold>
	You have <?php echo $techs_waiting; ?> developments waiting. Tech Level <?php echo $tech_level; ?> (BR <?php echo $current_br; ?>)
</div>

<div style="margin-top: 10pt;">
<table style="margin-left: auto; margin-right: auto;">
	<tr>
		<th style="vertical-align: top; text-align: right;">Developed:</th>
		<td><?php echo htmlspecialchars($player['techs']); ?></td>
	</tr>
<?php if ($is_sc3) {
	$max_r = (int)($series['max_restricted_techs'] ?? 0);
	$max_t = (int)($series['max_trade_ins'] ?? 0);
	$have_r = (int)($player['restricted_techs'] ?? 0);
	$have_t = (int)($player['traded_in'] ?? 0);
	if ($max_r > 0) {
		echo '<tr><th style="text-align: right;">Restricted techs:</th><td>'.$have_r.' of '.$max_r.'</td></tr>';
	}
	if ($max_t > 0) {
		echo '<tr><th style="text-align: right;">Trade-ins used:</th><td>'.$have_t.' of '.$max_t.'</td></tr>';
	}
} ?>
	<tr>
		<th style="vertical-align: top; text-align: right;">Undeveloped:</th>
		<td>
<?php
	$unrestricted = array();
	$restricted = array();
	if ($is_sc3 && !empty($game['ship_type_options']))
	{
		foreach (getEffectiveShipTypeNames('sc3') as $type)
		{
			if (in_array($type, explode(' ', $player['techs']), true))
				continue;
			$status = sc3GetShipTypeStatus($game, $type);
			if ($status === 'Unrestricted')
				$unrestricted[] = $type;
			else if ($status === 'Restricted' && $have_r < $max_r)
				$restricted[] = $type;
		}
		$x = 1;
		foreach ($unrestricted as $type)
		{
			if (!($x++ % 5)) echo '<br>';
			if ($techs_waiting > 0)
				echo '<input type=submit name="newTech" value="'.htmlspecialchars($type).'">';
			else
				echo '<input type=button disabled value="'.htmlspecialchars($type).'">';
		}
		if (!count($unrestricted))
			echo '&nbsp;';
	}
	else
	{
		$x = 1;
		foreach (array_keys($ship_types) as $type)
		{
			if (!in_array($type, explode(' ', $player['techs'])))
			{
				if (!($x++ % 5)) echo '<br>';
				if ($techs_waiting > 0)	echo '<input type=submit name="newTech" value="'.$type.'">';
				else echo '<input type=button disabled value="'.$type.'">';
			}
		}
	}
?>
		</td>
	</tr>
<?php
	if ($is_sc3 && !empty($game['ship_type_options']))
	{
		echo '<tr><th style="vertical-align: top; text-align: right;">Restricted Undeveloped:</th><td>';
		$x = 1;
		foreach ($restricted as $type)
		{
			if (!($x++ % 5)) echo '<br>';
			if ($techs_waiting > 0)
				echo '<input type=submit name="newTech" value="'.htmlspecialchars($type).'">';
			else
				echo '<input type=button disabled value="'.htmlspecialchars($type).'">';
		}
		if (!count($restricted))
			echo '&nbsp;';
		echo '</td></tr>';
	}

	if ($is_sc3 && $max_t > $have_t)
	{
		$owned = explode(' ', trim($player['techs']));
		$tradeable = array();
		foreach ($owned as $type)
		{
			if ($type === '') continue;
			if (sc3GetShipTypeStatus($game, $type) === 'Restricted')
				$tradeable[] = $type;
		}
		if (count($tradeable))
		{
			echo '<tr><th style="vertical-align: top; text-align: right;">Trade-in:</th><td>';
			foreach ($tradeable as $type)
				echo '<input type=submit name="tradeIn" value="'.htmlspecialchars($type).'"> ';
			echo '<div style="font-size: smaller;">Forgets a restricted tech (does not refund a selection).</div></td></tr>';
		}
	}
?>
</table>
</div>
<?php
	footer();
}

#----------------------------------------------------------------------------------------------------------------------#

function techScreen_processing($vars)
{
	global $ship_types;

	$player = $vars['player_data'];
	$game = $vars['game_data'];
	$series = $vars['series_data'];

	if (isset($vars['tradeIn']) && $game['game_type'] == 'sc3')
	{
		$tech = $vars['tradeIn'];
		$max_t = (int)($series['max_trade_ins'] ?? 0);
		$have_t = (int)($player['traded_in'] ?? 0);
		$owned = explode(' ', trim($player['techs']));
		if ($have_t >= $max_t)
			return sendGameMessage($player, 'You have no trade-ins remaining.');
		if (!in_array($tech, $owned, true))
			return sendGameMessage($player, 'You do not have that tech.');
		if (sc3GetShipTypeStatus($game, $tech) !== 'Restricted')
			return sendGameMessage($player, 'Only restricted techs can be traded in.');

		$owned = array_values(array_filter($owned, function ($t) use ($tech) { return $t !== $tech; }));
		$new_techs = implode(' ', $owned);
		$new_r = max(0, ((int)($player['restricted_techs'] ?? 0)) - 1);
		sc_query('UPDATE players SET techs = "'.addslashes($new_techs).'", traded_in = traded_in + 1, restricted_techs = '.$new_r.' WHERE id = '.$player['id']);
		return sendGameMessage($player, 'You have forgotten '.$tech.'.');
	}

	if (isset($vars['newTech']))
	{
		if (!techsWaiting($vars))
			return sendGameMessage($player, 'You have no tech developments left.');
		else if (!validTechChoice($vars))
			return sendGameMessage($player, 'Invalid tech choice.');
		else
		{
			$status = ($game['game_type'] == 'sc3') ? sc3GetShipTypeStatus($game, $vars['newTech']) : 'Unrestricted';
			$extra = '';
			if ($status === 'Restricted')
				$extra = ', restricted_techs = restricted_techs + 1';
			sc_query('UPDATE players SET techs = CONCAT(techs, " '.$vars['newTech'].'")'.$extra.' WHERE id = '.$player['id']);
			return sendGameMessage($player, 'You have developed '.$vars['newTech'].'.');
		}
	}

	return false;
}

function validTechChoice($vars)
{
	global $ship_types;

	$player = $vars['player_data'];
	$game = $vars['game_data'];
	$series = $vars['series_data'];

	if ($game['game_type'] == 'sc3' && !empty($game['ship_type_options']))
		return sc3ValidTechChoice($series, $game, $player, $vars['newTech']);

	return (in_array($vars['newTech'], array_keys($ship_types)) && !in_array($vars['newTech'], explode(' ', $player['techs'])));
}
?>
