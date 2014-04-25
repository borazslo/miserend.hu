<html>
<head>

    <script class="include" type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
	<link class="include" rel="stylesheet" type="text/css" href="library/jqplot/jquery.jqplot.min.css" />

	<script class="include" type="text/javascript" src="library/jqplot/jquery.jqplot.min.js"></script>
    
<!-- Additional plugins go here -->

  <script class="include" type="text/javascript" src="library/jqplot/plugins/jqplot.barRenderer.min.js"></script>
  <script class="include" type="text/javascript" src="library/jqplot/plugins/jqplot.pieRenderer.min.js"></script>
  <script class="include" type="text/javascript" src="library/jqplot/plugins/jqplot.categoryAxisRenderer.min.js"></script>
  <script type="text/javascript" src="library/jqplot/plugins/jqplot.dateAxisRenderer.min.js"></script>
<script type="text/javascript" src="library/jqplot/plugins/jqplot.canvasTextRenderer.min.js"></script>
<script type="text/javascript" src="library/jqplot/plugins/jqplot.canvasAxisTickRenderer.min.js"></script>


  <script class="include" type="text/javascript" src="library/jqplot/plugins/jqplot.pointLabels.min.js"></script>


</head>
<body>
<?php
include 'db.php';

//echo "<pre>".print_R(db_query('SELECT * FROM templomok LIMIT 10'),1)."</pre>";
//echo "<pre>".print_R(db_query('SHOW TABLES'),1)."</pre>";
$d1 = new DateTime();
$limit1 = '12';
	$limit2 = '36';
$templomok = db_query('SELECT templomok.id, templomok.varos, templomok.nev, frissites, egyhazmegye, egyhazmegye.nev as egyhazmegyenev FROM templomok LEFT JOIN egyhazmegye ON egyhazmegye.id = egyhazmegye WHERE templomok.ok = "i" ORDER BY frissites ASC LIMIT 10000',1);
foreach($templomok as $templom) {
	$honap = date('Y-m',strtotime($templom['frissites']));
	if(isset($honapok[$honap])) $honapok[$honap]++;
	else $honapok[$honap] = 1;

	$d2 = new DateTime($honap."-01");
	
	$diff = $d1->diff($d2)->m + ($d1->diff($d2)->y*12); 
	
	
	if(!isset($megyek1[$templom['egyhazmegye']])) $megyek1[$templom['egyhazmegye']] = 0;
	if(!isset($megyek2[$templom['egyhazmegye']])) $megyek2[$templom['egyhazmegye']] = 0;
	if(!isset($megyek3[$templom['egyhazmegye']])) $megyek3[$templom['egyhazmegye']] = 0;
	if(!isset($megyek[$templom['egyhazmegye']])) $megyek[$templom['egyhazmegye']] = "'".$templom['egyhazmegyenev']."'";
	
	if($diff < $limit1) {
		
		$megyek1[$templom['egyhazmegye']]++;
	}elseif($diff < $limit2) {
		
		$megyek2[$templom['egyhazmegye']]++;
	}else {
	
		$megyek3[$templom['egyhazmegye']]++;
	}
}

//print_R($megyek); exit;
foreach(array('honapok','megyek1','megyek2','megyek3','megyek') as $array) {
	ksort($$array);
	reset($$array);
}
$d1 = strtotime(key($honapok));
$d2 = time();
$min_date = min($d1, $d2);
$max_date = max($d1, $d2);
$i = 0;
while (($min_date = strtotime("+1 MONTH", $min_date)) <= $max_date) {
    $i++;
}
$max = $i;
for($c=0;$c<=$max;$c++) {
	$month = date('Y-m',strtotime(" -".($max - $c)." month"));
	$tickss[] = "'".$month."'";
	if(isset($honapok[$month])) $s1s[] = $honapok[$month]; else $s1s[] = 0;
			
}
$s1 = implode(',',$s1s);
$ticks = implode(',',$tickss);

$megyek1 = implode(',',$megyek1);
$megyek2 = implode(',',$megyek2);
$megyek3 = implode(',',$megyek3);
//echo"<pre>";print_R($honapok);


