<?php

class User {

    function __construct($uid = false) {
        if (isset($uid) AND ! is_numeric($uid) AND filter_var($uid, FILTER_VALIDATE_EMAIL)) {
            $query = "SELECT * FROM user WHERE email = '" . sanitize($uid) . "' AND ok = 'i' LIMIT 1";
            $result = mysql_query($query);
            $x = mysql_fetch_assoc($result);
            if (is_array($x)) {
                foreach ($x as $key => $value) {
                    $this->$key = $value;
                }
                $this->username = $x['login'];
                $this->nickname = $x['becenev'];
                $this->name = $x['nev'];
                $this->roles = explode('-', trim($this->jogok, " \t\n\r\0\x0B-"));
                $this->getResponsabilities();
                return true;
            } else {
                //TODO: kitalálni mit csináljon, ha  nincs uid-jű user. Legyen vendég?
                // There is no user with this uid;
                $uid = 0;
                //return false;
            }
        } elseif (isset($uid) AND ! is_numeric($uid)) {
            $query = "SELECT * FROM user WHERE login = '" . sanitize($uid) . "' AND ok = 'i' LIMIT 1";
            $result = mysql_query($query);
            $x = mysql_fetch_assoc($result);
            if (is_array($x)) {
                foreach ($x as $key => $value) {
                    $this->$key = $value;
                }
                $this->username = $x['login'];
                $this->nickname = $x['becenev'];
                $this->name = $x['nev'];
                $this->roles = explode('-', trim($this->jogok, " \t\n\r\0\x0B-"));
                $this->getResponsabilities();
                return true;
            } else {
                //TODO: kitalálni mit csináljon, ha  nincs uid-jű user. Legyen vendég?
                // There is no user with this uid;
                $uid = 0;
                //return false;
            }
        }

        if (!isset($uid) OR ! is_numeric($uid) OR $uid == 0) {
            $this->loggedin = false;
            $this->uid = 0;
            $this->username = '*vendeg*';
            $this->nickname = '*vendég*';
        } else {
            $query = "SELECT * FROM user WHERE uid = $uid AND ok = 'i' LIMIT 1";
            $result = mysql_query($query);
            $x = mysql_fetch_assoc($result);
            if (is_array($x)) {
                foreach ($x as $key => $value) {
                    $this->$key = $value;
                }
                $this->username = $x['login'];
                $this->nickname = $x['becenev'];
                $this->name = $x['nev'];
                $this->roles = explode('-', trim($this->jogok, " \t\n\r\0\x0B-"));
                $this->getResponsabilities();
                return true;
            } else {
                //TODO: kitalálni mit csináljon, hogy nincs uid-jű user. Legyen vendég?
                // There is no user with this uid;
                return false;
            }
        }
    }

    function checkRole($role = false) {
        if ($role == false)
            return true;

        if ($role == '"any"' OR $role == "'any'") {
            if (trim(preg_replace('/-/i', '', $this->jogok)) != '')
                return true;
            else
                return false;
        } elseif (preg_match('/^ehm:([0-9]{1,3})$/i', $role, $match)) {
            $query = "SELECT * FROM egyhazmegye WHERE id = " . $match[1] . " AND felelos = '" . $this->username . "' LIMIT 1";
            $result = mysql_query($query);
            if (mysql_num_rows($result) == 1)
                return true;
            else
                return false;
        } elseif (preg_match('/(^|-)' . $role . '(-|$)/i', $this->jogok)) {
            return true;
        } else
            return false;
    }

    function getResponsabilities() {
        $this->responsible = array(
            'diocese' => array(),
            'church' => array()
        );
        if ($this->uid > 0) {
            $query = "SELECT id FROM egyhazmegye WHERE ok = 'i' AND felelos = '" . $this->username . "' ";
            $result = mysql_query($query);
            while ($ehm = mysql_fetch_assoc($result)) {
                $this->responsible['diocese'][] = $ehm['id'];
            }
            $query = "SELECT id FROM templomok WHERE ok = 'i' AND letrehozta = '" . $this->username . "' ";
            $result = mysql_query($query);
            while ($church = mysql_fetch_assoc($result)) {
                $this->responsible['church'][] = $church['id'];
            }
        }
    }

