<?php

function miserend_index() {
	global $linkveg,$m_id,$db_name;

	$kod=miserend_adminmenu();

	return $kod;
}

function miserend_adminmenu() {
	global $m_id,$linkveg,$u_beosztas,$db_name;

	$menu.='<span class=alcim>Templomok és miserend szerkesztése</span><br><br>';

	$menu.="<a href=?m_id=$m_id&m_op=addtemplom$linkveg class=kismenulink>Új templom feltöltése</a><br>";
	$menu.="<a href=?m_id=$m_id&m_op=modtemplom$linkveg class=kismenulink>Meglévő templom módosítása, törlése, miserend hozzáadása, módosítása</a><br>";
	$menu.="<a href=?m_id=$m_id&m_op=events class=kismenulink>Kifejezések dátummá alakítása</a><br>";


	$adatT[2]=$menu;
	$tipus='doboz';
	$tartalom.=formazo($adatT,$tipus);	

	return $tartalom;
}

function miserend_addtemplom($tid) {
	global $sid,$linkveg,$m_id,$db_name,$onload,$u_beosztas,$u_login,$design_url;	
	
	$query="select id,nev from egyhazmegye where ok='i' order by sorrend";
	$lekerdez=mysql_db_query($db_name,$query);
	while(list($id,$nev)=mysql_fetch_row($lekerdez)) {
		$ehmT[$id]=$nev;
	}

	$query="select id,ehm,nev from espereskerulet";
	$lekerdez=mysql_db_query($db_name,$query);
	while(list($id,$ehm,$nev)=mysql_fetch_row($lekerdez)) {
		$espkerT[$ehm][$id]=$nev;
	}

	$query="select id,nev from orszagok where kiemelt='i'";
	$lekerdez=mysql_db_query($db_name,$query);
	while(list($id,$nev)=mysql_fetch_row($lekerdez)) {
		$orszagT[$id]=$nev;
	}

	$query="select id,megyenev,orszag from megye";
	$lekerdez=mysql_db_query($db_name,$query);
	while(list($id,$nev,$orszag)=mysql_fetch_row($lekerdez)) {
		$megyeT[$orszag][$id]=$nev;
	}

	$query="select megye_id,orszag,nev from varosok order by nev";
	$lekerdez=mysql_db_query($db_name,$query);
	while(list($megye,$orszag,$vnev)=mysql_fetch_row($lekerdez)) {		
		$vnev1=str_replace('Ö','O',$vnev);
		$vnev1=str_replace('Ő','O',$vnev1);
		$vnev1=str_replace('ö','o',$vnev1);
		$vnev1=str_replace('ő','o',$vnev1);
		$vnev1=str_replace('Ü','U',$vnev1);
		$vnev1=str_replace('Ü','U',$vnev1);
		$vnev1=str_replace('ü','u',$vnev1);
		$vnev1=str_replace('ű','u',$vnev1);
		$vnev1=str_replace('Á','A',$vnev1);
		$vnev1=str_replace('á','a',$vnev1);
		$vnev1=str_replace('É','E',$vnev1);
		$vnev1=str_replace('é','e',$vnev1);
		$vnev1=str_replace('í','i',$vnev1);
		$vnev1=str_replace('Ú','U',$vnev1);
		$vnev1=str_replace('ú','u',$vnev1);
		$vnev1=str_replace('Ó','O',$vnev1);
		$vnev1=str_replace('ó','o',$vnev1);

		$szam=rand(0,100);
		$vnev1.=$szam;
		$varos1T[$orszag][$megye][]=$vnev1;
		$varosT[$orszag][$megye][$vnev1]=$vnev;
	}
	foreach($varos1T as $orszagid=>$m1T) {
		foreach($m1T as $megyeid=>$v1T) {
			asort($v1T, SORT_STRING);
			$varos1T[$orszagid][$megyeid]=$v1T;
		}
	}

	if($tid>0) {
		$most=date("Y-m-d H:i:s");
		$urlap.=include('editscript2.php'); //Csak, ha módosításról van szó

		$query="select nev,ismertnev,turistautak,orszag,megye,varos,cim,megkozelites,plebania,pleb_url,pleb_eml,egyhazmegye,espereskerulet,leiras,megjegyzes,miseaktiv,misemegj,szomszedos1,szomszedos2,bucsu,nyariido,teliido,frissites,kontakt,kontaktmail,adminmegj,log,ok,letrehozta,megbizhato,eszrevetel,lat,lng from templomok LEFT JOIN terkep_geocode ON id=tid where id='$tid'";
		if(!$lekerdez=mysql_db_query($db_name,$query)) echo 'HIBA!<br>'.mysql_error();	list($nev,$ismertnev,$turistautak,$orszag,$megye,$varos,$cim,$megkozelites,$plebania,$pleb_url,$pleb_eml,$egyhazmegye,$espereskerulet,$szoveg,$megjegyzes,$miseaktiv,$misemegj,$szomszedos1,$szomszedos2,$bucsu,$nyariido,$teliido,$frissites,$kontakt,$kontaktmail,$adminmegj,$log,$ok,$feltolto,$megbizhato,$teszrevetel,$lat,$lng)=mysql_fetch_row($lekerdez);
	}
	else {
		$datum=date('Y-m-d H:i');
		$nyariido='2014-03-30';
		$teliido='2014-10-25';
		$urlapkieg="\n<input type=hidden name=elsofeltoltes value=i>";
	}

	$urlap.="\n<FORM ENCTYPE='multipart/form-data' method=post>";
	$urlap.=$urlapkieg;

	$urlap.="\n<input type=hidden name=m_id value=$m_id><input type=hidden name=sid value=$sid>";
	$urlap.="\n<input type=hidden name=m_op value=addingtemplom><input type=hidden name=tid value=$tid>";
	
	$urlap.='<table cellpadding=4>';

  //Észrevétel
	$jelzes = getRemarkMark($tid);
	$urlap.="\n<tr><td colspan=2><span class=kiscim>Észrevétel: </span>".$jelzes['html']."</td></tr>";

	if($tid>0) {
  //Megnéz
		$urlap.="\n<tr><td colspan=2><span class=kiscim>Nyilvános oldal megnyitása:</span><span class=alap> (új ablakban)</span> <a href=?templom=$tid class=link target=_blank><u>$nev</u></a></td></tr>";
	}

  //megjegyzés
	$urlap.="\n<tr><td bgcolor=#ECE5C8><div class=kiscim align=right>Megjegyzés:<br><a href=\"javascript:OpenNewWindow('sugo.php?id=1',200,300);\"><img src=img/sugo.gif border=0 title='Súgó'></a></div></td><td bgcolor=#ECE5C8><textarea name=adminmegj class=urlap cols=50 rows=3>$adminmegj</textarea><span class=alap> a szerkesztéssel kapcsolatosan</span></td></tr>";

  //kontakt
	$urlap.="\n<tr><td bgcolor=#efefef><div class=kiscim align=right>Felelős:<br><a href=\"javascript:OpenNewWindow('sugo.php?id=2',200,300);\"><img src=img/sugo.gif border=0 title='Súgó'></a></div></td><td bgcolor=#efefef><textarea name=kontakt class=urlap cols=50 rows=2>$kontakt</textarea><span class=alap> név és telefonszám</span><br><input type=text name=kontaktmail size=40 class=urlap value='$kontaktmail'><span class=alap> emailcím</span></td></tr>";
  //feltöltő
	if(empty($feltolto)) $feltolto=$u_login;
	$urlap.="\n<tr><td bgcolor=#efefef><div class=kiscim align=right>Feltöltő (jogosult):</div></td><td bgcolor=#efefef>";
	$urlap.="<select name=feltolto class=urlap><option value=''>Nincs</option>";
	$query="select login from user where ok='i' order by login";
	$lekerdez=mysql_db_query($db_name,$query);
	while(list($user)=mysql_fetch_row($lekerdez)) {
		$urlap.="<option value='$user'";
		if($user==$feltolto) $urlap.=" selected";
		$urlap.=">$user</option>";
	}
	$urlap.="</select> <input type=checkbox name=megbizhato class=urlap value=i";
	if($megbizhato!='n') $urlap.=" checked";
	$urlap.="><span class=alap> megbízható, nem kell külön engedélyezni</span></td></tr>";

  //név
	$urlap.="\n<tr><td bgcolor=#F5CC4C><div class=kiscim align=right>Templom neve:</div></td><td bgcolor=#F5CC4C><input type=text name=nev value=\"$nev\" class=urlap size=80 maxlength=150> <a href=\"javascript:OpenNewWindow('sugo.php?id=3',200,300);\"><img src=img/sugo.gif border=0 title='Súgó' align=absmiddle></a></td></tr>";
	$urlap.="\n<tr><td bgcolor=#FAE19C><div class=kiscim align=right>közismert neve:</div></td><td bgcolor=#FAE19C><input type=text name=ismertnev value=\"$ismertnev\" class=urlap size=80 maxlength=150> <a href=\"javascript:OpenNewWindow('sugo.php?id=4',200,300);\"><img src=img/sugo.gif border=0 title='Súgó' align=absmiddle></a><br><span class=alap>(Helyben elfogadott (ismert) templomnév, valamint település, vagy település résznév, amennyiben eltérő a település hivatalos nevétől, pl. <u>izbégi templom</u>)</span></td></tr>";

  //túristautak
	$urlap.="\n<tr><td bgcolor=#EFEFEF><div class=kiscim align=right>turistautak.hu ID:</div></td><td bgcolor=#EFEFEF><input type=text name=turistautak value=\"$turistautak\" class=urlap size=5 maxlength=10> <a href=\"javascript:OpenNewWindow('sugo.php?id=16',240,320);\"><img src=img/sugo.gif border=0 title='Súgó' align=absmiddle></a><br><span class=alap>(<a href=http://turistautak.hu/search.php?s=templom target=_blank class=link><u>ebből a listából</u></a> ha bennevan)</span></td></tr>";

  //cím
	$urlap.="\n<tr><td><div class=kiscim align=right>Templom címe:<br><a href=\"javascript:OpenNewWindow('sugo.php?id=5',200,300);\"><img src=img/sugo.gif border=0 title='Súgó' align=absmiddle></a></div></td><td>";

	//Egyházmegye
	$urlap.="<select name=egyhazmegye class=urlap onChange=\"if(this.value!=0) {";
	foreach($ehmT as $id=>$nev) {
		$urlap.="document.getElementById($id).style.display='none'; ";
	} 
	$urlap.="document.getElementById(this.value).style.display='inline'; document.getElementById('valassz').style.display='none'; } else {";
	foreach($ehmT as $id=>$nev) {
		$urlap.="document.getElementById($id).style.display='none'; ";
	} 
	$urlap.="document.getElementById('valassz').style.display='inline';}\"><option value=0>Nincs / nem tudom</option>";	
	foreach($ehmT as $id=>$nev) {
		$urlap.="<option value=$id";
		if($egyhazmegye==$id) $urlap.=' selected';
		$urlap.=">$nev</option>";
		
		if($egyhazmegye==$id) $display='inline';
		else $display='none';
		$espkerurlap.="<div id=$id style='display: $display'><select name=espkerT[$id] class=keresourlap><option value=0>Nincs / nem tudom</option>";
		if(is_array($espkerT[$id])) {			
			foreach($espkerT[$id] as $espid=>$espnev) {
				$espkerurlap.="<option value=$espid";
				if($espid==$espereskerulet) $espkerurlap.=' selected';
				$espkerurlap.=">$espnev</option>";
			}
		}
		$espkerurlap.="</select><span class=alap> (espereskerület)</span><br></div>";
	}
	$urlap.="</select><span class=alap> (egyházmegye)</span><br>";

	//Espereskerület
	$urlap.=$espkerurlap;

	//Ország
	$urlap.="<img src=img/space.gif width=5 height=8><br>\n<select name=orszag class=urlap onChange=\"if(this.value!=0) {";
	foreach($orszagT as $id=>$nev) {
		$urlap.="document.getElementById('m$id').style.display='none'; ";
	} 
	$urlap.="document.getElementById('m'+this.value).style.display='inline';} else {";
	foreach($orszagT as $id=>$nev) {
		$urlap.="document.getElementById('m$id').style.display='none'; ";
	} 
	$urlap.="}\"><option value=0>Nincs / nem tudom</option>";	
	foreach($orszagT as $id=>$nev) {
		$urlap.="\n<option value=$id";
		if($orszag==$id) $urlap.=' selected';
		$urlap.=">$nev</option>";
		
		if($orszag==$id) $mdisplay='inline';
		else $mdisplay='none';
		//megye
		if(is_array($megyeT[$id])) {

			$megyeurlap.="\n<div id=m$id style='display: $mdisplay'><select name=megyeT[$id] class=keresourlap onChange=\"if(this.value!=0) {";
			foreach($megyeT[$id] as $meid=>$nev) {
				$megyeurlap.="document.getElementById('v$meid').style.display='none'; ";
			} 
			$megyeurlap.="document.getElementById('v'+this.value).style.display='inline';} else {";
			foreach($megyeT[$id] as $meid=>$nev) {
				$megyeurlap.="document.getElementById('v$meid').style.display='none'; ";
			} 
			$megyeurlap.="}\"><option value=0>Nincs / nem tudom</option>";	
			foreach($megyeT[$id] as $meid=>$mnev) {
				$megyeurlap.="\n<option value='$meid'";
				if($meid==$megye) $megyeurlap.=' selected';
				$megyeurlap.=">$mnev</option>";

				//település
				if($megye==$meid) $vdisplay='inline';
				else $vdisplay='none';
		
				$varosurlap.="\n<div id=v$meid style='display: $vdisplay'><select name=varosT[$id][$meid] class=keresourlap><option value=0>Nincs / nem tudom</option>";	
				if(is_array($varos1T[$id][$meid])) {
					foreach($varos1T[$id][$meid] as $vnev1) {
						$varosurlap.="\n<option value='".$varosT[$id][$meid][$vnev1]."'";
						if($varosT[$id][$meid][$vnev1]==$varos) $varosurlap.=' selected';
						$varosurlap.=">".$varosT[$id][$meid][$vnev1]."</option>";
					}
				}
				else $varosurlap.="<option value=0 selected>NINCS település feltöltve!!!</option>";
				$varosurlap.="</select><span class=alap> (település)</span><br></div>";

			}
			$megyeurlap.="</select><span class=alap> (megye)</span><br></div>";
		}
		else {
			//település
		
				$varosurlap.="\n<div id=m$id style='display: $mdisplay'><select name=varosT[$id][0] class=keresourlap><option value=0>Nincs / nem tudom</option>";
				if(is_array($varos1T[$id][0])) {	
					foreach($varos1T[$id][0] as $vnev1) {
						$varosurlap.="\n<option value='".$varosT[$id][0][$vnev1]."'";
						if($varosT[$id][0][$vnev1]==$varos) $varosurlap.=' selected';
						$varosurlap.=">".$varosT[$id][0][$vnev1]."</option>";
					}
				}
				$varosurlap.="</select><span class=alap> (település)</span><br></div>";
		}
	}
	$urlap.="</select><span class=alap> (ország)</span><br>";

	//Település
	$urlap.=$megyeurlap.$varosurlap;
	$urlap.="<input type=text name=cim value=\"$cim\" class=urlap size=60 maxlength=250><span class=alap> (utca, házszám)</span>";
	$urlap.="<br><img src=img/space.gif widt=5 height=5><br><textarea name=megkozelites class=urlap cols=50 rows=2>$megkozelites</textarea><span class=alap> (megközelítés rövid leírása)</span>";
	
	//Koordináta
	$urlap.="<input type=text name=lat value=\"$lat\" class=urlap size=10 maxlength=7><span class=alap> (szélesség)</span> ";
	$urlap.="<input type=text name=lng value=\"$lng\" class=urlap size=10 maxlength=7><span class=alap> (hosszúság)</span>";
	$urlap.="</td></tr>";

  //plébánia
	$urlap.="\n<tr><td bgcolor=#efefef><div class=kiscim align=right>Plébánia adatai:<br><a href=\"javascript:OpenNewWindow('sugo.php?id=6',200,300);\"><img src=img/sugo.gif border=0 title='Súgó' align=absmiddle></a></div></td><td bgcolor=#efefef><textarea name=plebania class=urlap cols=50 rows=3>$plebania</textarea><span class=alap> cím, telefon, fax, kontakt</span>";
	$urlap.="<br><input type=text name=pleb_eml value='$pleb_eml' size=40 class=urlap maxlength=100><span class=alap> email</span>";
	$urlap.="<br><input type=text name=pleb_url value='$pleb_url' size=40 class=urlap maxlength=100><span class=alap> web http://-rel együtt!!!</span>";
	$urlap.="</td></tr>";


  //megjegyzés
	$urlap.="\n<tr><td bgcolor=#ffffff><div class=kiscim align=right>Megjegyzés:<br><a href=\"javascript:OpenNewWindow('sugo.php?id=10',200,360);\"><img src=img/sugo.gif border=0 title='Súgó' align=absmiddle></a></div></td><td bgcolor=#ffffff><textarea name=megjegyzes class=urlap cols=50 rows=3>$megjegyzes</textarea><br><span class=alap> ami a \"jó tudni...\" dobozban megjelenik (pl. búcsú, védőszent, \"reklám\" stb.)</span></td></tr>";


	//miseaktív
	if($miseaktiv == 1) $van = ' checked '; else $nincs = ' checked ';
	$urlap.="\n<tr><td bgcolor=#ffffff><div class=kiscim align=right>Aktív misézőhely:</div></td><td bgcolor=#ffffff>
	<input type=radio name=miseaktiv class=urlap value=1 ".$van."> <span class=alap>Van rendszeresen mise.</span>
	<input type=radio name=miseaktiv class=urlap value=0 ".$nincs."> <span class=alap>Nincs rendszeresen mise.</span></td></tr>";
  //mise megjegyzés
	$urlap.="\n<tr><td bgcolor=#ffffff><div class=kiscim align=right>Mise megjegyzés:<br><a href=\"javascript:OpenNewWindow('sugo.php?id=41',200,360);\"><img src=img/sugo.gif border=0 title='Súgó' align=absmiddle></a></div></td><td bgcolor=#ffffff><textarea name=misemegj class=urlap cols=50 rows=3>$misemegj</textarea><br><span class=alap> Rendszeres rózsafűzér, szentségimádás, hittan, stb.</span></td></tr>";

  //nyári-téli időszámítás
  //	$urlap.="\n<tr><td bgcolor=#efefef><div class=kiscim align=right>Nyári időszámítás:</div></td><td bgcolor=#efefef><input type=text name=nyariido value=\"$nyariido\" class=urlap size=10 maxlength=10><span class=alap> - </span><input type=text name=teliido value=\"$teliido\" class=urlap size=10 maxlength=10> <a href=\"javascript:OpenNewWindow('sugo.php?id=8',200,300);\"><img src=img/sugo.gif border=0 title='Súgó' align=absmiddle></a></td></tr>";

  //Szöveg
	$urlap.="<tr><td valign=top><div class=kiscim align=right>Részletes leírás, templom története:<br><a href=\"javascript:OpenNewWindow('sugo.php?id=9',200,300);\"><img src=img/sugo.gif border=0 title='Súgó' align=absmiddle></a></div></td><td valign=top><span class=alap><font color=red><b>FONTOS!</b></font> A szöveghez MINDIG legyen stílus rendelve!</span><br><textarea name=szoveg class=urlap cols=90 rows=30>$szoveg</textarea>";

	$urlap.="\n</td></tr>";

  //Fájlok
	$urlap.="\n<tr><td bgcolor=#efefef valign=top><div class=kiscim align=right>Letölthető fájl(ok):</td><td bgcolor=#efefef valign=top>";
	$urlap.="\n<span class=alap>Kapcsolódó dokumentum, ha van ilyen:</span><br>";
	$urlap.="\n<span class=alap>Új fájl: </span><input type=file size=60 name=fajl class=urlap> <a href=\"javascript:OpenNewWindow('sugo.php?id=12',200,300);\"><img src=img/sugo.gif border=0 title='Súgó' align=absmiddle></a><br>";
	//Könyvtár tartalmát beolvassa
	if($tid>0) {
		$konyvtar="fajlok/templomok/$tid";
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

  //Képek
	$urlap.="\n<tr><td><div class=kiscim align=right>Képek:<br><a href=\"javascript:OpenNewWindow('sugo.php?id=11',200,450);\"><img src=img/sugo.gif border=0 title='Súgó' align=absmiddle></a></div></td><td><span class=alap><font color=red>FIGYELEM!</font><br>Azonos nevű képek felülírják egymást!!! A fájlnévben ne legyen ékezet és szóköz!</span><br><input type=file name=kepT[] class=urlap size=20> <span class=alap>Képfelirat: </span><input type=text name=kepfeliratT[] size=40 maxlength=100 class=urlap><br><input type=file name=kepT[] class=urlap size=20> <span class=alap>Képfelirat: </span><input type=text name=kepfeliratT[] size=40 maxlength=100 class=urlap><br><input type=file name=kepT[] class=urlap size=20> <span class=alap>Képfelirat: </span><input type=text name=kepfeliratT[] size=40 maxlength=100 class=urlap>";
	if($tid>0) {
		//Meglévő képek listája
		$query="select fajlnev,felirat,sorszam,kiemelt from kepek where tid='$tid' order by sorszam";
		$lekerdez=mysql_db_query($db_name,$query);
		$konyvtar="kepek/templomok/$tid/kicsi";
		$urlap.="\n<table width=100% cellpadding=0 cellspacing=0><tr>";
		while(list($fajlnev,$felirat,$sorszam,$kiemelt)=mysql_fetch_row($lekerdez)) {			
			if($a%3==0 and $a>0) $urlap.="</tr><tr>";
			$a++;
			if($kiemelt=='n') $fokepchecked='';
			else $fokepchecked=' checked';
			$urlap.="\n<td valign=bottom><img src=$konyvtar/$fajlnev title='$fajlnev'><br><input type=text name=kepsorszamT[$fajlnev] value='$sorszam' maxlength=2 size=1 class=urlap><span class=alap> -főoldal:</span><input type=checkbox name=fooldalkepT[$fajlnev] $fokepchecked value='i' class=urlap><span class=alap> -töröl:</span><input type=checkbox name=delkepT[] value='$fajlnev' class=urlap><br><input type=text name=kepfeliratmodT[$fajlnev] value='$felirat' maxlength=250 size=20 class=urlap></td>";
		}
		$urlap.='</tr></table>';
	}
	$urlap.='</td></tr>';

  //Frissítés dátuma
	if($tid>0) {
		$urlap.="\n<tr><td valign=top><div class=kiscim align=right>Frissítés:<br><a href=\"javascript:OpenNewWindow('sugo.php?id=14',200,300);\"><img src=img/sugo.gif border=0 title='Súgó' align=absmiddle></a></div></td><td valign=top><input type=text disabled value='$frissites' size=10 class=urlap><br><input type=checkbox name=frissit value=i class=urlap><span class=alap> Frissítsük a dátumot</span></td></tr>";
	}
	
  //Engedélyezés
	$urlap.="\n<tr><td bgcolor=#efefef valign=top><div class=kiscim align=right>Megjelenhet:</div></td><td bgcolor=#efefef valign=top><input type=radio name=ok value=i";
	if($ok!='n' and $ok!='f') $urlap.=" checked";
	$urlap.="><span class=alap> igen</span>";
	$urlap.="<input type=radio name=ok value=f";
	if($ok=='f') $urlap.=" checked";
	$urlap.="><span class=alap> áttekintésre vár</span>";
	$urlap.="<input type=radio name=ok value=n";
	if($ok=='n') $urlap.=" checked";
	$urlap.="><span class=alap> nem</span>";
	$urlap.=" <a href=\"javascript:OpenNewWindow('sugo.php?id=15',200,300);\"><img src=img/sugo.gif border=0 title='Súgó' align=absmiddle></a></td></tr>";

  //Log
	$urlap.="\n<tr><td valign=top><div class=kiscim align=right>Történet:</div></td><td valign=top><textarea cols=50 rows=6 disabled>Számláló: $szamlalo\n$log</textarea></td></tr>";

	$urlap.="\n<tr><td><div align=right><input type=submit value=Mehet class=urlap>&nbsp;</div></td><td>";

	if($tid>0) {
		$urlap.="<input type=radio name=modosit value=i class=urlap checked><span class=alap> és újra módosít</span>";
		$urlap.="<br><input type=radio name=modosit value=m class=urlap><span class=alap> és tovább a miserendre</span>";
		$urlap.="<br><input type=radio name=modosit value=n class=urlap><span class=alap> és vissza a listába</span>";
		$urlap.="<br><input type=radio name=modosit value=t class=urlap><span class=alap> és vissza a templom oldalára</span>";
	}
	else $urlap.="<input type=hidden name=modosit value=i>";

	$urlap.='</td></tr></table>';
	$urlap.="\n</form>";

	$adatT[2]='<span class=alcim>Templom feltöltése / módosítása</span><br><br>'.$urlap;
	$tipus='doboz';
	$tartalom.=formazo($adatT,$tipus);	
	
	$kod=$tartalom;

	return $kod;
}

function miserend_addingtemplom() {
	global $_POST,$_SERVER,$db_name,$_FILES,$u_login,$u_beosztas;
	global $config;

	$ip=$_SERVER['REMOTE_ADDR'];
    $host = gethostbyaddr($ip);

	$hiba=false;
	$tid=$_POST['tid'];
  /*
	if($tid>0) {
		//Ha módosítás történt
		$lekerdez=mysql_db_query($db_name,"select megnyitva from hirek where id='$hid'");
		list($megnyitva)=mysql_fetch_row($lekerdez);
		if(strstr($megnyitva,$u_login)) { //és ő nyitotta meg utoljára,
			mysql_db_query($db_name,"update hirek set megnyitva='' where id='$hid'"); //akkor töröljük a bejegyzést
		}
	}
  */
	$ma=date('Y-m-d');

	$modosit=$_POST['modosit'];
	$adminmegj=$_POST['adminmegj'];
	$nev=$_POST['nev'];
	$ismertnev=$_POST['ismertnev'];
	$turistautak=$_POST['turistautak'];
	$egyhazmegye=$_POST['egyhazmegye'];
	$espkerT=$_POST['espkerT'];
	$espereskerulet=$espkerT[$egyhazmegye];
	$orszag=$_POST['orszag'];
	$megyeT=$_POST['megyeT'];
	$megye=$megyeT[$orszag];
	if(empty($megye)) $megye=0;
	$varosT=$_POST['varosT'];
	$varos=$varosT[$orszag][$megye];
	$cim=$_POST['cim'];
	$megkozelites=$_POST['megkozelites'];
	$plebania=$_POST['plebania'];
	$pleb_url=$_POST['pleb_url'];
	$pleb_eml=$_POST['pleb_eml'];
	$nyariido=$_POST['nyariido'];
	$teliido=$_POST['teliido'];
	$megjegyzes=$_POST['megjegyzes'];
	$miseaktiv=$_POST['miseaktiv'];
	$misemegj=$_POST['misemegj'];
	$frissit=$_POST['frissit'];
	if($frissit=='i') $frissites=" frissites='$ma', ";
	$kontakt=$_POST['kontakt'];
	$kontaktmail=$_POST['kontaktmail'];
	
	$bucsu=$_POST['bucsu'];
	$ok=$_POST['ok'];
	$feltolto=$_POST['feltolto'];
	$megbizhato=$_POST['megbizhato'];
	if($megbizhato!='i') $megbizhato='n';
	
	$lat = $_POST['lat'];
	$lng = $_POST['lng'];

	$szoveg=$_POST['szoveg'];
	$szoveg=str_replace('&eacute;','é',$szoveg);
	$szoveg=str_replace('&ouml;','ö',$szoveg);
	$szoveg=str_replace('&Ouml;','Ö',$szoveg);
	$szoveg=str_replace('&uuml;','ü',$szoveg);
	$szoveg=str_replace('&Uuml;','Ü',$szoveg);
	$szoveg=str_replace("'","\'",$szoveg);

	$elsofeltoltes=$_POST['elsofeltoltes'];
	if($elsofeltoltes=='i' and !empty($szoveg)) $szoveg='<p class=alap>'.nl2br($szoveg);
	
	if(empty($nev)) {
		$hiba=true;
		$hibauzenet.='<br>Nem lett kitöltve a templom neve!';
	}

	if($hiba) {
		$txt.="<span class=hiba>HIBA a templom feltöltésénél!</span><br>";
		$txt.='<span class=alap>'.$hibauzenet.'</span>';
		$txt.="<br><br><a href=javascript:history.go(-1); class=link>Vissza</a>";
	
		$adatT[2]='<span class=alcim>Templomok feltöltése / módosítása</span><br><br>'.$txt;
		$tipus='doboz';
		$kod.=formazo($adatT,$tipus);	
	}
	else {
		$most=date('Y-m-d H:i:s');
		if($tid>0) {
			$uj=false;
			$parameter1='update';
			list($log)=mysql_fetch_row(mysql_db_query($db_name,"select log from templomok where id='$tid'"));
			$ujlog=$log."\nMod: $u_login ($most)";
			$parameter2=", modositotta='$u_login', moddatum='$most', log='$ujlog' where id='$tid'";

			//Módosítjuk a hozzákapcsolódó miseidőpontoknál is az időszámítási dátumot
			$query="update misek set datumtol='$nyariido', datmig='$teliido' where tid='$tid' and torolte=''";
			mysql_db_query($db_name,$query);
		}
		else {
			$uj=true;
			$parameter1='insert';
			$parameter2=", regdatum='$most', log='Add: $u_login ($most)'";
			$frissites=" frissites='$ma', ";
		}

		$query="$parameter1 templomok set nev='$nev', ismertnev='$ismertnev', turistautak='$turistautak', orszag='$orszag', megye='$megye', varos='$varos', cim='$cim', megkozelites='$megkozelites', plebania='$plebania', pleb_url='$pleb_url', pleb_eml='$pleb_eml', egyhazmegye='$egyhazmegye', espereskerulet='$espereskerulet', leiras='$szoveg', megjegyzes='$megjegyzes',  miseaktiv='$miseaktiv', misemegj='$misemegj', bucsu='$bucsu', nyariido='$nyariido', teliido='$teliido', $frissites kontakt='$kontakt', kontaktmail='$kontaktmail', adminmegj='$adminmegj', letrehozta='$feltolto', megbizhato='$megbizhato', ok='$ok' $parameter2";
		if(!mysql_db_query($db_name,$query)) echo 'HIBA!<br>'.mysql_error();
		if($uj) $tid=mysql_insert_id();	
		else {
			$katnev="$nev ($varos)";
			if(!mysql_db_query($db_name,"update kepek set katnev='$katnev' where tid='$tid'")); 
		}
		
		//geolokáció
		$query = "SELECT * FROM terkep_geocode WHERE tid = ".$tid." LIMIT 1 ";
		$result = mysql_query($query);
		$geocode = mysql_fetch_assoc($result);
		if($config['debug'] > 0) echo $geocode['lng']."->".$lng.";".$geocode['lat']."->".$lat;
		if($lng != $geocode['lng'] OR $lat != $geocode['lat']) {
			if($geocode != array() ) {
				mysql_query("DELETE FROM terkep_geocode WHERE tid = ".$tid." LIMIT 1 ");
				$geocode['checked'] = 0;
			}
			$query = "INSERT INTO terkep_geocode (tid,lng,lat,checked) VALUES (".$tid.",".$lng.",".$lat.",1)";
			mysql_query($query);
			$query = "INSERT INTO terkep_geocode_suggestion (tid,tchecked,slng,slat,uid) VALUES (".$tid.",".$geocode['checked'].",".$lng.",".$lat.",'".$u_login."')";
			mysql_query($query);
			neighboursUpdate($tid);
			
		} 

	
	//fájlkezelés
		$fajl=$_FILES['fajl']['tmp_name'];
		$fajlnev=$_FILES['fajl']['name'];
		$delfajl=$_POST['delfajl'];

		if(is_array($delfajl)) {
			foreach($delfajl as $ertek) {
				unlink("fajlok/templomok/$tid/$ertek");
			}
		}

		if(!empty($fajl)) {
			$konyvtar="fajlok/templomok";
			//Könyvtár ellenőrzése
			if(!is_dir("$konyvtar/$tid")) {
				//létre kell hozni
				if(!mkdir("$konyvtar/$tid",0775)) {
					echo '<p class=hiba>HIBA a könyvtár létrehozásánál!</p>';					
				}
			}

			//Másolás
			if(!copy($fajl,"$konyvtar/$tid/$fajlnev")) echo '<p>HIBA a másolásnál!</p>';
			unlink($fajl);
		}	

	//képkezelés
		$konyvtar="kepek/templomok/$tid";		

		$delkepT=$_POST['delkepT'];
		if(is_array($delkepT)) {		
			foreach($delkepT as $ertek) {
				@unlink("$konyvtar/$ertek");
				@unlink("$konyvtar/kicsi/$ertek");
				if(!mysql_db_query($db_name,"delete from kepek where tid='$tid' and fajlnev='$ertek'")) echo 'HIBA!<br>'.mysql_error();
			}		
		}

		$kepfeliratT=$_POST['kepfeliratT'];		
		$kepT=$_FILES['kepT']['tmp_name'];
		$kepnevT=$_FILES['kepT']['name'];

		if(is_array($kepT)) {
			foreach($kepT as $id=>$kep) {
				if(!empty($kep)) {			
					//Könyvtár ellenőrzése
					if(!is_dir("$konyvtar")) {
						//létre kell hozni
						if(!mkdir("$konyvtar",0775)) {
							echo '<p class=hiba>HIBA a könyvtár létrehozásánál!</p>';					
						}
						if(!mkdir("$konyvtar/kicsi",0775)) {
							echo '<p class=hiba>HIBA a könyvtár létrehozásánál!</p>';					
						}
					}

					$kimenet="$konyvtar/$kepnevT[$id]";
					$kimenet1="$konyvtar/kicsi/$kepnevT[$id]";
	
					$info=getimagesize($kimenet);
					$w=$info[0];
					$h=$info[1];

					if ( !copy($kep, "$kimenet") )
						print("HIBA a másolásnál ($kimenet)!<br>\n");
					else  {
						//Bejegyzés az adatbázisba
						$katnev="$nev ($varos)";
						if(!mysql_db_query($db_name,"insert kepek set tid='$tid', nev='$katnev', fajlnev='$kepnevT[$id]', felirat='$kepfeliratT[$id]', width=$w, height=$h ")) echo 'HIBA!<br>'.mysql_error();
					}
					
					unlink($kep);
	
					
      
					if($w>800 or $h>600) kicsinyites($kimenet,$kimenet,800);
			  		kicsinyites($kimenet,$kimenet1,120);
				}
			}
		}
		$fooldalkepT=$_POST['fooldalkepT'];
		$kepfeliratmodT=$_POST['kepfeliratmodT'];
		$kepsorszamT=$_POST['kepsorszamT'];
		if(is_array($kepsorszamT)) {
			foreach($kepsorszamT as $melyikkep=>$ertek) {
				if($fooldalkepT[$melyikkep]=='i') $kiemelt='i';
				else $kiemelt='n';
				//Módosítás az adatbázisban
				if(!mysql_db_query($db_name,"update kepek set felirat='$kepfeliratmodT[$melyikkep]', sorszam='$ertek', kiemelt='$kiemelt' where tid='$tid' and fajlnev='$melyikkep'")) echo 'HIBA!<br>'.mysql_error();
			}
		}		
	
		if($modosit=='i') $kod=miserend_addtemplom($tid);
		elseif($modosit=='m') $kod=miserend_addmise($tid);
		elseif($modosit=='t') {
			header('Location: ?templom='.$tid);
			die();
		}
		else $kod=miserend_modtemplom();
	}

	return $kod;
}

function miserend_modtemplom() {
	global $db_name,$linkveg,$m_id,$_POST,$u_login,$sid;

	$egyhazmegye=$_POST['egyhazmegye'];
	if($egyhazmegye=='0') $egyhazmegye='mind';
	$kulcsszo=$_POST['kkulcsszo'];	
	$allapot=$_REQUEST['allapot'];

	$sort=$_POST['sort'];
	if(empty($sort)) $sort='moddatum desc';

	$min=$_POST['min'];
	if(!isset($min)) $min=$_GET['min'];
	if($min<0 or !isset($min)) $min=0;

	$leptet=$_POST['leptet'];
	if(!isset($leptet)) $leptet=$_GET['leptet'];
	if(!isset($leptet)) $leptet=50;

	$next=$min+$leptet;
	$prev=$min-$leptet;

	$query_kat="select id,ehm,nev from espereskerulet";
	$lekerdez_kat=mysql_db_query($db_name,$query_kat);
	while(list($esid,$eshm,$esnev)=mysql_fetch_row($lekerdez_kat)) {
		$espkerT[$eshm][$esid]=$esnev;
	}
	
	$kiir.="<span class=kiscim>A lista szűkíthető egyházmegyék, kulcsszó és állapot alapján:</span><br>";
	$csakpriv='mind';
	$ehmmindkiir='<option value=mind>Mind</option>';
	$query_kat="select id,nev,felelos,csakez from egyhazmegye where ok='i' order by sorrend";
	$lekerdez_kat=mysql_db_query($db_name,$query_kat);
	while(list($kid,$knev,$kfelelos,$kcsakez)=mysql_fetch_row($lekerdez_kat)) {
		if($kfelelos==$u_login) {
			$ehmT['priv'][$kid]=$knev;
			if($kcsakez=='i') {
				$csakpriv='priv';
				$ehmmindkiir='';
			}
			else $csakpriv='mind';
			if(empty($egyhazmegye)) $egyhazmegye="$kid-0";
		}
		$ehmT['mind'][$kid]=$knev;
	}
	if(empty($egyhazmegye)) $egyhazmegye='mind';

	$kiir.="\n<form method=post><input type=hidden name=m_id value='$m_id'><input type=hidden name=m_op value=modtemplom>";
	$kiir.="\n<input type=hidden name=sid value=$sid>";
	$kiir.="\n<select name=egyhazmegye class=urlap>";
	$kiir.=$ehmmindkiir;
	foreach($ehmT[$csakpriv] as $kid=>$knev) {
		$kiir.="<option value=$kid-0";
		if($egyhazmegye=="$kid-0") $kiir.=" selected";
		$kiir.=">";
		$kiir.="$knev</option>";
		if(is_array($espkerT[$kid])) {
			foreach($espkerT[$kid] as $esid=>$esnev) {
				$kiir.="<option value=$kid-$esid";
				if($egyhazmegye=="$kid-$esid") $kiir.=" selected";
				$kiir.="> -> $esnev espker.</option>";
			}
		}
	}
	$kiir.="</select>";
			
	$kiir.="\n <input type=text name=kkulcsszo value='$kulcsszo' class=urlap size=20>";

  //Állapot szerinti szűrés
	$kiir.="\n <select name=allapot class=urlap><option value=0>Mind</option><option value=i";
	if($allapot=='i') $kiir.=" selected";
	$kiir.=">csak engedélyezett templomok</option><option value=f";
	if($allapot=='f') $kiir.=" selected";
	$kiir.=">áttekintésre várók</option><option value=n";
	if($allapot=='n') $kiir.=" selected";
	$kiir.=">letiltott templomok</option><option value=e";
	if($allapot=='e') $kiir.=" selected";
	$kiir.=">észrevételezett templomok</option><option value=ef";
	if($allapot=='ef') $kiir.=" selected";
	$kiir.=">javítás alatt lévő templomok</option>";
	//$kiir.="<opton value=m";
  //	if($allapot=='m') $kiir.=" selected";
  //	$kiir.=">miserend nélküli templomok</option>";
	$kiir.="</select>";

	$kiir.="\n<br><span class=alap>rendezés: </span><select name=sort class=urlap> ";
	$sortT['utolsó módosítás']='moddatum desc';
	$sortT['település']='varos';
	$sortT['templomnév']='nev';
	foreach($sortT as $kulcs=>$ertek) {
		$kiir.="<option value='$ertek'";
		if($ertek==$sort) $kiir.=' selected';
		$kiir.=">$kulcs</option>";
	}
	$kiir.="\n</select><input type=submit value=Lista class=urlap></form><br>";

	if($egyhazmegye!='mind' and isset($egyhazmegye)) {
		$ehmT=explode('-',$egyhazmegye);
		if($ehmT[1]=='0')	$feltetelT[]="egyhazmegye='$ehmT[0]'";
		else $feltetelT[]="espereskerulet='$ehmT[1]'";
	}
	if(!empty($kulcsszo)) $feltetelT[]="(nev like '%$kulcsszo%' or varos like '%$kulcsszo%' or ismertnev like '%$kulcsszo%' or letrehozta like '%$kulcsszo%')";
	if(!empty($allapot)) {
		if($allapot=='e') $feltetelT[]="eszrevetel='i'";
		elseif($allapot=='ef') $feltetelT[]="eszrevetel='f'";
		else $feltetelT[]="ok='$allapot'";
	}
	if(is_array($feltetelT)) $feltetel=' where '.implode(' and ',$feltetelT);

  //Misék lekérdezése
	$querym="select distinct(tid) from misek where torolte=''";
	if(!$lekerdezm=mysql_db_query($db_name,$querym)) echo "HIBA!<br>$querym<br>".mysql_error();
	while(list($templom)=mysql_fetch_row($lekerdezm)) {
		$vanmiseT[$templom]=true;
	}

  //Észrevételek lekérdezése
	$querye="select distinct(hol_id) from eszrevetelek where hol='templomok'";
	if(!$lekerdeze=mysql_db_query($db_name,$querye)) echo "HIBA!<br>$querym<br>".mysql_error();
	while(list($templom)=mysql_fetch_row($lekerdeze)) {
		$vaneszrevetelT[$templom]=true;
	}

	$query="select id,nev,ismertnev,varos,ok,eszrevetel,miseaktiv from templomok $feltetel order by $sort";
	$lekerdez=mysql_db_query($db_name,$query);
	$mennyi=mysql_num_rows($lekerdez);
	if($mennyi>$leptet) {
		$query.=" limit $min,$leptet";
		$lekerdez=mysql_db_query($db_name,$query);
	}
	$kezd=$min+1;
	$veg=$min+$leptet;
	if($veg>$mennyi) $veg=$mennyi;
	if($mennyi>0) {
		$kiir.="<span class=alap>Összesen: $mennyi találat<br>Listázás: $kezd - $veg</span><br><br>";
		if($min>0) {
			$lapozo.="\n<form method=post><input type=hidden name=m_id value=$m_id><input type=hidden name=m_op value=modtemplom><input type=hidden name=sid value=$sid><input type=hidden name=kkulcsszo value='".$_POST['kkulcsszo']."'><input type=hidden name=egyhazmegye value=$egyhazmegye><input type=hidden name=min value=$prev><input type=hidden name=sort value='$sort'>";		
			$lapozo.="\n<input type=submit value=Előző class=urlap><input type=text size=2 value=$leptet name=leptet class=urlap></form>";
		}
		if($mennyi>$min+$leptet) {
			$lapozo.="\n<form method=post><input type=hidden name=m_id value=$m_id><input type=hidden name=m_op value=modtemplom><input type=hidden name=sid value=$sid><input type=hidden name=kkulcsszo value='".$_POST['kkulcsszo']."'><input type=hidden name=egyhazmegye value=$egyhazmegye><input type=hidden name=min value=$next><input type=hidden name=sort value='$sort'>";
			$lapozo.="\n<input type=submit value=Következő class=urlap><input type=text size=2 value=$leptet name=leptet class=urlap></form>";
		}
		$kiir.=$lapozo.'<br>';
	}
	else $kiir.="<span class=alap>Jelenleg nincs módosítható templom az adatbázisban.</span>";
	while(list($tid,$tnev,$tismert,$tvaros,$tok,$teszrevetel,$miseaktiv)=mysql_fetch_row($lekerdez)) {
		$jelzes='';
		//Észrevétel
		$RemarkMark = getRemarkMark($tid);
		if($RemarkMark['mark'] != false) $jelzes.=$RemarkMark['html'];

		if(!$vanmiseT[$tid] AND $miseaktiv == 1) {
			$jelzes.="<img src=img/lampa.gif title='Nincs hozzá mise!' align=absmiddle> ";
		}		
		//Jelzés beállítása -> lampa = nincs kategorizalva, ora = varakozik ok=n, tilos = megjelenhet X, jegyzettömb - szerkesztés alatt (megnyitva)
		//if(!empty($megnyitva)) $jelzes.="<img src=img/edit.gif title='Megnyitva: $megnyitva' align=absmiddle> ";
		//if(empty($rovatkat)) $jelzes.="<img src=img/lampa.gif title='Nincs kategórizálva!' align=absmiddle> ";
		//if(!strstr($megjelenhet,'kurir')) $jelzes.="<img src=img/tilos.gif title='Megjelenés nincs beállítva!' align=absmiddle> ";
		//if($ok!='i') $jelzes.="<img src=img/ora.gif title='Feltöltött hír, áttekintésre vár!' align=absmiddle> ";
		if($tok=='n') $jelzes.="<img src=img/tilos.gif title='Nem engedélyezett!' align=absmiddle> ";
		elseif($tok=='f') $jelzes.="<img src=img/ora.gif title='Feltöltött/módosított templom, áttekintésre vár!' align=absmiddle> ";
		
		$kiir.="\n$jelzes <a href=?m_id=$m_id&m_op=addtemplom&tid=$tid$linkveg class=felsomenulink title='$tismert'><b>- $tnev</b><font color=#8D317C> ($tvaros)</font></a> - <a href=?m_id=$m_id&m_op=addmise&tid=$tid$linkveg class=felsomenulink><img src=img/mise_edit.png title='misék' align=absmiddle border=0>szentmise</a> - <a href=?m_id=$m_id&m_op=deltemplom&tid=$tid$linkveg class=link><img src=img/del.jpg border=0 alt=Töröl align=absmiddle> töröl</a> - <a class=felsomenulink href=\"?templom=$tid\" target=\"_blank\">megnéz</a><br>";
	}

	$kiir.='<br>';
	$kiir.=$lapozo;

	/* észrevételezett templomok esetén RSS lehetőség */
	if($allapot == 'e') {
		$query = array();
		foreach(array('egyhazmegye','allapot','kkulcsszo','sort','sid') as $var) {
			if(isset($$var) AND $$var != '') $query[] = $var."=".urlencode($$var);
		}
		$link = 'naplo_rss.php';
		if(count($query)>0) $link .= "?".implode('&',$query);
		//$kiir .= "<br/><a href=\"".$link."\" class=felsomenulink>RSS</a>";
	}
	
	$adatT[2]='<span class=alcim>Templomok, miserendek módosítása</span><br><br>'.$kiir;
	$tipus='doboz';
	$tartalom.=formazo($adatT,$tipus);	
	
	$kod=$tartalom;


	return $kod;
}

function miserend_addmise($tid) {
	global $m_id;	

	global $script;
	$script .= "<script type='text/javascript' src='js/miserend_addmise.js'></script>\n";

	$vars['church'] = $church = getChurch($tid);	
	$masses = getMasses($tid);

	$vars['m_id'] = $m_id;
	$vars['tid'] = $tid;
	
	//Észrevétel
	$jelzes = getRemarkMark($tid);
	$vars['jelzes'] = $jelzes;

	//miseaktív
	if($church['miseaktiv'] == 1) $vars['active']['yes']  = ' checked '; else $vars['active']['no'] = ' checked ';


	$vars['lasperiod'] = 0;
	if(isset($masses['periods']))
	foreach($masses['periods'] as $pkey=>$period) {	
		$vars['periods'][] = formPeriod($pkey,$period,'period');
	}
	$vars['lasperiod'] = $pkey;

	$vars['lastparticular'] = 0;
	if(isset($masses['particulars']))
	foreach($masses['particulars'] as $pkey=>$particular) {	
		$vars['particulars'][] = formPeriod($pkey,$particular,'particular');
	}
	$vars['lastparticular'] = $pkey;

	$vars['misemegj'] =  array(
            'type' => 'textbox',
            'name' => "misemefj",
            'value' => $church['misemegj'],
            'label'=> 'Rendszeres rózsafűzér, szentségimádás, hittan, stb.<br/>');
	$vars['adminmegj'] =  array(
            'type' => 'textbox',
            'name' => "adminmegj",
            'value' => $church['adminmegj'],
            'labelback'=> ' A templom szerkesztésével kacsolatosan.');
	  	  
	$vars['helptext'] = <<<'EOT'
  <br><br><span class=alap><b>nyelvek</b> (h, hu vagy üres=magyar, en=angol, de=német, it=olasz, fr=francia, va=latin, gr=görög, sk=szlovák, hr=horvát, pl=lengyel, si=szlovén => további nyelvek esetén az internetes 2 betűs végződés az irányadó!) Előfordulhatnak periódusok is, ebben az esetben a nyelv mellett a periódus számát kell feltüntetni, pl de2,va3 -> minden hónap második hetén német, harmadik hetén latin (A vessző fontos, merty az tagolja).<br>
		<br/><b>Lehetséges periódusok</b>: <b> 0</b>=mindig<b>, 1, 2, 3, 4, 5, -1</b>=utolsó héten<b>, ps</b>=páros héten<b>, pt</b>=páratlan héten.<br>
		Alapeset, minden mise magyar: ebben az esetben nem kell kitölteni. Alapesetben a templomnak megfelelő rítusú a liturgia: csak akkor kell feltüntetni gor/rom, ha eltér a templom rítusától.</span>
		<br><br><span class=alap>A mise-tulajdonságok a nyelvekhez hasonlóan működnek. Előfordulhatnak periódusok is, ebben az esetben a hét számát is fel kell tüntetni. (Periódus nélkül 0-át lehet a betükód mögé írni, de nem szökséges.)
		<br><b>Betükódok</b>: gitáros = g, csendes = cs<br>családos/gyerek = csal, diák = d, egyetemista/ifjúsági = ifi<br/>igeliturgia = ige, görögkatolikus = gor, római katolikus = rom
		<br>Több tulajdonságot vesszővel kell egymástól elválasztani.
		<br><br><span class=alap>A <b>megjegyzés</b> rovatba minden további részletet tüntessünk fel, amit nem tudtunk a tulajdonságokhoz feljegyezni.</span>
EOT;
	 
	global $twig;
	return $twig->render('content_admin_editschedule.html',$vars); 
}

function miserend_addingmise() {
	global $user;

	$most=date('Y-m-d H:i:s');

	foreach($_REQUEST as $k=>$i) $_REQUEST[$k] = sanitize($i);
	if(!is_numeric($_REQUEST['tid'])) die('tid csak numeric');

	//DELETE
	if(isset($_REQUEST['delete']['period'])) {
		foreach($_REQUEST['delete']['period'] as $period) {
			$query = "UPDATE misek SET torles = '".$most."', torolte = '".$user->login."' WHERE tid = ".$_REQUEST['tid']." AND idoszamitas = '".$period."' ;";
			mysql_query($query);
		}
	}
	if(isset($_REQUEST['delete']['particular'])) {
		foreach($_REQUEST['delete']['particular'] as $particular) {
			$query = "UPDATE misek SET torles = '".$most."', torolte = '".$user->login."' WHERE tid = ".$_REQUEST['tid']." AND idoszamitas = '".$particular."' ;";
			mysql_query($query);
		}
	}
	if(isset($_REQUEST['delete']['mass'])) {
		foreach($_REQUEST['delete']['mass'] as $mid) {
			$query = "UPDATE misek SET torles = '".$most."', torolte = '".$user->login."' WHERE tid = ".$_REQUEST['tid']." AND id = '".$mid."' LIMIT 1;";
			mysql_query($query);
		}
	}

	//UPDATE
	if(is_array($_REQUEST['period'])) {
	foreach($_REQUEST['period'] as $period) {
		foreach($period as $key => $mass) {
		if(is_numeric($key)) {
			$mass['tid'] = $_REQUEST['tid'];
			$mass['idoszamitas'] = sanitize($period['name']);
			$mass['tol'] = sanitize($period['from']);
			if($period['from2'] != 0) $mass['tol'] .= ' '.$period['from2'];
			$mass['ig'] = sanitize($period['to']);
			if($period['to2'] != 0) $mass['ig'] .= ' '.$period['to2'];

			if($mass['id'] != 'new') {
				$query = "UPDATE misek SET ";
				$query .= "nap='".$mass['napid']."',ido='".$mass['ido'].":00',nap2='".$mass['nap2']."',idoszamitas='".$mass['idoszamitas']."',tol='".$mass['tol']."',ig='".$mass['ig']."',nyelv='".$mass['nyelv']."',milyen='".$mass['milyen']."',megjegyzes='".$mass['megjegyzes']."',";
				$query .= "modositotta='".$user->login."',moddatum='".$most."'";
				$query .= " WHERE tid = ".$mass['tid']." AND id = ".$mass['id']." LIMIT 1";
			} else {
				$query = "INSERT INTO misek ";
				$query .= " (tid,nap,ido,nap2,idoszamitas,tol,ig,nyelv,milyen,megjegyzes,modositotta,moddatum) ";
				$query .= " VALUES ('".$mass['tid']."','".$mass['napid']."','".$mass['ido'].":00','".$mass['nap2']."','".$mass['idoszamitas']."','".$mass['tol']."','".$mass['ig']."','".$mass['nyelv']."','".$mass['milyen']."','".$mass['megjegyzes']."',";
				$query .= "'".$user->login."','".$most."')";
			}
			mysql_query($query);
		}
		}
	}
	}
	if(is_array($_REQUEST['particular'])) {
	foreach($_REQUEST['particular'] as $particular) {
		foreach($particular as $key => $mass) {
		if(is_numeric($key)) {
			$mass['tid'] = $_REQUEST['tid'];
			$mass['idoszamitas'] = sanitize($particular['name']);
			$mass['tol'] = sanitize($particular['from']);
			if($particular['from2'] != 0) $mass['tol'] .= ' '.$particular['from2'];
			$mass['ig'] = $mass['tol'];
			$mass['napid'] = 0;

			if($mass['id'] != 'new') {
				$query = "UPDATE misek SET ";
				$query .= "nap='".$mass['napid']."',ido='".$mass['ido'].":00',nap2='".$mass['nap2']."',idoszamitas='".$mass['idoszamitas']."',tol='".$mass['tol']."',ig='".$mass['ig']."',nyelv='".$mass['nyelv']."',milyen='".$mass['milyen']."',megjegyzes='".$mass['megjegyzes']."',";
				$query .= "modositotta='".$user->login."',moddatum='".$most."'";
				$query .= " WHERE tid = ".$mass['tid']." AND id = ".$mass['id']." LIMIT 1";
			} else {
				$query = "INSERT INTO misek ";
				$query .= " (tid,nap,ido,nap2,idoszamitas,tol,ig,nyelv,milyen,megjegyzes,modositotta,moddatum) ";
				$query .= " VALUES ('".$mass['tid']."','".$mass['napid']."','".$mass['ido'].":00','".$mass['nap2']."','".$mass['idoszamitas']."','".$mass['tol']."','".$mass['ig']."','".$mass['nyelv']."','".$mass['milyen']."','".$mass['megjegyzes']."',";
				$query .= "'".$user->login."','".$most."')";
			}
			mysql_query($query);
		}
		}
	}
	}
	generateMassTmp('tid = '.$_REQUEST['tid']);


	//LOG
	$ip=$_SERVER['REMOTE_ADDR'];
    $host = gethostbyaddr($ip);
    $tid = $_REQUEST['tid'];
	$ma=date('Y-m-d');
	list($log)=mysql_fetch_row(mysql_query("select log from templomok where id='$tid'"));
	$log.="\nMISE_MOD: ".$user->login." ($most - [$ip - $host])";
	if($_REQUEST['frissit']=='i') $frissites=", frissites='$ma'";
	$_REQUEST['misemegj'] = preg_replace('/<br\/>/i',"\n", $_REQUEST['misemegj']);
	$_REQUEST['adminmegj'] = preg_replace('/<br\/>/i',"\n", $_REQUEST['adminmegj']);
	$query="update templomok set miseaktiv='".$_REQUEST['miseaktiv']."', misemegj='".$_REQUEST['misemegj']."', adminmegj='".$_REQUEST['adminmegj']."', log='$log' $frissites where id='$tid' LIMIT 1";
	mysql_query($query);


	$modosit = $_REQUEST['modosit'];
	if($modosit=='i') $kod=miserend_addmise($tid);
	elseif($modosit=='m') $kod=miserend_addtemplom($tid);
	elseif($modosit=='t') {
		header('Location: ?templom='.$tid);
		die();
	}
	else $kod=miserend_modtemplom();

	
	return $kod;
}



function miserend_deltemplom() {
	global $_GET,$db_name,$linkveg,$m_id,$u_login;

	$tid=$_GET['tid'];

	$kiir="<span class=alcim>Templom és miserend törlése</span><br><br>";
	$kiir.="\n<span class=kiscim>Biztosan törölni akarod a következő templomot?<br><font color=red>FIGYELEM! A kapcsolódó misék és képek is törlődnek!</font></span>";
		
	$query="select nev from templomok where id='$tid'";
	list($cim)=mysql_fetch_row(mysql_db_query($db_name,$query));
	if(!empty($cim)) {
		$kiir.="\n<br><br><span class=alap><b><i>$cim</i></b></span>";

		$kiir.="<br><br><a href=?m_id=$m_id&m_op=deletetemplom&tid=$tid$linkveg class=link>Igen</a> - <a href=?m_id=$m_id&m_op=modtemplom$linkveg class=link>NEM</a>";
	}
	else {
		$kiir.="<br><br><span class=hiba>HIBA! Ilyen templom nincs!</span>";
	}

	$adatT[2]=$kiir;
	$tipus='doboz';
	$tartalom.=formazo($adatT,$tipus);	
	
	$kod=$tartalom;

	return $kod;
}

function miserend_deletetemplom() {
	global $_GET,$db_name,$u_login,$u_beosztas;

	$tid=$_GET['tid'];
	$query="delete from templomok where id='$tid'";
	mysql_db_query($db_name,$query);

	//Miséket is töröljük
	$query="delete from misek where templom='$tid'";
	mysql_db_query($db_name,$query);

  //És kiszedi a törölt szomszédosokat!!!
	$query="select id, szomszedos1, szomszedos2 from templomok where szomszedos1 like '%-$tid-%' or szomszedos2 like '%-$tid-%'";
	$lekerdez=mysql_db_query($db_name,$query);
	while(list($szid,$sz1,$sz2)=mysql_fetch_row($lekerdez)) {
		if(strstr($sz1,$tid)) {
			//Ha a másik templomnál szerepel a mi templomunk
			//akkor töröljük onnan is a hozzárendelést!
			$sz1=str_replace('--','!',$sz1);
			$sz1=str_replace('-','',$sz1);
			$sz1T=explode('!',$sz1);
			foreach($sz1T as $ertek) {
				if($ertek!=$tid) {
					$ujsz1T[]=$ertek;
				}
			}
			if(is_array($ujsz1T)) $ujsz1='-'.implode('--',$ujsz1T).'-';
			else $ujsz1='';
			mysql_db_query($db_name,"update templomok set szomszedos1='$ujsz1' where id='$szid'");
		}
		if(strstr($sz2,$tid)) {
			//Ha a másik templomnál szerepel a mi templomunk
			//akkor töröljük onnan is a hozzárendelést!
			$sz2=str_replace('--','!',$sz2);
			$sz2=str_replace('-','',$sz2);
			$sz2T=explode('!',$sz2);
			foreach($sz2T as $ertek) {
				if($ertek!=$tid) {
					$ujsz2T[]=$ertek;
				}
			}
			if(is_array($ujsz2T)) $ujsz2='-'.implode('--',$ujsz2T).'-';
			else $ujsz2='';
			mysql_db_query($db_name,"update templomok set szomszedos2='$ujsz2' where id='$szid'");
		}
	}

	//Fájlokat és képeket is törölni kell!

	//Könyvtár tartalmát beolvassa
	$konyvtar="fajlok/templomok/$tid";
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
	$konyvtar="kepek/templomok/$tid";
		if(is_dir($konyvtar)) {
			$handle=opendir($konyvtar);
			while ($file = readdir($handle)) {
				if ($file!='.' and $file!='..' and $file!='fokep' and $file!='kicsi') {
					unlink("$konyvtar/$file");
				}
			}
			closedir($handle);
		}
	$konyvtar="kepek/templomok/$tid/kicsi";
		if(is_dir($konyvtar)) {
			$handle=opendir($konyvtar);
			while ($file = readdir($handle)) {
				if ($file!='.' and $file!='..') {
					unlink("$konyvtar/$file");
				}
			}
			closedir($handle);
		}

	$kod=miserend_modtemplom();

	return $kod;
}

function miserend_ehmlista() {
	global $_GET,$db_name,$linkveg,$m_id,$u_login;


	$txt.="<span class=alcim>Egyházmegyei templomok listája</span><br><form method=post><input type=hidden name=m_op value=ehmlista><input type=hidden name=m_id value=$m_id><select name=ehm class=urlap>";
	$query="select id,nev from egyhazmegye";
	$lekerdez=mysql_db_query($db_name,$query);
	while(list($id,$nev)=mysql_fetch_row($lekerdez)) {
		$txt.="<option value=$id";
		if($id==$ehm) $txt.=" selected";
		$txt.=">$nev</option>";
	}
	$txt.="</select><input type=submit value=Mutat class=urlap></form>";

	$ehm=$_POST['ehm'];
	if($ehm>0) {

		list($ehmnev)=mysql_fetch_row(mysql_db_query($db_name,"select nev from egyhazmegye where id='$ehm'"));
		$txt.="<h2>$ehmnev egyházmegye</h2>";

		$query="select templomok.id,templomok.nev,templomok.varos,espereskerulet.nev from espereskerulet, templomok where espereskerulet.id=templomok.espereskerulet and templomok.egyhazmegye=$ehm order by templomok.espereskerulet, templomok.varos";

		if(!$lekerdez=mysql_db_query($db_name,$query)) echo "<br>HIBA!<br>$query<br>".mysql_error();
		while(list($tid,$tnev,$varos,$espker)=mysql_fetch_row($lekerdez)) {
			$a++;
			if($espker!=$espkerell) {
				$txt.= "<br><h3>$espker espereskerület</h3>";
				$espkerell=$espker;
			}
			$txt.= "$a. [$tid] $tnev ($varos)<br>";
			$excel.="\n$tid;$tnev;$varos;$espker";
		}
		$txt.="<br><br><span class=alap>Az alábbi szöveget kimásolva excelbe importálható.<br>Excelben: Adatok / Szövegből oszlopok -> táblázattá alakítható</span><br><textarea class=urlap cols=60 rows=20>$excel</textarea>";
	}


	$adatT[2]=$txt;
	$tipus='doboz';
	$tartalom.=formazo($adatT,$tipus);	
	
	$kod=$tartalom;

	return $kod;
}

function atir() {
	global $db_name;

	$query="select id,megjegyzes,bucsu from templomok where bucsu!=''";
	if(!$lekerdez=mysql_db_query($db_name,$query)) echo "<br>HIBA!<br>$query<br>".mysql_error();
	while(list($tid,$megj,$bucsu)=mysql_fetch_row($lekerdez)) {
		$ujmegj=$bucsu."\n\n".$megj;
		mysql_db_query($db_name,"update templomok set megjegyzes='$ujmegj' where id='$tid'");
		echo "<br>$tid -> ok";
	}
	echo '<br>kész';
}


//Jogosultság ellenőrzése
if(strstr($u_jogok,'miserend')) {

switch($m_op) {
	case 'atir':
		atir();
		break;

	case 'ehmlista':
		$tartalom=miserend_ehmlista();
		break;

    case 'index':
        $tartalom=miserend_index();
        break;

	case 'addtemplom':
		$tid=$_GET['tid'];
        $tartalom=miserend_addtemplom($tid);
        break;

	case 'addmise':
		$tid=$_GET['tid'];
        $tartalom=miserend_addmise($tid);
        break;

    case 'modtemplom':
        $tartalom=miserend_modtemplom();
        break;

    case 'addingtemplom':
        $tartalom=miserend_addingtemplom();
        break;

	case 'addingmise':
        $tartalom=miserend_addingmise();
        break;

    case 'deltemplom':
        $tartalom=miserend_deltemplom();
        break;

	case 'deletetemplom':
        $tartalom=miserend_deletetemplom();
        break;

    case 'delmise':
        $tartalom=miserend_delmise();
        break;

	case 'deletemise':
        $tartalom=miserend_deletemise();
        break;

    case 'events':
        if(isset($_REQUEST['save'])) events_save($_REQUEST);

        $form=events_form();
        $form['m_id'] = $m_id;
        $tartalom = $twig->render('content_admin_editevents.html',$form);
        break;    
}
}
else {
	$tartalom="\n<span class=hiba>HIBA! Nincs hozzá jogosultságod!</span>";
}

?>
