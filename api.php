<?php

include_once('load.php');

ini_set('memory_limit', '256M');

try {    
    $action = \Request::SimpletextRequired('q');
} catch (Exception $e) {

    dieJsonException($e->getMessage());
}

try {
    switch ($action) {
        case 'login':
            $api = new Api\Login();
            break;
        case 'user':
            $api = new Api\User();
            break;
        case 'favorites':
            $api = new Api\Favorites();
            break;

        case 'report':
            //TODO: Api\Report();
            break;
        
        case 'updated':
            $api = new Api\Updated();
            break;

        case 'table':
            $api = new Api\Table();
            break;
        case 'sqlite':
            $api = new Api\Sqlite();
            break;

        default:
            throw new Exception("API action '$action' is not supported.");
    }

    $api->run();
    $api->printOutput();
} catch (Exception $e) {

    $api->printException($e);
}

if ($action === 'report') {
    $input = getInputJSON();

    if (!is_array($input)) {
        echo json_encode(array('error' => 1, 'text' => 'Nem kaptunk adatot.'));
        exit;
    }
    if (
            !isset($input['tid']) OR ! is_numeric($input['tid'])
            OR ! isset($input['pid']) OR ! in_array($input['pid'], array(0, 1, 2))
            OR ( $input['pid'] == 2 AND ! isset($input['text']) )
            OR ( isset($input['timestamp']) AND ! is_numeric($timestamp) )
    ) {
        echo json_encode(array('error' => 1, 'text' => 'Hibás formázású adatot kaptunk.'));
        exit;
    }
    if ($v > 3 AND ! isset($input['dbdate'])) {
        echo json_encode(array('error' => 1, 'text' => 'Hiányzik az adatbázis frissítettsége (dbdate).'));
        exit;
    }

    if (!isset($input['text']))
        $input['text'] = "";
    else
        $input['text'] = sanitize($input['text']);
    if (!isset($input['email']))
        $input['email'] = "";
    else
        $input['email'] = sanitize($input['email']);
    if (isset($input['dbdate']))
        $input['dbdate'] = sanitize($input['dbdate']);

    if (isset($input['token']) AND $v >= 4) {
        if (!$token = validateToken($input['token'])) {
            echo json_encode(array('error' => 1, 'text' => 'Érvénytelen token.'));
            exit;
        }
        global $user;
        $user = new User($token['uid']);
        $input['email'] = $user->email;
        $input['name'] = $user->nev;
    }


    $input['v'] = $v;

    $remark = new Remark();
    $remark->tid = $input['tid'];

    if (!isset($input['name']))
        $remark->name = "Mobil felhasználó";
    if ($input['email'] != '')
        $remark->email = $input['email'];
    if (isset($input['timestamp']))
        $remark->timestamp = $input['timestamp'];

    $remark->text = "Mobilalkalmazáson keresztül érkezett információ:\n" . $input['text'] . "\n <i>verzió:" . $v . ", pid:" . $input['pid'] . "</i>";
    if (isset($input['dbdate'])) {
        if (!is_numeric($input['dbdate']))
            $input['dbdate'] = strtotime($input['dbdate']);
        $remark->text .= "<i>, adatbázis: " . date("Y-m-d H:i", $input['dbdate']) . "</i>";
        $templom = getchurch($input['tid']);
        $updated = strtotime($templom['frissites']);

        if ($input['dbdate'] < $updated) {
            //echo date('Y-m-d',$updated)."-".date('Y-m-d',$input['dbdate']);	
            $remark->text .= "\n\n<strong>Figyelem! Elavult adatok alapján történt a bejelentés!</strong>";
        }
    }
    if ($user->uid > 0)
        $user->active();
    $remark->save();
    $remark->emails();
    echo json_encode(array('error' => 0, 'text' => 'Köszönjük. Elmentettük.'));

    exit;
}
?>