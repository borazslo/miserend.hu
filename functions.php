<?php
use Illuminate\Database\Capsule\Manager as DB;

function dbconnect() {
    global $config;    
    if(!@mysql_connect($config['connection']['host'], $config['connection']['user'], $config['connection']['password'])) {
        if($config['debug'] > 0)  die("Adatbázisszerverhez nem lehet csatlakozni!\n".mysql_error());
        else die('Elnézést kérünk, a szolgáltatás jelenleg nem érhető el.');
    }
    mysql_query("SET NAMES UTF8");
    //mysql_query("SET CHARACTER SET 'UTF8'");
    
    if(!mysql_select_db($config['connection']['database'])) {
        if($config['debug'] > 0)  die("Az '".$config['connection']['database']."' adatbázis nem létezik, vagy nincs megfelelő jogosultság elérni azt!\n".mysql_error());
        else die('Elnézést kérünk, a szolgáltatás jelenleg nem érhető el.');
    };
    
    $capsule = new DB;
    $capsule->addConnection([
        'driver'    => 'mysql',
        'host'      => $config['connection']['host'],
        'database'  => $config['connection']['database'],
        'username'  => $config['connection']['user'],
        'password'  => $config['connection']['password'],
        'charset'   => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'prefix'    => '',
    ]);
    // Make this Capsule instance available globally via static methods... (optional)
    $capsule->setAsGlobal();

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
    $name = sanitize($name);
    $query = "SELECT uid,jelszo FROM user where login='$name' and ok!='n' LIMIT 11";
    $result = mysql_query($query);
    $x = mysql_fetch_assoc($result);
    if($x == '') { return false; }

    if(password_verify($password,$x['jelszo'])) {
        //we are happy        
    }
    elseif(strlen($x['jelszo']) < 60 AND base64_decode($x['jelszo']) == $password) {
        $jelszo = password_hash(base64_decode($x['jelszo']),PASSWORD_BCRYPT);
        $query = "UPDATE user SET jelszo = '".$jelszo."' WHERE login='".$name."' LIMIT 1;";
        mysql_query($query);
    }
    else {
        return false;
    }

    $query = "UPDATE user SET lastlogin = '".date('Y-m-d H:i:s')."' WHERE uid = ".$x['uid'].";";
    mysql_query($query);

    cookieSave($x['uid'],$name);
    
    return $x['uid'];
}

function checkUsername($username) {
    if($username == '') return false;
    if($username == '*vendeg*') return false;
    if(strlen($username) > 20) return false;
    if(preg_match("/( |\"|'|;)/i", $username)) return false;

    //TODO: én ezt feloldanám
    if(!preg_match("/^([a-z0-9]{1,20})$/i", $username)) return false;

    $checkeduser = new User($username);
    if($checkeduser->uid > 0) return false;
    

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
    if($return->uid > 0) $return->loggedin = true;
    return $return;
}


function clearMenu($menuitems) {
    global $user;

    foreach ($menuitems as $key => $item ) {
        if(isset($item['permission']) AND !$user->checkRole($item['permission'])) {
            unset($menuitems[$key]);
        } else {
            if(isset($item['items']) AND is_array($item['items'])) { 
                foreach ($item ['items'] as $k => $i ) {
                    if(isset($i['permission']) AND !$user->checkRole($i['permission'])) {
                        unset($menuitems[$key][$k]);
                    } else {

                    }
                }
            }
        }
    }
    return $menuitems;

}


function cookieSave($uid,$name) {
    $isCLI = ( php_sapi_name() == 'cli' );
    
    $salt = 'Yzsdf';
    $identifier = md5($salt . md5($uid . $salt));
    $token = md5(uniqid(rand(), TRUE));
    $timeout = time() + 60 * 60 * 24 * 7;
    if(!$isCLI)   setcookie('auth', "$identifier:$token:".md5($timeout), $timeout);
    $query = "DELETE FROM session WHERE uid = ".$uid." AND login = '$name' LIMIT 1;";
    mysql_query($query);
    $query = "INSERT INTO session (uid,login,sessid,lejarat) VALUES (".$uid.",'$name','$identifier:$token:".md5($timeout)."',$timeout);";
    mysql_query($query);
    $_SESSION['auth'] = "$identifier:$token:".md5($timeout);
    $query = "UPDATE user SET lastlogin = ".time()." LIMIT 1;";
    mysql_query($query);    
}


function mapquestDistance($from1,$to1) {
    global $config;
    if(is_array($from1)) $from = $from1['lat'].",".$from1['lng']; else $from = $from1;
    if(is_array($to1)) $to = $to1['lat'].",".$to1['lng']; else $to = $to1;

    $url = "http://open.mapquestapi.com/directions/v2/route?key=".$config['mapquest']['appkey'];
    $url .= "&from=".$from."&to=".$to."";
    $url .= "&outFormat=json&unit=k&routeType=shortest&narrativeType=none";
    if(!is_array($from1) OR !is_array($to1)) $url .= "&doReverseGeocode=true";
    else $url .= "&doReverseGeocode=false";
    $file = file_get_contents($url);
    $mapquest = json_decode($file,true);
    if(isset($mapquest['route']['routeError']['errorCode'])) {
        if( $mapquest['info']['statuscode'] == 602) return -1;
        elseif( $mapquest['route']['routeError']['errorCode'] > 0) return -2;
    }
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

    //TODO: ha nincs könyvár, attól még megpróbálhatná élesben lehozni.
    if (!is_dir('fajlok/igenaptar')) {
        //die('Sajnos nincsen faljok/igenaptar könyvtár. Ez komoly hiba.');
        return false;
    }

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

    if($date == false) $date = date('Y-m-d'); 
    $alert = false;
    $day = LirugicalDay($date);
    if($day != false AND isset($day->Celebration))  { 
        if($day->Celebration->LiturgicalCelebrationLevel <= 4 AND date('N',strtotime($date)) != 7 ) {
            $alert = true;
        } 
    }
    $text = "Ma van <strong>".$day->Celebration->LiturgicalCelebrationName."</strong>";
    if(preg_match("/ünnep$/i",$day->Celebration->LiturgicalCelebrationType))
        $text .= " ".$day->Celebration->LiturgicalCelebrationType."e";
    
    if($html == false ) {
        if($alert == false) return false;
        else return true;
    } else {
        if($alert == false) return '';
        else {
            global $twig;
            return $twig->render('alert_liturgicalday.html',array('text'=>$text));
        }
    }

    }

function checkDateBetween($date,$start,$end) {
        global $config;
        if($config['debug'] > 1) 
            echo "Is ".$date." between ".$start." and ".$end."? <br/>";

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
            $events['name'][] = '/^'.$row['name'].'( (\+|-)([0-9]{1,3})|)( day|)$/i';
            $events['date'][] = $row['date']."$1$4";
         }
    $event = preg_replace($events['name'], $events['date'], $event);
    $event = preg_replace('/^([0-9]{2})(\.|-)([0-9]{2})/i',date('Y').'$2$1$2$3',$event);
    $event = date('m-d',strtotime($event));
    return $event;
}

function events_form($order = false) {
    if(!$order) $order = 'year, date';
    $query = "SELECT * FROM events WHERE year >= ".date('Y','-1 year')." ORDER BY ".$order.";";
    $result = mysql_query($query);
    $years = array(); $names = array();
    while(($row = mysql_fetch_array($result,MYSQL_ASSOC))) {
            $name = $row['name'];
            $form[$name][$row['year']]['input'] = array(
                'name' => 'events['.$name.']['.$row['year'].'][input]',
                'value' => $row['date'],
                'class' => 'input-sm')
            ;
            $form[$name][$row['year']]['id'] = array(
                'type' => 'hidden',
                'name' => 'events['.$name.']['.$row['year'].'][id]',
                'value' => $row['id']);
            
            $years[$row['year']] = $row['year'];
            $names[$name] = $name;
    }
    //new line
    //$array_shift(array_values($form))
    $newname = array(
        'name' => 'newname',
        'size' => 12);
    global $twig;
    $names['new'] = 'new';

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
    //stats
        $query = "SELECT count(*) as sum FROM misek where ig  REGEXP '^(".$name.")(( +| -)[0-9]{1,3})|)$'";
        $result = mysql_query($query);
        $row = mysql_fetch_array($result,MYSQL_ASSOC);
        $stats[$name] = $row['sum'];
    
    }
    

    return array('form'=>$form,'names'=>$names,'years'=>$years,'stats'=>$stats);
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
                    if($name == 'new') $name = sanitize($form['new']);
                    if($name != 'new' OR $form['new'] != '') {
                        $query = "INSERT INTO events (name,year,date) VALUES ('".$name."','".$year."','".$input['input']."');";
                        mysql_query($query);
                        //echo $query."<br/>";
                    }
                }
            }
        }
    }

    generateMassTmp();
}

function checkPrivilege($type,$privilege,$object,$user = false) {
    if(!$user) global $user;

    switch ($type) {
        case 'church':

            switch ($privilege) {
                case 'read':

                    if($object['ok'] == 'i') return true;
                    if($object['letrehozta'] == $user->username) return true;
                    if($user->checkRole('miserend')) return true;
                    break;
                
                default:
                    return false;
                    break;
            }

            break;
        
        default:
            return false;
            break;
    }
    return false;
}

function getChurch($tid) {
    //TODO: delete getChurch();
    try {
        $church = new Church($tid);
        $church->allowOldStructure();
        return (array) $church;        
    } catch (Exception $e) {
        //If there is no such church, we return an empty array();
        //echo $e->getMessage();
        return array();
    }
}

function getMasses($tid,$date = false) {
    if($date == false OR $date == '') $date = date('Y-m-d');

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
    
    if(is_array($return['periods'])) usort($return['periods'], "cmp");
    if(is_array($return['particulars'])) usort($return['particulars'], "cmp");


    return $return;
}

function cmp($a, $b) {
        return $a["weight"] - $b["weight"];
    }

