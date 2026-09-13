<?php
/**
 * SC3 helpers: ship-type options, tech access, and series knobs.
 * See docs/design-sc3-remaining-features.md
 */

/** Canonical tech status values (Available is a legacy alias of Developed). */
function sc3NormalizeStatus($status)
{
	if ($status === 'Available')
		return 'Developed';
	return $status;
}

function sc3IsDevelopedStatus($status)
{
	$status = sc3NormalizeStatus($status);
	return $status === 'Developed';
}

/**
 * Default special-field values for ship-type options when creating an sc3 series.
 */
function sc3DefaultShipTypeOptionFields($ship_type)
{
	$defaults = array(
		'status' => 'Unrestricted',
		'range_multiplier' => null,
		'loss' => null,
		'build_cost' => null,
		'maintenance_cost' => null,
	);

	switch ($ship_type)
	{
		case 'Attack':
		case 'Science':
		case 'Colony':
			$defaults['status'] = 'Developed';
			break;
		case 'Jumpgate':
			$defaults['status'] = 'Unrestricted';
			$defaults['loss'] = 0.25;
			$defaults['build_cost'] = 100;
			$defaults['maintenance_cost'] = 16;
			break;
		case 'Carrier':
			$defaults['loss'] = 0.5;
			$defaults['build_cost'] = 50;
			$defaults['maintenance_cost'] = 16;
			break;
		case 'Builder':
			$defaults['build_cost'] = 100;
			$defaults['maintenance_cost'] = 32;
			break;
		case 'Morpher':
			$defaults['loss'] = 0.5;
			$defaults['build_cost'] = 75;
			$defaults['maintenance_cost'] = 32;
			break;
		case 'Engineer':
			$defaults['loss'] = 0.5;
			break;
		case 'Stargate':
			// null range_multiplier = infinite
			break;
	}

	return $defaults;
}

/**
 * All ship type names for a game type (v2 only, or v2+v3).
 */
function getEffectiveShipTypeNames($game_type = 'sc2')
{
	global $ship_types, $v3_ship_types;

	$names = array_keys($ship_types);
	if ($game_type === 'sc3' && is_array($v3_ship_types))
	{
		foreach (array_keys($v3_ship_types) as $type)
		{
			if (!in_array($type, $names, true))
				$names[] = $type;
		}
	}
	sort($names);
	return $names;
}

function getEffectiveMovingShips($game_type = 'sc2')
{
	global $moving_ships, $v3_moving_ships;

	$names = $moving_ships;
	if ($game_type === 'sc3' && is_array($v3_moving_ships))
	{
		foreach ($v3_moving_ships as $type)
		{
			if (!in_array($type, $names, true))
				$names[] = $type;
		}
	}
	return $names;
}

/**
 * Options row for a ship type from series or game array, or empty array.
 */
function sc3GetShipTypeOptions($series_or_game, $ship_type)
{
	if (!is_array($series_or_game) || empty($series_or_game['ship_type_options']) || !is_array($series_or_game['ship_type_options']))
		return array();
	if (!array_key_exists($ship_type, $series_or_game['ship_type_options']))
		return array();
	return $series_or_game['ship_type_options'][$ship_type];
}

function sc3GetShipTypeStatus($series_or_game, $ship_type)
{
	$opts = sc3GetShipTypeOptions($series_or_game, $ship_type);
	if (empty($opts) || !array_key_exists('status', $opts))
	{
		// sc2 / missing options: classic twelve are unrestricted; unknown v3 types barred
		global $ship_types;
		if (array_key_exists($ship_type, $ship_types))
			return 'Unrestricted';
		return 'Barred';
	}
	return sc3NormalizeStatus($opts['status']);
}

function sc3GetOptionField($series_or_game, $ship_type, $field, $default = null)
{
	$opts = sc3GetShipTypeOptions($series_or_game, $ship_type);
	if (empty($opts) || !array_key_exists($field, $opts) || $opts[$field] === null || $opts[$field] === '')
		return $default;
	return $opts[$field];
}

function sc3MinPopToBuild($series)
{
	global $server;
	if (is_array($series) && array_key_exists('min_pop_to_build', $series) && $series['min_pop_to_build'] !== null && $series['min_pop_to_build'] !== '')
		return (int)$series['min_pop_to_build'];
	return (int)$server['builder_population'];
}

