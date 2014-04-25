<?php

function hirek_urlap($hid) {
	global $sessid,$linkveg,$m_id,$db_name,$onload,$u_login,$u_jogok;	

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

	$query="select id,nev from orszagok";
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

	$szerkesztheti=false;
	if(strstr($u_jogok,'hirek')) $szerkesztheti=true;
	
	if($hid>0) {
		$most=date("Y-m-d H:i:s");
		$urlap.=include('editscript2.php'); //Csak, ha módosításról van szó

		$query="select kontakt,kontaktmail,cim,intro,szoveg,kerdes,orszag,megye,varos,egyhazmegye,espereskerulet,datum,aktualis,tol,hatarido,szervezotipus,szervezonev,szervezoinfo,fizetos,szamlalo,fohir,rovatkat,kulcsszo,galeria,kiemelt,kapcsolodas,ok,hirlevel,feltette,megbizhato,modositotta,moddatum,megnyitva,megnyitvamikor,adminmegj,log from hirek where id='$hid'";
		if(!$lekerdez=mysql_db_query($db_name,$query)) echo 'HIBA!<br>'.mysql_error();	list($kontakt,$kontaktmail,$cim,$intro,$szoveg,$kerdes,$orszag,$megye,$varos,$egyhazmegye,$espereskerulet,$datum,$aktualis,$tol,$hatarido,$szervezotipus,$szervezonev,$szervezoinfo,$fizetos,$szamlalo,$fohir,$rovatkat,$kulcsszo,$galeria,$kiemelt,$kapcsolodas,$ok,$hirlevel,$feltolto,$megbizhato,$modositotta,$moddatum,$megnyitva,$megnyitvamikor,$adminmegj,$log)=mysql_fetch_row($lekerdez);

		if($u_login==$feltolto and $megbizhato=='i') $szerkesztheti=true; //Ha megbízható, akkor szerkesztheti
		elseif(strstr($u_jogok,'hirek')) $szerkesztheti=true; //Ha admin akkor is
		elseif($u_login!=$feltolto or ($u_login==$feltolto and $ok!='f')) { //Ha nem a saját híre, vagy admin már belenyúlt, akkor nem szerkeszthei!
			echo "HIBA! - Te nem nyithatod meg!";
			exit();
		}

		$rovatkatT=explode('-',$rovatkat);
		
		mysql_db_query($db_name,"update hirek set megnyitva='$u_login', megnyitvamikor='$most' where id='$hid'"); //Rögzítjük, hogy megnyitotta

		$datum=substr($datum,0,16);
	}
	else {
		$datum=date('Y-m-d H:i');
		$aktualis='';
		$urlapkieg="\n<input type=hidden name=elsofeltoltes value=i>";
	}

	$urlap.="\n<FORM ENCTYPE='multipart/form-data' method=post name=urlap id=urlap>";
	$urlap.=$urlapkieg;

	$urlap.="\n<input type=hidden name=m_id value=$m_id><input type=hidden name=sessid value=$sessid>";
	$urlap.="\n<input type=hidden name=m_op value=addinghirek><input type=hidden name=hid value=$hid>";
	
	$urlap.='<table cellpadding=4>';

//megnyitva
	$lejarat=date('Y-m-d H:i:s',(time()-(60*60*3))); //3 óránál régebben megnyitottnál nem jelez
	if($hid>0 and $megnyitvamikor>=$lejarat and !empty($megnyitva)) {
		$urlap.="\n<tr><td>&nbsp;</td><td><img src=img/edit.gif align=absmiddle><span class=alap><font color=red>Megnyitva!</font> $megnyitva [$megnyitvamikor]</span><br><a href=?m_id=$m_id&m_op=addmegse&hid=$hid&ki=$megnyitva&mikor=".rawurlencode($megnyitvamikor)."$linkveg class=link><b><font color=red>Vissza, mégsem szerkesztem</font></b></a></td></tr>";
	}
	elseif($hid>0) {
		$urlap.="\n<tr><td>&nbsp;</td><td><a href=?m_id=$m_id&m_op=addmegse&hid=$hid&kod=$kod$linkveg class=link><font color=red>Kilépés módosítás nélkül</font></a>";
		$moddatumT=explode(' ',$moddatum);
		if($moddatumT[0]==date('Y-m-d')) $moddatumkiir='ma ';
		elseif($moddatumT[0]==date('Y-m-d',(time()-86400))) $moddatumkiir='tegnap ';
		else $moddatumkiir=$moddatumT[0].' ';
		$moddatumido=substr($moddatumT[1],0,5);
		if($moddatumido[0]=='0') $moddatumkiir.=substr($moddatumido,1);
		else $moddatumkiir.=$moddatumido;
		$urlap.="<br><span class=alap>Utoljára szerkesztette: $modositotta [$moddatumkiir]</span>";
		$urlap.="</td></tr>";
	}

//elõnézet
	if($szerkesztheti) {
		if($hid>0) $urlap.="\n<tr><td bgcolor='#efefef'>&nbsp;</td><td bgcolor='#efefef'><a href=?m_id=19&id=$hid$linkveg class=link target=_blank><b>>> Hír megtekintése (elõnézet) <<</b></a></td></tr>";
	}

//megjegyzés	
	$urlap.="\n<tr><td bgcolor=#efefef><div class=kiscim align=right>Megjegyzés:<br><a href=\"javascript:OpenNewWindow('sugo.php?id=29',200,300);\"><img src=img/sugo.gif border=0 title='Súgó'></a></div></td><td bgcolor=#efefef><textarea name=adminmegj class=urlap cols=50 rows=3>$adminmegj</textarea><br><span class=alap> A hírrel, szerkesztésével kapcsolatosan</span></td></tr>";

//kontakt
	$urlap.="\n<tr><td bgcolor=#ffffff><div class=kiscim align=right>Felelõs:<br><a href=\"javascript:OpenNewWindow('sugo.php?id=30',200,300);\"><img src=img/sugo.gif border=0 title='Súgó'></a></div></td><td bgcolor=#ffffff><textarea name=kontakt class=urlap cols=50 rows=2>$kontakt</textarea><span class=alap> név és telefonszám</span><br><input type=text name=kontaktmail size=40 class=urlap value='$kontaktmail'><span class=alap> emailcím</span></td></tr>";

//feltöltõ
	if(strstr($u_jogok,'hirek')) {
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
	}

//cím
	$urlap.="\n<tr><td bgcolor=#F5CC4C><div class=kiscim align=right>Hír címe:</div></td><td bgcolor=#F5CC4C><input type=text name=cim value=\"$cim\" class=urlap size=80 maxlength=250></td></tr>";

//dátum
	$urlap.="\n<tr><td><div class=kiscim align=right>Dátum, idõ:</div></td><td><input type=text name=datum value=\"$datum\" class=urlap size=16 maxlength=16><span class=alap> (amikortól megjelenhet és kereshetõ) <a href=\"javascript:OpenNewWindow('sugo.php?id=31',200,300);\"><img src=img/sugo.gif border=0 title='Súgó'></a></td></tr>";

//hírlevélben
	if($szerkesztheti) {
		$urlap.="\n<tr><td bgcolor=#efefef><div class=kiscim align=right>Hírlevélben:</div></td><td bgcolor=#efefef><input type=radio name=hirlevel value=0 ";
		if($hirlevel=='0')  $urlap.=' checked';
		$urlap.="><span class=alap>nincs</span> <input type=radio name=hirlevel value=c ";
		if(empty($hid) or $hirlevel=='c') $urlap.=' checked';
		$urlap.="><span class=alap>csak cím</span>";

		$urlap.=" <input type=radio name=hirlevel value=i ";
		if($hirlevel=='i') $urlap.=' checked';
		$urlap.="><span class=alap>cím és bevezetõ</span>";

		$urlap.=" <input type=radio name=hirlevel value=t ";
		if($hirlevel=='t') $urlap.=' checked';
		$urlap.="><span class=alap>teljes szöveg</span> <a href=\"javascript:OpenNewWindow('sugo.php?id=32',200,300);\"><img src=img/sugo.gif border=0 title='Súgó'></a></td></tr>";
	}

//aktuális
	if(!empty($aktualis) and !empty($tol) and $tol!='0000-00-00') {
		$aktualiskiir=$tol.'=>';
		$aktualisT=explode('+',$aktualis);
		$mennyi=count($aktualisT);
		$aktualiskiir.=$aktualisT[$mennyi-1];
	}
	else $aktualiskiir=$aktualis;

	$urlap.="\n<tr><td bgcolor=#ECE5C8><div class=kiscim align=right>Aktuális:<br><a href=\"javascript:OpenNewWindow('sugo.php?id=33',200,500);\"><img src=img/sugo.gif border=0 title='Súgó'></a></div></td><td bgcolor=#ECE5C8><span class=alap>(Ha az aktualitás ki van töltve, megjelenik a naptárban! Több idõpont is felvihetõ + jellel elválasztva! Pl.: 2005-05-05+2005-05-16, vagy tól-ig formában Pl.: 2005-05-05=>2005-05-08 Részletek a súgóban!)</span><br><input type=text name=aktualis value=\"$aktualiskiir\" class=urlap size=60 maxlength=255></td></tr>";

//Típus
	$urlap.="\n<tr><td bgcolor=#FFFAE4><div class=kiscim align=right>Szervezés típusa:</div></td><td bgcolor=#FFFAE4><span class=alap> (A naptár napi nézetnél is megjelenik!)</span> <a href=\"javascript:OpenNewWindow('sugo.php?id=45',200,300);\"><img src=img/sugo.gif border=0 title='Súgó'></a>";
	$urlap.="<br><select name=szervezotipus class=urlap><option value=0>Nincs információ</option>";
	$query="select id,nev from szervezotipus";
	$lekerdez=mysql_db_query($db_name,$query);
	while(list($sztid,$sztnev)=mysql_fetch_row($lekerdez)) {
		$urlap.="<option value=$sztid";
		if($sztid==$szervezotipus) $urlap.=' selected';
		$urlap.=">$sztnev</option>";
	}
	$urlap.="</td></tr>";

//Szervezõ adatai
	$urlap.="\n<tr><td bgcolor=#ECE5C8><div class=kiscim align=right>Szervezõ (név):</div></td><td bgcolor=#ECE5C8><span class=alap> (A naptár napi nézetében is megjelenik!)</span> <a href=\"javascript:OpenNewWindow('sugo.php?id=43',200,300);\"><img src=img/sugo.gif border=0 title='Súgó'></a><br><input type=text name=szervezonev value=\"$szervezonev\" class=urlap size=60 maxlength=255></td></tr>";

	$urlap.="\n<tr><td bgcolor=#FFFAE4><div class=kiscim align=right>Szervezõ kontakt:</div></td><td bgcolor=#FFFAE4><span class=alap> (A programmal kapcsolatos további információk elérhetõsége)</span> <a href=\"javascript:OpenNewWindow('sugo.php?id=44',200,300);\"><img src=img/sugo.gif border=0 title='Súgó'></a><textarea name=szervezoinfo cols=50 rows=3 class=urlap>$szervezoinfo</textarea></td></tr>";

//Fizetõs
	$urlap.="\n<tr><td bgcolor=#ECE5C8><div class=kiscim align=right>Fizetõs:</div></td><td bgcolor=#ECE5C8>";
	$urlap.="\n<input type=radio name=fizetos value=i class=urlap";
	if($fizetos=='i') $urlap.=' checked';
	$urlap.="><span class=alap>igen, belépõs/költségtérítéses</span> ";
	$urlap.="\n<input type=radio name=fizetos value=n class=urlap";
	if($fizetos=='n') $urlap.=' checked';
	$urlap.="><span class=alap>nem, ingyenes</span> ";
	$urlap.="\n<input type=radio name=fizetos value=0 class=urlap";
	if(empty($fizetos)) $urlap.=' checked';
	$urlap.="><span class=alap>nincs információ</span> ";
	$urlap.="</td></tr>";

//Helyszín
	$urlap.="\n<tr><td bgcolor=#FFFAE4><div class=kiscim align=right>Esemény helyszíne:<br><a href=\"javascript:OpenNewWindow('sugo.php?id=34',200,300);\"><img src=img/sugo.gif border=0 title='Súgó' align=absmiddle></a></div></td><td bgcolor=#FFFAE4>";

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
		//espker
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
	$urlap.="}\">\n<option value=0>Nincs / nem tudom</option>";	
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
				foreach($varos1T[$id][$meid] as $vnev1) {
					$varosurlap.="\n<option value='".$varosT[$id][$meid][$vnev1]."'";
					if($varosT[$id][$meid][$vnev1]==$varos) $varosurlap.=' selected';
					$varosurlap.=">".$varosT[$id][$meid][$vnev1]."</option>";
				}
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

	$urlap.="</td></tr>";


//határidõ
	$urlap.="\n<tr><td bgcolor=#FFFFFF><div class=kiscim align=right>Határidõ:</div></td><td bgcolor=#FFFFFF><input type=text name=hatarido value=\"$hatarido\" class=urlap size=10 maxlength=10><span class=alap> (Pl. jelentkezési határidõ, jelzéssel megjelenik a naptárban)</span> <a href=\"javascript:OpenNewWindow('sugo.php?id=35',200,300);\"><img src=img/sugo.gif border=0 title='Súgó'></a></td></tr>";

//intro	
	$urlap.="\n<tr><td bgcolor=#efefef><div class=kiscim align=right>Rövid bevezetõ:</div></td><td bgcolor=#efefef><span class=alap>A fõoldalon és a naptár napi nézetben jelenik meg! (lásd: súgó)</span> <a href=\"javascript:OpenNewWindow('sugo.php?id=42',200,400);\"><img src=img/sugo.gif border=0 title='Súgó'></a><br><textarea id=intro name=intro class=urlap cols=75 rows=3>$intro</textarea></td></tr>";

//kategóriák		
	$urlap.="\n<tr><td><div align=right><span class=kiscim>Kategórizálás:</span>";
	if(strstr($u_jogok,'hirek')) $urlap.="<br><span class=kicsi>(rk = rovat kiemelt, <br>ak = aloldal kiemelt, <br>normál)</span>";
	$urlap.="<br><a href=\"javascript:OpenNewWindow('sugo.php?id=36',200,460);\"><img src=img/sugo.gif border=0 title='Súgó'></a></div></td><td><table width=100% cellspacing=0 cellpadding=2><tr>";

	//fõkiemelt
	if(strstr($u_jogok,'hirek')) {
		$urlap.="\n<td width=30% valign=top><span class=kiscim>Fõhír / fõesemény</span><br><span class=alap>fõoldali elsõ hír,<br>vagy kiemelt esemény: </span><input type=checkbox name=fohir value=i class=urlap";
		if($fohir=='i') $urlap.=" checked";
		$urlap.="></td>";
	}
	elseif($szerkesztheti) {
		$urlap.="\n<td width=30% valign=top><input type=hidden name=fohir value=$fohir></td>";
	}
	else {
		$urlap.="\n<td width=30% valign=top>&nbsp;</td>";
	}


	//rovat
	$urlap.="\n<td width=30% valign=top><span class=kiscim>Rovatok</span><span class=alap> (rk,ak,n)</span><br><table cellpadding=0 cellspacing=0>";
	$query="select id,nev from rovatkat where ok='i' and rovat=0 order by sorszam";
	if(!$lekerdez=mysql_db_query($db_name,$query)) echo 'HIBA!<br>'.mysql_error();
	while(list($rid,$rnev)=mysql_fetch_row($lekerdez)) {
		$a++;
		if($a%2==0) $bg='bgcolor=#efefef';
		else $bg='';
		$urlap.="\n<tr><td align=right $bg><span class=kicsi>$rnev </span></td><td $bg>";
		if(strstr($u_jogok,'hirek')) {
			$urlap.="<input type=radio name=k2[] value=rr$rid";		
			if(strstr($kiemelt,"-k2*rr$rid-")) $urlap.=' checked';
			$urlap.=" onClick=\"if(document.urlap.intro.value=='') {alert('Töltsd ki a rövid bevezetõt is!');}\">";
			$urlap.="\n<input type=radio name=k3[] value=rr$rid";
			if(strstr($kiemelt,"-k3*rr$rid-")) $urlap.=' checked';
			$urlap.=" onClick=\"if(document.urlap.intro.value=='') {alert('Töltsd ki a rövid bevezetõt is!');}\">";
		}
		elseif($szerkesztheti) {
			if(strstr($kiemelt,"-k2*rr$rid-")) {
				$urlap.="<input type=hidden name=k2[] value=rr$rid>";
			}
			if(strstr($kiemelt,"-k3*rr$rid-")) {
				$urlap.="<input type=hidden name=k3[] value=rr$rid>";
			}
		}
		$urlap.="<input type=checkbox name=rovat[] value=$rid";
		if(is_array($rovatkatT)) {
			if(in_array($rid,$rovatkatT)) $urlap.=' checked';
		}
		$urlap.="></td></tr>";	
	}
	$a++;
	if($a%2==0) $bg='bgcolor=#efefef';
	else $bg='';
	if(strstr($u_jogok,'hirek')) {
		$urlap.="\n<tr><td align=right $bg><span class=kicsi>nincs </span></td><td $bg>";
		$urlap.="<input type=radio name=k2[] value=''";
		$urlap.="><input type=radio name=k3[] value=''";
		$urlap.="></td></tr>";
	}
	$urlap.='</table></td>';
	
	//fõkat
	$urlap.="\n<td width=35% valign=top><span class=kiscim>Kategóriák</span><br><table cellpadding=0 cellspacing=0>";
	$query="select id,nev from rovatkat where ok='i' and rovat>0 order by rovat,sorszam";
	if(!$lekerdez=mysql_db_query($db_name,$query)) echo 'HIBA!<br>'.mysql_error();
	while(list($fkid,$fknev)=mysql_fetch_row($lekerdez)) {
		if($a%2==0) $bg='bgcolor=#efefef';
		else $bg='';
		$urlap.="<tr><td align=right $bg><span class=kicsi>$fknev </span></td><td $bg>";	
		$urlap.="<input type=checkbox name=rovat[] value=$fkid";
		if(is_array($rovatkatT)) {
			if(in_array($fkid,$rovatkatT)) $urlap.=' checked';
		}
		$urlap.="></td></tr>";
		$a++;
	}
	$urlap.='</table></td>';

	$urlap.='</tr></table></td></tr>';		

//kérdés	
	$urlap.="\n<tr><td bgcolor=#efefef><div class=kiscim align=right>Kérdés (tudtad-e):</div></td><td bgcolor=#efefef><input type=text name=kerdes value=\"$kerdes\" class=urlap size=80 maxlength=250> <a href=\"javascript:OpenNewWindow('sugo.php?id=37',200,400);\"><img src=img/sugo.gif border=0 title='Súgó'></a></td></tr>";

//kulcsszó
	if(strstr($u_jogok,'hirek')) {
		$kulcsszoT=explode('--',substr($kulcsszo,1,strlen($kulcsszo)-2));
		if(is_array($kulcsszoT) and !empty($kulcsszoT[0])) {
		    $feltetel='id='.implode(' or id=',$kulcsszoT);
		    $query="select nev from kulcsszo where $feltetel";
		    $lekerdez=mysql_db_query($db_name,$query);
		    while(list($ksz_nev)=mysql_fetch_row($lekerdez)) {
			$kulcsszokiir.="$ksz_nev, ";
		    }
		}
	
		$urlap.="\n<tr><td><div class=kiscim align=right>Kulcsszó<br>(kapcsolódó hírek): <br><a href=\"javascript:OpenNewWindow('sugo.php?id=38',200,300);\"><img src=img/sugo.gif border=0 title='Súgó'></a></div></td><td><span class=alap>új: </span><input type=text name=ujkulcsszo class=urlap size=40 maxlength=70><br><select name=kulcsszo[] class=urlap multiple size=8><option value='0'";
		$urlap.=">Nincs</option>";

		$query="select id,nev from kulcsszo order by nev";
		if(!$lekerdez=mysql_db_query($db_name,$query)) echo 'HIBA!<br>'.mysql_error();
		while(list($ksz_id,$ksz_nev)=mysql_fetch_row($lekerdez)) {
			$urlap.="<option value='$ksz_id'";
			if(strstr($kulcsszo,"-$ksz_id-")) $urlap.=' selected';
			$urlap.=">$ksz_nev</option>";
		}
		$urlap.="</select> <span class=alap>$kulcsszokiir</span></td></tr>";
	}
	
//Kapcsolódó galéria
	if($szerkesztheti) {
		$urlap.="\n<tr><td bgcolor=#efefef><div class=kiscim align=right>Kapcsolódó galéria:</div><br> <a href=\"javascript:OpenNewWindow('sugo.php?id=39',200,300);\"><img src=img/sugo.gif border=0 title='Súgó'></a></td><td bgcolor=#efefef><select name=galeria[] class=urlap multiple><option value=0";
		if(empty($galeria)) $urlap.=' selected';
		$urlap.=">Nincs</option>";
		$query="select id,cim,datum from galeria where ok='i' order by datum desc";
		if(!$lekerdez=mysql_db_query($db_name,$query)) echo 'HIBA!<br>'.mysql_error();
		while(list($gid,$gcim,$gdatum)=mysql_fetch_row($lekerdez)) {
			$urlap.="<option value='$gid'";
			if(strstr($galeria,"-$gid-")) $urlap.=' selected';
			$urlap.=">$gcim ($gdatum)</option>";
		}
		$urlap.='</select></td></tr>';
	}

//Képek
	$urlap.="\n<tr><td><div class=kiscim align=right>Képek:<br><a href=\"javascript:OpenNewWindow('sugo.php?id=40',200,550);\"><img src=img/sugo.gif border=0 title='Súgó' align=absmiddle></a></div></td><td><span class=alap><font color=red>FIGYELEM!</font><br>Azonos nevû képek felülírják egymást!!! A fájlnévben ne legyen ékezet és szóköz!</span><br><input type=file name=kepT[] class=urlap size=20> <span class=alap>Képfelirat: </span><input type=text name=kepfeliratT[] size=40 maxlength=100 class=urlap><br><input type=file name=kepT[] class=urlap size=20> <span class=alap>Képfelirat: </span><input type=text name=kepfeliratT[] size=40 maxlength=100 class=urlap><br><input type=file name=kepT[] class=urlap size=20> <span class=alap>Képfelirat: </span><input type=text name=kepfeliratT[] size=40 maxlength=100 class=urlap>";
	if($hid>0) {
		//Meglévõ képek listája
		$query="select fajlnev,felirat,sorszam,kiemelt from kepek where kat='hirek' and kid='$hid' order by sorszam";
		$lekerdez=mysql_db_query($db_name,$query);
		$konyvtar="kepek/hirek/$hid";
		$urlap.="\n<table width=100% cellpadding=0 cellspacing=0><tr>";
		$a=0;
		while(list($fajlnev,$felirat,$sorszam,$kiemelt)=mysql_fetch_row($lekerdez)) {			
			if($a%3==0 and $a>0) $urlap.="</tr><tr>";
			$a++;
			if($kiemelt=='i') $fokepchecked=' checked';
			else $fokepchecked='';
			$info=getimagesize("$konyvtar/$fajlnev");
			$w=$info[0];
			$h=$info[1];
			$urlap.="\n<td valign=bottom><a href=javascript:OpenNewWindow('view.php?kep=$konyvtar/$fajlnev',$w,$h);><img src=$konyvtar/kicsi/$fajlnev title='$felirat' border=0></a><br><input type=text name=kepsorszamT[$fajlnev] value='$sorszam' maxlength=2 size=1 class=urlap><span class=alap> -fõoldal:</span><input type=checkbox name=fooldalkepT[$fajlnev] $fokepchecked value='i' class=urlap><span class=alap> -töröl:</span><input type=checkbox name=delkepT[] value='$fajlnev' class=urlap><br><input type=text name=kepfeliratmodT[$fajlnev] value='$felirat' maxlength=250 size=20 class=urlap></td>";
		}
		$urlap.='</tr></table>';
	}
	$urlap.='</td></tr>';

//Fájlok
	$urlap.="\n<tr><td bgcolor=#efefef valign=top><div class=kiscim align=right>Letölthetõ fájl(ok):</td><td valign=top bgcolor='#efefef'>";
	$urlap.="\n<span class=alap>Kapcsolódó dokumentum (pl. jelentkezési lap, stb.), ha van ilyen:</span><br>";
	$urlap.="\n<span class=alap>Új fájl: </span><input type=file size=60 name=fajl class=urlap><br>";
	//Könyvtár tartalmát beolvassa
	if($hid>0) {
		$konyvtar="fajlok/hirek/$hid";
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
					$urlap.="<br><a href=\"$konyvtar/$filekiir\" class=\"link\" target=\"_blank\"><b>$file</b></a><span class=kicsi> ($meret) </span><input type=checkbox class=urlap name=delfajl[] value='$file'><span class=alap>Töröl</span>";
				}
			}
			closedir($handle);
		}
	}

