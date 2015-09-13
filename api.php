<?php
include_once('load.php');

ini_set('memory_limit', '256M');
	
if(isset($_REQUEST['datum']) AND preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/',$_REQUEST['datum'])) $datum = $_REQUEST['datum'];
if(isset($_REQUEST['v']) AND preg_match('/^[0-9]{1,3}$/',$_REQUEST['v'])) $v = $_REQUEST['v'];

// Set default timezone
date_default_timezone_set('UTC +1');

if(isset($_REQUEST['q']) and $_REQUEST['q'] == 'report') {
 	
	if(!isset($v) OR $v > 4) { echo json_encode(array('error'=>1,'text'=>'Nem támogatott API verzió.')); exit; } 

	$inputJSON = file_get_contents('php://input');
	$t = $inputJSON;
	$input= json_decode( $inputJSON, TRUE ); //convert JSON into array


	if(!is_array($input)) { echo json_encode(array('error'=>1,'text'=>'Nem kaptunk adatot.')); exit; }
	if(
			!isset($input['tid']) OR !is_numeric($input['tid']) 
			OR !isset($input['pid']) OR !in_array($input['pid'],array(0,1,2)) 
			OR ($input['pid'] == 2 AND !isset($input['text']) )
			OR ( isset($input['timestamp']) AND !is_numeric($timestamp)  )   
		) { echo json_encode(array('error'=>1,'text'=>'Hibás formázású adatot kaptunk.')); exit;  }
	if( $v > 3 AND !isset($input['dbdate'])) { echo json_encode(array('error'=>1,'text'=>'Hiányzik az adatbázis frissítettsége (dbdate).')); exit;  }

	if(!isset($input['text'])) $input['text'] = ""; else $input['text'] = sanitize($input['text']);
	if(!isset($input['email'])) $input['email'] = ""; else $input['email'] = sanitize($input['email']);
	if(isset($input['dbdate']))  $input['dbdate'] = sanitize($input['dbdate']);

	$input['v'] = $v;
	
	$inputJSON = json_encode($input);
	
	$remark = new Remark($input['tid']);
	
	$remark->name = "Mobil felhasználó";
	if($input['email'] != '') $remark->email = $input['email'];
	if(isset($input['timestamp'])) $remark->timestamp = $input['timestamp'];

	$remark->text = "Mobilalkalmazáson keresztül érkezett információ:\n".$input['text']."\n <i>verzió:".$v.", pid:".$input['pid']."</i>";
	if(isset($input['dbdate'])) {
		if(!is_numeric($input['dbdate'])) $input['dbdate'] = strtotime($input['dbdate']);
		$remark->text .= "<i>, adatbázis: ".date("Y-m-d H:i", $input['dbdate'])."</i>";
		$templom = getchurch($input['tid']);
		$updated = strtotime($templom['frissites']);
		
		if($input['dbdate'] < $updated) {
			//echo date('Y-m-d',$updated)."-".date('Y-m-d',$input['dbdate']);	
			$remark->text .= "\n\n<strong>Figyelem! Elavult adatok alapján történt a bejelentés!</strong>";
		}
	}
	$remark->save();
	$remark->emails();
	echo json_encode(array('error'=>0,'text'=>'Köszönjük. Elmentettük.'));

	exit;
} 
 

if(isset($_REQUEST['q']) and $_REQUEST['q'] == 'updated') {
	if(!isset($datum)) { die('Nincs \'datum\' megadva.'); }
	if(!isset($v) OR ( $v < 2 AND $v > 4)) { die('Ez az API verzió ezt nem támogatja.'); }	
		
	$query = "SELECT id, moddatum FROM templomok WHERE  moddatum >= '".$datum."' ";											
    $result = mysql_query($query);
    //echo "-".print_r($result,1)."-";
    if(mysql_num_rows($result) > 0) echo "1";
    else echo "0";
	exit;
} 

if(isset($_REQUEST['q']) and $_REQUEST['q'] == 'table') { 


	if(isset($_REQUEST['table']) AND in_array($_REQUEST['table'],array('templomok'))) {

		if( $v < 3 ) {
			echo json_encode(array('error'=>'Ez a funkció csak v3-tól érhető el.'));
			exit;
		}

		$inputJSON = file_get_contents('php://input');
		$t = $inputJSON;
		$input= json_decode( $inputJSON, TRUE ); //convert JSON into array

		if(!isset($input['delimiter'])) $input['delimiter'] = ';';
		if(!isset($input['format'])) $input['format'] = 'json'; //or 'text'
		$input['table'] = $_REQUEST['table'];
		
		if($input['table'] == 'templomok') {
			if(!isset($input['columns']) OR !is_array($input['columns'])) 
				$input['columns'] = array('id','nev','url','lng','lat');
			
			$query = "
				SELECT t.*,orszagok.nev as orszag, megye.megyenev as megye,lat,lng, checked FROM templomok as t 
						LEFT JOIN orszagok ON orszagok.id = t.orszag 
						LEFT JOIN megye ON megye.id = megye 
						LEFT JOIN terkep_geocode ON terkep_geocode.tid = t.id 
						WHERE t.ok = 'i' 
						LIMIT 10000";
		}
		
		$output = array();
		$result = mysql_query($query);    
		while($row =  mysql_fetch_assoc($result)) {
			$tmp = array();
			foreach ($input['columns'] as $column) {
				// data in mysql
				if(isset($row[$column]) AND in_array($column,array('id','nev','ismertnev','turistautak','orszag','megye','varos','cim','megkozelites','plebania','pleb_url','pleb_eml','egyhazmegye','espereskerulet','leiras','megjegyzes','miseaktiv','misemegj','bucsu','frissites','lat','lng','checked'))) {
					 $tmp[$column] = $row[$column];
				}
				// simple data mapping
				$mapping = array('name'=>'nev','alt_name'=>'ismertnev','lon'=>'lng');
				if (array_key_exists($column, $mapping)) {
					$tmp[$column] =  $row[$mapping[$column]];	
				}
				//extra mapping
				switch ($column) {
					case 'denomination':
						//http://wiki.openstreetmap.org/wiki/Key:denomination#Christian_denominations
						if(in_array($row['egyhazmegye'],array(17,18))) {
							$tmp[$column] = 'greek_catholic';
						} else {
							$tmp[$column] = 'roman_catholic';
						}
						break;

					case 'url':
						$tmp[$column] = 'http://miserend.hu/?templom='.$row['id'];
						break;
					
					default:
						# code...
						break;
				}
			}
			$output[] = $tmp;
		}
		
		if($input['format'] == 'json') {
			//JSON output
			echo json_encode(array('error'=>0,$input['table']=>$output));	
		
		} elseif ($input['format'] == 'text') {
			//SIMPLE table output
			//TODO: a szöveg nem tartalmazhatja az elválasztó karaktert, különben gond van.
			foreach ($output as $key => $row) {
				echo implode($input['delimiter'],$row)."<br/>\n";
			}
		}

	} else {
		echo json_encode(array('error'=>'table nélkül mit érek én?'));
	
	}
}

if(isset($_REQUEST['q']) and $_REQUEST['q'] == 'json') { 
	if(isset($_REQUEST['id']) AND is_numeric($_REQUEST['id'])) {

	if( $v < 4 ) {
		echo json_encode(array('error'=>'Ez a funkció csak v3 fölött érhető el.'));
		exit;
	}

	$query = "
			SELECT t.*,orszagok.nev as orszag, megye.megyenev as megye,lat,lng, checked FROM templomok as t 
					LEFT JOIN orszagok ON orszagok.id = t.orszag 
					LEFT JOIN megye ON megye.id = megye 
					LEFT JOIN terkep_geocode ON terkep_geocode.tid = t.id 
					WHERE t.id = ".$_REQUEST['id']." 
					LIMIT 1";
	$result = mysql_query($query);    
	$templomok =  mysql_fetch_array($result);

	unset($templomok['log']);
	$return['templom'] = $templomok;
					
	$query = "
			SELECT * FROM misek 
					WHERE torles = '0000-00-00 00:00:00' 
					AND tid = ".$_REQUEST['id']."
					LIMIT ".$limit;
	$result = mysql_query($query);    
	$return['misek'] = array();
	while(($mise = mysql_fetch_array($result))) {
		$return['misek'][] = $mise;
	}
					
	//echo "<pre>".print_r($return,1);
	echo json_encode($return);
	} else {
		echo json_encode(array('error'=>'id nélkül mit érek én?'));
	
	}
}
  
if(isset($_REQUEST['q']) and $_REQUEST['q'] == 'sqlite') {

	$sqllitefile = 'fajlok/sqlite/miserend_v'.$v.'.sqlite3';
    if (file_exists($sqllitefile) && strtotime("-20 hours") < filemtime($sqllitefile) AND $config['debug'] == 0 AND !isset($datum)) {
        header("Location: /".$sqllitefile);
        exit;
    }

    if(generateSqlite($v,'fajlok/sqlite/miserend_v'.$v.'.sqlite3')) {
		//Sajnos ez itten nem működik... Nem lesz szépen letölthető.  Headerrel sem
	    //$data = readfile($sqllitefile); exit($data);
		header("Location: /".$sqllitefile);
		die();
    }
    else {
    	die('Hiba történt az sqlite3 fájl léterhozása közben. Elnézést.');

    }
}

?>