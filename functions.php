<?php

include('function_chat.php');

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
    //mysql_query("SET CHARACTER SET 'UTF8'");
    
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

    $query = "UPDATE user SET lastlogin = '".date('Y-m-d H:i:s')."' WHERE uid = ".$x['uid'].";";
    mysql_query($query);

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

function neighboursUpdate($tid = false,$type = false) {
    global $config;

    if($type == false) $type = 'mapquest';

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
            
            if($type == 'mapquest') $maxdist = 12000;
            else $maxdist = 10000;

            if($d < $maxdist AND $szomszed['id'] <> $templom['id']) {
                if($type == 'mapquest') {
                    $d = mapquestDistance($templom,$szomszed);
                }
                if($d < 10000) {
                    $szomszedsag10[$d] = array('id'=>$szomszed['id'],'d'=>$d,'nev'=>$szomszed['nev'],'varos'=>$szomszed['varos']);
                    $ds10[$d] = $d;
                }
                }
            if($szomszed['id'] <> $templom['id']) {
                $szomszedsag[$d] = array('id'=>$szomszed['id'],'d'=>$d,'nev'=>$szomszed['nev'],'varos'=>$szomszed['varos']);            
                $ds[$d] = $d;
            }
            //if($c>10) break; $c++;
        }
        array_multisort($ds10, SORT_ASC, $szomszedsag10);
        $szomszedsag10 = array_slice($szomszedsag10, 0, 11); 
        
        $nyers = '';
        if($config['debug'] > 0) echo " ".$templom['frissites']." <a href=\"http://miserend.hu/?templom=".$templom['id']."\">".$templom['nev']." (".$templom['varos'].")</a><br/>";
        foreach($szomszedsag10 as $szomszed) {
            $nyers .= $szomszed['id'].",";      
            //echo "<div style='margin-left:40px;'>".print_r($szomszed,1)."</div>";
        }
       
       //HA nincs 10km-ben belül senki, aki keresni kell valakit
       if(count($szomszedsag10) == 0 ) {
            if($type != 'mapquest')
                $elso = array_shift(array_values($szomszedsag));
            else {
                array_multisort($ds, SORT_ASC, $szomszedsag);
                $szomszedsag = array_slice($szomszedsag, 0, 15); //feltételezzük, hogy a légvonalban legközelebbi 15 között van a valódi legközelebbi is
                foreach($szomszedsag as $szomszed) {
                    $d = mapquestDistance($templom,$szomszed);
                    $szomszedsag2[$d] = array('id'=>$szomszed['id'],'d'=>$d,'nev'=>$szomszed['nev'],'varos'=>$szomszed['varos']);
                    $ds2[$d] = $d;
                }
                array_multisort($ds2, SORT_ASC, $szomszedsag2);
                $elso = array_shift(array_values($szomszedsag2));
            }
        } else 
            $elso = array_shift(array_values($szomszedsag10));
        
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

function mapquestDistance($from,$to) {
    global $config;

    if(is_array($from)) $from = $from['lat'].",".$from['lng'];
    if(is_array($to)) $to = $to['lat'].",".$to['lng'];

    $url = "http://www.mapquestapi.com/directions/v2/route?key=".$config['mapquest']['appkey'];
    $url .= "&from=".$from."&to=".$to."";
    $url .= "&outFormat=json&unit=k&routeType=shortest&narrativeType=none";
    if(!is_array($form) OR !is_array($to)) $url .= "&doReverseGeocode=true";
    else $url .= "&doReverseGeocode=false";

    $file = file_get_contents($url);
    $mapquest = json_decode($file,true);
    $d = $mapquest['route']['distance'] * 1000;
    //echo $d.": ".$url."<br/>";
    return $d;
}

