<?php

namespace App\Legacy;

use App\User;
use Illuminate\Database\Capsule\Manager as DB;

class Security
{
    private User $user;

    public function getUser(): ?User
    {
        if (!isset($this->user)) {
            $this->user = $this->initUser();
        }

        return $this->user;
    }

    private function initUser(): User
    {
        return User::load();
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
}