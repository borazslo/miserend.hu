<?php

namespace Api;

use Illuminate\Database\Capsule\Manager as DB;

class Search extends Api {

    public $format = 'json'; //or text
	public $requiredFields = array('q');
        
    public function validateVersion() {
        if ($this->version < 4) {
            throw new \Exception("API action 'search' is not available under v4.");
        }
    }
    
    public function run() {
        parent::run();
		
        $this->getInputJson();
		
		$offset = isset($this->input['offset']) ? $this->input['offset'] : 0;
		$limit = isset($this->input['limit']) ? $this->input['limit'] : 10;
		
		$results = searchChurches(['kulcsszo' => $this->input['q']], $offset, $limit);
				
		$ids = [];
		foreach ($results['results'] as $key => $result) {		
			$ids[] = $result['id'];
		}		
		unset($results['results']);
		$this->return = $results;

		$this->return['templomok'] = \Eloquent\Church::select()	
			->whereIN('id',$ids)
			->orderByRaw("FIELD(id, " . implode(',', $ids) . ")")
			->get()->map->toAPIArray();


		
        return;
    }

}
