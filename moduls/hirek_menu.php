<?

function hirek_balmenu() {
	global $db_name,$fooldal_id,$linkveg;
		
	$ma=date('Y-m-d');
	$most=date('Y-m-d H:i:s');
	$query="select id,cim from hirek where ok='i' and datum<='$most' order by datum desc limit 0,5";
	$lekerdez=mysql_query($query);
	while(list($id,$cim)=mysql_fetch_row($lekerdez)) {
		$keretmenutxt.="<a href=?fooldal_id=1&hir=$id$linkveg class=link><img src=http://miserend.hu/design/miserend/img/negyzet.jpg border=0> $cim</a><br>";
	}

	//Tartalom létrehozása
	$kodT[0]="<span class=hasabcimlink>Friss hírek</span>";
	$kodT[1]=$keretmenutxt;

    return $kodT;
}

function hirek_jobbmenu() {
	global $db_name,$fooldal_id;
		
	$ma=date('Y-m-d');
	$query="select id,cim from hirek where ok='i' and datum<='$ma' order by datum desc limit 0,5";
	$lekerdez=mysql_query($query);
	while(list($id,$cim)=mysql_fetch_row($lekerdez)) {
		$keretmenutxt.="<a href=?fooldal_id=1&hir=$id$linkveg class=link><img src=http://miserend.hu/design/miserend/img/negyzet.jpg border=0> $cim</a><br>";
	}

	//Tartalom létrehozása
	$kodT[0]="<span class=hasabcimlink>Friss hírek</span>";
	$kodT[1]=$keretmenutxt;

    return $kodT;
}


switch($op) {
	case '1':
        $hmenuT=hirek_balmenu();
        break;

    case '2':
        $hmenuT=hirek_jobbmenu();
        break;

}

?>