function decodeMassAttr($text) {
    $return  = array();

    $milyen = array();
    $attributes = unserialize(ATTRIBUTES);
    foreach($attributes as $abbrev => $attribute) {
        $milyen[] = $abbrev;
    }
    $languages = unserialize(LANGUAGES);
    foreach($languages as $abbrev => $language) {
        $attributes[$abbrev] = $language;
        $milyen[] = $abbrev;
    }

    preg_match_all("/(".implode('|',$milyen).")([0-5]{1}|-1|ps|pt|)(,|$)/i", $text,$matches,PREG_SET_ORDER);
    foreach ($matches as $match) {
        if(!isset($return[$match[1]])) 
            $return[$match[1]] = $attributes[$match[1]];
        $return[$match[1]]['values'][] = $match[2];
    }

    $periods = unserialize(PERIODS);
    foreach($return as $abbrev => $attribute) {
        sort($attribute['values']);
        $tmp1 = $tmp2 = '';

        for($i = 0;$i<count($attribute['values']);$i++) { 
            $tmp1 .= $periods[$attribute['values'][$i]]['abbrev'];
            $tmp2 .= $periods[$attribute['values'][$i]]['name'];
            if($i<count($attribute['values'])-2) {
                $tmp1 .= ", ";
                $tmp2 .= ", ";
            } elseif($i<count($attribute['values'])-1) {
                $tmp1 .= ", ";
                $tmp2 .= " és ";                
            }
        }
        if(count($attribute['values'])>0 AND $tmp2 != '') {
            $tmp2 .= " héten";                            
        }

        if($tmp1 != '') $return[$abbrev]['name'] .= ' '.$tmp1;
        if($tmp2 != '' AND isset($attribute['description'])) $return[$abbrev]['description'] .= ' '.$tmp2;
        

    }
    //echo "<pre>".print_r($return,1)."</pre>";
    return $return;
}

function cleanMassAttr($text) {
    $milyen = array();
    $attributes = unserialize(ATTRIBUTES);
    foreach($attributes as $abbrev => $attribute) {
        $milyen[] = $abbrev;
    }
    $languages = unserialize(LANGUAGES);
    foreach($languages as $abbrev => $language) {
        $milyen[] = $abbrev;
    }
    foreach(unserialize(PERIODS) as $abbrev => $period)
        $periods[] = $abbrev;

    $text = trim($text," ,");
    $attrs = explode(',',$text);
    sort($attrs);
    foreach($attrs as $k => $attr) {
        preg_match('/^('.implode('|',$milyen).')('.implode('|',$periods).'|)$/',$attr,$match);
        if(count($match)<1) {
            //unset($attrs[$k]);
        } elseif ($match[2] == '0') {
            $attrs[$k] = $match[1];
        } elseif($match[2] != '') {
            if(in_array($match[1],$attrs))
                unset($attrs[$k]);
        }
    }
    $attrs = array_unique($attrs);
    return implode(',',$attrs);
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
            'size' => 4,
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
            'style' => 'margin-top:4px;width:*')
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

    
    $query = "SELECT templomok.id,nev,ismertnev,varos,letrehozta,lat,lng FROM templomok "; 
    $query .= "LEFT JOIN terkep_geocode ON terkep_geocode.tid = templomok.id ";
    if($args['tnyelv'] != '0' ) { 
        $query .= " INNER JOIN misek ON misek.tid = templomok.id ";
        if($args['tnyelv'] == 'h') $args['tnyelv'] = 'hu|h';
        $where[] = " misek.nyelv  REGEXP '(^|,)(".$args['tnyelv'].")([0-5]{0,1}|-1|ps|pt)(,|$)' ";
    }
    if(count($where)>0) $query .= "WHERE ".implode(' AND ',$where);
    if($args['tnyelv'] != '0' ) $query .= " GROUP BY templomok.id ";

    $query .= " ORDER BY nev ";
    if(!$lekerdez=mysql_query($query)) echo "HIBA a templom keresőben!<br>$query<br>".mysql_error();
    $return['sum']=mysql_num_rows($lekerdez);
    if(!$filterdistance) $query .= " LIMIT ".($offset ).",".($limit + $offset);
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
        foreach(array('nev','ismertnev','varos','cim','megkozelites','plebania','templomok.megjegyzes','misemegj') as $column ) {
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
        $where[] = "egyhazmegye IN (17,18)";

    }

    if($args['ehm'] != 0) $where[] = "egyhazmegye='".$args['ehm']."'";
    if(isset($args['espkerT'][$args['ehm']]) AND $args['espkerT'][$args['ehm']] != 0) $where[] = "espereskerulet='".$args['espkerT'][$args['ehm']]."'";

    return $where;
}
function searchMasses($args, $offset = 0, $limit = 20) {


    $return = array(
        'offset' => $offset,
        'limit' => $limit );
    $where = array(" m.torles = '0000:00:00 00:00:00' ");

    //templomok
    if(isset($args['templom']) AND is_numeric($args['templom']) ) {
        $where[] = ' m.tid = '.$args['templom']; }
    elseif($args['varos'] != '' OR $args['kulcsszo'] != '' OR $args['egyhazmegye'] != '' OR $args['gorog'] == 'gorog' OR $args['hely'] != '' OR $args['tnyelv'] != '0' ) {
        if($args['varos'] == 'Budapest') $args['varos'] = 'Budapest*';

        $tmp = $args;
        if(isset($tmp['leptet'])) unset($tmp['leptet']);
        if(isset($tmp['min'])) unset($tmp['min']);
        $results = searchChurches($args,0,1000000);
        $tids[] = 0;
        if(isset($results['results'])) foreach($results['results'] as $r) $tids[] = $r['id'];
        $where[] = " m.tid IN (".implode(",",$tids).")";
    }
    if($args['gorog'] == 'gorog') {
        $where[] = "egyhazmegye IN (17,18)";
    }
    //milyen nap
    if($args['mikor'] == 'x') $args['mikor'] = $args['mikordatum']; 
    $where[] = "m.nap IN ('".date('N',strtotime($args['mikor']))."',0)";



    //milyen időszakban
    $day = date('m-d',strtotime($args['mikor']));
    $where[] = "( ( m.tmp_datumtol <= '".$day."' AND '".$day."' <= m.tmp_datumig AND m.tmp_relation = '<'  )
    OR  ( ( m.tmp_datumig <= '".$day."' OR '".$day."' <= m.tmp_datumtol ) AND ( m.tmp_relation = '>' ) )  
    OR ( m.tmp_datumig = '".$day."' AND m.tmp_datumig = '".$day."' AND  m.tmp_relation = '=' ) )";

    //milyen héten
    if ( date('W',strtotime($args['mikor'])) & 1 ) { $parossag ='pt'; } else $parossag = "ps";
    $hanyadikP = getWeekInMonth($args['mikor']);
    $hanyadikM = getWeekInMonth($args['mikor'],'-');
    $where[] = "( m.nap2 IN ('','0','".$hanyadikM."','".$hanyadikP."','".$parossag."') OR m.nap2 IS NULL)";

    //milyen órákban
    if($args['mikor2'] == 'de') $where[] = " m.ido < '12:00:01' AND m.ido > '00:00:01' ";
    elseif($args['mikor2'] == 'du') $where[] = " m.ido > '11:59:59'";
    elseif($args['mikor2'] == 'x') {
        $idok = explode('-',$args['mikorido']);
        $where[] = " m.ido >= '".$idok[0].":00'"; 
        $where[] = " m.ido <= '".$idok[1].":00'"; 
    }

    //LANGUAGES
    $languages = unserialize(LANGUAGES);
    foreach($languages as $abbrev => $attribute) if($attribute['abbrev'] != 'h') $nothu[] = $abbrev;
    if($args['nyelv'] != '0' AND $args['nyelv'] != '') {
        if($args['nyelv'] == 'h') {
            $where[] = "( m.nyelv REGEXP '(^|,)(".$args['nyelv'].")([0]{0,1}|".$hanyadikP."|".$hanyadikM."|".$parossag.")(,|$)' OR 
                templomok.orszag = 12 AND m.nyelv NOT REGEXP '(^|,)(".implode("|",$nothu).")([0]{0,1}|".$hanyadikP."|".$hanyadikM."|".$parossag.")(,|$)' )";
        } else {
            $where[] = "( m.nyelv REGEXP '(^|,)(".$args['nyelv'].")([0]{0,1}|".$hanyadikP."|".$hanyadikM."|".$parossag.")(,|$)' )";
        }
    }

    //ATTRIBUTES
    $attributes = unserialize(ATTRIBUTES);

    //age group (checkbox)
    if(isset($args['kor'])) {
        foreach($attributes as $abbrev => $attribute) {
            if($attribute['group'] == 'age')
                $ages[] = $abbrev;
        }
        $wherekor = array();
        foreach($args['kor'] as $kor) {
            if(in_array($kor, $ages)) {
                 $wherekor[] = " m.milyen REGEXP '(^|,)(".$kor.")([0]{0,1}|".$hanyadikP."|".$hanyadikM."|".$parossag.")(,|$)' ";
            } elseif($kor == 'na') {
                 $wherekor[] = " m.milyen NOT REGEXP '(^|,)(".implode('|',$ages).")([0]{0,1}|".$hanyadikP."|".$hanyadikM."|".$parossag.")(,|$)' ";
            }
        }
        $where[] = " ( ".implode(' OR ',$wherekor).") ";
    }

    //music group (chekbox)
    if(isset($args['zene'])) {
        foreach($attributes as $abbrev => $attribute) {
           if($attribute['group'] == 'music')
                $musics[] = $abbrev;
        }
        $wherezene = array();
        foreach($args['zene'] as $zene) {
            if(in_array($zene, $musics)) {
                 $wherezene[] = " m.milyen REGEXP '(^|,)(".$zene.")([0]{0,1}|".$hanyadikP."|".$hanyadikM."|".$parossag.")(,|$)' ";
            } elseif($zene == 'na') {
                 $wherezene[] = " m.milyen NOT REGEXP '(^|,)(".implode("|",$musics).")([0]{0,1}|".$hanyadikP."|".$hanyadikM."|".$parossag.")(,|$)' ";
            }
        }
        $where[] = " ( ".implode(' OR ',$wherezene).") ";
    }
    
    //rite group (select)
    if($args['ritus'] != '0') {
        if($args['ritus'] == 'gor') {
            foreach($attributes as $abbrev => $attribute) if($attribute['group'] == 'liturgy' AND $attribute['isitmass'] == true AND $attribute['abbrev'] != 'gor') $notgor[] = $abbrev;
            $where[] = "( m.milyen REGEXP '(^|,)(gor)([0]{0,1}|".$hanyadikP."|".$hanyadikM."|".$parossag.")(,|$)' OR 
                        ( (egyhazmegye = 17 OR egyhazmegye = 18 ) AND m.milyen NOT REGEXP '(^|,)(".implode("|",$notgor).")([0]{0,1}|".$hanyadikP."|".$hanyadikM."|".$parossag.")(,|$)' ) )";    
        } elseif($args['ritus'] == 'rom') {
            foreach($attributes as $abbrev => $attribute) if($attribute['group'] == 'liturgy' AND $attribute['isitmass'] == true AND $attribute['abbrev'] != 'rom') $notrom[] = $abbrev;
            $where[] = "( (m.milyen NOT REGEXP '(^|,)(".implode("|",$notrom).")([0]{0,1}|".$hanyadikP."|".$hanyadikM."|".$parossag.")(,|$)' AND egyhazmegye NOT IN (17,18)) OR 
                        ( egyhazmegye IN (17,18) AND m.milyen REGEXP '(^|,)(rom)([0]{0,1}|".$hanyadikP."|".$hanyadikM."|".$parossag.")(,|$)' ) )";    
        } else {
            $where[] = " m.milyen REGEXP '(^|,)(".$args['ritus'].")([0]{0,1}|".$hanyadikP."|".$hanyadikM."|".$parossag.")(,|$)' ";    
        } 
    }

    //liturgy (not mass) group (checkbox/radio)
    $not = $only = array();
    foreach($attributes as $abbrev => $attribute) {
        if($attribute['group'] == 'liturgy' AND $attribute['isitmass'] == false) {
            $not[$abbrev] = $abbrev;
        }
    }
    if(isset($args['liturgy'])) {
        foreach($args['liturgy'] as $liturgy) {
            if(isset($not[$liturgy])) unset($not[$liturgy]);
        }
    } 
    if(count($not) > 0)
        $where[] = " m.milyen NOT REGEXP '(^|,)(".implode('|',$not).")([0]{0,1}|".$hanyadikP."|".$hanyadikM."|".$parossag.")(,|$)' ";    
    
    $select = "SELECT m.*,templomok.nev,templomok.ismertnev,templomok.varos,templomok.letrehozta \nFROM misek m \n"; 
    $select .= " LEFT JOIN templomok ON m.tid = templomok.id \n";

    //Tudjuk meg, hogy hány templomban van megfelelő összesen
    $query = "SELECT count(*) as sum FROM ( \n";
        $query .= $select;
        if(count($where)>0) $query .= " WHERE ".implode("\n AND ",$where);
        $query .= "\n GROUP BY tid \n";
    $query .= ") groups ;";
    if(!$lekerdez=mysql_query($query)) echo "HIBA a templom keresőben!<br>$query<br>".mysql_error();
    $row=mysql_fetch_row($lekerdez,MYSQL_ASSOC);
    $return['sum'] = $row['sum'];

    //Akkor jöhet a limitált csoportos lekérdezés, mert az jó
    $query = $select;
    $query .= " JOIN ( ";
        $query .= $select;
        if(count($where)>0) $query .= " WHERE ".implode(' AND ',$where);
        $query .= " GROUP BY tid \n";
        $query .= " ORDER BY ido, templomok.varos, templomok.nev ";
        $query .= " LIMIT ".($offset ).",".($limit);
    $query .=  ") groups ON groups.tid = m.tid ";
    if(count($where)>0) $query .= " WHERE ".implode(' AND ',$where);
    $query .= " ORDER BY ido, templomok.varos, templomok.nev ";
    if(!$lekerdez=mysql_query($query)) echo "HIBA a templom keresőben!<br>$query<br>".mysql_error();
    $masses = array();
    //echo $query;
    while($row=mysql_fetch_row($lekerdez,MYSQL_ASSOC)) {
        if($row['tmp_datumtol'] == $row['tmp_datumig']) $type = 'particulars';
        else $type = 'periods';
        $masses[$row['tid']][$type][$row['idoszamitas']][] = $row;
    }

    //use particulars only, if we can    
    foreach($masses as $tid => $church) {
        if(array_key_exists("particulars", $church))
            $masses[$tid] = $church['particulars'];
        elseif(array_key_exists("periods", $church))
            $masses[$tid] = $church['periods'];
    }
    // weight
    foreach($masses as $tid => $periods) {
        $weight = 0; $tmp = array();
        foreach($periods as $period) {
            $m = array_shift(array_values($period));
            $w = $m['weight'];
            if($w == '') $w = 0;
            if($w >= $weight) {
                $tmp = $period; 
                $weight = $w;
            }
        }
        $return['churches'][$tid] = array_shift(array_values($tmp)); //ezt szebben is lehetne
        $return['churches'][$tid]['masses'] = $tmp;
    }
    /* */
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
        $querye="SELECT allapot FROM eszrevetelek WHERE ( hol='templomok' OR hol = '' )  and hol_id = $tid GROUP BY allapot LIMIT 3";
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

