<?php

namespace Api;

class Login extends Api {

    public function validateVersion() {
        if ($this->version < 4) {
            throw new \Exception("API action 'login' is not available under v4.");
        }
    }

    public function validateInput() {
        if (!isset($this->input['username']) OR ! isset($this->input['password'])) {
            throw new \Exception("JSON input misses variables.");
        }
    }

    public function run() {
        parent::run();
        $this->getInputJson();
        $userId = login($this->input['username'], $this->input['password']);
        if (!$userId) {
            throw new \Exception("Invalid username or password.");
        }
        $token = generateToken($userId);

        $this->return['token'] = $token;
    }

}
