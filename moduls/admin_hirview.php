<?
$design_url='design/hirporta';

function hirview_index() {
    global $linkveg,$db_name,$lang,$m_id,$_GET;
	$design_url='design/hirporta';

	if(empty($lang)) $lang='hu';

	if(!isset($design)) $design='alap';

	$most=date('Y-m-d H:i:s');
	$ma=date('Y-m-d');
	$lejart=date('Y-m-d H:i:s',time()-604800); //7 nap
	$rovat=$_GET['rovat'];

	$min=$_GET['min'];
	if($min<0 or !isset($min)) $min=0;

	$id=$_GET['id'];
	if(!is_numeric($id)) {
		echo "HIBA!";
		exit;
	}

//Hírek keresése
	$query="select id,cim,intro,datum,aktualis,fohir,rovatkat,kiemelt from hirek where id='$id'";
	$lekerdez=mysql_db_query($db_name,$query);
	list($id,$cim,$intro,$datum,$aktualis,$fohir,$rovatkat,$kiemelt)=mysql_fetch_row($lekerdez);
	$datido=mktime(substr($datum,11,2),substr($datum,14,2),0,substr($datum,5,2),substr($datum,8,2),substr($datum,0,4));
	$ev=substr($datum,0,4);
	$ora=date('G',$datido);
	$perc=date('i',$datido);
	if($ev==date('Y')) $ev='';
	else $ev.='. ';
	$ho=date('n',$datido);
	$nap=date('j',$datido);
	if($ho==date('n') and $nap==date('j')) {
		$ho_nap='';
	}
	else {
		$honap=alapnyelv("ho$ho");
		$ho_nap="$honap $nap.";
	}

	if(!empty($ho_nap)) {
		$datumkiir="$ev$ho_nap";
		$datumkiir.=" $ora:$perc";
	}
	else {
		$datumkiir="$ora:$perc";
	}

	$intro=nl2br($intro);
	$intro=str_replace('href=?',"href=?$linkveg&",$intro); //link helyesbítése
	$intro=str_replace('href="?',"href=\"?$linkveg&",$intro); //link helyesbítése		

	//Fõesemény		
		$foesemenylink="?m_id=19&m_op=view&id=$id";
		$foesemenycim=$cim;
		$foesemenyintro=$intro;
		$foesemenydatum=$datumkiir;
		//Kell még hozzá kép!!!
		$queryk="select fajlnev from kepek where kat='hirek' and kid='$id' and kiemelt='i' order by sorszam limit 0,1";
		$lekerdezk=mysql_db_query($db_name,$queryk);
		list($fajlnev)=mysql_fetch_row($lekerdezk);
		if(!empty($fajlnev)) {
			$konyvtar="kepek/hirek/$id";
			if(is_file("$konyvtar/fooldal/$fajlnev")) {
				$foesemenyintro="<img src=$konyvtar/fooldal/$fajlnev align=left hspace=5>".$foesemenyintro;
			}
		}


	//Fõhír
		$fohirlink="?m_id=19&m_op=view&id=$id";
		$fohircim=$cim;
		$fohirintro=$intro;
		$fohirdatum=$datumkiir;
		//Kell még hozzá kép!!!
		$queryk="select fajlnev from kepek where kat='hirek' and kid='$id' and kiemelt='i' order by sorszam limit 0,1";
		$lekerdezk=mysql_db_query($db_name,$queryk);
		list($fajlnev)=mysql_fetch_row($lekerdezk);
		if(!empty($fajlnev)) {
			$konyvtar="kepek/hirek/$id";
			if(is_file("$konyvtar/fooldal/$fajlnev")) {
				$fohirintro="<img src=$konyvtar/fooldal/$fajlnev align=left hspace=5>".$fohirintro;
			}
		}


		//Rovatfõhírek
			//$kiemeles=str_replace('--','!',$kiemelt);
			//$kiemeles=str_replace('-','',$kiemeles);
			//$kiemelesT=explode('!',$kiemeles);
			$kiemelesT=array('k2*rr1','k2*rr2','k2*rr3','k2*rr4','k2*rr5','k2*rr6');
			foreach($kiemelesT as $ertek) {
				$ertekT=explode('*',$ertek);
				if($ertekT[0]=='k2') {
					$melyikrovat=str_replace('rr','',$ertekT[1]);
					if(!$rovatT[$melyikrovat]) {//Ha az adott rovatban még nincs elsõhír
						$rovatT[$melyikrovat]=1;						
						$melyikrovat="rovat$melyikrovat";
						${$melyikrovat}['link']="?m_id=19&m_op=view&id=$id";
						${$melyikrovat}['cim']=$cim;
						${$melyikrovat}['intro']=$intro;
						${$melyikrovat}['datum']=$datumkiir;
					}
			
					//Ha a választott rovatban már van kiemelt, akkor megy a további hírbe
					$hirlistaT[]="<td valign=top><img src=img/space.gif width=8 height=5><br><img src=$design_url/img/negyzet.jpg></td><td valign=top><a href=?m_id=19&m_op=view&id=$id$linkveg class=focimlink_kek>$cim</a><span class=kicsi> <i>($datumkiir)</i></span></td>";
				}
			}
			
	$max=0;
	if(is_array($hirlistaT)) {
		if(count($hirlistaT)>15) {
			foreach($hirlistaT as $ertek) {
				$max++;
				if($max<=15) {
					$ujhirlistaT[]=$ertek;
				}
			}
		}
		else $ujhirlistaT=$hirlistaT;
		
		if(is_array($ujhirlistaT)) $hirlista='<table cellpadding=0 cellspacing=2>'.implode('<tr><td colspan=2><img src=img/space.gif width=5 height=3></td></tr>',$ujhirlistaT).'</table>';
	}
	$max=0;
	if(is_array($esemenylistaT)) {
		if(count($esemenylistaT)>5) {
			foreach($esemenylistaT as $ertek) {
				$max++;
				if($max<=5) {
					$ujesemenylistaT[]=$ertek;
				}
			}
		}
		else {
			$ujesemenylistaT=$esemenylistaT;
		}
		
		if(is_array($ujesemenylistaT))	$esemenylista='<table cellpadding=0 cellspacing=2>'.implode('<tr><td colspan=2><img src=img/space.gif width=5 height=3></td></tr>',$ujesemenylistaT).'</table>';
	}

	//Közeli programok keresése
	$ma=time();
	$egynap=86400;
	$hetvege=$ma+(7*$egynap);
	for($i=0;$i<10;$i++) {
		$newtime=time()+($i*$egynap);
		$ujdatum=date('Y-m-d',$newtime);
		$feltetelT[]="aktualis like '%$ujdatum%'";
	}
	if(is_array($feltetelT)) $feltetel=' and ('.implode(' or ',$feltetelT).')';	
	$ma=date('Y-m-d');
	$hetvege=date('Y-m-d',$hetvege);
	$query="select id,cim,aktualis from hirek where ok='i' and datum<='$most' and aktualis!='' $feltetel";
	$lekerdez=mysql_db_query($db_name,$query);
	while(list($id,$cim,$aktualis)=mysql_fetch_row($lekerdez)) {
		$aktualisT=explode('+',$aktualis);
		foreach($aktualisT as $datum) {	
			$voltmar[$datum][$id]=true;
			if(!$voltmar[$id]) {
				if($datum>$ma and $datum<$hetvege) {
					$datido=mktime(0,0,0,substr($datum,5,2),substr($datum,8,2),substr($datum,0,4));
					$nap=date('w',$datido);
					$nap=alapnyelv("nap$nap");
					$aktualisesemenyT[$datum][]="<td valign=top><img src=img/space.gif width=8 height=5><br><img src=$design_url/img/sbarna_negyzet.jpg width=6 height=7></td><td valign=top><a href=# class=focimlink_sbarna>$cim</a><span class=kicsi><i> ($nap)</i></span></td>";			
					$voltmar[$id]=true;
				}
			}
		}
	}

	$max=0;
	ksort($aktualisesemenyT);
	$a=0;
	foreach($aktualisesemenyT as $datum=>$listaT) {
		foreach($listaT as $hirlink) {
			$a++;
			if($a<10) $tovabbiesemenylistaT[]=$hirlink;
		}
	}
	if(is_array($tovabbiesemenylistaT)) $tovabbiesemenylista='<table cellpadding=0 cellspacing=2>'.implode('<tr><td colspan=2><img src=img/space.gif width=5 height=3></td></tr>',$tovabbiesemenylistaT).'</table>';

	//Közeli határidõk keresése
	$ma=date('Y-m-d');
	$egynap=86400;
	$hetvege=$ma+(7*$egynap);
	$query="select id,cim,hatarido from hirek where ok='i' and hatarido>='$ma' and hatarido<='$hetvege' order by hatarido limit 0,10";
	$lekerdez=mysql_db_query($db_name,$query);
	while(list($id,$cim,$hatarido)=mysql_fetch_row($lekerdez)) {
		$datido=mktime(0,0,0,substr($hatarido,5,2),substr($hatarido,8,2),substr($hatarido,0,4));
		$nap=date('w',$datido);
		$nap=alapnyelv("nap$nap");
	    $hataridokiir=str_replace('-','.',$hatarido).'.';
	    if(substr($hatarido,0,4)==date('Y')) $hataridokiir=substr($hataridokiir,5);
	    $hataridokT[]="<td valign=top><img src=img/space.gif width=8 height=5><br><img src=$design_url/img/sbarna_negyzet.jpg width=6 height=7></td><td valign=top><a href=# class=focimlink_sbarna>$cim</a><span class=kicsi><i> (határidõ: $hataridokiir $nap)</i></span></td>";			
	}

	if(is_array($hataridokT) and count($hataridokT)>0) $hataridok='<table cellpadding=0 cellspacing=2>'.implode('<tr><td colspan=2><img src=img/space.gif width=5 height=3></td></tr>',$hataridokT).'</table>';
	else $hataridok="<span class=alap>a következõ napokban nincs határidõ</span>";

	$tmpl_file = $design_url.'/sablon_fooldal.htm';
	$thefile = implode("", file($tmpl_file));
	$thefile = addslashes($thefile);
	$thefile = "\$r_file=\"".$thefile."\";";
	eval($thefile);

	$kod .= $r_file;

    return $kod;
}

