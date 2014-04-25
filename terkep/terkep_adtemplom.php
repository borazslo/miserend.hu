<html>
<head>
    <title>Miserend: térképen a templomok</title>
    <meta charset="UTF-8" />
	
<style type="text/css">
        body {
        padding: 0px;
        margin: 0px;
        }

		a:link {color:#FF0000;}      /* unvisited link */
		a:visited {color:#FF0000;}  /* visited link */
		a:hover {color:#FF0000;}  /* mouse over link */
		a:active {color:#FF0000;}  /* selected link */
    </style>
</head>
<body>
<? include 'facebook.php';	?>
<div style="z-index:1001;position:absolute;height:22px;background-color:black;width:220px;padding:8px;padding-right:10px;color:white;right:0;text-align:right">
	<?php 
			echo $fb_logout_html." | ";
			if($user['name']) echo "<a>".$user['name']."</a>";
			else echo $fb_login_html; ?>
			 </div>	
			 
<div  style="z-index:1000;position:absolute;height:22px;background-color:black;width:100%;padding-top:8px;padding-bottom:8px;color:white">
	<span style="font-variant:small-caps;padding-left:45px">Templomok térképre tűzdelése - </span><?php
/* RANK */
$file = fopen("stats_adtemplom.txt", "r");
while(!feof($file)){
    $line = fgets($file);
	$log = json_decode($line);
		if(isset($log->user)) {
			if(isset($rank[$log->user])) $rank[$log->user]++;
			else $rank[$log->user] = 1;
		}
    # do same stuff with the $line
}
fclose($file);

arsort($rank);

foreach($rank as $id=>$point) {
	$c++;
	if($user['id'] == $id) {
		echo "<font color='yellow'>";
		if($last - $point < 2) echo "Csak ".($last - $point)." pont és a ".($c-1).". helyen leszel!";
		elseif($last - $point < 10 AND $c < 5) echo "Kicst elhúztak, de így is a ".($c).". helyen állsz!";
		elseif(count($rank) - $c < 2  ) echo "Előre, csak előre, lássuk mi lesz belőle!";
		else echo $rank[$user['id']]." ponttal a ".$c.". helyen állsz.";
		
		echo "</font>";
		
	}
	$last = $point;
}
?>	</div>

			 			 
<?php

include_once('db.php');

if(isset($_REQUEST['tid']) AND isset($_REQUEST['lat']) AND isset($_REQUEST['lng']) AND isset($_REQUEST['checked'])) {

	if($_REQUEST['lat'] != '' AND $_REQUEST['lng'] != '') {

	$query = "UPDATE terkep_geocode SET lat = '".$_REQUEST['lat']."', lng = '".$_REQUEST['lng']."', checked = '".$_REQUEST['checked']."' WHERE tid = ".$_REQUEST['tid']." LIMIT 1";
	db_query($query);
	$insert = "INSERT into terkep_geocode (lat,lng,checked,tid) VALUES ('".$_REQUEST['lat']."','".$_REQUEST['lng']."','".$_REQUEST['checked']."','".$_REQUEST['tid']."')"; 
	db_query($insert);
	echo "<div style='width:100%;color:red;padding-top:80px;padding-left:40px;padding-bottom:10px'><strong>Köszönjük, ezt sikerrel elmentettük! (Kell még idő, hogy a főtérképen is megjelenjen.)\n</strong></div>";
	//echo //'<a href="terkep_gyarto.php">gyárts</a>';
	
	$file = 'stats_adtemplom.txt';
	// Open the file to get existing content
	$current = file_get_contents($file);
	// Append a new person to the file
	//$current .= $input['tid'].";".$input['pid'].";'".$input['text']."'\n";
	$current .= json_encode(array('timestamp'=>date('Y-m-d H:i:s'),'query'=>$insert,'user'=>$user['id']))."\n";
	// Write the contents back to the file
	//echo $current;
	file_put_contents($file, $current);
	
	} else {
		echo "<div style='width:100%;color:red;padding-top:80px;padding-left:40px;padding-bottom:10px'><strong>Jaaaj, jaaj! Koordináta nélkül, mit érek én?\n</strong></div>";	
	}
	
}


  // Set default timezone
  date_default_timezone_set('UTC +1');

   // TODO: nem kell minden
 $query = "
		SELECT t.*,orszagok.nev as orszag, megye.megyenev as megye,lat,lng, terkep_geocode.* FROM templomok as t 
				LEFT JOIN orszagok ON orszagok.id = t.orszag 
				LEFT JOIN megye ON megye.id = megye 
				LEFT JOIN terkep_geocode ON terkep_geocode.tid = t.id 			
				WHERE ( lng = 0 OR lat = 0 OR lng IS NULL or lat IS NULL )
					AND t.ok = 'i' 
				ORDER BY t.orszag, t.varos
				LIMIT 10000";    
	$templomok = db_query($query);
?>
<script type="text/javascript">
    function loadURL(u) {
       //document.getElementById("web-panel").innerHTML = '<iframe src="http://miserend.hu/?templom=' + u + '" width="775" height="100%" border="0"></iframe>';
		document.getElementById("tovabb").innerHTML = '<a href="http://miserend.hu/?templom=' + u + '" target="_blank">miserend.hu/?templom=' + u + '</a>';
		
	}
  </script>

<script type="text/javascript"
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA_vWOnF5UqVnOkGVitY86hIDY2Rd4VmDY&sensor=false">
</script>

  
<script type="text/javascript">
	var templomcimek1 = new Array();
	var templomcimek2 = new Array();
	<?
	foreach($templomok as $templom) {
		//print_r($templom);
		$cim1 = $templom['orszag'].", ".$templom['varos'].", ".$templom['cim'];
		$cim2 = $templom['orszag'].", ".$templom['varos'];
		echo " templomcimek1[".$templom['id']."] = \"".$cim1."\"; \n";
		echo " templomcimek2[".$templom['id']."] = \"".$cim2."\"; \n";
		
	}
	?>
	//console.log(templomcimek);
    //<![CDATA[       
     function getSelectedText(elementId) {
    var elt = document.getElementById(elementId);

    if (elt.selectedIndex == -1)
        return null;

    return elt.options[elt.selectedIndex].text;
}
  
    var mgp;
    function LoadMapMgp() {
		 geocoder = new google.maps.Geocoder();
	
        var mapcenter = new google.maps.LatLng(47.38455936116259 , 19.766882607812477);
        var mapOptions = {
            zoom: 6,
            center: mapcenter,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        };                           
        mapOptions.mapTypeControl = true;
        mapOptions.zoomControl = true;
        mapOptions.zoomControlOptions = {style: google.maps.ZoomControlStyle.SMALL};
        mapOptions.overviewMapControl = true;
        mapOptions.scaleControl = false;
        mgp = new google.maps.Map(document.getElementById('map-id5fdfbf4c-dfea-4f7d-b768-124b5c914114'), mapOptions);  

	var pointGDynamicMarker19464624 = new google.maps.LatLng(45.9904428,18.0255496);

var Marker = new google.maps.Marker({position: pointGDynamicMarker19464624,map: mgp,draggable: true});



var input = document.createElement('input');
input.type = 'hidden';
input.id = 'hiddenGDynamicMarker19464624';input.name = 'hiddenGDynamicMarker19464624';
document.forms[0].appendChild(input);
google.maps.event.addListener(Marker, 'dragend', function() { 
	document.getElementById('hiddenGDynamicMarker19464624').value = Marker.getPosition().lat() + ';' + Marker.getPosition().lng() ;
	document.getElementById("lat").value = Marker.getPosition().lat();
	document.getElementById("lng").value = Marker.getPosition().lng();	
	});


        

        google.maps.event.addListener(mgp, 'center_changed', function() {
            //document.getElementById('mapLat').value = mgp.getCenter().lat();
            //document.getElementById('mapLng').value = mgp.getCenter().lng();
        });
        google.maps.event.addListener(mgp, 'zoom_changed', function() {
            //document.getElementById('zoomlevel').value = mgp.getZoom();            
        });
    
    	google.maps.event.addDomListener(document.getElementById('tid'),'change', function() {
		
			var address1 = templomcimek1[document.getElementById('tid').value]; //getSelectedText('tid');
			var address2 = templomcimek2[document.getElementById('tid').value]; //getSelectedText('tid');
		
 
  geocoder.geocode( { 'address': address1}, function(results, status) {
  console.log(address1);
    if (status == google.maps.GeocoderStatus.OK) {
      mgp.setCenter(results[0].geometry.location);
	  
	  Marker.setPosition(results[0].geometry.location);
      mgp.setZoom(15);
    } else {
      geocoder.geocode( { 'address': address2}, function(results, status) {
	  console.log(address2);
    if (status == google.maps.GeocoderStatus.OK) {
      mgp.setCenter(results[0].geometry.location);
	  
	  Marker.setPosition(results[0].geometry.location);
      mgp.setZoom(15);
    } else {
      alert('Ötletünk sincs, hogy hol lehet ez a hely! :( ' + status);
    }
  });
    }
  });
			
		});
	
	
	
	
	}

    google.maps.event.addDomListener(window, 'load', LoadMapMgp);
	
	
    //]]>

	
	
</script>



<div style="margin-top:38px;right:0px;float:right; width:100%; /* Firefox */
height: -moz-calc(100% - 38px);
/* WebKit */
height: -webkit-calc(100% - 38px);
/* Opera */
height: -o-calc(100% - 38px);
/* Standard */
height: calc(100% - 38px);">
    <div style="width:100%;height:100%;">
        <div  id="map-id5fdfbf4c-dfea-4f7d-b768-124b5c914114" style="width:100%;height:100%;"></div>
    </div>
</div>


<div id="box" style="overflow: hidden;
right: 5px;
margin-top: 68px;
width: 450px;
position: absolute;
background-color: rgba(255,255,255,0.9);"><div style="padding:15px">
<? if(!isset($user)) {
	echo '<br><span style="color:red">Kérlek előbb lépj be a Facebook felhasználóddal!<br/>
		'.$fb_login_html."</span>"; } ?>
<p><strong>Már csak <?php echo count($templomok); ?> templomot kellene feltűzni a térképre.</strong></p>
<p>A keresést megkönnyítheti a Street View. Vagy lehet képeket keresni a környékről. Vagy más honlapokon segítséget találni.</p>
<?php if(!isset($user)) {	echo "</div></body>";	exit;} ?>
  
<form action="terkep_adtemplom.php" method="post">
	<select name="tid" id="tid" onchange="loadURL(this.value);" style="width:420px">
		<option value="">Válassz templomot...</option>
		<? 
			foreach($templomok as $templom) {
				if($templom['ismertnev'] == '') $templom['ismertnev'] = $templom['varos'];
				echo "<option value='".$templom['id']."' ";
				//echo">".$templom['nev']." (".$templom['ismertnev'].")</option>";
				echo">(".$templom['orszag'].") ".$templom['ismertnev']." - ".$templom['nev']."</option>";
			}
		?>
		</select><br/>
	Lat (kb. 46...): <input type="text" id="lat" name="lat" value=""><br/>
	Lng (kb. 16...): <input type="text" id="lng" name="lng" value=""><br/>
	<input type="hidden" name="userid" value="<?php echo $user['id']; ?>">
	<input type="hidden" name="checked" value="2"><br/>
	<input type="submit" name="hajrá">
</form>
<div id="tovabb"></div>

</div>
</div>


<div id="web-panel" style="padding:40px"></div>
