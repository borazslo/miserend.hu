<?php

function atir() {
	global $db_name;

	$query="select id,plebania from templomok where egyhazmegye=17 or egyhazmegye=18";
	$lekerdez=mysql_db_query($db_name,$query);
	while(list($id,$plebania)=mysql_fetch_row($lekerdez)) {
		$plebania=str_replace('<b>Plébánia:</b>','<b>Paróchia:</b>',$plebania);
		mysql_db_query($db_name,"update templomok set plebania='$plebania' where id='$id'");
		echo "<br>$a. ($id) megvan";
	}
	echo "<br>kész";
}

function hirek_index() {
	global $linkveg,$m_id,$db_name;

	$kod=hirek_adminmenu();

	return $kod;
}

function hirek_adminmenu() {
	global $m_id,$linkveg,$db_name;

	$menu.='<span class=alcim>Hírek szerkesztése</span><br><br>';
	$menu.="<a href=?m_id=$m_id&m_op=add$linkveg class=kismenulink>Új hír feltöltése</a><br>";
	$menu.="<a href=?m_id=$m_id&m_op=mod$linkveg class=kismenulink>Meglévõ hír módosítása, törlése</a><br>";

	$query="select id from hirek where ok='f'";
	$lekerdez=mysql_db_query($db_name,$query);
	if(mysql_num_rows($lekerdez)>0) {
		$jelzes.="<img src=img/ora.gif alt='várakozó hírek vannak' align=absmiddle> ";
		$menu.="$jelzes <a href=?m_id=$m_id&m_op=mod&ok=f$linkveg class=kismenulink>Feltöltött hírek engedélyezése</a><br>";
	}
		
	$menu.="<a href=?m_id=$m_id&m_op=rovat$linkveg class=kismenulink>Rovatok (fõmenü) szerkesztése</a><br>";
	$menu.="<a href=?m_id=$m_id&m_op=kulcsszavak$linkveg class=kismenulink>Kulcsszavak kezelése</a><br>";

	$menu.="<br><form method=get action=hirlevel_proba.php><input type=text name=To value=g@florka.hu class=urlap><input type=submit value='Hírlevél próbaküldés' class=urlap></form><br>";

	$adatT[2]=$menu;
	$tipus='doboz';
	$tartalom.=formazo($adatT,$tipus);	

	return $tartalom;
}

function hirek_addmegse() {
	global $sessid,$linkveg,$m_id,$db_name,$_GET;

	$ki=$_GET['ki'];
	$mikor=$_GET['mikor'];
	$mikor=rawurldecode($mikor);
	$hid=$_GET['hid'];
	$query="update hirek set megnyitva='$ki', megnyitvamikor='$mikor' where id='$hid'";
	if(!mysql_db_query($db_name,$query)) echo 'HIBA!<br>'.mysql_error();

	$kod=hirek_mod();
	return $kod;
}

function hirek_add($hid) {
	global $sessid,$linkveg,$m_id,$db_name,$onload,$u_login;	

	if(!is_numeric($hid) and !empty($hid)) {
		$kod.="<p class=hiba>HIBA!</p>";
	}
	else {
		if($hid>0) $tartalomkod.=include('editscript2.php');
		include_once('moduls/urlap_hirek.php');
		$kod.=hirek_urlap($hid);
	}

	return $kod;
}

function hirek_adding() {
	global $db_name,$u_login,$u_beosztas;

	include_once('moduls/urlap_hirek.php');
	$id=hirekadding();

	if(is_numeric($id) and $id>0) {
		$kod=hirek_add($id);
	}
	elseif($id==0) {
		$kod=hirek_mod();
	}
	else {
		$kod=$id;
	}

	return $kod;
}

