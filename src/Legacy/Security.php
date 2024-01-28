<?php

namespace App\Legacy;

use App\User;
use Illuminate\Database\Capsule\Manager as DB;

class Security
{
    private User $user;

    public function __construct(
        private readonly TokenManager $tokenManager,
    )
    {
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
            return new self();
        }

        $token = $this->tokenManager->findToken($_COOKIE['token']);
        if (!$token || !$token->isValid) {
            $this->tokenManager->delete();

            return new self();
        }

        $this->tokenManager->extend($token);

        $user = new User($token->uid);
        $user->setLoggedin(true);
        $user->active();

        return $user;
    }

    public function isGranted(string|false $role = false): bool
    {
        if (false === $role) {
            return true;
        }

        if ('"any"' == $role || "'any'" == $role) {
            if (!isset($this->jogok)) {
                return false;
            }
            if ('' != trim(preg_replace('/-/i', '', $this->jogok))) {
                return true;
            } else {
                return false;
            }
        } elseif (preg_match('/^ehm:([0-9]{1,3})$/i', $role, $match)) {
            $isResponsible = DB::table('egyhazmegye')->where('id', $match[1])->where('felelos', $this->username)->first();
            if ($isResponsible) {
                return true;
            } else {
                return false;
            }
        } elseif (isset($this->jogok) && preg_match('/(^|-)'.$role.'(-|$)/i', $this->jogok)) {
            return true;
        } else {
            return false;
        }
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
}
