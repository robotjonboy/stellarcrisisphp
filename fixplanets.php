<?
# File:fixplanets.php
# this script is used to put long names in the systems to test
# page size problems.

   $game_number=2; # set this first!!!

   srand((double)10);   #microtime()*1000000); 

   $result = mysql_connect('localhost', 'mysql', 'mysql');
   if($result) 
   {
      echo "Connected to server: <p>";
   }
   else 
   {
      echo "Could not select db: <p>";
      die('MySQL connection error.');
   }

   $result = mysql_select_db('stellar');
   if($result) 
   {
      echo "Selected db <p>";
   }
   else 
   {
      echo "Could not select db<p>";
   }

   $select = mysql_query('SELECT * FROM systems WHERE game_number='."$game_number");
   while ($array = mysql_fetch_array($select))
   {

	$key=rand(0,1000);
	$name='planetplanetworld'.sprintf("%03d",$key);
	$query = 'UPDATE systems SET name = \''.$name.'\' where ID='."$array[0]";
	#if(strlen("$array[7]") == 0)
	echo substr("$array[7]",0,6).'<p>';
	if(!strncmp("$array[7]","planet",6))
	{
	   echo "$query".'<p>';
	   mysql_query($query);
	}
   }
?>
