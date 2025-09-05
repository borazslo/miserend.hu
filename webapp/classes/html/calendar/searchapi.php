<?php

namespace Html\Calendar;

class SearchApi extends \Html\Html {

    public $template = "layout_empty.twig";

    public function __construct($path) {
        $this->content = json_encode($_REQUEST);
    }
}