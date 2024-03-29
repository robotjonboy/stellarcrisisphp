<?php

require_once('jumpgatefunctions.php');

function debugecho($msg)
{
//	$debug=true;
	$debug=false;
	$debugtoscreen=false;
	$debugtocomment=true; //view source on web page to see debug comments
	if ($debug==true)
	{
		if($debugtoscreen) echo "<p>".$msg;
		if($debugtocomment) echo "\n<!--".$msg."-->";
	}
}
function editSeries($vars, $message = '')
{
	//debug print
	debugecho("**--editSeries---dump of vars--**");
	foreach ($vars as $var => $val)
	{
		debugecho("$var is $val");
	}
	debugecho("");
	reset($vars); //rewind just in case anyone else uses each
	//echo "-->";
	
	// load series to edit from mysql
	debugecho("calling getSeries");
	if ($vars['editSeriesID'])
	{
		$series = getSeries($vars['editSeriesID']);
	}

	// dump the query  [but I don't understand why I need two list/each lines to get the data nor why I get val twice]
	// but that probably explains the error message reporting "Variable passed to each() is not an array or object" on while line #
	/* remove as causing error on homeserve.org
	debugecho("*----------Dump query results---------------*");
	while (list($rec,$val ) = each($series)) //get column number and discard value
   {
	  list($var, $val)=each($series); //get column name and value
      debugecho("$rec: $var = $val");
   }
	debugecho("*----------End Dump query results---------------*");
  	*/
	
	// draw header
	$empire = $vars['empire_data'];
	standardHeader('Edit Series', $empire); //goes in browser frame

	// copy parameters (after header laid)
	//<input type=hidden name="empireID" value="<< echo $empire['empireID']; >> "> <!--cjp-->
	// NOT $empire['empireID'];  BUT  $empire['id'];   OR   $vars['empireID'];

?>
	<input type=hidden name="name" value="<?php echo $vars['name']; ?>">
	<input type=hidden name="pass" value="<?php echo $vars['pass']; ?>">
	<input type=hidden name="editSeriesID" value="<?php echo $vars['editSeriesID']; ?>">
	<input type=hidden name="empireID" value="<?php echo $empire['id']; ?>">
	<input type=hidden name="page" value="editSeries">
	<input type=hidden name="section" value="admin"> <!--cjp-->
<?php
	// page titles
	echo "<div class=pageTitle>\n".$empire['name']." : Edit a series\n";
	echo   ($vars['editSeriesID'] ? ': '.$series['name'] : ''); //show series if available
	echo "</div>\n";
	//buttons
	echo drawButtons($empire);
	echo "\n";
	// show time
	echo "<div class=message style=\"margin-top: 10pt;\" >\n";
	echo    'Local time and date: '.date('l, F j H:i:s T Y', time())."\n";
	echo "\n";
	// show online players in drop box  ** NOTE: this goes missing if $empire is lost
	echo onlinePlayers().empireMissive($empire);
	echo "\n";
	// rule
	echo "<img class=spacerule src=\"images/spacerule.jpg\" ";
	echo      "width=\"100%\" height=10 alt=\"spacerule.jpg\">";

	if (!$vars['editSeriesID']) 
	{	//ask for series to edit
		echo '<select name="series_id">'."\n";
		echo "<option value=0>Select series...\n";
		// and present list of series in dropdown box
		$select = sc_query('SELECT id, name FROM series ORDER BY name ASC',
								__FILE__.'*'.__LINE__);
		while ($series = $select->fetch_assoc())
		{
			echo '<option value="'.$series['id'].'">'.$series['name']."\n";
		}
		echo "</select>\n";
		echo '<input type=submit value="Go">'."\n";
		echo '<input type=submit name=action value="Cancel">'."\n";
		echo "</div>\n";
		footer();
	}
	else  //------------edit series----------------------------------------
	{
		// still "<div class=message style=\"margin-top: 10pt;\" >\n";
		debugecho("displaying form");
		// prepare time options
		$update_time=$series['update_time'];
			debugecho("Update_time: ".$update_time);
			$cjpresult=$update_time % 86400;
			debugecho("div: $cjpresult");
		//if (!($series['update_time'] % 86400))
		if (!($update_time % 86400))
		{
			debugecho("days");
			$update_time = $update_time/86400;
			$update_time_unit = 'days';
		}
		else if (!($update_time % 3600))
		{
			debugecho("hours");
			$update_time = $update_time/3600;
			$update_time_unit = 'hours';
		}
		else if (!($update_time % 60))
		{
			debugecho("minutes");
			$update_time = $update_time/60;
			$update_time_unit = 'minutes';
		}
		debugecho("Update_time: ".$update_time."   ".$update_time_unit);
?>

<table style="text-align: center; margin-left: auto; margin-right: auto;">
	<tr>
		<th style="text-align: right;">Series Name:</th>
		<td align=left>
		   <input type=text size=40 maxlength=40 name="series_name" value="<?php echo $series['name']; ?>">
		</td>
	</tr>
	<tr>
    <th style="text-align: right;">Game Type:</th>
    <td>
      <select name="game_type">
        <option value="sc2" <?php echo ($series['game_type'] == 'sc2' ? 'selected' : ''); ?>>sc2</option>
        <option value="sc3" <?php echo ($series['game_type'] == 'sc3' ? 'selected' : ''); ?>>sc3</option>
      </select>
    </td>
  </tr>
  <tr>
    <th style="text-align: right;">Jumpgate:</th>
    <td>
      <select name="jumpgate_status">
				<?php $jumpgate_status_options = ['Barred', 'Restricted', 'Unrestricted', 'Available'];
					foreach ($jumpgate_status_options as $status) {
						echo '<option value="' . $status . '" ';
						
						if (getJumpgateStatus($series) == $status) {
							echo 'selected';
						}

						echo '>' . $status . '</option>';
					}
				?>
      </select>
    </td>
  </tr>
  <tr>
    <th style="text-align: right;">Jumpgate Range Multiplier:</th>
    <td><input type=text size=7 maxlength=7 name="jumpgate_range_multiplier" value="<?php echo getJumpgateShipTypeOptions($series)['range_multiplier']; ?>"/> x BR (Blank indicates infinite range)</td>
  </tr>
  <tr>
    <th style="text-align: right;">Jumpgate Loss:</th>
    <td><input type=text size=6 maxlength=6 name="jumpgate_loss" value="<?php echo getJumpgateShipTypeOptions($series)['loss'];?>"/></td>
  </tr>
  <tr>
    <th style="text-align: right;">Jumpgate Build Cost:</th>
    <td><input type=text size=5 maxlength=5 name="jumpgate_build_cost" value="<?php echo getJumpgateShipTypeOptions($series)['build_cost'];?>"/></td>
  </tr>
  <tr>
    <th style="text-align: right;">Jumpgate Maintenance Cost:</th>
    <td><input type=text size=5 maxlength=5 name="jumpgate_maintenance_cost" value="<?php echo getJumpgateShipTypeOptions($series)['maintenance_cost'];?>"/></td>
  </tr>

	<tr>
		<th style="text-align: right;">Average agriculture:</th>
		<td align=left>
		   <input type=text size=5 maxlength=4 name="avg_ag" value="<?php echo $series['avg_ag']; ?>">
		</td>
	</tr>
	<tr>
		<th style="text-align: right;">Average fuel:</th>
		<td align=left>
		   <input type=text size=5 maxlength=4 name="avg_fuel" value="<?php echo $series['avg_fuel']; ?>">
		</td>
	</tr>
	<tr>
		<th style="text-align: right;">Average minerals:</th>
		<td align=left>
		   <input type=text size=5 maxlength=4 name="avg_min" value="<?php echo $series['avg_min']; ?>">
		</td>
	</tr>
	<tr>
		<th style="text-align: right;">Bridier allowed:</th>
		<td>
			<select name="bridier_allowed">
				<option value=1<?php echo ($series['bridier_allowed'] == 1 ? ' selected' : ''); ?>>Yes
				<option value=0<?php echo ($series['bridier_allowed'] == 0 ? ' selected' : ''); ?>>No
			</select>
		</td>
	</tr>
	<tr>
		<th style="text-align: right;">Can draw (2-player games only):</th>
		<td>
			<select name="can_draw">
				<option value=1<?php echo ($series['can_draw'] == 1 ? ' selected' : ''); ?>>Yes
				<option value=0<?php echo ($series['can_draw'] == 0 ? ' selected' : ''); ?>>No
			</select>
		</td>
	</tr>
	<tr>
		<th style="text-align: right;">Can surrender (2-player games only):</th>
		<td>
			<select name="can_surrender">
				<option value=1<?php echo ($series['can_surrender'] == 1 ? ' selected' : ''); ?>>Yes
				<option value=0<?php echo ($series['can_surrender'] == 0 ? ' selected' : ''); ?>>No
			</select>
		</td>
	</tr>
	<tr>
		<th style="text-align: right;">Cloakers appear as attacks:</th>
		<td>
			<select name="cloakers_as_attacks">
				<option value=1<?php echo ($series['cloakers_as_attacks'] == 1 ? ' selected' : ''); ?>>Yes
				<option value=0<?php echo ($series['cloakers_as_attacks'] == 0 ? ' selected' : ''); ?>>No
			</select>
		</td>
	</tr>
	<tr>
		<th style="text-align: right;">Cloakers build cloaked:</th>
		<td>
			<select name="build_cloakers_cloaked">
				<option value=1<?php 
				   echo ($series['build_cloakers_cloaked'] == 1 ? ' selected' : ''); ?>>
				   Yes
				<option value=0<?php echo ($series['build_cloakers_cloaked'] == 0 ? 
				   ' selected' : ''); ?>>
				   No
			</select>
		</td>
	</tr>
	<tr>
		<th style="text-align: right;">Creator:</th>
		<td>
		   <input type=text size=20 maxlength=20 name="creator" value="<?php echo $series['creator']; ?>">
		</td>
</tr>
	<tr>
		<th style="text-align: right;">Diplomatic states allowed:</th>
		<td>
			<select name="diplomacy">
				<option value=6<?php echo ($series['diplomacy'] == 6 ? ' selected' : ''); ?>>
				   All (Shared HQ)
				<option value=5<?php echo ($series['diplomacy'] == 5 ? ' selected' : ''); ?>>
				   Up to alliance
				<option value=4<?php echo ($series['diplomacy'] == 4 ? ' selected' : ''); ?>>
				   Up to trade
				<option value=3<?php echo ($series['diplomacy'] == 3 ? ' selected' : ''); ?>>
				   Up to truce
				<option value=2<?php echo ($series['diplomacy'] == 2 ? ' selected' : ''); ?>>
				   War
			</select>
		</td>
	</tr>
	<tr>
		<th style="text-align: right;">Map compresison:</th>
		<td align=left>
		   <input type=text size=5 maxlength=5 name="map_compression" 
		          value="<?php echo $series['map_compression']; ?>">
		</td>
	</tr>
	<tr>
		<th style="text-align: right;">Map type:</th>
		<td>
			<select name="map_type">
				<option value=1<?php echo ($series['map_type'] == 'standard' ? ' selected' : ''); ?>>
					Classic
				<option value=2<?php echo ($series['map_type'] == 'prebuilt' ? ' selected' : ''); ?>>
					Pre-built (random placement)
				<option value=3<?php echo ($series['map_type'] == 'twisted' ? ' selected' : ''); ?>>
					Twisted (2-player or team game only)
				<option value=4<?php echo ($series['map_type'] == 'mirror' ? ' selected' : ''); ?>>
					Mirror (2-player game only)
				<option value=5<?php echo ($series['map_type'] == 'balanced' ? ' selected' : ''); ?>>
					Balanced (2-player game only)
			</select>
		</td>
	</tr>
	<tr>
		<th style="text-align: right;">Map visible before game start:</th>
		<td>
			<select name="map_visible">
				<option value=1<?php echo ($series['map_visible'] == 1 ? ' selected' : ''); ?>>Yes
				<option value=0<?php echo ($series['map_visible'] == 0 ? ' selected' : ''); ?>>No
			</select>
		</td>
	</tr>
	<tr>
		<th style="text-align: right;">Maximum players:</th>
		<td align=left>
		   <input type=text size=3 maxlength=3 name="max_players" value="<?php echo $series['max_players']; ?>">
		</td>
	</tr>
	<tr>
		<th style="text-align: right;">Maximum wins:</th>
		<td>
			<!-- leave blank if max wins = -1 -->
			<input type=text size=5 maxlength=7 name="max_wins" 
			       value="<?php echo ($series['max_wins'] != -1 ? $series['max_wins'] : ''); ?>">
			<!-- but check the  "no max box" -->
			<input type=checkbox name="no_max_wins"<?php echo ($series['max_wins'] == -1 ? ' checked' : ''); ?>>
			   No maximum
		</td>
	</tr>
	<tr>
		<th style="text-align: right;">Minimum wins:</th>
		<td align=left>
		   <input type=text size=5 name="min_wins" value="<?php echo $series['min_wins']; ?>">
		</td>
	</tr>
	<tr>
		<th style="text-align: right;">Systems per player:</th>
		<td align=left>
		   <input type=text size=3 maxlength=3 name="systems_per_player" 
		          value="<?php echo $series['systems_per_player']; ?>">
		</td>
	</tr>
	<tr>
		<th style="text-align: right;">Team game:</th>
		<td>
			<select name="team_game">
				<option value=1<?php echo ($series['team_game'] == 1 ? ' selected' : ''); ?>>Yes
				<option value=0<?php echo ($series['team_game'] == 0 ? ' selected' : ''); ?>>No
			</select>
		</td>
	</tr>
	<tr>
		<th style="text-align: right;">Tech multiple:</th>
		<td align=left>
		   <input type=text size=5 maxlength=5 name="tech_multiple" 
		          value="<?php echo $series['tech_multiple']; ?>">
		</td>
	</tr>


	<tr>
		<th style="text-align: right;">Update Time:</th>
		<td>
			<input type=text size=5 maxlength=5 name="update_time" 
			       value="<?php echo $update_time; ?>">
			<select name="update_time_unit">
				<option<?php echo ($update_time_unit == 'minutes' ? ' selected' : ''); ?>>
				   Minutes
				<option<?php echo ($update_time_unit == 'hours' ? ' selected' : ''); ?>>
				   Hours
				<option<?php echo ($update_time_unit == 'days' ? ' selected' : ''); ?>>
				   Days
			</select>
			<input type=checkbox name="weekend_updates" 
				<?php echo ($series['weekend_updates'] == 1 ? ' checked' : ''); ?>
			> Weekend Updates
		</td>
	</tr>
	<tr>
		<th style="text-align: right;">Visible builds:</th>
		<td>
			<select name="visible_builds">
				<option value=1<?php 
				   echo ($series['visible_builds'] == 1 ? ' selected' : ''); ?>>
				   Yes
				<option value=0<?php 
				   echo ($series['visible_builds'] == 0 ? ' selected' : ''); ?>>
				   No
			</select>
		</td>
	</tr>
</table>

<br>
<input type=submit value="Update">
<input type=submit name="action" value="Cancel">
</div>
<?php
	footer();
	} // end of edit series
} //end of page

