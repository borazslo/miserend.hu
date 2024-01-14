<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App;

use Illuminate\Database\Capsule\Manager as DB;

/**
 * Azok az időzített feladatok, amiknek nincs saját helyük.
 */
class Crons
{
    /**
     * görög katolikus -> görögkatolikus.
     */
    public static function gorogkatolizalas()
    {
        $tables = [
            'templomok' => ['nev', 'ismertnev', 'megjegyzes', 'misemegj', 'leiras', 'megkozelites'],
            'misek' => ['megjegyzes'],
            'photos' => ['title'],
        ];
        $c = 0;
        foreach ($tables as $table => $fields) {
            foreach ($fields as $key => $field) {
                $results = DB::table($table)
                    ->select('id', $field)
                    ->where($field, 'LIKE', '%görög katolikus%')
                    ->get();
                foreach ($results as $row) {
                    $text = preg_replace('/(görög) katolikus/i', '$1katolikus', $row->$field);
                    DB::table($table)
                        ->where('id', $row->id)
                        ->update([$field => $text]);
                    ++$c;
                }
            }
        }
        if ($c > 0) {
            echo $c.' db görögkatolizálás<br/>';
        }
    }

    /**
     * misek.tol és misek.ig -> misek.tmp_datumtol, misek.tmp_relation, misek.tmp_datumig.
     */
    public static function generateMassTolIgTmp($where = false)
    {
        $results = DB::table('misek')
            ->select('id', 'tol', 'ig')
            ->where('torles', '0000-00-00 00:00:00');
        if (false != $where) {
            $results = $results->whereRaw($where);
        }
        $results = $results->get();
        foreach ($results as $row) {
            if ('' == $row->tol) {
                $row->tol = '01-01';
            }
            $row->tmp_datumtol = event2Date($row->tol);
            if ('' == $row->ig) {
                $row->ig = '12-31';
            }
            $row->tmp_datumig = event2Date($row->ig);
            if ($row->tmp_datumig > $row->tmp_datumtol) {
                $row->tmp_relation = '<';
            } elseif ($row->tmp_datumtol == $row->tmp_datumig) {
                $row->tmp_relation = '=';
            } else {
                $row->tmp_relation = '>';
            }

            DB::table('misek')
                ->where('id', $row->id)
                ->update(collect($row)->toArray());
        }
    }
}