function hirview_view() {
	global $linkveg,$db_name,$m_id,$_GET,$lang,$HID;

	$design_url='design/hirporta';

	if(empty($lang)) $lang='hu';
	if(empty($design)) $design='alap';

	$id=$_GET['id'];

	$kulcsszo=$_GET['kulcsszo'];

	$most=date('Y-m-d H:i:s');
	$query="select cim,intro,szoveg,orszag,megye,varos,egyhazmegye,espereskerulet,datum,aktualis,hatarido,rovatkat,kulcsszo,kapcsolodas from hirek where id='$id'";
	$lekerdez=mysql_db_query($db_name,$query);
	if(mysql_num_rows($lekerdez)>0) { 
		list($hcim,$hintro,$hszoveg,$horszag,$hmegye,$hvaros,$hegyhazmegye,$hespereskerulet,$datum,$haktualis,$hhatarido,$hrovatkat,$hkulcsszo,$hkapcsolodas)=mysql_fetch_row($lekerdez);

		$hintro=str_replace('href="?',"href=\"?$linkveg&",$hintro); //link helyesbítése
		$hintro=str_replace('href=?',"href=?$linkveg&",$hintro); //link helyesbítése
		$hintro=str_replace('href="http://'," target=_blank href=\"http://",$hintro); //link helyesbítése
		$hintro=str_replace(' target=_blank href="http://www.hirporta.hu/?'," href=\"http://www.hirporta.hu/?$linkveg&",$hintro); //link helyesbítése

		$hszoveg=str_replace('href="?',"href=\"?$linkveg&",$hszoveg); //link helyesbítése
		$hszoveg=str_replace('href=?',"href=?$linkveg&",$hszoveg); //link helyesbítése
		$hszoveg=str_replace('href="http://'," target=_blank href=\"http://",$hszoveg); //link helyesbítése
		$hszoveg=str_replace(' target=_blank href="http://www.hirporta.hu/?'," href=\"http://www.hirporta.hu/?$linkveg&",$hszoveg); //link helyesbítése

		$datido=mktime(substr($datum,11,2),substr($datum,14,2),0,substr($datum,5,2),substr($datum,8,2),substr($datum,0,4));
		$ev=substr($datum,0,4);
		$ora=date('G',$datido);
		$perc=date('i',$datido);
		if($ev==date('Y')) $ev='';
		else $ev.='.';
		$ho=date('n',$datido);
		$honap=alapnyelv("ho$ho");
		$nap=date('j',$datido);
		$datumkiir="<span class=kicsi><i> $ev $honap $nap. </i></span><span class=kicsi><i>$ora:$perc</i></span>";

		if(!empty($halcim)) $alcimkiir="<span class=kiscim><i>$halcim</i></span><br><br>";

		if(!empty($kulcsszo)) {
			$hcim=str_replace($kulcsszo,"<font color=red>$kulcsszo</font>",$hcim);
			$hintro=str_replace($kulcsszo,"<font color=red>$kulcsszo</font>",$hintro);
			$hszoveg=str_replace($kulcsszo,"<font color=red>$kulcsszo</font>",$hszoveg);
		}
 		if(!empty($hintro)) $hintro=nl2br($hintro);

		if(strstr($u_jogok,'hirek')) {
			$szerk="<a href=?m_id=10&m_op=add&hid=$id$linkveg><img src=img/edit.gif border=0 align=absmiddle alt=szerkesztés></a>";
		}

		$adatT[2]="<span class='cikkcim_piros'>$hcim</a> $szerk</span><br>$datumkiir<br><br>";
		//if(!empty($hintro)) $adatT[2].="<div class=kiscimkizart>$hintro</div>";
		$adatT[2].="<div class=alapkizart>$hszoveg</div>";
		
		//Nyomtatási nézet + továbbküldés
		$adatT[2].="<br><br><div align=center><a href=\"javascript:OpenPrintWindow('pview.php?id=$id',690,600);\" class=link><img align=absmiddle src=img/print.gif border=0> nyomtatási nézet</a> &nbsp;  <a href=\"javascript:OpenNewWindow('send.php?id=$id',500,350);\" class=link><img align=absmiddle src=img/mail.gif border=0> hír továbbküldése</a></div>";

	}
	else {
		$adatT[2]="<span class='hiba'>HIBA! A keresett hír nem található!</span>";
	}

	$tipus='doboz';
	$kod.=formazo($adatT,$tipus);	

	//kapcsolódó fájlok listázása
	$kod_lista='';
	$konyvtar="fajlok/hirek/$id";
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

				$adatT[0]="<b>$file</b> ($meret)";
				$adatT[1]="$konyvtar/$filekiir";
				$tipus='hirlista';
				$kod_lista.=formazo($adatT,$tipus);
			}
		}
		closedir($handle);
	}

	if(!empty($kod_lista)) {
		$adatT[0]='Kapcsolódó fájlok';
		$adatT[1]='#';
		$adatT[2]=$kod_lista;
		$tipus='hirlistadoboz';
		$kod.=formazo($adatT,$tipus);
		$kod_lista='';
	}

	//Kapcsolódó hírek listázása
	if(!empty($hkulcsszo)) {
		$hkulcsszo=substr($hkulcsszo,1,-1);
		$kT=explode('--',$hkulcsszo);
		foreach($kT as $ertek) {
			$feltetelT[]="kulcsszo like '%-$ertek-%'";
		}
		if(is_array($feltetelT)) $feltetel=' and ('.implode(' or ',$feltetelT).')';

		$query="select id,cim,datum from hirek where ok='i' and id!='$id' and datum<='$most' $feltetel order by datum desc limit 0, 10";
		if(!$lekerdez=mysql_db_query($db_name,$query)) echo 'HIBA!<br>'.mysql_error();
		$mennyi=mysql_num_rows($lekerdez);
	
		while(list($id,$cim,$datum)=mysql_fetch_row($lekerdez)) {
			$datido=mktime(substr($datum,11,2),substr($datum,14,2),0,substr($datum,5,2),substr($datum,8,2),substr($datum,0,4));
			$ev=substr($datum,0,4);
			$ora=date('G',$datido);
			$perc=date('i',$datido);
			if($ev==date('Y')) $ev='';
			else $ev.='.';
			$ho=date('n',$datido);
			$honap=alapnyelv("ho$ho");
			$nap=date('j',$datido);
			$datumkiir="<small> $ev $honap $nap. $ora:$perc</small>";
			$adatT[0]="$cim $datumkiir";
			$adatT[1]="?m_op=view&id=$id$holvan$linkveg";
			$tipus='hirlista';
			$kod_lista.=formazo($adatT,$tipus);
		}
		if($mennyi>0) {
			$adatT[0]='Kapcsolódó hírek, cikkek';
			$adatT[1]='#';
			$adatT[2]=$kod_lista;
			$tipus='hirlistadoboz';
			$kod.='<br>'.formazo($adatT,$tipus);
		}
	}	
	$feltetelT='';
	$feltetel='';
	$kod_lista='';

