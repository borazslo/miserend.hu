<?php
include_once('db.php');
  // Set default timezone
  date_default_timezone_set('UTC +1');

   // TODO: nem kell minden
 $query = "
		SELECT t.*,orszagok.nev as orszag, megye.megyenev as megye,lat,lng, terkep_geocode.* FROM templomok as t 
				LEFT JOIN orszagok ON orszagok.id = t.orszag 
				LEFT JOIN megye ON megye.id = megye 
				LEFT JOIN terkep_geocode ON terkep_geocode.tid = t.id 			
				
				LIMIT 10000";    
	$templomok = db_query($query);
	//echo "<pre>"; print_R($templomok);exit;
	
	
	$file = 'pois.txt';
	if(!isset($_REQUEST['mute'])) echo "<pre>";
	$content = $content_red = $content_green = "id	lat	lon	title	description	icon	iconSize	iconOffset\n";
	
	foreach($templomok as $templom) {
		if($templom['ismertnev'] == '') $templom['ismertnev'] = $templom['varos'];
		
		$description = "<h4 style=\"margin-top:-20px\">(".$templom['ismertnev'].")</h4>";
		$description .= "<p><a href=\"http://miserend.hu/?templom=".$templom['id']."\" target=\"_blank\">Ugrás a templom oldalára.</a></p>";
		
		
		if($templom['checked'] == 0) {
			if(preg_match('/templom/i',$templom['nev']." ".$templom['ismertnev']))
				$icon = "images/icons/church-red.png";
			else
				$icon = "images/icons/church-red2.png";
		
		}
		elseif($templom['checked'] == 1) $icon = "images/icons/church-green.png";
		elseif($templom['checked'] == 2) $icon = "images/icons/church-green2.png";
		else $icon = "images/icons/church-yellow.png";
		if($templom['lat'] == 0 OR $templom['lng'] == 0) $templom['lat'] = $templom['lng'] = ""; 
		$line = $templom['id']."	".$templom['lat'].'	'.$templom['lng'].'	'.$templom['nev']."	".$description."	".$icon."	24,24	-12,-24\n";
		$c++;
		if(!isset($_REQUEST['mute']))  echo $c."	";
		
		if($templom['lat'] == '') if(!isset($_REQUEST['mute'])) echo "ÜRES!! ";
		
		if($templom['checked'] == 0) $content_red .= $line;
		elseif($templom['checked'] == 1 OR $templom['checked'] == 2) $content_green .= $line;
		$content .= $line;
		if(!isset($_REQUEST['mute']))  echo htmlspecialchars($line);
		//if($c > 100) exit;		$c++;
	}
	file_put_contents($file, $content);
	file_put_contents('pois_green', $content_green);
	file_put_contents('pois_red', $content_red);
	/*
	// Open the file to get existing content
	//$current = file_get_contents($file);
	// Append a new person to the file
	//$current .= $input['tid'].";".$input['pid'].";'".$input['text']."'\n";
	//$current .= $inputJSON."\n";
	// Write the contents back to the file
	file_put_contents($file, $current);*/

?>