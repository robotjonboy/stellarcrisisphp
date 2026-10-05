<?php
#----------------------------------------------------------------------------------------------------------------------#
# Admin: spawn empty open games for series that do not have one (e.g. after a DB import).
#----------------------------------------------------------------------------------------------------------------------#

function spawnOpenGames_candidates()
{
	$sql = 'SELECT s.id, s.name, s.game_type, s.game_count,
		(SELECT COUNT(*) FROM series_ship_type_options o WHERE o.series_id = s.id) AS ship_options_count
		FROM series s
		WHERE s.halted = "0"
		AND NOT EXISTS (
			SELECT 1 FROM games g WHERE g.series_id = s.id AND g.player_count = 0
		)
		ORDER BY s.name ASC';

	$select = sc_query($sql, __FILE__.'*'.__LINE__);
	$candidates = array();

	while ($row = $select->fetch_assoc())
		{
		$row['missing_ship_options'] = ($row['game_type'] == 'sc3' && (int)$row['ship_options_count'] === 0);
		$candidates[] = $row;
		}

	return $candidates;
}

#----------------------------------------------------------------------------------------------------------------------#

function spawnOpenGames($vars)
{
	$empire = $vars['empire_data'];
	$candidates = spawnOpenGames_candidates();

	standardHeader('Spawn Open Games', $empire);
?>
<div class=pageTitle>Spawn Open Games</div>

<?php authHiddenFields($vars['name']); ?>
<input type=hidden name="section" value="admin">
<input type=hidden name="page" value="spawnOpenGames">
<?php
	echo drawButtons($empire).serverTime().onlinePlayers().empireMissive($empire);
?>
<img class=spacerule src="images/spacerule.jpg" width="100%">

<div style="text-align: center; margin-bottom: 10pt;">
	Creates the empty joinable game for series that do not have one (for example after a database import).
</div>
<?php
	if (!count($candidates))
		{
?>
<div style="text-align: center;">
	All active series already have an open game.
</div>
<div style="text-align: center; margin-top: 10pt;">
	<input type=submit name=cancel value="Back">
</div>
<?php
		footer();
		return;
		}
?>
<div style="text-align: center; margin-left: auto; margin-right: auto;">
<table style="margin-left: auto; margin-right: auto; text-align: left;">
	<tr>
		<th></th>
		<th>Series</th>
		<th>Type</th>
		<th>Game count</th>
		<th></th>
	</tr>
<?php
	foreach ($candidates as $series)
		{
		$can_spawn = !$series['missing_ship_options'];
		$checked = $can_spawn ? ' checked' : '';
		$disabled = $can_spawn ? '' : ' disabled';
?>
	<tr>
		<td><input type=checkbox name="seriesToSpawn[]" value="<?php echo (int)$series['id']; ?>"
			<?php echo $checked.$disabled; ?>
			onChange="spawnOpenGamesUpdateSpawnButton()"></td>
		<td><?php echo $series['name']; ?></td>
		<td><?php echo $series['game_type']; ?></td>
		<td><?php echo (int)$series['game_count']; ?></td>
		<td><?php
		if ($series['missing_ship_options'])
			echo '<span class=red>Missing ship options — use Edit series first</span>';
		?></td>
	</tr>
<?php
		}
?>
</table>
</div>
<div style="text-align: center; margin-top: 10pt;">
	<input type=submit name=spawn value="Spawn" id="spawnOpenGamesButton">
	<input type=submit name=cancel value="Cancel">
</div>
<script type="text/javascript">
function spawnOpenGamesUpdateSpawnButton()
{
	var boxes = document.getElementsByName('seriesToSpawn[]');
	var any = false;
	for (var i = 0; i < boxes.length; i++)
		if (boxes[i].checked && !boxes[i].disabled)
			any = true;
	document.getElementById('spawnOpenGamesButton').disabled = !any;
}
spawnOpenGamesUpdateSpawnButton();
</script>
<?php
	footer();
}

#----------------------------------------------------------------------------------------------------------------------#

function spawnOpenGames_processing($vars)
{
	if ($vars['cancel'])
		{
		sendEmpireMessage($vars['empire_data'], 'Spawn open games cancelled.');
		return mainPage_admin($vars);
		}

	if ($vars['spawn'])
		{
		$selected = array();
		if (isset($vars['seriesToSpawn']) && is_array($vars['seriesToSpawn']))
			$selected = $vars['seriesToSpawn'];

		$spawned = array();
		$skipped = array();

		foreach ($selected as $series_id)
			{
			$series = getSeries((int)$series_id);
			if (!$series || !$series['id'])
				{
				$skipped[] = 'unknown id '.$series_id.' (not found)';
				continue;
				}

			if ($series['halted'])
				{
				$skipped[] = $series['name'].' (halted)';
				continue;
				}

			$empty = sc_query(
				'SELECT id FROM games WHERE series_id = '.(int)$series['id'].' AND player_count = 0 LIMIT 1',
				__FILE__.'*'.__LINE__
			);
			if ($empty->num_rows)
				{
				$skipped[] = $series['name'].' (already has empty game)';
				continue;
				}

			if ($series['game_type'] == 'sc3')
				{
				$options = sc_query(
					'SELECT id FROM series_ship_type_options WHERE series_id = '.(int)$series['id'].' LIMIT 1',
					__FILE__.'*'.__LINE__
				);
				if (!$options->num_rows)
					{
					$skipped[] = $series['name'].' (sc3 missing ship options — use Edit series)';
					continue;
					}
				}

			$game_id = spawnGame($series['name']);
			if ($game_id)
				$spawned[] = $series['name'];
			else
				$skipped[] = $series['name'].' (spawn failed)';
			}

		$parts = array();
		if (count($spawned))
			$parts[] = 'Spawned open games for: <span class=red>'.implode(', ', $spawned).'</span>.';
		if (count($skipped))
			$parts[] = 'Skipped: '.implode('; ', $skipped).'.';
		if (!count($spawned) && !count($skipped))
			$parts[] = 'No series selected.';

		sendEmpireMessage($vars['empire_data'], implode(' ', $parts));
		return spawnOpenGames($vars);
		}

	sendEmpireMessage($vars['empire_data'], 'Invalid action.');
	return mainPage_admin($vars);
}
?>
