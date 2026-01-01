<?php

namespace Api;

use Illuminate\Database\Capsule\Manager as DB;

class Search extends Api {

    public $title = 'Misézőhely keresése';
    public $format = 'json'; //or text
    public $requiredVersion = ['>=',4]; // API v4-től érhető el

    public $fields = [
        'q' => [
            'required' => true, 
            'validation' => 'string', 
            'description' => 'a keresőkifejezés',
            'example' => 'Szent István'
        ],
        'offset' => [
            'validation' => 'integer', 
            'description' => 'hanyadik választól mutassuk az eredményeket (lapozó használatához)', 
            'default' => 0
        ],
        'limit' => [
            'validation' => ['integer' => [
                'minimum' => 1,
                'maximum' => 100    
            ]], 
            'description' => 'az egyszerre megmutatandó válaszok száma', 
            'default' => 10
        ],
        'response_length' => [
            'validation' => [
                'enum' => ['minimal', 'medium','full']
            ],
            'description' =>  'A válasz részletessége', 
            'default' => 'medium'
        ],
        'when' => [
            'validation' => [
                'enum' => ['today', 'tomorrow', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday',
                ['date'=>[]]]
            ],
            'description' => 'csak az adott napi misék megjelenítése',
            'default' => false
        ]
    ];

     public function docs() {

        $docs = [];            
	 
        $docs['description'] = <<<HTML
        <p>Templomok között lehet keresni egy (akár összetett) keresőszó megadásával.</p>
        HTML;

        $docs['response'] = <<<HTML
        <ul>
        	<li>„error”: <strong>0</strong>, ha nincs hiba. <strong>1</strong>, ha van valami hiba.</li>
        	<li>„templomok”: A megtalált templomok listája. Mindegyik egy <em>templom</em> adattömb, ahogy az egy-egy templom lekérésénél láttuk.</li>
            <li>„text” (opcionális): „error:1” esetén a hiba szöveges leírása</li>
        </ul>
        HTML;

        return $docs;
    }

    public function run() {
        parent::run();
		
        $this->getInputJson();
		
		$offset = isset($this->input['offset']) ? $this->input['offset'] : 0;
		$limit = isset($this->input['limit']) ? $this->input['limit'] : 10;
		
        $search = new \Search('masses');
        $search->keyword($this->input['q']);				
        if (isset($this->input['when']) && $this->input['when']) {            
            $search->day($this->input['when']);            
        } 
		$results = $search->getResults($offset, $limit, true);
            
        $this->return = [
            'offset' => $offset,
            'limit' => $limit,
            'sum' => $search->total,
            'error' => 1,
            'templomok' => []
        ];
        
        $ids = array_keys($results);
        unset($results);

		if(count($ids) == 0)
        {
            if($search->total != 0) {
                $this->return['error'] = 1;
                $this->return['text'] = 'Elvileg találtunk több templomot, de mégsem találtunk. Hmm.';
                return;
            }

			$this->return['templomok'] = [];
            $this->return['error'] = 0;
			return;
		}

		$this->return['templomok'] = \Eloquent\Church::select()	
			->whereIN('id',$ids)
			->orderByRaw("FIELD(id, " . implode(',', $ids) . ")")
			->get()->map->toAPIArray(
                isset($this->input['response_length']) ? $this->input['response_length'] : (  $this->fields['response_length']['default'] ? $this->fields['response_length']['default'] : false ), 
                isset($this->input["when"]) ? $this->input["when"] : (  $this->fields['when']['default'] ? $this->fields['when']['default'] : false ));
        
        if(count($ids) == count($this->return['templomok'])) {
            $this->return['error'] = 0;
        } else {
            $this->return['error'] = 1;
            $this->return['text'] = 'Belső hiba történt: nem sikerült minden templomot lekérni.';
        }

        return;
    }

}
