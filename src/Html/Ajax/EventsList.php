<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Html\Ajax;

use Illuminate\Database\Capsule\Manager as DB;

class EventsList extends Ajax
{
    public function __construct()
    {
        $return = DB::table('events')->groupBy('name')->pluck('name');
        $this->content = json_encode(['events' => $return]);
    }
}
