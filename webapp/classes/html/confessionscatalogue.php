<?php

namespace Html;

use Illuminate\Database\Capsule\Manager as DB;

class ConfessionsCatalogue extends Html {

    public function __construct($path) {
        global $user;

        if (!$user->checkRole('miserend')) {
            throw new \Exception('Nincs jogosultságod megnézni az események listáját.');
        }

        $this->confessions = \Eloquent\Confession::select()
                ->orderBy('timestamp', 'desc')
                ->limit(100)
                ->get();

       
       
    }



}
