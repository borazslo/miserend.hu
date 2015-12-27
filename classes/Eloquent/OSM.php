<?php

namespace Eloquent;

class OSM extends \Illuminate\Database\Eloquent\Model {

    protected $table = 'osm';
    protected $fillable = array('osmid', 'osmtype');

    public function scopeWhereOSMId($query, $osmtype, $osmid) {
        return $query->where('osmtype', $osmtype)->where('osmid', $osmid);
    }

    public function tags() {
        return $this->hasMany('\Eloquent\OSMTag', 'osm_id', 'id');
    }

}
