<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use App\Legacy\User;
use Illuminate\Database\Capsule\Manager as DB;

function sanitize($text)
{
    if (is_array($text)) {
        foreach ($text as $k => $i) {
            $text[$k] = sanitize($i);
        }
    } else {
        $text = preg_replace('/\n/i', '<br/>', $text);
        $text = strip_tags($text, '<a><i><b><strong><br>');
        $text = trim($text);
    }

    return $text;
}

function checkUsername($username)
{
    if ($username == '') {
        return false;
    }
    if ($username == '*vendeg*') {
        return false;
    }
    if (strlen($username) > 20) {
        return false;
    }
    if (preg_match("/( |\"|'|;)/i", $username)) {
        return false;
    }

    // TODO: én ezt feloldanám
    if (!preg_match('/^([a-z0-9]{1,20})$/i', $username)) {
        return false;
    }

    $checkeduser = new User(login: $username);
    if ($checkeduser->uid > 0) {
        return false;
    }

    return true;
}

function mapquestGeocode($location)
{
    global $config;
    $url = 'http://www.mapquestapi.com/geocoding/v1/address?key='.$config['mapquest']['appkey'];
    $url .= '&location='.urlencode($location);
    $url .= '&outFormat=json&maxResults=1';

    $file = file_get_contents($url);
    $mapquest = json_decode($file, true);

    // print_r($mapquest);
    // echo "<a href='".$mapquest['results'][0]['locations'][0]['mapUrl']."'>map</a>";
    return array_merge($mapquest['results'][0]['locations'][0]['latLng'], ['mapUrl' => $mapquest['results'][0]['locations'][0]['mapUrl']]);
}

