<?php

namespace App\Html\Ajax;

class Ajax extends \App\Html\Html {

    public $template = "layout_empty.twig";

    public function __construct($path) {
        $this->content = json_encode($_REQUEST);
    }
}
