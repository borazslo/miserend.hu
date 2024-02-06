<?php

namespace App\Html;

class StaticPage extends Html {

    public function __construct(array $routeParams)
    {
        $this->template = 'static_page/' . $routeParams['_route'] . '.twig';
    }
}
