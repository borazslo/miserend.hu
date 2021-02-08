<?php

namespace Html;

class Map extends Html {

    public function __construct() {
        $this->setTitle("OSM Térkép");

        if (isset($_REQUEST['tid']) AND is_numeric($_REQUEST['tid'])) {
            $church = \Eloquent\Church::find($_REQUEST['tid']);
            
            $this->location = $church->location;
            $this->church_id = $_REQUEST['tid'];   
        }
        
        if(isset($_REQUEST['map'])) {
            $parts = explode('/',$_REQUEST['map']);
            foreach($parts as $part) {
                if(!is_numeric($part)) return;
            }
            
            if(count($parts) == 3) {
                $this->center = [
                    'zoom' => $parts[0],
                    'lat' => $parts[1],
                    'lon' => $parts[2]
                ];
            }
            
            if(count($parts) == 2) {
                $this->center = [
                    'lat' => $parts[0],
                    'lon' => $parts[1]
                ];
            }
            
        }
        
    }

}
