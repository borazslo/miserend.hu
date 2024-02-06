<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Legacy\Html\Ajax;

class CheckUsername extends Ajax
{
    public function __construct()
    {
        if (CheckUsername($_REQUEST['text'])) {
            $this->content = 1;
        } else {
            $this->content = 0;
        }
    }
}
