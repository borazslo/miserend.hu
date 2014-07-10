<!-- OpenLayers.Strategy.AnimatedCluster -->
<!-- http://acuriousanimal.com/code/animatedCluster/ -->

<script defer="defer" type="text/javascript">
var selectedFeature;

$('#infosign').click(function(){
        $('.udvozlet').toggle( "slide", { direction: "up" }, 200);
    });


function torol() {
	//$('#box').slideUp("fast");
	$( "#box" ).hide( "slide", { direction: "right" }, 100);
	
	$('#folyamatban').slideDown("fast");
	console.log('http://terkep.miserend.hu/terkep_torol.php?'+$('form#bekuldForm').serialize());
	$.ajax({
        url: 'terkep_torol.php',
        type: 'post',
        dataType: 'json',
        data: $('form#bekuldForm').serialize(),
        success: function(data) {
                   //... do something with the data...		   	
				   $('.folyamatban').slideUp("fast");
				   $('#message').slideDown("fast");	
				   
					window.setTimeout(function() {$('#message').slideUp("fast");}, 3000);
   
				   document.getElementById("message").innerHTML = data['text'];
				   if(data['return'] == 'ok') {
						
						
				   }
                 }
		 
    });
	/* */	
}		
	

function bekuldForm() {	
	//$('#box').slideUp("fast");
	$( "#box" ).hide( "slide", { direction: "right" }, 100);
	
	$('#folyamatban').slideDown("fast");
	console.log('http://terkep.miserend.hu/terkep_bekuld.php?'+$('form#bekuldForm').serialize());
	$.ajax({
        url: 'terkep_bekuld.php',
        type: 'post',
        dataType: 'json',
        data: $('form#bekuldForm').serialize(),
        success: function(data) {
                   //... do something with the data...		   	
				   $('.folyamatban').slideUp("fast");
				   $('#message').slideDown("fast");	
				   
					window.setTimeout(function() {$('#message').slideUp("fast");}, 3000);
   
				   document.getElementById("message").innerHTML = data['text'];
				   if(data['return'] == 'ok') {
						selectedFeature.style.externalGraphic = "images/icons/church-green2.png";
						
						if(document.getElementById("checked").value == '0') {						
							document.getElementById("pontosszam").innerHTML = parseFloat(document.getElementById("pontosszam").innerHTML) + 1;
							document.getElementById("gyanusszam").innerHTML = parseFloat(document.getElementById("gyanusszam").innerHTML) - 1;
							redlayer.redraw();			   				   
						} else
						greenlayer.redraw();
						
				   }
                 }
		 
    });
	/* */
}


$('.folyamatban').click(function(){
        $('.folyamatban').slideUp("fast");
    });

	
$('.message').click(function(){
        $('.message').slideUp("fast");
    });

	
