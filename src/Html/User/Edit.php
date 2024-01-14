<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Html\User;

use App\Html\Html;
use App\Request;
use App\User as AppUser;

class Edit extends Html
{
    public function __construct()
    {
        global $user;

        $this->uid = Request::IntegerwDefault('uid', $user->uid);

        $isForm = Request::Text('submit');
        if ($isForm) {
            if ($this->modify()) {
                /* Ha az aktuális felhasználót frissítettük, akkor be kell töltenünk újra a felhasználót a friss adatokkal */
                if ($this->uid == $user->uid) {
                    $user2 = new AppUser($this->uid);
                    $user = $user2->load();
                }
            }
        }
        $this->preparePage();
    }

    public function modify(): bool
    {
        global $user;

        $newuser = new AppUser(uid: $_REQUEST['edituser']['uid'] ?? false);

        if ((!isset($_REQUEST['terms']) || 1 != $_REQUEST['terms']) && 0 == $newuser->uid && 0 == $user->uid) {
            addMessage('El kell fogadni a <i>Házirendet és szabályzatot</i>!', 'danger');

            return false;
        } elseif ((!isset($_REQUEST['robot']) || 'MKPK' != $_REQUEST['robot']) && 0 == $newuser->uid && 0 == $user->uid) {
            addMessage('Sajnos, ha nem válaszol az MKPK-val kapcsolatos kérdésre, akkor önt robotnak nézzük és nem regisztrálhat!', 'danger');

            return false;
        } else {
            try {
                // Jogokat nem adhat akárki, de lemondhat akráki.
                if (!$user->checkRole('user') && isset($_REQUEST['edituser']['roles'])) {
                    foreach ($_REQUEST['edituser']['roles'] as $key => $value) {
                        /* Ha eddig nem volt joga, de a formban joga lenne, akkor baj van */
                        if (!\in_array($key, $user->roles) && $key == $value) {
                            $_REQUEST['edituser']['roles'][$key] = false;
                            addMessage('A „'.$key.'” jogosultság megadásához nem rendelkezel elég jogosultsággal.', 'danger');
                        }
                    }
                }

                if ($newuser->submit($_REQUEST['edituser'])) {
                    if ($user->uid < 1) {
                        global $config;
                        // require_once('moduls/miserend.php');
                        // $tartalom = miserend_index();
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

    public function preparePage()
    {
        global $user;
        $uid = $this->uid;
        $roles = unserialize(ROLES);
        $vars['roles'] = [];
        foreach ($roles as $role) {
            $vars['roles'][$role] = $role;
        }

        // Ha folyamatban van új felszanáló szerkesztése
        if (0 == $user->uid && isset($_REQUEST['edituser'])) {
            $edituser = new AppUser();
            foreach ($_REQUEST['edituser'] as $key => $value) {
                $edituser->$key = $value;
            }
        } else {
            $edituser = new AppUser($uid);
        }

        if (0 == $edituser->uid && 0 == $user->uid && preg_match('/\/new$/i', $_SERVER['REQUEST_URI'])) {
            $vars['title'] = 'Regisztráció';
            $vars['new'] = true;
            $vars['helptext'] = true;
        } elseif (0 == $edituser->uid && $user->uid > 0) {
            $vars['title'] = 'Új felhasználó';
            if (!$user->checkRole('user')) {
                addMessage('Nincs megfelelő jogosultságod!', 'danger');
                $vars['accessdenied'] = true;
            }
            $vars['new'] = true;
        } else {
            $vars['title'] = 'Adatok módosítása';
            if (!$user->checkRole('user') && $user->uid != $edituser->uid) {
                addMessage('Nincs megfelelő jogosultságod!', 'danger');
                $vars['accessdenied'] = true;
            } elseif (0 == $edituser->uid && 0 == $user->uid) {
                addMessage('A személyes adatok módosításához be kell előbb lépni.', 'warning');
                $vars['needtologin'] = true;
            }
            $vars['edit'] = true;
        }

        if ('*vendeg*' == $edituser->username) {
            $edituser->username = false;
        }
        if ('*vendég*' == $edituser->nickname) {
            $edituser->nickname = false;
        }

        if ($user->loggedin) {
            $edituser->getRemarks(6);
        }

        if ($user->checkRole('user')) {
            $user->isadmin = true;
        }

        $vars['edituser'] = $edituser;

        foreach ($vars as $key => $value) {
            $this->$key = $value;
        }

        if ($user->loggedin) {
            $this->edituser->processResponsabilities();
        }
    }
}
