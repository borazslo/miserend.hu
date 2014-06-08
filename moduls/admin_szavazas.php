<?php

function szavazas_index() {
	global $linkveg,$m_id;

	$menu.="<a href=?m_id=$m_id&m_op=add$linkveg class=adminmenulink>Új szavazás - hozzáadás</a><br>";
	$menu.="<a href=?m_id=$m_id&m_op=mod$linkveg class=adminmenulink>Szavazás módosítása, törlése</a><br>";

	$adatT[2]="<span class=alcim>Szavazás beállítása</span><br><br>".$menu;
	$tipus='doboz';
	$tartalom.=formazo($adatT,$tipus);	
	
	$kod=$tartalom;

	return $kod;
}

function szavazas_add($kid) {
	global $sessid,$m_id,$db_name;

	if($kid>0) {
		$query="select kerdes,tipus,datum,fooldal from szavazask where id='$kid'";
		list($kerdes,$tipus,$datum,$fooldal)=mysql_fetch_row(mysql_db_query($db_name,$query));

		$query="select id,valasz,szamlalo,sorszam from szavazasv where kid='$kid' order by sorszam";
		$lekerdez=mysql_db_query($db_name,$query);
		while(list($vid,$valasz,$szamlalo,$sorszam)=mysql_fetch_row($lekerdez)) {
			$vidT[]=$vid;
			$valaszT[]=$valasz;
			$szamlaloT[]=$szamlalo;
			$sorszamT[]=$sorszam;
		}
	}
	else {
		$datum=date('Y-m-d');
		$sorszamT=array(1,2,3,4,5,6,7,8);
	}

	$urlap="\n<form method=post>";
	$urlap.="\n<input type=hidden name=m_id value=$m_id><input type=hidden name=sessid value=$sessid>";
	$urlap.="\n<input type=hidden name=m_op value=adding><input type=hidden name=kid value=$kid>";
	
	$urlap.="\n<span class=kiscim>Kérdés: </span><br><input type=text name=kerdes value='$kerdes' class=urlap size=60 maxlength=200>";
	
	$urlap.="\n<br><br><span class=kiscim>Típus: </span><br><select name=tipus class=urlap>";
	$urlap.="\n<option value=s";
	if($tipus!='m') $urlap.=' selected';
	$urlap.=">egy válasz</option><option value=m";
	if($tipus=='m') $urlap.=' selected';
	$urlap.=">több válasz</option></select>";

	$urlap.="\n<br><br><span class=kiscim>Dátum</span><span class=alap> (amikortól megjelenik):</span><br><input type=text name=datum class=urlap maxlength=10 value='$datum' size=10>";

	$urlap.="\n<br><br><span class=kiscim>Lehetséges válaszok</span><span class=alap> (válasz, számláló):</span><br>";
	for($i=0;$i<8;$i++) {
		$j=$i+1;
		$urlap.="\n<input type=hidden name=vid[$i] value=$vidT[$i]>";
		$urlap.="\n<span class=kiscim><input type=text name='sorszam[$i]' value='$sorszamT[$i]' class=urlap size=2 maxlength=3> <input type=text name=valasz[$i] class=urlap value='$valaszT[$i]' maxlength=200 size=60>";
		$urlap.="\n<input type=text name=szamlalo[$i] value='$szamlaloT[$i]' maxlength=5 size=3 class=urlap><br>";
	}

	$urlap.="\n<br><br><span class=kiscim>Főoldalak: </span><br><span class=alap>Mely főoldal(ak)on legyen ez a szavazás? Több is választható!</span><br><select name=fooldalT[] multiple class=urlap>";
	$lekerdez=mysql_db_query($db_name,"select id,menucim from fooldal where ok='i' order by menusorrend");
	while(list($rid,$rnev)=mysql_fetch_row($lekerdez)) {
		$urlap.="\n<option value=$rid";
		if(strstr($fooldal,"-$rid-")) $urlap.=' selected';
		$urlap.=">$rnev</option>";
	}
	$urlap.="</select>";

	$urlap.="\n<br><br><input type=submit value=Mehet class=urlap></form>";

	$adatT[2]="<span class=alcim>Szavazás beállítása</span><br><br>".$urlap;
	$tipus='doboz';
	$tartalom.=formazo($adatT,$tipus);	
	
	$kod=$tartalom;

	return $kod;
}

