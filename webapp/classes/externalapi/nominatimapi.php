<?php

namespace ExternalApi;

# https://operations.osmfoundation.org/policies/nominatim/

class NominatimApi extends \ExternalApi\ExternalApi {

    public $name = 'nominatim';
    public $apiUrl = "https://nominatim.openstreetmap.org/" ;    

    function OSM2GeoJson($osmtype, $osmid) {
        
        $this->cache = '2 weeks';  // Nem számolunnk azzal, hogy a boundary-k sűrűn változnának.
        $this->query = "details.php?addressdetails=1&hierarchy=0&group_hierarchy=1";
        $this->query .= "&osmtype=".$osmtype."&osmid=".$osmid;
        $this->query .= "&polygon_geojson=1&format=json";

        if($this->runQuery())
            return $this->jsonData->geometry;        
        else
            return false;
    }

    function buildQuery() {
        global $config;
        $this->rawQuery = $this->query;        
    }

}

