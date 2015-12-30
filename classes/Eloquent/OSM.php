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
        $order = ['name:hu', 'name', 'alt_name', 'alt_name:hu'];
        foreach ($order as $key) {
            if (array_key_exists($key, $this->tagList)) {
                return $this->tagList[$key];
            }
        }
        return false;
    }

    public function getAdministrativeAttribute($value) {        
        $administrationLevels = ['country','county','city'];
        foreach($administrationLevels as $level) {
            if($this->$level) {
                $return[$level] = $this->$level;
            }
        }
        return $return;
    }
    
    public function getCountryAttribute($value) {
        return $this->enclosing()
                        ->whereHasTag('boundary', 'administrative')
                        ->whereHasTag('admin_level', '2')
                        ->first();
    }

    public function getCountyAttribute($value) {
        return $this->enclosing()
                        ->whereHasTag('boundary', 'administrative')
                        ->whereHasTag('admin_level', '6')
                        ->first();
    }

    public function getCityAttribute($value) {
        return $this->enclosing()
                        ->whereHasTag('boundary', 'administrative')
                        ->whereHasTag('admin_level', '8')
                        ->first();
    }

    public function getReligiousAdministrationAttribute($value) {        
        $administrationLevels = ['diocese','deaconry','parish'];
        foreach($administrationLevels as $level) {
            if($this->$level) {
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

    public function enclosing() {
        return $this->belongsToMany('\Eloquent\OSM', 'lookup_osm_enclosed', 'osm_id', 'enclosing_id');
    }

}
