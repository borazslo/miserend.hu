<?php

namespace App\Html\User;

use App\Html\Html;
use App\User as AppUser;
use App\Request;

class Edit extends Html {

    public function __construct() {
        global $user;

        $this->uid = Request::IntegerwDefault('uid', $user->uid);

        $isForm = Request::Text('submit');
        if ($isForm) {
            if ($this->modify()) {
                /* Ha az aktuális felhasználót frissítettük, akkor be kell töltenünk újra a felhasználót a friss adatokkal */
                if($this->uid == $user->uid) {
                    $user2 = new AppUser($this->uid);
                    $user = $user2->load();
                }
            }
        }
        $this->preparePage();
    }

    function modify(): bool
    {
        global $user;
        
        $newuser = new AppUser(uid: isset($_REQUEST['edituser']['uid']) ? $_REQUEST['edituser']['uid'] : false);
        
        if ((!isset($_REQUEST['terms']) OR $_REQUEST['terms'] != 1 ) AND $newuser->uid == 0 AND $user->uid == 0) {

            addMessage("El kell fogadni a <i>Házirendet és szabályzatot</i>!", 'danger');
            return false;

        } else if ((!isset($_REQUEST['robot']) OR $_REQUEST['robot'] != 'MKPK' ) AND $newuser->uid == 0 AND $user->uid == 0) {

            addMessage("Sajnos, ha nem válaszol az MKPK-val kapcsolatos kérdésre, akkor önt robotnak nézzük és nem regisztrálhat!", 'danger');
            return false;

        } else {
            try {
                // Jogokat nem adhat akárki, de lemondhat akráki.
                if(!$user->checkRole('user') AND isset($_REQUEST['edituser']['roles'])) {
                    foreach($_REQUEST['edituser']['roles'] as $key => $value) {
                        /* Ha eddig nem volt joga, de a formban joga lenne, akkor baj van */
                        if(!in_array($key,$user->roles) AND $key == $value) {
                            $_REQUEST['edituser']['roles'][$key] = false;
                            addMessage('A „'.$key.'” jogosultság megadásához nem rendelkezel elég jogosultsággal.','danger');
                        }
                    }
                }
                
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


        //Ha folyamatban van új felszanáló szerkesztése
        if ($user->uid == 0 AND isset($_REQUEST['edituser'])) {    
            $edituser = new \App\User();
            foreach ($_REQUEST['edituser'] as $key => $value) {
                $edituser->$key = $value;
            }

            
        } else
            $edituser = new \App\User($uid);

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
