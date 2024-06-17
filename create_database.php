<?php
echo "create_tables.php <p>";
require('server.php');
echo "create_tables.php <p>";
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

mysql_query('CREATE TABLE bridier ('.implode(',', $fields).') TYPE=MyISAM;') or die('SQL CREATE error for table "bridier".');

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

mysql_query('CREATE TABLE diplomacies ('.implode(',', $fields).') TYPE=MyISAM;') or die('SQL CREATE error for table "diplomacies".');

#--------------------------------------------------------------------------------------------------------------------#
# Explored planets
#

$fields = array();
$fields[] = 'id int(11) NOT NULL auto_increment';
$fields[] = 'series_id int(11) NOT NULL default "0"';
$fields[] = 'game_number smallint(6) NOT NULL default "0"';
$fields[] = 'game_id int(11) NOT NULL default "0"';
$fields[] = 'empire varchar(20) NOT NULL default ""';
$fields[] = 'player_id int(11) NOT NULL default "0"';
$fields[] = 'coordinates varchar(12) NOT NULL default ""';
$fields[] = 'update_explored smallint(6) NOT NULL default "0"';
$fields[] = 'from_shared_hq enum("0","1") NOT NULL default "0"';
$fields[] = 'PRIMARY KEY  (player_id,coordinates)';
$fields[] = 'UNIQUE KEY id (id)';
$fields[] = 'KEY series_id (series_id,game_number)';

mysql_query('CREATE TABLE explored ('.implode(',', $fields).') TYPE=MyISAM;') or die('SQL CREATE error for table "explored".');

#--------------------------------------------------------------------------------------------------------------------#
# Fleets
#

$fields = array();
$fields[] = 'id int(11) NOT NULL auto_increment';
$fields[] = 'series_id int(11) NOT NULL default "0"';
$fields[] = 'game_number smallint(6) NOT NULL default "0"';
$fields[] = 'game_id int(11) NOT NULL default "0"';
$fields[] = 'name varchar(20) NOT NULL default ""';
$fields[] = 'owner varchar(20) NOT NULL default ""';
$fields[] = 'location varchar(20) NOT NULL default ""';
$fields[] = 'orders varchar(20) NOT NULL default ""';
$fields[] = 'collapsed enum("1","0") NOT NULL default "0"';
$fields[] = 'UNIQUE KEY id (id)';
$fields[] = 'KEY series_id (series_id,game_number)';
$fields[] = 'KEY game_id (game_id)';

mysql_query('CREATE TABLE fleets ('.implode(',', $fields).') TYPE=MyISAM;') or die('SQL CREATE error for table "fleets".');

#--------------------------------------------------------------------------------------------------------------------#
# Empires
#

$fields = array();
$fields[] = 'id int(11) NOT NULL auto_increment';
$fields[] = 'name varchar(20) NOT NULL default ""';
$fields[] = 'password varchar(20) NOT NULL default ""';
$fields[] = 'real_name varchar(50) NOT NULL default ""';
$fields[] = 'email varchar(50) NOT NULL default ""';
$fields[] = 'icon varchar(64) NOT NULL default "alien1.gif"';
$fields[] = 'comment text NOT NULL';
$fields[] = 'wins smallint(6) NOT NULL default "0"';
$fields[] = 'nukes smallint(6) NOT NULL default "0"';
$fields[] = 'nuked smallint(6) NOT NULL default "0"';
$fields[] = 'ruined smallint(6) NOT NULL default "0"';
$fields[] = 'bridier_rank smallint(6) NOT NULL default "500"';
$fields[] = 'bridier_index smallint(6) NOT NULL default "500"';
$fields[] = 'bridier_update int(11) NOT NULL default "0"';
$fields[] = 'max_economic_power int(11) NOT NULL default "0"';
$fields[] = 'max_military_power int(11) NOT NULL default "0"';
$fields[] = 'last_login int(11) NOT NULL default "0"';
$fields[] = 'last_ip varchar(15) NOT NULL default ""';
$fields[] = 'auto_update enum("1","0") NOT NULL default "1"';
$fields[] = 'show_coordinates enum("1","0") NOT NULL default "1"';
$fields[] = 'map_origin varchar(11) NOT NULL default "0,0"';
$fields[] = 'show_icons enum("1","0") NOT NULL default "0"';
$fields[] = 'join_date int(11) NOT NULL default "1018409756"';
$fields[] = 'PRIMARY KEY  (name,password)';
$fields[] = 'UNIQUE KEY id (id)';
$fields[] = 'KEY bridier_index (bridier_index)';

mysql_query('CREATE TABLE empires ('.implode(',', $fields).') TYPE=MyISAM;') or die('SQL CREATE error for table "empires".');

