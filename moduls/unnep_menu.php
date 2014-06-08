<?

function unnep_balmenu() {
    global $linkveg,$lang,$db_name,$sid,$u_id,$_SERVER,$m_id,$u_jogok,$u_login,$fooldal_id,$_GET;


}

function unnep_jobbmenu() {
    global $linkveg,$lang,$db_name,$sid,$u_id,$_SERVER,$m_id,$u_jogok,$u_login,$fooldal_id,$design_url,$_GET;

	
		$kod_cim="Mindenszentek";

		$kod_tartalom="\nAz ünnep célja, hogy az összes szentet - nemcsak azokat, akiket az Egyház külön szentnek nyilvánított - egy közös napon ünnepeljük. ";	

		$kod_tipus='piros';

	
	if(!empty($kod_tartalom)) {
		//Tartalom létrehozása
		$kodT[0]=$kod_cim;
		$kodT[1]=$kod_tartalom;
		$kodT[2]=$kod_tipus;

		return $kodT;
	}
}


switch($op) {
    case '1':
        $hmenuT=unnep_balmenu();
        break;

	case '2':
        $hmenuT=unnep_jobbmenu();
        break;

}

?>