function LirugicalDay($datum = false)
{
    global $config;

    // TODO: ha nincs könyvár, attól még megpróbálhatná élesben lehozni.
    if (!is_dir('fajlok/igenaptar')) {
        // die('Sajnos nincsen faljok/igenaptar könyvtár. Ez komoly hiba.');
        return false;
    }

    if (empty($datum)) {
        $datum = date('Y-m-d');
    }

    $file = 'fajlok/igenaptar/'.$datum.'.xml';
    if (file_exists($file) && $config['debug'] == 0) {
        $xmlstr = file_get_contents($file);
    } else {
        $source = 'http://breviar.kbs.sk/cgi-bin/l.cgi?qt=pxml&d='.substr($datum, 8, 2).'&m='.substr($datum, 5, 2).'&r='.substr($datum, 0, 4).'&j=hu';
        $ch = curl_init();
        $timeout = 1;
        curl_setopt($ch, \CURLOPT_URL, $source);
        curl_setopt($ch, \CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, \CURLOPT_CONNECTTIMEOUT, $timeout);
        $xmlstr = curl_exec($ch);
        curl_close($ch);
        if ($xmlstr) {
            @file_put_contents($file, $xmlstr);
        }
    }

    if ($xmlstr != '') {
        $xmlcont = @simplexml_load_string($xmlstr);
        if ($xmlcont != '') {
            $xmlcont = new SimpleXMLElement($xmlstr);

            return $xmlcont->CalendarDay;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

/** @deprecated  */
function LiturgicalDayAlert($html = false, $date = false)
{
    if ($date == false) {
        $date = date('Y-m-d');
    }
    $alert = false;
    $day = LirugicalDay($date);
    if ($day != false && isset($day->Celebration)) {
        if ($day->Celebration->LiturgicalCelebrationLevel <= 4 && date('N', strtotime($date)) != 7) {
            $text = 'Ma van <strong>'.$day->Celebration->LiturgicalCelebrationName.'</strong>';
            if (preg_match('/ünnep$/i', $day->Celebration->LiturgicalCelebrationType)) {
                $text .= ' '.$day->Celebration->LiturgicalCelebrationType.'e';
            }

            if ($html == false) {
                return true;
            } else {
                global $twig;

                return $twig->render('alert_liturgicalday.html', ['text' => $text]);
            }
        }
    }

    if ($html == false) {
        return false;
    } else {
        return '';
    }
}

function checkDateBetween($date, $start, $end)
{
    global $config;
    if ($config['debug'] > 1) {
        echo 'Is '.$date.' between '.$start.' and '.$end.'? <br/>';
    }

    $year = date('Y', strtotime($date));
    if (strtotime($year.'-'.$start) <= strtotime($year.'-'.$end)) {
        if (strtotime($year.'-'.$start) <= strtotime($date) && strtotime($date) <= strtotime($year.'-'.$end)) {
            return true;
        } else {
            return false;
        }
    } else {
        if (strtotime($year.'-'.$start) > strtotime($date) && strtotime($date) > strtotime($year.'-'.$end)) {
            return false;
        } else {
            return true;
        }
    }
}

function event2Date($event, $year = false)
{
    if ($year == false) {
        $year = date('Y');
    }

    if (preg_match('/^([0-9]{4})(\.|-)([0-9]{2})(\.|-)([0-9]{2})(\.|)/i', $event, $match)) {
        return $match[3].'-'.$match[5];
    }
    if (preg_match('/^([0-9]{2})(\.|-)([0-9]{2})(\.|)(( \-| \+)[0-9]{1,3}|$)/i', $event, $match)) {
        if ($match[5] != '') {
            $extra = $match[5].' days';
        } else {
            $extra = false;
        }

        return date('m-d', strtotime(date('Y').'-'.$match[1].'-'.$match[3].' '.$extra));
    }

    $event = preg_replace('/(\+|-)1$/', '${1}1 day', $event);
    $events = [];
    $query = "SELECT name, date FROM events WHERE year = '".$year."' ";
    $results = DB::table('events')
            ->select('name', 'date')
            ->where('year', $year)
            ->get();
    foreach ($results as $row) {
        $events['name'][] = '/^'.$row->name.'( (\+|-)([0-9]{1,3})|)( day|)$/i';
        $events['date'][] = $row->date.'$1$4';
    }
    $event = preg_replace($events['name'], $events['date'], $event);
    $event = preg_replace('/^([0-9]{2})(\.|-)([0-9]{2})/i', date('Y').'$2$1$2$3', $event);
    $event = date('m-d', strtotime($event));

    return $event;
}

function getMasses($tid, $date = false)
{
    if ($date == false || $date == '') {
        $date = date('Y-m-d');
    }

    $napok = ['x', 'hétfő', 'kedd', 'szerda', 'csütörtök', 'péntek', 'szombat', 'vasárnap'];
    $nap2options = [
        0 => 'minden héten',
        1 => '1. héten', 2 => '2. héten', 3 => '3. héten', 4 => '4. héten', 5 => '5. héten',
        '-1' => 'utolsó héten',
        'ps' => 'páros héten', 'pt' => 'páratlan héten'];

    $results = DB::table('misek')
        ->where('torles', '0000-00-00 00:00:00')
        ->where('tid', $tid)
        ->groupBy('idoszamitas')
        ->orderBy('weight', 'DESC')
        ->get();

    $currentExists = false;

    foreach ($results as $row) {
        $row = (array) $row;

        $tmp = [];
        $tmp['nev'] = $row['idoszamitas'];
        $tmp['weight'] = $row['weight'];
        $tmp['tol'] = $row['tol'];
        $tmp['ig'] = $row['ig'];
        $tmp['datumtol'] = $datumtol = $row['tmp_datumtol']; // event2Date($row['tol']);
        $tmp['datumig'] = $datumig = $row['tmp_datumig']; // event2Date($row['ig']);

        if (checkDateBetween($date, $datumtol, $datumig)) {
            $tmp['now'] = true;
            if ($currentExists == false) {
                $tmp['current'] = true;
                $currentExists = true;
            }
        }

        for ($i = 1; $i < 8; ++$i) {
            $tmp['napok'][$i]['nev'] = $napok[$i];
        }
        // unset($tmp['napok'][1]);  $tmp['napok'][1]['nev'] = $napok[1];

        $results2 = DB::table('misek')
            ->where('torles', '0000-00-00 00:00:00')
            ->where('tid', $tid)
            ->where('idoszamitas', $row['idoszamitas'])
            ->orderBy('nap')
            ->orderBy('ido')
            ->get();

        foreach ($results2 as $row2) {
            $row2 = (array) $row2;
            if ($row2['milyen'] != '') {
                $row2['attr'] = decodeMassAttr($row2['milyen']);
            } else {
                $row2['attr'] = [];
            }
            $row2['attr'] = array_merge($row2['attr'], decodeMassAttr($row2['nyelv']));

            $ido = (int) substr($row2['ido'], 0, 2);
            $row2['ido'] = $ido.':'.substr($row2['ido'], 3, 2);
            $row2['nap2_raw'] = $row2['nap2'];
            if ($row2['nap2'] != '') {
                $row2['nap2'] = '('.$nap2options[$row2['nap2']].')';
            }

            $row2['napid'] = $row2['nap'];
            $row2['nap'] = $napok[$row2['nap']];
            $tmp['napok'][$row2['napid']]['misek'][] = $row2;
            $tmp['napok'][$row2['napid']]['nev'] = $row2['nap'];
        }
        if ($tmp['tol'] == $tmp['ig']) {
            $return['particulars'][] = $tmp;
        } else {
            $return['periods'][] = $tmp;
        }
    }

    // order byweight

    if (isset($return['periods']) && is_array($return['periods'])) {
        usort($return['periods'], 'cmp');
    }
    if (isset($return['particulars']) && is_array($return['particulars'])) {
        usort($return['particulars'], 'cmp');
    }

    return $return ?? [];
}

function cmp($a, $b)
{
    return $a['weight'] - $b['weight'];
}

function decodeMassAttr($text)
{
    $return = [];

    $milyen = [];
    $attributes = unserialize(ATTRIBUTES);
    foreach ($attributes as $abbrev => $attribute) {
        $milyen[] = $abbrev;
    }
    $languages = unserialize(LANGUAGES);
    foreach ($languages as $abbrev => $language) {
        $attributes[$abbrev] = $language;
        $milyen[] = $abbrev;
    }

    preg_match_all('/('.implode('|', $milyen).')([0-5]{1}|-1|ps|pt|)(,|$)/i', $text, $matches, \PREG_SET_ORDER);
    foreach ($matches as $match) {
        if (!isset($return[$match[1]])) {
            $return[$match[1]] = $attributes[$match[1]];
        }
        $return[$match[1]]['values'][] = $match[2];
    }

    $periods = unserialize(PERIODS);
    foreach ($return as $abbrev => $attribute) {
        sort($attribute['values']);
        $tmp1 = $tmp2 = '';

        for ($i = 0; $i < count($attribute['values']); ++$i) {
            if ($attribute['values'][$i]) {
                $tmp1 .= $periods[$attribute['values'][$i]]['abbrev'];
                $tmp2 .= $periods[$attribute['values'][$i]]['name'];
            }
            if ($i < count($attribute['values']) - 2) {
                $tmp1 .= ', ';
                $tmp2 .= ', ';
            } elseif ($i < count($attribute['values']) - 1) {
                $tmp1 .= ', ';
                $tmp2 .= ' és ';
            }
        }
        if (count($attribute['values']) > 0 && $tmp2 != '') {
            $tmp2 .= ' héten';
        }

        if ($tmp1 != '') {
            $return[$abbrev]['name'] .= ' '.$tmp1;
        }
        if ($tmp2 != '' && isset($attribute['description'])) {
            $return[$abbrev]['description'] .= ' '.$tmp2;
        }
    }

    // echo "<pre>".print_r($return,1)."</pre>";
    return $return;
}

function cleanMassAttr($text)
{
    $milyen = [];
    $attributes = unserialize(ATTRIBUTES);
    foreach ($attributes as $abbrev => $attribute) {
        $milyen[] = $abbrev;
    }
    $languages = unserialize(LANGUAGES);
    foreach ($languages as $abbrev => $language) {
        $milyen[] = $abbrev;
    }
    foreach (unserialize(PERIODS) as $abbrev => $period) {
        $periods[] = $abbrev;
    }

    $text = trim($text, ' ,');
    $attrs = explode(',', $text);
    sort($attrs);
    foreach ($attrs as $k => $attr) {
        preg_match('/^('.implode('|', $milyen).')('.implode('|', $periods).'|)$/', $attr, $match);
        if (count($match) < 1) {
            // unset($attrs[$k]);
        } elseif ($match[2] == '0') {
            $attrs[$k] = $match[1];
        } elseif ($match[2] != '') {
            if (in_array($match[1], $attrs)) {
                unset($attrs[$k]);
            }
        }
    }
    $attrs = array_unique($attrs);

    return implode(',', $attrs);
}

function formMass($pkey, $mkey, $mass = false, $group = false)
{
    global $twig;

    if ($mass == false) {
        $mass = [
            'id' => 'new',
            'napid' => 7,
            'ido' => '00:00',
            'nyelv' => '',
            'milyen' => '',
            'megjegyzes' => '',
        ];
        if ($group == 'particular') {
            $mass['napid'] = 0;
        }
    }

    if ($group == false) {
        $group = 'period';
    }

    $nap2options = [
        0 => 'minden héten',
        1 => 'első héten', 2 => 'második héten', 3 => 'harmadik héten', 4 => 'negyedik héten', 5 => 'ötödik héten',
        '-1' => 'utolsó héten',
        'ps' => 'páros héten', 'pt' => 'páratlan héten'];

    $form = [
        'id' => [
            'type' => 'hidden',
            'name' => $group.'['.$pkey.']['.$mkey.'][id]',
            'value' => $mass['id']],
        'nap' => [
            'name' => $group.'['.$pkey.']['.$mkey.'][napid]',
            'options' => [0 => 'válassz', 1 => 'hétfő', 2 => 'kedd', 3 => 'szerda', 4 => 'csütörtök', 5 => 'péntek', 6 => 'szombat', 7 => 'vasárnap'],
            'selected' => $mass['napid'],
            'class' => 'nap'],
        'nap2' => [
            'name' => $group.'['.$pkey.']['.$mkey.'][nap2]',
            'options' => $nap2options,
            'selected' => $mass['nap2_raw'] ?? false],
        'ido' => [
            'name' => $group.'['.$pkey.']['.$mkey.'][ido]',
            'value' => $mass['ido'],
            'size' => 4,
            'class' => 'time'],
        'nyelv' => [
            'label' => 'nyelvek',
            'name' => $group.'['.$pkey.']['.$mkey.'][nyelv]',
            'value' => $mass['nyelv'],
            'size' => 5,
            'class' => 'language'],
        'milyen' => [
            'label' => 'milyen',
            'name' => $group.'['.$pkey.']['.$mkey.'][milyen]',
            'value' => $mass['milyen'],
            'size' => 13,
            'class' => 'attributes'],
        'megjegyzes' => [
            'label' => 'megjegyzések',
            'name' => $group.'['.$pkey.']['.$mkey.'][megjegyzes]',
            'value' => $mass['megjegyzes'],
            'style' => 'margin-top:4px;width:*'],
    ];
    foreach ($form as $k => $v) {
        $form[$k]['style'] = 'display:inline;width:unset';
    }

    return $form;
}

function formPeriod($pkey, $period = false, $group = false)
{
    global $twig;

    if ($group == false) {
        $group = 'period';
    }

    $c = 0;
    if ($period == false) {
        $groups = ['particular' => 'különleges miserend', 'period' => ' időszak'];
        $period = [
            'nev' => 'új '.$groups[$group],
            'tol' => '',
            'ig' => '',
            'napok' => ['new']];
    }

    $form = [
        'nev1' => [
            'type' => 'hidden',
            'name' => $group.'['.$pkey.'][origname]',
            'value' => $period['nev']],
        'nev' => [
            'name' => $group.'['.$pkey.'][name]',
            'value' => $period['nev'],
            'size' => 30,
            'class' => 'name '.$group],
        'from' => [
            'name' => $group.'['.$pkey.'][from]',
            'value' => trim(preg_replace('/(\+|-)([0-9]{1})$/i', '', $period['tol'])),
            'size' => 18,
            'class' => 'events'],
        'to' => [
            'name' => $group.'['.$pkey.'][to]',
            'value' => trim(preg_replace('/(\+|-)([0-9]{1})$/i', '', $period['ig'])),
            'size' => 18,
            'class' => 'events',
        ],
    ];

    if ($group == 'period') {
        $form['from2'] = [
            'name' => $group.'['.$pkey.'][from2]',
            'style' => 'display:inline;width:unset',
            'options' => [
                0 => '≤',
                '+1' => '<']];
        $form['to2'] = [
            'name' => $group.'['.$pkey.'][to2]',
            'style' => 'display:inline;width:unset',
            'options' => [
                0 => '≤',
                '-1' => '<']];
    } elseif ($group == 'particular') {
        $form['from2'] = [
            'name' => $group.'['.$pkey.'][from2]',
            'options' => [
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
                '+8' => 'utáni 8. nap']];
    }

    if (preg_match('/(\+|-)([0-9]{1})$/i', $period['tol'], $match)) {
        $form['from2']['selected'] = $match[1].$match[2];
    }
    if (preg_match('/(\+|-)([0-9]{1})$/i', $period['ig'], $match)) {
        $form['to2']['selected'] = $match[1].$match[2];
    }

    foreach ($form as $k => $v) {
        $form[$k]['style'] = 'display:inline;width:unset';
    }
    $form['pkey'] = $pkey;

    foreach ($period['napok'] as $dkey => $day) {
        if (isset($day['misek'])) {
            foreach ($day['misek'] as $mkey => $mass) {
                ++$c;
                $form['napok'][] = formMass($pkey, $c, $mass, $group);
            }
        } elseif ($day == 'new') {
            $form['napok'][] = formMass($pkey, $dkey, false, $group);
        }
    }

    $form['last'] = $c;

    return $form;
}

function searchChurches($args, $offset = 0, $limit = 20)
{
    global $config;

    $return = [
        'offset' => $offset,
        'limit' => $limit];

    if (isset($args['hely']) && $args['hely'] != '') {
        if ($args['tavolsag'] == '') {
            $args['tavolsag'] = 1;
        }

        if (!isset($args['hely_geocode'])) {
            $args['hely_geocode'] = mapquestGeocode($args['hely']);
        }
        $latlng = $args['hely_geocode'];
        $lat = $latlng['lat']; // latitude of centre of bounding circle in degrees
        $lon = $latlng['lng']; // longitude of centre of bounding circle in degrees
        $rad = $args['tavolsag'];
        $R = 6371;  // earth's mean radius, km
        $filterdistance = true;
    }

    $where = searchChurchesWhere($args);

    $search = DB::table('templomok')
        ->select('templomok.id', 'nev', 'ismertnev', 'varos', 'lat', 'lon');

    /* WHERE */
    $search->where('ok', 'i');

    // keyword
    if (isset($args['kulcsszo']) && $args['kulcsszo'] != '') {
        $subwhere = [];
        if (preg_match('(\*|\?)', $args['kulcsszo'])) {
            $regexp = preg_replace('/\*/i', '.*', $args['kulcsszo']);
            $regexp = preg_replace('/\?/i', '.{1}', $regexp);
            $text = " REGEXP '".$regexp."'";
        } else {
            $text = " LIKE '%".$args['kulcsszo']."%'";
        }
        foreach (['nev', 'ismertnev', 'varos', 'cim', 'megkozelites', 'plebania', 'templomok.megjegyzes', 'misemegj'] as $column) {
            $subwhere[] = $column.$text;
        }
        $search->whereRaw(' ('.implode(' OR ', $subwhere).') ');
    }

    // varos
    if (isset($args['varos']) && $args['varos'] != '') {
        if ($args['varos'] == 'Budapest') {
            $args['varos'] = 'Budapest*';
        }

        if (preg_match('(\*|\?)', $args['varos'])) {
            $regexp = preg_replace('/\*/i', '.*', $args['varos']);
            $regexp = preg_replace('/\?/i', '.{1}', $regexp);
            $search->where('varos', 'REGEXP', '^'.$regexp.'$');
        } else {
            $search->where('varos', $args['varos']);
        }
    }

    // tnyelv
    if (isset($args['tnyelv']) && $args['tnyelv'] != '0') {
        if ($args['tnyelv'] == 'h') {
            $args['tnyelv'] = 'hu|h';
        }

        $search->join('misek', 'misek.tid', '=', 'templomok.id');
        $search->where('misek.nyelv', 'REGEXP', '(^|,)('.$args['tnyelv'].')([0-5]{0,1}|-1|ps|pt)(,|$)');
    }

    // gorog only
    if (isset($args['gorog']) && $args['gorog'] == 'gorog') {
        $search->whereIn('egyhazmegye', [17, 18, 34]);
    }

    // egyhazmegye
    if (isset($args['ehm']) && $args['ehm'] != 0) {
        $search->where('egyhazmegye', $args['ehm']);
    }

    // espereskerulet
    if (isset($args['espker']) && $args['espker'] != 0) {
        $search->where('espereskerulet', $args['espker']);
    }

    if (count($where) > 0) {
        foreach ($where as $w) {
            $search->whereRaw($w);
        }
    }

    $search->groupBy('templomok.id'); // Igazából ez csak a tnyelv esetén szükséges
    $search->orderBy('nev');

    $sum = $search;
    $return['sum'] = count($sum->get());

    if (!isset($filterdistance)) {
        $search->offset($offset)->limit($limit);
        $results = $search->get()->toArray();
        $return['results'] = array_map(function ($value) {return (array) $value; }, $results);
    } else {
        foreach ($rows as $row) {
            if (isset($filterdistance)) {
                // acos(sin(:lat)*sin(radians(Lat)) + cos(:lat)*cos(radians(Lat))*cos(radians(Lon)-:lon)) * :R < :rad
                $d = acos(sin(deg2rad($lat)) * sin(deg2rad($row['lat'])) + cos(deg2rad($lat)) * cos(deg2rad($row['lat'])) * cos(deg2rad($row['lon']) - deg2rad($lon))) * $R;
                if ($d <= $rad) {
                    if ($config['mapquest']['useitforsearch'] == true) {
                        $d = mapquestDistance(['lat' => $lat, 'lng' => $lon], ['lat' => $row['lat'], 'lng' => $row['lon']]);
                        if ($d <= $rad) {
                            $return['results'][] = $row;
                        }
                    } else {
                        $return['results'][] = $row;
                    }
                }
            } else {
                $return['results'][] = (array) $row;
            }
        }
    }

    if (isset($filterdistance)) {
        $return['sum'] = count($return['results']);
        if ($return['sum'] > 0) {
            $return['results'] = array_slice($return['results'], $offset, $limit + $offset);
        }
    }

    return $return;
}

function searchChurchesWhere($args)
{
    $where = [];

    if (isset($args['hely']) && $args['hely'] != '') {
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

        $where[] = '( lat BETWEEN '.$minLat.' AND '.$maxLat.' AND lon BETWEEN '.$minLon.' AND '.$maxLon.')';
    }

    return $where;
}

function searchMasses($args, $offset = 0, $limit = 20)
{
    $return = [
        'offset' => $offset,
        'limit' => $limit];
    $where = [" m.torles = '0000:00:00 00:00:00' "];
    $where[] = ' templomok.miseaktiv = 1 ';

    // templomok
    if (isset($args['templom']) && is_numeric($args['templom'])) {
        $where[] = ' m.tid = '.$args['templom'];
    } elseif ($args['varos'] != '' || $args['kulcsszo'] != '' || $args['egyhazmegye'] != '' || $args['gorog'] == 'gorog' || $args['hely'] != '' || $args['tnyelv'] != '0') {
        if ($args['varos'] == 'Budapest') {
            $args['varos'] = 'Budapest*';
        }

        $tmp = $args;
        if (isset($tmp['leptet'])) {
            unset($tmp['leptet']);
        }
        if (isset($tmp['min'])) {
            unset($tmp['min']);
        }
        $results = searchChurches($args, 0, 1000000);
        $tids[] = 0;
        if (isset($results['results'])) {
            foreach ($results['results'] as $r) {
                $tids[] = $r['id'];
            }
        }
        $where[] = ' m.tid IN ('.implode(',', $tids).')';
    }
    if ($args['gorog'] == 'gorog') {
        $where[] = 'egyhazmegye IN (17,18,34)';
    }
    // milyen nap
    if ($args['mikor'] == 'x') {
        $args['mikor'] = $args['mikordatum'];
    }
    $where[] = "m.nap IN ('".date('N', strtotime($args['mikor']))."',0)";

    // milyen időszakban
    $day = date('m-d', strtotime($args['mikor']));
    $where[] = "( ( m.tmp_datumtol <= '".$day."' AND '".$day."' <= m.tmp_datumig AND m.tmp_relation = '<'  )
    OR  ( ( m.tmp_datumig <= '".$day."' OR '".$day."' <= m.tmp_datumtol ) AND ( m.tmp_relation = '>' ) )
    OR ( m.tmp_datumig = '".$day."' AND m.tmp_datumig = '".$day."' AND  m.tmp_relation = '=' ) )";

    // milyen héten
    if (date('W', strtotime($args['mikor'])) & 1) {
        $parossag = 'pt';
    } else {
        $parossag = 'ps';
    }
    $hanyadikP = getWeekInMonth($args['mikor']);
    $hanyadikM = getWeekInMonth($args['mikor'], '-');
    $where[] = "( m.nap2 IN ('','0','".$hanyadikM."','".$hanyadikP."','".$parossag."') OR m.nap2 IS NULL)";

    // milyen órákban
    if ($args['mikor2'] == 'de') {
        $where[] = " m.ido < '12:00:01' AND m.ido > '00:00:01' ";
    } elseif ($args['mikor2'] == 'du') {
        $where[] = " m.ido > '11:59:59'";
    } elseif ($args['mikor2'] == 'x') {
        $idok = explode('-', $args['mikorido']);
        $where[] = " m.ido >= '".$idok[0].":00'";
        $where[] = " m.ido <= '".$idok[1].":00'";
    }

    // LANGUAGES
    $languages = unserialize(LANGUAGES);
    foreach ($languages as $abbrev => $attribute) {
        if ($attribute['abbrev'] != 'h') {
            $nothu[] = $abbrev;
        }
    }
    if ($args['nyelv'] != '0' && $args['nyelv'] != '') {
        if ($args['nyelv'] == 'h') {
            $where[] = "( m.nyelv REGEXP '(^|,)(".$args['nyelv'].')([0]{0,1}|'.$hanyadikP.'|'.$hanyadikM.'|'.$parossag.")(,|$)' OR
                templomok.orszag = 12 AND m.nyelv NOT REGEXP '(^|,)(".implode('|', $nothu).')([0]{0,1}|'.$hanyadikP.'|'.$hanyadikM.'|'.$parossag.")(,|$)' )";
        } else {
            $where[] = "( m.nyelv REGEXP '(^|,)(".$args['nyelv'].')([0]{0,1}|'.$hanyadikP.'|'.$hanyadikM.'|'.$parossag.")(,|$)' )";
        }
    }

    // ATTRIBUTES
    $attributes = unserialize(ATTRIBUTES);

    // age group (checkbox)
    if (isset($args['kor'])) {
        foreach ($attributes as $abbrev => $attribute) {
            if ($attribute['group'] == 'age') {
                $ages[] = $abbrev;
            }
        }
        $wherekor = [];
        foreach ($args['kor'] as $kor) {
            if (in_array($kor, $ages)) {
                $wherekor[] = " m.milyen REGEXP '(^|,)(".$kor.')([0]{0,1}|'.$hanyadikP.'|'.$hanyadikM.'|'.$parossag.")(,|$)' ";
            } elseif ($kor == 'na') {
                $wherekor[] = " m.milyen NOT REGEXP '(^|,)(".implode('|', $ages).')([0]{0,1}|'.$hanyadikP.'|'.$hanyadikM.'|'.$parossag.")(,|$)' ";
            }
        }
        $where[] = ' ( '.implode(' OR ', $wherekor).') ';
    }

    // music group (chekbox)
    if (isset($args['zene'])) {
        foreach ($attributes as $abbrev => $attribute) {
            if ($attribute['group'] == 'music') {
                $musics[] = $abbrev;
            }
        }
        $wherezene = [];
        foreach ($args['zene'] as $zene) {
            if (in_array($zene, $musics)) {
                $wherezene[] = " m.milyen REGEXP '(^|,)(".$zene.')([0]{0,1}|'.$hanyadikP.'|'.$hanyadikM.'|'.$parossag.")(,|$)' ";
            } elseif ($zene == 'na') {
                $wherezene[] = " m.milyen NOT REGEXP '(^|,)(".implode('|', $musics).')([0]{0,1}|'.$hanyadikP.'|'.$hanyadikM.'|'.$parossag.")(,|$)' ";
            }
        }
        $where[] = ' ( '.implode(' OR ', $wherezene).') ';
    }

    // rite group (select)
    if ($args['ritus'] != '0') {
        if ($args['ritus'] == 'gor') {
            foreach ($attributes as $abbrev => $attribute) {
                if ($attribute['group'] == 'liturgy' && $attribute['isitmass'] == true && $attribute['abbrev'] != 'gor') {
                    $notgor[] = $abbrev;
                }
            }
            $where[] = "( m.milyen REGEXP '(^|,)(gor)([0]{0,1}|".$hanyadikP.'|'.$hanyadikM.'|'.$parossag.")(,|$)' OR
                        ( egyhazmegye IN (17,18,34) AND m.milyen NOT REGEXP '(^|,)(".implode('|', $notgor).')([0]{0,1}|'.$hanyadikP.'|'.$hanyadikM.'|'.$parossag.")(,|$)' ) )";
        } elseif ($args['ritus'] == 'rom') {
            foreach ($attributes as $abbrev => $attribute) {
                if ($attribute['group'] == 'liturgy' && $attribute['isitmass'] == true && $attribute['abbrev'] != 'rom') {
                    $notrom[] = $abbrev;
                }
            }
            $where[] = "( (m.milyen NOT REGEXP '(^|,)(".implode('|', $notrom).')([0]{0,1}|'.$hanyadikP.'|'.$hanyadikM.'|'.$parossag.")(,|$)' AND egyhazmegye NOT IN (17,18,34)) OR
                        ( egyhazmegye IN (17,18,34) AND m.milyen REGEXP '(^|,)(rom)([0]{0,1}|".$hanyadikP.'|'.$hanyadikM.'|'.$parossag.")(,|$)' ) )";
        } else {
            $where[] = " m.milyen REGEXP '(^|,)(".$args['ritus'].')([0]{0,1}|'.$hanyadikP.'|'.$hanyadikM.'|'.$parossag.")(,|$)' ";
        }
    }

    // liturgy (not mass) group (checkbox/radio)
    $not = $only = [];
    foreach ($attributes as $abbrev => $attribute) {
        if ($attribute['group'] == 'liturgy' && $attribute['isitmass'] == false) {
            $not[$abbrev] = $abbrev;
        }
    }
    if (isset($args['liturgy'])) {
        foreach ($args['liturgy'] as $liturgy) {
            if (isset($not[$liturgy])) {
                unset($not[$liturgy]);
            }
        }
    }
    if (count($not) > 0) {
        $where[] = " m.milyen NOT REGEXP '(^|,)(".implode('|', $not).')([0]{0,1}|'.$hanyadikP.'|'.$hanyadikM.'|'.$parossag.")(,|$)' ";
    }

    $select = "SELECT m.*,templomok.nev,templomok.ismertnev,templomok.varos \nFROM misek m \n";
    $select .= " LEFT JOIN templomok ON m.tid = templomok.id \n";

    // Tudjuk meg, hogy hány templomban van megfelelő összesen
    $query = "SELECT count(*) as sum FROM ( \n";
    $query .= $select;
    if (count($where) > 0) {
        $query .= ' WHERE '.implode("\n AND ", $where);
    }
    $query .= "\n GROUP BY tid \n";
    $query .= ') groups ;';

    $lekerdez = DB::select($query);

    $return['sum'] = $lekerdez[0]->sum;

    // Akkor jöhet a limitált csoportos lekérdezés, mert az jó
    $query = $select;
    $query .= ' JOIN ( ';
    $query .= $select;
    if (count($where) > 0) {
        $query .= ' WHERE '.implode(' AND ', $where);
    }
    $query .= " GROUP BY tid \n";
    $query .= ' ORDER BY ido, templomok.varos, templomok.nev ';
    $query .= ' LIMIT '.$offset.','.$limit;
    $query .= ') groups ON groups.tid = m.tid ';
    if (count($where) > 0) {
        $query .= ' WHERE '.implode(' AND ', $where);
    }
    $query .= ' ORDER BY ido, templomok.varos, templomok.nev ';

    // echo $query;
    $lekerdez = DB::select($query);
    $masses = [];

    foreach ($lekerdez as $row) {
        $row = (array) $row;
        if ($row['tmp_datumtol'] == $row['tmp_datumig']) {
            $type = 'particulars';
        } else {
            $type = 'periods';
        }
        $masses[$row['tid']][$type][$row['idoszamitas']][] = $row;
    }

    // use particulars only, if we can
    foreach ($masses as $tid => $church) {
        if (array_key_exists('particulars', $church)) {
            $masses[$tid] = $church['particulars'];
        } elseif (array_key_exists('periods', $church)) {
            $masses[$tid] = $church['periods'];
        }
    }
    // weight
    foreach ($masses as $tid => $periods) {
        $weight = 0;
        $tmp = [];
        foreach ($periods as $period) {
            $m = array_shift(array_values($period));
            $w = $m['weight'];
            if ($w == '') {
                $w = 0;
            }
            if ($w >= $weight) {
                $tmp = $period;
                $weight = $w;
            }
        }
        $return['churches'][$tid] = array_shift(array_values($tmp)); // ezt szebben is lehetne
        $return['churches'][$tid]['masses'] = $tmp;
    }

    // echo "<pre>".print_r($return,1); exit;
    return $return;
}

