<?php

$vars['title'] = 'JOSM összeköttetés';
$vars['template'] = 'layout_simpliest';

$churches = searchChurches(array());

if(isset($_REQUEST['page']) AND is_numeric($_REQUEST['page'])) 
	$offset = $_REQUEST['page'];
else $offset = 0;
$limit = 30;

$query = "SELECT t.id FROM templomok t 
	            LEFT JOIN osm ON osm.tid = t.id 
	            WHERE osm.type IS NULL and ok = 'i' 
				ORDER BY t.id LIMIT ".$limit." OFFSET ".($limit * $offset)." ;";
$query = "SELECT t.id FROM templomok t 
	            WHERE  ok = 'i' 
				ORDER BY t.id  ASC LIMIT ".$limit." OFFSET ".($limit * $offset)." ;";

$result = mysql_query($query);
$tmp = array();
while ($row = mysql_fetch_assoc($result)) {
   $tmp[] = $row['id'];
}


$html = '<a title="A már meglévő kapcsolatok adatait tölti csak le." href="cron.php?q=daily" target="_blank">OSM adatok frissítése</a>';
if(count($tmp)<1)
	addMessage('Hopsz','warning');
else {
	foreach($tmp as $t) {
		$church = getChurch($t);
		//To use uptodate OSM
		//$church['osm'] = getOSMelement($church['osm']['type'],$church['osm']['id']);
		$osm = $church['osm'];

		if($osm == '') {
			$around = 60;
			$lon = $church['lng'];
			$lat = $church['lat'];
			if($lat == '' OR $lon == '') { }
			else {
			   	$json = file_get_contents("http://overpass-api.de/api/interpreter?data=%5Bout%3Ajson%5D%5Btimeout%3A2%5D%3B%0A%28%0A%20%20node%5B%22amenity%22%3D%22place_of_worship%22%5D%28around%3A".$around."%2C".$lat."%2C".$lon."%29%3B%0A%20%20way%5B%22amenity%22%3D%22place_of_worship%22%5D%28around%3A".$around."%2C".$lat."%2C".$lon."%29%3B%0A%20%20rel%5B%22amenity%22%3D%22place_of_worship%22%5D%28around%3A".$around."%2C".$lat."%2C".$lon."%29%3B%0A%29%3B%0Aout%20body%20qt%20center%201%3B");
	   			$obj = json_decode($json);
	   			$element = $obj->elements[0];
	   			if(isset($element->center->lat)) $element->lat = $element->center->lat; 
	   			if(isset($element->center->lon)) $element->lon = $element->center->lon;  
	   			$osm = json_decode(json_encode($element), true);
   			}
		}

		
		$e = array('node' => 'n', 'way' => 'w', 'relation' => 'r' );

		if(!$church['osm'] AND is_array($osm) AND $osm['tags']['url:miserend'] != 'http://miserend.hu/?templom='.$church['id']) $context = 'warning';
		elseif(!$church['osm'] AND $osm['tags']['url:miserend'] != 'http://miserend.hu/?templom='.$church['id'] ) $context = 'danger';
		else $context = 'success';

		$html	.= "
			<div class='panel panel-".$context."'>
			  <div class='panel-heading'><a data-toggle='collapse' href='#collapse".$church['id']."'><span class='glyphicon glyphicon-collapse-down'></span></a> ";
		if($context == 'warning') {
			$html .= "Talán összekapcsolhatóak! ";
			$html  .= "<a title='url:miserend' href='http://localhost:8111/load_object?new_layer=false&objects=".$e[$osm['type']].$osm['id']."&addtags="."url:miserend=http://miserend.hu/?templom=".$church['id']."' class='ajax'>+</a>";
		} elseif($context == 'danger') {
			if($lat == '' OR $lon == '') { 
				$html .= "Nincs a templomnak koordinátája!";
			} else {
				$html .= "Nincs ".$around." méteres körzetben <i>amenity=place_of_worship</i>!";
			}
		}

		$html .= "	  
			  </div>
			  <div id='collapse".$church['id']."' class='panel-collapse"; 

		if($context == 'danger') $html .= ' collapse ';
		if($context == 'success') $html .= ' collapse ';


		$html .= "'>
			    <table class='table'>";

		$html .= "
			<thead><tr>
            <th width='50%'><a href='".$base_url."?templom=".$church['id']."' target='_blank'>?templom=".$church['id']."</a> </th>
            <th><a href='http://www.openstreetmap.org/".$osm['type']."/".$osm['id']."' target='_blank'>".$e[$osm['type']].$osm['id']."</a></th>
        	</tr></thead>";

		//NAME
        if($church['nev'] != $osm['tags']['name'] AND $church['nev'] != '') {
        	$add = "<a title='name' href='http://localhost:8111/load_object?new_layer=false&objects=".$e[$osm['type']].$osm['id']."&addtags="."name=".$church['nev']."' class='ajax'>+</a>";
        } else $add = '';
		$html .= "<tr><td>".$church['nev'].$add."</td><td><span title='name'>".$osm['tags']['name']."</span></td></tr>";
		if($church['ismertnev'] != '' OR isset($osm['tags']['alt_name'])) {
			if($church['ismertnev'] != $osm['tags']['alt_name'] AND $church['ismertnev'] != '') {
	        	$add = "<a title='alt_name' href='http://localhost:8111/load_object?new_layer=false&objects=".$e[$osm['type']].$osm['id']."&addtags="."alt_name=".$church['ismertnev']."' class='ajax'>+</a>";
	        } else $add = '';
			$html .= "<tr><td>".$church['ismertnev'].$add."</td><td><span title='alt_name'>".$osm['tags']['alt_name']."</span></td></tr>";
		}
		//DENOM
		if(in_array($church['egyhazmegye'], array(17,18))) { $denomN = 'görögkatolikus'; $denom = 'greek_catholic'; } else { $denomN = 'római katolikus'; $denom = 'roman_catholic'; }
		if($denom != $osm['tags']['denomination']) {		
        	$addDenomination = "<a title='denomination' href='http://localhost:8111/load_object?new_layer=false&objects=".$e[$osm['type']].$osm['id']."&addtags="."denomination=".$denom."' class='ajax'>+</a>";
        } else $addDenomination = '';
        if(!isset($osm['tags']['religion'])) {
        	$osm['tags']['religion'] = "<i>christian</i><a href='http://localhost:8111/load_object?new_layer=false&objects=".$e[$osm['type']].$osm['id']."&addtags="."religion=christian' class='ajax'>+</a>";
        } elseif ($osm['tags']['religion'] != 'christian') {
        	$osm['tags']['religion'] = "<font color='red'>".$osm['tags']['religion']."</font><a href='http://localhost:8111/load_object?new_layer=false&objects=".$e[$osm['type']].$osm['id']."&addtags="."religion=christian' class='ajax'>+</a>";
        }	
		$html .= "<tr><td>".$denomN.$addDenomination."</td><td>";
		$html .= "<span title='religion'>".$osm['tags']['religion']."</span>, <span title='denomination'>".$osm['tags']['denomination']."</span>";
		$html .= "</td></tr>";

		//ADDRESS
		$varos = preg_replace("/^(Budapest ).*kerület$/i", "Budapest", $church['varos']);
		if($varos != $osm['tags']['addr:city'] AND $church['varos'] != '') {
        	$addCity = "<a title='addr:city' href='http://localhost:8111/load_object?new_layer=false&objects=".$e[$osm['type']].$osm['id']."&addtags="."addr:city=".$church['varos']."' class='ajax'>+</a>";
        } else $addCity = '';
		if($church['irsz'] != $osm['tags']['addr:postcode'] AND $church['irsz'] != '') {
        	$addPostcode = "<a title='addr:postcode' href='http://localhost:8111/load_object?new_layer=false&objects=".$e[$osm['type']].$osm['id']."&addtags="."addr:postcode=".$church['irsz']."' class='ajax'>+</a>";
        } else $addPostcode = '';

        if(trim($church['cim'],'.') != ( $osm['tags']['addr:street']." ".$osm['tags']['addr:housenumber'] ) AND $church['cim'] != '') {
        	$addStreet = "<a title='addr:street' href='http://localhost:8111/load_object?new_layer=false&objects=".$e[$osm['type']].$osm['id']."&addtags="."addr:street=".$church['cim']."' class='ajax'>+</a>";
        	$addHousenumber = "<a title='addr:housenumber' href='http://localhost:8111/load_object?new_layer=false&objects=".$e[$osm['type']].$osm['id']."&addtags="."addr:housenumber=".$church['cim']."' class='ajax'>+</a>";
        } else {
        	$addStreet = '';
        	$addHousenumber = '';
        }
		$html .= "<tr><td>".$church['irsz'].$addPostcode." ".$church['varos'].$addCity.", ".$church['cim'].$addStreet.$addHousenumber."</td><td>";
		$html .= "<span title='addr:postcode'>".$osm['tags']['addr:postcode']."</span> <span title='addr:city'>".$osm['tags']['addr:city']."</span>, <span title='add:street'>".$osm['tags']['addr:street']."</span> <span title='add:housenumber'>".$osm['tags']['addr:housenumber']."</span>.";
		$html .= "</td></tr>";

		foreach ($osm['tags'] as $key => $value) {
			if(!in_array($key, array('name','alt_name','religion','denomination','addr:postcode','addr:city','addr:street','addr:housenumber','amenity'))) {
				if($key != 'url:miserend' OR !$church['osm'] )
					$html.= "<tr><td></td><td>".$key.": ".$value."</td></tr>";

			}
			# code...
		}

		//print_r($church);

//href='http://localhost:8111/load_object?new_layer=false&objects=".$e[$osm['type']].$osm['id']."'
//http://localhost:8111/load_object?new_layer=false&objects=n3485191810&addtags=name=M%C3%A1ria%20Szepl%C5%91telen%20Sz%C3%ADve-templom
		$html .= "	    
			    </table>
			</div></div>";		
	}
		$html .= '
<nav>
  <ul class="pager">
    <li class="previous"><a href="'.$base_url.'?q=josm&page='.($offset - 1).'"><span aria-hidden="true">&larr;</span> Előző '.$limit.'</a></li>
    <li class="next"><a href="'.$base_url.'?q=josm&page='.($offset + 1).'">Következő '.$limit.' <span aria-hidden="true">&rarr;</span></a></li>
  </ul>
</nav>
		';
}

$vars['content'] = $html;
?>