function map_view() {
    $return = array();
     
    $return['template'] = 'map';
    $return['script'][] = '

        <script src="js/lib/DecimalFormat.js" language="javascript"></script>
        <script src="http://openlayers.org/en/v3.2.0/build/ol.js" type="text/javascript"></script>
        <script src="http://openlayers.org/api/OpenLayers.js"></script>
        <script src="http://acuriousanimal.com/code/animatedCluster/ol/OpenLayers.js"></script>
        <script src="js/lib/AnimatedCluster.js"></script>
        ';



    
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

        if($args['misemegj'] == 'off') unset($vars['misemegj']);
        $html = $twig->render('widget_massschedule.twig', $vars);  
    };

    if(!isset($args['callback'])) return $html;
    else return $args['callback'] . '(' . json_encode(array('html'=>$html)). ')';
}

function generateSqlite($version,$filename) {
    $v = $version;
    $limit = 1650000;



    if(!isset($v)) {
        die('Kérlek adj meg egy verziót, hogy biztosan kapj eredményt!');   
    }
    elseif($v > 4) exit;

  try {
    /**************************************
    * Create databases and                *
    * open connections                    *
    **************************************/

    // Create (connect to) SQLite database in file
    $file_db = new PDO('sqlite:'.$filename);

    // Set errormode to exceptions
    $file_db->setAttribute(PDO::ATTR_ERRMODE, 
                            PDO::ERRMODE_EXCEPTION);
 
    $file_db->exec("DROP TABLE IF EXISTS templomok");
    $file_db->exec("DROP TABLE IF EXISTS misek");
    $file_db->exec("DROP TABLE IF EXISTS kepek");
 
    /**************************************
    * Create tables                       *
    **************************************/
    
    // Create table templomok
    $createtabletemplomok = "CREATE TABLE IF NOT EXISTS [templomok] (
            [tid] INTEGER  NOT NULL PRIMARY KEY,
            [nev] VARCHAR(200)  NULL,
            [ismertnev] vaRCHAR(200)  NULL,";

    if($v > 2) $createtabletemplomok .= "
            [gorog] INTEGER NULL,";
            
    $createtabletemplomok .= "
            [orszag] vARCHAR(30)  NULL,
            [megye] vARCHAR(80)  NULL,
            [varos] vaRCHAR(80)  NULL,
            [cim] vARCHAR(255)  NULL,
            [geocim] vARCHAR(255)  NULL,
            [megkozelites] vARCHAR(255)  NULL,
            [lng] fLOAT  NULL,
            [lat] flOAT  NULL,";

    if($v < 4) $createtabletemplomok .= "
            [nyariido] vARCHAR(10)  NULL,
            [teliido]vARCHAR(10)  NULL,";

    $createtabletemplomok .= "
            [kep] vARCHAR(255)  NULL
        
        )";

    $file_db->exec($createtabletemplomok);
 
    // Create table misek
    $createtablemisek = "CREATE TABLE IF NOT EXISTS [misek] (
            [mid] INTEGER  PRIMARY KEY NOT NULL,
            [tid] iNTEGER  NULL,";

    if($v < 4)
        $createtablemisek .= "      [telnyar] VARCHAR(1)  NULL,";
    
    if($v > 3) {
        $createtablemisek .= "      
                [periodus] VARCHAR(4)  NULL,
                [idoszak] VARCHAR(255)  NULL,
                [suly] INT NULL,
                [datumtol] INT  NULL,
                [datumig] INT  NULL,";
    }

    $createtablemisek .= "
            [nap] inTEGER  NULL,
            [ido] TIME  NULL,
            [nyelv] VARCHAR(3)  NULL,
            [milyen] VARCHAR(10)  NULL";

    if($v > 2) $createtablemisek .= "
            , [megjegyzes] VARCHAR(255) NULL";
    $createtablemisek .= "  )";
    $file_db->exec($createtablemisek);
 
    
    if($v > 1) {
    // Create table kepek
    $file_db->exec("CREATE TABLE IF NOT EXISTS [kepek] (
            [kid] INTEGER  PRIMARY KEY NOT NULL,
            [tid] INTEGER  NULL,
            [kep] vARCHAR(255)  NULL
        )");
    }
    /**************************************
    * Set initial data                    *
    **************************************/
 
    // mysql select templomok             
    $query = "
            SELECT t.*,orszagok.nev as orszag, megye.megyenev as megye,lat,lng, address2 as geocim FROM templomok as t 
                    LEFT JOIN orszagok ON orszagok.id = t.orszag 
                    LEFT JOIN megye ON megye.id = megye 
                    LEFT JOIN terkep_geocode ON terkep_geocode.tid = t.id 
                    WHERE t.ok = 'i'     ";                 
    if(isset($datum)) $query .= ' AND  moddatum >= "'.$datum.'" ';                                          
    $query .= " ORDER BY t.id desc LIMIT ".$limit;
    //echo $query."<br>";
    $templomok = array();
    $result = mysql_query($query);
    while($tmp = mysql_fetch_array($result)) {
        $templomok[] = $tmp; 
    }
    // mysql select kepek
    $query = "SELECT * 
            FROM  `kepek` 
            ORDER BY tid, kiemelt, sorszam DESC , id ASC ";
            //AND kiemelt =  'i'
   $result = mysql_query($query);
   $kiemeltkepek = array();
   $kepek = array();
    while($kep = mysql_fetch_array($result)) {
        if(!isset($kiemeltkepek[$kep['tid']])) { // AND $kep['kiemelt'] == 'i')
            $kiemeltkepek[$kep['tid']] = $kep;
            /*if($kep['kiemelt'] != 'i') { 
                echo 'jaj - <a href="http://www.miserend.hu/?templom='.$kep['kid'].'">'.$kep['kid'].'</a><br/>'; 
                echo $c++;
                }*/
            }
        $kepek[] = $kep;
    }
    //echo "<pre>".print_r($kepek,1);    
    
    // mysql select misek             
    $query  = "
            SELECT * FROM misek 
                    WHERE torles = '0000-00-00 00:00:00' 
                    AND tid <> 0";
    if(isset($datum)) $query .= '  AND moddatum >= "'.$datum.'" ';                                                          
    $query .= " LIMIT ".$limit;
    $misek = array();
    $result = mysql_query($query);
    while($tmp = mysql_fetch_array($result)) {
        $misek[] = $tmp;
    }
    //echo "<pre>".print_r($misek,1)."</pre>";
 
    /**************************************
    * Play with databases and tables      *
    **************************************/
    
    // Loop thru all messages and execute prepared insert statement
    $file_db->beginTransaction();
    if(is_array($templomok))
    foreach ($templomok as $templom) {
      //echo"<pre>"; print_R($misek); exit;
      set_time_limit(60);
      // Set values to bound variables
      $tid = $templom['id'];
      $nev = $templom['nev'];
      $ismertnev = $templom['ismertnev'];
      if($v > 2) {
        if(in_array($templom['egyhazmegye'],array(18,17))) { //Görög egyházmegyék kódja
            $gorog = 1;
        } else $gorog = 0;
      
      }
      $orszag = $templom['orszag'];
      $megye = $templom['megye'];
      $varos = $templom['varos'];
      $cim = $templom['cim'];
      $geocim = $templom['geocim'];
      $lng = $templom['lng'];
      $lat = $templom['lat'];
      if($v < 4) {
        $nyariido = $templom['nyariido'];
        $teliido = $templom['teliido'];
      }
      if(isset($kiemeltkepek[$tid])) $kep = "kepek/templomok/".$tid."/".$kiemeltkepek[$tid]['fajlnev'];
      else { $kep = ''; }
      if(!file_exists($kep)) $kep = ''; else 
        $kep = "http://miserend.hu/".$kep;

      $megkozelites = $templom['megkozelites']; 
 
        foreach(array('ismertnev','cim') as $var) {
            //if(preg_match("/'/",$$var)) echo $nev." (".$tid."): ".$$var."<br>\n";
            $$var = preg_replace("/'/","",$$var);
        }
 
    if($v > 3) {
      $insert = "INSERT INTO templomok (tid, nev, ismertnev, gorog, orszag, megye, varos, cim, lng, lat,megkozelites,geocim,kep) 
                VALUES ('".$tid."','".$nev."','".$ismertnev."','".$gorog."','".$orszag."','".$megye."','".$varos."','".$cim."','".$lng."','".$lat."','".$megkozelites."','".$geocim."','".$kep."')";
    } elseif($v > 2) {
      $insert = "INSERT INTO templomok (tid, nev, ismertnev, gorog, orszag, megye, varos, cim, lng, lat,nyariido,teliido,megkozelites,geocim,kep) 
                VALUES ('".$tid."','".$nev."','".$ismertnev."','".$gorog."','".$orszag."','".$megye."','".$varos."','".$cim."','".$lng."','".$lat."','".$nyariido."','".$teliido."','".$megkozelites."','".$geocim."','".$kep."')";
    } else {
        
        $insert = "INSERT INTO templomok (tid, nev, ismertnev, orszag, megye, varos, cim, lng, lat,nyariido,teliido,megkozelites,geocim,kep) 
                VALUES ('".$tid."','".$nev."','".$ismertnev."','".$orszag."','".$megye."','".$varos."','".$cim."','".$lng."','".$lat."','".$nyariido."','".$teliido."','".$megkozelites."','".$geocim."','".$kep."')";
                
    }

      // Execute statement
      $file_db->query($insert);
    }
    $file_db->commit();
    //echo "Templomok feltöltve<br>\n";
 
    
    // Loop thru all messages and execute prepared insert statement
    $file_db->beginTransaction();
    if(is_array($misek))
    foreach ($misek as $mise) {
      set_time_limit(60);
      // Set values to bound variables
      $mid = $mise['id'];
      $tid = $mise['tid'];

      if($v < 4) {
        $tmp = $mise['idoszamitas'];
        if(preg_match('/^(t$|tél)/i',$tmp)) $telnyar = 't';
        elseif(preg_match('/^(ny$|nyár)/i',$tmp)) $telnyar = 'ny';
        else $telnyar = false;
      } 
      
      $nap = $mise['nap'];
      $ido = $mise['ido'];
      $nyelv = $mise['nyelv'];
      $milyen = $mise['milyen'];
      $megjegyzes = $mise['megjegyzes'];

      if($v > 3) { 
        $mise['tmp_datumtol'] = preg_replace('/-/i', '', $mise['tmp_datumtol']);
        $mise['tmp_datumig'] = preg_replace('/-/i', '', $mise['tmp_datumig']);


        $insert = "INSERT INTO misek (mid, tid, periodus, idoszak, suly, datumtol, datumig, nap, ido, nyelv, milyen, megjegyzes) 
            VALUES ('".$mid."','".$tid."','".$mise['nap2']."','".$mise['idoszamitas']."','".$mise['weight']."','".$mise['tmp_datumtol']."','".$mise['tmp_datumig']."','".$nap."','".$ido."','".$nyelv."','".$milyen."','".$megjegyzes."')";
      } elseif($v > 2) { 
             if($telnyar != false) {
                $insert = "INSERT INTO misek (mid, tid, telnyar, nap, ido, nyelv, milyen, megjegyzes) 
                    VALUES ('".$mid."','".$tid."','".$telnyar."','".$nap."','".$ido."','".$nyelv."','".$milyen."','".$megjegyzes."')";
             } elseif($mise['idoszamitas'] == 'egész évben') {
                $insert = "INSERT INTO misek (mid, tid, telnyar, nap, ido, nyelv, milyen, megjegyzes) 
                    VALUES ('".$mid."','".$tid."','t','".$nap."','".$ido."','".$nyelv."','".$milyen."','".$megjegyzes."')";
                $file_db->query($insert);
                $insert = "INSERT INTO misek (mid, tid, telnyar, nap, ido, nyelv, milyen, megjegyzes) 
                    VALUES ('".($mid + 1000000)."','".$tid."','ny','".$nap."','".$ido."','".$nyelv."','".$milyen."','".$megjegyzes."')";
             } else $insert = '';
      } else {
             if($telnyar != false) {
                $insert = "INSERT INTO misek (mid, tid, telnyar, nap, ido, nyelv, milyen) 
                    VALUES ('".$mid."','".$tid."','".$telnyar."','".$nap."','".$ido."','".$nyelv."','".$milyen."')";
             } elseif($mise['idoszamitas'] == 'egész évben') {
                $insert = "INSERT INTO misek (mid, tid, telnyar, nap, ido, nyelv, milyen) 
                    VALUES ('".$mid."','".$tid."','t','".$nap."','".$ido."','".$nyelv."','".$milyen."')";
                $file_db->query($insert);
                $insert = "INSERT INTO misek (mid, tid, telnyar, nap, ido, nyelv, milyen) 
                    VALUES ('".($mid + 1000000)."','".$tid."','ny','".$nap."','".$ido."','".$nyelv."','".$milyen."')";
             } else $insert = '';
      // Execute statement
      }
      if($insert !='') $file_db->query($insert);
    }
    $file_db->commit();
    //echo "Misék feltöltve<br>\n";

    // Loop thru all messages and execute prepared insert statement
    if($v > 1) {
    $file_db->beginTransaction();
    //$c = 1565465465465;
    if(is_array($kepek)) 
    foreach ($kepek as $kep) {
      set_time_limit(60);
      // Set values to bound variables
      $kid = $kep['id']; //Nem megy, mert nem unique.
      $tid = $kep['tid'];
      $url = "http://miserend.hu/kepek/templomok/".$kep['tid']."/".$kep['fajlnev'];
      $file = "kepek/templomok/".$kep['tid']."/".$kep['fajlnev'];
      $insert = "INSERT INTO kepek (kid, tid, kep) 
                VALUES ('".$kid."','".$tid."','".$url."')";
      // Execute statement
      if(file_exists($file))
        $file_db->query($insert);
        //else echo $kid." - ".$tid." - /".$kep['tid']."/".$kep['fajlnev'].";<br/>";
    } 
    $file_db->commit();
    //echo "Képek feltöltve<br>\n";
    }
 
    /**************************************
    * Close db connections                *
    **************************************/
 
    // Close file db connection
    $file_db = null;

    return true;

  }
  catch(PDOException $e) {
    // Print PDOException message
    echo $e->getMessage();
    return false;
  }
}

