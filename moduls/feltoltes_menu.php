<?

function feltoltes_balmenu() {
    global $linkveg,$lang,$db_name,$sid,$u_id,$_SERVER,$m_id,$u_jogok,$u_login,$fooldal_id;

	if($fooldal_id==2) {
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
	}
	
	if(!empty($kod_tartalom)) {
		//Tartalom létrehozása
		$kod_cim="<span class=hasabcimlink>Feltöltés</span>";

		$kodT[0]=$kod_cim;
		$kodT[1]=$kod_tartalom;

		return $kodT;
	}
}

function feltoltes_jobbmenu() {
    global $linkveg,$lang,$db_name,$sid,$u_id,$_SERVER,$m_id,$u_jogok,$u_login,$fooldal_id,$design_url;

	if($fooldal_id==1) {
		$query="select count(id) from hirek where feltette='$u_login' and (megbizhato='i' or ok='f') order by datum desc limit 0,5";
		$lekerdez=mysql_query($query);
		list($mennyi)=mysql_fetch_row($lekerdez);

		$kod_tartalom="\n<a href=?m_id=29&m_op=addhirek$linkveg class=kismenulink><img src=$design_url/img/tovabb1.gif border=0> <b>Új hír beküldése</b></a><br>";
		
		if($mennyi>0) {
			$kod_tartalom.="\n<li class=felsomenulink><a href=?m_id=29$linkveg class=kismenulink>Feltöltött híreid szerkesztése</a>";
			$kod_tartalom.="</li><img src=img/space.gif width=5 height=3>";
		}
	}
	
	if(!empty($kod_tartalom)) {
		//Tartalom létrehozása
		$kod_cim="<span class=hasabcimlink>Feltöltés</span>";

		$kodT[0]=$kod_cim;
		$kodT[1]=$kod_tartalom;

		return $kodT;
	}
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
