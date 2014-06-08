<?

function admin_balmenu() {
    global $linkveg,$lang,$db_name,$u_id,$_SERVER,$m_id,$u_jogok;

	$jogokT=explode('-',$u_jogok);
	foreach($jogokT as $jogok) {
		$jogellT[]="jogkod like '%$jogok%'";
	}
	if(is_array($jogellT)) $jogell=' and ('.implode(' or ',$jogellT).')';

	//Tartalom létrehozása
	$query="select id,mid,nev from adminmenu where ok='i' and fm='0' $jogell order by sorszam";
	$lekerdez=mysql_db_query($db_name,$query);
	while(list($aid,$mid,$mcim)=mysql_fetch_row($lekerdez)) {
		$kod_tartalom.="\n<li class=felsomenulink><a href=?m_id=$mid$linkveg class=felsomenulink>$mcim</a>";		
		if($m_id==$mid) {
			$queryam="select op,nev from adminmenu where ok='i' and fm='$aid' order by sorszam";
			$lekerdezam=mysql_db_query($db_name,$queryam);
			while(list($mop,$mcim)=mysql_fetch_row($lekerdezam)) {
				$kod_tartalom.="\n<br><a href=?m_id=$mid&m_op=$mop$linkveg class=loginlink>-> $mcim</a>";
			}
		}
		$kod_tartalom.="</li><img src=img/space.gif width=5 height=3>";
	}

	//Tartalom létrehozása
	$kod_cim="<span class=admincimlink>Adminisztráció</span>";

	$kodT[0]=$kod_cim;
	$kodT[1]=$kod_tartalom;

	return $kodT;
}


switch($op) {
    case '0':
        $hmenuT=admin_balmenu();
        break;

}

?>
