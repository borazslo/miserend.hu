<?php
require_once 'db.php';

function rank_get($order = 'point',$uid = '') {
   $query = "SELECT * FROM rank ";
   if($uid) $query .= " WHERE uid = '".$uid."' ";
   if($order) $query .= " ORDER BY ".$order." DESC ";
   $rank = db_query($query);
   $return = array();
   
   if(!is_array($rank)) return array();
   $c = false;
   foreach($rank as $k=>$r) {
		$return[$r['uid']] = $r;
		$return[$r['uid']]['rank']=$k+1;
		if($r['uid'] == '1819502454') $c = true;
		if($c) $return[$r['uid']]['rank']--;
		
		
   }
   if($uid) return $return[$uid];
   return $return;
   /* Ezt még lehetne optimalizálni, 
     hogy rank_update-kor belekerüljenek rank-ok,
	 így limitált select mehetne.
	*/
}

function rank_update($uid = '') {
    $query = "SELECT * FROM geocode_suggestion";
	if($uid) $query .= " WHERE uid = '".$uid."' ";
	$suggestions = db_query($query);
	foreach($suggestions as $sugg) {  
	 if($sugg['schecked']>0 AND $sugg['schecked'] != 'x') {
		$user[$sugg['uid']]['point']+=$sugg['spoint'];
		$user[$sugg['uid']]['distance']+=$sugg['sdistance'];
		$user[$sugg['uid']]['suggestion']++;
		
		if(!in_array($sugg['tid'],$tids)) {
			$user[$sugg['uid']]['marker']++;
			$tids[] = $sugg['tid']; 
		}
	 } elseif($sugg['schecked'] != 'x') {
		$user[$sugg['uid']]['point_uc']+=$sugg['spoint'];
		$user[$sugg['uid']]['distance_uc']+=$sugg['sdistance'];
		$user[$sugg['uid']]['suggestion_uc']++;
		if(!in_array($sugg['tid'],$almost_tids)) {
			$user[$sugg['uid']]['marker_uc']++;
			$almost_tids[] = $sugg['tid']; 
		}
	}	
	$user[$sugg['uid']]['uid'] = $sugg['uid'];
	} 
	$fields = array('uid','point','point_uc','distance','distance_uc','suggestion','suggestion_uc','marker','marker_uc');
	
	foreach($user as $u) {
		$set = ''; $values = '';
		foreach($fields as $k=>$f) {
		   if($u[$f]==false)$u[$f]=0;
			$set .= $f." = ".$u[$f];
			$values .= $u[$f];
		  if(count($fields)-1>$k) {
				$set .= ', '; 
				$values .= ', '; }
		}
		$values .= ', '.time();
		$set .= ', time = '.time();
   $update = "UPDATE rank SET $set WHERE uid = ".$u['uid'].";";
   $insert = "INSERT INTO rank VALUES (".$values.");";
	//echo "--".db_query($update,'x','mysql_affected_rows')."--";
	mysql_query($update);
	if(mysql_affected_rows==0) db_query($insert);
 
	}
	if($uid) return $user[$uid];
	return $user; 
}

function rank_html($user) {  
$r ="&nbsp;&nbsp;"; $r .="&nbsp;&nbsp;";		
			for($i=0;$i < strlen($user['point']);$i++) {
				$r .= "<img id=\"rankimg\" src=\"icons/green/".$user['point']{$i}.".png\">"; 		}
			$r .="&nbsp;&nbsp;";
			foreach(array('p','t') as $i) {
				$r .= "<img id=\"wordimg\" src=\"icons/green/".$i.".png\">"; }
			for($i=0;$i < strlen($user['point_uc']);$i++) {
				$r .= "<img id=\"ucimg\" src=\"icons/yellow/".$user['point_uc']{$i}.".png\">"; 	}
$r .="&nbsp;&nbsp;"; $r .="&nbsp;&nbsp;";
			for($i=0;$i < strlen($user['marker']);$i++) {
				$r .= "<img id=\"rankimg\" src=\"icons/green/".$user['marker']{$i}.".png\">"; 		}
			$r .="&nbsp;&nbsp;";
			foreach(array('t','e','m','p','l','o','m') as $i) {
				$r .= "<img id=\"wordimg\" src=\"icons/green/".$i.".png\">"; }
			for($i=0;$i < strlen($user['marker_uc']);$i++) {
				$r .= "<img id=\"ucimg\" src=\"icons/yellow/".$user['marker_uc']{$i}.".png\">"; 	}				
$r .="&nbsp;&nbsp;";			$r .="&nbsp;&nbsp;";
			$user['distance_uc'] = number_format($user['distance_uc']/1000);
			$user['distance'] = number_format($user['distance']/1000);
			
			for($i=0;$i < strlen($user['distance']);$i++) {
				$r .= "<img id=\"rankimg\" src=\"icons/green/".$user['distance']{$i}.".png\">"; 		}
			$r .="&nbsp;&nbsp;";
			foreach(array('k','m') as $i) {
				$r .= "<img id=\"wordimg\" id=\"rank-img\" src=\"icons/green/".$i.".png\">"; }
			for($i=0;$i < strlen($user['distance_uc']);$i++) {
				$r .= "<img id=\"ucimg\" src=\"icons/yellow/".$user['distance_uc']{$i}.".png\">"; 	}				
$r .="&nbsp;&nbsp;";
			$r .= "<img title=\"Ez itten a helyetésed. Elég jó vagy!\" id=\"rank-img\" src=\"icons/green/".$user['rank'].".png\">";

return $r; 
}
?>
