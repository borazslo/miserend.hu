<?

function reklam_jobbmenu() {
    global $sessid,$db_name,$design;

	if(!isset($design)) $design='alap';
	//Tartalom létrehozása
	$ma=date('Y-m-d');
		$query="select id,url,title from reklam where sorszam>0 and hol='2' and (tol<='$ma' and (ig>='$ma' or ig='0000-00-00')) and ok='i' order by sorszam";
	if(!$lekerdez=mysql_db_query($db_name,$query)) echo "HIBA!<br>$query<br>".mysql_error();
	if(mysql_num_rows($lekerdez)>0) {
		while(list($id,$url,$title)=mysql_fetch_row($lekerdez)) {
			$idT[]=$id;
			$urlT[]=$url;
			$titleT[]=$title;
		}
		$mennyi=count($idT);
		for($i=0;$i<$mennyi;$i++) {
			$szam=$i;

			$id=$idT[$szam];
			if($id>0) {
				if(strstr($urlT[$szam],'http://')) $target='target=_blank'; //Ha http, akkor új ablak
				$kep1="kepek/reklam/$id.jpg";
				$kep2="kepek/reklam/$id.gif";
				if(is_file($kep1)) {
					$info=getimagesize($kep1);
					$w=$info[0];
					$h=$info[1];
					if($w<=170 and $h<=300)	$img="<img src=$kep1 border=0 width=$w height=$h alt='$titleT[$szam]'>";
				}
				elseif(is_file($kep2)) {
					$info=getimagesize($kep2);
					$w=$info[0];
					$h=$info[1];
					if($w<=170 and $h<=300) $img="<img src=$kep2 border=0 width=$w height=$h alt='$titleT[$szam]'>";
				}
				if(!empty($urlT[$szam]) and !empty($img)) $reklamT[]="<a href=viewurl.php?id=$id$linkveg $target>$img</a>";
				elseif(!empty($img)) $reklamT[]=$img;

				if(!empty($img)) mysql_db_query($db_name,"update reklam set szamlalo=szamlalo+1 where id='$id'");
				$img='';
			}
		}
	}
////////////
$idT='';
$urlT='';
$titleT='';

	$query="select id,url,title from reklam where sorszam=0 and hol='2' and (tol<='$ma' and (ig>='$ma' or ig='0000-00-00')) and ok='i'";
	if(!$lekerdez=mysql_db_query($db_name,$query)) echo "HIBA!<br>$query<br>".mysql_error();
	if(mysql_num_rows($lekerdez)>0) {
		while(list($id,$url,$title)=mysql_fetch_row($lekerdez)) {
			$idT[]=$id;
			$urlT[]=$url;
			$titleT[]=$title;
		}
		$mennyi=count($idT);
		if($mennyi>0) {
			$maxmennyi=$mennyi;
			if($maxmennyi>5) $maxmennyi=5;
			srand ((float) microtime() * 10000000);
			$kulcsok = array_rand ($idT, $maxmennyi);
		}
	
		for($i=0;$i<5;$i++) {
			$szam=$kulcsok[$i];	
			
			$id=$idT[$szam];
			if($id>0) {
				if(strstr($urlT[$szam],'http://')) $target='target=_blank'; //Ha http, akkor új ablak
				$kep1="kepek/reklam/$id.jpg";
				$kep2="kepek/reklam/$id.gif";
				if(is_file($kep1)) {
					$info=getimagesize($kep1);
					$w=$info[0];
					$h=$info[1];
					if($w<=170 and $h<=300)	$img="<img src=$kep1 border=0 width=$w height=$h alt='$titleT[$szam]'>";
				}
				elseif(is_file($kep2)) {
					$info=getimagesize($kep2);
					$w=$info[0];
					$h=$info[1];
					if($w<=170 and $h<=300) $img="<img src=$kep2 border=0 width=$w height=$h alt='$titleT[$szam]'>";
				}
				if(!empty($urlT[$szam]) and !empty($img)) $reklamT[]="<a href=viewurl.php?id=$id$linkveg $target>$img</a>";
				elseif(!empty($img)) $reklamT[]=$img;

				if(!empty($img)) mysql_db_query($db_name,"update reklam set szamlalo=szamlalo+1 where id='$id'");
				$img='';
			}
		}
	}
	
	if(is_array($reklamT)) {
		$reklam=implode('<br><br>',$reklamT);						

		$adatT[0]='&nbsp;';
		$tipus='jobbmenucim';
		$kod_cim=formazo($adatT,$tipus);

		$adatT[0]=$kod_cim;
		$adatT[2]="<td width=10><img src=img/space.gif width=10 height=5></td><td><div align=center>$reklam</div></td>";
		$tipus='jobbmenu';
		$kod=formazo($adatT,$tipus);
	}
	
    return $kod;
}


switch($op) {
    case '2':
        $hmenu=reklam_jobbmenu();
        break;

}

?>