# Colony ships each cost 1 population. A system must keep at least 1, so a planet
# can only start a colony build if it already meets the series minimum and can
# spare a colonist. A pop-1 system with min pop 1 cannot build colonies.
function sc3MinPopToBuildColony($series)
{
	return max(sc3MinPopToBuild($series), 2);
}

# How many colony ships can still be started at this population without dropping below 1.
function sc3MaxColonyBuilds($population)
{
	return max(0, (int)$population - 1);
}

function sc3EngineerLoss($series_or_game)
{
	global $server;
	$loss = sc3GetOptionField($series_or_game, 'Engineer', 'loss', null);
	if ($loss === null || $loss === '')
		return (float)$server['engineer_br_loss'];
	return (float)$loss;
}

function sc3CarrierLoss($series_or_game)
{
	$loss = sc3GetOptionField($series_or_game, 'Carrier', 'loss', 0.5);
	return (float)$loss;
}

function sc3MorpherLoss($series_or_game)
{
	$loss = sc3GetOptionField($series_or_game, 'Morpher', 'loss', 0.5);
	return (float)$loss;
}

function sc3PlanetCreateCost($series)
{
	if (is_array($series) && array_key_exists('planet_create_cost', $series) && $series['planet_create_cost'] !== null && $series['planet_create_cost'] !== '')
		return (int)$series['planet_create_cost'];
	return 90;
}

function sc3MaxPlanets($series)
{
	if (is_array($series) && array_key_exists('max_planets', $series) && $series['max_planets'] !== null && $series['max_planets'] !== '')
		return (int)$series['max_planets'];
	return 1000;
}

function sc3MaxPlanetsCustomCap()
{
	global $server;
	if (isset($server['max_planets_custom']))
		return (int)$server['max_planets_custom'];
	return 2000;
}

/**
 * Starting tech list for joining a game.
 */
function sc3StartingTechs($series)
{
	if (!is_array($series) || ($series['game_type'] ?? 'sc2') !== 'sc3' || empty($series['ship_type_options']))
		return 'Attack Science Colony';

	$developed = array();
	foreach ($series['ship_type_options'] as $type => $opts)
	{
		if (sc3IsDevelopedStatus($opts['status'] ?? ''))
			$developed[] = $type;
	}
	if (!count($developed))
		return 'Attack Science Colony';
	sort($developed);
	return implode(' ', $developed);
}

/**
 * Techs the player may still develop (respecting barred / restricted caps).
 */
function sc3DevelopableTechs($game, $player)
{
	$owned = array_filter(explode(' ', trim($player['techs'])));
	$types = getEffectiveShipTypeNames($game['game_type'] ?? 'sc2');
	$developable = array();

	$max_restricted = isset($game['max_restricted_techs']) ? (int)$game['max_restricted_techs'] : (isset($game['series_max_restricted_techs']) ? (int)$game['series_max_restricted_techs'] : 0);
	// Prefer series fields passed via series_data; caller should pass series for caps.
	$restricted_known = isset($player['restricted_techs']) ? (int)$player['restricted_techs'] : 0;

	foreach ($types as $type)
	{
		if (in_array($type, $owned, true))
			continue;
		$status = sc3GetShipTypeStatus($game, $type);
		if ($status === 'Barred' || $status === 'Developed')
			continue;
		if ($status === 'Restricted')
		{
			if ($restricted_known >= $max_restricted)
				continue;
		}
		if ($status === 'Unrestricted' || $status === 'Restricted')
			$developable[] = $type;
	}
	return $developable;
}

/**
 * Whether a tech choice is valid for this player/game. $series used for restricted caps.
 */
function sc3ValidTechChoice($series, $game, $player, $new_tech)
{
	$owned = explode(' ', trim($player['techs']));
	if (in_array($new_tech, $owned, true))
		return false;

	$status = sc3GetShipTypeStatus($game, $new_tech);
	if ($status === 'Unrestricted')
		return true;
	if ($status === 'Restricted')
	{
		$max = isset($series['max_restricted_techs']) ? (int)$series['max_restricted_techs'] : 0;
		$have = isset($player['restricted_techs']) ? (int)$player['restricted_techs'] : 0;
		return $have < $max;
	}
	return false;
}

/**
 * Apply special build/maint costs from ship_type_options when present.
 * Returns array($build_add, $maint_add) to ADD to base formula costs.
 */
