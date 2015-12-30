<?php

namespace Eloquent;

class OSMTag extends \Illuminate\Database\Eloquent\Model {

    protected $table = 'osmtags';
    protected $fillable = array('osm_id', 'name', 'value');

    public function osm() {
        return $this->belongsTo('\Eloquent\OSM', 'osm_id', 'id');
    }

}
