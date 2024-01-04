<?php

namespace App\Model;

class OSMTag extends \Illuminate\Database\Eloquent\Model {

    protected $table = 'osmtags';
    protected $fillable = array('osmtype','osmid', 'name', 'value');

    public function shortcut() {
        return $this->belongsTo(\App\Model\KeywordShortcut::class, 'id', 'osmtag_id');
    }
    
    public function delete() {
        $this->shortcut()->delete();      
        parent::delete();
    }

}
