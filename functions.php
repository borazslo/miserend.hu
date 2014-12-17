<?php
function dbconnect() {
    global $config;
    $db_host = $config['connection']['host'];
    $db_uname = $config['connection']['user'];
    $db_upass = $config['connection']['password'];
    $db_name = $config['connection']['database'];

    if(!@mysql_connect($db_host, $db_uname, $db_upass)) {
        if($config['debug'] > 0)  die("Adatbázisszerverhez nem lehet csatlakozni!\n".mysql_error()."\n$idopont");
        else die('Elnézést kérünk, a szolgáltatás jelenleg nem érhető el.');
    }
    
    mysql_query("SET NAMES UTF8");
    
    if(!mysql_select_db($db_name)) {
        if($config['debug'] > 0)  die("Az '".$db_name."' adatbázis nem létezik, vagy nincs megfelelő jogosultság elérni azt!\n".mysql_error()."\n$idopont");
        else die('Elnézést kérünk, a szolgáltatás jelenleg nem érhető el.');
    }

}

function kicsinyites($forras,$kimenet,$max) {

            if(!isset($max)) $max=120;    # maximum size of 1 side of the picture.

            $src_img=ImagecreateFromJpeg($forras);

            $oh = imagesy($src_img);  # original height
            $ow = imagesx($src_img);  # original width

            $new_h = $oh;
            $new_w = $ow;

            if($oh > $max || $ow > $max){
               $r = $oh/$ow;
               $new_h = ($oh > $ow) ? $max : $max*$r;
               $new_w = $new_h/$r;
            }

            // note TrueColor does 256 and not.. 8
            $dst_img = ImageCreateTrueColor($new_w,$new_h);
            /*imageantialias($dst_img, true);*/

            /* ImageCopyResized($dst_img, $src_img, 0,0,0,0, $new_w, $new_h, ImageSX($src_img), ImageSY($src_img));*/
            ImageCopyResampled($dst_img, $src_img, 0,0,0,0, $new_w, $new_h, ImageSX($src_img), ImageSY($src_img));
            ImageJpeg($dst_img, "$kimenet");
}

function sanitize($text) {
    if(is_array($text)) foreach($text as $k=>$i) $text[$k] = sanitize($i);
    else {
        $text = preg_replace('/\n/i','<br/>',$text);
        $text = strip_tags($text,'<a><i><b><strong><br>');
        $text = trim($text);
    }
    return $text;
}

function login($name,$password) {
    $password=base64_encode(sanitize($password));
    $name = sanitize($name);
    $query = "SELECT uid FROM user where login='$name' and jelszo='$password' and ok!='n' LIMIT 11";
    $result = mysql_query($query);
    $x = mysql_fetch_assoc($result);
    if($x == '') { 
        return false;
    }

    cookieSave($x['uid'],$name);
    
    return true;
}

function getuser() {
    $salt = 'Yzsdf';

    $uid = false;
    
    if(isset($_SESSION['auth'])) {
        $tmp = explode(':',$_SESSION['auth']);
        if(count($tmp) == 3) {
            $query = "SELECT uid,login,lejarat FROM session WHERE sessid = '".$_SESSION['auth']."' LIMIT 1 ";
            $result = mysql_query($query);
            $x = mysql_fetch_assoc($result);
            if(is_array($x)) {
                if($tmp[0] == md5($salt . md5($x['uid'] . $salt))) {
                    if($tmp[2] == md5($x['lejarat'])) {
                        $uid = $x['uid'];
                        //cookieSave($x['uid'],$x['name']);
                    }
                }
            }
        }
    }
    if($uid == false AND isset($_COOKIE['auth'])) {
        $tmp = explode(':',$_COOKIE['auth']);
        if(count($tmp) == 3) {
            $query = "SELECT uid,login,lejarat FROM session WHERE sessid = '".$_COOKIE['auth']."' LIMIT 1 ";
            $result = mysql_query($query);
            $x = mysql_fetch_assoc($result);
            if(is_array($x)) {
                if($tmp[0] == md5($salt . md5($x['uid'] . $salt))) {
                    if($tmp[2] == md5($x['lejarat'])) {
                        $uid = $x['uid'];
                        cookieSave($x['uid'],$x['name']);
                    }
                }
            }
        }
    }

    $return = new User($uid);
    return $return;
}