function hirek_mod() {
	global $db_name,$linkveg,$m_id,$u_login,$sid;

	$hirkat=$_POST['hirkat'];
	if(empty($hirkat)) $hirkat='mind';
	$kulcsszo=$_POST['kkulcsszo'];	
	$hirkapcs=$_POST['hirkapcs'];
	if(empty($hirkapcs)) $hirkapcs='mind';
	$allapot=$_POST['allapot'];

	$ok=$_POST['ok'];
	if(!isset($ok)) $ok=$_GET['ok'];

	$min=$_POST['min'];
	if(!isset($min)) $min=$_GET['min'];
	if($min<0 or !isset($min)) $min=0;

	$leptet=$_POST['leptet'];
	if(!isset($leptet)) $leptet=$_GET['leptet'];
	if(!isset($leptet)) $leptet=50;

	$next=$min+$leptet;
	$prev=$min-$leptet;
	
	$most=date('Y-m-d H:i:s');


	$kiir.="<span class=kiscim>A lista szûkíthetõ rovatok, témák, állapot szerint, illetve kulcsszó alapján:</span><br>";

	$kiir.="\n<form method=post><input type=hidden name=m_id value='$m_id'><input type=hidden name=m_op value=mod>";

	$kiir.="\n<select name=hirkat class=urlap><option value=mind>Mind</option>";
	$query_kat="select id,nev,rovat from rovatkat where ok='i' order by rovat,sorszam";
	$lekerdez_kat=mysql_db_query($db_name,$query_kat);
	while(list($kid,$knev,$krovat)=mysql_fetch_row($lekerdez_kat)) {
		$kiir.="<option value=$kid";
		if($kid==$hirkat) $kiir.=" selected";
		$kiir.=">";
		if($krovat>0 and $kfokat==0) $kiir.="->";
		elseif($kfokat>0 and $kkat==0) $kiir.="--->";
		$kiir.="$knev</option>";
	}
	$kiir.="</select>";
	
	$kiir.="\n<select name=hirkapcs class=urlap><option value=mind>Mind</option>";
	$query="select id,nev from kulcsszo order by nev";
	$lekerdez=mysql_db_query($db_name,$query);
	while(list($ksz_id,$ksz_nev)=mysql_fetch_row($lekerdez)) {
		$kiir.="<option value=$ksz_id";
		if($ksz_id==$hirkapcs) $kiir.=" selected";
		$kiir.=">$ksz_nev</option>";
	}
	$kiir.='</select>';

	//Állapot szerinti szûrés
	$kiir.="\n<br><select name=allapot class=urlap><option value=0>Mind</option><option value=i";
	if($allapot=='i') $kiir.=" selected";
	$kiir.=">csak engedélyezett hírek</option><option value=f";
	if($allapot=='f') $kiir.=" selected";
	$kiir.=">áttekintésre várók</option><option value=n";
	if($allapot=='n') $kiir.=" selected";
	$kiir.=">letiltott hírek</option><option value=e";
	if($allapot=='e') $kiir.=" selected";
	$kiir.=">észrevételezett hírek</option><option value=ef";
	if($allapot=='ef') $kiir.=" selected";
	$kiir.=">javítás alatt lévõ hírek</option>";
	$kiir.="</select>";
	
	if(isset($ok)) $kiir.="<input type=hidden name=ok value=$ok>";
	$kiir.=" <input type=text name=kkulcsszo value='$kulcsszo' class=urlap size=20> <input type=submit value=Lista class=urlap></form><br>";

	//Észrevételek lekérdezése
	$querye="select distinct(hol_id) from eszrevetelek where hol='hirek'";
	if(!$lekerdeze=mysql_db_query($db_name,$querye)) echo "HIBA!<br>$querym<br>".mysql_error();
	while(list($hir)=mysql_fetch_row($lekerdeze)) {
		$vaneszrevetelT[$hir]=true;
	}

	if($hirkat!='mind' and isset($hirkat)) $feltetelT[]="rovatkat like '%-$hirkat-%'";
	if(!empty($kulcsszo)) $feltetelT[]="(cim like '%$kulcsszo%' or intro like '%$kulcsszo%' or szoveg like '%$kulcsszo%' or feltette like '%$kulcsszo%')";
	if($hirkapcs!='mind') $feltetelT[]="kulcsszo like '%-$hirkapcs-%'";
	if(!empty($allapot)) {
		if($allapot=='e') $feltetelT[]="eszrevetel='i'";
		elseif($allapot=='ef') $feltetelT[]="eszrevetel='f'";
		else $feltetelT[]="ok='$allapot'";
	}
	
	if(is_array($feltetelT)) $feltetel=' where '.implode(' and ',$feltetelT);

	$query="select id,cim,datum,ok,eszrevetel,rovatkat,kiemelt,szamlalo,napiszamlalo,megnyitva,megnyitvamikor from hirek $feltetel order by datum desc";
	$lekerdez=mysql_db_query($db_name,$query);
	$mennyi=mysql_num_rows($lekerdez);
	if($mennyi>$leptet) {
		$query.=" limit $min,$leptet";
		$lekerdez=mysql_db_query($db_name,$query);
	}
	$kezd=$min+1;
	$veg=$min+$leptet;
	if($veg>$mennyi) $veg=$mennyi;
	if($mennyi>0) $kiir.="<span class=alap>Összesen: $mennyi találat<br>Listázás: $kezd - $veg</span><br><br>";
	else $kiir.="<span class=alap>Jelenleg nincs módosítható hír az adatbázisban.</span>";
	$megnyitvalejar=date('Y-m-d H:i:s',time()-10800);
	while(list($mid,$cim,$datum,$ok,$eszrevetel,$rovatkat,$kiemelt,$szamlalo,$napiszamlalo,$megnyitva,$megnyitvamikor)=mysql_fetch_row($lekerdez)) {
		$jelzes='';
		if($eszrevetel=='i') $jelzes.="<a href=\"javascript:OpenScrollWindow('naplo.php?kod=hirek&id=$mid&sid=$sid',550,500);\"><img src=img/csomag.gif title='Új észrevételt írtak hozzá!' align=absmiddle border=0></a> ";		
		elseif($eszrevetel=='f') $jelzes.="<a href=\"javascript:OpenScrollWindow('naplo.php?kod=hirek&id=$mid&sid=$sid',550,500);\"><img src=img/csomagf.gif title='Észrevétel javítása folyamatban!' align=absmiddle border=0></a> ";		
		elseif($vaneszrevetelT[$mid]) $jelzes.="<a href=\"javascript:OpenScrollWindow('naplo.php?kod=hirek&id=$mid&sid=$sid',550,500);\"><img src=img/csomag1.gif title='Észrevételek!' align=absmiddle border=0></a> ";				
		//Jelzés beállítása -> lampa = nincs kategorizalva, ora = varakozik ok=n, tilos = megjelenhet X, jegyzettömb - szerkesztés alatt (megnyitva)
		if(!empty($megnyitva) and $megnyitvamikor>=$megnyitvalejar) $jelzes.="<img src=img/edit.gif title='Megnyitva: $megnyitva [".substr($megnyitvamikor,0,-3)."]' align=absmiddle> ";
		if(!strstr($rovatkat,'-1-') and !strstr($rovatkat,'-2-') and !strstr($rovatkat,'-3-') and !strstr($rovatkat,'-4-') and !strstr($rovatkat,'-5-') and !strstr($rovatkat,'-6-')) $jelzes.="<img src=img/lampa.gif title='Nincs rovatban!' align=absmiddle> ";
		if($ok=='n') $jelzes.="<img src=img/tilos.gif title='Megjelenés nincs engedélyezve!' align=absmiddle> ";
		if($ok=='f') $jelzes.="<img src=img/ora.gif title='Feltöltött hír, áttekintésre vár!' align=absmiddle> ";
		

		if($datum<$most and !$vonal) {
		    $kiir.='<hr>';
		    $vonal=true;
		}
		$datum=substr($datum,0,16);
		$kiir.="\n$jelzes <a href=?m_id=$m_id&m_op=addhirek&hid=$mid$linkveg class=link><b>- $cim</b>($datum)</a><span class=kicsi> [<a title=napiszámláló>$napiszamlalo</a>/<a title=számláló>$szamlalo</a>]</span> - <a href=?m_id=$m_id&m_op=del&hid=$mid$linkveg class=link><img src=img/del.jpg border=0 alt=Töröl align=absmiddle> töröl</a><br>";
	}

	$kiir.='<br>';
	if($min>0) {
		$kiir.="\n<form method=post><input type=hidden name=m_id value=$m_id><input type=hidden name=m_op value=mod><input type=hidden name=sessid value=$sessid><input type=hidden name=kkulcsszo value='".$_POST['kkulcsszo']."'><input type=hidden name=hirkat value=$hirkat><input type=hidden name=min value=$prev>";
		if(isset($ok)) $kiir.="<input type=hidden name=ok value=$ok>";
		if(isset($hirkapcs)) $kiir.="<input type=hidden name=hirkapcs value=$hirkapcs>";
		$kiir.="\n<input type=submit value=Elõzõ class=urlap><input type=text size=2 value=$leptet name=leptet class=urlap></form>";
	}
	if($mennyi>$min+$leptet) {
		$kiir.="\n<form method=post><input type=hidden name=m_id value=$m_id><input type=hidden name=m_op value=mod><input type=hidden name=sessid value=$sessid><input type=hidden name=kkulcsszo value='".$_POST['kkulcsszo']."'><input type=hidden name=hirkat value=$hirkat><input type=hidden name=min value=$next>";
		if(isset($ok)) $kiir.="<input type=hidden name=ok value=$ok>";
		if(isset($hirkapcs)) $kiir.="<input type=hidden name=hirkapcs value=$hirkapcs>";
		$kiir.="\n<input type=submit value=Következõ class=urlap><input type=text size=2 value=$leptet name=leptet class=urlap></form>";
	}

	$adatT[2]='<span class=alcim>Hírek módosítása</span><br><br>'.$kiir;
	$tipus='doboz';
	$tartalom.=formazo($adatT,$tipus);	
	
	$kod=$tartalom;


	return $kod;
}



