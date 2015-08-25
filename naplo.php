<?php


$kidob="Érvénytelenség. Kérjük, zárjon be.<Script languate=javascript> close(); </script>";


include("load.php");


function teendok($tid) {
	global $user;
	
	//TODO: tisztes kidobást kérek!
	if(!is_numeric($tid)) die($kidob);

	$templom = getChurch($tid);
	if(!empty($templom['ismertnev'])) $templom['ismertnev']="(".$templom['ismertnev'].")";
	$vars['church'] = $templom;

	if(!$user->checkRole('miserend') and !($user->login == $templom['feltolto'] and $templom['megbizhato']=='i')) {
			die($kidob);
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
    			<input type=hidden name=rid value=".$remark->id.">
    			<input type=hidden name=op value=mod>";

		$urlap.="<span class=urlap>állapot: </span>
			<select name='allapot' class='urlap my_dropdown' id='my_dropdown'>
				<option value='0'>-----</option>";
				foreach(array(
						'u'=>'új',
						'f'=>'folyamatban',
						'plebaniara' => 'lezárva és a plébániára irányítva',
						'koszon' => 'javítva és megköszönve',
						 'j'=>'lezárva/javítva') as $i=>$ertek) {
					if($i!=$remark->allapot) {
						$urlap.="<option value=$i>$ertek</option>";
					}
				}
		$urlap.="</select>";
		
		if($remark->allapot!='j') $urlap.="<br><span class=urlap>megjegyzés:</span><br><textarea name=adminmegj class=urlap cols=17 rows=3></textarea>";
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

	if(!is_numeric($_REQUEST['rid'])) die('Tök helytelen azonosító.');

	$id=$_POST['rid'];
	$allapot=sanitize($_POST['allapot']);
	$adminmegj=sanitize($_POST['adminmegj']);

	//TODO: Csak saját tmeplom vagy miserend admin
	$jogok = $user->jogok;

	$most=date('Y-m-d H:i:s');
	$mostkiir=date('Y-m-d H:i');

	$remark = new Remark($_REQUEST['rid']);
	if($remark->id < 1) die('Attól még, hogy szám az rid, még nem biztos, hogy létezik.');

	if($adminmegj!='') {		
		if(!empty($remark->adminmegj)) $remark->adminmegj.="\n";
		$query="UPDATE eszrevetelek SET adminmegj=\"".$remark->adminmegj."<img src=img/edit.gif align=absmiddle title='".$user->login." (".date('Y-m-d H:i').")'> $adminmegj \" where id='".$id."' LIMIT 1";
		if(!mysql_query($query)) echo "HIBA!<br>".mysql_error();
	}

	if($allapot == 'plebaniara' OR $allapot == 'koszon') $allapot = 'j';

	if($allapot!='0') {	
		$query="UPDATE eszrevetelek set admin='".$user->login."', admindatum='".date('Y-m-d H:i:s.')."', allapot='".$allapot."' where id='".$id."' LIMIT 1";
	echo $query;	
		mysql_query($query);

		if($allapot=='u') $allapot1='i';
		elseif($allapot=='f') $allapot1='f';
		elseif($allapot=='j') $allapot1='n';

	}
	teendok($remark->tid);
}

function email() {
	global $twig;

	if(!is_numeric($_REQUEST['rid'])) die('Tök helytelen azonosító.');
	$remark = $vars['remark'] = new Remark($_REQUEST['rid']);
	$vars['church'] = $textvars['church'] = getChurch($remark->tid);
	$vars['type'] = $_REQUEST['type'];

	switch($_REQUEST['type']) {

		case 'plebaniara':
			$content .= "szívás babám";
			break;

		case 'koszonet':
			$content .= 'köszönjük ám';
			$textvars = array('remark'=>$remark);
			$vars['text'] = $twig->render('email_feedback_koszonet.twig',$textvars);
			break;

	}

	$vars['content'] = $content;

    echo $twig->render('naplo_email.twig',$vars);

	echo $header.$content.$footer;
	echo "?";

}

function sendemail() {
	if(isset($_REQUEST['clear'])) {
			$remark = new Remark($_REQUEST['rid']);
			teendok($remark->tid);
	}

	$mail = new Mail();
	$mail->to = $_REQUEST['email'];
	$mail->content = $_REQUEST['text'];
	$mail->type = "feedback_thanks";
	if(!isset($_REQUEST['subject']) OR $_REQUEST['subject'] == '') $_REQUEST['subject'] = "Miserend";
	$mail->subject = $_REQUEST['subject'];
	$mail->send();

	$remark = new Remark($_REQUEST['rid']);

    teendok($remark->tid);
	

}

switch($_REQUEST['op']) {

	case 'sendemail':

		sendemail();
        break;


	case 'email':

		email();
        break;


	case 'mod':

		mod();
        break;

    default:    	
        teendok($_REQUEST['id']);
        break;

}


?>
