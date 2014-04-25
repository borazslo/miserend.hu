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
	$menu.="<a href=?m_id=$m_id&m_op=modtemplom$linkveg class=kismenulink>Meglévõ templom módosítása, törlése, miserend hozzáadása, módosítása</a><br>";

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
		$vnev1=str_replace('Õ','O',$vnev1);
		$vnev1=str_replace('ö','o',$vnev1);
		$vnev1=str_replace('õ','o',$vnev1);
		$vnev1=str_replace('Ü','U',$vnev1);
		$vnev1=str_replace('Ü','U',$vnev1);
		$vnev1=str_replace('ü','u',$vnev1);
		$vnev1=str_replace('û','u',$vnev1);
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

		$query="select nev,ismertnev,turistautak,orszag,megye,varos,cim,megkozelites,plebania,pleb_url,pleb_eml,egyhazmegye,espereskerulet,leiras,megjegyzes,szomszedos1,szomszedos2,bucsu,nyariido,teliido,frissites,kontakt,kontaktmail,adminmegj,log,ok,letrehozta,megbizhato,eszrevetel from templomok where id='$tid'";
		if(!$lekerdez=mysql_db_query($db_name,$query)) echo 'HIBA!<br>'.mysql_error();	list($nev,$ismertnev,$turistautak,$orszag,$megye,$varos,$cim,$megkozelites,$plebania,$pleb_url,$pleb_eml,$egyhazmegye,$espereskerulet,$szoveg,$megjegyzes,$szomszedos1,$szomszedos2,$bucsu,$nyariido,$teliido,$frissites,$kontakt,$kontaktmail,$adminmegj,$log,$ok,$feltolto,$megbizhato,$teszrevetel)=mysql_fetch_row($lekerdez);
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

/*
//megnyitva
	if($hid>0 and !empty($megnyitva)) {
		$kod=rawurlencode($megnyitva);
		$urlap.="\n<tr><td>&nbsp;</td><td><img src=img/edit.gif align=absmiddle><span class=alap><font color=red>Megnyitva!</font> $megnyitva</span><br><a href=?m_id=$m_id&m_op=addmegse&hid=$hid&kod=$kod$linkveg class=link><b><font color=red>Vissza, mégsem szerkesztem</font></b></a></td></tr>";
	}
*/
/*
//elõnézet
	if($hid>0) $urlap.="\n<tr><td bgcolor='#efefef'>&nbsp;</td><td bgcolor='#efefef'><a href=?m_id=19&id=$hid$linkveg class=link><b>>> Hír megtekintése (elõnézet) <<</b></a></td></tr>";
*/
//Észrevétel
//Észrevételek lekérdezése
	$querye="select distinct(hol_id) from eszrevetelek where hol='templomok'";
	if(!$lekerdeze=mysql_db_query($db_name,$querye)) echo "HIBA!<br>$querym<br>".mysql_error();
	while(list($templom)=mysql_fetch_row($lekerdeze)) {
		$vaneszrevetelT[$templom]=true;
	}

	if($teszrevetel=='i') $jelzes.="<a href=\"javascript:OpenScrollWindow('naplo.php?kod=templomok&id=$tid&sid=$sid',550,500);\"><img src=img/csomag.gif title='Új észrevételt írtak hozzá!' align=absmiddle border=0></a> ";		
	elseif($teszrevetel=='f') $jelzes.="<a href=\"javascript:OpenScrollWindow('naplo.php?kod=templomok&id=$tid&sid=$sid',550,500);\"><img src=img/csomagf.gif title='Észrevétel javítása folyamatban!' align=absmiddle border=0></a> ";		
	elseif($vaneszrevetelT[$tid]) $jelzes.="<a href=\"javascript:OpenScrollWindow('naplo.php?kod=templomok&id=$tid&sid=$sid',550,500);\"><img src=img/csomag1.gif title='Észrevételek!' align=absmiddle border=0></a> ";		
	else $jelzes='<span class=alap>Nincs</span>';

	$urlap.="\n<tr><td colspan=2><span class=kiscim>Észrevétel: </span>$jelzes</td></tr>";

	if($tid>0) {
//Megnéz
		$urlap.="\n<tr><td colspan=2><span class=kiscim>Nyilvános oldal megnyitása:</span><span class=alap> (új ablakban)</span> <a href=?templom=$tid class=link target=_blank><u>$nev</u></a></td></tr>";
	}

//megjegyzés
	$urlap.="\n<tr><td bgcolor=#ECE5C8><div class=kiscim align=right>Megjegyzés:<br><a href=\"javascript:OpenNewWindow('sugo.php?id=1',200,300);\"><img src=img/sugo.gif border=0 title='Súgó'></a></div></td><td bgcolor=#ECE5C8><textarea name=adminmegj class=urlap cols=50 rows=3>$adminmegj</textarea><span class=alap> a szerkesztéssel kapcsolatosan</span></td></tr>";

//kontakt
	$urlap.="\n<tr><td bgcolor=#efefef><div class=kiscim align=right>Felelõs:<br><a href=\"javascript:OpenNewWindow('sugo.php?id=2',200,300);\"><img src=img/sugo.gif border=0 title='Súgó'></a></div></td><td bgcolor=#efefef><textarea name=kontakt class=urlap cols=50 rows=2>$kontakt</textarea><span class=alap> név és telefonszám</span><br><input type=text name=kontaktmail size=40 class=urlap value='$kontaktmail'><span class=alap> emailcím</span></td></tr>";
//feltöltõ
	if(empty($feltolto)) $feltolto=$u_login;
	$urlap.="\n<tr><td bgcolor=#efefef><div class=kiscim align=right>Feltöltõ (jogosult):</div></td><td bgcolor=#efefef>";
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
	$urlap.="\n<tr><td bgcolor=#FAE19C><div class=kiscim align=right>közismert neve:</div></td><td bgcolor=#FAE19C><input type=text name=ismertnev value=\"$ismertnev\" class=urlap size=80 maxlength=150> <a href=\"javascript:OpenNewWindow('sugo.php?id=4',200,300);\"><img src=img/sugo.gif border=0 title='Súgó' align=absmiddle></a><br><span class=alap>(Helyben elfogadott (ismert) templomnév, valamint település, vagy település résznév, amennyiben eltérõ a település hivatalos nevétõl, pl. <u>izbégi templom</u>)</span></td></tr>";

//túristautak
	$urlap.="\n<tr><td bgcolor=#EFEFEF><div class=kiscim align=right>turistautak.hu ID:</div></td><td bgcolor=#EFEFEF><input type=text name=turistautak value=\"$turistautak\" class=urlap size=5 maxlength=10> <a href=\"javascript:OpenNewWindow('sugo.php?id=16',240,320);\"><img src=img/sugo.gif border=0 title='Súgó' align=absmiddle></a><br><span class=alap>(<a href=http://turistautak.hu/search.php?s=templom target=_blank class=link><u>ebbõl a listából</u></a> ha bennevan)</span></td></tr>";

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
	$urlap.="</td></tr>";

//plébánia
	$urlap.="\n<tr><td bgcolor=#efefef><div class=kiscim align=right>Plébánia adatai:<br><a href=\"javascript:OpenNewWindow('sugo.php?id=6',200,300);\"><img src=img/sugo.gif border=0 title='Súgó' align=absmiddle></a></div></td><td bgcolor=#efefef><textarea name=plebania class=urlap cols=50 rows=3>$plebania</textarea><span class=alap> cím, telefon, fax, kontakt</span>";
	$urlap.="<br><input type=text name=pleb_eml value='$pleb_eml' size=40 class=urlap maxlength=100><span class=alap> email</span>";
	$urlap.="<br><input type=text name=pleb_url value='$pleb_url' size=40 class=urlap maxlength=100><span class=alap> web http://-rel együtt!!!</span>";
	$urlap.="</td></tr>";


