<?php
# file: ship_types.php
# generate two arrays as shown below from the database
# this allows easier addition of ship types without programming

$ship_types = array();
$moving_ships = array();

$select = sc_query('SELECT * FROM ship_types WHERE version = "v2" ORDER BY type');
while ($row = $select->fetch_assoc())
	{
	$ship_types[$row['type']] = $row;
	
	if ($row['mobile'])
		$moving_ships[] = $row['type'];
	}

$v3_ship_types = array();
$v3_moving_ships = array();
	
$select = sc_query('SELECT * FROM ship_types WHERE version = "v3" ORDER BY type');
while ($row = $select->fetch_assoc())
	{
	$v3_ship_types[$row['type']] = $row;
	
	if ($row['mobile'])
		$v3_moving_ships[] = $row['type'];
	}

/*
$ship_types = array(   'Attack', 
			 'Science', 
			 'Colony', 
			 'Stargate', 
			 'Cloaker', 
			 'Satellite', 
			 'Terraformer', 
			 'Troopship', 
			 'Doomsday', 
			 'Minefield', 
			 'Minesweeper', 
			 'Engineer' );

$moving_ships = array(	   'Attack', 
			   'Science', 
			   'Colony', 
			   'Cloaker', 
			   'Terraformer', 
			   'Troopship', 
			   'Doomsday', 
			   'Minesweeper', 
			   'Engineer');
*/

?>
