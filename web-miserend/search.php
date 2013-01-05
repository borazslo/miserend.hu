<?php
// TODO: valahogy jelezni a népnek, hogy alapértelmezetten csak 30km-es körzetet keresünk 
include_once('../db.php');

$nyelvek = array('magyar' => 'h','latin' => 'va','német' => 'de','szlovák' => 'sk','horvát' => 'hr','lengyel' => 'pl','szlovén' => 'si','román' => 'ro','angol' => 'en','görög' => 'gr','olasz' => 'it','francia' => 'fr','spanyol' => 'es');
$napok = array('','vasárnap','hétfő','kedd','szerda','csütörtök','péntek','szombat','vasárnap');
$days  = array('','Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday');

$napok2id = array('hétfő' =>  2,'kedd' => 3,'szerda' => 4,'csütörtök' => 5,'péntek' => 6,'szombat' => 7,'vasárnap' => 1);

$return = array();

function searchLog() {
	global $return;
	$log = new stdClass();
	$log->time = time();
	$log->request = $_REQUEST;
	$log->count = $return['count'];
	if(array_key_exists('error', $return))  $log->error = $return['error'];
	global $args;
	$log->query = $return['query'];
	$log->args = $args;
	$fp = fopen('search.log', 'a');
	fwrite($fp, json_encode($log)."\r\n");
	fclose($fp);
	
	
}

class Position {
	/*protected $lng;
	protected $lat;*/
	
	public function __construct($lat,$lng) {
		$this->lat = $lat;
		$this->lng = $lng;
	}
	//http://janmatuschek.de/LatitudeLongitudeBoundingCoordinates
	public function getDistance($position) {
		$lat1 = deg2rad($this->lat);
		$lng1 = deg2rad($this->lng);
		$lat2 = deg2rad($position->lat);
		$lng2 = deg2rad($position->lng);
		$d = (3958*3.1415926*sqrt(($lat2-$lat1)*($lat2-$lat1) + cos($lat2/57.29578)*cos($lat1/57.29578)*($lng2-$lng1)*($lng2-$lng1))/180)*1000;
		$d = acos(sin($lat1) * sin($lat2) + cos($lat1) * cos($lat2) * cos($lng1 - $lng2)) * 6371 * 1000;
		
		$return = new stdClass();
		$return->raw = $d;
		$return->formatted = $this->formatDistance($d);
		
		return $return;
	}

	//	http://janmatuschek.de/LatitudeLongitudeBoundingCoordinates
        function getBounds($distance_in_m) {    	
        		$r = ($distance_in_m / 1000) / 6371;
        		$lat1 = deg2rad($this->lat) - $r;
        		$lat2 = deg2rad($this->lat) + $r;
        		
        		$lon = asin(sin($r)/cos(deg2rad($this->lat)));    		
        		$lng1 = deg2rad($this->lng) - $lon;
        		$lng2 = deg2rad($this->lng) + $lon;
        		
        		if($lat1>$lat2) {
        				$tmp = $lat2;
        				$lat2 = $lat1;
        				$lat1 = $tmp; 
        		}
        		if($lng1>$lng2) {
        			$tmp = $lng2;
        			$lng2 = $lng1;
        			$lng1 = $tmp;
        		}
        		

        		return array(rad2deg($lat1),rad2deg($lat2),rad2deg($lng1),rad2deg($lng2));
        		
         }
	
	private function formatDistance($distance,$limits = array()) { // méterben
		if($limits == array()) $limits = array(1000,10000);
		if($distance < $limits[0]) {
			$dis = number_format($distance,0)." m";
		}
		elseif($distance < $limits[1]) {
			$dis = number_format($distance/1000,1)." km";
		}
		else {$dis = number_format($distance/1000,0)." km";
		}
		return $dis;
	}
}
class Templom {
	protected $id;

	public function __construct($id,$data = array()) {
		$this->id = $id;
		//$this->data = $data;
		
	}
	
}

function formatTime($minutes,$limits = array()) { //percben
	if($limits == array()) $limits = array(90,300);
	if($minutes < $limits[0]) {
		$time = number_format($minutes,0)." perc";
	}
	elseif($minutes < $limits[1]) {
		$ora = (int)($minutes/60);
		$perc = 1; //settype(60 / $minutes, int);
		$time = $ora." óra ".$perc. " perc";
		
	}
	else {$time = number_format($minutes/60,0)." óra";
	}
	return $time;
}

