<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Legacy;

use Illuminate\Database\Capsule\Manager as DB;

class Parish
{
    private $name;
    private $description;
    private $email;

    public function getByChurchId($id)
    {
        $result = DB::table('templomok')
            ->select('plebania', 'pleb_eml')
            ->where('id', '=', $id)
            ->limit(1)
            ->get();

        if (!\count($result)) {
            throw new \Exception("There is no church with tid = '$id' (parish).");
        }

        $this->name = '';
        $this->description = $result[0]->plebania;
        $this->email = $result[0]->pleb_eml;
    }
}
