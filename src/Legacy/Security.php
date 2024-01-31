<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Legacy;

use App\User;
use Illuminate\Database\Capsule\Manager as DB;

class Security
{
    private User $user;

    public function __construct(
        private readonly TokenManager $tokenManager,
    ) {
    }

    public function getUser(): ?User
    {
        if (!isset($this->user)) {
            $this->user = $this->initUser();
        }

        return $this->user;
    }

    public function replaceUser(User $user): void
    {
        $this->user = $user;
    }

    private function initUser(): User
    {
        if (!isset($_COOKIE['token'])) {
            return new User();
        }

        $token = $this->tokenManager->findToken($_COOKIE['token']);
        if (!$token || !$token->isValid) {
            $this->tokenManager->delete();

            return new User();
        }

        $this->tokenManager->extend($token);

        $user = new User($token->uid);
        $user->setLoggedin(true);
        $user->active();

        return $user;
    }

    public function isAuthenticated(): bool
    {
        return $this->user->getLoggedin();
    }

    public function captureAuthentication(): void
    {
        // Felhasználó
        if (isset($_REQUEST['login'])) {
            try {
                $this->login($_REQUEST['login'], $_REQUEST['passw']);
            } catch (\Exception $ex) {
                addMessage('Hibás név és/vagy jelszó!<br/><br/>Ha elfelejtetted a jelszavadat, <a href="/user/lostpassword">kérj ITT új jelszót</a>.', 'danger');
            }
        }
    }

    protected function login(string $name, string $password): void
    {
        $this->tokenManager->delete();

        $name = sanitize($name);
        $userRow = DB::table('user')
            ->where('login', $name)
            ->first();

        if (!$userRow) {
            throw new \Exception('There is no such user.');
        }
        if (!password_verify($password, $userRow->jelszo)) {
            throw new \Exception('Invalid password.');
        }

        $this->tokenManager->create($userRow->uid, 'web');

        DB::table('user')
            ->where('uid', $userRow->uid)
            ->update(['lastlogin' => date('Y-m-d H:i:s')]);
    }

    public function captureLogout(): void
    {
        if (isset($_REQUEST['logout'])) {
            $this->tokenManager->delete();
        }
    }

    /**
     * User::checkRole volt, azt nézi meg, hogy egy adott jogköre van-e a felhasználónak.
     *
     * @param string|null $role
     * @return bool
     */
    public function isGranted(string $role = null): bool
    {
        if ($role === null) {
            return true;
        }

        // ehm-re kerdezunk ra
        if (preg_match('/^ehm:([0-9]{1,3})$/i', $role, $match)) {
            $isResponsible = DB::table('egyhazmegye')->where('id', $match[1])->where('felelos', $this->getUser()->getUsername())->first();

            return (bool) $isResponsible;
        }

        $userRoles = $this->getUser()->getJogok();

        if ($userRoles === null) {
            return false;
        }

        if ($role === '"any"' || $role === "'any'") {
            return trim(preg_replace('/-/i', '', $userRoles)) !== '';
        }

        return preg_match('/(^|-)'.$role.'(-|$)/i', $userRoles);
    }
}
