<?

function galeria_balmenu() {
    global $sessid,$db_name,$design,$linkveg;

	if(!isset($design)) $design='alap';
	
	//Tartalom létrehozása
	
	$kod_cim="<a href=?m_id=11$linkveg class=kiscimlink>Galéria</a>";

	$ma=date('Y-m-d');
	$query="select id,cim from galeria where ok='i' and datum<='$ma' order by datum desc limit 0,1";
	$lekerdez=mysql_db_query($db_name,$query);
	list($gid,$cim)=mysql_fetch_row($lekerdez);
	$konyvtar="kepek/galeria/$gid/kicsi";

	//Könyvtár tartalmát beolvassa
	if(is_dir($konyvtar)) {
		$handle=opendir($konyvtar);
		while ($file = readdir($handle)) {
			if ($file!='.' and $file!='..') {
				$info=getimagesize("$konyvtar/$file");
				$whinfo=$info[2];
				$kepT[]="<a href=?m_id=11&m_op=view&gid=$gid$linkveg><img src=$konyvtar/$file border=0 $whinfo></a>";
			}
		}
		closedir($handle);
	}
	$max=count($kepT);
	if($max>1) {
		$szam=rand(0,$max-1);
		$kepkiir=$kepT[$szam];
	}
	elseif($max==1) $kepkiir=$kepT[0];
	else {
	}

	$adatT[2] = "<a href=?m_id=11&m_op=view&gid=$gid$linkveg class=kiscimlink>$cim</a><br><img src=img/space.gif width=5 height=10><br><div align=center>$kepkiir</div><br>";
	$tipus='balmenutartalomgaleria1';
	$kod_tartalom.=formazo($adatT,$tipus);

	$adatT[0]=$kod_cim;
	$adatT[2]=$kod_tartalom;
	$tipus='balmenu1';
	$kod=formazo($adatT,$tipus);

    return $kod;
}

function galeria_jobbmenu() {
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
        $hmenu=galeria_balmenu();
        break;

    case '2':
        $hmenu=galeria_jobbmenu();
        break;

}

?>
