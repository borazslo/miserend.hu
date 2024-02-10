<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Legacy\Html\User;

use App\Legacy\Html\Html;
use App\Legacy\Request;
use App\Legacy\User as AppUser;
use Illuminate\Database\Capsule\Manager as DB;
use Symfony\Component\HttpFoundation\Response;

class Edit extends Html
{
    public function registration(\Symfony\Component\HttpFoundation\Request $request): Response
    {
        $user = $this->getSecurity()->getUser();

        $uid = Request::IntegerwDefault('uid', $user->getUid());

        if ($request->getMethod() === 'POST') {
            if ($this->modify()) {
                /* Ha az aktuális felhasználót frissítettük, akkor be kell töltenünk újra a felhasználót a friss adatokkal */
                if ($uid == $user->getUid()) {
                    $this->getSecurity()->replaceUser(new AppUser($uid));
                }
            }

            // ?

            exit;
        }

        $roles = $this->getConstants()::ROLES;
        $vars = [
            'roles' => [],
        ];
        foreach ($roles as $role) {
            $vars['roles'][$role] = $role;
        }

        // Ha folyamatban van új felszanáló szerkesztése
        if ($user->getUid() == 0 && isset($_REQUEST['edituser'])) {
            $edituser = new AppUser();
            foreach ($_REQUEST['edituser'] as $key => $value) {
                $edituser->$key = $value;
            }
        } else {
            $edituser = new AppUser($uid);
        }

        if ($edituser->getUid() == 0 && $user->getUid() == 0 && preg_match('/\/new$/i', $_SERVER['REQUEST_URI'])) {
            $vars['title'] = 'Regisztráció';
            $vars['new'] = true;
            $vars['helptext'] = true;
        } elseif ($edituser->getUid() == 0 && $user->getUid() > 0) {
            $vars['title'] = 'Új felhasználó';
            if (!$user->checkRole('user')) {
                addMessage('Nincs megfelelő jogosultságod!', 'danger');
                $vars['accessdenied'] = true;
            }
            $vars['new'] = true;
        } else {
            $vars['title'] = 'Adatok módosítása';
            if (!$user->checkRole('user') && $user->getUid() != $edituser->getUid()) {
                addMessage('Nincs megfelelő jogosultságod!', 'danger');
                $vars['accessdenied'] = true;
            } elseif ($edituser->getUid() == 0 && $user->getUid() == 0) {
                addMessage('A személyes adatok módosításához be kell előbb lépni.', 'warning');
                $vars['needtologin'] = true;
            }
            $vars['edit'] = true;
        }

        if ($edituser->getUsername() == '*vendeg*') {
            $edituser->setUsername(false);
        }
        if ($edituser->getNickname() == '*vendég*') {
            $edituser->setNickname(false);
        }

        if ($user->getLoggedin()) {
            $edituser->getRemarks(6);
        }

        if ($user->checkRole('user')) {
            $user->setIsadmin(true);
        }

        $vars['edituser'] = $edituser;

        if ($user->getLoggedin()) {
            $edituser->processResponsabilities();
        }

        return $this->render('user/edit.twig', [
            'vars' => $vars,
        ]);
    }

    /**
     * Admin user edit.
     *
     * @throws \Exception
     */
    public function edit(\Symfony\Component\HttpFoundation\Request $request): Response
    {
        $userId = $request->attributes->getInt('user_id');

        if (!$this->getSecurity()->isGranted('ROLE_USER_ADMIN')) {
            throw new \Exception('Nincs megfelelő jogosultságod!');
        }

        $editUser = DB::table('user')
            ->select('*')
            ->where('uid', $userId)
            ->first();

        $roles = $this->getConstants()::ROLES;
        $vars = [
            'title' => 'Adatok módosítása',
            'edit' => true,
            'edituser' => $editUser,
            'roles' => [],
        ];
        foreach ($roles as $role) {
            $vars['roles'][$role] = $role;
        }

        return $this->render('user/edit.twig', [
            'vars' => $vars,
        ]);
    }

    public function modify(): bool
    {
        $user = $this->getSecurity()->getUser();

        $newuser = new AppUser(uid: $_REQUEST['edituser']['uid'] ?? false);

        if ((!isset($_REQUEST['terms']) || $_REQUEST['terms'] != 1) && $newuser->uid == 0 && $user->uid == 0) {
            addMessage('El kell fogadni a <i>Házirendet és szabályzatot</i>!', 'danger');

            return false;
        } elseif ((!isset($_REQUEST['robot']) || $_REQUEST['robot'] != 'MKPK') && $newuser->uid == 0 && $user->uid == 0) {
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
                        $uid = $newuser->uid;
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
}
