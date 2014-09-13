<?php
include 'load.php';

/*
ALTER TABLE kepek ADD COLUMN height integer;
ALTER TABLE kepek ADD COLUMN width integer;
DELETE FROM kepek wHERE kat <> 'templomok';
ALTER TABLE kepek DROP COLUMN kat;
ALTER TABLE kepek DROP COLUMN katnev;
ALTER TABLE kepek CHANGE kid tid int;
*/

//neighbour
//neighboursUpdate();

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