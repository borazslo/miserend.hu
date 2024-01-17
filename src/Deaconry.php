<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App;

use Illuminate\Database\Capsule\Manager as DB;

class Deaconry
{
    public $id;

    private $name;
    private $shortname;

    public function getByChurchId($id)
    {
        $result = DB::table('templomok')
            ->where('templomok.id', '=', $id)
            ->select('espereskerulet')
            ->limit(1)
            ->get();
        if (!\count($result)) {
            throw new \Exception("There is no church with tid = '$id' (deaconry).");
        }
        $this->id = $result[0]->espereskerulet;
        $this->getById($this->id);
    }

    public function getById($id)
    {
        $this->id = $id;

        $deaconry = DB::table('espereskerulet')->select('*')
            ->where('id', '=', $this->id)
            ->limit(1)
            ->get();

        if (!\count($deaconry)) {
            // throw new Exception("There is no deocanry with id = '$id'");
            $this->name = '';
            $this->shortname = '';
        } else {
            $this->name = $deaconry[0]->nev.' espereskerület';
            $this->shortname = $deaconry[0]->nev;
        }
    }
}