function getWeekInMonth($date, $order = '+')
{
    $num = 0;
    if ($order == '+') {
        for ($i = 0; $i < 6; ++$i) {
            if (date('m', strtotime($date)) == date('m', strtotime($date.' -'.$i.' week'))) {
                ++$num;
            }
        }
    }
    if ($order == '-') {
        for ($i = 0; $i < 6; ++$i) {
            if (date('m', strtotime($date)) == date('m', strtotime($date.' +'.$i.' week'))) {
                --$num;
            }
        }
    }

    return $num;
}

function generateMassTmp($where = false)
{
    global $config;

    $results = DB::table('misek')
            ->select('id', 'tol', 'ig')
            ->where('torles', '0000-00-00 00:00:00');
    if ($where != false) {
        $results = $results->whereRaw($where);
    }
    $results = $results->get();
    foreach ($results as $row) {
        if ($row->tol == '') {
            $row->tol = '01-01';
        }
        $row->tmp_datumtol = event2Date($row->tol);
        if ($row->ig == '') {
            $row->ig = '12-31';
        }
        $row->tmp_datumig = event2Date($row->ig);
        if ($row->tmp_datumig > $row->tmp_datumtol) {
            $row->tmp_relation = '<';
        } elseif ($row->tmp_datumtol == $row->tmp_datumig) {
            $row->tmp_relation = '=';
        } else {
            $row->tmp_relation = '>';
        }

        DB::table('misek')
                ->where('id', $row->id)
                ->update(collect($row)->toArray());
    }
}

