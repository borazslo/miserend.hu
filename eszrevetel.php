<?php

$header="<html><head><title>VPP - Észrevételek</title>\n<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">\n<style TYPE=\"text/css\">\n.alap { font-family: Arial, Verdana; font-size: 10pt; text-align: justify; }\n.urlap { font-family: Arial, Verdana;  font-size: 70%; color: #000000; background-color: #FFFFFF; }\n</style>\n</head>\n<body bgcolor=\"#FFFFFF\" text=\"#000000\">";

$footer="</body></html>";

include("config.inc");
dbconnect();

function urlap() {
	global $db_name,$header,$footer;

	$sid=$_GET['sid'];
	$id=$_GET['id'];
	$kod=$_GET['kod'];

	if(!is_numeric($id)) {		
		echo $header."<script language=Javascript>close();</script>".$footer;
		exit();
	}
	
	$kiir.="<form method=post action=?op=add><input type=hidden name=kod value='$kod'><input type=hidden name=sid value='$sid'><input type=hidden name=id value='$id'>";

	if($kod=='templomok') {
		$query="select nev,ismertnev,varos,egyhazmegye from templomok where id='$id' and ok='i'";
		if(!$lekerdez=mysql_query($query)) echo "HIBA!<br>$query<br>".mysql_error();
		list($nev,$ismertnev,$varos,$ehm)=mysql_fetch_row($lekerdez);
		$kiir.="<input type=hidden name=ehm value=$ehm>";
		$kiir.="\n<table width=100% bgcolor=#F5CC4C><tr><td class=alap><big><b>$nev</b> $ismertnev - <u>$varos</u></big><br><i>Javítások, változások bejelentése a templom adataival, miserenddel, kapcsolódó információkkal (szentségimádás, rózsafűzér, hittan, stb.) kapcsolatban</i></big></td></tr></table>";
		$kiir.="\n<table width=100% bgcolor=#ECD9A4 cellpadding=5 cellspacing=1><tr><td bgcolor=#FFFFFF>";
		$kiir.="<span class=alap>Nevem: </span><input type=text size=40 name=nev class=urlap>";
		$kiir.="<br><span class=alap>Email címem: </span><input type=text size=40 name=email class=urlap>";
		$kiir.="<br><br><span class=alap>Észrevételeim a templom adataihoz: </span><br><textarea name=leiras class=urlap cols=70 rows=20></textarea>";
	}
	if($kod=='hirek') {
		$query="select cim,datum from hirek where id='$id' and ok='i'";
		if(!$lekerdez=mysql_query($query)) echo "HIBA!<br>$query<br>".mysql_error();
		list($cim,$datum)=mysql_fetch_row($lekerdez);
		$datum=substr($datum,0,10);
		$kiir.="\n<table width=100% bgcolor=#F5CC4C><tr><td class=alap><big><b>$cim</b></big> - $datum<br><i>Javítások, változások bejelentése a hír / esemény adataival kapcsolatban</i></big></td></tr></table>";
		$kiir.="\n<table width=100% bgcolor=#ECD9A4 cellpadding=5 cellspacing=1><tr><td bgcolor=#FFFFFF>";
		$kiir.="<span class=alap>Nevem: </span><input type=text size=40 name=nev class=urlap>";
		$kiir.="<br><span class=alap>Email címem: </span><input type=text size=40 name=email class=urlap>";
		$kiir.="<br><br><span class=alap>Észrevételeim a hírhez, eseményhez: </span><br><textarea name=leiras class=urlap cols=70 rows=20></textarea>";
	}

	$kiir.="<br><input type=submit value=Elküld class=urlap></td></tr></table></form>";

	echo $header.$kiir.$footer;
}

