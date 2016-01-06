<?php

namespace Html\User;

class Edit extends \Html\Html {

    public function __construct() {
        global$user;

        $this->uid = \Request::IntegerwDefault('uid', $user->uid);

        $isForm = \Request::Text('submit');
        if ($isForm) {
            $this->modify();
        }
        $this->preparePage();
    }

    function modify() {
        global $user;
        
        $newuser = new \User($_REQUEST['edituser']['uid']);

        if ($_REQUEST['terms'] != 1 AND $newuser->uid == 0 AND $user->uid == 0) {
            addMessage("El kell fogadni a <i>Házirendet és szabályzatot</i>!", 'danger');            
            return false;            
            
        } else {
            try {
                $newuser->submit($_REQUEST['edituser']);
                if ($user->uid < 1) {
                    global $config;
                    //require_once('moduls/miserend.php');
                    //$tartalom = miserend_index();
                    header("Location: ".$config['path']['domain']);
                } else {
                    $_REQUEST['uid'] = $newuser->uid;
                    return true;
                }
            } catch (\Exceptions $e) {
                addMessage($e->getMessage());
                return false;                
            }
        }
    }

    function preparePage() {
        global $user;
        $uid = $this->uid;
        $roles = unserialize(ROLES);
        $vars['roles'] = array();
        foreach ($roles as $role) {
            $vars['roles'][$role] = $role;
        }

        //Ha folyamatban van a szerkesztés, akkor azokat az adatokat tesszük be
        if (is_array($uid)) {
            $edituser = new \User();
            foreach ($uid as $key => $value) {
                $edituser->$key = $value;
            }
        } else
            $edituser = new \User($uid);

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

        foreach ($vars as $key => $value) {
            $this->$key = $value;
        }
    }

}
