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
-- Dumping data for table `modulok`
--

LOCK TABLES `modulok` WRITE;
/*!40000 ALTER TABLE `modulok` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `modulok` VALUES
(25,'chat','','chat','aloldal',1,'',0,'n','i'),
(9,'admin','admin menü','admin','alap',1,'',0,'n','i'),
(17,'infó oldalak','','alap','alap',0,'',43094,'n','i'),
(21,'user admin','admin','admin_user','aloldal',1,'user',219,'n','i'),
(26,'miserend','','miserend','alap',0,'',0,'n','i'),
(27,'miserend admin','admin','admin_miserend','aloldal',1,'miserend',0,'n','i'),
(28,'regisztráció','','regisztracio','alap',0,'',0,'i','i'),
(29,'feltöltés','anyagok feltöltése (pl. miserend, hír) felhasználók által','feltoltes','aloldal',1,'',0,'n','i'),
(30,'igenaptár','','igenaptar','alap',0,'',0,'n','i'),
(31,'igenaptár admin','admin','admin_igenaptar','aloldal',1,'igenaptar',0,'n','i'),
(37,'terkep','','terkep','alap',0,'',0,'i','i');
/*!40000 ALTER TABLE `modulok` ENABLE KEYS */;
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
