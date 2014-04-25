Közeli templomokat nézünk. Gyanúsan erőforrás igényűek leszünk. :)<br><br>
<?php
include 'db.php';

//echo "<pre>".print_R(db_query('SELECT * FROM eszrevetelek ORDER BY datum DESC LIMIT 10'),1)."</pre>"; exit;

$templomok = db_query('SELECT szomszedos1, szomszedos2, templomok.id, lng, lat,  templomok.varos, templomok.nev FROM templomok LEFT JOIN terkep_geocode ON id = tid 
	WHERE templomok.ok = "i" 
		
	ORDER BY frissites DESC LIMIT 1000000',1);
$i = 0;
foreach($templomok as $templom) {
	set_time_limit('600');
	$ds10 = $ds = array();
	$c = 0;
	$szomszedsag = array();
	$szomszedsag10 = array();
	foreach($templomok as $szomszed) {
		
		$lat1 = $templom['lat'] * M_PI / 180;
		$lat2 = $szomszed['lat'] * M_PI / 180;
		$long1 = $templom['lng'] * M_PI / 180;
		$long2 = $szomszed['lng'] * M_PI / 180;
		$R = 6371; // km
		$d = $R * acos(sin($lat1) * sin($lat2) + cos($lat1) * cos($lat2) * cos($long2 - $long1)) * 1000;
		
		if($d < 10000 AND $szomszed['id'] <> $templom['id']) {
			$szomszedsag10[$d] = array('id'=>$szomszed['id'],'d'=>$d,'nev'=>$szomszed['nev'],'varos'=>$szomszed['varos']);
			$ds10[$d] = $d;
			}
		if($szomszed['id'] <> $templom['id']) {
			$szomszedsag[$d] = array('id'=>$szomszed['id'],'d'=>$d,'nev'=>$szomszed['nev'],'varos'=>$szomszed['varos']);			
			$ds[$d] = $d;
		}
		//if($c>10) break; $c++;
	}
	array_multisort($ds10, SORT_ASC, $szomszedsag10);
	array_multisort($ds, SORT_ASC, $szomszedsag);
	
	$szomszedsag = array_slice($szomszedsag, 0, 1); 
	//ksort($szomszedsag10);
	//reset($szomszedsag10);
	$szomszedsag10 = array_slice($szomszedsag10, 0, 11); 
	
	$nyers = '';
	echo " ".$templom['frissites']." <a href=\"http://miserend.hu/?templom=".$templom['id']."\">".$templom['nev']." (".$templom['varos'].")</a><br/>";
	foreach($szomszedsag10 as $szomszed) {
		$nyers .= $szomszed['id'].",";		
		//echo "<div style='margin-left:40px;'>".print_r($szomszed,1)."</div>";
	}
	
	$elso = array_shift(array_values($szomszedsag));
	$elso = "".$elso['id']."";
	if($nyers == '') $nyers = '';
	if($templom['szomszedos1'] == "") {}
	
		$query = "UPDATE templomok SET szomszedos1 = '".$elso."' WHERE id = ".$templom['id']." LIMIT 1";
		echo $query."<br/>";
		db_query($query);
		$query = "UPDATE templomok SET szomszedos2 = '".$nyers."' WHERE id = ".$templom['id']." LIMIT 1";
		echo $query."<br/>";
		db_query($query);
	/*
		if($templom['szomszedos1'] != '') echo "szomszédos1: ".$templom['szomszedos1']."<br/>";
		if($templom['szomszedos2'] != '') echo "szomszédos2: ".$templom['szomszedos2']."<br/>";
		echo $elso."<br/>";
		echo $nyers."<br/>";
		if($templom['szomszedos1'] != '' OR $templom['szomszedos2'] != '' ) {
			echo "<pre>".print_R($szomszedsag,1)."</pre>";
			echo "<pre>".print_R($szomszedsag10,1)."</pre>";
		}
	*/
	
	
	//echo "<br><br>";
	
	
	//if($i>10) break; $i++;
}

?>