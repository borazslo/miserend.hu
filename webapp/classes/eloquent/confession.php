<?php

namespace Eloquent;

class Confession extends \Illuminate\Database\Eloquent\Model {

    protected $fillable = array('church_id');

    // Disable automatic timestamps
    public $timestamps = false;

    public function getChurchAttribute($value) {
        return \Eloquent\Church::find($this->church_id);
    }    

}