function cookieSave($uid,$name) {
    $salt = 'Yzsdf';
    $identifier = md5($salt . md5($uid . $salt));
    $token = md5(uniqid(rand(), TRUE));
    $timeout = time() + 60 * 60 * 24 * 7;
    setcookie('auth', "$identifier:$token:".md5($timeout), $timeout);
    $query = "DELETE FROM session WHERE uid = ".$uid." AND login = '$name' LIMIT 1;";
    mysql_query($query);
    $query = "INSERT INTO session (uid,login,sessid,lejarat) VALUES (".$uid.",'$name','$identifier:$token:".md5($timeout)."',$timeout);";
    mysql_query($query);
    $_SESSION['auth'] = "$identifier:$token:".md5($timeout);
    $query = "UPDATE user SET lastlogin = ".time()." LIMIT 1;";
    mysql_query($query);
}

function nyelvmeghatarozas() {
    global $modul_url,$linkveg;
    //Nyelv meghatározása
    $lang=$_POST['lang'];
    if(!isset($lang)) $lang=$_GET['lang'];
    if($lang=='hu') $lang='';
        
    if(!@include_once("$modul_url/szotar/alapszotar$lang.inc")) {
        $hiba=true;
        $hibauzenet_prog.='<br>Sorry, not translated this language!';
    }

    if(!empty($lang)) {
        $linkveg.="&lang=$lang";
    }
}

function neighboursUpdate($tid = false) {
    global $config;

    $query = 'SELECT szomszedos1, szomszedos2, templomok.id, lng, lat,  templomok.varos, templomok.nev 
            FROM templomok LEFT JOIN terkep_geocode ON id = tid 
            WHERE templomok.ok = "i" ORDER BY frissites DESC ';
    $result = mysql_query($query);
    
    while(($row = mysql_fetch_array($result))) $templomok[$row['id']] = $row;    

    $i = 0;
    foreach($templomok as $templom) { if($tid == false OR $templom['id'] == $tid) { 

        set_time_limit('600');
        $ds10 = $ds = array();
        $c = 0;
        $szomszedsag = array();
        $szomszedsag10 = array();
        foreach($templomok as $szomszed) {
            
            $lat1 = $templom['lat'] * M_PI / 180;
            $lat2 = $szomszed['lat'] * M_PI / 180;
            $long1 = $templom['lng'] * M_PI / 180;
            $long2 = $szomszed['lng'] * M_PI / 180;
            $R = 6371; // km
            $d = $R * acos(sin($lat1) * sin($lat2) + cos($lat1) * cos($lat2) * cos($long2 - $long1)) * 1000;
            
            if($d < 10000 AND $szomszed['id'] <> $templom['id']) {
                $szomszedsag10[$d] = array('id'=>$szomszed['id'],'d'=>$d,'nev'=>$szomszed['nev'],'varos'=>$szomszed['varos']);
                $ds10[$d] = $d;
                }
            if($szomszed['id'] <> $templom['id']) {
                $szomszedsag[$d] = array('id'=>$szomszed['id'],'d'=>$d,'nev'=>$szomszed['nev'],'varos'=>$szomszed['varos']);            
                $ds[$d] = $d;
            }
            //if($c>10) break; $c++;
        }
        array_multisort($ds10, SORT_ASC, $szomszedsag10);
        array_multisort($ds, SORT_ASC, $szomszedsag);
        
        $szomszedsag = array_slice($szomszedsag, 0, 1); 
        //ksort($szomszedsag10);
        //reset($szomszedsag10);
        $szomszedsag10 = array_slice($szomszedsag10, 0, 11); 
        
        $nyers = '';
        if($config['debug'] > 0) echo " ".$templom['frissites']." <a href=\"http://miserend.hu/?templom=".$templom['id']."\">".$templom['nev']." (".$templom['varos'].")</a><br/>";
        foreach($szomszedsag10 as $szomszed) {
            $nyers .= $szomszed['id'].",";      
            //echo "<div style='margin-left:40px;'>".print_r($szomszed,1)."</div>";
        }
        
        $elso = array_shift(array_values($szomszedsag));
        $elso = "".$elso['id']."";
        if($nyers == '') $nyers = '';
        if($templom['szomszedos1'] == "") {}
        
            $query = "UPDATE templomok SET szomszedos1 = '".$elso."' WHERE id = ".$templom['id']." LIMIT 1";
            if($config['debug'] > 1) echo $query."<br/>";
            mysql_query($query);
            $query = "UPDATE templomok SET szomszedos2 = '".$nyers."' WHERE id = ".$templom['id']." LIMIT 1";
            if($config['debug'] > 1) echo $query."<br/>";
            mysql_query($query);
    } }
}

