<?php
# file: cjp_create_tables.php
# create tables needed by this game
# (also loads ship_types with static data)

# tables are in alphabetical order

echo "File: cjp_create_tables.php <p>";
require('../serverconfig.php');
require('../server.php');

// Report all errors except E_NOTICE, with our own error handler.
#error_reporting(E_ALL ^ E_NOTICE);
#set_error_handler('sc_errorHandler');

#--------------------------------------------------------------------------------------------------------------------#
# Bridier
#
$fields = array();
$fields[] = 'id int(11) NOT NULL auto_increment';
$fields[] = 'game_id int(11) NOT NULL default "0"';
$fields[] = 'series_name varchar(20) NOT NULL default ""';
$fields[] = 'game_number int(11) NOT NULL default "0"';
$fields[] = 'start_time int(11) NOT NULL default "0"';
$fields[] = 'end_time int(11) NOT NULL default "0"';
$fields[] = 'empire1 varchar(20) NOT NULL default ""';
$fields[] = 'starting_rank1 smallint(6) NOT NULL default "0"';
$fields[] = 'starting_index1 smallint(6) NOT NULL default "0"';
$fields[] = 'ending_rank1 smallint(6) NOT NULL default "0"';
$fields[] = 'empire2 varchar(20) NOT NULL default ""';
$fields[] = 'starting_rank2 smallint(6) NOT NULL default "0"';
$fields[] = 'starting_index2 smallint(6) NOT NULL default "0"';
$fields[] = 'ending_rank2 smallint(6) NOT NULL default "0"';
$fields[] = 'winner tinyint(4) NOT NULL default "0"';
$fields[] = 'UNIQUE KEY id (id)';
$fields[] = 'KEY game_id (game_id,empire1,empire2)';
$fields[] = 'KEY end_time (end_time)';
$fields[] = 'KEY empire1 (empire1,empire2)';

$sql = 'CREATE TABLE IF NOT EXISTS bridier ('.implode(',', $fields).') TYPE=MyISAM;';
#echo $sql."<p>";
$result = mysql_query($sql);
#echo $result."<p>"; // 1 if table exists

#--------------------------------------------------------------------------------------------------------------------#
# Diplomacies
#
$fields = array();
$fields[] = 'id int(11) NOT NULL auto_increment';
$fields[] = 'series_id int(11) NOT NULL default "0"';
$fields[] = 'game_number int(11) NOT NULL default "0"';
$fields[] = 'game_id int(11) NOT NULL default "0"';
$fields[] = 'empire varchar(20) NOT NULL default ""';
$fields[] = 'opponent varchar(20) NOT NULL default ""';
$fields[] = 'offer enum("6","5","4","3","2","1","0") NOT NULL default "2"';
$fields[] = 'status enum("6","5","4","3","2","1","0") NOT NULL default "2"';
$fields[] = 'PRIMARY KEY (game_id,empire,opponent)';
$fields[] = 'UNIQUE KEY id (id)';

mysql_query('CREATE TABLE IF NOT EXISTS diplomacies ('.implode(',', $fields).') TYPE=MyISAM;');

#--------------------------------------------------------------------------------------------------------------------#
# Empires
#
$fields = array();
$fields[] = 'id int(11) NOT NULL auto_increment';
$fields[] = 'name varchar(20) NOT NULL default ""';

