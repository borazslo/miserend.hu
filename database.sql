# --------------------------------------------------------
# Host:                         localhost
# Server version:               5.5.27-0ubuntu2
# Server OS:                    debian-linux-gnu
# HeidiSQL version:             6.0.0.3603
# Date/time:                    2013-01-05 01:08:51
# --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

# Dumping database structure for vp_portal
CREATE DATABASE IF NOT EXISTS `vp_portal` /*!40100 DEFAULT CHARACTER SET latin2 COLLATE latin2_hungarian_ci */;
USE `vp_portal`;


# Dumping structure for table vp_portal.misek
CREATE TABLE IF NOT EXISTS `misek` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `templom` int(5) NOT NULL DEFAULT '0',
  `nap` int(1) NOT NULL DEFAULT '0',
  `ido` time NOT NULL DEFAULT '00:00:00',
  `idoszamitas` enum('t','ny') NOT NULL DEFAULT 'ny',
  `datumtol` date NOT NULL DEFAULT '2006-03-26',
  `datumig` date NOT NULL DEFAULT '2006-10-29',
  `nyelv` varchar(100) NOT NULL DEFAULT '',
  `milyen` varchar(50) NOT NULL DEFAULT '',
  `megjegyzes` text NOT NULL,
  `modositotta` varchar(20) NOT NULL DEFAULT '',
  `moddatum` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `torles` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `torolte` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=196125 DEFAULT CHARSET=utf8;