function LirugicalDay($datum = false) {
    global $config;

    if(empty($datum)) $datum=  date('Y-m-d');
    
    $file = 'fajlok/igenaptar/'.$datum.'.xml';
    if(file_exists($file) AND $config['debug'] == 0) { 
        $xmlstr = file_get_contents($file);
        }
    else {
        $source = "http://breviar.sk/cgi-bin/l.cgi?qt=pxml&d=".substr($datum,8,2)."&m=".substr($datum,5,2)."&r=".substr($datum,0,4)."&j=hu";
        $xmlstr = file_get_contents($source);
        @file_put_contents($file,$xmlstr);
    }
    
    if($xmlstr != '') {
    
        $xmlcont = new SimpleXMLElement($xmlstr);
        return $xmlcont->CalendarDay;
    } else 
        return false;    
 }

function LiturgicalDayAlert($html = false,$date = false) {
    global $design_url;

    $alert = false;
    $day = LirugicalDay($date);
    if($day != false AND isset($day->Celebration))  { 
        if($day->Celebration->LiturgicalCelebrationLevel <= 4 ) {
            $alert = true;
        } 
    }

    if($html == false ) {
        if($alert == false) return false;
        else return true;
    } else {
        if($alert == false) return '';
        else {
            global $twig;
            return $twig->render('alert_liturgicalday.html',array('design_url'=>$design_url));
        }
    }

    }

function checkDateBetween($date,$start,$end) {
        global $config;
        if($config['debug'] > 1) echo "Is ".$date." between ".$start." and ".$end."? <br/>";

        $year = date('Y',strtotime($date));
        if(strtotime($year."-".$start) <= strtotime($year."-".$end)) {
            if(strtotime($year."-".$start) <= strtotime($date) AND strtotime($date) <= strtotime($year."-".$end)) return true;
            else return false;
        } else {
            if(strtotime($year."-".$start) > strtotime($date) AND strtotime($date) > strtotime($year."-".$end)) return false;
            else return true;
        }
 }

function event2Date($event, $year = false) {
    if($year == false) $year = date('Y');

    $event = preg_replace('/1$/', '1 day', $event);

    $events = array();
    $query = "SELECT name, date FROM events WHERE year = '".$year."' ";
    $result = mysql_query($query);    
    while(($row = mysql_fetch_array($result))) {
            $events['name'][] = '/^'.$row['name'].'/i';
            $events['date'][] = $row['date'];
         }
    $event = preg_replace($events['name'], $events['date'], $event);
    $event = date('m-d',strtotime($event));
    
    return $event;
}

