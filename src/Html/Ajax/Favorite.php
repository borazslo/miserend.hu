<?php

namespace App\Html\Ajax;

class Favorite extends Ajax {

    public function __construct() {
        global $user;

        $tid = \App\Request::IntegerRequired('tid');
        $method = \App\Request::SimpletextRequired('method');
        echo $tid."-".$method;
        if ($method == 'add') {
            if (!$user->addFavorites($tid)) {
                throw new \Exception("Could not add favorites.");
            }
        } else if ($method == 'del') {
            if (!$user->removeFavorites($tid)) {
                throw new \Exception("Could not remove favorites.");
            }
        }
    }

}
