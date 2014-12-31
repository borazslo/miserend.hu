<?php

function user_index() {
	global $linkveg,$m_id;

	$menu.="<a href=?m_id=$m_id&m_op=add$linkveg class=kismenulink>Új felhasználó - hozzáadás</a><br>";
	$menu.="<a href=?m_id=$m_id&m_op=mod$linkveg class=kismenulink>Meglévő módosítása, törlése</a><br>";

	$adatT[2]="<span class=alcim>Felhasználók szerkesztése</span><br><br>".$menu;
	$tipus='doboz';
	$tartalom.=formazo($adatT,$tipus);	
	
	$kod=$tartalom;

	return $kod;
}

function user_add($uid) {
	global $sessid,$m_id,$db_name,$u_id;

	if($uid>0) {
		$query="select login,jogok,ok,regdatum,lastlogin,lastactive,email,becenev,nev,kontakt,magamrol,msn,skype,vallas,orszag,varos,csaladiallapot,szuldatum,nevnap,foglalkozas from user where uid='$uid'";
		list($ulogin,$ujogok,$ok,$regdatum,$lastlogin,$lastactive,$email,$becenev,$nev,$kontakt,$magamrol,$msn,$skype,$vallas,$orszag,$varos,$csaladiallapot,$szuldatum,$nevnap,$foglalkozas)=mysql_fetch_row(mysql_db_query($db_name,$query));
	}

	$urlap="\n<form method=post>";
	$urlap.="\n<input type=hidden name=m_id value=$m_id><input type=hidden name=sessid value=$sessid>";
	$urlap.="\n<input type=hidden name=m_op value=adding><input type=hidden name=uid value=$uid>";

//Bejelentkezési név	
	$urlap.="\n<span class=kiscim>Bejelentkezési név: </span><br><input type=text name=ulogin value='$ulogin' class=urlap size=20 maxlength=20";
	if(!empty($ulogin)) $urlap.=' readonly';
	$urlap.=">";

//regdátum, lastlogin
	$urlap.="<br><br><span class=kiscim>Belépések:</span><br><textarea readonly cols=40 rows=3 class=urlap>Regisztráció: $regdatum\nUtolsó belépés: $lastlogin\nUtolsó aktivitás: $lastactive</textarea>";

//Becenév
	$urlap.="\n<br><br><span class=kiscim>Becenév, megszólítás: </span><br><input type=text name=becenev value='$becenev' class=urlap size=20 maxlength=50>";

//Jelszó
	$urlap.="\n<br><br><span class=kiscim>(új) jelszó:</span><br><input type=password name=ujelszo class=urlap maxlength=40 size=20> <span class=alap>mégegyszer:</span> <input type=password name=ujelszo1 class=urlap maxlength=40 size=20>";

//Engedélyezés
	$urlap.="\n<br><br><span class=kiscim>Engedélyezés/kizárás: </span><br><input type=checkbox name=ok value='i' class=urlap";
	if($ok!='n') $urlap.=' checked';
	$urlap.='><span class=alap> aktív</span>';

//Jogosultság	
	$urlap.="\n<br><br><span class=kiscim>Jogosultságok: </span>";
	$query="select jogkod from modulok where jogkod!=''";
	$lekerdez=mysql_db_query($db_name,$query);
	while(list($jogkod)=mysql_fetch_row($lekerdez)) {
		$urlap.="\n<br><input type=checkbox name=jogok[] value='$jogkod' class=urlap";
		if(strstr($ujogok,$jogkod)) $urlap.=' checked';
		$urlap.="> <span class=alap>$jogkod</span>";
	}

//Email
	$urlap.="\n<br><br><span class=kiscim>Email: </span><br><input type=text name=email class=urlap maxlength=100 size=40 value='$email'>";

//Név
	$urlap.="\n<br><br><span class=kiscim>Név: </span><br><input type=text name=nev class=urlap maxlength=100 size=40 value='$nev'>";	

//Elérhetőség	
	$urlap.="\n<br><br><span class=kiscim>Elérhetőségek:</span>";
	$urlap.="\n<br><span class=alap>Ország: </span><input type=text name=orszag class=urlap size=40 maxlength=50 value='$orszag'>";
	$urlap.="\n<br><span class=alap>Település: </span><input type=text name=varos class=urlap size=40 maxlength=50 value='$varos'>";
	$urlap.="\n<br><span class=alap>Kontakt: </span><br><textarea name=kontakt class=urlap cols=60 rows=4>$kontakt</textarea>";
	$urlap.="\n<br><span class=alap>Skype: </span><input type=text name=skype class=urlap size=40 maxlength=50 value='$skype'>";
	$urlap.="\n<br><span class=alap>MSN messenger: </span><input type=text name=msn class=urlap size=40 maxlength=50 value='$msn'>";

//Egyéb adatok
	$urlap.="\n<br><br><span class=kiscim>Bemutatkozás: </span><br><textarea name=magamrol class=urlap cols=60 rows=8>$magamrol</textarea>";
	$urlap.="\n<br><span class=alap>Foglalkozás: </span><input type=text name=foglalkozas class=urlap size=40 maxlength=50 value='$foglalkozas'>";
	$urlap.="\n<br><br><span class=kiscim>Vallás: </span><br><input type=text readonly class=urlap maxlength=100 size=40 value='$vallas'>";	
	$urlap.="\n<br><br><span class=kiscim>Családi állapot: </span><br><input type=text readonly class=urlap maxlength=100 size=40 value='$csaladiallapot'>";	
	$urlap.="\n<br><br><span class=kiscim>Születésnap: </span><br><input type=text readonly class=urlap maxlength=100 size=40 value='$szuldatum'>";	
	if($szuldatum>0) {
		$ev=date('Y');
		$szulev=substr($szuldatum,0,4);
		$kor=$ev-$szulev;
		$urlap.="<span class=alap> (idén $kor éves)</span>";
	}
	$urlap.="\n<br><br><span class=kiscim>Névnap: </span><br><input type=text readonly class=urlap maxlength=100 size=40 value='$nevnap'>";	
	

	$urlap.='<br><br><input type=submit value=Mehet class=urlap></form>';

	$adatT[2]="<span class=alcim>Felhasználók szerkesztése</span><br><br>".$urlap;
	$tipus='doboz';
	$tartalom.=formazo($adatT,$tipus);	
	
	$kod=$tartalom;

	return $kod;
}

