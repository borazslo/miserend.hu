<?php

namespace App;

use Illuminate\Database\Capsule\Manager as DB;

class Deaconry
{

    public $id;

    public function getByChurchId($id)
    {
        $result = DB::table('templomok')
            ->where('templomok.id', "=", $id)
            ->select("espereskerulet")
            ->limit(1)
            ->get();
        if (!count($result)) {
            throw new Exception("There is no church with tid = '$id' (deaconry).");
        }
        $this->id = $result[0]->espereskerulet;
        $this->getById($this->id);
    }

    public function getById($id)
    {
        $this->id = $id;

        $deocanry = DB::table("espereskerulet")->select("*")
            ->where("id", "=", $this->id)
            ->limit(1)
            ->get();
        if (!count($deocanry)) {
            //throw new Exception("There is no deocanry with id = '$id'");
            $this->name = "";
            $this->shortname = "";
        } else {
            $this->name = $deocanry[0]->nev." espereskerület";
            $this->shortname = $deocanry[0]->nev;
        }
    }

}
