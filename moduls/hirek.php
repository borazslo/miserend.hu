<?

function hirek_index() {
    global $linkveg,$db_name,$lang,$m_id,$design_url,$design,$_GET;
	if(empty($lang)) $lang='hu';

	if(!isset($design)) $design='alap';

	$most=date('Y-m-d H:i:s');
	$ma=date('Y-m-d');
	$lejart=date('Y-m-d H:i:s',time()-1209600); //14 nap
	$rovat=$_GET['rovat'];

	$min=$_GET['min'];
	if($min<0 or !isset($min)) $min=0;


//Hírek keresése
	$query="select id,cim,intro,datum,aktualis,fohir,rovatkat,kiemelt from hirek where ok='i' and datum<='$most' and datum>='$lejart' order by datum desc";
	$lekerdez=mysql_query($query);
	while(list($id,$cim,$intro,$datum,$aktualis,$fohir,$rovatkat,$kiemelt)=mysql_fetch_row($lekerdez)) {
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
		if($fohir=='i' and !empty($aktualis) and $vanfoesemeny<1) {
			$foesemenyid=$id;
			$foesemenylink="?hir=$id";
			$foesemenycim=$cim;
			$foesemenyintro=$intro;
			$foesemenydatum=$datumkiir;
			//Kell még hozzá kép!!!
			$queryk="select fajlnev from kepek where kat='hirek' and kid='$id' and kiemelt='i' order by sorszam limit 0,1";
			$lekerdezk=mysql_query($queryk);
			list($fajlnev)=mysql_fetch_row($lekerdezk);
			if(!empty($fajlnev)) {
				$konyvtar="kepek/hirek/$id";
				if(is_file("$konyvtar/fooldal/$fajlnev")) {
					$foesemenyintro="<img src=$konyvtar/fooldal/$fajlnev align=left hspace=5>".$foesemenyintro;
				}
			}

			$vanfoesemeny++;
		}
		//További események
		elseif(!empty($aktualis)) {
			$esemenylistaT[]="<td valign=top><img src=img/space.gif width=8 height=5><br><img src=$design_url/img/sbarna_negyzet.jpg width=6 height=7></td><td valign=top><a href=?hir=$id$linkveg class=focimlink_sbarna>$cim</a></td>";
			//Esetleg ikon a kategória szerint???
		}

		//Fõhír
		if($fohir=='i' and empty($aktualis) and $vanfohir<1) {
			$fohirid=$id;
			$fohirlink="?hir=$id$linkveg";
			$fohircim=$cim;
			$fohirintro=$intro;
			$fohirdatum=$datumkiir;
			//Kell még hozzá kép!!!
			$queryk="select fajlnev from kepek where kat='hirek' and kid='$id' and kiemelt='i' order by sorszam limit 0,1";
			$lekerdezk=mysql_query($queryk);
			list($fajlnev)=mysql_fetch_row($lekerdezk);
			if(!empty($fajlnev)) {
				$konyvtar="kepek/hirek/$id";
				if(is_file("$konyvtar/fooldal/$fajlnev")) {
					$fohirintro="<img src=$konyvtar/fooldal/$fajlnev align=left hspace=5>".$fohirintro;
				}
			}

			$vanfohir++;
		}
		//Rovatfõhírek
		elseif(!empty($kiemelt) and $id!=$fohirid and $id!=$foesemenyid) {
			$kiemeles=str_replace('--','!',$kiemelt);
			$kiemeles=str_replace('-','',$kiemeles);
			$kiemelesT=explode('!',$kiemeles);
			foreach($kiemelesT as $ertek) {
				$ertekT=explode('*',$ertek);
				if($ertekT[0]=='k2') {
					$melyikrovat=str_replace('rr','',$ertekT[1]);
					if(!$rovatT[$melyikrovat]) {//Ha az adott rovatban még nincs elsõhír
						$rovatT[$melyikrovat]=1;						
						$melyikrovat="rovat$melyikrovat";
						${$melyikrovat}['link']="?hir=$id$linkveg";
						${$melyikrovat}['cim']=$cim;
						${$melyikrovat}['intro']=$intro;
						${$melyikrovat}['datum']=$datumkiir;
					}
					elseif(!$vanmar[$id]) {
						//Ha a választott rovatban már van kiemelt, akkor megy a további hírbe
						$hirlistaT[]="<td valign=top><img src=img/space.gif width=8 height=5><br><img src=$design_url/img/negyzet.jpg></td><td valign=top><a href=?hir=$id$linkveg class=focimlink_kek>$cim</a><span class=kicsi> <i>($datumkiir)</i></span></td>";
						$vanmar[$id]=true;
					}
				}
			}
			
		}
		//További hírek
		else {
			$hirlistaT[]="<tr><td valign=top><img src=img/space.gif width=8 height=5><br><img src=$design_url/img/negyzet.jpg></td><td valign=top><a href=?hir=$id$linkveg class=cimlink_kek>$cim</a><span class=kicsi> <i>($datumkiir)</i></span></td></tr>";
		}		
	}
	$max=0;
	$maxhir=20;
	if(is_array($hirlistaT)) {
		if(count($hirlistaT)>$maxhir) {
			foreach($hirlistaT as $ertek) {
				$max++;
				if($max<=$maxhir) {
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
		$feltetelT[]="(aktualis like '%$ujdatum%' and (tol='0000-00-00' or tol='$ujdatum'))";
	}
	if(is_array($feltetelT)) $feltetel=' and ('.implode(' or ',$feltetelT).')';	
	$ma=date('Y-m-d');
	$hetvege=date('Y-m-d',$hetvege);
	$query="select id,cim,aktualis from hirek where ok='i' and datum<='$most' and aktualis!='' $feltetel";
	$lekerdez=mysql_query($query);
	while(list($id,$cim,$aktualis)=mysql_fetch_row($lekerdez)) {
		$aktualisT=explode('+',$aktualis);
		foreach($aktualisT as $datum) {	
			$voltmar[$datum][$id]=true;
			if(!$voltmar[$id]) {
				if($datum>$ma and $datum<$hetvege) {
					$datido=mktime(0,0,0,substr($datum,5,2),substr($datum,8,2),substr($datum,0,4));
					$nap=date('w',$datido);
					$nap=alapnyelv("nap$nap");
					$aktualisesemenyT[$datum][]="<td valign=top><img src=img/space.gif width=8 height=5><br><img src=$design_url/img/sbarna_negyzet.jpg width=6 height=7></td><td valign=top><a href=?hir=$id$linkveg class=cimlink_sbarna>$cim</a><span class=kicsi><i> ($nap)</i></span></td>";			
					$voltmar[$id]=true;
				}
			}
		}
	}

	$max=0;
	$maxesemeny=20;
	ksort($aktualisesemenyT);
	$a=0;
	foreach($aktualisesemenyT as $datum=>$listaT) {
		foreach($listaT as $hirlink) {
			$a++;
			if($a<=$maxesemeny) $tovabbiesemenylistaT[]=$hirlink;
		}
	}
	if(is_array($tovabbiesemenylistaT)) $tovabbiesemenylista='<table cellpadding=0 cellspacing=2>'.implode('<tr><td colspan=2><img src=img/space.gif width=5 height=3></td></tr>',$tovabbiesemenylistaT).'</table>';

	//Közeli határidõk keresése
	$ma=date('Y-m-d');
	$egynap=86400;
	$hetvege=date('Y-m-d',(time()+(7*$egynap)));
	$query="select id,cim,hatarido from hirek where ok='i' and hatarido>='$ma' and hatarido<='$hetvege' order by hatarido limit 0,10";
	$lekerdez=mysql_query($query);
	while(list($id,$cim,$hatarido)=mysql_fetch_row($lekerdez)) {
		if($hatarido==$ma) {
			$hataridokiir='';
			$nap='<font color=red><b>ma!</b></font>';
		}
		else {
			$datido=mktime(0,0,0,substr($hatarido,5,2),substr($hatarido,8,2),substr($hatarido,0,4));
			$nap=date('w',$datido);
			$nap=alapnyelv("nap$nap");
		    $hataridokiir=str_replace('-','.',$hatarido).'.';
		    if(substr($hatarido,0,4)==date('Y')) $hataridokiir=substr($hataridokiir,5);
		}
	    $hataridokT[]="<td valign=top><img src=img/space.gif width=8 height=5><br><img src=$design_url/img/sbarna_negyzet.jpg width=6 height=7></td><td valign=top><a href=?hir=$id$linkveg class=cimlink_sbarna>$cim</a><span class=kicsi><i> (határidõ: $nap)</i></span></td>";			
	}

	if(is_array($hataridokT) and count($hataridokT)>0) $hataridok='<table cellpadding=0 cellspacing=2>'.implode('<tr><td colspan=2><img src=img/space.gif width=5 height=3></td></tr>',$hataridokT).'</table>';
	else $hataridok="<span class=alap>a következõ napokban nincs határidõ</span>";

	//Archív ûrlap
	$ma=date('Y-m-d');
	$now=time();
	$egynap=86400;
	$tegnapido=$now-$egynap;
	$tegnap=date('Y-m-d',$tegnapido);	
	$archivurlap.= "\n<select name=datum class=urlap><option value=$ma>mai hírek</option><option value=$tegnap>tegnapi</option>";
	/*
	$min=$tegnapido-(10*$egynap);
	for($i=$tegnapido-$egynap; $i>$min; $i=$i-$egynap) {
		$datum=date('Y-m-d',$i);
		$archivurlap.="\n<option value=$datum>$datum</option>";
	}
	*/
	$archivurlap.="\n<option value=0>teljes archívum</option>";
	$archivurlap.="\n</select><br><img src=img/space.gif width=5 height=8><br><div align=right><input type=image src=$design_url/img/keresgomb.jpg border=0></div>";


	$tmpl_file = $design_url.'/sablon_fooldal.htm';
	$thefile = implode("", file($tmpl_file));
	$thefile = addslashes($thefile);
	$thefile = "\$r_file=\"".$thefile."\";";
	eval($thefile);

	$kod .= $r_file;

    return $kod;
}

function hirek_rovat() {
    global $linkveg,$db_name,$lang,$m_id,$design_url,$design,$_GET;
	if(empty($lang)) $lang='hu';

	if(!isset($design)) $design='alap';

	$most=date('Y-m-d H:i:s');
	$ma=date('Y-m-d');
	$lejart=date('Y-m-d H:i:s',time()-259200); //3 nap
	$rovat=$_GET['rovat'];

	$min=$_GET['min'];
	if($min<0 or !isset($min)) $min=0;

	if(empty($rovat)) {
	    if($lang=='en') $rovat=12;
	    elseif($lang=='de') $rovat=14;
	    elseif($lang=='it') $rovat=13;
	    else $rovat=1;
	}
	
	//Rovat szerinti kiírások lekérdezése
	$query="select friss,k3,lista,datumkiir from rovatkat where id='$rovat'";
	$lekerdez=mysql_query($query);
	list($frisshirek,$k3focim,$max,$datumkiirok)=mysql_fetch_row($lekerdez);
	$feltetelk1=" and kiemelt like '%-k1*rr$rovat-%'";
	$feltetelk2=" and kiemelt like '%-k2*rr$rovat-%'";
	$feltetelk3=" and kiemelt like '%-k3*rr$rovat-%'";
	$feltetel=" and rovatkat like '%-$rovat-%'";
	$holvan="&rovat=$rovat";


	//Ha alsóbb kategóriában vagyunk és van megadva érték, akkor felülírjuk a rovat szerinti értéket:
	if($fokat>0) {
		$query="select friss,k3,lista,datumkiir from rovatkat where id='$fokat'";
		$lekerdez=mysql_query($query);
		list($fkfrisshirek,$fkk3focim,$fkmax,$datumkiirok)=mysql_fetch_row($lekerdez);
		if(!empty($fkfrisshirek)) $frisshirek=$fkfrisshirek;
		if(!empty($fkk3focim)) $k3focim=$fkk3focim;
		if(!empty($fkmax)) $max=$fkmax;
		$feltetelk1=" and kiemelt like '%-k1*rr$fokat-%'";
		$feltetelk2=" and kiemelt like '%-k2*rr$fokat-%'";
		$feltetelk3=" and kiemelt like '%-k3*rr$fokat-%'";
		$feltetel=" and rovatkat like '%-$fokat-%'";
		$holvan.="&fokat=$fokat";
	}

	if($kat>0) {
		$query="select friss,k3,lista,datumkiir from rovatkat where id='$kat'";
		$lekerdez=mysql_query($query);
		list($kfrisshirek,$kk3focim,$kmax,$datumkiirok)=mysql_fetch_row($lekerdez);
		if(!empty($kfrisshirek)) $frisshirek=$kfrisshirek;
		if(!empty($kk3focim)) $k3focim=$kk3focim;
		if(!empty($kmax)) $max=$kmax;
		$feltetelk1=" and kiemelt like '%-k1*rr$kat-%'";
		$feltetelk2=" and kiemelt like '%-k2*rr$kat-%'";
		$feltetelk3=" and kiemelt like '%-k3*rr$kat-%'";
		$feltetel=" and rovatkat like '%-$kat-%'";
		$holvan.="&kat=$kat";
	}

	if($alkat>0) {
		$query="select friss,k3,lista,datumkiir from rovatkat where id='$alkat'";
		$lekerdez=mysql_query($query);
		list($akfrisshirek,$akk3focim,$akmax,$datumkiirok)=mysql_fetch_row($lekerdez);
		if(!empty($akfrisshirek)) $frisshirek=$akfrisshirek;
		if(!empty($akk3focim)) $k3focim=$akk3focim;
		if(!empty($akmax)) $max=$akmax;
		$feltetelk1=" and kiemelt like '%-k1*ak$alkat-%'";
		$feltetelk2=" and kiemelt like '%-k2*ak$alkat-%'";
		$feltetelk3=" and kiemelt like '%-k3*ak$alkat-%'";
		$feltetel=" and rovatkat like '%-$alkat-%'";
		$holvan.="&alkat=$alkat";
	}

	$next=$min+$max;
	$prev=$min-$max;
	$limit="limit $min,$max";

//Gondolatok rovat -> include a communióról
	if($rovat==6) {
		$tmpl_file="http://www.communio.hu/szombathely/evangelium-kurir.php";
		$thefile = implode("", file($tmpl_file));
	    $thefile = addslashes($thefile);
		$thefile = "\$r_file=\"".$thefile."\";";
	    eval($thefile);  
	    $gondolat .= $r_file;

		$adatT[2]=$gondolat;
		$tipus='doboz';
		$kod.=formazo($adatT,$tipus);

		$kod.='<div align=center><img src=img/vonal.gif width=546 height=7></div>';
	}

//Szemle -> kiemelt 1 nyitva
	if($rovat==4) {
		$query="select id,cim,alcim,fokepfelirat,intro,szoveg,datum from hirek where ok='i' and datum<='$most' and datum>='$lejart' and (lejarat>='$ma' or lejarat='000-00-00') and nyelv='$lang' and megjelenhet like '%kurir%' $feltetelk1 order by datum desc";
		$lekerdez=mysql_query($query);
		if(mysql_num_rows($lekerdez)>0) {
			list($hid,$hcim,$halcim,$fokepfelirat,$hintro,$hszoveg,$datum,$hkulcsszo,$galeria)=mysql_fetch_row($lekerdez);
			if(!mysql_query("update hirek set szamlalo=szamlalo+1, napiszamlalo=napiszamlalo+1 where id='$id'")) echo 'HIBA!<br>'.mysql_error();

			$hintro=str_replace('href="?',"href=\"?$linkveg&",$hintro); //link helyesbítése
			$hintro=str_replace('href=?',"href=?$linkveg&",$hintro); //link helyesbítése
			$hintro=str_replace('href="http://'," target=_blank href=\"http://",$hintro); //link helyesbítése
			$hintro=str_replace(' target=_blank href="http://www.magyarkurir.hu/?'," href=\"http://www.magyarkurir.hu/?$linkveg&",$hintro); //link helyesbítése

			$hszoveg=str_replace('href="?',"href=\"?$linkveg&",$hszoveg); //link helyesbítése
			$hszoveg=str_replace('href=?',"href=?$linkveg&",$hszoveg); //link helyesbítése
			$hszoveg=str_replace('href="http://'," target=_blank href=\"http://",$hszoveg); //link helyesbítése
			$hszoveg=str_replace(' target=_blank href="http://www.magyarkurir.hu/?'," href=\"http://www.magyarkurir.hu/?$linkveg&",$hszoveg); //link helyesbítése

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
		
 			if(!empty($hintro)) $hintro=nl2br($hintro);
			if(is_file("kepek/hirek/$hid/fokep/k1.jpg")) {
				$info=getimagesize("kepek/hirek/$hid/fokep/kep.jpg");
				$w=$info[0];
				if($w>800) $w=820;
				$h=$info[1];
				if($h>600) $h=600;
				$fokep="<a href='javascript:OpenNewWindow(\"view.php?kep=kepek/hirek/$hid/fokep/kep.jpg\",$w,$h)'; title='$fokepfelirat'><img src=kepek/hirek/$hid/fokep/k1.jpg align=left vspace=10 hspace=10 border=0></a>";
			}

			$adatT[2]="<a href=?m_id=$m_id&m_op=view&id=$hid$linkveg class='k1link'>$hcim</a><br>$alcimkiir$datumkiir<br><br>$fokep";
			if(!empty($hintro)) $adatT[2].="<span class=kiscimkizart>$hintro</span><br><br>";
			$adatT[2].="<span class=alapkizart>$hszoveg</span>";			

		}
		else {
			$adatT[2]="<span class='hiba'>HIBA! A keresett hír nem található!</span>";
		}

		$tipus='doboz';
		$kod.=formazo($adatT,$tipus);	
	}
	else {
//Fõhír keresése -> csak ha nem a szemle rovatban vagyunk!
	$query="select id,cim,alcim,fokepfelirat,intro,datum from hirek where ok='i' and datum<='$most' and datum>='$lejart' and (lejarat>='$ma' or lejarat='000-00-00') and nyelv='$lang' and megjelenhet like '%kurir%' $feltetelk1 order by datum desc";
	$lekerdez=mysql_query($query);
	if(mysql_num_rows($lekerdez)>0) {
		list($id,$cim,$alcim,$fokepfelirat,$intro,$datum)=mysql_fetch_row($lekerdez);
		$datido=mktime(substr($datum,11,2),substr($datum,14,2),0,substr($datum,5,2),substr($datum,8,2),substr($datum,0,4));
		$ev=substr($datum,0,4);
		$ora=date('G',$datido);
		$perc=date('i',$datido);
		if($ev==date('Y')) $ev='';
		else $ev.='.';
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
			$datumkiir="<span class=kicsi><i> $ev $ho_nap</i></span>";
			$datumkiir.="<span class=kicsi><i> $ora:$perc</i></span>";
		}
		else {
			$datumkiir="<span class=kicsi><i> $ora:$perc</i></span>";
		}
		if($datumkiirok!='i') $datumkiir='';

		if(!empty($alcim)) $alcimkiir="<span class=kiscim>$alcim</span><br>";

		$intro=nl2br($intro);
		$intro=str_replace('href=?',"href=?$linkveg&",$intro); //link helyesbítése
		$intro=str_replace('href="?',"href=\"?$linkveg&",$intro); //link helyesbítése
		if(is_file("kepek/hirek/$id/fokep/k1.jpg")) $fokep="<img src=kepek/hirek/$id/fokep/k1.jpg border=0 align=left vspace=10 hspace=10 alt='$fokepfelirat'>";
		$fohir=$fokep."<a href=?m_id=1&m_op=view&id=$id$holvan$linkveg class=k1link>$cim</a><br>$alcimkiir$datumkiir<br><br><span class=alapkizart>$intro</span>";
		$idnemT[]=$id;
	}
	}

//Kiemelt hírek keresése
	$feltetelnem=" and id!='$id'"; //a fõhír nem jelenhet meg kiemeltként is!
	$query="select id,cim,alcim,fokepfelirat,intro,datum from hirek where ok='i' and megjelenhet like '%kurir%' and datum<='$most' and datum>='$lejart' and (lejarat>='$ma' or lejarat='000-00-00') and nyelv='$lang' $feltetelk2 $feltetelnem order by datum desc limit 0,2";
	$lekerdez=mysql_query($query);
	if(mysql_num_rows($lekerdez)>0) {
		while(list($id,$cim,$alcim,$fokepfelirat,$intro,$datum)=mysql_fetch_row($lekerdez)) {
			$datido=mktime(substr($datum,11,2),substr($datum,14,2),0,substr($datum,5,2),substr($datum,8,2),substr($datum,0,4));
			$ev=substr($datum,0,4);
			$ora=date('G',$datido);
			$perc=date('i',$datido);
			if($ev==date('Y')) $ev='';
			else $ev.='.';
			$ho=date('n',$datido);
			$honap=alapnyelv("ho$ho");
			$nap=date('j',$datido);
			if($ho==date('n') and $nap==date('j')) {
				$ho_nap='';
			}
			else {
				$honap=alapnyelv("ho$ho");
				$ho_nap="$honap $nap.";
			}
			$datumkiir="<span class=kicsi><i> $ev $ho_nap</i></span><span class=kicsi><i> $ora:$perc</i></span>";
			if($datumkiirok!='i') $datumkiir='';

			if(!empty($alcim)) $alcimkiir="<span class=kiscim>$alcim</span><br>";
			else $alcimkiir='';

			$intro=nl2br($intro);
			$intro=str_replace('href=?',"href=?$linkveg&",$intro); //link helyesbítése
			$intro=str_replace('href="?',"href=\"?$linkveg&",$intro); //link helyesbítése

			if(is_file("kepek/hirek/$id/fokep/k2.jpg")) $kiemeltkep="<img src=kepek/hirek/$id/fokep/k2.jpg border=0 align=left vspace=5 hspace=5 alt='$fokepfelirat'>";
			else $kiemeltkep='';
			$kiemeltT[]="<a href=?m_id=1&m_op=view&id=$id$holvan$linkveg class=k2link>$cim</a><br>$alcimkiir$datumkiir<br>$kiemeltkep<span class=alapkizart>$intro</span>";	
			$idnemT[]=$id;
		}

		$kiemelt1=$kiemeltT[0];
		$kiemelt2=$kiemeltT[1];
	}

	if(!empty($fohir) and !empty($kiemelt1)) {
		$tmpl_file = $design_url.'/alap/kiemelt_elvalaszto.htm';
		$thefile = implode("", file($tmpl_file));
	    $thefile = addslashes($thefile);
		$thefile = "\$r_file=\"".$thefile."\";";
	    eval($thefile);  
	    $kiemelt_elvalaszto = $r_file;
	}

	if(!empty($fohir) or !empty($kiemelt1)) {
		$tmpl_file = $design_url.'/alap/fohirek.htm';

	    $thefile = implode("", file($tmpl_file));
		$thefile = addslashes($thefile);
	    $thefile = "\$r_file=\"".$thefile."\";";
		eval($thefile);
    
	    $kod .= $r_file;
	}

//További hírek listázása
	//Kiemelt híreket nem listázunk!
	foreach($idnemT as $id_k) {
		$feltetelT[]="id!='$id_k'";
	}
	if(is_array($feltetelT)) $feltetel_id=' and ' . implode(' and ',$feltetelT);
	$query="select id,cim,datum from hirek where ok='i' and megjelenhet like '%kurir%' and datum<='$most' and nyelv='$lang' $feltetel $feltetel_id";
	$lekerdez=mysql_query($query);
	$mennyiossz=mysql_num_rows($lekerdez);

	$query.=" order by datum desc $limit";
	$lekerdez=mysql_query($query);
	$mennyi=mysql_num_rows($lekerdez);
	while(list($id,$cim,$datum)=mysql_fetch_row($lekerdez)) {
		$datido=mktime(substr($datum,11,2),substr($datum,14,2),0,substr($datum,5,2),substr($datum,8,2),substr($datum,0,4));
		$ev=substr($datum,0,4);
		$ora=date('G',$datido);
		$perc=date('i',$datido);
		if($ev==date('Y')) $ev='';
		else $ev.='. ';
		$ho=date('n',$datido);
		$honap=alapnyelv("ho$ho");
		$nap=date('j',$datido);
		if($ho==date('n') and $nap==date('j')) {
			$ho_nap='';
		}
		else {
			$honap=alapnyelv("ho$ho");
			$ho_nap="$honap $nap. ";
		}
		$datumkiir=" <small>($ev$ho_nap$ora:$perc)</small>";
		if($datumkiirok!='i') $datumkiir='';
		$adatT[0]="$cim $datumkiir";
		$adatT[1]="?m_op=view&id=$id$holvan$linkveg";
		$tipus='hirlista';
		$kod_lista.=formazo($adatT,$tipus);
	}
	if($mennyi>0) {
		$adatT[0]=$frisshirek;
		$adatT[1]="?$holvan$linkveg";
		$adatT[2]=$kod_lista;
		$tipus='hirlistadoboz';
		$kod.=formazo($adatT,$tipus);
	}
	elseif(empty($fohir) and empty($kiemelt1)) {
		$adatT[2]='<span class=kiscim>'.alapnyelv('Jelenleg nincs hír a rovatban').'</span>';
		$tipus='doboz';
		$kod.=formazo($adatT,$tipus);
	}

	//Léptetés a hírek között
		if($mennyiossz>$next) {
			$link="?m_op=hirlista";
			if($rovat>0) $link.="&rovat=$rovat";
			if($fokat>0) $link.="&fokat=$fokat";
			if($kat>0) $link.="&kat=$kat";
			if($alkat>0) $link.="&alkat=$alkat";
			$linkn=$link."&min=0";
	
			$leptetes="<table width=100% cellpadding=0 cellspacing=0 border=0><tr><td width=80%><img src=img/space.gif width=10 height=11></td><td rowspan=2 width=20% align=center bgcolor='#2672AC'><a href=$linkn$linkveg class=emllink>".alapnyelv('tovább')." <img src=$design_url/alap/img/nyilj.gif border=0></a></td></tr>";
			$leptetes.="<tr><td height=5 background=$design_url/alap/img/szaggatott_v.jpg><img src=img/space.gif width=10 height=5></td></tr></table>";
		}
		$adatT[2]=$leptetes;
		$tipus='doboz';
		$kod.=formazo($adatT,$tipus);

	

//Tudta-e kérdés
	//-6 napos cikkek között keresgél
	$now=time();
	$egynap=86400;
	$tegnapido=$now-(6*$egynap);
	$tegnap=date('Y-m-d 00:00:00',$tegnapido);
	$query="select id,kerdes from hirek where ok='i' and megjelenhet like '%kurir%' and datum<='$most' and nyelv='$lang' and kerdes!='' and datum>='$tegnap'";
	$lekerdez=mysql_query($query);
	while(list($id,$cim)=mysql_fetch_row($lekerdez)) {
		$cimT[]=$cim;
		$idT[]=$id;
	}
	$mennyi=count($cimT);
	if($mennyi>1) {
		$szam=rand(0,$mennyi-1);
	}
	elseif($mennyi==1) {
		$szam=0;
	}

	if($mennyi>0) {
		$adatT[0]=$cimT[$szam];
		$adatT[1]="?m_op=view&id=$idT[$szam]$holvan$linkveg";
		$tipus='hirlista';
		$kod_hir=formazo($adatT,$tipus);

		$adatT[0]='Tudta-e, hogy...';
		$adatT[1]="?$linkveg";
		$adatT[2]=$kod_hir;
		$tipus='hirlistadoboz';
		$kod.=formazo($adatT,$tipus);
	}

//Alsó kiemelt hírek keresése
	$query="select id,cim,alcim,fokepfelirat,intro,datum from hirek where ok='i' and megjelenhet like '%kurir%' and datum<='$most' and (lejarat>='$ma' or lejarat='000-00-00') and nyelv='$lang' $feltetelk3 $feltetel_id order by datum desc limit 0,10";
	$lekerdez=mysql_query($query);
	if(mysql_num_rows($lekerdez)>0) {
		$tmpl_file = $design_url.'/alap/kiemelt_elvalaszto.htm';
		$thefile = implode("", file($tmpl_file));
	    $thefile = addslashes($thefile);
		$thefile = "\$r_file=\"".$thefile."\";";
	    eval($thefile);  
	    $kiemelt_elvalaszto = $r_file;

		while(list($k3id,$k3cim,$alcim,$fokepfelirat,$intro,$datum)=mysql_fetch_row($lekerdez)) {
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
			if($datumkiirok!='i') $datumkiir='';
			$intro=nl2br($intro);
			if(is_file("kepek/hirek/$k3id/fokep/n.jpg")) $kiemeltkep="<img src=kepek/hirek/$k3id/fokep/n.jpg border=0 align=left vspace=5 hspace=5 alt='$fokepfelirat'>";
			else $kiemeltkep='';
			$tartalom.="<tr><td colspan=3>$kiemeltkep<a href=?m_id=1&m_op=view&id=$k3id$holvan$linkveg class=k3link>$k3cim</a><br>$datumkiir<br><img src=$design_url/$design/img/space.gif width=5 height=5><br><span class=alap>$intro</span></td></tr><tr><td colspan=3 height=5><img src=$design_url/$design/img/space.gif width=5 height=5></td></tr><tr><td colspan=3 height=5 background=$design_url/$design/img/szaggatott_v.jpg><img src=$design_url/$design/img/space.gif width=5 height=5></td></tr><tr><td colspan=3 height=5><img src=$design_url/$design/img/space.gif width=5 height=5></td></tr>";			
		}
		$cim=$k3focim;
		if(!empty($alcim)) $cim.="<br><span class=kiscim>$alcim</span>";
		$tmpl_file = $design_url.'/alap/alsokiemeltdoboz.htm';
		$thefile = implode("", file($tmpl_file));
		$thefile = addslashes($thefile);
		$thefile = "\$r_file=\"".$thefile."\";";
	    eval($thefile);

		$kod .= $r_file;
	}

    return $kod;
}

function hirek_hirlista() {
    global $linkveg,$db_name,$lang,$m_id,$design_url,$design,$_GET;
	if(empty($lang)) $lang='hu';

	if(!isset($design)) $design='alap';

	$most=date('Y-m-d H:i:s');
	$lejart=date('Y-m-d H:i:s',time()-259200); //3 nap
	$rovat=$_GET['rovat'];
	$fokat=$_GET['fokat'];
	$kat=$_GET['kat'];
	$alkat=$_GET['alkat'];

	$min=$_GET['min'];
	if($min<0 or !isset($min)) $min=0;


	if(empty($rovat)) {
	    if($lang=='en') $rovat=15;
	    elseif($lang=='de') $rovat=18;
	    elseif($lang=='it') $rovat=17;
	    else $rovat=1;
	}
	
	//Rovat szerinti kiírások lekérdezése
	$query="select friss,k3,lista,datumkiir from rovatkat where id='$rovat'";
	$lekerdez=mysql_query($query);
	list($frisshirek,$k3focim,$max,$datumkiirok)=mysql_fetch_row($lekerdez);
	$feltetelk1=" and kiemelt like '%-k1*rr$rovat-%'";
	$feltetelk2=" and kiemelt like '%-k2*rr$rovat-%'";
	$feltetelk3=" and kiemelt like '%-k3*rr$rovat-%'";
	$feltetel=" and rovatkat like '%-$rovat-%'";
	$holvan="&rovat=$rovat";


	//Ha alsóbb kategóriában vagyunk és van megadva érték, akkor felülírjuk a rovat szerinti értéket:
	if($fokat>0) {
		$query="select friss,k3,lista,datumkiir from rovatkat where id='$fokat'";
		$lekerdez=mysql_query($query);
		list($fkfrisshirek,$fkk3focim,$fkmax,$datumkiirok)=mysql_fetch_row($lekerdez);
		if(!empty($fkfrisshirek)) $frisshirek=$fkfrisshirek;
		if(!empty($fkk3focim)) $k3focim=$fkk3focim;
		if(!empty($fkmax)) $max=$fkmax;
		$feltetelk1=" and kiemelt like '%-k1*rr$fokat-%'";
		$feltetelk2=" and kiemelt like '%-k2*rr$fokat-%'";
		$feltetelk3=" and kiemelt like '%-k3*rr$fokat-%'";
		$feltetel=" and rovatkat like '%-$fokat-%'";
		$holvan.="&fokat=$fokat";
	}

	if($kat>0) {
		$query="select friss,k3,lista,datumkiir from rovatkat where id='$kat'";
		$lekerdez=mysql_query($query);
		list($kfrisshirek,$kk3focim,$kmax,$datumkiirok)=mysql_fetch_row($lekerdez);
		if(!empty($kfrisshirek)) $frisshirek=$kfrisshirek;
		if(!empty($kk3focim)) $k3focim=$kk3focim;
		if(!empty($kmax)) $max=$kmax;
		$feltetelk1=" and kiemelt like '%-k1*rr$kat-%'";
		$feltetelk2=" and kiemelt like '%-k2*rr$kat-%'";
		$feltetelk3=" and kiemelt like '%-k3*rr$kat-%'";
		$feltetel=" and rovatkat like '%-$kat-%'";
		$holvan.="&kat=$kat";
	}

	if($alkat>0) {
		$query="select friss,k3,lista,datumkiir from rovatkat where id='$alkat'";
		$lekerdez=mysql_query($query);
		list($akfrisshirek,$akk3focim,$akmax,$datumkiirok)=mysql_fetch_row($lekerdez);
		if(!empty($akfrisshirek)) $frisshirek=$akfrisshirek;
		if(!empty($akk3focim)) $k3focim=$akk3focim;
		if(!empty($akmax)) $max=$akmax;
		$feltetelk1=" and kiemelt like '%-k1*rr$alkat-%'";
		$feltetelk2=" and kiemelt like '%-k2*rr$alkat-%'";
		$feltetelk3=" and kiemelt like '%-k3*rr$alkat-%'";
		$feltetel=" and rovatkat like '%-$alkat-%'";
		$holvan.="&alkat=$alkat";
	}

	$max=60; //hírlistánál csak a lista látszik
	$next=$min+$max;
	$prev=$min-$max;
	$limit1="limit $min,$max"; 

//hírek listázása
	$query="select id,cim,datum from hirek where ok='i' and megjelenhet like '%kurir%' and datum<='$most' and nyelv='$lang' $feltetel $feltetel_id";
	$lekerdez=mysql_query($query);
	$mennyiossz=mysql_num_rows($lekerdez);

	$query.=" order by datum desc $limit1";
	$lekerdez=mysql_query($query);
	$mennyi=mysql_num_rows($lekerdez);
	while(list($id,$cim,$datum)=mysql_fetch_row($lekerdez)) {
		$datido=mktime(substr($datum,11,2),substr($datum,14,2),0,substr($datum,5,2),substr($datum,8,2),substr($datum,0,4));
		$ev=substr($datum,0,4);
		$ora=date('G',$datido);
		$perc=date('i',$datido);
		if($ev==date('Y')) $ev='';
		else $ev.='. ';
		$ho=date('n',$datido);
		$honap=alapnyelv("ho$ho");
		$nap=date('j',$datido);
		if($ho==date('n') and $nap==date('j')) {
			$ho_nap='';
		}
		else {
			$honap=alapnyelv("ho$ho");
			$ho_nap="$honap $nap. ";
		}
		$datumkiir=" <small>($ev$ho_nap$ora:$perc)</small>";
		if($datumkiirok!='i') $datumkiir='';
		$adatT[0]="$cim $datumkiir";
		$adatT[1]="?m_op=view&id=$id$holvan$linkveg";
		$tipus='hirlista';
		$kod_lista.=formazo($adatT,$tipus);
	}
	if($mennyi>0) {
		$adatT[0]=$frisshirek;
		$adatT[1]="?$holvan$linkveg";
		$adatT[2]=$kod_lista;
		$tipus='hirlistadoboz';
		$kod.=formazo($adatT,$tipus);
	}
	elseif(empty($fohir) and empty($kiemelt1)) {
		$adatT[2]='<span class=kiscim>'.alapnyelv('Jelenleg nincs hír a rovatban').'</span>';
		$tipus='doboz';
		$kod.=formazo($adatT,$tipus);
	}
	
	//Léptetés a hírek között
	if($min>0 or $mennyiossz>$next) {
		$link="?m_op=hirlista";
		if($rovat>0) $link.="&rovat=$rovat";
		if($fokat>0) $link.="&fokat=$fokat";
		if($kat>0) $link.="&kat=$kat";
		if($alkat>0) $link.="&alkat=$alkat";
		$linkn=$link."&min=$next";
		$linkp=$link."&min=$prev";

		$leptetes="\n<table width=100% cellpadding=0 cellspacing=0 border=0><tr><td width=75%><img src=img/space.gif width=10 height=11></td><td rowspan=2 width=25% align=center bgcolor='#2672AC'>";

		if($min>0) 
			$leptetes.="<img src=$design_url/alap/img/nyilb.gif border=0> <a href=$linkp$linkveg class=emllink>".alapnyelv('vissza')." </a>";

		if($min>0 and $mennyiossz>$next) 
			$leptetes.="<img src=img/space.gif width=5 height=5><img src=$design_url/alap/img/sgolyopici.gif border=0><img src=img/space.gif width=5 height=5>";

		if($mennyiossz>$next)
			$leptetes.="<a href=$linkn$linkveg class=emllink> ".alapnyelv('tovább')." <img src=$design_url/alap/img/nyilj.gif border=0></a>";
	
		$leptetes.="</td></tr>";
		$leptetes.="<tr><td height=5 background=$design_url/alap/img/szaggatott_v.jpg><img src=img/space.gif width=10 height=5></td></tr></table>";

		$adatT[2]=$leptetes;
		$tipus='doboz';
		$kod.=formazo($adatT,$tipus);
	}

    return $kod;
}

function hirek_view() {
	global $linkveg,$db_name,$m_id,$_GET,$lang,$design_url,$design,$sid,$u_jogok,$HID;

	if(empty($lang)) $lang='hu';
	if(empty($design)) $design='alap';

	if($HID>0) $id=$HID;
	else $id=$_GET['id'];

	$eszrevetellink="<a href=\"javascript:OpenNewWindow('eszrevetel.php?sid=$sid&id=$id&kod=hirek',450,530);\" class=link>";

	$kulcsszo=$_GET['kulcsszo'];

	$most=date('Y-m-d H:i:s');
	$query="select cim,intro,szoveg,orszag,megye,varos,egyhazmegye,espereskerulet,datum,aktualis,hatarido,rovatkat,kulcsszo,kapcsolodas from hirek where id='$id' and ok='i' and datum<='$most'";
	$lekerdez=mysql_query($query);
	if(mysql_num_rows($lekerdez)>0) {
		if(!strstr($u_jogok,'hirek')) {
			if(!mysql_query("update hirek set szamlalo=szamlalo+1, napiszamlalo=napiszamlalo+1 where id='$id'")) echo 'HIBA!<br>'.mysql_error();
		}
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
		if(!$lekerdez=mysql_query($query)) echo 'HIBA!<br>'.mysql_error();
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
		if(!$lekerdez=mysql_query($query)) echo 'HIBA!<br>'.mysql_error();
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

	$query="select id,cim from hirek where rovatkat like '%-$rovat-%' and ok='i' and datum<='$most' order by datum desc limit 0,15";
	$lekerdez=mysql_query($query);
	while(list($rhid,$rhcim)=mysql_fetch_row($lekerdez)) {
		$rovathirek.="<a href=?hir=$rhid class=linkkicsi><img src=$design_url/img/sbarna_negyzet.jpg border=0> <b>$rhcim</b></a><br>";
	}

	//$tmpl_file = $design_url.'/sablon_hirview.htm';
	$tmpl_file = 'design/hirporta/sablon_hirview.htm';
	$thefile = implode("", file($tmpl_file));
	$thefile = addslashes($thefile);
	$thefile = "\$r_file=\"".$thefile."\";";
	eval($thefile);

	$kod = $r_file;

    return $kod;	
}

function hirek_kereso() {
	global $linkveg,$db_name,$m_id,$_GET,$_POST,$design_url;

	$kulcsszo=$_POST['kulcsszo'];
	$datumtol=$_POST['datumtol'];
	$datumig=$_POST['datumig'];
	$rovatT=$_POST['rovatT'];
	$kategoriaT=$_POST['kategoriaT'];

	if(empty($datumtol)) $datumtol='2006-05-23';
	if(empty($datumig)) $datumig=date('Y-m-d');

	$min=$_POST['min'];
	$leptet=$_POST['leptet'];
	if($min<0 or empty($min)) $min=0;
	if(empty($leptet)) $leptet=40;

	$next=$min+$leptet;
	$prev=$min-$leptet;
	
	$urlap.="\n<input type=hidden name=m_id value=$m_id><input type=hidden name=sessid value=$sessid>";
	$urlap.="\n<input type=hidden name=m_op value=kereso>";
	$urlap.="\n<span class=kiscim>Dátum:</span> <input type=text name=datumtol value='$datumtol' size=10 maxlength=10 class=urlap><span class=alap>-tól</span> &nbsp; <input type=text name=datumig value='$datumig' size=10 maxlength=10 class=urlap><span class=alap>-ig</span>";
	$urlap.="\n&nbsp; &nbsp; &nbsp; <span class=kiscim>Kulcsszó: </span><input type=text name=kulcsszo value='$kulcsszo' size=20 class=urlap>";
	$urlap.="\n<br><img src=img/space.gif width=5 height=8><br><span class=kiscim>Rovatok: </span>";
	$query="select id,nev from rovatkat where rovat=0 order by sorszam"; //Rovatok
	$lekerdez=mysql_query($query);
	while(list($id,$nev)=mysql_fetch_row($lekerdez)) {
		$urlap.="\n<input type=checkbox name=rovatT[] value=$id";
		if(@in_array($id,$rovatT)) $urlap.=' checked';
		$urlap.="><span class=alap>&nbsp;$nev  &nbsp;</span>";
	}

	$urlap.="\n<br><img src=img/space.gif width=5 height=8><br><span class=kiscim>Kategóriák: </span>";
	$query1="select id,nev from rovatkat where rovat>0 order by sorszam"; //Fõkategóriák
	$lekerdez1=mysql_query($query1);
	while(list($id,$nev)=mysql_fetch_row($lekerdez1)) {
		$urlap.="\n<input type=checkbox name=kategoriaT[] value=$id";
		if(@in_array($id,$kategoriaT)) $urlap.=' checked';
		$urlap.="><span class=alap>&nbsp;$nev  &nbsp;</span>";
		$a++;
		if($a%6==0) $urlap.='<br>';
	}

	$urlap.="<br><div align=right><input type=image src=$design_url/img/keresgomb.jpg border=0> &nbsp; &nbsp; &nbsp;</div><img src=img/space.gif width=5 height=10>";

	$kod_lista="<form method=post><tr><td colspan=3 bgcolor=#ECE5C8>$urlap</td></tr></form>";

	$adatT[0]="Hírkeresõ";
	$adatT[1]='#';
	$adatT[2]=$kod_lista;
	$tipus='hirlistadoboz';
	$kod.=formazo($adatT,$tipus);
	$kod_lista='';


//Lista
	$datumtol=$datumtol.' 0:00:00';
	$datumig=$datumig.' 23:59:59';
	$most=date('Y-m-d H:i:s');
	if($most<$datumig) $datumig=$most;
	if(!empty($kulcsszo)) {
		$feltetelT[]="(cim like '%$kulcsszo%' or intro like '%$kulcsszo%' or szoveg like '%$kulcsszo%')";
		$kulcs="&kulcsszo=$kulcsszo";
	}
	if(is_array($rovatT)) {
		foreach($rovatT as $ertek) {
			$feltetel1T[]="rovatkat like '%-$ertek-%'";
		}
	}
	if(is_array($kategoriaT)) {
		foreach($kategoriaT as $ertek) {
			$feltetel2T[]="rovatkat like '%-$ertek-%'";
		}
	}

	if(is_array($feltetel1T)) $feltetelT[]='('.implode(' or ',$feltetel1T).')';
	if(is_array($feltetel2T)) $feltetelT[]='('.implode(' or ',$feltetel2T).')';
	if(is_array($feltetelT)) $feltetel='and '.implode(' and ',$feltetelT);
	$query="select id,cim,datum from hirek where ok='i' and datum>='$datumtol' and datum<='$datumig' $feltetel order by datum desc";
	if(!$lekerdez=mysql_query($query)) echo 'HIBA!<br>'.mysql_error();
	$mennyi=mysql_num_rows($lekerdez);
	if($mennyi>$leptet) {
		$query.=" limit $min,$leptet";
		if(!$lekerdez=mysql_query($query)) echo 'HIBA!<br>'.mysql_error();
	}
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
		$adatT[1]="?m_op=view&id=$id$kulcs$holvan$linkveg";
		$tipus='hirlista';
		$kod_lista.=formazo($adatT,$tipus);
	}
	if($mennyi==0) {
		$kod_lista="<tr><td colspan=3><span class=alap>Nincs találat.</span></td></tr>";
	}
	else {
		$kezd=$min+1;
		$veg=$next;
		if($next>$mennyi) $veg=$mennyi;
		$szamolas="($kezd-$veg / $mennyi)";
		$kod_lista.='<tr><td colspan=3><img src=img/space.gif width=5 height=10><br><table width=250><tr><td>';
		if($min>0) {
			$kod_lista.="\n<form method=post><input type=hidden name=sessid value=$sessid><input type=hidden name=m_id value=$m_id><input type=hidden name=m_op value=kereso><input type=hidden name=kulcsszo value='$kulcsszo'><input type=hidden name=datumtol value=$datumtol><input type=hidden name=datumig value=$datumig><input type=hidden name=min value=$prev>";
			$kod_lista.="\n<input type=submit value=Elõzõ class=urlap><input type=text name=leptet value=$leptet class=urlap size=2></form>";
		}
		else {
			$kod_lista.='&nbsp;';
		}
		$kod_lista.='</td><td>';
		if($mennyi>$next) {
			$kod_lista.="\n<form method=post><input type=hidden name=sessid value=$sessid><input type=hidden name=m_id value=$m_id><input type=hidden name=m_op value=kereso><input type=hidden name=kulcsszo value='$kulcsszo'><input type=hidden name=datumtol value=$datumtol><input type=hidden name=datumig value=$datumig><input type=hidden name=min value=$next>";
			$kod_lista.="\n<input type=submit value=Következõ class=urlap><input type=text name=leptet value=$leptet class=urlap size=2></form>";
		}
		else $kod_lista.='&nbsp;';
		$kod_lista.='</td></tr></table></td></tr>';

	}

	if(isset($_POST['datumtol']) or isset($_POST['kulcsszo'])) {
		$adatT[0]="Találatok $szamolas";
		$adatT[1]='#';
		$adatT[2]=$kod_lista;
		$tipus='hirlistadoboz';
		$kod.='<br>'.formazo($adatT,$tipus);
	}

	$tartalom=$kod;
	$tmpl_file = $design_url.'/sablon_hirlista.htm';
	$thefile = implode("", file($tmpl_file));
	$thefile = addslashes($thefile);
		$thefile = "\$r_file=\"".$thefile."\";";
	eval($thefile);
		
	$kod = $r_file;

	return $kod;
}

function hirek_archivlista() {
	global $linkveg,$db_name,$m_id,$_POST,$design_url;

	$datum=$_POST['datum'];
	$nyelv=$lang;
	if(empty($nyelv)) $nyelv='hu';

	if(empty($datum)) $kod=hirek_kereso();
	else {		
		$ma=date('Y-m-d');
	
		$kezd=$datum.' 0:00:00';
		if($datum==$ma) $veg=date('Y-m-d H:i:s');
		else $veg=$datum.' 23:59:59';
		$most=date('Y-m-d H:i:s');
		if($most<$veg) $veg=$most;
	
		$ev=substr($datum,0,4);
		$honap=substr($datum,5,2);
		$nap=substr($datum,8,2);
	
		$ido=mktime(0,0,0,$honap,$nap,$ev);
		$honapnev=alapnyelv("ho$honap");
		$napszam=date('w',$ido);
		$napnev=alapnyelv("nap$napszam");	
		if($nap[0]=='0') $nap=$nap[1];

		$datumkiiras="$ev. $honapnev $nap. $napnev";

		$query="select id,cim,datum from hirek where ok='i' and datum>='$kezd' and datum<='$veg' and ok='i' order by datum desc";
		if(!$lekerdez=mysql_query($query)) echo 'HIBA!<br>'.mysql_error();
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
		if($mennyi==0) {
			$kod_lista="<tr><td colspan=3><span class=alap>Erre a napra nincs hírünk az adatbázisban</span></td></tr>";			
		}
		
		$adatT[0]="Archívum: $datumkiiras";
		$adatT[1]='#';
		$adatT[2]=$kod_lista;
		$tipus='hirlistadoboz';
		$tartalom=formazo($adatT,$tipus);

		$tmpl_file = $design_url.'/sablon_hirlista.htm';
		$thefile = implode("", file($tmpl_file));
		$thefile = addslashes($thefile);
		$thefile = "\$r_file=\"".$thefile."\";";
		eval($thefile);

		$kod = $r_file;
	}

	return $kod;
}


switch($m_op) {
    case 'index':
        $tartalom=hirek_index();
        break;

	case 'view':
		$tartalom=hirek_view();
		break;

	case 'kereso':
		$tartalom=hirek_kereso();
		break;

	case 'hirlista':
		$tartalom=hirek_hirlista();
		break;

	case 'archivlista':
		$tartalom=hirek_archivlista();
		break;
}

?>
