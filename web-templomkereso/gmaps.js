    //<![CDATA[
	
   
        var icons = {
        'church':          'icons/church-red.png',
		'church-':          'icons/church-red.png',
		'church-0':          'icons/church-red.png',
        'church-selected': 'icons/church-red.png',
		'church-1': 'icons/church-yellow.png',
		'church-2': 'icons/church-green.png',
		'suggestion-0': 'icons/red/blank.png',
		'suggestion-1': 'icons/yellow/blank.png',
		'suggestion-2': 'icons/green/blank.png',
		
      }
    var map = null;
    var markers = {};
	var markersSuggestions = {};
	var markerDrag = '';
	var filter = new Object;
		filter[0] = true;
		filter[1] = true;
		filter[2] = true;
	
    function load() {
		var myOptions = {
			center: new google.maps.LatLng(47.498416,19.040766),
			zoom: 12,
			mapTypeId: google.maps.MapTypeId.ROADMAP
		};
		
		map = new google.maps.Map(document.getElementById('map_canvas'), myOptions);
		
		prepareGeolocation();
		doGeolocation();
		
		
		google.maps.event.addListener(map, 'idle', function(){
			setMarkers(getMarkers(map.getBounds()));
		
		  var uid = document.getElementById('uid').value;
		  if(uid == '1819502454') { 
				setMarkers(getMarkers(map.getBounds(),'23'),'markersSuggestions');
			}
	 
	 });  
	 if(getUrlVar("templom")) {
		document.getElementById('welcome').style.visibility = 'hidden';
	 }
	 
	 google.maps.event.addListener(map, 'click', function(){
			document.getElementById('welcome').style.visibility = 'hidden';
			document.getElementById('suggestion-ac').style.visibility = 'hidden';
			var info = $('#RightBar');
            info.animate({right: '-350'});
			
      });
	  	  
    }
	
	function MarkerListeners(marker) {
		google.maps.event.addListener(marker, 'click', function() {
			if(marker.type == 'church') { SliderSuggest(marker); }
			else if(marker.type == 'suggestion') { SliderApprove(marker); }
		}); 
		google.maps.event.addListener(marker, 'dragend', function() {
			if(marker.type == 'church') { SliderSuggest(marker); }
		}); 
		google.maps.event.addListener(marker, 'dragstart', function() {
			if(marker.type == 'church') { 
				  console.log("előbb: "+markerDrag.tid);
				  if(markerDrag != '') markerDrag.setPosition(new google.maps.LatLng(markerDrag.lat,markerDrag.lng));
				  markerDrag = '';
				  markerDrag = marker;
				  console.log("utóbb: "+markerDrag.tid);
				}
		}); 
	}
	function SliderSuggest(marker) {
			
		var currentPlace = null;
		var detail = $('#Details');
		var info = $('#RightBar');
			
		$('h1', detail).text(marker.title);
        $('p',  detail).html(marker.address + "<br>"); 	
				
		var hidingMarker = currentPlace;
        var slideIn = function(marker) {
            
			info.animate({right: '0'});
			  document.getElementById('RightBarContent1').style.display = 'block';
			document.getElementById('RightBarContent2').style.display = 'none';			
        	
			document.getElementById('suggestion').style.visibility = 'visible'; 
			document.getElementById('slat').value = marker.getPosition().lat();
			document.getElementById('slng').value = marker.getPosition().lng();
						
			var lat1 = marker.lat * Math.PI / 180;
			var lat2 = marker.getPosition().lat() * Math.PI / 180;
			var long1 = marker.lng * Math.PI / 180;
			var long2 = marker.getPosition().lng() * Math.PI / 180;
			//document.write(lat1+"##"+lat2);
			var R = 6371; // km
			var d = R * Math.acos(Math.sin(lat1) * Math.sin(lat2) + Math.cos(lat1) * Math.cos(lat2) * Math.cos(long2 - long1)) * 1000;
			
			formatter = new DecimalFormat("0");
			d = formatter.format(d);
			
			var p = 0;
			var A = 300; //szakasz határ távolság
			var B = 3500; //szakasz határ távolság
			var H = 100; //szakasz határ pont
			var max = 250; // csúcs pont
			var l = 80; // laoposodás
			var x = d; //távolság
	
			if(x < A) { 
				a1 = H/Math.pow(A,2);
				p = a1*Math.pow(x,2); }
			else if (x==A) { p = H; }
			else if (x < B) { 
					a2=((A+B)/2);
					b2=max;
					/* c2=((max-H)/((A-B)/2)^2); */
					c2=(max-H)/Math.pow((A-B)/2,2);
					p = -c2*Math.pow(x-a2,2)+b2;
					}
			
			else { 
				a3 = l;
				c3 = H*(B-a3);
				p = c3/(x-a3); 
			}
		
			//A messzi dolgok helyrerakását határozottan díjazzuk
			if(x>50000) { p += max; }

			//Attól függően, hogy mennyire ellenőrzött már, másképp támogatjuk
			if(marker.checked == 1 && x > 1000) {
			   p = p * 1.5;
			}
			else if(marker.checked == 'x') {
				p = p * 0.5;
			}
			
			document.getElementById('insert_response').style.visibility = 'hidden';
			document.getElementById('spoint').value = p;
			document.getElementById('sdistance').value = d;
			document.getElementById('tchecked').value = marker.tchecked;

			p = formatter.format(p);		
			document.getElementById('geocode-text').innerHTML = 
					"felhasználó: "+document.getElementById('uid').value +"<br>"+
					"szélesség "+ marker.getPosition().lat()+"<br>"+
					"hosszúság: "+ marker.getPosition().lng()+"<br>"+
					"távolság: "+d+" m"+"<br>"+
					"PONTOK: "+p+" pt<br>";
					
					//"debug: "+mgr.getMarkerCount(map.getZoom());
			document.getElementById('tid').value = marker.tid;
			
		}; 
			
            if (currentPlace) {
              //currentPlace.setIcon(icons['church']);

              info.animate(
                { right: '-350px' },
                { complete: function() {
                  if (hidingMarker != marker) {
                    slideIn(marker);
                  } else {
                    currentPlace = null;
                  }
                }}
              );
			  $('#RightBarContent1').display('block');
			  $('#RightBarContent2').display('none');
			
            } else {
              slideIn(marker);
            }
            currentPlace = marker;
		
	};
		
	function SliderApprove(marker) {
		var currentPlace = null;	
		var detail = $('#Details');
		var info = $('#RightBar');
			
		$('h1', detail).text(marker.title);
        $('p',  detail).html(marker.address + "<br>"); 	
		
		var hidingMarker = currentPlace;		
		var slideIn = function(marker) {
            
			$('#RightBarContent1').display('block');
			  $('#RightBarContent2').display('none');
			info.animate({right: '0'});
			
			//console.log(marker);
        	
			document.getElementById('geocode-text').innerHTML = 
					"<a href=\"javascript:Approve('"+marker.sid+"','deny')\">elutasít</a> "+
					"<a href=\"javascript:Approve('"+marker.sid+"','accept')\">elfogad</a> "+
					"<a href=\"javascript:Approve('"+marker.sid+"','accept','default')\">elfogad és áthelyez</a> ";
		}; 
			
        if (currentPlace) {
              //currentPlace.setIcon(icons['church']);

              info.animate(
                { right: '-350px' },
                { complete: function() {
                  if (hidingMarker != marker) {
                    slideIn(marker);
                  } else {
                    currentPlace = null;
                  }
                }}
              );
            } else {
              slideIn(marker);
            }
            currentPlace = marker;
	};