$('#boxx').click(function(){
        /*$('#box').slideUp("fast");*/
	  $( "#box" ).hide( "slide", { direction: "right" }, 100);
    });	

    var map = new OpenLayers.Map('map');
    map.addLayer(new OpenLayers.Layer.OSM());
    map.addLayer(new OpenLayers.Layer.Google("Google maps", {numZoomLevels: 20} ));
    map.addLayer(new OpenLayers.Layer.Google("Google Satellite", {type: google.maps.MapTypeId.SATELLITE, numZoomLevels: 22} ));

	
            // Define three colors that will be used to style the cluster features
            // depending on the number of features they contain.
			/*
            var colors = {
                low: "rgb(181, 226, 140)", 
                middle: "rgb(241, 211, 87)", 
                high: "rgb(253, 156, 115)"
            };*/
			var colors = {
                low: "rgb(181, 226, 140)", 
                middle: "rgb(181, 226, 140)", 
                high: "rgb(181, 226, 140)", 
            };
            
            // Define three rules to style the cluster features.
            var lowRule = new OpenLayers.Rule({
                filter: new OpenLayers.Filter.Comparison({
                    type: OpenLayers.Filter.Comparison.LESS_THAN,
                    property: "count",
                    value: 15
                }),
                symbolizer: {
                    fillColor: colors.low,
                    fillOpacity: 0.9, 
                    strokeColor: colors.low,
                    strokeOpacity: 0.5,
                    strokeWidth: 12,
                    pointRadius: 10,
                    label: "${count}",
                    labelOutlineWidth: 1,
                    fontColor: "#ffffff",
                    fontOpacity: 0.8,
                    fontSize: "12px"
                }
            });
            var middleRule = new OpenLayers.Rule({
                filter: new OpenLayers.Filter.Comparison({
                    type: OpenLayers.Filter.Comparison.BETWEEN,
                    property: "count",
                    lowerBoundary: 15,
                    upperBoundary: 50
                }),
                symbolizer: {
                    fillColor: colors.middle,
                    fillOpacity: 0.9, 
                    strokeColor: colors.middle,
                    strokeOpacity: 0.5,
                    strokeWidth: 12,
                    pointRadius: 15,
                    label: "${count}",
                    labelOutlineWidth: 1,
                    fontColor: "#ffffff",
                    fontOpacity: 0.8,
                    fontSize: "12px"
                }
            });
            var highRule = new OpenLayers.Rule({
                filter: new OpenLayers.Filter.Comparison({
                    type: OpenLayers.Filter.Comparison.GREATER_THAN,
                    property: "count",
                    value: 50
                }),
                symbolizer: {
                    fillColor: colors.high,
                    fillOpacity: 0.9, 
                    strokeColor: colors.high,
                    strokeOpacity: 0.5,
                    strokeWidth: 12,
                    pointRadius: 20,
                    label: "${count}",
                    labelOutlineWidth: 1,
                    fontColor: "#ffffff",
                    fontOpacity: 0.8,
                    fontSize: "12px"
                }
            });    
			var oneRule = new OpenLayers.Rule({
            filter: new OpenLayers.Filter.Comparison({
                type: OpenLayers.Filter.Comparison.LESS_THAN,
                 property: "count",
                value: 1
            }),
            symbolizer: {
                fillOpacity: 1, 
				opacity : 1,
				label: "",
                externalGraphic: "${text}",
                graphicHeight: 64, 
				graphicWidth: 32, 
				graphicXOffset:0, 
				graphicYOffset:+5,
			},
			
            
			
        }); //graphicHeight: 32, graphicWidth: 32, graphicXOffset:-16, graphicYOffset:-32,
            // Create a Style that uses the three previous rules
            var style = new OpenLayers.Style(null, {
                rules: [lowRule, middleRule, highRule,oneRule],	
					context: {
						text: function(feature) {
							/*console.log(feature.cluster[0]);*/
							return feature.cluster[0].style.externalGraphic;
							}
					}
					
            });	
	
	
	
	var redlayer = new OpenLayers.Layer.Vector("Pontatlan templomok", {
		protocol: new OpenLayers.Protocol.HTTP({
			url: "pois_red",
			format: new OpenLayers.Format.Text()
		}),
		renderers: ['Canvas','SVG'],
		strategies: [
			new OpenLayers.Strategy.Fixed(),
			/*new OpenLayers.Strategy.AnimatedCluster({
				distance: 55,
				animationMethod: OpenLayers.Easing.Expo.easeOut,
				animationDuration: 10
			})*/
		],
		styleMap:  new OpenLayers.StyleMap(style)
		});
	
	var greenlayer = new OpenLayers.Layer.Vector("Pontos templomok", {
		protocol: new OpenLayers.Protocol.HTTP({
			url: "pois_green",
			format: new OpenLayers.Format.Text()
		}),
		 symbolizer: {
                    fillColor: colors.middle,
                    fillOpacity: 0.9, 
                    strokeColor: colors.middle,
                    strokeOpacity: 0.5,
                    strokeWidth: 12,
                    pointRadius: 15,
                    label: "${count}",
                    labelOutlineWidth: 1,
                    fontColor: "#ffffff",
                    fontOpacity: 0.8,
                    fontSize: "12px"
                },
		renderers: ['Canvas','SVG'],
		strategies: [
			new OpenLayers.Strategy.Fixed()
			<?php
			if(isset($user['id']) OR $user['id'] == '1819502454' OR $user['id'] == '100000577833955' OR $user['id'] == '100000966245096') { }
			else {
			echo ',
			new OpenLayers.Strategy.AnimatedCluster({
				distance: 55,
				threshold: 3,
				animationMethod: OpenLayers.Easing.Expo.easeOut,
				animationDuration: 10
			})';
			}
			?>
		],
		styleMap:  new OpenLayers.StyleMap(style)
		});
		
		map.addLayer(redlayer);
		map.addLayer(greenlayer);
		
		//suggestionlayer.setVisibility(false)
	<?php
	if(isset($_REQUEST['templom'])) {
		
	} else
		echo " greenlayer.setVisibility(false); ";
	?>
	
	var ls = new OpenLayers.Control.LayerSwitcher()
    
	map.addControl(ls);
	ls.maximizeControl();
	
    map.addControl(new OpenLayers.Control.MousePosition())

  

