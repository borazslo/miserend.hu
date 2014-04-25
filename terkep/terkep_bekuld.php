<?php
include 'db.php';
include 'facebook.php';


//FELTÖLTÉS ÉS ELMENTÉS VAGY AMIT AKARTOK 
if(isset($_REQUEST['tid']) AND isset($_REQUEST['user']) AND isset($user['id']) AND $_REQUEST['user'] == $user['id']) {
	 if($_REQUEST['checked'] == 0 OR $user['id'] == '1819502454' OR $user['id'] == '100000577833955' OR $user['id'] == '100000966245096' OR isset($user['id'])) {
	
	$query = "INSERT INTO terkep_geocode_suggestion (tid,tchecked, slng, slat, sdistance, spoint, uid, stime ) 
		VALUES ('".$_REQUEST['tid']."','".$_REQUEST['checked']."','".$_REQUEST['nlng']."','".$_REQUEST['nlat']."','".$_REQUEST['distance']."',1,'".$_REQUEST['user']."','".date('Y-m-d H:i:s')."') ;";
	db_query($query);	
	$query = "UPDATE terkep_geocode SET lat = '".$_REQUEST['nlat']."', lng = '".$_REQUEST['nlng']."', checked = '2' WHERE tid = ".$_REQUEST['tid']." LIMIT 1";
	db_query($query);
	//echo "<!--";
	$_REQUEST['mute'] = 'true';
	include 'terkep_gyarto.php';
	//echo "-->";
		$return = array('return' => 'ok','text' => "Köszönjük, ezt sikerrel elmentettük! (Kellhet még idő, hogy a főtérképen is megjelenjen.)");
	} else {
	$return = array('return' => 'error',
	'text' => "Jelenleg csak a pirossal jelölt templomokat lehet vánszorogtatni! De, ha elég sokat helyre raksz, akkor szabad majd mást is. ;)");
	}
} else $return = array('return' => 'error','text' => 'Hiba volt itt bizony.');
echo json_encode($return);

?>