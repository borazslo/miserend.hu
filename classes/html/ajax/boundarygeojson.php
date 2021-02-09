<?php

namespace Html\Ajax;

class BoundaryGeoJson extends Ajax {

    public function __construct() {
        
        if(!isset($_REQUEST['osm'])) return;
                
        preg_match('/(node|way|relation):([0-9]{1,8})$/i', $_REQUEST['osm'], $osm);
                
        if($osm[1] == 'relation') $osm[1] = 'R';
        elseif($osm[1] == 'way') $osm[1] = 'W';
        elseif($osm[1] == 'node') $osm[1] = 'N';
                        
        $nominatim = new \ExternalApi\NominatimApi();
        $geoJson = $nominatim->OSM2GeoJson($osm[1], $osm[2]);

        header('Content-Type: application/json');        
        print_r(json_encode($geoJson));
        
    }
}
