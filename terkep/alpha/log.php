<?php if($_REQUEST['pw'] != 'nemTudom') {echo "Jelentkezz be!"; exit;} ?>
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

		<!--<meta property="og:image" content="http://prayexamen.com/img/og_image.jpg" />-->

		<title>Miserend - VPP</title>
		
		
	
		<link rel="stylesheet" href="http://code.jquery.com/mobile/1.1.0/jquery.mobile-1.1.0.min.css" />
		
		<script src="http://code.jquery.com/jquery-1.7.1.min.js"></script>
		<script src="http://code.jquery.com/mobile/1.1.0/jquery.mobile-1.1.0.min.js"></script>
		
		<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=true"></script>		
		
		<script type="text/javascript" src="jquery.ui.map.js"></script>
		<script type="text/javascript" src="jquery.ui.map.extensions.js"></script>
		<script type="text/javascript" src="markerclusterer.js"></script>
		<?php if($_REQUEST['pw'] == 'nemTudom'): ?>
		<script>
		var logs = new Array();
		function load() {
			$.ajax({
		        url: 'log_json.php?pw=nemTudom&json=true',
		        async: false,
		        dataType: 'json',
		        success: function(data) {
			        $.each( data, function(i, log) {
			        	logs.push(log);
						//console.log(log);
						/*logs[log.c] = log;*/
		    		});
		        }
		    });
			logs.sort(function(a,b) {return (a.time < b.time) ? 1 : ((b.time < a.time) ? -1 : 0);});
			 
				
				$('#map_canvas').gmap({'zoom': 5, 'disableDefaultUI':false, 'center': new google.maps.LatLng(47.49843,19.040762)}).bind('init', function(evt, map) { 
					$.each( logs, function(i, log) {
				    	$('#map_canvas').gmap('addMarker', { 
							'logc': log.c,
							'maci':'krumpli',
							'position': new google.maps.LatLng(log.request.lat, log.request.lng),
						}).click(function() {
							$('#map_canvas').gmap('openInfoWindow', { content : log.request.search }, this);
						}) 
					});	 								
					$('#map_canvas').gmap('set', 'MarkerClusterer', new MarkerClusterer(map, $(this).gmap('get', 'markers')));
				});

				$.each( logs, function(i, log) {
					
					var kk = '<div data-role="collapsible"><h3>';
					//kk += templom.templom.sql.id
					var date = new Date(log.time*1000);
					
					kk += "("+date.getFullYear()+". "+(date.getMonth()+1)+". "+date.getDate()+" "+date.getHours()+":"+date.getMinutes()+")";
					kk += ' "'+log.request.search+'"';
					kk += '<span class="ui-li-has-count ui-li-count ui-btn-up-c ui-btn-corner-all" style="float: right; font-size: 80%; padding: 1px; right:25px; margin-right: 5px;">';
					if(log.count != 0) kk += log.count;
					if (log.error) kk += log.error;
					kk += '</span></h3>';

					kk += '<p><a href="" onClick="javascript:clicklog('+log.c+')">térkép</a></p>';
					kk +='</div>'; 
					$(kk).appendTo('[id="results"]');	
			});

		$('div[data-role=collapsible]').collapsible({theme:'c',refresh:true});
				
		$('#map').live("pageshow", function() {
		      $('#map_canvas').gmap('refresh');
		});
		
		}

		
		function clicklog(c) { 
			$('#map_canvas').gmap('find', 'markers', { 'property': 'logc', 'value': c }, function(marker, found) {
				
				if(marker.logc == c) {
					console.log(marker);
					$('#map_canvas').gmap('get','map').setOptions({'center':marker.position,'zoom':15});
				}			
			});
			$.mobile.changePage('#map');  
		  } 
	
		</script>
		<?php endif; ?>
	</header>
	
	<body onload="load()">		
	
	<div data-role="page" id="list">
			<div data-role="header">
				<h1>Napló listája - miserend.hu</h1>
				<a id="map" href="#map" title="Térkép"  data-role="button" style="float:right"><span >Térkép</span></a>
			</div><!-- /header -->
		
			<div data-role="content">
			<?php if($_REQUEST['pw'] != 'nemTudom') echo "Jelentkezz be!"; ?>
				<div id="results"></div>
			</div><!-- /content -->		
		</div><!-- /page -->
		
		<!-- Start of second page -->
		<div data-role="page" id="map">

			<div data-role="header">
				<h1>Napló térképe - miserend.hu</h1>
				<a id="list" href="#list" title="Lista"  data-role="button" style="float:right"><span >Lista</span></a>
			</div><!-- /header -->
	
			<div data-role="content">
					<?php if($_REQUEST['pw'] != 'nemTudom') echo "Jelentkezz be!"; ?>
			
				<div id="map_canvas" style="width:100%; margin: -15px;
											height: 100%;
											position: absolute;"></div>
			</div><!-- /content -->		
		</div><!-- /page -->
	
	
		
		</body>
</html>
