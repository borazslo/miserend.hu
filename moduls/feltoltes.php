<?php

function feltoltes_index() {
	global $linkveg,$m_id,$db_name,$u_login,$sid;

	$menu.="\n<span class=alcim>Feltöltés oldal</span><br><br>";
	$menu.="\n<span class=alap>Köszönjük, hogy segítesz oldalunk tartalmának gazdagításában, naprakészen tartásában!</span><br>";
	
	//Kapcsolódó templomok listája
	$querye="select distinct(hol_id),hol from eszrevetelek where hol='templomok' or hol='hirek'";
	if(!$lekerdeze=mysql_query($querye)) echo "HIBA!<br>$querym<br>".mysql_error();
	while(list($idk,$hol)=mysql_fetch_row($lekerdeze)) {
		$vaneszrevetelT[$hol][$idk]=true;
	}

	$query="select id,nev,varos,eszrevetel,megbizhato from templomok where letrehozta='$u_login' order by varos";
	$lekerdez=mysql_query($query);
	if(mysql_num_rows($lekerdez)>0) {
		$menu.="<br><span class=felsomenulink>Módosítható templomaid:</span><br>";
		while(list($tid,$tnev,$tvaros,$teszrevetel,$megbizhato)=mysql_fetch_row($lekerdez)) {
			$jelzes='';
			if($megbizhato=='i') {				
				if($teszrevetel=='i') $jelzes.="<a href=\"javascript:OpenScrollWindow('naplo.php?kod=templomok&id=$tid&sid=$sid',550,500);\"><img src=img/csomag.gif title='Új észrevételt írtak hozzá!' align=absmiddle border=0></a> ";		
				elseif($teszrevetel=='f') $jelzes.="<a href=\"javascript:OpenScrollWindow('naplo.php?kod=templomok&id=$tid&sid=$sid',550,500);\"><img src=img/csomagf.gif title='Észrevétel javítása folyamatban!' align=absmiddle border=0></a> ";		
				elseif($vaneszrevetelT['templomok'][$tid]) $jelzes.="<a href=\"javascript:OpenScrollWindow('naplo.php?kod=templomok&id=$tid&sid=$sid',550,500);\"><img src=img/csomag1.gif title='Észrevételek!' align=absmiddle border=0></a> ";		
			}

			$menu.="$jelzes<a href=?m_id=27&m_op=addtemplom&tid=$tid$linkveg class=kismenulink>- $tnev<font color=#8D317C> ($tvaros)</font></a> - <a href=?m_id=27&m_op=addmise&tid=$tid$linkveg class=kismenulink><img src=img/edit.gif title='misék' align=absmiddle border=0>szentmise</a><br>";
		}
		$menu.="\n<br>";
	}	
	
	$adatT[2]=$menu;
	$tipus='doboz';
	$kod.=formazo($adatT,$tipus);	

	return $kod;
}

echo $m_op;

switch($m_op) {
    case 'index':
        $tartalom=feltoltes_index();
        break;

	case 'addtemplom':
		$tid=$_GET['tid'];
		include_once('admin_miserend.php');
        $tartalom=miserend_addtemplom($tid);
        break;

	case 'addmise':
		$tid=$_GET['tid'];
		include_once('admin_miserend.php');
        $tartalom=miserend_addmise($tid);
        break;

    case 'addingtemplom':
		include_once('admin_miserend.php');
        $tartalom=miserend_addingtemplom();
        break;

	case 'addingmise':
		include_once('admin_miserend.php');	
        $tartalom=miserend_addingmise();
        break;

}

?>
