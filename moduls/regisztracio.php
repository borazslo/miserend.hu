<?php

function user_list() {
    global $db_name, $linkveg, $m_id, $sid, $_POST;

    $kulcsszo = $_REQUEST['kulcsszo'];
    $sort = $_REQUEST['sort'];
    if (empty($sort))
        $sort = 'lastactive desc';
    $adminok = $_REQUEST['adminok'];
    $limit = $_REQUEST['limit'];
    if (empty($limit))
        $limit = 50;

    $kiir.="\n<form method=post><input type=hidden name=sid value=$sid>";
    $kiir.="\n<input type=hidden name=m_id value=$m_id><input type=hidden name=m_op value=list>";
    $kiir.="\n<input type=text name=kulcsszo value='$kulcsszo' class=urlap size=20>";
    $kiir.="\n<select name=adminok class=urlap><option value=0>Mindenki</option>";
    $query = "select jogkod from modulok where jogkod!=''";
    $lekerdez = mysql_db_query($db_name, $query);
    while (list($jogkod) = mysql_fetch_row($lekerdez)) {
        $kiir.="\n<option value='$jogkod'";
        if ($adminok == $jogkod)
            $kiir.=' selected';
        $kiir.=">$jogkod</option>s";
    }
    $kiir.="\n</select>";

    $kiir.="\n<br><span class=alap>rendezés: </span><select name=sort class=urlap> ";
    $sortT['felhasználó név'] = 'login';
    $sortT['becenév'] = 'becenev';
    $sortT['név'] = 'nev';
    $sortT['utolsó belépés'] = 'lastlogin desc';
    $sortT['utolsó aktivitás'] = 'lastactive desc';
    $sortT['regisztráció'] = 'regdatum desc';



    foreach ($sortT as $kulcs => $ertek) {
        $kiir.="<option value='$ertek'";
        if ($ertek == $sort)
            $kiir.=' selected';
        $kiir.=">$kulcs</option>";
    }
    $kiir.="\n</select><input type=submit value=Lista class=urlap></form>";

    $vars['form'] = $kiir;

    if (!empty($kulcsszo)) {
        $feltetelT[] = "login like '%$kulcsszo%'";
        $feltetelT[] = "nev like '%$kulcsszo%'";
        $feltetelT[] = "email like '%$kulcsszo%'";
    }
    if (!empty($adminok)) {
        $feltetelT[] = "jogok like '%$adminok%'";
    }
    if (is_array($feltetelT))
        $feltetel = "where (" . implode(' or ', $feltetelT) . ')';

    $users = array();
    $query = "select * from user $feltetel order by $sort";
    $lekerdez = mysql_query($query);
    while ($user = mysql_fetch_assoc($lekerdez)) {

        if (preg_match('/^(lastlogin|lastactive|regdatum)/i', $sort, $match))
            $field = preg_replace(array('/ /i', '/-/i'), array('&nbsp;', '&#8209;'), $match[1]);
        else
            $field = 'lastlogin';

        $user['field'] = $user[$field];
        $users[$user['uid']] = $user;
    }

    $vars['field'] = $field;
    $vars['m_id'] = $m_id;
    $vars['users'] = $users;
    $vars['template'] = "users_list";


    return $vars;
}

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
        $edituser = new stdClass();
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

function user_jelszo() {
    global $m_id;


    $szoveg = <<<EOD
			<p>Az alábbi két adat közül legalább az egyik kitöltése alapján a rendszer megpróbál azonosítani. Ha sikerül, akkor elküld a megadott (regisztrált!) email címre egy ÚJ jelszót.</sp>
			<form method="post">
				<input type=hidden name=m_op value=jelszokuld>
				<input type=hidden name=m_id value=$m_id>
				<div class="form-group">
					<label for="username">Felhasználónév</label>
					<input type="text" name="lnev" class="form-control" id="username" placeholder="Felhasználónév">
				</div>
				<div class="form-group">
					<label for="email">Email cím</label>
					<input type="email" name="mail" class="form-control" id="email" placeholder="Email">
				</div>
				<button type="submit" class="btn btn-default">Kérem a jelszót</button>
			</form>
EOD;

    $vars['title'] = 'Jelszó emlékeztető';
    $vars['content'] = $szoveg;
    $vars['template'] = 'layout';

    return $vars;
}

function user_jelszokuld() {

    $lnev = $_POST['lnev'];
    $mail = $_POST['mail'];

    $user = new User($mail);

    if (!empty($lnev))
        $userByNev = new User($lnev);
    if (!empty($mail))
        $userByMail = new User($mail);

    if (!empty($lnev) AND ! empty($mail) AND $userByMail->uid != $userByNev) {
        addMessage('A megadott adatok alapján nem találtunk felhasználót.', 'danger');
        return user_jelszo();
    }

    if ($userByNev->uid > 0)
        $user = $userByNev;
    elseif ($userByMail > 0)
        $user = $userByMail;
    else {
        addMessage('A megadott adatok alapján nem találtunk felhasználót.', 'danger');
        return user_jelszo();
    }

    $email = new Mail();
    $email->subject = "Jelszó emlékeztető - Virtuális Plébánia Portál";

    $newpassword = $user->generatePassword();
    $user->newPassword($newpassword);

    $email->content = "Kedves " . $user->username . "!<br/><br/>";
    $email->content.="\n\nKérésedre küldjük a bejelentkezéshez szükséges újjelszót:";
    $email->content.="\n" . $newpassword . "<br/><br>";
    $email->content.="Kérjük mihamarabb változtasd meg a jelszót.<br/><br/>";
    $email->content.="\n\nVPP \nhttp://www.plebania.net";

    $email->to = $user->email;
    $email->send();

    addMessage("Az új jelszót elküldtük a regisztrált emailcímre. Kérjük lépjen be, és mihamarabb módosítsa.", 'success');
    header('Location http://miserend.hu');
    return true;
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
            if ($newuser->submit($_REQUEST['edituser'])) {
                if ($user->uid < 1) {
                    require_once('moduls/miserend.php');
                    $tartalom = miserend_index();
                    //header("Location: http://miserend.hu/");
                } else
                    $tartalom = user_edit($newuser->uid);
            } else {
                $tartalom = user_edit($_REQUEST['edituser']);
            }
        }

        break;

    case 'jelszo':
        $tartalom = user_jelszo();
        break;

    case 'jelszokuld':
        $tartalom = user_jelszokuld();
        break;

    case 'list':
        if ($user->checkRole('user'))
            $tartalom = user_list();
        else {
            addMessage('Nincs jogosultságod megnézni a felhasználók listáját.', 'warning');
            $tartalom = array('title' => 'Felhasználók listája');
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
}
?>