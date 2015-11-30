<?php

namespace Api;

class Sqlite extends Api {

    public $format = false;

    public function run() {
        parent::run();

        $sqllitefile = 'fajlok/sqlite/miserend_v' . $this->version . '.sqlite3';
        if (file_exists($sqllitefile) && strtotime("-20 hours") < filemtime($sqllitefile) AND $config['debug'] == 0 AND ! isset($date)) {
            header("Location: /" . $sqllitefile);
        } else if (generateSqlite($this->version, 'fajlok/sqlite/miserend_v' . $this->version . '.sqlite3')) {
            //Sajnos ez itten nem működik... Nem lesz szépen letölthető.  Headerrel sem
            //$data = readfile($sqllitefile); exit($data);
            header("Location: /" . $sqllitefile);
        } else {
            throw new \Exception("Could not make the requested sqlite3 file.");
        }
    }

}
