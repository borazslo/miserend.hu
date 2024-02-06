<?php

namespace App\Html\Ajax;

use Illuminate\Database\Capsule\Manager as DB;

class EventsList extends Ajax {

    public function __construct() {
        $return = DB::table('events')->groupBy('name')->pluck('name');        
        $this->content = json_encode(array('events' => $return));
    }

}