$fields[] = 'auto_update enum("1","0") NOT NULL default "1"';
$fields[] = 'background_attachment enum("scroll","fixed") NOT NULL default "scroll"';
$fields[] = 'bridier_delta tinyint(4) NOT NULL default "0"';
$fields[] = 'bridier_index smallint(6) NOT NULL default "500"';
$fields[] = 'bridier_rank smallint(6) NOT NULL default "500"';
$fields[] = 'bridier_update int(11) NOT NULL default "0"';
$fields[] = 'can_create_custom_series enum("1","0") NOT NULL default "0"';
$fields[] = 'comment text NOT NULL';
$fields[] = 'custom_bg_url tinytext';
$fields[] = 'draw_background enum("1","0") NOT NULL default "1"';
$fields[] = 'email tinytext NOT NULL default ""';
$fields[] = 'email_visible enum("0","1") NOT NULL default "0"'; //cjp
$fields[] = 'icon varchar(64) NOT NULL default "alien1.gif"';
$fields[] = 'is_admin enum("0","1") NOT NULL default "0"';      //cjp
$fields[] = 'join_date int(11) NOT NULL default "1018409756"';
$fields[] = 'last_ip varchar(15) NOT NULL default ""';
$fields[] = 'last_login int(11) NOT NULL default "0"';
$fields[] = 'list_ships_by_system enum("1","0") NOT NULL default "0"';
$fields[] = 'map_origin varchar(11) NOT NULL default "0,0"';
$fields[] = 'max_economic_power int(11) NOT NULL default "0"';
$fields[] = 'max_military_power int(11) NOT NULL default "0"';
$fields[] = 'nuked smallint(6) NOT NULL default "0"';
$fields[] = 'nukes smallint(6) NOT NULL default "0"';
$fields[] = 'password varchar(20) NOT NULL default ""';
$fields[] = 'real_name tinytext NOT NULL default ""';
$fields[] = 'ruined smallint(6) NOT NULL default "0"';
$fields[] = 'show_coordinates enum("1","0") NOT NULL default "1"';
$fields[] = 'show_icons enum("1","0") NOT NULL default "0"';
$fields[] = 'tos_accepted enum("0","1") NOT NULL default "0"';  //cjp - accept terms of service
$fields[] = 'url tinytext';
$fields[] = 'validation_info varchar(50) NOT NULL default ""';  //cjp - emailed check key
$fields[] = 'wins smallint(6) NOT NULL default "0"';

$fields[] = 'PRIMARY KEY  (name,password)';
$fields[] = 'UNIQUE KEY id (id)';
$fields[] = 'KEY bridier_index (bridier_index)';

$sql = 'CREATE TABLE IF NOT EXISTS empires ('.implode(',', $fields).') TYPE=MyISAM;';
$result = mysql_query($sql);

if (!$result)
{
	echo 'Failed to create table empires.<br/>';
	echo 'sql: ' . $sql . '<br/>';
	echo 'mysql error: ' . mysql_error() . '<br/>';
}
else
{
	echo 'Table Empires created or already exists.<br/>';
}

mysql_query('INSERT INTO empires set name="admin" password="admin" is_admin="1"');

#--------------------------------------------------------------------------------------------------------------------#
# Explored planets
#
$fields = array();
$fields[] = 'id int(11) NOT NULL auto_increment';
$fields[] = 'series_id int(11) NOT NULL default "0"';
$fields[] = 'game_number smallint(6) NOT NULL default "0"';
$fields[] = 'game_id int(11) NOT NULL default "0"';
$fields[] = 'coordinates varchar(12) NOT NULL default ""';
$fields[] = 'empire varchar(20) NOT NULL default ""';
$fields[] = 'player_id int(11) NOT NULL default "0"';
$fields[] = 'update_explored smallint(6) NOT NULL default "0"';
$fields[] = 'from_shared_hq int(11) NOT NULL default "0"';
$fields[] = 'PRIMARY KEY  (game_id,empire,coordinates)';
$fields[] = 'UNIQUE KEY id (id)';
$fields[] = 'KEY series_id (series_id,game_number)';
$fields[] = 'KEY player_id (player_id,coordinates)';

mysql_query('CREATE TABLE IF NOT EXISTS explored ('.implode(',', $fields).') TYPE=MyISAM;');

#--------------------------------------------------------------------------------------------------------------------#
# Fleets
#
$fields = array();
$fields[] = 'id int(11) NOT NULL auto_increment';
$fields[] = 'series_id int(11) NOT NULL default "0"';
$fields[] = 'game_number smallint(6) NOT NULL default "0"';
$fields[] = 'game_id int(11) NOT NULL default "0"';
$fields[] = 'collapsed enum("1","0") NOT NULL default "0"';
$fields[] = 'location tinytext NOT NULL default ""';
$fields[] = 'name varchar(20) NOT NULL default ""';
$fields[] = 'order_arguments tinytext NOT NULL';
$fields[] = 'orders varchar(20) NOT NULL default ""';
$fields[] = 'owner varchar(20) NOT NULL default ""';
$fields[] = 'player_id int(11) NOT NULL default "0"';
$fields[] = 'UNIQUE KEY id (id)';
$fields[] = 'KEY series_id (series_id,game_number)';
$fields[] = 'KEY game_id (game_id)';

