<?

function chat_balmenu() {
    global $u_id,$u_login,$db_name,$linkveg;

	$loginkiir=urlencode($u_login);
   
	//Tartalom létrehozása
	$kod_cim="<span class=admincimlink>Admin üzenõfal</span>";

	$kod_tartalom="<iframe name=chatadd width=135 height=135 frameborder=0 src=chatadd.php?u_login=$loginkiir$linkveg></iframe>";
	$kod_tartalom.="<hr>";
	$kod_tartalom.="<iframe name=chat width=135 height=500 frameborder=0 src=chat.php?u_login=$loginkiir$linkveg></iframe>";

	$kodT[0]=$kod_cim;
	$kodT[1]=$kod_tartalom;

    return $kodT;

}


switch($op) {
    case '0':
        $hmenuT=chat_balmenu();
        break;
}

?>
