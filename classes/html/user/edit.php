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
        
        $newuser = new \User(isset($_REQUEST['edituser']['uid']) ? $_REQUEST['edituser']['uid'] : false);
        
        if ((!isset($_REQUEST['terms']) OR $_REQUEST['terms'] != 1 ) AND $newuser->uid == 0 AND $user->uid == 0) {
            addMessage("El kell fogadni a <i>Házirendet és szabályzatot</i>!", 'danger');
            return false;
        } else {
            try {
                if($newuser->submit($_REQUEST['edituser'])) {                
                    if ($user->uid < 1) {
                        global $config;                    
                        //require_once('moduls/miserend.php');
                        //$tartalom = miserend_index();
                        $this->newusercreated = true;
                    } else {
                        $this->uid = $newuser->uid;
                    }
                    return true;
                } else {
                    return false;
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

        //Ha folyamatban van meglévő felhasználó szerkesztéss, akkor azokat az adatokat tesszük be
        if (is_array($uid)) {
            $edituser = new \User();
            foreach ($uid as $key => $value) {
                $edituser->$key = $value;
            }
        //Ha folyamatban van új felszanáló szerkesztése
        } elseif ($user->uid == 0 AND isset($_REQUEST['edituser'])) {    
            $edituser = new \User();
            foreach ($_REQUEST['edituser'] as $key => $value) {
                $edituser->$key = $value;
            }

            
        } else
            $edituser = new \User($uid);

        if ($edituser->uid == 0 AND $user->uid == 0 AND preg_match('/\/new$/i',$_SERVER['REQUEST_URI'])) {                                
            $vars['title'] = "Regisztráció";
            $vars['new'] = true;
            $vars['helptext'] = true;
        } elseif ($edituser->uid == 0 AND $user->uid > 0) {
            $vars['title'] = "Új felhasználó";
            if (!$user->checkRole('user')) {
                addMessage("Nincs megfelelő jogosultságod!", "danger");
                $vars['accessdenied'] = true;        
            }
            $vars['new'] = true;
        } else {
            $vars['title'] = "Adatok módosítása";
            if (!$user->checkRole('user') AND $user->uid != $edituser->uid) {
                addMessage("Nincs megfelelő jogosultságod!", "danger");
                $vars['accessdenied'] = true;                               
            } elseif($edituser->uid == 0 AND $user->uid == 0 ) {
                addMessage("A személyes adatok módosításához be kell előbb lépni.", "warning");
                $vars['needtologin'] = true;
            }
            $vars['edit'] = true;
        }

        if ($edituser->username == '*vendeg*')
            $edituser->username = false;
        if ($edituser->nickname == '*vendég*')
            $edituser->nickname = false;

        if($user->loggedin)
            $edituser->getRemarks(6);

        if ($user->checkRole('user')) {
            $user->isadmin = true;
        }

        $vars['edituser'] = $edituser;

        foreach ($vars as $key => $value) {
            $this->$key = $value;
        }
        
        if($user->loggedin)
            $this->edituser->processResponsabilities();        
    }

}