?>
<script defer="defer" type="text/javascript">
$(document).ready(function(){
        $.jqplot.config.enablePlugins = true;
        var s1 = [<?php echo $s1; ?> ];
        var ticks = [<?php echo $ticks; ?>];
         
        plot1 = $.jqplot('chart1', [s1], {
            // Only animate if we're not using excanvas (not in IE 7 or IE 8)..
            title: 'A miserendek frissítettsége',
			animate: !$.jqplot.use_excanvas,
			seriesColors: [ "#996600", "#ccc", "#999"],
            seriesDefaults:{
                renderer:$.jqplot.BarRenderer,
				rendererOptions: {
					// Set varyBarColor to tru to use the custom colors on the bars.
					varyBarColor: true
				},
                pointLabels: { show: true },
				rendererOptions: {
					barPadding: 1,      // number of pixels between adjacent bars in the same
                                // group (same category or bin).
					barMargin: 2,      // number of pixels between adjacent groups of bars.
					barDirection: 'vertical', // vertical or horizontal.
					barWidth: null,     // width of the bars.  null to calculate automatically.
					shadowOffset: 0,    // offset from the bar edge to stroke the shadow.
					shadowDepth: 0,     // nuber of strokes to make for the shadow.
					shadowAlpha: 0.8,   // transparency of the shadow.
				}
            },
			axesDefaults: {
				tickRenderer: $.jqplot.CanvasAxisTickRenderer ,
				tickOptions: {
					angle: -90,
					fontSize: '10pt'
				}
			},
            axes: {
                xaxis: {
                    renderer: $.jqplot.CategoryAxisRenderer,
                    ticks: ticks
                }
            },
			
            highlighter: { show: true }
        });
     
        $('#chart1').bind('jqplotDataClick', 
            function (ev, seriesIndex, pointIndex, data) {
                $('#info1').html('series: '+seriesIndex+', point: '+pointIndex+', data: '+data);
            }
        );
    });
</script>
<!--<p><strong>A miserend.hu frissítettsége:</strong></p>-->
<!--Infó: <span id="info1"></span>-->
<div id="chart1" style="height:400px;width:100%; "></div>

<script defer="defer" type="text/javascript">
$(document).ready(function(){
  var megyek1 = [<?php echo $megyek1; ?>];
  var megyek2 = [<?php echo $megyek2; ?>];
  var megyek3 = [<?php echo $megyek3; ?>];
  var ticks = [<?php echo implode(',',$megyek); ?>];
  plot3 = $.jqplot('megyek', [megyek1, megyek2, megyek3], {
	title: 'A miserendek frissítettsége megyénként',
    // Tell the plot to stack the bars.
    stackSeries: true,
    captureRightClick: true,
    seriesDefaults:{
      renderer:$.jqplot.BarRenderer,
      rendererOptions: {
          // Put a 30 pixel margin between bars.
          barMargin: 30,
          // Highlight bars when mouse button pressed.
          // Disables default highlighting on mouse over.
          highlightMouseDown: true   
      },
      pointLabels: {show: true}
    },
	axesDefaults: {
				tickRenderer: $.jqplot.CanvasAxisTickRenderer ,
				tickOptions: {
					angle: -40,
					fontSize: '10pt',
					mark: 'inside',
				}
			},
    axes: {
      xaxis: {
          renderer: $.jqplot.CategoryAxisRenderer,
		  ticks: ticks  
      },
      yaxis: {
        // Don't pad out the bottom of the data range.  By default,
        // axes scaled as if data extended 10% above and below the
        // actual range to prevent data points right on grid boundaries.
        // Don't want to do that here.
        padMin: 0,	
      }
    },
	 series:[
            {label:'Friss'},
            {label:'Egy-két éves adatok'},            
			{label:'Réges-régiek'},
        ],
    legend: {
      show: true,
      location: 'n',
      placement: 'inside',
	  
    }      
  });
  // Bind a listener to the "jqplotDataClick" event.  Here, simply change
  // the text of the info3 element to show what series and ponit were
  // clicked along with the data for that point.
  $('#megyek').bind('jqplotDataClick', 
    function (ev, seriesIndex, pointIndex, data) {
      $('#megyek_info').html('series: '+seriesIndex+', point: '+pointIndex+', data: '+data);
    }
  ); 
});
</script>
<div id="megyek" style="height:500px;width:100%; "></div>
<div id="megyek_info" "></div>

