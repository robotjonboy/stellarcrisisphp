<?
// updates.php
#=----------------------------------------------------------------------------=#
require('../server.php');
require('../debug.php');
require('../sql.php');
#=----------------------------------------------------------------------------=#

// decode passed id into empire
$player = $_GET['id'];
$result = sc_query('SELECT * FROM empires');
while ($row = mysql_fetch_array($result))
{
	$code5=md5($row['id'].$row['name'].$row['join_date']);
	if ($code5 == $player)
	{
		$empire=$row['name'];
		break;
	}
}

// 
$fields = array();
$fields[] = 'series.*';
$fields[] = 'games.game_number';
$fields[] = 'games.created_at';
$fields[] = 'games.last_update';
$fields[] = 'update_count';
$fields[] = 'games.id AS game_id';
$fields[] = 'players.id AS player_id';
$fields[] = 'players.ended_turn';

$tables = 'series INNER JOIN games ON series.id = games.series_id '.
				 'INNER JOIN players ON games.id = players.game_id '.
				 'INNER JOIN empires ON players.name = empires.name';
$conditions = 'empires.name = "'.$empire.'" ';
$order = 'ORDER BY series.update_time, series.name, games.game_number ASC';

$sql=	'SELECT '.implode(',', $fields).
		' FROM '.$tables.
		' WHERE '.$conditions.
		' '.$order;
$result = sc_query($sql);

header('Content-Type: application/xml'."\n\n");
echo '<?xml version="1.0" encoding="ISO-8859-1" ?>';
echo '<rss version="2.0">';
echo 	'<channel>';
echo		'<title>';
echo			'Stellar Crisis: ';
echo			$server['servername'];
echo			' - Game Updates';
echo		'</title>';
echo		'<link>';
echo			'http://'.$server['domainname'].'/';
echo		'</link>';
echo		'<description>';
echo			'The webs first complete multi-player strategy game.';
echo		'</description>';

$count_all = mysql_num_rows($result);
$count_updated = 0;
if ( $count_all > 0 )
{
	while ($row = mysql_fetch_array($result))
		if ( $row['ended_turn'] == 0 AND $row['update_count'] > 0) 
			$count_updated++; // = $count_updated + 1;
	mysql_data_seek($result,0); //rewind query
}

	$first=0;
	while ($row = mysql_fetch_array($result))
	{
		if ( $first == 0 )
		{
			$first++;
			echo '<item>';
			echo '<title> ';
			echo 	$count_updated.' of '.$count_all.' games have updated.';
			echo '</title>';
			echo '<pubDate>'.date('r', time()).'</pubDate>';
			echo '</item>';
		}
		if ( $row['ended_turn'] == 0 AND $row['update_count'] > 0)
		{
			echo '<item>';
			echo '<title> '.$row['name'].' </title>';
			echo '<link> http://homeserve.org/sc/sc.php </link>';
			echo '<pubDate>'.date('r', time()).'</pubDate>';
			echo '<description>';
			echo 	$empire.', you are not ready.';
			echo '</description>';
			echo '</item>';
		}
	}
	echo '</channel>';
	echo '</rss>';
?>
