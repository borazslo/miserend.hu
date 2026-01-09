/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19-11.8.5-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: 127.0.0.1    Database: miserend
-- ------------------------------------------------------
-- Server version	12.1.2-MariaDB-ubu2404

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;

--
-- Table structure for table `attributes`
--

USE miserend;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE IF NOT EXISTS `attributes` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `church_id` int(11) NOT NULL,
  `key` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_hungarian_ci NOT NULL,
  `value` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_hungarian_ci NOT NULL,
  `fromOSM` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `church_id` (`church_id`),
  CONSTRAINT `attributes_ibfk_1` FOREIGN KEY (`church_id`) REFERENCES `templomok` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `boundaries`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE IF NOT EXISTS `boundaries` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `boundary` varchar(50) NOT NULL,
  `admin_level` int(2) DEFAULT NULL,
  `name` varchar(50) NOT NULL,
  `alt_name` varchar(50) DEFAULT NULL,
  `denomination` varchar(50) DEFAULT NULL,
  `osmtype` varchar(9) DEFAULT NULL,
  `osmid` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `index1` (`boundary`,`admin_level`),
  KEY `index2` (`osmtype`,`osmid`)
) ENGINE=InnoDB AUTO_INCREMENT=7891 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `chat`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE IF NOT EXISTS `chat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `datum` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `user` varchar(20) NOT NULL DEFAULT '',
  `kinek` varchar(20) NOT NULL DEFAULT '',
  `szoveg` tinytext NOT NULL,
  `ip` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `church_holders`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE IF NOT EXISTS `church_holders` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `church_id` int(10) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `status` enum('asked','allowed','denied','revoked') NOT NULL DEFAULT 'asked',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `church_links`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE IF NOT EXISTS `church_links` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `church_id` int(10) NOT NULL,
  `href` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1505 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `confessions`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE IF NOT EXISTS `confessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `deduplicationId` char(36) NOT NULL,
  `church_id` int(11) NOT NULL,
  `local_id` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `fulldata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`fulldata`)),
  PRIMARY KEY (`id`),
  UNIQUE KEY `deduplicationId` (`deduplicationId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `crons`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE IF NOT EXISTS `crons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `class` varchar(45) DEFAULT NULL,
  `function` varchar(45) DEFAULT NULL,
  `frequency` varchar(45) NOT NULL,
  `from` varchar(45) DEFAULT NULL COMMENT 'strtotime',
  `until` varchar(45) DEFAULT NULL COMMENT 'strtotime',
  `deadline_at` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  `attempts` int(2) DEFAULT 0,
  `lastsuccess_at` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  `created_at` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  `updated_At` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `distances`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE IF NOT EXISTS `distances` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fromLat` decimal(11,7) NOT NULL,
  `fromLon` decimal(11,7) NOT NULL,
  `toLat` decimal(11,7) NOT NULL,
  `toLon` decimal(11,7) NOT NULL,
  `distance` float NOT NULL,
  `updated_at` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  `toupdate` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `Coord` (`fromLat`,`fromLon`,`toLat`,`toLon`),
  KEY `From` (`fromLat`,`fromLon`)
) ENGINE=InnoDB AUTO_INCREMENT=58224 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `egyhazmegye`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE IF NOT EXISTS `egyhazmegye` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nev` varchar(250) NOT NULL DEFAULT '',
  `sorrend` int(3) NOT NULL DEFAULT 0,
  `ok` enum('i','n') NOT NULL DEFAULT 'i',
  `felelos` varchar(20) NOT NULL DEFAULT '',
  `email` varchar(50) NOT NULL DEFAULT '',
  `csakez` enum('i','n') NOT NULL DEFAULT 'i',
  `osm_relation` int(45) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_uca1400_ai_ci PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `emails`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE IF NOT EXISTS `emails` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(30) DEFAULT NULL,
  `to` varchar(100) NOT NULL,
  `header` text DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  `status` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `espereskerulet`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE IF NOT EXISTS `espereskerulet` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `ehm` int(2) NOT NULL DEFAULT 0,
  `nev` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=239 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `events`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE IF NOT EXISTS `events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `year` varchar(4) DEFAULT NULL,
  `date` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  UNIQUE KEY `name+year` (`name`,`year`)
) ENGINE=InnoDB AUTO_INCREMENT=381 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `favorites`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE IF NOT EXISTS `favorites` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `tid` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uid_tid_UNIQUE` (`uid`,`tid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `igenaptar`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE IF NOT EXISTS `igenaptar` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `szin` enum('piros','feher','zold','lila') NOT NULL DEFAULT 'feher',
  `ev` enum('0','A','B','C') NOT NULL DEFAULT '0',
  `idoszak` char(1) NOT NULL DEFAULT '',
  `nap` varchar(250) NOT NULL DEFAULT '',
  `oszov_hely` varchar(50) NOT NULL DEFAULT '',
  `oszov` text NOT NULL,
  `ujszov_hely` varchar(50) NOT NULL DEFAULT '',
  `ujszov` text NOT NULL,
  `evang_hely` varchar(50) NOT NULL DEFAULT '',
  `evang` text NOT NULL,
  `unnep` varchar(250) NOT NULL DEFAULT '',
  `intro` text NOT NULL,
  `gondolat` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=821 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `keyword_shortcuts`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE IF NOT EXISTS `keyword_shortcuts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `church_id` int(11) NOT NULL,
  `osmtag_id` int(11) NOT NULL,
  `type` varchar(30) NOT NULL,
  `value` varchar(200) NOT NULL,
  `created_at` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `index_value` (`value`),
  KEY `FK_keyword_shortchuts_church_idx` (`church_id`),
  KEY `FK_keyword_shortchuts_osmtag_idx` (`osmtag_id`),
  KEY `church_type_value` (`church_id`,`type`,`value`),
  KEY `type_value` (`type`,`value`)
) ENGINE=InnoDB AUTO_INCREMENT=12825 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `lnaptar`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE IF NOT EXISTS `lnaptar` (
  `datum` date NOT NULL DEFAULT '0000-00-00',
  `ige` int(5) NOT NULL DEFAULT 0,
  `szent` int(5) NOT NULL DEFAULT 0,
  `szin` enum('piros','feher','zold','lila') NOT NULL DEFAULT 'lila',
  PRIMARY KEY (`datum`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `lookup_boundary_church`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE IF NOT EXISTS `lookup_boundary_church` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `boundary_id` int(11) NOT NULL,
  `church_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique` (`church_id`,`boundary_id`),
  KEY `FK_church_id_idx` (`church_id`),
  KEY `FK_boundary_id_idx` (`boundary_id`)
) ENGINE=InnoDB AUTO_INCREMENT=84777320 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `lookup_church_osm`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE IF NOT EXISTS `lookup_church_osm` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `church_id` int(11) NOT NULL,
  `osm_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique` (`church_id`,`osm_id`),
  KEY `FK_church_id_idx` (`church_id`),
  KEY `FK_osm_id_idx` (`osm_id`),
  CONSTRAINT `FK_lookup_church_osm_osm_id` FOREIGN KEY (`osm_id`) REFERENCES `osm` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=5317741 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `lookup_osm_enclosed`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE IF NOT EXISTS `lookup_osm_enclosed` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `osm_id` int(11) NOT NULL,
  `enclosing_id` int(11) NOT NULL,
  `created_at` varchar(45) DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique` (`enclosing_id`,`osm_id`),
  KEY `FK_osm_id_idx` (`osm_id`),
  KEY `FK_osm_enclosing_id_idx` (`enclosing_id`),
  CONSTRAINT `FK_lookup_osm_enclosed_enclosing` FOREIGN KEY (`enclosing_id`) REFERENCES `osm` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `FK_lookup_osm_enclosed_osm` FOREIGN KEY (`osm_id`) REFERENCES `osm` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=36847 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `megye`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE IF NOT EXISTS `megye` (
  `id` int(2) NOT NULL AUTO_INCREMENT,
  `megyenev` varchar(50) NOT NULL DEFAULT '',
  `orszag` int(2) NOT NULL DEFAULT 12,
  `egyeb` varchar(255) NOT NULL DEFAULT '',
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `messages`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE IF NOT EXISTS `messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` varchar(45) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `severity` varchar(10) DEFAULT 'info',
  `text` text DEFAULT NULL,
  `shown` int(11) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `misek`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE IF NOT EXISTS `misek` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tid` int(5) DEFAULT NULL,
  `nap` int(1) NOT NULL DEFAULT 0,
  `ido` time NOT NULL DEFAULT '00:00:00',
  `nap2` varchar(4) DEFAULT NULL,
  `idoszamitas` varchar(255) DEFAULT NULL,
  `weight` int(11) DEFAULT NULL,
  `tol` varchar(100) DEFAULT NULL,
  `ig` varchar(100) DEFAULT NULL,
  `tmp_datumtol` varchar(5) DEFAULT NULL,
  `tmp_relation` char(1) DEFAULT NULL,
  `tmp_datumig` varchar(5) DEFAULT NULL,
  `nyelv` varchar(100) NOT NULL DEFAULT '',
  `milyen` varchar(50) NOT NULL DEFAULT '',
  `megjegyzes` text NOT NULL,
  `modositotta` varchar(20) NOT NULL DEFAULT '',
  `moddatum` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `torles` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `torolte` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=242464 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `modulok`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE IF NOT EXISTS `modulok` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `nev` varchar(50) NOT NULL DEFAULT '',
  `leiras` tinytext NOT NULL,
  `fajlnev` varchar(50) NOT NULL DEFAULT '',
  `sablon` varchar(20) NOT NULL DEFAULT 'alap',
  `zart` int(1) NOT NULL DEFAULT 0,
  `jogkod` varchar(50) NOT NULL DEFAULT '',
  `szamlalo` int(11) NOT NULL DEFAULT 0,
  `funkcio` enum('i','n') NOT NULL DEFAULT 'n',
  `ok` enum('i','n') NOT NULL DEFAULT 'n',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nevnaptar`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE IF NOT EXISTS `nevnaptar` (
  `datum` varchar(4) NOT NULL DEFAULT '',
  `nevnap` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`datum`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `oldalkeret`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE IF NOT EXISTS `oldalkeret` (
  `fooldal_id` int(2) NOT NULL DEFAULT 0,
  `modul_id` int(10) NOT NULL DEFAULT 0,
  `fajlnev` varchar(50) NOT NULL DEFAULT '',
  `html_tmpl` varchar(30) NOT NULL DEFAULT 'hasabdoboz',
  `helyzet` int(2) NOT NULL DEFAULT 0,
  `lang` varchar(10) NOT NULL DEFAULT 'hu',
  `sorrend` int(2) NOT NULL DEFAULT 0,
  `zart` int(2) NOT NULL DEFAULT 0,
  `megj` varchar(100) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `orszagok`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE IF NOT EXISTS `orszagok` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `nev` varchar(50) NOT NULL DEFAULT '',
  `telkod` varchar(5) NOT NULL DEFAULT '',
  `ok` enum('i','n') NOT NULL DEFAULT 'i',
  `kiemelt` enum('i','n') NOT NULL DEFAULT 'n',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `osm`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE IF NOT EXISTS `osm` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `osmid` varchar(11) NOT NULL,
  `osmtype` varchar(9) NOT NULL,
  `lon` decimal(10,8) DEFAULT NULL,
  `lat` decimal(11,8) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`osmid`,`osmtype`),
  UNIQUE KEY `idUNIQUE` (`osmid`,`osmtype`)
) ENGINE=InnoDB AUTO_INCREMENT=8439 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `osm_tags`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE IF NOT EXISTS `osm_tags` (
  `type` varchar(8) CHARACTER SET utf8mb3 COLLATE utf8mb3_uca1400_ai_ci NOT NULL,
  `id` varchar(11) CHARACTER SET utf8mb3 COLLATE utf8mb3_uca1400_ai_ci NOT NULL,
  `name` varchar(45) CHARACTER SET utf8mb3 COLLATE utf8mb3_uca1400_ai_ci NOT NULL,
  `value` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_uca1400_ai_ci DEFAULT NULL,
  UNIQUE KEY `valami` (`id`,`name`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `osmtags`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE IF NOT EXISTS `osmtags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `osmtype` varchar(9) NOT NULL,
  `osmid` varchar(11) NOT NULL,
  `name` varchar(45) NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `valami` (`osmtype`,`osmid`,`name`),
  KEY `index_osm` (`osmtype`,`osmid`),
  KEY `index_name` (`name`),
  KEY `index_name_value` (`name`,`value`)
) ENGINE=InnoDB AUTO_INCREMENT=90215 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `photos`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE IF NOT EXISTS `photos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `church_id` int(11) NOT NULL,
  `filename` varchar(100) NOT NULL DEFAULT '',
  `title` varchar(250) NOT NULL DEFAULT '',
  `weight` int(2) NOT NULL DEFAULT 0,
  `flag` enum('i','n') NOT NULL DEFAULT 'i',
  `height` int(11) DEFAULT NULL,
  `width` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `kiemelt` (`flag`),
  KEY `FKchurch` (`church_id`),
  CONSTRAINT `FKchurch` FOREIGN KEY (`church_id`) REFERENCES `templomok` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=44673 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `remarks`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE IF NOT EXISTS `remarks` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `nev` varchar(50) NOT NULL DEFAULT '',
  `login` varchar(20) NOT NULL DEFAULT '',
  `email` varchar(50) NOT NULL DEFAULT '',
  `megbizhato` enum('?','i','n','e') NOT NULL DEFAULT '?',
  `church_id` int(11) NOT NULL DEFAULT 0,
  `allapot` enum('u','f','j') NOT NULL DEFAULT 'u',
  `admin` varchar(20) NOT NULL DEFAULT '',
  `admindatum` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `leiras` text NOT NULL,
  `adminmegj` text DEFAULT NULL,
  `log` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `index2` (`id`,`church_id`,`allapot`),
  KEY `index1` (`id`,`church_id`),
  KEY `FK_church_id` (`church_id`),
  CONSTRAINT `FK_church_id` FOREIGN KEY (`church_id`) REFERENCES `templomok` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stats_externalapi`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE IF NOT EXISTS `stats_externalapi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `responsecode` int(11) DEFAULT NULL,
  `rawdata` longtext DEFAULT NULL,
  `date` date DEFAULT NULL,
  `count` int(11) DEFAULT NULL,
  `diff` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fast` (`url`,`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `szentek`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE IF NOT EXISTS `szentek` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nev` varchar(200) NOT NULL DEFAULT '',
  `nevnap` varchar(200) NOT NULL DEFAULT '',
  `intro` text NOT NULL,
  `ho` int(2) NOT NULL DEFAULT 0,
  `nap` int(2) NOT NULL DEFAULT 0,
  `leiras` text NOT NULL,
  `szin` enum('piros','feher','zold','lila') NOT NULL DEFAULT 'feher',
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=261 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_uca1400_ai_ci PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `szentsegimadasok`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE IF NOT EXISTS `szentsegimadasok` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `church_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `starttime` varchar(5) NOT NULL,
  `endtime` varchar(5) NOT NULL,
  `type` varchar(40) DEFAULT NULL,
  `info` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_hungarian_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `templomok`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE IF NOT EXISTS `templomok` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nev` varchar(150) NOT NULL DEFAULT '',
  `ismertnev` varchar(150) NOT NULL DEFAULT '',
  `orszag` int(2) NOT NULL DEFAULT 0,
  `megye` int(2) NOT NULL DEFAULT 0,
  `varos` varchar(100) NOT NULL DEFAULT '',
  `cim` varchar(250) NOT NULL DEFAULT '',
  `megkozelites` tinytext NOT NULL,
  `plebania` text NOT NULL,
  `pleb_url` varchar(100) NOT NULL DEFAULT '',
  `pleb_eml` varchar(100) NOT NULL DEFAULT '',
  `egyhazmegye` int(2) NOT NULL DEFAULT 0,
  `espereskerulet` int(3) NOT NULL DEFAULT 0,
  `leiras` text NOT NULL,
  `megjegyzes` text NOT NULL,
  `miseaktiv` int(11) DEFAULT 1,
  `misemegj` text NOT NULL,
  `bucsu` text NOT NULL,
  `frissites` date NOT NULL DEFAULT '0000-00-00',
  `kontakt` varchar(250) NOT NULL DEFAULT '',
  `kontaktmail` varchar(70) NOT NULL DEFAULT '',
  `adminmegj` text NOT NULL,
  `letrehozta` varchar(20) NOT NULL DEFAULT '',
  `megbizhato` enum('i','n') NOT NULL DEFAULT 'n',
  `created_at` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  `modositotta` varchar(20) NOT NULL DEFAULT '',
  `moddatum` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `log` text NOT NULL,
  `ok` enum('i','n','f') NOT NULL DEFAULT 'i',
  `eszrevetel` enum('i','n','f') NOT NULL DEFAULT 'n',
  `updated_at` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `osmid` varchar(11) DEFAULT NULL,
  `osmtype` varchar(9) DEFAULT NULL,
  `lat` decimal(11,7) DEFAULT NULL,
  `lon` decimal(10,7) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`),
  KEY `varos` (`varos`),
  KEY `ismertnev` (`ismertnev`),
  KEY `egyhazmegye` (`egyhazmegye`),
  KEY `espereskerulet` (`espereskerulet`),
  KEY `osm` (`osmid`,`osmtype`)
) ENGINE=InnoDB AUTO_INCREMENT=5420 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `templomok_full`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE IF NOT EXISTS `templomok_full` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nev` varchar(150) NOT NULL DEFAULT '',
  `ismertnev` varchar(150) NOT NULL DEFAULT '',
  `orszag` int(2) NOT NULL DEFAULT 0,
  `megye` int(2) NOT NULL DEFAULT 0,
  `varos` varchar(100) NOT NULL DEFAULT '',
  `cim` varchar(250) NOT NULL DEFAULT '',
  `megkozelites` tinytext NOT NULL,
  `plebania` text NOT NULL,
  `pleb_url` varchar(100) NOT NULL DEFAULT '',
  `pleb_eml` varchar(100) NOT NULL DEFAULT '',
  `egyhazmegye` int(2) NOT NULL DEFAULT 0,
  `espereskerulet` int(3) NOT NULL DEFAULT 0,
  `leiras` text NOT NULL,
  `megjegyzes` text NOT NULL,
  `miseaktiv` int(11) DEFAULT 1,
  `misemegj` text NOT NULL,
  `bucsu` text NOT NULL,
  `frissites` date NOT NULL DEFAULT '0000-00-00',
  `kontakt` varchar(250) NOT NULL DEFAULT '',
  `kontaktmail` varchar(70) NOT NULL DEFAULT '',
  `adminmegj` text NOT NULL,
  `letrehozta` varchar(20) NOT NULL DEFAULT '',
  `megbizhato` enum('i','n') NOT NULL DEFAULT 'n',
  `created_at` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  `modositotta` varchar(20) NOT NULL DEFAULT '',
  `moddatum` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `log` text NOT NULL,
  `ok` enum('i','n','f') NOT NULL DEFAULT 'i',
  `eszrevetel` enum('i','n','f') NOT NULL DEFAULT 'n',
  `updated_at` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `osmid` varchar(11) DEFAULT NULL,
  `osmtype` varchar(9) DEFAULT NULL,
  `lat` decimal(11,7) DEFAULT NULL,
  `lon` decimal(10,7) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`),
  KEY `varos` (`varos`),
  KEY `ismertnev` (`ismertnev`),
  KEY `egyhazmegye` (`egyhazmegye`),
  KEY `espereskerulet` (`espereskerulet`),
  KEY `osm` (`osmid`,`osmtype`)
) ENGINE=InnoDB AUTO_INCREMENT=5420 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tokens`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE IF NOT EXISTS `tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(15) DEFAULT NULL,
  `name` varchar(40) NOT NULL,
  `uid` int(11) DEFAULT NULL,
  `timeout` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  UNIQUE KEY `name_UNIQUE` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `unnepnaptar`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE IF NOT EXISTS `unnepnaptar` (
  `datum` date NOT NULL DEFAULT '0000-00-00',
  `unnep` varchar(50) NOT NULL DEFAULT '',
  `szabadnap` enum('i','n') NOT NULL DEFAULT 'i',
  `mise` enum('v','n','u') NOT NULL DEFAULT 'u',
  `miseinfo` varchar(250) NOT NULL DEFAULT '',
  PRIMARY KEY (`datum`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `updates`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE IF NOT EXISTS `updates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `tid` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_hungarian_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE IF NOT EXISTS `user` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `login` varchar(20) NOT NULL DEFAULT '',
  `jelszo` varchar(255) NOT NULL DEFAULT '',
  `jogok` varchar(200) NOT NULL DEFAULT '',
  `regdatum` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `lastlogin` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `lastactive` datetime DEFAULT NULL,
  `email` varchar(100) NOT NULL DEFAULT '',
  `notifications` int(1) DEFAULT 1,
  `becenev` varchar(50) NOT NULL DEFAULT '',
  `nev` varchar(100) NOT NULL DEFAULT '',
  `volunteer` int(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `varosok`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE IF NOT EXISTS `varosok` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `irsz` int(4) NOT NULL DEFAULT 0,
  `megye_id` int(2) NOT NULL DEFAULT 0,
  `orszag` int(2) NOT NULL DEFAULT 46,
  `nev` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7845 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping events for database 'miserend'
--

--
-- Dumping routines for database 'miserend'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2026-01-09  1:08:33
