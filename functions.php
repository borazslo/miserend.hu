<?php

use Illuminate\Database\Capsule\Manager as DB;

function dbconnect() {

    global $config;
    if (!@mysql_connect($config['connection']['host'], $config['connection']['user'], $config['connection']['password'])) {
        if ($config['debug'] > 0)
            die("Adatbázisszerverhez nem lehet csatlakozni!\n" . mysql_error());
        else
            die('Elnézést kérünk, a szolgáltatás jelenleg nem érhető el.');
    }
    mysql_query("SET NAMES UTF8");
    //mysql_query("SET CHARACTER SET 'UTF8'");

    if (!mysql_select_db($config['connection']['database'])) {
        if ($config['debug'] > 0)
            die("Az '" . $config['connection']['database'] . "' adatbázis nem létezik, vagy nincs megfelelő jogosultság elérni azt!\n" . mysql_error());
        else
            die('Elnézést kérünk, a szolgáltatás jelenleg nem érhető el.');
    };
    
    global $capsule;
    $capsule = new DB;
    $capsule->addConnection([
        'driver' => 'mysql',
        'host' => $config['connection']['host'],
        'database' => $config['connection']['database'],
        'username' => $config['connection']['user'],
        'password' => $config['connection']['password'],
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'prefix' => '',
            ], 'default');
    // Make this Capsule instance available globally via static methods... (optional)
    $capsule->setAsGlobal();
    $capsule->bootEloquent();
    DB::statement("SET time_zone='+05:00';");
}

function sanitize($text) {
    if (is_array($text))
        foreach ($text as $k => $i)
            $text[$k] = sanitize($i);
    else {
        $text = preg_replace('/\n/i', '<br/>', $text);
        $text = strip_tags($text, '<a><i><b><strong><br>');
        $text = trim($text);
    }
    return $text;
}

function checkUsername($username) {
    if ($username == '')
        return false;
    if ($username == '*vendeg*')
        return false;
    if (strlen($username) > 20)
        return false;
    if (preg_match("/( |\"|'|;)/i", $username))
        return false;

    //TODO: én ezt feloldanám
    if (!preg_match("/^([a-z0-9]{1,20})$/i", $username))
        return false;

    $checkeduser = new User($username);
    if ($checkeduser->uid > 0)
        return false;


    return true;
}

function mapquestGeocode($location) {
    global $config;
    $url = "http://www.mapquestapi.com/geocoding/v1/address?key=" . $config['mapquest']['appkey'];
    $url .= "&location=" . urlencode($location);
    $url .= "&outFormat=json&maxResults=1";

    $file = file_get_contents($url);
    $mapquest = json_decode($file, true);
    //print_r($mapquest);
    //echo "<a href='".$mapquest['results'][0]['locations'][0]['mapUrl']."'>map</a>";
    return array_merge($mapquest['results'][0]['locations'][0]['latLng'], array('mapUrl' => $mapquest['results'][0]['locations'][0]['mapUrl']));
}

function LirugicalDay($datum = false) {
    global $config;

    //TODO: ha nincs könyvár, attól még megpróbálhatná élesben lehozni.
    if (!is_dir('fajlok/igenaptar')) {
        //die('Sajnos nincsen faljok/igenaptar könyvtár. Ez komoly hiba.');
        return false;
    }

    if (empty($datum))
        $datum = date('Y-m-d');

    $file = 'fajlok/igenaptar/' . $datum . '.xml';
    if (file_exists($file) AND $config['debug'] == 0) {
        $xmlstr = file_get_contents($file);
    } else {
        $source = "http://breviar.kbs.sk/cgi-bin/l.cgi?qt=pxml&d=" . substr($datum, 8, 2) . "&m=" . substr($datum, 5, 2) . "&r=" . substr($datum, 0, 4) . "&j=hu";
        $ch = curl_init();
        $timeout = 1;
        curl_setopt($ch, CURLOPT_URL, $source);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $xmlstr = curl_exec($ch);
        curl_close($ch);
        if ($xmlstr) {
            @file_put_contents($file, $xmlstr);
        }
    }

    if ($xmlstr != '') {
        $xmlcont = @simplexml_load_string($xmlstr);
        if($xmlcont != '') {
            $xmlcont = new SimpleXMLElement($xmlstr);
            return $xmlcont->CalendarDay;    
        } else {
            return false;
        }
        
    } else
        return false;
}

function LiturgicalDayAlert($html = false, $date = false) {

    if ($date == false)
        $date = date('Y-m-d');
    $alert = false;
    $day = LirugicalDay($date);
    if ($day != false AND isset($day->Celebration)) {
        if ($day->Celebration->LiturgicalCelebrationLevel <= 4 AND date('N', strtotime($date)) != 7) {

            $text = "Ma van <strong>" . $day->Celebration->LiturgicalCelebrationName . "</strong>";
            if (preg_match("/ünnep$/i", $day->Celebration->LiturgicalCelebrationType))
                $text .= " " . $day->Celebration->LiturgicalCelebrationType . "e";

            if ($html == false) {
                return true;
            } else {
                global $twig;
                return $twig->render('alert_liturgicalday.html', array('text' => $text));
            }
        }
    }

    if ($html == false) {
        return false;
    } else {
        return '';
    }
}

function checkDateBetween($date, $start, $end) {
    global $config;
    if ($config['debug'] > 1)
        echo "Is " . $date . " between " . $start . " and " . $end . "? <br/>";

    $year = date('Y', strtotime($date));
    if (strtotime($year . "-" . $start) <= strtotime($year . "-" . $end)) {
        if (strtotime($year . "-" . $start) <= strtotime($date) AND strtotime($date) <= strtotime($year . "-" . $end))
            return true;
        else
            return false;
    } else {
        if (strtotime($year . "-" . $start) > strtotime($date) AND strtotime($date) > strtotime($year . "-" . $end))
            return false;
        else
            return true;
    }
}

