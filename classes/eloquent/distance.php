<?php

namespace Eloquent;

class Distance extends \Illuminate\Database\Eloquent\Model {

    protected $fillable = array('church_from', 'church_to');
    
    public function getToAttribute($value) {
        return \Eloquent\Church::find($this->church_to);
    }
    
    public function getFromAttribute($value) {
        return \Eloquent\Church::find($this->church_from);
    }
}
