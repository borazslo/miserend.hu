<?
echo"fejlesztés alatt...";
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