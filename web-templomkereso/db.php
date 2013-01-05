<?php
function db_connect() {
	
	$user="xxx";
	$password="xxx";
	$database="vp_portal";
	$db_link = mysql_connect('localhost:3306',$user,$password) or die ("Can't connect to mysql");
	mysql_set_charset('utf8');
	if ($db_link) @mysql_select_db($database);
	return $db_link;
}

function db_close() {
	if(isset($db_link)) { $result = mysql_close($db_link);
	return $result;
	}
}

function db_query($query,$debug = '',$return = '') {
	if($debug == 'x' or $debug == '') $debug = 0;
	 /* 
	  * Debug 1 -> ha hiba van jelzi
	  * Debug 2 -> mindenképp közöl valamit
	  * FIGYELEM! a debug 2 kilövi az ajax_framewokr.js-t mert a mysqltoxml.php nem xml válasza miatt!
	  *
	  */
	db_connect();
	if(!($result = mysql_query($query))) $error = mysql_errno().": ".mysql_error()." (<i>$query</i>)\n<br>";
	
	if($debug==1 and isset($error)) echo $error;
	elseif($debug==2 and !isset($error)) echo $query."<br>\n";
	elseif($debug==2) echo $error;

	//FIXIT: insert eset�n nem megy a fetch, de akkor nincs error hadling;
	if(is_bool($result)) return;
	$rows = array();
	while(($row = mysql_fetch_array($result,  MYSQL_ASSOC))) {
		foreach($row as $k => $i) {
			//$row[$k] = iconv("ISO-8859-1", "UTF-8", $i);
			$row[$k] = $i;
		}
		$rows[] = $row;
	}
	if($rows!=array()) return $rows;
	
	//echo "++".mysql_affected_rows()."++";
	/*
	 * Ezt itten kivételeztem.
	 */
	//if(!isset($error) AND isset($return)) return $return();
	db_close();
	if(isset($error)) return false;
	else return true;

	}
	
	
function setvar($name,$value) {
	$test = getvar($name);
	if( $test == false) {
		$query="INSERT INTO vars (name, value) VALUES ('$name','$value')";
	} else {	
		$query='UPDATE vars SET value = \''.$value.'\' WHERE name = \''.$name.'\'';
	}
	db_query($query);
}

function getvar($name) {
	$query="SELECT * FROM vars WHERE name = '".$name."' LIMIT 0,1";
	$result = db_query($query);
	
	if(!$result) return false; 
	return $result[0]['value'];
}
?>