function mapquestGeocode($location) {
    global $config;
    $url = "http://www.mapquestapi.com/geocoding/v1/address?key=".$config['mapquest']['appkey'];
    $url .= "&location=".urlencode($location);
    $url .= "&outFormat=json&maxResults=1";

    $file = file_get_contents($url);
    $mapquest = json_decode($file,true);
    //print_r($mapquest);
    //echo "<a href='".$mapquest['results'][0]['locations'][0]['mapUrl']."'>map</a>";
    return array_merge($mapquest['results'][0]['locations'][0]['latLng'],array('mapUrl'=>$mapquest['results'][0]['locations'][0]['mapUrl']));

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

    if($date == false) $date = date('Y-m-d'); 
    $alert = false;
    $day = LirugicalDay($date);
    if($day != false AND isset($day->Celebration))  { 
        if($day->Celebration->LiturgicalCelebrationLevel <= 4 AND date('N',strtotime($date)) != 7 ) {
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

    if(preg_match('/^([0-9]{4})(\.|-)([0-9]{2})(\.|-)([0-9]{2})(\.|)/i',$event,$match)) return $match[3]."-".$match[5];
    if(preg_match('/^([0-9]{2})(\.|-)([0-9]{2})(\.|)(( \-| \+)[0-9]{1,3}|$)/i',$event,$match)) {
        if($match[5] != '') $extra = $match[5]." days";
        return date('m-d',strtotime(date('Y')."-".$match[1]."-".$match[3]." ".$extra));
    }

    
    $event = preg_replace('/(\+|-)1$/', '${1}1 day', $event);
    $events = array();
    $query = "SELECT name, date FROM events WHERE year = '".$year."' ";
    $result = mysql_query($query);    
    while(($row = mysql_fetch_array($result))) {
            $events['name'][] = '/^'.$row['name'].'( (\+|-)([0-9]{1,3})|)$/i';
            $events['date'][] = $row['date'];
         }
    $event = preg_replace($events['name'], $events['date'], $event);
    $event = preg_replace('/^([0-9]{2})(\.|-)([0-9]{2})/i',date('Y').'$2$1$2$3',$event);
    $event = date('m-d',strtotime($event));
    return $event;
}

function events_form() {
    $query = "SELECT * FROM events ;";
    $result = mysql_query($query);
    $years = array(); $names = array();
    while(($row = mysql_fetch_array($result,MYSQL_ASSOC))) {
            $name = $row['name'];
            $form[$name][$row['year']]['input'] = array(
                'name' => 'events['.$name.']['.$row['year'].'][input]',
                'value' => $row['date'],
                'size' => '8')
            ;
            $form[$name][$row['year']]['id'] = array(
                'type' => 'hidden',
                'name' => 'events['.$name.']['.$row['year'].'][id]',
                'value' => $row['id']);
            
            $years[$row['year']] = $row['year'];
            $names[$name] = $name;
    }
    $years[date('Y')] = date('Y');
    $years[date('Y',strtotime('+1 years'))] = date('Y',strtotime('+1 years'));

    foreach(array('tol','ig') as $tolig) {
        $query = "SELECT ".$tolig." FROM misek WHERE ".$tolig." NOT REGEXP('^([0-9]{1,4})') GROUP BY ".$tolig." ";
        $result = mysql_query($query);
        while(($row = mysql_fetch_array($result,MYSQL_ASSOC))) {
            $name = preg_replace('/ (\+|-)([0-9]{1,3}$)/i','',$row[$tolig]);
            if(!isset($names[$name])) $names[$name] = $name;
        }
    }
    foreach($names as $name) {
        foreach($years as $year) {
            if(!isset($form[$name][$year])) {
                $form[$name][$year] = array(
                    'input' => array(
                        'name' => 'events['.$name.']['.$year.'][input]',
                        'size' => '8'));

            }

        }
    }
    return array('form'=>$form,'names'=>$names,'years'=>$years);
}

function events_save($form) {
    foreach($form['events'] as $name => $years) {
        foreach($years as $year => $input) {
            if(isset($input['id'])) {
                if($input['input'] != '') $date = date('Y-m-d',strtotime($input['input'])); else $date = '';
                $query = "UPDATE events SET `date` = '".$date."' WHERE id = ".$input['id']." LIMIT 1";
                mysql_query($query);
            } else {
                if($input['input'] != '') {
                    $query = "INSERT INTO events (name,year,date) VALUES ('".$name."','".$year."','".$input['input']."');";
                    mysql_query($query);


                }
            }
        }
    }

    generateMassTmp();
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

    $napok = array('x','hétfő','kedd','szerda','csütörtök','péntek','szombat','vasárnap');
    $nap2options = array(
        0 => 'minden héten',
        1 => '1. héten',2 => '2. héten',3 => '3. héten',4 => '4. héten',5 => '5. héten',
        '-1' => 'utolsó héten',
        'ps' => 'páros héten','pt'=>'páratlan héten');

   
    $return = array();
    $query = "SELECT * FROM misek WHERE torles = '0000-00-00 00:00:00' AND tid = $tid GROUP BY idoszamitas ORDER BY weight DESC";
    $result = mysql_query($query);
    while(($row = mysql_fetch_array($result))) {
        $tmp = array();
        $tmp['nev'] = $row['idoszamitas'];
        $tmp['weight'] = $row['weight'];
        $tmp['tol'] = $row['tol'];
        $tmp['ig'] = $row['ig'];
        $tmp['datumtol'] = $datumtol = $row['tmp_datumtol']; //event2Date($row['tol']);
        $tmp['datumig'] = $datumig = $row['tmp_datumig']; //event2Date($row['ig']);

        if(checkDateBetween($date,$datumtol,$datumig)) $tmp['now'] = true;

        for ($i=1; $i < 8 ; $i++) {  $tmp['napok'][$i]['nev'] = $napok[($i)];    }
        //unset($tmp['napok'][1]);  $tmp['napok'][1]['nev'] = $napok[1];

        $query2 = "SELECT * FROM misek WHERE torles = '0000-00-00 00:00:00' AND tid = $tid AND idoszamitas = '".$row['idoszamitas']."'  ORDER BY nap, ido";
        $result2 = mysql_query($query2);
        while(($row2 = mysql_fetch_array($result2,MYSQL_ASSOC))) {
            if($row2['milyen'] != '') {
                $row2['attr'] = decodeMassAttr($row2['milyen']);
            } else $row2['attr'] = array();
            $row2['attr'] = array_merge($row2['attr'],decodeMassAttr($row2['nyelv']));
            
            $ido = (int) substr($row2['ido'],0,2);
            $row2['ido'] = $ido.":".substr($row2['ido'],3,2);
            $row2['nap2_raw'] = $row2['nap2'];
            if($row2['nap2']!='') $row2['nap2'] = '('.$nap2options[$row2['nap2']].')';

            $row2['napid'] = $row2['nap'];
            $row2['nap'] = $napok[$row2['nap'] ];
            $tmp['napok'][$row2['napid']]['misek'][] = $row2;
            $tmp['napok'][$row2['napid']]['nev'] = $row2['nap'];
        }
        if($tmp['tol'] == $tmp['ig']) 
            $return['particulars'][] = $tmp;
        else $return['periods'][] = $tmp;
    }

    //order byweight
    function cmp($a, $b) {
        return $a["weight"] - $b["weight"];
    }
    usort($return['periods'], "cmp");
    usort($return['particulars'], "cmp");


    return $return;
}

function updateMass() {



}

function decodeMassAttr($text) {
    $return  = array();

    $milyen = array('d','g','cs','gor','rom','regi','ige','ifi','csal');
    $d = array('file' => 'diak.gif','name'=>'diák mise');
    $g = array('file' => 'gitar.gif','name'=>'gitáros mise');
    $cs = array('file' => 'csendes.gif','name'=>'csendes mise');

    $gor = array('file' => 'jelzes1.png','name'=>'görögkatolikus liturgia');
    $rom = array('file' => 'jelzes10.png','name'=>'római katolikus szentmise');
    $regi = array('file' => 'jelzes6.png','name'=>'régi rítusú szentmise');

    $ige = array('file' => 'biblia.gif','name'=>'igeliturgia');


    $ifi = array('file' => 'fiu.png','name'=>'ifjúsági/egyetemista');
    $csal = array('file' => 'lany.png','name'=>'családos/mocorgós');


    $nyelvek = array('h'=>'magyar', 'en'=>'angol', 'de'=>'német', 'it'=>'olasz', 'va'=>'latin', 'gr'=>'görög', 'sk'=>'szlovák', 'hr'=>'horvát', 'pl'=>'lengyel', 'si'=>'szlovén', 'ro'=>'román', 'fr'=>'francia', 'es'=>'spanyol','uk'=>'ukrán');
    foreach($nyelvek as $k => $v) {
        $$k = array('file' => 'zaszloikon/'.$k.'.gif','name'=>$v." nyelven");
        $milyen[] = $k;
    }

    preg_match_all("/(".implode('|',$milyen).")([0-5]{1}|-1|ps|pt|)(,|$)/i", $text,$matches,PREG_SET_ORDER);
    foreach ($matches as $match) {
        if($match[1] != 'h' OR $match[2] != 0) {
            $tmp = $match[1]; $tmp = $$tmp;
            $tmp['abbrev'] = $match[1];
            if($match[2] != '0' AND $match[2] != '' ) {
                $tmp['week'] = $match[2];
                if($match[2] == '-1') $match[2] = 'utolsó';
                elseif($match[2] == 'ps') $match[2] = 'páros';
                elseif($match[2] == 'pt') $match[2] = 'páratlan';
                else $match[2] = $match[2].".";
                $tmp['weektext'] = $match[2]." héten";
            }
            $return[] = $tmp;
        }
    }
    //print_r($return);
    return $return;
}


function formMass($pkey,$mkey,$mass = false,$group = false) {
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
        if($group == 'particular') $mass['napid'] = 0;
    }

    if($group == false) $group = 'period';

    $nap2options = array(
        0 => 'minden héten',
        1 => 'első héten',2 => 'második héten',3 => 'harmadik héten',4 => 'negyedik héten',5 => 'ötödik héten',
        '-1' => 'utolsó héten',
        'ps' => 'páros héten','pt'=>'páratlan héten');

    $form = array(
        'id' => array(
            'type' => 'hidden',
            'name' => $group."[".$pkey."][".$mkey."][id]",
            'value' => $mass['id']),
        'nap' => array(
            'name' => $group."[".$pkey."][".$mkey."][napid]",
            'options' => array(0=>'válassz',1=>'hétfő',2=>'kedd',3=>'szerda',4=>'csütörtök',5=>'péntek',6=>'szombat',7=>'vasárnap'),
            'selected' => $mass['napid'],
            'class' => 'nap'),
        'nap2' => array(
            'name' => $group."[".$pkey."][".$mkey."][nap2]",
            'options' => $nap2options,
            'selected' => $mass['nap2_raw']),
        'ido' => array(
            'name' => $group."[".$pkey."][".$mkey."][ido]",
            'value' => $mass['ido'],
            'size' => 1,
            'class'=> 'time' ),
        'nyelv' => array(
            'label' => 'nyelvek',
            'name' => $group."[".$pkey."][".$mkey."][nyelv]",
            'value' => $mass['nyelv'],
            'size'=>5,
            'class'=>'language'),
        'milyen' => array(
            'label' => 'milyen',
            'name' => $group."[".$pkey."][".$mkey."][milyen]",
            'value' => $mass['milyen'],
            'size'=>13,
            'class'=>'attributes' ),
        'megjegyzes' => array(
            'label' => 'megjegyzések',
            'name' => $group."[".$pkey."][".$mkey."][megjegyzes]",
            'value' => $mass['megjegyzes'],
            'style' => 'margin-top:4px;width:194px')
        );
    return $form;
}

function formPeriod($pkey,$period = false,$group = false) {
    global $twig;

    if($group == false) $group = 'period';

    $c = 0;
    if($period == false) {
        $groups = array('particular' => 'különleges miserend','period'=>' időszak');
        $period = array(
            'nev' => 'új '.$groups[$group],
            'tol' => '',
            'ig' => '',
            'napok' => array('new'));
    
    }
    
    $form = array(
        'nev1' => array(
            'type' => 'hidden',
            'name' => $group."[".$pkey."][origname]",
            'value' => $period['nev'] ),
        'nev' => array(
            'name' => $group."[".$pkey."][name]",
            'value' => $period['nev'],
            'size' => 30,
            'class'=> 'name '.$group),
        'from' => array(
            'name' => $group."[".$pkey."][from]",
            'value' => trim(preg_replace('/(\+|-)([0-9]{1})$/i','',$period['tol'])),
            'size' => 18,
            'class' => 'events'),
        'to' => array(
            'name' => $group."[".$pkey."][to]",
            'value' => trim(preg_replace('/(\+|-)([0-9]{1})$/i','',$period['ig'])),
            'size' => 18,
            'class' => 'events',
        )
    );

    if($group == 'period') {
        $form['from2'] = array(
            'name' => $group."[".$pkey."][from2]",
            'options'=> array(
                0 => '≤',
                '+1' => '<'));
        $form['to2'] = array(
            'name' => $group."[".$pkey."][to2]",
            'options'=> array(
                0 => '≤',
                '-1' => '<'));
    } elseif($group == 'particular') {
        $form['from2'] = array(
            'name' => $group."[".$pkey."][from2]",
            'options'=> array(
                '-8' => 'előtti 8. nap',
                '-7' => 'előtti 7. nap',
                '-6' => 'előtti 6. nap',
                '-5' => 'előtti 5. nap',
                '-4' => 'előtti 4. nap',
                '-3' => 'előtti 3. nap',
                '-2' => 'előtti 2. nap',
                '-1' => 'előtti 1. nap',
                0 => '',
                '+1' => 'utáni 1. nap',
                '+2' => 'utáni 2. nap',
                '+3' => 'utáni 3. nap',
                '+4' => 'utáni 4. nap',
                '+5' => 'utáni 5. nap',
                '+6' => 'utáni 6. nap',
                '+7' => 'utáni 7. nap',
                '+8' => 'utáni 8. nap'));
    }
    
    if(preg_match('/(\+|-)([0-9]{1})$/i',$period['tol'],$match)) $form['from2']['selected'] = $match[1].$match[2];
    if(preg_match('/(\+|-)([0-9]{1})$/i',$period['ig'],$match)) $form['to2']['selected'] = $match[1].$match[2];


    $form['pkey'] = $pkey;

    foreach($period['napok'] as $dkey=>$day) {
        if(isset($day['misek'])) {
            foreach($day['misek'] as $mkey=>$mass) {
                $c++;
                $form['napok'][] = formMass($pkey,$c,$mass,$group);
            }
        }
        elseif($day == 'new') $form['napok'][] = formMass($pkey,$dkey,false,$group);
    }

    $form['last'] = $c;

    return $form;
}
 
function searchChurches($args, $offset = 0, $limit = 20) {
    global $config;

    $return = array(
        'offset' => $offset,
        'limit' => $limit );

    if($args['hely'] AND $args['hely'] != '') {
        if($args['tavolsag'] == '') $args['tavolsag'] = 1;

        if(!isset($args['hely_geocode'])) $args['hely_geocode'] = mapquestGeocode($args['hely']);
        $latlng = $args['hely_geocode'];
        $lat = $latlng['lat']; // latitude of centre of bounding circle in degrees
        $lon = $latlng['lng']; // longitude of centre of bounding circle in degrees
        $rad = $args['tavolsag'];
        $R = 6371;  // earth's mean radius, km
        $filterdistance = true;
    } 

    $where = searchChurchesWhere($args);

    
    $query = "SELECT id,nev,ismertnev,varos,letrehozta,lat,lng FROM templomok "; 
    $query .= "LEFT JOIN terkep_geocode ON terkep_geocode.tid = templomok.id ";
    if(count($where)>0) $query .= "WHERE ".implode(' AND ',$where);
    $query .= " ORDER BY nev ";
    if(!$lekerdez=mysql_query($query)) echo "HIBA a templom keresőben!<br>$query<br>".mysql_error();
    $return['sum']=mysql_num_rows($lekerdez);
    if(!$filterdistance) $query .= " LIMIT ".($offset ).",".($limit + $offset);
    //echo $query;
    if(!$lekerdez=mysql_query($query)) echo "HIBA a templom keresőben!<br>$query<br>".mysql_error();
    while($row=mysql_fetch_row($lekerdez,MYSQL_ASSOC)) {
        if($filterdistance) { 
            //acos(sin(:lat)*sin(radians(Lat)) + cos(:lat)*cos(radians(Lat))*cos(radians(Lon)-:lon)) * :R < :rad
            $d = acos(sin(deg2rad($lat))*sin(deg2rad($row['lat'])) + cos(deg2rad($lat))*cos(deg2rad($row['lat']))*cos(deg2rad($row['lng'])-deg2rad($lon))) * $R;
            if($d <= $rad) {
                if($config['mapquest']['useitforsearch'] == true) {
                    $d = mapquestDistance(array('lat'=>$lat,'lng'=>$lng),array('lat'=>$row['lat'],'lng'=>$row['lng']));
                    if($d <= $rad)  $return['results'][] = $row; 
                } else {
                    $return['results'][] = $row; 
                }
            } 
        }
        else {
            $return['results'][] = $row; 
        }
    }

    if($filterdistance) {
        $return['sum'] = count($return['results']);
        if($return['sum'] > 0) $return['results'] = array_slice($return['results'], $offset, $limit + $offset);
    }
    return $return;
}

function searchChurchesWhere($args) {
    $where = array(" ok = 'i' ");

    if($args['kulcsszo'] != '') {
        $subwhere = array();
        if(preg_match('(\*|\?)',$args['kulcsszo'])) {
            $regexp = preg_replace('/\*/i','.*',$args['kulcsszo']);
            $regexp = preg_replace('/\?/i','.{1}',$regexp);
            $text = " REGEXP '".$regexp."'";
        } else {
            $text = " LIKE '%".$args['kulcsszo']."%'";
        }
        foreach(array('nev','ismertnev','varos','cim','megkozelites','plebania','megjegyzes','misemegj') as $column ) {
            $subwhere[] = $column.$text;
        }
        $where[] = " (". implode(' OR ', $subwhere).") ";

    }

    if($args['varos'] != '') {
        if($args['varos'] == 'Budapest') $args['varos'] = 'Budapest*';

            if(preg_match('(\*|\?)',$args['varos'])) {
                $regexp = preg_replace('/\*/i','.*',$args['varos']);
                $regexp = preg_replace('/\?/i','.{1}',$regexp);
                $where[] = "varos REGEXP '^".$regexp."$'";
            } else {
                $where[] = "varos='".$args['varos']."'";
            }
    }
    
    if($args['hely'] AND $args['hely'] != '') {

            $latlng = $args['hely_geocode'];
            $lat = $latlng['lat']; // latitude of centre of bounding circle in degrees
            $lon = $latlng['lng']; // longitude of centre of bounding circle in degrees
            $rad = $args['tavolsag']; // radius of bounding circle in kilometers

            $R = 6371;  // earth's mean radius, km

            // first-cut bounding box (in degrees)
            $maxLat = $lat + rad2deg($rad/$R);
            $minLat = $lat - rad2deg($rad/$R);
            // compensate for degrees longitude getting smaller with increasing latitude
            $maxLon = $lon + rad2deg($rad/$R/cos(deg2rad($lat)));
            $minLon = $lon - rad2deg($rad/$R/cos(deg2rad($lat)));

            $where[] = "( lat BETWEEN ".$minLat." AND ".$maxLat." AND lng BETWEEN ".$minLon." AND ".$maxLon.")";

    }

    if($args['gorog'] == 'gorog') {
        $where[] = "( egyhazmegye=17 OR egyhazmegye=18 )";

    }

    if($args['ehm'] != 0) $where[] = "egyhazmegye='".$args['ehm']."'";
    if(isset($args['espkerT'][$args['ehm']]) AND $args['espkerT'][$args['ehm']] != 0) $where[] = "espereskerulet='".$args['espkerT'][$args['ehm']]."'";

    return $where;
}
function searchMasses($args, $offset = 0, $limit = 20, $groupby = false) {

    $return = array(
        'offset' => $offset,
        'limit' => $limit );
    $where = array(" torles = '0000:00:00 00:00:00' ");

    //templomok
    if(isset($args['templom']) AND is_numeric($args['templom']) ) {
        $where[] = ' tid = '.$args['templom']; }
    elseif($args['varos'] != '' OR $args['kulcsszo'] != '' OR $args['egyhazmegye'] != '' OR $args['gorog'] == 'gorog' OR $args['hely'] != '' ) {
        if($args['varos'] == 'Budapest') $args['varos'] = 'Budapest*';

        $tmp = $args;
        if(isset($tmp['leptet'])) unset($tmp['leptet']);
        if(isset($tmp['min'])) unset($tmp['min']);
        $results = searchChurches($args,0,1000000);
        if(isset($results['results'])) foreach($results['results'] as $r) $subwhere[] = " tid = ".$r['id']." ";
        if(!$subwhere) $where[] = " tid = 'nincs templom' ";
        else $where[] = " (".implode(' OR ', $subwhere).")";
    }
    if($args['gorog'] == 'gorog') {
        $where[] = "( egyhazmegye=17 OR egyhazmegye=18 )";
    }
    $where[] = "miseaktiv = 1";


    //milyen nap
    if($args['mikor'] == 'x') $args['mikor'] = $args['mikordatum']; 
    $where[] = "( nap = '".date('N',strtotime($args['mikor']))."' OR nap = 0 )";



    //milyen időszakban
    $day = date('m-d',strtotime($args['mikor']));
    $where[] = "( ( tmp_datumtol <= '".$day."' AND '".$day."' <= tmp_datumig AND (tmp_relation = '<' OR  tmp_relation = '=') )
    OR  ( ( tmp_datumig <= '".$day."' OR '".$day."' <= tmp_datumtol ) AND ( tmp_relation = '>'  OR  tmp_relation = '=') ) )";

    //milyen héten
    $subwhere = array();
    if ( date('W',strtotime($args['mikor'])) & 1 ) { $parossag ='pt'; } else $parossag = "ps";
    $subwhere[] = "nap2='".$parossag."'";
    $hanyadikP = getWeekInMonth($args['mikor']);
    $hanyadikM = getWeekInMonth($args['mikor'],'-');
    $subwhere[] = "nap2='".$hanyadikP."'";
    $subwhere[] = "nap2='".$hanyadikM."'";
    $subwhere[] = "nap2='0'";
    $subwhere[] = "nap2 IS NULL";
    $subwhere[] = "nap2 = '' ";
    $where[] = " (".implode(' OR ', $subwhere).")";

    //milyen órákban
    if($args['mikor2'] == 'de') $where[] = " ido < '12:00:01' AND ido > '00:00:01' ";
    elseif($args['mikor2'] == 'du') $where[] = " ido > '11:59:59'";
    elseif($args['mikor2'] == 'x') {
        $idok = explode('-',$args['mikorido']);
        $where[] = " ido >= '".$idok[0].":00'"; 
        $where[] = " ido <= '".$idok[1].":00'"; 
    }

    //nyelv és egyéb tulajdonságok
    if($args['nyelv'] != '0' AND $args['nyelv'] != '') $where[] = "( nyelv REGEXP '(^|,)(".$args['nyelv'].")([0]{0,1}|".$hanyadikP."|".$hanyadikM."|".$parossag.")(,|$)' )";

    if(isset($args['kor'])) {
        $wherekor = array();
        foreach($args['kor'] as $kor) {
            if(in_array($kor, array('csal','d','ifi'))) {
                 $wherekor[] = " milyen REGEXP '(^|,)(".$kor.")([0]{0,1}|".$hanyadikP."|".$hanyadikM."|".$parossag.")(,|$)' ";
            } elseif($kor == 'na') {
                 $wherekor[] = " milyen NOT REGEXP '(^|,)(csal|d|ifi)([0]{0,1}|".$hanyadikP."|".$hanyadikM."|".$parossag.")(,|$)' ";
            }
        }
        $where[] = " ( ".implode(' OR ',$wherekor).") ";
    }

    if(isset($args['zene'])) {
        $wherezene = array();
        foreach($args['zene'] as $zene) {
            if(in_array($zene, array('g','cs'))) {
                 $wherezene[] = " milyen REGEXP '(^|,)(".$zene.")([0]{0,1}|".$hanyadikP."|".$hanyadikM."|".$parossag.")(,|$)' ";
            } elseif($zene == 'na') {
                 $wherezene[] = " milyen NOT REGEXP '(^|,)(g|cs)([0]{0,1}|".$hanyadikP."|".$hanyadikM."|".$parossag.")(,|$)' ";
            }
        }
        $where[] = " ( ".implode(' OR ',$wherezene).") ";
    }
    
    if($args['ritus'] != '0') {
        if($args['ritus'] == 'gor') {
            $where[] = "( milyen REGEXP '(^|,)(gor)([0]{0,1}|".$hanyadikP."|".$hanyadikM."|".$parossag.")(,|$)' OR ( (egyhazmegye = 17 OR egyhazmegye = 18 ) AND milyen NOT REGEXP '(^|,)(rom)([0]{0,1}|".$hanyadikP."|".$hanyadikM."|".$parossag.")(,|$)' ) )";    
        } elseif($args['ritus'] == 'rom') {
            $where[] = "( (milyen NOT REGEXP '(^|,)(gor)([0]{0,1}|".$hanyadikP."|".$hanyadikM."|".$parossag.")(,|$)' AND (egyhazmegye <> 17 AND egyhazmegye <> 18 )) OR ( (egyhazmegye = 17 OR egyhazmegye = 18 ) AND milyen REGEXP '(^|,)(rom)([0]{0,1}|".$hanyadikP."|".$hanyadikM."|".$parossag.")(,|$)' ) )";    
        } elseif($args['ritus'] == 'regi') {
            $where[] = " milyen REGEXP '(^|,)(regi)([0]{0,1}|".$hanyadikP."|".$hanyadikM."|".$parossag.")(,|$)' ";    
        }    
    }

    if(!isset($args['ige']) OR $args['ige'] != 'ige') {
        $where[] = " milyen NOT REGEXP '(^|,)(ige)([0]{0,1}|".$hanyadikP."|".$hanyadikM."|".$parossag.")(,|$)' ";    
    }

    //mehet a lekérés
    $query = "SELECT misek.*,templomok.nev,templomok.ismertnev,templomok.varos,templomok.letrehozta FROM misek "; 
    $query .= " LEFT JOIN templomok ON misek.tid = templomok.id ";
    if(count($where)>0) $query .= "WHERE ".implode(' AND ',$where);
    if($groupby != false) $query .= " GROUP BY ".$groupby;
    $query .= " ORDER BY ido, templomok.varos, templomok.nev ";
    if(!$lekerdez=mysql_query($query)) echo "HIBA a templom keresőben!<br>$query<br>".mysql_error();
    $mennyi=mysql_num_rows($lekerdez);
    $return['sum'] = $mennyi;


    $query .= " LIMIT ".($offset ).",".($limit);
    //echo $query; //exit;
    if(!$lekerdez=mysql_query($query)) echo "HIBA a templom keresőben!<br>$query<br>".mysql_error();
    while($row=mysql_fetch_row($lekerdez,MYSQL_ASSOC)) {
        $row['datumtol'] = $datumtol = event2Date($row['tol']);
        $row['datumig'] = $datumig = event2Date($row['ig']);
        if(checkDateBetween($date,$datumtol,$datumig)) $tmp['now'] = true;

        $return['results'][] = $row; 
    }
    //echo "<pre>".print_r($return,1); exit;
    return $return;
}