# Dumping data for table vp_portal.misek: 196 124 rows
DELETE FROM `misek`;
/*!40000 ALTER TABLE `misek` DISABLE KEYS */;
INSERT INTO `misek` (`id`, `templom`, `nap`, `ido`, `idoszamitas`, `datumtol`, `datumig`, `nyelv`, `milyen`, `megjegyzes`, `modositotta`, `moddatum`, `torles`, `torolte`) VALUES
	(19, 2, 4, '07:00:00', 'ny', '2006-03-26', '2006-10-29', 'h0', '', '', 'AUTO', '2006-02-13 14:40:53', '2006-02-14 14:20:30', 'jeno'),
	(20, 2, 4, '07:00:00', 't', '2006-03-26', '2006-10-29', 'h0', '', '', 'AUTO', '2006-02-13 14:40:53', '2006-02-14 14:20:30', 'jeno'),
	(36, 0, 7, '10:10:00', 'ny', '2006-03-26', '2006-10-29', 'e0', '', '', 'AUTO', '2006-02-13 14:40:53', '0000-00-00 00:00:00', ''),
	(37, 0, 7, '10:10:00', 't', '2006-03-26', '2006-10-29', 'e0', '', '', 'AUTO', '2006-02-13 14:40:53', '0000-00-00 00:00:00', ''),
	(66, 0, 1, '06:00:00', 't', '2006-03-26', '2006-10-29', 'h0', '', '6:00 mise után zsolozsma', 'AUTO', '2006-02-13 14:40:53', '0000-00-00 00:00:00', ''),
	(67, 0, 1, '07:00:00', 't', '2006-03-26', '2006-10-29', 'h0', '', '', 'AUTO', '2006-02-13 14:40:53', '0000-00-00 00:00:00', ''),
	(68, 0, 1, '08:00:00', 't', '2006-03-26', '2006-10-29', 'h0', '', '', 'AUTO', '2006-02-13 14:40:53', '0000-00-00 00:00:00', ''),
	(69, 0, 1, '18:00:00', 't', '2006-03-26', '2006-10-29', 'h0', '', '', 'AUTO', '2006-02-13 14:40:53', '0000-00-00 00:00:00', ''),
	(70, 0, 1, '19:30:00', 't', '2006-03-26', '2006-10-29', 'h0', '', '', 'AUTO', '2006-02-13 14:40:53', '0000-00-00 00:00:00', ''),
	(71, 0, 2, '06:00:00', 't', '2006-03-26', '2006-10-29', 'h0', '', '6:00 mise után zsolozsma', 'AUTO', '2006-02-13 14:40:53', '0000-00-00 00:00:00', ''),
	(72, 0, 2, '07:00:00', 't', '2006-03-26', '2006-10-29', 'h0', '', '', 'AUTO', '2006-02-13 14:40:53', '0000-00-00 00:00:00', ''),
	(73, 0, 2, '08:00:00', 't', '2006-03-26', '2006-10-29', 'h0', '', '', 'AUTO', '2006-02-13 14:40:53', '0000-00-00 00:00:00', ''),
	(74, 0, 2, '18:00:00', 't', '2006-03-26', '2006-10-29', 'h0', '', '18:00 mise után Szent Antal litánia', 'AUTO', '2006-02-13 14:40:53', '0000-00-00 00:00:00', ''),
	(75, 0, 2, '19:30:00', 't', '2006-03-26', '2006-10-29', 'h0', '', '', 'AUTO', '2006-02-13 14:40:53', '0000-00-00 00:00:00', ''),
	(76, 0, 3, '06:00:00', 't', '2006-03-26', '2006-10-29', 'h0', '', '6:00 mise után zsolozsma', 'AUTO', '2006-02-13 14:40:53', '0000-00-00 00:00:00', ''),
	(77, 0, 3, '07:00:00', 't', '2006-03-26', '2006-10-29', 'h0', '', '', 'AUTO', '2006-02-13 14:40:53', '0000-00-00 00:00:00', ''),
	(78, 0, 3, '08:00:00', 't', '2006-03-26', '2006-10-29', 'h0', '', '', 'AUTO', '2006-02-13 14:40:53', '0000-00-00 00:00:00', ''),
	(79, 0, 3, '18:00:00', 't', '2006-03-26', '2006-10-29', 'h0', '', '18:00 mise után Szent József litánia', 'AUTO', '2006-02-13 14:40:53', '0000-00-00 00:00:00', ''),
	(80, 0, 3, '19:30:00', 't', '2006-03-26', '2006-10-29', 'h0', '', '', 'AUTO', '2006-02-13 14:40:53', '0000-00-00 00:00:00', ''),
	(81, 0, 4, '06:00:00', 't', '2006-03-26', '2006-10-29', 'h0', '', '6:00 mise után zsolozsma', 'AUTO', '2006-02-13 14:40:53', '0000-00-00 00:00:00', ''),
	(82, 0, 4, '07:00:00', 't', '2006-03-26', '2006-10-29', 'h0', '', '', 'AUTO', '2006-02-13 14:40:53', '0000-00-00 00:00:00', ''),
	(83, 0, 4, '08:00:00', 't', '2006-03-26', '2006-10-29', 'h0', '', '', 'AUTO', '2006-02-13 14:40:53', '0000-00-00 00:00:00', ''),
	(84, 0, 4, '18:00:00', 't', '2006-03-26', '2006-10-29', 'h0', '', '', 'AUTO', '2006-02-13 14:40:53', '0000-00-00 00:00:00', ''),
	(85, 0, 4, '19:30:00', 't', '2006-03-26', '2006-10-29', 'h0', '', '', 'AUTO', '2006-02-13 14:40:53', '0000-00-00 00:00:00', ''),
	(86, 0, 5, '06:00:00', 't', '2006-03-26', '2006-10-29', 'h0', '', '6:00 mise után zsolozsma, első péntek 10:00 is.', 'AUTO', '2006-02-13 14:40:53', '0000-00-00 00:00:00', ''),
	(87, 0, 5, '07:00:00', 't', '2006-03-26', '2006-10-29', 'h0', '', '', 'AUTO', '2006-02-13 14:40:53', '0000-00-00 00:00:00', ''),
	(88, 0, 5, '08:00:00', 't', '2006-03-26', '2006-10-29', 'h0', '', '', 'AUTO', '2006-02-13 14:40:53', '0000-00-00 00:00:00', ''),
	(89, 0, 5, '18:00:00', 't', '2006-03-26', '2006-10-29', 'h0', '', '', 'AUTO', '2006-02-13 14:40:53', '0000-00-00 00:00:00', ''),
	(90, 0, 5, '19:30:00', 't', '2006-03-26', '2006-10-29', 'h0', '', '', 'AUTO', '2006-02-13 14:40:53', '0000-00-00 00:00:00', ''),
	(91, 0, 6, '06:00:00', 't', '2006-03-26', '2006-10-29', 'h0', '', '', 'AUTO', '2006-02-13 14:40:53', '0000-00-00 00:00:00', ''),
	(92, 0, 6, '07:00:00', 't', '2006-03-26', '2006-10-29', 'h0', '', '', 'AUTO', '2006-02-13 14:40:53', '0000-00-00 00:00:00', ''),
	(93, 0, 6, '08:00:00', 't', '2006-03-26', '2006-10-29', 'h0', '', '', 'AUTO', '2006-02-13 14:40:53', '0000-00-00 00:00:00', ''),
	(94, 0, 6, '18:00:00', 't', '2006-03-26', '2006-10-29', 'h0', '', '', 'AUTO', '2006-02-13 14:40:53', '0000-00-00 00:00:00', ''),
	(95, 0, 6, '17:00:00', 'ny', '2006-03-26', '2006-10-29', 'h0', '', 'óvodás mise', 'AUTO', '2006-02-13 14:40:53', '0000-00-00 00:00:00', ''),
	(96, 0, 6, '17:00:00', 't', '2006-03-26', '2006-10-29', 'h0', '', 'óvodás mise', 'AUTO', '2006-02-13 14:40:53', '0000-00-00 00:00:00', ''),
	(97, 0, 6, '19:30:00', 't', '2006-03-26', '2006-10-29', 'h0', '', '', 'AUTO', '2006-02-13 14:40:53', '0000-00-00 00:00:00', ''),
	(98, 0, 7, '08:00:00', 't', '2006-03-26', '2006-10-29', 'h0', '', '', 'AUTO', '2006-02-13 14:40:53', '0000-00-00 00:00:00', ''),
	(99, 0, 7, '09:00:00', 't', '2006-03-26', '2006-10-29', 'h0', '', '', 'AUTO', '2006-02-13 14:40:53', '0000-00-00 00:00:00', ''),
	(100, 0, 7, '10:00:00', 'ny', '2006-03-26', '2006-10-29', 'h0', '', 'diákmise', 'AUTO', '2006-02-13 14:40:53', '0000-00-00 00:00:00', ''),
	(101, 0, 7, '10:00:00', 't', '2006-03-26', '2006-10-29', 'h0', '', 'diákmise', 'AUTO', '2006-02-13 14:40:53', '0000-00-00 00:00:00', ''),
	(102, 0, 7, '11:00:00', 't', '2006-03-26', '2006-10-29', 'h0', '', '', 'AUTO', '2006-02-13 14:40:53', '0000-00-00 00:00:00', ''),
	(103, 0, 7, '12:30:00', 't', '2006-03-26', '2006-10-29', 'h0', '', '', 'AUTO', '2006-02-13 14:40:53', '0000-00-00 00:00:00', ''),
	(104, 0, 7, '18:00:00', 'ny', '2006-03-26', '2006-10-29', 'h0', '', '', 'AUTO', '2006-02-13 14:40:53', '0000-00-00 00:00:00', ''),
	(105, 0, 7, '18:00:00', 't', '2006-03-26', '2006-10-29', 'h0', '', '', 'AUTO', '2006-02-13 14:40:53', '0000-00-00 00:00:00', ''),
	(106, 0, 7, '19:30:00', 'ny', '2006-03-26', '2006-10-29', 'h0', '', 'gitáros ifjúsági mise', 'AUTO', '2006-02-13 14:40:53', '0000-00-00 00:00:00', ''),
	(107, 0, 7, '19:30:00', 't', '2006-03-26', '2006-10-29', 'h0', '', 'gitáros ifjúsági mise', 'AUTO', '2006-02-13 14:40:53', '0000-00-00 00:00:00', ''),
	(485, 0, 7, '07:00:00', 'ny', '2006-03-26', '2006-10-29', 'h0', '', '', 'AUTO', '2006-02-13 14:40:53', '0000-00-00 00:00:00', ''),
	(486, 0, 7, '07:00:00', 't', '2006-03-26', '2006-10-29', 'h0', '', '', 'AUTO', '2006-02-13 14:40:53', '0000-00-00 00:00:00', ''),
	(487, 0, 7, '09:00:00', 'ny', '2006-03-26', '2006-10-29', 'h0', '', '', 'AUTO', '2006-02-13 14:40:53', '0000-00-00 00:00:00', ''),
	(488, 0, 7, '09:00:00', 't', '2006-03-26', '2006-10-29', 'h0', '', '', 'AUTO', '2006-02-13 14:40:53', '0000-00-00 00:00:00', ''),
	(489, 0, 7, '11:00:00', 'ny', '2006-03-26', '2006-10-29', 'h0', '', '', 'AUTO', '2006-02-13 14:40:53', '0000-00-00 00:00:00', ''),
	(490, 0, 7, '11:00:00', 't', '2006-03-26', '2006-10-29', 'h0', '', '', 'AUTO', '2006-02-13 14:40:53', '0000-00-00 00:00:00', ''),
	(491, 0, 7, '18:00:00', 'ny', '2006-03-26', '2006-10-29', 'h0', '', '', 'AUTO', '2006-02-13 14:40:53', '0000-00-00 00:00:00', ''),
	(492, 0, 7, '18:00:00', 't', '2006-03-26', '2006-10-29', 'h0', '', '', 'AUTO', '2006-02-13 14:40:53', '0000-00-00 00:00:00', ''),
	(493, 0, 1, '07:00:00', 'ny', '2006-03-26', '2006-10-29', 'h0', '', 'július-augusztusban elmarad', 'AUTO', '2006-02-13 14:40:53', '0000-00-00 00:00:00', ''),
	(494, 0, 1, '07:00:00', 't', '2006-03-26', '2006-10-29', 'h0', '', 'július-augusztusban elmarad', 'AUTO', '2006-02-13 14:40:53', '0000-00-00 00:00:00', ''),
	(495, 0, 1, '18:00:00', 'ny', '2006-03-26', '2006-10-29', 'h0', '', '', 'AUTO', '2006-02-13 14:40:53', '0000-00-00 00:00:00', ''),
	(496, 0, 1, '18:00:00', 't', '2006-03-26', '2006-10-29', 'h0', '', '', 'AUTO', '2006-02-13 14:40:53', '0000-00-00 00:00:00', ''),
	(813, 0, 2, '17:00:00', 't', '2006-03-26', '2006-10-29', 'h0', '', '', 'AUTO', '2006-02-13 14:40:53', '0000-00-00 00:00:00', ''),
	(814, 0, 2, '18:00:00', 'ny', '2006-03-26', '2006-10-29', 'h0', '', '', 'AUTO', '2006-02-13 14:40:53', '0000-00-00 00:00:00', ''),
	(815, 0, 4, '17:00:00', 't', '2006-03-26', '2006-10-29', 'h0', '', '', 'AUTO', '2006-02-13 14:40:53', '0000-00-00 00:00:00', ''),
	(816, 0, 4, '18:00:00', 'ny', '2006-03-26', '2006-10-29', 'h0', '', '', 'AUTO', '2006-02-13 14:40:53', '0000-00-00 00:00:00', ''),
	(817, 0, 7, '10:30:00', 'ny', '2006-03-26', '2006-10-29', 'h0', '', 'minden hónap 3. vasárnap német nyelven', 'AUTO', '2006-02-13 14:40:53', '0000-00-00 00:00:00', ''),
	(818, 0, 7, '10:30:00', 't', '2006-03-26', '2006-10-29', 'h0', '', 'minden hónap 3. vasárnap német nyelven', 'AUTO', '2006-02-13 14:40:53', '0000-00-00 00:00:00', ''),
	(131294, 3, 1, '17:00:00', 'ny', '2009-03-29', '2009-10-25', 'h0', '', '', 'tombi', '2009-03-03 08:42:23', '0000-00-00 00:00:00', ''),
	(131295, 3, 1, '17:00:00', 't', '2009-03-29', '2009-10-25', 'h0', '', '', 'tombi', '2009-03-03 08:42:23', '0000-00-00 00:00:00', ''),
	(131296, 3, 2, '17:00:00', 'ny', '2009-03-29', '2009-10-25', 'h0', '', '', 'tombi', '2009-03-03 08:42:23', '0000-00-00 00:00:00', ''),
	(131297, 3, 2, '17:00:00', 't', '2009-03-29', '2009-10-25', 'h0', '', '', 'tombi', '2009-03-03 08:42:23', '0000-00-00 00:00:00', ''),
	(131298, 3, 3, '17:00:00', 'ny', '2009-03-29', '2009-10-25', 'h0', '', '', 'tombi', '2009-03-03 08:42:23', '0000-00-00 00:00:00', ''),
	(131299, 3, 3, '17:00:00', 't', '2009-03-29', '2009-10-25', 'h0', '', '', 'tombi', '2009-03-03 08:42:23', '0000-00-00 00:00:00', ''),
	(131300, 3, 4, '17:00:00', 'ny', '2009-03-29', '2009-10-25', 'h0', '', '', 'tombi', '2009-03-03 08:42:23', '0000-00-00 00:00:00', ''),
	(131301, 3, 4, '17:00:00', 't', '2009-03-29', '2009-10-25', 'h0', '', '', 'tombi', '2009-03-03 08:42:23', '0000-00-00 00:00:00', ''),
	(131302, 3, 5, '17:00:00', 'ny', '2009-03-29', '2009-10-25', 'h0', '', '', 'tombi', '2009-03-03 08:42:23', '0000-00-00 00:00:00', ''),
	(131303, 3, 5, '17:00:00', 't', '2009-03-29', '2009-10-25', 'h0', '', '', 'tombi', '2009-03-03 08:42:23', '0000-00-00 00:00:00', ''),
	(131304, 3, 6, '17:00:00', 'ny', '2009-03-29', '2009-10-25', 'h0', '', '', 'tombi', '2009-03-03 08:42:23', '0000-00-00 00:00:00', ''),
	(131305, 3, 6, '17:00:00', 't', '2009-03-29', '2009-10-25', 'h0', '', '', 'tombi', '2009-03-03 08:42:23', '0000-00-00 00:00:00', ''),
	(131306, 3, 7, '12:00:00', 'ny', '2009-03-29', '2009-10-25', 'h0', '', '', 'tombi', '2009-03-03 08:42:23', '0000-00-00 00:00:00', ''),
	(131307, 3, 7, '12:00:00', 't', '2009-03-29', '2009-10-25', 'h0', '', '', 'tombi', '2009-03-03 08:42:23', '0000-00-00 00:00:00', ''),
	(174582, 1, 1, '07:00:00', 'ny', '2011-03-27', '2011-10-29', 'h0', '', '', 'gregory', '2011-04-10 15:26:02', '0000-00-00 00:00:00', ''),
	(174583, 1, 1, '18:30:00', 'ny', '2011-03-27', '2011-10-29', 'h0', '', '', 'gregory', '2011-04-10 15:26:02', '0000-00-00 00:00:00', ''),
	(174584, 1, 1, '07:30:00', 't', '2011-03-27', '2011-10-29', 'h0', '', '', 'gregory', '2011-04-10 15:26:02', '0000-00-00 00:00:00', ''),
	(174585, 1, 1, '18:00:00', 't', '2011-03-27', '2011-10-29', 'h0', '', '', 'gregory', '2011-04-10 15:26:02', '0000-00-00 00:00:00', ''),
	(174586, 1, 2, '07:00:00', 'ny', '2011-03-27', '2011-10-29', 'h0', '', '', 'gregory', '2011-04-10 15:26:02', '0000-00-00 00:00:00', ''),
	(174587, 1, 2, '18:30:00', 'ny', '2011-03-27', '2011-10-29', 'h0', '', 'előtte rózsafüzér', 'gregory', '2011-04-10 15:26:02', '0000-00-00 00:00:00', ''),
	(174588, 1, 2, '07:30:00', 't', '2011-03-27', '2011-10-29', 'h0', '', '', 'gregory', '2011-04-10 15:26:02', '0000-00-00 00:00:00', ''),
	(174589, 1, 2, '18:00:00', 't', '2011-03-27', '2011-10-29', 'h0', '', 'előtte rózsafüzér', 'gregory', '2011-04-10 15:26:02', '0000-00-00 00:00:00', ''),
	(174590, 1, 3, '07:00:00', 'ny', '2011-03-27', '2011-10-29', 'h0', '', '', 'gregory', '2011-04-10 15:26:02', '0000-00-00 00:00:00', ''),
	(174591, 1, 3, '07:30:00', 't', '2011-03-27', '2011-10-29', 'h0', '', '', 'gregory', '2011-04-10 15:26:02', '0000-00-00 00:00:00', ''),
	(174592, 1, 4, '07:00:00', 'ny', '2011-03-27', '2011-10-29', 'h0', '', '', 'gregory', '2011-04-10 15:26:02', '0000-00-00 00:00:00', ''),
	(174593, 1, 4, '18:30:00', 'ny', '2011-03-27', '2011-10-29', 'h0', '', '', 'gregory', '2011-04-10 15:26:02', '0000-00-00 00:00:00', ''),
	(174594, 1, 4, '07:30:00', 't', '2011-03-27', '2011-10-29', 'h0', '', '', 'gregory', '2011-04-10 15:26:02', '0000-00-00 00:00:00', ''),
	(174595, 1, 4, '18:00:00', 't', '2011-03-27', '2011-10-29', 'h0', '', '', 'gregory', '2011-04-10 15:26:02', '0000-00-00 00:00:00', ''),
	(174596, 1, 5, '07:00:00', 'ny', '2011-03-27', '2011-10-29', 'h0', '', '', 'gregory', '2011-04-10 15:26:02', '0000-00-00 00:00:00', ''),
	(174597, 1, 5, '18:30:00', 'ny', '2011-03-27', '2011-10-29', 'h0', '', '', 'gregory', '2011-04-10 15:26:02', '0000-00-00 00:00:00', ''),
	(174598, 1, 5, '07:30:00', 't', '2011-03-27', '2011-10-29', 'h0', '', '', 'gregory', '2011-04-10 15:26:02', '0000-00-00 00:00:00', ''),
	(174599, 1, 5, '18:00:00', 't', '2011-03-27', '2011-10-29', 'h0', '', '', 'gregory', '2011-04-10 15:26:02', '0000-00-00 00:00:00', ''),
	(174600, 1, 6, '07:00:00', 'ny', '2011-03-27', '2011-10-29', 'h0', '', '', 'gregory', '2011-04-10 15:26:02', '0000-00-00 00:00:00', ''),
	(174601, 1, 6, '18:30:00', 'ny', '2011-03-27', '2011-10-29', 'h0', '', '', 'gregory', '2011-04-10 15:26:02', '0000-00-00 00:00:00', ''),
	(174602, 1, 6, '07:30:00', 't', '2011-03-27', '2011-10-29', 'h0', '', '', 'gregory', '2011-04-10 15:26:02', '0000-00-00 00:00:00', ''),
	(174603, 1, 6, '18:00:00', 't', '2011-03-27', '2011-10-29', 'h0', '', '', 'gregory', '2011-04-10 15:26:02', '0000-00-00 00:00:00', ''),
	(174604, 1, 7, '07:00:00', 'ny', '2011-03-27', '2011-10-29', 'h0', '', 'görögkatolikus liturgia', 'gregory', '2011-04-10 15:26:02', '0000-00-00 00:00:00', ''),
	(174605, 1, 7, '09:00:00', 'ny', '2011-03-27', '2011-10-29', 'h0', 'd0,g2', 'kéthetente gitáros mise', 'gregory', '2011-04-10 15:26:02', '0000-00-00 00:00:00', ''),
	(174606, 1, 7, '11:30:00', 'ny', '2011-03-27', '2011-10-29', 'h0', '', '', 'gregory', '2011-04-10 15:26:02', '0000-00-00 00:00:00', ''),
	(174607, 1, 7, '18:30:00', 'ny', '2011-03-27', '2011-10-29', 'h0', '', '', 'gregory', '2011-04-10 15:26:02', '0000-00-00 00:00:00', ''),
	(174608, 1, 7, '07:30:00', 't', '2011-03-27', '2011-10-29', 'h0', '', 'görögkatolikus liturgia', 'gregory', '2011-04-10 15:26:02', '0000-00-00 00:00:00', ''),
	(174609, 1, 7, '09:00:00', 't', '2011-03-27', '2011-10-29', 'h0', 'd0,g2', 'kéthetente gitáros mise', 'gregory', '2011-04-10 15:26:02', '0000-00-00 00:00:00', ''),
	(174610, 1, 7, '11:30:00', 't', '2011-03-27', '2011-10-29', 'h0', '', '', 'gregory', '2011-04-10 15:26:02', '0000-00-00 00:00:00', ''),
	(174611, 1, 7, '18:00:00', 't', '2011-03-27', '2011-10-29', 'h0', '', '', 'gregory', '2011-04-10 15:26:02', '0000-00-00 00:00:00', ''),
	(190770, 2, 1, '08:00:00', 'ny', '2012-03-25', '2012-10-27', 'h0', '', '', 'tombi', '2012-02-27 11:32:15', '0000-00-00 00:00:00', ''),
	(190771, 2, 1, '18:00:00', 'ny', '2012-03-25', '2012-10-27', 'h0', '', '', 'tombi', '2012-02-27 11:32:15', '0000-00-00 00:00:00', ''),
	(190772, 2, 1, '08:00:00', 't', '2012-03-25', '2012-10-27', 'h0', '', '', 'tombi', '2012-02-27 11:32:15', '0000-00-00 00:00:00', ''),
	(190773, 2, 1, '18:00:00', 't', '2012-03-25', '2012-10-27', 'h0', '', '', 'tombi', '2012-02-27 11:32:15', '0000-00-00 00:00:00', ''),
	(190774, 2, 2, '08:00:00', 'ny', '2012-03-25', '2012-10-27', 'h0', '', '', 'tombi', '2012-02-27 11:32:15', '0000-00-00 00:00:00', ''),
	(190775, 2, 2, '18:00:00', 'ny', '2012-03-25', '2012-10-27', 'h0', '', '', 'tombi', '2012-02-27 11:32:15', '0000-00-00 00:00:00', ''),
	(190776, 2, 2, '08:00:00', 't', '2012-03-25', '2012-10-27', 'h0', '', '', 'tombi', '2012-02-27 11:32:15', '0000-00-00 00:00:00', ''),
	(190777, 2, 2, '18:00:00', 't', '2012-03-25', '2012-10-27', 'h0', '', '', 'tombi', '2012-02-27 11:32:15', '0000-00-00 00:00:00', ''),
	(190778, 2, 3, '08:00:00', 'ny', '2012-03-25', '2012-10-27', 'h0', '', '', 'tombi', '2012-02-27 11:32:15', '0000-00-00 00:00:00', ''),
	(190779, 2, 3, '18:00:00', 'ny', '2012-03-25', '2012-10-27', 'h0', '', '', 'tombi', '2012-02-27 11:32:15', '0000-00-00 00:00:00', ''),
	(190780, 2, 3, '08:00:00', 't', '2012-03-25', '2012-10-27', 'h0', '', '', 'tombi', '2012-02-27 11:32:15', '0000-00-00 00:00:00', ''),
	(190781, 2, 3, '18:00:00', 't', '2012-03-25', '2012-10-27', 'h0', '', '', 'tombi', '2012-02-27 11:32:15', '0000-00-00 00:00:00', ''),
	(190782, 2, 4, '08:00:00', 'ny', '2012-03-25', '2012-10-27', 'h0', '', '', 'tombi', '2012-02-27 11:32:15', '0000-00-00 00:00:00', ''),
	(190783, 2, 4, '18:00:00', 'ny', '2012-03-25', '2012-10-27', 'h0', '', '', 'tombi', '2012-02-27 11:32:15', '0000-00-00 00:00:00', ''),
	(190784, 2, 4, '08:00:00', 't', '2012-03-25', '2012-10-27', 'h0', '', '', 'tombi', '2012-02-27 11:32:15', '0000-00-00 00:00:00', ''),
	(190785, 2, 4, '18:00:00', 't', '2012-03-25', '2012-10-27', 'h0', '', '', 'tombi', '2012-02-27 11:32:15', '0000-00-00 00:00:00', ''),
	(190786, 2, 5, '08:00:00', 'ny', '2012-03-25', '2012-10-27', 'h0', '', '', 'tombi', '2012-02-27 11:32:15', '0000-00-00 00:00:00', ''),
	(190787, 2, 5, '18:00:00', 'ny', '2012-03-25', '2012-10-27', 'h0', '', '', 'tombi', '2012-02-27 11:32:15', '0000-00-00 00:00:00', ''),
	(190788, 2, 5, '08:00:00', 't', '2012-03-25', '2012-10-27', 'h0', '', '', 'tombi', '2012-02-27 11:32:15', '0000-00-00 00:00:00', ''),
	(190789, 2, 5, '18:00:00', 't', '2012-03-25', '2012-10-27', 'h0', '', '', 'tombi', '2012-02-27 11:32:15', '0000-00-00 00:00:00', ''),
	(190790, 2, 6, '08:00:00', 'ny', '2012-03-25', '2012-10-27', 'h0', '', '', 'tombi', '2012-02-27 11:32:15', '0000-00-00 00:00:00', ''),
	(190791, 2, 6, '18:00:00', 'ny', '2012-03-25', '2012-10-27', 'h0', '', '', 'tombi', '2012-02-27 11:32:15', '0000-00-00 00:00:00', ''),
	(190792, 2, 6, '08:00:00', 't', '2012-03-25', '2012-10-27', 'h0', '', '', 'tombi', '2012-02-27 11:32:15', '0000-00-00 00:00:00', ''),
	(190793, 2, 6, '18:00:00', 't', '2012-03-25', '2012-10-27', 'h0', '', '', 'tombi', '2012-02-27 11:32:15', '0000-00-00 00:00:00', ''),
	(190794, 2, 7, '08:00:00', 'ny', '2012-03-25', '2012-10-27', 'h0', '', 'idősek miséje', 'tombi', '2012-02-27 11:32:15', '0000-00-00 00:00:00', ''),
	(190795, 2, 7, '09:00:00', 'ny', '2012-03-25', '2012-10-27', 'h0', '', 'családi mise', 'tombi', '2012-02-27 11:32:15', '0000-00-00 00:00:00', ''),
	(190796, 2, 7, '11:00:00', 'ny', '2012-03-25', '2012-10-27', 'h0', '', '', 'tombi', '2012-02-27 11:32:15', '0000-00-00 00:00:00', ''),
	(190797, 2, 7, '18:00:00', 'ny', '2012-03-25', '2012-10-27', 'h0', 'g0', 'ifjúsági mise', 'tombi', '2012-02-27 11:32:15', '0000-00-00 00:00:00', ''),
	(190798, 2, 7, '21:00:00', 'ny', '2012-03-25', '2012-10-27', 'h0', 'cs0', '', 'tombi', '2012-02-27 11:32:15', '0000-00-00 00:00:00', ''),
	(190799, 2, 7, '08:00:00', 't', '2012-03-25', '2012-10-27', 'h0', '', 'idősek miséje', 'tombi', '2012-02-27 11:32:15', '0000-00-00 00:00:00', ''),
	(190800, 2, 7, '09:00:00', 't', '2012-03-25', '2012-10-27', 'h0', '', 'családi mise', 'tombi', '2012-02-27 11:32:15', '0000-00-00 00:00:00', ''),
	(190801, 2, 7, '11:00:00', 't', '2012-03-25', '2012-10-27', 'h0', '', '', 'tombi', '2012-02-27 11:32:15', '0000-00-00 00:00:00', ''),
	(190802, 2, 7, '18:00:00', 't', '2012-03-25', '2012-10-27', 'h0', 'g0', 'ifjúsági mise', 'tombi', '2012-02-27 11:32:15', '0000-00-00 00:00:00', ''),
	(190803, 2, 7, '21:00:00', 't', '2012-03-25', '2012-10-27', 'h0', 'cs0', '', 'tombi', '2012-02-27 11:32:15', '0000-00-00 00:00:00', '');
