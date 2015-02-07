<?php
include 'load.php';


switch ($_REQUEST['q']) {
    case 'FormMassEmpty':
    	$form = formMass($_POST['period'],$_POST['count'],false,'period');
    	echo $twig->render('admin_form_mass.html', $form);  
        break;
    case 'FormMassParticularEmpty':
        $form = formMass($_POST['particular'],$_POST['count'],false,'particular');
        echo $twig->render('admin_form_mass_particular.html', $form);  
        break;    
    case 'FormPeriodEmpty':
    	$form = formPeriod($_POST['period'],false,'period');
        echo $twig->render('admin_form_period.html', $form);  
        break;
    case 'FormParticularEmpty':
        $form = formPeriod($_POST['particular'],false,'particular');
        echo $twig->render('admin_form_particular.html', $form);  
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
    case 'AutocompleteCity':
        $return[] = array('label' => $_REQUEST['text']."* <i>(Minden ".$_REQUEST['text']."-al kezdődő)</i>",'value'=>$_REQUEST['text']."*");

        $query = "SELECT varos, orszag FROM templomok WHERE varos LIKE '".$_REQUEST['text']."%' ";
        $query .= "GROUP BY varos ORDER BY varos LIMIT 10";
        if(!$lekerdez=mysql_query($query)) echo "HIBA a város keresőben!<br>$query<br>".mysql_error();
        while($row=mysql_fetch_row($lekerdez,MYSQL_ASSOC)) {
            $return[] = array('label' => $row['varos'],'value'=>$row['varos']);
        }
        echo json_encode(array('results'=>$return));
        break;
    case 'AutocompleteName':
        $text = sanitize($_REQUEST['text']);

        $query = "SELECT idoszamitas, tol, ig FROM misek WHERE torles = '0000-00-00 00:00:00' AND idoszamitas LIKE '%".$text."%' ";
        if($_REQUEST['type'] == 'period') $query .= " AND tmp_datumtol <> tmp_datumig ";
        elseif($_REQUEST['type'] == 'particular') $query .= " AND tmp_datumtol = tmp_datumig ";
        $query .= " GROUP BY idoszamitas ORDER BY idoszamitas LIMIT 10";

        if(!$lekerdez=mysql_query($query)) echo "HIBA az időszak keresőben!<br>$query<br>".mysql_error();
        while($row=mysql_fetch_row($lekerdez,MYSQL_ASSOC)) {
            preg_match('/^(.*?)( -[0-9]{1,3}| \+[0-9]{1,3}|)$/',$row['tol'],$from);
            preg_match('/^(.*?)( -[0-9]{1,3}| \+[0-9]{1,3}|)$/',$row['ig'],$to);
            if($to[2] == '') $to[2] = '0'; if($from[2] == '') $from[2] = '0';
            $return[] = array('label' => preg_replace('/('.$text.')/i','<b>$1</b>',$row['idoszamitas']),'value'=>$row['idoszamitas'],'from'=>$from[1],'from2'=>trim($from[2]),'to'=>$to[1],'to2'=>trim($to[2]));
        }
        echo json_encode(array('results'=>$return));
        break;
    case 'AutocompleteAttributes':
        $text = sanitize($_REQUEST['text']);

        $results = array();

        if($_REQUEST['type'] == 'language') $attributes = array('h' => 'magyar',
                    'en' => 'angol',
                    'fr' => 'francia',
                    'gr' => 'görög',
                    'hr' => 'horvát',
                    'va' => 'latin',
                    'pl' => 'lengyel',
                    'de' => 'német',
                    'it' => 'olasz',
                    'ro' => 'román',
                    'es' => 'spanyol',
                    'sk' => 'szlovák',
                    'si' => 'szlovén');
        else { 
            $attributes = array();
            $tmp = unserialize (ATTRIBUTES);
            foreach($tmp as $abbrev => $attribute) {
                $attributes[$abbrev] = $attribute['name'];
            }
        }
        $periods = array(''=>'minden héten','1'=>'1. héten','2'=>'2. héten','3'=>'3. héten','4'=>'4. héten','5'=>'5. héten','-1'=>'utolsó héten','ps'=>'páros héten','pt'=>'páratlan héten');
        
        foreach($attributes as $key => $val) {
            if(preg_match('/^'.$text.'/i',$key) OR preg_match('/^'.$text.'/i',$val) ) {
                $results[] = array('label' => $key." <i>(".$val.")</i>",'value'=>$key);
            }
        }

        foreach($attributes as $key => $val) {
            if($text == $key) {
                foreach ($periods as $k => $v) {
                    $results[] = array('label' => $key.$k." <i>(".$val." ".$v.")</i>",'value'=>$key.$k);
                }
            }
        }
        
        

        echo json_encode(array('results'=>$results));
        break;
    case 'AutocompleteEvents':
        if($_REQUEST['text'] == '' OR preg_match('/^[0-9]{1}/i',$_REQUEST['text'])) {
            $return[] = array('label' => '<i>hónap és nap (hh-nn)</i>','value'=>date('m-d'));
            $return[] = array('label' => '<i>pontos dátum (éééé-hh-nn)</i>','value'=>date('Y-m-d'));
            $return[] = array('label' => '<i>vagy megfelelő kifejezés</i>','value'=>'');

        }

        $query = "SELECT name FROM events WHERE name  LIKE '%".$_REQUEST['text']."%' GROUP BY name ORDER BY name LIMIT 8";
        if(!$lekerdez=mysql_query($query)) $return[] = array('label' => 'hiba', 'value'=> "HIBA az esemény keresőben!<br>$query<br>".mysql_error());
        while($row=mysql_fetch_row($lekerdez,MYSQL_ASSOC)) {
            $return[] = array('label' => $row['name'],'value'=>$row['name']);
        }
        echo json_encode(array('results'=>$return));
        break;
    case 'OSMOsszeKapcsol':
        echo osm_kapcsol_ment($_POST['oid'],$_POST['tid']);
        break;
    case 'JSONP_miserend':
        echo widget_miserend($_REQUEST);
        break;
    case 'EventsList':
        $query = "SELECT name FROM events GROUP BY name";
        if(!$lekerdez=mysql_query($query)) echo "HIBA a város keresőben!<br>$query<br>".mysql_error();
        while($row=mysql_fetch_row($lekerdez,MYSQL_ASSOC)) {
            $return[] = $row['name'];
        }
        echo json_encode(array('events'=>$return));
        break;    
    default:
        //code to be executed if n is different from all labels;
}



?>