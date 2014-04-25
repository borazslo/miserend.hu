<?

function galeria_index() {
	global $linkveg,$db_name,$elso,$m_id,$m_op,$_GET,$design_url,$design;

	if(!isset($design)) $design='alap';

	$min=$_GET['min'];
	if(!isset($min) or $min<1) $min=0;

	$leptet=$_GET['leptet'];
	if(!isset($leptet)) $leptet=8;

	$prev=$min-$leptet;
	$next=$min+$leptet;

	$tmpl_file = $design_url.'/alap/galeria_elvalaszto.htm';
	$thefile = implode("", file($tmpl_file));
    $thefile = addslashes($thefile);
	$thefile = "\$r_file=\"".$thefile."\";";
    eval($thefile);  
    $kiemelt_elvalaszto = $r_file;


	$ma=date('Y-m-d');
	$query="select id,cim,datum from galeria where ok='i' and datum<='$ma' order by datum desc ";
	$lekerdez=mysql_db_query($db_name,$query);
	$mennyi=mysql_num_rows($lekerdez);
	if($mennyi>$leptet) {
		$query.="limit $min,$leptet";
		$lekerdez=mysql_db_query($db_name,$query);
	}
	while(list($gid,$cim,$datum)=mysql_fetch_row($lekerdez)) {
		$datumkiir=str_replace('-','.',$datum).'.';
		$tartalom.="<a href=?m_id=$m_id&m_op=view&gid=$gid$linkveg class=k1link>$cim</a><span class=kicsi> ($datumkiir)</span><br>";

		//pár mintakép behívása
		$konyvtar="kepek/galeria/$gid";

		//Könyvtár tartalmát beolvassa
		if(is_dir($konyvtar)) {
			$handle=opendir($konyvtar);
			while ($file = readdir($handle)) {
				if ($file!='.' and $file!='..' and $file!='kicsi') {
					$info=getimagesize("$konyvtar/$file");
					$w=$info[0];
					if($w>800) $w=820;
					$h=$info[1];
					if($h>600) $h=600;
					$info=getimagesize("$konyvtar/kicsi/$file");
					$whinfo=$info[2];
					$kepekT[]="<a href='javascript:OpenNewWindow(\"view.php?kep=$konyvtar/$file\",$w,$h)';><img src=\"$konyvtar/kicsi/$file\" $whinfo border=0 $align vspace=10 hspace=10></a> ";
				}
			}
			closedir($handle);
		}

		srand((float)microtime()*100000);
		shuffle($kepekT);
		for($i=0;$i<3;$i++) {
			$tartalom.=$kepekT[$i];
		}
		$kepekT='';
		$tartalom.="\n<div align=right><a href=?m_id=$m_id&m_op=view&gid=$gid$linkveg class=link>további képek...</a></div>";
		$tartalom.=$kiemelt_elvalaszto;
		$tartalom.='<br>';
		//<img src=img/space.gif width=5 height=10><br>";
	}

	if($min>1) {
		//Visszaléptetés
		$visszalink="<a href=?m_id=$m_id&min=$prev$linkveg class=link>Vissza</a>";
	}

	if($mennyi>$leptet+1) {
		//elõre léptetés
		$tovabblink="<a href=?m_id=$m_id&min=$next$linkveg class=link>Tovább</a>";
	}

	$tartalom.="<br>$visszalink";
	if(!empty($visszalink) and !empty($tovabblink)) $tartalom.= ' - ';
	$tartalom.=$tovabblink;

	$adatT[2]="<span class=alcim>Galéria</span><br><br>".$tartalom;
	$tipus='doboz';
	$kod.=formazo($adatT,$tipus);	

    return $kod;
}


function galeria_view() {
	global $db_name,$linkveg,$_GET;

	$gid=$_GET['gid'];

	$query="select cim,intro,datum from galeria where ok='i' and id='$gid'";
	$lekerdez=mysql_db_query($db_name,$query);
	if(mysql_num_rows($lekerdez)>0) {
		mysql_db_query($db_name,"update galeria set szamlalo=szamlalo+1 where id='$gid'");
		list($cim,$intro,$datum)=mysql_fetch_row($lekerdez);
		$intro=nl2br($intro);
	}

	$konyvtar="kepek/galeria/$gid";
/*
	//Könyvtár tartalmát beolvassa
	if(is_dir($konyvtar)) {
		$handle=opendir($konyvtar);
		while ($file = readdir($handle)) {
			if ($file!='.' and $file!='..' and $file!='kicsi') {
				$info=getimagesize("$konyvtar/$file");
				$w=$info[0];
				if($w>800) $w=820;
				$h=$info[1];
				if($h>600) $h=600;
				$info=getimagesize("$konyvtar/kicsi/$file");
				$whinfo=$info[2];
				$kepek.="<a href='javascript:OpenNewWindow(\"view.php?kep=$konyvtar/$file\",$w,$h)';><img src=\"$konyvtar/kicsi/$file\" $whinfo border=0 $align vspace=10 hspace=10></a> ";
			}
		}
		closedir($handle);
	}
*/
	//képcímek behívása
	$kepek.="<table width=98%><tr>";
	$query="select cim,fajlnev,sorszam from g_kepcimek where gid='$gid' order by sorszam";
	if(!$lekerdez=mysql_db_query($db_name,$query)) echo "HIBA!<br>".mysql_error();

	while(list($kcim,$kfnev,$ksorszam)=mysql_fetch_row($lekerdez)) {
		if($a%3==0 and $a>0) $kepek.='</tr><tr>';
		$a++;
		$info=getimagesize("$konyvtar/$kfnev");
		$w=$info[0];
		if($w>800) $w=820;
		$h=$info[1];
		if($h>600) $h=600;
		$info=getimagesize("$konyvtar/kicsi/$kfnev");
		$whinfo=$info[2];
		$w=820;
		$h=630;
		$kepek.="<td><a href='javascript:OpenNewWindow(\"gview.php?gid=$gid&kep=$kfnev\",$w,$h)';><img src=\"$konyvtar/kicsi/$kfnev\" $whinfo border=0 $align title='$kcim' vspace=10 hspace=10></a></td>";
	}
	$kepek.="</tr></table>";

	
	$adatT[2]="<span class=alcim>Galéria</span><br><br>";
	$adatT[2].="<span class=kiscim>$cim</span><br><span class=alap>$datumkiir</span><br><br>";
	if(!empty($intro)) $adatT[2].="<span class=alap>$intro</span><br><br>";
	$adatT[2].=$kepek;
	$tipus='doboz';
	$kod.=formazo($adatT,$tipus);

	return $kod;
}

switch($m_op) {
    case 'index':
        $tartalom=galeria_index();
        break;

	case 'view':
		$tartalom=galeria_view();
		break;
	
}

?>
