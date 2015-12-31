<?php

namespace Html;

class Collection extends Html {
       
    public function __construct() {
        $this->input = $_REQUEST;
        
         preg_match('/(node|way|relation):([0-9]{1,8})$/i',$this->input['q'],$match);         
         $osm = \Eloquent\OSM::whereOSMId($match[1],$match[2])->first();
         $this->setTitle($osm->name);
         
         $churches = \Eloquent\Church::whereHas('osms.enclosing',function($query) use ($osm) {
             $query->where('enclosing_id',$osm->id);
         } );                                             
        
         $this->churches = $churches->get();
         foreach ($this->churches as &$church) {
             $church->photos;
         }
                 
    }
    
}