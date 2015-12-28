-- MySQL dump 10.13  Distrib 5.6.17, for osx10.6 (i386)
--
-- Host: localhost    Database: miserend
-- ------------------------------------------------------
-- Server version	5.5.38-0ubuntu0.14.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `chat`
--

DROP TABLE IF EXISTS `chat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `datum` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `user` varchar(20) NOT NULL DEFAULT '',
  `kinek` varchar(20) NOT NULL DEFAULT '',
  `szoveg` tinytext NOT NULL,
  `ip` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=570 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chat`
--

LOCK TABLES `chat` WRITE;
/*!40000 ALTER TABLE `chat` DISABLE KEYS */;
INSERT INTO `chat` VALUES (1,'2013-01-04 11:22:12','bela','jeno','Nézem ezek szerint csak a karácsonyi levelet tettem még fel.','195.54.91.25');
INSERT INTO `chat` VALUES (2,'2013-01-04 11:27:41','bela','jeno','A letölthető változat az otthoni gépemen van, este hozzáteszem azt is.','195.54.91.85');
/*!40000 ALTER TABLE `chat` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `distances`
--

DROP TABLE IF EXISTS `distances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `distances` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `from` int(11) NOT NULL,
  `to` int(11) NOT NULL,
  `distance` float NOT NULL,
  `toupdate` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `tid1` (`from`,`to`),
  KEY `FK_to_idx` (`to`),
  CONSTRAINT `FK_to` FOREIGN KEY (`to`) REFERENCES `templomok` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `FK_from` FOREIGN KEY (`from`) REFERENCES `templomok` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=66224 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `distances`
--

LOCK TABLES `distances` WRITE;
/*!40000 ALTER TABLE `distances` DISABLE KEYS */;
INSERT INTO `distances` VALUES (1,1,2,21065.3,'2015-02-18 23:54:40',1,'0000-00-00 00:00:00','0000-00-00 00:00:00');
INSERT INTO `distances` VALUES (2,1,3,100727,'2015-02-18 23:56:05',1,'0000-00-00 00:00:00','0000-00-00 00:00:00');
INSERT INTO `distances` VALUES (3,1,7,105922,'2015-02-18 23:56:04',1,'0000-00-00 00:00:00','0000-00-00 00:00:00');
INSERT INTO `distances` VALUES (4,1,14,12506.2,'2015-02-18 23:52:51',1,'0000-00-00 00:00:00','0000-00-00 00:00:00');
INSERT INTO `distances` VALUES (5,1,35,19876.7,'2015-02-18 23:54:57',1,'0000-00-00 00:00:00','0000-00-00 00:00:00');
INSERT INTO `distances` VALUES (6,1,36,19357.6,'2015-02-18 23:53:50',1,'0000-00-00 00:00:00','0000-00-00 00:00:00');
INSERT INTO `distances` VALUES (7,1,37,18533.3,'2015-02-18 23:53:50',1,'0000-00-00 00:00:00','0000-00-00 00:00:00');
INSERT INTO `distances` VALUES (8,1,38,19495.9,'2015-02-18 23:54:14',1,'0000-00-00 00:00:00','0000-00-00 00:00:00');
INSERT INTO `distances` VALUES (9,1,102,72869.3,'2015-02-18 23:56:04',NULL,'0000-00-00 00:00:00','0000-00-00 00:00:00');
INSERT INTO `distances` VALUES (10,1,114,1499.9,'2015-02-19 00:03:55',NULL,'0000-00-00 00:00:00','0000-00-00 00:00:00');
/*!40000 ALTER TABLE `distances` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `egyhazmegye`
--

DROP TABLE IF EXISTS `egyhazmegye`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `egyhazmegye` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nev` varchar(250) NOT NULL DEFAULT '',
  `sorrend` int(3) NOT NULL DEFAULT '0',
  `ok` enum('i','n') NOT NULL DEFAULT 'i',
  `felelos` varchar(20) NOT NULL DEFAULT '',
  `email` varchar(50) NOT NULL DEFAULT '',
  `csakez` enum('i','n') NOT NULL DEFAULT 'i',
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=34 DEFAULT CHARSET=utf8 PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `egyhazmegye`
--

LOCK TABLES `egyhazmegye` WRITE;
/*!40000 ALTER TABLE `egyhazmegye` DISABLE KEYS */;
INSERT INTO `egyhazmegye` VALUES (1,'Debrecen-Nyíregyháza',1,'i','','','i');
INSERT INTO `egyhazmegye` VALUES (2,'Eger',2,'i','nbalazs','','i');
INSERT INTO `egyhazmegye` VALUES (3,'Esztergom-Budapest',3,'i','judy83','','i');
INSERT INTO `egyhazmegye` VALUES (4,'Győr',4,'i','','','n');
INSERT INTO `egyhazmegye` VALUES (6,'Kalocsa-Kecskemét',6,'i','','','i');
INSERT INTO `egyhazmegye` VALUES (7,'Kaposvár',7,'i','bebee','dobi411@citromail.hu','i');
INSERT INTO `egyhazmegye` VALUES (8,'Pécs',10,'i','f.orsolya','','i');
INSERT INTO `egyhazmegye` VALUES (9,'Szeged-Csanád',11,'i','','','i');
INSERT INTO `egyhazmegye` VALUES (10,'Székesfehérvár',12,'i','Marian','','i');
INSERT INTO `egyhazmegye` VALUES (11,'Szombathely',13,'i','Istvánatya','his1969@gmail.com','n');
INSERT INTO `egyhazmegye` VALUES (12,'Vác',14,'i','zsotom','','i');
INSERT INTO `egyhazmegye` VALUES (13,'Veszprém',15,'i','','','i');
INSERT INTO `egyhazmegye` VALUES (15,'Pannonhalmi Területi Apátság',9,'i','','','i');
INSERT INTO `egyhazmegye` VALUES (16,'Tábori Püspökség',16,'i','','','i');
INSERT INTO `egyhazmegye` VALUES (17,'Miskolci Apostoli Exarchátus (gk)',8,'i','','','i');
INSERT INTO `egyhazmegye` VALUES (18,'Hajdúdorogi egyházmegye (gk)',5,'i','','','i');
INSERT INTO `egyhazmegye` VALUES (19,'Munkácsi em. (Kárpátalja)',17,'i','','','i');
INSERT INTO `egyhazmegye` VALUES (20,'Gyulafehérvári főem. (Erdély)',18,'i','','','i');
INSERT INTO `egyhazmegye` VALUES (21,'Szatmári em. (Erdély)',20,'i','','','i');
INSERT INTO `egyhazmegye` VALUES (22,'Nagyváradi egyházmegye (Erdély)',19,'i','spzoltan','','i');
INSERT INTO `egyhazmegye` VALUES (23,'Temesvári em. (Erdély)',21,'i','','','i');
INSERT INTO `egyhazmegye` VALUES (24,'Szabadkai em. (Délvidék)',23,'i','','','i');
INSERT INTO `egyhazmegye` VALUES (25,'Nagybecskereki em. (Délvidék)',22,'i','','','i');
INSERT INTO `egyhazmegye` VALUES (26,'Kassai em. (Felvidék)',24,'i','','','i');
INSERT INTO `egyhazmegye` VALUES (27,'Rozsnyói em. (Felvidék)',28,'i','VoxCordis','','i');
INSERT INTO `egyhazmegye` VALUES (28,'Nyitrai egyházmegye (Felvidék)',25,'i','','','i');
INSERT INTO `egyhazmegye` VALUES (29,'Nagyszombati főem.(Felvidék)',26,'i','','','i');
INSERT INTO `egyhazmegye` VALUES (30,'Eisenstadti',30,'i','','','i');
INSERT INTO `egyhazmegye` VALUES (32,'Pozsonyi főem. (Felvidék)',27,'i','','','i');
INSERT INTO `egyhazmegye` VALUES (33,'Egyéb',50,'i','','','i');
/*!40000 ALTER TABLE `egyhazmegye` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `espereskerulet`
--

DROP TABLE IF EXISTS `espereskerulet`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `espereskerulet` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `ehm` int(2) NOT NULL DEFAULT '0',
  `nev` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=235 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `espereskerulet`
--

LOCK TABLES `espereskerulet` WRITE;
/*!40000 ALTER TABLE `espereskerulet` DISABLE KEYS */;
INSERT INTO `espereskerulet` VALUES (1,4,'Csepregi');
INSERT INTO `espereskerulet` VALUES (2,4,'Csornai');
INSERT INTO `espereskerulet` VALUES (3,4,'Győri Székesegyházi');
INSERT INTO `espereskerulet` VALUES (4,4,'Fertőszéplaki');
INSERT INTO `espereskerulet` VALUES (5,4,'Kapuvári');
INSERT INTO `espereskerulet` VALUES (6,4,'Komáromi');
INSERT INTO `espereskerulet` VALUES (7,4,'Mosoni');
INSERT INTO `espereskerulet` VALUES (8,4,'Soproni');
INSERT INTO `espereskerulet` VALUES (9,4,'Szanyi');
INSERT INTO `espereskerulet` VALUES (10,4,'Szigetközi');
INSERT INTO `espereskerulet` VALUES (11,4,'Tatai');
INSERT INTO `espereskerulet` VALUES (12,4,'Téti');
INSERT INTO `espereskerulet` VALUES (13,7,'Kaposvári');
INSERT INTO `espereskerulet` VALUES (14,7,'Segesdi');
INSERT INTO `espereskerulet` VALUES (15,7,'Barcsi');
INSERT INTO `espereskerulet` VALUES (16,7,'Dél-balatoni');
INSERT INTO `espereskerulet` VALUES (17,7,'Somogyvári');
INSERT INTO `espereskerulet` VALUES (18,7,'Nagykanizsai');
INSERT INTO `espereskerulet` VALUES (19,7,'Csurgói');
INSERT INTO `espereskerulet` VALUES (20,6,'Kalocsai');
INSERT INTO `espereskerulet` VALUES (21,6,'Keceli');
INSERT INTO `espereskerulet` VALUES (22,6,'Bácsalmási');
INSERT INTO `espereskerulet` VALUES (23,6,'Jánoshalmi');
INSERT INTO `espereskerulet` VALUES (24,6,'Bajai');
INSERT INTO `espereskerulet` VALUES (25,6,'Hajósi');
INSERT INTO `espereskerulet` VALUES (26,6,'Kecskeméti');
INSERT INTO `espereskerulet` VALUES (27,6,'Félegyházi');
INSERT INTO `espereskerulet` VALUES (28,6,'Majsai');
INSERT INTO `espereskerulet` VALUES (29,6,'Solti');
INSERT INTO `espereskerulet` VALUES (30,8,'Pécsi');
INSERT INTO `espereskerulet` VALUES (31,8,'Siklósi');
INSERT INTO `espereskerulet` VALUES (32,8,'Mohácsi');
INSERT INTO `espereskerulet` VALUES (33,8,'Szigetvári');
INSERT INTO `espereskerulet` VALUES (34,8,'Komlói');
INSERT INTO `espereskerulet` VALUES (35,8,'Szekszárdi');
INSERT INTO `espereskerulet` VALUES (36,8,'Paksi');
INSERT INTO `espereskerulet` VALUES (37,8,'Dombóvári');
INSERT INTO `espereskerulet` VALUES (38,8,'Tamási');
INSERT INTO `espereskerulet` VALUES (39,9,'Szegedi');
INSERT INTO `espereskerulet` VALUES (40,9,'Kisteleki');
INSERT INTO `espereskerulet` VALUES (41,9,'Szentesi');
INSERT INTO `espereskerulet` VALUES (42,9,'Makói');
INSERT INTO `espereskerulet` VALUES (43,9,'Szarvasi');
INSERT INTO `espereskerulet` VALUES (44,9,'Gyulai');
INSERT INTO `espereskerulet` VALUES (45,9,'Orosházi');
INSERT INTO `espereskerulet` VALUES (46,9,'Mezőkovácsi');
INSERT INTO `espereskerulet` VALUES (47,3,'Esztergomi');
INSERT INTO `espereskerulet` VALUES (48,3,'Szentendrei');
INSERT INTO `espereskerulet` VALUES (49,3,'Dorogi');
INSERT INTO `espereskerulet` VALUES (50,3,'Bajóti');
INSERT INTO `espereskerulet` VALUES (51,3,'Óbudai');
INSERT INTO `espereskerulet` VALUES (52,3,'Budai-Északi');
INSERT INTO `espereskerulet` VALUES (53,3,'Budai-Középső');
INSERT INTO `espereskerulet` VALUES (54,3,'Budai-Déli');
INSERT INTO `espereskerulet` VALUES (55,3,'Pesti-Belső');
INSERT INTO `espereskerulet` VALUES (56,3,'Pesti-Déli');
INSERT INTO `espereskerulet` VALUES (57,3,'Pesti-Középső');
INSERT INTO `espereskerulet` VALUES (58,3,'Pesti-Északi');
INSERT INTO `espereskerulet` VALUES (59,3,'Újpest-Rákospalotai');
INSERT INTO `espereskerulet` VALUES (60,3,'Rákosi');
INSERT INTO `espereskerulet` VALUES (61,3,'Kispest-Pestszenterzsébeti');
INSERT INTO `espereskerulet` VALUES (62,3,'Pestszentlőrinc-Soroksári');
INSERT INTO `espereskerulet` VALUES (63,1,'Kisvárdai');
INSERT INTO `espereskerulet` VALUES (64,1,'Polgári');
INSERT INTO `espereskerulet` VALUES (65,1,'Nyíregyházi');
INSERT INTO `espereskerulet` VALUES (66,1,'Nagykállói');
INSERT INTO `espereskerulet` VALUES (67,1,'Szatmári');
INSERT INTO `espereskerulet` VALUES (68,1,'Debreceni');
INSERT INTO `espereskerulet` VALUES (69,1,'Berettyóújfalui');
INSERT INTO `espereskerulet` VALUES (70,2,'Eger és vidéke');
INSERT INTO `espereskerulet` VALUES (71,2,'Mezőkövesdi');
INSERT INTO `espereskerulet` VALUES (72,2,'Ónodi');
INSERT INTO `espereskerulet` VALUES (73,2,'Miskolci');
INSERT INTO `espereskerulet` VALUES (74,2,'Szendrői');
INSERT INTO `espereskerulet` VALUES (75,10,'Székesfehérvári');
INSERT INTO `espereskerulet` VALUES (76,10,'Vértesi');
INSERT INTO `espereskerulet` VALUES (77,10,'Budai');
INSERT INTO `espereskerulet` VALUES (78,10,'Dunamenti');
INSERT INTO `espereskerulet` VALUES (79,10,'Mezőföldi');
INSERT INTO `espereskerulet` VALUES (80,13,'Veszprémi');
INSERT INTO `espereskerulet` VALUES (81,13,'Várpalotai');
INSERT INTO `espereskerulet` VALUES (82,13,'Pápai');
INSERT INTO `espereskerulet` VALUES (83,13,'Zirci');
INSERT INTO `espereskerulet` VALUES (84,13,'Sümegi');
INSERT INTO `espereskerulet` VALUES (85,13,'Tapolcai');
INSERT INTO `espereskerulet` VALUES (86,13,'Keszthely-Zalaszentgróti');
INSERT INTO `espereskerulet` VALUES (224,17,'Sajópálfalai');
INSERT INTO `espereskerulet` VALUES (88,11,'Szombathelyi');
INSERT INTO `espereskerulet` VALUES (89,11,'Jáki');
INSERT INTO `espereskerulet` VALUES (90,11,'Kőszegi');
INSERT INTO `espereskerulet` VALUES (91,11,'Sárvári');
INSERT INTO `espereskerulet` VALUES (92,11,'Kemenesaljai');
INSERT INTO `espereskerulet` VALUES (93,11,'Vasvári');
INSERT INTO `espereskerulet` VALUES (94,11,'Őrségi');
INSERT INTO `espereskerulet` VALUES (95,11,'Zalaegerszegi');
INSERT INTO `espereskerulet` VALUES (96,11,'Letenyei');
INSERT INTO `espereskerulet` VALUES (97,11,'Lenti');
INSERT INTO `espereskerulet` VALUES (98,12,'Váci');
INSERT INTO `espereskerulet` VALUES (99,12,'Hatvani');
INSERT INTO `espereskerulet` VALUES (100,12,'Bercel-Kállói');
INSERT INTO `espereskerulet` VALUES (101,12,'Pásztói');
INSERT INTO `espereskerulet` VALUES (102,12,'Gödöllői');
INSERT INTO `espereskerulet` VALUES (105,12,'Nagymarosi');
INSERT INTO `espereskerulet` VALUES (106,12,'Érsekvadkerti');
INSERT INTO `espereskerulet` VALUES (107,12,'Szécsényi');
INSERT INTO `espereskerulet` VALUES (108,12,'Salgótarjáni');
INSERT INTO `espereskerulet` VALUES (109,12,'Kisterenyei');
INSERT INTO `espereskerulet` VALUES (110,12,'Szolnoki');
INSERT INTO `espereskerulet` VALUES (112,12,'Nagykátai');
INSERT INTO `espereskerulet` VALUES (137,2,'Patai');
INSERT INTO `espereskerulet` VALUES (136,2,'Törökszentmiklósi');
INSERT INTO `espereskerulet` VALUES (135,2,'Hevesi');
INSERT INTO `espereskerulet` VALUES (134,2,'Füzesabonyi');
INSERT INTO `espereskerulet` VALUES (133,2,'Parádi');
INSERT INTO `espereskerulet` VALUES (131,2,'Ózdi');
INSERT INTO `espereskerulet` VALUES (138,2,'Gyöngyösi');
INSERT INTO `espereskerulet` VALUES (139,2,'Jászberényi');
INSERT INTO `espereskerulet` VALUES (140,2,'Jászapáti');
INSERT INTO `espereskerulet` VALUES (141,2,'Gönci');
INSERT INTO `espereskerulet` VALUES (142,2,'Szikszó-Encsi');
INSERT INTO `espereskerulet` VALUES (143,2,'Szerencsi');
INSERT INTO `espereskerulet` VALUES (144,2,'Sárospataki');
INSERT INTO `espereskerulet` VALUES (145,2,'Sátoraljaújhely-Bodrogközi');
INSERT INTO `espereskerulet` VALUES (146,15,'Pannonhalmi');
INSERT INTO `espereskerulet` VALUES (147,20,'Gyulafehérvári');
INSERT INTO `espereskerulet` VALUES (148,20,'Hunyadi');
INSERT INTO `espereskerulet` VALUES (149,20,'Szeben-fogarasi');
INSERT INTO `espereskerulet` VALUES (150,20,'Erzsébetvárosi');
INSERT INTO `espereskerulet` VALUES (151,20,'Sepsi-barcasági');
INSERT INTO `espereskerulet` VALUES (152,20,'Kézdi-orbai');
INSERT INTO `espereskerulet` VALUES (153,20,'Kolozs-dobokai');
INSERT INTO `espereskerulet` VALUES (154,20,'Belső-szolnoki');
INSERT INTO `espereskerulet` VALUES (155,20,'Torda-aranyosi');
INSERT INTO `espereskerulet` VALUES (156,20,'Külüllői');
INSERT INTO `espereskerulet` VALUES (157,20,'Marosi');
INSERT INTO `espereskerulet` VALUES (158,20,'Alcsíki');
INSERT INTO `espereskerulet` VALUES (159,20,'Felcsíki');
INSERT INTO `espereskerulet` VALUES (160,20,'Gyergyói');
INSERT INTO `espereskerulet` VALUES (161,20,'Székelyudvarhelyi');
INSERT INTO `espereskerulet` VALUES (162,27,'Gömör-Kishonti');
INSERT INTO `espereskerulet` VALUES (163,27,'Rozsnyói');
INSERT INTO `espereskerulet` VALUES (164,27,'Nógrádi (Losonci)');
INSERT INTO `espereskerulet` VALUES (165,27,'Jászói (Tornai)');
INSERT INTO `espereskerulet` VALUES (166,24,'Topolyai');
INSERT INTO `espereskerulet` VALUES (167,24,'Apatini');
INSERT INTO `espereskerulet` VALUES (168,24,'Bácsi');
INSERT INTO `espereskerulet` VALUES (169,24,'Becsei');
INSERT INTO `espereskerulet` VALUES (170,24,'Kanizsai');
INSERT INTO `espereskerulet` VALUES (171,24,'Kúlai');
INSERT INTO `espereskerulet` VALUES (172,24,'Szabadka alsóvárosi');
INSERT INTO `espereskerulet` VALUES (173,24,'Szabadka belvárosi');
INSERT INTO `espereskerulet` VALUES (174,28,'Érsekújvári');
INSERT INTO `espereskerulet` VALUES (175,24,'Szabadka külvárosi');
INSERT INTO `espereskerulet` VALUES (176,24,'Újvidéki');
INSERT INTO `espereskerulet` VALUES (177,24,'Zentai');
INSERT INTO `espereskerulet` VALUES (178,24,'Zombori');
INSERT INTO `espereskerulet` VALUES (179,25,'Központi');
INSERT INTO `espereskerulet` VALUES (180,25,'Északi');
INSERT INTO `espereskerulet` VALUES (181,25,'Keleti');
INSERT INTO `espereskerulet` VALUES (182,25,'Déli');
INSERT INTO `espereskerulet` VALUES (183,21,'Szatmári');
INSERT INTO `espereskerulet` VALUES (184,21,'Ugocsai');
INSERT INTO `espereskerulet` VALUES (185,21,'Erdődi');
INSERT INTO `espereskerulet` VALUES (186,21,'Nagykároly I.');
INSERT INTO `espereskerulet` VALUES (187,21,'Nagykároly II.');
INSERT INTO `espereskerulet` VALUES (188,21,'Nagybányai');
INSERT INTO `espereskerulet` VALUES (189,21,'Máramarosszigeti');
INSERT INTO `espereskerulet` VALUES (190,19,'Beregszászi');
INSERT INTO `espereskerulet` VALUES (191,19,'Huszti');
INSERT INTO `espereskerulet` VALUES (192,19,'Munkácsi');
INSERT INTO `espereskerulet` VALUES (193,19,'Szigeti');
INSERT INTO `espereskerulet` VALUES (194,19,'Ugocsai');
INSERT INTO `espereskerulet` VALUES (195,19,'Ung alsó');
INSERT INTO `espereskerulet` VALUES (196,19,'Ung felső');
INSERT INTO `espereskerulet` VALUES (197,22,'Váradi');
INSERT INTO `espereskerulet` VALUES (198,22,'Várad-környéki');
INSERT INTO `espereskerulet` VALUES (199,22,'Margittai');
INSERT INTO `espereskerulet` VALUES (200,22,'Székelyhídi');
INSERT INTO `espereskerulet` VALUES (201,22,'Tenkei');
INSERT INTO `espereskerulet` VALUES (202,22,'Szilágysomlyói');
INSERT INTO `espereskerulet` VALUES (203,22,'Tasnádi');
INSERT INTO `espereskerulet` VALUES (204,29,'Vágsellyei');
INSERT INTO `espereskerulet` VALUES (205,18,'Hajdúdorogi');
INSERT INTO `espereskerulet` VALUES (206,18,'Karászi');
INSERT INTO `espereskerulet` VALUES (207,18,'Budai');
INSERT INTO `espereskerulet` VALUES (208,18,'Pesti');
INSERT INTO `espereskerulet` VALUES (209,17,'Sátoraljaújhelyi');
INSERT INTO `espereskerulet` VALUES (210,17,'Borsodi');
INSERT INTO `espereskerulet` VALUES (211,18,'Nyíri');
INSERT INTO `espereskerulet` VALUES (212,18,'Hegyaljai');
INSERT INTO `espereskerulet` VALUES (213,18,'Nagylétai');
INSERT INTO `espereskerulet` VALUES (214,18,'Máriapócsi');
INSERT INTO `espereskerulet` VALUES (215,18,'Nyírbélteki');
INSERT INTO `espereskerulet` VALUES (216,18,'Nyíregyházi');
INSERT INTO `espereskerulet` VALUES (217,18,'Tiszai');
INSERT INTO `espereskerulet` VALUES (218,18,'Csengeri');
INSERT INTO `espereskerulet` VALUES (219,17,'Miskolci');
INSERT INTO `espereskerulet` VALUES (220,17,'Abaúj-Hegyaljai');
INSERT INTO `espereskerulet` VALUES (221,17,'Csereháti');
INSERT INTO `espereskerulet` VALUES (222,29,'Dunaszerdahelyi');
INSERT INTO `espereskerulet` VALUES (223,12,'Dabasi');
INSERT INTO `espereskerulet` VALUES (225,29,'Galántai');
INSERT INTO `espereskerulet` VALUES (226,29,'Galgóci');
INSERT INTO `espereskerulet` VALUES (227,29,'Komáromi');
INSERT INTO `espereskerulet` VALUES (228,29,'Nagyszombati');
INSERT INTO `espereskerulet` VALUES (229,29,'Nemsói');
INSERT INTO `espereskerulet` VALUES (230,29,'Ógyallai');
INSERT INTO `espereskerulet` VALUES (231,29,'Pöstyéni');
INSERT INTO `espereskerulet` VALUES (232,29,'Vágújhelyi');
INSERT INTO `espereskerulet` VALUES (233,32,'Somorjai');
INSERT INTO `espereskerulet` VALUES (234,33,'Egyéb');
/*!40000 ALTER TABLE `espereskerulet` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `remarks`
--

DROP TABLE IF EXISTS `remarks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `remarks` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `nev` varchar(50) NOT NULL DEFAULT '',
  `login` varchar(20) NOT NULL DEFAULT '',
  `email` varchar(50) NOT NULL DEFAULT '',
  `megbizhato` enum('?','i','n','e') NOT NULL DEFAULT '?',
  `church_id` int(11) NOT NULL DEFAULT '0',
  `allapot` enum('u','f','j') NOT NULL DEFAULT 'u',
  `admin` varchar(20) NOT NULL DEFAULT '',
  `admindatum` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `leiras` text NOT NULL,
  `adminmegj` text,
  `log` text,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `index2` (`id`,`church_id`,`allapot`),
  KEY `index1` (`id`,`church_id`),
  KEY `FK_church_id_idx` (`church_id`),
  CONSTRAINT `FK_church_id` FOREIGN KEY (`church_id`) REFERENCES `templomok` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=8197 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `remarks`
--

LOCK TABLES `remarks` WRITE;
/*!40000 ALTER TABLE `remarks` DISABLE KEYS */;
INSERT INTO `remarks` VALUES (103,'Ólmos Nikolett','niki','valami@valami.hu','i','2015-04-16 13:17:26',644,'j','elem','Dicsértessék a Jézus Krisztus!\r\n\r\nÉszrevételeim vannak. Fogadjátok szeretettel.','','','2015-04-16 20:11:10','0000-00-00 00:00:00');
INSERT INTO `remarks` VALUES (102,'Bela','*vendeg*','belea@skocia.elemer','e',125,'j','elem','2015-04-16 13:07:35','Sziasztok!\r\n A kápolna miserendjében van egy kis eltérés. Íme: xxxx stb.','<img src=img/edit.gif align=absmiddle title=\'elem (2015-04-16 13:03)\'> miserend küldés ','','2015-04-16 11:08:16','0000-00-00 00:00:00');
UNLOCK TABLES;

--
-- Table structure for table `photos`
--

DROP TABLE IF EXISTS `photos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `photos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `church_id` int(11) NOT NULL,
  `filename` varchar(100) NOT NULL DEFAULT '',
  `title` varchar(250) NOT NULL DEFAULT '',
  `order` int(2) NOT NULL DEFAULT '0',
  `flag` enum('i','n') NOT NULL DEFAULT 'i',
  `height` int(11) DEFAULT NULL,
  `width` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `church_id` (`church_id`),
  CONSTRAINT `FKchurch` FOREIGN KEY (`church_id`) REFERENCES `templomok` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=23598 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `photos`
--

LOCK TABLES `photos` WRITE;
/*!40000 ALTER TABLE `photos` DISABLE KEYS */;
INSERT INTO `photos` VALUES (23,19,'111_1143.jpg','A torony',0,'i',800,600,'0000-00-00 00:00:00','0000-00-00 00:00:00');
INSERT INTO `photos` VALUES (24,20,'111_1147.jpg','',0,'i',800,600,'0000-00-00 00:00:00','0000-00-00 00:00:00');
INSERT INTO `photos` VALUES (25,20,'111_1145.jpg','',0,'i',800,600,'0000-00-00 00:00:00','0000-00-00 00:00:00');
INSERT INTO `photos` VALUES (22,19,'111_1129.jpg','A templom bejárata',0,'n',800,600,'0000-00-00 00:00:00','0000-00-00 00:00:00');
INSERT INTO `photos` VALUES (27,23,'heviz2.jpg','Jézus Szíve Templom',0,'i',600,450,'0000-00-00 00:00:00','0000-00-00 00:00:00');
INSERT INTO `photos` VALUES (26,22,'heviz.jpg','',0,'i',453,600,'0000-00-00 00:00:00','0000-00-00 00:00:00');
INSERT INTO `photos` VALUES (31,28,'homokvar.jpg','homokvár',2,'i',NULL,NULL,'0000-00-00 00:00:00','0000-00-00 00:00:00');
INSERT INTO `photos` VALUES (28,24,'alsopahok1.jpg','Szent Kereszt felmagasztalása templom',0,'i',333,250,'0000-00-00 00:00:00','0000-00-00 00:00:00');
INSERT INTO `photos` VALUES (29,25,'gyeneshavas.jpg','',0,'i',320,400,'0000-00-00 00:00:00','0000-00-00 00:00:00');
INSERT INTO `photos` VALUES (30,26,'gyenesilona.jpg','',0,'i',400,300,'0000-00-00 00:00:00','0000-00-00 00:00:00');
INSERT INTO `photos` VALUES (20,17,'111_1104.jpg','A nagybácsai temlom tornya',0,'i',800,600,'0000-00-00 00:00:00','0000-00-00 00:00:00');
INSERT INTO `photos` VALUES (21,17,'111_1117.jpg','A nagybácsai  templom a plébánia felől',0,'i',800,600,'0000-00-00 00:00:00','0000-00-00 00:00:00');
INSERT INTO `photos` VALUES (19,17,'111_1101.jpg','Templombejárat',0,'n',800,600,'0000-00-00 00:00:00','0000-00-00 00:00:00');
INSERT INTO `photos` VALUES (35,22,'heviz02.jpg','',0,'i',290,350,'0000-00-00 00:00:00','0000-00-00 00:00:00');
/*!40000 ALTER TABLE `photos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lnaptar`
--

DROP TABLE IF EXISTS `lnaptar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lnaptar` (
  `datum` date NOT NULL DEFAULT '0000-00-00',
  `ige` int(5) NOT NULL DEFAULT '0',
  `szent` int(5) NOT NULL DEFAULT '0',
  `szin` enum('piros','feher','zold','lila') NOT NULL DEFAULT 'lila',
  PRIMARY KEY (`datum`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lnaptar`
--

LOCK TABLES `lnaptar` WRITE;
/*!40000 ALTER TABLE `lnaptar` DISABLE KEYS */;
INSERT INTO `lnaptar` VALUES ('2009-04-14',294,0,'feher');
INSERT INTO `lnaptar` VALUES ('2009-04-15',293,0,'feher');
INSERT INTO `lnaptar` VALUES ('2009-05-10',549,0,'feher');
INSERT INTO `lnaptar` VALUES ('2009-05-07',0,165,'feher');
INSERT INTO `lnaptar` VALUES ('2009-05-17',550,0,'feher');
INSERT INTO `lnaptar` VALUES ('2009-05-24',551,0,'feher');
/*!40000 ALTER TABLE `lnaptar` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `megye`
--

DROP TABLE IF EXISTS `megye`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `megye` (
  `id` int(2) NOT NULL AUTO_INCREMENT,
  `megyenev` varchar(50) NOT NULL DEFAULT '',
  `orszag` int(2) NOT NULL DEFAULT '12',
  `egyeb` varchar(255) NOT NULL DEFAULT '',
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=22 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `megye`
--

LOCK TABLES `megye` WRITE;
/*!40000 ALTER TABLE `megye` DISABLE KEYS */;
INSERT INTO `megye` VALUES (1,'Bács-Kiskun',12,'');
INSERT INTO `megye` VALUES (2,'Baranya',12,'');
INSERT INTO `megye` VALUES (3,'Békés',12,'');
INSERT INTO `megye` VALUES (4,'Borsod-Abaúj-Zemplén',12,'');
INSERT INTO `megye` VALUES (5,'Budapest',12,'');
INSERT INTO `megye` VALUES (6,'Csongrád',12,'');
INSERT INTO `megye` VALUES (7,'Fejér',12,'');
INSERT INTO `megye` VALUES (8,'Győr-Moson-Sopron',12,'');
INSERT INTO `megye` VALUES (9,'Hajdú-Bihar',12,'');
INSERT INTO `megye` VALUES (10,'Heves',12,'');
INSERT INTO `megye` VALUES (11,'Jász-Nagykun-Szolnok',12,'');
INSERT INTO `megye` VALUES (12,'Komárom-Esztergom',12,'');
INSERT INTO `megye` VALUES (13,'Nógrád',12,'');
INSERT INTO `megye` VALUES (14,'Pest',12,'');
INSERT INTO `megye` VALUES (15,'Somogy',12,'');
INSERT INTO `megye` VALUES (16,'Szabolcs-Szatmár-Bereg',12,'');
INSERT INTO `megye` VALUES (17,'Tolna',12,'');
INSERT INTO `megye` VALUES (18,'Vas',12,'');
INSERT INTO `megye` VALUES (19,'Veszprém',12,'');
INSERT INTO `megye` VALUES (20,'Zala',12,'');
INSERT INTO `megye` VALUES (21,'Külföld',0,'');
/*!40000 ALTER TABLE `megye` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `misek`
--

DROP TABLE IF EXISTS `misek`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `misek` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `moddatum` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `torles` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `torolte` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=233188 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `misek`
--

LOCK TABLES `misek` WRITE;
/*!40000 ALTER TABLE `misek` DISABLE KEYS */;
INSERT INTO `misek` VALUES (233150,288,2,'07:00:00',NULL,'nyáron',NULL,'03-30','10-25','03-30','<','10-25','','','','tombi','2015-02-08 17:58:52','0000-00-00 00:00:00','');
INSERT INTO `misek` VALUES (233151,288,2,'07:00:00',NULL,'télen',NULL,'10-26','03-29','10-26','>','03-29','','','adventben 6,00-kor','tombi','2015-02-08 17:58:52','0000-00-00 00:00:00','');
INSERT INTO `misek` VALUES (233152,288,3,'18:00:00',NULL,'nyáron',NULL,'03-30','10-25','03-30','<','10-25','','','','tombi','2015-02-08 17:58:52','0000-00-00 00:00:00','');
INSERT INTO `misek` VALUES (233153,288,3,'18:00:00',NULL,'télen',NULL,'10-26','03-29','10-26','>','03-29','','','adventben 6,00-kor','tombi','2015-02-08 17:58:52','0000-00-00 00:00:00','');
INSERT INTO `misek` VALUES (233154,288,4,'07:00:00',NULL,'nyáron',NULL,'03-30','10-25','03-30','<','10-25','','','','tombi','2015-02-08 17:58:52','0000-00-00 00:00:00','');
INSERT INTO `misek` VALUES (233155,288,4,'07:00:00',NULL,'télen',NULL,'10-26','03-29','10-26','>','03-29','','','adventben 6,00-kor','tombi','2015-02-08 17:58:52','0000-00-00 00:00:00','');
INSERT INTO `misek` VALUES (233156,288,5,'18:00:00',NULL,'nyáron',NULL,'03-30','10-25','03-30','<','10-25','','','','tombi','2015-02-08 17:58:52','0000-00-00 00:00:00','');
INSERT INTO `misek` VALUES (233157,288,5,'18:00:00',NULL,'télen',NULL,'10-26','03-29','10-26','>','03-29','','','adventben 6,00-kor','tombi','2015-02-08 17:58:52','0000-00-00 00:00:00','');
INSERT INTO `misek` VALUES (233158,288,6,'18:00:00',NULL,'nyáron',NULL,'03-30','10-25','03-30','<','10-25','','','','tombi','2015-02-08 17:58:52','0000-00-00 00:00:00','');
INSERT INTO `misek` VALUES (233159,288,6,'18:00:00',NULL,'télen',NULL,'10-26','03-29','10-26','>','03-29','','','','tombi','2015-02-08 17:58:52','0000-00-00 00:00:00','');
INSERT INTO `misek` VALUES (233160,288,7,'08:00:00',NULL,'nyáron',NULL,'03-30','10-25','03-30','<','10-25','','','','tombi','2015-02-08 17:58:52','0000-00-00 00:00:00','');
INSERT INTO `misek` VALUES (233161,288,7,'10:00:00',NULL,'nyáron',NULL,'03-30','10-25','03-30','<','10-25','de','','német nyelven','tombi','2015-02-08 17:58:52','0000-00-00 00:00:00','');
INSERT INTO `misek` VALUES (233162,288,7,'18:00:00',NULL,'nyáron',NULL,'03-30','10-25','03-30','<','10-25','','','','tombi','2015-02-08 17:58:52','0000-00-00 00:00:00','');
INSERT INTO `misek` VALUES (233163,288,7,'08:00:00',NULL,'télen',NULL,'10-26','03-29','10-26','>','03-29','','','','tombi','2015-02-08 17:58:52','0000-00-00 00:00:00','');
INSERT INTO `misek` VALUES (233164,288,7,'10:00:00',NULL,'télen',NULL,'10-26','03-29','10-26','>','03-29','de','','német nyelven','tombi','2015-02-08 17:58:52','0000-00-00 00:00:00','');
INSERT INTO `misek` VALUES (233165,288,7,'18:00:00',NULL,'télen',NULL,'10-26','03-29','10-26','>','03-29','','','','tombi','2015-02-08 17:58:52','0000-00-00 00:00:00','');
INSERT INTO `misek` VALUES (233166,289,5,'17:00:00',NULL,'egész évben',NULL,'01-01','12-31','01-01','<','12-31','','','','tombi','2015-02-08 18:02:22','0000-00-00 00:00:00','');
INSERT INTO `misek` VALUES (233168,289,7,'09:00:00',NULL,'egész évben',NULL,'01-01','12-31','01-01','<','12-31','','','','tombi','2015-02-08 18:02:22','0000-00-00 00:00:00','');
INSERT INTO `misek` VALUES (233170,4182,2,'08:00:00',NULL,'nyáron',NULL,'03-30','10-25','03-30','<','10-25','','','','tombi','2015-02-08 18:04:29','0000-00-00 00:00:00','');
INSERT INTO `misek` VALUES (233171,4182,2,'08:00:00',NULL,'télen',NULL,'10-26','03-29','10-26','>','03-29','','','','tombi','2015-02-08 18:04:29','0000-00-00 00:00:00','');
INSERT INTO `misek` VALUES (233172,4182,3,'19:00:00',NULL,'nyáron',NULL,'03-30','10-25','03-30','<','10-25','','','igeliturgia','tombi','2015-02-08 18:04:29','0000-00-00 00:00:00','');
INSERT INTO `misek` VALUES (233173,4182,3,'18:00:00',NULL,'télen',NULL,'10-26','03-29','10-26','>','03-29','','','igeliturgia','tombi','2015-02-08 18:04:29','0000-00-00 00:00:00','');
INSERT INTO `misek` VALUES (233174,4182,7,'11:00:00',NULL,'nyáron',NULL,'03-30','10-25','03-30','<','10-25','','','','tombi','2015-02-08 18:04:29','0000-00-00 00:00:00','');
INSERT INTO `misek` VALUES (233175,4182,7,'11:00:00',NULL,'télen',NULL,'10-26','03-29','10-26','>','03-29','','','','tombi','2015-02-08 18:04:29','0000-00-00 00:00:00','');
INSERT INTO `misek` VALUES (233176,5258,7,'12:00:00','0','Iskolai időben',3,'első tanítási nap','utolsó tanítási nap','09-03','>','06-07','','','','borazslo','2015-02-05 22:44:15','0000-00-00 00:00:00','');
INSERT INTO `misek` VALUES (233177,5258,7,'11:00:00','0','Nagyböjtben',2,'Hamvazószerda','Húsvétvasárnap -1','02-18','<','04-04','','','','borazslo','2015-02-05 22:44:15','0000-00-00 00:00:00','');
INSERT INTO `misek` VALUES (233178,5258,7,'09:00:00','0','Adventben',1,'Advent I. vasárnapja','12-25 -1','11-29','<','12-24','','','','borazslo','2015-02-05 22:44:15','0000-00-00 00:00:00','');
INSERT INTO `misek` VALUES (233179,5258,0,'17:00:00','','Nagycsütörtök',103,'Húsvétvasárnap -3','Húsvétvasárnap -3','04-04','=','04-04','','','','borazslo','2015-02-05 23:15:17','0000-00-00 00:00:00','');
INSERT INTO `misek` VALUES (233180,5258,0,'18:00:00','','Nagypéntek',104,'Húsvétvasárnap -2','Húsvétvasárnap -2','04-04','=','04-04','','','','borazslo','2015-02-05 23:15:17','0000-00-00 00:00:00','');
INSERT INTO `misek` VALUES (233181,5258,0,'12:00:00','','Nagyszombat',105,'Húsvétvasárnap -1','Húsvétvasárnap -1','04-04','=','04-04','','','','borazslo','2015-02-05 23:15:17','0000-00-00 00:00:00','');
INSERT INTO `misek` VALUES (233182,5258,0,'11:00:00','','Húsvéthétfő',107,'Húsvétvasárnap +1','Húsvétvasárnap +1','04-06','=','04-06','','','','borazslo','2015-02-05 23:15:17','0000-00-00 00:00:00','');
INSERT INTO `misek` VALUES (233183,5258,0,'11:00:00','','Karácsony',101,'12-25','12-25','12-25','=','12-25','','','','borazslo','2015-02-05 23:15:17','0000-00-00 00:00:00','');
INSERT INTO `misek` VALUES (233184,5258,0,'12:00:00','','Karácsony (előeste)',102,'12-24','12-24','12-24','=','12-24','','','','borazslo','2015-02-05 23:15:17','0000-00-00 00:00:00','');
INSERT INTO `misek` VALUES (233185,5258,0,'09:00:00','','Húsvét',106,'Húsvétvasárnap','Húsvétvasárnap','04-05','=','04-05','','','','borazslo','2015-02-05 23:15:17','0000-00-00 00:00:00','');
INSERT INTO `misek` VALUES (233186,5104,6,'16:00:00','ps','télen',1,'10-30','03-25','10-30','>','03-25','','','','borazslo','2015-02-08 20:28:20','0000-00-00 00:00:00','');
INSERT INTO `misek` VALUES (233187,5104,7,'08:00:00','pt','télen',1,'10-30','03-25','10-30','>','03-25','','','','borazslo','2015-02-08 20:28:20','0000-00-00 00:00:00','');
/*!40000 ALTER TABLE `misek` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nevnaptar`
--

DROP TABLE IF EXISTS `nevnaptar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nevnaptar` (
  `datum` varchar(4) NOT NULL DEFAULT '',
  `nevnap` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`datum`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nevnaptar`
--

LOCK TABLES `nevnaptar` WRITE;
/*!40000 ALTER TABLE `nevnaptar` DISABLE KEYS */;
INSERT INTO `nevnaptar` VALUES ('0508','Mihály');
INSERT INTO `nevnaptar` VALUES ('0509','Gergely, Édua');
/*!40000 ALTER TABLE `nevnaptar` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orszagok`
--

DROP TABLE IF EXISTS `orszagok`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orszagok` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `nev` varchar(50) NOT NULL DEFAULT '',
  `telkod` varchar(5) NOT NULL DEFAULT '',
  `ok` enum('i','n') NOT NULL DEFAULT 'i',
  `kiemelt` enum('i','n') NOT NULL DEFAULT 'n',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=49 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orszagok`
--

LOCK TABLES `orszagok` WRITE;
/*!40000 ALTER TABLE `orszagok` DISABLE KEYS */;
INSERT INTO `orszagok` VALUES (1,'Ausztria','+43','i','i');
INSERT INTO `orszagok` VALUES (2,'Belgium','+32','i','i');
INSERT INTO `orszagok` VALUES (3,'Bulgária','+359','i','n');
INSERT INTO `orszagok` VALUES (4,'Ciprus','+357','i','n');
INSERT INTO `orszagok` VALUES (5,'Cseh Köztársaság','+420','i','i');
INSERT INTO `orszagok` VALUES (6,'Dánia','+45','i','n');
INSERT INTO `orszagok` VALUES (7,'Észtország','+372','i','n');
INSERT INTO `orszagok` VALUES (8,'Finnország','+358','i','n');
INSERT INTO `orszagok` VALUES (9,'Franciaország','+33','i','n');
INSERT INTO `orszagok` VALUES (10,'Németország','+49','i','n');
INSERT INTO `orszagok` VALUES (11,'Görögország','+30','i','n');
INSERT INTO `orszagok` VALUES (12,'Magyarország','+36','i','i');
INSERT INTO `orszagok` VALUES (13,'Olaszország','+39','i','i');
INSERT INTO `orszagok` VALUES (14,'Írország','+353','i','i');
INSERT INTO `orszagok` VALUES (15,'Izland','+354','i','n');
INSERT INTO `orszagok` VALUES (16,'Lettország','+371','i','n');
INSERT INTO `orszagok` VALUES (17,'Liechtenstein','+4175','i','n');
INSERT INTO `orszagok` VALUES (18,'Luxemburg','+352','i','n');
INSERT INTO `orszagok` VALUES (19,'Litvánia','+370','i','n');
INSERT INTO `orszagok` VALUES (20,'Málta','+356','i','n');
INSERT INTO `orszagok` VALUES (21,'Hollandia','+31','i','n');
INSERT INTO `orszagok` VALUES (22,'Norvégia','+47','i','n');
INSERT INTO `orszagok` VALUES (23,'Lengyelország','+48','i','i');
INSERT INTO `orszagok` VALUES (24,'Portugália','+351','i','n');
INSERT INTO `orszagok` VALUES (25,'Románia','+40','i','i');
INSERT INTO `orszagok` VALUES (26,'Szlovákia','+421','i','i');
INSERT INTO `orszagok` VALUES (27,'Szlovénia','+386','i','i');
INSERT INTO `orszagok` VALUES (28,'Spanyolország','+34','i','n');
INSERT INTO `orszagok` VALUES (29,'Svédország','+46','i','n');
INSERT INTO `orszagok` VALUES (30,'Egyesült Királyság','+44','i','i');
INSERT INTO `orszagok` VALUES (31,'Törökország','+90','i','n');
INSERT INTO `orszagok` VALUES (32,'Svájc','+41','i','i');
INSERT INTO `orszagok` VALUES (33,'Bosznia-Hercegovina','','i','i');
INSERT INTO `orszagok` VALUES (34,'Amerikai Egyesült Államok (USA)','','i','n');
INSERT INTO `orszagok` VALUES (35,'Kanada','','i','n');
INSERT INTO `orszagok` VALUES (36,'Brazília','','i','n');
INSERT INTO `orszagok` VALUES (37,'Japán','','i','n');
INSERT INTO `orszagok` VALUES (38,'Argentína','','i','n');
INSERT INTO `orszagok` VALUES (39,'Kína','','i','n');
INSERT INTO `orszagok` VALUES (40,'Mongólia','','i','n');
INSERT INTO `orszagok` VALUES (41,'Egyéb','','i','n');
INSERT INTO `orszagok` VALUES (42,'Ausztrália','','i','n');
INSERT INTO `orszagok` VALUES (43,'Mexikó','','i','n');
INSERT INTO `orszagok` VALUES (44,'Egyiptom','','i','n');
INSERT INTO `orszagok` VALUES (45,'Izrael','','i','n');
INSERT INTO `orszagok` VALUES (46,'Szerbia-Montenegro','','i','i');
INSERT INTO `orszagok` VALUES (47,'Ukrajna','','i','i');
/*!40000 ALTER TABLE `orszagok` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `session`
--

DROP TABLE IF EXISTS `session`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `session` (
  `uid` int(11) NOT NULL DEFAULT '0',
  `login` varchar(30) NOT NULL DEFAULT '',
  `sessid` varchar(250) NOT NULL DEFAULT '',
  `lejarat` int(10) NOT NULL,
  KEY `sessid_2` (`sessid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `szentek`
--

DROP TABLE IF EXISTS `szentek`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `szentek` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nev` varchar(200) NOT NULL DEFAULT '',
  `nevnap` varchar(200) NOT NULL DEFAULT '',
  `intro` text NOT NULL,
  `ho` int(2) NOT NULL DEFAULT '0',
  `nap` int(2) NOT NULL DEFAULT '0',
  `leiras` text NOT NULL,
  `szin` enum('piros','feher','zold','lila') NOT NULL DEFAULT 'feher',
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=261 DEFAULT CHARSET=utf8 PACK_KEYS=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `szentek`
--

LOCK TABLES `szentek` WRITE;
/*!40000 ALTER TABLE `szentek` DISABLE KEYS */;
INSERT INTO `szentek` VALUES (1,'Szent Sebestyén vértanú','','Az Újszövetség mindent megújít. A régi szertartások helyébe állította Krisztus keresztáldozatát, és az ároni papság helyébe Krisztus örök főpapságából részesíti azokat, akiket az oltár szolgálatára lefoglalt. Ezeket helyezte a hívek élére, hogy jó példával előljárjanak és mint az újszövetség sáfárai az engedelmesség útjára tanítsanak. ',1,20,'Diokleciánusz császár kedvelt tisztje (a praetoriánusok I. kohorszának centuriója) volt. Keresztény hite miatt a császár egy réten fához köttette, és saját katonáival nyilaztatta. Egy keresztény matróna, Iréne ápolta. Felgyógyulása után szemrehányást tett a császárnak kegyetlensége miatt, ezért az súlyos dorongokkal agyonverette. Holttestét a római szennyvízcsatornába dobták. Lucina nevű matróna - látomása révén - rátalált, és a Via Appián temettette el. Ezen a helyen áll a róla elnevezett Szent Sebestyén bazilika. Vértanúságának erejét csodáljuk a szentmise kezdőénekével: <I>\" Ez a szent haláláig küzdött Istenének törvényéért, nem félt a gonoszok szavától, mert életének alapja szilárd kőszikla volt.\"</I>\r\n<b>Bűnbánati cselekmény</b>\r\n\r\nTestvéreim! Krisztusból merítettek erőt a szent vértanúk, hogy állhatatosak maradjanak a szenvedésekben. Vizsgáljuk meg azért lelkiismeretünket és bánjuk meg bűneinket, hogy tiszta lélekkel szorosan kapcsolódjunk Urunkhoz, Jézus Krisztushoz.<br>Jézus Krisztus, Aki erőt öntesz a hitvallókba, hogy hűségesek maradjanak: Uram, irgalmazz!<br>Jézus Krisztus, Aki elvetted a félelmet az örök élet reményével: Krisztus, kegyelmezz! <br>Jézus Krisztus, Aki megígérted, hogy a Téged megvallókat magadénak vallod Atyád előtt: Uram, irgalmazz! \r\n\r\n<b>Hívek könyörgése</b>\r\n\r\nImádkozzunk, Testvéreim, a vértanúk Királyához, erősítsen meg bennünket az Iránta való szeretetben, hogy a szent vértanúk erejével tehessünk tanúságot Róla.<br><br><img src=\"http://www.plebania.net/img/kocka.gif\">Növeld, Urunk, szeretetünket, hogy a pogány korszellem ellenére is ragaszkodjunk Hozzád.<br><img src=\"http://www.plebania.net/img/kocka.gif\">Adj igazi bátorságot, hogy vállalni tudjuk hitünkért a megpróbáltatásokat és üldözéseket.<br><img src=\"http://www.plebania.net/img/kocka.gif\">Engedd, hogy tanuljunk Tőled: szelídek és alázatos szívűek legyünk.<br><img src=\"http://www.plebania.net/img/kocka.gif\">Oltsd lelkünkbe az irgalom lelkületét, hogy meg tudjunk bocsátani ellenségeinknek.<br><img src=\"http://www.plebania.net/img/kocka.gif\">Vezess bennünket, Urunk, a földi életből a szentek társaságába.<br><br>Urunk, Jézus Krisztus! Te engedelmességeddel a szeretet legragyogóbb példáját adtad, és megihletted a vértanúk lelkét az önmagát feláldozó szeretetre. Kérünk, Szent Sebestyén vértanúd közbenjárására növeld bennünk is Irántad az önfeláldozó szeretetet. Aki élsz és uralkodol, mindörökkön örökké. ','piros');
INSERT INTO `szentek` VALUES (127,'Szent István király ereklyéinek átvitele','','Földi életünkre az Isten fáradságot és munkát rendelt, hogy hűségünket és állhatatosságunkat kipróbálja. Nem hagyott azonban magunkra, hanem az emberi élet célját és a cél elérésének eszközét világosan megmondta.',5,30,'','feher');
INSERT INTO `szentek` VALUES (128,'Szent Barnabás apostol','','Sok névtelen hithirdető viszi az örömhírt. Ugyanakkor nélkülözhetetlen az Egyház, a közösség képviselője. Barnabás az apostolok küldötte, aki erősíti és továbbviszi az örömhír hirdetését. A jézusi küldetés örömének örülhetünk ezekben a napokban a papszentelésekben. Örüljünk együtt az Úr kegyelmének működéséért.',6,11,'<b><br>ApCsel 11, 21b-26;13, 1-3<br></b>Barnabás derékember volt, telve Szentlélekkel és hittel.\r\n<P class=MsoNormal style=\"MARGIN: 0cm 0cm 0pt\"><FONT face=Geneva,Arial,Sans-Serif><FONT size=2><b>Mt 10, 7-13<br></b>Menjetek, és hirdessétek: közel van az Isten Országa!<br><br><b><?xml:namespace prefix = o ns = \"urn:schemas-microsoft-com:office:office\" /><o:p></o:p></b>\r\n<SPAN style=\"FONT-SIZE: 12pt; FONT-FAMILY: \'Times New Roman\'; mso-bidi-font-size: 10.0pt; mso-fareast-font-family: \'Times New Roman\'; mso-ansi-language: HU; mso-fareast-language: HU; mso-bidi-language: AR-SA\"><FONT face=Geneva,Arial,Sans-Serif size=2>Barnabás szép jellemzést kap az Apostolok Cselekedeteiben: derék ember volt. Vajon rólam elmondható? Én is hallom a jézusi felszólítás: menjetek és hirdessétek az Isten Országát! Vajon merek-e nekivágni az igehirdetésnek a magam lehetőségei szerint.</SPAN><br><br><SPAN style=\"FONT-SIZE: 12pt; FONT-FAMILY: \'Times New Roman\'; mso-bidi-font-size: 10.0pt; mso-fareast-font-family: \'Times New Roman\'; mso-ansi-language: HU; mso-fareast-language: HU; mso-bidi-language: AR-SA\">\r\nA zsolozsma második olvasmánya az apostol ünnepén:\r\nSzent Chromatius püspöknek Szent Máté evangéliumáról szóló fejtegetéseiből\r\n<br><FONT size=2><FONT face=\"Courier New\"><SPAN style=\"mso-spacerun: yes\">&nbsp;</SPAN>(Tract. 5, 1. 3-4: CCL 9, 405-407)\r\n<FONT size=2><FONT face=\"Courier New\"><SPAN style=\"mso-spacerun: yes\">&nbsp;</SPAN>Ti vagytok a világ világossága\r\n<FONT size=2><FONT face=\"Courier New\"><SPAN style=\"mso-spacerun: yes\">&nbsp;</SPAN>Ti vagytok a világ világossága. A hegyen épült várost nem lehet elrejteni. S ha világosságot gyújtanak, nem rejtik véka alá, hanem a tartóra teszik, hogy mindenkinek világítson a házban (Mt 5, 14-15). Az Úr a föld sójának mondta tanítványait, mivel az ördög által elcsábított emberszíveket a mennyei bölcsességgel ők fűszerezték. Most a világ világosságának is nevezi őket, mivel ő maga, az örök és igazi világosság világítja meg tanítványait, s így ők is világossággá lettek a sötétségben.\r\n<FONT size=2><FONT face=\"Courier New\"><SPAN style=\"mso-spacerun: yes\">&nbsp;</SPAN>Mivel Krisztus az igazság Napja, méltán nevezi tanítványait is a világ világosságának: rajtuk keresztül mint ragyogó sugarakon át árasztja az egész világra ismeretének a fényét. Ők az igazság fényét felragyogtatva, kiűzték az emberek szívéből a tévedés sötétségét.\r\n<FONT size=2><FONT face=\"Courier New\"><SPAN style=\"mso-spacerun: yes\">&nbsp;</SPAN>Mi magunk is általuk nyertük a fényt, s lettünk világosság a sötétségből, ahogy az Apostol mondja: Valaha sötétség voltatok, most azonban világosság az Úrban. Éljetek úgy, mint a világosság fiai (Ef 5, 8). S máskor: Mindnyájan a világosság és a nappal fiai vagytok. Nem vagyunk az éjszakáé és a sötétségé (1 Tessz 5, 5).\r\n<SPAN style=\"mso-spacerun: yes\"></SPAN>&nbsp;\r\n<FONT size=2><FONT face=\"Courier New\"><SPAN style=\"mso-spacerun: yes\">&nbsp;</SPAN>Méltán tanúskodik Szent János is levelében: Az Isten világosság (1 Jn 1, 5), és aki Istenben marad, az világosságban van, amint Isten is fényben van. Ezért, mivel annak örvendünk, hogy megszabadultunk a sötétség tévedéseitől, mint a világosság fiainak, állandóan fényben kell járnunk. Emiatt mondja az Apostol: Úgy kell ragyognotok közöttük, mint a csillagoknak a mindenségben. Ragaszkodjatok az életet tápláló tanításhoz (Fil 2, 15-16).\r\n<FONT size=2><FONT face=\"Courier New\"><SPAN style=\"mso-spacerun: yes\">&nbsp;</SPAN>Amit ha nem teszünk meg, olyanok leszünk, mint akik önmaguk és mások kárhozatára is hitetlenségük fátylával eltakarják és elhomályosítják az annyira szükséges fény áldásait. A Szentírásból olvassuk és tudjuk, hogy az az ember, aki a mennyei kamatoztatásra kapott talentumot inkább elrejtette, mint hogy a pénzváltó asztalára adja, megkapta megérdemelt büntetését.\r\n<FONT size=2><FONT face=\"Courier New\"><SPAN style=\"mso-spacerun: yes\">&nbsp;</SPAN>Éppen ezért annak az égő lámpásnak, amely üdvösségünk javára gyulladt fel bennünk, állandóan világítania kell. A lelki kegyelem és a mennyei törvény lámpásának birtokában vagyunk, amelyről Dávid ezt mondta: Lámpás a te igéd lábam előtt, ösvényemen csak ez világít (Zsolt 118, 105). Salamon is azt mondja róla: Lámpás a törvény parancsa (vö. Péld 6, 23).\r\n<FONT size=2><FONT face=\"Courier New\"><SPAN style=\"mso-spacerun: yes\">&nbsp;</SPAN>Nem szabad tehát elrejtenünk a törvény és a hit lámpását, hanem az Egyházban mindenki üdvösségére mintegy tartóra kell helyeznünk, hogy az igazság fényét mi magunk is élvezzük, és a többi hívőt is megvilágítsa.\r\n</SPAN>&nbsp;','piros');
/*!40000 ALTER TABLE `szentek` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `templomok`
--

DROP TABLE IF EXISTS `templomok`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `templomok` (
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
  `miseaktiv` int(11) DEFAULT '1',
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
) ENGINE=MyISAM AUTO_INCREMENT=5259 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `templomok`
--

LOCK TABLES `templomok` WRITE;
/*!40000 ALTER TABLE `templomok` DISABLE KEYS */;
INSERT INTO `templomok` VALUES (138,'Szent Anna templom','Szabadhegyi templom',0,12,8,'Győr','','Megközelíthető a Belvárosból a 19-es, 5-ös és 7-es helyi járattal.','<b>Plébánia:</b>\r\n9028 Győr, József Attila u. 46. \r\nTelefon: (xx) xxx-xxx','http://www.ember.emecclesia.hu','vanittvalaki@dehogy.hu',4,3,'<p><span class=\"alap\">Szabadhegy ősi település. 1200 körül m&aacute;r okiratok eml&iacute;tik Szabadi, illetve Szőllős néven. A h&iacute;vek kis sz&aacute;ma miatt azonban ön&aacute;ll&oacute; pléb&aacute;nia alak&iacute;t&aacute;s&aacute;t csak 1750 körül lehetett komolyan tervbe venni. 1743 &oacute;ta m&aacute;r rendszeresen j&aacute;rtak ki vas&aacute;rnapi istentiszteletre a székesegyh&aacute;zi k&aacute;pl&aacute;nok. 1787-ben megkezdték az egyh&aacute;zi anyakönyvezést a kij&aacute;r&oacute; belv&aacute;rosi k&aacute;pl&aacute;nok.<br />1789-ben egy &aacute;ldozatos szabadhegyi polg&aacute;r, Farkas M&aacute;rton végrendeletében minden vagyon&aacute;t a szabadhegyi pléb&aacute;nia alap&iacute;t&aacute;s&aacute;ra hagyta. Az 1994-ben elbontott pléb&aacute;nia az ő saj&aacute;t h&aacute;za volt. Az akkori templom - val&oacute;sz&iacute;nűleg csak kis k&aacute;poln&aacute;cska - m&aacute;r összeoml&oacute;ban volt. 1800 t&aacute;j&aacute;n épült a m&aacute;sodik szabadhegyi templom a mostani pléb&aacute;nia udvar&aacute;n. Fafedeles templom volt, torony csak később épült hozz&aacute;, amikor a szabadhegyiek saj&aacute;t erejükből 2 harangot szereztek bele.<br />A megnövekedett léleksz&aacute;mnak egyre szűkebb templom m&aacute;r düledezőben volt, amikor Kosty&aacute;n Mih&aacute;ly pléb&aacute;nos a jelenlegi templom ép&iacute;tését megkezdte. Az ép&iacute;tésnél a v&aacute;ros is sokat seg&iacute;tett, de a fő terhet a szabadhegyi h&iacute;vek v&aacute;llalt&aacute;k.<br />A mai templomot 1903. j&uacute;nius 7-én szentelték fel, Szent Anna tiszteletére.<br />A két vil&aacute;gh&aacute;bor&uacute; között Szabadhegy teljesen összeépült Győrrel. A pléb&aacute;nia területe jelenleg a p&aacute;pai vas&uacute;tvonalt&oacute;l délre eső v&aacute;rosrész, Kismegyer kivételével.<br />A II. vil&aacute;gh&aacute;bor&uacute;ban a templom sokat szenvedett, a tornyot is tal&aacute;lat érte. A h&aacute;bor&uacute;s k&aacute;rokat Haller J&aacute;nos pléb&aacute;nos gondoss&aacute;ga &aacute;ll&iacute;totta helyre. A templom k&iacute;vülről és belülről is meg&uacute;jult. A templom belsejét Samodai J&oacute;zsef festőművész 5 fresk&oacute;ja d&iacute;sz&iacute;ti. A templom belső fel&uacute;j&iacute;t&aacute;sa 2002-ben készült el, j&oacute; lelkű adakoz&oacute;k és t&aacute;mogat&oacute;k j&oacute;volt&aacute;b&oacute;l.<br />A templom jubileum&aacute;ra, 2003. j&uacute;nius&aacute;ra a templom szentélye is meg&uacute;jult: az &uacute;j liturgikus térbe &uacute;j szembemiséző olt&aacute;r készült, korszerűs&iacute;tették a templom vil&aacute;g&iacute;t&aacute;s&aacute;t és elkészültek a templom sz&iacute;nes üvegablakai is.</span>&nbsp;</p>','Templom védőszentje: Szent Anna\r\nBúcsúnapja Szent Anna ünnepe (július 26.), július utolsó vasárnapja',1,'Elsőpénteken és a nagyböjt péntekjein a szentmise 18,00-kor van.\r\nAdvent hétköznapjain a szentmise reggel 6,00-kor van.\r\n<b>Imaórák</b>\r\nMinden hónap első vasárnapján 17,00-től 18,00-ig rózsafüzéres imaóra.\r\nMinden hónap második csütörtökén egésznapos szentségimádás:\r\nreggel 8,00-kor szentmise és szentségkitétel,\r\nnapközben egyéni szentségimádás,\r\n17,00-től 18,00-ig közös imaóra, a végén szentségeltétel, szentmise.\r\n\r\nGyóntatás\r\nMinden nap a szentmisék előtti félórában.\r\n\r\nÉvi szentségimádási nap: január 10.\r\nFebruártól decemberi minden hónap 2. csütörtökön egésznapos szentségimádást tartunk.\r\nNagyböjtben minden pénteken este 17.30-kor keresztút.\r\nMájusban és októberben minden este 17.30-kor litánia és rózsafüzér.\r\nAdventben hétfőtől péntekig a szentmise reggel 6.00-kor, hajnali mise.\r\nMinden hónap 1. és 3. hétfőjén, este 19.00-kor hitünk kérdéseiről beszélgetünk a plébánián.','307','307,385,945,5035,382,136,134,139,117,3755,135,','','2014-07-01','2014-08-31','2010-06-17','Piroska Sándor','vala@masik.hu','','modly','n','2006-02-17 14:10:03','verem','2010-06-17 11:25:14','Add: verem (2006-02-17 14:10:03)\nMod: verem (2006-02-17 14:10:16)','i','n');
INSERT INTO `templomok` VALUES (139,'Loyolai Szent Ignác-templom','Bencés templom',0,12,8,'Győr','','','<b>Plébánia:</b><br>9022 Győr\r\nSzéchenyi tér 9. \r\nTelefon: (xx) xx-xxx \r\n','','',15,146,'<p class=\"p-kopf alap\">A bencések kora barokk temploma 1634-1641 között épült Baccio de Bianco tervei <span class=\"alap\">szerint. Főhajóját 1783-ban az érett barokk stílusában alakították át, ezt a stílust képviseli a teljes berendezés és a falképek is. </span></p> <p class=\"alap\">A város neves temploma, a Szent Ignác-templom a bencés (eredetileg jezsuita) rendház és a gimnázium közé ékelődve kéttornyú barokk homlokzatát mutatja a Széchenyi téren álló szemlélőnek. A templomban az oldalkápolnákat és a főhajót szemlélve a barokk változását, gazdagságát kísérhetjük nyomon a XVII. századi egyszerűbbtől a XVIII. századi érett, burjánzó barokkig. A hat oldalkápolna mindegyikét egy-egy szent tiszteletére ajánlották. Szép stukkókeretben szerényebb képek díszítik az oltárok fölötti falakat. </p>  <p>A monumentális főoltárt mesterek sora készítette, közülük kiemelkedik a Szent Ignác megdicsőülése oltárképet festő Paul Troger. Ő készítette a boltozat mennyezetképeit 1744-1747 között, amelyek Szent Ignác mennybevitelét és az angyali üdvözletet ábrázolják. A boltsüveg ívrészein a négy evangélista és a négy próféta képét láthatjuk. Közülük az egyiknek (Szent Lukács) az arca a festő arcát mintázza. A falak szépségével harmonizál a templom egész berendezése: a freskókkal egy időben készült szószék, a jezsuita fráterek készítette padok sora, a gazdag figurális díszekkel ékes szentélyajtó, az orgona és az áttört karzatrács. </p>','',1,'<b>Időszaki miserendi változások</b>\r\nIskolai szünnapokon és szünidőben 5,45 helyett 6,45-kor kezdődnek hétköznap reggel a szentmisék.\r\nÁprilistól októberig a szombat esti szentmisék 19,00-kor kezdődnek.\r\n\r\nHétköznap 5:45 (6:45) Laudes és szentmise\r\nVasárnap 7:30-tól Laudes, 18:30-ól vesperás\r\nA szerzetesközösség reggeli és esti imája nyitott a híveknek.\r\n\r\nNagycsütörtök: 18.00 Az utolsó vacsora ünneplése, 22 óráig szentségimádás\r\nNagypéntek: 15. 00 keresztút a templomban, 18.00 Jézus halálának ünneplése\r\nNagyszombat: 21.30 Húsvét vigíliája\r\nHúsvét vasárnap: 8.00 és 9.30 és 19.00 Szentmisék\r\nHúsvét hétfő: 8.00 és 9.30 kor szentmisék\r\n','117','117,116,135,3755,388,118,386,387,136,5242,382,','','2014-06-16','2014-08-31','2014-07-03','','','','muki','i','2006-02-17 14:12:51','ember','2014-03-25 19:27:34','Add: de sok log van itten','i','n');
/*!40000 ALTER TABLE `templomok` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `terkep_geocode`
--

DROP TABLE IF EXISTS `terkep_geocode`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `terkep_geocode` (
  `tid` int(11) NOT NULL DEFAULT '0',
  `address2` varchar(255) NOT NULL DEFAULT '',
  `lng` float DEFAULT NULL,
  `lat` float DEFAULT NULL,
  `checked` varchar(1) DEFAULT NULL,
  PRIMARY KEY (`tid`),
  KEY `lat` (`lat`),
  KEY `lng` (`lng`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `terkep_geocode`
--

LOCK TABLES `terkep_geocode` WRITE;
/*!40000 ALTER TABLE `terkep_geocode` DISABLE KEYS */;
INSERT INTO `terkep_geocode` VALUES (1887,'3996 Füzér, Árpád út 5, Magyarország',21.4555,48.5395,'1');
INSERT INTO `terkep_geocode` VALUES (1287,'3533 Miskolc, Técsey Ferenc utca 2-4, Magyarország',20.7268,48.0913,'1');
INSERT INTO `terkep_geocode` VALUES (23,'1142 Budapest, Kassai tér 24-25, Magyarország',19.0965,47.5232,'1');
INSERT INTO `terkep_geocode` VALUES (3872,'',17.448,46.1895,'2');
INSERT INTO `terkep_geocode` VALUES (2241,'',16.3822,46.8038,'1');
INSERT INTO `terkep_geocode` VALUES (2146,'',18.2478,46.4231,'1');
/*!40000 ALTER TABLE `terkep_geocode` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `terkep_vars`
--

DROP TABLE IF EXISTS `terkep_vars`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `terkep_vars` (
  `name` varchar(40) NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `terkep_vars`
--

LOCK TABLES `terkep_vars` WRITE;
/*!40000 ALTER TABLE `terkep_vars` DISABLE KEYS */;
INSERT INTO `terkep_vars` VALUES ('id','64384');
INSERT INTO `terkep_vars` VALUES ('over_query_limit','13367081');
INSERT INTO `terkep_vars` VALUES ('templom','48310');
INSERT INTO `terkep_vars` VALUES ('templom_max','4810');
INSERT INTO `terkep_vars` VALUES ('templom_checked','429');
INSERT INTO `terkep_vars` VALUES ('templom_suggested','622');
/*!40000 ALTER TABLE `terkep_vars` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `unnepnaptar`
--

DROP TABLE IF EXISTS `unnepnaptar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `unnepnaptar` (
  `datum` date NOT NULL DEFAULT '0000-00-00',
  `unnep` varchar(50) NOT NULL DEFAULT '',
  `szabadnap` enum('i','n') NOT NULL DEFAULT 'i',
  `mise` enum('v','n','u') NOT NULL DEFAULT 'u',
  `miseinfo` varchar(250) NOT NULL DEFAULT '',
  PRIMARY KEY (`datum`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `unnepnaptar`
--

LOCK TABLES `unnepnaptar` WRITE;
/*!40000 ALTER TABLE `unnepnaptar` DISABLE KEYS */;
INSERT INTO `unnepnaptar` VALUES ('2006-04-14','Nagypéntek','n','n','Nincs szentmise');
INSERT INTO `unnepnaptar` VALUES ('2006-04-15','Nagyszombat','n','n','Este húsvéti vigíliamise (templomonként eltérő)');
INSERT INTO `unnepnaptar` VALUES ('2006-04-16','Húsvét vasárnap','i','u','');
INSERT INTO `unnepnaptar` VALUES ('2006-04-17','Húsvét hétfő','i','u','ünnepi miserend (általában vasárnapi)');
INSERT INTO `unnepnaptar` VALUES ('2006-06-05','Pünkösd','i','u','ünnepi miserend (általában vasárnapi)');
/*!40000 ALTER TABLE `unnepnaptar` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `updates`
--

DROP TABLE IF EXISTS `updates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `updates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `tid` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=113 DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `updates`
--

LOCK TABLES `updates` WRITE;
/*!40000 ALTER TABLE `updates` DISABLE KEYS */;
INSERT INTO `updates` VALUES (12,4737,618,'2013-06-16 23:44:27');
/*!40000 ALTER TABLE `updates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `login` varchar(20) NOT NULL DEFAULT '',
  `jelszo` varchar(255) NOT NULL DEFAULT '',
  `jogok` varchar(200) NOT NULL DEFAULT '',
  `ok` enum('i','n','x','o') NOT NULL DEFAULT 'i',
  `letrehozta` varchar(50) NOT NULL DEFAULT '',
  `regdatum` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `lastlogin` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `lastactive` datetime DEFAULT NULL,
  `email` varchar(100) NOT NULL DEFAULT '',
  `becenev` varchar(50) NOT NULL DEFAULT '',
  `nev` varchar(100) NOT NULL DEFAULT '',
  `adminmegj` text NOT NULL,
  `atvett` enum('i','n') NOT NULL DEFAULT 'i',
  `volunteer` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM AUTO_INCREMENT=5822 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES (10,'vacskamati','$2y$10$RmePrAkA68lNiKS9zY9yUuCdw6L7pw4ITysPe2MsBJ3QEqJKVz5V2','hirek-szavazas-galeria-info-hirek-user-nevnaptar-reklam-miserend-igenaptar','i','*vendeg*','2011-02-23 19:52:06','2015-03-18 22:29:29','2015-03-18 23:52:50','egyik@gmail.com','','Lázár Ervin','','n',1);
INSERT INTO `user` VALUES (11,'manyok','$2y$10$bWzPPXw3uGTbxLhvnVGTOu3S74CxjwpSNba2Vin/klQys6A1ufECG','','i','*vendeg*','2012-12-22 17:24:19','2015-02-03 11:10:39',NULL,'email@cim.com','Manyók','Hétszünyükapanyányimanyók','','n',0);
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `varosok`
--

DROP TABLE IF EXISTS `varosok`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `varosok` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `irsz` int(4) NOT NULL DEFAULT '0',
  `megye_id` int(2) NOT NULL DEFAULT '0',
  `orszag` int(2) NOT NULL DEFAULT '46',
  `nev` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3752 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `varosok`
--

LOCK TABLES `varosok` WRITE;
/*!40000 ALTER TABLE `varosok` DISABLE KEYS */;
INSERT INTO `varosok` VALUES (1,4000,9,12,'Debrecen');
INSERT INTO `varosok` VALUES (2,7600,2,12,'Pécs');
INSERT INTO `varosok` VALUES (3,6700,6,12,'Szeged');
INSERT INTO `varosok` VALUES (4,3500,4,12,'Miskolc');
INSERT INTO `varosok` VALUES (5,9000,8,12,'Győr');
INSERT INTO `varosok` VALUES (6,2000,14,12,'Szentendre');
INSERT INTO `varosok` VALUES (7,2009,14,12,'Pilisszentlászló');
INSERT INTO `varosok` VALUES (8,2011,14,12,'Budakalász');
INSERT INTO `varosok` VALUES (9,2013,14,12,'Pomáz');
INSERT INTO `varosok` VALUES (10,2014,14,12,'Csobánka');
INSERT INTO `varosok` VALUES (11,2015,14,12,'Szigetmonostor');
INSERT INTO `varosok` VALUES (12,2016,14,12,'Leányfalu');
INSERT INTO `varosok` VALUES (13,2017,14,12,'Pócsmegyer');
INSERT INTO `varosok` VALUES (14,2021,14,12,'Tahitótfalu');
INSERT INTO `varosok` VALUES (15,2023,14,12,'Dunabogdány');
INSERT INTO `varosok` VALUES (16,2024,14,12,'Kisoroszi');
INSERT INTO `varosok` VALUES (17,2025,14,12,'Visegrád');
INSERT INTO `varosok` VALUES (18,2027,12,12,'Dömös');
INSERT INTO `varosok` VALUES (19,2028,12,12,'Pilismarót');
INSERT INTO `varosok` VALUES (20,2030,14,12,'Érd');
INSERT INTO `varosok` VALUES (21,2038,14,12,'Sóskút');
INSERT INTO `varosok` VALUES (22,2039,14,12,'Pusztazámor');
INSERT INTO `varosok` VALUES (23,2040,14,12,'Budaörs');
INSERT INTO `varosok` VALUES (24,2045,14,12,'Törökbálint');
INSERT INTO `varosok` VALUES (25,2049,14,12,'Diósd');
INSERT INTO `varosok` VALUES (26,2051,14,12,'Biatorbágy');
INSERT INTO `varosok` VALUES (27,2053,14,12,'Herceghalom');
INSERT INTO `varosok` VALUES (28,2060,7,12,'Bicske');
INSERT INTO `varosok` VALUES (29,2064,7,12,'Csabdi');
INSERT INTO `varosok` VALUES (30,2065,7,12,'Mány');
INSERT INTO `varosok` VALUES (31,2066,7,12,'Szár');
INSERT INTO `varosok` VALUES (32,2067,7,12,'Szárliget');
INSERT INTO `varosok` VALUES (33,2071,14,12,'Páty');
INSERT INTO `varosok` VALUES (34,2072,14,12,'Zsámbék');
INSERT INTO `varosok` VALUES (35,2073,14,12,'Tök');
INSERT INTO `varosok` VALUES (36,2074,14,12,'Perbál');
INSERT INTO `varosok` VALUES (37,2080,14,12,'Pilisjászfalu');
INSERT INTO `varosok` VALUES (38,2081,14,12,'Piliscsaba');
INSERT INTO `varosok` VALUES (39,2083,14,12,'Solymár');
INSERT INTO `varosok` VALUES (40,2084,14,12,'Pilisszentiván');
INSERT INTO `varosok` VALUES (41,2085,14,12,'Pilisvörösvár');
INSERT INTO `varosok` VALUES (42,2086,14,12,'Tinnye');
INSERT INTO `varosok` VALUES (43,2089,14,12,'Telki');
INSERT INTO `varosok` VALUES (44,2090,14,12,'Remeteszőlős');
INSERT INTO `varosok` VALUES (45,2091,7,12,'Etyek');
INSERT INTO `varosok` VALUES (46,2092,14,12,'Budakeszi');
INSERT INTO `varosok` VALUES (47,2093,14,12,'Budajenő');
INSERT INTO `varosok` VALUES (48,2094,14,12,'Nagykovácsi');
INSERT INTO `varosok` VALUES (49,2095,14,12,'Pilisszántó');
INSERT INTO `varosok` VALUES (50,2096,14,12,'Üröm');
INSERT INTO `varosok` VALUES (51,2097,14,12,'Pilisborosjenő');
INSERT INTO `varosok` VALUES (52,2098,14,12,'Pilisszentkereszt');
INSERT INTO `varosok` VALUES (53,2099,14,12,'Dobogókő');
INSERT INTO `varosok` VALUES (54,2100,14,12,'Gödöllő');
INSERT INTO `varosok` VALUES (55,2111,14,12,'Szada');
INSERT INTO `varosok` VALUES (56,2112,14,12,'Veresegyház');
INSERT INTO `varosok` VALUES (57,2113,14,12,'Erdőkertes');
INSERT INTO `varosok` VALUES (58,2114,14,12,'Valkó');
INSERT INTO `varosok` VALUES (59,2115,14,12,'Vácszentlászló');
INSERT INTO `varosok` VALUES (60,2116,14,12,'Zsámbok');
INSERT INTO `varosok` VALUES (61,2117,14,12,'Isaszeg');
INSERT INTO `varosok` VALUES (62,2118,14,12,'Dány');
INSERT INTO `varosok` VALUES (63,2119,14,12,'Pécel');
INSERT INTO `varosok` VALUES (64,2120,14,12,'Dunakeszi');
INSERT INTO `varosok` VALUES (65,2131,14,12,'Göd');
INSERT INTO `varosok` VALUES (66,2133,14,12,'Sződliget');
INSERT INTO `varosok` VALUES (67,2134,14,12,'Sződ');
INSERT INTO `varosok` VALUES (68,2135,14,12,'Csörög');
INSERT INTO `varosok` VALUES (69,2141,14,12,'Csömör');
INSERT INTO `varosok` VALUES (70,2142,14,12,'Nagytarcsa');
INSERT INTO `varosok` VALUES (71,2143,14,12,'Kistarcsa');
INSERT INTO `varosok` VALUES (72,2144,14,12,'Kerepes');
INSERT INTO `varosok` VALUES (73,2146,14,12,'Mogyoród');
INSERT INTO `varosok` VALUES (74,2151,14,12,'Fót');
INSERT INTO `varosok` VALUES (75,2161,14,12,'Csomád');
INSERT INTO `varosok` VALUES (76,2162,14,12,'Őrbottyán');
INSERT INTO `varosok` VALUES (77,2163,14,12,'Vácrátót');
INSERT INTO `varosok` VALUES (78,2164,14,12,'Váchartyán');
INSERT INTO `varosok` VALUES (79,2165,14,12,'Kisnémedi');
INSERT INTO `varosok` VALUES (80,2166,14,12,'Püspökszilágy');
INSERT INTO `varosok` VALUES (81,2167,14,12,'Vácduka');
INSERT INTO `varosok` VALUES (82,2170,14,12,'Aszód');
INSERT INTO `varosok` VALUES (83,2173,14,12,'Kartal');
INSERT INTO `varosok` VALUES (84,2174,14,12,'Verseg');
INSERT INTO `varosok` VALUES (85,2175,13,12,'Kálló');
INSERT INTO `varosok` VALUES (86,2176,13,12,'Erdőkürt');
INSERT INTO `varosok` VALUES (87,2177,13,12,'Erdőtarcsa');
INSERT INTO `varosok` VALUES (88,2181,14,12,'Iklad');
INSERT INTO `varosok` VALUES (89,2182,14,12,'Domony');
INSERT INTO `varosok` VALUES (90,2183,14,12,'Galgamácsa');
INSERT INTO `varosok` VALUES (91,2184,14,12,'Vácegres');
INSERT INTO `varosok` VALUES (92,2185,14,12,'Váckisújfalu');
INSERT INTO `varosok` VALUES (93,2191,14,12,'Bag');
INSERT INTO `varosok` VALUES (94,2192,14,12,'Hévízgyörk');
INSERT INTO `varosok` VALUES (95,2193,14,12,'Galgahévíz');
INSERT INTO `varosok` VALUES (96,2194,14,12,'Tura');
INSERT INTO `varosok` VALUES (97,2200,14,12,'Monor');
INSERT INTO `varosok` VALUES (98,2209,14,12,'Péteri');
INSERT INTO `varosok` VALUES (99,2211,14,12,'Vasad');
INSERT INTO `varosok` VALUES (100,2212,14,12,'Csévharaszt');
INSERT INTO `varosok` VALUES (101,2214,14,12,'Pánd');
INSERT INTO `varosok` VALUES (102,2215,14,12,'Káva');
INSERT INTO `varosok` VALUES (103,2216,14,12,'Bénye');
INSERT INTO `varosok` VALUES (104,2217,14,12,'Gomba');
INSERT INTO `varosok` VALUES (105,2220,14,12,'Vecsés');
INSERT INTO `varosok` VALUES (106,2225,14,12,'Üllő');
INSERT INTO `varosok` VALUES (107,2230,14,12,'Gyömrő');
INSERT INTO `varosok` VALUES (108,2233,14,12,'Ecser');
INSERT INTO `varosok` VALUES (109,2234,14,12,'Maglód');
INSERT INTO `varosok` VALUES (110,2235,14,12,'Mende');
INSERT INTO `varosok` VALUES (111,2241,14,12,'Sülysáp');
INSERT INTO `varosok` VALUES (112,2243,14,12,'Kóka');
INSERT INTO `varosok` VALUES (113,2244,14,12,'Úri');
INSERT INTO `varosok` VALUES (114,2251,14,12,'Tápiószecső');
INSERT INTO `varosok` VALUES (115,2252,14,12,'Tóalmás');
INSERT INTO `varosok` VALUES (116,2253,14,12,'Tápióság');
INSERT INTO `varosok` VALUES (117,2254,14,12,'Szentmártonkáta');
INSERT INTO `varosok` VALUES (118,2255,14,12,'Szentlőrinckáta');
INSERT INTO `varosok` VALUES (119,2300,14,12,'Ráckeve');
INSERT INTO `varosok` VALUES (120,2309,14,12,'Lórév');
INSERT INTO `varosok` VALUES (121,2310,14,12,'Szigetszentmiklós');
INSERT INTO `varosok` VALUES (122,2314,14,12,'Halásztelek');
INSERT INTO `varosok` VALUES (123,2315,14,12,'Szigethalom');
INSERT INTO `varosok` VALUES (124,2316,14,12,'Tököl');
INSERT INTO `varosok` VALUES (125,2317,14,12,'Szigetcsép');
INSERT INTO `varosok` VALUES (126,2318,14,12,'Szigetszentmárton');
INSERT INTO `varosok` VALUES (127,2319,14,12,'Szigetújfalu');
INSERT INTO `varosok` VALUES (128,2321,14,12,'Szigetbecse');
INSERT INTO `varosok` VALUES (129,2322,14,12,'Makád');
INSERT INTO `varosok` VALUES (130,2330,14,12,'Dunaharaszti');
INSERT INTO `varosok` VALUES (131,2335,14,12,'Taksony');
INSERT INTO `varosok` VALUES (132,2336,14,12,'Dunavarsány');
INSERT INTO `varosok` VALUES (133,2337,14,12,'Délegyháza');
INSERT INTO `varosok` VALUES (134,2338,14,12,'Áporka');
INSERT INTO `varosok` VALUES (135,2339,14,12,'Majosháza');
INSERT INTO `varosok` VALUES (136,2340,14,12,'Kiskunlacháza');
INSERT INTO `varosok` VALUES (137,2344,14,12,'Dömsöd');
INSERT INTO `varosok` VALUES (138,2345,14,12,'Apaj');
INSERT INTO `varosok` VALUES (139,2347,14,12,'Bugyi');
INSERT INTO `varosok` VALUES (140,2351,14,12,'Alsónémedi');
INSERT INTO `varosok` VALUES (141,2360,14,12,'Gyál');
INSERT INTO `varosok` VALUES (142,2363,14,12,'Felsőpakony');
INSERT INTO `varosok` VALUES (143,2364,14,12,'Ócsa');
INSERT INTO `varosok` VALUES (144,2365,14,12,'Inárcs');
INSERT INTO `varosok` VALUES (145,2366,14,12,'Kakucs');
INSERT INTO `varosok` VALUES (146,2367,14,12,'Újhartyán');
INSERT INTO `varosok` VALUES (147,2370,14,12,'Dabas');
INSERT INTO `varosok` VALUES (148,2375,14,12,'Tatárszentgyörgy');
INSERT INTO `varosok` VALUES (149,2376,14,12,'Hernád');
INSERT INTO `varosok` VALUES (150,2377,14,12,'Örkény');
INSERT INTO `varosok` VALUES (151,2378,14,12,'Pusztavacs');
INSERT INTO `varosok` VALUES (152,2381,14,12,'Táborfalva');
INSERT INTO `varosok` VALUES (153,2400,7,12,'Dunaújváros');
INSERT INTO `varosok` VALUES (154,2421,7,12,'Nagyvenyim');
INSERT INTO `varosok` VALUES (155,2422,7,12,'Mezőfalva');
INSERT INTO `varosok` VALUES (156,2423,7,12,'Daruszentmiklós');
INSERT INTO `varosok` VALUES (157,2424,7,12,'Előszállás');
INSERT INTO `varosok` VALUES (158,2425,7,12,'Nagykarácsony');
INSERT INTO `varosok` VALUES (159,2426,7,12,'Baracs');
INSERT INTO `varosok` VALUES (160,2428,7,12,'Kisapostag');
INSERT INTO `varosok` VALUES (161,2431,7,12,'Perkáta');
INSERT INTO `varosok` VALUES (162,2432,7,12,'Szabadegyháza');
INSERT INTO `varosok` VALUES (163,2433,7,12,'Sárosd');
INSERT INTO `varosok` VALUES (164,2434,7,12,'Hantos');
INSERT INTO `varosok` VALUES (165,2435,7,12,'Nagylók');
INSERT INTO `varosok` VALUES (166,2440,14,12,'Százhalombatta');
INSERT INTO `varosok` VALUES (167,2451,7,12,'Ercsi');
INSERT INTO `varosok` VALUES (168,2454,7,12,'Iváncsa');
INSERT INTO `varosok` VALUES (169,2455,7,12,'Beloiannisz');
INSERT INTO `varosok` VALUES (170,2456,7,12,'Besnyő');
INSERT INTO `varosok` VALUES (171,2457,7,12,'Adony');
INSERT INTO `varosok` VALUES (172,2458,7,12,'Kulcs');
INSERT INTO `varosok` VALUES (173,2459,7,12,'Rácalmás');
INSERT INTO `varosok` VALUES (174,2461,14,12,'Tárnok');
INSERT INTO `varosok` VALUES (175,2462,7,12,'Martonvásár');
INSERT INTO `varosok` VALUES (176,2463,7,12,'Tordas');
INSERT INTO `varosok` VALUES (177,2464,7,12,'Gyúró');
INSERT INTO `varosok` VALUES (178,2465,7,12,'Ráckeresztúr');
INSERT INTO `varosok` VALUES (179,2471,7,12,'Baracska');
INSERT INTO `varosok` VALUES (180,2472,7,12,'Kajászó');
INSERT INTO `varosok` VALUES (181,2473,7,12,'Vál');
INSERT INTO `varosok` VALUES (182,2475,7,12,'Kápolnásnyék');
INSERT INTO `varosok` VALUES (183,2476,7,12,'Pázmánd');
INSERT INTO `varosok` VALUES (184,2477,7,12,'Vereb');
INSERT INTO `varosok` VALUES (185,2481,7,12,'Velence');
INSERT INTO `varosok` VALUES (186,2483,7,12,'Gárdony');
INSERT INTO `varosok` VALUES (187,2490,7,12,'Pusztaszabolcs');
INSERT INTO `varosok` VALUES (188,2500,12,12,'Esztergom');
INSERT INTO `varosok` VALUES (189,2510,12,12,'Dorog');
INSERT INTO `varosok` VALUES (190,2517,12,12,'Kesztölc');
INSERT INTO `varosok` VALUES (191,2518,12,12,'Leányvár');
INSERT INTO `varosok` VALUES (192,2519,12,12,'Piliscsév');
INSERT INTO `varosok` VALUES (193,2521,12,12,'Csolnok');
INSERT INTO `varosok` VALUES (194,2522,12,12,'Dág');
INSERT INTO `varosok` VALUES (195,2523,12,12,'Sárisáp');
INSERT INTO `varosok` VALUES (196,2524,12,12,'Nagysáp');
INSERT INTO `varosok` VALUES (197,2525,12,12,'Bajna');
INSERT INTO `varosok` VALUES (198,2526,12,12,'Epöl');
INSERT INTO `varosok` VALUES (199,2527,12,12,'Máriahalom');
INSERT INTO `varosok` VALUES (200,2528,12,12,'Úny');
INSERT INTO `varosok` VALUES (201,2529,12,12,'Annavölgy');
INSERT INTO `varosok` VALUES (202,2531,12,12,'Tokod');
INSERT INTO `varosok` VALUES (203,2532,12,12,'Tokodaltáró');
INSERT INTO `varosok` VALUES (204,2533,12,12,'Bajót');
INSERT INTO `varosok` VALUES (205,2534,12,12,'Tát');
INSERT INTO `varosok` VALUES (206,2535,12,12,'Mogyorósbánya');
INSERT INTO `varosok` VALUES (207,2536,12,12,'Nyergesújfalu');
INSERT INTO `varosok` VALUES (208,2541,12,12,'Lábatlan');
INSERT INTO `varosok` VALUES (209,2543,12,12,'Süttő');
INSERT INTO `varosok` VALUES (210,2544,12,12,'Neszmély');
INSERT INTO `varosok` VALUES (211,2545,12,12,'Dunaalmás');
INSERT INTO `varosok` VALUES (212,2600,14,12,'Vác');
INSERT INTO `varosok` VALUES (213,2610,13,12,'Nőtincs');
INSERT INTO `varosok` VALUES (214,2611,13,12,'Felsőpetény');
INSERT INTO `varosok` VALUES (215,2612,14,12,'Kosd');
INSERT INTO `varosok` VALUES (216,2613,14,12,'Rád');
INSERT INTO `varosok` VALUES (217,2614,14,12,'Penc');
INSERT INTO `varosok` VALUES (218,2615,14,12,'Csővár');
INSERT INTO `varosok` VALUES (219,2616,13,12,'Keszeg');
INSERT INTO `varosok` VALUES (220,2617,13,12,'Alsópetény');
INSERT INTO `varosok` VALUES (221,2618,13,12,'Nézsa');
INSERT INTO `varosok` VALUES (222,2619,13,12,'Legénd');
INSERT INTO `varosok` VALUES (223,2621,14,12,'Verőce');
INSERT INTO `varosok` VALUES (224,2623,14,12,'Kismaros');
INSERT INTO `varosok` VALUES (225,2624,14,12,'Szokolya');
INSERT INTO `varosok` VALUES (226,2625,14,12,'Kóspallag');
INSERT INTO `varosok` VALUES (227,2626,14,12,'Nagymaros');
INSERT INTO `varosok` VALUES (228,2627,14,12,'Zebegény');
INSERT INTO `varosok` VALUES (229,2628,14,12,'Szob');
INSERT INTO `varosok` VALUES (230,2629,14,12,'Márianosztra');
INSERT INTO `varosok` VALUES (231,2631,14,12,'Ipolydamásd');
INSERT INTO `varosok` VALUES (232,2632,14,12,'Letkés');
INSERT INTO `varosok` VALUES (233,2633,14,12,'Ipolytölgyes');
INSERT INTO `varosok` VALUES (234,2634,14,12,'Nagybörzsöny');
INSERT INTO `varosok` VALUES (235,2635,14,12,'Vámosmikola');
INSERT INTO `varosok` VALUES (236,2636,14,12,'Tésa');
INSERT INTO `varosok` VALUES (237,2637,14,12,'Perőcsény');
INSERT INTO `varosok` VALUES (238,2638,14,12,'Kemence');
INSERT INTO `varosok` VALUES (239,2639,14,12,'Bernecebaráti');
INSERT INTO `varosok` VALUES (240,2640,13,12,'Szendehely');
INSERT INTO `varosok` VALUES (241,2641,13,12,'Berkenye');
INSERT INTO `varosok` VALUES (242,2642,13,12,'Nógrád');
INSERT INTO `varosok` VALUES (243,2643,13,12,'Diósjenő');
INSERT INTO `varosok` VALUES (244,2644,13,12,'Borsosberény');
INSERT INTO `varosok` VALUES (245,2645,13,12,'Nagyoroszi');
INSERT INTO `varosok` VALUES (246,2646,13,12,'Drégelypalánk');
INSERT INTO `varosok` VALUES (247,2647,13,12,'Hont');
INSERT INTO `varosok` VALUES (248,2648,13,12,'Patak');
INSERT INTO `varosok` VALUES (249,2649,13,12,'Dejtár');
INSERT INTO `varosok` VALUES (250,2651,13,12,'Rétság');
INSERT INTO `varosok` VALUES (251,2652,13,12,'Tereske');
INSERT INTO `varosok` VALUES (252,2653,13,12,'Bánk');
INSERT INTO `varosok` VALUES (253,2654,13,12,'Romhány');
INSERT INTO `varosok` VALUES (254,2655,13,12,'Kétbodony');
INSERT INTO `varosok` VALUES (255,2656,13,12,'Szátok');
INSERT INTO `varosok` VALUES (256,2657,13,12,'Tolmács');
INSERT INTO `varosok` VALUES (257,2658,13,12,'Horpács');
INSERT INTO `varosok` VALUES (258,2659,13,12,'Érsekvadkert');
INSERT INTO `varosok` VALUES (259,2660,13,12,'Balassagyarmat');
INSERT INTO `varosok` VALUES (260,2668,13,12,'Patvarc');
INSERT INTO `varosok` VALUES (261,2669,13,12,'Ipolyvece');
INSERT INTO `varosok` VALUES (262,2671,13,12,'Őrhalom');
INSERT INTO `varosok` VALUES (263,2672,13,12,'Hugyag');
INSERT INTO `varosok` VALUES (264,2673,13,12,'Csitár');
INSERT INTO `varosok` VALUES (265,2675,13,12,'Nógrádmarcal');
INSERT INTO `varosok` VALUES (266,2676,13,12,'Cserhátsurány');
INSERT INTO `varosok` VALUES (267,2677,13,12,'Herencsény');
INSERT INTO `varosok` VALUES (268,2678,13,12,'Csesztve');
INSERT INTO `varosok` VALUES (269,2681,14,12,'Galgagyörk');
INSERT INTO `varosok` VALUES (270,2682,14,12,'Püspökhatvan');
INSERT INTO `varosok` VALUES (271,2683,14,12,'Acsa');
INSERT INTO `varosok` VALUES (272,2685,13,12,'Nógrádsáp');
INSERT INTO `varosok` VALUES (273,2686,13,12,'Galgaguta');
INSERT INTO `varosok` VALUES (274,2687,13,12,'Bercel');
INSERT INTO `varosok` VALUES (275,2688,13,12,'Vanyarc');
INSERT INTO `varosok` VALUES (276,2691,13,12,'Nógrádkövesd');
INSERT INTO `varosok` VALUES (277,2692,13,12,'Szécsénke');
INSERT INTO `varosok` VALUES (278,2693,13,12,'Becske');
INSERT INTO `varosok` VALUES (279,2694,13,12,'Magyarnándor');
INSERT INTO `varosok` VALUES (280,2696,13,12,'Terény');
INSERT INTO `varosok` VALUES (281,2697,13,12,'Szanda');
INSERT INTO `varosok` VALUES (282,2698,13,12,'Mohora');
INSERT INTO `varosok` VALUES (283,2699,13,12,'Szügy');
INSERT INTO `varosok` VALUES (284,2700,14,12,'Cegléd');
INSERT INTO `varosok` VALUES (285,2711,14,12,'Tápiószentmárton');
INSERT INTO `varosok` VALUES (286,2712,14,12,'Nyársapát');
INSERT INTO `varosok` VALUES (287,2713,14,12,'Csemő');
INSERT INTO `varosok` VALUES (288,2721,14,12,'Pilis');
INSERT INTO `varosok` VALUES (289,2723,14,12,'Nyáregyháza');
INSERT INTO `varosok` VALUES (290,2724,14,12,'Újlengyel');
INSERT INTO `varosok` VALUES (291,2730,14,12,'Albertirsa');
INSERT INTO `varosok` VALUES (292,2735,14,12,'Dánszentmiklós');
INSERT INTO `varosok` VALUES (293,2736,14,12,'Mikebuda');
INSERT INTO `varosok` VALUES (294,2737,14,12,'Ceglédbercel');
INSERT INTO `varosok` VALUES (295,2740,14,12,'Abony');
INSERT INTO `varosok` VALUES (296,2745,14,12,'Kőröstetétlen');
INSERT INTO `varosok` VALUES (297,2746,14,12,'Jászkarajenő');
INSERT INTO `varosok` VALUES (298,2747,14,12,'Törtel');
INSERT INTO `varosok` VALUES (299,2750,14,12,'Nagykőrös');
INSERT INTO `varosok` VALUES (300,2755,14,12,'Kocsér');
INSERT INTO `varosok` VALUES (301,2760,14,12,'Nagykáta');
INSERT INTO `varosok` VALUES (302,2764,14,12,'Tápióbicske');
INSERT INTO `varosok` VALUES (303,2765,14,12,'Farmos');
INSERT INTO `varosok` VALUES (304,2766,14,12,'Tápiószele');
INSERT INTO `varosok` VALUES (305,2767,14,12,'Tápiógyörgye');
INSERT INTO `varosok` VALUES (306,2768,14,12,'Újszilvás');
INSERT INTO `varosok` VALUES (307,2769,14,12,'Tápiószőlős');
INSERT INTO `varosok` VALUES (308,2800,12,12,'Tatabánya');
INSERT INTO `varosok` VALUES (309,2821,12,12,'Gyermely');
INSERT INTO `varosok` VALUES (310,2822,12,12,'Szomor');
INSERT INTO `varosok` VALUES (311,2823,12,12,'Vértessomló');
INSERT INTO `varosok` VALUES (312,2824,12,12,'Várgesztes');
INSERT INTO `varosok` VALUES (313,2831,12,12,'Tarján');
INSERT INTO `varosok` VALUES (314,2832,12,12,'Héreg');
INSERT INTO `varosok` VALUES (315,2833,12,12,'Vértestolna');
INSERT INTO `varosok` VALUES (316,2834,12,12,'Tardos');
INSERT INTO `varosok` VALUES (317,2836,12,12,'Baj');
INSERT INTO `varosok` VALUES (318,2837,12,12,'Vértesszőlős');
INSERT INTO `varosok` VALUES (319,2840,12,12,'Oroszlány');
INSERT INTO `varosok` VALUES (320,2851,12,12,'Környe');
INSERT INTO `varosok` VALUES (321,2852,12,12,'Kecskéd');
INSERT INTO `varosok` VALUES (322,2853,12,12,'Kömlőd');
INSERT INTO `varosok` VALUES (323,2854,12,12,'Dad');
INSERT INTO `varosok` VALUES (324,2855,12,12,'Bokod');
INSERT INTO `varosok` VALUES (325,2856,12,12,'Szákszend');
INSERT INTO `varosok` VALUES (326,2858,12,12,'Császár');
INSERT INTO `varosok` VALUES (327,2859,12,12,'Vérteskethely');
INSERT INTO `varosok` VALUES (328,2861,12,12,'Bakonysárkány');
INSERT INTO `varosok` VALUES (329,2862,12,12,'Aka');
INSERT INTO `varosok` VALUES (330,2870,12,12,'Kisbér');
INSERT INTO `varosok` VALUES (331,2881,12,12,'Ászár');
INSERT INTO `varosok` VALUES (332,2882,12,12,'Kerékteleki');
INSERT INTO `varosok` VALUES (333,2883,12,12,'Bársonyos');
INSERT INTO `varosok` VALUES (334,2884,12,12,'Bakonyszombathely');
INSERT INTO `varosok` VALUES (335,2885,12,12,'Bakonybánk');
INSERT INTO `varosok` VALUES (336,2886,12,12,'Réde');
INSERT INTO `varosok` VALUES (337,2887,12,12,'Ácsteszér');
INSERT INTO `varosok` VALUES (338,2888,12,12,'Csatka');
INSERT INTO `varosok` VALUES (339,2889,12,12,'Súr');
INSERT INTO `varosok` VALUES (340,2890,12,12,'Tata');
INSERT INTO `varosok` VALUES (341,2896,12,12,'Szomód');
INSERT INTO `varosok` VALUES (342,2897,12,12,'Dunaszentmiklós');
INSERT INTO `varosok` VALUES (343,2898,12,12,'Kocs');
INSERT INTO `varosok` VALUES (344,2899,12,12,'Naszály');
INSERT INTO `varosok` VALUES (345,2900,12,12,'Komárom');
INSERT INTO `varosok` VALUES (346,2911,12,12,'Mocsa');
INSERT INTO `varosok` VALUES (347,2931,12,12,'Almásfüzitő');
INSERT INTO `varosok` VALUES (348,2941,12,12,'Ács');
INSERT INTO `varosok` VALUES (349,2942,12,12,'Nagyigmánd');
INSERT INTO `varosok` VALUES (350,2943,12,12,'Bábolna');
INSERT INTO `varosok` VALUES (351,2944,12,12,'Bana');
INSERT INTO `varosok` VALUES (352,2945,12,12,'Tárkány');
INSERT INTO `varosok` VALUES (353,2946,12,12,'Csép');
INSERT INTO `varosok` VALUES (354,2947,12,12,'Ete');
INSERT INTO `varosok` VALUES (355,2948,12,12,'Kisigmánd');
INSERT INTO `varosok` VALUES (356,2949,12,12,'Csém');
INSERT INTO `varosok` VALUES (357,3000,10,12,'Hatvan');
INSERT INTO `varosok` VALUES (358,3011,10,12,'Heréd');
INSERT INTO `varosok` VALUES (359,3012,10,12,'Nagykökényes');
INSERT INTO `varosok` VALUES (360,3013,10,12,'Ecséd');
INSERT INTO `varosok` VALUES (361,3014,10,12,'Hort');
INSERT INTO `varosok` VALUES (362,3015,10,12,'Csány');
INSERT INTO `varosok` VALUES (363,3016,10,12,'Boldog');
INSERT INTO `varosok` VALUES (364,3021,10,12,'Lőrinci');
INSERT INTO `varosok` VALUES (365,3023,10,12,'Petőfibánya');
INSERT INTO `varosok` VALUES (366,3031,10,12,'Zagyvaszántó');
INSERT INTO `varosok` VALUES (367,3032,10,12,'Apc');
INSERT INTO `varosok` VALUES (368,3033,10,12,'Rózsaszentmárton');
INSERT INTO `varosok` VALUES (369,3034,10,12,'Szűcsi');
INSERT INTO `varosok` VALUES (370,3035,10,12,'Gyöngyöspata');
INSERT INTO `varosok` VALUES (371,3036,10,12,'Gyöngyöstarján');
INSERT INTO `varosok` VALUES (372,3041,13,12,'Héhalom');
INSERT INTO `varosok` VALUES (373,3042,13,12,'Palotás');
INSERT INTO `varosok` VALUES (374,3043,13,12,'Egyházasdengeleg');
INSERT INTO `varosok` VALUES (375,3044,13,12,'Szirák');
INSERT INTO `varosok` VALUES (376,3045,13,12,'Bér');
INSERT INTO `varosok` VALUES (377,3046,13,12,'Kisbágyon');
INSERT INTO `varosok` VALUES (378,3047,13,12,'Buják');
INSERT INTO `varosok` VALUES (379,3051,13,12,'Szarvasgede');
INSERT INTO `varosok` VALUES (380,3052,13,12,'Csécse');
INSERT INTO `varosok` VALUES (381,3053,13,12,'Ecseg');
INSERT INTO `varosok` VALUES (382,3060,13,12,'Pásztó');
INSERT INTO `varosok` VALUES (383,3063,13,12,'Jobbágyi');
INSERT INTO `varosok` VALUES (384,3064,13,12,'Szurdokpüspöki');
INSERT INTO `varosok` VALUES (385,3066,13,12,'Cserhátszentiván');
INSERT INTO `varosok` VALUES (386,3067,13,12,'Felsőtold');
INSERT INTO `varosok` VALUES (387,3068,13,12,'Mátraszőlős');
INSERT INTO `varosok` VALUES (388,3069,13,12,'Alsótold');
INSERT INTO `varosok` VALUES (389,3070,13,12,'Bátonyterenye');
INSERT INTO `varosok` VALUES (390,3073,13,12,'Tar');
INSERT INTO `varosok` VALUES (391,3074,13,12,'Sámsonháza');
INSERT INTO `varosok` VALUES (392,3075,13,12,'Nagybárkány');
INSERT INTO `varosok` VALUES (393,3077,13,12,'Mátraverebély');
INSERT INTO `varosok` VALUES (394,3100,13,12,'Salgótarján');
INSERT INTO `varosok` VALUES (395,3123,13,12,'Cered');
INSERT INTO `varosok` VALUES (396,3124,13,12,'Zabar');
INSERT INTO `varosok` VALUES (397,3125,13,12,'Szilaspogony');
INSERT INTO `varosok` VALUES (398,3126,13,12,'Bárna');
INSERT INTO `varosok` VALUES (399,3127,13,12,'Kazár');
INSERT INTO `varosok` VALUES (400,3128,13,12,'Vizslás');
INSERT INTO `varosok` VALUES (401,3129,13,12,'Lucfalva');
INSERT INTO `varosok` VALUES (402,3131,13,12,'Sóshartyán');
INSERT INTO `varosok` VALUES (403,3132,13,12,'Nógrádmegyer');
INSERT INTO `varosok` VALUES (404,3133,13,12,'Magyargéc');
INSERT INTO `varosok` VALUES (405,3134,13,12,'Piliny');
INSERT INTO `varosok` VALUES (406,3135,13,12,'Szécsényfelfalu');
INSERT INTO `varosok` VALUES (407,3136,13,12,'Etes');
INSERT INTO `varosok` VALUES (408,3137,13,12,'Karancsberény');
INSERT INTO `varosok` VALUES (409,3138,13,12,'Ipolytarnóc');
INSERT INTO `varosok` VALUES (410,3142,13,12,'Mátraszele');
INSERT INTO `varosok` VALUES (411,3143,13,12,'Mátranovák');
INSERT INTO `varosok` VALUES (412,3145,13,12,'Mátraterenye');
INSERT INTO `varosok` VALUES (413,3151,13,12,'Rákóczibánya');
INSERT INTO `varosok` VALUES (414,3152,13,12,'Nemti');
INSERT INTO `varosok` VALUES (415,3153,13,12,'Dorogháza');
INSERT INTO `varosok` VALUES (416,3154,13,12,'Szuha');
INSERT INTO `varosok` VALUES (417,3155,13,12,'Mátramindszent');
INSERT INTO `varosok` VALUES (418,3161,13,12,'Kishartyán');
INSERT INTO `varosok` VALUES (419,3162,13,12,'Ságújfalu');
INSERT INTO `varosok` VALUES (420,3163,13,12,'Karancsság');
INSERT INTO `varosok` VALUES (421,3165,13,12,'Endrefalva');
INSERT INTO `varosok` VALUES (422,3170,13,12,'Szécsény');
INSERT INTO `varosok` VALUES (423,3175,13,12,'Nagylóc');
INSERT INTO `varosok` VALUES (424,3176,13,12,'Hollókő');
INSERT INTO `varosok` VALUES (425,3177,13,12,'Rimóc');
INSERT INTO `varosok` VALUES (426,3178,13,12,'Varsány');
INSERT INTO `varosok` VALUES (427,3179,13,12,'Nógrádsipek');
INSERT INTO `varosok` VALUES (428,3181,13,12,'Karancsalja');
INSERT INTO `varosok` VALUES (429,3182,13,12,'Karancslapujtő');
INSERT INTO `varosok` VALUES (430,3183,13,12,'Karancskeszi');
INSERT INTO `varosok` VALUES (431,3184,13,12,'Mihálygerge');
INSERT INTO `varosok` VALUES (432,3185,13,12,'Egyházasgerge');
INSERT INTO `varosok` VALUES (433,3186,13,12,'Litke');
INSERT INTO `varosok` VALUES (434,3187,13,12,'Nógrádszakál');
INSERT INTO `varosok` VALUES (435,3188,13,12,'Ludányhalászi');
INSERT INTO `varosok` VALUES (436,3200,10,12,'Gyöngyös');
INSERT INTO `varosok` VALUES (437,3211,10,12,'Gyöngyösoroszi');
INSERT INTO `varosok` VALUES (438,3212,10,12,'Gyöngyöshalász');
INSERT INTO `varosok` VALUES (439,3213,10,12,'Atkár');
INSERT INTO `varosok` VALUES (440,3214,10,12,'Nagyréde');
INSERT INTO `varosok` VALUES (441,3231,10,12,'Gyöngyössolymos');
INSERT INTO `varosok` VALUES (442,3235,10,12,'Mátraszentimre');
INSERT INTO `varosok` VALUES (443,3240,10,12,'Parád');
INSERT INTO `varosok` VALUES (444,3242,10,12,'Parádsasvár');
INSERT INTO `varosok` VALUES (445,3243,10,12,'Bodony');
INSERT INTO `varosok` VALUES (446,3245,10,12,'Recsk');
INSERT INTO `varosok` VALUES (447,3246,10,12,'Mátraderecske');
INSERT INTO `varosok` VALUES (448,3247,10,12,'Mátraballa');
INSERT INTO `varosok` VALUES (449,3248,10,12,'Ivád');
INSERT INTO `varosok` VALUES (450,3250,10,12,'Pétervására');
INSERT INTO `varosok` VALUES (451,3252,10,12,'Erdőkövesd');
INSERT INTO `varosok` VALUES (452,3253,10,12,'Istenmezeje');
INSERT INTO `varosok` VALUES (453,3254,10,12,'Váraszó');
INSERT INTO `varosok` VALUES (454,3255,10,12,'Fedémes');
INSERT INTO `varosok` VALUES (455,3256,10,12,'Kisfüzes');
INSERT INTO `varosok` VALUES (456,3257,10,12,'Bükkszenterzsébet');
INSERT INTO `varosok` VALUES (457,3258,10,12,'Tarnalelesz');
INSERT INTO `varosok` VALUES (458,3259,10,12,'Szentdomonkos');
INSERT INTO `varosok` VALUES (459,3261,10,12,'Abasár');
INSERT INTO `varosok` VALUES (460,3262,10,12,'Markaz');
INSERT INTO `varosok` VALUES (461,3263,10,12,'Domoszló');
INSERT INTO `varosok` VALUES (462,3264,10,12,'Kisnána');
INSERT INTO `varosok` VALUES (463,3265,10,12,'Vécs');
INSERT INTO `varosok` VALUES (464,3271,10,12,'Visonta');
INSERT INTO `varosok` VALUES (465,3273,10,12,'Halmajugra');
INSERT INTO `varosok` VALUES (466,3274,10,12,'Ludas');
INSERT INTO `varosok` VALUES (467,3275,10,12,'Detk');
INSERT INTO `varosok` VALUES (468,3281,10,12,'Karácsond');
INSERT INTO `varosok` VALUES (469,3282,10,12,'Nagyfüged');
INSERT INTO `varosok` VALUES (470,3283,10,12,'Tarnazsadány');
INSERT INTO `varosok` VALUES (471,3284,10,12,'Tarnaméra');
INSERT INTO `varosok` VALUES (472,3291,10,12,'Vámosgyörk');
INSERT INTO `varosok` VALUES (473,3292,10,12,'Adács');
INSERT INTO `varosok` VALUES (474,3293,10,12,'Visznek');
INSERT INTO `varosok` VALUES (475,3294,10,12,'Tarnaörs');
INSERT INTO `varosok` VALUES (476,3295,10,12,'Erk');
INSERT INTO `varosok` VALUES (477,3296,10,12,'Zaránk');
INSERT INTO `varosok` VALUES (478,3300,10,12,'Eger');
INSERT INTO `varosok` VALUES (479,3321,10,12,'Egerbakta');
INSERT INTO `varosok` VALUES (480,3322,10,12,'Hevesaranyos');
INSERT INTO `varosok` VALUES (481,3323,10,12,'Szarvaskő');
INSERT INTO `varosok` VALUES (482,3324,10,12,'Felsőtárkány');
INSERT INTO `varosok` VALUES (483,3325,10,12,'Noszvaj');
INSERT INTO `varosok` VALUES (484,3326,10,12,'Ostoros');
INSERT INTO `varosok` VALUES (485,3327,10,12,'Novaj');
INSERT INTO `varosok` VALUES (486,3328,10,12,'Egerszólát');
INSERT INTO `varosok` VALUES (487,3331,10,12,'Tarnaszentmária');
INSERT INTO `varosok` VALUES (488,3332,10,12,'Sirok');
INSERT INTO `varosok` VALUES (489,3334,10,12,'Szajla');
INSERT INTO `varosok` VALUES (490,3335,10,12,'Bükkszék');
INSERT INTO `varosok` VALUES (491,3336,10,12,'Bátor');
INSERT INTO `varosok` VALUES (492,3337,10,12,'Egerbocs');
INSERT INTO `varosok` VALUES (493,3341,10,12,'Egercsehi');
INSERT INTO `varosok` VALUES (494,3343,10,12,'Bekölce');
INSERT INTO `varosok` VALUES (495,3344,10,12,'Mikófalva');
INSERT INTO `varosok` VALUES (496,3345,10,12,'Mónosbél');
INSERT INTO `varosok` VALUES (497,3346,10,12,'Bélapátfalva');
INSERT INTO `varosok` VALUES (498,3347,10,12,'Balaton');
INSERT INTO `varosok` VALUES (499,3348,10,12,'Szilvásvárad');
INSERT INTO `varosok` VALUES (500,3349,10,12,'Nagyvisnyó');
INSERT INTO `varosok` VALUES (501,3350,10,12,'Kál');
INSERT INTO `varosok` VALUES (502,3351,10,12,'Verpelét');
INSERT INTO `varosok` VALUES (503,3352,10,12,'Feldebrő');
INSERT INTO `varosok` VALUES (504,3353,10,12,'Aldebrő');
INSERT INTO `varosok` VALUES (505,3354,10,12,'Tófalu');
INSERT INTO `varosok` VALUES (506,3355,10,12,'Kápolna');
INSERT INTO `varosok` VALUES (507,3356,10,12,'Kompolt');
INSERT INTO `varosok` VALUES (508,3357,10,12,'Nagyút');
INSERT INTO `varosok` VALUES (509,3358,10,12,'Erdőtelek');
INSERT INTO `varosok` VALUES (510,3359,10,12,'Tenk');
INSERT INTO `varosok` VALUES (511,3360,10,12,'Heves');
INSERT INTO `varosok` VALUES (512,3368,10,12,'Boconád');
INSERT INTO `varosok` VALUES (513,3369,10,12,'Tarnabod');
INSERT INTO `varosok` VALUES (514,3371,10,12,'Átány');
INSERT INTO `varosok` VALUES (515,3372,10,12,'Kömlő');
INSERT INTO `varosok` VALUES (516,3373,10,12,'Besenyőtelek');
INSERT INTO `varosok` VALUES (517,3374,10,12,'Dormánd');
INSERT INTO `varosok` VALUES (518,3375,10,12,'Mezőtárkány');
INSERT INTO `varosok` VALUES (519,3377,10,12,'Szihalom');
INSERT INTO `varosok` VALUES (520,3378,10,12,'Mezőszemere');
INSERT INTO `varosok` VALUES (521,3379,10,12,'Egerfarmos');
INSERT INTO `varosok` VALUES (522,3381,10,12,'Pély');
INSERT INTO `varosok` VALUES (523,3382,10,12,'Tarnaszentmiklós');
INSERT INTO `varosok` VALUES (524,3383,10,12,'Hevesvezekény');
INSERT INTO `varosok` VALUES (525,3384,10,12,'Kisköre');
INSERT INTO `varosok` VALUES (526,3385,10,12,'Tiszanána');
INSERT INTO `varosok` VALUES (527,3386,10,12,'Sarud');
INSERT INTO `varosok` VALUES (528,3387,10,12,'Újlőrincfalva');
INSERT INTO `varosok` VALUES (529,3388,10,12,'Poroszló');
INSERT INTO `varosok` VALUES (530,3390,10,12,'Füzesabony');
INSERT INTO `varosok` VALUES (531,3394,10,12,'Egerszalók');
INSERT INTO `varosok` VALUES (532,3395,10,12,'Demjén');
INSERT INTO `varosok` VALUES (533,3396,10,12,'Kerecsend');
INSERT INTO `varosok` VALUES (534,3397,10,12,'Maklár');
INSERT INTO `varosok` VALUES (535,3398,10,12,'Nagytálya');
INSERT INTO `varosok` VALUES (536,3399,10,12,'Andornaktálya');
INSERT INTO `varosok` VALUES (537,3400,4,12,'Mezőkövesd');
INSERT INTO `varosok` VALUES (538,3411,4,12,'Szomolya');
INSERT INTO `varosok` VALUES (539,3412,4,12,'Bogács');
INSERT INTO `varosok` VALUES (540,3413,4,12,'Cserépfalu');
INSERT INTO `varosok` VALUES (541,3414,4,12,'Bükkzsérc');
INSERT INTO `varosok` VALUES (542,3416,4,12,'Tard');
INSERT INTO `varosok` VALUES (543,3417,4,12,'Cserépváralja');
INSERT INTO `varosok` VALUES (544,3418,4,12,'Szentistván');
INSERT INTO `varosok` VALUES (545,3421,4,12,'Mezőnyárád');
INSERT INTO `varosok` VALUES (546,3422,4,12,'Bükkábrány');
INSERT INTO `varosok` VALUES (547,3423,4,12,'Tibolddaróc');
INSERT INTO `varosok` VALUES (548,3424,4,12,'Kács');
INSERT INTO `varosok` VALUES (549,3425,4,12,'Sály');
INSERT INTO `varosok` VALUES (550,3426,4,12,'Borsodgeszt');
INSERT INTO `varosok` VALUES (551,3431,4,12,'Vatta');
INSERT INTO `varosok` VALUES (552,3432,4,12,'Emőd');
INSERT INTO `varosok` VALUES (553,3433,4,12,'Nyékládháza');
INSERT INTO `varosok` VALUES (554,3434,4,12,'Mályi');
INSERT INTO `varosok` VALUES (555,3441,4,12,'Mezőkeresztes');
INSERT INTO `varosok` VALUES (556,3442,4,12,'Csincse');
INSERT INTO `varosok` VALUES (557,3443,4,12,'Mezőnagymihály');
INSERT INTO `varosok` VALUES (558,3444,4,12,'Gelej');
INSERT INTO `varosok` VALUES (559,3450,4,12,'Mezőcsát');
INSERT INTO `varosok` VALUES (560,3458,4,12,'Tiszakeszi');
INSERT INTO `varosok` VALUES (561,3459,4,12,'Igrici');
INSERT INTO `varosok` VALUES (562,3461,4,12,'Egerlövő');
INSERT INTO `varosok` VALUES (563,3462,4,12,'Borsodivánka');
INSERT INTO `varosok` VALUES (564,3463,4,12,'Négyes');
INSERT INTO `varosok` VALUES (565,3464,4,12,'Tiszavalk');
INSERT INTO `varosok` VALUES (566,3465,4,12,'Tiszabábolna');
INSERT INTO `varosok` VALUES (567,3466,4,12,'Tiszadorogma');
INSERT INTO `varosok` VALUES (568,3467,4,12,'Ároktő');
INSERT INTO `varosok` VALUES (569,3551,4,12,'Ónod');
INSERT INTO `varosok` VALUES (570,3552,4,12,'Muhi');
INSERT INTO `varosok` VALUES (571,3553,4,12,'Kistokaj');
INSERT INTO `varosok` VALUES (572,3554,4,12,'Bükkaranyos');
INSERT INTO `varosok` VALUES (573,3555,4,12,'Harsány');
INSERT INTO `varosok` VALUES (574,3556,4,12,'Kisgyőr');
INSERT INTO `varosok` VALUES (575,3557,4,12,'Bükkszentkereszt');
INSERT INTO `varosok` VALUES (576,3559,4,12,'Répáshuta');
INSERT INTO `varosok` VALUES (577,3561,4,12,'Felsőzsolca');
INSERT INTO `varosok` VALUES (578,3562,4,12,'Onga');
INSERT INTO `varosok` VALUES (579,3563,4,12,'Hernádkak');
INSERT INTO `varosok` VALUES (580,3564,4,12,'Hernádnémeti');
INSERT INTO `varosok` VALUES (581,3565,4,12,'Tiszalúc');
INSERT INTO `varosok` VALUES (582,3571,4,12,'Alsózsolca');
INSERT INTO `varosok` VALUES (583,3572,4,12,'Sajólád');
INSERT INTO `varosok` VALUES (584,3573,4,12,'Sajópetri');
INSERT INTO `varosok` VALUES (585,3574,4,12,'Bőcs');
INSERT INTO `varosok` VALUES (586,3575,4,12,'Berzék');
INSERT INTO `varosok` VALUES (587,3576,4,12,'Sajóhidvég');
INSERT INTO `varosok` VALUES (588,3577,4,12,'Köröm');
INSERT INTO `varosok` VALUES (589,3578,4,12,'Girincs');
INSERT INTO `varosok` VALUES (590,3579,4,12,'Kesznyéten');
INSERT INTO `varosok` VALUES (591,3580,4,12,'Tiszaújváros');
INSERT INTO `varosok` VALUES (592,3586,4,12,'Sajóörös');
INSERT INTO `varosok` VALUES (593,3587,4,12,'Tiszapalkonya');
INSERT INTO `varosok` VALUES (594,3588,4,12,'Hejőkürt');
INSERT INTO `varosok` VALUES (595,3589,4,12,'Tiszatarján');
INSERT INTO `varosok` VALUES (596,3591,4,12,'Oszlár');
INSERT INTO `varosok` VALUES (597,3592,4,12,'Nemesbikk');
INSERT INTO `varosok` VALUES (598,3593,4,12,'Hejőbába');
INSERT INTO `varosok` VALUES (599,3594,4,12,'Hejőpapi');
INSERT INTO `varosok` VALUES (600,3595,4,12,'Hejőszalonta');
INSERT INTO `varosok` VALUES (601,3596,4,12,'Szakáld');
INSERT INTO `varosok` VALUES (602,3597,4,12,'Hejőkeresztúr');
INSERT INTO `varosok` VALUES (603,3598,4,12,'Nagycsécs');
INSERT INTO `varosok` VALUES (604,3599,4,12,'Sajószöged');
INSERT INTO `varosok` VALUES (605,3600,4,12,'Ózd');
INSERT INTO `varosok` VALUES (606,3622,4,12,'Uppony');
INSERT INTO `varosok` VALUES (607,3623,4,12,'Borsodszentgyörgy');
INSERT INTO `varosok` VALUES (608,3626,4,12,'Hangony');
INSERT INTO `varosok` VALUES (609,3627,4,12,'Domaháza');
INSERT INTO `varosok` VALUES (610,3630,4,12,'Putnok');
INSERT INTO `varosok` VALUES (611,3635,4,12,'Dubicsány');
INSERT INTO `varosok` VALUES (612,3636,4,12,'Vadna');
INSERT INTO `varosok` VALUES (613,3641,4,12,'Nagybarca');
INSERT INTO `varosok` VALUES (614,3642,4,12,'Bánhorváti');
INSERT INTO `varosok` VALUES (615,3643,4,12,'Dédestapolcsány');
INSERT INTO `varosok` VALUES (616,3644,4,12,'Tardona');
INSERT INTO `varosok` VALUES (617,3645,4,12,'Mályinka');
INSERT INTO `varosok` VALUES (618,3646,4,12,'Nekézseny');
INSERT INTO `varosok` VALUES (619,3647,4,12,'Csokvaomány');
INSERT INTO `varosok` VALUES (620,3648,4,12,'Csernely');
INSERT INTO `varosok` VALUES (621,3652,4,12,'Sajónémeti');
INSERT INTO `varosok` VALUES (622,3653,4,12,'Sajópüspöki');
INSERT INTO `varosok` VALUES (623,3654,4,12,'Bánréve');
INSERT INTO `varosok` VALUES (624,3655,4,12,'Hét');
INSERT INTO `varosok` VALUES (625,3656,4,12,'Sajóvelezd');
INSERT INTO `varosok` VALUES (626,3657,4,12,'Királd');
INSERT INTO `varosok` VALUES (627,3658,4,12,'Borsodbóta');
INSERT INTO `varosok` VALUES (628,3659,4,12,'Sáta');
INSERT INTO `varosok` VALUES (629,3663,4,12,'Arló');
INSERT INTO `varosok` VALUES (630,3664,4,12,'Járdánháza');
INSERT INTO `varosok` VALUES (631,3671,4,12,'Borsodnádasd');
INSERT INTO `varosok` VALUES (632,3700,4,12,'Kazincbarcika');
INSERT INTO `varosok` VALUES (633,3711,4,12,'Szirmabesenyő');
INSERT INTO `varosok` VALUES (634,3712,4,12,'Sajóvámos');
INSERT INTO `varosok` VALUES (635,3713,4,12,'Arnót');
INSERT INTO `varosok` VALUES (636,3714,4,12,'Sajópálfala');
INSERT INTO `varosok` VALUES (637,3715,4,12,'Gesztely');
INSERT INTO `varosok` VALUES (638,3716,4,12,'Újcsanálos');
INSERT INTO `varosok` VALUES (639,3717,4,12,'Alsódobsza');
INSERT INTO `varosok` VALUES (640,3718,4,12,'Megyaszó');
INSERT INTO `varosok` VALUES (641,3720,4,12,'Sajókaza');
INSERT INTO `varosok` VALUES (642,3721,4,12,'Felsőnyárád');
INSERT INTO `varosok` VALUES (643,3722,4,12,'Felsőkelecsény');
INSERT INTO `varosok` VALUES (644,3723,4,12,'Zubogy');
INSERT INTO `varosok` VALUES (645,3724,4,12,'Ragály');
INSERT INTO `varosok` VALUES (646,3726,4,12,'Zádorfalva');
INSERT INTO `varosok` VALUES (647,3728,4,12,'Kelemér');
INSERT INTO `varosok` VALUES (648,3729,4,12,'Serényfalva');
INSERT INTO `varosok` VALUES (649,3731,4,12,'Szuhakálló');
INSERT INTO `varosok` VALUES (650,3732,4,12,'Kurityán');
INSERT INTO `varosok` VALUES (651,3733,4,12,'Rudabánya');
INSERT INTO `varosok` VALUES (652,3734,4,12,'Szuhogy');
INSERT INTO `varosok` VALUES (653,3735,4,12,'Felsőtelekes');
INSERT INTO `varosok` VALUES (654,3741,4,12,'Izsófalva');
INSERT INTO `varosok` VALUES (655,3742,4,12,'Rudolftelep');
INSERT INTO `varosok` VALUES (656,3743,4,12,'Ormosbánya');
INSERT INTO `varosok` VALUES (657,3744,4,12,'Múcsony');
INSERT INTO `varosok` VALUES (658,3751,4,12,'Szendrőlád');
INSERT INTO `varosok` VALUES (659,3752,4,12,'Szendrő');
INSERT INTO `varosok` VALUES (660,3753,4,12,'Abod');
INSERT INTO `varosok` VALUES (661,3754,4,12,'Szalonna');
INSERT INTO `varosok` VALUES (662,3755,4,12,'Martonyi');
INSERT INTO `varosok` VALUES (663,3756,4,12,'Perkupa');
INSERT INTO `varosok` VALUES (664,3757,4,12,'Szőlősardó');
INSERT INTO `varosok` VALUES (665,3758,4,12,'Jósvafő');
INSERT INTO `varosok` VALUES (666,3759,4,12,'Aggtelek');
INSERT INTO `varosok` VALUES (667,3761,4,12,'Szin');
INSERT INTO `varosok` VALUES (668,3762,4,12,'Szögliget');
INSERT INTO `varosok` VALUES (669,3763,4,12,'Bódvaszilas');
INSERT INTO `varosok` VALUES (670,3764,4,12,'Bódvarákó');
INSERT INTO `varosok` VALUES (671,3765,4,12,'Komjáti');
INSERT INTO `varosok` VALUES (672,3767,4,12,'Tornanádaska');
INSERT INTO `varosok` VALUES (673,3768,4,12,'Hidvégardó');
INSERT INTO `varosok` VALUES (674,3769,4,12,'Tornaszentjakab');
INSERT INTO `varosok` VALUES (675,3770,4,12,'Sajószentpéter');
INSERT INTO `varosok` VALUES (676,3773,4,12,'Sajókápolna');
INSERT INTO `varosok` VALUES (677,3775,4,12,'Kondó');
INSERT INTO `varosok` VALUES (678,3776,4,12,'Radostyán');
INSERT INTO `varosok` VALUES (679,3777,4,12,'Parasznya');
INSERT INTO `varosok` VALUES (680,3778,4,12,'Varbó');
INSERT INTO `varosok` VALUES (681,3779,4,12,'Alacska');
INSERT INTO `varosok` VALUES (682,3780,4,12,'Edelény');
INSERT INTO `varosok` VALUES (683,3786,4,12,'Lak');
INSERT INTO `varosok` VALUES (684,3787,4,12,'Tomor');
INSERT INTO `varosok` VALUES (685,3791,4,12,'Sajókeresztúr');
INSERT INTO `varosok` VALUES (686,3792,4,12,'Sajóbábony');
INSERT INTO `varosok` VALUES (687,3793,4,12,'Sajóecseg');
INSERT INTO `varosok` VALUES (688,3794,4,12,'Boldva');
INSERT INTO `varosok` VALUES (689,3795,4,12,'Hangács');
INSERT INTO `varosok` VALUES (690,3796,4,12,'Borsodszirák');
INSERT INTO `varosok` VALUES (691,3800,4,12,'Szikszó');
INSERT INTO `varosok` VALUES (692,3809,4,12,'Selyeb');
INSERT INTO `varosok` VALUES (693,3811,4,12,'Alsóvadász');
INSERT INTO `varosok` VALUES (694,3812,4,12,'Homrogd');
INSERT INTO `varosok` VALUES (695,3813,4,12,'Kupa');
INSERT INTO `varosok` VALUES (696,3814,4,12,'Felsővadász');
INSERT INTO `varosok` VALUES (697,3815,4,12,'Gadna');
INSERT INTO `varosok` VALUES (698,3816,4,12,'Gagyvendégi');
INSERT INTO `varosok` VALUES (699,3817,4,12,'Gagybátor');
INSERT INTO `varosok` VALUES (700,3821,4,12,'Krasznokvajda');
INSERT INTO `varosok` VALUES (701,3825,4,12,'Rakaca');
INSERT INTO `varosok` VALUES (702,3826,4,12,'Rakacaszend');
INSERT INTO `varosok` VALUES (703,3831,4,12,'Kázsmárk');
INSERT INTO `varosok` VALUES (704,3832,4,12,'Léh');
INSERT INTO `varosok` VALUES (705,3833,4,12,'Rásonysápberencs');
INSERT INTO `varosok` VALUES (706,3834,4,12,'Detek');
INSERT INTO `varosok` VALUES (707,3836,4,12,'Baktakék');
INSERT INTO `varosok` VALUES (708,3837,4,12,'Felsőgagy');
INSERT INTO `varosok` VALUES (709,3841,4,12,'Aszaló');
INSERT INTO `varosok` VALUES (710,3842,4,12,'Halmaj');
INSERT INTO `varosok` VALUES (711,3843,4,12,'Kiskinizs');
INSERT INTO `varosok` VALUES (712,3844,4,12,'Nagykinizs');
INSERT INTO `varosok` VALUES (713,3846,4,12,'Hernádkércs');
INSERT INTO `varosok` VALUES (714,3847,4,12,'Felsődobsza');
INSERT INTO `varosok` VALUES (715,3848,4,12,'Csobád');
INSERT INTO `varosok` VALUES (716,3849,4,12,'Forró');
INSERT INTO `varosok` VALUES (717,3851,4,12,'Ináncs');
INSERT INTO `varosok` VALUES (718,3852,4,12,'Hernádszentandrás');
INSERT INTO `varosok` VALUES (719,3853,4,12,'Pere');
INSERT INTO `varosok` VALUES (720,3855,4,12,'Fancsal');
INSERT INTO `varosok` VALUES (721,3860,4,12,'Encs');
INSERT INTO `varosok` VALUES (722,3863,4,12,'Szalaszend');
INSERT INTO `varosok` VALUES (723,3864,4,12,'Fulókércs');
INSERT INTO `varosok` VALUES (724,3865,4,12,'Fáj');
INSERT INTO `varosok` VALUES (725,3866,4,12,'Szemere');
INSERT INTO `varosok` VALUES (726,3871,4,12,'Méra');
INSERT INTO `varosok` VALUES (727,3872,4,12,'Novajidrány');
INSERT INTO `varosok` VALUES (728,3873,4,12,'Garadna');
INSERT INTO `varosok` VALUES (729,3874,4,12,'Hernádvécse');
INSERT INTO `varosok` VALUES (730,3875,4,12,'Hernádszurdok');
INSERT INTO `varosok` VALUES (731,3876,4,12,'Hidasnémeti');
INSERT INTO `varosok` VALUES (732,3877,4,12,'Tornyosnémeti');
INSERT INTO `varosok` VALUES (733,3881,4,12,'Abaújszántó');
INSERT INTO `varosok` VALUES (734,3882,4,12,'Abaújkér');
INSERT INTO `varosok` VALUES (735,3884,4,12,'Boldogkőújfalu');
INSERT INTO `varosok` VALUES (736,3885,4,12,'Boldogkőváralja');
INSERT INTO `varosok` VALUES (737,3886,4,12,'Korlát');
INSERT INTO `varosok` VALUES (738,3887,4,12,'Hernádcéce');
INSERT INTO `varosok` VALUES (739,3888,4,12,'Vizsoly');
INSERT INTO `varosok` VALUES (740,3891,4,12,'Vilmány');
INSERT INTO `varosok` VALUES (741,3892,4,12,'Hejce');
INSERT INTO `varosok` VALUES (742,3893,4,12,'Fony');
INSERT INTO `varosok` VALUES (743,3894,4,12,'Göncruszka');
INSERT INTO `varosok` VALUES (744,3895,4,12,'Gönc');
INSERT INTO `varosok` VALUES (745,3896,4,12,'Telkibánya');
INSERT INTO `varosok` VALUES (746,3897,4,12,'Zsujta');
INSERT INTO `varosok` VALUES (747,3898,4,12,'Abaújvár');
INSERT INTO `varosok` VALUES (748,3899,4,12,'Kéked');
INSERT INTO `varosok` VALUES (749,3900,4,12,'Szerencs');
INSERT INTO `varosok` VALUES (750,3903,4,12,'Bekecs');
INSERT INTO `varosok` VALUES (751,3904,4,12,'Legyesbénye');
INSERT INTO `varosok` VALUES (752,3905,4,12,'Monok');
INSERT INTO `varosok` VALUES (753,3906,4,12,'Golop');
INSERT INTO `varosok` VALUES (754,3907,4,12,'Tállya');
INSERT INTO `varosok` VALUES (755,3908,4,12,'Rátka');
INSERT INTO `varosok` VALUES (756,3909,4,12,'Mád');
INSERT INTO `varosok` VALUES (757,3910,4,12,'Tokaj');
INSERT INTO `varosok` VALUES (758,3915,4,12,'Tarcal');
INSERT INTO `varosok` VALUES (759,3916,4,12,'Bodrogkeresztúr');
INSERT INTO `varosok` VALUES (760,3917,4,12,'Bodrogkisfalud');
INSERT INTO `varosok` VALUES (761,3918,4,12,'Szegi');
INSERT INTO `varosok` VALUES (762,3921,4,12,'Taktaszada');
INSERT INTO `varosok` VALUES (763,3922,4,12,'Taktaharkány');
INSERT INTO `varosok` VALUES (764,3924,4,12,'Taktakenéz');
INSERT INTO `varosok` VALUES (765,3925,4,12,'Prügy');
INSERT INTO `varosok` VALUES (766,3926,4,12,'Taktabáj');
INSERT INTO `varosok` VALUES (767,3927,4,12,'Csobaj');
INSERT INTO `varosok` VALUES (768,3928,4,12,'Tiszatardos');
INSERT INTO `varosok` VALUES (769,3929,4,12,'Tiszaladány');
INSERT INTO `varosok` VALUES (770,3931,4,12,'Mezőzombor');
INSERT INTO `varosok` VALUES (771,3932,4,12,'Erdőbénye');
INSERT INTO `varosok` VALUES (772,3933,4,12,'Olaszliszka');
INSERT INTO `varosok` VALUES (773,3934,4,12,'Tolcsva');
INSERT INTO `varosok` VALUES (774,3935,4,12,'Erdőhorváti');
INSERT INTO `varosok` VALUES (775,3936,4,12,'Háromhuta');
INSERT INTO `varosok` VALUES (776,3937,4,12,'Komlóska');
INSERT INTO `varosok` VALUES (777,3941,4,12,'Vámosújfalu');
INSERT INTO `varosok` VALUES (778,3942,4,12,'Sárazsadány');
INSERT INTO `varosok` VALUES (779,3943,4,12,'Bodrogolaszi');
INSERT INTO `varosok` VALUES (780,3950,4,12,'Sárospatak');
INSERT INTO `varosok` VALUES (781,3954,4,12,'Györgytarló');
INSERT INTO `varosok` VALUES (782,3955,4,12,'Kenézlő');
INSERT INTO `varosok` VALUES (783,3956,4,12,'Viss');
INSERT INTO `varosok` VALUES (784,3957,4,12,'Zalkod');
INSERT INTO `varosok` VALUES (785,3958,4,12,'Hercegkút');
INSERT INTO `varosok` VALUES (786,3959,4,12,'Makkoshotyka');
INSERT INTO `varosok` VALUES (787,3961,4,12,'Vajdácska');
INSERT INTO `varosok` VALUES (788,3962,4,12,'Karos');
INSERT INTO `varosok` VALUES (789,3963,4,12,'Karcsa');
INSERT INTO `varosok` VALUES (790,3964,4,12,'Pácin');
INSERT INTO `varosok` VALUES (791,3965,4,12,'Nagyrozvágy');
INSERT INTO `varosok` VALUES (792,3967,4,12,'Lácacséke');
INSERT INTO `varosok` VALUES (793,3971,4,12,'Tiszakarád');
INSERT INTO `varosok` VALUES (794,3972,4,12,'Tiszacsermely');
INSERT INTO `varosok` VALUES (795,3973,4,12,'Cigánd');
INSERT INTO `varosok` VALUES (796,3974,4,12,'Ricse');
INSERT INTO `varosok` VALUES (797,3976,4,12,'Révleányvár');
INSERT INTO `varosok` VALUES (798,3977,4,12,'Zemplénagárd');
INSERT INTO `varosok` VALUES (799,3978,4,12,'Dámóc');
INSERT INTO `varosok` VALUES (800,3980,4,12,'Sátoraljaújhely');
INSERT INTO `varosok` VALUES (801,3985,4,12,'Alsóberecki');
INSERT INTO `varosok` VALUES (802,3987,4,12,'Bodroghalom');
INSERT INTO `varosok` VALUES (803,3989,4,12,'Mikóháza');
INSERT INTO `varosok` VALUES (804,3991,4,12,'Vilyvitány');
INSERT INTO `varosok` VALUES (805,3992,4,12,'Kovácsvágás');
INSERT INTO `varosok` VALUES (806,3993,4,12,'Füzérradvány');
INSERT INTO `varosok` VALUES (807,3994,4,12,'Pálháza');
INSERT INTO `varosok` VALUES (808,3995,4,12,'Pusztafalu');
INSERT INTO `varosok` VALUES (809,3996,4,12,'Füzér');
INSERT INTO `varosok` VALUES (810,3997,4,12,'Füzérkomlós');
INSERT INTO `varosok` VALUES (811,3999,4,12,'Hollóháza');
INSERT INTO `varosok` VALUES (812,4060,9,12,'Balmazújváros');
INSERT INTO `varosok` VALUES (813,4064,9,12,'Nagyhegyes');
INSERT INTO `varosok` VALUES (814,4065,9,12,'Újszentmargita');
INSERT INTO `varosok` VALUES (815,4066,9,12,'Tiszacsege');
INSERT INTO `varosok` VALUES (816,4069,9,12,'Egyek');
INSERT INTO `varosok` VALUES (817,4071,9,12,'Hortobágy');
INSERT INTO `varosok` VALUES (818,4075,9,12,'Görbeháza');
INSERT INTO `varosok` VALUES (819,4080,9,12,'Hajdúnánás');
INSERT INTO `varosok` VALUES (820,4087,9,12,'Hajdúdorog');
INSERT INTO `varosok` VALUES (821,4090,9,12,'Polgár');
INSERT INTO `varosok` VALUES (822,4096,9,12,'Újtikos');
INSERT INTO `varosok` VALUES (823,4097,9,12,'Tiszagyulaháza');
INSERT INTO `varosok` VALUES (824,4100,9,12,'Berettyóújfalu');
INSERT INTO `varosok` VALUES (825,4110,9,12,'Biharkeresztes');
INSERT INTO `varosok` VALUES (826,4114,9,12,'Bojt');
INSERT INTO `varosok` VALUES (827,4115,9,12,'Ártánd');
INSERT INTO `varosok` VALUES (828,4116,9,12,'Berekböszörmény');
INSERT INTO `varosok` VALUES (829,4117,9,12,'Told');
INSERT INTO `varosok` VALUES (830,4118,9,12,'Mezőpeterd');
INSERT INTO `varosok` VALUES (831,4119,9,12,'Váncsod');
INSERT INTO `varosok` VALUES (832,4121,9,12,'Szentpéterszeg');
INSERT INTO `varosok` VALUES (833,4122,9,12,'Gáborján');
INSERT INTO `varosok` VALUES (834,4123,9,12,'Hencida');
INSERT INTO `varosok` VALUES (835,4124,9,12,'Esztár');
INSERT INTO `varosok` VALUES (836,4125,9,12,'Pocsaj');
INSERT INTO `varosok` VALUES (837,4126,9,12,'Kismarja');
INSERT INTO `varosok` VALUES (838,4127,9,12,'Nagykereki');
INSERT INTO `varosok` VALUES (839,4128,9,12,'Bedő');
INSERT INTO `varosok` VALUES (840,4130,9,12,'Derecske');
INSERT INTO `varosok` VALUES (841,4132,9,12,'Tépe');
INSERT INTO `varosok` VALUES (842,4133,9,12,'Konyár');
INSERT INTO `varosok` VALUES (843,4134,9,12,'Mezősas');
INSERT INTO `varosok` VALUES (844,4135,9,12,'Körösszegapáti');
INSERT INTO `varosok` VALUES (845,4136,9,12,'Körösszakál');
INSERT INTO `varosok` VALUES (846,4137,9,12,'Magyarhomorog');
INSERT INTO `varosok` VALUES (847,4138,9,12,'Komádi');
INSERT INTO `varosok` VALUES (848,4141,9,12,'Furta');
INSERT INTO `varosok` VALUES (849,4142,9,12,'Zsáka');
INSERT INTO `varosok` VALUES (850,4143,9,12,'Vekerd');
INSERT INTO `varosok` VALUES (851,4144,9,12,'Darvas');
INSERT INTO `varosok` VALUES (852,4145,9,12,'Csökmő');
INSERT INTO `varosok` VALUES (853,4146,9,12,'Újiráz');
INSERT INTO `varosok` VALUES (854,4150,9,12,'Püspökladány');
INSERT INTO `varosok` VALUES (855,4161,9,12,'Báránd');
INSERT INTO `varosok` VALUES (856,4163,9,12,'Szerep');
INSERT INTO `varosok` VALUES (857,4164,9,12,'Bakonszeg');
INSERT INTO `varosok` VALUES (858,4171,9,12,'Sárrétudvari');
INSERT INTO `varosok` VALUES (859,4172,9,12,'Biharnagybajom');
INSERT INTO `varosok` VALUES (860,4173,9,12,'Nagyrábé');
INSERT INTO `varosok` VALUES (861,4174,9,12,'Bihartorda');
INSERT INTO `varosok` VALUES (862,4175,9,12,'Bihardancsháza');
INSERT INTO `varosok` VALUES (863,4176,9,12,'Sáp');
INSERT INTO `varosok` VALUES (864,4177,9,12,'Földes');
INSERT INTO `varosok` VALUES (865,4181,9,12,'Nádudvar');
INSERT INTO `varosok` VALUES (866,4183,9,12,'Kaba');
INSERT INTO `varosok` VALUES (867,4184,9,12,'Tetétlen');
INSERT INTO `varosok` VALUES (868,4200,9,12,'Hajdúszoboszló');
INSERT INTO `varosok` VALUES (869,4211,9,12,'Ebes');
INSERT INTO `varosok` VALUES (870,4212,9,12,'Hajdúszovát');
INSERT INTO `varosok` VALUES (871,4220,9,12,'Hajdúböszörmény');
INSERT INTO `varosok` VALUES (872,4231,16,12,'Bököny');
INSERT INTO `varosok` VALUES (873,4232,16,12,'Geszteréd');
INSERT INTO `varosok` VALUES (874,4233,16,12,'Balkány');
INSERT INTO `varosok` VALUES (875,4234,16,12,'Szakoly');
INSERT INTO `varosok` VALUES (876,4235,16,12,'Biri');
INSERT INTO `varosok` VALUES (877,4241,9,12,'Bocskaikert');
INSERT INTO `varosok` VALUES (878,4242,9,12,'Hajdúhadház');
INSERT INTO `varosok` VALUES (879,4243,9,12,'Téglás');
INSERT INTO `varosok` VALUES (880,4244,16,12,'Újfehértó');
INSERT INTO `varosok` VALUES (881,4245,16,12,'Érpatak');
INSERT INTO `varosok` VALUES (882,4251,9,12,'Hajdúsámson');
INSERT INTO `varosok` VALUES (883,4254,9,12,'Nyíradony');
INSERT INTO `varosok` VALUES (884,4262,9,12,'Nyíracsád');
INSERT INTO `varosok` VALUES (885,4263,9,12,'Nyírmártonfalva');
INSERT INTO `varosok` VALUES (886,4264,9,12,'Nyírábrány');
INSERT INTO `varosok` VALUES (887,4266,9,12,'Fülöp');
INSERT INTO `varosok` VALUES (888,4267,16,12,'Penészlek');
INSERT INTO `varosok` VALUES (889,4271,9,12,'Mikepércs');
INSERT INTO `varosok` VALUES (890,4272,9,12,'Sáránd');
INSERT INTO `varosok` VALUES (891,4273,9,12,'Hajdúbagos');
INSERT INTO `varosok` VALUES (892,4274,9,12,'Hosszúpályi');
INSERT INTO `varosok` VALUES (893,4275,9,12,'Monostorpályi');
INSERT INTO `varosok` VALUES (894,4281,9,12,'Létavértes');
INSERT INTO `varosok` VALUES (895,4284,9,12,'Kokad');
INSERT INTO `varosok` VALUES (896,4285,9,12,'Álmosd');
INSERT INTO `varosok` VALUES (897,4286,9,12,'Bagamér');
INSERT INTO `varosok` VALUES (898,4287,9,12,'Vámospércs');
INSERT INTO `varosok` VALUES (899,4288,9,12,'Újléta');
INSERT INTO `varosok` VALUES (900,4300,16,12,'Nyírbátor');
INSERT INTO `varosok` VALUES (901,4311,16,12,'Nyírgyulaj');
INSERT INTO `varosok` VALUES (902,4320,16,12,'Nagykálló');
INSERT INTO `varosok` VALUES (903,4324,16,12,'Kállósemjén');
INSERT INTO `varosok` VALUES (904,4325,16,12,'Kisléta');
INSERT INTO `varosok` VALUES (905,4326,16,12,'Máriapócs');
INSERT INTO `varosok` VALUES (906,4327,16,12,'Pócspetri');
INSERT INTO `varosok` VALUES (907,4331,16,12,'Nyírcsászári');
INSERT INTO `varosok` VALUES (908,4332,16,12,'Nyírderzs');
INSERT INTO `varosok` VALUES (909,4333,16,12,'Nyírkáta');
INSERT INTO `varosok` VALUES (910,4334,16,12,'Hodász');
INSERT INTO `varosok` VALUES (911,4335,16,12,'Kántorjánosi');
INSERT INTO `varosok` VALUES (912,4336,16,12,'Őr');
INSERT INTO `varosok` VALUES (913,4337,16,12,'Jármi');
INSERT INTO `varosok` VALUES (914,4338,16,12,'Papos');
INSERT INTO `varosok` VALUES (915,4341,16,12,'Nyírvasvári');
INSERT INTO `varosok` VALUES (916,4342,16,12,'Terem');
INSERT INTO `varosok` VALUES (917,4343,16,12,'Bátorliget');
INSERT INTO `varosok` VALUES (918,4351,16,12,'Vállaj');
INSERT INTO `varosok` VALUES (919,4352,16,12,'Mérk');
INSERT INTO `varosok` VALUES (920,4353,16,12,'Tiborszállás');
INSERT INTO `varosok` VALUES (921,4354,16,12,'Fábiánháza');
INSERT INTO `varosok` VALUES (922,4355,16,12,'Nagyecsed');
INSERT INTO `varosok` VALUES (923,4356,16,12,'Nyírcsaholy');
INSERT INTO `varosok` VALUES (924,4361,16,12,'Nyírbogát');
INSERT INTO `varosok` VALUES (925,4362,16,12,'Nyírgelse');
INSERT INTO `varosok` VALUES (926,4363,16,12,'Nyírmihálydi');
INSERT INTO `varosok` VALUES (927,4371,16,12,'Nyírlugos');
INSERT INTO `varosok` VALUES (928,4372,16,12,'Nyírbéltek');
INSERT INTO `varosok` VALUES (929,4373,16,12,'Ömböly');
INSERT INTO `varosok` VALUES (930,4374,16,12,'Encsencs');
INSERT INTO `varosok` VALUES (931,4375,16,12,'Piricse');
INSERT INTO `varosok` VALUES (932,4376,16,12,'Nyírpilis');
INSERT INTO `varosok` VALUES (933,4400,16,12,'Nyíregyháza');
INSERT INTO `varosok` VALUES (934,4434,16,12,'Kálmánháza');
INSERT INTO `varosok` VALUES (935,4440,16,12,'Tiszavasvári');
INSERT INTO `varosok` VALUES (936,4441,16,12,'Szorgalmas');
INSERT INTO `varosok` VALUES (937,4445,16,12,'Nagycserkesz');
INSERT INTO `varosok` VALUES (938,4450,16,12,'Tiszalök');
INSERT INTO `varosok` VALUES (939,4455,16,12,'Tiszadada');
INSERT INTO `varosok` VALUES (940,4456,16,12,'Tiszadob');
INSERT INTO `varosok` VALUES (941,4461,16,12,'Nyírtelek');
INSERT INTO `varosok` VALUES (942,4463,16,12,'Tiszanagyfalu');
INSERT INTO `varosok` VALUES (943,4464,16,12,'Tiszaeszlár');
INSERT INTO `varosok` VALUES (944,4465,16,12,'Rakamaz');
INSERT INTO `varosok` VALUES (945,4466,16,12,'Timár');
INSERT INTO `varosok` VALUES (946,4467,16,12,'Szabolcs');
INSERT INTO `varosok` VALUES (947,4468,16,12,'Balsa');
INSERT INTO `varosok` VALUES (948,4471,16,12,'Gávavencsellő');
INSERT INTO `varosok` VALUES (949,4474,16,12,'Tiszabercel');
INSERT INTO `varosok` VALUES (950,4475,16,12,'Paszab');
INSERT INTO `varosok` VALUES (951,4482,16,12,'Kótaj');
INSERT INTO `varosok` VALUES (952,4483,16,12,'Buj');
INSERT INTO `varosok` VALUES (953,4484,16,12,'Ibrány');
INSERT INTO `varosok` VALUES (954,4485,16,12,'Nagyhalász');
INSERT INTO `varosok` VALUES (955,4487,16,12,'Tiszatelek');
INSERT INTO `varosok` VALUES (956,4488,16,12,'Beszterec');
INSERT INTO `varosok` VALUES (957,4491,16,12,'Újdombrád');
INSERT INTO `varosok` VALUES (958,4492,16,12,'Dombrád');
INSERT INTO `varosok` VALUES (959,4493,16,12,'Tiszakanyár');
INSERT INTO `varosok` VALUES (960,4494,16,12,'Kékcse');
INSERT INTO `varosok` VALUES (961,4495,16,12,'Döge');
INSERT INTO `varosok` VALUES (962,4496,16,12,'Szabolcsveresmart');
INSERT INTO `varosok` VALUES (963,4501,16,12,'Kemecse');
INSERT INTO `varosok` VALUES (964,4502,16,12,'Vasmegyer');
INSERT INTO `varosok` VALUES (965,4503,16,12,'Tiszarád');
INSERT INTO `varosok` VALUES (966,4511,16,12,'Nyírbogdány');
INSERT INTO `varosok` VALUES (967,4515,16,12,'Kék');
INSERT INTO `varosok` VALUES (968,4516,16,12,'Demecser');
INSERT INTO `varosok` VALUES (969,4517,16,12,'Gégény');
INSERT INTO `varosok` VALUES (970,4521,16,12,'Berkesz');
INSERT INTO `varosok` VALUES (971,4522,16,12,'Nyírtass');
INSERT INTO `varosok` VALUES (972,4523,16,12,'Pátroha');
INSERT INTO `varosok` VALUES (973,4524,16,12,'Ajak');
INSERT INTO `varosok` VALUES (974,4525,16,12,'Rétközberencs');
INSERT INTO `varosok` VALUES (975,4531,16,12,'Nyírpazony');
INSERT INTO `varosok` VALUES (976,4532,16,12,'Nyírtura');
INSERT INTO `varosok` VALUES (977,4533,16,12,'Sényő');
INSERT INTO `varosok` VALUES (978,4534,16,12,'Székely');
INSERT INTO `varosok` VALUES (979,4535,16,12,'Nyíribrony');
INSERT INTO `varosok` VALUES (980,4536,16,12,'Ramocsaháza');
INSERT INTO `varosok` VALUES (981,4537,16,12,'Nyírkércs');
INSERT INTO `varosok` VALUES (982,4541,16,12,'Nyírjákó');
INSERT INTO `varosok` VALUES (983,4542,16,12,'Petneháza');
INSERT INTO `varosok` VALUES (984,4543,16,12,'Laskod');
INSERT INTO `varosok` VALUES (985,4544,16,12,'Nyírkarász');
INSERT INTO `varosok` VALUES (986,4545,16,12,'Gyulaháza');
INSERT INTO `varosok` VALUES (987,4546,16,12,'Anarcs');
INSERT INTO `varosok` VALUES (988,4547,16,12,'Szabolcsbáka');
INSERT INTO `varosok` VALUES (989,4552,16,12,'Napkor');
INSERT INTO `varosok` VALUES (990,4553,16,12,'Apagy');
INSERT INTO `varosok` VALUES (991,4554,16,12,'Nyírtét');
INSERT INTO `varosok` VALUES (992,4555,16,12,'Levelek');
INSERT INTO `varosok` VALUES (993,4556,16,12,'Magy');
INSERT INTO `varosok` VALUES (994,4557,16,12,'Besenyőd');
INSERT INTO `varosok` VALUES (995,4558,16,12,'Ófehértó');
INSERT INTO `varosok` VALUES (996,4561,16,12,'Baktalórántháza');
INSERT INTO `varosok` VALUES (997,4562,16,12,'Vaja');
INSERT INTO `varosok` VALUES (998,4563,16,12,'Rohod');
INSERT INTO `varosok` VALUES (999,4564,16,12,'Nyírmada');
INSERT INTO `varosok` VALUES (1000,4565,16,12,'Pusztadobos');
INSERT INTO `varosok` VALUES (1001,4566,16,12,'Ilk');
INSERT INTO `varosok` VALUES (1002,4567,16,12,'Gemzse');
INSERT INTO `varosok` VALUES (1003,4600,16,12,'Kisvárda');
INSERT INTO `varosok` VALUES (1004,4611,16,12,'Jéke');
INSERT INTO `varosok` VALUES (1005,4621,16,12,'Fényeslitke');
INSERT INTO `varosok` VALUES (1006,4622,16,12,'Komoró');
INSERT INTO `varosok` VALUES (1007,4623,16,12,'Tuzsér');
INSERT INTO `varosok` VALUES (1008,4624,16,12,'Tiszabezdéd');
INSERT INTO `varosok` VALUES (1009,4625,16,12,'Záhony');
INSERT INTO `varosok` VALUES (1010,4627,16,12,'Zsurk');
INSERT INTO `varosok` VALUES (1011,4628,16,12,'Tiszaszentmárton');
INSERT INTO `varosok` VALUES (1012,4631,16,12,'Pap');
INSERT INTO `varosok` VALUES (1013,4632,16,12,'Nyírlövő');
INSERT INTO `varosok` VALUES (1014,4633,16,12,'Lövőpetri');
INSERT INTO `varosok` VALUES (1015,4634,16,12,'Aranyosapáti');
INSERT INTO `varosok` VALUES (1016,4635,16,12,'Újkenéz');
INSERT INTO `varosok` VALUES (1017,4641,16,12,'Mezőladány');
INSERT INTO `varosok` VALUES (1018,4642,16,12,'Tornyospálca');
INSERT INTO `varosok` VALUES (1019,4643,16,12,'Benk');
INSERT INTO `varosok` VALUES (1020,4644,16,12,'Mándok');
INSERT INTO `varosok` VALUES (1021,4645,16,12,'Tiszamogyorós');
INSERT INTO `varosok` VALUES (1022,4646,16,12,'Eperjeske');
INSERT INTO `varosok` VALUES (1023,4700,16,12,'Mátészalka');
INSERT INTO `varosok` VALUES (1024,4721,16,12,'Szamoskér');
INSERT INTO `varosok` VALUES (1025,4722,16,12,'Nyírmeggyes');
INSERT INTO `varosok` VALUES (1026,4731,16,12,'Tunyogmatolcs');
INSERT INTO `varosok` VALUES (1027,4732,16,12,'Cégénydányád');
INSERT INTO `varosok` VALUES (1028,4733,16,12,'Gyügye');
INSERT INTO `varosok` VALUES (1029,4734,16,12,'Szamosújlak');
INSERT INTO `varosok` VALUES (1030,4735,16,12,'Szamossályi');
INSERT INTO `varosok` VALUES (1031,4737,16,12,'Kisnamény');
INSERT INTO `varosok` VALUES (1032,4741,16,12,'Jánkmajtis');
INSERT INTO `varosok` VALUES (1033,4742,16,12,'Csegöld');
INSERT INTO `varosok` VALUES (1034,4743,16,12,'Csengersima');
INSERT INTO `varosok` VALUES (1035,4745,16,12,'Szamosbecs');
INSERT INTO `varosok` VALUES (1036,4746,16,12,'Szamostatárfalva');
INSERT INTO `varosok` VALUES (1037,4751,16,12,'Kocsord');
INSERT INTO `varosok` VALUES (1038,4752,16,12,'Győrtelek');
INSERT INTO `varosok` VALUES (1039,4754,16,12,'Géberjén');
INSERT INTO `varosok` VALUES (1040,4755,16,12,'Ököritófülpös');
INSERT INTO `varosok` VALUES (1041,4756,16,12,'Rápolt');
INSERT INTO `varosok` VALUES (1042,4761,16,12,'Porcsalma');
INSERT INTO `varosok` VALUES (1043,4762,16,12,'Tyukod');
INSERT INTO `varosok` VALUES (1044,4763,16,12,'Ura');
INSERT INTO `varosok` VALUES (1045,4764,16,12,'Csengerújfalu');
INSERT INTO `varosok` VALUES (1046,4765,16,12,'Csenger');
INSERT INTO `varosok` VALUES (1047,4766,16,12,'Pátyod');
INSERT INTO `varosok` VALUES (1048,4767,16,12,'Szamosangyalos');
INSERT INTO `varosok` VALUES (1049,4800,16,12,'Vásárosnamény');
INSERT INTO `varosok` VALUES (1050,4811,16,12,'Kisvarsány');
INSERT INTO `varosok` VALUES (1051,4812,16,12,'Nagyvarsány');
INSERT INTO `varosok` VALUES (1052,4813,16,12,'Gyüre');
INSERT INTO `varosok` VALUES (1053,4821,16,12,'Ópályi');
INSERT INTO `varosok` VALUES (1054,4822,16,12,'Nyírparasznya');
INSERT INTO `varosok` VALUES (1055,4823,16,12,'Nagydobos');
INSERT INTO `varosok` VALUES (1056,4824,16,12,'Szamosszeg');
INSERT INTO `varosok` VALUES (1057,4826,16,12,'Olcsva');
INSERT INTO `varosok` VALUES (1058,4831,16,12,'Tiszaszalka');
INSERT INTO `varosok` VALUES (1059,4832,16,12,'Tiszavid');
INSERT INTO `varosok` VALUES (1060,4833,16,12,'Tiszaadony');
INSERT INTO `varosok` VALUES (1061,4834,16,12,'Tiszakerecseny');
INSERT INTO `varosok` VALUES (1062,4835,16,12,'Mátyus');
INSERT INTO `varosok` VALUES (1063,4836,16,12,'Lónya');
INSERT INTO `varosok` VALUES (1064,4841,16,12,'Jánd');
INSERT INTO `varosok` VALUES (1065,4842,16,12,'Gulács');
INSERT INTO `varosok` VALUES (1066,4843,16,12,'Hetefejércse');
INSERT INTO `varosok` VALUES (1067,4844,16,12,'Csaroda');
INSERT INTO `varosok` VALUES (1068,4845,16,12,'Tákos');
INSERT INTO `varosok` VALUES (1069,4900,16,12,'Fehérgyarmat');
INSERT INTO `varosok` VALUES (1070,4911,16,12,'Nábrád');
INSERT INTO `varosok` VALUES (1071,4912,16,12,'Kérsemjén');
INSERT INTO `varosok` VALUES (1072,4913,16,12,'Panyola');
INSERT INTO `varosok` VALUES (1073,4914,16,12,'Olcsvaapáti');
INSERT INTO `varosok` VALUES (1074,4921,16,12,'Kisar');
INSERT INTO `varosok` VALUES (1075,4922,16,12,'Nagyar');
INSERT INTO `varosok` VALUES (1076,4931,16,12,'Tarpa');
INSERT INTO `varosok` VALUES (1077,4932,16,12,'Márokpapi');
INSERT INTO `varosok` VALUES (1078,4933,16,12,'Beregsurány');
INSERT INTO `varosok` VALUES (1079,4934,16,12,'Beregdaróc');
INSERT INTO `varosok` VALUES (1080,4935,16,12,'Gelénes');
INSERT INTO `varosok` VALUES (1081,4936,16,12,'Vámosatya');
INSERT INTO `varosok` VALUES (1082,4937,16,12,'Barabás');
INSERT INTO `varosok` VALUES (1083,4941,16,12,'Penyige');
INSERT INTO `varosok` VALUES (1084,4942,16,12,'Mánd');
INSERT INTO `varosok` VALUES (1085,4943,16,12,'Kömörő');
INSERT INTO `varosok` VALUES (1086,4944,16,12,'Túristvándi');
INSERT INTO `varosok` VALUES (1087,4945,16,12,'Szatmárcseke');
INSERT INTO `varosok` VALUES (1088,4946,16,12,'Tiszakóród');
INSERT INTO `varosok` VALUES (1089,4947,16,12,'Tiszacsécse');
INSERT INTO `varosok` VALUES (1090,4948,16,12,'Milota');
INSERT INTO `varosok` VALUES (1091,4951,16,12,'Tiszabecs');
INSERT INTO `varosok` VALUES (1092,4952,16,12,'Uszka');
INSERT INTO `varosok` VALUES (1093,4953,16,12,'Magosliget');
INSERT INTO `varosok` VALUES (1094,4954,16,12,'Sonkád');
INSERT INTO `varosok` VALUES (1095,4955,16,12,'Botpalád');
INSERT INTO `varosok` VALUES (1096,4956,16,12,'Kispalád');
INSERT INTO `varosok` VALUES (1097,4961,16,12,'Zsarolyán');
INSERT INTO `varosok` VALUES (1098,4962,16,12,'Nagyszekeres');
INSERT INTO `varosok` VALUES (1099,4963,16,12,'Kisszekeres');
INSERT INTO `varosok` VALUES (1100,4964,16,12,'Fülesd');
INSERT INTO `varosok` VALUES (1101,4965,16,12,'Kölcse');
INSERT INTO `varosok` VALUES (1102,4966,16,12,'Vámosoroszi');
INSERT INTO `varosok` VALUES (1103,4967,16,12,'Csaholc');
INSERT INTO `varosok` VALUES (1104,4968,16,12,'Túrricse');
INSERT INTO `varosok` VALUES (1105,4969,16,12,'Tisztaberek');
INSERT INTO `varosok` VALUES (1106,4971,16,12,'Rozsály');
INSERT INTO `varosok` VALUES (1107,4972,16,12,'Gacsály');
INSERT INTO `varosok` VALUES (1108,4973,16,12,'Császló');
INSERT INTO `varosok` VALUES (1109,4974,16,12,'Zajta');
INSERT INTO `varosok` VALUES (1110,4975,16,12,'Méhtelek');
INSERT INTO `varosok` VALUES (1111,4976,16,12,'Garbolc');
INSERT INTO `varosok` VALUES (1112,4977,16,12,'Nagyhódos');
INSERT INTO `varosok` VALUES (1113,5000,11,12,'Szolnok');
INSERT INTO `varosok` VALUES (1114,5051,11,12,'Zagyvarékas');
INSERT INTO `varosok` VALUES (1115,5052,11,12,'Újszász');
INSERT INTO `varosok` VALUES (1116,5053,11,12,'Szászberek');
INSERT INTO `varosok` VALUES (1117,5054,11,12,'Jászalsószentgyörgy');
INSERT INTO `varosok` VALUES (1118,5055,11,12,'Jászladány');
INSERT INTO `varosok` VALUES (1119,5061,11,12,'Tiszasüly');
INSERT INTO `varosok` VALUES (1120,5062,11,12,'Kőtelek');
INSERT INTO `varosok` VALUES (1121,5063,11,12,'Hunyadfalva');
INSERT INTO `varosok` VALUES (1122,5064,11,12,'Csataszög');
INSERT INTO `varosok` VALUES (1123,5065,11,12,'Nagykörű');
INSERT INTO `varosok` VALUES (1124,5071,11,12,'Besenyszög');
INSERT INTO `varosok` VALUES (1125,5081,11,12,'Szajol');
INSERT INTO `varosok` VALUES (1126,5082,11,12,'Tiszatenyő');
INSERT INTO `varosok` VALUES (1127,5083,11,12,'Kengyel');
INSERT INTO `varosok` VALUES (1128,5084,11,12,'Rákócziújfalu');
INSERT INTO `varosok` VALUES (1129,5085,11,12,'Rákóczifalva');
INSERT INTO `varosok` VALUES (1130,5091,11,12,'Tószeg');
INSERT INTO `varosok` VALUES (1131,5092,11,12,'Tiszavárkony');
INSERT INTO `varosok` VALUES (1132,5093,11,12,'Vezseny');
INSERT INTO `varosok` VALUES (1133,5094,11,12,'Tiszajenő');
INSERT INTO `varosok` VALUES (1134,5100,11,12,'Jászberény');
INSERT INTO `varosok` VALUES (1135,5111,11,12,'Jászfelsőszentgyörgy');
INSERT INTO `varosok` VALUES (1136,5121,11,12,'Jászjákóhalma');
INSERT INTO `varosok` VALUES (1137,5122,11,12,'Jászdózsa');
INSERT INTO `varosok` VALUES (1138,5123,11,12,'Jászárokszállás');
INSERT INTO `varosok` VALUES (1139,5124,11,12,'Jászágó');
INSERT INTO `varosok` VALUES (1140,5125,11,12,'Pusztamonostor');
INSERT INTO `varosok` VALUES (1141,5126,11,12,'Jászfényszaru');
INSERT INTO `varosok` VALUES (1142,5130,11,12,'Jászapáti');
INSERT INTO `varosok` VALUES (1143,5135,11,12,'Jászivány');
INSERT INTO `varosok` VALUES (1144,5136,11,12,'Jászszentandrás');
INSERT INTO `varosok` VALUES (1145,5137,11,12,'Jászkisér');
INSERT INTO `varosok` VALUES (1146,5141,11,12,'Jásztelek');
INSERT INTO `varosok` VALUES (1147,5142,11,12,'Alattyán');
INSERT INTO `varosok` VALUES (1148,5143,11,12,'Jánoshida');
INSERT INTO `varosok` VALUES (1149,5144,11,12,'Jászboldogháza');
INSERT INTO `varosok` VALUES (1150,5200,11,12,'Törökszentmiklós');
INSERT INTO `varosok` VALUES (1151,5211,11,12,'Tiszapüspöki');
INSERT INTO `varosok` VALUES (1152,5222,11,12,'Örményes');
INSERT INTO `varosok` VALUES (1153,5231,11,12,'Fegyvernek');
INSERT INTO `varosok` VALUES (1154,5232,11,12,'Tiszabő');
INSERT INTO `varosok` VALUES (1155,5233,11,12,'Tiszagyenda');
INSERT INTO `varosok` VALUES (1156,5234,11,12,'Tiszaroff');
INSERT INTO `varosok` VALUES (1157,5235,11,12,'Tiszabura');
INSERT INTO `varosok` VALUES (1158,5241,11,12,'Abádszalók');
INSERT INTO `varosok` VALUES (1159,5243,11,12,'Tiszaderzs');
INSERT INTO `varosok` VALUES (1160,5244,11,12,'Tiszaszőlős');
INSERT INTO `varosok` VALUES (1161,5300,11,12,'Karcag');
INSERT INTO `varosok` VALUES (1162,5309,11,12,'Berekfürdő');
INSERT INTO `varosok` VALUES (1163,5310,11,12,'Kisújszállás');
INSERT INTO `varosok` VALUES (1164,5321,11,12,'Kunmadaras');
INSERT INTO `varosok` VALUES (1165,5322,11,12,'Tiszaszentimre');
INSERT INTO `varosok` VALUES (1166,5324,11,12,'Tomajmonostora');
INSERT INTO `varosok` VALUES (1167,5331,11,12,'Kenderes');
INSERT INTO `varosok` VALUES (1168,5340,11,12,'Kunhegyes');
INSERT INTO `varosok` VALUES (1169,5350,11,12,'Tiszafüred');
INSERT INTO `varosok` VALUES (1170,5361,11,12,'Tiszaigar');
INSERT INTO `varosok` VALUES (1171,5362,11,12,'Tiszaörs');
INSERT INTO `varosok` VALUES (1172,5363,11,12,'Nagyiván');
INSERT INTO `varosok` VALUES (1173,5400,11,12,'Mezőtúr');
INSERT INTO `varosok` VALUES (1174,5411,11,12,'Kétpó');
INSERT INTO `varosok` VALUES (1175,5412,11,12,'Kuncsorba');
INSERT INTO `varosok` VALUES (1176,5420,11,12,'Túrkeve');
INSERT INTO `varosok` VALUES (1177,5430,11,12,'Tiszaföldvár');
INSERT INTO `varosok` VALUES (1178,5435,11,12,'Martfű');
INSERT INTO `varosok` VALUES (1179,5440,11,12,'Kunszentmárton');
INSERT INTO `varosok` VALUES (1180,5451,11,12,'Öcsöd');
INSERT INTO `varosok` VALUES (1181,5452,11,12,'Mesterszállás');
INSERT INTO `varosok` VALUES (1182,5453,11,12,'Mezőhék');
INSERT INTO `varosok` VALUES (1183,5462,11,12,'Cibakháza');
INSERT INTO `varosok` VALUES (1184,5463,11,12,'Nagyrév');
INSERT INTO `varosok` VALUES (1185,5464,11,12,'Tiszainoka');
INSERT INTO `varosok` VALUES (1186,5465,11,12,'Cserkeszőlő');
INSERT INTO `varosok` VALUES (1187,5471,11,12,'Tiszakürt');
INSERT INTO `varosok` VALUES (1188,5473,11,12,'Tiszaug');
INSERT INTO `varosok` VALUES (1189,5474,11,12,'Tiszasas');
INSERT INTO `varosok` VALUES (1190,5475,11,12,'Csépa');
INSERT INTO `varosok` VALUES (1191,5476,11,12,'Szelevény');
INSERT INTO `varosok` VALUES (1192,5500,3,12,'Gyomaendrőd');
INSERT INTO `varosok` VALUES (1193,5510,3,12,'Dévaványa');
INSERT INTO `varosok` VALUES (1194,5515,3,12,'Ecsegfalva');
INSERT INTO `varosok` VALUES (1195,5516,3,12,'Körösladány');
INSERT INTO `varosok` VALUES (1196,5520,3,12,'Szeghalom');
INSERT INTO `varosok` VALUES (1197,5525,3,12,'Füzesgyarmat');
INSERT INTO `varosok` VALUES (1198,5526,3,12,'Kertészsziget');
INSERT INTO `varosok` VALUES (1199,5527,3,12,'Bucsa');
INSERT INTO `varosok` VALUES (1200,5530,3,12,'Vésztő');
INSERT INTO `varosok` VALUES (1201,5534,3,12,'Okány');
INSERT INTO `varosok` VALUES (1202,5536,3,12,'Körösújfalu');
INSERT INTO `varosok` VALUES (1203,5537,3,12,'Zsadány');
INSERT INTO `varosok` VALUES (1204,5538,3,12,'Biharugra');
INSERT INTO `varosok` VALUES (1205,5539,3,12,'Körösnagyharsány');
INSERT INTO `varosok` VALUES (1206,5540,3,12,'Szarvas');
INSERT INTO `varosok` VALUES (1207,5551,3,12,'Csabacsűd');
INSERT INTO `varosok` VALUES (1208,5552,3,12,'Kardos');
INSERT INTO `varosok` VALUES (1209,5553,3,12,'Kondoros');
INSERT INTO `varosok` VALUES (1210,5555,3,12,'Hunya');
INSERT INTO `varosok` VALUES (1211,5556,3,12,'Örménykút');
INSERT INTO `varosok` VALUES (1212,5561,3,12,'Békésszentandrás');
INSERT INTO `varosok` VALUES (1213,5600,3,12,'Békéscsaba');
INSERT INTO `varosok` VALUES (1214,5609,3,12,'Csabaszabadi');
INSERT INTO `varosok` VALUES (1215,5621,3,12,'Csárdaszállás');
INSERT INTO `varosok` VALUES (1216,5622,3,12,'Köröstarcsa');
INSERT INTO `varosok` VALUES (1217,5624,3,12,'Doboz');
INSERT INTO `varosok` VALUES (1218,5630,3,12,'Békés');
INSERT INTO `varosok` VALUES (1219,5641,3,12,'Tarhos');
INSERT INTO `varosok` VALUES (1220,5643,3,12,'Bélmegyer');
INSERT INTO `varosok` VALUES (1221,5650,3,12,'Mezőberény');
INSERT INTO `varosok` VALUES (1222,5661,3,12,'Újkígyós');
INSERT INTO `varosok` VALUES (1223,5662,3,12,'Csanádapáca');
INSERT INTO `varosok` VALUES (1224,5663,3,12,'Medgyesbodzás');
INSERT INTO `varosok` VALUES (1225,5665,3,12,'Pusztaottlaka');
INSERT INTO `varosok` VALUES (1226,5666,3,12,'Medgyesegyháza');
INSERT INTO `varosok` VALUES (1227,5667,3,12,'Magyarbánhegyes');
INSERT INTO `varosok` VALUES (1228,5668,3,12,'Nagybánhegyes');
INSERT INTO `varosok` VALUES (1229,5672,3,12,'Murony');
INSERT INTO `varosok` VALUES (1230,5673,3,12,'Kamut');
INSERT INTO `varosok` VALUES (1231,5674,3,12,'Kétsoprony');
INSERT INTO `varosok` VALUES (1232,5675,3,12,'Telekgerendás');
INSERT INTO `varosok` VALUES (1233,5700,3,12,'Gyula');
INSERT INTO `varosok` VALUES (1234,5712,3,12,'Szabadkígyós');
INSERT INTO `varosok` VALUES (1235,5720,3,12,'Sarkad');
INSERT INTO `varosok` VALUES (1236,5725,3,12,'Kötegyán');
INSERT INTO `varosok` VALUES (1237,5726,3,12,'Méhkerék');
INSERT INTO `varosok` VALUES (1238,5727,3,12,'Újszalonta');
INSERT INTO `varosok` VALUES (1239,5731,3,12,'Sarkadkeresztúr');
INSERT INTO `varosok` VALUES (1240,5732,3,12,'Mezőgyán');
INSERT INTO `varosok` VALUES (1241,5734,3,12,'Geszt');
INSERT INTO `varosok` VALUES (1242,5741,3,12,'Kétegyháza');
INSERT INTO `varosok` VALUES (1243,5742,3,12,'Elek');
INSERT INTO `varosok` VALUES (1244,5743,3,12,'Lőkösháza');
INSERT INTO `varosok` VALUES (1245,5744,3,12,'Kevermes');
INSERT INTO `varosok` VALUES (1246,5745,3,12,'Dombiratos');
INSERT INTO `varosok` VALUES (1247,5746,3,12,'Kunágota');
INSERT INTO `varosok` VALUES (1248,5747,3,12,'Almáskamarás');
INSERT INTO `varosok` VALUES (1249,5751,3,12,'Nagykamarás');
INSERT INTO `varosok` VALUES (1250,5800,3,12,'Mezőkovácsháza');
INSERT INTO `varosok` VALUES (1251,5811,3,12,'Végegyháza');
INSERT INTO `varosok` VALUES (1252,5820,3,12,'Mezőhegyes');
INSERT INTO `varosok` VALUES (1253,5830,3,12,'Battonya');
INSERT INTO `varosok` VALUES (1254,5836,3,12,'Dombegyház');
INSERT INTO `varosok` VALUES (1255,5837,3,12,'Kisdombegyház');
INSERT INTO `varosok` VALUES (1256,5838,3,12,'Magyardombegyház');
INSERT INTO `varosok` VALUES (1257,5900,3,12,'Orosháza');
INSERT INTO `varosok` VALUES (1258,5919,3,12,'Pusztaföldvár');
INSERT INTO `varosok` VALUES (1259,5920,3,12,'Csorvás');
INSERT INTO `varosok` VALUES (1260,5925,3,12,'Gerendás');
INSERT INTO `varosok` VALUES (1261,5931,3,12,'Nagyszénás');
INSERT INTO `varosok` VALUES (1262,5932,3,12,'Gádoros');
INSERT INTO `varosok` VALUES (1263,5940,3,12,'Tótkomlós');
INSERT INTO `varosok` VALUES (1264,5945,3,12,'Kardoskút');
INSERT INTO `varosok` VALUES (1265,5946,3,12,'Békéssámson');
INSERT INTO `varosok` VALUES (1266,5948,3,12,'Kaszaper');
INSERT INTO `varosok` VALUES (1267,6000,1,12,'Kecskemét');
INSERT INTO `varosok` VALUES (1268,6031,1,12,'Szentkirály');
INSERT INTO `varosok` VALUES (1269,6032,1,12,'Nyárlőrinc');
INSERT INTO `varosok` VALUES (1270,6033,1,12,'Városföld');
INSERT INTO `varosok` VALUES (1271,6034,1,12,'Helvécia');
INSERT INTO `varosok` VALUES (1272,6035,1,12,'Ballószög');
INSERT INTO `varosok` VALUES (1273,6041,1,12,'Kerekegyháza');
INSERT INTO `varosok` VALUES (1274,6042,1,12,'Fülöpháza');
INSERT INTO `varosok` VALUES (1275,6043,1,12,'Kunbaracs');
INSERT INTO `varosok` VALUES (1276,6045,1,12,'Ladánybene');
INSERT INTO `varosok` VALUES (1277,6050,1,12,'Lajosmizse');
INSERT INTO `varosok` VALUES (1278,6055,1,12,'Felsőlajos');
INSERT INTO `varosok` VALUES (1279,6060,1,12,'Tiszakécske');
INSERT INTO `varosok` VALUES (1280,6065,1,12,'Lakitelek');
INSERT INTO `varosok` VALUES (1281,6066,1,12,'Tiszaalpár');
INSERT INTO `varosok` VALUES (1282,6070,1,12,'Izsák');
INSERT INTO `varosok` VALUES (1283,6075,1,12,'Páhi');
INSERT INTO `varosok` VALUES (1284,6076,1,12,'Ágasegyháza');
INSERT INTO `varosok` VALUES (1285,6077,1,12,'Orgovány');
INSERT INTO `varosok` VALUES (1286,6078,1,12,'Jakabszállás');
INSERT INTO `varosok` VALUES (1287,6080,1,12,'Szabadszállás');
INSERT INTO `varosok` VALUES (1288,6085,1,12,'Fülöpszállás');
INSERT INTO `varosok` VALUES (1289,6086,1,12,'Szalkszentmárton');
INSERT INTO `varosok` VALUES (1290,6087,1,12,'Dunavecse');
INSERT INTO `varosok` VALUES (1291,6088,1,12,'Apostag');
INSERT INTO `varosok` VALUES (1292,6090,1,12,'Kunszentmiklós');
INSERT INTO `varosok` VALUES (1293,6096,1,12,'Kunpeszér');
INSERT INTO `varosok` VALUES (1294,6097,1,12,'Kunadacs');
INSERT INTO `varosok` VALUES (1295,6098,1,12,'Tass');
INSERT INTO `varosok` VALUES (1296,6100,1,12,'Kiskunfélegyháza');
INSERT INTO `varosok` VALUES (1297,6111,1,12,'Gátér');
INSERT INTO `varosok` VALUES (1298,6112,1,12,'Pálmonostora');
INSERT INTO `varosok` VALUES (1299,6113,1,12,'Petőfiszállás');
INSERT INTO `varosok` VALUES (1300,6114,1,12,'Bugac');
INSERT INTO `varosok` VALUES (1301,6115,1,12,'Kunszállás');
INSERT INTO `varosok` VALUES (1302,6116,1,12,'Fülöpjakab');
INSERT INTO `varosok` VALUES (1303,6120,1,12,'Kiskunmajsa');
INSERT INTO `varosok` VALUES (1304,6131,1,12,'Szank');
INSERT INTO `varosok` VALUES (1305,6132,1,12,'Móricgát');
INSERT INTO `varosok` VALUES (1306,6133,1,12,'Jászszentlászló');
INSERT INTO `varosok` VALUES (1307,6134,1,12,'Kömpöc');
INSERT INTO `varosok` VALUES (1308,6135,1,12,'Csólyospálos');
INSERT INTO `varosok` VALUES (1309,6136,1,12,'Harkakötöny');
INSERT INTO `varosok` VALUES (1310,6200,1,12,'Kiskőrös');
INSERT INTO `varosok` VALUES (1311,6211,1,12,'Kaskantyú');
INSERT INTO `varosok` VALUES (1312,6221,1,12,'Akasztó');
INSERT INTO `varosok` VALUES (1313,6222,1,12,'Csengőd');
INSERT INTO `varosok` VALUES (1314,6223,1,12,'Soltszentimre');
INSERT INTO `varosok` VALUES (1315,6224,1,12,'Tabdi');
INSERT INTO `varosok` VALUES (1316,6230,1,12,'Soltvadkert');
INSERT INTO `varosok` VALUES (1317,6235,1,12,'Bócsa');
INSERT INTO `varosok` VALUES (1318,6236,1,12,'Tázlár');
INSERT INTO `varosok` VALUES (1319,6237,1,12,'Kecel');
INSERT INTO `varosok` VALUES (1320,6238,1,12,'Imrehegy');
INSERT INTO `varosok` VALUES (1321,6239,1,12,'Császártöltés');
INSERT INTO `varosok` VALUES (1322,6300,1,12,'Kalocsa');
INSERT INTO `varosok` VALUES (1323,6311,1,12,'Öregcsertő');
INSERT INTO `varosok` VALUES (1324,6320,1,12,'Solt');
INSERT INTO `varosok` VALUES (1325,6321,1,12,'Újsolt');
INSERT INTO `varosok` VALUES (1326,6323,1,12,'Dunaegyháza');
INSERT INTO `varosok` VALUES (1327,6325,1,12,'Dunatetétlen');
INSERT INTO `varosok` VALUES (1328,6326,1,12,'Harta');
INSERT INTO `varosok` VALUES (1329,6328,1,12,'Dunapataj');
INSERT INTO `varosok` VALUES (1330,6331,1,12,'Foktő');
INSERT INTO `varosok` VALUES (1331,6332,1,12,'Uszód');
INSERT INTO `varosok` VALUES (1332,6333,1,12,'Dunaszentbenedek');
INSERT INTO `varosok` VALUES (1333,6334,1,12,'Géderlak');
INSERT INTO `varosok` VALUES (1334,6335,1,12,'Ordas');
INSERT INTO `varosok` VALUES (1335,6336,1,12,'Szakmár');
INSERT INTO `varosok` VALUES (1336,6337,1,12,'Újtelek');
INSERT INTO `varosok` VALUES (1337,6341,1,12,'Homokmégy');
INSERT INTO `varosok` VALUES (1338,6342,1,12,'Drágszél');
INSERT INTO `varosok` VALUES (1339,6343,1,12,'Miske');
INSERT INTO `varosok` VALUES (1340,6344,1,12,'Hajós');
INSERT INTO `varosok` VALUES (1341,6345,1,12,'Nemesnádudvar');
INSERT INTO `varosok` VALUES (1342,6346,1,12,'Sükösd');
INSERT INTO `varosok` VALUES (1343,6347,1,12,'Érsekcsanád');
INSERT INTO `varosok` VALUES (1344,6348,1,12,'Érsekhalma');
INSERT INTO `varosok` VALUES (1345,6351,1,12,'Bátya');
INSERT INTO `varosok` VALUES (1346,6352,1,12,'Fajsz');
INSERT INTO `varosok` VALUES (1347,6353,1,12,'Dusnok');
INSERT INTO `varosok` VALUES (1348,6400,1,12,'Kiskunhalas');
INSERT INTO `varosok` VALUES (1349,6411,1,12,'Zsana');
INSERT INTO `varosok` VALUES (1350,6412,1,12,'Balotaszállás');
INSERT INTO `varosok` VALUES (1351,6413,1,12,'Kunfehértó');
INSERT INTO `varosok` VALUES (1352,6414,1,12,'Pirtó');
INSERT INTO `varosok` VALUES (1353,6421,1,12,'Kisszállás');
INSERT INTO `varosok` VALUES (1354,6422,1,12,'Tompa');
INSERT INTO `varosok` VALUES (1355,6423,1,12,'Kelebia');
INSERT INTO `varosok` VALUES (1356,6424,1,12,'Csikéria');
INSERT INTO `varosok` VALUES (1357,6425,1,12,'Bácsszőlős');
INSERT INTO `varosok` VALUES (1358,6430,1,12,'Bácsalmás');
INSERT INTO `varosok` VALUES (1359,6435,1,12,'Kunbaja');
INSERT INTO `varosok` VALUES (1360,6440,1,12,'Jánoshalma');
INSERT INTO `varosok` VALUES (1361,6444,1,12,'Kéleshalom');
INSERT INTO `varosok` VALUES (1362,6445,1,12,'Borota');
INSERT INTO `varosok` VALUES (1363,6446,1,12,'Rém');
INSERT INTO `varosok` VALUES (1364,6447,1,12,'Felsőszentiván');
INSERT INTO `varosok` VALUES (1365,6448,1,12,'Csávoly');
INSERT INTO `varosok` VALUES (1366,6449,1,12,'Mélykút');
INSERT INTO `varosok` VALUES (1367,6451,1,12,'Tataháza');
INSERT INTO `varosok` VALUES (1368,6452,1,12,'Mátételke');
INSERT INTO `varosok` VALUES (1369,6453,1,12,'Bácsbokod');
INSERT INTO `varosok` VALUES (1370,6454,1,12,'Bácsborsód');
INSERT INTO `varosok` VALUES (1371,6455,1,12,'Katymár');
INSERT INTO `varosok` VALUES (1372,6456,1,12,'Madaras');
INSERT INTO `varosok` VALUES (1373,6500,1,12,'Baja');
INSERT INTO `varosok` VALUES (1374,6511,1,12,'Bácsszentgyörgy');
INSERT INTO `varosok` VALUES (1375,6512,1,12,'Szeremle');
INSERT INTO `varosok` VALUES (1376,6513,1,12,'Dunafalva');
INSERT INTO `varosok` VALUES (1377,6521,1,12,'Vaskút');
INSERT INTO `varosok` VALUES (1378,6522,1,12,'Gara');
INSERT INTO `varosok` VALUES (1379,6523,1,12,'Csátalja');
INSERT INTO `varosok` VALUES (1380,6524,1,12,'Dávod');
INSERT INTO `varosok` VALUES (1381,6525,1,12,'Hercegszántó');
INSERT INTO `varosok` VALUES (1382,6527,1,12,'Nagybaracska');
INSERT INTO `varosok` VALUES (1383,6528,1,12,'Bátmonostor');
INSERT INTO `varosok` VALUES (1384,6600,6,12,'Szentes');
INSERT INTO `varosok` VALUES (1385,6612,6,12,'Nagytőke');
INSERT INTO `varosok` VALUES (1386,6621,6,12,'Derekegyház');
INSERT INTO `varosok` VALUES (1387,6622,6,12,'Nagymágocs');
INSERT INTO `varosok` VALUES (1388,6623,6,12,'Árpádhalom');
INSERT INTO `varosok` VALUES (1389,6624,6,12,'Eperjes');
INSERT INTO `varosok` VALUES (1390,6625,6,12,'Fábiánsebestyén');
INSERT INTO `varosok` VALUES (1391,6630,6,12,'Mindszent');
INSERT INTO `varosok` VALUES (1392,6635,6,12,'Szegvár');
INSERT INTO `varosok` VALUES (1393,6636,6,12,'Mártély');
INSERT INTO `varosok` VALUES (1394,6640,6,12,'Csongrád');
INSERT INTO `varosok` VALUES (1395,6645,6,12,'Felgyő');
INSERT INTO `varosok` VALUES (1396,6646,6,12,'Tömörkény');
INSERT INTO `varosok` VALUES (1397,6647,6,12,'Csanytelek');
INSERT INTO `varosok` VALUES (1398,6750,6,12,'Algyő');
INSERT INTO `varosok` VALUES (1399,6754,6,12,'Újszentiván');
INSERT INTO `varosok` VALUES (1400,6755,6,12,'Kübekháza');
INSERT INTO `varosok` VALUES (1401,6756,6,12,'Tiszasziget');
INSERT INTO `varosok` VALUES (1402,6758,6,12,'Röszke');
INSERT INTO `varosok` VALUES (1403,6760,6,12,'Kistelek');
INSERT INTO `varosok` VALUES (1404,6762,6,12,'Sándorfalva');
INSERT INTO `varosok` VALUES (1405,6763,6,12,'Szatymaz');
INSERT INTO `varosok` VALUES (1406,6764,6,12,'Balástya');
INSERT INTO `varosok` VALUES (1407,6765,6,12,'Csengele');
INSERT INTO `varosok` VALUES (1408,6766,6,12,'Dóc');
INSERT INTO `varosok` VALUES (1409,6767,6,12,'Ópusztaszer');
INSERT INTO `varosok` VALUES (1410,6768,6,12,'Baks');
INSERT INTO `varosok` VALUES (1411,6769,6,12,'Pusztaszer');
INSERT INTO `varosok` VALUES (1412,6772,6,12,'Deszk');
INSERT INTO `varosok` VALUES (1413,6773,6,12,'Klárafalva');
INSERT INTO `varosok` VALUES (1414,6774,6,12,'Ferencszállás');
INSERT INTO `varosok` VALUES (1415,6775,6,12,'Kiszombor');
INSERT INTO `varosok` VALUES (1416,6781,6,12,'Domaszék');
INSERT INTO `varosok` VALUES (1417,6782,6,12,'Mórahalom');
INSERT INTO `varosok` VALUES (1418,6783,6,12,'Ásotthalom');
INSERT INTO `varosok` VALUES (1419,6784,6,12,'Öttömös');
INSERT INTO `varosok` VALUES (1420,6785,6,12,'Pusztamérges');
INSERT INTO `varosok` VALUES (1421,6786,6,12,'Ruzsa');
INSERT INTO `varosok` VALUES (1422,6787,6,12,'Zákányszék');
INSERT INTO `varosok` VALUES (1423,6792,6,12,'Zsombó');
INSERT INTO `varosok` VALUES (1424,6793,6,12,'Forráskút');
INSERT INTO `varosok` VALUES (1425,6794,6,12,'Üllés');
INSERT INTO `varosok` VALUES (1426,6795,6,12,'Bordány');
INSERT INTO `varosok` VALUES (1427,6800,6,12,'Hódmezővásárhely');
INSERT INTO `varosok` VALUES (1428,6821,6,12,'Székkutas');
INSERT INTO `varosok` VALUES (1429,6900,6,12,'Makó');
INSERT INTO `varosok` VALUES (1430,6911,6,12,'Királyhegyes');
INSERT INTO `varosok` VALUES (1431,6912,6,12,'Kövegy');
INSERT INTO `varosok` VALUES (1432,6913,6,12,'Csanádpalota');
INSERT INTO `varosok` VALUES (1433,6914,6,12,'Pitvaros');
INSERT INTO `varosok` VALUES (1434,6915,6,12,'Csanádalberti');
INSERT INTO `varosok` VALUES (1435,6916,6,12,'Ambrózfalva');
INSERT INTO `varosok` VALUES (1436,6917,6,12,'Nagyér');
INSERT INTO `varosok` VALUES (1437,6921,6,12,'Maroslele');
INSERT INTO `varosok` VALUES (1438,6922,6,12,'Földeák');
INSERT INTO `varosok` VALUES (1439,6923,6,12,'Óföldeák');
INSERT INTO `varosok` VALUES (1440,6931,6,12,'Apátfalva');
INSERT INTO `varosok` VALUES (1441,6932,6,12,'Magyarcsanád');
INSERT INTO `varosok` VALUES (1442,6933,6,12,'Nagylak');
INSERT INTO `varosok` VALUES (1443,7000,7,12,'Sárbogárd');
INSERT INTO `varosok` VALUES (1444,7011,7,12,'Alap');
INSERT INTO `varosok` VALUES (1445,7012,7,12,'Alsószentiván');
INSERT INTO `varosok` VALUES (1446,7013,7,12,'Cece');
INSERT INTO `varosok` VALUES (1447,7014,7,12,'Sáregres');
INSERT INTO `varosok` VALUES (1448,7015,7,12,'Igar');
INSERT INTO `varosok` VALUES (1449,7017,7,12,'Mezőszilas');
INSERT INTO `varosok` VALUES (1450,7020,17,12,'Dunaföldvár');
INSERT INTO `varosok` VALUES (1451,7025,17,12,'Bölcske');
INSERT INTO `varosok` VALUES (1452,7026,17,12,'Madocsa');
INSERT INTO `varosok` VALUES (1453,7030,17,12,'Paks');
INSERT INTO `varosok` VALUES (1454,7038,17,12,'Pusztahencse');
INSERT INTO `varosok` VALUES (1455,7039,17,12,'Németkér');
INSERT INTO `varosok` VALUES (1456,7041,7,12,'Vajta');
INSERT INTO `varosok` VALUES (1457,7042,17,12,'Pálfa');
INSERT INTO `varosok` VALUES (1458,7043,17,12,'Bikács');
INSERT INTO `varosok` VALUES (1459,7044,17,12,'Nagydorog');
INSERT INTO `varosok` VALUES (1460,7045,17,12,'Györköny');
INSERT INTO `varosok` VALUES (1461,7047,17,12,'Sárszentlőrinc');
INSERT INTO `varosok` VALUES (1462,7051,17,12,'Kajdacs');
INSERT INTO `varosok` VALUES (1463,7052,17,12,'Kölesd');
INSERT INTO `varosok` VALUES (1464,7054,17,12,'Tengelic');
INSERT INTO `varosok` VALUES (1465,7056,17,12,'Szedres');
INSERT INTO `varosok` VALUES (1466,7057,17,12,'Medina');
INSERT INTO `varosok` VALUES (1467,7061,17,12,'Belecska');
INSERT INTO `varosok` VALUES (1468,7062,17,12,'Keszőhidegkút');
INSERT INTO `varosok` VALUES (1469,7063,17,12,'Szárazd');
INSERT INTO `varosok` VALUES (1470,7064,17,12,'Gyönk');
INSERT INTO `varosok` VALUES (1471,7065,17,12,'Miszla');
INSERT INTO `varosok` VALUES (1472,7066,17,12,'Udvari');
INSERT INTO `varosok` VALUES (1473,7067,17,12,'Varsád');
INSERT INTO `varosok` VALUES (1474,7068,17,12,'Kistormás');
INSERT INTO `varosok` VALUES (1475,7071,17,12,'Szakadát');
INSERT INTO `varosok` VALUES (1476,7072,17,12,'Diósberény');
INSERT INTO `varosok` VALUES (1477,7081,17,12,'Simontornya');
INSERT INTO `varosok` VALUES (1478,7082,17,12,'Kisszékely');
INSERT INTO `varosok` VALUES (1479,7083,17,12,'Tolnanémedi');
INSERT INTO `varosok` VALUES (1480,7084,17,12,'Pincehely');
INSERT INTO `varosok` VALUES (1481,7085,17,12,'Nagyszékely');
INSERT INTO `varosok` VALUES (1482,7086,17,12,'Ozora');
INSERT INTO `varosok` VALUES (1483,7087,17,12,'Fürged');
INSERT INTO `varosok` VALUES (1484,7090,17,12,'Tamási');
INSERT INTO `varosok` VALUES (1485,7092,17,12,'Nagykónyi');
INSERT INTO `varosok` VALUES (1486,7093,17,12,'Értény');
INSERT INTO `varosok` VALUES (1487,7094,17,12,'Koppányszántó');
INSERT INTO `varosok` VALUES (1488,7095,17,12,'Iregszemcse');
INSERT INTO `varosok` VALUES (1489,7097,17,12,'Nagyszokoly');
INSERT INTO `varosok` VALUES (1490,7098,17,12,'Magyarkeszi');
INSERT INTO `varosok` VALUES (1491,7099,17,12,'Felsőnyék');
INSERT INTO `varosok` VALUES (1492,7100,17,12,'Szekszárd');
INSERT INTO `varosok` VALUES (1493,7121,17,12,'Szálka');
INSERT INTO `varosok` VALUES (1494,7122,17,12,'Kakasd');
INSERT INTO `varosok` VALUES (1495,7130,17,12,'Tolna');
INSERT INTO `varosok` VALUES (1496,7132,17,12,'Bogyiszló');
INSERT INTO `varosok` VALUES (1497,7133,17,12,'Fadd');
INSERT INTO `varosok` VALUES (1498,7134,17,12,'Gerjen');
INSERT INTO `varosok` VALUES (1499,7135,17,12,'Dunaszentgyörgy');
INSERT INTO `varosok` VALUES (1500,7136,17,12,'Fácánkert');
INSERT INTO `varosok` VALUES (1501,7140,17,12,'Bátaszék');
INSERT INTO `varosok` VALUES (1502,7142,17,12,'Pörböly');
INSERT INTO `varosok` VALUES (1503,7143,17,12,'Őcsény');
INSERT INTO `varosok` VALUES (1504,7144,17,12,'Decs');
INSERT INTO `varosok` VALUES (1505,7145,17,12,'Sárpilis');
INSERT INTO `varosok` VALUES (1506,7146,17,12,'Várdomb');
INSERT INTO `varosok` VALUES (1507,7147,17,12,'Alsónána');
INSERT INTO `varosok` VALUES (1508,7148,17,12,'Alsónyék');
INSERT INTO `varosok` VALUES (1509,7149,17,12,'Báta');
INSERT INTO `varosok` VALUES (1510,7150,17,12,'Bonyhád');
INSERT INTO `varosok` VALUES (1511,7158,17,12,'Bonyhádvarasd');
INSERT INTO `varosok` VALUES (1512,7159,17,12,'Kisdorog');
INSERT INTO `varosok` VALUES (1513,7161,17,12,'Cikó');
INSERT INTO `varosok` VALUES (1514,7162,17,12,'Grábóc');
INSERT INTO `varosok` VALUES (1515,7163,17,12,'Mőcsény');
INSERT INTO `varosok` VALUES (1516,7164,17,12,'Bátaapáti');
INSERT INTO `varosok` VALUES (1517,7165,17,12,'Mórágy');
INSERT INTO `varosok` VALUES (1518,7171,17,12,'Sióagárd');
INSERT INTO `varosok` VALUES (1519,7172,17,12,'Harc');
INSERT INTO `varosok` VALUES (1520,7173,17,12,'Zomba');
INSERT INTO `varosok` VALUES (1521,7174,17,12,'Kéty');
INSERT INTO `varosok` VALUES (1522,7175,17,12,'Felsőnána');
INSERT INTO `varosok` VALUES (1523,7176,17,12,'Murga');
INSERT INTO `varosok` VALUES (1524,7181,17,12,'Tevel');
INSERT INTO `varosok` VALUES (1525,7182,17,12,'Závod');
INSERT INTO `varosok` VALUES (1526,7183,17,12,'Kisvejke');
INSERT INTO `varosok` VALUES (1527,7184,2,12,'Lengyel');
INSERT INTO `varosok` VALUES (1528,7185,17,12,'Mucsfa');
INSERT INTO `varosok` VALUES (1529,7186,17,12,'Aparhant');
INSERT INTO `varosok` VALUES (1530,7191,17,12,'Hőgyész');
INSERT INTO `varosok` VALUES (1531,7192,17,12,'Szakály');
INSERT INTO `varosok` VALUES (1532,7193,17,12,'Regöly');
INSERT INTO `varosok` VALUES (1533,7194,17,12,'Kalaznó');
INSERT INTO `varosok` VALUES (1534,7195,17,12,'Mucsi');
INSERT INTO `varosok` VALUES (1535,7200,17,12,'Dombóvár');
INSERT INTO `varosok` VALUES (1536,7211,17,12,'Dalmand');
INSERT INTO `varosok` VALUES (1537,7212,17,12,'Kocsola');
INSERT INTO `varosok` VALUES (1538,7213,17,12,'Szakcs');
INSERT INTO `varosok` VALUES (1539,7214,17,12,'Lápafő');
INSERT INTO `varosok` VALUES (1540,7215,17,12,'Nak');
INSERT INTO `varosok` VALUES (1541,7224,17,12,'Dúzs');
INSERT INTO `varosok` VALUES (1542,7225,17,12,'Csibrák');
INSERT INTO `varosok` VALUES (1543,7226,17,12,'Kurd');
INSERT INTO `varosok` VALUES (1544,7227,17,12,'Gyulaj');
INSERT INTO `varosok` VALUES (1545,7228,17,12,'Döbrököz');
INSERT INTO `varosok` VALUES (1546,7251,17,12,'Kapospula');
INSERT INTO `varosok` VALUES (1547,7252,17,12,'Attala');
INSERT INTO `varosok` VALUES (1548,7253,15,12,'Csoma');
INSERT INTO `varosok` VALUES (1549,7255,15,12,'Nagyberki');
INSERT INTO `varosok` VALUES (1550,7256,15,12,'Kercseliget');
INSERT INTO `varosok` VALUES (1551,7257,15,12,'Mosdós');
INSERT INTO `varosok` VALUES (1552,7258,15,12,'Baté');
INSERT INTO `varosok` VALUES (1553,7261,15,12,'Taszár');
INSERT INTO `varosok` VALUES (1554,7271,15,12,'Fonó');
INSERT INTO `varosok` VALUES (1555,7272,15,12,'Gölle');
INSERT INTO `varosok` VALUES (1556,7273,15,12,'Büssü');
INSERT INTO `varosok` VALUES (1557,7274,15,12,'Kazsok');
INSERT INTO `varosok` VALUES (1558,7275,15,12,'Igal');
INSERT INTO `varosok` VALUES (1559,7276,15,12,'Somogyszil');
INSERT INTO `varosok` VALUES (1560,7279,15,12,'Kisgyalán');
INSERT INTO `varosok` VALUES (1561,7281,15,12,'Bonnya');
INSERT INTO `varosok` VALUES (1562,7282,15,12,'Kisbárapáti');
INSERT INTO `varosok` VALUES (1563,7283,15,12,'Somogyacsa');
INSERT INTO `varosok` VALUES (1564,7284,15,12,'Somogydöröcske');
INSERT INTO `varosok` VALUES (1565,7285,15,12,'Törökkoppány');
INSERT INTO `varosok` VALUES (1566,7286,15,12,'Miklósi');
INSERT INTO `varosok` VALUES (1567,7300,2,12,'Komló');
INSERT INTO `varosok` VALUES (1568,7304,2,12,'Mánfa');
INSERT INTO `varosok` VALUES (1569,7331,2,12,'Liget');
INSERT INTO `varosok` VALUES (1570,7332,2,12,'Magyaregregy');
INSERT INTO `varosok` VALUES (1571,7333,2,12,'Kárász');
INSERT INTO `varosok` VALUES (1572,7334,2,12,'Szalatnak');
INSERT INTO `varosok` VALUES (1573,7341,17,12,'Csikóstőttős');
INSERT INTO `varosok` VALUES (1574,7342,2,12,'Mágocs');
INSERT INTO `varosok` VALUES (1575,7343,2,12,'Nagyhajmás');
INSERT INTO `varosok` VALUES (1576,7344,2,12,'Mekényes');
INSERT INTO `varosok` VALUES (1577,7345,2,12,'Alsómocsolád');
INSERT INTO `varosok` VALUES (1578,7346,2,12,'Bikal');
INSERT INTO `varosok` VALUES (1579,7347,2,12,'Egyházaskozár');
INSERT INTO `varosok` VALUES (1580,7348,2,12,'Hegyhátmaróc');
INSERT INTO `varosok` VALUES (1581,7349,2,12,'Szászvár');
INSERT INTO `varosok` VALUES (1582,7351,2,12,'Máza');
INSERT INTO `varosok` VALUES (1583,7352,17,12,'Györe');
INSERT INTO `varosok` VALUES (1584,7353,17,12,'Izmény');
INSERT INTO `varosok` VALUES (1585,7354,17,12,'Váralja');
INSERT INTO `varosok` VALUES (1586,7355,17,12,'Nagymányok');
INSERT INTO `varosok` VALUES (1587,7356,17,12,'Kismányok');
INSERT INTO `varosok` VALUES (1588,7361,17,12,'Kaposszekcső');
INSERT INTO `varosok` VALUES (1589,7362,2,12,'Vásárosdombó');
INSERT INTO `varosok` VALUES (1590,7370,2,12,'Sásd');
INSERT INTO `varosok` VALUES (1591,7381,2,12,'Kisvaszar');
INSERT INTO `varosok` VALUES (1592,7383,2,12,'Tormás');
INSERT INTO `varosok` VALUES (1593,7384,2,12,'Baranyajenő');
INSERT INTO `varosok` VALUES (1594,7385,2,12,'Gödre');
INSERT INTO `varosok` VALUES (1595,7391,2,12,'Mindszentgodisa');
INSERT INTO `varosok` VALUES (1596,7393,2,12,'Bakóca');
INSERT INTO `varosok` VALUES (1597,7394,2,12,'Magyarhertelend');
INSERT INTO `varosok` VALUES (1598,7396,2,12,'Magyarszék');
INSERT INTO `varosok` VALUES (1599,7400,15,12,'Kaposvár');
INSERT INTO `varosok` VALUES (1600,7431,15,12,'Juta');
INSERT INTO `varosok` VALUES (1601,7432,15,12,'Hetes');
INSERT INTO `varosok` VALUES (1602,7434,15,12,'Mezőcsokonya');
INSERT INTO `varosok` VALUES (1603,7435,15,12,'Somogysárd');
INSERT INTO `varosok` VALUES (1604,7436,15,12,'Újvárfalva');
INSERT INTO `varosok` VALUES (1605,7439,15,12,'Bodrog');
INSERT INTO `varosok` VALUES (1606,7441,15,12,'Magyaregres');
INSERT INTO `varosok` VALUES (1607,7442,15,12,'Várda');
INSERT INTO `varosok` VALUES (1608,7443,15,12,'Somogyjád');
INSERT INTO `varosok` VALUES (1609,7444,15,12,'Osztopán');
INSERT INTO `varosok` VALUES (1610,7452,15,12,'Somogyaszaló');
INSERT INTO `varosok` VALUES (1611,7453,15,12,'Mernye');
INSERT INTO `varosok` VALUES (1612,7454,15,12,'Somodor');
INSERT INTO `varosok` VALUES (1613,7455,15,12,'Somogygeszti');
INSERT INTO `varosok` VALUES (1614,7456,15,12,'Felsőmocsolád');
INSERT INTO `varosok` VALUES (1615,7457,15,12,'Ecseny');
INSERT INTO `varosok` VALUES (1616,7458,15,12,'Polány');
INSERT INTO `varosok` VALUES (1617,7463,15,12,'Magyaratád');
INSERT INTO `varosok` VALUES (1618,7464,15,12,'Ráksi');
INSERT INTO `varosok` VALUES (1619,7465,15,12,'Szentgáloskér');
INSERT INTO `varosok` VALUES (1620,7471,15,12,'Zimány');
INSERT INTO `varosok` VALUES (1621,7472,15,12,'Szentbalázs');
INSERT INTO `varosok` VALUES (1622,7473,15,12,'Gálosfa');
INSERT INTO `varosok` VALUES (1623,7474,15,12,'Simonfa');
INSERT INTO `varosok` VALUES (1624,7475,15,12,'Bőszénfa');
INSERT INTO `varosok` VALUES (1625,7476,15,12,'Kaposszerdahely');
INSERT INTO `varosok` VALUES (1626,7477,15,12,'Szenna');
INSERT INTO `varosok` VALUES (1627,7478,15,12,'Bárdudvarnok');
INSERT INTO `varosok` VALUES (1628,7479,15,12,'Sántos');
INSERT INTO `varosok` VALUES (1629,7500,15,12,'Nagyatád');
INSERT INTO `varosok` VALUES (1630,7511,15,12,'Ötvöskónyi');
INSERT INTO `varosok` VALUES (1631,7512,15,12,'Mike');
INSERT INTO `varosok` VALUES (1632,7513,15,12,'Rinyaszentkirály');
INSERT INTO `varosok` VALUES (1633,7514,15,12,'Tarany');
INSERT INTO `varosok` VALUES (1634,7515,15,12,'Somogyudvarhely');
INSERT INTO `varosok` VALUES (1635,7516,15,12,'Berzence');
INSERT INTO `varosok` VALUES (1636,7517,15,12,'Bolhás');
INSERT INTO `varosok` VALUES (1637,7521,15,12,'Kaposmérő');
INSERT INTO `varosok` VALUES (1638,7522,15,12,'Kaposújlak');
INSERT INTO `varosok` VALUES (1639,7523,15,12,'Kaposfő');
INSERT INTO `varosok` VALUES (1640,7524,15,12,'Kiskorpád');
INSERT INTO `varosok` VALUES (1641,7525,15,12,'Jákó');
INSERT INTO `varosok` VALUES (1642,7526,15,12,'Csököly');
INSERT INTO `varosok` VALUES (1643,7527,15,12,'Gige');
INSERT INTO `varosok` VALUES (1644,7530,15,12,'Kadarkút');
INSERT INTO `varosok` VALUES (1645,7532,15,12,'Hencse');
INSERT INTO `varosok` VALUES (1646,7533,15,12,'Hedrehely');
INSERT INTO `varosok` VALUES (1647,7535,15,12,'Lad');
INSERT INTO `varosok` VALUES (1648,7536,15,12,'Patosfa');
INSERT INTO `varosok` VALUES (1649,7537,15,12,'Homokszentgyörgy');
INSERT INTO `varosok` VALUES (1650,7538,15,12,'Kálmáncsa');
INSERT INTO `varosok` VALUES (1651,7539,15,12,'Szulok');
INSERT INTO `varosok` VALUES (1652,7541,15,12,'Kutas');
INSERT INTO `varosok` VALUES (1653,7542,15,12,'Kisbajom');
INSERT INTO `varosok` VALUES (1654,7543,15,12,'Beleg');
INSERT INTO `varosok` VALUES (1655,7544,15,12,'Szabás');
INSERT INTO `varosok` VALUES (1656,7545,15,12,'Nagykorpád');
INSERT INTO `varosok` VALUES (1657,7551,15,12,'Lábod');
INSERT INTO `varosok` VALUES (1658,7552,15,12,'Rinyabesenyő');
INSERT INTO `varosok` VALUES (1659,7553,15,12,'Görgeteg');
INSERT INTO `varosok` VALUES (1660,7555,15,12,'Csokonyavisonta');
INSERT INTO `varosok` VALUES (1661,7556,15,12,'Rinyaújlak');
INSERT INTO `varosok` VALUES (1662,7561,15,12,'Nagybajom');
INSERT INTO `varosok` VALUES (1663,7562,15,12,'Segesd');
INSERT INTO `varosok` VALUES (1664,7563,15,12,'Somogyszob');
INSERT INTO `varosok` VALUES (1665,7564,15,12,'Kaszó');
INSERT INTO `varosok` VALUES (1666,7570,15,12,'Barcs');
INSERT INTO `varosok` VALUES (1667,7582,15,12,'Komlósd');
INSERT INTO `varosok` VALUES (1668,7584,15,12,'Babócsa');
INSERT INTO `varosok` VALUES (1669,7585,15,12,'Háromfa');
INSERT INTO `varosok` VALUES (1670,7586,15,12,'Bolhó');
INSERT INTO `varosok` VALUES (1671,7587,15,12,'Heresznye');
INSERT INTO `varosok` VALUES (1672,7588,15,12,'Vízvár');
INSERT INTO `varosok` VALUES (1673,7589,15,12,'Bélavár');
INSERT INTO `varosok` VALUES (1674,7661,2,12,'Erzsébet');
INSERT INTO `varosok` VALUES (1675,7663,2,12,'Máriakéménd');
INSERT INTO `varosok` VALUES (1676,7664,2,12,'Berkesd');
INSERT INTO `varosok` VALUES (1677,7666,2,12,'Pogány');
INSERT INTO `varosok` VALUES (1678,7668,2,12,'Keszü');
INSERT INTO `varosok` VALUES (1679,7671,2,12,'Bicsérd');
INSERT INTO `varosok` VALUES (1680,7672,2,12,'Boda');
INSERT INTO `varosok` VALUES (1681,7673,2,12,'Kővágószőlős');
INSERT INTO `varosok` VALUES (1682,7675,2,12,'Bakonya');
INSERT INTO `varosok` VALUES (1683,7677,2,12,'Orfű');
INSERT INTO `varosok` VALUES (1684,7678,2,12,'Abaliget');
INSERT INTO `varosok` VALUES (1685,7681,2,12,'Hetvehely');
INSERT INTO `varosok` VALUES (1686,7682,2,12,'Bükkösd');
INSERT INTO `varosok` VALUES (1687,7683,2,12,'Helesfa');
INSERT INTO `varosok` VALUES (1688,7694,2,12,'Hosszúhetény');
INSERT INTO `varosok` VALUES (1689,7695,2,12,'Mecseknádasd');
INSERT INTO `varosok` VALUES (1690,7696,2,12,'Hidas');
INSERT INTO `varosok` VALUES (1691,7700,2,12,'Mohács');
INSERT INTO `varosok` VALUES (1692,7711,2,12,'Bár');
INSERT INTO `varosok` VALUES (1693,7712,2,12,'Dunaszekcső');
INSERT INTO `varosok` VALUES (1694,7716,2,12,'Homorúd');
INSERT INTO `varosok` VALUES (1695,7717,2,12,'Kölked');
INSERT INTO `varosok` VALUES (1696,7718,2,12,'Udvar');
INSERT INTO `varosok` VALUES (1697,7720,2,12,'Pécsvárad');
INSERT INTO `varosok` VALUES (1698,7723,2,12,'Erdősmecske');
INSERT INTO `varosok` VALUES (1699,7724,2,12,'Feked');
INSERT INTO `varosok` VALUES (1700,7725,2,12,'Szebény');
INSERT INTO `varosok` VALUES (1701,7726,2,12,'Véménd');
INSERT INTO `varosok` VALUES (1702,7727,2,12,'Palotabozsok');
INSERT INTO `varosok` VALUES (1703,7728,2,12,'Somberek');
INSERT INTO `varosok` VALUES (1704,7731,2,12,'Nagypall');
INSERT INTO `varosok` VALUES (1705,7732,2,12,'Fazekasboda');
INSERT INTO `varosok` VALUES (1706,7733,2,12,'Geresdlak');
INSERT INTO `varosok` VALUES (1707,7735,2,12,'Hímesháza');
INSERT INTO `varosok` VALUES (1708,7737,2,12,'Székelyszabar');
INSERT INTO `varosok` VALUES (1709,7741,2,12,'Nagykozár');
INSERT INTO `varosok` VALUES (1710,7744,2,12,'Ellend');
INSERT INTO `varosok` VALUES (1711,7745,2,12,'Olasz');
INSERT INTO `varosok` VALUES (1712,7747,2,12,'Belvárdgyula');
INSERT INTO `varosok` VALUES (1713,7751,2,12,'Szederkény');
INSERT INTO `varosok` VALUES (1714,7752,2,12,'Versend');
INSERT INTO `varosok` VALUES (1715,7753,2,12,'Szajk');
INSERT INTO `varosok` VALUES (1716,7754,2,12,'Bóly');
INSERT INTO `varosok` VALUES (1717,7755,2,12,'Töttös');
INSERT INTO `varosok` VALUES (1718,7756,2,12,'Borjád');
INSERT INTO `varosok` VALUES (1719,7757,2,12,'Babarc');
INSERT INTO `varosok` VALUES (1720,7759,2,12,'Lánycsók');
INSERT INTO `varosok` VALUES (1721,7761,2,12,'Kozármisleny');
INSERT INTO `varosok` VALUES (1722,7762,2,12,'Pécsudvard');
INSERT INTO `varosok` VALUES (1723,7763,2,12,'Egerág');
INSERT INTO `varosok` VALUES (1724,7766,2,12,'Újpetre');
INSERT INTO `varosok` VALUES (1725,7768,2,12,'Vokány');
INSERT INTO `varosok` VALUES (1726,7771,2,12,'Palkonya');
INSERT INTO `varosok` VALUES (1727,7772,2,12,'Villánykövesd');
INSERT INTO `varosok` VALUES (1728,7773,2,12,'Villány');
INSERT INTO `varosok` VALUES (1729,7774,2,12,'Márok');
INSERT INTO `varosok` VALUES (1730,7775,2,12,'Magyarbóly');
INSERT INTO `varosok` VALUES (1731,7781,2,12,'Lippó');
INSERT INTO `varosok` VALUES (1732,7782,2,12,'Bezedek');
INSERT INTO `varosok` VALUES (1733,7783,2,12,'Majs');
INSERT INTO `varosok` VALUES (1734,7784,2,12,'Nagynyárád');
INSERT INTO `varosok` VALUES (1735,7785,2,12,'Sátorhely');
INSERT INTO `varosok` VALUES (1736,7800,2,12,'Siklós');
INSERT INTO `varosok` VALUES (1737,7811,2,12,'Szalánta');
INSERT INTO `varosok` VALUES (1738,7812,2,12,'Garé');
INSERT INTO `varosok` VALUES (1739,7813,2,12,'Szava');
INSERT INTO `varosok` VALUES (1740,7814,2,12,'Ócsárd');
INSERT INTO `varosok` VALUES (1741,7815,2,12,'Harkány');
INSERT INTO `varosok` VALUES (1742,7817,2,12,'Diósviszló');
INSERT INTO `varosok` VALUES (1743,7822,2,12,'Nagyharsány');
INSERT INTO `varosok` VALUES (1744,7823,2,12,'Siklósnagyfalu');
INSERT INTO `varosok` VALUES (1745,7824,2,12,'Egyházasharaszti');
INSERT INTO `varosok` VALUES (1746,7826,2,12,'Alsószentmárton');
INSERT INTO `varosok` VALUES (1747,7827,2,12,'Beremend');
INSERT INTO `varosok` VALUES (1748,7831,2,12,'Pellérd');
INSERT INTO `varosok` VALUES (1749,7833,2,12,'Görcsöny');
INSERT INTO `varosok` VALUES (1750,7834,2,12,'Baksa');
INSERT INTO `varosok` VALUES (1751,7836,2,12,'Bogádmindszent');
INSERT INTO `varosok` VALUES (1752,7837,2,12,'Hegyhátszentmárton');
INSERT INTO `varosok` VALUES (1753,7838,2,12,'Vajszló');
INSERT INTO `varosok` VALUES (1754,7839,2,12,'Zaláta');
INSERT INTO `varosok` VALUES (1755,7841,2,12,'Sámod');
INSERT INTO `varosok` VALUES (1756,7843,2,12,'Kémes');
INSERT INTO `varosok` VALUES (1757,7846,2,12,'Drávacsepely');
INSERT INTO `varosok` VALUES (1758,7847,2,12,'Kovácshida');
INSERT INTO `varosok` VALUES (1759,7851,2,12,'Drávaszabolcs');
INSERT INTO `varosok` VALUES (1760,7853,2,12,'Gordisa');
INSERT INTO `varosok` VALUES (1761,7854,2,12,'Matty');
INSERT INTO `varosok` VALUES (1762,7900,2,12,'Szigetvár');
INSERT INTO `varosok` VALUES (1763,7912,2,12,'Nagypeterd');
INSERT INTO `varosok` VALUES (1764,7913,2,12,'Szentdénes');
INSERT INTO `varosok` VALUES (1765,7914,2,12,'Rózsafa');
INSERT INTO `varosok` VALUES (1766,7915,2,12,'Dencsháza');
INSERT INTO `varosok` VALUES (1767,7918,15,12,'Lakócsa');
INSERT INTO `varosok` VALUES (1768,7921,2,12,'Somogyhatvan');
INSERT INTO `varosok` VALUES (1769,7922,2,12,'Somogyapáti');
INSERT INTO `varosok` VALUES (1770,7925,2,12,'Somogyhárságy');
INSERT INTO `varosok` VALUES (1771,7926,2,12,'Vásárosbéc');
INSERT INTO `varosok` VALUES (1772,7932,2,12,'Mozsgó');
INSERT INTO `varosok` VALUES (1773,7934,2,12,'Almamellék');
INSERT INTO `varosok` VALUES (1774,7935,2,12,'Ibafa');
INSERT INTO `varosok` VALUES (1775,7936,2,12,'Szentlászló');
INSERT INTO `varosok` VALUES (1776,7937,2,12,'Boldogasszonyfa');
INSERT INTO `varosok` VALUES (1777,7940,2,12,'Szentlőrinc');
INSERT INTO `varosok` VALUES (1778,7951,2,12,'Szabadszentkirály');
INSERT INTO `varosok` VALUES (1779,7953,2,12,'Királyegyháza');
INSERT INTO `varosok` VALUES (1780,7954,2,12,'Magyarmecske');
INSERT INTO `varosok` VALUES (1781,7957,2,12,'Okorág');
INSERT INTO `varosok` VALUES (1782,7958,2,12,'Kákics');
INSERT INTO `varosok` VALUES (1783,7960,2,12,'Sellye');
INSERT INTO `varosok` VALUES (1784,7964,2,12,'Csányoszró');
INSERT INTO `varosok` VALUES (1785,7966,2,12,'Bogdása');
INSERT INTO `varosok` VALUES (1786,7967,2,12,'Drávafok');
INSERT INTO `varosok` VALUES (1787,7968,2,12,'Felsőszentmárton');
INSERT INTO `varosok` VALUES (1788,7971,2,12,'Hobol');
INSERT INTO `varosok` VALUES (1789,7972,2,12,'Gyöngyösmellék');
INSERT INTO `varosok` VALUES (1790,7973,2,12,'Teklafalu');
INSERT INTO `varosok` VALUES (1791,7975,2,12,'Kétújfalu');
INSERT INTO `varosok` VALUES (1792,7976,2,12,'Zádor');
INSERT INTO `varosok` VALUES (1793,7977,15,12,'Kastélyosdombó');
INSERT INTO `varosok` VALUES (1794,7979,15,12,'Drávatamási');
INSERT INTO `varosok` VALUES (1795,7981,2,12,'Nemeske');
INSERT INTO `varosok` VALUES (1796,7985,2,12,'Nagydobsza');
INSERT INTO `varosok` VALUES (1797,7987,15,12,'Istvándi');
INSERT INTO `varosok` VALUES (1798,7988,15,12,'Darány');
INSERT INTO `varosok` VALUES (1799,8000,7,12,'Székesfehérvár');
INSERT INTO `varosok` VALUES (1800,8041,7,12,'Csór');
INSERT INTO `varosok` VALUES (1801,8042,7,12,'Moha');
INSERT INTO `varosok` VALUES (1802,8043,7,12,'Iszkaszentgyörgy');
INSERT INTO `varosok` VALUES (1803,8044,7,12,'Kincsesbánya');
INSERT INTO `varosok` VALUES (1804,8045,7,12,'Isztimér');
INSERT INTO `varosok` VALUES (1805,8046,7,12,'Bakonykúti');
INSERT INTO `varosok` VALUES (1806,8051,7,12,'Sárkeresztes');
INSERT INTO `varosok` VALUES (1807,8052,7,12,'Fehérvárcsurgó');
INSERT INTO `varosok` VALUES (1808,8053,7,12,'Bodajk');
INSERT INTO `varosok` VALUES (1809,8055,7,12,'Balinka');
INSERT INTO `varosok` VALUES (1810,8056,7,12,'Bakonycsernye');
INSERT INTO `varosok` VALUES (1811,8060,7,12,'Mór');
INSERT INTO `varosok` VALUES (1812,8065,7,12,'Nagyveleg');
INSERT INTO `varosok` VALUES (1813,8066,7,12,'Pusztavám');
INSERT INTO `varosok` VALUES (1814,8071,7,12,'Magyaralmás');
INSERT INTO `varosok` VALUES (1815,8072,7,12,'Söréd');
INSERT INTO `varosok` VALUES (1816,8073,7,12,'Csákberény');
INSERT INTO `varosok` VALUES (1817,8074,7,12,'Csókakő');
INSERT INTO `varosok` VALUES (1818,8080,7,12,'Bodmér');
INSERT INTO `varosok` VALUES (1819,8081,7,12,'Zámoly');
INSERT INTO `varosok` VALUES (1820,8082,7,12,'Gánt');
INSERT INTO `varosok` VALUES (1821,8083,7,12,'Csákvár');
INSERT INTO `varosok` VALUES (1822,8085,7,12,'Vértesboglár');
INSERT INTO `varosok` VALUES (1823,8086,7,12,'Felcsút');
INSERT INTO `varosok` VALUES (1824,8087,7,12,'Alcsútdoboz');
INSERT INTO `varosok` VALUES (1825,8088,7,12,'Tabajd');
INSERT INTO `varosok` VALUES (1826,8089,7,12,'Vértesacsa');
INSERT INTO `varosok` VALUES (1827,8092,7,12,'Pátka');
INSERT INTO `varosok` VALUES (1828,8093,7,12,'Lovasberény');
INSERT INTO `varosok` VALUES (1829,8095,7,12,'Pákozd');
INSERT INTO `varosok` VALUES (1830,8096,7,12,'Sukoró');
INSERT INTO `varosok` VALUES (1831,8097,7,12,'Nadap');
INSERT INTO `varosok` VALUES (1832,8100,19,12,'Várpalota');
INSERT INTO `varosok` VALUES (1833,8105,19,12,'Pétfürdő');
INSERT INTO `varosok` VALUES (1834,8109,19,12,'Tés');
INSERT INTO `varosok` VALUES (1835,8111,7,12,'Seregélyes');
INSERT INTO `varosok` VALUES (1836,8112,7,12,'Zichyújfalu');
INSERT INTO `varosok` VALUES (1837,8121,7,12,'Tác');
INSERT INTO `varosok` VALUES (1838,8122,7,12,'Csősz');
INSERT INTO `varosok` VALUES (1839,8123,7,12,'Soponya');
INSERT INTO `varosok` VALUES (1840,8124,7,12,'Káloz');
INSERT INTO `varosok` VALUES (1841,8125,7,12,'Sárkeresztúr');
INSERT INTO `varosok` VALUES (1842,8126,7,12,'Sárszentágota');
INSERT INTO `varosok` VALUES (1843,8127,7,12,'Aba');
INSERT INTO `varosok` VALUES (1844,8130,7,12,'Enying');
INSERT INTO `varosok` VALUES (1845,8132,7,12,'Lepsény');
INSERT INTO `varosok` VALUES (1846,8133,7,12,'Mezőszentgyörgy');
INSERT INTO `varosok` VALUES (1847,8134,7,12,'Mátyásdomb');
INSERT INTO `varosok` VALUES (1848,8135,7,12,'Dég');
INSERT INTO `varosok` VALUES (1849,8136,7,12,'Lajoskomárom');
INSERT INTO `varosok` VALUES (1850,8137,7,12,'Mezőkomárom');
INSERT INTO `varosok` VALUES (1851,8138,7,12,'Szabadhidvég');
INSERT INTO `varosok` VALUES (1852,8142,7,12,'Úrhida');
INSERT INTO `varosok` VALUES (1853,8143,7,12,'Sárszentmihály');
INSERT INTO `varosok` VALUES (1854,8144,7,12,'Sárkeszi');
INSERT INTO `varosok` VALUES (1855,8145,7,12,'Nádasdladány');
INSERT INTO `varosok` VALUES (1856,8146,7,12,'Jenő');
INSERT INTO `varosok` VALUES (1857,8151,7,12,'Szabadbattyán');
INSERT INTO `varosok` VALUES (1858,8152,7,12,'Kőszárhegy');
INSERT INTO `varosok` VALUES (1859,8154,7,12,'Polgárdi');
INSERT INTO `varosok` VALUES (1860,8156,7,12,'Kisláng');
INSERT INTO `varosok` VALUES (1861,8157,7,12,'Füle');
INSERT INTO `varosok` VALUES (1862,8161,19,12,'Ősi');
INSERT INTO `varosok` VALUES (1863,8162,19,12,'Küngös');
INSERT INTO `varosok` VALUES (1864,8163,19,12,'Csajág');
INSERT INTO `varosok` VALUES (1865,8164,19,12,'Balatonfőkajár');
INSERT INTO `varosok` VALUES (1866,8171,19,12,'Balatonvilágos');
INSERT INTO `varosok` VALUES (1867,8174,19,12,'Balatonkenese');
INSERT INTO `varosok` VALUES (1868,8175,19,12,'Balatonfűzfő');
INSERT INTO `varosok` VALUES (1869,8181,19,12,'Berhida');
INSERT INTO `varosok` VALUES (1870,8183,19,12,'Papkeszi');
INSERT INTO `varosok` VALUES (1871,8191,19,12,'Öskü');
INSERT INTO `varosok` VALUES (1872,8192,19,12,'Hajmáskér');
INSERT INTO `varosok` VALUES (1873,8193,19,12,'Sóly');
INSERT INTO `varosok` VALUES (1874,8194,19,12,'Vilonya');
INSERT INTO `varosok` VALUES (1875,8195,19,12,'Királyszentistván');
INSERT INTO `varosok` VALUES (1876,8196,19,12,'Litér');
INSERT INTO `varosok` VALUES (1877,8200,19,12,'Veszprém');
INSERT INTO `varosok` VALUES (1878,8220,19,12,'Balatonalmádi');
INSERT INTO `varosok` VALUES (1879,8225,19,12,'Szentkirályszabadja');
INSERT INTO `varosok` VALUES (1880,8226,19,12,'Alsóörs');
INSERT INTO `varosok` VALUES (1881,8227,19,12,'Felsőörs');
INSERT INTO `varosok` VALUES (1882,8228,19,12,'Lovas');
INSERT INTO `varosok` VALUES (1883,8229,19,12,'Csopak');
INSERT INTO `varosok` VALUES (1884,8230,19,12,'Balatonfüred');
INSERT INTO `varosok` VALUES (1885,8237,19,12,'Tihany');
INSERT INTO `varosok` VALUES (1886,8241,19,12,'Aszófő');
INSERT INTO `varosok` VALUES (1887,8242,19,12,'Balatonudvari');
INSERT INTO `varosok` VALUES (1888,8243,19,12,'Balatonakali');
INSERT INTO `varosok` VALUES (1889,8244,19,12,'Dörgicse');
INSERT INTO `varosok` VALUES (1890,8245,19,12,'Pécsely');
INSERT INTO `varosok` VALUES (1891,8246,19,12,'Tótvázsony');
INSERT INTO `varosok` VALUES (1892,8247,19,12,'Hidegkút');
INSERT INTO `varosok` VALUES (1893,8248,19,12,'Nemesvámos');
INSERT INTO `varosok` VALUES (1894,8251,19,12,'Zánka');
INSERT INTO `varosok` VALUES (1895,8252,19,12,'Balatonszepezd');
INSERT INTO `varosok` VALUES (1896,8253,19,12,'Révfülöp');
INSERT INTO `varosok` VALUES (1897,8254,19,12,'Kővágóörs');
INSERT INTO `varosok` VALUES (1898,8255,19,12,'Balatonrendes');
INSERT INTO `varosok` VALUES (1899,8256,19,12,'Ábrahámhegy');
INSERT INTO `varosok` VALUES (1900,8258,19,12,'Badacsonytomaj');
INSERT INTO `varosok` VALUES (1901,8263,19,12,'Badacsonytördemic');
INSERT INTO `varosok` VALUES (1902,8264,19,12,'Szigliget');
INSERT INTO `varosok` VALUES (1903,8265,19,12,'Hegymagas');
INSERT INTO `varosok` VALUES (1904,8271,19,12,'Mencshely');
INSERT INTO `varosok` VALUES (1905,8272,19,12,'Szentantalfa');
INSERT INTO `varosok` VALUES (1906,8273,19,12,'Monoszló');
INSERT INTO `varosok` VALUES (1907,8274,19,12,'Köveskál');
INSERT INTO `varosok` VALUES (1908,8275,19,12,'Balatonhenye');
INSERT INTO `varosok` VALUES (1909,8281,19,12,'Szentbékkálla');
INSERT INTO `varosok` VALUES (1910,8282,19,12,'Mindszentkálla');
INSERT INTO `varosok` VALUES (1911,8283,19,12,'Káptalantóti');
INSERT INTO `varosok` VALUES (1912,8284,19,12,'Nemesgulács');
INSERT INTO `varosok` VALUES (1913,8286,19,12,'Gyulakeszi');
INSERT INTO `varosok` VALUES (1914,8291,19,12,'Nagyvázsony');
INSERT INTO `varosok` VALUES (1915,8292,19,12,'Öcs');
INSERT INTO `varosok` VALUES (1916,8294,19,12,'Kapolcs');
INSERT INTO `varosok` VALUES (1917,8295,19,12,'Taliándörögd');
INSERT INTO `varosok` VALUES (1918,8296,19,12,'Monostorapáti');
INSERT INTO `varosok` VALUES (1919,8300,19,12,'Tapolca');
INSERT INTO `varosok` VALUES (1920,8308,19,12,'Zalahaláp');
INSERT INTO `varosok` VALUES (1921,8311,19,12,'Nemesvita');
INSERT INTO `varosok` VALUES (1922,8312,19,12,'Balatonederics');
INSERT INTO `varosok` VALUES (1923,8313,20,12,'Balatongyörök');
INSERT INTO `varosok` VALUES (1924,8314,20,12,'Vonyarcvashegy');
INSERT INTO `varosok` VALUES (1925,8315,20,12,'Gyenesdiás');
INSERT INTO `varosok` VALUES (1926,8316,20,12,'Várvölgy');
INSERT INTO `varosok` VALUES (1927,8318,19,12,'Lesencetomaj');
INSERT INTO `varosok` VALUES (1928,8319,19,12,'Lesenceistvánd');
INSERT INTO `varosok` VALUES (1929,8321,19,12,'Uzsa');
INSERT INTO `varosok` VALUES (1930,8330,19,12,'Sümeg');
INSERT INTO `varosok` VALUES (1931,8341,20,12,'Mihályfa');
INSERT INTO `varosok` VALUES (1932,8342,20,12,'Óhid');
INSERT INTO `varosok` VALUES (1933,8344,19,12,'Zalaerdőd');
INSERT INTO `varosok` VALUES (1934,8345,19,12,'Dabronc');
INSERT INTO `varosok` VALUES (1935,8346,19,12,'Gógánfa');
INSERT INTO `varosok` VALUES (1936,8347,19,12,'Ukk');
INSERT INTO `varosok` VALUES (1937,8348,19,12,'Rigács');
INSERT INTO `varosok` VALUES (1938,8349,19,12,'Zalagyömörő');
INSERT INTO `varosok` VALUES (1939,8351,19,12,'Sümegprága');
INSERT INTO `varosok` VALUES (1940,8353,19,12,'Bazsi');
INSERT INTO `varosok` VALUES (1941,8353,20,12,'Zalaszántó');
INSERT INTO `varosok` VALUES (1942,8354,20,12,'Karmacs');
INSERT INTO `varosok` VALUES (1943,8355,20,12,'Vindornyaszőlős');
INSERT INTO `varosok` VALUES (1944,8356,20,12,'Kisgörbő');
INSERT INTO `varosok` VALUES (1945,8357,20,12,'Sümegcsehi');
INSERT INTO `varosok` VALUES (1946,8360,20,12,'Keszthely');
INSERT INTO `varosok` VALUES (1947,8371,20,12,'Nemesbük');
INSERT INTO `varosok` VALUES (1948,8372,20,12,'Cserszegtomaj');
INSERT INTO `varosok` VALUES (1949,8373,20,12,'Rezi');
INSERT INTO `varosok` VALUES (1950,8380,20,12,'Hévíz');
INSERT INTO `varosok` VALUES (1951,8391,20,12,'Sármellék');
INSERT INTO `varosok` VALUES (1952,8392,20,12,'Zalavár');
INSERT INTO `varosok` VALUES (1953,8393,20,12,'Szentgyörgyvár');
INSERT INTO `varosok` VALUES (1954,8394,20,12,'Alsópáhok');
INSERT INTO `varosok` VALUES (1955,8400,19,12,'Ajka');
INSERT INTO `varosok` VALUES (1956,8409,19,12,'Úrkút');
INSERT INTO `varosok` VALUES (1957,8413,19,12,'Eplény');
INSERT INTO `varosok` VALUES (1958,8414,19,12,'Olaszfalu');
INSERT INTO `varosok` VALUES (1959,8415,19,12,'Nagyesztergár');
INSERT INTO `varosok` VALUES (1960,8416,19,12,'Dudar');
INSERT INTO `varosok` VALUES (1961,8417,19,12,'Csetény');
INSERT INTO `varosok` VALUES (1962,8418,19,12,'Bakonyoszlop');
INSERT INTO `varosok` VALUES (1963,8419,19,12,'Csesznek');
INSERT INTO `varosok` VALUES (1964,8420,19,12,'Zirc');
INSERT INTO `varosok` VALUES (1965,8422,19,12,'Bakonynána');
INSERT INTO `varosok` VALUES (1966,8423,19,12,'Szápár');
INSERT INTO `varosok` VALUES (1967,8424,19,12,'Jásd');
INSERT INTO `varosok` VALUES (1968,8425,19,12,'Lókút');
INSERT INTO `varosok` VALUES (1969,8426,19,12,'Pénzesgyőr');
INSERT INTO `varosok` VALUES (1970,8427,19,12,'Bakonybél');
INSERT INTO `varosok` VALUES (1971,8428,19,12,'Borzavár');
INSERT INTO `varosok` VALUES (1972,8429,19,12,'Porva');
INSERT INTO `varosok` VALUES (1973,8430,19,12,'Bakonyszentkirály');
INSERT INTO `varosok` VALUES (1974,8431,19,12,'Bakonyszentlászló');
INSERT INTO `varosok` VALUES (1975,8432,19,12,'Fenyőfő');
INSERT INTO `varosok` VALUES (1976,8433,19,12,'Bakonygyirót');
INSERT INTO `varosok` VALUES (1977,8434,19,12,'Románd');
INSERT INTO `varosok` VALUES (1978,8435,19,12,'Gic');
INSERT INTO `varosok` VALUES (1979,8436,19,12,'Bakonypéterd');
INSERT INTO `varosok` VALUES (1980,8437,19,12,'Lázi');
INSERT INTO `varosok` VALUES (1981,8438,19,12,'Veszprémvarsány');
INSERT INTO `varosok` VALUES (1982,8439,19,12,'Sikátor');
INSERT INTO `varosok` VALUES (1983,8440,19,12,'Herend');
INSERT INTO `varosok` VALUES (1984,8441,19,12,'Márkó');
INSERT INTO `varosok` VALUES (1985,8442,19,12,'Hárskút');
INSERT INTO `varosok` VALUES (1986,8443,19,12,'Bánd');
INSERT INTO `varosok` VALUES (1987,8444,19,12,'Szentgál');
INSERT INTO `varosok` VALUES (1988,8445,19,12,'Városlőd');
INSERT INTO `varosok` VALUES (1989,8446,19,12,'Kislőd');
INSERT INTO `varosok` VALUES (1990,8449,19,12,'Magyarpolány');
INSERT INTO `varosok` VALUES (1991,8452,19,12,'Halimba');
INSERT INTO `varosok` VALUES (1992,8454,19,12,'Nyirád');
INSERT INTO `varosok` VALUES (1993,8455,19,12,'Pusztamiske');
INSERT INTO `varosok` VALUES (1994,8456,19,12,'Noszlop');
INSERT INTO `varosok` VALUES (1995,8457,19,12,'Bakonypölöske');
INSERT INTO `varosok` VALUES (1996,8458,19,12,'Oroszi');
INSERT INTO `varosok` VALUES (1997,8460,19,12,'Devecser');
INSERT INTO `varosok` VALUES (1998,8468,19,12,'Kolontár');
INSERT INTO `varosok` VALUES (1999,8469,19,12,'Kamond');
INSERT INTO `varosok` VALUES (2000,8471,19,12,'Káptalanfa');
INSERT INTO `varosok` VALUES (2001,8473,19,12,'Gyepükaján');
INSERT INTO `varosok` VALUES (2002,8474,19,12,'Csabrendek');
INSERT INTO `varosok` VALUES (2003,8475,19,12,'Veszprémgalsa');
INSERT INTO `varosok` VALUES (2004,8476,19,12,'Zalaszegvár');
INSERT INTO `varosok` VALUES (2005,8477,19,12,'Tüskevár');
INSERT INTO `varosok` VALUES (2006,8478,19,12,'Somlójenő');
INSERT INTO `varosok` VALUES (2007,8479,19,12,'Borszörcsök');
INSERT INTO `varosok` VALUES (2008,8481,19,12,'Somlóvásárhely');
INSERT INTO `varosok` VALUES (2009,8482,19,12,'Doba');
INSERT INTO `varosok` VALUES (2010,8483,19,12,'Somlószőlős');
INSERT INTO `varosok` VALUES (2011,8484,19,12,'Nagyalásony');
INSERT INTO `varosok` VALUES (2012,8485,19,12,'Dabrony');
INSERT INTO `varosok` VALUES (2013,8491,19,12,'Karakószörcsök');
INSERT INTO `varosok` VALUES (2014,8492,19,12,'Kerta');
INSERT INTO `varosok` VALUES (2015,8493,19,12,'Iszkáz');
INSERT INTO `varosok` VALUES (2016,8494,19,12,'Kiscsősz');
INSERT INTO `varosok` VALUES (2017,8495,19,12,'Csögle');
INSERT INTO `varosok` VALUES (2018,8496,19,12,'Nagypirit');
INSERT INTO `varosok` VALUES (2019,8497,19,12,'Adorjánháza');
INSERT INTO `varosok` VALUES (2020,8500,19,12,'Pápa');
INSERT INTO `varosok` VALUES (2021,8512,19,12,'Nyárád');
INSERT INTO `varosok` VALUES (2022,8513,19,12,'Mihályháza');
INSERT INTO `varosok` VALUES (2023,8514,19,12,'Mezőlak');
INSERT INTO `varosok` VALUES (2024,8515,19,12,'Békás');
INSERT INTO `varosok` VALUES (2025,8516,19,12,'Kemeneshőgyész');
INSERT INTO `varosok` VALUES (2026,8517,19,12,'Magyargencs');
INSERT INTO `varosok` VALUES (2027,8518,19,12,'Kemenesszentpéter');
INSERT INTO `varosok` VALUES (2028,8521,19,12,'Nagyacsád');
INSERT INTO `varosok` VALUES (2029,8522,19,12,'Nemesgörzsöny');
INSERT INTO `varosok` VALUES (2030,8523,19,12,'Egyházaskesző');
INSERT INTO `varosok` VALUES (2031,8532,19,12,'Marcaltő');
INSERT INTO `varosok` VALUES (2032,8533,19,12,'Malomsok');
INSERT INTO `varosok` VALUES (2033,8534,8,12,'Csikvánd');
INSERT INTO `varosok` VALUES (2034,8541,19,12,'Takácsi');
INSERT INTO `varosok` VALUES (2035,8542,19,12,'Vaszar');
INSERT INTO `varosok` VALUES (2036,8543,19,12,'Gecse');
INSERT INTO `varosok` VALUES (2037,8544,8,12,'Szerecseny');
INSERT INTO `varosok` VALUES (2038,8545,8,12,'Gyarmat');
INSERT INTO `varosok` VALUES (2039,8551,19,12,'Nagygyimót');
INSERT INTO `varosok` VALUES (2040,8552,19,12,'Vanyola');
INSERT INTO `varosok` VALUES (2041,8553,19,12,'Lovászpatona');
INSERT INTO `varosok` VALUES (2042,8554,19,12,'Nagydém');
INSERT INTO `varosok` VALUES (2043,8555,19,12,'Bakonytamási');
INSERT INTO `varosok` VALUES (2044,8556,19,12,'Pápateszér');
INSERT INTO `varosok` VALUES (2045,8557,19,12,'Bakonyszentiván');
INSERT INTO `varosok` VALUES (2046,8558,19,12,'Csót');
INSERT INTO `varosok` VALUES (2047,8561,19,12,'Adásztevel');
INSERT INTO `varosok` VALUES (2048,8562,19,12,'Nagytevel');
INSERT INTO `varosok` VALUES (2049,8563,19,12,'Homokbödöge');
INSERT INTO `varosok` VALUES (2050,8564,19,12,'Ugod');
INSERT INTO `varosok` VALUES (2051,8565,19,12,'Béb');
INSERT INTO `varosok` VALUES (2052,8571,19,12,'Bakonykoppány');
INSERT INTO `varosok` VALUES (2053,8572,19,12,'Bakonyszücs');
INSERT INTO `varosok` VALUES (2054,8581,19,12,'Bakonyjákó');
INSERT INTO `varosok` VALUES (2055,8582,19,12,'Farkasgyepű');
INSERT INTO `varosok` VALUES (2056,8591,19,12,'Nóráp');
INSERT INTO `varosok` VALUES (2057,8592,19,12,'Dáka');
INSERT INTO `varosok` VALUES (2058,8593,19,12,'Pápadereske');
INSERT INTO `varosok` VALUES (2059,8594,19,12,'Pápasalamon');
INSERT INTO `varosok` VALUES (2060,8595,19,12,'Kup');
INSERT INTO `varosok` VALUES (2061,8596,19,12,'Pápakovácsi');
INSERT INTO `varosok` VALUES (2062,8597,19,12,'Ganna');
INSERT INTO `varosok` VALUES (2063,8600,15,12,'Siófok');
INSERT INTO `varosok` VALUES (2064,8612,15,12,'Nyim');
INSERT INTO `varosok` VALUES (2065,8613,15,12,'Balatonendréd');
INSERT INTO `varosok` VALUES (2066,8614,15,12,'Bálványos');
INSERT INTO `varosok` VALUES (2067,8617,15,12,'Kőröshegy');
INSERT INTO `varosok` VALUES (2068,8618,15,12,'Kereki');
INSERT INTO `varosok` VALUES (2069,8619,15,12,'Pusztaszemes');
INSERT INTO `varosok` VALUES (2070,8621,15,12,'Zamárdi');
INSERT INTO `varosok` VALUES (2071,8622,15,12,'Szántód');
INSERT INTO `varosok` VALUES (2072,8623,15,12,'Balatonföldvár');
INSERT INTO `varosok` VALUES (2073,8624,15,12,'Balatonszárszó');
INSERT INTO `varosok` VALUES (2074,8625,15,12,'Szólád');
INSERT INTO `varosok` VALUES (2075,8626,15,12,'Teleki');
INSERT INTO `varosok` VALUES (2076,8627,15,12,'Kötcse');
INSERT INTO `varosok` VALUES (2077,8628,15,12,'Nagycsepely');
INSERT INTO `varosok` VALUES (2078,8630,15,12,'Balatonboglár');
INSERT INTO `varosok` VALUES (2079,8635,15,12,'Ordacsehi');
INSERT INTO `varosok` VALUES (2080,8636,15,12,'Balatonszemes');
INSERT INTO `varosok` VALUES (2081,8637,15,12,'Balatonőszöd');
INSERT INTO `varosok` VALUES (2082,8638,15,12,'Balatonlelle');
INSERT INTO `varosok` VALUES (2083,8640,15,12,'Fonyód');
INSERT INTO `varosok` VALUES (2084,8646,15,12,'Balatonfenyves');
INSERT INTO `varosok` VALUES (2085,8647,15,12,'Balatonmáriafürdő');
INSERT INTO `varosok` VALUES (2086,8649,15,12,'Balatonberény');
INSERT INTO `varosok` VALUES (2087,8651,15,12,'Balatonszabadi');
INSERT INTO `varosok` VALUES (2088,8652,15,12,'Siójut');
INSERT INTO `varosok` VALUES (2089,8653,15,12,'Ádánd');
INSERT INTO `varosok` VALUES (2090,8654,15,12,'Ságvár');
INSERT INTO `varosok` VALUES (2091,8655,15,12,'Som');
INSERT INTO `varosok` VALUES (2092,8656,15,12,'Nagyberény');
INSERT INTO `varosok` VALUES (2093,8658,15,12,'Bábonymegyer');
INSERT INTO `varosok` VALUES (2094,8660,15,12,'Tab');
INSERT INTO `varosok` VALUES (2095,8666,15,12,'Bedegkér');
INSERT INTO `varosok` VALUES (2096,8667,15,12,'Kánya');
INSERT INTO `varosok` VALUES (2097,8668,15,12,'Tengőd');
INSERT INTO `varosok` VALUES (2098,8671,15,12,'Kapoly');
INSERT INTO `varosok` VALUES (2099,8672,15,12,'Zics');
INSERT INTO `varosok` VALUES (2100,8673,15,12,'Somogymeggyes');
INSERT INTO `varosok` VALUES (2101,8674,15,12,'Nágocs');
INSERT INTO `varosok` VALUES (2102,8675,15,12,'Andocs');
INSERT INTO `varosok` VALUES (2103,8676,15,12,'Karád');
INSERT INTO `varosok` VALUES (2104,8681,15,12,'Látrány');
INSERT INTO `varosok` VALUES (2105,8683,15,12,'Somogytúr');
INSERT INTO `varosok` VALUES (2106,8684,15,12,'Somogybabod');
INSERT INTO `varosok` VALUES (2107,8685,15,12,'Gamás');
INSERT INTO `varosok` VALUES (2108,8692,15,12,'Szőlősgyörök');
INSERT INTO `varosok` VALUES (2109,8693,15,12,'Lengyeltóti');
INSERT INTO `varosok` VALUES (2110,8694,15,12,'Hács');
INSERT INTO `varosok` VALUES (2111,8695,15,12,'Buzsák');
INSERT INTO `varosok` VALUES (2112,8696,15,12,'Táska');
INSERT INTO `varosok` VALUES (2113,8697,15,12,'Öreglak');
INSERT INTO `varosok` VALUES (2114,8698,15,12,'Somogyvár');
INSERT INTO `varosok` VALUES (2115,8699,15,12,'Somogyvámos');
INSERT INTO `varosok` VALUES (2116,8700,15,12,'Marcali');
INSERT INTO `varosok` VALUES (2117,8705,15,12,'Somogyszentpál');
INSERT INTO `varosok` VALUES (2118,8706,15,12,'Nikla');
INSERT INTO `varosok` VALUES (2119,8707,15,12,'Pusztakovácsi');
INSERT INTO `varosok` VALUES (2120,8708,15,12,'Somogyfajsz');
INSERT INTO `varosok` VALUES (2121,8710,15,12,'Balatonszentgyörgy');
INSERT INTO `varosok` VALUES (2122,8711,15,12,'Vörs');
INSERT INTO `varosok` VALUES (2123,8712,15,12,'Balatonújlak');
INSERT INTO `varosok` VALUES (2124,8713,15,12,'Kéthely');
INSERT INTO `varosok` VALUES (2125,8714,15,12,'Kelevíz');
INSERT INTO `varosok` VALUES (2126,8716,15,12,'Mesztegnyő');
INSERT INTO `varosok` VALUES (2127,8717,15,12,'Szenyér');
INSERT INTO `varosok` VALUES (2128,8718,15,12,'Tapsony');
INSERT INTO `varosok` VALUES (2129,8719,15,12,'Böhönye');
INSERT INTO `varosok` VALUES (2130,8721,15,12,'Vése');
INSERT INTO `varosok` VALUES (2131,8722,15,12,'Nemesdéd');
INSERT INTO `varosok` VALUES (2132,8723,15,12,'Varászló');
INSERT INTO `varosok` VALUES (2133,8724,15,12,'Inke');
INSERT INTO `varosok` VALUES (2134,8725,15,12,'Iharosberény');
INSERT INTO `varosok` VALUES (2135,8726,15,12,'Iharos');
INSERT INTO `varosok` VALUES (2136,8728,15,12,'Pogányszentpéter');
INSERT INTO `varosok` VALUES (2137,8731,15,12,'Hollád');
INSERT INTO `varosok` VALUES (2138,8732,15,12,'Sávoly');
INSERT INTO `varosok` VALUES (2139,8733,15,12,'Somogysámson');
INSERT INTO `varosok` VALUES (2140,8734,15,12,'Somogyzsitfa');
INSERT INTO `varosok` VALUES (2141,8735,15,12,'Csákány');
INSERT INTO `varosok` VALUES (2142,8736,15,12,'Szőkedencs');
INSERT INTO `varosok` VALUES (2143,8737,15,12,'Somogysimonyi');
INSERT INTO `varosok` VALUES (2144,8738,15,12,'Nemesvid');
INSERT INTO `varosok` VALUES (2145,8739,15,12,'Nagyszakácsi');
INSERT INTO `varosok` VALUES (2146,8741,20,12,'Zalaapáti');
INSERT INTO `varosok` VALUES (2147,8742,20,12,'Esztergályhorváti');
INSERT INTO `varosok` VALUES (2148,8743,20,12,'Zalaszabar');
INSERT INTO `varosok` VALUES (2149,8744,20,12,'Orosztony');
INSERT INTO `varosok` VALUES (2150,8745,20,12,'Kerecseny');
INSERT INTO `varosok` VALUES (2151,8746,20,12,'Nagyrada');
INSERT INTO `varosok` VALUES (2152,8747,20,12,'Garabonc');
INSERT INTO `varosok` VALUES (2153,8749,20,12,'Zalakaros');
INSERT INTO `varosok` VALUES (2154,8751,20,12,'Zalakomár');
INSERT INTO `varosok` VALUES (2155,8753,20,12,'Balatonmagyaród');
INSERT INTO `varosok` VALUES (2156,8754,20,12,'Galambok');
INSERT INTO `varosok` VALUES (2157,8756,20,12,'Nagyrécse');
INSERT INTO `varosok` VALUES (2158,8761,20,12,'Pacsa');
INSERT INTO `varosok` VALUES (2159,8762,20,12,'Szentpéterúr');
INSERT INTO `varosok` VALUES (2160,8764,20,12,'Dióskál');
INSERT INTO `varosok` VALUES (2161,8765,20,12,'Egeraracsa');
INSERT INTO `varosok` VALUES (2162,8767,20,12,'Felsőrajk');
INSERT INTO `varosok` VALUES (2163,8771,20,12,'Hahót');
INSERT INTO `varosok` VALUES (2164,8772,20,12,'Zalaszentbalázs');
INSERT INTO `varosok` VALUES (2165,8773,20,12,'Pölöskefő');
INSERT INTO `varosok` VALUES (2166,8774,20,12,'Gelse');
INSERT INTO `varosok` VALUES (2167,8776,20,12,'Magyarszerdahely');
INSERT INTO `varosok` VALUES (2168,8777,20,12,'Hosszúvölgy');
INSERT INTO `varosok` VALUES (2169,8778,20,12,'Újudvar');
INSERT INTO `varosok` VALUES (2170,8782,20,12,'Zalacsány');
INSERT INTO `varosok` VALUES (2171,8784,20,12,'Kehidakustány');
INSERT INTO `varosok` VALUES (2172,8785,20,12,'Kallósd');
INSERT INTO `varosok` VALUES (2173,8788,20,12,'Zalaszentlászló');
INSERT INTO `varosok` VALUES (2174,8790,20,12,'Zalaszentgrót');
INSERT INTO `varosok` VALUES (2175,8792,20,12,'Zalavég');
INSERT INTO `varosok` VALUES (2176,8796,20,12,'Türje');
INSERT INTO `varosok` VALUES (2177,8797,20,12,'Batyk');
INSERT INTO `varosok` VALUES (2178,8798,20,12,'Zalabér');
INSERT INTO `varosok` VALUES (2179,8799,20,12,'Pakod');
INSERT INTO `varosok` VALUES (2180,8800,20,12,'Nagykanizsa');
INSERT INTO `varosok` VALUES (2181,8821,20,12,'Nagybakónak');
INSERT INTO `varosok` VALUES (2182,8822,20,12,'Zalaújlak');
INSERT INTO `varosok` VALUES (2183,8824,20,12,'Sand');
INSERT INTO `varosok` VALUES (2184,8825,20,12,'Miháld');
INSERT INTO `varosok` VALUES (2185,8827,20,12,'Zalaszentjakab');
INSERT INTO `varosok` VALUES (2186,8831,20,12,'Liszó');
INSERT INTO `varosok` VALUES (2187,8834,20,12,'Murakeresztúr');
INSERT INTO `varosok` VALUES (2188,8835,20,12,'Fityeház');
INSERT INTO `varosok` VALUES (2189,8840,15,12,'Csurgó');
INSERT INTO `varosok` VALUES (2190,8849,15,12,'Szenta');
INSERT INTO `varosok` VALUES (2191,8851,15,12,'Gyékényes');
INSERT INTO `varosok` VALUES (2192,8852,15,12,'Zákány');
INSERT INTO `varosok` VALUES (2193,8853,15,12,'Zákányfalu');
INSERT INTO `varosok` VALUES (2194,8854,15,12,'Őrtilos');
INSERT INTO `varosok` VALUES (2195,8855,20,12,'Belezna');
INSERT INTO `varosok` VALUES (2196,8856,20,12,'Surd');
INSERT INTO `varosok` VALUES (2197,8858,15,12,'Porrog');
INSERT INTO `varosok` VALUES (2198,8861,20,12,'Szepetnek');
INSERT INTO `varosok` VALUES (2199,8862,20,12,'Semjénháza');
INSERT INTO `varosok` VALUES (2200,8863,20,12,'Molnári');
INSERT INTO `varosok` VALUES (2201,8864,20,12,'Tótszerdahely');
INSERT INTO `varosok` VALUES (2202,8865,20,12,'Tótszentmárton');
INSERT INTO `varosok` VALUES (2203,8866,20,12,'Becsehely');
INSERT INTO `varosok` VALUES (2204,8868,20,12,'Letenye');
INSERT INTO `varosok` VALUES (2205,8872,20,12,'Muraszemenye');
INSERT INTO `varosok` VALUES (2206,8873,20,12,'Csörnyeföld');
INSERT INTO `varosok` VALUES (2207,8874,20,12,'Dobri');
INSERT INTO `varosok` VALUES (2208,8876,20,12,'Tormafölde');
INSERT INTO `varosok` VALUES (2209,8877,20,12,'Tornyiszentmiklós');
INSERT INTO `varosok` VALUES (2210,8878,20,12,'Lovászi');
INSERT INTO `varosok` VALUES (2211,8879,20,12,'Szécsisziget');
INSERT INTO `varosok` VALUES (2212,8881,20,12,'Sormás');
INSERT INTO `varosok` VALUES (2213,8882,20,12,'Eszteregnye');
INSERT INTO `varosok` VALUES (2214,8883,20,12,'Rigyác');
INSERT INTO `varosok` VALUES (2215,8885,20,12,'Borsfa');
INSERT INTO `varosok` VALUES (2216,8886,20,12,'Oltárc');
INSERT INTO `varosok` VALUES (2217,8887,20,12,'Bázakerettye');
INSERT INTO `varosok` VALUES (2218,8888,20,12,'Lispeszentadorján');
INSERT INTO `varosok` VALUES (2219,8891,20,12,'Bánokszentgyörgy');
INSERT INTO `varosok` VALUES (2220,8893,20,12,'Szentliszló');
INSERT INTO `varosok` VALUES (2221,8895,20,12,'Pusztamagyaród');
INSERT INTO `varosok` VALUES (2222,8896,20,12,'Pusztaszentlászló');
INSERT INTO `varosok` VALUES (2223,8897,20,12,'Söjtör');
INSERT INTO `varosok` VALUES (2224,8900,20,12,'Zalaegerszeg');
INSERT INTO `varosok` VALUES (2225,8911,20,12,'Nagykutas');
INSERT INTO `varosok` VALUES (2226,8912,20,12,'Kispáli');
INSERT INTO `varosok` VALUES (2227,8913,20,12,'Egervár');
INSERT INTO `varosok` VALUES (2228,8914,20,12,'Vasboldogasszony');
INSERT INTO `varosok` VALUES (2229,8915,20,12,'Nemesrádó');
INSERT INTO `varosok` VALUES (2230,8917,20,12,'Milejszeg');
INSERT INTO `varosok` VALUES (2231,8918,20,12,'Csonkahegyhát');
INSERT INTO `varosok` VALUES (2232,8919,20,12,'Kustánszeg');
INSERT INTO `varosok` VALUES (2233,8921,20,12,'Zalaszentiván');
INSERT INTO `varosok` VALUES (2234,8923,20,12,'Nemesapáti');
INSERT INTO `varosok` VALUES (2235,8924,20,12,'Alsónemesapáti');
INSERT INTO `varosok` VALUES (2236,8925,20,12,'Búcsúszentlászló');
INSERT INTO `varosok` VALUES (2237,8929,20,12,'Pölöske');
INSERT INTO `varosok` VALUES (2238,8931,20,12,'Kemendollár');
INSERT INTO `varosok` VALUES (2239,8932,20,12,'Pókaszepetk');
INSERT INTO `varosok` VALUES (2240,8934,20,12,'Bezeréd');
INSERT INTO `varosok` VALUES (2241,8935,20,12,'Nagykapornak');
INSERT INTO `varosok` VALUES (2242,8936,20,12,'Zalaszentmihály');
INSERT INTO `varosok` VALUES (2243,8943,20,12,'Bocfölde');
INSERT INTO `varosok` VALUES (2244,8944,20,12,'Sárhida');
INSERT INTO `varosok` VALUES (2245,8945,20,12,'Bak');
INSERT INTO `varosok` VALUES (2246,8946,20,12,'Tófej');
INSERT INTO `varosok` VALUES (2247,8947,20,12,'Zalatárnok');
INSERT INTO `varosok` VALUES (2248,8948,20,12,'Nova');
INSERT INTO `varosok` VALUES (2249,8949,20,12,'Mikekarácsonyfa');
INSERT INTO `varosok` VALUES (2250,8951,20,12,'Gutorfölde');
INSERT INTO `varosok` VALUES (2251,8953,20,12,'Szentpéterfölde');
INSERT INTO `varosok` VALUES (2252,8954,20,12,'Ortaháza');
INSERT INTO `varosok` VALUES (2253,8956,20,12,'Páka');
INSERT INTO `varosok` VALUES (2254,8957,20,12,'Csömödér');
INSERT INTO `varosok` VALUES (2255,8958,20,12,'Iklódbördőce');
INSERT INTO `varosok` VALUES (2256,8960,20,12,'Lenti');
INSERT INTO `varosok` VALUES (2257,8969,20,12,'Gáborjánháza');
INSERT INTO `varosok` VALUES (2258,8971,20,12,'Zalabaksa');
INSERT INTO `varosok` VALUES (2259,8973,20,12,'Csesztreg');
INSERT INTO `varosok` VALUES (2260,8975,20,12,'Szentgyörgyvölgy');
INSERT INTO `varosok` VALUES (2261,8976,20,12,'Nemesnép');
INSERT INTO `varosok` VALUES (2262,8977,20,12,'Resznek');
INSERT INTO `varosok` VALUES (2263,8978,20,12,'Rédics');
INSERT INTO `varosok` VALUES (2264,8981,20,12,'Gellénháza');
INSERT INTO `varosok` VALUES (2265,8983,20,12,'Nagylengyel');
INSERT INTO `varosok` VALUES (2266,8984,20,12,'Petrikeresztúr');
INSERT INTO `varosok` VALUES (2267,8985,20,12,'Becsvölgye');
INSERT INTO `varosok` VALUES (2268,8986,20,12,'Pórszombat');
INSERT INTO `varosok` VALUES (2269,8988,20,12,'Kálócfa');
INSERT INTO `varosok` VALUES (2270,8991,20,12,'Teskánd');
INSERT INTO `varosok` VALUES (2271,8992,20,12,'Bagod');
INSERT INTO `varosok` VALUES (2272,8994,20,12,'Zalaszentgyörgy');
INSERT INTO `varosok` VALUES (2273,8995,20,12,'Salomvár');
INSERT INTO `varosok` VALUES (2274,8996,20,12,'Zalacséb');
INSERT INTO `varosok` VALUES (2275,8997,20,12,'Zalaháshágy');
INSERT INTO `varosok` VALUES (2276,8998,20,12,'Vaspör');
INSERT INTO `varosok` VALUES (2277,8999,20,12,'Zalalövő');
INSERT INTO `varosok` VALUES (2278,9061,8,12,'Vámosszabadi');
INSERT INTO `varosok` VALUES (2279,9062,8,12,'Kisbajcs');
INSERT INTO `varosok` VALUES (2280,9063,8,12,'Nagybajcs');
INSERT INTO `varosok` VALUES (2281,9071,8,12,'Gönyű');
INSERT INTO `varosok` VALUES (2282,9072,8,12,'Nagyszentjános');
INSERT INTO `varosok` VALUES (2283,9073,8,12,'Bőny');
INSERT INTO `varosok` VALUES (2284,9074,8,12,'Rétalap');
INSERT INTO `varosok` VALUES (2285,9081,8,12,'Győrújbarát');
INSERT INTO `varosok` VALUES (2286,9082,8,12,'Nyúl');
INSERT INTO `varosok` VALUES (2287,9083,8,12,'Écs');
INSERT INTO `varosok` VALUES (2288,9084,8,12,'Győrság');
INSERT INTO `varosok` VALUES (2289,9085,8,12,'Pázmándfalu');
INSERT INTO `varosok` VALUES (2290,9086,8,12,'Töltéstava');
INSERT INTO `varosok` VALUES (2291,9090,8,12,'Pannonhalma');
INSERT INTO `varosok` VALUES (2292,9091,8,12,'Ravazd');
INSERT INTO `varosok` VALUES (2293,9092,8,12,'Tarjánpuszta');
INSERT INTO `varosok` VALUES (2294,9093,8,12,'Győrasszonyfa');
INSERT INTO `varosok` VALUES (2295,9094,8,12,'Tápszentmiklós');
INSERT INTO `varosok` VALUES (2296,9095,8,12,'Táp');
INSERT INTO `varosok` VALUES (2297,9096,8,12,'Nyalka');
INSERT INTO `varosok` VALUES (2298,9097,8,12,'Mezőörs');
INSERT INTO `varosok` VALUES (2299,9099,8,12,'Pér');
INSERT INTO `varosok` VALUES (2300,9100,8,12,'Tét');
INSERT INTO `varosok` VALUES (2301,9111,8,12,'Tényő');
INSERT INTO `varosok` VALUES (2302,9112,8,12,'Sokorópátka');
INSERT INTO `varosok` VALUES (2303,9113,8,12,'Koroncó');
INSERT INTO `varosok` VALUES (2304,9121,8,12,'Győrszemere');
INSERT INTO `varosok` VALUES (2305,9122,8,12,'Felpéc');
INSERT INTO `varosok` VALUES (2306,9123,8,12,'Kajárpéc');
INSERT INTO `varosok` VALUES (2307,9124,8,12,'Gyömöre');
INSERT INTO `varosok` VALUES (2308,9131,8,12,'Mórichida');
INSERT INTO `varosok` VALUES (2309,9132,8,12,'Árpás');
INSERT INTO `varosok` VALUES (2310,9133,8,12,'Kisbabot');
INSERT INTO `varosok` VALUES (2311,9134,8,12,'Bodonhely');
INSERT INTO `varosok` VALUES (2312,9135,8,12,'Rábaszentmihály');
INSERT INTO `varosok` VALUES (2313,9136,8,12,'Rábacsécsény');
INSERT INTO `varosok` VALUES (2314,9141,8,12,'Ikrény');
INSERT INTO `varosok` VALUES (2315,9142,8,12,'Rábapatona');
INSERT INTO `varosok` VALUES (2316,9143,8,12,'Enese');
INSERT INTO `varosok` VALUES (2317,9144,8,12,'Kóny');
INSERT INTO `varosok` VALUES (2318,9145,8,12,'Bágyogszovát');
INSERT INTO `varosok` VALUES (2319,9146,8,12,'Rábapordány');
INSERT INTO `varosok` VALUES (2320,9147,8,12,'Dör');
INSERT INTO `varosok` VALUES (2321,9151,8,12,'Abda');
INSERT INTO `varosok` VALUES (2322,9152,8,12,'Börcs');
INSERT INTO `varosok` VALUES (2323,9153,8,12,'Öttevény');
INSERT INTO `varosok` VALUES (2324,9154,8,12,'Mosonszentmiklós');
INSERT INTO `varosok` VALUES (2325,9155,8,12,'Lébény');
INSERT INTO `varosok` VALUES (2326,9161,8,12,'Győrsövényház');
INSERT INTO `varosok` VALUES (2327,9162,8,12,'Bezi');
INSERT INTO `varosok` VALUES (2328,9163,8,12,'Fehértó');
INSERT INTO `varosok` VALUES (2329,9164,8,12,'Markotabödöge');
INSERT INTO `varosok` VALUES (2330,9165,8,12,'Rábcakapi');
INSERT INTO `varosok` VALUES (2331,9167,8,12,'Bősárkány');
INSERT INTO `varosok` VALUES (2332,9168,8,12,'Acsalag');
INSERT INTO `varosok` VALUES (2333,9169,8,12,'Barbacs');
INSERT INTO `varosok` VALUES (2334,9171,8,12,'Győrújfalu');
INSERT INTO `varosok` VALUES (2335,9172,8,12,'Győrzámoly');
INSERT INTO `varosok` VALUES (2336,9173,8,12,'Győrladamér');
INSERT INTO `varosok` VALUES (2337,9174,8,12,'Dunaszeg');
INSERT INTO `varosok` VALUES (2338,9175,8,12,'Dunaszentpál');
INSERT INTO `varosok` VALUES (2339,9176,8,12,'Mecsér');
INSERT INTO `varosok` VALUES (2340,9177,8,12,'Ásványráró');
INSERT INTO `varosok` VALUES (2341,9178,8,12,'Hédervár');
INSERT INTO `varosok` VALUES (2342,9181,8,12,'Kimle');
INSERT INTO `varosok` VALUES (2343,9182,8,12,'Károlyháza');
INSERT INTO `varosok` VALUES (2344,9184,8,12,'Kunsziget');
INSERT INTO `varosok` VALUES (2345,9200,8,12,'Mosonmagyaróvár');
INSERT INTO `varosok` VALUES (2346,9211,8,12,'Feketeerdő');
INSERT INTO `varosok` VALUES (2347,9221,8,12,'Levél');
INSERT INTO `varosok` VALUES (2348,9222,8,12,'Hegyeshalom');
INSERT INTO `varosok` VALUES (2349,9223,8,12,'Bezenye');
INSERT INTO `varosok` VALUES (2350,9224,8,12,'Rajka');
INSERT INTO `varosok` VALUES (2351,9225,8,12,'Dunakiliti');
INSERT INTO `varosok` VALUES (2352,9226,8,12,'Dunasziget');
INSERT INTO `varosok` VALUES (2353,9228,8,12,'Halászi');
INSERT INTO `varosok` VALUES (2354,9231,8,12,'Máriakálnok');
INSERT INTO `varosok` VALUES (2355,9232,8,12,'Darnózseli');
INSERT INTO `varosok` VALUES (2356,9233,8,12,'Lipót');
INSERT INTO `varosok` VALUES (2357,9234,8,12,'Kisbodak');
INSERT INTO `varosok` VALUES (2358,9235,8,12,'Püski');
INSERT INTO `varosok` VALUES (2359,9241,8,12,'Jánossomorja');
INSERT INTO `varosok` VALUES (2360,9243,8,12,'Várbalog');
INSERT INTO `varosok` VALUES (2361,9244,8,12,'Újrónafő');
INSERT INTO `varosok` VALUES (2362,9245,8,12,'Mosonszolnok');
INSERT INTO `varosok` VALUES (2363,9300,8,12,'Csorna');
INSERT INTO `varosok` VALUES (2364,9311,8,12,'Pásztori');
INSERT INTO `varosok` VALUES (2365,9312,8,12,'Szilsárkány');
INSERT INTO `varosok` VALUES (2366,9313,8,12,'Rábacsanak');
INSERT INTO `varosok` VALUES (2367,9314,8,12,'Egyed');
INSERT INTO `varosok` VALUES (2368,9315,8,12,'Sobor');
INSERT INTO `varosok` VALUES (2369,9316,8,12,'Rábaszentandrás');
INSERT INTO `varosok` VALUES (2370,9317,8,12,'Szany');
INSERT INTO `varosok` VALUES (2371,9321,8,12,'Farád');
INSERT INTO `varosok` VALUES (2372,9322,8,12,'Rábatamási');
INSERT INTO `varosok` VALUES (2373,9323,8,12,'Jobaháza');
INSERT INTO `varosok` VALUES (2374,9324,8,12,'Bogyoszló');
INSERT INTO `varosok` VALUES (2375,9325,8,12,'Sopronnémeti');
INSERT INTO `varosok` VALUES (2376,9326,8,12,'Szil');
INSERT INTO `varosok` VALUES (2377,9327,8,12,'Vág');
INSERT INTO `varosok` VALUES (2378,9330,8,12,'Kapuvár');
INSERT INTO `varosok` VALUES (2379,9341,8,12,'Kisfalud');
INSERT INTO `varosok` VALUES (2380,9342,8,12,'Mihályi');
INSERT INTO `varosok` VALUES (2381,9343,8,12,'Beled');
INSERT INTO `varosok` VALUES (2382,9344,8,12,'Rábakecöl');
INSERT INTO `varosok` VALUES (2383,9345,8,12,'Páli');
INSERT INTO `varosok` VALUES (2384,9346,8,12,'Magyarkeresztúr');
INSERT INTO `varosok` VALUES (2385,9351,8,12,'Babót');
INSERT INTO `varosok` VALUES (2386,9352,8,12,'Veszkény');
INSERT INTO `varosok` VALUES (2387,9353,8,12,'Szárföld');
INSERT INTO `varosok` VALUES (2388,9354,8,12,'Osli');
INSERT INTO `varosok` VALUES (2389,9361,8,12,'Hövej');
INSERT INTO `varosok` VALUES (2390,9362,8,12,'Himod');
INSERT INTO `varosok` VALUES (2391,9363,8,12,'Gyóró');
INSERT INTO `varosok` VALUES (2392,9364,8,12,'Cirák');
INSERT INTO `varosok` VALUES (2393,9365,8,12,'Dénesfa');
INSERT INTO `varosok` VALUES (2394,9371,8,12,'Vitnyéd');
INSERT INTO `varosok` VALUES (2395,9372,8,12,'Csapod');
INSERT INTO `varosok` VALUES (2396,9373,8,12,'Pusztacsalád');
INSERT INTO `varosok` VALUES (2397,9374,8,12,'Iván');
INSERT INTO `varosok` VALUES (2398,9375,8,12,'Répceszemere');
INSERT INTO `varosok` VALUES (2399,9400,8,12,'Sopron');
INSERT INTO `varosok` VALUES (2400,9421,8,12,'Fertőrákos');
INSERT INTO `varosok` VALUES (2401,9422,8,12,'Harka');
INSERT INTO `varosok` VALUES (2402,9423,8,12,'Ágfalva');
INSERT INTO `varosok` VALUES (2403,9431,8,12,'Fertőd');
INSERT INTO `varosok` VALUES (2404,9435,8,12,'Sarród');
INSERT INTO `varosok` VALUES (2405,9436,8,12,'Fertőszéplak');
INSERT INTO `varosok` VALUES (2406,9437,8,12,'Hegykő');
INSERT INTO `varosok` VALUES (2407,9441,8,12,'Agyagosszergény');
INSERT INTO `varosok` VALUES (2408,9442,8,12,'Fertőendréd');
INSERT INTO `varosok` VALUES (2409,9443,8,12,'Petőháza');
INSERT INTO `varosok` VALUES (2410,9444,8,12,'Fertőszentmiklós');
INSERT INTO `varosok` VALUES (2411,9451,8,12,'Röjtökmuzsaj');
INSERT INTO `varosok` VALUES (2412,9461,8,12,'Lövő');
INSERT INTO `varosok` VALUES (2413,9462,8,12,'Völcsej');
INSERT INTO `varosok` VALUES (2414,9463,8,12,'Sopronhorpács');
INSERT INTO `varosok` VALUES (2415,9464,8,12,'Und');
INSERT INTO `varosok` VALUES (2416,9471,8,12,'Nemeskér');
INSERT INTO `varosok` VALUES (2417,9472,8,12,'Újkér');
INSERT INTO `varosok` VALUES (2418,9473,8,12,'Egyházasfalu');
INSERT INTO `varosok` VALUES (2419,9474,8,12,'Szakony');
INSERT INTO `varosok` VALUES (2420,9475,8,12,'Répcevis');
INSERT INTO `varosok` VALUES (2421,9476,8,12,'Zsira');
INSERT INTO `varosok` VALUES (2422,9481,8,12,'Pinnye');
INSERT INTO `varosok` VALUES (2423,9482,8,12,'Nagylózs');
INSERT INTO `varosok` VALUES (2424,9483,8,12,'Sopronkövesd');
INSERT INTO `varosok` VALUES (2425,9484,8,12,'Pereszteg');
INSERT INTO `varosok` VALUES (2426,9485,8,12,'Nagycenk');
INSERT INTO `varosok` VALUES (2427,9491,8,12,'Hidegség');
INSERT INTO `varosok` VALUES (2428,9492,8,12,'Fertőhomok');
INSERT INTO `varosok` VALUES (2429,9493,8,12,'Fertőboz');
INSERT INTO `varosok` VALUES (2430,9495,8,12,'Kópháza');
INSERT INTO `varosok` VALUES (2431,9500,18,12,'Celldömölk');
INSERT INTO `varosok` VALUES (2432,9511,18,12,'Kemenesmihályfa');
INSERT INTO `varosok` VALUES (2433,9512,18,12,'Ostffyasszonyfa');
INSERT INTO `varosok` VALUES (2434,9513,18,12,'Csönge');
INSERT INTO `varosok` VALUES (2435,9514,18,12,'Kenyeri');
INSERT INTO `varosok` VALUES (2436,9515,18,12,'Pápoc');
INSERT INTO `varosok` VALUES (2437,9516,18,12,'Vönöck');
INSERT INTO `varosok` VALUES (2438,9517,18,12,'Kemenessömjén');
INSERT INTO `varosok` VALUES (2439,9521,18,12,'Kemenesszentmárton');
INSERT INTO `varosok` VALUES (2440,9522,18,12,'Kemenesmagasi');
INSERT INTO `varosok` VALUES (2441,9523,18,12,'Szergény');
INSERT INTO `varosok` VALUES (2442,9531,18,12,'Mersevát');
INSERT INTO `varosok` VALUES (2443,9532,19,12,'Külsővat');
INSERT INTO `varosok` VALUES (2444,9533,19,12,'Nemesszalók');
INSERT INTO `varosok` VALUES (2445,9534,19,12,'Marcalgergelyi');
INSERT INTO `varosok` VALUES (2446,9542,18,12,'Boba');
INSERT INTO `varosok` VALUES (2447,9544,18,12,'Kemenespálfa');
INSERT INTO `varosok` VALUES (2448,9545,18,12,'Jánosháza');
INSERT INTO `varosok` VALUES (2449,9547,18,12,'Karakó');
INSERT INTO `varosok` VALUES (2450,9548,18,12,'Nemeskeresztúr');
INSERT INTO `varosok` VALUES (2451,9549,18,12,'Keléd');
INSERT INTO `varosok` VALUES (2452,9551,18,12,'Mesteri');
INSERT INTO `varosok` VALUES (2453,9552,18,12,'Vásárosmiske');
INSERT INTO `varosok` VALUES (2454,9553,18,12,'Köcsk');
INSERT INTO `varosok` VALUES (2455,9554,18,12,'Egyházashetye');
INSERT INTO `varosok` VALUES (2456,9555,18,12,'Kissomlyó');
INSERT INTO `varosok` VALUES (2457,9556,18,12,'Duka');
INSERT INTO `varosok` VALUES (2458,9561,18,12,'Nagysimonyi');
INSERT INTO `varosok` VALUES (2459,9600,18,12,'Sárvár');
INSERT INTO `varosok` VALUES (2460,9611,18,12,'Csénye');
INSERT INTO `varosok` VALUES (2461,9612,18,12,'Bögöt');
INSERT INTO `varosok` VALUES (2462,9621,18,12,'Ölbő');
INSERT INTO `varosok` VALUES (2463,9622,18,12,'Szeleste');
INSERT INTO `varosok` VALUES (2464,9623,18,12,'Répceszentgyörgy');
INSERT INTO `varosok` VALUES (2465,9624,18,12,'Chernelházadamonya');
INSERT INTO `varosok` VALUES (2466,9625,18,12,'Bő');
INSERT INTO `varosok` VALUES (2467,9631,18,12,'Hegyfalu');
INSERT INTO `varosok` VALUES (2468,9632,18,12,'Sajtoskál');
INSERT INTO `varosok` VALUES (2469,9633,18,12,'Simaság');
INSERT INTO `varosok` VALUES (2470,9634,18,12,'Lócs');
INSERT INTO `varosok` VALUES (2471,9635,18,12,'Zsédeny');
INSERT INTO `varosok` VALUES (2472,9636,18,12,'Pósfa');
INSERT INTO `varosok` VALUES (2473,9641,18,12,'Rábapaty');
INSERT INTO `varosok` VALUES (2474,9643,18,12,'Jákfa');
INSERT INTO `varosok` VALUES (2475,9651,18,12,'Uraiújfalu');
INSERT INTO `varosok` VALUES (2476,9652,18,12,'Nick');
INSERT INTO `varosok` VALUES (2477,9653,18,12,'Répcelak');
INSERT INTO `varosok` VALUES (2478,9654,18,12,'Csánig');
INSERT INTO `varosok` VALUES (2479,9661,18,12,'Vasegerszeg');
INSERT INTO `varosok` VALUES (2480,9662,18,12,'Tompaládony');
INSERT INTO `varosok` VALUES (2481,9663,18,12,'Nemesládony');
INSERT INTO `varosok` VALUES (2482,9664,18,12,'Nagygeresd');
INSERT INTO `varosok` VALUES (2483,9665,18,12,'Vámoscsalád');
INSERT INTO `varosok` VALUES (2484,9671,18,12,'Sitke');
INSERT INTO `varosok` VALUES (2485,9672,18,12,'Gérce');
INSERT INTO `varosok` VALUES (2486,9673,18,12,'Káld');
INSERT INTO `varosok` VALUES (2487,9674,18,12,'Vashosszúfalu');
INSERT INTO `varosok` VALUES (2488,9675,18,12,'Bögöte');
INSERT INTO `varosok` VALUES (2489,9676,18,12,'Hosszúpereszteg');
INSERT INTO `varosok` VALUES (2490,9681,18,12,'Sótony');
INSERT INTO `varosok` VALUES (2491,9682,18,12,'Nyőgér');
INSERT INTO `varosok` VALUES (2492,9683,18,12,'Bejcgyertyános');
INSERT INTO `varosok` VALUES (2493,9684,18,12,'Egervölgy');
INSERT INTO `varosok` VALUES (2494,9685,18,12,'Szemenye');
INSERT INTO `varosok` VALUES (2495,9700,18,12,'Szombathely');
INSERT INTO `varosok` VALUES (2496,9721,18,12,'Gencsapáti');
INSERT INTO `varosok` VALUES (2497,9722,18,12,'Perenye');
INSERT INTO `varosok` VALUES (2498,9723,18,12,'Lukácsháza');
INSERT INTO `varosok` VALUES (2499,9724,18,12,'Gyöngyösfalu');
INSERT INTO `varosok` VALUES (2500,9725,18,12,'Kőszegszerdahely');
INSERT INTO `varosok` VALUES (2501,9726,18,12,'Velem');
INSERT INTO `varosok` VALUES (2502,9727,18,12,'Bozsok');
INSERT INTO `varosok` VALUES (2503,9730,18,12,'Kőszeg');
INSERT INTO `varosok` VALUES (2504,9733,18,12,'Horvátzsidány');
INSERT INTO `varosok` VALUES (2505,9734,18,12,'Peresznye');
INSERT INTO `varosok` VALUES (2506,9735,18,12,'Csepreg');
INSERT INTO `varosok` VALUES (2507,9736,18,12,'Tormásliget');
INSERT INTO `varosok` VALUES (2508,9737,18,12,'Bük');
INSERT INTO `varosok` VALUES (2509,9738,18,12,'Tömörd');
INSERT INTO `varosok` VALUES (2510,9739,18,12,'Nemescsó');
INSERT INTO `varosok` VALUES (2511,9741,18,12,'Vassurány');
INSERT INTO `varosok` VALUES (2512,9742,18,12,'Salköveskút');
INSERT INTO `varosok` VALUES (2513,9743,18,12,'Söpte');
INSERT INTO `varosok` VALUES (2514,9744,18,12,'Vasasszonyfa');
INSERT INTO `varosok` VALUES (2515,9745,18,12,'Meszlen');
INSERT INTO `varosok` VALUES (2516,9746,18,12,'Acsád');
INSERT INTO `varosok` VALUES (2517,9747,18,12,'Vasszilvágy');
INSERT INTO `varosok` VALUES (2518,9748,18,12,'Vát');
INSERT INTO `varosok` VALUES (2519,9749,18,12,'Nemesbőd');
INSERT INTO `varosok` VALUES (2520,9751,18,12,'Vép');
INSERT INTO `varosok` VALUES (2521,9752,18,12,'Bozzai');
INSERT INTO `varosok` VALUES (2522,9754,18,12,'Pecöl');
INSERT INTO `varosok` VALUES (2523,9756,18,12,'Ikervár');
INSERT INTO `varosok` VALUES (2524,9757,18,12,'Meggyeskovácsi');
INSERT INTO `varosok` VALUES (2525,9761,18,12,'Táplánszentkereszt');
INSERT INTO `varosok` VALUES (2526,9762,18,12,'Tanakajd');
INSERT INTO `varosok` VALUES (2527,9763,18,12,'Vasszécseny');
INSERT INTO `varosok` VALUES (2528,9764,18,12,'Csempeszkopács');
INSERT INTO `varosok` VALUES (2529,9766,18,12,'Rum');
INSERT INTO `varosok` VALUES (2530,9771,18,12,'Balogunyom');
INSERT INTO `varosok` VALUES (2531,9772,18,12,'Kisunyom');
INSERT INTO `varosok` VALUES (2532,9773,18,12,'Sorokpolány');
INSERT INTO `varosok` VALUES (2533,9774,18,12,'Sorkifalud');
INSERT INTO `varosok` VALUES (2534,9775,18,12,'Nemeskolta');
INSERT INTO `varosok` VALUES (2535,9776,18,12,'Püspökmolnári');
INSERT INTO `varosok` VALUES (2536,9777,18,12,'Rábahidvég');
INSERT INTO `varosok` VALUES (2537,9781,18,12,'Egyházashollós');
INSERT INTO `varosok` VALUES (2538,9782,18,12,'Nemesrempehollós');
INSERT INTO `varosok` VALUES (2539,9783,18,12,'Egyházasrádóc');
INSERT INTO `varosok` VALUES (2540,9784,18,12,'Rádóckölked');
INSERT INTO `varosok` VALUES (2541,9789,18,12,'Sé');
INSERT INTO `varosok` VALUES (2542,9791,18,12,'Torony');
INSERT INTO `varosok` VALUES (2543,9792,18,12,'Bucsu');
INSERT INTO `varosok` VALUES (2544,9793,18,12,'Narda');
INSERT INTO `varosok` VALUES (2545,9794,18,12,'Felsőcsatár');
INSERT INTO `varosok` VALUES (2546,9795,18,12,'Vaskeresztes');
INSERT INTO `varosok` VALUES (2547,9796,18,12,'Pornóapáti');
INSERT INTO `varosok` VALUES (2548,9797,18,12,'Nárai');
INSERT INTO `varosok` VALUES (2549,9798,18,12,'Ják');
INSERT INTO `varosok` VALUES (2550,9799,18,12,'Szentpéterfa');
INSERT INTO `varosok` VALUES (2551,9800,18,12,'Vasvár');
INSERT INTO `varosok` VALUES (2552,9811,18,12,'Andrásfa');
INSERT INTO `varosok` VALUES (2553,9812,18,12,'Telekes');
INSERT INTO `varosok` VALUES (2554,9813,18,12,'Gersekarát');
INSERT INTO `varosok` VALUES (2555,9814,18,12,'Halastó');
INSERT INTO `varosok` VALUES (2556,9821,18,12,'Győrvár');
INSERT INTO `varosok` VALUES (2557,9823,18,12,'Pácsony');
INSERT INTO `varosok` VALUES (2558,9824,18,12,'Olaszfa');
INSERT INTO `varosok` VALUES (2559,9825,18,12,'Oszkó');
INSERT INTO `varosok` VALUES (2560,9826,18,12,'Petőmihályfa');
INSERT INTO `varosok` VALUES (2561,9831,18,12,'Bérbaltavár');
INSERT INTO `varosok` VALUES (2562,9832,18,12,'Nagytilaj');
INSERT INTO `varosok` VALUES (2563,9833,18,12,'Csehi');
INSERT INTO `varosok` VALUES (2564,9834,18,12,'Csehimindszent');
INSERT INTO `varosok` VALUES (2565,9835,18,12,'Mikosszéplak');
INSERT INTO `varosok` VALUES (2566,9836,18,12,'Csipkerek');
INSERT INTO `varosok` VALUES (2567,9841,18,12,'Kám');
INSERT INTO `varosok` VALUES (2568,9842,18,12,'Alsóújlak');
INSERT INTO `varosok` VALUES (2569,9900,18,12,'Körmend');
INSERT INTO `varosok` VALUES (2570,9909,18,12,'Magyarnádalja');
INSERT INTO `varosok` VALUES (2571,9912,18,12,'Molnaszecsőd');
INSERT INTO `varosok` VALUES (2572,9913,18,12,'Szarvaskend');
INSERT INTO `varosok` VALUES (2573,9914,18,12,'Döbörhegy');
INSERT INTO `varosok` VALUES (2574,9915,18,12,'Nádasd');
INSERT INTO `varosok` VALUES (2575,9917,18,12,'Halogy');
INSERT INTO `varosok` VALUES (2576,9918,18,12,'Felsőmarác');
INSERT INTO `varosok` VALUES (2577,9919,18,12,'Csákánydoroszló');
INSERT INTO `varosok` VALUES (2578,9921,18,12,'Vasalja');
INSERT INTO `varosok` VALUES (2579,9922,18,12,'Pinkamindszent');
INSERT INTO `varosok` VALUES (2580,9923,18,12,'Kemestaródfa');
INSERT INTO `varosok` VALUES (2581,9931,18,12,'Ivánc');
INSERT INTO `varosok` VALUES (2582,9932,18,12,'Viszák');
INSERT INTO `varosok` VALUES (2583,9933,18,12,'Őrimagyarósd');
INSERT INTO `varosok` VALUES (2584,9934,18,12,'Hegyhátszentjakab');
INSERT INTO `varosok` VALUES (2585,9935,18,12,'Szőce');
INSERT INTO `varosok` VALUES (2586,9936,18,12,'Kisrákos');
INSERT INTO `varosok` VALUES (2587,9937,18,12,'Pankasz');
INSERT INTO `varosok` VALUES (2588,9938,18,12,'Nagyrákos');
INSERT INTO `varosok` VALUES (2589,9941,18,12,'Őriszentpéter');
INSERT INTO `varosok` VALUES (2590,9942,18,12,'Szalafő');
INSERT INTO `varosok` VALUES (2591,9943,18,12,'Kondorfa');
INSERT INTO `varosok` VALUES (2592,9944,18,12,'Bajánsenye');
INSERT INTO `varosok` VALUES (2593,9945,18,12,'Kercaszomor');
INSERT INTO `varosok` VALUES (2594,9946,18,12,'Magyarszombatfa');
INSERT INTO `varosok` VALUES (2595,9951,18,12,'Rátót');
INSERT INTO `varosok` VALUES (2596,9952,18,12,'Gasztony');
INSERT INTO `varosok` VALUES (2597,9953,18,12,'Vasszentmihály');
INSERT INTO `varosok` VALUES (2598,9954,18,12,'Rönök');
INSERT INTO `varosok` VALUES (2599,9961,18,12,'Rábagyarmat');
INSERT INTO `varosok` VALUES (2600,9962,18,12,'Csörötnek');
INSERT INTO `varosok` VALUES (2601,9970,18,12,'Szentgotthárd');
INSERT INTO `varosok` VALUES (2602,9982,18,12,'Apátistvánfalva');
INSERT INTO `varosok` VALUES (2603,9983,18,12,'Alsószölnök');
INSERT INTO `varosok` VALUES (2604,9985,18,12,'Felsőszölnök');
INSERT INTO `varosok` VALUES (2605,1000,5,12,'Budapest');
INSERT INTO `varosok` VALUES (2606,1010,5,12,'Budapest I. kerület');
INSERT INTO `varosok` VALUES (2607,1020,5,12,'Budapest II. kerület');
INSERT INTO `varosok` VALUES (2608,1030,5,12,'Budapest III. kerület');
INSERT INTO `varosok` VALUES (2609,1040,5,12,'Budapest IV. kerület');
INSERT INTO `varosok` VALUES (2610,1050,5,12,'Budapest V. kerület');
INSERT INTO `varosok` VALUES (2611,1060,5,12,'Budapest VI. kerület');
INSERT INTO `varosok` VALUES (2612,1070,5,12,'Budapest VII. kerület');
INSERT INTO `varosok` VALUES (2613,1080,5,12,'Budapest VIII. kerület');
INSERT INTO `varosok` VALUES (2614,1090,5,12,'Budapest IX. kerület');
INSERT INTO `varosok` VALUES (2615,1100,5,12,'Budapest X. kerület');
INSERT INTO `varosok` VALUES (2616,1110,5,12,'Budapest XI. kerület');
INSERT INTO `varosok` VALUES (2617,1120,5,12,'Budapest XII. kerület');
INSERT INTO `varosok` VALUES (2618,1130,5,12,'Budapest XIII. kerület');
INSERT INTO `varosok` VALUES (2619,1140,5,12,'Budapest XIV. kerület');
INSERT INTO `varosok` VALUES (2620,1150,5,12,'Budapest XV. kerület');
INSERT INTO `varosok` VALUES (2621,1160,5,12,'Budapest XVI. kerület');
INSERT INTO `varosok` VALUES (2622,1170,5,12,'Budapest XVII. kerület');
INSERT INTO `varosok` VALUES (2623,1180,5,12,'Budapest XVIII. kerület');
INSERT INTO `varosok` VALUES (2624,1190,5,12,'Budapest XIX. kerület');
INSERT INTO `varosok` VALUES (2625,1200,5,12,'Budapest XX. kerület');
INSERT INTO `varosok` VALUES (2626,1210,5,12,'Budapest XXI. kerület');
INSERT INTO `varosok` VALUES (2627,1220,5,12,'Budapest XXII. kerület');
INSERT INTO `varosok` VALUES (2628,9062,8,12,'Vének');
INSERT INTO `varosok` VALUES (2629,9062,8,12,'Szógye');
INSERT INTO `varosok` VALUES (2630,8380,20,12,'Felsőpáhok');
INSERT INTO `varosok` VALUES (2631,8019,7,12,'Börgönd');
INSERT INTO `varosok` VALUES (2632,2485,7,12,'Dinnyés');
INSERT INTO `varosok` VALUES (2633,2655,13,12,'Szente');
INSERT INTO `varosok` VALUES (2634,2694,13,12,'Debercsény');
INSERT INTO `varosok` VALUES (2635,2655,13,12,'Kisecset');
INSERT INTO `varosok` VALUES (2636,2066,7,12,'Újbarok');
INSERT INTO `varosok` VALUES (2637,2063,7,12,'Óbarok');
INSERT INTO `varosok` VALUES (2638,7477,15,12,'Zselickisfalud');
INSERT INTO `varosok` VALUES (2639,0,15,12,'Zselickislak');
INSERT INTO `varosok` VALUES (2640,7400,15,12,'Orci');
INSERT INTO `varosok` VALUES (2641,9133,8,12,'Rábaszentmiklós');
INSERT INTO `varosok` VALUES (2642,9343,8,12,'Edve');
INSERT INTO `varosok` VALUES (2643,8523,19,12,'Várkesző');
INSERT INTO `varosok` VALUES (2644,9451,8,12,'Ebergőc');
INSERT INTO `varosok` VALUES (2645,0,15,12,'Balatonkeresztúr');
INSERT INTO `varosok` VALUES (2646,0,15,12,'Buzsák');
INSERT INTO `varosok` VALUES (2647,0,20,12,'Miklósfa');
INSERT INTO `varosok` VALUES (2648,9169,8,12,'Maglóca');
INSERT INTO `varosok` VALUES (2649,0,8,12,'Homokkomárom');
INSERT INTO `varosok` VALUES (2650,0,4,12,'Tornaszentandrás');
INSERT INTO `varosok` VALUES (2651,9474,8,12,'Gyalóka');
INSERT INTO `varosok` VALUES (2652,9375,8,12,'Csáfordjánosfa');
INSERT INTO `varosok` VALUES (2653,8475,19,12,'Szentimrefalva');
INSERT INTO `varosok` VALUES (2654,9625,18,12,'Gór');
INSERT INTO `varosok` VALUES (2655,9733,18,12,'Kiszsidány');
INSERT INTO `varosok` VALUES (2656,9662,18,12,'Mesterháza');
INSERT INTO `varosok` VALUES (2657,9235,8,12,'Dunaremete');
INSERT INTO `varosok` VALUES (2658,9791,18,12,'Dozmat');
INSERT INTO `varosok` VALUES (2659,9343,8,12,'Vásárosfalu');
INSERT INTO `varosok` VALUES (2660,1238,5,12,'Budapest XXIII. kerület');
INSERT INTO `varosok` VALUES (2661,3704,4,12,'Berente');
INSERT INTO `varosok` VALUES (2662,3233,13,12,'Mátrakeresztes');
INSERT INTO `varosok` VALUES (2663,3636,4,12,'Sajógalgóc');
INSERT INTO `varosok` VALUES (2664,4737,16,12,'Darnó');
INSERT INTO `varosok` VALUES (2665,4754,16,12,'Fülpösdaróc');
INSERT INTO `varosok` VALUES (2666,3874,4,12,'Hernádpetri');
INSERT INTO `varosok` VALUES (2667,3812,4,12,'Monaj');
INSERT INTO `varosok` VALUES (2668,2697,13,12,'Szandaváralja');
INSERT INTO `varosok` VALUES (2669,3809,4,12,'Nyésta');
INSERT INTO `varosok` VALUES (2670,3786,4,12,'Szakácsi');
INSERT INTO `varosok` VALUES (2671,2673,13,12,'Iliny');
INSERT INTO `varosok` VALUES (2672,3716,4,12,'Sóstófalva');
INSERT INTO `varosok` VALUES (2673,3928,4,12,'Szegilong');
INSERT INTO `varosok` VALUES (2674,3994,4,12,'Kishuta');
INSERT INTO `varosok` VALUES (2675,9962,18,12,'Magyarlak');
INSERT INTO `varosok` VALUES (2676,8245,19,12,'Vászoly');
INSERT INTO `varosok` VALUES (2679,8291,19,12,'Vöröstó');
INSERT INTO `varosok` VALUES (2678,3075,13,12,'Kisbárkány');
INSERT INTO `varosok` VALUES (2680,9983,18,12,'Szakonyfalu');
INSERT INTO `varosok` VALUES (2681,9735,18,12,'Kőszegpaty');
INSERT INTO `varosok` VALUES (2682,7362,17,12,'Jágónak');
INSERT INTO `varosok` VALUES (2683,9725,18,12,'Cák');
INSERT INTO `varosok` VALUES (2684,8973,20,12,'Kerkakutas');
INSERT INTO `varosok` VALUES (2685,9612,18,12,'Porpác');
INSERT INTO `varosok` VALUES (2686,7775,2,12,'Lapáncsa');
INSERT INTO `varosok` VALUES (2687,7814,2,12,'Siklósbodony');
INSERT INTO `varosok` VALUES (2688,7184,2,12,'Szárász');
INSERT INTO `varosok` VALUES (2689,8992,19,12,'Paloznak');
INSERT INTO `varosok` VALUES (2690,8767,20,12,'Alsórajk');
INSERT INTO `varosok` VALUES (2691,8741,20,12,'Bókaháza');
INSERT INTO `varosok` VALUES (2692,8762,20,12,'Gétye');
INSERT INTO `varosok` VALUES (2693,8767,20,12,'Pötréte');
INSERT INTO `varosok` VALUES (2694,8316,20,12,'Vállus');
INSERT INTO `varosok` VALUES (2695,8354,20,12,'Vindornyafok');
INSERT INTO `varosok` VALUES (2696,8353,20,12,'Vindornyalak');
INSERT INTO `varosok` VALUES (2697,8761,20,12,'Zalaigrice');
INSERT INTO `varosok` VALUES (2698,8354,20,12,'Zalaköveskút');
INSERT INTO `varosok` VALUES (2699,8764,20,12,'Zalaszentmárton');
INSERT INTO `varosok` VALUES (2700,9784,18,12,'Nagykölked');
INSERT INTO `varosok` VALUES (2701,9912,18,12,'Magyarszecsőd');
INSERT INTO `varosok` VALUES (2702,9784,18,12,'Harasztifalu');
INSERT INTO `varosok` VALUES (2713,8983,20,12,'Babosdöbréte');
INSERT INTO `varosok` VALUES (2704,9327,8,12,'Rábasebes');
INSERT INTO `varosok` VALUES (2705,9913,18,12,'Döröske');
INSERT INTO `varosok` VALUES (2706,7095,17,12,'Újireg');
INSERT INTO `varosok` VALUES (2707,7214,17,12,'Várong');
INSERT INTO `varosok` VALUES (2708,7362,2,12,'Gerényes');
INSERT INTO `varosok` VALUES (2709,7362,2,12,'Tarrós');
INSERT INTO `varosok` VALUES (2710,7381,2,12,'Tékes');
INSERT INTO `varosok` VALUES (2711,8272,19,12,'Balatoncsicsó');
INSERT INTO `varosok` VALUES (2712,8284,19,12,'Kisapáti');
INSERT INTO `varosok` VALUES (2714,8992,20,12,'Boncodfölde');
INSERT INTO `varosok` VALUES (2715,8992,20,12,'Hagyárosbörönd');
INSERT INTO `varosok` VALUES (2716,8912,20,12,'Nagypáli');
INSERT INTO `varosok` VALUES (2717,8291,19,12,'Pula');
INSERT INTO `varosok` VALUES (2718,7381,2,12,'Ág');
INSERT INTO `varosok` VALUES (2719,8242,19,12,'Örvényes');
INSERT INTO `varosok` VALUES (2720,8233,19,12,'Balatonszőlős');
INSERT INTO `varosok` VALUES (2721,8294,19,12,'Vigántpetend');
INSERT INTO `varosok` VALUES (2722,8256,19,12,'Salföld');
INSERT INTO `varosok` VALUES (2723,7756,2,12,'Kisbudmér');
INSERT INTO `varosok` VALUES (2724,7720,2,12,'Lovászhetény');
INSERT INTO `varosok` VALUES (2725,7914,2,12,'Bánfa');
INSERT INTO `varosok` VALUES (2726,7954,2,12,'Kisasszonyfa');
INSERT INTO `varosok` VALUES (2727,7960,2,12,'Drávasztára');
INSERT INTO `varosok` VALUES (2728,7334,2,12,'Köblény');
INSERT INTO `varosok` VALUES (2729,7838,2,12,'Vejti');
INSERT INTO `varosok` VALUES (2730,7383,2,12,'Baranyaszentgyörgy');
INSERT INTO `varosok` VALUES (2731,7391,2,12,'Kishajmás');
INSERT INTO `varosok` VALUES (2732,7664,2,12,'Szilágy');
INSERT INTO `varosok` VALUES (2733,8291,19,12,'Barnag');
INSERT INTO `varosok` VALUES (2734,8925,20,12,'Kisbucsa');
INSERT INTO `varosok` VALUES (2735,8925,20,12,'Nemeshetés');
INSERT INTO `varosok` VALUES (2736,8925,20,12,'Nemessándorháza');
INSERT INTO `varosok` VALUES (2737,3780,4,12,'Ládbesenyő');
INSERT INTO `varosok` VALUES (2738,9825,20,12,'Nemesszentandrás');
INSERT INTO `varosok` VALUES (2739,8943,20,12,'Csatár');
INSERT INTO `varosok` VALUES (2740,8935,20,12,'Padár');
INSERT INTO `varosok` VALUES (2741,8931,20,12,'Vöckönd');
INSERT INTO `varosok` VALUES (2742,8799,20,12,'Dötk');
INSERT INTO `varosok` VALUES (2743,8932,20,12,'Gyűrűs');
INSERT INTO `varosok` VALUES (2744,8341,20,12,'Szalapa');
INSERT INTO `varosok` VALUES (2745,0,0,1,'Bécs');
INSERT INTO `varosok` VALUES (2746,8935,20,12,'Almásháza');
INSERT INTO `varosok` VALUES (2747,8782,20,12,'Tilaj');
INSERT INTO `varosok` VALUES (2748,8782,20,12,'Tilaj');
INSERT INTO `varosok` VALUES (2749,9946,18,12,'Velemér');
INSERT INTO `varosok` VALUES (2750,7695,2,12,'Óbánya');
INSERT INTO `varosok` VALUES (2751,7698,2,12,'Ófalu');
INSERT INTO `varosok` VALUES (2752,8782,20,12,'Ligetfalva');
INSERT INTO `varosok` VALUES (2753,8921,20,12,'Alibánfa');
INSERT INTO `varosok` VALUES (2754,8788,20,12,'Sénye');
INSERT INTO `varosok` VALUES (2755,8475,19,12,'Hosztót');
INSERT INTO `varosok` VALUES (2756,8800,20,12,'Bajcsa');
INSERT INTO `varosok` VALUES (2757,8946,20,12,'Baktüttös');
INSERT INTO `varosok` VALUES (2758,8948,20,12,'Barlahida');
INSERT INTO `varosok` VALUES (2759,8978,20,12,'Belsősárd');
INSERT INTO `varosok` VALUES (2760,8969,20,12,'Bödeháza');
INSERT INTO `varosok` VALUES (2761,8893,20,12,'Bucsuta');
INSERT INTO `varosok` VALUES (2762,8917,20,12,'Dobronhegy');
INSERT INTO `varosok` VALUES (2763,8978,20,12,'Gosztola');
INSERT INTO `varosok` VALUES (2764,8991,20,12,'Hottó');
INSERT INTO `varosok` VALUES (2765,8971,20,12,'Kerkabarabás');
INSERT INTO `varosok` VALUES (2766,8874,20,12,'Kerkaszentkirály');
INSERT INTO `varosok` VALUES (2767,8888,20,12,'Kiscsehi');
INSERT INTO `varosok` VALUES (2768,8911,20,12,'Kiskutas');
INSERT INTO `varosok` VALUES (2769,8957,20,12,'Kissziget');
INSERT INTO `varosok` VALUES (2770,8868,20,12,'Kistolmács');
INSERT INTO `varosok` VALUES (2771,8887,20,12,'Lasztonya');
INSERT INTO `varosok` VALUES (2772,8981,20,12,'Lickóvadamos');
INSERT INTO `varosok` VALUES (2773,8888,20,12,'Maróc');
INSERT INTO `varosok` VALUES (2774,8868,20,12,'Murarátka');
INSERT INTO `varosok` VALUES (2775,8918,20,12,'Németfalu');
INSERT INTO `varosok` VALUES (2776,8983,20,12,'Ormándlak');
INSERT INTO `varosok` VALUES (2777,8917,20,12,'Pálfiszeg');
INSERT INTO `varosok` VALUES (2778,8866,20,12,'Petrivente');
INSERT INTO `varosok` VALUES (2779,8956,20,12,'Pördefölde');
INSERT INTO `varosok` VALUES (2780,8986,20,12,'Pusztaapáti');
INSERT INTO `varosok` VALUES (2781,8946,20,12,'Pusztaederics');
INSERT INTO `varosok` VALUES (2782,8947,20,12,'Szentkozmadombja');
INSERT INTO `varosok` VALUES (2783,8872,20,12,'Szentmargitfalva');
INSERT INTO `varosok` VALUES (2784,8986,20,12,'Szilvágy');
INSERT INTO `varosok` VALUES (2785,8885,20,12,'Valkonya');
INSERT INTO `varosok` VALUES (2786,8891,20,12,'Várfölde');
INSERT INTO `varosok` VALUES (2787,8868,20,12,'Zajk');
INSERT INTO `varosok` VALUES (2788,8992,20,12,'Zalaboldogfa');
INSERT INTO `varosok` VALUES (2789,8969,20,12,'Zalaszombatfa');
INSERT INTO `varosok` VALUES (2790,8957,20,12,'Zebecke');
INSERT INTO `varosok` VALUES (2791,3334,10,12,'Terpes');
INSERT INTO `varosok` VALUES (2792,9774,18,12,'Gyanógeregye');
INSERT INTO `varosok` VALUES (2793,9796,18,12,'Horvátlövő');
INSERT INTO `varosok` VALUES (2794,9739,18,12,'Pusztacsó');
INSERT INTO `varosok` VALUES (2795,9725,18,12,'Kőszegdoroszló');
INSERT INTO `varosok` VALUES (2796,9752,18,12,'Kenéz');
INSERT INTO `varosok` VALUES (2797,9754,18,12,'Megyehíd');
INSERT INTO `varosok` VALUES (2798,9766,18,12,'Rábatöttös');
INSERT INTO `varosok` VALUES (2799,9554,18,12,'Borgáta');
INSERT INTO `varosok` VALUES (2800,9553,18,12,'Kemeneskápolna');
INSERT INTO `varosok` VALUES (2801,8913,20,12,'Gősfa');
INSERT INTO `varosok` VALUES (2802,8921,20,12,'Zalaszentlőrinc');
INSERT INTO `varosok` VALUES (2803,9813,18,12,'Sárfimizdó');
INSERT INTO `varosok` VALUES (2804,9821,18,12,'Hegyhátszentpéter');
INSERT INTO `varosok` VALUES (2805,9917,18,12,'Daraboshegy');
INSERT INTO `varosok` VALUES (2806,9915,18,12,'Hegyháthodász');
INSERT INTO `varosok` VALUES (2807,9915,18,12,'Hegyhátsál');
INSERT INTO `varosok` VALUES (2808,9915,18,12,'Katafa');
INSERT INTO `varosok` VALUES (2809,9913,18,12,'Nagymizdó');
INSERT INTO `varosok` VALUES (2810,24300,0,46,'Topolya');
INSERT INTO `varosok` VALUES (2811,8782,20,12,'Ligetfalva');
INSERT INTO `varosok` VALUES (2812,0,0,25,'Abosfalva');
INSERT INTO `varosok` VALUES (2813,0,0,25,'Ákosfalva');
INSERT INTO `varosok` VALUES (2814,0,0,25,'Gyergyóalfalu');
INSERT INTO `varosok` VALUES (2815,0,0,25,'Kézdialmás');
INSERT INTO `varosok` VALUES (2816,0,0,25,'Alsócsernáton');
INSERT INTO `varosok` VALUES (2817,0,0,25,'Alsókapnik');
INSERT INTO `varosok` VALUES (2818,0,0,25,'Altorja');
INSERT INTO `varosok` VALUES (2819,0,0,25,'Alvinc');
INSERT INTO `varosok` VALUES (2820,0,0,25,'Aninósza');
INSERT INTO `varosok` VALUES (2821,0,0,25,'Aranyosgyéres');
INSERT INTO `varosok` VALUES (2822,0,0,25,'Kisszántó');
INSERT INTO `varosok` VALUES (2823,0,0,25,'Atyha');
INSERT INTO `varosok` VALUES (2824,0,0,25,'Balánbánya');
INSERT INTO `varosok` VALUES (2825,0,0,25,'Bánffyhunyad');
INSERT INTO `varosok` VALUES (2826,0,0,25,'Barót');
INSERT INTO `varosok` VALUES (2827,0,0,25,'Bátos');
INSERT INTO `varosok` VALUES (2828,0,0,25,'Bélafalva');
INSERT INTO `varosok` VALUES (2829,0,0,25,'Mezőbikács');
INSERT INTO `varosok` VALUES (2830,0,0,25,'Bereck');
INSERT INTO `varosok` VALUES (2831,0,0,25,'Beszterce');
INSERT INTO `varosok` VALUES (2832,0,0,25,'Bethlen');
INSERT INTO `varosok` VALUES (2833,0,0,25,'Bólya');
INSERT INTO `varosok` VALUES (2834,0,0,25,'Bonchida');
INSERT INTO `varosok` VALUES (2835,0,0,25,'Vaskohsziklás');
INSERT INTO `varosok` VALUES (2836,0,0,25,'Borbánd');
INSERT INTO `varosok` VALUES (2837,0,0,25,'Bordos');
INSERT INTO `varosok` VALUES (2838,0,0,25,'Borszék');
INSERT INTO `varosok` VALUES (2839,0,0,25,'Bögöz');
INSERT INTO `varosok` VALUES (2840,0,0,25,'Brád');
INSERT INTO `varosok` VALUES (2841,0,0,25,'Brassó');
INSERT INTO `varosok` VALUES (2842,0,0,25,'Búzásbesenyő');
INSERT INTO `varosok` VALUES (2843,0,0,25,'Csatószeg');
INSERT INTO `varosok` VALUES (2844,0,0,25,'Csernakeresztúr');
INSERT INTO `varosok` VALUES (2845,0,0,25,'Csicsókeresztúr');
INSERT INTO `varosok` VALUES (2846,0,0,25,'Csíkcsicsó');
INSERT INTO `varosok` VALUES (2847,0,0,25,'Csíkdánfalva');
INSERT INTO `varosok` VALUES (2848,0,0,25,'Csíkdelne');
INSERT INTO `varosok` VALUES (2849,0,0,25,'Csíknagyboldogasszony');
INSERT INTO `varosok` VALUES (2850,0,0,25,'Kozmás');
INSERT INTO `varosok` VALUES (2851,0,0,25,'Csíkmadaras');
INSERT INTO `varosok` VALUES (2852,0,0,25,'Csíkmenaság');
INSERT INTO `varosok` VALUES (2853,0,0,25,'Csíkmindszent');
INSERT INTO `varosok` VALUES (2854,0,0,25,'Csíkrákos');
INSERT INTO `varosok` VALUES (2855,0,0,25,'Csíksomlyó (Miercurea Ciuc)');
INSERT INTO `varosok` VALUES (2856,0,0,25,'Csíkszentdomokos');
INSERT INTO `varosok` VALUES (2857,0,0,25,'Csíkszentgyörgy');
INSERT INTO `varosok` VALUES (2858,0,0,25,'Csíkszentimre');
INSERT INTO `varosok` VALUES (2859,0,0,25,'Csíkszentmihály');
INSERT INTO `varosok` VALUES (2860,0,0,25,'Csíkszentlélek');
INSERT INTO `varosok` VALUES (2861,0,0,25,'Csíkszentmárton');
INSERT INTO `varosok` VALUES (2862,0,0,25,'Görgényszentimre');
INSERT INTO `varosok` VALUES (2863,0,0,25,'Csíkszentmiklós');
INSERT INTO `varosok` VALUES (2864,0,0,25,'Csíkszentsimon');
INSERT INTO `varosok` VALUES (2865,0,0,25,'Csíkszenttamás');
INSERT INTO `varosok` VALUES (2866,0,0,25,'Csíkszereda');
INSERT INTO `varosok` VALUES (2867,0,0,25,'Csíktapolca');
INSERT INTO `varosok` VALUES (2868,0,0,25,'Deményháza');
INSERT INTO `varosok` VALUES (2869,0,0,25,'Dés');
INSERT INTO `varosok` VALUES (2870,0,0,25,'Déva');
INSERT INTO `varosok` VALUES (2871,0,0,25,'Dicsőszentmárton');
INSERT INTO `varosok` VALUES (2872,0,0,25,'Ditró');
INSERT INTO `varosok` VALUES (2873,0,0,25,'Egeres');
INSERT INTO `varosok` VALUES (2874,0,0,25,'Egrestő');
INSERT INTO `varosok` VALUES (2875,0,0,25,'Ehed');
INSERT INTO `varosok` VALUES (2876,0,0,25,'Erdőszentgyörgy');
INSERT INTO `varosok` VALUES (2877,0,0,25,'Erzsébetbánya');
INSERT INTO `varosok` VALUES (2878,0,0,25,'Esztelnek');
INSERT INTO `varosok` VALUES (2879,0,0,25,'Etéd');
INSERT INTO `varosok` VALUES (2880,0,0,25,'Farkaslaka');
INSERT INTO `varosok` VALUES (2881,0,0,25,'Feketehalom');
INSERT INTO `varosok` VALUES (2882,0,0,25,'Feltorja');
INSERT INTO `varosok` VALUES (2883,0,0,25,'Felvinc');
INSERT INTO `varosok` VALUES (2884,0,0,25,'Fenyéd');
INSERT INTO `varosok` VALUES (2885,0,0,25,'Fogaras');
INSERT INTO `varosok` VALUES (2886,0,0,25,'Gelence');
INSERT INTO `varosok` VALUES (2887,0,0,25,'Gödemesterháza');
INSERT INTO `varosok` VALUES (2888,0,0,25,'Görgénysüvegcsűr');
INSERT INTO `varosok` VALUES (2889,0,0,25,'Gyergyóbékás');
INSERT INTO `varosok` VALUES (2890,0,0,25,'Gyergyócsomafalva');
INSERT INTO `varosok` VALUES (2891,0,0,25,'Gyergyóhodos');
INSERT INTO `varosok` VALUES (2892,0,0,25,'Gyergyóremete');
INSERT INTO `varosok` VALUES (2893,0,0,25,'Gyergyószentmiklós');
INSERT INTO `varosok` VALUES (2894,0,0,25,'Gyergyótölgyes');
INSERT INTO `varosok` VALUES (2895,0,0,25,'Gyergyóújfalu');
INSERT INTO `varosok` VALUES (2896,0,0,25,'Gyimesbük');
INSERT INTO `varosok` VALUES (2897,0,0,25,'Gyimesfelsőlok');
INSERT INTO `varosok` VALUES (2898,0,0,25,'Gyimesközéplok');
INSERT INTO `varosok` VALUES (2899,0,0,25,'Györgyfalva');
INSERT INTO `varosok` VALUES (2900,0,0,25,'Gyulafehérvár');
INSERT INTO `varosok` VALUES (2901,0,0,25,'Harasztos');
INSERT INTO `varosok` VALUES (2902,0,0,25,'Hátszeg');
INSERT INTO `varosok` VALUES (2903,0,0,25,'Holcmány');
INSERT INTO `varosok` VALUES (2904,0,0,25,'Homoródkarácsonfalva');
INSERT INTO `varosok` VALUES (2905,0,0,25,'Homoródremete');
INSERT INTO `varosok` VALUES (2906,0,0,25,'Illyefalva');
INSERT INTO `varosok` VALUES (2907,0,0,25,'Imecsfalva');
INSERT INTO `varosok` VALUES (2908,0,0,25,'Jegenye');
INSERT INTO `varosok` VALUES (2909,0,0,25,'Jobbágyfalva');
INSERT INTO `varosok` VALUES (2910,0,0,25,'Jobbágytelke');
INSERT INTO `varosok` VALUES (2911,0,0,46,'Kellene');
INSERT INTO `varosok` VALUES (2912,0,0,46,'Szabadka (Subotica)');
INSERT INTO `varosok` VALUES (2913,0,0,46,'Kelebia');
INSERT INTO `varosok` VALUES (2914,0,0,46,'Bácsszőlős');
INSERT INTO `varosok` VALUES (2915,0,0,46,'Hajdújárás');
INSERT INTO `varosok` VALUES (2916,0,0,46,'Tavankut');
INSERT INTO `varosok` VALUES (2917,0,0,46,'Palics');
INSERT INTO `varosok` VALUES (2918,0,0,46,'Csantavér');
INSERT INTO `varosok` VALUES (2919,0,0,46,'Györgyén');
INSERT INTO `varosok` VALUES (2920,0,0,46,'Nagyfény');
INSERT INTO `varosok` VALUES (2921,0,0,46,'Bajmok');
INSERT INTO `varosok` VALUES (2922,0,0,46,'Kisbosznia');
INSERT INTO `varosok` VALUES (2923,0,0,26,'Ajnácskő');
INSERT INTO `varosok` VALUES (2924,0,0,26,'Béna');
INSERT INTO `varosok` VALUES (2925,0,0,26,'Berzéte');
INSERT INTO `varosok` VALUES (2926,0,0,26,'Bolgárom');
INSERT INTO `varosok` VALUES (2927,0,0,26,'Bolyk');
INSERT INTO `varosok` VALUES (2928,0,0,26,'Bottovo');
INSERT INTO `varosok` VALUES (2929,0,0,26,'Csákányháza');
INSERT INTO `varosok` VALUES (2930,0,0,26,'Csíz');
INSERT INTO `varosok` VALUES (2931,0,0,26,'Csoltó');
INSERT INTO `varosok` VALUES (2932,0,0,26,'Csomatelke');
INSERT INTO `varosok` VALUES (2933,0,0,26,'Deresk');
INSERT INTO `varosok` VALUES (2934,0,0,26,'Dernyő');
INSERT INTO `varosok` VALUES (2935,0,0,26,'Egyházasbást');
INSERT INTO `varosok` VALUES (2936,0,0,26,'Dobfenek');
INSERT INTO `varosok` VALUES (2937,0,0,26,'Feled');
INSERT INTO `varosok` VALUES (2938,0,0,26,'Fülek');
INSERT INTO `varosok` VALUES (2939,0,0,26,'Fülekkelecsény');
INSERT INTO `varosok` VALUES (2940,0,0,26,'Fülekkovácsi');
INSERT INTO `varosok` VALUES (2941,0,0,26,'Fülekpilís');
INSERT INTO `varosok` VALUES (2942,0,0,26,'Fülekpüspöki');
INSERT INTO `varosok` VALUES (2943,0,0,26,'Gömöralmágy');
INSERT INTO `varosok` VALUES (2944,0,0,26,'Gömörpanyit');
INSERT INTO `varosok` VALUES (2945,0,0,26,'Gömörsíd');
INSERT INTO `varosok` VALUES (2946,0,0,26,'Gömörújfalu');
INSERT INTO `varosok` VALUES (2947,0,0,26,'Hárskút');
INSERT INTO `varosok` VALUES (2948,0,0,26,'Ipolygalsa');
INSERT INTO `varosok` VALUES (2950,0,0,25,'Kadicsfalva');
INSERT INTO `varosok` VALUES (2951,0,0,25,'Kapnikbánya');
INSERT INTO `varosok` VALUES (2952,0,0,25,'Kápolnásfalu');
INSERT INTO `varosok` VALUES (2953,0,0,25,'Kászonjakabfalva');
INSERT INTO `varosok` VALUES (2954,0,0,25,'Kászonújfalu');
INSERT INTO `varosok` VALUES (2955,0,0,25,'Katona');
INSERT INTO `varosok` VALUES (2956,0,0,25,'Kerelőszentpál');
INSERT INTO `varosok` VALUES (2957,0,0,25,'Nagyszántó');
INSERT INTO `varosok` VALUES (2958,0,0,25,'Kézdikővár');
INSERT INTO `varosok` VALUES (2959,0,0,25,'Kézdimartonos');
INSERT INTO `varosok` VALUES (2960,0,0,25,'Kézdisárfalva');
INSERT INTO `varosok` VALUES (2961,0,0,25,'Kézdiszárazpatak');
INSERT INTO `varosok` VALUES (2962,0,0,25,'Kézdiszentkereszt');
INSERT INTO `varosok` VALUES (2963,0,0,25,'Kézdiszentlélek');
INSERT INTO `varosok` VALUES (2964,0,0,25,'Kézdivásárhely');
INSERT INTO `varosok` VALUES (2965,0,0,25,'Kide');
INSERT INTO `varosok` VALUES (2966,0,0,25,'Kilyénfalva');
INSERT INTO `varosok` VALUES (2967,0,0,25,'Kisbács');
INSERT INTO `varosok` VALUES (2968,0,0,25,'Kiskapus');
INSERT INTO `varosok` VALUES (2969,0,0,25,'Kolozs');
INSERT INTO `varosok` VALUES (2970,0,0,25,'Kolozsvár (Cluj-Napoca)');
INSERT INTO `varosok` VALUES (2971,0,0,25,'Korond');
INSERT INTO `varosok` VALUES (2972,0,0,25,'Kostelek');
INSERT INTO `varosok` VALUES (2973,0,0,25,'Kovászna');
INSERT INTO `varosok` VALUES (2974,0,0,25,'Kőhalom');
INSERT INTO `varosok` VALUES (2975,0,0,25,'Körösbánya');
INSERT INTO `varosok` VALUES (2976,0,0,25,'Kudzsir');
INSERT INTO `varosok` VALUES (2977,0,0,25,'Küküllőkeményfalva');
INSERT INTO `varosok` VALUES (2978,0,0,25,'Lázárfalva');
INSERT INTO `varosok` VALUES (2979,0,0,25,'Lemhény');
INSERT INTO `varosok` VALUES (2980,0,0,25,'Lónyaitelep');
INSERT INTO `varosok` VALUES (2981,0,0,25,'Lövéte');
INSERT INTO `varosok` VALUES (2982,0,0,25,'Lupény');
INSERT INTO `varosok` VALUES (2983,0,0,25,'Madéfalva');
INSERT INTO `varosok` VALUES (2984,0,0,25,'Magyarlápos');
INSERT INTO `varosok` VALUES (2985,0,0,25,'Magyarszarvaskend');
INSERT INTO `varosok` VALUES (2986,0,0,25,'Magyarzsákod');
INSERT INTO `varosok` VALUES (2987,0,0,25,'Málnásfürdő');
INSERT INTO `varosok` VALUES (2988,0,0,25,'Máréfalva');
INSERT INTO `varosok` VALUES (2989,0,0,25,'Marosfő');
INSERT INTO `varosok` VALUES (2990,0,0,25,'Maroshévíz');
INSERT INTO `varosok` VALUES (2991,0,0,25,'Marosillye');
INSERT INTO `varosok` VALUES (2992,0,0,25,'Marosjára');
INSERT INTO `varosok` VALUES (2993,0,0,25,'Marosszentgyörgy');
INSERT INTO `varosok` VALUES (2994,0,0,25,'Marosújvár');
INSERT INTO `varosok` VALUES (2995,0,0,25,'Marosvásárhely (Târgu Mureș)');
INSERT INTO `varosok` VALUES (2996,0,0,25,'Medgyes');
INSERT INTO `varosok` VALUES (2997,0,0,25,'Mezősámsond');
INSERT INTO `varosok` VALUES (2998,0,0,25,'Mezőszengyel');
INSERT INTO `varosok` VALUES (2999,0,0,25,'Mikháza');
INSERT INTO `varosok` VALUES (3000,0,0,25,'Miklósvár');
INSERT INTO `varosok` VALUES (3001,0,0,25,'Mikóújfalu');
INSERT INTO `varosok` VALUES (3002,0,0,25,'Mócs');
INSERT INTO `varosok` VALUES (3003,0,0,25,'Nagyenyed');
INSERT INTO `varosok` VALUES (3004,0,0,25,'Nagyernye');
INSERT INTO `varosok` VALUES (3005,0,0,25,'Nagykászon');
INSERT INTO `varosok` VALUES (3006,0,0,25,'Nagyszeben');
INSERT INTO `varosok` VALUES (3007,0,0,25,'Nyárádköszvényes');
INSERT INTO `varosok` VALUES (3008,0,0,25,'Nyárádremete');
INSERT INTO `varosok` VALUES (3009,0,0,25,'Nyárádselye');
INSERT INTO `varosok` VALUES (3010,0,0,25,'Nyárádtő');
INSERT INTO `varosok` VALUES (3011,0,0,25,'Nyárádszereda');
INSERT INTO `varosok` VALUES (3012,0,0,25,'Nyikómalomfalva');
INSERT INTO `varosok` VALUES (3013,0,0,25,'Nyújtód');
INSERT INTO `varosok` VALUES (3014,0,0,25,'Ojtoz');
INSERT INTO `varosok` VALUES (3015,0,0,25,'Óradna');
INSERT INTO `varosok` VALUES (3016,0,0,25,'Orlát');
INSERT INTO `varosok` VALUES (3017,0,0,25,'Oroszhegy');
INSERT INTO `varosok` VALUES (3018,0,0,25,'Orotva');
INSERT INTO `varosok` VALUES (3019,0,0,25,'Ozsdola');
INSERT INTO `varosok` VALUES (3020,0,0,25,'Pálpataka');
INSERT INTO `varosok` VALUES (3021,0,0,25,'Parajd (Praid)');
INSERT INTO `varosok` VALUES (3022,0,0,25,'Petrilla');
INSERT INTO `varosok` VALUES (3023,0,0,25,'Petrozsény');
INSERT INTO `varosok` VALUES (3024,0,0,25,'Piskitelep');
INSERT INTO `varosok` VALUES (3025,0,0,25,'Pusztakalán');
INSERT INTO `varosok` VALUES (3026,0,0,25,'Radnalajosfalva');
INSERT INTO `varosok` VALUES (3027,0,0,25,'Radnót');
INSERT INTO `varosok` VALUES (3028,0,0,25,'Segesvár');
INSERT INTO `varosok` VALUES (3029,0,0,25,'Sepsibükszád');
INSERT INTO `varosok` VALUES (3030,0,0,25,'Sepsiköröspatak');
INSERT INTO `varosok` VALUES (3031,0,0,25,'Sepsiszentgyörgy');
INSERT INTO `varosok` VALUES (3032,0,0,25,'Sinfalva');
INSERT INTO `varosok` VALUES (3033,0,0,25,'Szamosújvár');
INSERT INTO `varosok` VALUES (3034,0,0,25,'Szárhegy');
INSERT INTO `varosok` VALUES (3035,0,0,25,'Szászfenes');
INSERT INTO `varosok` VALUES (3036,0,0,25,'Szászrégen');
INSERT INTO `varosok` VALUES (3037,0,0,25,'Szászsebes');
INSERT INTO `varosok` VALUES (3038,0,0,25,'Szászváros');
INSERT INTO `varosok` VALUES (3039,0,0,25,'Szék');
INSERT INTO `varosok` VALUES (3040,0,0,25,'Székelykál');
INSERT INTO `varosok` VALUES (3041,0,0,25,'Székelykeresztúr');
INSERT INTO `varosok` VALUES (3042,0,0,25,'Székelylengyelfalva');
INSERT INTO `varosok` VALUES (3043,0,0,25,'Székelypálfalva');
INSERT INTO `varosok` VALUES (3044,0,0,25,'Székelyszentkirály');
INSERT INTO `varosok` VALUES (3045,0,0,25,'Székelyszentlélek');
INSERT INTO `varosok` VALUES (3046,0,0,25,'Szenttamás');
INSERT INTO `varosok` VALUES (3047,0,0,25,'Székelyudvarhely');
INSERT INTO `varosok` VALUES (3048,0,0,25,'Székelyvarság');
INSERT INTO `varosok` VALUES (3049,0,0,25,'Székelyvécke');
INSERT INTO `varosok` VALUES (3050,0,0,25,'Szentágota');
INSERT INTO `varosok` VALUES (3051,0,0,25,'Szentdemeter');
INSERT INTO `varosok` VALUES (3052,0,0,25,'Szentegyházasfalu');
INSERT INTO `varosok` VALUES (3053,0,0,25,'Szentháromság');
INSERT INTO `varosok` VALUES (3054,0,0,25,'Szentivánlaborfalva');
INSERT INTO `varosok` VALUES (3055,0,0,25,'Szentkatolna');
INSERT INTO `varosok` VALUES (3056,0,0,25,'Szentkeresztbánya');
INSERT INTO `varosok` VALUES (3057,0,0,25,'Szépvíz');
INSERT INTO `varosok` VALUES (3058,0,0,25,'Szováta');
INSERT INTO `varosok` VALUES (3059,0,0,25,'Szőkefalva');
INSERT INTO `varosok` VALUES (3060,0,0,25,'Teke');
INSERT INTO `varosok` VALUES (3061,0,0,25,'Tekerőpatak');
INSERT INTO `varosok` VALUES (3062,0,0,25,'Torda');
INSERT INTO `varosok` VALUES (3063,0,0,25,'Tövis');
INSERT INTO `varosok` VALUES (3064,0,0,25,'Tusnád');
INSERT INTO `varosok` VALUES (3065,0,0,25,'Tusnádfürdő');
INSERT INTO `varosok` VALUES (3066,0,0,25,'Tür');
INSERT INTO `varosok` VALUES (3067,0,0,25,'Türkös');
INSERT INTO `varosok` VALUES (3068,0,0,25,'Újtusnád');
INSERT INTO `varosok` VALUES (3069,0,0,25,'Uzon');
INSERT INTO `varosok` VALUES (3070,0,0,25,'Vágás');
INSERT INTO `varosok` VALUES (3071,0,0,25,'Vajdahunyad');
INSERT INTO `varosok` VALUES (3072,0,0,25,'Verespatak');
INSERT INTO `varosok` VALUES (3073,0,0,25,'Vice');
INSERT INTO `varosok` VALUES (3074,0,0,25,'Vulkán');
INSERT INTO `varosok` VALUES (3075,0,0,25,'Zabola');
INSERT INTO `varosok` VALUES (3076,0,0,25,'Zágon');
INSERT INTO `varosok` VALUES (3077,0,0,25,'Zalatna');
INSERT INTO `varosok` VALUES (3078,0,0,25,'Zernyest');
INSERT INTO `varosok` VALUES (3079,0,0,25,'Zetelaka');
INSERT INTO `varosok` VALUES (3080,0,0,25,'Zeteváralja');
INSERT INTO `varosok` VALUES (3081,0,0,25,'Zsögöd');
INSERT INTO `varosok` VALUES (3082,8991,20,12,'Böde');
INSERT INTO `varosok` VALUES (3083,4090,9,12,'Folyás');
INSERT INTO `varosok` VALUES (3084,0,0,12,'');
INSERT INTO `varosok` VALUES (3085,0,0,25,'Erzsébetváros');
INSERT INTO `varosok` VALUES (3086,0,0,25,'Nagybacon');
INSERT INTO `varosok` VALUES (3087,0,0,25,'Boica');
INSERT INTO `varosok` VALUES (3088,0,0,25,'Krisztyor-Gurabárza');
INSERT INTO `varosok` VALUES (3089,0,0,25,'Magyarigen (Ighiu)');
INSERT INTO `varosok` VALUES (3090,0,0,25,'Torockószentgyörgy');
INSERT INTO `varosok` VALUES (3091,0,0,25,'Balázsfalva');
INSERT INTO `varosok` VALUES (3092,0,0,25,'Felek');
INSERT INTO `varosok` VALUES (3093,0,0,25,'Nagydisznód');
INSERT INTO `varosok` VALUES (3094,0,0,25,'Verestorony');
INSERT INTO `varosok` VALUES (3095,0,0,25,'Nagytalmács');
INSERT INTO `varosok` VALUES (3096,8995,20,12,'Keménfa');
INSERT INTO `varosok` VALUES (3097,8998,20,12,'Ozmánbük');
INSERT INTO `varosok` VALUES (3098,8998,20,12,'Vaspör');
INSERT INTO `varosok` VALUES (3099,3341,10,12,'Szúcs');
INSERT INTO `varosok` VALUES (3100,0,0,26,'Abafalva');
INSERT INTO `varosok` VALUES (3101,0,0,26,'Barca');
INSERT INTO `varosok` VALUES (3102,0,0,26,'Szentkirály');
INSERT INTO `varosok` VALUES (3103,0,0,26,'Füge');
INSERT INTO `varosok` VALUES (3104,0,0,26,'Barka');
INSERT INTO `varosok` VALUES (3105,0,0,26,'Lucska');
INSERT INTO `varosok` VALUES (3106,0,0,26,'Kovácspatak');
INSERT INTO `varosok` VALUES (3107,0,0,26,'Méhész');
INSERT INTO `varosok` VALUES (3108,0,0,26,'Velkenye');
INSERT INTO `varosok` VALUES (3109,0,0,26,'Lénártfalva');
INSERT INTO `varosok` VALUES (3110,0,0,26,'Nagydaróc');
INSERT INTO `varosok` VALUES (3111,0,0,26,'Guszona');
INSERT INTO `varosok` VALUES (3112,0,0,26,'Magyarhegymeg');
INSERT INTO `varosok` VALUES (3113,3053,13,12,'Kozárd');
INSERT INTO `varosok` VALUES (3114,3067,13,12,'Garáb');
INSERT INTO `varosok` VALUES (3115,2694,13,12,'Cserháthaláp');
INSERT INTO `varosok` VALUES (3116,2212,14,12,'Csévharaszt');
INSERT INTO `varosok` VALUES (3117,2145,14,12,'Szilasliget');
INSERT INTO `varosok` VALUES (3118,0,0,26,'Ragyolc');
INSERT INTO `varosok` VALUES (3119,2658,13,12,'Pusztaberki');
INSERT INTO `varosok` VALUES (3120,0,0,25,'Mikeszásza');
INSERT INTO `varosok` VALUES (3121,0,0,25,'Hosszúaszó');
INSERT INTO `varosok` VALUES (3122,0,0,25,'Somogyom');
INSERT INTO `varosok` VALUES (3123,0,0,25,'Fehéregyháza');
INSERT INTO `varosok` VALUES (3124,0,0,25,'Almakerék');
INSERT INTO `varosok` VALUES (3125,0,0,25,'Sárpatak');
INSERT INTO `varosok` VALUES (3126,0,0,25,'Székelyhidegkút');
INSERT INTO `varosok` VALUES (3127,0,0,26,'Bolyk-puszta');
INSERT INTO `varosok` VALUES (3128,0,0,25,'Vargyas');
INSERT INTO `varosok` VALUES (3129,0,0,26,'Pinc');
INSERT INTO `varosok` VALUES (3130,0,0,25,'Köpec');
INSERT INTO `varosok` VALUES (3131,0,0,25,'Uzonkafürdő');
INSERT INTO `varosok` VALUES (3132,0,0,25,'Szotyor');
INSERT INTO `varosok` VALUES (3133,0,0,25,'Homoróddaróc');
INSERT INTO `varosok` VALUES (3134,0,0,25,'Alsórákos');
INSERT INTO `varosok` VALUES (3135,0,0,25,'Olthévíz');
INSERT INTO `varosok` VALUES (3136,0,0,25,'Ürmös');
INSERT INTO `varosok` VALUES (3137,0,0,25,'Nagyajta');
INSERT INTO `varosok` VALUES (3138,0,0,25,'Zalánpatak');
INSERT INTO `varosok` VALUES (3139,0,0,25,'Olasztelek');
INSERT INTO `varosok` VALUES (3140,0,0,25,'Sepsiszentkirály');
INSERT INTO `varosok` VALUES (3141,0,0,26,'Lévárt');
INSERT INTO `varosok` VALUES (3142,0,0,25,'Sepsibodok');
INSERT INTO `varosok` VALUES (3143,0,0,25,'Oltszem');
INSERT INTO `varosok` VALUES (3144,0,0,25,'Árapatak');
INSERT INTO `varosok` VALUES (3145,0,0,25,'Előpatak');
INSERT INTO `varosok` VALUES (3146,0,0,26,'Várgede');
INSERT INTO `varosok` VALUES (3147,0,0,26,'Balogfalva');
INSERT INTO `varosok` VALUES (3148,0,0,26,'Kisgömöri');
INSERT INTO `varosok` VALUES (3149,0,0,26,'Kerekgede');
INSERT INTO `varosok` VALUES (3150,0,0,26,'Korláti');
INSERT INTO `varosok` VALUES (3151,0,0,26,'Jászó');
INSERT INTO `varosok` VALUES (3152,0,0,26,'Krasznahorkaváralja');
INSERT INTO `varosok` VALUES (3153,0,0,26,'Várhosszúrét');
INSERT INTO `varosok` VALUES (3154,0,0,26,'Jólész');
INSERT INTO `varosok` VALUES (3155,0,0,26,'Losonc');
INSERT INTO `varosok` VALUES (3156,0,0,26,'Miksi');
INSERT INTO `varosok` VALUES (3157,0,0,26,'Rimaszécs');
INSERT INTO `varosok` VALUES (3158,0,0,26,'Rimaszombat');
INSERT INTO `varosok` VALUES (3159,0,0,26,'Vilke');
INSERT INTO `varosok` VALUES (3160,0,0,26,'Rozsnyó');
INSERT INTO `varosok` VALUES (3161,0,0,26,'Csucsom');
INSERT INTO `varosok` VALUES (3162,0,0,26,'Berzétekőrös');
INSERT INTO `varosok` VALUES (3163,0,0,26,'Perse');
INSERT INTO `varosok` VALUES (3164,0,0,26,'Sőreg');
INSERT INTO `varosok` VALUES (3165,0,0,26,'Tornalja');
INSERT INTO `varosok` VALUES (3166,0,0,26,'Torna');
INSERT INTO `varosok` VALUES (3167,0,0,25,'Kilyén');
INSERT INTO `varosok` VALUES (3168,0,0,25,'Szépmező');
INSERT INTO `varosok` VALUES (3169,0,0,25,'Bodola');
INSERT INTO `varosok` VALUES (3170,0,0,25,'Keresztvár');
INSERT INTO `varosok` VALUES (3171,0,0,25,'Tatrang');
INSERT INTO `varosok` VALUES (3172,0,0,25,'Kökös');
INSERT INTO `varosok` VALUES (3173,0,0,25,'Lisznyó');
INSERT INTO `varosok` VALUES (3174,0,0,25,'Lisznyópatak');
INSERT INTO `varosok` VALUES (3175,0,0,25,'Barcarozsnyó');
INSERT INTO `varosok` VALUES (3176,0,0,25,'Kézdimárkosfalva');
INSERT INTO `varosok` VALUES (3177,0,0,25,'Felsőcsernáton');
INSERT INTO `varosok` VALUES (3178,0,0,25,'Dálnok');
INSERT INTO `varosok` VALUES (3179,0,0,25,'Kurtapatak');
INSERT INTO `varosok` VALUES (3180,0,0,25,'Futásfalva');
INSERT INTO `varosok` VALUES (3181,0,0,25,'Ikafalva');
INSERT INTO `varosok` VALUES (3182,0,0,25,'Haraly');
INSERT INTO `varosok` VALUES (3183,0,0,25,'Hilib');
INSERT INTO `varosok` VALUES (3184,0,0,25,'Petőfalva');
INSERT INTO `varosok` VALUES (3185,0,0,25,'Tamásfalva');
INSERT INTO `varosok` VALUES (3186,0,0,25,'Szörcse');
INSERT INTO `varosok` VALUES (3187,0,0,25,'Kézdicsomortán');
INSERT INTO `varosok` VALUES (3188,0,0,25,'Válaszút');
INSERT INTO `varosok` VALUES (3189,0,0,25,'Nagysármás');
INSERT INTO `varosok` VALUES (3190,0,0,25,'Tordatúr');
INSERT INTO `varosok` VALUES (3191,0,0,25,'Magyarfenes');
INSERT INTO `varosok` VALUES (3192,0,0,25,'Gyalu');
INSERT INTO `varosok` VALUES (3193,0,0,25,'Szamosfalva');
INSERT INTO `varosok` VALUES (3194,0,0,25,'Désakna');
INSERT INTO `varosok` VALUES (3195,0,0,25,'Marosludas');
INSERT INTO `varosok` VALUES (3196,0,0,25,'Csöb');
INSERT INTO `varosok` VALUES (3197,0,0,25,'Gyulakuta');
INSERT INTO `varosok` VALUES (3198,0,0,25,'Ádámos');
INSERT INTO `varosok` VALUES (3199,0,0,25,'Küküllővár');
INSERT INTO `varosok` VALUES (3200,0,0,25,'Marosugra');
INSERT INTO `varosok` VALUES (3201,0,0,25,'Kóród');
INSERT INTO `varosok` VALUES (3202,0,0,25,'Kutyfalva');
INSERT INTO `varosok` VALUES (3203,0,0,25,'Maroscsapó');
INSERT INTO `varosok` VALUES (3204,0,0,25,'Vaskoh');
INSERT INTO `varosok` VALUES (3205,0,0,25,'Mikefalva');
INSERT INTO `varosok` VALUES (3206,0,0,25,'Héjjasfalva');
INSERT INTO `varosok` VALUES (3207,3163,13,12,'Szalmatercs');
INSERT INTO `varosok` VALUES (3208,0,0,25,'Dedrád');
INSERT INTO `varosok` VALUES (3209,0,0,25,'Vajola');
INSERT INTO `varosok` VALUES (3210,0,0,25,'Búzaháza');
INSERT INTO `varosok` VALUES (3211,0,0,25,'Székelysárd');
INSERT INTO `varosok` VALUES (3212,0,0,25,'Székelyhodos');
INSERT INTO `varosok` VALUES (3213,0,0,25,'Jedd');
INSERT INTO `varosok` VALUES (3214,0,0,25,'Tófalva');
INSERT INTO `varosok` VALUES (3215,0,0,25,'Sáromberke');
INSERT INTO `varosok` VALUES (3216,0,0,25,'Marossárpatak');
INSERT INTO `varosok` VALUES (3217,0,0,25,'Póka');
INSERT INTO `varosok` VALUES (3218,0,0,25,'Maroskeresztúr');
INSERT INTO `varosok` VALUES (3219,0,0,25,'Vadad');
INSERT INTO `varosok` VALUES (3220,0,0,25,'Székes');
INSERT INTO `varosok` VALUES (3221,0,0,25,'Erdőcsinád');
INSERT INTO `varosok` VALUES (3222,0,0,25,'Iszló');
INSERT INTO `varosok` VALUES (3223,0,0,25,'Nagyvárad (Oradea)');
INSERT INTO `varosok` VALUES (3224,0,0,25,'Hargitafürdő');
INSERT INTO `varosok` VALUES (3225,0,0,25,'Bihar');
INSERT INTO `varosok` VALUES (3226,0,0,25,'Csíkszentkirály');
INSERT INTO `varosok` VALUES (3227,0,0,25,'Szécseny');
INSERT INTO `varosok` VALUES (3228,8272,19,12,'Óbudavár');
INSERT INTO `varosok` VALUES (3229,8272,19,12,'Szentjakabfa');
INSERT INTO `varosok` VALUES (3230,8272,19,12,'Tagyon');
INSERT INTO `varosok` VALUES (3231,0,0,26,'Nádszeg');
INSERT INTO `varosok` VALUES (3232,0,0,25,'Kaplony');
INSERT INTO `varosok` VALUES (3233,0,0,25,'Borzont');
INSERT INTO `varosok` VALUES (3234,0,0,25,'Háromkút');
INSERT INTO `varosok` VALUES (3235,0,0,25,'Gyergyódomokos');
INSERT INTO `varosok` VALUES (3236,0,0,25,'Magyarbékás');
INSERT INTO `varosok` VALUES (3237,0,0,25,'Csutakfalva');
INSERT INTO `varosok` VALUES (3238,0,0,25,'Marosfalva');
INSERT INTO `varosok` VALUES (3239,0,0,25,'Marosnyír');
INSERT INTO `varosok` VALUES (3240,0,0,25,'Vasláb');
INSERT INTO `varosok` VALUES (3241,0,0,25,'Güdüc');
INSERT INTO `varosok` VALUES (3242,0,0,26,'Érsekújvár (Nové Zámky)');
INSERT INTO `varosok` VALUES (3243,0,0,47,'Beregszász');
INSERT INTO `varosok` VALUES (3244,0,0,47,'Beregújfalu');
INSERT INTO `varosok` VALUES (3245,0,0,47,'Borzsava');
INSERT INTO `varosok` VALUES (3246,0,0,47,'Macsola');
INSERT INTO `varosok` VALUES (3247,0,0,47,'Muzsaly');
INSERT INTO `varosok` VALUES (3248,0,0,47,'Bene');
INSERT INTO `varosok` VALUES (3249,0,0,47,'Mezőkaszony');
INSERT INTO `varosok` VALUES (3250,0,0,47,'Nagybakos');
INSERT INTO `varosok` VALUES (3251,0,0,47,'Bótrágy');
INSERT INTO `varosok` VALUES (3252,0,0,47,'Nagybégány');
INSERT INTO `varosok` VALUES (3253,0,0,47,'Sárosoroszi');
INSERT INTO `varosok` VALUES (3254,0,0,47,'Técső');
INSERT INTO `varosok` VALUES (3255,0,0,47,'Huszt');
INSERT INTO `varosok` VALUES (3256,0,0,47,'Kerekhegy');
INSERT INTO `varosok` VALUES (3257,0,0,47,'Bustyaháza');
INSERT INTO `varosok` VALUES (3258,0,0,47,'Királymező');
INSERT INTO `varosok` VALUES (3259,0,0,47,'Németmokra');
INSERT INTO `varosok` VALUES (3260,0,0,47,'Visk');
INSERT INTO `varosok` VALUES (3261,0,0,47,'Munkács');
INSERT INTO `varosok` VALUES (3262,0,0,25,'Felsőboldogfalva');
INSERT INTO `varosok` VALUES (3263,0,0,47,'Alsókerepec');
INSERT INTO `varosok` VALUES (3264,0,0,25,'Bikafalva');
INSERT INTO `varosok` VALUES (3265,8242,19,12,'Veszprémfajsz');
INSERT INTO `varosok` VALUES (3266,0,0,25,'Székelyszenttamás');
INSERT INTO `varosok` VALUES (3267,3985,4,12,'Felsőberecki');
INSERT INTO `varosok` VALUES (3268,0,0,46,'Adorján');
INSERT INTO `varosok` VALUES (3269,7756,2,12,'Pócsa');
INSERT INTO `varosok` VALUES (3270,0,0,47,'Ungvár');
INSERT INTO `varosok` VALUES (3271,8879,20,12,'Kerkateskánd');
INSERT INTO `varosok` VALUES (3272,8445,19,12,'Csehbánya');
INSERT INTO `varosok` VALUES (3273,0,0,25,'Nyüved');
INSERT INTO `varosok` VALUES (3274,0,0,25,'Élesd');
INSERT INTO `varosok` VALUES (3275,0,0,25,'Feketeerdő');
INSERT INTO `varosok` VALUES (3276,0,0,25,'Fugyivásárhely');
INSERT INTO `varosok` VALUES (3277,0,0,25,'Váradhegyalja');
INSERT INTO `varosok` VALUES (3278,0,0,25,'Hegyközcsatár');
INSERT INTO `varosok` VALUES (3279,0,0,25,'Hegyközújlak');
INSERT INTO `varosok` VALUES (3280,0,0,25,'Hegyközpályi');
INSERT INTO `varosok` VALUES (3281,0,0,25,'Pelbárthida');
INSERT INTO `varosok` VALUES (3282,0,0,25,'Köröstarján');
INSERT INTO `varosok` VALUES (3283,0,0,25,'Magyarcséke');
INSERT INTO `varosok` VALUES (3284,0,0,25,'Mezőtelegd');
INSERT INTO `varosok` VALUES (3285,0,0,25,'Szalárd');
INSERT INTO `varosok` VALUES (3286,0,0,25,'Sólyomkővár');
INSERT INTO `varosok` VALUES (3287,0,0,25,'Váradszentmárton');
INSERT INTO `varosok` VALUES (3288,0,0,25,'Újpalota');
INSERT INTO `varosok` VALUES (3289,8994,20,12,'Kávás');
INSERT INTO `varosok` VALUES (3290,0,0,26,'Kalonda');
INSERT INTO `varosok` VALUES (3291,0,0,26,'Rapp');
INSERT INTO `varosok` VALUES (3292,0,0,26,'Kismúlyad');
INSERT INTO `varosok` VALUES (3293,0,0,26,'Terbeléd');
INSERT INTO `varosok` VALUES (3294,0,0,26,'Mucsény');
INSERT INTO `varosok` VALUES (3295,0,0,26,'Fülekpilis');
INSERT INTO `varosok` VALUES (3296,0,0,26,'Panyidaróc');
INSERT INTO `varosok` VALUES (3297,0,0,26,'Tőrincs');
INSERT INTO `varosok` VALUES (3298,3780,4,12,'Balajt');
INSERT INTO `varosok` VALUES (3299,3989,4,12,'Alsóregmec');
INSERT INTO `varosok` VALUES (3300,3881,4,12,'Baskó');
INSERT INTO `varosok` VALUES (3301,3994,4,12,'Filkeháza');
INSERT INTO `varosok` VALUES (3302,3893,4,12,'Mogyoróska');
INSERT INTO `varosok` VALUES (3303,0,0,25,'Máriaradna');
INSERT INTO `varosok` VALUES (3304,3821,4,12,'Szászfa');
INSERT INTO `varosok` VALUES (3305,0,0,46,'Becse');
INSERT INTO `varosok` VALUES (3306,0,0,46,'Mohol');
INSERT INTO `varosok` VALUES (3307,0,0,46,'Péterréve');
INSERT INTO `varosok` VALUES (3308,0,0,46,'Csúrog');
INSERT INTO `varosok` VALUES (3309,0,0,46,'Bácsföldvár');
INSERT INTO `varosok` VALUES (3310,0,0,46,'Ada');
INSERT INTO `varosok` VALUES (3311,3965,4,12,'Kisrozvágy');
INSERT INTO `varosok` VALUES (3312,0,0,1,'Máriazell');
INSERT INTO `varosok` VALUES (3313,3765,4,12,'Tornabarakony');
INSERT INTO `varosok` VALUES (3314,0,0,26,'Pozsony');
INSERT INTO `varosok` VALUES (3315,0,0,1,'Frauenkirchen (Boldogasszony)');
INSERT INTO `varosok` VALUES (3316,3989,4,12,'Felsőregmec');
INSERT INTO `varosok` VALUES (3317,3893,4,12,'Regéc');
INSERT INTO `varosok` VALUES (3318,3786,4,12,'Irota');
INSERT INTO `varosok` VALUES (3319,3825,4,12,'Viszló');
INSERT INTO `varosok` VALUES (3320,3825,4,12,'Debréte');
INSERT INTO `varosok` VALUES (3321,3809,4,12,'Abaújszolnok');
INSERT INTO `varosok` VALUES (3322,3837,4,12,'Alsógagy');
INSERT INTO `varosok` VALUES (3323,3815,4,12,'Abaújlak');
INSERT INTO `varosok` VALUES (3324,3821,4,12,'Kány');
INSERT INTO `varosok` VALUES (3325,3821,4,12,'Büttös');
INSERT INTO `varosok` VALUES (3326,3821,4,12,'Perecse');
INSERT INTO `varosok` VALUES (3327,3752,4,12,'Galvács');
INSERT INTO `varosok` VALUES (3328,3754,4,12,'Meszes');
INSERT INTO `varosok` VALUES (3329,3735,4,12,'Alsótelekes');
INSERT INTO `varosok` VALUES (3330,0,0,12,'');
INSERT INTO `varosok` VALUES (3331,0,0,25,'Szolnok');
INSERT INTO `varosok` VALUES (3332,7394,2,12,'zzz üres');
INSERT INTO `varosok` VALUES (3333,8471,19,12,'Nemeshany');
INSERT INTO `varosok` VALUES (3334,8471,19,12,'Bodorfa');
INSERT INTO `varosok` VALUES (3335,3844,4,12,'Szentistvánbaksa');
INSERT INTO `varosok` VALUES (3336,3885,4,12,'Arka');
INSERT INTO `varosok` VALUES (3337,3768,4,12,'Bódvalenke');
INSERT INTO `varosok` VALUES (3338,3648,4,12,'Lénárddaróc');
INSERT INTO `varosok` VALUES (3339,3648,4,12,'Bükkmogyorósd');
INSERT INTO `varosok` VALUES (3340,7932,2,12,'Szulimán');
INSERT INTO `varosok` VALUES (3341,7932,2,12,'Almáskeresztúr');
INSERT INTO `varosok` VALUES (3342,7935,2,12,'Horváthertelend');
INSERT INTO `varosok` VALUES (3343,7935,2,12,'Csebény');
INSERT INTO `varosok` VALUES (3344,3795,4,12,'Nyomár');
INSERT INTO `varosok` VALUES (3345,3756,4,12,'Varbóc');
INSERT INTO `varosok` VALUES (3346,3712,4,12,'Sajósenye');
INSERT INTO `varosok` VALUES (3347,8597,19,12,'Döbrönte');
INSERT INTO `varosok` VALUES (3348,8581,19,12,'Németbánya');
INSERT INTO `varosok` VALUES (3349,3721,4,12,'Dövény');
INSERT INTO `varosok` VALUES (3350,3726,4,12,'Szuhafő');
INSERT INTO `varosok` VALUES (3351,3346,10,12,'Bükkszentmárton');
INSERT INTO `varosok` VALUES (3352,3672,4,12,'Kissikátor');
INSERT INTO `varosok` VALUES (3353,3356,4,12,'Sajómercse');
INSERT INTO `varosok` VALUES (3354,3866,4,12,'Litka');
INSERT INTO `varosok` VALUES (3355,3874,4,12,'Pusztaradvány');
INSERT INTO `varosok` VALUES (3356,3821,4,12,'Pamlény');
INSERT INTO `varosok` VALUES (3357,3821,4,12,'Keresztéte');
INSERT INTO `varosok` VALUES (3358,3578,4,12,'Kiscsécs');
INSERT INTO `varosok` VALUES (3359,3994,4,12,'Bózsva');
INSERT INTO `varosok` VALUES (3360,3994,4,12,'Nagyhuta');
INSERT INTO `varosok` VALUES (3361,3994,4,12,'Vágáshuta');
INSERT INTO `varosok` VALUES (3362,3997,4,12,'Nyíri');
INSERT INTO `varosok` VALUES (3363,3721,4,12,'Jákfalva');
INSERT INTO `varosok` VALUES (3364,3608,4,12,'Farkaslyuk');
INSERT INTO `varosok` VALUES (3365,8357,20,12,'Döbröce');
INSERT INTO `varosok` VALUES (3366,8356,20,12,'Nagygörbő');
INSERT INTO `varosok` VALUES (3367,7253,15,12,'Szabadi');
INSERT INTO `varosok` VALUES (3368,7432,15,12,'Csombárd');
INSERT INTO `varosok` VALUES (3369,7523,15,12,'Kisasszond');
INSERT INTO `varosok` VALUES (3370,7477,15,12,'Patalom');
INSERT INTO `varosok` VALUES (3371,7258,15,12,'Kaposkeresztúr');
INSERT INTO `varosok` VALUES (3372,7261,15,12,'Kaposhomok');
INSERT INTO `varosok` VALUES (3373,7473,15,12,'Kaposgyarmat');
INSERT INTO `varosok` VALUES (3374,7473,15,12,'Hajmás');
INSERT INTO `varosok` VALUES (3375,7474,15,12,'Zselicszentpál');
INSERT INTO `varosok` VALUES (3376,7472,15,12,'Cserénfa');
INSERT INTO `varosok` VALUES (3431,0,0,25,'Kakszentmárton');
INSERT INTO `varosok` VALUES (3378,0,0,25,'Nagymajtény');
INSERT INTO `varosok` VALUES (3379,0,0,25,'Hágótő');
INSERT INTO `varosok` VALUES (3380,0,0,25,'Holló');
INSERT INTO `varosok` VALUES (3381,8777,20,12,'Fűzvölgy');
INSERT INTO `varosok` VALUES (3382,9715,15,12,'Gadány');
INSERT INTO `varosok` VALUES (3383,7561,15,12,'Pálmajor');
INSERT INTO `varosok` VALUES (3384,8732,15,12,'Főnyed');
INSERT INTO `varosok` VALUES (3385,8732,15,12,'Szegerdő');
INSERT INTO `varosok` VALUES (3386,7527,15,12,'Rinyakovácsi');
INSERT INTO `varosok` VALUES (3387,7530,15,12,'Kőkút');
INSERT INTO `varosok` VALUES (3388,0,0,25,'Máramarossziget');
INSERT INTO `varosok` VALUES (3389,7477,15,12,'Patca');
INSERT INTO `varosok` VALUES (3390,7477,15,12,'Szilvásszentmárton');
INSERT INTO `varosok` VALUES (3391,0,0,26,'Bős');
INSERT INTO `varosok` VALUES (3392,0,0,25,'Szatmárnémeti');
INSERT INTO `varosok` VALUES (3393,7922,2,12,'Basal');
INSERT INTO `varosok` VALUES (3394,7900,2,12,'Csertő');
INSERT INTO `varosok` VALUES (3395,7925,2,12,'Magyarlukafa');
INSERT INTO `varosok` VALUES (3396,3075,13,12,'Márkháza');
INSERT INTO `varosok` VALUES (3397,3129,13,12,'Nagykeresztúr');
INSERT INTO `varosok` VALUES (3398,8681,15,12,'Visz');
INSERT INTO `varosok` VALUES (3399,0,0,26,'Révkomárom (Komárno)');
INSERT INTO `varosok` VALUES (3400,0,0,26,'Gúta');
INSERT INTO `varosok` VALUES (3401,0,0,26,'Keszegfalva');
INSERT INTO `varosok` VALUES (3402,0,0,26,'Naszvad');
INSERT INTO `varosok` VALUES (3403,0,0,25,'Tiszaveresmart');
INSERT INTO `varosok` VALUES (3404,7745,2,12,'Hásságy');
INSERT INTO `varosok` VALUES (3405,7747,2,12,'Bírján');
INSERT INTO `varosok` VALUES (3406,3066,13,12,'Kutasó');
INSERT INTO `varosok` VALUES (3407,3066,13,12,'Bokor');
INSERT INTO `varosok` VALUES (3408,8700,15,12,'Csömend');
INSERT INTO `varosok` VALUES (3409,7285,15,12,'Kára');
INSERT INTO `varosok` VALUES (3410,8666,15,12,'Somogyegres');
INSERT INTO `varosok` VALUES (3411,7285,15,12,'Szorosad');
INSERT INTO `varosok` VALUES (3412,8660,15,12,'Lulla');
INSERT INTO `varosok` VALUES (3413,8660,15,12,'Sérsekszőlős');
INSERT INTO `varosok` VALUES (3414,8660,15,12,'Torvaj');
INSERT INTO `varosok` VALUES (3415,8660,15,12,'Zala');
INSERT INTO `varosok` VALUES (3416,8658,15,12,'Bábonymegyer');
INSERT INTO `varosok` VALUES (3417,7370,2,12,'Oroszló');
INSERT INTO `varosok` VALUES (3418,8692,15,12,'Gyugy');
INSERT INTO `varosok` VALUES (3419,7443,15,12,'Edde');
INSERT INTO `varosok` VALUES (3420,7443,15,12,'Alsóbogát');
INSERT INTO `varosok` VALUES (3421,8693,15,12,'Kisberény');
INSERT INTO `varosok` VALUES (3422,8707,15,12,'Libickozma');
INSERT INTO `varosok` VALUES (3423,8698,15,12,'Pamuk');
INSERT INTO `varosok` VALUES (3424,8774,20,12,'Gelsesziget');
INSERT INTO `varosok` VALUES (3425,8774,20,12,'Kilimán');
INSERT INTO `varosok` VALUES (3426,8772,20,12,'Börzönce');
INSERT INTO `varosok` VALUES (3427,8776,20,12,'Bocska');
INSERT INTO `varosok` VALUES (3428,8776,20,12,'Magyarszentmiklós');
INSERT INTO `varosok` VALUES (3429,8825,20,12,'Pat');
INSERT INTO `varosok` VALUES (3430,8773,20,12,'Kacorlak');
INSERT INTO `varosok` VALUES (3432,0,0,25,'Batizgombás');
INSERT INTO `varosok` VALUES (3433,0,0,25,'Batiz');
INSERT INTO `varosok` VALUES (3434,0,0,25,'Szatmárudvari');
INSERT INTO `varosok` VALUES (3435,0,0,25,'Nagypeleske');
INSERT INTO `varosok` VALUES (3436,0,0,25,'Sár');
INSERT INTO `varosok` VALUES (3437,0,0,25,'Egri');
INSERT INTO `varosok` VALUES (3438,0,0,25,'Mikola');
INSERT INTO `varosok` VALUES (3439,0,0,25,'Lázári');
INSERT INTO `varosok` VALUES (3440,0,0,25,'Szárazberek');
INSERT INTO `varosok` VALUES (3441,0,0,25,'Sándorhomok');
INSERT INTO `varosok` VALUES (3442,0,0,25,'Szamosdara');
INSERT INTO `varosok` VALUES (3443,0,0,25,'Pusztadaróc');
INSERT INTO `varosok` VALUES (3444,0,0,25,'Rézbánya');
INSERT INTO `varosok` VALUES (3445,0,0,25,'Túrterebes');
INSERT INTO `varosok` VALUES (3446,0,0,25,'Túrterebes-hegy');
INSERT INTO `varosok` VALUES (3447,0,0,25,'Kisbábony');
INSERT INTO `varosok` VALUES (3448,0,0,25,'Halmi');
INSERT INTO `varosok` VALUES (3449,0,0,25,'Nagytarna');
INSERT INTO `varosok` VALUES (3450,0,0,25,'Turcbánya');
INSERT INTO `varosok` VALUES (3451,0,0,25,'Dabolc');
INSERT INTO `varosok` VALUES (3452,0,0,25,'Józsefháza');
INSERT INTO `varosok` VALUES (3453,7811,2,12,'Szilvás');
INSERT INTO `varosok` VALUES (3454,0,0,25,'Bikszád');
INSERT INTO `varosok` VALUES (3455,0,0,25,'Bujánháza');
INSERT INTO `varosok` VALUES (3456,0,0,25,'Kökényesd');
INSERT INTO `varosok` VALUES (3457,0,0,25,'Csedreg');
INSERT INTO `varosok` VALUES (3458,0,0,25,'Lajosvölgyihuta');
INSERT INTO `varosok` VALUES (3459,0,0,25,'Avasfelsőfalu');
INSERT INTO `varosok` VALUES (3460,0,0,25,'Sárközújlak');
INSERT INTO `varosok` VALUES (3461,0,0,25,'Adorján');
INSERT INTO `varosok` VALUES (3462,0,0,25,'Meggyesgombás');
INSERT INTO `varosok` VALUES (3463,0,0,25,'Pálfalva');
INSERT INTO `varosok` VALUES (3464,0,0,25,'Körtvélyes');
INSERT INTO `varosok` VALUES (3465,0,0,25,'Nagymadarász');
INSERT INTO `varosok` VALUES (3466,0,0,25,'Vetés');
INSERT INTO `varosok` VALUES (3467,0,0,25,'Óvári');
INSERT INTO `varosok` VALUES (3468,0,0,25,'Kövesláz');
INSERT INTO `varosok` VALUES (3469,0,0,25,'Nagyszokond');
INSERT INTO `varosok` VALUES (3470,0,0,25,'Szakasz');
INSERT INTO `varosok` VALUES (3471,0,0,25,'Gyöngy');
INSERT INTO `varosok` VALUES (3472,0,0,25,'Erdőd');
INSERT INTO `varosok` VALUES (3473,0,0,25,'Krasznasándorfalu');
INSERT INTO `varosok` VALUES (3474,0,0,25,'Szatmárhegy');
INSERT INTO `varosok` VALUES (3475,0,0,25,'Ákos');
INSERT INTO `varosok` VALUES (3476,0,0,25,'Krasznaterebes');
INSERT INTO `varosok` VALUES (3477,0,0,25,'Nántű');
INSERT INTO `varosok` VALUES (3478,0,0,25,'Szinfalu');
INSERT INTO `varosok` VALUES (3479,0,0,25,'Alsóhomoród');
INSERT INTO `varosok` VALUES (3480,0,0,25,'Bankasor');
INSERT INTO `varosok` VALUES (3481,0,0,25,'Börvely');
INSERT INTO `varosok` VALUES (3482,0,0,25,'Csanálos');
INSERT INTO `varosok` VALUES (3483,0,0,25,'Csanáloserdő');
INSERT INTO `varosok` VALUES (3484,0,2,12,'Romonya');
INSERT INTO `varosok` VALUES (3485,0,0,25,'Nagybocskó');
INSERT INTO `varosok` VALUES (3486,0,0,25,'Rónaszék');
INSERT INTO `varosok` VALUES (3487,0,0,25,'Borsabánya');
INSERT INTO `varosok` VALUES (3488,0,0,25,'Borsa');
INSERT INTO `varosok` VALUES (3489,0,0,25,'Aknasugatag');
INSERT INTO `varosok` VALUES (3490,0,0,25,'Hosszúmező');
INSERT INTO `varosok` VALUES (3491,0,0,25,'Szelestyehuta');
INSERT INTO `varosok` VALUES (3492,0,0,25,'Szigetkamara');
INSERT INTO `varosok` VALUES (3493,0,0,25,'Barlafalu');
INSERT INTO `varosok` VALUES (3494,0,0,25,'Avasújváros');
INSERT INTO `varosok` VALUES (3495,0,0,25,'Iloba');
INSERT INTO `varosok` VALUES (3496,0,0,25,'Szinérváralja');
INSERT INTO `varosok` VALUES (3497,0,0,25,'Miszbánya');
INSERT INTO `varosok` VALUES (3498,0,0,25,'Misztótfalu');
INSERT INTO `varosok` VALUES (3499,0,0,25,'Gidródtótfalu');
INSERT INTO `varosok` VALUES (3500,0,0,25,'Láposbánya');
INSERT INTO `varosok` VALUES (3501,0,0,25,'Felsőbánya');
INSERT INTO `varosok` VALUES (3502,0,0,25,'Kálmánd');
INSERT INTO `varosok` VALUES (3503,0,0,25,'Mezőfény');
INSERT INTO `varosok` VALUES (3504,0,0,25,'Nagykároly');
INSERT INTO `varosok` VALUES (3505,0,0,25,'Gencs');
INSERT INTO `varosok` VALUES (3506,0,0,25,'Csomaköz');
INSERT INTO `varosok` VALUES (3507,0,0,25,'Losdengeleg');
INSERT INTO `varosok` VALUES (3508,0,0,25,'Érkörtvélyes');
INSERT INTO `varosok` VALUES (3509,0,0,25,'Mezőpetri');
INSERT INTO `varosok` VALUES (3510,0,0,25,'Mezőterem');
INSERT INTO `varosok` VALUES (3511,0,0,25,'Piskolt');
INSERT INTO `varosok` VALUES (3512,0,0,25,'Iriny');
INSERT INTO `varosok` VALUES (3513,0,0,25,'Érendréd');
INSERT INTO `varosok` VALUES (3514,0,0,25,'Gilvács');
INSERT INTO `varosok` VALUES (3515,0,0,25,'Domihida');
INSERT INTO `varosok` VALUES (3516,0,0,25,'Szaniszló');
INSERT INTO `varosok` VALUES (3517,0,0,25,'Reszege');
INSERT INTO `varosok` VALUES (3518,0,0,25,'Farkasaszó');
INSERT INTO `varosok` VALUES (3519,0,0,25,'Nagysomkút');
INSERT INTO `varosok` VALUES (3520,0,0,25,'Felsőkohó');
INSERT INTO `varosok` VALUES (3521,8858,15,12,'Somogybükkösd');
INSERT INTO `varosok` VALUES (3522,7582,15,12,'Péterhida');
INSERT INTO `varosok` VALUES (3523,8858,15,12,'Porrogszentkirály');
INSERT INTO `varosok` VALUES (3524,8726,15,12,'Somogycsicsó');
INSERT INTO `varosok` VALUES (3525,8756,20,12,'Zalasárszeg');
INSERT INTO `varosok` VALUES (3526,8756,20,12,'Kisrécse');
INSERT INTO `varosok` VALUES (3527,8756,20,12,'Csapi');
INSERT INTO `varosok` VALUES (3528,8747,20,12,'Zalamerenye');
INSERT INTO `varosok` VALUES (3529,7584,15,12,'Somogyaracs');
INSERT INTO `varosok` VALUES (3530,7742,2,12,'Bogád');
INSERT INTO `varosok` VALUES (3531,7761,2,12,'Magyarsarlós');
INSERT INTO `varosok` VALUES (3532,7370,2,12,'Felsőegerszeg');
INSERT INTO `varosok` VALUES (3533,7370,2,12,'Meződ');
INSERT INTO `varosok` VALUES (3534,7380,2,12,'Palé');
INSERT INTO `varosok` VALUES (3535,7370,2,12,'Varga');
INSERT INTO `varosok` VALUES (3536,7383,2,12,'Szágy');
INSERT INTO `varosok` VALUES (3537,7678,12,12,'Szusztót');
INSERT INTO `varosok` VALUES (3538,7394,2,12,'Bodolyabér');
INSERT INTO `varosok` VALUES (3539,0,0,25,'Krasznabéltek');
INSERT INTO `varosok` VALUES (3540,0,2,12,'Husztót');
INSERT INTO `varosok` VALUES (3541,7639,2,12,'Kökény');
INSERT INTO `varosok` VALUES (3542,7668,2,12,'Gyód');
INSERT INTO `varosok` VALUES (3543,7675,2,12,'Kővágótöttös');
INSERT INTO `varosok` VALUES (3544,7673,2,12,'Cserkút');
INSERT INTO `varosok` VALUES (3545,7824,2,12,'Old');
INSERT INTO `varosok` VALUES (3546,7775,2,12,'Illocska');
INSERT INTO `varosok` VALUES (3547,7772,2,12,'Ivánbattyán	');
INSERT INTO `varosok` VALUES (3548,7776,2,12,'Kiskassa	');
INSERT INTO `varosok` VALUES (3549,7827,2,12,'Kásád	');
INSERT INTO `varosok` VALUES (3550,7833,2,12,'Regenye');
INSERT INTO `varosok` VALUES (3551,7833,2,12,'Szőke	');
INSERT INTO `varosok` VALUES (3552,7814,2,12,'Kisdér	');
INSERT INTO `varosok` VALUES (3553,0,0,26,'Komárom');
INSERT INTO `varosok` VALUES (3554,7763,2,12,'Áta');
INSERT INTO `varosok` VALUES (3555,7763,2,12,'Kisherend');
INSERT INTO `varosok` VALUES (3556,7761,2,12,'Lothárd');
INSERT INTO `varosok` VALUES (3557,7763,2,12,'Szemely');
INSERT INTO `varosok` VALUES (3558,7763,2,12,'Szőkéd');
INSERT INTO `varosok` VALUES (3559,7766,2,12,'Pécsdevecser');
INSERT INTO `varosok` VALUES (3560,7814,2,12,'Babarcszőlős');
INSERT INTO `varosok` VALUES (3561,7811,2,12,'Bisse');
INSERT INTO `varosok` VALUES (3562,7849,2,12,'Drávacsehi');
INSERT INTO `varosok` VALUES (3563,7823,2,12,'Kistapolca');
INSERT INTO `varosok` VALUES (3564,7800,2,12,'Kisharsány');
INSERT INTO `varosok` VALUES (3565,7773,2,12,'Kisjakabfalva');
INSERT INTO `varosok` VALUES (3566,7781,2,12,'Ivándárda');
INSERT INTO `varosok` VALUES (3567,7781,2,12,'Sárok');
INSERT INTO `varosok` VALUES (3568,7756,2,12,'Nagybudmér');
INSERT INTO `varosok` VALUES (3569,7735,2,12,'Szűr');
INSERT INTO `varosok` VALUES (3570,7735,2,12,'Erdősmárok');
INSERT INTO `varosok` VALUES (3571,7733,2,12,'Maráza');
INSERT INTO `varosok` VALUES (3572,7661,2,12,'Kátoly');
INSERT INTO `varosok` VALUES (3573,7751,2,12,'Monyoród');
INSERT INTO `varosok` VALUES (3574,7766,2,12,'Peterd');
INSERT INTO `varosok` VALUES (3575,7728,2,12,'Görcsönydoboka');
INSERT INTO `varosok` VALUES (3576,7757,2,12,'Liptód');
INSERT INTO `varosok` VALUES (3577,7759,2,12,'Kisnyárád');
INSERT INTO `varosok` VALUES (3578,7720,2,12,'Apátvarasd');
INSERT INTO `varosok` VALUES (3579,0,0,14,'Dublin');
INSERT INTO `varosok` VALUES (3580,0,0,25,'Kisdengeleg');
INSERT INTO `varosok` VALUES (3581,0,0,25,'Nagybánya');
INSERT INTO `varosok` VALUES (3582,0,0,25,'Felsővisó');
INSERT INTO `varosok` VALUES (3583,0,0,25,'Tóti');
INSERT INTO `varosok` VALUES (3584,0,0,25,'Szilágypér');
INSERT INTO `varosok` VALUES (3585,0,0,25,'Kővágó');
INSERT INTO `varosok` VALUES (3586,0,0,25,'Hagymádfalva');
INSERT INTO `varosok` VALUES (3587,0,0,25,'Hegyköztóttelek');
INSERT INTO `varosok` VALUES (3588,0,0,25,'Újsástelek');
INSERT INTO `varosok` VALUES (3589,0,0,25,'Körösrév');
INSERT INTO `varosok` VALUES (3590,0,0,25,'Bárod');
INSERT INTO `varosok` VALUES (3591,0,0,25,'Kakucs');
INSERT INTO `varosok` VALUES (3592,0,0,25,'Alsóludas');
INSERT INTO `varosok` VALUES (3593,0,0,25,'Remec');
INSERT INTO `varosok` VALUES (3594,0,0,25,'Esküllő');
INSERT INTO `varosok` VALUES (3595,0,0,25,'Margitta');
INSERT INTO `varosok` VALUES (3596,0,0,25,'Almásfegyvernek');
INSERT INTO `varosok` VALUES (3597,0,0,25,'Baromlaka');
INSERT INTO `varosok` VALUES (3598,0,0,25,'Bodonos');
INSERT INTO `varosok` VALUES (3599,0,0,25,'Érszalacs');
INSERT INTO `varosok` VALUES (3600,0,0,25,'Monospetri');
INSERT INTO `varosok` VALUES (3601,0,0,25,'Micske');
INSERT INTO `varosok` VALUES (3602,0,0,25,'Szentjobb');
INSERT INTO `varosok` VALUES (3603,0,0,25,'Székelyhíd');
INSERT INTO `varosok` VALUES (3604,0,0,25,'Asszonyvására');
INSERT INTO `varosok` VALUES (3605,0,0,25,'Bihardiószeg');
INSERT INTO `varosok` VALUES (3606,0,0,25,'Éradony');
INSERT INTO `varosok` VALUES (3607,0,0,25,'Érkeserű');
INSERT INTO `varosok` VALUES (3608,0,0,25,'Érmihályfalva');
INSERT INTO `varosok` VALUES (3609,0,0,25,'Érsemjén');
INSERT INTO `varosok` VALUES (3610,0,0,25,'Tenke');
INSERT INTO `varosok` VALUES (3611,0,0,25,'Bél');
INSERT INTO `varosok` VALUES (3612,0,0,25,'Bélfenyér');
INSERT INTO `varosok` VALUES (3613,0,0,25,'Biharsályi');
INSERT INTO `varosok` VALUES (3614,0,0,25,'Nagyszalonta');
INSERT INTO `varosok` VALUES (3615,0,0,25,'Belényes');
INSERT INTO `varosok` VALUES (3616,0,0,25,'Szilágysomlyó');
INSERT INTO `varosok` VALUES (3617,0,0,25,'Berettyószéplak');
INSERT INTO `varosok` VALUES (3618,0,0,25,'Kárásztelek');
INSERT INTO `varosok` VALUES (3619,0,0,25,'Érselind');
INSERT INTO `varosok` VALUES (3620,0,0,25,'Magyarpatak');
INSERT INTO `varosok` VALUES (3621,0,0,25,'Kraszna');
INSERT INTO `varosok` VALUES (3622,0,0,25,'Selymesilosva');
INSERT INTO `varosok` VALUES (3623,0,0,25,'Szilágycseh');
INSERT INTO `varosok` VALUES (3624,0,0,25,'Zilah');
INSERT INTO `varosok` VALUES (3625,0,0,25,'Zsibó');
INSERT INTO `varosok` VALUES (3626,0,0,25,'Királydaróc');
INSERT INTO `varosok` VALUES (3627,0,0,25,'Tasnád');
INSERT INTO `varosok` VALUES (3628,0,0,25,'Tasnádszántó');
INSERT INTO `varosok` VALUES (3629,0,0,25,'Szolnokháza');
INSERT INTO `varosok` VALUES (3630,0,0,25,'Berettyócsanálos');
INSERT INTO `varosok` VALUES (3631,0,0,25,'Értarcsa');
INSERT INTO `varosok` VALUES (3632,7678,2,12,'Kovácsszénája');
INSERT INTO `varosok` VALUES (3633,7671,2,12,'Aranyosgadány');
INSERT INTO `varosok` VALUES (3634,7683,2,12,'Cserdi');
INSERT INTO `varosok` VALUES (3635,7683,2,12,'Dinnyeberki');
INSERT INTO `varosok` VALUES (3636,7967,2,12,'Drávakeresztúr');
INSERT INTO `varosok` VALUES (3637,7681,2,12,'Szentkatalin');
INSERT INTO `varosok` VALUES (3638,7977,15,12,'Potony');
INSERT INTO `varosok` VALUES (3639,7918,15,12,'Tótújfalu');
INSERT INTO `varosok` VALUES (3640,7918,15,12,'Szentborbás');
INSERT INTO `varosok` VALUES (3641,7951,2,12,'Gerde');
INSERT INTO `varosok` VALUES (3642,7951,2,12,'Pécsbagota');
INSERT INTO `varosok` VALUES (3643,7940,2,12,'Csonkamindszent');
INSERT INTO `varosok` VALUES (3644,7940,2,12,'Kacsóta');
INSERT INTO `varosok` VALUES (3645,7915,2,12,'Szentegát');
INSERT INTO `varosok` VALUES (3646,7960,2,12,'Sumony');
INSERT INTO `varosok` VALUES (3647,7678,2,12,'Kovácsszénája');
INSERT INTO `varosok` VALUES (3648,7671,2,12,'Aranyosgadány');
INSERT INTO `varosok` VALUES (3649,7683,2,12,'Cserdi');
INSERT INTO `varosok` VALUES (3650,7683,2,12,'Dinnyeberki');
INSERT INTO `varosok` VALUES (3651,7967,2,12,'Drávakeresztúr');
INSERT INTO `varosok` VALUES (3652,7681,2,12,'Szentkatalin');
INSERT INTO `varosok` VALUES (3653,0,0,25,'Ipp');
INSERT INTO `varosok` VALUES (3654,7661,2,12,'Kékesd');
INSERT INTO `varosok` VALUES (3655,0,0,25,'Bályok');
INSERT INTO `varosok` VALUES (3656,0,0,25,'Cséffa');
INSERT INTO `varosok` VALUES (3657,0,0,25,'Tenkegörbed');
INSERT INTO `varosok` VALUES (3658,0,0,25,'Dólya');
INSERT INTO `varosok` VALUES (3659,0,0,25,'Cserpatak');
INSERT INTO `varosok` VALUES (3660,0,0,25,'Zoványfürdő');
INSERT INTO `varosok` VALUES (3661,0,0,25,'Krasznarécse');
INSERT INTO `varosok` VALUES (3662,0,0,25,'Sárán');
INSERT INTO `varosok` VALUES (3663,0,0,25,'Sülelmed');
INSERT INTO `varosok` VALUES (3664,0,0,25,'Szélszeg');
INSERT INTO `varosok` VALUES (3665,0,0,25,'Szilágynagyfalu');
INSERT INTO `varosok` VALUES (3666,0,0,25,'Szentkirály');
INSERT INTO `varosok` VALUES (3667,0,0,25,'Krasznacégény');
INSERT INTO `varosok` VALUES (3668,0,0,25,'Újtasnád');
INSERT INTO `varosok` VALUES (3669,0,0,25,'Csány');
INSERT INTO `varosok` VALUES (3670,0,0,25,'Ady Endre (Érmindszent)');
INSERT INTO `varosok` VALUES (3671,8300,19,12,'Raposka');
INSERT INTO `varosok` VALUES (3672,0,0,25,'SzĂŠkelybethlenfalva');
INSERT INTO `varosok` VALUES (3673,0,0,25,'Szekelybethlenfalva');
INSERT INTO `varosok` VALUES (3674,0,0,46,'Pancsova (Pančevo)');
INSERT INTO `varosok` VALUES (3675,0,0,46,'Herteledyfalva');
INSERT INTO `varosok` VALUES (3676,0,0,46,'Torontálvásárhely');
INSERT INTO `varosok` VALUES (3677,0,0,46,'Fehértemplom');
INSERT INTO `varosok` VALUES (3678,0,0,46,'Udvarszállás');
INSERT INTO `varosok` VALUES (3679,0,0,46,'Karasjeszenő');
INSERT INTO `varosok` VALUES (3680,0,0,46,'Kevevára');
INSERT INTO `varosok` VALUES (3681,0,0,46,'Gálya');
INSERT INTO `varosok` VALUES (3682,0,0,46,'Fejértelep');
INSERT INTO `varosok` VALUES (3683,0,0,46,'Sándoregyháza');
INSERT INTO `varosok` VALUES (3684,0,0,46,'Székelykeve');
INSERT INTO `varosok` VALUES (3685,0,0,46,'Ürményháza');
INSERT INTO `varosok` VALUES (3686,0,0,46,'Alikútja');
INSERT INTO `varosok` VALUES (3687,0,0,46,'Istvánvölgy');
INSERT INTO `varosok` VALUES (3688,0,0,46,'Versec');
INSERT INTO `varosok` VALUES (3689,0,0,46,'Károlyfalva');
INSERT INTO `varosok` VALUES (3690,0,0,46,'Kutas');
INSERT INTO `varosok` VALUES (3691,0,0,46,'Versecvát');
INSERT INTO `varosok` VALUES (3692,0,0,46,'Nagyszered');
INSERT INTO `varosok` VALUES (3693,0,0,46,'Temesvajkóc');
INSERT INTO `varosok` VALUES (3694,0,0,46,'Zichyfalva');
INSERT INTO `varosok` VALUES (3695,0,0,46,'Újfalu');
INSERT INTO `varosok` VALUES (3696,0,0,25,'Biharszentjános');
INSERT INTO `varosok` VALUES (3697,7768,2,12,'Kistótfalu');
INSERT INTO `varosok` VALUES (3698,7800,2,12,'Nagytótfalu');
INSERT INTO `varosok` VALUES (3699,9733,18,12,'Ólmod');
INSERT INTO `varosok` VALUES (3700,0,0,2,'Brüsszel');
INSERT INTO `varosok` VALUES (3702,7091,17,12,'Pári');
INSERT INTO `varosok` VALUES (3704,8973,20,12,'Magyarföld');
INSERT INTO `varosok` VALUES (3705,3261,10,12,'Pálosvörösmart');
INSERT INTO `varosok` VALUES (3706,8296,19,12,'Hegyesd');
INSERT INTO `varosok` VALUES (3707,8484,19,12,'Vid');
INSERT INTO `varosok` VALUES (3708,8956,20,12,'Kányavár');
INSERT INTO `varosok` VALUES (3709,8957,20,12,'Hernyék');
INSERT INTO `varosok` VALUES (3710,8452,19,12,'Szőc');
INSERT INTO `varosok` VALUES (3711,8484,19,12,'Somlóvecse');
INSERT INTO `varosok` VALUES (3712,8477,19,12,'Apácatorna');
INSERT INTO `varosok` VALUES (3713,8477,19,12,'Kisberzseny');
INSERT INTO `varosok` VALUES (3714,8348,19,12,'Megyer');
INSERT INTO `varosok` VALUES (3715,8348,19,12,'Zalameggyes');
INSERT INTO `varosok` VALUES (3716,0,0,32,'Zürich');
INSERT INTO `varosok` VALUES (3717,8309,19,12,'Sáska');
INSERT INTO `varosok` VALUES (3718,8309,19,12,'Lesencefalu');
INSERT INTO `varosok` VALUES (3719,8254,19,12,'Kékkút');
INSERT INTO `varosok` VALUES (3721,7391,2,12,'Kisbeszterce');
INSERT INTO `varosok` VALUES (3722,0,0,30,'London');
INSERT INTO `varosok` VALUES (3723,7973,2,12,'Endrőc');
INSERT INTO `varosok` VALUES (3724,7839,2,12,'Kemse');
INSERT INTO `varosok` VALUES (3725,7847,2,12,'Ipacsfa');
INSERT INTO `varosok` VALUES (3726,0,0,25,'Négyfalu');
INSERT INTO `varosok` VALUES (3727,7186,17,12,'Nagyvejke');
INSERT INTO `varosok` VALUES (3728,9953,18,12,'Nemesmedves');
INSERT INTO `varosok` VALUES (3729,8921,20,12,'Pethőhenye');
INSERT INTO `varosok` VALUES (3730,7585,15,12,'Bakháza');
INSERT INTO `varosok` VALUES (3731,7584,15,12,'Rinyaújnép');
INSERT INTO `varosok` VALUES (3732,3726,4,12,'Alsószuha');
INSERT INTO `varosok` VALUES (3733,0,0,46,'Óbecse');
INSERT INTO `varosok` VALUES (3734,8344,19,12,'Hetyefő');
INSERT INTO `varosok` VALUES (3735,0,0,26,'Dunaszerdahely (Dunajská Streda)');
INSERT INTO `varosok` VALUES (3736,0,0,26,'Albár (Dolný Bar)');
INSERT INTO `varosok` VALUES (3737,0,0,26,'Alistál (Dolný Štál)');
INSERT INTO `varosok` VALUES (3738,0,0,26,'Baka (Baka)');
INSERT INTO `varosok` VALUES (3739,0,0,26,'Balony (Baloň)');
INSERT INTO `varosok` VALUES (3740,0,0,26,'Bős (Gabčíkovo)');
INSERT INTO `varosok` VALUES (3741,0,0,26,'Csallóközkürt (Ohrady)');
INSERT INTO `varosok` VALUES (3742,0,0,26,'Dercsika (Jurová)');
INSERT INTO `varosok` VALUES (3743,0,0,26,'Nagyszarva (Rohovce)');
INSERT INTO `varosok` VALUES (3744,0,0,26,'Bacsfa (Báč)');
INSERT INTO `varosok` VALUES (3745,0,0,26,'Csallóköztárnok (Trnávka)');
INSERT INTO `varosok` VALUES (3746,8496,19,12,'Kispirit');
INSERT INTO `varosok` VALUES (3747,8557,19,12,'Bakonyság');
INSERT INTO `varosok` VALUES (3748,0,0,26,'Nagymegyer (Veľký Meder)');
INSERT INTO `varosok` VALUES (3749,7664,2,12,'Pereked');
INSERT INTO `varosok` VALUES (3750,9346,8,12,'Vadosfa');
INSERT INTO `varosok` VALUES (3751,7720,2,12,'Martonfa');
/*!40000 ALTER TABLE `varosok` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

--
-- Table structure for table `favorites`
--

DROP TABLE IF EXISTS `favorites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `favorites` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `tid` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uid_tid_UNIQUE` (`uid`,`tid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `favorites`
--

LOCK TABLES `favorites` WRITE;
/*!40000 ALTER TABLE `favorites` DISABLE KEYS */;
/*!40000 ALTER TABLE `favorites` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `osm`
--

DROP TABLE IF EXISTS `osm`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `osm` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `osmid` varchar(11) NOT NULL,
  `osmtype` varchar(9) NOT NULL,
  `lon` decimal(10,8) DEFAULT NULL,
  `lat` decimal(11,8) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`osmid`,`osmtype`)
) ENGINE=InnoDB AUTO_INCREMENT=4075 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tokens`
--

DROP TABLE IF EXISTS `tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `name` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `uid` int(11) DEFAULT NULL,
  `timeout` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  UNIQUE KEY `name_UNIQUE` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;


--
-- Table structure for table `osmtags`
--

DROP TABLE IF EXISTS `osmtags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `osmtags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `osm_id` int(11) NOT NULL,
  `name` varchar(45) CHARACTER SET utf8 NOT NULL,
  `value` varchar(200) CHARACTER SET utf8 DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  UNIQUE KEY `id_UNIQUE` (`id`),
  UNIQUE KEY `unique` (`osm_id`,`name`),
  CONSTRAINT `FK_osm_id` FOREIGN KEY (`osm_id`) REFERENCES `osm` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=20816 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `messages`
--

DROP TABLE IF EXISTS `messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `severity` varchar(10) COLLATE utf8_unicode_ci DEFAULT 'info',
  `text` text COLLATE utf8_unicode_ci,
  `shown` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=396 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `messages`
--

LOCK TABLES `messages` WRITE;
/*!40000 ALTER TABLE `messages` DISABLE KEYS */;
INSERT INTO `messages` VALUES (1,'644ee6db659af1f1a8031e2320cdd66e','2015-08-25 01:14:15','success','Bajvan',1);
/*!40000 ALTER TABLE `messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `igenaptar`
--

DROP TABLE IF EXISTS `igenaptar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `igenaptar` (
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
) ENGINE=MyISAM AUTO_INCREMENT=821 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `igenaptar`
--

LOCK TABLES `igenaptar` WRITE;
/*!40000 ALTER TABLE `igenaptar` DISABLE KEYS */;
INSERT INTO `igenaptar` VALUES (2,'zold','A','e','4. vasárnap','','','','','','','','',''),(3,'zold','0','e','3. hét, szombat','','','','','','','','A hit a nem látott dolgok bizonysága és reményeink alapja. Az Újszövetség szentjei is ebben a hitben adtak példát és ez a hit kapcsol egybe minket és odafűz az Istenhez, aki bennünket gyermekeinek ismer.','<p><font size=\"1\">Zsid 11, 1-2.8-19<br />A hit szil&aacute;rd bizalom abban, amit rem&eacute;l&uuml;nk, meggyőződ&eacute;s arr&oacute;l, amit nem l&aacute;tunk.<br />Mk 4, 35-40<br />Mi&eacute;rt f&eacute;ltek, kicsinyhitűek?</font></p> <p align=\"center\"><font><img border=\"0\" src=\"http://www.plebania.net/img/kocka.gif\" /></font></p> <p><font>A hit a nem l&aacute;tott dolgok bizonys&aacute;ga &eacute;s rem&eacute;nyeink alapja. Az &Uacute;jsz&ouml;vets&eacute;g szentjei is ebben a hitben adtak p&eacute;ld&aacute;t &eacute;s ez a hit kapcsol egybe minket &eacute;s odafűz az Istenhez, aki benn&uuml;nket gyermekeinek ismer.</font></p> <p align=\"center\"><img border=\"0\" src=\"http://www.plebania.net/img/kocka.gif\" /></p> <p><font>A hit legyőzi a f&eacute;lelmet. A J&eacute;zus Krisztusba vetett hit megadja a<br />b&aacute;tors&aacute;got, hogy b&aacute;tran &eacute;s bizalommal n&eacute;zz&uuml;nk szembe &eacute;let&uuml;nk dolgaival, b&aacute;tran v&aacute;gjunk neki mindennapi feladatainknak.<br />J&eacute;zus erej&eacute;vel j&aacute;ruk utunkon, &eacute;s szombat l&eacute;v&eacute;n M&aacute;ri&aacute;hoz, &eacute;gi &eacute;desany&aacute;nkhoz is bizalommal sz&aacute;ll foh&aacute;szunk, seg&iacute;ten k&ouml;zbenj&aacute;r&aacute;s&aacute;val nek&uuml;nk, j&aacute;rja ki nek&uuml;nk a sz&uuml;ks&eacute;ges kegyelmet.</font></p> <p><font><img border=\"0\" src=\"http://www.plebania.net/img/kocka.gif\" /></font></p> <p><font><font>Zsid 11, 1-2.8-19 A hit szil&aacute;rd bizalom abban, amit rem&eacute;l&uuml;nk, meggy?z?d&eacute;s arr&oacute;l, <br />amit nem l&aacute;tunk.<br />Mk 4, 35-40 Mi&eacute;rt f&eacute;ltek, kicsinyhitűek?<br /><br />A hit a nem l&aacute;tott dolgok bizonys&aacute;ga &eacute;s rem&eacute;nyeink alapja. Az &Uacute;jsz&ouml;vets&eacute;g szentjei is ebben a hitben adtak p&eacute;ld&aacute;t &eacute;s ez a hit kapcsol egybe minket &eacute;s odafűz az Istenhez, aki benn&uuml;nket gyermekeinek ismer.<br /><br />A hit legyőzi a f&eacute;lelmet. A J&eacute;zus Krisztusba vetett hit megadja a b&aacute;tors&aacute;got, hogy b&aacute;tran &eacute;s bizalommal n&eacute;zz&uuml;nk szembe &eacute;let&uuml;nk dolgaival, b&aacute;tran v&aacute;gjunk neki mindennapi feladatainknak.<br />J&eacute;zus erej&eacute;vel j&aacute;runk utunkon, &eacute;s szombat l&eacute;v&eacute;n M&aacute;ri&aacute;hoz, &eacute;gi &eacute;desany&aacute;nkhoz is bizalommal sz&aacute;ll foh&aacute;szunk, seg&iacute;tsen k&ouml;zbenj&aacute;r&aacute;s&aacute;val nek&uuml;nk, j&aacute;rja ki nek&uuml;nk a sz&uuml;ks&eacute;ges kegyelmet.<br /><br />Fulop Akos RM</font><br /><br /></font></p>');
/*!40000 ALTER TABLE `igenaptar` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `emails`
--

DROP TABLE IF EXISTS `emails`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `emails` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `to` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `header` text COLLATE utf8_unicode_ci,
  `subject` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `body` text COLLATE utf8_unicode_ci NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `events`
--

DROP TABLE IF EXISTS `events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `year` varchar(4) DEFAULT NULL,
  `date` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  UNIQUE KEY `name+year` (`name`,`year`)
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `events`
--

LOCK TABLES `events` WRITE;
/*!40000 ALTER TABLE `events` DISABLE KEYS */;
INSERT INTO `events` VALUES (1,'utolsó tanítási nap','2014','2014-06-01'),(2,'utolsó tanítási nap','2015','2015-06-16'),(3,'első tanítási nap','2014','2014-09-01'),(4,'első tanítási nap','2015','2015-09-03'),(5,'Advent I. vasárnapja','2014','2014-11-30'),(6,'Advent I. vasárnapja','2015','2015-11-29'),(7,'Advent I. vasárnapja','2016','2016-11-27'),(8,'Hamvazószerda','2015','2015-02-18'),(9,'Hamvazószerda','2016','2016-02-10'),(10,'Húsvétvasárnap','2015','2015-04-05'),(11,'Húsvétvasárnap','2016','2016-03-27'),(12,'Tavaszi óraátállítás','2015','2015-03-12'),(13,'Tavaszi óraátállítás','2016','2016-03-27'),(14,'Őszi óraátállítás','2015','2015-10-29'),(15,'Őszi óraátállítás','2016','2016-10-30'),(16,'Pünkösdvasárnap','2014','2014-06-08'),(17,'Pünkösdvasárnap','2015','2015-05-24'),(18,'Pünkösdvasárnap','2016','2016-05-15'),(19,'Pünkösdhétfő','2014','2014-06-09'),(20,'Pünkösdhétfő','2015','2015-05-25'),(21,'Pünkösdhétfő','2016','2016-05-16'),(23,'szeptember utolsó vasárnapja','2015','2015-09-27'),(24,'szeptember 1. vasárnapja','2015','2015-09-02'),(25,'Húsvéthétfő','2015','2015-04-06'),(26,'Húsvéthétfő','2016','2016-03-28'),(27,'október utolsó vasárnapja','2015','2015-10-25'),(28,'szeptember 2. vasárnapja','2015','2015-09-09'),(29,'június 2. vasárnapja','2015','2015-06-14'),(30,'Isteni Irgalmasság vasárnapja','2015','2015-04-12'),(31,'Szent Kereszt felmagasztalása követő vasárna','2015','2015-09-16'),(32,'Szent Kereszt megtalálása köv. vasárnap','2015','2015-05-05'),(34,'Szűz Mária neve követő vasárnap','2015','2015-09-13'),(35,'Nagycsütörtök','2015','2015-04-02'),(36,'Szentháromság vasárnapja','2015','2015-05-31'),(37,'Szentháromság vasárnapja','2016','2016-05-22'),(38,'Május 3. vasárnapja','2015','2015-05-17'),(39,'október első vasárnapja','2015','2015-10-04'),(40,'Úrnapja','2015','2015-06-07'),(41,'Úrnapja','2016','2016-05-29'),(42,'június utolsó vasárnapja','2015','2015-06-28'),(43,'augusztus utolsó vasárnapja','2015','2015-08-30'),(44,'május első vasárnapja','2015','2015-05-03'),(45,'június első szombatja','2015','2015-06-06'),(46,'március utolsó vasárnapja','2015','2015-03-29'),(47,'március utolsó vasárnapja','2016','2016-03-27'),(48,'Urunk mennybemenetele','2015','2015-05-14'),(49,'Urunk mennybemenetele követő vasárnap','2015','2015-05-17'),(50,'Szent Anna követő vasárnap','2015','2015-07-26'),(51,'Sárgabarack','2015','2015-03-29');
/*!40000 ALTER TABLE `events` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
