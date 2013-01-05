templomok = Object();

var Markers = {};
var mc = new Object;

function load() {
	$.mobile.showPageLoadingMsg();

	//prepareGeolocation();
	//doGeolocation();
	$('[data-role="page"]').live('pageshow', function () {
		if( $(this).attr("id") == 'search' ) {
			search();
		}
		else if( $(this).attr("id") == 'map' ) {
			markers();
		}
	});
	
	gmap();

	
	//function wait() {if(xy == 'maci') {		wait();	}	}	wait();

	$('#map').live("pageshow", function() {
	      $('#map_canvas').gmap('refresh');
	});
	
}

function search() {
	$.mobile. showPageLoadingMsg();
	
    document.getElementById('results').innerHTML = '';
    
	getTemplomok(clientPosition.lat(),clientPosition.lng(),document.getElementById('search-text').value);
	//console.log(templomok);

	if(templomok.count == 0) templomok.results = Object();
	
	
	for (var i=0; i<templomok.results.length; i++) {
		var templom = templomok.results[i];
			
			var kk = '<div data-role="collapsible"><h3>';
			//kk += templom.templom.sql.id
			kk += " " + templom.templom.nev;
			if(templom.templom.distance.raw > 10000 ) kk += " (" + templom.templom.sql.varos +") ";
			//kk += templom.milyen;
			if(templom.megjegyzes != '') kk += '<span class="ui-li-has-count ui-li-count ui-btn-up-c ui-btn-corner-all" style="float: right; font-size: 80%; padding: 1px; right:25px; margin-right: 5px;"><a title="' + templom.megjegyzes + '">&nbsp;i&nbsp;</a></span>';
			kk += '<span class="ui-li-has-count ui-li-count ui-btn-up-c ui-btn-corner-all" style="float: right; font-size: 80%; padding: 1px; right:25px; margin-right: 5px;">' + templom.templom.distance.formatted + '</span>';
			kk += '<span class="ui-li-has-count ui-li-count ui-btn-up-c ui-btn-corner-all" style="float: right; font-size: 80%; padding: 1px; right:25px; margin-right: 5px;">' + templom.next.formatted + '</span>';
			kk += '<span class="ui-li-has-count ui-li-count" style="float: right; font-size: 80%; padding: 1px; right:25px; margin-right: 5px;"><a href="' + templom.templom.distance.url + '">útvonal</a></span>';
			if(templom.lastchanged.formatted > 1) kk += '<span class="ui-li-has-count ui-li-count " style="float: right; font-size: 80%; padding: 1px; right:25px; margin-right: 5px;"><a title="Tött mint '+ templom.lastchanged.formatted + ' éves adat!">' + templom.lastchanged.sign + '</a></span>';
			kk += '</h3>';
			kk += '<p><strong>' + templom.templom.sql.nev + '</strong></p>';
			kk += '<p>' + templom.templom.sql.megkozelites + '</p>';
			kk += '<p>' + templom.templom.sql.plebania + '</p>';
			kk += '<p><a href="'+ templom.templom.distance.url + '">útvonaltervezés</a></p>';
			kk += '<p><a href="http://miserend.hu/?templom='+templom.templom.sql.id+'">miserend.hu/?templom='+templom.templom.sql.id+'</a></p>';
			kk += '<p><a href="templom.php?tid='+templom.templom.sql.id+'">részletek</a></p>';
			kk +='</div>';
			$(kk).appendTo('[id="results"]');
				
	}
	
	// TODO-design: Ennek normálisan kéne kinéznie.
	var kk = '<p>';
	if(templomok.count == 0) {
			
			if(templomok.error == "templomok") {
				kk += 'Nem találtunk (ilyen) templomot az adott környéken. Talán próbáld meg nagyobb körben keresni, vagy helyezd át magadat a térképen.';
			}
			else if(templomok.error == "misek") {
				kk += 'Templomot találtunk, de az adott feltételeknek megfelelő misét sajnos nem.';
			}
			else {
				kk += 'Nincs egyetlen találat sem. Sajnáljuk.';
			} 
			console.log(templomok);
	}
	else if(templomok.count <= 20) kk += templomok.count + ' db találatunk van.';
	else kk += 'Összesen ' + templomok.count + ' találatunk van. Ez csak az első húsz volt.';
	kk += '</p>';
	$(kk).appendTo('[id="results"]');
		
	$('div[data-role=collapsible]').collapsible({theme:'c',refresh:true});
	
	$.mobile.hidePageLoadingMsg();
}

