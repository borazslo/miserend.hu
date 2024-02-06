<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Legacy\Api;

class Login extends Api
{
    public function validateVersion()
    {
        if ($this->version < 4) {
            throw new \Exception("API action 'login' is not available under v4.");
        }
    }

    public function validateInput()
    {
        if (!isset($this->input['username']) || !isset($this->input['password'])) {
            throw new \Exception('JSON input misses variables.');
        }
    }

    public function run()
    {
        parent::run();
        $this->getInputJson();
        $userId = \App\Legacy\User::login($this->input['username'], $this->input['password']);
        if (!$userId) {
            throw new \Exception('Invalid username or password.');
        }
        $token = \App\Legacy\Token::create($userId, 'API');

        $this->return['token'] = $token;
    }
}
