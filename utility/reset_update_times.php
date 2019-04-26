<?
require('server.php');
require('serverconfig.php');
mysql_query('UPDATE games SET last_update = '.time());
mysql_query('UPDATE players SET ended_turn = "0"');
?>
