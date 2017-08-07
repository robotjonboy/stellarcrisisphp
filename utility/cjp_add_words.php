<?php
# file: cjp_create_tables.php
# create tables needed by this game
# (also loads ship_types with static data)

# tables are in alphabetical order

echo "File: cjp_add_words.php <p>";
require('../serverconfig.php');
require('../server.php');

echo "<p>Starting to load words.<br>";

$i=0;
$d=0;
$l=0;
$words = fopen('cities.txt', 'r');
while ($word = fgets($words))
{
	$word = trim($word);
	$word = str_replace("\n", "", $word);
	
	if (strlen($word) > 2 and strlen($word) < 21)
	{
		$result = mysql_query('INSERT INTO words SET word = "'.$word.'"');
		echo $result;
		if ($result) 
		{
			echo $i.":  ".$word."  <br>" ;
			    $i =$i+1;
		}
		if (!$result) 
		{
			echo "Duplicate:  ".$word."  <br>" ;
			    $d =$d+1;
		}
	}
	else
	{
		echo "Dropped:  ".$word."  <br>" ;
		    $l =$l+1;

	}
//	if ($i > 25) break;
}

echo "<p>Done.<br>";
echo "Inserted: ".$i." words.<br>";
echo "Duplicate: ".$d." words.<br>";
echo "Wrong size: ".$l."<br>";
?>