//megjegyzés
	$urlap.="\n<tr><td bgcolor=#ffffff><div class=kiscim align=right>Megjegyzés:<br><a href=\"javascript:OpenNewWindow('sugo.php?id=10',200,360);\"><img src=img/sugo.gif border=0 title='Súgó' align=absmiddle></a></div></td><td bgcolor=#ffffff><textarea name=megjegyzes class=urlap cols=50 rows=3>$megjegyzes</textarea><br><span class=alap> ami a \"jó tudni...\" dobozban megjelenik (pl. búcsú, védõszent, \"reklám\" stb.)</span></td></tr>";

//nyári-téli idõszámítás
	$urlap.="\n<tr><td bgcolor=#efefef><div class=kiscim align=right>Nyári idõszámítás:</div></td><td bgcolor=#efefef><input type=text name=nyariido value=\"$nyariido\" class=urlap size=10 maxlength=10><span class=alap> - </span><input type=text name=teliido value=\"$teliido\" class=urlap size=10 maxlength=10> <a href=\"javascript:OpenNewWindow('sugo.php?id=8',200,300);\"><img src=img/sugo.gif border=0 title='Súgó' align=absmiddle></a></td></tr>";

//Szöveg
	$urlap.="<tr><td valign=top><div class=kiscim align=right>Részletes leírás, templom története:<br><a href=\"javascript:OpenNewWindow('sugo.php?id=9',200,300);\"><img src=img/sugo.gif border=0 title='Súgó' align=absmiddle></a></div></td><td valign=top><span class=alap><font color=red><b>FONTOS!</b></font> A szöveghez MINDIG legyen stílus rendelve!</span><br><textarea name=szoveg class=urlap cols=90 rows=30>$szoveg</textarea>";

	$urlap.="\n</td></tr>";

//Fájlok
	$urlap.="\n<tr><td bgcolor=#efefef valign=top><div class=kiscim align=right>Letölthetõ fájl(ok):</td><td bgcolor=#efefef valign=top>";
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
	$urlap.="\n<tr><td><div class=kiscim align=right>Képek:<br><a href=\"javascript:OpenNewWindow('sugo.php?id=11',200,450);\"><img src=img/sugo.gif border=0 title='Súgó' align=absmiddle></a></div></td><td><span class=alap><font color=red>FIGYELEM!</font><br>Azonos nevû képek felülírják egymást!!! A fájlnévben ne legyen ékezet és szóköz!</span><br><input type=file name=kepT[] class=urlap size=20> <span class=alap>Képfelirat: </span><input type=text name=kepfeliratT[] size=40 maxlength=100 class=urlap><br><input type=file name=kepT[] class=urlap size=20> <span class=alap>Képfelirat: </span><input type=text name=kepfeliratT[] size=40 maxlength=100 class=urlap><br><input type=file name=kepT[] class=urlap size=20> <span class=alap>Képfelirat: </span><input type=text name=kepfeliratT[] size=40 maxlength=100 class=urlap>";
	if($tid>0) {
		//Meglévõ képek listája
		$query="select fajlnev,felirat,sorszam,kiemelt from kepek where kat='templomok' and kid='$tid' order by sorszam";
		$lekerdez=mysql_db_query($db_name,$query);
		$konyvtar="kepek/templomok/$tid/kicsi";
		$urlap.="\n<table width=100% cellpadding=0 cellspacing=0><tr>";
		while(list($fajlnev,$felirat,$sorszam,$kiemelt)=mysql_fetch_row($lekerdez)) {			
			if($a%3==0 and $a>0) $urlap.="</tr><tr>";
			$a++;
			if($kiemelt=='n') $fokepchecked='';
			else $fokepchecked=' checked';
			$urlap.="\n<td valign=bottom><img src=$konyvtar/$fajlnev title='$fajlnev'><br><input type=text name=kepsorszamT[$fajlnev] value='$sorszam' maxlength=2 size=1 class=urlap><span class=alap> -fõoldal:</span><input type=checkbox name=fooldalkepT[$fajlnev] $fokepchecked value='i' class=urlap><span class=alap> -töröl:</span><input type=checkbox name=delkepT[] value='$fajlnev' class=urlap><br><input type=text name=kepfeliratmodT[$fajlnev] value='$felirat' maxlength=250 size=20 class=urlap></td>";
		}
		$urlap.='</tr></table>';
	}
	$urlap.='</td></tr>';


/*
	$query="select id,nev,ismertnev,varos from templomok where id!='$tid' order by varos";
	$lekerdez=mysql_db_query($db_name,$query);
	while(list($eid,$enev,$eismert,$evaros)=mysql_fetch_row($lekerdez)) {
		if(strlen($enev)>65) $enev=substr($enev,0,65).'...';
		$ismT[$eid]=" title='$eismert'>$evaros -> [$enev]";
	}

//szomszédos 1
	$urlap.="\n<tr><td bgcolor=#efefef><div class=kiscim align=right>Szomszédos templomok (legközelebbi):<br><a href=\"javascript:OpenNewWindow('sugo.php?id=13',200,500);\"><img src=img/sugo.gif border=0 title='Súgó' align=absmiddle></a></div></td><td bgcolor=#efefef>";
	
	if(!empty($szomszedos1)) {
		$sz1=str_replace('--','!',$szomszedos1);
		$sz1=str_replace('-','',$sz1);
		$sz1T=explode('!',$sz1);
		if(is_array($sz1T)) {
			$urlap.="\n<table width=100% bgcolor=#ECE5C8><tr><td colspan=2><span class=kiscim>Kiválasztott legközelebbi templomok:</span></td></tr>";

			foreach($sz1T as $ertek) {
				if($ertek!=$tid) {
					$urlap.="<input type=hidden name=oldsz1T[] value=$ertek>";
					$urlap.="\n<tr><td bgcolor=#FEFDFA><a href=?m_id=27&m_op=addtemplom&tid=$ertek target=_blank class=link ".$ismT[$ertek]."</a></td><td bgcolor=#FEFDFA><input type=checkbox name='delsz1T[]' value='$ertek' class=urlap><span class=alap> töröl</span></td></tr>";
				}
			}
			$urlap.='</table><hr>';
		}
	}
	
	$urlap.="<span class=kiscim>Hozzáadás:</span><br><span class=alap>CTRL-lal több is kijelölhetõ, illetve visszavonható!<br></span><select name=szomszedos1T[] class=urlap multiple size=10>";
	foreach($ismT as $eid=>$enev) {		
		if(!strstr($szomszedos1,"-$eid-")) {
			$urlap.="\n<option value='$eid'";
			$urlap.="$enev</option>";
		}
	}
	$urlap.="</select>";
	$urlap.="</td></tr>";

//szomszédos 2
	$urlap.="\n<tr><td><div class=kiscim align=right>Szomszédos templomok (10km-en belüli):<br><a href=\"javascript:OpenNewWindow('sugo.php?id=13',200,500);\"><img src=img/sugo.gif border=0 title='Súgó' align=absmiddle></a></div></td><td>";

	if(!empty($szomszedos2)) {
		$sz2=str_replace('--','!',$szomszedos2);
		$sz2=str_replace('-','',$sz2);
		$sz2T=explode('!',$sz2);
		if(is_array($sz2T)) {
			$urlap.="\n<table width=100% bgcolor=#ECE5C8><tr><td colspan=2><span class=kiscim>Kiválasztott 10km-en belüli templomok:</span></td></tr>";

			foreach($sz2T as $ertek) {
				if($ertek!=$tid) {
					$urlap.="<input type=hidden name=oldsz2T[] value=$ertek>";
					$urlap.="\n<tr><td bgcolor=#FEFDFA><a href=?m_id=27&m_op=addtemplom&tid=$ertek target=_blank class=link ".$ismT[$ertek]."</a></td><td bgcolor=#FEFDFA><input type=checkbox name='delsz2T[]' value='$ertek' class=urlap><span class=alap> töröl</span></td></tr>";
				}
			}
			$urlap.='</table><hr>';
		}
	}

	$urlap.="<span class=kiscim>Hozzáadás:</span><br><span class=alap>CTRL-lal több is kijelölhetõ, illetve visszavonható!<br></span><select name=szomszedos2T[] class=urlap multiple size=10>";
	foreach($ismT as $eid=>$enev) {		
		if(!strstr($szomszedos1,"-$eid-") and !strstr($szomszedos2,"-$eid-")) {
			$urlap.="\n<option value='$eid'";
			$urlap.="$enev</option>";
		}
	}
	$urlap.="</select>";
	$urlap.="</td></tr>";

*/
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

	$ip=$_SERVER['REMOTE_ADDR'];
    $host = gethostbyaddr($ip);

	$hiba=false;
	$tid=$_POST['tid'];
