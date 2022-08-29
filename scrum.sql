-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               10.6.5-MariaDB - mariadb.org binary distribution
-- Server OS:                    Win64
-- HeidiSQL Version:             11.3.0.6295
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for scrum
CREATE DATABASE IF NOT EXISTS `scrum` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;
USE `scrum`;

-- Dumping structure for table scrum.backlog
CREATE TABLE IF NOT EXISTS `backlog` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(11) unsigned NOT NULL DEFAULT 0,
  `epicid` int(11) unsigned NOT NULL DEFAULT 0,
  `scrumid` int(11) unsigned DEFAULT NULL,
  `grabid` int(11) unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'no name',
  `details` varchar(5000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `comments` varchar(10000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'new',
  `priority` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'no',
  `number` int(11) DEFAULT NULL,
  `points` int(11) DEFAULT 0,
  `xcord` int(11) DEFAULT NULL,
  `ycord` int(11) DEFAULT NULL,
  `time` timestamp NULL DEFAULT current_timestamp(),
  `update` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `grabtime` datetime DEFAULT NULL,
  `completetime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `epicidonbacklog` (`epicid`),
  KEY `scrumidonbacklog` (`scrumid`),
  KEY `FK_backlog_user` (`userid`),
  CONSTRAINT `FK_backlog_user` FOREIGN KEY (`userid`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `epicidonbacklog` FOREIGN KEY (`epicid`) REFERENCES `epic` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `scrumidonbacklog` FOREIGN KEY (`scrumid`) REFERENCES `scrum` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=137 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='This is where all the task will be stored';

-- Data exporting was unselected.

-- Dumping structure for table scrum.controls
CREATE TABLE IF NOT EXISTS `controls` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `description` varchar(5000) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `setting` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `time` timestamp NOT NULL DEFAULT current_timestamp(),
  `update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='This is where any configuration goes';

-- Data exporting was unselected.

-- Dumping structure for table scrum.dailyscrum
CREATE TABLE IF NOT EXISTS `dailyscrum` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `scrumid` int(11) unsigned NOT NULL DEFAULT 0,
  `notes` varchar(10000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `time` timestamp NULL DEFAULT current_timestamp(),
  `update` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `date` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `completedpoints` int(11) DEFAULT NULL,
  `grabpoints` int(11) DEFAULT NULL,
  `avgpoints` int(11) DEFAULT NULL,
  `userid` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `scrumidondailyscrum` (`scrumid`),
  KEY `FK_dailyscrum_user` (`userid`),
  CONSTRAINT `FK_dailyscrum_user` FOREIGN KEY (`userid`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `scrumidondailyscrum` FOREIGN KEY (`scrumid`) REFERENCES `scrum` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='This is to keep meeting notes on the daily scrums';

-- Data exporting was unselected.

-- Dumping structure for table scrum.epic
CREATE TABLE IF NOT EXISTS `epic` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(11) unsigned DEFAULT NULL,
  `datebegin` date DEFAULT NULL,
  `dateend` date DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `details` varchar(5000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `finance` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'no',
  `time` timestamp NOT NULL DEFAULT current_timestamp(),
  `update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `FK_epic_user` (`userid`),
  CONSTRAINT `FK_epic_user` FOREIGN KEY (`userid`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='This stores each epic''s information';

-- Data exporting was unselected.

-- Dumping structure for table scrum.epicplayers
CREATE TABLE IF NOT EXISTS `epicplayers` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `epicid` int(11) unsigned NOT NULL DEFAULT 0,
  `userid` int(11) unsigned NOT NULL DEFAULT 0,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `position` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `time` timestamp NOT NULL DEFAULT current_timestamp(),
  `update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `FK_epicplayers_user` (`userid`),
  CONSTRAINT `FK_epicplayers_user` FOREIGN KEY (`userid`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='lists what players are assigned to what epic or scrum';

-- Data exporting was unselected.

-- Dumping structure for table scrum.files
CREATE TABLE IF NOT EXISTS `files` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `backlogid` int(11) unsigned NOT NULL DEFAULT 0,
  `userid` int(11) unsigned NOT NULL DEFAULT 0,
  `fileurl` varchar(1000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `name` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(4000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `time` timestamp NULL DEFAULT current_timestamp(),
  `update` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `backlogonfiles` (`backlogid`),
  KEY `FK_files_user` (`userid`),
  CONSTRAINT `FK_files_user` FOREIGN KEY (`userid`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `backlogonfiles` FOREIGN KEY (`backlogid`) REFERENCES `backlog` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='this is to store files for each backlog item';

-- Data exporting was unselected.

-- Dumping structure for table scrum.finance
CREATE TABLE IF NOT EXISTS `finance` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `epicid` int(11) unsigned DEFAULT NULL,
  `scrumid` int(11) unsigned DEFAULT NULL,
  `backlogid` int(11) unsigned DEFAULT NULL,
  `userid` int(11) unsigned DEFAULT NULL,
  `item` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `itemimg` varchar(1000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qnty` int(11) unsigned DEFAULT NULL,
  `cost` decimal(13,2) DEFAULT NULL,
  `location` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` varchar(2000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date` date DEFAULT NULL,
  `time` timestamp NULL DEFAULT current_timestamp(),
  `update` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `epicinfinance` (`epicid`),
  KEY `scrumonfinance` (`scrumid`),
  KEY `backlogonfinance` (`backlogid`),
  KEY `FK_finance_user` (`userid`),
  CONSTRAINT `FK_finance_user` FOREIGN KEY (`userid`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `backlogonfinance` FOREIGN KEY (`backlogid`) REFERENCES `backlog` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `epicinfinance` FOREIGN KEY (`epicid`) REFERENCES `epic` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `scrumonfinance` FOREIGN KEY (`scrumid`) REFERENCES `scrum` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table scrum.points
CREATE TABLE IF NOT EXISTS `points` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `backlogid` int(11) unsigned NOT NULL DEFAULT 0,
  `userid` int(11) unsigned NOT NULL,
  `points` int(11) unsigned NOT NULL DEFAULT 0,
  `time` timestamp NOT NULL DEFAULT current_timestamp(),
  `update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `backlogidpoints` (`backlogid`),
  KEY `FK_points_user` (`userid`),
  CONSTRAINT `FK_points_user` FOREIGN KEY (`userid`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `backlogidpoints` FOREIGN KEY (`backlogid`) REFERENCES `backlog` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='This is where the voting and storage of points is';

-- Data exporting was unselected.

-- Dumping structure for table scrum.scrum
CREATE TABLE IF NOT EXISTS `scrum` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `epicid` int(11) unsigned NOT NULL DEFAULT 0,
  `userid` int(11) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `details` varchar(5000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `unfinishedbacklogids` varchar(5000) COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `completedpoints` int(11) DEFAULT NULL,
  `totalpoints` int(11) DEFAULT NULL,
  `weekdays` int(11) DEFAULT NULL,
  `pointsperperson` int(11) DEFAULT NULL,
  `datebegin` date DEFAULT NULL,
  `dateend` date DEFAULT NULL,
  `time` timestamp NULL DEFAULT current_timestamp(),
  `update` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `FK_scrum_user` (`userid`),
  CONSTRAINT `FK_scrum_user` FOREIGN KEY (`userid`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='This is the inforamtion for the scrum';

-- Data exporting was unselected.

-- Dumping structure for table scrum.scrumplayers
CREATE TABLE IF NOT EXISTS `scrumplayers` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `scrumid` int(11) unsigned NOT NULL DEFAULT 0,
  `userid` int(11) unsigned NOT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `position` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `time` timestamp NOT NULL DEFAULT current_timestamp(),
  `update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `FK_scrumplayers_user` (`userid`),
  CONSTRAINT `FK_scrumplayers_user` FOREIGN KEY (`userid`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT COMMENT='lists what players are assigned to what epic or scrum';

-- Data exporting was unselected.

-- Dumping structure for table scrum.user
CREATE TABLE IF NOT EXISTS `user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fname` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lname` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `color` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#000000',
  `terms` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
