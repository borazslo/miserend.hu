<?php

namespace Api;

use Illuminate\Database\Capsule\Manager as DB;

class Table extends Api {

    public $tableName;
    public $columns;
    public $table; //output
    public $format = 'json'; //or text
    public $delimiter = ';';
    public $validColumnsTables = array(
        'templomok' => array(
            'id', 'nev', 'ismertnev', 'turistautak', 'orszag', 'megye', 'varos', 'cim',
            'megkozelites', 'plebania', 'pleb_eml', 'egyhazmegye',
            'espereskerulet', 'leiras', 'megjegyzes', 'miseaktiv', 'misemegj',
            'frissites', 'lat', 'lon', 'geochecked', 'name', 'alt_name',
            'denomination', 'url'),
    );

    public function validateVersion() {
        if ($this->version < 3) {
            throw new \Exception("API action 'table' is not available under v3.");
        }
    }

    public function validateInput() {
        if (!is_array($this->input['columns']) OR $this->input['columns'] === array()) {
            throw new \Exception("JSON input 'columns' should be a list/array.");
        }
        foreach ($this->input['columns'] as $column) {
            if (!in_array($column, $this->validColumnsTables[$this->tableName])) {
                throw new \Exception("Column '$column' is invalid in '$this->tableName'.");
            }
        }
        if (isset($this->input['format']) AND ! in_array($this->input['format'], array('json', 'text', 'csv'))) {
            throw new \Exception("Format '" . $this->input['format'] . "' is not supported.");
        }
    }

    public function run() {
        parent::run();

        $this->tableName = \Request::SimpletextRequired('table');
        if (!array_key_exists($this->tableName, $this->validColumnsTables)) {
            throw new \Exception("Table '$this->tableName' is invalid.");
        }
        $this->getInputJson();

        if (isset($this->input['delimiter'])) {
            $this->delimiter = $this->input['delimeter'];
        }
        if (isset($this->input['format'])) {
            $this->format = $this->input['format'];
        }
        $this->columns = $this->input['columns'];


        switch ($this->tableName) {
            case 'templomok':
                
                $this->table = DB::table("templomok as t")
                        ->SELECT("t.*","orszagok.nev as orszag","megye.megyenev as megye")
                        ->leftJoin('orszagok', 'orszagok.id','=','t.orszag')
                        ->leftJoin('megye', 'megye.id',"=","megye")
                        ->where('t.ok',"=","i")
                        ->limit(10000)
                        ->get();
                
                $this->mapTemplomok();
                break;

            default:
                throw new \Exception("Table '$this->tableName' is accepted, but we cannot process.");
                break;
        }

        if ($this->format == 'text')
            $this->format = 'csv';

        $this->return[$this->tableName] = $this->table;

        return;
    }
    
  
    function mapTemplomok() {
        $output = array();
        foreach ($this->table as $row) {
            $tmp = array();
            foreach ($this->columns as $column) {
                // data in mysql
                if (isset($row->$column) AND in_array($column, array('id', 'nev', 'ismertnev', 'turistautak', 'orszag', 'megye', 'varos', 'cim', 'megkozelites', 'plebania', 'pleb_eml', 'egyhazmegye', 'espereskerulet', 'leiras', 'megjegyzes', 'miseaktiv', 'misemegj', 'bucsu', 'frissites', 'lat', 'lon', 'geochecked'))) {
                    $tmp[$column] = $row->$column;
                }
                // simple data mapping
                // FIXME for Issue #257
                $mapping = array('name' => 'nev', 'alt_name' => 'ismertnev');
                if (array_key_exists($column, $mapping)) {
                    $tmp[$column] = $row->{$mapping[$column]};
                }
                //extra mapping
                switch ($column) {
                    case 'denomination':
                        //http://wiki.openstreetmap.org/wiki/Key:denomination#Christian_denominations
                        if (in_array($row['egyhazmegye'], array(17, 18, 34))) {
                            $tmp[$column] = 'greek_catholic';
                        } else {
                            $tmp[$column] = 'roman_catholic';
                        }
                        break;

                    case 'url':
                        $tmp[$column] = DOMAIN . '/templom/' . $row->id;
                        break;

                    default:
                        # code...
                        break;
                }
            }
            $output[] = $tmp;
        }
        $this->table = $output;
    }

}
