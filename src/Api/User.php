<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api;

class User extends Api
{
    public function validateVersion()
    {
        if ($this->version < 4) {
            throw new \Exception("API action 'user' is not available under v4.");
        }
    }

    public function validateInput()
    {
        if (!isset($this->input['token'])) {
            throw new \Exception('JSON input misses token.');
        }
    }

    public function run()
    {
        parent::run();
        $this->getInputJson();
        $token = \App\Model\Token::where('name', $this->input['token'])->first();
        if (!$token || !$token->isValid) {
            throw new \Exception('Invalid token.');
        }

        // TODO: delete global somehow
        $user = $this->getSecurity()->getUser();
        $user = new \App\User($token->uid);
        $user->getFavorites();
        $data = [
            'username' => $user->getUsername(),
            'nickname' => $user->getNickname(),
            'name' => $user->getName(),
            'email' => $user->getEmail(),
        ];
        foreach ($user->getFavorites() as $favorite) {
            $data['favorites'][] = $favorite['tid'];
        }

        $this->return['user'] = $data;
    }
}
