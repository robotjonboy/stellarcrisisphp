<?
require('server.php');

mysql_query('BEGIN');

$select = mysql_query('SELECT id, ships, comment FROM scouting_reports');
while ($row = mysql_fetch_array($select))
	{
	$ships = addslashes(urldecode(stripslashes($row['ships'])));
	$comment = addslashes(urldecode(stripslashes($row['comment'])));

	mysql_query('UPDATE scouting_reports SET ships = "'.$ships.'", comment = "'.$comment.'" WHERE id = '.$row['id']);
	}

mysql_query('COMMIT');
?>
