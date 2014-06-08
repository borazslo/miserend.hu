<?

function eszrevetelek_balmenu() {
    global $linkveg,$lang,$db_name,$sid,$u_id,$_SERVER,$m_id,$u_jogok,$u_login,$fooldal_id,$_GET;

	$hirid=$_GET['hir'];
	if(empty($hirid) and $_GET['m_op']=='view') {
		$hirid=$_GET['id'];
	}

	$templomid=$_GET['templom'];
	if(empty($templomid) and $_GET['m_op']=='view') {
		$templomid=$_GET['id'];
	}

	if($fooldal_id==1 and !empty($hirid)) {		
		$kod_cim="Észrevételek, kiegészítés";

		$kod_tartalom="\nHa a hír / esemény adataival kapcsolatos észrevételed van, (javítás, kiegészítés, változás) kérünk írd meg nekünk! <b><i>Hálásan köszönjük a segítséged!</i></b>";
		$kod_tartalom.="\n<div class=\"bekuld\"><a href=\"javascript:OpenNewWindow('eszrevetel.php?sid=$sid&id=$hirid&kod=hirek',450,530);\">Észrevételek beküldése</a></div>";	
	}

	elseif($fooldal_id==2 and !empty($templomid)) {		
		$kod_cim="Észrevételek, kiegészítés";

		$kod_tartalom="\nAmennyiben a templommal, adataival, vagy a miserenddel kapcsolatosan észrevételed van, kérünk írd meg nekünk! <b><i>Hálásan köszönjük a segítséged!</i></b>";
		$kod_tartalom.="\n<div class=\"bekuld\"><a href=\"javascript:OpenNewWindow('eszrevetel.php?sid=$sid&id=$templomid&kod=templomok',450,530);\">Észrevételek beküldése</a></div>";	
	}

	if(!empty($kod_tartalom)) {
		//Tartalom létrehozása
		$kodT[0]=$kod_cim;
		$kodT[1]=$kod_tartalom;
	
		return $kodT;
	}
}

function eszrevetelek_jobbmenu() {
    global $linkveg,$lang,$db_name,$sid,$u_id,$_SERVER,$m_id,$u_jogok,$u_login,$fooldal_id,$design_url,$_GET;

	$hirid=$_GET['hir'];
	if(empty($hirid) and $_GET['m_op']=='view') {
		$hirid=$_GET['id'];
	}

	$templomid=$_GET['templom'];
	if(empty($templomid) and $_GET['m_op']=='view') {
		$templomid=$_GET['id'];
	}

	if($fooldal_id==1 and !empty($hirid)) {		
		$kod_cim="Észrevételek, kiegészítés";

		$kod_tartalom="\nHa a hír / esemény adataival kapcsolatos észrevételed van, (javítás, kiegészítés, változás) kérünk írd meg nekünk! <b><i>Hálásan köszönjük a segítséged!</i></b>";
		$kod_tartalom.="\n<div class=\"bekuld\"><a href=\"javascript:OpenNewWindow('eszrevetel.php?sid=$sid&id=$hirid&kod=hirek',450,530);\">Észrevételek beküldése</a></div>";	
	}
	elseif($fooldal_id==2 and !empty($templomid)) {		
		$kod_cim="Észrevételek, kiegészítés";

		$kod_tartalom="\nAmennyiben a templommal, adataival, vagy a miserenddel kapcsolatosan észrevételed van, kérünk írd meg nekünk! <b><i>Hálásan köszönjük a segítséged!</i></b>";
		$kod_tartalom.="\n<div class=\"bekuld\"><a href=\"javascript:OpenNewWindow('eszrevetel.php?sid=$sid&id=$templomid&kod=templomok',450,530);\">Észrevételek beküldése</a></div>";	
	}
	
	if(!empty($kod_tartalom)) {
		//Tartalom létrehozása
		$kodT[0]=$kod_cim;
		$kodT[1]=$kod_tartalom;

		return $kodT;
	}
}


switch($op) {
    case '1':
        $hmenuT=eszrevetelek_balmenu();
        break;

	case '2':
        $hmenuT=eszrevetelek_jobbmenu();
        break;

}

?>
