<?php

namespace Html\Calendar;

class CalendarApi extends \Html\Html {

    public $template = "layout_empty.twig";

    public function __construct($path) {
        $this->content = json_encode($_REQUEST);
    }
}