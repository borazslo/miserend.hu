<?php
include 'db.php';
include 'facebook.php';

if(isset($_REQUEST['tid']) AND $user['id'] == '1819502454' AND 6 == 8) {
	if($_REQUEST['checked'] <> 1) {
	db_query("DELETE FROM terkep_geocode WHERE tid = ".$_REQUEST['tid']." LIMIT 1");
	$return = array('return' => 'ok','text' => "Köszönjük, ezt sikerrel töröltük! (Kellhet még idő, hogy a főtérképen is megjelenjen.)".print_r($_REQUEST,1));
	$_REQUEST['mute'] = 'true';
	include 'terkep_gyarto.php';
	} else $return = array('return' => 'error','text' => 'Légyszi-légyszi ne ezt!');

} else $return = array('return' => 'error','text' => 'Hiba volt itt bizony.');
echo json_encode($return);

?>

