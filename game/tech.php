<?php
function techScreen($vars)
{
	global $ship_types;

	$techs_waiting = techsWaiting($vars);
	$tech_level = number_format($vars['player_data']['tech_level'], 4, '.', ' ');
	$current_br = ($vars['player_data']['tech_level'] > 0.0 ? floor(sqrt($vars['player_data']['tech_level'])) : 0);

	gameHeader($vars, 'Tech');
?>
<div class=messageBold>
	You have <?php echo $techs_waiting; ?> developments waiting. Tech Level <?php echo $tech_level; ?> (BR <?php echo $current_br; ?>)
</div>

<div style="margin-top: 10pt;">
<table style="margin-left: auto; margin-right: auto;">
	<tr>
		<th style="vertical-align: top; text-align: right;">Developed:</th>
		<td><?php echo $vars['player_data']['techs']; ?></td>
	</tr>
	<tr>
		<th style="vertical-align: top; text-align: right;">Undeveloped:</th>
		<td>
<?php
	$x = 1;
	foreach (array_keys($ship_types) as $type)
		{
		if (!in_array($type, explode(' ', $vars['player_data']['techs'])))
			{
			if (!($x++ % 5)) echo '<br>';

			// We'll show disabled buttons if the player can't develop anything.
			if ($techs_waiting > 0)	echo '<input type=submit name="newTech" value="'.$type.'">';
			else echo '<input type=button disabled value="'.$type.'">';
			}
		}
?>
		</td>
	</tr>
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
	
	if (isset($vars['newTech']))
		{
		if (!techsWaiting($vars))
			return sendGameMessage($player, 'You have no tech developments left.');
		else if (!in_array($vars['newTech'], array_keys($ship_types)) or in_array($vars['newTech'], explode(' ', $player['techs'])))
			return sendGameMessage($player, 'Invalid tech choice.');
		else
			{
			sc_query('UPDATE players SET techs = CONCAT(techs, " '.$vars['newTech'].'") WHERE id = '.$player['id']);
			return sendGameMessage($player, 'You have developed '.$vars['newTech'].'.');
			}
		}

	return false;
}
?>