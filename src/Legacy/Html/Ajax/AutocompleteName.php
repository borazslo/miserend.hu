<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Legacy\Html\Ajax;

use Illuminate\Database\Capsule\Manager as DB;

class AutocompleteName extends Ajax
{
    public function __construct()
    {
        $text = \App\Legacy\Request::TextRequired('text');
        $type = \App\Legacy\Request::InArrayRequired('type', ['period', 'particular']);

        $query = DB::table('misek')
                    ->select('idoszamitas', 'tol', 'ig')
                    ->where('torles', '0000-00-00 00:00:00')
                    ->where('idoszamitas', 'LIKE', '%'.$text.'%');
        if ($type == 'period') {
            $query = $query->where('tmp_datumtol', '<>', 'tmp_datumig');
        } elseif ($type == 'particular') {
            $query = $query->where('tmp_datumtol', 'tmp_datumig');
        }

        $results = $query->groupBy('idoszamitas')
                ->orderBy('idoszamitas')
                ->limit(10)
                ->get();

        $return = [];
        foreach ($results as $row) {
            preg_match('/^(.*?)( -[0-9]{1,3}| \+[0-9]{1,3}|)$/', $row->tol, $from);
            preg_match('/^(.*?)( -[0-9]{1,3}| \+[0-9]{1,3}|)$/', $row->ig, $to);
            if ($to[2] == '') {
                $to[2] = '0';
            }
            if ($from[2] == '') {
                $from[2] = '0';
            }
            $return[] = ['label' => preg_replace('/('.$text.')/i', '<b>$1</b>', $row->idoszamitas), 'value' => $row->idoszamitas, 'from' => $from[1], 'from2' => trim($from[2]), 'to' => $to[1], 'to2' => trim($to[2])];
        }
        $this->content = json_encode(['results' => $return]);
    }
}