function hirek_del() {
	global $_GET,$db_name,$linkveg,$m_id,$u_login,$u_beosztas;

	$hid=$_GET['hid'];

	$kiir="<span class=alcim>Hírek törlése</span><br><br>";
	$kiir.="\n<span class=kiscim>Biztosan törölni akarod a következõ hírt?</span>";
		
	$query="select cim from hirek where id='$hid'";
	if($u_beosztas=='hb')
		$query.=" and feltette='$u_login' and ok='n'";
	list($cim)=mysql_fetch_row(mysql_db_query($db_name,$query));

	if(!empty($cim)) {
		$kiir.="\n<br><br><span class=alap>$cim</span>";

		$kiir.="<br><br><a href=?m_id=$m_id&m_op=delete&hid=$hid$linkveg class=link>Igen</a> - <a href=?m_id=$m_id&m_op=mod$linkveg class=link>NEM</a>";
	}
	else {
		$kiir.="<br><br><span class=hiba>HIBA! Ilyen hír nincs!</span>";
	}

	$adatT[2]=$kiir;
	$tipus='doboz';
	$tartalom.=formazo($adatT,$tipus);	
	
	$kod=$tartalom;

	return $kod;
}

function hirek_delete() {
	global $_GET,$db_name,$u_login,$u_beosztas;

	$id=$_GET['hid'];
	$query="delete from hirek where id='$id'";
	if($u_beosztas=='hb')
		$query.=" and feltette='$u_login' and ok='n'";
	mysql_db_query($db_name,$query);

	//Fájlokat és képeket is törölni kell!

	//Könyvtár tartalmát beolvassa
	$konyvtar="fajlok/hirek/$id";
		if(is_dir($konyvtar)) {
			$handle=opendir($konyvtar);
			while ($file = readdir($handle)) {
				if ($file!='.' and $file!='..') {
					@unlink("$konyvtar/$file");
				}
			}
			closedir($handle);
		}
	

	//Könyvtár tartalmát beolvassa
	$konyvtar="kepek/hirek/$id";
		if(is_dir($konyvtar)) {
			$handle=opendir($konyvtar);
			while ($file = readdir($handle)) {
				if ($file!='.' and $file!='..' and $file!='fokep' and $file!='kicsi') {
					unlink("$konyvtar/$file");
				}
			}
			closedir($handle);
		}
	$konyvtar="kepek/hirek/$id/kicsi";
		if(is_dir($konyvtar)) {
			$handle=opendir($konyvtar);
			while ($file = readdir($handle)) {
				if ($file!='.' and $file!='..') {
					unlink("$konyvtar/$file");
				}
			}
			closedir($handle);
		}

	@unlink("kepek/hirek/$id/fokep/kep.jpg");
	@unlink("kepek/hirek/$id/fokep/k1.jpg");
	@unlink("kepek/hirek/$id/fokep/k2.jpg");
	@unlink("kepek/hirek/$id/fokep/k3.jpg");
	@unlink("kepek/hirek/$id/fokep/n.jpg");

	$kod=hirek_mod();

	return $kod;
}

