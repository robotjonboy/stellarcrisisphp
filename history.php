<?php
#----------------------------------------------------------------------------------------------------------------------#
# Dumps accumulated history to the history table.
#

function write_history($game, $history)
{
	$series = getSeries($game['series_id']);
	
	$values = array();
	$values[0] = 'game_id = '.$game['id'];
	$values[1] = 'update_no = '.$game['update_count'];
	
	foreach ($history as $event)
		{
		$values[2] = 'coordinates = "'.$event[0].'"';
		$values[3] = 'empire = "'.$event[1].'"';
		$values[4] = 'event = "'.$event[2].'"';
		$values[5] = 'info = "'.$event[3].'"';
				
		sc_mysql_query('INSERT INTO history SET '.implode(',', $values));
		}
}

#----------------------------------------------------------------------------------------------------------------------#

function endGame($series, $game, $history, $saved_map)
{
	global $server;
	
	// Flush pending history.
	write_history($game, $history);

	// Initialize ownership array for the movie.
	$owner_history = array();
	$select = sc_mysql_query('SELECT coordinates FROM systems WHERE game_id = '.$game['id']);
	while ($system = mysql_fetch_array($select))
		{
		$owner_history[$system['coordinates']] = array();
		$owner_history[$system['coordinates']][0] = 0;
		}

	$icon_list = array();
	$icon_list[0] = 'planet.gif';
	$icon_list[1] = 'annihilated.gif';
	$icon_list[2] = 'nukedani.gif'; // nuke animation
	$icon_list[3] = 'anilani.gif'; // annihilation animation
	$icon_list[4] = 'planet.gif'; // save for invasion animation

	$player_list = array();
	$player_list['=annihilated='] = 1;
	$player_list['=unowned='] = 0;
	
	$n_icons = 5;
	
	$first_update = 0;
	$update = 0;
	$bridier_string = '';
	$nuked_out = array();
	$draw = array();
	$won = array();	
	$all_players = array();
	$side = array();
	$complete_game = false;
	
	$title_string = 'Game History for '.$series['name'].' '.$game['game_number'];

	ob_start();

	$game_history = sc_mysql_query('SELECT * from history WHERE game_id = '.$game['id'].' ORDER BY id');
	while ($event = mysql_fetch_array($game_history))
		{
		// This is set up as a while loop to catch up over gaps in the history data.
		// Gaps should only occur for games started before history was implemented so
		// later this can be changed to an if.
		while ($event['update_no'] != $update)
			{
			$update += 1;
			
			foreach (array_keys($owner_history) as $location)
				{
				$previous_owner = $owner_history[$location][$update-1];
				
				// System nuked last turn.
				if ($previous_owner == 2)
					$owner_history[$location][$update] = 0;

				// System annihilated last turn.
				else if ($previous_owner == 3)
					$owner_history[$location][$update] = 1;
				
				// Owner nuked out last turn.
				else if (in_array($previous_owner, $nuked_out))
					$owner_history[$location][$update] = 0;
				
				// Nothing happened!
				else
					$owner_history[$location][$update] = $previous_owner;
				}
			
			// Reset the nuked out array.
			$nuked_out = array();
			}
		
		switch ($event['event'])
			{
			case 'started':
				$complete_game = true;
				echo '<span class=updateHeader>Game started at '.date('D M j H:i:s T Y', $event['info']).'</span>';
				break;
			
			case 'update':
				// Save the update time. The last one to be saved will be the end time recorded in the gamelog table.
				$end_date = $event['info'];
				echo '<div class=updateHeader style="margin-top: 5pt;">Update '.$event['update_no'].' occurred at '.date('D M j H:i:s T Y', $event['info']).'</div>';
				break;
			
			case 'joined':
				echo $event['empire'].' joined the game';

				if ($event['coordinates'])
					$owner_history[$event['coordinates']][$update] = $player_list[$event['empire']];
				
				if ($event['info'])
					echo ' as part of '.$event['info'];
			
			// Special record for starting up the history process.
			case 'empire':
				$all_players[] = $event['empire'];
				$player_list[$event['empire']] = $n_icons;
				$empire = getEmpire($event['empire']);
				$icon_list[$n_icons] = 'aliens/'.$empire['icon'];
				$owner_history[$event['coordinates']][$update] = $n_icons;
				$n_icons += 1;
				break;

			case 'War':
			case 'Truce':
			case 'Trade':
			case 'Alliance':
			case 'Shared HQ':
				echo $event['empire'].' and '.$event['info'].' have declared '.$event['event'].'.';
				break;

			case 'nuked out':
			case 'invaded out':
			case 'annihilated out':
#				// set planets to unowned
#				foreach ( array_keys($owner_history) as $location )
#					if ( $owner_history[$location][$update] == $player_list[$event['empire']] )
#						$owner_history[$location][$update] = 0;
				echo $event['empire'].' was '.$event['event'].' of the game by '.$event['info'].'.';
				$nuked_out[] = $player_list[$event['empire']];
				break;
			
			case 'ruins':
#				foreach ( array_keys($owner_history) as $location )
#					if ( $owner_history[$location][$update] == $player_list[$event['empire']] )
#						$owner_history[$location][$update] = 0;
				echo $event['empire'].' fell into ruins.';
				$nuked_out[] = $player_list[$event['empire']];
				break;
			
			case 'surrender':
#				// set planets to unowned
#				foreach ( array_keys($owner_history) as $location )
#					if ( $owner_history[$location][$update] == $player_list[$event['empire']] )
#						$owner_history[$location][$update] = 0;
				echo $event['empire'].' surrendered.';
				$nuked_out[] = $player_list[$event['empire']];
				break;

			case 'ship to ship':
			case 'ship to system':
				echo '<span class=updateFirstContact>'.$event['empire'].' met '.$event['info'].' '.
					 $event['event'].' at ('.$event['coordinates'].').</span>';
				break;
			
			case 'sighted':
			case 'destroyed':
				echo '<span class=updateDestroy>'.$event['info'].' of '.$event['empire'].' '.
					 $event['event'].' at ('.$event['coordinates'].').</span>';
				break;

			case 'minefield':
				echo '<span class=updateNuke>A minefield exploded at ('.$event['coordinates'].').</span>';
				break;

			case 'nuked':
				$owner_history[$event['coordinates']][$update] = 2;
				echo '<span class=updateNuke>('.$event['coordinates'].') was '.
					 $event['event'].' by '.($event['info'] ? $event['info'].' of ' : '').$event['empire'].'.</span>';
				break;

			case 'annihilated':
				$owner_history[$event['coordinates']][$update] = 3;
				echo '<span class=updateNuke>('.$event['coordinates'].') was '.
					 $event['event'].' by '.($event['info'] ? $event['info'].' of ' : '').$event['empire'].'.</span>';
				break;

			case 'invaded':
				$owner_history[$event['coordinates']][$update] = $player_list[$event['empire']];
				echo '<span class=updateInvade>('.$event['coordinates'].') was '.
					 $event['event'].' by '.($event['info'] ? $event['info'].' of ' : '').$event['empire'].'.</span>';
				break;

			case 'colonized':
				$owner_history[$event['coordinates']][$update] = $player_list[$event['empire']];
			case 'terraformed':
				echo '<span class=updateTerraform>('.$event['coordinates'].') was '.
					 $event['event'].' by '.($event['info'] ? $event['info'].' of ' : '').$event['empire'].'.</span>';
				break;

			case 'unsuccesfully invaded':
				echo '<span class=updateInvade>'.$event['empire']."'s invasion of (".$event['coordinates'].') failed.</span>';
				break;

			case 'opened':
			case 'closed':
				list($target, $shipname, $consumed) = explode('/', $event['info']);
				echo '<span class=updateEngineer>'.($shipname ? $shipname : 'An engineer').' of '.$event['empire'].' '.
					 $event['event'].' the jump from ('.$event['coordinates'].') to ('.$target.
					 ($consumed == 'yes' ? ') and was consumed in the process' : ')').'.</span>';
				break;

			case 'draw':
				$draw[] = $event['empire'];
				
				// Pick a side for friendly/enemy ships display.
				// We can only have draws for grudges and team games. In a grudge, either player can be the
				// friendly. In grudges, just use the first player with a draw record
				if ($series['team_game'])
					{
					if ($event['info'] == "Team 1")
						$side[] = $event['empire'];
					}
				else if ( count($side) == 0 )
					$side[] = $event['empire'];
				break;

			case 'won':
				$won[] = $event['empire'];
				$side[] = $event['empire'];
				break;

			case 'bridier':
				list($rankdata, $indexdata) = explode('/', $event['info']);
				$bridier_string .= '<tr align=center><th rowspan=2>'.$event['empire'].'</td><td>Rank</td><td>'.
								   ereg_replace(',', '</td><td>', $rankdata).'</td></tr><tr align=center><td>Index</td><td>'.
								   ereg_replace(',', '</td><td>', $indexdata).'</td></tr>';
				break;
			default:
				echo 'Bad history record: '.implode('--',$event);
				break;
			}

		echo '<br>'."\n";
		}
	
	if (count($draw))
		echo '<div style="text-align: center; font-size: 12pt; font-weight: bold;">Game declared a draw by '.implode(', ', $draw).'</div>';
	
	if (count($won))
		echo '<div style="text-align: center; font-size: 12pt; font-weight: bold;">Game won by '.implode(', ',$won).'</div>';
	
	if ($bridier_string)
		{
?>
<div style="text-align: center;">
<table border=1 cellpadding=2 cellspacing=0>
	<tr><th colspan=6 style="background-color: #569; color: white;">Bridier Results</></th></tr>
	<tr>
		<td colspan=2>Empire</td>
		<td>Game Start</td>
		<td>Game End</td>
		<td>Adjustment</td>
		<td>New Value</td>
	</tr>
	<?php echo $bridier_string; ?>
</table></div>
<?php
		}

	$history_string = ob_get_contents();
	ob_end_clean();

	ob_start();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<title><?php echo $title_string; ?></title>
	<link rel="stylesheet" type="text/css" href="<?php echo $server['history_css_path'].'styles.css'; ?>">
	<script type="text/javascript" src="MapDraw.js"></script>
	<script>
		var universe = new Array();
		var nPlanets = 0;
		icons = new Array("<?php echo implode('","', $icon_list); ?>");
		owner_history = new Array();
		***HISTORY_JAVASCRIPT***
		var firstUpdate = <?php echo $first_update; ?>;
		var lastUpdate = <?php echo $update; ?>;
	</script>
</head>
<body>
<div style="text-align: center; font-size: 13pt;"><?php echo $title_string; ?></div>
<div style="text-align: center;">Stellar Crisis <?php echo $server['version'].' @ '.$server['servername']; ?></div>

<img class=spacerule src="<?php echo $server['history_image_path']; ?>spacerule.jpg">

<div style="text-align: center;">
<table style="margin-left: auto; margin-right: auto;">
	<tr>
		<td>
			<?php echo $history_string; ?>
<img class=spaceruleThin src="<?php echo $server['history_image_path']; ?>spacerule.jpg">
<div class=smallTitle>Game Map</div>
<div style="text-align: center; font-size: 8pt;">(<?php echo implode(', ', $side); ?> as friendly ships)</div>
<div style="text-align: center; margin: 10pt;"><img src="upno.gif"><img name="hundreds" src="blank.gif" width=16 height=24><img name="tens" src="blank.gif" width=16 height=24><img name="ones" src="blank.gif" width=16 height=24>

	<script>
		showUpdateNumber(lastUpdate);
		imagePath = "<?php echo $server['history_image_path']; ?>";
		drawMap();
		<?php echo ($complete_game ? 'if (document.images) drawMovieControl();' : ''); ?>
	</script>
</div>
</body>
</html>
<?php
	$buffer = ob_get_contents();
	ob_end_clean();

	ob_start();

	$select = sc_mysql_query('SELECT * FROM systems WHERE game_id = '.$game['id']);
	while ($system = mysql_fetch_array($select))
		{
		if (count($saved_map))
			{
			$mineral = $saved_map[$system['coordinates']]['mineral'];
			$fuel = $saved_map[$system['coordinates']]['fuel'];
			$agriculture = $saved_map[$system['coordinates']]['agriculture'];
			$population = $saved_map[$system['coordinates']]['population'];
			$friends = 0;
			$enemies = 0;
			
			if (array_key_exists('ships', $saved_map[$system['coordinates']]))
				{
				foreach (array_keys($saved_map[$system['coordinates']]['ships']) as $owner)
					{
					if (in_array($owner, $side))
						$friends += $saved_map[$system['coordinates']]['ships'][$owner];
					else
						$enemies += $saved_map[$system['coordinates']]['ships'][$owner];
					}
				}
			}
		else
			{
			$mineral = $system['mineral'];
			$fuel = $system['fuel'];
			$agriculture = $system['agriculture'];
			$population = $system['population'];
			$friends = 0;
			$enemies = 0;
			}
			
		// use icon from the saved map-- this makes sure the initial map is good even if history is corrupted
		$icon = $icon_list[  $player_list[ $saved_map[$system['coordinates']]['owner'] ]  ];

		echo 'universe[nPlanets] = new planet('.$system['coordinates'].', "'.$system['name'].'", "'.$icon.'", '.$mineral.', '.
												$fuel.', '.$agriculture.', '.$population.', '.$friends.', '.$enemies;
		
		if ($system['jumps'] == '')
			echo ', 0,0, 0,0, 0,0, 0,0';
		else
			{
			$jumps = explode(' ', trim($system['jumps']));
			for ($i = 0; $i < 4; $i++)
				{
				if ($i >= count($jumps))
					echo ', 0,0';
				else
					echo ', '.$jumps[$i];
				}
			}

		echo ');'."\n".'owner_history[nPlanets++] = new Array('.implode(',',$owner_history[$system['coordinates']]).');'."\n";
		}

	$history_javascript = ob_get_contents();
	ob_end_clean();

	$buffer = str_replace('***HISTORY_JAVASCRIPT***', $history_javascript, $buffer);

	$filename = strtr($series['name'], " ?'", "___").'_'.$game['game_number'].'.html';
	
	$fp = fopen($server['history_write_path'].$filename, 'w');
	
	fwrite($fp, $buffer);	
	fclose($fp);

	// The history file is written. Now update the game log.

	$lost = array();

	$fields = array();
	$fields[] = 'name = "'.$series['name'].' '.$game['game_number'].'"';
	
	if (count($won))
		{
		$fields[] = 'result = "win"';
		$fields[] = 'emps_left = "='.implode('=',$won).'="';
		
		foreach ($all_players as $player)
			if (!in_array($player, $won)) $lost[] = $player;
		}
	else if (count($draw))
		{
		$fields[] = 'result = "draw"';
		$fields[] = 'emps_left = "='.implode('=', $draw).'="';
		
		foreach ($all_players as $player)
			if (!in_array($player, $draw)) $lost[] = $player;
		}
	else
		{
		// All losers!
		$fields[] = 'result = "no winners"';
		$fields[] = 'emps_left = ""';
		
		foreach ($all_players as $player)
			$lost[] = $player;
		}
		
	if ($game['bridier'] >= 0)
		$fields[] = 'bridier = "yes"';
	else
		$fields[] = 'bridier = "no"';
		
	$fields[] = 'emps_nuked = "='.implode('=', $lost).'="';
	$fields[] = 'end_date = "'.date('Y-m-d H:i:s', $end_date).'"';
	
	sc_mysql_query('INSERT INTO gamelog SET '.implode(',',$fields));
	
	//is this a tournament game?
	$sql = 'select * from tournamentgame where game = ' . $game['id'];
	$result = sc_mysql_query($sql);
	
	if ($tourneygame = mysql_fetch_assoc($result)) {
		$empire = getEmpire($won['0']);
		$sql = 'update tournamentgame set winner = ' . $empire['id'] . ' where game = ' . $game['id'];
		sc_mysql_query($sql);
		
		$empire = getEmpire($lost['0']);
		$sql = 'update tournamententrant set eliminated = true where tournamentid = ' . $tourneygame['tournament'] . ' AND empireid = ' . $empire['id'];
		sc_mysql_query($sql);
		
		//is the tournament over?
		$sql = 'select count(*) as numberofplayersremaining from tournamententrant where tournamentid = ' . $tourneygame['tournament'] . ' AND eliminated = false';
		$result = sc_mysql_query($sql);
		$line = mysql_fetch_assoc($result);
		
		if ($line['numberofplayersremaining'] == 1) {
			$sql = 'update tournament set completed = true where id = ' . $tourneygame['tournament'];
			sc_mysql_query($sql);
		}
	}
	
	// And, finally, erase the game.
	eraseGame($game['id']);
}
?>