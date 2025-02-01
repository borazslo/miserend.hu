<?php

namespace ExternalApi;

class OpeninghApi extends \ExternalApi\ExternalApi {

    public $name = 'openingh';
    public $apiUrl = "https://openingh.openstreetmap.de/api/" ;    
    public $settings = [
        'mode' => 2,
        'warnings_severity' => 7
    ]; 
	public $testQuery = "validate?value=PH";
    
    function buildQuery() {
        foreach($this->settings as $key => $value) {
            $this->query .= '&'.$key.'='.$value;
        }
        $this->rawQuery = $this->query;        
        
    }

    function validate($string) {        
        $sanitized_string = urlencode(str_replace("\n","",$string));
        $this->query = 'validate?value='.$sanitized_string;

        $this->runQuery();
        
        $this->linkForDetails = "See: <a href='https://openingh.openstreetmap.de/evaluation_tool/?mode=2&EXP=".$string."'>details</a>";

        if(isset($this->jsonData->errors)) {
            $message = "Error! It is not a valid opening_hour!";
            $message .= $this->linkForDetails;
            $message .= "\n".( count($this->jsonData->errors) > 1 ? print_r($this->jsonData->errors,1) : (is_object($this->jsonData->errors[0]) ? print_r($this->jsonData->errors[0],1) : $this->jsonData->errors[0]) );
            throw new Exception($message);
        }
        
        return true;   
    }

    function linkForDetails() {
        return $this->linkForDetails;
    }


}

