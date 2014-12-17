<?php
include 'load.php';

$tid = rand(1,4111);

print_r(getMasses(rand(1,1)));

exit;

/*
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
UPDATE misek SET torolte = datumig, datumig = datumtol, datumtol = torolte, torolte = ''  WHERE idoszamitas = 't' AND datumtol LIKE '2014-%' AND datumig LIKE '2014-%';
ALTER TABLE misek CHANGE datumtol tol varchar(100);
ALTER TABLE misek CHANGE datumig ig varchar(100);

CREATE TABLE `miserend`.`events` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NULL,
  `year` VARCHAR(4) NULL,
  `date` DATE NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_UNIQUE` (`id` ASC),
  UNIQUE INDEX `name+year` (`name` ASC, `year` ASC));

  img/zaszloikon/hu.gif -> img/zaszloikon/h.gid

ALTER TABLE misek ADD COLUMN nap2 varchar(4) AFTER ido;

*/

//misek
$idoszamitas = array('t' => 'télen', 'ny'=> 'nyáron');
$plusznap = array('t' => '', 'ny' => ' +1');
$minusznap = array('t' => '', 'ny' => ' -1');

$query = 'SELECT * FROM misek';
$result = mysql_query($query);    
while(($mise = mysql_fetch_array($result))) {
	if(date('Y',strtotime($mise['tol'])) != date('Y') AND date('Y',strtotime($mise['ig'])) != date('Y') AND preg_match('/[0-9]{4}-[0-9]{2}-[0-9]{2}/i', $mise['ig'])) {
		$tmp = array('t','ny');
		$query =  "UPDATE misek SET idoszamitas = '".$idoszamitas[$mise['idoszamitas']]."', tol = 'utolsó tanítási nap".$plusznap[$mise['idoszamitas']]."', ig = 'első tanítási nap".$minusznap[$mise['idoszamitas']]."' WHERE id = ".$mise['id']." LIMIT 1";
		echo $query."<br>";
		mysql_query($query);
	}

}



//neighbour
neighboursUpdate();

//kepek
$query = 'SELECT * FROM kepek WHERE width IS NULL OR height IS NULL OR width = 0 OR height = 0 ';
$result = mysql_query($query);    
while(($kep = mysql_fetch_array($result))) {

	$file = "kepek/templomok/".$kep['kid']."/".$kep['fajlnev'];

	if(file_exists($file)) {
		$src_img == ImagecreateFromJpeg($file);
	 	$kep['height'] = imagesy($src_img);  # original height
	    $kep['width'] = imagesx($src_img);  # original width

		$query =  "UPDATE kepek SET height = '".$kep['height']."', width = '".$kep['width']."' WHERE id = ".$kep['id']." LIMIT 1";
		if($config['debug'] > 0) echo $query."<br>";
		mysql_query($query);
	} else {
		if($config['debug'] > 0) echo "Hiányzó kép: ".$file."<br>";
	}

}

?>