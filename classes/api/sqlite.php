<?php

namespace Api;

use Illuminate\Database\Capsule\Manager as DB;

class Sqlite extends Api {

    public $format = false;
    public $sqliteFileName;
    public $folder = 'fajlok/sqlite/';
    public $sqlite;

    public function run() {
        parent::run();

        $this->sqliteFileName = 'miserend_v' . $this->version . '.sqlite3';

        if ($this->generateSqlite()) {
            //Sajnos ez itten nem működik... Nem lesz szépen letölthető.  Headerrel sem
            //$data = readfile($sqllitefile); exit($data);
            return true;
        } else {
            throw new \Exception("Could not make the requested sqlite3 file.");
        }
    }

    function connectToSqlite($name, $file = false) {
        try {
            $this->sqlite = DB::connection($name);
        } catch (\InvalidArgumentException $e) {
            if ($file == false) {
                throw new \Exception("Sqlite connection '$name' does not exists and there is no file for it to open.");
            }
            if (!file_exists($file)) {
                $this->createEmptySqliteFile($file);
            }
            global $capsule;
            $capsule->addConnection([
                'driver' => 'sqlite',
                'database' => $file,
                'charset' => 'utf8',
                'collation' => 'utf8_unicode_ci'
                    ], $name);
            $this->sqlite = DB::connection($name);
        }
    }

    function getDatabaseToArray() {
        if (!isset($this->sqlite)) {
            throw nex \Exception('There is no Sqlite connectionto make it an array.');
        }
        $array = [];
        $tables = $this->sqlite->table('sqlite_master')->select('name')->get();
        foreach ($tables as $table) {
            $rows = $this->sqlite->table($table->name)->get();
            foreach ($rows as $row) {
                $array[$table->name][] = (array) $row;
            }
        }
        return $array;
    }

    function generateSqlite() {
        echo "Sqlite is beginning right now...";
        $this->sqliteFile = PATH . $this->folder . $this->sqliteFileName;

        $this->connectToSqlite('sqlite_v' . $this->version, $this->sqliteFile);
        $this->sqlite->beginTransaction();

        $this->dropAllTables();
        echo "\nCreate Tables ...";
        $this->createTables();
        $this->insertData();
        echo "\n";
        echo $this->sqliteFile;
        $this->sqlite->commit();
        DB::disconnect('sqlite_v' . $this->version);
        return true;
    }

    function dropAllTables() {
        $tables = $this->sqlite->table('sqlite_master')->select('name')->get();
        foreach ($tables as $table) {
            $this->sqlite->statement("DROP TABLE IF EXISTS " . $table->name);
        }
    }

    function createTables() {
        $this->createTableTemplomok();
        $this->createTableMisek();
        if ($this->version > 1) {
            $this->createTableKepek();
        }
    }

    function insertData() {
        ini_set('memory_limit', '800M');
        DB::disableQueryLog();
        $this->sqlite->disableQueryLog();
        echo "\ninsertDataTemplomok ... \n";
        $this->insertDataTemplomok();
        echo "\ninsertDataMisek ... \n";
        $this->insertDataMisek();
        if ($this->version > 1) {
            $this->insertDataKepek();
        }
        $this->sqlite->enableQueryLog();
        DB::enableQueryLog();
    }

    function createTableTemplomok() {
        $createtabletemplomok = "CREATE TABLE IF NOT EXISTS [templomok] (
            [tid] INTEGER  NOT NULL PRIMARY KEY,
            [nev] VARCHAR(200)  NULL,
            [ismertnev] vaRCHAR(200)  NULL,";

        if ($this->version > 2)
            $createtabletemplomok .= "
            [gorog] INTEGER NULL,";

        $createtabletemplomok .= "
            [orszag] vARCHAR(30)  NULL,
            [megye] vARCHAR(80)  NULL,
            [varos] vaRCHAR(80)  NULL,
            [cim] vARCHAR(255)  NULL,
            [geocim] vARCHAR(255)  NULL,
            [megkozelites] vARCHAR(255)  NULL,
            [lng] fLOAT  NULL,
            [lat] flOAT  NULL,";

        if ($this->version < 4)
            $createtabletemplomok .= "
            [nyariido] vARCHAR(10)  NULL,
            [teliido]vARCHAR(10)  NULL,";

        $createtabletemplomok .= "
            [kep] vARCHAR(255)  NULL        
        )";

