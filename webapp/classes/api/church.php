<?php

namespace Api;

use Illuminate\Database\Capsule\Manager as DB;

class Church extends Api {

    public $format = 'json'; //or text
	public $requiredFields = array('id');
        
    public function validateVersion() {
        if ($this->version < 4) {
            throw new \Exception("API action 'church' is not available under v4.");
        }
    }

    public function validateInput() {
		if (!is_numeric($this->input['id']))  {
            throw new \Exception("JSON input 'id' should be an integer.");
        }
        if (isset($this->input['response_length']) AND !in_array($this->input['response_length'], ['minimal', 'medium', 'full'])) {
            throw new \Exception("JSON input 'response_length' should be 'minimal', 'medium', or 'full'.");
        }
	}

    public function run() {
        parent::run();
		
        $this->getInputJson();
		
		$church = \Eloquent\Church::Where('id',$this->input['id'])->get()->map->toAPIArray(isset($this->input['response_length']) ? $this->input['response_length'] : false );
				

        if(count($church) < 1 ) {
            $this->return = [
                'error' => 1,
                'text' => 'Nem létezik misézőhely ezzel az asonosítóval.'
            ];
            return;
        }       

       $this->return = $church[0];

       return;
    }
    
	
}
