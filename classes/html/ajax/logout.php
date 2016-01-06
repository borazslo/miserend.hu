<?php

namespace Html\Ajax;

class Logout extends Ajax {

    public function __construct() {
        quit();
        addMessage('Sikeresen kiléptünk!', 'info');
    }

}