function user_adding() {
	global $_POST,$_FILES,$u_login,$db_name;

	$uid=$_POST['uid'];
	$nev=$_POST['nev'];
	$becenev=$_POST['becenev'];
	$email=$_POST['email'];
	$orszag=$_POST['orszag'];
	$varos=$_POST['varos'];
	$magamrol=$_POST['magamrol'];
	$foglalkozas=$_POST['foglalkozas'];
	$skype=$_POST['skype'];
	$msn=$_POST['msn'];
	$login=$_POST['ulogin'];
	$ok=$_POST['ok'];
	if($ok!='i') $ok='n';
	$jelszo=$_POST['ujelszo'];
	$jelszo1=$_POST['ujelszo1'];
	$jogokT=$_POST['jogok'];
	if(is_array($jogokT)) $jogok=implode('-',$jogokT);
	$kontakt=$_POST['kontakt'];
	$most=date('Y-m-d H:i:s');

	if(!empty($jelszo)) {
		if($jelszo=$jelszo1) {
			$jelszo=base64_encode($jelszo);
			$jelszomod=", jelszo='$jelszo'";
		}
		else {
			$hiba=true;
			$hibauzenet='HIBA! A beírt két jelszó nem egyezik!';
		}
	}

	if($uid>0) {
		$uj=false;
		$parameter1='update';
		$parameter2="$jelszomod where uid='$uid'";
	}
	else {
		$uj=true;
		$parameter1='insert';
		$parameter2="$jelszomod ,login='$login', letrehozta='$u_login', regdatum='$most'";
	}

	if(!$hiba) {
		$query="$parameter1 user set becenev='$becenev', nev='$nev', email='$email', kontakt='$kontakt', magamrol='$magamrol', orszag='$orszag', varos='$varos', msn='$msn', skype='$skype', foglalkozas='$foglalkozas', ok='$ok', jogok='$jogok' $parameter2";
		if(!mysql_db_query($db_name,$query)) echo "HIBA!<br>$query<br>".mysql_error();
		if($uj) $uid=mysql_insert_id();

		$kod=user_add($uid);
	}
	else {
		$adatT[2]="<span class=alcim>Felhasználók szerkesztése</span><br><br><span class=hiba>$hibauzenet</span><br><br><a href=javascript:history.go(-1); class=link>Vissza</a>";
		$tipus='doboz';
		$kod.=formazo($adatT,$tipus);	
	}

	return $kod;
}