//Megjelenhet (jogosultság!)
	if(strstr($u_jogok,'hirek')) {
		$urlap.="\n<tr><td><div class=kiscim align=right>Megjelenhet:</div></td><td>";
		$urlap.="\n<input type=checkbox name=ok value=i class=urlap";
		if($ok!='n' and $ok!='f') $urlap.=' checked';
		$urlap.="><span class=alap>igen</span> ";
		$urlap.="</td></tr>";
	}

//Szöveg
	$urlap.="<tr><td bgcolor=#EFEFEF valign=top><div class=kiscim align=right>Hír részletes szövege:</div></td><td bgcolor=#EFEFEF valign=top><span class=alap><font color=red><b>FONTOS!</b></font> A szöveghez MINDIG legyen stílus rendelve! <br>(Elsõ feltöltés után a szerkesztõablakkal formázható.)</span><br><textarea name=szoveg class=urlap cols=90 rows=40>$szoveg</textarea>";

	$urlap.="\n</td></tr>";
	
//Log
	if($hid>0 and strstr($u_jogok,'hirek')) {
		$urlap.="\n<tr><td valign=top><div class=kiscim align=right>történet:</div></td><td valign=top><textarea cols=50 rows=6 disabled>Számláló: $szamlalo\n$log</textarea></td></tr>";
	}

	$urlap.='</table>';

	$urlap.="\n<br><input type=submit value=Mehet class=urlap>";
	if($hid>0) {
		$urlap.="<input type=checkbox name=modosit value=i class=urlap checked><span class=alap> és újra módosít</span>";
		$urlap.=" &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <a href=?m_id=$m_id&m_op=addmegse&hid=$hid&kod=$kod$linkveg class=link><font color=red>Kilépés módosítás nélkül</font></a>";
	}
	else $urlap.="<input type=hidden name=modosit value=i>";
	$urlap.="\n</form>";

	$adatT[2]='<span class=alcim>Hír feltöltése</span><br><br>'.$urlap;
	$tipus='doboz';
	$tartalom.=formazo($adatT,$tipus);	
	
	$kod=$tartalom;

	return $kod;
}

