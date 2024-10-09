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
		
		$church = \Eloquent\Church::find($this->input['id']);
				

        if($church == array() ) {
            $this->return = [
                'error' => 1,
                'text' => 'Nem létezik misézőhely ezzel az asonosítóval.'
            ];
            return;
        }       

        $masses = searchMasses(['templom'=>$church->id, 'mikor' => date('Y-m-d')] );
        
        $misek = [];
        
        if(isset($masses['churches'][$church->id])) {
            foreach($masses['churches'][$church->id]['masses'] as $key => $mise) {
                $misek[$key]['idopont'] = date('Y-m-d')." ".$mise['ido'];
                $info = trim($mise['milyen']." ".$mise['megjegyzes']." ".$mise['nyelv']);
                if($info != '') $misek[$key]['informacio'] = $info;
        }	
            
        }
        $this->return = [
            'id' => $church->id,
            'nev' => $church->nev,
            'ismertnev' => $church->ismertnev,
            'varos' => $church->varos,
            'misek' => $misek,
            'lat' => $church->lat,
            'lon' => $church->lon
        ];
						
        return;
    }
    
	
}
