<?

function hirlevel_jobbmenu() {
    global $sessid,$db_name,$design,$linkveg;

	if(!isset($design)) $design='alap';
	
	//Tartalom létrehozása
	
	$adatT[0]='Hírlevél';
	$adatT[1]="?m_id=8$linkveg";
	$tipus='jobbmenucim';
	$kod_cim=formazo($adatT,$tipus);

	$adatT[0]=$kod_cim;
	$adatT[1]='';
	$adatT[2]='<form method=post><td width=15><img src=img/space.gif width=15 height=5></td><td>';
	$adatT[2].="<input type=hidden name=m_id value=8>";
	$adatT[2].="<input type=text name=email value='emailcím' size=25 class='urlap'><br><input type=submit value=Feliratkozás class=urlap>";
	$adatT[2].='</td></form>';
	$tipus='jobbmenu';
	$kod=formazo($adatT,$tipus);

    return $kod;
}


switch($op) {
    case '2':
        $hmenu=hirlevel_jobbmenu();
        break;

}

?>
