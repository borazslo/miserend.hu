<?php

namespace Api;

use Illuminate\Database\Capsule\Manager as DB;

class Church extends Api {

    public $title = 'Egy misézőhely adatai és miséi röviden';
    public $format = 'json'; //or text	
    public $requiredVersion = ['>=',4]; // API v4-től érhető el

    public $fields = [
        'id' => [
            'required' => true, 
            'validation' => 'integer', 
            'description' => 'A misézőhely azonosítója, amely egyedi azonosító a rendszerben.',
            'example' => 7
        ],
        'response_length' => [
            'validation' => [
                'enum' => ['minimal', 'medium','full']
            ],
            'description' =>  'A válasz részletessége', 
            'default' => 'medium'
        ]
    ];

    public function docs() {
        $docs = [];
             
        $docs['description'] = <<<HTML
        <p>Egy templom adatát adja vissza. Csak röviden, a legszükségesebb adatokkal. Az aktuális napi misék rendjét is hozza.</p>        
        HTML;

        $docs['response'] = <<<HTML
            <ul>
            <li>„error”: <strong>0</strong>, ha nincs hiba. <strong>1</strong>, ha van valami hiba.</li>
            <li>„text” (opcionális): „error:1” esetén a hiba szöveges leírása</li>
        </ul>
        <p>Majd következik egy <em>templom</em> tömb és adatai.</p>
        <ul>
            <li>„id”</li>
            <li>„nev”</li>
            <li>„ismertnev”</li>
            <li>„varos”</li>
            <li>„lat”</li>
            <li>„lon”</li>
            <li>„tavolsag” (<em>integer</em>): távolság méterben</li>
            <li>„misek”: az adott napi szentmisék listája
                <ul>
                    <li>„idopont” (<em>YYYY-MM-NN HH:ii:ss</em>): a szentmise időpontja</li>
                    <li>„informacio” (<em>string</em>, opcionális): megjegyzés, nyelv, stílus, satöbbi., ha van</li>
                </ul>
            </li>
        </ul>
        HTML;

        return $docs;
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
