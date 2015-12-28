<?php

namespace Eloquent;

class Distance extends \Illuminate\Database\Eloquent\Model {

    public function getToAttribute($value) {
        return \Eloquent\Church::find($value);
    }
}
