<?php

namespace Html\Ajax;

class Favorite extends Ajax {

    public function __construct() {
        global $user;

        $tid = \Request::IntegerRequired('tid');
        $method = \Request::SimpletextRequired('method');
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