function getChurch($tid) {
    $return = array();
    $query = "SELECT * FROM templomok WHERE id = $tid LIMIT 1";
    $result = mysql_query($query);
    while(($row = mysql_fetch_array($result,MYSQL_ASSOC))) {
        foreach($row as $k => $v) {
            $return[$k] = $v;
        }
        unset($return['log']);
    }
    return $return;
}

function getMasses($tid,$date = false) {
    if($date == false) $date = date('Y-m-d');

    $napok = array('x','vasárnap','hétfő','kedd','szerda','csütörtök','péntek','szombat');
   
    $return = array();
    $query = "SELECT * FROM misek WHERE torles = '0000-00-00 00:00:00' AND tid = $tid GROUP BY idoszamitas ";
    $result = mysql_query($query);
    while(($row = mysql_fetch_array($result))) {
        $tmp = array();
        $tmp['nev'] = $row['idoszamitas'];
        $tmp['tol'] = $row['tol'];
        $tmp['ig'] = $row['ig'];
        $tmp['datumtol'] = $datumtol = event2Date($row['tol']);
        $tmp['datumig'] = $datumig = event2Date($row['ig']);
        if(checkDateBetween($date,$datumtol,$datumig)) $tmp['now'] = true;
        
        for ($i=1; $i < 8 ; $i++) {  $tmp['napok'][$i]['nev'] = $napok[$i];    }
        unset($tmp['napok'][1]);  $tmp['napok'][1]['nev'] = $napok[1];

        $query2 = "SELECT * FROM misek WHERE torles = '0000-00-00 00:00:00' AND tid = $tid AND idoszamitas = '".$row['idoszamitas']."'  ORDER BY nap, ido";
        $result2 = mysql_query($query2);
        while(($row2 = mysql_fetch_array($result2,MYSQL_ASSOC))) {
            if($row2['milyen'] != '') {
                $row2['attr'] = decodeMassAttr($row2['milyen']);
            } else $row2['attr'] = array();
            $row2['attr'] = array_merge($row2['attr'],decodeMassAttr($row2['nyelv']));
            
            $ido = (int) substr($row2['ido'],0,2);
            $row2['ido'] = $ido.":".substr($row2['ido'],3,2);

            $row2['napid'] = $row2['nap'];
            $row2['nap'] = $napok[$row2['nap']];
            $tmp['napok'][$row2['napid']]['misek'][] = $row2;
            $tmp['napok'][$row2['napid']]['nev'] = $row2['nap'];
        }
        $return[] = $tmp;

    }

    return $return;
}

function updateMass() {



}

function decodeMassAttr($text) {
    $return  = array();

    $milyen = array('d','g','cs');
    $d = array('file' => 'diak.gif','name'=>'diák mise');
    $g = array('file' => 'gitar.gif','name'=>'gitáros mise');
    $cs = array('file' => 'csendes.gif','name'=>'csendes mise');

    $nyelvek = array('h'=>'magyar', 'en'=>'angol', 'de'=>'német', 'it'=>'olasz', 'va'=>'latin', 'gr'=>'görög', 'sk'=>'szlovák', 'hr'=>'horvát', 'pl'=>'lengyel', 'si'=>'szlovén', 'ro'=>'román', 'fr'=>'francia', 'es'=>'spanyol','uk'=>'ukrán');
    foreach($nyelvek as $k => $v) {
        $$k = array('file' => 'zaszloikon/'.$k.'.gif','name'=>$v." nyelven");
        $milyen[] = $k;
    }

    preg_match_all("/(".implode('|',$milyen).")([0-5])/i", $text,$matches,PREG_SET_ORDER);
    foreach ($matches as $match) {
        if($match[1] != 'h' OR $match[2] != 0) {
            $tmp = $match[1]; $tmp = $$tmp;
            $tmp['abbrev'] = $match[1];
            if($match[2] != 0) {
                $tmp['week'] = $match[2];
                $tmp['weektext'] = $match[2].". héten";
            }
            $return[] = $tmp;
        }
    }
    //print_r($return);
    return $return;



}