function getWeekInMonth($date,$order = '+') {
    $num = 0;
    if($order == '+')
        for($i=0;$i<6;$i++) {
            if(date("m",strtotime($date)) == date('m',strtotime($date." -".$i." week")))
                $num++;
        }
    if($order == '-')
        for($i=0;$i<6;$i++) {
            if(date("m",strtotime($date)) == date('m',strtotime($date." +".$i." week")))
            $num--;
        }
    return $num;
}

function sugolink($id,$height=false) {
    global $twig;
    $args['id'] = $id;
    if($height != false) $args['height'] = $height;
    return $twig->render('help_link.html', $args)  ; 
}


function generateMassTmp($where = false) {
    global $config;
    $updates = array();
    $query = "SELECT id, tol, ig FROM misek WHERE torles = '0000-00-00 00:00:00' ";
    if($where != false) $query .= "AND ( ".$where." ) ";
    if(!$lekerdez=mysql_query($query)) echo "HIBA a templom keresőben!<br>$query<br>".mysql_error();
    while($row=mysql_fetch_row($lekerdez,MYSQL_ASSOC)) {
        if($row['tol'] == '')  $row['tol'] = '01-01';
        $row['tmp_datumtol'] = event2Date($row['tol']);
        if($row['ig'] == '')  $row['ig'] = '12-31';
        $row['tmp_datumig'] = event2Date($row['ig']);
        if($row['tmp_datumig'] > $row['tmp_datumtol']) $row['tmp_relation'] = '<';
        elseif($row['tmp_datumtol'] == $row['tmp_datumig']) $row['tmp_relation'] = '=';
        else $row['tmp_relation'] = '>';
        $updates[] = $row;
    }

    foreach($updates as $update) {
        $query = "UPDATE misek SET tmp_datumtol = '".$update['tmp_datumtol']."',tmp_datumig = '".$update['tmp_datumig']."',tmp_relation = '".$update['tmp_relation']."' WHERE id = ".$update['id']." LIMIT 1";
        if($global['config']>1) echo $query."<br/>";
        mysql_query($query);
    }
}

