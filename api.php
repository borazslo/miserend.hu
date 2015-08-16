<?php
include_once('load.php');

 ini_set('memory_limit', '256M');
	
	if(isset($_REQUEST['datum']) AND preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/',$_REQUEST['datum'])) $datum = $_REQUEST['datum'];
	if(isset($_REQUEST['v']) AND preg_match('/^[0-9]{1,3}$/',$_REQUEST['v'])) $v = $_REQUEST['v'];

  // Set default timezone
  date_default_timezone_set('UTC +1');

if(isset($_REQUEST['q']) and $_REQUEST['q'] == 'updated') {
	if(!isset($datum)) { die('Nincs \'datum\' megadva.'); }
	if(!isset($v) OR ( $v < 2 AND $v > 4)) { die('Ez az API verzió ezt nem támogatja.'); }	
		
	$query = "SELECT id, moddatum FROM templomok WHERE  moddatum >= '".$datum."' ";											
    $result = mysql_query($query);
    //echo "-".print_r($result,1)."-";
    if(mysql_num_rows($result) > 0) echo "1";
    else echo "0";
	exit;
} 

if(isset($_REQUEST['q']) and $_REQUEST['q'] == 'json') { 
	if(isset($_REQUEST['id']) AND is_numeric($_REQUEST['id'])) {

	if( $v < 4 ) {
		echo json_encode(array('error'=>'Ez a funkció csak v3 fölött érhető el.'));
		exit;
	}

	$query = "
			SELECT t.*,orszagok.nev as orszag, megye.megyenev as megye,lat,lng, checked FROM templomok as t 
					LEFT JOIN orszagok ON orszagok.id = t.orszag 
					LEFT JOIN megye ON megye.id = megye 
					LEFT JOIN terkep_geocode ON terkep_geocode.tid = t.id 
					WHERE t.id = ".$_REQUEST['id']." 
					LIMIT 1";
	$result = mysql_query($query);    
	$templomok =  mysql_fetch_array($result);

	unset($templomok['log']);
	$return['templom'] = $templomok;
					
	$query = "
			SELECT * FROM misek 
					WHERE torles = '0000-00-00 00:00:00' 
					AND tid = ".$_REQUEST['id']."
					LIMIT ".$limit;
	$result = mysql_query($query);    
	$return['misek'] = array();
	while(($mise = mysql_fetch_array($result))) {
		$return['misek'][] = $mise;
	}
					
	//echo "<pre>".print_r($return,1);
	echo json_encode($return);
	} else {
		echo json_encode(array('error'=>'id nélkül mit érek én?'));
	
	}
}
  
if(isset($_REQUEST['q']) and $_REQUEST['q'] == 'sqlite') {

	$sqllitefile = 'fajlok/sqlite/miserend_v'.$v.'.sqlite3';
    if (file_exists($sqllitefile) && strtotime("-20 hours") < filemtime($sqllitefile) AND $config['debug'] == 0 AND !isset($datum)) {
        header("Location: /".$sqllitefile);
        exit;
    }

    if(generateSqlite($v,'fajlok/sqlite/miserend_v'.$v.'.sqlite3')) {
		//Sajnos ez itten nem működik... Nem lesz szépen letölthető.  Headerrel sem
	    //$data = readfile($sqllitefile); exit($data);
		header("Location: /".$sqllitefile);
		die();
    }
    else {
    	die('Hiba történt az sqlite3 fájl léterhozása közben. Elnézést.');

    }
}

?>