/*
	//Kapcsolódó galéria
	if(!empty($galeria)) {
		$max=strlen($galeria);
		$galeria=substr($galeria,1,$max-1);
		$gT=explode('--',$galeria);
		foreach($gT as $ertek) {
			$feltetelT[]="id='$ertek'";
		}
		if(is_array($feltetelT)) $feltetel=' and ('.implode(' or ',$feltetelT).')';

		$query="select id,cim from galeria where ok='i' and datum<='$most' $feltetel order by datum desc limit 0, 10";
		if(!$lekerdez=mysql_db_query($db_name,$query)) echo 'HIBA!<br>'.mysql_error();
		$mennyi=mysql_num_rows($lekerdez);
	
		while(list($id,$cim)=mysql_fetch_row($lekerdez)) {
			$adatT[0]=$cim;
			$adatT[1]="?m_id=11&m_op=view&gid=$id$linkveg";
			$tipus='hirlista';
			$kod_lista.=formazo($adatT,$tipus);
		}
		if($mennyi>0) {
			$adatT[0]='Kapcsolódó galéria';
			$adatT[1]='#';
			$adatT[2]=$kod_lista;
			$tipus='hirlistadoboz';
			$kod.=formazo($adatT,$tipus);
		}
	}
*/

	$tartalom=$kod;

	//Rovat hírei (oldalsó hasábban)
	$most=date('Y-m-d H:i:s');
	$rovat=$_GET['rovat'];
	if(empty($rovat)) {
		$rovatkat=str_replace('--','!',$hrovatkat);
		$rovatkat=str_replace('-','',$rovatkat);
		$rovatT=explode('!',$rovatkat);
		$rovat=$rovatT[0];
	}

	$query="select id,cim from hirek where rovatkat like '%-$rovat-%' and ok='i' and datum<='$most' limit 0,15";
	$lekerdez=mysql_db_query($db_name,$query);
	while(list($rhid,$rhcim)=mysql_fetch_row($lekerdez)) {
		$rovathirek.="<a href=?hir=$rhid class=linkkicsi><img src=$design_url/img/sbarna_negyzet.jpg border=0> <b>$rhcim</b></a><br>";
	}

	$tmpl_file = $design_url.'/sablon_hirview.htm';
	$thefile = implode("", file($tmpl_file));
	$thefile = addslashes($thefile);
	$thefile = "\$r_file=\"".$thefile."\";";
	eval($thefile);

	$kod = $r_file;

    return $kod;	
}

//Jogosultság ellenõrzése
if(strstr($u_jogok,'hirek')) {
	switch($m_op) {
		case 'index':
			$tartalom=hirview_index();
			break;

		case 'view':
			$tartalom=hirview_view();
			break;
	}
}
else {
	$tartalom="\n<span class=hiba>HIBA! Nincs hozzá jogosultságod!</span>";
}
