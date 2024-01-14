<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Html;

class StaticPage extends Html
{
    public function __construct(array $routeParams)
    {
        $this->template = 'static_page/'.$routeParams['_route'].'.twig';
    }
}
