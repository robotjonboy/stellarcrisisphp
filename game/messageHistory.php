<?php
function messageHistory($vars)
{
	$series = $vars['series_data'];
	$game = $vars['game_data'];
	$player = $vars['player_data'];
	$filter = (((array_key_exists('filterHistory', $vars) && $vars['filterHistory']) or (array_key_exists('deleteMessages', $vars) && $vars['deleteMessages'])) ? 
					$vars['message_history_filter'] : 
					'');

	gameHeader($vars, 'Message History');

// prepare the drop down box	
	$options = '';
	$total_messages = 0;
	# debase types are instant, motd, private, broadcast, team, update, scout
	# assume motd = message of the day
	$type_descriptor = array('broadcast' => 'Broadcasts', 
							'private' => 'Private messages',
							'team' => 'Team broadcasts', 
							'scout' => 'Scouting reports', 
							'' => 'Instant',
							'update' => 'Updates');
	$select = sc_query('SELECT type, COUNT(id) as c '.
							'FROM messages '.
							'WHERE player_id = '.$player['id'].' '.
							'GROUP BY type');
	while ($row = $select->fetch_assoc())
	{
		$selected = false;  //adjusted if to trap blank row tiypes
		if ($filter <> '' and $row['type'] == $filter) $selected = true; 
		if ($filter == '' and $row['type'] == 'broadcast') $selected = true;

		$options .= '<option value="'.
					($row['type'] == '' ? 'Instant' : $row['type']).'"'.
						($selected ? ' selected' : '').
					'>'.
					$type_descriptor[$row['type']].
					' ('.$row['c'].')';

		$total_messages += $row['c'];
	}

	if ($select->num_rows)
	{
?>
<div class=messageBold>
Choose the category of messages you wish to display.
</div>
<div style="text-align: center; margin-top: 10pt;">
	<select name="message_history_filter">
		<?php echo $options; ?>
		<option value="all"<?php echo ($filter == 'all' ? ' selected' : ''); ?>>
				All (<?php echo $total_messages; ?>)
	</select>&nbsp;
	<input	type='checkbox' 
		name=reverse_order<?php echo (array_key_exists('reverse_order', $vars) && $vars['reverse_order'] ? ' checked' : ''); ?>>
		Reverse order&nbsp;
   <input type=submit name=filterHistory value="Go">
</div>
<?php
	}
	else
	{
		echo '<div class=messageBold>There are no archived messages.</div>';
	}
	
	// set default display filter
	echo "<center>".(array_key_exists('message_history_filter', $vars) ? $vars['message_history_filter'] : '')."</center>";
	if (!array_key_exists('message_history_filter', $vars) || $vars['message_history_filter'] == "" )
		$filter="broadcast";

	$conditions = array();
	$conditions[] = 'player_id = '.$player['id'];
	// no type condition for "all" otherwise match filter
	if ($filter == 'Instant') $conditions[] = 'type = ""';
	else if ($filter != 'all') $conditions[] = 'type = "'.$filter.'"';

	$order = (array_key_exists('reverse_order', $vars) && $vars['reverse_order'] ? 'ASC' : 'DESC');

	$select = sc_query('SELECT * 
								FROM messages 
								WHERE '.implode(' AND ', $conditions).
								' ORDER BY id '.$order);

	if ($select->num_rows)
		{
		$tmp_missives = array();
		while ($row = $select->fetch_assoc())
			{
			// The message header is handled in messageHeader(), but first we 
			// adjust the sender and recipient if the player's name is in them.
			$row['recipient'] = array_key_exists('recipient', $row) && isset($row['recipient']) ? str_replace($player['name'], 'you', $row['recipient']) : '';
			
			if ($row['sender'] == $player['name']) $row['sender'] = 'You';

			$tmp_missives[] = '<input type=checkbox '.
								'name="deleteMessage[]" '.
								'value='.$row['id'].'>'.
								'&nbsp;'.
							  messageHeader($row).
							  wordwrap(stripslashes(urldecode($row['text'])), 90).
							  '</div>';
			}
?>
<div>
<table style="margin-left: auto; margin-right: auto; margin-top: 10pt;">
	<tr>
		<td align=left>
<?php		
		echo implode("<br>\n", $tmp_missives);
?>
		</td>
	</tr>
</table>
</div>

<div style="text-align: center; margin-top: 10pt;">
	<input type='hidden' 
			name='checkOrUncheck' 
			value="check">
	<input type='button' 
			class='submit' 
			name='checkAllButton' 
			onClick="checkAll()" 
			value="Check/Uncheck All">
	<input type='submit' 
			name='deleteMessages' 
			value="Delete selected messages">
</div>
<?php
		}
	#else if ($filter != '')
	#	echo '<div align=center>There are no archived messages of this type ('.$filter.').</div>';
	
	footer();
}

#----------------------------------------------------------------------------------------------------------------------#

function messageHistory_processing($vars)
{
	global $mysqli;
	
	$player = $vars['player_data'];

	if (isset($vars['deleteMessages']))
		{
		if (count($vars['deleteMessage']))
			{
			$affected_rows = 0;
			
			// We include the player ID in this query to make sure people don't go around 
			// deleting messages that don't belong to them.
			foreach ($vars['deleteMessage'] as $message_id)
				{
				sc_query('DELETE 
								FROM messages 
								WHERE id = '.$message_id.' 
								AND player_id = '.$player['id']);
				$affected_rows += $mysqli->affected_rows;
				}
				
			return sendGameMessage($player, $affected_rows.' message'.($affected_rows != 1 ? 's' : '').' deleted.');
			}
		else
			return sendGameMessage($player, 'No messages selected.');
		}
	else if (isset($vars['filterHistory']))
		return true;

	return false;
}
?>
