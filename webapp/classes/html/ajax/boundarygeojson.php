<?php

namespace Html\Ajax;

class BoundaryGeoJson extends Ajax {

    public function __construct() {
        
        if(!isset($_REQUEST['osm'])) return;
                     
        header('Content-Type: application/json');  
        echo "[";
        $osmdatas = explode(';',$_REQUEST['osm']);
        foreach($osmdatas as $key => $osmdata ) {
            
            preg_match('/(node|way|relation|N|W|R):([0-9]{1,8})$/i', $osmdata, $osm); 
            if($osm[1] == 'relation') $osm[1] = 'R';
            elseif($osm[1] == 'way') $osm[1] = 'W';
            elseif($osm[1] == 'node') $osm[1] = 'N';

            $nominatim = new \ExternalApi\NominatimApi();
            $geoJson = $nominatim->OSM2GeoJson($osm[1], $osm[2]);
            
            print_r(json_encode($geoJson));
                        
            if( $key + 1 < count($osmdatas)) echo ",";            
        }
        echo "]";        
    }}
