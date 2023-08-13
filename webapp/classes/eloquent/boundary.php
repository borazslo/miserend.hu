<?php

namespace Eloquent;

class Boundary extends \Illuminate\Database\Eloquent\Model {

    #protected $table = 'osmtags';
    protected $fillable = array('osmtype', 'osmid','boundary','denomination','admin_level','name');
    protected $appends = array('url');
    
    function getUrlAttribute($value) {
        return 'https://www.openstreetmap.org/'.$this->osmtype.'/'.$this->osmid;
    }
    
    public function location() {
        $location = \Eloquent\OSM::
                    where('osmtype',$this->osmtype)
                    ->where('osmid',$this->osmid)
                    ->first();       
        return $location;
    }
}

