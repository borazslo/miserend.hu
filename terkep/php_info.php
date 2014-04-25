
<?php

include_once('db.php');
echo"<pre>";
//print_R(db_query('SHOW TABLES'));
///print_R(db_query('SELECT * FROM misek LIMIT 4'));

/*
 * CSV-ből új geocode-k bevitele 
 */
$row = 1;
if (($handle = fopen("miserend_locations.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $num = count($data);
        $row++;
		print_R($data);
		$vane = db_query('SELECT * FROM terkep_geocode WHERE tid = '.$data[3].' LIMIT 1');
		if(isset($vane)) {
			$query = "UPDATE terkep_geocode SET tid = ".$data[3].", address2 = '".$data[4]."', lng = '".$data[2]."', lat = '".$data[1]."', checked = 1 WHERE tid = ".$data[3]." LIMIT 1";
		} else
			$query = "INSERT INTO terkep_geocode (tid,address2,lng,lat,checked) VALUES ('".$data[3]."','".$data[4]."','".$data[2]."','".$data[1]."',1)";
		echo $query."<br>";
		db_query($query);
		//if($row > 10) break;
    }
    fclose($handle);
}
/* vége */


/*
// Affiche toutes les informations, comme le ferait INFO_ALL
phpinfo();

// Affiche uniquement le module d'information.
// phpinfo(8) fournirait les mêmes informations.
phpinfo(INFO_MODULES);
*/
?>