<!-- -->
<h3>Legrégebben frissített templomaink</h3>
<?php 
$c = 0;
foreach($templomok as $templom) {
	echo $templom['frissites']." <a href=\"http://miserend.hu/?templom=".$templom['id']."\">".$templom['nev']." (".$templom['varos'].")</a><br/>";
	//echo print_R($templom,1)."<br>";
	
	if($c>10) break;
	$c++;
} ?>

<?php 
$c = 0;
echo "<br><br>";
if($_REQUEST['limit'] ) $limit = $_REQUEST['limit']; else $limit = 10;
foreach($megyek as $megye => $nev) {
	
	echo "<strong>".$nev."</strong><br>";
	$c = 0;
foreach($templomok as $templom) {
	if($templom['egyhazmegye'] == $megye) {
	echo $templom['frissites']." <a href=\"http://miserend.hu/?templom=".$templom['id']."\">".$templom['nev']." (".$templom['varos'].")</a><br/>";
	//echo print_R($templom,1)."<br>";
	
	if($c>$limit) break;
	$c++;
	}
} 
echo "<br>";
}

?>

<br/><br/>
<?
$suggestions = db_query('SELECT stime FROM terkep_geocode_suggestion LIMIT 100000');

foreach($suggestions as $suggestion) {
	$nap = date('Y-m-d',strtotime($suggestion['stime']));
	if(isset($napok[$nap])) $napok[$nap]++;
	else $napok[$nap] = 1;
}
ksort($napok);
reset($napok);
$d1 = strtotime(key($napok));
$d2 = time();
$min_date = min($d1, $d2);
$max_date = max($d1, $d2);
$i = 0;
while (($min_date = strtotime("+1 DAY", $min_date)) <= $max_date) {
    $i++;
}
$max = $i;
for($c=0;$c<=$max;$c++) {
	$day = date('Y-m-d',strtotime(" -".($max - $c)." day"));
	$ticks2s[] = "'".$day."'";
	if(isset($napok[$day])) $s2s[] = $napok[$day]; else $s2s[] = 0;
}
$s2 = implode(',',$s2s);
$ticks2 = implode(',',$ticks2s);
//echo"<pre>";print_R($napok);


?>
<script defer="defer" type="text/javascript">
$(document).ready(function(){
        $.jqplot.config.enablePlugins = true;
        var s2 = [<?php echo $s2; ?> ];
        var ticks2 = [<?php echo $ticks2; ?>];
         
        plot2 = $.jqplot('chart2', [s2], {
            // Only animate if we're not using excanvas (not in IE 7 or IE 8)..
            title: 'Templomtologatások',
			animate: !$.jqplot.use_excanvas,
			seriesColors: [ "#996600", "#ccc", "#999"],
            seriesDefaults:{
                renderer:$.jqplot.BarRenderer,
				rendererOptions: {
					// Set varyBarColor to tru to use the custom colors on the bars.
					varyBarColor: true
				},
                pointLabels: { show: true },
				rendererOptions: {
					barPadding: 1,      // number of pixels between adjacent bars in the same
                                // group (same category or bin).
					barMargin: 2,      // number of pixels between adjacent groups of bars.
					barDirection: 'vertical', // vertical or horizontal.
					barWidth: null,     // width of the bars.  null to calculate automatically.
					shadowOffset: 0,    // offset from the bar edge to stroke the shadow.
					shadowDepth: 0,     // nuber of strokes to make for the shadow.
					shadowAlpha: 0.8,   // transparency of the shadow.
				}
            },
			axesDefaults: {
				tickRenderer: $.jqplot.CanvasAxisTickRenderer ,
				tickOptions: {
					angle: -90,
					fontSize: '10pt'
				}
			},
            axes: {
                xaxis: {
                    renderer: $.jqplot.CategoryAxisRenderer,
                    ticks: ticks2
                }
            },
			
            highlighter: { show: true }
        });
     
        $('#chart2').bind('jqplotDataClick', 
            function (ev, seriesIndex, pointIndex, data) {
                $('#info2').html('series: '+seriesIndex+', point: '+pointIndex+', data: '+data);
            }
        );
    });
</script>
<!--Infó: <span id="info2"></span>-->
<div id="chart2" style="height:400px;width:100%; "></div>
</body>
</html>