mysql_query('CREATE TABLE IF NOT EXISTS fleets ('.implode(',', $fields).') TYPE=MyISAM;');

# --------------------------------------------------------
#
# gamelog
#
$sql = 'CREATE TABLE IF NOT EXISTS gamelog (
  id int(11) NOT NULL auto_increment,
  bridier enum("yes","no") NOT NULL default "no",
  emps_left text,
  emps_nuked text,
  end_date datetime default NULL,
  name tinytext NOT NULL default "",
  result enum("win","draw","adandoned","no winner") default NULL,
  PRIMARY KEY (id)
) TYPE=MyISAM;';
#echo $sql."<p>";
$result = mysql_query($sql);

if (!$result)
{
	echo 'Error creating table gamelog<br/>';
	echo 'sql: ' . $sql . '<br/>';
	echo 'mysql error: ' . mysql_error() . '<br/>';
}
else
{
	echo 'Table gamelog created or already exists.<br/>';
}

# --------------------------------------------------------
#
# games
#
$sql = 'CREATE TABLE IF NOT EXISTS games (
  id int(11) NOT NULL auto_increment,
  series_id int(11) NOT NULL default "0",
  game_number smallint(6) NOT NULL default "0",
  
  avg_ag smallint(6) NOT NULL default "0",
  avg_fuel smallint(6) NOT NULL default "0",
  avg_min smallint(6) NOT NULL default "0",
  bridier tinyint(4) NOT NULL default "-1",
  closed enum("1","0") NOT NULL default "0",
  created_at int(11) NOT NULL default "0",
  created_by varchar(20) NOT NULL default "",
  diplomacy enum("6","5","4","3","2","1","0") NOT NULL default "2",
  last_update int(11) NOT NULL default "0",
  max_allies tinyint(3) unsigned default NULL,
  on_hold enum("1","0") NOT NULL default "0",
  password1 varchar(10) NOT NULL default "",
  password2 varchar(10) NOT NULL default "",
  player_count int(11) NOT NULL default "0",
  processing tinyint(4) NOT NULL default "0",
  update_count int(11) NOT NULL default "0",
  update_time int(11) NOT NULL default "0",
  updating enum("0","1") NOT NULL default "0",
  version varchar(20) NOT NULL default "",
  weekend_updates enum("1","0") NOT NULL default "1",
  PRIMARY KEY  (series_id,game_number),
  UNIQUE KEY id (id)
) TYPE=MyISAM;';
$result = mysql_query($sql);

# --------------------------------------------------------
#
# history
#
$sql = 'CREATE TABLE IF NOT EXISTS history (
  id int(11) NOT NULL auto_increment,
  game_id int(11) NOT NULL default "0",
  empire varchar(20) NOT NULL default "",
  coordinates varchar(12) NOT NULL default "",
  event enum("started", "bridier", "update", "Truce", "Trade", "War", "Alliance", "empire", "unknown", "ship to ship", "ship to system", "destroyed", "sighted", "minefield", "nuked", "annihilated", "invaded", "unsucessfully invaded", "colonized", "terraformed", "opened", "closed", "joined", "nuked out", "invaded out", "annihilated out", "ruins", "surrender", "draw", "won") NOT NULL default "unknown",
  info varchar(255) NOT NULL default "",
  update_no smallint(6) NOT NULL default "0",
  UNIQUE KEY id (id),
  KEY game_id (game_id),
  KEY empire (empire)
) TYPE=MyISAM;';
$result = mysql_query($sql);

