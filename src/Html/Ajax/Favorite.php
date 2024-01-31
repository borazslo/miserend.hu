<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Html\Ajax;

use App\Request;

class Favorite extends Ajax
{
    public function __construct()
    {
        $user = $this->getSecurity()->getUser();

        $tid = Request::IntegerRequired('tid');
        $method = Request::SimpletextRequired('method');
        echo $tid.'-'.$method;
        if ('add' == $method) {
            if (!$user->addFavorites($tid)) {
                throw new \Exception('Could not add favorites.');
            }
        } elseif ('del' == $method) {
            if (!$user->removeFavorites($tid)) {
                throw new \Exception('Could not remove favorites.');
            }
        }
    }
}
