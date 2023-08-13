<?php

namespace Api;

use Illuminate\Database\Capsule\Manager as DB;
        
class Updated extends Api {

    public $format = 'text';

    public function validateVersion() {
        if ($this->version < 2) {
            throw new \Exception("API action 'updated' is not available under v2.");
        }
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
