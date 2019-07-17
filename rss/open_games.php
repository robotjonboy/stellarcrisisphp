<?php
#=----------------------------------------------------------------------------=#
require('../server.php');
require('../debug.php');
require('../sql.php');
#=----------------------------------------------------------------------------=#

function description($data)
{
	switch ($data['map_type'])
		{
		case 'standard':
			$map_text = 'Classic SC map. ';
			break;
		case 'prebuilt':
			$map_text = 'Random placement map (first player can be on the edge). ';
			break;
		case 'twisted':
			$map_text = 'Twisted map. ';
			break;
		case 'mirror':
			$map_text = 'Mirror map. ';
			break;
		case 'balanced':
			$map_text = 'Balanced map. ';
			break;
		default:
			$map_text = '';
			break;
		}


	if ($data['avg_min'] != $data['avg_fuel'] or $data['avg_min'] != $data['avg_ag'])
		$average_resources = $data['avg_min'].'/'.$data['avg_fuel'].'/'.$data['avg_ag'].' average minerals/fuel/agriculture';
	else
		$average_resources = $data['average_resources'].' average resources';

	$description = (($data['bridier'] < 0 or $data['password1']) ? 
					'Started by '.$data['created_by'].'. ' : 
					'').
				   ($data['team_game'] ? 'Team game. ' : '').
				   ($data['diplomacy'] == 6 ? 'Shared HQ. ' : '').
				   'Updates every '.($data['update_time'] % 3600 == 0 ? 
					($data['update_time']/3600).' hours' : 
					($data['update_time']/60).' minutes').', '.
				   ($data['weekend_updates'] ? '' : 'no weekend updates, ').
				   $data['max_players'].' players, '.
				   $data['systems_per_player'].' systems per player. '.
				   $map_text.( !$data['map_visible'] ? 
				     'Map hidden until game starts. ' : '' ).
				   number_format($data['tech_multiple'], 2, '.', '').
				   ' tech, '.$average_resources.', ';
		
	if (!$data['min_wins'] and $data['max_wins'] == -1)
		$description .= 'anyone can join.';
    else if ($data['max_wins'] == -1)
    	$description .= $data['min_wins'].' or more wins needed to join.';
    else
    	$description .= 'from '.$data['min_wins'].' to '.$data['max_wins'].' wins needed to join.';
    	
   	$description .= ($data['build_cloakers_cloaked'] ? ' Cloakers built cloaked.' : '').
					($data['cloakers_as_attacks'] ? ' Cloakers appear as attacks.' : '');
					   	
   	switch ($data['diplomacy'])
		{
		case 2: $description .= ' Truce, trade and alliances not allowed.';	break;
		case 3: $description .= ' Trade and alliances not allowed.';		break;
		case 4: $description .= ' No alliances allowed.';					break;
		}

	$description .= ($data['password1'] ? ' Password required to join the game.' : '');
	
	return $description;
}

#=----------------------------------------------------------------------------=#

$fields = array();
$fields[] = 'series.*';
$fields[] = 'games.created_by';
$fields[] = 'games.created_at';
$fields[] = 'games.last_update';
$fields[] = 'games.game_number';
$fields[] = 'games.player_count';
$fields[] = 'games.bridier';
$fields[] = 'games.update_count';
$fields[] = 'games.password1';

$tables = 'series INNER JOIN games ON series.id = games.series_id';

$result = sc_query('SELECT '.implode(',', $fields).'  FROM '.$tables.' WHERE games.player_count > 0 AND games.closed = "0"');

#=----------------------------------------------------------------------------=#

header('Content-Type: application/xml'."\n\n");
echo '<?xml version="1.0" encoding="ISO-8859-1" ?>';
?>
<rss version="2.0">
	<channel>
		<title>Stellar Crisis: <?php echo $server['servername'] ?> - Open games</title>
		<link>http://<?php $server['domainname'] ?>/</link>
		<description>The web's first complete multi-player strategy game.</description>
<?php
while ($row = $result->fetch_assoc())
	{
	$title = $row['name'].' '.$row['game_number'].' - '.
			 ($row['update_count'] ? $row['update_count'].' update'.($row['update_count'] > 1 ? 's' : '') : 'new game').
			 ' ('.$row['player_count'].' / '.$row['max_players'].' players)';
?>
		<item>
			<title><?= $title; ?></title>
			<link>http://<?php $server['domainname'] ?>/</link>
			<pubDate><?= date('r', ($row['last_update'] ? $row['last_update'] : $row['created_at'])); ?></pubDate>
			<description><?= description($row); ?></description>
		</item>
<?php
	}
?>
	</channel>
</rss>