<?
require('server.php');

mysql_query('DELETE FROM words');

$words = fopen('/usr/share/dict/words', 'r');

while ($word = fgets($words))
	{
	$word = trim($word);
	$word = str_replace("\n", "", $word);
	
	if (strlen($word) > 3 and strlen($word) < 9)
		mysql_query('INSERT INTO words SET word = "'.$word.'"');
	}
?>