function hirekadding() {
	global $db_name,$u_login,$u_jogok;

	$ip=$_SERVER['REMOTE_ADDR'];
    $host = gethostbyaddr($ip);

	$hiba=false;
	$hid=$_POST['hid'];
	if(!is_numeric($hid) and !empty($hid)) {
		echo 'HIBA! - nem jó a hid';
		exit();
	}
	if($hid>0) {
		//Ha módosítás történt
		$lekerdez=mysql_db_query($db_name,"select megnyitva,feltette,megbizhato,ok from hirek where id='$hid'");
		list($megnyitva,$feltolto,$megbizhato,$ok)=mysql_fetch_row($lekerdez);
		if(strstr($megnyitva,$u_login)) { //és õ nyitotta meg utoljára,
			mysql_db_query($db_name,"update hirek set megnyitva='' where id='$hid'"); //akkor töröljük a bejegyzést
		}

		$szerkesztheti=false;
		if(strstr($u_jogok,'hirek')) {
			$szerkesztheti=true;
			$ok=$_POST['ok'];
			if($ok!='i') $ok='n';
			$feltolto=$_POST['feltolto'];
			if(empty($feltolto)) $feltolto=$u_login;
			$megbizhato=$_POST['megbizhato'];
			if($megbizhato!='i') $megbizhato='n';
		}
		elseif($u_login==$feltolto and $megbizhato=='i') {
			$szerkesztheti=true;
		}
		elseif($ok=='f') {
			$szerkesztheti=true;
		}
	}
	else {
		$szerkesztheti=true;
		$ok='f';

		if(strstr($u_jogok,'hirek')) {
			//HA admin tölt fel új hírt, akkor az engedélyezés adatait is rögzítjük!
			$ok=$_POST['ok'];
			if($ok!='i') $ok='n';
			$feltolto=$_POST['feltolto'];
			if(empty($feltolto)) $feltolto=$u_login;
			$megbizhato=$_POST['megbizhato'];
			if($megbizhato!='i') $megbizhato='n';
		}
		else {
			//Ha sima újfeltöltés, akkor a saját nevéhez rendeljük
			//és megkeressük, hogy más híreknél hogy lett beállítva a megbízhatósága
			$feltolto=$u_login;
			$query="select megbizhato from hirek where feltette='$u_login' limit 0,1";
			list($megbizhato)=mysql_fetch_row(mysql_db_query($db_name,$query));
			if($megbizhato!='i') $megbizhato='n';
		}

	}

	if(!$szerkesztheti) {
		echo 'HIBA! Te nem szerkesztheted!';
		exit();
	}

	$modosit=$_POST['modosit'];
	$adminmegj=$_POST['adminmegj'];
	$kontakt=$_POST['kontakt'];
	$kontaktmail=$_POST['kontaktmail'];	
	$cim=$_POST['cim'];
	$datum=$_POST['datum'];
	$hirlevel=$_POST['hirlevel'];
	$szervezotipus=$_POST['szervezotipus'];
	$szervezonev=$_POST['szervezonev'];
	$szervezoinfo=$_POST['szervezoinfo'];
	$fizetos=$_POST['fizetos'];
	$intro=$_POST['intro'];
	$aktualis=$_POST['aktualis'];
	if(strstr($aktualis,'=>')) { //tól-ig lett megadva
		$aktualisT=explode('=>',$aktualis);
		$tol=$aktualisT[0];
		$ig=$aktualisT[1];
		$tolev=substr($tol,0,4);
		$igev=substr($ig,0,4);
		$tolho=substr($tol,5,2);
		$igho=substr($ig,5,2);
		$tolnap=substr($tol,8,2);
		$ignap=substr($ig,8,2);
		$tolido=mktime(0,0,0,$tolho,$tolnap,$tolev);
		$igido=mktime(0,0,0,$igho,$ignap,$igev);
		$egynap=86400;
		for($i=$tolido;$i<=$igido;$i=$i+$egynap) {
			$ujaktualisT[]=date('Y-m-d',$i);
		}
		if(is_array($ujaktualisT)) $aktualis=implode('+',$ujaktualisT);
	}
	$egyhazmegye=$_POST['egyhazmegye'];
	$espkerT=$_POST['espkerT'];
	if($egyhazmegye>0) {
		$espker=$espkerT[$egyhazmegye];
	}
	$orszag=$_POST['orszag'];
	$megyeT=$_POST['megyeT'];
	if($orszag>0) {
		$megye=$megyeT[$orszag];
	}
	$varosT=$_POST['varosT'];
	if($orszag>0 and $megye>0) {
		$varos=$varosT[$orszag][$megye];
	}
	elseif($orszag>0) {
		$varos=$varosT[$orszag][0];
	}
	$hatarido=$_POST['hatarido'];
	$fohir=$_POST['fohir'];
	if($fohir!='i') $fohir='n';
		
	$rovatT=$_POST['rovat'];
	if(is_array($rovatT)) $rovatkat='-'.implode('--',$rovatT).'-';
	
	$k2T=$_POST['k2'];
	if(is_array($k2T)) $kiemeltT[]='-k2*'.implode('--k2*',$k2T).'-';
	$k3T=$_POST['k3'];
	if(is_array($k3T)) $kiemeltT[]='-k3*'.implode('--k3*',$k3T).'-';

	if(is_array($kiemeltT)) $kiemelt=implode('',$kiemeltT);


	$kerdes=$_POST['kerdes'];	

	$szoveg=$_POST['szoveg'];
	$szoveg=str_replace('&eacute;','é',$szoveg);
	$szoveg=str_replace('&aacute;','á',$szoveg);
	$szoveg=str_replace('&Eacute;','É',$szoveg);
	$szoveg=str_replace('&Aacute;','Á',$szoveg);
	$szoveg=str_replace('&ouml;','ö',$szoveg);
	$szoveg=str_replace('&Ouml;','Ö',$szoveg);
	$szoveg=str_replace('&uuml;','ü',$szoveg);
	$szoveg=str_replace('&Uuml;','Ü',$szoveg);
	$szoveg=str_replace("'","\'",$szoveg);

	$elsofeltoltes=$_POST['elsofeltoltes'];
	if($elsofeltoltes=='i') $szoveg='<p class=alap>'.nl2br($szoveg);


	$galeriaT=$_POST['galeria'];
	if(is_array($galeriaT)) $galeria='-'.implode('--',$galeriaT).'-';

///////////////////////////
	$kep=$_FILES['kep']['tmp_name'];
	$kepnev=$_FILES['kep']['name'];
	$kicsinyit=$_POST['kicsinyit'];
	if(empty($kicsinyit)) $kicsinyit=120;
	$align=$_POST['align'];
	if($align!='0') $align="align=$align";

	$fajl=$_FILES['fajl']['tmp_name'];
	$fajlnev=$_FILES['fajl']['name'];
	$delfajl=$_POST['delfajl'];

	if(is_array($delfajl)) {
		foreach($delfajl as $ertek) {
			unlink("fajlok/hirek/$hid/$ertek");
		}
	}

//Kulcsszókezelés
	$kulcsszoT=$_POST['kulcsszo'];
	$ujkulcsszo=$_POST['ujkulcsszo'];

	if(is_array($kulcsszoT)) {
		$kulcsszo='-'.implode('--',$kulcsszoT).'-';
	}

	if(!empty($ujkulcsszo)) {
		$query="select id from kulcsszo where nev='$ujkulcsszo'";
		$lekerdez=mysql_db_query($db_name,$query);
		list($ksz_id)=mysql_fetch_row($lekerdez);
		if($ksz_id>0) $kulcsszo.="-$ksz_id-"; //ha olyat írtak be újnak, ami már volt
		else {
			mysql_db_query($db_name,"insert kulcsszo set nev='$ujkulcsszo'");
			$ksz_id=mysql_insert_id();
			$kulcsszo.="-$ksz_id-";
		}
	}

	if(empty($cim)) {
		$hiba=true;
		$hibauzenet.='<br>Nem lett kitöltve a cím mezõ!';
	}
	if(empty($intro) and empty($szoveg)) {
		$hiba=true;
		$hibauzenet.='<br>Nem lett kitöltve sem bevezetõ, sem szövegmezõ!';
	}

	if($hiba) {
		$txt.="<span class=hiba>HIBA a hírek feltöltésénél!</span><br>";
		$txt.='<span class=alap>'.$hibauzenet.'</span>';
		$txt.="<br><br><a href=javascript:history.go(-1); class=link>Vissza</a>";
	
		$adatT[2]='<span class=alcim>Hírek admin</span><br><br>'.$txt;
		$tipus='doboz';
		$kod.=formazo($adatT,$tipus);	
		
		echo $txt;
	}
	else {
		$most=date('Y-m-d H:i:s');
		if($hid>0) {
			$uj=false;
			$parameter1='update';
			list($log)=mysql_fetch_row(mysql_db_query($db_name,"select log from hirek where id='$hid'"));
			$ujlog=$log."\nMod: $u_login ($most)";
			$parameter2=", modositotta='$u_login', moddatum='$most', log='$ujlog' where id='$hid'";
		}
		else {
			$uj=true;
			$parameter1='insert';
			$parameter2=", fdatum='$most', log='Add: $u_login ($most)'";
		}

		$query="$parameter1 hirek set kontakt='$kontakt', kontaktmail='$kontaktmail', cim='$cim', intro='$intro', szoveg='$szoveg', kerdes='$kerdes', orszag='$orszag', megye='$megye', varos='$varos', egyhazmegye='$egyhazmegye', espereskerulet='$espker', datum='$datum', aktualis='$aktualis', tol='$tol', hatarido='$hatarido', szervezotipus='$szervezotipus', szervezonev='$szervezonev', szervezoinfo='$szervezoinfo', fizetos='$fizetos', fohir='$fohir', rovatkat='$rovatkat', kulcsszo='$kulcsszo', galeria='$galeria', kiemelt='$kiemelt', ok='$ok', hirlevel='$hirlevel', adminmegj='$adminmegj', feltette='$feltolto', megbizhato='$megbizhato' $parameter2";
		if(!mysql_db_query($db_name,$query)) echo 'HIBA!<br>'.mysql_error();
		if($uj) $hid=mysql_insert_id();

		if(!empty($fajl)) {
		$konyvtar="fajlok/hirek";
		//Könyvtár ellenõrzése
		if(!is_dir("$konyvtar/$hid")) {
			//létre kell hozni
			if(!mkdir("$konyvtar/$hid",0775)) {
				echo '<p class=hiba>HIBA a könyvtár létrehozásánál!</p>';					
			}
		}
		//Másolás
		if(!copy($fajl,"$konyvtar/$hid/$fajlnev")) echo '<p>HIBA a másolásnál!</p>';
		unlink($fajl);
	}

	//képkezelés
		$konyvtar="kepek/hirek/$hid";		

		$delkepT=$_POST['delkepT'];
		if(is_array($delkepT)) {		
			foreach($delkepT as $ertek) {
				@unlink("$konyvtar/$ertek");
				@unlink("$konyvtar/kicsi/$ertek");
				if(!mysql_db_query($db_name,"delete from kepek where kat='hirek' and kid='$hid' and fajlnev='$ertek'")) echo 'HIBA!<br>'.mysql_error();
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
						if(!mkdir("$konyvtar/fooldal",0775)) {
							echo '<p class=hiba>HIBA a könyvtár létrehozásánál!</p>';					
						}
					}

					$kimenet="$konyvtar/$kepnevT[$id]";
					$kimenet1="$konyvtar/kicsi/$kepnevT[$id]";
					$kimenet2="$konyvtar/fooldal/$kepnevT[$id]";
	
					if ( !copy($kep, "$kimenet") )
						print("HIBA a másolásnál ($kimenet)!<br>\n");
					else  {
						//Bejegyzés az adatbázisba
						if(!mysql_db_query($db_name,"insert kepek set kat='hirek', kid='$hid', fajlnev='$kepnevT[$id]', felirat='$kepfeliratT[$id]'")) echo 'HIBA!<br>'.mysql_error();
					}
					
					unlink($kep);
	
					$info=getimagesize($kimenet);
					$w=$info[0];
					$h=$info[1];
      
					if($w>800 or $h>600) kicsinyites($kimenet,$kimenet,800);
			  		kicsinyites($kimenet,$kimenet1,120);
					kicsinyites($kimenet,$kimenet2,90);
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
				if(!mysql_db_query($db_name,"update kepek set felirat='$kepfeliratmodT[$melyikkep]', sorszam='$ertek', kiemelt='$kiemelt' where kat='hirek' and kid='$hid' and fajlnev='$melyikkep'")) echo 'HIBA!<br>'.mysql_error();
			}
		}	

		if($modosit=='i') $kod=$hid;
		else $kod=0;
	}

	return $kod;
}

?>