function widget_miserend($args)
{
    global $twig, $config;
    $tid = $args['tid'];
    $vars = App\Legacy\Model\Church::find($tid)->toArray();
    if ($vars == []) {
        $html = 'Nincs ilyen templom.';
    } else {
        $vars['miserend'] = getMasses($tid);

        if ($args['misemegj'] == 'off') {
            unset($vars['misemegj']);
        }
        $html = $twig->render('widget_massschedule.twig', $vars);
    }

    if (!isset($args['callback'])) {
        return $html;
    } else {
        return $args['callback'].'('.json_encode(['html' => $html]).')';
    }
}

function feltoltes_block()
{
    return []; // TODO restore

    $user = $this->getSecurity()->getUser();

    if (!isset($user->responsibilities['church']['allowed'])) {
        return false;
    }

    $allowed = $user->responsibilities['church']['allowed'];
    $ids = [];
    foreach ($allowed as $church) {
        $ids[] = $church->church_id;
    }
    $churches = App\Legacy\Model\Church::whereIn('id', $ids)->get();

    if (count($churches) == 0) {
        return;
    }

    $kod_tartalom = '<ul>';
    foreach ($churches as $church) {
        $jelzes = '';
        if ($church->eszrevetel == 'u') {
            $jelzes .= "<a href=\"javascript:OpenScrollWindow('/templom/".$church->id."/eszrevetelek',550,500);\"><img src=/img/csomag.gif title='Új észrevételt írtak hozzá!' align=absmiddle border=0></a> ";
        } elseif ($church->eszrevetel == 'i') {
            $jelzes .= "<a href=\"javascript:OpenScrollWindow('/templom/".$church->id."/eszrevetelek',550,500);\"><img src=/img/csomag1.gif title='Észrevételek!' align=absmiddle border=0></a> ";
        } elseif ($church->eszrevetel == 'f') {
            $jelzes .= "<a href=\"javascript:OpenScrollWindow('/templom/".$church->id."/eszrevetelek',550,500);\"><img src=/img/csomagf.gif title='Észrevétel javítása folyamatban!' align=absmiddle border=0></a> ";
        } else {
            $jelzes = '';
        }

        $kod_tartalom .= "\n<li>$jelzes<a href='/templom/".$church->id."/edit' class=link_kek title='".$church->varos."'>".$church->nev.'</a></li>';
    }

    $kod_tartalom .= "\n<li><a href='/user/maintainedchurches' class=felsomenulink>Teljes lista...</a></li>";
    $kod_tartalom .= '</ul>';

    return $kod_tartalom;
}

