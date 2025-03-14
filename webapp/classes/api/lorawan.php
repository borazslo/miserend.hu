<?php

namespace Api;

class LoRaWAN extends Api {

    public $requiredFields = array('deduplicationId','time','deviceInfo','object');

    public function validateVersion() {
        if ($this->version < 4) {
            throw new \Exception("API action 'LoRaWAN' is not available under v4.");
        }
    }

    public function validateInput() {
        
        if (!preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/', $this->input['deduplicationId'])) {
            throw new \Exception("JSON input 'deduplicationId' is not in valid form.");
        }
        
        if (!preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{3}\+\d{2}:\d{2}$/', $this->input['time'])) {
            throw new \Exception("JSON input 'time' is not in valid form. Expected format: YYYY-MM-DDTHH:MM:SS.sss+00:00");
        }

        if (!is_array($this->input['deviceInfo'])) {
            throw new \Exception("JSON input 'deviceInfo' should be an array.");
        }
        if (!isset($this->input['deviceInfo']['tags']['local_id'])|| !is_numeric($this->input['deviceInfo']['tags']['local_id'])) {
            throw new \Exception("JSON input 'deviceInfo[tags][local_id]' is required and should be a number.");
        }
        if (!isset($this->input['deviceInfo']['tags']['templom_id']) || !is_numeric($this->input['deviceInfo']['tags']['templom_id'])) {
            throw new \Exception("JSON input 'deviceInfo[tags][templom_id]' is required and should be a number.");
        }
        if (!isset($this->input['deviceInfo']['devEui']) || !preg_match('/^[a-f0-9]{16}$/', $this->input['deviceInfo']['devEui'])) {
            throw new \Exception("JSON input 'deviceInfo[deveui]' is required and should be in a valid format.");
        }

        if (!is_array($this->input['object'])) {
            throw new \Exception("JSON input 'object' should be an array.");
        }


	}

    public function run() {
        parent::run();

        $this->getInputJson();

        $confession = new \Eloquent\Confession();
        $confession->local_id = $this->input['deviceInfo']['tags']['local_id'];
        $confession->church_id = $this->input['deviceInfo']['tags']['templom_id'];
        $confession->deduplicationId = $this->input['deduplicationId'];
        $confession->timestamp = date('Y-m-d H:i:s', strtotime($this->input['time']));
        $confession->fulldata = json_encode($this->input['object']);
        //$confession->fulldata = json_encode($this->input);
        $confession->save();
        
    }

}
