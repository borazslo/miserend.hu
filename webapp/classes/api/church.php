<?php

namespace Api;

use Illuminate\Database\Capsule\Manager as DB;

class Church extends Api {

    public $format = 'json'; //or text
	public $requiredFields = array('id');
    public $requiredVersion = ['>=',4]; // API v4-től érhető el

    public function docs() {
        $docs = [];
        $docs['title'] = 'Egy misézőhely adatai és miséi röviden';
        $docs['input'] = [
            'id' => [
                'required',
                'integer',
                'A misézőhely azonosítója, amely egyedi azonosító a rendszerben.'
            ],
            'response_length' => [
                'optional',
                'enum(minimal, medium, full)', 
                'A válasz részletessége: "minimal", "medium", vagy "full".',
                'medium']
        ];

        $docs['description'] = <<<HTML
        <p>Egy templom adatát adja vissza. Csak röviden, a legszükségesebb adatokkal. Az aktuális napi misék rendjét is hozza.</p>
        <p><strong>Elérhető:</strong> <code>http://miserend.hu/api/v4/church</code></p>
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
