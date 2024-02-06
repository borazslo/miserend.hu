<?php

namespace App;

use Illuminate\Database\Capsule\Manager as DB;

class Parish
{

    public function getByChurchId($id)
    {
        $result = DB::table('templomok')
            ->select('plebania', 'pleb_eml')
            ->where('id', "=", $id)
            ->limit(1)
            ->get();
        if (!count($result)) {
            throw new Exception("There is no church with tid = '$id' (parish).");
        }
        $this->name = "";
        $this->description = $result[0]->plebania;
        $this->email = $result[0]->pleb_eml;
    }

}
