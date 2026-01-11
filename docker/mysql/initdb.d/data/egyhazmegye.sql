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
-- Dumping data for table `egyhazmegye`
--

LOCK TABLES `egyhazmegye` WRITE;
/*!40000 ALTER TABLE `egyhazmegye` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `egyhazmegye` VALUES
(1,'Debrecen-Nyíregyháza',1,'i','','','i',5635585),
(2,'Eger',2,'i','nbalazs','einsti442@gmail.com','i',5635586),
(3,'Esztergom-Budapest',3,'i','judy83','','i',5635587),
(4,'Győr',4,'i','ÁgiM','magi@orsolyita.hu','n',5635589),
(6,'Kalocsa-Kecskemét',6,'i','','','i',5635590),
(7,'Kaposvár',7,'i','bebee','dobi411@citromail.hu','i',12535238),
(8,'Pécs',10,'i','f.orsolya','','i',5638351),
(9,'Szeged-Csanád',11,'i','','','i',5635591),
(10,'Székesfehérvár',12,'i','Marian','marian@szfvar.katolikus.hu','i',5635765),
(11,'Szombathely',13,'i','Istvánatya','his1969@gmail.com','n',5665523),
(12,'Vác',14,'i','zsotom','annatom@freemail.hu','i',5635592),
(13,'Veszprém',15,'i','','','i',13004858),
(15,'Pannonhalmi Területi Apátság',9,'i','','serviam@freemail.hu','i',13005188),
(16,'Tábori Püspökség',16,'i','','','i',NULL),
(17,'Miskolci egyházmegye (gk)',8,'i','','','i',14735845),
(18,'Hajdúdorogi metropólia (gk)',5,'i','szgabor','','i',14735896),
(19,'Munkácsi em. (Kárpátalja)',17,'i','','','i',NULL),
(20,'Gyulafehérvári főem. (Erdély)',18,'i','','','i',NULL),
(21,'Szatmári em. (Erdély)',20,'i','','','i',NULL),
(22,'Nagyváradi egyházmegye (Erdély)',19,'i','spzoltan','sp.zoltan@yahoo.com','i',NULL),
(23,'Temesvári em. (Erdély)',21,'i','','','i',NULL),
(24,'Szabadkai em. (Délvidék)',23,'i','','','i',NULL),
(25,'Nagybecskereki em. (Délvidék)',22,'i','','','i',NULL),
(26,'Kassai em. (Felvidék)',24,'i','','','i',NULL),
(27,'Rozsnyói em. (Felvidék)',28,'i','VoxCordis','simonjani@stonline.sk','i',NULL),
(28,'Nyitrai egyházmegye (Felvidék)',25,'i','','','i',NULL),
(29,'Nagyszombati főem.(Felvidék)',26,'i','','','i',NULL),
(30,'Eisenstadti',30,'i','','','i',NULL),
(32,'Pozsonyi főem. (Felvidék)',27,'i','','','i',NULL),
(33,'Egyéb',50,'i','','','i',NULL),
(34,'Nyíregyházi egyházmegye (gk)',0,'i','','','i',14730150);
/*!40000 ALTER TABLE `egyhazmegye` ENABLE KEYS */;
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
