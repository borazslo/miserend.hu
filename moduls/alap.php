<?

/*
//////////////////////////
 //      alap modul      //
//////////////////////////
*/

function alap_index() {
    global $design_url,$m,$db_name,$m_id,$am_id,$_GET,$lang;

	$fm=$_GET['fm'];
	if(!isset($fm)) {
		$feltetel="helyzet='f' and ok='i' and nyelv='$lang' order by sorszam limit 0,1";
	}
	else $feltetel="id='$fm'";

	$query="select cim,leiras from fomenu where $feltetel";
	if(!$lekerdez=mysql_db_query($db_name,$query)) echo "HIBA!<br>$query".mysql_error();
	list($cim,$leiras)=mysql_fetch_row($lekerdez);


	if(strstr($leiras,'!*!=')) {
		//ugratni kell a megfelelő oldalra
		$link=strip_tags($leiras);
		$link=str_replace('!*!=','',$link);
		$link=str_replace('&amp;','&',$link);
		header("Location: ".$link);
	}
	
    $tartalomkod="<p class=alcim>$cim</p>";
	$tartalomkod.="<p class=alap>$leiras</p>";

	//Letölthető fájlok:
	//Könyvtár tartalmát beolvassa
	$konyvtar="fajlok/info/$fm";
	if(is_dir($konyvtar)) {
		$handle=opendir($konyvtar);
		while ($file = readdir($handle)) {
			if ($file!='.' and $file!='..') {
				$meret=intval((filesize("$konyvtar/$file")/1024));
				if($meret>1000) {
					$meret=intval(($meret/1024)*10)/10;
					$meret.=' MB';
				}
				else $meret.=' kB';

				$filekiir=rawurlencode($file);

				$kit=strtolower(substr($file,-3));
				if($kit=='pdf') $ikon='<img src=img/pdf.gif border=0 align=absmiddle>';
				elseif($kit=='doc') $ikon='<img src=img/doc.gif border=0 align=absmiddle>';
				elseif($kit=='xls') $ikon='<img src=img/xls.gif border=0 align=absmiddle>';
				elseif($kit=='zip') $ikon='<img src=img/zip.gif border=0 align=absmiddle>';
				elseif($kit=='rar') $ikon='<img src=img/rar.gif border=0 align=absmiddle>';
				elseif($kit=='jpg' or $kit=='gif') $ikon='<img src=img/pic.gif border=0 align=absmiddle>';
				else $ikon='<img src=img/file.gif border=0 align=absmiddle>';

				$letoltes.="<br \><a href='$konyvtar/$filekiir' class=alap>$ikon $file</a><span class=alap> ($meret) </span>";
			}
		}
		closedir($handle);
	}
	if(!empty($letoltes)) {
		$tartalomkod.="<hr><span class=kiscim>Letölthető anyagok:</span>$letoltes";
	}

	$adatT[2]=$tartalomkod;
	$tipus='doboz';
	$kod=formazo($adatT,$tipus);

    return $kod;
}


switch($m_op) {
    case 'index':
        $tartalom=alap_index();
        break;

}

?>
