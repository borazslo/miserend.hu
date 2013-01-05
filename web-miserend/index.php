<html>
	<header>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, target-densitydpi=160, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no"> 
		<meta name="HandheldFriendly" content="True">
		<meta name="MobileOptimized" content="320">
		<meta name="format-detection" content="telephone=no, email=no">
		<meta name="apple-mobile-web-app-capable" content="yes">
		<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
		<meta http-equiv="cleartype" content="on">

	
		<meta name="description" content="Katolikus templomok miserendje Magyarországon és másutt.">
		<meta property="og:url" content="http://miserend.hu/" /> 
		<meta property="og:title" content="Miserend - VPP" /> 
		<meta property="og:description" content="Katolikus templomok miserendje Magyarországon és másutt." />
		<!--<meta property="og:image" content="http://prayexamen.com/img/og_image.jpg" />-->

		<title>Miserend - VPP</title>
		
		
	
	<link rel="stylesheet" href="http://code.jquery.com/mobile/1.1.0/jquery.mobile-1.1.0.min.css" />
		
		<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.min.js"></script>
	
		<script src="http://code.jquery.com/jquery-1.7.1.min.js"></script>
		<script src="http://code.jquery.com/mobile/1.1.0/jquery.mobile-1.1.0.min.js"></script>
		
		<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=true"></script>		
		
		<script type="text/javascript" src="jquery.ui.map.js"></script>
		<script type="text/javascript" src="jquery.ui.map.extensions.js"></script>
		<script type="text/javascript" src="jquery.ui.map.overlays.js"></script>
		<script type="text/javascript" src="markerclusterer.js"></script>
		
		<script src="miserend.js"></script>
		<script src="getPosition.js"></script>
	</header>
	
	<body onload="load();">
		<div data-role="page" id="search">

			<div data-role="header">
			<h1>Magyar miserend - VPP</h1>
			<!--  <a id="map" href="#map" title="Térkép"><span>Térkép</span></a>-->
			<a id="help" href="#help" title="Segítség"  data-role="button" data-icon="info" style="float:right" data-iconpos="notext"><span >Segítség</span></a>
			<!--   history.back();<a id="help" href="#help" title="Segítség"  data-role="button" data-icon="alert" style="float:right" data-iconpos="notext"><span >Figyelmeztetés</span></a>-->
			<a id="map" href="#map" title="Térkép"  data-role="button" style="float:right"><span >Térkép</span></a>
		
		</div><!-- /header -->

			<div data-role="content">	
		
    	<div id="search">
			<form id="search" action="javascript:search()" method="post"> 
			<!-- <form id="search" action="index.php" method="post"> -->	
					<label for="search-text" class="ui-hidden-accessible">Text Input:</label>
					<input id="search-text" width="100%" name="search" placeholder="diák gitáros német Budapest 300km vasárnap 17:00" type="search" <? if(array_key_exists('search',$_REQUEST)) echo "value=\"".$_REQUEST['search']."\" "; ?>>
				
				<div data-role="fieldcontain" style="display:none;">
					<label for="lng">Longitude:</label>
					<input id="lng" name="lng" type="hidden" value="<?php if(array_key_exists('lng',$_REQUEST)) echo $_REQUEST['lng']; ?>">
				</div>
				<div data-role="fieldcontain" style="display:none;">
					<label for="lat">Latitude:</label>
					<input id="lat" name="lat" type="hidden" value="<?php if(array_key_exists('lat',$_REQUEST)) echo $_REQUEST['lat']; ?>">
				</div>
				<div data-role="fieldcontain" >
					<label for="submit" class="ui-hidden-accessible">Keresés:</label>
					<input name="mehet" type="submit" id="submit" value="keresés">
				</div>
			</form>
		</div>
		<div id="results"></div>
		
			
			</div><!-- /content -->
			
			<div data-role="footer" data-position="fixed">
        <h1>véleményezz/ötletelj: <a href="mailto:eleklaszlosj@gmail.com">eleklaszlosj@gmail.com</a></h1>
    </div>
		</div><!-- /page -->
		
		<!-- Start of second page -->
		<div data-role="page" id="map">

			<div data-role="header">
				<h1>Magyar miserend - Térkép</h1>
				<a id="search" href="#search" title="Keresés"   data-role="button" data-icon="search" data-iconpos="notext"><span>Keresés</span></a>
				<a id="help" href="#help" title="Segítség"><span>Segítség</span></a>
			</div><!-- /header -->
	
			<div data-role="content">
	
			<div id="map_canvas" style="width:100%; margin: -15px;
											height: 100%;
											position: absolute;"></div>
			</div><!-- /content -->		
		</div><!-- /page -->
	
		<!-- Start of info page -->
		<div data-role="page" id="help">

			<div data-role="header">
				<h1>Magyar miserend - Segítség</h1>
				<a id="search" href="#search" title="Keresés"><span>Keresés</span></a>
				<a id="map" href="#map" title="Térkép"><span>Térkép</span></a>
			</div><!-- /header -->
	
			<div data-role="content">
				<div data-role="collapsible" data-collapsed="false">
					<h3>Figyelmeztetés!</h3>
					<p>Az oldal a <a href="http://miserend.hu">miserend.hu</a> mindig legfrissebb adatbázisát használja, de a templomok koordinátái általában nincsenek ellenőrizve!</p>
				</div>
				<div data-role="collapsible" data-collapsed="true">
					<h3>A keresőről</h3>
					<p>A beírt szavak alapján keres a templomok neve, címe, városa között az aktuális távolság szerint sorbarendezve (légvonalban).</p>
					<p>Az idézőjelbe rakott kifejezéseket egyben keresi.</p>
					<p>A <i>gitáros</i>, <i>orgonás</i>, <i>csendes</i>, <i>zenés</i> kifejezésekkel a misék fajtái között lehet szűrni.</p>
					<p>Ha megad egy vagy több nyelvet, akkor csak olyan miséket keres, amik az adott nyelven történnek.</p>  
					<p>Megadhat egy időt is, hogy mi legyen a mise kezdő időpontja. Többféleképpen is megadható: 
				 		<ul><li>18:15 - az adott időponttól keres a következő nap ugyan ilyen időpontjáig. 
						<li>2 óra - az aktuális időponttól számított két óra múlva kezdődő misék között keres.</li></ul>
						Valamint megadható a nap is:
						<ul><li>ma, holnap, holnapután
						<li>vasárnap, hétfő, kedd, stb. - a legközelebbi megfelelő napon keres</li></ul></p>
					<p>A <i>diák</i>, <i>diák mise</i>, vagy <i>diákmise</i> szintén működik</p>
					<p>Megadott távolságon belül is lehet keresni: 1 km, 100 m, stb.</p>
			 	</div>
			 	<div data-role="collapsible" data-collapsed="true">
					<h3>Segíts!</h3>
					<p>Jelezzétek a problémákat, de azt is, ha lenne ötletetek a megoldásra.</p>
					<ul>
						<li>Gyaníthatóan a űőíúó-t nem kezeli jól a rendszer. </li>
						<li>A listában az "útvonal" link nem kattintható. (<i>Hogy kell egymás fölé helyezni js izéket?</i>)</li>
						<li>A honlap indításakor lassan jön be a default keresés. (<i>Az aktuális hely meghatározása async(?)-ban történik, ezért a default keresés késleltetve van. Hogy kell az async-et kilőni?</i>)</li>
						<li>Csak az első 20 találat jelenik meg, a többit hogyan lehet megnézni? (<i>Még nincs kifejelesztve. De csak idő kérdése.</i>)
						<li>Több koordináta rossz. (<i>Már készül egy oldal, ahol segíthettek pontosítani a koordinátákat.</i>)</li>
					</ul>
				</div>
			</div><!-- /content -->		
		</div><!-- /page -->


		
	</body>
</html>
