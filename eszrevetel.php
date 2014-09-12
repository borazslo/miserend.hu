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

	$id=$_GET['id'];
	$kod=$_GET['kod'];

	$query="select nev,ismertnev,varos,egyhazmegye from templomok where id='$id' and ok='i'";
	if(!$lekerdez=mysql_query($query)) echo "HIBA!<br>$query<br>".mysql_error();
	list($nev,$ismertnev,$varos,$ehm)=mysql_fetch_row($lekerdez);

	if(!is_numeric($id)) {	
		$form = "Hibás templom azonosító. Ennyike.<script language=Javascript>close();</script>";
	}	
	else {
		$form ="<form method=post action=?op=add><input type=hidden name=id value='$id'>";
		$form.="<input type=hidden name=ehm value=$ehm>";
		if(!$user->loggedin) {
			$form.="<span class=alap>Nevem: </span><input type=text size=40 name=nev class=urlap >";
			$form.="<br><span class=alap>Email címem: </span><input type=text size=40 name=email class=urlap >";
			$form.="<br><br><span class=alap>Észrevételeim a templom adataihoz: </span><br>";
		} else {
			$form.="<input type=hidden size=40 name=nev value='".$user->nev."'>";
			$form.="<input type=hidden size=40 name=email value='".$user->email."'>";
		}
		$form .= "<textarea name=leiras class=urlap cols=70 rows=20></textarea>";
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
	global $_POST,$config,$twig,$user;

	include_once('classes.php');

	$id=$_POST['id'];

	$nev=$_POST['nev'];
	$email=$_POST['email'];
	$leiras=$_POST['leiras'];
	$ehm=$_POST['ehm'];

	$login = $user->username;

	$most=date('Y-m-d H:i:s');
	
	if(!empty($email) and strlen($email)>7) $feltetelT[]="email='$email'";
	if($login!='*vendeg*') $feltetelT[]="login='$login'";
	if(is_array($feltetelT)) {
		$feltetel=implode(' or ',$feltetelT);
		$query="select megbizhato from eszrevetelek where $feltetel order by datum limit 0,1";
		$lekerdez=mysql_query($query);
		list($megbizhato)=mysql_fetch_row($lekerdez);
	}
	if(!empty($megbizhato)) $mbiz="megbizhato='$megbizhato', ";
	$query="insert eszrevetelek set nev='$nev', login='$login', email='$email', $mbiz datum='$most', hol_id='$id', allapot='u', leiras='".sanitize($leiras)."'";
	mysql_query($query);

	$query="update $kod set eszrevetel='i' where id='$id'";
	mysql_query($query);
	
	$query="select nev,ismertnev,varos,kontaktmail from templomok where id = ".$id." limit 0,1";
	$lekerdez=mysql_query($query);
	$templom=mysql_fetch_assoc($lekerdez);
				
	$eszrevetel.= "<a href=\"http://miserend.hu/?templom=".$id."\">".$templom['nev']." (";
	if($templom['ismertnev'] != "" ) $eszrevetel .= $templom['ismertnev'].", ";
	$eszrevetel .= $templom['varos'].")</a><br/>\n";
	$eszrevetel.= "<i><a href=\"mailto:".$email."\" target=\"_blank\">".$nev."</a>"; if($login != '') $eszrevetel .= ' ('.$login.') '; $eszrevetel .= ":</i><br/>\n";
	$eszrevetel.= sanitize($leiras)."<br/>\n";
	
	$query="select email from egyhazmegye where id='$ehm'";
	$lekerdez=mysql_query($query);
	list($felelosmail)=mysql_fetch_row($lekerdez);

	$remark = new Remark();
	$remark->PreparedText4Email = $eszrevetel;

	//Mail küldés az egyházmegyei felelősnek
	if(!empty($felelosmail)) { $remark->SendMail('diocese',$felelosmail);}
	
	//Mail küldés a karbantartónak / felelősnek
	if(!empty($templom['kontaktmail'])) { $remark->SendMail('contact',$felelosmail);}

	//Mail küldése a debuggernek, hogy boldog legyen
	$remark->SendMail('debug',$config['mail']['debugger']);
	

	$content .= "<h2>Köszönjük!</h2><strong>A megjegyzést elmentettük és igyekszünk mihamarabb feldolgozni!</strong></br></br>".$remark->PreparedText4Email."<br/><input type='button' value='Ablak bezárása' onclick='self.close()'>";
	if($config['debug']<1) $content .= "<script language=Javascript>setTimeout(function(){self.close();},3000);</script>";

	$vars = array(
			'pagetitle' => 'Észrevétel beküldése',
			'title' => "<b>".$templom['nev']." </b> ".$templom['ismertnev']."  - <u>".$templom['varos']." </u>",
			'description' => 'Javítások, változások bejelentése a templom adataival, miserenddel, kapcsolódó információkkal (szentségimádás, rózsafűzér, hittan, stb.) kapcsolatban.',
			'content' => $content);

	echo $twig->render('remark.html',$vars);
}


?>
