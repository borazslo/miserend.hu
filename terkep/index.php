<?php session_start(); ?>
<html>
<head>
    <title>Miserend: térképen a templomok</title>

	<script src="library/DecimalFormat.js" language="javascript"></script>
    <script src="http://openlayers.org/api/OpenLayers.js"></script>
    <script src="http://maps.google.com/maps/api/js?v=3.2&sensor=false"></script>
	<script src="http://acuriousanimal.com/code/animatedCluster/ol/OpenLayers.js"></script>
	
<script src="http://code.jquery.com/jquery-1.10.2.js"></script>
  <script src="http://code.jquery.com/ui/1.10.4/jquery-ui.js"></script>	
	
	
	<!--<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.min.js"></script>-->
	
	<script src="library/AnimatedCluster.js"></script>
	
    <meta charset="UTF-8" />
	<meta name="description" content="Magyaroszági és külföldi magyar templomok és misézőhelyek egyetlen online digitális térképen">
	<meta name="keywords" content="katolikus, mise, szentmise, miserend, templom, misézőhely, kápolna, bazilika, térkép, online, digitális">

	<meta name="author" content="miserend.hu">

<style type="text/css">
        body {
        padding: 0px;
        margin: 0px;
        }

		a:link {color:#FF0000;}      /* unvisited link */
		a:visited {color:#FF0000;}  /* visited link */
		a:hover {color:#FF0000;}  /* mouse over link */
		a:active {color:#FF0000;}  /* selected link */
		
		.olControlLayerSwitcher {
			/* width:15em;*/
			margin-top: -18px !important;
			z-index:1200 !important;
			width: 340px !important
		} 
		
		#box {
			overflow: hidden; display:none;
			right: 0px;
			margin-top: 195px;
			width: 340px;
			position: absolute;
			background-color: rgba(255,255,255,0.9);
			z-index:1100;}

	  #boxx {
		float:right;
	  }
    </style>

<?
ini_set('error_reporting', E_ALL);

error_reporting(-1);
?>
<?php
include 'db.php';
include 'facebook.php';

$marcsak = (int) ((strtotime('2014-03-20') - time())/  ( 60 * 60 * 24 ));

//FELTÖLTÉS ÉS ELMENTÉS VAGY AMIT AKARTOK 
if(isset($_REQUEST['hajra']) AND isset($_REQUEST['user']) AND $_REQUEST['user'] == $user['id']) {
	
	
	 if($_REQUEST['checked'] == 0) {
	
	$query = "INSERT INTO terkep_geocode_suggestion (tid,tchecked, slng, slat, sdistance, spoint, uid, stime ) 
		VALUES ('".$_REQUEST['tid']."','".$_REQUEST['checked']."','".$_REQUEST['nlng']."','".$_REQUEST['nlat']."','".$_REQUEST['distance']."',1,'".$_REQUEST['user']."','".date('Y-m-d H:i:s')."') ;";
	db_query($query);	
	$query = "UPDATE terkep_geocode SET lat = '".$_REQUEST['nlat']."', lng = '".$_REQUEST['nlng']."', checked = '2' WHERE tid = ".$_REQUEST['tid']." LIMIT 1";
	db_query($query);
	echo "<!--";
	include 'terkep_gyarto.php';
	echo "-->";
	$text = "Köszönjük, ezt sikerrel elmentettük! (Kell még idő, hogy a főtérképen is megjelenjen.)";
	} else {
	
	$text = "Jelenleg csak a pirossal jelölt templomokat lehet vánszorogtatni!";
	
	}
	
	$mindenokdiv = "<div id=\"message\" class=\"message\" style='/* width: 100%; */
color: red;
/* padding-top: 80px; */
/* padding-left: 40px; */
padding-bottom: 10px;
position: absolute;
background-color: rgba(255,255,255,0.9);
margin-top: 110px;
margin-left: 30%;
margin-right: 30%;
padding: 30px;
'>\n
<strong>".$text."\n</strong></div>";
	
}



$query = "
		SELECT t.*,orszagok.nev as orszag, megye.megyenev as megye,lat,lng, terkep_geocode.* FROM templomok as t 
				LEFT JOIN orszagok ON orszagok.id = t.orszag 
				LEFT JOIN megye ON megye.id = megye 
				LEFT JOIN terkep_geocode ON terkep_geocode.tid = t.id 			
				
				LIMIT 10000";    
	$templomok = db_query($query);
	$kesz = 0;
	$gyanus = 0;
	$ures = 0;
	$folyamatban = 0;
	foreach($templomok as $templom) {
		if($templom['lat'] == 0 OR $templom['lng'] == 0) $templom['lat'] = $templom['lng'] = ""; 
		if($templom['lat'] == '' OR $templom['lng'] == '') $ures++;
		if($templom['checked'] == 0) $gyanus++;
		elseif($templom['checked'] == 1) $kesz++;
		elseif($templom['checked'] == 2) $folyamatban++;
	}

	?>

<meta property="og:image" content="http://terkep.miserend.hu/images/miserend2.JPG">
<meta property="og:description" content="Segíts meghatározni a helyét pár templomnak a maradék <?php echo ($gyanus + $ures); ?> darabból! <?php echo ($kesz + $folyamatban); ?> már meg is van. Ha időben megleszünk, lesz android alkalmazás is!">
<meta property="og:title" content="Templomok tömkelege a térképen.">
<body>

	
<div style="z-index:1001;position:absolute;height:22px;background-color:black;width:220px;padding-bottom:8px;padding-top:8px;color:white;right:0;text-align:right;padding-right:10px">

<span id="infosign">(i)</span> |
<?php 
			echo $fb_logout_html." | ";
			if($user['name']) echo "<a>".$user['name']."</a>";
			else echo $fb_login_html; ?>
			 </div>	
<!--<div style="z-index:1001;position:absolute;height:22px;background-color:black;width:220px;padding:8px;padding-right:10px;color:white;right:0"><strong><a href="http://terkep.miserend.hu/terkep_adtemplom.php">Segíts a megtalálni a hiányzókat!</a></strong></div>	-->
<div  style="z-index:1000;position:absolute;height:22px;background-color:black;width:100%;padding-top:8px;padding-bottom:8px;color:white">
	<span style="font-variant:small-caps;padding-left:45px">Templomok tömkelege a térképen - </span> 
	<? 
	echo " <font color='green' alt='Pontosan beazonosítottuk és elhelyeztük a térképen.'>Pontos: <span id='pontosszam'>".($kesz + $folyamatban )."</span></font> ";
//	echo "<font color='yellow' alt='Valaki már pontosan beazonosította a térképen.'>Folyamtban: $folyamatban</font> ";
	echo "<font color='red' alt='A gép talált neki helyet, ami vagy jó, vagy nem.'>Gyanús: <span id='gyanusszam'>$gyanus</span></font> ";
	echo "<font color='yellow' alt='Nincs is rajta a térképen még.'>Pozíció nélkül: $ures</font> ";
	
	?>	
	<div style="float:right;margin-right:240px">
		<font color='yellow'><a href='https://play.google.com/store/apps/details?id=com.frama.miserend.hu' target='_blank'>Miserend már androidra is!</a></font></div>
	</div>



	
<div style="width:100%; position:absolute; top:38px;/* Firefox */
height: -moz-calc(100% - 38px);
/* WebKit */
height: -webkit-calc(100% - 38px);
/* Opera */
height: -o-calc(100% - 38px);
/* Standard */
height: calc(100% - 38px); margin-top:38px" id="map"></div>


<div id="box">
<img id="OpenLayers_Control_MinimizeDiv_innerImage" class="olAlphaImg" src="http://openlayers.org/api/img/layer-switcher-minimize.png" style="position: relative;">

<div id="boxx">[-]</div><div style="padding:15px" id="boxinside">
</div></div>

<?php if(isset($mindenokdiv)) echo $mindenokdiv; ?>


<div id="message" class="message" style='/* width: 100%; */
/*color: red;*/
/* padding-top: 80px; */
/* padding-left: 40px; */
padding-bottom: 10px;
position: absolute;
background-color: rgba(255,255,255,0.9);
margin-top: 110px;
margin-left: 30%;
margin-right: 30%;
padding: 30px;
display:none;
'></div>

<div id="folyamatban" class="folyamatban" style='/* width: 100%; */
/*color: red;*/
/* padding-top: 80px; */
/* padding-left: 40px; */
padding-bottom: 10px;
position: absolute;
background-color: rgba(255,255,255,0.9);
margin-top: 110px;
margin-left: 30%;
margin-right: 30%;
padding: 30px;
display:none;
'>Dolgozom, dolgozom, ne búsúlj.</div>


/* üdvözlő tábla */


<div id="message" class="message udvozlet" style='/* width: 100%; */
/*color: red;*/
/* padding-top: 80px; */
/* padding-left: 40px; */
padding-bottom: 10px;
position: absolute;
background-color: rgba(255,255,255,0.9);
margin-top: 110px;
margin-left: 30%;
margin-right: 30%;
padding: 30px;;
<?
if(!isset($_SESSION['udvozlet']) OR !isset($user)) echo " display:block; ";
else  echo " display:none; ";
?>
'>
<h1>Templomok tömkelege a térképen</h1>
<h4 style="margin-top:-28px"><a href="https://play.google.com/store/apps/details?id=com.frama.miserend.hu" target="_blank">Egy androidos miserend alkalmazás</a> pontosításáért</h4>
<p>A <a href="http://miserend.hu">miserend.hu</a> rendszerében mintegy <? echo count($templomok); ?> templom ill. miséző hely található. Ezeket szeretnénk mind feltűzdelni a térképre, hogy jártunkban keltünkben megtalálhassuk a környékbeli szentmiséket.</p>
<p>Bár már <font color="green"><?php echo ($kesz + $folyamatban ); ?> a helyén van</font>, de még mintegy <font color=""><?php echo $ures; ?> templomról nem tudjuk, hogy merre van</font> és másik <font color="red"><?php echo $gyanus; ?> templomot kézzel kéne pontosítani</font> ill. az eltévedteket helyükre vinni. Ebben kérjük a te segítségedet is!</p>
<?php 
if(!isset($user)) { ?>
<center>Az alábbi linkre kattinva léphetsz be a facebook felhasználód segítségével:*<br><strong><? echo $fb_login_html; ?></strong><br><font size="-1">Nevedet csak rangsorokban tesszük esetleg közzé. Hírfolyamodra külön engedélyed nélkül nem küldünk semmit.</font></center>
</div>
<?
} else { ?>
<p>Egy-egy <font color="red">piros templomra</font> kattintva arrébb húzhatod a pontos helyére, majd a elmentheted, a sikerült.</p>
<p><strong>A <font color="">pozíció nélküli templomokat</font> egy másik oldalon tűzheted fel a térképre. <a href="/terkep_adtemplom.php">Kattints érte ide!</a> </strong></p>

<? } ?>
<p>Óhaj, sóhaj, jaj, panasz esetén: eleklaszlosj@gmail.com</p>
<?
if(isset($user)) {
 $_SESSION['udvozlet'] = 'megmutatva';
}
?>

<!--<div style="position:absolute;top:40px;">
	<img height='300' src='https://lh4.ggpht.com/NfgDvTC_3xYM9YlxhvRuhB2rhgWUGhQSyzOTkLygU2OwMAl__eucsD2Jg4GCIDObCA=h900-rw'>
	<h3>Hamarosan az <a href="https://play.google.com/store/apps/details?id=com.frama.miserend.hu">Android Play</a>-en.</h3>
	<p>Templom és mise kereső az ország bármely pontján Android alkalmazásként.</p>
</div>-->
</div>




<?php include 'terkep.php'; ?>
</body>
<?php
exit;
?>
<?php // http://www.sitepoint.com/embellishing-your-google-map-with-css3-and-jquery/
session_start();

$fields = array('uid','point','point_uc','distance','distance_uc','suggestion','suggestion_uc','marker','marker_uc','rank');
require_once 'db.php';

require_once 'facebook.php';
require_once 'check.php';

foreach($fields as $f) {
 $user[$f]="0";
 }
 //$user['name'] = 'látogató';
require_once 'rank.php';
rank_update($user['id']);

$rank = rank_get('point',$user['id']);
if($rank != '') $user = array_merge($user,$rank);

?>
<html xmlns="http://www.w3.org/1999/xhtml" style="height:100%">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <title>Templomok a térképen</title>
    <script src="http://maps.google.com/maps/api/js?sensor=false"  type="text/javascript"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.min.js"></script>
	<script src="ajax_framework.js" language="javascript"></script>
	
	<script src="library/DecimalFormat.js" language="javascript"></script>
	<script src="library/geometa.js" language="javascript"></script>
		
    <script src="gmaps.js" type="text/javascript"></script>
	<link rel="stylesheet" href="gmaps.css" />
	
	
	
	<style type="text/css">
  *, html { margin:0; padding:0 }
  div#map_canvas { width:100%; height:100%; }
  div#info { width:100%; position:absolute; overflow:hidden; text-align:center; top:0;
    left:0; }
  .lightBox {
    filter:alpha(opacity=60);
    -moz-opacity:0.6;
    -khtml-opacity: 0.6;
    opacity: 0.6;
    background-color:white;
    padding:2px;
  }
</style>
<script type="text/javascript">
  
  function doGeolocation() {
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(positionSuccess, positionError);
    } else {
      positionError(-1);
    }
  }

  function positionError(err) {
    var msg;
    switch(err.code) {
      case err.UNKNOWN_ERROR:
        msg = "Unable to find your location";
        break;
      case err.PERMISSION_DENINED:
        msg = "Permission denied in finding your location";
        break;
      case err.POSITION_UNAVAILABLE:
        msg = "Your location is currently unknown";
        break;
      case err.BREAK:
        msg = "Attempt to find location took too long";
        break;
      default:
        msg = "Location detection not supported in browser";
    }
    //document.getElementById('info').innerHTML = msg;
  }

  function positionSuccess(position) {
    // Centre the map on the new location
    var coords = position.coords || position.coordinate || position;
    var latLng = new google.maps.LatLng(coords.latitude, coords.longitude);
    map.setCenter(latLng);
    map.setZoom(16);
    /*
	var marker = new google.maps.Marker({
	    map: map,
	    position: latLng,
	    title: 'Why, there you are!'
    }); 
    document.getElementById('info').innerHTML = 'Looking for <b>' +
        coords.latitude + ', ' + coords.longitude + '</b>...';
    */
    // And reverse geocode.
	/*
    (new google.maps.Geocoder()).geocode({latLng: latLng}, function(resp) {
		  var place = "You're around here somewhere!";
		  if (resp[0]) {
			  var bits = [];
			  for (var i = 0, I = resp[0].address_components.length; i < I; ++i) {
				  var component = resp[0].address_components[i];
				  if (contains(component.types, 'political')) {
					  bits.push('<b>' + component.long_name + '</b>');
					}
				}
				if (bits.length) {
					place = bits.join(' > ');
				}
	//			marker.setTitle(resp[0].formatted_address);
			}
    		
			//document.getElementById('place').value = place;
				
	  }); */
  }

  function contains(array, item) {
	  for (var i = 0, I = array.length; i < I; ++i) {
		  if (array[i] == item) return true;
		}
		return false;
	}

