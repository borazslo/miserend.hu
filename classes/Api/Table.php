<?php

namespace Api;

class Table extends Api {

    public $tableName;
    public $columns;
    public $table; //output
    public $format = 'json'; //or text
    public $delimiter = ';';
    public $validColumnsTables = array(
        'templomok' => array(
            'id', 'nev', 'ismertnev', 'turistautak', 'orszag', 'megye', 'varos', 'cim',
            'megkozelites', 'plebania', 'pleb_url', 'pleb_eml', 'egyhazmegye',
            'espereskerulet', 'leiras', 'megjegyzes', 'miseaktiv', 'misemegj',
            'bucsu', 'frissites', 'lat', 'lng', 'checked', 'name', 'alt_name',
            'denomination', 'url', 'lon'),
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
        if (isset($this->input['format']) AND ! in_array($this->input['format'], array('json', 'text'))) {
            throw new \Exception("Format '" . $this->input['format'] . "' is not supported.");
        }
    }

    public function run() {
        parent::run();

        $this->tableName = $this->request->SimpletextRequired('table');
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
                $this->prepareTemplomokQuery();
                $this->runQuery();
                $this->mapTemplomok();
                break;

            default:
                throw new \Exception("Table '$this->tableName' is accepted, but we cannot process.");
                break;
        }

        switch ($this->format) {
            case 'json':
                $this->return[$this->tableName] = $this->table;

                break;

            case 'text':
                break;

            default:
                throw new \Exception("Format '" . $this->input['format'] . "' is not supported.");
                break;
        }

        $this->return[$this->tableName] = $this->table;

        return;
    }

    function runQuery() {
        $this->resultRows = array();
        $result = mysql_query($this->query);
        while ($row = mysql_fetch_assoc($result)) {
            $this->table[] = $row;
        }
    }

    function prepareTemplomokQuery() {
        $this->query = "SELECT t.*,orszagok.nev as orszag, megye.megyenev as megye,lat,lng, checked FROM templomok as t 
                    LEFT JOIN orszagok ON orszagok.id = t.orszag 
                    LEFT JOIN megye ON megye.id = megye 
                    LEFT JOIN terkep_geocode ON terkep_geocode.tid = t.id 
                    WHERE t.ok = 'i' 
                    LIMIT 10000";
    }

    function mapTemplomok() {
        $output = array();
        foreach ($this->table as $row) {
            $tmp = array();
            foreach ($this->columns as $column) {
                // data in mysql
                if (isset($row[$column]) AND in_array($column, array('id', 'nev', 'ismertnev', 'turistautak', 'orszag', 'megye', 'varos', 'cim', 'megkozelites', 'plebania', 'pleb_url', 'pleb_eml', 'egyhazmegye', 'espereskerulet', 'leiras', 'megjegyzes', 'miseaktiv', 'misemegj', 'bucsu', 'frissites', 'lat', 'lng', 'checked'))) {
                    $tmp[$column] = $row[$column];
                }
                // simple data mapping
                $mapping = array('name' => 'nev', 'alt_name' => 'ismertnev', 'lon' => 'lng');
                if (array_key_exists($column, $mapping)) {
                    $tmp[$column] = $row[$mapping[$column]];
                }
                //extra mapping
                switch ($column) {
                    case 'denomination':
                        //http://wiki.openstreetmap.org/wiki/Key:denomination#Christian_denominations
                        if (in_array($row['egyhazmegye'], array(17, 18))) {
                            $tmp[$column] = 'greek_catholic';
                        } else {
                            $tmp[$column] = 'roman_catholic';
                        }
                        break;

                    case 'url':
                        $tmp[$column] = 'http://miserend.hu/?templom=' . $row['id'];
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

    public function printOutputText() {
        if (is_array($this->return)) {
            //TODO: a szöveg nem tartalmazhatja az elválasztó karaktert, különben gond van.
            $columnNames = array_keys($this->return[$this->tableName][0]);
            echo implode($this->delimiter, $columnNames), ";\n";
            foreach ($this->return[$this->tableName] as $row) {
                echo implode($this->delimiter, $row), ";\n";
            }
        } else {
            echo $this->return;
        }
    }

}
