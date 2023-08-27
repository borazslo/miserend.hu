<?php

namespace Eloquent;

class OSMTag extends \Illuminate\Database\Eloquent\Model {

    protected $table = 'osmtags';
    protected $fillable = array('osmtype','osmid', 'name', 'value');

    public function shortcut() {
        return $this->belongsTo('\Eloquent\KeywordShortcut', 'id', 'osmtag_id');
    }
    
    public function delete() {
        $this->shortcut()->delete();      
        parent::delete();
    }

}