function setMarkers(markersRaw,object) {
	
	var markersInUse = {};
	if(object) markersInUse = eval(object);
	else markersInUse = markers;
	
	var size = 0;	
	for (var i in markersInUse) {
		if (markersRaw.hasOwnProperty(i)) { delete markersRaw[i]; size++;}
		else { 
			if(markerDrag.tid) { 
					markersInUse[markerDrag.tid] = markerDrag;}
			else {
				markersInUse[i].setMap(null); delete markersInUse[i]; }
			}
	}  
	
	for (var i in markersRaw) {
		if (markersRaw.hasOwnProperty(i)) size++;
		markersInUse[markersRaw[i].tid] = new google.maps.Marker({
			position: markersRaw[i].position,
			tid: markersRaw[i].tid,
			map: map,
			address: markersRaw[i].address,
			icon: markersRaw[i].icon,
			draggable: markersRaw[i].draggable,
			type: markersRaw[i].type,
			description: markersRaw[i].description,
			title: markersRaw[i].title,
			lat: markersRaw[i].position.lat(),
			lng: markersRaw[i].position.lng(),
			sid: markersRaw[i].sid,
			tchecked: markersRaw[i].tchecked
		})
		MarkerListeners(markersInUse[markersRaw[i].tid]);
		if(markerDrag != '') { 
		//	markersInUse[markerDrag.tid] = markerDrag;
		//console.log(markerDrag); 
		}
		
	}
}

function getMarkers(bounds,suggestions) {
  
  latNE = bounds.getNorthEast().lat();
  lngNE = bounds.getNorthEast().lng();
  latSW = bounds.getSouthWest().lat();
  lngSW = bounds.getSouthWest().lng();
  var file = 'getmarkers.php?latNE='+latNE+'&lngNE='+lngNE+'&latSW='+latSW+'&lngSW='+lngSW; 
  if(getUrlVar("templom")) file = 'getmarkers.php?tid='+getUrlVar("templom");
  if(suggestions == '23') file += '&suggestions=true';
  //console.log(file);
  var markers = {};
    $.ajax({
        url: file,
        async: false,
        dataType: 'json',
        success: function(data) {
			$.each(data.result, function(tid, sql) {
	
				var marker	= new Object();
					marker.map = map;
					marker.position = new google.maps.LatLng(sql.lat,sql.lng);
					marker.title = sql.name;
					marker.icon = icons[sql.icon];
					marker.tid = tid;
					marker.address = sql.address;
					marker.type = sql.type;
					marker.draggable = sql.draggable;
					marker.tchecked = sql.checked;
					marker.sid = sql.sid;
					
				if(getUrlVar("templom")) {
					map.setCenter(new google.maps.LatLng(sql.lat,sql.lng));
					}
			if(marker) {
				if(marker.type == 'church') {
				if(sql.icon == 'church-0' && filter[0] == true)	markers[tid] = marker;	
				else if(sql.icon == 'church-1' && filter[1] == true)	markers[tid] = marker;	
				else if(sql.icon == 'church-2' && filter[2] == true)	markers[tid] = marker;	
				}
				else markers[tid] = marker;
			}
			});
        }
    });
  return markers;
}

function changeFilter(type) {
	var tag = document.getElementById('filter'+type+'a').innerHTML;
	if(tag == 'I') {
		document.getElementById('filter'+type+'a').innerHTML = '0';
		filter[type] = false;
	}
	else {
		document.getElementById('filter'+type+'a').innerHTML = 'I';
		filter[type] = true;
	}
	setMarkers(getMarkers(map.getBounds()));

}

function getUrlVar(key){
	var result = new RegExp(key + "=([^&]*)", "i").exec(window.location.search); 
	return result && unescape(result[1]) || ""; 
}
    //]]>