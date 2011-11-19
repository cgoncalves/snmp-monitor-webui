-- phpMyAdmin SQL Dump
-- version 2.11.11.3
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Nov 10, 2011 at 09:43 PM
-- Server version: 5.0.77
-- PHP Version: 5.3.8

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `girs_monitoring`
--

-- --------------------------------------------------------

--
-- Table structure for table `eventlogs`
--

DROP TABLE IF EXISTS `eventlogs`;
CREATE TABLE IF NOT EXISTS `eventlogs` (
  `Id` int(11) NOT NULL auto_increment,
  `RefIDServer` int(11) NOT NULL,
  `IDMetric` int(11) NOT NULL,
  `Date` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `OID` varchar(128) NOT NULL,
  `Threshold_min1` int(11) NOT NULL,
  `Threshold_min2` int(11) NOT NULL,
  `Threshold_max1` int(11) NOT NULL,
  `Threshold_max2` int(11) NOT NULL,
  `Value` int(11) NOT NULL,
  `Ack` tinyint(1) NOT NULL,
  PRIMARY KEY  (`Id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `metrics`
--

DROP TABLE IF EXISTS `metrics`;
CREATE TABLE IF NOT EXISTS `metrics` (
  `Id` int(11) NOT NULL auto_increment,
  `Name` varchar(255) NOT NULL,
  `Parameters` varchar(255) NOT NULL,
  `DataType` varchar(50) NOT NULL,
  `Unit` varchar(10) NOT NULL,
  PRIMARY KEY  (`Id`),
  UNIQUE KEY `Name` (`Name`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE IF NOT EXISTS `notifications` (
  `Id` int(11) NOT NULL auto_increment,
  `Name` varchar(50) NOT NULL,
  PRIMARY KEY  (`Id`),
  UNIQUE KEY `Name` (`Name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `oids`
--

DROP TABLE IF EXISTS `oids`;
CREATE TABLE IF NOT EXISTS `oids` (
  `Id` int(11) NOT NULL auto_increment,
  `OID` varchar(128) NOT NULL,
  `Description` varchar(255) NOT NULL,
  PRIMARY KEY  (`Id`),
  UNIQUE KEY `OID` (`OID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `servers`
--

DROP TABLE IF EXISTS `servers`;
CREATE TABLE IF NOT EXISTS `servers` (
  `Id` int(11) NOT NULL auto_increment,
  `Name` varchar(255) NOT NULL,
  `IP` varchar(39) NOT NULL,
  `Periodicity` int(11) NOT NULL,
  PRIMARY KEY  (`Id`),
  UNIQUE KEY `Nome` (`Name`),
  UNIQUE KEY `IP` (`IP`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

-- --------------------------------------------------------

--
-- Table structure for table `servers_metrics`
--

DROP TABLE IF EXISTS `servers_metrics`;
CREATE TABLE IF NOT EXISTS `servers_metrics` (
  `Id` int(11) NOT NULL auto_increment,
  `RefIDServer` int(11) NOT NULL,
  `RefIDMetric` int(11) NOT NULL,
  `Threshold_max1` int(11),
  `Threshold_max2` int(11),
  `Threshold_min1` int(11),
  `Threshold_min2` int(11),
  `Status` varchar(255),
  PRIMARY KEY  (`Id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

-- --------------------------------------------------------

--
-- Table structure for table `servers_notifications`
--

DROP TABLE IF EXISTS `servers_notifications`;
CREATE TABLE IF NOT EXISTS `servers_notifications` (
  `Id` int(11) NOT NULL,
  `RefIDServer` int(11) NOT NULL,
  `RefIDNotification` int(11) NOT NULL,
  `Receiver` varchar(255) NOT NULL,
  PRIMARY KEY  (`Id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