function hirek_rovat() {
	global $db_name,$linkveg,$m_id,$_POST,$u_beosztas;

	$kiir.="<a href=?m_id=$m_id&m_op=rovatadd$linkveg class=link><b>Új rovat hozzáadása</b></a><br><br>";

	$query="select id,nev,ok,szamlalo from rovatkat where rovat=0 order by sorszam";
	$lekerdez=mysql_db_query($db_name,$query);
	while(list($rid,$rnev,$rok,$rszamlalo)=mysql_fetch_row($lekerdez)) {
		if($rok!='i') $OK="<span class=alap><font color=red>(várakozó)</font></span>";
		else $OK="<span class=alap><font color=green>(aktív)</font></span>";
		$kiir.="\n<a href=?m_id=$m_id&m_op=rovatadd&rid=$rid$linkveg class=link><b> $rnev</b></a><span class=kicsi> <font color=blue>[$rszamlalo]</font></span> $OK - <a href=?m_id=$m_id&m_op=rovatdel&rid=$rid$linkveg class=link><img src=img/del.jpg border=0 alt=Töröl align=absmiddle> töröl</a><br>";		

		$query1="select id,nev,ok,szamlalo from rovatkat where rovat like '%$rid%' order by sorszam";
		$lekerdez1=mysql_db_query($db_name,$query1);
		while(list($fkid,$fknev,$fkok,$fkszamlalo)=mysql_fetch_row($lekerdez1)) {
			if($fkok!='i') $OK="<span class=alap><font color=red>(várakozó)</font></span>";
			else $OK="<span class=alap><font color=green>(aktív)</font></span>";
			$kiir.="\n<a href=?m_id=$m_id&m_op=rovatadd&rid=$fkid$linkveg class=link><b>- - > $fknev</b></a><span class=kicsi> <font color=blue>[$fkszamlalo]</font></span> $OK - <a href=?m_id=$m_id&m_op=rovatdel&rid=$fkid$linkveg class=link><img src=img/del.jpg border=0 alt=Töröl align=absmiddle> töröl</a><br>";							
		}
		$kiir.='<br>';
	}

	$adatT[2]='<span class=alcim>Rovatok szerkesztése</span><br><br>'.$kiir;
	$tipus='doboz';
	$tartalom.=formazo($adatT,$tipus);	
	
	$kod=$tartalom;


	return $kod;
}

