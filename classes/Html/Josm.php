<?php

namespace Html;

class Josm extends Html {

    public function __construct($path) {

        if (isset($_REQUEST['update'])) {
            updateOverpass();
            return;
        }

        $this->setTitle('JOSM összeköttetés');
        $this->template = 'layout_simpliest.twig';

        addMessage('Üzemen kívül. Elnézést.','danger');
        return;
        
        if (isset($_REQUEST['nosuccess']))
            $nosuccess = "&nosuccess";

        $churches = searchChurches(array());

        if (isset($_REQUEST['page']) AND is_numeric($_REQUEST['page']))
            $offset = $_REQUEST['page'];
        else
            $offset = 0;
        if (isset($_REQUEST['limit']) AND is_numeric($_REQUEST['limit']))
            $limit = $_REQUEST['limit'];
        else
            $limit = 30;

        $query1 = "SELECT t.id FROM templomok t 
	            LEFT JOIN osm ON osm.tid = t.id 
	            WHERE osm.type IS NULL and ok = 'i' 
				ORDER BY t.id LIMIT " . $limit . " OFFSET " . ($limit * $offset) . " ;";
        $query = "SELECT t.id FROM templomok t 
	            WHERE  ok = 'i' 
				ORDER BY t.id  ASC LIMIT " . $limit . " OFFSET " . ($limit * $offset) . " ;";
        if ($nosuccess)
            $query = $query1;

        $result = mysql_query($query);
        $tmp = array();
        while ($row = mysql_fetch_assoc($result)) {
            $tmp[] = $row['id'];
        }


        $html = '';

        if ($nosuccess)
            $html .= ' [<a href="?q=josm&page=' . $offset . '" >mindet</a>] ';
        else
            $html .= ' [<a href="?q=josm&page=' . $offset . '&nosuccess" >csak a problémásakat</a>] ';


        $result = mysql_query("select count(*) from osm;");
        $statOSM = mysql_fetch_row($result);
        $result = mysql_query("select count(*) from templomok left join terkep_geocode ON templomok.id = terkep_geocode.tid where ok = 'i';");
        $statMR = mysql_fetch_row($result);

        $html .= " Stat: <span title='OSM adatbázisba bekötött templomok'>" . $statOSM[0] . "</span>/<span title='Összes templom a miserend.hu-n.'>" . $statMR[0] . "</span>";


        if (count($tmp) < 1)
            addMessage('Hopsz', 'warning');
        else {
            foreach ($tmp as $t) {
                $church = \Eloquent\Church::find($t);
                $church->osm();
                $church = $church->toArray();
                //To use uptodate OSM
                //$church['osm'] = getOSMelement($church['osm']['type'],$church['osm']['id']);
                $osm = (array) $church['osm'];

                if ($osm == '') {
                    $around = 60;
                    $around2 = 3000;
                    $lon = $church['lng'];
                    $lat = $church['lat'];
                    if ($lat == '' OR $lon == '') {
                        
                    } else {
                        $query = '[out:json][timeout:6]; ( node["amenity"="place_of_worship"](around:' . $around2 . ',' . $lat . ',' . $lon . '); way["amenity"="place_of_worship"](around:' . $around2 . ',' . $lat . ',' . $lon . '); rel["amenity"="place_of_worship"](around:' . $around2 . ',' . $lat . ',' . $lon . '); ); out body center qt 6;';
                        $obj = getOverpass($query);
                        if (count((array) $obj->elements) > 5) {
                            $query = '[out:json][timeout:6]; ( node["amenity"="place_of_worship"](around:' . $around . ',' . $lat . ',' . $lon . '); way["amenity"="place_of_worship"](around:' . $around . ',' . $lat . ',' . $lon . '); rel["amenity"="place_of_worship"](around:' . $around . ',' . $lat . ',' . $lon . '); ); out body center qt;';
                            $obj = getOverpass($query);
                        }
                        $k = false;
                        $dmisn = $around2 + 1;
                        foreach ($obj->elements as $key => $element) {
                            if (isset($element->center->lat))
                                $element->lat = $element->center->lat;
                            if (isset($element->center->lon))
                                $element->lon = $element->center->lon;
                            $d = Distance($church, (array) $element);

                            if ((int) $d < (int) $dmisn) {
                                $k = $key;
                                $dmisn = $d;
                            }
                            $obj->elements[$key]->distance = $d;
                        }

                        $osmelements = json_decode(json_encode($obj->elements), true);

                        if ($dmisn <= 60)
                            $osm = json_decode(json_encode($obj->elements[$k]), true);
                    }
                }


                $e = array('node' => 'n', 'way' => 'w', 'relation' => 'r');

                if (!$church['osm'] AND is_array($osm) AND $osm['tags']['url:miserend'] != 'http://miserend.hu/?templom=' . $church['id'])
                    $context = 'warning';
                elseif (!$church['osm'] AND $osm['tags']['url:miserend'] != 'http://miserend.hu/?templom=' . $church['id'])
                    $context = 'danger';
                else
                    $context = 'success';

                $html .= "
			<div class='panel panel-" . $context . "'>
			  <div class='panel-heading'><a data-toggle='collapse' href='#collapse" . $church['id'] . "'><span class='glyphicon glyphicon-collapse-down'></span></a> ";
                if ($context == 'warning') {
                    $html .= "Talán összekapcsolhatóak! ";
                    $html .= "<a title='url:miserend' href='http://localhost:8111/load_object?new_layer=false&objects=" . $e[$osm['type']] . $osm['id'] . "&addtags=" . "url:miserend=http://miserend.hu/?templom=" . $church['id'] . "' class='ajax'>+</a>";
                } elseif ($context == 'danger') {
                    if ($lat == '' OR $lon == '') {
                        $html .= "Nincs a templomnak koordinátája!";
                    } else {
                        $html .= "Nincs " . $around . " méteres körzetben <i>amenity=place_of_worship</i>!";
                    }
                } else {
                    $html .= "<a href='http://miserend.hu/?templom=" . $church['id'] . "' target='_blank'>?templom=" . $church['id'] . "</a> = <a href='http://www.openstreetmap.org/" . $osm['type'] . "/" . $osm['id'] . "' target='_blank'>" . $e[$osm['type']] . $osm['id'] . "</a>";
                }

                if (count($osmelements) > 1) {
                    $html .= ' (Továbbiak: ';
                    $osmelements = array_slice($osmelements, 0, 4);
                    foreach ($osmelements as $key => $value) {
                        if ($osm != $value)
                            $html .= " <a href='http://localhost:8111/load_object?new_layer=false&objects=" . $e[$value['type']] . $value['id'] . "' class='ajax'>" . (int) ( $value['distance'] ) . "m</a> ";
                        # code...
                    }
                    $html .= ')';
                }

                $html .= "	  
			  </div>
			  <div id='collapse" . $church['id'] . "' class='panel-collapse";

                if ($context == 'danger')
                    $html .= ' collapse ';
                if ($context == 'success')
                    $html .= ' collapse ';


                $html .= "'>
			    <table class='table'>";

                $d = '0.004';
                $html .= "
			<thead><tr>
            <th width='50%' colspan='2'>
            	<a href='http://miserend.hu/?templom=" . $church['id'] . "' target='_blank'>?templom=" . $church['id'] . "</a> 
            	 [Mutasd: <a href='http://www.openstreetmap.org/?mlat=" . $church['lat'] . "&mlon=" . $church['lng'] . "#map=18/" . $church['lat'] . "/" . $church['lng'] . "' target='_blank'>OSM</a> 
            	 <a href='http://maps.google.com/maps?z=20&t=h&q=loc:" . $church['lat'] . "+" . $church['lng'] . "' target='_blank'>GMaps</a>]
            	 </th>
            <th>";
                if ($context != 'danger') {
                    $html .= "<a href='http://www.openstreetmap.org/" . $osm['type'] . "/" . $osm['id'] . "' target='_blank'>" . $e[$osm['type']] . $osm['id'] . "</a> 
            	[<a title='Megnyitás JOSM-ben' href='http://localhost:8111/load_object?new_layer=false&objects=" . $e[$osm['type']] . $osm['id'] . "' target='_blank' class='ajax'>edit</a>] 
            	[<a title='Megnyitás JOSM-ben és a környék betöltése' href='http://localhost:8111/load_and_zoom?new_layer=false&left=" . ($osm['lon'] - $d) . "&right=" . ($osm['lon'] + $d) . "&top=" . ($osm['lat'] + $d) . "&bottom=" . ($osm['lat'] - $d) . "&objects=" . $e[$osm['type']] . $osm['id'] . "' target='_blank' class='ajax'>load</a>] ";
                } else {
                    $html .= "[<a title='Megnyitás JOSM-ben és a környék betöltése' href='http://localhost:8111/load_and_zoom?new_layer=false&left=" . ($church['lng'] - $d) . "&right=" . ($church['lng'] + $d) . "&top=" . ($church['lat'] + $d) . "&bottom=" . ($church['lat'] - $d) . "' target='_blank' class='ajax'>load</a>] ";
                }
                $html.="	</th>
        	</tr></thead>";

                //NAME
                if ($church['kepek']) {
                    if ($church['kepek'][0]['height'] > 0 AND $church['kepek'][0]['width'] / $church['kepek'][0]['height'] < 1) {
                        $size = "height='190px'";
                    } else
                        $size = "width='190px'";

                    $image = '';
                    foreach ($church['kepek'] as $i => $kep) {
                        $image .= "<span class='colorbox'>";
                        if ($i < 1)
                            $image .= "<img title='Kattintásra sorra vehetők a képek, ha vannak még.' src='http://miserend.hu/" . $church['kepek'][$i]['url'] . "' " . $size . ">";
                        $image .= "</span>";
                        $image .= "</span>";
                        $image .= "<div tid='" . $church['kepek'][$i]['id'] . "' style='display:none;text-align:center'>
				                    <img src=\"http://miserend.hu/" . $church['kepek'][$i]['url'] . "\"  align=\"center\" style=\"max-height:90%;display:block;margin-left:auto;margin-right:auto\">
				                    <div style=\"background-color:rgba(255,255,255,0.3);padding:10px;\" class=\"felsomenulink\">" . $church['nev'] . " (" . $church['varos'] . ")</div>
								</div>";
                    }
                } else
                    $image = '';

                $html .= "<tr><td rowspan='6' width='10%'>" . $image . "</td>";

                if ($church['nev'] != $osm['tags']['name'] AND $church['nev'] != '') {
                    $add = "<a title='name' href='http://localhost:8111/load_object?new_layer=false&objects=" . $e[$osm['type']] . $osm['id'] . "&addtags=" . "name=" . $church['nev'] . "' class='ajax'>+</a>";
                } else
                    $add = '';
                $html .= "<td>" . $church['nev'] . $add . "</td><td>";
                if ($context != 'danger')
                    $html .= "<span title='name'>" . $osm['tags']['name'] . "</span>";
                elseif (isset($osmelements[0]))
                    $html .= osm2txt($osmelements[0]);
                $html .= "</td></tr>";
                //if($church['ismertnev'] != '' OR isset($osm['tags']['alt_name'])) {
                if ($church['ismertnev'] != $osm['tags']['alt_name'] AND $church['ismertnev'] != '') {
                    $add = "<a title='alt_name' href='http://localhost:8111/load_object?new_layer=false&objects=" . $e[$osm['type']] . $osm['id'] . "&addtags=" . "alt_name=" . $church['ismertnev'] . "' class='ajax'>+</a>";
                } else
                    $add = '';
                $html .= "<tr><td>" . $church['ismertnev'] . $add . "</td><td>";
                if ($context != 'danger')
                    $html .= "<span title='alt_name'>" . $osm['tags']['alt_name'] . "</span>";
                elseif (isset($osmelements[1]))
                    $html .= osm2txt($osmelements[1]);
                $html .= "</td></tr>";
                //}
                //DENOM
                if (in_array($church['egyhazmegye'], array(17, 18))) {
                    $denomN = 'görögkatolikus';
                    $denom = 'greek_catholic';
                } else {
                    $denomN = 'római katolikus';
                    $denom = 'roman_catholic';
                }
                if ($denom != $osm['tags']['denomination']) {
                    $addDenomination = "<a title='denomination' href='http://localhost:8111/load_object?new_layer=false&objects=" . $e[$osm['type']] . $osm['id'] . "&addtags=" . "denomination=" . $denom . "' class='ajax'>+</a>";
                } else
                    $addDenomination = '';
                if (!isset($osm['tags']['religion'])) {
                    $osm['tags']['religion'] = "<font color='green'><i>christian</i><a href='http://localhost:8111/load_object?new_layer=false&objects=" . $e[$osm['type']] . $osm['id'] . "&addtags=" . "religion=christian' class='ajax'>+</a></font>";
                } elseif ($osm['tags']['religion'] != 'christian') {
                    $osm['tags']['religion'] = "<font color='red'>" . $osm['tags']['religion'] . "</font><a href='http://localhost:8111/load_object?new_layer=false&objects=" . $e[$osm['type']] . $osm['id'] . "&addtags=" . "religion=christian' class='ajax'>+</a>";
                }
                $html .= "<tr><td>";
                $html.= $denomN . $addDenomination;
                $html .= "</td><td>";
                if ($context != 'danger')
                    $html .= "<span title='religion'>" . $osm['tags']['religion'] . "</span>, <span title='denomination'>" . $osm['tags']['denomination'] . "</span>";
                elseif (isset($osmelements[2]))
                    $html .= osm2txt($osmelements[2]);
                $html .= "</td></tr>";

                //ADDRESS
                $varos = preg_replace("/^(Budapest ).*kerület$/i", "Budapest", $church['varos']);
                if ($varos != $osm['tags']['addr:city'] AND $church['varos'] != '') {
                    $addCity = "<a title='addr:city' href='http://localhost:8111/load_object?new_layer=false&objects=" . $e[$osm['type']] . $osm['id'] . "&addtags=" . "addr:city=" . $church['varos'] . "' class='ajax'>+</a>";
                } else
                    $addCity = '';
                if ($church['irsz'] != $osm['tags']['addr:postcode'] AND $church['irsz'] != '') {
                    $addPostcode = "<a title='addr:postcode' href='http://localhost:8111/load_object?new_layer=false&objects=" . $e[$osm['type']] . $osm['id'] . "&addtags=" . "addr:postcode=" . $church['irsz'] . "' class='ajax'>+</a>";
                } else
                    $addPostcode = '';

                if (trim($church['cim'], '.') != ( $osm['tags']['addr:street'] . " " . $osm['tags']['addr:housenumber'] ) AND $church['cim'] != '') {
                    $addStreet = "<a title='addr:street' href='http://localhost:8111/load_object?new_layer=false&objects=" . $e[$osm['type']] . $osm['id'] . "&addtags=" . "addr:street=" . $church['cim'] . "' class='ajax'>+</a>";
                    $addHousenumber = "<a title='addr:housenumber' href='http://localhost:8111/load_object?new_layer=false&objects=" . $e[$osm['type']] . $osm['id'] . "&addtags=" . "addr:housenumber=" . $church['cim'] . "' class='ajax'>+</a>";
                } else {
                    $addStreet = '';
                    $addHousenumber = '';
                }
                $html .= "<tr><td>" . $church['irsz'] . $addPostcode . " " . $church['varos'] . $addCity . ", " . $church['cim'] . $addStreet . $addHousenumber . "</td><td>";
                if ($context != 'danger')
                    $html .= "<span title='addr:postcode'>" . $osm['tags']['addr:postcode'] . "</span> <span title='addr:city'>" . $osm['tags']['addr:city'] . "</span>, <span title='add:street'>" . $osm['tags']['addr:street'] . "</span> <span title='add:housenumber'>" . $osm['tags']['addr:housenumber'] . "</span>.";
                elseif (isset($osmelements[3]))
                    $html .= osm2txt($osmelements[3]);
                $html .= "</td></tr>";

                //building
                $html .= "<tr><td><span title='building'>";
                if (preg_match("/templom$/i", $church['nev']))
                    $building = "templom";
                elseif (preg_match("/kápolna$/i", $church['nev']))
                    $building = "kápolna";
                elseif (preg_match("/bazilika$/i", $church['nev']))
                    $building = "bazilika";
                elseif (preg_match("/székesegyház$/i", $church['nev']))
                    $building = "székesegyház";
                else
                    $building = "??";
                $html .= $building . "</span></td><td>";
                $buildings = array('templom' => 'church', 'kápolna' => 'chapel', 'bazilika' => 'basilica', 'székesegyház' => 'cathedral', '??' => 'yes');

                if ($context != 'danger') {
                    $html .= "<span title='building'>";
                    if (!isset($osm['tags']['building']))
                        $html .= "<font color='green'><i>" . $buildings[$building] . "</i><a href='http://localhost:8111/load_object?new_layer=false&objects=" . $e[$osm['type']] . $osm['id'] . "&addtags=" . "building=" . $buildings[$building] . "' class='ajax'>+</a></font>";
                    elseif ($osm['tags']['building'] != $buildings[$building])
                        $html .= "<font color='red'><i>" . $osm['tags']['building'] . "</i><a href='http://localhost:8111/load_object?new_layer=false&objects=" . $e[$osm['type']] . $osm['id'] . "&addtags=" . "building=" . $buildings[$building] . "' class='ajax'>+</a></font>";
                    else
                        $html .= $osm['tags']['building'];
                    $html .= "</span>";
                }
                $html .= "</td></tr>";



                unset($go);
                foreach ($osm['tags'] as $key => $value) {
                    if (!in_array($key, array('name', 'alt_name', 'religion', 'denomination', 'addr:postcode', 'addr:city', 'addr:street', 'addr:housenumber', 'amenity', 'building'))) {
                        if ($key != 'url:miserend' OR ! $church['osm']) {
                            $html.= "<tr>";
                            if ($go)
                                $html .= '<td></td>';
                            else
                                $go = true;
                            $html .= "<td></td><td>" . $key . ": " . $value . "</td></tr>";
                        }
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

            if ($limit != 30)
                $limiturl = '&limit=' . $limit;
            $html .= '<nav><ul class="pager">';
            if ($offset > 1)
                $html .= '<li class="previous"><a href="/josm?page=' . ($offset - 1) . $nosuccess . $limiturl . '"><span aria-hidden="true">&larr;</span> Előző ' . $limit . '</a></li>';
            $html .= '<li class="next"><a href="/josm?page=' . ($offset + 1) . $nosuccess . $limiturl . '">Következő ' . $limit . ' <span aria-hidden="true">&rarr;</span></a></li>
  </ul>
</nav>
		';
        }

        $html .= ' [<a title="Naagyon sokáig csinálja. De naponta átmegy magától is, szóval nó para." href="/josm?update" target="_blank">OSM adtok frissítése</a>]';


        if ($image !== '') {
            $html .= <<< EOT
		<script>
		$("span.colorbox").each(function() {
                    $(this).colorbox({
                        html: $(this).next("div").html(),
                        rel: "group_random",
                        transition:"fade",
                        maxHeight:"98%"
                    }                   
                   );
                });
		</script>


EOT;
        }
        $html .= "<br/>Használat pl: ?q=josm&page=7&nosuccess&limit=2";
        $vars['content'] = $html;
        foreach ($vars as $key => $value) {
            $this->$key = $value;
        }
    }

    function osm2txt($osm) {
        $osm = (array) $osm;

        $return = '';
        $e = array('node' => 'n', 'way' => 'w', 'relation' => 'r');
        $return .= (int) $osm['distance'] . "m: ";
        $return .= " <a title='Megnyitás JOSM-ben' href='http://localhost:8111/load_object?new_layer=false&objects=" . $e[$osm['type']] . $osm['id'] . "' target='_blank' class='ajax'>";
        if (isset($osm['tags']['name']))
            $return .= $osm['tags']['name'] . " ";
        else
            $return .= $e[$osm['type']] . $osm['id'];
        $return .= "</a>";
        if (isset($osm['tags']['alt_name']))
            $return .= "<span title='alt_name'>" . $osm['tags']['alt_name'] . "</span> ";
        if (isset($osm['tags']['denomination']))
            $return .= "<span title='denomination'>" . $osm['tags']['denomination'] . "</span> ";
        if (isset($osm['tags']['building']))
            $return .= "<span title='building'>" . $osm['tags']['building'] . "</span> ";

        return $return;
    }

}

?>