-- phpMyAdmin SQL Dump
-- version 2.6.4-pl3
-- http://www.phpmyadmin.net
-- 
-- Host: db614.perfora.net
-- Generation Time: Apr 09, 2007 at 04:00 PM
-- Server version: 4.0.27
-- PHP Version: 4.3.10-200.schlund.1
-- 
-- Database: `db192189988`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `series`
-- 

--CREATE TABLE `series` (
--  `id` int(11) NOT NULL auto_increment,
--  `name` varchar(40) NOT NULL default '',
--  `average_resources` smallint(6) NOT NULL default '30',
--  `avg_ag` smallint(6) NOT NULL default '30',
--  `avg_fuel` smallint(6) NOT NULL default '30',
--  `avg_min` smallint(6) NOT NULL default '30',
--  `bridier_allowed` enum('1','0') NOT NULL default '0',
--  `map_compression` float NOT NULL default '0.001',
--  `map_type` enum('standard','prebuilt','twisted','mirror','balanced') NOT NULL default 'standard',
--  `map_visible` enum('0','1') NOT NULL default '0',
--  `max_players` smallint(6) NOT NULL default '0',
--  `max_wins` smallint(6) NOT NULL default '-1',
--  `min_wins` smallint(6) NOT NULL default '0',
--  `systems_per_player` tinyint(4) NOT NULL default '0',
--  `team_game` enum('1','0') NOT NULL default '0',
--  `tech_multiple` float NOT NULL default '0',
--  `update_time` int(11) NOT NULL default '0',
--  `visible_builds` enum('1','0') NOT NULL default '0',
--  `weekend_updates` enum('1','0') NOT NULL default '1',
--  PRIMARY KEY  (`name`),
--  UNIQUE KEY `id` (`id`)
--) TYPE=MyISAM AUTO_INCREMENT=43 ;

-- 
-- Dumping data for table `series`
-- 

