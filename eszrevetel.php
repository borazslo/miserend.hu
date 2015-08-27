<?php
include("load.php");

$op=$_POST['op'];
if(empty($op)) $op=$_GET['op'];

switch($op) {
	default:
    	urlap();
    	break;

	case 'add':
		adatadd();
    	break;        	
}


function urlap() {
	global $db_name,$twig, $user;

	//TODO: következő sor törlése + url-ból "kód" törlése
	$tid=sanitize($_GET['id']);
	if(isset($_REQUEST['tid']) AND is_numeric($_REQUEST['tid'])) $tid = $_REQUEST['tid'];

	$query="select nev,ismertnev,varos,egyhazmegye from templomok where id='$tid' and ok='i'";
	if(!$lekerdez=mysql_query($query)) echo "HIBA!<br>$query<br>".mysql_error();
	list($nev,$ismertnev,$varos,$ehm)=mysql_fetch_row($lekerdez);

	if(!is_numeric($tid)) {	
		$form = "Hibás templom azonosító. Ennyike.<script language=Javascript>close();</script>";
	}	
	else {
		$form ="<form method=post action=?op=add><input type=hidden name=tid value='$tid'>";
		if(!$user->loggedin) {
			$form.="<span class=alap>Nevem: </span><input type=text size=40 name=nev class=urlap >";
			$form.="<br><span class=alap>Email címem: </span><input type=text size=40 name=email class=urlap > * <strong>kötelező</strong>";
			$form.="<br><br><span class=alap>Észrevételeim a templom adataihoz: </span><br>";
		} else {
			$form.="<input type=hidden size=40 name=nev value='".$user->nev."'>";
			$form.="<input type=hidden size=40 name=email value='".$user->email."'>";
		}
		$form .= "<textarea name=leiras class='form-control' rows=20></textarea>";
		$form.="<br><input type=submit value=Elküld class=urlap></td></tr></table></form>";
		}
	$vars = array(
			'pagetitle' => 'Észrevétel beküldése',
			'title' => "<b>$nev</b> $ismertnev - <u>$varos</u>",
			'description' => 'Javítások, változások bejelentése a templom adataival, miserenddel, kapcsolódó információkkal (szentségimádás, rózsafűzér, hittan, stb.) kapcsolatban.',
			'content' => $form,
			'disclaimer' => 'Figyelem! Nem állunk közvetlen kapcsolatban a plébániákkal ezért plébániai ügyekben (pl. keresztelési okiratok, stb.) sajnos nem tudunk segíteni.' );

	echo $twig->render('remark_form.html',$vars);

}

function adatadd() {
	global $_REQUEST,$config,$twig;

	include_once('classes.php');

	if(!isset($_REQUEST['tid']) OR !is_numeric($_REQUEST['tid'])) 
		$content = "<h4>HIBA!</h4>Nincs templomazonosító elküldve!";
	else {
		$remark = new Remark();

		$remark->tid=$_REQUEST['tid'];
		$templom = getChurch($_REQUEST['tid']);
		$remark->name = sanitize($_REQUEST['nev']);		
		$remark->email = sanitize($_REQUEST['email']);
		
		if($remark->email == '') unset($remark->email);
		$remark->text = sanitize($_REQUEST['leiras']);
		if(!$remark->save()) addMessage("Nem sikerült elmenteni az észrevételt. Sajánljuk.","danger");
		if(!$remark->emails()) addMessage("Nem sikerült elküldeni az értesítő emaileket.","warning");
		$content = "<h2>Köszönjük!</h2><strong>A megjegyzést elmentettük és igyekszünk mihamarabb feldolgozni!</strong></br></br>".$remark->PreparedText4Email."<br/><input type='button' value='Ablak bezárása' onclick='self.close()'>";
		if($config['debug']<1) $content .= "<script language=Javascript>setTimeout(function(){self.close();},3000);</script>";

	}

	$vars = array(
			'pagetitle' => 'Észrevétel beküldése',
			'title' => "<b>".$templom['nev']." </b> ".$templom['ismertnev']."  - <u>".$templom['varos']." </u>",
			'description' => 'Javítások, változások bejelentése a templom adataival, miserenddel, kapcsolódó információkkal (szentségimádás, rózsafűzér, hittan, stb.) kapcsolatban.',
			'content' => $content);

	echo $twig->render('remark.html',$vars);
}


?>