#
#-----------------------------------------------------------------------------------------------------------------------------------------#
#

function editSeries_processing($vars)
{
	global $authenticated_as_admin;

	//debug print
	debugecho(""); //throw a <p>
	debugecho("**--editSeries_processing--dump of vars--**");
	foreach ($vars as $var => $val)
	{
		debugecho("$var is $val");
	}
	reset($vars); //just in case anyone else uses each

	if (!$authenticated_as_admin)
		return loginFailed('Identity check failed.');
	debugecho("01: Authenticated");

	if ($vars['action'] == 'Cancel')
		return mainPage_admin($vars, 'Series update cancelled.');
	debugecho("02: passed cancel check");

	//first time through we just move series_id into editSeriesID and call editSeries() to present the edit form
	if ($vars['editSeriesID'] == '')
	{
		debugecho("03: editSeriesID=null");
		if ($vars['series_id'])
		{
			debugecho("04: series_id=".$vars['series_id']);
			$vars['editSeriesID'] = $vars['series_id'];
			debugecho("05: editSeriesID=".$vars['series_id']);
			return editSeries($vars); //now edit
		}
		else
			return editSeries($vars, 'Invalid series selected.'); //no series selected //cjp
	}
	debugecho("06: editSeriesID=".$vars['editSeriesID']." passed check");

	$vars['editSeriesID'] = (int)$vars['editSeriesID'];

	$map_types = array(1 => 'standard', 2 => 'prebuilt', 3 => 'twisted', 4 => 'mirror', 5 => 'balanced');
	$message = '';

	$series = getSeries($vars['editSeriesID']);

	debugecho("07: starting sanity checks");
	// Various sanity checks.
	if ($vars['game_type'] != 'sc3') {
		$vars['game_type'] = 'sc2';
	}
	
	// Make sure the new series name is not already in use. if changed
	if ($series['name'] != stripslashes($vars['series_name']) and getSeriesByName($vars['series_name']))
		return editSeries($vars, 'That series name is already in use.');

	debugecho("08");
	if ($vars['map_compression'] == '' or sscanf($vars['map_compression'], '%f', $map_compression) == 0)
		$map_compression = 0.001;

	if ($map_compression < 0.001 or $map_compression > 0.8)
		return editSeries($vars, 'Map compression must be between 0.001 and 0.8.');

	debugecho("09");
	if (preg_match('/=/', $vars['series_name']))		return editSeries($vars, 'The series name cannot contain an "=".');
	if ($vars['update_time'] == 0)				return editSeries($vars, 'An invalid update time was entered.');
	if ($vars['max_players'] < 2)				return editSeries($vars, 'An invalid player maximum was entered.');
	if ($vars['systems_per_player'] < 3)		return editSeries($vars, 'An invalid systems per player was entered.');
	if (!is_numeric($vars['tech_multiple']))	return editSeries($vars, 'An invalid tech multiple was entered.');
	if (!is_numeric($vars['min_wins']))			return editSeries($vars, 'No minimum wins entered.');

	debugecho("10");
	if ($vars['bridier_allowed'] and $vars['max_players'] != 2)
		return editSeries($vars, 'Bridier is for 2 player games only.');
	if ($vars['map_type'] == 2 and $vars['max_players']*$vars['systems_per_player'] > 250)
		return editSeries($vars, 'Pre-built map not allowed for more than 250 total systems');
	if ($vars['no_max_wins'] == '' and !is_numeric($vars['max_wins']))
		return editSeries($vars, 'No maximum wins entered.');
	if ($vars['no_max_wins'] == '' and $vars['max_wins'] < $vars['min_wins'])
		return editSeries($vars, 'Maximum wins was less than minimum wins.');

	debugecho("11");
	if ($vars['team_game'])
		{
		if (floor($vars['max_players']/2) != $vars['max_players']/2)
			return editSeries($vars, 'Team games <b>must</b> have an even number of players');
		
		if ($vars['diplomacy'] < 5)
			return editSeries($vars, 'Team games <b>must</b> allow alliance');

		if ($vars['map_type'] != 3)
			{
			$vars['map_type'] = 3;
			$message .= 'Map type set to twisted for team game.<br>';
			}
		}
	else if ($vars['map_type'] == 3 and $vars['max_players'] > 2)
		return editSeries($vars, 'Twisted map type not allowed in non-team games with more than two players.');
	else if ($vars['map_type'] == 4 and $vars['max_players'] > 2)
		return editSeries($vars, 'Mirror map type not allowed in non-team games with more than two players.');
	else if ($vars['map_type'] == 5 and $vars['max_players'] > 2)
		return editSeries($vars, 'Balanced map not allowed in games with more than two players.');
	debugecho("12: passed sanity checks");

	switch ($vars['update_time_unit'])
	{  //convert to seconds
   		case 'Minutes':	$vars['update_time'] *= 60;		break;
   		case 'Hours':	$vars['update_time'] *= 3600;	break;
   		case 'Days':	$vars['update_time'] *= 86400;	break;
	}

	$vars['weekend_updates'] = ($vars['weekend_updates'] != '' ? 1 : 0);
	$vars['max_wins'] = ($vars['no_max_wins'] != '' ? -1 : $vars['max_wins']);

	if ($vars['jumpgate_status'] != 'Available' && $vars['jumpgate_status'] != 'Unrestricted' && $vars['jumpgate_status'] != 'Restricted') {
    $vars['jumpgate_status'] = 'Barred';
  } 
    
  if (array_key_exists('jumpgate_range_multiplier', $vars) && strlen(trim($vars['jumpgate_range_multiplier'])) > 0) {
    $vars['jumpgate_range_multiplier'] = floatval($vars['jumpgate_range_multiplier']);
  } else {
    $vars['jumpgate_range_multiplier'] = 'NULL';
  }   
      
  if (array_key_exists('jumpgate_loss', $vars)) {
    $vars['jumpgate_loss'] = floatval($vars['jumpgate_loss']);
  }   
      
  if (array_key_exists('jumpgate_build_cost', $vars)) {
    $vars['jumpgate_build_cost'] = intval($vars['jumpgate_build_cost']);
  }   
      
  if (array_key_exists('jumpgate_maintenance_cost', $vars)) {
    $vars['jumpgate_maintenance_cost'] = intval($vars['jumpgate_maintenance_cost']);
  }

  if ($vars['game_type'] == 'sc2') {
    if ($vars['jumpgate_status'] != 'Barred') {
      return editSeries($vars, 'Jumpgate must be barred in sc2 games.');
    }
  }

	debugecho("loading values");
	// load fields for update
	$values = array();
	//id is auto increment
	$values[] = 'name = "'.$vars['series_name'].'"';
	// keep rest of fields in alpha order
	$values[] = 'average_resources = "'.$vars['avg_min'].'"';
	$values[] = 'avg_ag = "'.$vars['avg_ag'].'"';
	$values[] = 'avg_fuel = "'.$vars['avg_fuel'].'"';
	$values[] = 'avg_min = "'.$vars['avg_min'].'"';
	$values[] = 'bridier_allowed = "'.$vars['bridier_allowed'].'"';
	$values[] = 'build_cloakers_cloaked = "'.$vars['build_cloakers_cloaked'].'"';
	$values[] = 'can_draw = "'.$vars['can_draw'].'"';
	$values[] = 'can_surrender = "'.$vars['can_surrender'].'"';
	$values[] = 'cloakers_as_attacks = "'.$vars['cloakers_as_attacks'].'"';
	//creator
	//custom
	$values[] = 'diplomacy = "'.$vars['diplomacy'].'"';
	//game_count
	//halted
	$values[] = 'map_compression = "'.$map_compression.'"';
	$values[] = 'map_type = "'.$map_types[$vars['map_type']].'"';
	$values[] = 'map_visible = "'.$vars['map_visible'].'"';
	$values[] = 'max_players = "'.$vars['max_players'].'"';
	$values[] = 'max_wins = "'.$vars['max_wins'].'"';
	$values[] = 'min_wins = "'.$vars['min_wins'].'"';
	$values[] = 'systems_per_player = "'.$vars['systems_per_player'].'"';
	$values[] = 'team_game = "'.$vars['team_game'].'"';
	$values[] = 'tech_multiple = "'.$vars['tech_multiple'].'"';
	$values[] = 'update_time = "'.$vars['update_time'].'"';
	$values[] = 'visible_builds = "'.$vars['visible_builds'].'"';
	$values[] = 'weekend_updates = "'.$vars['weekend_updates'].'"';

	debugecho("14: pre mysql update");
	$cjpsql='UPDATE series SET '.implode(',', $values).' WHERE id = '.$vars['editSeriesID'];
	debugecho($cjpsql);
	sc_query('UPDATE series SET '.implode(',', $values).' WHERE id = '.$vars['editSeriesID'], __FILE__.'*'.__LINE__);

	$values = array();
	$values[] = 'status = \'' . $vars['jumpgate_status'] . '\'';
	$values[] = 'range_multiplier = \'' . $vars['jumpgate_range_multiplier'] . '\'';
	$values[] = 'loss = \'' . $vars['jumpgate_loss'] . '\'';
	$values[] = 'build_cost = \'' . $vars['jumpgate_build_cost'] . '\'';
	$values[] = 'maintenance_cost = \'' . $vars['jumpgate_maintenance_cost'] . '\'';
	
	$sql = 'update series_ship_type_options set ' . implode(',', $values) . ' where series_id = ' . $vars['editSeriesID'] . ' AND ship_type = \'Jumpgate\'';
	sc_query($sql);

	$sql = 'update game_ship_type_options set ' . implode(',', $values) . ' where game_id in (select id from games where series_id = ' . $vars['editSeriesID'] . ' AND player_count = 0) AND ship_type = \'Jumpgate\'';
	sc_query($sql);

	debugecho("14: pre sendEmpireMessage");
	$empire = [];
	$empire['id'] = $vars['empireID'];
	sendEmpireMessage($empire, 'Series <span style="color: red;">'.stripslashes($vars['series_name']).'</span> successfully updated.');
	debugecho("14: afater sendEmpireMessage");

	mainPage_admin($vars, $message.'Series <font color=FF0000>'.stripslashes($vars['series_name']).
	'</font> udpated.');
}
?>