    function getRemarks($limit = false, $ago = false) {
        if ($limit == false OR ! is_numeric($limit))
            $limit = 5;

        if ($ago != false)
            $datum = " AND datum > '" . date('Y-m-d H:i:s', strtotime("-" . $ago)) . "' ";
        else
            $datum = '';

        $query = "SELECT id FROM eszrevetelek WHERE 
				(login = '" . $this->username . "' OR email = '" . $this->email . "') 
				" . $datum . " ORDER BY datum desc";
        $result = mysql_query($query);
        $this->remarksCount = mysql_num_rows($result);
        $query .= " LIMIT " . $limit . ";";
        $result = mysql_query($query);
        while ($remark = mysql_fetch_assoc($result)) {
            $this->remarks[$remark['id']] = new Remark($remark['id']);
        }
        return true;
    }

    function submit($vars) {
        $return = true;

        if (isset($vars['uid']) AND ! is_numeric($vars['uid']) AND $vars['uid'] != '') {
            addMessage('Nincs ilyen felhasználónk!', 'danger');
            return false;
        }

        $dangers = array(
            'uid' => 'Probléma támadt az azonosítóval!',
            'username' => 'Probléma a felhasználónévvel! (A felhasználó nevet nem lehet megváltoztatni és nem lehet olyan név, ami már használatban van.)',
            'nickname' => 'Probléma a becenévvel!',
            'name' => 'Probléma a névvel!',
            'email' => 'Nem megfelelő email cím!',
            'volunteer' => 'Hibás értéke van az önkéntességnek!',
            'ok' => 'Csak az „i” = „igen” és a „n” = „nem” elfogadható érték az aktivitást illetően!',
            'roles' => 'Hibás formátumú jogkörök!',
        );

        foreach (array('uid', 'username', 'nickname', 'name', 'email', 'volunteer', 'ok', 'roles') as $input) {
            if (isset($vars[$input])) {
                if (!$this->presave($input, $vars[$input])) {
                    $return = false;
                    addMessage($dangers[$input], 'danger');
                }
            }
        }

        if ($vars['password1'] != '' OR $vars['password2'] != '') {
            if ($vars['password1'] != $vars['password2'] OR $vars['password1'] == '') {
                addMessage('A két jelszó nem egyezik meg egymással', 'danger');
                $return = false;
            } else {
                if (!$this->presave('password', $vars['password1'])) {
                    $return = false;
                    addMessage('Sajnos nem megfelelő a jelszó!', 'danger');
                }
            }
        }

        if ($return == false)
            return false;

        if (!$vars['uid']) {
            $pwd = $this->generatePassword();
            $this->presave('password', $pwd);

            //email küldése
            $email = new Mail();
            $email->subject = 'Regisztráció - Virtuális Plébánia Portál';
            $email->content = "Kedves " . $this->username . "!<br/><br/>";
            $email->content = "Köszöntünk a Virtuális Plébánia Portál felhasználói között!<br/><br/>";
            $email->content .="\n\nA belépéshez szükséges jelszó: $pwd<br/>";
            $email->content .="\nA belépést követően a BEÁLLÍTÁSOK menüben kérjük megváltoztatni a jelszót.<br><br/>";
            $email->content .="\n\nVPP \nwww.plebania.net";
            $email->to = $this->presaved['email'];
            if ($email->send())
                addMessage("Elküldtük az emailt az új regisztrációról.", "success");
        }

        if (!$this->save()) {
            addMessage("Nem sikerült elmenteni. Pedig minden rendben volt előtte.", "warning");
            return false;
        } else {
            if (!$vars['uid'])
                addMessage("A felhasználót sikeresen létrehoztuk.", "success");
            else
                addMessage("A változásokat elmentettük.", "success");
        }
        return true;
    }

