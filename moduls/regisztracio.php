<?php

function user_list() {
	global $db_name,$linkveg,$m_id,$sid,$_POST;

	$kulcsszo=$_REQUEST['kulcsszo'];
	$sort=$_REQUEST['sort'];
	if(empty($sort)) $sort='nev';
	$adminok=$_REQUEST['adminok'];
	$limit=$_REQUEST['limit'];
	if(empty($limit)) $limit=50;

	$kiir.="\n<span class=kiscim>Keresés:</span><br><br>";
	$kiir.="\n<form method=post><input type=hidden name=sid value=$sid>";
	$kiir.="\n<input type=hidden name=m_id value=$m_id><input type=hidden name=m_op value=list>";
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
		$kiir.="\n<a href=?m_id=$m_id&m_op=edit&uid=".$user['uid']."$linkveg class=link>";
		$kiir .= "<b>- ".$user['login']."</b> (".$user['nev'].")</a> - ";
		$kiir .= "<span class=\"alap\"><a href=\"mailto:".$user['email']."\">".$user['email']."</a></span> - ";
		if(preg_match('/^(lastlogin|lastactive|regdatum)/i',$sort,$match)) 
			$field = $match[1];
		else $field = 'lastlogin';
		$kiir .= "<span class=\"alap\">".$user[$field]."</span> - ";
		$kiir .= "<a href=?m_id=$m_id&m_op=del&uid=".$user['uid']."$linkveg class=link><img src=img/del.jpg border=0 alt=Töröl align=absmiddle> töröl</a><br>";
	}

	$adatT[2]="<span class=alcim>Felhasználók szerkesztése - módosítás</span><br><br>".$kiir;
	$tipus='doboz';
	$tartalom.=formazo($adatT,$tipus);	
	
	$kod=$tartalom;

	return $kod;
}


function user_edit($uid = false) {
	global$user;

	$edituser = new User($uid);

	if($edituser->uid == 0 AND $user->uid == 0) {
		$vars['title'] = "Regisztráció";
	} elseif($edituser->uid == 0 AND $user->uid > 0 ) {
		if(!$user->checkRole('user')) {
			addMessage("Nincs megfelelő jogosultságod!","danger");
			return $vars;
		}
		$vars['title'] = "Új felhasználó";
	} else {
		$vars['title'] = "Adatok módosítása";
		if(!$user->checkRole('user') AND $user->uid != $edituser->uid) {
			addMessage("Nincs megfelelő jogosultságod!","danger");
			return $vars;
		}
	}

	$vars['form'] = $edituser->form();
	$vars['template'] = 'user_form';
	
	return $vars;
}



function user_jelszo() {
	global $db_name,$m_id;
	
	$cim='<span class=alcim>Jelszó emlékeztető</span><br><br>';
	$szoveg='<span class=alap>Az alábbi két adat közül legalább az egyik kitöltése alapján a rendszer megpróbál azonosítani és elküldi a megadott (regisztrált!) email címre egy ÚJ jelszót.</span><br><br>';
	$szoveg.="\n<form method=post><input type=hidden name=m_op value=jelszokuld><input type=hidden name=m_id value=$m_id><span class=alap>Felhasználónév: </span> <input type=text name=lnev size=18 class=urlap><br><span class=alap>Emailcím: </span> <input type=text name=mail size=25 class=urlap><br><br><input type=submit value='Kérem a jelszót'></form>";

	$adatT[2]=$cim.$szoveg;
	$tipus='doboz';
	$kod.=formazo($adatT,$tipus);

	return $kod;
}


