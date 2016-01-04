<?php

namespace Html;

class Collection extends Html {

    public function __construct() {
        parent::__construct();

        preg_match('/(node|way|relation):([0-9]{1,8})$/i', $this->input['q'], $match);
        $osm = \Eloquent\OSM::whereOSMId($match[1], $match[2])->first();
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

        $churches = \Eloquent\Church::whereHas('osms.enclosing', function($query) use ($osm) {
                    $query->where('enclosing_id', $osm->id);
                });

        $this->churches = $churches->skip($this->pagination->skip)->take($this->pagination->take)->get();
        foreach ($this->churches as &$church) {
            $church->photos;
        }

        $this->pagination->set($churches->count());
    }

}
