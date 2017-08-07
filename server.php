<?php
#echo( "server.php <p>");

// Get server variables from seperate file. -depends where called from
@include('serverconfig.php'); 
@include('../serverconfig.php'); 
#echo "server.php <p>";

// connect to mysql
$result = mysql_connect($server['mysql_host'], $server['mysql_user'], $server['mysql_password']);
if($result) 
{
#   echo "Connected to server: ".$server['mysql_host']."<p>";
}
else 
{
#   echo "Could not select db: ".$server['mysql_host']."<p>";
   die('MySQL connection error.');
}

// Select (or create) stellar database
$result = mysql_select_db($server['mysql_database']);
if($result) 
{
#   echo "Selected db: ".$server['mysql_database']."<p>";
}
else 
{
#   echo "Could not select db: ".$server['mysql_database']."<p>";
   // then create it or die
#   echo "Attempting to create it.<p>";
   $result = mysql_query("CREATE DATABASE ".$server['mysql_database']);
   if($result)
   {
#      echo "Created db: ".$server['mysql_database']."<p>";
      $result = mysql_select_db($server['mysql_database']);
      if(!$result)
      {
#         echo "Could not select db: ".$server['mysql_database']."<p>";
         die('MySQL database selection error.');
      }
   }
   else
   {
#      echo "Could not create db: ".$server['mysql_database']."<p>";
      die('MySQL database createion error.');
   }
}

// Allow scripts to run for as long as they need to. You should comment this line out
// if your server is running in safe mode (it will throw an error otherwise).
set_time_limit(0);
?>