# --------------------------------------------------------
#
# invitations
#
$sql = 'CREATE TABLE IF NOT EXISTS invitations (
  id int(11) NOT NULL auto_increment,
  series_id int(11) NOT NULL default "0",
  game_number int(11) NOT NULL default "0",
  empire varchar(20) NOT NULL default "",
  game_id int(11) NOT NULL default "0",  
  message mediumtext,
  status enum("Accepted","Declined","None") NOT NULL default "None",
  team int(4) NOT NULL default 0, 
  PRIMARY KEY  (series_id,game_number,empire),
  UNIQUE KEY id (id)
) TYPE=MyISAM;';
# brad's keys =
#  PRIMARY KEY  (`id`),
#  UNIQUE KEY `game_id` (`game_id`,`empire`)

$result = mysql_query($sql);
if (!$result)
{
	echo 'Error creating table invitations.<br/>';
	echo 'sql: ' . $sql . '<br/>';
	echo 'mysql error: ' . mysql_error() . '<br/>';
}
else
{
	echo 'Table invitations created or already exists.<br/>';
}

# --------------------------------------------------------
#
# messages
#
$sql = 'CREATE TABLE IF NOT EXISTS messages (
  id int(11) NOT NULL auto_increment,
  time int(11) NOT NULL default "0",
  sender varchar(20) NOT NULL default "",
  recipient text,
  player_id int(11) NOT NULL default "0",
  empire_id int(11) NOT NULL default "0",
  text mediumtext NOT NULL,
  type enum("instant","motd","private","broadcast","team","update","scout") default NULL,
  flag enum("1","0") NOT NULL default "0",
  UNIQUE KEY id (id),
  KEY player_id (player_id,flag,type),
  KEY type (type),
  KEY empire_id (empire_id)
) TYPE=MyISAM;';
$result = mysql_query($sql);

# --------------------------------------------------------
#
# players
#
$sql = 'CREATE TABLE IF NOT EXISTS players (
  id int(11) NOT NULL auto_increment,
  name varchar(20) NOT NULL default "",
  series_id int(11) NOT NULL default "0",
  game_number smallint(6) NOT NULL default "0",
  game_id int(11) NOT NULL default "0",
  
  agriculture int(11) NOT NULL default "0",
  agriculture_ratio float NOT NULL default "1",
  build int(11) NOT NULL default "0",
  economic_power int(11) NOT NULL default "0",
  ended_turn enum("1","0") NOT NULL default "0",
  fuel int(11) NOT NULL default "0",
  fuel_ratio float default NULL,
  fuel_use smallint(6) NOT NULL default "0",
  ip varchar(15) NOT NULL default "",
  last_access int(11) NOT NULL default "0",
  last_update smallint(6) NOT NULL default "0",
  maintenance int(11) NOT NULL default "0",
  map_origin varchar(11) NOT NULL default "0,0",
  max_population int(11) NOT NULL default "0",
  military_power int(11) NOT NULL default "0",
  mineral int(11) NOT NULL default "0",
  mineral_ratio float default NULL,
  notes text,
  population int(11) NOT NULL default "0",
  team tinyint(4) NOT NULL default "0",
  team_spot varchar(20) NOT NULL default "",
  tech_development float NOT NULL default "0",
  tech_level float NOT NULL default "0",
  techs text NOT NULL,
  traded_in smallint(6) NOT NULL default "0",
  PRIMARY KEY  (series_id,game_number,name),
  UNIQUE KEY id (id),
  KEY game_id (game_id,name)
) TYPE=MyISAM;';
$result = mysql_query($sql);
# --------------------------------------------------------
#
# scouting_reports
#
$sql = 'CREATE TABLE IF NOT EXISTS scouting_reports (
  id int(11) NOT NULL auto_increment,
  player_id int(11) NOT NULL default "0",
  agriculture smallint(6) NOT NULL default "0",
  annihilated enum("1","0") NOT NULL default "0",
  comment text,
  coordinates varchar(11) NOT NULL default "",
  fuel smallint(6) NOT NULL default "0",
  jumps varchar(47) NOT NULL default "",
  mineral smallint(6) NOT NULL default "0",
  name varchar(30) NOT NULL default "",
  owner varchar(20) NOT NULL default "",
  population smallint(6) NOT NULL default "0",
  ships text,
  UNIQUE KEY id (id),
  UNIQUE KEY player_id (player_id,coordinates)
) TYPE=MyISAM;';
$result = mysql_query($sql);