    function presave($key, $val) {
        if (!isset($this->presaved))
            $this->presaved = array();
        //TODO: check duplicate for: logn + email
        //TODO: van, amit ne engedjen, csak, amikor még tök új a cuccos.
        //TODO: törölhető oszlop: ismerosok, baratok, regip, lastip, log, adminmegj,atvett
        //TODO: a nickname - becenev / name - nev esetén ez nem segít, bár nem sok dupla munka azért
        //TODO: elrontja ...
        //if($this->$key == $val) return true;
        //TODO: szóljon vissza a kötelező
        if ($val == '' AND in_array($key, array('username', 'login', 'email'))) {
            return false;
        }

        if ($key == 'uid') {
            if ($this->uid != $val)
                return false;
        } elseif (in_array($key, array('username', 'login'))) {
            if ($this->uid == 0) {
                if (!checkUsername($val))
                    return false;
                $this->presaved['login'] = sanitize($val);
            } elseif ($this->username != $val) {
                return false;
            }
        } elseif (in_array($key, array('jelszo', 'password'))) {
            $this->presaved['jelszo'] = password_hash($val, PASSWORD_BCRYPT);
        } elseif ($key == 'roles' OR $key == 'jogok') {
            if (!is_array($val))
                $val = array($val);
            foreach ($val as $k => $i)
                $val['jogok'] = trim(sanitize($i), "-");
            $val = array_unique($val);
            $this->presaved['jogok'] = implode('-', $val);
        } elseif ($key == 'nickname' or $key == 'becenev') {
            $this->presaved['becenev'] = sanitize($val);
        } elseif ($key == 'name' or $key == 'nev') {
            $this->presaved['nev'] = sanitize($val);
        } elseif ($key == 'ok') {
            $con = array(1 => 'i', 0 => 'n');
            if ($val == '')
                $this->presaved[$key] = 'n';
            elseif (in_array($val, array('i', 'n')))
                $this->presaved[$key] = $val;
            elseif (in_array($val, array(1, 0)))
                $this->presaved[$key] = $con[$val];
            else
                return false;
        } elseif ($key == 'volunteer') {
            if ($val == '')
                $this->presaved[$key] = 0;
            elseif (in_array($val, array(0, 1)))
                $this->presaved[$key] = $val;
            else
                return false;
        } elseif (in_array($key, array('letrehozta'))) {
            //TODO: túlzás lenne megnézni, hogy valódi name-e? (bár ha törölt... user...)
            $this->presaved[$key] = sanitize($val);
        } elseif (in_array($key, array('regdatum', 'lastlogin', 'lastactive'))) {
            if (is_numeric($val)) {
                $this->presaved[$key] = date('Y-m-d H:i:s', $val);
            } elseif (strtotime($val))
                $this->presaved[$key] = date('Y-m-d H:i:s', strtotime($val));
            else
                return false;
        } elseif ($key == 'email') {
            if (!filter_var($val, FILTER_VALIDATE_EMAIL))
                return false;

            //TODO: dupla email címeket kiszűrni
            $this->presaved[$key] = $val;
        } else
            return false;

        return true;
    }

    function save() {
        if (!$this->presaved)
            return false;

        //Set Deafult
        if ($this->uid < 1) {
            if (!isset($this->presaved['ok']))
                $this->presave('ok', 'i');
            if (!isset($this->presaved['regdatum']))
                $this->presave('regdatum', time());
            global $user;
            if (!isset($this->presaved['letrehozta']))
                $this->presave('letrehozta', $user->username);
        }

        foreach ($this->presaved as $key => $val) {
            $keys[] = $key;
            $vals[] = $val;
            $sets[] = $key . ' = "' . $val . '"';
        }

        if ($this->uid == 0 AND isset($this->presaved['login'])) {
            $query = "INSERT INTO user (" . implode(', ', $keys) . ") VALUES ('" . implode("', '", $vals) . "');";
            if (!mysql_query($query))
                return false;
            $this->uid = mysql_insert_id();
        } elseif ($this->uid > 0) {
            $query = "UPDATE user SET " . implode(', ', $sets) . " WHERE uid = " . $this->uid . " LIMIT 1;";
            if (!mysql_query($query))
                return false;
        }

        foreach ($this->presaved as $key => $val)
            $this->$key = $val;

        //TODO: ezt már egyszer leírtam
        $this->username = $this->login;
        $this->nickname = $this->becenev;
        $this->name = $this->nev;
        $this->roles = explode('-', trim($this->jogok, " \t\n\r\0\x0B-"));

        unset($this->presaved);

        return $this->uid;
    }

