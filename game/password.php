<?php
function passwordScreen($vars)
{
	$series = $vars['series_data'];
	$game = $vars['game_data'];
	
	gameHeader($vars, 'Set Passwords');
	
	if ($series['team_game'])
		{
?>
<div style="text-align: center;">
<table style="margin-left: auto; margin-right: auto; margin-bottom: 10pt;">
	<tr>
		<th style="background-color: #006; color: white;">Your Team's Password</th>
		<th style="background-color: #600; color: white;">Other Team's Password</th>
	</tr>
	<tr>
		<th style="background-color: #006;"><input type=text name=password1 size=10 maxlength=10 value="<?php echo $game['password1']; ?>"></th>
		<th style="background-color: #600;"><input type=text name=password2 size=10 maxlength=10 value="<?php echo $game['password2']; ?>"></th>
	</tr>
</table>
<input type=submit name=set value=Set>&nbsp;<input type=submit name=cancel value=Cancel>
</div>
<?php
		}
	else
		{
?>
<div style="text-align: center;">
<table style="margin-left: auto; margin-right: auto; margin-bottom: 10pt;">
	<tr>
		<th>Game Password</th>
	</tr>
	<tr>
		<th><input type=text name=password1 size=10 maxlength=10 value="<?php echo $game['password1']; ?>"></th>
	</tr>
</table>
<input type=submit name=set value=Set>&nbsp;<input type=submit name=cancel value=Cancel>
</div>
<?php
		}
	
	footer();
}

#
#-----------------------------------------------------------------------------------------------------------------------------------------#
#

function passwordScreen_processing(&$vars)
{
	$player = $vars['player_data'];
	$game = $vars['game_data'];
	
	if ($vars['cancel']) 
		return sendGameMessage($player, 'Password '.($game['password1'] ? 'change' : 'set').' cancelled.');
	
	if ($vars['set'] == 'Set')
		{
		if ($series['team_game'])
			{
			if ($vars['password1'] == '' and $vars['password2'] == '')
				return sendGameMessage($player, 'No passwords entered. Action cancelled.');
			else if ($vars['password1'] == '' or $vars['password2'] == '')
				{
				$vars['gameAction'] = 'Set Passwords';
				
				if ($game['password1'])
					sendGameMessage($player, 'You cannot remove passwords.');
				else
					sendGameMessage($player, 'You must set both passwords or none.');
					
				return false;
				}
			else if ($vars['password1'] == $vars['password2'])
				return sendGameMessage($player, 'Passwords must be different.');
					
			sc_mysql_query('UPDATE games SET password1 ="'.$vars['password1'].'", password2 ="'.$vars['password2'].'", last_update = '.time().' WHERE id = '.$game['id']);
			
			return sendGameMessage($player, 'Passwords '.($game['password1'] ? 'changed' : 'set').'.');
			}
		else
			{
			if ($game['password1'] and $vars['password1'] == '')
				return sendGameMessage($player, 'You cannot clear a set game password.');
			else
				{
				$now = time();
				
				// We also reset the update time so that the kill game timeout doesn't hit too soon.
				sc_mysql_query('UPDATE games SET password1 = "'.$vars['password1'].'", last_update = '.$now.' WHERE id = '.$game['id']);
				return sendGameMessage($player, 'Password changed.');
	    		}
			}
		}
}
?>