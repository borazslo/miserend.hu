<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Html\Ajax;

use Illuminate\Database\Capsule\Manager as DB;

class AutocompleteEvents extends Ajax
{
    public function __construct()
    {
        $text = \App\Request::Text('text');
        if ('' == $text || preg_match('/^[0-9]{1}/i', $text)) {
            $return[] = ['label' => '<i>hónap és nap (hh-nn)</i>', 'value' => date('m-d')];
            $return[] = ['label' => '<i>pontos dátum (éééé-hh-nn)</i>', 'value' => date('Y-m-d')];
            $return[] = ['label' => '<i>vagy megfelelő kifejezés</i>', 'value' => ''];
        }

        $return = DB::table('events')->select('name as label', 'name as value')->where('name', 'LIKE', '%'.$text.'%')->groupBy('name')->get();
        $this->content = json_encode(['results' => $return]);
    }
}
