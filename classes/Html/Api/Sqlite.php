<?php

namespace Html\Api;

class Sqlite extends Api {

    public function __construct() {
        ini_set('memory_limit', '256M');

        try {
            $this->api = new \Api\Sqlite();
            $this->api->run();
            $this->redirect('/' . $this->api->sqliteFile);
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
        }
    }

}