function getMisek($args = NULL) {

	$query = "SELECT * FROM misek, terkep_misek_next WHERE misek.id = terkep_misek_next.id AND `torles` = '0000-00-00 00:00:00'";

	if($args != NULL AND property_exists($args, 'time')) $query .= " AND next between ".$args->time->from." and ".$args->time->till;

	if($args != NULL AND property_exists($args, 'zene')) {
		$query .= $args->zene;
	}
	if($args != NULL AND property_exists($args, 'diak')) {
		$query .= $args->diak;
	}
	if($args != NULL AND property_exists($args, 'tid')) {
		if(!is_array($args->tid)) $args->tid = array($args->tid);	
		$query .= " AND (";
		foreach($args->tid as $k => $tid) {
			$query .= " `templom` = ".$tid;
			if($k < count($args->tid)-1) $query .= ' OR ';
		}
		$query .= ")";		
	}
	if($args != NULL AND property_exists($args, 'lang')) {
		if(!is_array($args->lang)) $args->lang = array($args->lang);
		$query .= " AND (";
		foreach($args->lang as $k => $lang) {
			$query .= " `nyelv` LIKE  '%".$lang."%' ";
			if($k < count($args->lang)-1) $query .= ' OR ';
		}
		$query .= ")";
	}

	$query .= " ORDER BY next ";
	//echo  $query;
	global $return;
	$return['query'][] = $query;
	$ret = db_query($query);

	$check['datum'] = date('Y-m-d');
	$check['day'] = date('w');
	if($check['day']==0) $check['day']=7;

	global $napok;
	if(is_array($ret)) foreach($ret as $k => $i) {
		
		$next['raw'] = $i['next'];	
		if(date('m-d',strtotime($i['next'])) == date('m-d')) $next['formatted'] = date('H:i',$i['next']);
		elseif ($i['next']-time() < 60*60*24*7) $next['formatted'] = $napok[date('w',$i['next'])+1]." ".date('H:i',$i['next']); //xxx
		else $next['formatted'] = date('m. d. H:i',$i['next']);
		$ret[$k]['next'] = $next;
		
		$regi = (time()-strtotime($i['moddatum']))/86400/365;
		$ret[$k]['lastchanged']['raw'] = $regi;
		$sign = '';
		for($i=1;$i<$regi;$i++) $sign .= '!';
		$ret[$k]['lastchanged']['sign'] = $sign;
		$ret[$k]['lastchanged']['formatted'] = (int) $regi;
		
	}
	return $ret;
}

