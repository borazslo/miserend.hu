<?php

$header = <<< EOD
	<html>
		<head>
			<title>VPP - Észrevételek</title>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
			<style TYPE="text/css">
				.alap { font-family: Arial, Verdana; font-size: 10pt; text-align: justify; }
				.urlap { font-family: Arial, Verdana;  font-size: 70%; color: #000000; background-color: #FFFFFF; }
			</style>

			<link rel="stylesheet" href="templates/style.css" type="text/css">
			<link rel="stylesheet" href="templates/newstyle.css" type="text/css">

			<link href="css/jquery-ui.icon-font.css" rel="stylesheet" type="text/css" />
			<script src="bower_components/jquery/dist/jquery.min.js"></script>
			<script src="bower_components/jquery-ui/jquery-ui.js"></script>
			<link rel="stylesheet" href="bower_components/jquery-ui/themes/smoothness/jquery-ui.css">
			<script src="js/miserend.js"></script>

		</head>
		<body bgcolor=#FFFFFF text=#000000>

EOD;

$footer ="</body></html>";

$kidob="<Script languate=javascript> close(); </script>";


include("load.php");


function teendok($tid) {
	global $user;
	global $header,$footer,$kidob;

	if(!is_numeric($tid)) die($header.$kidob.$footer);


	$templom = getChurch($tid);
	if(!empty($templom['ismertnev'])) $templom['ismertnev']="(".$templom['ismertnev'].")";
	$vars['church'] = $templom;


	if(!$user->checkRole('miserend') and !($user->login == $templom['feltolto'] and $templom['megbizhato']=='i')) {
			die($header.$kidob.$footer);
	}
	
    $remarks = array();
	$query="select id from eszrevetelek where hol_id='".$templom['id']."' order by datum desc";
	$lekerdez=mysql_query($query);
	while($row=mysql_fetch_row($lekerdez)) {
		 $tmp = new Remark($row[0]);
		 if(isset($tmp) AND $tmp->id > 0) 
		 	$remarks[$row[0]] = $tmp;
	}

    foreach($remarks as $id => $remark ) { 
    	//TODO: form kiszervezése twig-be

    	$urlap="
    		<form method=post>
    			<input type=hidden name=eid value=".$remark->id.">
    			<input type=hidden name=id value=".$remark->tid.">
    			<input type=hidden name=op value=mod>";

		$urlap.="<span class=urlap>állapot: </span><select name=allapot class=urlap><option value=0>-----</option>";
		foreach(array('u'=>'új','f'=>'folyamatban','j'=>'javítva') as $i=>$ertek) {
			if($i!=$remark->allapot) {
				$urlap.="<option value=$i>$ertek</option>";
			}
		}
		$urlap.="</select>";
		
		if($remark->allapot!='j') $urlap.="<br><span class=urlap>Megjegyzés a javításhoz:</span><br><textarea name=adminmegj class=urlap cols=17 rows=5></textarea>";
		$urlap.="<input type=submit value=ok class=urlap></form>";

		$remarks[$id]->urlap = $urlap;

    }
    $vars['remarks'] = $remarks;
  	
    global $twig;
    $kiir = $twig->render('naplo.html',$vars);


	echo $header.$kiir.$footer;
}

function mod() {
	global $header,$footer;
	global $user;

	$id=$_POST['id'];
	$kod=$_POST['kod'];

	$allapot=$_POST['allapot'];
	$eid=$_POST['eid'];
	$adminmegj=$_POST['adminmegj'];

	$jogok = $user->jogok;

	$most=date('Y-m-d H:i:s');
	$mostkiir=date('Y-m-d H:i');

	list($elogin,$eemail,$emegbizhato,$amegj)=mysql_fetch_row(mysql_query("select login,email,megbizhato,adminmegj from eszrevetelek where id='$eid'"));

	if($adminmegj!='') {		
		if(!empty($amegj)) $amegj.="\n";
		$query="update eszrevetelek set adminmegj=\"$amegj<img src=img/edit.gif align=absmiddle title='".$user->login." ($mostkiir)'> $adminmegj \" where id='$eid'";
		if(!mysql_query($query)) echo "HIBA!<br>".mysql_error();
	}

/* No EZT KELL ÁTTELEPÍTENI *
	if($emegbizhato!=$megbizhato) {
		//A megbízhatóságot az összes beküldésénél átállítjuk
		if(!empty($eemail) and strlen($eemail)>7) {			
			$query="select distinct(login) from eszrevetelek where email='$eemail' and login!='*vendeg*'";
			$lekerdez=mysql_query($query);
			$vanlogin=false;
			while(list($loginok)=mysql_fetch_row($lekerdez)) {
				$loginokT[]="login='$loginok'";
				if($loginok==$elogin) {
					$vanlogin=true;
				}
			}
		}
		if($elogin!='*vendeg*') {
			$query="select distinct(email) from eszrevetelek where login='$elogin' and email!=''";
			$lekerdez=mysql_query($query);
			$vanemail=false;
			while(list($emailek)=mysql_fetch_row($lekerdez)) {
				$emailekT[]="email='$emailek'";
				if($emailek==$eemail) $vanemail=true;
			}
		}
		if(!$vanemail and !empty($eemail) and strlen($eemail)>7) $emailekT[]="email='$eemail'";
		if(!$vanlogin and $elogin!='*vendeg*') $loginokT[]="login='$elogin'";
		if(is_array($emailekT)) {
			$feltetel.=' or '.implode(' or ',$emailekT);
		}
		if(is_array($loginokT)) {
			$feltetel.=' or '.implode(' or ',$loginokT);
		}
		$query="update eszrevetelek set megbizhato='$megbizhato' where id='$eid' $feltetel";
		mysql_query($query);
	}
/* */
	if($allapot!='0') {	
		$query="update eszrevetelek set admin='".$user->login."', admindatum='$most', allapot='$allapot' $adminmegjegyzes where id='$eid'";
		mysql_query($query);

		if($allapot=='u') $allapot1='i';
		elseif($allapot=='f') $allapot1='f';
		elseif($allapot=='j') $allapot1='n';

		$query="select id from eszrevetelek where hol='$kod' and hol_id='$id' and allapot='u'";
		$lekerdez=mysql_query($query);
		if(mysql_num_rows($lekerdez)==0) {
			$query="update $kod set eszrevetel='$allapot1' where id='$id'";
			mysql_query($query);
		}
		elseif($allapot=='u') {
			$query="update $kod set eszrevetel='$allapot1' where id='$id'";
			mysql_query($query);
		}
	}

	teendok($id,$kod);
}

switch($_REQUEST['op']) {
    default:
    	
        teendok($_REQUEST['id']);
        break;

	case 'mod':
		mod();
        break;
}


?>
