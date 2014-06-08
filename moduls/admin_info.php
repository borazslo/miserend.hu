<?

/*
//////////////////////////
//       1. modul       //
//////////////////////////
*/


function info_index() {
	global $linkveg,$m_id;

	$kod=info_adminmenu();

	return $kod;
}

function info_adminmenu() {
	global $m_id,$linkveg;

	$menu.='<span class=alcim>Statikus (tartalmi) oldalak/menük szerkesztése</span><br><br>';
	$menu.="<a href=?m_id=$m_id&m_op=add$linkveg class=kismenulink>Új oldal feltöltése</a><br>";
	$menu.="<a href=?m_id=$m_id&m_op=mod$linkveg class=kismenulink>Meglévő oldal módosítása, törlése</a><br>";

	$adatT[2]=$menu;
	$tipus='doboz';
	$tartalom.=formazo($adatT,$tipus);	

	return $tartalom;
}

function info_add($id) {
	global $m_id,$db_name,$onload,$sessid;;

	$tartalomkod="<p class=alcim>Menü, oldal hozzáadása/módosítása</p>";

	$tartalomkod.=include('editscript2.php');
	$tartalomkod.=info_urlap($id);

	$adatT[2]=$tartalomkod;
	$tipus='doboz';
	$tartalom.=formazo($adatT,$tipus);	

    return $tartalom;
	
}