var selectFeature = new OpenLayers.Control.SelectFeature(
    redlayer,
    {
        onSelect:  function(feature) { dragFeatureonComplete.call(feature); }
    }
); 

//dragFeatureonComplete();
  var dragFeatureonComplete = function() {
		document.getElementById("boxinside").innerHTML = "egy pillanat...";
		var feature = this;
  
		
		if(typeof feature.cluster == 'undefined') {
			var tid = feature.attributes.id;
			var marker = feature;
		}
		else if(feature.cluster.length==1) {  
			feature.cluster[0].geometry.move( feature.geometry);
			//feature.geometry.move( feature.geometry);
		
            var tid = feature.cluster[0].attributes.id;;
			var marker = feature.cluster[0];
			
			
		}
		selectedFeature = marker;
		
		if(typeof feature.cluster == 'undefined' || feature.cluster.length==1)  {

		
        if($("#box").css("display") == "none") {			
			/*$( "#box" ).slideDown("fast");*/
			$( "#box" ).show( "slide", { direction: "left" }, 100);
		}
		
		
		$('.message').slideUp("fast");
		
		var json = (function () {
			var json = null;
			$.ajax({
				'async': false,
				'global': false,
				'url': "api.php?q=json&id=" + tid,
				'dataType': "json",
				'success': function (data) {
					json = data;
			}
			});
			return json;
		})(); 
		//console.log(json['templom']);
		var html = '';
		//html = html + "<p><a href='http://miserend.hu/?templom=" + json['templom']['id'] + "' target='_blank'>Ugrás a templom oldalára</a></p>";
		 html = html + "<a href='http://miserend.hu/?templom=" + json['templom']['id'] + "' target='_blank'><h2 style='margin-top:-20px'>" + json['templom']['nev'] + "</a></h2><h4 style='margin-top:-20px'>(";
		 if(json['templom']['ismertnev'] == "") html = html + json['templom']['varos'];
		 else html = html + json['templom']['ismertnev'] ;
		 html = html + ")</h4>";
		 
		 
		 html = html + "Eredeti koordináták: " + json['templom']['lng'] + "; " + json['templom']['lat'];
			var x1 = json['templom']['lng'];
			var y1 = json['templom']['lat']; 
		 //html = html + "<br><br>x: " + feature.geometry.x + " y:" + feature.geometry.y;		
		
		/*console.log(marker.attributes);
		console.log(feature.geometry);*/
		<?php if(isset($user)) { ?> 
		var point = feature.geometry;
		var coord = point.transform(new OpenLayers.Projection("EPSG:900913"),new OpenLayers.Projection("EPSG:4326"));
		
		var numx = new Number(coord.x);
		var numy = new Number(coord.y);
		
		html = html + "<br/>Új koordináták: " + numx.toPrecision(6) + "; " + numy.toPrecision(6) ;
					
		var x2 = coord.x;
		var y2 = coord.y;
		html = html + "<form  id='bekuldForm' method='post' action='index.php'>\n";
		html = html + "<input type='hidden' name='nlat' value='" + coord.y + "'>\n";
		html = html + "<input type='hidden' name='nlng' value='" + coord.x + "'>\n";
		var coord = point.transform(new OpenLayers.Projection("EPSG:4326"),new OpenLayers.Projection("EPSG:900913"));					
		
		html = html + "<input type='hidden' id='tid' name='tid' value='" + json['templom']['id'] + "'>\n";
		html = html + "<input type='hidden' name='checked' id='checked' value='" + json['templom']['checked'] + "'>\n";
		html = html + "<input type='hidden' name='user' value='<?php echo $user['id']; ?>'>\n";
		
		
		// distance - távolság 
		var lat1 = y1 * Math.PI / 180;
		var lat2 = y2 * Math.PI / 180;
		var long1 = x1 * Math.PI / 180;
		var long2 = x2 * Math.PI / 180;
		//document.write(lat1+"##"+lat2);
		var R = 6371; // km
		var d = R * Math.acos(Math.sin(lat1) * Math.sin(lat2) + Math.cos(lat1) * Math.cos(lat2) * Math.cos(long2 - long1)) * 1000;
			formatter = new DecimalFormat("0");
			d = formatter.format(d);
		html = html + "<input type='hidden' name='distance' value='" + d + "'>\n" + "<br/>Hümm, ez így " + d + " méterrel van arrébb.";
		/*console.log(json['templom']);*/
		//html = html + "<br/><input type='submit' id='submitButton' name='hajra' value='Mentsük el ezt a templomot!'>";
		html = html + "</form><div id='bekuldom' style='margin-top:-20px;z-index:10000' onClick='bekuldForm()'  >[Ide kattintva menthetjük az új pozíciót!]</div>";
		<? } ?>
		
		<? if($user['id'] == '1819502454' ) echo '	html = html + "<span id=\'torol\' onClick=\'torol()\' >[Töröld a térképről]</span>"; ';
		?>
		
		html = html + "<div id='miserend'>" +  "</div>";
		document.getElementById("boxinside").innerHTML = html;
		
		$("#miserend").load("miserend.php?templom=" + json['templom']['id']);
	
	} else {
		if($("#box").css("display") == "block") {
			//$( "#box" ).slideUp("fast");
			$( "#box" ).hide( "slide", { direction: "right" }, 100);
		}
	
	}
  }