function sqlTemplomok($args = array()) {
	global $return;
	global $currentPosition;
	
	$query = "SELECT * FROM `templomok`,`terkep_geocode` WHERE `id` = `tid` ";
	$query.= " AND ";
	$pieces = $args->text;
	foreach($pieces as $k => $piece) {
		$query.=
		"(`nev` LIKE  '%".$piece."%'
		OR  `ismertnev` LIKE  '%".$piece."%'
		OR  `varos` LIKE  '%".$piece."%'
		OR  `cim` LIKE  '%".$piece."%')";
		if($k<count($pieces)-1) $query .= ' AND ';
	}

	if(property_exists($args, 'distance')) {
		list($lat1,$lat2,$lon1,$lon2) = $currentPosition->getBounds($args->distance);
		$query .= " AND (lat between $lat1 and $lat2 AND lng between $lon1 and $lon2)";
		$tmp = new Position($lat1,$lon1);
	}
	$return['query'][] = $query;
	
	//$query .= " LIMIT 0,10";

	return db_query($query);
}
function processSearch($search) {
	//$search = urlencode($search);
	$search = urldecode($search);
	
	$args = new stdClass();
	
	$search = preg_replace('/,/', ' ', $search);
	
	/* van-e idézőjeles rész */
	$pieces = array();
	preg_match_all('/\"(.*?)\"/', $search, $matches,PREG_SET_ORDER);
	foreach($matches as $m) {
		$pieces[] = $m[1];
	}
	$search = preg_replace('/\"(.*?)\"/', '', $search);
	
	
	/* van-e nap */
	$args->time = new stdClass();
	$matches = array();
	preg_match_all('/(^| )((holnapután)|(holnap)|(ma))($| )/', $search, $matches);
		if($matches[0]!=array()) {
			if($matches[0][0] == 'ma') $day = time();
			elseif($matches[0][0] == 'holnap') $day = strtotime('tomorrow',time());
			elseif($matches[0][0] == 'holnapután') $day = strtotime('+2 days',time());
		}
	//
	$search = preg_replace('/(^| )((holnapután)|(holnap)|(ma))($| )/', '', $search);
	
	preg_match_all('/(^| )((hétfő)|(kedd)|(szerda)|(csütörtök)|(péntek)|(szombat)|(vasárnap))($| )/', $search, $matches);
		global $days;
		global $napok2id;
		if($matches[0]!=array()) {
			
			$tmp = $napok2id[trim($matches[0][0])];
			$day = strtotime('next '.$days[$tmp],time());
		}
		if(!isset($day)) $day = time();
		$args->time->day = date('Y-m-d',$day); 
	$search = preg_replace('/(^| )((hétfő)|(kedd)|(szerda)|(csütörtök)|(péntek)|(szombat)|(vasárnap))($| )/', '', $search);
	

	/* van-e óra */
	preg_match_all('/(([0-9]{1,3})( )?(óra))/', $search, $matches);
		if($matches[0]!=array() AND (int) $matches[0][0] < 25 AND (int) $matches[0][0] >= 0) {
		$args->time->hour = date('H:s',strtotime("+".$matches[2][0]." hours",time()));
		}
	$search = preg_replace('/(([0-9]{1,3})( )?(óra))/', '',$search);

	preg_match_all('/([0-9]{1,2})(:)([0-9]{1,2})/', $search, $matches);
	if($matches[0]!=array() AND (int) $matches[1][0] < 25 AND (int) $matches[1][0] >= 0 AND (int) $matches[3][0] < 60 AND (int) $matches[3][0] >= 0) {
		$args->time->hour = $matches[0][0];
	}
	if(!property_exists($args->time, 'hour')) {
		if($args->time->day == date('Y-m-d')) $args->time->hour = date('H:i',time());
		else $args->time->hour = '00:00';
	}
	$search = preg_replace('/([0-9]{1,2})(:)([0-9]{1,2})/', '',$search);
	
	
	$args->time->from = strtotime($args->time->day." ".$args->time->hour);
	$args->time->till = $args->time->from + (60*60*24*8); //alapértelmezetten 14 napot keresünk előlre
	//Todo:óra-e?
	//print_R($args->time);
	
	/* van-e távolság */
	$matches = array();
	preg_match_all('/(([0-9]+)( )?(km|m))/', $search, $matches);
	// csak az elsőt vesszük figyelembe!
	if(count($matches[0])>0) {
		$d = $matches[2][0];
		if($matches[4][0] == 'km') $d = $d*1000;
		$args->distance = $d;
	}
	$search = preg_replace('/(([0-9]+)( )?(km|m))/', '', $search);
	
	/* diák-e*/
	$matches = array();
	preg_match_all('/((nem diák)|(diák))/', $search, $matches);
		if($matches[0] != array()) {
			$args->diak = " AND (";
			foreach($matches[0] as $k => $i) {
				if($i == 'diák') $args->diak .= " `milyen` LIKE '%d%' ";
				elseif($i == 'nem diák') $args->diak .= " `milyen` NOT LIKE '%d%'";
			}
			$args->diak .= ")";
		}
	$search = preg_replace('/((nem diák mise)|(nem diákmise))/', '', $search);
	$search = preg_replace('/((diák mise)|(diákmise))/', '', $search);
	$search = preg_replace('/((diák)|(nem diák))/', '', $search);
	
	/* zenés-e*/
	$matches = array();
	preg_match_all('/((csendes)|(gitáros)|(orgonás)|(zenés))/', $search, $matches);
		$types = array('csendes'=>'cs','gitáros'=>'g','orgonás'=>'o');
		if($matches[0] != array()) {
			$args->zene = " AND (";
			foreach($matches[0] as $k => $i) {
				if($i == 'orgonás') $args->zene .= " (`milyen` NOT LIKE '%g%' AND `milyen` NOT LIKE '%cs%' )";
				elseif($i == 'zenés') $args->zene .= " (`milyen` LIKE '%g%' OR `milyen` NOT LIKE '%cs%' )";
				else $args->zene .= " `milyen` LIKE '%".$types[$i]."%'";
				if($k < count($matches[0])-1) $args->zene .= ' OR ';
			}
		$args->zene .= ")";
		}
	$search = preg_replace('/((csendes)|(gitáros)|(orgonás)|(zenés))/', '', $search);
	
	/* idegen nyelvű-e */
	global $nyelvek;
	$matches = array();
	preg_match_all('/((magyar)|(latin)|(német)|(szlovák)|(horvát)|(lengyel)|(szlovén)|(román)|(angol)|(görög)|(olasz)|(francia)|(spanyol))/', $search, $matches);
	//print_r($matches);
	if($matches[0]!=array()) {
		foreach($matches[0] as $nyelv) {
			$args->lang[] = $nyelvek[$nyelv];
		}
	
	}
	$search = preg_replace('/((magyar)|(latin)|(német)|(szlovák)|(horvát)|(lengyel)|(szlovén)|(román)|(angol)|(görög)|(olasz)|(francia)|(spanyol))/', '', $search);
	
	
	if(trim($search)=='' AND ($args != NULL AND !property_exists($args, 'distance'))) $args->distance = 30000;
		
	$search = preg_replace('/  /', ' ', $search);
	$search = trim($search);
	
	/* maradék szavakra szedés*/
	$pieces = array_merge($pieces,explode(' ',$search));
	
	$args->text = $pieces;
	
	return $args;
}