function hirek_rovatadd($rid) {
	global $sessid,$linkveg,$m_id,$db_name,$u_beosztas;

	if($rid>0) {
		$query="select nev,rovat,sorszam,listao,lista,datumkiir,friss,k3,ok from rovatkat where id='$rid'";	list($nev,$rovat,$sorszam,$listao,$lista,$datumkiir,$friss,$k3,$ok)=mysql_fetch_row(mysql_db_query($db_name,$query));
		$hol="$rovat-$fokat-$kat";
	}
	else {
		$menuben='b';
		$datumkiir='i';
		$ok='i';
		$sorszam=50;
	}

	$urlap="\n<form method=post>";

	$urlap.="\n<input type=hidden name=m_id value=$m_id><input type=hidden name=sessid value=$sessid>";
	$urlap.="\n<input type=hidden name=m_op value=rovatadding><input type=hidden name=rid value=$rid>";
	
	$urlap.='<table>';
//Cím
	$urlap.="\n<tr><td><div class=kiscim align=right>Rovat címe:</div></td><td><input type=text name=nev value=\"$nev\" class=urlap size=50 maxlength=50></td></tr>";

//Hol
	$urlap.="\n<tr><td><div class=kiscim align=right>Hol:</div></td><td><select name=hol class=urlap><option value='0-0-0'>Rovat</option>";
	$query="select id,nev from rovatkat where rovat=0 order by sorszam";
	$lekerdez=mysql_db_query($db_name,$query);
	while(list($rid,$rnev)=mysql_fetch_row($lekerdez)) {
		$urlap.="\n<option value='$rid-0-0'";
		if($hol=="$rid-0-0") $urlap.=" selected";
		$urlap.=">$rnev</option>";
	}
	$urlap.='</select></td></tr>';

//Sorrend
	$urlap.="\n<tr><td><div class=kiscim align=right>Sorszám (sorrend):</div></td><td><input type=text name=sorszam value=\"$sorszam\" class=urlap size=2 maxlength=2></td></tr>";

//Listabal
	$urlap.="\n<tr><td><div class=kiscim align=right>Listázandó hírek száma oldalt:</div></td><td><input type=text name=listao value=\"$listao\" class=urlap size=2 maxlength=2></td></tr>";
//Lista
	$urlap.="\n<tr><td><div class=kiscim align=right>Listázandó hírek száma középen:</div></td><td><input type=text name=lista value=\"$lista\" class=urlap size=2 maxlength=2></td></tr>";

//Dátumkiírás látszik-e
	$urlap.="\n<tr><td><div class=kiscim align=right>Dátumkiírás listában:</div></td><td><input type=checkbox name=datumkiir value='i' class=urlap";
	if($datumkiir!='n') $urlap.=' checked';
	$urlap.="></td></tr>";

//címek
	$urlap.="\n<tr><td bgcolor=#efefef><div class=kiscim align=right><i>Friss hírek:</i></div></td><td bgcolor=#efefef><input type=text name=friss value=\"$friss\" class=urlap size=40 maxlength=50></td></tr>";
	$urlap.="\n<tr><td bgcolor=#efefef><div class=kiscim align=right><i>További fontos:</i></div></td><td bgcolor=#efefef><input type=text name=k3 value=\"$k3\" class=urlap size=40 maxlength=50></td></tr>";

//Engedélyezés (jogosultság!)
	$urlap.="\n<tr><td><div class=kiscim align=right>Engedélyezés:</div></td><td><input type=checkbox name=ok value='i' class=urlap";
	if($ok!='n') $urlap.=' checked';
	$urlap.="></td></tr>";

	$urlap.='</table>';

	$urlap.="\n<br><span class=alap><b>FIGYELEM!</b> Áthelyezésnél a kapcsolódó rovatok is áthelyezõdnek,<br>illetve almenübe helyezésnél egy szintre kerülnek azonos helyen!</span><br><br><input type=submit value=Mehet class=urlap></form>";

	$adatT[2]='<span class=alcim>Rovat szerkesztése</span><br><br>'.$urlap;
	$tipus='doboz';
	$tartalom.=formazo($adatT,$tipus);	
	
	$kod=$tartalom;

	return $kod;
}