function upload2ftp($ftp_server,$ftp_user_name,$ftp_user_pass,$destination_file,$source_file) {
    // set up basic connection
    $conn_id = ftp_connect($ftp_server); 

    // login with username and password
    $login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass); 

    // check connection
    if ((!$conn_id) || (!$login_result)) { 
        echo "FTP connection has failed!";
        echo "Attempted to connect to $ftp_server for user $ftp_user_name"; 
        return false;
        exit; 
    } else {
        //echo "Connected to $ftp_server, for user $ftp_user_name";
    }

    // upload the file
    $upload = ftp_put($conn_id, $destination_file, $source_file, FTP_BINARY); 

    // check upload status
    if (!$upload) { 
        echo "FTP upload has failed!";
        return false;
    } else {
        return true;
        //echo "Uploaded $source_file to $ftp_server as $destination_file";
    }

    // close the FTP stream 
    ftp_close($conn_id); 
}

function getImages($tid) {
    if(!is_numeric($tid)) return false;

    $query="select * from kepek where tid='$tid' order by sorszam";
    $lekerdez=mysql_query($query);
    $mennyi=mysql_num_rows($lekerdez);

    $return = array();

    if($mennyi>0) {
        $images = array();
        while($row=mysql_fetch_assoc($lekerdez)) {
            $images[] = $row;
        }

        $dir="kepek/templomok/";

        foreach ($images as  $key => $image) {
            $images[$key]['url'] = $dir.$tid."/".$image['fajlnev'];
        }

        //kiemelt
        foreach($images as $image) {
            if($image['width'] > 0 AND $image['kiemelt'] == 'i' AND ($image['height'] / $image['width']) > 1 AND !isset($kiemelt)) {
                $kiemelt = $image;
            }
        }
        if(!isset($kiemelt)) {
            foreach($images as $image) {
                if($image['kiemelt'] == 'i' AND !isset($kiemelt)) {
                    $kiemelt = $image;
                }
            }
        }
        if(!isset($kiemelt)) {
            foreach($images as $image) {
                if(!isset($kiemelt)) {
                    $kiemelt = $image;
                }
            }
        }
        $return[0] = $kiemelt;

        foreach ($images as  $image) {
            if($return[0] != $image)
                $return[] = $image;
        }        
        return $return;

    } else
        return array();


}

