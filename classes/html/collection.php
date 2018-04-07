<?php

namespace Html;

use Illuminate\Database\Capsule\Manager as DB;

class Collection extends Html {

    public function __construct() {
        parent::__construct();

        preg_match('/(node|way|relation):([0-9]{1,8})$/i', $this->input['q'], $match);
        $osm = \Eloquent\Boundary::where('osmtype',$match[1])
                ->where('osmid',$match[2])->first();
        $this->setTitle($osm->name);

        if ($osm->lat) {
            $this->lat = $osm->lat;
        } else {
            $this->lat = 47.5;
        }

        if ($osm->lon) {
            $this->lon = $osm->lon;
        } else {
            $this->lon = 19.05;
        }
        $this->zoom = 12;

        $churchIds = DB::table('boundaries')
                ->join('lookup_boundary_church','boundaries.id','=','lookup_boundary_church.boundary_id')
                ->where('boundaries.osmtype',$match[1])
                ->where('boundaries.osmid',$match[2])
                ->select('church_id')
                ->pluck('church_id');
        
        $churches = \Eloquent\Church::whereIn('id',$churchIds)
                ->where('ok','i')
                ->orderBy('nev')
                ;

        $this->pagination->set($churches->count());
        $this->churches = $churches->skip($this->pagination->skip)->take($this->pagination->take)->get();
        foreach ($this->churches as &$church) {
            $church->photos;
        }
    }

}