/*!40000 ALTER TABLE `misek` ENABLE KEYS */;


# Dumping structure for table vp_portal.templomok
CREATE TABLE IF NOT EXISTS `templomok` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `nev` varchar(150) NOT NULL DEFAULT '',
  `ismertnev` varchar(150) NOT NULL DEFAULT '',
  `turistautak` int(5) NOT NULL DEFAULT '0',
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
  `misemegj` text NOT NULL,
  `szomszedos1` varchar(100) NOT NULL DEFAULT '',
  `szomszedos2` varchar(100) NOT NULL DEFAULT '',
  `bucsu` text NOT NULL,
  `nyariido` date NOT NULL DEFAULT '2006-03-26',
  `teliido` date NOT NULL DEFAULT '2006-10-29',
  `frissites` date NOT NULL DEFAULT '0000-00-00',
  `kontakt` varchar(250) NOT NULL DEFAULT '',
  `kontaktmail` varchar(70) NOT NULL DEFAULT '',
  `adminmegj` text NOT NULL,
  `letrehozta` varchar(20) NOT NULL DEFAULT '',
  `megbizhato` enum('i','n') NOT NULL DEFAULT 'n',
  `regdatum` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modositotta` varchar(20) NOT NULL DEFAULT '',
  `moddatum` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `log` text NOT NULL,
  `ok` enum('i','n','f') NOT NULL DEFAULT 'i',
  `eszrevetel` enum('i','n','f') NOT NULL DEFAULT 'n',
  KEY `turistautak` (`turistautak`),
  KEY `id` (`id`),
  KEY `varos` (`varos`),
  KEY `ismertnev` (`ismertnev`),
  KEY `egyhazmegye` (`egyhazmegye`),
  KEY `espereskerulet` (`espereskerulet`)
) ENGINE=MyISAM AUTO_INCREMENT=5081 DEFAULT CHARSET=utf8;

# Dumping data for table vp_portal.templomok: 4 851 rows
DELETE FROM `templomok`;
/*!40000 ALTER TABLE `templomok` DISABLE KEYS */;
INSERT INTO `templomok` (`id`, `nev`, `ismertnev`, `turistautak`, `orszag`, `megye`, `varos`, `cim`, `megkozelites`, `plebania`, `pleb_url`, `pleb_eml`, `egyhazmegye`, `espereskerulet`, `leiras`, `megjegyzes`, `misemegj`, `szomszedos1`, `szomszedos2`, `bucsu`, `nyariido`, `teliido`, `frissites`, `kontakt`, `kontaktmail`, `adminmegj`, `letrehozta`, `megbizhato`, `regdatum`, `modositotta`, `moddatum`, `log`, `ok`, `eszrevetel`) VALUES
	(1, 'Péter-Pál templom', 'Péter-Pál templom (Csiprovacska)', 1359, 12, 14, 'Szentendre', '', 'Volán busszal a Bükkös part megállóig, onnan besétálva a patak partján.', '<b>Plébánia:</b><br>Cím: 2000 Szentendre, Bajcsy-Zsilinszky u. 2.\r\nTel: 26/312-545\r\nPlébános: Blanckenstein György \r\n', 'http://www.szentendre-plebania.hu/', 'plebania@szentendre-plebania.hu', 3, 48, '<p class="alap">M&aacute;r 1002-ben oklevél eml&iacute;ti, mint a veszprémi püspök faluj&aacute;t. Egy ideig a veszprémi püspökséghez tartoz&oacute; archipresbyter&aacute;tusi, f&ocirc;esperesi központ is. Püspöki birtok marad 1318-ig, amikor csere révén R&oacute;bert K&aacute;roly birtok&aacute;ba kerül, aki különféle kiv&aacute;lts&aacute;gokban részes&iacute;ti a v&aacute;rost. A török megsz&aacute;ll&aacute;s alatt, 1588-ban csup&aacute;n 6 port&aacute;t &iacute;rnak össze. 1690 ut&aacute;n nagy sz&aacute;mban szerbek telepednek le. A Zichy csal&aacute;d birtoka lesz, majd a Koronauradalomhoz kerül. Pléb&aacute;ni&aacute;ja &otilde;si. Az 1332&ndash;37. évi p&aacute;pai tizedjegyzék eml&iacute;ti P&aacute;l nev&ucirc; papj&aacute;t. A török id&otilde;ben és ut&aacute;na különféle szerzetesek l&aacute;tj&aacute;k el a h&iacute;veket. 1723-ban &aacute;ll&iacute;tj&aacute;k vissza a pléb&aacute;ni&aacute;t. 1732-ben Berényi Zsigmond c. püspök, esztergomi kanonok végzi itt, mint az esztergomi érsek joghat&oacute;s&aacute;ga al&aacute; tartoz&oacute;, exempt pléb&aacute;ni&aacute;n az egyh&aacute;zi vizsg&aacute;latot. A felvett jegyz&otilde;könyv szerint: &bdquo;A h&iacute;vek sz&aacute;ma 300. A többi szakad&aacute;r, akiknek saj&aacute;t püspökük van és 7 templomban tartanak istentiszteletet.&rdquo; Az 1776. évi Calendarium archidioecesanum-ban is exempt pléb&aacute;niaként szerepel Szentendre. Templom&aacute;r&oacute;l m&aacute;r a XIII. sz.-ban van eml&iacute;tés. A török id&ocirc;t is &aacute;tvészeli. XIV. sz.-i ép&iacute;tészeti st&iacute;lusjegyek fedezhet&ocirc;k fel rajta. A XV. sz. von&aacute;sait is viseli. 1791-ben a Kamara b&ocirc;v&iacute;tését hat&aacute;rozza el (200 m2). Mint m&ucirc;emléket az OMF 1954&ndash;57-ben renov&aacute;ltatja (törzssz&aacute;m 7322). Bels&ocirc; berendezése együttesen m&ucirc;emlék. &ndash; 1942-ben megv&aacute;s&aacute;rolj&aacute;k az 1751-ben épült szerb g. keleti Csiprovacska templomot, amely a pléb&aacute;nia kezelésébe kerül Szent Péter és P&aacute;l titulussal (300 m2). A m&ucirc;emlék templomban 1997-ben &uacute;j karzat készült (törzssz&aacute;m 7288). A pléb&aacute;niah&aacute;zr&oacute;l m&aacute;r az 1732-es visitatio canonica megemlékezik. 1800-ban t&ucirc;zvész puszt&iacute;tja el az iratt&aacute;rral együtt. Csak az anyakönyveket tudt&aacute;k megmenteni. 1992-ben a volt egyh&aacute;zi &oacute;vod&aacute;t és iskol&aacute;t visszakapja az egyh&aacute;zközség. 1996 j&uacute;nius&aacute;t&oacute;l Szent Andr&aacute;s &Oacute;voda és &Aacute;ltal&aacute;nos Iskola néven m&ucirc;ködik. A jelenlegi pléb&aacute;niah&aacute;z egyh&aacute;zi tulajdon.<br />Anyakönyvek: kereszteltek 1705-t&ocirc;l, h&aacute;zasultak és halottak 1723-t&oacute;l.<br /><br />K&aacute;poln&aacute;k: a Szent Fl&oacute;ri&aacute;n &ndash; és a m&ucirc;emlék K&aacute;lv&aacute;ria-k&aacute;polna.<br /><br />Katolikus &oacute;voda és iskola a pléb&aacute;nia területén:<br />Szent Andr&aacute;s &Oacute;voda és &Aacute;ltal&aacute;nos Iskola<br />(2000 Bükkös part 29/b. Tel: 26/311-608)</p>', 'Búcsú: június 24., június 29.\r\n\r\n\r\n', 'Családos hittan a tanév ideje alatt minden hónap utolsó hétfőjén este 8 órától a katolikus iskola ebédlőjében\r\n\r\nMájusban az esti misék előtt 18 órától litánia.\r\nVasárnap 7,30-tól görög katolikus Szent Liturgia van.', '', '', '', '2012-03-25', '2012-10-27', '2011-04-10', 'Valaki Neve\r\n70/455-455', 'egymail@cim.hu', 'Ezt a templomot én kezelem, ha gond van, szóljatok. Igyekszem az észrevételeket is kezelni.\r\n\r\n(Észrevételt küldött: Hollókövi Béla\r\nMobil: +36(20)9739466)', 'gergo', 'i', '2006-02-10 22:23:53', 'bela', '2011-04-10 15:25:25', '', 'i', 'n'),
	(2, 'Szent Imre templom', 'Budai Ciszterci templom', 0, 12, 5, 'Budapest XI. kerület', 'Villányi út 25', 'Móricz Zsigmond körtérről a Villányi úton gyalog.', '<b>Plébánia:</b>\r\n1114 Budapest, Himfy u. 9.\r\nTelefon: (1) 466 58 86 / 111mellék\r\nFax: (1) 209-2189', 'http://www.szentimre.hu', 'office@szentimre.hu', 3, 53, '<p><span class="alap">A Főv&aacute;rosban letelepedett ciszterci rend <strong>1912-ben</strong> kezdi meg a tan&iacute;t&aacute;st a Szent Imre Gimn&aacute;ziumban. Az esztergomi főhat&oacute;s&aacute;g megb&iacute;zza a rendet a tab&aacute;ni pléb&aacute;nia területéből kihas&iacute;tand&oacute; pléb&aacute;nia megszervezésével. Megalakul 1917-ben exposituraként a <strong>Kiseg&iacute;tő K&aacute;polna Egyesület</strong>. Kelenföldi Nagyboldogasszony Pléb&aacute;nia néven 1923-ban jön létre a pléb&aacute;nia. 1924-re elkészül a Vill&aacute;nyi &uacute;t 3. sz. alatti telken a pléb&aacute;niah&aacute;z és az ideiglenes Nagyboldogasszony templom. Dr. W&auml;lder Gyula egyetemi tan&aacute;rt b&iacute;zz&aacute;k meg a végleges templom tervének elkész&iacute;tésével &uacute;gy, hogy a templom bal oldal&aacute;n a gimn&aacute;zium, jobb oldal&aacute;n a rendh&aacute;z is helyet kapjon. A gimn&aacute;zium 1927&ndash;29-ben készül el. 1930-ban, a Szent Imre jubileumi év alkalm&aacute;val a kerületet Szentimrev&aacute;rosnak nevezik el, ami a pléb&aacute;nia nevébe is belekerül. Szent Imre tiszteletére szenteli fel a barokk st&iacute;lus&uacute; templomot (921 m2) 1938-ban, Szent Imre napj&aacute;n Serédi Jusztini&aacute;n hercegpr&iacute;m&aacute;s. A régi, kis templomot lebontj&aacute;k. A rendh&aacute;z felép&iacute;tését a h&aacute;bor&uacute; meghi&uacute;s&iacute;tja. A nagy léleksz&aacute;m&uacute; pléb&aacute;nia lelkip&aacute;sztori munk&aacute;j&aacute;nak eredményesebb végzésére 5 kiseg&iacute;t&ocirc; k&aacute;polnaigazgat&oacute;s&aacute;g alakult. </span><span class="alap">1950-t&ocirc;l az esztergomi főegyh&aacute;zmegye paps&aacute;ga l&aacute;tja el a pléb&aacute;ni&aacute;t. A pléb&aacute;niaépület a ciszterci rend tulajdona. A templomot 1972&ndash;75 között belsőleg, 1976&ndash;79 között külsőleg fel&uacute;j&iacute;tott&aacute;k. A pléb&aacute;ni&aacute;t 1989-től ismét a ciszterciek l&aacute;tj&aacute;k el. A templom 15 704. törzssz&aacute;m alatt a műemléki nyilv&aacute;ntart&aacute;sban szerepel.</span></p>', 'Búcsú: november 5. és következő vasárnap\r\n\r\nVédőszent: Szent Imre', 'A templom akadálymentesített, mozgáskorlátozottak is megközelíthetik.\r\n\r\nVASÁRNAPI miserend télen, nyáron\r\n08:00 idősek\r\n09:00 családosok\r\n11:00 plébániai ünnepi\r\n18:00 gitáros\r\n21:00 csendes\r\n<b>A 21 órai mise Budapesten a legkésőbb kezdődő vasárnapi szentmise</b> bővebben\r\n<a href="http://www.facebook.com/notes/budai-ciszterci-szent-imre-pl%C3%A9b%C3%A1nia/utols%C3%B3-kapaszkod%C3%B3-a-v%C3%A9szbej%C3%A1rat%C3%BA-szentmise/267194966633663" target="_blank">http://www.facebook.com/notes/budai-ciszterci-szent-imre-pl%C3%A9b%C3%A1nia/utols%C3%B3-kapaszkod%C3%B3-a-v%C3%A9szbej%C3%A1rat%C3%BA-szentmise/267194966633663<br /></a>\r\n', '', '-2028--2028--2028--2028-', '', '2012-03-25', '2012-10-27', '2012-02-27', '', '', '', 'bela', 'i', '2006-02-10 22:23:53', 'gergo', '2010-03-16 12:50:18', '', 'i', 'n'),
	(3, 'Páduai Szent Antal templom', 'Minorita templom', 0, 12, 10, 'Eger', 'Dobó tér', '', '<b>Plébánia:</b><br>3300 Eger\r\nDobó tér 4.\r\nTelefon: (36) 516-613', '', '', 2, 70, '', '', '', '', '', '', '2012-03-25', '2012-10-27', '2010-09-29', '', '', '', 'elemer', 'i', '2006-02-10 22:23:53', 'jeno', '2010-09-29 20:35:53', '', 'i', 'n');
/*!40000 ALTER TABLE `templomok` ENABLE KEYS */;


# Dumping structure for table vp_portal.terkep_geocode
CREATE TABLE IF NOT EXISTS `terkep_geocode` (
  `tid` int(11) NOT NULL DEFAULT '0',
  `address2` varchar(255) NOT NULL DEFAULT '',
  `lng` float DEFAULT NULL,
  `lat` float DEFAULT NULL,
  `checked` varchar(1) DEFAULT NULL,
  PRIMARY KEY (`tid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

