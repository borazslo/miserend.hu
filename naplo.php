<?php


$kidob="Érvénytelenség. Kérjük, zárjon be.<Script languate=javascript> close(); </script>";


include("load.php");


function teendok($tid) {
	global $user, $twig;
		
	//TODO: tisztes kidobást kérek!
	if(!is_numeric($tid)) die($kidob);


	$templom = getChurch($tid);
	if(!empty($templom['ismertnev'])) $templom['ismertnev']="(".$templom['ismertnev'].")";
	$vars['church'] = $templom;

	if(!$user->checkRole('miserend') and !($user->username == $templom['letrehozta'] )) {
			addMessage("Hiányzó jogosultság. Elnézést.","danger");
			$vars['messages'] = getMessages();
    		echo  $twig->render('naplo.twig',$vars);
			exit;
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

		$urlap.="<div class='form-group '>
			<label for='my_dropdown'>Állapot</label>
			<select name='allapot' class='form-control input-sm' id='my_dropdown'>
				<option value='0'>-----</option>";
				foreach(array(
						'u'=>'új',
						'f'=>'folyamatban',
						 'j'=>'lezárva/javítva') as $i=>$ertek) {
					if($i!=$remark->allapot) {
						$urlap.="<option value=$i>$ertek</option>";
					}
				}
		$urlap.="</select></div>";
		
		if($remark->allapot!='j') $urlap.='
			<div class="form-group">
				<label for="comment">Megjegyzés</label>
				<textarea id="comment" name="adminmegj" class="form-control input-sm" rows="3" id=""></textarea>
			</div>';

		$urlap .= '<button type="submit" class="btn btn-default input-sm">Ok</button></form>';

		$remarks[$id]->urlap = $urlap;

    }
    $vars['remarks'] = $remarks;
  	
    global $twig;
    $kiir = $twig->render('naplo.twig',$vars);


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
		mysql_query($query);

		if($allapot=='u') $allapot1='i';
		elseif($allapot=='f') $allapot1='f';
		elseif($allapot=='j') $allapot1='n';

	}
	teendok($remark->tid);
}

function email() {
	global $twig,$user;

	$textvars = array();
	if(!is_numeric($_REQUEST['rid'])) die('Tök helytelen azonosító.');
	$remark = $vars['remark'] = new Remark($_REQUEST['rid']);
	$vars['church'] = $textvars['church'] = getChurch($remark->tid);
	$vars['type'] = $_REQUEST['type'];
	$textvars['remark'] = $remark;
	$textvars['user'] = $user;


	switch($_REQUEST['type']) {

		case 'koszonet':
			$vars['text'] = $twig->render('email_feedback_koszonet.twig',$textvars);
			break;

		case 'plebaniara':
			$vars['text'] = $twig->render('email_feedback_plebaniara.twig',$textvars);
			break;

		default:
			$vars['text'] = '';
			break;

	}

	$vars['content'] = $content;

    echo $twig->render('naplo_email.twig',$vars);

	echo $header.$content.$footer;

}

function sendemail() {
	if(isset($_REQUEST['clear'])) {
			$remark = new Remark($_REQUEST['rid']);
			teendok($remark->tid);
	}

	$mail = new Mail();
	$mail->to = $_REQUEST['email'];
	$mail->content = nl2br($_REQUEST['text']);
	$mail->type = "eszrevetel_".$_REQUEST['type'];
	if(!isset($_REQUEST['subject']) OR $_REQUEST['subject'] == '') $_REQUEST['subject'] = "Miserend";
	$mail->subject = $_REQUEST['subject'];

	if(!$mail->send()) addMessage('Nem sikerült elküldeni az emailt. Bocsánat.','danger');
	
	$remark = new Remark($_REQUEST['rid']);
	$remark->addComment("email küldve: ".$mail->type);

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
