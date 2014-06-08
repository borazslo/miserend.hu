<?

function admin_igenaptar_balmenu() {
    global $linkveg,$lang,$db_name;

	
	//Cím létrehozása
	$adatT[0]=alapnyelv('aktuális hírek');
	$tipus='balmenucim';
	$kod.=formazo($adatT,$tipus);

	//Tartalom létrehozása
	$query="select id,cim from hirek where lang='$lang' and ok='i' order by kiemelt asc, datum desc limit 0,4";
	$lekerdez=mysql_db_query($db_name,$query);
	while(list($hid,$hcim)=mysql_fetch_row($lekerdez)) {
		$adatT[2]='<a href=?m_id=7&m_op=view&id=$hid$linkveg class=link>$hcim</a>';
	}
	$tipus='doboz';
	$kod.=formazo($adatT,$tipus);
	

    return $kod;
}

function admin_igenaptar_aktivmenu() {
    global $linkveg,$db_name;

	
	//Cím létrehozása
	$adatT[0]=alapnyelv('Liturgikus naptár');
	$tipus='balmenucim';
	$kod.=formazo($adatT,$tipus);

	$menu.="<a href=?m_id=11&m_op=addige$linkveg class=kismenulink>gondolatok hozzáadása</a><br>";
	$menu.="<a href=?m_id=11&m_op=gondolatok$linkveg class=kismenulink>gondolatok módosítása</a><br>";
	$menu.="<a href=?m_id=11&m_op=addszent$linkveg class=kismenulink>szentek hozzáadása</a><br>";
	$menu.="<a href=?m_id=11&m_op=szentek$linkveg class=kismenulink>szentek módosítása</a><br>";
	$menu.="<a href=?m_id=11&m_op=naptar$linkveg class=kismenulink>liturgikus naptár beállítása</a><br>";

	//Tartalom létrehozása
	$adatT[2]=$menu;
	$tipus='doboz';
	$kod.=formazo($adatT,$tipus);
	

    return $kod;
}
if(strstr($u_jogok,'igenaptar')) {
switch($op) {
    case '1':
        $hmenu=admin_igenaptar_balmenu();
        break;

	case 'aktiv':
		$hmenu=admin_igenaptar_aktivmenu();
		break;
}
}

?>