function updateImageSizes() {
    global $config;

    $query = 'SELECT * FROM kepek WHERE width IS NULL OR height IS NULL OR width = 0 OR height = 0 ';
    $result = mysql_query($query);    
    while(($kep = mysql_fetch_array($result))) {

      $file = "kepek/templomok/".$kep['tid']."/".$kep['fajlnev'];

      if(file_exists($file)) {
        if(preg_match('/(jpg|jpeg)$/i',$file)) {
              $src_img = @ImagecreateFromJpeg($file);
              $kep['height'] = @imagesy($src_img);  # original height
              $kep['width'] = @imagesx($src_img);  # original width

              if($kep['height'] != '' AND $kep['width'] != '') {
                  $query =  "UPDATE kepek SET height = '".$kep['height']."', width = '".$kep['width']."' WHERE id = ".$kep['id']." LIMIT 1";
                  if($config['debug'] > 0) echo $query."<br>";
                  mysql_query($query);
              }
        } else {
          if($config['debug'] > 0) echo "A kép nem jpg: ".$file."<br>";
        }
      } else {
        if($config['debug'] > 0) echo "Hiányzó kép: ".$file."<br>";
      }

    }
}

function updateGorogkatolizalas() {
    global $config;
    // görög katolikus -> görögkatolikus
    $tables = array(
        'templomok' => array('nev','ismertnev','megjegyzes','misemegj','leiras','megkozelites'),
        'misek' => array('megjegyzes'),
        'kepek' => array('felirat')
        );
    $c = 0;
    foreach ($tables as $table => $fields) {
        foreach ($fields as $key => $field) {
            $query = "SELECT id,".$field." from ".$table." WHERE ".$field." LIKE '%görög katolikus%' ";
            $result = mysql_query($query);    
            while(($row = mysql_fetch_array($result))) {
                $text = preg_replace('/(görög) katolikus/i', '$1katolikus',$row[$field]);                
                $query = "UPDATE ".$table." SET ".$field." = '".$text."' WHERE id = ".$row['id']." LIMIT 1;";
                mysql_query($query);
                $c++;
            }
        }
    }
    if($config['debug'] > 0) echo $c." db görögkatolizálás<br/>";
}

function updateDeleteZeroMass() {
    global $config;

    // Ha csak 00:00:00-k vannak, akkor töröljük azokat is ianktiváljuk a misét
    $query = "SELECT count(misek.id) as misek ,SUM(if(ido = '00:00:00', 1, 0)) AS nullak, tid, misek.id,misek.megjegyzes,templomok.misemegj FROM misek LEFT JOIN templomok ON tid = templomok.id GROUP BY tid;";
    $result = mysql_query($query);    
    $c = 0;
    while(($tmp = mysql_fetch_array($result))) {
        if($tmp['nullak'] == 1 AND $tmp['misek'] == 1) {
            $c ++;
            if($tmp['megjegyzes'] != '' AND $tmp['misemegj'] == '') {
                //echo $tmp['tid'].": ".$tmp['megjegyzes']." -::-".$tmp['misemegj']."<br/>";
                $query = "UPDATE templomok SET misemegj = '".$tmp['megjegyzes']."' WHERE id = ".$tmp['tid']." LIMIT 1";
                //echo $query."<br/>";
                mysql_query($query);
            }
            $query = "UPDATE templomok SET  miseaktiv = 0 WHERE id = ".$tmp['tid']." LIMIT 1";
            //echo $query."<br/>";
            mysql_query($query);

            $query = "DELETE FROM misek WHERE id = ".$tmp['id']." LIMIT 1;";
            if($config['debug'] > 1) echo $query."<br/>";
            mysql_query($query);
        }
    }
    if($config['debug'] > 0) echo $c." db csak nullák eltávolítva<br/>";
}

function updateCleanMassLanguages() {
    global $config;
    // magyarországi templomok nyelve
    $query = " UPDATE misek LEFT JOIN templomok on misek.tid = templomok.id SET nyelv = NULL  WHERE ( nyelv = 'h0' OR nyelv = 'h') AND templomok.orszag = 12;";
    mysql_query($query);
    if($config['debug'] > 0) echo "Magyarországi templomokban alapértelmezetten magyarul misézünk<br/>";
}

function updateAttributesOptimalization() {
    global $config;
    //milyen/nyelv optimalizálás (!minden misén átmegy!)
    $c = 0;
    $query = "SELECT * from misek WHERE milyen <> '' OR nyelv <> '' ";
    $result = mysql_query($query);    
    while(($row = mysql_fetch_array($result))) {
      $query = "UPDATE misek SET milyen = '".cleanMassAttr($row['milyen'])."', nyelv = '".cleanMassAttr($row['nyelv'])."' WHERE id = ".$row['id']." LIMIT 1";
      //echo $query."<br/>";
      mysql_query($query);
      $c++;
    } 
    if($config['debug'] > 0) echo $c." db milyen/nyelv optimalizálva<br/>";
}

function updateComments2Attributes() {
    global $config;
    //megjegyzés -> tulajdonság
    $c = 0;
    foreach ($attributes as $abbrev => $attribute) {
        $query = "SELECT * from misek WHERE megjegyzes REGEXP '^".$attribute['name']."$' ";
        $result = mysql_query($query);    
        while(($row = mysql_fetch_array($result))) {
            if(!preg_match('/(^|,)'.$abbrev.'($|,)/i',$row['milyen'])) $milyen = $abbrev.",".$row['milyen'];
            else $milyen = $abbrev.",".$row['milyen'];
            $query = "UPDATE misek SET megjegyzes = '', milyen = '".$milyen."' WHERE id = ".$row['id']." LIMIT 1";
            //echo $query."<br/>";
            mysql_query($query);
            $c++;
        }   
    }
    if($config['debug'] > 0) echo $c." db megjegyzés tulajdonsággá alakítva<br/>";
}

function updateDistances($tid = false,$limit = false) {
    global $config;

    if(!is_numeric($limit) or $limit == false) $limit = 120;

    $query = "
        SELECT count(*) as sum FROM templomok 
            LEFT JOIN terkep_geocode as geo ON geo.tid = id
        WHERE ok = 'i' 
            AND geo.lat <> '' AND geo.lng <> ''
    ";
    $result = mysql_query($query);    
    $sum = mysql_fetch_assoc($result);
    $sum = $sum['sum'];

    $query = "
        SELECT id,geo.lat,geo.lng FROM templomok  
            LEFT JOIN terkep_geocode as geo ON geo.tid = templomok.id 
            WHERE geo.lat <> '' AND geo.lng <> '' 
    ";
    if($tid != false) $query .= "AND id = ".$tid." ";
    $query .= "  ORDER BY moddatum DESC ";
    if($tid != false) $query .= " LIMIT 1;";
    $result = mysql_query($query);    
    $c = 0;
    while(($church = mysql_fetch_assoc($result))) {
        set_time_limit('600');

        // we'll want everything within, say, 10km distance
        $distance = 10;

        // earth's radius in km = ~6371
        $radius = 6371;

        // latitude boundaries
        $maxlat = $church['lat'] + rad2deg($distance / $radius);
        $minlat = $church['lat'] - rad2deg($distance / $radius);

        // longitude boundaries (longitude gets smaller when latitude increases)
        $maxlng = $church['lng'] + rad2deg($distance / $radius / cos(deg2rad($church['lat'])));
        $minlng = $church['lng'] - rad2deg($distance / $radius / cos(deg2rad($church['lat'])));

        $query = "
            SELECT t.id,geo.lat,geo.lng,(ABS(geo.lat - ".$church['lat'].") + ABS(geo.lng - ".$church['lng'].")) diff,(IFNULL(d1.up,0) + IFNULL(d2.up,0)) toupdate
            FROM templomok AS t
                LEFT JOIN terkep_geocode as geo ON geo.tid = t.id 
                LEFT JOIN ( SELECT tid1,distance,toupdate as up FROM distance WHERE tid2 = ".$church['id']." ) AS d1 ON d1.tid1 = t.id
                LEFT JOIN ( SELECT tid2,distance,toupdate as up FROM distance WHERE tid1 = ".$church['id']." ) AS d2 ON d2.tid2 = t.id
                WHERE geo.lng <> '' AND geo.lat <> ''
                    AND t.ok = 'i'
                    AND ( tid1 IS NULL OR d1.up IS NOT NULL ) AND ( tid2 IS NULL OR d2.up IS NOT NULL) 
                    AND id <> ".$church['id']."
                    AND ( 
                        ( lat BETWEEN ".$minlat." AND ".$maxlat." AND lng BETWEEN ".$minlng." AND ".$maxlng." ) 
                         OR d1.up IS NOT NULL  OR d2.up IS NOT NULL 
                        ) 
            ORDER by diff, t.id ";

        if($tid == false) $query .= ' LIMIT 15';
        else $query .= " LIMIT ".$limit;
        
        $result2 = mysql_query($query);  
        while(($other = mysql_fetch_assoc($result2))) {
                $c++;
                if($c > $limit) return;
                if($other['id'] < $church['id']) { $tid1 = $other['id']; $tid2 = $church['id']; }
                else  { $tid1 = $church['id']; $tid2 = $other['id']; }
                $d = Distance($church,$other);
                if($d < ($distance * 1000) AND $d > 0) {
                    $dist = mapquestDistance($church,$other);
                    if($dist == -2) return;
                    elseif($dist > 0 ) $d = $dist;
                }                
                if($other['toupdate'] == 0 )
                    $query = "INSERT INTO distance (tid1,tid2,distance,updated,toupdate) VALUES (".$tid1.",".$tid2.",'".$d."','".date('Y-m-d H:i:s')."',NULL);";
                else 
                    $query = "UPDATE distance SET distance = '".$d."', updated = '".date('Y-m-d H:i:s')."', toupdate = NULL WHERE tid1 = ".$tid1." AND tid2 = ".$tid2." LIMIT 1";
                mysql_query($query);
                //echo $query."<br/>";
        }
        if($c == $limit) return;
    }
}

function Distance($templom,$szomszed) {

    if(!isset($templom['lng']) AND isset($templom['lon']))  $templom['lng'] = $templom['lon'];
    if(!isset($szomszed['lng']) AND isset($szomszed['lon']))  $szomszed['lng'] = $szomszed['lon'];

    $lat1 = $templom['lat'] * M_PI / 180;
    $lat2 = $szomszed['lat'] * M_PI / 180;
    $long1 = $templom['lng'] * M_PI / 180;
    $long2 = $szomszed['lng'] * M_PI / 180;

    if($lat1 == $lat2 AND $long1 == $long2) return 0;

    $R = 6371; // km
    $d = $R * acos(sin($lat1) * sin($lat2) + cos($lat1) * cos($lat2) * cos($long2 - $long1)) * 1000;
    return $d;
}