# --------------------------------------------------------

#
# Table structure for table `gamelog`
#
echo "hello <p>";
?>
CREATE TABLE gamelog (
  id int(11) NOT NULL auto_increment,
  name varchar(30) NOT NULL default "",
  result enum("win","draw","adandoned","no winner") default NULL,
  emps_left text,
  emps_nuked text,
  bridier enum("yes","no") NOT NULL default "no",
  end_date datetime default NULL,
  PRIMARY KEY  (id)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `games`
#

CREATE TABLE games (
  id int(11) NOT NULL auto_increment,
  series_id int(11) NOT NULL default "0",
  game_number smallint(6) NOT NULL default "0",
  created_by varchar(20) NOT NULL default "",
  created_at int(11) NOT NULL default "0",
  password1 varchar(10) NOT NULL default "",
  password2 varchar(10) NOT NULL default "",
  last_update int(11) NOT NULL default "0",
  update_count int(11) NOT NULL default "0",
  player_count int(11) NOT NULL default "0",
  closed enum("1","0") NOT NULL default "0",
  weekend_updates enum("1","0") NOT NULL default "1",
  update_time int(11) NOT NULL default "0",
  version varchar(20) NOT NULL default "",
  bridier tinyint(4) NOT NULL default "-1",
  processing tinyint(4) NOT NULL default "0",
  updating enum("0","1") NOT NULL default "0",
  PRIMARY KEY  (series_id,game_number),
  UNIQUE KEY id (id)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `history`
#

CREATE TABLE history (
  id int(11) NOT NULL auto_increment,
  game_id int(11) NOT NULL default "0",
  update_no smallint(6) NOT NULL default "0",
  coordinates varchar(12) NOT NULL default "",
  empire varchar(20) NOT NULL default "",
  event enum("started","bridier","update","Truce","Trade","War","Alliance","empire","unknown","ship to ship","ship to system","destroyed","sighted","minefield","nuked","annihilated","invaded","unsucessfully invaded","colonized","terraformed","opened","closed","joined","nuked out","invaded out","annihilated out","ruins","surrender","draw","won") NOT NULL default "unknown",
  info varchar(255) NOT NULL default "",
  UNIQUE KEY id (id),
  KEY game_id (game_id),
  KEY empire (empire)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `invitations`
#

CREATE TABLE invitations (
  id int(11) NOT NULL auto_increment,
  series_id int(11) NOT NULL default "0",
  game_number int(11) NOT NULL default "0",
  game_id int(11) NOT NULL default "0",
  empire varchar(20) NOT NULL default "",
  team int(4) NOT NULL default "0",
  status enum("Accepted","Declined","None") NOT NULL default "None",
  message mediumtext,
  PRIMARY KEY  (series_id,game_number,empire),
  UNIQUE KEY id (id)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `messages`
#

CREATE TABLE messages (
  id int(11) NOT NULL auto_increment,
  time int(11) NOT NULL default "0",
  sender varchar(20) NOT NULL default "",
  recipient text,
  player_id int(11) NOT NULL default "0",
  empire_id int(11) NOT NULL default "0",
  text mediumtext NOT NULL,
  type enum("instant","motd","private","broadcast","team","update","scout","game_message") default NULL,
  flag enum("1","0") NOT NULL default "0",
  UNIQUE KEY id (id),
  KEY player_id (player_id,flag,type),
  KEY type (type),
  KEY empire_id (empire_id)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `players`
#

CREATE TABLE players (
  id int(11) NOT NULL auto_increment,
  name varchar(20) NOT NULL default "",
  series_id int(11) NOT NULL default "0",
  game_number smallint(6) NOT NULL default "0",
  game_id int(11) NOT NULL default "0",
  team tinyint(4) NOT NULL default "0",
  team_spot varchar(20) NOT NULL default "",
  map_origin varchar(11) NOT NULL default "0,0",
  mineral int(11) NOT NULL default "0",
  fuel int(11) NOT NULL default "0",
  agriculture int(11) NOT NULL default "0",
  population int(11) NOT NULL default "0",
  max_population int(11) NOT NULL default "0",
  mineral_ratio float NOT NULL default "0",
  fuel_ratio float NOT NULL default "0",
  agriculture_ratio float NOT NULL default "0",
  maintenance int(11) NOT NULL default "0",
  build smallint(6) NOT NULL default "0",
  fuel_use smallint(6) NOT NULL default "0",
  tech_development float NOT NULL default "0",
  tech_level float NOT NULL default "0",
  techs text NOT NULL,
  economic_power int(11) NOT NULL default "0",
  military_power int(11) NOT NULL default "0",
  last_access int(11) NOT NULL default "0",
  last_update smallint(6) NOT NULL default "0",
  ended_turn enum("1","0") NOT NULL default "0",
  ip varchar(15) NOT NULL default "",
  PRIMARY KEY  (series_id,game_number,name),
  UNIQUE KEY id (id),
  KEY game_id (game_id,name)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `scouting_reports`
#

CREATE TABLE scouting_reports (
  id int(11) NOT NULL auto_increment,
  player_id int(11) NOT NULL default "0",
  coordinates varchar(11) NOT NULL default "",
  jumps varchar(47) NOT NULL default "",
  name varchar(30) NOT NULL default "",
  owner varchar(20) NOT NULL default "",
  mineral smallint(6) NOT NULL default "0",
  fuel smallint(6) NOT NULL default "0",
  agriculture smallint(6) NOT NULL default "0",
  population smallint(6) NOT NULL default "0",
  annihilated enum("1","0") NOT NULL default "0",
  ships text,
  comment text,
  UNIQUE KEY id (id)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `series`
#

CREATE TABLE series (
  id int(11) NOT NULL auto_increment,
  name varchar(40) NOT NULL default "",
  update_time int(11) NOT NULL default "0",
  weekend_updates enum("1","0") NOT NULL default "1",
  diplomacy enum("6","5","4","3","2","1","0") NOT NULL default "2",
  max_players smallint(6) NOT NULL default "0",
  map_type enum("standard","prebuilt","twisted","mirror","balanced") NOT NULL default "standard",
  map_visible enum("0","1") NOT NULL default "0",
  systems_per_player tinyint(4) NOT NULL default "0",
  tech_multiple float NOT NULL default "0",
  average_resources smallint(6) NOT NULL default "30",
  min_wins smallint(6) NOT NULL default "0",
  max_wins smallint(6) NOT NULL default "-1",
  game_count smallint(6) NOT NULL default "0",
  can_draw enum("1","0") NOT NULL default "0",
  can_surrender enum("1","0") NOT NULL default "0",
  visible_builds enum("1","0") NOT NULL default "0",
  bridier_allowed enum("1","0") NOT NULL default "0",
  halted enum("1","0") NOT NULL default "0",
  team_game enum("1","0") NOT NULL default "0",
  map_compression float NOT NULL default "0.001",
  PRIMARY KEY  (name),
  UNIQUE KEY id (id)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `ships`
#

CREATE TABLE ships (
  id int(11) NOT NULL auto_increment,
  fleet_id int(11) NOT NULL default "0",
  series_id int(11) NOT NULL default "0",
  game_number smallint(6) NOT NULL default "0",
  game_id int(11) NOT NULL default "0",
  name varchar(20) NOT NULL default "",
  location varchar(20) NOT NULL default "",
  owner varchar(20) NOT NULL default "",
  br float NOT NULL default "0",
  max_br float NOT NULL default "0",
  type varchar(20) NOT NULL default "",
  orders varchar(20) NOT NULL default "",
  build_cost smallint(6) NOT NULL default "0",
  maintenance_cost smallint(6) NOT NULL default "0",
  fuel_cost smallint(6) NOT NULL default "0",
  cloaked enum("1","0") NOT NULL default "0",
  UNIQUE KEY id (id),
  KEY series_id (series_id,game_number),
  KEY owner (game_id,owner),
  KEY location (game_id,location),
  KEY type (fleet_id,type)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `systems`
#

CREATE TABLE systems (
  id int(11) NOT NULL auto_increment,
  series_id int(11) NOT NULL default "0",
  game_number smallint(6) NOT NULL default "0",
  game_id int(11) NOT NULL default "0",
  coordinates varchar(11) NOT NULL default "",
  jumps varchar(47) NOT NULL default "",
  player_number smallint(6) NOT NULL default "0",
  name varchar(20) NOT NULL default "",
  owner varchar(20) NOT NULL default "",
  homeworld varchar(20) NOT NULL default "",
  mineral int(11) NOT NULL default "0",
  fuel int(11) NOT NULL default "0",
  agriculture int(11) NOT NULL default "0",
  population int(11) NOT NULL default "0",
  max_population int(11) NOT NULL default "0",
  annihilated enum("1","0") NOT NULL default "0",
  system_active enum("0","1") NOT NULL default "1",
  PRIMARY KEY  (game_id,coordinates),
  UNIQUE KEY id (id),
  KEY series_id (series_id,game_number)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table `words`
#

CREATE TABLE words (
  id int(11) NOT NULL auto_increment,
  word varchar(8) NOT NULL default "",
  PRIMARY KEY  (id)
) TYPE=MyISAM;

    

?>
