# phpMyAdmin MySQL-Dump
# version 2.2.7-pl1
# http://phpwizard.net/phpMyAdmin/
# http://www.phpmyadmin.net/ (download page)
#
# Host: localhost
# Generation Time: Dec 29, 2006 at 07:58 PM
# Server version: 4.00.24
# PHP Version: 4.3.10-16
# Database : `stellar`
# --------------------------------------------------------

#
# Table structure for table `series`
#

CREATE TABLE series (
  id int(11) NOT NULL auto_increment,
  name varchar(40) NOT NULL default '',
  update_time int(11) NOT NULL default '0',
  weekend_updates enum('1','0') NOT NULL default '1',
  diplomacy enum('6','5','4','3','2','1','0') NOT NULL default '2',
  max_players smallint(6) NOT NULL default '0',
  map_type enum('standard','prebuilt','twisted','mirror','balanced') NOT NULL default 'standard',
  map_visible enum('0','1') NOT NULL default '0',
  systems_per_player tinyint(4) NOT NULL default '0',
  tech_multiple float NOT NULL default '0',
  average_resources smallint(6) NOT NULL default '30',
  min_wins smallint(6) NOT NULL default '0',
  max_wins smallint(6) NOT NULL default '-1',
  game_count smallint(6) NOT NULL default '0',
  can_draw enum('1','0') NOT NULL default '0',
  can_surrender enum('1','0') NOT NULL default '0',
  visible_builds enum('1','0') NOT NULL default '0',
  bridier_allowed enum('1','0') NOT NULL default '0',
  halted enum('1','0') NOT NULL default '0',
  team_game enum('1','0') NOT NULL default '0',
  map_compression float NOT NULL default '0.001',
  custom enum('0','1') NOT NULL default '0',
  creator varchar(20) NOT NULL default '""',
  cloakers_as_attacks enum('1','0') NOT NULL default '1',
  avg_min smallint(6) NOT NULL default '30',
  avg_fuel smallint(6) NOT NULL default '30',
  avg_ag smallint(6) NOT NULL default '30',
  PRIMARY KEY  (name),
  UNIQUE KEY id (id)
) TYPE=MyISAM;

#
# Dumping data for table `series`
#

INSERT INTO series VALUES (6, 'Iceberg', 86400, '1', '5', 15, 'standard', '1', 15, '2', 30, 0, -1, 4, '1', '1', '0', '0', '0', '0', '0.001', '1', '""', '0', 30, 30, 30);
INSERT INTO series VALUES (7, 'Petroni\'s Blood', 86400, '1', '2', 15, 'prebuilt', '0', 5, '1.75', 37, 1, -1, 1, '1', '1', '0', '0', '0', '0', '0.001', '0', '""', '0', 37, 38, 39);
INSERT INTO series VALUES (8, 'Middle Ground', 86400, '0', '6', 12, 'standard', '1', 22, '2', 30, 0, -1, 5, '1', '1', '0', '0', '0', '0', '0.001', '0', '""', '0', 30, 30, 30);
INSERT INTO series VALUES (1, 'Glacier', 86400, '1', '5', 15, 'standard', '1', 15, '2', 30, 0, -1, 4, '1', '1', '0', '0', '0', '0', '0.001', '0', '""', '0', 30, 30, 30);
INSERT INTO series VALUES (9, 'mirror01', 86400, '1', '6', 6, 'twisted', '1', 12, '5', 30, 0, -1, 6, '1', '1', '1', '0', '0', '1', '0.001', '0', 'Strider22', '0', 30, 30, 30);

