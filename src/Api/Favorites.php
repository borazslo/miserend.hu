<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api;

class Favorites extends Api
{
    public function validateVersion()
    {
        if ($this->version < 4) {
            throw new \Exception("API action 'favorites' is not available under v4.");
        }
    }

    public function validateInput()
    {
        if (!isset($this->input['token'])) {
            throw new \Exception('JSON input misses token.');
        }
        foreach (['add', 'remove'] as $method) {
            if (isset($this->input[$method])) {
                if (!\is_array($this->input[$method]) && !is_numeric($this->input[$method])) {
                    throw new \Exception("Wrong format of '$method' in JSON input.");
                } elseif (!\is_array($this->input[$method])) {
                    $this->input[$method] = [$this->input[$method]];
                }
                foreach ($this->input[$method] as $tid) {
                    if (!is_numeric($tid)) {
                        throw new \Exception("Wrong value in '$method' of JSON input.");
                    }
                }
            }
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
        global $user;
        $user = new \App\User($token->uid);

        if (isset($this->input['remove'])) {
            if (!$user->removeFavorites($this->input['remove'])) {
                throw new \Exception('Could not remove favorites.');
            }
        }
        if (isset($this->input['add'])) {
            if (!$user->addFavorites($this->input['add'])) {
                throw new \Exception('Could not add favorites.');
            }
        }

        $favorites = [];
        $user->getFavorites();
        foreach ($user->getFavorites() as $favorite) {
            $favorites[] = $favorite['tid'];
        }

        $this->return['favorites'] = $favorites;
    }
}
