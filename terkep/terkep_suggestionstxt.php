<?php
include_once('db.php');
  // Set default timezone
  date_default_timezone_set('UTC +1');

   // TODO: nem kell minden
 $query = "
		SELECT s.*,templomok.* FROM terkep_geocode_suggestion as s
			LEFT JOIN templomok on templomok.id = s.tid
	WHERE stime > '2014-01-01 00:00:00'		
				LIMIT 10000";    
	$templomok = db_query($query);
	
	//echo "<pre>"; print_R($templomok);exit;
	
	

//	echo "<pre>";
	$content = "lat	lon	title	description	icon	iconSize	iconOffset\n";
	
	foreach($templomok as $templom) {
		if($templom['ismertnev'] == '') $templom['ismertnev'] = $templom['varos'];
		
		$description = "<h4 style=\"margin-top:-20px\">(".$templom['ismertnev'].")</h4>";
		$description .= "<p><a href=\"http://miserend.hu/?templom=".$templom['id']."\" target=\"_blank\">Ugrás a templom oldalára.</a></p>";
		
		
		if($templom['checked'] == 0) $icon = "images/icons/church-red.png";
		elseif($templom['checked'] == 1) $icon = "images/icons/church-green.png";
		else $icon = "images/icons/church-yellow.png";
		
		$icon = "images/icons/church-yellow.png";
		
		if($templom['slat'] == 0 OR $templom['slng'] == 0) $templom['slat'] = $templom['slng'] = ""; 
		$line = $templom['slat'].'	'.$templom['slng'].'	'.$templom['nev']."	".$description."	".$icon."	24,24	0,0\n";
		$c++;
		//echo $c."	";
		
		//if($templom['lat'] == '') echo "ÜRES!! ";
		$content .= $line;
		//echo htmlspecialchars($line);
		//if($c > 100) exit;		$c++;
	}
	echo $content;
	
?>