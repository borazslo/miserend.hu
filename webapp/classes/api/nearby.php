<?php

namespace Api;

use Illuminate\Database\Capsule\Manager as DB;

class NearBy extends Api {

    public $format = 'json'; //or text
	public $requiredFields = array('lat','lon');

        
    public function validateVersion() {
        if ($this->version < 4) {
            throw new \Exception("API action 'nearby' is not available under v4.");
        }
    }

    public function validateInput() {
		if (!is_numeric($this->input['lat']) OR $this->input['lat'] > 90 OR $this->input['lat'] < -90 ) {
            throw new \Exception("JSON input 'lat' should be float between -90 and 90.");
        }
		if (!is_numeric($this->input['lon']) OR $this->input['lon'] > 90 OR $this->input['lon'] < -180 ) {
            throw new \Exception("JSON input 'lon' should be float between -180 and 90.");
        }		
	}

    public function run() {
        parent::run();
		
        $this->getInputJson();
		
		$churches = \Eloquent\Church::select()
				->addSelect(DB::raw("ST_distance_sphere( ST_GeomFromText('POINT ( ".$this->input['lat']." ".$this->input['lon']." )', 4326), ST_GeomFromText(CONCAT('POINT ( ',lat,' ', lon, ')'), 4326) ) as distance"))
                ->where('ok','i')
				->where('lat','<>','')
                ->orderBy('distance', 'ASC')
				->limit(10)
                ->get();
				
//		printr($churches);		
		foreach($churches as $church) {
			$masses = searchMasses(['templom'=>$church->id, 'mikor' => date('Y-m-d')] );
			$misek = [];
			foreach($masses['churches'][$church->id]['masses'] as $key => $mise) {
				$misek[$key]['idopont'] = date('Y-m-d')." ".$mise['ido'];
				$info = trim($mise['milyen']." ".$mise['megjegyzes']." ".$mise['nyelv']);
				if($info != '') $misek[$key]['informacio'] = $info;
				
				
			}
			$this->return['templomok'][] = [
				'id' => $church->id,
				'nev' => $church->nev,
				'ismertnev' => $church->ismertnev,
				'varos' => $church->varos,
				'tavolsag' => (int) $church->distance,
				'misek' => $misek
			];
		}
        //$this->return['lat'] = $this->input['lat'];

		
		
        return;
    }
    
  
}
