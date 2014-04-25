<?php
if(isset($_REQUEST['v']) AND preg_match('/^[0-9]{1,3}$/',$_REQUEST['v'])) $v = $_REQUEST['v'];


if(!isset($v)) { echo json_encode(array('error'=>1,'text'=>'Verzió szám nélkül nem tudo mit tenni.')); exit; }
elseif($v == 1) { echo json_encode(array('error'=>0,'text'=>'Minden rendben, bár nem csináltunk semmit.')); exit; }
elseif($v == 2) {
	
	$inputJSON = file_get_contents('php://input');
	$t = $inputJSON;
	$input= json_decode( $inputJSON, TRUE ); //convert JSON into array

	if(!is_array($input)) { json_encode(array('error'=>1,'text'=>'Nem kaptunk adatot.')); exit; }
	if(!isset($input['tid']) OR !isset($input['pid']) OR !isset($input['text'])) {
		echo json_encode(array('error'=>1,'text'=>'Hibás formázású adatot kaptunk.')); 
		//exit; 
		}
		
	$input['timestamp'] = date('Y-m-d H:i:s');
	$input['v'] = $v;
	$inputJSON = json_encode($input);
	
	$leiras = "Mobilalkalmazáson keresztül érkezett információ:\n".$input['text']."\n <i>verzió:".$v.", pid:".$input['pid']."</i>";
	include 'db.php';
	//echo "<pre>".print_R(db_query('SELECT * FROM eszrevetelek ORDER BY datum DESC LIMIT 10'),1)."</pre>";
	//echo "<pre>".print_R(db_query('SHOW TABLES'),1)."</pre>";
	if(isset($input['email']) And $input['email'] != '') $email = $input['email']; else $email = '';
	$query = "INSERT INTO eszrevetelek 
		(nev,login,email,megbizhato,datum,hol,hol_id,allapot,leiras) 
		VALUES 
			('android felhasználó','*vendég*','".$email."','?','".$input['timestamp']."','templomok','".$input['tid']."','u','".$leiras."');";
	db_query($query);
	
	$query="update templomok set eszrevetel='i' where id='".$input['tid']."'";
	db_query($query);
	
	
	$file = 'jelentesek.txt';
	// Open the file to get existing content
	$current = file_get_contents($file);
	// Append a new person to the file
	//$current .= $input['tid'].";".$input['pid'].";'".$input['text']."'\n";
	$current .= $inputJSON."\n";
	// Write the contents back to the file
	file_put_contents($file, $current);

	echo json_encode(array('error'=>0,'text'=>'Köszönjük. Elmentettük.'));
}
else { echo json_encode(array('error'=>1,'text'=>'Verzió szám nélkül nem tudo mit tenni.')); exit; }

?>