/*
	if($tid>0) {
		//Ha módosítás történt
		$lekerdez=mysql_db_query($db_name,"select megnyitva from hirek where id='$hid'");
		list($megnyitva)=mysql_fetch_row($lekerdez);
		if(strstr($megnyitva,$u_login)) { //és õ nyitotta meg utoljára,
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
	$frissit=$_POST['frissit'];
	if($frissit=='i') $frissites=" frissites='$ma', ";
	$kontakt=$_POST['kontakt'];
	$kontaktmail=$_POST['kontaktmail'];
	$szomszedos1T=$_POST['szomszedos1T'];
	$delsz1T=$_POST['delsz1T'];
	$oldsz1T=$_POST['oldsz1T'];
	$szomszedos2T=$_POST['szomszedos2T'];
	$oldsz2T=$_POST['oldsz2T'];
	$delsz2T=$_POST['delsz2T'];
	$bucsu=$_POST['bucsu'];
	$ok=$_POST['ok'];
	$feltolto=$_POST['feltolto'];
	$megbizhato=$_POST['megbizhato'];
	if($megbizhato!='i') $megbizhato='n';

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

			//Módosítjuk a hozzákapcsolódó miseidõpontoknál is az idõszámítási dátumot
			$query="update misek set datumtol='$nyariido', datmig='$teliido' where tid='$tid' and torolte=''";
			mysql_db_query($db_name,$query);
		}
		else {
			$uj=true;
			$parameter1='insert';
			$parameter2=", regdatum='$most', log='Add: $u_login ($most)'";
			$frissites=" frissites='$ma', ";
		}

		$query="$parameter1 templomok set nev='$nev', ismertnev='$ismertnev', turistautak='$turistautak', orszag='$orszag', megye='$megye', varos='$varos', cim='$cim', megkozelites='$megkozelites', plebania='$plebania', pleb_url='$pleb_url', pleb_eml='$pleb_eml', egyhazmegye='$egyhazmegye', espereskerulet='$espereskerulet', leiras='$szoveg', megjegyzes='$megjegyzes', szomszedos1='$szomszedos1', szomszedos2='$szomszedos2', bucsu='$bucsu', nyariido='$nyariido', teliido='$teliido', $frissites kontakt='$kontakt', kontaktmail='$kontaktmail', adminmegj='$adminmegj', letrehozta='$feltolto', megbizhato='$megbizhato', ok='$ok' $parameter2";
		if(!mysql_db_query($db_name,$query)) echo 'HIBA!<br>'.mysql_error();
		if($uj) $tid=mysql_insert_id();	
		else {
			$katnev="$nev ($varos)";
			if(!mysql_db_query($db_name,"update kepek set katnev='$katnev' where kat='templomok' and kid='$tid'")); 
		}

	//Szomszédos 1 (legközelebbi templomok)
		if(is_array($oldsz1T)) {
			if(is_array($delsz1T)) {
				foreach($oldsz1T as $ertek) {
					if(!in_array($ertek,$delsz1T)) {
						$ujsz1T[]=$ertek;
					}
				}
			}
			else {
				$ujsz1T=$oldsz1T;
			}
			if(is_array($szomszedos1T)) {
				$ujsz1T=array_merge($ujsz1T,$szomszedos1T);
			}
		}
		elseif(is_array($szomszedos1T)) {
			$ujsz1T=$szomszedos1T;
		}
		if(is_array($ujsz1T)) {
			$ujsz1='-'.implode('--',$ujsz1T).'-';
		}
	
	//Szomszédos 2 (10km-en belüli templomok)
		if(is_array($oldsz2T)) {
			if(is_array($delsz2T)) {
				foreach($oldsz2T as $ertek) {
					if(!in_array($ertek,$delsz2T)) {
						$ujsz2T[]=$ertek;
					}
				}
			}
			else {
				$ujsz2T=$oldsz2T;
			}
			if(is_array($szomszedos2T)) {
				$ujsz2T=array_merge($ujsz2T,$szomszedos2T);
			}
		}
		elseif(is_array($szomszedos2T)) {
			$ujsz2T=$szomszedos2T;
		}
		if(is_array($ujsz2T)) {
			$ujsz2='-'.implode('--',$ujsz2T).'-';
		}
		
		$query="update templomok set szomszedos1='$ujsz1', szomszedos2='$ujsz2' where id='$tid'";
		if(!mysql_db_query($db_name,$query)) echo "<br>HIBA!<br>$query<br>".mysql_error();


	//És hozzáteszi az új szomszédosokat!!!
		if(is_array($szomszedos2T)) {
			foreach($szomszedos2T as $ertek) {
				$query="select szomszedos2 from templomok where id='$ertek'";
				$lekerdez=mysql_db_query($db_name,$query);
				list($masiksz2)=mysql_fetch_row($lekerdez);
				$masiksz2.="-$tid-";
				mysql_db_query($db_name,"update templomok set szomszedos2='$masiksz2' where id='$ertek'");
			}
		}

	//És kiszedi a törölt szomszédosokat!!!
		if(is_array($delsz2T)) {
			foreach($delsz2T as $ertek) {
				$query="select szomszedos2 from templomok where id='$ertek'";
				$lekerdez=mysql_db_query($db_name,$query);
				list($masiksz2)=mysql_fetch_row($lekerdez);
				$masiksz2=str_replace("-$tid-",'',$masiksz2);
				mysql_db_query($db_name,"update templomok set szomszedos2='$masiksz2' where id='$ertek'");
			}
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
			//Könyvtár ellenõrzése
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
				if(!mysql_db_query($db_name,"delete from kepek where kat='templomok' and kid='$tid' and fajlnev='$ertek'")) echo 'HIBA!<br>'.mysql_error();
			}		
		}

		$kepfeliratT=$_POST['kepfeliratT'];		
		$kepT=$_FILES['kepT']['tmp_name'];
		$kepnevT=$_FILES['kepT']['name'];

		if(is_array($kepT)) {
			foreach($kepT as $id=>$kep) {
				if(!empty($kep)) {			
					//Könyvtár ellenõrzése
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
	
					if ( !copy($kep, "$kimenet") )
						print("HIBA a másolásnál ($kimenet)!<br>\n");
					else  {
						//Bejegyzés az adatbázisba
						$katnev="$nev ($varos)";
						if(!mysql_db_query($db_name,"insert kepek set kat='templomok', kid='$tid', katnev='$katnev', fajlnev='$kepnevT[$id]', felirat='$kepfeliratT[$id]'")) echo 'HIBA!<br>'.mysql_error();
					}
					
					unlink($kep);
	
					$info=getimagesize($kimenet);
					$w=$info[0];
					$h=$info[1];
      
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
				if(!mysql_db_query($db_name,"update kepek set felirat='$kepfeliratmodT[$melyikkep]', sorszam='$ertek', kiemelt='$kiemelt' where kat='templomok' and kid='$tid' and fajlnev='$melyikkep'")) echo 'HIBA!<br>'.mysql_error();
			}
		}		
	
		if($modosit=='i') $kod=miserend_addtemplom($tid);
		elseif($modosit=='m') $kod=miserend_addmise($tid);
		else $kod=miserend_modtemplom();
	}

	return $kod;
}

function miserend_modtemplom() {
	global $db_name,$linkveg,$m_id,$_POST,$u_login,$sid;

	$egyhazmegye=$_POST['egyhazmegye'];
	if($egyhazmegye=='0') $egyhazmegye='mind';
	$kulcsszo=$_POST['kkulcsszo'];	
	$allapot=$_POST['allapot'];

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
	
	$kiir.="<span class=kiscim>A lista szûkíthetõ egyházmegyék, kulcsszó és állapot alapján:</span><br>";
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

//Állapot szerinti szûrés
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
	$kiir.=">javítás alatt lévõ templomok</option>";
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
	$querym="select distinct(templom) from misek where torolte=''";
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

	$query="select id,nev,ismertnev,varos,ok,eszrevetel from templomok $feltetel order by $sort";
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
			$lapozo.="\n<input type=submit value=Elõzõ class=urlap><input type=text size=2 value=$leptet name=leptet class=urlap></form>";
		}
		if($mennyi>$min+$leptet) {
			$lapozo.="\n<form method=post><input type=hidden name=m_id value=$m_id><input type=hidden name=m_op value=modtemplom><input type=hidden name=sid value=$sid><input type=hidden name=kkulcsszo value='".$_POST['kkulcsszo']."'><input type=hidden name=egyhazmegye value=$egyhazmegye><input type=hidden name=min value=$next><input type=hidden name=sort value='$sort'>";
			$lapozo.="\n<input type=submit value=Következõ class=urlap><input type=text size=2 value=$leptet name=leptet class=urlap></form>";
		}
		$kiir.=$lapozo.'<br>';
	}
	else $kiir.="<span class=alap>Jelenleg nincs módosítható templom az adatbázisban.</span>";
	while(list($tid,$tnev,$tismert,$tvaros,$tok,$teszrevetel)=mysql_fetch_row($lekerdez)) {
		$jelzes='';
		if($teszrevetel=='i') $jelzes.="<a href=\"javascript:OpenScrollWindow('naplo.php?kod=templomok&id=$tid&sid=$sid',550,500);\"><img src=img/csomag.gif title='Új észrevételt írtak hozzá!' align=absmiddle border=0></a> ";		
		elseif($teszrevetel=='f') $jelzes.="<a href=\"javascript:OpenScrollWindow('naplo.php?kod=templomok&id=$tid&sid=$sid',550,500);\"><img src=img/csomagf.gif title='Észrevétel javítása folyamatban!' align=absmiddle border=0></a> ";		
		elseif($vaneszrevetelT[$tid]) $jelzes.="<a href=\"javascript:OpenScrollWindow('naplo.php?kod=templomok&id=$tid&sid=$sid',550,500);\"><img src=img/csomag1.gif title='Észrevételek!' align=absmiddle border=0></a> ";		
		if(!$vanmiseT[$tid]) {
			$jelzes.="<img src=img/lampa.gif title='Nincs hozzá mise!' align=absmiddle> ";
		}		
		//Jelzés beállítása -> lampa = nincs kategorizalva, ora = varakozik ok=n, tilos = megjelenhet X, jegyzettömb - szerkesztés alatt (megnyitva)
		//if(!empty($megnyitva)) $jelzes.="<img src=img/edit.gif title='Megnyitva: $megnyitva' align=absmiddle> ";
		//if(empty($rovatkat)) $jelzes.="<img src=img/lampa.gif title='Nincs kategórizálva!' align=absmiddle> ";
		//if(!strstr($megjelenhet,'kurir')) $jelzes.="<img src=img/tilos.gif title='Megjelenés nincs beállítva!' align=absmiddle> ";
		//if($ok!='i') $jelzes.="<img src=img/ora.gif title='Feltöltött hír, áttekintésre vár!' align=absmiddle> ";
		if($tok=='n') $jelzes.="<img src=img/tilos.gif title='Nem engedélyezett!' align=absmiddle> ";
		elseif($tok=='f') $jelzes.="<img src=img/ora.gif title='Feltöltött/módosított templom, áttekintésre vár!' align=absmiddle> ";
		
		$kiir.="\n$jelzes <a href=?m_id=$m_id&m_op=addtemplom&tid=$tid$linkveg class=felsomenulink title='$tismert'><b>- $tnev</b><font color=#8D317C> ($tvaros)</font></a> - <a href=?m_id=$m_id&m_op=addmise&tid=$tid$linkveg class=felsomenulink><img src=img/mise_edit.png title='misék' align=absmiddle border=0>szentmise</a> - <a href=?m_id=$m_id&m_op=deltemplom&tid=$tid$linkveg class=link><img src=img/del.jpg border=0 alt=Töröl align=absmiddle> töröl</a><br>";
	}

	$kiir.='<br>';
	$kiir.=$lapozo;

	$adatT[2]='<span class=alcim>Templomok, miserendek módosítása</span><br><br>'.$kiir;
	$tipus='doboz';
	$tartalom.=formazo($adatT,$tipus);	
	
	$kod=$tartalom;


	return $kod;
}

function miserend_addmise($tid) {
	global $sid,$linkveg,$m_id,$db_name,$onload,$u_beosztas,$u_login;	
	
	$most=date("Y-m-d H:i:s");		
	
	$query="select nap,ido,idoszamitas,nyelv,milyen,megjegyzes from misek where templom='$tid' and torolte='' order by nap,ido";
	if(!$lekerdez=mysql_db_query($db_name,$query)) echo 'HIBA!<br>'.mysql_error();	
	if(mysql_num_rows($lekerdez)>0) {
		$idopontT=array('','-','-','-','-','-','-','-');
		$idoponttT=array('','-','-','-','-','-','-','-');
	}
	while(list($nap,$ido,$idoszamitas,$nyelv,$milyen,$megjegyzes)=mysql_fetch_row($lekerdez)) {
		$ido=substr($ido,0,-3);
		$ido=str_replace(':',',',$ido);
		if($idoszamitas=='ny') {
			$misenyaridb[$nap]++;
			if($idopontT[$nap]!='-') $idopontT[$nap].='+'.$ido;
			else $idopontT[$nap]=$ido;
			if(!empty($nyelvT[$nap])) $nyelvT[$nap].='+'.$nyelv;
			else $nyelvT[$nap]=$nyelv;
			if(!empty($gitarosT[$nap])) $gitarosT[$nap].=$milyen.'+';
			else $gitarosT[$nap]=$milyen.'+';
			if(!empty($megjT[$nap])) $megjT[$nap].=$megjegyzes."+";
			else $megjT[$nap]=$megjegyzes."+";		
		}
		if($idoszamitas=='t') {
			$misetelidb[$nap]++;
			if($idoponttT[$nap]!='-') $idoponttT[$nap].='+'.$ido;
			else $idoponttT[$nap]=$ido;
			if(!empty($nyelvtT[$nap])) $nyelvtT[$nap].='+'.$nyelv;
			else $nyelvtT[$nap]=$nyelv;
			if(!empty($gitarostT[$nap])) $gitarostT[$nap].=$milyen.'+';
			else $gitarostT[$nap]=$milyen.'+';
			if(!empty($megjtT[$nap])) $megjtT[$nap].=$megjegyzes."+";
			else $megjtT[$nap]=$megjegyzes."+";	
		}
	}
	list($tnev,$tvaros,$datumtol,$datumig,$misemegj,$frissites)=mysql_fetch_row(mysql_db_query($db_name,"select nev,varos,nyariido,teliido,misemegj,frissites from templomok where id='$tid'"));

	if(is_array($gitarosT)) {
		foreach($gitarosT as $kulcs=>$ertek) {
			$csakplusz='';
			$hossz=strlen($gitarosT[$kulcs]);
			$ujhossz=$hossz-1;
			$gitarosT[$kulcs]=substr($gitarosT[$kulcs],0,$ujhossz);
			for($i=0;$i<($misenyaridb[$kulcs]-1);$i++) {
				$csakplusz.='+';
			}
			if($gitarosT[$kulcs]==$csakplusz) $gitarosT[$kulcs]='';
		}
	}
	
	if(is_array($megjT)) {
		foreach($megjT as $kulcs=>$ertek) {
			$csakplusz='';
			$hossz=strlen($megjT[$kulcs]);
			$ujhossz=$hossz-1;
			$megjT[$kulcs]=substr($megjT[$kulcs],0,$ujhossz);
			for($i=0;$i<($misenyaridb[$kulcs]-1);$i++) {
				$csakplusz.="+";
			}
			if($megjT[$kulcs]==$csakplusz) $megjT[$kulcs]='';
			else str_replace("+","\n+",$megjT[$kulcs]);
		}
	}

	$urlap.="\n<FORM ENCTYPE='multipart/form-data' method=post>";

	$urlap.="\n<input type=hidden name=m_id value=$m_id><input type=hidden name=sid value=$sid>";
	$urlap.="\n<input type=hidden name=m_op value=addingmise><input type=hidden name=tid value=$tid>";
	$urlap.="\n<input type=hidden name=datumtol value=$datumtol><input type=hidden name=datumig value=$datumig>";
	
	$urlap.='<table cellpadding=4 width=100%>';

//név
	$urlap.="\n<tr><td bgcolor=#F5CC4C><div class=kiscim align=right>Templom neve:</div></td><td bgcolor=#F5CC4C><span class=kiscim>$tnev ($tvaros)</span></td></tr>";

//idõszámítás
	$urlap.="\n<tr><td bgcolor=#efefef><div class=kiscim align=right>Nyári idõszámítás:</div></td><td bgcolor=#efefef><span class=kiscim>$datumtol - $datumig</span><span class=alap> (a templom adatainál módosítható!)</span></td></tr>";

//Misemegjegyzés
	$urlap.="\n<tr><td bgcolor=#D6F8E6><span class=kiscim>Kiegészítõ infók:</span><br><a href=\"javascript:OpenNewWindow('sugo.php?id=41',200,300);\"><img src=img/sugo.gif border=0 title='Súgó' align=absmiddle></a></td><td bgcolor=#D6F8E6>";
	$urlap.="<span class=alap>Rendszeres rózsafûzér, szentségimádás, hittan, stb.</span><br><textarea name=misemegj class=urlap cols=50 rows=10>$misemegj</textarea></td></tr>";

//miserend
	$urlap.="\n<tr><td><span class=kiscim>Miseidõpontok:</span></td><td>";
	$urlap.="&nbsp;</td></tr>";

	$ma=date('Y-m-d');
	$urlap.="\n<tr><td bgcolor=#D6F8E6><span class=kiscim>Frissítés:</span></td><td bgcolor=#D6F8E6>";
	$urlap.="<input type=checkbox name=frissit value='i' checked class=urlap><span class=alap>Dátum frissítése módosítás esetén (utolsó frissítés: $frissites)</span></td></tr>";

//hétfõ
	$urlap.="\n<tr><td bgcolor=#efefef><div class=kiscim align=right>hétfõ:</div></td><td bgcolor=#efefef>";
	$urlap.="<input type=text name=idopontT[1] value=\"$idopontT[1]\" class=urlap size=30><span class=alap> nyári misekezdések</span>";
	$urlap.="<br>&nbsp; &nbsp;<input type=text name=idoponttT[1] value=\"$idoponttT[1]\" class=urlap size=30><span class=alap> téli misekezdések, ha különbözik</span>";
	$urlap.="<br><img src=img/space.gif width=5 height=4>";
	$urlap.="<br><input type=text name=nyelvT[1] value=\"$nyelvT[1]\" class=urlap size=30><span class=alap> nyelvek nyáron -> </span><a title='latin' class=alap>va, </a><a title='német' class=alap>de, </a><a title='szlovák' class=alap>sk, </a><a title='lengyel' class=alap>pl, </a><a title='szlovén' class=alap>si, </a><a title='horvát' class=alap>hr, </a><a title='olasz' class=alap>it, </a><a title='görög' class=alap>gr, </a><a title='angol' class=alap>en, </a><a title='francia' class=alap>fr, </a>";
	$urlap.="<br>&nbsp; &nbsp;<input type=text name=nyelvtT[1] value=\"$nyelvtT[1]\" class=urlap size=30><span class=alap> nyelvek télen</span>";
	$urlap.="<br><img src=img/space.gif width=5 height=4>";
	$urlap.="<br><input type=text name=gitarosT[1] value=\"$gitarosT[1]\" class=urlap size=30><span class=alap> [<b>g</b>]itáros, [<b>cs</b>]endes, [<b>d</b>]iák nyáron</span>";
	$urlap.="<br>&nbsp; &nbsp;<input type=text name=gitarostT[1] value=\"$gitarostT[1]\" class=urlap size=30><span class=alap> télen</span>";
	$urlap.="<br><img src=img/space.gif width=5 height=4>";
	$urlap.="<br><textarea name=megjT[1] class=urlap cols=60 rows=4>$megjT[1]</textarea><span class=alap> megjegyzések</span>";
	$urlap.="<br><textarea name=megjtT[1] class=urlap cols=60 rows=4>$megjtT[1]</textarea><span class=alap> téli megjegyzések</span>";
	$urlap.="</td></tr>";

//kedd
	$urlap.="\n<tr><td bgcolor=#D6F8E6><div class=kiscim align=right>kedd:</div></td><td bgcolor=#D6F8E6>";
	$urlap.="<input type=text name=idopontT[2] value=\"$idopontT[2]\" class=urlap size=30><span class=alap> nyári misekezdések</span>";
	$urlap.="<br>&nbsp; &nbsp;<input type=text name=idoponttT[2] value=\"$idoponttT[2]\" class=urlap size=30><span class=alap> téli misekezdések, ha különbözik</span>";
	$urlap.="<br><img src=img/space.gif width=5 height=4>";
	$urlap.="<br><input type=text name=nyelvT[2] value=\"$nyelvT[2]\" class=urlap size=30><span class=alap> nyelvek nyáron -> </span><a title='latin' class=alap>va, </a><a title='német' class=alap>de, </a><a title='szlovák' class=alap>sk, </a><a title='lengyel' class=alap>pl, </a><a title='szlovén' class=alap>si, </a><a title='horvát' class=alap>hr, </a><a title='olasz' class=alap>it, </a><a title='görög' class=alap>gr, </a><a title='angol' class=alap>en, </a><a title='francia' class=alap>fr, </a>";
	$urlap.="<br>&nbsp; &nbsp;<input type=text name=nyelvtT[2] value=\"$nyelvtT[2]\" class=urlap size=30><span class=alap> nyelvek télen</span>";
	$urlap.="<br><img src=img/space.gif width=5 height=4>";
	$urlap.="<br><input type=text name=gitarosT[2] value=\"$gitarosT[2]\" class=urlap size=30><span class=alap> [<b>g</b>]itáros, [<b>cs</b>]endes, [<b>d</b>]iák nyáron</span>";
	$urlap.="<br>&nbsp; &nbsp;<input type=text name=gitarostT[2] value=\"$gitarostT[2]\" class=urlap size=30><span class=alap> télen</span>";
	$urlap.="<br><img src=img/space.gif width=5 height=4>";
	$urlap.="<br><textarea name=megjT[2] class=urlap cols=60 rows=4>$megjT[2]</textarea><span class=alap> megjegyzések</span>";
	$urlap.="<br><textarea name=megjtT[2] class=urlap cols=60 rows=4>$megjtT[2]</textarea><span class=alap> téli megjegyzések</span>";
	$urlap.="</td></tr>";

//szerda
	$urlap.="\n<tr><td bgcolor=#efefef><div class=kiscim align=right>szerda:</div></td><td bgcolor=#efefef>";
	$urlap.="<input type=text name=idopontT[3] value=\"$idopontT[3]\" class=urlap size=30><span class=alap> nyári misekezdések</span>";
	$urlap.="<br>&nbsp; &nbsp;<input type=text name=idoponttT[3] value=\"$idoponttT[3]\" class=urlap size=30><span class=alap> téli misekezdések, ha különbözik</span>";
	$urlap.="<br><img src=img/space.gif width=5 height=4>";
	$urlap.="<br><input type=text name=nyelvT[3] value=\"$nyelvT[3]\" class=urlap size=30><span class=alap> nyelvek nyáron -> </span><a title='latin' class=alap>va, </a><a title='német' class=alap>de, </a><a title='szlovák' class=alap>sk, </a><a title='lengyel' class=alap>pl, </a><a title='szlovén' class=alap>si, </a><a title='horvát' class=alap>hr, </a><a title='olasz' class=alap>it, </a><a title='görög' class=alap>gr, </a><a title='angol' class=alap>en, </a><a title='francia' class=alap>fr, </a>";
	$urlap.="<br>&nbsp; &nbsp;<input type=text name=nyelvtT[3] value=\"$nyelvtT[3]\" class=urlap size=30><span class=alap> nyelvek télen</span>";
	$urlap.="<br><img src=img/space.gif width=5 height=4>";
	$urlap.="<br><input type=text name=gitarosT[3] value=\"$gitarosT[3]\" class=urlap size=30><span class=alap> [<b>g</b>]itáros, [<b>cs</b>]endes, [<b>d</b>]iák nyáron</span>";
	$urlap.="<br>&nbsp; &nbsp;<input type=text name=gitarostT[3] value=\"$gitarostT[3]\" class=urlap size=30><span class=alap> télen</span>";
	$urlap.="<br><img src=img/space.gif width=5 height=4>";
	$urlap.="<br><textarea name=megjT[3] class=urlap cols=60 rows=4>$megjT[3]</textarea><span class=alap> megjegyzések</span>";
	$urlap.="<br><textarea name=megjtT[3] class=urlap cols=60 rows=4>$megjtT[3]</textarea><span class=alap> téli megjegyzések</span>";
	$urlap.="</td></tr>";

//csütörtök
	$urlap.="\n<tr><td bgcolor=#D6F8E6><div class=kiscim align=right>csütörtök:</div></td><td bgcolor=#D6F8E6>";
	$urlap.="<input type=text name=idopontT[4] value=\"$idopontT[4]\" class=urlap size=30><span class=alap> nyári misekezdések</span>";
	$urlap.="<br>&nbsp; &nbsp;<input type=text name=idoponttT[4] value=\"$idoponttT[4]\" class=urlap size=30><span class=alap> téli misekezdések, ha különbözik</span>";
	$urlap.="<br><img src=img/space.gif width=5 height=4>";
	$urlap.="<br><input type=text name=nyelvT[4] value=\"$nyelvT[4]\" class=urlap size=30><span class=alap> nyelvek nyáron -> </span><a title='latin' class=alap>va, </a><a title='német' class=alap>de, </a><a title='szlovák' class=alap>sk, </a><a title='lengyel' class=alap>pl, </a><a title='szlovén' class=alap>si, </a><a title='horvát' class=alap>hr, </a><a title='olasz' class=alap>it, </a><a title='görög' class=alap>gr, </a><a title='angol' class=alap>en, </a><a title='francia' class=alap>fr, </a>";
	$urlap.="<br>&nbsp; &nbsp;<input type=text name=nyelvtT[4] value=\"$nyelvtT[4]\" class=urlap size=30><span class=alap> nyelvek télen</span>";
	$urlap.="<br><img src=img/space.gif width=5 height=4>";
	$urlap.="<br><input type=text name=gitarosT[4] value=\"$gitarosT[4]\" class=urlap size=30><span class=alap> [<b>g</b>]itáros, [<b>cs</b>]endes, [<b>d</b>]iák nyáron</span>";
	$urlap.="<br>&nbsp; &nbsp;<input type=text name=gitarostT[4] value=\"$gitarostT[4]\" class=urlap size=30><span class=alap> télen</span>";
	$urlap.="<br><img src=img/space.gif width=5 height=4>";
	$urlap.="<br><textarea name=megjT[4] class=urlap cols=60 rows=4>$megjT[4]</textarea><span class=alap> megjegyzések</span>";
	$urlap.="<br><textarea name=megjtT[4] class=urlap cols=60 rows=4>$megjtT[4]</textarea><span class=alap> téli megjegyzések</span>";
	$urlap.="</td></tr>";

//péntek
	$urlap.="\n<tr><td bgcolor=#efefef><div class=kiscim align=right>péntek:</div></td><td bgcolor=#efefef>";
	$urlap.="<input type=text name=idopontT[5] value=\"$idopontT[5]\" class=urlap size=30><span class=alap> nyári misekezdések</span>";
	$urlap.="<br>&nbsp; &nbsp;<input type=text name=idoponttT[5] value=\"$idoponttT[5]\" class=urlap size=30><span class=alap> téli misekezdések, ha különbözik</span>";
	$urlap.="<br><img src=img/space.gif width=5 height=4>";
	$urlap.="<br><input type=text name=nyelvT[5] value=\"$nyelvT[5]\" class=urlap size=30><span class=alap> nyelvek nyáron -> </span><a title='latin' class=alap>va, </a><a title='német' class=alap>de, </a><a title='szlovák' class=alap>sk, </a><a title='lengyel' class=alap>pl, </a><a title='szlovén' class=alap>si, </a><a title='horvát' class=alap>hr, </a><a title='olasz' class=alap>it, </a><a title='görög' class=alap>gr, </a><a title='angol' class=alap>en, </a><a title='francia' class=alap>fr, </a>";
	$urlap.="<br>&nbsp; &nbsp;<input type=text name=nyelvtT[5] value=\"$nyelvtT[5]\" class=urlap size=30><span class=alap> nyelvek télen</span>";
	$urlap.="<br><img src=img/space.gif width=5 height=4>";
	$urlap.="<br><input type=text name=gitarosT[5] value=\"$gitarosT[5]\" class=urlap size=30><span class=alap> [<b>g</b>]itáros, [<b>cs</b>]endes, [<b>d</b>]iák nyáron</span>";
	$urlap.="<br>&nbsp; &nbsp;<input type=text name=gitarostT[5] value=\"$gitarostT[5]\" class=urlap size=30><span class=alap> télen</span>";
	$urlap.="<br><img src=img/space.gif width=5 height=4>";
	$urlap.="<br><textarea name=megjT[5] class=urlap cols=60 rows=4>$megjT[5]</textarea><span class=alap> megjegyzések</span>";
	$urlap.="<br><textarea name=megjtT[5] class=urlap cols=60 rows=4>$megjtT[5]</textarea><span class=alap> téli megjegyzések</span>";
	$urlap.="</td></tr>";

//szombat
	$urlap.="\n<tr><td bgcolor=#F1BF8F><div class=kiscim align=right>szombat:</div></td><td bgcolor=#F1BF8F>";
	$urlap.="<input type=text name=idopontT[6] value=\"$idopontT[6]\" class=urlap size=30><span class=alap> nyári misekezdések</span>";
	$urlap.="<br>&nbsp; &nbsp;<input type=text name=idoponttT[6] value=\"$idoponttT[6]\" class=urlap size=30><span class=alap> téli misekezdések, ha különbözik</span>";
	$urlap.="<br><img src=img/space.gif width=5 height=4>";
	$urlap.="<br><input type=text name=nyelvT[6] value=\"$nyelvT[6]\" class=urlap size=30><span class=alap> nyelvek nyáron -> </span><a title='latin' class=alap>va, </a><a title='német' class=alap>de, </a><a title='szlovák' class=alap>sk, </a><a title='lengyel' class=alap>pl, </a><a title='szlovén' class=alap>si, </a><a title='horvát' class=alap>hr, </a><a title='olasz' class=alap>it, </a><a title='görög' class=alap>gr, </a><a title='angol' class=alap>en, </a><a title='francia' class=alap>fr, </a>";
	$urlap.="<br>&nbsp; &nbsp;<input type=text name=nyelvtT[6] value=\"$nyelvtT[6]\" class=urlap size=30><span class=alap> nyelvek télen</span>";
	$urlap.="<br><img src=img/space.gif width=5 height=4>";
	$urlap.="<br><input type=text name=gitarosT[6] value=\"$gitarosT[6]\" class=urlap size=30><span class=alap> [<b>g</b>]itáros, [<b>cs</b>]endes, [<b>d</b>]iák nyáron</span>";
	$urlap.="<br>&nbsp; &nbsp;<input type=text name=gitarostT[6] value=\"$gitarostT[6]\" class=urlap size=30><span class=alap> télen</span>";
	$urlap.="<br><img src=img/space.gif width=5 height=4>";
	$urlap.="<br><textarea name=megjT[6] class=urlap cols=60 rows=4>$megjT[6]</textarea><span class=alap> megjegyzések</span>";
	$urlap.="<br><textarea name=megjtT[6] class=urlap cols=60 rows=4>$megjtT[6]</textarea><span class=alap> téli megjegyzések</span>";
	$urlap.="</td></tr>";

//vasárnap
	$urlap.="\n<tr><td bgcolor=#E67070><div class=kiscim align=right>vasárnap:</div></td><td bgcolor=#E67070>";
	$urlap.="<input type=text name=idopontT[7] value=\"$idopontT[7]\" class=urlap size=30><span class=alap> nyári misekezdések</span>";
	$urlap.="<br>&nbsp; &nbsp;<input type=text name=idoponttT[7] value=\"$idoponttT[7]\" class=urlap size=30><span class=alap> téli misekezdések, ha különbözik</span>";
	$urlap.="<br><img src=img/space.gif width=5 height=4>";
	$urlap.="<br><input type=text name=nyelvT[7] value=\"$nyelvT[7]\" class=urlap size=30><span class=alap> nyelvek nyáron -> </span><a title='latin' class=alap>va, </a><a title='német' class=alap>de, </a><a title='szlovák' class=alap>sk, </a><a title='lengyel' class=alap>pl, </a><a title='szlovén' class=alap>si, </a><a title='horvát' class=alap>hr, </a><a title='olasz' class=alap>it, </a><a title='görög' class=alap>gr, </a><a title='angol' class=alap>en, </a><a title='francia' class=alap>fr, </a>";
	$urlap.="<br>&nbsp; &nbsp;<input type=text name=nyelvtT[7] value=\"$nyelvtT[7]\" class=urlap size=30><span class=alap> nyelvek télen</span>";
	$urlap.="<br><img src=img/space.gif width=5 height=4>";
	$urlap.="<br><input type=text name=gitarosT[7] value=\"$gitarosT[7]\" class=urlap size=30><span class=alap> [<b>g</b>]itáros, [<b>cs</b>]endes, [<b>d</b>]iák nyáron</span>";
	$urlap.="<br>&nbsp; &nbsp;<input type=text name=gitarostT[7] value=\"$gitarostT[7]\" class=urlap size=30><span class=alap> télen</span>";
	$urlap.="<br><img src=img/space.gif width=5 height=4>";
	$urlap.="<br><textarea name=megjT[7] class=urlap cols=60 rows=4>$megjT[7]</textarea><span class=alap> megjegyzések</span>";
	$urlap.="<br><textarea name=megjtT[7] class=urlap cols=60 rows=4>$megjtT[7]</textarea><span class=alap> téli megjegyzések</span>";
	$urlap.="</td></tr>";

//súgó
	$urlap.="\n<tr><td><span class=kiscim>Kitöltési útmutató:</span></td><td>";
	$urlap.="<span class=alap>Kitöltésnél a hétfõ az alapnap, a többi napnál, ha nincs kitöltve, a hétfõi miseadatokat másolja be automatikusan (csak, ha nincs kitöltve miseidõpont!). Ha valamelyik napon nincs mise, ott ki kell húzni egy gondolatjellel (<b>-</b>), így akkor nem másolja. A téli adatoknál mindig a nyári az alapértelmezett, ha ott nincs kitöltve, akkor a nyárit másolja be automatikusan, nem a hétfõi télit. Itt is érvényes, ha télen valami nincs, akkor ki kell húzni!</span>";

	$urlap.="<br><br><span class=alap> <b>misekezdések</b> <input type=text value=\"9,00+18,00\" class=urlap size=10 disabled> Az idõpontnál <b>óra,perc (0,00)</b> a formátum, több idõpontnál az <b>elválasztó a +</b> jel (példa az ûrlapban). <br>Téli adatokat csak akkor kell megadni, ha az eltérõ a nyáritól.</span>";
	
	$urlap.="<br><br><span class=alap><b>nyelvek</b> (h, hu vagy üres=magyar, en=angol, de=német, it=olasz, fr=francia, va=latin, gr=görög, sk=szlovák, hr=horvát, pl=lengyel, si=szlovén => további nyelvek esetén az internetes 2 betûs végzõdés az irányadó!)<br>A nyelvek a beállított miseidõpontokhoz tartoznak, így az elválasztó itt is a <b>+</b> jel. Elõfordulhatnak periódusok is, ebben az esetben a nyelv mellett a periódus számát kell feltüntetni, pl de2,va3 -> minden hónap második hetén német, harmadik hetén latin (A vesszõ nem fontos, csak jobban tagolja). Ha minden héten az adott nyelven van mise, akkor nem kell megjegyzést írni, viszont <u>periódusok vagy egyéni esetekben a mejegyzés rovatba szövegesen is tüntessük föl</u>!<br>";
	$urlap.="\n<u>Példa 1:</u> a fenti 9-es mise magyar nyelvû, az esti 6-os viszont minden hónap második vasárnapján latin: <input type=text disabled class=urlap value=\"h0+,va2\" size=10> (<b>h0+va2</b>)";
	$urlap.="\n<br><u>Példa 2:</u> a 9-es mise mindig német nyelvû, az esti 6-os viszont minden hónap második vasárnapján angol, egyébként latin:  <input type=text disabled class=urlap value=\"de0+va1,en2,va3,va4\" size=10> (<b>de0+va1,en2,va3,va4</b>)";
	$urlap.="\n<br><u>Példa 3:</u> alapeset, minden mise magyar: ebben az esetben nem kell kitölteni</span>";

	$urlap.="<br><br><span class=alap><b>gitáros, diák, csendes</b> misék esetén a nyelvekhez hasonlóan, a beállított miseidõpontokhoz tartoznak, így az elválasztó itt is a <b>+</b> jel. Elõfordulhatnak periódusok is, ebben az esetben a hét számát is fel kell tüntetni, periódus nélkül 0-át kell a betükód mögé írni. Fontos, hogy minden esetben a mejegyzés rovatba is tüntessük föl!<br>Betükódok: gitáros = g, csendes = cs, diák = d";
	$urlap.="\n<br><u>Példa 1:</u> a fenti 9-es mise gitáros, az esti 6-os viszont csendes: <input type=text disabled class=urlap value=\"g0+cs0\" size=10> (<b>g0+cs0</b>)";
	$urlap.="\n<br><u>Példa 2:</u> a 9-es mise diák mise és a hónap minden második vasárnapján gitáros, az esti 6-os viszont rendes orgonás:  <input type=text disabled class=urlap value=\"d0,g2+\" size=10> (<b>d0,g2+</b>)";

	$urlap.="<br><br><span class=alap><b>megjegyzés</b> mivel nem minden paramétert tudunk pontosan beállítani, illetve lehetnek egyéb eltérések is, a megjegyzés rovatba mindig tüntessük föl a bizonytalan dolgokat. Pl. minden második héten gitáros mise, de ünnepeknél, betegségeknél csúszhat. A megjegyzésnél is a <b>+</b> jel az elválasztó az egyes miseidõpontoknak megfelelõen. Tagolni lehet sortöréssel, nincs jelentõsége.</span>";

	$urlap.="</td></tr>";


	$urlap.='</table>';

	$urlap.="\n<br><input type=submit value=Mehet class=urlap>";
	if($tid>0) {
		$urlap.="<input type=checkbox name=modosit value=i class=urlap checked><span class=alap> és újra módosít</span>";
		//$urlap.=" &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <a href=?m_id=$m_id&m_op=addmegse&hid=$hid&kod=$kod$linkveg class=link><font color=red>Kilépés módosítás nélkül</font></a>";
	}
	else $urlap.="<input type=hidden name=modosit value=i>";
	$urlap.="\n</form>";

	$adatT[2]='<span class=alcim>Templom feltöltése / módosítása</span><br><br>'.$urlap;
	$tipus='doboz';
	$tartalom.=formazo($adatT,$tipus);	
	
	$kod=$tartalom;

	return $kod;
}

function miserend_addingmise() {
	global $_POST,$_SERVER,$sid,$m_id,$db_name,$u_login;

	$ip=$_SERVER['REMOTE_ADDR'];
    $host = gethostbyaddr($ip);

	$tid=$_POST['tid'];
	$frissit=$_POST['frissit'];
	$idopontT=$_POST['idopontT'];
	$idoponttT=$_POST['idoponttT'];
	$nyelvT=$_POST['nyelvT'];
	$nyelvtT=$_POST['nyelvtT'];
	$gitarosT=$_POST['gitarosT'];
	$gitarostT=$_POST['gitarostT'];
	$misemegj=$_POST['misemegj'];
	$megjT=$_POST['megjT'];
	$megjT=str_replace("\n",'',$megjT);
	$megjtT=$_POST['megjtT'];
	$megjtT=str_replace("\n",'',$megjtT);
	$modosit=$_POST['modosit'];
	$datumtol=$_POST['datumtol'];
	$datumig=$_POST['datumig'];

	if($tid>0) {
		$ma=date('Y-m-d');
		$most=date('Y-m-d H:i:s');
		$query="update misek set torles='$most', torolte='$u_login' where templom='$tid' and torolte=''";
		mysql_db_query($db_name,$query);
		list($log)=mysql_fetch_row(mysql_db_query($db_name,"select log from templomok where id='$tid'"));
		$log.="\nMISE_MOD: $u_login ($most - [$ip - $host])";
		if($frissit=='i') $frissites=", frissites='$ma'";
		$query="update templomok set misemegj='$misemegj', log='$log' $frissites where id='$tid'";
		mysql_db_query($db_name,$query);
	}


	for($nap=1;$nap<=7;$nap++) {
		$miseT=$idopontT[$nap];
		$misetT=$idoponttT[$nap]; //téli
		$nyelvekT=explode('+',$nyelvT[$nap]);
		$milyenT=explode('+',$gitarosT[$nap]);
		$megjegyzesT=explode('+',$megjT[$nap]);

		if(empty($miseT)) { 
			//ha nincs kitöltve, akkor a hétfõit vesszük át
			$miseT=$idopontT[1];
			$misetT=$idoponttT[1];
			$nyelvekT=explode('+',$nyelvT[1]);
			$milyenT=explode('+',$gitarosT[1]);
			$megjegyzesT=explode('+',$megjT[1]);
		}		

		$miseT=str_replace(',',':',$miseT); // a ,-õt átalakítjuk : ponttá a rögzítéshez
		$misekT=explode('+',$miseT); //ha több lett megadva
		if(!empty($misetT)) {
			//Ha ki lett töltve (tehát különbözik a nyáritól)
			$misetT=str_replace(',',':',$misetT); // téliben is átalakítjuk
			$misektT=explode('+',$misetT); //ha több lett megadva
		}
		else {
			//Ha nem lett kitöltve, akkor a nyári érvényes télre is
			$misektT=$misekT;
		}

		if(!empty($nyelvtT[$nap])) $nyelvektT=explode('+',$nyelvtT[$nap]); 
		else $nyelvektT=$nyelvekT; 
		if(!empty($gitarostT[$nap])) $milyentT=explode('+',$gitarostT[$nap]);
		else $milyentT=$milyenT;
		if(!empty($megjtT[$nap])) $megjegyzestT=explode('+',$megjtT[$nap]);
		else $megjegyzestT=$megjegyzesT;

		foreach($misekT as $id=>$mise) {
			if($mise!='-' and !empty($mise)) {
				if(empty($nyelvekT[$id])) $nyelvekT[$id]='h0';
				$query="insert misek set templom='$tid', nap='$nap', ido='$mise', idoszamitas='ny', datumtol='$datumtol', datumig='$datumig', nyelv='$nyelvekT[$id]', milyen='$milyenT[$id]', megjegyzes='$megjegyzesT[$id]', modositotta='$u_login', moddatum='$most'";
				mysql_db_query($db_name,$query);
			}
		
		}
		foreach($misektT as $id=>$mise) {
			if($mise!='-' and !empty($mise)) {
				if(empty($nyelvektT[$id])) $nyelvektT[$id]='h0';
				$query="insert misek set templom='$tid', nap='$nap', ido='$mise', idoszamitas='t', datumtol='$datumtol', datumig='$datumig', nyelv='$nyelvektT[$id]', milyen='$milyentT[$id]', megjegyzes='$megjegyzestT[$id]', modositotta='$u_login', moddatum='$most'";
				mysql_db_query($db_name,$query);
			}
		
		}

	}



	if($modosit=='i') {
		$kod=miserend_addmise($tid);
	}
	else {
		$kod=miserend_modtemplom();
	}
	
	return $kod;
}



function miserend_deltemplom() {
	global $_GET,$db_name,$linkveg,$m_id,$u_login;

	$tid=$_GET['tid'];

	$kiir="<span class=alcim>Templom és miserend törlése</span><br><br>";
	$kiir.="\n<span class=kiscim>Biztosan törölni akarod a következõ templomot?<br><font color=red>FIGYELEM! A kapcsolódó misék és képek is törlõdnek!</font></span>";
		
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
		$txt.="<br><br><span class=alap>Az alábbi szöveget kimásolva excelbe importálható.<br>Excelben: Adatok / Szövegbõl oszlopok -> táblázattá alakítható</span><br><textarea class=urlap cols=60 rows=20>$excel</textarea>";
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


//Jogosultság ellenõrzése
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
}
}
else {
	$tartalom="\n<span class=hiba>HIBA! Nincs hozzá jogosultságod!</span>";
}

?>
