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

        // Each endpoint can have 'requiredVersion' property to specify the minimum or maximum version required
        if(isset($this->requiredVersion))  {
            if (is_array($this->requiredVersion)) {
                if (!version_compare($this->requiredVersion[1], $this->version, $this->requiredVersion[0] )) {
                    throw new \Exception("API version does not match the required version.");
                }
            } else {            
                throw new \Exception("Invalid requiredVersion for API endpoint.");                
            }

        }

        // Each endpoint can have its own version validation
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

        if(isset($this->requiredFields)) {
            $this->requiredInput($this->requiredFields);
        }
        if (method_exists($this, 'validateInput')) {
            $this->validateInput();
        }
    }
    
    public function requiredInput($fields) {
        foreach($fields as $field) {
            if(! isset($this->input[$field])) {
                throw new \Exception("Field '".$field."' is required in JSON.");
            }
        }
    }

    /**
     * Összegyűjti az összes API endpoint osztály nevét.
     * Azaz visszaadja a classes/api könyvtárban található összes PHP fájl által ténylegesen definiált osztály nevét (kivéve api.php).
     *
     * @return array Az endpoint osztályok nevei (string)
     */
    public static function collectApiEndpoints() {
        $dir = __DIR__ . '/';
        $files = scandir($dir);
        $result = [];
        foreach ($files as $file) {
            if (substr($file, -4) === '.php' && $file !== 'api.php') {
                $before = get_declared_classes();
                include_once($dir . $file);
                $after = get_declared_classes();
                $new = array_diff($after, $before);
                foreach ($new as $class) {
                    $result[] = preg_replace('/^Api\\\/', '', $class);
                }
            }
        }

        // Az Api osztály nem külön endpoint, ezért kivesszük a listából
        if (($key = array_search('Api', $result)) !== false) {
            unset($result[$key]);
            $result = array_values($result);
        }

        // A ReportByAnonym és ReportByUser osztályok nem külön endpointok, ezért kivesszük a listából
        // TODO: máshogy megoldani, hogy ne kelljen kézzel frissíteni
        $reportClasses = ['ReportByAnonym', 'ReportByUser'];
        foreach ($reportClasses as $reportClass) {
            if (($key = array_search($reportClass, $result)) !== false) {
                unset($result[$key]);
            }
        }

        sort($result);
        return $result;
    }

}
