<?php

namespace Api;

class LoRaWAN extends Api {

    public function validateVersion() {
        if ($this->version < 4) {
            throw new \Exception("API action 'LoRaWAN' is not available under v4.");
        }
    }

    
    public function run() {
        parent::run();
                
    }

}
