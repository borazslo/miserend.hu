<?php
 ini_set('memory_limit', '256M');
	$limit = 1650000;
	
	if(isset($_REQUEST['datum']) AND preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/',$_REQUEST['datum'])) $datum = $_REQUEST['datum'];
	if(isset($_REQUEST['v']) AND preg_match('/^[0-9]{1,3}$/',$_REQUEST['v'])) $v = $_REQUEST['v'];



	include_once('db.php');
  // Set default timezone
  date_default_timezone_set('UTC +1');
if(isset($_REQUEST['q']) and $_REQUEST['q'] == 'json') { 
	if(isset($_REQUEST['id']) AND is_numeric($_REQUEST['id'])) {
	
	$query = "
			SELECT t.*,orszagok.nev as orszag, megye.megyenev as megye,lat,lng, checked FROM templomok as t 
					LEFT JOIN orszagok ON orszagok.id = t.orszag 
					LEFT JOIN megye ON megye.id = megye 
					LEFT JOIN terkep_geocode ON terkep_geocode.tid = t.id 
					WHERE t.id = ".$_REQUEST['id']." 
					LIMIT 1";    
	$templomok = db_query($query);
	unset($templomok[0]['log']);
	$return['templom'] = $templomok[0];
					
	$query = "
			SELECT * FROM misek 
					WHERE torles = '0000-00-00 00:00:00' 
					AND templom = ".$_REQUEST['id']."
					LIMIT ".$limit;
	$misek = db_query($query);
	//echo "<pre>".print_r($misek,1)."</pre>";
	
	$return['misek'] = $misek;
					
	//echo "<pre>".print_r($return,1);
	echo json_encode($return);
	} else {
		echo json_encode(array('error'=>'id nélkül mit érek én?'));
	
	}
}
  
if(isset($_REQUEST['q']) and $_REQUEST['q'] == 'sqlite') {
	if(!isset($v)) {
		die('Kérlek adj meg egy verziót, hogy biztosan kapj eredményt!');	
	}
	elseif($v > 3) exit;
 
	$sqllitefile = 'sqlite/miserend_v'.$v.'.sqlite3';
	if (file_exists($sqllitefile) && strtotime("-1 day") < filemtime($sqllitefile)) {
		include($sqllitefile);
		
		$file = 'stats.txt';
		// Open the file to get existing content
		$current = file_get_contents($file);
		// Append a new person to the file
		$current .= json_encode(array('timestamp'=>date('Y-m-d H:i:s'),'v'=>$v,'cached'=>'true'))."\n";
		// Write the contents back to the file
		file_put_contents($file, $current);
		exit;
	}
 
  try {
    /**************************************
    * Create databases and                *
    * open connections                    *
    **************************************/
 
    // Create (connect to) SQLite database in file
    $file_db = new PDO('sqlite:sqlite/miserend_v'.$v.'.sqlite3');
    // Set errormode to exceptions
    $file_db->setAttribute(PDO::ATTR_ERRMODE, 
                            PDO::ERRMODE_EXCEPTION);
 
 
	$file_db->exec("DROP TABLE IF EXISTS templomok");
	$file_db->exec("DROP TABLE IF EXISTS misek");
	$file_db->exec("DROP TABLE IF EXISTS kepek");
 
    /**************************************
    * Create tables                       *
    **************************************/
	
    // Create table templomok
	$createtabletemplomok = "CREATE TABLE IF NOT EXISTS [templomok] (
			[tid] INTEGER  NOT NULL PRIMARY KEY,
			[nev] VARCHAR(200)  NULL,
			[ismertnev] vaRCHAR(200)  NULL,";

	if($v > 2) $createtabletemplomok .= "
			[gorog] INTEGER NULL,";
			
	$createtabletemplomok .= "
			[orszag] vARCHAR(30)  NULL,
			[megye] vARCHAR(80)  NULL,
			[varos] vaRCHAR(80)  NULL,
			[cim] vARCHAR(255)  NULL,
			[geocim] vARCHAR(255)  NULL,
			[megkozelites] vARCHAR(255)  NULL,
			[lng] fLOAT  NULL,
			[lat] flOAT  NULL,
			[nyariido] vARCHAR(10)  NULL,
			[teliido]vARCHAR(10)  NULL,
			[kep] vARCHAR(255)  NULL
		
		)";
    $file_db->exec($createtabletemplomok);
 
	// Create table misek
	$createtabletemplomok = "CREATE TABLE IF NOT EXISTS [misek] (
			[mid] INTEGER  PRIMARY KEY NOT NULL,
			[tid] iNTEGER  NULL,
			[telnyar] VARCHAR(1)  NULL,
			[nap] inTEGER  NULL,
			[ido] TIME  NULL,
			[nyelv] VARCHAR(3)  NULL,
			[milyen] VARCHAR(10)  NULL";

	if($v > 2) $createtabletemplomok .= "
			, [megjegyzes] VARCHAR(255) NULL";
	$createtabletemplomok .= "	)";
    $file_db->exec($createtabletemplomok);
 
	
    if($v > 1) {
	// Create table kepek
	$file_db->exec("CREATE TABLE IF NOT EXISTS [kepek] (
			[kid] INTEGER  PRIMARY KEY NOT NULL,
			[tid] INTEGER  NULL,
			[kep] vARCHAR(255)  NULL
		)");
	}
    /**************************************
    * Set initial data                    *
    **************************************/
 
    // mysql select templomok             
	$query = "
			SELECT t.*,orszagok.nev as orszag, megye.megyenev as megye,lat,lng, address2 as geocim FROM templomok as t 
					LEFT JOIN orszagok ON orszagok.id = t.orszag 
					LEFT JOIN megye ON megye.id = megye 
					LEFT JOIN terkep_geocode ON terkep_geocode.tid = t.id ";					
	if(isset($datum)) $query .= ' WHERE  moddatum >= "'.$datum.'" ';											
	$query .= " LIMIT ".$limit;
	//echo $query."<br>";
   $templomok = db_query($query);
	//echo "<pre>".print_r($templomok,1);
	
	// mysql select kepek
	$query = "SELECT * 
			FROM  `kepek` 
			WHERE kat =  'templomok'
			ORDER BY kid, kiemelt, sorszam DESC , id ASC ";
			//AND kiemelt =  'i'
   $tmp = db_query($query);
   //echo"<pre>"; print_r($tmp);
   $kiemeltkepek = array();
   $kepek = array();
   foreach($tmp as $kep) {
		if(!isset($kiemeltkepek[$kep['kid']])) { // AND $kep['kiemelt'] == 'i')
			$kiemeltkepek[$kep['kid']] = $kep;
			/*if($kep['kiemelt'] != 'i') { 
				echo 'jaj - <a href="http://www.miserend.hu/?templom='.$kep['kid'].'">'.$kep['kid'].'</a><br/>'; 
				echo $c++;
				}*/
			}
		$kepek[] = $kep;
	}
	//echo "<pre>".print_r($kepek,1);
	
	
	// mysql select misek             
	$query  = "
			SELECT * FROM misek 
					WHERE torles = '0000-00-00 00:00:00' 
					AND templom <> 0";
	if(isset($datum)) $query .= '  AND moddatum >= "'.$datum.'" ';															
	$query .= " LIMIT ".$limit;
	//echo $query;
   $misek = db_query($query);
	//echo "<pre>".print_r($misek,1)."</pre>";
 

    /**************************************
    * Play with databases and tables      *
    **************************************/
	
    // Loop thru all messages and execute prepared insert statement
	$file_db->beginTransaction();
	if(is_array($templomok))
    foreach ($templomok as $templom) {
	  //echo"<pre>"; print_R($misek); exit;
	  set_time_limit(60);
      // Set values to bound variables
	  $tid = $templom['id'];
      $nev = $templom['nev'];
      $ismertnev = $templom['ismertnev'];
	  if($v > 2) {
		if(in_array($templom['egyhazmegye'],array(18,17))) { //Görög egyházmegyék kódja
			$gorog = 1;
		} else $gorog = 0;
	  
	  }
	  $orszag = $templom['orszag'];
	  $megye = $templom['megye'];
	  $varos = $templom['varos'];
	  $cim = $templom['cim'];
	  $geocim = $templom['geocim'];
	  $lng = $templom['lng'];
	  $lat = $templom['lat'];
	  $nyariido = $templom['nyariido'];
	  $teliido = $templom['teliido'];
	  if(isset($kiemeltkepek[$tid])) $kep = "http://miserend.hu/kepek/templomok/".$tid."/".$kiemeltkepek[$tid]['fajlnev'];
	  else { $kep = ''; }
	  
	  
	  
	  if(!checkRemoteFile($kep)) $kep = '';
	  $megkozelites = '..'; //$templom['megkozelites']; 
 
		foreach(array('ismertnev','cim') as $var) {
			//if(preg_match("/'/",$$var)) echo $nev." (".$tid."): ".$$var."<br>\n";
			$$var = preg_replace("/'/","",$$var);
		}
 
	if($v > 2) {
	  $insert = "INSERT INTO templomok (tid, nev, ismertnev, gorog, orszag, megye, varos, cim, lng, lat,nyariido,teliido,megkozelites,geocim,kep) 
                VALUES ('".$tid."','".$nev."','".$ismertnev."','".$gorog."','".$orszag."','".$megye."','".$varos."','".$cim."','".$lng."','".$lat."','".$nyariido."','".$teliido."','".$megkozelites."','".$geocim."','".$kep."')";
	
	}
	else {
		
	$insert = "INSERT INTO templomok (tid, nev, ismertnev, orszag, megye, varos, cim, lng, lat,nyariido,teliido,megkozelites,geocim,kep) 
                VALUES ('".$tid."','".$nev."','".$ismertnev."','".$orszag."','".$megye."','".$varos."','".$cim."','".$lng."','".$lat."','".$nyariido."','".$teliido."','".$megkozelites."','".$geocim."','".$kep."')";
				
	}
	
      // Execute statement
      $file_db->query($insert);
    }
	$file_db->commit();
	//echo "Templomok feltöltve<br>\n";
 
    
	// Loop thru all messages and execute prepared insert statement
	$file_db->beginTransaction();
	if(is_array($misek))
    foreach ($misek as $mise) {
	  set_time_limit(60);
      // Set values to bound variables
      $mid = $mise['id'];
      $tid = $mise['templom'];
	  $telnyar = $mise['idoszamitas'];
	  $nap = $mise['nap'];
	  $ido = $mise['ido'];
	  $nyelv = $mise['nyelv'];
	  $milyen = $mise['milyen'];
	  $megjegyzes = $mise['megjegyzes'];

	  if($v > 2) { echo $insert."<br>\n";
	  $insert = "INSERT INTO misek (mid, tid, telnyar, nap, ido, nyelv, milyen, megjegyzes) 
                VALUES ('".$mid."','".$tid."','".$telnyar."','".$nap."','".$ido."','".$nyelv."','".$milyen."','".$megjegyzes."')";
	  } else {
	  $insert = "INSERT INTO misek (mid, tid, telnyar, nap, ido, nyelv, milyen) 
                VALUES ('".$mid."','".$tid."','".$telnyar."','".$nap."','".$ido."','".$nyelv."','".$milyen."')";
      // Execute statement
	  }
      $file_db->query($insert);
    }
	$file_db->commit();
	//echo "Misék feltöltve<br>\n";
	
	// Loop thru all messages and execute prepared insert statement
	if($v > 1) {
	$file_db->beginTransaction();
	//$c = 1565465465465;
	if(is_array($kepek)) 
    foreach ($kepek as $kep) {
	  set_time_limit(60);
      // Set values to bound variables
      $kid = $kep['id']; //Nem megy, mert nem unique.
      $tid = $kep['kid'];
	  $url = "http://miserend.hu/kepek/templomok/".$kep['kid']."/".$kep['fajlnev'];
	  $insert = "INSERT INTO kepek (kid, tid, kep) 
                VALUES ('".$kid."','".$tid."','".$url."')";
      // Execute statement
	  if(checkRemoteFile($url))
		$file_db->query($insert);
		//else echo $kid." - ".$tid." - /".$kep['kid']."/".$kep['fajlnev'].";<br/>";
    } 
	$file_db->commit();
	//echo "Képek feltöltve<br>\n";
	}
	
    // Select all data from file db messages table 
    //$result = $file_db->query('SELECT * FROM misek');
    //foreach ($result as $m) { echo"<pre>".print_r($m,1);   }
 
    /**************************************
    * Close db connections                *
    **************************************/
 
    // Close file db connection
    $file_db = null;
	
	$file = 'stats.txt';
	// Open the file to get existing content
	$current = file_get_contents($file);
	// Append a new person to the file
	$current .= json_encode(array('timestamp'=>date('Y-m-d H:i:s'),'v'=>$v))."\n";
	// Write the contents back to the file
	file_put_contents($file, $current);
	
	
	echo file_get_contents($sqllitefile, FILE_USE_INCLUDE_PATH);
  }
  catch(PDOException $e) {
    // Print PDOException message
    echo $e->getMessage();
  }
}

function checkRemoteFile($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$url);
    // don't download content
    curl_setopt($ch, CURLOPT_NOBODY, 1);
    curl_setopt($ch, CURLOPT_FAILONERROR, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    if(curl_exec($ch)!==FALSE)
    {
        return true;
    }
    else
    {
        return false;
    }
}
?>