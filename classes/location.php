<?php

use Illuminate\Database\Capsule\Manager as DB;

class Location {

    public function getByChurchId($id) {
        $result = DB::table('templomok')
                ->join('terkep_geocode as geo', 'geo.tid', '=', 'templomok.id', 'left')
                ->join('varosok', 'varosok.nev', '=', 'templomok.varos', 'left')
                ->select('templomok.*', 'geo.lat', 'geo.lng', 'geo.checked', 'geo.address2', 'varosok.irsz')
                ->where('templomok.id', "=", $id)
                ->limit(1)
                ->get();
        if (!count($result)) {
            throw new Exception("There is no church with tid = '$id' (location).");
        }

        $acceptedColumns = array('orszag', 'megye', 'varos', 'cim', 'megkozelites', 'lat', 'lng', 'checked', 'address2', 'irsz');
        foreach ($acceptedColumns as $column) {
            $this->$column = $result[0]->$column;
        }
    }

}
