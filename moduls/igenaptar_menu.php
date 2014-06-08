<?

include_once("szotar/datumok.php");

function igenaptar_balmenu() {
    global $sessid,$db_name,$m_id,$dateh;
	
	//Cím létrehozása
	$adatT[0]=alapnyelv('igenaptár');
	$tipus='balmenucim';
	$kod.=formazo($adatT,$tipus);

	include_once("igenaptar_functions_havi.php");

	//Tartalom létrehozása
	$adatT[2] = naptari(); 
	$tipus='doboz';
	$kod.=formazo($adatT,$tipus);
	
	if($m_id!=5) return $kod;
}

function igenaptar_aktivmenu() {
    global $sessid, $db_name, $dateh;

	
	//Cím létrehozása
	$adatT[0]=alapnyelv('igenaptár');
	$tipus='balmenucim';
	$kod.=formazo($adatT,$tipus);

	include_once("igenaptar_functions_havi.php");

	//Tartalom létrehozása
	$adatT[2] = naptari(); 
	$tipus='doboz';
	$kod.=formazo($adatT,$tipus);
	

    return $kod;
}

switch($op) {
    case '1':
        $hmenu=igenaptar_balmenu();
        break;

	case 'aktiv':
		$hmenu=igenaptar_aktivmenu();
		break;
}

?>
