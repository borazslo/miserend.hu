<?php

namespace Eloquent;

class Remark extends \Illuminate\Database\Eloquent\Model {
    
    public function church() {
        return $this->belongsTo('\Eloquent\Church');
    }

}