function sc3SpecialBuildCosts($game, $ship_type)
{
	$build = sc3GetOptionField($game, $ship_type, 'build_cost', null);
	$maint = sc3GetOptionField($game, $ship_type, 'maintenance_cost', null);
	if ($build === null && $maint === null)
		return null;
	return array(
		$build !== null ? (int)$build : 0,
		$maint !== null ? (int)$maint : 0,
	);
}

/**
 * Insert or replace all ship-type option rows for a series from $vars form fields.
 * Expects $vars['tech_status'][$type], optional tech_range/tech_loss/tech_build/tech_maint.
 */
function sc3SaveSeriesShipTypeOptions($series_id, $vars, $game_type, $update_empty_games = false)
{
	global $mysqli;

	$series_id = (int)$series_id;
	$types = getEffectiveShipTypeNames($game_type === 'sc3' ? 'sc3' : 'sc2');

	if ($game_type !== 'sc3')
	{
		// sc2: only ensure Jumpgate barred row for spawn compatibility
		sc_query('DELETE FROM series_ship_type_options WHERE series_id = '.$series_id);
		sc_query('INSERT INTO series_ship_type_options SET series_id = '.$series_id.
			', ship_type = \'Jumpgate\', status = \'Barred\', loss = 0.25, build_cost = 100, maintenance_cost = 16');
		if ($update_empty_games)
			sc3PropagateOptionsToEmptyGames($series_id);
		return;
	}

	sc_query('DELETE FROM series_ship_type_options WHERE series_id = '.$series_id);

	foreach ($types as $type)
	{
		$defaults = sc3DefaultShipTypeOptionFields($type);
		$status = $vars['tech_status'][$type] ?? $defaults['status'];
		$status = sc3NormalizeStatus($status);
		if (!in_array($status, array('Developed', 'Unrestricted', 'Restricted', 'Barred'), true))
			$status = $defaults['status'];

		$values = array();
		$values[] = 'series_id = '.$series_id;
		$values[] = 'ship_type = \''.$mysqli->real_escape_string($type).'\'';
		$values[] = 'status = \''.$mysqli->real_escape_string($status).'\'';

		$range = $vars['tech_range'][$type] ?? '';
		if ($range !== '' && $range !== null && is_numeric($range))
			$values[] = 'range_multiplier = '.floatval($range);

		$loss = $vars['tech_loss'][$type] ?? '';
		if ($loss !== '' && $loss !== null && is_numeric($loss))
			$values[] = 'loss = '.floatval($loss);
		else if ($defaults['loss'] !== null && (!isset($vars['tech_loss'][$type]) || $vars['tech_loss'][$type] === ''))
		{
			// keep default loss for types that need it when field omitted
			if (in_array($type, array('Jumpgate', 'Carrier', 'Morpher', 'Engineer'), true))
				$values[] = 'loss = '.floatval($defaults['loss']);
		}

		$build = $vars['tech_build'][$type] ?? '';
		if ($build !== '' && $build !== null && is_numeric($build))
			$values[] = 'build_cost = '.intval($build);
		else if ($defaults['build_cost'] !== null && in_array($type, array('Jumpgate', 'Carrier', 'Builder', 'Morpher'), true)
			&& (!isset($vars['tech_build'][$type]) || $vars['tech_build'][$type] === ''))
			$values[] = 'build_cost = '.intval($defaults['build_cost']);

		$maint = $vars['tech_maint'][$type] ?? '';
		if ($maint !== '' && $maint !== null && is_numeric($maint))
			$values[] = 'maintenance_cost = '.intval($maint);
		else if ($defaults['maintenance_cost'] !== null && in_array($type, array('Jumpgate', 'Carrier', 'Builder', 'Morpher'), true)
			&& (!isset($vars['tech_maint'][$type]) || $vars['tech_maint'][$type] === ''))
			$values[] = 'maintenance_cost = '.intval($defaults['maintenance_cost']);

		sc_query('INSERT INTO series_ship_type_options SET '.implode(', ', $values));
	}

	if ($update_empty_games)
		sc3PropagateOptionsToEmptyGames($series_id);
}

