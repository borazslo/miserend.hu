<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Html\Ajax;

use App\Html\Html;

class Ajax extends Html
{
    public $template = 'layout_empty.twig';

    public function __construct($path)
    {
        $this->content = json_encode($_REQUEST);
    }
}
