<?php 

//if(file_exists('../db.php')) include_once '../db.php';
/*
set_time_limit(600);
updateNextMise('','force');
*/
function insertGeocode() {
	$query = "SELECT cim, varos, terkep_geocode.lat,terkep_geocode.lng, templomok.id,templomok.nev FROM templomok LEFT JOIN terkep_geocode ON  templomok.id = terkep_geocode.tid ";
	$results = db_query($query);
	$c = 0;
	foreach($results as $result) {
		
		if($result['lat'] == '') {
///			print_R($result); echo "<br>";
			
				$query = "INSERT INTO terkep_geocode (tid,lat,lng) VALUES (".$result['id'].",".(rand(48583875,48854776)/1000000).",".(rand(22218475,22847443)/1000000).")";
				db_query($query,2);
				$c++;
		
				
				
		}
	}
	echo $c." templom random geocode beillesztve";
}


/* 
 * Elõször elérjük, hogy minden még létezõ misének legyen megfelelõje a terkep_misek_next táblában
 * 
 * Mivel elsõ körben több mint 18000 sort kell átnézni/létrehozni, ezért többször le kell futnia
 * mire hibaüzenet (maximum execution time of XX seconds exceeded ) nélkül megoldja a feladatot.
 */ 
function insertNextMise() {
	$query = "SELECT misek.id,terkep_misek_next.next FROM misek LEFT JOIN terkep_misek_next ON  terkep_misek_next.id = misek.id WHERE torles = '0000-00-00 00:00:00'";
	$results = db_query($query);
	$c = 0;
	foreach($results as $result) {
		if($result['next'] == '') {
			$next = getNextMise($result['id']);
			if($next != '') {
				$query = "INSERT INTO terkep_misek_next VALUES (".$result['id'].",".$next.")";
				db_query($query,2);
				$c++;
			}
		}
	}
	//echo $c." mise dátuma beillesztve";
	}

function updateNextMise($id = '',$flag = '') {
	$query = "SELECT * FROM misek, terkep_misek_next WHERE misek.id = terkep_misek_next.id";
	if($flag != 'force') $query .= " AND next < ".time();
	if($id != '') $query .= ' AND misek.id = '.$id; 
	$query .= " LIMIT 0,30";
	$misek = db_query($query);
	
	if(is_array($misek)) { foreach($misek as $mise) {
		//$next = getNextMise($mise['id'],$mise);
		//$query = "UPDATE terkep_misek_next SET next = ".$next." WHERE id = ".$mise['id']." ";
		//db_query($query);
		echo date('Y-m-d h:s',$next)."<br>";
	} }
	
}

function getNextMise($mid,$mise = array()) {
	$check = date('Y-m-d');
	
	$days  = array('','Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday');
	if($mise == array()) {
		$query = "SELECT * FROM misek WHERE id = ".$mid." LIMIT 0,1";
		$mise = db_query($query);
		$mise = $mise[0];
	}
	$datum = strtotime($check." ".$mise['ido']);
	
	if($mise['datumtol'] == '0000-00-00') $mise['datumtol'] = '0000-05-00';
	if($mise['datumig'] == '0000-00-00') $mise['datumig'] = '0000-05-00';
	
	$mise['datumtol'] = date('Y')."-".date('m-d',strtotime($mise['datumtol']));
	$mise['datumig'] = date('Y')."-".date('m-d',strtotime($mise['datumig']));

	$datumtol = strtotime($mise['datumtol']." ".$mise['ido']);
	$datumig = strtotime($mise['datumig']." ".$mise['ido']);
	
	//echo date('Y-m-d h:s',$datumig)."l<br>";
	
	if($mise['idoszamitas'] == 'ny') {
		if($datum < $datumtol) {
			$next = strtotime('next '.$days[$mise['nap']+1],$datumtol);
		} elseif( $datumtol <= $datum AND $datum <= $datumig) {
			$next = strtotime('next '.$days[$mise['nap']+1],$datum);
			if($next > $datumig)
				$next = strtotime('+1 year',strtotime('next '.$days[$mise['nap']+1],$datumtol));
		} elseif ($datumig < $datum) {
			$next = strtotime('+1 year',strtotime('next '.$days[$mise['nap']+1],$datumtol));
		}
	}
	elseif($mise['idoszamitas'] == 't') {
		if($datum < $datumtol) {
			$next = strtotime('next '.$days[$mise['nap']+1],$datum);
			if($next >= $datumtol)
				$next = strtotime('next '.$days[$mise['nap']+1],$datumig);
		} elseif( $datumtol <= $datum AND $datum <= $datumig) {
			$next = strtotime('next '.$days[$mise['nap']+1],$datumig);
		} elseif ($datumig < $datum) {
			$next = strtotime('next '.$days[$mise['nap']+1],$datum);
		}		
	}
	
	$next = strtotime(date('Y-m-d',$next)." ".$mise['ido']);
	
	//foreach($mise as $k=>$i) echo $k.":".$i." "; echo "<br>";
	
	return $next;
}
?>