function hirek_rovatadding() {
	global $_POST,$db_name,$u_beosztas;

if($u_beosztas=='fsz' or $u_beosztas=='sz') {

	$hiba=false;
	$rid=$_POST['rid'];
	$nev=$_POST['nev'];
	$hol=$_POST['hol'];
	$holT=explode('-',$hol);
	$rovat=$holT[0];
	$fokat=$holT[1];
	$kat=$holT[2];
	$menuben=$_POST['menuben'];
	$friss=$_POST['friss'];
	$k3=$_POST['k3'];
	$lista=$_POST['lista'];
	$listao=$_POST['listao'];
	$nyelv=$_POST['nyelv'];
	$ok=$_POST['ok'];
	if($ok!='i') $ok='n';
	$datumkiir=$_POST['datumkiir'];
	if($datumkiir!='i') $datumkiir='n';
	$sorszam=$_POST['sorszam'];

	if(empty($nev)) {
		$hiba=true;
		$hibauzenet.='<br>Nem lett kitöltve a cím mezõ!';
	}

	if($hiba) {
		$txt.="<span class=hiba>HIBA a rovat szerkesztésénél!</span><br>";
		$txt.='<span class=alap>'.$hibauzenet.'</span>';
		$txt.="<br><br><a href=javascript:history.go(-1); class=link>Vissza</a>";
	
		$adatT[2]='<span class=alcim>Rovatok szerkesztése</span><br><br>'.$txt;
		$tipus='doboz';
		$kod.=formazo($adatT,$tipus);	
	}
	else {
		if($rid>0) {
			$uj=false;
			$parameter1='update';
			$parameter2="where id='$rid'";
		}
		else {
			$uj=true;
			$parameter1='insert';
			$parameter2="";
		}

		$query="$parameter1 rovatkat set nev='$nev', rovat='$rovat', fokat='$fokat', kat='$kat', datumkiir='$datumkiir', sorszam='$sorszam', menuben='$menuben', friss='$friss', k3='$k3', lista='$lista', listao='$listao', lang='$nyelv', ok='$ok' $parameter2";
		mysql_db_query($db_name,$query);
		if($uj) $rid=mysql_insert_id();
		else {
			//Az esetleges kapcsolódó rovatokat is átkategrizáljuk
			if($kat>0) $hova="rovat='$rovat', fokat='$fokat', $kat='$kat'";
			if($fokat>0) $hova="rovat='$rovat', fokat='$fokat', kat='$rid'";
			elseif($rovat>0) $hova="rovat='$rovat', fokat='$rid', kat='0'";
			elseif($rovat==0) $hova="rovat='$rid', fokat=0, $kat=0";
			$query="update rovatkat set $hova where rovat=$rid or fokat=$rid or kat=$rid";
			mysql_db_query($db_name,$query);
		}

		$kod=hirek_rovatadd($rid);
	}

	return $kod;
}//jogok
}

