<?php

namespace Api;

use Illuminate\Database\Capsule\Manager as DB;
        
class Updated extends Api {

    public $title = 'Adatbázis frissítettsége';
    
    public $fields = [
        'date' => [
            'validation' => 'date', 
            'description' => 'a dátum, amely óta vizsgálni kell a frissítéseket (kötelező itt vagy URL-ben)',
            'example' => '2025-10-16'
        ],
        'format' => [
            'validation' => [
                'enum' => ['text', 'json']
            ],
            'description' =>  'A visszatérés formátuma',
            'default' => 'text',
            'example' => 'json'
        ]
    ];

    public function docs() {

        $docs = [];
                        
        $docs['description'] = <<<HTML
        <p>Visszaadja, hogy adott dátum után volt-e változás a miserendekben vagy templomokban. A képek változását nem vizsgálja.</p>
        
        <p>Régen a dátumot az url-ben kellett megadni, például: <code>api/v3/updated/2024-10-16</code>, és a válasz csak 0 vagy 1 lehetett mindenféle formázás nélkül. 
        Ez ma is működik, de javasoljuk a JSON formátum használatát az adatok küldésére és fogadására is, mert így egységesebb a válaszformátum.</p>

        HTML;

        $docs['response'] = <<<HTML
        TEXT esetén: 0, ha nem volt változás és 1, ha volt változás.<br/>
        JSON esetén:  Egy JSON objektumot ad vissza, amely tartalmazza, hogy volt-e változás (true/false), illetve hiba esetén egy hibaüzenetet.
        HTML;

        return $docs;
    }

    public function run() {
        parent::run();
        $this->getInputJson();

        $this->format = isset($this->input['format']) ? $this->input['format'] : $this->fields['format']['default'];

        if (isset($_REQUEST['date']) and preg_match('#^\d{4}-\d{2}-\d{2}$#',$_REQUEST['date'])) {
            $this->date = $_REQUEST['date'];
        } elseif (isset($this->input['date'])) {
           $this->date = $this->input['date'];
        } else {
            throw new \Exception("Field 'date' is required in URL or JSON input.");
        }

        // If we cannot find the sqlite file, return "0" (no update)
        $sqlite = new \Api\Sqlite();
        $sqlite->version = $this->version;        
        if(!$sqlite->checkSqliteFile()) {
            if($this->input['format'] == 'json') {
                $this->return = [
                    "error" => 1, 
                    "text" => "Sqlite file is not available.",
                    "updated" => false
                ];
            } else 
                $this->return = "0";
            return;
        }

        if( DB::table('templomok')->where('frissites','>=',$this->date)->count() > 0)  {
            if($this->input['format'] == 'json') {
                $this->return = ["error" => 0, "updated" => true];
            } else
                $this->return = "1";
        } else 
            if($this->input['format'] == 'json') {
                $this->return = ["error" => 0, "updated" => false];
            } else  
                $this->return = "0";
    }

}
