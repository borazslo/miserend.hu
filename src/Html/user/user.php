<?php

namespace App\Html\User;

class User extends \App\Html\Html {

    static function factory($path) {
        if (is_numeric($path[0])) {
            $_REQUEST['uid'] = $path[0];
            $className = "\Html\\User\\" . $path[1];
        } else {
            $urlmapping = ['new' => 'edit'];
            if (array_key_exists($path[0], $urlmapping)) {
                $class = $urlmapping[$path[0]];
            } else {
                $class = $path[0];
            }
            $className = "\Html\\User\\" . $class;
            return new $className();
        }
        return new $className();
    }

}
