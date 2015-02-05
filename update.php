<?php
include 'load.php';

echo 'hajrá';
/*
//$tid = rand(1,4111);
//print_r(getMasses(rand(1,1)));
//exit;
//

//  img/zaszloikon/hu.gif -> img/zaszloikon/h.gid

$query = " 
ALTER TABLE kepek ADD COLUMN height integer;
ALTER TABLE kepek ADD COLUMN width integer;
DELETE FROM kepek wHERE kat <> 'templomok';
ALTER TABLE kepek DROP COLUMN kat;
ALTER TABLE kepek DROP COLUMN katnev;
ALTER TABLE kepek CHANGE kid tid int;
DELETE FROM misek WHERE torles > '0000-00-00';
ALTER TABLE misek CHANGE templom tid int(5);
ALTER TABLE misek CHANGE idoszamitas idoszamitas varchar(255);
DELETE FROM misek WHERE tid = '0';
ALTER TABLE misek CHANGE datumtol tol varchar(100);
ALTER TABLE misek CHANGE datumig ig varchar(100);
DROP TABLE IF EXISTS `events`;
CREATE TABLE `events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `year` varchar(4) COLLATE utf8_hungarian_ci DEFAULT NULL,
  `date` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  UNIQUE KEY `name+year` (`name`,`year`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;
LOCK TABLES `events` WRITE;
INSERT INTO `events` VALUES (1,'utolsó tanítási nap','2014','2014-06-01'),(2,'utolsó tanítási nap','2015','2015-06-07'),(3,'első tanítási nap','2014','2014-09-01'),(4,'első tanítási nap','2015','2015-09-03');
UNLOCK TABLES;
ALTER TABLE misek ADD COLUMN nap2 varchar(4) AFTER ido;
ALTER TABLE misek ADD COLUMN tmp_datumig VARCHAR(5) AFTER ig;
ALTER TABLE misek ADD COLUMN tmp_relation CHAR(1) AFTER ig;
ALTER TABLE misek ADD COLUMN tmp_datumtol VARCHAR(5) AFTER ig;
ALTER TABLE user ADD COLUMN lastactive DATETIME AFTER lastlogin;
ALTER TABLE templomok ADD COLUMN miseaktiv INT DEFAULT 1 AFTER megjegyzes;

CREATE TABLE `osm` (
  `tid` int(11) NOT NULL,
  `osm-id` int(11) NOT NULL,
  `osm-type` varchar(9) NOT NULL,
  `osm2templom` int(1) DEFAULT 0,
  `osm2templom_date` DATETIME,
  `templom2osm` int(1) DEFAULT 0,
  `templom2osm_date` DATETIME,  
  PRIMARY KEY (`tid`),
  UNIQUE KEY `id_UNIQUE` (`tid`,`osm-id`,`osm-type`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;
ALTER TABLE chat ADD id INT PRIMARY KEY AUTO_INCREMENT FIRST;
ALTER TABLE `terkep_geocode` ADD INDEX `lat` (`lat`);
ALTER TABLE `terkep_geocode` ADD INDEX `lng` (`lng`);
INSERT INTO `miserend`.`adminmenu` (`nev`, `fm`, `sorszam`, `mid`, `op`, `ok`, `jogkod`) VALUES ('kifejezések és dátumok', '9', '4', '27', 'events', 'i', 'miserend');

INSERT INTO sugo VALUES (46,'<h4>Nyelvek</h4>Az szentmise nyelvének azonosítója esetleg egy periódus megjelölésével. Több érték esetén vesszővel elválasztva. Például: <i>sk-1,grps</i> = minden hónap utolsó hetében szlovák nyelvű, minden páros héten görög (egyéb esetekben magyar)<br/><br/>Lehetséges nyelvek:<ul><li>h, hu vagy üres = magyar</li><li>en = angol</li><li>de = német</li><li>it = olasz</li><li>fr = francia</li><li>va = latin</li><li>gr = görög</li><li>sk = szlovák</li><li>hr = horvát</li><li>pl = lengyel</li><li>si = szlovén</li><li>további nyelvek esetén az internetes 2 betűs végződés az irányadó!</li></ul>Lehetséges periódusok:<ul><li>0 vagy nincs periódus megadva = mindig</li><li>1, 2, 3, 4, 5 = adott héten</li><li>-1 = utolsó héten</li><li>ps = páros héten</li><li>pt = páratlan héten</li></ul>Ha maga a mise periódusa meg van advan, akkor nem szükséges itt is megadni a periódust.','miserend adatlap');
INSERT INTO sugo VALUES (47,'<h4>Tulajdonságok</h4>Az szentmise tulajdonságának rövidítése esetleg egy periódus megjelölésével. Több érték esetén vesszővel elválasztva. Például: <i>ifi,ige3</i> = mindig ifjúsági/egyetemista mise, de a hónap harmadik hetében csak igeliturgia.<br/><br/>Lehetséges tulajdonságok:<ul><li> g = gitáros</li><li> cs = csendes</li><li> csal = családos/gyerek</li><li> d = diák</li><li> ifi = egyetemista/ifjúsági</li><li> ige = igeliturgia</li><li> gor = görögkatolikus (római rítusú templomban)</li><li> rom = római katolikus (görögkatolikus rítusú templomban)</li><li> regi = régi rítusú</ul>Lehetséges periódusok:<ul><li>0 vagy nincs periódus megadva = mindig</li><li>1, 2, 3, 4, 5 = adott héten</li><li>-1 = utolsó héten</li><li>ps = páros héten</li><li>pt = páratlan héten</li></ul>Ha maga a mise periódusa meg van advan, akkor nem szükséges itt is megadni a periódust.','miserend adatlap');
INSERT INTO sugo VALUES (48,'A <b>megjegyzés</b> rovatba minden további részletet tüntessünk fel, amit nem tudtunk a tulajdonságokhoz feljegyezni.','miserend adatlap');
INSERT INTO sugo VALUES (49,'<h4>Periódus / Különleges mierend név</h4>Minden periódusnak, más néven időszaknak, valamint minden különleges miserendnek kell legyen egy egyedi neve. Például: <i>téli miserend</i>, <i>ádventi időszak</i> vagy <i>karácsony napja</i>. Gépelés közben megjelennek a már használt nevek is. Ha egy már használt nevet választunk, akkor az időszak kezdetét és végét átmásolja onnan, így nem kell újra beállítani.','miserend adatlap');
INSERT INTO sugo VALUES (50,'<h4>Periódus határok</h4>Minden periódusnak, más néven időszaknak, be kell állítani a kezdetét és a végét. Több féleképpen adhatjuk meg a kezdő napot és a lezáró napot.<ul><li>Megadhatjuk egy konkért dátum nélküli kifejezéssel. Például: <i>első tanítási nap</i> vagy <i>Krisztus Király vasárnapja</i>. Gépelés közben megjelennek a választható kezdeti időpontok. Ha szükség lenne olyanra, ami még nincs, akkor írj nekem: eleklaszlosj@gmail.com. Azért nagyon praktikus ilyen kifejezéssel megadni egy időszak határát, mert így nem kell minden évben átírni. A miserend.hu tudja, hogy melyik évben mikor van pl. Húsvét.</li><li>Ha minden évben ugyan azon a naptári napon van az időszak váltása, akkor megadhatunk egy dátumot is. Például: <i>12-25</i>. Ilyenkor minden évben, pont ez lesz a forduló nap.</li><li>Nagy ritkán előfordulhat, hogy egy periódus/időszak csak egy adott évben létezik és más években nincsen rá szükség. Ilyen esetben meg lehet adni teljes tádummal a határt. Például: <i>2016-03-12</i>. Fontos, hogy így az adott időszaki miserend nem fog megjelenni más évben.</li></ul>Fontos, hogy nem szabad két különböző időszaknak pontosan ugyan azokat a határokat megadni, mert akkor nincs ami megkülönböztesse azokat egymástól.','miserend adatlap');
INSERT INTO sugo VALUES (51,'<h4>Különleges miserend</h4>Mindig be kell állítani, hogy a különleges mierend mikor van érvénybe.Több féleképpen adhatjuk meg ezt:<ul><li>Megadhatjuk egy konkért dátum nélküli kifejezéssel. Például: <i>első tanítási nap</i>, <i>Krisztus Király vasárnapja</i> vagy <i>Karácsony utáni 3. nap</i>. Gépelés közben megjelennek a választható időpont-kifejezések. Ha szükség lenne olyanra, ami még nincs, akkor írj nekem: eleklaszlosj@gmail.com. Azért nagyon praktikus ilyen kifejezéssel megadni egy különleges miserend érvényességét, mert így nem kell minden évben átírni. A miserend.hu tudja, hogy melyik évben mikor van pl. Húsvét.</li><li>Ha minden évben ugyan azon a naptári napon érvényes a miserend, akkor megadhatunk egy dátumot is. Például: <i>01-06</i>. Ilyenkor minden évben, pont ezen a napon lép életben a miserend.</li><li>Előfordulhat, hogy egy különleges miserend egy konkrét év egyetlen napján fordul elő és más évben várhatóan nem. Ilyen esetben meg lehet adni teljes tádummal a napot. Például: <i>2016-03-12</i>. Fontos, hogy így az adott miserend nem fog megjelenni más évben.</li></ul>Fontos, hogy nem szabad két különböző különleges miserendnek pontosan ugyan azt a napot megadni érvényességként, mert akkor nincs ami megkülönböztesse a két különleges miserendet egymástól.','miserend adatlap');

ALTER TABLE misek ADD COLUMN weight INT AFTER idoszamitas;

UPDATE misek LEFT JOIN templomok on misek.tid = templomok.id SET nyelv = NULL  WHERE ( nyelv = 'h0' OR nyelv = 'h') AND templomok.orszag = 12;

DROP TABLE 0kurir;
DROP TABLE 0log;
DROP TABLE 0nyelvek;
DROP TABLE 0sorozatok;
DROP TABLE 0statisztika;
DROP TABLE enghirkat;
DROP TABLE fooldal;
DROP TABLE galeria;
DROP TABLE g_kepcimek;
DROP TABLE hirek;
DROP TABLE kiemelthirkat;
DROP TABLE kulcsszo;
DROP TABLE rovatkat;
DROP TABLE statkat;
DROP TABLE szervezotipus;
DROP TABLE templom_exp;
DROP TABLE templomok1;
DROP TABLE terkep_rank;
DROP TABLE terkep_misek_next;
DROP TABLE terkep_geocode_suggestion;
DROP TABLE szavazasv;
DROP TABLE szavazask;
DROP TABLE szavazasell;

UPDATE `miserend`.`fomenu` SET `leiras`='<p class=\"kiscim\">A regisztr&aacute;ci&oacute;ra &eacute;s a port&aacute;lrendszer haszn&aacute;lat&aacute;ra az al&aacute;bbi szab&aacute;lyok &eacute;rv&eacute;nyesek, k&eacute;rj&uuml;k olvasd el figyelmesen!</p>           <table width=\"100%\" cellspacing=\"1\" cellpadding=\"4\" border=\"0\">   <tbody>      <tr>        <td valign=\"top\" align=\"right\" class=\"kiscim\" style=\"background-color: #ffffff\">1.</td>            <td class=\"alap\">  <p>      A Virtu&aacute;lis Pl&eacute;b&aacute;nia Port&aacute;lcsoport, melyhez a főmen&uuml;ben tal&aacute;lhat&oacute; aloldalak (h&iacute;rporta, miserend, t&aacute;borhely, m&eacute;diat&aacute;r, pl&eacute;b&aacute;nia) tartoznak, magyar katolikus oldalak. Tartalm&aacute;t papok      &eacute;s elk&ouml;telezett h&iacute;vek friss&iacute;tik. <strong>A port&aacute;lon megjelenő anyagok tekintet&eacute;ben      a Katolikus Egyh&aacute;z hivatalos tan&iacute;t&aacute;sa a m&eacute;rvad&oacute;!</strong></p>   </td>   </tr>       <tr>        <td valign=\"top\" align=\"right\" class=\"kiscim\">2.</td>            <td class=\"alap\">  <p>      Felhaszn&aacute;l&oacute;ink k&ouml;z&ouml;tt term&eacute;szetesen korra, nemre, felekezetre val&oacute; tekintet      n&eacute;lk&uuml;l mindenkit szeretettel l&aacute;tunk! Azonban k&eacute;rj&uuml;k az 1. pont tudom&aacute;sulv&eacute;tel&eacute;t &eacute;s egym&aacute;s k&ouml;lcs&ouml;n&ouml;s tiszteletbentart&aacute;s&aacute;t!</p>   <p><strong>FONTOS!</strong> Oldalunkon b&aacute;rki, b&aacute;rmilyen sz&aacute;nd&eacute;kkal jelen lehet, a regisztr&aacute;ci&oacute;, illetve a k&uuml;l&ouml;nb&ouml;ző kapcsolatok sor&aacute;n szem&eacute;lyes adataid mindig kellő &oacute;vatoss&aacute;ggal kezeld! <u>Felhaszn&aacute;l&oacute;ink publikusan megadott adatainak val&oacute;s&aacute;gtartalm&aacute;&eacute;rt nem tudunk felelőss&eacute;get v&aacute;llalni!</u><br />  </p>  </td>   </tr>       <tr>        <td valign=\"top\" align=\"right\" class=\"kiscim\">3.</td>            <td class=\"alap\">      A port&aacute;lon lehetős&eacute;get biztos&iacute;tunk besz&eacute;lget&eacute;sekre, ismerked&eacute;sre, kapcsolattart&aacute;sra, azonban mindezt csak kultur&aacute;lt keretek k&ouml;z&ouml;tt, az 1. pont figyelembev&eacute;tel&eacute;vel. Oldalunkon, t&eacute;mak&ouml;reinkben val&oacute; provok&aacute;l&aacute;s, m&aacute;sok zaklat&aacute;sa kiz&aacute;r&aacute;ssal j&aacute;r!      </td>   </tr>       <tr>        <td valign=\"top\" align=\"right\" class=\"kiscim\">4.</td>            <td class=\"alap\">  <p>A regisztr&aacute;ci&oacute; sor&aacute;n k&ouml;telező megadni emailc&iacute;med, valamint egy bejelentkez&eacute;si nevet. A v&aacute;lasztott <u>bejelentkez&eacute;si n&eacute;v</u> viszont <u>mindig egy &eacute;s csak egy konkr&eacute;t szem&eacute;lyt k&eacute;pviselhet</u> (szervezetet, int&eacute;zm&eacute;nyt vagy csoportot, k&ouml;z&ouml;ss&eacute;get, csal&aacute;dot nem) &eacute;s azt <u>csak egy ember haszn&aacute;lhatja</u>.</p>   <p><strong>FONTOS!</strong> Figyelj, hogy <u>emailc&iacute;med mindig aktu&aacute;lis legyen</u>, v&aacute;ltoz&aacute;s eset&eacute;n oldalunkon is m&oacute;dos&iacute;tsd. K&eacute;retlen rekl&aacute;mleveleket nem k&uuml;ld&uuml;nk, harmadik f&eacute;lnek nem adjuk ki, de adott esetben az itt megadott c&iacute;mre k&uuml;ld&uuml;nk oldalunkkal kapcsolatos &eacute;rtes&iacute;t&eacute;st, figyelmeztet&eacute;st. Saj&aacute;t &eacute;rdeked, hogy ezt mindig időben megkaphasd.<br />  </p>  </td>   </tr>       <tr>        <td valign=\"top\" align=\"right\" class=\"kiscim\">5.</td>            <td class=\"alap\"><p>Regisztr&aacute;lt felhaszn&aacute;l&oacute;inknak lehetős&eacute;g&uuml;k van egym&aacute;s k&ouml;zti kapcsolatokat be&aacute;ll&iacute;tani. Ennek megfelelően a regisztr&aacute;ci&oacute; sor&aacute;n megadott adatok szűrhetőek, hogy mely k&ouml;r l&aacute;thassa. A nyilv&aacute;nosan megadott adatok mellett lehetős&eacute;g van csak ismerős&ouml;k &eacute;s bar&aacute;tok, vagy csak bar&aacute;tok r&eacute;sz&eacute;re megadni adatokat.</p><p>A kapcsolatok jel&ouml;l&eacute;se nem k&ouml;lcs&ouml;n&ouml;sen műk&ouml;dik, akit az egyik f&eacute;l bar&aacute;tj&aacute;nak jel&ouml;l, nem sz&uuml;ks&eacute;ges, hogy a m&aacute;sik f&eacute;l is elfogadja, mivel itt csup&aacute;n az adatok kezel&eacute;s&eacute;ről van sz&oacute;, kapcsolatrendszert nem &eacute;p&iacute;t&uuml;nk.<br /> </p>  </td>   </tr> <tr>        <td valign=\"top\" align=\"right\" class=\"kiscim\">6.</td>            <td class=\"alap\">  <p>A Virtu&aacute;lis Pl&eacute;b&aacute;nia Port&aacute;l regisztr&aacute;lt felhaszn&aacute;l&oacute;jak&eacute;nt haszn&aacute;lhatod a Pl&eacute;b&aacute;nia &aacute;ltal ny&uacute;jtott szolg&aacute;ltat&aacute;sokat, funkci&oacute;kat, bőv&iacute;theted a Pl&eacute;b&aacute;nia adatb&aacute;zis&aacute;t, illetve felhaszn&aacute;lhatod azt munk&aacute;d, szem&eacute;lyes fejlőd&eacute;sed vagy sz&oacute;rakoz&aacute;sod &eacute;rdek&eacute;ben.</p>   <p>      A Pl&eacute;b&aacute;ni&aacute;n tal&aacute;lhat&oacute; anyagokat felhaszn&aacute;lhatod, azokra hivatkozhatsz,      azonban ez esetben k&eacute;rj&uuml;k megjel&ouml;lni a forr&aacute;st! <br />  </p>  </td>   </tr>                </tbody></table>   <br /> <br /> ' WHERE `id`='11';


";


//UPDATE misek SET torolte = datumig, datumig = datumtol, datumtol = torolte, torolte = ''  WHERE idoszamitas = 't' AND datumtol LIKE '2014-%' AND datumig LIKE '2014-%';

$queries = explode(';',$query);
foreach($queries as $query) {
	if(trim($query) != '') if(!$lekerdez=mysql_query($query)) die( "<p>HIBA #711!<br>$query<br>".mysql_error());	
}
echo 'mysql ok';


mysql_query("SET collation_connection = 'utf8_general_ci'");
mysql_query("ALTER DATABASE db CHARACTER SET utf8 COLLATE utf8_general_ci");
$result=mysql_query('show tables');
while($tables = mysql_fetch_array($result)) {
        foreach ($tables as $key => $value) {
         mysql_query("ALTER TABLE $value CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci");
   }}
echo "The collation of your database has been successfully changed!";


//misek
$idoszamitas = array('t' => 'télen', 'ny'=> 'nyáron');
$plusznap = array('t' => '', 'ny' => ' +1');
$minusznap = array('t' => '', 'ny' => ' -1');

$query = 'SELECT * FROM misek';
$result = mysql_query($query);    
while(($mise = mysql_fetch_array($result))) {
	if(date('Y',strtotime($mise['tol'])) != date('Y') AND date('Y',strtotime($mise['ig'])) != date('Y') AND preg_match('/[0-9]{4}-[0-9]{2}-[0-9]{2}/i', $mise['ig'])) {
		$tmp = array('t','ny');
		if($mise['idoszamitas'] == 't') { $tol = 'ig'; $ig = 'tol'; }
		elseif($mise['idoszamitas'] == 'ny') { $tol = 'tol'; $ig = 'ig'; }
		$query =  "UPDATE misek SET idoszamitas = '".$idoszamitas[$mise['idoszamitas']]."', ".$tol." = 'utolsó tanítási nap".$plusznap[$mise['idoszamitas']]."', ".$ig." = 'első tanítási nap".$minusznap[$mise['idoszamitas']]."' WHERE id = ".$mise['id']." LIMIT 1";
		echo $query."<br>";
		mysql_query($query);

	}

}

//neighbour
neighboursUpdate();

//
generateMassTmp();

//kepek
$query = 'SELECT * FROM kepek WHERE width IS NULL OR height IS NULL OR width = 0 OR height = 0 ';
$result = mysql_query($query);    
while(($kep = mysql_fetch_array($result))) {

	$file = "kepek/templomok/".$kep['tid']."/".$kep['fajlnev'];

	if(file_exists($file)) {
		if(preg_match('/(jpg|jpeg)$/i',$file)) {
			$src_img = ImagecreateFromJpeg($file);
		 	$kep['height'] = imagesy($src_img);  # original height
		    $kep['width'] = imagesx($src_img);  # original width

			$query =  "UPDATE kepek SET height = '".$kep['height']."', width = '".$kep['width']."' WHERE id = ".$kep['id']." LIMIT 1";
			if($config['debug'] > 0) echo $query."<br>";
			mysql_query($query);
		} else {
			if($config['debug'] > 0) echo "A kép nem jpg: ".$file."<br>";
		}
	} else {
		if($config['debug'] > 0) echo "Hiányzó kép: ".$file."<br>";
	}

}

/**/







?>