<?
function editMessage($vars)
{
	$empire = $vars['empire_data'];
	standardHeader('Edit Message', $empire);
	
	$select = sc_mysql_query('SELECT text FROM messages WHERE type = "'.$vars['message_type'].'" LIMIT 1');
	$row = mysql_fetch_array($select);
	
	switch ($vars['message_type'])
		{
		case 'motd':
			$caption = 'This message will be displayed on the login page.';
			break;
		case 'privacy':
			$caption = '?';
			break;
		case 'policy':
			$caption = '?';
			break;
		case 'tos':
			$caption = 'This message is displayed if users are required to accept the terms of service.';
			break;
		case 'news':
			$caption = '?';
			break;
		}
		
	$message .= '<div style="font-size: smaller;">It should be entered in HTML format.</div>';
?>
<div class=pageTitle>Message of the day</div>
<div>
<input type=hidden name="name" value="<? echo $vars['name']; ?>">
<input type=hidden name="pass" value="<? echo $vars['pass']; ?>">
<input type=hidden name="message_type" value="<? echo $type; ?>">
<input type=hidden name="section" value="admin">
<input type=hidden name="page" value="editMessage">
<input type=hidden name="message_type" value="<? echo $vars['message_type']; ?>">
<?
	echo drawButtons($empire).serverTime().onlinePlayers().empireMissive($empire);
?>
<img class=spacerule src="images/spacerule.jpg">
<div class=messageBold><? echo $caption; ?></div>
<div style="text-align: center; margin-top: 10pt;">
	<textarea name="message" cols=100 rows=20><? echo stripslashes($row['text']); ?></textarea>
</div>
<div style="text-align: center; margin-top: 10pt;"><input type=submit name="save" value="Save"></div>
<?
	footer();
}

#----------------------------------------------------------------------------------------------------------------------#

function editMessage_processing($vars)
{
	sc_mysql_query('DELETE FROM messages WHERE type = "'.$vars['message_type'].'"');
	sc_mysql_query('INSERT INTO messages SET type = "'.$vars['message_type'].'", text = "'.addslashes($vars['message']).'"');

	sendEmpireMessage($vars['empire_data'], 'Message updated.');
	return mainPage_admin($vars);
}
?>