    function delete() {
        if ($this->uid == 0)
            return false;

        $query = "DELETE FROM user WHERE uid = " . $this->uid . " LIMIT 1";
        if (mysql_query($query)) {
            foreach ($this as $key => $value)
                unset($this->$key);
            $this->loggedin = false;
            $this->uid = 0;
            $this->username = '*vendeg*';
            $this->nickname = '*vendeg*';
            addMessage('Sikeresen töröltük a felhasználót.', 'success');
            return true;
        } else {
            addMessage('Hiba lépett fel a felhasználó törlése közben', 'danger');
            return false;
        }
    }

    function generatePassword($length = 8) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $count = mb_strlen($chars);

        for ($i = 0, $result = ''; $i < $length; $i++) {
            $index = rand(0, $count - 1);
            $result .= mb_substr($chars, $index, 1);
        }

        return $result;
    }

    function newPassword($text) {
        $this->presave('password', $text);
        $this->save();
    }

    function active() {
        mysql_query("UPDATE user SET lastactive = '" . date('Y-m-d H:i:s') . "' WHERE uid = " . $this->uid . " LIMIT 1;");
    }

    function getFavorites() {
        $favorites = array();
        $results = mysql_query("
	    	SELECT f.tid,t.nev,t.ismertnev,t.varos FROM favorites f
	    		LEFT JOIN templomok t ON t.id = f.tid
	    	WHERE t.ok = 'i' AND f.tid IS NOT NULL AND f.uid = " . $this->uid . "
	    	ORDER BY nev;");
        while ($row = mysql_fetch_row($results, MYSQL_ASSOC)) {
            $row['li'] = "<a class='link' href='?templom=" . $row['tid'] . "'>" . $row['nev'];
            if ($row['ismertnev'] != '')
                $row['li'] .= " (" . $row['ismertnev'] . ")";
            $row['li'] .= "</a>, " . $row['varos'];
            $favorites[$row['tid']] = $row;
        }
        sort($favorites);
        $this->favorites = $favorites;
        return $favorites;
    }

    function addFavorites($tids) {
        if (!is_array($tids))
            $tids = array($tids);
        foreach ($tids as $tid) {
            if (!is_numeric($tid))
                return false;
        }
        foreach ($tids as $key => $tid) {
            if (getChurch($tid) == array())
                unset($tids[$key]);
        }

        $query = "INSERT IGNORE INTO favorites (uid,tid) VALUES ";
        $values = array();
        foreach ($tids as $key => $tid) {
            $values[] = "(" . $this->uid . "," . $tid . ")";
        }
        $query .= implode(', ', $values) . ";";
        if (mysql_query($query))
            return true;
        else
            return false;
    }

    function removeFavorites($tids) {
        if (!is_array($tids))
            $tids = array($tids);
        foreach ($tids as $tid) {
            if (!is_numeric($tid))
                return false;
        }

        $query = "DELETE FROM favorites WHERE ";
        foreach ($tids as $key => $tid) {
            $query .= "( uid = " . $this->uid . " AND tid = " . $tid . ")";
            if ($key < count($tids) - 1)
                $query .= " OR ";
        }
        $query .= ";";
        if (mysql_query($query))
            return true;
        else
            return false;
    }

}
