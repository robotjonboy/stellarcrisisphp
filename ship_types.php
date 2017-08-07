<?
# file: ship_types.php
# generate two arrays as shown below from the database
# this allows easier addition of ship types without programming

$ship_types = array();
$moving_ships = array();

$select = sc_mysql_query('SELECT * FROM ship_types WHERE version = "v2" ORDER BY type');
while ($row = mysql_fetch_array($select))
	{
	$ship_types[$row['type']] = $row;
	
	if ($row['mobile'])
		$moving_ships[] = $row['type'];
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
