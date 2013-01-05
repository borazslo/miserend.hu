/* ---------------------------- */
/* XMLHTTPRequest Enable */
/* ---------------------------- */
function createObject() {

var request_type;
var browser = navigator.appName;
if(browser == "Microsoft Internet Explorer"){
request_type = new ActiveXObject("Microsoft.XMLHTTP");
}else{
request_type = new XMLHttpRequest();
}
return request_type;
}

var http = createObject();

/* -------------------------- */
/* INSERT */
/* -------------------------- */
/* Required: var nocache is a random number to add to request. This value solve an Internet Explorer cache issue */
var nocache = 0;

function insert() {
// Optional: Show a waiting message in the layer with ID login_response
document.getElementById('insert_response').innerHTML = "Pillanat..."
document.getElementById('insert_response').style.visibility = 'visible';
// Required: verify that all fileds is not empty. Use encodeURI() to solve some issues about character encoding.
var slng= encodeURI(document.getElementById('slng').value);
var slat = encodeURI(document.getElementById('slat').value);
tid= encodeURI(document.getElementById('tid').value);
var uid= encodeURI(document.getElementById('uid').value);
var spoint= encodeURI(document.getElementById('spoint').value);
var sdistance= encodeURI(document.getElementById('sdistance').value);
var tchecked= encodeURI(document.getElementById('tchecked').value);
// Set te random number to add to URL request
nocache = Math.random();

if(sdistance == "0") {
document.getElementById('insert_response').innerHTML = "Nem is került arrébb a jelölő!";
document.getElementById('insert_response').style.visibility = 'visible';
} else {

// Pass the login variables like URL variable
http.open('get', 'insert.php?slat='+slat+'&slng=' +slng+'&tid=' +tid+'&uid=' +uid+ '&spoint=' +spoint+'&sdistance=' +sdistance+'&tchecked=' +tchecked+'+&nocache = '+nocache);
http.onreadystatechange = insertReply;
http.send(null); 
}
}

function insertReply() {
//getMarkers(); //Nem csak újra kéne rakni, de ki is kéne írtani a régit...
//mgr.refresh();

if(http.readyState == 4){ 
//var response = http.responseText;
// else if login is ok show a message: "Site added+ site URL".
//document.getElementById('insert_response').innerHTML = 'New place added:'+response+"d"+"<br>";

for (var i in markers)
 {
 if ( markers[i].tid == tid)
  { var key = i;  }
 }
 
 if(markers[key].icon.match(/red/g)) markers[key].setIcon(icons['church-1']); 
  markers[key].setPosition(new google.maps.LatLng(markers[key].lat,markers[key].lng));
  
eval("var response = ("+http.responseText+")");
document.getElementById('insert_response').innerHTML = response.message;
document.getElementById('insert_response').style.visibility = 'visible';

if(response.rank) document.getElementById('main-rank').innerHTML = response.rank;

var uid = document.getElementById('uid').value;
if(uid == '1819502454') { 
		setMarkers(getMarkers(map.getBounds(),'23'),'markersSuggestions');
}
//getMarkers(); //Nem csak újra kéne rakni, de ki is kéne írtani a régit...
//mgr.refresh();

}

}

function Approve(sid,ad,arg) {
  var file = 'approve.php?sid='+sid+'&ad='+ad+'&arg='+arg; 
  console.log(file);
  var markers = {};
    $.ajax({
        url: file,
        async: false,
        dataType: 'json',
        success: function(data) {
			/*
			$.each(data.result, function(tid, sql) {
	
					
				if(getUrlVar("templom")) {
					map.setCenter(new google.maps.LatLng(sql.lat,sql.lng));
					}
			if(marker) 	markers[tid] = marker;	
			}); */
			console.log(data);
        }
    });
	
	document.getElementById('insert_response').innerHTML = "Rögzítve.";
	setMarkers(getMarkers(map.getBounds()));
	setMarkers(getMarkers(map.getBounds(),'23'),'markersSuggestions');
}