<?php

namespace Api;

class Api {

    public $version;
    public $format = 'json';    
    public $return = array();
    public $fields = array();

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
        
        foreach ($this->input as $key => $value) {
            if (!isset($this->fields[$key])) {
                throw new \Exception("Unknown field '".$key."' in JSON input.");
            }
        }

        if(isset($this->fields)) {
            foreach($this->fields as $field => $details) {
                if(isset($details['required']) && $details['required'] === true) {
                    $this->requiredInput([$field]);
                }

                // Prepare validation rules
                if(!isset($details['validation'])) {
                    $details['validation'] = [];
                }
                if(!is_array($details['validation'])) {
                    $details['validation'] = [$details['validation'] => []];
                }
                foreach($details['validation'] as $key => $value) {
                    if(!is_array($value)) {
                        $details['validation'][$key] = [$value => []];
                    }
                }
                
                //Check validation rules
                foreach($details['validation'] as $function => $details) {
                    if(!isset($this->input[$field])) {
                        continue; // Skip validation if field is not set
                    }

                    if($function == 'integer') {
                        $this->validateInteger($field, $details); // Call custom integer validation if exists
                    } elseif($function == 'float') {
                        $this->validateFloat($field, $details); // Call custom float validation if exists                        
                    } elseif($function == 'string') {
                        if(!is_string($this->input[$field])) {
                            throw new \Exception("Field '".$field."' should be a string.");
                        }
                    } elseif($function == 'boolean') {
                        if(!is_bool($this->input[$field])) {
                            throw new \Exception("Field '".$field."' should be a boolean.");
                        }
                    } elseif($function == 'date') {
                        if(!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $this->input[$field])) {
                            throw new \Exception("Field '".$field."' should be a date (yyyy-mm-dd).");
                        }
                    } elseif($function == 'enum') {
                        $this->validateEnum($field, $details); // Call custom enum validation if exists                        
                    } elseif($function == 'list') {                        
                        if(!is_array($this->input[$field])) {
                            throw new \Exception("Field '".$field."' should be a list/array.");
                        }                        
                        foreach($this->input[$field] as $item) {
                            
                            foreach($details as $function => $detail) {
                                
                                if($function == 'integer') {
                                    $this->validateInteger($field, $detail, $item); // Call custom integer validation if exists                          
                                } elseif($function == 'float') {
                                     $this->validateFloat($field, $detail, $item); // Call custom integer validation if exists                          
                                } elseif($function == 'string') {
                                    if(!is_string($item)) {
                                        throw new \Exception("Items in field '".$field."' should be strings.");
                                    }                                    
                                } elseif($function == 'enum') {
                                    if(!in_array($item, $detail)) {
                                        throw new \Exception("Items in field '".$field."' should be one of: ".implode(", ", $detail).".");
                                    }                                    
                                } elseif(method_exists($this, 'validateField')) {
                                    $this->validateField($field, [$function => $detail]);
                                } else {
                                    throw new \Exception("Unknown validation function '".$function."' for items in field '".$field."'.");
                                }                                
                            }

                            
                        }   
                    } elseif(method_exists($this, 'validateField')) {
                        $this->validateField($field, [$function => $details]);
                    } else {
                        throw new \Exception("Unknown validation function '".$function."' for field '".$field."'.");
                }
                    
                }
            
                if(isset($details['validation']) && method_exists($this, 'validateField')) {
                    $this->validateField($field, $details['validation']);
                }                
            }
        }   

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

    public function validateFloat($field, $details) {
        if(!is_numeric($this->input[$field]) || floatval($this->input[$field]) != $this->input[$field]) {
            throw new \Exception("Field '".$field."' should be a float.");
        }
        if(isset($details['minimum']) && $this->input[$field] < $details['minimum']) {
            throw new \Exception("Field '".$field."' should be at least ".$details['minimum'].".");
        }
        if(isset($details['maximum']) && $this->input[$field] > $details['maximum']) {
            throw new \Exception("Field '".$field."' should be at most ".$details['maximum'].".");
        }
    }   

    public function validateInteger($fieldName, $details, $field = null) {        
        if($field == null) {
            $field = $this->input[$fieldName];        
        }

        if(!is_numeric($field) || intval($field) != $field) {
            throw new \Exception("Field '".$fieldName."' should be an integer.");
        }
        if(isset($details['minimum']) && $field < $details['minimum']) {
            throw new \Exception("Field '".$fieldName."' should be at least ".$details['minimum'].".");
        }
        if(isset($details['maximum']) && $field > $details['maximum']) {
            throw new \Exception("Field '".$fieldName."' should be at most ".$details['maximum'].".");
        }
    }   

    public function validateEnum($field, $details) {
        $return = false;
        foreach($details as $key => $value) {
            // Simple value
            if(!is_array($value) && $this->input[$field] === $value) {
                $return = true;
                break;
            }
            // Value with validation rules
            if(is_array($value)) {
                foreach($value as $fieldType => $validationRule) {
                    $details[$key] = json_encode($value);
                    try {
                        if($fieldType == 'integer') {
                            $this->validateInteger($field, $validationRule);
                        } elseif($fieldType == 'float') {
                            $this->validateFloat($field, $validationRule);                            
                        } elseif($fieldType == 'string') {
                            if(!is_string($this->input[$field])) {
                                throw new \Exception("Field '".$field."' should be a string.");
                            }                            
                        } elseif($fieldType == 'date') {
                            if(!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $this->input[$field])) {
                                throw new \Exception("Field '".$field."' should be a date (yyyy-mm-dd).");
                            }
                        } elseif($fieldType == 'boolean') {
                            if(!is_bool($this->input[$field])) {
                                throw new \Exception("Field '".$field."' should be a boolean.");
                            }       
                        } else {
                            throw new \Exception("Unknown enum validation type '".$fieldType."' for field '".$field."'.");
                        }
                        $return = true;                        
                        break;
                    } catch (\Exception $e) {
                        // Do nothing, try next
                    }
                }                 
                
            }

        }

        if(!$return) {            
            throw new \Exception("Field '".$field."' should be one of: ".implode(", ", $details).".");
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

    public function getAlternativeUrlPatterns() {        
        return isset($this->extraUri) ? $this->extraUri : [""];
    }   
}