function getOverpass($query) {
    $obj = false;
    $query = urlencode($query);
    $dir = 'fajlok/tmp/';
    if(file_exists($dir."osm_".md5($query))) {
        $json = file_get_contents($dir."osm_".md5($query)); 
        //echo "cache<br/>";
    } else {        
        $json = file_get_contents("http://overpass-api.de/api/interpreter?data=".$query);
        //$json = file_get_contents("http://overpass.osm.rambler.ru/cgi/interpreter?data=".$query);
        file_put_contents($dir."osm_".md5($query), $json);         
        //echo $query."<br/>";
    }
    $obj = json_decode($json);
    return $obj;
}

function getOSMelement($type,$id) {
   $json = file_get_contents("http://overpass-api.de/api/interpreter?data=%5Bout%3Ajson%5D%5Btimeout%3A5%5D%3B%0A%28%0A%20%20".$type."%28".$id."%29%3B%0A%29%3B%0Aout%20body%20qt%20center%3B");
   $obj = json_decode($json);
   $element = $obj->elements[0];
   if(isset($element->center->lat)) $element->lat = $element->center->lat; 
   if(isset($element->center->lon)) $element->lon = $element->center->lon;  
   return json_decode(json_encode($element), true);
}

function deleteOverpass() {
    $start = microtime();
    //updateOSM();
    $files = scandir('fajlok/tmp');
    /* */
    foreach($files as $file)
        if(preg_match('/^osm_/i',$file))
            unlink('fajlok/tmp/'.$file);
    /* * /

    $query = "SELECT t.id, g.* FROM templomok t 
                LEFT JOIN osm ON osm.tid = t.id 
                LEFT JOIN terkep_geocode as g ON g.tid = t.id 
                WHERE osm.type IS NULL and ok = 'i' 
                ORDER BY t.id";
    //$query .= " LIMIT 15";
    $result = mysql_query($query);

    while ($row = mysql_fetch_assoc($result)) {
        set_time_limit(600);
        $around = 60;
        $around2 = 3000;
        $lon = $row['lng'];
        $lat = $row['lat'];
        $query = '[out:json][timeout:6]; ( node["amenity"="place_of_worship"](around:'.$around2.','.$lat.','.$lon.'); way["amenity"="place_of_worship"](around:'.$around2.','.$lat.','.$lon.'); rel["amenity"="place_of_worship"](around:'.$around2.','.$lat.','.$lon.'); ); out body center qt 6;';        
        $obj = getOverpass($query);
        if(count((array)$obj->elements) > 5) {
            $query = '[out:json][timeout:6]; ( node["amenity"="place_of_worship"](around:'.$around.','.$lat.','.$lon.'); way["amenity"="place_of_worship"](around:'.$around.','.$lat.','.$lon.'); rel["amenity"="place_of_worship"](around:'.$around.','.$lat.','.$lon.'); ); out body center qt;';
            getOverpass($query);
        }
    }
    /* */
    echo (  $start - microtime() )."s ";
    return true;

}

function updateOverpass($limit = false) {
    $start = microtime();
    //updateOSM();
    $files = scandir('fajlok/tmp');
    /* * /
    foreach($files as $file)
        if(preg_match('/^osm_/i',$file))
            unlink('fajlok/tmp/'.$file);
    /* */

    $query = "SELECT t.id, g.* FROM templomok t 
                LEFT JOIN osm ON osm.tid = t.id 
                LEFT JOIN terkep_geocode as g ON g.tid = t.id 
                WHERE osm.type IS NULL and ok = 'i' 
                ORDER BY t.id";
     
    $result = mysql_query($query);
    $c = 0;
    while ($row = mysql_fetch_assoc($result)) {
        set_time_limit(600);
        $around = 60;
        $around2 = 3000;
        $lon = $row['lng'];
        $lat = $row['lat'];
        $query = '[out:json][timeout:6]; ( node["amenity"="place_of_worship"](around:'.$around2.','.$lat.','.$lon.'); way["amenity"="place_of_worship"](around:'.$around2.','.$lat.','.$lon.'); rel["amenity"="place_of_worship"](around:'.$around2.','.$lat.','.$lon.'); ); out body center qt 6;';        
        $obj = getOverpass($query);
        if(count((array)$obj->elements) > 5) {
            $query = '[out:json][timeout:6]; ( node["amenity"="place_of_worship"](around:'.$around.','.$lat.','.$lon.'); way["amenity"="place_of_worship"](around:'.$around.','.$lat.','.$lon.'); rel["amenity"="place_of_worship"](around:'.$around.','.$lat.','.$lon.'); ); out body center qt;';
            getOverpass($query);
        }
        $c++;
        if(is_numeric($limit) AND $limit > 0 AND $limit >= $c)
            break;
    }
    /* */
    echo (  $start - microtime() )."s ";
    return true;

}

function updateOSM() {
    $json = file_get_contents("http://overpass-api.de/api/interpreter?data=%5Bout%3Ajson%5D%5Btimeout%3A25%5D%3B%0A%28%0A%20%20node%5B%22url%3Amiserend%22%5D%3B%0A%20%20way%5B%22url%3Amiserend%22%5D%3B%0A%20%20relation%5B%22url%3Amiserend%22%5D%3B%0A%29%3B%0Aout%20body%20qt%20center%3B");
    //file_put_contents("json.tmp", $json); 
    //$json = file_get_contents("json.tmp");   
    $obj = json_decode($json);
    if(count((array)$obj->elements) > 0) {

        mysql_query("TRUNCATE osm;");
        mysql_query("TRUNCATE osm_tags;");

        foreach ($obj->elements as $key => $item) {
            if(count((array)$item->tags) > 0) {
                if(preg_match("/=([0-9]{0,6})$/i",$item->tags->{'url:miserend'},$match)) {
                    $tid = $match[1];                    
                    mysql_query("INSERT INTO osm (tid,id,type) VALUES (".$tid.",".$item->id.",'".$item->type."');");
                    unset($item->tags->{'url:miserend'});
                    if(isset($item->lon)) $item->tags->lon = $item->lon;
                    if(isset($item->lat)) $item->tags->lat = $item->lat;
                    if(isset($item->center->lon)) $item->tags->lon = $item->center->lon;
                    if(isset($item->center->lat)) $item->tags->lat = $item->center->lat;

                    foreach ($item->tags as $name => $value) {
                        mysql_query("INSERT INTO osm_tags (`type`,`id`,`name`,`value`) VALUES ('".$item->type."','".(int) $item->id."','".$name."','".$value."');");
                    }
                    //echo $c ." ".$tid.": <a href='http://openstreetmap.org/".$item->type."/".$item->id."'>".$item->type."/".$item->id."</a> tags:".count((array)$item->tags)." @".$item->lat."@".$item->lon."<br/>";
                }
            }
        }

    }

    return true;
}

function str_putcsv($input, $delimiter = ',', $enclosure = '"') {
  // Open a memory "file" for read/write...
  $fp = fopen('php://temp', 'r+');
  // ... write the $input array to the "file" using fputcsv()...
  fputcsv($fp, $input, $delimiter, $enclosure);
  // ... rewind the "file" so we can read what we just wrote...
  rewind($fp);
  // ... read the entire line into a variable...
  $data = fread($fp, 1048576); // [changed]
  // ... close the "file"...
  fclose($fp);
  // ... and return the $data to the caller, with the trailing newline from fgets() removed.
  return rtrim( $data, "\n" );
}