function hirek_rovatdel() {
	global $_GET,$db_name,$linkveg,$m_id,$u_beosztas;

if($u_beosztas=='fsz' or $u_beosztas=='sz') {

	$rid=$_GET['rid'];

	$kiir="<span class=alcim>Rovat törlése</span><br><br>";
	$kiir.="\n<span class=kiscim>Biztosan törölni akarod a következõ rovatot?</span>";
		
	$query="select nev from rovatkat where id='$rid'";
	list($nev)=mysql_fetch_row(mysql_db_query($db_name,$query));

	$kiir.="\n<br><br><span class=alap>$nev</span>";

	$kiir.="<br><br><a href=?m_id=$m_id&m_op=rovatdelete&rid=$rid$linkveg class=link>Igen</a> - <a href=?m_id=$m_id&m_op=rovat$linkveg class=link>NEM</a>";

	$adatT[2]=$kiir;
	$tipus='doboz';
	$tartalom.=formazo($adatT,$tipus);	
	
	$kod=$tartalom;

	return $kod;
}
}

function hirek_rovatdelete() {
	global $_GET,$db_name,$u_beosztas;

if($u_beosztas=='fsz' or $u_beosztas=='sz') {

	$id=$_GET['rid'];
	$query="delete from rovatkat where id='$id'";
	mysql_db_query($db_name,$query);

	$kod=hirek_rovat();

	return $kod;
}
}

