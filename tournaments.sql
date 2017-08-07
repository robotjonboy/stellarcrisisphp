-- phpMyAdmin SQL Dump
-- version 4.0.10.14
-- http://www.phpmyadmin.net
--
-- Host: localhost:3306
-- Generation Time: Aug 06, 2017 at 10:14 PM
-- Server version: 10.1.22-MariaDB
-- PHP Version: 5.4.31

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `rocketem_sc`
--

-- --------------------------------------------------------

--
-- Table structure for table `tournament`
--

CREATE TABLE IF NOT EXISTS `tournament` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `starttime` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(500) NOT NULL,
  `series` int(11) NOT NULL,
  `completed` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=11 ;

-- --------------------------------------------------------

--
-- Table structure for table `tournamententrant`
--

CREATE TABLE IF NOT EXISTS `tournamententrant` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tournamentid` int(11) NOT NULL,
  `empireid` int(11) NOT NULL,
  `eliminated` tinyint(1) DEFAULT '0',
  `byes` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=75 ;

-- --------------------------------------------------------

--
-- Table structure for table `tournamentgame`
--

CREATE TABLE IF NOT EXISTS `tournamentgame` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tournament` int(11) NOT NULL,
  `game` int(11) NOT NULL,
  `round` tinyint(4) NOT NULL,
  `winner` int(11) DEFAULT NULL,
  `firstempire` varchar(20) DEFAULT NULL,
  `secondempire` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=61 ;
