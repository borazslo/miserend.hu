<?php

$head='<html><head><title>VPP - Hírporta</title><meta http-equiv="Content-Type" content="text/html; charset=iso-8859-2"><link rel="stylesheet" href="design/alap/img/style.css" type="text/css"></head><body bgcolor="#FFFFFF" text="#000000">';

$head1='<html><head><title>VPP - Hírporta</title><meta http-equiv="Content-Type" content="text/html; charset=iso-8859-2"><meta http-equiv="refresh" content="1;URL=http://www.hirporta.hu/send.php?op=bezar"><link rel="stylesheet" href="design/alap/img/style.css" type="text/css"></head><body bgcolor="#FFFFFF" text="#000000">';

$foot='</body></html>';

include_once('config.inc');
dbconnect();

function index($uzenet) {
	global $db_name,$_GET,$_POST,$head,$foot;

	$id=$_POST['id'];
	if(empty($id)) $id=$_GET['id'];
	$cimemail=$_POST['cimemail'];
	$cimnev=$_POST['cimnev'];
	$kuldemail=$_POST['kuldemail'];
	$kuldnev=$_POST['kuldnev'];
	$kuzenet=$_POST['kuzenet'];
	

	if($id<1 or !is_numeric($id)) {
		echo "<html><body><script><!-- JavaScript kód elrejtése \n close(); \n // --></SCRIPT></body></html>";
		exit;
	}
	echo $head;
	if(!empty($uzenet)) echo "<span class=alap><font color=red>$uzenet</font></span>";
    echo "<table width=450 border=0 cellpadding=0 cellspacing=0><tr><td valign=top>";

     echo "<table width=100% border=0 cellpadding=4 cellspacing=4>";
      echo "<tr><td colspan=2><span class=alcim>";
      echo "Hír továbbküldése:</span>";
	  if(!$lekerdez=mysql_db_query($db_name,"select cim from hirek where id='$id' and ok='i'")) echo "HIBA!<br>$query<br>".mysql_error();
	  list($cim)=mysql_fetch_row($lekerdez);
	  
	  if(empty($cim)) {
		  echo "<html><body><script><!-- JavaScript kód elrejtése \n close(); \n // --></SCRIPT></body></html>";
		  exit;
	  }

	  echo "<br><span class=kiscim>$cim</span>";		  
	  echo '</td></tr>';
      echo "<form name=form1 method=post>";
      echo "<input type=hidden name=op value=sending>";
      echo "<input type=hidden name=id value=$id>";
      echo "<tr><td valign=top class=alap>Címzett email címe:</td><td><input type=text name=cimemail value='$cimemail' size=40></td></tr>";
      echo "<tr><td valign=top class=alap>Címzett neve:</td><td><input type=text name=cimnev value='$cimnev' size=40></td></tr>";
      echo "<tr><td valign=top class=alap>Saját email címed:</td><td><input type=text name=kuldemail value='$kuldemail' size=40></td></tr>";
      echo "<tr><td valign=top class=alap>A Te neved:</td><td><input type=text name=kuldnev value='$kuldnev' size=40></td></tr>";
      echo "<tr><td valign=top class=alap>Üzenet:<br>Ha kívánsz valamit üzenni</td><td><textarea name=kuzenet cols=30 rows=4>$kuzenet</textarea></td></tr>";


      echo "<tr><td>&nbsp;</td><td><input type=submit value=Küldés class=urlap> &nbsp; <input type=reset value=Töröl class=urlap></td></tr>";
      echo "</form>";
     echo "</td></tr></table>";

    echo "</td></tr></table>";
	echo $foot;
}

function sending() {
	global $_POST,$head1,$foot,$db_name;

	$hiba=false;

	$id=$_POST['id'];
	$cimemail=$_POST['cimemail'];
	$cimnev=$_POST['cimnev'];
	$kuldemail=$_POST['kuldemail'];
	$kuldnev=$_POST['kuldnev'];
	$kuzenet=$_POST['kuzenet'];

	if($id<1 or !is_numeric($id)) {
		echo "<html><body><script><!-- JavaScript kód elrejtése \n close(); \n // --></SCRIPT></body></html>";
		exit;
	}


	//Email ellenőrzés
	$domain=strstr($cimemail,'@');
	$mennyi1=strlen($cimemail);
	$mennyi2=strlen($domain);
	$domain=substr($domain,1);
	
	if(!checkdnsrr($domain, "MX") and !checkdnsrr($domain, "A")) {
		$uzenet.="<br>- nemlétező domian név";
		$hiba=true;
	}
	if(!strstr($cimemail,'@')) {
		$uzenet.="<br>- az emailcímből hiányzik a @";
		$hiba=true;
	}
	if($mennyi1==$mennyi2) {
		$uzenet.="<br>- az emailcímből hiányzik a @ előtti rész";
		$hiba=true;
	}
	
	if(!$hiba) {
		$link="?hir=$id";

	    $to=$cimemail;
		$from=$kuldemail;
		if(empty($cimnev)) $cimnev='Címzett';
		if(empty($kuldemail)) $from='web@hirporta.hu';
	    if(!empty($kuldnev)) $subj="$kuldnev értesítése a VPP - Hírporta honlapról";
		else $subj="Értesítés a Hírporta honlapról";
	    $uzenet="Kedves $cimnev!\n\n";
		$uzenet.="Egy kedves ismerős szeretné felhívni figyelmed";
	    $uzenet.="\na Hírporta honlapján (http://www.hirporta.hu)";
		$uzenet.="\ntalálható hírre!";
	    if($kuzenet!="")  $uzenet.="\n\nÜzenet:\n----------------------\n$kuzenet\n----------------------\n";
		$uzenet.="\nLátogasd meg: http://www.hirporta.hu/$link";
	    $uzenet.="\n\nHasznos időtöltést kívánunk oldalaink böngészéséhez!\nVPP - Hírporta\nwww.hirporta.hu";

		mysql_db_query($db_name,"update hirek set send=send+1 where id='$id'");
		mail($to,$subj,$uzenet,"From:$from");
		echo $head1;
		echo "<p class=kiscim><br>A cikket továbbítottuk!<br>Köszönjük az ajánlást!</p>";
		echo $foot;
	}
	else {
			$uzenet="HIBA! Az emailcím hibás, kérlek ellenőrizd!".$uzenet;
			index($uzenet);	
	}
}

function bezar() {
	echo "<html><body><script><!-- JavaScript kód elrejtése \n close(); \n // --></SCRIPT></body></html>";
}

$op=$_POST['op'];
if(empty($op)) $op=$_GET['op'];

switch($op) {
    case "sending":
        sending();
        break;

	case 'bezar':
		bezar();
		break;

    default:
        index($uzenet);
        break;
}

?>
