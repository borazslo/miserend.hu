<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Html\Ajax;

class JSONP_miserend extends Ajax
{
    public function __construct()
    {
        $this->content = widget_miserend($_REQUEST);
    }
}