# --------------------------------------------------------
#
# series
#
// try to keep most fields in alpha order
$sql = 'CREATE TABLE IF NOT EXISTS series (
  id int(11) NOT NULL auto_increment,
  name varchar(40) NOT NULL default "",

  average_resources smallint(6) NOT NULL default "30",
  avg_ag smallint(6) NOT NULL default "30",
  avg_fuel smallint(6) NOT NULL default "30",
  avg_min smallint(6) NOT NULL default "30",
  bridier_allowed enum("1","0") NOT NULL default "0",
  build_cloakers_cloaked enum("1","0") NOT NULL default "0",
  can_draw enum("1","0") NOT NULL default "0",
  can_surrender enum("1","0") NOT NULL default "0",
  cloakers_as_attacks enum("1","0") NOT NULL default "0",
  creator varchar(20) NOT NULL default "Admin",
  custom enum("0","1") NOT NULL default "0",
  diplomacy enum("6","5","4","3","2","1","0") NOT NULL default "2",
  game_count smallint(6) NOT NULL default "0",
  halted enum("1","0") NOT NULL default "0",
  map_compression float NOT NULL default "0.001",
  map_type enum("standard","prebuilt","twisted","mirror","balanced") NOT NULL default "standard",
  map_visible enum("0","1") NOT NULL default "0",
  max_players smallint(6) NOT NULL default "0",
  max_wins smallint(6) NOT NULL default "-1",
  min_wins smallint(6) NOT NULL default "0",
  systems_per_player tinyint(4) NOT NULL default "0",
  team_game enum("1","0") NOT NULL default "0",
  tech_multiple float NOT NULL default "0",
  update_time int(11) NOT NULL default "0",
  visible_builds enum("1","0") NOT NULL default "0",
  weekend_updates enum("1","0") NOT NULL default "1",  PRIMARY KEY  (name),
  UNIQUE KEY id (id)
) TYPE=MyISAM;';
$result = mysql_query($sql);

#
#  data for table `series`
#
$values = array();

$values[] = 'name = "Iceberg"';
// fields in alpha order
$values[] = 'average_resources = "30"';
$values[] = 'avg_ag = "30"';
$values[] = 'avg_fuel = "30"';
$values[] = 'avg_min = "30"';
$values[] = 'bridier_allowed = "0"'; //if bridier then max_players = 2
$values[] = 'build_cloakers_cloaked = "1"';
$values[] = 'can_draw = "1"';
$values[] = 'can_surrender = "1"';
$values[] = 'cloakers_as_attacks = "1"';
$values[] = 'creator = "Admin"';
$values[] = 'custom = "0"';
$values[] = 'description = mediumtext';
$values[] = 'diplomacy` = "5"';
$values[] = 'game_count smallint(6) = "0"';
$values[] = 'halted = "0"';
$values[] = 'map_compression = ".001"'; //must be between .001 & .8 !!
$values[] = 'map_type = "standard"';
$values[] = 'map_visible = "1"';
$values[] = 'max_allies = "3"';
$values[] = 'max_players = "16"';
$values[] = 'max_wins = ""';
$values[] = 'min_wins = ""';
$values[] = 'systems_per_player = "15"';
$values[] = 'team_game = "0"';
$values[] = 'tech_multiple = "3"';
$values[] = 'update_time = "86400"';
$values[] = 'visible_builds = "1"';
$values[] = 'weekend_updates = "0"';

$result = mysql_query('INSERT INTO series SET '.implode(',', $values));