</script>
	
  <!--[if IE]>
  <style>
    html #placeDetails {
      background-color: black;
    }
  </style>
  <![endif]-->
  </head>

  <body onload="load()" style="height:100%">
      
	<div id="suggestion-ac"></div>
	<div id="filter">
		<div id="filter0"><a id="filter0a" href="javascript:changeFilter('0')">I</a></div>
		<div id="filter1"><a id="filter1a" href="javascript:changeFilter('1')">I</a></div>
		<div id="filter2"><a id="filter2a" href="javascript:changeFilter('2')">I</a></div>
	</div>
	<?php echo percent_html(); ?>
	<div id='topbar'><?php 
			echo $fb_logout_html." ";
			if($user['name']) echo "<a>".$user['name']."</a>";
			else echo $fb_login_html;
			echo "<span id=\"main-rank\">".rank_html($user); ?>
			 </span></div>
			 </div>
	<div id="ResponseDiv"></div>
	
	<div class='map'>
		<div id="welcome" class="welcome"><?php echo welcome_html($fb_login_url,$user); ?></div>
		<div id='map_canvas' ></div>
		<div id="RightBar">
			<div id="RightBarIcons">
				<div id="RightBarIcon1">1</div>
				<div id="RightBarIcon2">2</div>
				</div>
			<div id="RightBarContent2"></div>
			<div id="RightBarContent1">
				<div id='Details'>
				<h1></h1>
				<p></p>
				</div>
				<div id="suggestion">
				<div id="insert_response" style="visibility:hidden"></div>
				<div id="geocode-input">
				<form id="cords" action="javascript:insert()" method="post">
					<p id="geocode-text"></p>
					<input name="lat" type="hidden" id="slat" value=""/>
					<input name="lng" type="hidden" id="slng" value=""/>
					<input name="tid" type="hidden" id="tid" value=""/>
					<input name="tchecked" type="hidden" id="tchecked" value=""/>
					<input name="point" type="hidden" id="spoint" value=""/>
					<input name="distance" type="hidden" id="sdistance" value=""/>
					<input name="uid" type="hidden" id="uid" value="<?php echo $user['id']; ?>"/>
					<?php //if($user['id']) echo'<input type="submit" name="Submit" value="Beküldöm jóváhagyásra!"/>';
					if($user['id']) echo'<a href="javascript:insert()">Beküldöm jóváhagyásra!</a>';
							else echo $fb_login_html; ?>
				</form></div>
				</div>
			</div>
		</div>
  </div>
	
  </body>
</html>
<?php ?>