function user_jelszokuld() {

	$lnev=$_POST['lnev'];
	$mail=$_POST['mail'];

	$cim='<span class=alcim>Jelszó emlékeztető</span><br><br>';

	$user = new User($mail);
	
	if(!empty($lnev)) $userByNev = new User($lnev);
	if(!empty($mail)) $userByMail = new User($mail);

	if(!empty($lnev) AND !empty($mail) AND $userByMail->uid != $userByNev) {
		addMessage('Ez az email cím és felhasználó név nem találkozik sehogy sem.','danger');
		$szoveg='<span class=alap>A megadott adatok alapján nem találtunk felhasználót.</span><br><br><a href=javascript:history.go(-1); class=link>Vissza</a>';
		return false;
	} 

	if($userByNev->uid > 0) $user = $userByNev;
	elseif($userByMail > 0) $user = $userByMail;
	else {
		$szoveg='<span class=alap>A megadott adatok alapján nem találtunk felhasználót.</span><br><br><a href=javascript:history.go(-1); class=link>Vissza</a>';
		return false;	
	}

	$mail = new Mail();
	$mail->subject = "Jelszó emlékeztető - Virtuális Plébánia Portál";

	$newpassword = $user->generatePassword();
	$user->newPassword($newpassword);
	
	$mail->content="Kedves ".$user->username."!<br/><br/>";
	$mail->content.="\n\nKérésedre küldjük a bejelentkezéshez szükséges újjelszót:";
	$mail->content.="\n".$newpassword."<br/><br>";
	$mail->content.="Kérjük mihamarabb változtasd meg a jelszót.<br/><br/>";
	$mail->content.="\n\nVPP \nhttp://www.plebania.net";

	$mail->to = $user->email;
	$mail->send();
			
	$szoveg="<span class=alap>Az új jelszót elküldtük a regisztrált emailcímre. Kérjük lépjen be, és mihamarabb módosítsa.</span>";
		
	

	$adatT[2]=$cim.$szoveg;
	$tipus='doboz';
	$kod.=formazo($adatT,$tipus);


	return $kod;
}

function user_del($uid) {
	global $m_id;

	$user2delete = new User($uid);
	if($user2delete->uid == 0) {
		$kiir="\n<span class=kiscim>Nincs ilyen felhasználó!</span>";
	} else {
		$kiir="\n<span class=kiscim>Biztosan törölni akarod a következő felhasználót?</span>";
		$kiir.="\n<br><br><span class=alap>".$user2delete->username." (".$user2delete->nev.")</span>";
		$kiir.="<br><br><a href=?m_id=$m_id&m_op=delete&uid=$uid class=link>Igen</a> - <a href=?m_id=$m_id&m_op=mod class=link>NEM</a>";
	}
	$adatT[2]="<span class=alcim>Felhasználók szerkesztése - törlés</span><br><br>".$kiir;
	$tipus='doboz';
	$tartalom.=formazo($adatT,$tipus);	
	
	$kod=$tartalom;

	return $kod;
}


function user_delete() {
	global $user;

	$delete = new User($_GET['uid']);
	$delete->delete();
	
	$kod=user_list();

	return $kod;
}

switch($m_op) {
    case 'index':
        $tartalom=user_edit($user->uid);
        break;

	case 'edit':
		$tartalom = user_edit($_REQUEST['uid']);
        break;

    case 'adding':    	
    	$newuser = new User($_REQUEST['uid']);

		if($_REQUEST['terms']!=1 AND $newuser->uid == 0 AND $user->uid == 0) {
			addMessage("El kell fogadni a <i>Házirendet és szabályzatot</i>!",'danger');
		} else {
    		$newuser->submit($_REQUEST);
    	}

    	if($user->uid == 0) {
    		//TODO: főoldalra irányítani
    	} else {
    		$tartalom = user_edit($newuser->uid);
    	}
        break;

	case 'jelszo':
		$tartalom=user_jelszo();
		break;

	case 'jelszokuld':
		$tartalom=user_jelszokuld();
		break;

	case 'list':
		$tartalom=user_list();
		break;

    case 'del':
        $tartalom=user_del($_REQUEST['uid']);
        break;

	case 'delete':
		if(is_numeric($_REQUEST['uid']) AND $user->checkRole('user') AND $user->uid != $_REQUEST['uid']) {
			$user2delete = new User($_REQUEST['uid']);
			$user2delete->delete();
		} else {
			//TODO: elegánsabb hibakezelést!
			die('No-no! Nem lehetséges így a törlés!');
		}
        
        $tartalom = user_mod();
    	break;
}

?>