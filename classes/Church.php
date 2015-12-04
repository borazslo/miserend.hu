<?php

use Illuminate\Database\Capsule\Manager as DB;

class Church {

    public function __construct($tid) {

        $result = DB::table('templomok')
                ->join('terkep_geocode as geo', 'geo.tid', '=', 'templomok.id','left')
                ->join('osm', 'osm.tid', '=', 'templomok.id','left')
                ->join('varosok', 'varosok.nev', '=', 'templomok.varos','left')
                ->select('templomok.*', 'geo.lat', 'geo.lng', 'geo.checked', 'geo.address2', 'osm.id as osm_id', 'osm.type as osm_type', 'varosok.irsz')
                ->where('templomok.id', "=", $tid)
                ->limit(1)
                ->get();

        if ($result != array()) {


            $row = (array) $result[0];
            foreach ($row as $k => $v) {
                $return[$k] = $v;
            }

            $return['responsible'] = array($return['letrehozta']);

            $neighbours = DB::select("SELECT d.distance tavolsag,t.nev,t.ismertnev,t.varos,t.id tid FROM distance as d
            LEFT JOIN templomok as t ON (tid1 <> :tid1 AND tid1 = id ) OR (tid2 <> :tid2 AND tid2 = id )
            WHERE ( tid1 = :tid3 OR tid2 = :tid4 ) AND distance <= 10000 
            AND t.id IS NOT NULL 
            ORDER BY distance ", ['tid1' => $tid, 'tid2' => $tid, 'tid3' => $tid, 'tid4' => $tid]);
            $return['szomszedok'] = (array) $neighbours;

            if (!isset($return['szomszedok'])) {
                $neighbours = DB::select("SELECT d.distance tavolsag,t.nev,t.ismertnev,t.varos,t.id tid FROM distance as d
                LEFT JOIN templomok as t ON (tid1 <> :tid1 AND tid1 = id ) OR (tid2 <> :tid2 AND tid2 = id )
                WHERE ( tid1 = :tid3 OR tid2 = tid4 )
                ORDER BY distance 
                LIMIT 1", ['tid1' => $tid, 'tid2' => $tid, 'tid3' => $tid, 'tid4' => $tid]);
                $return['szomszedok'] = (array) $neighbours;
            }

            if ($return['osm_id'] != '' AND $return['osm_type'] != '') {
                $return['osm']['type'] = $return['osm_type'];
                $return['osm']['id'] = $return['osm_id'];

                $tags = DB::table("osm_tags")->select("*")
                        ->where("type", "=", $return['osm_type'])
                        ->where("id", "=", $return['osm_id'])
                        ->get();
                foreach ($tags as $tag) {
                    $tag = (array) $tag;
                    if (in_array($tag['name'], array('lat', 'lon'))) {
                        $return['osm'][$tag['name']] = $tag['value'];
                    } else
                        $return['osm']['tags'][$tag['name']] = $tag['value'];
                }
            }

            unset($return['log']);

            $diocese = DB::table("egyhazmegye")->select("*")
                    ->where("id", "=", $return['egyhazmegye'])
                    ->limit(1)
                    ->get();
            $return['diocese'] = (array) $diocese[0];
            $return['diocese']['responsible'][] = $return['diocese']['felelos'];

            $return['kepek'] = getImages($tid);


            if ($return != array()) {
                if (!checkPrivilege('church', 'read', $return))
                    return array();
            }
            foreach ($return as $k => $r) {
                $this->$k = $r;
            }
            //$this = $return;
        }
    }

}
