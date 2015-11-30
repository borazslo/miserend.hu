<?php

namespace Api;

class Updated extends Api {

    public $format = 'text';

    public function validateVersion() {
        if ($this->version < 2) {
            throw new \Exception("API action 'updated' is not available under v2.");
        }
    }

    public function run() {
        parent::run();
        $query = "SELECT id, moddatum FROM templomok WHERE  moddatum >= '" . $this->date . "' ";
        $result = mysql_query($query);
        if (mysql_num_rows($result) > 0)
            $this->return = "1";
        else
            $this->return = "0";
    }

}
