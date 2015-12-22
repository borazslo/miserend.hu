<?php

function user_edit($uid = false) {
    global$user, $m_id;

    $vars['template'] = 'user_form';
    $vars['m_id'] = $m_id;

    $vars['roles'] = array();
    $query = "select jogkod from modulok where jogkod !=''";
    $lekerdez = mysql_query($query);
    while (list($jogkod) = mysql_fetch_row($lekerdez)) {
        $vars['roles'][$jogkod] = $jogkod;
    }

    //Ha folyamatban van a szerkesztés, akkor azokat az adatokat tesszük be
    if (is_array($uid)) {
        $edituser = new User();
        foreach ($uid as $key => $value) {
            $edituser->$key = $value;
        }
    } else
        $edituser = new User($uid);

    if ($edituser->uid == 0 AND $user->uid == 0) {
        $vars['title'] = "Regisztráció";
        $vars['new'] = true;
        $vars['helptext'] = true;
    } elseif ($edituser->uid == 0 AND $user->uid > 0) {
        $vars['title'] = "Új felhasználó";
        if (!$user->checkRole('user')) {
            addMessage("Nincs megfelelő jogosultságod!", "danger");
            $vars['template'] = 'layout';
            return $vars;
        }
        $vars['new'] = true;
    } else {
        $vars['title'] = "Adatok módosítása";
        if (!$user->checkRole('user') AND $user->uid != $edituser->uid) {
            addMessage("Nincs megfelelő jogosultságod!", "danger");
            $vars['template'] = 'layout';
            return $vars;
        }
        $vars['edit'] = true;
    }

    if ($edituser->username == '*vendeg*')
        $edituser->username = false;
    if ($edituser->nickname == '*vendég*')
        $edituser->nickname = false;

    $edituser->getRemarks(6);

    if ($user->checkRole('user')) {
        $user->isadmin = true;
    }

    $vars['edituser'] = $edituser;

    return $vars;
}

function user_del($uid) {
    global $m_id;

    $vars['title'] = 'Felhasználó törlése';

    $user2delete = new User($uid);
    if ($user2delete->uid == 0) {
        $kiir = "\n<span class=kiscim>Nincs ilyen felhasználó!</span>";
    } else {
        $kiir = "\n<span class=kiscim>Biztosan törölni akarod a következő felhasználót?</span>";
        $kiir.="\n<br><br><span class=alap>" . $user2delete->username . " (" . $user2delete->nev . ")</span>";
        $kiir.="<br><br><a href=?m_id=$m_id&m_op=delete&uid=$uid class=link>Igen</a> - <a href=?m_id=$m_id&m_op=mod class=link>NEM</a>";
    }
    $vars['content'] = $kiir;
    $vars['template'] = 'layout';

    return $vars;
}

switch ($m_op) {
    case 'index':
        $tartalom = user_edit($user->uid);
        break;

    case 'edit':
        $tartalom = user_edit($_REQUEST['uid']);
        break;

    case 'adding':
        $newuser = new User($_REQUEST['edituser']['uid']);


        if ($_REQUEST['terms'] != 1 AND $newuser->uid == 0 AND $user->uid == 0) {
            addMessage("El kell fogadni a <i>Házirendet és szabályzatot</i>!", 'danger');
            $tartalom = user_edit($_REQUEST['edituser']);
        } else {
            try {
                $newuser->submit($_REQUEST['edituser']);
                if ($user->uid < 1) {
                    require_once('moduls/miserend.php');
                    $tartalom = miserend_index();
                    //header("Location: http://miserend.hu/");
                } else
                    $tartalom = user_edit($newuser->uid);
            } catch (Exceptions $e) {
                printr($e);

                $tartalom = user_edit($_REQUEST['edituser']);
            }
        }

        break;

    case 'del':
        $tartalom = user_del($_REQUEST['uid']);
        break;

    case 'delete':
        if (is_numeric($_REQUEST['uid']) AND $user->checkRole('user') AND $user->uid != $_REQUEST['uid']) {
            $user2delete = new User($_REQUEST['uid']);
            $user2delete->delete();
            $tartalom = user_list();
        } else {
            //TODO: elegánsabb hibakezelést!
            addMessage('Hiányzó jogosultság miatt nem lehetséges a törlése!', danger);
            $tartalom['content'] = '';
            $tartalom['template'] = 'layout';
        }

        break;

    default :
        $tartalom = user_edit($user->uid);
        break;
}
?>