function formMass($pkey,$mkey,$mass = false) {
    global $twig;

    if($mass == false) {
        $mass = array(
            'id' => 'new',
            'napid' => 7,
            'ido' => '00:00',
            'nyelv' => '',
            'milyen' => '',
            'megjegyzes' => '',
            );
    }

    $nap2options = array(
        0 => 'minden héten',
        1 => 'első héten',2 => 'második héten',3 => 'harmadik héten',4 => 'negyedik héten',5 => 'ötödik héten',
        '-1' => 'utolsó héten',
        'ps' => 'páros héten','pt'=>'páratlan héten');

    $form = array(
        'id' => array(
            'type' => 'hidden',
            'name' => "period[".$pkey."][".$mkey."][id]",
            'value' => $mass['id']),
        'nap' => array(
            'name' => "period[".$pkey."][".$mkey."][napid]",
            'options' => array('válassz','hétfő','kedd','szerda','csütörtök','péntek','szombat','vasárnap'),
            'selected' => $mass['napid']),
        'nap2' => array(
            'name' => "period[".$pkey."][".$mkey."][nap2]",
            'options' => $nap2options,
            'selected' => $mass['nap2']),
        'ido' => array(
            'name' => "period[".$pkey."][".$mkey."][ido]",
            'value' => $mass['ido'],
            'size' => 1 ),
        'nyelv' => array(
            'label' => 'nyelvek',
            'name' => "period[".$pkey."][".$mkey."][nyelv]",
            'value' => $mass['nyelv']),
        'milyen' => array(
            'label' => '[<b>g</b>]itáros, [<b>cs</b>]endes, [<b>d</b>]iák',
            'name' => "period[".$pkey."][".$mkey."][milyen]",
            'value' => $mass['milyen'],
            'style'=>'margin-left:10px' ),
        'megjegyzes' => array(
            'label' => 'megjegyzések',
            'name' => "period[".$pkey."][".$mkey."][megjegyzes]",
            'value' => $mass['megjegyzes'],
            'style' => 'margin-top:4px;width:244px')
        );
    return $form;
}

function formPeriod($pkey,$period = false) {
    global $twig;

    $c = 0;
    if($period == false) {
        $period = array(
            'nev' => 'új időszak',
            'tol' => '',
            'ig' => '',
            'napok' => array('new','new','new'));
    }
    
    $form = array(
        'nev1' => array(
            'type' => 'hidden',
            'name' => "period[".$pkey."][origname]",
            'value' => $period['nev'] ),
        'nev' => array(
            'name' => "period[".$pkey."][name]",
            'value' => $period['nev'],
            'size' => 30 ),
        'from' => array(
            'name' => "period[".$pkey."][from]",
            'value' => trim(preg_replace('/\+1$/i','',$period['tol'])),
            'size' => 20),
        'to' => array(
            'name' => "period[".$pkey."][to]",
            'value' => trim(preg_replace('/-1$/i','',$period['ig'])),
            'size' => 20),
        'from2' => array(
            'name' => "period[".$pkey."][from2]",
            'options'=> array(
                0 => '≤',
                1 => '<')),
        'to2' => array(
            'name' => "period[".$pkey."][to2]",
            'options'=> array(
                0 => '≤',
                1 => '<'))
    );
    
    if(preg_match('/\+1$/i',$period['tol'])) $form['form2']['selected'] = 1;
    if(preg_match('/-1$/i',$period['ig'])) $form['to2']['selected'] = 1;


    $form['pkey'] = $pkey;

    foreach($period['napok'] as $dkey=>$day) {
        if(isset($day['misek'])) {
            foreach($day['misek'] as $mkey=>$mass) {
                $c++;
                $form['napok'][] = formMass($pkey,$c,$mass);
            }
        }
        elseif($day == 'new') $form['napok'][] = formMass($pkey,$dkey);
    }

    $form['last'] = $c;
    $return = $twig->render('admin_form_period.html', $form);  


    return $return;

}

?>