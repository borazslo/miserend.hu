<?php

namespace Html\Ajax;

class JSONP_miserend extends Ajax {

    public function __construct() {
        $this->content = widget_miserend($_REQUEST);
    }

}
