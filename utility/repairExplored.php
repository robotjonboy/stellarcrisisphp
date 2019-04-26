<?
require('server.php');

$select = mysql_query('SELECT game_id, empire, coordinates, COUNT(from_shared_hq) AS dupes FROM explored WHERE from_shared_hq > 0 GROUP  BY game_id, empire, coordinates
HAVING dupes >= 2');
while ($row = mysql_fetch_array($select))
	{
	echo $query = 'DELETE FROM explored WHERE game_id = '.$row['game_id'].' AND empire = "'.$row['empire'].'" AND coordinates = "'.$row['coordinates'].'" AND from_shared_hq > 0 LIMIT 1';
	echo "\n";
	#mysql_query($query) or die( mysql_error() );
	}
?>
