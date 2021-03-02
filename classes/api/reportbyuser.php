<?php

namespace Api;

class ReportByUser extends Report {

    public function validateVersion() {
        if ($this->version < 4) {
            throw new \Exception("API action 'report' with user 'token' is not available under v4.");
        }
    }

    public function validateInput() {
        parent::validateInput();
        if (!isset($this->input['token'])) {
            throw new \Exception("JSON input misses token.");
        }
        $this->token = \Eloquent\Token::where('name',$this->input['token'])->first();
        if(!$this->token or !$this->token->isValid) {
            throw new \Exception("Invalid token.");
        }            
    }

    public function prepareUser() {        
        $this->user = new \User($this->token->uid);
    }

}