function addMessage($text, $severity = false)
{
    return App\Legacy\Message::add($text, $severity);
}

function copyArrayToObject($array, &$object)
{
    foreach ($array as $key => $value) {
        $object->$key = $value;
    }
}

function br2nl($string)
{
    return preg_replace('/\<br(\s*)?\/?\>/i', \PHP_EOL, $string);
}

function idoszak($i)
{
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

function callPageFake($uri, $post, $phpinput = [])
{
    stream_wrapper_unregister('php');
    stream_wrapper_register('php', 'MockPhpStream');
    file_put_contents('php://input', json_encode($phpinput));
    $_REQUEST = array_merge($_REQUEST, $post);

    ob_start();
    include $uri;
    $page = ob_get_contents();
    ob_end_clean();

    stream_wrapper_restore('php');

    return $page;
}

function file_exists_ci($fileName)
{
    if (file_exists($fileName)) {
        return $fileName;
    }
    $pattern = __DIR__.'/classes';
    $files = [];
    for ($i = 0; $i < 5; ++$i) {
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

function printr($variable)
{
    echo '<pre>'.print_r($variable, 1).'</pre>';
}

function overrideArray(&$orig, $new)
{
    foreach ($new as $k => $n) {
        if (!is_array($n)) {
            $orig[$k] = $n;
        } else {
            overrideArray($orig[$k], $n);
        }
    }
}