        $this->sqlite->statement($createtabletemplomok);
    }

    function createTableMisek() {
        $createtablemisek = "CREATE TABLE IF NOT EXISTS [misek] (
            [mid] INTEGER  PRIMARY KEY NOT NULL,
            [tid] iNTEGER  NULL,";

        if ($this->version < 4)
            $createtablemisek .= "      [telnyar] VARCHAR(1)  NULL,";

        if ($this->version > 3) {
            $createtablemisek .= "      
                [periodus] VARCHAR(4)  NULL,
                [idoszak] VARCHAR(255)  NULL,
                [suly] INT NULL,
                [datumtol] INT  NULL,
                [datumig] INT  NULL,";
        }

        $createtablemisek .= "
            [nap] inTEGER  NULL,
            [ido] TIME  NULL,
            [nyelv] VARCHAR(3)  NULL,
            [milyen] VARCHAR(10)  NULL";

        if ($this->version > 2)
            $createtablemisek .= "
            , [megjegyzes] VARCHAR(255) NULL";
        $createtablemisek .= "  )";

        $this->sqlite->statement($createtablemisek);
    }

    function createTableKepek() {
        $this->sqlite->statement("CREATE TABLE IF NOT EXISTS [kepek] (
            [kid] INTEGER  PRIMARY KEY NOT NULL,
            [tid] INTEGER  NULL,
            [kep] vARCHAR(255)  NULL
        )");
    }

    function insertDataTemplomok() {
        set_time_limit(120);
        $churches = \Eloquent\Church::where('ok', 'i')->orderBy('id')->get();
        if (!$churches) {
            throw new Exception("There are no valid churches.");
        }
        $sum = count($churches);
        $c = 1;
        foreach ($churches as $church) {
            $line = "v" . $this->version . " " . (int) ( microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]) . "s : " . $c++ . "/" . $sum . " -- " . $church->id . " " . $church->nev;
            echo "\r" . str_pad($line, 120);
            $church->location;

            $insert = [
                'tid' => $church->id,
                'nev' => $church->nev,
                'ismertnev' => $church->ismertnev
            ];

            //Location
            $insert['orszag'] = $church->location->country;
            if (isset($church->location->county)) {
                $insert['megye'] = $church->location->county;
            } else {
                $insert['megye'] = "";
            }
            $insert['varos'] = $church->location->city;
            $insert['cim'] = $church->cim;
            $insert['geocim'] = $church->geoaddress;
            $insert['lng'] = $church->location->lon;
            $insert['lat'] = $church->location->lat;
            $insert['megkozelites'] = $church->location->access;

            if ($this->version > 2) {
                if (in_array($church->egyhazmegye, array(18, 17))) { //Görög egyházmegyék kódja
                    $insert['gorog'] = 1;
                } else
                    $insert['gorog'] = 0;
            }

            if ($this->version < 4) {
                $insert['nyariido'] = date('Y-') . date('m-d', strtotime($church->nyariido));
                $insert['teliido'] = date('Y-') . date('m-d', strtotime($church->teliido));
            }

            if (isset($church->photos[0])) {
                $insert['kep'] = DOMAIN . "/kepek/templomok/" . $church->id . "/" . $church->photos[0]->filename;
            } else {
                $insert['kep'] = '';
            }
            $inserts[] = $insert;
        }
        $this->insertDataSql('templomok', $inserts);
    }

    function insertDataMisek() {
        set_time_limit(60);
        $masses = DB::table('misek')->where('torles', '0000-00-00 00:00:00')->where('tid', '<>', 0)->orderBy('tid')->orderBy('id')->get();
        if (!$masses) {
            throw new Exception("There are no valid masses.");
        }

        $c = 1;
        $sum = count($masses);
        foreach ($masses as $mass) {
            $line = "v" . $this->version . " " . (int) ( microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]) . "s : " . $c++ . "/" . $sum . " -- " . $mass->id . " (in " . $mass->tid . ")";
            echo "\r" . str_pad($line, 120);
            $insert = [
                'mid' => $mass->id,
                'tid' => $mass->tid,
                'nap' => $mass->nap,
                'ido' => $mass->ido,
                'nyelv' => $mass->nyelv,
                'milyen' => $mass->milyen,
            ];

            if ($this->version >= 4) {
                $insert['datumtol'] = preg_replace('/-/i', '', $mass->tmp_datumtol);
                $insert['datumig'] = preg_replace('/-/i', '', $mass->tmp_datumig);
                $insert['periodus'] = $mass->nap2;
                $insert['idoszak'] = $mass->idoszamitas;
                $insert['suly'] = $mass->weight;
            }

            if ($this->version >= 3) {
                $insert['megjegyzes'] = $mass->megjegyzes;
            }

            if ($this->version < 4) {
                if (preg_match('/^(t$|tél)/i', $mass->idoszamitas)) {
                    $insert['telnyar'] = 't';
                } elseif (preg_match('/^(ny$|nyár)/i', $mass->idoszamitas)) {
                    $insert['telnyar'] = 'ny';
                } elseif ($mass->idoszamitas == 'egész évben') {
                    $insert['telnyar'] = 'ny';
                    $extraInsert = $insert;
                    $extraInsert['telnyar'] = 't';
                    $extraInsert['mid'] = $insert['mid'] + 1000000;
                    $inserts[] = $extraInsert;
                } else {
                    unset($insert);
                }
            }

            if (isset($insert)) {
                $inserts[] = $insert;
            }
        }
        $this->insertDataSql('misek', $inserts);
    }

    function insertDataKepek() {
        $photos = \Eloquent\Photo::orderBy('church_id')->get();
        if (!$photos) {
            throw new Exception("There are no valid churches.");
        }

        foreach ($photos as $photo) {
            $insert = [
                'kid' => $photo->id,
                'tid' => $photo->church_id,
                'kep' => DOMAIN . $photo->url
            ];
            $inserts[] = $insert;
        }
        $this->insertDataSql('kepek', $inserts);
    }

    function insertDataSql($table, $inserts) {
        $limit = (int) ( 999 / count($inserts[0]) ); //SQLite variable limit is 999       
        $churchChunks = array_chunk($inserts, $limit);
        foreach ($churchChunks as $chunk) {
            $this->sqlite->table($table)->insert($chunk);
        }
    }

    function getEmptySqliteFile() {
        $coded = "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MCkgIE5VTEwsCiAgICAgICAgICAgIFtpc21lcnRuZXZdIHZhUkNIQVIoMjAwKSAgTlVMTCwKICAgICAgICAgICAgW29yc3phZ10gdkFSQ0hBUigzMCkgIE5VTEwsCiAgICAgICAgICAgIFttZWd5ZV0gdkFSQ0hBUig4MCkgIE5VTEwsCiAgICAgICAgICAgIFt2YXJvc10gdmFSQ0hBUig4MCkgIE5VTEwsCiAgICAgICAgICAgIFtjaW1dIHZBUkNIQVIoMjU1KSAgTlVMTCwKICAgICAgICAgICAgW2dlb2NpbV0gdkFSQ0hBUigyNTUpICBOVUxMLAogICAgICAgICAgICBbbWVna296ZWxpdGVzXSB2QVJDSEFSKDI1NSkgIE5VTEwsCiAgICAgICAgICAgIFtsbmddIGZMT0FUICBOVUxMLAogICAgICAgICAgICBbbGF0XSBmbE9BVCAgTlVMTCwKICAgICAgICAgICAgW255YXJpaWRvXSB2QVJDSEFSKDEwKSAgTlVMTCwKICAgICAgICAgICAgW3RlbGlpZG9ddkFSQ0hBUigxMCkgIE5VTEwsCiAgICAgICAgICAgIFtrZXBdIHZBUkNIQVIoMjU1KSAgTlVMTCAgICAgICAgCiAgICAgICAgKQ==";
        return base64_decode($coded);
    }

    function createEmptySqliteFile($file) {
        $emptySqliteFile = $this->getEmptySqliteFile();
        $fp = fopen($file, 'w');
        fwrite($fp, $emptySqliteFile);
        fclose($fp);
        return true;
    }

    function cron() {
        for ($i = 2; $i <= 4; $i++) {
            $_REQUEST['v'] = $i;
            $this->run();
        }
    }

    function bufferout($newline, $fullLength = false) {
        if (!$fullLength)
            $fullLength = 120;
        $length = strlen(rtrim($newline));

        $whitespaceLength = $fullLength - $length;
        if ($whitespaceLength > 0) {
            $return = str_repeat(" ", $whitespaceLength);
        }
        echo $newline . $return . "\r";
    }

}
