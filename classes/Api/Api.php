<?php

namespace Api;

class Api {

    public $version;
    public $format = 'json';
    public $return = array();

    public function run() {
        $this->version = \Request::IntegerRequired('v');
        $this->validateVersionMain();

        $defaultDate = date('Y-m-d');
        $this->date = \Request::DatewDefault('datum', $defaultDate);
    }

    public function validateVersionMain() {
        if (!in_array($this->version, array(1, 2, 3, 4))) {
            throw new \Exception("Invalid API version.");
        }
        if (method_exists($this, 'validateVersion')) {
            $this->validateVersion();
        }
    }

    public function getInputJson() {
        if (!$inputJSONstring = file_get_contents('php://input')) {
            throw new \Exception("There is no JSON input.");
        }
        if (!$inputJSONarray = json_decode($inputJSONstring, TRUE)) {
            throw new \Exception("Invalid JSON input.");
        }
        $this->input = $inputJSONarray;
        if (method_exists($this, 'validateInput')) {
            $this->validateInput();
        }
    }

    public function printException($exception) {
        $this->return = array(
            'error' => '1',
            'text' => $exception->getMessage()
        );
        $this->printOutput();
    }

    public function printOutput() {
        switch ($this->format) {
            case 'json':
                $this->printOutputJson();
                break;

            case 'text':
                $this->printOutputText();
                break;

            default:
                $this->printOutputText();
                break;
        }
    }

    public function printOutputText() {
        if (is_array($this->return)) {
            foreach ($this->return as $key => $value) {
                echo $key . ": \"" . $value . "\";\n";
            }
        } else {
            echo $this->return;
        }
    }

    public function printOutputJson() {
        if (!isset($this->return['error'])) {
            $this->return['error'] = 0;
        }
        echo json_encode($this->return);
    }

}
