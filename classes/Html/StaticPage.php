<?php

namespace Html;

class StaticPage extends Html {

    public function __construct() {
        $name = \Request::SimpletextRequired('name');
        $this->template = 'staticpage_' . $name . '.twig';
    }

}
