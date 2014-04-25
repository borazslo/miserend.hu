<?php
include'db.php';

db_query('UPDATE terkep_geocode SET checked = 0 WHERE tid = 5018 LIMIT 1');

echo "<pre>";
$suggests = db_query('SELECT * FROM terkep_geocode_suggestion ORDER BY stime DESC');
foreach($suggests as $s) {
	//echo "<a href='https://www.facebook.com/profile.php?id=".$s['uid']."'>".str_pad($s['uid'], 16)."</a> - <a href='http://miserend.hu/?templom=".$s['tid']."'>".str_pad($s['tid'],4)."</a> ".$s['stime']."\n";
}
$file = 'jelentesek.txt';
$current = file_get_contents($file);
foreach(preg_split("/((\r?\n)|(\r\n?))/", $current) as $line){
	$vars = json_decode($line);
	if(!$vars->timestamp) $vars->timestamp = '0000-00-00 00:00:00';
	$vars->type = 'jelent';
	$logs[] = $vars;	
}

$file = 'stats.txt';
$current = file_get_contents($file);
foreach(preg_split("/((\r?\n)|(\r\n?))/", $current) as $line){
	$vars = json_decode($line);
	if(!$vars->timestamp) $vars->timestamp = '0000-00-00 00:00:00';
	$vars->type = 'letolt';
	$logs[] = $vars;	
}


// Obtain a list of columns
foreach ($logs as $key => $row) {
    $timestamp[$key]  = $row->timestamp;
    $version[$key] = $row->version;
}

// Sort the data with volume descending, edition ascending
// Add $data as the last parameter, to sort by the common key
array_multisort($timestamp, SORT_DESC, $version, SORT_ASC, $logs);

foreach($logs as $log) {
	echo $log->timestamp." ".$log->type." v".$log->v." -> ";
	unset($log->timestamp); unset($log->v); unset($log->type);
	foreach($log as $l=>$g) echo $l.":'".$g."' ";
	echo "\n";
}
 
 //echo "<pre>"; print_R($logs);
?>