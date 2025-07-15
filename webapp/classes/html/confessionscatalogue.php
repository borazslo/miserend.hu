<?php

namespace Html;

use Illuminate\Database\Capsule\Manager as DB;

class ConfessionsCatalogue extends Html {
    public $confessions = [];

    public function __construct($path) {
        global $user;

        if (!$user->checkRole('miserend')) {
            throw new \Exception('Nincs jogosultságod megnézni az események listáját.');
        }

        $this->confessions = \Eloquent\Confession::select()
                ->orderBy('timestamp', 'desc')
                ->limit(100)
                ->get();

        // Minden confession elem fulldata mezőjét json_decode-oljuk
        foreach ($this->confessions as $confession) {
            if (isset($confession->fulldata) && is_string($confession->fulldata)) {
                $confession->fulldata = json_decode($confession->fulldata, true);
            } else {
                $confession->fulldata = null;
            }
        }
    }
}
