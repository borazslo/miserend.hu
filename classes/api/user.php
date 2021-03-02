<?php

namespace Api;

class User extends Api {

    public function validateVersion() {
        if ($this->version < 4) {
            throw new \Exception("API action 'user' is not available under v4.");
        }
    }

    public function validateInput() {
        if (!isset($this->input['token'])) {
            throw new \Exception("JSON input misses token.");
        }
    }

    public function run() {
        parent::run();
        $this->getInputJson();
        $token = \Eloquent\Token::where('name',$this->input['token'])->first();
        if(!$token or !$token->isValid) {
            throw new \Exception("Invalid token.");
        }    
        
        //TODO: delete global somehow
        global $user;
        $user = new \User($token->uid);
        $user->getFavorites();
        $data = array(
            'username' => $user->username,
            'nickname' => $user->nickname,
            'name' => $user->name,
            'email' => $user->email
        );
        foreach ($user->favorites as $favorite)
            $data['favorites'][] = $favorite['tid'];

        $this->return['user'] = $data;
    }

}
