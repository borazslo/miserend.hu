<?php 
$napok = array('','vasárnap','hétfő','kedd','szerda','csütörtök','péntek','szombat','vasárnap');
include_once '../db.php';
$tid = $_REQUEST['tid'];
$query = "SELECT * FROM `templomok`,`terkep_geocode` WHERE `id` = `tid` AND `id` = ".$tid." LIMIT 0,1";
$templomok = db_query($query);

$templom = new stdClass();
foreach ($templomok[0] as $key => $value)
{
	$templom->$key = $value;
}
$query = "SELECT * FROM  misek, terkep_misek_next WHERE misek.id = terkep_misek_next.id  AND `templom` = ".$tid." AND `torles` = '0000-00-00 00:00:00' ORDER BY next LIMIT 0,100";
$templom->misek = db_query($query);



//print_R($templom);

?>
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

	
		<meta name="description" content="Katolikus templomok miserendje Magyarorsz�gon �s m�sutt.">
		<meta property="og:url" content="http://miserend.hu/" /> 
		<meta property="og:title" content="Miserend - VPP" /> 
		<meta property="og:description" content="Katolikus templomok miserendje Magyarorsz�gon �s m�sutt." />
		<!--<meta property="og:image" content="http://prayexamen.com/img/og_image.jpg" />-->

		<title><?php echo $templom->nev; ?> - miserend.hu</title>
		
		
	
	<link rel="stylesheet" href="http://code.jquery.com/mobile/1.1.0/jquery.mobile-1.1.0.min.css" />
		
		<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.min.js"></script>
	
		<script src="http://code.jquery.com/jquery-1.7.1.min.js"></script>
		<script src="http://code.jquery.com/mobile/1.1.0/jquery.mobile-1.1.0.min.js"></script>
		
		<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=true"></script>		
		
		<script type="text/javascript" src="jquery.ui.map.js"></script>
		<script type="text/javascript" src="jquery.ui.map.extensions.js"></script>
		<script type="text/javascript" src="jquery.ui.map.overlays.js"></script>
		
		<script src="miserend.js"></script>
		<script src="getPosition.js"></script>
	</header>
	
	<body>
	
		<!-- Start of info page -->
		<div data-role="page" id="church">

			<div data-role="header">
				<h1><?php echo $templom->nev; ?></h1>
				<a id="search" href="index.php/#search" title="Keresés"><span>Keresés</span></a>
				<a id="map" href="index.php#map" title="Térkép"><span>Térkép</span></a>
			</div><!-- /header -->
	
			<div data-role="content">
				<a href="http://miserend.hu/?templom=<?php echo $templom->id; ?>">miserend.hu</a>
				<div data-role="collapsible" data-collapsed="true">
					<h3><?php if($templom->ismertnev != '') echo $templom->ismertnev; else echo $templom->nev; ?></h3>
					<p><?php echo $templom->orszag." ".$templom->megye." ".$templom->varos; ?></p>
				</div>
				<div data-role="collapsible" data-collapsed="false">
					<h3>Megközelíthetőség</h3>
					<p><?php echo $templom->megkozelites; ?></p>
					<p><a href="index.php#map">térképen</a>, <a href="">útvonalterv</a></p>
				</div>
				<div data-role="collapsible" data-collapsed="false">
					<h3>Misék</h3>
					<?php if(is_array($templom->misek)) { 
							foreach($templom->misek as $mise) {
								echo "<p>".$mise['idoszamitas']." ".$napok[$mise['nap']+1]." ".$mise['ido']." next: <b>".date('Y-m-d H:i',$mise['next'])."</b></p>";
								//print_R($mise);
							}
						} else echo "Nem tudunk miséről";
					?>
			 	</div>
			 </div><!-- /content -->		
		</div><!-- /page -->


		
	</body>
</html>