function gmap() {
	$('#map_canvas').gmap({'zoom': 5}).bind('init', function(evt, map) {
		
		$('#map_canvas').gmap('getCurrentPosition', function(position, status) {
			if ( status === 'OK') {
				clientPosition = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
			}
			else {
				clientPosition = new google.maps.LatLng(47.49843,19.040762);
			}
	
			$('#map_canvas').gmap('addMarker', {
					'position': clientPosition, 
					'draggable': true,
					'icon':  '../images/icons/red/blank.png'})
				.click(function() {
					$('#map_canvas').gmap('openInfoWindow', {'content': 'Reméljük, éppen itt vagy.'}, this);
				})
				.dragend(function(event) {
					clientPosition = this.position;
					//console.log(clientPosition);
				});
			
			$('#map_canvas').gmap('get','map').setOptions({'center':clientPosition,'zoom':15});
			
			document.getElementById('lat').value = clientPosition.lat();
		 	document.getElementById('lng').value = clientPosition.lng();
			
			search();
		 	
		 	/*
			var bounds = map.getBounds();
			latNE = 90; //bounds.getNorthEast().lat();
			lngNE = 180; //bounds.getNorthEast().lng();
			latSW = -90 //bounds.getSouthWest().lat();
			lngSW = -180; //bounds.getSouthWest().lng();
			var filename = '../getmarkers.php?latNE='+latNE+'&lngNE='+lngNE+'&latSW='+latSW+'&lngSW='+lngSW; 
		  
			console.log(filename);
			var templom;
			$.ajax({
				type: "GET",
				url: filename,
				async: false,
				beforeSend: function(x) {
					if(x && x.overrideMimeType) {
						x.overrideMimeType("application/j-son;charset=UTF-8");
					}
				},
				dataType: "json",
				success: function(data){
					templom = data;
					
					
					$.each(templom.result, function(tid, sql) {
						//console.log(sql);
						var position = new google.maps.LatLng(sql.lat, sql.lng);
						$('#map_canvas').gmap('addMarker', { 
							'position': position,
							'type': 'church',
							'icon' : '../images/icons/church-green.png'
						}).click(function() {
							//$('#map_canvas').gmap('openInfoWindow', { content : sql.nev }, this);
							$('#map_canvas').gmap('get','map').setOptions({'center':position});
							$.mobile.changePage('templom.php?tid='+sql.id);
						});
					});
					
					//$('#map_canvas').gmap('set', 'MarkerClusterer', new MarkerClusterer(map, $('#map_canvas').gmap('get', 'markers')));
					var markers = new Array();;					
					$('#map_canvas').gmap('find', 'markers', { 'property': 'type', 'value': 'church' }, function(marker, found) {
						if(marker.type == 'church') {
							markers.push(marker);
						}
					});
					
					 var styles = [[{
						 url: '../images/icons/green/blank.png',
					        height: 35,
					        width: 35,
					        anchor: [0, 0],
					        textColor: '#ff00ff',
					        textSize: 10,				        
					      }]];				
					$('#map_canvas').gmap('set', 'MarkerClusterer', new MarkerClusterer(map, markers, {
						styles: styles[0],
					}));					
					//$('#map_canvas').gmap('find', 'markers', { 'property': 'type', 'value': 'church' }, function(marker, found) {					
				}	
			});		
			*/
			var position = clientPosition;					
			// To call methods in MarkerClusterer simply call 
			// $('#map_canvas').gmap('get', 'MarkerClusterer').callingSomeMethod();
	
		});
	
	});
	
	var map = $('#map_canvas').gmap('get','map');
	//map.dragstart();
	google.maps.event.addListener(map, 'idle',function() { markers(); });
	
} 

