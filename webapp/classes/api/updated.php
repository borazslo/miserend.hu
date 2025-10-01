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
            'default' => 'text'
        ]
    ];

    public function docs() {

        $docs = [];
                        
        $docs['description'] = <<<HTML
        <p>TEXT esetén: Egyet ad vissza, ha adott dátum óta volt változás a miserendek és templomok között. (A képek közötti változást nem vizsgálja.) <br/>Nullát ad vissza, ha nem volt változás. (Vagy valamiért épp nem volt elérhető az adatbázis sqlite fájl.)</p>
        <p>JSON esetén: Egy JSON objektumot ad vissza, amely tartalmazza, hogy volt-e változás (true/false), illetve hiba esetén egy hibaüzenetet.</p>
        <p>Nem csak JSON payload-dal, hanem a dátumot a URL-ben is meg lehet adni. Például: <code>api/v4/updated/2026-12-01</code> .</p>
            
        HTML;

        $docs['response'] = <<<HTML
        0, ha nem volt változás és 1, ha volt változás.
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