function assignUpdates() {
    global $config;

    $limit = 7;

    $numbers = array('nulla','egy','kettő','három','négy','öt','hat','hét','nyolc','kilenc','tiz');


    //users
    $query = "
        SELECT user.uid,login,email,becenev,nev,c FROM user 
        LEFT JOIN (
            SELECT count(*) as c, uid FROM updates
            WHERE timestamp > '".date('Y-m-d H:i:s',strtotime("-160 hours"))."' 
            GROUP BY uid 
            ORDER BY timestamp DESC
        ) u ON u.uid = user.uid 
        WHERE volunteer = 1 AND (c < ".$limit." OR c IS NULL)
    ;";
    $result = mysql_query($query); $users = array();
    while ($user = mysql_fetch_assoc($result)) {
        $users[$user['uid']] = $user;    
    }
    $cUsers = mysql_num_rows($result);
    
    //templomok
    $query = "
        SELECT t.id,t.nev,t.ismertnev,t.varos,t.nev,t.frissites,u.uid, u.timestamp 
        FROM templomok  t
            LEFT JOIN (
                SELECT * FROM updates
                WHERE timestamp > '".date('Y-m-d',strtotime("-2 months"))."' 
                ORDER BY timestamp DESC
            ) u ON u.tid = t.id  
            LEFT JOIN (
                SELECT * FROM eszrevetelek
                WHERE allapot = 'u' or allapot = 'f' 
                GROUP BY hol_id
                ORDER BY datum
            ) e ON e.hol_id = t.id
        WHERE 
            ok = 'i' 
            AND orszag = 12
            AND ( t.nev LIKE '%templom%' OR t.nev LIKE '%bazilika%' OR t.nev LIKE '%székesegyház%')
            AND frissites < '".date('Y-m-d',strtotime("-2 years"))."' 
            AND u.timestamp IS NULL
            AND e.allapot IS NULL                
        GROUP BY t.id
        ORDER BY frissites, t.id ";
    //$query .= " LIMIT ".( $limit * $cUsers );

    $result = mysql_query($query); $templomok = array();
    while ($templom = mysql_fetch_assoc($result)) {
        $templomokFull[$templom['id']] = $templom;    
    }
    $cKioszthato = mysql_num_rows($result);
    //echo "Kiosztható: ".$cKioszthato;

    //echo $cUsers * $limit;
    if( ($cUsers * $limit) > $cKioszthato) {
        $mail = new Mail();
        $mail->subject = "Miserend.hu - Önkéntes FIGYELMEZTETÉS!";
        $mail->content = "Itt a vége?\n\n".$cUsers." önkéntesünk van. Nekik kéne kiosztani ".( $cUsers * $limit )." templomot, de csak ".$cKioszthato." templom van a raktáron.";
        if($cKioszthato > 0 ) {
            $limit = ceil($cKioszthato / $cUsers);
            $mail->content .= "\nÚgy határoztunk hát, hogy csak ".$limit." templomot osztunk ki fejenként.";
        }
        $mail->Send($config['mail']['debugger']);
    }

    //változók a levélhez
    $query = "
        SELECT count(*),t.nev FROM eszrevetelek
                RIGHT JOIN templomok t ON t.id = eszrevetelek.hol_id
            WHERE datum > '".date('Y-m-d H:i:s',strtotime("-1 week"))."' 
                AND ok = 'i' 
                AND orszag = 12
                AND ( t.nev LIKE '%templom%' OR t.nev LIKE '%bazilika%' OR t.nev LIKE '%székesegyház%')
            GROUP BY hol_id
    ;";
    $result = mysql_query($query);
    $M = mysql_num_rows($result);


    $query = "
        SELECT count(*) FROM templomok t
            WHERE frissites > '".date('Y-m-d',strtotime("-6 months"))."' 
                AND ok = 'i' 
                AND orszag = 12
                AND ( t.nev LIKE '%templom%' OR t.nev LIKE '%bazilika%' OR t.nev LIKE '%székesegyház%')
    ;";
    $result = mysql_query($query);
    $tmp = mysql_fetch_row($result);
    $L = $tmp[0];

    $query = "
        SELECT count(*) FROM templomok t
            WHERE frissites < '".date('Y-m-d',strtotime("-2 years"))."' 
                AND ok = 'i' 
                AND orszag = 12
                AND ( t.nev LIKE '%templom%' OR t.nev LIKE '%bazilika%' OR t.nev LIKE '%székesegyház%')
    ;";
    $result = mysql_query($query);
    $tmp = mysql_fetch_row($result);
    $O = $tmp[0];



    //minden felhasználó egyesével
    $c = 0;
    foreach ($users as $uid => $user) {

        $templomok = array_slice($templomokFull, $c * $limit , $limit , true);
        $c++;

        $list = "<ul>";
        foreach ($templomok as $tid => $templom) {
            $query = "INSERT INTO updates (uid,tid) VALUES (".$uid.",".$tid.");";
            if($config['debug'] < 1) mysql_query($query);
            else echo $query."\n<br/>";
            $list .= "<li><a href='http://miserend.hu/?templom=".$templom['id']."'>".$templom['nev']."</a>";
            if($templom['ismertnev'] != '') $list .= " (".$templom['ismertnev'].")";
            $list .= ", ".$templom['varos'];
            $list .= " <font size='-1'>- utolsó frissítés: ".preg_replace('/-/', '. ', $templom['frissites']).".</font>";
            $list .="</li>";
        }
        $list .= "</ul>";

        //változók a levélhez
        
        if($user['becenev']!='') $nev = $user['becenev'];
        elseif($user['nev']!='') $nev = $user['nev'];
        else $nev = $user['login'];
        
        $query = "
            SELECT count(*),t.nev FROM eszrevetelek e
                    RIGHT JOIN templomok t ON t.id = e.hol_id
                WHERE datum > '".date('Y-m-d H:i:s',strtotime("-1 week"))."' 
                    AND e.login = '".$user['login']."' OR e.email = '".$user['email']."' 
                    AND ok = 'i' 
                    AND orszag = 12
                    AND ( t.nev LIKE '%templom%' OR t.nev LIKE '%bazilika%' OR t.nev LIKE '%székesegyház%')
                GROUP BY hol_id
        ;";
        $result = mysql_query($query);
        $N = mysql_num_rows($result);


        if($O > $L ) $ol = "de még";
        else $ol = "és már csak";

        $mail = new Mail();

        $mail->subject = "Miserend frissítése, ".date('W').". hét";
        $text = "
            <strong>Kedves $nev!</strong>\n
            <p>A <a href='http://miserend.hu'>miserend.hu</a>-n a múlt héten $M magyarországi templomhoz kaptunk észrevételt. ";
        if($N == 0) $text .= "Reméljük, a héten te is tudsz küldeni helyesbítést.";
        elseif($N * 5 < $M) $text .= "Te $N észrevételt küldtél be. És pont az ilyen sok kicsi ment ilyen sokra. ";
        else $text .= "Ebből $N templomhoz te küldtél be helyesbítést. Nagyon köszönjük! ";
        $text .= "
            Összesen már $L templomnak vannak fél évnél frissebb adatai, $ol $O nagyon régen frissített magyarországi templom van az adatbázisunkban.</p>\n
            <p>A következő héten a következő ".$numbers[count($templomok)]." templom miserendjének frissítésében kérjük a te segítségedet:\n
            ".$list;
            
        $text .= <<<EOT
            <p>Amire érdemes figyelni információ kereséskor:</p>
            <ul>
                <li>Nem csak azktuális miserendre szükséges rákérdezni, hanem minden más időszak miserendjére is. Pl. téli/nyári miserend, adventi idő, hétköznapra eső ünnepek. (Bármilyen egyéb időszak is felvihető a rendszerünkbe.)</li>
                <li>Fontos megtudni, hogy mikor van a téli/nyári időszak határa (és minden más időszak határa). A tanévvel van összehangolva? Vagy a napfordulóval? Esetleg egy konkrét ünneppel?</li>
                <li>A legbiztosabb információt közvetlen az atyától, sekrestyéstől vagy titkártól lehet kapni. A plébániai honlapok nagyon sokszor teljesen elavultak és amúgy is csak az aktuális miserendet tartalmazzák.</li>
                <li>Ha a plébániához nincs megfelelő elérhetőség, akkor az egyházmegyei honlapot ill. annak használhatatlansága esetén az egyházmegyei titkárságot érdemes megkeresni. Ha sikerül élő elérhetőséget szerezni a plébániához, akkor azt is küldjük be a miseadatokkal. (Személyes mobilszámokat csak akkor adjunk meg, ha a tulajdonos hozzájárult, hogy megjelenjen a honlapon.)</li>
                <li>Egy-egy plébániához/paphoz általában több templom is tartozik. Ha már sikerült felvenni egy illetékessel a kapcsolatot, akkor érdemes a fíliák és kapcsolódó templomok adatait is megtudni.</li>
                <li>Ha hiába régen volt már frissítés, mégis minden adat stimmel a honlapunkon, akkor is kérünk visszajelzést, hogy tudjuk, nem kell újra ellenőrizni.</li>
                <li><strong>A visszajelzéseket lehetőség szerint a templom oldalán az észrevétel beküldésénkeresztül kérjük feltölteni.</strong> Segít, ha be vagy jelentkezve, így tudjuk, hogy mebízható forrásból származik az információ. </li>
            </ul>

EOT;
        $text .= "<p><strong>Segítségedet nagyon köszönjük!</strong></p><p>&nbsp;&nbsp;A miserend.hu önkéntes csapata</p>\n
            <p><font size='-1'>Ezt a levelet azért kaptad, mert a <a href='http://misrend.hu'>miserend.hu</a> honlapon egyszer jelentkeztél önkéntes frissítőnék. Vállalásodat bármikor visszavonhatod a <a href='http://miserend.hu/?m_id=28&m_op=add'>személyes beállításadinál</a>, vagy írhatsz az <a href='mailto:eleklaszlosj@gmail.com'>eleklaszlosj@gmail.com</a> címre. Technikai segítség szintén az <a href='mailto:eleklaszlosj@gmail.com'>eleklaszlosj@gmail.com</a> címen kérhető.</font></p>
        ";
        $mail->type = "heti7templom_hetiadag";
        $mail->content = $text;
        $mail->Send($user['email']);        
        /* */
    }
}

function clearoutVolunteers() {
    $query = "
    SELECT user.uid,
        (IF (login.count IS NULL,0,login.count) + IF (email.count IS NULL,0,email.count)) as eszrevetelek,
        IF (updates.count IS NULL,0,updates.count) as updates
    FROM user 
    LEFT JOIN (
        SELECT count(*) as count,login 
        FROM eszrevetelek 
        WHERE datum > '".date('Y-m-d H:i:s',strtotime("-1 month"))."' 
        GROUP BY login
        ) login 
        ON login.login = user.login
    LEFT JOIN (
        SELECT count(*) as count,email 
        FROM eszrevetelek 
        WHERE datum > '".date('Y-m-d H:i:s',strtotime("-1 month"))."'
            AND login like '*vendeg*' GROUP BY email 
        ) email 
        ON email.email = user.email
    LEFT JOIN (
        SELECT count(*) as count,uid
        FROM updates 
        WHERE timestamp > '".date('Y-m-d H:i:s',strtotime("-1 month"))."'
        GROUP BY uid
        ) updates 
        ON updates.uid = user.uid    

    WHERE volunteer = 1;";

    $limit = 10; $c = 1;
    $result = mysql_query($query); $volunteer = array();
    while ($volunteer = mysql_fetch_assoc($result)) {
        if($volunteer['updates'] > 0 AND $volunteer['eszrevetelek'] == 0) {

            $user = new User($volunteer['uid']);
            if($user->nickname != '') $nev = $user->nickname;
            elseif($user->name != '') $nev = $user->name;
            else $nev = $user->username;


            $mail = new Mail();

            $mail->subject = "Miserend önkéntesség";

            $text = <<<EOD
            <strong>Kedves $nev!</strong>\n
            <p>Templomaink miserendjének frissentartása elképzelhetetlen lenne önkéntesek segítsége nélkül. Sajnáljuk, hogy az elmúlt hónapban nem állt módodban teljesíteni vállalásodat. Ezért, hogy aktív önkénteseinknek biztosan jusson elég frissítendő adat, feloldunk önkéntes vállalásod alól. A továbbiakban nem küldünk neked frissítendő templomokat emailben.</p>\n
            <p>Észrevételeidet, helyesbítéseidet továbbra is köszönettel várjuk a honlapon keresztül. Valamint, ha mégis tudod vállalni újra heti hét templom frissítését, a honlapon a <a href='http://miserend.hu/?m_id=28&m_op=add'>személyes beállításadinál</a> vállalásodat megteheted.</p>\n
            <p><strong>Köszönjük korábbi és majdani minden helyesbítésedet!</strong></p>
            <p>&nbsp;&nbsp;A miserend.hu önkéntes csapata</p>\n
EOD;
        $text .= "<p><font size='-1'>Ezt a levelet azért kaptad, mert a <a href='http://misrend.hu'>miserend.hu</a> honlapon egyszer jelentkeztél önkéntes frissítőnék. Vállalásodat bármikor módosíthatod a <a href='http://miserend.hu/?m_id=28&m_op=add'>személyes beállításadinál</a>, vagy írhatsz az <a href='mailto:eleklaszlosj@gmail.com'>eleklaszlosj@gmail.com</a> címre. Technikai segítség szintén az <a href='mailto:eleklaszlosj@gmail.com'>eleklaszlosj@gmail.com</a> címen kérhető.</font></p>
        ";
        $mail->content = $text;
        $mail->type = "heti7templom_lemondas";
        $mail->Send($user->email);        
        $user->presave('volunteer',0);
        $user->save();
        $c++;
        if($c > $limit) return true;
        }
    }
    return true;
}