function event2Date($event, $year = false) {    
    if ($year == false)
        $year = date('Y');

    if (preg_match('/^([0-9]{4})(\.|-)([0-9]{2})(\.|-)([0-9]{2})(\.|)/i', $event, $match))
        return $match[3] . "-" . $match[5];
    if (preg_match('/^([0-9]{2})(\.|-)([0-9]{2})(\.|)(( \-| \+)[0-9]{1,3}|$)/i', $event, $match)) {
        if ($match[5] != '') {
            $extra = $match[5] . " days";
        } else {
            $extra = false;
        }
        return date('m-d', strtotime(date('Y') . "-" . $match[1] . "-" . $match[3] . " " . $extra));
    }

    $event = preg_replace('/(\+|-)1$/', '${1}1 day', $event);
    $events = array();
    $query = "SELECT name, date FROM events WHERE year = '" . $year . "' ";
    $results = DB::table('events')
            ->select('name','date')
            ->where('year',$year)
            ->get();
    foreach($results as $row)  {
        $events['name'][] = '/^' . $row->name . '( (\+|-)([0-9]{1,3})|)( day|)$/i';
        $events['date'][] = $row->date . "$1$4";
    }
    $event = preg_replace($events['name'], $events['date'], $event);
    $event = preg_replace('/^([0-9]{2})(\.|-)([0-9]{2})/i', date('Y') . '$2$1$2$3', $event);
    $event = date('m-d', strtotime($event));
    return $event;
}

function getMasses($tid, $date = false) {
    if ($date == false OR $date == '')
        $date = date('Y-m-d');

    $napok = array('x', 'hétfő', 'kedd', 'szerda', 'csütörtök', 'péntek', 'szombat', 'vasárnap');
    $nap2options = array(
        0 => 'minden héten',
        1 => '1. héten', 2 => '2. héten', 3 => '3. héten', 4 => '4. héten', 5 => '5. héten',
        '-1' => 'utolsó héten',
        'ps' => 'páros héten', 'pt' => 'páratlan héten');

    $return = array();
    $query = "SELECT * FROM misek WHERE torles = '0000-00-00 00:00:00' AND tid = $tid GROUP BY idoszamitas ORDER BY weight DESC";
    $result = mysql_query($query);
    while (($row = mysql_fetch_array($result))) {
        $tmp = array();
        $tmp['nev'] = $row['idoszamitas'];
        $tmp['weight'] = $row['weight'];
        $tmp['tol'] = $row['tol'];
        $tmp['ig'] = $row['ig'];
        $tmp['datumtol'] = $datumtol = $row['tmp_datumtol']; //event2Date($row['tol']);
        $tmp['datumig'] = $datumig = $row['tmp_datumig']; //event2Date($row['ig']);

        if (checkDateBetween($date, $datumtol, $datumig))
            $tmp['now'] = true;

        for ($i = 1; $i < 8; $i++) {
            $tmp['napok'][$i]['nev'] = $napok[($i)];
        }
        //unset($tmp['napok'][1]);  $tmp['napok'][1]['nev'] = $napok[1];

        $query2 = "SELECT * FROM misek WHERE torles = '0000-00-00 00:00:00' AND tid = $tid AND idoszamitas = '" . $row['idoszamitas'] . "'  ORDER BY nap, ido";
        $result2 = mysql_query($query2);
        while (($row2 = mysql_fetch_array($result2, MYSQL_ASSOC))) {
            if ($row2['milyen'] != '') {
                $row2['attr'] = decodeMassAttr($row2['milyen']);
            } else
                $row2['attr'] = array();
            $row2['attr'] = array_merge($row2['attr'], decodeMassAttr($row2['nyelv']));

            $ido = (int) substr($row2['ido'], 0, 2);
            $row2['ido'] = $ido . ":" . substr($row2['ido'], 3, 2);
            $row2['nap2_raw'] = $row2['nap2'];
            if ($row2['nap2'] != '')
                $row2['nap2'] = '(' . $nap2options[$row2['nap2']] . ')';

            $row2['napid'] = $row2['nap'];
            $row2['nap'] = $napok[$row2['nap']];
            $tmp['napok'][$row2['napid']]['misek'][] = $row2;
            $tmp['napok'][$row2['napid']]['nev'] = $row2['nap'];
        }
        if ($tmp['tol'] == $tmp['ig'])
            $return['particulars'][] = $tmp;
        else
            $return['periods'][] = $tmp;
    }

    //order byweight

    if (isset($return['periods']) AND is_array($return['periods']))
        usort($return['periods'], "cmp");
    if (isset($return['particulars']) AND is_array($return['particulars'])) {
        usort($return['particulars'], "cmp");
    }


    return $return;
}

function cmp($a, $b) {
    return $a["weight"] - $b["weight"];
}

