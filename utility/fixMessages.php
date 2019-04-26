<?
require('server.php');

mysql_query('BEGIN');

$select = mysql_query('SELECT id, text FROM messages');
while ($row = mysql_fetch_array($select))
	{
	$message = addslashes(urldecode(stripslashes($row['text'])));

	mysql_query('UPDATE messages SET text = "'.$message.'" WHERE id = '.$row['id']);
	}

mysql_query('COMMIT');
?>