function user_mod() {
	global $db_name,$linkveg,$m_id,$sid,$_POST;

	$kulcsszo=$_POST['kulcsszo'];
	$sort=$_POST['sort'];
	if(empty($sort)) $sort='nev';
	$adminok=$_POST['adminok'];
	$limit=$_POST['limit'];
	if(empty($limit)) $limit=50;

	$kiir.="\n<span class=kiscim>Keresés:</span><br><br>";
	$kiir.="\n<form method=post><input type=hidden name=sid value=$sid>";
	$kiir.="\n<input type=hidden name=m_id value=$m_id><input type=hidden name=m_op value=mod>";
	$kiir.="\n<input type=text name=kulcsszo value='$kulcsszo' class=urlap size=20>";
	$kiir.="\n<select name=adminok class=urlap><option value=0>Mindenki</option>";
	$query="select jogkod from modulok where jogkod!=''";
	$lekerdez=mysql_db_query($db_name,$query);
	while(list($jogkod)=mysql_fetch_row($lekerdez)) {
		$kiir.="\n<option value='$jogkod'";
		if($adminok==$jogkod) $kiir.=' selected';
		$kiir.=">$jogkod</option>s";
	}
	$kiir.="\n</select>";
	
	$kiir.="\n<br><span class=alap>rendezés: </span><select name=sort class=urlap> ";
	$sortT['felhasználó név']='login';
	$sortT['becenév']='becenev';
	$sortT['név']='nev';
	$sortT['utolsó belépés']='lastlogin desc';	
	$sortT['utolsó aktivitás']='lastactive desc';	
	$sortT['regisztráció']='regdatum desc';	


	
	foreach($sortT as $kulcs=>$ertek) {
		$kiir.="<option value='$ertek'";
		if($ertek==$sort) $kiir.=' selected';
		$kiir.=">$kulcs</option>";
	}
	$kiir.="\n</select><input type=submit value=Lista class=urlap></form>";

	$kiir.="<span class=kiscim>Válassz az alábbi felhasználók közül:</span><br><br>";

	if(!empty($kulcsszo)) {
		$feltetelT[]="login like '%$kulcsszo%'";
		$feltetelT[]="nev like '%$kulcsszo%'";
		$feltetelT[]="email like '%$kulcsszo%'";
	}
	if(!empty($adminok)) {
		$feltetelT[]="jogok like '%$adminok%'";
	}
	if(is_array($feltetelT)) $feltetel="where (".implode(' or ',$feltetelT).')';

	$query="select * from user $feltetel order by $sort";
	$lekerdez=mysql_db_query($db_name,$query);
	while($user=mysql_fetch_assoc($lekerdez)) {
		$kiir.="\n<a href=?m_id=$m_id&m_op=add&uid=".$user['uid']."$linkveg class=link>";
		$kiir .= "<b>- ".$user['login']."</b> (".$user['nev'].")</a> - ";
		$kiir .= "<span class=\"alap\"><a href=\"mailto:".$user['email']."\">".$user['email']."</a></span> - ";
		$kiir .= "<span class=\"alap\">".$user['lastlogin']."</span> - ";
		$kiir .= "<a href=?m_id=$m_id&m_op=del&uid=".$user['uid']."$linkveg class=link><img src=img/del.jpg border=0 alt=Töröl align=absmiddle> töröl</a><br>";
	}

	$adatT[2]="<span class=alcim>Felhasználók szerkesztése - módosítás</span><br><br>".$kiir;
	$tipus='doboz';
	$tartalom.=formazo($adatT,$tipus);	
	
	$kod=$tartalom;

	return $kod;
}

function user_del() {
	global $_GET,$db_name,$linkveg,$m_id;

	$uid=$_GET['uid'];

	$kiir="\n<span class=kiscim>Biztosan törölni akarod a következő felhasználót?</span>";
	
	$query="select login,nev from user where uid='$uid'";
	list($ulogin,$unev)=mysql_fetch_row(mysql_db_query($db_name,$query));

	$kiir.="\n<br><br><span class=alap>$ulogin ($unev)</span>";

	$kiir.="<br><br><a href=?m_id=$m_id&m_op=delete&uid=$uid$linkveg class=link>Igen</a> - <a href=?m_id=$m_id&m_op=mod$linkveg class=link>NEM</a>";

	$adatT[2]="<span class=alcim>Felhasználók szerkesztése - törlés</span><br><br>".$kiir;
	$tipus='doboz';
	$tartalom.=formazo($adatT,$tipus);	
	
	$kod=$tartalom;

	return $kod;
}

function user_delete() {
	global $_GET,$db_name,$u_id;

	$uid=$_GET['uid'];
	if($uid!=$u_id) {
		$query="delete from user where uid='$uid'";
		mysql_db_query($db_name,$query);
	}

	$kod=user_mod();

	return $kod;
}


if(strstr($u_jogok,'user')) {
	//Csak, ha van user jogosultsága!

switch($m_op) {
    case 'index':
        $tartalom=user_index();
        break;

	case 'add':
		$uid=$_GET['uid'];
        $tartalom=user_add($uid);
        break;

    case 'mod':
        $tartalom=user_mod();
        break;

    case 'adding':
        $tartalom=user_adding();
        break;

    case 'del':
        $tartalom=user_del();
        break;

	case 'delete':
        $tartalom=user_delete();
        break;
}
}
else {
	$tartalom="\n<span class=hiba>HIBA! Nincs hozzá jogosultságod!</span>";
}
?>
