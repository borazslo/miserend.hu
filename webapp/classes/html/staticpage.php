<?php

namespace Html;

class StaticPage extends Html {

    public function __construct($path) {
        $name = $path[0];
        $this->template = 'StaticPage/' . $name . '.twig';
    }

}
