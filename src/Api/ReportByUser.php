<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api;

class ReportByUser extends Report
{
    public function validateVersion()
    {
        if ($this->version < 4) {
            throw new \Exception("API action 'report' with user 'token' is not available under v4.");
        }
    }

    public function validateInput()
    {
        parent::validateInput();
        if (!isset($this->input['token'])) {
            throw new \Exception('JSON input misses token.');
        }
        $this->token = \App\Model\Token::where('name', $this->input['token'])->first();
        if (!$this->token || !$this->token->isValid) {
            throw new \Exception('Invalid token.');
        }
    }

    public function prepareUser()
    {
        $this->user = new \App\User($this->token->uid);
    }
}
