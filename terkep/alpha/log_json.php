<?php 
if($_REQUEST['pw'] == 'nemTudom') {

	$handle = @fopen("search.log", "r");
	$return = array();
	if ($handle) {
		$c = 0;
		while (($buffer = fgets($handle, 4096)) !== false) {
			$log = json_decode($buffer);
			if($log) {
				$log->c = $c++;
				$return[$log->time] = $log;
			}
		}
		if (!feof($handle)) {
			echo "Error: unexpected fgets() fail\n";
		}
		fclose($handle);
	}	
	krsort($return);
	
	
	if(!array_key_exists('json', $_REQUEST)) {
		echo"<table>";
	
		foreach($return as $r) {
	
			echo"<tr><td>".$r->c.".</td><td>".date('Y.m.d. \<\b\>H:i:s\<\/\b\>',$r->time)."</td>
	             <td><a href='https://maps.google.com/maps?q=".$r->request->lat."+".$r->request->lng."'>".$r->request->lat.";".$r->request->lng."</a></td>
				 <td>".$r->request->search."</td><td>";
			if(property_exists($r, 'error'))  echo $r->error;
			echo "</td><td><strong>".$r->count."</strong></td></tr>";
		
		}
		echo"</table>";
	
	}
	else echo json_encode($return);
	
}
?>