function markers() {
	var MarkersInUse = Markers;
	var MarkersRendered = new Array();
	var MarkersNew = new Array();
		
	
	var map = $('#map_canvas').gmap('get','map');
	
	var bounds = map.getBounds();
	latNE = 90; //bounds.getNorthEast().lat();
	lngNE = 180; //bounds.getNorthEast().lng();
	latSW = -90 //bounds.getSouthWest().lat();
	lngSW = -180; //bounds.getSouthWest().lng();
	latNE = bounds.getNorthEast().lat();
	lngNE = bounds.getNorthEast().lng();
	latSW = bounds.getSouthWest().lat();
	lngSW = bounds.getSouthWest().lng();
	var filename = '../getmarkers.php?latNE='+latNE+'&lngNE='+lngNE+'&latSW='+latSW+'&lngSW='+lngSW; 
  
	//console.log(filename);
	var templom;
	$.ajax({
		type: "GET",
		url: filename,
		async: false,
		beforeSend: function(x) {
			if(x && x.overrideMimeType) {
				x.overrideMimeType("application/j-son;charset=UTF-8");
			}
		},
		dataType: "json",
		success: function(data){
			templom = data;
			
			$.each(templom.result, function(tid, sql) {
				if(MarkersInUse[sql.id]) {
					
				} else {
					MarkersInUse[sql.id] = sql;
					MarkersNew[sql.id] = sql.id;
					
					var position = new google.maps.LatLng(sql.lat, sql.lng);
					 $('#map_canvas').gmap('addMarker', { 
						'position': position,
						'id' : sql.id,
						'type': 'church',
						'icon' : '../images/icons/church-green.png'
					}).click(function() {
						//$('#map_canvas').gmap('openInfoWindow', { content : sql.nev }, this);
						$('#map_canvas').gmap('get','map').setOptions({'center':position});
						$.mobile.changePage('templom.php?tid='+sql.id);
					});
				}				
			});					
					
		}	
	});	
	
	if(!mc.map) {
		var styles = [[{
			 url: '../images/icons/green/blank.png',
		        height: 35,
		        width: 35,
		        anchor: [0, 0],
		        textColor: '#ff00ff',
		        textSize: 10,				        
		      }]];
		
		$('#map_canvas').gmap('set', 'MarkerClusterer', mc = new MarkerClusterer(map, [], {
			styles: styles[0],
		}));
	}
	
	$('#map_canvas').gmap('find', 'markers', { 'property': 'type', 'value': 'church' }, function(marker, found) {
		if(marker.type == 'church' && jQuery.inArray(marker.id, MarkersNew) < 0) {
			MarkersRendered.push(marker);
		}
	});
	console.log(mc);
	mc.addMarkers(MarkersRendered);	
	
}

function getTemplomok(lat,lng,search) {
	var filename = 'search.php?lng=' + encodeURIComponent(lng) + '&lat=' + encodeURIComponent(lat) + '&limit=20' + '&search=' + encodeURIComponent(search) ;
	console.log(filename);
	$.ajax({
	     type: "GET",
	     url: filename,
	     async: false,
	     beforeSend: function(x) {
	      if(x && x.overrideMimeType) {
	       x.overrideMimeType("application/j-son;charset=UTF-8");
	      }
	 },
	 dataType: "json",
	 success: function(data){
	    //do your stuff with the JSON data
		 templomok = data;
	 }	
	 
	});
	
}

function isEmpty(ob){
	   for(var i in ob){ return false;}
	  return true;
	}	