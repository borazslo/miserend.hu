<?php

$u_login=$_GET['u_login'];
$sid=$_GET['sid'];

if(empty($u_login) and empty($sessid)) exit;

$head='<html><head><title>VPP</title><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"><meta http-equiv="refresh" content="15;URL=chat.php?u_login='.$u_login.'&sessid='.$sid.'"><link rel="stylesheet" href="templates/style.css" type="text/css"></head><body bgcolor="#ECE5C8" text="#000000">';

$foot='</body></html>';

include_once('config.inc');
dbconnect();

function index() {
	global $db_name,$_GET,$head,$foot;

	$u_login=$_GET['u_login'];
	$loginkiir1=urlencode($u_login);
	$sid=$_GET['sid'];

	$aktualis=date('Y-m-d H:i:s',time()-3600); //egy órán belül
	$aktualis1=date('Y-m-d H:i:s',time()-36000); //10 órán belül

	$huszperc=60*20;
	$huszperce=time()-$huszperc;

	//online adminok
	$query="select distinct(login) from session where uid>0 and jogok!='' and lastlogin>='$huszperce' order by lastlogin desc";
	if(!$lekerdez=mysql_db_query($db_name,$query)) $online.="HIBA<br>$query<br>".mysql_error();
	if(mysql_num_rows($lekerdez)>1) {
		$online.="\n<span class=alap>Online admin:<br>";
		while(list($loginnev)=mysql_fetch_row($lekerdez)) {
			$loginkiir2=urlencode($loginnev);
			$online.="<a href=chatadd.php?u_login=$loginkiir1&kinek=$loginkiir2&sid=$sid target=chatadd class=link>$loginnev</a>, ";
		}
		$online.="</span><hr>";
	}
	else $online="\n<span class=alap>Nincs más admin</span><hr>";

	//üzenetek
	$query="select datum,user,kinek,szoveg from chat where datum>='$aktualis1' and (kinek='' or kinek='$u_login' or user='$u_login') order by datum desc limit 0,8";
	$lekerdez=mysql_db_query($db_name,$query);
	while(list($datum,$user,$kinek,$szoveg)=mysql_fetch_row($lekerdez)) {
		if(!empty($kinek)) {
			if($kinek==$u_login) $loginkiir2=urlencode($user);
			else $loginkiir2=urlencode($kinek);
			$jelzes="<a href=chatadd.php?u_login=$loginkiir1&kinek=$loginkiir2&sid=$sid target=chatadd class=link title='válasz'><img src=img/lakat.gif align=absmiddle border=0><i> $kinek</i></a><br>";
		}
		else $jelzes='';
		if($datum<=$aktualis and $volt<1) {
			$uzenetek.="<br><span class=kicsi><font color=blue>-- 1 óránál korábbi üzenetek --</font></span><br>";
			$volt=1;
		}
		if($user==$u_login) $szin='#394873';
		else $szin='red';
		$datumkiir=substr($datum,11,-3);
		$szoveg=nl2br($szoveg);
		$uzenetek.="<span class=kicsi><b>$user</b> ($datumkiir)<br>$jelzes<font color=$szin>$szoveg</font></span><br><br>";
	}
	if(mysql_num_rows($lekerdez)==0) {
		$uzenetek="<span class=alap>Az elmúlt 10 órában nem volt üzenet</span>";
	}

	echo $head;
	echo $online.$uzenetek;
	echo $foot;
}


switch($op) {
    default:
        index();
        break;
}

?>
