<?php

namespace App\Html\Ajax;

use Illuminate\Database\Capsule\Manager as DB;

class AutocompleteEvents extends Ajax {

    public function __construct() {
        $text = \App\Request::Text('text');
        if ($text == '' OR preg_match('/^[0-9]{1}/i', $text)) {
            $return[] = array('label' => '<i>hónap és nap (hh-nn)</i>', 'value' => date('m-d'));
            $return[] = array('label' => '<i>pontos dátum (éééé-hh-nn)</i>', 'value' => date('Y-m-d'));
            $return[] = array('label' => '<i>vagy megfelelő kifejezés</i>', 'value' => '');
        }

        $return = DB::table('events')->select('name as label','name as value')->where('name','LIKE','%'.$text.'%')->groupBy('name')->get();
        $this->content = json_encode(array('results' => $return));
    }

}
 