function decodeMassAttr($text) {
    $return = array();

    $milyen = array();
    $attributes = unserialize(ATTRIBUTES);
    foreach ($attributes as $abbrev => $attribute) {
        $milyen[] = $abbrev;
    }
    $languages = unserialize(LANGUAGES);
    foreach ($languages as $abbrev => $language) {
        $attributes[$abbrev] = $language;
        $milyen[] = $abbrev;
    }

    preg_match_all("/(" . implode('|', $milyen) . ")([0-5]{1}|-1|ps|pt|)(,|$)/i", $text, $matches, PREG_SET_ORDER);
    foreach ($matches as $match) {
        if (!isset($return[$match[1]]))
            $return[$match[1]] = $attributes[$match[1]];
        $return[$match[1]]['values'][] = $match[2];
    }

    $periods = unserialize(PERIODS);
    foreach ($return as $abbrev => $attribute) {
        sort($attribute['values']);
        $tmp1 = $tmp2 = '';

        for ($i = 0; $i < count($attribute['values']); $i++) {
            if ($attribute['values'][$i]) {
                $tmp1 .= $periods[$attribute['values'][$i]]['abbrev'];
                $tmp2 .= $periods[$attribute['values'][$i]]['name'];
            }
            if ($i < count($attribute['values']) - 2) {
                $tmp1 .= ", ";
                $tmp2 .= ", ";
            } elseif ($i < count($attribute['values']) - 1) {
                $tmp1 .= ", ";
                $tmp2 .= " és ";
            }
        }
        if (count($attribute['values']) > 0 AND $tmp2 != '') {
            $tmp2 .= " héten";
        }

        if ($tmp1 != '')
            $return[$abbrev]['name'] .= ' ' . $tmp1;
        if ($tmp2 != '' AND isset($attribute['description']))
            $return[$abbrev]['description'] .= ' ' . $tmp2;
    }
    //echo "<pre>".print_r($return,1)."</pre>";
    return $return;
}

function cleanMassAttr($text) {
    $milyen = array();
    $attributes = unserialize(ATTRIBUTES);
    foreach ($attributes as $abbrev => $attribute) {
        $milyen[] = $abbrev;
    }
    $languages = unserialize(LANGUAGES);
    foreach ($languages as $abbrev => $language) {
        $milyen[] = $abbrev;
    }
    foreach (unserialize(PERIODS) as $abbrev => $period)
        $periods[] = $abbrev;

    $text = trim($text, " ,");
    $attrs = explode(',', $text);
    sort($attrs);
    foreach ($attrs as $k => $attr) {
        preg_match('/^(' . implode('|', $milyen) . ')(' . implode('|', $periods) . '|)$/', $attr, $match);
        if (count($match) < 1) {
            //unset($attrs[$k]);
        } elseif ($match[2] == '0') {
            $attrs[$k] = $match[1];
        } elseif ($match[2] != '') {
            if (in_array($match[1], $attrs))
                unset($attrs[$k]);
        }
    }
    $attrs = array_unique($attrs);
    return implode(',', $attrs);
}

function formMass($pkey, $mkey, $mass = false, $group = false) {
    global $twig;

    if ($mass == false) {
        $mass = array(
            'id' => 'new',
            'napid' => 7,
            'ido' => '00:00',
            'nyelv' => '',
            'milyen' => '',
            'megjegyzes' => '',
        );
        if ($group == 'particular')
            $mass['napid'] = 0;
    }

    if ($group == false)
        $group = 'period';

    $nap2options = array(
        0 => 'minden héten',
        1 => 'első héten', 2 => 'második héten', 3 => 'harmadik héten', 4 => 'negyedik héten', 5 => 'ötödik héten',
        '-1' => 'utolsó héten',
        'ps' => 'páros héten', 'pt' => 'páratlan héten');

    $form = array(
        'id' => array(
            'type' => 'hidden',
            'name' => $group . "[" . $pkey . "][" . $mkey . "][id]",
            'value' => $mass['id']),
        'nap' => array(
            'name' => $group . "[" . $pkey . "][" . $mkey . "][napid]",
            'options' => array(0 => 'válassz', 1 => 'hétfő', 2 => 'kedd', 3 => 'szerda', 4 => 'csütörtök', 5 => 'péntek', 6 => 'szombat', 7 => 'vasárnap'),
            'selected' => $mass['napid'],
            'class' => 'nap'),
        'nap2' => array(
            'name' => $group . "[" . $pkey . "][" . $mkey . "][nap2]",
            'options' => $nap2options,
            'selected' => isset($mass['nap2_raw']) ? $mass['nap2_raw'] : false ),
        'ido' => array(
            'name' => $group . "[" . $pkey . "][" . $mkey . "][ido]",
            'value' => $mass['ido'],
            'size' => 4,
            'class' => 'time'),
        'nyelv' => array(
            'label' => 'nyelvek',
            'name' => $group . "[" . $pkey . "][" . $mkey . "][nyelv]",
            'value' => $mass['nyelv'],
            'size' => 5,
            'class' => 'language'),
        'milyen' => array(
            'label' => 'milyen',
            'name' => $group . "[" . $pkey . "][" . $mkey . "][milyen]",
            'value' => $mass['milyen'],
            'size' => 13,
            'class' => 'attributes'),
        'megjegyzes' => array(
            'label' => 'megjegyzések',
            'name' => $group . "[" . $pkey . "][" . $mkey . "][megjegyzes]",
            'value' => $mass['megjegyzes'],
            'style' => 'margin-top:4px;width:*')
    );
    return $form;
}

