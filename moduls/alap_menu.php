<?

function alap_balmenu() {
    global $sessid,$db_name,$design,$linkveg;
	
	if(!isset($design)) $design='alap';
	
	//Tartalom létrehozása
	$query="select id, menucim, intro, mid, op from fomenu where helyzet='b' and ok='i' order by sorszam";
	$lekerdez=mysql_db_query($db_name,$query);
	while(list($rid,$rcim,$rintro,$rmid,$rop)=mysql_fetch_row($lekerdez)) {
		$rintro=nl2br($rintro);
		$adatT[0]=$rcim;
		if(!empty($rmid)) {
			$adatT[1]="?m_id=$rmid&m_op=$rop&fm=$rid$linkveg";
		}
		else {
			$adatT[1]="?m_id=17&fm=$rid$linkveg";
		}

		$tipus='balmenucim';
		$kod_cim=formazo($adatT,$tipus);

		$adatT[2]="<span class=alap>$rintro</span>";
		$tipus='balmenutartalom';
		$kod_tartalom=formazo($adatT,$tipus);
		
		$adatT[0]=$kod_cim;
		$adatT[2]=$kod_tartalom;
		$tipus='balmenu';
		$kod.=formazo($adatT,$tipus);
		$kod_cim='';
		$kod_tartalom='';

	}

    return $kod;
}

function alap_jobbmenu() {
    global $sessid,$db_name,$design,$linkveg;
	
	if(!isset($design)) $design='alap';
	
	//Tartalom létrehozása
	$query="select id, menucim, intro, mid, op from fomenu where helyzet='j' and ok='i' order by sorszam";
	$lekerdez=mysql_db_query($db_name,$query);
	while(list($rid,$rcim,$rintro,$rmid,$rop)=mysql_fetch_row($lekerdez)) {
		$rintro=nl2br($rintro);
		$adatT[0]=$rcim;
		if(!empty($rmid)) {
			$adatT[1]="?m_id=$rmid&m_op=$rop&fm=$rid$linkveg";
		}
		else {
			$adatT[1]="?m_id=17&fm=$rid$linkveg";
		}

		$tipus='jobbmenucim';
		$kod_cim=formazo($adatT,$tipus);

		$adatT[2]="<span class=rovatcikklink>$rintro</span><br><br><div align=right><a href=?m_id=17&fm=$rid$linkveg class=rovatcikklink>tovább >></a></div>";
		$tipus='jobbmenutartalom';
		$kod_tartalom=formazo($adatT,$tipus);
		
		$adatT[0]=$kod_cim;
		$adatT[2]=$kod_tartalom;
		$tipus='jobbmenu';
		$kod.=formazo($adatT,$tipus);
		$kod_cim='';
		$kod_tartalom='';

	}

    return $kod;
}


switch($op) {
	case '1':
        $hmenu=alap_balmenu();
        break;

    case '2':
        $hmenu=alap_jobbmenu();
        break;

}

?>
