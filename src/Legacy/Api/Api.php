<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Legacy\Api;

use App\Legacy\Request;

class Api
{
    public $version;
    public $format = 'json';
    public $return = [];

    public function run()
    {
        $this->version = Request::IntegerRequired('v');
        $this->validateVersionMain();

        $defaultDate = date('Y-m-d');
        $this->date = Request::DatewDefault('datum', $defaultDate);
    }

    public function validateVersionMain()
    {
        if (!\in_array($this->version, [1, 2, 3, 4])) {
            throw new \Exception('Invalid API version.');
        }
        if (method_exists($this, 'validateVersion')) {
            $this->validateVersion();
        }
    }

    public function getInputJson()
    {
        if (!$inputJSONstring = file_get_contents('php://input')) {
            throw new \Exception('There is no JSON input.');
        }
        if (!$inputJSONarray = json_decode($inputJSONstring, true)) {
            throw new \Exception('Invalid JSON input.');
        }
        $this->input = $inputJSONarray;

        if (isset($this->requiredFields)) {
            $this->requiredInput($this->requiredFields);
        }
        if (method_exists($this, 'validateInput')) {
            $this->validateInput();
        }
    }

    public function requiredInput($fields)
    {
        foreach ($fields as $field) {
            if (!isset($this->input[$field])) {
                throw new \Exception("Field '".$field."' is required in JSON.");
            }
        }
    }
}