function formPeriod($pkey, $period = false, $group = false) {
    global $twig;

    if ($group == false)
        $group = 'period';

    $c = 0;
    if ($period == false) {
        $groups = array('particular' => 'különleges miserend', 'period' => ' időszak');
        $period = array(
            'nev' => 'új ' . $groups[$group],
            'tol' => '',
            'ig' => '',
            'napok' => array('new'));
    }

    $form = array(
        'nev1' => array(
            'type' => 'hidden',
            'name' => $group . "[" . $pkey . "][origname]",
            'value' => $period['nev']),
        'nev' => array(
            'name' => $group . "[" . $pkey . "][name]",
            'value' => $period['nev'],
            'size' => 30,
            'class' => 'name ' . $group),
        'from' => array(
            'name' => $group . "[" . $pkey . "][from]",
            'value' => trim(preg_replace('/(\+|-)([0-9]{1})$/i', '', $period['tol'])),
            'size' => 18,
            'class' => 'events'),
        'to' => array(
            'name' => $group . "[" . $pkey . "][to]",
            'value' => trim(preg_replace('/(\+|-)([0-9]{1})$/i', '', $period['ig'])),
            'size' => 18,
            'class' => 'events',
        )
    );

    if ($group == 'period') {
        $form['from2'] = array(
            'name' => $group . "[" . $pkey . "][from2]",
            'options' => array(
                0 => '≤',
                '+1' => '<'));
        $form['to2'] = array(
            'name' => $group . "[" . $pkey . "][to2]",
            'options' => array(
                0 => '≤',
                '-1' => '<'));
    } elseif ($group == 'particular') {
        $form['from2'] = array(
            'name' => $group . "[" . $pkey . "][from2]",
            'options' => array(
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

    if (preg_match('/(\+|-)([0-9]{1})$/i', $period['tol'], $match))
        $form['from2']['selected'] = $match[1] . $match[2];
    if (preg_match('/(\+|-)([0-9]{1})$/i', $period['ig'], $match))
        $form['to2']['selected'] = $match[1] . $match[2];


    $form['pkey'] = $pkey;

    foreach ($period['napok'] as $dkey => $day) {
        if (isset($day['misek'])) {
            foreach ($day['misek'] as $mkey => $mass) {
                $c++;
                $form['napok'][] = formMass($pkey, $c, $mass, $group);
            }
        } elseif ($day == 'new')
            $form['napok'][] = formMass($pkey, $dkey, false, $group);
    }

    $form['last'] = $c;

    return $form;
}

function searchChurches($args, $offset = 0, $limit = 20) {
    global $config;

    $return = array(
        'offset' => $offset,
        'limit' => $limit);

    if ($args['hely'] AND $args['hely'] != '') {
        if ($args['tavolsag'] == '')
            $args['tavolsag'] = 1;

        if (!isset($args['hely_geocode']))
            $args['hely_geocode'] = mapquestGeocode($args['hely']);
        $latlng = $args['hely_geocode'];
        $lat = $latlng['lat']; // latitude of centre of bounding circle in degrees
        $lon = $latlng['lng']; // longitude of centre of bounding circle in degrees
        $rad = $args['tavolsag'];
        $R = 6371;  // earth's mean radius, km
        $filterdistance = true;
    }

    $where = searchChurchesWhere($args);


    $query = "SELECT templomok.id,nev,ismertnev,varos,lat,lon FROM templomok ";
    if (isset($args['tnyelv']) AND $args['tnyelv'] != '0') {
        $query .= " INNER JOIN misek ON misek.tid = templomok.id ";
        if ($args['tnyelv'] == 'h')
            $args['tnyelv'] = 'hu|h';
        $where[] = " misek.nyelv  REGEXP '(^|,)(" . $args['tnyelv'] . ")([0-5]{0,1}|-1|ps|pt)(,|$)' ";
    }
    if (count($where) > 0)
        $query .= "WHERE " . implode(' AND ', $where);
    if (isset($args['tnyelv']) AND $args['tnyelv'] != '0')
        $query .= " GROUP BY templomok.id ";

    $query .= " ORDER BY nev ";
    if (!$lekerdez = mysql_query($query))
        echo "HIBA a templom keresőben!<br>$query<br>" . mysql_error();
    $return['sum'] = mysql_num_rows($lekerdez);
    if (!isset($filterdistance))
        $query .= " LIMIT " . ($offset ) . "," . ($limit);
    if (!$lekerdez = mysql_query($query))
        echo "HIBA a templom keresőben!<br>$query<br>" . mysql_error();
    while ($row = mysql_fetch_row($lekerdez, MYSQL_ASSOC)) {
        if (isset($filterdistance)) {
            //acos(sin(:lat)*sin(radians(Lat)) + cos(:lat)*cos(radians(Lat))*cos(radians(Lon)-:lon)) * :R < :rad
            $d = acos(sin(deg2rad($lat)) * sin(deg2rad($row['lat'])) + cos(deg2rad($lat)) * cos(deg2rad($row['lat'])) * cos(deg2rad($row['lon']) - deg2rad($lon))) * $R;
            if ($d <= $rad) {
                if ($config['mapquest']['useitforsearch'] == true) {
                    $d = mapquestDistance(array('lat' => $lat, 'lng' => $lon), array('lat' => $row['lat'], 'lng' => $row['lon']));
                    if ($d <= $rad)
                        $return['results'][] = $row;
                } else {
                    $return['results'][] = $row;
                }
            }
        } else {
            $return['results'][] = $row;
        }
    }

    if (isset($filterdistance)) {
        $return['sum'] = count($return['results']);
        if ($return['sum'] > 0)
            $return['results'] = array_slice($return['results'], $offset, $limit + $offset);
    }
    return $return;
}

function searchChurchesWhere($args) {
    $where = array(" ok = 'i' ");

    if ($args['kulcsszo'] != '') {
        $subwhere = array();
        if (preg_match('(\*|\?)', $args['kulcsszo'])) {
            $regexp = preg_replace('/\*/i', '.*', $args['kulcsszo']);
            $regexp = preg_replace('/\?/i', '.{1}', $regexp);
            $text = " REGEXP '" . $regexp . "'";
        } else {
            $text = " LIKE '%" . $args['kulcsszo'] . "%'";
        }
        foreach (array('nev', 'ismertnev', 'varos', 'cim', 'megkozelites', 'plebania', 'templomok.megjegyzes', 'misemegj') as $column) {
            $subwhere[] = $column . $text;
        }
        $where[] = " (" . implode(' OR ', $subwhere) . ") ";
    }

    if ($args['varos'] != '') {
        if ($args['varos'] == 'Budapest')
            $args['varos'] = 'Budapest*';

        if (preg_match('(\*|\?)', $args['varos'])) {
            $regexp = preg_replace('/\*/i', '.*', $args['varos']);
            $regexp = preg_replace('/\?/i', '.{1}', $regexp);
            $where[] = "varos REGEXP '^" . $regexp . "$'";
        } else {
            $where[] = "varos='" . $args['varos'] . "'";
        }
    }

    if ($args['hely'] AND $args['hely'] != '') {

        $latlng = $args['hely_geocode'];
        $lat = $latlng['lat']; // latitude of centre of bounding circle in degrees
        $lon = $latlng['lng']; // longitude of centre of bounding circle in degrees
        $rad = $args['tavolsag']; // radius of bounding circle in kilometers

        $R = 6371;  // earth's mean radius, km
        // first-cut bounding box (in degrees)
        $maxLat = $lat + rad2deg($rad / $R);
        $minLat = $lat - rad2deg($rad / $R);
        // compensate for degrees longitude getting smaller with increasing latitude
        $maxLon = $lon + rad2deg($rad / $R / cos(deg2rad($lat)));
        $minLon = $lon - rad2deg($rad / $R / cos(deg2rad($lat)));

        $where[] = "( lat BETWEEN " . $minLat . " AND " . $maxLat . " AND lon BETWEEN " . $minLon . " AND " . $maxLon . ")";
    }

    if (isset($args['gorog']) AND $args['gorog'] == 'gorog') {
        $where[] = "egyhazmegye IN (17,18, 34)";
    }

    if ($args['ehm'] != 0)
        $where[] = "egyhazmegye='" . $args['ehm'] . "'";

    if (isset($args['espker']) AND $args['espker'] != 0)
        $where[] = "espereskerulet='" . $args['espker'] . "'";
    return $where;
}

function searchMasses($args, $offset = 0, $limit = 20) {


    $return = array(
        'offset' => $offset,
        'limit' => $limit);
    $where = array(" m.torles = '0000:00:00 00:00:00' ");

    //templomok
    if (isset($args['templom']) AND is_numeric($args['templom'])) {
        $where[] = ' m.tid = ' . $args['templom'];
    } elseif ($args['varos'] != '' OR $args['kulcsszo'] != '' OR $args['egyhazmegye'] != '' OR $args['gorog'] == 'gorog' OR $args['hely'] != '' OR $args['tnyelv'] != '0') {
        if ($args['varos'] == 'Budapest')
            $args['varos'] = 'Budapest*';

        $tmp = $args;
        if (isset($tmp['leptet']))
            unset($tmp['leptet']);
        if (isset($tmp['min']))
            unset($tmp['min']);
        $results = searchChurches($args, 0, 1000000);
        $tids[] = 0;
        if (isset($results['results']))
            foreach ($results['results'] as $r)
                $tids[] = $r['id'];
        $where[] = " m.tid IN (" . implode(",", $tids) . ")";
    }
    if ($args['gorog'] == 'gorog') {
        $where[] = "egyhazmegye IN (17,18,34)";
    }
    //milyen nap
    if ($args['mikor'] == 'x')
        $args['mikor'] = $args['mikordatum'];
    $where[] = "m.nap IN ('" . date('N', strtotime($args['mikor'])) . "',0)";



    //milyen időszakban
    $day = date('m-d', strtotime($args['mikor']));
    $where[] = "( ( m.tmp_datumtol <= '" . $day . "' AND '" . $day . "' <= m.tmp_datumig AND m.tmp_relation = '<'  )
    OR  ( ( m.tmp_datumig <= '" . $day . "' OR '" . $day . "' <= m.tmp_datumtol ) AND ( m.tmp_relation = '>' ) )  
    OR ( m.tmp_datumig = '" . $day . "' AND m.tmp_datumig = '" . $day . "' AND  m.tmp_relation = '=' ) )";

    //milyen héten
    if (date('W', strtotime($args['mikor'])) & 1) {
        $parossag = 'pt';
    } else
        $parossag = "ps";
    $hanyadikP = getWeekInMonth($args['mikor']);
    $hanyadikM = getWeekInMonth($args['mikor'], '-');
    $where[] = "( m.nap2 IN ('','0','" . $hanyadikM . "','" . $hanyadikP . "','" . $parossag . "') OR m.nap2 IS NULL)";

    //milyen órákban
    if ($args['mikor2'] == 'de')
        $where[] = " m.ido < '12:00:01' AND m.ido > '00:00:01' ";
    elseif ($args['mikor2'] == 'du')
        $where[] = " m.ido > '11:59:59'";
    elseif ($args['mikor2'] == 'x') {
        $idok = explode('-', $args['mikorido']);
        $where[] = " m.ido >= '" . $idok[0] . ":00'";
        $where[] = " m.ido <= '" . $idok[1] . ":00'";
    }

    //LANGUAGES
    $languages = unserialize(LANGUAGES);
    foreach ($languages as $abbrev => $attribute)
        if ($attribute['abbrev'] != 'h')
            $nothu[] = $abbrev;
    if ($args['nyelv'] != '0' AND $args['nyelv'] != '') {
        if ($args['nyelv'] == 'h') {
            $where[] = "( m.nyelv REGEXP '(^|,)(" . $args['nyelv'] . ")([0]{0,1}|" . $hanyadikP . "|" . $hanyadikM . "|" . $parossag . ")(,|$)' OR 
                templomok.orszag = 12 AND m.nyelv NOT REGEXP '(^|,)(" . implode("|", $nothu) . ")([0]{0,1}|" . $hanyadikP . "|" . $hanyadikM . "|" . $parossag . ")(,|$)' )";
        } else {
            $where[] = "( m.nyelv REGEXP '(^|,)(" . $args['nyelv'] . ")([0]{0,1}|" . $hanyadikP . "|" . $hanyadikM . "|" . $parossag . ")(,|$)' )";
        }
    }

    //ATTRIBUTES
    $attributes = unserialize(ATTRIBUTES);

    //age group (checkbox)
    if (isset($args['kor'])) {
        foreach ($attributes as $abbrev => $attribute) {
            if ($attribute['group'] == 'age')
                $ages[] = $abbrev;
        }
        $wherekor = array();
        foreach ($args['kor'] as $kor) {
            if (in_array($kor, $ages)) {
                $wherekor[] = " m.milyen REGEXP '(^|,)(" . $kor . ")([0]{0,1}|" . $hanyadikP . "|" . $hanyadikM . "|" . $parossag . ")(,|$)' ";
            } elseif ($kor == 'na') {
                $wherekor[] = " m.milyen NOT REGEXP '(^|,)(" . implode('|', $ages) . ")([0]{0,1}|" . $hanyadikP . "|" . $hanyadikM . "|" . $parossag . ")(,|$)' ";
            }
        }
        $where[] = " ( " . implode(' OR ', $wherekor) . ") ";
    }

    //music group (chekbox)
    if (isset($args['zene'])) {
        foreach ($attributes as $abbrev => $attribute) {
            if ($attribute['group'] == 'music')
                $musics[] = $abbrev;
        }
        $wherezene = array();
        foreach ($args['zene'] as $zene) {
            if (in_array($zene, $musics)) {
                $wherezene[] = " m.milyen REGEXP '(^|,)(" . $zene . ")([0]{0,1}|" . $hanyadikP . "|" . $hanyadikM . "|" . $parossag . ")(,|$)' ";
            } elseif ($zene == 'na') {
                $wherezene[] = " m.milyen NOT REGEXP '(^|,)(" . implode("|", $musics) . ")([0]{0,1}|" . $hanyadikP . "|" . $hanyadikM . "|" . $parossag . ")(,|$)' ";
            }
        }
        $where[] = " ( " . implode(' OR ', $wherezene) . ") ";
    }

    //rite group (select)
    if ($args['ritus'] != '0') {
        if ($args['ritus'] == 'gor') {
            foreach ($attributes as $abbrev => $attribute)
                if ($attribute['group'] == 'liturgy' AND $attribute['isitmass'] == true AND $attribute['abbrev'] != 'gor')
                    $notgor[] = $abbrev;
            $where[] = "( m.milyen REGEXP '(^|,)(gor)([0]{0,1}|" . $hanyadikP . "|" . $hanyadikM . "|" . $parossag . ")(,|$)' OR 
                        ( egyhazmegye IN (17,18,34) AND m.milyen NOT REGEXP '(^|,)(" . implode("|", $notgor) . ")([0]{0,1}|" . $hanyadikP . "|" . $hanyadikM . "|" . $parossag . ")(,|$)' ) )";
        } elseif ($args['ritus'] == 'rom') {
            foreach ($attributes as $abbrev => $attribute)
                if ($attribute['group'] == 'liturgy' AND $attribute['isitmass'] == true AND $attribute['abbrev'] != 'rom')
                    $notrom[] = $abbrev;
            $where[] = "( (m.milyen NOT REGEXP '(^|,)(" . implode("|", $notrom) . ")([0]{0,1}|" . $hanyadikP . "|" . $hanyadikM . "|" . $parossag . ")(,|$)' AND egyhazmegye NOT IN (17,18,34)) OR 
                        ( egyhazmegye IN (17,18,34) AND m.milyen REGEXP '(^|,)(rom)([0]{0,1}|" . $hanyadikP . "|" . $hanyadikM . "|" . $parossag . ")(,|$)' ) )";
        } else {
            $where[] = " m.milyen REGEXP '(^|,)(" . $args['ritus'] . ")([0]{0,1}|" . $hanyadikP . "|" . $hanyadikM . "|" . $parossag . ")(,|$)' ";
        }
    }

    //liturgy (not mass) group (checkbox/radio)
    $not = $only = array();
    foreach ($attributes as $abbrev => $attribute) {
        if ($attribute['group'] == 'liturgy' AND $attribute['isitmass'] == false) {
            $not[$abbrev] = $abbrev;
        }
    }
    if (isset($args['liturgy'])) {
        foreach ($args['liturgy'] as $liturgy) {
            if (isset($not[$liturgy]))
                unset($not[$liturgy]);
        }
    }
    if (count($not) > 0)
        $where[] = " m.milyen NOT REGEXP '(^|,)(" . implode('|', $not) . ")([0]{0,1}|" . $hanyadikP . "|" . $hanyadikM . "|" . $parossag . ")(,|$)' ";

    $select = "SELECT m.*,templomok.nev,templomok.ismertnev,templomok.varos \nFROM misek m \n";
    $select .= " LEFT JOIN templomok ON m.tid = templomok.id \n";

    //Tudjuk meg, hogy hány templomban van megfelelő összesen
    $query = "SELECT count(*) as sum FROM ( \n";
    $query .= $select;
    if (count($where) > 0)
        $query .= " WHERE " . implode("\n AND ", $where);
    $query .= "\n GROUP BY tid \n";
    $query .= ") groups ;";
    if (!$lekerdez = mysql_query($query))
        echo "HIBA a templom keresőben!<br>$query<br>" . mysql_error();
    $row = mysql_fetch_row($lekerdez, MYSQL_ASSOC);
    $return['sum'] = $row['sum'];

    //Akkor jöhet a limitált csoportos lekérdezés, mert az jó
    $query = $select;
    $query .= " JOIN ( ";
    $query .= $select;
    if (count($where) > 0)
        $query .= " WHERE " . implode(' AND ', $where);
    $query .= " GROUP BY tid \n";
    $query .= " ORDER BY ido, templomok.varos, templomok.nev ";
    $query .= " LIMIT " . ($offset ) . "," . ($limit);
    $query .= ") groups ON groups.tid = m.tid ";
    if (count($where) > 0)
        $query .= " WHERE " . implode(' AND ', $where);
    $query .= " ORDER BY ido, templomok.varos, templomok.nev ";
    if (!$lekerdez = mysql_query($query))
        echo "HIBA a templom keresőben!<br>$query<br>" . mysql_error();
    $masses = array();
    //echo $query;
    while ($row = mysql_fetch_row($lekerdez, MYSQL_ASSOC)) {
        if ($row['tmp_datumtol'] == $row['tmp_datumig'])
            $type = 'particulars';
        else
            $type = 'periods';
        $masses[$row['tid']][$type][$row['idoszamitas']][] = $row;
    }

    //use particulars only, if we can    
    foreach ($masses as $tid => $church) {
        if (array_key_exists("particulars", $church))
            $masses[$tid] = $church['particulars'];
        elseif (array_key_exists("periods", $church))
            $masses[$tid] = $church['periods'];
    }
    // weight
    foreach ($masses as $tid => $periods) {
        $weight = 0;
        $tmp = array();
        foreach ($periods as $period) {
            $m = array_shift(array_values($period));
            $w = $m['weight'];
            if ($w == '')
                $w = 0;
            if ($w >= $weight) {
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

function getWeekInMonth($date, $order = '+') {
    $num = 0;
    if ($order == '+')
        for ($i = 0; $i < 6; $i++) {
            if (date("m", strtotime($date)) == date('m', strtotime($date . " -" . $i . " week")))
                $num++;
        }
    if ($order == '-')
        for ($i = 0; $i < 6; $i++) {
            if (date("m", strtotime($date)) == date('m', strtotime($date . " +" . $i . " week")))
                $num--;
        }
    return $num;
}

function sugolink($id, $height = false) {
    global $twig;
    $args['id'] = $id;
    if ($height != false)
        $args['height'] = $height;
    return $twig->render('help_link.html', $args);
}

function generateMassTmp($where = false) {   
    global $config;
    
    $results = DB::table('misek')
            ->select('id','tol','ig')
            ->where('torles','0000-00-00 00:00:00');
    if ($where != false)
        $results = $results->whereRaw($where);        
    $results = $results->get();
    foreach($results as $row) {
        if ($row->tol == '')
            $row->tol = '01-01';
        $row->tmp_datumtol = event2Date($row->tol);
        if ($row->ig == '')
            $row->ig = '12-31';
        $row->tmp_datumig = event2Date($row->ig);
        if ($row->tmp_datumig > $row->tmp_datumtol)
            $row->tmp_relation = '<';
        elseif ($row->tmp_datumtol == $row->tmp_datumig)
            $row->tmp_relation = '=';
        else
            $row->tmp_relation = '>';
                
        DB::table('misek')
                ->where('id',$row->id)
                ->update(collect($row)->toArray());        
    }    
}

function widget_miserend($args) {
    global $twig, $config;
    $tid = $args['tid'];
    $vars = \Eloquent\Church::find($tid)->toArray();
    if ($vars == array())
        $html = 'Nincs ilyen templom.';
    else {
        $vars['miserend'] = getMasses($tid);

        if ($args['misemegj'] == 'off')
            unset($vars['misemegj']);
        $html = $twig->render('widget_massschedule.twig', $vars);
    };

    if (!isset($args['callback']))
        return $html;
    else
        return $args['callback'] . '(' . json_encode(array('html' => $html)) . ')';
}

function upload2ftp($ftp_server, $ftp_user_name, $ftp_user_pass, $destination_file, $source_file) {
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

function updateDeleteZeroMass() {
    global $config;

    // Ha csak 00:00:00-k vannak, akkor töröljük azokat is ianktiváljuk a misét
    $query = "SELECT count(misek.id) as misek ,SUM(if(ido = '00:00:00', 1, 0)) AS nullak, tid, misek.id,misek.megjegyzes,templomok.misemegj FROM misek LEFT JOIN templomok ON tid = templomok.id GROUP BY tid;";
    $result = mysql_query($query);
    $c = 0;
    while (($tmp = mysql_fetch_array($result))) {
        if ($tmp['nullak'] == 1 AND $tmp['misek'] == 1) {
            $c ++;
            if ($tmp['megjegyzes'] != '' AND $tmp['misemegj'] == '') {
                //echo $tmp['tid'].": ".$tmp['megjegyzes']." -::-".$tmp['misemegj']."<br/>";
                $query = "UPDATE templomok SET misemegj = '" . $tmp['megjegyzes'] . "' WHERE id = " . $tmp['tid'] . " LIMIT 1";
                //echo $query."<br/>";
                mysql_query($query);
            }
            $query = "UPDATE templomok SET  miseaktiv = 0 WHERE id = " . $tmp['tid'] . " LIMIT 1";
            //echo $query."<br/>";
            mysql_query($query);

            $query = "DELETE FROM misek WHERE id = " . $tmp['id'] . " LIMIT 1;";
            if ($config['debug'] > 1)
                echo $query . "<br/>";
            mysql_query($query);
        }
    }
    if ($config['debug'] > 0)
        echo $c . " db csak nullák eltávolítva<br/>";
}

function updateCleanMassLanguages() {
    global $config;
    // magyarországi templomok nyelve
    $query = " UPDATE misek LEFT JOIN templomok on misek.tid = templomok.id SET nyelv = NULL  WHERE ( nyelv = 'h0' OR nyelv = 'h') AND templomok.orszag = 12;";
    mysql_query($query);
    if ($config['debug'] > 0)
        echo "Magyarországi templomokban alapértelmezetten magyarul misézünk<br/>";
}

function updateAttributesOptimalization() {
    global $config;
    //milyen/nyelv optimalizálás (!minden misén átmegy!)
    $c = 0;
    $query = "SELECT * from misek WHERE milyen <> '' OR nyelv <> '' ";
    $result = mysql_query($query);
    while (($row = mysql_fetch_array($result))) {
        $query = "UPDATE misek SET milyen = '" . cleanMassAttr($row['milyen']) . "', nyelv = '" . cleanMassAttr($row['nyelv']) . "' WHERE id = " . $row['id'] . " LIMIT 1";
        //echo $query."<br/>";
        mysql_query($query);
        $c++;
    }
    if ($config['debug'] > 0)
        echo $c . " db milyen/nyelv optimalizálva<br/>";
}

function updateComments2Attributes() {
    global $config;
    //megjegyzés -> tulajdonság
    $c = 0;
    $attributes = unserialize(ATTRIBUTES);
    foreach ($attributes as $abbrev => $attribute) {
        $query = "SELECT * from misek WHERE megjegyzes REGEXP '^" . $attribute['name'] . "$' ";
        $result = mysql_query($query);
        while (($row = mysql_fetch_array($result))) {
            if (!preg_match('/(^|,)' . $abbrev . '($|,)/i', $row['milyen']))
                $milyen = $abbrev . "," . $row['milyen'];
            else
                $milyen = $abbrev . "," . $row['milyen'];
            $query = "UPDATE misek SET megjegyzes = '', milyen = '" . $milyen . "' WHERE id = " . $row['id'] . " LIMIT 1";
            //echo $query."<br/>";
            mysql_query($query);
            $c++;
        }
    }
    if ($config['debug'] > 0)
        echo $c . " db megjegyzés tulajdonsággá alakítva<br/>";
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
    return rtrim($data, "\n");
}


function feltoltes_block() {
    global $user;
    
    if(!isset($user->responsibilities['church']['allowed']))
        return false;
    
    $allowed = $user->responsibilities['church']['allowed'];
    $ids = [];
    foreach($allowed as $church) {
        $ids[] = $church->church_id;
    }
    $churches = \Eloquent\Church::whereIn('id',$ids)->get();
    
    if(count($churches) == 0) return;
    
    $kod_tartalom = '<ul>';
    foreach( $churches as $church) { 
        $jelzes = '';        
        if ($church->eszrevetel == 'u')
            $jelzes.="<a href=\"javascript:OpenScrollWindow('/templom/".$church->id."/eszrevetelek',550,500);\"><img src=/img/csomag.gif title='Új észrevételt írtak hozzá!' align=absmiddle border=0></a> ";
        elseif ($church->eszrevetel == 'i')
            $jelzes.="<a href=\"javascript:OpenScrollWindow('/templom/".$church->id."/eszrevetelek',550,500);\"><img src=/img/csomag1.gif title='Észrevételek!' align=absmiddle border=0></a> ";
        elseif ($church->eszrevetel == 'f')
            $jelzes.="<a href=\"javascript:OpenScrollWindow('/templom/".$church->id."/eszrevetelek',550,500);\"><img src=/img/csomagf.gif title='Észrevétel javítása folyamatban!' align=absmiddle border=0></a> ";
        else
            $jelzes = '';

        $kod_tartalom.="\n<li>$jelzes<a href='/templom/".$church->id."/edit' class=link_kek title='".$church->varos."'>".$church->nev."</a></li>";
    }

    $kod_tartalom.="\n<li><a href='/user/maintainedchurches' class=felsomenulink>Teljes lista...</a></li>";
    $kod_tartalom .= '</ul>';

    return $kod_tartalom;
    
}

function addMessage($text, $severity = false) {
    return \Message::add($text,$severity);    
}

function copyArrayToObject($array, &$object) {
    foreach ($array as $key => $value) {
        $object->$key = $value;
    }
}

function br2nl($string) {
    return preg_replace('/\<br(\s*)?\/?\>/i', PHP_EOL, $string);
}

function idoszak($i) {
    switch ($i) {
        case 'a': $tmp = 'Ádventi idő';
            break;
        case 'k': $tmp = 'Karácsonyi idő';
            break;
        case 'n': $tmp = 'Nagyböjti idő';
            break;
        case 'h': $tmp = 'Húsvéti idő';
            break;
        case 'e': $tmp = 'Évközi idő';
            break;
        case 's': $tmp = 'Szent ünnepe';
            break;
    }
    return $tmp;
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

spl_autoload_register(function ($class) {
    $classpath = PATH . '/classes/' . str_replace('\\', '/', strtolower($class)) . '.php';
    if ($file = file_exists_ci($classpath)) {
        require_once($file);
    }
});

if (!function_exists("env")) {

    function env($name, $default = false) {
        if (!getenv($name))
            return $default;
        else
            return getenv($name);
    }

}

function file_exists_ci($fileName) {
    if (file_exists($fileName)) {
        return $fileName;
    }
    $pattern = dirname(__FILE__) . "/classes";
    $files = array();
    for ($i = 0; $i < 5; $i++) {
        $pattern .= '/*';
        $files = array_merge($files, glob($pattern));
    }
    $fileNameLowerCase = strtolower($fileName);
    foreach ($files as $file) {
        if (strtolower($file) == $fileNameLowerCase) {
            return $file;
        }
    }
    return false;
}

function printr($variable) {

    echo"<pre>" . print_r($variable, 1) . "</pre>";
}

function configurationSetEnvironment($env) {
    global $config;
    include('config.php');
    if (!array_key_exists($env, $environment)) {
        $env = 'default';
    }
    $config = $environment['default'];
    $config['env'] = $env;
    if ($env != 'default') {
        overrideArray($config, $environment[$env]);
    }
    putenv('MISEREND_WEBAPP_ENVIRONMENT=' . $env);
    dbconnect();
}

function overrideArray(&$orig, $new) {
    foreach ($new as $k => $n) {
        if (!is_array($n)) {
            $orig[$k] = $n;
        } else {
            overrideArray($orig[$k], $n);
        }
    }
}