function info_urlap($id) {
	global $m_id,$db_name,$aloldal,$sessid;
	
	//Főmenü
	if($id>0) {
		$query="select menucim,cim,leiras,helyzet,sorszam,rovat,mid,ok,nyelv from fomenu where id='$id'";
		$lekerdez=mysql_db_query($db_name,$query);
		list($menucim,$cim,$szoveg,$helyzet,$sorszam,$rovat,$mid,$ok,$nyelv)=mysql_fetch_row($lekerdez);
		if($ok!='i') $ok='n';
	}
	else $mid=14;

	$urlap.="\n<FORM ENCTYPE='multipart/form-data' method=post>";
	$urlap.="\n<input type=hidden name=m_id value=$m_id><input type=hidden name=m_op value=adding>";
	$urlap.="\n<input type=hidden name=sessid value=$sessid><input type=hidden name=id value=$id>";

	$urlap.="\n<br><br><span class=kiscim>Menücím</span><span class=alap> (Ami menüben megjelenik, ha be van állítva.)</span>";
	$urlap.="\n<br><input type=text size=50 name=menucim maxlength=100 class=urlap value='$menucim'>";

	$urlap.="\n<br><br><span class=kiscim>Cím</span><span class=alap> (Teljes cím)</span>";
	$urlap.="\n<br><input type=text size=60 name=cim maxlength=250 class=urlap value='$cim'>";

	$urlap.="\n<br><br><span class=kiscim>Sorszám</span><span class=alap> (Elhelyezkedés a felsorolásban)</span>";
	$urlap.="\n<br><input type=text size=2 name=sorszam maxlength=2 class=urlap value='$sorszam'>";
/*
	$urlap.="\n<br><br><span class=kiscim>Menü nyelve</span>";
	$urlap.="\n<br><select name=nyelv class=urlap>";
	$lekerdez=mysql_db_query($db_name,"select kod,nevhu from nyelvek where ok='i'");
	while(list($nyelvkod,$nyelvnev)=mysql_fetch_row($lekerdez)) {
		$urlap.="<option value=$nyelvkod";
		if($nyelvkod==$nyelv) $urlap.= ' selected';
		$urlap.=">$nyelvnev</option>";
	}
	$urlap.='</select>';
*/
	$urlap.="\n<br><br><span class=kiscim>Helyzet</span><span class=alap> (bal, jobb, felső, alsó menü vagy alapban nem látszik)</span>";
	$urlap.="\n<br><select name=helyzet class=urlap>";
	$urlap.="\n<option value=0";
	if(empty($helyzet)) $urlap.=' selected';
	$urlap.=">-</option>";
	$urlap.="\n<option value=b";
	if($helyzet=='b') $urlap.=' selected';
	$urlap.=">balmenü</option>";
	$urlap.="\n<option value=f";
	if($helyzet=='f') $urlap.=' selected';
	$urlap.=">felső menü</option>";
	$urlap.="\n<option value=j";
	if($helyzet=='j') $urlap.=' selected';
	$urlap.=">jobb menü</option>";
	$urlap.="\n<option value=l";
	if($helyzet=='l') $urlap.=' selected';
	$urlap.=">alsó menü</option></select>";
	
	$urlap.="\n<br><br><span class=kiscim>Engedélyezés</span><span class=alap> (A menü megjelenhet-e, működhet-e?)</span>";
	$urlap.="\n<br><input type=checkbox name=ok value='i' class=urlap";
	if($ok!='n') $urlap.=' checked';
	$urlap.=">";	

//Modul
	$urlap.="\n<br><br><span class=kiscim>Modul (funkció) kiválasztása</span><br><span class=alap>(Csak, ha a menüponttal másik funkció jön be!)</span>";
	$urlap.="\n<br><select name=mid class=urlap><option value=''>nem funkció</option>";
	$lekerdez=mysql_db_query($db_name,"select id,nev,zart from modulok where jogkod='' and funkcio='i' order by zart,nev");
	while(list($mmid,$mmnev,$mmzart)=mysql_fetch_row($lekerdez)) {
		$urlap.="<option value=$mmid";
		if($mid==$mmid or (empty($mid) and $mmid==14)) $urlap.= ' selected';
		$urlap.=">$mmnev";
		if($mmzart>0) $urlap.=' (zárt)';
		$urlap.="</option>";
	}
	$urlap.='</select>';

	//Rovat
		$urlap.="\n<br><br><span class=kiscim>Ha a menüpont egyben egy rovat</span><br><span class=alap>(Ilyenkor nem kell a leírás mezőt kitölteni!)</span>";
		$urlap.="\n<br><select name=rovat class=urlap><option value=0>Nem rovat</option>";
		$lekerdez=mysql_db_query($db_name,"select id,nev from rovatkat where ok='i'");
		while(list($rid,$rnev)=mysql_fetch_row($lekerdez)) {
			$urlap.="<option value=$rid";
			if($rid==$rovat) $urlap.= ' selected';
			$urlap.=">$rnev</option>";
		}
		$urlap.='</select>';

		$urlap.="\n<br><br><span class=kiscim>Letölthető fájl(ok):</span>";
		//Könyvtár tartalmát beolvassa
		if($id>0) {
			$konyvtar="../fajlok/info/$id";
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
						$urlap.="<br><li><a href='$konyvtar/$filekiir' class=alap><b>$file</b></a><span class=alap> ($meret) </span><input type=checkbox class=urlap name=delfajl[] value='$file'><span class=alap>Töröl</span></li>";
					}
				}
				closedir($handle);
			}
		}
		$urlap.="\n<br><span class=alap>Új fájl: </span><input type=file size=60 name=fajl class=urlap>";

		$urlap.="\n<br><br><span class=kiscim>Képfeltöltés</span><span class=alap> (A kép bekerül a szövegmezőbe, ahol mozgatható. CSAK <b>jpg</b> fájl!)</span>";
		$urlap.="\n<br><input type=file size=60 name=kep class=urlap>";

		$urlap.="\n<br><br><span class=kiscim>Kép igazítása szöveg-körbefuttatással</span>";
		$urlap.="<br><select name=align class=urlap><option value='0'>kép külön</option><option value=left>balra</option><option value=right>jobbra</option></select>";

		$urlap.="\n<br><br><span class=kiscim>Kicsinyítés:</span>";
		$urlap.="\n<br><input type=text name=kicsinyit value=120 size=3 class=urlap>";

	//Leírás
		if($mid!='14') $urlap.="<br><br><span class=kiscim>Ehhez a menühöz funkció tartozik!</span><span class=alap><br>Lehet, hogy az itt beírt tartalom nem jelenik meg!</span>";

		$urlap.="<br><br><span class=kiscim>Leírás</span>";
		$urlap.="<br><span class=alap>(Ha a menüpont átugrik egy másik oldalra, akkor a szövegrészbe a következőt kell írni: !*!=oldalcíme <br>Pl: !*!=?m_id=1&lang=en vagy lehet önálló oldal is: !*!=http://www.index.hu)</span>";
		$urlap.="<textarea name=szoveg class=urlap cols=100 rows=30>$szoveg</textarea>";	



	$urlap.="\n<br><br><input type=submit value=Mehet class=urlap></form>";

	Return $urlap;	
}


