<?
include_once('db.php');

if(!array_key_exists('suggestions',$_GET) OR $_GET['suggestions'] == '') {
	
	if(array_key_exists('tid',$_GET) AND $_GET['tid'] !='') $query = "SELECT * FROM terkep_geocode, templomok WHERE terkep_geocode.tid = templomok.id AND templomok.id = ".$_GET['tid'];
	else $query = "SELECT * FROM terkep_geocode, templomok WHERE terkep_geocode.tid = templomok.id AND lat < ".$_GET['latNE']." AND lat > ".$_GET['latSW']." AND lng < ".$_GET['lngNE']." AND lng > ".$_GET['lngSW'];;
	$query_result = db_query($query);
	foreach($query_result as $r) {
		$r['icon'] = 'church-'.$r['checked'];
		$r['type'] = 'church';
		$r['draggable'] = true;
		$result[$r['tid']] = $r;
	}
} else {
	$query = "SELECT * FROM geocode g, geocode_suggestion s, templom t WHERE s.tid = t.tid AND t.tid = s.tid  AND (schecked IS NULL ) AND slat < ".$_GET['latNE']." AND slat > ".$_GET['latSW']." AND slng < ".$_GET['lngNE']." AND slng > ".$_GET['lngSW'];
	$query_result = db_query($query);
	foreach($query_result as $r) {
		$r['icon'] = 'suggestion-'.$r['tchecked'];
		$r['type'] = 'suggestion';
		$r['lat'] = $r['slat'];
		$r['lng'] = $r['slng'];
		$r['draggable'] = false;
		$result[$r['tid']] = $r;
	}



}
	$return['query'] = $query;
	$return['result'] = $result	;
	$return['count'] = count($result);

echo  json_encode($return);




?>