function szavazas_adding() {
	global $_POST,$db_name;

	$kid=$_POST['kid'];
	$kerdes=$_POST['kerdes'];
	$tipus=$_POST['tipus'];
	$datum=$_POST['datum'];
	$valaszT=$_POST['valasz'];
	$sorszamT=$_POST['sorszam'];
	$szamlaloT=$_POST['szamlalo'];
	$vidT=$_POST['vid'];
	$fooldalT=$_POST['fooldalT'];
	if(is_array($fooldalT)) $fooldalak='-'.implode('--',$fooldalT).'-';

	if($kid>0) {
		$uj=false;
		$parameter1='update';
		$parameter2="where id='$kid'";
	}
	else {
		$uj=true;
		$parameter1='insert';
		$parameter2='';
	}

	$query="$parameter1 szavazask set kerdes='$kerdes', tipus='$tipus', datum='$datum', fooldal='$fooldalak' $parameter2";
	mysql_db_query($db_name,$query);
	if($uj) $kid=mysql_insert_id();

	for($i=0;$i<8;$i++) {
		if($vidT[$i]>0) {
			if(!empty($valaszT[$i])) $query="update szavazasv set valasz='$valaszT[$i]', szamlalo='$szamlaloT[$i]', sorszam='$sorszamT[$i]' where id='$vidT[$i]'";
			else $query="delete from szavazasv where id='$vidT[$i]'";
		}
		elseif(!empty($valaszT[$i])) {
			$query="insert szavazasv set valasz='$valaszT[$i]', szamlalo='$szamlaloT[$i]', kid='$kid', sorszam='$sorszamT[$i]'";
		}
		else $query='';

		if(!empty($query)) {
			if(!mysql_db_query($db_name,$query)) echo "HIBA!<br>".mysql_error();
		}		
	}

	$kod=szavazas_add($kid);

	return $kod;
}

function szavazas_mod() {
	global $db_name,$linkveg,$m_id;

	$kiir.="<span class=kiscim>Válassz az alábbi szavazások közül:</span><br><br>";

	$query="select id,kerdes from szavazask order by datum desc";
	$lekerdez=mysql_db_query($db_name,$query);
	while(list($kid,$kerdes)=mysql_fetch_row($lekerdez)) {
		$kiir.="\n<a href=?m_id=$m_id&m_op=add&kid=$kid$linkveg class=adminlink><b>- $kerdes</b></a> - <a href=?m_id=$m_id&m_op=del&kid=$kid$linkveg class=link><img src=img/del.jpg border=0 alt=Töröl align=absmiddle> töröl</a><br>";
	}

	$adatT[2]="<span class=alcim>Szavazás beállítása</span><br><br>".$kiir;
	$tipus='doboz';
	$tartalom.=formazo($adatT,$tipus);	
	
	$kod=$tartalom;


	return $kod;
}

function szavazas_del() {
	global $_GET,$db_name,$linkveg,$m_id;

	$kid=$_GET['kid'];

	$kiir="\n<span class=kiscim>Biztosan törölni akarod a következő szavazást?</span>";
	
	$query="select kerdes from szavazask where id='$kid'";
	list($kerdes)=mysql_fetch_row(mysql_db_query($db_name,$query));

	$kiir.="\n<br><br><span class=alap>$kerdes</span>";

	$kiir.="<br><br><a href=?m_id=$m_id&m_op=delete&kid=$kid$linkveg class=adminlink>Igen</a> - <a href=?m_id=$m_id&m_op=mod$linkveg class=link>NEM</a>";

	$adatT[2]="<span class=alcim>Szavazás beállítása</span><br><br>".$kiir;
	$tipus='doboz';
	$tartalom.=formazo($adatT,$tipus);	
	
	$kod=$tartalom;

	return $kod;
}

function szavazas_delete() {
	global $_GET,$db_name;

	$id=$_GET['kid'];
	$query="delete from szavazask where id='$id'";
	mysql_db_query($db_name,$query);
	$query="delete from szavazasv where kid='$id'";
	mysql_db_query($db_name,$query);
	$query="delete from szavazasell where kid='$id'";
	mysql_db_query($db_name,$query);

	$kod=szavazas_mod();

	return $kod;
}

//Jogosultság ellenőrzése
if(strstr($u_jogok,'szavazas')) {

switch($m_op) {
    case 'index':
        $tartalom=szavazas_index();
        break;

	case 'add':
		$kid=$_GET['kid'];
        $tartalom=szavazas_add($kid);
        break;

    case 'mod':
        $tartalom=szavazas_mod();
        break;

    case 'adding':
        $tartalom=szavazas_adding();
        break;

    case 'del':
        $tartalom=szavazas_del();
        break;

	case 'delete':
        $tartalom=szavazas_delete();
        break;
}
}
else {
	$tartalom="\n<span class=hiba>HIBA! Nincs hozzá jogosultságod!</span>";
}
?>