function sc3PropagateOptionsToEmptyGames($series_id)
{
	$series_id = (int)$series_id;
	$games = sc_query('SELECT id FROM games WHERE series_id = '.$series_id.' AND player_count = 0');
	while ($game = $games->fetch_assoc())
	{
		$gid = (int)$game['id'];
		sc_query('DELETE FROM game_ship_type_options WHERE game_id = '.$gid);
		$opts = sc_query('SELECT * FROM series_ship_type_options WHERE series_id = '.$series_id);
		while ($row = $opts->fetch_assoc())
		{
			$values = array();
			$values[] = 'game_id = '.$gid;
			$values[] = 'ship_type = \''.addslashes($row['ship_type']).'\'';
			$values[] = 'status = \''.addslashes($row['status']).'\'';
			if ($row['range_multiplier'] !== null && $row['range_multiplier'] !== '')
				$values[] = 'range_multiplier = '.$row['range_multiplier'];
			if ($row['loss'] !== null && $row['loss'] !== '')
				$values[] = 'loss = '.$row['loss'];
			if ($row['build_cost'] !== null && $row['build_cost'] !== '')
				$values[] = 'build_cost = '.$row['build_cost'];
			if ($row['maintenance_cost'] !== null && $row['maintenance_cost'] !== '')
				$values[] = 'maintenance_cost = '.$row['maintenance_cost'];
			sc_query('INSERT INTO game_ship_type_options SET '.implode(', ', $values));
		}
	}
}

/**
 * Series-level column values from form $vars (shared by create/edit/custom).
 */
function sc3SeriesLevelValuesFromVars($vars, $is_custom = false)
{
	global $mysqli;

	$max_planets = isset($vars['max_planets']) ? intval($vars['max_planets']) : 1000;
	if ($max_planets < 1)
		$max_planets = 1000;
	if ($is_custom)
	{
		$cap = sc3MaxPlanetsCustomCap();
		if ($max_planets > $cap)
			$max_planets = $cap;
	}

	$values = array();
	$values[] = 'max_ag_ratio = '.floatval($vars['max_ag_ratio'] ?? 100);
	$values[] = 'max_ships = '.intval($vars['max_ships'] ?? 0);
	$values[] = 'initial_tech = '.floatval($vars['initial_tech'] ?? 1.0);
	$values[] = 'hw_ag = '.intval($vars['hw_ag'] ?? 100);
	$values[] = 'hw_min = '.intval($vars['hw_min'] ?? 100);
	$values[] = 'hw_fuel = '.intval($vars['hw_fuel'] ?? 100);
	$values[] = 'min_pop_to_build = '.intval($vars['min_pop_to_build'] ?? 50);
	$values[] = 'planet_create_cost = '.intval($vars['planet_create_cost'] ?? 90);
	$values[] = 'max_planets = '.$max_planets;
	$values[] = 'max_restricted_techs = '.intval($vars['max_restricted_techs'] ?? 0);
	$values[] = 'max_trade_ins = '.intval($vars['max_trade_ins'] ?? 0);
	if (!isset($vars['max_allies']) || trim((string)$vars['max_allies']) === '')
		$values[] = 'max_allies = NULL';
	else
		$values[] = 'max_allies = '.intval($vars['max_allies']);
	return $values;
}

/**
 * HTML fragment: tech status + conditional fields for sc3 series forms.
 * $existing = series array with ship_type_options, or empty for defaults.
 */
