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

	public function validateInput() {
        if (!is_numeric($this->input['offset']) ) {
            throw new \Exception("JSON input 'offset' should be an integer.");
        }
		if (!is_numeric($this->input['limit']) OR $this->input['limit'] > 100 OR $this->input['limit'] < 1 ) {
            throw new \Exception("JSON input 'limit' should be an integer between 1 and 100.");
        }
		if (isset($this->input['response_length']) AND !in_array($this->input['response_length'], ['minimal', 'medium', 'full'])) {
            throw new \Exception("JSON input 'response_length' should be 'minimal', 'medium', or 'full'.");
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

		if(count($ids) == 0) {
			$this->return['templomok'] = [];
			return;
		}
		$this->return['templomok'] = \Eloquent\Church::select()	
			->whereIN('id',$ids)
			->orderByRaw("FIELD(id, " . implode(',', $ids) . ")")
			->get()->map->toAPIArray(isset($this->input['response_length']) ? $this->input['response_length'] : false );


		
        return;
    }

}
