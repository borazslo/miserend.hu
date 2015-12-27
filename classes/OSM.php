<?php

use Illuminate\Database\Capsule\Manager as DB;

class OSM  {

    public $type;
    public $id;
    public $table = 'osm';

  
    function downloadEnclosingBoundaries() {
        $overpass = new \OverpassApi();
        $overpass->downloadEnclosingBoundaries($this->lat, $this->lon);
        if (!$overpass->jsonData->elements) {
            throw new Exception("Missing Json Elements from OverpassApi Query");
        }
        foreach ($overpass->jsonData->elements as $element) {
            if (isset($element->center->lat)) {
                $element->lat = $element->center->lat;
            }
            if (isset($element->center->lon)) {
                $element->lon = $element->center->lon;
            }
                      
            $newOSM = new \Eloquent\OSM();
            printr($newOSM);
            copyArrayToObject($element, $newOSM);                        
            $newOSM->save();
            printr($newOSM);
            exit;
        }
    }

   
}
