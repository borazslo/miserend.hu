<?php


function chat_vars() {
	global $user;
	
	$vars['comments'] = chat_getcomments();
	$vars['lastcomment'] = $vars['comments'][0]['datum_raw'];	
	$vars['users'] = chat_getusers('html');
	return $vars;
}

function chat_html() {
	$vars = chat_vars();
	global $twig;
    return $twig->render('chat/chatBox.twig',$vars);
}

function chat_getcomments($args = array()) {
	global $user;
	$limit = 10;

	$return = array();

	$loginkiir1 = urlencode($user->login);

	$query="select id,datum,user,kinek,szoveg from chat where (kinek='' or kinek='".$user->login."' or user='".$user->login."') ";
	if(isset($args['last'])) $query .= " AND datum > '".$args['last']."' ";
	if(isset($args['first'])) $query .= " AND datum < '".$args['first']."' ";

	$query .= " order by datum desc limit 0,".$limit;
	$lekerdez=mysql_query($query);
	while($row=mysql_fetch_array($lekerdez,MYSQL_ASSOC)) {
		$row['datum_raw'] = $row['datum'];
		if(date('Y',strtotime($row['datum'])) < date('Y')) $row['datum'] = date('Y.m.d.',strtotime($row['datum']));
		elseif(date('m',strtotime($row['datum'])) < date('m')) $row['datum'] = date('m.d.',strtotime($row['datum']));
		elseif(date('d',strtotime($row['datum'])) < date('d')) $row['datum'] = date('m.d. H:i',strtotime($row['datum']));
		else $row['datum'] = date('H:i',strtotime($row['datum']));

		if($row['user'] == $user->login) $row['color'] ='#394873';
		elseif($row['kinek'] == $user->login) $row['color'] ='red';
		elseif(preg_match('/@'.$user->login.'([^a-zA-Z]{1}|$)/i',$row['szoveg'])) $row['color']='red';

		if($row['kinek'] != '') {
			if($row['kinek']==$user->login) $loginkiir2=urlencode($user->login);
			else $loginkiir2=urlencode($row['kinek']);
			
			$row['jelzes'] = "<span class='response_closed link' title='Válasz csak neki' data-to='".$row['kinek']."' ><img src=img/lakat.gif align=absmiddle height='13' border=0><i> ".$row['kinek']."</i></span>: ";
			//$row['jelzes'] .= "<a class='response_open link' title='Nyilvános válasz / említés' data-to='".$row['kinek']."'><i> ".$row['kinek']."</i></a>: ";
		}


		$row['szoveg'] = preg_replace('/@(\w+)/i','<span class="response_open" data-to="$1" style="background-color: rgba(0,0,0,0.15);">$1</span>',$row['szoveg']);
		
		
		$return[] = $row;
	}

	return $return;
}

function chat_getusers($format = false) {
	global $user;
	$return = array();
	$query="select login from user where jogok!='' and lastactive >= '".date('Y-m-d H:i:s',strtotime("-5 minutes"))."' and login <> '".$user->login."' order by lastactive desc";
	if(!$lekerdez=mysql_query($query)) $online.="HIBA<br>$query<br>".mysql_error();
	if(mysql_num_rows($lekerdez)>0) {
		while(list($loginnev)=mysql_fetch_row($lekerdez)) {
			$return[] = $loginnev;
		}
	}
	if($format == 'html') {
		foreach($return as $k=>$i) $return[$k] = '<span class="response_closed" data-to="'.$i.'" style="background-color: rgba(0,0,0,0.15);">'.$i.'</span>';
    	$text = '<strong>Online adminok:</strong> '.implode(', ', $return);
    	if(count($return)==0) $text = '<strong><i>Nincs (más) admin online.</i></strong>';
    	$return = $text;
	}
	return $return;
}


?>