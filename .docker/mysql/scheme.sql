-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Gép: mysql:3306
-- Létrehozás ideje: 2023. Jún 10. 14:19
-- Kiszolgáló verziója: 5.7.42
-- PHP verzió: 8.1.15

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

SET GLOBAL sql_mode = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

CREATE TABLE `boundaries` (
  `id` int(10) NOT NULL,
  `boundary` varchar(50) NOT NULL,
  `admin_level` int(2) DEFAULT NULL,
  `name` varchar(50) NOT NULL,
  `alt_name` varchar(50) DEFAULT NULL,
  `denomination` varchar(50) DEFAULT NULL,
  `osmtype` varchar(9) DEFAULT NULL,
  `osmid` int(11) DEFAULT NULL,
  `created_at` timestamp,
  `updated_at` timestamp
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `chat` (
  `id` int(11) NOT NULL,
  `datum` datetime,
  `user` varchar(20) NOT NULL DEFAULT '',
  `kinek` varchar(20) NOT NULL DEFAULT '',
  `szoveg` tinytext NOT NULL,
  `ip` varchar(50) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `church_holders` (
  `id` int(10) NOT NULL,
  `user_id` int(11) NOT NULL,
  `church_id` int(10) NOT NULL,
  `description` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `status` enum('asked','allowed','denied','revoked') COLLATE utf8_bin NOT NULL DEFAULT 'asked',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE `church_links` (
  `id` int(10) NOT NULL,
  `church_id` int(10) NOT NULL,
  `href` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `title` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE `crons` (
  `id` int(11) NOT NULL,
  `class` varchar(45) DEFAULT NULL,
  `function` varchar(45) DEFAULT NULL,
  `frequency` varchar(45) NOT NULL,
  `from` varchar(45) DEFAULT NULL COMMENT 'strtotime',
  `until` varchar(45) DEFAULT NULL COMMENT 'strtotime',
  `deadline_at` timestamp,
  `attempts` int(2) DEFAULT '0',
  `lastsuccess_at` timestamp,
  `created_at` timestamp,
  `updated_At` timestamp
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `distances` (
  `id` int(11) NOT NULL,
  `fromLat` decimal(11,7) NOT NULL,
  `fromLon` decimal(11,7) NOT NULL,
  `toLat` decimal(11,7) NOT NULL,
  `toLon` decimal(11,7) NOT NULL,
  `distance` float NOT NULL,
  `updated_at` timestamp,
  `toupdate` int(11) DEFAULT NULL,
  `created_at` timestamp
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `egyhazmegye` (
  `id` int(11) NOT NULL,
  `nev` varchar(250) NOT NULL DEFAULT '',
  `sorrend` int(3) NOT NULL DEFAULT '0',
  `ok` enum('i','n') NOT NULL DEFAULT 'i',
  `felelos` varchar(20) NOT NULL DEFAULT '',
  `email` varchar(50) NOT NULL DEFAULT '',
  `csakez` enum('i','n') NOT NULL DEFAULT 'i',
  `osm_relation` int(45) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 PACK_KEYS=1;

CREATE TABLE `emails` (
  `id` int(11) NOT NULL,
  `type` varchar(30) DEFAULT NULL,
  `to` varchar(100) NOT NULL,
  `header` text,
  `subject` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp,
  `status` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `espereskerulet` (
  `id` int(3) NOT NULL,
  `ehm` int(2) NOT NULL DEFAULT '0',
  `nev` varchar(50) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `year` varchar(4) DEFAULT NULL,
  `date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `favorites` (
  `id` int(10) UNSIGNED NOT NULL,
  `uid` int(11) NOT NULL,
  `tid` int(11) NOT NULL,
  `created_at` timestamp,
  `updated_at` timestamp
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `igenaptar` (
  `id` int(5) NOT NULL,
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
  `gondolat` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `keyword_shortcuts` (
  `id` int(11) NOT NULL,
  `church_id` int(11) NOT NULL,
  `osmtag_id` int(11) NOT NULL,
  `type` varchar(30) NOT NULL,
  `value` varchar(200) NOT NULL,
  `created_at` timestamp,
  `updated_at` timestamp
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `lnaptar` (
  `datum` date,
  `ige` int(5) NOT NULL DEFAULT '0',
  `szent` int(5) NOT NULL DEFAULT '0',
  `szin` enum('piros','feher','zold','lila') NOT NULL DEFAULT 'lila'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `lookup_boundary_church` (
  `id` int(11) NOT NULL,
  `boundary_id` int(11) NOT NULL,
  `church_id` int(11) NOT NULL,
  `created_at` timestamp,
  `updated_at` timestamp
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `lookup_church_osm` (
  `id` int(11) NOT NULL,
  `church_id` int(11) NOT NULL,
  `osm_id` int(11) NOT NULL,
  `created_at` timestamp,
  `updated_at` timestamp
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `lookup_osm_enclosed` (
  `id` int(11) NOT NULL,
  `osm_id` int(11) NOT NULL,
  `enclosing_id` int(11) NOT NULL,
  `created_at` varchar(45) DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `megye` (
  `id` int(2) NOT NULL,
  `megyenev` varchar(50) NOT NULL DEFAULT '',
  `orszag` int(2) NOT NULL DEFAULT '12',
  `egyeb` varchar(255) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sid` varchar(45) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `severity` varchar(10) DEFAULT 'info',
  `text` text,
  `shown` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `misek` (
  `id` int(11) NOT NULL,
  `tid` int(5) DEFAULT NULL,
  `nap` int(1) NOT NULL DEFAULT '0',
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
  `moddatum` datetime,
  `torles` datetime,
  `torolte` varchar(20) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `modulok` (
  `id` int(10) NOT NULL,
  `nev` varchar(50) NOT NULL DEFAULT '',
  `leiras` tinytext NOT NULL,
  `fajlnev` varchar(50) NOT NULL DEFAULT '',
  `sablon` varchar(20) NOT NULL DEFAULT 'alap',
  `zart` int(1) NOT NULL DEFAULT '0',
  `jogkod` varchar(50) NOT NULL DEFAULT '',
  `szamlalo` int(11) NOT NULL DEFAULT '0',
  `funkcio` enum('i','n') NOT NULL DEFAULT 'n',
  `ok` enum('i','n') NOT NULL DEFAULT 'n'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `nevnaptar` (
  `datum` varchar(4) NOT NULL DEFAULT '',
  `nevnap` varchar(50) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `oldalkeret` (
  `fooldal_id` int(2) NOT NULL DEFAULT '0',
  `modul_id` int(10) NOT NULL DEFAULT '0',
  `fajlnev` varchar(50) NOT NULL DEFAULT '',
  `html_tmpl` varchar(30) NOT NULL DEFAULT 'hasabdoboz',
  `helyzet` int(2) NOT NULL DEFAULT '0',
  `lang` varchar(10) NOT NULL DEFAULT 'hu',
  `sorrend` int(2) NOT NULL DEFAULT '0',
  `zart` int(2) NOT NULL DEFAULT '0',
  `megj` varchar(100) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `orszagok` (
  `id` int(3) NOT NULL,
  `nev` varchar(50) NOT NULL DEFAULT '',
  `telkod` varchar(5) NOT NULL DEFAULT '',
  `ok` enum('i','n') NOT NULL DEFAULT 'i',
  `kiemelt` enum('i','n') NOT NULL DEFAULT 'n'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `osm` (
  `id` int(11) NOT NULL,
  `osmid` varchar(11) NOT NULL,
  `osmtype` varchar(9) NOT NULL,
  `lon` decimal(10,8) DEFAULT NULL,
  `lat` decimal(11,8) DEFAULT NULL,
  `created_at` timestamp,
  `updated_at` timestamp
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `osmtags` (
  `id` int(11) NOT NULL,
  `osmtype` varchar(9) NOT NULL,
  `osmid` varchar(11) NOT NULL,
  `name` varchar(45) NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  `created_at` timestamp,
  `updated_at` timestamp
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `osm_tags` (
  `type` varchar(8) CHARACTER SET utf8 NOT NULL,
  `id` varchar(11) CHARACTER SET utf8 NOT NULL,
  `name` varchar(45) CHARACTER SET utf8 NOT NULL,
  `value` varchar(200) CHARACTER SET utf8 DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `photos` (
  `id` int(11) NOT NULL,
  `church_id` int(11) NOT NULL,
  `filename` varchar(100) NOT NULL DEFAULT '',
  `title` varchar(250) NOT NULL DEFAULT '',
  `weight` int(2) NOT NULL DEFAULT '0',
  `flag` enum('i','n') NOT NULL DEFAULT 'i',
  `height` int(11) DEFAULT NULL,
  `width` int(11) DEFAULT NULL,
  `created_at` timestamp,
  `updated_at` timestamp
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `remarks` (
  `id` int(10) NOT NULL,
  `nev` varchar(50) NOT NULL DEFAULT '',
  `login` varchar(20) NOT NULL DEFAULT '',
  `email` varchar(50) NOT NULL DEFAULT '',
  `megbizhato` enum('?','i','n','e') NOT NULL DEFAULT '?',
  `church_id` int(11) NOT NULL DEFAULT '0',
  `allapot` enum('u','f','j') NOT NULL DEFAULT 'u',
  `admin` varchar(20) NOT NULL DEFAULT '',
  `admindatum` datetime,
  `leiras` text NOT NULL,
  `adminmegj` text,
  `log` text,
  `created_at` timestamp,
  `updated_at` timestamp
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `stats_externalapi` (
  `id` int(11) NOT NULL,
  `name` varchar(45) COLLATE utf8_bin DEFAULT NULL,
  `url` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `responsecode` int(11) DEFAULT NULL,
  `rawdata` longtext COLLATE utf8_bin,
  `date` date DEFAULT NULL,
  `count` int(11) DEFAULT NULL,
  `diff` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE `szentek` (
  `id` int(11) NOT NULL,
  `nev` varchar(200) NOT NULL DEFAULT '',
  `nevnap` varchar(200) NOT NULL DEFAULT '',
  `intro` text NOT NULL,
  `ho` int(2) NOT NULL DEFAULT '0',
  `nap` int(2) NOT NULL DEFAULT '0',
  `leiras` text NOT NULL,
  `szin` enum('piros','feher','zold','lila') NOT NULL DEFAULT 'feher'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 PACK_KEYS=1;

CREATE TABLE `templomok` (
  `id` int(11) NOT NULL,
  `nev` varchar(150) NOT NULL DEFAULT '',
  `ismertnev` varchar(150) NOT NULL DEFAULT '',
  `orszag` int(2) NOT NULL DEFAULT '0',
  `megye` int(2) NOT NULL DEFAULT '0',
  `varos` varchar(100) NOT NULL DEFAULT '',
  `cim` varchar(250) NOT NULL DEFAULT '',
  `megkozelites` tinytext NOT NULL,
  `plebania` text NOT NULL,
  `pleb_url` varchar(100) NOT NULL DEFAULT '',
  `pleb_eml` varchar(100) NOT NULL DEFAULT '',
  `egyhazmegye` int(2) NOT NULL DEFAULT '0',
  `espereskerulet` int(3) NOT NULL DEFAULT '0',
  `leiras` text NOT NULL,
  `megjegyzes` text NOT NULL,
  `miseaktiv` int(11) DEFAULT '1',
  `misemegj` text NOT NULL,
  `bucsu` text NOT NULL,
  `frissites` date,
  `kontakt` varchar(250) NOT NULL DEFAULT '',
  `kontaktmail` varchar(70) NOT NULL DEFAULT '',
  `adminmegj` text NOT NULL,
  `letrehozta` varchar(20) NOT NULL DEFAULT '',
  `megbizhato` enum('i','n') NOT NULL DEFAULT 'n',
  `created_at` timestamp,
  `modositotta` varchar(20) NOT NULL DEFAULT '',
  `moddatum` datetime,
  `log` text NOT NULL,
  `ok` enum('i','n','f') NOT NULL DEFAULT 'i',
  `eszrevetel` enum('i','n','f') NOT NULL DEFAULT 'n',
  `updated_at` timestamp,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `osmid` varchar(11) DEFAULT NULL,
  `osmtype` varchar(9) DEFAULT NULL,
  `lat` decimal(11,7) DEFAULT NULL,
  `lon` decimal(10,7) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tokens` (
  `id` int(11) NOT NULL,
  `type` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `name` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `uid` int(11) DEFAULT NULL,
  `timeout` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp,
  `updated_at` timestamp
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `unnepnaptar` (
  `datum` date,
  `unnep` varchar(50) NOT NULL DEFAULT '',
  `szabadnap` enum('i','n') NOT NULL DEFAULT 'i',
  `mise` enum('v','n','u') NOT NULL DEFAULT 'u',
  `miseinfo` varchar(250) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `updates` (
  `id` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `tid` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE `user` (
  `uid` int(11) NOT NULL,
  `login` varchar(20) NOT NULL DEFAULT '',
  `jelszo` varchar(255) NOT NULL DEFAULT '',
  `jogok` varchar(200) NOT NULL DEFAULT '',
  `regdatum` datetime,
  `lastlogin` datetime,
  `lastactive` datetime DEFAULT NULL,
  `email` varchar(100) NOT NULL DEFAULT '',
  `notifications` int(1) DEFAULT '1',
  `becenev` varchar(50) NOT NULL DEFAULT '',
  `nev` varchar(100) NOT NULL DEFAULT '',
  `volunteer` int(1) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `varosok` (
  `id` int(3) NOT NULL,
  `irsz` int(4) NOT NULL DEFAULT '0',
  `megye_id` int(2) NOT NULL DEFAULT '0',
  `orszag` int(2) NOT NULL DEFAULT '46',
  `nev` varchar(50) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

ALTER TABLE `boundaries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `index1` (`boundary`,`admin_level`),
  ADD KEY `index2` (`osmtype`,`osmid`);

ALTER TABLE `chat`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `church_holders`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `church_links`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `crons`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `distances`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `Coord` (`fromLat`,`fromLon`,`toLat`,`toLon`),
  ADD KEY `From` (`fromLat`,`fromLon`);

ALTER TABLE `egyhazmegye`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id` (`id`);

ALTER TABLE `emails`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `espereskerulet`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_UNIQUE` (`id`),
  ADD UNIQUE KEY `name+year` (`name`,`year`);

ALTER TABLE `favorites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uid_tid_UNIQUE` (`uid`,`tid`);

ALTER TABLE `igenaptar`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `keyword_shortcuts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `index_value` (`value`),
  ADD KEY `FK_keyword_shortchuts_church_idx` (`church_id`),
  ADD KEY `FK_keyword_shortchuts_osmtag_idx` (`osmtag_id`),
  ADD KEY `church_type_value` (`church_id`,`type`,`value`),
  ADD KEY `type_value` (`type`,`value`);

ALTER TABLE `lnaptar`
  ADD PRIMARY KEY (`datum`);

ALTER TABLE `lookup_boundary_church`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique` (`church_id`,`boundary_id`),
  ADD KEY `FK_church_id_idx` (`church_id`),
  ADD KEY `FK_boundary_id_idx` (`boundary_id`);

ALTER TABLE `lookup_church_osm`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique` (`church_id`,`osm_id`),
  ADD KEY `FK_church_id_idx` (`church_id`),
  ADD KEY `FK_osm_id_idx` (`osm_id`);

ALTER TABLE `lookup_osm_enclosed`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique` (`enclosing_id`,`osm_id`),
  ADD KEY `FK_osm_id_idx` (`osm_id`),
  ADD KEY `FK_osm_enclosing_id_idx` (`enclosing_id`);

ALTER TABLE `megye`
  ADD UNIQUE KEY `id` (`id`);

ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `misek`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `modulok`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `nevnaptar`
  ADD PRIMARY KEY (`datum`);

ALTER TABLE `orszagok`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `osm`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_UNIQUE` (`osmid`,`osmtype`),
  ADD UNIQUE KEY `idUNIQUE` (`osmid`,`osmtype`);

ALTER TABLE `osmtags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `valami` (`osmtype`,`osmid`,`name`),
  ADD KEY `index_osm` (`osmtype`,`osmid`),
  ADD KEY `index_name` (`name`),
  ADD KEY `index_name_value` (`name`,`value`);

ALTER TABLE `osm_tags`
  ADD UNIQUE KEY `valami` (`id`,`name`,`type`);

ALTER TABLE `photos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kiemelt` (`flag`),
  ADD KEY `FKchurch` (`church_id`);

ALTER TABLE `remarks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `index2` (`id`,`church_id`,`allapot`),
  ADD KEY `index1` (`id`,`church_id`),
  ADD KEY `FK_church_id` (`church_id`);

ALTER TABLE `stats_externalapi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fast` (`url`,`date`);

ALTER TABLE `szentek`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id` (`id`);

ALTER TABLE `templomok`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id` (`id`),
  ADD KEY `varos` (`varos`),
  ADD KEY `ismertnev` (`ismertnev`),
  ADD KEY `egyhazmegye` (`egyhazmegye`),
  ADD KEY `espereskerulet` (`espereskerulet`),
  ADD KEY `osm` (`osmid`,`osmtype`);

ALTER TABLE `tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_UNIQUE` (`id`),
  ADD UNIQUE KEY `name_UNIQUE` (`name`);

ALTER TABLE `unnepnaptar`
  ADD PRIMARY KEY (`datum`);

ALTER TABLE `updates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_UNIQUE` (`id`);

ALTER TABLE `user`
  ADD PRIMARY KEY (`uid`);

ALTER TABLE `varosok`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `boundaries`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7891;

ALTER TABLE `chat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `church_holders`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

ALTER TABLE `church_links`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1505;

ALTER TABLE `crons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

ALTER TABLE `distances`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58224;

ALTER TABLE `egyhazmegye`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

ALTER TABLE `emails`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `espereskerulet`
  MODIFY `id` int(3) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=239;

ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=381;

ALTER TABLE `favorites`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `igenaptar`
  MODIFY `id` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=821;

ALTER TABLE `keyword_shortcuts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12825;

ALTER TABLE `lookup_boundary_church`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=84777320;

ALTER TABLE `lookup_church_osm`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5317741;

ALTER TABLE `lookup_osm_enclosed`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36847;

ALTER TABLE `megye`
  MODIFY `id` int(2) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `misek`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=242464;

ALTER TABLE `modulok`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

ALTER TABLE `orszagok`
  MODIFY `id` int(3) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

ALTER TABLE `osm`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8439;

ALTER TABLE `osmtags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=90215;

ALTER TABLE `photos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44673;

ALTER TABLE `remarks`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

ALTER TABLE `stats_externalapi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `szentek`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=261;

ALTER TABLE `templomok`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5420;

ALTER TABLE `tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `updates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `user`
  MODIFY `uid` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `varosok`
  MODIFY `id` int(3) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7845;

ALTER TABLE `lookup_church_osm`
  ADD CONSTRAINT `FK_lookup_church_osm_osm_id` FOREIGN KEY (`osm_id`) REFERENCES `osm` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

ALTER TABLE `lookup_osm_enclosed`
  ADD CONSTRAINT `FK_lookup_osm_enclosed_enclosing` FOREIGN KEY (`enclosing_id`) REFERENCES `osm` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `FK_lookup_osm_enclosed_osm` FOREIGN KEY (`osm_id`) REFERENCES `osm` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

ALTER TABLE `photos`
  ADD CONSTRAINT `FKchurch` FOREIGN KEY (`church_id`) REFERENCES `templomok` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

ALTER TABLE `remarks`
  ADD CONSTRAINT `FK_church_id` FOREIGN KEY (`church_id`) REFERENCES `templomok` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
