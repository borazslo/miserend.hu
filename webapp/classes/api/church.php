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
	}

    public function run() {
        parent::run();
		
        $this->getInputJson();
		
		$church = \Eloquent\Church::Where('id',$this->input['id'])->get()->map->toAPIArray();
				

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
