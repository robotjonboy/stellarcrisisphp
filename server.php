<?php
#echo( "server.php <p>");

// Get server variables from a separate file.
// SC_CONFIG_PATH (container / bind-mount) wins over the in-tree serverconfig.php.
$sc_config_path = getenv('SC_CONFIG_PATH');
if ($sc_config_path !== false && $sc_config_path !== '')
	include($sc_config_path);
else
	@include('serverconfig.php');
//@include('../serverconfig.php'); 
#echo "server.php <p>";

// connect to mysql
$mysqli = new mysqli($server['mysql_host'], $server['mysql_user'], $server['mysql_password'], $server['mysql_database']);

if ($mysqli->connect_errno) {
	echo "Failed to connect to MySQL: " . $mysqli->connect_error;
	die;
}

// Schema and pages are latin1 / ISO-8859-1. Pin the connection so escapes and
// stored names stay consistent (MySQL 8 often defaults to utf8mb4).
if (!$mysqli->set_charset('latin1')) {
	error_log('sc: failed to set mysqli charset latin1: '.$mysqli->error);
}

// Allow scripts to run for as long as they need to. You should comment this line out
// if your server is running in safe mode (it will throw an error otherwise).
set_time_limit(0);
?>
