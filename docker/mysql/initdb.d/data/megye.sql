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
-- Dumping data for table `megye`
--

LOCK TABLES `megye` WRITE;
/*!40000 ALTER TABLE `megye` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `megye` VALUES
(1,'Bács-Kiskun',12,''),
(2,'Baranya',12,''),
(3,'Békés',12,''),
(4,'Borsod-Abaúj-Zemplén',12,''),
(5,'Budapest',12,''),
(6,'Csongrád',12,''),
(7,'Fejér',12,''),
(8,'Győr-Moson-Sopron',12,''),
(9,'Hajdú-Bihar',12,''),
(10,'Heves',12,''),
(11,'Jász-Nagykun-Szolnok',12,''),
(12,'Komárom-Esztergom',12,''),
(13,'Nógrád',12,''),
(14,'Pest',12,''),
(15,'Somogy',12,''),
(16,'Szabolcs-Szatmár-Bereg',12,''),
(17,'Tolna',12,''),
(18,'Vas',12,''),
(19,'Veszprém',12,''),
(20,'Zala',12,''),
(21,'Külföld',0,'');
/*!40000 ALTER TABLE `megye` ENABLE KEYS */;
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
