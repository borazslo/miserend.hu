<?

function feltoltes_balmenu() {
    global $linkveg,$lang,$db_name,$sid,$u_id,$_SERVER,$m_id,$u_jogok,$u_login,$fooldal_id;


	$query="select id,nev,varos,eszrevetel from templomok where letrehozta='$u_login' limit 0,5";
	$lekerdez=mysql_query($query);
	$mennyi=mysql_num_rows($lekerdez);
	
	if($mennyi>0) {
		while(list($tid,$tnev,$tvaros,$teszrevetel)=mysql_fetch_row($lekerdez)) {
			if($teszrevetel=='i') $jelzes.="<a href=\"javascript:OpenScrollWindow('naplo.php?kod=templomok&id=$tid&sid=$sid',550,500);\"><img src=img/csomag.gif title='Új észrevételt írtak hozzá!' align=absmiddle border=0></a> ";		
			elseif($teszrevetel=='f') $jelzes.="<a href=\"javascript:OpenScrollWindow('naplo.php?kod=templomok&id=$tid&sid=$sid',550,500);\"><img src=img/csomagf.gif title='Észrevétel javítása folyamatban!' align=absmiddle border=0></a> ";						
			else $jelzes='';
			
			$kod_tartalom.="\n<li class=link_kek>$jelzes<a href=?m_id=29&m_op=addtemplom&tid=$tid$linkveg class=link_kek title='$tvaros'>$tnev</a></li><img src=img/space.gif width=5 height=3>";
		}	

		$kod_tartalom.="\n<li class=felsomenulink><a href=?m_id=29$linkveg class=felsomenulink>Teljes lista...</a>";
		$kod_tartalom.="</li><img src=img/space.gif width=5 height=3>";
	}
	
	if(!empty($kod_tartalom)) {
		//Tartalom létrehozása
		$kod_cim="<span class=hasabcimlink>Módosítás</span>";

		$kodT[0]=$kod_cim;
		$kodT[1]=$kod_tartalom;

		return $kodT;
	}
}

function feltoltes_jobbmenu() {
    	
	return false;
}


switch($op) {
    case 'aktiv':
        $hmenuT=feltoltes_balmenu();
        break;

    case '1':
        $hmenuT=feltoltes_balmenu();
        break;

	case '2':
        $hmenuT=feltoltes_jobbmenu();
        break;

}

?>