# Dumping data for table vp_portal.terkep_geocode: 4 852 rows
DELETE FROM `terkep_geocode`;
/*!40000 ALTER TABLE `terkep_geocode` DISABLE KEYS */;
INSERT INTO `terkep_geocode` (`tid`, `address2`, `lng`, `lat`, `checked`) VALUES
	(2, 'Budapest XI. kerÃ¼let, VillÃ¡nyi Ãºt 25', 19.0422, 47.4786, '2'),
	(3, 'Eger, DobÃ³ tÃ©r', 20.3773, 47.9026, '0'),
	(1, 'Szentendre, ', 19.0756, 47.667, '2');
/*!40000 ALTER TABLE `terkep_geocode` ENABLE KEYS */;


# Dumping structure for table vp_portal.terkep_geocode_suggestion
CREATE TABLE IF NOT EXISTS `terkep_geocode_suggestion` (
  `sid` int(11) NOT NULL AUTO_INCREMENT,
  `tid` int(11) NOT NULL,
  `tchecked` int(11) NOT NULL,
  `slng` float NOT NULL,
  `slat` float NOT NULL,
  `sdistance` int(11) NOT NULL,
  `spoint` int(4) DEFAULT NULL,
  `uid` varchar(50) DEFAULT NULL,
  `stime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `schecked` varchar(1) DEFAULT NULL,
  PRIMARY KEY (`sid`)
) ENGINE=MyISAM AUTO_INCREMENT=64 DEFAULT CHARSET=utf8;

# Dumping data for table vp_portal.terkep_geocode_suggestion: 62 rows
DELETE FROM `terkep_geocode_suggestion`;
/*!40000 ALTER TABLE `terkep_geocode_suggestion` DISABLE KEYS */;
INSERT INTO `terkep_geocode_suggestion` (`sid`, `tid`, `tchecked`, `slng`, `slat`, `sdistance`, `spoint`, `uid`, `stime`, `schecked`) VALUES
	(1, 58, 0, 18.9592, 47.547, 39, 2, '1819502454', '2012-05-13 16:14:24', '1'),
	(2, 2730, 0, 18.9932, 47.5243, 23, 1, '1819502454', '2012-05-13 16:17:49', '1'),
	(3, 224, 0, 18.9282, 47.5096, 233, 60, '1819502454', '2012-05-13 16:24:53', '1'),
	(4, 209, 0, 18.9442, 47.4979, 1817, 250, '1819502454', '2012-05-13 16:26:15', '1'),
	(5, 14, 0, 19.0453, 47.5563, 312, 102, '1065278767', '2012-05-13 17:18:07', '1'),
	(6, 2029, 0, 19.0396, 47.5431, 33, 1, '1065278767', '2012-05-13 17:18:36', '1'),
	(7, 2011, 0, 19.0326, 47.546, 34, 1, '1065278767', '2012-05-13 17:18:51', '1'),
	(8, 18, 0, 19.0448, 47.5386, 67, 5, '1065278767', '2012-05-13 17:19:53', '1'),
	(9, 3669, 0, 19.0684, 47.4903, 28, 1, '1065278767', '2012-05-13 17:22:49', '1'),
	(10, 3667, 0, 19.0665, 47.4831, 59, 4, '1065278767', '2012-05-13 17:23:25', '1'),
	(11, 2188, 0, 19.0598, 47.4749, 96, 10, '1065278767', '2012-05-13 17:23:41', '1'),
	(12, 2, 0, 19.0422, 47.4786, 178, 35, '1065278767', '2012-05-13 17:24:08', '1'),
	(13, 2024, 0, 19.0621, 47.5927, 62, 4, '1065278767', '2012-05-13 17:26:34', '1'),
	(14, 372, 0, 18.7194, 47.5485, 78, 7, '1266937389', '2012-05-13 21:10:31', '1'),
	(15, 2729, 0, 19.0247, 47.5129, 16, 0, '1266937389', '2012-05-13 21:15:52', '1'),
	(16, 2725, 0, 19.0344, 47.5126, 17, 0, '1266937389', '2012-05-13 21:17:15', '1'),
	(17, 2727, 0, 19.0213, 47.5204, 28, 1, '1266937389', '2012-05-13 21:19:17', '1'),
	(18, 14, 0, 19.0361, 47.557, 751, 173, '1819502454', '2012-05-13 22:34:30', '0'),
	(19, 2725, 0, 19.0623, 47.5591, 5585, 62, '1819502454', '2012-05-13 22:36:41', '0'),
	(20, 2727, 0, 19.0672, 47.552, 4939, 70, '1819502454', '2012-05-13 22:37:03', '0'),
	(21, 372, 1, 18.7631, 47.5375, 3554, 98, '1819502454', '2012-05-13 22:44:17', 'x'),
	(23, 209, 2, 18.9469, 47.4972, 220, 54, '1819502454', '2012-05-13 23:59:21', 'x'),
	(24, 224, 1, 18.9282, 47.5096, 234, 61, '1819502454', '2012-05-14 00:05:27', '1'),
	(25, 2738, 0, 18.9415, 47.5422, 445, 126, '1819502454', '2012-05-14 00:28:18', 'x'),
	(26, 1, 0, 19.0756, 47.667, 759, 174, '1207442034', '2012-05-14 05:27:10', '1'),
	(27, 114, 0, 19.0745, 47.6675, 1948, 250, '1207442034', '2012-05-14 05:30:31', '1'),
	(28, 2156, 0, 19.0616, 47.6767, 41, 2, '1207442034', '2012-05-14 05:31:15', '1'),
	(29, 357, 0, 18.6728, 47.3583, 720, 168, '1207442034', '2012-05-14 05:52:00', '1');
/*!40000 ALTER TABLE `terkep_geocode_suggestion` ENABLE KEYS */;


# Dumping structure for table vp_portal.terkep_rank
CREATE TABLE IF NOT EXISTS `terkep_rank` (
  `uid` int(11) NOT NULL,
  `point` int(11) DEFAULT NULL,
  `point_uc` int(11) DEFAULT NULL,
  `suggestion` int(11) DEFAULT NULL,
  `suggestion_uc` int(11) DEFAULT NULL,
  `marker` int(11) DEFAULT NULL,
  `marker_uc` int(11) DEFAULT NULL,
  `distance` int(11) DEFAULT NULL,
  `distance_uc` int(11) DEFAULT NULL,
  `time` varchar(20) NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

# Dumping data for table vp_portal.terkep_rank: 8 rows
DELETE FROM `terkep_rank`;
/*!40000 ALTER TABLE `terkep_rank` DISABLE KEYS */;
INSERT INTO `terkep_rank` (`uid`, `point`, `point_uc`, `suggestion`, `suggestion_uc`, `marker`, `marker_uc`, `distance`, `distance_uc`, `time`) VALUES
	(1, 0, 1, 0, 1, 0, 1, 0, 1, '1336917107'),
	(1819502454, 378, 305, 9, 3, 0, 0, 2471, 11275, '1342108555'),
	(0, 0, 162, 0, 1, 0, 1, 0, 3124, '1336514167'),
	(1065278767, 163, 0, 9, 0, 0, 0, 869, 0, '1342108555'),
	(1266937389, 882, 0, 12, 0, 0, 0, 529941, 0, '1342108555'),
	(1207442034, 810, 0, 17, 0, 0, 0, 4778, 0, '1342108555'),
	(1607303262, 2, 0, 2, 0, 0, 0, 54, 0, '1342108555'),
	(2147483647, 0, 2, 0, 43, 0, 1, 0, 1, '1337550361');
/*!40000 ALTER TABLE `terkep_rank` ENABLE KEYS */;


# Dumping structure for table vp_portal.terkep_vars
CREATE TABLE IF NOT EXISTS `terkep_vars` (
  `name` varchar(40) NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

# Dumping data for table vp_portal.terkep_vars: 6 rows
DELETE FROM `terkep_vars`;
/*!40000 ALTER TABLE `terkep_vars` DISABLE KEYS */;
INSERT INTO `terkep_vars` (`name`, `value`) VALUES
	('id', '6484'),
	('over_query_limit', '1336267081'),
	('templom', '4810'),
	('templom_max', '4810'),
	('templom_checked', '49'),
	('templom_suggested', '62');
/*!40000 ALTER TABLE `terkep_vars` ENABLE KEYS */;


# Dumping structure for table vp_portal.varosok
CREATE TABLE IF NOT EXISTS `varosok` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `irsz` int(4) NOT NULL DEFAULT '0',
  `megye_id` int(2) NOT NULL DEFAULT '0',
  `orszag` int(2) NOT NULL DEFAULT '46',
  `nev` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3728 DEFAULT CHARSET=utf8;

# Dumping data for table vp_portal.varosok: 3 720 rows
DELETE FROM `varosok`;
/*!40000 ALTER TABLE `varosok` DISABLE KEYS */;
INSERT INTO `varosok` (`id`, `irsz`, `megye_id`, `orszag`, `nev`) VALUES
	(1, 4000, 9, 12, 'Debrecen'),
	(2, 7600, 2, 12, 'Pécs'),
	(3, 6700, 6, 12, 'Szeged'),
	(4, 3500, 4, 12, 'Miskolc'),
	(5, 9000, 8, 12, 'Győr');
/*!40000 ALTER TABLE `varosok` ENABLE KEYS */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