$currentPosition = new Position($_REQUEST['lat'],$_REQUEST['lng']);


if($_REQUEST['search']=='') $search = "10 km";
else $search = $_REQUEST['search'];
search($search);

function search($search) {
	//$search = urldecode($search);
	global $return;
	
	global $args;
	$args = processSearch($search);

	//print_R($args);
	
	$templomok = sqlTemplomok($args);

	if(!is_array($templomok)) {
		$return['count'] = 0;
		$return['error'] = 'templomok';
		echo json_encode($return);
		searchLog();
		exit;
	}

	foreach($templomok as $templom) {
		$tid = $templom['id'];
	
		$templ = new Templom($templom['id']);
		$templ->position = new Position($templom['lat'],$templom['lng']);
	
		global $currentPosition;
		
		$templ->distance = $currentPosition->getDistance($templ->position);
		$templ->distance->url = 'https://maps.google.com/?saddr='.$templ->position->lat.','.$templ->position->lng.'&daddr='.$currentPosition->lat.','.$currentPosition->lng;
	
		$templ->sql = $templom;
		if($templ->sql['ismertnev'] == '') $templ->nev =  $templ->sql['nev'];
		else $templ->nev = $templ->sql['ismertnev'];
	
		$temploms[$tid] = $templ;
		
		/* a túl messze lévők kiszűrése */
		if(property_exists($args, 'distance') AND $templ->distance->raw > $args->distance) {
				unset($temploms[$tid]);
		}
	}

	foreach ($temploms as $key => $row) {
		$distance[$key]  = $row->distance->raw;
		$nev[$key] = $row->sql['nev'];
		$args->tid[] = $row->sql['id']; 
	}
	$misek = getMisek($args);
	if(!is_array($misek)) {
	$return['count'] = 0;
	$return['error'] = 'misek';
	$return['debug'] = $temploms;
	//$return['results'] = $temploms;
	echo json_encode($return);
	
	searchLog();
	exit;
} 
	
	
foreach($misek as $k=>$i) {
	$misek[$k]['templom'] = $temploms[$i['templom']];
	$misek[$k]['time'] = formatTime($temploms[$i['templom']]->distance->raw / 2500 * 60); // sebesslg 2500 m/h
	
	$sortDistance[] = $misek[$k]['templom']->distance->raw;
	$sortNext[] = $misek[$k]['next']['raw'];
}

//print_R($sortDistance);

array_multisort($sortNext, SORT_ASC, $sortDistance, SORT_ASC, $misek);

$return['results'] = $misek;



$return['count'] = count($return['results']);
if(isset($_REQUEST['limit'])) {
	$c = 0;
	foreach($return['results'] as $k=>$i) {
		$c++;
		if($_REQUEST['limit']<$c) unset($return['results'][$k]); 	
	}
}

}



//foreach($return['results'] as $k) {	echo $k['nev']." - ".$k['varos']." (".$k['distance2'].")<br>";} unset($return['results']);




echo json_encode($return);
searchLog();
?>