function sc3ShipTypeOptionsFormHtml($existing = array(), $game_type = 'sc3')
{
	$types = getEffectiveShipTypeNames('sc3');
	$statuses = array('Developed', 'Unrestricted', 'Restricted', 'Barred');
	$html = '<tr><th colspan="2" style="padding-top: 12pt;">Ship type access (sc3)</th></tr>';
	$html .= '<tr><td colspan="2"><table style="margin-left: auto; margin-right: auto; font-size: smaller;">';
	$html .= '<tr><th>Type</th><th>Status</th><th>Range × BR</th><th>Loss</th><th>Build</th><th>Maint</th></tr>';

	foreach ($types as $type)
	{
		$defaults = sc3DefaultShipTypeOptionFields($type);
		$opts = array();
		if (!empty($existing['ship_type_options'][$type]))
			$opts = $existing['ship_type_options'][$type];

		$cur_status = sc3NormalizeStatus($opts['status'] ?? $defaults['status']);
		$range = $opts['range_multiplier'] ?? '';
		$loss = $opts['loss'] ?? ($defaults['loss'] !== null ? $defaults['loss'] : '');
		$build = $opts['build_cost'] ?? ($defaults['build_cost'] !== null ? $defaults['build_cost'] : '');
		$maint = $opts['maintenance_cost'] ?? ($defaults['maintenance_cost'] !== null ? $defaults['maintenance_cost'] : '');

		$show_range = in_array($type, array('Jumpgate', 'Stargate'), true);
		$show_loss = in_array($type, array('Jumpgate', 'Carrier', 'Morpher', 'Engineer'), true);
		$show_cost = in_array($type, array('Jumpgate', 'Carrier', 'Builder', 'Morpher'), true);

		$html .= '<tr><td>'.htmlspecialchars($type).'</td><td><select name="tech_status['.htmlspecialchars($type).']">';
		foreach ($statuses as $st)
		{
			$sel = ($cur_status === $st) ? ' selected' : '';
			$html .= '<option value="'.$st.'"'.$sel.'>'.$st.'</option>';
		}
		$html .= '</select></td>';

		if ($show_range)
			$html .= '<td><input type="text" size="6" name="tech_range['.htmlspecialchars($type).']" value="'.htmlspecialchars((string)$range).'"></td>';
		else
			$html .= '<td></td>';

		if ($show_loss)
			$html .= '<td><input type="text" size="5" name="tech_loss['.htmlspecialchars($type).']" value="'.htmlspecialchars((string)$loss).'"></td>';
		else
			$html .= '<td></td>';

		if ($show_cost)
		{
			$html .= '<td><input type="text" size="5" name="tech_build['.htmlspecialchars($type).']" value="'.htmlspecialchars((string)$build).'"></td>';
			$html .= '<td><input type="text" size="5" name="tech_maint['.htmlspecialchars($type).']" value="'.htmlspecialchars((string)$maint).'"></td>';
		}
		else
			$html .= '<td></td><td></td>';

		$html .= '</tr>';
	}
	$html .= '</table><div style="font-size: smaller; margin-top: 4pt;">Blank range = infinite. Loss/build/maint blank uses defaults for that type.</div></td></tr>';
	return $html;
}

/**
 * HTML fragment for series-level SC3 knobs.
 */
function sc3SeriesLevelFormHtml($series = array(), $is_custom = false)
{
	$g = function ($key, $default) use ($series) {
		return htmlspecialchars((string)($series[$key] ?? $default));
	};
	$cap_note = $is_custom ? ' (max '.sc3MaxPlanetsCustomCap().')' : '';

	$html = '';
	$html .= '<tr><th style="text-align: right;">Max ag ratio:</th><td><input type=text size=6 name="max_ag_ratio" value="'.$g('max_ag_ratio', '100').'"></td></tr>';
	$html .= '<tr><th style="text-align: right;">Max ships (0=unlimited):</th><td><input type=text size=6 name="max_ships" value="'.$g('max_ships', '0').'"></td></tr>';
	$html .= '<tr><th style="text-align: right;">Initial tech:</th><td><input type=text size=6 name="initial_tech" value="'.$g('initial_tech', '1.0').'"></td></tr>';
	$html .= '<tr><th style="text-align: right;">Homeworld ag / min / fuel:</th><td>';
	$html .= '<input type=text size=4 name="hw_ag" value="'.$g('hw_ag', '100').'"> / ';
	$html .= '<input type=text size=4 name="hw_min" value="'.$g('hw_min', '100').'"> / ';
	$html .= '<input type=text size=4 name="hw_fuel" value="'.$g('hw_fuel', '100').'"></td></tr>';
	$html .= '<tr><th style="text-align: right;">Min pop to build:</th><td><input type=text size=5 name="min_pop_to_build" value="'.$g('min_pop_to_build', '50').'"></td></tr>';
	$html .= '<tr><th style="text-align: right;">Planet create cost:</th><td><input type=text size=5 name="planet_create_cost" value="'.$g('planet_create_cost', '90').'"></td></tr>';
	$html .= '<tr><th style="text-align: right;">Max planets:</th><td><input type=text size=5 name="max_planets" value="'.$g('max_planets', '1000').'">'.$cap_note.'</td></tr>';
	$html .= '<tr><th style="text-align: right;">Max allies:</th><td><input type=text size=5 name="max_allies" value="'.htmlspecialchars((string)($series['max_allies'] ?? '')).'"> (blank = unlimited; 0 = none; e.g. 1 for Galactic Powers)</td></tr>';
	$html .= '<tr><th style="text-align: right;">Max restricted techs:</th><td><input type=text size=5 name="max_restricted_techs" value="'.$g('max_restricted_techs', '0').'"></td></tr>';
	$html .= '<tr><th style="text-align: right;">Max trade-ins:</th><td><input type=text size=5 name="max_trade_ins" value="'.$g('max_trade_ins', '0').'"></td></tr>';
	return $html;
}

?>