# --------------------------------------------------------
#
# ships
#
$sql = 'CREATE TABLE IF NOT EXISTS ships (
  id int(11) NOT NULL auto_increment,
  br float NOT NULL default "0",
  build_cost smallint(6) NOT NULL default "0",
  cloaked enum("1","0") NOT NULL default "0",
  fleet_id int(11) NOT NULL default "0",
  fuel_cost smallint(6) NOT NULL default "0",
  game_id int(11) NOT NULL default "0",
  game_number smallint(6) NOT NULL default "0",
  location varchar(20) NOT NULL default "",
  maintenance_cost smallint(6) NOT NULL default "0",
  max_br float NOT NULL default "0",
  name varchar(20) NOT NULL default "",
  order_arguments varchar(20) NOT NULL default "",
  orders varchar(20) NOT NULL default "",
  owner varchar(20) NOT NULL default "",
  player_id int(11) NOT NULL default "0",
  series_id int(11) NOT NULL default "0",
  type varchar(20) NOT NULL default "",
  UNIQUE KEY id (id),
  KEY series_id (series_id,game_number),
  KEY owner (game_id,owner),
  KEY location (game_id,location),
  KEY type (fleet_id,type)
) TYPE=MyISAM;';
$result = mysql_query($sql);

# --------------------------------------------------------
#
# ship_types
#
$sql = 'CREATE TABLE IF NOT EXISTS ship_types (
  id int(11) NOT NULL auto_increment,
  type varchar(20) NOT NULL default "",
  mobile enum("1","0") NOT NULL default "1",
  version varchar(20) NOT NULL default "v2",
  PRIMARY KEY  (id),
  UNIQUE KEY type (type)
) TYPE=MyISAM;';
$result = mysql_query($sql);

$sql = 'insert into ship_types set type="Attack", mobile="1"';
mysql_query($sql);
$sql = 'insert into ship_types set type="Science", mobile="1"';
mysql_query($sql);
$sql = 'insert into ship_types set type="Colony", mobile="1"';
mysql_query($sql);
$sql = 'insert into ship_types set type="Stargate", mobile="0"';
mysql_query($sql);
$sql = 'insert into ship_types set type="Cloaker", mobile="1"';
mysql_query($sql);
$sql = 'insert into ship_types set type="Satellite", mobile="0"';
mysql_query($sql);
$sql = 'insert into ship_types set type="Terraformer", mobile="1"';
mysql_query($sql);
$sql = 'insert into ship_types set type="Troopship", mobile="1"';
mysql_query($sql);
$sql = 'insert into ship_types set type="Doomsday", mobile="1"';
mysql_query($sql);
$sql = 'insert into ship_types set type="Minefield", mobile="0"';
mysql_query($sql);
$sql = 'insert into ship_types set type="Minesweeper", mobile="1"';
mysql_query($sql);
$sql = 'insert into ship_types set type="Engineer", mobile="1"';
mysql_query($sql);

# --------------------------------------------------------
#
# systems
#
$sql = 'CREATE TABLE IF NOT EXISTS systems (
  id int(11) NOT NULL auto_increment,
  agriculture int(11) NOT NULL default "0",
  annihilated enum("1","0") NOT NULL default "0",
  coordinates varchar(11) NOT NULL default "",
  fuel int(11) NOT NULL default "0",
  game_id int(11) NOT NULL default "0",
  game_number smallint(6) NOT NULL default "0",
  homeworld varchar(20) NOT NULL default "",
  jumps varchar(47) NOT NULL default "",
  max_population int(11) NOT NULL default "0",
  mineral int(11) NOT NULL default "0",
  name varchar(20) NOT NULL default "",
  owner varchar(20) NOT NULL default "",
  player_number smallint(6) NOT NULL default "0",
  population int(11) NOT NULL default "0",
  series_id int(11) NOT NULL default "0",
  system_active enum("0","1") NOT NULL default "1",
  PRIMARY KEY  (game_id,coordinates),
  UNIQUE KEY id (id),
  KEY series_id (series_id,game_number)
) TYPE=MyISAM;';
$result = mysql_query($sql);

# --------------------------------------------------------
#
# words
#
$sql = 'CREATE TABLE IF NOT EXISTS words (
   id int(11) NOT NULL auto_increment,
   word varchar(20) NOT NULL default "", 
   PRIMARY KEY  (id),
   UNIQUE KEY  (word)
) TYPE=MyISAM;';
$result = mysql_query($sql);
// varchar(20) to match size of empire names(20)

if (!$result)
{
	echo 'Error creating table words.<br/>';
	echo 'sql: ' . $sql . '<br/>';
	echo 'mysql error: ' . mysql_error() . '<br/>';
}
else
{
	echo 'Table words created or already exists.';
}

?>
