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
-- Dumping data for table `orszagok`
--

LOCK TABLES `orszagok` WRITE;
/*!40000 ALTER TABLE `orszagok` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `orszagok` VALUES
(1,'Ausztria','+43','i','i'),
(2,'Belgium','+32','i','i'),
(3,'Bulgária','+359','i','n'),
(4,'Ciprus','+357','i','n'),
(5,'Cseh Köztársaság','+420','i','i'),
(6,'Dánia','+45','i','n'),
(7,'Észtország','+372','i','n'),
(8,'Finnország','+358','i','n'),
(9,'Franciaország','+33','i','n'),
(10,'Németország','+49','i','n'),
(11,'Görögország','+30','i','n'),
(12,'Magyarország','+36','i','i'),
(13,'Olaszország','+39','i','i'),
(14,'Írország','+353','i','i'),
(15,'Izland','+354','i','n'),
(16,'Lettország','+371','i','n'),
(17,'Liechtenstein','+4175','i','n'),
(18,'Luxemburg','+352','i','n'),
(19,'Litvánia','+370','i','n'),
(20,'Málta','+356','i','n'),
(21,'Hollandia','+31','i','n'),
(22,'Norvégia','+47','i','n'),
(23,'Lengyelország','+48','i','i'),
(24,'Portugália','+351','i','n'),
(25,'Románia','+40','i','i'),
(26,'Szlovákia','+421','i','i'),
(27,'Szlovénia','+386','i','i'),
(28,'Spanyolország','+34','i','n'),
(29,'Svédország','+46','i','n'),
(30,'Egyesült Királyság','+44','i','i'),
(31,'Törökország','+90','i','n'),
(32,'Svájc','+41','i','i'),
(33,'Bosznia-Hercegovina','','i','i'),
(34,'Amerikai Egyesült Államok (USA)','','i','n'),
(35,'Kanada','','i','n'),
(36,'Brazília','','i','n'),
(37,'Japán','','i','n'),
(38,'Argentína','','i','n'),
(39,'Kína','','i','n'),
(40,'Mongólia','','i','n'),
(41,'Egyéb','','i','n'),
(42,'Ausztrália','','i','n'),
(43,'Mexikó','','i','n'),
(44,'Egyiptom','','i','n'),
(45,'Izrael','','i','n'),
(46,'Szerbia-Montenegro','','i','i'),
(47,'Ukrajna','','i','i');
/*!40000 ALTER TABLE `orszagok` ENABLE KEYS */;
UNLOCK TABLES;
commit;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2026-01-09  1:08:33
