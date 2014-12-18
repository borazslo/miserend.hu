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