INSERT INTO `series` (
  `id`, `name`, `average_resources`, `avg_ag`, `avg_fuel`, `avg_min`,
  `bridier_allowed`, `build_cloakers_cloaked`, `can_draw`, `can_surrender`,
  `cloakers_as_attacks`, `creator`, `custom`, `diplomacy`, `game_count`,
  `halted`, `map_compression`, `map_type`, `map_visible`, `max_players`,
  `max_wins`, `min_wins`, `systems_per_player`, `team_game`, `tech_multiple`,
  `update_time`, `visible_builds`, `weekend_updates`
) VALUES
(1, 'Iceberg01', 30, 30, 30, 30, '0', '1', '0', '0', '1', 'admin', '0', '5', 0, '0', 0.001, 'standard', '1', 15, -1, 0, 15, '0', 3, 86400, '1', '0'),
(2, 'test04', 30, 30, 30, 30, '0', '0', '1', '1', '0', 'admin', '0', '5', 1, '0', 0.001, 'standard', '0', 5, -1, 0, 15, '0', 3, 86400, '0', '1'),
(17, 'Harvester''s Blood', 80, 80, 80, 80, '0', '1', '0', '0', '0', 'Manetheren', '0', '2', 6, '0', 0.001, 'standard', '0', 6, -1, 0, 8, '0', 3, 172800, '0', '0'),
(13, 'test01', 30, 30, 30, 30, '0', '0', '0', '0', '0', 'admin', '0', '6', 1, '0', 0.001, 'standard', '0', 8, -1, 0, 15, '0', 2.5, 36000, '0', '0'),
(7, 'First Blood', 50, 50, 50, 50, '0', '1', '0', '0', '0', 'Bicentennialman', '0', '2', 4, '0', 0.001, 'standard', '0', 5, 1, 0, 5, '0', 1, 86400, '0', '0'),
(6, '1976', 76, 76, 76, 76, '0', '1', '0', '0', '0', 'Bicentennialman', '0', '6', 10, '0', 0.001, 'twisted', '1', 4, -1, 0, 19, '1', 2, 273600, '0', '1'),
(11, 'Grudge Match', 30, 30, 30, 30, '1', '1', '1', '1', '0', 'admin', '0', '2', 39, '0', 0.001, 'standard', '0', 2, -1, 0, 7, '0', 2.5, 86400, '0', '0'),
(15, 'Blitzkrieg', 33, 33, 33, 33, '0', '1', '0', '0', '0', 'admin', '0', '4', 8, '0', 0.001, 'standard', '0', 8, -1, 0, 5, '0', 3, 180, '0', '1'),
(35, 'Weekend Warriors', 36, 36, 36, 36, '0', '1', '1', '0', '0', 'admin', '0', '6', 3, '0', 0.001, 'prebuilt', '1', 6, -1, 0, 6, '0', 1.75, 82800, '0', '1'),
(18, 'Manifest Destiny', 50, 50, 50, 50, '0', '0', '0', '0', '0', 'Bicentennialman', '0', '4', 6, '0', 0.001, 'standard', '0', 10, -1, 0, 50, '0', 2, 86400, '1', '0'),
(21, 'Winless Wonder', 35, 35, 35, 35, '0', '1', '1', '1', '0', '', '0', '5', 5, '0', 0.001, 'standard', '0', 2, -1, 0, 4, '0', 2.5, 240, '0', '1'),
(22, 'Jumping Jupiter.....', 65, 250, 35, 65, '0', '1', '0', '0', '1', 'TRIAXX', '0', '2', 6, '0', 0.001, 'prebuilt', '0', 15, -1, 0, 12, '0', 1.5, 129600, '0', '0'),
(23, 'The Generals GP', 50, 100, 75, 50, '0', '1', '0', '0', '1', 'General Grimm', '0', '6', 3, '0', 0.001, 'standard', '0', 16, -1, 0, 25, '0', 1, 97200, '0', '0'),
(24, 'Twisted Grudge', 30, 30, 30, 30, '1', '1', '1', '1', '1', '', '0', '2', 16, '0', 0.001, 'twisted', '1', 2, -1, 0, 7, '0', 2, 86400, '1', '0'),
(25, 'Hall of Mirrors', 60, 60, 60, 60, '1', '1', '1', '1', '0', '', '0', '2', 12, '0', 0.5, 'mirror', '0', 2, -1, 0, 7, '0', 2, 86400, '0', '0'),
(26, 'test0311', 30, 30, 30, 30, '0', '0', '0', '0', '0', 'admin', '0', '6', 1, '0', 0.001, 'standard', '0', 8, -1, 0, 15, '0', 2.5, 93600, '0', '0'),
(27, 'Resource Hell', 500, 500, 500, 500, '0', '1', '0', '0', '1', 'admin', '0', '6', 6, '0', 0.001, 'standard', '1', 999, -1, 0, 8, '0', 5, 86400, '0', '1'),
(28, 'Resource Hell Express', 250, 250, 250, 250, '0', '1', '0', '0', '1', 'Blitz Jester Empire', '0', '6', 1, '0', 0.001, 'standard', '1', 999, -1, 0, 6, '0', 3, 240, '0', '1'),
(29, 'Blood Bath', 100, 100, 100, 100, '0', '1', '0', '0', '0', '', '0', '2', 3, '0', 0.001, 'standard', '0', 100, -1, 0, 3, '0', 4, 86400, '0', '0'),
(30, 'Blood Bath Blitz', 100, 100, 100, 100, '0', '1', '0', '0', '0', '', '0', '2', 1, '0', 0.001, 'standard', '0', 10, -1, 0, 3, '0', 4, 180, '0', '1'),
(31, 'Funhouse Mirror', 60, 60, 60, 60, '1', '1', '1', '1', '1', '', '0', '2', 2, '0', 0.8, 'mirror', '0', 2, -1, 0, 5, '0', 2, 240, '0', '1'),
(32, 'Close Proximity', 50, 50, 50, 50, '0', '0', '0', '0', '0', 'GW', '0', '2', 4, '0', 0.001, 'prebuilt', '0', 50, -1, 0, 4, '0', 2, 86400, '0', '0'),
(34, 'Teh Tru Alliance', 75, 75, 75, 75, '0', '0', '0', '0', '0', 'RTS Phill', '0', '6', 3, '0', 0.001, 'twisted', '0', 8, -1, 0, 15, '1', 3, 86400, '0', '0'),
(37, ' Triskadecaphobia', 13, 13, 13, 13, '0', '0', '0', '0', '0', '13', '0', '2', 1, '0', 0.13, 'prebuilt', '0', 13, -1, 0, 13, '0', 13, 46800, '0', '1'),
(38, 'Need More Cowbell', 40, 40, 40, 40, '1', '1', '1', '1', '0', 'Christopher Walken', '0', '2', 10, '0', 0.001, 'standard', '0', 2, -1, 0, 5, '0', 2.8, 97200, '0', '0'),
(39, 'The Stonewall', 65, 10, 75, 65, '0', '1', '0', '0', '0', 'admin', '0', '3', 3, '0', 0.001, 'standard', '0', 6, -1, 2, 50, '0', 1.65, 100800, '0', '0'),
(40, 'Two Toed Sloth', 30, 30, 30, 30, '1', '1', '1', '1', '0', '', '0', '2', 3, '0', 0.001, 'balanced', '0', 2, -1, 0, 10, '0', 1, 604800, '0', '1'),
(41, 'Three Toed Sloth', 30, 30, 30, 30, '0', '1', '0', '0', '0', '', '0', '2', 2, '0', 0.8, 'prebuilt', '0', 3, -1, 0, 10, '0', 1, 604800, '0', '1'),
(42, '6', 66, 66, 66, 66, '0', '1', '0', '0', '0', '', '0', '5', 3, '0', 0.001, 'twisted', '1', 6, -1, 0, 6, '1', 6, 86400, '0', '0');