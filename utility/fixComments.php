<?
require('../server.php');

$select = mysql_query('SELECT id, comment FROM empires WHERE comment <> ""');
while ($row = mysql_fetch_array($select))
	{
	$comment = str_replace('\\', '', $row['comment']);
	$comment = stripslashes($row['comment']);
	
	
	mysql_query('UPDATE empires SET comment = "'.addslashes(urldecode($comment)).'" WHERE id = '.$row['id']) or die( mysql_error() );
	}
?>