<?php

$u_login=$_GET['u_login'];
$sid=$_GET['sid'];

if(empty($u_login) and empty($sessid)) exit;

$head='<html><head><title>VPP</title><meta http-equiv="Content-Type" content="text/html; charset=windows-1250"><link rel="stylesheet" href="design/miserend/img/style.css" type="text/css"></head><body bgcolor="#ECE5C8" text="#000000">';

$foot='</body></html>';

include_once('config.inc');
dbconnect();

function index() {
	global $db_name,$_GET,$_POST,$head,$foot,$sid,$u_login,$kinek,$_SERVER;

	$ip=$_SERVER['REMOTE_ADDR'];
    $host = gethostbyaddr($ip);
	$ipkiir="$ip ($host)";

	$sid=$_POST['sid'];
	if(empty($sid)) $sid=$_GET['sid'];

	$u_login=$_POST['u_login'];
	if(empty($u_login)) $u_login=$_GET['u_login'];
	$loginkiir1=urlencode($u_login);

	$szoveg=$_POST['szoveg'];

	if(!empty($szoveg)) {
		$kinek=$_POST['kinek'];
		//Hozzászólás volt
		$datum=date('Y-m-d H:i:s');
		$query="insert chat set datum='$datum', user='$u_login', kinek='$kinek', szoveg='$szoveg', ip='$ipkiir'";
		mysql_db_query($db_name,$query);
		$kinek='';
	}
	else $kinek=$_GET['kinek'];

	$urlap.="<div style='display: none'><form method=post></div><input type=hidden name=sid value=$sid>";
	$urlap.="<input type=hidden name=u_login value='$u_login'>";
	$urlap.="<input type=hidden name=kinek value='$kinek'>";
	if(!empty($kinek)) $urlap.="<a href=chatadd.php?u_login=$loginkiir1&kinek=&sid=$sid class=link title='visszavon'><img src=img/lakat.gif align=absmiddle border=0><i> $kinek</i></a><br>";
	$urlap.="<textarea name=szoveg class=urlap cols=15 rows=4></textarea><br>";
	$urlap.="<input type=submit value='Elküldés' class=urlap></form>";

	echo $head;
	echo $urlap;
	echo $foot;
}

switch($op) {
    default:
        index();
        break;
}

?>
