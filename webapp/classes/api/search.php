<?php

namespace Api;

use Illuminate\Database\Capsule\Manager as DB;

class Search extends Api {

    public $format = 'json'; //or text
	public $requiredFields = array('q');        
    public $requiredVersion = ['>=',4]; // API v4-től érhető el

     public function docs() {

        $docs = [];
        $docs['title'] = 'Misézőhely keresése';
        $docs['input'] = [
            'q' => [
                'required',
                'string',
                'a keresőkifejezés'
			],
			'offset' => [
                'optional',
                'integer',
                'hanyadik választól mutassuk az eredményeket (lapozó használatához)'
						],
			'limit' => [
                'optional',
                'integer',
                'az egyszerre megmutatandó válaszok száma, 0 &lt; c &lt; 101'
            ]
        ];
		 
        $docs['description'] = <<<HTML
        <p>Templomok között lehet keresni egy (akár összetett) keresőszó megadásával.</p>
        <p><strong>Elérhető:</strong> <code>http://miserend.hu/api/v4/search</code></p>
        HTML;

        $docs['response'] = <<<HTML
        <ul>
        	<li>„error”: <strong>0</strong>, ha nincs hiba. <strong>1</strong>, ha van valami hiba.</li>
        	<li>„templomok”: A megtalált templomok listája. Mindegyik egy <em>templom</em> adattömb, ahogy az egy-egy templom lekérésénél láttuk.</li>
        </ul>
        HTML;

        return $docs;
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