function hirek_kulcsszavak() {
	global $sessid,$linkveg,$m_id,$db_name,$_POST;

	$ksznev=$_POST['ksznev'];
	$kszid=$_POST['kszid'];

	if(empty($ksznev) and $kszid>0) {
		mysql_db_query($db_name,"delete from kulcsszo where id='$kszid'");
	}
	elseif($kszid>0) {
		mysql_db_query($db_name,"update kulcsszo set nev='$ksznev' where id='$kszid'");
	}
	elseif(!empty($ksznev)) {
		mysql_db_query($db_name,"insert kulcsszo set nev='$ksznev'");
	}

	$query="select id,nev from kulcsszo order by nev";
	$lekerdez=mysql_db_query($db_name,$query);
	$szoveg="<table width=100%><tr><td colspan=2 align=center><form method=post><input type=text name=ksznev size=40 class=urlap><input type=submit value=Hozzáad class=urlap></form></td></tr><tr>";
	while(list($id,$nev)=mysql_fetch_row($lekerdez)) {
		if($a>0 and $a%2==0) $szoveg.='</tr><tr>';
		$szoveg.="\n<td width=50%><form method=post><input type=hidden name=kszid value=$id><input type=text name=ksznev value='$nev' size=40 class=urlap><input type=submit value=Módosít class=urlap></form></td>";
		$a++;
	}
	if($a%2==0) $szoveg.='</tr>';
	else $szoveg.='<td></td></tr>';
	$szoveg.='</table>';

	$adatT[2]=$szoveg;
	$tipus='doboz';
	$kod=formazo($adatT,$tipus);	

	return $kod;
}

function hirek_sorozatok() {
	global $sessid,$linkveg,$m_id,$db_name,$_POST;

	$snev=$_POST['snev'];
	$sid=$_POST['sid'];

	if(empty($snev) and $sid>0) {
		mysql_db_query($db_name,"delete from sorozatok where id='$sid'");
	}
	elseif($sid>0) {
		mysql_db_query($db_name,"update sorozatok set nev='$snev' where id='$sid'");
	}
	elseif(!empty($snev)) {
		mysql_db_query($db_name,"insert sorozatok set nev='$snev'");
	}

	$query="select id,nev from sorozatok order by nev";
	$lekerdez=mysql_db_query($db_name,$query);
	$szoveg="<table width=100%><tr><td colspan=2 align=center><form method=post><input type=text name=snev size=40 class=urlap><input type=submit value=Hozzáad class=urlap></form></td></tr><tr>";
	while(list($id,$nev)=mysql_fetch_row($lekerdez)) {
		if($a>0 and $a%2==0) $szoveg.='</tr><tr>';
		$szoveg.="\n<td width=50%><form method=post><input type=hidden name=sid value=$id><input type=text name=snev value='$nev' size=40 class=urlap><input type=submit value=Módosít class=urlap></form></td>";
		$a++;
	}
	if($a%2==0) $szoveg.='</tr>';
	else $szoveg.='<td></td></tr>';
	$szoveg.='</table>';

	$adatT[2]=$szoveg;
	$tipus='doboz';
	$kod=formazo($adatT,$tipus);	

	return $kod;
}


//Jogosultság ellenõrzése
if(strstr($u_jogok,'hirek')) {

switch($m_op) {
	case 'atir':
		atir();
		break;

    case 'index':
        $tartalom=hirek_index();
        break;

	case 'kulcsszavak':
		$tartalom=hirek_kulcsszavak();
		break;


	case 'add':
		$hid=$_GET['hid'];
        $tartalom=hirek_add($hid);
        break;

	case 'addhirek':
		$hid=$_GET['hid'];
        $tartalom=hirek_add($hid);
        break;

	case 'addmegse':
		$tartalom=hirek_addmegse();
		break;

    case 'mod':
        $tartalom=hirek_mod();
        break;

    case 'addinghirek':
        $tartalom=hirek_adding();
        break;

    case 'del':
        $tartalom=hirek_del();
        break;

	case 'delete':
        $tartalom=hirek_delete();
        break;

    case 'rovat':
			$tartalom=hirek_rovat();
        break;

    case 'rovatadd':
			$rid=$_GET['rid'];
		    $tartalom=hirek_rovatadd($rid);
        break;

    case 'rovatadding':
	        $tartalom=hirek_rovatadding();
        break;

    case 'rovatdel':
	        $tartalom=hirek_rovatdel();
        break;

    case 'rovatdelete':
	        $tartalom=hirek_rovatdelete();
        break;

}
}
else {
	$tartalom="\n<span class=hiba>HIBA! Nincs hozzá jogosultságod!</span>";
}

?>
