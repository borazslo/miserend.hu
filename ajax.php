<?php
include 'load.php';


switch ($_REQUEST['q']) {
    case 'FromMassEmpty':
    	$form = formMass($_POST['period'],$_POST['count']);
    	echo $twig->render('admin_form_mass.html', $form);  
        break;
    case 'FromPeriodEmpty':
    	echo formPeriod($_POST['period']);
        break;
    case 'ChatSave':
        if($user->jogok == '') { echo json_encode(array('result'=>'error','text'=>'Hiányzó jogosultság')); break; }
        $text = sanitize($_REQUEST['text']);
        if(preg_match('/^\$(\w+)/si',$text,$match)) { 
            $kinek = $match[1]; 
            $text = preg_replace('/^(\$\w+(:*))/si',"",$text);
        }  else $kinek = "";
        if(trim(preg_replace('/^((\$|@)\w+(:*))/si',"",$text)) == '') {
            echo json_encode(array('result'=>'error','text'=>'Nem volt igazán üzenet, amit elküldhettünk volna.'));
            break;
        }
        $ip=$_SERVER['REMOTE_ADDR'];
        $host = gethostbyaddr($ip);
        $ipkiir="$ip ($host)";
        $query = "INSERT INTO chat (datum, user, kinek, szoveg, ip) VALUES ('".date('Y-m-d H:i:s')."','".$user->login."','".$kinek."','".trim($text)."','".$ipkiir."' );";
        $rv = mysql_query($query);
        if ( $rv === false ){
            echo json_encode(array('result'=>'error','text'=>'Hiba a mysql küldésben!'));
        } else {
            echo json_encode(array('result'=>'saved','text'=>$query));
        }
        //code to be executed if n=label3;
        break;
    case 'ChatLoad':
        if($user->jogok == '') { echo json_encode(array('result'=>'error','text'=>'Hiányzó jogosultság')); break; }
        $date = date('Y-m-d H:i:s',strtotime($_REQUEST['date']));
        if(!isset($_REQUEST['rev']))
            $comments = chat_getcomments(array('last'=>$date));
        else
            $comments = chat_getcomments(array('first'=>$date));

        $alert = 0;
        foreach($comments as $k => $i) {
            $comments[$k]['html'] =  $twig->render('chat_comment.html',array('comment'=>$i));
            if($i['user'] != $user->login) $alert ++;
        }

        echo json_encode(array('result'=>'loaded','comments'=>$comments,'new'=>count($comments),'alert'=>$alert));
        break;
    case 'ChatUsers':
        if($user->jogok == '') { echo json_encode(array('result'=>'error','text'=>'Hiányzó jogosultság')); break; }
        $text = chat_getusers('html');
        echo json_encode(array('result'=>'loaded','text'=>$text));
        break;
    default:
        //code to be executed if n is different from all labels;
}



?>