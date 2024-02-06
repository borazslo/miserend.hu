<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Legacy\Html;

class Help extends Html
{
    public function __construct($path)
    {
        $this->setTitle('Súgó');
        $this->content = '';

        // TODO: validate
        $idT = explode('-', $path[0]);
        foreach ($idT as $id) {
            $help = new \App\Legacy\Help($id);
            $this->content .= $help->html;
        }
    }
}
