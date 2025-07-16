<?php

namespace Api;

use Illuminate\Database\Capsule\Manager as DB;
        
class Updated extends Api {

    public $format = 'text';

    public $requiredVersion = ['>=',2]; // API v2-től érhető el

    public function docs() {

        $docs = [];
        $docs['title'] = 'Adatbázis frissítettsége';
        $docs['input'] = [];
        

        $docs['description'] = <<<HTML
        <p>Egyet ad vissza, ha adott dátum óta nem volt változás a miserendek és templomok között. (A képek közötti változást nem vizsgálja.) Egyébként nullát.</p>
        <p><strong>Elérhető:</strong> <code>http://miserend.hu/api/v3/updated/2015-01-16</code></p>
        HTML;

        $docs['response'] = <<<HTML
        0, ha nem volt változás és 1, ha volt változás.
        HTML;

        return $docs;
    }

    public function run() {
        parent::run();

        $sqlite = new \Api\Sqlite();
        $sqlite->version = $this->version;        
        if(!$sqlite->checkSqliteFile()) {
            $this->return = "0";
            return;
        }

        if( DB::table('templomok')->where('frissites','>=',$this->date)->count() > 0) 
            $this->return = "1";
        else 
            $this->return = "0";
    }

}
