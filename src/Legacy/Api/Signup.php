<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Legacy\Api;

class Signup extends Api
{
    public function validateVersion()
    {
        if ($this->version < 4) {
            throw new \Exception("API action 'login' is not available under v4.");
        }
    }

    public function validateInput()
    {
        if (!isset($this->input['username']) || !isset($this->input['email']) || !isset($this->input['password'])) {
            throw new \Exception('JSON input misses variables: username and/or email and/or password');
        }
    }

    public function run()
    {
        parent::run();
        $this->getInputJson();
        $newuser = new \App\Legacy\User();
        $validFields = ['username', 'email', 'password', 'nickname', 'name'];
        $fieldsToSubmit = [];
        foreach ($validFields as $field) {
            if ($this->input[$field] && '' != $this->input[$field]) {
                $fieldsToSubmit[$field] = $this->input[$field];
            }
        }

        $success = $newuser->submit($fieldsToSubmit);

        $messages = \App\Legacy\Message::getToShow();
        if (!$success) {
            $exceptionTexts = [];
            foreach ($messages as $message) {
                $exceptionTexts[] = $message['text'];
            }
            throw new \Exception(implode("\n", $exceptionTexts));
        }
    }
}
