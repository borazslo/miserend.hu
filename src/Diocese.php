<?php

/*
 * This file is part of the Miserend App.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App;

use Illuminate\Database\Capsule\Manager as DB;

class Diocese
{
    public $id;

    private string $name;
    private string $shortname;
    private array $responsible;

    public function getByChurchId($id)
    {
        $result = DB::table('templomok')
            ->where('templomok.id', '=', $id)
            ->select('egyhazmegye')
            ->limit(1)
            ->get();
        if (!\count($result)) {
            throw new \Exception("There is no church with tid = '$id' (diocese).");
        }
        $this->id = $result[0]->egyhazmegye;
        $this->getById($this->id);
    }

    public function getById($id)
    {
        $this->id = $id;

        $diocese = DB::table('egyhazmegye')
            ->select('*')
            ->where('id', '=', $this->id)
            ->limit(1)
            ->get();

        if (!\count($diocese)) {
            throw new \Exception("There is no diocese with id = '$id'");
        }

        $this->name = $diocese[0]->nev;
        $this->shortname = $diocese[0]->nev;
        $this->responsible = [];

        if ('' != $diocese[0]->felelos) {
            $this->responsible[] = $diocese[0]->felelos;
        }
    }
}
