<?

function archivum_balmenu() {
    global $sessid,$db_name,$design,$linkveg;

	if(!isset($design)) $design='alap';
	
	//Tartalom létrehozása
	
	$kod_cim="<a href=?m_id=1&m_op=kereso$linkveg class=kiscimlink>Archívum</a>";


	$ma=date('Y-m-d');
	$now=time();
	$egynap=86400;
	$tegnapido=$now-$egynap;
	$tegnap=date('Y-m-d',$tegnapido);
	$adatT[2] = "<form method=post><input type=hidden name=m_id value=1><input type=hidden name=m_op value=archivlista>";
	$adatT[2].= "\n<select name=datum class=urlap><option value=$ma>mai hírek</option><option value=$tegnap>tegnap</option>";

	$min=$tegnapido-(10*$egynap);
	for($i=$tegnapido-$egynap; $i>$min; $i=$i-$egynap) {
		$datum=date('Y-m-d',$i);
		$adatT[2].="\n<option value=$datum>$datum</option>";
	}
	$adatT[2].="\n<option value=0>Teljes archívum</option>";
	$adatT[2].="\n</select><input type=submit value=Lista class=urlap></form>";

	$tipus='balmenutartalomgaleria1';
	$kod_tartalom.=formazo($adatT,$tipus);

	$adatT[0]=$kod_cim;
	$adatT[2]=$kod_tartalom;
	$tipus='balmenu1';
	$kod=formazo($adatT,$tipus);

    return $kod;
}

function archivum_jobbmenu() {
    global $sessid,$db_name,$design;

	if(!isset($design)) $design='alap';
	
	//Tartalom létrehozása
	
	$adatT[0]='Galéria';
	$tipus='jobbmenucim';
	$kod_cim=formazo($adatT,$tipus);

	$adatT[0] = 'cím';
	$adatT[1] = 'link';
	$tipus='jobbmenutartalomcikk';
	$kod_tartalom.=formazo($adatT,$tipus);

	$adatT[0]=$kod_cim;
	$adatT[2]=$kod_tartalom;
	$tipus='jobbmenu';
	$kod=formazo($adatT,$tipus);

    return $kod;
}


switch($op) {
	case '1':
        $hmenu=archivum_balmenu();
        break;

    case '2':
        $hmenu=archivum_jobbmenu();
        break;

}

?>
