<?php

use Illuminate\Database\Capsule\Manager as DB;

/**
 * Azok az időzített feladatok, amiknek nincs saját helyük
 */
class Crons {
    
    /**
     * görög katolikus -> görögkatolikus
     */
    static function gorogkatolizalas() {
        $tables = array(
            'templomok' => array('nev', 'ismertnev', 'megjegyzes', 'misemegj', 'leiras', 'megkozelites'),
            'misek' => array('megjegyzes'),
            'photos' => array('title')
        );        
        $c = 0;
        foreach ($tables as $table => $fields) {
            foreach ($fields as $key => $field) {
                $results = DB::table($table)
                        ->select('id',$field)
                        ->where($field,'LIKE','%görög katolikus%')
                        ->get();
                foreach($results as $row) {
                    $text = preg_replace('/(görög) katolikus/i', '$1katolikus', $row->$field);
                    DB::table($table)
                            ->where('id',$row->id)
                            ->update([$field => $text]);                    
                    $c++;
                }
            }
        }
        if( $c > 0 )
            echo $c . " db görögkatolizálás<br/>";
    }

}