function info_adding() {
	global $m_id,$linkveg,$db_name,$_POST,$_FILES;

	$id=$_POST['id'];
	$menucim=$_POST['menucim'];
	$cim=$_POST['cim'];
	$leiras=$_POST['szoveg'];
	$mid=$_POST['mid'];
	$rovat=$_POST['rovat'];

	$sorszam=$_POST['sorszam'];
	$helyzet=$_POST['helyzet'];
	$nyelv=$_POST['nyelv'];
	$ok=$_POST['ok'];
	$align=$_POST['align'];
	if($align=='left') $align="align=left";
	elseif($align=='right') $align="align=right";
	else $align='';

	$kicsinyit=$_POST['kicsinyit'];
	if(empty($kicsinyit)) $kicsinyit=120;

	$kep=$_FILES['kep']['tmp_name'];
	$kepnev=$_FILES['kep']['name'];

	if(empty($id)){
		//új főmenü
		$query="insert fomenu set menucim='$menucim', cim='$cim', leiras='$leiras', sorszam='$sorszam', helyzet='$helyzet', rovat='$rovat', ok='$ok', nyelv='$nyelv', mid='$mid'";
		$uj=true;
	}
	else {
		//meglévő főmenü
		$query="update fomenu set menucim='$menucim', cim='$cim', leiras='$leiras', sorszam='$sorszam', helyzet='$helyzet', rovat='$rovat', ok='$ok', nyelv='$nyelv', mid='$mid' where id='$id'";
		$uj=false;
	}

	if(!mysql_db_query($db_name,$query)) $tartalom.='<p class=hiba>HIBA!<br>'.mysql_error();
	else {
		if($uj) $id=mysql_insert_id();


		$delfajl=$_POST['delfajl'];
		if(is_array($delfajl)) {
			foreach($delfajl as $ertek) {
				unlink("fajlok/info/$id/$ertek");
			}
		}

		$fajl=$_FILES['fajl']['tmp_name'];
		$fajlnev=$_FILES['fajl']['name'];

		if(!empty($fajl)) {
			$konyvtar="fajlok/info";
			//Könyvtár ellenőrzése
			if(!is_dir("$konyvtar/$id")) {
				//létre kell hozni
				if(!mkdir("$konyvtar/$id",0775)) {
					echo '<p class=hiba>HIBA a könyvtár létrehozásánál!</p>';					
				}
			}
			//Másolás
			if(!copy($fajl,"$konyvtar/$id/$fajlnev")) echo '<p>HIBA a másolásnál!</p>';
			unlink($fajl);
		}


		if(!empty($kep)) {
			$konyvtar="kepek/info";
			//Könyvtár ellenőrzése
			if(!is_dir("$konyvtar/$id")) {
				//létre kell hozni
				if(!mkdir("$konyvtar/$id",0775)) {
					echo '<p class=hiba>HIBA a könyvtár létrehozásánál!</p>';					
				}
				if(!mkdir("$konyvtar/$id/kicsi",0775)) {
					echo '<p class=hiba>HIBA a könyvtár létrehozásánál!</p>';					
				}
			}
			$info=getimagesize($kep);
			$w=$info[0];
			$h=$info[1];
        
			$kimenet="$konyvtar/$id/$kepnev";
			if($w>1024 or $h>768) {
				if (($hiba = exec("convert -geometry 1024x768 $kep $kimenet")) != "") echo "Hiba: $hiba";
			}
			else {
				if ( !copy($kep, "$kimenet") )
				print("HIBA a másolásnál ($kimenet)!<br>\n");
			}
			//kicsinyítés
			$kimenet1="$konyvtar/$id/kicsi/$kepnev";
			if (($hiba = exec("convert -geometry ".$kicsinyit.'x'.$kicsinyit." $kep $kimenet1")) != "") echo "Hiba: $hiba";

			unlink($kep);

			$info=getimagesize("$konyvtar/$id/$kepnev");
			$w=$info[0];
			if($w>800) $w=800;
			$h=$info[1];
			if($h>600) $h=600;
			$info=getimagesize("$konyvtar/$id/kicsi/$kepnev");
			$whinfo=$info[2];
			$kepkod="<a href=\'javascript:OpenNewWindow(\"view.php?kep=kepek/info/$id/$kepnev\",$w,$h)\';><img src=\"kepek/info/$id/kicsi/$kepnev\" $whinfo border=0 $align vspace=10 hspace=10></a>";

			$leiras=$kepkod.$leiras;
		
			$query="update fomenu set leiras='$leiras' where id='$id'";
			if(!mysql_db_query($db_name,$query)) echo '<p class=hiba>HIBA!<br>'.mysql_error();
		}

		
		$tartalom=info_add($id);
	}	

	Return $tartalom;
}