function getRemarkMark($tid) {
    $return = array();

    if(is_numeric($tid)) {
        $querye="SELECT allapot FROM eszrevetelek WHERE hol='templomok' and hol_id = $tid GROUP BY allapot LIMIT 3";
        if(!$lekerdeze=mysql_query($querye)) echo "HIBA!<br>$querym<br>".mysql_error();
        while(list($cell)=mysql_fetch_row($lekerdeze)) {
            $allapot[$cell] = true;
        }
    }

    if(isset($allapot['u'])) { 
        $return['html'] = "<a href=\"javascript:OpenScrollWindow('naplo.php?kod=templomok&id=$tid',550,500);\"><img src=img/csomag.gif title='Új észrevételt írtak hozzá!' align=absmiddle border=0></a> "; 
        $return['text'] = "Új észrevételt írtak hozzá!";
        $return['mark'] = 'u';
    } elseif (isset($allapot['f'])) { 
        $return['html'] = "<a href=\"javascript:OpenScrollWindow('naplo.php?kod=templomok&id=$tid',550,500);\"><img src=img/csomagf.gif title='Észrevétel javítása folyamatban!' align=absmiddle border=0></a> "; 
        $return['text'] = "Észrevétel javítása folyamatban!";
        $return['mark'] = 'f';
    } elseif(isset($allapot['j'])) { 
        $return['html'] = "<a href=\"javascript:OpenScrollWindow('naplo.php?kod=templomok&id=$tid',550,500);\"><img src=img/csomag1.gif title='Észrevételek' align=absmiddle border=0></a> "; 
        $return['text'] = "Észrevételek";
        $return['mark'] = 'j';
    } else {
        $return['html'] = "<span class='alap'>(nincs)</span>"; 
        $return['text'] = "Nincsenek észrevételek";
        $return['mark'] = false; 
    }

    return $return;
}

function widget_miserend($args) {
    global $twig,$config;
    $tid = $args['tid'];
    $vars = getChurch($tid);
    if($vars == array()) $html = 'Nincs ilyen templom.';
    else {
        $vars['design_url'] = $config['path']['domain'];
        $vars['miserend'] = getMasses($tid);
        $html = $twig->render('widget_miserend.html', $vars);  
    };
    if(!isset($args['callback'])) return $html;
    else return $args['callback'] . '(' . json_encode(array('html'=>$html)). ')';
}

?>