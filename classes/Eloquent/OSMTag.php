<?php

namespace Eloquent;

class OSMTag extends \Illuminate\Database\Eloquent\Model {

    protected $table = 'osmtags';
    protected $fillable = array('osm_id', 'name', 'value');

    public function osm() {
        return $this->belongsTo('\Eloquent\OSM', 'osm_id', 'id');
    }
    
    public function shortcut() {
        return $this->belongsTo('\Eloquent\KeywordShortcut', 'id', 'osmtag_id');
    }
    
    public function delete() {
        $this->shortcut()->delete();      
        parent::delete();
    }

}