var dragFeature = new OpenLayers.Control.DragFeature(redlayer, {
    onComplete: function(feature) { dragFeatureonComplete.call(feature); },
	onStart: function(feature) { dragFeatureonComplete.call(feature); }
});

dragFeature.handlers['drag'].stopDown = false; 
dragFeature.handlers['drag'].stopUp = false; 
dragFeature.handlers['drag'].stopClick = false; 
dragFeature.handlers['feature'].stopDown = false; 
dragFeature.handlers['feature'].stopUp = false; 
dragFeature.handlers['feature'].stopClick = false; 

map.addControls([selectFeature,dragFeature]);
selectFeature.activate();
<?php if(isset($user)) { ?> 
dragFeature.activate();
<?php } ?>
var selectFeature = new OpenLayers.Control.SelectFeature(
    greenlayer,
    {
        onSelect: function(feature) { dragFeatureonComplete.call(feature); }
    }
); 
//http://gis.stackexchange.com/questions/24679/creating-2-dragable-layers-at-the-same-time-in-openlayers
var dragFeature = new OpenLayers.Control.DragFeature(greenlayer, {
    onComplete: function(feature) { dragFeatureonComplete.call(feature); },
	onStart: function(feature) { dragFeatureonComplete.call(feature); }
});

dragFeature.handlers['drag'].stopDown = false; 
dragFeature.handlers['drag'].stopUp = false; 
dragFeature.handlers['drag'].stopClick = false; 
dragFeature.handlers['feature'].stopDown = false; 
dragFeature.handlers['feature'].stopUp = false; 
dragFeature.handlers['feature'].stopClick = false; 

map.addControls([selectFeature,dragFeature]);
selectFeature.activate();
<?php if(isset($user)) { ?> 
dragFeature.activate();
<?php } ?>


<? if(isset($user['id'])) { echo ";

";
}
 
	else  {
		echo "map.addControls([selectFeature]);
		selectFeature.activate();";


		}
		?>
<? 
//echo "/* macskaalom ".print_r($_REQUEST,1)."*/";
if(isset($_REQUEST['templom'])) {
	//AND isset($templomok[$_REQUEST['templom']]['lng']) AND isset($templomok[$_REQUEST['templom']]['lat']) ) {
	foreach($templomok as $t) {
		if($t['id'] == $_REQUEST['templom']) {
			echo " var lonLat = new OpenLayers.LonLat( ".$t['lng'].", ".$t['lat'].").transform(";
			$ok = true;
		}
	}
	
	if($ok != true) {
		echo " var lonLat = new OpenLayers.LonLat( 19.465880, 47.254835).transform(";
	}
	
		
} else 
	echo "var lonLat = new OpenLayers.LonLat( 19.465880, 47.254835).transform(";

 ?>    
       new OpenLayers.Projection("EPSG:4326"), // transfom from WGS 1984
       map.getProjectionObject() // to Spherical Mercator Projection
    );
<?php if(isset($_REQUEST['templom'])) { echo " var zoom=18; "; } 
 else { echo " var zoom=8; "; } ?>
    map.setCenter (lonLat, zoom);
</script>