<?php

namespace Eloquent;

class OSM extends \Illuminate\Database\Eloquent\Model {

    protected $table = 'osm';
    protected $fillable = array('osmid', 'osmtype');
    protected $appends = array('tagList', 'name');

    public function scopeWhereOSMId($query, $osmtype, $osmid) {
        return $query->where('osmtype', $osmtype)->where('osmid', $osmid);
    }

    public function tags() {
        return $this->hasMany('\Eloquent\OSMTag', 'osm_id', 'id')->orderBy('name');
    }

    public function churches() {
        return $this->belongsToMany('\Eloquent\Church', 'lookup_church_osm', 'osm_id', 'church_id');
    }

    public function getNameAttribute($value) {
        $order = ['name:hu', 'name', 'alt_name:hu', 'alt_name', 'old_name:hu', 'old_name'];
        foreach ($order as $key) {
            if (array_key_exists($key, $this->tagList)) {
                return $this->tagList[$key];
            }
        }
        return false;
    }

    public function getAdministrativeAttribute($value) {
        for($i=1;$i<10;$i++) {
             $osm = $this->_getAdministrative($i);
             if($osm->name) {
                $return[$i] = $osm;
             }             
        }                
        return $return;
    }

    public function _getAdministrative($level) {
        return $this->enclosing()
                        ->whereHasTag('boundary', 'administrative')
                        ->whereHasTag('admin_level', $level)
                        ->first();
    }

    public function getAddressAttribute($value) {
        $return = new \stdClass();
        if (array_key_exists('addr:street', $this->tagList)) {
            $return->name = $this->tagList['addr:street'] . " " . $this->tagList['addr:housenumber'];
        }
        return $return;
    }

    public function getReligiousAdministrationAttribute($value) {
        $administrationLevels = ['diocese', 'deaconry', 'parish'];
        foreach ($administrationLevels as $level) {
            if ($this->$level) {
                $return[$level] = $this->$level;
            }
        }
        return $return;
    }

    public function getDioceseAttribute($value) {
        return $this->enclosing()
                        ->whereHasTag('boundary', 'religious_administration')
                        ->whereHasTag('admin_level', '6')
                        ->first();
    }

    public function getDeaconryAttribute($value) {
        return $this->enclosing()
                        ->whereHasTag('boundary', 'religious_administration')
                        ->whereHasTag('admin_level', '7')
                        ->first();
    }

    public function getParishAttribute($value) {
        return $this->enclosing()
                        ->whereHasTag('boundary', 'religious_administration')
                        ->whereHasTag('admin_level', '8')
                        ->first();
    }

    public function scopeWhereHasTag($query, $name, $arg2, $arg3 = false) {
        if ($arg2 == false) {
            $value = $arg2;
            $operator = '=';
        } else {
            $value = $arg3;
            $operator = $arg2;
        }
        return $query->whereHas('tags', function ($query) use ($name, $operator, $value) {
                    $query->where('name', $name)->where('value', $operator, $value);
                });
    }

    public function getTagListAttribute($value) {
        $return = [];
        foreach ($this->tags()->get() as $tag) {
            $return[$tag->name] = $tag->value;
        }
        return $return;
    }

    //Returns OSM elements that encloses this element. This element enclosed by the returning ones.
    public function enclosing() { 
        return $this->belongsToMany('\Eloquent\OSM', 'lookup_osm_enclosed', 'osm_id', 'enclosing_id');
    }

    //Return OSM elements that is enclosed by this element. This element encloses the returning ones.
    public function enclosed() {
        return $this->belongsToMany('\Eloquent\OSM', 'lookup_osm_enclosed', 'enclosing_id', 'osm_id');
    }
    
    public function delete() {
        $this->tags()->delete();      
        parent::delete();
    }
}
