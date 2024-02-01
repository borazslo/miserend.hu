<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Legacy\Api;

use Illuminate\Database\Capsule\Manager as DB;

class Table extends Api
{
    public $tableName;
    public $columns;
    public $table; // output
    public $format = 'json'; // or text
    public $delimiter = ';';
    public $validColumnsTables = [
        'templomok' => [
            'id', 'nev', 'ismertnev', 'turistautak', 'orszag', 'megye', 'varos', 'cim',
            'megkozelites', 'plebania', 'pleb_eml', 'egyhazmegye',
            'espereskerulet', 'leiras', 'megjegyzes', 'miseaktiv', 'misemegj',
            'bucsu', 'frissites', 'lat', 'lon', 'geochecked', 'name', 'alt_name',
            'denomination', 'url'],
    ];

    public function validateVersion()
    {
        if ($this->version < 3) {
            throw new \Exception("API action 'table' is not available under v3.");
        }
    }

    public function validateInput()
    {
        if (!\is_array($this->input['columns']) || [] === $this->input['columns']) {
            throw new \Exception("JSON input 'columns' should be a list/array.");
        }
        foreach ($this->input['columns'] as $column) {
            if (!\in_array($column, $this->validColumnsTables[$this->tableName])) {
                throw new \Exception("Column '$column' is invalid in '$this->tableName'.");
            }
        }
        if (isset($this->input['format']) && !\in_array($this->input['format'], ['json', 'text', 'csv'])) {
            throw new \Exception("Format '".$this->input['format']."' is not supported.");
        }
    }

    public function run()
    {
        parent::run();

        $this->tableName = \App\Legacy\Request::SimpletextRequired('table');
        if (!\array_key_exists($this->tableName, $this->validColumnsTables)) {
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
                $this->table = DB::table('templomok as t')
                        ->SELECT('t.*', 'orszagok.nev as orszag', 'megye.megyenev as megye')
                        ->leftJoin('orszagok', 'orszagok.id', '=', 't.orszag')
                        ->leftJoin('megye', 'megye.id', '=', 'megye')
                        ->where('t.ok', '=', 'i')
                        ->limit(10000)
                        ->get();

                $this->mapTemplomok();
                break;

            default:
                throw new \Exception("Table '$this->tableName' is accepted, but we cannot process.");
                break;
        }

        if ('text' == $this->format) {
            $this->format = 'csv';
        }

        $this->return[$this->tableName] = $this->table;

        return;
    }

    public function mapTemplomok()
    {
        $output = [];
        foreach ($this->table as $row) {
            $tmp = [];
            foreach ($this->columns as $column) {
                // data in mysql
                if (isset($row->$column) && \in_array($column, ['id', 'nev', 'ismertnev', 'turistautak', 'orszag', 'megye', 'varos', 'cim', 'megkozelites', 'plebania', 'pleb_eml', 'egyhazmegye', 'espereskerulet', 'leiras', 'megjegyzes', 'miseaktiv', 'misemegj', 'bucsu', 'frissites', 'lat', 'lon', 'geochecked'])) {
                    $tmp[$column] = $row->$column;
                }
                // simple data mapping
                $mapping = ['name' => 'nev', 'alt_name' => 'ismertnev'];
                if (\array_key_exists($column, $mapping)) {
                    $tmp[$column] = $row->{$mapping[$column]};
                }
                // extra mapping
                switch ($column) {
                    case 'denomination':
                        // http://wiki.openstreetmap.org/wiki/Key:denomination#Christian_denominations
                        if (\in_array($row['egyhazmegye'], [17, 18, 34])) {
                            $tmp[$column] = 'greek_catholic';
                        } else {
                            $tmp[$column] = 'roman_catholic';
                        }
                        break;

                    case 'url':
                        $tmp[$column] = DOMAIN.'/templom/'.$row->id;
                        break;

                    default:
                        // code...
                        break;
                }
            }
            $output[] = $tmp;
        }
        $this->table = $output;
    }
}
