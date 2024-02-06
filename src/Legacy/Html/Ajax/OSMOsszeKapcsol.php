<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Legacy\Html\Ajax;

use function App\Html\Ajax\osm_kapcsol_ment;

class OSMOsszeKapcsol extends Ajax
{
    public function __construct()
    {
        $this->content = osm_kapcsol_ment($_POST['oid'], $_POST['tid']);
    }
}
