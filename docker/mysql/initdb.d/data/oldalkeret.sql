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
-- Dumping data for table `oldalkeret`
--

LOCK TABLES `oldalkeret` WRITE;
/*!40000 ALTER TABLE `oldalkeret` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `oldalkeret` VALUES
(2,7,'szavazasm','hasabdoboz',2,'hu',2,0,'szavazás menü'),
(1,15,'esemenynaptar','hasabdoboz',2,'hu',3,0,'eseménynaptár menü'),
(2,20,'latogatok','hasabdoboz',1,'hu',5,0,'látogatók'),
(10,2,'foreklam','hasabdoboz',2,'hu',2,0,'főreklám'),
(1,7,'szavazasm','hasabdoboz',2,'hu',4,0,'szavazás menü'),
(1,20,'latogatok','hasabdoboz',2,'hu',5,0,'látogatók'),
(10,29,'feltoltes','hasabdoboz',1,'hu',4,0,'Feltöltés'),
(1,32,'naptar','hasabdoboz',2,'hu',2,0,'naptár'),
(1,1,'hirek','hasabdoboz',1,'hu',2,0,'hírek menü'),
(10,29,'feltoltes','hasabdoboz',1,'hu',3,0,'feltöltés'),
(1,35,'eszrevetelek','hasab_eszrevetelek',2,'hu',3,1,'észrevétel menü'),
(2,36,'unnep','hasab_unnep',2,'hu',1,1,'Ünnep, fontos üzenet');
/*!40000 ALTER TABLE `oldalkeret` ENABLE KEYS */;
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