function info_mod() {
	global $m_id,$db_name,$_POST,$aloldal,$sessid,$linkveg;

    $tartalom="<p class=alcim>Menü / oldal kiválasztása módosításra</p>";

	$query="select id,menucim,helyzet,nyelv,ok from fomenu order by helyzet,nyelv desc,sorszam";
	$lekerdez=mysql_db_query($db_name,$query);
	while(list($fmid,$fmcim,$fmhelyzet,$fmnyelv,$fmok)=mysql_fetch_row($lekerdez)) {
		if(!empty($fmhell) and $fmhell!=$fmhelyzet) $tartalom.='<hr>';
		$tartalom.="\n<a href=?m_id=$m_id&m_op=add&id=$fmid$linkveg class=kismenulink>$fmcim</a>";
		if($fmok=='n') $tartalom.=' <span class=kismenulink><font color=red>(várakozó)</font></span>';
		$tartalom.=" <span class=kismenulink><font color=blue>($fmnyelv)</font></span>";
		$tartalom.="\n<a href=?m_id=$m_id&m_op=del&id=$fmid$linkveg><img src=img/del.jpg border=0 alt=Töröl></a>";
		$tartalom.='<br>';
		$fmhell=$fmhelyzet;
	}

	$adatT[2]=$tartalom;
	$tipus='doboz';
	$kod.=formazo($adatT,$tipus);	

	Return $kod;
}

function info_del() {
	global $m_id,$db_name,$_GET,$linkveg;
	
	$kod=$_GET['kod'];
	$id=$_GET['id'];

    $kiir="<p class=alcim>Admin / Menü törlése</p>";
	$kiir.="\n<div class=kiscim>Biztosan törölni akarod a következő menüt?</div><br>";

	$query="select menucim from fomenu where id='$id'";
	$lekerdez=mysql_db_query($db_name,$query);
	list($menucim)=mysql_fetch_row($lekerdez);
	$kiir.="<div class=alap><i><u><b>$menucim</b></u></i></div>";

	$kiir.="<br><br><a href=?m_id=$m_id&m_op=delete&id=$id$linkveg class=link>Igen</a>";

	return $kiir;
}

function info_delete() {
	global $m_id,$db_name,$_GET;
	$id=$_GET['id'];

	$query="delete from fomenu where id='$id'";
	mysql_db_query($db_name,$query);

	$kod=info_mod();

	return $kod;
}


switch($m_op) {
    case 'index':
        $tartalom=info_index();
        break;

	case 'add':
		$id=$_GET['id'];
		$tartalom=info_add($id);
		break;

	case 'mod':
		$tartalom=info_mod();
		break;

	case 'del':
		$tartalom=info_del();
		break;

	case 'delete':
		$tartalom=info_delete();
		break;

	case 'adding':
		$tartalom=info_adding();
		break;

}

?>
