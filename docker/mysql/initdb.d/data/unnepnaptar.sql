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
-- Dumping data for table `unnepnaptar`
--

LOCK TABLES `unnepnaptar` WRITE;
/*!40000 ALTER TABLE `unnepnaptar` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `unnepnaptar` VALUES
('2006-04-14','Nagypéntek','n','n','Nincs szentmise'),
('2006-04-15','Nagyszombat','n','n','Este húsvéti vigíliamise (templomonként eltérő)'),
('2006-04-16','Húsvét vasárnap','i','u',''),
('2006-04-17','Húsvét hétfő','i','u','ünnepi miserend (általában vasárnapi)'),
('2006-06-05','Pünkösd','i','u','ünnepi miserend (általában vasárnapi)'),
('2006-11-01','Mindenszentek','i','v',''),
('2006-12-25','Karácsony','i','u','ünnepi miserend (általában vasárnapi)'),
('2006-12-26','Karácsony','i','u','ünnepi miserend (általában vasárnapi)'),
('2007-01-01','Újév','i','u','ünnepi miserend (általában vasárnapi)'),
('2006-08-15','Nagyboldogasszony ünnepe','n','u','ünnepi miserend (általában vasárnapi)'),
('2007-03-15','Nemzeti ünnep','i','v',''),
('2007-10-23','Nemzeti ünnep','i','v',''),
('2007-12-25','Karácsony, Jézus születésének ünnepe','i','u','Ünnepi miserend (általában vasárnapi)'),
('2007-12-26','Karácsony 2. napja','i','u','Ünnepi miserend (általában vasárnapi)'),
('2007-02-21','Hamvazószerda','n','v',''),
('2007-02-25','Nagyböjt első vasárnapja','n','v',''),
('2007-03-04','Nagyböjt 2. vasárnapja','n','v',''),
('2007-03-11','Nagyböjt 3. vasárnapja','n','v',''),
('2007-03-18','Nagyböjt 4. vasárnapja','n','v',''),
('2007-03-25','Nagyböjt 5. vasárnapja','n','v',''),
('2007-04-01','Virágvasárnap','n','v',''),
('2007-04-05','Nagycsütörtök','n','v',''),
('2007-04-06','Nagypéntek','n','n','Nagypénteken nincs mise a templomokban, csak szertartás'),
('2007-04-07','Nagyszombat','n','v',''),
('2007-04-08','Húsvétvasárnap','n','u','Ünnepi miserend (általában vasárnapi)'),
('2007-04-09','Húsvéthétfő','i','u','Ünnepi miserend (általában vasárnapi)'),
('2007-05-20','Urunk Mennybemenetelének ünnepe','n','v',''),
('2007-05-27','Pünkösdvasárnap','n','u','Ünnepi miserend (általában vasárnapi)'),
('2007-05-28','Pünkösdhétfő','i','u','Ünnepi miserend (általában vasárnapi)'),
('2007-06-03','Szentháromság vasárnapja','n','v',''),
('2007-08-15','Nagyboldogasszony ünnepe','n','u','Ünnepi miserend (általában vasárnapi)'),
('2007-08-20','Szent István király ünnepe','n','v',''),
('2007-11-01','Mindenszentek ünnepe','n','v',''),
('2007-11-02','Halottak napja','n','v',''),
('2007-11-25','Krisztus király ünnepe','n','v',''),
('2007-12-02','Advent 1. vasárnapja','n','v',''),
('2007-12-09','Advent 2. vasárnapja','n','v',''),
('2007-12-16','Advent 3. vasárnapja','n','v',''),
('2007-12-23','Advent 4. vasárnapja','n','v',''),
('2007-12-30','Szent Család ünnepe','n','v','');
/*!40000 ALTER TABLE `unnepnaptar` ENABLE KEYS */;
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
