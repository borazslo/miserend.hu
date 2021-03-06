<?php

namespace Api;

class Signup extends Api {

    public function validateVersion() {
        if ($this->version < 4) {
            throw new \Exception("API action 'login' is not available under v4.");
        }
    }

    public function validateInput() {
        if (!isset($this->input['username']) OR ! isset($this->input['email']) OR ! isset($this->input['password'])) {
            throw new \Exception("JSON input misses variables: username and/or email and/or password");
        }
    }

    public function run() {
        parent::run();
        $this->getInputJson();
        $newuser = new \User();
        $validFields = array('username', 'email', 'password', 'nickname', 'name');
        $fieldsToSubmit = array();
        foreach ($validFields as $field) {
            if ($this->input[$field] AND $this->input[$field] != '') {
                $fieldsToSubmit[$field] = $this->input[$field];
            }
        }

        $success = $newuser->submit($fieldsToSubmit);

        $messages = \Message::getToShow();
        if (!$success) {
            $exceptionTexts = array();            
            foreach ($messages as $message) {
                $exceptionTexts[] = $message['text'];
            }
            throw new \Exception(implode("\n", $exceptionTexts));
        }
    }

}
