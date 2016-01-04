<?php

namespace Html\Ajax;

class CheckUsername extends Ajax {

    public function __construct() {
        if (CheckUsername($_REQUEST['text'])) {
            $this->content = 1;
        } else {
            $this->content = 0;
        }
    }

}
