<?php

//this function is called to process move, explore, and send orders
function move(&$series, &$game, &$explored_planets, &$history, &$missive, &$player, &$ship, 
	&$destinationCoordinates, &$action) {
	global $mysqli;

	// Update the ship's location.
  $ship['location'] = $destinationCoordinates;

  if ($action == 'explore' and !explored($player, $destinationCoordinates))
  {
    // Clean up any previous records. There really shouldn't be any, but while we're testing
    // the Shared HQ feature we need to be extra carefeul.
    $conditions = array();
    $conditions[] = 'player_id = "'.$player['id'].'"';
    $conditions[] = 'coordinates = "'.$destinationCoordinates.'"';

    sc_query('DELETE FROM explored WHERE '.implode(' AND ', $conditions));

    $values = array();
    $values[] = 'series_id = '.$series['id'];
    $values[] = 'game_number = '.$game['game_number'];
    $values[] = 'game_id = '.$game['id'];
    $values[] = 'empire = "'.$player['name'].'"';
    $values[] = 'player_id = "'.$player['id'].'"';
    $values[] = 'coordinates = "'.$destinationCoordinates.'"';
    $values[] = 'update_explored = "'.$game['update_count'].'"';

    sc_query('INSERT INTO explored SET '.implode(',', $values));

    // Share the wealth, if we have to.
    if ($series['diplomacy'] == 6)
      addExploredToFriends($player, $mysqli->insert_id);

    // Remove this planet from the scouting reports if it's there; we don't need it anymore.
    $conditions = array();
    $conditions[] = 'player_id = "'.$player['id'].'"';
    $conditions[] = 'coordinates = "'.$destinationCoordinates.'"';

    sc_query('DELETE FROM scouting_reports WHERE '.implode(' AND ', $conditions));

		// Save the coordinates for the update report.
    $explored_planets[$destinationCoordinates] = 1;
  }

	checkForFirstContact($series, $game, $player['name'], $destinationCoordinates, $history, $missive);
}

function checkForFirstContact($series, $game, $playerName, $destinationCoordinates, &$history, 
															&$missive) {
  $destination = getSystem($game['id'], $destinationCoordinates);

  if ($destination['owner'] != '' and $destination['owner'] != $playerName)
  {
    // So the system belongs to someone else... Did we make first contact?
    if (!$diplomacy = getDiplomacyWithOpponent($game['id'], $playerName, 
				$destination['owner'])) {
      // Yup, ship-to-system first contact.
      $fields = array();
      $fields[0] = 'series_id = '.$series['id'];
      $fields[1] = 'game_number = '.$game['game_number'];
      $fields[2] = 'game_id = '.$game['id'];
      $fields[3] = 'empire = "'.$playerName.'"';
      $fields[4] = 'opponent = "'.$destination['owner'].'"';

      sc_query('INSERT INTO diplomacies SET '.implode(',', $fields));

      $fields[3] = 'empire = "'.$destination['owner'].'"';
      $fields[4] = 'opponent = "'.$playerName.'"';

      sc_query('INSERT INTO diplomacies SET '.implode(',', $fields));

      $history[] = array($destination['coordinates'], $playerName, 'ship to system', 
				$destination['owner']);

      $missive[$destination['coordinates']]['first_contact'][$playerName][] =
        'You have had first contact with '.$destination['owner'].' in '.$destination['name'].
				' (*coord*) (ship to system).';

      $missive[$destination['coordinates']]['first_contact'][$destination['owner']][] =
      	'You have had first contact with '.$playerName.' in '.$destination['name'].
				' (*coord*) (system to ship).';
    }
  }
}

?>