function adatadd() {
	global $_POST,$db_name,$header,$footer;

	$sid=$_POST['sid'];
	$id=$_POST['id'];
	$kod=$_POST['kod'];

	$nev=$_POST['nev'];
	$email=$_POST['email'];
	$leiras=$_POST['leiras'];
	$ehm=$_POST['ehm'];

	$query="select login,oldlogin from session where sessid='$sid'";
	$lekerdez=mysql_query($query);
	list($login,$oldlogin)=mysql_fetch_row($lekerdez);

	if($login=='*vendeg*' and !empty($oldlogin)) $login=$oldlogin;

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
	$query="insert eszrevetelek set nev='$nev', login='$login', email='$email', $mbiz datum='$most', hol='$kod', hol_id='$id', allapot='u', leiras='".sanitize($leiras)."'";
	mysql_query($query);

	$query="update $kod set eszrevetel='i' where id='$id'";
	mysql_query($query);

	if($kod=='templomok') {
		
		$query="select nev,ismertnev,varos,kontaktmail from templomok where id = ".$id." limit 0,1";
		$lekerdez=mysql_query($query);
		$templom=mysql_fetch_assoc($lekerdez);
					
		$eszrevetel ="------------------<br/>\n";
		$eszrevetel.= "<a href=\"http://miserend.hu/?templom=".$id."\">".$templom['nev']." (";
		if($templom['ismertnev'] != "" ) $eszrevetel .= $templom['ismertnev'].", ";
		$eszrevetel .= $templom['varos'].")</a><br/>\n";
		$eszrevetel.= "<i><a href=\"mailto:".$email."\" target=\"_blank\">".$nev."</a>"; if($login != '') $eszrevetel .= ' ('.$login.') '; $eszrevetel .= ":</i><br/>\n";
		$eszrevetel.= sanitize($leiras)."<br/>\n";
		$eszrevetel.="------------------<br/>\n";
		$eszrevetel.="Köszönjük munkádat!<br/>\nVPP";

		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		//$headers .= 'Bcc: eleklaszlosj@gmail.com' . "\r\n";
		$headers .= 'From: miserend.hu <info@miserend.hu>' . "\r\n";

		$query="select email from egyhazmegye where id='$ehm'";
		$lekerdez=mysql_query($query);
		list($felelosmail)=mysql_fetch_row($lekerdez);
		if(!empty($felelosmail)) {
			//Mail küldés az egyházmegyei felelősnek
			$targy = "Miserend - észrevétel érkezett";
			$szoveg = "Kedves egyházmegyei felelős!\n\n<br/><br/>Az egyházmegyéhez tartozó egyik templom adataihoz észrevétel érkezett.<br/>\n";
			$szoveg .= $eszrevetel;
			$fejlec = $headers; //.'To: ' . $felelosmail . "\r\n";
			mail($felelosmail,$targy,$szoveg,$fejlec);
		}
		
		if(!empty($templom['kontaktmail'])) {
			//Mail küldés az karbantartónak felelősnek
			$targy = "Miserend - észrevétel érkezett";
			$szoveg = "Kedves templom karbantartó!\n\n<br/><br/>Az egyik karbantartott templomod adataihoz észrevétel érkezett.<br/>\n";
			$szoveg .= $eszrevetel;
			$fejlec = $headers; //.'To: ' . $templom['kontaktmail'] . "\r\n";
			mail($templom['kontaktmail'],$targy,$szoveg,$fejlec);
		}
		
		//Mail küldése Elek Lacinak, hogy boldog legyen
		$targy = "Miserend - észrevétel érkezett";
		$szoveg = "Kedves admin!\n\n<br/><br/>Az egyik templom adataihoz észrevétel érkezett.<br/>\n";
		$szoveg .= $eszrevetel;
		$fejlec = $headers; //.'To: ' . $templom['kontaktmail'] . "\r\n";
		mail('eleklaszlosj@gmail.com',$targy,$szoveg,$fejlec);
	
	}
	echo $header."<script language=Javascript>close();</script>".$footer;
}

function bezar() {
	echo $header."<script language=Javascript>close();</script>".$footer;
}

$op=$_POST['op'];
if(empty($op)) $op=$_GET['op'];

switch($op) {
	default:
        	urlap();
        	break;

	case 'add':
		adatadd();
        	break;
        	
        case 'bezar':
        	bezar();
        	break;
}

function sanitize($text) {
	$text = preg_replace('/\n/i','<br/>',$text);
	$text = strip_tags($text,'<a><i><b><strong><br>');

	return $text;
}
?>