function updatesCampaign() {
    global $twig, $design_url, $user;

         $query = "SELECT count(*) FROM user WHERE ok = 'i'  AND volunteer = 1;";
        $result = mysql_query($query);
        $tmp = mysql_fetch_row($result);
        $C = $tmp[0];

        $query = "
            SELECT count(*) FROM templomok t
                WHERE frissites < '".date('Y-m-d',strtotime("2015-12-24 -2 years"))."' 
                    AND ok = 'i' 
                    AND orszag = 12
                    AND ( t.nev LIKE '%templom%' OR t.nev LIKE '%bazilika%' OR t.nev LIKE '%székesegyház%')
        ;";
        $result = mysql_query($query);
        $tmp = mysql_fetch_row($result);
        $O = $tmp[0];

        $W = date('W',strtotime('2015-12-24')) - date('W');

        $S = (int) ( $O / $W / 7 ) + 1;

        if($O > $L ) $ol = "de még";
        else $ol = "és már csak";

    $dobozszoveg = "<span class='alap'>Alig $S önkéntes heti hét templom miserendjének frissítésével karácsonyra naprakésszé teheti az összes magyarországi templomot. <strong>Már $C ember segít nekünk";

    if($C >= $S ) $dobozszoveg .= ", de segítő kézre még szükségünk van. ";
    else $dobozszoveg .= ". ";
    if($user->volunteer == 1) $dobozszoveg .= "Köszönjük, hogy te is köztük vagy!";
    else $dobozszoveg .= "Jelentkezz te is: <a href='mailto:eleklaszlosj@gmail.com?subject=Önkéntesnek jelentkezem'>eleklaszlosj@gmail.com</a>!";
    
    $dobozszoveg .= "</strong></span>";
    
    $variables = array(
            'header' => array('content'=>'Hét nap, hét frissítés'),
            'content' => nl2br($dobozszoveg),
            'settings' => array('width=100%','align=center','style="padding:1px"'),
            'design_url' => $design_url);       
    //return $twig->render('doboz_lila.html',$variables); 

    if($C >= ( $S * 2 ) ) {
        return false;
    }

    return array(
        'title' => 'Hét nap, hét frissítése',
        'content' => nl2br($dobozszoveg)
        );
}


function clearoutMessages() {
    mysql_query("DELETE FROM messages WHERE timestamp < '".date('Y-m-d H:i:s',strtotime('-1 month'))."' OR shown = 1;");
}

function clearoutTokens() {
    mysql_query("DELETE FROM tokens WHERE timeout < '".date('Y-m-d H:i:s')."';");
}

function generateToken($forUserId) {
    global $config;    
    
    $inserted = false;
    for($i = 1;$i < 5; $i++) {
            $token = md5(uniqid(mt_rand(), true));
            mysql_query("INSERT INTO tokens (name,type,uid,timeout) VALUES ('".$token."','API',".$forUserId.",'".date('Y-m-d H:i:s',strtotime("+".$config['token']['timeout']))."');");
            if(mysql_affected_rows() == 1) {
                    $inserted = true;
                    break;
            }
    }
    if($inserted != true) {
           throw new Exception("We could not generate unique token.");
    } 
    return $token;
}

function validateToken($token) {
    $result = mysql_query("SELECT * FROM tokens WHERE name = '".sanitize($token)."' LIMIT 1");
    $row = mysql_fetch_assoc($result);

    if(!is_array($row)) {
        return false;
    } elseif($row['timeout'] < date('Y-m-d H:i:s')) {
        return false;
    } 
    //TODO: check also: type,uid

    extendToken($token);
    return $row;
}

function extendToken($token) {
    global $config;
    $query = "UPDATE tokens SET timeout = '".date('Y-m-d H:i:s',strtotime("+".$config['token']['timeout']))."' WHERE name = '".$token."' LIMIT 1;";
    if(mysql_query($query))
        return true;
    else
        return false;

}

function getInputJSON() {
    global $config;

    $inputJSON = file_get_contents('php://input');
    $input= json_decode( $inputJSON, TRUE ); //convert JSON into array
    if($config['debug'] > 0 AND is_array($input)) {
        $input = array_replace($input,$_REQUEST);
    }
    elseif($config['debug'] >0 ) $input = $_REQUEST;
    
    if(!is_array($input)) {
        throw new Exception("Nem érkezett megfelelő JSON input.");
    }
    
    return $input;
}

function feltoltes_block() {
    global $user;    

    $query="select id,nev,varos,eszrevetel from templomok where letrehozta='".sanitize($user->login)."' limit 0,5";
    $lekerdez=mysql_query($query);
    $mennyi=mysql_num_rows($lekerdez);
    
    if($mennyi>0) {
        $kod_tartalom = '<ul>';
        while(list($tid,$tnev,$tvaros,$teszrevetel)=mysql_fetch_row($lekerdez)) {
            if($teszrevetel=='i') $jelzes.="<a href=\"javascript:OpenScrollWindow('naplo.php?kod=templomok&id=$tid',550,500);\"><img src=img/csomag.gif title='Új észrevételt írtak hozzá!' align=absmiddle border=0></a> ";       
            elseif($teszrevetel=='f') $jelzes.="<a href=\"javascript:OpenScrollWindow('naplo.php?kod=templomok&id=$tid',550,500);\"><img src=img/csomagf.gif title='Észrevétel javítása folyamatban!' align=absmiddle border=0></a> ";                     
            else $jelzes='';
            
            $kod_tartalom.="\n<li>$jelzes<a href=?m_id=27&m_op=addtemplom&tid=$tid class=link_kek title='$tvaros'>$tnev</a></li>";
        }   

        $kod_tartalom.="\n<li><a href=?m_id=29 class=felsomenulink>Teljes lista...</a></li>";
        $kod_tartalom .= '</ul>';
    }

    return $kod_tartalom;

    if(!empty($kod_tartalom)) {
        //Tartalom létrehozása
        $kod_cim="<span class=hasabcimlink>Módosítás</span>";

        $kodT[0]=$kod_cim;
        $kodT[1]=$kod_tartalom;

        return $kodT;
    }
}

function formazo($adatT,$tipus) {
    global $design_url,$szin,$design;

    if(!isset($design)) $design='alap';

    if($tipus == 'doboz') return $adatT[2];
    
    $cim=$adatT[0];
    $cimlink=$adatT[1];
    $tartalom=$adatT[2];
    $tartalom2=$adatT[3]; //híreknél 2. hasáb
    $tovabb=$adatT[4]; //híreknél "cikk bővebben"
    $tovabblink=$adatT[5]; //általában a $cimlink
        
    $tmpl_file = $design_url.'/'.$tipus.'.htm';
    echo $tmpl_file;
    $thefile = implode("", file($tmpl_file));
    $thefile = addslashes($thefile);
    $thefile = "\$r_file=\"".$thefile."\";";
    eval($thefile);
    
    return $kod = $r_file;
}

function addMessage($text,$severity = false) {
    global $user;

    $query = "INSERT INTO messages (sid, timestamp, severity, text) VALUES ('".session_id()."','".date('Y-m-d H:i:s')."','".$severity."','".$text."');";

    if(mysql_query($query)) return true;
    return false;
}

function getMessages() {
    global $user;

    $return = array();
    $query = "SELECT id,timestamp,text,severity FROM messages WHERE shown = 0 AND sid = '".session_id()."' ;";
    $results = mysql_query($query);
    while($message = mysql_fetch_assoc($results)) {
        $return[] = $message;
        mysql_query("UPDATE messages SET shown = 1 WHERE id = ".$message['id']." LIMIT 1;");

    }
    if(mysql_query($query)) return $return;
    return false;
}


/*
    TODO: delete alapnyelv();
 */
function alapnyelv($text) {
    return $text;

}

function quit() {
    global $user;

    unset($_SESSION['auth']);
    unset($_COOKIE['auth']);
    setcookie('auth', null, time());
    //session_destroy();
    unset($user);
    $user = new User();

}


function chat_load() {
    
    $vars['comments'] = chat_getcomments();
    $vars['lastcomment'] = $vars['comments'][0]['datum_raw'];   
    $vars['users'] = chat_getusers('html');
    return $vars;
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

        $row['szoveg'] = preg_replace('@(https?://([-\w\.]+[-\w])+(:\d+)?(/([\w/_\.#-]*(\?\S+)?[^\.\s])?)?)@', '<a href="$1" target="_blank">$1</a>', $row['szoveg']);
        $row['szoveg'] = preg_replace('@>(https?://miserend\.hu/)@','>', $row['szoveg']);
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

function dieJsonException($message) {
    $error = ['error' => 1,
        'text' => $message];
    echo(json_encode($error));
}
function callPageFake($uri, $post, $phpinput = array()) {
    stream_wrapper_unregister("php");
    stream_wrapper_register("php", "MockPhpStream");

    file_put_contents('php://input', json_encode($phpinput));
    $_REQUEST = array_merge($_REQUEST, $post);

    ob_start();
    include $uri;
    $page = ob_get_contents();
    ob_end_clean();

    stream_wrapper_restore("php");

    return $page;
}

function sendJson($url,$content) {
    if(!preg_match('/^http:\/\//i',$url)) {
        global $config;
        $url = $config['path']['domain'].$url;
    }
   print_r($_REQUEST);
    $contentEncoded = json_encode($content);
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER,array("Content-type: application/json"));
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $contentEncoded);
    
    $response = curl_exec($curl);
    if(!$responseArray = json_decode($response, true)) {
        $responseArray = $response;
    } else 
    $responseArray['status'] = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    
    curl_close($curl);
    
    return $responseArray;
}

spl_autoload_register(function ($class) {

    $classpath = 'classes/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($classpath)) {
        require_once($classpath);
    }            
});

function env($name,$default=false) {    
    if(!getenv($name)) return $default;
    else return getenv($name);
}

function printr($variable) {

    echo"<pre>".print_r($variable,1)."</pre>";

}

?>
